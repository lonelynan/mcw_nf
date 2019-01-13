<?php
require_once('global.php');
$header_title = $rsBiz["Biz_Name"];
$rsConfig['Biz_Skin_ID'] = $rsBiz['Skin_ID'];
$rsSkin=$DB->GetRs("biz_home","*","where Users_ID='".$UsersID."' and Skin_ID=".$rsConfig['Biz_Skin_ID']." AND Biz_ID = ".$rsBiz["Biz_ID"]);
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
//模版
if (!empty($_GET['OwnerID'])) {
	$_SESSION['Parent_ID'] =  $_GET['OwnerID'];
}
$_SESSION['Get_BizID'] = $_GET['BizID'];
if (empty($rsBiz['Skin_ID'])) {
	echo '模版不存在，请重新选择模版！';
	exit;
}

include($rsBiz['Skin_ID']."/index.php");
?>