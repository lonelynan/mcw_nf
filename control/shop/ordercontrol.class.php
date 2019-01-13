<?php
	/*
	*	订单相关操作类
	*/
class ordercontrol{
	
	private $DB;
	private $UsersID;
	
	function __construct($DB,$UsersID){
		$this->DB = $DB;//数据库连接资源
		$this->UsersID = $UsersID;//数据库连接资源
		
	}
	
	/*
	*	订单创建
	*	@param int $Post_Data 订单数据
	*	@echo json 订单生成状态
	*/
	function order_create($User_ID,$Post_Data,$rsConfig){
	
		$Data = array (
			"Users_ID" => $this->UsersID,
			"User_ID" => $User_ID 
		);
		
		$virtual = isset ( $Post_Data ['virtual'] ) ? $Post_Data ['virtual'] : 0;
		$needcart = isset ( $Post_Data ['needcart'] ) ? $Post_Data ['needcart'] : 1;
		
		if ($virtual) {
			$cart_key = $this->UsersID . "Virtual";
		} else {
			if ($needcart == 1) {
				$cart_key = $this->UsersID . "CartList";
			} else {
				$cart_key = $this->UsersID . "BuyList";
			}
		}
		
		if ($virtual == 0) {
			
			$AddressID = empty ( $Post_Data ['AddressID'] ) ? 0 : $Post_Data ['AddressID'];
			$Need_Shipping = $Post_Data ['Need_Shipping'];
			if ($Need_Shipping == 1) {
				if (! empty ( $Post_Data ['AddressID'] )) {
					
					$rsAddress = $this->DB->GetRs ( "user_address", "*", "where Users_ID='" . $this->UsersID . "' and User_ID='" . $User_ID . "' and Address_ID='" . $AddressID . "'" );
					
					$Data ["Address_Name"] = $rsAddress ['Address_Name'];
					$Data ["Address_Mobile"] = $rsAddress ["Address_Mobile"];
					$Data ["Address_Province"] = $rsAddress ["Address_Province"];
					$Data ["Address_City"] = $rsAddress ["Address_City"];
					$Data ["Address_Area"] = $rsAddress ["Address_Area"];
					$Data ["Address_Detailed"] = $rsAddress ["Address_Detailed"];
				}
			}
		} else {
			$Data ["Order_IsVirtual"] = 1;
			$Data ["Order_IsRecieve"] = $Post_Data ["recieve"];
			$Data ["Address_Mobile"] = $Post_Data ["Mobile"];
		}
		
		
		$Data ["Order_Type"] = "shop";
		
		$owner = get_Owner($rsConfig,$this->UsersID);
		$Data['Owner_ID'] = $owner['id'];

		$error = false;
		if(empty($_SESSION [$cart_key])){
			$Data=array(
						"msg" => "无效数据!你可能已提交过该产品",
						"status"=>0,
						"url" => "/api/".$this->UsersID."/shop/"
					);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			return;
		}else{
			$OrderCart = $_SESSION [$cart_key];
		}
		
		$CartList = json_decode($OrderCart,true);
		
		//获取每个供货商的运费默认配置
		$Biz_ID_String = implode(',',array_keys($CartList));
		$condition = "where Users_ID  = '".$this->UsersID."' and Biz_ID in (".$Biz_ID_String.")";
		$rsBizConfigs = $this->DB->get('biz','Biz_ID,Biz_Name,Shipping,Default_Shipping,Default_Business',$condition);
		$Biz_Config_List = $this->DB->toArray($rsBizConfigs);
		
		$Biz_Config_Dropdown = array();

		foreach($Biz_Config_List as $key=>$item){
			$Biz_Config_Dropdown[$item['Biz_ID']] = $item;
		}
		
		//生成订单
		$orderids = array();
		$pre_total = 0;
		$pre_sn = build_pre_order_no();
		$pre_sn = 'PRE'.$pre_sn;
		
		
		//此次下单是否为分销行为
		$is_distribute = is_distribute_action($User_ID, $rsConfig['Dis_Self_Bonus']);
	
		if($is_distribute){
			Dis_Record::observe(new DisRecordObserver());
		}
		
		foreach($CartList as $Biz_ID=>$BizCart){
			
			$Data["Biz_ID"] = $Biz_ID;
			$Data ["Order_Remark"] = $Post_Data ["Remark"][$Biz_ID];
			//整理快递信息
			if ($virtual == 0){
				$biz_company_dropdown = get_front_shiping_company_dropdown($this->UsersID,$Biz_Config_Dropdown[$Biz_ID]);
				if(count($biz_company_dropdown) >0 ){
					$express = !empty($biz_company_dropdown[$Post_Data['Shiping_ID_'.$Biz_ID]])?$biz_company_dropdown[$Post_Data['Shiping_ID_'.$Biz_ID]]:'';
					$price = !empty($Post_Data['Biz_Shipping_Fee'][$Biz_ID])?$Post_Data['Biz_Shipping_Fee'][$Biz_ID]:0;
					$shipping = array('Express'=>$express,'Price'=>$price);
				}else{
				   $shipping = array(); 
				}
				
			}else{
				 $shipping = array(); 
			}
		
			$Data["Order_Shipping"] = json_encode($shipping,JSON_UNESCAPED_UNICODE);	
			$Data["Order_CartList"]= json_encode($BizCart,JSON_UNESCAPED_UNICODE);
			
			$total_price = 0;

		    //计算你购物车中产品价格
		  
			foreach($BizCart as $Product_ID=>$Product_List){
				$this->DB->get('shop_products','*',' where Products_ID='.$Product_ID);
				$product = $this->DB->toArray();
				foreach($Product_List as $k=>$v){
					$total_price+=$v["Qty"]*$v["ProductsPriceX"];
				}
			}
			
			$Data["Order_TotalAmount"]=$total_price+(empty($Post_Data["Biz_Shipping_Fee"][$Biz_ID])?0:$Post_Data["Biz_Shipping_Fee"][$Biz_ID]);
			$Data["Order_NeedInvoice"] = isset($Post_Data['Order_NeedInvoice'][$Biz_ID])?$Post_Data['Order_NeedInvoice'][$Biz_ID]:0;
			$Data["Order_InvoiceInfo"] = isset($Post_Data['Order_InvoiceInfo'][$Biz_ID])?$Post_Data['Order_InvoiceInfo'][$Biz_ID]:"";
			//优惠券使用判断
			

			if(isset($Post_Data["CouponID"][$Biz_ID])){//优惠券使用判断
				require_once ($_SERVER ["DOCUMENT_ROOT"] .'/control/shop/coupon_use.class.php');
				$coupon_use = new coupon_use($this->DB);
				$res = $coupon_use->coupon_check($Post_Data["CouponID"][$Biz_ID],$User_ID,$total_price,$Biz_ID,$this->UsersID);
				$Data = array_merge($Data,$res);
			}
			
			$Data["Order_TotalPrice"]=$total_price+(empty($Post_Data["Biz_Shipping_Fee"][$Biz_ID])?0:$Post_Data["Biz_Shipping_Fee"][$Biz_ID]);
			$pre_total += $Data["Order_TotalPrice"]-(isset($Data["Coupon_Cash"])?$Data["Coupon_Cash"]:0);
			$Data["Order_CreateTime"]=time();
			$Data["Order_Status"]= ($rsConfig["CheckOrder"] == 1) ? 1 : 0;
			
			$Flag=$this->DB->Add("user_order",$Data);
			
			if($Flag){
				$neworderid = $this->DB->insert_id();
				$orderids[] = $neworderid;
				//更新销售记录
				//其中$kk为产品ID,$k为 Cart_ID,这里的Cart_ID是三维数组中
				//具有不同属性的相同产品在购物车数组中的key
				
				foreach($BizCart as $kk=>$vv){
					$qty = 0;
					
					foreach($vv as $k=>$v){
						$qty += $v['Qty'];
						//加入分销记录
						if($is_distribute){
							begin_trans(); //开始事务
							add_distribute_record($this->UsersID,$v['OwnerID'],$v['ProductsPriceX'],$kk,$v['Qty'],$neworderid,$k);
						}
					}
					$Product = get_product_distribute_info($this->UsersID, $kk);
					
					$comm = new Salesman_commission();
					$salesman = $comm->handout_salesman_commission($neworderid,$Product,$qty);
					
					$condition ="where Users_ID='".$this->UsersID."' and Products_ID=".$kk;
					$this->DB->set('shop_products','Products_Sales=Products_Sales+'.$qty.',Products_Count=Products_Count-'.$qty,$condition);
				}
			}else{
				$error = true;
			}
		}
	
		
		$_SESSION[$cart_key] = "";
		if($error){
			$Data=array(
				"status"=>0
			);
		}else{
			if($rsConfig["CheckOrder"]==1){
				$Data = array(
					"usersid"=>$this->UsersID,
					"userid"=>$User_ID,
					"pre_sn"=>$pre_sn,
					"orderids"=>implode(",",$orderids),
					"total"=>$pre_total,
					"createtime"=>time(),
					"status"=>1
				);
				$flag = $this->DB->Add("user_pre_order",$Data);
				if($flag){
					$url = "/api/".$this->UsersID."/shop/cart/payment/".$pre_sn."/";
					$Data=array(
						"url" => $url,
						"status"=>1
					);
				}else{
					$Data=array(
						"msg" => "订单提交失败",
						"status"=>0
					);
				}
			}else{
				$url = "/api/".$this->UsersID."/shop/member/status/0/";
				$Data=array(
					"url" => $url,
					"status"=>1
				);
			}
		}		
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		return $neworderid;
	}
	//线下订单提交
	function offline_order_create($User_ID, $Post_Data, $rsConfig){
		$Data = array (
			"Users_ID" => $this->UsersID,
			"User_ID" => $User_ID 
		);		
		
		$cart_key = $this->UsersID . "Virtual";//虚拟产品对待
		$Data ["Order_IsVirtual"] = 1;
		$Data ["Order_IsRecieve"] = 1;
		$Data ["Address_Mobile"] = empty($_SESSION[$this->UsersID.'User_Mobile']) ? '' : $_SESSION[$this->UsersID.'User_Mobile'];

        $Data ["Order_Type"] = "offline_st";
		$dis_config = dis_config($this->UsersID);
		//合并参数
		$rsConfig = array_merge($rsConfig,$dis_config);
		$owner = get_Owner($rsConfig, $this->UsersID);
		$Data['Owner_ID'] = $owner['id'];

		$error = false;
        $myRedis = new Myredis($this->DB, 7);
        $redisCart = $myRedis->getCartListValue($cart_key, $User_ID);
        if(empty($redisCart)){
			$Data = array(
						"msg" => "无效数据!你可能已提交过该产品",
						"status"=>0,
						"url" => "/api/".$this->UsersID."/shop/"
					);
			echo json_encode($Data, JSON_UNESCAPED_UNICODE);
			return;
		}else{
			$OrderCart = $redisCart;
		}
        $CartList = json_decode($OrderCart,true);
		
		//生成订单
		$orderids = array();
		$pre_total = 0;
		$pre_sn = build_pre_order_no();
		$pre_sn = 'PRE'.$pre_sn;
		
		
		//此次下单是否为分销行为
		$is_distribute = is_distribute_action($User_ID, $rsConfig['Dis_Self_Bonus']);
	
		if($is_distribute){
			Dis_Record::observe(new DisRecordObserver());
		}
		
		foreach($CartList as $Biz_ID => $BizCart){
			
			$Data["Biz_ID"] = $Biz_ID;
			$Data ["Order_Remark"] = '';

			$shipping = array(); 
		
			$Data["Order_Shipping"] = json_encode($shipping, JSON_UNESCAPED_UNICODE);	
			$Data["Order_CartList"] = json_encode($BizCart, JSON_UNESCAPED_UNICODE);
			
			$total_price = 0;
			$ProductsWebTotal = 0;
			$total_pricey = 0;
			
		    //计算你购物车中产品价格
			foreach($BizCart as $Product_ID => $Product_List){
				foreach($Product_List as $k=>$v){
					$total_price+=$v["Qty"]*$v["ProductsPriceX"];
					$total_pricey+=$v["Qty"]*$v["ProductsPriceY"];
					$ProductsWebTotal+=$v["Qty"]*$v["ProductsWebTotal"];
				}
			}
			$cash = 0;
	    if(isset($Post_Data["CouponID"]) && !empty($_POST["CouponID"])){//优惠券使用判断
			$CouponID = $_POST["CouponID"];
			$r = $this->DB->GetRs("user_coupon_record","*","where Coupon_ID=".$CouponID." and Users_ID='".$this->UsersID."' and User_ID=".$User_ID." and Coupon_UseArea=1 and (Coupon_UsedTimes=-1 or Coupon_UsedTimes>0) and Coupon_StartTime<=".time()." and Coupon_EndTime>=".time()." and Coupon_Condition<=".$total_pricey." and Biz_ID=".$Post_Data['BizID']);
			if($r){
				$Data["Coupon_ID"] = $CouponID;
				if($r["Coupon_UseType"]==0 && $r["Coupon_Discount"]>0 && $r["Coupon_Discount"]<1){
					$cash = $total_pricey * (1 - $r["Coupon_Discount"]);
					$cash = number_format($cash, 2, '.', '');
					$Data["Coupon_Discount"] = $r["Coupon_Discount"];					
				}
				if($r['Coupon_UseType'] == 1 && $r['Coupon_Cash'] > 0){
					$cash = $r['Coupon_Cash'];					
				}
	            if($total_pricey - $cash < 1){
                    $tip = array(
                        "status" => 0,
                        "msg" => "使用优惠券抵现后的金额不能少于1元！"
                    );
                    echo json_encode($tip, JSON_UNESCAPED_UNICODE);exit;
                }
				$Data["Coupon_Cash"] = $cash;
				$Logs_Price = $cash;
				
				if($r['Coupon_UsedTimes'] >= 1){
					$this->DB->Set("user_coupon_record","Coupon_UsedTimes=Coupon_UsedTimes-1","where Users_ID='".$this->UsersID."' and User_ID='".$User_ID."' and Coupon_ID=".$_POST['CouponID']);
				}
				$item = $this->DB->GetRs("user","User_Name","where User_ID=".$User_ID);
				$Data1 = array(
					'Users_ID'=>$this->UsersID,
					'User_ID'=>$_SESSION[$this->UsersID."User_ID"],
					'User_Name'=>$item["User_Name"],
					'Coupon_Subject'=>"优惠券编号：".$CouponID,
					'Logs_Price'=>$Logs_Price,
					'Coupon_UsedTimes'=>$r['Coupon_UsedTimes']>=1?$r['Coupon_UsedTimes']-1:$r['Coupon_UsedTimes'],
					'Logs_CreateTime'=>time(),
					"Biz_ID"=>$Post_Data['BizID'],
				
					'Operator_UserName' => "微商城购买商品"
				);
				$this->DB->Add("user_coupon_logs", $Data1);
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
			
			$Data["Web_Price"] = $ProductsWebTotal - $Data["Coupon_Cash"];
			$Data["Web_Pricejs"] = $ProductsWebTotal;
			$Data["Order_TotalAmount"] = $total_pricey;
			$Data["Order_NeedInvoice"] = 0;//是否需要发票
			$Data["Order_InvoiceInfo"] = '';
			
			
			$Data["Order_TotalPrice"] = $total_price;
			$pre_total += $Data["Order_TotalPrice"];
			$Data["Order_CreateTime"] = time();
			$Data["Order_Status"] = 1;
			
			$Flag = $this->DB->Add("user_order",$Data);
			
			if($Flag){
				$neworderid = $this->DB->insert_id();
				$orderids[] = $neworderid;
				//更新销售记录
				//其中$kk为产品ID,$k为 Cart_ID,这里的Cart_ID是三维数组中
				//具有不同属性的相同产品在购物车数组中的key
				
				foreach($BizCart as $kk => $vv){
					$qty = 0;
					
					foreach($vv as $k => $v){
						$qty += $v['Qty'];
						//加入分销记录
						if($is_distribute){ 
						    begin_trans(); //开始事务
							add_offline_distribute_record($this->UsersID, $v['OwnerID'], $v['ProductsPriceX'], $Biz_ID, $v['Qty'], $neworderid, $k);
						}
					}
					$Product = get_offline_product_distribute_info($this->UsersID, $Biz_ID, $total_price);
					require_once($_SERVER["DOCUMENT_ROOT"].'/include/compser_library/Salesman_Commission.php');
					$comm = new Salesman_commission();
				    $salesman = $comm->handout_salesman_commission($neworderid,$Product,$qty);
				}
			}else{
				$error = true;
			}
		}
	
		require_once(CMS_ROOT.'/include/library/weixin_message.class.php');
		$weixin_message = new weixin_message($this->DB,$this->UsersID,$User_ID);
		$contentStr = '您已成功提交微商城订单，<a href="http://'.$_SERVER["HTTP_HOST"].'/api/'.$this->UsersID.'/shop/member/detail/'.$neworderid.'/">查看详情</a>';
		$weixin_message->sendscorenotice($contentStr);

        $myRedis->clearCartListValue($cart_key, $User_ID);
		if($error){
			$Data=array(
				"status"=>0,
                'msg'=>'订单提交失败！',
			);
		}else{
			$Data = array(
				"usersid"=>$this->UsersID,
				"userid"=>$User_ID,
				"pre_sn"=>$pre_sn,
				"orderids"=>implode(",",$orderids),
				"total"=>$pre_total,
				"createtime"=>time(),
				"status"=>1
			);
			$flag = $this->DB->Add("user_pre_order",$Data);
			if($flag){
				$url = "/api/".$this->UsersID."/shop/cart/payment/".$pre_sn."/";
				$Data = array(
					"url" => $url,
					'msg' => '提交成功',
					'orderid' => $pre_sn,
					"status"=>1
				);
			}else{
				$Data=array(
					"msg" => "订单提交失败",
					"status"=>0
				);
			}
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);exit;
	}
	/*
	*	订单创建前商品数量修改
	*	@param int $Post_Data 订单数据
	*	@echo json 新订单数据
	*/
	function checkout_qty($User_ID,$Post_Data,$smarty){
		
		$rsConfig = $this->DB->GetRs ( "shop_config", "*", "where Users_ID='" . $this->UsersID . "'" );
		
		$virtual = isset ( $Post_Data ['virtual'] ) ? $Post_Data ['virtual'] : 0;
		$needcart = isset ( $Post_Data ['needcart'] ) ? $Post_Data ['needcart'] : 1;
		$Shipping_IDS = isset ( $Post_Data ['Shipping_ID'] ) ? organize_shipping_id ( $Post_Data ['Shipping_ID'] ) : array ();

		$cart_key = get_cart_key ( $this->UsersID, $virtual, $needcart );
		$CartList = json_decode ( $_SESSION [$cart_key], true );

		$Qty = $Post_Data ["_Qty"];
		$k = explode ( "_", $Post_Data ["_CartID"] );
		if(count($CartList [$k [0]] [$k [1]] [$k [2]])<=1){
			$Data = array (
				"status" => 0,
				"msg" => "请勿重复提交",
				"url" => "/api/".$this->UsersID."/shop/products/".$k[1]."/"
			);
			echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );
			return;
		}
		$CartList [$k [0]] [$k [1]] [$k [2]] ["Qty"] = $Qty;
		$price = $CartList [$k [0]] [$k [1]] [$k [2]] ["ProductsPriceX"];
		$_SESSION [$cart_key] = json_encode ( $CartList, JSON_UNESCAPED_UNICODE );
		
		if ($virtual == 1) {
			$total_info = get_order_total_info ( $this->UsersID, $CartList, $rsConfig, array (), 0 );
			if(empty($total_info["total"])){
				foreach($CartList as $bizid=>$value){
					foreach($value as $productsid=>$vv){
						foreach($vv as $key=>$v){
							$total_info["total"] += $v["ProductsPriceX"]*$v["Qty"];
						}
					}
				}
			}
		} else {
			$total_info = get_order_total_info ( $this->UsersID, $CartList, $rsConfig, $Shipping_IDS, $Post_Data ['City_Code'] );
		}
		
		// 计算所需运费
		$Info = array (
			'Qty' => $Qty,
			'Weight' => $CartList [$k [0]] [$k [1]] [$k [2]] ["ProductsWeight"],
			'Price' => $CartList [$k [0]] [$k [1]] [$k [2]] ["ProductsPriceX"] 
		);
		
		/* 免运费商品运费为零 */
		if ($Post_Data ['virtual'] == 1) {
			$shipping_fee = 0;
		} else {
			$shipping_fee = 0;
		}
		
		if (! empty ( $rsConfig ['Integral_Convert'] )) {
			$integral = intval(($total_info ['total'] + $total_info ['total_shipping_fee']) / abs ( $rsConfig ['Integral_Convert'] ));
		} else {
			$integral = 0;
		}
		
		$reduce = ! empty ( $total_info ['reduce'] ) ? $total_info ['reduce'] : 0;
		
		/* 获取可用优惠券信息 */
		// 获取优惠券
		
		$coupon_info = get_useful_coupons ( $User_ID, $this->UsersID, $k [0],$total_info ['total'] );
		if ($coupon_info ['num'] > 0) {
			$coupon_html = build_coupon_html( $smarty, $coupon_info );
		} else {
			$coupon_html = '';
		}
		
		$Data = array (
				"status" => 1,
				"Sub_Total" => $Info ['Price'] * $Info ['Qty'] + $shipping_fee,
				"Sub_Qty" => $Info ['Qty'],
				"price" => $price,
				"biz_shipping_name"=>isset($total_info [$k [0]] ['Shipping_Name']) ? $total_info [$k [0]] ['Shipping_Name'] : '',
				"biz_shipping_fee" =>isset($total_info [$k [0]] ['total_shipping_fee']) ? $total_info [$k [0]] ['total_shipping_fee'] : 0,
				"total_shipping_fee" => $total_info ['total_shipping_fee'],
				"total" => $total_info ['total'],
				"reduce" => $reduce,
				"integral" => $integral,
				"coupon_html" => $coupon_html 
		);
		
		echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );
	}
	
	/*
	*	订单创建前物流信息
	*	@param int $Post_Data 订单数据
	*	@echo json 物流信息
	*/
	function checkout_shipping($Post_Data){
		
		$rsConfig = $this->DB->GetRs ( "shop_config", "*", "where Users_ID='" . $this->UsersID . "'" );
		$virtual = isset ( $Post_Data ['virtual'] ) ? $Post_Data ['virtual'] : 0;
		$needcart = isset ( $Post_Data ['needcart'] ) ? $Post_Data ['needcart'] : 1;
		$Shipping_IDS = isset ( $Post_Data ['Shipping_ID'] ) ? organize_shipping_id ( $Post_Data ['Shipping_ID'] ) : array ();
		$City_Code = $Post_Data ['City_Code'];
		$Biz_ID = isset($Post_Data ['Biz_ID'])? $Post_Data ['Biz_ID']:0;
		$cart_key = get_cart_key ( $this->UsersID, $virtual, $needcart );
		$CartList = json_decode ( $_SESSION [$cart_key], true );
		
		$total_info = get_order_total_info ( $this->UsersID, $CartList, $rsConfig, $Shipping_IDS, $City_Code );
		
		//edit by xpc 20151026
		if (! empty ( $rsConfig ['Integral_Convert'] )) {
			$integral = intval(($total_info ['total'] + $total_info ['total_shipping_fee']) / abs ( $rsConfig ['Integral_Convert'] ));
		} else {
			$integral = 0;
		}
		//end
		
		$Data = array();
		if($Biz_ID != 0){
			$Data["biz_shipping_name"] = $total_info[$Biz_ID]['Shipping_Name'];
			$Data["biz_shipping_fee"] = $total_info[$Biz_ID]['total_shipping_fee'];
		}
		$Data["status"] = 1;
		$Data["total_shipping_fee"] = $total_info['total_shipping_fee'];
		$Data["total"] = $total_info ['total'] ;
		$Data["stauts"] = 1;
		$Data["total_shipping_fee"] = $total_info['total_shipping_fee'];
		//edit by xpc 20151026
		$Data["integral"] = $integral;
		//end
		
		echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );
	}
	
	/*
	*	订单创建前产品属性信息
	*	@param int $Post_Data 订单数据
	*	@echo json 属性信息
	*/
	function checkout_property($request){
		
		$Products_ID = $request ["ProductsID"];
		$response = array (
				'err_msg' => '',
				'result' => '',
				'qty' => 1 
		);

		$attr_id = isset ( $request ['attr'] ) ? $request ['attr'] : array ();
		$qty = (isset ( $request ['qty'] )) ? intval ( $request ['qty'] ) : 1;
		$no_attr_price = $request ['no_attr_price'];
		$UsersID = $request ['UsersID'];
		
		if ($Products_ID == 0) {
			$response ['msg'] = '无产品ID';
			$response ['status'] = 0;
		} else {
			if ($qty == 0) {
				$response ['qty'] = $qty = 1;
			} else {
				$response ['qty'] = $qty;
			}
			
			$shop_price = get_final_price ( $no_attr_price, $attr_id, $Products_ID, $UsersID );
			$response ['result'] = $shop_price;
		}
		
		echo json_encode ( $response, JSON_UNESCAPED_UNICODE );
	}
}
?>