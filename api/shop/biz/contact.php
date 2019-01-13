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
if(isset($_GET["BizID"])){
	$BizID=$_GET["BizID"];
	$rsBiz=$DB->GetRs("weicbd_biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID.' and Biz_Status=1');
	if(!$rsBiz){
		echo '您要访问的商铺不存在！';
		exit;
	}
}else{
	echo '缺少必要的参数';
	exit;
}
if(isset($_GET['OpenID'])){
	$_SESSION['OpenID']=$_GET['OpenID'];
	header("location:/api/".$UsersID."/weicbd/");
	exit;
}else{
	if(empty($_SESSION['OpenID'])){
		$_SESSION['OpenID']=session_id();
	}
}
$rsWeb=$DB->GetRs("weicbd_config","WeicbdName","where Users_ID='".$UsersID."'");
$header_title = $rsWeb["WeicbdName"].'商铺';

if($rsBiz['Skin_ID'] <= 3){
	include($rsBiz['Skin_ID']."/contact.php");
}else{
	/*全局变量*/
	$base_url = base_url();
	$home_url = $base_url.'api/'.$UsersID.'/weicbd/';
	$biz_url  = $base_url.'api/weicbd/biz/index.php?UsersID='.$UsersID.'&BizID='.$BizID;
	
	/*smarty模板变量赋值*/
	
	$smarty->assign('base_url',$base_url);
	$smarty->assign('home_url',$home_url);
	$smarty->assign('biz_url',$biz_url);
	
	/*本页数据*/
	
	
	$smarty->assign('title',$rsBiz['Biz_Name'].'----联系我们');
	$smarty->assign('biz_url',$biz_url);
	$smarty->assign('biz',$rsBiz);
	
	$contact_url = $base_url.'api/weicbd/biz/contact.php?UsersID='.$UsersID.'&BizID='.$BizID;
	$smarty->assign('contact_url',$contact_url);
	/*渲染页面*/
	$smarty->display("weicbd/biz/".$rsBiz['Skin_ID']."/contact.html"); 

}

?>