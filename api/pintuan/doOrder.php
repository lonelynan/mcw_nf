<?php
/*
 * 本文件主要是下订单不涉及支付情况
 */
require_once($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
if (!defined("CMS_ROOT")) define("CMS_ROOT", $_SERVER ["DOCUMENT_ROOT"]);
require_once(CMS_ROOT . '/include/helper/shipping.php');
require_once(CMS_ROOT . '/include/helper/tools.php');
require_once(CMS_ROOT . '/include/helper/lib_pintuan.php');
require_once(CMS_ROOT . '/include/helper/order.php');
require_once(CMS_ROOT . '/include/library/pay_order.class.php');
require_once(CMS_ROOT . '/Framework/Ext/virtual.func.php');
require_once(CMS_ROOT . '/Framework/Ext/sms.func.php');
require_once(CMS_ROOT . '/include/helper/url.php');

use Illuminate\Database\Capsule\Manager as DB;

$action             = isset($_REQUEST["action"]) && $_REQUEST["action"] ? $_REQUEST["action"] : '';
$ProductsID         = isset($_POST['ProductsID']) && $_POST['ProductsID'] ? intval($_POST['ProductsID']): 0;
$Users_ID           = isset($_POST['UsersID']) && $_POST['UsersID'] ? $_POST['UsersID']: '';
$UserID             = isset($_SESSION[$_POST['UsersID'].'User_ID']) && $_SESSION[$_POST['UsersID'].'User_ID'] ?
                            $_SESSION[$_POST['UsersID'].'User_ID'] : 0;

$msg = [
    '0000' => 'It\'s ok',
    '1000' => '非法参数提交',
    '1001' => '不正确的商户ID',
    '1002' => '用户未登录',
    '1003' => '商品不存在',
    '1004' => '商品库存不足',
    '1005' => '请选择收货地址',
    '1006' => '购物车不能为空',
    '1007' => '拼团未开始',
    '1008' => '拼团已结束',
    '1009' => '订单生成失败',
    '1010' => '手机号码不正确'
];

if ($action!=="checkout") {
    die(json_encode([ 'status'=>0, 'code'=> 1000, 'msg' => $msg['1000'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}

if(!$ProductsID){
    die(json_encode([ 'status'=>0, 'code'=> 1000, 'msg' => $msg['1000'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}
if(!$Users_ID){
    die(json_encode([ 'status'=>0, 'code'=> 1001, 'msg' => $msg['1001'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}
if(!$UserID){
    die(json_encode([ 'status'=>0, 'code'=> 1002, 'msg' => $msg['1002'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}

$proInfo = $DB->GetRs("shop_products", "*", "where Products_ID='{$ProductsID}' and Users_ID='{$Users_ID}'");
if ($proInfo['Products_Count'] == 0) { // 库存不足
    die(json_encode([
        'status' => 0,
        'code' => - 1,
        'msg' => '商品库存不足'
    ]));
}

if(empty($proInfo)){
    die(json_encode([ 'status'=>0, 'code'=> 1003, 'msg' => $msg['1003'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}
if($proInfo['Products_Count']<=0){   //库存不足
    die(json_encode([ 'status'=>0, 'code'=> 1004, 'msg' => $msg['1004'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}

$addressID          = isset($_POST['Address_ID']) && $_POST['Address_ID'] ? intval($_POST['Address_ID']) : 0;
//个人地址
$Data               = [];
$results            = [];
$shipp_TotalPrice   = 0;
$Order_Shipping     = [];
$speclist           = isset($_POST['spec_list']) && $_POST['spec_list'] ? $_POST['spec_list'] : '';
// 获取商品信息
//$goodsInfo  = getProducts($ProductsID,$Users_ID,$speclist);
//$CartList 	= $goodsInfo["cartlist"];
$BizID 		= $proInfo["Biz_ID"];
//$CartList['Shipping_fee'] = 0;
$goodsName = $proInfo['Products_Name'];
$sessionid = session_id();
$teamid =  isset($_POST['pintuanID']) && $_POST['pintuanID']? intval($_POST['pintuanID']) : 0;
//$CartList['TeamID']=isset($_SESSION[$sessionid.'_TeamID']) && $_SESSION[$sessionid.'_TeamID'] ? intval($_SESSION[$sessionid.'_TeamID']) : 0;// 物流信息:代码开始

$wuliu = isset($_POST["wuliu"]) && $_POST["wuliu"] ? $_POST["wuliu"] : 0;
if ($wuliu) {
    $shipping = $DB->GetRs("shop_shipping_company", "Shipping_Name", "where Users_ID='" . $Users_ID . "' and Shipping_ID=" . $_POST["wuliu"]);
	if(!empty($shipping)){
        $shipp_TotalPrice = getShipFee($wuliu,$Users_ID,$BizID,$proInfo['Products_Weight']);
        $Order_Shipping =["Express"=>$shipping["Shipping_Name"], "Price" => $shipp_TotalPrice ? $shipp_TotalPrice:0];
        if($proInfo['Products_IsShippingFree']==1){ //包邮
            $shipp_TotalPrice = 0;
        }
    }
}
//$CartList['Shipping_fee'] = $shipp_TotalPrice;

$myRedis = new Myredis($DB, 7);
$redisCart = $myRedis->getCartListValue($Users_ID.'PTCartList', $UserID);
$pro_info = [];
if (!empty($redisCart)) {
    $cart = json_decode($redisCart ,true);
    $pintuan_shops = [];
    foreach ($cart as $biz_id => $pro_info) {
        foreach ($pro_info as $pro_id => $value) {
            foreach ($value as $attr_id => $attr_value) {
                $pintuan_shops["goodsid"] = $pro_id;
                $pintuan_shops["is_One"] = 1; // 1是团购,  0 是单购

                //拼团产品价格
                if (empty($attr_value['Property'])) { //无属性
                    $goods_price = $attr_value['pintuan_pricex'];
                } else {    //有属性
                    $goods_price = $attr_value['Property']['shu_pricesimp'];
                }
                $pintuan_shops['goods_price'] = $goods_price; //单价

                $pintuan_shops["is_vgoods"] = $attr_value['Products_IsVirtual'] == 1 ? 0 : 1; // 是否虚拟 0：是 1：不是
                $pintuan_shops["is_Draw"] = 1; // 是否抽奖0：是1：不是
                $pintuan_shops["goods_num"] = $attr_value['Qty']; // 购买数量
            }
        }
    }
} else {
    die(json_encode([ 'status'=>0, 'code'=> 1006, 'msg' => $msg['1006'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}
//拼团结束时间
$pintuan_stop=date('Y-m-d',$proInfo['pintuan_end_time']);
$pintuan_start=date('Y-m-d',$proInfo['pintuan_start_time']);
$now = date('Y-m-d',time());

//如果是拼团并且超过指定的时间
if($pintuan_shops["is_One"] && $now<$pintuan_start){
    die(json_encode([ 'status'=>0, 'code'=> 1007, 'msg' => $msg['1007'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}
if($pintuan_shops["is_One"] && $pintuan_stop<$now){
    die(json_encode([ 'status'=>0, 'code'=> 1008, 'msg' => $msg['1008'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}
$order_no =order_no($UserID);
$order_id = 0;
//订单状态:  0：待确认       1：订单已生成未付款       2：已付款     3：已发货        4：完成
//订单提交
begin_trans();
if($pintuan_shops["is_vgoods"]==1){
    $address = DB::table("user_address")->where(["Address_ID" => $addressID])->get();
    if(empty($address)){
        die(json_encode([ 'status'=>0, 'code'=> 1005, 'msg' => $msg['1005'] , 'url' => '']));
    }
    foreach ($address as $res){
        $Data['Address_Name']=$res['Address_Name'];
        $Data['Address_Mobile']=$res['Address_Mobile'];
        $Data['Address_Province']=$res['Address_Province'];
        $Data['Address_City']=$res['Address_City'];
        $Data['Address_Area']=$res['Address_Area'];
        $Data['Address_Detailed']=$res['Address_Detailed'];
    }
}else{
    $mobile = !empty($_POST['mobile'])?$_POST['mobile']:'';
    if(!$mobile || !preg_match("/^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})/", $mobile)){
        die(json_encode([ 'status'=>0, 'code'=> 1010, 'msg' => $msg['1010'], 'url' => ''], JSON_UNESCAPED_UNICODE));
    }
    $Data['Address_Mobile']=$mobile;
}
$Data['Users_ID'] = $Users_ID;
$Data['Order_Type'] = $pintuan_shops['is_One']==1 ? 'pintuan' : 'dangou';
$Data['Order_Status']='1';
$Data['Order_CreateTime']=time();
$Data['Order_TotalPrice']=$shipp_TotalPrice+$pintuan_shops["goods_num"]*$pintuan_shops["goods_price"];
$Data['Order_TotalAmount'] = $Data['Order_TotalPrice'];
$Data["Order_Shipping"]= !empty($Order_Shipping) ? json_encode($Order_Shipping,JSON_UNESCAPED_UNICODE) : '';
$Data["Order_CartList"]=json_encode($pro_info, JSON_UNESCAPED_UNICODE);
$Data["Order_ShippingID"]=$wuliu;
$Data["Order_Code"]=$order_no;
$Data["Biz_ID"] = $BizID;
$Data['User_ID']=$UserID;
$order_id = DB::table("user_order")->insertGetId($Data);

$pData=[];
$pData['order_id']=$order_id;
$pData['pintuan_id'] = $teamid;
$pData['Users_ID'] = $Users_ID;
$pData['User_ID'] = $UserID;
$pData['addtime'] = time();
$pData['order_status']="1";
$pData['pintuan_status'] = '1';
$pData['products_status']= $pintuan_shops['is_One']==1 ? ($pintuan_shops['is_Draw']==0 ? 2 : 1) : 0;
$pData['is_vgoods']= $pintuan_shops["is_vgoods"]==0 ? 0 : 1;
$pLastId=DB::table('pintuan_order')->insertGetId($pData);
if($order_id && $pLastId){
    $myRedis->clearCartListValue($Users_ID.'PTCartList', $UserID);
    if(isset($_SESSION[$sessionid.'_TeamID'])){
        unset($_SESSION[$sessionid.'_TeamID']);
    }
    Stock($ProductsID,$Users_ID);
    commit_trans();
    // 此处调用微信推送接口，告知用户已下单
    sendWXMessage($Users_ID,$order_id,"您已成功提交拼团订单，订单号为：".$order_no, $UserID);
    die(json_encode([ 'status'=>1, 'code'=> '0000', 'msg' => $msg['0000'], 'url'=>'/api/'.$Users_ID.'/pintuan/cart/payment/'.$order_id.'/'], JSON_UNESCAPED_UNICODE));
}else{
    back_trans();
    die(json_encode([ 'status'=>0, 'code'=> 1009, 'msg' => $msg['1009'], 'url' => ''], JSON_UNESCAPED_UNICODE));
}
?>