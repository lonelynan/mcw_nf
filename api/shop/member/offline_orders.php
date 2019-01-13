<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');

ini_set("display_errors","On"); 

/*分享页面初始化配置*/
$share_flag = 1;
$signature = '';


$base_url = base_url();
$shop_url = shop_url();

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}
if(empty($_SESSION[$UsersID."User_ID"])){
	$_SESSION[$UsersID."HTTP_REFERER"]="/api/".$UsersID."/shop/member/";
	header("location:/api/".$UsersID."/user/login/");
}
$Status=empty($_GET["Status"])?4:$_GET["Status"];

$rsConfig=$DB->GetRs("shop_config","*","where Users_ID='".$UsersID."'");

//获取可用的支付方式列表
$Pay_List = get_enabled_pays($DB,$UsersID);

//如果设置了action
if(!empty($_GET['action'])){
	$action = $_GET['action'];
	$Order_ID = $_GET['OrderID'];
	
	if($action == 'delete'){
		
		//若是分销订单，删除分销记录
		if(is_distribute_order($DB,$UsersID,$Order_ID)){
			delete_distribute_record($DB,$UsersID,$Order_ID);
		}
	    $condition = "where Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."' and Order_ID=".$Order_ID;
		$rsOrder = $DB->getRs('user_order','Integral_Consumption',$condition);
		mysql_query("BEGIN"); //开始事务定义
		
		$Flag_a = TRUE;
		if($rsOrder['Integral_Consumption']>0){
			$Falg_a = remove_userless_integral($UsersID,$_SESSION[$UsersID."User_ID"],$rsOrder['Integral_Consumption']);
		}
		
		$condition = "Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."' and Order_ID=".$Order_ID;
		$Flag_b =$DB->Del("user_order",$condition);
		
		
		if($Flag_a&&$Flag_b)
		{
			mysql_query("COMMIT"); //执行事务
			$url = $base_url.'api/'.$UsersID.'/shop/member/status/0/';	
			echo '<script language="javascript">alert("订单取消成功");window.location="'.$url.'";</script>';
		}else
		{
			mysql_query("ROLLBACK"); //回滚事务
			echo '<script language="javascript">alert("订单取消失败");history.back();</script>';
		}
		exit;
	}
}
$_STATUS = array('<font style="color:#F00; font-size:12px;">申请中</font>','<font style="color:#F60; font-size:12px;">卖家同意</font>','<font style="color:#0F3; font-size:12px;">买家发货</font>','<font style="color:#600; font-size:12px;">卖家收货并确定退款价格</font>','<font style="color:blue; font-size:12px;">完成</font>','<font style="color:#999; font-size:12px; text-decoration:line-through;">卖家拒绝退款</font>');
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>个人中心</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/shop/js/shop.js'></script>
<script language="javascript">
	var base_url = '<?php echo $base_url;?>';
	var UsersID = '<?php echo $UsersID;?>';
	$(document).ready(shop_obj.page_init);
</script>
</head>

<body>
<div id="shop_page_contents">
	<div id="cover_layer"></div>
	<link href='/static/api/shop/skin/default/css/member.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
	<h2 style="width:100%; height:40px; line-height:38px; font-size:16px; text-align:center; font-weight:bold; border-bottom:1px #dfdfdf solid">我的实体店消费单</h2>
	<div id="order_list">
		<?php
$DB->get("user_order","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."' and (Order_Type='offline_st' || Order_Type='offline_qrcode') and Order_Status=".$Status." order by Order_CreateTime desc");
$lists = array();
while($r=$DB->fetch_assoc()){
	$lists[] = $r;
}
foreach($lists as $rsOrder){
	$CartList=json_decode($rsOrder["Order_CartList"],true);
?>
		<div class="item">
			<h1> 订单号：<a href="<?php echo '/api/'.$UsersID.'/shop/member/offline_detail/'.$rsOrder["Order_ID"].'/' ?>"><?php echo date("Ymd",$rsOrder["Order_CreateTime"]).$rsOrder["Order_ID"] ?></a> （<strong class="fc_red">￥<?php echo $rsOrder["Order_TotalPrice"] ?></strong>）<?php echo $rsOrder["Coupon_ID"]==0 ? '' : '<font style="color:blue; font-size:12px;">已使用优惠券</font>';?> </h1>
			<?php
	if(isset($CartList)){
		foreach($CartList as $key=>$value){
			foreach($value as $k=>$v){
				echo '<div class="pro">
					    <div class="img"><a href="/api/'.$UsersID.'/shop/biz/'.$rsOrder["Biz_ID"].'/"><img src="'.$v["ImgPath"].'" width="70" height="70"></a></div>
                            <dl class="info"><dd class="name"><a href="/api/'.$UsersID.'/shop/biz/'.$rsOrder['Biz_ID'].'/">'.$v["ProductsName"].'</a></dd><dd>消费时间：'.date("Y-m-d H:i:s",$rsOrder["Order_CreateTime"]).'</dd></dl>';
				echo '<div class="clear"></div>
                     </div>';
			}
		}
	}
?>
			<?php if($rsOrder['Order_Status'] == 4): ?>
			<div class="button_panel">
				<div class="payment"><a href="<?=$base_url?>api/<?php echo $UsersID ?>/shop/member/commit/<?php echo $rsOrder["Order_ID"] ?>/">评论</a></div>
				<div class="clear"></div>
			</div>
			<?php endif;?>
		</div>
		<?php }?>
	</div>
</div>
<?php require_once('../skin/distribute_footer.php');?>
</body>
</html>