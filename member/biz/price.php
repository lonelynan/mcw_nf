<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
$rsConfig = $DB->GetRs("shop_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
if(empty($rsConfig)){
	echo '<script language="javascript">alert("请先配置基本设置！");history.back();</script>';
	exit;
}
$range_list = json_decode($rsConfig['PriceRange'], true);
if(!empty($_POST)){
	$price_range = array();
	$price_start = $_POST['price_start'];
	$price_end = $_POST['price_end'];
	foreach($price_start as $key => $val){
		//一对都为空则不入库
		if(empty($val) && empty($price_end[$key])){
			continue;
		}
		$price_range[$key]['start'] = $val;
		$price_range[$key]['end'] = $price_end[$key];
	}
	$Data = array(
	    'PriceRange'=>json_encode($price_range,JSON_UNESCAPED_UNICODE),
	);
	$Set = $DB->Set("shop_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	if($Set){
		echo '<script language="javascript">alert("保存成功！");location.href="price.php";</script>';
	    exit;
	}else{
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	    exit;
	}
}
$jcurid = 5;
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
<style>
.element {
	margin-bottom: 8px;
	line-height: 28px;
}
.element input {
	border: 1px solid #ddd;
	border-radius: 5px;
	height: 28px;
	line-height: 28px;
	text-indent: 2px;
}
.add_element {
	color: #188eee;
	cursor: pointer;
	font-size: 21px;
	font-weight: 600;
	vertical-align: top;
}
.del_element {
	color: #188eee;
	cursor: pointer;
	font-weight: 600;
	vertical-align: top;
}
.del_element:hover {
	color: #F60;
}
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
	<div class="iframe_content">
		<link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
		<?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/bizunion_menubar.php');?>
		<div id="products" class="r_con_wrap">
			<div class="tips_info"> 
			注：前台筛选价格区间； （开始或结束）价格为空表示（开始或结束）价格<span>不限</span>。<br>
            </div>
			<div class="category">
				<form method="post" action="?">
					<table width="100%" border="0" cellpadding="0" cellspacing="0" class="mytable">
						<?php if(empty($range_list)){?>
						<tr>
							<td><div class="element">
									<input type="text" name="price_start[]" value="" size="10"/>
									&nbsp;-&nbsp;
									<input type="text" name="price_end[]" value="" size="10"/>
									&nbsp;<span class="add_element">+</span></div></td>
						</tr>
						<?php }else{?>
						<tr>
							<td><?php foreach($range_list as $key => $val){?>
								<?php if($key==0){?>
								<div class="element">
									<input type="text" name="price_start[]" value="<?php echo $val['start'];?>" size="10"/>
									&nbsp;-&nbsp;
									<input type="text" name="price_end[]" value="<?php echo $val['end'];?>" size="10"/>
									&nbsp;<span class="add_element">+</span></div>
								<?php }else{?>
								<div class="element">
									<input type="text" name="price_start[]" value="<?php echo $val['start'];?>" size="10"/>
									&nbsp;-&nbsp;
									<input type="text" name="price_end[]" value="<?php echo $val['end'];?>" size="10"/>
									&nbsp;<span class="del_element">删除</span></div>
								<?php }?>
								<?php }?></td>
						</tr>
						<?php }?>
					</table>
					<div class="submit" style="margin-top:10px;">
						<input type="submit" value="提交保存" name="submit_button">
					</div>
				</form>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<script>
    $('.add_element').click(function(){
		var content = '<div class="element"><input type="text" name="price_start[]" value="" size="10"/>&nbsp;-&nbsp;<input type="text" name="price_end[]" value="" size="10"/>&nbsp;<span class="del_element">删除</span></div>';
		$(this).parent().parent('td').append(content);
		$('.del_element').click(function(){
			$(this).parent().remove();
		})
	})
	$('.del_element').click(function(){
		$(this).parent().remove();
	});
</script>
</body>
</html>