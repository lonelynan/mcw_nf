<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"]."/pay/alipay/lib/alipay_notify.class.php");
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_pintuan.php');
require_once($_SERVER["DOCUMENT_ROOT"]."/pay/alipay/alipay.config.php");

$batch_no = $_POST['batch_no'];
$success_num = $_POST['success_num'];
$result_details = $_POST['result_details'];
if($batch_no){
    $rsRefund = $DB->GetRs("alipay_refund", "*", "WHERE Batch_No='{$batch_no}' AND Status = 0");
    if(!empty($rsRefund)){
        $UsersID = $rsRefund['Users_ID'];
        $rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='" . $UsersID . "'");
        if(empty($rsPay)) return false;
        $alipay_config = [];
        $alipay_config['partner']		= trim($rsPay["Payment_AlipayPartner"]);
        $alipay_config['seller_user_id']=$alipay_config['partner'];
        $alipay_config['key']			= trim($rsPay["Payment_AlipayKey"]);
        $alipay_config['notify_url']= "http://".$_SERVER['HTTP_HOST']."/pay/alipay/refund_pintuan_notify_url.php";
        $alipay_config['sign_type']    = 'MD5';
        $alipay_config['refund_date']= date("Y-m-d H:i:s",$rsRefund['Addtime']);
        $alipay_config['service']='refund_fastpay_by_platform_pwd';
        $alipay_config['input_charset']= 'utf-8';
        $alipay_config['cacert']    = getcwd().'\\cacert.pem';
        $alipay_config['transport']    = 'http';
        
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result){
            $strOrderList = trim($rsRefund['OrderList'], ',');
            $DB->Set("pintuan_order", "order_status='6'", "where order_id IN (".$strOrderList.")");
            $DB->Set("user_order", "Order_Status='6'", "where Order_ID IN (".$strOrderList.")");
            $DB->Set("alipay_refund", [ 'Status' => 1 ], "WHERE Batch_No='{$batch_no}' AND Status = 0");
        }
    }
}