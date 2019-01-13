<?php 
$DB->showErr=false;
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}
if($_POST)
{

	$UserLevel=$_POST['UserLevel'];

	foreach($UserLevel as $k=>$v){
		if($k>0){
			if(empty($v['UpCost'])){
				unset($UserLevel[$k]);
			}elseif(empty($v['Name'])){
				unset($UserLevel[$k]);
			}elseif(isset($UserLevel[$k-1]['UpCost'])){
				if($v['UpCost']<$UserLevel[$k-1]['UpCost']){
					unset($UserLevel[$k]);
					$uplevetip = 1;
				}
			}else{
				unset($UserLevel[$k]);
			}
		}
	}
	if (isset($uplevetip)) {
			echo '<script language="javascript">alert("高等级消费要大于低等级消费！");window.open("user_level.php","_self");</script>';
		exit();
		}
		
	$Data=array(
		"UserLevel"=>json_encode($UserLevel,JSON_UNESCAPED_UNICODE)
	);
	$flag = $DB->Set("user_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	if($flag){		
		echo '<script language="javascript">alert("修改成功！");window.open("user_level.php","_self");</script>';
		exit();
	}else{
		echo '<script language="javascript">alert("修改失败！");window.location="javascript:history.back()";</script>';
		exit();
	}
}

$rsConfig=$DB->GetRs("user_config","UserLevel","where Users_ID='".$_SESSION["Users_ID"]."'");
if(empty($rsConfig)){
	header("location:config.php");
}else{
	if(empty($rsConfig['UserLevel'])){
		$UserLevel[0]=array(
			"Name"=>"普通会员",
			"UpCost"=>0,
			"Discount"=>0,
			"ImgPath"=>""
			
		);
		$Data=array(
			"UserLevel"=>json_encode($UserLevel,JSON_UNESCAPED_UNICODE)
		);
		$DB->Set("user_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	}else{
		$UserLevel=json_decode($rsConfig['UserLevel'],true);
	}
}
$jcurid = 2;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>好分销</title>
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
		uploadJson : '/member/upload_json.php?TableField=app_wedding',
		fileManagerJson : '/member/file_manager_json.php',
		showRemote : true,
		allowFileManager : true,
	});
	K('#ImgUpload_0').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ImgPath_0').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ImgPath_0').val(url);
					K('#ImgDetail_0').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
	K('#ImgUpload_1').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ImgPath_1').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ImgPath_1').val(url);
					K('#ImgDetail_1').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
	K('#ImgUpload_2').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ImgPath_2').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ImgPath_2').val(url);
					K('#ImgDetail_2').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
	K('#ImgUpload_3').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ImgPath_3').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ImgPath_3').val(url);
					K('#ImgDetail_3').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
	K('#ImgUpload_4').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ImgPath_4').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ImgPath_4').val(url);
					K('#ImgDetail_4').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
        K('#ImgUpload_5').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ImgPath_5').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ImgPath_5').val(url);
					K('#ImgDetail_5').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
	K('#ImgUpload_6').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ImgPath_6').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ImgPath_6').val(url);
					K('#ImgDetail_6').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
})
</script>
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
    <script type='text/javascript' src='/static/member/js/user.js?<?= time();?>'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/user/user_menubar.php');?>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <script language="javascript">$(document).ready(user_obj.user_level_init);</script>
    <div id="user_level" class="r_con_config r_con_wrap">
      <form id="level_form" method="post" action="user_level.php">
        <table border="0" cellpadding="5" cellspacing="0" class="level_table">
          <thead>
            <tr>
              <td width="10%">序号</td>
              <td width="15%">会员等级名称</td>
              <td width="10%">升级所需消费额(元)</td>
              <td width="15%">优惠额度(%)<span class="fc_red">&nbsp;&nbsp;[10%即为9折]</span></td>
              <td width="30%">会员卡背景图</td>
              <td width="20%">操作</td>
            </tr>
          </thead>
          <tbody>
            <tr style="display:;" FieldType="text">
              <td>1</td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[0]['Name'])?'':$UserLevel[0]['Name'] ?>" name="UserLevel[0][Name]" notnull /></td>
              <td>0
                <input type="hidden" class="form_input" value="0" name="UserLevel[0][UpCost]" />
              </td>
              <td>
              0
              	<input type="hidden"  class="form_input" value="0" name="UserLevel[0][Discount]"/>
              </td>
              <td>
              <label>
                  <input type="button" id="ImgUpload_0" value="选择图片" style="width:80px;" />
                </label>
                <span class="pic" id="ImgDetail_0"></span>
                <input type="hidden" id="ImgPath_0" name="UserLevel[0][ImgPath]" value="<?php echo empty($UserLevel[0]['ImgPath'])?'':$UserLevel[0]['ImgPath'] ?>" /></td>
              <td><a href="javascript:void(0);" class="input_add"><img src="/static/member/images/ico/add.gif" /></a></td>
            </tr>
            <tr style="display:<?php echo empty($UserLevel[1])?'none':'' ?>;" FieldType="text">
              <td>2</td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[1]['Name'])?'':$UserLevel[1]['Name'] ?>" name="UserLevel[1][Name]"  /></td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[1]['UpCost'])?'':$UserLevel[1]['UpCost'] ?>" name="UserLevel[1][UpCost]" /></td>
               <td>
               
              	<input type="text"  class="form_input" value="<?php echo empty($UserLevel[1]['Discount'])?'':$UserLevel[1]['Discount'] ?>" name="UserLevel[1][Discount]"/>
              </td>
              <td><label>
                  <input type="button" id="ImgUpload_1" value="选择图片" style="width:80px;" />
                </label>
                <span class="pic" id="ImgDetail_1"></span>
                <input type="hidden" id="ImgPath_1" name="UserLevel[1][ImgPath]" value="<?php echo empty($UserLevel[1]['ImgPath'])?'':$UserLevel[1]['ImgPath'] ?>" /></td>
              <td><a href="javascript:void(0);" class="input_del"><img src="/static/member/images/ico/del.gif" /></a></td>
            </tr>
            <tr style="display:<?php echo empty($UserLevel[2])?'none':'' ?>;" FieldType="text">
              <td>3</td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[2]['Name'])?'':$UserLevel[2]['Name'] ?>" name="UserLevel[2][Name]"  /></td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[2]['UpCost'])?'':$UserLevel[2]['UpCost'] ?>" name="UserLevel[2][UpCost]" /></td>
              <td>         
              	<input type="text"  class="form_input" value="<?php echo empty($UserLevel[2]['Discount'])?'':$UserLevel[2]['Discount'] ?>" name="UserLevel[2][Discount]"/>
              </td>
              <td><label>
                  <input type="button" id="ImgUpload_2" value="选择图片" style="width:80px;" />
                </label>
                <span class="pic" id="ImgDetail_2"></span>
                <input type="hidden" id="ImgPath_2" name="UserLevel[2][ImgPath]" value="<?php echo empty($UserLevel[2]['ImgPath'])?'':$UserLevel[2]['ImgPath'] ?>" /></td>
              <td><a href="javascript:void(0);" class="input_del"><img src="/static/member/images/ico/del.gif" /></a></td>
            </tr>
            <tr style="display:<?php echo empty($UserLevel[3])?'none':'' ?>;" FieldType="text">
              <td>4</td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[3]['Name'])?'':$UserLevel[3]['Name'] ?>" name="UserLevel[3][Name]"  /></td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[3]['UpCost'])?'':$UserLevel[3]['UpCost'] ?>" name="UserLevel[3][UpCost]" /></td>
               <td>         
              	<input type="text"  class="form_input" value="<?php echo empty($UserLevel[3]['Discount'])?'':$UserLevel[3]['Discount'] ?>" name="UserLevel[3][Discount]"/>
              </td>
              <td><label>
                  <input type="button" id="ImgUpload_3" value="选择图片" style="width:80px;" />
                </label>
                <span class="pic" id="ImgDetail_3"></span>
                <input type="hidden" id="ImgPath_3" name="UserLevel[3][ImgPath]" value="<?php echo empty($UserLevel[3]['ImgPath'])?'':$UserLevel[3]['ImgPath'] ?>" /></td>
              <td><a href="javascript:void(0);" class="input_del"><img src="/static/member/images/ico/del.gif" /></a></td>
            </tr>
            <tr style="display:<?php echo empty($UserLevel[4])?'none':'' ?>;" FieldType="text">
              <td>5</td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[4]['Name'])?'':$UserLevel[4]['Name'] ?>" name="UserLevel[4][Name]"  /></td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[4]['UpCost'])?'':$UserLevel[4]['UpCost'] ?>" name="UserLevel[4][UpCost]" /></td>
              <td>         
              	<input type="text"  class="form_input" value="<?php echo empty($UserLevel[4]['Discount'])?'':$UserLevel[4]['Discount'] ?>" name="UserLevel[4][Discount]"/>
              </td>
              <td><label>
                  <input type="button" id="ImgUpload_4" value="选择图片" style="width:80px;" />
                </label>
                <span class="pic" id="ImgDetail_4"></span>
                <input type="hidden" id="ImgPath_4" name="UserLevel[4][ImgPath]" value="<?php echo empty($UserLevel[4]['ImgPath'])?'':$UserLevel[4]['ImgPath'] ?>" /></td>
              <td><a href="javascript:void(0);" class="input_del"><img src="/static/member/images/ico/del.gif" /></a></td>
            </tr>
            <tr style="display:<?php echo empty($UserLevel[5])?'none':'' ?>;" FieldType="text">
              <td>6</td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[5]['Name'])?'':$UserLevel[5]['Name'] ?>" name="UserLevel[5][Name]"  /></td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[5]['UpCost'])?'':$UserLevel[5]['UpCost'] ?>" name="UserLevel[5][UpCost]" /></td>
              <td>         
              	<input type="text"  class="form_input" value="<?php echo empty($UserLevel[5]['Discount'])?'':$UserLevel[5]['Discount'] ?>" name="UserLevel[5][Discount]"/>
              </td>
              <td><label>
                  <input type="button" id="ImgUpload_5" value="选择图片" style="width:80px;" />
                </label>
                <span class="pic" id="ImgDetail_5"></span>
                <input type="hidden" id="ImgPath_5" name="UserLevel[5][ImgPath]" value="<?php echo empty($UserLevel[5]['ImgPath'])?'':$UserLevel[5]['ImgPath'] ?>" /></td>
              <td><a href="javascript:void(0);" class="input_del"><img src="/static/member/images/ico/del.gif" /></a></td>
            </tr>
	    <tr style="display:<?php echo empty($UserLevel[6])?'none':'' ?>;" FieldType="text">
              <td>7</td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[6]['Name'])?'':$UserLevel[6]['Name'] ?>" name="UserLevel[6][Name]"  /></td>
              <td><input type="text" class="form_input" value="<?php echo empty($UserLevel[6]['UpCost'])?'':$UserLevel[6]['UpCost'] ?>" name="UserLevel[6][UpCost]" /></td>
              <td>         
              	<input type="text"  class="form_input" value="<?php echo empty($UserLevel[6]['Discount'])?'':$UserLevel[6]['Discount'] ?>" name="UserLevel[6][Discount]"/>
              </td>
              <td><label>
                  <input type="button" id="ImgUpload_6" value="选择图片" style="width:80px;" />
                </label>
                <span class="pic" id="ImgDetail_6"></span>
                <input type="hidden" id="ImgPath_6" name="UserLevel[6][ImgPath]" value="<?php echo empty($UserLevel[6]['ImgPath'])?'':$UserLevel[6]['ImgPath'] ?>" /></td>
              <td><a href="javascript:void(0);" class="input_del"><img src="/static/member/images/ico/del.gif" /></a></td>
            </tr>
          </tbody>
        </table>
        <div class="blank20"></div>
        <div class="submit">
          <input type="submit" name="submit_button" value="提交保存" />
        </div>
        <input type="hidden" name="action" value="user_level">
      </form>
    </div>
  </div>
</div>
</body>
</html>