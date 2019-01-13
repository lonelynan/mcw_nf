<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once(CMS_ROOT . '/include/compser_library/Salesman_Commission.php');
require_once(CMS_ROOT . '/include/helper/tools.php');
require_once(CMS_ROOT . '/include/helper/flow.php');
require_once(CMS_ROOT . '/include/library/user_order.class.php');
require_once(CMS_ROOT . '/include/helper/backup.class.php');
require_once(CMS_ROOT . '/include/helper/balance.class.php');
require_once(CMS_ROOT . '/include/models/Distribute_Sales_Record.php');
require_once(CMS_ROOT . '/include/models/UserPayConfig.php');
require_once(CMS_ROOT . '/Framework/Ext/virtual.func.php');
require_once(CMS_ROOT . '/include/support/distribute_helpers.php');

use Illuminate\Database\Capsule\Manager as DB;

$receiveData = isset($GLOBALS['HTTP_RAW_POST_DATA']) && ! empty(isset($GLOBALS['HTTP_RAW_POST_DATA'])) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
if($receiveData){
    include_once ("WxPayPubHelper.php");
    $notify = new Notify_pub();
    $notify->saveData($receiveData);
    $response = $notify->data;
    file_put_contents(CMS_ROOT . "/data/offline.log",$receiveData . '\r\n', FILE_APPEND);
    $openid = $response['openid'];
    $out_trade_no = $response['out_trade_no'];
    $OrderID = substr($out_trade_no, 14);
    //判断是否生成过订单
    $rsOrder = DB::table('user_order')->where(['Order_ID' => $OrderID])->first();
    if(empty($rsOrder)){
        $notify->setReturnParameter("return_code","FAIL");//返回状态码
        $notify->setReturnParameter("return_msg","订单不存在");//返回信息
        $returnXml = $notify->returnXml();
        echo $returnXml;
        exit;
    }
    if(in_array($rsOrder['Order_Status'], [2,4])){
        $notify->setReturnParameter("return_code","SUCCESS");//返回状态码
        $notify->setReturnParameter("return_msg","OK");//返回信息
        $returnXml = $notify->returnXml();
        echo $returnXml;
        exit;
    }
    $UsersID = $rsOrder['Users_ID'];

    //获取商城支付配置信息并验证签名
    $rsUsers = Users::where("Users_ID", $UsersID)->first(["Users_WechatAppId", "Users_WechatAppSecret"])->toArray();
    $rsPay = UserPayConfig::where("Users_ID", $UsersID)->first(["PaymentWxpayPartnerId", "PaymentWxpayPartnerKey","PaymentWxpayCert","PaymentWxpayKey"])->toArray();
    define("APPID" , trim($rsUsers["Users_WechatAppId"]));
    define("APPSECRET", trim($rsUsers["Users_WechatAppSecret"]));
    define("MCHID",trim($rsPay["PaymentWxpayPartnerId"]));
    define("KEY",trim($rsPay["PaymentWxpayPartnerKey"]));
    define("CURL_TIMEOUT",30);
    define("SSLCERT_PATH",CMS_ROOT . $rsPay["PaymentWxpayCert"]);
    define("SSLKEY_PATH",CMS_ROOT . $rsPay["PaymentWxpayKey"]);

    if($notify->checkSign() == false){
        $notify->setReturnParameter("return_code","FAIL");//返回状态码
        $notify->setReturnParameter("return_msg","签名失败");//返回信息
        $returnXml = $notify->returnXml();
        echo $returnXml;
        exit;
    }else{
        $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        $returnXml = $notify->returnXml();
        echo $returnXml;
    }

    $pay_order = new user_order($DB, $OrderID);
    $pay_order->transaction_id = $response['transaction_id'];
    $data = $pay_order->make_pay();
    if($data["status"]==1){
        //执行确认收货
        Order::observe(new OrderObserver());
        $order = Order::find($OrderID);
        $order->confirmReceive();
        exit;
    }else{
        back_trans();
        echo $data["msg"];
        exit;
    }
}else{
    echo "非法请求";exit;
}
