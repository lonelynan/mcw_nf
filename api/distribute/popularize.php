<?php
require_once('global.php');
$level_config = $rsConfig['Dis_Level'];
$posterity = $accountObj->getPosterity($level_config);

$show_name = $rsUser['User_NickName'] ? $rsUser['User_NickName'] : '未设置昵称';
$show_logo = !empty($rsUser['User_HeadImg']) ? $rsUser['User_HeadImg'] : '/static/api/images/user/face.jpg';

if (isset($rsAccount) && $rsAccount && $rsConfig['Distribute_Customize'] == 1) {
	$show_name = !empty($rsAccount['Shop_Name']) ? $rsAccount['Shop_Name'] : '暂无';
	$show_logo = !empty($rsAccount['Shop_Logo']) ? $rsAccount['Shop_Logo'] : '/static/api/images/user/face.jpg';	
} 

$total_sales = round_pad_zero(get_my_leiji_sales($UsersID,$User_ID,$posterity),2);
				$accountObj->Total_Sales = $total_sales;
				$accountObj->save();
$total_income = round_pad_zero(get_my_leiji_income($UsersID,$User_ID),2);

$first = $DB->GetRs('distribute_level','*','where Users_ID="'.$UsersID.'" order by Level_ID asc');//获取第一个分销商级别

//我的推荐人
$RecommendMan = '来自总店';
$user = $DB->GetRs('user', 'Owner_Id, User_NickName, User_Mobile, User_HeadImg, User_PayPassword, Is_Distribute', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . $_SESSION[$UsersID.'User_ID']);
$agent_uid = 0;
if ($user['Owner_Id'] > 0) {
	$agent_uid = $user['Owner_Id'];
	$user = $DB->GetRs('user', 'User_NickName,User_Mobile, User_HeadImg, User_PayPassword, Is_Distribute', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . $user['Owner_Id']);
	$RecommendMan = $user['User_NickName'] ? $user['User_NickName'] : '';
}

//短消息
$sql = "SELECT COUNT(*) AS total FROM `user_message` WHERE Users_ID='" . $UsersID . "' AND Message_ID NOT IN (SELECT Message_ID FROM user_message_record WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($_SESSION[$UsersID.'User_ID']) . ")";
$query = $DB->query($sql);
$msg = $DB->fetch_assoc();
$messageTotal = (int)$msg['total'];

unset($msg);
$txtinfo = "http://".$_SERVER['HTTP_HOST']."/api/".$UsersID."/shop/".$_SESSION[$UsersID."User_ID"]."/";
//我的推荐人给我发的消息，未读数量
//$msg = $DB->GetRs('distribute_account_message', 'COUNT(*) AS total', "WHERE UsersID='" . $UsersID . "' AND User_ID=" . $agent_uid . " AND Receiver_User_ID=" . $_SESSION[$UsersID.'User_ID'] . " AND Mess_Status=0");

require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/defaut_switch.php');

//模块开关的开启
$perm_config = array();
$rs = $DB->Get('permission_config','*','where Users_ID="'.$UsersID.'"  AND Is_Delete = 0 AND Perm_Tyle = 3  order by Permission_ID asc');
while($rs = $DB->fetch_assoc()){
	$perm_config[] = $rs;
}
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<title></title>
 <link href="/static/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" href="/static/css/font-awesome.css">
    <link href="/static/api/distribute/css/style.css" rel="stylesheet">
     <link href="/static/api/distribute/css/distribute_center.css" rel="stylesheet">
	 <link href="/static/api/distribute/css/popularize.css" rel="stylesheet">
     <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="/static/js/jquery-1.11.1.min.js"></script>
	<script src="/static/api/distribute/js/distribute.js"></script>
	<script type="text/javascript" src="/static/js/clipboard.min.js"></script>
    <script type="text/javascript">
		$(document).ready(function(){
			distribute_obj.init();
		});
    </script>
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>
<?php if ($rsAccount['Is_Audit'] == 1): ?>
<div class="wrap">
	<?php if ($rsAccount['status']): ?>
	<div class="w">
	     <div class="fxbj_x">
			 <!--span class="baob_x l"><a href="#"><img src="/static/api/distribute/images/baob.png" width="20" height="20"></a></span-->
			 <span class="baob_x r"><a href="/api/<?php echo $UsersID;?>/user/message/"><img src="/static/api/distribute/images/xiaoxi_X.png" width="25" height="25">
			 <?php if ($messageTotal > 0) {?><span class="sl_x"><?php echo $messageTotal;?></span><?php } ?>

			 </a></span>
			 <div class="clear"></div>
			 <span class="flk_x l">
				 <a href="/api/<?php echo $UsersID;?>/distribute/edit_headimg/"><img id="hd_image" src="<?=$show_logo?>" /></a>
<?php
if ($show_logo == '/static/api/images/user/face.jpg') {
?>				 
				 <p style=" background:#b5b5b5; width:40px;font-size:10px; color:#fff; margin:0 auto; text-align:center;">未设置</p>
<?php
}
?>				 
			 </span>
			 <span class="flx_x l">
			 <span style="font-size:14px;"><?=$show_name?></span><span style="display:none;">ID：<?php echo $rsAccount['User_ID']; ?></span><br>

<?php
if ($user['Is_Distribute'] == 1 && (empty($user['User_NickName']) || empty($user['User_Mobile']) || empty($show_logo) || empty($user['User_PayPassword']) ) ) {
?>	

			 <a href="/api/<?=$UsersID?>/shop/member/init/?love" style="color:#fff;">完善个人资料<img src="/static/api/distribute/images/xg.png" width="15" height="15"></a>
<?php
} else {
?>
			 <a href="/api/<?=$UsersID?>/shop/member/setting/?love" style="color:#fff;">编辑个人资料<img src="/static/api/distribute/images/xg.png" width="15" height="15"></a>
<?php
}
?>
			 
			 </span>
<!--			 <span class="pj_x l"><a href="/api/<?=$UsersID?>/distribute/qrcodehb/"><img src="/static/api/distribute/images/ewm.png" width="22" height="22"></a></span>-->
			 <a >
			 <span class="fxs_x r"><?php echo empty($dis_level[$rsAccount['Level_ID']]) ? '' : $dis_level[$rsAccount['Level_ID']]['Level_Name']; ?> </span>
			 <span class="fxtb_x r"><i>V</i></span></a>
		 </div>
	 <div class="clear"></div>    
	<div class="tgm_x">
		<p>复制推广链接分享给好友：</p>
		<span class="ljm_x">
			<input id="txtInfo"  value="<?=$txtinfo?>" type="text">
		</span>
		<span style=" margin-left:1%;">
				<a class="fzm_x" id="d_clip_button" data-clipboard-text="<?=$txtinfo?>">复制<a>
		</span>
	</div>
	<?php if (!empty($perm_config)) { ?>
	<div class="fenleit_x">
         <ul class="fenleit1_x"  style="    -webkit-padding-start: 0;margin-bottom: 0;">			
			<?php foreach($perm_config as $key=>$value) { ?>
			<?php if ($value['Perm_Field'] == 'winxbarcode' && $value['Perm_On'] == 0) { ?>
			<li><a href="/api/<?=$UsersID?>/distribute/qrcodehb/"><img src="/static/api/distribute/images/wxm_x.png" width="28" height="28"><br><?=$value['Perm_Name']?></a>
			</li>
			<?php } ?>
			<?php if ($value['Perm_Field'] == 'popbarcode' && $value['Perm_On'] == 0) { ?>
			<li><a href="/api/<?=$UsersID?>/distribute/popuqrcodehb/"><img src="/static/api/distribute/images/tgm_x.png" width="25" height="25"><br><?=$value['Perm_Name']?></a>
			</li>
			<?php } ?>
			<?php if ($value['Perm_Field'] == 'winxbarcode' && $value['Perm_On'] == 0) { ?>
			<li><a href="/api/<?=$UsersID?>/distribute/reqrcodehb/"><img src="/static/api/distribute/images/wxcs_x.png" width="28" height="28"><br>重生<?=$value['Perm_Name']?></a>
			</li>
			<?php } ?>
			<?php if ($value['Perm_Field'] == 'popbarcode' && $value['Perm_On'] == 0) { ?>
			<li><a href="/api/<?=$UsersID?>/distribute/repopuqrcodehb/"><img src="/static/api/distribute/images/tgcs_x.png" width="25" height="25"><br>重生<?=$value['Perm_Name']?></a>
			</li>
			<?php } } ?>			
         </ul>
     </div>
	<?php } ?>
	</div>
   <div style="height:15px; width:100%; clear:both"></div>
   
	<?php else: ?>
        <p>您的分销账号已被禁用</p>
        <a href="<?=$shop_url?>">返回</a>
    <?php endif;?>
<?php else: ?>
	<div>
            	<div id="desc" class="col-xs-10">
    				<p style="font-size:18px;color:red;">
       				 <br/>
        			<br/>
           				您的分销申请正在审核中,<br/>
                        请耐心等待...
        			</p>
  				</div>
            </div>
<?php endif;?>

<?php require_once '../shop/skin/distribute_footer.php';?>
</body>
</html>
<script type="text/javascript">
$(function(){
	$("#d_clip_button").on("click", function(){
		var btn = document.getElementById('d_clip_button');
		var clipboard = new Clipboard(btn);//实例化
		clipboard.on('success', function(e) {
			layer.open({
				content: '复制成功',
				btn: '我知道了'
			});
			e.clearSelection();
		});

		//复制失败执行的回调，可选
		clipboard.on('error', function(e) {
			layer.open({
				content: '您的IOS系统版本不支持本功能！请选择“拷贝”进行复制!',
				btn: '我知道了'
			});
		});
	});
});
</script>