<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/level3.php');
ini_set('display_errors','On');
$posterity_tem = $accountObj->getPosterity($rsConfig['Dis_Level']);

$b = $DB->query("select a.`User_ID`,a.`User_NickName`,a.`User_Mobile`,a.`User_Level`,a.`User_HeadImg`,b.`Level_ID` from `user` a inner join `distribute_account` b on a.`User_ID` = b.`User_ID` and b.`invite_id` = '".$User_ID."'");
$row = $DB->fetch_assoc($b);

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
	.dislist .message_send{display:block; position:absolute; height:22px; line-height:22px; top:65px; right:8px; color:#FFF; background:#EEC900; padding:3px 10px; border-radius:5px}
</style>
</head>

<body>
<?php if ($rsAccount['Is_Audit'] == 1): ?>
<div class="wrap">
	<?php if ($rsAccount['status']): ?>
    	<div class="container">

      	<div class="row">
         <div class="distribute_header">
      <div id="header_cushion">
     
      		<div id="account_info" style="width:100%">
            	<div class="pull-left" style="width:25%">
                	  	 <img id="hd_image" src="<?=$show_logo?>"/>
                </div>
            
                <div class="pull-right" style="width:75%;">
                	<ul id="txt" style="padding-left:0px;">
                      <li>昵称：<?=$show_name?>　　ID：<?php echo $rsAccount['User_ID']; ?></li>
                      <li style="padding-right:3px;">分销级别：<?php echo empty($dis_level[$rsAccount['Level_ID']]) ? '' : $dis_level[$rsAccount['Level_ID']]['Level_Name']; ?> 【<a style="color:#FFF;" href="<?=distribute_url('upgrade/')?>">升级</a>】</li>
                      <li style="padding-right:3px;">
                        <?php if(strlen($rsUser['User_Mobile'])==0): ?> 
                           <a style="color:#4878C6;text-decoration:underline;line-height:20px;" href="<?=distribute_url('bind_mobile/')?>">&nbsp;【绑定手机】</a>
                        <?php else:?>
                           手机：<?php echo $rsUser['User_Mobile'];?>　　<a style="color:#4878C6;line-height:20px;" href="<?=distribute_url('change_bind/')?>">&nbsp;【更改手机】</a>
                        <?php endif;?>
                      </li>
                  
                    </ul>
                </div>
                
           		<div class="clearfix">
                </div>
            </div>
      </div>
	 
      <div id="account_sum">	
      	    <a href="javascript:void(0)"><span><?=$total_sales?></span><br>累计销售额</a>
   			    <a href="javascript:void(0)"><span><?=$total_income?></span><br>累计佣金</a>
            <div class="clearfix"></div>
      </div>      
      </div>
    </div>
   	    <div class="clearfix"></div>
  		</div>

  	 	<!-- 我的分销商列表begin -->
    	<div class="list_item" id="posterity_list">

	<div class="dline"></div>
      <div class="last_list_line">       
          <?php if (!empty($row)){?>
          <?php foreach ($row as $key => $value){ ?>
          <?php
              $vsshow_name = !empty($value['User_NickName']) ? $value['User_NickName'] : '暂无';
              $vsShop_Logo = !empty($value['User_HeadImg']) ? $value['User_HeadImg'] : '/static/api/images/user/face.jpg';
          ?>
		  <div class="dislist" style="position:relative">
		  <div class="img">
		  <img class="hd_img" src="<?=$vsShop_Logo?>"/>
		  </div>
		  <div class="imgli">
		  <dd>昵称：
		  <?php
		  //$vsshow_name = "及时答复后就开始放好久开始了对方考虑就分开来决定是否";
		  $Real_Name_Num = strlen($vsshow_name);
		  if($Real_Name_Num >8){
			$Real_Name = mb_substr($vsshow_name,0,6,'utf-8');
			echo $Real_Name."..."; 
		  }else{
		    echo $vsshow_name;
		  }?>
		  &nbsp;&nbsp;ID：<?=$value['User_ID']?></dd>
		  <dd>分销级别：<?php echo empty($dis_level[$value['Level_ID']]) ? '' : $dis_level[$value['Level_ID']]['Level_Name']; ?></dd>
		  <dd>手机：<?php echo empty($value['User_Mobile']) ? '' : $value['User_Mobile'];?></dd>
		  </div>
		<?php if(!empty($countMessage[$value['User_ID']])): ?>
			<a href="/api/<?=$UsersID?>/distribute/my_message/<?=$value['User_ID']?>/?love" class="message_tips">未读消息：<?php echo $countMessage[$value['User_ID']]; ?></a> 
		<?php endif; ?>
			<a href="/api/<?=$UsersID?>/distribute/my_message/<?=$value['User_ID']?>/?love" class="message_send">发送消息</a>
		  </div>		  			
          <?php } ?>
          <?php } ?>       
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