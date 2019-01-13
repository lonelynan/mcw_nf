<?php
//生成二维码
function createQrcode($url)
{
    global $DB;
    if(!isset($_SESSION["BIZ_ID"])){
        return "";
    }
    $Biz_Id = $_SESSION["BIZ_ID"];
    require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/phpqrcode/phpqrcode.php');
    // 生成的文件名
    $filepath = $_SERVER["DOCUMENT_ROOT"].'/uploadfiles/biz/'.$Biz_Id.'/qrcode/';
    if(!file_exists($filepath)){
        if(file_exists($filepath.DIRECTORY_SEPARATOR.'qrcode.png')){
            @unlink($filepath.DIRECTORY_SEPARATOR.'qrcode.png');
        }
        @mkdir($filepath, 0777, true);
    }
    $filename = $filepath.DIRECTORY_SEPARATOR.'qrcode.png';
    // 纠错级别：L、M、Q、H
    $errorCorrectionLevel = 'H';
    // 点的大小：1到10
    $matrixPointSize = 3;
    //创建一个二维码文件
    QRcode::png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    return 'http://'.$_SERVER['HTTP_HOST'].'/uploadfiles/biz/'.$Biz_Id.'/qrcode/qrcode.png';
}

/*
 *  获取支付配置信息
 *  @param $UsersID 商家ID
 */
//
function getPayConfig ($UsersID, $isChoose = false)
{
    global $DB;

    if(!$UsersID)  return array();
    $payConfig = $DB->GetRs("users_payconfig","*","WHERE Users_ID = '{$UsersID}'");
    if ($isChoose)
    {
        $pay = array();
        if($payConfig['PaymentWxpayEnabled'] == 1)
        {
            $pay['WxPay']['Pay'] = "WxPay";
            $pay['WxPay']['Name'] = "微信支付";
            $pay['WxPay']['ID'] = 1;
        }
        if($payConfig['Payment_AlipayEnabled'] == 1)
        {
            $pay['AliPay']['Pay'] = "AliPay";
            $pay['AliPay']['Name'] = "支付宝支付";
            $pay['AliPay']['ID'] = 2;
        }
        /*
        if($payConfig['PaymentYeepayEnabled'] == 1)
        {
            $pay['YeePay']['Pay'] = "YeePay";
            $pay['YeePay']['Name'] = "易宝支付";
            $pay['YeePay']['ID'] = 4;
        }*/
        return $pay;
    }

    return $payConfig;
}


/**
 *确定需要多少运费
 *
 */
function get_shipping_fee($UsersID,$Template_ID,$Business,$City_Code,$Biz_ID,$Products_Info) {
	
	$template = get_cur_shipping_template($UsersID,$Biz_ID,$Template_ID);

	if(!$template){
	   $result = array('error'=>1,'msg'=>'没有找到相应的物流模板，请联系店主');
	   return $result;
	}
	
	$method = $template['By_Method'];
	
	//是否符合免运费条件
	if (!empty($template['Free_Content'])) {
		//进行免运费处理
		$res = free_shipping_deal($template['Free_Content'], $Business, $City_Code, $Products_Info);
		//如果免运费
		if ($res) {
			return 0;
		}
	}

	//不符合免运费条件,计算具体运费
	$rule = array();
	$template_content = organize_template_content(json_decode($template['Template_Content'], TRUE));
	$business_content = $template_content[$Business];
	$specify_areas = array();

	//此运费模板存在特殊区域
	if (!empty($business_content['specify'])) {
		foreach ($business_content['specify'] as $key => $specify) {
			$citys = explode(',', $specify['areas']);
			if (in_array($City_Code, $citys)) {
				$rule = $specify;
				break;
			}
		}
	}
	
	//如果rule仍为空
	if (empty($rule)) {
		$rule = $business_content['default'];
	}

	//根据规则计算具体运费
	$weight = $Products_Info['weight'];
	$qty = $Products_Info['qty'];
	$money = $Products_Info['money'];
	$fee = 0;
	
	if ($template['By_Method'] == 'by_weight') {
		
		if ($weight == 0) {
			$fee = 0;
		} elseif ($weight <= $rule['start']) {
			$fee = $rule['postage'];
		} else {
		
			$extra_weight = $weight - $rule['start'];
			if ($rule['plus']) {
			$extra_fee = @ceil($extra_weight / $rule['plus']) * $rule['postageplus'];
			} else {
			$extra_fee = 0;
			}
			$fee = $rule['postage'] + $extra_fee;
		}

	} elseif ($template['By_Method'] == 'by_qty') {
		if ($qty == 0) {
			$fee = 0;
		} elseif ($qty <= $rule['start']) {
			$fee = $rule['postage'];
		} else {
			$extra_qty = $qty - $rule['start'];
			if ($rule['plus']) {
			$extra_fee = @ceil($extra_qty / $rule['plus']) * $rule['postageplus'];
			} else {
			$extra_fee = 0;
			}
			$fee = $rule['postage'] + $extra_fee;
		}

	}
	

	return $fee;
	
}

/**
 *确定此产品是否满足免运费条件
 */
function free_shipping_deal($free_content, $Business, $City_Code, $Products_Info) {

	$free_content = json_decode($free_content, true);

	//整理免运费条件
	$list_by_business = array();
	foreach ($free_content as $key => $item) {
		$list_by_business[$item['trans_type']][] = $item;
	}

	//如果不存在此业务的免费条款
	if (!array_key_exists($Business, $list_by_business)) {
		return false;
	} else {
		$regulartions = $list_by_business[$Business];
		$result = FALSE;

		foreach ($regulartions as $key => $regulation) {
			//是否在免运费城市之内
			if (!empty($regulation['areas'])) {
				$citys = explode(',', $regulation['areas']);
				//如果不在免运费城市中，继续循环
				if (!in_array($City_Code, $citys)) {
					continue;
				}
			}

			//根据优惠类型，进行具体决定
			//0 按件数,1 按金额,2 件数+金额
			if ($regulation['designated'] == 0) {

				if ($Products_Info['qty'] >= $regulation['prefrrendial_qty']) {
					$result = TRUE;
					break;
				}

			} elseif ($regulation['designated'] == 1) {

				$money = $Products_Info['money'];
				if ($money >= $regulation['prefrrendial_money']) {
					$result = TRUE;
					break;
				}

			} elseif ($regulation['designated'] == 2) {
				$money = $Products_Info['money'];

				if ($Products_Info['qty'] >= $regulation['prefrrendial_qty'] && $money >= $regulation['prefrrendial_money']) {
					$result = TRUE;
					break;
				}
			}

		}

	}

	return $result;

}

/**
 *确定使用的是哪一个物流模板
 *店铺配置参数
 *
 */
function  get_cur_shipping_template($UsersID,$Biz_ID,$Template_ID) {

	global $DB1;
	
	$condition = "where Users_ID = '" . $UsersID . "' and Biz_ID = " .$Biz_ID. " and Template_ID = " . $Template_ID;
	$result = $DB1->getRs('shop_shipping_template', '*', $condition);

	return $result;
}

/**
 * 获取物流简述信息,包含可用快递公司信息，及其下属物流模板
 * @param  String $UsersID
 * @return Array  $brief  结构
 *
 */
function get_shipping_brief($UsersID, $Biz_ID) {

	
	global $DB1;
	//获得所有可用的快递公司
	$condition = "where Users_ID ='" . $UsersID . "' and Biz_ID = " . $Biz_ID . " and Shipping_Status = 1";
	$rsShippingCompanys = $DB1->get('shop_shipping_company', 'Shipping_ID,Shipping_Name,Cur_Template,Biz_ID', $condition);

	if (empty($rsShippingCompanys)) {
		return false;
	}

	$Shipping_List = $DB1->toArray($rsShippingCompanys);

	//获取这些快递公司下属的物流模板
	$brief['Shipping_List'] = $Shipping_List;

	//检索出快递公司Shipping_ID
	$Shipping_ID_List = array();
	foreach ($Shipping_List as $key => $company) {
		$Shipping_ID_List[] = $company['Shipping_ID'];
	}
	
	if(count($Shipping_ID_List) >0){
		$Shipping_ID_Str = implode(',', $Shipping_ID_List);
		$condition = "where Users_ID ='" . $UsersID . "'  and Biz_ID = " . $Biz_ID . " and Template_Status = 1" .
	" and  Shipping_ID in (" . $Shipping_ID_Str . ")";

		$rsTemplates = $DB1->get('shop_shipping_template', 'Template_ID,Template_Name,Shipping_ID', $condition);
	}else{
		$rsTemplates = false;
	}
	
	$Template_List = array();
	if ($rsTemplates) {
		$Template_List = $DB1->toArray($rsTemplates);
	}

	$Template_Dropdown = array();
	foreach ($Template_List as $key => $item) {
		$Template_Dropdown[$item['Shipping_ID']][] = $item;
	}

	$brief['Template_Dropdown'] = $Template_Dropdown;

	return $brief;

}


/*获取某供货商下运费*/
function get_biz_total_info($UsersID, $Biz_Cart, $Biz_ID, $Template_ID, $City_Code = 0, $isCuragio = 0, $param = []) {
	
	global $DB1;
		
	$qty = 0;
	$weight = 0;
	$money = 0;
	$total = 0;
	foreach ($Biz_Cart as $Products_ID=>$Products_List) {
        foreach ($Products_List as $k=>$v) {
            if ($City_Code != 0) {
                if ($v['IsShippingFree'] == 0) {
                    $qty += $v["Qty"];
                    $weight += $v["Qty"] * $v["ProductsWeight"];
                    if($isCuragio){
                        $money += $v["Qty"] * ($v["ProductsPriceX"] * (100-$v['user_curagio'])/100);
                    }else{
                        $money += $v["Qty"] * $v["ProductsPriceX"];
                    }
                } else {
                    $qty += 0;
                    $weight += 0;
                    $money += 0;
                }
            }
            if (count($v["Property"]) > 0) {
				if($isCuragio){
                    $total += $v["Qty"] * ($v["Property"]["shu_pricesimp"] * (100-$v['user_curagio'])/100);
                }else{
                    $total += $v["Qty"] * $v["Property"]["shu_pricesimp"];
                }
            }else{
                if($isCuragio){
                    $total += $v["Qty"] * ($v["ProductsPriceX"] * (100-$v['user_curagio'])/100);
                }else{
                    $total += $v["Qty"] * $v["ProductsPriceX"];
                }
            }
        }

	}
	
	$Product_Info = array('qty'=>$qty,'weight'=>$weight,'money'=>$money);
   
	$Business = 'express';

	if ($City_Code == 0) {
		$total_shipping_fee = 0;
		//对达达配送的判断
		if(!empty($param['StoreListID'][$Biz_ID])){
			$UserID = $param['UserID'];
			$AddressID = $param['AddressID'];
			$StoreID = $param['StoreListID'][$Biz_ID];
			$result = getDataFee($UsersID, $Biz_ID, $StoreID, $UserID, $AddressID, $total, 'dada');
			if($result['status']==1){
				$total_shipping_fee = $result['data']['fee'];
			}
		}
	} else {
		$total_shipping_fee = get_shipping_fee($UsersID, $Template_ID, $Business, $City_Code, $Biz_ID, $Product_Info);
		
		if(is_array($total_shipping_fee)){
		   
			return $total_shipping_fee;
		}
	}
	$total_info = array('total'=>$total,'total_shipping_fee'=>$total_shipping_fee, 'weight' => $weight);
	return $total_info;
	
}

/*获取某供货商下运费砍价*/
function get_biz_total_infokanjia($UsersID, $Biz_Cart, $Biz_ID, $Template_ID, $City_Code = 0) {
	
	global $DB1;
		
	$qty = 0;
	$weight = 0;
	$money = 0;
	$total = 0;
	foreach ($Biz_Cart as $Products_ID=>$Products_List) {
		foreach ($Products_List as $k=>$v) {
			
			if ($City_Code != 0) {
				if ($v['IsShippingFree'] == 0) {
					$qty += $v["Qty"];
					$weight += $v["Qty"] * $v["ProductsWeight"];
					$money += $v["Qty"] * $v["ProductsPriceX"];
				} else {
					$qty += 0;
					$weight += 0;
					$money += 0;
				}
			}
			if (count($v["Property"]) > 0) {
			$total += $v["Qty"] * $v["Property"]["shu_pricesimp"];
			}else{
			$total += $v["Qty"] * $v["ProductsPriceX"];
			}
		}

	}
	
	$Product_Info = array('qty'=>$qty,'weight'=>$weight,'money'=>$money);
   
	$Business = 'express';

	if ($City_Code == 0) {
		$total_shipping_fee = 0;
	} else {
		$total_shipping_fee = get_shipping_fee($UsersID, $Template_ID, $Business, $City_Code, $Biz_ID, $Product_Info);
		
		if(is_array($total_shipping_fee)){
		   
			return $total_shipping_fee;
		}
	}
	
	$total_info = array('total'=>$total,'total_shipping_fee'=>$total_shipping_fee);
	return $total_info;
	
}

/**
 *获取购物车产品总价
 *以及总运费
 */
function get_order_total_info($UsersID, $CartList, $rsConfig, $Shipping_IDS, $City_Code = 0, $user_curagio = 0, $param = []) {
	global $DB1;
    $Biz_ID_String = implode(',', array_keys($CartList));
	//获取每个供货商的运费默认配置
	$condition = "where Users_ID  = '".$UsersID."' and Biz_ID in (".$Biz_ID_String.")";
	$rsBizConfigs = $DB1->get('biz','Biz_ID,Biz_Name,Shipping,Default_Shipping,Default_Business',$condition);
	$Biz_Config_List = $DB1->toArray($rsBizConfigs);
	
	$Biz_Config_Dropdown = array();

	foreach($Biz_Config_List as $key=>$item){
		$Biz_Config_Dropdown[$item['Biz_ID']] = $item; 
	}
    $total = 0;
	$total_shipping_fee = 0;
    $order_total_info = [];
    foreach($CartList as $Biz_ID => $item){
		$Biz_Config = $Biz_Config_Dropdown[$Biz_ID];
		$Shipping_Config = json_decode($Biz_Config['Shipping'], TRUE);
		//如果未指定Shipping_ID,则使用默认配置
        if($Shipping_IDS == -1){
            $Shipping_ID = -1;
        }else{
            if(count($Shipping_IDS) == 0) {
                $Shipping_ID = $Biz_Config['Default_Shipping'];
            }else {
                $Shipping_ID = $Shipping_IDS[$Biz_ID];;
            }
        }
		$Biz_Default_Shipping_Tempatle = json_decode($Biz_Config['Shipping'],TRUE);
		$Biz_Default_Shipping = json_decode($Biz_Config['Shipping'], TRUE);
		$biz_company_dropdown = get_front_shiping_company_dropdown($UsersID, $Biz_Config);
		if($Shipping_ID == -1){  //虚拟产品
		    $total_shipping_fee = 0;
            $Biz_Total_Info = get_biz_total_info($UsersID, $item, $Biz_ID, 0, 0, $user_curagio);
            $total += $Biz_Total_Info['total'];
            $order_total_info[$Biz_ID] = $Biz_Total_Info;
        }else{
            if(!empty($Biz_Default_Shipping[$Shipping_ID])){
                $Template_ID = $Biz_Default_Shipping[$Shipping_ID];
                $Biz_Total_Info = get_biz_total_info($UsersID, $item, $Biz_ID, $Template_ID, $City_Code, $user_curagio);
                if(!empty($Biz_Total_Info)){
                    $Biz_Total_Info['Shipping_ID'] = $Shipping_ID;
                    $Biz_Total_Info['Shipping_Name'] = isset($biz_company_dropdown[$Shipping_ID])?$biz_company_dropdown[$Shipping_ID]:"";
                }
            }else{
				if ($Shipping_ID == 'Is_dada') {
					$Biz_Total_Info = get_biz_total_info($UsersID, $item, $Biz_ID, 0, 0, $user_curagio, $param);
					if(!empty($Biz_Total_Info)){
						global $capsule;
						$rsShipping = $capsule->table('third_shipping')->where(['Users_ID' => $UsersID, 'Tag' => 'dada'])->first();
						$Biz_Total_Info['Shipping_ID'] = $Shipping_ID;
						$Biz_Total_Info['Store_ID'] = !empty($param['StoreListID'][$Biz_ID]) ? $param['StoreListID'][$Biz_ID] : 0;
						$Biz_Total_Info['Shipping_Name'] = $rsShipping['Name'];
					} else {
						$Biz_Total_Info = array(
								'error'=>1,
								'msg'=> (!empty($Biz_Config['Biz_Name']) ? '商家：' . $Biz_Config['Biz_Name'] . '，' : '商家') . '未指定运费模板，请联系店主。'
						);
						$Biz_Total_Info['Shipping_ID'] = 0;
					}
				}else if($Shipping_ID == 'Is_BizStores'){
					$Biz_Total_Info = get_biz_total_info($UsersID, $item, $Biz_ID, 0, 0, $user_curagio);
					$Biz_Total_Info['Shipping_ID'] = $Shipping_ID;
                    $Biz_Total_Info['Shipping_Name'] = '到店自提';
					$Biz_Total_Info['total_shipping_fee'] = 0;
				}else {
					$Biz_Total_Info = array(
							'error'=>1,
							'msg'=> (!empty($Biz_Config['Biz_Name']) ? '商家：' . $Biz_Config['Biz_Name'] . '，' : '商家') . '未指定运费模板，请联系店主。'
					);
					$Biz_Total_Info['Shipping_ID'] = 0;
				}
            }
            if(!empty($Biz_Total_Info)){
                $Biz_Total_Info['Biz_Config'] = $Biz_Config_Dropdown[$Biz_ID];
                $order_total_info[$Biz_ID] = $Biz_Total_Info;
            }

            if(!isset($Biz_Total_Info['error']) || empty($Biz_Total_Info['error'])){
                $total += $Biz_Total_Info['total'];
                $total_shipping_fee += $Biz_Total_Info['total_shipping_fee'];
				$order_total_info[$Biz_ID]['total_shipping_fee'] = $Biz_Total_Info['total_shipping_fee'];
            }
        }
	}
	$order_total_info['total'] = $total;
	
	$order_total_info['total_shipping_fee'] = $total_shipping_fee;
	return $order_total_info;
}

/**
 *获取购物车产品总价
 *以及总运费砍价模块
 */
function get_order_total_infokanjia($UsersID, $CartList, $rsConfig, $Shipping_IDS, $City_Code = 0) {
	global $DB1;	
    $Biz_ID_String = implode(',', array_keys($CartList));
	
	//获取每个供货商的运费默认配置
	$condition = "where Users_ID  = '".$UsersID."' and Biz_ID in (".$Biz_ID_String.")";
	$rsBizConfigs = $DB1->get('biz','Biz_ID,Biz_Name,Shipping,Default_Shipping,Default_Business',$condition);
	$Biz_Config_List = $DB1->toArray($rsBizConfigs);
	
	$Biz_Config_Dropdown = array();

	foreach($Biz_Config_List as $key=>$item){
		$Biz_Config_Dropdown[$item['Biz_ID']] = $item; 
	}

    $total = 0;
	$total_shipping_fee = 0;
    foreach($CartList as $Biz_ID => $item){		
		$Biz_Config = $Biz_Config_Dropdown[$Biz_ID];
		$Shipping_Config = json_decode($Biz_Config['Shipping'], TRUE);
		//如果未指定Shipping_ID,则使用默认配置
		if(count($Shipping_IDS) == 0) {
			$Shipping_ID = $Biz_Config['Default_Shipping'];
		}else {
			$Shipping_ID = $Shipping_IDS[$Biz_ID];;
		}		
		
		$Biz_Default_Shipping_Tempatle = json_decode($Biz_Config['Shipping'],TRUE);
		$Biz_Default_Shipping = json_decode($Biz_Config['Shipping'], TRUE);
	
		$biz_company_dropdown = get_front_shiping_company_dropdown($UsersID, $Biz_Config);
		
		if(!empty($Biz_Default_Shipping[$Shipping_ID])){
			$Template_ID = $Biz_Default_Shipping[$Shipping_ID];
			$Biz_Total_Info = get_biz_total_infokanjia($UsersID, $item, $Biz_ID, $Template_ID, $City_Code);
			if(!empty($Biz_Total_Info)){
			     $Biz_Total_Info['Shipping_ID'] = $Shipping_ID;
			     $Biz_Total_Info['Shipping_Name'] = isset($biz_company_dropdown[$Shipping_ID])?$biz_company_dropdown[$Shipping_ID]:"";	
			}
		}else{
			$Biz_Total_Info = array(
			    'error'=>1,
				'msg'=> (!empty($Biz_Config['Biz_Name']) ? '商家：' . $Biz_Config['Biz_Name'] . '，' : '商家') . '未指定运费模板，请联系店主。'
			);
			$Biz_Total_Info['Shipping_ID'] = 0;
		}
		if(!empty($Biz_Total_Info)){
		  $Biz_Total_Info['Biz_Config'] = $Biz_Config_Dropdown[$Biz_ID];
		  $order_total_info[$Biz_ID] = $Biz_Total_Info;
		}
		if(empty($Biz_Total_Info['error'])){		
			$total += $Biz_Total_Info['total'];
			$total_shipping_fee += $Biz_Total_Info['total_shipping_fee'];
		}
	}
   
	$order_total_info['total'] = $total;
	$order_total_info['total_shipping_fee'] = $total_shipping_fee;
	return $order_total_info;
}

//计算参加满多少减多少活动后的金额,无需叠加性
function  man_act($sum,$regulation){
	
	$man_sum = $sum;

	$regulation =  array_reverse($regulation);
	foreach($regulation as $key=>$item){
		
		if($sum >= $item['reach']){
		    $man_sum = $sum - $item['award'];
			break;
		}

	}
	
	return $man_sum;
	
}

/**
 *获取可用优惠券
 **/
function get_useful_coupons($User_ID, $UsersID,$Biz_ID, $total_price) {
	global $DB1;
	$num = 0;
	$condition = "where User_ID=" . $User_ID . " and Users_ID='" . $UsersID . "' and Coupon_UseArea=1 and (Coupon_UsedTimes=-1 or Coupon_UsedTimes>0) and Coupon_StartTime<=" . time() . " and Coupon_EndTime>=" . time()." and Biz_ID=".$Biz_ID;
	
	$DB1->Get("user_coupon_record", "*", $condition);

	$lists = array();
	while ($rsCoupon = $DB1->fetch_assoc()) {
		if ($rsCoupon["Coupon_Condition"] <= $total_price) {
			if ($rsCoupon['Coupon_UseType'] == 0 && $rsCoupon['Coupon_Discount'] > 0 && $rsCoupon['Coupon_Discount'] < 1) {
				$lists[] = $rsCoupon;
				$num++;
			}
			if ($rsCoupon['Coupon_UseType'] == 1 && $rsCoupon['Coupon_Cash'] > 0) {
				$lists[] = $rsCoupon;
				$num++;
			}
		}
	}
	
	//完善优惠券信息
	if ($num > 0) {
		foreach ($lists as $k => $v) {
			$r = $DB1->GetRs("user_coupon", "Coupon_Subject", "where Coupon_ID=" . $v["Coupon_ID"]);
			$v["Subject"] = $r["Coupon_Subject"];
			if ($v['Coupon_UseType'] == 0 && $v['Coupon_Discount'] > 0 && $v['Coupon_Discount'] < 1) {
				$v["Subject"] .= '(可享受折扣' . ($v['Coupon_Discount'] * 10) . '折)';
			}
			if ($v['Coupon_UseType'] == 1 && $v['Coupon_Cash'] > 0) {
				$v["Subject"] .= '(可抵现金' . $v['Coupon_Cash'] . '元)';
			}
			$lists[$k] = $v;
		}

	}
	
	
	$coupon_info = Array();
	$coupon_info['num'] = $num;
	$coupon_info['lists'] = $lists;
	return $coupon_info;
}

/**
 *生成优惠券html
 */
function build_coupon_html($smarty, $coupon_info) {

	$coupon_html = '';
	if ($coupon_info['num'] > 0) {
		$lists = $coupon_info['lists'];
		foreach ($lists as $key => $item) {
			$lists[$key]['Price'] = $item["Coupon_UseType"] == 0 ? $item["Coupon_Discount"] : $item["Coupon_Cash"];
		}
		$smarty->assign('lists', $lists);
		$coupon_html = $smarty->fetch('order_coupon.html');
	}

	return $coupon_html;
}


/**
 * 根据传递数组写入消费记录表里
 * @param  array $param 传递所要添加的数据
 * @return bool 返回
 */
function add_user_money_record($param) {

    $rsObjUserMoneyRecord = new UserMoneyRecord();
    $rsObjUserMoneyRecord->Users_ID = $param['Users_ID'];
    $rsObjUserMoneyRecord->User_ID = $param['User_ID'];
    $rsObjUserMoneyRecord->Type = isset($param['Type'])?$param['Type']:0;
    $rsObjUserMoneyRecord->Amount = isset($param['Amount'])?$param['Amount']:0;
    $rsObjUserMoneyRecord->Total = isset($param['Total'])?$param['Total']:0;
    $rsObjUserMoneyRecord->Note = isset($param['Note'])?$param['Note']:'';
    $rsObjUserMoneyRecord->CreateTime = time();
    return $rsObjUserMoneyRecord->save();
}

/**
 * 购买商品后生成消费记录
 * @param  array $param 传递所要添加的数据
 * @param  array $type 订单类型  1 充值  2 订单 3 代理  4  股东 5 分销
 * @return bool 返回
 */
function write_user_money_record($rsOrder, $type = 1,$method){
    $Method = array(
        '余额支付',
        '微支付',
        '支付宝'
    );
    if(isset($rsOrder['User_ID']) && $rsOrder['User_ID']){
        $rsObjUser = User::Multiwhere([
            'Users_ID' => $rsOrder['Users_ID'],
            'User_ID' => $rsOrder['User_ID']
        ])->first();
        if(!$rsObjUser){
            return false;
        }
        $rsUser = $rsObjUser->toArray();
        $UserName = $rsUser['User_NickName']?$rsUser['User_NickName']:($rsUser['User_Name']?$rsUser['User_Name']:'');
        $Order_Type = "";
        $frontMsg = "";
        $backMsg = "";
        if($type ==1){  //充值记录
            $amount = $rsUser['User_Money']+$rsOrder['Amount'];
            add_user_money_record([
                'Users_ID' => $rsOrder["Users_ID"],
                'User_ID' => $rsOrder['User_ID'],
                'Type' => 1,
                'Amount' => $rsOrder['Amount'],
                'Total' => $amount,
                'Note' => $rsOrder['Operator']
            ]);
            $rsObjUser->User_Money = $amount;
            $rsObjUser->save();
            return true;
        }else if($type == 2){    // 订单
            if($rsOrder['Order_Type'] == 'shop'){
                $Order_Type = "微商城";
            }else if($rsOrder['Order_Type'] == 'pintuan' || $rsOrder['Order_Type'] == 'dangou'){
                $Order_Type = "拼团";
            }else if($rsOrder['Order_Type'] == 'cloud'){
                $Order_Type = "云购";
            }else if($rsOrder['Order_Type'] == 'kanjia'){
                $Order_Type = "砍价";
            }else if(stripos($rsOrder['Order_Type'],'zhongchou')!==false){
                $Order_Type = "众筹";
            }else{
                $Order_Type = "微商城";
            }
            $frontMsg = $UserName."  ".$Method[$method]."  ".$Order_Type." 支付 +" . $rsOrder['Order_TotalPrice'] . "到余额 (订单号:" . $rsOrder['Order_ID'] . ")";
            $backMsg = $UserName."  ".$Method[$method]."  ".$Order_Type." 从余额支出 -" . $rsOrder['Order_TotalPrice'] . " (订单号:" . $rsOrder['Order_ID'] . ")";
        }else if($type == 3){   //股东
            $frontMsg = $UserName."  ".$Method[$method] . "   购买成为股东 到余额 +" . $rsOrder['Order_TotalPrice'] . " (订单号:" . $rsOrder['Order_ID'] . ")";
            $backMsg = $UserName."  ".$Method[$method]."  购买成为股东 从余额支出 -" . $rsOrder['Order_TotalPrice'] . " (订单号:" . $rsOrder['Order_ID'] . ")";
        }else if($type == 4){   //区域
            $Area = array(
                '省级代理',
                '市级代理',
                '县级代理'
            );
            $frontMsg = $UserName."  ".$Method[$method] . "购买" . $Area[$rsOrder['Area'] - 1] . '到余额 +' . $rsOrder['Order_TotalPrice'] . " (订单号:" . $rsOrder['Order_ID'] . ")";
            $backMsg = $UserName."  ".$Method[$method]."  购买" . $Area[$rsOrder['Area'] - 1] . " 从余额支出 -" . $rsOrder['Order_TotalPrice'] . " (订单号:" . $rsOrder['Order_ID'] . ")";
        }else if($type == 5){   //分销
            $Active = array(
                '购买',
                '升级'
            );
            $frontMsg = $UserName."  ".$Method[$method].$Active[$rsOrder['Order_Type']] . "成为" . $rsOrder['Level_Name'] . "到余额  +" . $rsOrder['Order_TotalPrice']."到余额". " (订单号:" . $rsOrder['Order_ID'] . ")";
            $backMsg = $UserName."  ".$Method[$method].$Active[$rsOrder['Order_Type']] . "成为" . $rsOrder['Level_Name'] . "支出 -" . $rsOrder['Order_TotalPrice']. " (订单号:" . $rsOrder['Order_ID'] . ")";

        }else if($type == 6){
            $frontMsg = $UserName."  ".$Method[$method]."充值话费 到余额 +" . $rsOrder['Order_TotalPrice'] . $rsOrder['Order_TotalPrice'] . " (订单号:" . $rsOrder['Order_ID'] . ")";
            $backMsg = $UserName."  ".$Method[$method]."充值话费 支出 -" . $rsOrder['Order_TotalPrice'] . $rsOrder['Order_TotalPrice'] . " (订单号:" . $rsOrder['Order_ID'] . ")";
        }
        $amount = $rsUser['User_Money']+$rsOrder['Order_TotalPrice'];
        $rsObjUser->User_Money = $amount;
        $rsObjUser->save();
        add_user_money_record([
            'Users_ID' => $rsOrder['Users_ID'],
            'User_ID' => $rsOrder['User_ID'],
            'Type' => 1,
            'Amount' => $rsOrder['Order_TotalPrice'],
            'Total' => $amount,
            'Note' => $frontMsg
        ]);

        $rsObjUser = User::Multiwhere([
            'Users_ID' => $rsOrder['Users_ID'],
            'User_ID' => $rsOrder['User_ID']
        ])->first();
        $rsUser = $rsObjUser->toArray();
        $amount = $rsUser['User_Money']-$rsOrder['Order_TotalPrice'];
        $rsObjUser->User_Money = $amount;
        $rsObjUser->save();
        add_user_money_record([
            'Users_ID' => $rsOrder['Users_ID'],
            'User_ID' => $rsOrder['User_ID'],
            'Type' => 0,
            'Amount' => $rsOrder['Order_TotalPrice'],
            'Total' => $amount,
            'Note' => $backMsg
        ]);

        if($type == 5){
            $rsLevel = Dis_Level::Multiwhere([
                'Users_ID' => $rsOrder['Users_ID'],
                'Level_ID' => $rsOrder['Level_ID']
            ])->first();
            if($rsLevel && $rsLevel->Present_type == 2) {
                $Active = array(
                    '购买',
                    '升级'
                );
                $rsObjUser = User::Multiwhere([
                    'Users_ID' => $rsOrder['Users_ID'],
                    'User_ID' => $rsOrder['User_ID']
                ])->first();
                $rsUser = $rsObjUser->toArray();
                $amount = $rsUser['User_Money'] + $rsOrder['Order_Present'];
                $rsObjUser->User_Money = $amount;
                $rsObjUser->save();
                add_user_money_record([
                    'Users_ID' => $rsOrder['Users_ID'],
                    'User_ID' => $rsOrder['User_ID'],
                    'Type' => 1,
                    'Amount' => $rsOrder['Order_Present'],
                    'Total' => $amount,
                    'Note' => $UserName."  ".$Method[$method].$Active[$rsOrder['Order_Type']] . "成为" . $rsOrder['Level_Name'] . "，赠送 + ".$rsOrder['Order_Present'],
                    'CreateTime' => time()
                ]);
            }
            $rsObjUser = User::Multiwhere([
                'Users_ID' => $rsOrder['Users_ID'],
                'User_ID' => $rsOrder['User_ID']
            ])->first();
            $rsUser = $rsObjUser->toArray();
            $amount = $rsUser['User_Money']+$rsOrder['Order_TotalPrice'];
            $rsObjUser->User_Money = $amount;
            $rsObjUser->save();
            add_user_money_record([
                'Users_ID' => $rsOrder['Users_ID'],
                'User_ID' => $rsOrder['User_ID'],
                'Type' => 1,
                'Amount' => $rsOrder['Order_TotalPrice'],
                'Total' => $amount,
                'Note' => $UserName."  ".$Method[$method].$Active[$rsOrder['Order_Type']] . "成为" . $rsOrder['Level_Name'] . "返还 +" . $rsOrder['Order_TotalPrice'] . " (订单号:" . $rsOrder['Order_ID'] . ")"
            ]);
        }
        return true;
    }
}

//获取会员优惠
if(!function_exists('get_user_curagio')) {
    function get_user_curagio($UsersID, $User_ID)
    {
        global $DB1;
        $rsUser = $DB1->GetRs('user', '*', 'where Users_ID = "' . $UsersID . '" and User_ID = ' . $User_ID);
        $rsUserConfig = $DB1->GetRs('User_Config', '*', 'where Users_ID = "' . $UsersID . '"');

        $discount_list = $rsUserConfig["UserLevel"] ? json_decode($rsUserConfig["UserLevel"], true) : [];

        $user_curagio = 0;
        if (!empty($discount_list)) {
            if (count($discount_list) >= 1) {
                $user_curagio = isset($discount_list[$rsUser['User_Level']]['Discount']) ? $discount_list[$rsUser['User_Level']]['Discount'] : 0;
                if (empty($user_curagio)) {
                    $user_curagio = 0;
                }
            }
        }
        return $user_curagio;
    }
}

/**
 * 
 * @param  [number] $sum        [商品总价格]
 * @param  [array] $regulation [满减活动数组]
 * @return [array]             [参数的满减活动项]
 */
function hand_man_reduce_act($sum, $regulation) {
	$regulation =  array_reverse($regulation);
	$max = 0;
	$return = [];

	foreach ($regulation as $key => $item) {
		if ($sum >= $item['reach'] && $item['award'] > $max) {
		    $max = $item['award'];
		    $return = $item;
		    $return['total'] = $sum;
		    $return['result'] = $sum - $item['award'];
		}
	}

	return $return;	
}

/**
 * 获取参加的满减活动项
 * @param  string 商城ID
 * @param  $CartList 购买车商品信息
 * @param  array $rsConfig 商城配置信息
 * @return array 
 */
function get_hand_man_reduce_act($UsersID, $CartList, $rsConfig) {
	$qty = 0;
	$weight = 0;
	$shipping_money = 0;
	$total =0 ;
	$total_shipping_fee = 0;

	foreach($CartList as $key=>$value){
		foreach ($value as $j => $v) {
			$total += $v["Qty"] * $v["ProductsPriceX"];
		}
	}

	//是否符合满多少送多少活动
	$man_list = json_decode($rsConfig['Man'], true);

	$result = [];
	if (count($man_list) > 0) {
		$result = hand_man_reduce_act($total, $man_list);
	}

	return $result;
}