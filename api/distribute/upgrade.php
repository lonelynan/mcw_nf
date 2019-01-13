<?php
require_once('global.php');

$userid = $User_ID;
//是否为分销商
$user = $DB->GetRs('user', 'User_NickName, User_Mobile, User_HeadImg, User_PayPassword, Is_Distribute', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($User_ID));

$show_name = $rsUser['User_NickName'] ? $rsUser['User_NickName'] : '未设置昵称';
$show_logo = !empty($rsUser['User_HeadImg']) ? $rsUser['User_HeadImg'] : '/static/api/images/user/face.jpg';

if (isset($rsAccount) && $rsAccount && $rsConfig['Distribute_Customize'] == 1) {
	$show_name = !empty($rsAccount['Shop_Name']) ? $rsAccount['Shop_Name'] : '暂无';
	$show_logo = !empty($rsAccount['Shop_Logo']) ? $rsAccount['Shop_Logo'] : '/static/api/images/user/face.jpg';	
} 

$levelids = array_keys($dis_level);
$LastLevel = end($levelids);

//短消息
//短消息
$sql = "SELECT COUNT(*) AS total FROM `user_message` WHERE Users_ID='" . $UsersID . "' AND Message_ID NOT IN (SELECT Message_ID FROM user_message_record WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid) . ")";
$query = $DB->query($sql);
$msg = $DB->fetch_assoc();
$messageTotal = (int)$msg['total'];
unset($msg);

				$r = $DB1->GetRs('user_order','SUM(Order_TotalPrice) as money','where User_ID='.$userid.' and Order_Status>=2');
				$cost_status_2 = empty($r['money']) ? 0 : $r['money'];
				
				$r = $DB1->GetRs('user_order','SUM(Order_TotalPrice) as money','where User_ID='.$userid.' and Order_Status=4');
				$cost_status_4 = empty($r['money']) ? 0 : $r['money'];
				
				if (count($dis_level) > 1) {
					$realtwo = next($dis_level);
				}
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>级别升级</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/distribute/css/style.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href='/static/api/distribute/css/upgrade.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link rel="stylesheet" href="/static/css/font-awesome.css">
<link href="/static/api/distribute/css/distribute_center.css" rel="stylesheet">
<link href='/static/api/distribute/css/apply.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/cart.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js?t=<?php echo time();?>'></script>
<script type='text/javascript' src='/static/api/distribute/js/upgrade.js?t=<?php echo time();?>'></script>
<script language="javascript">
	var UsersID = "<?=$UsersID?>";
	var ajax_url  = "<?=distribute_url()?>ajax/";
	$(document).ready(upgrade_obj.upgrade_init);
</script>
<style type="text/css">
.distribute_header #header_cushion #account_info #txt { margin-top: 25px; }
    #txt li { color: #FFF; font-size: 14px; line-height: 25px; }
    .upgrade_head { margin-top: 20px; }
	.error_msg{height:60px; line-height:60px; text-align:center; width:100%}
</style>
</head>

<body>

    <div class="container" style=" padding-right:0px; padding-left:0px">
		<div class="fxbj_x">
			<span class="baob_x r"><a href="/api/<?=$UsersID?>/user/message/"><img src="/static/api/distribute/images/xiaoxi_X.png" width="25" height="25">
			<?php if ($messageTotal > 0) {?><span class="sl_x"><?php echo $messageTotal;?></span><?php } ?>
			</a></span>
			<div class="clear"></div>
			<span class="flk_x l">
			<a href="/api/<?=$UsersID?>/distribute/edit_headimg/"><img id="hd_image" src="<?=$show_logo?>" /></a>
			<?php if ($show_logo == '/static/api/images/user/face.jpg') { ?>				 
				<p style=" background:#b5b5b5; width:40px;font-size:10px; color:#fff; margin:0 auto; text-align:center;">未设置</p>
			<?php } ?>
			</span>
			<span class="flx_x l">
				<span style="font-size:14px;"><?=$show_name?>未设置昵称</span><span style="display:none;">ID：<?php echo $rsAccount['User_ID']; ?></span><br>
<?php
if ($user['Is_Distribute'] == 1 && (empty($user['User_NickName']) || empty($user['User_Mobile']) || empty($show_logo) || empty($user['User_PayPassword']) ) ) {
?>	

			 <a href="/api/<?=$UsersID?>/shop/member/init/" style="color:#fff;">完善个人资料<img src="/static/api/distribute/images/xg.png" width="15" height="15"></a>
<?php
} else {
?>
			 <a href="/api/<?=$UsersID?>/shop/member/setting/" style="color:#fff;">编辑个人资料<img src="/static/api/distribute/images/xg.png" width="15" height="15"></a>
<?php
}
?>
			</span>
			<a>
				<span class="fxs_x r"><?php echo empty($dis_level[$rsAccount['Level_ID']]) ? '' : $dis_level[$rsAccount['Level_ID']]['Level_Name']; ?> </span>
				<span class="fxtb_x r"><i>V</i></span>
			</a>
		 </div>		
		 <?php if(!isset($realtwo) || !$realtwo['Update_switch'] || $rsConfig['Distribute_Type'] == 1) { ?>
		<div class="error_msg">非法操作！</div>
		 <?php } elseif ($LastLevel<=$rsAccount['Level_ID']) { ?>
		<div class="error_msg">你已经是最高级，无需升级！</div>
	<?php } else { ?>
	<form id="upgrade_buy" class="upgrade_form">
		<div class="level_list">
			<h2>请选择级别</h2>
			<ul>
				<?php
					$update_fee = 0;  
					$update_present = 0;
					$i = 1;
					if($rsConfig['Distribute_UpgradeWay'] == 0){
						$levels = array_keys($dis_level);
						$a = array();
						foreach($levels as $k=>$v){
							if($v == $rsAccount['Level_ID']){
								$b = $k+1;
								$a[$levels[$b]] = $dis_level[$levels[$b]];
							}
						}
						$dis_level = $a;
					}
					foreach($dis_level as $key=>$value){
						if($key<=$rsAccount['Level_ID']){
							continue;
						}
						if ($value['Update_switch'] == 1) {
						if($value['Level_UpdateType']==0){
							$update_present = $update_present + $value['Level_PresentUpdateValue'];
							$update_fee = $update_fee + $value['Level_UpdateValue'];
							if($value['Present_type'] == 2){
								$limit_value = '支付'.$update_fee.'元  送'.$update_present .' 元';
							}else{
								$limit_value = '支付'.$update_fee.'元';
							}
						}else if ($value['Level_UpdateType']==2) {
							$update_present = $value['Level_PresentUpdateValue'];
							$update_fee = $value['Level_UpdateValue'];
							if($value['Present_type'] == 2){
								$limit_value = '支付'.$update_fee.'元  送'.$update_present .' 元';
							}else{
								$limit_value = '支付'.$update_fee.'元';
							}
						}else{
							$limit_value = '需购买指定商品';
						} 
						} else {
							if ($rsConfig['Distribute_Type'] != 1) {
							$update_present = $value['Level_PresentValue'];
							$update_fee = $value['Level_LimitValue'];
							if($value['Present_type'] == 2){
								$limit_value = '支付'.$update_fee.'元  送'.$update_present .' 元';
							} else {
								$limit_value = '支付'.$update_fee.'元';
							}
							} else {
								$arr_temp = explode('|',$value['Level_LimitValue']);
								if ($arr_temp[2] == 2) {								
								if ($cost_status_2 >= $arr_temp[1]) {
								$limit_value = '立刻升级';
								} else {
								$limit_value = '继续消费';
								}
								} else {								
								if ($cost_status_4 >= $arr_temp[1]) {
								$limit_value = '立刻升级';
								} else {
								$limit_value = '继续消费';
								}
								}
							}
						}
						$i++;
				?>
				
					<li class="submit_btn" lid="<?=$value['Level_ID']?>" settype="<?=$rsConfig['Distribute_Type']?>"><img src="<?=$value['Level_ImgPath']?>"><strong><?=$value['Level_Name']?></strong><b><?=$limit_value?></b></li>
					<?php if($rsConfig['Distribute_UpgradeWay'] == 0){  break ; } ?>
				<?php }?>            
			</ul>
		</div>
		<input type="hidden" name="action" value="upgrade_level_order" />
		<input name="LevelID" type="hidden" value="" />		
		<?php $rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");?>
		<div class="pay_select_bg"></div>
		<div class="pay_select_list" id="">
			<div id="payment">
				<div class="i-ture">
					<h1 class="t">订单提交成功！</h1>
					<div class="info"> <span class="Order_info"  style="font-size: 14px;">订 单 号111：</span><br />
						订单总价：<span class="fc_red" id="Order_TotalPrice" style="font-size: 14px;">￥0<br/>
						</span> 
					</div>
				</div>
				<div class="i-ture">
					<h1 class="t">选择支付方式</h1>
					<ul id="pay-btn-panel">
						<?php if(!empty($rsPay["PaymentWxpayEnabled"])){ ?>
						<li> <a href="javascript:void(0)" class="btn btn-default btn-pay direct_pay" id="wzf" data-value="微支付"><img  src="/static/api/shop/skin/default/wechat_logo.jpg" width="16px" height="16px"/>&nbsp;微信支付</a> </li>
						<?php }?>
						<?php if(!empty($rsPay["Payment_AlipayEnabled"])){?>
						<li> <a href="javascript:void(0)" class="btn btn-danger btn-pay direct_pay" id="zfb" data-value="支付宝"><img  src="/static/api/shop/skin/default/alipay.png" width="16px" height="16px"/>&nbsp;支付宝支付</a> </li>
						<?php if(!empty($rsPay["PaymentYeepayEnabled"])){?>
						<li style="display:none;"> <a href="javascript:void(0)" class="btn btn-danger btn-pay direct_pay" id="ybzf" data-value="易宝支付"><img  src="/static/api/shop/skin/default/yeepay.png" width="16px" height="16px"/>&nbsp;易宝支付</a> </li>
						<?php }?>
						<?php }
						if(!empty($rsPay["Payment_UnionpayEnabled"])){ ?>
						<li>
							 <a href="javascript:void(0)" class="btn btn-danger btn-pay direct_pay" id="uni" data-value="银联支付"><img  src="/static/api/shop/skin/default/Unionpay.png" width="16px" height="16px"/>&nbsp;银联支付</a>
						</li>
				<?php }
					   if(!empty($rsPay["Payment_OfflineEnabled"])){?>
						<li> <a  class="btn btn-warning  btn-pay" id="out" data-value="线下支付"><img  src="/static/api/shop/skin/default/huodao.png" width="16px" height="16px"/>&nbsp;线下支付</a> </li>
						<?php }?>
						<?php if(!empty($rsPay["Payment_RmainderEnabled"])){?>
						<li> <a  class="btn btn-warning  btn-pay money_yue" id="money" data-value="余额支付"><img  src="/static/api/shop/skin/default/money.jpg"  width="16px" height="16px"/>&nbsp;余额支付</a> </li>
						<?php }?>
					</ul>
				</div>
			</div>
		</div>
		<div class="products_list_tobuy">
		<h2><span>[×]</span>请选择商品</h2>
		<div class="products_list_content"></div>
	</div>
	</form>
	<div class="body_bg"></div>
<?php }?>
<?php require_once('../shop/skin/distribute_footer.php');?> 

</body>
</html>
<script>
	$(".money_yue").click(function(){
		var Order_ID = $('.pay_select_list').attr("id");
		 window.location.href= "/api/<?=$UsersID?>/distribute/complete_pay/money/"+Order_ID+"/";
	});
</script>