<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

if(isset($_GET["BizID"])){
	$BizID=$_GET["BizID"];
	$rsBiz=$DB->GetRs("weicbd_biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID.' and Biz_Status=1');
	if(!$rsBiz){
		echo '您要访问的商铺不存在！';
		exit;
	}
}else{
	echo '缺少必要的参数';
	exit;
}

if(!empty($_SESSION["User_ID"])){
	$userexit = $DB->GetRs("user","*","where User_ID=".$_SESSION["User_ID"]." and Users_ID='".$UsersID."'");
	if(!$userexit){
		$_SESSION["User_ID"] = "";
	}	
}

if(empty($_SESSION["User_ID"])){
	$_SESSION["HTTP_REFERER"]="/api/".$UsersID."/weicbd/cart/checkout/";
	header("location:/api/".$UsersID."/user/login/");
}else{
	$u_profile = $DB->GetRs("user","*","where User_ID=".$_SESSION["User_ID"]);
	
	if($u_profile["User_Profile"]==0){
		$_SESSION["HTTP_REFERER"]="/api/".$UsersID."/weicbd/cart/checkout/";		
		header("location:/api/".$UsersID."/user/complete/?wxref=mp.weixin.qq.com");
	}
}

$rsConfig=$DB->GetRs("weicbd_config","*","where Users_ID='".$UsersID."'");
$rsPay=$DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
$CartList=json_decode($_SESSION["WeicbdList"],true);
$total=0;
if(count($CartList)<=0){
	header("location:/api/".$UsersID."/weicbd/cart/");
	exit;
}
$OrderList = $Total = array();
foreach($CartList as $key=>$value){
	$arr = explode(",",$key);
	$OrderList[$key[0]][] = $value;
}
$total = 0;


foreach($OrderList as $ko=>$vo){
	foreach($vo as $key=>$value){
		foreach($value as $k=>$v){
			$Total[$ko] = $v["Qty"]*$v["ProductsPriceX"];
			$total += $v["Qty"]*$v["ProductsPriceX"];
		}
	}
}



$lists = array();
$num = 0;
foreach($Total as $k=>$t){
	$DB->Get("user_coupon_record","*","where User_ID=".$_SESSION["User_ID"]." and Users_ID='".$UsersID."' and Biz_ID=".$k." and Coupon_UseArea=1 and (Coupon_UsedTimes=-1 or Coupon_UsedTimes>0) and Coupon_StartTime<=".time()." and Coupon_EndTime>=".time());
	while($rsCoupon=$DB->fetch_assoc()){
		if($rsCoupon["Coupon_Condition"]<=$t){
			if($rsCoupon['Coupon_UseType']==0 && $rsCoupon['Coupon_Discount']>0 && $rsCoupon['Coupon_Discount']<1){
				$lists[] = $rsCoupon;
				$num++;
			}
			if($rsCoupon['Coupon_UseType']==1 && $rsCoupon['Coupon_Cash']>0){
				$lists[] = $rsCoupon;
				$num++;
			}
		}
	}
}


	/*获取收件地址*/
	 $rsAd = $DB->get("user_address","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."'");
	 $address_list = $DB->toArray($rsAd);
	
	 //全局变量配置
	$base_url = base_url();
	$dist = dist_url();
	$public = $base_url.'static/api/weicbd/biz/'.$rsBiz['Skin_ID'].'/';
	$home_url = $base_url.'api/'.$UsersID.'/weicbd/';
	$shop_url = $base_url.'api/weicbd/biz/index.php?UsersID='.$UsersID.'&BizID='.$rsBiz['Biz_ID'];
	
	$header_title = '提交订单页面';
	
	//网页基本参数
	$smarty->assign('home_url',$home_url);
	$smarty->assign('shop_url',$shop_url);
	$smarty->assign('UsersID',$UsersID);
	$smarty->assign('base_url',$base_url);
	$smarty->assign('dist',$dist);
	$smarty->assign('public',$public);
	$smarty->assign('header_title',$header_title); 
	$smarty->assign('biz_name',$rsBiz['Biz_Name']);
	$smarty->assign('biz_id',$rsBiz['Biz_ID']);
	$smarty->assign('Biz',$rsBiz);
	$smarty->assign('address_list',$address_list);
	$smarty->assign('total',$total);
	
	//渲染页面
	$smarty->display("weicbd/biz/".$rsBiz['Skin_ID']."/checkout.html"); 
?>