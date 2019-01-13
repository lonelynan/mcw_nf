<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');

$base_url = base_url();
$shop_url = shop_url();
$UsersID = $request->input('UsersID');
if(empty($UsersID)){
	die('缺少必要的参数');
}
$Is_store = isset($_GET['Is_store']) ? $_GET['Is_store'] : '' ;
$UserID = !empty(S($UsersID."User_ID"))?S($UsersID."User_ID"):0;

$share_flag = 0;
$signature = '';
$is_login=1;
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/wechatuser.php');

$OrderID = $request->input('OrderID');
$rsConfig = shop_config($UsersID);
$rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
$appFlag = false;
$Products_IsPaysBalance = 1;
$Coupon_Cash = 0;
$Integral_Money = 0;
$Curgio_Cash = 0;
if(strpos($OrderID,"PRE") !== false){
	$rsOrd=$DB->GetRs("user_pre_order","*","where usersid='".$UsersID."' and userid='".$UserID."' and pre_sn='".$OrderID."'");
	if(empty($rsOrd)){
		layerOpen("订单不存在", 2, $shop_url . 'member/status/2/');
	}
	$total = $rsOrd['total'];
	$ordersn = $rsOrd['pre_sn'];
	$ids = explode(',', $rsOrd['orderids']);
	$orderlist = $capsule->table('user_order')->where(['Users_ID' => $UsersID, 'User_ID' => $UserID])->whereIn('Order_ID', $ids)->get(['Order_CartList', 'Order_Type','Coupon_Cash', 'Integral_Money', 'curagio_money']);
	if(!empty($orderlist)){
		foreach($orderlist as $k => $rsOrder){
			$Order_CartList = !empty($rsOrder["Order_CartList"]) ? json_decode($rsOrder["Order_CartList"],true): array();
			$Coupon_Cash += $rsOrder['Coupon_Cash'];
			$Curgio_Cash += $rsOrder['curagio_money'];
			$Integral_Money += $rsOrder['Integral_Money'];
			if ($rsOrder['Order_Type'] != 'offline_st') {
				foreach($Order_CartList as $k=>$v){
					foreach($v as $kk=>$vv){
						if (!isset($vv['Products_IsPaysBalance']) || $vv['Products_IsPaysBalance'] != 1) {
							$Products_IsPaysBalance = 0;
							break;
						}
					}
				}
			}
		}
	}
	$total_souce = $total;
}else{
	$rsOrder=$DB->GetRs("user_order","*","where Users_ID='".$UsersID."' and User_ID='".$UserID."' and Order_ID='".$OrderID."'");
	if(empty($rsOrder)){
		layerOpen("订单不存在", 2, $shop_url . 'member/status/2/');
	}
	$total = $rsOrder['Order_TotalPrice'];
	$total_souce = $rsOrder['Order_TotalAmount'];
	$Coupon_Cash = $rsOrder['Coupon_Cash'];
	$Curgio_Cash = $rsOrder['curagio_money'];
	$Integral_Money = $rsOrder['Integral_Money'];
	$ordersn = date("Ymd",$rsOrder["Order_CreateTime"]).$rsOrder["Order_ID"];
	$Order_CartList = !empty($rsOrder["Order_CartList"]) ? json_decode($rsOrder["Order_CartList"],true): array();
	if ($rsOrder['Order_Type'] != 'offline_st') {
		foreach($Order_CartList as $k=>$v){
			foreach($v as $kk=>$vv){
					if (!isset($vv['Products_IsPaysBalance']) || $vv['Products_IsPaysBalance'] != 1) {
						$Products_IsPaysBalance = 0;
						break;
					}
			}
		}
	}
}
//处理商品
$total = $total * 100;
$total = (string)$total;
$total = floor($total) / 100;

$rsUser = $DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]);

//积分抵用
// $diyong_flag = false;
// $diyong_list = json_decode($rsConfig['Integral_Use_Laws'],true);

//用户设置了积分抵用规则，且抵用率大于零 
// if(count($diyong_list) >0 && $rsConfig['Integral_Buy']>0){
	// $diyong_intergral = diyong_act($total,$diyong_list,$rsUser['User_Integral']);
	//print_R($rsUser['User_Integral']);die;
	//如果符合抵用规则中的某一个规则,且此订单之前未执行过抵用操作
	// if($diyong_intergral >0 && $rsOrder['Integral_Consumption'] ==0 && $rsUser["User_Integral"]>0){
		// $diyong_flag = true;
	// }
	
// }

//计算参加抵用后的余额
// function diyong_act($sum,$regulartion,$User_Integral){
	
	// $diyong_integral = 0;
	// foreach($regulartion as $key=>$item){	
		// if($sum >= $item['man']){
			// $diyong_integral = $item['use'];
			// continue;
		// }
		 		
	// }
	
	//如果用户积分小于最少抵用所需积分
	// if($diyong_integral > $User_Integral){
		// $diyong_integral = $User_Integral;
	// }

	// return $diyong_integral;
// }
//print_r($rsOrder);
$Yueprice = !empty(floatval($rsUser['User_Money'])) ? $rsUser['User_Money'] : 0;
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
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css' />
<link rel="stylesheet" href="/static/api/shop/css/toggle-switch.css" />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/js/plugin/layer/mobile/layer.js'></script>
<script type='text/javascript' src='/static/api/shop/js/shop.js?t=0525'></script>
<script language="javascript">
var base_url = '<?=$base_url?>';
var UsersID = '<?=$UsersID?>';
$(document).ready(shop_obj.page_init);
$(document).ready(shop_obj.select_payment_init);
</script>
<style>
a:link {text-decoration:none}
a:visited {text-decoration:none}
a:hover {text-decoration:none}
a:active {text-decoration:none}
.txt{ width:98%; height:28px; background-color:#fff;}
.inputsr{
    height: 28px;
	width:97%;
    line-height: 28px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 5px;
    padding: 0 5px;
	}
.kuang_pay{
	background: #fff;
    overflow: hidden;
    position: fixed;
    bottom: 0;
    width: 100%;
    left: 0;
    z-index: 5;
    text-align: right;
    margin: 0 auto;
	height:50px;
}
.kuang_pay .btn_paytop{
	float: left;
    margin: 10px 20px 10px 10px;
    font-size: 15px;
    color: ccc;
    color: #bbb;    
}
.kuang_pay .btn_pay{
	background: #dc2929;
    width: 28%;    
    display: block;
    float: right;
    text-align: center;    
    padding: 10px 0px 10px 0px;
    color: #fff;
    font-size: 18px;
    margin: 8px 8px 0px 0px;
}
</style>
</head>

<body>
<div id="shop_page_contents">
	<div id="cover_layer"></div>
	<link href='/static/api/shop/skin/default/css/cart.css?t=0525' rel='stylesheet' type='text/css' />
	<link href='/static/api/shop/skin/default/css/payraido.css?t=0525' rel='stylesheet' type='text/css' />
	<script language="javascript">$(document).ready(shop_obj.payment_init);</script>
	<div id="payment">
		<div class="i-ture">
			<h1 class="t">订单信息</h1>
			<div class="info"> 订 单 号：<?=$ordersn?>
			</div>
		</div>
		<?php if ($rsUser['User_Money'] > 0 && !$appFlag) { ?>
		<?php if(!empty($rsPay["Payment_RmainderEnabled"])){ ?>
		<?php if($Products_IsPaysBalance == 1){ ?>
		<?php if(!empty($rsPay["PaymentWxpayEnabled"]) || !empty($rsPay["Payment_AlipayEnabled"])){
			$showyue = 1;
		?>
		<div class="i-ture" id="Yuebc" style="height:60px;">
			<h1 class="t">使用余额</h1>			<!-- 余额补差 -->
				
                    <span style="margin-top:5px;display: block;float: left;">使用余额</span>
                    <a style="float:right;padding:5px;">
                        <input type="checkbox" orderid="<?=$OrderID?>" class="Yuebc_btn toggle-switch" style="margin:0;" name="Order_Yuebc" value="1" />
                    </a>

                    <div class="container" id="Yuebc_info" style="display:none">
                        <div class="row order_extra_info">
                            <input type="text" class="inputsr" name="Yuebc_InvoiceInfo" orderid="<?=$OrderID?>" placeholder="当前余额:<?=$rsUser['User_Money']?>" class="txt" />
                        </div>
                    </div>		
		</div>
		<?php } } } } ?>
		
		<div class="i-ture">
			<h1 class="t">明细</h1>
			<div class="info">
				订单金额：<span class="fc_red">￥<?=$total_souce?></span><br/>
				优惠金额：<span class="fc_red">￥<?=$Coupon_Cash?></span><br/>
				会员金额：<span class="fc_red">￥<?=$Curgio_Cash?></span><br/>
				积分抵用：<span class="fc_red">￥<?=$Integral_Money?></span><br/>
				<?php if (!empty($showyue)) { ?>
				使用余额：<span class="fc_red" id="Useryue">￥0</span><br/>	
				<?php } ?>
			</div>
		</div>
		
		<form id="payment_form" action="/api/<?=$UsersID ?>/shop/cart/">
		
			<!-- 支付方式选择 begin  -->
			<div class="i-ture pay">
				<h1 class="t">选择支付方式</h1>				
				<ul class="show" id="pay-btn-panel">
					<?php if($appFlag){ ?>
						<?php if(!empty($rsPay["PaymentAppWxpayEnabled"])){ ?>
						<li><label><img src="/static/api/shop/skin/default/weixinridio.png">微信支付<input name="Payselect" type="radio" value="" data-value="app微支付" id="appwzf"><span></span></label> </li>
						<?php }?>
					<?php }?>
					<?php if(!empty($rsPay["PaymentWxpayEnabled"]) && !$appFlag){ ?>
					<li><label><img src="/static/api/shop/skin/default/weixinridio.png">微信支付<input name="Payselect" type="radio" value="" data-value="微支付" id="wzf"><span></span></label> </li>					
					<?php }?>
					<?php if(!empty($rsPay["Payment_AlipayEnabled"])){?>
						<li><label><img src="/static/api/shop/skin/default/zhifubaoridio.png">支付宝支付<input name="Payselect" type="radio" value="" data-value="支付宝" id="zfb"><span></span></label> </li>
						<?php if(!empty($rsPay["PaymentYeepayEnabled"])){?>
						<li><label><img src="/static/api/shop/skin/default/yeepayridio.png">易宝支付<input name="Payselect" type="radio" value="" data-value="易宝支付" id="ybzf"><span></span></label> </li>
						<?php }?>
					<?php }
					if(!empty($rsPay["Payment_UnionpayEnabled"])){ ?>
						<li><label><img src="/static/api/shop/skin/default/Unionpayridio.png">银联支付<input name="Payselect" type="radio" value="" data-value="银联支付" id="uni"><span></span></label> </li>
					<?php } ?>
					<?php if(!empty($rsPay["PaymentTeegonEnabled"])){ ?>
					<li><label><img src="/static/api/shop/skin/default/teegon.png">商派天工支付<input name="Payselect" type="radio" value="" data-value="商派天工支付" id="teegon"><span></span></label> </li>
					<?php } ?>
					<?php
                   if(!empty($rsPay["Payment_OfflineEnabled"])){?>
					<li><label><img src="/static/api/shop/skin/default/yuedow.png">线下支付<input name="Payselect" type="radio" value="" data-value="线下支付" id="out"><span></span></label> </li>
					<?php }?>
					<?php if ($rsUser['User_Money'] >= $total) { ?>
					<?php if(!empty($rsPay["Payment_RmainderEnabled"])){?>
						<?php if($Products_IsPaysBalance == 1){?>
							<li><label><img src="/static/api/shop/skin/default/yue.png">余额支付<input name="Payselect" type="radio" value="" data-value="余额支付" id="money"><span></span></label> </li>
						<?php }?>
					<?php }?>
					<?php }?>
				</ul>
			</div>
			<!-- 支付方式选择 end -->
			
			<input type="hidden" name="PaymentMethod" id="PaymentMethod_val" value=""/>
			<input type="hidden" name="PaymentID" id="PaymentID_val" value=""/>
			<input type="hidden" name="OrderID" value="<?=$OrderID?>" />
			<input type="hidden" name="Yuebc_price" value="" />
			<input type="hidden" name="total_price" value="<?=$total ?>" />
			<input type="hidden" name="total_pricecurpay" value="<?=$total ?>" />
			<input type="hidden" name="action" value="payment" />
			<input type="hidden" name="DefautlPaymentMethod" value="" />
			<input type="hidden" name="yuepay0524" value="0524" />
		</form>
	</div>
</div>
<div id="footer_points"></div><br><br><br><br>
<div class="kuang_pay">            
            <span class="btn_paytop">应支付：<b style="color:#ed1414">￥</b><span style="color: #ed1414;font-size:22px;" id="Yingpay"><?=$total?><label id="total"></label></span></span>
            <a href="javascript:void(0);" class="btn_pay" id="paygogo">去支付</a>
        </div>
</body>
</html>