<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');

define("DEBUG", false);

use Illuminate\Database\Capsule\Manager as DB;
if(PHP_SAPI != 'cgi-fcgi'){
    $_SERVER["DOCUMENT_ROOT"] = dirname(__DIR__);
}
if(isset($_GET['create']) && $_GET['create']){
	
	if(PHP_OS == 'WINNT'){
		require_once ($_SERVER["DOCUMENT_ROOT"] . '/cron/windowSchedule.php');
		if($_GET['create']=='remove'){
			$task = new Task();
			$task->remove("autoconfirm_Task");
			$task->remove("autoconfirm_test_Task");
		}else{
			if(DEBUG===false){	//创建计划任务
				$taskName = "autoconfirm_Task";
				$Time = "23:00";
				$task = new Task();
				$task->add("ST", $Time);
				$task->add("RU",'"System"');
				$task->remove($taskName);
				$task->create($taskName ,"cmd /c " .$_SERVER["DOCUMENT_ROOT"]."/cron/Run.bat http://".$_SERVER['HTTP_HOST']."/cron/autoconfirm.php");
				$task->getXML($taskName);
				echo "创建计划任务成功";exit;
				exit;
			}else if(DEBUG===true){	//测试环境
				$taskName = "autoClear_test_Task";
				$Time = date("H:i");
				$interval = 1;
				$task = new Task();
				$task->add("ST", $Time);
				$task->add("RI", $interval);
				$task->add("RU",'"System"');
				$task->remove($taskName);
				$task->create($taskName ,"cmd /c " .$_SERVER["DOCUMENT_ROOT"]."/cron/Run.bat http://".$_SERVER['HTTP_HOST']."/cron/autoconfirm.php");
				$task->getXML($taskName);
				echo "创建测试计划任务成功";exit;
				exit;
			}
		}
		
	}else{
		require_once ($_SERVER["DOCUMENT_ROOT"] . '/cron/crontab.php');
		// 非windows 执行
		$Time = "00:01";
		$interval = 31;
		$arrTime = explode(':', $Time);
		$cron  = "*/" . $interval . " " . $arrTime[0] . ' ';
		$cron .= "* * * ";
		$cron .= "curl -s http://".$_SERVER['HTTP_HOST']."/cron/autoconfirm.php";
		try{
			Crontab::removeJob($cron);
			Crontab::addJob($cron);
		}catch(Exception $e){
			echo "请授予当前用户运行crontab的权限";exit;
		}
	}
}

$UserList = DB::table("users")->where(["Users_Status" => 1])->get(["Users_ID"]);
foreach ($UserList as $k => $v){
	$rsConfig = DB::table("shop_config")->where("Users_ID", $v['Users_ID'])->first();
	//将超过自动收货时限的订单进行自动收货
	$ids = Order::get_expire_order($rsConfig);
	Order::observe(new OrderObserver());

	foreach($ids as $key=>$Order_ID){
		$order = Order::find($Order_ID);
		$order->confirmReceive();
	}
}

