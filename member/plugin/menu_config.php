<?php
use Illuminate\Database\Capsule\Manager as DB;

$UsersID = !empty($_SESSION['Users_ID'])?$_SESSION['Users_ID']:'';
$thridShippList = DB::table('third_shipping')->where(['Users_ID' => $UsersID, 'Status' => 1])->get();
$rmenu['plugin'] = ['plugin' => '第三方配送'];
if(!empty($thridShippList)){
	foreach($thridShippList as $k =>$v){
		$rmenu['plugin']['dada_config'] = $v['Name'];
	}
}else{
	$rmenu['plugin']['dada_config'] = '达达配送';
}
$rmenu['plugin']['temp_config'] = '模板消息管理';
?>