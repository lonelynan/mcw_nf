<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
if(!defined("CMS_ROOT")) define("CMS_ROOT", $_SERVER["DOCUMENT_ROOT"]);
require_once (CMS_ROOT . '/include/helper/shipping.php');
require_once (CMS_ROOT . '/include/helper/tools.php');
require_once (CMS_ROOT . '/include/helper/lib_pintuan.php');
require_once (CMS_ROOT . '/include/helper/lib_products.php');
require_once (CMS_ROOT . '/include/helper/order.php');
require_once (CMS_ROOT . '/include/library/pay_order.class.php');
require_once (CMS_ROOT . '/Framework/Ext/virtual.func.php');
require_once (CMS_ROOT . '/Framework/Ext/sms.func.php');
require_once (CMS_ROOT . '/include/helper/flow.php');
require_once (CMS_ROOT . '/api/pintuan/comm/function.php');
require_once (CMS_ROOT . '/include/models/UserMoneyRecord.php');
require_once (CMS_ROOT . '/include/helper/distribute.php');

use Illuminate\Database\Capsule\Manager as DB;

$UsersID = I('UsersID');
$UserID = S($UsersID."User_ID") ? S($UsersID."User_ID") : 0;
$action = I("action");
if(!$UsersID || !$action){
    die(json_encode([
        'status' => 0,
        'msg' => '非法的参数传递',
        'url' => '/api/' . $UsersID . '/pintuan/'
    ], JSON_UNESCAPED_UNICODE));
}
if(!$UserID){
    die(json_encode([
        'status' => 0,
        'msg' => '您还没有登录，请登录',
        'url' => '/api/' . $UsersID . '/user/0/login/'
    ], JSON_UNESCAPED_UNICODE));
}

// 地址的写入 删除 修改 等
if($action == 'address_edit_save'){
    $Flag=true;
    $AddressID = I("AddressID");
    $Data=array(
        "Address_Name" => I("Name"),
        "Address_Mobile" => I("Mobile"),
        "Address_Province" => I("Province"),
        "Address_City" => I("City"),
        "Address_Area" => I("Area"),
        "Address_Detailed" => I("Detailed"),
        "Address_Is_Default" => I("default"),
    );
    begin_trans();
    if($Data['Address_Is_Default'] == 1){
        $isExists = DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->first();
        if(!empty($isExists)){
            $Flag = DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->update(["Address_Is_Default" => 0]);
            if($Flag === false){
                back_trans();
            }
        }
    }
    if(! $AddressID){
        //增加
        $Data["Users_ID"] = $UsersID;
        $Data["User_ID"] = $UserID;
        $Flag = $Flag && DB::table("user_address")->insert($Data);

    }else{
        //修改
        $Flag = $Flag && DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID, "Address_ID" => $AddressID])->update($Data);
    }
    if($Flag){
        $url = '/api/'.$UsersID.'/pintuan/my/address/';
        if(isset($_SESSION[$UsersID."IS_NEW_USER"]) && $_SESSION[$UsersID."IS_NEW_USER"]){
            $url = isset($_SESSION[$UsersID . "NEW_USER_REFER"])?$_SESSION[$UsersID . "NEW_USER_REFER"]:'';
        }
        if(!empty($_SESSION[$UsersID."From_Checkout"])){
            if($_SESSION[$UsersID."From_Checkout"]==1){
                $url = $_SESSION[$UsersID."HTTP_REFERER"];
            }else{
                $url = $_SESSION[$UsersID."From_Checkout"];
            }
            unset($_SESSION[$UsersID."From_Checkout"]);
        }
        if(!empty($_SESSION[$UsersID."Select_Model"])&&!empty($_POST['AddressID'])){
            $url = '/api/'.$UsersID.'/pintuan/my/address/'.$_POST['AddressID'].'/';
            unset($_SESSION[$UsersID."Select_Model"]);
        }
        commit_trans();
        $Data=[
            'status'=>1,
            'msg'=>'操作成功',
            'url'=>$url
        ];
    }else{
        back_trans();
        $Data=[
            'status'=>0,
            'msg'=>'网络拥堵，请稍后再试！',
            'url'=> ''
        ];
    }
    die(json_encode($Data,JSON_UNESCAPED_UNICODE));
}
//通过ajax改变默认地址
if($action == 'set_default_address'){
    $address_id = I("address_id");
    if(!$address_id){
        die(json_encode(array(
            'status'=>0,
            'msg'=>'缺少必要的参数'
        ),JSON_UNESCAPED_UNICODE));
    }
    begin_trans();
    $Flag = DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->update(["Address_Is_Default" => 0]);
    $Flag1 = DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID, "Address_ID" =>
        $address_id])
        ->update(["Address_Is_Default" => 1]);

    if($Flag && $Flag1){
        commit_trans();
        die(json_encode(array(
            'status'=>1,
            'msg'=>'修改默认地址成功'
        ),JSON_UNESCAPED_UNICODE));
    }else{
        back_trans();
        die(json_encode(array(
            'status'=>0,
            'msg'=>'设置默认地址失败'
        ),JSON_UNESCAPED_UNICODE));
    }
}
// 收藏表ajax 插入
if($action == 'collect'){
    $goodsid = I("goodsid");
    if(!$goodsid){
        die(json_encode([
            'status' => 0,
            'msg' => '您所收藏的产品不存在',
            'url' => '/api/' . $UsersID . '/pintuan/'
        ], JSON_UNESCAPED_UNICODE));
    }
    $rsCollect = DB::table("pintuan_collet")->where(["users_id" => $UsersID, 'userid' => $UserID, 'productid' =>
        $goodsid])->first();
    $state = 0; // 1 收藏  0 取消收藏
    if (!empty($rsCollect)) {
        DB::table("pintuan_collet")->where(["users_id" => $UsersID, 'userid' => $UserID, 'productid' =>
            $goodsid])->delete();
        $state = 0;
    } else {
        $Data = array(
            'productid' => $goodsid,
            'userid' => $UserID,
            'addtime' => time(),
            'users_id' => $UsersID
        );
        DB::table("pintuan_collet")->insert($Data);
        $state = 1;
    }
    die(json_encode([
        'status' => 1,
        'msg' => 'It \'s ok',
        'state' => $state,
        'url' => ''
    ], JSON_UNESCAPED_UNICODE));
}
// 收藏列表页面删除
if($action == 'collectDel'){
    $cid = I("cid");
    if(!$cid){
        die(json_encode([
            'status' => 0,
            'msg' => '您所收藏的产品不存在',
            'url' => '/api/' . $UsersID . '/pintuan/'
        ], JSON_UNESCAPED_UNICODE));
    }
    $flag = DB::table("pintuan_collet")->where(["users_id" => $UsersID, 'userid' => $UserID, 'id' =>
        $cid])->delete();
    $status = 0;
    $msg = '取消收藏失败';
    if($flag){
        $status = 1;
        $msg = '取消收藏成功';
    }
    die(json_encode([
        'status' => $status,
        'msg' => $msg
    ], JSON_UNESCAPED_UNICODE));
}
// 加入购物车
if($action == 'addcart'){
    $goodsid = intval(I("goodsid"));
    $sessionid = session_id();
    $teamid=intval(I("teamid", 0));
    $goodsType = I("goodsType");
    //是否参团进行判断
    $goodsInfo = DB::table("pintuan_products")->where(["Users_ID" => $UsersID, "Products_ID" => $goodsid])->first();
    if(!$goodsid){
        die(json_encode([
            'status' => 0,
            'msg' => '您所购买的产品不存在',
            'url' => '/api/' . $UsersID . '/pintuan/'
        ], JSON_UNESCAPED_UNICODE));
    }
    //参团的情况
    if ($teamid) {
        $flagTeamDetail = DB::table("pintuan_teamdetail")->where(["teamid" => $teamid, "userid" => $UserID])->first();
        if ($flagTeamDetail) {
            die(json_encode(array(
                'status' => 0,
                'msg' => '您已经参加过本团了'
            ), JSON_UNESCAPED_UNICODE));
        }
        $flagTeam = DB::table("pintuan_team")->where(["id" => $teamid, "teamnum" => $goodsInfo['people_num']])->first();
        if ($flagTeam) {
            die(json_encode(array(
                'status' => 0,
                'msg' => '您所参加的团已结束'
            ), JSON_UNESCAPED_UNICODE));
        }
    }

    //产品库存判断
    if($goodsInfo['Products_Count']==0){
        die(json_encode(array(
            'status' => 0,
            'msg' => '商品库存不足'
        ), JSON_UNESCAPED_UNICODE));
    }
    if($goodsInfo['order_process'] == 1||$goodsInfo['order_process'] ==2) {
        $isvirual=1;
    }else{
        $isvirual=0;
    }

    $time = time();
    if($goodsInfo['starttime']>$time){
        die(json_encode(array(
            'status' => 0,
            'msg' => '活动未开始'
        ), JSON_UNESCAPED_UNICODE));
    }
    if($goodsInfo['stoptime']<$time){
        die(json_encode(array(
            'status' => 0,
            'msg' => '活动已结束'
        ), JSON_UNESCAPED_UNICODE));
    }
    if($goodsType){     //团购
        $price = $goodsInfo['Products_PriceT'];
    }else{
        $price = $goodsInfo['Products_PriceD'];
    }
    $Data=array(
        'goodsid'=>$goodsid,
        'is_vgoods'=>$isvirual,
        'usersid'=>$UsersID,
        'sessionid'=>$sessionid,
        'goods_price'=>$price,
        'goods_name'=>$goodsInfo['Products_Name'],
        'goods_num'=> 1,
        'is_Draw'=>$goodsInfo['Is_Draw'],
        'is_One'=>$goodsType,
        'teamid' => $teamid,
    );
    $CartList[$sessionid] = $Data;
    $myRedis = new Myredis($DB, 7);
    $myRedis->setCartListValue($UsersID.'PTCartList', $UserID, json_encode($CartList, JSON_UNESCAPED_UNICODE));
    $sessionid = session_id();
    $_SESSION[$sessionid.'_TeamID']=$teamid;
    die(json_encode(array(
        'isvirtual' => $isvirual,
        'status' => 1,
        'msg' => 'ok'
    ), JSON_UNESCAPED_UNICODE));
}
// 已收货按钮的控制
if ($action == 'confirm') {
    $orderid=intval(I("orderid",0));
    $rsOrder = DB::table("user_order")->where(["Users_ID" =>$UsersID, "Order_ID" => $orderid])->first(["Order_Status","Users_ID"]);
    if (! $rsOrder) {
        die(json_encode([
            'status' => 0,
            'msg' => '该订单不存在',
            'url' => ''
        ], JSON_UNESCAPED_UNICODE));
    }
    if ($rsOrder["Order_Status"] != 3) {
        die(json_encode([
            'status' => 0,
            'msg' => '只有在‘已发货’状态下才可确认收货',
            'url' => ''
        ], JSON_UNESCAPED_UNICODE));
    }

    $sql = "update user_order as o inner join pintuan_order as p on o.Order_ID = p.order_id set o.Order_Status = 4,p
.Order_Status=4 where o.Order_ID=:OrderID";
    $flagOrder = DB::statement($sql,[":OrderID" => $orderid]);

    require_once (CMS_ROOT . '/include/helper/balance.class.php');
    $balance_sales = new balance($DB1, $rsOrder['Users_ID']);
    $balance_sales->add_sales($orderid);
    if ($flagOrder){
        die(json_encode([
            'status' => 1,
            'msg' => '收货成功',
            'url' => ''
        ], JSON_UNESCAPED_UNICODE));
    }else{
        die(json_encode([
            'status' => 0,
            'msg' => '确认收货失败,请联系商城管理员',
            'url' => ''
        ], JSON_UNESCAPED_UNICODE));
    }
}
if($action=="selectPayment"){

    $OrderID = I("OrderID", 0);

    if(!$OrderID){
        die(json_encode(["status" => 0, "msg" => "参数缺失！" ],JSON_UNESCAPED_UNICODE));
    }
    $rsOrder = DB::table("user_order")->where(["Users_ID" => $UsersID,"User_ID" => $UserID, "Order_ID" => $OrderID])
        ->first();
    if(empty($rsOrder)){
        die(json_encode(["status" => 0, "msg" => "非法支付，订单不存在！" ],JSON_UNESCAPED_UNICODE));
    }

    if($rsOrder['Order_Status'] != 1){
        die(json_encode(["status"=>0, "msg"=>'此订单不是“待付款”状态，不能付款！'],JSON_UNESCAPED_UNICODE));
    }
    $payment = I("PaymentMethod");
    $Data=[
        "Order_PaymentMethod"=> $payment,
        "Order_PaymentInfo"=> $payment,
        "Order_DefautlPaymentMethod"=> $payment
    ];
    $PaymentMethod = [
        "微支付" => 1,
        "支付宝" => 2,
        "易宝支付" => 4,
        "银联支付" => 5
    ];
    $Method = $PaymentMethod[$payment];
    $Flag = DB::table("user_order")->where(["Users_ID" => $UsersID,"User_ID" => $UserID, "Order_ID" => $OrderID])->update($Data);
    if($Flag!== false){
        $rsPay = DB::table("users_payconfig")->where(["Users_ID" => $UsersID])->first();
        if($Method==1){//微支付
            $rsUsers = DB::table("users")->where(["Users_ID" => $UsersID])->first();
            if($rsPay["PaymentWxpayEnabled"]==0 || empty($rsPay["PaymentWxpayPartnerId"]) || empty($rsPay["PaymentWxpayPartnerKey"]) || empty($rsUsers["Users_WechatAppId"]) || empty($rsUsers["Users_WechatAppSecret"])){
                die(json_encode(["status"=>0, "msg"=>'商家“微支付”支付方式未启用或信息不全，暂不能支付！'],JSON_UNESCAPED_UNICODE));
            }
            $url = "/pay/wxpay2/sendto_pintuan.php?UsersID=".$UsersID."_".$OrderID;
        }elseif($Method==2){//支付宝
            if($rsPay["Payment_AlipayEnabled"]==0 || empty($rsPay["Payment_AlipayPartner"]) || empty($rsPay["Payment_AlipayKey"]) || empty($rsPay["Payment_AlipayAccount"])){
                die(json_encode(["status"=>0, "msg"=>'商家“支付宝”支付方式未启用或信息不全，暂不能支付！'],JSON_UNESCAPED_UNICODE));
            }
            $url = "/pay/alipay/sendto_pintuan.php?UsersID=".$UsersID."_".$OrderID;
        }elseif($Method==4){//易宝支付
            if($rsPay["PaymentYeepayEnabled"]==0 || empty($rsPay["PaymentYeepayAccount"]) || empty($rsPay["PaymentYeepayPrivateKey"]) || empty($rsPay["PaymentYeepayPublicKey"]) || empty($rsPay["PaymentYeepayYeepayPublicKey"])){
                die(json_encode(["status"=>0, "msg"=>'商家“支付宝”支付方式未启用或信息不全，暂不能支付！'],JSON_UNESCAPED_UNICODE));
            }
            $url = "/pay/yeepay/sendto.php?UsersID=".$UsersID."_".$OrderID;
        }elseif($Method==5){//银联支付
            if($rsPay["Payment_UnionpayEnabled"]==0 || empty($rsPay["Payment_UnionpayAccount"]) || empty($rsPay["PaymentUnionpayPfx"]) || empty($rsPay["PaymentUnionpayPfxpwd"])){
                die(json_encode(["status"=>0, "msg"=>'商家“银联支付”支付方式未启用或信息不全，暂不能支付！'],JSON_UNESCAPED_UNICODE));
            }
            $url = "/pay/Unionpay/sendto.php?UsersID=".$UsersID."_".$OrderID;
        }
        $Data = [ "status"=>1, "url"=>$url ];
    }else{
        $Data = [ "status"=>0, "msg"=>'在线支付出现错误' ];
    }

    die(json_encode($Data,JSON_UNESCAPED_UNICODE));
}
// 余额支付的
if ($action == "payment") {
    $OrderID = I("OrderID", 0);
    if(!$OrderID){
        die(json_encode([
            "status"=>0,
            "msg"=>'不正确的参数传递'
        ],JSON_UNESCAPED_UNICODE));
    }
    $payment = I("PaymentMethod");
    if($payment == "余额支付"){//余额支付
        $result = payDone($UsersID, $OrderID, "余额支付");
        die(json_encode($result, JSON_UNESCAPED_UNICODE));
    }else if($payment == "线下支付"){
        $result = payDone($UsersID, $OrderID, "线下支付");
        die(json_encode($result, JSON_UNESCAPED_UNICODE));
    }else{
        die(json_encode(["status"=>0,"msg"=>'不正确的支付方式'], JSON_UNESCAPED_UNICODE));
    }
}
// 确认收货

// 评论
if($action=="submitCommit"){
    $Data=array();
    $Data["Product_ID"] = intval(I("products_id"));
    $Data["Order_ID"] = I("Order_ID");
    $Data["User_ID"] = $UserID;
    $Data["MID"] = "pintuan";
    $Data["CreateTime"] = time();
    $Data["Users_ID"] = $UsersID;
    $Data["Score"] = I("fenshu");
    $Data["Note"] = I("txt");
    $Data["Status"] = 0;
    $Data["pingfen"]=I('fenshu');
    if(!$Data["Product_ID"] || !$Data["Order_ID"]){
        die(json_encode([
            'status' => 0,
            'msg' => '您所评论的产品不存在',
            'url' => '/api/' . $UsersID . '/pintuan/'
        ], JSON_UNESCAPED_UNICODE));
    }
    $rsOrder = DB::table('user_order')->where(['Order_ID' => $Data['Order_ID']])->first(["Order_Status","Users_ID","Biz_ID"]);
    if (! empty($rsOrder) && $rsOrder['Biz_ID']) {
        $Data['Biz_ID'] = $rsOrder['Biz_ID'];
    }
    $Flag = DB::table("pintuan_commit")->insert($Data);
    if($Flag){
        die(json_encode(['status' => 1, 'msg' => "It\'s ok"], JSON_UNESCAPED_UNICODE));
    }
    else {
        die(json_encode(['status' => 0, 'msg' => "订单评论失败"], JSON_UNESCAPED_UNICODE));
    }
}
// 取消订单
if ($action =="cancelOrder") {
    $Order_ID = I("orderid");
    //若是分销订单，删除分销记录
	mysql_query("BEGIN"); //开始事务定义

	if (is_distribute_order($DB, $UsersID, $Order_ID)) {
		delete_distribute_record($DB, $UsersID, $Order_ID);
	}
	
	$condition = "Users_ID='" . $UsersID . "' and User_ID='" . $_SESSION[$UsersID . "User_ID"] . "' and Order_ID=" . $Order_ID;
	$Flag_b = $DB->Del("user_order", $condition);


	if ($Flag_b===false) {
		mysql_query("ROLLBACK"); //回滚事务
		die(json_encode(['status' => 0, 'msg' => "订单取消失败"], JSON_UNESCAPED_UNICODE));
	} else {
		mysql_query("COMMIT"); //执行事务
		
		die(json_encode(['status' => 1, 'msg' => "It\'s ok"], JSON_UNESCAPED_UNICODE));
	}
}
?>