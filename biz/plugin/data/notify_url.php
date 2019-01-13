<?php
header("Content-Type: text/html;charset=utf-8");
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/biz/plugin/dada/DadaOpenapi.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/biz/plugin/dada/function.php');
require_once(CMS_ROOT.'/include/library/weixin_token.class.php');


/**
 * 获取小程序的access_token,用于达达配送
 * @param $appid
 * @param $appsecret
 * @param $usersid
 * @return bool|string
 */
function getAccessTokenLp($appid, $appsecret, $usersid) {
	global $DB;
	$weixin_token = new weixin_token($DB, $usersid);
	$access_token = $weixin_token->get_access_token_lp($appid, $appsecret);
	return $access_token;
}

use Illuminate\Database\Capsule\Manager as DB;

$receiveData = isset($GLOBALS['HTTP_RAW_POST_DATA']) && ! empty(isset($GLOBALS['HTTP_RAW_POST_DATA'])) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
$cancel_status = [
	'0' => '默认',
	'1' => '达达配送员取消',
	'2' => '商家主动取消',
	'3' => '系统或客服取消'
];
$order_status = [
	'1' => '待接单',
	'2' => '待取货',
	'3' => '配送中',
	'4' => '已完成',
	'5' => '已取消',
	'7' => '已过期',
	'8' => '指派单',
	'9' => '妥投异常之物品返回中',
	'10' => '妥投异常之物品返回完成',
	'1000' => '创建达达运单失败'
];

if(!empty($receiveData)){
	$receiveData = json_decode($receiveData, 1);
	$OrderID = $receiveData['order_id'];
	$signature = $receiveData['signature'];
	
	$signArr = ['client_id' => $receiveData['client_id'], 'order_id' => $receiveData['order_id'], 'update_time' => $receiveData['update_time']];
	asort($signArr, 2);
	$sign = md5(implode('', $signArr));
	if($signature==$sign){
		$OrderID = substr($OrderID,10);
		$rsOrder = DB::table('user_order')->where(['Order_ID' => $OrderID])->first();
		$data = ['dada' => [
				'dm_name' => $receiveData['dm_name'],
				'dm_mobile' => $receiveData['dm_mobile'],
				'dm_id' => $receiveData['dm_id'],
				'cancel' => [
					'code' => $receiveData['cancel_from'],
					'name' => $cancel_status[$receiveData['cancel_from']],
					'reason' => $receiveData['cancel_reason']
				],
				'order_status' => [
					'code' => $receiveData['order_status'],
					'name' => $order_status[$receiveData['order_status']],
				]
			]
		];
		$flag = DB::table('user_order')->where(['Order_ID' => $OrderID])->update(['Order_Ext' => json_encode($data, JSON_UNESCAPED_UNICODE)]);

		//开始小程序模板消息推送
		$rsUser = DB::table('user')->where(['User_ID' => $rsOrder['User_ID']])->first();
		if (!empty($rsUser) && !empty($rsUser['User_OpenID_Lp'])) {
			//查询是否有合适的推送授权码
			$rsCode = DB::table('template_message')->where(['userid' => $rsUser['User_ID'], 'status' => 1])->first();
			if (!empty($rsCode)) {
				//更新推送授权码状态,和次数
				$update_data = ['times' => $rsCode['times'] - 1];
				if ($rsCode['times'] == 1) {
					$update_data['status'] = 0;
				}
				$update_flag = DB::table('template_message')->where(['id' => $rsCode['id']])->update($update_data);
				if ($update_flag) {
					//如果更新成功,开始发送模板消息
					$access_token = getAccessTokenLp(APPID_LP, APPSECRET_LP, $rsOrder['Users_ID']);
					$config = DB::table('shop_config')->where(['Users_ID' => $rsOrder['Users_ID']])->first();
					$biz_info = DB::table('biz')->where(['Biz_ID' => $rsOrder['Biz_ID']])->first();
					$temp_data = [];
					switch ($receiveData['order_status']) {
						case 2:
							if (!empty($config['accept_temp_id'])) {
								$temp_data = [
									'touser' => $rsUser['User_OpenID_Lp'],
									'template_id' => $config['accept_temp_id'],
									'form_id' => $rsCode['code'],
									'data' => [
										'keyword1' => [
											'value' => $biz_info['Biz_Name']
										],
										'keyword2' => [
											'value' => $rsOrder['Order_CreateTime'] . $rsOrder['Order_ID']
										],
										'keyword3' => [
											'value' => date('Y-m-d H:i:s', time())
										],
										'keyword4' => [
											'value' => $receiveData['dm_name']
										],
										'keyword5' => [
											'value' => $receiveData['dm_mobile']
										]
									]
								];
							}
							break;
						case 3:
							if (!empty($config['get_temp_id'])) {
								$temp_data = [
									'touser' => $rsUser['User_OpenID_Lp'],
									'template_id' => $config['get_temp_id'],
									'form_id' => $rsCode['code'],
									'data' => [
										'keyword1' => [
											'value' => $rsOrder['Order_CreateTime'] . $rsOrder['Order_ID']
										],
										'keyword2' => [
											'value' => $receiveData['dm_name']
										],
										'keyword3' => [
											'value' => $receiveData['dm_mobile']
										],
										'keyword4' => [
											'value' => $order_status[$receiveData['order_status']]
										]
									]
								];
							}
							break;
						case 4:
							if (!empty($config['comp_temp_id'])) {
								$temp_data = [
									'touser' => $rsUser['User_OpenID_Lp'],
									'template_id' => $config['comp_temp_id'],
									'form_id' => $rsCode['code'],
									'data' => [
										'keyword1' => [
											'value' => $rsOrder['Order_CreateTime'] . $rsOrder['Order_ID']
										],
										'keyword2' => [
											'value' => $order_status[$receiveData['order_status']]
										]
									]
								];
							}
							break;
					}

					if (!empty($temp_data)) {
						$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $access_token;
						$ch = curl_init($url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($temp_data, JSON_UNESCAPED_UNICODE));
						curl_exec($ch);
					}

				}
			}
		}


		require_once(CMS_ROOT.'/include/library/weixin_message.class.php');
		$weixin_message = new weixin_message($DB,$rsOrder['Users_ID'],$rsOrder['User_ID']);

		switch ($receiveData['order_status']) {
			case 2:
				$contentStr = '配送员' . $receiveData['dm_name'] .',联系电话:'. $receiveData['dm_mobile'] .',已接到您的订单,现在正赶往商家';
				break;
			case 3:
				$contentStr = '配送员' . $receiveData['dm_name'] .',联系电话:'. $receiveData['dm_mobile'] .',已成功取到您的商品';
				break;
			case 4:
				$contentStr = '您的订单已成功送达';
				break;
			default :
				$contentStr = '订单异常';
				break;
		}
		$weixin_message->sendscorenotice($contentStr);
		if($flag!==false){
			header('Content-type: application/json');
			header('HTTP/1.1 200 OK');
		}else{
			header('Content-type: application/json');
			header('HTTP/1.1 403 the modify table is error');
		}
	}else{
		header('Content-type: application/json');
		header('HTTP/1.1 403 signature is error');
	}
}else{
	header('Content-type: application/json');
	header('HTTP/1.1 403 not found');
}
?>