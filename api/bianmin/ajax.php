<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/shipping.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/flow.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/order.php');
require_once ($_SERVER ["DOCUMENT_ROOT"] . '/Framework/Ext/virtual.func.php');
require_once ($_SERVER ["DOCUMENT_ROOT"] . '/Framework/Ext/sms.func.php');

ini_set("display_errors","On");
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo 'error';
	exit;
}

$action = empty($_REQUEST["action"]) ? "" : $_REQUEST["action"];
if($action == "check_phone"){//从实物购物车中删除产品
	if(empty($_POST['input'])){
		$data = array('msg'=>'');
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$phone=$_POST['input'];
	$json_sms = file_get_contents('https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$phone.'');	
	$json_sms = str_replace('__GetZoneResult_ =', '', $json_sms);
	$json_sms = str_replace(['{','}'], ['',''], $json_sms);
	$json_sms_arr = explode(',', $json_sms);
	$Provider=$json_sms_arr['2'];
	//转换编码
	$a=strstr(iconv('GBK', 'utf-8', $Provider),'中国');
	
	echo json_encode(array('msg'=>$a),JSON_UNESCAPED_UNICODE);
	exit;
	
}elseif($action=='get_price'){
	
	//数组的键名
   	$num=$_POST['i'];
   	//服务类别
   	$server_type=$_POST['Server_Type'];
   	//服务商的类别
   	$type=$_POST['type'];
   	//获取数据库数据
   	$res=$DB->GetRs('bianmin_server','*',"WHERE Users_ID='".$UsersID."'and Server_Type='".$server_type."'and Service_Provider='".$type."'");
	if($server_type==0){//话费
		$new=json_decode($res['Server_Newprice']);
	}else{
		$new=json_decode($res['Server_Oldprice']);
	}
	$arr=json_encode(array('price'=>$new[$num]), JSON_UNESCAPED_UNICODE);
	echo $arr;
	exit;			
}elseif($action=='create_order'){
	$rsConfig = $DB->GetRs("shop_config","*","where Users_ID='".$UsersID."'");
	//分销相关设置
	$dis_config = dis_config($UsersID);
	//合并参数
	$rsConfig = array_merge($rsConfig,$dis_config);
	$owner = get_owner($rsConfig, $UsersID);
	//支付金额
	$money=$_POST['money'];
	//支付人id
	$userid=$_SESSION[$UsersID.'User_ID'];
	//支付便民服务的类型   话费[0]还是流量[1]
	$bianmintype=$_POST['Server_Type'];
	//支付通信商名字
	$provider=$_POST['type'];
	//支付类别
	$order_type='bianmin';
	//会员手机号
	$userphone=$_SESSION[$UsersID.'User_Mobile'];
	//充值手机号   写入到  地址电话上
	$phonenum=$_POST['phone'];
	//添加时间
	$time=time();
	//订单状态
	$orders_tatus='1';
	//表名
	$table='user_order';
	//判断
	$qianmi_money = 0;
	if($bianmintype==0){
		//获取充值金额
		$DB->query("SELECT * FROM bianmin_server WHERE Users_ID='".$UsersID."'and Server_Type='".$bianmintype."'and Service_Provider='".$provider."'");
		$res=$DB->fetch_assoc();
		//应交款数组
		$new=json_decode($res['Server_Newprice']);
		//应交款数组的总数
		$number=count($new);
		
		for($i=0; $i<$number; $i++){ 
			if ($money==$new[$i]) {
				//充值金额数组
				$old=json_decode($res['Server_Oldprice']);
				//充值金额
				$qianmi_money=$old[$i];
			}
		}
	}else{
			//获取充值金额   流量的值  和话费的值 获取的数组   是相反的
		$DB->query("SELECT * FROM bianmin_server WHERE Users_ID='".$UsersID."'and Server_Type='".$bianmintype."'and Service_Provider='".$provider."'");
		$res=$DB->fetch_assoc();
		//应交款数组
		$new=json_decode($res['Server_Oldprice']);
		//应交款数组的总数
		$number=count($new);
		for($i=0; $i<$number; $i++){ 
			if ($money==$new[$i]) {
				//充值金额数组
				$old=json_decode($res['Server_Newprice']);
				//充值金额
				$qianmi_money=$old[$i];
			}
		}
	}
	//货物详情
	$products_name = ($provider==0 ? '联通' : '移动').'手机号'.$phonenum.'充'.($bianmintype==0 ? $qianmi_money.'元话费' : $qianmi_money.'流量');
	$CartList[$res['Server_ID']][] = array(
		"ProductsName" => $products_name,
		"ImgPath" => '',
		"ProductsPriceX" => $money,
		"ProductsPriceY" => $money,
		"ProductsWeight" => 0,
		"Products_Shipping" => '',
		"Products_Business" => '',
		"Shipping_Free_Company" => '',
		"IsShippingFree" => 1,
		"OwnerID" => $owner['id'],
		"ProductsIsShipping" => 0,
		"Qty" => 1,
		"spec_list" => '',
		"Property" => ''
	);

	$cartlist=json_encode($CartList, JSON_UNESCAPED_UNICODE);
	//刷新限制
	// 获取session中的订单id
	
	//生成千米充值订单
	$billid = '';
	require($_SERVER["DOCUMENT_ROOT"]."/pay/qianmi/OpenSdk.php");
	$re = $DB->GetRs("mycount","*","WHERE usersid='".$UsersID."'");
	
	$appKey=$re['appkey'];
	$appSecret=$re['appsecret'];
	$accessToken = $re['token'];
	
			
	$loader  = new QmLoader;
	$loader  -> autoload_path  = array(CURRENT_FILE_DIR.DS."client");
	$loader  -> init();
	$loader  -> autoload();
			
	$client  = new OpenClient;
	$client  -> appKey =  $appKey;
	$client  -> appSecret =  $appSecret;
	
	if($bianmintype==0){//话费充值			
		//查询单个话费充值商品
		$req = new RechargeMobileGetItemInfoRequest;
		$req->setMobileNo("$phonenum");
		$req->setRechargeAmount("$qianmi_money");	
		$res_0 = $client->execute($req, $accessToken);
		
		if(!empty($res_0->itemId)){
			$req_1 = new RechargeMobileCreateBillRequest;
			$req_1->setItemId($res_0->itemId);
			$req_1->setMobileNo("$phonenum");
			$req_1->setRechargeAmount("$qianmi_money");
			$res_1 = $client->execute($req_1, $accessToken);
			if(!empty($res_1->billId)){
				$billid = $res_1->billId;
			}
		}
	}else{//流量充值
		$req = new MobileFlowItemsList2Request;
		$qianmi_money = strtoupper(strval($qianmi_money));
		$req->setFlow("$qianmi_money");
		$req->setMobileNo("$phonenum");
		$res_0 = $client->execute($req, $accessToken);
		$res_0 =  json_decode( json_encode( $res_0),true);
		
		if(!empty($res_0['items']['item'])){
			$res_list = $res_0['items']['item'];
			$itemid = $res_list[0]['itemId'];
			if(!empty($itemid)){
				$req_1 = new MobileFlowCreateBillRequest;
				$req_1->setItemId($itemid);
				$req_1->setMobileNo("$phonenum");
				$res_1 = $client->execute($req_1, $accessToken);
				$res_1 = json_decode(json_encode($res_1),true);
				$bb=json_decode(json_encode($res_1),true);
			
				if(!empty($res_1['orderDetailInfo']['billId'])){
					
					$billid = $res_1['orderDetailInfo']['billId'];
				}
			}
		}

	}
	

	if(empty($billid)){
		$arr=json_encode(array('t'=>false), JSON_UNESCAPED_UNICODE);
		echo $arr;
		exit;
	}
	
	//插入表操作
	$table='user_order';
	$t = $DB->Add($table,array('Users_ID'=>$UsersID,'User_ID'=>$userid,'Order_Type'=>$order_type,'Address_Mobile'=>$phonenum,'Order_CartList'=>$cartlist,'Order_CreateTime'=>$time,'Order_Status'=>$orders_tatus,'Order_TotalPrice'=>$money,'Bianmintype'=>$bianmintype,'Service_Provider'=>$provider,'Service_billId'=>$billid));			
	//返回执行后的id
	$id = $DB->insert_id();
	//加入分销记录
	if($t){
		if($owner['id']>0){
			Dis_Record::observe(new DisRecordObserver());
			$res['Products_Name'] = $products_name;
			add_distribute_record_bianmin($UsersID,$owner['id'],$money,$res['Server_ID'],1,$id,$res);
		}
		
		$arr=json_encode(array('t'=>$t, 'orderId' => $id), JSON_UNESCAPED_UNICODE);
		echo $arr;
		exit;
	}else{
		$arr=json_encode(array('t'=>false), JSON_UNESCAPED_UNICODE);
		echo $arr;
		exit;
	}
	
	//返回执行后的id
	//$SESSION['bianmin_order']=$id;
	//订单编号
	//$time=time();
	//$OrderID=date("YmdHis",$time).$id;
	//将订单号写入session
	//$_SESSION['Order_ID']=array($order);
	//返回ajax 进行判断
	
}elseif($action == "deal_chongzhi_order_pay"){
	if(empty($_POST['OrderID'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($_POST['password'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$OrderID = $_POST['OrderID'];
	$rsOrder = $DB->GetRs('user_order','*','where Users_ID="'.$UsersID.'" and Order_ID='.$OrderID.' and User_ID='.$_SESSION[$UsersID.'User_ID']);
	
	if(!$rsOrder){
		$Data = array(
			"status"=> 0,
			"msg"=>'订单不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsOrder['Order_Status']<>1){
		$Data = array(
			"status"=> 0,
			"msg"=>'该订单不是待付款状态'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$rsUser = $DB->GetRs('user','User_Money,User_PayPassword','where User_ID='.$rsOrder['User_ID']);

	if($rsUser['User_PayPassword'] != md5($_POST['password'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'支付密码错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsUser['User_Money'] < $rsOrder['Order_TotalPrice']){
		$Data = array(
			"status"=> 0,
			"msg"=>'当前余额不足'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/pay_order.class.php');
	$pay_order = new pay_order($DB, $OrderID);
	$response = $pay_order->deal_chongzhi_order();
	
	if($response['status']){
		$Data = array(
			"status"=> 1,
			'msg'=>'话费充值成功',
			'url'=>distribute_url()
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}else{
		$Data = array(
			"status"=> 0,
			"msg"=>'支付失败'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
}elseif($action=='get_proxy_pay_method'){
	if(empty($_POST['OrderID'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if(empty($_POST['method'])){
		$Data = array(
			"status"=> 0,
			"msg"=>'参数错误'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$method = $_POST['method'];
	
	$OrderID = $_POST['OrderID'];
	$rsOrder = $DB->GetRs('user_order','*','where Users_ID="'.$UsersID.'" and Order_ID='.$OrderID.' and User_ID='.$_SESSION[$UsersID.'User_ID']);
	
	if(!$rsOrder){
		$Data = array(
			"status"=> 0,
			"msg"=>'订单不存在'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($rsOrder['Order_Status']<>1){
		$Data = array(
			"status"=> 0,
			"msg"=>'该订单不是待付款状态'
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
	
	if($method==1){//微支付
		$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
		if($rsPay["PaymentWxpayEnabled"]==0 || empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“微支付”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
		if($rsPay["PaymentWxpayType"]==1){
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/wxpay2/sendto_qiammi.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/wxpay/sendto_qiammi.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
	}elseif($method==2){//支付宝		
		if($rsPay["Payment_AlipayEnabled"]==0 || empty($rsPay["Payment_AlipayPartner"]) || empty($rsPay["Payment_AlipayKey"]) || empty($rsPay["Payment_AlipayAccount"])){
			$Data = array(
				"status"=> 0,
				"msg"=>'商家“支付宝”支付方式未启用或信息不全，暂不能支付！'
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}else{
			$Data = array(
				"status"=> 1,
				"url"=>'/pay/alipay/sendto_qiammi.php?UsersID='.$UsersID.'_'.$OrderID
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	}
}
?>