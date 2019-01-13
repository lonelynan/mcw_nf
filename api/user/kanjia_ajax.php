<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/Framework/Conn.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/include/helper/tools.php');

$UsersID = $request->input('UsersID');
$UserID = S($UsersID . 'User_ID');
if(empty($UserID)){
	layerOpen("Session已过期，请重新登录", "/api/{$UsersID}/user/login/");
}
$action = $request->input('action');

if($action == 'payment'){
	$OrderID = $request->input('OrderID');
	$rsOrder = $capsule->table('user_order')->where(['Users_ID' => $UsersID, 'Order_ID' => $OrderID])->first();
	$rsUser = $capsule->table('user')->where(['Users_ID' => $UsersID, 'User_ID' => $UserID])->first();
	$payStatus = [
		"微支付"=>"1",
		"支付宝"=>"2",
		"线下支付"=>"3"
	];
	$CartList = json_decode($rsOrder['Order_CartList'] ,true);
	$flag = true;
	$BizID = 0;
	begin_trans();
	foreach($CartList as $bizid => $bizcart){
		$BizID = $bizid;
		foreach($bizcart as $pro_id => $value){
			$rsProducts = $capsule->table('shop_products')->where(['Products_ID' => $pro_id])->first();
			if($rsProducts['Products_Count']-1<0){
				$flag = -1;
				break;
			}
			$flag = $capsule->table('shop_products')->where(['Users_ID' => $UsersID,'Products_ID' => $pro_id])->decrement('Products_Count', 1);
		}
	}
	if($flag==-1){
		die(json_encode([
			"status"=>0,
			"msg"=>'库存为零无法支付'
		], JSON_UNESCAPED_UNICODE));
	}
	if(!$flag){
		back_trans();
		die(json_encode([
			"status"=>0,
			"msg"=>'支付失败'
		], JSON_UNESCAPED_UNICODE));
	}
	$PaymentMethod = $request->input('PaymentMethod', '');
	if($PaymentMethod == "线下支付" || $rsOrder["Order_TotalPrice"]<=0){
		$flag = $capsule->table('user_order')->where(['Users_ID' => $UsersID, 'Order_ID' => $OrderID])->update([
				"Order_PaymentMethod" => $PaymentMethod,
				"Order_PaymentInfo" => $request->input('PaymentInfo', ''),
				"Order_DefautlPaymentMethod" => $request->input('DefautlPaymentMethod', ''),
				"Order_Status" => 1
			]);
		if(!$flag){
			back_trans();
			die(json_encode([
				"status"=>0,
				"msg"=>'支付失败'
			], JSON_UNESCAPED_UNICODE));
		}
		$url=empty($request->input('DefautlPaymentMethod', ''))?"/api/".$UsersID."/user/kanjia_order/status/1/":"/api/".$UsersID."/user/kanjia_order/detail/".$OrderID ."/";
		commit_trans();
		$Data=[
			"status"=>1,
			"url"=>$url
		];	
	}elseif($PaymentMethod == "余额支付" && $rsUser["User_Money"]>=$rsOrder["Order_TotalPrice"]){//余额支付
		//增加资金流水
		$PayPassword = $request->input('PayPassword');
		if(empty($PayPassword)){
			$Data = [
				"status"=>0,
				"msg"=>'请输入支付密码'
			];
		}elseif(md5($_POST["PayPassword"])!=$rsUser["User_PayPassword"]){
			$Data = [
				"status"=>0,
				"msg"=>'支付密码输入错误'
			];
		}else{
			$Data = [
				'Users_ID'=>$UsersID,
				'User_ID'=>$UserID,				
				'Type'=>0,
				'Amount'=>$rsOrder["Order_TotalPrice"],
				'Total'=>$rsUser['User_Money']-$rsOrder["Order_TotalPrice"],
				'Note'=>"商城购买支出 -".$rsOrder["Order_TotalPrice"]." (订单号:".$OrderID.")",
				'CreateTime'=>time()		
			];
			$flag = $capsule->table('user_money_record')->insert($Data);
			if(!$flag){
				back_trans();
				die(json_encode([
					"status"=>0,
					"msg"=>'写入资金明细失败'
				], JSON_UNESCAPED_UNICODE));
			}
			//更新用户余额
			$Data = [				
				'User_Money'=>$rsUser['User_Money']-$rsOrder["Order_TotalPrice"]				
			];
			$flag = $capsule->table('user')->where(['Users_ID' => $UsersID, 'User_ID' => $UserID])->update($Data);
			if($flag===false){
				back_trans();
				die(json_encode([
					"status"=>0,
					"msg"=>'更改用户余额失败'
				], JSON_UNESCAPED_UNICODE));
			}
			$flag = $capsule->table('user_order')->where(['Users_ID' => $UsersID, 'Order_ID' => $OrderID, 'User_ID' => $UserID])->update([
				"Order_PaymentMethod" => $PaymentMethod,
				"Order_PaymentInfo" => $request->input('PaymentInfo', ''),
				"Order_DefautlPaymentMethod" => $request->input('DefautlPaymentMethod', ''),
				"Order_Status" => 2
			]);
			if($flag===false){
				back_trans();
				die(json_encode([
					"status"=>0,
					"msg"=>'更改支付方式失败'
				], JSON_UNESCAPED_UNICODE));
			}
			$url="/api/".$UsersID."/user/kanjia_order/detail/".$OrderID."/";
			$Data = [
				"status"=>1,
				"url"=>$url
			];
			commit_trans();
		}			
	}else{//在线支付
		$flag = $capsule->table('user_order')->where(['Users_ID' => $UsersID, 'Order_ID' => $OrderID, 'User_ID' => $UserID])->update([
				"Order_PaymentMethod" => $PaymentMethod,
				"Order_PaymentInfo" => $request->input('PaymentInfo', ''),
				"Order_DefautlPaymentMethod" => $request->input('DefautlPaymentMethod', ''),
			]);
		if($flag===false){
			back_trans();
			die(json_encode([
				"status"=>0,
				"msg"=>'更改支付方式失败'
			], JSON_UNESCAPED_UNICODE));
		}
		$url="/api/".$UsersID."/user/pay_kanjia/".$OrderID."/".$payStatus[$PaymentMethod]."/";
		$Data = [
			"status"=>1,
			"url"=>$url
		];
		
		commit_trans();		
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit();
}elseif($action == 'commit'){

	$OrderID = $_POST["OrderID"];
	$rsConfig = $DB->GetRs("shop_config","Commit_Check","where Users_ID='".$UsersID."'");
	$rsOrder=$DB->GetRs("user_order","*","where Order_ID=".$OrderID." and User_ID=".$_SESSION[$UsersID."User_ID"]." and Users_ID='".$UsersID."' and (Order_Status=3 or Order_Status=4)");
	if(!$rsOrder){
		$Data=array(
			"status"=>2,
			"msg"=>"无此订单"
		);

	}else{
		if($rsOrder["Is_Commit"]==1){
			$Data=array(
				"status"=>3,
				"msg"=>"此订单已评论过，不可重复评论"
			);
		}else{
			$Data1=array(
				"Is_Commit"=>1
			);
			
			$DB->Set("user_order",$Data1,"where Order_ID=".$OrderID);
			$CartList=json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]),true);
			foreach($CartList as $key=>$v){
				$Data=array(
					"MID"=>$rsOrder["Order_Type"],
					"Order_ID"=>$OrderID,
					"Product_ID"=>$key,
					"Score"=>$_POST["Score"],
					"Note"=>$_POST["Note"],
					"Status"=>$rsConfig["Commit_Check"]==1 ? 1 : 0,
					"Users_ID"=>$UsersID,
					"User_ID"=>$_SESSION[$UsersID."User_ID"],
					"CreateTime"=>time()
				);
				$DB->Add("user_order_commit",$Data);
			}
			
			$Data=array(
				"status"=>1
			);
		}
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit();
}