<?php
require_once('global.php');
$header_title = $rsBiz["Biz_Name"];
if(!empty($_GET['Def'])) {
	$def = $_GET['Def'];
} else {
	$def = 0;
}
$order_filter_base = $base_url.'api/biz/more.php?UsersID='.$UsersID.'&BizID='.$BizID.'&Def='.$def;
$page_url = $base_url.'api/biz/more.php?UsersID='.$UsersID.'&BizID='.$BizID.'&Def='.$def;

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

$condition = "where Users_ID='".$UsersID."' AND Biz_ID=".$BizID." and Products_SoldOut=0 and Products_Status=1";
if($def == '1') {
	$condition .= " and Products_BizIsRec = 1";
} elseif($def == '2') {
	$condition .= " and Products_BizIsHot = 1";
} elseif($def == '3') {
	$condition .= " and Products_BizIsNew = 1";
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
include($rsBiz['Skin_ID']."/more.php");
?>