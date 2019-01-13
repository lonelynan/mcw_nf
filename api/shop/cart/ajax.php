<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/shipping.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/flow.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/order.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/support/tool_helpers.php');
require_once ($_SERVER ["DOCUMENT_ROOT"] . '/Framework/Ext/virtual.func.php');
require_once ($_SERVER ["DOCUMENT_ROOT"] . '/Framework/Ext/sms.func.php');
require_once ($_SERVER ["DOCUMENT_ROOT"] .'/control/shop/cart.class.php');
require_once ($_SERVER ["DOCUMENT_ROOT"] .'/include/models/UserMoneyRecord.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/compser_library/Salesman_Commission.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/biz/plugin/dada/DadaOpenapi.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/biz/plugin/dada/function.php');

use Illuminate\Database\Capsule\Manager as DB;
ini_set("display_errors","On");

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
    die(json_encode([
        'status'=>0,
        'msg'=>'非法提交'
    ],JSON_UNESCAPED_UNICODE));
}
$action = empty($_REQUEST["action"]) ? "" : $_REQUEST["action"];
if (isset($_POST['User_ID'])) {
    $UserID = (int)$_POST['User_ID'];

    $res = little_program_key_check($_POST);
    //$res = ['errorCode' => 0];
    if ($res['errorCode'] != 0) {
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    }
} else {
    if (isset($_SESSION[$UsersID . "User_ID"])) {
        $UserID = $_SESSION[$UsersID . "User_ID"];
    } else {
        $UserID = 0;
    }
}

$myRedis = new Myredis($DB, 7);

if(empty($action)){//加入购物车
	//参数判断
	if(isset($_POST["ProductsID"])){
		$ProductsID = $_POST["ProductsID"];
	}else{
		$Data = array(
			"status"=>0,
			"msg"=>'非法提交'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	if(!isset($UserID) || empty($UserID)){
		$Data = array(
			"status"=>118,
			"url"=>'http://'.$_SERVER['HTTP_HOST']."/api/".$UsersID."/user/login/"
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	$rsProducts = $DB->GetRs("shop_products","*","where Users_ID='".$UsersID."' and Products_ID=".$ProductsID." and Products_SoldOut=0 and Products_Status=1");

	//会员折扣
    $user_curagio = get_user_curagio($UsersID, $UserID);

	if(!$rsProducts){
		$Data = array(
			"status"=>0,
			"msg"=>'产品已下架'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	if($rsProducts["Products_Count"] > 0 && $rsProducts["Products_Count"] < $_POST["Qty"]){
		$Data = array(
			"status" => 0,
			"msg" => '产品库存不足，最多能购买' . $rsProducts["Products_Count"] . '件'
		);
		echo json_encode($Data, JSON_UNESCAPED_UNICODE);
		exit;
	}
	$BizID = $rsProducts["Biz_ID"];

	// 判断是否为显示抢购提交
    $flashsale_flag = 0;
    if (!empty($_POST['flashsale'])) {
        //判断系统是否开启显示抢购活动，并且在使用方范围内
        $flashsale_flag = shop_flashsale_flag($UsersID);
    }

    // 获得限时抢购价格（如果既是抢购又是拼团那么拼团价就是抢购价，单购价还是单购价。如果只是抢购不是拼团，那么单购价就是抢购价）
    if ($_POST['cart_key'] == 'PTCartList') {    //拼团订单
        $cur_price = $rsProducts["pintuan_pricex"];

    } else {
        if ($flashsale_flag) {
            $cur_price = $rsProducts["flashsale_pricex"];
        } else {
            $cur_price = $rsProducts["Products_PriceX"];
        }
    }

	/*如果此产品包含属性*/
	$Property = array();
	$JSON = json_decode($rsProducts['Products_JSON'], true);//产品图片
	$OwnerID = !empty($_POST['OwnerID']) ? $_POST['OwnerID'] : 0;//店主	
	$cart_key = $UsersID.$_POST['cart_key'];
	$flag = true;
    if($cart_key == $UsersID.'CartList'){
        $redisCart = $myRedis->getCartListValue($cart_key, $UserID);
        if (!empty($redisCart)) {
            $CartList = json_decode($redisCart, true);
        }
    }else{
        $myRedis->getCartListValue($cart_key, $UserID);
    }

	if (!empty($rsProducts['skuvaljosn'])) {
            $skuattr = json_decode($rsProducts['skuvaljosn'], true);
		$keyatrrid = substr($_POST['atrid_str'],0,strlen($_POST['atrid_str'])-1);
		//属性值	
		$Property['shu_value'] = !empty($_POST['atr_str'])?$_POST['atr_str']:0;
		$Property['shu_vid'] = $keyatrrid;

		// 获得抢购价格
        // 获得抢购属性价格
        if ($_POST['cart_key'] == 'PTCartList') {    //拼团订单
            $Txt_PtPriceSon = !empty($skuattr[$keyatrrid]['Txt_PtPriceSon']) ? $skuattr[$keyatrrid]['Txt_PtPriceSon'] : $rsProducts['pintuan_pricex'];
            $Property['shu_price'] = $Txt_PtPriceSon * $_POST["Qty"];
            $Property['shu_pricesimp'] = $Txt_PtPriceSon ;

        } else {
            if ($flashsale_flag) {
                $Txt_XsPriceSon = !empty($skuattr[$keyatrrid]['Txt_XsPriceSon']) ? $skuattr[$keyatrrid]['Txt_XsPriceSon'] : 0;

                $Property['shu_price'] = $Txt_XsPriceSon * $_POST["Qty"];
                $Property['shu_pricesimp'] = $Txt_XsPriceSon;
                $rsProducts["Products_PriceS"] = !isset($skuattr[$keyatrrid]['Txt_XsPriceSon']) ? $rsProducts["Products_PriceS"] : $skuattr[$keyatrrid]['Txt_XsPriceSon'];
            } else {
                $Txt_PriceSon = !empty($skuattr[$keyatrrid]['Txt_PriceSon']) ? $skuattr[$keyatrrid]['Txt_PriceSon'] : 0;

                $Property['shu_price'] = $Txt_PriceSon * $_POST["Qty"];
                $Property['shu_pricesimp'] = $Txt_PriceSon;
                $rsProducts["Products_PriceS"] = !isset($skuattr[$keyatrrid]['Txt_PriceSon']) ? $rsProducts["Products_PriceS"] : $skuattr[$keyatrrid]['Txt_PriceSon'];
            }
        }

		$Property['shu_priceg'] = !empty($skuattr[$keyatrrid]['Txt_PriceG']) ? $skuattr[$keyatrrid]['Txt_PriceG'] : 0;
		$Property['shu_conut'] = $_POST['count'];
		$Property['showimg'] = $_POST['showimg'];
	} else {
        //如果没有属性的情况下,因为下面都是用的Products_PriceS,因此需要把这个index给重置为拼团的供货价
        if ($_POST['cart_key'] == 'PTCartList') {
            $rsProducts["Products_PriceS"] = $rsProducts['pintuan_prices'];
        }
    }

	$existence = 0;
	if(!empty($redisCart)){
		if (!empty($rsProducts['skuvaljosn'])) {
		$souceqty = isset($CartList[$BizID][$ProductsID][$Property['shu_vid']]['Qty']) ? $CartList[$BizID][$ProductsID][$Property['shu_vid']]['Qty'] :  0 ;
		}else{	
			foreach($CartList as $key=>$products){
				foreach($products as $id=>$vale){
					if ($ProductsID == $id) {
						$existence = 1;
						foreach($vale as $k=>$v){
							$existencekey = $k;
							break 3;
						}					
					}
				}			
			}
			if ($existence == 1) {
				$souceqty = isset($CartList[$BizID][$ProductsID][$existencekey]['Qty']) ? $CartList[$BizID][$ProductsID][$existencekey]['Qty'] :  0 ;
			}else{
				$souceqty = 0;
			}
		}
	}else{
		$souceqty = 0;
	}
	// 图片
	if (!empty($_POST['showimg']) && $_POST['showimg'] != 'undefined') {
		$showimg = $_POST['showimg'];
	}elseif(!empty($JSON["ImgPath"])){
		$showimg = $JSON["ImgPath"][0];
	}else{
		$showimg = '';
	}
	$rsBiz = DB::table('biz')->where(['Users_ID' => $UsersID, 'Biz_ID' => $BizID])->first(['Finance_Type','Finance_Rate']);

	if($flag){
        $pay_type = !empty($_POST['pay_type']) ? (int)$_POST['pay_type'] : 0;
        if ($_POST['cart_key'] == 'PTCartList') {    //拼团订单
            if ($rsProducts['pintuan_flag'] != 1 || $rsProducts['pintuan_people'] < 2 || $rsProducts['pintuan_start_time'] > time() || $rsProducts['pintuan_end_time'] < time() || $rsProducts['pintuan_pricex'] <= 0) {   //拼团条件不符
                $Data = array(
                    "status" => 0,
                    "msg" => '拼团失败，该产品不符合拼团条件'
                );
                echo json_encode($Data, JSON_UNESCAPED_UNICODE);
                exit;
            }
            $teamid = !empty($_POST['teamid']) ? (int)$_POST['teamid'] : 0; //拼团id 为 0 则为开团
            if ($teamid > 0) {  //拼团订单  查询是否符合此拼团条件
                $rsTeam = $DB->GetRs('pintuan_team', 'teamnum, userid', 'where id = ' . $teamid . ' and users_id = "' . $UsersID . '" and productid = ' . $ProductsID . ' and teamstatus = 0 and addtime > ' . (time() - 24*60*60) . ' and teamnum > 0 and teamnum < ' . $rsProducts['pintuan_people']);
                if (empty($rsTeam)) {
                    $Data = array(
                        "status" => 0,
                        "msg" => '此拼团不符合条件，请去开团'
                    );
                    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $rsTeamDetail = $DB->GetAssoc('pintuan_teamdetail', 'userid', 'where teamid = ' . $teamid);
                if ($UserID == $rsTeam['userid'] || in_array($UserID, array_column($rsTeamDetail, 'userid'))) {
                    $Data = array(
                        "status" => 0,
                        "msg" => '您已参加过此拼团，不可再参加'
                    );
                    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }

            $atrid_str = !empty($_POST['atrid_str']) ? rtrim($_POST['atrid_str'], ';') : '';

            $CartList[$BizID][$ProductsID][$atrid_str] = [
                "teamid" => $teamid,
                'atrid_str' => $atrid_str,
                'pintuan_flag' => $rsProducts["pintuan_flag"],
                'pintuan_start_time' => $rsProducts["pintuan_start_time"],
                'pintuan_end_time' => $rsProducts["pintuan_end_time"],
                'pintuan_people' => $rsProducts["pintuan_people"],
                'pintuan_pricex' => $cur_price,
                'Is_BizStores' => $rsProducts['Is_BizStores'], //是否支持到店自提

                "ProductsName" => $rsProducts["Products_Name"],
                "ImgPath" => $showimg,  //产品图片
                "ProductsPriceX" => $cur_price,
                "ProductsPriceY" => $rsProducts["Products_PriceY"],
                "Products_PriceS" => $rsProducts["Products_PriceS"],
                "ProductsPriceA" => !empty($rsProducts['skuvaljosn']) ? $rsProducts["Products_PriceA"] : 0,
                "ProductsPriceAmax" => !empty($rsProducts['skuvaljosn']) ? $rsProducts["Products_PriceAmax"] : 0,
                "Products_Integration" => $rsProducts["Products_Integration"],
                "user_curagio" => 0,    //拼团产品不参与用户折扣
                "Products_IsPaysBalance" => $rsProducts["Products_IsPaysBalance"],
                "Productsattrstrval" => !empty($rsProducts['skuvaljosn']) && !empty($Property['shu_value']) ? $Property['shu_value'] : '',
                "Productsattrkeystrval" => !empty($rsProducts['skuvaljosn']) && isset($keyatrrid) && !empty($skuattr[$keyatrrid]) ? $skuattr[$keyatrrid] : [],
                "ProductsWeight" => $rsProducts["Products_Weight"],
                "Products_Shipping" => $rsProducts["Products_Shipping"],
                "Products_Business" => $rsProducts["Products_Business"],
                "Shipping_Free_Company" => $rsProducts["Shipping_Free_Company"],
                "IsShippingFree" => $rsProducts["Products_IsShippingFree"],
                "OwnerID" => $OwnerID,
                "ProductsIsShipping" => $rsProducts["Products_IsShippingFree"],
                "Qty" => $_POST["Qty"],

                "Products_FinanceType" => $rsProducts["Products_FinanceType"],
                "Products_FinanceRate" => $rsProducts["Products_FinanceRate"],
                "Biz_FinanceType" => $rsBiz["Finance_Type"],
                "Biz_FinanceRate" => $rsBiz["Finance_Rate"],

                "Property" => $Property,
                "nobi_ratio" => $rsProducts["nobi_ratio"],
                "platForm_Income_Reward" => $rsProducts["platForm_Income_Reward"],
                "area_Proxy_Reward" => $rsProducts["area_Proxy_Reward"],
                "sha_Reward" => $rsProducts["sha_Reward"],
                'pay_type' => $pay_type,

                "Products_IsVirtual" => $rsProducts['Products_IsVirtual'],
                "Products_IsRecieve" => $rsProducts['Products_IsRecieve'],
                // 特价
                'flashsale_flag' => $flashsale_flag,
                'flashsale_pricex' => $rsProducts['flashsale_pricex']
            ];

        } else {

			if (!empty($rsProducts['skuvaljosn'])) {
				$CartList[$BizID][$ProductsID][$Property['shu_vid']] = array(
					"ProductsName" => $rsProducts["Products_Name"],
					"ImgPath" => $showimg,
					"ProductsPriceX" => $cur_price,
					"ProductsPriceY" => $rsProducts["Products_PriceY"],
					"Products_PriceS" => $rsProducts["Products_PriceS"],
					"ProductsPriceA" => $rsProducts["Products_PriceA"],
					"ProductsPriceAmax" => $rsProducts["Products_PriceAmax"],
					"Products_Integration" => $rsProducts["Products_Integration"],
					"user_curagio" => $user_curagio,			
					"Products_IsPaysBalance" => $rsProducts["Products_IsPaysBalance"],
					"Productsattrstrval" => $Property['shu_value'],
					"Productsattrkeystrval" => isset($keyatrrid) && !empty($skuattr[$keyatrrid]) ? $skuattr[$keyatrrid] : [],
					"ProductsWeight" => $rsProducts["Products_Weight"],
					"Products_Shipping" => $rsProducts["Products_Shipping"],
					"Products_Business" => $rsProducts["Products_Business"],
					"Shipping_Free_Company" => $rsProducts["Shipping_Free_Company"],
					"IsShippingFree" => $rsProducts["Products_IsShippingFree"],
					"OwnerID" => $OwnerID,
					"ProductsIsShipping" => $rsProducts["Products_IsShippingFree"],
					"Qty" => $souceqty + $_POST["Qty"],
					"Products_FinanceType" => $rsProducts["Products_FinanceType"],
					"Products_FinanceRate" => $rsProducts["Products_FinanceRate"],
					"Biz_FinanceType" => $rsBiz["Finance_Type"],
					"Biz_FinanceRate" => $rsBiz["Finance_Rate"],
					"Property" => $Property,
					"nobi_ratio" => $rsProducts["nobi_ratio"],
					"platForm_Income_Reward" => $rsProducts["platForm_Income_Reward"],
					"area_Proxy_Reward" => $rsProducts["area_Proxy_Reward"],
					"sha_Reward" => $rsProducts["sha_Reward"],
					'pay_type' => $pay_type,

					"Products_IsVirtual" => $rsProducts['Products_IsVirtual'],
					"Products_IsRecieve" => $rsProducts['Products_IsRecieve'],

					'Is_BizStores' => $rsProducts['Is_BizStores'], //是否支持到店自提
					// 拼团
					'pintuan_flag' => ($rsProducts['pintuan_flag'] == 1 && $rsProducts['pintuan_start_time'] <= time() && $rsProducts['pintuan_end_time'] > time() && $rsProducts['pintuan_people'] >= 2) ? 1 : 0,
					// 特价
					'flashsale_flag' => $flashsale_flag,
					'flashsale_pricex' => $rsProducts['flashsale_pricex']
				);
			}else{
				if ($existence == 1) {
					$CartList[$BizID][$ProductsID][$existencekey] = array(
						"ProductsName" => $rsProducts["Products_Name"],
						"ImgPath" => $showimg,
						"ProductsPriceX" => $cur_price,
						"ProductsPriceY" => $rsProducts["Products_PriceY"],
						"Products_PriceS" => $rsProducts["Products_PriceS"],			
						"ProductsPriceA" => 0,
						"ProductsPriceAmax" => 0,
						"Products_Integration" => $rsProducts["Products_Integration"],
						"user_curagio" => $user_curagio,
						"Productsattrstrval" => '',
						"Productsattrkeystrval" => array(),
						"ProductsWeight" => $rsProducts["Products_Weight"],
						"Products_IsPaysBalance" => $rsProducts["Products_IsPaysBalance"],
						"Products_Shipping" => $rsProducts["Products_Shipping"],
						"Products_Business" => $rsProducts["Products_Business"],
						"Shipping_Free_Company" => $rsProducts["Shipping_Free_Company"],
						"IsShippingFree" => $rsProducts["Products_IsShippingFree"],
						"OwnerID" => $OwnerID,
						"ProductsIsShipping" => $rsProducts["Products_IsShippingFree"],
						"Qty" => $souceqty + $_POST["Qty"],
						"Products_FinanceType" => $rsProducts["Products_FinanceType"],
						"Products_FinanceRate" => $rsProducts["Products_FinanceRate"],
						"Biz_FinanceType" => $rsBiz["Finance_Type"],
						"Biz_FinanceRate" => $rsBiz["Finance_Rate"],
						"Property" => $Property,
						"nobi_ratio" => $rsProducts["nobi_ratio"],
						"platForm_Income_Reward" => $rsProducts["platForm_Income_Reward"],
						"area_Proxy_Reward" => $rsProducts["area_Proxy_Reward"],
						"sha_Reward" => $rsProducts["sha_Reward"],
						'pay_type' => $pay_type,

						"Products_IsVirtual" => $rsProducts['Products_IsVirtual'],
						"Products_IsRecieve" => $rsProducts['Products_IsRecieve'],

						'Is_BizStores' => $rsProducts['Is_BizStores'], //是否支持到店自提
						// 拼团
						'pintuan_flag' => ($rsProducts['pintuan_flag'] == 1 && $rsProducts['pintuan_start_time'] <= time() && $rsProducts['pintuan_end_time'] > time() && $rsProducts['pintuan_people'] >= 2) ? 1 : 0,
						// 特价
						'flashsale_flag' => $flashsale_flag,
						'flashsale_pricex' => $rsProducts['flashsale_pricex'],
					);
				}else{
					$CartList[$BizID][$ProductsID][] = array(
						"ProductsName" => $rsProducts["Products_Name"],
						"ImgPath" => $showimg,
						"ProductsPriceX" => $cur_price,
						"ProductsPriceY" => $rsProducts["Products_PriceY"],
						"Products_PriceS" => $rsProducts["Products_PriceS"],
						"ProductsPriceA" => 0,
						"ProductsPriceAmax" => 0,
						"Products_Integration" => $rsProducts["Products_Integration"],
						"user_curagio" => $user_curagio,
						"Productsattrstrval" => '',
						"Productsattrkeystrval" => array(),
						"ProductsWeight" => $rsProducts["Products_Weight"],
						"Products_IsPaysBalance" => $rsProducts["Products_IsPaysBalance"],
						"Products_Shipping" => $rsProducts["Products_Shipping"],
						"Products_Business" => $rsProducts["Products_Business"],
						"Shipping_Free_Company" => $rsProducts["Shipping_Free_Company"],
						"IsShippingFree" => $rsProducts["Products_IsShippingFree"],
						"OwnerID" => $OwnerID,
						"ProductsIsShipping" => $rsProducts["Products_IsShippingFree"],
						"Qty" => $souceqty + $_POST["Qty"],
						"Products_FinanceType" => $rsProducts["Products_FinanceType"],
						"Products_FinanceRate" => $rsProducts["Products_FinanceRate"],
						"Biz_FinanceType" => $rsBiz["Finance_Type"],
						"Biz_FinanceRate" => $rsBiz["Finance_Rate"],
						"Property" => $Property,
						"nobi_ratio" => $rsProducts["nobi_ratio"],
						"platForm_Income_Reward" => $rsProducts["platForm_Income_Reward"],
						"area_Proxy_Reward" => $rsProducts["area_Proxy_Reward"],
						"sha_Reward" => $rsProducts["sha_Reward"],
						'pay_type' => $pay_type,

						"Products_IsVirtual" => $rsProducts['Products_IsVirtual'],
						"Products_IsRecieve" => $rsProducts['Products_IsRecieve'],

						'Is_BizStores' => $rsProducts['Is_BizStores'], //是否支持到店自提
						// 拼团
						'pintuan_flag' => ($rsProducts['pintuan_flag'] == 1 && $rsProducts['pintuan_start_time'] <= time() && $rsProducts['pintuan_end_time'] > time() && $rsProducts['pintuan_people'] >= 2) ? 1 : 0,
						// 特价
						'flashsale_flag' => $flashsale_flag,
						'flashsale_pricex' => $rsProducts['flashsale_pricex'],
					);	
				}		
			}
        }
	}

    $myRedis->setCartListValue($cart_key, $UserID, json_encode($CartList, JSON_UNESCAPED_UNICODE));
	$qty = 0;

    if ($_POST['cart_key'] == 'PTCartList') {    //拼团订单
        $qty = 1;
    } else {
        foreach($CartList as $bizid => $bizcart){
            foreach($bizcart as $key => $value){
                foreach($value as $v){
                    $qty += $v["Qty"];
                }
            }
        }
    }

    $Data = array(
        "status" => 1,
        "qty" => $qty
    );

    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
    exit;
	
} else if ($action == 'get_shipping') { //计算购物车中产品价格及运费，包括选择优惠券，积分以及选择普通物流或者达达配送的方式
	/******* 变量获取以及初始化 start ********/
	$cart_key = $request->input('cart_key', '');
	$CouponListID = $request->input('CouponID', []);		//获取优惠券ID
	$StoreListID = $request->input('StoreID', []);	//获取门店ID
	$AddressID = $request->input('AddressID', 0);	//获取地址
	$source = $request->input('source', 0);	//获取请求来源，0 表示来自于小程序，1 表示来自于公众号请求
	$express = $request->input('express', 0);	//判断是否是虚拟产品请求，0 表示虚拟产品，1 表示实物产品
	$offsetList = $request->input('IsOffset', []);	//是否使用积分
	$default_express = $request->input('default_express', []);	//获取所有商家的物流方式
	/******* 变量获取以及初始化 end ********/
	//获取商城和分销商设置
    $rsConfig = shop_config($UsersID);
    $dis_config = dis_config($UsersID);
    $rsConfig = array_merge($rsConfig,$dis_config);
	//获取所有商家的物流方式，格式为：default_express:'{30:12,31:45,...,39:141}'
	if (!empty($default_express)) {
		$default_express = json_decode($default_express, true);
	}
	//获取所有商家使用积分的情况，格式为：IsOffset:'{30:1,31:1,...,39:0}'
	if (!empty($offsetList)) {
		$offsetList = json_decode($offsetList, true);
	}
	//获取所有商家选择门店的情况，格式为：StoreID:'{30:11,31:12,...,39:0}'
	if (!empty($StoreListID)) {
		$StoreListID = json_decode($StoreListID, true);
	}
	//获取所有商家选择优惠券的情况，格式为：CouponID:'{30:11,31:12,...,39:0}'
	if (!empty($CouponListID)) {
		$CouponListID = json_decode($CouponListID, true);
	}
	
	//获取个人设置的默认收货地址
	if (!empty($AddressID)) {
        $rsAddress = $DB->GetRs('user_address', '*', "where User_ID = " . $UserID . " and Address_ID = " . $AddressID);
        if (empty($rsAddress)) {
            $City_Code = 0;
        } else {
            $City_Code = $rsAddress['Address_City'];
        }
    } else {
        $City_Code = 0;
    }
	
	//获取购物车信息
	if (empty($cart_key)) {
        exit(json_encode(['errorCode' => 1, 'msg' => '缺少必要的参数'], JSON_UNESCAPED_UNICODE));
    }
    $myRedis = new Myredis($DB, 7);
    $redisCart = $myRedis->getCartListValue($UsersID . $cart_key, $UserID);
    if (!$redisCart) {
        exit(json_encode(['errorCode' => 1, 'msg' => '购物车为空'], JSON_UNESCAPED_UNICODE));
    }
    $CartList = json_decode($redisCart, true);

    //获取用户数据
    $rsUser = $DB->GetRs('user', '*', "where User_ID = " . $UserID . " and Users_ID = '" . $UsersID ."'");
	//用户优惠折扣
    $user_curagio = $cart_key == 'PTCartList' ? 0 : get_user_curagio($UsersID, $UserID);
    $total_info = get_order_total_info($UsersID, $CartList, $rsConfig, $default_express, $City_Code, $user_curagio, ['UserID' => $UserID, 'StoreListID' => $StoreListID, 'AddressID' => $AddressID]);
    $data = [
        'total_info' => $total_info
    ];
    $data['coupon_lists'] = [];
    $data['Integral'] = [];
    //获取物流信息
    $biz_company_dropdown = [];
	$cash = 0;	//计算各种优惠的总和
    $use_Integral = 0;  // 使用积分
	foreach ($total_info as $Biz_ID => $BizCartList) {

        if(isset($BizCartList['error']) && $BizCartList['error'] == 1){
            exit(json_encode(['errorCode' => 404, 'msg' => $BizCartList['msg']], JSON_UNESCAPED_UNICODE));
        }
		//非虚拟产品
        if (!empty($express)) {
            if (is_integer($Biz_ID)) {
				$biz_company_dropdown[$Biz_ID] = get_front_shiping_company_dropdown($UsersID, !empty($BizCartList['Biz_Config']) ? $BizCartList['Biz_Config'] : []);
                $isStores = $DB->GetAssoc('stores', '*', 'where Users_ID = "' . $UsersID . '" and Biz_ID = ' . $Biz_ID . ' and Stores_Status = 1');
				$thridShippList = $DB->GetAssoc('third_shipping', '*', 'where Users_ID = "' . $UsersID . '" and Status = 1');
                if (!empty($thridShippList) && !empty($isStores)) {
					$result = getStoreslist($UsersID, $Biz_ID, $UserID, $AddressID, $ak_baidu);
                    // 判断是否选择了门店，没选择则给小程序的第一个默认勾选
					foreach ($result as $k => $v) {
					    if (empty($StoreListID[$Biz_ID])) {
                            $result[$k]['checked'] = $k == 0 ? 1 : 0;
                        } else {
                            $result[$k]['checked'] = $StoreListID[$Biz_ID] == $v['Stores_ID'] ? 1 : 0;
                        }
                    }
					$data['Stores'][$Biz_ID] = $result;
					$biz_company_dropdown[$Biz_ID]['Is_dada'] = '同城配送';
				}
			}
		}
        if (!is_integer($Biz_ID)) {
			continue;
		}
		// 获取用户优惠券
		$coupon_cash = 0;
		$coupon_info = get_useful_coupons($UserID, $UsersID, $Biz_ID, $BizCartList['total']);
		if ($coupon_info['num'] > 0 && !empty($coupon_info['lists'])) {
			$coupon_lists = $coupon_info['lists'];
			foreach ($coupon_lists as $key => $item) {
				$coupon_lists[$key]['Price'] = $item["Coupon_UseType"] == 0 ? ($item["Coupon_Discount"] > 1 || $item["Coupon_Discount"] <= 0 ? 0 : $BizCartList['total'] * (1 - $item["Coupon_Discount"])) : $item["Coupon_Cash"];
				if(!empty($CouponListID[$Biz_ID]) && $CouponListID[$Biz_ID] == $item['Coupon_ID']){
					$coupon_cash = $coupon_lists[$key]['Price'];
				}
			}
			$data['coupon_lists'][$Biz_ID] = $coupon_lists;
		}
		$Integral_cash = 0;
		// 获取用户积分
		if (!empty($rsConfig['Integral_Buy'])) {
			$Products_Integration = Integration_get($Biz_ID, $rsConfig, $rsUser['User_Integral'], $total_info[$Biz_ID]['total']);
			$diyong_m = $Products_Integration / $rsConfig['Integral_Buy'];
			if(!empty($offsetList[$Biz_ID])){
				$Integral_cash = $diyong_m;
                $use_Integral += $Products_Integration;
			}
			$data['Integral'][$Biz_ID]['diyong_m'] = $diyong_m;
			$data['Integral'][$Biz_ID]['Int'] = $Products_Integration;
		}
		$youhui = $coupon_cash + $Integral_cash;
		$cash += $youhui;
		$data['total_info'][$Biz_ID]['cash'] = $youhui;
		$data['total_info'][$Biz_ID]['total'] -= $youhui;
        $data['total_info'][$Biz_ID]['total'] = round($data['total_info'][$Biz_ID]['total'] * 100) / 100;

        // 统计总价
        $data['total_info'][$Biz_ID]['pay_total'] = $data['total_info'][$Biz_ID]['total'] + (empty($data['total_info'][$Biz_ID]['total_shipping_fee']) ? 0 : $data['total_info'][$Biz_ID]['total_shipping_fee']);

        if($data['total_info'][$Biz_ID]['total']<1){
			exit(json_encode(['errorCode' => 1, 'msg' => '抵用之后的价格不能少于1元'], JSON_UNESCAPED_UNICODE));
		}
	}
    $data['Integral']['User_Integral'] = $rsUser['User_Integral'] - $use_Integral;
    if($data['Integral']['User_Integral']<0){
        exit(json_encode(['errorCode' => 1, 'msg' => '积分不足'], JSON_UNESCAPED_UNICODE));
    }
    $data['total_info']['cash'] = $cash;
	$data['total_info']['total'] -= $cash;
    $data['total_info']['total'] = round($data['total_info']['total'] * 100) / 100;

    // 统计总价
    $data['total_info']['pay_total'] = $data['total_info']['total'] + (empty($data['total_info']['total_shipping_fee']) ? 0 : $data['total_info']['total_shipping_fee']);

	if($data['total_info']['total']<1){
		exit(json_encode(['errorCode' => 1, 'msg' => '抵用之后的价格不能少于1元'], JSON_UNESCAPED_UNICODE));
	}

    // 商家是否支持门店自提
    if(!empty($CartList)){
        foreach ($CartList as $biz_id => $pro_info){
            $biz = $DB->GetRs('biz', 'Biz_ID, Is_BizStores, Biz_Name, Biz_Logo', 'where Users_ID = "' . $UsersID . '" and Biz_ID = ' . $biz_id);
            foreach ($pro_info as $pro_id => $value) {
                foreach ($value as $attr_id => $attr_val) {
                    $data['Is_BizStores'] = $attr_val['Is_BizStores'];
                    if(!empty($biz)){
                        if($biz['Is_BizStores'] == 1 && $attr_val['Is_BizStores'] == 1){
                            $biz_company_dropdown[$biz_id]['Is_BizStores'] = '到店自提';
                        }
                    }
                }
            }
        }
    }

    $data['biz_company_dropdown'] = $biz_company_dropdown;

    //是否需要查询发票开关
    if (isset($_POST['invoice']) && $_POST['invoice'] == 1) {
        //模块开关的开启
        $perm_config = $DB->GetAssoc('permission_config', '*', 'where Users_ID="' . $UsersID . '"  AND Is_Delete = 0 AND Perm_Tyle = 5 order by Permission_ID asc');
        $invoice_switch = 0;
        foreach ($perm_config as $key => $value) {
            if ($value['Perm_Field'] == 'fapiao' && $value['Perm_On'] == 0) {
                $invoice_switch = 1;
            }
        }
        $data['invoice_switch'] = $invoice_switch;
    }
    //$total_price = $total_info['total'];
    //$total_shipping_fee = $total_info['total_shipping_fee'];

    exit(json_encode(['errorCode' => 0, 'msg' => '计算成功', 'data' => $data], JSON_UNESCAPED_UNICODE));

}elseif($action == "getBizProduct"){	//获取购物车里的产品并且进行价格计算
	//获取商城和分销商设置
    $rsConfig = shop_config($UsersID);
    $dis_config = dis_config($UsersID);
    $rsConfig = array_merge($rsConfig,$dis_config);

	$cart_key = $request->input('cart_key', '');
	$AddressID = $request->input('AddressID', 0);	//获取地址
	$source = $request->input('source', 0);	//获取请求来源，0 表示来自于小程序，1 表示来自于公众号请求
	$myRedis = new Myredis($DB, 7);
    $redisCart = $myRedis->getCartListValue($UsersID . $cart_key, $UserID);
    if (!$redisCart) {
        exit(json_encode(['errorCode' => 1, 'msg' => '购物车为空'], JSON_UNESCAPED_UNICODE));
    }
    $CartList = json_decode($redisCart, true);
	$datacartlist = [];
	$user_curagio = get_user_curagio($UsersID, $UserID);
	
	$rsAddress = DB::table('user_address')->where(['Users_ID' => $UsersID, 'User_ID' => $UserID, 'Address_ID' => $AddressID])->first();

	if (!empty($rsAddress) && $rsConfig['NeedShipping'] == 1) {
        $City_Code = $rsAddress['Address_City'];
    } else {
        $City_Code = 0;
    }
	$info = get_order_total_info($UsersID, $CartList, $rsConfig, array(), $City_Code, $user_curagio);

	foreach ($CartList as $Biz_ID => $BizCartList) {		
		$result = getStoreslist($UsersID, $Biz_ID, $UserID, $AddressID, $ak_baidu);
		$datacartlist[$Biz_ID]['store'] = $result;
		//只有公众号才获取产品信息
		if($source){
			$rsBiz = $capsule->table('biz')->where(['Biz_ID' => $Biz_ID, 'is_biz' => 1, 'is_auth' => 2])->first(['Biz_Account', 'Biz_Name', 'Biz_Logo', 'Is_BizStores', 'Biz_Address', 'Biz_PrimaryLng', 'Biz_PrimaryLat', 'Biz_Phone','Is_Union']);
			$shippingName = '';
			$shipping_fee = 0;
			if(!empty($info[$Biz_ID]['Shipping_Name'])){
				$shippingName = $info[$Biz_ID]['Shipping_Name'];
				$shipping_fee = $info[$Biz_ID]['total_shipping_fee'];
			}else if($rsBiz['Is_BizStores'] == 1){
				$shippingName = '到店自提';
				$shipping_fee = 0;
			}else{
				$shippingName = '选择配送方式';
				$shipping_fee = 0;
			}
			
			$rsStores = $capsule->table('stores')->where(['Users_ID' => $UsersID, 'Biz_ID' => $Biz_ID, 'Stores_Status' => 1])->first();
			$thridShippList = $capsule->table('third_shipping')->where(['Users_ID' => $UsersID, 'Status' => 1])->get();
			if (empty($thridShippList) || empty($rsStores)) {
				$datacartlist[$Biz_ID]['hasDada'] = 0;	//没有达达物流
			}else{
				$datacartlist[$Biz_ID]['hasDada'] = 1;
			}
			$datacartlist[$Biz_ID]['isStoresZiti'] = 0;
			if($rsBiz['Is_BizStores'] == 1){
				$datacartlist[$Biz_ID]['isStoresZiti'] = 1;
			}
			$datacartlist[$Biz_ID]['BizID'] = $Biz_ID;
			$datacartlist[$Biz_ID]['thridShippList'] = $thridShippList;
			$datacartlist[$Biz_ID]['shippinginfo'] = get_front_shiping_company_dropdown($UsersID, $info[$Biz_ID]['Biz_Config']);
			$datacartlist[$Biz_ID]['shippingName'] = $shippingName;
			$datacartlist[$Biz_ID]['shipping_fee'] = $shipping_fee;
			$datacartlist[$Biz_ID]['bizinfo'] = [
				'Biz_Logo' => !empty($rsBiz['Biz_Logo']) ? $rsBiz['Biz_Logo'] : '/static/api/shop/skin/default/images/face.jpg',
				'Biz_Name' => !empty($rsBiz['Biz_Name']) ? $rsBiz['Biz_Name'] : (!empty($rsBiz['Biz_Account']) ? $rsBiz['Biz_Account'] : '')
			];
			$productData = [];
			foreach ($BizCartList as $Products_ID => $Product_List) {
				foreach ($Product_List as $attr_id => $product) {
					if (empty($product['Property'])) { //无属性
						$shu_price = $product['ProductsPriceX'];
					} else {    //有属性
						$shu_price = $product['Property']['shu_pricesimp'];
					}
					//计算优惠
					if (!empty($user_curagio)) {
						$shu_price = $shu_price * (1 - $user_curagio / 100);
						$shu_price <= 0.01 ? 0.01 : sprintf("%.2f",substr(sprintf("%.3f", $shu_price), 0, -2));
					}
					$pro = [
						'attr_id' => $attr_id,
						'attr_cart' => [
							'ImgPath' => $product['ImgPath'],
							'ProductsName' => $product['ProductsName'],
							'Productsattrstrval' => $product['Productsattrstrval'],
							'shu_price' => $shu_price,
							'Qty' => $product['Qty'],
						]
					];
					$productData[$Products_ID]['Products_ID'] = $Products_ID;
					$productData[$Products_ID]['goods'] = $pro;
				}
			}
			$datacartlist[$Biz_ID]['cart'] = $productData;
		}
	}
	exit(json_encode(['errorCode' => 0, 'msg' => '获取成功', 'data' => $datacartlist], JSON_UNESCAPED_UNICODE));
}elseif($action == "cart_del"){//从实物购物车中删除产品
	$BizID = $_POST["BizID"];
	$ProductsID = $_POST["ProductsID"];
	$CartID = $_POST["CartID"];
	$Data = array();
	$Data["status"] = 1;
	$cart_key = $UsersID.'CartList';
    $CartList = json_decode($myRedis->getCartListValue($cart_key, $UserID), true);
	unset($CartList[$BizID][$ProductsID][$CartID]);
	if(count($CartList[$BizID][$ProductsID])==0){//购物车中不存在该产品的存储，释放
		unset($CartList[$BizID][$ProductsID]);
	}
	if(count($CartList[$BizID])==0){//购物车中不存在该商家的存储，释放
		unset($CartList[$BizID]);
		$Data["status"] = 2;
	}
	
	if(count($CartList)==0){//购物车中已无商品，释放
        $myRedis->clearCartListValue($cart_key, $UserID);
		$Data["status"] = 3;
		$Data["total"] = 0;
	}else{
        $myRedis->setCartListValue($cart_key, $UserID, json_encode($CartList, JSON_UNESCAPED_UNICODE));
		$total=0;
		foreach($CartList as $bizid=>$bizcart){
			foreach($bizcart as $productsid=>$products){
				foreach($products as $carid=>$cart){
                    if (count($cart["Property"]) > 0) {
                        $price = $cart["Property"]['shu_pricesimp'];
                    }else{
                        $price = $cart["ProductsPriceX"];
                    }
                    if (isset($cart['user_curagio']) && !empty($cart['user_curagio'])) {
                        $price = $price * (1 - $cart['user_curagio'] / 100);
                    }
                    $total += $cart["Qty"] * $price;
				}				
			}
		}
		$Data["total"] = $total;
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	
}elseif($action == "cart_update"){//更新实物购物车
	$BizID = $_POST["BizID"];
	$ProductsID = $_POST["ProductsID"];
	$CartID = $_POST["CartID"];
	$Type = $_POST["Type"];
	$Qty = $_POST["Qty"];
    $cart_key = $UsersID.'CartList';
    $redisCart = $myRedis->getCartListValue($cart_key, $UserID);
	if(empty($redisCart)){
		$Data=array(
			"status"=>0,
			"msg"=>"购物车空的，赶快去逛逛吧！"
		);
	    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
	    exit;
	}
    $CartList = $redisCart ? json_decode($redisCart,true) : array();
	$total = 0;
	$rsProducts=$DB->GetRs("shop_products","Products_Count","where Users_ID='".$UsersID."' and Biz_ID = " . $BizID . " and Products_ID=".$ProductsID);
	if(empty($CartList[$BizID][$ProductsID][$CartID]) || empty($rsProducts)){
		foreach($CartList as $bizid=>$bizcart){
			foreach($bizcart as $productsid=>$products){
				foreach($products as $carid=>$cart){
                    if (count($cart["Property"]) > 0) {
                        $price = $cart["Property"]['shu_pricesimp'];
                    }else{
                        $price = $cart["ProductsPriceX"];
                    }
                    if (isset($cart['user_curagio']) && !empty($cart['user_curagio'])) {
                        $price = $price * (1 - $cart['user_curagio'] / 100);
                    }
                    $total += $cart["Qty"] * $price;
				}				
			}
		}
		echo json_encode(array("status"=>0,"msg"=>"该商品不存在","total"=>$total),JSON_UNESCAPED_UNICODE);
		exit;
	}
	$Data = array();
	switch($Type){
		case 'qty_sub'://减少
			if($Qty<=1){
				$Data["status"] = 1;
				$Data["qty"] = 1;
				$Data["msg"] = '最小购买数量为1！';
				$CartList[$BizID][$ProductsID][$CartID]["Qty"] = 1;
			}else{
				$CartList[$BizID][$ProductsID][$CartID]["Qty"] = $Qty-1;
				$Data["status"] = 2;
				$Data["qty"] = $Qty-1;
				$Data["msg"] = '';
			}
		break;
		case 'qty_add'://增加
			if($Qty>=$rsProducts["Products_Count"] && $rsProducts["Products_Count"]>0){
				$Data["status"] = 1;
				$Data["qty"] = $rsProducts["Products_Count"];
				$Data["msg"] = '产品库存不足，最多能购买'.$rsProducts["Products_Count"].'件';
				$CartList[$BizID][$ProductsID][$CartID]["Qty"] = $rsProducts["Products_Count"];
			}else{
				$CartList[$BizID][$ProductsID][$CartID]["Qty"] = $Qty+1;
				$Data["status"] = 2;
				$Data["qty"] = $Qty+1;
				$Data["msg"] = '';
			}
		break;
		case 'change':
			if($Qty<1){
				$Data["status"] = 1;
				$Data["qty"] = 1;
				$Data["msg"] = '最小购买数量为1！';
				$CartList[$BizID][$ProductsID][$CartID]["Qty"] = 1;
			}elseif($Qty>$rsProducts["Products_Count"] && $rsProducts["Products_Count"]>0){
				$Data["status"] = 1;
				$Data["qty"] = $rsProducts["Products_Count"];
				$Data["msg"] = '产品库存不足，最多能购买'.$rsProducts["Products_Count"].'件';
				$CartList[$BizID][$ProductsID][$CartID]["Qty"] = $rsProducts["Products_Count"];
			}else{
				$CartList[$BizID][$ProductsID][$CartID]["Qty"] = $Qty;
				$Data["status"] = 2;
				$Data["qty"] = $Qty;
				$Data["msg"] = '';
			}
		break;
	}
	
	foreach($CartList as $bizid=>$bizcart){
		foreach($bizcart as $productsid=>$products){
			foreach($products as $carid=>$cart){
				if (count($cart["Property"]) > 0) {
				    $price = $cart["Property"]['shu_pricesimp'];
				}else{
                    $price = $cart["ProductsPriceX"];
				}
				if (isset($cart['user_curagio']) && !empty($cart['user_curagio'])) {
                    $price = $price * (1 - $cart['user_curagio'] / 100);
                }
                $total += $cart["Qty"] * $price;
			}				
		}
	}
	
	$Data["total"] = $total;
    $myRedis->setCartListValue($cart_key, $UserID, json_encode($CartList, JSON_UNESCAPED_UNICODE));
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	
}elseif($action=="favourite"){//收藏商品
	//检测用户是否登陆
	if(empty($UserID)){
		$_SESSION[$UsersID."HTTP_REFERER"]='http://'.$_SERVER['HTTP_HOST']."/api/".$UsersID."/shop/products/".$_POST['productId'].'/?wxref=mp.weixin.qq.com';
		$url = 'http://'.$_SERVER['HTTP_HOST']."/api/".$UsersID."/user/login/";
		/*返回值*/
		$Data=array(
			"status"=>0,
			"info"=>"您还为登陆，请登陆！",
			"url"=>$url
		);
		
	}else{

		$insertInfo = array('User_ID'=>$UserID,
							'Products_ID'=>$_POST['productId'],
							'MID'=>'shop',
							'IS_Attention'=>1);
		
		$Result=$DB->Add("user_favourite_products",$insertInfo);
		
			$Data=array(
			"status"=>1,
			"info"=>"收藏成功！",
			
			);
	}

	echo json_encode($Data,JSON_UNESCAPED_UNICODE);	
	
}elseif($action == "cancel_favourite"){//删除收藏
	//检测用户是否登陆
	if(empty($UserID)){
		$_SESSION[$UsersID."HTTP_REFERER"]='http://'.$_SERVER['HTTP_HOST']."/api/".$UsersID."/shop/products/".$_POST['productId'].'/?wxref=mp.weixin.qq.com';
		$url = 'http://'.$_SERVER['HTTP_HOST']."/api/".$UsersID."/user/login/";
		/*返回值*/
		$Data=array(
			"status"=>0,
			"info"=>"您还为登陆，请登陆！",
			"url"=>$url
		);
		
	}else{

		$insertInfo = array('User_ID'=>$UserID,
							'Products_ID'=>$_POST['productId'],
							'IS_Attention'=>1);
		
		$DB->Del("user_favourite_products","User_ID='".$UserID."' and Products_ID=".$_POST['productId']);
		
		$Data=array(
			"status"=>1,
			"info"=>"取消收藏成功！",	
			);
	}

	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	
}elseif($action=="cart_check"){//实物购物车检测
    $redisCart = $myRedis->getCartListValue($UsersID.'CartList', $UserID);
    $CartList = json_decode($redisCart, true);

	if(count($CartList)>0){
		$Data=array(
			"status"=>1,
			"msg"=>""
		);
	}else{
		$Data=array(
			"status"=>0,
			"msg"=>"购物车空的，赶快去逛逛吧！"
		);
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	
}elseif($action == "checkout"){ //订单提交（所有类型订单提交都用这个方法）
	$cartkey = $request->input('cart_key', '');
	$cart_key = $UsersID . $cartkey;
	$carturl = "/api/".$UsersID."/shop/cart/";
    $redisCart = $myRedis->getCartListValue($cart_key, $UserID);
	if(empty($redisCart)){
		$tip = array(
			"status" => 2,
			"url" => $carturl,
			"msg" => "购物车空的，赶快去逛逛吧！"
		);
	    echo json_encode($tip, JSON_UNESCAPED_UNICODE);exit;
	}
	$Data = array (
		"Users_ID" => $UsersID,
		"User_ID" => $UserID
	);
	$user_curagio = $cartkey == 'PTCartList' ? 0 : get_user_curagio($UsersID, $UserID);
    $CartList = json_decode($redisCart,true);
    $is_virtual = 0;
	$StoreListID = [];
	$post = $request->input();
	$Stores = $request->input('Store', []);
	$Stores = is_array($Stores) ? $Stores : json_decode($Stores, true);
    foreach($CartList as $bizid => $bizcart){
		$StoreListID[$bizid] = !empty($Stores[$bizid]) ? $Stores[$bizid] : 0;
        foreach($bizcart as $pro_id => $value){
            foreach($value as $attr_id => $val){
                $rsProducts = $DB->GetRs('shop_products', 'Users_ID, Products_IsVirtual, Products_IsRecieve', 'where Products_ID = ' . $pro_id);
                if ($cart_key == $UsersID . 'PTCartList') {   //只有一件产品
                    if ($val['pintuan_flag'] != 1 || $val['pintuan_people'] < 2 || $val['pintuan_start_time'] > time() || $val['pintuan_end_time'] < time() || $val['pintuan_pricex'] <= 0) {
                        $tip = [
                            "status" => 0,
                            "msg" => "产品不符合拼团条件"
                        ];
                        echo json_encode($tip, JSON_UNESCAPED_UNICODE);
                        exit;
                    }

                    $ProductsID = $pro_id;   //产品id  //拼团产品只有一件
                    $pintuan_end_time = $val['pintuan_end_time'];   //拼团结束时间

                    $is_virtual = $rsProducts['Products_IsVirtual'];   //产品类型  0：虚拟产品  1：实物产品
                }
            }
        }
    }
	$AddressID = $request->input('AddressID', 0);
	if ($cart_key == $UsersID . 'CartList' || $cart_key == $UsersID . 'DirectBuy' || ($cart_key == $UsersID . 'PTCartList' && $is_virtual == 0)) {
		
		$Need_Shipping = $_POST ['Need_Shipping'];
		if ($Need_Shipping == 1) {
			if (! empty ( $AddressID )) {
				$rsAddress = $DB->GetRs ( "user_address", "*", "where Users_ID='" . $UsersID . "' and User_ID='" . $UserID . "' and Address_ID='" . $AddressID . "'" );
				
				$Data ["Address_Name"] = $rsAddress ['Address_Name'];
				$Data ["Address_Mobile"] = $rsAddress ["Address_Mobile"];
				$Data ["Address_Province"] = $rsAddress ["Address_Province"];
				$Data ["Address_City"] = $rsAddress ["Address_City"];
				$Data ["Address_Area"] = $rsAddress ["Address_Area"];
				$Data ["Address_Detailed"] = $rsAddress ["Address_Detailed"];
			}
		}
		if(isset($_POST['Shiping_ID_'.$bizid]) && $_POST['Shiping_ID_'.$bizid] != 'Is_BizStores'){
            $Data ["Order_IsVirtual"] = 0;
        }else{
            $Data ["Order_IsVirtual"] = 1;
        }

	} else {
		$Data ["Order_IsVirtual"] = 1;
		$Data ["Order_IsRecieve"] = $_POST ["recieve"];
		$Data ["Address_Mobile"] = $_POST ["Mobile"];
	}
    $Data["Order_Type"] = ($_POST['cart_key'] == 'PTCartList') ? 'pintuan' : "shop";
	$rsConfig = $DB->GetRs("shop_config","*","where Users_ID='".$UsersID."'");
	//分销相关设置
	$dis_config = dis_config($UsersID);
	//合并参数
	$rsConfig = array_merge($rsConfig,$dis_config);
	$owner = get_owner($rsConfig, $UsersID);
	$Data['Owner_ID'] = $owner['id'];

	//是否加入分销记录
	//$ds_account = get_dsaccount_by_id($DB,$UsersID,$UserID);
	$is_distribute = true;
	$error = false;

	$biznum = count($CartList);	
	
	//获取每个供货商的运费默认配置
    $Biz_ID_String = implode(',',array_keys($CartList));

	$condition = "where Users_ID  = '".$UsersID."' and Biz_ID in (".$Biz_ID_String.")";
	$rsBizConfigs = $DB->get('biz','Biz_ID,Biz_Name,Shipping,Default_Shipping,Default_Business',$condition);
	$Biz_Config_List = $DB->toArray($rsBizConfigs);
 
	$Biz_Config_Dropdown = array();

	foreach($Biz_Config_List as $key=>$item){
		$Biz_Config_Dropdown[$item['Biz_ID']] = $item;
	}

	//生成订单
	$orderids = array();
	$pre_total = 0;
	$pre_sn = build_pre_order_no();
	$pre_sn = 'PRE'.$pre_sn;
	Dis_Record::observe(new DisRecordObserver());//佣金记录初始化
	$neworderid = 0;
	$catloop = count($CartList);
	$City_Code = isset($Data["Address_City"]) ? $Data["Address_City"] : 0;
	$Shipping_IDsstr = $request->input('Shipping_arr', []);
	if ($Data["Order_IsVirtual"] == 1) {
        $Shipping_IDs = -1;
	} else {
        $Shipping_IDs = [];
        if (!empty($Shipping_IDsstr)) {
            $Shipping_IDsarr = is_array($Shipping_IDsstr) || empty($Shipping_IDsstr) ? $Shipping_IDsstr : json_decode($Shipping_IDsstr,true);
            $Shipping_IDs = !empty($Shipping_IDsarr) ? organize_shipping_id_new($Shipping_IDsarr) : [];
        }
    }
	$total_info = get_order_total_info($UsersID, $CartList, $rsConfig, $Shipping_IDs, $City_Code, $user_curagio, ['UserID' => $UserID, 'StoreListID' => $StoreListID, 'AddressID' => $AddressID]);
	
	foreach($CartList as $Biz_ID => $BizCart){
		$Data["Biz_ID"] = $Biz_ID;
		$Data["Order_Remark"] = !empty($_POST["Remark"][$Biz_ID]) ? $_POST["Remark"][$Biz_ID] : '';
		$Shipping_Mehtod = $request->input('Shiping_ID_' . $Biz_ID, '');
		$shipping_fee = 0;
        //不是达达物流的情况
		if($Shipping_Mehtod != 'dada'){
			//整理快递信息
			if ($cart_key == $UsersID . 'CartList' || $cart_key == $UsersID . 'DirectBuy' || ($cart_key == $UsersID . 'PTCartList' && $is_virtual == 0 || (isset($_POST['Shiping_ID_'.$Biz_ID]) && $_POST['Shiping_ID_'.$Biz_ID] != 'Is_BizStores'))){
				$biz_company_dropdown = get_front_shiping_company_dropdown($UsersID,$Biz_Config_Dropdown[$Biz_ID]);
				if(count($biz_company_dropdown) >0 ){
					$express = !empty($biz_company_dropdown[$_POST['Shiping_ID_'.$Biz_ID]]) ? $biz_company_dropdown[$_POST['Shiping_ID_'.$Biz_ID]] : '';
					if (!empty($total_info)) {
						$shipping_fee = $total_info[$Biz_ID]['total_shipping_fee'];
					} else {
						$shipping_fee = 0;
					}
					$shipping = array('Express'=>$express,'Price'=>$shipping_fee);
				}else{
					$shipping = array();
				}
			}else{
				$shipping = array('Express'=>'','Price'=>0);
			}
			$Data["Order_Shipping"] = json_encode($shipping,JSON_UNESCAPED_UNICODE);	
		}else{
			$StoreID = !empty($StoreListID[$Biz_ID]) ? $StoreListID[$Biz_ID] : 0;
			$rsStores = DB::table('stores')->where(['Users_ID' => $UsersID, 'Stores_ID' => $StoreID, 'Stores_Status' => 1])->first();
			if(empty($rsStores)){
				die(json_encode([
						"status" => 0,
                        "msg" => "请选择门店"
				], JSON_UNESCAPED_UNICODE));
			}
			$Data['Order_StoresID'] = $rsStores['Stores_ID'];
			$shipping_fee = !empty($total_info[$Biz_ID]['total_shipping_fee']) ? $total_info[$Biz_ID]['total_shipping_fee'] : 0;
			if(empty($shipping_fee)){
				die(json_encode([
						"status" => 0,
                        "msg" => "该配送方式暂时不可用"
				], JSON_UNESCAPED_UNICODE));
			}
			$rsShipping = DB::table('third_shipping')->where(['Users_ID' => $UsersID, 'Tag' => $Shipping_Mehtod])->first();
			$Data["Order_Shipping"] = json_encode([
					'Express'=>$rsShipping['Name'], 
					'Price'=>$shipping_fee,
					'Tag' => $Shipping_Mehtod
				],JSON_UNESCAPED_UNICODE);
		}
		$total_price = 0;
		$All_Qty = 0;
		foreach($BizCart as $Product_ID => $Product_List){
			foreach($Product_List as $k => $v){
				$All_Qty += $v["Qty"];
				if(!empty($v['Property'])){
					//修改产品属性的库存
					$rsProduct = $DB->GetRs("shop_products","skuvaljosn","where Users_ID='".$UsersID."' and Products_ID=".$Product_ID);
					//产品属性详情
					$rsProduct['skuvaljosn'] = json_decode($rsProduct['skuvaljosn'],true);
					//获得加入购买产品的属性skuID 如：[1;4;5]
					$atrid_str = $k;
					//减去购买件数，剩余多少库存
					foreach($rsProduct['skuvaljosn'] as $key=>$value){
						if($atrid_str == $key){
							if ($value['Txt_CountSon'] < $v["Qty"]) {
								$tip = array(
									"status" => 0,
									"msg" => "库存不够！该产品只有".$value['Txt_CountSon']."件"
								);
								echo json_encode($tip, JSON_UNESCAPED_UNICODE);exit;
							}
							$rsProduct['skuvaljosn'][$key]['Txt_CountSon'] -= $v["Qty"];
							if ($rsProduct['skuvaljosn'][$key]['Txt_CountSon'] < 0) {
								$rsProduct['skuvaljosn'][$key]['Txt_CountSon'] = 0;
							}
						}
					}
					$skuvaljosn = json_encode($rsProduct['skuvaljosn'],JSON_UNESCAPED_UNICODE);
					//更新产品表属性JSON数据
					$Pro_Data = array('skuvaljosn'=>$skuvaljosn);
					$DB->Set("shop_products",$Pro_Data,"where Users_ID='".$UsersID."' and Products_ID=".$Product_ID);
				}

				if (count($v["Property"]) > 0) {
				    if ($cart_key == $UsersID . 'PTCartList') {
                        $total_price += !empty($v["Property"]["shu_pricesimp"]) ? $v["Qty"] * $v["Property"]["shu_pricesimp"] : $v["Qty"] * $v["Property"]["pintuan_pricex"] ;
                    } else {
                        $total_price += $v["Qty"] * $v["Property"]["shu_pricesimp"] ;
                    }

				}else{
					$total_price += $v["Qty"] * $v["ProductsPriceX"];	
				}
			}
		}

		$toal_price_ago = $total_price;
		$total_price = $total_info[$Biz_ID]['total'];
		if($Shipping_Mehtod !== 'dada'){
			if ($Data ["Order_IsVirtual"] == 1 || (isset($_POST['Shiping_ID_'.$Biz_ID]) && $_POST['Shiping_ID_'.$Biz_ID] == "Is_BizStores")) {
				$Data["Order_TotalAmount"] = $toal_price_ago;
			} else {
				$Data["Order_TotalAmount"] = $toal_price_ago + $total_info[$Biz_ID]["total_shipping_fee"];
			}
		} else {
            $Data["Order_TotalAmount"] = $toal_price_ago;
        }
		$Data["Order_NeedInvoice"] = isset($_POST['Order_NeedInvoice'][$Biz_ID]) ? $_POST['Order_NeedInvoice'][$Biz_ID] : 0;
		$Data["Order_InvoiceInfo"] = isset($_POST['Order_NeedInvoice'][$Biz_ID]) && isset($_POST['Order_InvoiceInfo'][$Biz_ID]) ? $_POST['Order_InvoiceInfo'][$Biz_ID] : "";
	    //优惠券使用判断
		$cash = 0;
	    if(isset($_POST["CouponID"][$Biz_ID]) && !empty($_POST["CouponID"][$Biz_ID])){//优惠券使用判断
			$CouponID = $_POST["CouponID"][$Biz_ID];
			$r = $DB->GetRs("user_coupon_record","*","where Coupon_ID=".$CouponID." and Users_ID='".$UsersID."' and User_ID=".$UserID." and Coupon_UseArea=1 and (Coupon_UsedTimes=-1 or Coupon_UsedTimes>0) and Coupon_StartTime<=".time()." and Coupon_EndTime>=".time()." and Coupon_Condition<=".$total_price." and Biz_ID=".$Biz_ID);
			if($r){
				$Data["Coupon_ID"] = $CouponID;
				if($r["Coupon_UseType"]==0 && $r["Coupon_Discount"]>0 && $r["Coupon_Discount"]<1){
					$cash = $total_price * (1 - $r["Coupon_Discount"]);
					$cash = number_format($cash, 2, '.', '');
					$Data["Coupon_Discount"] = $r["Coupon_Discount"];					
				}
				if($r['Coupon_UseType'] == 1 && $r['Coupon_Cash'] > 0){
					$cash = $r['Coupon_Cash'];					
				}
	            if($total_price < 1){
                    $tip = array(
                        "status" => 0,
                        "msg" => "使用优惠券抵现后的金额不能少于1元！"
                    );
                    echo json_encode($tip, JSON_UNESCAPED_UNICODE);exit;
                }
				$Data["Coupon_Cash"] = $cash;
				$Logs_Price = $cash;
				
				if($r['Coupon_UsedTimes'] >= 1){
					$DB->Set("user_coupon_record","Coupon_UsedTimes=Coupon_UsedTimes-1","where Users_ID='".$UsersID."' and User_ID='".$UserID."' and Coupon_ID=".$_POST['CouponID'][$Biz_ID]);
				}
				$item = $DB->GetRs("user","User_Name","where User_ID=".$UserID);
				$Data1 = array(
					'Users_ID'=>$UsersID,
					'User_ID'=>$UserID,
					'User_Name'=>$item["User_Name"],
					'Coupon_Subject'=>"优惠券编号：".$CouponID,
					'Logs_Price'=>$Logs_Price,
					'Coupon_UsedTimes'=>$r['Coupon_UsedTimes']>=1?$r['Coupon_UsedTimes']-1:$r['Coupon_UsedTimes'],
					'Logs_CreateTime'=>time(),
					"Biz_ID"=>$Biz_ID,
					'Operator_UserName' => "微商城购买商品"
				);
				$DB->Add("user_coupon_logs", $Data1);
			}else{
				$Data["Coupon_ID"] = 0;
				$Data["Coupon_Discount"] = 0;
				$Data["Coupon_Cash"] = 0;
			}
		} else {
			$Data["Coupon_ID"] = 0;
			$Data["Coupon_Discount"] = 0;
			$Data["Coupon_Cash"] = 0;
		}

		// 获得产品的ID 计算积分	
		$Integration = 0;
		foreach($CartList[$Biz_ID] as $key=>$products){
			foreach($products as $k=>$v){
			    if (isset($v['Products_Integration'])) {
                    $CartList[$Biz_ID][$key][$k]['ProductsIntegration'] = $v['Products_Integration'];
                    $Integration += $v['Qty'] * $v['Products_Integration'];
                }

			}
		}
		//积分抵用的具体金额
		$diyong_money = 0;
		$offset = I('Offset_arr', []);

		if(!empty($offset)){
			$offset = is_array($offset) ? $offset : json_decode($offset, true);
			if(isset($offset[$Biz_ID]) && $offset[$Biz_ID]==1){
				$diyong_intergral = 0;
				 $rsUser = DB::table('user')->where(['Users_ID' => $UsersID, 'User_ID' => $UserID])->first(['User_Integral']);
				  $diyong_intergral = Integration_get($Biz_ID,$rsConfig,$rsUser['User_Integral'],$total_price);

				$diyong_money = $diyong_intergral/$rsConfig['Integral_Buy'];
				$Data['Integral_Consumption'] = $diyong_intergral;
				$Data['Integral_Money'] = $diyong_money;
			}else{
				$Data['Integral_Consumption'] = 0;
				$Data['Integral_Money'] = 0;
			}
		}else{
			$Data['Integral_Consumption'] = 0;
			$Data['Integral_Money'] = 0;
		}
		$Data['Integral_Get'] = $Integration;
		$curagio_money = 0;
        // 先减去会员专享折扣
        $curagio_money = $toal_price_ago - $total_price;
		
		$total_price -= $cash;
		$total_price -= $diyong_money;
		if($total_price < 0.01){
            $total_price = 0.01;
        }
		$total_price = $total_price <= 0.01 ? 0.01 : sprintf("%.2f",substr(sprintf("%.3f", $total_price), 0, -1));

		$Data['Integral_Get'] = $Integration;
		$Data['curagio_money'] = $curagio_money;
		$Data['All_Qty'] = $All_Qty;
		require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/balance.class.php');
		$balance = new balance($DB, $UsersID);
		$Web_Price = $balance->repeat_list($BizCart, $diyong_money, $curagio_money, $cash, $catloop);
		$Data['Web_Price'] = $Web_Price['web_prie'];
		$Data["Order_CartList"]= json_encode($Web_Price['CartListupdate'],JSON_UNESCAPED_UNICODE);
		$BizCart = $Web_Price['CartListupdate'];
		$Data['Web_Pricejs'] = $Web_Price['web_priejs'];
		$Data['muilti'] = $Web_Price['muilti'];

		if(!empty($Shipping_Mehtod)){
			$Data["Order_TotalPrice"] = $total_price + $shipping_fee;
		}else{
			$Data["Order_TotalPrice"] = $total_price + (isset($total_info[$Biz_ID]['total_shipping_fee']) ? $total_info[$Biz_ID]['total_shipping_fee'] : 0);
		}
		if($Data["Order_TotalPrice"]<1){
			$tip = array(
				"status" => 0,
				"msg" => "订单总价不能少于1元！"
			);
			echo json_encode($tip, JSON_UNESCAPED_UNICODE);exit;
		}
		$pre_total += $Data["Order_TotalPrice"];
		$Data["Order_CreateTime"] = time();
		$Data["Order_Status"] = $rsConfig["CheckOrder"] == 1 ? 1 : 0;
		$neworderid = DB::table('user_order')->insertGetId($Data);
		if($neworderid){
			$myCartList = get_filter_cart_list($DB, $CartList, [
				'Integral_Money' => $diyong_money,
				'Coupon_Cash' => $cash
			]);
			$orderids[] = $neworderid;
			//更新销售记录
			foreach($BizCart as $kk=>$vv){
				$qty = 0;
				foreach($vv as $k=>$v){
					$qty += $v['Qty'];
					$vvk = $k;
					//加入分销记录
					if($v['OwnerID']>0){
						if (count($v['Property']) > 0) {
							$vprice = $v['Property']['shu_pricesimp'];						
						}else{
							$vprice = $v['ProductsPriceX'];						
						}
						$Profit = isset($myCartList[$Biz_ID][$kk][$k]["ProductsProfit"]) && $myCartList[$Biz_ID][$kk][$k]["ProductsProfit"] ? $myCartList[$Biz_ID][$kk][$k]["ProductsProfit"] : 0;
						$Profit = $Profit * (1 - $user_curagio / 100);
						$vv[$k]['Products_Profit'] = $Profit;
						add_distribute_record($UsersID,$v['OwnerID'],$vprice,$kk,$v['Qty'],$neworderid,$Profit,$k);
					}else{
						$Profit = isset($myCartList[$Biz_ID][$kk][$k]["ProductsProfit"]) && $myCartList[$Biz_ID][$kk][$k]["ProductsProfit"] ? $myCartList[$Biz_ID][$kk][$k]["ProductsProfit"] : 0;
						$Profit = $Profit * (1 - $user_curagio / 100);
						$vv[$k]['Products_Profit'] = $Profit; 
					}
					$BizCart[$kk][$k]['ProductsProfit'] = $Profit;
				$fields = ['Products_ID','salesman_ratio','salesman_level_ratio','commission_ratio','Biz_ID','Users_ID','Products_PriceX','platForm_Income_Reward'];
				$product2 = Product::Multiwhere(array('Users_ID' => $UsersID, 'Products_ID' => $kk))->first($fields)->toArray();
				$product = array_merge($vv,$product2);				
				$comm = new Salesman_commission();
				$salesman = $comm->handout_salesman_commission($neworderid,$product,$qty,$kk,$k);
				}
			}
			DB::table('user_order')->where(['Order_ID' => $neworderid])->update(['Order_CartList'=> json_encode($BizCart,JSON_UNESCAPED_UNICODE)]);
		}else{
			$error = true;
		}		
	}
    if ($cart_key == $UsersID . 'PTCartList') {
	    $msg_url = 'http://' . $_SERVER["HTTP_HOST"] . '/api/' . $UsersID . '/pintuan/orderdetails/' . $neworderid . '/';
    } else {
        $msg_url = 'http://' . $_SERVER["HTTP_HOST"] . '/api/' . $UsersID . '/shop/member/detail/' . $neworderid . '/';
    }
	require_once(CMS_ROOT.'/include/library/weixin_message.class.php');
    $weixin_message = new weixin_message($DB,$UsersID,$UserID);
    $contentStr = '您已成功提交微商城订单，<a href="' . $msg_url . '">查看详情</a>';
    $weixin_message->sendscorenotice($contentStr);

    $myRedis->clearCartListValue($cart_key, $UserID);
	
	if($error){
		$Data=array(
			"status"=>0,
            'msg' => '订单提交失败'
		);
	}else{
		if($rsConfig["CheckOrder"]==1){
			$Data = array(
				"usersid"=>$UsersID,
				"userid"=>$UserID,
				"pre_sn"=>$pre_sn,
				"orderids"=>implode(",",$orderids),
				"total"=>$pre_total,
				"createtime"=>time(),
				"status"=>1
			);
			$flag = $DB->Add("user_pre_order",$Data);
			if($flag){
				if ($biznum > 1) {
				    $url = "/api/".$UsersID."/shop/cart/payment/".$pre_sn."/?love";
				} else {
				    $url = "/api/".$UsersID."/shop/cart/payment/".$neworderid."/?love";
				}
                
				$Data = array(
					"url" => $url,
					"status" => 1,
                    'msg' => '订单提交成功',
                    "Order_ID" => $biznum > 1 ? $pre_sn : $neworderid
				);
			}else{
				$Data = array(
					"msg" => "订单提交失败",
					"status" => 0
				);
			}
		}else{
            if ($cart_key == $UsersID . 'PTCartList') {
                $jump_url = "/api/" . $UsersID . "/pintuan/orderlist/" . $Data["Order_Status"] . '/';
            } else {
                $jump_url = "/api/" . $UsersID . "/shop/member/status/" . $Data["Order_Status"] . '/?love';
            }
			$Data=array(
				"url" => $jump_url,
				"status"=>1,
                'msg' => '订单提交成功',
                "Order_ID" => $biznum > 1 ? $pre_sn : $neworderid
			);
		}
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
	
}elseif($action=="payment"){
	$OrderID = empty($_POST['OrderID']) ? 0 : $_POST['OrderID'];
	$Yuebc_price = !empty($_POST['Yuebc_price']) ? (float)$_POST['Yuebc_price'] : 0;
	$orderids = '';
	if(strpos($OrderID,"PRE") !== false){
		$pre_order = $DB->GetRs("user_pre_order","*","where pre_sn='".$OrderID."' and usersid='".$UsersID."' and userid=".$UserID);
		$orderids = $pre_order["orderids"];
		$order_status = $pre_order["status"];
		$order_total = $pre_order["total"];
		$Order_Yebc = empty($pre_order['Order_Yebc']) ? 0 : $pre_order['Order_Yebc'];
		$order_type = '';
	}else{
		$orderids = $OrderID;
		$rsOrder = $DB->GetRs ( "user_order", "*", "where Users_ID='" . $UsersID . "' and Order_ID='" . $OrderID . "'" );
		$order_status = $rsOrder["Order_Status"];
		$order_total = $rsOrder["Order_TotalPrice"];
        $Order_Yebc = empty($rsOrder['Order_Yebc']) ? 0 : $rsOrder['Order_Yebc'];
        $order_type = $rsOrder["Order_Type"];
	}
	$rsUser = $DB->GetRs("user","User_Money,User_PayPassword,Is_Distribute,User_Name,User_NickName,Owner_Id,User_Integral","where Users_ID='".$UsersID."' and User_ID=".$UserID);
	
	$PaymentMethod = array(
        "余额支付" => "0",
		"微支付" => "1",
		"支付宝" => "2",
		"线下支付" => "3",
		"易宝支付" => "4",
	);
	
	if (empty($_POST['PaymentMethod'])) {
		$Data=array(
				"status"=>0,
				"msg"=>'请选择支付方式！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);exit;
	}
	
	if ($order_total == $Yuebc_price && $_POST['PaymentMethod'] != "余额支付") {
		$Data=array(
				"status"=>0,
				"msg"=>'支付金额不能为零！'
			);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);exit;
	}
	$Flagbc = true;
	if (!empty($Yuebc_price) && strpos($OrderID,"PRE") === false) {
		$Data = array(
				"Order_Fyepay" => bcsub($order_total, $Yuebc_price, 2),			
				"Order_Yebc" => $Yuebc_price
				);
		
		$Flagbc = $DB->Set ( "user_order", $Data, "where Users_ID='" . $UsersID . "' and Order_ID=" . $OrderID);
	}
	
	if (!$Flagbc) {
		$Data=array(
				"status"=>0,
				"msg"=>'补差支付失败！'
			);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);exit;
	}
	
	//$_POST['yuepay0524']   该变量只有在当选择线下及余额支付提交时才有，这样当提交过来就可以转到相应的支付页面了。
	if ($_POST['PaymentMethod'] == "余额支付" && !empty($_POST['yuepay0524'])) {
		$Data=array(
				"status"=>2,
				"url"=>'/api/'.$UsersID.'/shop/cart/complete_pay/money/'.$OrderID.'/'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);exit;
	}
	
	if ($_POST['PaymentMethod'] == "线下支付" && !empty($_POST['yuepay0524'])) {
		$Data=array(
				"status"=>2,
				"url"=>'/api/'.$UsersID.'/shop/cart/complete_pay/huodao/'.$OrderID.'/'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);exit;
	}


	if($_POST['PaymentMethod'] == "线下支付" || $order_total <= 0 || $_POST['PaymentMethod']=="余额充值线下支付"){
		$PaymentInfo = json_encode($_POST['PaymentInfo'],JSON_UNESCAPED_UNICODE);
		if($_POST['Paymethod']=='huodao'){
			$Data = array(
			"Order_PaymentMethod"=>$_POST['PaymentMethod'],
			"Order_PaymentInfo"=>$PaymentInfo,
			//"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"],
			"Order_Status"=>1
			);
			
		}
		if($_POST['Paymethod']=='huodaocharge'){
			$Data = array(
			"Order_PaymentMethod"=>$_POST['PaymentMethod'],
			"Order_PaymentInfo"=>$PaymentInfo,
			//"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"],
			"Order_Status"=>31
			);
			
		}
		
		$Status = 1;
		$Flag = $DB->Set ( "user_order", $Data, "where Users_ID='" . $UsersID . "' and User_ID='" . $UserID . "' and Order_ID in(".$orderids.")");
		$url = "/api/" . $UsersID . "/shop/member/status/" . $Status . "/";
		if($Flag){
			$Data=array(
				"status"=>1,
				"url"=>$url,
				"msg"=>'线下支付提交成功'
			);
		}else{
			$Data=array(
				"status"=>0,
				"msg"=>'线下支付提交失败'
			);
		}		
		
	}elseif($_POST['PaymentMethod'] == "余额支付"){//余额支付
		if($rsUser["User_Money"] < ($order_total - $Order_Yebc)){
            die(json_encode([
                "status"=>0,
                "msg"=>'余额不足'
            ],JSON_UNESCAPED_UNICODE));
        }
		//增加资金流水
		if($order_status != 1){
			$Data=array(
				"status"=>0,
				"msg"=>'该订单状态不是待付款状态，不能付款'
			);
		}elseif(!$_POST["PayPassword"]){
			$Data = array(
				"status"=>0,
				"msg"=>'请输入支付密码'
			);
			
		}elseif(md5($_POST["PayPassword"]) != $rsUser["User_PayPassword"]){
			$Data = array(
				"status"=>0,
				"msg"=>'支付密码输入错误'
			);
		}else{
			if($order_type!='pintuan'){ // 因拼团中已做操作
				$Data = array(
					'Users_ID' => $UsersID,
					'User_ID' => $UserID,
					'Type' => 0,
					'Amount' => $order_total - $Order_Yebc,
					'Total' => $rsUser ['User_Money'] - ($order_total - $Order_Yebc),
					'Note' => ($order_type == 'pintuan' ? '  拼团' : '  商城') . "购买支出 -" . ($order_total - $Order_Yebc) . " (订单号:" . $orderids . ")",
					'CreateTime' => time()
				);
				$Flag = $DB->Add('user_money_record', $Data);

                //更新用户余额
                $Data = array(
                    'User_Money'=>$rsUser['User_Money'] - ($order_total - $Order_Yebc)
                );
                $Flag = $DB->Set('user',$Data,"where Users_ID='".$UsersID."' and User_ID=".$UserID);
			}
			
			$Data = array(
				"Order_PaymentMethod"=>$_POST['PaymentMethod'],
				"Order_PaymentInfo"=>"",
				"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"]	
			);
			
			$DB->Set ('user_order', $Data, "where Users_ID='" . $UsersID . "' and User_ID='" . $UserID . "' and Order_ID in(".$orderids.")");

			if ($_POST['Is_store'] != 1) {
                if (isset($rsOrder['Order_Type']) && $rsOrder['Order_Type'] == 'pintuan') {
                    require_once(CMS_ROOT . '/include/helper/lib_pintuan.php');
                    require_once(CMS_ROOT.'/api/pintuan/comm/function.php');
                    $Data = payDone($UsersID, $OrderID, '余额支付');  //执行拼团支付逻辑
                } else {
                    require_once($_SERVER["DOCUMENT_ROOT"] . '/include/library/pay_order.class.php');
                    $pay_order = new pay_order($DB, $OrderID);
                    $Data = $pay_order->make_pay();
                }
            } else {
                require_once($_SERVER["DOCUMENT_ROOT"] . '/include/library/pay_order.class.php');
                $pay_order = new pay_order($DB, $OrderID);
                $Data = $pay_order->make_pay();
            }
			//获取团任务
			$teamid = 0;
			$productid = 0;
			$pdetail = $capsule->table('pintuan_teamdetail')->where(['order_id' => $OrderID, 'userid' => $UserID])->first();
			if(!empty($pdetail)){
				$teamid = $pdetail['teamid'];
				$pteam = $capsule->table('pintuan_team')->where(['users_id' => $UsersID, 'id' => $teamid])->first();
				$productid = $pteam['productid'];
				if($pteam['teamstatus']==1){
					/*
					if(!empty($rsOrder['Owner_ID'])){
						$url = '/api/'.$UsersID.'/pintuan/' . $rsOrder['Owner_ID'] . '/xiangqing/' . $productid . '/';
					}else{
						$url = '/api/'.$UsersID.'/pintuan/xiangqing/' . $productid . '/';
					}*/
					$url = '/api/'.$UsersID.'/pintuan/orderlist/2/';
					
				}else{
					if(!empty($rsOrder['Owner_ID'])){
						$url = '/api/'.$UsersID.'/pintuan/' . $rsOrder['Owner_ID'] . '/jointeam/' . $productid . '/'.$teamid.'/';
					}else{
						$url = '/api/'.$UsersID.'/pintuan/jointeam/' . $productid . '/'.$teamid.'/';
					}
				}
				$Data['url'] = $url;
			}
		}
    } else {//在线支付
		$Data=array(
			"Order_PaymentMethod"=>$_POST['PaymentMethod'],
			"Order_PaymentInfo"=>"",
			"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"]
		);
		$Flag = $DB->Set ( "user_order", $Data, "where Users_ID='" . $UsersID . "' and User_ID='" . $UserID . "' and Order_ID in(".$orderids.")");
		
		
		$url = "/api/" . $UsersID . "/shop/cart/pay/" . $OrderID . "/" . $PaymentMethod [$_POST ['PaymentMethod']] . "/?love";
		
		if($Flag){
			$Data=array(
				"status"=>1,
				"url"=>$url
			);
		}else{
			$Data=array(
				"status"=>0,
				"msg"=>'在线支付出现错误'
			);
		}		
	}
	
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
}elseif($action == 'distribute_product'){
	
	//检测用户是否登陆
	if(empty($UserID)){
		$_SESSION[$UsersID."HTTP_REFERER"]='http://'.$_SERVER['HTTP_HOST']."/api/".$UsersID."/shop/products/".$_POST['productid'].'/?wxref=mp.weixin.qq.com';
		$url = 'http://'.$_SERVER['HTTP_HOST']."/api/".$UsersID."/user/login/";
		/*返回值*/
		$response=array(
			"status"=>0,
			"info"=>"您还为登陆，请登陆！",
			"url"=>$url
		);
		
	}else{
		
		$condition = "where Users_ID='".$UsersID."' and User_ID='".$UserID."'";
		$rsUser = $DB->getRs("user","Is_Distribute",$condition);
		
		$response = array(
				"status"=>1,
				"Is_Distribute"=>$rsUser['Is_Distribute'],
			);	

	}
	
	echo json_encode($response,JSON_UNESCAPED_UNICODE);	
}else if($action == 'price'){

	$Products_ID = $_REQUEST["ProductsID"];
	$response    = array('err_msg' => '', 'result' => '', 'qty' => 1);
    $attr_id    = isset($_REQUEST['attr']) ? $_REQUEST['attr']: array();
    $qty     = (isset($_REQUEST['qty'])) ? intval($_REQUEST['qty']) : 1;
	$no_attr_price = $_REQUEST['no_attr_price'];
	$UsersID = $_REQUEST['UsersID'];
	
    if ($Products_ID == 0)
    {
        $response['msg'] = '无产品ID';
        $response['status']  = 0;
    }
    else
    {
        if ($qty == 0)
        {
            $response['qty'] = $qty = 1;
        }
        else
        {
            $response['qty'] = $qty;
        }
		
	
        $shop_price  = get_final_price($no_attr_price,$attr_id,$Products_ID,$UsersID);
        //查看用户是否登陆
		//获取登录用户的用户级别及其是否对应优惠价
		if(!empty($UserID)){
			$rsUser = $DB->GetRs("user","User_Level","where Users_ID='".$UsersID."' and User_ID=".$UserID);
			
			if($rsUser['User_Level'] >0 ){
				$rsUserConfig = $DB->GetRs("User_Config","UserLevel","where Users_ID='".$UsersID."'");
				$discount_list = json_decode($rsUserConfig["UserLevel"],TRUE);
				$discount =  $discount_list[$rsUser['User_Level']]['Discount'];
				$discount_price = $shop_price*(1-$discount/100);
				$discount_price = round($discount_price,2);
				$shop_price = $discount_price;
			}
		
		}
		$response['result'] = $shop_price;
    }
	
	echo json_encode($response,JSON_UNESCAPED_UNICODE);	
}else if($action == 'diyong'){
	//判断是否为pre订单
	$Pre_Order = $_POST['Order_ID'];
	if(strpos($Pre_Order,"PRE") !== false){
		$rsOrd=$DB->GetRs("user_pre_order","*","where usersid='".$UsersID."' and userid='".$UserID."' and pre_sn='".$Pre_Order."'");
		$condition = "where Users_ID = '".$UsersID."' and Order_ID=".$rsOrd['orderids'];
	}else{
		$condition = "where Users_ID = '".$UsersID."' and Order_ID=".$_POST['Order_ID'];
	}
	$rsOrder = Order::where("Order_ID", $Pre_Order)->first(["Order_TotalPrice"])->toArray();
	if($rsOrder['Order_TotalPrice'] - $_POST['Integral_Money'] < 1){
	    die(json_encode(array('status'=>0,'msg'=>'抵用后的金额不能少于1元'),JSON_UNESCAPED_UNICODE));
    }
	//记录此订单消耗多少积分
	$Data = array('Integral_Consumption'=>$_POST['Integral_Consumption'],
				   'Integral_Money'=>$_POST['Integral_Money'],
				   'Order_TotalPrice'=>'Order_TotalPrice-'.$_POST['Integral_Money']);
	
	mysql_query('start transaction');
	$Flag_a = $DB->Set('user_order',$Data,$condition,'Order_TotalPrice');
	//将积分移入不可用积分
	$Flag_b = add_userless_integral($UsersID,$UserID,$_POST['Integral_Consumption']);
			  	
	
	if($Flag_a&&$Flag_b){
		mysql_query('commit');
		$response = array('status'=>1,'msg'=>'抵用消耗记录到订单成功');
	}else{
		mysql_query("ROLLBACK");
	    $response = array('status'=>0,'msg'=>'抵用消耗记录到订单失败');
	}
	
	echo json_encode($response,JSON_UNESCAPED_UNICODE);	
	
}elseif($action == 'checkout_update'){ //提交订单页面跟新购物车（所有类型）
	$cart_key = $UsersID.$_POST['cart_key'];
	$k = explode("_", $_POST["_CartID"]);
	
    $BizID = array_shift($k);
	$ProductsID = array_shift($k);
	$CartID = array_shift($k);
    $redisCart = $myRedis->getCartListValue($cart_key, $UserID);
	if(empty($redisCart)){
		$Data=array(
			"status"=>0,
			"msg"=>"购物车空的，赶快去逛逛吧！"
		);
	    echo json_encode($Data, JSON_UNESCAPED_UNICODE);exit;
	}
    $CartList = json_decode($redisCart, true);
	$CartList[$BizID][$ProductsID][$CartID]["Qty"] = $_POST["_Qty"];
    $myRedis->setCartListValue($cart_key, $UserID, json_encode($CartList, JSON_UNESCAPED_UNICODE));

	//计算所需运费
	$rsConfig = $DB->GetRs("shop_config","*","where Users_ID='".$UsersID."'");
	$Shipping_IDs = isset($_POST['Shipping_ID']) ? organize_shipping_id($_POST['Shipping_ID']) : array();
	$City_Code = isset($_POST['City_Code']) ? $_POST['City_Code'] : 0;
	if($cart_key == $UsersID . 'Virtual'){
		$total_info['total'] = $total_info['total_shipping_fee'] = 0;
		foreach($CartList as $Biz_ID => $Biz_Cart){
			foreach ($Biz_Cart as $Products_ID=>$Products_List) {
				foreach ($Products_List as $k=>$v) {
					if (count($v["Property"]) > 0) {
						$total_info['total'] += $v["Qty"] * $v["Property"]["shu_pricesimp"];					
					}else{
						$total_info['total'] += $v["Qty"] * $v["ProductsPriceX"];					
					}
				}
			}
		}
	}else{
		//达达物流判断
		$Shipping_Method = $request->input('Shipping_ID');
		$total_shipping_fee = $request->input('total_shipping_fee');
		$Shipping_Method = !empty($Shipping_Method[0]['property'])?$Shipping_Method[0]['property']:'';
		if(!empty($Shipping_Method)){
			$total_info = get_biz_total_info($UsersID, $CartList[$BizID], $BizID, 0, 0);
			$total_info['total_shipping_fee'] = $total_shipping_fee;
			$rsShipping = DB::table('third_shipping')->where(['Users_ID' => $UsersID, 'Tag' => $Shipping_Method])->first();
			$total_info[$BizID]['Shipping_Name'] = $rsShipping['Name'];
			$total_info[$BizID]['total_shipping_fee'] = $total_shipping_fee;
		}else{
			$total_info = get_order_total_info($UsersID, $CartList, $rsConfig, $Shipping_IDs, $City_Code);
		}
	}//运费计算结束	
	$total_biz_price[$BizID]['price'] = 0;
	foreach($CartList[$BizID] as $Products_ID=>$Products_List){
		foreach ($Products_List as $k=>$v) {				
			if (count($v["Property"]) > 0) {
				$total_biz_price[$BizID]['price'] += $v["Qty"] * $v["Property"]["shu_pricesimp"];				
			}else{
				$total_biz_price[$BizID]['price'] += $v["Qty"] * $v["ProductsPriceX"];					
			}				
		}
	}
	//积分计算
	if(!empty($rsConfig['Integral_Convert'])){
		$integral = ($total_info['total'] + $total_info['total_shipping_fee']) / abs($rsConfig['Integral_Convert']);
	}else{
		$integral = 0;
	}
	
	// 获得产品的ID 计算积分	
	$Integration = 0;
	foreach($CartList as $key=>$products){
		foreach($products as $kk=>$vv){
			foreach($vv as $k=>$v){				
			 $ProductsIntegration= $DB->GetRs("shop_products","Products_Integration","where Products_ID=".$kk);
			 $CartList[$key][$kk][$k]['ProductsIntegration'] = $ProductsIntegration['Products_Integration'];
			 $Integration += $v['Qty'] * $ProductsIntegration['Products_Integration'];				
			}
		}
	}
	
	$reduce = !empty($total_info['reduce']) ? $total_info['reduce'] : 0;
	
	/*获取可用优惠券信息*/
	//获取优惠券
	//获取优惠券
	$coupon_info = get_useful_coupons($UserID,$UsersID,$BizID,$total_biz_price[$BizID]['price']);
	$youhui = 0;
	$youhuicontent = '';
	if($coupon_info['num'] >0) {
		require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');
		//设置smarty
		$smarty->left_delimiter = "{{";
		$smarty->right_delimiter = "}}";
		$template_dir = $_SERVER["DOCUMENT_ROOT"].'/api/shop/html';
		$smarty->template_dir = $template_dir;
		$youhui = 1;
		$youhuicontent = build_coupon_html($smarty, $coupon_info);
	}	
	/*j*/
	$pricels = array();
	$pricels = $CartList[$BizID][$ProductsID][$CartID]["Property"];
	if (count($pricels) > 0) {
		$price = $CartList[$BizID][$ProductsID][$CartID]["Property"]['shu_pricesimp'];	
	} else {
		$price = $CartList[$BizID][$ProductsID][$CartID]["ProductsPriceX"];
	}
	$Sub_Total_source = $price;
	$price = $price * (1 - $CartList[$BizID][$ProductsID][$CartID]["user_curagio"] * 0.01);	
	$total_info['total'] = $total_info['total'] * (1 - $CartList[$BizID][$ProductsID][$CartID]["user_curagio"] * 0.01);
	$Data = array(
		"status" => 1,
		"Sub_Total" => $price * $_POST["_Qty"],
		"Sub_Total_source" => $Sub_Total_source * $_POST["_Qty"],
		"Sub_Qty" => $_POST["_Qty"],
		"price" => $price,
		"biz_shipping_name" => isset($total_info [$BizID] ['Shipping_Name']) ? $total_info [$BizID] ['Shipping_Name'] : '',
		"biz_shipping_fee" => isset($total_info [$BizID] ['total_shipping_fee']) ? $total_info [$BizID] ['total_shipping_fee'] : 0,
		"total_shipping_fee" => $total_info ['total_shipping_fee'],
		"total" => $total_info['total'],
		"man_flag" => 0,
		"reduce" => $reduce,
		"integral" => $Integration,
		"youhui" => $youhui,
		"youhuicontent" => $youhuicontent,
	);
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);exit;
}elseif ($action == 'paymoney'){
    $BizID = $_POST["BizID"];
	$rsBiz = $DB->GetRs("biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID.' and Biz_Status=0');
	$rsUser = $DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$UserID);
	if($rsUser && is_numeric($_POST['Amount']) && $_POST['Amount']>0){
		//模拟加入购物车（兼容。。。。。。。。不解释。。。。都是泪）
		$cart = new cart($DB,$UsersID);
        $cart->offline_cart_add($_POST, $UserID);
        require_once ($_SERVER ["DOCUMENT_ROOT"] .'/control/shop/ordercontrol.class.php');
        $order = new ordercontrol($DB, $UsersID);
		require_once($_SERVER["DOCUMENT_ROOT"].'/control/shop/setting.class.php');
		$setting = new setting($DB,$UsersID);
		$rsConfig = $setting->get_one();
        $order->offline_order_create($UserID,$_POST,$rsConfig);
    }else{
		$Data=array(
			'status'=>0,
			'msg'=>'消费金额输入格式错误'
		);
	}
	echo json_encode (!empty($Data) ? $Data : [], JSON_UNESCAPED_UNICODE );
	exit;
} else if ($action == 'del') {  //取消订单 （shop、pintuan）

    $Order_ID = $_POST['Order_ID'];

    //获取订单详情
    $rsOrder = $DB->GetRs('user_order', '*', 'where Users_ID = "' . $UsersID . '" and User_ID = ' . $UserID . ' and Order_ID = ' . $Order_ID);

    if (empty($rsOrder)) {
        $Data = [
            'status' => 0,
            'msg' => '不存在该订单，请重新操作'
        ];
        echo json_encode($Data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (! in_array($rsOrder['Order_Status'], [0, 1])) {
        $Data = [
            'status' => 0,
            'msg' => '此订单不能取消'
        ];
        echo json_encode($Data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/distribute.php');

    mysql_query("BEGIN"); //开始事务定义

    //若是分销订单，删除分销记录
    if (is_distribute_order($DB, $UsersID, $Order_ID)) {
        delete_distribute_record($DB, $UsersID, $Order_ID);
    }

    $Flag = $DB->Del("user_order", 'Users_ID = "' . $UsersID . '" and User_ID = ' . $UserID . ' and Order_ID = ' . $Order_ID);

    if ($Flag) {
        mysql_query("COMMIT"); //执行事务
        $Data = [
            'status' => 1,
            'msg' => '删除成功'
        ];
    } else {
        mysql_query("ROLLBACK"); //回滚事务
        $Data = [
            'status' => 0,
            'msg' => '删除失败'
        ];
    }
    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
    exit;
} else if ($action == 'Yuebc_update') {
	$Yuebcprice = !empty($_POST['Yuebcprice']) ? $_POST['Yuebcprice'] : 0;
	$OrderID = !empty($_POST['orderid']) ? $_POST['orderid'] : 0;	
	
	if (!is_numeric($Yuebcprice) || !is_numeric($OrderID)) {
		$data = array(
			'status'=>0,
			'msg' => '请输入有效金额'
		);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit;
	}
		
	if(strpos($OrderID,"PRE") !== false){
	$rsOrder=$DB->GetRs("user_pre_order","*","where usersid='".$UsersID."' and userid=".$_SESSION[$UsersID.'User_ID']." and pre_sn=".$OrderID);
	$total = $rsOrder['total'];	
	}else{
		$rsOrder=$DB->GetRs("user_order","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID.'User_ID']." and Order_ID=".$OrderID);
		$total = $rsOrder['Order_TotalPrice'];		
	}
	
	$rsuserYue = $DB->GetRs('user', 'User_ID,User_Money', 'where Users_ID = "' . $UsersID . '" and User_ID = ' . $_SESSION[$UsersID.'User_ID']);
	$Yuebcpriceupdat = bcsub($rsuserYue['User_Money'], $Yuebcprice, 2);	
	$orderchprice = bcsub($total, $Yuebcprice, 2);
	if (!$rsOrder) {
		$data = array(
			'status'=>0,
			'msg' => '订单不存在'
		);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if ($rsOrder['User_ID'] != $_SESSION[$UsersID.'User_ID']) {
		$data = array(
			'status'=>0,
			'msg' => '非法操作'
		);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if ($orderchprice < 0) {
		$data = array(
			'status'=>0,
			'msg' => '输入余额不能大于订单总额'
		);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if ($Yuebcpriceupdat < 0) {
		$data = array(
			'status'=>0,
			'msg' => '余额不足,无法补差'
		);
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$total = bcsub($total, $Yuebcprice, 2);
	
	$data = array(
			'status' => 1,
			'total' => $total,			
			'Yuebcprice' => $Yuebcprice
		);
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit;
	
	}

function get_final_price($cur_price,$attr_id,$ProductID,$UsersID){
	
	global $DB1;
	
    $_SESSION[$UsersID.'attr_id'] = $attr_id;
	
	$attr_id_str = implode(',',$attr_id);
	
	$condition = "where  Products_ID =".$ProductID;
	$condition .= " And Product_Attr_ID in (".$attr_id_str.")";
   
	$rsProductAttrs = $DB1->Get("shop_products_attr","*",$condition);
	$product_attrs = $DB1->toArray($rsProductAttrs); 
    
	if(!empty($product_attrs)){
		foreach($product_attrs as $key=>$item){
			$cur_price += $item['Attr_Price']; 
		}
	}
	
	return $cur_price;
	
}
function organize_shipping_id($shipping_id) {
	$Shipping_IDS = array ();
	foreach ( $shipping_id as $key => $item ) {
		$Shipping_IDS[$item ['Biz_ID']] = $item ['Shipping_ID'];
	}
	
	return $Shipping_IDS;
}
function organize_shipping_id_new($shipping_id) {
	$Shipping_IDS = array ();
	foreach ( $shipping_id as $key => $item ) {
		$Shipping_IDS[$key] = $item;
	}
	
	return $Shipping_IDS;
}
function build_pre_order_no(){
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);

    return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
?>