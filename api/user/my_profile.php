<?php require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}
$rsConfig = shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfigs = array_merge($rsConfig,$dis_config);


$TypeID=empty($_GET["TypeID"])?0:$_GET["TypeID"];	
if(isset($_GET['OpenID'])){
	$_SESSION[$UsersID.'OpenID']=$_GET['OpenID'];
	header("location:/api/".$UsersID."/user/my/profile/".(empty($TypeID)?'':$TypeID.'/')."?wxref=mp.weixin.qq.com");
	exit;
}else{
	if(empty($_SESSION[$UsersID.'OpenID'])){
		$_SESSION[$UsersID.'OpenID']=session_id();
	}
}
if(!strpos($_SERVER['REQUEST_URI'],"mp.weixin.qq.com")){
	header("location:?wxref=mp.weixin.qq.com");
}
$rsConfig=$DB->GetRs("user_config","*","where Users_ID='".$UsersID."'");
$rsProfile=$DB->GetRs("user_profile","*","where Users_ID='".$UsersID."'");
$UserLevel=json_decode($rsConfig['UserLevel'],true);
if(isset($_SESSION[$UsersID."User_ID"])){
	$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]);
}else{
	$_SESSION[$UsersID."HTTP_REFERER"]="/api/".$UsersID."/user/my/profile/";
	header("location:/api/".$UsersID."/user/login/?wxref=mp.weixin.qq.com");
	exit;
}
$distribute_account = $DB->GetRs("distribute_account","Shop_Logo","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>会员中心</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/user.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/style.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/js/user.js'></script>
</head>

<body>
<div id="my_header">
	<div class="face">
		<?php if ($rsConfigs['Distribute_Customize'] == 0) {?>
			<img src="/static/api/images/user/face.jpg" />
		<?} else { ?>
			<?php echo !empty($distribute_account['Shop_Logo']) ? '<img src="'.$distribute_account['Shop_Logo'].'" />' :'' ; ?>
		<?php } ?>
	</div>
  <ul>
    <li><?php echo $rsUser["User_Name"] ?>【<?php echo $UserLevel[$rsUser["User_Level"]]["Name"] ?>】</li>
    <!--<li>余额:￥1.25</li>-->
    <li>现有积分: <?php echo $rsUser["User_Integral"] ?>分</li>
    <li>总获积分: <?php echo $rsUser["User_TotalIntegral"] ?>分</li>
  </ul>
</div>
<script language="javascript">$(document).ready(user_obj.user_profile_init);</script>
<div id="my">
  <form id="user_form">
    <div class="profile">修改我的资料</div>
    <div class="input">
      <input type="text" name="Name" value="<?php echo $rsUser['User_Name'] ?>" maxlength="10" placeholder="您的姓名(必填)" notnull />
    </div>
      <div class="input">
          <input type="text" name="NickName"  value="<?php echo $rsUser['User_NickName'] ?>" maxlength="10" placeholder='昵称(必填)'<?php echo empty($rsProfile['Profile_NickNameNotNull'])?'':' notnull' ?> />
          <?php if($rsProfile['Profile_IDNum']){ ?><input type="text" name="IDNum" value="<?php echo $rsUser['User_IDNum'] ?>" maxlength="18" placeholder='身份证号(必填)'<?php echo empty($rsProfile['Profile_IDNumNotNull'])?'':' notnull' ?> /><?php }?>
          <?php if($rsProfile['Profile_Telephone']){ ?><input type="text" name="Telephone"  value="<?php echo $rsUser['User_Telephone'] ?>" maxlength="15" placeholder='电话(必填)' pattern="[0-9]*"<?php echo empty($rsProfile['Profile_TelephoneNotNull'])?'':' notnull' ?> /><?php }?>
          <?php if($rsProfile['Profile_Fax']){ ?><input type="text" name="Fax"  value="<?php echo $rsUser['User_Fax'] ?>" maxlength="15" placeholder='传真(必填)' pattern="[0-9]*"<?php echo empty($rsProfile['Profile_FaxNotNull'])?'':' notnull' ?> /><?php }?>
          <?php if($rsProfile['Profile_QQ']){ ?><input type="text" name="QQ"  value="<?php echo $rsUser['User_QQ'] ?>" maxlength="11" placeholder='QQ(必填)' pattern="[0-9]*"<?php echo empty($rsProfile['Profile_QQNotNull'])?'':' notnull' ?> /><?php }?>
          <?php if($rsProfile['Profile_Email']){ ?><input type="text" name="Email"  value="<?php echo $rsUser['User_Email'] ?>" maxlength="30" placeholder='邮箱(必填)'<?php echo empty($rsProfile['Profile_EmailNotNull'])?'':' notnull' ?> /><?php }?>
          <?php if($rsProfile['Profile_Company']){ ?><input type="text" name="Company"  value="<?php echo $rsUser['User_Company'] ?>" maxlength="30" placeholder='公司(必填)'<?php echo empty($rsProfile['Profile_CompanyNotNull'])?'':' notnull' ?> /><?php }?>
          <?php if($rsProfile['Profile_Gender']){ ?><select name="Gender" class="w_33" placeholder='性别(必填)'<?php echo empty($rsProfile['Profile_GenderNotNull'])?'':' notnull' ?>>
        <option value="">--性别--</option>
        <option value="保密"<?php echo $rsUser['User_Gender']=='保密'&&$rsUser['User_Gender']!=''?' selected':'' ?>>保密</option>
        <option value="男"<?php echo $rsUser['User_Gender']=='男'?' selected':'' ?>>男</option>
        <option value="女"<?php echo $rsUser['User_Gender']=='女'?' selected':'' ?>>女</option>
      </select><?php }?>
      <?php if($rsProfile['Profile_Age']){ ?><select name="Age" class="w_33" placeholder='年龄(必填)'<?php echo empty($rsProfile['Profile_AgeNotNull'])?'':' notnull' ?>>
        <option value="">--年龄--</option>
        <?php for($i=10;$i<=80;$i++){
			echo '<option value="'.$i.'"'.($i==$rsUser['User_Age']?' selected':'').'>'.$i.'</option>';
		}?>
      </select><?php }?>
    </div><?php if($rsProfile['Profile_Birthday']){ 
	$birdayarr = explode('-', $rsUser['User_Birthday']);
	?>
    <div class="input">
      <select name="BirthdayY" class="w_33" placeholder='生日(必填)'<?php echo empty($rsProfile['Profile_BirthdayNotNull'])?'':' notnull' ?>>
        <option value="">--生日(年)--</option>
        <?php for($i=1950;$i<=date('Y');$i++){
			echo '<option value="'.$i.'"'.(empty($rsUser['User_Birthday'])?'':($i==$birdayarr[0]?' selected':'')).'>'.$i.'</option>';
		}?>
      </select>
      <select name="BirthdayM" class="w_33" placeholder='生日(必填)'<?php echo empty($rsProfile['Profile_BirthdayNotNull'])?'':' notnull' ?>>
        <option value="">--生日(月)--</option>
        <?php for($i=1;$i<=12;$i++){
			echo '<option value="'.$i.'"'.(empty($rsUser['User_Birthday'])?'':($i==$birdayarr[1]?' selected':'')).'>'.$i.'</option>';
		}?>
      </select>
      <select name="BirthdayD" class="w_33" placeholder='生日(必填)'<?php echo empty($rsProfile['Profile_BirthdayNotNull'])?'':' notnull' ?>>
        <option value="">--生日(日)--</option>
		<?php for($i=1;$i<=31;$i++){
			echo '<option value="'.$i.'"'.(empty($rsUser['User_Birthday'])?'':($i==$birdayarr[2]?' selected':'')).'>'.$i.'</option>';
		}?>
      </select>
    </div><?php }?><?php if($rsProfile['Profile_Area']){ ?>
    <script type='text/javascript' src='/static/js/plugin/pcas/pcas.js'></script>
    <div class="input">
      <select name="Province" class="w_33" placeholder='地区(必填)'<?php echo empty($rsProfile['Profile_AreaNotNull'])?'':' notnull' ?>>
      </select>
      <select name="City" class="w_33"  placeholder='地区(必填)'<?php echo empty($rsProfile['Profile_AreaNotNull'])?'':' notnull' ?>>
      </select>
      <select name="Area" class="w_33" placeholder='地区(必填)'<?php echo empty($rsProfile['Profile_AreaNotNull'])?'':' notnull' ?>>
      </select>
      <script language="javascript">new PCAS('Province', 'City', 'Area', '<?php echo $rsUser['User_Province'] ?>', '<?php echo $rsUser['User_City'] ?>', '<?php echo $rsUser['User_Area'] ?>');</script> 
    </div><?php }?><?php if($rsProfile['Profile_Address']){ ?>
    <div class="input">
      <input type="text" name="Address" value="<?php echo $rsUser['User_Address'] ?>" maxlength="30" placeholder='详细地址(必填)'<?php echo empty($rsProfile['Profile_AddressNotNull'])?'':' notnull' ?> />
    </div><?php }?>
	
	<?php
	if(isset($rsProfile['Profile_Input'])){
		$User_Json_Input = !empty($rsUser['User_Json_Input'])?json_decode($rsUser['User_Json_Input'],1):[];
		$Home_Json_Input=json_decode($rsProfile['Profile_Input'],true);
		for($no=0;$no<=4;$no++){
			if(isset($Home_Json_Input[$no]['InputName'])){
	?>
		<input type="text" name="InputValue_<?php echo $no;?>" value="<?=!empty($User_Json_Input[$no]['InputValue'])?$User_Json_Input[$no]['InputValue']:''?>" class=" dll_x" placeholder="<?php echo $Home_Json_Input[$no]['InputValue'];?>" />
	<?php }
		}
	}?>
	<?php
	$arr_value = array();
	if(isset($rsProfile['Profile_Select'])){
		$User_Json_Select = !empty($rsUser['User_Json_Select'])?json_decode($rsUser['User_Json_Select'],1):[];
		$Home_Json_Select=json_decode($rsProfile['Profile_Select'],true);
		for($no=0;$no<=4;$no++){
			if(isset($Home_Json_Select[$no]['SelectName'])){
				$arr_value = explode("|", $Home_Json_Select[$no]['SelectValue']);
	?>
	 <div class="input dll_x2">
		<select name="SelectValue_<?php echo $no;?>" style="margin-bottom:0px; background-color:#f9f9f9;">
		<?php foreach($arr_value as $k=>$v){?>
			<option value='<?php echo $v;?>'  <?=!empty($User_Json_Select[$no]['SelectValue']) && $User_Json_Select[$no]['SelectValue']==$v?"selected":"" ?>><?php echo $v;?></option>
		<?php }?>
		</select>

	<?php 	
			}
		}
	}
	?>

    <div class="submit">
      <input type="button" class="submit_btn" value="提交修改" />
      <a href="/api/<?php echo $UsersID ?>/shop/member/setting/?love" class="cancel">取消</a> </div>
    <input type="hidden" name="action" value="profile" />
  </form>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>