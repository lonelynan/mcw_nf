<?php
require_once('../global.php');
if ($rsBiz['is_agree'] !=1 || $rsBiz['is_auth'] !=2) {
        echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.top.location="/biz/index.php";</script>';
        exit;
    }
    if ($rsBiz['is_biz'] !=1) {
        echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.top.location="/biz/index.php";</script>';
        exit;
    }
    $Biz_ID=$_SESSION["BIZ_ID"];
    $bizInfo = $DB->getRs('biz','','where Biz_ID = '.$Biz_ID);
    if (time() > $bizInfo['expiredate']) {
            echo '<script language="javascript">alert("您的商家已经到期,请及时续费");window.top.location="/biz/authen/charge_man.php";</script>';
            exit;
    }
$CategoryID=empty($_REQUEST['CategoryID'])?0:$_REQUEST['CategoryID'];
$rsCategory=$DB->GetRs("biz_category","*","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Category_ID=".$CategoryID);
if($_POST){
	$Data=array(
		"Category_Index"=>intval($_POST['Index']),
		"Category_Name"=>$_POST["Name"],
		"Category_ParentID"=>$_POST["ParentID"],
        "Category_img"=>(string)$_POST['ImgPath']
	);
	$Flag=$DB->Set("biz_category",$Data,"where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Category_ID=".$CategoryID);
	if($Flag)
	{
		echo '<script language="javascript">alert("修改成功");window.location="category.php";</script>';
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
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type="text/javascript" src="/third_party/uploadify/jquery.uploadify.min.js"></script>
<link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
    <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
    <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src='/biz/js/shop.js'></script>
<script type='text/javascript'>
$(document).ready(function(){
	shop_obj.category_init();
});
</script>
    <script type='text/javascript'>
        window.onload = function () {
            shop_obj.category_init();
        };
        KindEditor.ready(function(K) {
            var editor = K.editor({
                uploadJson : '/member/upload_json.php?TableField=category_add',
                fileManagerJson : '/member/file_manager_json.php',
                showRemote : true,
                allowFileManager : true
            });
            K('#ImgUpload').click(function(){
                editor.loadPlugin('image', function() {
                    editor.plugin.imageDialog({
                        clickFn : function(url, title, width, height, border, align) {
                            K('#ImgPath').val(url);
                            K('#ImgDetail').html('<img src="'+url+'" style="width:100px;height:100px"/>');
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
    
    <div class="r_nav">
      <ul>
        <li class="cur"><a href="category.php">自定义分类</a></li>
        <li><a href="category_add.php">添加分类</a></li>
      </ul>
    </div>
    <div id="products" class="r_con_wrap"> 
      <script type='text/javascript' src='/static/js/plugin/dragsort/dragsort-0.5.1.min.js'></script>
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
      <div class="category">
        <div class="m_righter" style="margin-left:0px;">
          <form action="category_edit.php" name="category_form" id="category_form" method="post">
              <input name="CategoryID" type="hidden" value="<?php echo $rsCategory["Category_ID"] ?>">
            <h1>修改产品分类</h1>
             <div class="opt_item">
              <label>菜单排序：</label>
              <span class="input">
              <input type="text" name="Index" value="<?php echo $rsCategory["Category_Index"] ?>" class="form_input" size="5" maxlength="30" notnull />
              <font class="fc_red">*</font>请输入数字</span>
              <div class="clear"></div>
            </div>
            <div class="opt_item">
              <label>上级分类：</label>
              <span class="input">
              <select name='ParentID'>
                <option value='0'>一级分类</option>
				<?php $DB->get("biz_category","*","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Category_ID<>".$CategoryID." and Category_ParentID=0 order by Category_Index asc");
                while($rsPCategory=$DB->fetch_assoc()){
                    echo '<option value="'.$rsPCategory["Category_ID"].'"'.($rsPCategory["Category_ID"]==$rsCategory["Category_ParentID"]?" selected":"").'>&nbsp;├'.$rsPCategory["Category_Name"].'</option>';
                }?>
              </select>
              </span>
              <div class="clear"></div>
            </div>

              <div class="opt_item">
                  <label>分类图片</label>
                  <div class="file">
                      <input id="ImgUpload" name="ImgUpload" type="button" style="width: 80px;" value="上传图片">
                      (图片尺寸100px*100px)
                  </div>
                  <br /><br/>
                  <div class="img" id="ImgDetail"><img width="100" src="<?php echo empty($rsCategory["Category_img"])?"/static/member/images/shop/nopic.jpg":$rsCategory["Category_img"]; ?>" ></div>
                  <input type="hidden" id="ImgPath" name="ImgPath" value="<?php echo empty($rsCategory["Category_img"])?"/static/member/images/shop/nopic.jpg":$rsCategory["Category_img"]; ?>" />

                  <div classs="pro-list-type">
                  </div>
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
              <label></label>
              <span class="input">
              <input type="submit" class="btn_green btn_w_120" name="submit_button" value="修改分类" /></span>
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