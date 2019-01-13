<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/region.class.php');
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}

if(isset($_GET["action"])){
	if($_GET["action"]=="del"){
		$r = $DB->GetRs("area_region","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."' and Region_ParentID=".$_GET["RegionID"]);
		if($r["num"]>0){
			echo '<script language="javascript">alert("该区域下有子区域,请勿删除");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			$r = $DB->GetRs("area_region","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."' and Region_ParentID=".$_GET["RegionID"]);
			if($r["num"]>0){
				echo '<script language="javascript">alert("该区域下有商家,请勿删除");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
			}else{
				$Flag=$DB->Del("area_region","Users_ID='".$_SESSION["Users_ID"]."' and Region_ID=".$_GET["RegionID"]);
				
				if($Flag){
					echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
				}else{
					echo '<script language="javascript">alert("删除失败");history.back();</script>';
				}
			}
		}
		exit;
	}
}
$region = new region($DB, $_SESSION["Users_ID"]);
$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Region_Model='shop' order by Region_ParentID asc, Region_Index asc, Region_ID asc";
$region_arr = $region->get_region($condition);
$jcurid = 4;
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
		<?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/bizunion_menubar.php');?>
		<div id="products" class="r_con_wrap">
			<div class="category">
				<div class="control_btn"><a href="region_add.php" class="btn_green btn_w_120">添加区域</a></div>
				<table border="0" cellpadding="0" cellspacing="0" class="mytable" style="width:530px;">
					<?php
		  foreach($region_arr as $areaid => $area){
			  $areainfo = $region->get_areainfo("area_id",$areaid);
		  ?>
					<tr>
						<td width="100">&nbsp;<?php echo $areainfo ? $areainfo["area_name"] : '';?></td>
						<td width="130">&nbsp;</td>
						
						<td width="80">&nbsp;</td>
					</tr>
					<?php
          foreach($area as $regionid => $regions){
		  ?>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;<?php echo $regions["Region_Name"]?></td>
						
						<td align="center"><a href="region_edit.php?RegionID=<?php echo $regions["Region_ID"];?>" title="修改"><img src="/static/member/images/ico/mod.gif" align="absmiddle" /></a> <a href="?action=del&RegionID=<?php echo $regions["Region_ID"];?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></td>
					</tr>
					<?php
          if(!empty($regions["child"])){
			  $i=0;
			  $count = count($regions["child"]);
			  foreach($regions["child"] as $childid => $child){
				  $i++;
		  ?>
					<tr>
						<td>&nbsp;</td>
						<?php if($i==1){?>
						<td<?php echo $count>1 ? ' rowspan="'.$count.'"' : '';?>></td>
						<?php }?>
						<td>&nbsp;<?php echo $child["Region_Name"];?></td>
						<td align="center"><a href="region_edit.php?RegionID=<?php echo $child["Region_ID"];?>" title="修改"><img src="/static/member/images/ico/mod.gif" align="absmiddle" /></a> <a href="?action=del&RegionID=<?php echo $child["Region_ID"];?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></td>
					</tr>
					<?php
		  	}
		  }
		  ?>
					<?php }?>
					<?php }?>
				</table>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
</body>
</html>