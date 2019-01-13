<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
/**
* 	配置账号信息初始化
*/
	
	$rsPayConfig =$DB->GetRs("users_payconfig","PaymentWxpayEnabled,PaymentWxpayPartnerId,PaymentWxpayPartnerKey,PaymentWxpayCert,PaymentWxpayKey","where Users_ID='".$UsersID."'");
	
	$rsUsers=$DB->GetRs("users","Users_WechatAppId,Users_WechatAppSecret","where Users_ID='".$UsersID."'");
	
	define ( 'APPIDINIT', $rsUsers['Users_WechatAppId'] );
	define ( 'MCHIDINIT', $rsPayConfig['PaymentWxpayPartnerId'] );
	define ( 'KEYINIT', $rsPayConfig['PaymentWxpayPartnerKey'] );
	define ( 'APPSECRETINIT', $rsUsers['Users_WechatAppSecret'] );
	define ( 'SSLCERT_PATHINIT', $rsPayConfig['PaymentWxpayCert'] );
	define ( 'SSLKEY_PATHINIT', $rsPayConfig['PaymentWxpayKey'] );
	define ( 'CURL_PROXY_HOSTINIT', "0.0.0.0" );
	define ( 'CURL_PROXY_PORTINIT', 0 );
	define ( 'REPORT_LEVENLINIT', 1 );
	?>