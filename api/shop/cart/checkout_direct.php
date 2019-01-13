<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/shipping.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/flow.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/library/smarty.php');
use Illuminate\Database\Capsule\Manager as DB;

$base_url = base_url();
$shop_url = shop_url();

if (isset($_GET["UsersID"])) {
    $UsersID = $_GET["UsersID"];
} else {
    layerOpen('缺少必要的参数');
    exit;
}

/****************** 设置smarty模板 start ***********************/
$smarty->left_delimiter = "{{";
$smarty->right_delimiter = "}}";
$template_dir = $_SERVER["DOCUMENT_ROOT"] . '/api/shop/html';
$smarty->template_dir = $template_dir;
/****************** 设置smarty模板 end ***********************/
if (isset($_GET['cart_key']) && !empty($_GET['cart_key'])) {
    $_SESSION[$UsersID . "HTTP_REFERER"] = "/api/" . $UsersID . "/shop/cart/checkout_direct/?love&cart_key=" . $_GET['cart_key'];
} else {
    $_SESSION[$UsersID . "HTTP_REFERER"] = "/api/" . $UsersID . "/shop/cart/checkout_direct/?love";
}

$rsConfig = shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig, $dis_config);

//用户授权登录
$is_login = 1;
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/library/wechatuser.php');
$User_ID = !empty($_SESSION[$UsersID . "User_ID"]) ? $_SESSION[$UsersID . "User_ID"] : 0;

$rsUser = DB::table('user')->where(['Users_ID' => $UsersID, 'User_ID' => $User_ID])->first();

/*************会员价计算 start **************/
$user_curagio = get_user_curagio($UsersID, $User_ID);
/*************会员价计算 end **************/

/******************* 获得用户收货地址 start ***********************/
$Address_ID = $request->input('AddressID');
$condition = [];
if (empty($Address_ID)) {
    $condition = ['Users_ID' => $UsersID, 'User_ID' => $User_ID, 'Address_Is_Default' => 1];
} else {
    $condition = ['Users_ID' => $UsersID, 'User_ID' => $User_ID, 'Address_ID' => $Address_ID];
}
$rsAddress = DB::table('user_address')->where($condition)->first();
$area_json = read_file($_SERVER["DOCUMENT_ROOT"] . '/data/area.js');
$area_array = json_decode($area_json, true);
$province_list = $area_array[0];
if ($rsAddress) {
    $Province = $province_list[$rsAddress['Address_Province']];
    $City = $area_array['0,' . $rsAddress['Address_Province']][$rsAddress['Address_City']];
    $Area = $area_array['0,' . $rsAddress['Address_Province'] . ',' . $rsAddress['Address_City']][$rsAddress['Address_Area']];
} else {
    $_SESSION[$UsersID . "From_Checkout"] = 1;
    header("location:/api/" . $UsersID . "/user/my/address/edit/");
    exit;
}
/******************* 获得用户收货地址 end ***********************/

/********************* 计算积分 start *************************/
$myRedis = new Myredis($DB, 7);
$cartKey = isset($_GET['cart_key']) ? $_GET['cart_key'] : 'DirectBuy';
$cart_key = isset($_GET['cart_key']) ? $UsersID.$_GET['cart_key'] :  $UsersID . "DirectBuy";
$redisCart = $myRedis->getCartListValue($cart_key, $User_ID);
if (empty($redisCart)) {
    header("location:/api/" . $UsersID . "/shop/cart/?love");
    exit;
}
$CartList = json_decode($redisCart, true);
$Integration = 0;
$biz_info = [];
$Is_BizStores = [];
$BizIDArr = [];
foreach ($CartList as $biz_id => $products) {
	$BizIDArr[] = $biz_id;
    //获取对应商家信息
    $rsBiz = $DB->GetRs('biz', 'Biz_Account, Biz_Name, Biz_Logo, Is_BizStores, Biz_Address, Biz_PrimaryLng, Biz_PrimaryLat, Biz_Phone', 'where Biz_ID = ' . $biz_id);
    if (empty($rsBiz)) {
        unset($CartList[$biz_id]);
        continue;
    }

    $biz_info[$biz_id] = $rsBiz;
    foreach ($products as $pro_id => $vv) {
        foreach ($vv as $attr_id => $v) {
            $ProductsIntegration = $DB->GetRs("shop_products", "Products_Integration", "where Products_ID=" . $pro_id);
            $CartList[$biz_id][$pro_id][$attr_id]['ProductsIntegration'] = $ProductsIntegration['Products_Integration'];
            $Integration += $v['Qty'] * $ProductsIntegration['Products_Integration'];
            //获取该产品是否支持门店自提
            $Is_BizStores = $DB->GetRs("shop_products", "Is_BizStores", "where Products_ID=" . $pro_id);
        }
    }
}
/********************* 计算积分 end *************************/
if ($cartKey == 'PTCartList') {
    $user_curagio = 0;
}

$total_price = 0;
$toal_shipping_fee = 0;
if (count($CartList) > 0) {
    if ($rsAddress && $rsConfig['NeedShipping'] == 1) {
        $City_Code = $rsAddress['Address_City'];
    } else {
        $City_Code = 0;
    }
    $info = get_order_total_info($UsersID, $CartList, $rsConfig, array(), $City_Code, $user_curagio);
    $Shipping_IDs = array();
    foreach ($info as $key => $val) {
        if (is_numeric($key)) {
            $Shipping_IDs[$key] = $val['Shipping_ID'];
        }
    }
    $Shipping_IDsstr = json_encode($Shipping_IDs, JSON_UNESCAPED_UNICODE);
    $total_price = $info['total'];
    $total_shipping_fee = $info['total_shipping_fee'];
} else {
    header("location:/api/" . $UsersID . "/shop/cart/?love");
    exit;
}

/*商品信息汇总*/
$total_price = $total_price + $info['total_shipping_fee'];
$Default_Business = 'express';
$Business_List = array('express' => '快递', 'common' => '平邮');
//获取前台可用的快递公司
foreach ($info as $Biz_ID => $BizCartList) {
    if (is_integer($Biz_ID)) {
        $biz_company_dropdown[$Biz_ID] = get_front_shiping_company_dropdown($UsersID, $BizCartList['Biz_Config']);
    }
    // 门店自提
//    if($rsBiz['Is_BizStores'] == 1 && $Is_BizStores['Is_BizStores'] == 1){
//        $biz_company_dropdown[$Biz_ID]['Is_BizStores'] = '到店自提';
//    }
}

$Business = 'express';

require_once($_SERVER["DOCUMENT_ROOT"] . '/include/yinru/defaut_switch.php');

//模块开关的开启
$perm_config = $DB->GetAssoc('permission_config', '*', 'where Users_ID="' . $UsersID . '"  AND Is_Delete = 0 AND Perm_Tyle = 5 order by Permission_ID asc');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <title>提交订单——实物</title>
    <link href="/static/api/js/mui/mui.min.css" rel="stylesheet"/>
    <link href="/static/api/css/checkout.css" rel="stylesheet"/>
    <script src="/static/js/jquery-1.7.2.min.js"></script>
    <script src="/static/js/layer/mobile/layer.js"></script>
    <script type='text/javascript' src='/static/api/js/global.js?t=<?=time()?>'></script>
    <script type='text/javascript' src='/static/api/shop/js/flow.js?t=<?=time()?>'></script>
    <script type='text/javascript' src='/static/api/shop/js/toast.js?t=<?=time();?>'></script>
    <script>
        //门店js插入使用, 在flow.js中
        var new_checkout = 1;
        var base_url = '<?=$base_url?>';
        var Users_ID = '<?=$UsersID?>';
		var BizIDStr = '<?=implode(',', $BizIDArr) ?>';
		
        $(document).ready(function(){
            flow_obj.checkout_init();
        });
    </script>
</head>

<body>
<header class="mui-bar mui-bar-nav">
    <a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></a>
    <h1 class="mui-title">提交订单</h1>
</header>
<div class="mui-content">
    <?php require_once(CMS_ROOT . '/api/shop/cart/checkout_common.php') ?>
</div>

<!--点击滑出 遮罩-->
<div class="tc-bg"></div>
<!--点击滑出-end-->
</body>
</html>