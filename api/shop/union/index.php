<?php
ini_set("display_errors","On");
include('../global.php');

if (isset($_COOKIE['dcact'])) {
    $cookieact = $_COOKIE['dcact'];
}
if (isset($cookieact)) {
    $act = $cookieact;
} else {
    $act = 'auto';
}
//分享开始
$Share = array();
$Share["link"] = $shop_url.'union/';
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

//获取选择的模版
$rsBiz_config = $DB->GetRs('biz_config', 'Skin_ID', 'where Users_ID = "' . $UsersID . '"');
$rsSkin=$DB->GetRs("biz_union_home","*","where Users_ID='".$UsersID."' and Skin_ID = " . $rsBiz_config['Skin_ID']);
//调用模版
$show_support = true;

require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_message.class.php');
$weixin_message = new weixin_message($DB,$UsersID,0);
$weixin_message->sendordernotice();

include("skin/index" . ($rsBiz_config['Skin_ID'] > 1 ? $rsBiz_config['Skin_ID'] : '') . ".php");
?>