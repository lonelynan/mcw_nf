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
if(isset($_GET["action"])){
	if($_GET["action"]=="switch"){
		
		$id_array = array_filter(explode(',',$_GET['id_array']));
		$status_array = array_filter(explode(',',$_GET['status_array']));
		$type_array = array_filter(explode(',',$_GET['Type_IS']));
		$switch_array = array();
		foreach($id_array as $key=>$value){
			if($type_array[$key] == 1){ // 1是分销中心 2个人中心
				$switch_array[1][$key][] = $value;
				$switch_array[1][$key][] = $status_array[$key];
			}elseif($type_array[$key] == 2){
				$switch_array[2][$key][] = $value;
				$switch_array[2][$key][] = $status_array[$key];
			}
			
		}
		$switch_data = json_encode($switch_array,JSON_UNESCAPED_UNICODE);
			
		$Flag = $DB->GetRs("permission_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
		if(empty($Flag)){
			$Data=array(				
				'Users_ID'=>$_SESSION["Users_ID"],				
				'Permission_Data'=>$switch_data,
				'Create_Time'=>time()
			);
			$DB->Add('permission_config',$Data);
		}else{
			$Data=array(				
				'Users_ID'=>$_SESSION["Users_ID"],				
				'Permission_Data'=>$switch_data
			);
			$DB->Set("permission_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
		}
		$Data=array("status"=>1);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}elseif($_GET["action"]=="get_Perm_Name"){
		
		$Data=array(					
			'Perm_Name'=>$_GET['Perm_Name']
		);
		$DB->Set("permission_config",$Data,"where Permission_ID=".$_GET["Perm_ID"]);
		$Data=array("status"=>1);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}elseif($_GET["action"]=="get_delete"){
		$Data=array(					
			'Is_Delete' => 1
		);
		$DB->Set("permission_config",$Data,"where Permission_ID=".$_GET["Perm_ID"]);
		$Data=array("status"=>1);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}elseif($_GET["action"]=="get_Withdraw"){
		$Data=array(					
			'Perm_On' => $_GET['Perm_On']
		);
		$DB->Set("permission_config",$Data,"where Permission_ID=".$_GET["Perm_ID"]);
		$Data=array("status"=>1);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
}

if($_POST){
	foreach($_POST['name'] as $key=>$value){
		$Data = array(
			'Users_ID' => $_SESSION["Users_ID"],
			'Perm_Name' => $value,
			'Perm_Picture' => $_POST['Logo'][$key],
			'Perm_Url' => $_POST['http_url'][$key],
			'Perm_Tyle' => $_POST['Tyle_IS'][$key],
		);
		$DB->Add("permission_config",$Data);
	}
}

require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/defaut_switch.php');

	$perm_config = array();
	$rs = $DB->Get('permission_config','*','where Users_ID="'.$_SESSION['Users_ID'].'" AND Is_Delete = 0  order by Perm_On desc');
	while($rs = $DB->fetch_assoc()){
		$perm_config[] = $rs;
	}
	$switchplace = array('非法操作','分销中心','个人中心','分销中心-二维码','产品详情','提交订单');
	$jcurid = 7;
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
	.form_select_sun{height:32px;border:1px solid #ddd;padding:5px;vertical-align:middle;border-radius: 5px;}
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
	<link href='/static/member/css/wechat.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/wechat.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/setting/switch_menubar.php');?>
	<link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/user.js'></script>
	<script language="javascript">$(document).ready(user_obj.switch_init);</script>
    <div id="user_profile" class="r_con_wrap">
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
		  <tr>
            <tr>
				<td>标题</td>
				<td>图片</td>
				<td>URL</td>
				<td>类型</td>
				<td>操作</td>
			</tr>
          </tr>
        </thead>
        <tbody>
		<?php foreach($perm_config as $ky=>$value){?>
				<input type="hidden" name="" class="Permission_ID" value="<?=$value['Permission_ID']?>">
				<td><?=$value['Perm_Name']?></td> 
				<td>
					<div class="photo_config">
						<?php if($value['Perm_Field'] == 'withdraw') {?>
						<?php } else {?>
							<img src="<?=$value['Perm_Picture']?>" style="width:30px;height:30px;">
						<?php }?>
					</div>
				</td>
				<td><?=$value['Perm_Url']?></td>
				<td><?=$switchplace[$value['Perm_Tyle']]?></td>
				<td>
				<?php if($value['Perm_On'] == 1){?>
					<img src="/static/member/images/ico/off.gif" class="Withdraw" Perm_On="0"  Status="<?=$value['Permission_ID']?>" />
				<?php }else{?>
					<img src="/static/member/images/ico/on.gif" class="Withdraw" Perm_On="1" Status="<?=$value['Permission_ID']?>" />
				<?php }?>
				<img src="/static/member/images/ico/xiugai.png" style="width:25px;height:25px;" class="modification" Permission_ID="<?=$value['Permission_ID']?>" />
				<?php if(empty($value['Perm_Field'])){?>
					<img src="/static/member/images/ico/del.gif" style="width:25px;height:25px;" class="deleted" Permission_ID="<?=$value['Permission_ID']?>"/>
				<?php }?>
				</td>
            </tr>
		<?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
<script>
	$('.form_add').click(function(){
		var num = $(".form_list tr").length;
		var TelHtml='<tr><td><input type="text" class="sun_input" name="name[]" value=""></td><td><div class="photo_config"><input name="LogoUpload[]" class="file_input  LogoUpload" type="button"  value="上传图标" /><input name="logo_num" type="hidden" value="'+num+'"><div class="img LogoDetail LogoDetail'+num+'"></div><input type="hidden" class="Logo Logo'+num+'" name="Logo[]" value="" /></div></td><td><input type="text" name="http_url[]" value="" class="sun_input menu_url'+num+'" size="30" maxlength="200" /><img src="/static/member/images/ico/search.png" style="width:22px; height:22px; margin:0px 0px 0px 5px; vertical-align:middle; cursor:pointer" id="btn_select_url" class="http_url_sun" key="'+num+'"/></td><td><select name="Tyle_IS[]"><option value="1">分销中心</option><option value="2">个人中心</option></select></td><td><a href="javascript:void(0);" onclick="TelDel(this)"><img src="/static/member/images/ico/del.gif" /></a></td></tr>';
		$('.form_list tr:last').after(TelHtml);
	});
	function TelDel(DelDem){
		 $(DelDem).parent().parent().remove();
	}
	/*$(".Perm_Name").keydown(function() {
		if (event.keyCode == "13") {//keyCode=13是回车键
			var Perm_ID = $(this).parent().prev().val();
			var Perm_Name = $(this).val();
			$.get('?','action=get_Perm_Name&Perm_ID='+Perm_ID+'&Perm_Name='+Perm_Name,function(data){
				if(data.status==1){					
					alert('修改成功！');
				}else{
					alert('修改失败！');
				}
			},'json');
        }
	});*/
	$(".deleted").click(function(){
		var Perm_ID = $(this).attr('Permission_ID');
		if(confirm('您确定要删除吗？')){
			$.get('?','action=get_delete&Perm_ID='+Perm_ID,function(data){
				if(data.status == 1){					
					alert('删除成功！');
					window.location.href= "";
				}else{
					alert('删除失败！');
				}
			},'json');	
		}
	});
	$(".Withdraw").click(function(){
		var Perm_ID = $(this).attr('Status');
		var Perm_On = $(this).attr('Perm_On');
		$.get('?','action=get_Withdraw&Perm_ID='+Perm_ID+'&Perm_On='+Perm_On,function(data){
			if(data.status == 1){					
				alert('修改成功！');
				window.location.href= "";
			}else{
				alert('修改失败！');
			}
		},'json');	
	});
	$('.modification').click(function(){
		var Permission_ID = $(this).attr('Permission_ID');
		global_obj.create_layer('选择链接', '/member/shop/setting/switch_xiug.php?dialog=1&Permission_ID='+Permission_ID,1100,600);
	});
	$(document).on('click', '.http_url_sun', function(){
		var key = $(this).attr('key');
		global_obj.create_layer('选择链接', '/member/material/sysurl_http.php?dialog=1&input=menu&key=' + key,900,500);
		
	});
	
	
</script>



