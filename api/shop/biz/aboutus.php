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


//获取指定店铺BizID
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
		
	/*先设置skinid为1，等到需要扩展时再变成灵活的方法*/
	$skinId = $rsBiz['Skin_ID'];

	/*全局变量*/
	$base_url = base_url();
	$public = $base_url.'static/api/weicbd/intro/'.$skinId .'/';
	$home_url = $base_url.'api/'.$UsersID.'/weicbd/';
	$biz_url = $base_url.'api/weicbd/biz/index.php?UsersID='.$UsersID.'&BizID='.$BizID;
	
	/*smarty模板变量赋值*/
	
	$smarty->assign('base_url',$base_url);
	$smarty->assign('public',$public);
	$smarty->assign('home_url',$home_url);
	$smarty->assign('biz_url',$biz_url);
	
	/*本页数据*/
	
	$smarty->assign('title',$rsBiz['Biz_Name'].'----关于我们');
	$smarty->assign('biz_url',$biz_url);
	$smarty->assign('detail',$rsBiz['Biz_Introduce']);
	
	$contact_url = $base_url.'api/weicbd/biz/contact.php?UsersID='.$UsersID.'&BizID='.$BizID;
	$smarty->assign('contact_url',$contact_url);
		
	/*渲染页面*/
	$smarty->display("weicbd/biz/".$skinId."/aboutus.html"); 
?>