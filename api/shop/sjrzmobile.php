<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/api/shop/global.php'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');


if ($_POST) {
    if(!empty($_POST["usersid"])){
	$UsersID = $_POST["usersid"];
 
    }else{
        $Data=array(
            'status'=>0,
            'msg'=>'非法链接1'
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
        include '../../include/support/sysurl_helpers.php';
        $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
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
        $data = array(
            'Biz_Account' => $_POST['mobile'],
            'Biz_PassWord'=> md5($_POST['passwd']),
            'Users_ID' => $UsersID,
			'Biz_Status' => 2,
            'Biz_CreateTime' => time()
        );

        $row = $DB->add("biz",$data);
        if ($row) {
			$Data=array(
                    'status'=>1,
                    'msg'=>'入驻成功！'
            );
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            $Data=array(
                    'status'=>0,
                    'msg'=>'注册失败！'
            );
            echo json_encode($Data,JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
        
} else {
    if(!empty($_GET["UsersID"])){
	$UsersID = $_GET["UsersID"]; 
    }else{
        echo '<script language="javascript">alert("非法链接2");history.back();</script>';
        exit;
    }
    $rsConfig = shop_config($UsersID);
    //分销相关设置
    $dis_config = dis_config($UsersID);
    //合并参数
    $rsConfig = array_merge($rsConfig,$dis_config);
	$bizConfig = $DB->GetRs('biz_config', 'mobile_desc', "WHERE Users_ID='" . $UsersID . "'");
}
$isSjrzMobile = 1;
require_once('skin/top.php');
?>
<body>
<script type='text/javascript' src='/static/api/shop/js/reserve.js?t=<?php echo time();?>'></script>
<script language="javascript">
var UsersID = '<?php echo $UsersID;?>';
var base_url = '<?=$base_url?>';
$(document).ready(reserve_obj.reserve_init);
</script>
<link href='/static/api/shop/skin/default/css/sjrz.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<style>
.regImg {width:100%;}
.regImg img{width:100%;}
</style>
<div id="reserve_success">提交成功！</div>
<div id="shop_page_contents">
	<div id="cover_layer"></div>
    <!--header-->
 	<div id="header_common">
  		<div class="remark"><span onClick="history.go(-1);"></span>商家入驻</div>
  		<div class="clear"></div>
 	</div>
	<div class="regImg"><img src="/static/api/shop/biz/images/regmobile.png"/></div>
	<div id="reserve">
	  <form name="reserve_form" action="?" method="post"  id="user_form">
		<div class="reserve_table">
		  <table width="100%" cellpadding="0" cellspacing="0" border="0">
			<thead>
			  <tr>
				<td colspan="2">请认真填写表单</td>
			  </tr>
			</thead>
			<tbody>
			  <tr>
				<td class="label1">手机号码</td>
				<td><input type="text" name="mobile" placeholder="请输入手机号码" id="mobile" value="" class="form_input" notnull /></td>
			  </tr>
                           <tr>
				<td class="label1">验证码</td>
				<td><input style="width:58%" placeholder="请输入短信验证码" type="text" name="code" id="code" value="" class="form_input" notnull /><input  id="sendsms" style="float:right;cursor:pointer;background-color: #00A50D;color: #F2FCF3;" type="button" class="tijiao" state="0" value="获取验证码"></td>
			  </tr>
                           <tr>
				<td class="label1">注册密码</td>
				<td><input type="password" name="passwd" placeholder="请输入登录密码" id="passwd" value="" class="form_input" notnull /></td>
			  </tr>
			</tbody>
		  </table>
		</div>
		<div class="blank9"></div>
		<div>
			<input type="button" style="width: 100%;margin: 5px auto;margin-bottom: 0px;box-sizing: border-box;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;background: #f60;padding: 8px;font-size: 18px;border-radius: 5px;color: #fff;" id ="regmobile" value="立即入驻" />
			<input type="hidden" name="usersid" value="<?=$UsersID?>" />
			<input type="hidden" name="act" value="reg" />
		</div>
	  </form>
	  <?php if (!empty($bizConfig['mobile_desc'])) { ?>
	  <div id="reserve_show" style="display:none"><?=$bizConfig['mobile_desc']?></div>
	  <?php } else { ?>
	  <div id="reserve_show" style="display:none">请在电脑上登录<?php echo $base_url.'biz/，完成入驻！' ?></div>
	  <?php } ?>
	</div>
</div>
<script>
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
        
        var usersid = '<?php echo isset($_GET['UsersID'])?$_GET['UsersID']:''?>';
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

    });
	$('#regmobile').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};			
			$(this).attr('disabled', true).val('提交中...');
			$.post('/api/'+UsersID+'/shop/sjrzmobile/', $('form').serialize(), function(data){
				if(data.status==1){
					$('input, select, textarea').attr('disabled', true);
					$('#regmobile').attr('disabled', true).val('已完成');
					$('#regmobile').val('已完成');
					$('#reserve_success').show().animate({
						bottom:150,
						opacity:'0.7'
					}, 2000).animate({
						opacity:0
					}, 1000,function(){
					$('#reserve_show').show();});					
				}else{
					global_obj.win_alert(data.msg);
					$('#regmobile').attr('disabled', false).val('提 交');
				};
			}, 'json');
		});
</script>    
<?php require_once('skin/distribute_footer.php');?>