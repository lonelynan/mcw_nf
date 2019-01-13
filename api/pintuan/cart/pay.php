<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/support/tool_helpers.php');

use Illuminate\Database\Capsule\Manager as DB;

$base_url = base_url();
$shop_url = $base_url.'api/'.$UsersID.'/pintuan/';
$share_flag=0;

$OrderID=I("OrderID", 0);
$Method=I("Method");
if(! $Method){
    echo '缺少必要的参数';
    exit;
}
$rsConfig = DB::table("shop_config")->where(["Users_ID" => $UsersID])->first();
$rsOrder = DB::table("user_order")->where(["Users_ID" => $UsersID, "User_ID" => $UserID, "Order_ID" => $OrderID])
    ->first();

$rsPay = DB::table("users_payconfig")->where(["Users_ID" => $UsersID])->first();
if(!$rsConfig || !$rsOrder){
    echo '此订单无效';
    exit;
}
$status = $rsOrder['Order_Status'];
if($rsOrder && $status!=1){
    echo '此订单不是“待付款”状态，不能付款';
    exit;
}
$PaymentMethod = array(
    "1"=>"微支付",
    "2"=>"支付宝",
    "5"=>"银联支付"
);

if($Method==1){//微支付
    $rsUsers = DB::table("users")->where(["Users_ID" => $UsersID])->first();
    $rsAppSecret = DB::table("component_users_auth")->where(["Users_ID" => $UsersID])->first();
    //判断是授权的 还是手动填写的
    if($rsAppSecret){
        if($rsPay["PaymentWxpayEnabled"]==0 || empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsUsers["Users_WechatAppId"])){
            echo '商家“微支付”支付方式未启用或信息不全，暂不能支付！';
            exit;
        }
    }else{
        if($rsPay["PaymentWxpayEnabled"]==0 || empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
            echo '商家“微支付”支付方式未启用或信息不全，暂不能支付！';
            exit;
        }
    }

    if($rsPay["PaymentWxpayType"]==1){
        header("location:/pay/wxpay2/sendto.php?UsersID=".$UsersID."_".$OrderID);
    }else{
        header("location:/pay/wxpay/sendto.php?UsersID=".$UsersID."&OrderID=".$OrderID);
    }
}elseif($Method==2){//支付宝

    if($rsPay["Payment_AlipayEnabled"]==0 || empty($rsPay["Payment_AlipayPartner"]) || empty($rsPay["Payment_AlipayKey"]) || empty($rsPay["Payment_AlipayAccount"])){
        echo '商家“支付宝”支付方式未启用或信息不全，暂不能支付！';
        exit;
    }

    header("location:/pay/alipay/sendto_pintuan.php?UsersID=".$UsersID."_".$OrderID);
}elseif($Method==4){//易宝支付
    if($rsPay["PaymentYeepayEnabled"]==0 || empty($rsPay["PaymentYeepayAccount"]) || empty($rsPay["PaymentYeepayPrivateKey"]) || empty($rsPay["PaymentYeepayPublicKey"]) || empty($rsPay["PaymentYeepayYeepayPublicKey"])){
        echo '商家“支付宝”支付方式未启用或信息不全，暂不能支付！';
        exit;
    }
    header("location:/pay/yeepay/sendto.php?UsersID=".$UsersID."_".$OrderID);
}
elseif($Method==5){//银联支付
    if($rsPay["Payment_UnionpayEnabled"]==0 || empty($rsPay["Payment_UnionpayAccount"]) || empty($rsPay["PaymentUnionpayPfx"]) || empty($rsPay["PaymentUnionpayPfxpwd"])){
        echo '商家“银联支付”支付方式未启用或信息不全，暂不能支付！';
        exit;
    }
    header("location:/pay/Unionpay/sendto.php?UsersID=".$UsersID."_".$OrderID);
}

$owner = get_owner($rsConfig,$UsersID);

if($owner['id'] != '0'){
    $rsConfig["ShopName"] = $owner['shop_name'];
    $rsConfig["ShopLogo"] = $owner['shop_logo'];
};

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $rsConfig["ShopName"] ?>付款</title>
</head>
<body>
页面正在跳转至<?php echo $PaymentMethod[$Method];?>支付页面
</body>
</html>