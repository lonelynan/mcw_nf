<?php

if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}


function alert($msg) {
	header('Content-type: text/html; charset=UTF-8');
	echo "<script>alert('" . $msg . "');history.back();</script>";
	die();
}

if($_POST){
	
	//有上传文件时
	$save_path = $_SERVER['DOCUMENT_ROOT'];
	$max_size = 1024 * 512;
	$ext_arr = ['txt'];
	$file_name = '';
	
 $erroro = $_FILES['imgFile']['error'];
	if (($erroro)!='4') {
		//原文件名
		$file_name = $_FILES['imgFile']['name'];
		//服务器上临时文件名
		$tmp_name = $_FILES['imgFile']['tmp_name'];
		//文件大小
		$file_size = $_FILES['imgFile']['size'];
		//检查文件名
		if (!$file_name) {
			alert("请选择文件。");
		}
		
		//检查文件名是否符合要求
		$ask = 'MP_verify_';
		 if(strstr($file_name,$ask) === false){
			 alert("不是微信认证文件，请重新上传");
		 }   

		//检查目录
		if (@is_dir($save_path) === false) {
			alert("上传目录不存在。");
		}
		//检查目录写权限
		if (@is_writable($save_path) === false) {
			alert("上传目录没有写权限。");
		}
		//检查是否已上传
		if (@is_uploaded_file($tmp_name) === false) {
			alert("上传失败。");
		}
		//检查文件大小
		if ($file_size > $max_size) {
			alert("上传文件大小超过".($max_size/1024)."K限制。");
		}

		//获得文件扩展名
		$temp_arr = explode(".", $file_name);
		$file_ext = array_pop($temp_arr);
		$file_ext = trim($file_ext);
		$file_ext = strtolower($file_ext);
		
		//检查扩展名
		if (in_array($file_ext, $ext_arr) === false) {
			
			alert("上传文件只允许TXT格式。");
		}

		$file_path = $save_path . '/' . $file_name;
		
		if (move_uploaded_file($tmp_name, $file_path) === false) {
			alert("上传文件失败。");
		}
		
	}
	$rsUsers=$DB->GetRs("users","*","where Users_ID='".$_SESSION["Users_ID"]."'");
	if (($erroro)=='4') {
		$file_name = $rsUsers['weixinfile'];	
	}

	$Data=array(
		"Users_WechatName"=>trim($_POST["WechatName"]),
		"Users_WechatEmail"=>trim($_POST["WechatEmail"]),
		"Users_WechatID"=>trim($_POST["WechatID"]),
		"Users_WechatAccount"=>trim($_POST["WechatAccount"]),
		"Users_EncodingAESKey"=>trim($_POST["EncodingAESKey"]),
		"Users_EncodingAESKeyType"=>$_POST["EncodingAESKeyType"],
		"Users_WechatAppId"=>trim($_POST["WechatAppId"]),
		"Users_WechatAppSecret"=>trim($_POST["WechatAppSecret"]),
		"Users_WechatAuth"=>isset($_POST["WechatAuth"])?1:0,
		"Users_WechatVoice"=>isset($_POST["WechatVoice"])?1:0,
		"Users_WechatType"=>$_POST["WechatType"],
		"weixinfile"=> $file_name
	);
	$Flag=$DB->Set("users",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	if($Flag){
		echo '<script language="javascript">alert("保存成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
	}else{
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	}
	exit;
}
$rsUsers=$DB->GetRs("users","*","where Users_ID='".$_SESSION["Users_ID"]."'");
$jcurid = 1;
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
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}

    
    .main{
        width:300px;
        height:60px;
        position:absolute;
        left:50%;
        top:50%;
        margin-left:-150px;
        margin-top:-30px;
    }
    .box{
        position:relative;
        float:left;
    }
    input.uploadFile{
        position:absolute;
        right:0px;
        top:0px;
        opacity:0;
        filter:alpha(opacity=0);
        cursor:pointer;
        width:276px;
        height:36px;
        overflow: hidden;
		
    }
    input.textbox{
        float:left;
        padding:5px;
        color:#999;
        height:20px;
        line-height:24px;
        border:1px #ccc solid;
        width:200px;
        margin-right:4px;
		border-radius: 4px;
    }
    a.link{
        float:left;
        display:inline-block;
        padding:4px 16px;
        color:#fff;
        font:14px "Microsoft YaHei", Verdana, Geneva, sans-serif;
        cursor:pointer;
		border-radius: 8px;
        background-color:#0099ff;
        line-height:24px;
        text-decoration:none;
    }

</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/wechat.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/wechat.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/weixin_menubar.php');?>
    <script language="javascript">$(document).ready(wechat_obj.set_token_init);</script>
    <div id="token" class="r_con_wrap">
      <div class="tips_info">
      	1. 在公众平台申请接口使用的AppId和AppSecret，然后填入下边表单。<br />
		2. <font style="color:#F00; font-size:12px;">服务认证号</font>请在<font style="color:#F00; font-size:12px;">微信公众平台开发者中心->网页服务->网页账号->网页授权获取用户基本信息</font>设置授权回调页面域名为 <font style="color:#F00; font-size:12px;"><?php echo $_SERVER["HTTP_HOST"];?></font> <br />
		3. 请在<font style="color:#F00; font-size:12px;">微信公众平台公众号设置->功能设置->JS接口安全域名</font>设置域名为 <font style="color:#F00; font-size:12px;"><?php echo $_SERVER["HTTP_HOST"];?></font>
      </div>
      <form class="r_con_form" action="token_set.php" method="post" enctype="multipart/form-data">
        <div class="set_token_msg"></div>
        <div class="rows">
          <label>接口URL</label>
          <span class="input"><span class="tips">http://<?php echo $_SERVER['HTTP_HOST'].'/api/'.$_SESSION["Users_ID"]; ?>/</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>接口Token</label>
          <span class="input"><span class="tips"><?php echo $rsUsers["Users_WechatToken"]; ?></span></span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>公众号类型</label>
          <span class="input">
			<input type="radio" name="WechatType" value="0" id="type_0"<?php echo $rsUsers["Users_WechatType"]==0 ? " checked" : "";?>/><label for="type_0">订阅号未认证</label>&nbsp;&nbsp;
			<input type="radio" name="WechatType" value="1" id="type_1"<?php echo $rsUsers["Users_WechatType"]==1 ? " checked" : "";?>/><label for="type_1">订阅号已认证</label>&nbsp;&nbsp;
			<input type="radio" name="WechatType" value="2" id="type_2"<?php echo $rsUsers["Users_WechatType"]==2 ? " checked" : "";?>/><label for="type_2">服务号未认证</label>&nbsp;&nbsp;
			<input type="radio" name="WechatType" value="3" id="type_3"<?php echo $rsUsers["Users_WechatType"]==3 ? " checked" : "";?>/><label for="type_3">服务号已认证</label>
		  </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>AppId <span class="fc_red">*</span></label>
          <span class="input">
          <input name="WechatAppId" value="<?php echo $rsUsers["Users_WechatAppId"] ?>" type="text" class="form_input" size="35" maxlength="18" notnull>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>AppSecret <span class="fc_red">*</span></label>
          <span class="input">
          <input name="WechatAppSecret" value="<?php echo $rsUsers["Users_WechatAppSecret"] ?>" type="text" class="form_input" size="35" maxlength="32" notnull>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>微信认证</label>
          <span class="input">
          <input type="checkbox" value="1" name="WechatAuth"<?php echo $rsUsers["Users_WechatAuth"]?" checked":""; ?> />
          <span class="tips">如果您的公众号已通过微信认证，请勾选此选项</span><br />
          <input type="checkbox" value="1" name="WechatVoice"<?php echo $rsUsers["Users_WechatVoice"]?" checked":""; ?> />
          <span class="tips">开启语音关键词回复，需同时开启微信认证选项</span>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>公众号名称</label>
          <span class="input">
          <input name="WechatName" id="WechatName" value="<?php echo $rsUsers["Users_WechatName"]; ?>" type="text" class="form_input" size="35" maxlength="100" notnull>
          </span>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>公众号邮箱</label>
          <span class="input">
          <input name="WechatEmail" id="WechatEmail" value="<?php echo $rsUsers["Users_WechatEmail"]; ?>" type="text" class="form_input" size="35" maxlength="100" notnull>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>公众号原始ID</label>
          <span class="input">
          <input name="WechatID" id="WechatID" value="<?php echo $rsUsers["Users_WechatID"]; ?>" type="text" class="form_input" size="35" maxlength="100" notnull>
          </span> </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>微信号</label>
          <span class="input">
          <input name="WechatAccount" id="WechatAccount" value="<?php echo $rsUsers["Users_WechatAccount"]; ?>" type="text" class="form_input" size="35" maxlength="100" notnull>
          </span> </span>
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>EncodingAESKey</label>
          <span class="input">
          <input name="EncodingAESKey" id="EncodingAESKey" value="<?php echo $rsUsers["Users_EncodingAESKey"]; ?>" type="text" class="form_input" size="35" maxlength="100" />          
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>消息加解密方式</label>
          <span class="input">
          <select name="EncodingAESKeyType" id="EncodingAESKeyType">
			  <option value="0"<?php echo $rsUsers["Users_EncodingAESKeyType"]==0 ? " selected" : "";?>>明文模式</option>
			  <option value="1"<?php echo $rsUsers["Users_EncodingAESKeyType"]==1 ? " selected" : "";?>>兼容模式</option>
			  <option value="2"<?php echo $rsUsers["Users_EncodingAESKeyType"]==2 ? " selected" : "";?>>安全模式（推荐）</option>
		  </select>
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
                <label>微信认证文件</label>
                <span class="input">
					<div class="box">
					 <input type="text" name="imgFile"  class="textbox" value="<?php echo($rsUsers['weixinfile']); ?>" id="fileName"/>
					 <a href="javascript:void(0);" id="look_file" class="link">浏览</a>
					 <input type="file" id="file" class="uploadFile" name="imgFile" onchange="handleFile()" />
					 <br /><font style="font-size:12px; color:red">注：认证文件将在24小时后自动删除,请及时进行认证</font>
					</div>
				</span>
                <div class="clear"></div>
            </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" name="submit_button" value="设置" />
          </span>
          <div class="clear"></div>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
	var file = document.getElementById("file");
    var fileName = document.getElementById("fileName");
    function handleFile(){
     fileName.value = file.value;
        }
</script>
</body>
</html>