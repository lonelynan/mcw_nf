<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/api/shop/global.php');
if(isset($_GET["BizID"])){
	$BizID=$_GET["BizID"];
	$rsBiz=$DB->GetRs("biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID.' and Biz_Status=0');
	
	if(!$rsBiz){
		echo '您要访问的商铺不存在！';
		exit;
	}
}else{
	echo '缺少必要的参数';
	exit;
}

$header_title = $rsBiz["Biz_Name"];

if($owner['id'] != '0' && $rsConfig["Distribute_Customize"]==1){
	$share_config["desc"] = $owner['shop_announce'] ? $owner['shop_announce'] : $rsConfig["ShareIntro"];
	if (!empty($rsBiz["Biz_Logo"])) {
	$share_config["img"] = strpos($rsBiz["Biz_Logo"],"http://")>-1 ? $rsBiz["Biz_Logo"] : 'http://'.$_SERVER["HTTP_HOST"].$rsBiz["Biz_Logo"];
	} else {
	$share_config["img"] = strpos($owner['shop_logo'],"http://")>-1 ? $owner['shop_logo'] : 'http://'.$_SERVER["HTTP_HOST"].$owner['shop_logo'];
	}
}else{
	$share_config["desc"] = $rsConfig["ShareIntro"];
	if (!empty($rsBiz["Biz_Logo"])) {
	$share_config["img"] = strpos($rsBiz["Biz_Logo"],"http://")>-1 ? $rsBiz["Biz_Logo"] : 'http://'.$_SERVER["HTTP_HOST"].$rsBiz["Biz_Logo"];
	} else {
	$share_config["img"] = strpos($rsConfig['ShareLogo'],"http://")>-1 ? $rsConfig['ShareLogo'] : 'http://'.$_SERVER["HTTP_HOST"].$rsConfig['ShareLogo'];
	}
}
// if(!$rsBiz['Is_Union']){
	// echo "<script>alert('非联盟商家无店铺！');window.history.go(-1);</script>";exit;
// }
// echo $rsBiz['Skin_ID'];exit;
/*如果是先前的两个模板*/
include($rsBiz['Skin_ID']."/index.php");
?>