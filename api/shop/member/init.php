<?php
require_once('global.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

if (!isset($rsAccount['Shop_Logo'])) {
	$rsAccount['Shop_Logo'] = '';
}

$userid = $_SESSION[$UsersID.'User_ID'];

//检查用户信息是否已经完善
$user = $DB->GetRs('user', 'User_NickName, User_Mobile, User_HeadImg, User_PayPassword, Is_Distribute', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid));
if ($user['Is_Distribute'] == 0) {
	//普通用户不使用此页面
	header('Location:' . '/api/' . $UsersID . '/shop/member/setting/?love');
}
if (empty($user['User_Name']) || empty($user['User_Mobile']) || empty($user['User_HeadImg']) || empty($user['User_PayPassword'])) {

} else {
	header('Location:' . '/api/' . $UsersID . '/shop/member/setting/?love');
}

if($_POST){

	//验证码登录
	$act = isset($_POST['act']) ? $_POST['act'] : '';

	if ($act == 'send') {

		//手机验证码校验开始
		$smsMobileKey = isset($_POST['Mobile']) ? $_POST['Mobile'] : '';
		$smsMobileKey = 'bind' . $smsMobileKey;

		//发送手机验证码
		if (isset($_POST['act']) && ($_POST['act'] == 'send') && isset($_POST['Mobile']) && $_POST['Mobile']) {

			//检查手机号是否已注册
			$mobile = $_POST['Mobile'];
			$user = $DB->GetRs('user', '*', "WHERE Users_ID='".$UsersID."' AND User_Mobile='" . $mobile . "' AND User_ID!=".$userid);
			if ($user) {
				//手机号已被占用
				$data = [
					'status' => 0,
					'msg' => '手机号已绑定用户，请更换一个手机号'
				];
				
				echo json_encode($data);
				exit();
			}
			unset($user);

			//发送验证码
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
					"msg"=>"短信发送失败",
				);
			}

			echo json_encode($Data, JSON_UNESCAPED_UNICODE);
			exit;	
		}

	} else if ($act == 'bind') {

		$mobile = isset($_POST['Mobile']) ? $_POST['Mobile'] : '';
		$captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';
		
		//手机号非法
		require_once($_SERVER["DOCUMENT_ROOT"] . '/include/support/sysurl_helpers.php');
		if (!is_mobile($mobile)) {
			$data = [
				'status' => 0,
				'msg' => '手机号格式非法',
			];
			echo json_encode($data);
			exit();
		}

		//验证码
		$smsMobileKey = 'bind' . $mobile;
		if (!isset($_SESSION[$smsMobileKey]) || empty($_SESSION[$smsMobileKey]) || empty($captcha)) {
			$Data = array(
				"status" => 0,
				"msg" => "手机验证码错误",
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit();
		} else {
			$dbSmsMobileKey = json_decode($_SESSION[$smsMobileKey], true);
			if ((time() > $dbSmsMobileKey['time']) || ($dbSmsMobileKey['code'] != $captcha)) {
				$Data = array(
					"status" => 0,
					"msg" => "手机验证码错误",
				);
				echo json_encode($Data,JSON_UNESCAPED_UNICODE);
				exit();
			}
		}

		if (isset($_SESSION[$smsMobileKey])) {
			unset($_SESSION[$smsMobileKey]);
		}
				
		//检查手机号是否已注册
		$user = $DB->GetRs('user', '*', "WHERE Users_ID='".$UsersID."' AND User_Mobile='" . $mobile . "' AND User_ID!=".$userid);
		if ($user) {
			//用户已存在
			$data = [
				'status' => 0,
				'msg' => '手机号已绑定用户，请更换一个手机号'
			];
		} else {
			//保存手机号
			$data = [
				'User_Mobile' => $mobile,
			];
			$DB->Set('user', $data, "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid));

			$data = [
				'status' =>1,
				'msg' => '绑定手机成功'
			];
		}

		echo json_encode($data);
		exit();

	} else if ($act == 'dosubmit') {
		$payword = $_POST['paypwd'];
		$nickname = $_POST['nickname'];

		//昵称
		if (empty($nickname)) {
			$data = [
				'status' => 0,
				'msg' => '用户昵称不能为空'
			];
			echo json_encode($data);
			exit();			
		} else {
			//设置昵称
			$data = [
				'User_NickName' => $nickname,
			];
			$DB->Set('user', $data, "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid));
			
			$user = $DB->GetRs('user', '*', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . $userid);
			if($user['Is_Distribute']){
				$DB->Set('distribute_account', ['Shop_Name' => $nickname . '的店'], "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid));
				$rsAccount = $DB->GetRs('distribute_account', '*', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid));
				define('UPLOAD_DIR', $_SERVER["DOCUMENT_ROOT"].'/data/avatar/');
				put_file_from_url_content($rsAccount["Shop_Logo"],$UsersID.$user['Owner_Id'].'.jpg',UPLOAD_DIR);
			}
		}

		//支付密码
		if (empty($payword) || (strlen($payword) < 6) ) {
			$data = [
				'status' => 0,
				'msg' => '支付密码长度至少为六位字符'
			];
			echo json_encode($data);
			exit();			
		} else {
			//设置密码
			$data = [
				'User_PayPassword' => md5($payword),
			];
			$DB->Set('user', $data, "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid));
		}

		//绑定手机号
		$row = $DB->GetRs('user', 'User_Mobile, User_HeadImg', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid) );

		if (empty($row['User_Mobile'])) {
			$data = [
				'status' => 0,
				'msg' => '未绑定手机号'
			];
			echo json_encode($data);
			exit();	
		}

		//用户头像
		//if (empty($row['User_HeadImg'])) {
		if (empty($rsAccount['Shop_Logo'])) {
			$data = [
				'status' => 0,
				'msg' => '未上传用户头像'
			];
			echo json_encode($data);
			exit();	
		}

		//保存成功
		$data = [
			'status' => 1,
			'msg' => '保存成功',
		];
		echo json_encode($data);
		exit();					

	}
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
<title>设置</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/user.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/cart.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/shop/js/shop.js'></script>
<script type='text/javascript' src='/static/api/js/user.js'></script>
<script language="javascript">$(document).ready(shop_obj.page_init);</script>
<style>
img#avatar{width:60px; height:60px; border-radius: 60px;}
</style>
</head>
<body>
<div id="cart">
   <div class="cart_title"><a href="../../../distribute/?love"><b></b></a>完善资料</div>
	<div class="sz_x">
		<ul>
			<li>
				<form action="/api/distribute/upmobile.php?TableField=headimg&UsersID=<?php echo $UsersID;?>" method="post" enctype="multipart/form-data" target="_self">
					<div class="head_default"><img id="avatar" src="<?php echo $rsAccount["Shop_Logo"] ? $rsAccount["Shop_Logo"] : '/static/api/images/user/face.jpg'?>"/></div>
					<div style="display:block; width:100%; text-align:center;color:#999; font-family:'宋体'; font-size:12px; line-height:35px;">注：头像尺寸100*100 (gif, jpg, jpeg, png)</div>
					<div style="position:relative; width:80%; height:34px; margin:0px auto">
						<div style="width:100%; height:34px; padding:0px; margin:0px; font-size:14px; line-height:34px; text-align:center; color:#FFF; cursor:pointer; background:#3396FE; border:none">上传头像</div>
						<input type="file" style="position:absolute; top:0; left:0; height:34px; filter:alpha(opacity:0);opacity: 0;width:100%; cursor:pointer" name="upthumb" onchange="this.form.submit();" />
					</div>
					<div class="clear"></div>
				</form>            
			 </li>		

			 <li style="padding:0px;">
				<span class="flt_btn l "><span style=" width:45px; float:left; text-align:center; margin-left:5px;">昵称：</span><input name="nickname" id="nickname" placeholder="昵称" class="btn_modify" value="<?php echo $user['User_NickName'];?>" />
				<div class="clear"></div>
			 </li>
			 <li class="modify_mobile">
				<span class="flt_x l">绑定手机号：<?php echo $user['User_Mobile'] ? $user['User_Mobile'] : '未绑定';?></span>
				<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="22px" height="22px"></span>
				<div class="clear"></div>
			 </li>
			 <li style="padding:0px;">
				<span class="flt_btn l"><span style=" width:75px; float:left; text-align:center; margin-left:5px;">支付密码：</span><input name="paypwd" id="paypwd" placeholder="六位密码" maxlength="6" type="password" class="btn_password" /></span>
			 </li>
		</ul>
		<div style=" width:80%; margin:0 auto;"><input type="submit" value="确认修改" class="btn_confirm" id="submit-btn"></div>
	</div>
</div>

<!--修改密码div-->
<style>
#btn_send{text-align: center; color:#666; font-size: 12px; width:29.9%; height:35px; line-height:35px; border:1px #f4f4f4 solid; border-right:none; background:#f8f8f8; border-radius:0; float:left; margin:8px 0;}
</style>
<div class="pop_form" id="modify_mobile_div" style="display:none; wdith:100%;">
  <form id="modify_mobile_form" style=" margin:3%; padding:2%;">
	<input type="hidden" name="act" id="act" value="bind">   
    <h1>绑定手机号</h1>
    <input type="User_Mobile" name="Mobile" id="Mobile" class="input" value="" placeholder="11位手机号" notnull  style=" width:100%; height:35px; line-height:35px; border:1px #f4f4f4 solid; padding:0; background:#f8f8f8; border-radius:0"/>
    <span><input type="Ran_Code" name="captcha" id="captcha" class="input" value="" placeholder="手机验证码" maxlength="4" notnull  style=" width:69%; height:35px; line-height:35px; border:1px #f4f4f4 solid; padding:0; background:#f8f8f8; border-radius:0; float:left;" /></span>
    <span id="btn_send" state="0">获取验证码</span>
    <div class="btn" style="width:100%; padding:0; border:none;">
      <span style="float:left; width:55%;text-align:center; margin-left:3%;"><input type="button" class="btnMobileLogin sub_modify" value="提 交" /></span>
      <span style="float:left; width:25%;text-align:center; margin-left:10%;"><input type="button" class="sub_qx cancel" value="取 消" style=" border:none; width:100%; border-radius:0; line-height:34px; height:34px; background:#999;"/></span>
      <div class="clear"></div>
    </div>
  </form>
</div>
<script type="text/javascript">

function isMobile(mobile) {
	if(!/^(13[0-9]|147|15[0-9]|17[0-9]|18[0-9])\d{8}$/i.test(mobile)) {
		return false;
	}
	return true;
}


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
	$(document).on('click', '.btnMobileLogin', function(){
		var Mobile = $("#Mobile").val();
		var captcha = $("#captcha").val();

		if (Mobile == '' || !isMobile(Mobile)) {
			$("#Mobile").focus();
			global_obj.win_alert("请输入正确的手机号", function(){});			
			return false;
		}
		if (captcha.length != 4) {
			global_obj.win_alert("请填写四位验证码", function(){});			
			return false;
		}

		var data = $("#modify_mobile_form").serialize();
		$.post('', data, function(json) {
			if (json.status == 1) {
				global_obj.win_alert(json.msg, function(){
							global_obj.div_mask(1);
							$('#modify_mobile_div').hide();
							$('#modify_mobile_form .btnMobileLogin').attr('disabled', false);
							$('#modify_mobile_form input[name="Mobile"], #modify_mobile_form input[name="captcha"]').val('');
							$(".modify_mobile .flt_x").html('绑定手机号：' + Mobile);

						});

			} else {
				global_obj.win_alert(json.msg, function(){
							$('#modify_mobile_form .btnMobileLogin').attr('disabled', false);
						});
			}
		}, 'json');
	
	})

	$("#modify_mobile_div").on('click', '#btn_send', function() {
		var state = $(this).attr("state");
		if (state == "1") return false;
		if (state == "2") return false;

		var Mobile = $("#Mobile").val();
		if (Mobile == '' || !isMobile(Mobile)) {
			$("#Mobile").focus();
			global_obj.win_alert("请输入正确的手机号", function(){});

			return false;
		}

		var btn = $(this);
		var url = '?wxref=mp.weixin.qq.com';
		$(this).attr('state', '2');
		$.post(url, {act: 'send', Mobile:Mobile}, function(json) {
			if (json.status == "0") {
				global_obj.win_alert(json.msg, function(){});
				return false;
			} else {
				btn.html("120秒").attr('state', '1');
				jishi();
			}
		}, 'json')

	})

	//下方提交按钮
	$("#submit-btn").click(function(){
		var nickname = $("#nickname").val();
		var paypwd = $("#paypwd").val();
		$.post('', {'nickname': nickname, 'paypwd': paypwd, 'act': 'dosubmit'}, function(json) {

			if (json.status == 1) {
				global_obj.win_alert(json.msg, function(){
							location.href = '../../../distribute/?love';
						});

			} else {
				global_obj.win_alert(json.msg, function(){
							
						});
			}
		}, 'json');
	})
})	

</script>	
<!--//修改密码div -->

<script language="javascript">$(document).ready(user_obj.my_init);</script>

</body>
</html>