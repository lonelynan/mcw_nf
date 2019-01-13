<?php
ini_set("display_errors","On");
include('../global.php');

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

if(isset($_GET["UsersID"]))
{
	$UsersID=$_GET["UsersID"];
}else
{
	echo '缺少必要的参数';
	exit;
}

if(isset($_GET['categoryid'])){
	$CategoryID = $_GET['categoryid'];
	
}else{
	echo '缺少分类ID';
	exit();
}


$rsCategory = $DB->GetRs("biz_union_category","Category_Name","where Users_ID='".$UsersID."' and Category_ID=".$CategoryID);

$param = array(
	"page"=>1,
	"price"=>"",
	"areaid"=>$rsConfig["DefaultAreaID"],
	"regionid"=>0,
	"categoryid"=>$CategoryID,
	"orderby"=>"1"
);
$flag_result = 1;
if($_POST){
	if(!empty($_REQUEST["categoryid"])){
		$param["categoryid"] = $_REQUEST["categoryid"];
	}
	if(!empty($_POST["price"])){
		$param["price"] = $_POST["price"];
	}
	if(!empty($_POST["areaid"])){
		$param["areaid"] = $_POST["areaid"];
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
}
// echo '<pre>';
// print_R($rsConfig["DefaultAreaID"]);
// print_R($param);
// exit;
include("skin/bizlist.php");
?>