<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once(CMS_ROOT . "/pay/alipay/lib/alipay_submit.class.php");
require_once(CMS_ROOT . "/pay/alipay/lib/alipay_submit_refund.class.php");
require_once(CMS_ROOT.'/include/models/UsersPayConfig.php');
require_once(CMS_ROOT.'/include/models/UserBackOrder.php');
define("ALI_NOTIFY_URL", "http://".$_SERVER['HTTP_HOST']."/pay/alipay/refund_notify_url.php");

function alipay_refund($BackID){

    $objUserBackOrder = UserBackOrder::where("Back_ID", $BackID)->first();
    if(! $objUserBackOrder){
        return [
            'status' =>0,
            'msg' => '退款单不存在'
        ];
    }
    $objUsersPayConfig = UsersPayConfig::where("Users_ID", $objUserBackOrder->Users_ID)->get(["Payment_AlipayPartner","Payment_AlipayAccount","Payment_AlipayKey","Payment_AlipayRSAPrivateKey","Payment_AlipayPublicKey"])->first();
    if(! $objUsersPayConfig){
        return [
            'status' =>0,
            'msg' => '支付宝配置不正确'
        ];
    }
    $rsPay = $objUsersPayConfig->toArray();
    $rsObjOrder = Order::where("Order_ID", $objUserBackOrder->Order_ID)->first();
    if(! $rsObjOrder){
        return [
            'status' =>0,
            'msg' => '订单不存在'
        ];
    }
    $rsOrder = $rsObjOrder->toArray();
    if(!$rsOrder['transaction_id']){
        return [
            'status' =>0,
            'msg' => '不正确的支付宝交易号'
        ];
    }
    mt_srand((double) microtime() * 1000000);
    $batchNo = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    $batchNo = date("YmdHis",time()).$batchNo;
    $objUserBackOrder->batch_no= $batchNo;
    $objUserBackOrder->save();

    $detail_data = $rsOrder['transaction_id'] ."^".$objUserBackOrder->Back_Amount . "^正常退款";
    $alipay_config = [];
    $refund_date = date("Y-m-d H:i:s",time());

    $alipay_config['partner']		= trim($rsPay["Payment_AlipayPartner"]);
    $alipay_config['seller_user_id'] = trim($rsPay["Payment_AlipayPartner"]);
    $alipay_config['key']			= trim($rsPay["Payment_AlipayKey"]);
    $alipay_config['notify_url']= ALI_NOTIFY_URL;
    $alipay_config['sign_type']    = 'MD5';
    $alipay_config['refund_date']= $refund_date;
    $alipay_config['service']='refund_fastpay_by_platform_pwd';
    $alipay_config['input_charset']= 'utf-8';
    $alipay_config['cacert']    = getcwd().'\\cacert.pem';
    $alipay_config['transport']    = 'http';

    $parameter = array(
        "service" => trim($alipay_config['service']),
        "partner" => trim($alipay_config['partner']),
        "notify_url"	=> trim($alipay_config['notify_url']),
        "seller_user_id"	=> trim($alipay_config['seller_user_id']),
        "refund_date"	=> trim($alipay_config['refund_date']),
        "batch_no"	=> $batchNo,
        "batch_num"	=> 1,
        "detail_data"	=> $detail_data,
        "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
    );
    $alipaySubmit = new AlipaySubmitRefund($alipay_config);
    $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确定");
    echo $html_text;
    exit;
}