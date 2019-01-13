?><?php
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}

$rsProfile=$DB->GetRs("user_profile","*","where Users_ID='".$_SESSION["Users_ID"]."'");
if(empty($rsProfile)){
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"]
	);
	$DB->Add("user_profile",$Data);
	$rsProfile=$DB->GetRs("user_profile","*","where Users_ID='".$_SESSION["Users_ID"]."'");
}
if($_POST){
	$Data = array(
		'Perm_Name' => $_POST['name'],
		'Perm_Picture' => $_POST['Logo'],
		'Perm_Url' => $_POST['http_url'],
		'Perm_Tyle' => $_POST['Tyle_IS']
	);
	$DB->Set("permission_config",$Data,'where (Users_ID="'.$_SESSION['Users_ID'].'" OR Users_ID= "") AND Permission_ID='.$_POST['Permission_ID']);
	
	echo '<script> parent.window.location.href="switch.php";
			   var index = parent.layer.getFrameIndex(window.name); //获取窗口索引
               parent.layer.close(index);</script>';
}else{
	$Permission_data = $DB->GetRs("permission_config","*",'where (Users_ID="'.$_SESSION['Users_ID'].'" OR Users_ID= "") AND Permission_ID ='.$_GET['Permission_ID']);
}
	
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
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script>
KindEditor.ready(function(K) {
	var editor = K.editor({
		uploadJson : '/member/upload_json.php?TableField=web_article',
		fileManagerJson : '/member/file_manager_json.php',
		showRemote : true,
		allowFileManager : true,
	});
	
	$(document).on('click', '.LogoUpload', function(){
		var log_num = $(this).next().val();
		var logo = '.Logo'+log_num ;
		var LogoDetail = '.LogoDetail'+log_num ;
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K(logo).val(),
				clickFn : function(url, title, width, height, border, align){
					K(logo).val(url);
					K(LogoDetail).html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
	
	K('#LogoUpload_er').click(function(){
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
})
</script>
<style>
	.sun_input{height: 28px;line-height: 28px; border: 1px solid #ddd;background: #fff;border-radius: 5px; padding: 0 5px;}
	.file_input{height: 30px;color: #fff;border: none;border-radius: 2px;background: url('/static/member/images/global/ok-btn-120-bg.jpg') no-repeat left top;cursor: pointer;width:80px;}
	.LogoUpload{float: left;}
	.LogoDetail{float: left;}
	.LogoDetail img{width:30px;height:30px;}
	.photo_config{width:110px;height:40px; margin:0 auto;}
	#Tyle_IS{height: 32px;border: 1px solid #ddd;padding: 5px;vertical-align: middle;border-radius: 5px;}
</style>
</head>

<body>
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
  	<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
    <link href='/static/member/css/shop.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
	<link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/user.js'></script>
	<script language="javascript">$(document).ready(user_obj.switch_init);</script>
    <div id="user_profile" class="r_con_wrap">
		<form class="r_con_form" action="" style="margin-top:10px" method="post">
		<input type="hidden" name="Permission_ID" value="<?=$_GET['Permission_ID']?>">
		 <div class="rows">
				<table border="0" cellpadding="5" cellspacing="0" style="width: 100%;text-align: center;" class="reverve_field_table">
					<thead>
						<tr>
							<td>标题</td>
							<td>图片</td>
							<td>URL</td>
							<td>类型</td>
						</tr>
					</thead>
					<tbody class="form_list">
						<tr>
							<td><input type="text" class="sun_input" name="name" value="<?=$Permission_data['Perm_Name']?>"></td>
							<td>
								<div class="photo_config">
									<input name="LogoUpload[]" class="file_input LogoUpload" type="button"  value="上传图标" />
									<input name="logo_num" type="hidden" value="">
									<div class="img LogoDetail">
										<img src="<?=$Permission_data['Perm_Picture']?>" style="width:30px;height:30px;">
									</div>
									<input type="hidden" class="Logo" name="Logo" value="<?php echo $Permission_data['Perm_Picture']; ?>" />
								</div>
							</td>
							<td>
							<?php if($Permission_data['Perm_Field'] != '0') {?>
								<input type="text" name="http_url" value="<?=$Permission_data['Perm_Url']?>" class="sun_input menu_url" size="30" maxlength="200" disabled="true" />
								<input type="hidden" name="http_url" value="<?=$Permission_data['Perm_Url']?>" class="sun_input menu_url" size="30" maxlength="200"  />
							<?php } else {?>
								<input type="text" name="http_url" value="<?=$Permission_data['Perm_Url']?>" class="sun_input menu_url" size="30" maxlength="200"  /><img src="/static/member/images/ico/search.png" style="width:22px; height:22px; margin:0px 0px 0px 5px; vertical-align:middle; cursor:pointer" id="btn_select_url" class="http_url_sun" key=""/>
							<?php }?>
							
							</td>
							<td>
								<?php if($Permission_data['Perm_Field'] != '0') {?>
									<select name="Tyle_IS" id="Tyle_IS">
										<?php if($Permission_data['Perm_Tyle'] == 1) {?>
											<option value="1">分销中心</option>
										<?php } else if ($Permission_data['Perm_Tyle'] == 2) {?>
											<option value="2">个人中心</option>
											<?php } else if ($Permission_data['Perm_Tyle'] == 3) {?>
											<option value="3">分销中心-二维码</option>
											<?php } else if ($Permission_data['Perm_Tyle'] == 4) {?>
											<option value="4">产品详情</option>
										<?php } else if ($Permission_data['Perm_Tyle'] == 5) { ?>
											<option value="5">提交订单</option>
										<?php } ?>
									</select>
								<?php } else {?>
									<select name="Tyle_IS" id="Tyle_IS">
										<option value="1" <?php if($Permission_data['Perm_Tyle'] == 1){?> selected <?php }?> >分销中心</option>
										<option value="2" <?php if($Permission_data['Perm_Tyle'] == 2){?> selected <?php }?>>个人中心</option>
										<option value="3" <?php if($Permission_data['Perm_Tyle'] == 3){?> selected <?php }?> >分销中心-二维码</option>
										<option value="4" <?php if($Permission_data['Perm_Tyle'] == 4){?> selected <?php }?>>产品详情</option>
										<option value="5" <?php if($Permission_data['Perm_Tyle'] == 5){?> selected <?php }?>>提交订单</option>
									</select>
								<?php }?>
								
							</td>
						
						</tr>
					</tbody>
				</table>
				
				<div class="clear"></div>
			</div>
			<div class="rows" style="height:30px;margin-top:10px; border:0px;">
				<input type="submit" class="btn_ok" style="margin: 0 auto; float:none;" name="submit_button" value="提交保存" />
			
			</div>
		</form>	  
    </div>
  </div>
</div>
</body>
</html>
<script>
	$('.form_add').click(function(){
		var num = $(".form_list tr").length;
		alert(num);
		var TelHtml='<tr><td><input type="text" class="sun_input" name="name[]" value=""></td><td><div class="photo_config"><input name="LogoUpload[]" class="file_input  LogoUpload" type="button"  value="上传图标" /><input name="logo_num" type="hidden" value="'+num+'"><div class="img LogoDetail LogoDetail'+num+'"></div><input type="hidden" class="Logo Logo'+num+'" name="Logo[]" value="" /></div></td><td><input type="text" class="sun_input" name="http_url[]" value=""></td><td><select name="Tyle_IS[]"><option value="1">分销中心</option><option value="2">个人中心</option></select></td><td><a href="javascript:void(0);" onclick="TelDel(this)"><img src="/static/member/images/ico/del.gif" /></a></td></tr>';
		$('.form_list tr:last').after(TelHtml);
	});
	function TelDel(DelDem){
		 $(DelDem).parent().parent().remove();
	}
	$(document).on('click', '.http_url_sun', function(){
		var key = $(this).attr('key');
		global_obj.create_layer('选择链接', '/member/material/sysurl_http.php?dialog=1&input=menu&key=' + key,1000,300);
		
	});
</script>