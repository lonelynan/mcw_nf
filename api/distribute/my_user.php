<?php
require_once('global.php');
// $level_id = 1;

/**
 * 返回下级用户user_id
 * @param  [int] $invite_user_id 上级用户userid
 * @param  int $is_distribute 是否为分销商
 * @return [array]                 [description]
 */
function getSonUserID($invite_user_id, $is_distribute = 1) {
	global $UsersID, $DB;
	
	$rows = [];

	$DB->Get('user', 'User_ID', "WHERE Users_ID='" . $UsersID . "' AND Owner_Id IN (" . implode(',', $invite_user_id) . ")");
	while($row = $DB->fetch_assoc()) {
		$rows[] = $row['User_ID'];
	}

	return $rows;
}

// $level_config = $level_id;
// $posterity_tem = $accountObj->getPosterity($rsConfig['Dis_Level']);
// $posterity = $accountObj->getPosterity($level_config);
// $posterity_count = $posterity->count();
// $posterity_list = $accountObj->getLevelList($level_config);
// $posterity_list = array_slice($posterity_list, 0, $rsConfig['Dis_Mobile_Level'],TRUE);
// //获取此分销账户佣金情况
// echo '<pre>';
// print_r($posterity);


$level = $dis_config['Dis_Level'];
$result = $user_id = [$_SESSION[$UsersID . 'User_ID']];
for ($i = 0; $i < $level; $i++) {
	$dataUserID = getSonUserID($user_id);
	if (empty($dataUserID)) {
		break;
	} else {
		$result = array_merge($result, $dataUserID);
		$user_id = $dataUserID;
	}
}

$DB->Get('user', 'User_ID, User_NickName, User_Mobile, User_HeadImg, User_CreateTime', "WHERE Users_ID='" . $UsersID . "' AND Owner_Id IN (" . implode(',', $result) . ")");
$users = [];
while ($row = $DB->fetch_assoc()) {
	$users[] = $row;
}



//查看当前登录用户的未读消息
$countMessage = array();
$DB->Get('distribute_account_message', 'count(*) as num,User_ID', ' WHERE `Receiver_User_ID` = '.$User_ID.' AND `Mess_Status`=0 group by User_ID');
while($r = $DB->fetch_assoc()){
	$countMessage[$r['User_ID']] = $r['num'];
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

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
<style type="text/css">
 .last_list_line ul { display: block;
    margin: 0;
    padding: 0; }
  .last_list_line li { display: block;
    overflow: hidden;
    background: #FFF;
    width: 100%;
    text-indent: 20px;
    height: 50px;
    line-height: 50px;
    margin: 5px 0 15px 0; }
    .last_list_line li span {     float: right;
    margin-right: 20px; }
    .imgs a { display: block; overflow: hidden; background: #FFB90F; color: #FFF; padding: 3px 5px; font-size: 12px; border-radius: 3px; }
	.dislist .message_tips{display:block; position:absolute; height:22px; line-height:22px; top:30px; right:8px; color:red}
	.dislist .message_send{display:block; position:absolute; height:22px; line-height:22px; right:5px; color:#FFF; background:#1fb4f8; padding:3px 6px; border-radius:5px; top:40px; }
	.sxl_x {
    vertical-align: super;
    color: #fff;
    background: #ff0000;
    border-radius: 50%;
    width: 15px;
    height: 15px;
    font-size: 10px;
    text-align: center;
    position: absolute;
    right: 6px;
    top:25px;
}
</style>
</head>

<body>
<?php if ($rsAccount['Is_Audit'] == 1): ?>
<div class="wrap">
	<?php if ($rsAccount['status']): ?>

  	 	<!-- 我的分销商列表begin -->

		<div class="wdtdt_x">
		 <a href="javascript:void(0);" class="item_group_title">
			 <span class="fllpt_x l"><img src="/static/api/shop/skin/default/images/td_x.png" width="23px" height="23px"/></span>
			 <span class="fltt_x l ">我的会员
			 <a class="tdst_x"></a></span>
		 </a>
		</div>
      <div class="last_list_line">       
          <?php
  if (!empty($users)) {

       foreach ($users as $user) {
 
             $user['User_HeadImg'] = !empty($user['User_HeadImg']) ? $user['User_HeadImg'] : '/static/api/images/user/face.jpg';
          ?>
		  <div class="dislist" style="position:relative">
		  <div class="img">
		  <img class="hd_img" src="<?php echo $user['User_HeadImg'] ?>"/>
		  </div>
		  <div class="imgli l">
		  <dd>
		  <span>
		  <?php
if ($user['User_NickName']) {
	echo $user['User_NickName'];
} else {
	echo '未填写';
}
		  ?>
		  </span>
		  <span></span>
		  </dd>
		  <dd>
			手机号：<?php echo empty(handle_getuserinfo($UsersID, $user['User_ID'])) ? '' : handle_getuserinfo($UsersID, $user['User_ID']);?>
		  </dd>
		  <dd style="color:#999;"><?php echo empty( $user['User_CreateTime']) ? '' : ldate( $user['User_CreateTime']); ?></dd>
		  </div>
		  <div class="l">			
			<a href="/api/<?=$UsersID?>/distribute/my_message/<?= $user['User_ID']?>/?love" class="message_send">发送消息			
			</a>
			<?php if(!empty($countMessage[ $user['User_ID']])) { ?>
				<a href="/api/<?=$UsersID?>/distribute/my_message/<?= $user['User_ID']?>/?love" class="sxl_x"><?php echo $countMessage[$user['User_ID']]; ?></a>				
			<?php } ?>			
		  </div>
		  </div>		  			
          <?php 
          	}
	}
          ?>
     
      </div>
	</div>
  		<!-- 我的分销商列表end -->


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