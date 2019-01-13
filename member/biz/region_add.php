<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
if($_POST){
	if($_POST["Area"]>0){
		$areaid = $_POST["Area"];
	}else{
		if($_POST["City"]>0){
			$areaid = $_POST["City"];
		}else{
			echo '<script language="javascript">alert("请选择地级市或市、县、区");history.back();</script>';
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
		"Region_Model"=> 'shop',
		"Users_ID"=>$_SESSION["Users_ID"]
	);
	$names = explode("\n",$_POST["Name"]);
	// echo '<pre>';
	// print_R($names);
	// print_R($Data);
	// exit;
	foreach($names as $name){
		$name = trim($name);
		if(!$name) continue;
		$Data["Region_Name"] = $name;
		$DB->Add("area_region",$Data);
	}
	echo '<script language="javascript">window.location="region.php";</script>';
	exit;
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
	showLocation(0,0,0);
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
						<h1>添加区域</h1>
						<div class="opt_item">
							<label>排序：</label>
							<span class="input">
							<input type="text" name="Index" value="1" class="form_input" size="5" maxlength="30" notnull />
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
							</select>
							</span>
							<div class="clear"></div>
						</div>
						<div class="opt_item">
							<label>名称：</label>
							<span class="input">
							<textarea name="Name" class="Attr_Group" notnull ></textarea>
							<font class="fc_red">*</font> <font style="color:#999; font-size:12px;">一行一个</font></span>
							<div class="clear"></div>
						</div>
						<div class="opt_item">
							<label></label>
							<span class="input">
							<input type="submit" class="btn_green btn_w_120" name="submit_button" value="添加" />
							<a href="javascript:void(0);" class="btn_gray" onClick="location.href='category.php'">返回</a></span>
							<div class="clear"></div>
						</div>
						<input type="hidden" id="usersid" value="<?php echo $_SESSION["Users_ID"];?>">
						<input type="hidden" id="region_model" value="shop">
					</form>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
</body>
</html>