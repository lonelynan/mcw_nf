<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once(CMS_ROOT . '/include/helper/tools.php');
require_once(CMS_ROOT . '/include/compser_library/Salesman_Commission.php');
use Illuminate\Database\Capsule\Manager as DB;

$bizID = I('bizid');
if (!$bizID) {
    exit('参数错误');
}

$bizInfo = DB::table('biz')->where(['Biz_ID' => $bizID])->first();
if (empty($bizInfo)) {
    exit('商户信息不存在!');
}
if ($bizInfo['expiredate'] < time()) {
    exit('商家已过期!');
}

$UsersID = $bizInfo['Users_ID'];
//商城配置信息
$rsConfig = shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);
if(! empty($dis_config)){
    $rsConfig = array_merge($rsConfig, $dis_config);
}

$rsUsers = DB::table('users')->where(['Users_ID' => $UsersID])->first();
$rsPay = DB::table('users_payconfig')->where(['Users_ID' => $UsersID])->first();

require_once($_SERVER["DOCUMENT_ROOT"] . '/include/library/wechatuser.php');
$User_ID = S($UsersID . 'User_ID');
if(!$User_ID){
    die("公众号配置有错误，无法获取openid");
}

define("APPID" , trim($rsUsers["Users_WechatAppId"]));
define("APPSECRET", trim($rsUsers["Users_WechatAppSecret"]));
define("MCHID",trim($rsPay["PaymentWxpayPartnerId"]));
define("KEY",trim($rsPay["PaymentWxpayPartnerKey"]));
define("JS_API_CALL_URL","http://".$_SERVER['HTTP_HOST']."/pay/wxpay2/bill_wxpay.php?bizid=" . $bizID);
define("NOTIFY_URL","http://".$_SERVER['HTTP_HOST']."/pay/wxpay2/offline_notify_url.php");
define("CURL_TIMEOUT",30);

if (!$rsUsers || empty($rsUsers['Users_WechatAppId']) || empty($rsUsers['Users_WechatAppSecret']) || empty($rsPay['PaymentWxpayPartnerId']) || empty($rsPay['PaymentWxpayPartnerKey'])) {
    exit('参数不足');
}
$offer = [];
if ($bizInfo['bill_reduce_type'] == 1 && !empty($bizInfo['bill_man_json'])) {
    $offer = json_decode($bizInfo['bill_man_json'], true);
}

$openid = '';
require_once($_SERVER["DOCUMENT_ROOT"] . "/pay/wxpay2/WxPayPubHelper.php");
if(!S($UsersID . 'OpenID')){
	$jsApi = new JsApi_pub();
	$code = I('code', 0);
	if (! $code){
		$url = $jsApi->createOauthUrlForCode(JS_API_CALL_URL);
		header("Location: " . $url);exit;
	}else{
		$jsApi->setCode($code);
	}
	$openid = !I('openid') ? $jsApi->getOpenid() : I('openid');
	S($UsersID . 'OpenID', $openid);
}else{
	$openid = S($UsersID .'OpenID');
}
if(empty($openid)){
	layerMsg("请重新扫描", $_SERVER['REQUEST_URI']);
}

$orderAmount = I('orderAmount');
if ($orderAmount) {
    $orderAmount = intval(strval($orderAmount * 100));
    if ($bizInfo['bill_reduce_type'] == 1) {
        if (!empty($bizInfo['bill_man_json'])) {
            //取出配置的满减
            $manArr = json_decode($bizInfo['bill_man_json'], true);
            //舍弃支付的小数点
            $newAmount = (int)$_GET['orderAmount'];
            //合并到满减数组里
            $newArr = array_merge(array_keys($manArr), [$newAmount]);
            //对数组进行排序,方便取出优惠金额
            sort($newArr);
            //对数组进行旋转,方便排列顺序
            $nnArr = array_flip($newArr);
            //拿到优惠金额
            if ($nnArr[$newAmount] != 0) {
                $reduceAmount = $manArr[$newArr[$nnArr[$newAmount]-1]];

                $orderAmount = $orderAmount-($reduceAmount * 100);
            }
        }
    } else if ($bizInfo['bill_reduce_type'] == 2) {
        if (!empty($bizInfo['bill_rebate']) && $bizInfo['bill_rebate'] > 0) {
            $orderAmount = ceil($orderAmount * $bizInfo['bill_rebate']);
        }
    }
    //提交订单
    $offline_order_info = DB::table("user_order")->where(["Users_ID" => $UsersID, "Biz_ID" => $bizID, "User_ID" => $User_ID, "Order_Type" => 'offline_qrode'])->orderBy("Order_CreateTime", "desc")->first();
    $rsProducts = array(
        'Products_ID'     => isset($offline_order_info['Order_ID']) && $offline_order_info['Order_ID']? ($bizID + $User_ID) : ($offline_order_info['Order_ID']+1),
        'Products_PriceX' => $orderAmount/100,
        'Biz_ID'          => $bizID,
        'ImgPath'         => isset($bizInfo['Biz_Logo']) && $bizInfo['Biz_Logo'] ? $bizInfo['Biz_Logo']:'/static/member/images/shop/nopic.jpg',
    );
    $rsUser = DB::table('user')->where(['Users_ID' => $UsersID, 'User_ID' => $User_ID])->first();
	$OwnerID = 0;
	if($rsUser['Is_Distribute']){
		$OwnerID = $rsUser['User_ID'];
	}else{
		$OwnerID = $rsUser['Owner_Id'];
	}
	$Shop_oofCommision_Reward_Json = json_decode($rsConfig["Shop_oofCommision_Reward_Json"], true);
	if (empty($Shop_oofCommision_Reward_Json)) {
		die(json_encode(['status' => 0, 'msg' => '佣金设置有误！请联系管理员！']));
	}
    
    $web_total = $rsProducts["Products_PriceX"] * ($bizInfo['Biz_Profit']/100);//利润
    $web_total = number_format ( $web_total, 2, ".", "" );//利润
    $orderCartList[$bizID][$rsProducts['Products_ID']][] = [
        "ProductsName" => $bizInfo['Biz_Name'].'线下扫码消费',
        "ImgPath" => $rsProducts ["ImgPath"],
        "ProductsPriceX" => $rsProducts ["Products_PriceX"],
        "Products_Supplyprice" => $rsProducts ["Products_PriceX"] * ((100-$bizInfo['Biz_Profit'])/100),
        "ProductsWebTotal" => $web_total,
        "ProductsPriceY" => $rsProducts ["Products_PriceX"],
        "platForm_Income_Reward" => $Shop_oofCommision_Reward_Json["platForm_Income_Reward"],
        "sha_Reward" => $Shop_oofCommision_Reward_Json["sha_Reward"],
        "ProductsWeight" => 0,
        "OwnerID" => $OwnerID,
        "IsShippingFree" => 1,
        "Qty" => 1,
        "spec_list" => '',
        "Property" => [],
    ];
    $OrderID = create_order($UsersID, $User_ID, $rsConfig, $orderCartList, $bizID, $OwnerID, $web_total);
    if(! $OrderID) {
        die(json_encode(['status' => 0, 'msg' => '订单提交失败！']));
    }
	
    $unifiedOrder = new UnifiedOrder_pub();
    $unifiedOrder->setParameter("openid",$openid);
    $unifiedOrder->setParameter("body",$bizInfo['Biz_Name'] . "线下买单");
    $unifiedOrder->setParameter("out_trade_no", date("YmdHis") . $OrderID);
    $unifiedOrder->setParameter("total_fee",  $orderAmount);
    $unifiedOrder->setParameter("attach", $bizID);
    $unifiedOrder->setParameter("notify_url",NOTIFY_URL);
    $unifiedOrder->setParameter("trade_type","JSAPI");
    $prepay_id = $unifiedOrder->getPrepayId();
	$jsApi = new JsApi_pub();
    $jsApi->setPrepayId($prepay_id);
    $jsApiParameters = $jsApi->getParameters();

    echo json_encode(['arg' => json_decode($jsApiParameters, true), 'openid' => $openid]);
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no" />
    <title>向商户付款</title>
</head>
<body>
<link href="/static/biz/css/pay_v.css?t=<?= time() ?>" type="text/css" rel="stylesheet">
<script src="/static/biz/js/index.css.js"></script>
<script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script>
<script>
    function jsApiCall(data) {
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest',
            data,
            function(res){
                WeixinJSBridge.log(res.err_msg);
                if(res.err_msg=='get_brand_wcpay_request:ok'){
                    WeixinJSBridge.call('closeWindow');
                }
                if (res.err_msg == 'get_brand_wcpay_request:cancel') {
                    $('.sub_pay_v').removeAttr('disabled').val('立即买单');
                }
            }
        );
    }

    function callpay(data) {
        if (typeof WeixinJSBridge == "undefined"){
            if( document.addEventListener ){
                document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
            }else if (document.attachEvent){
                document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
            }
        }else{
            jsApiCall(data);
        }
    }

    $(function(){
        $(".sub_pay_v").on('click', function() {
            var input = $("input[name=orderAmount]").val();
            if (input == '') {
                $("#errormsg").show().html('金额不能为空');
                return false;
            } else if (parseFloat(input) <= 0 || (Math.floor(parseFloat(input) * 10000 / 100) != parseFloat(input) * 10000 / 100)) {
                $("#errormsg").show().html('金额输入不正确');
                return false;
            } else {
                $("#errormsg").hide().html('');
            }
            $(this).attr({'disabled': 'disabled', 'value': '提交中...'});
            //提交
			/*
			var openid = $("input[name='openid']").val();
			var orderAmount = $("input[name='orderAmount']").val();
			var code = $("input[name='code']").val();
			var bizid = $("input[name='bizid']").val();
			location.href = "?openid=" + openid + '&orderAmount=' + orderAmount + '&code=' + code + '&bizid=' + bizid;
			return false;*/
            $.ajax({
                type: 'get',
                url: '?',
                data: $("#wxpay").serialize(),
                dataType: 'json',
                //async: false,
                success: function(json){
					if(json.status==1){
						$('input[name=openid]').val(json.openid);
						callpay(json.arg);
					}else{
						alert(json.msg);
					}
                }
            });
        });

        //显示实际支付价格
        $('input[name=orderAmount]').on('input propertychange', function(){
            $("#errormsg").hide().html('');
            var input = $(this).val() == '' ? 0 : parseFloat($(this).val()) * 10000;
            var type = <?= $bizInfo['bill_reduce_type'] ?>;
            var pay = input;

            //判断输入金额是否合规
            if (Math.floor(input / 100) != input / 100) {
                $("#errormsg").show().html('输入的金额错误');
                return false;
            }

            if (type == 1 && '<?= $bizInfo['bill_man_json'] ?>' != '') {    //满减
                //实际支付金额
                var flag = 1;
                $.each(<?= !empty($bizInfo['bill_man_json']) ? $bizInfo['bill_man_json'] : '{}' ?>, function(i, n){
                    if (input >= parseFloat(i) * 10000) {
                        pay = input - parseFloat(n) * 10000;
                        pay = pay/100;
                        flag = 0;
                    }
                });
                if(flag){
                    pay = pay/100;
                }
            } else if (type == 2 && parseFloat(<?= $bizInfo['bill_rebate'] ?>) > 0) {      //折扣
                pay = Math.ceil(input * parseFloat(<?= $bizInfo['bill_rebate'] ?>) / 100);
            } else {
                pay = pay / 100;
            }

            if (input) {
                $('#pay .pay_money').html((pay / 100) + '元');
            } else {
                $('#pay .pay_money').html('');
            }
        });
    });
</script>
<div class="title_pay_v">
    <?=$bizInfo['Biz_Name']?>
</div>
<form method="get" action="?" id="wxpay">
    <div class="form_pay_v">
        <span>消费总额:</span>
        <input type="number" value="<?= I('orderAmount','') ?>" name="orderAmount" step="0.01" placeholder="请询问店员后输入">
        <input type="hidden" name="bizid" value="<?=I('bizid') ?>" />
        <input type="hidden" name="code" value="<?= I('code') ?>" />
        <input type="hidden" name="openid" value="<?=$openid ?>" />
    </div>
    <div id="pay">
        <span class="pay_tit">实付金额:</span>
        <span class="pay_money"></span>
    </div>
    <div id="errormsg"></div>
    <div class="clear"></div>
    <input type="button" value="立即买单" class="sub_pay_v">

    <?php if ($bizInfo['bill_reduce_type'] == 1 && !empty($offer)) { ?>
        <div class="activity">
            <p class="tit">本店优惠</p>
            <div class="con">
                <?php foreach ($offer as $k => $v) { ?>
                    <p>订单满 <?= $k ?>元，优惠 <?= $v ?>元</p>
                <?php } ?>
            </div>
        </div>
    <?php } else if ($bizInfo['bill_reduce_type'] == 2 && $bizInfo['bill_rebate'] > 0) { ?>
        <div class="activity">
            <p class="tit">本店优惠</p>
            <div class="con">
                <p>全场 <?= $bizInfo['bill_rebate'] * 10 ?>折</p>
            </div>
        </div>
    <?php } ?>
</form>
</body>
</html>