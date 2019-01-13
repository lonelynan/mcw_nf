<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/shop/unioncate.class.php');

if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}

$unioncate = new unioncate($DB,$_SESSION["Users_ID"]);

if(isset($_GET["action"])){
	if($_GET["action"]=="del"){
		$Flag = $unioncate->delete_cat($_GET["CategoryID"]);
		if($Flag["status"]==1){
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			echo '<script language="javascript">alert("'.$Flag["msg"].'");history.back();</script>';
		}
		exit;
	}
}else{
	$condition = "where Users_ID='".$_SESSION["Users_ID"]."' order by Category_ParentID asc,Category_Index asc,Category_ID asc";
	$lists = $unioncate->get_cate_list("Category_ID,Category_Name,Category_ParentID",$condition);
}
$jcurid = 3;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href="/static/style.css" rel="stylesheet" type="text/css" />
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/bizunion_menubar.php');?>
    <div id="products" class="r_con_wrap"> 
      <script type='text/javascript' src='/static/js/plugin/dragsort/dragsort-0.5.1.min.js'></script>
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
      <script language="javascript">$(document).ready(shop_obj.products_category_init);</script>
      <div class="category">
        <div class="control_btn"><a href="category_add.php" class="btn_green btn_w_120">添加分类</a></div>
        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="mytable">
          <tr bgcolor="#f5f5f5">
            <td width="50" align="center"><strong>排序</strong></td>
            <td align="center"><strong>类别名称</strong></td>
            <td width="60" align="center"><strong>操作</strong></td>
          </tr>
          <?php
		  $i=0;
		  foreach($lists as $key=>$value){
			  $i++;
		  ?>
          <tr onMouseOver="this.bgColor='#D8EDF4';" onMouseOut="this.bgColor='';">
            <td>&nbsp;&nbsp;<?php echo $i; ?></td>
            <td><?php echo $value["Category_Name"]; ?></td>
            <td align="center"><a href="category_edit.php?CategoryID=<?php echo $value["Category_ID"]; ?>" title="修改"><img src="/static/member/images/ico/mod.gif" align="absmiddle" /></a> <a href="?action=del&CategoryID=<?php echo $value["Category_ID"]; ?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a></td>
          </tr>
          <?php
			if(!empty($value["child"])){
				foreach($value["child"] as $k=>$v){
		  ?>
          <tr onMouseOver="this.bgColor='#D8EDF4';" onMouseOut="this.bgColor='';">
            <td>&nbsp;&nbsp;<?php echo $i.'.'.($k+1); ?></td>
            <td>└─<?php echo $v["Category_Name"]; ?></td>
            <td align="center"><a href="category_edit.php?CategoryID=<?php echo $v["Category_ID"]; ?>" title="修改"><img src="/static/member/images/ico/mod.gif" align="absmiddle" /></a> <a href="?action=del&CategoryID=<?php echo $v["Category_ID"]; ?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a></td>
          </tr>
          <?php
				}
			}
		  }
		  ?>
        </table>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
</body>
</html>