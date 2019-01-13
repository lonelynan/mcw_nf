<?php
use Illuminate\Database\Capsule\Manager as DB;

/**
 * 请求
 * @param string $url
 * @param string $method
 * @param array $params
 * @param return json
 */
if (!function_exists('http_request')) {
    function http_request($url, $method = 'post', $params = array()){
        $options = array(
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER =>FALSE,
            CURLOPT_SSL_VERIFYHOST =>FALSE
        );
        $param_string = http_build_query($params);
        if($method == 'post'){
            $options += array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $param_string);
        }else{
            if($param_string)
                $options[CURLOPT_URL] .= '?'.$param_string;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        if( ! $result = curl_exec($ch))
        {
            $result = curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
}

/* Remove JS/CSS/IFRAME/FRAME 过滤JS/CSS/IFRAME/FRAME/XSS等恶意攻击代码(可安全使用)
 * Return string
 */
if (!function_exists('cleanJsCss')) {
    function cleanJsCss($html)
    {
        $html = trim($html);
        $html = preg_replace('/\0+/', '', $html);
        $html = preg_replace('/(\\\\0)+/', '', $html);
        $html = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u', "\\1;", $html);
        $html = preg_replace('#(&\#x*)([0-9A-F]+);*#iu', "\\1\\2;", $html);
        $html = preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $html);
        $html = preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $html);
        $html = str_replace(array('<?', '?>'), array('<?', '?>'), $html);
        $html = preg_replace('#\t+#', ' ', $html);
        $scripts = array('javascript', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
        foreach ($scripts as $script) {
            $temp_str = "";
            for ($i = 0; $i < strlen($script); $i++) {
                $temp_str .= substr($script, $i, 1) . "\s*";
            }
            $temp_str = substr($temp_str, 0, -3);
            $html = preg_replace('#' . $temp_str . '#s', $script, $html);
            $html = preg_replace('#' . ucfirst($temp_str) . '#s', ucfirst($script), $html);
        }
        $html = preg_replace("#<a.+?href=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>.*?</a>#si", "", $html);
        $html = preg_replace("#<img.+?src=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>#si", "", $html);
        $html = preg_replace("#<(script|xss).*?\>#si", "<\\1>", $html);
        $html = preg_replace('#(<[^>]*?)(onblur|onchange|onclick|onfocus|onload|onmouseover|onmouseup|onmousedown|onselect|onsubmit|onunload|onkeypress|onkeydown|onkeyup|onresize)[^>]*>#is', "\\1>", $html);
        $html = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "<\\1\\2\\3>", $html);
        $html = preg_replace('#(alert|cmd|passthru|$code=|exec|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2(\\3)", $html);
        $bad = array(
            'document.cookie' => '',
            'document.write' => '',
            'window.location' => '',
            "javascript\s*:" => '',
            "Redirect\s+302" => '',
            '<!--' => '<!--',
            '-->' => '-->'
        );
        foreach ($bad as $key => $val) {
            $html = preg_replace("#" . $key . "#i", $val, $html);
        }
        return cleanFilter($html);
    }
}

//过滤HTML标签
if (!function_exists('cleanFilter')) {
    function cleanFilter($html)
    {
        $html = trim($html);
        $html = strip_tags($html, "");
        /*$html=preg_replace("/<p[^>]*?>/is","",$html);
        $html=preg_replace("/<div[^>]*?>/is","",$html);
        $html=preg_replace("/<ul[^>]*?>/is","",$html);
        $html=preg_replace("/<li[^>]*?>/is","",$html);
        $html=preg_replace("/<span[^>]*?/is","",$html);
        $html=preg_replace("/<video[^>]*?/is","",$html);
        $html=preg_replace("/<script[^>]*?/is","",$html);
        $html=preg_replace("/<style[^>]*?/is","",$html);
        $html=preg_replace("/<select[^>]*?/is","",$html);
        $html=preg_replace("/<iframe[^>]*?/is","",$html);
        $html=preg_replace("/<audio[^>]*?/is","",$html);
        $html=preg_replace("/<object[^>]*?/is","",$html);
        $html=preg_replace("/<a[^>]*?>(.*)?<\/a>/is","",$html);
        $html=preg_replace("/<table[^>]*?>/is","",$html);
        $html=preg_replace("/<tr[^>]*?>/is","",$html);
        $html=preg_replace("/<td[^>]*?>/is","",$html);
        $html=preg_replace("/<ol[^>]*?>/is","",$html);
        $html=preg_replace("/<form[^>]*?>/is","",$html);
        $html=preg_replace("/<input[^>]*?>/is","",$html);*/
        return $html;
    }
}

/**
 * 根据中文裁减字符串
 * @param $string - 字符串
 * @param $length - 长度
 * @param $doc - 缩略后缀
 * @return 返回带省略号被裁减好的字符串
 */
function cutstr($string, $length, $dot = ' ...') {

    if(strlen($string) <= $length) {
        return $string;
    }

    $pre = chr(1);
    $end = chr(1);
    //保护特殊字符串
    $string = str_replace(array('&', '"', '<', '>'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

    $strcut = '';
    if(strtolower(CHARSET) == 'utf-8') {

        $n = $tn = $noc = 0;
        while($n < strlen($string)) {

            $t = ord($string[$n]);
            if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1; $n++; $noc++;
            } elseif(194 <= $t && $t <= 223) {
                $tn = 2; $n += 2; $noc += 2;
            } elseif(224 <= $t && $t <= 239) {
                $tn = 3; $n += 3; $noc += 2;
            } elseif(240 <= $t && $t <= 247) {
                $tn = 4; $n += 4; $noc += 2;
            } elseif(248 <= $t && $t <= 251) {
                $tn = 5; $n += 5; $noc += 2;
            } elseif($t == 252 || $t == 253) {
                $tn = 6; $n += 6; $noc += 2;
            } else {
                $n++;
            }

            if($noc >= $length) {
                break;
            }

        }
        if($noc > $length) {
            $n -= $tn;
        }

        $strcut = substr($string, 0, $n);

    } else {
        for($i = 0; $i < $length; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
        }
    }

    //还原特殊字符串
    $strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&', '"', '<', '>'), $strcut);

    //修复出现特殊字符串截段的问题
    $pos = strrpos($strcut, chr(1));
    if($pos !== false) {
        $strcut = substr($strcut,0,$pos);
    }
    return $strcut.$dot;
}

//经纬度转换     高德（腾讯）转 百度
if (!function_exists('gd_trans_bd')) {
    function gd_trans_bd($gd_lat, $gd_lon){
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

//逆地理解析，根据经纬度获取地理信息
if (!function_exists('getBdPositionAddress')) {
    function getBdPositionAddress($coordinate, $ak_baidu)
    {
        $url = "http://api.map.baidu.com/geocoder/v2/?location=" . $coordinate['lat'] . ',' . $coordinate['lng'] . "&output=json&pois=1&ak=" . $ak_baidu;

        return http_request($url, 'get');
    }
}

//小程序签名验证
if (!function_exists('little_program_key_check')) {
    function little_program_key_check($postsData)
    {
        if (!isset($postsData['timestamp']) || !isset($postsData['sign'])) {
            return ['errorCode' => 101, 'msg' => '缺少参数'];
            //exit(json_encode(['errorCode' => 101, 'msg' => '缺少参数'], JSON_UNESCAPED_UNICODE));
        } else {
            $key = '458f_$#@$*!fdjisdJDFHUk4%%653154%^@#(FSD#$@0-T';
            $postString = '';
            $postArr = [];
            $sign = $postsData['sign'];
            $timestamp = $postsData['timestamp'];
            if (!empty($postsData['sortToken'])) {
                $postsData = sortArr($postsData, $postArr);
            }
            $postString = createTokenString($postsData, $postString);
            $postString = substr($postString, 0, -1);
            $filterEmoji = filterEmoji($postString);
            $mysign = strtoupper(md5(base64_encode($postString . $key . $timestamp)));
            if ($mysign != $sign && $filterEmoji == $postString) {
                return ['errorCode' => 403, 'msg' => '签名认证错误!', 'data' => $postString];
                //exit(json_encode(['errorCode' => 403, 'msg' => '权限不足!', 'data' => $postString]));
            }
        }
        return ['errorCode' => 0];
    }
}

if (!function_exists('sortArr')) {
    function sortArr($arr, &$postArr, $add_key = '')
    {
        if (!is_array($arr)) {
            return $arr;
        }
        foreach ($arr as $key => $val) {
            $new_key = $add_key . ($add_key == '' ? $key : '[' . $key . ']');
            if (is_array($val)) {
                sortArr($val, $postArr, $new_key);
            } else {
                if ($key == 'sign' || $key == 'timestamp' || $key == 'sortToken' || strlen($val) < 1) {
                    continue;
                } else {
                    $postArr = array_merge($postArr, [$new_key => $val]);
                }
            }
        }
        ksort($postArr);
        return $postArr;
    }
}


if (!function_exists('createTokenString')) {
    function createTokenString($arr, &$postString)
    {
        if (!is_array($arr)) {
            return $arr;
        }
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                createTokenString($val, $postString);
            } else {
                if ($key == 'sign' || $key == 'timestamp' || strlen($val) < 1) {
                    continue;
                } else {
                    $postString .= $val . ',';
                }
            }
        }
        //sort($this->tmp);
        return $postString;
    }
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

//PC端弹窗提示
if (!function_exists('alert')){
	function alert($msg, $url = ''){
        $str = '<script>';
        if($url == ''){
            $url = "javascript:history.go(-1);";
        }
        $str .= "alert(\"".$msg."\");";
		$str .= "location.href=\"" . $url . "\"";
        $str .= '</script>';
        echo $str;
        exit;
	}
}

//将区域数字形式的编码转换为汉字形式，如 240转换为 郑州市
if(!function_exists('getArea')){
	function getArea($rsAddress){
		
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
}

if(!function_exists("build_pre_order_no")) {
    function build_pre_order_no(){
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    } 
}
if(!function_exists("create_order")) {
	//生成订单并生成分销记录
	function create_order($UsersID, $User_ID, $rsConfig, $orderCartList, $Biz_ID, $OwnerID = 0, $web_total = 0) {
		global $DB;
		$Data = [
			"Users_ID" => $UsersID,
			"User_ID" => $User_ID,
			"Order_IsVirtual" => 1,
			"Order_IsRecieve" => 0,
			"Order_Type" => "offline_qrcode", 
			"Order_PaymentMethod" =>"微支付",
			"Owner_ID" => $OwnerID,
			"Order_Shipping" => json_encode(['Express'=>'','Price'=>0],JSON_UNESCAPED_UNICODE),
			"Web_Price" => $web_total,
			"Web_Pricejs" => $web_total,
		];

		$is_distribute = is_distribute_action($User_ID, $rsConfig['Dis_Self_Bonus']);
		if($is_distribute){
			Dis_Record::observe(new DisRecordObserver());
		}
		$pre_total = 0;
		$pre_sn = build_pre_order_no();
		$pre_sn = 'PRE'.$pre_sn;
		$isError = 0;
		$orderids = [];
		$OrderID = 0;
		$BizCart = $orderCartList[$Biz_ID];

		$Data["Biz_ID"] = $Biz_ID;
		$Data["Order_Remark"] = '';
		$Data["Order_Shipping"] = json_encode([], JSON_UNESCAPED_UNICODE);
		$Data["Order_CartList"] = json_encode($BizCart, JSON_UNESCAPED_UNICODE);
		$total_price = 0;
		//计算你购物车中产品价格
		foreach($BizCart as $Product_ID => $Product_List){
			foreach($Product_List as $k=>$v){
				$total_price+=$v["Qty"]*$v["ProductsPriceX"];
			}
		}
		$Data["Order_TotalAmount"] = $total_price;
		$Data["Order_NeedInvoice"] = 0;//是否需要发票
		$Data["Order_InvoiceInfo"] = '';
		$Data["Order_TotalPrice"] = $total_price;
		$pre_total += $Data["Order_TotalPrice"];
		$Data["Order_CreateTime"] = time();
		$Data["Order_Status"] = 1;
		//begin_trans();
		$OrderID = DB::table("user_order")->insertGetId($Data);
		if($OrderID){
			$orderids[] = $OrderID;
			foreach($BizCart as $kk => $vv){
				$qty = 0;
				foreach($vv as $k => $v){
					$qty += $v['Qty'];
					//加入分销记录
					if($is_distribute){
						add_offline_distribute_record($UsersID, $v['OwnerID'], $v['ProductsPriceX'], $Biz_ID, $v['Qty'], $OrderID, $k);
					}
				}
				$Product = get_offline_product_distribute_info($UsersID, $Biz_ID, $total_price, 1);
				$comm = new Salesman_commission();
				$salesman = $comm->handout_salesman_commission($OrderID,$Product,$qty);
				if($salesman === false){
					//back_trans();
					break;
				}
			}
			require_once(CMS_ROOT.'/include/library/weixin_message.class.php');
		$weixin_message = new weixin_message($DB,$UsersID,$User_ID);
		$contentStr = '您已成功提交微商城订单，<a href="http://'.$_SERVER["HTTP_HOST"].'/api/'.$UsersID.'/shop/member/detail/'.$OrderID.'/">查看详情</a>';
		$weixin_message->sendscorenotice($contentStr);
			$Data = array(
				"usersid"=>$UsersID,
				"userid"=>$User_ID,
				"pre_sn"=>$pre_sn,
				"orderids"=>implode(",",$orderids),
				"total"=>$pre_total,
				"createtime"=>time(),
				"status"=>1
			);
			$preID = DB::table("user_pre_order")->insertGetId($Data);
			if(!$preID){
				//back_trans();
				return 0;
			}
			//commit_trans();
		}else{
			//back_trans();
			return 0;
		}
		return $OrderID;
	}
}

//清除会员
function clearUser($UsersID, array $UserID = [])
{
    $flag = true;
    if(!empty($UserID)){
        $result = DB::table('user')->where(['Users_ID' => $UsersID])->whereIn('User_ID', $UserID)->get(['User_ID']);
    }else{
        $result = DB::table('user')->where(['Users_ID' => $UsersID])->get(['User_ID']);
		DB::select('delete FROM shop_sales_payment');
    }
	
    if(!empty($result)){
        $uid = array_map(function($val){
            return $val['User_ID'];
        }, $result);
        $result = DB::table('distribute_account')->where(['Users_ID' => $UsersID])->whereIn('User_ID', $uid)->get(['Account_ID']);
        $Account_list = array_map(function($val){
            return $val['Account_ID'];
        }, $result);
        $result = DB::table('user_order')->where(['Users_ID' => $UsersID])->whereIn('User_ID', $uid)->get(['Order_ID']);
        $orderlist = array_map(function($val){
            return $val['Order_ID'];
        }, $result);		
		
        $tables = ['action_num_record','active','active_type','ad_advertising','ad_list','agent','agent_back_tie','agent_level','agent_order','alipay_refund','anli','announce','announce_category','area_region','battle','battle_act','battle_exam','battle_sn','bianmin_server','biz','biz_active','biz_apply','biz_apply_bak','biz_article','biz_article_cate','biz_bond_back','biz_category','biz_config','biz_group','biz_home','biz_menu','biz_pay','biz_union_category','biz_union_home','cloud_category','cloud_products','cloud_products_detail','cloud_record','cloud_shopcodes','cloud_slide','comein','distribute_account','distribute_account_message','distribute_account_record','distribute_agent_areas','distribute_agent_rec','distribute_config','distribute_fanben_record','distribute_fuxiao','distribute_level','distribute_order','distribute_order_record','distribute_record','distribute_sales_record','distribute_sha_rec','distribute_withdraw_method','distribute_withdraw_methods','distribute_withdraw_record','fruit','fruit_config','fruit_sn','games','games_config','games_result','guide','http_raw_post_data','kanjia','kanjia_helper_record','kanjia_member','kf_account','kf_config','kf_language','kf_message','message_template','newtable','node','pc_focus','pc_index','pc_setting','pc_user_message','permission_config','pifa_category','pifa_config','pifa_products','pintuan_attribute','pintuan_aword','pintuan_category','pintuan_collet','pintuan_commit','pintuan_config','pintuan_order','pintuan_order_detail','pintuan_products','pintuan_team','pintuan_teamdetail','pintuan_virtual_card','pintuan_virtual_card_type','scratch','scratch_config','scratch_sn','sha_account_record','sha_order','share_click','share_record','shipping_orders','shipping_orders_commit','shipping_template_section','shop_articles','shop_articles_category','shop_attr','shop_attribute','shop_bank_card','shop_category','shop_config','shop_dis_agent_areas','shop_dis_agent_rec','shop_distribute_account','shop_distribute_account_record','shop_distribute_config','shop_distribute_fuxiao','shop_distribute_msg','shop_distribute_record','shop_home','shop_product_type','shop_products','shop_products_attr','shop_property','shop_sales_payment','shop_sales_record','shop_shipping_company','shop_shipping_template','shop_user_withdraw_methods','shop_virtual_card','shop_virtual_card_type','shop_withdraw_method','slide','sms','statistics','stores','stores_config','third_login_config','third_login_users','turntable','turntable_config','turntable_sn','update_category','uploadfiles','user','user_address','user_back_order','user_back_order_detail','user_card_benefits','user_charge','user_config','user_coupon','user_coupon_logs','user_coupon_record','user_favourite_products','user_get_orders','user_get_product','user_gift','user_gift_orders','user_integral_record','user_message','user_message_record','user_money_record','user_operator','user_order','user_order_commit','user_peas','user_peas_orders','user_pre_order','user_profile','user_recieve_address','user_reserve','user_yielist','users','users_access_token','users_employee','users_group','users_menu','users_money_record','users_payconfig','users_permit','users_reserve','users_roles','users_schedule','votes','votes_item','votes_order','web_article','web_code','web_column','web_config','web_home','wechat_attention_reply','wechat_keyword_reply','wechat_material','wechat_menu','wechat_url','weixin_log','zhongchou_config','zhongchou_prize','zhongchou_project','zhuli','zhuli_act','zhuli_record'];
        $item[] = 'User_ID';
        if (!empty($Account_list)) {
            $item[] = 'Account_ID';
        }
        if (!empty($orderlist)) {
            $item[] = 'Order_ID';
        }
        $sql = "";
        foreach ($item as $it) {
            if ($it == 'Account_ID') {
                $kid = $Account_list;
                $itemarray = array('distribute_agent_areas', 'distribute_agent_rec', 'distribute_sha_rec');
            } elseif ($it == 'Order_ID') {
                $kid = $orderlist;
                $itemarray = array('distribute_record', 'shop_sales_record');
            } else {
                $kid = $uid;
                $itemarray = 1;
            }
            foreach ($tables as $key) {
                if ($itemarray == 1 || in_array($key, $itemarray)) {
                    $json = DB::select('Describe ' . $key . ' ' . $it . '');
                    if(isset($json[0])){
                        $json = $json[0];
                        if (count($json) > 0) {
                            if ($json['Field'] == $it) {
                                DB::table($key)->whereIn($it, $kid)->delete();
                            }
                        }
                    }
                }
            }
        }
        $sql = trim($sql, ';');
        foreach ($uid as $v){
            $file = CMS_ROOT . '/data/avatar/' . $UsersID . $v . '.jpg';
            if(is_file($file) && file_exists($file)){
                $flag &= unlink($file);
            }
            $file = CMS_ROOT . '/data/temp/user_' . $v . '.jpg';
            if(is_file($file) && file_exists($file)){
                $flag &= unlink($file);
            }
            $file = CMS_ROOT . '/data/poster/user_'  . $UsersID . $v .  '.png';
            if(is_file($file) && file_exists($file)){
                $flag &= unlink($file);
            }
            $file = CMS_ROOT . '/data/poster/user_'  . $UsersID . $v .  'pop.png';
            if(is_file($file) && file_exists($file)){
                $flag &= unlink($file);
            }
        }
    }
    return $flag;
}

//批量取消订单
function cancelOrder($OrderID)
{
    $OrderList = [];
    $flag_handle = true;
    if(is_array($OrderID)){
        $OrderList = array_merge($OrderList, $OrderID);
    }else{
        array_push($OrderList, $OrderID);
    }
    $orderList = DB::table("user_order")->whereIn("Order_ID", $OrderList)->get(["Integral_Consumption", "Users_ID","User_ID","Order_CartList","Order_Status","Order_Store"]);
    try{
        begin_trans();
        $flag = $flag1 = 1;
        if(!empty($orderList)){
            foreach ($orderList as $k => $v){                
                $var = handle_products_count($v['Users_ID'], $v, "+");
            }
        }
        $OrderList = implode(',', $OrderList);
        $sql = "delete o,pt,disr,r from user_order as o left join pintuan_teamdetail as pt on o.Order_ID = pt.order_id left join 
distribute_record as disr
 on o
.Order_ID = 
disr
.Order_ID left join distribute_account_record as r on disr.Record_ID = r.Ds_Record_ID where o
.Order_ID in ({$OrderList})";
        $flag_handle = DB::statement($sql);
        commit_trans();
    }catch (Exception $e){
        $flag_handle = false;
        back_trans();
    }
    return $flag_handle;
}

//获取get,post请求的值，为空则返回所有
if(!function_exists("I")) {
    function I($key = '',$default = 0)
    {
        $param = $_GET + $_POST;
        if(!$key){
            return $param;
        }
        if(isset($param[$key]) && $param[$key]){
            return $param[$key];
        }
        return $default;
    }
}

//获取或者设置session请求的值，为空则返回所有
if(!function_exists("S")) {
    function S($key = '',$val = null)
    {
        $session = $_SESSION;
        if(is_null($val)){
            if(!$key){
                return $session;
            }else if(isset($session[$key])){
                return $session[$key];
            }else{
                return 0;
            }
        }else{
            if($key){
                $_SESSION[$key] = $val;
                if($val == ''){
                    unset($_SESSION[$key]);
                }
            }
        }
    }
}

//通过分销商ID获取用户昵称
function getUserNickName($ownerid)
{
    $dis = Dis_Account::where("Account_ID", $ownerid)->first(['User_ID']);
    if($dis){
        $rsUser = User::where("User_ID", $dis->User_ID)->first(["User_NickName"]);
        if($rsUser){
            return $rsUser->User_NickName;
        }
    }
    return '';
}

function layerMsg($msg,$url = ''){
  echo "<!DOCTYPE html>
        <html>
        <head>
        <meta name=\"viewport\" content=\"width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;\" />
        <meta content=\"telephone=no\" name=\"format-detection\" />
        <meta name=\"apple-mobile-web-app-capable\" content=\"yes\">
        <meta name=\"apple-mobile-web-app-status-bar-style\" content=\"black\">
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
        <title>个人中心</title>
        <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
        <script type='text/javascript' src='/static/js/plugin/layer_mobile/layer.js'></script>
        
        </head>
        <body>
        <script>
            $(function(){
                layer.open({
                    content: '{$msg}',
                    btn:'确定',
                    yes: function(index){
        ";
              if($url){
                  echo "window.location.href = '{$url}';";
              }
              echo " layer.close(index);
                    }
                 });
            });
        </script>
        </body>
        </html>
        ";

}
/*
 * 调取缩略图方法
 * @param String $srcUrl 原图片路径, integer $scenarias 调取文件夹 0为宽200图片,1为宽190图片,2为高350图片,3为宽100图片
 * @return 新路径地址
 */
function getImageUrl($srcUrl,$scenarias = 1)
{
    if (strpos($srcUrl, 'http') !== false) return $srcUrl;

	$srcPath = dirname($srcUrl);
	$filename = basename($srcUrl);
	//return $srcPath . "/n{$scenarias}/" . $filename;
	$new_srcUrl = $srcPath . "/n{$scenarias}/" . $filename;
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . $new_srcUrl)) {
		return $new_srcUrl;
	} else {
		return $srcUrl;
	}
}

function combination(){
	$array = array();
	$arguments = func_get_arg(0);
	foreach($arguments as $argument){
		if(is_array($argument) === true){
			$array[] = $argument;
		}else{
			$array[] = array($argument);
		}
	}
	$size = count($array);
	if($size === 0){
		return array();
	}else if($size === 1){
		return is_array($array[0]) === true ? $array[0] : array();
	}else{
		$result = array();
		$a = $array[0];
		array_shift($array);
		if(is_array($array) === false){
			return $result;
		}
		foreach($a as $val){
			$b = call_user_func_array("combinationa", $array);
			foreach($b as $c){
				if(is_array($c) === true){
					$result[] = array_merge(array($val), $c);
				}else{
					$result[] = array($val, $c);
				}
			}
		}
		return $result;
	}
}

function combinationa(){
	$array = array();
	$arguments = func_get_args();
	foreach($arguments as $argument){
		if(is_array($argument) === true){
			$array[] = $argument;
		}else{
			$array[] = array($argument);
		}
	}
	$size = count($array);
	if($size === 0){
		return array();
	}else if($size === 1){
		return is_array($array[0]) === true ? $array[0] : array();
	}else{
		$result = array();
		$a = $array[0];
		array_shift($array);
		if(is_array($array) === false){
			return $result;
		}
		foreach($a as $val){
			$b = call_user_func_array("combinationa", $array);
			foreach($b as $c){
				if(is_array($c) === true){
					$result[] = array_merge(array($val), $c);
				}else{
					$result[] = array($val, $c);
				}
			}
		}
		return $result;
	}
}

/**
 *去除字符串中的emoji表情
 */
function removeEmoji($text) {

    $clean_text = "";

    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $text);

    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);

    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);

    // Match Miscellaneous Symbols
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '', $clean_text);

    // Match Dingbats
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);

    return $clean_text;
}





function get_property($usersid="",$typeid=0, $ProductsID=0){
	
	global $DB1;
	$html = "";
	$PROPERTY = array();
	if($ProductsID){
		$rsProducts=$DB1->GetRs("shop_products","*","where Users_ID='".$usersid."' and Products_ID=".$ProductsID);
		
		if($rsProducts){
			$JSON=json_decode($rsProducts['Products_JSON'],true);
			if(!empty($JSON["Property"])){
				$PROPERTY = $JSON["Property"];
			}
		}
	}
	
	$DB1->get("shop_property","*","where Users_ID='".$usersid."' and (Type_ID=".$typeid." or Type_ID=0) order by Property_Index asc,Property_ID asc");
	
	
	while($r=$DB1->fetch_assoc()){
		if($r["Property_Type"]==0){//单行文本
			$html .='<div class="rows">
			  <label>'.$r["Property_Name"].'</label>
			  <span class="input"><input type="text" name="JSON[Property]['.$r["Property_Name"].']" value="'.(!empty($PROPERTY) && !empty($PROPERTY[$r["Property_Name"]]) ? $PROPERTY[$r["Property_Name"]] : "").'" class="form_input" size="35" /></span>
			  <div class="clear"></div>
			</div>';
		}elseif($r["Property_Type"]==1){//多行文本
			
			$html .='<div class="rows">
			  <label>'.$r["Property_Name"].'</label>
			  <span class="input"><textarea name="JSON[Property]['.$r["Property_Name"].']" class="briefdesc">'.(!empty($PROPERTY) && !empty($PROPERTY[$r["Property_Name"]]) ? $PROPERTY[$r["Property_Name"]] : "").'</textarea></span>
			  <div class="clear"></div>
			</div>';
			
		}elseif($r["Property_Type"]==2){//下拉框
			$html .='<div class="rows">
			  <label>'.$r["Property_Name"].'</label>
			  <span class="input"><select name="JSON[Property]['.$r["Property_Name"].']" style="width:180px">';
			  $List=json_decode($r["Property_Json"],true);
			  foreach($List as $key=>$value){
				  $html .='<option value="'.$value.'"'.(!empty($PROPERTY) && !empty($PROPERTY[$r["Property_Name"]]) && $value==$PROPERTY[$r["Property_Name"]] ? " selected" : "").'>'.$value.'</option>';
			  }
			  $html .='</select></span>
			  <div class="clear"></div>
			</div>';
		}elseif($r["Property_Type"]==3){//多选框
			
		
			$html .='<div class="rows">
			  <label>'.$r["Property_Name"].'</label>
			  <span class="input">';
			  $List=json_decode($r["Property_Json"],true);
			  
			 foreach($List as $key=>$value){
				  
				  $html .='<input type="checkbox" name="JSON[Property]['.$r["Property_Name"].'][]" value="'.$value.'"'.(!empty($PROPERTY) && !empty($PROPERTY[$r["Property_Name"]]) && in_array($value,$PROPERTY[$r["Property_Name"]]) ? " checked" : "").'>&nbsp;'.$value.'&nbsp;&nbsp;&nbsp;&nbsp;';
			  }
			  $html .='</span>
			  <div class="clear"></div>
			</div>';
		}else{//单选按钮
			$html .='<div class="rows">
			  <label>'.$r["Property_Name"].'</label>
			  <span class="input">';
			  $List=json_decode($r["Property_Json"],true);
			  foreach($List as $key=>$value){
				  $html .='<input type="radio" name="JSON[Property]['.$r["Property_Name"].']" value="'.$value.'"'.(!empty($PROPERTY) && !empty($PROPERTY[$r["Property_Name"]]) && $value==$PROPERTY[$r["Property_Name"]] ? " checked" : "").'/>&nbsp;'.$value.'&nbsp;&nbsp;&nbsp;&nbsp;';
			  }
			  $html .='</span>
			  <div class="clear"></div>
			</div>';
		}
	}
	return $html;
}


/**
 * 截取UTF-8编码下字符串的函数
 *
 * @param   string      $str        被截取的字符串
 * @param   int         $length     截取的长度
 * @param   bool        $append     是否附加省略号
 *
 * @return  string
 */
function sub_str($str, $length = 0, $append = true)
{
	$ec_charset = 'utf-8';
    $str = trim($str);
    $strlength = strlen($str);

    if ($length == 0 || $length >= $strlength)
    {
        return $str;
    }
    elseif ($length < 0)
    {
        $length = $strlength + $length;
        if ($length < 0)
        {
            $length = $strlength;
        }
    }

    if (function_exists('mb_substr'))
    {
        $newstr = mb_substr($str, 0, $length, $ec_charset);
    }
    elseif (function_exists('iconv_substr'))
    {
        $newstr = iconv_substr($str, 0, $length,$ec_charset);
    }
    else
    {
        //$newstr = trim_right(substr($str, 0, $length));
        $newstr = substr($str, 0, $length);
    }

    if ($append && $str != $newstr)
    {
        $newstr .= '...';
    }

    return $newstr;
}



function filter($var)
{
	if($var == '')
	{
		return false;
	}
	return true;
}


/**
 *将积分移入不可用积分
 *@param $UsersID
 *@param $UserID  用户ID
 *@param $Integral 移动积分量 
 */
function  add_userless_integral($UsersID,$User_ID,$Integral){
	 
	 $Data = array(
	 			"User_Integral"=>'User_Integral-'.$Integral,
				"User_UseLessIntegral"=>'User_UseLessIntegral+'.$Integral,
	 		);
			
	  global $DB1;
	  $condition = "where Users_ID='".$UsersID."' and User_ID=".$User_ID;
	  $Flag = $DB1->Set('user',$Data,$condition,'User_Integral,User_UseLessIntegral');
	
	  return $Flag;
}
	
/**
 *将不可用积分返还到可用积分 
 *@param $UsersID
 *@param $UserID  用户ID
 *@param $Integral 移动积分量 
 */	
function  remove_userless_integral($UsersID,$User_ID,$Integral){
	 
	 $Data = array(
	 			"User_Integral"=>'User_Integral+'.$Integral,
				"User_UseLessIntegral"=>'User_UseLessIntegral-'.$Integral,
	 		);
			
	  global $DB1;
	  $condition = "where Users_ID='".$UsersID."' and User_ID=".$User_ID;
	  $Flag = $DB1->Set('user',$Data,$condition,'User_Integral,User_UseLessIntegral');
	
	  return $Flag;
}	

 
/**
 *根据昵称关键词获取用户id串
 */ 
function find_userids_by_nickname($Users_ID,$NickName){
	global $DB1;
	$condition = "where Users_ID ='".$Users_ID."' and User_NickName like '%".$NickName."%'";
	$rsIds = $DB1->get('user','User_ID',$condition);
	$rsIDList = $DB1->toArray($rsIds);
	
	if(!empty($rsIDList)){
		$id_array = array();
		foreach($rsIDList as $key=>$item){
			$id_array[] = $item['User_ID'];	   
		}
		
		$result  =implode(',',$id_array);
		
	}else{
		$result = false;
	}
	
	return $result;
	
}
 
/**
 *根据产品名获取产品id串
 */ 
function find_productids_by_Name($Users_ID,$Name){
	
	global $DB1;
	$condition = "where Users_ID ='".$Users_ID."' and Products_Name like '%".$Name."%'";
	
	$rsIds = $DB1->get('shop_products','Products_ID',$condition);
	$rsIDList = $DB1->toArray($rsIds);
	
	if(!empty($rsIDList)){
		$id_array = array();
		foreach($rsIDList as $key=>$item){
			$id_array[] = $item['Products_ID'];	   
		}
		
		$result  =implode(',',$id_array);
	}else{
		$result = false;
	}
	
	
	return $result;
	

}

/**
 * 创建以逗号分隔值字符串
 * @param  Array  $val_list 值列表 
 * @param  String $ids     以逗号分隔的id字符串
 * @return String $html    以逗号分隔的值字符串
 */
function build_comma_html($val_list,$ids){
	$html = '';
	if(strlen($ids) >0 ){
		
		$id_list = explode(',',$ids);
		$html_list = array();
		foreach($id_list as $k=>$id){
			$html_list [] = $val_list[$id];
		}
		
		$html = implode(',',$html_list);
	}
	
	return $html;
}


/**
 *若值为空，则输出默认值
 *@param $val 需要输出的值
 *@pram  $default 默认值
 *@return $result 需要输出的最终值
 */
 
function empty_default($val,$default){
	$result = !empty($val)?$val:$default;
	return $result;
}


/**
 *获取地区列表
 */
function get_regison_list(){
	
	global $DB1;
	$rsAreas = $DB1->get('area','*','where area_deep = 1');
	$province_all_array = $DB1->toArray($rsAreas);
	foreach ($province_all_array as $a) {
     
            if ($a['area_deep'] == 1 && $a['area_region'])
                $region[$a['area_region']][] = $a['area_id'];
     
	}
	
	return $region;
}

/**
 * 获取所有区域信息
 * @param  int     $deep 区域深度
 * @return Array   $arr 区域数组
 */
function get_all_area($deep){
	
    global $DB1;
	$rsAreas = $DB1->get('area','*','where area_deep <='.$deep);
	
	$area_all_array = $DB1->toArray($rsAreas);
	
	
	foreach ($area_all_array as $a) {
            $data['name'][$a['area_id']] = $a['area_name'];
            $data['parent'][$a['area_id']] = $a['area_parent_id'];
            $data['children'][$a['area_parent_id']][] = $a['area_id'];

            if ($a['area_deep'] == 1 && $a['area_region'])
                $data['region'][$a['area_region']][] = $a['area_id'];
     }
	 
	$arr = array();
	
    foreach ($data['children'] as $k => $v) {
          foreach ($v as $vv) {
               $arr[$k][] = array($vv, $data['name'][$vv]);
          }
		  
	}
	
	return $arr;
			
}


/**
 *默认地址被删除，设置另一个地址为默认
 */
function set_anoter_default($UsersID,$User_ID){
	
	global $DB1;
	$condition = "where Users_ID='".$UsersID."' and User_ID='".$User_ID."'";
    $rsAddress = $DB1->GetRs("user_address","*",$condition);
	if($rsAddress){
		$Address_ID = 	$rsAddress['Address_ID'];	
		$condition = "where Users_ID='".$UsersID."' and User_ID='".$User_ID."' and Address_ID=".$Address_ID;
	
		$DB1->Set("user_address",array('Address_Is_Default'=>1),$condition);
	}
}



/**
 *确定商品所在购物车cartkey
 */
function get_cart_key($UsersID,$virtual,$needcart){
	if($virtual==1){
		$cart_key = $UsersID."Virtual";
	}else{
		if($needcart==1){
			$cart_key = $UsersID."CartList";
		}else{
			$cart_key = $UsersID."BuyList";
		}
	}
	
	return $cart_key;
}

/**
 *cloud确定商品所在购物车cartkey
 */
function get_cart_key_cloud($UsersID,$virtual,$needcart){
	if($virtual==1){
		$cart_key = $UsersID."CloudVirtual";
	}else{
		if($needcart==1){
			$cart_key = $UsersID."CloudCart";
		}else{
			$cart_key = $UsersID."CloudBuy";
		}
	}
	return $cart_key;
}
/*
   生成云购码 
   CountNum @ 生成个数
   len 	    @ 生成长度
   sid	    @ 商品ID
*/
function content_get_go_codes($CountNum = null, $len = null, $sid = null){
	global $DB;
	$table = 'cloud_shopcodes';
	
	$num = ceil($CountNum/$len);
	$code_i = $CountNum;
	if($num == 1) {
		$codes = array();
		for($i = 1; $i <= $CountNum; $i++) {
			$codes[$i] = 10000000 + $i;
		}
		shuffle($codes);
		$codes = serialize($codes);
		$query = $DB->query("INSERT INTO `$table` (`s_id`, `s_cid`, `s_len`, `s_codes`,`s_codes_tmp`) VALUES ('$sid', '1','$CountNum','$codes','$codes')");
		unset($codes);
		return $query;
	}
	$query_1 = true;
	$sql1 = '';
	$sql1 = "INSERT INTO `$table` (`s_id`, `s_cid`, `s_len`, `s_codes`,`s_codes_tmp`) VALUES";
	for($k = 1; $k < $num; $k++) {
		$codes = array();
		for($i = 1; $i <= $len; $i++) {
			$codes[$i] = 10000000 + $code_i;
			$code_i--;
		}
		shuffle($codes);
		$codes = serialize($codes);
		$sql1 .= " ('$sid', '$k','$len','$codes','$codes'),";
		unset($codes);
	}
	
	$sql1 = substr($sql1, 0, strlen($sql1)-1).';';
	$query_1 = $DB->query($sql1);
	$CountNum = $CountNum - (($num-1) * $len);
	$codes = array();	
	for($i = 1; $i <= $CountNum; $i++) {
		$codes[$i] = 10000000 + $code_i;	
		$code_i--;
	}
	shuffle($codes);
	$codes = serialize($codes);
	$query_2 = $DB->query("INSERT INTO `$table` (`s_id`, `s_cid`,`s_len`, `s_codes`,`s_codes_tmp`) VALUES ('$sid', '$num','$CountNum','$codes','$codes')");
	unset($codes);
	return $query_1 && $query_2;
}
//购买一个云码动作处理
function get_cloud_code($CartList = array()) {
	global $DB;
	$codes = $shopcodes = array();
	$ProductsIDS = implode(',', array_keys($CartList));
	$IDS = '';
	$DB->Get('cloud_shopcodes','id,s_id,s_codes_tmp','where s_id in ('.$ProductsIDS.') and s_codes_tmp<>"" order by s_cid desc' );
	while($r = $DB->fetch_assoc()){
		$shopcodes[$r['s_id']][] = $r;
	}
	
	$sql = "UPDATE cloud_shopcodes SET s_codes_tmp = CASE id";
	foreach($CartList as $key => $value){
		$codes[$key] = array();
		foreach($value as $val){
			$for_num = count($shopcodes[$key]);//总循环次数
			$code_sub = $val['Qty'];//剩余量
			
			for($j=0;$j<$for_num; $j++){
				if($code_sub<=0){
					break 2;
				}else{
					$s_codes = unserialize($shopcodes[$key][$j]['s_codes_tmp']);
					if(count($s_codes)>=$code_sub){
						$arr_temp = array_slice($s_codes, 0, $code_sub);
						$codes_tmp_sub = count($s_codes)==$code_sub ? '' : serialize(array_slice($s_codes, $code_sub, (count($s_codes)-$code_sub)));
						$sql .= ' WHEN '.$shopcodes[$key][$j]['id'].' THEN "'.$codes_tmp_sub.'"';
						$IDS .= $shopcodes[$key][$j]['id'].',';
						$code_sub = 0;//取完
						$codes[$key] = array_merge($codes[$key], $arr_temp);
						break 2;
					}else{
						$arr_temp = $s_codes;
						$codes[$key] = array_merge($codes[$key], $arr_temp);
						$code_sub = $code_sub - count($s_codes);
						$sql .= ' WHEN '.$shopcodes[$key][$j]['id'].' THEN ""';
						$IDS .= $shopcodes[$key][$j]['id'].',';
					}
				}
			}
		}
	}
	$IDS = trim($IDS, ',');
	$sql .= ' END WHERE id IN ('.$IDS.')';
	$DB->query($sql);
	return $codes;
}

//过滤非法字符
function filterspecil($str, $ing=''){
	$filestr = "/\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\`|\-|\=|\\\|\|/";
	if (is_array($ing)) {
		foreach($ing as $val){				
			$findstr = '\\' .$val. '|';
			$filestr = str_replace($findstr, '', $filestr);
		}
	}
	if(preg_match($filestr, $str)){
	$feifa = 0;
	}else{
	$feifa = 1;
	}	
	return $feifa;	
}

/*
 * 载取指定字符之前串
 * @param int $strlen 全部字符长度
 * @return int $tp   limit之前的字符长度
 */
function beforestr($str, $strlimit)
{
	$strlen = strlen($str);
	$tp = strpos($str,$strlimit);
	$sql = substr($str,-$strlen,$tp);
	return $sql;
}

/*
 * 递归创建目录
 */
function dmkdir($dir, $mode = 0777, $makeindex = TRUE){
	if(!is_dir($dir)) {
		dmkdir(dirname($dir), $mode, $makeindex);
		@mkdir($dir, $mode);
		if(!empty($makeindex)) {
			@touch($dir.'/index.html'); @chmod($dir.'/index.html', 0777);
		}
	}
	return true;
}

///计算参加抵用后的余额
if(!function_exists("diyong_act")) {
function diyong_act($sum,$regulartion,$User_Integral){
	
	$diyong_integral = 0;
	foreach($regulartion as $key=>$item){	
		if($sum >= $item['man']){
			$diyong_integral = $item['use'];
			continue;
		}
		 		
	}
	
	//如果用户积分小于最少抵用所需积分
	if($diyong_integral > $User_Integral){
		$diyong_integral = $User_Integral;
	}
	
	return $diyong_integral;
}
}

if(!function_exists("Integration_get")) {
    function Integration_get($Biz_ID,$rsConfig,$User_Integral,$Sub_Totalall)
    {		
		$diyong_list = json_decode($rsConfig['Integral_Use_Laws'],true);
		//用户设置了积分抵用规则，且抵用率大于零 
		  if(count($diyong_list) >0 && $rsConfig['Integral_Buy']>0){
			$diyong_intergral = diyong_act($Sub_Totalall,$diyong_list,$User_Integral);			
		  } else {
			  $diyong_intergral = 0;
		  }	  
		  return $diyong_intergral;
    }
}

//分销商可体现明细
function add_distribute_balance_record($UsersID, array $param = [])
{
	$_Status = [
		'门店','产品佣金','股东佣金','爵位佣金','代理佣金','返本','业务佣金','提现','级别佣金','其它'
	];	
	
	$param['Users_ID'] = $UsersID;	
	$param['Record_CreateTime'] = time();	
	return DB::table('distribute_balance_record')->insert($param);
}

//文档图片路径处理
function conUrl($con = '', $host = '')
{
    if (empty($host)) {
        $host = $host = (!empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' .  $_SERVER['HTTP_HOST'];
    }
    if (!empty($con)) {
        $pregRule = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.jpg|\.jpeg|\.png|\.gif|\.bmp]))[\'|\"].*?[\/]?>/";
        preg_match_all($pregRule, $con, $imgs);
        $imgs = $imgs[1];
        if (!empty($imgs)) {
            $new_imgs = [];
            foreach ($imgs as $key => $value) {
                $imgs[$key] = '"' . $value . '"';
                $new_imgs[] = '"' . (strpos($value, 'http') !== false ? $value : (rtrim($host, '/') . '/' . ltrim($value, '/'))) . '"';
            }
            $con = str_replace($imgs, $new_imgs, $con);
        }
    }
    return $con;
}

//保留小数点后几位，并补0
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

/**
 * 过滤掉emoji表情
 * @param string $str
 * @return bool  true false
 */
if (!function_exists('filterEmoji')) {
    function filterEmoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }
}