<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

require_once('vertify.php');
$rsConfig = $DB->GetRs("zhongchou_config","*","where usersid='{$UsersID}'");
if(!$rsConfig){
	header("location:config.php");
}

$itemid=empty($_REQUEST['itemid'])?0:$_REQUEST['itemid'];
$item=$DB->GetRs("zhongchou_project","*","where usersid='{$UsersID}' and itemid=".$itemid);
if(!$item){
	echo '<script language="javascript">alert("要修改的信息不存在！");window.location="project.php";</script>';
}

if($_POST){
	$Data=array(
	    "status"=>$_POST["status"]
	);
	$Flag=$DB->Set("zhongchou_project",$Data,"where usersid='{$UsersID}' and itemid=$itemid");
	if($Flag){
		echo '<script language="javascript">alert("修改成功");window.location="project.php";</script>';
	}else{
		echo '<script language="javascript">alert("修改失败");history.back();</script>';
	}
	exit;
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
KindEditor.options.cssData = 'body {color:#aaa;}';
KindEditor.ready(function(K) {
	K.create('textarea[name="Description"]', {
		themeType : 'simple',
		filterMode : false,
		uploadJson : '/member/upload_json.php?TableField=web_column',
		fileManagerJson : '/member/file_manager_json.php',
		allowFileManager : true,
    	readonlyMode : true,
		items : [
			'source', '|', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
			'removeformat', 'undo', 'redo', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link' , '|', 'preview']
	});
})
</script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
.r_con_form .rows .input .disabled,.disabled  { color:#aaa; } 
.img img { max-width:240px;width:240px; margin-left:5px;}

</style>
<div id="iframe_page">
  <div class="iframe_content">
    <script type='text/javascript' src='/static/member/js/zhongchou.js'></script>
    <div class="r_nav">
      <ul>
        <li class=""><a href="config.php">基本设置</a></li>
        <li class="cur"><a href="project.php">项目管理</a></li>
      </ul>
    </div>
    <div id="project" class="r_con_wrap"> 
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
      <script type='text/javascript' src='/static/js/plugin/daterangepicker/moment_min.js'></script>
      <link href='/static/js/plugin/daterangepicker/daterangepicker.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/daterangepicker/daterangepicker.js'></script> 
      <script language="javascript">$(document).ready(zhongchou_obj.project_edit);</script>
      <form id="form_submit" action="project_edit.php" class="r_con_form" method="post">
        <div class="rows">
          <label>项目名称</label>
          <span class="input">
          <input type="text" class="form_input disabled" name="Title" value="<?php echo $item["title"];?>" maxlength="100" size="35" readonly />
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>形象图片</label>
          <span class="input">
          <div class="img" id="ImgDetail">
          <?php echo $item["thumb"] ? '<img src="'.$item["thumb"].'" />' : '';?>
          </div>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>活动时间</label>
          <span class="input">
          <input name="Time" type="text" value="<?php echo date("Y-m-d H:i:s",$item["fromtime"])." - ".date("Y-m-d H:i:s",$item["totime"]) ?>" class="form_input disabled" size="42"  disabled/>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>筹款金额</label>
          <span class="input">
          <input name="Amount" value="<?php echo $item["amount"];?>" type="text" class="form_input disabled" size="15" maxlength="20" readonly> 元
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>简短介绍</label>
          <span class="input">
          <textarea name="Introduce" class="textarea disabled" readonly disabled><?php echo $item["introduce"];?></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>项目详情</label>
          <span class="input">
          <textarea  name="Description" id="Description" readonly><?php echo $item["description"];?></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>是否审核</label>
          <span class="input" style="font-size:12px;">
              <input type="radio" id="status_0" value="0" name="status"<?php echo $item["status"]==0 ? ' checked' : '';?> /><label for="status_0"> 待审核 </label>&nbsp;&nbsp;
              <input type="radio" id="status_1" value="1" name="status"<?php echo $item["status"]==1 ? ' checked' : '';?> /><label for="status_1"> 通过审核 </label>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" value="提交保存" name="submit_btn">
          <a href="#" onClick="history.go(-1);" class="btn_gray">返回</a></span>
          <div class="clear"></div>
        </div>        
        <input type="hidden" id="ImgPath" name="ImgPath" value="<?php echo $item["thumb"];?>" />
        <input type="hidden" name="itemid" value="<?php echo $itemid;?>" />
      </form>
    </div>
  </div>
</div>
</body>
</html>