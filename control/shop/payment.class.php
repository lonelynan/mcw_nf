<?php
	/*
	*	支付类
	*/
class payment{
	
	private $DB;
	private $UsersID;
	
	function __construct($DB,$UsersID){
		$this->DB = $DB;//数据库连接资源
		$this->UsersID = $UsersID;//数据库连接资源
		
	}
	
	/*
	*	支付选择
	*	@param int $User_ID 会员id
	*	@param int $Post_Data $_POST数据
	*	@echo jeson 支付选择状态信息
	*/
	function payment_check($User_ID,$Post_Data){
		
		$OrderID = empty ( $Post_Data ['OrderID'] ) ? 0 : $Post_Data ['OrderID'];

		$orderids = '';
		if(strpos($OrderID,"PRE")>-1){
			$pre_order = $this->DB->GetRs("user_pre_order","*","where pre_sn='".$OrderID."' and usersid='".$this->UsersID."' and userid=".$User_ID);
			$orderids = $pre_order["orderids"];
			$order_status = $pre_order["status"];
			$order_total = $pre_order["total"];
		}else{
			$orderids = $OrderID;
			$rsOrder = $this->DB->GetRs ( "user_order", "*", "where Users_ID='" . $this->UsersID . "' and Order_ID='" . $OrderID . "'" );
			$order_status = $rsOrder["Order_Status"];
			$order_total = $rsOrder["Order_TotalPrice"];
		}
		
		$rsUser = $this->DB->GetRs("user", "User_Money,User_PayPassword,Is_Distribute,User_Name,User_NickName,Owner_Id,User_Integral", "where Users_ID='" . $this->UsersID . "' and User_ID=" . $User_ID );
		$PaymentMethod = array(
			"微支付" => "1",
			"支付宝" => "2",
			"银联支付" => "3",
			"线下支付" => "4"
		);
		
		if($Post_Data ['PaymentMethod'] == "线下支付" || $order_total <= 0) {
			$Data = array (
					"Order_PaymentMethod" => $Post_Data ['PaymentMethod'],
					"Order_PaymentInfo" => $Post_Data ["PaymentInfo"],
					"Order_Status" => 1
			);
			
			$Status = 1;
			$flag = $this->DB->Set ( "user_order", $Data, "where Users_ID='" . $this->UsersID . "' and User_ID='" . $_SESSION [$this->UsersID . "User_ID"] . "' and Order_ID in(".$orderids.")");
			$url = "/api/" . $this->UsersID . "/shop/member/status/" . $Status . "/";
			if ($flag) {
				$Data = array (
						"status" => 1,
						"url" => $url 
				);
			} else {
				$Data = array (
						"status" => 0,
						"msg" => '线下支付提交失败' 
				);
			}
		} elseif ($Post_Data ['PaymentMethod'] == "余额支付" && $rsUser ["User_Money"] >= $order_total) { // 余额支付
			if ($order_status != 1) {
				$Data = array (
						"status" => 0,
						"msg" => '该订单状态不是待付款状态，不能付款' 
				);
			} elseif (! $Post_Data ["PayPassword"]) {
				$Data = array (
						"status" => 0,
						"msg" => '请输入支付密码' 
				);
			} elseif (md5 ( $Post_Data ["PayPassword"] ) != $rsUser ["User_PayPassword"]) {
				$Data = array (
						"status" => 0,
						"msg" => '支付密码输入错误' 
				);
			} else {
				$Data = array (
						'Users_ID' => $this->UsersID,
						'User_ID' => $User_ID,
						'Type' => 0,
						'Amount' => $order_total,
						'Total' => $rsUser ['User_Money'] - $order_total,
						'Note' => "商城购买支出 -" . $order_total . " (订单号:" . $orderids . ")",
						'CreateTime' => time () 
				);
				$Flag = $this->DB->Add ( 'user_money_record', $Data );
				// 更新用户余额
				$Data = array (
						'User_Money' => $rsUser ['User_Money'] - $order_total 
				);
				$Flag = $this->DB->Set ( 'user', $Data, "where Users_ID='" . $this->UsersID . "' and User_ID=" . $User_ID );
				
				$Data = array (
						"Order_PaymentMethod" => $Post_Data ['PaymentMethod'],
						"Order_PaymentInfo" => "",
				);
				$this->DB->Set ('user_order', $Data, "where Users_ID='" . $this->UsersID . "' and User_ID='" . $_SESSION [$this->UsersID . "User_ID"] . "' and Order_ID in(".$orderids.")");
				
				require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/pay_order.class.php');
				$pay_order = new pay_order($this->DB,$OrderID);
				$Data = $pay_order->make_pay();
			}
		} else { // 在线支付
			$Data = array (
					"Order_PaymentMethod" => $Post_Data ['PaymentMethod'],
					"Order_PaymentInfo" => "",
					"Order_DefautlPaymentMethod" => $Post_Data ["DefautlPaymentMethod"] 
			);
			$Flag = $this->DB->Set ( "user_order", $Data, "where Users_ID='" . $this->UsersID . "' and User_ID='" . $_SESSION [$this->UsersID . "User_ID"] . "' and Order_ID in(".$orderids.")");
			$url = "/api/" . $this->UsersID . "/shop/cart/pay/" . $OrderID . "/" . $PaymentMethod [$Post_Data ['PaymentMethod']] . "/";
			
			if ($Flag) {
				$Data = array (
						"status" => 1,
						"url" => $url 
				);
			} else {
				$Data = array (
						"status" => 0,
						"msg" => '在线支付出现错误' 
				);
			}
		}
		
		echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );
	}
}
?>