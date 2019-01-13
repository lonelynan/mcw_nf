<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once(CMS_ROOT.'/include/support/tool_helpers.php');
require_once(CMS_ROOT.'/include/helper/url.php');
require_once(CMS_ROOT.'/include/helper/distribute.php');
require_once(CMS_ROOT.'/include/helper/tools.php');

$base_url = base_url();

if(isset($_REQUEST["UsersID"])){
	$UsersID=$_REQUEST["UsersID"];
}else{
	echo 'error';
	exit;
}

if (isset($_POST['User_ID'])) {
    $UserID = (int)$_POST['User_ID'];

    $res = little_program_key_check($_POST);
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

$action=empty($_REQUEST["action"])?"":$_REQUEST["action"];
//开始事务定义
$Flag=true;
if($action == 'withdraw_appy'){
	//商城配置信息
	$rsConfig = shop_config($UsersID);
	//分销相关设置
	$dis_config = dis_config($UsersID);
	//合并参数
	$rsConfig = array_merge($rsConfig,$dis_config);
	
	$condition = "where User_ID=".$UserID." and Users_ID='".$UsersID."'";
	$dsAccount = $DB->getRs('distribute_account',"balance",$condition);
	$moneys = $_POST['money'];
	$feifas = is_shuz($moneys);	
	if($feifas != 1){
	if($moneys >$dsAccount['balance']){
		$response = array('status'=>0,'msg'=>'余额不足');
	}else{
		if ($rsConfig["Withdraw_Type"]==2) {
			$Data['Enable_Tixian'] = 0;
			$SetEnable_Tixian = $DB->Set('distribute_account',$Data,$condition);
			}
		$condition = "where User_Method_ID=".$_POST['User_Method_ID']." and Users_ID='".$UsersID."'";
		$UserMethod = $DB->GetRs('distribute_withdraw_methods',"*",$condition);

		//转入余额
		if($UserMethod["Method_Type"]=='yue_income'){
			//增加资金流水
			$amount = $moneys;
			// $rsUser = $DB->GetRs("user", "User_Money", "where Users_ID='".$UsersID."' and User_ID=".$UserID);

			// $Data=array(
				// 'Users_ID'=>$UsersID,
				// 'User_ID'=>$UserID,
				// 'Type'=>1,
				// 'Amount'=>$amount,
				// 'Total'=>$rsUser['User_Money']+$amount,
				// 'Note'=>"佣金提现转入余额 +".$amount,
				// 'CreateTime'=>time()		
			// );

			// $Flag=$DB->Add('user_money_record',$Data);
			// //更新用户余额
			// $Data=array(				
				// 'User_Money'=>$rsUser['User_Money']+$amount					
			// );		
			// $Set = $DB->Set("user",$Data,"where Users_ID='".$UsersID."' and User_ID=".$UserID);
			// $Flag = $Flag && $Set;

			$data = array(
				"Users_ID"=>$UsersID,
				"User_ID"=>$UserID,
				"Record_Total"=>$amount,
				"Record_CreateTime"=>time(),
				"Method_Name" => '余额提现',
				//"Record_Status"=>1,
				"Record_Status"=>0,
				"Record_Yue" => $amount,
				"Record_Fee" => 0,
				"Record_Money" => $amount,
			);
			$Add = $DB->add('distribute_withdraw_record',$data);


			//$Flag = $Flag && $Add;
			$condition = "where User_ID=".$UserID." and Users_ID='".$UsersID."'";
			$Data = array(
				"balance"=>$dsAccount["balance"]-$amount
			);
			
			$Set = $DB->Set('distribute_account',$Data,$condition);
			if ($Set) {
				add_distribute_balance_record($UsersID, [
							'User_ID' => $UserID,
							'Record_Balance' => $dsAccount["balance"],
							'Record_Money' => -$amount,
							'Record_Desc' => '转入余额提现申请',
							'Record_Type' => 7
						]);
			}
			//$Flag = $Flag && $Set;
			$response = array('status'=>1,'msg'=>'提现申请成功,请等待审核!');
		}else{//提现转帐
			$Flag = true;
			$total_money  = $moneys;
			$fee = 0;
			if($rsConfig["Poundage_Ratio"]>0){
				$fee = $total_money * $rsConfig["Poundage_Ratio"] * 0.01;
			}
			$money  = $total_money * (1 - ($rsConfig['Balance_Ratio']/100));//佣金
			if($money<$fee){
				$response = array('status'=>0,'msg'=>'提现金额中的转帐部分小于手续费');
				echo json_encode($response,JSON_UNESCAPED_UNICODE);
				exit;
			}
			$money = $money - $fee;
			$Ratio_money = $total_money * $rsConfig['Balance_Ratio']/100;//余额
			
			//获取用户提现方式		
			
			if($UserMethod["Method_Type"]=='wx_hongbao' && ($money<1 || $money>200)){
				$response = array('status'=>0,'msg'=>'提现金额在1-200之间才可使用微信红包提现');
			}elseif($UserMethod["Method_Type"]=='wx_zhuanzhang' && $money<0){
				$response = array('status'=>0,'msg'=>'提现金额必须大于零才可使用微信红包提现');
			}else{
				$Account_Info = $UserMethod['Method_Name'].' '.$UserMethod['Account_Name'].' '.$UserMethod['Account_Val'].' '.$UserMethod['Bank_Position'];

				$data = array(
					"Users_ID"=>$UsersID,
					"User_ID"=>$UserID,
					"Record_Total"=>$total_money,
					"Record_CreateTime"=>time(),
					"Record_Status"=>0,
					"Record_Yue" => $Ratio_money,
					"Record_Fee" => $fee,
					"Record_Money" => $total_money - ($Ratio_money + $fee),
					"Method_Name" => $UserMethod['Method_Name'],
					"Method_Account" => $UserMethod['Account_Name'],
					"Method_No" => $UserMethod['Account_Val'],
					"Method_Bank" => $UserMethod['Bank_Position']
				);
				$Add = $DB->add('distribute_withdraw_record',$data);
				$Flag = $Flag && $Add;
				$recordid = $DB->insert_id();
				$condition = "where User_ID=".$UserID." and Users_ID='".$UsersID."'";
				$Set = $DB->set('distribute_account',"balance=balance-$total_money",$condition);
				if ($Set) {
				add_distribute_balance_record($UsersID, [
							'User_ID' => $UserID,
							'Record_Balance' => $dsAccount["balance"],
							'Record_Money' => -$total_money,
							'Record_Desc' => '提现申请',
							'Record_Type' => 7
						]);
				}
				$Flag = $Flag && $Set;
				if($Ratio_money>0){
					$rsUser = $DB->GetRs("user", "User_Money", "where Users_ID='".$UsersID."' and User_ID=".$UserID);
					$Ratio_Data = array(
						'Users_ID' => $UsersID,
						'User_ID' => $UserID,
						'Type'=>1,
						'Amount' => $Ratio_money,
						'Total' => $rsUser['User_Money']+$Ratio_money,
						'Note' => "佣金提现 +".$Ratio_money,
						'CreateTime' => time()            
					);
					$Flag = $Flag && $DB->Add('user_money_record', $Ratio_Data);
					// $Data = array(                
						// 'User_Money'=>$rsUser['User_Money']+$Ratio_money                 
					// );
					// $Flag = $Flag && $DB->Set('user',$Data,"where Users_ID='".$UsersID."' and User_ID=".$UserID);
				}
				if($Flag){
					if($rsConfig["TxCustomize"]){
						$response = array('status'=>1,'msg'=>'提现申请提交成功,预计2个工作日内申请通过');
					}else{
						if($UserMethod["Method_Type"]=='wx_hongbao' || $UserMethod["Method_Type"]=='wx_zhuanzhang'){
							require_once(CMS_ROOT.'/include/library/pay_order.class.php');
							$pay_order = new pay_order($DB, 0);
							$Data = $pay_order->withdraw($UsersID,$UserID,$recordid,$UserMethod["Method_Type"]);
							if($Data['status']==1){
								if($UserMethod["Method_Type"]=='wx_hongbao'){
									$response = array('status'=>1,'msg'=>'提现成功，请查看微信红包');
								}else{
									$response = array('status'=>1,'msg'=>'提现成功，请查看微信转账');
								}
							}else{
								$response = array('status'=>0,'msg'=>$Data['msg']);
							}
						}else{
							$response = array('status'=>1,'msg'=>'提现申请提交成功,预计2个工作日内申请通过');
						}
					}
				}else{
					$response = array('status'=>0,'msg'=>'发生位置错误，申请提交失败');
				}
			}
		}
	}
	}else{
		$response = array('status'=>0,'msg'=>'非法操作');
	}

	echo json_encode($response,JSON_UNESCAPED_UNICODE);
}elseif($action == 'removeApplyOrder'){
	$Order_ID = intval($_GET['orderId']);
	$orderstatus = $DB->GetRs('agent_order', 'count(*) as num', ' WHERE `Users_ID`="'.$_GET['UsersID'].'" AND `User_ID` = "' .$UserID. '" AND `Order_ID` = "'.$Order_ID.'"');
	
	if ($orderstatus['num'] = 0) {
		echo json_encode(array('status' => 0, 'msg' => '不存在该信息!'), JSON_UNESCAPED_UNICODE); exit();
	}

	$flag = $DB->Set('agent_order', array('Order_Status' => 3), ' where  `Users_ID`="'.$_GET['UsersID'].'" AND `User_ID` = "' .$UserID. '" AND `Order_ID` = "'.$Order_ID.'"');
	if ($flag) {
		echo json_encode(array('status' => 1, 'msg' => '取消成功!'), JSON_UNESCAPED_UNICODE); exit();
	} else {
		echo json_encode(array('status' => 0, 'msg' => '取消失败!'), JSON_UNESCAPED_UNICODE); exit();
	}

}elseif($action == 'get_product'){
	$Get_Level_ID = $_POST["Get_Level_ID"];
	$GetID = $_POST["GetID"];
	$Flag = $DB->GetRs("user_get_orders","*", "where Users_ID='".$UsersID."' and User_ID=".$UserID.' and Order_Level='.$Get_Level_ID);
	if($Flag){
		$Data=array("status"=>1);
	}else{
		$Data=array("status"=>0,"msg"=>"可以领取");
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
}elseif($action=='Get_change'){
		$rsGet=$DB->GetRs("user_get_product","*","where Users_ID='".$UsersID."' and Get_ID=".$_POST['GetID']);
		if($rsGet){
			if($rsGet['Get_Qty']>=1){
				$Flag=$Flag&&$DB->Set("user_get_product","Get_Qty=Get_Qty-1","where Users_ID='".$UsersID."' and Get_ID=".$_POST['GetID']);
				if(empty($rsGet['Get_Shipping'])){
					$Data=array(
						"Users_ID"=>$UsersID,
						"User_ID"=>$UserID
					);
				}else{
					$AddressID=empty($_POST['AddressID'])?0:$_POST['AddressID'];
					if(empty($_POST['AddressID'])){
					$area_json = read_file(CMS_ROOT.'/data/areaduihuan.js');
					$area_array = json_decode($area_json,TRUE);
					$province_list = $area_array[0];
					
					$Province_Name = $_POST['Province'];
					$length = mb_strlen($Province_Name,'utf-8');
					$length = $length == 2 ? $length : $length-1;
					$Province_Name = mb_substr($Province_Name,0,$length,'utf-8');
					$Address_Province = '';
					foreach($province_list as $id=>$value){
					if($value == $Province_Name){
					$Address_Province = $id;
					break;
					}
					}
					$City_list = $area_array['0,'.$Address_Province];
					$City_Name = $_POST['City'];						
					$Address_City = '';
					foreach($City_list as $id=>$value){
					if($value == $City_Name){
					$Address_City = $id;
					break;
					}
					}
					
					$Area_list = $area_array['0,'.$Address_Province.','.$Address_City];
					$Area_Name = $_POST['Area'];						
					$Address_Area = '';
					foreach($Area_list as $id=>$value){
					if($value == $Area_Name){
					$Address_Area = $id;
					break;
					}
					}
					
						//增加
						$Data=array(
							"Address_Name"=>$_POST['Name'],
							"Address_Mobile"=>$_POST["Mobile"],
							"Address_Province"=>$Address_Province,
							"Address_City"=>$Address_City,
							"Address_Area"=>$Address_Area,
							"Address_Detailed"=>$_POST["Detailed"],
							"Users_ID"=>$UsersID,
							"User_ID"=>$UserID
						);
						$Flag=$Flag&&$DB->Add("user_address",$Data);
					}else{
						$rsAddress=$DB->GetRs("user_address","*","where Users_ID='".$UsersID."' and User_ID='".$UserID."' and Address_ID='".$AddressID."'");
						$Data=array(
							"Address_Name"=>$rsAddress['Address_Name'],
							"Address_Mobile"=>$rsAddress["Address_Mobile"],
							"Address_Province"=>$rsAddress["Address_Province"],
							"Address_City"=>$rsAddress["Address_City"],
							"Address_Area"=>$rsAddress["Address_Area"],
							"Address_Detailed"=>$rsAddress["Address_Detailed"],
							"Users_ID"=>$UsersID,
							"User_ID"=>$UserID
						);
					}
				}
				$Data["Orders_Status"]=0;
				$Data["Get_ID"]=$_POST['GetID'];
				$Data["Order_Level"]=$_POST['Get_Level_ID'];
				$Data["Orders_FinishTime"]=time();
				$Data["Orders_CreateTime"]=time();
				$Flag=$Flag&&$DB->Add('user_get_orders',$Data);				
				if($Flag){
					$Data=array(
						'status'=>1,
						'msg'=>'操作成功！'
					);
				}else{
					$Data=array(
						'status'=>0,
						'msg'=>'网络拥堵，请稍后再试！'
					);
				}
				
			}else{
					$Data=array(
						'status'=>0,
						'msg'=>'礼品已经兑换完了！'
					);
			}
		
		}else{
			$Data=array(
				'status'=>0,
				'msg'=>'请勿非法操作！'
			);
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
}elseif($action == 'checkCityApply'){

	$ajaxCondition = 'WHERE `status` = 1 ';

	if (!empty($_GET['AreaId'])) 
	{
		$ajaxCondition .= ' AND `area_id`= '.intval($_GET['AreaId']);
	}elseif (!empty($_GET['CityId'])) {
		$ajaxCondition .= ' AND `area_id`= '.intval($_GET['CityId']);
	}elseif (!empty($_GET['ProvinceId'])) {
		$ajaxCondition .= ' AND `area_id`= '.intval($_GET['ProvinceId']);
	}
	$ajaxCondition .='AND `Users_ID`="'.$UsersID.'"';
	$s = $DB->GetRs('distribute_agent_areas', 'count(id) AS num', $ajaxCondition);
	if ($s['num'] > 0) 
	{
		echo json_encode(array('status' => 1, 'msg' => '该区域已被申请'), JSON_UNESCAPED_UNICODE);
	} else {
		echo json_encode(array('status' => 0, 'msg' => '该区域可以申请'), JSON_UNESCAPED_UNICODE);
	}
	exit();

}elseif($action == 'removeShaOrder'){
	$Order_ID = intval($_GET['orderId']);
	$orderstatus = $DB->GetRs('sha_order', 'count(*) as num', ' WHERE `Users_ID`="'.$_GET['UsersID'].'" AND `User_ID` = "' .$UserID. '" AND `Order_ID` = "'.$Order_ID.'"');
	
	if ($orderstatus['num'] = 0) {
		echo json_encode(array('status' => 0, 'msg' => '不存在该信息!'), JSON_UNESCAPED_UNICODE); exit();
	}

	$flag = $DB->Set('sha_order', array('Order_Status' => 3), ' where  `Users_ID`="'.$_GET['UsersID'].'" AND `User_ID` = "' .$UserID. '" AND `Order_ID` = "'.$Order_ID.'"');
	if ($flag) {
		echo json_encode(array('status' => 1, 'msg' => '取消成功!'), JSON_UNESCAPED_UNICODE); exit();
	} else {
		echo json_encode(array('status' => 0, 'msg' => '取消失败!'), JSON_UNESCAPED_UNICODE); exit();
	}
}elseif($action == 'check_exist'){
	$field = $_GET['field'];
	if($field == 'Real_Name'){
		$value = $_GET['real_name'];
	}elseif($field == 'ID_Card'){
		$value = $_GET['idcard'];
	}elseif($field == 'Email'){
		$value = $_GET['email'];
	}
	$condition = "where User_ID=".$UserID." and ".$field."='".$value."'";
	
	$rsUser = $DB->getRs("distribute_account","*",$condition);
	
	if(!empty($rsUser)){
		echo 'false';
	}else{
		echo 'true';
	}
}elseif($action == "add_user_withdraw_method"){

	
	$data = array();
	$data['Users_ID'] = $UsersID;
	$data['User_ID'] = $UserID;
	$data['Method_Name'] = $_POST['Method_Name'];
	$data['Method_Type'] = $_POST['Method_Type'];
	$data['Account_Name'] = $_POST['Account_Name'];
    $data['Account_Val'] = !empty($_POST['Account_Val'])?$_POST['Account_Val']:'';
	$data['Bank_Position'] = !empty($_POST['Bank_Position'])?$_POST['Bank_Position']:'';
	$data['Method_CreateTime'] = time();
	$data['Method_Status'] = 1;		
	
	$Flag = $DB->add('distribute_withdraw_methods',$data);
	
	if($Flag){
		$response = array('status'=>1,'msg'=>'添加新的提现方式成功');
	}else{
		$response = array('status'=>0,'msg'=>'添加新的提现方式失败');
	}
	
	echo json_encode($response,JSON_UNESCAPED_UNICODE);
	
}elseif($action == "delete_user_withdraw_method"){
	$method_id = $_POST['method_id'];
	
	$condition = "User_Method_ID=".$method_id." and Users_ID='".$UsersID."'";
	$Flag = $DB->Del('distribute_withdraw_methods',$condition);
	
	if($Flag){
		$response = array('status'=>1);
	}else{
		$response = array('status'=>0);
	}

	echo json_encode($response,JSON_UNESCAPED_UNICODE);

}elseif($action == 'store_poster'){
	
 	$img = $_POST['dataUrl'];
 	$owner_id = $_POST['owner_id'];
	$file_path = '/data/poster/'.$UsersID.$owner_id.'.png';
	$web_path = '/data/poster/'.$UsersID.$owner_id.'.png';
	 
	$Flag = generate_postere($img,$UsersID,$owner_id);
	
	if($Flag){
		
		$condition = "Where Users_ID = '".$UsersID."' and User_ID=".$owner_id;	
		$DB->Set('distribute_account',array('Is_Regeposter'=>0),$condition);
		$response = array('status'=>1,'poster_path'=>$web_path);
		
	}else{
		$response = array('status'=>0,'msg'=>'Unable to save the file.');
	}
	
	echo json_encode($response,JSON_UNESCAPED_UNICODE);
	
}elseif($action == 'store_posterpop'){	
 	$img = $_POST['dataUrl'];
 	$owner_id = $_POST['owner_id'];
	$file_path = '/data/poster/'.$UsersID.$owner_id.'pop.png';
	$web_path = '/data/poster/'.$UsersID.$owner_id.'pop.png';
	
	$Flag = generate_posterepop($img,$UsersID,$owner_id);	
	if($Flag){		
		$condition = "Where Users_ID = '".$UsersID."' and User_ID=".$owner_id;	
		$DB->Set('distribute_account',array('Is_Regeposter'=>0),$condition);
		$response = array('status'=>1,'poster_path'=>$web_path);
		
	}else{
		$response = array('status'=>0,'msg'=>'Unable to save the file.');
	}
	
	echo json_encode($response,JSON_UNESCAPED_UNICODE);
	
}elseif($action == 'get_ex_bonus'){
   //检测此账号是否有额外奖金
   $condition = "where Users_ID='".$UsersID."' and User_ID=".$UserID;
   $rsDistirbuteAccount = $DB->getRs('distribute_account','balance',$condition);
   $rsDistirbuteRecord = $DB->getRs('distribute_account_record','SUM(Nobi_Money) as money',$condition." and Nobi_Money>0 and Record_Status=2 and Nobi_Status=0");
   $rsDistirbuteRecord["money"] = empty($rsDistirbuteRecord["money"]) ? 0 : $rsDistirbuteRecord["money"];
   if($rsDistirbuteRecord["money"] == 0){
   		$response = array('status'=>0,'msg'=>'分销额外奖金为零');
   }else{
   		 		
   		$data = array('Ex_Bonus'=>0,'balance'=>$rsDistirbuteAccount['balance']+$rsDistirbuteRecord["money"]);
   					  
   		$Flag = $DB->Set('distribute_account',$data,$condition);
		if ($Flag) {
		add_distribute_balance_record($UsersID, [
							'User_ID' => $UserID,
							'Dis_Balance' => $rsDistirbuteAccount["balance"],
							'Record_Money' => $rsDistirbuteRecord["money"],
							'Record_Desc' => '分销额外奖金',
							'Record_Type' => 9
						]);
		}
		$DB->Set('distribute_account_record',array("Nobi_Status"=>1),"where Users_ID='".$UsersID."' and User_ID=".$UserID." and Nobi_Money>0 and Record_Status=2 and Nobi_Status=0");
   		$response = array('status'=>1, 'msg'=>'获取奖金成功');
   }

   echo json_encode($response,JSON_UNESCAPED_UNICODE);

}elseif($action == 'sqsallman'){
	if (empty($_POST['Name']) || empty($_POST['Mobile']) || empty($_POST['JSON'])) {
	$response = array('status'=>0, 'msg'=>'不能为空');echo json_encode($response,JSON_UNESCAPED_UNICODE);exit;
	}
	$condition = "where Users_ID='".$UsersID."' and User_ID=".$UserID;
	$distribute_smansq_record = $DB->getRs('distribute_smansq_record','Record_ID',$condition);
	if ($distribute_smansq_record) {
			$Data = array(				
				"Salesman_SqName" => $_POST['Name'],
				"Salesman_SqTelephone" => $_POST['Mobile'],
				"Salesman_Sq" => json_encode($_POST['JSON'], JSON_UNESCAPED_UNICODE),
				"Record_Status" => 0,
				"Record_CreateTime" => time()
			);
		$Flag = $DB->Set('distribute_smansq_record',$Data,$condition);
	} else {
		$data = array(
				"Users_ID" => $UsersID,
				"User_ID" => $UserID,
				"Salesman_SqName" => $_POST['Name'],
				"Salesman_SqTelephone" => $_POST['Mobile'],
				"Salesman_Sq" => json_encode($_POST['JSON'], JSON_UNESCAPED_UNICODE),
				"Record_CreateTime" => time()
			);
	$Flag = $DB->Add('distribute_smansq_record',$data,$condition);
	}
		if ($Flag) {
			$response = array('status'=>1, 'msg'=>'提交成功！');
		} else {
			$response = array('status'=>0, 'msg'=>'提交失败！');
		}
   echo json_encode($response,JSON_UNESCAPED_UNICODE);
}elseif( $action== 'check_mobile_exist'){
	
	$User_Mobile = $request->input('User_Mobile');
    
	$user = User::multiWhere(array('Users_ID'=>$UsersID,'User_Mobile'=>$User_Mobile))
	             ->get()
				 ->first();
	if(!empty($user)){
		echo 'false';
	}else{
		echo 'true';
	}
	
}elseif($action == 'send_short_msg'){

      //发送短信功能未开启
      if($setting["sms_enabled"]==0){
			$Data = array(
				"status"=> 0,
				"msg"=>"短信发送功能已关闭"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}

		$mobile = trim($request->input('mobile'));
       
		if(!$mobile){
			$Data = array(
				"status"=> 0,
				"msg"=>"请输入手机号码"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}

		
		if(!isset($_SESSION[$UsersID.'mobile_send']) || empty($_SESSION[$UsersID.'mobile_send'])){
			$_SESSION[$UsersID.'mobile_send'] = 0;
		}
		
		
		if(isset($_SESSION[$UsersID.'mobile_time']) && !empty($_SESSION[$UsersID.'mobile_time'])){
			if(time() - $_SESSION[$UsersID.'mobile_time'] < 300){
				$Data = array(
					"status"=> 0,
					"msg"=>"发送短信过快"
				);
				echo json_encode($Data,JSON_UNESCAPED_UNICODE);
				exit;
			}
		}
		
		
		
		if($_SESSION[$UsersID.'mobile_send'] > 4){
			$Data = array(
				"status"=> 0,
				"msg"=>"发送短信次数频繁"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}		


		//防止号码重复
		$user = User::multiWhere(array('Users_ID'=>$UsersID,'User_Mobile'=>$mobile))
		             ->get()
					 ->first();
		if(!empty($user)){
			$Data = array(
				"status"=> 0,
				"msg"=>"此手机号已被占用"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}

	
		$code = randcode(6);		
		
		$message = $SiteName."注册验证码：".$code."。此验证码十分钟有效。";
		require_once(CMS_ROOT.'/Framework/Ext/sms.func.php');
	
		$result = send_sms($mobile,$message,$UsersID);
		
		if($result==1){
			$_SESSION[$UsersID.'mobile'] = $mobile;
			$_SESSION[$UsersID.'sms_code'] = $code;
			$_SESSION[$UsersID.'mobile_code'] = md5($mobile.'|'.$code);
			$_SESSION[$UsersID.'mobile_time'] = time();
			$_SESSION[$UsersID.'mobile_send'] = $_SESSION[$UsersID.'mobile_send'] + 1;
			
			$Data = array(
				"status"=> 1,
				"msg"=>"发送成功，验证码四分钟有效。"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 0,
				"msg"=>"发送失败"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
}elseif($action == 'get_level_products'){
	$LevelID = $_POST['LevelID'];
	$rsLevel = $DB->GetRs('distribute_level','*','where Users_ID="'.$UsersID.'" and Level_ID='.$LevelID);
	if(!$rsLevel){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	if($rsLevel['Level_LimitType']<>2){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别条件已发生改变'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	$arr = explode('|',$rsLevel['Level_LimitValue']);
	if($arr[0]==0){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别条件已发生改变'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($arr[1])){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别条件未设置商品'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$html = '';
	$DB->Get('shop_products','Products_ID,Products_Name,Products_Json,Products_PriceY,Products_PriceX','where Users_ID="'.$UsersID.'" and Products_ID in('.$arr[1].')');
	$i = 0;
	while($r = $DB->fetch_assoc()){
		$Json = json_decode($r['Products_Json'],true);
		$html .= '<div class="items"><ul><li class="img"><a href="'.shop_url().'products/'.$r['Products_ID'].'/"><img src="'.(empty($Json["ImgPath"][0]) ? '' : $Json["ImgPath"][0]).'" /></a></li><li class="price">&yen;'.$r['Products_PriceX'].'<span>&yen;'.$r['Products_PriceY'].'</span></li><li class="name"><a href="'.shop_url().'products/'.$r['Products_ID'].'/">'.$r['Products_Name'].'</a></li></li></ul></div>';
		if($i%2==1){
			$html .= '<div class="clear"></div>';
		}
		$i++;
	}
	
	$Data = array(
		"status"=> 1,
		"msg"=>$html
	);
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
}elseif($action == 'get_level_price'){
	$LevelID = $_POST['LevelID'];
	$rsLevel = $DB->GetRs('distribute_level','*','where Users_ID="'.$UsersID.'" and Level_ID='.$LevelID);
	if(!$rsLevel){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	if($rsLevel['Level_LimitType']<>0){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别条件已发生改变'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($rsLevel['Level_LimitValue'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别条件已发生改变'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$Data = array(
		"status"=> 1,
		"price"=>number_format($rsLevel['Level_LimitValue'],2,'.','')
	);
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
}elseif($action == 'deal_level_order'){
	$LevelID = $_POST['LevelID'];
	$rsLevel = $DB->GetRs('distribute_level','*','where Users_ID="'.$UsersID.'" and Level_ID='.$LevelID);
	if(!$rsLevel){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	if($rsLevel['Level_LimitType']<>0){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别条件已发生改变'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($rsLevel['Level_LimitValue'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别条件已发生改变'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(!empty($UserID)){
		$User_ID = $UserID;
		$rsUser = $DB->GetRs('user','Owner_Id','where User_ID='.$User_ID);
		$dis_level = get_dis_level($DB,$UsersID);
		if($rsLevel['Present_type'] == 2){
			$Order_Present = $rsLevel['Level_PresentValue'];
			$Order_BalancePrice = $rsLevel['Level_LimitValue'] + $Order_Present;
		}else{
			$Order_Present = 0;
			$Order_BalancePrice = 0;
		}
		if($rsLevel['Present_type'] == 1){
			$Order_Level_ID = $rsLevel['Level_ID'];
			
		}else{
			$Order_Level_ID = 0;
		}
		$JSON=empty($_POST['JSON'])?array():$_POST['JSON'];
		$datetime = time();
		$rsorder_dis = $DB->GetRs('distribute_order','User_ID','where User_ID='.$User_ID.' and Level_ID='.$LevelID);
		if ($rsorder_dis) {
		   $DB->Del('distribute_order','User_ID='.$User_ID.' and Level_ID='.$LevelID);
		}
		$Data = array(
			'Users_ID'=> $UsersID,
			'User_ID'=>$User_ID,
			'Address_Name'=> !empty($_POST['Name'])?$_POST['Name']:'',
			'Address_Mobile'=>!empty($_POST['Mobile'])?$_POST['Mobile']:'',	
			'Address_Detail'=>!empty($_POST['Detail'])?$_POST['Detail']:'',	
			'Address_WeixinID'=>!empty($_POST['WeixinID'])?$_POST['WeixinID']:'',	
			'Order_TotalPrice'=>number_format($rsLevel['Level_LimitValue'],2,'.',''),
			'Owner_ID'=>$rsUser['Owner_Id'],
			'Level_ID'=>$LevelID,
			'Level_Name'=>$rsLevel['Level_Name'],
			'Order_CreateTime'=>$datetime,
			'Order_Present'=>$Order_Present,
			'Order_LevelID'=>$Order_Level_ID,
			'Order_BalancePrice'=> $Order_BalancePrice,
			'Order_Form'=> json_encode($JSON,JSON_UNESCAPED_UNICODE),
			'Order_Present_type'=>!empty($rsLevel['Present_type'])?$rsLevel['Present_type']:0,
			'Order_Status'=>1
		);
		$Flag = $DB->Add('distribute_order',$Data);
		$order_id = $DB->insert_id();
		$ordersn = date("Ymd",$datetime).$order_id;
		if($Flag){
			$Data = array(
				"status"=> 1,
				'orderid'=>$order_id,
				'orderhao'=>$ordersn,
				'money'=>number_format($rsLevel['Level_LimitValue'],2,'.','')
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 0,
				"msg"=>'提交订单失败'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	} else {
        $Data = array(
            "status"=> 0,
            "msg"=>'提交订单失败'
        );
        echo json_encode($Data,JSON_UNESCAPED_UNICODE);
        exit;
    }
}elseif($action == 'deal_level_order_pay'){
	if(empty($_POST['OrderID'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($_POST['password'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$OrderID = $_POST['OrderID'];
	$rsOrder = $DB->GetRs('distribute_order','*','where Users_ID="'.$UsersID.'" and Order_ID='.$OrderID.' and User_ID='.$UserID);
	
	if(!$rsOrder){
		$Data = array(
			"status"=> 0,
			"msg"=>'订单不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsOrder['Order_Status']<>1){
		$Data = array(
			"status"=> 0,
			"msg"=>'该订单不是待付款状态'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$rsUser = $DB->GetRs('user','*','where User_ID='.$rsOrder['User_ID']);	
// echo '<pre>';
// print_R($rsUser);
// exit;
	if($rsUser['User_PayPassword'] != md5($_POST['password'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'支付密码错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsUser['User_Money'] < $rsOrder['Order_TotalPrice']){
		$Data = array(
			"status"=> 0,
			"msg"=>'当前余额不足'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	require_once(CMS_ROOT.'/include/library/pay_order.class.php');
	$pay_order = new pay_order($DB, $OrderID);
	$response = $pay_order->deal_distribute_order();
	$rsLevel = $DB->GetRs('distribute_level','*','where Users_ID="'.$UsersID.'" and Level_ID='.$rsOrder['Level_ID']);
	$rsUser_money = $DB->GetRs('user','*',' WHERE `Users_ID`="' .$rsOrder['Users_ID']. '" AND `User_ID` = '.$rsOrder['User_ID']);
	
	if($response['status']){
		if($rsOrder['Order_Present_type'] == 1){
			$Data = array(
				"status"=> 1,
				'msg'=>'支付成功，您已成功成为'.$rsOrder['Level_Name'].',可以领取商品。',
				'url'=>distribute_url().'?love'
			);
		}elseif($rsOrder['Order_Present_type'] == 2){
			$Data = array(
				"status"=> 1,
				'msg'=>'支付成功，您已成功成为'.$rsOrder['Level_Name'].','.$rsOrder['Order_TotalPrice'].'元已转存到余额赠送'.$rsOrder['Order_Present'].'元',
				'url'=>distribute_url().'?love'
			);
		}else{
			$Data = array(
				"status"=> 1,
				'msg'=>'支付成功，您已成功成为'.$rsOrder['Level_Name'],
				'url'=>distribute_url().'?love'
			);
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}else{
		$Data = array(
			"status"=> 0,
			"msg"=>'支付失败'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
}elseif ($action == 'getmanualorder') { // 根据User_ID获取是否提交过申请和申请的状态
    $manualorder = $DB->GetRs("dismanual_order","*","where Users_ID = '" . $UsersID . "' and User_ID = " . $UserID);
    if (!empty($manualorder)) {
        $Data = [
            "errorCode" => 0,
            "msg" => '请求成功',
            "data" => $manualorder
        ];
        $Data = [
            'errorCode' => 0,
            'msg' => '请求成功',
            'data' => $manualorder
        ];
    } else {
        $Data = [
            "errorCode" => 2,
            "msg" => '暂无申请记录'
        ];
    }
    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
    exit;

}elseif($action == 'manual_tj'){	//手动提交分销商申请

    if (empty($UserID)) {
        $Data = [
            "status" => -1,
            "msg" => '未登录，请去登录'
        ];
    } else {
        $rsUser = $DB->GetRs('user', 'User_Mobile, User_Name, User_NickName', 'where User_ID = ' . $UserID);

        //获得分销级别
        $dis_level = get_dis_level($DB, $UsersID);
        $low_level = array_shift($dis_level);

        if (!empty($low_level) && $low_level['Level_LimitType'] == 4) {
            $JSON = empty($_POST['JSON']) ? '' : (is_array($_POST['JSON']) ? json_encode($_POST['JSON'], JSON_UNESCAPED_UNICODE) : $_POST['JSON']);
            $manualorder = $DB->GetRs("dismanual_order","Order_Status, Refuse_Be","where Users_ID = '" . $UsersID . "' and User_ID = " . $UserID);
            if (!empty($manualorder)) {
                if ($manualorder['Order_Status'] == 0) {
                    $Data = [
                        "status" => 0,
                        "msg" => '请求已提交，请勿重复提交'
                    ];
                } else {
                    $Data = [
                        'Applyfor_Name' => !empty($_POST['Name']) ? $_POST['Name'] : '',
                        'Applyfor_Mobile' => !empty($_POST['Mobile']) ? $_POST['Mobile'] : '',
                        'Applyfor_form' => $JSON,
                        'Order_Status' => $low_level['Level_Isauditing'] == 1 ? 0 : 1,
                        'Order_CreateTime' => time()
                    ];
                    $flag = $DB->Set('dismanual_order', $Data, 'where Users_ID = "' . $UsersID . '" and User_ID = ' . $UserID);
                    if ($flag) {
                        $Data = [
                            "status" => 1,
                            "msg" => '提交成功' . ($low_level['Level_Isauditing'] == 1 ? ', 等待商家审核' : ''),
                            "url" => '/api/' . $UsersID . '/distribute/join/'
                        ];
                    } else {
                        $Data = [
                            "status" => 0,
                            "msg" => '提交失败，请稍后重试'
                        ];
                    }
                }
            } else {
                //提交请求
                $Data = [
                    'Users_ID' => $UsersID,
                    'User_ID' => $UserID,
                    'Applyfor_Name' => !empty($_POST['Name']) ? $_POST['Name'] : '',
                    'Applyfor_Mobile' => !empty($_POST['Mobile']) ? $_POST['Mobile'] : '',
                    'Applyfor_form' => $JSON,
                    'Order_Status' => $low_level['Level_Isauditing'] == 1 ? 0 : 1,
                    'Order_CreateTime' => time()
                ];
                $flag = $DB->Add('dismanual_order', $Data);
                if ($flag) {
                    $Data = [
                        "status" => 1,
                        "msg" => '提交成功' . ($low_level['Level_Isauditing'] == 1 ? ', 等待商家审核' : ''),
                        "url" => '/api/' . $UsersID . '/distribute/join/'
                    ];
                } else {
                    $Data = [
                        "status" => 0,
                        "msg" => '提交失败，请稍后重试'
                    ];
                }
            }
        } else {
            $Data = [
                "status" => -1,
                "msg" => '成为分销商方法变更，请重新加载页面'
            ];
        }
    }
    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
    exit;

}elseif($action == 'sendMessageContent'){
	$vdata = array();
	if (!empty($_POST['Mess_Content'])) {
		$currentTime = time();
		$vdata['Mess_Content'] = $_POST['Mess_Content'];
		$vdata['Mess_Status'] = 0;
		$vdata['UsersID'] = $UsersID;
		$vdata['Mess_CreateTime'] = $currentTime;
		$vdata['Receiver_User_ID'] = intval($_POST['Receiver_User_ID']);
		$vdata['User_ID'] = intval($_POST['UserID']);

		$flag = $DB->Add('distribute_account_message', $vdata);
		if ($flag) {
			echo json_encode(array('status' => 1, 'msg' => '消息发送成功', 'time' => date('Y-m-d H:i:s',$currentTime)));
		}else {
			echo json_encode(array('status' => 0, 'msg' => '消息发送失败', 'time' => date('Y-m-d H:i:s',$currentTime)));
		}
	}
	
}elseif($action == 'get_distribute_pay_method'){
	if(empty($_POST['OrderID'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($_POST['method'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$method = $_POST['method'];
	$PaymentMethod = array(
		"微支付" => "1",
		"支付宝" => "2",
		"易宝支付" => "3",
		"银联支付" => "4",
		"线下支付" => "5"
	);
	$OrderID = $_POST['OrderID'];
	$rsOrder = $DB->GetRs('distribute_order','*','where Users_ID="'.$UsersID.'" and Order_ID='.$OrderID.' and User_ID='.$UserID);
	$rsLevel = $DB->GetRs('distribute_level','*','where Users_ID="'.$UsersID.'" and Level_ID='.$rsOrder['Level_ID']);
	$rsUser_money = $DB->GetRs('user','*',' WHERE `Users_ID`="' .$rsOrder['Users_ID']. '" AND `User_ID` = '.$rsOrder['User_ID']);
	$rsUser = $DB->GetRs('user','*','where User_ID='.$rsOrder['User_ID']);	
		
	if(!$rsOrder){
		$Data = array(
			"status"=> 0,
			"msg"=>'订单不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsOrder['Order_Status']<>1){
		$Data = array(
			"status"=> 0,
			"msg"=>'该订单不是待付款状态'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
	if($PaymentMethod[$method]==1){//微支付
		$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
		if($rsPay["PaymentWxpayEnabled"]==0 || empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“微支付”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
		if($rsPay["PaymentWxpayType"]==1){
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/wxpay2/sendto_distribute.php?UsersID='.$UsersID.'_'.$OrderID
			);
			
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/wxpay/sendto_distribute.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
	}elseif($PaymentMethod[$method]==2){//支付宝		
		if($rsPay["Payment_AlipayEnabled"]==0 || empty($rsPay["Payment_AlipayPartner"]) || empty($rsPay["Payment_AlipayKey"]) || empty($rsPay["Payment_AlipayAccount"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“支付宝”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/alipay/sendto_distribute.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	}elseif($PaymentMethod[$method]==3){//易宝支付	
		if(empty($rsPay["PaymentYeepayAccount"]) || empty($rsPay["PaymentYeepayPrivateKey"]) || empty($rsPay["PaymentYeepayPublicKey"]) || empty($rsPay["PaymentYeepayYeepayPublicKey"]) || empty($rsPay["PaymentYeepayProductCatalog"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“易宝支付”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/yeepay/sendto.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	}elseif($PaymentMethod[$method]==4){//银联支付
		if($rsPay["Payment_UnionpayEnabled"]==0 || empty($rsPay["Payment_UnionpayAccount"]) || empty($rsPay["PaymentUnionpayPfx"]) || empty($rsPay["PaymentUnionpayPfxpwd"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“易宝支付”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/Unionpay/sendto.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	}elseif($PaymentMethod[$method]==5){//线下支付		
			$Data = array(
				"status"=> 1,
				"url"=>'/api/'.$UsersID.'/shop/cart/complete_dispay/huodao/'.$OrderID.'/?love'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;		
	}
}elseif($action=='get_level_info_upgrade'){//获取升级条件
	$LevelID = $_POST['LevelID'];
	$settype = $_POST['settype'];
	$tip = isset($_POST['tip']) ? $_POST['tip'] : '';
	$rsLevel = $DB->GetRs('distribute_level','*','where Users_ID="'.$UsersID.'" and Level_ID='.$LevelID);
	if(!$rsLevel){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$rsAccount = $DB->GetRs('distribute_account','Level_ID','where User_ID='.$UserID);
	$my_level = $rsAccount['Level_ID'];
	
	if($my_level>=$LevelID){
		$Data = array(
			"status"=> 0,
			"msg"=>'您的级别必须低于要升级的级别'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if ($settype != 1) {
	$money = 0;
	if($rsLevel['Level_UpdateType']==0){//补差价
		$DB->Get('distribute_level','Level_Name,Level_UpdateType,Level_UpdateValue','where Users_ID="'.$UsersID.'" and Level_ID>'.$my_level.' and Level_ID<'.$LevelID.' order by Level_ID asc');
		while($r = $DB->fetch_assoc()){
			if($r["Level_UpdateType"]==1){
				$Data = array(
					"status"=> 0,
					"msg"=>$r['Level_Name'].'的升级条件是购买指定商品，请先升级至'.$r['Level_Name']
				);
				echo json_encode($Data,JSON_UNESCAPED_UNICODE);
				exit;
			}
			
			$money += $r['Level_UpdateValue'];
		}
		
		$money = $money + $rsLevel['Level_UpdateValue'];
		$Data = array(
			"status"=> 1,
			"price"=>$money
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	} else if ($rsLevel['Level_UpdateType']==2){//指定价
		$DB->Get('distribute_level','Level_Name,Level_UpdateType,Level_UpdateValue','where Users_ID="'.$UsersID.'" and Level_ID>'.$my_level.' and Level_ID<'.$LevelID.' order by Level_ID asc');
		while($r = $DB->fetch_assoc()){
			if($r["Level_UpdateType"]==1){
				$Data = array(
					"status"=> 0,
					"msg"=>$r['Level_Name'].'的升级条件是购买指定商品，请先升级至'.$r['Level_Name']
				);
				echo json_encode($Data,JSON_UNESCAPED_UNICODE);
				exit;
			}
			
			$money = $r['Level_UpdateValue'];
		}
		
		$money = $rsLevel['Level_UpdateValue'];
		$Data = array(
			"status"=> 1,
			"price"=>$money
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}else{//购买指定商品
		$html = '';
		$DB->Get('shop_products','Products_ID,Products_Name,Products_Json,Products_PriceY,Products_PriceX','where Users_ID="'.$UsersID.'" and Products_ID in('.$rsLevel['Level_UpdateValue'].')');
		$i = 0;
		while($r = $DB->fetch_assoc()){
			$Json = json_decode($r['Products_Json'],true);
			$html .= '<div class="items"><ul><li class="img"><a href="'.shop_url().'products/'.$r['Products_ID'].'/"><img src="'.(empty($Json["ImgPath"][0]) ? '' : $Json["ImgPath"][0]).'" /></a></li><li class="price">&yen;'.$r['Products_PriceX'].'<span>&yen;'.$r['Products_PriceY'].'</span></li><li class="name"><a href="'.shop_url().'products/'.$r['Products_ID'].'/">'.$r['Products_Name'].'</a></li></li></ul></div>';
			if($i%2==1){
				$html .= '<div class="clear"></div>';
			}
			$i++;
		}
		
		$Data = array(
			"status"=> 2,
			"msg"=>$html
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	} else {
		if ($tip == '继续消费') {
			$Data = array(
				"status"=> 3,
				"gourl"=> '/api/'.$UsersID.'/shop/?love',
				"msg"=>'累计消费不足，升级失败！'
			);
		} else {
			$Data = array(
				"status"=> 3,
				"gourl"=> distribute_url(),
				"msg"=>'升级成功！'
			);
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
}elseif($action == 'upgrade_level_order'){
	$LevelID = $_POST['LevelID'];
	$rsLevel = $DB->GetRs('distribute_level','*','where Users_ID="'.$UsersID.'" and Level_ID='.$LevelID);
	$DB->Get('distribute_level','*','where Users_ID="'.$UsersID.'"');
	while($r = $DB->fetch_assoc()){
		$rsLevels[] = $r;
	}
	$User_ID = $UserID;
	$rsUser = $DB->GetRs('distribute_account','*','where User_ID='.$User_ID.' and Users_ID="'.$UsersID.'"');
	$distribute_level = $DB->GetRs('distribute_level','*','where Level_ID='.$rsUser['Level_ID'].' and Users_ID="'.$UsersID.'"');
	$Order_Present = 0;
	if($rsLevel['Present_type'] == 2){
		foreach($rsLevels as $key=>$value){
			if($value['Level_ID']<=$distribute_level['Level_ID']){
				continue;
			}
			if($value['Level_ID']>$LevelID){
				continue;
			}
			if ($value['Update_switch'] == 1) {
			if ($value['Level_UpdateType'] == 0) {
			$Order_Present += $value['Level_PresentUpdateValue'];
			} else {
			$Order_Present = $value['Level_PresentUpdateValue'];
			}
			} else {
			$Order_Present = $value['Level_PresentValue'];
			}
		}
	}
	if($rsLevel['Present_type'] == 1){
		$Order_Level_ID = $rsLevel['Level_ID'];
	}else{
		$Order_Level_ID = 0;
	}
	if(!$rsLevel){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	if($rsLevel['Level_UpdateType'] == 1){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别升级条件已发生改变'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($rsLevel['Level_UpdateValue'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'该级别升级条件已发生改变'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$rsAccount = $DB->GetRs('distribute_account','Level_ID','where User_ID='.$UserID);
	$my_level = $rsAccount['Level_ID'];
	
	if($my_level>=$LevelID){
		$Data = array(
			"status"=> 0,
			"msg"=>'您的级别必须低于要升级的级别'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	//佣金设置明细
	$distributes = array();
	$dis_config = dis_config($UsersID);
	for($i=1;$i<=$dis_config['Dis_Level'];$i++){
		$distributes[$i] = 0;
	}
	
	$money = 0;
	
	$DB->Get('distribute_level','Level_Name,Level_UpdateType,Level_UpdateValue,Level_UpdateDistributes','where Users_ID="'.$UsersID.'" and Level_ID>'.$my_level.' and Level_ID<'.$LevelID.' order by Level_ID asc');
	while($r = $DB->fetch_assoc()){
		if($r["Level_UpdateType"]==1){
			$Data = array(
				"status"=> 0,
				"msg"=>$r['Level_Name'].'的升级条件是购买指定商品，请先升级至'.$r['Level_Name']
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
		$Upgrade = json_decode($r['Level_UpdateDistributes'], true);
		foreach($distributes as $key=>$value){
			if(empty($Upgrade[$key])){
				continue;
			}
			$distributes[$key] = $distributes[$key] + $Upgrade[$key];
		}
			
		if ($rsLevel['Update_switch'] == 1) {
		if ($rsLevel['Level_UpdateType'] == 0) {	
		$money += $r['Level_UpdateValue'];
		} else {
		$money = $r['Level_UpdateValue'];
		}
		} else {
		$money = $rsLevel['Level_LimitValue'];
		}
	}
		
	$Upgrade = json_decode($rsLevel['Level_UpdateDistributes'], true);
	foreach($distributes as $key=>$value){
		if(empty($Upgrade[$key])){
			continue;
		}
		$distributes[$key] = $distributes[$key] + $Upgrade[$key];
	}
	
	if ($rsLevel['Update_switch'] == 1) {
		if ($rsLevel['Level_UpdateType'] == 0) {
		$money = $money + $rsLevel['Level_UpdateValue'];
		} else {
		$money = $rsLevel['Level_UpdateValue'];
		}
		} else {
		$money = $rsLevel['Level_LimitValue'];
		}
	$datetime = time();
	$Data = array(
		'Users_ID'=> $UsersID,
		'User_ID'=>$User_ID,
		'Address_Name'=>'',
		'Address_Mobile'=>'',	
		'Address_Detail'=>'',	
		'Address_WeixinID'=>'',	
		'Order_TotalPrice'=>number_format($money,2,'.',''),
		'Owner_ID'=>$User_ID,
		'Level_ID'=>$LevelID,
		'Level_Name'=>$rsLevel['Level_Name'],
		'Order_CreateTime'=>$datetime,
		'Order_Type'=>1,
		'Order_Status'=>1,
		'Order_Present'=>$Order_Present,
		'Order_LevelID'=>$Order_Level_ID,
		'Order_Present_type'=>!empty($rsLevel['Present_type'])?$rsLevel['Present_type']:0,
		'UpgradeDistributes'=>json_encode($distributes,JSON_UNESCAPED_UNICODE)
	);
	$Flag = $DB->Add('distribute_order',$Data);
	$order_id = $DB->insert_id();
	$ordersn = date("Ymd",$datetime).$order_id;
	if($Flag){
		$Data = array(
			"status"=> 1,
			'orderid'=>$order_id,
			'orderhao'=>$ordersn,
			'money'=>number_format($money,2,'.','')
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}else{
		$Data = array(
			"status"=> 0,
			"msg"=>'提交订单失败'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
}elseif($action == "deal_proxy_order_pay"){

	if(empty($_POST['OrderID'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($_POST['password'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$OrderID = $_POST['OrderID'];
	$rsOrder = $DB->GetRs('agent_order','*','where Users_ID="'.$UsersID.'" and Order_ID='.$OrderID.' and User_ID='.$UserID);
	
	if(!$rsOrder){
		$Data = array(
			"status"=> 0,
			"msg"=>'订单不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsOrder['Order_Status']<>1){
		$Data = array(
			"status"=> 0,
			"msg"=>'该订单不是待付款状态'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$rsUser = $DB->GetRs('user','User_Money,User_PayPassword','where User_ID='.$rsOrder['User_ID']);

	if($rsUser['User_PayPassword'] != md5($_POST['password'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'支付密码错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsUser['User_Money'] < $rsOrder['Order_TotalPrice']){
		$Data = array(
			"status"=> 0,
			"msg"=>'当前余额不足'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	require_once(CMS_ROOT.'/include/library/pay_order.class.php');
	$pay_order = new pay_order($DB, $OrderID);
	$response = $pay_order->deal_proxy_order();
	
	if($response['status']){
		$Data = array(
			"status"=> 1,
			'msg'=>'支付成功，你已经成为'.$rsOrder['AreaMark'],
			'url'=>distribute_url() . '?love',
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}else{
		$Data = array(
			"status"=> 0,
			"msg"=>'支付失败'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}

}elseif($action == 'get_proxy_pay_method'){
	if(empty($_POST['OrderID'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($_POST['method'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$method = $_POST['method'];
	
	$OrderID = $_POST['OrderID'];
    $rsOrder = $DB->GetRs('agent_order','o.*, d.Account_ID',' as o left join distribute_account as d on o.User_ID = d.User_ID where o.Order_ID = ' . $OrderID . ' and o.User_ID = ' . $UserID);

    // 检查此地区是否已被代理
    $res = agent_area_check([ empty($rsOrder['AreaId']) ? (empty($rsOrder['CityId']) ? $rsOrder['ProvinceId'] : $rsOrder['CityId']) : $rsOrder['AreaId'] ],$rsOrder['Account_ID'], $UsersID);
    if (!isset($res['status']) || $res['status'] != 1) {
        $Data = [
            "status" => 0,
            "msg" => empty($res['msg']) ? '检查地区是否已被代理失败' : $res['msg']
        ];
        echo json_encode($Data, JSON_UNESCAPED_UNICODE);
        exit;
    }
	
	if(!$rsOrder){
		$Data = array(
			"status"=> 0,
			"msg"=>'订单不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsOrder['Order_Status']<>1){
		$Data = array(
			"status"=> 0,
			"msg"=>'该订单不是待付款状态'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
	
	if($method==1){//微支付
		$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
		if($rsPay["PaymentWxpayEnabled"]==0 || empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“微支付”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
		if($rsPay["PaymentWxpayType"]==1){
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/wxpay2/sendto_proxy.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/wxpay2/sendto_proxy.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
	}elseif($method==2){//支付宝		
		if($rsPay["Payment_AlipayEnabled"]==0 || empty($rsPay["Payment_AlipayPartner"]) || empty($rsPay["Payment_AlipayKey"]) || empty($rsPay["Payment_AlipayAccount"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“支付宝”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/alipay/sendto_proxy.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	}
}elseif($action == "deal_sha_order_pay"){

	if(empty($_POST['OrderID'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($_POST['password'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$OrderID = $_POST['OrderID'];
	$rsOrder = $DB->GetRs('sha_order','*','where Users_ID="'.$UsersID.'" and Order_ID='.$OrderID.' and User_ID='.$UserID);

	if(!$rsOrder){
		$Data = array(
			"status"=> 0,
			"msg"=>'订单不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsOrder['Order_Status']<>1){
		$Data = array(
			"status"=> 0,
			"msg"=>'该订单不是待付款状态'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}

        //订单股东级别
        $sha_level =$rsOrder['Applyfor_level'];
        if(empty($sha_level)){
		$Data = array(
			"status"=> 0,
			"msg"=>'未选择股东级别'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
        //股东级别名称
        $dis_config = dis_config($UsersID);
        $Sha_Rate = json_decode($dis_config['Sha_Rate'], true);
	$Sha_Name = !empty($Sha_Rate['sha'][$sha_level]['name'])?$Sha_Rate['sha'][$sha_level]['name']:'未知';

	$rsUser = $DB->GetRs('user','User_Money,User_PayPassword','where User_ID='.$rsOrder['User_ID']);

	if($rsUser['User_PayPassword'] != md5($_POST['password'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'支付密码错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsUser['User_Money'] < $rsOrder['Order_TotalPrice']){
		$Data = array(
			"status"=> 0,
			"msg"=>'当前余额不足'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	require_once(CMS_ROOT.'/include/library/pay_order.class.php');
	$pay_order = new pay_order($DB, $OrderID);
	$response = $pay_order->deal_sha_order();
	

	$effset = $DB->set('distribute_account', array('Enable_Agent' => 1,'sha_level'=>$sha_level), ' WHERE `Users_ID`="' .$rsOrder['Users_ID']. '" AND `User_ID` = '.$rsOrder['User_ID']);
	if($response['status'] && $effset){
		$Data = array(
			"status"=> 1,
			'msg'=>'支付成功，你已经成为'.$Sha_Name.'股东',

			'url'=>distribute_url()
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}else{
		$Data = array(
			"status"=> 0,
			"msg"=>'支付失败'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}


}elseif($action == 'get_sha_pay_method'){
	if(empty($_POST['OrderID'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($_POST['method'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$method = $_POST['method'];
	
	$OrderID = $_POST['OrderID'];
	$rsOrder = $DB->GetRs('sha_order','*','where Users_ID="'.$UsersID.'" and Order_ID='.$OrderID.' and User_ID='.$UserID);
	
	if(!$rsOrder){
		$Data = array(
			"status"=> 0,
			"msg"=>'订单不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsOrder['Order_Status']<>1){
		$Data = array(
			"status"=> 0,
			"msg"=>'该订单不是待付款状态'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
	
	if($method==1){//微支付
		$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
		if($rsPay["PaymentWxpayEnabled"]==0 || empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“微支付”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
		if($rsPay["PaymentWxpayType"]==1){
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/wxpay2/sendto_sha.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/wxpay2/sendto_sha.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
	}elseif($method==2){//支付宝		
		if($rsPay["Payment_AlipayEnabled"]==0 || empty($rsPay["Payment_AlipayPartner"]) || empty($rsPay["Payment_AlipayKey"]) || empty($rsPay["Payment_AlipayAccount"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“支付宝”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/alipay/sendto_sha.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	}
}elseif($action=='send_hongbao'){
	if(empty($_POST['RecordID'])){
		$response = array('status'=>0,'msg'=>'参数不全');
		echo json_encode($response,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$RecordID = $_POST['RecordID'];
	$rsRecord = $DB->GetRs('distribute_withdraw_record','*','where Record_ID='.$RecordID.' and User_ID='.$UserID);
	if(!$rsRecord){
		$response = array('status'=>0,'msg'=>'该提现记录不存在');
		echo json_encode($response,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsRecord['Record_SendType']!='wx_hongbao'){
		$response = array('status'=>0,'msg'=>'该提现记录不是使用的微信红包提现');
		echo json_encode($response,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsRecord['Record_Status']!=1){
		$response = array('status'=>0,'msg'=>'请勿非法操作');
		echo json_encode($response,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	require_once(CMS_ROOT.'/include/library/pay_order.class.php');
	$pay_order = new pay_order($DB, 0);
	$Data = $pay_order->withdraw($UsersID,$UserID,$RecordID,'wx_hongbao');
	if($Data['status']==1){
		$response = array('status'=>1,'msg'=>'领取成功，请查看微信红包');		
	}else{
		$response = array('status'=>0,'msg'=>$Data['msg']);
	}
	
	echo json_encode($response,JSON_UNESCAPED_UNICODE);
	exit;
}