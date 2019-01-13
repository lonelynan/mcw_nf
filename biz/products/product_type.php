<?php 
require_once('../global.php');
if ($rsBiz['is_agree'] !=1 || $rsBiz['is_auth'] !=2) {
        echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.location="/biz/index.php";</script>';
        exit;
    }
    if ($rsBiz['is_biz'] !=1) {
        echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.location="/biz/index.php";</script>';
        exit;
    }
    $Biz_ID=$_SESSION["BIZ_ID"];
    $bizInfo = $DB->getRs('biz','','where Biz_ID = '.$Biz_ID);
    if (time() > $bizInfo['expiredate']) {
            echo '<script language="javascript">alert("您的商家已经到期,请及时续费");window.location="/biz/authen/charge_man.php";</script>';
            exit;
    }
//获取分类列表
$lists = array();
$rsTypes = $DB->get("shop_product_type","*","where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID='".$_SESSION["BIZ_ID"]."' order by Type_Index asc");
$lists = $DB->toArray($rsTypes);

foreach ($lists as $key=>$val)
{
  	$lists[$key]['Attr_Group'] = strtr($val['Attr_Group'], array("\r" => '', "\n" => ", "));
}

if(isset($_GET["action"]))
{
	if($_GET["action"]=="del")
	{
		$rsattrnum = $DB->GetRs("shop_product_type","attrnum","where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Type_ID=".$_GET["TypeID"]);
		if(!$rsattrnum){
		echo '<script language="javascript">alert("非法操作！");history.back();</script>';exit;
		}else{
		if ($rsattrnum['attrnum'] > 0) {
		echo '<script language="javascript">alert("类型有属性关联不能删除！");history.back();</script>';exit;	
		}
		}
		$rsPro = $DB->GetRs("shop_products","Products_Type","where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and Products_Type=".$_GET['TypeID']);
		if($rsPro){
		echo '<script language="javascript">alert("有产品在用不能删除！");history.back();</script>';exit;
		}
		$Flag=$DB->Del("shop_product_type","Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and Type_ID=".$_GET["TypeID"]);
		
		if($Flag)
		{
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else
		{
			echo '<script language="javascript">alert("删除失败");history.back();</script>';
		}
		
		exit;
	}
}
function get_type($typeid){
	global $DB;
	$r = $DB->Get("shop_attribute","Attr_Name","where Type_ID='".$typeid."' and Biz_ID='".$_SESSION["BIZ_ID"]."'");
	while($r = $DB->fetch_assoc()){
			$rlist[] = $r["Attr_Name"];
		}
	if (isset($rlist)) {
	$rstr = join(" ",$rlist);
	}else{
	$rstr = '未指定';	
	}
	return $rstr;
}
$jcurid = 2;
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
    <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/weicbd.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/biz/products/bizshop_menubar.php');?>
    <div id="products" class="r_con_wrap">
      <div class="category">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="mytable">
          <tr bgcolor="#f5f5f5">
            <td width="3%" align="center"><strong>#</strong></td>
            <td width="60"><strong>类型名称</strong></td>
            <td width="200">关联属性</td>
            <td width="110" align="center"><strong>操作</strong></td>
          </tr>
       
		 <?php foreach($lists as $key=>$rsType):?>
          <tr onMouseOver="this.bgColor='#D8EDF4';" onMouseOut="this.bgColor='';">
            <td>&nbsp;&nbsp;<?=$rsType['Type_ID']?></td>
            <td><?php echo $rsType["Type_Name"]; ?></td>
            <td><?=get_type($rsType['Type_ID'])?></td>
            <td align="center"><a href="shop_attr.php">属性列表</a><a href="product_type_edit.php?TypeID=<?php echo $rsType["Type_ID"]; ?>" title="修改"><img src="/static/member/images/ico/mod.gif" align="absmiddle" /></a> <a href="?action=del&TypeID=<?php echo $rsType["Type_ID"]; ?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a></td>
          </tr>
          <?php endforeach; ?>
     
        </table>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
</body>
</html>