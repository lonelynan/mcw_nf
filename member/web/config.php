<?php
$DB->showErr=false;
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
require_once('vertify.php');
$rsConfig=$DB->GetRs("web_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
if(empty($rsConfig)){
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"SiteName"=>"微官网",
		"CallEnable"=>0,
		"Animation"=>0,
	);
	$DB->Add("web_config",$Data);
	$rsConfig=$DB->GetRs("web_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
}
$json=$DB->GetRs("wechat_material","*","where Users_ID='".$_SESSION["Users_ID"]."' and Material_Table='web' and Material_TableID=0 and Material_Display=0");
if(empty($json)){
	$Material=array(
		"Title"=>"微官网",
		"ImgPath"=>"/static/api/images/cover_img/web.jpg",
		"TextContents"=>"",
		"Url"=>"/api/".$_SESSION["Users_ID"]."/web/"
	);
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"Material_Table"=>"web",
		"Material_TableID"=>0,
		"Material_Display"=>0,
		"Material_Type"=>0,
		"Material_Json"=>json_encode($Material,JSON_UNESCAPED_UNICODE),
		"Material_CreateTime"=>time()
	);
	$DB->Add("wechat_material",$Data);
	$MaterialID=$DB->insert_id();
	$rsMaterial=$Material;
}else{
	$rsMaterial=json_decode($json['Material_Json'],true);
}
$rsKeyword=$DB->GetRs("wechat_keyword_reply","*","where Users_ID='".$_SESSION["Users_ID"]."' and Reply_Table='web' and Reply_TableID=0 and Reply_Display=0");
if(empty($rsKeyword)){
	$MaterialID=empty($json['Material_Json'])?$MaterialID:$json['Material_Json'];
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"Reply_Table"=>"web",
		"Reply_TableID"=>0,
		"Reply_Display"=>0,
		"Reply_Keywords"=>"微官网",
		"Reply_PatternMethod"=>0,
		"Reply_MsgType"=>1,
		"Reply_MaterialID"=>$MaterialID,
		"Reply_CreateTime"=>time()
	);
	$DB->Add("wechat_keyword_reply",$Data);
	$rsKeyword=$Data;
}
if($_POST)
{
	//开始事务定义
	$flag=true;
	$msg="";
	mysql_query("begin");
	$Data=array(
		"SiteName"=>$_POST["SiteName"],
		"CallEnable"=>isset($_POST["CallEnable"])?$_POST["CallEnable"]:0,
		"CallPhoneNumber"=>$_POST["CallPhoneNumber"],
		"MusicPath"=>$_POST["MusicPath"],
		"Animation"=>isset($_POST["Animation"])?$_POST["Animation"]:0,
		"PagesShow"=>$_POST["PagesShow"],
		"ShowTime"=>$_POST["ShowTime"],
		"PagesPic"=>$_POST["PagesPic"]
	);
	$Set=$DB->Set("web_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	$flag=$flag&&$Set;
	$Data=array(
		"Reply_Keywords"=>$_POST["Keywords"],
		"Reply_PatternMethod"=>isset($_POST["PatternMethod"])?$_POST["PatternMethod"]:0
	);
	$Set=$DB->Set("wechat_keyword_reply",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Reply_Table='web' and Reply_TableID=0 and Reply_Display=0");
	$flag=$flag&&$Set;
	$Material=array(
		"Title"=>$_POST["Title"],
		"ImgPath"=>$_POST["ImgPath"],
		"TextContents"=>"",
		"Url"=>"/api/".$_SESSION["Users_ID"]."/web/"
	);
	$Data=array(
		"Material_Json"=>json_encode($Material,JSON_UNESCAPED_UNICODE)
	);
	$Set=$DB->Set("wechat_material",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Material_Table='web' and Material_TableID=0 and Material_Display=0");
	$flag=$flag&&$Set;
	if($flag)
	{
		mysql_query("commit");
		$Data=array(
			"status"=>1,
			"url"=>$_SERVER['HTTP_REFERER'].'?t='.time(),
			"msg"=>"保存成功，继续修改？"
		);
	}else
	{
		mysql_query("roolback");
		$Data=array(
			"status"=>0,
			"msg"=>"保存失败"
		);
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
}
$jcurid = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
    <?php require_once (CMS_ROOT . '/member/image_config.php'); ?>
</head>
<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/web.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/web.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/web/web_menubar.php');?>
    <script language="javascript">
$(document).ready(function(){
	global_obj.config_form_init();
});
</script>
    <div class="r_con_config r_con_wrap">
      <form id="config_form">
        <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="50%" valign="top"><h1><span class="fc_red">*</span> <strong>微官网名称</strong></h1>
              <input type="text" class="input" name="SiteName" value="<?php echo $rsConfig["SiteName"] ?>" maxlength="30" notnull /></td>
            <td width="50%" valign="top"><h1><strong>一键拨号</strong>
                <input type="checkbox" name="CallEnable" value="1"<?php echo empty($rsConfig["CallEnable"])?"":" checked"; ?> />
                <span class="tips">启用</span></h1>
              <input type="text" class="input" name="CallPhoneNumber" value="<?php echo empty($rsConfig["CallPhoneNumber"])?"":$rsConfig["CallPhoneNumber"]; ?>" maxlength="20" /></td>
          </tr>
          <tr>
            <td valign="top">
                <h1>
                    <strong>背景音乐</strong>
                    <span class="tips">（填写音乐链接地址或上传音乐文件）</span>
                </h1>
                <div class="file" style="margin-top:10px;">
                    <input name="MusicPathUpload" id="MusicPathUpload" type="button" style="width:120px;" value="上传音乐" />
                    <input type="hidden" id="MusicPath" name="MusicPath" value="<?php echo $rsConfig && $rsConfig['MusicPath']<>'' ? $rsConfig['MusicPath'] : ''?>" />
                    <p class="up_mp3"><span class="tips">500KB以内，mp3格式</span> </p>
                </div>
            </td>
            <td valign="top"><h1><strong>动画效果</strong></h1>
              <div class="input">
                <label><input type="radio" value="0" name="Animation"<?php echo empty($rsConfig["Animation"])?" checked":""; ?> />
                无</label>
                <label><input type="radio" value="1" name="Animation"<?php echo $rsConfig["Animation"]==1?" checked":""; ?> />
                雪花</label>
                <label><input type="radio" value="2" name="Animation"<?php echo $rsConfig["Animation"]==2?" checked":""; ?> />
                烟花</label></div></td>
          </tr>
		  <tr>
            <td valign="top">
				<h1><strong>引导页</strong></h1>
                <div class="input">
                    <input type="radio" value="0" name="PagesShow"<?php echo $rsConfig["PagesShow"]==0 ? " checked" : "";?> />无
                    <input type="radio" value="1" name="PagesShow"<?php echo $rsConfig["PagesShow"]==1 ? " checked" : "";?> />马赛克
                    <input type="radio" value="2" name="PagesShow"<?php echo $rsConfig["PagesShow"]==2 ? " checked" : "";?> />淡出
                    <input type="radio" value="3" name="PagesShow"<?php echo $rsConfig["PagesShow"]==3 ? " checked" : "";?> />开门
                </div>
                <div class="rows">
                    <span class="lbar">播放时间(秒):</span>
                    <span class="rbar">
                        <input type="text" class="input" name="ShowTime" style="width:70px" value="<?php echo $rsConfig["ShowTime"];?>" maxlength="10" />
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="file" style="margin:10px;">
                    <input name="PagesPicUpload" id="PagesPicUpload" type="button" style="width:120px" value="上传图片" />
                    <input type="hidden" name="PagesPic" id="PagesPic" value="<?php echo $rsConfig["PagesPic"] ? $rsConfig["PagesPic"] : '/static/api/web/images/leader.jpg';?>" />
                    <span class="tips">建议尺寸:640*1010px</span>
                    <div class="clear"></div>
                </div>
                <div id="PagesPicDetail">
                    <img src="<?php echo $rsConfig["PagesPic"] ? $rsConfig["PagesPic"] : '/static/api/web/images/leader.jpg';?>" />
                </div>
			</td>
            <td valign="top">&nbsp;</td>
          </tr>
        </table>
        <table align="center" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td><h1><strong>触发信息设置</strong></h1>
              <div class="reply_msg">
                <div class="m_left"> <span class="fc_red">*</span> 触发关键词<!--<span class="tips_key">（有多个关键词请用 <font style="color:red">"|"</font> 隔开）</span>--><br />
                  <input type="text" class="input" name="Keywords" value="<?php echo $rsKeyword["Reply_Keywords"] ?>" maxlength="100" notnull />
                  <br />
                  <br />
                  <br />
                  <span class="fc_red">*</span> 匹配模式<br />
                  <div class="input">
                    <label>
                      <input type="radio" name="PatternMethod" value="0"<?php echo empty($rsKeyword["Reply_PatternMethod"])?" checked":""; ?> />
                      精确匹配<span class="tips">（输入的文字和此关键词一样才触发）</span></label>
                  </div>
                  <div class="input">
                    <label>
                      <input type="radio" name="PatternMethod" value="1"<?php echo $rsKeyword["Reply_PatternMethod"]==1?" checked":""; ?> />
                      模糊匹配<span class="tips">（输入的文字包含此关键词就触发）</span></label>
                  </div>
                  <br />
                  <br />
                  <span class="fc_red">*</span> 图文消息标题<br />
                  <input type="text" class="input" name="Title" value="<?php echo $rsMaterial["Title"] ?>" maxlength="100" notnull />
                </div>
                <div class="m_right"> <span class="fc_red">*</span> 图文消息封面<span class="tips">（大图尺寸建议：640*360px，500KB以内，gif,jpg,jpeg,png格式）</span><br />
                  <div class="file" style="margin:10px;">
                      <input name="ImgUpload" id="ImgUpload" type="button" style="width:120px" value="上传图片" />
                      <input type="hidden" name="ImgPath" id="ImgPath" value="<?php echo isset($rsConfig["ImgPath"]) ? $rsConfig["ImgPath"] : '/static/api/web/images/leader.jpg';?>" />
                  </div>
                  <div class="img" id="ImgDetail"><img src="<?php echo empty($rsMaterial["ImgPath"])?"/api/images/cover/web.jpg":$rsMaterial["ImgPath"]; ?>" width="640" height="360"></div>
                </div>
                <div class="clear"></div>
              </div>
              </td>
          </tr>
        </table>
        <div class="submit">
          <input type="submit" name="submit" value="提交保存" />
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>