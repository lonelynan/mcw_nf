<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');

//设置smarty
$smarty->left_delimiter = "{{";
$smarty->right_delimiter = "}}";
$template_dir = $_SERVER["DOCUMENT_ROOT"].'/api/user/html';
$smarty->template_dir = $template_dir;

$base_url = base_url();
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;	
}

//获取指定订单
$OrderID=$_GET['OrderID'];
$rsConfig=$DB->GetRs("shop_config","ShopName,NeedShipping","where Users_ID='".$UsersID."'");
$rsOrder=$DB->GetRs("user_order","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."' and Order_ID='".$OrderID."'");
$Status=$rsOrder["Order_Status"];
$Status_List=array("待确认","待付款","已付款","已发货","已完成");
$Shipping=json_decode(htmlspecialchars_decode($rsOrder["Order_Shipping"]),true);
$CartList=json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]),true);
$amount = $fee = 0;

$Order_Sn = date("Ymd",$rsOrder['Order_CreateTime']).'-'.($rsOrder['Order_ID']);

if($rsOrder["Order_Status"]==3 && $rsOrder["Is_Commit"]==0){
	$show_comment_btn = 1;
}else{
	$show_comment_btn = 0;
}

//收货地址
$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
$area_array = json_decode($area_json,TRUE);
$province_list = $area_array[0];
$Province = '';
if(!empty($rsOrder['Address_Province'])){
	$Province = $province_list[$rsOrder['Address_Province']].',';
}
$City = '';
if(!empty($rsOrder['Address_City'])){
	$City = $area_array['0,'.$rsOrder['Address_Province']][$rsOrder['Address_City']].',';
}

$Area = '';
if(!empty($rsOrder['Address_Area'])){
	$Area = $area_array['0,'.$rsOrder['Address_Province'].','.$rsOrder['Address_City']][$rsOrder['Address_Area']];
}

//通用变量赋值
$smarty->assign('base_url',$base_url);
$smarty->assign('UsersID',$UsersID);
$smarty->assign('title','砍价订单详情');
$smarty->assign('address',$Province.$City.$Area);

//本页数据赋值
$smarty->assign('OrderSn',$Order_Sn);
$smarty->assign('OrderTime',date("Y-m-d H:i:s",$rsOrder['Order_CreateTime']));
$smarty->assign('Order',$rsOrder);
$smarty->assign('Status',$Status);
$smarty->assign("Status_List",$Status_List);
$smarty->assign('Shipping',$Shipping);
$smarty->assign('show_comment_btn',$show_comment_btn );
$smarty->assign('CartList',$CartList);

//渲染页面
$smarty->display('kanjia_order_detail.html');
