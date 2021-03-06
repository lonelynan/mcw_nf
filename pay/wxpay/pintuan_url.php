﻿<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/virtual.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/order.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/pay_order.class.php');
ini_set("display_errors","On"); 

if(isset($GLOBALS['HTTP_RAW_POST_DATA'])){
	
	include_once("WxPayPubHelper.php");
	$notify = new Notify_pub();
	$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
	$notify->saveData($xml);
	$OrderID = $notify->data["out_trade_no"];
	//$OrderID = substr($OrderID,10);	
	//$rsOrder=$DB->GetRs("user_order","Users_ID,User_ID,Order_Status","where Order_ID='".$OrderID."'");
	//$UsersID = $rsOrder["Users_ID"];
	//$UserID = $rsOrder["User_ID"];
	//$Status = $rsOrder["Order_Status"];
	
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
	include_once("WxPay.pub.config.php");
	
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
			
			$info = payDone($UsersID,$OrderID,"微支付");
			$url = $info['url'];
			echo '<!doctype html><html><head><title>'.$info['msg'].'</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no,minimal-ui">';
            echo '<script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script><script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script></head><body>';
        	echo '<script>layer.msg("'.$info['msg'].'",{icon:1,time:3000},function(){ window.location="'.$url.'";});</script>';
        	echo "</body></html>";
        	exit;
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
	
	$UsersID = $rsOrder["Users_ID"];
	$Status = $rsOrder["Order_Status"];
	$info = payDone($UsersID,$OrderID,"微支付");
	$url = $info['url'];
	echo '<!doctype html><html><head><title>'.$info['msg'].'</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no,minimal-ui">';
    echo '<script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script><script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script></head><body>';
	echo '<script>layer.msg("'.$info['msg'].'",{icon:1,time:3000},function(){ window.location="'.$url.'";});</script>';
	echo "</body></html>";
	exit;
}
?>