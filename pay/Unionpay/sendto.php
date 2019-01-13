<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>银联支付交易接口接口</title>
</head>
<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/pay_order.class.php');

if(isset($_GET["UsersID"])){
	if(!strpos($_GET["UsersID"],'_')){
		echo '缺少必要的参数';
		exit;
	}else{
		$arr = explode("_",$_GET["UsersID"]);
		$UsersID = $arr[0];
		$OrderID = $arr[1];
	}
}else{
	echo '缺少必要的参数';
	exit;
}
$rsPay=$DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");


$pay_order = new pay_order($DB,$OrderID);
$payinfo = $pay_order->get_pay_info();
$fistgo = 'first';
require_once("UnionpayConfig.php");
require_once("acp_service.php");
$params = array(
		
		//以下信息非特殊情况不需要改动
		'version' => '5.0.0',                 //版本号
		'encoding' => 'utf-8',				  //编码方式
		'txnType' => '01',				      //交易类型
		'txnSubType' => '01',				  //交易子类
		'bizType' => '000201',				  //业务类型
		'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  //前台通知地址
		'backUrl' => SDK_BACK_NOTIFY_URL,	  //后台通知地址
		'signMethod' => '01',	              //签名方法
		'channelType' => '08',	              //渠道类型，07-PC，08-手机
		'accessType' => '0',		          //接入类型
		'currencyCode' => '156',	          //交易币种，境内商户固定156
		
		//TODO 以下信息需要填写
		'merId' => $rsPay['Payment_UnionpayAccount'],		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
		'orderId' => $payinfo["out_trade_no"],	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
		'txnTime' => date('YmdHis'),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
		'txnAmt' => $payinfo["total_fee"]*100,	//交易金额，单位分，此处默认取demo演示页面传递的参数
// 		'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据

		//TODO 其他特殊用法请查看 special_use_purchase.php
	);

//建立请求
AcpService::sign ( $params );
$uri = SDK_FRONT_TRANS_URL;
$html_form = AcpService::createAutoFormHtml( $params, $uri );
$fistgo = '';
echo $html_form;
?>
</body>
</html>