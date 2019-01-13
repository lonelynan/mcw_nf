<?php
require_once('global.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/include/compser_library/Protitle.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/compser_library/Salesman.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/level3.php');
$userid = $User_ID;

$level_config = $rsConfig['Dis_Level'];
$posterity = $accountObj->getPosterity($level_config);
$posterity_count = $posterity->count();
$posterity_list = $accountObj->getLevelList($level_config);
$posterity_list = array_slice($posterity_list,0,$rsConfig['Dis_Mobile_Level'],TRUE);
//获取此分销账户佣金情况
$record_list = Dis_Account_Record::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$User_ID,'Record_Type'=>0))
                                   ->get(array('Record_Money','Record_Status','Record_CreateTime'))
								   ->toArray();
								   
 //分销开关关闭不在显示该页面 
if (empty($rsConfig['DisSwitch'])) { 
    exit();
}

//是否为分销商
$user = $DB->GetRs('user', 'User_NickName, User_Mobile, User_HeadImg, User_PayPassword, Is_Distribute', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($User_ID));

$bonus_list = dsaccount_bonus_statistic($record_list);


$show_name = $rsUser['User_NickName'] ? $rsUser['User_NickName'] : '未设置昵称';
$show_logo = !empty($rsUser['User_HeadImg']) ? $rsUser['User_HeadImg'] : '/static/api/images/user/face.jpg';

if (isset($rsAccount) && $rsAccount && $rsConfig['Distribute_Customize'] == 1) {
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

$total_sales = round_pad_zero(get_my_leiji_sales($UsersID,$User_ID,$posterity),2);
				$accountObj->Total_Sales = $total_sales;
				$accountObj->save();
$total_income = round_pad_zero(get_my_leiji_income($UsersID,$User_ID),2);
					 	  
$dis_agent_type = $rsConfig['Dis_Agent_Type'];
$is_agent = is_agent($rsConfig,$rsAccount);

//爵位设置
$pro_titles = Dis_Config::get_dis_pro_title($UsersID);
$show_support = true;

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

//屏蔽后台手动指定股东分红
$f = $DB->GetRs('sha_order', 'count(*) as num', ' where `Users_ID`="'.$UsersID.'" and `User_ID` = '.$User_ID);

//判断分销商是否具有查看申请区域代理的链接
$Agent_Rate = array();
if (isset($dis_config['Agent_Rate']) && !empty($dis_config['Agent_Rate'])) 
{
  $Agent_Rate = json_decode($dis_config['Agent_Rate'], true);
  $Agentenable = $Agent_Rate['Agentenable'];
  unset($Agent_Rate['Agentenable']);
  unset($Agent_Rate['Nameshow']);
}

$user_consue = Order::where(array('User_ID'=>$_SESSION[$UsersID.'User_ID'],'Order_Status'=>4))->sum('Order_TotalPrice');
$user_count = Dis_Account::where('invite_id',$_SESSION[$UsersID.'User_ID'])->count();

$ProTitle = new ProTitle($UsersID, $User_ID);
$childs = $ProTitle->get_sons($dis_config['Dis_Level'],$User_ID);
if(!empty($childs)){
  $Sales_Group = Order::where(array('Order_Status'=>4))->whereIn('Owner_ID',$childs)->sum('Order_TotalPrice');
}else{
  $Sales_Group = 0;
}

$MY_Distribute_Level = $rsAccount['Level_ID'];
$areaStepFlag = '';

foreach ($Agent_Rate as $k => $v) 
{
  $v['Protitle'] = isset($v['Protitle']) ? $v['Protitle'] : 0;
  $v['Level'] = isset($v['Level']) ? $v['Level'] : 0;
  $v['Selfpro'] = isset($v['Selfpro']) ? $v['Selfpro'] : 0;
  $v['Teampro'] = isset($v['Teampro']) ? $v['Teampro'] : 0;
  
  //判断分销商级别是否达到预设值的区域代理等级
  if ($MY_Distribute_Level >= $v['Level'] && $rsAccount['Professional_Title'] >= $v['Protitle'] && $user_consue >= $v['Selfpro'] && $user_count >= $v['Teampro']) 
  {
    $areaStepFlag = $k;
    break;
  }
}

$Sha_Rate = NULL;
$enable = 0;
if (isset($dis_config) && !empty($dis_config['Sha_Rate'])) 
{
  $Sha_Rate = json_decode($dis_config['Sha_Rate'], true);
  $enable = $Sha_Rate['Shaenable'];
  unset($Sha_Rate['Shaenable']);
}

$roleFlag = '';
if ($enable) 
{
  $roleFlag = 'open';
}else {
  if (!empty($Sha_Rate)) 
  {
    foreach ($Sha_Rate as $k => $v) 
    {
      $v['Protitle'] = isset($v['Protitle']) ? $v['Protitle'] : 0;
      $v['Level'] = isset($v['Level']) ? $v['Level'] : 0;
      $v['Selfpro'] = isset($v['Selfpro']) ? $v['Selfpro'] : 0;
      $v['Teampro'] = isset($v['Teampro']) ? $v['Teampro'] : 0; 
      //判断分销商级别是否达到预设值的区域代理等级
      //if ($MY_Distribute_Level >= $v['Level'] && $rsAccount['Professional_Title'] >= $v['Protitle'] && $user_consue >= $v['Selfpro'] && $user_count >= $v['Teampro']) 
	  if ($MY_Distribute_Level >= $v['Level'] && $rsAccount['Professional_Title'] >= $v['Protitle'] && $user_consue >= $v['Selfpro'] && $Sales_Group  >= $v['Teampro']) 	  
      {
        $roleFlag = $k;
        break;
      }
    }

  }
}
$Fanxian_Count = $DB->GetRs('distribute_fanben_record','count(*) as Fanxian_Count','where Users_ID="' . $UsersID . '" and User_ID=' . $User_ID)['Fanxian_Count'];
$first = $DB->GetRs('distribute_order','*','where Users_ID="'.$UsersID.'" and User_ID='.$User_ID.' AND Order_Present_type = 1  order by Order_PayTime asc');
$Present_type_level = $DB->GetRs('distribute_level','Present_type','where Users_ID="'.$UsersID.'"  order by Level_CreateTime asc');
if(empty($first)){
	if($Present_type_level['Present_type'] == 1){
		$firsts = true;
	}else{
		$firsts = false;
	}
}else{
	$firsts = true;
}

//短消息
$sql = "SELECT COUNT(*) AS total FROM `user_message` WHERE Users_ID='" . $UsersID . "' AND Message_ID NOT IN (SELECT Message_ID FROM user_message_record WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid) . ")";
$query = $DB->query($sql);
$msg = $DB->fetch_assoc();
$messageTotal = (int)$msg['total'];

require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/defaut_switch.php');

//模块开关的开启
$perm_config = array();
$rs = $DB->Get('permission_config','*','where Users_ID="'.$UsersID.'"  AND Is_Delete = 0 AND Perm_Tyle = 1  order by Permission_ID asc');
while($rs = $DB->fetch_assoc()){
	$perm_config[] = $rs;
}
$shaInfo = $DB->GetRs('sha_order', 'count(Order_ID) as num', ' where `Users_ID`="' .$UsersID. '" AND `User_ID`="' .$User_ID. '" AND `Order_Status`=2');

//业务员
$salesman = new Salesman($UsersID,$User_ID);
$limit = $salesman->up_salesman();
$is_salesman = $salesman->get_salesman();

$realtwo['Update_switch'] = 0;
				if (count($dis_level) > 1) {
					$realtwo = next($dis_level);
				}
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>分销中心</title>
	<link href="/static/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" href="/static/css/font-awesome.css">
    <link href="/static/api/distribute/css/style.css" rel="stylesheet">
    <link href="/static/api/distribute/css/distribute_center.css?t=1" rel="stylesheet">
    <script src="/static/js/jquery-1.11.1.min.js"></script>
	<script src="/static/api/distribute/js/distribute.js"></script>
    <script type="text/javascript">
		$(document).ready(function(){
			distribute_obj.init();
		});
    </script>
</head>
<body>
<?php if ($rsAccount['Is_Audit'] == 1): ?>
<div class="wrap">
	<?php if ($rsAccount['status']): ?>
	<div class="w">
	     <div class="fxbj_x">
		 <div class="unottitle">ID：<?=$rsAccount['Account_ID']?></div>
			 <span class="baob_x r"><a href="/api/<?=$UsersID?>/user/message/"><img src="/static/api/distribute/images/xiaoxi_X.png" width="25" height="25">
			 <?php if ($messageTotal > 0) {?><span class="sl_x"><?php echo $messageTotal;?></span><?php } ?>
			 </a></span>
			 <div class="clear"></div>
			 <span class="flk_x l">
				<a href="/api/<?=$UsersID?>/distribute/edit_headimg/"><img id="hd_image" src="<?=$show_logo?>" /></a>
				<?php if (empty($show_logo)) {?>
					<p style=" background:#b5b5b5; width:40px;font-size:10px; color:#fff; margin:0 auto; text-align:center;">未设置</p>
				<?php } ?> 
			 </span>
			 <span class="flx_x l">
			 <span><?=$show_name?></span><span style="display:none;">ID：<?php echo $rsAccount['User_ID']; ?></span><br>
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
	    <div class="clear"></div>
		<div class="jine_x">
			 <ul class="jine1_x" style="    -webkit-padding-start: 0;margin-bottom: 0;">
				<li style="border-right: 1px solid #fff;"><a href="javascript:void(0)">累计销售额（元）<p class="jiage_x"><?=$total_sales?></p></a></li>
				<li><a href="javascript:void(0)">累计<?php echo !empty($Index_Professional_Json['catcommission'])?$Index_Professional_Json['catcommission']:'佣金'; ?>（元）<p class="jiage_x"><?=$total_income?></p></a></li>
			 </ul>
		</div>		
		<div class="clear"></div>
		<?php foreach($perm_config as $key=>$value) {?>
			<?php if($value['Perm_Field'] == 'withdraw' && $value['Perm_On'] == 0) {?>
				<div style="background:#fff; overflow:hidden; padding:10px 0;">
					<span class="l" style=" text-align:left; text-indent:1em; width:60%;">
						<div class="flt_x">可提现<?php echo !empty($Index_Professional_Json['catcommission'])?$Index_Professional_Json['catcommission']:'佣金'; ?>(元)</div>
						<div class="flt1_x"><?=round_pad_zero($rsAccount['balance'],2)?></div>
					</span>
					<span class="l" style=" text-align:center;width:40%;">
						<a href="/api/<?=$UsersID?>/distribute/withdraw/"><span id='withdraw_btn' class="tixian_x r" link="/api/<?=$UsersID?>/distribute/withdraw/">提现</span></a>
					</span>
				</div>
			<?php }?>
		<?php }?>
	 <div class="clear"></div>
   <div class="fenlei_x">
         <ul class="fenlei1_x"  style="    -webkit-padding-start: 0;margin-bottom: 0;">
			<?php foreach($perm_config as $key=>$value) {?>
				<?php if($value['Perm_Field'] == 'barcode' && $value['Perm_On'] == 0) {?>
					<li>
						<a href="/api/<?=$UsersID?>/distribute/popularize/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php }?>
				<?php if($value['Perm_Field'] == 'team' && $value['Perm_On'] == 0) {?>
					<li>
						<a href="/api/<?=$UsersID?>/distribute/team/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php }?>
				<?php if($value['Perm_Field'] == 'finance' && $value['Perm_On'] == 0) {?>
					<li>
						<a href="/api/<?=$UsersID?>/distribute/detaillist/all/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php }?>
				<?php if($value['Perm_Field'] == 'financenobi' && $value['Perm_On'] == 0) {?>
					<li>
						<a href="/api/<?=$UsersID?>/distribute/detaillist_nobi/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php }?>
				<?php if($value['Perm_Field'] == 'knighthood' && $value['Perm_On'] == 0) {?>
					<li>
						<a href="/api/<?=$UsersID?>/distribute/pro_title/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php }?>
				<?php if($value['Perm_Field'] == 'salesman' && $value['Perm_On'] == 0) {?>
					<?php if ($is_salesman) {?>
						<li>
							<a href="/api/<?=$UsersID?>/distribute/sales/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
						</li>
					<?php }else{ ?>
						<li>
							<a href="/api/<?=$UsersID?>/distribute/sales/join/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br>成为创始人</a>
						</li>					
				<?php }} ?>
				<?php if($value['Perm_Field'] == 'area_agency' && $value['Perm_On'] == 0) {?>
					<?php if(!empty($areaStepFlag) || isset($Agentenable)) { ?>
						<li>
							<a href="/api/<?=$UsersID?>/distribute/area_proxy/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
						</li>
					<?php } ?>
				<?php }?>
				<?php if($value['Perm_Field'] == 'shareholder' && $value['Perm_On'] == 0) {?>
					<?php if ($rsConfig['Sha_Agent_Type'] == 1 || $shaInfo['num'] > 0) {?>
						<li>
							<a href="/api/<?=$UsersID?>/distribute/sha/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
						</li>
					<?php } ?>
				<?php }?>
				<?php if($value['Perm_Field'] == 'wealth' && $value['Perm_On'] == 0) {?>
					<li>
						<a href="/api/<?=$UsersID?>/distribute/income_list/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php }?>
				<?php if($value['Perm_Field'] == 'get_product' && $value['Perm_On'] == 0) {?>
					<?php if($firsts){?>
						<li>
							<a href="/api/<?=$UsersID?>/distribute/get_product/1/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
						</li>
					<?php } ?>
				<?php }?>
				<?php if($value['Perm_Field'] == 'custom' && $value['Perm_On'] == 0) {?>
					<li>
						<a href="/api/<?=$UsersID?>/distribute/edit_shop/"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php }?>
				<?php if($rsConfig["Distribute_Type"] != 1){ ?>
				<?php if ($realtwo['Update_switch']) { ?>
				<?php if($value['Perm_Field'] == 'grade' && $value['Perm_On'] == 0) { ?>
					<li>
						<a href="<?=distribute_url('upgrade/')?>"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php } } } ?>
				<?php if($value['Perm_Field'] == 'gather' && $value['Perm_On'] == 0) {?>
					<li>
						<a href="/api/<?=$UsersID?><?=$value['Perm_Url']?>"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php }?>
			<?php }?>
			<?php foreach($perm_config as $key=>$value) {?>
				<?php if($value['Perm_Field'] == '0' && $value['Perm_On'] == 0) {?>
					<li>
						<a href="<?=$value['Perm_Url']?>"><img src="<?=$value['Perm_Picture']?>" width="25" height="25"><br><?=$value['Perm_Name']?></a>
					</li>
				<?php }?>
			<?php }?>
         </ul>
     </div>
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
</div>
<?php
//微信分享
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_jssdk.class.php');
$weixin_jssdk = new weixin_jssdk($DB,$UsersID);
$share_config = $weixin_jssdk->jssdk_get_signature();
if (!empty($share_config)) {
	$share_config['url'] = str_replace("/distribute/", "/distribute/join/", $share_config['url']);	
}
?>
<?php require_once '../shop/skin/distribute_footer.php';?>
<!-- 强制完善资料 -->
<?php //昵称、手机号、支付密码
if (empty($rsUser['User_NickName']) || empty($rsUser['User_Mobile']) || empty($rsUser['User_PayPassword'])) {?>
	<script type="text/javascript">
	$(function(){
		 $('.wrap a').attr("href", "/api/<?=$UsersID?>/shop/member/init/?love");
	})

	</script>
<?php }?>
<!-- 强制完善资料 -->


</body>
</html>