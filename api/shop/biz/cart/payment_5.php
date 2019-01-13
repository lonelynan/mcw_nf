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
$OrderID=$_GET['OrderID'];
$rsConfig=$DB->GetRs("weicbd_config","*","where Users_ID='".$UsersID."'");
$rsPay=$DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
$rsOrder=$DB->GetRs("user_order","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."' and Order_ID='".$OrderID."'");
$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION["User_ID"]);
$rsBiz=$DB->GetRs("weicbd_biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$rsOrder["Biz_ID"]);
$rsOrder["CreateDate"] = date("Ymd",$rsOrder["Order_CreateTime"]);
$header_title = $rsConfig["WeicbdName"].'商铺';

if($_POST){
	$rsOrder=$DB->GetRs("user_order","*","where Users_ID='".$UsersID."' and Order_ID='".$OrderID."'");
	$rsUser = $DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION['User_ID']);
	$PaymentMethod = array(
		"微支付"=>"1",
		"支付宝"=>"2",
		"线下支付"=>"3"
	);
	if($_POST['PaymentMethod']=="线下支付" || $rsOrder["Order_TotalPrice"]<=0){
		$Data=array(
			"Order_PaymentMethod"=>$_POST['PaymentMethod'],
			"Order_PaymentInfo"=>$_POST["PaymentInfo"],
			"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"],
			"Order_Status"=>1
		);
		$Status=1;
		$Flag=$DB->Set("user_order",$Data,"where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."' and Order_ID=".$OrderID);
		$url=empty($_POST['DefautlPaymentMethod'])?"/api/".$UsersID."/weicbd/member/status/".$Status."/":"/api/".$UsersID."/weicbd/member/detail/".$_POST['OrderID']."/";
		if($Flag){
			$Data=array(
				"status"=>1,
				"url"=>$url
			);
		}else{
			$Data=array(
				"status"=>0,
				"msg"=>'线下支付提交失败'
			);
		}
	}elseif($_POST['PaymentMethod']=="余额支付" && $rsUser["User_Money"]>=$rsOrder["Order_TotalPrice"]){//余额支付
		//增加资金流水
		if(!$_POST["PayPassword"]){
			$Data=array(
				"status"=>0,
				"msg"=>'请输入支付密码'
			);
		}elseif(md5($_POST["PayPassword"])!=$rsUser["User_PayPassword"]){
			$Data=array(
				"status"=>0,
				"msg"=>'支付密码输入错误'
			);
		}else{
			$Data=array(
				'Users_ID'=>$UsersID,
				'User_ID'=>$_SESSION['User_ID'],				
				'Type'=>0,
				'Amount'=>$rsOrder["Order_TotalPrice"],
				'Total'=>$rsUser['User_Money']-$rsOrder["Order_TotalPrice"],
				'Note'=>"商城购买支出 -".$rsOrder["Order_TotalPrice"]." (订单号:".$OrderID.")",
				'CreateTime'=>time()		
			);
			$Flag=$DB->Add('user_money_record',$Data);
			//更新用户余额
			$Data=array(				
				'User_Money'=>$rsUser['User_Money']-$rsOrder["Order_TotalPrice"]				
			);
			$Flag=$DB->Set('user',$Data,"where Users_ID='".$UsersID."' and User_ID=".$_SESSION['User_ID']);
			$Data=array(
				"Order_PaymentMethod"=>$_POST['PaymentMethod'],
				"Order_PaymentInfo"=>"",
				"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"],
				'Order_Status'=>2			
			);
			$Flag=$DB->Set('user_order',$Data,"where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."' and Order_ID=".$OrderID);
			$url="/api/".$UsersID."/weicbd/member/status/2/";
			if($Flag){
				$Data=array(
					"status"=>1,
					"url"=>$url
				);
			}else{
				$Data=array(
					"status"=>0,
					"msg"=>'余额支付失败'
				);
			}
		}	
	}else{//在线支付
		$Data=array(
			"Order_PaymentMethod"=>$_POST['PaymentMethod'],
			"Order_PaymentInfo"=>"",
			"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"]
		);
		$Flag=$DB->Set("user_order",$Data,"where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."' and Order_ID=".$OrderID);
		$url = "/api/weicbd/biz/cart/pay_".$rsBiz['Skin_ID'].".php?UsersID=".$UsersID."&OrderID=".$OrderID."&Method=".$PaymentMethod[$_POST['PaymentMethod']];
		if($Flag){
			$Data=array(
				"status"=>1,
				"url"=>$url
			);
		}else{
			$Data=array(
				"status"=>0,
				"msg"=>'在线支付出现错误'
			);
		}
	}
	
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit();
}
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
$smarty->assign('public',$public);
$smarty->assign('header_title',$header_title);
$smarty->assign('biz',$rsBiz);
$smarty->assign('pay',$rsPay);
$smarty->assign('rsorder',$rsOrder);
$smarty->assign('rsuser',$rsUser);

	//渲染页面
$smarty->display("weicbd/biz/".$rsBiz['Skin_ID']."/payment.html"); 
?>

