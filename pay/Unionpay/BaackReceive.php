<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/pay/Unionpay/common.php';
?>
<!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>响应页面</title>

<style type="text/css">
body table tr td {
	font-size: 14px;
	word-wrap: break-word;
	word-break: break-all;
	empty-cells: show;
}
</style>
</head>
<body>
	<table width="800px" border="1" align="center">
		<tr>
			<th colspan="2" align="center">响应结果</th>
		</tr>
	
			<?php
			foreach ( $_POST as $key => $val ) {
				?>
			<tr>
			<td width='30%'><?php echo isset($mpi_arr[$key]) ?$mpi_arr[$key] : $key ;?></td>
			<td><?php echo $val ;?></td>
		</tr>
			<?php }?>
			<tr>
			<td width='30%'>验证签名</td>
			<td><?php			
			if (isset ( $_POST ['signature'] )) {
				
				echo AcpService::validate ( $_POST ) ? '验签成功' : '验签失败';
				$orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
				$respCode = $_POST ['respCode']; //判断respCode=00或A6即可认为交易成功
				//如果卡号我们业务配了会返回且配了需要加密的话，请按此方法解密
        //if(array_key_exists ("accNo", $_POST)){
				//	$accNo = decryptData($_POST["accNo"]);
				//}
				if($respCode == '00' || $respCode == 'A6'){
if(strpos($out_trade_no,'PRE')>-1){
	$OrderID = $out_trade_no;
	$rsOrder = $DB->GetRs("user_pre_order","*","where pre_sn='".$OrderID."'");
	if(!$rsOrder){
		echo "订单不存在";
		exit;
	}
	$UsersID = $rsOrder["usersid"];
	$UserID = $rsOrder["userid"];
	$Status = $rsOrder["status"];
}else{
	$OrderID = substr($out_trade_no,10);
	$rsOrder=$DB->GetRs("user_order","Users_ID,User_ID,Order_Status","where Order_ID='".$OrderID."'");
	if(!$rsOrder){
		echo "订单不存在";
		exit;
	}
	$UsersID = $rsOrder["Users_ID"];
	$UserID = $rsOrder["User_ID"];
	$Status = $rsOrder["Order_Status"];
}
$pay_order = new pay_order($DB,$OrderID);
if($Status==1){
		$data = $pay_order->make_pay();
		if($data["status"]==1){
			echo "<script type='text/javascript'>window.location.href='".$data["url"]."';</script>";	
			exit;		
		}else{
			echo $data["msg"];
			exit;
		}
	}else{
		$url = '/api/'.$UsersID.'/shop/member/status/'.$Status.'/';
		echo "<script type='text/javascript'>window.location.href='".$url."';</script>";	
		exit;
	}
}
			} else {
				echo '签名为空';
			}
			?></td>
		</tr>
	</table>
</body>
</html>
