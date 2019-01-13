<?php
include('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/public/property.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/shop/products.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/shop/biz.class.php');
if(isset($_GET["ProductsID"])){
	$ProductsID=$_GET["ProductsID"];
}else{
	echo '缺少必要的参数';
	exit;
}

//分享开始
$Share = array();
$Share["link"] = $shop_url.'products/'.$ProductsID.'/';
$Share["title"] = $rsConfig["ShopName"];
if($owner['id'] != '0' && $rsConfig["Distribute_Customize"]==1){
	$Share["desc"] = $owner['shop_announce'] ? $owner['shop_announce'] : $rsConfig["ShareIntro"];
	$Share["img"] = strpos($owner['shop_logo'],"http://")>-1 ? $owner['shop_logo'] : 'http://'.$_SERVER["HTTP_HOST"].$owner['shop_logo'];
}else{
	$Share["desc"] = $rsConfig["ShareIntro"];
	$Share["img"] = strpos($rsConfig['ShareLogo'],"http://")>-1 ? $rsConfig['ShareLogo'] : 'http://'.$_SERVER["HTTP_HOST"].$rsConfig['ShareLogo'];
}
require_once($_SERVER["DOCUMENT_ROOT"].'/control/weixin/share.class.php');
$shareclass = new share($DB,$UsersID);
$share_config = $shareclass->getshareinfo($rsConfig,$Share);

//页面内容
$products_class = new products($DB,$UsersID);
$rsProducts = $products_class->get_one($ProductsID);
if(!$rsProducts){
	echo '此商品不存在';
	exit;
}elseif($rsProducts["Products_SoldOut"]==1){
	echo '此商品已下架';
	exit;
}elseif($rsProducts["Products_Status"]==0){
	echo '此商品不存在';
	exit;
}
$JSON = json_decode($rsProducts['Products_JSON'],TRUE);
if(isset($JSON["ImgPath"])){
	$rsProducts['ImgPath'] = $JSON["ImgPath"][0];
}else{
	$rsProducts['ImgPath'] =  'static/api/shop/skin/default/nopic.jpg';
}

$rsProducts["Products_Description"] = str_replace('&quot;','"',$rsProducts["Products_Description"]);
$rsProducts["Products_Description"] = str_replace("&quot;","'",$rsProducts["Products_Description"]);
$rsProducts["Products_Description"] = str_replace('&gt;','>',$rsProducts["Products_Description"]);
$rsProducts["Products_Description"] = str_replace('&lt;','<',$rsProducts["Products_Description"]);
$no_attr_price = $cur_price = $rsProducts['Products_PriceX'];

$bizclass = new biz($DB,$UsersID);
$rsBiz = $bizclass->get_one($rsProducts["Biz_ID"]);
$rsBiz["Biz_Notice"] = str_replace('&quot;','"',$rsBiz["Biz_Notice"]);
$rsBiz["Biz_Notice"] = str_replace("&quot;","'",$rsBiz["Biz_Notice"]);
$rsBiz["Biz_Notice"] = str_replace('&gt;','>',$rsBiz["Biz_Notice"]);
$rsBiz["Biz_Notice"] = str_replace('&lt;','<',$rsBiz["Biz_Notice"]);
//会员相关
$UserID = 0;
$Is_Distribute = 0;  //用户是否为分销会员
$un_accept_coupon_num = 0;
$rsProducts['Products_IsFavourite'] = 0;

if(!empty($_SESSION[$UsersID."User_ID"])){
	$UserID = $_SESSION[$UsersID."User_ID"];
	$rsUser = $DB->GetRs("user","Is_Distribute,User_Level","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Users_ID='".$UsersID."'");
	$Is_Distribute = $rsUser['Is_Distribute'];
	
	//查看此用户是否有未领取优惠券
	$DB->query("SELECT COUNT(*) as num FROM user_coupon WHERE Users_ID='".$UsersID."' and Coupon_StartTime<".time()." and Coupon_EndTime>".time()." and user_coupon.Coupon_ID NOT IN ( SELECT Coupon_ID FROM user_coupon_record WHERE Users_ID='".$UsersID."' and User_ID = ".$_SESSION[$UsersID."User_ID"]." ) order by Coupon_CreateTime desc");
	$rs_unaccept_count = $DB->fetch_assoc();
	$un_accept_coupon_num = $rs_unaccept_count['num'];
	
	//若用户已经登陆，判断此商品是否被当前登陆用户收藏
	$rsFavourites = $DB->getRs('user_favourite_products',"Products_ID","where User_ID='".$_SESSION[$UsersID."User_ID"]."' and Products_ID=".$ProductsID);	
	if($rsFavourites != FALSE){
		$rsProducts['Products_IsFavourite'] = 1;
	}
}

//属性
$flag = 0;
//得到该商品的属性josn
$rsAttr = $DB->GetRs("shop_products","skujosn,skuvaljosn,skuimg","where Users_ID='".$UsersID."' and Products_ID=".$ProductsID);
$rsattrarr = json_decode($rsAttr['skujosn'], true);
if (count($rsattrarr) > 0) {
	$flag = 1;
}
$cur_jiage = $rsProducts['Products_PriceX'];

$DB->query('SELECT MAX(convert(Attr_Price,  SIGNED)) as max_Attr_Price FROM shop_products_attr WHERE Products_ID='.$ProductsID);
$max_price = $DB->fetch_assoc();
if(!empty($max_price)) {
	$max_Attr_Price = $max_price['max_Attr_Price'];
} else {
	$max_Attr_Price = 0;
}
$DB->query('SELECT MIN(convert(Attr_Price,  SIGNED)) as min_Attr_Price FROM shop_products_attr WHERE Products_ID='.$ProductsID);
$min_price = $DB->fetch_assoc();
if(!empty($min_price)) {
	$min_Attr_Price = $min_price['min_Attr_Price'];
} else {
	$min_Attr_Price = 0;
}

//获取此商品评论信息
$comment_aggregate=$DB->GetRs("user_order_commit","AVG(Score) as Points,COUNT(Item_ID) as NUM","where Users_ID='".$UsersID."' and Status=1 and MID='shop' and Product_ID=".$ProductsID);
if(!$comment_aggregate){
	$comment_aggregate = array(
		"Points"=>0,
		"NUM"=>0,
		"ico"=>0,
	);
}else{
	$comment_aggregate["Points"] = getFloatValue($comment_aggregate["Points"],2);
	$comment_aggregate["ico"] = round($comment_aggregate["Points"], 0);
}

/*相关联产品，从此产品的分类中找出三个其他产品*/
$condition = "where Products_Category in (".trim($rsProducts['Products_Category'],',').") and Products_SoldOut=0 and Products_ID !=".$rsProducts['Products_ID'];
$condition .= " and Users_ID='".$UsersID."'";
$products = $DB->GetAssoc('shop_products','*',$condition,4);
$relatedProducts = handle_product_list($products);

include("skin/products.php");
?>