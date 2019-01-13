<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');
if(isset($_GET['Makeup_Skin_ID'])){
	$Makeup_Skin_ID = $_GET['Makeup_Skin_ID'];
}else{
	echo "缺少必要的参数"; exit;
}
$rsSkin=$DB->GetRs("makeup_home","*","where Users_ID='".$UsersID."' and Skin_ID=".$Makeup_Skin_ID);
//自定义分享
if(!empty($share_config)){
	$share_config["link"] = $shop_url;
	$share_config["title"] = $rsConfig["ShopName"];
	if($owner['id'] != '0' && $rsConfig["Distribute_Customize"]==1){	
		$share_config["desc"] = $owner['shop_announce'] ? $owner['shop_announce'] : $rsConfig["ShareIntro"];
		$share_config["img"] = strpos($owner['shop_logo'],"http://")>-1 ? $owner['shop_logo'] : 'http://'.$_SERVER["HTTP_HOST"].$owner['shop_logo'];
	}else{
		$share_config["desc"] = $rsConfig["ShareIntro"];
		$share_config["img"] = strpos($rsConfig['ShareLogo'],"http://")>-1 ? $rsConfig['ShareLogo'] : 'http://'.$_SERVER["HTTP_HOST"].$rsConfig['ShareLogo'];
	}
	
	//商城分享相关业务
	include("share.php");
}

$show_support = true;

//未付款订单提醒
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_message.class.php');
$weixin_message = new weixin_message($DB,$UsersID,0);
$weixin_message->sendordernotice();
//echo $rsConfig['Skin_ID'];exit;
//模版
if (!empty($_GET['OwnerID'])) {
	$_SESSION['Parent_ID'] =  $_GET['OwnerID'];
}
$makeup_skin = $DB->GetRs("makeup_skin","*","where Skin_ID =".$_GET['Makeup_Skin_ID']." order by Skin_Index asc,Skin_ID asc");
if(isset($_GET['Makeup_Skin_ID'])){
	$_SESSION['Makeup_Skin_ID'] =  $_GET['Makeup_Skin_ID'];
	
}
if(!empty($makeup_skin)){
	$_SESSION['Makeup_Skin_Name'] =  $makeup_skin['Skin_Name'];
}
include("makeup/1/index.php");
?>