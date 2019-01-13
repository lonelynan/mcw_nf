<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/support/tool_helpers.php');

use Illuminate\Database\Capsule\Manager as DB;

$base_url = base_url();
$shop_url = $base_url.'api/'.$UsersID.'/pintuan/';

$share_flag = 0;
$signature = '';
$is_login=1;
require_once(CMS_ROOT . '/include/library/wechatuser.php');

$OrderID = I("OrderID");
$rsPay = DB::table("users_payconfig")->where(["Users_ID" => $UsersID])->first();

$rsOrder = DB::table("user_order")->where(["Users_ID" => $UsersID, "User_ID" => $UserID,"Order_ID" => $OrderID])
    ->first();
if(empty($rsOrder)){
    sendAlert("此订单不存在！","/api/{$UsersID}/pintuan/orderlist/0/");
}
if($rsOrder['Order_Status']>1){
    sendAlert("此订单已经支付！","/api/{$UsersID}/pintuan/orderlist/0/");
}
$total = $rsOrder['Order_TotalPrice'];
$ordersn = $rsOrder['Order_Code'];

$rsUser = DB::table("user")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->first();
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
    <link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script>
    <script type='text/javascript' src='/static/api/js/global.js'></script>
    <script type='text/javascript' src='/static/api/pintuan/js/pintuan1.js'></script>
    <script language="javascript">
        $(document).ready(shop_obj.page_init);
        $(document).ready(shop_obj.payment_init);
    </script>
</head>

<body>
<div class="top_back">
    <a href="javascript:history.go(-1);"><img src="/static/api/shop/skin/default/images/back_1.png"></a>
    <span>选择支付方式</span>
</div>
<div id="shop_page_contents">
    <div id="cover_layer"></div>
    <link href='/static/api/shop/skin/default/css/cart.css' rel='stylesheet' type='text/css' />
    <script language="javascript">$(document).ready(shop_obj.payment_init);</script>
    <div id="payment">
        <div class="i-ture">
            <div class="info"> 订 单 号：<?php echo $ordersn;?><br />
                订单总价：<span class="fc_red" id="Order_TotalPrice">￥<?php echo $total;?><br/>
				</span>
            </div>
        </div>
        <form id="payment_form" action="/api/<?php echo $UsersID ?>/pintuan/">
            <!-- 支付方式选择 begin  -->
            <div class="i-ture">
                <h1 class="t">选择支付方式</h1>
                <ul id="pay-btn-panel">
                    <?php if(!empty($rsPay["PaymentWxpayEnabled"])){ ?>
                        <li> <a href="javascript:void(0)" class="btn btn-default btn-pay direct_pay" id="wzf" data-value="微支付"><img  src="/static/api/pintuan/images/wechat_logo.jpg" width="16px" height="16px"/>&nbsp;微信支付</a> </li>
                    <?php }?>
                    <?php if(!empty($rsPay["Payment_AlipayEnabled"])){?>
                        <li> <a href="javascript:void(0)" class="btn btn-danger btn-pay direct_pay" id="zfb" data-value="支付宝"><img  src="/static/api/pintuan/images/alipay.png" width="16px" height="16px"/>&nbsp;支付宝支付</a> </li>
                        <?php if(!empty($rsPay["PaymentYeepayEnabled"])){?>
                            <li style="display:none;"> <a href="javascript:void(0)" class="btn btn-danger btn-pay direct_pay" id="ybzf" data-value="易宝支付"><img  src="/static/api/pintuan/images/yeepay.png" width="16px" height="16px"/>&nbsp;易宝支付</a> </li>
                        <?php }?>
                    <?php }
                    if(!empty($rsPay["Payment_UnionpayEnabled"])){ ?>
                        <li>
                            <a href="javascript:void(0)" class="btn btn-danger btn-pay direct_pay" id="uni" data-value="银联支付"><img  src="/static/api/pintuan/images/Unionpay.png" width="16px" height="16px"/>&nbsp;银联支付</a>
                        </li>
                    <?php }
                    if(!empty($rsPay["Payment_OfflineEnabled"])){?>
                        <li> <a href="/api/<?=$UsersID?>/pintuan/cart/complete_pay/huodao/<?=$OrderID?>/" class="btn btn-warning  btn-pay" id="out" data-value="线下支付"><img  src="/static/api/pintuan/images/huodao.png" width="16px" height="16px"/>&nbsp;线下支付</a> </li>
                    <?php }?>
                    <?php if(!empty($rsPay["Payment_RmainderEnabled"])):?>
                        <li> <a href="/api/<?=$UsersID?>/pintuan/cart/complete_pay/money/<?=$OrderID?>/" class="btn btn-warning  btn-pay" id="money" data-value="余额支付"><img  src="/static/api/pintuan/images/money.jpg"  width="16px" height="16px"/>&nbsp;余额支付</a> </li>
                    <?php endif;?>
                </ul>
            </div>
            <!-- 支付方式选择 end -->

            <input type="hidden" name="PaymentMethod" id="PaymentMethod_val" value="微支付"/>
            <input type="hidden" name="OrderID" value="<?php echo $OrderID;?>" />
            <input type="hidden" name="total_price" value="<?php echo $rsOrder["Order_TotalPrice"] ?>" />
            <input type="hidden" name="action" value="selectPayment" />
            <input type="hidden" name="UsersID" value="<?=$UsersID ?>"/>
            <input type="hidden" name="DefautlPaymentMethod" value="余额支付" />
        </form>
    </div>
</div>
</body>
</html>