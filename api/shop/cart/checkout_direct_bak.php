<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/shipping.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/flow.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');
use Illuminate\Database\Capsule\Manager as DB;

$base_url = base_url();
$shop_url = shop_url();

$UsersID = $request->input('UsersID');
if(empty($UsersID)){
	echo '缺少必要的参数';
	exit;
}

/****************** 设置smarty模板 start ***********************/
$smarty->left_delimiter = "{{";
$smarty->right_delimiter = "}}";
$template_dir = $_SERVER["DOCUMENT_ROOT"].'/api/shop/html';
$smarty->template_dir = $template_dir;
/****************** 设置smarty模板 end ***********************/

$_SESSION[$UsersID."HTTP_REFERER"] = "/api/".$UsersID."/shop/cart/checkout_direct/?love";

$rsConfig =  shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig,$dis_config);

//用户授权登录
$is_login = 1;
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/wechatuser.php');
$User_ID = !empty($_SESSION[$UsersID."User_ID"])?$_SESSION[$UsersID."User_ID"]:0;

$rsUser = DB::table('user')->where(['Users_ID' => $UsersID, 'User_ID' => $User_ID])->first();
$rsUserConfig = DB::table('User_Config')->where('Users_ID', $UsersID)->first();
$discount_list = $rsUserConfig["UserLevel"] ? json_decode($rsUserConfig["UserLevel"],true) : [];

/*************会员价计算 start **************/
$user_curagio = 0;
if(!empty($discount_list)){
	if(count($discount_list)>=1){
		$user_curagio = isset($discount_list[$rsUser['User_Level']]['Discount']) ? $discount_list[$rsUser['User_Level']]['Discount'] : 0;
		if (empty($user_curagio)) {
			$user_curagio = 0;
		}
	}
}
/*************会员价计算 end **************/

/******************* 获得用户收货地址 start ***********************/
$Address_ID = $request->input('AddressID');
$condition = [];
if(empty($Address_ID)){
	$condition = ['Users_ID' => $UsersID, 'User_ID' => $User_ID, 'Address_Is_Default' => 1];
}else{
	$condition = ['Users_ID' => $UsersID, 'User_ID' => $User_ID, 'Address_ID' => $Address_ID];
}
$rsAddress = DB::table('user_address')->where($condition)->first();
$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
$area_array = json_decode($area_json,true);
$province_list = $area_array[0];
if($rsAddress){
	$Province = $province_list[$rsAddress['Address_Province']];
	$City = $area_array['0,'.$rsAddress['Address_Province']][$rsAddress['Address_City']];
	$Area = $area_array['0,'.$rsAddress['Address_Province'].','.$rsAddress['Address_City']][$rsAddress['Address_Area']];
}else{
	$_SESSION[$UsersID."From_Checkout"] = 1;
	header("location:/api/".$UsersID."/user/my/address/edit/");
	exit;
}
/******************* 获得用户收货地址 end ***********************/

/********************* 计算积分 start *************************/

$myRedis = new Myredis($DB, 7);
$cart_key = $UsersID . "DirectBuy";
$redisCart = $myRedis->getCartListValue($cart_key, $User_ID);
if(empty($redisCart)){
	header("location:/api/".$UsersID."/shop/cart/?love");
	exit;
}
$CartList = json_decode($redisCart, true);
$Integration = 0;
foreach($CartList as $key=>$products){
	foreach($products as $kk=>$vv){
		foreach($vv as $k=>$v){				
		 $ProductsIntegration= $DB->GetRs("shop_products","Products_Integration","where Products_ID=".$kk);
		 $CartList[$key][$kk][$k]['ProductsIntegration'] = $ProductsIntegration['Products_Integration'];
		 $Integration += $v['Qty'] * $ProductsIntegration['Products_Integration'];				
		}
	}
}
/********************* 计算积分 end *************************/

$total_price =0;
$toal_shipping_fee = 0;
if(count($CartList)>0){
	if($rsAddress&&$rsConfig['NeedShipping'] == 1){
		$City_Code = $rsAddress['Address_City'];
	}else{
		$City_Code = 0;
	}
	$info = get_order_total_info($UsersID, $CartList, $rsConfig, array(), $City_Code, $user_curagio);
	$Shipping_IDs = array();
	foreach($info  as $key=>$val){
		if (is_numeric($key)) {
			$Shipping_IDs[$key] = $val['Shipping_ID'];
		}
	}
	$Shipping_IDsstr = json_encode($Shipping_IDs,JSON_UNESCAPED_UNICODE);
	$total_price = $info['total'];
	$total_shipping_fee = $info['total_shipping_fee'];
}else{
	header("location:/api/".$UsersID."/shop/cart/?love");
	exit;
}

/*商品信息汇总*/
$total_price = $total_price + $info['total_shipping_fee'];
$Default_Business = 'express';	
$Business_List = array('express'=>'快递','common'=>'平邮');	
//获取前台可用的快递公司
foreach($info  as $Biz_ID=>$BizCartList){
	if(is_integer($Biz_ID)){
		$biz_company_dropdown[$Biz_ID] = get_front_shiping_company_dropdown($UsersID,$BizCartList['Biz_Config']);
	}
}
$Business = 'express';

require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/defaut_switch.php');

//模块开关的开启
$perm_config = array();
$rs = $DB->Get('permission_config','*','where Users_ID="'.$UsersID.'"  AND Is_Delete = 0 AND Perm_Tyle = 5  order by Permission_ID asc');
while($rs = $DB->fetch_assoc()){
	$perm_config[] = $rs;
}
$islayer = 1;
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
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
<link rel="stylesheet" href="/static/api/shop/skin/default/toggle-switch.css" />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/js/bootstrap.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js?t=<?=time()?>'></script>
<script type='text/javascript' src='/static/api/shop/js/flow.js?t=<?=time()?>'></script>
<script type='text/javascript' src='/static/api/shop/js/toast.js?t=<?=time();?>'></script> 
<?php  if(isset($islayer) && $islayer ==1) {  ?>
<script type = "text/javascript" src = "/static/js/plugin/layer/layer.js?t=<?=time()?>"></script>
<? }
?>
<script type='text/javascript'>
	var base_url = '<?=$base_url?>';
	var Users_ID = '<?=$UsersID?>';
	$(document).ready(function(){
		flow_obj.checkout_init();
	});
</script>
<style>
	.number_gm1 {line-height: 36px; height: 36px;padding-left:10px;}
</style>
</head>

<body >
<header class="bar bar-nav"> <a href="javascript:history.go(-1)" class="pull-left"><img src="/static/api/shop/skin/default/images/black_arrow_left.png" /></a> <a href="/api/<?=$UsersID?>/shop/cart/" class="pull-right"><img src="/static/api/shop/skin/default/images/cart_two_points.png" /></a>
	<h1 class="title" id="page_title">提交订单 </h1>
</header>
<div id="wrap"> 
	<!-- 地址信息简述begin -->
	<?php if ($rsConfig['NeedShipping'] == 1) {?>
		<?php if ($rsAddress) {?>
			<div class="adr_bg">
			<a  href="/api/<?=$UsersID?>/user/my/address/<?=$rsAddress['Address_ID'] ?>/">
				<div class="adr_n">
					<span class="left">收货人：<?=$rsAddress['Address_Name']?></span>
					<span class="right"><?=$rsAddress['Address_Mobile']?></span>
					<div class="clear"></div>
				</div>
				<div>
					<span class="left adr_tb"><i class="fa fa-map-marker fa-x" aria-hidden="true"></i></span>
					<span class="left adr_m">收货地址：<?=$Province.'&nbsp;&nbsp;'.$City.'&nbsp;&nbsp;'.$Area?><?=$rsAddress['Address_Detailed']?></span>
					<input type="hidden" id="City_Code" value="<?=$rsAddress['Address_City']?>"/>
					<span class="right adr_or"><i class="fa  fa-angle-right fa-x" aria-hidden="true"></i></span>
				</div>
				</a>
				<div class="clear"></div>
			</div>
			<div class="texture_x"></div>
			<div class="b15"></div>
		<?php } else { ?>
				<div class="adr_bg">
					<div>
						<span class="left adr_tb"><i class="fa fa-map-marker fa-x" aria-hidden="true"></i></span>
						<a  href="/api/<?=$UsersID?>/user/my/address/edit/"><span class="left adr_mm">添加收货地址</span>
						<span class="right adr_or"><i class="fa  fa-angle-right fa-x" aria-hidden="true"></i></span></a>
					</div>
					<div class="clear"></div>
				</div>
				<div class="texture_x"></div>
				<div class="b15"></div>
		<?php } ?>
	<?php } else {?>
		<p class="row" style="text-align:center;"><br/> 此商城无需物流</p>
	<?php } ?>
	
	<!-- 地址信息简述end-->
	
	<form id="checkout_form" action="<?=$base_url?>api/<?=$UsersID?>/shop/cart/">
		<input type="hidden" name="AddressID" value="<?=$rsAddress['Address_ID'] ?>" />
		<input type="hidden" name="action" value="checkout" />
		<input type="hidden" name="Need_Shipping" value="<?=$rsConfig['NeedShipping']?>" />
		<input type="hidden" name="Shipping_arr" value='<?=$Shipping_IDsstr?>'/>
		<input type="hidden" name="City_Code" value="<?=$rsAddress['Address_City']?>"/>
		
		<!-- 产品列表begin-->
		<div class="container" id="Biz_List">
			<?php foreach($CartList as $Biz_ID=>$BizCartList):?>
			<div class="row panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<?=$info[$Biz_ID]['Biz_Config']['Biz_Name']?>
					</h3>
				</div>
				<?php $Shipping_ID = $info[$Biz_ID]['Shipping_ID'];?>
				<div class="panel-body">
					<div class="product-list" >	
						<?php							
							$Sub_Totalall = 0;
							$Sub_Totalall_souce = 0;
						?>
						<?php foreach($BizCartList as $Products_ID=>$Product_List) { ?>
						<?php
							$condition = "where Users_ID = '".$UsersID."' and Products_ID =".$Products_ID;
							$rsProduct = $DB->getRs('shop_products','Products_Count,Products_IsShippingFree,skuvaljosn',$condition);
						?>
						<!-- 购物车中产品attr begin -->						
						<input type="hidden" id="IsShippingFree_<?=$Products_ID?>" value="<?=$rsProduct['Products_IsShippingFree']?>" />
						
						<!-- 购物车中产品attr end -->
						<?php foreach($Product_List as $key=>$product) { ?>
						<?php if(strpos($product["ImgPath"], 'http') == false && !file_exists($_SERVER['DOCUMENT_ROOT'] . $product["ImgPath"])){
								$product["ImgPath"] = "/static/images/none.png";
							} ?>
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
												 $shu_price = $shu_price_souce = $product["Property"]['shu_pricesimp'];
												 $shu_conut = $product["Property"]['shu_conut'];
													echo '<dd>'.$product["Property"]['shu_value'].'</dd>';
											 }else{
												 $shu_price = $shu_price_souce = $product["ProductsPriceX"];
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
							if(!empty($rsProduct['skuvaljosn'])){
							$newkey = str_replace(";","",$key);
							}else{
							$newkey = $key;
							}
							?>
							<div class="row">
								<div class="qty_container"> <span class="pull-left">&nbsp;&nbsp;购买数量</span>
								<input type="hidden" id="Products_Count_<?=$Products_ID?>" value="<?=$shu_conut ?>" />
									<div class="qty_selector pull-right"> <a class="btn  btn-default" name="minus">-</a>
										<input id="<?=$Biz_ID?>_<?=$Products_ID?>_<?=$newkey?>" class="qty_value" type="text" value="<?=$product['Qty']?>" size="2"/>
										<input type="hidden" name="soucekey" id="<?=$Biz_ID?>_<?=$Products_ID?>_<?=$key?>" value="" />
										<a class="btn btn-default" name="add">+</a> </div>
									<div class="clearfix"></div>
								</div>
								<?php 
									$Sub_Weight = $product['ProductsWeight'] * $product['Qty'];
									$Sub_Total_souce = $shu_price_souce * $product['Qty'];
									$Sub_Total = $shu_price * $product['Qty'];
									$Sub_Qty = $product['Qty'];
								?>
							</div>
							<!-- 产品小计开始 -->
							<div class="sub_total row">
								<p> 共&nbsp;<span class="red" id="subtotal_qty_<?=$Biz_ID?>_<?=$Products_ID?>_<?=$newkey?>"><?=$product['Qty']?></span>&nbsp;件商品&nbsp;&nbsp;<span class="orange" id="subtotal_price_<?=$Biz_ID?>_<?=$Products_ID?>_<?=$newkey?>">&yen;
									<?=$Sub_Total?>
									元</span> </p>
							</div>
							<!-- 产品小计结束 --> 
						</div>
						<?php
						$Sub_Totalall_souce += $Sub_Total_souce;
						$Sub_Totalall += $Sub_Total;						
						} 
						}
						//获取优惠券
						$coupon_info = get_useful_coupons($User_ID,$UsersID,$Biz_ID,$Sub_Totalall);
						if (!empty($rsConfig['Integral_Buy'])) {
						$Products_Integration = Integration_get($Biz_ID,$rsConfig,$rsUser['User_Integral'],$Sub_Totalall_souce);
						$diyong_m = $Products_Integration/$rsConfig['Integral_Buy'];
						}
						?>
						<input type="hidden" id="Sub_Totalall_<?=$Biz_ID?>" name="total_price_bizb" value="<?=$Sub_Totalall_souce?>"/>
					</div>
				</div>
				
				<!-- 优惠券begin -->
				<div class="panel-footer">			
					<div class="container">
						<div class="row" id="coupon-list-<?=$Biz_ID?>" <?php echo $coupon_info['num'] > 0 ? '' : 'style="display:none"';?>>
							<?=build_coupon_html($smarty, $coupon_info)?>
						</div>
					</div>			
				</div>
				<?php foreach($perm_config as $key=>$value) { ?>
					<?php if($value['Perm_Field'] == 'fapiao' && $value['Perm_On'] == 0) { ?>
					<div class="panel-footer">
						<div class="row fapiao number_gm1">
							<span>是否要发票</span>
							<a class="pull-right">
								<input type="checkbox" class="Invoice_btn toggle-switch" rel="<?=$Biz_ID?>" name="Order_NeedInvoice[<?=$Biz_ID?>]" value="1" />
							</a>
							<div class="clearfix"></div>
						</div>
						
						<div class="container" id="Invoice_info_<?=$Biz_ID?>" style="display:none">
							<div class="row order_extra_info">
								<input type="text" name="Order_InvoiceInfo[<?=$Biz_ID?>]" placeholder="发票抬头，个人姓名或公司名称..." style="width:100%; height:30px; border:1px #dfdfdf solid; line-height:30px; margin-top:8px;" />
							</div>
						</div>
					</div>
					<?php } ?>
				<?php }?>
				 <!--优惠券end -->				
				<div class="row" id="Integration-list-<?=$Biz_ID?>" <?php echo !empty($Products_Integration) ? '' : 'style="display:none"';?>>
					<ul class="list-group" id="man_reduce_act">
						<li class="list-group-item" style="margin-bottom:10px;">
							<span class="jieleftfive">是否抵用积分</span>
							<a class="pull-right">
							  <input type="checkbox" class="toggle-switch" name="offset<?=$Biz_ID?>" biz_id= "<?=$Biz_ID?>" value="1" />
							</a>
						</li>
						<li class="list-group-item jieleftfive">
						   用掉<span class="fc_red" id="jin_jf<?=$Biz_ID?>"><?=$Products_Integration?></span>积分,可抵用积分金额为：￥<span class="fc_red" id="jin_diyong<?=$Biz_ID?>"><?=$diyong_m?></span>元
						</li>		
						<li class="list-group-item jieleftfive">
						   当前拥有积分：<span class="fc_red jin_zz" id="jin_zz"><?=$rsUser['User_Integral']?></span>
						</li>
						
					</ul>
				  </div>
				  
				  <script type='text/javascript'>
					$('input[name="offset<?=$Biz_ID?>"]').click(function(){
						var total = parseFloat($("#total_price").val());
						var diyong = parseFloat($("#jin_diyong<?=$Biz_ID?>").text());
						var jin_jf = parseFloat($("#jin_jf<?=$Biz_ID?>").text());
						var jin_zz = parseFloat($("#jin_zz").text());		
						if($(this).is(":checked")==true){
							var man = (Math.round((total-diyong)*100)/100).toFixed(2);
							var	jin_zzk = (Math.round((jin_zz - jin_jf)*100)/100).toFixed(2);
							$("#total_price_txt").html('&yen;'+man);
							$(".jin_zz").html(jin_zzk);
							$("#total_price").val(man);					
						}else{
							var man = (Math.round((total + diyong)*100)/100).toFixed(2);
							var	jin_zzk = (Math.round((jin_zz + jin_jf)*100)/100).toFixed(2);
							$("#total_price_txt").html('&yen;'+man);
							$(".jin_zz").html(jin_zzk);
							$("#total_price").val(man);
						}	
					});
				</script>
				<!-- 配送方式begin -->
				<div class="panel-footer">
					<?php if(!empty($info[$Biz_ID]['error'])):?>
					<div class="row"> <a href="javascript:void(0)">
						<?=$info[$Biz_ID]['msg']?>
						</a> </div>
					<?php exit;?>
					<?php else: ?>
					<div class="row shipping_method" Biz_ID=<?=$Biz_ID?> style="margin-left: -4px;">配送方式<a href="javascript:void(0)" class="pull-right"><img src="/static/api/shop/skin/default/images/arrow_right.png" height="25px" width="25px"></a> <a class="pull-right"><span id="biz_shipping_<?=$Biz_ID?>">
						<?=$info[$Biz_ID]['Shipping_Name']?>
						</span>&nbsp;&nbsp; <span id="biz_shipping_fee_txt_<?=$Biz_ID?>">
						<?=$info[$Biz_ID]['total_shipping_fee']?>
						元 </span>&nbsp;&nbsp;</a>
						<input type="hidden" name="Biz_Shipping_Fee[<?=$Biz_ID?>]" id="Shipping_ID_<?=$Biz_ID?>" value="<?=$info[$Biz_ID]['total_shipping_fee']?>"/>
					</div>
					<?php endif;?>
				</div>
				<!-- 门店列表显示 -->
				
				<?php
				$rsBiz = DB::table('biz')->where(['Biz_ID' => $Biz_ID, 'Is_Union' =>1, 'is_biz' => 1, 'is_auth' => 2])->first();
				if(!empty($rsBiz)){
				//此区域代码块主要用于检测是否有门店,如果有的话,可以选择是否上门自提以及下面的展示门店信息
				$storelist = DB::table('stores')->where(['Users_ID' => $UsersID, 'Biz_ID' => $Biz_ID, 'Stores_Status' => 1])->first(['Stores_ID', 'Stores_Name', 'Stores_PrimaryLng', 'Stores_PrimaryLat']);
				?>
				<?php
				if (!empty($storelist)) {
					?>
					<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $ak_baidu;?>"></script>
					<script type='text/javascript' src='/static/api/js/stores.js?t=<?=time()?>'></script>
					<div class="clear"></div>
					<!--- 选择门店begin -->
					<div id="Self" class="container" style="background-color: #fff; padding: 10px 30px; border-bottom: 1px dashed #ccc;">
						<div id="stores" style="display:none;">
							<input type="hidden" name="storeFlag" value="1" />
						</div>
					</div>
				<?php } ?>
				<?php } ?>
				
				<!-- 门店列表显示 -->
				<!-- 配送方式end --> 
				<!-- 订单备注begin -->
				<div class="panel-footer">
					<div class="container">
						<div class="row order_extra_info">
							<input type="text" name="Remark[<?=$Biz_ID?>]" placeholder="订单备注信息" style="width:100%; height:30px; border:1px #dfdfdf solid; line-height:30px; margin-bottom:8px;" />
							
						</div>
					</div>
				</div>
				<!-- 订单备注end --> 
			</div>
			<?php endforeach;?>
		</div>
		<!-- 产品列表end--> 
		
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
							<?php echo getFloatValue($total_price, 2); ?>
							</span></p>
						<div class="clearfix"></div>
					</li>
				</ul>
				<input type="hidden" name="cart_key" id="cart_key" value="DirectBuy"/>
				<input type="hidden" id="total_price" value="<?=$total_price?>"/>
				<input type="hidden" id="coupon_value" value="0"/> 
				<input type="hidden" id="prebiz_value" value="0"/>	
				<input type="hidden" name="Shiping_Method_<?=$Biz_ID?>" id="Shiping_Method_<?=$Biz_ID?>" value="" />
				<input type="hidden" name="deliveryNo_<?=$Biz_ID?>" id="deliveryNo_<?=$Biz_ID?>" value="" />
				<input type="hidden" id="total_shipping_fee" name="Order_Shipping" value="<?=$total_shipping_fee?>"/>
				<div class="clearfix"></div>
			</div>
			<br/>
			<div class="row" id="btn-panel">
				<button type="button" class="shop-btn btn-orange pull-right" id="submit-btn">提交</button>
				<div class="clearfix"></div>
			</div>
		</div>
		<!-- 订单汇总信息end --> 
		
		<!-- 快递公司选择begin -->
		
		<div class="container">
			<div class="row">
				<div class="modal shipping_modal_sun"  role="dialog" id="shipping-modal-<?php echo $Biz_ID?>">
					<div class="modal-dialog modal-sm">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
								<h5 class="modal-title" id="mySmallModalLabel">配送方式</h5>
							</div>
							<div class="modal-body">
								<dl id="shipping-company-list">
									<?php foreach($biz_company_dropdown as $Biz_ID=>$shipping_company_dropdown){?>
									<?php if($shipping_company_dropdown){?>
										<?php foreach($shipping_company_dropdown as  $key=>$item){?>
										<dd>
											<div class="pull-left shipping-company-name">
												<?=$item?>
											</div>
											<div class="pull-right">
												<input  type="radio" <?=($info[$Biz_ID]['Biz_Config']['Default_Shipping'] == $key)?'checked':'';?> class="Shiping_ID_Val" name="Shiping_ID_<?=$Biz_ID?>"  Biz_ID="<?=$Biz_ID?>" value="<?=$key?>" property="" id="shipping<?=$key?>"/>
											</div>
											<div class="clearfix"></div>
										</dd>
										<?php }?>
									<?php } ?>
									<?php } ?>
									<!-- 获取达达物流配置信息 start -->
									<?php
									$rsBiz = DB::table('biz')->where(['Biz_ID' => $Biz_ID])->first();
									if($rsBiz['Is_Union']==1){
										$isStores = DB::table('stores')->where(['Users_ID' => $UsersID, 'Biz_ID' => $Biz_ID, 'Stores_Status' => 1])->first();
										$thridShippList = DB::table('third_shipping')->where(['Users_ID' => $UsersID, 'Status' => 1])->get();
										if(!empty($thridShippList) && !empty($isStores)){
											foreach($thridShippList as $k => $v){
										?>
										<dd>
											<div class="pull-left shipping-company-name">
												<?=$v['Name']?>
											</div>
											<div class="pull-right">
												<input  type="radio" class="Shiping_ID_Val" name="Shiping_ID_<?=$Biz_ID?>"  Biz_ID="<?=$Biz_ID?>"  value="<?=$v['ID']?>" property="<?=$v['Tag']?>" id="shipping<?=$v['ID']?>/>
											</div>
											<div class="clearfix"></div>
										</dd>
										<?php
											}
										}
									}
									?>
									<!-- 获取达达物流配置信息 end -->
								</dl>
								<div class="clearfix"></div>
							</div>
							<div class="modal-footer">
								<a class="pull-left modal-btn" id="confirm_shipping_btn" biz_id= "<?=$Biz_ID?>">确定</a>
								<a class="pull-right modal-btn" id="cancel_shipping_btn" biz_id= "<?=$Biz_ID?>">取消</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- 快递公司选择end -->
		<input type="hidden"  id="trigger_cart_id" value=""/>
	</form>
</div>
</body>
</html>