<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/level3.php');

//分销相关设置
$dis_config = dis_config($UsersID);
//我的分销商等级
$level_config = $rsConfig['Dis_Level'];
$posterity = $accountObj->getPosterity($level_config);
//var_dump($posterity);exit;
$posterity_count = $posterity->count();
$posterity_list = $accountObj->getLevelList($level_config);
$posterity_list = array_slice($posterity_list,0,$rsConfig['Dis_Mobile_Level'],TRUE);


$level_name_list = $yinrulevel;

//首页自定义设置 edit in 2016.3.24
if (!empty($rsConfig['Index_Professional_Json'])){
	$Index_Professional_Json = json_decode($rsConfig['Index_Professional_Json'], TRUE);
} else {
	$Index_Professional_Json = array(
		'myterm' => '我的团队',
		'childlevelterm' => $level_name_list,
		'catcommission' => '佣金',
		'cattuijian' => '我的推荐人',
		'childtuijian' => $yinruRecommend
	);
}

//我的推荐人
$RecommendMan = '来自总店';
$user = $DB->GetRs('user', 'Owner_Id', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . $_SESSION[$UsersID.'User_ID']);
$agent_uid = 0;
if (isset($user['Owner_Id']) && $user['Owner_Id'] > 0) {
	$agent_uid = $user['Owner_Id'];
	$user = $DB->GetRs('user', 'User_NickName,User_Mobile', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . $user['Owner_Id']);
	$RecommendMan = $user['User_NickName'] ? $user['User_NickName'] : '';
} else {
	$user = [
		'User_NickName' => '',
		'User_Mobile' => '',
	];
}

//我的推荐人给我发的消息，未读数量
$msg = $DB->GetRs('distribute_account_message', 'COUNT(*) AS total', "WHERE UsersID='" . $UsersID . "' AND User_ID=" . $agent_uid . " AND Receiver_User_ID=" . $_SESSION[$UsersID.'User_ID'] . " AND Mess_Status=0");

$type = isset($_GET['type']) ? (int)$_GET['type'] : 0;
if ($type > 3) $type = 0;

//查看当前登录用户的未读消息
$countMessage = array();
$DB->Get('distribute_account_message', 'count(*) as num,User_ID', ' WHERE `Receiver_User_ID` = '.$User_ID.' AND `Mess_Status`=0 group by User_ID');
while($r = $DB->fetch_assoc()){
	$countMessage[$r['User_ID']] = $r['num'];
}
$teamnum = 0;
foreach ($posterity_list as $key => $sub_list) {
	$teamnum += count($sub_list);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我的团队</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/user.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css' />
<link href='/static/api/distribute/css/distribute_center.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/cart.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>

</head>
<body>
<?php
 //分销开关关闭不在显示该页面 
if (empty($rsConfig['DisSwitch'])) { 
    exit();
}

?>
<div id="cart">
   <div class="cart_title"><a href="/api/<?=$UsersID?>/distribute/?love"><b></b></a><?php if(!empty($Index_Professional_Json['myterm'])) { echo $Index_Professional_Json['myterm']; } else { echo "我的团队"; } ?></div>
   <div>
		<div class="wdtd_x">
				<span class="fllp_x l"><img src="/static/api/shop/skin/default/images/tj_x.png" width="23px" height="23px"></span>
				<span class="flt1_x l "><?php if(!empty($Index_Professional_Json['cattuijian'])) { echo $Index_Professional_Json['cattuijian']; } else { echo "我的推荐人"; } ?></span>
				<span class="r">			

<?php
if ($msg['total'] > 0) {
?>				
				<a href="/api/<?=$UsersID?>/distribute/my_message/<?php echo $agent_uid;?>/" class="message_send"><img src="/static/api/shop/skin/default/images/xxx_x.png" width="20px" height="20px"></a>
				<a href="/api/<?=$UsersID?>/distribute/my_message/<?php echo $agent_uid;?>/" class="sxl_x"><?php echo $msg['total']; ?></a>						
<?php
}
?>				
				</span>
			<div class="clear"></div>
		</div>				
			<div class="wtd_x">
				<ul>
					<li>
						<span class="flt1_x l ">昵称：</span>
						<span class="l tjrt1_x"><?php echo $RecommendMan ? $RecommendMan : '未填写' ?></span>
						<div class="clear"></div>
					</li>
					<li>
						<span class="flt1_x l ">手机号：</span>
						<span class="l tjrt1_x"><?php echo $user['User_Mobile'] ? $user['User_Mobile'] : '未填写';?></span>
						<div class="clear"></div>
					</li>
				</ul>
			</div>
	</div>

   
	<div class="sz_x">
		<ul>
			<li>
				<a href="/api/<?=$UsersID?>/distribute/my_user/">
					<span class="fllp_x l"><img src="/static/api/shop/skin/default/images/hy_x.png" width="23px" height="23px"></span>
					<span class="flt2_x l modify_password">我的会员</span>
					<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="22px" height="22px"></span>
				</a>
				<div class="clear"></div>
			</li>	
		</ul>
	</div>
	<div>
		<div class="wdtd_x">
			<!--<a href="/api/<?=$UsersID?>/distribute/team/2/">
					<span class="fllp_x l"><img src="/static/api/shop/skin/default/images/td_x.png" width="23px" height="23px"></span>
					<span class="flt_x l ">我的团队</span>
			</a>-->
					<span class="fllp_x l"><img src="/static/api/shop/skin/default/images/td_x.png" width="23px" height="23px"></span>
					<span class="flt2_x l "><?php if(!empty($Index_Professional_Json['myterm'])) { echo $Index_Professional_Json['myterm']; } else { echo "我的团队"; } ?><span class="tds1_x">(<?=$teamnum?>)</span></span>
			<div class="clear"></div>
		</div>				
		<div class="wtd_x">
			<ul>
				<?php foreach ($posterity_list as $key => $sub_list): ?>
					<?php if ($key == 1) {?>
						<li>
							<a href="/api/<?=$UsersID?>/distribute/my_term/<?=$key?>/" class="item item_<?=$key?> item_group_account_btn" status="close">
								
								<span class="flt1_x l " <?php echo !empty($countMessage) ? 'style="color:red"' : '' ;?> ><?php echo empty($Index_Professional_Json['childlevelterm'][$key]) ? $level_name_list[$key] : $Index_Professional_Json['childlevelterm'][$key]; ?></span>
								
								<span class="r tds1_x"><?=count($sub_list)?></span>
							</a>  
							<div class="clear"></div>
						</li>
					<?php } else {?>
						<li>
							<a href="/api/<?=$UsersID?>/distribute/my_term/<?=$key?>/" class="item item_<?=$key?> item_group_account_btn" status="close">
								<span class="flt1_x l "><?php echo empty($Index_Professional_Json['childlevelterm'][$key]) ? $level_name_list[$key] : $Index_Professional_Json['childlevelterm'][$key]; ?></span>
								<span class="r tds1_x"><?=count($sub_list)?></span>
							</a>  
							<div class="clear"></div>
						</li>
					<?php } ?>
				<?php endforeach;?>
			</ul>
		</div>
	</div>
</div>

</body>
</html>