<?php
class user_order{
	private $orderid;
	private $db;

	function __construct($db, $orderid){
		$this->orderid = $orderid;
		$this->db = $db;
	}
	
	private function get_order($orderid, array $field = []){
	    if(!empty($field)){
            $rsOrder = Order::where("Order_ID", $orderid)->first($field)->toArray();
        }else{
            $rsOrder = Order::where("Order_ID", $orderid)->first()->toArray();
        }

		return $rsOrder;
	}
	
	private function get_user($userid, array $field = []){
        if(!empty($field)){
            $rsUser = User::where("User_ID", $userid)->first($field)->toArray();
        }else{
            $rsUser = User::where("User_ID", $userid)->first()->toArray();
        }
		return $rsUser;
	}
	
	private function get_products($pid, array $field = []){
        if(!empty($field)) {
            $rsProduct = Product::where("Products_ID", $pid)->field($field)->toArray();
        }else{
            $rsProduct = Product::where("Products_ID", $pid)->field()->toArray();
        }
		return $rsProduct;
	}
	
	private function get_shopconfig($usersid){
        $shop_config = shop_config($usersid);
        $dis_config = dis_config($usersid);
        $shop_config = array_merge($shop_config,$dis_config);

		return $shop_config;
	}
	
	private function update_order($orderid,array $data){
	    $flag = Order::where("Order_ID", $orderid)->update($data);
	    return $flag;
	}

	public function __set($key, $val)
    {
        $this->$key = $val;
    }

	private function pay_orders($orderid){
		
		$rsOrder = $this->get_order($orderid);
		$rsUser = $this->get_user($rsOrder["User_ID"]);

        if(!$rsOrder){
			return ["status"=>0,"msg"=>"订单不存在"];
		}

		//更新订单状态
		$Data = [
			"Order_Status" => 2
		];
        if($this->transaction_id){
            $Data['transaction_id'] = $this->transaction_id;
        }
		$flag = $this->update_order($orderid, $Data);
		if(! $flag) {
		    back_trans();
            return ["status"=>0,"msg"=>"修改订单状态失败"];
        }
        $flag_b = true;
		//更改业务提成状态为已付款
        require_once(CMS_ROOT . '/include/compser_library/Salesman_Commission.php');
        $sales_man = new Salesman_commission();
        $sales_man->up_sales_status($orderid,1);
        if($rsOrder["Integral_Consumption"] > 0 ){
            $flag_b = change_user_integral($rsOrder["Users_ID"],$rsOrder["User_ID"],$rsOrder["Integral_Consumption"],'reduce','积分抵用消耗积分');
        }

		//更改分销账号记录状态,置为已付款
        $flag_c = true;
        $order = new Order();
        $disAccountRecord = $order->disAccountRecord();

        if($disAccountRecord->count() >0){
            $flag_c = $disAccountRecord->rawUpdate(array('Record_Status'=>1));
        }

        if(! $flag_c) {
            back_trans();
        }

		if($flag_b && $flag_c){
			$rsConfig=$this->get_shopconfig($rsOrder["Users_ID"]);
			$CartList = json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]), true);
			
			//获取订单中的产品id集
			$productids = array_keys($CartList);
			$is_distribute = $rsUser["Is_Distribute"];//是否分销商标识
			
			//分销商级别数组
            $rsDisLevel = Dis_Level::where("Users_ID", $rsOrder["Users_ID"])->orderBy("Level_ID","asc")->get()
                ->toArray();
            $level_data = [];
            foreach($rsDisLevel as $k => $v){
                $level_data[$v['Level_ID']] = $v;
            }

            if ($rsUser["Is_Distribute"] == 0) {//不是分销商
                $LevelID = 0;//分销商级别
                if($rsConfig["Distribute_Type"]==1){//消费金额
					$LevelID = get_user_distribute_level_cost($level_data,$rsConfig,$rsOrder['Order_TotalPrice'],$rsOrder['User_ID']);
					if (empty($LevelID)) {
					$LevelID = get_user_distribute_level($rsConfig,$level_data,$rsOrder['User_ID'],$rsOrder['Users_ID'],$orderid);
					}
				}

                if ($LevelID >= 1) {
                    $is_distribute = 1;
                    $f = create_distribute_acccount($rsConfig, $rsUser, $LevelID, '', 1);
                    if(! $f){
                        back_trans();
                    }
                }

                //爵位晋级
                if ($rsConfig["Pro_Title_Status"] == 2) {
                    $pro = new ProTitle($rsOrder["Users_ID"], $rsUser["Owner_Id"]);
                    $flag_x = $pro->up_nobility_level();
                }
            } else {//是分销商判定可提现条件
                $tixian = 0;
                $rsAccount = Dis_Account::Multiwhere(["Users_ID" => $rsOrder["Users_ID"], "User_ID" => $rsOrder["User_ID"]])
                    ->first(["User_ID","Enable_Tixian","Account_ID","Is_Dongjie","Is_Delete","Users_ID","Level_ID"])->toArray();

                if ($rsAccount) {
                    //爵位晋级
                    if ($rsConfig["Pro_Title_Status"] == 2) {
                        $pro = new ProTitle($rsOrder["Users_ID"], $rsOrder["User_ID"]);
                        $flag_x = $pro->up_nobility_level();
                    }

                    $is_distribute = 1;
                    $account_data = array();
                    if ($rsAccount["Enable_Tixian"] == 0) {
                        if ($rsConfig["Withdraw_Type"] == 0) {
                            $tixian = 1;
                        } elseif ($rsConfig["Withdraw_Type"] == 2) {
                            $arr_temp = explode("|", $rsConfig["Withdraw_Limit"]);
                            if ($arr_temp[0] == 0) {
                                $tixian = 1;
                            } else {
                                if (!empty($arr_temp[1])) {
                                    $productsid = explode(",", $arr_temp[1]);
                                    foreach ($productsid as $id) {
                                        if (!empty($CartList[$id])) {
                                            $tixian = 1;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        if ($tixian == 1) {
                            $account_data['Enable_Tixian'] = 1;
                        }
                    }

                    //判定升级级别
                    $LevelID = get_user_distribute_level_upgrade($level_data, $rsConfig, $productids);
                    if ($LevelID > $rsAccount['Level_ID']) {
                        $account_data['Level_ID'] = $LevelID;
                    }

                    if (!empty($account_data)) {
                        Dis_Account::Multiwhere(["Users_ID" => $rsOrder["Users_ID"], "Account_ID" => $rsAccount["Account_ID"]])->update($account_data);
                    }

                    if ($rsConfig["Fuxiao_Open"] == 1 && $rsAccount["Is_Delete"] == 0) {//开启复销功能
                        distribute_fuxiao_return_action($this->db, $rsConfig["Fuxiao_Rules"], $rsAccount, $rsUser["User_OpenID"]);//是否达到复销要求并处理
                    }
                }
            }
			return ["status"=>1,"msg" => "ok"];
		}else{
			return ["status"=>0,"msg"=>"订单支付失败"];
		}
	}
	
	public function make_pay(){
        $data = $this->pay_orders($this->orderid);
		return $data;
	}
	
	public function get_pay_info(){

        $orderinfo = $this->get_order($this->orderid);
        if(!strpos($orderinfo["Order_Type"],'zhongchou')){
            if($orderinfo["Order_Type"]=='weicbd'){
                $pay_subject = "微商圈在线付款，订单编号:".$this->orderid;
            }elseif($orderinfo["Order_Type"]=='kanjia'){
                $pay_subject = "微砍价在线付款，订单编号:".$this->orderid;
            }elseif($orderinfo["Order_Type"]=='pifa'){
                 $pay_subject = "批发商城在线付款，订单编号:".$this->orderid;
            }else{
                $pay_subject = "微商城在线付款，订单编号:".$this->orderid;
            }
        }else{
            $pay_subject = "微众筹在线付款，订单编号:".$this->orderid;
        }
        $data = array(
            "out_trade_no"=>$orderinfo["Order_CreateTime"].$this->orderid,
            "subject"=>$pay_subject,
            "total_fee"=>$orderinfo["Order_TotalPrice"]
        );
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

}
?>