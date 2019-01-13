<?php 
$DB->showErr=false;
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
if($_POST){
	$Data=array(
		"CardName"=>$_POST['CardName'],
		"CardLogo"=>$_POST['CardLogo'],
		"CardStyle"=>$_POST['CardStyle'],
		"CardStyleCustom"=>empty($_POST['CardStyleCustom'])?0:1,
		"CustomImgPath"=>$_POST['CustomImgPath']
	);
	$Flag=$DB->Set("user_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	if($Flag){
		$Data=array(
			"status"=>1
		);
	}else{
		$Data=array(
			"status"=>0,
			"msg"=>"设置失败"
		);
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
}

$rsConfig=$DB->GetRs("user_config","CardName,CardLogo,CardStyle,CardStyleCustom,CustomImgPath","where Users_ID='".$_SESSION["Users_ID"]."'");
if(empty($rsConfig)){
	header("location:config.php");
}
$jcurid = 3;
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
<script type="text/javascript" src="/third_party/uploadify/jquery.uploadify.min.js"></script>
<link href="/third_party/uploadify/uploadify.css" rel="stylesheet" type="text/css">

</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
<style>
#card_style .file .img,#card_style .file .img img{max-height:160px; max-width: 320px}
</style>    
    <script type='text/javascript' src='/static/member/js/user.js'></script>
     <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/user/user_menubar.php');?>
    <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script> 
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <script language="javascript">$(document).ready(function(){user_obj.card_config_init()});</script>
    <div class="r_con_config r_con_wrap">
      <form id="card_config_form">
        <div id="card_style">
          <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="50%" valign="top" align="left"><div class="file"> <span class="fc_red">*</span> 会员卡名称<br />
                  <input type="text" class="input" name="CardName" value="<?php echo $rsConfig['CardName'] ?>" maxlength="30" notnull />
                  <br />
                  <br />
                  <br />
                  <span class="fc_red">*</span> 商家LOGO上传<span class="fc_red">（请上传png透明格式）</span><br />
                  <span class="tips">&nbsp;&nbsp;大图尺寸建议：640*320px</span><br />
                  <br />
                  <div>
                    <div class="logo_upload">
                      <input id="CardLogoUpload" name="CardLogoUpload" type="file">
                    </div>
                    <div class="del"><a href="javascript:;"><img src="/static/member/images/ico/del.gif" alt="删除LOGO" /></a></div>
                  </div>
                  <div class="blank12"></div>
                  <div class="img" id="CardLogoDetail"></div>
                </div></td>
              <td width="50%" valign="top" align="left"> 自定义会员卡背景
                <input type="checkbox" name="CardStyleCustom" value="1"<?php echo empty($rsConfig['CardStyleCustom'])?'':' checked' ?> />
                <div id="CardStyleCustomBox" style="display:none;">
                  <div class="blank12"></div>
                      <input id="CustomImgUpload" name="CustomImgUpload" type="file">
                </div>
                <div class="blank12"></div>
                <div id="card_style_select" style="display:none;" class="style"> <a href="#card_style_list"><img src="/static/api/images/user/card_bg/<?php echo $rsConfig['CardStyle'] ? $rsConfig['CardStyle'] : '3'; ?>.png" /></a><br />
                  <a href="#card_style_list">选择会员卡样式</a> </div>
                <div id="CustomImgDetail" style="display:none;" class="style"></div></td>
            </tr>
          </table>
          <div class="clear"></div>
        </div>
        <br />
        <div class="submit">
          <input type="submit" name="submit_button" value="提交保存" />
        </div>
        <input type="hidden" id="CardLogo" name="CardLogo" value="<?php echo $rsConfig['CardLogo'] ?>" />
        <input type="hidden" name="CardStyle" value="<?php echo empty($rsConfig['CardStyle']) ? 0 : $rsConfig['CardStyle']; ?>" />
        <input type="hidden" id="CustomImgPath" name="CustomImgPath" value="<?php echo $rsConfig['CustomImgPath'] ?>" />
      </form>
    </div>
    <div id="card_style_list" class="lean-modal">
      <div class="h">选择会员卡样式<a class="modal_close" href="#"></a></div>
      <div class="list">
        <table align="center" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td width="50%"><a href="#" value="0" class="cur"><img src="/static/api/images/user/card_bg/0.png" /></a></td>
            <td width="50%"><a href="#" value="1" class=""><img src="/static/api/images/user/card_bg/1.png" /></a></td>
          </tr>
          <tr>
            <td width="50%"><a href="#" value="2" class=""><img src="/static/api/images/user/card_bg/2.png" /></a></td>
            <td width="50%"><a href="#" value="3" class=""><img src="/static/api/images/user/card_bg/3.png" /></a></td>
          </tr>
          <tr>
            <td width="50%"><a href="#" value="4" class=""><img src="/static/api/images/user/card_bg/4.png" /></a></td>
            <td width="50%"><a href="#" value="5" class=""><img src="/static/api/images/user/card_bg/5.png" /></a></td>
          </tr>
          <tr>
            <td width="50%"><a href="#" value="6" class=""><img src="/static/api/images/user/card_bg/6.png" /></a></td>
            <td width="50%"><a href="#" value="7" class=""><img src="/static/api/images/user/card_bg/7.png" /></a></td>
          </tr>
          <tr>
            <td width="50%"><a href="#" value="8" class=""><img src="/static/api/images/user/card_bg/8.png" /></a></td>
            <td width="50%"><a href="#" value="9" class=""><img src="/static/api/images/user/card_bg/9.png" /></a></td>
          </tr>
          <tr>
            <td width="50%"><a href="#" value="10" class=""><img src="/static/api/images/user/card_bg/10.png" /></a></td>
            <td width="50%"><a href="#" value="11" class=""><img src="/static/api/images/user/card_bg/11.png" /></a></td>
          </tr>
          <tr>
            <td width="50%"><a href="#" value="12" class=""><img src="/static/api/images/user/card_bg/12.png" /></a></td>
            <td width="50%"><a href="#" value="13" class=""><img src="/static/api/images/user/card_bg/13.png" /></a></td>
          </tr>
          <tr>
            <td width="50%"><a href="#" value="14" class=""><img src="/static/api/images/user/card_bg/14.png" /></a></td>
            <td width="50%"><a href="#" value="15" class=""><img src="/static/api/images/user/card_bg/15.png" /></a></td>
          </tr>
          <tr>
            <td width="50%"><a href="#" value="16" class=""><img src="/static/api/images/user/card_bg/16.png" /></a></td>
            <td width="50%"><a href="#" value="17" class=""><img src="/static/api/images/user/card_bg/17.png" /></a></td>
          </tr>
          <tr>
            <td width="50%"><a href="#" value="18" class=""><img src="/static/api/images/user/card_bg/18.png" /></a></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>