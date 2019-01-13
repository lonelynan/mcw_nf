<?php
require_once('../global.php');
if ($rsBiz['is_agree'] !=1 || $rsBiz['is_auth'] !=2) {
        echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.location="/biz/authen/index.php";</script>';
        exit;
    }
    if ($rsBiz['is_biz'] !=1) {
        echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.location="/biz/authen/index.php";</script>';
        exit;
    }
    $Biz_ID=$_SESSION["BIZ_ID"];
    $bizInfo = $DB->getRs('biz','','where Biz_ID = '.$Biz_ID);
    if (time() > $bizInfo['expiredate']) {
            echo '<script language="javascript">alert("您的商家已经到期,请及时续费");window.location="/biz/authen/charge_man.php";</script>';
            exit;
    }
if(isset($_GET["action"])){
	if($_GET["action"]=="del"){
		$rsType_ID = $DB->GetRs("shop_attribute","Type_ID","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Attr_ID=".$_GET["Attr_ID"]);
		$rsPro = $DB->GetRs("shop_products","Products_Type","where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Products_Type=".$rsType_ID['Type_ID']);
		if($rsPro){
		echo '<script language="javascript">alert("有产品在用不能删除！");history.back();</script>';exit;
		}		
		$Flag=$DB->Del("shop_attribute","Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Attr_ID=".$_GET["Attr_ID"]);
		if($Flag){
		$rsattrnum = $DB->GetRs("shop_product_type","attrnum","where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and Type_ID=".$rsType_ID['Type_ID']);
	$Data = array(
	 	'attrnum'=> $rsattrnum['attrnum'] - 1
    );
	$Flagattr=$DB->Set("shop_product_type",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and Type_ID=".$rsType_ID['Type_ID']);
	if(!$Flagattr){
	echo '<script language="javascript">alert("非法操作！");history.back();</script>';exit;
	}
		echo '<script language="javascript">alert("删除成功");window.location="shop_attr.php";</script>';exit;
		}else{
			echo '<script language="javascript">alert("删除失败");history.back();</script>';
		}
		exit;
	}
}
$Attr_Option_List = array("唯一属性","单选属性","复选属性"); 
$Input_List = array("手工录入","从下面列表中选择(一行代表一个可选项)","多行文本框");

//获取分类列表
$types = array();
$DB->Get("shop_product_type","Type_ID,Type_Name","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." order by Type_Index asc");
while($t=$DB->fetch_assoc()){
	$types[$t["Type_ID"]] = $t;
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
    <script type='text/javascript' src='/biz/js/shop.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/biz/products/bizshop_menubar.php');?>
    <div id="attr" class="r_con_wrap">
     <form class="search" id="search_form" method="get" action="?">
        按商品类型显示：
        <select name="Type_ID">
          <option value="0">--请选择--</option>
          <?php foreach($types as $key=>$item):?>
          <option value="<?=$item['Type_ID']?>"><?=$item['Type_Name']?></option>
		  <?php endforeach;?>
        </select>
        <input class="search_btn" value="显示" type="submit">
      </form>
      
      <div class="property">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="mytable">
          <tr bgcolor="#f5f5f5">
            <td width="50">序号</td>
            <td width="50" align="center"><strong>排序</strong></td>
            <td width="100" align="center"><strong>属性名称</strong></td>
            <td align="center" width="80"><strong>所属类型</strong></td>
            <td width="100" align="center"><strong>类型</strong></td>
            <td align="center"><strong>属性值</strong></td>
            <td width="100" align="center"><strong>操作</strong></td>
          </tr>
          <?php
		    $lists = array();
			
			$condition = "where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"];
			
			if(!empty($_GET['Type_ID'])){
				$Type_ID = $_GET['Type_ID'];	
				$condition .= " And Type_ID =".$Type_ID;
			}
			
			$condition .= " order by Attr_ID desc";
		     
			$rsList = $DB->getPage("shop_attribute","*",$condition,20);
			
			$lists = $DB->toArray($rsList);			
			foreach($lists as $k=>$rsAttr){
			?>
          <tr onMouseOver="this.bgColor='#D8EDF4';" onMouseOut="this.bgColor='';">
            <td align="center"><?=$rsAttr["Attr_ID"];?></td>
            <td align="center"><?php echo $rsAttr["Sort_Order"]; ?></td>
             <td align="center"><?php echo $rsAttr["Attr_Name"]; ?></td>
            <td align="center"><?php echo empty($types[$rsAttr["Type_ID"]]) ? '' : $types[$rsAttr["Type_ID"]]["Type_Name"]; ?></td>
            
            <td align="center"><?php echo $Attr_Option_List[$rsAttr["Attr_Type"]];?></td>
            <td><?php 
				
				if(!empty($rsAttr["Attr_Values"])){
					$values = str_replace("\r", ' ', $rsAttr['Attr_Values']);
					echo $values;
				}
			?></td>
            <td align="center"><a href="shop_attr_edit.php?Attr_ID=<?php echo $rsAttr["Attr_ID"]; ?>" title="修改"><img src="/static/member/images/ico/mod.gif" align="absmiddle" /></a> <a href="shop_attr.php?action=del&Attr_ID=<?php echo $rsAttr["Attr_ID"]; ?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a></td>
          </tr>
          <?php
}?>
        </table>
        <div class="blank20"></div>
      	<?php $DB->showPage(); ?>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
</body>
</html>