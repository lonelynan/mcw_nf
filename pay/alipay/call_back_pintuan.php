<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/virtual.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/order.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/pay_order.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_pintuan.php');
$out_trade_no = $_GET['out_trade_no'];
$OrderID = 0;
if(strpos($out_trade_no,'PRE')>-1){
	$OrderID = $out_trade_no;
	$rsOrder = $DB->GetRs("user_pre_order","*","where pre_sn='".$OrderID."'");
	if(empty($rsOrder)){
		echo "订单不存在";
		exit;
	}
	$UsersID = $rsOrder["usersid"];
	$UserID = $rsOrder["userid"];
	$Status = $rsOrder["status"];
}else{
	$rsOrder=$DB->GetRs("user_order","Order_ID,Users_ID,User_ID,Order_Status","where Order_Code='".$out_trade_no."'");
	if(empty($rsOrder)){
		echo "订单不存在";
		exit;
	}
	$OrderID = $rsOrder['Order_ID'];
	$UsersID = $rsOrder["Users_ID"];
	$UserID = $rsOrder["User_ID"];
	$Status = $rsOrder["Order_Status"];
}
$pay_order = new pay_order($DB,$OrderID);
$rsPay=$DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$UserID);
require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");
?>
<!DOCTYPE HTML>
<html>
    <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php

$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();
if($verify_result) {//验证成功
	if($Status==1){
	    $data = payDone($UsersID, $OrderID, "支付宝支付", true);
		if($data["status"]==1){
			echo "<script type='text/javascript'>window.location.href='".$data["url"]."';</script>";	
			exit;		
		}else{
			echo $data["msg"];
			exit;
		}
	}else{
		$url = '/api/'.$UsersID.'/pintuan/orderlist/0/';
		echo "<script type='text/javascript'>window.location.href='".$url."';</script>";	
		exit;
	}
}else {
    echo "验证失败";
}
?>
        <title>支付宝即时到账交易接口</title>
	</head>
    <body>
    </body>
</html>