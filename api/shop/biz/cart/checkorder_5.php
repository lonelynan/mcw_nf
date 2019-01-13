<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
	$rsWeb=$DB->GetRs("weicbd_config","WeicbdName","where Users_ID='".$UsersID."'");
	$header_title = $rsWeb["WeicbdName"].'商铺';
}else{
	echo '缺少必要的参数';
	exit;
}

if(isset($_GET["ProductsID"])){
	$ProductsID=$_GET["ProductsID"];
	$rsProducts=$DB->GetRs("weicbd_products","*","where Users_ID='".$UsersID."' and Products_ID=".$ProductsID.' and Products_Status=1');
	if(!$rsProducts){
		echo '您要访问的商品不存在！';
		exit;
	}
	$rsBiz=$DB->GetRs("weicbd_biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$rsProducts["Biz_ID"]." and Biz_Status=1");
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
	$_SESSION["HTTP_REFERER"]="/api/weicbd/biz/cart/checkorder_".$rsBiz["Skin_ID"].".php?UsersID=".$UsersID."&ProductsID=".$ProductsID;
	header("location:/api/".$UsersID."/user/login/");
}else{
	$u_profile = $DB->GetRs("user","*","where User_ID=".$_SESSION["User_ID"]);
	if($u_profile["User_Profile"]==0){
		$_SESSION["HTTP_REFERER"]="/api/weicbd/biz/cart/checkorder_".$rsBiz["Skin_ID"].".php?UsersID=".$UsersID."&ProductsID=".$ProductsID;	
		header("location:/api/".$UsersID."/user/complete/?wxref=mp.weixin.qq.com");
	}
}
if($_POST){
	$neworderid = 0;
	$JSON=json_decode($rsProducts['Products_JSON'],true);
	$CartList[$ProductsID]=array(
		"ProductsName"=>$rsProducts["Products_Name"],
		"ImgPath"=>empty($JSON["ImgPath"])?"":$JSON["ImgPath"][0],
		"ProductsPriceX"=>$rsProducts["Products_PriceX"],
		"ProductsPriceY"=>$rsProducts["Products_PriceY"],
		"Qty"=>$_POST["Qty"],
		"Property"=>array()
	);
	$Data=array(
		"Address_Name"=>$_POST['Name'],
		"Address_Mobile"=>$_POST["Telephone"],
		"Address_Province"=>'',
		"Address_City"=>'',
		"Address_Area"=>'',
		"Address_Detailed"=>'',
		"Users_ID"=>$UsersID,
		"User_ID"=>$_SESSION["User_ID"]
	);
	$Data["Order_Type"]="weicbd";
	$Data["Order_Remark"]="入住时间：".$_POST["DateFrom"]." ".$_POST["HourFrom"].":".$_POST["MinuteFrom"].":00\r\n退房时间：".$_POST["DateEnd"]." ".$_POST["HourEnd"].":".$_POST["MinuteEnd"].":00\r\n".$_POST["Note"];
	$Data["Order_Shipping"]='';
	$Data["Order_CreateTime"]=time();
	$rsConfig=$DB->GetRs("weicbd_config","CheckOrder","where Users_ID='".$UsersID."'");
	$Data["Order_Status"]=$rsConfig["CheckOrder"]==1 ? 0 : 1;
	$Data["Order_CartList"]=json_encode($CartList,JSON_UNESCAPED_UNICODE);
	$total = $diff_time = $days = 0;
	$datefrom = $_POST["DateFrom"]." ".$_POST["HourFrom"].":".$_POST["MinuteFrom"].":00";
	$dateend = $_POST["DateEnd"]." ".$_POST["HourEnd"].":".$_POST["MinuteEnd"].":00";
	$diff_time = strtotime($dateend) - strtotime($datefrom);
	$days = $diff_time/86400;
	if($diff_time%86400>0){
		$days = $days+1;
	}
	$Data["Order_TotalAmount"]=$rsProducts["Products_PriceX"]*$_POST["Qty"]*$days;
	$Data["Order_TotalPrice"]=$rsProducts["Products_PriceX"]*$_POST["Qty"]*$days;
	$Data["Biz_ID"] = $rsProducts["Biz_ID"];
	$Flag=$DB->Add("user_order",$Data);
	$neworderid = $DB->insert_id();
	if($Flag){
		$url=$rsConfig["CheckOrder"]==1 ? "/api/weicbd/biz/cart/payment_".$rsBiz['Skin_ID'].".php?UsersID=".$UsersID."&OrderID=".$neworderid:"/api/".$UsersID."/weicbd/member/status/1/";
		$Data=array(
			"status"=>1,
			"url"=>$url
		);
	}else{
		$Data=array(
			"status"=>0
		);
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit();
}
$base_url = base_url();
$dist = dist_url();
$public = $base_url.'static/api/weicbd/biz/'.$rsBiz['Skin_ID'].'/';
$home_url = $base_url.'api/'.$UsersID.'/weicbd/';
$shop_url = $base_url.'api/weicbd/biz/index.php?UsersID='.$UsersID.'&BizID='.$rsBiz['Biz_ID'];
$timehours = $timeminutes = '';
for($Hour=0;$Hour<24;$Hour++){
	$timehours .= '<option value="'.$Hour.'"'.($Hour==date("H")?" selected":"").'>'.($Hour>9 ? $Hour : '0'.$Hour).'</option>';
}
for($Minute=0;$Minute<6;$Minute++){
    $timeminutes .= '<option value="'.($Minute*10).'"'.($Minute==intval(date("i")/10)?" selected":"").'>'.($Minute*10).'</option>';
}
//网页基本参数
$smarty->assign('home_url',$home_url);
$smarty->assign('shop_url',$shop_url);
$smarty->assign('UsersID',$UsersID);
$smarty->assign('base_url',$base_url);
$smarty->assign('dist',$dist);
$smarty->assign('public',$public);
$smarty->assign('header_title',$header_title);
$smarty->assign('biz',$rsBiz);
$smarty->assign('products',$rsProducts);
$smarty->assign('timehours',$timehours);
$smarty->assign('timeminutes',$timeminutes);

	//渲染页面
$smarty->display("weicbd/biz/".$rsBiz['Skin_ID']."/checkorder.html"); 
?>

