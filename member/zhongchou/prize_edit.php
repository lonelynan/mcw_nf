<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

require_once('vertify.php');
$rsConfig = $DB->GetRs("zhongchou_config","*","where usersid='{$UsersID}'");
if(!$rsConfig){
	header("location:config.php");
}
$prizeid=empty($_REQUEST['prizeid'])?0:$_REQUEST['prizeid'];
$item=$DB->GetRs("zhongchou_prize","*","where usersid='{$UsersID}' and prizeid=".$prizeid);
if(!$item){
	echo '<script language="javascript">alert("要修改的信息不存在！");window.location="prize.php?projectid='.$projectid.'";</script>';
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
<style>
.img img {width:120px;}
</style>
<script>
KindEditor.ready(function(K) {
	var editor = K.editor({
		uploadJson : '/member/upload_json.php?TableField=zhongchou',
		fileManagerJson : '/member/file_manager_json.php',
		showRemote : true,
		allowFileManager : true
	});
	K('#ImgUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ImgPath').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ImgPath').val(url);
					K('#ImgDetail').html('<img src="'+url+'" />');
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

<div id="iframe_page">
  <div class="iframe_content">
    <script type='text/javascript' src='/static/member/js/zhongchou.js'></script>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
    <script language="javascript">$(document).ready(zhongchou_obj.form_submit);</script>
    <div class="r_nav">
      <ul>
        <li class=""><a href="config.php">基本设置</a></li>
        <li class="cur"><a href="project.php">项目管理</a></li>
      </ul>
    </div>
    <div class="r_con_wrap">
      <form id="form_submit" class="r_con_form" method="post" >
      	<div class="rows">
          <label>支持金额</label>
          <span class="input">
          <?php echo $item["money"];?>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>赠品名称</label>
          <span class="input">
          <?php echo $item["title"];?>
          </span>
          <div class="clear"></div>
        </div>
		    <div class="rows">
          <label>赠品图片</label>
          <div class="img" id="ImgDetail">
           <?php echo $item["thumb"] ? '<img src="'.$item["thumb"].'" style="margin-top:10px;"/>' : '<img src="/static/member/images/shop/nopic.jpg" style="margin-top:10px;"/>';?>
          </div>
          </span> </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>赠品简介</label>
          <span class="input">
           <?php echo $item["introduce"];?>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>每人最多支持次数</label>
          <span class="input">
          <?php echo $item["maxtimes"];?>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <a href="#" onclick="history.go(-1);" class="btn_gray">返回</a></span>
          <div class="clear"></div>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>