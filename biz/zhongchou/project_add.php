<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

if($_POST){
	$amount = empty($_POST["Amount"]) ? 0 : floatval($_POST["Amount"]);
	if($amount<=0){
		echo '<script language="javascript">alert("筹款金额必须大于零");history.back();</script>';exit;
	}
	$Time=empty($_POST["Time"])?array(time(),time()):explode(" - ",$_POST["Time"]);
	$fromtime=strtotime($Time[0]);
	$totime=strtotime($Time[1]);
	$_POST['Description'] = str_replace('"','&quot;',$_POST['Description']);
	$_POST['Description'] = str_replace("'","&quot;",$_POST['Description']);
	$_POST['Description'] = str_replace('>','&gt;',$_POST['Description']);
	$_POST['Description'] = str_replace('<','&lt;',$_POST['Description']);
	$Data=array(
		"title"=>addslashes($_POST["Title"]),
		"thumb"=>addslashes($_POST["ImgPath"]),
		"fromtime"=>$fromtime,
		"totime"=>$totime,
		"introduce"=>addslashes($_POST["Introduce"]),
		"description"=>addslashes($_POST["Description"]),
		"amount"=>$amount,
		"addtime"=>time(),
		"usersid"=>$UsersID,
	    "Biz_ID"=>$BizID,
	    "status"=>0
	);
	
	$time = time();
	$mintime = intval($_POST['minTime']);
	$maxtime = intval($_POST['maxTime']);
	if(isset($mintime) && $mintime){
	    if($mintime>$Data['fromtime']){
	        showMsg("众筹开始时间不能小于活动时间","javascript:history.go(-1);");
	    }
	    if($maxtime<$Data['totime']){
	        showMsg("众筹结束时间不能大于活动结束时间","javascript:history.go(-1);");
	    }
	}
	if($Data['totime']<$time){
	    showMsg("众筹结束时间不能小于当前时间","javascript:history.go(-1);");
	}
	
	if($Data['fromtime']>$Data['totime']){
	    showMsg("众筹开始时间不能大于结束时间","javascript:history.go(-1);");
	}
	$Flag=$DB->Add("zhongchou_project",$Data);
	if($Flag){
		echo '<script language="javascript">alert("添加成功");window.location="project.php";</script>';
	}else{
		echo '<script language="javascript">alert("添加失败");history.back();</script>';
	}
	exit;
}

$rsActive = getLastActive(3);
$searchStartdate = "";
$searchEnddate = "";
if(!empty($rsActive)){
    $searchStartdate = date('Y-m-d H:i:s',$rsActive['starttime']);
    $searchEnddate = date('Y-m-d H:i:s',$rsActive['stoptime']);
}else{
    $searchStartdate = date('Y-m-d H:i:s',time());
    $searchEnddate = date('Y-m-d H:i:s',strtotime("+3 day",time()));
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.11.1.min.js'></script>
<script type='text/javascript' src='/static/js/jquery.validate.min.js'></script>
<script type='text/javascript' src='/static/js/jquery.validate.zh_cn.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script>
KindEditor.ready(function(K) {	
	var editor = K.editor({
		uploadJson : '/biz/upload_json.php?TableField=zhongchou',
		fileManagerJson : '/biz/file_manager_json.php',
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
<script type='text/javascript' src='/static/member/js/zhongchou.js'></script>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
.r_con_form .rows .input .error { color:#f00; }
.error,.fc_red { margin-left:20px;}
.fc_red_1 {color:#f00;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    
    <div class="r_nav">
      <ul>
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
      <form id="form_submit" action="project_add.php" class="r_con_form" method="post">
        <div class="rows">
          <label>项目名称</label>
          <span class="input">
          <input type="text" class="form_input" name="Title" value="" maxlength="100" size="35" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>形象图片</label>
          <span class="input"> <span class="upload_file">
          <div>
            <div class="up_input">
              <input type="button" id="ImgUpload" value="添加图片" style="width:80px;" />
            </div>
            <div class="tips">图片建议尺寸：640*400px</div>
            <div class="clear"></div>
          </div>
          <div class="img" id="ImgDetail"></div>
          </span> </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>活动时间</label>
          <span class="input">
          <input name="Time" type="text" value="<?=$searchStartdate." - ".$searchEnddate ?>" class="form_input" size="42" readonly notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>筹款金额</label>
          <span class="input">
          <input name="Amount" value="0.00" type="text" class="form_input" size="15" maxlength="20" > 元
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>简短介绍</label>
          <span class="input">
          <textarea name="Introduce" class="textarea"></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>项目详情</label>
          <span class="input">
          <textarea  name="Description" id="Description"></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" value="提交保存" name="submit_btn">
          <a href="javascript:window.history.go(-1);" class="btn_gray">返回</a></span>
          <div class="clear"></div>
        </div>        
        <input type="hidden" id="ImgPath" name="ImgPath" value="" />
        <input type="hidden" name="minTime" value="<?=isset($rsActive['starttime'])?$rsActive['starttime']:0 ?>">
        <input type="hidden" name="maxTime" value="<?=isset($rsActive['stoptime'])?$rsActive['stoptime']:0 ?>">
      </form>
    </div>
  </div>
</div>
</body>
</html>