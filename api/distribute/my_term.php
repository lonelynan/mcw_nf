<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/level3.php');
$level_id = intval($_GET['levelId']);
if (!$level_id) 
{
  exit('缺少必要参数');
}

$level_config = $level_id;
$posterity_tem = $accountObj->getPosterity($rsConfig['Dis_Level']);
$posterity = $accountObj->getPosterity($level_config);
$posterity_count = $posterity->count();
$posterity_list = $accountObj->getLevelList($level_config);
$posterity_list = array_slice($posterity_list, 0, $rsConfig['Dis_Mobile_Level'],TRUE);
//获取此分销账户佣金情况
$record_list = Dis_Account_Record::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$User_ID,'Record_Type'=>0))
                                   ->get(array('Record_Money','Record_Status','Record_CreateTime'))
								   ->toArray();

$bonus_list = dsaccount_bonus_statistic($record_list);

if ($rsConfig['Distribute_Customize'] == 0) {
	$show_name = $rsUser['User_NickName'];
	$show_logo = !empty($rsUser['User_HeadImg']) ? $rsUser['User_HeadImg'] : '/static/api/images/user/face.jpg';
} else {
	$show_name = !empty($rsAccount['Shop_Name']) ? $rsAccount['Shop_Name'] : '暂无';
	$show_logo = !empty($rsAccount['Shop_Logo']) ? $rsAccount['Shop_Logo'] : '/static/api/images/user/face.jpg';
}	

$level_name_list = $yinrulevel;

//判断当前分销账户可否提现并处理
if($rsAccount["Enable_Tixian"] == 0){
	if($rsConfig["Withdraw_Type"]==0){//无限制
		$accountObj->Enable_Tixian = 1;
		$accountObj->save();
	}elseif($rsConfig["Withdraw_Type"]==1){//佣金限制
		if($rsConfig["Withdraw_Limit"]==0){			
			$accountObj->Enable_Tixian = 1;
			$accountObj->save();			
		}else{
			if($rsAccount['Total_Income'] >= $rsConfig["Withdraw_Limit"]){
				$accountObj->Enable_Tixian = 1;
				$accountObj->save();
			}
		}
	}
}

$total_sales = round_pad_zero(get_my_leiji_sales($UsersID,$User_ID,$posterity_tem),2);
$total_income = round_pad_zero(get_my_leiji_income($UsersID,$User_ID),2);

//首页自定义设置 edit in 2016.3.24
$Index_Professional_Json = $rsConfig['Index_Professional_Json'] ? json_decode($rsConfig['Index_Professional_Json'], TRUE) : array();

$count_posterity_list = 0;
//edit in 2016.3.24

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
		<?php if (!empty($posterity_list)) : ?>
			<?php foreach ($posterity_list as $key => $sub_list): ?>
				<?php if (count($sub_list) > 0 && $key == $level_id): ?>
				<?php  $count_posterity_list = count($sub_list); ?>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<div class="wdtdt_x">
		 <a href="javascript:void(0);" class="item_group_title">
			 <span class="fllpt_x l"><img src="/static/api/shop/skin/default/images/td_x.png" width="23px" height="23px"/></span>
			 <span class="fltt_x l "><?php echo empty($Index_Professional_Json['childlevelterm'][$level_config]) ? $level_name_list[$level_config] : $Index_Professional_Json['childlevelterm'][$level_config]; ?>&nbsp;&nbsp;
			 <a class="tdst_x">(<?php echo $count_posterity_list; ?>)</a></span>
		 </a>
		</div>
      
      <div class="last_list_line">       
          <?php if (!empty($posterity_list)) : ?>
          <?php foreach ($posterity_list as $key => $sub_list): ?>
          <?php if (count($sub_list) > 0 && $key == $level_id): ?>
          <?php foreach ($sub_list as $k => $v): ?>
          <?php
              // print_r($v);
              $vsshow_name = !empty($v->Shop_Name) ? $v->Shop_Name : '暂无';
              $vsShop_Logo = !empty($v->Shop_Logo) ? $v->Shop_Logo : '/static/api/images/user/face.jpg';
          ?>
		  <div class="dislist" style="position:relative">
		  <div class="img">
		  <img class="hd_img" src="<?=$vsShop_Logo?>"/>
		  </div>
		  <div class="imgli l">
		  <dd>
		  <span>
		  <?php
		  //$vsshow_name = "及时答复后就开始放好久开始了对方考虑就分开来决定是否";
		  $Real_Name_Num = strlen($vsshow_name);
		  if($Real_Name_Num >8){
			$Real_Name = mb_substr($vsshow_name,0,6,'utf-8');
			echo $Real_Name."..."; 
		  }else{
		    echo $vsshow_name;
		  }?>
		  </span>
		  <span><?php echo empty($dis_level[$v['Level_ID']]) ? '' : $dis_level[$v['Level_ID']]['Level_Name']; ?></span>
		  <span><?php echo empty($dis_level[$v['Level_ID']]) ? '' : $dis_level[$v['Level_ID']]['Level_Name']; ?>(<?php $sql="select count(*) from distribute_account where invite_id=".$v['User_ID']; $a=mysql_query($sql);$b= mysql_fetch_array($a);echo $b[0];?>位下级)</span>
		  </dd>
		  <dd>
			手机号：<?php echo empty(handle_getuserinfo($UsersID,$v['User_ID'])) ? '' : handle_getuserinfo($UsersID,$v['User_ID']);?>
		  </dd>
		  <dd style="color:#999;"><?php echo empty($v['Account_CreateTime']) ? '' : ldate($v['Account_CreateTime']); ?></dd>
		  </div>
		  <?php if ($_GET['levelId'] == 1) {?>
				<div class="l">			
					<a href="/api/<?=$UsersID?>/distribute/my_message/<?=$v['User_ID']?>/?love" class="message_send">发送消息			
					</a>
					<?php if(!empty($countMessage[$v['User_ID']])): ?>
						<a href="/api/<?=$UsersID?>/distribute/my_message/<?=$v['User_ID']?>/?love" class="sxl_x"><?php echo $countMessage[$v['User_ID']]; ?></a>				
					<?php endif; ?>			
				</div>
		  <?php }?>
		 
		  </div>		  			
          <?php endforeach; ?>
          <?php endif; ?>
          <?php endforeach; ?>
          <?php endif; ?>       
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