<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
$UA = strtoupper($_SERVER['HTTP_USER_AGENT']);
if(strpos($UA, 'WINDOWS NT') == false) {
			$base_url = base_url();			
			$url = $base_url . 'api/'.$_GET['usersid'].'/shop/sjrzmobile/';
			header('location:' . $url);
		}  
 
if ($_POST) {
 
    if(!empty($_POST["usersid"])){
		$rsUsers = $DB->GetRs("users","*","where Users_ID='".$_POST['usersid']."'");
		if (empty($rsUsers)) {
			echo '<script language="javascript">alert("非法链接");history.back();</script>';
			exit;
		}

		$UsersID = $_POST["usersid"];
 
    }else{
        $Data=array(
            'status'=>0,
            'msg'=>'非法链接'
        );
        echo json_encode(empty($Data)?array('status'=>0,'msg'=>'请勿非法操作！'):$Data,JSON_UNESCAPED_UNICODE);
        exit;
    }
    
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
        if (!empty($bizInfo)) {
            $Data = array(
                    "status" => 0,
                    "msg" => "此手机号已被注册",
            );
            echo json_encode($Data,JSON_UNESCAPED_UNICODE);
            exit();
        }
        
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
    } 
        
    if ($act == 'reg') {
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
                                
        if(empty($_POST['passwd'])){
            $Data=array(
                    'status'=>0,
                    'msg'=>'请填写密码'
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
        $smsMobileKey = 'login' . $mobile;
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
        if (!empty($bizInfo)) {
            $Data = array(
                    "status" => 0,
                    "msg" => "此手机号已被注册",
            );
            echo json_encode($Data,JSON_UNESCAPED_UNICODE);
            exit();
        }
		$rsBizgroup = $DB->GetRs("biz_group","*","where Users_ID='".$_POST['usersid']."' and is_default = 1");
		$Group_ID = isset($rsBizgroup['Group_ID'])?$rsBizgroup['Group_ID']:'0';
        $data = array(
            'Biz_Account' => $_POST['Mobile'],
            'Biz_PassWord'=> md5($_POST['passwd']),
            'Users_ID' => $UsersID,
            'Biz_CreateTime' => time(),
			'Group_ID'=> $Group_ID
        );

        $row = $DB->add("biz",$data);
        if ($row) {
            $Data=array(
                    'status'=>1,
                    'url'=>'/biz/login.php',
                    'msg'=>'注册成功！'
            );
            echo json_encode(empty($Data)?array('status'=>0,'msg'=>'请勿非法操作！'):$Data,JSON_UNESCAPED_UNICODE);
            exit;

        }
    }
        
} else {
	
    if(!empty($_GET["usersid"])){
		$rsUsers = $DB->GetRs("users","*","where Users_ID='".$_GET['usersid']."'");
		if (empty($rsUsers)) {
			echo '<script language="javascript">alert("非法链接");history.back();</script>';
			exit;
		}
		$UsersID = $_GET["usersid"];
 
    }else{
        echo '<script language="javascript">alert("非法链接");history.back();</script>';
        exit;
    }
	$bizConfig = $DB->GetRs("biz_config","*","where Users_ID='".$_GET['usersid']."'");
	$bizConfig["join_desc"] = str_replace('&quot;','"',$bizConfig["join_desc"]);
	$bizConfig["join_desc"] = str_replace("&quot;","'",$bizConfig["join_desc"]);
	$bizConfig["join_desc"] = str_replace('&gt;','>',$bizConfig["join_desc"]);
	$bizConfig["join_desc"] = str_replace('&lt;','<',$bizConfig["join_desc"]);
	
    $arc_cate = array();
    $DB->get("biz_article_cate","*","where Users_ID='".$UsersID."' order by Category_Index asc");
    while($r = $DB->fetch_assoc()){
        $arc_cate[] = $r;
    }
}
   
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>商家入驻-注册</title>
<style>
.bannar_bg{ background:url(<?php echo !empty($bizConfig['bannerimg'])?$bizConfig['bannerimg']:'/static/biz/images/index-2_01.jpg'?>) no-repeat center; height:542px;}
</style>
</head>
<link href="/static/biz/css/log_x.css" type="text/css" rel="stylesheet">
<script type='text/javascript' src='/static/biz/js/jquery-1.7.2.min.js'></script
<body>
<div class="w">
	<div class="bannar_bg">
    	<div class="w1">
        	<div class="consult_x r">
            	<p>商家注册</p>
                <div  class="box_bg">
                    <form action="?" method="post" id="user_form" >
                    <input type="text" class="zhuce" placeholder="请输入手机号码" name="mobile" id="mobile" onfocus="if (placeholder =='请输入手机号码'){placeholder =''}" onblur="if (placeholder ==''){placeholder='请输入手机号码'}">
                    <span class="l"><input type="text" class="yanzhengma" id="code" name="code" placeholder="请输入短信验证码" onfocus="if (placeholder =='请输入短信验证码'){placeholder =''}" onblur="if (placeholder ==''){placeholder='请输入短信验证码'}"></span>
                    <span class="l"><input  id="sendsms" style="cursor:pointer;background-color: #00A50D;color: #F2FCF3;" type="button" class="tijiao" state="0" value="获取验证码"></span>
                    <input type="password" class="zhuce" id="passwd" placeholder="请输入登录密码" name="passwd" onfocus="if (placeholder =='请输入登录密码'){placeholder =''}" onblur="if (placeholder ==''){placeholder='请输入登录密码'}">
                    <input name="提交" type="button" id="reg" class="tijiao_x" value="立即入驻">
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
	<div class="main_bg">
    	<div class="w1">
            <table class="main_title">
                <tr>
                    <td><img src="/static/biz/images/index-2_05.jpg" width="65" height="63"></td>
                    <td style=" padding:0 20px;">轻松⑤步，入驻我们</td>
                    <td><img src="/static/biz/images/index-2_07.jpg" width="65" height="63"></td>
                </tr>
            </table>
        </div>
    	<div class="buzhou"></div>
    </div>
  <div class="clear"></div>
    <div class="w1">
        <?php if (!empty($arc_cate)) { 
        foreach ($arc_cate as $k => $v) {?>
            <div class="new l">
                <div class="gonggao l"><?php echo $v['category_name']?><span class="r more_x"><a href="article_cate.php?cid=<?php echo $v['id']?>">更多>></a></span></div>
                <div class="xw_x">
                    <ul>
                        <?php  $DB->get("biz_article","*","where Users_ID='".$UsersID."' and category_id= ".$v['id']." order by id asc");
			while($res=$DB->fetch_assoc()){?>
                        <li><a href="article.php?id=<?=$res['id']?>"><span class="l"><?php echo $res['atricle_title']?></span><span class="r" style="color:#666;"><?php echo date('Y-m-d',$res['addtime'])?></span></a><div class="clear"></div></li>
			<?php } ?>
                            
                           
                    </ul>
                </div>
            </div>
			<?php
			if (($k+1)%3 == 0) {
					echo '<div class="clear"></div>';
				}
			?>
        <?php } 
       }?>

    </div>
    <div class="clear"></div>
     <div class="main_bg">
    	<div class="w1">
        	<table class="main_title">
                <tr>
                    <td><img src="/static/biz/images/index-2_05.jpg" width="65" height="63"></td>
                    <td style=" padding:0 20px;">为什么要加入我们</td>
                    <td><img src="/static/biz/images/index-2_07.jpg" width="65" height="63"></td>
                </tr>
            </table>
            <div class="clear"></div>
			<p><?php echo $bizConfig['join_desc']?></p>
           </div>
    </div>
</div>
<script>
    function isMobile(mobile) {
		if(!/^(13[0-9]|14[0-9]|15[0-9]|17[0-9]|18[0-9])\d{8}$/i.test(mobile)) {
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
        
        var usersid = '<?php echo isset($_GET['usersid'])?$_GET['usersid']:''?>';
        $.post('?', {act: 'send', Mobile:Mobile,usersid:usersid}, function(json) {
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
    
    $("#reg").click(function(){
        var Mobile = $("#mobile").val();
        var code = $("#code").val();
        var passwd = $("#passwd").val();
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
        var usersid = '<?php echo isset($_GET['usersid'])?$_GET['usersid']:''?>';
        $.post('?', {act: 'reg', Mobile:Mobile,usersid:usersid,passwd:passwd,code:code}, function(json){
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
