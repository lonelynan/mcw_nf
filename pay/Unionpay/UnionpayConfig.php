<?php
require_once("log.class.php");
define('PFXFILE', $_SERVER["DOCUMENT_ROOT"].$rsPay['PaymentUnionpayPfx']);
define('PFXFILPWD', $rsPay['PaymentUnionpayPfxpwd']);
define('CERFILE', $_SERVER["DOCUMENT_ROOT"].$rsPay['PaymentUnionpayCer']);
define('PFXPATH', $_SERVER["DOCUMENT_ROOT"].dirname($rsPay['PaymentUnionpayPfx']).'/');	
 // ######(以下配置为PM环境：入网测试环境用，生产环境配置见文档说明)#######
// 签名证书路径
const SDK_SIGN_CERT_PATH = PFXFILE;

// 签名证书密码
const SDK_SIGN_CERT_PWD = PFXFILPWD;

// 密码加密证书（这条一般用不到的请随便配）
const SDK_ENCRYPT_CERT_PATH = CERFILE;

// 验签证书路径（请配到文件夹，不要配到具体文件）
const SDK_VERIFY_CERT_DIR = PFXPATH;


// 前台请求地址
const SDK_FRONT_TRANS_URL = 'https://gateway.95516.com/gateway/api/frontTransReq.do';

// 后台请求地址
const SDK_BACK_TRANS_URL = 'https://gateway.95516.com/gateway/api/backTransReq.do';

// 批量交易
const SDK_BATCH_TRANS_URL = 'https://gateway.95516.com/gateway/api/batchTrans.do';

//单笔查询请求地址
const SDK_SINGLE_QUERY_URL = 'https://gateway.95516.com/gateway/api/queryTrans.do';

//文件传输请求地址
const SDK_FILE_QUERY_URL = 'https://101.231.204.80:9080/';

//有卡交易地址
const SDK_Card_Request_Url = 'https://gateway.95516.com/gateway/api/cardTransReq.do';

//App交易地址
const SDK_App_Request_Url = 'https://gateway.95516.com/gateway/api/appTransReq.do';

// 前台通知地址 (商户自行配置通知地址)
define('FRONT_NOTIFY_URL', 'http://'.$_SERVER['HTTP_HOST'].'/pay/unionpay/FrontReceive.php');
const SDK_FRONT_NOTIFY_URL = FRONT_NOTIFY_URL;

// 后台通知地址 (商户自行配置通知地址，需配置外网能访问的地址)
define('BACK_NOTIFY_URL', 'http://'.$_SERVER['HTTP_HOST'].'/pay/unionpay/BackReceive.php');
const SDK_BACK_NOTIFY_URL = BACK_NOTIFY_URL;

//文件下载目录 
const SDK_FILE_DOWN_PATH = 'D:/file/';

//日志 目录 
const SDK_LOG_FILE_PATH = 'D:/logs/';

//日志级别，PhpLog::DEBUG关掉的话改PhpLog::OFF
const SDK_LOG_LEVEL = PhpLog::OFF;
?>