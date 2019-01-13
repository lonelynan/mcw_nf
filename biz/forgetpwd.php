<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if ($_POST) {
 
    
    
    $act = isset($_POST['act']) ? $_POST['act'] : '';
    if ($act == 'send') {
        if(empty($_POST['Mobile'])){
            $Data=array(
                    'status'=>0,
                    'msg'=>'请填写手机号'
            );
            echo json_encode(empty($Data)?array('status'=>0,'msg'=>'请勿非法操作！'):$Data,JSON_UNESCAPED_UNICODE);
            exit;
        }
        $bizInfo = $DB->GetRs('biz', '*', "WHERE Biz_Account='" . $_POST['Mobile'] . "'");
        if (empty($bizInfo)) {
            $Data = array(
                    "status" => 0,
                    "msg" => "未找到该手机号",
            );
            echo json_encode($Data,JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        //手机验证码校验开始
        $smsMobileKey = isset($_POST['Mobile']) ? $_POST['Mobile'] : '';
        $smsMobileKey = 'forget' . $smsMobileKey;

        //发送手机验证码
        if (isset($_POST['act']) && ($_POST['act'] == 'send') && isset($_POST['Mobile']) && $_POST['Mobile']) {
                $code = sprintf("%'.04d", rand(0, 9999));
                $_SESSION[$smsMobileKey] = json_encode([
                                'code' => $code,
                                'time' => time() + 1200,	//120秒
                        ]);

                require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
                $message = "手机验证码为：" . $code . "。120秒内有效，过期请重新获取。" ;
                $success = send_sms($_POST['Mobile'], $message, $bizInfo['Users_ID']);
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
    } 
        
    if ($act == 'reset') {
        //手机号非法
        include '../include/support/sysurl_helpers.php';
        $mobile = isset($_POST['Mobile']) ? $_POST['Mobile'] : '';
        if (!is_mobile($mobile)) {
                $data = [
                        'status' => 0,
                        'msg' => '手机号格式非法',
                ];
                echo json_encode($data);
                exit();
        }
                                
        if(empty($_POST['passwd']) || empty($_POST['repasswd'])){
            $Data=array(
                    'status'=>0,
                    'msg'=>'请填写密码'
            );
            echo json_encode(empty($Data)?array('status'=>0,'msg'=>'请填写密码！'):$Data,JSON_UNESCAPED_UNICODE);
            exit;
        }
        if($_POST['passwd'] != $_POST['repasswd']){
            $Data=array(
                    'status'=>0,
                    'msg'=>'两次密码输入不一致'
            );
            echo json_encode(empty($Data)?array('status'=>0,'msg'=>'请填写密码！'):$Data,JSON_UNESCAPED_UNICODE);
            exit;
        }
        if(empty($_POST['code'])){
            $Data=array(
                    'status'=>0,
                    'msg'=>'请填写短信验证码'
            );
            echo json_encode(empty($Data)?array('status'=>0,'msg'=>'请填写短信验证码！'):$Data,JSON_UNESCAPED_UNICODE);
            exit;
        }
        //验证码
        $smsMobileKey = 'forget' . $mobile;
		if (!isset($_SESSION[$smsMobileKey]) || empty($_SESSION[$smsMobileKey])) {
			
			$Data = array(
                        "status" => 0,
                        "msg" => "手机验证码错误",
            );
            echo json_encode($Data,JSON_UNESCAPED_UNICODE);
            exit();	
		}		
		$smsMobileKeys = json_decode($_SESSION[$smsMobileKey],true);
		 
        if ($smsMobileKeys['code'] != $_POST['code']) {
                $Data = array(
                        "status" => 0,
                        "msg" => "手机验证码错误",
                );
                echo json_encode($Data,JSON_UNESCAPED_UNICODE);
                exit();
        } else {
                unset($_SESSION[$smsMobileKey]);
        }
         
        $bizInfo = $DB->GetRs('biz', '*', "WHERE Biz_Account='" . $mobile . "'");
        if (empty($bizInfo)) {
            $Data = array(
                    "status" => 0,
                    "msg" => "未找到该手机号",
            );
            echo json_encode($Data,JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $data = [
		"Biz_PassWord" => md5($_POST['passwd']),
	];
	$row = $DB->Set("biz", $data, "WHERE Biz_Account='" . $_POST['Mobile'] . "'");

        if ($row) {
            $Data=array(
                    'status'=>1,
                    'url'=>'/biz/login.php',
                    'msg'=>'修改密码成功，请使用新密码登录！！'
            );
            echo json_encode(empty($Data)?array('status'=>0,'msg'=>'请勿非法操作！'):$Data,JSON_UNESCAPED_UNICODE);
            exit;

        }
    }
        
} else {
     
 
}

?>

<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $SiteName;?>商家后台</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type="text/javascript" src="/static/js/jquery.placeholder.1.3.min.js"></script>
</head>

<body>
<link href='style/login.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/biz/js/account.js'></script>
<script language="javascript">$(document).ready(account_obj.login_init);</script>
<div id="login">
	<form id="login" method="post" action="?">
    	<div class="login_form">
            <div class="t" style="background: url('style/forget_t.jpg') no-repeat 33px center"></div>
            <div class="form">
            	<div class="login_msg"></div>
                <div class="rows">
                    <span><input name="mobile" id="mobile" type="text" maxlength="30" value="" autocomplete="off" tabindex="1" placeholder="请输入手机号" notnull /></span>
                    <div class="clear"></div>
                </div>
                <div class="rows" style="padding: 28px 0px 0px 0px">
                    <span style="width:184px;float:left"><input  style="width:172px;float:left" id="code" name="code"  type="password" maxlength="20" value="" autocomplete="off" tabindex="2" placeholder="请输入短信验证码" notnull /></span>
                    <span class="l" style="width: 82px;float:right" ><input style="width: 82px;float:right;cursor:pointer" id="sendsms" type="button" class="tijiao" state="0" value="获取验证码"></span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <span><input type="password" name="passwd" id="passwd" type="text" maxlength="30" value="" autocomplete="off" tabindex="1" placeholder="请输入您的新密码" notnull /></span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <span><input type="password" name="repasswd" id="repasswd" type="text" maxlength="30" value="" autocomplete="off" tabindex="1" placeholder="请再次输入您的新密码" notnull /></span>
                    <div class="clear"></div>
                </div>
                <div class="submit"><input type="button" id="reset" style="background: url('style/xiugai_btn.jpg') no-repeat left top;" value="" name="submit"></div>
 
            </div>
        </div>
	</form>
</div>
<script>
 function isMobile(mobile) {
		if(!/^(13[0-9]|14[0-9]|15[0-9]|18[0-9])\d{8}$/i.test(mobile)) {
			return false;
		}
		return true;
    }
	
	var sec = 120;
	function jishi() {
		sec--;
		if (sec == 0) {
			$("#sendsms").val('获取验证码').attr('state', '0');
                         $("#sendsms").attr('disabled',false);
                         $("#sendsms").css('background-color','#1ba0a3');
			sec = 120;
		} else {
			$("#sendsms").val(sec + ' 秒').attr('state', '1');
			setTimeout('jishi()', 1000);	
		}
	}
    
    $("#sendsms").click(function(){
        var Mobile = $("#mobile").val();
        if (Mobile == '' || !isMobile(Mobile)) {
                $("#Mobile").focus();
                alert("请输入正确的手机号");
                return false;
        }
        
       
        $.post('?', {act: 'send', Mobile:Mobile}, function(json) {
                if (json.status == "0") {
                        alert(json.msg);
                        return false;
                } else {
                     
                        $("#sendsms").val("120秒").attr('state', '1');
                          $("#sendsms").attr('disabled',true);
                          $("#sendsms").css('background-color','#cddcdd');
                        jishi();
                }
        }, 'json')

    })
    
    $("#reset").click(function(){
        var Mobile = $("#mobile").val();
        var code = $("#code").val();
        var passwd = $("#passwd").val();
        var repasswd = $("#repasswd").val();
        if (Mobile == '' || !isMobile(Mobile)) {
                $("#Mobile").focus();
                alert("请输入正确的手机号");
                return false;
        }
        if (passwd == '') {
                alert("请输入登陆密码");
                return false;
        }
        if (code.length != 4) {
                alert('请填写四位验证码');
                return false;
        }
       
        $.post('?', {act: 'reset', Mobile:Mobile,passwd:passwd,repasswd:repasswd,code:code}, function(json){
                if (json.status == 1) {
                       window.location.href = json.url;
                } else {
                        alert(json.msg);
                }
        }, 'json');
    })
</script>
</body>
</html>