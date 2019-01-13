<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/control/wechat/keyword.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/wechat/material.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/shop/setting.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/region.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
$setting = new setting($DB,$_SESSION["Users_ID"]);
$keyword = new keyword($DB,$_SESSION["Users_ID"]);
$material = new material($DB,$_SESSION["Users_ID"]);
$DB->showErr=false;
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$rsConfig = $DB->GetRs("shop_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
$rsKeyword = $DB->GetRs("wechat_keyword_reply","*","where Users_ID='".$_SESSION["Users_ID"]."' and Reply_Table='shop' and Reply_TableID=0 and Reply_Display=0");
$json=$DB->GetRs("wechat_material","*","where Users_ID='".$_SESSION["Users_ID"]."' and Material_Table='shop' and Material_TableID=0 and Material_Display=0");
$rsMaterial=json_decode($json['Material_Json'],true);

$man_list = json_decode($rsConfig['Man'],true);
$integral_use_laws = json_decode($rsConfig['Integral_Use_Laws'],true);

if($_POST)
{
	//开始事务定义
	$flag=true;
	$msg="";
	mysql_query("begin");
	
	if($_POST["Area"]>0){
		$areaid = $_POST["Area"];
	}else{
		if($_POST["City"]>0){
			$areaid = $_POST["City"];
		}else{
			echo '<script language="javascript">alert("请选择默认地级市");history.back();</script>';
			exit;
		}
	}
	
	
	$_POST['ShopAnnounce'] = str_replace('"','&quot;',$_POST['ShopAnnounce']);
	$_POST['ShopAnnounce'] = str_replace("'","&quot;",$_POST['ShopAnnounce']);
	$_POST['ShopAnnounce'] = str_replace('>','&gt;',$_POST['ShopAnnounce']);
	$_POST['ShopAnnounce'] = str_replace('<','&lt;',$_POST['ShopAnnounce']);
	$Data=array(
		"ShopName"=>$_POST["ShopName"],
		"biz_qrcodeone"=>$_POST["biz_qrcodeone"],
		"biz_qrcodetwo"=>$_POST["biz_qrcodetwo"],
		//"show_t"=>$_POST["show_t"],
        "DisSwitch"=>isset($_POST["DisSwitch"])?$_POST["DisSwitch"]:0,
		"ShopAnnounce"=>$_POST["ShopAnnounce"],
		"Bottom_Style"=>$_POST["Bottom_Style"],
		"ShopLogo"=>$_POST["Logo"],
		"NeedShipping"=>1,
		"SendSms"=>isset($_POST["SendSms"])?$_POST["SendSms"]:0,
		"MobilePhone"=>$_POST["MobilePhone"],
		"CallEnable"=>isset($_POST["CallEnable"])?$_POST["CallEnable"]:0,
		"CallPhoneNumber"=>$_POST["CallPhoneNumber"],
		"Icon_Color"=>$_POST["icon_color"],
		"CheckOrder"=>isset($_POST["CheckOrder"])?$_POST["CheckOrder"]:0,
		"Confirm_Time"=>isset($_POST["Confirm_Time"])?$_POST["Confirm_Time"]*86400:0,
		"DefaultLng"=>isset($_POST["DefaultLng"])?$_POST["DefaultLng"]:0,
		"Commit_Check"=>isset($_POST["CommitCheck"])?$_POST["CommitCheck"]:0,
		"Substribe"=>isset($_POST["Substribe"])?$_POST["Substribe"]:0,
		"SubstribeUrl"=>trim($_POST["SubstribeUrl"]),
		"SubscribeQrcode"=>trim(htmlspecialchars($_POST["SubscribeQrcodeDetail"])),
		"Distribute_Share"=>isset($_POST["DistributeShare"])?$_POST["DistributeShare"]:0,		
		"Member_Share"=>isset($_POST["MemberShare"])?$_POST["MemberShare"]:0,		
		"Member_SubstribeScore"=>empty($_POST["Member_SubstribeScore"]) ? 0 : $_POST["Member_SubstribeScore"],
		"SubstribeScore"=>isset($_POST["SubstribeScore"])?$_POST["SubstribeScore"]:0,
		"ShareLogo"=>$_POST["ShareLogo"],
		"ShareIntro"=>$_POST["ShareIntro"],
		"DefaultAreaID"=>$areaid,
		'user_trans_switch' => (int)$_POST['user_trans_switch']
	);
	$Set=$DB->Set("shop_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	$flag=$flag&&$Set;
	$Data=array(
		"Reply_Keywords"=>$_POST["Keywords"],
		"Reply_PatternMethod"=>isset($_POST["PatternMethod"])?$_POST["PatternMethod"]:0
	);
	$Set=$DB->Set("wechat_keyword_reply",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Reply_Table='shop' and Reply_TableID=0 and Reply_Display=0");
	$flag=$flag&&$Set;
	$Material=array(
		"Title"=>$_POST["Title"],
		"ImgPath"=>$_POST["ImgPath"],
		"TextContents"=>"",
		"Url"=>"/api/".$_SESSION["Users_ID"]."/shop/"
	);
	$Data=array(
		"Material_Json"=>json_encode($Material,JSON_UNESCAPED_UNICODE)
	);

	$Set=$DB->Set("wechat_material",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Material_Table='shop/union' and Material_TableID=0 and Material_Display=0");
	$flag=$flag&&$Set;
	
	if($flag)
	{
		mysql_query("commit");
		echo '<script language="javascript">alert("设置成功");window.location="config.php";</script>';
	}else
	{
		mysql_query("roolback");
		echo '<script language="javascript">alert("设置失败");history.back();</script>';
	}
}else{
	$item = $DB->GetRs("users","Users_Sms","where Users_ID='".$_SESSION["Users_ID"]."'");
	$rsConfig = $setting->get_one();	
	$json = $material->get_one_bymodel("shop/union");
	$rsMaterial=json_decode($json['Material_Json'],true);
	if($rsConfig["DefaultAreaID"]>0){
		$region = new region($DB, $_SESSION["Users_ID"]);
		$areaids = $region->get_areaparent($rsConfig["DefaultAreaID"]);
	}
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
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js?t=1'></script>
<script type='text/javascript' src='/static/member/js/shop.js'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>


<script type='text/javascript' src="/static/js/select2.js"></script>
<script type="text/javascript" src="/static/js/location.js"></script>
<script type="text/javascript" src="/static/js/area.js"></script>
<link href="/static/css/select2.css" rel="stylesheet"/>
</head>

<script>
KindEditor.ready(function(K) {
	var editor = K.editor({
		uploadJson : '/member/upload_json.php?TableField=web_article',
		fileManagerJson : '/member/file_manager_json.php',
		showRemote : true,
		allowFileManager : true,
	});
	
	K('#LogoUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#Logo').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#Logo').val(url);
					K('#LogoDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
	
	K('#SubscribeQrcodeUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#SubscribeQrcodeDetail').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#SubscribeQrcodeDetail').val(url);
					K('#SubscribeQrcode').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
	
	K('#ShareLogoUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ShareLogo').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ShareLogo').val(url);
					K('#ShareLogoDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
	
	K('#ReplyImgUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ReplyImgPath').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ReplyImgPath').val(url);
					K('#ReplyImgDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
});

$(function() {
		$("#clearSubscribeQrcode").on('click', function () {
			$("#SubscribeQrcode").html('');
			$("#SubscribeQrcodeDetail").val('');
		})
	})
</script>
<style type="text/css">
#config_form img{width:100px; height:100px;}
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
   <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/setting/basic_menubar.php');?>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
	<script type='text/javascript' src="/third_party/jscolor/jscolor.js"></script> 
	<script language="javascript">$(document).ready(function(){
		showLocation(<?php echo empty($areaids["province"]) ? 0 : $areaids["province"];?>,<?php echo empty($areaids["city"]) ? 0 : $areaids["city"];?>,<?php echo empty($areaids["area"]) ? 0 : $areaids["area"];?>);
		global_obj.config_setcheck_init();		
		
	});</script>
    <div class="r_con_config r_con_wrap">
      <form id="config_form" action="config.php" method="post">
        <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="50%" valign="top"><h1><span class="fc_red">*</span> <strong>微商城名称</strong></h1>
              <input type="text" class="input" name="ShopName" value="<?php echo $rsConfig["ShopName"] ?>" maxlength="30" notnull /></td>
            <td width="50%" valign="top"><h1><strong>分销总开关</strong><span class="tips">（默认为开启状态,如果关闭后将不再显示分销相关功能和文字）</span></h1>
              <div class="input">
                <label>开启<input type="radio" <?php if(!empty($rsConfig["DisSwitch"])){?>checked <?php }?> value="1" name="DisSwitch"></label>
		<label>关闭<input type="radio" <?php if(empty($rsConfig["DisSwitch"])){?>checked <?php }?> value="0" name="DisSwitch"></label>&nbsp;&nbsp;
              </div></td>
          </tr>
		  <tr>
            <td width="50%" valign="top"><h1><strong>商家收款二维码一</strong><span class="tips">（添写格式：文字|位置）</span></h1>
              <input type="text" class="input" name="biz_qrcodeone" value="<?php echo $rsConfig["biz_qrcodeone"] ?>" maxlength="30" /></td>
			  <td width="50%" valign="top"><h1><strong>商家收款二维码二</strong><span class="tips">（添写格式：文字|位置）</span></h1>
              <input type="text" class="input" name="biz_qrcodetwo" value="<?php echo $rsConfig["biz_qrcodetwo"] ?>" maxlength="30" /></td>
           
          </tr>
          <tr>
            <td width="50%" valign="top"><h1><strong>订单确认</strong><span class="tips">（关闭后下订单可直接付款，无需经过卖家确认）</span></h1>
              <div class="input">
				<label>关闭<input type="radio" <?php if(!empty($rsConfig["CheckOrder"])){?>checked <?php }?> value="1" name="CheckOrder"></label>&nbsp;&nbsp;
				<label>开启<input type="radio" <?php if(empty($rsConfig["CheckOrder"])){?>checked <?php }?> value="0" name="CheckOrder"></label>
				<span class="tips">(只针对在线付款有效)</span>
              </div></td>
              <td width="50%" valign="top"><h1><strong>评论审核</strong><span class="tips">（关闭后客户评论可不经过审核直接显示在前台页面）</span></h1>
              <div class="input">
				<label>关闭<input type="radio" <?php if(!empty($rsConfig["Commit_Check"])){?>checked <?php }?> value="1" name="CommitCheck"></label>&nbsp;&nbsp;
				<label>开启<input type="radio" <?php if(empty($rsConfig["Commit_Check"])){?>checked <?php }?> value="0" name="CommitCheck"></label>
				<span class="tips"></span>
              </div>
              </td>
          </tr>
		  <tr>
            <td width="50%" valign="top">
             <h1><strong>一键拨号</strong>
                <input type="checkbox" name="CallEnable" value="1"<?php echo empty($rsConfig["CallEnable"])?"":" checked"; ?> />
                <span class="tips">启用</span></h1>
              <input type="text" class="input" name="CallPhoneNumber" value="<?php echo empty($rsConfig["CallPhoneNumber"])?"":$rsConfig["CallPhoneNumber"]; ?>" maxlength="20" />
            </td>
            <td width="50%" valign="top">
				<h1><span class="fc_red">*</span> <strong>图标切换颜色</strong></h1>
				<input type="text" class="input jscolor" name="icon_color" style="width: 150px;" value="<?php echo !empty($rsConfig['Icon_Color']) ? $rsConfig['Icon_Color'] : 'E30C0C';?>" />
            </td>
          </tr>
          
          <tr>
            <td width="50%" valign="top">
              <h1><strong>订单手机短信通知</strong>
                <input type="checkbox" name="SendSms" value="1"<?php echo empty($rsConfig["SendSms"])?"":" checked"; ?> />
                <span class="tips">启用（填接收短信的手机号）</span></h1>
              <input type="text" class="input" name="MobilePhone" style="width:120px" value="<?php echo $rsConfig["MobilePhone"] ?>" maxlength="11" /><span class="tips"> 短信剩余 <font style="color:red"><?php echo get_remain_sms();?></font> 条&nbsp;&nbsp;</span>
            </td>
			
			<td width="50%" valign="top">
			<h1><span class="fc_red">*</span> <strong>订单自动确认收货时间(单位是天)</strong></h1>
              <input type="text" class="input" name="Confirm_Time" value="<?php echo $rsConfig["Confirm_Time"]/86400 ?>" size="10" notnull />
            </td>
			
          </tr>
          <tr>            
           <td width="50%" valign="top">
               <h1><strong>商城关注提醒</strong>
                <input type="checkbox" name="Substribe" value="1"<?php echo empty($rsConfig["Substribe"])?"":" checked"; ?> />
                <span class="tips">启用(若用户未关注公众号，则提示用户关注)</span></h1>
              <input type="text" class="input" name="SubstribeUrl" value="<?php echo empty($rsConfig["SubstribeUrl"])?"":$rsConfig["SubstribeUrl"]; ?>" placeholder="请填写链接地址" />
				<div class="file" style="margin-top: 5px;">
					<input name="SubscribeQrcodeUpload" id="SubscribeQrcodeUpload" type="button" style="width:100px;" value="上传公众号二维码" />&nbsp;&nbsp;<input id="clearSubscribeQrcode" type="button" style="width:100px;" value="清除公众号二维码" /><br /><br />
					<div class="img" id="SubscribeQrcode">
						<?php echo $rsConfig && $rsConfig['SubscribeQrcode']<>'' ? '<img src="'.$rsConfig['SubscribeQrcode'].'" />' : ''?>

					</div>
					<input type="hidden" id="SubscribeQrcodeDetail" name="SubscribeQrcodeDetail" value="<?php echo $rsConfig && $rsConfig['SubscribeQrcode']<>'' ? $rsConfig['SubscribeQrcode'] : ''?>" />
				</div>
            </td>
            <td width="50%" valign="top" style="display:none;"><h1><strong>需要物流</strong><span class="tips">（关闭后下订单无需填写收货地址）</span></h1>
              <div class="input">
				<label>需要<input type="radio" <?php if(!empty($rsConfig["NeedShipping"])){?>checked <?php }?> value="1" name="NeedShipping"></label>&nbsp;&nbsp;
				<label>不需要<input type="radio" <?php if(empty($rsConfig["NeedShipping"])){?>checked <?php }?> value="0" name="NeedShipping"></label>
               </div>
            </td>
			<td width="50%" valign="top">
				<h1><span class="fc_red">*</span> <strong>底部菜单样式</strong></h1>
				<input type="radio"  name="Bottom_Style" value="0" <?php echo $rsConfig["Bottom_Style"] == 0? "checked":''; ?>/> 图标
                <input type="radio"  name="Bottom_Style" value="1" <?php echo $rsConfig["Bottom_Style"] == 1? "checked":''; ?>/> 图片
            </td>
          </tr>
		  <tr>
			<td  width="50%" valign="top">
               <h1><strong>会员首次关注获取积分</strong>
                <input type="checkbox" name="SubstribeScore" value="1"<?php echo empty($rsConfig["SubstribeScore"])?"":" checked"; ?> />
                <span class="tips">启用</span></h1>
              <input type="text" class="input" name="Member_SubstribeScore" style="width:50px" value="<?php echo empty($rsConfig["Member_SubstribeScore"])?"":$rsConfig["Member_SubstribeScore"]; ?>" />
            </td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
            <td width="50%" valign="top">
			  <h1><strong>logo</strong></h1>
                        <div id="card_style">
                            <div class="file">
                                <span class="fc_red">（请上传png透明格式）</span><br />
                                <span class="tips">&nbsp;&nbsp;尺寸建议：100*100px</span><br /><br />
                                <input name="LogoUpload" id="LogoUpload" type="button" style="width:80px;" value="上传图片" /><br /><br />
                                <div class="img" id="LogoDetail">
                                <?php echo $rsConfig && $rsConfig['ShopLogo']<>'' ? '<img src="'.$rsConfig['ShopLogo'].'" />' : ''?>
								
                                </div>
								<input type="hidden" id="Logo" name="Logo" value="<?php echo $rsConfig && $rsConfig['ShopLogo']<>'' ? $rsConfig['ShopLogo'] : ''?>" />
                            </div>
                            <div class="clear"></div>
                        </div>
            </td>
			<td width="50%" valign="top">
				<h1><strong>自定义分享图片</strong></h1>
                        <div id="card_style">
                            <div class="file">
                                <span class="tips">&nbsp;&nbsp;尺寸建议：100*100px</span><br /><br />
                                <input name="ShareLogoUpload" id="ShareLogoUpload" type="button" style="width:80px;" value="上传图片" /><br /><br />
                                <div class="img" id="ShareLogoDetail">
                                <?php echo $rsConfig && $rsConfig['ShareLogo']<>'' ? '<img src="'.$rsConfig['ShareLogo'].'" />' : ''?>
								
                                </div>
								<input type="hidden" id="ShareLogo" name="ShareLogo" value="<?php echo $rsConfig && $rsConfig['ShareLogo']<>'' ? $rsConfig['ShareLogo'] : ''?>" />
                            </div>
                            <div class="clear"></div>
                        </div>               
            </td>
          </tr>
		  
		  <tr>
            <td width="50%" valign="top">
			  <h1><strong>商城公告</strong></h1>
              <textarea name="ShopAnnounce"><?php echo $rsConfig["ShopAnnounce"] ?></textarea>
            </td>
			<td width="50%" valign="top">            
               <h1><strong>自定义分享语</strong></h1>
              <textarea name="ShareIntro"><?php echo $rsConfig["ShareIntro"] ?></textarea>
            </td>
          </tr>
           <tr>
            <td width="50%" valign="top">
			  <h1><strong>默认地区</strong></h1>
              <select name="Province" id="loc_province" style="width:120px" notnull>
                </select>
                <select name="City" id="loc_city" style="width:120px" notnull>
                </select>
                <select name="Area" id="loc_town" style="width:120px; display:none">
                </select>
            </td>
			<td width="50%" valign="top">
			<h1><span class="fc_red"></span> <strong>默认距离(单位km)</strong></h1>
              <input type="text" class="input" name="DefaultLng" value="<?=$rsConfig["DefaultLng"]?>" size="10"/>
            </td>
          </tr>
		  <tr>
				<td width="50%" valign="top"><h1><strong>会员转移开关</strong><span class="tips" style="color:#f00;">（默认为关闭状态,开启此开关后,会员在成为分销商之前如果扫描其他分销商二维码会变成其他分销商下级）</span></h1>
					<div class="input">
						<label>开启<input type="radio" <?php if(!empty($rsConfig["user_trans_switch"])){?>checked <?php }?> value="1" name="user_trans_switch"></label>
						<label>关闭<input type="radio" <?php if(empty($rsConfig["user_trans_switch"])){?>checked <?php }?> value="0" name="user_trans_switch"></label>&nbsp;&nbsp;
					</div></td>
					<!--<td width="50%" valign="top"><h1><strong>字眼</strong><span class="tips">（原字眼：佣金）</span></h1>
              <input type="text" class="input" name="show_t" value="<?php echo $rsConfig["show_t"] ?>" maxlength="30" /></td>-->
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
                    <input type="radio" name="PatternMethod" value="0"<?php echo empty($rsKeyword["Reply_PatternMethod"])?" checked":""; ?> />
                    精确匹配<span class="tips">（输入的文字和此关键词一样才触发）</span></div>
                  <div class="input">
                    <input type="radio" name="PatternMethod" value="1"<?php echo $rsKeyword["Reply_PatternMethod"]==1?" checked":""; ?> />
                    模糊匹配<span class="tips">（输入的文字包含此关键词就触发）</span></div>
                  <br />
                  <br />
                  <span class="fc_red">*</span> 图文消息标题<br />
                  <input type="text" class="input" name="Title" value="<?php echo $rsMaterial["Title"] ?>" maxlength="100" notnull />
                </div>
                <div class="m_right">
					<span class="fc_red">*</span> 图文消息封面<span class="tips">（大图尺寸建议：640*360px）</span><br />
                       <div class="file"><input name="ReplyImgUpload" id="ReplyImgUpload" type="button" style="width:80px;" value="上传图片" /></div><br />
                       <div class="img" id="ReplyImgDetail">
                       <?php echo $rsMaterial["ImgPath"] ? '<img src="'.$rsMaterial["ImgPath"].'" />' : '';?>
                    </div>
                </div>
                <div class="clear"></div>
              </div>
              <input type="hidden" id="ReplyImgPath" name="ImgPath" value="<?php echo $rsMaterial["ImgPath"] ?>" /></td>
          </tr>
        </table>
        <div class="submit">
          <input type="submit" name="submit_button" value="提交保存" />
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>