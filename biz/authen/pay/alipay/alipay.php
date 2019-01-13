<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/biz/global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Submit.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Corefunction.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Md5function.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Notify.php');

    if(empty($_GET['OrderID'])) {
        $this->error('缺少参数');
    }else {
        $OrderID = $_GET['OrderID'];
    }
 
    
    $UsersID = $rsBiz['Users_ID'];
    $orderinfo = $DB->GetRs("biz_pay","*","where Users_ID='".$UsersID."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and id='".$OrderID."'");
    $payinfo = array(
        'out_trade_no'=>$orderinfo['addtime'].$orderinfo['id'],
        'subject'=>'商家入驻在线付款，订单编号:'.$orderinfo['id'],
        'total_fee'=>$orderinfo['total_money']
        
    );
    
    $users_payconfig = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
    $alipay_config = array(
        'partner' => $users_payconfig['Payment_AlipayPartner'],   //这里是你在成功申请支付宝接口后获取到的PID；
            'key' => $users_payconfig['Payment_AlipayKey'],//这里是你在成功申请支付宝接口后获取到的Key
            'sign_type' => 'MD5',
            'input_charset' => 'utf-8',
            'transport' => 'http',
    );
  //  $rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
            /**************************请求参数**************************/
    $payment_type = '1'; //支付类型 //必填，不能修改
    $notify_url = "http://".$_SERVER['HTTP_HOST']."/biz/authen/pay/alipay/notify_url.php";//服务器异步通知页面路径
    $return_url = "http://".$_SERVER['HTTP_HOST']."/biz/authen/pay/alipay/return_url.php";//页面跳转同步通知页面路径
 
    $seller_email = $users_payconfig['Payment_AlipayAccount'];//卖家支付宝帐户必填
    $out_trade_no = $payinfo['out_trade_no'];//商户订单号 通过支付页面的表单进行传递，注意要唯一！
    $subject = $payinfo['subject'];  //订单名称 //必填 通过支付页面的表单进行传递
    $total_fee = strval(floatval($payinfo['total_fee']));   //付款金额  //必填 通过支付页面的表单进行传递
    $anti_phishing_key = '';//防钓鱼时间戳 //若要使用请调用类文件submit中的query_timestamp函数
    /************************************************************/

            //构造要请求的参数数组，无需改动
    $parameter = array(
                    'service' => 'create_direct_pay_by_user',
                    'partner' => trim($alipay_config['partner']),
                    'payment_type' => $payment_type,
                    'notify_url' => $notify_url,
                    'return_url' => $return_url,
                    'seller_email' => $seller_email,
                    'out_trade_no' => $out_trade_no,
                    'subject' => $subject,
                    'total_fee' => $total_fee,
                    'anti_phishing_key' => $anti_phishing_key,
                    '_input_charset' => 'utf-8'
            );
            //建立请求
    $alipaySubmit = new \vendor\alipay\pc\Submit($alipay_config);
    $html_text = $alipaySubmit->buildRequestForm($parameter, 'post', '确认');
    echo $html_text;

?>