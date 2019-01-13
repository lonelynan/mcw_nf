<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_GET['UsersID'])) {
	$this->error('缺少参数');
}else {
	$UsersID = $_GET['UsersID'];
}
require_once "lib/WxPay.Api.php";
require_once "WxPay.NativePay.php";
if(empty($_GET['OrderID'])) {
	$this->error('缺少参数');
}else {
	$OrderID = $_GET['OrderID'];
}
$orderinfo = $DB->GetRs("biz_pay","*","where id='".$OrderID."'");

$notify = new NativePay();
$input = new WxPayUnifiedOrder();
$input->SetBody('商家入驻在线付款，订单编号:'.$orderinfo['id']);
$input->SetOut_trade_no($orderinfo['addtime'].$orderinfo['id']);
$input->SetTotal_fee($orderinfo['total_money'] * 100);
$input->SetNotify_url("http://".$_SERVER['HTTP_HOST']."/biz/authen/pay/wxpay/notify.php");
$input->SetTrade_type("NATIVE");
$input->SetProduct_id($orderinfo['id']);
$result = $notify->GetPayUrl($input);
if ($result['return_msg'] == '签名错误') {
	$url2 = 110;
} else {
	$url2 = $result["code_url"];
}

echo $url2;
?>