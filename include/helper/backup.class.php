<?php
class backup{
	var $db;
	var $usersid;

	function __construct($DB,$usersid){
		$this->db = $DB;
		$this->usersid = $usersid;	
	}
	
	public function add_backup($rsOrder, $productid, $cartid, $qty, $reason, $account){
		$orderid = $rsOrder["Order_ID"];
		$allQty = $rsOrder["All_Qty"];
		$CartList=json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]),true);
		$ShippingList=json_decode(htmlspecialchars_decode($rsOrder["Order_Shipping"]),true);
		$item = $CartList[$productid][$cartid];


        $product = $this->db->GetRs("shop_products","Products_IsVirtual","WHERE Products_ID=". intval($productid));
		//虚拟产品物流费用为0
		if ($product['Products_IsVirtual'] == 1) {
		 			$ShippingMoney = 0;
		} else {
		 			$ShippingMoney = $ShippingList['Price'];
		
		}
		unset($product);
		
		$item["Qty"] = $qty;
		if(!empty($item['Property']) && isset($item['Property']['shu_pricesimp'])){
			$ProductsPriceX = $item['Property']['shu_pricesimp'];
		}else{
			$ProductsPriceX = $item['ProductsPriceX'];
		}
		$skuvaljosn = '';
		//获得更新的产品的属性库存，保存到退货表
		foreach ($CartList as $Product_ID=>$Product_List) {
			foreach ($Product_List as $k => $v) {

                if (!empty($v['Property'])) {
					//修改产品属性的库存
					$rsProduct = $this->db->GetRs("shop_products","*","where Users_ID='".$this->usersid."' and Products_ID=".$Product_ID);
					//产品属性详情
					$rsProduct['skuvaljosn'] = json_decode($rsProduct['skuvaljosn'],true);
					//获得加入购买产品的属性skuID 如：[1;4;5]
					$atrid_str = $k;
					//加上退货的件数，剩余多少库存
					if(!empty($rsProduct['skuvaljosn'])){
						foreach($rsProduct['skuvaljosn'] as $key=>$value){
							if($atrid_str == $key){
								$rsProduct['skuvaljosn'][$key]['Txt_CountSon'] += $v["Qty"];
							}
						}
						$skuvaljosn = json_encode($rsProduct['skuvaljosn'],JSON_UNESCAPED_UNICODE);
					}
					
					//更新产品表属性JSON数据
					// $Pro_Data = array('skuvaljosn'=>$skuvaljosn);
					// $DB->Set("shop_products",$Pro_Data,"where Users_ID='".$this->usersid."' and Products_ID=".$Product_ID);
				} else {
					$skuvaljosn = '';
				}
			}
		}
		/*$rsUserConfig = $this->db->GetRs("User_Config","UserLevel","where Users_ID='".$this->usersid."'");
		$discount_list = $rsUserConfig["UserLevel"] ? json_decode($rsUserConfig["UserLevel"],true) : array();
		$rsUser = $this->db->GetRs("user","User_Level","where User_ID=".$rsOrder['User_ID']);
		$user_curagio = 0;
		if(!empty($discount_list)){
			if(count($discount_list)>1){
				$user_curagio = isset($discount_list[$rsUser['User_Level']]['Discount'])?$discount_list[$rsUser['User_Level']]['Discount']:0;
				if (empty($user_curagio)) {
					$user_curagio = 0;
				}
			}
		}*/
		// echo '<pre>';
		// print_R($CartList);
		// print_R($rsProduct['skuvaljosn']);
		// exit;
		if($rsOrder['Order_Status'] >= 2){    //已付款
		    $cpyCarList = $CartList;

            $cpyCarList[$productid][$cartid]["Qty"] = $cpyCarList[$productid][$cartid]["Qty"] - $qty;
		    if($cpyCarList[$productid][$cartid]["Qty"]==0){
		        unset($cpyCarList[$productid][$cartid]);
		    }
		    if(count($cpyCarList[$productid])==0){
		        unset($cpyCarList[$productid]);
		    }

				$amount =  $qty * $ProductsPriceX <= 0.01 ? 0.01 : sprintf("%.2f",substr(sprintf("%.3f", $qty * $ProductsPriceX), 0, -1));

		    if ($rsOrder['Order_Type'] != 'pintuan') {
                //$amount = isset($user_curagio) && !empty($user_curagio) ? $amount * (1 - $user_curagio / 100) : $amount;
                $amount = (!empty($item['user_curagio']) && $item['user_curagio'] > 0 && $item['user_curagio'] <= 100 ? (100 - $item['user_curagio']) / 100 : 1) * $amount;
			}


            $amount = getFloatValue($amount, 2);
		    if(empty($cpyCarList) && $rsOrder['Order_Status'] == 2){
		        $amount += $ShippingMoney;
		    }
		}else{    //已发货
		  $amount = $qty * $ProductsPriceX <= 0.01 ? 0.01 : sprintf("%.2f",substr(sprintf("%.3f", $qty * $ProductsPriceX), 0, -1));;
		}

        $amount_source = $qty * $ProductsPriceX <= 0.01 ? 0.01 : sprintf("%.2f",substr(sprintf("%.3f", $qty * $ProductsPriceX), 0, -1));;
		$Integral_Money = 0;
		if(empty($CartList) && $rsOrder['Order_Type'] != 'pintuan'){
			if(!empty((int)$rsOrder['Coupon_Cash'])){
				$amount -= $rsOrder['Coupon_Cash'];
			}
			if(!empty((int)$rsOrder['Integral_Money'])){
				$amount -= $rsOrder['Integral_Money'];
			}
				
				
		} else {
			if(!empty((int)$rsOrder['Coupon_Cash'])){
				$Coupon_Cash = $rsOrder['Coupon_Cash'] / $allQty * $qty;
				$amount -= $Coupon_Cash;
			}
			if(!empty((int)$rsOrder['Integral_Money'])){
				$Integral_Money = $rsOrder['Integral_Money'] / $allQty * $qty;
				$amount -= $Integral_Money;
			}			
		}
		$time = time();
		$data = array(
			'Back_SN' => $this->build_order_no(),
			'Users_ID'=>$this->usersid,
			'Biz_ID'=>$rsOrder["Biz_ID"],
			'Order_ID'=>$orderid,
			'User_ID'=>$rsOrder["User_ID"],
			'Owner_ID'=>$rsOrder["Owner_ID"],
			'Back_Type'=>'shop',
			'Back_Json'=>json_encode($item,JSON_UNESCAPED_UNICODE),
			'Back_Status'=>0,
			'Back_CreateTime'=>$time,
			'Back_Qty'=>$qty,
			'Back_CartID'=>$cartid,
			'Back_Integral' => $Integral_Money,
			'Back_Amount'=>$amount <= 0.01 ? 0.01 : $amount,
			'Back_Amount_Source'=>$amount_source,
			'Back_Account'=>$account,
			'ProductID'=>$productid,
			'Pro_skuvaljosn'=>$skuvaljosn
		);
		$this->db->add('user_back_order',$data);
		$detail = "买家申请退款，退款金额：".($amount)."，退款原因：".$reason;
		$recordid = $this->db->insert_id();
		//增加退款流程记录
		$this->add_record($recordid,0,$detail,$time);
		$CartList[$productid][$cartid]["Qty"] = $CartList[$productid][$cartid]["Qty"] - $qty;
		if($CartList[$productid][$cartid]["Qty"]==0){
		    unset($CartList[$productid][$cartid]);
		}
		if(count($CartList[$productid])==0){
		    unset($CartList[$productid]);
		}
		//
		$rsSales_record = $this->db->Get("distribute_sales_record","Products_Qty,Sales_Money,Level","where Order_ID=".$rsOrder['Order_ID']);
		$rsSales_recordarr = $this->db->toArray($rsSales_record);
		$sales_backarr = array();
		$sales_backarr_souce = array();
		foreach($rsSales_recordarr as $key=>$rssal){
			$sales_backarr[$rssal['Level']] = $rssal['Sales_Money'] / $rssal['Products_Qty'] * $qty;
			$sales_backarr_souce[$rssal['Level']] = $rssal['Sales_Money'];
		}
		
		if (!empty($rsOrder['Back_salems'])) {			
			$rsOrder_salesback = json_decode($rsOrder['Back_salems'],true);
			foreach($rsOrder_salesback as $key=>$val){
				$data = array(
					'Sales_Money'=>($sales_backarr_souce[$key] - $val) < 0 ? 0 : $sales_backarr_souce[$key] - $val
				);
				$this->db->set("distribute_sales_record",$data,"where Level=".$key." and Products_ID=".$productid." and Order_ID=".$rsOrder["Order_ID"]);
			}
		} else {
			$rsOrder_salesback = array();
			foreach($sales_backarr as $key=>$val){
				$data = array(
					'Sales_Money'=>($sales_backarr_souce[$key] - $val) < 0 ? 0 : $sales_backarr_souce[$key] - $val
				);
				$this->db->set("distribute_sales_record",$data,"where Level=".$key." and Products_ID=".$productid." and Order_ID=".$rsOrder["Order_ID"]);
			}
		}
		
		if (!empty($rsOrder_salesback)) {
			foreach($rsOrder_salesback as $key=>$val){
				$sales_backarr[$key] = $sales_backarr[$key] + $val;
			}
		}
		
		if (empty($rsOrder['back_qty_str'])) {
			$backqtyarr = array();
			$backqtyarr[$productid][$cartid] = $qty;
			} else {
				$backqtyarr = json_decode($rsOrder['back_qty_str'],true);
				if (isset($backqtyarr[$productid][$cartid])) {
					$backqtyarr[$productid][$cartid] = $backqtyarr[$productid][$cartid] + $qty;
				}			
			}
			
	    if(!empty($CartList)){			
			if($rsOrder['Order_Status'] == 2){
				$data = array(
					'Order_Status'=>2,
				    'Front_Order_Status'=>2,
				    'Is_Backup'=>1,
					'Order_CartList'=>json_encode($CartList,JSON_UNESCAPED_UNICODE),
					'Back_salems'=>json_encode($sales_backarr,JSON_UNESCAPED_UNICODE),
					'back_qty_str'=>json_encode($backqtyarr,JSON_UNESCAPED_UNICODE),
					'Back_Integral' => $rsOrder["Back_Integral"] + $Integral_Money,
					'back_qty' => $rsOrder["back_qty"] + $qty,
					'Back_Amount'=>$rsOrder["Back_Amount"]+$amount,
					'Back_Amount_Source'=>$rsOrder["Back_Amount_Source"]+$amount_source
				);
			}elseif($rsOrder['Order_Status'] == 3){
				$data = array(
					'Order_Status'=>3,
				    'Front_Order_Status'=>3,
				    'Is_Backup'=>1,
					'Order_CartList'=>json_encode($CartList,JSON_UNESCAPED_UNICODE),
					'Back_salems'=>json_encode($sales_backarr,JSON_UNESCAPED_UNICODE),
					'back_qty_str'=>json_encode($backqtyarr,JSON_UNESCAPED_UNICODE),
					'Back_Integral' => $rsOrder["Back_Integral"] + $Integral_Money,
					'back_qty' => $rsOrder["back_qty"] + $qty,
					'Back_Amount'=>$rsOrder["Back_Amount"]+$amount,
					'Back_Amount_Source'=>$rsOrder["Back_Amount_Source"]+$amount_source
				);
			}
		}else{
			$data = array(
			    'Order_Status'=>5,
			    'Is_Backup'=>1,
			    'Front_Order_Status'=>$rsOrder['Order_Status'],
				'Order_CartList'=>json_encode($CartList,JSON_UNESCAPED_UNICODE),
				'Back_salems'=>json_encode($sales_backarr,JSON_UNESCAPED_UNICODE),
				'back_qty_str'=>json_encode($backqtyarr,JSON_UNESCAPED_UNICODE),
				'Back_Integral' => $rsOrder["Back_Integral"] + $Integral_Money,
				'back_qty' => $rsOrder["back_qty"] + $qty,
				'Back_Amount'=>$rsOrder["Back_Amount"]+$amount,
				'Back_Amount_Source'=>$rsOrder["Back_Amount_Source"]+$amount_source
			);
			
		}
		
		$this->db->set("user_order",$data,"where Order_ID=".$rsOrder["Order_ID"]);
		/*
		if($rsOrder["Order_Status"]==2 && $rsOrder["Order_IsVirtual"]==0){//已付款,商家未发货订单退款
			$this->update_backup("seller_recieve",$recordid,$amount."||%$%已付款/商家未发货订单退款，系统自动完成");		
		}*/
		//增加退款佣金记录
		$this->update_distribute_money($rsOrder["Order_ID"],$productid,$cartid,$qty,0,$allQty, $rsOrder);
	}
	
	public function update_backup($action,$backid,$reason=''){
		$time = time();
		$backinfo = $this->db->GetRs("user_back_order","*","where Back_ID=".$backid);
		switch($action){
			case 'seller_agree'://卖家同意
				$detail = '卖家同意退款';
				//增加流程
				$this->add_record($backid,1,$detail,$time);
				// 更新产品属性库存
				// if(!empty($backinfo['Pro_skuvaljosn'])){
					// $this->db->Set("shop_products",array('skuvaljosn'=>$backinfo['Pro_skuvaljosn']),"where Users_ID='".$this->usersid."' and Products_ID=".$backinfo['ProductID']);
				// }
		        $Data = array(
					"Back_Status"=>1,
					"Buyer_IsRead"=>0,
					"Back_UpdateTime"=>$time
				);
				$this->db->Set("user_back_order",$Data,"where Back_ID=".$backid);
			break;
			case 'seller_agrees'://卖家同意
			    $detail = '卖家同意退款';
			    //增加流程
			    $this->add_record($backid,1,$detail,$time);
				// 更新产品属性库存
				// if(!empty($backinfo['Pro_skuvaljosn'])){
					// $this->db->Set("shop_products",array('skuvaljosn'=>$backinfo['Pro_skuvaljosn']),"where Users_ID='".$this->usersid."' and Products_ID=".$backinfo['ProductID']);
				// }
			    //更新退款单
			    $Data = array(
			        "Back_Status"=>3,
			        "Biz_IsRead"=>0,
			        "Back_UpdateTime"=>$time,
			        	
			    );
			    $this->db->Set("user_back_order",$Data,"where Back_ID=".$backid);
			    break;
			case 'seller_reject'://卖家驳回
				$detail = '卖家驳回退款申请，驳回理由：'.$reason;
				
				//增加退款流程
				$this->add_record($backid,1,$detail,$time);
				
				//更新退款单
				$Data = array(
					"Back_Status"=>5,
					"Buyer_IsRead"=>0,
					"Back_UpdateTime"=>$time
				);
				$this->db->Set("user_back_order",$Data,"where Back_ID=".$backid);
					
				$back = $this->db->GetRs("user_back_order","*","where Back_ID=".$backid);
				$backjson = json_decode(htmlspecialchars_decode($back["Back_Json"]),true);
				$Order = $this->db->GetRs("user_order","*","where Order_ID=".$back["Order_ID"]);
				//更新订单
				$CartList=json_decode(htmlspecialchars_decode($Order["Order_CartList"]),true);
				if(empty($CartList[$back["ProductID"]])){
					$CartList[$back["ProductID"]][] = $backjson;
				}else{
					if(empty($CartList[$back["ProductID"]][$back["Back_CartID"]])){
						$CartList[$back["ProductID"]][] = $backjson;
					}else{
						$CartList[$back["ProductID"]][$back["Back_CartID"]]["Qty"] = $CartList[$back["ProductID"]][$back["Back_CartID"]]["Qty"] + $backjson["Qty"];
					}
				}
				if($back['Back_Status'] == 5){
				    if($Order['Order_IsVirtual']==1){  //虚拟商品
				        $Data = array(
				            'Is_Backup'=>0,
				            'Order_Status'=>2,
				            'Order_CartList'=>json_encode($CartList,JSON_UNESCAPED_UNICODE),
				            'Back_Amount'=>$Order["Back_Amount"]-$back["Back_Amount"],
				            'Back_Amount_Source'=>$Order["Back_Amount_Source"]-$back["Back_Amount_Source"]
				        );
				    }else{
				        
    				    $Data = array(
    				        'Order_Status'=>$Order['Front_Order_Status'],
    				        'Is_Backup'=>0,
    				        'Order_CartList'=>json_encode($CartList,JSON_UNESCAPED_UNICODE),
    				        'Back_Amount'=>$Order["Back_Amount"]-$back["Back_Amount"],
							'Back_Amount_Source'=>$Order["Back_Amount_Source"]-$back["Back_Amount_Source"]
    				    );
				    }	
				}else{
				    $Data = array(
				        'Is_Backup'=>0,
				        'Order_Status'=>2,
				        'Order_CartList'=>json_encode($CartList,JSON_UNESCAPED_UNICODE),
				        'Back_Amount'=>$Order["Back_Amount"]-$back["Back_Amount"],
						'Back_Amount_Source'=>$Order["Back_Amount_Source"]-$back["Back_Amount_Source"]
				    );
				}

				$this->db->Set("user_order",$Data,"where Order_ID=".$back["Order_ID"]);
				
				//增加退款佣金记录
				$bonus = $this->update_distribute_money($back["Order_ID"],$back["ProductID"],$back["Back_CartID"],$backjson["Qty"],1);
				if($Order["Order_Status"]==4){
					//增加销售记录
					$this->add_sales_record($back, $bonus);
				}
			break;
			case 'buyer_send':
				$arr = explode("||%$%",$reason);
				$detail = '买家已发货，物流方式：'.$arr[0]."，物流单号：".$arr[1];
				
				//增加流程
				$this->add_record($backid,1,$detail,$time);
				
				//更新退款单
				$Data = array(
					"Back_Status"=>2,
					"Biz_IsRead"=>0,
					"Back_Shipping"=>$arr[0],
					"Back_ShippingID"=>$arr[1],
					"Back_UpdateTime"=>$time
				);
				$this->db->Set("user_back_order",$Data,"where Back_ID=".$backid);
			break;
			case 'seller_recieve':
				$arr = explode("||%$%",$reason);
				$backinfo = $this->db->GetRs("user_back_order","Back_Amount,Back_Amount_Source,Order_ID,Pro_skuvaljosn,ProductID","where Back_ID=".$backid);
				$orderinfo = $this->db->GetRs("user_order","Back_Amount,Back_Amount_Source,Order_IsVirtual","where Order_ID=".$backinfo["Order_ID"]);
				if($orderinfo["Order_IsVirtual"]==1){
					$detail = '卖家已同意并确定了退款金额，退款金额为：'.$arr[0]."，理由：".$arr[1];
				}else{
					$detail = '卖家已收货并确定了退款金额，退款金额为：'.$arr[0]."，理由：".$arr[1];
				}
				// 更新产品属性库存
				// if(!empty($backinfo['Pro_skuvaljosn'])){
					// $this->db->Set("shop_products",array('skuvaljosn'=>$backinfo['Pro_skuvaljosn']),"where Users_ID='".$this->usersid."' and Products_ID=".$backinfo['ProductID']);
				// }
				//增加流程
				$this->add_record($backid,1,$detail,$time);
				
				//更新退款单 
				$Data = array(
					"Back_Status"=>3,
					"Buyer_IsRead"=>0,
					"Back_Amount"=>$arr[0],
					"Back_UpdateTime"=>$time
				);
				$this->db->Set("user_back_order",$Data,"where Back_ID=".$backid);
				$amount = $orderinfo["Back_Amount"]+$arr[0]-$backinfo["Back_Amount"];				
				$Data = array(
					"Back_Amount"=>$amount>0 ? $amount : 0
				);
				$this->db->Set("user_order",$Data,"where Order_ID=".$backinfo["Order_ID"]);
			break;
			case 'admin_backmoney'://卖家同意
        require_once($_SERVER["DOCUMENT_ROOT"] .'/pay/wxpay2/refund.php');
        require_once($_SERVER["DOCUMENT_ROOT"] . '/pay/alipay/refund.php');
				$Order = $this->db->GetRs("user_order","*","where Order_ID=".$backinfo["Order_ID"]);			
				//微信支付，支付宝支付，余额支付
				$method = $Order['Order_PaymentMethod'];
				$PaymentMethod = array(
					"微支付" => "1",
					"支付宝" => "2",
					"余额支付" => "3",
					"商派天工支付" => "4",
					"线下支付" => "5"
				);
				
				$user_data = $this->db->GetRs("user","*","where User_ID=".$Order['User_ID']." AND Users_ID='".$Order['Users_ID']."'");
				$rsConfig =  $this->db->GetRs("shop_config","*","where Users_ID='".$Order['Users_ID']."'");
				//分销相关设置
				$dis_config = dis_config($Order['Users_ID']);
				//合并参数
				
				$user_data = $this->db->GetRs("user","*","where User_ID=".$Order['User_ID']." AND Users_ID='".$Order['Users_ID']."'");
				if ($PaymentMethod[$method]==1) { //微支付
					$weixinflag = refund($Order,$backid,$this->usersid);
					if($weixinflag['status']){
                        //更新退款单
                        $Data = array(
                            "Back_Status"=>4,
                            "Buyer_IsRead"=>0,
                            "Back_IsCheck"=>1,
                            "Back_UpdateTime"=>$time
                        );
                        $this->db->Set("user_back_order",$Data,"where Back_ID=".$backid);
                        $Order_CartList = json_decode($Order['Order_CartList'],true);
						if (!empty(floatval($Order['Order_Yebc']))) {
						DB::table("user")->where(['User_ID' => $Order["User_ID"]])->update([
                            "User_Money" => $rsUser['User_Money'] + $Order['Order_Yebc']
                        ]);
						}
                        if(empty($Order_CartList)){
                            Order::where("Order_ID", $Order['Order_ID'])->update([
                                'Order_Status' =>4
                            ]);
                        }
					}else{
                        return [
                            'status' => 0,
                            'msg' => $weixinflag['msg']
                        ];
                    }

				} elseif ($PaymentMethod[$method]==2) { //支付宝
					$result = alipay_refund($backid);
                    if(isset($result['status']) && $result['status'] == 0){
                        return [
                            'status' => 0,
                            'msg' => $result['msg']
                        ];
                    }
				} elseif ($PaymentMethod[$method]==3) { //余额支付
					$User_Money = $backinfo['Back_Amount'] + $user_data['User_Money'];
					$Data = array(
						"User_Money" => $User_Money,
					);
					$this->db->Set("user",$Data,"where User_ID=".$Order['User_ID']." AND Users_ID='".$Order['Users_ID']."'");
					//更新退款单
					$Data = array(
						"Back_Status"=>4,
						"Buyer_IsRead"=>0,
						"Back_IsCheck"=>1,
						"Back_UpdateTime"=>$time
					);
					$this->db->Set("user_back_order",$Data,"where Back_ID=".$backid);
                    $Order_CartList = json_decode($Order['Order_CartList'],true);
					if (!empty(floatval($Order['Order_Yebc']))) {
						DB::table("user")->where(['User_ID' => $Order["User_ID"]])->update([
                            "User_Money" => $rsUser['User_Money'] + $Order['Order_Yebc']
                        ]);
						}
                    if(empty($Order_CartList)){
                        Order::where("Order_ID", $Order['Order_ID'])->update([
                            'Order_Status' =>4
                        ]);
                    }
				}else if($PaymentMethod[$method]==4){
					$User_Money = $backinfo['Back_Amount'] + $user_data['User_Money'];
					$Data = array(
					  "User_Money" => $User_Money,
					);
					$this->db->Set("user",$Data,"where User_ID=".$Order['User_ID']." AND Users_ID='".$Order['Users_ID']."'");
					$Data = array(
					  "Back_Status"=>4,
					  "Buyer_IsRead"=>0,
					  "Back_IsCheck"=>1,
					  "Back_UpdateTime"=>$time
					);
					$this->db->Set("user_back_order",$Data,"where Back_ID=".$backid);
                    $Order_CartList = json_decode($Order['Order_CartList'],true);
					if (!empty(floatval($Order['Order_Yebc']))) {
						DB::table("user")->where(['User_ID' => $Order["User_ID"]])->update([
                            "User_Money" => $rsUser['User_Money'] + $Order['Order_Yebc']
                        ]);
						}
                    if(empty($Order_CartList)){
                        Order::where("Order_ID", $Order['Order_ID'])->update([
                            'Order_Status' =>4
                        ]);
                    }
				} else if($PaymentMethod[$method]==5){
                    $User_Money = $backinfo['Back_Amount'] + $user_data['User_Money'];
                    $Data = array(
                        "User_Money" => $User_Money,
                    );
                    $this->db->Set("user",$Data,"where User_ID=".$Order['User_ID']." AND Users_ID='".$Order['Users_ID']."'");
                    $Data = array(
                        "Back_Status"=>4,
                        "Buyer_IsRead"=>0,
                        "Back_IsCheck"=>1,
                        "Back_UpdateTime"=>$time
                    );
                    $this->db->Set("user_back_order",$Data,"where Back_ID=".$backid);
                    $Order_CartList = json_decode($Order['Order_CartList'],true);
					if (!empty(floatval($Order['Order_Yebc']))) {
						DB::table("user")->where(['User_ID' => $Order["User_ID"]])->update([
                            "User_Money" => $rsUser['User_Money'] + $Order['Order_Yebc']
                        ]);
						}
                    if(empty($Order_CartList)){
                        Order::where("Order_ID", $Order['Order_ID'])->update([
                            'Order_Status' =>4
                        ]);
                    }
                }
				$CartList=json_decode(htmlspecialchars_decode($Order["Order_CartList"]),true);
				$detail = '管理员已退款给买家';
				
				//增加流程
				$this->add_record($backid,4,$detail,$time);
				$return_money =  $backinfo["Back_Amount"];
				$Data=array(
					'Users_ID'=>$Order['Users_ID'],
					'User_ID'=>$Order['User_ID'],				
					//'Type'=>0,
					'Type'=>1,
					'Amount'=>$backinfo['Back_Amount'],
					'Total'=>$user_data['User_Money']+$backinfo["Back_Amount"],
					'Note'=>"买家退货返还 +".$return_money ." (订单号:".$backinfo["Order_ID"].")",
					'CreateTime'=>time()		
				);
				$this->db->Add('user_money_record',$Data);
			break;
		}
	}
	
	protected function add_record($recordid,$status,$detail,$time){
		$Data = array(
			"backid"=>$recordid,
			"detail"=>$detail,
			"status"=>$status,
			"createtime"=>$time
		);
		$this->db->Add("user_back_order_detail",$Data);
	}
	
	protected function update_distribute_money($orderid,$productid,$cartid,$qty,$type=0,$allQty=1,$orderInfo = []){
		$bonus = array();
		if($type==0){//减少佣金
			$descrition = "订单退款，减少佣金。退款数量：".$qty;
		}else{//增加佣金
			$descrition = "订单退款被驳回，增加佣金。退款数量：".$qty;
		}
		$bonus_list = array();
		
		$this->db->query("select d.* from distribute_account_record as d LEFT JOIN distribute_record as r ON d.Ds_Record_ID=r.Record_ID where r.Order_ID=".$orderid." and r.Product_ID=".$productid." and d.CartID='".$cartid."' group by d.level");
		while($r = $this->db->fetch_assoc()){
			$bonus_list[] = $r;
		}
		/*$cartList = json_decode($orderInfo['Order_CartList'], true);
		$backListJson = json_decode($orderInfo['back_qty_str'], true);
		foreach ($cartList as $k => $v) {
			foreach ($v as $key => $val) {
				foreach ($backListJson as $bk => $bv) {
					foreach ($bv as $bkey => $bval) {
						if ($k == $bk && $key == $bkey) {
							$cartList[$k][$key]['Qty'] += $bval;
						}
					}
				}
			}
		}*/
		if(!empty($bonus_list)){
			foreach($bonus_list as $item){
				$data = $item;//复制数组
				unset($data["Record_ID"]);
				unset($data["deleted_at"]);
				unset($data["Owner_ID"]);
				$amount = $item["Record_Price"] * $qty;
				$data["Record_Money"] = $type==0 ? -$amount : $amount;
				$data["Record_Sn"] = $this->build_order_no();
				$lsQty = $data["Record_Qty"];//从分销记录里拿出下订单时写入的数量
				$data["Record_Qty"] = $qty;
				$data["Record_Description"] = $type==0 ? '用户退款，减少佣金'.$amount.'元' : '用户退款被驳回，增加佣金'.$amount.'元';
				$data["Record_CreateTime"] = time();
				$data["Owner_ID"] = 0;
				//$data['Nobi_Money'] = 0;
				//edit 2017-12-19.
				$data['Nobi_Money'] = $data['Nobi_Money'] / $lsQty * $qty;
				if ($type == 0) {
					$data['Nobi_Description'] = '用户退款，减少爵位奖金' . $data['Nobi_Money'] . '元';
					if ($data['Nobi_Money'] > 0) {
						$data['Nobi_Money'] = -$data['Nobi_Money'];	
					}					
				} else {
					$data['Nobi_Description'] = '用户退款被驳回，增加爵位奖金' . $data['Nobi_Money'] . '元';
					$data['Nobi_Money'] = $data['Nobi_Money'];
				}
								
				$this->db->Add('distribute_account_record', $data);
			}
		}
	}
	
	protected function add_sales_record($item,$bonus){
		$bonus_1 = empty($bonus[1]) ? 0 : $bonus[1];
		$bonus_2 = empty($bonus[2]) ? 0 : $bonus[2];
		$bonus_3 = empty($bonus[3]) ? 0 : $bonus[3];
		$CartList = array();
		$CartList[$item["ProductID"]][] = $item["Back_Json"];
		$Data = array(
			"Users_ID"=>$this->usersid,
			"Order_ID"=>$item["Order_ID"],
			"Order_Json"=>json_encode($CartList,JSON_UNESCAPED_UNICODE),
			"Biz_ID"=>$item["Biz_ID"],
			"Bonus_1"=>$bonus_1,
			"Bonus_2"=>$bonus_2,
			"Bonus_3"=>$bonus_3,
			"Order_Amount"=>$item["Back_Amount"],
			"Order_Diff"=>0,
			"Order_Shipping"=>0,
			"Order_TotalPrice"=>$item["Back_Amount"],
			"Record_CreateTime"=>time()
		);
		$this->db->Add("shop_sales_record",$Data);
	}
	
	protected function build_order_no(){
    	mt_srand((double) microtime() * 1000000);
   	 	return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	}
}
?>
