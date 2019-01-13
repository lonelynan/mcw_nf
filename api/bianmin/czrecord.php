<?php
ini_set('display_errors', 'on');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
//设立产品类别  0  联通   1 移动
if (isset($_GET["UsersID"])) {
	$UsersID = $_GET["UsersID"];
} else {
	echo '缺少必要的参数';
	exit;
}

//设立产品类别  0  联通   1 移动
//获得平台所有者的id  
//支付方式:判断


$user_id=$_SESSION[$UsersID.'User_ID'];
$recordInfo = array();
$recordSource = $DB->Get('user_order', 'Order_ID,Order_CartList,Order_CreateTime,Order_TotalPrice,Address_Mobile', ' WHERE `Order_Type` = "bianmin" and `Users_ID` = "'.$UsersID.'" and `Order_PaymentMethod` = "余额支付"and `User_ID` = '.$user_id.' ORDER BY `Order_CreateTime` DESC');
while ($row = $DB->fetch_assoc()) 
{
	$recordInfo[] = $row;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>流量中心</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/user.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/js/user.js'></script>
<style type="text/css">
	.czList { display: block; overflow: hidden; background: #FFF; margin: 10px auto; width: 97%; border-radius: 5px; }
	.czList p { padding: 0 10px; margin: 5px 0; }
	span.bold { font-weight: bold; }
	span.fc_red { color: #ff0000; font-weight: bold; }
</style>
</head>

<body>
	<?php foreach ($recordInfo as $k => $v) : ?>
	<?php $Order_CartList = json_decode($v['Order_CartList'], true); ?>
	<?php
	$money = 0;
	if(isset($Order_CartList)){
		foreach($Order_CartList as $key=>$value){
			foreach($value as $k2=>$v2){
			    $money = $v2["ProductsPriceX"]*$v2["Qty"];
			}
		}
	}
	?>
	<div class="czList">
		<p><span class="bold">编号：</span><span><?php echo $v['Order_ID']; ?></span>　　<span class="bold">充值金额：</span><span class="fc_red">￥<?php echo $money; ?></span></p>
		<p><span class="bold">充值号码：</span><span><?php echo $v['Address_Mobile'];?></span></p>
		<p><span class="bold">应付金额：</span><span class="fc_red">￥<?php echo $v['Order_TotalPrice']; ?></span></p>
		<p><span class="bold">创建时间：</span><span><?php echo date('Y-m-d H:i:s', $v['Order_CreateTime']); ?></span></p>
	</div>

	<?php endforeach; ?>
	<div>
		<input id="button" style="border-radius: 5px; width: 100%;background: #f60 none repeat scroll 0 0; line-height: 40px;color:white;"  type="button" value="返回上一级" onclick="history.go(-1)"></input>
	</div>
</body>
</html>