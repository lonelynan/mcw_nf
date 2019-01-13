<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/level3.php');

$Status=empty($_GET["Status"])?0:$_GET["Status"];
$_SESSION[$UsersID."HTTP_REFERER"] = "/api/".$UsersID."/shop/member/";
$rsUser=$DB->GetRs("user", "*", "WHERE User_ID=" . (int)$_SESSION[$UsersID . "User_ID"]);

$userid = $_SESSION[$UsersID."User_ID"];

$num0 = $num1 = $num2 = $num3 = 0;
$r = $DB->GetRs("user_order","count(*) as num","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='shop' and Order_Status=0");
$num0 = $r["num"];
$r = $DB->GetRs("user_order","count(*) as num","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='shop' and Order_Status=1");
$num1 = $r["num"];
$r = $DB->GetRs("user_order","count(*) as num","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='shop' and Order_Status=2");
$num2 = $r["num"];
$r = $DB->GetRs("user_order","count(*) as num","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='shop' and Order_Status=3");
$num3 = $r["num"];
$r = $DB->GetRs("user_order","count(*) as num","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type='shop' and Order_Status=4");
$num4 = $r["num"];
$rsConfig1=$DB->GetRs("user_config","*","where Users_ID='".$UsersID."'");
$LevelName = '普通会员';
if(!empty($rsConfig1["UserLevel"])){
	$level_arr = json_decode($rsConfig1["UserLevel"],true);
	$rsUser["User_Level"] = isset($level_arr[$rsUser["User_Level"]]) ? $rsUser["User_Level"] : 0;
	$LevelName = $level_arr[$rsUser["User_Level"]]["Name"];
	if(!empty($level_arr[$rsUser["User_Level"]])){
		$LevelName = $level_arr[$rsUser["User_Level"]]["Name"];
	}
}

require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/defaut_switch.php');

//模块开关的开启
$perm_config = array();
$rs = $DB->Get('permission_config','*','where Users_ID="'.$UsersID.'" AND Is_Delete = 0 AND Perm_Tyle = 2  order by Permission_ID asc');
while($rs = $DB->fetch_assoc()){
	$perm_config[] = $rs;
}

$show_name = $rsUser['User_NickName'] ? $rsUser['User_NickName'] : '未设置昵称';
$show_logo = !empty($rsUser['User_HeadImg']) && preg_match('/http/',$rsUser["User_HeadImg"]) ? $rsUser["User_HeadImg"] : '/static/api/images/user/face.jpg';

if (isset($rsAccount) && $rsAccount && $rsConfig['Distribute_Customize'] == 1) {
	$show_name = !empty($rsAccount['Shop_Name']) ? $rsAccount['Shop_Name'] : '暂无';
	$show_logo = !empty($rsAccount['Shop_Logo']) && (strpos($rsAccount["Shop_Logo"], 'http') !== false || (strpos($rsAccount["Shop_Logo"], 'http') === false && file_exists($_SERVER['DOCUMENT_ROOT'] . $rsAccount["Shop_Logo"]))) ? $rsAccount['Shop_Logo'] : '/static/api/images/user/face.jpg';
}

//短消息
$sql = "SELECT COUNT(*) AS total FROM `user_message` WHERE Users_ID='" . $UsersID . "' AND Message_ID NOT IN (SELECT Message_ID FROM user_message_record WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid) . ")";
$query = $DB->query($sql);
$msg = $DB->fetch_assoc();
$messageTotal = (int)$msg['total'];

//分销开关关闭显示财富 
if (empty($rsConfig['DisSwitch'])) {
    $accountObj =  Dis_Account::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$User_ID)) ->first();
    if (!empty($accountObj)) {
	$rsAccount = $accountObj->toArray(); 
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
	}
    $total_income = round_pad_zero(get_my_leiji_income($UsersID,$User_ID),2);
    if (!empty($rsConfig['Index_Professional_Json'])){
            $Index_Professional_Json = json_decode($rsConfig['Index_Professional_Json'], TRUE);
    } else {
            $Index_Professional_Json = array(
                    'myterm' => '我的团队',
                    //'childlevelterm' => $level_name_list,
                    'catcommission' => '佣金',
                    'cattuijian' => '我的推荐人',
                    'childtuijian' => $yinruRecommend
            );
    }       
}
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<title>个人中心</title>
 <link href="/static/css/bootstrap.css" rel="stylesheet">

    <link href="/static/api/distribute/css/style.css" rel="stylesheet">
     <link href="/static/api/shop/skin/default/css/member.css?t=<?php echo time();?>" rel="stylesheet">
    <script src="/static/js/jquery-1.11.1.min.js"></script>
	<script type='text/javascript' src='/static/api/js/global.js'></script>
	<script type='text/javascript' src='/static/api/shop/js/shop.js'></script>
	<script language="javascript">
		$(document).ready(shop_obj.page_init);
	</script>
</head>

<body>
<div class="wrap">
      <div class="zbj_x">
	  <div class="unottitle">会员号：<?=$rsUser['User_No']?></div>
		<span class="baob_x r"><a href="/api/<?=$UsersID?>/user/message/"><img src="/static/api/distribute/images/xiaoxi_X.png" width="25" height="25">
			 <?php if ($messageTotal > 0) {?><span class="sll_x"><?php echo $messageTotal;?></span><?php } ?>
			 </a></span>
		<div class="clear"></div>
        <div class="txk">
			<a href="/api/<?=$UsersID?>/distribute/edit_headimg/"><img src="<?php echo $show_logo;?>" /></a>
			<p  class="txt"><?=$LevelName?></p>
		</div>
    </div>
<div class="clear"></div>
    <div class="nav_xx">
		 <ul  style="    -webkit-padding-start: 0;margin-bottom: 0;">
			  <li class="l"><a href="/api/<?=$UsersID?>/shop/member/status/0/"><img src="/static/api/shop/skin/default/images/qr.png" width="25px" height="25px"><?php if(!empty($num0)){?><span class="sl_x"><?=$num0?></span><?php }?><br>待确认</a></li>
			  <li class="l"><a href="/api/<?=$UsersID?>/shop/member/status/1/"><img src="/static/api/shop/skin/default/images/fk.png" width="25px" height="25px"><?php if(!empty($num1)){?><span class="sl_x"><?=$num1?></span><?php }?><br>待付款</a></li>
			  <li class="l"><a href="/api/<?=$UsersID?>/shop/member/status/2/"><img src="/static/api/shop/skin/default/images/fh1.png" width="25px" height="25px"><?php if(!empty($num2)){?><span class="sl_x"><?=$num2?></span><?php }?><br>已付款</a></li>
			  <li class="l"><a href="/api/<?=$UsersID?>/shop/member/status/3/"><img src="/static/api/shop/skin/default/images/fh.png" width="25px" height="25px"><?php if(!empty($num3)){?><span class="sl_x"><?=$num3?></span><?php }?><br>已发货</a></li>
			  <li class="l"><a href="/api/<?=$UsersID?>/shop/member/status/4/"><img src="/static/api/shop/skin/default/images/pj.png" width="25px" height="25px"><?php if(!empty($num4)){?><span class="sl_x"><?=$num4?></span><?php }?><br>已完成</a></li>
		</ul>
	</div>
<div class="clear"></div>
<div class="fll_x">
     <ul style="    -webkit-padding-start: 0;margin-bottom: 0;">
         <li>
             <a href="/api/<?=$UsersID?>/pintuan/user/">
                 <span class="fllp_x l"><img src="/static/api/shop/skin/default/images/pintuan.png" width="25px" height="25px"></span>
                 <span class="flt_x l">拼团中心</span>
                 <span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
             </a>
             <div class="clear"></div>
         </li>

         <?php // print_r($perm_config);?>
		<?php foreach($perm_config as $key=>$value) {?>
			<?php if($value['Perm_Field'] == 'integral' && $value['Perm_On'] == 0) {?>
				<li>
					<a href="/api/<?=$UsersID?>/user/integral/">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="flt1_x l"><?=$rsUser['User_Integral']?>分</span>
						<?php if($rsUser['User_UseLessIntegral']>0): ?>
							&nbsp;&nbsp;不可用(<?=$rsUser['User_UseLessIntegral']?>分)
						<?php endif; ?>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
			<?php if($value['Perm_Field'] == 'balance' && $value['Perm_On'] == 0) {?>
				 <li>
					<a href="/api/<?=$UsersID?>/user/money/">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="flt1_x l"><?=$rsUser['User_Money']?>元</span>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
			<?php if($value['Perm_Field'] == 'oofline_orders' && $value['Perm_On'] == 0) {?>
				 <li>
					<a href="/api/<?=$UsersID?>/offline/member/status/4/">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="flt1_x l">查看详情</span>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
			<?php if($value['Perm_Field'] == 'kanjia' && $value['Perm_On'] == 0) {?>
				 <li>
					<a href="/api/<?=$UsersID?>/user/kanjia_order/status/2/">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="flt1_x l">查看详情</span>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
				<?php if($value['Perm_Field'] == 'cloud' && $value['Perm_On'] == 0) {?>
				 <li>
					<a href="/api/<?=$UsersID?>/cloud/member/status/2/">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="flt1_x l">查看详情</span>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
				<?php if($value['Perm_Field'] == 'pintuan' && $value['Perm_On'] == 0) {?>
				 <li>
					<a href="/api/<?=$UsersID?>/pintuan/orderlist/0/">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="flt1_x l">查看详情</span>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
        
			<?php if($value['Perm_Field'] == 'gift_exchange' && $value['Perm_On'] == 0) {?>
				<li>
					 <a href="/api/<?=$UsersID?>/user/gift/1/">
					   <span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
					   <span class="flt_x l"><?=$value['Perm_Name']?></span>
					   <span class="flt1_x l">更多礼品等你来拿</span>
					   <span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
			<?php if($value['Perm_Field'] == 'coupon' && $value['Perm_On'] == 0) {?>
				<li>
					 <a href="/api/<?=$UsersID?>/user/coupon/1/">
					   <span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
					   <span class="flt_x l"><?=$value['Perm_Name']?></span>
					   <span class="flt1_x l">查看详情</span>
					   <span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<?php if($rsUser['Is_Distribute'] == 1):?>
					<?php endif;?> 
					<div class="clear"></div>
				</li>
			<?php }?>
			<?php if($value['Perm_Field'] == 'address' && $value['Perm_On'] == 0) {?>
				<li>
					<a href="/api/<?=$UsersID?>/user/my/address/">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="flt1_x l">点击修改</span>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			
			<?php }?>
			<?php if($value['Perm_Field'] == 'favorite' && $value['Perm_On'] == 0) {?>
				<li>
					 <a href="/api/<?=$UsersID?>/shop/member/favourite/?love">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="flt1_x l">喜欢的都在这里</span>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
			<?php if($value['Perm_Field'] == 'goods_returned' && $value['Perm_On'] == 0) {?>
				<li>
					 <a href="/api/<?=$UsersID?>/shop/member/backup/status/5/">
					   <span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
					   <span class="flt_x l"><?=$value['Perm_Name']?></span>
					   <span class="flt1_x l">查看详情</span>
					   <span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<?php if($rsUser['Is_Distribute'] == 1):?>
					<?php endif;?> 
					<div class="clear"></div>
				</li>
			<?php }?>
			<?php if($value['Perm_Field'] == 'huanhuo' && $value['Perm_On'] == 0) {?>
				<li>
					 <a href="/api/<?=$UsersID?>/shop/member/myafter_sale/0/?love">
					   <span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
					   <span class="flt_x l">换货单</span>
					   <span class="flt1_x l">查看详情</span>
					   <span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<?php if($rsUser['Is_Distribute'] == 1):?>
					<?php endif;?> 
					<div class="clear"></div>
				</li>
			<?php }?>
			<?php if($value['Perm_Field'] == 'weixiu' && $value['Perm_On'] == 0) {?>
				<li>
					 <a href="/api/<?=$UsersID?>/shop/member/maintain_sale/0/?love">
					   <span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
					   <span class="flt_x l">售后维修</span>
					   <span class="flt1_x l">查看详情</span>
					   <span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<?php if($rsUser['Is_Distribute'] == 1):?>
					<?php endif;?> 
					<div class="clear"></div>
				</li>
			<?php }?>
			<?php if($value['Perm_Field'] == 'settings' && $value['Perm_On'] == 0) {?>
				<li>
					<a href="/api/<?=$UsersID?>/shop/member/setting/?love">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="flt1_x l">完善个人资料</span>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
		<?php }?>
		<?php foreach($perm_config as $key=>$value) {?>
			<?php if($value['Perm_Field'] == '0' && $value['Perm_On'] == 0) {?>
				<li>
					<a href="<?=$value['Perm_Url']?>">
						<span class="fllp_x l"><img src="<?=$value['Perm_Picture']?>" width="25px" height="25px"></span>
						<span class="flt_x l"><?=$value['Perm_Name']?></span>
						<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="25px" height="25px"></span>
					</a>
					<div class="clear"></div>
				</li>
			<?php }?>
		<?php }?>
     </ul>
</div>
</div>
 <?php
 	require_once('../skin/distribute_footer.php');
 ?>
</body>
</html>


