<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/flow.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/shipping.php');
use Illuminate\Database\Capsule\Manager as DB;

$base_url = base_url();
$shop_url = shop_url();

if(isset($_GET["UsersID"])){
	$UsersID = $_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

$_SESSION[$UsersID."HTTP_REFERER"] = "/api/".$UsersID."/shop/cart/checkout_virtual/";
$rsConfig =  $DB->GetRs("shop_config","*","where Users_ID='".$UsersID."'");
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig,$dis_config);
//用户授权登录
$is_login = 1;
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/wechatuser.php');
$User_ID = $_SESSION[$UsersID."User_ID"];

$rsUserConfig = $DB->GetRs("User_Config","UserLevel","where Users_ID='".$UsersID."'");
$discount_list = $rsUserConfig["UserLevel"] ? json_decode($rsUserConfig["UserLevel"],TRUE) : array();
$rsUser = $DB->GetRs("user","User_Level","where User_ID=".$User_ID);
$user_curagio = 0;
if(!empty($discount_list)){
	if(count($discount_list)>=1){
		$user_curagio = isset($discount_list[$rsUser['User_Level']]['Discount']) ? $discount_list[$rsUser['User_Level']]['Discount'] : 0;
		if (empty($user_curagio)) {
			$user_curagio = 0;
		}
	}
}

//获取产品列表
$myRedis = new Myredis($DB, 7);
$cart_key = $UsersID . "Virtual";
$redisCart = $myRedis->getCartListValue($cart_key, $User_ID);
$CartList = !empty($redisCart) ? json_decode($redisCart, true) : [];

// 获得产品的ID 计算积分	
$Integration = 0;
//产品是否有门店
$storeFlag = false;
if(!empty($CartList)){
    foreach($CartList as $key=>$products){
        foreach($products as $kk=>$vv){
            foreach($vv as $k=>$v){
                if (!empty($v['ProductsStore'])) {
                    $storeFlag = true;
                }
                $ProductsIntegration= $DB->GetRs("shop_products","Products_Integration","where Products_ID=".$kk);
                $CartList[$key][$kk][$k]['ProductsIntegration'] = $ProductsIntegration['Products_Integration'];
                $Integration += $v['Qty'] * $ProductsIntegration['Products_Integration'];
            }
        }
    }
}else{
    header("location:/api/".$UsersID."/shop/cart/?love");
    exit;
}


$total_price =0;
if(!empty($CartList)){
	$info = get_order_total_info($UsersID, $CartList, $rsConfig, -1, 0, $user_curagio);
	$total_price = $info['total'];
	$total_shipping_fee = $info['total_shipping_fee'];
}else{
	header("location:/api/".$UsersID."/shop/cart/?love");
	exit;
}

/*商品信息汇总*/
$total_price = $total_price + $info['total_shipping_fee'];
$recieve = 0;
$pids = array();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>订单确认页面</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/style.css?t=<?=time()?>' rel='stylesheet' type='text/css' />
<link href="/static/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/static/css/font-awesome.css" />
<link rel="stylesheet" href="/static/api/shop/skin/default/css/tao_checkout.css" />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/js/bootstrap.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js?t=<?=time()?>'></script>
<script type='text/javascript' src='/static/api/shop/js/flow.js?t=<?=time()?>'></script>
<script type='text/javascript' src='/static/api/shop/js/toast.js?t=<?=time();?>'></script>
<script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script>
    <?
    if ($storeFlag) {
        ?>
        <script type='text/javascript' src='/static/api/js/stores.js'></script>
        <script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $ak_baidu;?>"></script>
        <script language="javascript">var baidu_map_ak='Xbq3g4meudxD5Q0MB9osTLpg'; $(document).ready(stores_obj.stores_init);</script>
    <? } ?>
<script type='text/javascript'>
	var base_url = '<?=$base_url?>';
	var Users_ID = '<?=$UsersID?>';
	$(document).ready(function(){
		flow_obj.checkout_init();
	});
</script>
</head>

<body >
<header class="bar bar-nav"> <a href="javascript:history.go(-1)" class="pull-left"><img src="/static/api/shop/skin/default/images/black_arrow_left.png" /></a> <a href="/api/<?=$UsersID?>/shop/cart/" class="pull-right"><img src="/static/api/shop/skin/default/images/cart_two_points.png" /></a>
	<h1 class="title" id="page_title">提交订单 </h1>
</header>
<div id="wrap"> 
	<div class="b15"></div>
	
	<!-- 地址信息简述end-->
	
	<form id="checkout_form" action="<?=$base_url?>api/<?=$UsersID?>/shop/cart/">
		<input type="hidden" name="action" value="checkout" />
		<input type="hidden" name="checkout" value="checkout_virtual" />
		<!-- 产品列表begin-->
		<div class="container" id="Biz_List">
			<?php foreach($CartList as $Biz_ID => $BizCartList){?>
			<div class="row panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
                        <?php
                        $rsBiz = DB::table("biz")->where(["Biz_ID" => $Biz_ID])->first(['Biz_Name']);

                        ?>
						<?=$rsBiz['Biz_Name']?>
					</h3>
				</div>
				<div class="panel-body">
					<div class="product-list" >
						<?php
							//获取优惠券
							//$coupon_info = get_useful_coupons($User_ID,$UsersID,$Biz_ID,$total_price);
						?>
						<?php foreach($BizCartList as $Products_ID => $Product_List){?>
						<?php
							$pids[] = $Products_ID;
							$condition = "where Users_ID = '".$UsersID."' and Products_ID =".$Products_ID;
							$rsProduct = $DB->getRs('shop_products','Products_Count,Products_IsShippingFree,Products_IsRecieve,skuvaljosn',$condition);
							if($rsProduct["Products_IsRecieve"]== 1){
								$recieve = 1;
							}
						?>
						<!-- 购物车中产品attr begin -->
						<input type="hidden" id="Products_Count_<?=$Products_ID?>" value="<?=$rsProduct['Products_Count']?>" />
						
						<!-- 购物车中产品attr end -->
						<?php foreach($Product_List as $key=>$product){?>
						<div class="product">
							<div class="simple-info row">
								<dl>
									<dd class="col-xs-2"><img src="<?=$product['ImgPath']?>" class="thumb"/></dd>
									<dd class="col-xs-6">
										<h5>
											<?=sub_str($product['ProductsName'],18,true)?>
										</h5>
										<dl class="option">
											<?php 
											if(!empty($rsProduct['skuvaljosn'])){
												 $shu_price = $product["Property"]['shu_pricesimp'];
												 $shu_conut = $product["Property"]['shu_conut'];
													echo '<dd>'.$product["Property"]['shu_value'].'</dd>';
											 }else{
												 $shu_price = $product["ProductsPriceX"];
												 $shu_conut = $rsProduct['Products_Count'];
											 }
											 
											 if (isset($user_curagio) && !empty($user_curagio)) {
												$shu_price = $shu_price * (1 - $user_curagio / 100);
												$shu_price = getFloatValue($shu_price, 2);
											 }
											?>
										</dl>
									</dd>
									<dd class="col-xs-2 price"> <span class="orange">
									&yen;<?=$shu_price?>
										</span></dd>
									<div class="clearfix"></div>
								</dl>
							</div>
							<?php 
							$newkey = str_replace(";","",$key);
							?>
							<div class="row">
								<div class="qty_container"> <span class="pull-left">&nbsp;&nbsp;购买数量</span>
									<div class="qty_selector pull-right"> <a class="btn  btn-default" name="minus">-</a>
										<input id="<?=$Biz_ID?>_<?=$Products_ID?>_<?=$newkey?>" class="qty_value" type="text" value="<?=$product['Qty']?>" size="2"/>
										<input type="hidden" name="soucekey" id="<?=$Biz_ID?>_<?=$Products_ID?>_<?=$key?>" value="" />
										<a class="btn btn-default" name="add">+</a> </div>
									<div class="clearfix"></div>
								</div>
								<?php 
									$Sub_Weight = $product['ProductsWeight'] * $product['Qty']; 
									$Sub_Total = $shu_price * $product['Qty']; 
									$Sub_Qty = $product['Qty']; 
								?>
							</div>
							<!-- 产品小计开始 -->
							<div class="sub_total row">
								<p> 共&nbsp;<span class="red" id="subtotal_qty_<?=$Biz_ID?>_<?=$Products_ID?>_<?=$newkey?>">
									<?=$product['Qty']?>
									</span>&nbsp;件商品&nbsp;&nbsp;<span class="orange" id="subtotal_price_<?=$Biz_ID?>_<?=$Products_ID?>_<?=$newkey?>">&yen;
									<?=$Sub_Total?>
									元</span> </p>
							</div>
							<!-- 产品小计结束 --> 
						</div>
						<?php } ?>
						<?php }?>
					</div>
				</div>
				<!-- 订单备注begin -->
				<div class="container">
					<?php $cardcount = $DB->GetRs('shop_virtual_card','count(Card_Id) as num','where Products_Relation_ID in('.implode(',',$pids).')');?>
					<?php if($recieve == 1 && $cardcount['num']==0){?>
					<input type="hidden" name="Mobile" value="<?php echo !empty($_SESSION[$UsersID."User_Mobile"]) ? $_SESSION[$UsersID."User_Mobile"] : ''; ?>" />
					<?php }else{ ?>
					<div class="row order_extra_info" >
						<h5 class="t">接收短信手机</h5>
						<input type="text"  id="Mobile_Input" name="Mobile" value="<?php echo !empty($_SESSION[$UsersID."User_Mobile"]) ? $_SESSION[$UsersID."User_Mobile"] : ''; ?>" pattern="[0-9]*" notnull />
						
					</div>
					<?php } ?>
					<div class="row order_extra_info">
						<h5>订单备注信息</h5>
						<textarea name="Remark[<?=$Biz_ID?>]" placeholder="选填，填写您对本订单的特殊需求，如送货时间等"></textarea>
						<div style="height:10px;"></div>
					</div>
				</div>
				<!-- 订单备注end --> 
			</div>
			<?php }?>
		</div>
		<!-- 产品列表end-->
        <?
        //此区域代码块主要用于检测是否有门店,如果有的话,可以选择是否上门自提以及下面的展示门店信息
        $storeInfo = [];
        if (!empty($rsProduct)) {
            if (!empty($rsProduct['Products_Stores'])) {
                $storesArr = json_decode($rsProduct['Products_Stores'], true);
                foreach ($storesArr as $k => $v) {
                    $storeLsInfo = $DB->GetRs('stores', '*', "where Stores_ID = " . $v);
                    if (!empty($storeLsInfo)) {
                        $storeInfo[$v] = $storeLsInfo;
                    }
                }
            }
        }

        if (!empty($storeInfo)) {
            ?>

            <!--是否上门自提开始-->
            <div class="container" style="border-bottom:1px dashed #ccc; line-height: 45px;">
                <div class="row" style="padding: 0 10px;"> &nbsp;&nbsp;<span>是否上门自提</span> <a class="pull-right">
                        <input type="checkbox" class="Self_Get toggle-switch" name="Self_Get" value="1" />
                        &nbsp;&nbsp;&nbsp;&nbsp; </a>
                    <div class="clearfix"></div>
                </div>
            </div>
            <!--是否上门自提结束-->

        <? } ?>

        <?
        if (!empty($storeInfo)) {
            ?>
            <!--- 选择门店begin -->
            <div class="container" style="background-color: #fff; padding: 10px 30px; border-bottom: 1px dashed #ccc;">
                <div class="row" style="line-height: 35px;text-align: center;height: 35px;">选择消费门店</div>
                <div id="stores">
                    <input type="hidden" name="storeFlag" value="1" />
                    <?
                    foreach ($storeInfo as $k => $v) {
                        ?>
                        <div class="row store" style="line-height: 30px;" lng="<?php echo $v["Stores_PrimaryLng"] ?>" lat="<?php echo $v["Stores_PrimaryLat"] ?>">
                            <div style="height: 30px;">
                                <span style="float: left;"><input type="radio" name="Store" value="<?=$k?>">&nbsp;&nbsp;<?=$v['Stores_Name']?>&nbsp;&nbsp;地址:<a href="http://api.map.baidu.com/marker?location=<?php echo $v["Stores_PrimaryLat"] ?>,<?php echo $v["Stores_PrimaryLng"] ?>&title=<?php echo $v["Stores_Name"] ?>&name=<?php echo $v["Stores_Name"] ?>&content=<?php echo $v["Stores_Address"] ?>&output=html"><?=$v['Stores_Address']?></a></span>
                                <span style="float: right; color: #f00;" class="dis">0m</span>
                            </div>
                            <div>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;联系电话:
                                <?
                                $telPhone = explode("\n", $v['Stores_Telephone']);
                                foreach ($telPhone as $key => $val) {
                                    ?>
                                    <a href="tel:<?=$val?>"><?=$val?></a>
                                <? } ?>
                            </div>
                        </div>
                    <? } ?>
                </div>
            </div>
        <? } ?>
        <!-- 选择门店end -->
		<!--- 订单汇总信息begin -->
		<div class="container">
			<div class="row" >
				<ul class="list-group">
					<?php if($Integration > 0){?>	
						<li  class="list-group-item">
						   此次购物可获得<span id="total_integral" class="red">
						   <?=$Integration?></span>个积分
						</li>
					 <?php } ?>
					<li class="list-group-item">
						<p class="pull-right" id="order_total_info">合计<span id="total_price_txt" class="red">&yen;
							<?=$total_price?>
							</span></p>
						<div class="clearfix"></div>
					</li>
				</ul>
				<input type="hidden" id="coupon_value" value="0"/> 
				<input type="hidden" id="total_shipping_fee" name="Order_Shipping" value="0"/>
				<div class="clearfix"></div>
			</div>
			<br/>
			<div class="row" id="btn-panel">
				<button type="button" class="shop-btn btn-orange pull-right"   id="submit-btn">提交</button>
				<div class="clearfix"></div>
			</div>
		</div>
		<!-- 订单汇总信息end --> 
		<input type="hidden" name="cart_key" id="cart_key" value="Virtual"/>
		<input type="hidden" name="recieve" value="<?php echo $recieve;?>" />
	</form>
</div>
</body>
</html>