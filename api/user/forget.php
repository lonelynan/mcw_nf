<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}


//登录中状态
if (isset($_SESSION[$UsersID."User_ID"]) && !empty($_SESSION[$UsersID."User_ID"]) && (strpos($HTTP_REFERER, 'login') === false)) {
	header('Location:' . $HTTP_REFERER);
}

//获取来源网站owner_id
$shop_config = shop_config($UsersID);
$dis_config = dis_config($UsersID);
//合并参数
$shop_config = array_merge($shop_config,$dis_config);
$owner = get_owner($shop_config,$UsersID);
$dis_config_limit_result = $DB->GetRs('distribute_config','Distribute_Limit','where Users_ID="'.$UsersID.'"');


if(empty($_SESSION[$UsersID."HTTP_REFERER"])){
	$HTTP_REFERER="/api/".$UsersID."/user/";
}else{
	$HTTP_REFERER=$_SESSION[$UsersID."HTTP_REFERER"];
}

$item = $DB->GetRs("user_config","ExpireTime","where Users_ID='".$UsersID."'");
$expiretime = $item["ExpireTime"];

$rsConfig=$DB->GetRs("user_profile","*","where Users_ID='".$UsersID."'");
if ($_POST) {
	//手机验证码校验开始
	$smsMobileKey = isset($_POST['Mobile']) ? 'forget' . $_POST['Mobile'] : '';

	//发送手机验证码
	if (isset($_POST['act']) && ($_POST['act'] == 'send') && isset($_POST['Mobile']) && $_POST['Mobile']) {
		//检查手机号是否为注册用户
		$row = $DB->GetRs('user', '*', "WHERE Users_ID='".$UsersID."' AND User_Mobile='" . $_POST['Mobile'] . "'");
		if (empty($row)) {
			$Data = array(
				"status"=> 0,
				"msg"=>"手机号未注册本站用户"
			);
			echo json_encode($Data, JSON_UNESCAPED_UNICODE);
			exit;
		}


		$code = sprintf("%'.04d", rand(0, 9999));
		$_SESSION[$smsMobileKey] = json_encode([
				'code' => $code,
				'time' => time() + 120,	//120秒
			]);

		require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
		$message = "手机验证码为：" . $code . "。120秒内有效，过期请重新获取。" ;
		$success = send_sms($_POST['Mobile'], $message, $UsersID);
		if ($success) {
			$Data = array(
				"status"=> 1,
				"msg"=>"短信发送成功"
			);
		} else {
			$Data = array(
				"status"=> 0,
				"msg"=>"短信发送失败"
			);
		}

		echo json_encode($Data, JSON_UNESCAPED_UNICODE);
		exit;	
	}
	
	//校验
	$captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';
	if (!isset($_SESSION[$smsMobileKey]) || empty($_SESSION[$smsMobileKey]) || empty($captcha)) {
		$Data = array(
			"status" => 0,
			"msg" => "手机验证码错误"
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	
	} else {
		$cacheData = json_decode($_SESSION[$smsMobileKey], true);
		if ( ($cacheData['code'] != $captcha) || ($cacheData['time'] < time()) ) {
			$Data = array(
				"status" => 0,
				"msg" => "手机验证码错误",
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	}
	//手机验证码校验结束

	$password = isset($_POST['Password']) ? $_POST['Password'] : '';
	$ConfirmPassword = isset($_POST['ConfirmPassword']) ? $_POST['ConfirmPassword'] : '';

	if (strlen($password) < 6) {
		$Data = [
			"status" => 0,
			"msg" => "密码长度至少6位",
		];
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}

	if ($password != $ConfirmPassword) {
		$Data = [
			"status" => 0,
			"msg" => "两次密码不一致",
		];
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;		
	}

	$data = [
		"User_Password" => md5($_POST['Password']),
	];
	$DB->Set("user", $data, "WHERE User_Mobile='" . $_POST['Mobile'] . "'");

	$Data=array(
		"status" => 1,
		"msg" => "修改密码成功，请使用新密码登录！",
		"url" => '/api/' . $UsersID . '/user/login/',
	);

	
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
}
if(!empty($_SESSION[$UsersID."User_ID"]))
{
	header("location:/api/".$UsersID."/shop/member/");
}
if(!strpos($_SERVER['REQUEST_URI'],"mp.weixin.qq.com")){
	header("location:?wxref=mp.weixin.qq.com");
}
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
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/js/user.js'></script>
</head>

<body>
<style type="text/css">
html, body{background:#f3f3f3;}
</style>
<script language="javascript">$(document).ready(user_obj.user_create_init);</script>
<form action="?" method="post" id="user_forget_form">
  <!--<h1>会员注册</h1>
  <div class="input">
    <input type="tel" name="Mobile" value="" maxlength="11" placeholder="手机号码" pattern="[0-9]*" notnull />
  </div>
  <div class="input">
    <input type="password" name="Password" value="" maxlength="16" placeholder="登录密码" notnull />
  </div>
  <div class="input">
    <input type="password" name="ConfirmPassword" value="" maxlength="16" placeholder="确认密码" notnull />
  </div>-->
  <div class="w">
       <div class="zhuce_x">找回密码</div>
       <div class="bz_x">1.输入手机号 > 2.输入验证码 > 3.输入新密码</div>
       <div class="box_x">
            <input tabIndex=1 type="tel" name="Mobile" id="Mobile" value="" maxlength="11"  class="dll_x" placeholder="请输入手机号码" pattern="[0-9]*" notnull >
            <span class="l yzm1_x"><input tabIndex=2 id="captcha" name="captcha" maxlength="4"  pattern="[0-9]*" type="text" class="yzm2_x" placeholder="请输入短信验证码" notnull></span>
            <span class="l yzm_x"><a href="javascript:;" id="btn_send" state="0" style="text-decoration:none;">获取验证码</a></span>
            <div class="clear"></div>
            <input tabIndex=3 type="password" name="Password" value="" maxlength="16"  class="dll_x" placeholder="请设置新的账户密码" notnull >
            <input tabIndex=4 type="password" name="ConfirmPassword" maxlength="16"  value=""  class="dll_x" placeholder="请确认新的账户密码" notnull >                    
  <div class="submit zc_x secret_xx">
    <input name="submit" class="tx_x" type="submit" class="submit" value="修改密码" />
  </div>

<script type="text/javascript">

	var sec = 120;
	function jishi() {
		sec--;
		if (sec == 0) {
			$("#btn_send").html('获取验证码').attr('state', '0');
			sec = 120;
		} else {
			$("#btn_send").html(sec + ' 秒').attr('state', '1');
			setTimeout('jishi()', 1000);	
		}
		
	}
$(function(){
	$("#btn_send").click(function(){
		var state = $(this).attr("state");
		if (state == "1") return false;

		var Mobile = $("#Mobile").val();
		if (Mobile == '') {
			$("#Mobile").focus();
			alert("请输入手机号");
			return false;
		}

		var url = '?wxref=mp.weixin.qq.com';
		$.post(url, {act: 'send', Mobile:Mobile}, function(json) {
			if (json.status == "0") {
				alert(json.msg);
				return false;
			} else {
				$("#btn_send").html("60秒").attr('state', '1');
				jishi();
			}
		}, 'json')

	})

	//提交
	$('#user_forget_form .submit input').click(function(){
		if(global_obj.check_form($('*[notnull]'))){return false};
		
		$(this).attr('disabled', true);
		$.post('?', $('#user_forget_form').serialize(), function(data){
			if (data.status==1) {
				alert(data.msg);
				window.location=data.url;
				
			}else{
				global_obj.win_alert(data.msg, function(){
					$('#user_forget_form .submit input').attr('disabled', false)
				});
			};
		}, 'json');
	});	
})	



</script>            


  </div>
  </div>
</form>
</body>
</html>
<?php exit;?>