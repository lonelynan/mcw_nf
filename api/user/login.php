<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
	$rsweixin=$DB->GetRs("Users","*","where Users_ID='".$UsersID."'");
}else{
	echo '缺少必要的参数';
	exit;
}

if(empty($_SESSION[$UsersID."HTTP_REFERER"])){
	$HTTP_REFERER="/api/".$UsersID."/shop/member/?love";
}else{
	//$HTTP_REFERER="/api/".$UsersID."/shop/union/?love";
	$HTTP_REFERER=$_SESSION[$UsersID."HTTP_REFERER"].'?love';
}

//登录中状态
if (isset($_SESSION[$UsersID."User_ID"]) && !empty($_SESSION[$UsersID."User_ID"]) && (isset($HTTP_REFERER) && (strpos($HTTP_REFERER, 'login') === false))) {
	header('Location:' . $HTTP_REFERER);
}
//$user_url = '/api/'.$UsersID.'/user/';
if(isset($_GET['OrderID'])){
  $user_url = "/api/" . $UsersID . "/user/0/create/" . $_GET['OrderID'] . "/";
}else{
	$user_url = "/api/" . $UsersID . "/user/0/create/";
}


$rsConfig = shop_config($UsersID);
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig,$dis_config);
$owner = get_owner($rsConfig,$UsersID);


if($owner['id'] != '0'){
	//$user_url = $user_url.$owner['id'].'/';
	if(isset($_GET['OrderID'])){
		$user_url = "/api/" . $UsersID . "/user/" . $owner['id'] . "/create/" . $_GET['OrderID'] . "/";
	}else{
		$user_url = "/api/" . $UsersID . "/user/" . $owner['id'] . "/create/";
	}
}

if(empty($_SESSION[$UsersID."User_ID"])){
	

	if(empty($_SESSION[$UsersID."OpenID"])){
		$_SESSION[$UsersID."OpenID"]=0;
	}
	
	$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_OpenID='".$_SESSION[$UsersID."OpenID"]."'");
	
	if($rsUser){
	
		$_SESSION[$UsersID."User_ID"]=$rsUser["User_ID"];
		$_SESSION[$UsersID."User_Name"]=$rsUser["User_Name"];
		$_SESSION[$UsersID."User_Mobile"]=$rsUser["User_Mobile"];
		$_SESSION[$UsersID."HTTP_REFERER"]="";
		
		header("location:".$HTTP_REFERER);
		exit;
	}else{

		if($_POST){

			//验证码登录
			$act = isset($_POST['act']) ? $_POST['act'] : '';

			if ($act == 'send') {

				//手机验证码校验开始
				$smsMobileKey = isset($_POST['Mobile']) ? $_POST['Mobile'] : '';
				$smsMobileKey = 'login' . $smsMobileKey;

				//发送手机验证码
				if (isset($_POST['act']) && ($_POST['act'] == 'send') && isset($_POST['Mobile']) && $_POST['Mobile']) {
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


			} else if ($act == 'login') {

				$mobile = isset($_POST['Mobile']) ? $_POST['Mobile'] : '';
				$captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';
				
				//手机号非法
				include '../../include/support/sysurl_helpers.php';
				if (!is_mobile($mobile)) {
					$data = [
						'status' => 0,
						'msg' => '手机号格式非法',
					];
					echo json_encode($data);
					exit();
				}

				//验证码开始
				$smsMobileKey = 'login' . $mobile;
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
				//验证码结束
						
				//检查手机号是否已注册
				$user = $DB->GetRs('user', '*', "WHERE Users_ID='".$UsersID."' AND User_Mobile='" . $mobile . "'");
				if ($user) {
					//用户已存在
					$_SESSION[$UsersID."User_ID"] = $user["User_ID"];
					$_SESSION[$UsersID."User_Name"] = $user["User_Name"];
					$_SESSION[$UsersID."User_Mobile"] = $user["User_Mobile"];

				} else {
					//注册新用户					
						$User_No = "2";					

					$data = [
						'Users_ID' => $UsersID,
						'User_Mobile' => $mobile,
						'User_CreateTime' => time(),
						'User_Status' => 1,
						'User_No' => $User_No,
						"User_PayPassword" => md5('123456'),
						"User_Password" => '',						
					];


		//推荐人
		if (isset($_SESSION[$UsersID."OwnerID"])) {
			$rsUseroot = $DB->GetRs("user", "Root_ID,Owner_ID", "WHERE Users_ID='".$UsersID."' AND User_ID='".$_SESSION[$UsersID."OwnerID"]."'");
			$root_id = $rsUseroot['Root_ID'] == 0 ? $_SESSION[$UsersID."OwnerID"] : $rsUseroot['Root_ID'];

			$data["Owner_ID"] = $_SESSION[$UsersID . "OwnerID"];
			$data["Root_ID"] = $root_id;
			unset($rsUseroot);
		}

					$DB->Add("user", $data);
					$userid = $DB->insert_id();
					$_SESSION[$UsersID."User_ID"] = $userid;
					$_SESSION[$UsersID."User_Name"] = '';
					$_SESSION[$UsersID."User_Mobile"] = $mobile;

					//更新openid
					$OpenID = md5(session_id() . $_SESSION[$UsersID."User_ID"]);
					$userno = '600000' + $userid;
					$DB->Set("user",array('User_OpenID' => $OpenID, 'User_No'=>$userno), 'WHERE Users_ID="'.$UsersID.'" AND User_ID='.$userid);
				}
			

				$data = [
					'status' => 1,
					'url' => $HTTP_REFERER,
				];
				echo json_encode($data);
				exit();
			}


			//普通登陆
			if(empty($_POST["Mobile"])){
				echo '<script language="javascript">alert("手机号码必须填写！");history.back();</script>';
				exit();
			}
			if(empty($_POST["Password"])){
				echo '<script language="javascript">alert("登录密码必须填写！");history.back();</script>';
				exit();
			}
			$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_Mobile='".$_POST["Mobile"]."'");
			if($rsUser){
				if (!empty($_POST['OrderID'])) {
					$Order_Data = $DB->GetRs("distribute_order","*","where Users_ID='" . $UsersID . "' and User_ID=" . $rsUser["User_ID"]);
					if(empty($Order_Data)){
						$rsUsers = $DB->GetRs('user','Owner_Id','where User_ID=' . $rsUser["User_ID"]);
						$Data = array(
							'User_ID'=>$rsUser["User_ID"],
							'Owner_ID'=>$rsUsers['Owner_Id'],
						);
						$DB->Set("distribute_order",$Data,'where Order_ID=' . intval($_POST['OrderID']));
						$HTTP_REFERER = '/api/'.$UsersID.'/distribute/complete_pay/money/'.$_POST['OrderID'].'/';
					}
					
				}
				if(md5($_POST["Password"])==$rsUser["User_Password"]){
					if($rsUser["User_Status"]){
						$_SESSION[$UsersID."User_ID"]=$rsUser["User_ID"];
						$_SESSION[$UsersID."User_Name"]=$rsUser["User_Name"];
						$_SESSION[$UsersID."User_Mobile"]=$rsUser["User_Mobile"];
						$_SESSION[$UsersID."HTTP_REFERER"]="";

						//如果是砍价来源的url	
						if(isset($_SESSION[$UsersID.'is_kanjia'])){
							$HTTP_REFERER .= $_SESSION[$UsersID."User_ID"].'/';
						}

						header("location:".$HTTP_REFERER);
						exit;
					}else{
						echo '<script language="javascript">alert("帐号锁定，禁止登录！");history.back();</script>';
						exit();
					}
				}else{
					echo '<script language="javascript">alert("登录密码错误！");history.back();</script>';
					exit();
				}
			}else{
				echo '<script language="javascript">alert("手机号码不存在！请重新输入！");history.back();</script>';
				exit();
			}
		}
	}
}

if(!strpos($_SERVER['REQUEST_URI'],"mp.weixin.qq.com")){
	header("location:?wxref=mp.weixin.qq.com");
	exit;
}

//开户QQ三方登录
$QQLoginRow = $DB->GetRs('third_login_config', '*', "WHERE type='qq' AND users_id='" . $UsersID . "'");
$QQLogin = $QQLoginRow && ($QQLoginRow['state'] == 1) ? true : false;
unset($QQLoginRow);
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
<style type="text/css">html, body{background:#f3f3f3;}</style>
<script language="javascript">//$(document).ready(user_obj.user_login_init);</script>

  <!--<div class="tips">您还未登录，请先登录！</div>
  <h1>没有帐号</h1>
  <div class="reg"><a href="<?=$user_url?>create/">注册只需10秒</a></div>
  <h1>已有帐号</h1>
  <div class="input">
    <input type="tel" name="Mobile" value="" maxlength="11" placeholder="手机号码" pattern="[0-9]*" notnull />
  </div>
  <div class="input">
    <input type="password" name="Password" value="" maxlength="16" placeholder="登录密码" notnull />
  </div>
  <div class="submit">
    <input name="提交" type="submit" value="立即登录" />
  </div>-->
  <div class="w">
<?php
if ($QQLogin) {
?>  
      <div class="tj_x">推荐使用</div>
      
      <div class="qw_x">
         <a href="/login/third/qq/?users_id=<?php echo $UsersID;?>" class="q_x qd1_X qd_x">
		 <img  src="/static/api/images/user/qqi.png" width="16px" height="16px"/>&nbsp;QQ授权登录</a>
		 <!--
		 <a href="/login/third/qq/?users_id=<?php echo $UsersID;?>" class="w_x qd1_X qd_x">
		 <img  src="/static/api/images/user/wxi.png" width="20px" height="20px"/>&nbsp;微信授权登录</a>-->
      </div>
      <div class="xian_x"></div>
<?php
}
?>
	  <div class="wrap">
	        <div class="tab1">
		        <ul class="tab1-hd">
                   <li class="active">手机快捷登录</li>
                   <li>账号密码登录</li>
                </ul>
		        <ul class="tab1-bd">
                   <li class="thisclass">
<form action="" method="post" id="mobile_user_form">   
<input type="hidden" name="act"0 id="act" value="login">                
				      <div class="boxsj_x">
					  <input tabIndex=1 type="tel" name="Mobile" id="Mobile" value="" maxlength="11"  class="xgmm_x" placeholder="请输入手机号码" pattern="[0-9]*" notnull >
            <span class="l xgmm1_x"><input tabIndex=2 id="captcha" name="captcha" maxlength="4"  pattern="[0-9]*" type="text" class="xgmm2_x" placeholder="请输入短信验证码" notnull></span>
			<input name="OrderID" type="hidden"  value="<?php echo !empty($_GET['OrderID']) ? $_GET['OrderID'] : '' ;?>">
            <span class="l xgmm3_x"><a href="javascript:;" id="btn_send" state="0">获取验证码</a></span>			          
					  <input name="提交" type="button" class="dl_x btnMobileLogin" value="立即登录" />
					  </div>
                     <div class="t_x submit" >
                       <span class="r"><a href="<?=$user_url?>">立即注册</a></span>
                    </div>	
</form>		
<script type="text/javascript">

function isMobile(mobile) {
	if(!/^(13[0-9]|17[0-9]|15[0-9]|18[0-9])\d{8}$/i.test(mobile)) {
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
	$(".btnMobileLogin").click(function(){
		var Mobile = $("#Mobile").val();
		var captcha = $("#captcha").val();
 
		if (Mobile == '' || !isMobile(Mobile)) {
			$("#Mobile").focus();
			alert("请输入正确的手机号");
			return false;
		}
		if (captcha.length != 4) {
			alert('请填写四位验证码');
			return false;
		}

		var data = $("#mobile_user_form").serialize();
		$.post('?wxref=mp.weixin.qq.com', data, function(json){
			if (json.status == 1) {
				parent.location.href = json.url;
			} else {
				alert(json.msg);
			}
		}, 'json');
	
	})

	$("#btn_send").click(function(){
		var state = $(this).attr("state");
		if (state == "1") return false;

		var Mobile = $("#Mobile").val();
		if (Mobile == '' || !isMobile(Mobile)) {
			$("#Mobile").focus();
			alert("请输入正确的手机号");
			return false;
		}

		var url = '?wxref=mp.weixin.qq.com';
		$.post(url, {act: 'send', Mobile:Mobile}, function(json) {
			if (json.status == "0") {
				alert(json.msg);
				return false;
			} else {
				$("#btn_send").html("120秒").attr('state', '1');
				jishi();
			}
		}, 'json')

	})
})	

</script>		  
                   </li>

                   <li> 
                   <form action="" method="post" id="user_form">
           <div class="box1_x">
           <input type="tel" name="Mobile" value="" maxlength="11" class="sousuo_x" placeholder="手机号码/用户名/邮件地址" notnull >
           <input type="password" name="Password" value="" maxlength="16" style=" margin-top:10px;" class="sousuo1_x" placeholder="请输入密码" notnull >
		   <input name="OrderID" type="hidden"  value="<?php echo !empty($_GET['OrderID']) ? $_GET['OrderID'] : '' ;?>">
           <!--<a href="#" class="dl_x">立即登陆</a>-->
		   <input name="提交" type="submit" class="dl_x" value="立即登录" />
         <div class="t_x submit" >
               <span class="l"><a href="/api/<?=$UsersID?>/user/forget/">忘记密码？</a></span>
               <span class="r"><a href="<?=$user_url?>">立即注册</a></span>
         </div>
      </div>
      </form>
                   </li>

                </ul>
	        </div>   
         </div>
</div>

       <script>
$(function(){
    function tabs(tabTit,on,tabCon){
        $(tabTit).children().click(function(){
            $(this).addClass(on).siblings().removeClass(on);
            var index = $(tabTit).children().index(this);
           	$(tabCon).children().eq(index).show().siblings().hide();
    	});
	};
    tabs(".tab1-hd","active",".tab1-bd");
});
</script>


</body>
</html>