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

if(isset($_GET["ProductsID"])){
	$ProductsID=$_GET["ProductsID"];
	$rsProducts=$DB->GetRs("weicbd_products","*","where Users_ID='".$UsersID."' and Products_ID=".$ProductsID.' and Products_Status=1');
	if(!$rsProducts){
		echo '您要访问的商品不存在！';
		exit;
	}
	$rsProducts["Products_Description"] = str_replace('&quot;','"',$rsProducts["Products_Description"]);
	$rsProducts["Products_Description"] = str_replace("&quot;","'",$rsProducts["Products_Description"]);
	$rsProducts["Products_Description"] = str_replace('&gt;','>',$rsProducts["Products_Description"]);
	$rsProducts["Products_Description"] = str_replace('&lt;','<',$rsProducts["Products_Description"]);
	$JSON=json_decode($rsProducts['Products_JSON'],true);
	$Products_Imgs = empty($JSON["ImgPath"]) ? array() : $JSON["ImgPath"];
	$rsBiz=$DB->GetRs("weicbd_biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$rsProducts["Biz_ID"].' and Biz_Status=1');
	if(!$rsBiz){
		echo '您要访问的商铺不存在！';
		exit;
	}
}else{
	echo '缺少必要的参数';
	exit;
}

if(!strpos($_SERVER['REQUEST_URI'],"OpenID=")){
	if(empty($_SESSION['OpenID'])){
		$_SESSION['OpenID']=session_id();
	}
}else{
	$url_arr = explode("OpenID=",$_SERVER['REQUEST_URI']);
	$endpos = explode("&",$url_arr[1]);
	$_SESSION['OpenID']=$endpos[0];	
}

if(!empty($_SESSION["User_ID"])){
	$userexit = $DB->GetRs("user","*","where User_ID=".$_SESSION["User_ID"]." and Users_ID='".$UsersID."'");
	if(!$userexit){
		$_SESSION["User_ID"] = "";
	}	
}

$rsWeb=$DB->GetRs("weicbd_config","WeicbdName","where Users_ID='".$UsersID."'");
$header_title = $rsWeb["WeicbdName"].'商铺';
if($rsBiz['Skin_ID'] <= 3){
 include($rsBiz['Skin_ID']."/prodetail.php");
}else{
	//如果不是，则使用smarty
	//全局变量配置
	$base_url = base_url();
	$dist = dist_url();
	$public = $base_url.'static/api/weicbd/biz/'.$rsBiz['Skin_ID'].'/';
	$home_url = $base_url.'api/'.$UsersID.'/weicbd/';
	$shop_url = $base_url.'api/weicbd/biz/index.php?UsersID='.$UsersID.'&BizID='.$rsBiz['Biz_ID'];
	
	//网页基本参数
	$smarty->assign('home_url',$home_url);
	$smarty->assign('shop_url',$shop_url);
	$smarty->assign('UsersID',$UsersID);
	$smarty->assign('base_url',$base_url);
	$smarty->assign('dist',$dist);
	$smarty->assign('biz',$rsBiz);
	$smarty->assign('public',$public);
	$smarty->assign('header_title',$header_title);
	$smarty->assign('prodcts_images_num',count($Products_Imgs));
	$smarty->assign('prodcts_images',$Products_Imgs);
	$smarty->assign('products',$rsProducts);

	//渲染页面
	$smarty->display("weicbd/biz/".$rsBiz['Skin_ID']."/prodetail.html"); 
	
}
?>