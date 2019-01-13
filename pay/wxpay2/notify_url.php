<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/virtual.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/order.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/pay_order.class.php');
ini_set("display_errors","On"); 
$xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) && !empty(isset($GLOBALS['HTTP_RAW_POST_DATA']))?$GLOBALS['HTTP_RAW_POST_DATA']:file_get_contents("php://input");

if($xml){
	include_once("WxPayPubHelper.php");
	$notify = new Notify_pub();
	$notify->saveData($xml);
	$OrderID = $notify->data["out_trade_no"];
	//$OrderID = substr($OrderID,10);	
	//$rsOrder=$DB->GetRs("user_order","Users_ID,User_ID,Order_Status","where Order_ID='".$OrderID."'");
	//$UsersID = $rsOrder["Users_ID"];
	//$UserID = $rsOrder["User_ID"];
	//$Status = $rsOrder["Order_Status"];
	
	if(strpos($OrderID,'PRE')>-1){
		$rsOrder = $DB->GetRs("user_pre_order","*","where pre_sn='".$OrderID."'");
		if(!$rsOrder){
			echo "订单不存在";
			exit;
		}
		$UsersID = $rsOrder["usersid"];
		$UserID = $rsOrder["userid"];
		$Status = $rsOrder["status"];
	}else{
		$OrderID = substr($OrderID,10);
        $rsOrder=$DB->GetRs("user_order","Users_ID,User_ID,Order_Status,Order_Type","where Order_ID='".$OrderID."'");
		if(!$rsOrder){
			echo "订单不存在";
			exit;
		}
		$UsersID = $rsOrder["Users_ID"];
		$UserID = $rsOrder["User_ID"];
		$Status = $rsOrder["Order_Status"];
	}
	
	$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
	$rsPay=$DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
	include_once("WxPay.pub.config.php");
	
	if($notify->checkSign() == FALSE){
		$notify->setReturnParameter("return_code","FAIL");//返回状态码
		$notify->setReturnParameter("return_msg","签名失败");//返回信息
	}else{
		$notify->setReturnParameter("return_code","SUCCESS");//设置返回码
	}
	$returnXml = $notify->returnXml();
	echo $returnXml;

	if($notify->checkSign() == TRUE){
		if ($notify->data["return_code"] == "FAIL") {
			echo "【通信出错】";
		}elseif($notify->data["result_code"] == "FAIL"){
			echo "【业务出错】";
		}else{
			$pay_order = new pay_order($DB,$OrderID);
            $tid = $notify->data["transaction_id"]?$notify->data["transaction_id"]:'';
            $pay_order->setTransactionid($tid);
            $rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$UserID);

            if($Status==1){
                if (isset($rsOrder['Order_Type']) && $rsOrder['Order_Type'] == 'pintuan') {
                    require_once(CMS_ROOT.'/include/helper/lib_pintuan.php');
                    require_once(CMS_ROOT.'/include/helper/flow.php');
                    require_once(CMS_ROOT.'/api/pintuan/comm/function.php');
                    $data = payDone($UsersID, $OrderID, '微支付', $tid);
                } else {
                    $data = $pay_order->make_pay('微支付');
                }
				if($data["status"]==1){
					echo "SUCCESS";
					exit;
				}else{
					echo $data["msg"];
					exit;
				}
			}else{
				echo "SUCCESS";
				exit;
			}
		}
	}
}else{
	$OrderID = isset($_GET["OrderID"]) ? $_GET["OrderID"] : 0;
	if(strpos($OrderID,'PRE')>-1){
		$rsOrder = $DB->GetRs("user_pre_order","status,usersid","where pre_sn='".$OrderID."'");
		if(!$rsOrder){
			echo "订单不存在";
			exit;
		}
		$UsersID = $rsOrder["usersid"];
		$Status = $rsOrder["status"];
	}else{
		$rsOrder=$DB->GetRs("user_order","Users_ID,Order_Status,Order_Type,Owner_ID,User_ID","where Order_ID='".$OrderID."'");
		if(!$rsOrder){
			echo "订单不存在";
			exit;
		}
		$UsersID = $rsOrder["Users_ID"];
		$Status = $rsOrder["Order_Status"];
	}
	
	if($Status==1){
		echo "付款失败";
		exit;
	}else{
        if (isset($rsOrder['Order_Type']) && $rsOrder['Order_Type'] == 'pintuan') {
			//获取团任务
			$teamid = 0;
			$productid = 0;
			$pdetail = $capsule->table('pintuan_teamdetail')->where(['order_id' => $OrderID, 'userid' => $rsOrder['User_ID']])->first();
			if(!empty($pdetail)){
				$teamid = $pdetail['teamid'];
				$pteam = $capsule->table('pintuan_team')->where(['users_id' => $UsersID, 'id' => $teamid])->first();
				$productid = $pteam['productid'];
				if($pteam['teamstatus']==1){
					/*
					if(!empty($rsOrder['Owner_ID'])){
						$url = '/api/'.$UsersID.'/pintuan/' . $rsOrder['Owner_ID'] . '/xiangqing/' . $productid . '/';
					}else{
						$url = '/api/'.$UsersID.'/pintuan/xiangqing/' . $productid . '/';
					}*/
					$url = '/api/'.$UsersID.'/pintuan/orderlist/2/';
				}else{
					if(!empty($rsOrder['Owner_ID'])){
						$url = '/api/'.$UsersID.'/pintuan/' . $rsOrder['Owner_ID'] . '/jointeam/' . $productid . '/'.$teamid.'/';
					}else{
						$url = '/api/'.$UsersID.'/pintuan/jointeam/' . $productid . '/'.$teamid.'/';
					}
				}
			}else{
				die("拼团失败");
			}
        } else {
            $url = '/api/'.$UsersID.'/shop/member/status/'.$Status.'/';
        }
		echo "<script type='text/javascript'>window.location.href='".$url."';</script>";	
		exit;
	}
}
?>