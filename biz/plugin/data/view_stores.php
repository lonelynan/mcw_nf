<?php
require_once('../../global.php');
require_once(CMS_ROOT . '/include/helper/tools.php');
require_once('config.php');
require_once('function.php');
require_once('DadaOpenapi.php');

!defined("REQUEST_METHOD") && define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
!defined("IS_GET") && define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
!defined("IS_POST") && define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);

use Illuminate\Database\Capsule\Manager as DB;

$storeid = $request->input('StoreID');
if(!$storeid){
	layerMsg("非法参数传递", 'stores.php?id=' . $id);
}
if(empty($rsBiz['Biz_Ext'])){
	layerMsg("您还没有添加商户，请添加商户", 'stores.php?id=' . $id);
}
$rsStores = DB::table('stores')->where(['Users_ID' => $rsBiz['Users_ID'], 'Biz_ID' => $rsBiz['Biz_ID'], 'Stores_ID' => $storeid])->first();
if(empty($rsStores)){
	layerMsg("门店不存在", 'stores.php?id=' . $id);
}
$bdPostion = gd_trans_bd($rsStores['Stores_PrimaryLat'], $rsStores['Stores_PrimaryLng']);
$rsStores['Stores_PrimaryLat'] = $bdPostion['lat'];
$rsStores['Stores_PrimaryLng'] = $bdPostion['lng'];
$ext = json_decode($rsBiz['Biz_Ext'], true)['dada'];
$source_id = $ext['source_id'];
$result = get_cityCode($source_id);
$citylist = [];

if(empty($result) || !is_array($result)){
	layerMsg("请设置正确的source_id");
}

foreach($result as $k => $v){
	$citylist[$v['cityCode']] = $v['cityName'];
}

if(empty($citylist)){
	layerMsg("无法获取城市信息");
}

$jcurid = 2;
$subid = "sub21";
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
	<title>门店详情</title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>
	<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
	<script type='text/javascript' src='/static/member/js/global.js'></script>
	<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?=$ak_baidu ?>"></script>
    <style type="text/css">
		input{outline:none;margin:0 10px;padding-left:5px;border: 1px solid #ddd;border-radius: 5px;height: 32px;width: 210px;}
		.r_con_wrap {padding-left:100px;}
		input[type='radio']{width:20px;height:20px;margin-left:40px;}
		.r_con_wrap dl dt,.r_con_wrap dl dd {height:60px;line-height:60px;}
		.r_con_wrap dl{clear:both;overflow:hidden;}
		.r_con_wrap dl dt {width:100px;float:left;}
		.r_con_wrap dl dd {width:400px;margin-left:100px;}
		.r_con_wrap dl dd select {margin-left:10px;width:100px;text-align:center;height:40px;}
		.btn_green{display:inline;float:none;margin:0px auto;cursor:pointer;}
		.submit {text-align:center;}
		.primary {
			display:inline-grid;
			height: 30px;
			line-height: 30px;
			background:#3aa0eb;
			border: none;
			color: #fff;
			width: 145px;
			border-radius: 5px;
			text-align: center;
			text-decoration: none;
			margin-right: 10px;
			cursor:pointer;
		}
		#map{width:500px;height:400px;}
    </style>
	<script>
	$(function(){
		global_obj.map_init();
	});
	</script>
</head>

<body>
<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/wechat.css' rel='stylesheet' type='text/css'/>
        <?php require_once('menubar.php'); ?>
        <div class="r_con_wrap">
			<form action="?" method="post" id="submit">
				<input type="hidden" name="id" value="<?=$id ?>" />
				<input type="hidden" name="StoreID" value="<?=$storeid ?>" />
				<input type="hidden" name="StoreNo" value="<?=$rsStores['Stores_No'] ?>">
				<dl>
					<dt>门店名称</dt>
					<dd><?=$rsStores['Stores_Name'] ?></dd>
				</dl>
			
				<dl>
					<dt>业务类型</dt>
					<dd>
						<?=$business_list[$rsStores['Stores_Business']] ?>
					</dd>
				</dl>
			
				<dl>
					<dt>地区</dt>
					<dd><?=$rsStores['Stores_City'] ?><?=$rsStores['Stores_Area'] ?>
					</dd>
				</dl>
				
				<dl>
					<dt>门店地址</dt>
					<dd><?=$rsStores['Stores_Address'] ?>
						<input type="hidden" name="Address" id="Address" value="<?=$rsStores['Stores_Address'] ?>"/>
					</dd>
				</dl>
				
				<dl>
					<dt></dt>
					<dd style="height:300px;">
						<div id="map"></div>
					</dd>
				</dl>
				
				<dl>
					<dt>联系人姓名</dt>
					<dd><?=$rsStores['Stores_Contact'] ?></dd>
				</dl>
			
				<dl>
					<dt>手联系人电话</dt>
					<dd><?=$rsStores['Stores_Telephone'] ?></dd>
				</dl>
			
				<dl>
					<dt>联系人身份证</dt>
					<dd><?=$rsStores['Stores_Contact_IDNum'] ?></dd>
				</dl>
				
				<dl>
					<dt>状态</dt>
					<dd>
						<?=$rsStores['Stores_Status']==0?"门店下线":"门店激活"?>
					</dd>
				</dl>
				<input type="hidden" name="PrimaryLng" value="<?=$rsStores['Stores_PrimaryLng'] ?>">
				<input type="hidden" name="PrimaryLat" value="<?=$rsStores['Stores_PrimaryLat'] ?>">
			</form>  
        </div>
    </div>
</div>
</body>
</html>
