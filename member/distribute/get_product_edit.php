<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$dis_level = get_dis_level($DB,$_SESSION['Users_ID']);
$GetID=empty($_REQUEST['GetID'])?0:$_REQUEST['GetID'];
$rsGet=$DB->GetRs("user_get_product","*","where Users_ID='".$_SESSION["Users_ID"]."' and Get_ID=".$GetID);
$rsConfigmenu = Dis_Config::find($_SESSION["Users_ID"]);
if($_POST)
{
	$Data=array(
		"Get_Name"=>$_POST['Name'],
		"Get_ImgPath"=>$_POST['ImgPath'],
		"Get_Qty"=>$_POST['Qty'],
		"Get_Shipping"=>$_POST['Shipping'],
		"Get_MyOrder"=>$_POST['MyOrder'],
		"shop_price"=>$_POST['shop_price'],
		"Get_Level_ID"=>$_POST['Level_ID'],
		"Get_BriefDescription"=>$_POST['BriefDescription']
	);
	$Flag=$DB->Set("user_get_product",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Get_ID=".$GetID);
	if($Flag)
	{
		echo '<script language="javascript">alert("修改成功");window.location="get_product.php";</script>';
	}else
	{
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	}
	exit;
}
$jcurid = 7;
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
		uploadJson : '/member/upload_json.php?TableField=user_Get',
		fileManagerJson : '/member/file_manager_json.php',
		allowFileManager : true,
		items : [
			'source', '|', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
			'removeformat', 'undo', 'redo', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link' , '|', 'preview']
	});
	var editor = K.editor({
		uploadJson : '/member/upload_json.php?TableField=user_Get',
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
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/distribute/distribute_menubar.php');?>
    <div id="Peas" class="r_con_wrap">
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
      <script language="javascript">$(document).ready(user_obj.gift_edit_init);</script>
      <form id="gift_edit_form" class="r_con_form" method="post" action="get_product_edit.php">
        <div class="rows">
          <label>商品名称</label>
          <span class="input">
          <input type="text" name="Name" value="<?php echo $rsGet['Get_Name'] ?>" class="form_input" size="35" maxlength="100" notnull />
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
          <div class="img" id="ImgDetail"><?php echo empty($rsGet["Get_ImgPath"])?'':'<img src="'.$rsGet["Get_ImgPath"].'" />' ?></div>
          </span> </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>分销商级别</label>
          <span class="input price">
		  <select name="Level_ID">
			<?php foreach($dis_level as $key=>$value){?>
				<option value="<?=$value['Level_ID']?>" <?php if($value['Level_ID'] == $rsGet['Get_Level_ID']){?> selected<?php }?> ><?=$value['Level_Name']?></option>
			<?php } ?>
		  </select>
		  </span>
          <div class="clear"></div>
        </div>
		 <div class="rows">
          <label>商品价格</label>
          <span class="input price">
          <input type="text" name="shop_price" value="<?php echo $rsGet['shop_price'] ?>" class="form_input" size="5" maxlength="10" />
          <font class="fc_red">*</font><span class="tips">商品单件价格或一斤价格</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>剩余数量</label>
          <span class="input price">
          <input type="text" name="Qty" value="<?php echo $rsGet['Get_Qty'] ?>" class="form_input" size="5" maxlength="10" notnull />
          <font class="fc_red">*</font> <span class="tips">商品领取完，将自动下架</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>需要物流</label>
          <span class="input price">
          <select name="Shipping">
            <option value='0'<?php echo $rsGet['Get_Shipping']==0?' selected':'' ?>>否</option>
            <option value='1'<?php echo $rsGet['Get_Shipping']==1?' selected':'' ?>>是</option>
          </select>
          <span class="tips">如果您提供的是本地化服务或商品是虚拟物品，请选择"否"</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>排序优先级</label>
          <span class="input">
          <select name="MyOrder">
            <option value='0'<?php echo $rsGet['Get_MyOrder']==0?' selected':'' ?>>默认</option>
            <option value='1'<?php echo $rsGet['Get_MyOrder']==1?' selected':'' ?>>一级优先</option>
            <option value='2'<?php echo $rsGet['Get_MyOrder']==2?' selected':'' ?>>二级优先</option>
            <option value='3'<?php echo $rsGet['Get_MyOrder']==3?' selected':'' ?>>三级优先</option>
            <option value='4'<?php echo $rsGet['Get_MyOrder']==4?' selected':'' ?>>四级优先</option>
            <option value='5'<?php echo $rsGet['Get_MyOrder']==5?' selected':'' ?>>五级优先</option>
          </select>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>简短介绍</label>
          <span class="input">
          <textarea name="BriefDescription" class="briefdesc" style="width:500px; height:300px;"><?php echo $rsGet['Get_BriefDescription'] ?></textarea>
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
        <input type="hidden" name="GetID" value="<?php echo $GetID ?>">
        <input type="hidden" id="ImgPath" name="ImgPath" value="<?php echo $rsGet['Get_ImgPath'] ?>" />
      </form>
    </div>
  </div>
</div>
</body>
</html>
