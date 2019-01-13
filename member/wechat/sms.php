<?php
$DB->showErr=false;
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}
if($_POST){
	$reg_enabled = $_POST['reg_enabled'] == 0 ? 0 : 1;
	$user_reg_enabled = $_POST['user_reg_enabled'] == 0 ? 0 : 1;
	
	if (strpos($_POST['sms_sign'], '【') !== false || strpos($_POST['sms_sign'], '】') !== false) {
			$_POST['sms_sign'] = str_replace(array("【","】"),"",$_POST['sms_sign']);
		}
		
	$Data=array(
		"sms_enabled"=>$_POST['sms_enabled'],
		"sms_account"=>$_POST['sms_account'],
		"sms_pass"=>$_POST['sms_pass'],
		"sms_sign"=>$_POST['sms_sign'] ? '【'.$_POST['sms_sign'].'】' : '【'.$SiteName.'】',
		'reg_enabled' => $reg_enabled,
		'user_reg_enabled' => $user_reg_enabled,
	);
	$Flag = $DB->Set("setting",$Data,"where id=1");
	if($Flag){
		echo '<script language="javascript">alert("设置成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
	}else{
		echo '<script language="javascript">alert("设置失败");history.go(-1);</script>';
	}
	exit;
}
$jcurid = 2;

?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
</head>
<body>
<div id="iframe_page">
  <div class="iframe_content">
	<?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/jiebase_menubar.php');?>
    <div class="r_con_wrap">
		<form class="r_con_form" method="post" action="?">
        	
			<div class="rows">
                <label>是否启用</label>
                <span class="input">
                <label><input name="sms_enabled" type="radio" value="1"<?php echo $setting["sms_enabled"]==1 ? " checked" : "";?>>启用</label>&nbsp;&nbsp;
                <label><input name="sms_enabled" type="radio" value="0"<?php echo $setting["sms_enabled"]==0 ? " checked" : "";?>>关闭</label>
                </span>
                <div class="clear"></div>
            </div>
			
            <div class="rows">
            	<label>短信宝平台账号</label>
                <span class="input"><input type="text" name="sms_account" value="<?php echo $setting["sms_account"];?>" size="30" class="form_input" /><span class="tips">&nbsp;
				&nbsp;目前只支持“<font style="color:red">短信宝</font>”</span></span>
                <div class="clear"></div>
            </div>
            <div class="rows">
            	<label>短信宝平台密码</label>
                <span class="input"><input type="text" name="sms_pass" value="<?php echo $setting["sms_pass"];?>" size="30" class="form_input" /></span>
                <div class="clear"></div>
            </div>
             <div class="rows">
            	<label>短信签名</label>
                <span class="input"><input type="text" name="sms_sign" value="<?php echo $setting["sms_sign"];?>" size="30" class="form_input" /><span class="tips">&nbsp;
				&nbsp;例如“<font style="color:red"><?php echo $SiteName;?></font>”</span></span>
                <div class="clear"></div>
            </div>
			<div class="rows">
                <label>注册商城启用</label>
                <span class="input">
                <label><input name="reg_enabled" type="radio" value="1"<?php echo isset($setting["reg_enabled"]) && $setting["reg_enabled"]==1 ? " checked" : "";?>>启用</label>&nbsp;&nbsp;
                <label><input name="reg_enabled" type="radio" value="0"<?php echo isset($setting["reg_enabled"]) && $setting["reg_enabled"]==0 ? " checked" : "";?>>关闭</label>
                </span>
                <div class="clear"></div>
            </div>	
			<div class="rows">
                <label>注册会员启用</label>
                <span class="input">
                <label><input name="user_reg_enabled" type="radio" value="1"<?php echo isset($setting["user_reg_enabled"]) && $setting["user_reg_enabled"]==1 ? " checked" : "";?>>启用</label>&nbsp;&nbsp;
                <label><input name="user_reg_enabled" type="radio" value="0"<?php echo isset($setting["user_reg_enabled"]) && $setting["user_reg_enabled"]==0 ? " checked" : "";?>>关闭</label>
                </span>
                <div class="clear"></div>
            </div>			            
            <div class="rows">
                <label></label>
                <span class="input"><input type="submit" class="btn_green" name="submit_button" value="提交保存" /></span>
                <div class="clear"></div>
            </div>
        </form>
     </div>
  </div>
</div>
</body>
</html>