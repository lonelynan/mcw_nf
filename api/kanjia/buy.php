<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/shipping.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/flow.php');

//设置smarty
$smarty->left_delimiter = "{{";
$smarty->right_delimiter = "}}";

$template_dir = $_SERVER["DOCUMENT_ROOT"].'/api/kanjia/skin/1';
$smarty->template_dir = $template_dir;

$base_url = base_url();

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

if(isset($_GET["KanjiaID"])){
	$KanjiaID=$_GET["KanjiaID"];
}else{
	echo '缺少必要的参数';
	exit;
}
$User_ID = $_SESSION[$UsersID."User_ID"];
//获取此活动信息
$condition = "where Users_ID = '".$UsersID."' and Kanjia_ID='".$KanjiaID."'";
$activity = $DB->GetRs('kanjia',"*",$condition);
//获取产品列表
$CartList = json_decode($_SESSION[$UsersID."KanjiaCartList"], true);
$rsConfig =  $DB->GetRs("shop_config","*","where Users_ID='".$UsersID."'");
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig,$dis_config);
//获得用户地址
$Address_ID = !empty($_GET['AddressID'])?$_GET['AddressID']:0;
if($Address_ID == 0){
	$condition = "Where Users_ID = '".$UsersID."' And User_ID = ".$User_ID." And Address_Is_Default = 1";
}else{
	$condition = "Where Users_ID = '".$UsersID."' And User_ID = ".$User_ID." And Address_ID =".$Address_ID;
}
$rsAddress = $DB->GetRs('user_address','*',$condition);
$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
$area_array = json_decode($area_json,TRUE);
$province_list = $area_array[0];
if($rsAddress){
	$Province = $province_list[$rsAddress['Address_Province']];
	$City = $area_array['0,'.$rsAddress['Address_Province']][$rsAddress['Address_City']];
	$Area = $area_array['0,'.$rsAddress['Address_Province'].','.$rsAddress['Address_City']][$rsAddress['Address_Area']];
}else{
	$_SESSION[$UsersID."From_Checkout"] = 1;
	header("location:/api/".$UsersID."/user/my/address/edit/");
}
//获取配送方式
if(!empty($CartList)){
	if($rsAddress&&$rsConfig['NeedShipping'] == 1){
		$City_Code = $rsAddress['Address_City'];
	}else{
		$City_Code = 0;
	}
}
$info = get_order_total_infokanjia($UsersID, $CartList, $rsConfig, array(), $City_Code);
$Default_Business = 'express';	
$Business_List = array('express'=>'快递','common'=>'平邮');	
//获取前台可用的快递公司
$shipping_list = array();
foreach($info  as $Biz_ID=>$BizCartList){
	if(!isset($BizCartList['error'])){
		if(is_integer($Biz_ID)){
			$biz_company_dropdown[$Biz_ID] = get_front_shiping_company_dropdown($UsersID,$BizCartList['Biz_Config']);
			$shipping_list[$Biz_ID]['Express'] = $biz_company_dropdown[$Biz_ID][$BizCartList['Shipping_ID']];
			$shipping_list[$Biz_ID]['Price'] = $BizCartList['total_shipping_fee'];
		}
	}else{
		echo "<script>alert('请添加快递公司！');location.href='http://".$_SERVER['HTTP_HOST']."/api/".$UsersID."/kanjia/'</script>";exit;
	}
	
}

$Business = 'express';
//获取用户地址
$rsAddress = $DB->get("user_address","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."'");
$address_list = $DB->toArray($rsAddress);

//获取用户参加此活动信息
$condition = "where Users_ID = '".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."'";
$condition .= " and Kanjia_ID=".$KanjiaID;
$member_activity = $DB->GetRs('kanjia_member','*',$condition);

//计算订单总价
$cur_price = $member_activity['Cur_Price'];

$shipping_price = $info['total_shipping_fee']; 
$order_sum = $cur_price+$shipping_price;
$shippingcom = count($shipping_list);
//通用变量赋值
$smarty->assign('base_url',$base_url);
$smarty->assign('UsersID',$UsersID);
$smarty->assign('public',$base_url.'/static/api/kanjia/');
$smarty->assign('kanjia_url',$base_url.'api/'.$UsersID.'/kanjia/');
$smarty->assign('title','确认订单');

//本页变量赋值
$smarty->assign('KanjiaID',$KanjiaID);
$smarty->assign('activity',$activity);
$smarty->assign('member_activity',$member_activity);
$smarty->assign('shipping_list',$shipping_list);
$smarty->assign('address_list',$address_list);
$smarty->assign('shipping_price',$shipping_price);
$smarty->assign('order_sum',$order_sum);
$smarty->assign('shippingcom',$shippingcom);
$smarty->display('buy.html');