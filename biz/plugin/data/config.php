<?php
use Illuminate\Database\Capsule\Manager as DB;
$UsersID =  $rsBiz['Users_ID'];
if(!$UsersID)
{
    header("location:/member/login.php");
    exit;
}
$id = $request->input('id');
if(!$id) {
	layerMsg("不正确的参数传递");
}
$rsConfig = DB::table('third_shipping_config')->where('Tid', $id)->first();
if(empty($rsConfig['ext'])){
	layerMsg("开发者配置信息没有配置");
}
$ext = json_decode($rsConfig['ext'], 1);
!defined('APP_KEY') && define('APP_KEY', $ext['app_key']);
!defined('APP_SECRET') && define('APP_SECRET', $ext['app_secret']);
!defined('APP_DOMAIN') && define('APP_DOMAIN', 'http://' . $ext['domain']);
$source_id = $ext['source_id'];
$business_list = [
	'1' => '食品小吃',
	'2' => '饮料',
	'3' => '鲜花',
	'8' => '文印票务',
	'9' => '便利店',
	'13' => '水果生鲜',
	'19' => '同城电商',
	'20' => '医药',
	'21' => ',蛋糕',
	'24' => '酒品',
	'25' => '小商品市场',
	'26' => '服装',
	'27' => '汽修零配',
	'28' => '数码',
	'29' => '小龙虾',
	'5' => '其他'
];