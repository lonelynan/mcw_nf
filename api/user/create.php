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
	exit;
}

//获取来源网站owner_id
$shop_config = shop_config($UsersID);
$dis_config = dis_config($UsersID);
//合并参数
$shop_config = array_merge($shop_config,$dis_config);
$owner = get_owner($shop_config,$UsersID);
$dis_config_limit_result = $DB->GetRs('distribute_config','Distribute_Limit','where Users_ID="'.$UsersID.'"');
if($dis_config_limit_result['Distribute_Limit'] == 1 && $owner['id']==0){
	echo '必须通过邀请人才能成为会员';
	exit;
}

if(empty($_SESSION[$UsersID."HTTP_REFERER"])){
	$HTTP_REFERER="/api/".$UsersID."/user/";
}else{
	$HTTP_REFERER=!empty($_SESSION[$UsersID."HTTP_REFERER"])?$_SESSION[$UsersID."HTTP_REFERER"]:'';
}

//商城配置,注册短信开关
$setting = $DB->GetRs('setting', "sms_enabled,user_reg_enabled");


$item = $DB->GetRs("user_config","ExpireTime","where Users_ID='".$UsersID."'");
$expiretime = $item["ExpireTime"];

$rsConfig=$DB->GetRs("user_profile","*","where Users_ID='".$UsersID."'");
if ($_POST) {
	//手机验证码校验开始
	$smsMobileKey = isset($_POST['Mobile']) ? 'reg' . $_POST['Mobile'] : '';

	//发送手机验证码
	if (isset($_POST['act']) && ($_POST['act'] == 'send') && isset($_POST['Mobile']) && $_POST['Mobile'] && ($setting['sms_enabled'] == 1 && $setting['user_reg_enabled'] == 1)) {
		//检查手机号是否已经存在
		$row = $DB->GetRs('user', '*', "WHERE Users_ID='" . $UsersID . "' AND User_Mobile='" . $_POST['Mobile'] . "'");
		if (!empty($row)) {
			$Data = array(
				"status" => 0,
				"msg" => "手机号已被占用"
			);
			echo json_encode($Data, JSON_UNESCAPED_UNICODE);
			exit;
		}

		//发送验证码
		$code = sprintf("%'.04d", rand(0, 9999));
		//echo $code.'------';
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
	if ($setting['sms_enabled'] == 1 && $setting['user_reg_enabled'] == 1) {
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
					"msg" => "手机验证码错误"
				);
				echo json_encode($Data,JSON_UNESCAPED_UNICODE);
				exit;
			}
		}
	}

	//手机验证码校验结束

	$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_Mobile='".$_POST["Mobile"]."'");
	if ($owner['id'] != 0) {
		$ownerid = $owner['id'];
	} else {
		$ownerid = 0;
	}
	
	if($rsUser){
		$Data=array(
			"status"=>0,
			"msg"=>"对不起，此手机号已经注册，请勿重复注册！",
			"url"=>$HTTP_REFERER
		);
	}else{		
			$User_No="1";		
		if(isset($rsConfig['Profile_Input'])){
			$Home_Json_Input=json_decode($rsConfig['Profile_Input'],true);
			for($no=0;$no<=4;$no++){
				if(isset($Home_Json_Input[$no]['InputName'])){
					$User_Json_Input[] = array(
						"InputName"=>$Home_Json_Input[$no]['InputName'],
						"InputValue"=>isset($_POST["InputValue_".$no]) ? $_POST["InputValue_".$no] : ""
					);
				}
			}
		}
		if(isset($rsConfig['Profile_Select'])){
			$Home_Json_Select=json_decode($rsConfig['Profile_Select'],true);
			for($no=0;$no<=4;$no++){
				if(isset($Home_Json_Select[$no]['SelectName'])){
					if (!empty($_POST["SelectValue_".$no]) && $_POST["SelectValue_".$no] == 1000000) {
						$Data = array(
						"status" => 0,
						"msg" => "请选择正确的下拉框项！"
					);
					echo json_encode($Data,JSON_UNESCAPED_UNICODE);
					exit;
					}
					$User_Json_Select[] = array(
						"SelectName"=>$Home_Json_Select[$no]['SelectName'],
						"SelectValue"=>isset($_POST["SelectValue_".$no]) ? $_POST["SelectValue_".$no] : ""
					);
				}
			}
		}
		$Data=array(
			"User_Mobile"=>$_POST['Mobile'],
			"User_Password"=>md5($_POST['Password']),
			"User_PayPassword"=>md5($_POST['Password']),				
			"User_From"=>1,
			"User_CreateTime"=>time(),
			"User_Status"=>1,
			"User_Remarks"=>"",
			"User_No"=>$User_No,
			"User_Json_Input"=>isset($User_Json_Input) ? json_encode($User_Json_Input,JSON_UNESCAPED_UNICODE) : "",
			"User_Json_Select"=>isset($User_Json_Select) ? json_encode($User_Json_Select,JSON_UNESCAPED_UNICODE) : "",
			"User_ExpireTime"=>$expiretime==0 ? 0 : ( time() + $expiretime*86400 ),
			"Users_ID"=>$UsersID,
		);
		
		if($rsConfig["Profile_Name"]){
			$Data["User_Name"] = $_POST['Name'];
		}
		
		if($rsConfig["Profile_Area"]){
			$Data["User_Province"] = $_POST['Province'];
			$Data["User_City"] = $_POST['City'];
			$Data["User_Area"] = $_POST['Area'];
		}
		
		if($rsConfig["Profile_Gender"]){
			$Data["User_Gender"] = $_POST['Gender'];
		}
		
		if($rsConfig["Profile_Age"]){
			$Data["User_Age"] = $_POST['Age'];
		}
		
		if($rsConfig["Profile_NickName"]){
			$Data["User_NickName"] = removeEmoji($_POST['NickName']);
		}
		
		if($rsConfig["Profile_IDNum"]){
			$Data["User_IDNum"] = $_POST['IDNum'];
		}
		if($rsConfig["Profile_Telephone"]){
			$Data["User_Telephone"] = $_POST['Telephone'];
		}
		if($rsConfig["Profile_Fax"]){
			$Data["User_Fax"] = $_POST['Fax'];
		}
		if($rsConfig["Profile_Birthday"]){
			$Data["User_Birthday"] = $_POST['Birthday'];
		}
		if($rsConfig["Profile_QQ"]){
			$Data["User_QQ"] = $_POST['QQ'];
		}
		if($rsConfig["Profile_Email"]){
			$Data["User_Email"] = $_POST['Email'];
		}
		if($rsConfig["Profile_Company"]){
			$Data["User_Company"] = $_POST['Company'];
		}
		if($rsConfig["Profile_Address"]){
			$Data["User_Address"] = $_POST['Address'];
		}
	
		if($owner['id'] != 0){
			$Data["Owner_Id"] = $owner['id'] ;
			$Data["Root_ID"] = $owner['Root_ID'];
		}
		$Flag = $DB->Add("user",$Data);
		$userid = $DB->insert_id();
		if($Flag){
			if (!empty($_POST['OrderID'])) {
				$Datas = array(
					'User_ID'=>$userid,
					'Owner_ID'=>!empty($_POST['OwnerID']) ? $_POST['OwnerID'] : '',
				);
				$DB->Set("distribute_order",$Datas,'where Order_ID=' . intval($_POST['OrderID']));
				$HTTP_REFERER = '/api/'.$UsersID.'/distribute/complete_pay/money/'.$_POST['OrderID'].'/';
			}
			$_SESSION[$UsersID."User_ID"] = $userid;
			$_SESSION[$UsersID."User_Mobile"]=$_POST['Mobile'];
			$OpenID = md5(session_id() . $_SESSION[$UsersID."User_ID"]);
			$userno = '600000' + $userid;
			$DB->Set("user",array('User_OpenID'=>$OpenID, 'User_No'=>$userno),'where Users_ID="'.$UsersID.'" and User_ID='.$userid);
			$Data=array(
				"status"=>1,
				"msg"=>"恭喜，注册成功！",
				"url"=>$HTTP_REFERER
			);
		}else{
			$Data=array(
				"status"=>0,
				"msg"=>"注册失败！"
			);
		}
	}
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
if(isset($_GET['OrderID'])){
	$user_url = "/api/" . $UsersID . "/user/" . $_GET['OwnerID'] . "/login/" . $_GET['OrderID'] . "/?wxref=mp.weixin.qq.com";
}else{
	$user_url = "/api/" . $UsersID . "/user/" . $_GET['OwnerID'] . "/login/?wxref=mp.weixin.qq.com";
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
<form action="?" method="post" id="user_form">
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
       <div class="zhuce_x">注册</div>
       <div class="bz_x">1.输入手机号 > 2.输入验证码 > 3.设置密码</div>
       <div class="box_x">
            <input tabIndex=1 type="tel" name="Mobile" id="Mobile" value="" maxlength="11"  class="dll_x" placeholder="请输入手机号码" pattern="[0-9]*" notnull >

			<?php
			if ($setting['sms_enabled'] == 1 && $setting['user_reg_enabled'] == 1) {
			?>            
			<span class="l yzm1_x"><input tabIndex=2 id="captcha" name="captcha" maxlength="4"  pattern="[0-9]*" type="text" class="yzm2_x" placeholder="请输入短信验证码" notnull></span>
            <span class="l yzm_x"><a href="javascript:;" id="btn_send" state="0" style="text-decoration:none;">获取验证码</a></span>
			<?php
			}
			?>


			<input name="OrderID" type="hidden"  value="<?php echo !empty($_GET['OrderID']) ? $_GET['OrderID'] : '' ;?>">
			<input name="OwnerID" type="hidden"  value="<?php echo !empty($_GET['OwnerID']) ? $_GET['OwnerID'] : '' ;?>">
            <div class="clear"></div>
            <input tabIndex=3 type="password" name="Password" value="" maxlength="16"  class="dll_x" placeholder="请设置账户密码" notnull >
            <input tabIndex=4 type="password" name="ConfirmPassword" value=""  class="dll_x" placeholder="请确认账户密码" notnull >                    
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
})	

</script>            
  <?php if($rsConfig["Profile_Area"]){?>
    <select name="Province"<?php echo $rsConfig["Profile_AreaNotNull"]==1 ? " notnull" : ""?>>
    </select>
    <select name="City"<?php echo $rsConfig["Profile_AreaNotNull"]==1 ? " notnull" : ""?>>
    </select>
    <select name="Area"<?php echo $rsConfig["Profile_AreaNotNull"]==1 ? " notnull" : ""?>>
    </select>
	<script type='text/javascript' src='/static/js/plugin/pcas/pcas.js'></script>
    <script language="javascript">new PCAS('Province', 'City', 'Area');</script>
  <?php }?>
  <?php if($rsConfig["Profile_Gender"]){?>
  <div class="input dll_x2">
    <select name="Gender" style="margin-bottom:0px;background-color:#f9f9f9;">
	 <option value="男">男</option>
	 <option value="女">女</option>
    </select>
  </div>
  <?php }?>
   <?php if($rsConfig["Profile_Name"]){?>
    <input type="text" name="Name" value="" class="dll_x" maxlength="10" placeholder="您的姓名"<?php echo $rsConfig["Profile_NameNotNull"]==1 ? " notnull" : ""?> />
    <?php }?>
  <?php if($rsConfig["Profile_Age"]){?>
    <input type="text" name="Age" value="" class="dll_x" maxlength="10" placeholder="您的年龄"<?php echo $rsConfig["Profile_AgeNotNull"]==1 ? " notnull" : ""?> />
  <?php }?>
  <?php if($rsConfig["Profile_NickName"]){?>
    <input type="text" name="NickName" value="" class="dll_x" placeholder="您的昵称"<?php echo $rsConfig["Profile_NickNameNotNull"]==1 ? " notnull" : ""?> />
  <?php }?>
  <?php if($rsConfig["Profile_IDNum"]){?>
    <input type="text" name="IDNum" value="" class="dll_x" placeholder="您的身份证号"<?php echo $rsConfig["Profile_IDNumNotNull"]==1 ? " notnull" : ""?> />
  <?php }?>
  <?php if($rsConfig["Profile_Telephone"]){?>
    <input type="text" name="Telephone" value="" class="dll_x" placeholder="您的电话"<?php echo $rsConfig["Profile_TelephoneNotNull"]==1 ? " notnull" : ""?> />
  <?php }?>
  <?php if($rsConfig["Profile_Fax"]){?>
    <input type="text" name="Fax" value="" class="dll_x" placeholder="您的传真"<?php echo $rsConfig["Profile_FaxNotNull"]==1 ? " notnull" : ""?> />
  <?php }?>
  <?php if($rsConfig["Profile_Birthday"]){?>  
    <input type="text" name="Birthday" value="" class="dll_x1" placeholder="您的生日"<?php echo $rsConfig["Profile_Birthday"]==1 ? " notnull" : ""?> />  
  <?php }?>
  <?php if($rsConfig["Profile_QQ"]){?>
    <input type="text" name="QQ" value="" class="dll_x" placeholder="您的QQ"<?php echo $rsConfig["Profile_QQNotNull"]==1 ? " notnull" : ""?> />
  <?php }?>
  <?php if($rsConfig["Profile_Email"]){?>
    <input type="text" name="Email" value="" class="dll_x" placeholder="您的邮箱"<?php echo $rsConfig["Profile_EmailNotNull"]==1 ? " notnull" : ""?> />
  <?php }?>
  <?php if($rsConfig["Profile_Company"]){?>
    <input type="text" name="Company" value="" class="dll_x" placeholder="您的公司"<?php echo $rsConfig["Profile_CompanyNotNull"]==1 ? " notnull" : ""?> />
  <?php }?>
  <?php if($rsConfig["Profile_Address"]){?>
    <input type="text" name="Address" value="" class="dll_x" placeholder="您的详细地址"<?php echo $rsConfig["Profile_AddressNotNull"]==1 ? " notnull" : ""?> />
  <?php }?>
  
  
  						<?php
								if(isset($rsConfig['Profile_Input'])){
									$Home_Json_Input=json_decode($rsConfig['Profile_Input'],true);
									for($no=0;$no<=4;$no++){
										if(isset($Home_Json_Input[$no]['InputName'])){
						?>
                        <input type="text" name="InputValue_<?php echo $no;?>" value="<?php echo $Home_Json_Input[$no]['InputValue'];?>" class=" dll_x" placeholder="<?php echo $Home_Json_Input[$no]['InputName'];?>" />
                        <?php }}}?>
                        <?php
								$arr_value = array();
								if(isset($rsConfig['Profile_Select'])){
									$Home_Json_Select=json_decode($rsConfig['Profile_Select'],true);
									for($no=0;$no<=4;$no++){
										if(isset($Home_Json_Select[$no]['SelectName'])){
											$arr_value = explode("|", $Home_Json_Select[$no]['SelectValue']);
						?>
						 <div class="input dll_x2">
                        	<select name="SelectValue_<?php echo $no;?>" style="margin-bottom:0px; background-color:#f9f9f9;">
							<option value='1000000' >请选择<?=$Home_Json_Select[$no]['SelectName']?></option>
                            <?php foreach($arr_value as $k=>$v){?>
								<option value='<?php echo $v;?>' ><?php echo $v;?></option>
                            <?php }?>
                            </select>
					
                        <?php }}}?>
   </div>
  <div class="submit zc_x">
    <input name="提交" class="tx_x" type="button" value="立即注册" />
  </div>
		   <div class="t_x submit" >
			   <span class="r"><a href="<?= $user_url ?>">有账号？ 去登录</a></span>
		   </div>

	   </div>
  </div>
</form>
</body>
</html>
<?php exit;?>