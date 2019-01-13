<?php
class pay_order{
	var $db;
	var $orderid;

    private $transaction_id;

	function __construct($DB,$orderid){
		$this->db = $DB;
		$this->orderid = $orderid;
	}
	
	private function get_order($orderid){
		$r = $this->db->GetRs("user_order","*","where Order_ID=".$orderid);
		return $r;
	}
	
	private function get_user($userid){
		$r = $this->db->GetRs("user","*","where User_ID=".$userid);
		return $r;
	}
	
	private function get_products($pid){
		$r = $this->db->GetRs("shop_products","*","where Products_ID=".$pid);
		return $r;
	}
	
	private function get_shopconfig($usersid){
		$r = $this->db->GetRs("shop_config","*","where Users_ID='".$usersid."'");
		$r1 = $this->db->GetRs("distribute_config","*","where Users_ID='".$usersid."'");
		$r = array_merge($r,$r1);
		return $r;
	}
	
	private function update_order($orderid,$data){
		$this->db->Set("user_order",$data,"where Order_ID=".$orderid);
	}
	
	private function update_user_integral($user_id,$data){
		$this->db->Set("user",$data,"where User_ID=".$user_id);
	}

    public function setTransactionid($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }
	
	private function pay_orders($orderid, $method = ''){
		
		$rsOrder = $this->get_order($orderid);
		$rsUser = $this->get_user($rsOrder["User_ID"]);
		
		if(!$rsOrder){
			return array("status"=>0,"msg"=>"订单不存在");
		}
		
		$url = '/api/'.$rsOrder["Users_ID"].'/'.$rsOrder["Order_Type"].'/member/status/'.$rsOrder["Order_Status"].'/';
		
		if($rsOrder["Order_Status"]<>1){
			return array("status"=>1,"url"=>$url);
		}
		//更新订单状态
		$Data = array(
			"Order_Status" => 2,
			"Pay_time" => time()
		);
        if (!empty($method) && empty($rsOrder['Order_PaymentMethod'])) {
            $Data['Order_PaymentMethod'] = $method;
            $Data['Order_PaymentInfo'] = $method;
            $Data['Order_DefautlPaymentMethod'] = $method;
        }
        if ($this->transaction_id) {
            $Data['transaction_id'] = $this->transaction_id;
        }
		$this->update_order($orderid, $Data);
		$Order_Yebc = $rsOrder["Order_Yebc"] != '0.00' || $rsOrder["Order_Yebc"] != 0 ? $rsOrder["Order_Yebc"] : 0;
			if ($rsOrder["Order_Yebc"] != '0.00' || $rsOrder["Order_Yebc"] != 0) {				
				$this->db->set('user',"User_Money=User_Money-$Order_Yebc","where User_ID=".$rsOrder["User_ID"]);

				// 写入余额补差记录
                $money_record = array(
                    'Users_ID' => $rsOrder['Users_ID'],
                    'User_ID' => $rsOrder['User_ID'],
                    'Type' => 0,
                    'Amount' => $Order_Yebc,
                    'Total' => $rsUser['User_Money'] - $Order_Yebc,
                    'Note' => ($rsOrder["Order_Type"] == 'pintuan' ? '  拼团' : '  商城') . "购买使用余额补差支出 -" . $Order_Yebc . " (订单号:" . $rsOrder['Order_ID'] . ")",
                    'CreateTime' => time()
                );
                $Flag = $this->db->Add('user_money_record', $money_record);
			}
		//更改业务提成状态为已付款
        require_once($_SERVER["DOCUMENT_ROOT"].'/include/compser_library/Salesman_Commission.php');
		$sales_man = new Salesman_commission();
		$sales_man->up_sales_status($orderid,1);
		if(strpos($rsOrder["Order_Type"],'zhongchou')>-1){
			$url = '/api/'.$rsOrder["Users_ID"].'/zhongchou/orders/';
			return array("status"=>1,"url"=>$url);
		}elseif($rsOrder["Order_Type"]=="kanjia"){
			$url = '/api/'.$rsOrder["Users_ID"].'/user/kanjia_order/status/2';
			return array("status"=>1,"url"=>$url);
		}
		//更新商品销量
		$CartList = json_decode(htmlspecialchars_decode($rsOrder['Order_CartList']),true);
		foreach($CartList as $ProductID=>$product_list){
			$qty = 0;
			foreach($product_list as $key=>$item){
				$qty += $item['Qty'];
			}
			$condition ="where Users_ID='".$rsOrder['Users_ID']."' and Products_ID=".$ProductID;
			$this->db->Set('shop_products','Products_Sales=Products_Sales+'.$qty,$condition);
		}

		if(strpos($rsOrder["Order_Type"],'zhongchou')>-1){
			$url = '/api/'.$rsOrder["Users_ID"].'/zhongchou/orders/';
			return array("status"=>1,"url"=>$url);
		}elseif($rsOrder["Order_Type"]=="kanjia"){
			$url = '/api/'.$rsOrder["Users_ID"].'/user/kanjia_order/status/2';
			return array("status"=>1,"url"=>$url);
		}
		//积分抵用
		$Flag_b = TRUE;
		if($rsOrder['Order_Type'] != 'cloud'){
		if($rsOrder["Integral_Consumption"] > 0 ){
			$Flag_b = change_user_integral($rsOrder["Users_ID"],$rsOrder["User_ID"],$rsOrder["Integral_Consumption"],'reduce','积分抵用消耗积分');
		}
		}
		$integral_up = $rsUser['User_Integral'] - $rsOrder['Integral_Consumption'];
		$integral_up_amote = $rsUser['User_TotalIntegral'] - $rsOrder['Integral_Consumption'];
		//updata 积分		
		$Data = array(
			"User_Integral" => $integral_up,
			"User_TotalIntegral" => $integral_up_amote
		);
		$this->update_user_integral($rsUser['User_ID'], $Data);
		//更改分销账号记录状态,置为已付款		
		if($rsOrder['Order_Type'] != 'cloud'){		
		$Flag_c = Dis_Account_Record::changeStatusByOrderID($orderid,1);
		}
		if($rsOrder["Order_Type"] == 'cloud'){
			$Flag_b = $Flag_c = TRUE;
			$CartList = json_decode(htmlspecialchars_decode($rsOrder['Order_CartList']), true);
			$sql1 = 'UPDATE cloud_products SET canyurenshu = CASE Products_ID';
			$sql2 = "INSERT INTO cloud_record (`User_ID`,`Products_ID`,`Add_Time`,`Cloud_Code`,`qishu`,`Order_ID`) VALUES";
			$codes = get_cloud_code($CartList);

			foreach($CartList as $key => $value){
				$cp_arr[] = $key;
				foreach($value as $j => $v){
					$sql1 .= ' WHEN '.$key.' THEN canyurenshu+'.$v['Qty'];
					for($i = 0; $i < $v['Qty']; $i++){
						$cloud_code = $codes[$key][$i];
						$sql2 .= ' ('.$rsOrder["User_ID"].', '.$key.', "'.microtime(true).'", '.$cloud_code.', '.$v['qishu'].', '.$rsOrder['Order_ID'].'),';
					}
				}
			}
			$cp_ids = implode(',', $cp_arr);
			$sql1 .= ' END WHERE Products_ID IN ('.$cp_ids.')';
			$sql2 = substr($sql2, 0, strlen($sql2)-1).';';
            $this->db->query($sql1);
			$this->db->query($sql2);
		}else{
			handle_products_count($rsOrder["Users_ID"], $rsOrder);
		}
		
		if($Flag_b&&$Flag_c){
			$isvirtual = $rsOrder["Order_IsVirtual"];
			$url = '/api/'.$rsOrder["Users_ID"].'/'.$rsOrder["Order_Type"].'/member/status/2/';
			$rsConfig=$this->get_shopconfig($rsOrder["Users_ID"]);				
			$CartList = json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]), true);
			
			//获取订单中的产品id集
			$productids = array_keys($CartList);
			$is_distribute = $rsUser["Is_Distribute"];//是否分销商标识
			
			//分销商级别数组			
			$level_data = get_dis_level($this->db,$rsConfig['Users_ID']);
			if($rsOrder['Order_Type'] != 'cloud'){
			if($rsUser["Is_Distribute"]==0){//不是分销商
				$LevelID = 0;//分销商级别
				if($rsConfig["Distribute_Type"]==2){//购买商品
					$LevelID = get_user_distribute_level_buy($level_data,$rsConfig,$productids,$rsOrder['User_ID']);
				}elseif($rsConfig["Distribute_Type"]==1){//消费金额
					$LevelID = get_user_distribute_level_cost($level_data,$rsConfig,$rsOrder['Order_TotalPrice'],$rsOrder['User_ID']);
					if (empty($LevelID)) {
					$LevelID = get_user_distribute_level($rsConfig,$level_data,$rsOrder['User_ID'],$rsOrder['Users_ID'],$orderid);
					}
				}
				
				if($LevelID >= 1){
					$is_distribute = 1;
					$f = create_distribute_acccount($rsConfig, $rsUser, $LevelID, '', 1);
				}
				
				//爵位晋级
				if($rsConfig["Pro_Title_Status"]==2){
					$pro = new ProTitle($rsOrder["Users_ID"],$rsUser["Owner_Id"]);
					$flag_x = $pro->up_nobility_level();
				}
			}else{//是分销商判定可提现条件
				$tixian = 0;
				$rsAccount = $this->db->GetRs("distribute_account","User_ID,Enable_Tixian,Account_ID,Is_Dongjie,Is_Delete,Users_ID,Level_ID","where Users_ID='".$rsOrder["Users_ID"]."' and User_ID=".$rsOrder["User_ID"]);
				if($rsAccount){
					//爵位晋级
					if($rsConfig["Pro_Title_Status"]==2){
						$pro = new ProTitle($rsOrder["Users_ID"],$rsOrder["User_ID"]);
						$flag_x = $pro->up_nobility_level();
					}
					
					$is_distribute = 1;
					$account_data = array();
					if($rsAccount["Enable_Tixian"]==0){
						if($rsConfig["Withdraw_Type"] == 0){
							$tixian = 1;
						}elseif($rsConfig["Withdraw_Type"] == 2){
							$arr_temp = explode("|",$rsConfig["Withdraw_Limit"]);
							if($arr_temp[0]==0){
								$tixian = 1;
							}else{
								if(!empty($arr_temp[1])){
									$productsid = explode(",",$arr_temp[1]);
									foreach($productsid as $id){
										if(!empty($CartList[$id])){
											$tixian = 1;
											break;
										}
									}
								}
							}
						}
						if($tixian==1){
							$account_data['Enable_Tixian'] = 1;
						}
					}
					
					// 判定升级级别
                    if ($rsConfig["Distribute_Type"] == 1) { // 消费金额
                        $LevelID = get_user_distribute_level_cost($level_data, $rsConfig, $rsOrder['Order_TotalPrice'],$rsOrder['User_ID']);
                    } else {
                        $two_level_data = next($level_data);
                        if ($two_level_data['Update_switch']) {
                            $LevelID = get_user_distribute_level_upgrade($level_data, $rsConfig, $productids);
                        } else {
                            $LevelID = get_user_distribute_level_buy($level_data,$rsConfig,$productids,$rsOrder['User_ID']);
                        }
                    }

                    if ($LevelID > $rsAccount['Level_ID']) {
                        $account_data['Level_ID'] = $LevelID;
                    }
					
					if(!empty($account_data)){
						$this->db->Set("distribute_account",$account_data,"where Users_ID='".$rsOrder["Users_ID"]."' and Account_ID=".$rsAccount["Account_ID"]);
					}
					
					if($rsConfig["Fuxiao_Open"]==1 && $rsAccount["Is_Delete"]==0){//开启复销功能
						distribute_fuxiao_return_action($this->db,$rsConfig["Fuxiao_Rules"],$rsAccount,$rsUser["User_OpenID"]);//是否达到复销要求并处理
					}
				}
			}
			
			
			if($is_distribute == 1 && $rsUser["Owner_Id"]>0 && $rsConfig["Fanben_Open"]==1 && $rsConfig["Fanben_Type"]==1 && $rsConfig["Fanben_Limit"]){//返本规则开启，下级限制开启，限制条件设置，会员是分销商，有推荐人
				$productids = array_keys($CartList);
				$arr_temp = explode(',',$rsConfig["Fanben_Limit"]);
				$condition = "";
				foreach($productids as $pid){
					if(!in_array($pid,$arr_temp)){
						continue;
					}
					$str_temp = '{"'.$pid.'":';
					$condition = $condition ? " or Order_CartList like '%".$str_temp."%'" : "Order_CartList like '%".$str_temp."%'";
				}
				if(!empty($condition)){
					$condition = "where Order_Status>1 and Order_ID<>".$orderid." and User_ID=".$rsOrder["User_ID"]." and (".$condition.")";
					
					$r_temp = $this->db->GetRs("user_order","count(Order_ID) as num",$condition);
					if($r_temp["num"]==0){
						$Fanben = $rsConfig["Fanben_Rules"] ? json_decode($rsConfig['Fanben_Rules'],true) : array();
						deal_distribute_fanben($Fanben, $rsUser["Owner_Id"]);
					}
				}
			}
			
			$confirm_code = '';
			$cardinfo = '';
			if($rsOrder["Order_IsVirtual"]==1){
				if($rsOrder["Order_IsRecieve"]==1){
					$pids = 0;
					$pqty = 0;

					foreach($CartList as $productsid=>$productsinfo){
						$pids = $productsid;
						foreach ($productsinfo as $pk => $pv) {
							$pqty = $pv['Qty'];
						}
					}

					$cardids = array();
					$this->db->query('select Card_ID,Card_Name,Card_Password from shop_virtual_card where Products_Relation_ID='.$pids.' and Card_Status=0');
					$cardinfo = '';
					$i = 0;
					while($rrr = $this->db->fetch_assoc()){
						if ($pqty > $i) { 
							$cardids[] = $rrr['Card_ID']; 
							$cardinfo .= '卡号:'.$rrr['Card_Name'].',密码:'.$rrr['Card_Password'].';';
						}
						$i++;
					}
					
					if ($cardinfo && $rsOrder["Order_PaymentMethod"] != '后台手动下单') {
						$this->db->query('UPDATE shop_virtual_card SET Card_Status=1 WHERE Card_ID IN('.implode(',', $cardids).')');
						$Data = array('Order_Virtual_Cards'=>$cardinfo);
						$this->update_order($orderid,$Data);
						send_sms($rsOrder["Address_Mobile"], $cardinfo, $rsOrder["Users_ID"]);
					}
					
					Order::observe(new OrderObserver());
					$order = Order::find($orderid);
					$Flag = $order->confirmReceive();
					
					$url="/api/".$rsOrder["Users_ID"]."/".$rsOrder["Order_Type"]."/member/status/4/";
				
				}else{
					$confirm_code = get_virtual_confirm_code($rsOrder["Users_ID"]);
					$Data = array('Order_Code'=>$confirm_code);
					$this->update_order($orderid,$Data);
				}
			}

                $orderidnum = date('Ymd',$rsOrder['Order_CreateTime']) . $orderid;
                $setting = $this->db->GetRs("setting","sms_enabled","where id=1");
				if($rsOrder['addtype'] != 1){
				if($setting["sms_enabled"]==1){
				if($rsConfig["SendSms"]==1){
					if($rsConfig["MobilePhone"]){
						$sms_mess = '您的商品有订单付款，订单号'.$orderidnum.'请及时查看！';
						send_sms($rsConfig["MobilePhone"], $sms_mess, $rsOrder["Users_ID"]);
					}
				}
					if($rsOrder["Order_IsVirtual"]==1 && $rsOrder["Order_IsRecieve"]==0){
						$sms_mess = '您已成功购买商品，订单号'.$orderidnum.'，消费券码为 '.$confirm_code;
						send_sms($rsOrder["Address_Mobile"], $sms_mess, $rsOrder["Users_ID"]);
					}
				
					if($rsOrder["Biz_ID"]>0){
						$biz = $this->db->GetRs("biz","Biz_SmsPhone","where Biz_ID=".$rsOrder["Biz_ID"]);
						if(!empty($biz["Biz_SmsPhone"])){
							$sms_mess = '您的商城有新订单，订单号'.$orderidnum.'，请及时查看';
							send_sms($biz["Biz_SmsPhone"], $sms_mess, $rsOrder["Users_ID"]);
						}
					}
				}
			}
			}
			require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_message.class.php');
			$weixin_message = new weixin_message($this->db,$rsOrder["Users_ID"],$rsOrder["User_ID"]);
                        $rsConfig = $this->db->GetRs("shop_config","DisSwitch,Users_ID","where Users_ID='".$rsOrder["Users_ID"]."'");
                        if ($rsConfig['DisSwitch'] == 1) { 
                                $weixin_message->sendorder($rsOrder["Order_TotalPrice"],$orderid);
                         }
			return array("status"=>1,"url"=>$url);
		}else{
			return array("status"=>0,"msg"=>"订单支付失败");
		}
	}
	
	public function make_pay($method = ''){
		if(strpos($this->orderid,"PRE")>-1){
			
			$pre_order = $this->db->GetRs("user_pre_order","orderids","where pre_sn='".$this->orderid."'");
			$orderids = explode(",",$pre_order["orderids"]);
			foreach($orderids as $orderid){
				if(!$orderid){
					continue;
				}
				$data = $this->pay_orders($orderid);
			}
			$this->db->Set("user_pre_order",array("status"=>2),"where pre_sn='".$this->orderid."'");
		}else{
			$data = $this->pay_orders($this->orderid, $method);
		}
		return $data;
	}
	
	public function get_pay_info(){
		if(strpos($this->orderid,"PRE")>-1){
			$pre_order = $this->db->GetRs("user_pre_order","*","where pre_sn='".$this->orderid."'");
			if ($pre_order) {
			$pre_order["total"] = $pre_order["Order_Yebc"] != '0.00' || $pre_order["Order_Yebc"] != 0 ? $pre_order["Order_Fyepay"] : $pre_order["total"];
			$data = array(
				"out_trade_no"=> $this->orderid,
				"subject"=>"微商城在线付款，订单编号:".$pre_order["orderids"],
				"total_fee"=>$pre_order["total"]
			);
			}
		}else{
			$orderinfo = $this->get_order($this->orderid);
			if ($orderinfo) {
			$orderinfo["Order_TotalPrice"] = $orderinfo["Order_Yebc"] != '0.00' || $orderinfo["Order_Yebc"] != 0 ? $orderinfo["Order_Fyepay"] : $orderinfo["Order_TotalPrice"];
			$rsConfig = $this->get_shopconfig($orderinfo["Users_ID"]);
			if(!strpos($orderinfo["Order_Type"],'zhongchou')){
				if($orderinfo["Order_Type"]=='weicbd'){
					$pay_subject = $rsConfig['ShopName']."微商圈在线付款，订单编号:".$this->orderid;
				}elseif($orderinfo["Order_Type"]=='kanjia'){
					$pay_subject = $rsConfig['ShopName']."微砍价在线付款，订单编号:".$this->orderid;
				}elseif($orderinfo["Order_Type"]=='pifa'){
				     $pay_subject = $rsConfig['ShopName']."批发商城在线付款，订单编号:".$this->orderid;
				}elseif($orderinfo["Order_Type"]=='pintuan' || $orderinfo["Order_Type"]=='dangou'){
				     $pay_subject = $rsConfig['ShopName']."微拼团支付付款，订单编号:".$this->orderid;
				}else{
					$pay_subject = $rsConfig['ShopName']."在线付款，订单编号:".$this->orderid;
				}
			}else{
				$pay_subject = $rsConfig['ShopName']."微众筹在线付款，订单编号:".$this->orderid;
			}
			$data = array(
				"out_trade_no"=>$orderinfo["Order_CreateTime"].$this->orderid,
				"subject"=>$pay_subject,
				"total_fee"=>$orderinfo["Order_TotalPrice"]
			);
		}
	}
		return $data;
	}

	public function deal_sha_order($method=0,$payid=''){
		$Method = array('余额支付','微支付','支付宝');
		
		$rsOrder = $this->db->GetRs('sha_order','*','where Order_ID='.$this->orderid);
		if(!$rsOrder){
			return array("status"=>0,"msg"=>"订单不存在");
		}
		
		if($rsOrder['Order_Status']<>1){
			return array("status"=>0,"msg"=>"该订单不是待付款状态");
		}

		$rsUser = $this->get_user($rsOrder['User_ID']);	
		
		$Flag = true;
		
		if($method==0){//余额支付
			//增加资金流水
			$Data = array(
				'Users_ID' => $rsOrder['Users_ID'],
				'User_ID' => $rsOrder['User_ID'],
				'Type' => 0,
				'Amount' => $rsOrder['Order_TotalPrice'],
				'Total' => $rsUser ['User_Money'] - $rsOrder['Order_TotalPrice'],
				'Note' => "购买成为股东支出 -".$rsOrder['Order_TotalPrice'],
				'CreateTime' => time () 		
			);
			$Add = $this->db->Add('user_money_record', $Data);
			$Flag = $Flag && $Add;
			$user_data['User_Money'] = $rsUser['User_Money'] - $rsOrder['Order_TotalPrice'];
		}

		//更新用户信息
		$user_data['Is_Distribute'] = 1;
		$Set = $this->db->Set('user',$user_data,"where User_ID=".$rsOrder['User_ID']);
		$Flag = $Flag && $Set;
		
		//更改订单信息
		$order_data = array(
			'Order_Status'=>2,
			'Order_PayTime'=>time(),
			'Order_PaymentMethod'=>$Method[$method],
			'Order_PayID'=>$payid
		);
		$Set= $this->db->Set('sha_order',$order_data,'where Order_ID='.$this->orderid);
		$Flag = $Flag && $Set;

		if ($Flag) 
		{
			return array('status'=>1);
		} else {
			return array('status'=>0,'msg'=>'订单支付成功');
		}
	}

	public function deal_proxy_order($method=0,$payid=''){
		$Method = array('余额支付','微支付','支付宝');
		$Area = array('省级代理', '市级代理', '县级代理');
		
		$rsOrder = $this->db->GetRs('agent_order','*','where Order_ID='.$this->orderid);
		if(!$rsOrder){
			return array("status"=>0,"msg"=>"订单不存在");
		}
		
		if($rsOrder['Order_Status']<>1){
			return array("status"=>0,"msg"=>"该订单不是待付款状态");
		}

		$rsUser = $this->get_user($rsOrder['User_ID']);	
		
		$Flag = true;
		
		if($method==0){//余额支付
			//增加资金流水
			$Data = array(
				'Users_ID' => $rsOrder['Users_ID'],
				'User_ID' => $rsOrder['User_ID'],
				'Type' => 0,
				'Amount' => $rsOrder['Order_TotalPrice'],
				'Total' => $rsUser ['User_Money'] - $rsOrder['Order_TotalPrice'],
				'Note' => "购买".$Area[$rsOrder['Area']-1].'支出 -'.$rsOrder['Order_TotalPrice'],
				'CreateTime' => time () 		
			);
			$Add = $this->db->Add('user_money_record', $Data);
			$Flag = $Flag && $Add;
			$user_data['User_Money'] = $rsUser['User_Money'] - $rsOrder['Order_TotalPrice'];
		}

		//更新用户信息
		$user_data['Is_Distribute'] = 1;
		$Set = $this->db->Set('user',$user_data,"where User_ID=".$rsOrder['User_ID']);
		$Flag = $Flag && $Set;
		
		//更改订单信息
		$order_data = array(
			'Order_Status'=>2,
			'Order_PayTime'=>time(),
			'Order_PaymentMethod'=>$Method[$method],
			'Order_PayID'=>$payid
		);
		$Set= $this->db->Set('agent_order',$order_data,'where Order_ID='.$this->orderid);
		$Flag = $Flag && $Set;

		if ($Flag) 
		{
			$AreaPinyin = array('ProvinceId', 'CityId', 'AreaId');
			//申请成功在代理信息表里增加代理信息
			$AreaIds = $AreaPinyin[$rsOrder['Area']-1];
			$areaInfo = $this->db->GetRs('area', 'area_name', ' WHERE `area_id`='.$rsOrder[$AreaIds]);
			$rsAccount = $this->db->GetRs("distribute_account","Account_ID","where Users_ID='".$rsOrder["Users_ID"]."' and User_ID=".$rsOrder["User_ID"]);

			$AreaData = array(
				'type' => $rsOrder['Area'],
				'Users_ID' => $rsOrder['Users_ID'],
				'Account_ID' => $rsAccount['Account_ID'],
				'area_id' => $rsOrder[$AreaIds],
				'area_name' => $areaInfo['area_name'],
				'create_at' => time(),
				'status' => 1
			);
			$this->db->Add('distribute_agent_areas', $AreaData);
			return array('status'=>1);
		} else {
			return array('status'=>0,'msg'=>'订单支付成功');
		}
	}
	
	public function deal_distribute_order($method=0,$payid=''){
		$Method = array('余额支付','微支付','支付宝');
		$Active = array('购买','升级');	
		
		$rsOrder = $this->db->GetRs('distribute_order','*','where Order_ID='.$this->orderid);
		if(!$rsOrder){
			return array("status"=>0,"msg"=>"订单不存在");
		}
		
		if($rsOrder['Order_Status']<>1){
			return array("status"=>0,"msg"=>"该订单不是待付款状态");
		}
		
		$level_data = get_dis_level($this->db,$rsOrder['Users_ID']);
		if(empty($level_data[$rsOrder['Level_ID']])){
			return array("status"=>0,"msg"=>"该分销级别不存在");
		}
		
		$rsUser = $this->get_user($rsOrder['User_ID']);	
		
		$Flag = true;
		
		if($method==0){//余额支付
			//增加资金流水
			$user_data_money = 0;
			$UserName = $rsUser['User_NickName']?$rsUser['User_NickName']:($rsUser['User_Name']?$rsUser['User_Name']:'');
			$Data = array(
				'Users_ID' => $rsOrder['Users_ID'],
				'User_ID' => $rsOrder['User_ID'],
				'Type' => 0,
				'Amount' => $rsOrder['Order_TotalPrice'],
				'Total' => $rsUser ['User_Money'] - $rsOrder['Order_TotalPrice'],
				'Note' => $UserName."  ".$Method[$method].$Active[$rsOrder['Order_Type']]."成为".$rsOrder['Level_Name']."支出 -" . $rsOrder['Order_TotalPrice'],
				'CreateTime' => time () 		
			);
			$user_data_money = $rsUser['User_Money'] - $rsOrder['Order_TotalPrice'];
			$Add = $this->db->Add('user_money_record', $Data);
			//写返还
            $rsLevel = $this->db->GetRs('distribute_level','*','where Users_ID="'.$rsOrder['Users_ID'].'" and Level_ID='.$rsOrder['Level_ID']);
            if($rsLevel['Present_type'] == '2') {
				$orderpresent = $rsOrder['Order_Present'] ? $rsOrder['Order_Present'] : "" ;
                $Data1 = array(
                    'Users_ID' => $rsOrder['Users_ID'],
                    'User_ID' => $rsOrder['User_ID'],
                    'Type' => 1,
                    'Amount' => $rsOrder['Order_TotalPrice'] + ($orderpresent ? $orderpresent : 0 ),
                    'Total' => $user_data_money + $rsOrder['Order_TotalPrice'] + ($orderpresent ? $orderpresent : 0 ),
                    'Note' => $Active[$rsOrder['Order_Type']] . "成为" . $rsOrder['Level_Name'] . "返还 +" . $rsOrder['Order_TotalPrice'].($orderpresent ? "，赠送+".$orderpresent : "" ),
                    'CreateTime' => time()
                );
                $Add1 = $this->db->Add('user_money_record', $Data1);
				$user_data_money += $rsOrder['Order_TotalPrice'] + ($orderpresent ? $orderpresent : 0);
            }
			$Flag = $Flag && $Add;
			$user_data['User_Money'] = $user_data_money;
		}
		
		//更新用户信息
		$user_data['Is_Distribute'] = 1;
		$Set = $this->db->Set('user',$user_data,"where User_ID=".$rsOrder['User_ID']);
		$Flag = $Flag && $Set;
		
		//更改订单信息
		$order_data = array(
			'Order_Status'=>4,
			'Order_PayTime'=>time(),
			'Order_PaymentMethod'=>$Method[$method],
			'Order_PayID'=>$payid
		);
		$Set= $this->db->Set('distribute_order',$order_data,'where Order_ID='.$this->orderid);
		$Flag = $Flag && $Set;
		
		//分销商处理
		$rsConfig = shop_config($rsOrder['Users_ID']);
		//分销相关设置
		$dis_config = dis_config($rsOrder['Users_ID']);
		//合并参数
		$rsConfig = array_merge($rsConfig,$dis_config);
		
		if($rsOrder['Order_Type']==0){//购买成为分销商
			$flag = create_distribute_acccount($rsConfig, $rsUser, $rsOrder['Level_ID'], '', 1);
		}else{//分销商升级
			$flag = $this->db->Set('distribute_account','Level_ID='.$rsOrder['Level_ID'],'where User_ID='.$rsOrder['User_ID']);
		}
		if($Flag){
			if($rsUser['Owner_Id']>0){//佣金处理
				//佣金设置
				$bonus_list = array();
				
				$level_data[$rsOrder['Level_ID']]['Level_Distributes'] = $rsOrder['Order_Type']==0 ? json_decode($level_data[$rsOrder['Level_ID']]['Level_Distributes'], true) : json_decode($rsOrder['UpgradeDistributes'], true);
				
				foreach($level_data as $key=>$levelinfo){
					$bonus_list[$key] = array_values($level_data[$rsOrder['Level_ID']]['Level_Distributes']);
				}
				$DisAccount =  Dis_Account::Multiwhere(array('Users_ID'=>$rsOrder['Users_ID'],'User_ID'=>$rsUser['Owner_Id']))
						 ->first();
				$ancestors = $DisAccount->getAncestorIds($rsConfig['Dis_Level'],0);
				array_push($ancestors,$rsUser['Owner_Id']);
				$ancestors = array_reverse($ancestors);
				$ancestors = count($ancestors)>3 ? array_slice($ancestors,0,3) : $ancestors;
				$ancestors_meet = get_distribute_balance_userids($rsOrder['Users_ID'],$rsOrder['User_ID'],$ancestors,$bonus_list,1);
				$sql_insert = '';
				$sql_update = '';
				$sql_update_income = '';
				$where_update = array();
				$account_list = array();
				$this->db->Get('distribute_account','Account_ID,balance,User_ID,Total_Income','where User_ID in('.implode(',',$ancestors).')');
				while($r = $this->db->fetch_assoc()){
					$account_list[$r['User_ID']] = array(
					    'Account_ID'=>$r['Account_ID'],
                        'balance'=>$r['balance'],
                        'Total_Income'=>$r['Total_Income']
                    );
				}
				foreach($ancestors as $k=>$val){
					if(!empty($ancestors_meet[$val]) && !empty($account_list[$val])){
						if($ancestors_meet[$val]['status']==1){//正常
							$money = $ancestors_meet[$val]['bonus'];
							$description = ($k+1).'级下属'.$Active[$rsOrder['Order_Type']].''.$rsOrder['Level_Name'].'获得佣金';
						}else{
							$money = 0;
							$description = $ancestors_meet[$val]['msg'];
						}
						$sql_insert .= ',("'.$rsOrder['Users_ID'].'",'.$this->orderid.','.$val.','.($k + 1).','.$money.',"'.$description.'",1,'.$rsUser['Owner_Id'].','.time().','.$rsOrder['User_ID'].','.$rsOrder['Order_TotalPrice'].')';
						$sql_update .= '
						WHEN '.$account_list[$val]['Account_ID'].' THEN '.($account_list[$val]['balance']+$money);
						$sql_update_income .= '
						WHEN '.$account_list[$val]['Account_ID'].' THEN '.($account_list[$val]['Total_Income']+$money);
						$where_update[] = $account_list[$val]['Account_ID'];
					}else{
						continue;
					}
				}
				
				if($sql_insert){
					$sql_insert = 'insert into distribute_order_record(Users_ID,Order_ID,User_ID,level,Record_Money,Record_Description,Record_Status,Owner_ID,Record_CreateTime,Buyer_ID,Price) values'.substr($sql_insert,1);
					$this->db->query($sql_insert);
					//jiechuli
					$sql_update = '
						UPDATE distribute_account
							SET balance = CASE Account_ID'.$sql_update.'        
							END,
							Total_Income = CASE Account_ID'.$sql_update_income.'        
							END
						WHERE Account_ID IN ('.implode(',',$where_update).')';
					$this->db->query($sql_update);
				}
			}
			require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_message.class.php');
			$weixin_message = new weixin_message($this->db,$rsOrder["Users_ID"],$rsOrder["User_ID"]);
			$weixin_message->sendorder_distribute($rsOrder["Order_TotalPrice"],$this->orderid);
			return array('status'=>1);
		}else{
			return array('status'=>0,'msg'=>'订单支付成功');
		}
	}
	public function withdraw($UsersID,$UserID,$RecordID,$type='wx_hongbao'){//佣金提现(微信红包、微信转账
	
		$rsUser = $this->db->GetRs("user","User_OpenID","where User_ID=".$UserID);
		if(empty($rsUser["User_OpenID"])){
			return array('status'=>0,'msg'=>'用户未授权');
		}
		
		$rsUsers = $this->db->GetRs("users","Users_ID,Users_WechatAppId,Users_WechatAppSecret","where Users_ID='".$UsersID."'");
		if(empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
			return array('status'=>0,'msg'=>'系统还未设置AppId和AppSecret');
		}
		
		$rsPay = $this->db->GetRs("users_payconfig","PaymentWxpayPartnerId,PaymentWxpayPartnerKey,PaymentWxpayCert,PaymentWxpayKey","where Users_ID='".$UsersID."'");
		if(empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsPay["PaymentWxpayCert"]) || empty($rsPay["PaymentWxpayKey"])){
			return array('status'=>0,'msg'=>'系统支付信息配置不全');
		}
		
		$shop_sonfig = $this->db->GetRs('shop_config','ShopName','where Users_ID="'.$UsersID.'"');
		//必需OrderID无关参数		
		$OrderID=0;
		
		include_once($_SERVER["DOCUMENT_ROOT"].'/pay/wxpay2/WxPay.pub.config.php');
		include_once($_SERVER["DOCUMENT_ROOT"].'/pay/wxpay2/WxPayPubHelper.php');
		$mch_billno = MCHID.date('YmdHis').rand(1000, 9999);
		
		$rsRecord = $this->db->GetRs("distribute_withdraw_record","Record_Money","where Record_ID=".$RecordID);
		
		$money = strval($rsRecord["Record_Money"]*100);
		$ip = $this->getIp();
		$ip = $ip ? $ip : $_SERVER['SERVER_ADDR'];
		if($type=='wx_hongbao'){
			$weixinhongbao = new Wxpay_client_pub();
			//参数设定			
			$weixinhongbao->setParameter("mch_billno",$mch_billno);
			$weixinhongbao->setParameter("send_name","佣金发放");//发送者名称
			$weixinhongbao->setParameter("re_openid",$rsUser["User_OpenID"]);
			$weixinhongbao->setParameter("total_amount",$money);
			$weixinhongbao->setParameter("total_num","1");
			$weixinhongbao->setParameter("wishing",$shop_sonfig["ShopName"]."提现");//红包祝福语
			$weixinhongbao->setParameter("client_ip",$ip);
			$weixinhongbao->setParameter("act_name","佣金发放");//活动名称
			$weixinhongbao->setParameter("remark",$shop_sonfig["ShopName"]."提现");//描述
			$weixinhongbao->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
			$weixinhongbao->curl_timeout = 30;
			$return_xml = $weixinhongbao->postXmlSSL_hongbao();
		}elseif($type=='wx_zhuanzhang'){
			$weixinzhuanzhang = new Wxpay_client_pub();
			//参数设定
			$weixinzhuanzhang->setParameter("partner_trade_no",$mch_billno);
			//$weixinzhuanzhang->setParameter("device_info",'');
			$weixinzhuanzhang->setParameter("openid",$rsUser["User_OpenID"]);
			$weixinzhuanzhang->setParameter("check_name",'NO_CHECK');
			//$weixinzhuanzhang->setParameter("re_user_name",'');
			$weixinzhuanzhang->setParameter("amount",$money);
			$weixinzhuanzhang->setParameter("spbill_create_ip",$ip);
			$weixinzhuanzhang->setParameter("desc",$shop_sonfig["ShopName"]."提现");//描述
			
			$weixinzhuanzhang->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
			$weixinzhuanzhang->curl_timeout = 30;
			$return_xml = $weixinzhuanzhang->postXmlSSL_zhuanzhang();
		}
		
		$responseObj = simplexml_load_string($return_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$return_code = trim($responseObj->return_code);
		$return_msg = trim($responseObj->return_msg);
		if($return_code=='SUCCESS'){
			$result_code = trim($responseObj->result_code);
			if($result_code=='SUCCESS'){
				//处理提现记录
				if($type=='wx_hongbao'){
					$record_data = array(
						"Record_Status"=>1,
						"Record_SendID"=>$mch_billno,
						"Record_SendTime"=>strtotime(trim($responseObj->send_time)) ? strtotime(trim($responseObj->send_time)) : 0,
						"Record_WxID"=>trim($responseObj->send_listid),
						"Record_SendType"=>$type
					);
				}else{
					$record_data = array(
						"Record_Status"=>1,
						"Record_SendID"=>$mch_billno,
						"Record_SendTime"=>strtotime(trim($responseObj->payment_time)),
						"Record_WxID"=>trim($responseObj->payment_no),
						"Record_SendType"=>$type
					);
				}
				$condition = "where Record_ID=".$RecordID;
				$this->db->Set("distribute_withdraw_record",$record_data,$condition);
				return array("status"=>1);
			}else{
				return array("status"=>0,"msg"=>trim($responseObj->err_code_des));
			}
		}else{
			return array("status"=>0,"msg"=>$return_msg);
		}
	}
	public function withdraws($UsersID, $OpenID, $money)
    { // 佣金提现(微信红包、微信转账
        $rsUser = $this->db->GetRs("user", "*", "WHERE Users_ID='{$UsersID}' and User_OpenID='{$OpenID}'");
        if (empty($rsUser)) {
            return array(
                'status' => 0,
                'msg' => '用户未授权'
            );
        }
        
        $rsUsers = $this->db->GetRs("users", "Users_ID,Users_WechatAppId,Users_WechatAppSecret", "WHERE Users_ID='" . $UsersID . "'");
        if (empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])) {
            return array(
                'status' => 0,
                'msg' => '系统还未设置AppId和AppSecret'
            );
        }
        
        $rsPay = $this->db->GetRs("users_payconfig", "PaymentWxpayPartnerId,PaymentWxpayPartnerKey,PaymentWxpayCert,PaymentWxpayKey", "WHERE Users_ID='" . $UsersID . "'");
        if (empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsPay["PaymentWxpayCert"]) || empty($rsPay["PaymentWxpayKey"])) {
            return array(
                'status' => 0,
                'msg' => '系统支付信息配置不全'
            );
        }
        
        $shop_sonfig = $this->db->GetRs('shop_config', 'ShopName', 'WHERE Users_ID="' . $UsersID . '"');
        // 必需OrderID无关参数
        $OrderID = 0;
        
        include_once ($_SERVER["DOCUMENT_ROOT"] . '/pay/wxpay2/WxPay.pub.config.php');
        include_once ($_SERVER["DOCUMENT_ROOT"] . '/pay/wxpay2/WxPayPubHelper.php');
        $mch_billno = MCHID . date('YmdHis') . rand(1000, 9999);
        
        // $rsRecord = $this->db->GetRs("distribute_withdraw_record","Record_Money","WHERE Record_ID=".$RecordID);
        // $money = strval($rsRecord["Record_Money"]*100);
        $money = strval($money * 100);
        $ip = $this->getIp();
		$ip = $ip ? $ip : $_SERVER['SERVER_ADDR'];
        $weixinzhuanzhang = new Wxpay_client_pub();
        // 参数设定
        $weixinzhuanzhang->setParameter("partner_trade_no", $mch_billno);
        // $weixinzhuanzhang->setParameter("device_info",'');
        $weixinzhuanzhang->setParameter("openid", $rsUser["User_OpenID"]);
        $weixinzhuanzhang->setParameter("check_name", 'NO_CHECK');
        // $weixinzhuanzhang->setParameter("re_user_name",'');
        $weixinzhuanzhang->setParameter("amount", $money);
        $weixinzhuanzhang->setParameter("spbill_create_ip", $ip);
        $weixinzhuanzhang->setParameter("desc", $shop_sonfig["ShopName"] . "财务结算"); // 描述
        
        $weixinzhuanzhang->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        $weixinzhuanzhang->curl_timeout = 30;
        $return_xml = $weixinzhuanzhang->postXmlSSL_zhuanzhang();
        
        $responseObj = simplexml_load_string($return_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $return_code = trim($responseObj->return_code);
        
        $return_msg = trim($responseObj->return_msg);
        if ($return_code == 'SUCCESS') {
            $result_code = trim($responseObj->result_code);
            
            if ($result_code == 'SUCCESS') {
                // 处理提现记录
                $record_data = array(
                    "Users_ID"=>$UsersID,
                    "User_ID"=>$rsUser["User_ID"],
                    "Method_Name"=>"微信转账",
                    "Method_Account"=>$rsUser["User_OpenID"],
                    "Record_Total"=>$money/100,
                    "Record_Status" => 1,
                    "Record_CreateTime" => time(),
                    "No_Record_Desc" => "微信财务结算转账",
                    "Record_SendID" => $mch_billno,
                    "Record_SendTime" => time(),
                    "Record_WxID" => trim($responseObj->payment_no),
                    "Record_SendType" => "wx_zhuanzhang"
                );
                $this->db->Add("distribute_withdraw_record",$record_data);
                return array(
                    "status" => 1
                );
            } else {
                return array(
                    "status" => 0,
                    "msg" => trim($responseObj->err_code_des)
                );
            }
        } else {
            return array(
                "status" => 0,
                "msg" => $return_msg
            );
        }
    }
	
	public function checkhongbao($UsersID,$mch_billno){//佣金提现(微信红包、微信转账
		$rsUsers = $this->db->GetRs("users","Users_ID,Users_WechatAppId,Users_WechatAppSecret","where Users_ID='".$UsersID."'");
		if(empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
			return 2;
		}
		
		$rsPay = $this->db->GetRs("users_payconfig","PaymentWxpayPartnerId,PaymentWxpayPartnerKey,PaymentWxpayCert,PaymentWxpayKey","where Users_ID='".$UsersID."'");
		if(empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsPay["PaymentWxpayCert"]) || empty($rsPay["PaymentWxpayKey"])){
			return 2;
		}
		
		$shop_sonfig = $this->db->GetRs('shop_config','ShopName','where Users_ID="'.$UsersID.'"');
		//必需OrderID无关参数		
		$OrderID=0;
		
		include_once($_SERVER["DOCUMENT_ROOT"].'/pay/wxpay2/WxPay.pub.config.php');
		include_once($_SERVER["DOCUMENT_ROOT"].'/pay/wxpay2/WxPayPubHelper.php');
		
		
		$weixinhongbao = new Wxpay_client_pub();
		//参数设定			
		$weixinhongbao->setParameter("mch_billno",$mch_billno);
		$weixinhongbao->setParameter("bill_type",'MCHT');		
		$weixinhongbao->url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/gethbinfo";
		$weixinhongbao->curl_timeout = 30;
		$return_xml = $weixinhongbao->postXmlSSL_hongbao();		
		$responseObj = simplexml_load_string($return_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$return_code = trim($responseObj->return_code);
		$return_msg = trim($responseObj->return_msg);
		if($return_code=='SUCCESS'){
			$result_code = trim($responseObj->result_code);
			if($result_code=='SUCCESS'){
				//处理查询结果
				$result_status = trim($responseObj->status);
				if($result_status == 'FAILED' || $result_status == 'REFUND'){//红包发放失败或退款
					return 1;
				}else{
					return 2;
				}				
			}else{
				return 2;
			}
		}else{
			return 2;
		}
	}
	
	private function getIp(){
		if (!empty($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']!='unknown') {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']!='unknown') {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return preg_match('/^\d[\d.]+\d$/', $ip) ? $ip : '';
	}
	
	public function deal_chongzhi_order($method=0,$payid=''){
		$Method = array('余额支付','微支付','支付宝');
		
		$rsOrder = $this->db->GetRs('user_order','*','where Order_ID='.$this->orderid);
		if(!$rsOrder){
			return array("status"=>0,"msg"=>"订单不存在");
		}
		
		if($rsOrder['Order_Status']<>1){
			return array("status"=>0,"msg"=>"该订单不是待付款状态");
		}

		$rsUser = $this->get_user($rsOrder['User_ID']);
		
		$Flag = true;
		
		if($method==0){//余额支付
			//增加资金流水
			$Data = array(
				'Users_ID' => $rsOrder['Users_ID'],
				'User_ID' => $rsOrder['User_ID'],
				'Type' => 0,
				'Amount' => $rsOrder['Order_TotalPrice'],
				'Total' => $rsUser ['User_Money'] - $rsOrder['Order_TotalPrice'],
				'Note' => "充值话费支出 -".$rsOrder['Order_TotalPrice'],
				'CreateTime' => time () 		
			);
			$Add = $this->db->Add('user_money_record', $Data);
			$Flag = $Flag && $Add;
			$user_data['User_Money'] = $rsUser['User_Money'] - $rsOrder['Order_TotalPrice'];
		}

		// 更新用户信息        
        $Set = $this->db->Set('user', $user_data, "where User_ID=" . $rsOrder['User_ID']);
        $Flag = $Flag && $Set;
		
		//更改订单信息
		$order_data = array(
			'Order_Status'=>2,
			'Order_PaymentMethod'=>$Method[$method],
		);
		$Set= $this->db->Set('user_order',$order_data,'where Order_ID='.$this->orderid);
		$Flag = $Flag && $Set;
		if($Flag){
			//充值接口
			//充值接口
			require($_SERVER["DOCUMENT_ROOT"]."/pay/qianmi/OpenSdk.php");
			$re = $this->db->GetRs("mycount","*","WHERE usersid='".$rsOrder['Users_ID']."'");
				
			$appKey=$re['appkey'];
			$appSecret=$re['appsecret'];
			$accessToken = $re['token'];
				
			$loader  = new QmLoader;
			$loader  -> autoload_path  = array(CURRENT_FILE_DIR.DS."client");
			$loader  -> init();
			$loader  -> autoload();
				
			$client  = new OpenClient;
			$client  -> appKey =  $appKey;
			$client  -> appSecret =  $appSecret;				
			$req = new RechargeBasePayBillRequest;
			$req->setBillId($rsOrder['Service_billId']);
			$res = $client->execute($req, $accessToken);
			
			//分销记录状态更新
			$Flag_c = Dis_Account_Record::changeStatusByOrderID($this->orderid,1);
			Order::observe(new OrderObserver());
			$order = Order::find($this->orderid);
			$Flag_d = $order->confirmReceive();
			
			return array('status'=>1);
		} else {
			return array('status'=>0,'msg'=>'订单支付成功');
		}
	}
}
?>