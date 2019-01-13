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
if(isset($_GET["introid"])){
	$introid=$_GET["introid"];	
}else{
	echo '缺少必要的参数';
	exit;
}
if ($introid == 1) {
$header_title = "店铺简介";
} else {
$header_title = "购物须知";
}

$rsBiz['Biz_Introduce'] = str_replace('&quot;','"',$rsBiz['Biz_Introduce']);
$rsBiz['Biz_Introduce'] = str_replace("&quot;","'",$rsBiz['Biz_Introduce']);
$rsBiz['Biz_Introduce'] = str_replace('&gt;','>',$rsBiz['Biz_Introduce']);
$rsBiz['Biz_Introduce'] = str_replace('&lt;','<',$rsBiz['Biz_Introduce']);

/*如果是先前的两个模板*/
include($rsBiz['Skin_ID']."/intro.php");
?>