<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/helper/url.php');
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/helper/lib_pintuan.php');
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/helper/distribute.php');

if (isset($_GET["UsersID"])) {
    $UsersID = $_GET["UsersID"];
} else {
    echo '缺少必要的参数';
    exit();
}

$base_url = base_url();
$shop_url = $base_url . 'api/' . $UsersID . '/zhongchou/';

$rsConfig = $DB->GetRs("shop_config", "*", "where Users_ID='" . $UsersID . "'");

$share_flag = 0;
$signature = '';

$is_login = 1;
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/library/wechatuser.php');

$OrderID = $_GET['OrderID'];
$rsPay = $DB->GetRs("users_payconfig", "*", "where Users_ID='" . $UsersID . "'");

if (strpos($OrderID, "PRE") !== false) {
    $rsOrder = $DB->GetRs("user_pre_order", "*", "where usersid='" . $UsersID . "' and userid='" . $_SESSION[$UsersID . "User_ID"] . "' and pre_sn='" . $OrderID . "'");
    $total = $rsOrder['total'];
    $ordersn = $rsOrder['Order_Code'];
} else {
    $rsOrder = $DB->GetRs("user_order", "*", "WHERE Users_ID='" . $UsersID . "' AND User_ID='" . $_SESSION[$UsersID . "User_ID"] . "' AND Order_ID='" . $OrderID . "'");
    if ($rsOrder['Order_Status'] > 1) {
        sendAlert("此订单已经支付！", "/api/{$UsersID}/pintuan/orderlist/0/");
    }
    $total = $rsOrder['Order_TotalPrice'];
    $ordersn = date("Ymd",$rsOrder["Order_CreateTime"]).$rsOrder["Order_ID"];
}

$rsUser = $DB->GetRs("user", "*", "where Users_ID='" . $UsersID . "' and User_ID=" . $_SESSION[$UsersID . "User_ID"]);

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport"
	content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $rsConfig["ShopName"] ?>付款</title>
<link href="/static/api/pintuan/css/css.css" rel="stylesheet"
	type="text/css">
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/style.css'
	rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/pintuan/js/pintuan.js'></script>
<script language="javascript">
$(document).ready(shop_obj.page_init);
$(document).ready(shop_obj.select_payment_init);
</script>
</head>

<body>
	<div id="shop_page_contents">
		<div id="cover_layer"></div>
		<link
			href='/static/api/shop/skin/default/css/cart.css?t=<?php echo time();?>'
			rel='stylesheet' type='text/css' />
		<script language="javascript">$(document).ready(shop_obj.payment_init);</script>
		<div id="payment">
			<div class="dingdan">
				<span class="fanhui l"><a href="javascript:history.go(-1);"><img
						src="/static/api/pintuan/images/fanhui.png" width="17px"
						height="17px"></a></span> <span class="querendd l">选择支付方式</span>
				<div class="clear"></div>
			</div>
			<div class="i-ture">
				<div class="info"> 订 单 号：<?php echo $ordersn;?><br /> 订单总价：<span
						class="fc_red" id="Order_TotalPrice">￥<?php echo $total;?><br />
					</span>
				</div>
			</div>
			<form id="payment_form"
				action="/api/<?php echo $UsersID ?>/zhongchou/cart/">
				<!-- 支付方式选择 begin  -->
				<div class="i-ture">
					<h1 class="t">选择支付方式</h1>
					<ul id="pay-btn-panel">
					<?php if(!empty($rsPay["PaymentWxpayEnabled"])){ ?>
					<li><a href="javascript:void(0)"
							class="btn btn-default btn-pay direct_pay" id="wzf"
							data-value="微支付"><img
								src="/static/api/pintuan/images/wechat_logo.jpg" width="16px"
								height="16px" />&nbsp;微信支付</a></li>
					<?php }?>
					<?php if(!empty($rsPay["Payment_AlipayEnabled"])){?>
					<li><a href="javascript:void(0)"
							class="btn btn-danger btn-pay direct_pay" id="zfb"
							data-value="支付宝"><img src="/static/api/pintuan/images/alipay.png"
								width="16px" height="16px" />&nbsp;支付宝支付</a></li>
					<?php
                    }
                    ?>
  
				</ul>
				</div>
				<!-- 支付方式选择 end -->

				<input type="hidden" name="PaymentMethod" id="PaymentMethod_val"
					value="微支付" /> <input type="hidden" name="OrderID"
					value="<?php echo $OrderID;?>" /> <input type="hidden"
					name="total_price"
					value="<?php echo $rsOrder["Order_TotalPrice"] ?>" /> <input
					type="hidden" name="action" value="payment" /> <input type="hidden"
					name="Users_ID" value="<?=$UsersID ?>" /> <input type="hidden"
					name="DefautlPaymentMethod" value="微支付" />
			</form>
		</div>
	</div>
</body>
</html>