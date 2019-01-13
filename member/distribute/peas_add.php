<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if($_POST)
{
	$Data=array(
		"Peas_Name"=>$_POST['Name'],
		"Peas_ImgPath"=>$_POST['ImgPath'],
		"Peas_Integral"=>$_POST['Integral'],
		"Peas_Qty"=>$_POST['Qty'],
		"Peas_Shipping"=>$_POST['Shipping'],
		"Peas_MyOrder"=>$_POST['MyOrder'],
		"Peas_BriefDescription"=>$_POST['BriefDescription'],
		"Peas_CreateTime"=>time(),
		"shop_price"=>$_POST['shop_price'],
		"Users_ID"=>$_SESSION["Users_ID"]
	);
	$Flag=$DB->Add("user_peas",$Data);
	if($Flag)
	{
		echo '<script language="javascript">alert("添加成功");window.location="peas.php";</script>';
	}else
	{
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	}
	exit;
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>微易宝</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script>
KindEditor.ready(function(K) {
	K.create('textarea[name="BriefDescription"]', {
		themeType : 'simple',
		filterMode : false,
		uploadJson : '/member/upload_json.php?TableField=user_Peas',
		fileManagerJson : '/member/file_manager_json.php',
		allowFileManager : true,
		items : [
			'source', '|', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
			'removeformat', 'undo', 'redo', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link' , '|', 'preview']
	});
	var editor = K.editor({
		uploadJson : '/member/upload_json.php?TableField=user_Peas',
		fileManagerJson : '/member/file_manager_json.php',
		showRemote : true,
		allowFileManager : true,
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
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/user.js'></script>
    <div class="r_nav">
      <ul>
        <li class="cur"> <a href="peas_orders.php">商品领取</a>
          <dl>
            <dd class="first"><a href="peas.php">商品管理</a></dd>
            <dd class=""><a href="peas_orders.php">领取订单管理</a></dd>
          </dl>
        </li>
      </ul>
    </div>
    <div id="Peas" class="r_con_wrap">
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
      <script language="javascript">$(document).ready(user_obj.Peas_edit_init);</script>
      <form id="Peas_edit_form" class="r_con_form" method="post" action="peas_add.php">
        <div class="rows">
          <label>商品名称</label>
          <span class="input">
          <input type="text" name="Name" value="" class="form_input" size="35" maxlength="100" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>商品图片</label>
          <span class="input"> <span class="upload_file">
          <div>
            <div class="up_input">
              <input type="button" id="ImgUpload" value="添加图片" style="width:80px;" />
            </div>
            <div class="tips">图片大小建议：640*360px</div>
            <div class="clear"></div>
          </div>
          <div class="img" id="ImgDetail"></div>
          </span> </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>领取所需豆豆</label>
          <span class="input price">
          <input type="text" name="Integral" value="0" class="form_input" size="5" maxlength="10" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
		 <div class="rows">
          <label>商品价格</label>
          <span class="input price">
          <input type="text" name="shop_price" value="0" class="form_input" size="5" maxlength="10" />
          <font class="fc_red">*</font><span class="tips">商品单件价格或一斤价格</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>剩余数量</label>
          <span class="input price">
          <input type="text" name="Qty" value="0" class="form_input" size="5" maxlength="10" notnull />
          <font class="fc_red">*</font> <span class="tips">商品领取完，将自动下架</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>需要物流</label>
          <span class="input price">
          <select name="Shipping">
            <option value='0'>否</option>
            <option value='1'>是</option>
          </select>
          <span class="tips">如果您提供的是本地化服务或商品是虚拟物品，请选择"否"</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>排序优先级</label>
          <span class="input">
          <select name="MyOrder">
            <option value='0'>默认</option>
            <option value='1'>一级优先</option>
            <option value='2'>二级优先</option>
            <option value='3'>三级优先</option>
            <option value='4'>四级优先</option>
            <option value='5'>五级优先</option>
          </select>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>简短介绍</label>
          <span class="input">
          <textarea name="BriefDescription" class="briefdesc" style="width:500px; height:300px;"></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" name="submit_button" value="提交保存" />
          <a href="" class="btn_gray">返回</a></span>
          <div class="clear"></div>
        </div>
        <input type="hidden" name="PeasID" value="0">
        <input type="hidden" id="ImgPath" name="ImgPath" value="" />
      </form>
    </div>
  </div>
</div>
</body>
</html>