<?php
ini_set("display_errors","On");
include('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
//分享开始
$Share = array();
$Share["link"] = $shop_url;
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

$param = array(
	"page"=>1,
	"price"=>"",
	"regionid"=>0,
	"categoryid"=>0,
	"orderby"=>"1",
	"title"=>!empty($_POST["title"]) ? $_POST["title"] : ''
);
$flag_result = 0;
if($_POST){
	$flag_result = 1;
	if(!empty($_REQUEST["categoryid"])){
		$param["categoryid"] = $_REQUEST["categoryid"];
	}
	if(!empty($_POST["price"])){
		$param["price"] = $_POST["price"];
	}
	if(!empty($_POST["regionid"])){
		$param["regionid"] = $_POST["regionid"];
	}
	if(!empty($_POST["page"])){
		$param["page"] = $_POST["page"];
	}
	if(!empty($_POST["orderby"])){
		$param["orderby"] = $_POST["orderby"];
	}
	if(!empty($_POST["title"])){
		$param["title"] = $_POST["title"];
	}
}
//echo '111';exit;

include("skin/search.php");
?>