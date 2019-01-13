<?php
use Illuminate\Database\Capsule\Manager as Capsule;

//平台工具类
if ( ! function_exists('base_url'))
{
	/**
	 * 获取某用户下属分销账号
	 * @param  String $uri        uri参数
	 * @return String $base_url  本站基地址
	 */
	function base_url($uri = ''){
		$base_url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$uri;
		return $base_url;
	}
	
}

if ( ! function_exists('shop_url'))
{
	/**
	 * 获取某用户下属分销账号
	 * @param  String $uri        uri参数
	 * @return String $base_url  本站基地址
	 */
	function shop_url($uri = ''){
		
		$UsersID = $_GET['UsersID'];
		return base_url().'api/'.$UsersID.'/shop/'.$uri;
	}
	
}

if ( ! function_exists('shop_urlwzw'))
{
	/**
	 * 获取某用户下属分销账号
	 * @param  String $uri        uri参数
	 * @return String $base_url  本站基地址
	 */
	function shop_urlwzw($uri = ''){
		
		$UsersID = $_GET['UsersID'];
		return base_url().'api/'.$UsersID.'/shop/wzw/'.$uri;
	}
	
}

if ( ! function_exists('distribute_url'))
{
	/**
	 * 获取某用户下属分销账号
	 * @param  String $uri        uri参数
	 * @return String $base_url  本站基地址
	 */
	function distribute_url($uri = ''){
		
		$UsersID = $_GET['UsersID'];
		return base_url().'api/'.$UsersID.'/distribute/'.$uri;
	}
	
}


if ( ! function_exists('shop_config'))
{
	/**
	 * 获取本店配置
	 * @param  String $UsersID   本店唯一标示
	 * @param  Array $fields    指定的字段
	 * @return Array $shop_config   返回结果
	 */
	function shop_config($UsersID = '',$fields = array()){
		
		$builder = Shop_Config::where('Users_ID',$UsersID);
		if(count($fields) >0 ){
		    if(!$builder->first($fields)) return false;
			$shop_config = $builder->first($fields)
			                       ->toArray();	
		}else{
		    if(!$builder->first()) return false;
			$shop_config = $builder->first()
			                       ->toArray();
		}			
		
		return !empty($shop_config)?$shop_config:false;
	}
}

if ( ! function_exists('dis_config'))
{
	/**
	 * 获取本店配置
	 * @param  String $UsersID   本店唯一标示
	 * @param  Array $fields    指定的字段
	 * @return Array $shop_config   返回结果
	 */
	function dis_config($UsersID = '',$fields = array()){
		
		$builder = Dis_Config::where('Users_ID',$UsersID);
		if(count($fields) >0 ){
		    if(!$builder->first($fields)) return false;
			$dis_config = $builder->first($fields)
			                       ->toArray();	
		}else{
		    if(!$builder->first()) return false;
			$dis_config = $builder->first()
			                       ->toArray();
		}			
		
		return !empty($dis_config)?$dis_config:false;
	}
}


if ( ! function_exists('shop_user_config'))
{
	/**
	 * 获取本店针对用户的配置
	 * @param  String $UsersID   本店唯一标示
	 * @param  Array $fields    指定的字段
	 * @return Array $shop_user_config   返回结果
	 */
	function shop_user_config($UsersID = '',$fields = array()){
		
		$builder = User_Config::where('Users_ID',$UsersID);
		if(count($fields) >0 ){
		    if(!$builder->first($fields)) return false;
			$shop_user_config = $builder->first($fields)->toArray();	
		}else{
		    if(!$builder->first()) return false;
			$shop_user_config = $builder->first()->toArray();	
		}
		return !empty($shop_user_config)?$shop_user_config:false;
	}
}

if ( ! function_exists('round_pad_zero'))
{
	/**
	* 浮点数四舍五入补零函数
	* 
	* @param float $num
	*        	待处理的浮点数
	* @param int $precision
	*        	小数点后需要保留的位数
	* @return float $result 处理后的浮点数
	*/
	function round_pad_zero($num, $precision) {
		if ($precision < 1) {  
				return round($num, $precision);  
			}  
		
			$r_num = round($num, $precision);  
			$num_arr = explode('.', "$r_num");  
			if (count($num_arr) == 1) {  
				return "$r_num" . '.' . str_repeat('0', $precision);  
			}  
			$point_str = "$num_arr[1]";  
			if (strlen($point_str) < $precision) {  
				$point_str = str_pad($point_str, $precision, '0');  
			}  
			return $num_arr[0] . '.' . $point_str;  
	}  
}

if ( ! function_exists('getorderno'))
{		
	function getorderno($oid) {
		$builder = Order::where('Order_ID',$oid);
		$rsOrder = $builder->first(array('Order_Type','Order_CreateTime','Order_Code'));
		if ($rsOrder) {
			$rsOrder->toArray();
		} else {
			return false;
		}
		if($rsOrder['Order_Type'] == 'pintuan' || $rsOrder['Order_Type'] == 'dangou'){
                $orderno = $rsOrder['Order_Code'];
            }else{
                $orderno = date("Ymd", $rsOrder["Order_CreateTime"]) . $oid;
            }
			return $orderno;  
	}  
}


if(!function_exists('write_file')){
	
	 /**
	 * Write File
	 *
	 * Writes data to the file specified in the path.
	 * Creates a new file if non-existent.
	 *
	 * @param	string	$path	File path
	 * @param	string	$data	Data to write
	 * @param	string	$mode	fopen() mode (default: 'wb')
	 * @return	bool
	 */
	function write_file($path, $data, $mode = 'wb')
	{
		if ( ! $fp = @fopen($path, $mode))
		{
			return FALSE;
		}

		flock($fp, LOCK_EX);

		for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result)
		{
			if (($result = fwrite($fp, substr($data, $written))) === FALSE)
			{
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return is_int($result);
	}
}


if ( ! function_exists('read_file'))
{
	/**
	* Read File
	*
	* Opens the file specfied in the path and returns it as a string.
	*
	* @access	public
	* @param	string	path to file
	* @return	string
	*/	
	function read_file($file)
	{
		if ( ! file_exists($file))
		{
			return FALSE;
		}

		if (function_exists('file_get_contents'))
		{
			return file_get_contents($file);
		}

		if ( ! $fp = @fopen($file, FOPEN_READ))
		{
			return FALSE;
		}

		flock($fp, LOCK_SH);

		$data = '';
		if (filesize($file) > 0)
		{
			$data =& fread($fp, filesize($file));
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return $data;
	}
}


if ( ! function_exists('build_withdraw_sn'))
{	
	/**
	* 得到提现流水号
	* @return  string
	*/
	function build_withdraw_sn() {
		/* 选择一个随机的方案 */
		//mt_srand((double) microtime() * 1000000);

		return 'WD' . date('Ymd') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
	}

}
	
if ( ! function_exists('generateUpdateBatchQuery'))
{	
	/**
	 *生成mysql批量更新语句
	 */
	 function generateUpdateBatchQuery($tableName = "", $multipleData = array()){

		if( $tableName && !empty($multipleData) ) {

			// column or fields to update
			$updateColumn = array_keys($multipleData[0]);
			$referenceColumn = $updateColumn[0]; //e.g id
			unset($updateColumn[0]);
			$whereIn = "";

			$q = "UPDATE ".$tableName." SET "; 
			foreach ( $updateColumn as $uColumn ) {
				$q .=  $uColumn." = CASE ";

				foreach( $multipleData as $data ) {
					$q .= "WHEN ".$referenceColumn." = ".$data[$referenceColumn]." THEN '".$data[$uColumn]."' ";
				}
				$q .= "ELSE ".$uColumn." END, ";
			}
			foreach( $multipleData as $data ) {
				$whereIn .= "'".$data[$referenceColumn]."', ";
			}
			$q = rtrim($q, ", ")." WHERE ".$referenceColumn." IN (".  rtrim($whereIn, ', ').")";

			// Update  
			return $q;

		} else {
			return false;
		}
	}

}
	
if ( ! function_exists('handle_product_list'))
{
	/*
	 * 处理产品列表
	 * $flashsale  显示抢购活动是否开启
	 */
	function handle_product_list($list, $shop_url = '', $pintuan_url = ''){
	
		$result = array();

		foreach($list as $key=>$item){
            $product = $item;
            $product['Products_JSON'] = json_decode($item['Products_JSON'],TRUE);

			if(isset($product['Products_JSON']["ImgPath"])){
				$product['ImgPath'] = $product['Products_JSON']["ImgPath"][0];
			}else{
				$product['ImgPath'] =  'static/api/shop/skin/default/nopic.jpg';
			}

            //产品是否符合拼团条件
            $product['pt_flag'] = pro_pt_flag($product);

            //产品url
            if ($product['pt_flag']) {
                $product['link'] = (empty($pintuan_url) ? '/api/' . $product['Users_ID'] . '/' : $pintuan_url) . 'xiangqing/' . $product['Products_ID'] . '/';
            } else {
                $product['link'] = (empty($shop_url) ? '/api/' . $product['Users_ID'] . '/shop/' : $shop_url) . 'products/' . $product['Products_ID'] . '/';
            }

            $product['Products_PriceX'] = !empty($product['pt_flag']) ? $product['pintuan_pricex'] : $product['Products_PriceX'];

            $product['Products_PriceY'] = !empty($product['pt_flag']) ? $product['Products_PriceX'] : $product['Products_PriceY'];
		
			$result[$product['Products_ID']] = $product;
		}
		unset($key);
		unset($item);
		return $result;
	}
}

if ( ! function_exists('pro_pt_flag'))
{
    /*判断产品是否符合拼团条件*/
    function pro_pt_flag($proInfo){
        $flag = false;
        if (isset($proInfo['pintuan_flag']) && isset($proInfo['pintuan_people']) && isset($proInfo['pintuan_start_time']) && isset($proInfo['pintuan_end_time']) && isset($proInfo['pintuan_pricex'])) {
            $flag = $proInfo['pintuan_flag'] == 1 && $proInfo['pintuan_people'] >= 2 && $proInfo['pintuan_start_time'] < time() && $proInfo['pintuan_end_time'] > time() && $proInfo['pintuan_pricex'] > 0;    //产品是否符合拼团条件
        }
        return $flag;
    }
}

if ( ! function_exists('shop_flashsale_flag'))
{
    /*判断商城是否开启限时抢购活动*/
    function shop_flashsale_flag($Users_ID){
        global $DB;
        $active_info = $DB->GetRs('active', 'Active_ID', 'where Users_ID = "' . $Users_ID . '" and Status = 1 and Type_ID = 5 and starttime < ' . time() . ' and stoptime > ' . time());
        $flashsale = !empty($active_info) ? 1 : 0;
        return $flashsale;
    }
}

if ( ! function_exists('pro_flashsale_flag'))
{
    /*判断产品是否符合显示抢购条件*/
    function pro_flashsale_flag($proInfo, $Users_ID){
        $flashsale = shop_flashsale_flag($Users_ID);
        $flashsale = !empty($proInfo['flashsale_flag']) && !empty($flashsale) ? 1 : 0;
        return $flashsale;
    }
}


if( !function_exists('FetchRepeatMemberInArray')){
	
	/*获取数组中的重复元素*/
	function FetchRepeatMemberInArray($array) {
		// 获取去掉重复数据的数组
		$unique_arr = array_unique ( $array );
		// 获取重复数据的数组
		$repeat_arr = array_diff_assoc ( $array, $unique_arr );
		return $repeat_arr;
	} 
}

if (!function_exists('agent_area_check')) {
    // 判断选择的代理地区是否已被代理（一个地区只能有一个代理）
    function agent_area_check($area_id_list, $account_id, $Users_ID){
        global $DB;
        if (!empty($area_id_list)) {
            $res = $DB->GetAssoc('distribute_agent_areas', '*', 'where Users_ID = "' . $Users_ID . '" and Account_ID = ' . $account_id . ' and area_id in (' . implode(',', $area_id_list) . ')');

            if (!empty($res)) {
                return [
                    'status' => 0,
                    'msg' => implode(',', array_column($res, 'area_name')) . '已被代理，不能选择'
                ];
            }
        }
        return [
            'status' => 1
        ];
    }
}


if(!function_exists('sql_diff')){
	/**
	 *新老数据比对，确定哪个需要新增，哪个需要删除，哪个需要更新
	 *@param $new 新数据
	 *@param $old 老数据
	 *
	 */
	 function sql_diff($new,$old){
	 		$need_update = array_intersect($new,$old);
			$need_add =  array_diff($new,$old);
			$need_del =   array_diff($old,$new);
		
		    $res = array(
			    'need_update'=>$need_update,
				'need_add'=>$need_add,
				'need_del'=>$need_del
				);
				
			return $res;	

	 }
}

if(!function_exists('get_dropdown_collection')){
	//生成collection dropdown数组
	function get_dropdown_collection($data,$id_field,$value_field = ''){
	    $drop_down = array();
	
		foreach($data as $key=>$item){
			if(strlen($value_field) > 0 ){
				$drop_down[$item->$id_field] = $item->$value_field;
			}else{
				$drop_down[$item->$id_field] = $item;
			}
		}
	
		return $drop_down;
		
	}
	
}

if(!function_exists('get_dropdown_list')){
	//生成dropdown数组
	function get_dropdown_list($data,$id_field,$value_field = ''){
		$drop_down = array();
	
		foreach($data as $key=>$item){
			if(strlen($value_field) > 0 ){
				$drop_down[$item[$id_field]] = $item[$value_field];
			}else{
				$drop_down[$item[$id_field]] = $item;
			}
		}
	
		return $drop_down;
	}
	
}

if(!function_exists('sdate')){
	/*
	 *return short format date,not incluing hour,minutes,seconds
	 *
	 */
	function sdate($time = '')
	{
		if (strlen($time) == 0) {
			$time = time();
		}
		if (is_string($time)) {
			$time = intval($time);
		}
		return date('Y/m/d', $time);
	}
}

if(!function_exists('ldate')){
	/*
	 *return short format date,not incluing hour,minutes,seconds
	 *
	 */
	function ldate($time = '')
	{
		if (strlen($time) == 0) {
			$time = time();
		}
		if (is_string($time)) {
			$time = intval($time);
		}
		return date('Y/m/d H:i:s', $time);
	}
}

/*开始事务*/
if(!function_exists('begin_trans')){
	function begin_trans(){ Capsule::beginTransaction();}
}

/*回滚事务*/
if(!function_exists('back_trans')){
	function back_trans(){ Capsule::rollback();}
}

/*结束事务*/
if(!function_exists('commit_trans')){
	function commit_trans(){ Capsule::commit();}
}



if(!function_exists('generageUpdateBatchQuery')){
	/**
	 * 生成批量更新Sql语句
	 * @param  string $tableName    
	 * @param  array  $multipleData 需要更新的数据,以子数组第一字段作为case 条件
	 * @return string  $string 所生成的批量更新语句               
	 */
	function generageUpdateBatchQuery($tableName = "", $multipleData = array()){

    	if( $tableName && !empty($multipleData) ) {

        	// column or fields to update
        	$updateColumn = array_keys($multipleData[0]);
        	$referenceColumn = $updateColumn[0]; //e.g id
        	unset($updateColumn[0]);
        	$whereIn = "";

        	$q = "UPDATE ".$tableName." SET "; 
        	foreach ( $updateColumn as $uColumn ) {
            	$q .=  $uColumn." = CASE ";

            	foreach( $multipleData as $data ) {
                	$q .= "WHEN ".$referenceColumn." = ".$data[$referenceColumn]." THEN '".$data[$uColumn]."' ";
            	}
            	$q .= "ELSE ".$uColumn." END, ";
        	}
		
        	foreach( $multipleData as $data ) {
            	$whereIn .= "'".$data[$referenceColumn]."', ";
        	}
        	$q = rtrim($q, ", ")." WHERE ".$referenceColumn." IN (".  rtrim($whereIn, ', ').")";

        	// Update  
        	return $q;

    	} else {
        	return false;
    	}
	}
}

/**
 * 处理单个产品
 * @param  Array $product 需要处理的产品
 * @return Array $product 经过处理的产品 
 */
function handle_product($product){
	
	$JSON = json_decode($product['Products_JSON'],TRUE);
	
	if(isset($JSON["ImgPath"])){
			$product['ImgPath'] = $JSON["ImgPath"][0];
		}else{
			$product['ImgPath'] =  'static/api/shop/skin/default/nopic.jpg';
	}
	
	return $product;
}

	
/*获得产品封面图片地址*/
function get_prodocut_cover_img($Product){
	$JSON = json_decode($Product['Products_JSON'],TRUE);
	$product = $Product;
	
    if(isset($JSON["ImgPath"])){
			$product['ImgPath'] = $JSON["ImgPath"][0];
	}else{
			$product['ImgPath'] =  'static/api/shop/skin/default/nopic.jpg';
	}
		
	return $product['ImgPath'] ;
}

//手机端弹窗
if (!function_exists('layerOpen')) {
    function layerOpen($msg, $time = 2, $url = ''){
        $str = '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">';
        $str .= '<meta name="app-mobile-web-app-capable" content="yes">';
        $str .= '<script type="text/javascript" src="/static/js/jquery-1.11.1.min.js"></script>';
        $str .= '<script type="text/javascript" src="/static/js/layer/mobile/layer.js"></script>';
        $str .= '<script>';
        if($url == ''){
            $url = "javascript:history.go(-1);";
        }
        $str .= '$(function(){ layer.open({style: "border:none; background-color:rgba(1,1,1,0.4); color:#fff;",content:"' .
            $msg . '",skin: "msg", time: '.$time.',end:function(){ location.href = "'.$url
            .'"; } }); });';
        $str .= '</script>';
        echo $str;
        exit;
    }
}

/**
 * 判断是否为ajax请求
 * @return boolean
 */
if (!function_exists('isAjax')) {
    function isAjax() {
        if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){
            return true;
        } else {
            return false;
        }
    }
}

/**
 *获取商品的平均评分
 */
function get_comment_aggregate($DB, $UsersID, $ProductsID='',$Biz_ID=''){

    if(!empty($ProductsID)){
    $rsCommit = $DB->GetRs('user_order_commit','AVG(Score) AS points, COUNT(Item_ID) AS num', 'where Users_ID = "' . $UsersID . '" and status = 1 and Product_ID = ' . $ProductsID);
    }
    if(!empty($Biz_ID)){
        $rsCommit = $DB->GetRs('user_order_commit','AVG(Score) AS points, COUNT(Item_ID) AS num', 'where Users_ID = "' . $UsersID . '" and status = 1 and Biz_ID = ' . $Biz_ID);
    }
    if(!empty($rsCommit)){
        $aggerate = [
            'points' => (int)$rsCommit['points'],
            'num' => $rsCommit['num']
        ];
    } else {
        $aggerate = [
            'points' => 0,
            'num' => 0
        ];
    }
    return $aggerate;
}
	
/**
 * 获取可用的支付方式
 * @return [type] [description]
 */
function get_enabled_pays($DB,$UsersID){
	$rsPayConfig = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");

	$pays = array();
	if($rsPayConfig['PaymentWxpayEnabled'] == 1){
		$pays['Wxpay'] = '微信支付';
	}
	if($rsPayConfig['Payment_AlipayEnabled'] == 1){

		$pays['Alipay'] = '支付宝支付';
	}
	if($rsPayConfig['PaymentYeepayEnabled'] == 1){
	
		$pays['Yeepay'] = '易宝支付';
	}
	
	if($rsPayConfig['Payment_OfflineEnabled'] == 1){
		$pays['Offline'] = '线下支付(货到付款)';
	}
	
	
	return $pays;
}	


/**
 *付款后更改商品库存，若库存为零则下架
 */
function handle_products_count($UsersID,$rsOrder){
	
	global $DB1;
	$CartList = json_decode(htmlspecialchars_decode($rsOrder['Order_CartList']),true);
	
	if(empty($CartList)){
		return false;
	}
	
	//取出购物车所包含产品信息	
	foreach($CartList as $ProductID=>$product_list){
		$qty = 0;
                //edit20160324
		/*foreach($product_list as $key=>$item){
			$qty += $item['Qty'];
		}*/
                foreach($product_list as $key=>$item){
						$qty += $item['Qty'];
						if(!empty($item['spec_list'])){
							$Product_Attr_ID = $item['spec_list'];
							//edit20160324 订单成功属性库存减少
							$condition = "where Product_Attr_ID in(".$Product_Attr_ID.")";
							$rsProductAttr  = $DB1->GetRs('shop_products_attr','Property_count',$condition);
							$proattr_data['Property_count'] = $rsProductAttr['Property_count']-$item['Qty'];
							$DB1->set('shop_products_attr',$proattr_data,$condition);
						}
				}
		$condition = "where Users_ID='".$UsersID."' and Products_ID=".$ProductID;
		$rsProduct  = $DB1->GetRs('shop_products','Products_Count',$condition);
		
		$Products_Count = $rsProduct['Products_Count']-$qty;
		$product_data['Products_Count'] = $Products_Count;

		if($Products_Count == 0){
			$product_data['Products_SoldOut'] = 1;
		}
		
		$DB1->set('shop_products',$product_data,$condition);
	}
}

/**
 *用getDictionary获得的Collection数组生成dropdown数组
 */
function collection_dropdown($dictionary,$field){
	
	$dropdown = array();
	foreach($dictionary as $id=>$item){
		$dropdown[$id] = $item->$field;
	}
	
	return $dropdown;
}
	define('EARTH_RADIUS', 6378.137);//地球半径，假设地球是规则的球体
    define('PI', 3.1415926);
	function map($Location_X, $Location_Y,$distance = 2){
		$lat = floatval($Location_X);
		$lng = floatval($Location_Y);
		$dlng =  2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
		$dlng = rad2deg($dlng);
		$dlat = $distance/EARTH_RADIUS;
		$dlat = rad2deg($dlat);	
		$return = array();	
		$return['lat_min'] = $lat-$dlat;
		$return['lat_max'] = $lat+$dlat;
		$return['lng_min'] = $lng-$dlng;
		$return['lng_max'] = $lng+$dlng;
		return $return;
	}
	
	function GetDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2){
		$radLat1 = $lat1 * PI() / 180.0;   //PI()圆周率
		$radLat2 = $lat2 * PI() / 180.0;
		$a = $radLat1 - $radLat2;
		$b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
		$s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
		$s = $s * EARTH_RADIUS;
		$s = round($s * 1000);
		if ($len_type --> 1){
			$s /= 1000;
		}
		return round($s, $decimal);
	}
	function fxy_array_sort($arr, $keys, $type = 'asc') {
		$keysvalue = $new_array = array();
		foreach ($arr as $k => $v) {
			$keysvalue[$k] = $v[$keys];
		}
		if ($type == 'asc') {
			//对数组进行排序并保持索引关系
			asort($keysvalue);
		} else {
			//对数组进行逆向排序并保持索引关系
			arsort($keysvalue);
		}
		reset($keysvalue);
		foreach ($keysvalue as $k => $v) {
			$new_array[] = $arr[$k];
		}
		return $new_array;
	}
	 /**
     * 异步将远程链接上的内容(图片或内容)写到本地
     * 
     * @param unknown $url
     *            远程地址
     * @param unknown $saveName
     *            保存在服务器上的文件名
     * @param unknown $path
     *            保存路径
     * @return boolean
     */
    function put_file_from_url_content($url, $saveName, $path) {
        // 设置运行时间为无限制
        set_time_limit ( 0 );
        
        $url = trim ( $url );
        $curl = curl_init ();
        // 设置你需要抓取的URL
        curl_setopt ( $curl, CURLOPT_URL, $url );
        // 设置header
        curl_setopt ( $curl, CURLOPT_HEADER, 0 );
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
        // 运行cURL，请求网页
        $file = curl_exec ( $curl );
        // 关闭URL请求
        curl_close ( $curl );
        // 将文件写入获得的数据
        $filename = $path . $saveName;
        $write = @fopen ( $filename, "w" );
        if ($write == false) {
            return false;
        }
        if (fwrite ( $write, $file ) == false) {
            return false;
        }
        if (fclose ( $write ) == false) {
            return false;
        }
    }

	if(!function_exists('check_short_msg')){
	/**
	* 检测手机验证码是否正确
	* @param   int $sms_code 手机短信验证码 
	* @param   string $user_mobile 用户手机号码
	* @return  string $result   验证结果是否正确
	*/
	function check_short_msg($user_mobile,$sms_code,$UsersID=''){
	
		$result = true;
		if(!preg_match("/[0-9]{6}/", $sms_code) || $_SESSION[$UsersID.'mobile_code'] != md5($user_mobile.'|'.$sms_code)){
		$result = false;		
		}
	
		return $result;
	}
}

if(!function_exists('randcode')){
	/**
	 * 生成指定长度纯数字随机码
	 * @param  integer $length 所需生成随机码长度
	 * @return string  $temchars  所生成的随机码
	 */
	function randcode($length=6){
		$chars = '0123456789';
		$temchars = '';
		for($i=0;$i<$length;$i++)
		{
			$temchars .= $chars[ mt_rand(0, strlen($chars) - 1) ];
		}
	
		return $temchars;
	}
}

 
if(!function_exists('star_mobile')){
    
   /**
    * 获取掩码手机号 13781673071 变为 137******71
    * @param  string $mobile 原手机号
    * @return string $starMobile   掩码手机号
    *    
    **/
   function star_mobile($mobile = ''){
	   $head = substr($mobile, 0,3);
	   $tail = substr($mobile,-2,2);
	   $starMobile  = $head.'******'.$tail;	   
	   return $starMobile;
   }	
   
	
}

if(!function_exists('getFloatValue')){
	function getFloatValue($f,$len)
	{
	  $tmpInt=intval($f);

	  $tmpDecimal=$f-$tmpInt;
	  $str="$tmpDecimal";
	  $subStr=strstr($str,'.');
	  if($tmpDecimal != 0){	
		 if(strlen($subStr)<$len+1)
		{
			$repeatCount=$len+1-strlen($subStr);
			$str=$str."".str_repeat("0",$repeatCount);

		}
		
		$result = $tmpInt."".substr($str,1,1+$len);
	  }else{
		$result =  	$tmpInt.".".str_repeat("0",$len);
	  }

	  return   $result;

	}
}

if (!function_exists('getmonth')) {//获取前后月的第一天
	function getmonth($type=0){
		$month = date('n');
		$year = date('Y');
		if($type==0){//上个月一号
			if($month==1){
				$month = 12;
				$year = $year - 1;
			}else{
				$month = $month - 1;
			}
		}else{//下个月一号
			if($month==12){
				$month = 1;
				$year = $year + 1;
			}else{
				$month = $month + 1;
			}
		}
		return strtotime($year.'-'.$month.'-01');
	}
}
if ( ! function_exists('sort_product_list'))
{	
	/*处理产品列表*/
	function sort_product_list($list){
	
		$result = array();
        $i=1;
		foreach($list as $key=>$item){
			$JSON = json_decode($item['Products_JSON'],TRUE);
			$product = $item;
			if(isset($JSON["ImgPath"])){
				$product['ImgPath'] = $JSON["ImgPath"][0];
			}else{
				$product['ImgPath'] =  'static/api/shop/skin/default/nopic.jpg';
			}
		
			$result[$product['Products_Index']+$i] = $product;
			$i++;
		}
		return $result;
	}
}

if(!function_exists('get_dis_level')){
	function get_dis_level($DB,$UsersID){
		$file_path = $_SERVER["DOCUMENT_ROOT"].'/data/cache/'.$UsersID.'dis_level.php';
		if(is_file($file_path)){
			include($file_path);
			return $dis_level;
		}else{
			$dis_level = array();
			$DB->Get('distribute_level','*','where Users_ID="'.$UsersID.'" order by Level_ID asc');
			while($r = $DB->fetch_assoc()){
				$dis_level[$r['Level_ID']] = $r;
			}
			return $dis_level;
		}
	}
}

if(!function_exists('dis_level_update')){//更新分销商级别
	function update_dis_level($DB,$UsersID){
		$dir_name = $_SERVER["DOCUMENT_ROOT"].'/data/cache';
		//目录判断
		if(!is_dir($dir_name)){
			$temp = explode('/', $dir_name);
			$max = count($temp);
			$cur_dir = '';
			for($i = 0; $i < $max; $i++) {
				$cur_dir .= $temp[$i] . '/';
				if(is_dir($cur_dir)) continue;
				@mkdir($cur_dir);
			}
		}
		
		//文件判断			
		$lists = array();
		$DB->Get('distribute_level','*','where Users_ID="'.$UsersID.'" order by Level_ID asc');
		while($r = $DB->fetch_assoc()){
			$lists[$r['Level_ID']] = $r;
		}
		$arrayname = '$dis_level';
		$data = var_export($lists,true);
		$data = "<?php\n".$arrayname." = ".$data.";\n?>";
		
		$filename = $dir_name.'/'.$UsersID.'dis_level.php';
		if(@$fp = fopen($filename, 'wb')) {
			flock($fp, LOCK_EX);
			$len = fwrite($fp, $data);
			flock($fp, LOCK_UN);
			fclose($fp);
			return $len;
		} else {
			return false;
		}
	}
}

if(!function_exists('handle_getuserinfo')){//更新分销商级别
	function handle_getuserinfo($UsersID,$UserID){	
		global $DB1;
		$condition = "where Users_ID='".$UsersID."' and User_ID=".$UserID;
		$rsUser  = $DB1->GetRs('user','User_Mobile',$condition);		
		return $rsUser['User_Mobile'];
	}
}

/**
 * 补丁函数，解决 curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true) 在apache 的 open_basedir 指令下无法正常工作的问题
 * @param  [type] $ch [description]
 * @return [type]     [description]
 */
function curl_redir_exec($ch) {
	static $curl_loops = 0;
	static $curl_max_loops = 20;
	if ($curl_loops++ >= $curl_max_loops)
	{
	    $curl_loops = 0;
	    return FALSE;
	}
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	$ret = explode("\n\n", $data, 2);
	$header = $ret[0];
	if (isset($ret[1])) $data = $ret[1];

	//        list($header, $data) = explode("\n\n", $data, 2);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($http_code == 301 || $http_code == 302)
	{
	    $matches = array();
	    preg_match('/Location:(.*?)\n/', $header, $matches);
	    $url = @parse_url(trim(array_pop($matches)));
	    if (!$url)
	    {
	        //couldn't process the url to redirect to
	        $curl_loops = 0;
	        return $data;
	    }
	    $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
	    if (!$url['scheme'])
	        $url['scheme'] = $last_url['scheme'];
	    if (!$url['host'])
	        $url['host'] = $last_url['host'];
	    if (!$url['path'])
	        $url['path'] = $last_url['path'];
	    $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
	    curl_setopt($ch, CURLOPT_URL, $new_url);
	    debug('Redirecting to', $new_url);
	    return curl_redir_exec($ch);
	} else {
	    $curl_loops=0;
	    return $data;
	}
}

if(!function_exists('sha_sifenh')){//获取当前股东分红状态
	function sha_sifenh($UsersID, $UserID, $Sha_Money){	
		global $DB1;
		$condition = "where Users_ID='".$UsersID."' and User_ID=".$UserID;
		$curdisid = $DB1->GetRs("distribute_account", "Account_ID", $condition);
		$conditrec = "where Sha_Accountid LIKE'%".$curdisid['Account_ID']."%'";
		$DB1->query("select Users_ID,Record_Money,Sha_Qty,Sha_Accountid from distribute_sha_rec ".$conditrec);
		  $sha_rec = array();		  
		  $shafenh = 0;
		  while($rb=$DB1->fetch_assoc()){
			$sha_rec[] = $rb;
		  }
		
		$acid = ','.$curdisid['Account_ID'].',';
		foreach($sha_rec as $key=>$rec){
			if(strpos($rec['Sha_Accountid'], ','.$acid.',') !== false){
			$shafenh += $rec['Record_Money']/$rec['Sha_Qty'];
			}	
			 }
			 
		if ($shafenh >= $Sha_Money) {
			$issha = $curdisid['Account_ID'];
		} else {
			$issha = 0;
		} 
		return $issha;
	}
}

if(!function_exists('sha_nofenhnum')){//获取当前参与分红的股东
	function sha_nofenhnum($UsersID, $Sha_Money){	
		global $DB1;
		$condition = "where Users_ID='".$UsersID."'";
		$condition .= " and Enable_Agent = 1 order by User_ID desc";
		  $DB1->get("distribute_account","User_ID,Account_ID,Real_Name,sha_level,Enable_Agent",$condition);		  
		  /*获取订单列表牵扯到的分销商*/
		  $sha_list = array();		 
		  $accid = array();		 
		  while($rr=$DB1->fetch_assoc()){
			$sha_list[] = $rr;			
			$accid[] = $rr['Account_ID'];
		  }
		  $conditrec = "where Users_ID = '".$UsersID."'";
		  $sha_fenh_ok = array();
		  $sha_fenh = array();
		  $sha_fenh_souce = array();
		  if(count($sha_list)>0){
		  $DB1->query("select Users_ID,Record_Money,Sha_Qty,Sha_Accountid from distribute_sha_rec ".$conditrec);
		  $sha_rec = array();		  
		  $shafenh = 0;
		  while($rb=$DB1->fetch_assoc()){
			$sha_rec[] = $rb;			
		  }
			foreach($sha_list as $key=>$acid){			  
			 foreach($sha_rec as $key=>$rec){
			if(strpos($rec['Sha_Accountid'], ','.$acid['Account_ID'].',') !== false){
			$shafenh += $rec['Record_Money']/$rec['Sha_Qty'];
			}	
			 }
			 $sha_fenh_souce[] = $acid;
			 if ($shafenh < $Sha_Money || empty(ceil($Sha_Money))) {
			 $sha_fenh[] = $acid;
			 }			 
			 $shafenh = 0;
		}		
		}
		$sha_fenh_ok['souce'] = $sha_fenh_souce;
		$sha_fenh_ok['cur'] = $sha_fenh;
		return $sha_fenh_ok;
	}
}