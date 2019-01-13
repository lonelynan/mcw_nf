<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once(CMS_ROOT.'/Framework/Ext/virtual.func.php');
require_once(CMS_ROOT.'/include/helper/order.php');
require_once(CMS_ROOT.'/include/helper/tools.php');
require_once(CMS_ROOT.'/Framework/Ext/sms.func.php');
require_once(CMS_ROOT.'/include/library/pay_order.class.php');
require_once(CMS_ROOT.'/include/helper/flow.php');
ini_set("display_errors","On"); 
$xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) && !empty(isset($GLOBALS['HTTP_RAW_POST_DATA']))?$GLOBALS['HTTP_RAW_POST_DATA']:file_get_contents("php://input");
if($xml){
	include_once("WxPayPubHelper.php");
	$notify = new Notify_pub();
	$notify->saveData($xml);
	$OrderID = $notify->data["out_trade_no"];
	if(strpos($OrderID,'PRE')>-1){
		$rsOrder = $DB->GetRs("user_pre_order","*","where pre_sn='".$OrderID."'");
		if(!$rsOrder){
			echo "订单不存在";
			exit;
		}
		$UsersID = $rsOrder["usersid"];
		$UserID = $rsOrder["userid"];
		$Status = $rsOrder["status"];
	}else{
		$OrderID = substr($OrderID,10);
		$rsOrder=$DB->GetRs("user_order","Users_ID,User_ID,Order_Status","where Order_ID='".$OrderID."'");
		if(!$rsOrder){
			echo "订单不存在";
			exit;
		}
		$UsersID = $rsOrder["Users_ID"];
		$UserID = $rsOrder["User_ID"];
		$Status = $rsOrder["Order_Status"];
	}
	
	$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
	$rsPay=$DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
		define("APPID" , trim($rsUsers["Users_WechatAppId"]));
    define("APPSECRET", trim($rsUsers["Users_WechatAppSecret"]));
    define("MCHID",trim($rsPay["PaymentWxpayPartnerId"]));
    define("KEY",trim($rsPay["PaymentWxpayPartnerKey"]));
    define("JS_API_CALL_URL","http://".$_SERVER['HTTP_HOST']."/pay/wxpay2/sendto_cloud.php?UsersID=".$UsersID."_".$OrderID);
    define("NOTIFY_URL","http://".$_SERVER['HTTP_HOST']."/pay/wxpay2/notify_cloud.php");
    define("CURL_TIMEOUT",30);
    define("SSLCERT_PATH",$_SERVER["DOCUMENT_ROOT"].$rsPay["PaymentWxpayCert"]);
    define("SSLKEY_PATH",$_SERVER["DOCUMENT_ROOT"].$rsPay["PaymentWxpayKey"]);
	
	if($notify->checkSign() == FALSE){
		$notify->setReturnParameter("return_code","FAIL");//返回状态码
		$notify->setReturnParameter("return_msg","签名失败");//返回信息
	}else{
		$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
	}
	$returnXml = $notify->returnXml();
	echo $returnXml;
	if($notify->checkSign() == TRUE){
		if ($notify->data["return_code"] == "FAIL") {
			echo "【通信出错】";
		}elseif($notify->data["result_code"] == "FAIL"){
			echo "【业务出错】";
		}else{
			  $pay_order = new pay_order($DB,$OrderID);
			  $rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$UserID);
			  $data = $pay_order->make_pay();
				if($data["status"]==1){
					echo "SUCCESS";
					exit;
				}else{
					echo $data["msg"];
					exit;
				}
		}
	}
}else{
	$OrderID = isset($_GET["OrderID"]) ? $_GET["OrderID"] : 0;
	if(strpos($OrderID,'PRE')>-1){
		$rsOrder = $DB->GetRs("user_pre_order","status,usersid","where pre_sn='".$OrderID."'");
		if(!$rsOrder){
			echo "订单不存在";
			exit;
		}
		$UsersID = $rsOrder["usersid"];
		$Status = $rsOrder["status"];
	}else{
		$rsOrder=$DB->GetRs("user_order","Users_ID,Order_Status","where Order_ID='".$OrderID."'");
		if(!$rsOrder){
			echo "订单不存在";
			exit;
		}
		$UsersID = $rsOrder["Users_ID"];
		$Status = $rsOrder["Order_Status"];
	}
  $url = '/api/'.$UsersID.'/cloud/member/';
  echo "<script type='text/javascript'>window.location.href='".$url."';</script>";	
  exit;
}
?>