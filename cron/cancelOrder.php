<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');

use Illuminate\Database\Capsule\Manager as DB;

if(isset($_GET['create']) && $_GET['create']){
	$Time = "23:00";
	$interval = 20;
	if(PHP_OS == 'WINNT'){
		require_once ($_SERVER["DOCUMENT_ROOT"] . '/cron/windowSchedule.php');
		$taskName = "cancelOrder_Task";
		$task = new Task();
		$task->add("ST", $Time);
		$task->add("RI", $interval);
		$task->add("RU",'"System"');
		$task->remove($taskName);
		$task->create($taskName ,"cmd /c " .$_SERVER["DOCUMENT_ROOT"]."/cron/Run.bat http://".$_SERVER['HTTP_HOST']."/cron/cancelOrder.php");
		$task->getXML($taskName);
		exit;
	}else{
		require_once ($_SERVER["DOCUMENT_ROOT"] . '/cron/crontab.php');
		// 非windows 执行
		$arrTime = explode(':', $Time);
		$cron  = "*/" . $interval . " " . $arrTime[0] . ' ';
		$cron .= "* * * ";
		$cron .= "curl -s http://".$_SERVER['HTTP_HOST']."/cron/cancelOrder.php";
		try{
			Crontab::removeJob($cron);
			Crontab::addJob($cron);
		}catch(Exception $e){
			echo "请授予当前用户运行crontab的权限";exit;
		}
	}
}
$currentTime = time();
$getOrderList = DB::table("user_order")->where(["Order_Status" => 1])->where("Order_CreateTime","<", $currentTime - 1200)->get(['Order_ID']);
if(!empty($getOrderList)){
    $orderlist = array_map(function($val){
        return $val['Order_ID'];
    }, $getOrderList);
    cancelOrder($orderlist);
}
