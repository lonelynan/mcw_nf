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

$header_title = "全部分类";

/*如果是先前的两个模板*/
include($rsBiz['Skin_ID']."/allcate.php");
?>