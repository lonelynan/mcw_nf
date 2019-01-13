<?php 
ini_set ( "display_errors", "On" );
require_once($_SERVER["DOCUMENT_ROOT"].'/api/shop/global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/flow.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');
if(isset($_GET["BizID"])){
	$BizID = $_GET["BizID"];
	$rsBiz = $DB->GetRs("biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID.' and Biz_Status=0');
	
	if(!$rsBiz){
		echo "<script>alert('您要访问的商铺不存在！');history.back()</script>";
		exit;
	}
	if(empty($rsBiz['Biz_stmdShow'])){
		echo "<script>alert('该商家暂未设置在线买单！');history.back()</script>";
		exit;
	}
}else{
	echo "<script>alert('缺少必要的参数！');history.back()</script>";
	exit;
}

$action = empty($_REQUEST["action"]) ? "" : $_REQUEST["action"];
if ($action == 'coupon_go') {   // 检查是否有可使用优惠券
    $total_price = isset($_POST["total_price"]) ? $_POST["total_price"] : 0;
    $coupon_info = get_useful_coupons($User_ID,$UsersID,$BizID,$total_price);
    if ($coupon_info['num'] > 0) {
        $status = 1;
    } else {
        $status = 0;
    }
    $Data = [
        "status" => $status,
        "total_price" => $total_price,
        "coupon_info" => !empty($coupon_info['lists']) ? $coupon_info['lists'] : ''
    ];
    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
    exit;
}
//分销信息
$page_url = $order_filter_base = $base_url.'api/shop/biz/offline_paymoney.php?UsersID='.$UsersID.'&BizID='.$BizID;
if($owner['id'] != '0'){	
	$shop_url = $shop_url.$owner['id'].'/';
	$page_url .= '&OwnerID='.$owner['id'];
	$order_filter_base .= '&OwnerID='.$owner['id'];
};

$header_title = $rsBiz["Biz_Name"];
if(isset($_SESSION[$UsersID."User_ID"])){
	$rsUser = $DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]);
}else{
	$_SESSION[$UsersID."HTTP_REFERER"] = "/api/".$UsersID."/shop/biz/".$BizID."/paymoney/";//
	header("location:/api/".$UsersID."/user/login/?wxref=mp.weixin.qq.com");
}

$rsConfig = $DB->GetRs("user_config","*","where Users_ID='".$UsersID."'");
$rsProfile = $DB->GetRs("user_profile","*","where Users_ID='".$UsersID."'");
$UserLevel = json_decode($rsConfig['UserLevel'],true);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $header_title;?>消费支付</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/user.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js?t=<?=time()?>'></script>
<script type='text/javascript' src='/static/api/shop/js/shop.js?t=<?=time()?>'></script>
</head>

<body>
<div id="my_header">
  <div class="face"><?php echo $rsUser["User_HeadImg"] ? '<img src="'.$rsUser["User_HeadImg"].'" />' : '';?></div>
  <ul>
    <li><?php echo $rsUser["User_Name"] ?></li>
    <li>余额: <?php echo $rsUser["User_Money"] ?>元</li>   
  </ul>
</div>
<script language="javascript">
var UsersID = '<?php echo $UsersID;?>';
$(document).ready(shop_obj.user_paymoney_init);
</script>
<div id="my">
  <form id="user_form" style="min-height: 50px;">
    <div class="profile">店内买单<?=$total_price?></div>
    <div class="input">
      <input type="text" name="Amount" value="" placeholder="消费金额" notnull />
    </div>
		
	<!-- 优惠券begin--> 
				<div class="panel-footer" style="display:none">					
					<div class="container">
						<div class="row" id="coupon-list"></div>
					</div>					
				</div>
				<!-- 优惠券end -->
		
    <div class="submit">
      <input type="button" class="submit_btn" value="提交" />
      <a href="javascript:void(0);" onclick="history.go(-1);" class="cancel">取消</a>
	</div>
      <input type="hidden" name="Amount_Y" value="0" />
    <input type="hidden" name="action" value="paymoney" />
    <input type="hidden" name="Is_store" value="1" />
	<input type="hidden" name="BizID" value="<?php echo $BizID;?>" />
	<input type="hidden" name="BizLogo" value="<?php echo $rsBiz['Biz_Logo'];?>" />
  </form>
  
</div>
<?php require_once($rsBiz['Skin_ID'].'/footer.php'); ?>
</body>
</html>