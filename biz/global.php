<?php
basename($_SERVER['PHP_SELF'])=='global.php' && header('Location:http://'.$_SERVER['HTTP_HOST']);
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
if(empty($_SESSION["BIZ_ID"])){
	header("location:/biz/login.php");
}
$BizID = !empty($_SESSION['BIZ_ID'])?$_SESSION['BIZ_ID']:0;
$rsBiz = $capsule->table('biz')->where('Biz_ID', $BizID)->first();
if(!$rsBiz){
	echo '<script language="javascript">alert("此商家不存在！");history.back();</script>';
	exit();
}
$rsUsers = $capsule->table('users')->where('Users_ID', $rsBiz['Users_ID'])->first();
$rssetconfig = $capsule->table('setting')->where('id', 1)->first();
if(empty($rsUsers)){
	session_unset();
	echo '<script language="javascript">alert("该商城不存在！");window.location="login.php";</script>';
	exit();
}
$rsGroup = $capsule->table('biz_group')->where('Group_ID', $rsBiz["Group_ID"])->first(['Group_Name','Group_IsStore']);
if($rsGroup){
	$IsStore = $rsGroup["Group_IsStore"];
}else{
	$IsStore = 0;
}

//查询限时抢购活动设置参数，根据设置来判断价格的合法
//判断系统是否开启显示抢购活动，并且在使用方范围内
$time = time();
$active_info = $capsule->table('active')->where(['Users_ID' => $rsBiz['Users_ID'], 'Status' => 1, 'Type_ID' => 5])->where('starttime', '<=', $time)->where('stoptime', '>=', $time)->first(['ext_json']);
$flashsale = !empty($active_info['ext_json']) ? json_decode($active_info['ext_json'],true) : '';