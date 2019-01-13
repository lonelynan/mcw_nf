<?php 
//20161101
require_once ($_SERVER ["DOCUMENT_ROOT"] .'/control/shop/cart.class.php');
if ($action == 'paymoney'){
	
	$BizID = $_POST["BizID"];
	$rsBiz = $DB->GetRs("biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID.' and Biz_Status=0');
	$rsUser = $DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID.'User_ID']);
	if($rsUser && is_numeric($_POST['Amount']) && $_POST['Amount']>0){
		//模拟加入购物车（兼容。。。。。。。。不解释。。。。都是泪）
		$cart = new cart($DB,$UsersID);
	    $cart->offline_cart_add($_POST);
		require_once ($_SERVER ["DOCUMENT_ROOT"] .'/control/shop/ordercontrol.class.php');
		$order = new ordercontrol($DB, $UsersID);
		require_once($_SERVER["DOCUMENT_ROOT"].'/control/shop/setting.class.php');
		$setting = new setting($DB,$UsersID);
		$rsConfig = $setting->get_one();
		$order->offline_order_create($_SESSION[$UsersID.'User_ID'],$_POST,$rsConfig);
	}else{
		$Data=array(
			'status'=>0,
			'msg'=>'消费金额输入格式错误'
		);
	}
	echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );exit;
}
?>