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

$result = get_cityCode($source_id);
$citylist = [];
if(empty($result) || !is_array($result)){
	layerMsg("请设置正确的source_id"); exit;
}

foreach($result as $k => $v){
	$citylist[$v['cityCode']] = $v['cityName'];
}

if(empty($citylist)){
	layerMsg("无法获取城市信息"); exit;
}

if(IS_POST){
	$mobile = $request->input('mobile');
	$city_name = $request->input('city_name');
	$enterprise_name = $request->input('enterprise_name');
	$enterprise_address = $request->input('enterprise_address');
	$contact_name = $request->input('contact_name');
	$contact_phone = $request->input('contact_phone');
	$email = $request->input('email');
	$id = $request->input('id');
	
	if(empty($mobile)){
		die(json_encode([
			'status' => 0,
			'msg' => '注册商户手机号不能为空'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($city_name)){
		die(json_encode([
			'status' => 0,
			'msg' => '请选择城市'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($enterprise_name)){
		die(json_encode([
			'status' => 0,
			'msg' => '请输入企业名称'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($enterprise_address)){
		die(json_encode([
			'status' => 0,
			'msg' => '请输入企业地址'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($contact_name)){
		die(json_encode([
			'status' => 0,
			'msg' => '联系人姓名不能为空'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($contact_phone)){
		die(json_encode([
			'status' => 0,
			'msg' => '联系人电话不能为空'
		], JSON_UNESCAPED_UNICODE));
	}
	if(empty($email)){
		die(json_encode([
			'status' => 0,
			'msg' => '邮箱地址不能为空'
		], JSON_UNESCAPED_UNICODE));
	}
	$data = [
		'mobile' => $mobile,
		'city_name' => $city_name,
		'enterprise_name' => $enterprise_name,
		'enterprise_address' => $enterprise_address,
		'contact_name' => $contact_name,
		'contact_phone' => $contact_phone,
		'email' => $email
	];
	if($source_id == '73753'){
		$data['source_id'] = $source_id;
		$flag = DB::table('biz')->where(['Users_ID' => $rsBiz['Users_ID'], 'Biz_ID' => $rsBiz['Biz_ID']])->update(['Biz_Ext' => json_encode(['dada' => $data],JSON_UNESCAPED_UNICODE)]);
		if($flag!==false){
			die(json_encode([
				'status' => 1,
				'msg' => '创建商户成功'
			], JSON_UNESCAPED_UNICODE));
		}else{
			die(json_encode([
				'status' => 0,
				'msg' => '创建商户失败'
			], JSON_UNESCAPED_UNICODE));
		}
	}
	$result = add_shop($data);
	if(isset($result['status']) && $result['status']!='fail!' && $result['status']!='fail'){
		$data['source_id'] = $result['result'];
		$flag = DB::table('biz')->where(['Users_ID' => $rsBiz['Users_ID'], 'Biz_ID' => $rsBiz['Biz_ID']])->update(['Biz_Ext' => json_encode(['dada' => $data],JSON_UNESCAPED_UNICODE)]);
		if($flag!==false){
			die(json_encode([
				'status' => 1,
				'msg' => '创建商户成功'
			], JSON_UNESCAPED_UNICODE));
		}else{
			die(json_encode([
				'status' => 0,
				'msg' => '创建商户失败'
			], JSON_UNESCAPED_UNICODE));
		}
	}else{
		die(json_encode([
			'status' => 0,
			'msg' => $result['msg']
		], JSON_UNESCAPED_UNICODE));
	}
}

$jcurid = 1;
$subid = "sub11";
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
	<title>创建店铺</title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>
	<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
	<script src="/static/js/jquery.validate.min.js"></script>
	<script src="/static/js/jquery.validate.zh_cn.js"></script>
	<script src="/biz/style/validate.js"></script>
    <style type="text/css">
		input{outline:none;margin:0 10px;padding-left:5px;border: 1px solid #ddd;border-radius: 5px;height: 32px;width: 210px;}
		input[type='radio']{width:20px;height:20px;margin-left:40px;}
		.r_con_wrap dl dt,.r_con_wrap dl dd {height:60px;line-height:60px;}
		.r_con_wrap dl{clear:both;overflow:hidden;}
		.r_con_wrap dl dt {width:100px;float:left;}
		.r_con_wrap dl dd {width:400px;margin-left:100px;}
		.r_con_wrap dl dd select {margin-left:10px;width:100px;text-align:center;height:40px;}
		.btn_green{display:inline;float:none;}
    </style>
	<script>
	$(function(){
		$(".btn_green").click(function(){
			//字段验证
			$("#submit").validate({
				rules:{
					mobile: {
						required: true,
						minlength:11,
						maxlength:11,
						isMobile:true
					},
					contact_name: {
						required: true,
						minlength:2,
						maxlength:20
					},
					contact_phone: {
						required: true,
						minlength:11,
						maxlength:11,
						isMobile:true
					},
					enterprise_name: {
						required: true,
						minlength:2,
						maxlength:30
					},
					enterprise_address: {
						required: true,
						minlength:2,
						maxlength:50
					},
					email: {
						required: true,
						minlength:2,
						maxlength:100,
						checkemail:true
					}
				},
				messages:{
					mobile: {
						required: "注册商户手机号不能为空",
						minlength:"注册商户手机号不正确",
						maxlength:"注册商户手机号不正确",
						isMobile:"注册商户手机号不正确"
					},
					contact_name: {
						required: "联系人姓名不能为空",
						minlength:"联系人姓名在2到20个字符之间",
						maxlength:"联系人姓名在2到20个字符之间"
					},
					contact_phone: {
						required: "联系人电话不能为空",
						minlength:"联系人电话不正确",
						maxlength:"联系人电话不正确",
						isMobile:"联系人电话不正确"
					},
					enterprise_name: {
						required: "请输入企业名称",
						minlength:"企业名称长度在2至30个字符之间",
						maxlength:"企业名称长度在2至30个字符之间"
					},
					enterprise_address: {
						required: "请输入企业地址",
						minlength:"企业地址长度在2至50个字符之间",
						maxlength:"企业地址长度在2至50个字符之间"
					},
					email: {
						required: "请输入邮箱地址",
						minlength:"邮箱地址长度在2至100个字符之间",
						maxlength:"邮箱地址长度在2至100个字符之间",
						checkemail:"邮箱不正确"
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
							layer.alert("提交成功", function(){
								location.reload();
							});
						}else{
							layer.alert(data.msg);
						}
					}, 'json');
				}
			});
			$("#submit").submit();
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
			<?php if(empty($rsBiz['Biz_Ext'])){ ?>
			<form action="?" method="post" id="submit">
				<input type="hidden" name="id" value="<?=$id ?>" />

				<dl>
					<dt>手机号</dt>
					<dd><input type="text" name="mobile" value="" maxlength="11"/></dd>
				</dl>
			
				<dl>
					<dt>联系人姓名</dt>
					<dd><input type="text" name="contact_name" value=""/></dd>
				</dl>
			
				<dl>
					<dt>手联系人电话</dt>
					<dd><input type="text" name="contact_phone" value=""/></dd>
				</dl>
			
				<dl>
					<dt>城市</dt>
					<dd>
						<select name="city_name">
						<?php foreach($citylist as $k => $v){ ?>
						<option value="<?=$v?>"><?=$v?></option>
						<?php } ?>
						</select>
					</dd>
				</dl>
			
				<dl>
					<dt>企业全称</dt>
					<dd><input type="text" name="enterprise_name" value=""/></dd>
				</dl>
			
				<dl>
					<dt>企业地址</dt>
					<dd><input type="text" name="enterprise_address" value=""/></dd>
				</dl>
			
				<dl>
					<dt>邮箱地址</dt>
					<dd><input type="text" name="email" value="<?=$rsBiz["Biz_Email"] ?>"/></dd>
				</dl>

				<div class="submit">
					<input type="button" class="btn_green" name="submit_button" value="保存"/>
				</div>
			</form>
			<?php }else{ ?>
			<?php $ext = json_decode($rsBiz['Biz_Ext'], true)['dada']; ?>
			<dl>
					<dt>手机号</dt>
					<dd><?=$ext['mobile'] ?></dd>
				</dl>
			
				<dl>
					<dt>联系人姓名</dt>
					<dd><?=$ext['contact_name'] ?></dd>
				</dl>
			
				<dl>
					<dt>联系人电话</dt>
					<dd><?=$ext['contact_phone'] ?></dd>
				</dl>
			
				<dl>
					<dt>城市</dt>
					<dd>
						<?=$ext['city_name']?>
					</dd>
				</dl>
			
				<dl>
					<dt>企业全称</dt>
					<dd><?=$ext['enterprise_name'] ?></dd>
				</dl>
			
				<dl>
					<dt>企业地址</dt>
					<dd><?=$ext['enterprise_address'] ?></dd>
				</dl>
			
				<dl>
					<dt>邮箱地址</dt>
					<dd><?=$ext['email'] ?></dd>
				</dl>
				<?php 
				$source_id = $ext['source_id'];
				//查询余额
				$balance = balanceQuery(['category' => 3], $source_id);
				?>
				<dl>
					<dt>余额</dt>
					<dd><span class="red">¥ <?=$balance['result']['deliverBalance'] ?></span></dd>
				</dl>
				<dl>
					<dt>红包</dt>
					<dd><span class="red">¥ <?=$balance['result']['redPacketBalance'] ?></span></dd>
				</dl>
			<?php } ?>
        </div>
    </div>
</div>
</body>
</html>
