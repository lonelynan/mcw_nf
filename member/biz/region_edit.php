<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/region.class.php');
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
ini_set ( "display_errors", "On" );
$RegionID=empty($_REQUEST['RegionID'])?0:$_REQUEST['RegionID'];
$rsRegion=$DB->GetRs("area_region","*","where Users_ID='".$_SESSION["Users_ID"]."' and Region_ID=".$RegionID);
if($_POST){
	if($_POST["Area"]>0){
		$areaid = $_POST["Area"];
	}else{
		if($_POST["City"]>0){
			$areaid = $_POST["City"];
		}else{
			echo '<script language="javascript">alert("请选择地级市");history.back();</script>';
			exit;
		}
	}
	if(empty($_POST["Name"])){
		echo '<script language="javascript">alert("名称必须填写");history.back();</script>';
		exit;
	}
	$Data=array(
		"Region_Index"=>$_POST['Index'],
		"Region_ParentID"=>$_POST["ParentID"],
		"Area_ID"=>$areaid,
		"Region_Name"=>$_POST["Name"]
	);
	$Flag=$DB->Set("area_region",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Region_ID=".$RegionID);
	$flag = $DB->Set("area_region",array("Area_ID"=>$areaid),"where Users_ID='".$_SESSION["Users_ID"]."' and Region_ParentID=".$RegionID);
	$Flag = $Flag && $flag;
	if($Flag){
		echo '<script language="javascript">alert("修改成功");window.location="region.php";</script>';
	}else{
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	}
	exit;
}else{
	$region = new region($DB, $_SESSION["Users_ID"]);
	$areaids = $region->get_areaparent($rsRegion["Area_ID"]);
}
$jcurid = 4;
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
<script type='text/javascript' src="/static/js/select2.js"></script>
<script type="text/javascript" src="/static/js/location.js"></script>
<script type="text/javascript" src="/static/js/area.js"></script>
<script type='text/javascript' src='/static/member/js/shop.js'></script>
<link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
<link href="/static/css/select2.css" rel="stylesheet"/>
<script type="text/javascript">
$(document).ready(function(){
	showLocation(<?php echo empty($areaids["province"]) ? 0 : $areaids["province"];?>,<?php echo empty($areaids["city"]) ? 0 : $areaids["city"];?>,<?php echo empty($areaids["area"]) ? 0 : $areaids["area"];?>);
	shop_obj.region_init();
});
</script>
<style type="text/css">
#loc_province,#loc_city,#loc_town{display:none;}
</style>
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
			<div class="category">
				<div class="m_righter" style="margin-left:0px;">
					<form action="?" name="region_form" id="region_form" method="post">
						<input name="RegionID" type="hidden" value="<?php echo $rsRegion["Region_ID"] ?>">
						<h1>修改区域</h1>
						<div class="opt_item">
							<label>排序：</label>
							<span class="input">
							<input type="text" name="Index" value="<?php echo $rsRegion["Region_Index"] ?>" class="form_input" size="5" maxlength="30" notnull />
							<font class="fc_red">*</font>请输入数字</span>
							<div class="clear"></div>
						</div>
						<div class="opt_item">
							<label>所属地区：</label>
							<span class="input">
							<select name="Province" id="loc_province" style="width:120px" notnull>
							</select>
							<select name="City" id="loc_city" style="width:120px" notnull>
							</select>
							<select name="Area" id="loc_town" style="width:120px; display:none">
							</select>
							</span>
							<div class="clear"></div>
						</div>
						<div class="opt_item">
							<label>所属区域：</label>
							<span class="input">
							<select name='ParentID' id="parentid">
								<option value='0'>一级区域</option>
								<?php
                $DB->Get("area_region","*","where Region_Model='shop' and Users_ID='".$_SESSION["Users_ID"]."' and Area_ID=".$rsRegion["Area_ID"]." and Region_ID<>".$RegionID." and Region_ParentID=0 order by Region_Index asc, Region_ID asc");
				while($r = $DB->fetch_assoc()){
				?>
								<option value="<?php echo $r["Region_ID"];?>"<?php echo $r["Region_ID"]==$rsRegion["Region_ParentID"] ? ' selected' : '';?>><?php echo $r["Region_Name"];?></option>
								<?php }?>
							</select>
							</span>
							<div class="clear"></div>
						</div>
						<div class="opt_item">
							<label>名称：</label>
							<span class="input">
							<input type="text" name="Name" value="<?php echo $rsRegion["Region_Name"] ?>" class="form_input" size="15" maxlength="30" notnull />
							<font class="fc_red">*</font></span>
							<div class="clear"></div>
						</div>
						<div class="opt_item">
							<label></label>
							<span class="input">
							<input type="submit" class="btn_green btn_w_120" name="submit_button" value="修改" />
							<a href="javascript:void(0);" class="btn_gray" onClick="location.href='category.php'">返回</a></span>
							<div class="clear"></div>
						</div>
						<input type="hidden" id="usersid" value="<?php echo $_SESSION["Users_ID"];?>">
						<input type="hidden" id="region_model" value="repast">
					</form>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
</body>
</html>