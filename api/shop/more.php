<?php
require_once('global.php');

if(!empty($_GET['Def'])) {
	$def = $_GET['Def'];
} else {
	$def = 0;
}

$order_filter_base = $base_url.'api/shop/more.php?UsersID='.$UsersID.'&Def='.$def;
$page_url = $base_url.'api/shop/more.php?UsersID='.$UsersID.'&Def='.$def;

if($owner['id'] != '0') {
	$order_filter_base .= '&OwnerID='.$owner['id'];
	$page_url .= '&OwnerID='.$owner['id'];
}

$rsMore = array(
	'More_ID'=>0,
	'More_Name'=>'全部商品',
	'More_ListTypeID'=>1
);

if($def > 0) {
	if($def == '1') {
		$rsMore['More_ID'] = $def;
		$rsMore['More_Name'] = '推荐商品';
		$rsMore['More_ListTypeID'] = 1;
	}
	if($def == '2') {
		$rsMore['More_ID'] = $def;
		$rsMore['More_Name'] = '热卖商品';
		$rsMore['More_ListTypeID'] = 1;
	}
	if($def == '3') {
		$rsMore['More_ID'] = $def;
		$rsMore['More_Name'] = '新商品';
		$rsMore['More_ListTypeID'] = 1;
	}
}

$condition = "where Users_ID='".$UsersID."' and Products_SoldOut=0 and Products_Status=1";
if($def == '1') {
	$condition .= " and Products_IsRecommend = 1";
} elseif($def == '2') {
	$condition .= " and Products_IsHot = 1";
} elseif($def == '3') {
	$condition .= " and Products_IsNew = 1";
}

$order_by = !empty($_GET['order_by'])?$_GET['order_by']:'';
if($order_by == '1') {
	$condition .= " order by Products_CreateTime desc";
} elseif($order_by == 'price') {
	$condition .= " order by Products_PriceX asc,Products_Index asc,Products_ID desc";
} elseif($order_by == 'sales') {
	$condition .= " order by Products_Sales desc,Products_Index asc,Products_ID desc";
} else {
	$condition .= " order by Products_Index asc,Products_ID desc";
}

$order_filter_base .= '&order_by=';
$page_url .= '&order_by='.$order_by.'&page=';

//自定义分享
if(!empty($share_config)) {
	$share_config["link"] = $shop_url.'more/'.$def.'/';
	$share_config["title"] = $rsConfig["ShopName"];
	if($owner['id'] != '0' && $rsConfig["Distribute_Customize"] == 1) {	
		$share_config["desc"] = $owner['shop_announce'] ? $owner['shop_announce'] : $rsConfig["ShareIntro"];
		$share_config["img"] = strpos($owner['shop_logo'],"http://")>-1 ? $owner['shop_logo'] : 'http://'.$_SERVER["HTTP_HOST"].$owner['shop_logo'];
	} else {
		$share_config["desc"] = $rsConfig["ShareIntro"];
		$share_config["img"] = strpos($rsConfig['ShareLogo'],"http://")>-1 ? $rsConfig['ShareLogo'] : 'http://'.$_SERVER["HTTP_HOST"].$rsConfig['ShareLogo'];
	}
	
	//商城分享相关业务
	include("share.php");
}

include("skin/more.php");
?>