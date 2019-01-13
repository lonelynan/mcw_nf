<?php
require_once('../../global.php');
require_once(CMS_ROOT . '/include/helper/tools.php');
require_once('config.php');
require_once('function.php');
require_once('DadaOpenapi.php');
require_once('city.php');

!defined("REQUEST_METHOD") && define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
!defined("IS_GET") && define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
!defined("IS_POST") && define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);

use Illuminate\Database\Capsule\Manager as DB;

if(empty($rsBiz['Biz_Ext'])){
	layerMsg("您还没有添加商户，请添加商户", 'list.php?id=' . $id); exit;
}
$ext = json_decode($rsBiz['Biz_Ext'], true)['dada'];
$source_id = $ext['source_id'];

if(empty($ext['source_id'])){
    layerMsg("您还没有添加商户，请添加商户", 'list.php?id=' . $id); exit;
}

$result = get_cityCode($source_id);
$citylist = [];
if(empty($result) || !is_array($result)){
	layerMsg("请设置正确的source_id", 'list.php?id=' . $id); exit;
}

foreach($result as $k => $v){
	$citylist[$v['cityCode']] = $v['cityName'];
}

if(empty($citylist)){
	layerMsg("无法获取城市信息", 'list.php?id=' . $id); exit;
}

$action = $request->input('action');
if($action === 'getArea'){
	$name = $request->input('city_name');
	if(!empty($address[$name])){
		die(json_encode([
			'status' => 1,
			'data' => $address[$name]
		], JSON_UNESCAPED_UNICODE));
	}else{
		die(json_encode([
			'status' => 0,
			'data' => []
		]));
	}
}
if(IS_POST){
	$station_name = $request->input('station_name');
	$business = $request->input('business');
	$city_name = $request->input('city_name');
	$area_name = $request->input('area_name');
	$station_address = $request->input('Address');
	$contact_name = $request->input('contact_name');
	$phone = $request->input('phone');
	$id_card = $request->input('id_card');
	$PrimaryLng = $request->input('PrimaryLng');
	$PrimaryLat = $request->input('PrimaryLat');
	
	if(empty($station_name)){
		die(json_encode([
			'status' => 0,
			'msg' => '门店名称不能为空'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($business)){
		die(json_encode([
			'status' => 0,
			'msg' => '请选择业务类型'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($city_name)){
		die(json_encode([
			'status' => 0,
			'msg' => '请选择城市名称'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($area_name)){
		die(json_encode([
			'status' => 0,
			'msg' => '请输入区域名称'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($station_address)){
		die(json_encode([
			'status' => 0,
			'msg' => '门店地址不能为空'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($contact_name)){
		die(json_encode([
			'status' => 0,
			'msg' => '联系人姓名不能为空'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($phone)){
		die(json_encode([
			'status' => 0,
			'msg' => '联系人电话不能为空'
		], JSON_UNESCAPED_UNICODE));
	}
	
	if(empty($PrimaryLng) || empty($PrimaryLat)){
		die(json_encode([
			'status' => 0,
			'msg' => '请点击定位选择坐标'
		], JSON_UNESCAPED_UNICODE));
	}
	
	$Stores_No = 'S' . chr(rand(65,90)) . rand(100000,999999);
	$data = [
		'station_name' => $station_name,
		'business' => $business,
		'city_name' => $city_name,
		'area_name' => $area_name,
		'station_address' => $station_address,
		'contact_name' => $contact_name,
		'phone' => $phone,
		'origin_shop_id' => $Stores_No
	];
	
	$data = array_merge($data, bd_trans_gd($PrimaryLat, $PrimaryLng));
	if(!empty($id_card)){
		$data['id_card'] = $id_card;
	}
	$result = add_stores($data, $source_id);
	if($result['status']=='success'){
		if(!empty($result['result']['successList'])){
			$insertData = [];
			foreach($result['result']['successList'] as $k => $v){
				$insertData = [
					'Users_ID' => $UsersID,
					'Biz_ID' => $rsBiz['Biz_ID'],
					'Stores_Name' => !empty($v['station_name']) ? $v['station_name'] : $v['stationName'],
					'Stores_Business' => $v['business'],
					'Stores_Telephone' => $v['phone'],
					'Stores_PrimaryLng' => $v['lng'],
					'Stores_PrimaryLat' => $v['lat'],
					'Stores_No' => !empty($v['origin_shop_id']) ? $v['origin_shop_id'] : $v['originShopId'],
					'Stores_CreateTime' => time(),
					'Stores_Address' => !empty($v['station_address']) ? $v['station_address'] : $v['stationAddress'],
					'Stores_City' => !empty($v['city_name']) ? $v['city_name'] : $v['cityName'],
					'Stores_Area' => !empty($v['area_name']) ? $v['area_name'] : $v['areaName'],
					'Stores_Contact' => !empty($v['contact_name']) ? $v['contact_name'] : $v['contactName'],
					'Stores_Contact_IDNum' => !empty($v['id_card']) ? $v['id_card'] : $v['legalId']
				];
			}
			$flag = DB::table('stores')->insert($insertData);
			if($flag){
				die(json_encode([
					'status' => 1,
					'msg' => '添加门店成功'
				], JSON_UNESCAPED_UNICODE));
			}else{
				die(json_encode([
					'status' => 0,
					'msg' => '添加门店失败'
				], JSON_UNESCAPED_UNICODE));
			}
		}else{
			die(json_encode([
				'status' => 0,
				'msg' => '没有数据'
			], JSON_UNESCAPED_UNICODE));
		}
	}else{
		die(json_encode([
			'status' => 0,
			'msg' => $result['msg']
		], JSON_UNESCAPED_UNICODE));
	}
}

$jcurid = 2;
$subid = "sub21";
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
	<title>创建门店</title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>
	<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
	<script src="/static/js/jquery.validate.min.js"></script>
	<script src="/static/js/jquery.validate.zh_cn.js"></script>
	<script type='text/javascript' src='/static/member/js/global.js'></script>
	<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?=$ak_baidu ?>"></script>
	<script src="/biz/style/validate.js?<?=time()?>"></script>
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
			display:inline-table;
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
		
		$(".btn_green").click(function(){
			//字段验证
			$("#submit").validate({
				rules:{
					station_name: {
						required: true,
						minlength:2,
						maxlength:20
					},
					Address: {
						required: true,
						minlength:2,
						maxlength:50
					},
					contact_name: {
						required: true,
						minlength:2,
						maxlength:30
					},
					phone: {
						required: true,
						minlength:11,
						maxlength:11,
						isMobile:true
					},
					id_card: {
						required: true,
						minlength:15,
						maxlength:18,
						isIdCardNo:true
					}
				},
				messages:{
					station_name: {
						required: "请输入门店名称",
						minlength:"门店名称在2到20个字符之间",
						maxlength:"门店名称在2到20个字符之间"
					},
					Address: {
						required: "请输入门店地址",
						minlength:"门店地址长度在2至50个字符之间",
						maxlength:"门店地址长度在2至50个字符之间"
					},
					contact_name: {
						required: "请输入联系人姓名",
						minlength:"联系人姓名长度在2至30个字符之间",
						maxlength:"联系人姓名长度在2至30个字符之间"
					},
					phone: {
						required: "联系人电话不能为空",
						minlength:"联系人电话不正确",
						maxlength:"联系人电话不正确",
						isMobile:"联系人电话不正确"
					},
					id_card: {
						required: "请输入联系人身份证",
						minlength:"联系人身份证长度在2至100个字符之间",
						maxlength:"联系人身份证在2至100个字符之间",
						isIdCardNo:"联系人身份证不正确"
					}
				},
				errorPlacement:function(error, element){
					if($(error).html()!=""){
						index = layer.tips($(error).html(), $(element), {tips:[2,'rgb(239, 111, 71)'],  time: 0 });
						$(element).attr('layer_index', index);
						return true;
					}
				},
				success:function(error, element){
					var layer_index = $(element).attr("layer_index");
					if(layer_index!=null && layer_index!=''){
						layer.close(layer_index);
					}
				},submitHandler:function(form){
					$.post("?", $("#submit").serialize(), function(data){
						if(data.status==1){
							layer.alert(data.msg, function(){
								location.href="stores.php?id=<?=$id?>";
							});
						}else{
							layer.alert(data.msg);
						}
					}, 'json');
				}
			});
			$("#submit").submit();
        });
		
		//区域联动
		var area = $("#city_name").val();
		var url = "?id=<?=$id?>";
		getArea(url, "#area_name", area);
		$("#city_name").change(function(){
			var area = $(this).val();
			getArea(url, "#area_name", area);
		});
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

				<dl>
					<dt>门店名称</dt>
					<dd><input type="text" name="station_name" value=""/></dd>
				</dl>
			
				<dl>
					<dt>业务类型</dt>
					<dd>
						<select name="business">
						<?php foreach($business_list as $k => $v){ ?>
						<option value="<?=$k?>"><?=$v?></option>
						<?php } ?>
						</select>
					</dd>
				</dl>
			
				<dl>
					<dt>城市名称</dt>
					<dd>
						<select name="city_name" id="city_name">
						<?php foreach($citylist as $k => $v){ ?>
						<option value="<?=$v?>"><?=$v?></option>
						<?php } ?>
						</select>
					</dd>
				</dl>
			
				<dl>
					<dt>区域名称</dt>
					<dd>
						<select name="area_name" id="area_name"></select>
					</dd>
				</dl>
			
				<dl>
					<dt>门店地址</dt>
					<dd>
						<input type="text" name="Address" id="Address" value=""/>
						<span class="primary" id="Primary">定位</span><br/>
					    <div class="tips">如果输入地址后点击定位按钮无法定位，请在地图上直接点击选择地点</div>
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
					<dd><input type="text" name="contact_name" value=""/></dd>
				</dl>
			
				<dl>
					<dt>联系人电话</dt>
					<dd><input type="text" name="phone" value=""/></dd>
				</dl>
			
				<dl>
					<dt>联系人身份证</dt>
					<dd><input type="text" name="id_card" value=""/></dd>
				</dl>
				<input type="hidden" name="PrimaryLng" value="116.411084">
				<input type="hidden" name="PrimaryLat" value="39.92155">
				<div class="submit">
					<input type="button" class="btn_green" name="submit_button" value="保存"/>
				</div>
			</form>  
        </div>
    </div>
</div>
</body>
</html>
