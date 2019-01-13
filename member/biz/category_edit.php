<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/shop/unioncate.class.php');
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}

$unioncate = new unioncate($DB,$_SESSION["Users_ID"]);

$CategoryID=empty($_REQUEST['CategoryID'])?0:$_REQUEST['CategoryID'];
if($_POST){	
	$Data=array(
		"Category_Index"=>$_POST['Index'],
		"Category_Name"=>$_POST["Name"],
		"Category_ParentID"=>$_POST["ParentID"],
		"Category_Img"=>$_POST['ImgPath'],
	);
	$Flag=$unioncate->edit_cat($Data,$CategoryID);
	if($Flag){
		echo '<script language="javascript">alert("修改成功");window.location="category.php";</script>';
	}else{
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	}
	exit;
}else{
	$rsCategory=$unioncate->get_cat_one($CategoryID);
	if(!$rsCategory){
		echo "分类不存在";
		exit;
	}
	$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID=0 and Category_ID!=".$CategoryID." order by Category_Index asc,Category_ID asc";
	$lists = $unioncate->get_cate_list("Category_ID,Category_Name,Category_ParentID",$condition);
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
<link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/member/js/shop.js'></script>
    <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
    <script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
    <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script type='text/javascript'>
    window.onload = function () {
        shop_obj.category_init();
    };
    KindEditor.ready(function(K) {
        var editor = K.editor({
            uploadJson: '/member/upload_json.php?TableField=category_add',
            fileManagerJson: '/member/file_manager_json.php',
            showRemote: true,
            allowFileManager: true
        });
        K('#ImgUpload').click(function () {
            editor.loadPlugin('image', function () {
                editor.plugin.imageDialog({
                    clickFn: function (url, title, width, height, border, align) {
                        K('#ImgPath').val(url);
                        K('#ImgDetail').html('<img src="' + url + '" style="width:100px;height:100px"/>');
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
    
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/bizunion_menubar.php');?>
    <div id="products" class="r_con_wrap"> 
      <script type='text/javascript' src='/static/js/plugin/dragsort/dragsort-0.5.1.min.js'></script>
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
      <script language="javascript">//$(document).ready(shop_obj.products_category_init);</script>
      <div class="category">
        <div class="m_righter" style="margin-left:0px;">
          <form action="category_edit.php" name="category_form" id="category_form" method="post">
              <input name="CategoryID" type="hidden" value="<?php echo $rsCategory["Category_ID"] ?>">
            <h1>添加产品分类</h1>
             <div class="opt_item">
              <label>菜单排序：</label>
              <span class="input">
              <input type="text" name="Index" value="<?php echo $rsCategory["Category_Index"] ?>" class="form_input" size="5" maxlength="30" notnull />
              <font class="fc_red">*</font>请输入数字</span>
              <div class="clear"></div>
            </div>
            <div class="opt_item">
              <label>类别名称：</label>
              <span class="input">
              <input type="text" name="Name" value="<?php echo $rsCategory["Category_Name"] ?>" class="form_input" size="15" maxlength="30" notnull />
              <font class="fc_red">*</font></span>
              <div class="clear"></div>
            </div>
            <div class="opt_item">
              <label>隶属关系：</label>
              <span class="input">
              <select name='ParentID'>
                <option value='0'>─根节点─</option>
				<?php
                foreach($lists as $key=>$value){
                    echo '<option value="'.$value["Category_ID"].'"'.($rsCategory["Category_ParentID"]==$value["Category_ID"] ? " selected" : "").'>&nbsp;├'.$value["Category_Name"].'</option>';
                }
				?>
              </select>
              </span>
              <div class="clear"></div>
            </div>
            <div class="opt_item">
            	<label>分类图片</label>
                <div class="file">
                    <input id="ImgUpload" name="ImgUpload" type="button" style="width: 80px;" value="上传图片">
                    （图片建议尺寸：100px X 100px）
                </div>
                    <br /><br/>
                  <div class="img" id="ImgDetail"><img width="100" src="<?php echo empty($rsCategory["Category_Img"])?"/static/member/images/shop/nopic.jpg":$rsCategory["Category_Img"]; ?>" ></div>
                  <input type="hidden" id="ImgPath" name="ImgPath" value="<?=$rsCategory['Category_Img']?>" />
                   
                <div classs="pro-list-type">
                </div>
                <div class="clear"></div>
            </div>
            
            <div class="opt_item">
              <label></label>
              <span class="input">
              <input type="submit" class="btn_green btn_w_120" name="submit_button" value="修改分类" />
              <a href="javascript:void(0);" class="btn_gray" onClick="location.href='category.php'">返回</a></span>
              <div class="clear"></div>
            </div>
          </form>
        </div>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
</body>
</html>