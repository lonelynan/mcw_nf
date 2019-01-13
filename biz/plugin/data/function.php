<?php
use Illuminate\Database\Capsule\Manager as DB;

function get_cityCode($source_id){
	$config = array();
	$config['app_key'] = APP_KEY;
	$config['app_secret'] = APP_SECRET;
	$config['source_id'] = $source_id;
	$config['url'] = APP_DOMAIN . '/api/cityCode/list';

	$obj = new DadaOpenapi($config);
	$reqStatus = $obj->makeRequest([]);
	if (!$reqStatus) {
		//接口请求正常，判断接口返回的结果，自定义业务操作
		if ($obj->getCode() == 0) {
			return $obj->getResult();
		}else{
			return $obj->getMsg();
		}
	}
	return [];
}

function add_shop($postData){

	$config = array();
	$config['app_key'] = APP_KEY;
	$config['app_secret'] = APP_SECRET;
	$config['source_id'] = '';
	$config['url'] = APP_DOMAIN . '/merchantApi/merchant/add';

	$obj = new DadaOpenapi($config);
	$reqStatus = $obj->makeRequest($postData);
	if (!$reqStatus) {
		//接口请求正常，判断接口返回的结果，自定义业务操作
		return $obj->getData();
	}
	return ['status' => 'fail!', 'errorCode' => '1', 'code' => 1, 'msg' => '不正确的请求'];
}

function add_stores($postData, $source_id){

	$config = array();
	$config['app_key'] = APP_KEY;
	$config['app_secret'] = APP_SECRET;
	$config['source_id'] = $source_id;
	$config['url'] = APP_DOMAIN . '/api/shop/add';

	$obj = new DadaOpenapi($config);
	$reqStatus = $obj->makeRequest([$postData]);
	if (!$reqStatus) {
		//接口请求正常，判断接口返回的结果，自定义业务操作
		return $obj->getData();
	}
	return ['status' => 'fail!', 'errorCode' => '1', 'code' => 1, 'msg' => '不正确的请求'];
}

function edit_stores($postData, $source_id){
	$config = array();
	$config['app_key'] = APP_KEY;
	$config['app_secret'] = APP_SECRET;
	$config['source_id'] = $source_id;
	$config['url'] = APP_DOMAIN . '/api/shop/update';

	$obj = new DadaOpenapi($config);
	$reqStatus = $obj->makeRequest($postData);
	if (!$reqStatus) {
		//接口请求正常，判断接口返回的结果，自定义业务操作
		return $obj->getData();
	}
	return ['status' => 'fail!', 'errorCode' => '1', 'code' => 1, 'msg' => '不正确的请求'];
}

//经纬度转换     百度 转 高德（腾讯）
if (!function_exists('bd_trans_gd')) {
    function bd_trans_gd($bd_lat, $bd_lon){
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $bd_lon - 0.0065;
        $y = $bd_lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $gg_lon = $z * cos($theta);
        $gg_lat = $z * sin($theta);
        return [
            'lat' => $gg_lat,
            'lng' => $gg_lon
        ];
    }
}

//经纬度转换     高德（腾讯）转 百度
if (!function_exists('gd_trans_bd')) {
    function gd_trans_bd($gd_lat, $gd_lon)
    {
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $gd_lon;
        $y = $gd_lat;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
        $bd_lon = $z * cos($theta) + 0.0065;
        $bd_lat = $z * sin($theta) + 0.006;
        return [
            'lat' => $bd_lat,
            'lng' => $bd_lon
        ];
    }
}

//前台获取运费接口
function queryDeliverFee($postData, $source_id)
{
	$config = array();
	$config['app_key'] = APP_KEY;
	$config['app_secret'] = APP_SECRET;
	$config['source_id'] = $source_id;
	$config['url'] = APP_DOMAIN . '/api/order/queryDeliverFee';
	$obj = new DadaOpenapi($config);
	$reqStatus = $obj->makeRequest($postData);
	if (!$reqStatus) {
		//接口请求正常，判断接口返回的结果，自定义业务操作
		return $obj->getData();
	}
	return ['status' => 'fail!', 'errorCode' => '1', 'code' => 1, 'msg' => '不正确的请求'];
}


//前台获取运费接口
function acceptNotify($postData, $source_id)
{
	$config = array();
	$config['app_key'] = APP_KEY;
	$config['app_secret'] = APP_SECRET;
	$config['source_id'] = $source_id;
	$config['url'] = APP_DOMAIN . '/api/order/accept';
	$obj = new DadaOpenapi($config);
	$reqStatus = $obj->makeRequest($postData);
	if (!$reqStatus) {
		//接口请求正常，判断接口返回的结果，自定义业务操作
		return $obj->getData();
	}
	return ['status' => 'fail!', 'errorCode' => '1', 'code' => 1, 'msg' => '不正确的请求'];
}

//发单接口
function addOrder($postData, $source_id)
{
	$config = array();
	$config['app_key'] = APP_KEY;
	$config['app_secret'] = APP_SECRET;
	$config['source_id'] = $source_id;
	$config['url'] = APP_DOMAIN . '/api/order/addOrder';
	$obj = new DadaOpenapi($config);
	$reqStatus = $obj->makeRequest($postData);
	if (!$reqStatus) {
		//接口请求正常，判断接口返回的结果，自定义业务操作
		return $obj->getData();
	}
	return ['status' => 'fail!', 'errorCode' => '1', 'code' => 1, 'msg' => '不正确的请求'];
}

//查询余额接口
function balanceQuery($postData, $source_id)
{
	$config = array();
	$config['app_key'] = APP_KEY;
	$config['app_secret'] = APP_SECRET;
	$config['source_id'] = $source_id;
	$config['url'] = APP_DOMAIN . '/api/balance/query';
	$obj = new DadaOpenapi($config);
	$reqStatus = $obj->makeRequest($postData);
	if (!$reqStatus) {
		//接口请求正常，判断接口返回的结果，自定义业务操作
		return $obj->getData();
	}
	return ['status' => 'fail!', 'errorCode' => '1', 'code' => 1, 'msg' => '不正确的请求'];
}

//获取达达物流接口数据
function getDataFee($UsersID, $BizID, $StoreID, $UserID, $AddressID, $total, $type='dada'){
	global $DB;
	//获取商家里的商户信息
	$rsBiz = DB::table('biz')->where(['Users_ID' => $UsersID,'Biz_ID' => $BizID])->first(['Biz_Ext']);
	if(empty($rsBiz['Biz_Ext'])){
		return ['status' => 0, 'msg' => '未创建商户请联系商家', 'data' => []];
	}
	//获取商户的source_id
	$source_id = json_decode($rsBiz['Biz_Ext'], true)['dada']['source_id'];
	//判断门店是否存在
	$rsStores = DB::table('stores')->where(['Users_ID' => $UsersID, 'Stores_ID' => $StoreID])->first();
	if(empty($rsStores)){
		return ['status' => 0, 'msg' => '请选择门店', 'data' => []];
	}
	//获取并判断用户收货地址是否存在
	$rsAddress = DB::table('user_address')->where(['Users_ID' => $UsersID, 'User_ID' => $UserID, 'Address_ID' => $AddressID])->first();
	if(empty($rsAddress)){
		return ['status' => 0, 'data' => [], 'msg' => '获取地址错误'];	
	}
	/********** 根据省市区编码解析成具体的省市区的汉字描述 start **********/
	$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
	$area_array = json_decode($area_json,true);
	$province_list = $area_array[0];
	$Province = $province_list[$rsAddress['Address_Province']];
	$City = $area_array['0,'.$rsAddress['Address_Province']][$rsAddress['Address_City']];
	$Area = $area_array['0,'.$rsAddress['Address_Province'].','.$rsAddress['Address_City']][$rsAddress['Address_Area']];
	$City = str_replace('市','',$City);
	/********** 根据省市区编码解析成具体的省市区的汉字描述 end **********/
	$rsShippingList = DB::table('third_shipping')->where(['Status' => 1, 'Tag' => $type])->first(['ID']);
	if(empty($rsShippingList)){
		return ['status' => 0, 'msg' => '商户开发者信息没有配置，请配置', 'data' => []];
	}
	
	$rsThirdConfig = DB::table('third_shipping_config')->where('Tid', $rsShippingList['ID'])->first();
	if(empty($rsThirdConfig)){
		return ['status' => 0, 'msg' => '商户开发者信息没有配置，请配置', 'data' => []];
	}
	$ext = json_decode($rsThirdConfig['ext'], 1);

    !defined('APP_KEY') && define('APP_KEY', $ext['app_key']);
	!defined('APP_SECRET') && define('APP_SECRET', $ext['app_secret']);
	!defined('APP_DOMAIN') && define('APP_DOMAIN', 'http://' . $ext['domain']);

	//获取城市列表
	$result = get_cityCode($source_id);
    $citylist = [];
	$cityCode = 0;
	if (is_array($result)) {
        foreach($result as $k => $v){
            if($v['cityName'] == $City){
                $cityCode = $v['cityCode'];
                break;
            }
        }
    } else {
	    return ['status' => 0, 'msg' => $result, 'data' => []];
    }

	if(!$cityCode){
		return ['status' => 2, 'msg' => '达达配送不支持本地区', 'data' => []];
	}

    //请求获取物流接口
	$result = queryDeliverFee([
		'shop_no' => $rsStores['Stores_No'],
		'origin_id' => date("YmdHis", time()),
		'city_code' => $cityCode,
		'cargo_price' => $total,
		'is_prepay' => 0,
		'expected_fetch_time' => time() + 20 * 60,
		'receiver_name' =>  $rsAddress['Address_Name'],
		'receiver_phone' => $rsAddress['Address_Mobile'],
		'receiver_address' => $Province . $City . $Area . $rsAddress['Address_Detailed'],
		'callback' => 'http://' . $_SERVER['HTTP_HOST'] . '/biz/plugin/dada/notify_url.php'
	],$source_id);
	if($result['status']!='success'){
		return ['status' => 0, 'msg' => '该配送方式暂时不可用', 'data' => []];
	}
	return ['status' => 1, 'msg' => '', 'data' => $result['result']];
}

//获取百度坐标系
function getBdPosition($address, $ak_baidu)
{
	$url = "http://api.map.baidu.com/geocoder/v2/?address=".$address ."&output=json&ak=".$ak_baidu;
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	$data = curl_exec($curl);
	curl_close($curl);
	$position = json_decode($data, true);
	return $position['result']['location'];
}

//获取区域名称
function getAreaName($UsersID, $UserID, $rsAddress){
	
	$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
	$area_array = json_decode($area_json,true);
	unset($area_json);
	if(empty($rsAddress)){
		return [];	
	}
	$province_list = $area_array[0];
	$Province = $province_list[$rsAddress['Address_Province']];
	$City = $area_array['0,'.$rsAddress['Address_Province']][$rsAddress['Address_City']];
	$Area = $area_array['0,'.$rsAddress['Address_Province'].','.$rsAddress['Address_City']][$rsAddress['Address_Area']];
	
	return ['Province' => $Province, 'City' => $City, 'Area' => $Area, 'Address' => $rsAddress['Address_Detailed']];
}

function getStoreslist($UsersID, $BizID, $UserID, $AddressID, $ak_baidu)
{
	$rsAddress = DB::table('user_address')->where(['Users_ID' => $UsersID, 'User_ID' => $UserID, 'Address_ID' => $AddressID])->first();
	$Areas = getAreaName($UsersID, $UserID, ['Address_Province' => $rsAddress['Address_Province'], 'Address_City' => $rsAddress['Address_City'], 'Address_Area' => $rsAddress['Address_Area'], 'Address_Detailed' => $rsAddress['Address_Detailed']]);
	
	//获取百度坐标
	$address = $Areas['Province'] . $Areas['City'] . $Areas['Area'] . $Areas['Address'];
	$location = getBdPosition($address, $ak_baidu);
	$position = bd_trans_gd($location['lat'], $location['lng']);
	$currentLng = $position['lng'];
	$currentLat = $position['lat'];

	$sql = "
select Stores_ID,Biz_ID,Stores_Name,Stores_Telephone,Stores_Address,Stores_PrimaryLng,Stores_PrimaryLat,(6366000 * ACOS(COS({$currentLat}*3.14169/180)*COS({$currentLng}*3.14169/180)*COS(Stores_PrimaryLat*3.14169/180)*COS(Stores_PrimaryLng*3.14169/180) + COS({$currentLat}*3.14169/180)*SIN({$currentLng}*3.14169/180)*COS(Stores_PrimaryLat*3.14169/180)*SIN(Stores_PrimaryLng*3.14169/180) + SIN({$currentLat}*3.14169/180)*SIN(Stores_PrimaryLat*3.14169/180))) as distance from stores where Users_ID='{$UsersID}' and Biz_ID in({$BizID}) and Stores_Status=1 order by distance asc limit 50";
	$result = DB::select($sql);
	return $result;
}