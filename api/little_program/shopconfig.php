<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/shipping.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/flow.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/userinfo.class.php');
require_once('Lib.class.php');

use Illuminate\Database\Capsule\Manager as DB;

$res = little_program_key_check($_POST);
if ($res['errorCode'] != 0) {
    exit(json_encode($res, JSON_UNESCAPED_UNICODE));
}

if (!isset($_POST['Users_Account']) || !isset($_POST['act'])) {
    exit(json_encode(['errorCode' => 401, 'msg' => 'missing args hint ..']));
}

$resBiz = $DB->GetRs('users', 'Users_ID', 'where Users_Account = "' . $_POST['Users_Account'] . '"');
if (empty($resBiz)) {
    exit(json_encode(['errorCode' => 401, 'msg' => '不存在此商城']));
}

$Users_ID = $resBiz['Users_ID'];
//$Users_ID = '5wy9h3z78k';

$act = $_POST['act'];

$host_url = (!empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' .  $_SERVER['HTTP_HOST'];

//---------------- 用户登录、注册 start ----------------
//用户登录
if ($act == 'login') {
    if (!isset($_POST['code'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数code']));
    }
    $code = $_POST['code'];
    $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='. APPID_LP .'&secret='. APPSECRET_LP .'&js_code='. $code .'&grant_type=authorization_code';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = json_decode(curl_exec($ch), true);
    if (!isset($output['errcode'])) {
        if (isset($output['unionid'])) {
            $userInfo = $DB->GetRs('user', '*', "where unionid = '". $output['unionid'] ."' and Users_ID = '". $Users_ID ."'");
            if (!$userInfo) {
                $userInfo = $DB->GetRs('user', '*', "where (User_OpenID = '" . $output['openid'] . "' or User_OpenID_Lp = '". $output['openid'] ."') and Users_ID = '". $Users_ID ."'");
                if ($userInfo) {
                    $DB->Set('user', ['unionid' => $output['unionid']], " where User_ID = " . $userInfo['User_ID']);
                }
            } else {
                if (empty($userInfo['User_OpenID_Lp'])) {
                    $DB->Set('user', ['User_OpenID_Lp' => $output['openid']], "where unionid = '". $output['unionid'] ."'");
                }
            }
        } else {
            $userInfo = $DB->GetRs('user', '*', "where (User_OpenID = '" . $output['openid'] . "' or User_OpenID_Lp = '". $output['openid'] ."') and Users_ID = '". $Users_ID ."'");
        }
        if ($userInfo) {
            $userInfo['User_OpenID'] = $output['openid'];
            exit(json_encode(['errorCode' => 0, 'msg' => '登录成功', 'data' => $userInfo]));
        } else {
            if (isset($_POST['unique_key'])) {
                $unique_key = htmlspecialchars($_POST['unique_key']);
            } else {
                $unique_key = time() . mt_rand(00000,99999);
            }
            $redis = new \Redis();
            $redis->connect('127.0.0.1');
            $redis->select(6);
            $redis->hSet('sessionKey', $unique_key, $output['session_key']);
            exit(json_encode(['errorCode' => 1, 'msg' => '需要注册', 'data' => $unique_key]));
        }
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '请求失败', 'data' => $output]));
    }
}

//用户注册
if ($act == 'reg') {
    $unique_key = htmlspecialchars($_POST['unique_key']);
    $ownerid = isset($_POST['ownerid']) ? (int)$_POST['ownerid'] : 0;
    $Root_ID = 0;
    $owerInfo = $DB->GetRs('user', '*', "where User_ID = " . $ownerid . " and Is_Distribute = 1");
    if ($owerInfo) {
        $Root_ID = $owerInfo['Root_ID'] == 0 ? $ownerid : $owerInfo['Root_ID'];
    } else {
        $ownerid = 0;
    }
    $redis = new \Redis();
    $redis->connect('127.0.0.1');
    $redis->select(6);
    $session_key = $redis->hGet('sessionKey', $unique_key);
    $rawData = $_POST['rawData'];
    $mysignature = sha1($rawData . $session_key);
    $signature = htmlspecialchars($_POST['signature']);
    if ($signature == $mysignature) {
        $encryptedData = $_POST['encryptedData'];
        $iv = $_POST['iv'];
        $decryptModel = new Lib();
        $decryptModel->sessionKey = $session_key;
        $decryptModel->appid = APPID_LP;
        $decrytedRes = $decryptModel->decryptData($encryptedData, $iv, $decrytedData);
        if ($decrytedRes == 0) {
            $userModel = new userinfo();
            $userModel->UsersID = $Users_ID;
            $userModel->DB = $DB;
            $lsUserInfo = json_decode($decrytedData, true);
            $userInfo = $userModel->registerbyopenid($ownerid, $Root_ID, 0, $lsUserInfo);
            if (!empty($userInfo)) {
                $redis->hDel('sessionKey', $unique_key);
                exit(json_encode(['errorCode' => 0, 'msg' => '登录成功', 'data' => $userInfo]));
            } else {
                exit(json_encode(['errorCode' => 2, 'msg' => '服务器忙,请稍后再试']));
            }
        } else {
            exit(json_encode(['errorCode' => 1, 'msg' => '解密失败']));
        }
    }
}
//---------------- 用户登录、注册 end ----------------

//---------------- 商城配置 start ----------------
//商城配置
if ($act == 'shop_config') {
    $shopConfig = $DB->GetRs('shop_config', '*', 'where Users_ID = "' . $Users_ID . '"');
    $userConfig = $DB->GetRs('user_config','*','where Users_ID= "' . $Users_ID . '"');
    $shopConfig['signflag'] = ($userConfig['IsSign'] && $userConfig['SignIntegral'] > 0) ? 1 : 0;
    if (!empty($shopConfig)) {
        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $shopConfig];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 商城配置 end ----------------

//---------------- 获取订单角标 start ----------------
if ($act == 'get_order_num') {
    if (!$_POST['User_ID']) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数User_ID']));
    }

    $order = [];
    $order['shop'] = [];
    $order['pintuan'] = [];
    // 待付款
    $order['shop']['waitpay'] = $DB->GetRs('user_order','count(Order_ID) as count','where Users_ID="' . $Users_ID . '" and User_ID=' . $_POST['User_ID'] . ' and Order_Status=1 and Order_Type="shop"')['count'];

    // 已付款
    $order['shop']['waitsend'] = $DB->GetRs('user_order','count(Order_ID) as count','where Users_ID="' . $Users_ID . '" and User_ID=' . $_POST['User_ID'] . ' and Order_Status=2 and Order_Type="shop"')['count'];

    // 已发货
    $order['shop']['waitconfirm'] = $DB->GetRs('user_order','count(Order_ID) as count','where Users_ID="' . $Users_ID . '" and User_ID=' . $_POST['User_ID'] . ' and Order_Status=3 and Order_Type="shop"')['count'];

    // 拼团待付款
    $order['pintuan']['waitpay'] = $DB->GetRs('user_order','count(Order_ID) as count','where Users_ID="' . $Users_ID . '" and User_ID=' . $_POST['User_ID'] . ' and Order_Status=1 and Order_Type="pintuan"')['count'];

    // 拼团已付款
    $order['pintuan']['waitsend'] = $DB->GetRs('user_order','count(Order_ID) as count','where Users_ID="' . $Users_ID . '" and User_ID=' . $_POST['User_ID'] . ' and Order_Status=2 and Order_Type="pintuan"')['count'];

    // 拼团已发货
    $order['pintuan']['waitconfirm'] = $DB->GetRs('user_order','count(Order_ID) as count','where Users_ID="' . $Users_ID . '" and User_ID=' . $_POST['User_ID'] . ' and Order_Status=3 and Order_Type="pintuan"')['count'];

    if (!empty($order['pintuan']) || !empty($order['shop']) ) {
        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $order];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 获取订单角标 end ----------------
//---------------- 签到送积分 start ----------------

if ($act == 'get_jifen') {
    if (!$_POST['User_ID']) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数User_ID']));
    }
    $userConfig = $DB->GetRs('user_config','*','where Users_ID= "' . $Users_ID . '"');
    $User_Integral = $DB->GetRs('user','User_Integral','where Users_ID="' . $Users_ID . '" and User_ID=' . $_POST['User_ID'])['User_Integral'];
    if (!$userConfig['IsSign'] || $userConfig['SignIntegral'] <= 0) {
        exit(json_encode(['errorCode' => 403, 'msg' => '该商家暂未开通签到送积分功能！']));
    }
    $record = [];
    $sql = "select count(Record_ID) as count from user_integral_record where Users_ID='{$Users_ID}' and User_ID={$_POST['User_ID']} and DATE_FORMAT(FROM_UNIXTIME(Record_CreateTime),'%Y-%m-%d')='" . date("Y-m-d",time()) . "'";
    $user_record = $DB->query($sql);
    while ($r = $DB->fetch_assoc()) {
        $record = $r;
    }
    if ($record['count'] > 0) {
        exit(json_encode(['errorCode' => 403, 'msg' => '您今天已经签到过了！']));
    } else {
        begin_trans();
        $data = array(
            'Record_Integral' => $userConfig['SignIntegral'],
            'Record_SurplusIntegral' => $User_Integral + $userConfig['SignIntegral'],
            'Operator_UserName' => '',
            'Record_Type' => 0,
            'Record_Description' => '每日签到领取积分',
            'Record_CreateTime' => time(),
            'Users_ID' => $Users_ID,
            'User_ID' => $_POST['User_ID'],
        );
        $set = $DB->Set('user',['User_Integral' => (int)($User_Integral + $userConfig['SignIntegral'])],' where Users_ID="' . $Users_ID . '" and User_ID=' . $_POST['User_ID']);
        $set_record = $DB->Add('user_integral_record',$data);

        if ($set !== false && $set_record !== false) {
            commit_trans();
            exit(json_encode(['errorCode' => 0, 'msg' => '签到成功,' . $userConfig['SignIntegral'] . '积分已经存入您的余额']));
        } else {
            back_trans();
            exit(json_encode(['errorCode' => 2, 'msg' => '签到失败']));
        }
    }

}

//---------------- 签到送积分 end ----------------

//---------------- 获取积分兑换产品 start ----------------
if ($act == 'get_jifen_pro') {
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $jifen_pro = $DB->GetAssoc('user_gift','*','where Users_ID="' . $Users_ID . '" limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize));
    if (!empty($jifen_pro)) {
        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $jifen_pro];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 获取积分兑换产品 end ----------------

//---------------- 获取积分兑换产品详情 start ----------------
if ($act == 'jifen_pro_detail') {
    if (!$_POST['Gift_ID']) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数Gift_ID']));
    }
    $jifen_pro = $DB->GetRs('user_gift','*','where Users_ID="' . $Users_ID . '" and Gift_ID=' . (int)$_POST['Gift_ID']);
    $jifen_pro['Gift_BriefDescription'] = conUrl($jifen_pro['Gift_BriefDescription'],$host_url);
    if (!empty($jifen_pro)) {
        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $jifen_pro];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 获取积分兑换产品详情 end ----------------

//---------------- 积分明细start ----------------
if ($act == 'get_jifen_mx') {
    if (!$_POST['User_ID']) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数User_ID']));
    }
    // 0: 全部 1：收入 2：支出
    $Type     = !empty($_POST['Type']) && in_array((int)$_POST['Type'], [0, 1, 2]) ? (int)$_POST['Type'] : 0;

    $condition = 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . (int)$_POST['User_ID'];

    if ($Type == 1) {   // 收入
        $condition .= ' and Record_Integral > 0';
    } else if ($Type == 2) {    // 支出
        $condition .= ' and Record_Integral < 0';
    }

    $totalCount = $DB->GetRs('user_integral_record', 'count(*) as count', $condition)['count'];

    $condition .= ' order by Record_ID desc';

    //分页
    $page      = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize  = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $jifen_mx = $DB->GetAssoc('user_integral_record', '*', $condition);

    $RecordType = ['每日签到', '手动修改', '获取积分', '使用积分', '使用优惠券获取积分', '积分兑换礼品', '', '大转盘消耗积分', '刮刮卡消耗积分'];

    if (!empty($jifen_mx)) {
        foreach ($jifen_mx as $k => $v) {
            $jifen_mx[$k]['Record_Type_desc'] = empty($RecordType[$v['Record_Type']]) ? '' : $RecordType[$v['Record_Type']];
        }
        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $jifen_mx, 'totalCount' => $totalCount], JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 积分明细 end ----------------

//---------------- 积分兑换产品订单列表  start ----------------
if ($act == 'get_jf_order') {
    if (!$_POST['User_ID']) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数User_ID']));
    }
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $DB->get("user_gift`,`user_gift_orders","user_gift.Gift_Name,user_gift.Gift_Integral,user_gift.Gift_ImgPath,user_gift.Gift_Shipping,user_gift.Gift_BriefDescription,user_gift_orders.Orders_ID,user_gift_orders.Orders_Status,user_gift_orders.Orders_Shipping,user_gift_orders.Orders_ShippingID,user_gift_orders.Orders_FinishTime","where user_gift.Gift_ID=user_gift_orders.Gift_ID and user_gift.Users_ID='".$Users_ID."' and user_gift_orders.User_ID=".$_POST['User_ID']." order by user_gift_orders.Orders_Status asc,user_gift_orders.Orders_ID desc limit " . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize));
    while($rsGift=$DB->fetch_assoc()){
        $jifen_order[] = $rsGift;
    }
    if (!empty($jifen_order)) {
        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $jifen_order];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 积分兑换产品订单列表 end ----------------

//---------------- 积分兑换产品提交 start ----------------
if ($act == 'duihuan_pro') {
    if (!$_POST['Gift_ID']) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数Gift_ID']));
    }
    if (!$_POST['User_ID']) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数User_ID']));
    }
    $UserID = $_POST['User_ID'];
    $Flag=true;
    $rsUser=$DB->GetRs("user","*","where Users_ID='".$Users_ID."' and User_ID=".$UserID);
    $rsGift = $DB->GetRs('user_gift','*','where Users_ID="' . $Users_ID . '" and Gift_ID=' . (int)$_POST['Gift_ID']);
    if (!empty($rsGift)) {
        if($rsUser['User_Integral']-$rsGift['Gift_Integral']>=0){
            if($rsGift['Gift_Qty']>=1){
                $Flag=$Flag&&$DB->Set("user_gift","Gift_Qty=Gift_Qty-1","where Users_ID='".$Users_ID."' and Gift_ID=".$_POST['Gift_ID']);
                //增加积分记录
                $Data=array(
                    'Record_Integral'=>-$rsGift['Gift_Integral'],
                    'Record_SurplusIntegral'=>$rsUser['User_Integral']-$rsGift['Gift_Integral'],
                    'Operator_UserName'=>'',
                    'Record_Type'=>5,
                    'Record_Description'=>'使用积分兑换礼品',
                    'Record_CreateTime'=>time(),
                    'Users_ID'=>$Users_ID,
                    'User_ID'=>$UserID
                );
                $Flag=$Flag&&$DB->Add('user_Integral_record',$Data);
                //减掉用户表用户积分
                $Flag=$Flag&&$DB->Set("user","User_Integral=User_Integral-".$rsGift['Gift_Integral'],"where Users_ID='".$Users_ID."' and User_ID=".$UserID);
                if(empty($rsGift['Gift_Shipping'])){
                    $Data=array(
                        "Users_ID"=>$Users_ID,
                        "User_ID"=>$UserID
                    );
                }else{
                    $AddressID=empty($_POST['AddressID'])?0:$_POST['AddressID'];

                    if(empty($_POST['AddressID'])){
                        $area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/areaduihuan.js');
                        $area_array = json_decode($area_json,TRUE);
                        $province_list = $area_array[0];

                        $Province_Name = $_POST['Province'];
                        $length = mb_strlen($Province_Name,'utf-8');
                        $Province_Name = mb_substr($Province_Name,0,$length,'utf-8');
                        $Address_Province = '';
                        foreach($province_list as $id=>$value){
                            if($value == $Province_Name){
                                $Address_Province = $id;
                                break;
                            }
                        }

                        $City_list = $area_array['0,'.$Address_Province];
                        $City_Name = $_POST['City'];
                        $Address_City = '';
                        foreach($City_list as $id=>$value){
                            if($value == $City_Name){
                                $Address_City = $id;
                                break;
                            }
                        }

                        $Area_list = $area_array['0,'.$Address_Province.','.$Address_City];
                        $Area_Name = $_POST['Area'];
                        $Address_Area = '';
                        foreach($Area_list as $id=>$value){
                            if($value == $Area_Name){
                                $Address_Area = $id;
                                break;
                            }
                        }

                        //增加
                        $Data=array(
                            "Address_Name"=>$_POST['Name'],
                            "Address_Mobile"=>$_POST["Mobile"],
                            "Address_Province"=>$Address_Province,
                            "Address_City"=>$Address_City,
                            "Address_Area"=>$Address_Area,
                            "Address_Detailed"=>$_POST["Detailed"],
                            "Users_ID"=>$Users_ID,
                            "User_ID"=>$UserID
                        );
                        $Flag=$Flag&&$DB->Add("user_address",$Data);
                    }else{
                        $rsAddress=$DB->GetRs("user_address","*","where Users_ID='".$Users_ID."' and User_ID='".$UserID."' and Address_ID='".$AddressID."'");
                        $Data=array(
                            "Address_Name"=>$rsAddress['Address_Name'],
                            "Address_Mobile"=>$rsAddress["Address_Mobile"],
                            "Address_Province"=>$rsAddress["Address_Province"],
                            "Address_City"=>$rsAddress["Address_City"],
                            "Address_Area"=>$rsAddress["Address_Area"],
                            "Address_Detailed"=>$rsAddress["Address_Detailed"],
                            "Users_ID"=>$Users_ID,
                            "User_ID"=>$UserID
                        );
                    }
                }
                $Data["Orders_Status"]=0;
                $Data["Gift_ID"]=$_POST['Gift_ID'];
                $Data["Orders_FinishTime"]=time();
                $Data["Orders_CreateTime"]=time();

//对于不需要物流信息的商品，在保存礼品订单时，将用户的手机号保存到数据库内 20160615 10:00 sxf
                if ($rsGift['Gift_Shipping'] == 0) {
                    $userInfo = $DB->GetRs('user', 'User_Mobile', 'WHERE User_ID=' . intval($UserID));

                    $Data["Address_Mobile"] = $userInfo['User_Mobile'];
                    unset($userInfo);
                }

                $Flag=$Flag&&$DB->Add('user_gift_orders',$Data);
                if($Flag){
                    exit(json_encode(['errorCode' => 0, 'msg' => '提交成功！']));
                }else{
                    exit(json_encode(['errorCode' => 1, 'msg' => '服务器繁忙，请稍后再试！']));
                }
            }else{
                exit(json_encode(['errorCode' => 1, 'msg' => '产品数量不足！']));
            }
        }else{
            exit(json_encode(['errorCode' => 1, 'msg' => '积分不足！']));
        }
    } else {
        exit(json_encode(['errorCode' => 404, 'msg' => '获取产品信息失败！']));
    }
}
//---------------- 积分兑换产品提交 end ----------------


//---------------- 模版获取 start ----------------
//联盟商家模版（首页）
if ($act == 'union_home') {
    $rsBiz_config = $DB->GetRs('biz_config', 'Pro_Skin_ID', 'where Users_ID = "' . $Users_ID . '"');
    $unionhome = $DB->GetRs("biz_union_program_home", '*', "where Users_ID = '" . $Users_ID . "' and Pro_Skin_ID = " . (int)$rsBiz_config['Pro_Skin_ID']);
    if (!empty($unionhome)) {
        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $unionhome];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//商城模版（购物）
if ($act == 'shop_home') {
    $shopConfig = $DB->GetRs('shop_config', 'Pro_Skin_ID', "where Users_ID = '". $Users_ID ."'");
    $shopHome = $DB->GetRs('shop_home_program', '*', "where Users_ID = '". $Users_ID ."' and Pro_Skin_ID = " . (int)$shopConfig['Pro_Skin_ID']);
    $shopHome['Home_Json'] = htmlspecialchars_decode($shopHome['Home_Json']);
    if (!empty($shopHome)) {
        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $shopHome];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//商家模版（商家店铺）
if ($act == 'biz_home') {
    if (isset($_POST['Biz_ID'])) {
        $biz = $DB->GetRs('biz', 'Skin_ID', 'where Users_ID = "' . $Users_ID . '" and Biz_ID = ' . (int)$_POST['Biz_ID']);
        $bizhome = $DB->GetRs("biz_home", "*", "where Users_ID = '". $Users_ID ."' and  Skin_ID = " . (int)$biz["Skin_ID"] . " and Biz_ID=" . (int)$_POST['Biz_ID']);
        if (!empty($bizhome)) {
            $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $bizhome];
            exit(json_encode($res, JSON_UNESCAPED_UNICODE));
        } else {
            exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
        }
    } else {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少参数Biz_ID']));
    }
}
//---------------- 模版获取 end ----------------

//---------------- 城市名称获取 start ----------------
if ($act == 'get_city') {
    $biz_list = $DB->GetAssoc('biz', 'b.City_ID, b.Biz_PrimaryLng, b.Biz_PrimaryLat, a.area_id, a.area_name', ' as b left join area as a on b.City_ID = a.area_id where b.Users_ID = "' . $Users_ID . '" and b.Is_Union = 1 and b.City_ID > 0 group by b.City_ID order by b.City_ID, b.Biz_ID desc');

    foreach ($biz_list as $k => $v) {
        //坐标转换  百度 转 高德（腾讯）
        $coordinate = bd_trans_gd($v['Biz_PrimaryLat'], $v['Biz_PrimaryLng']);
        $biz_list[$k]['Biz_PrimaryLat'] = $coordinate['lat'];
        $biz_list[$k]['Biz_PrimaryLng'] = $coordinate['lng'];
    }

    if (!empty($biz_list)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $biz_list]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 城市名称获取 end ----------------

//---------------- 分类获取 start ----------------
//行业分类
if ($act == 'union_cate') {
    $condition = 'where Users_ID = "' . $Users_ID . '"';
    if (isset($_POST['first_cate']) && $_POST['first_cate'] == 1) {     //请求一级分类
        $condition .= ' and Category_ParentID = 0';
    } else if (isset($_POST['Cate_ID']) && (int)$_POST['Cate_ID'] > 0) {    //请求指定一级分类下的二级分类
        $condition .= ' and Category_ParentID = ' . (int)$_POST['Cate_ID'];
    } else {
        //不传参数则获取所有分类信息
    }
    $condition .= ' order by Category_Index asc,Category_ID asc';

    $catlist = $DB->GetAssoc('biz_union_category', '*', $condition);
    if (!empty($catlist)) {
        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $catlist];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取产品分类
if ($act == 'pro_cate') {
    $condition = 'where Users_ID = "' . $Users_ID . '"';
    if (isset($_POST['first_cate']) && $_POST['first_cate'] == 1) {     //请求一级分类
        $condition .= ' and Category_ParentID = 0';
    } else if (isset($_POST['Cate_ID']) && (int)$_POST['Cate_ID'] > 0) {    //请求指定一级分类下的二级分类
        $Cate_ID = (int)$_POST['Cate_ID'];
        //$condition .= ' and Category_ParentID = ' . $Cate_ID;


        $res = $DB->GetRs('shop_category', 'Category_ParentID', 'where Category_ID = ' . $Cate_ID);
        if (empty($res)) {
            exit(json_encode(['errorCode' => 1, 'msg' => '分类获取失败']));
        }
        if ($res['Category_ParentID'] == 0) {
            $condition .= ' and (Category_ParentID = ' . $Cate_ID . ' or Category_ID = ' . $Cate_ID . ')';
        } else {
            $condition .= ' and (Category_ParentID = ' . $res['Category_ParentID'] . ' or Category_ID = ' . $res['Category_ParentID'] . ')';
        }
    } else {
        //不传参数则获取所有分类信息
    }
    $condition .= ' order by Category_Index asc,Category_ID asc';

    $catlist = $DB->GetAssoc('shop_category', '*', $condition);
    if (!empty($catlist)) {

        $list = [];
        foreach ($catlist as $k => $v) {
            if ($v['Category_ParentID'] == 0) {
                $list[$v['Category_ID']] = !empty($list[$v['Category_ID']]) ? array_merge($list[$v['Category_ID']], $v) : $v;
            } else {
                $list[$v['Category_ParentID']]['child'][] = $v;
            }
        }
        $list = array_merge($list, []);
        //$list = $catlist;

        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $list];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取商家产品分类
if ($act == 'biz_cate') {
    if (!isset($_POST['Biz_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数Biz_ID']));
    }
    $condition = 'where Users_ID = "' . $Users_ID . '" and Biz_ID='.(int)$_POST['Biz_ID'];
    if (isset($_POST['first_cate']) && $_POST['first_cate'] == 1) {     //请求一级分类
        $condition .= ' and Category_ParentID = 0';
    } else if (isset($_POST['Cate_ID']) && (int)$_POST['Cate_ID'] > 0) {    //请求指定一级分类下的二级分类
        $condition .= ' and Category_ParentID = ' . (int)$_POST['Cate_ID'];
    } else {
        //不传参数则获取所有分类信息
    }
    $condition .= ' order by Category_Index asc,Category_ID asc';
    $catlist = $DB->GetAssoc('biz_category', '*', $condition);
    if (!empty($catlist)) {
        $res = ['errorCode' => 0, 'msg' => '请求成功', 'data' => $catlist];
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//---------------- 分类获取 end ----------------


//---------------- 商家获取 start ----------------
//获取联盟商家
if ($act == 'get_biz') {
    //用户是否同意获取地理位置 0:不同意
    $coordinate = isset($_POST['coordinate']) && $_POST['coordinate'] == 0 ? false : true;

    //获取当前经纬度
    $current_lng = !empty($_POST['lng']) ? (float)$_POST['lng'] : 0;
    $current_lat = !empty($_POST['lat']) ? (float)$_POST['lat'] : 0;

    if ($coordinate && (empty($current_lng) || empty($current_lat))) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少参数lng或lat']));
    }

    $condition = ' where b.Is_Union = 1 and b.Users_ID="' . $Users_ID . '" and b.Biz_Status=0';

    //是否在首页显示
    if (isset($_POST['IndexShow']) && $_POST['IndexShow'] == 1) {
        $condition .= ' and b.Biz_IndexShow = 1';
    }

    //通过城市查询
    if (!empty($_POST['cityid'])) {
        $condition .= ' and b.Biz_City = ' . (int)$_POST['cityid'];
    }
    //行业分类查询
    if (isset($_POST['Cate_ID']) && (int)$_POST['Cate_ID'] > 0) {
        $condition .= ' and b.Category_ID like "%,' . (int)$_POST['Cate_ID'] . ',%"';
    }

    //搜索商家
    if (isset($_POST['Biz_Name'])) {
        $Biz_Name = cleanJsCss($_POST['Biz_Name']);
        $condition .= ' and b.Biz_Name like "%' . $Biz_Name . '%"';
    }

    $totalCount = $DB->GetRs('biz', 'count(b.Biz_ID) as count', ' as b ' . $condition)['count'];

    //因为一对多关系
    $condition .= ' group by b.Biz_ID';

    //判断商家排序
    $order_by = isset($_POST['order_by']) ? $_POST['order_by'] : '';
    if ($coordinate && $order_by == 'distance') {  //按距离最近排序
        $condition .= ' order by distance asc';
    } else if ($order_by == 'score') {  //按评分最高排序
        $condition .= ' order by commit_avg_score desc' . ($coordinate ? ', distance asc' : '') . ', b.Biz_Index asc';
    } else {
        $condition .= ' order by b.Biz_Index asc, commit_avg_score desc' . ($coordinate ? ', distance asc' : '');
    }

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    //组装查询sql
    $sql = 'select b.Biz_ID, b.Biz_Name, b.Biz_Logo, b.Biz_Address, b.Region_ID, b.Biz_PrimaryLng, b.Biz_PrimaryLat, count(c.Item_ID) as commit_count, avg(c.Score) as commit_avg_score';
    if ($coordinate) {
        //坐标转换  高德（腾讯）转 百度
        $current_coordinate = gd_trans_bd($current_lat, $current_lng);

        $sql .= ", round(6378.138*2*asin(sqrt(pow(sin(({$current_coordinate['lat']}*pi()/180-Biz_PrimaryLat*pi()/180)/2),2)+cos({$current_coordinate['lat']}*pi()/180)*cos(Biz_PrimaryLat*pi()/180)*pow(sin(({$current_coordinate['lng']}*pi()/180-Biz_PrimaryLng*pi()/180)/2),2)))*1000) as distance ";
    }
    $sql .= ' from biz as b left join user_order_commit as c on b.Biz_ID = c.Biz_ID and c.Status = 1 ' . $condition;
    $DB->query($sql);
    $biz_list = $DB->toArray();

    if (!empty($biz_list)) {
        foreach ($biz_list as $k => $v) {
            $biz_list[$k]['distance'] = !empty($v['distance']) ? $v['distance'] : 0;

            //坐标转换  百度 转 高德（腾讯）
            $coordinate = bd_trans_gd($v['Biz_PrimaryLat'], $v['Biz_PrimaryLng']);
            $biz_list[$k]['Biz_PrimaryLat'] = $coordinate['lat'];
            $biz_list[$k]['Biz_PrimaryLng'] = $coordinate['lng'];

            $biz_list[$k]['commit_avg_score'] = !empty($v['commit_avg_score']) ? number_format($v['commit_avg_score'], 2, '.', '') : 0;
            $biz_list[$k]['commit_count'] = !empty($v['commit_count']) ? (int)$v['commit_count'] : 0;
        }

        $Data = [
            'errorCode' => 0,
            'msg' => '查询成功',
            'data' => $biz_list,
            'totalCount' => !empty($totalCount) ? (int)$totalCount : 0
        ];
    } else {
        $Data = [
            'errorCode' => 2,
            'msg' => '暂无联盟商家'
        ];
    }
    echo json_encode($Data);
    exit;
}

//获取联盟商家详情
if ($act == 'get_biz_detail') {
    if (!isset($_POST['Biz_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少参数Biz_ID']));
    }

    $Biz_ID = (int)$_POST['Biz_ID'];

    $res = $DB->GetRs('biz', '*', 'where Users_ID = "' . $Users_ID . '" and Biz_ID = ' . $Biz_ID);
    if (!empty($res)) {
        //坐标转换  百度 转 高德（腾讯）
        $coordinate = bd_trans_gd($res['Biz_PrimaryLat'], $res['Biz_PrimaryLng']);
        $res['Biz_PrimaryLat'] = $coordinate['lat'];
        $res['Biz_PrimaryLng'] = $coordinate['lng'];
        
        exit(json_encode(['errorCode' => 0, 'msg' => '获取成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无此商家']));
    }
}
//---------------- 商家获取 end ----------------

//---------------- 商家门店获取 start ----------------
if ($act == 'get_biz_stores') {
    if (!isset($_POST['Biz_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少参数Biz_ID']));
    }

    $Biz_ID = (int)$_POST['Biz_ID'];

    $res = $DB->GetAssoc('biz_stores', '*', 'where Users_ID = "' . $Users_ID . '" and Biz_ID = ' . $Biz_ID);
    if (!empty($res)) {
        foreach ($res as $k => $v) {
            //坐标转换  百度 转 高德（腾讯）
            $coordinate = bd_trans_gd($v['Stores_PrimaryLat'], $v['Stores_PrimaryLng']);
            $res[$k]['Stores_PrimaryLat'] = $coordinate['lat'];
            $res[$k]['Stores_PrimaryLng'] = $coordinate['lng'];
        }
        exit(json_encode(['errorCode' => 0, 'msg' => '获取成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '商家暂无门店']));
    }
}
//---------------- 商家门店获取 end ----------------

//---------------- 商家优惠券获取 start ----------------
//优惠券列表
if ($act == 'get_coupon') {
    if (!isset($_POST['Biz_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少参数Biz_ID']));
    }

    $Biz_ID = (int)$_POST['Biz_ID'];

    $condition = 'where Users_ID = "' . $Users_ID . '" and Biz_ID = ' . $Biz_ID . ' and Is_Delete = 0 and Is_Deletes = 0 and Coupon_StartTime < ' . time() . ' and Coupon_EndTime > ' . time();

    $rsCoupon = $DB->GetAssoc('user_coupon', '*', $condition);

    $flag = 0;
    if (!empty($rsCoupon)) {
        if (isset($_POST['User_ID']) && !empty($_POST['User_ID'])) {
            // 获取用户优惠券
            $coupon_ids_arr = array_column($rsCoupon, 'Coupon_ID');
            $coupon_ids = implode(',', $coupon_ids_arr);
            $User_Coupon = $DB->GetAssoc('user_coupon_record', '*', 'where Users_ID="' . $Users_ID . '" and Biz_ID=' . $Biz_ID . ' and Coupon_ID in (' . $coupon_ids . ') and User_ID=' . (int)$_POST['User_ID']);

            if (!empty($User_Coupon)) {
                $User_CouID = array_column($User_Coupon, 'Coupon_ID');
                foreach ($rsCoupon as $k => $v) {
                    if (in_array($v['Coupon_ID'], $User_CouID)) {
                        $rsCoupon[$k]['flag'] = 1;
                    } else {
                        $rsCoupon[$k]['flag'] = 0;
                    }
                }
            } else {
                $flag = 0;
            }
        }
        exit(json_encode(['errorCode' => 0, 'msg' => '获取成功', 'data' => $rsCoupon]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//优惠券详情
if ($act == 'get_coupon_detail') {
    if (!isset($_POST['Biz_ID']) || !isset($_POST['Coupon_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少参数Biz_ID或Coupon_ID']));
    }

    $Biz_ID = (int)$_POST['Biz_ID'];
    $Coupon_ID = (int)$_POST['Coupon_ID'];

    $rsCoupon = $DB->GetRs('user_coupon', '*', 'where Coupon_ID = ' . $Coupon_ID . ' and Biz_ID = ' . $Biz_ID . ' and Is_Delete = 0 and Is_Deletes = 0 and Coupon_StartTime < ' . time() . ' and Coupon_EndTime > ' . time());

    if (!empty($rsCoupon)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '获取成功', 'data' => $rsCoupon]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取优惠券
if ($act == 'receive_coupon') {
    if (!isset($_POST['User_ID']) || !isset($_POST['Coupon_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少参数User_ID或Coupon_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];
    $Coupon_ID = (int)$_POST['Coupon_ID'];

    $UserCoupon = $DB->GetRs('user_coupon_record', '*', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID . ' and Coupon_ID = ' . $Coupon_ID);
    if(!empty($UserCoupon)){
        $Data = [
            'errorCode' => 1,
            'msg' => '您已经领取此优惠券，请勿重复领取！'
        ];
    }else {
        $rsCoupon = $DB->GetRs('user_coupon', '*', 'where Users_ID = "' . $Users_ID . '" and Coupon_ID = ' . $Coupon_ID);
        if (!empty($rsCoupon)) {
            $rsUser = $DB->GetRs('user', '*', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID);
            if ($rsCoupon["Coupon_UserLevel"] <= $rsUser["User_Level"]) {
                $Data = [
                    'Coupon_ID' => $rsCoupon['Coupon_ID'],
                    'Coupon_UsedTimes' => $rsCoupon['Coupon_UsedTimes'],
                    'Coupon_UseArea' => $rsCoupon['Coupon_UseArea'],
                    'Coupon_UseType' => $rsCoupon['Coupon_UseType'],
                    'Coupon_Condition' => $rsCoupon['Coupon_Condition'],
                    'Coupon_Discount' => $rsCoupon['Coupon_Discount'],
                    'Coupon_Cash' => $rsCoupon['Coupon_Cash'],
                    'Coupon_StartTime' => $rsCoupon['Coupon_StartTime'],
                    'Coupon_EndTime' => $rsCoupon['Coupon_EndTime'],
                    'Record_CreateTime' => time(),
                    'Users_ID' => $Users_ID,
                    'User_ID' => $User_ID,
                    'Biz_ID' => $rsCoupon['Biz_ID']
                ];
                $Flag = $DB->Add('user_coupon_record', $Data);
                if ($Flag) {
                    $Data = [
                        'errorCode' => 0,
                        'msg' => '领取成功'
                    ];
                } else {
                    $Data = [
                        'errorCode' => 1,
                        'msg' => '领取失败'
                    ];
                }
            } else {
                $Data = [
                    'errorCode' => 1,
                    'msg' => '你没有权限领取该优惠券'
                ];
            }
        } else {
            $Data = [
                'errorCode' => 1,
                'msg' => '无此优惠券, 请勿非法操作'
            ];
        }
    }
    exit(json_encode($Data, JSON_UNESCAPED_UNICODE));
}

//用户已领取优惠券
if ($act == 'user_coupon') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }if (!isset($_POST['Biz_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数Biz_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];
    $Biz_ID = (int)$_POST['Biz_ID'];

    $res = $DB->GetAssoc('user_coupon_record', 'u.*,c.Coupon_Subject', " as u left join user_coupon as c on u.Coupon_ID=c.Coupon_ID where u.User_ID=" . $User_ID . " and u.Users_ID='" . $Users_ID . "' and u.Coupon_UseArea=1 and (u.Coupon_UsedTimes=-1 or u.Coupon_UsedTimes>0) and u.Coupon_StartTime<=" . time() . " and u.Coupon_EndTime>=" . time()." and u.Biz_ID=".$Biz_ID);
    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '获取成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 商家优惠券获取 end ----------------

//---------------- 产品获取 start ----------------
//获取产品列表（产品详情）
if ($act == 'get_pro') {

    $condition = ' as p left join biz as b on p.Biz_ID = b.Biz_ID where b.Biz_Status=0 and';

    //是否有过滤联盟商的产品
    if (false) {
        if ($_POST['Is_Union'] == 1) {  //联盟商家
            $condition .= ' b.Is_Union = 1 and ';
        } else {    //普通商家
            $condition .= ' b.Is_Union = 0 and ';
        }
    }

    if (!empty($_POST['cityid'])) {
        $condition .= ' b.Biz_City = ' . (int)$_POST['cityid'] . ' and ';
    }

    $condition .= ' p.Users_ID = "' . $Users_ID . '" and p.Products_Status = 1 and p.Products_SoldOut = 0';

    if (isset($_POST['Products_ID'])) {
        if ($_POST['Products_ID'] == (int)$_POST['Products_ID']) {  //单个产品
            $condition .= ' and p.Products_ID = ' . (int)$_POST['Products_ID'];
        } else {    //多个产品（1,2,3）
            $condition .= ' and p.Products_ID in (' . trim($_POST['Products_ID'], ',') . ')';
        }
    }

    //商家产品
    if (isset($_POST['Biz_ID'])) {
        $condition .= ' and p.Biz_ID = ' . (int)$_POST['Biz_ID'];
    }

    //根据产品二级分类查询产品
    if (isset($_POST['Cate_ID']) && (int)$_POST['Cate_ID'] > 0) {
        $Cate_ID = (int)$_POST['Cate_ID'];

        $res = $DB->GetRs('shop_category', 'Category_ParentID', 'where Category_ID = ' . $Cate_ID);

        if (!empty($res)) {
            if ($res['Category_ParentID'] == 0) {
                $condition .= ' and p.Father_Category = ' . $Cate_ID;
            } else {
                $condition .= ' and p.Products_Category = ' . $Cate_ID;
            }
        }
    }

    //根据商家分类查询产品
    if (isset($_POST['Biz_Cate_ID']) && (int)$_POST['Biz_Cate_ID'] > 0) {
        $condition .= ' and p.Products_BizCategory = ' . (int)$_POST['Biz_Cate_ID'];
    }

    //是否为拼团产品
    if (isset($_POST['pintuan_flag']) && $_POST['pintuan_flag'] == 1) {
        $condition .= ' and p.pintuan_flag = 1 and p.pintuan_people >= ' . 2 . ' and p.pintuan_start_time < ' . time() . ' and p.pintuan_end_time > ' . time();
    }

    //是否为新品
    if (isset($_POST['Is_New']) && $_POST['Is_New'] == 1) {
        $condition .= ' and p.Products_BizIsNew = 1';
    }

    //是否为热卖推荐
    if (isset($_POST['Is_Hot']) && $_POST['Is_Hot'] == 1) {
        $condition .= ' and p.Products_IsHot = 1';
    }

    //是否为推荐
    if (isset($_POST['Is_Rec']) && $_POST['Is_Rec'] == 1) {
        $condition .= ' and p.Products_BizIsRec = 1';
    }

    //是否推荐到首页
    if (isset($_POST['Is_Shop_Rec']) && $_POST['Is_Shop_Rec'] == 1) {
        $condition .= ' and p.Products_IsRecommend = 1';
    }

    //是否参与限时抢购
    if (isset($_POST['flashsale_flag']) && $_POST['flashsale_flag'] == 1) {
        $condition .= ' and p.flashsale_flag = 1';
    }

    //超级团产品
    if (isset($_POST['super_team']) && $_POST['super_team'] == 1) {
        $condition .= ' and pintuan_flag = 1 and pintuan_end_time > ' . time() . ' and pintuan_people >= 5 and pintuan_pricex <= Products_PriceX * 0.5';  //如果人数超过5个,拼团价小于现价的50%,则加入超级团
    }
    //9.9特价产品
    if (isset($_POST['Is_Spe']) && $_POST['Is_Spe'] == 1) {
        $condition .= ' and ((Products_PriceX >= 8 and Products_PriceX <= 12) or (pintuan_pricex >= 8 and pintuan_pricex <= 12))';
    }

    //搜索产品
    if (!empty($_POST['Products_Name'])) {
        $Products_Name = cleanJsCss($_POST['Products_Name']);
        $condition .= ' and p.Products_Name like "%' . $Products_Name . '%"';
    }

    $totalCount = $DB->GetRs('shop_products', 'count(p.Products_ID) as count', $condition)['count'];

    //排序
    $condition .= ' order by Products_Index asc, Products_CreateTime desc';

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    //判断系统是否开启限时抢购活动，并且在使用方范围内
    $active_info = $DB->GetRs('active', 'Active_ID', 'where Users_ID = "' . $Users_ID . '" and Status = 1 and Type_ID = 5 and starttime < ' . time() . ' and stoptime > ' . time());
    $flashsale = !empty($active_info) ? 1 : 0;

    //判断在查询限时抢购条件时系统是否有限时抢购活动
    if (isset($_POST['flashsale_flag']) && $_POST['flashsale_flag'] == 1) {
        if ($flashsale) {
            $rsPro = $DB->GetAssoc('shop_products', 'p.*, p.Is_BizStores as pro_Is_Stores, b.Is_Union, b.Biz_Name, b.Biz_Logo, b.Biz_Ext, b.Is_BizStores as biz_Is_Stores', $condition);
        } else {
            $rsPro = [];
        }
    } else {
        $rsPro = $DB->GetAssoc('shop_products', 'p.*, p.Is_BizStores as pro_Is_Stores, b.Is_Union, b.Biz_Name, b.Biz_Logo, b.Biz_Ext, b.Is_BizStores as biz_Is_Stores', $condition);
    }

    if (!empty($rsPro)) {
        $user_curagio = 0;
        $user_level_name = '';
        if (isset($_POST['User_ID'])) {
            $rsUserConfig = $DB->GetRs('user_config', 'UserLevel', 'where Users_ID = "' . $Users_ID . '"');
            $rsUser = $DB->GetRs('user', 'User_Level', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . (int)$_POST['User_ID']);
            $user_level_config = !empty($rsUserConfig['UserLevel']) ? json_decode($rsUserConfig['UserLevel'], true) : '';

            if ($rsUser['User_Level'] != 0) {
                $user_level = !empty($user_level_config[$rsUser['User_Level']]) ? $user_level_config[$rsUser['User_Level']] : [];
                $user_curagio = !empty($user_level['Discount']) ? $user_level['Discount'] : 0;
                $user_level_name = !empty($user_level['Name']) ? $user_level['Name'] : 0;
            }
        }
        foreach ($rsPro as $k => $v) {
            $rsPro[$k]['Products_Description'] = conUrl(stripslashes(htmlspecialchars_decode($v['Products_Description'])), $host_url);

            //获取会员身份和优惠
            $rsPro[$k]['user_curagio'] = $user_curagio;
            $rsPro[$k]['user_level_name'] = $user_level_name;

            //产品限时抢购状态
            $rsPro[$k]['flashsale_flag'] = $rsPro[$k]['flashsale_flag'] && $flashsale;
        }

        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $rsPro, 'totalCount' => !empty($totalCount) ? (int)$totalCount : 0]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无产品']));
    }
}

//产品详情
/*if ($act == 'get_pro_detail') {
    if (!isset($_POST['Products_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少参数Products_ID']));
    }
    $Products_ID = (int)$_POST['Products_ID'];
    $productsInfo = $DB->GetRs('shop_products', 'p.*, b.Is_Union, b.Biz_Name, b.Biz_Logo, b.Biz_Ext', ' as p left join biz as b on p.Biz_ID = b.Biz_ID where p.Users_ID = "' . $Users_ID . '" and p.Products_ID = ' . $Products_ID . ' and p.Products_Status = 1');
    if (!empty($productsInfo)) {
        $productsInfo['Products_Description'] = conUrl(stripslashes(htmlspecialchars_decode($productsInfo['Products_Description'])), $host_url);

        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $productsInfo]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无产品']));
    }
}*/
//---------------- 产品获取 end ----------------

//---------------- 收货地址操作 start ----------------
//获取收货地址
if ($act == 'get_address') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数User_ID']));
    }
    $User_ID = (int)$_POST['User_ID'];
    $AddressID = isset($_POST['Address_ID']) ? (int)$_POST['Address_ID'] : 0;
    if ($AddressID) {
        $addressInfo = $DB->GetAssoc('user_address', '*', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID . ' and Address_ID = ' . $AddressID);
    } else {
        $addressInfo = $DB->GetAssoc('user_address', '*', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID);
    }
    if (!empty($addressInfo)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $addressInfo]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '该会员暂无收货地址!']));
    }
}

//编辑收货地址
if ($act == 'edit_address') {
    if (!isset($_POST['Address_ID']) || !isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数Address_ID或User_ID']));
    }

    $data = $_POST;
    $AddressID = (int)$_POST['Address_ID'];
    $User_ID = (int)$_POST['User_ID'];
    $Address_Is_Default = !empty($_POST['Address_Is_Default']) ? 1 : 0;

    if ($Address_Is_Default) {
        $DB->Set('user_address', ['Address_Is_Default' => 0], 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID);
    }

    $data = [
        'Users_ID' => $Users_ID,
        'User_ID' => $User_ID,
        'Address_Name' => $_POST['Address_Name'],
        'Address_Mobile' => $_POST['Address_Mobile'],
        'Address_Province' => $_POST['Address_Province'],
        'Address_City' => $_POST['Address_City'],
        'Address_Area' => $_POST['Address_Area'],
        'Address_Detailed' => $_POST['Address_Detailed'],
        'Address_Is_Default' => $Address_Is_Default
    ];
    
    $flag = $DB->Set('user_address', $data, 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID . ' and Address_ID = ' . $AddressID);
    if ($flag) {
        exit(json_encode(['errorCode' => 0, 'msg' => '编辑成功']));
    } else {
        exit(json_encode(['errorCode' => 1, 'msg' => '编辑失败']));
    }
}

//新增收货地址
if ($act == 'add_address') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];
    $Address_Is_Default = !empty($_POST['Address_Is_Default']) ? 1 : 0;

    if ($Address_Is_Default) {
        $DB->Set('user_address', ['Address_Is_Default' => 0], 'where Users_ID = "' . $Users_ID . '" and  User_ID = ' . $User_ID);
    }

    $data = [
        'Users_ID' => $Users_ID,
        'User_ID' => $User_ID,
        'Address_Name' => $_POST['Address_Name'],
        'Address_Mobile' => $_POST['Address_Mobile'],
        'Address_Province' => $_POST['Address_Province'],
        'Address_City' => $_POST['Address_City'],
        'Address_Area' => $_POST['Address_Area'],
        'Address_Detailed' => $_POST['Address_Detailed'],
        'Address_Is_Default' => $Address_Is_Default
    ];

    $flag = $DB->Add('user_address', $data);
    if ($flag) {
        exit(json_encode(['errorCode' => 0, 'msg' => '新增成功']));
    } else {
        exit(json_encode(['errorCode' => 1, 'msg' => '新增失败']));
    }
}

//删除收货地址
if ($act == 'del_address') {
    if (!isset($_POST['Address_ID']) || !isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数Address_ID或User_ID']));
    }

    $AddressID = (int)$_POST['Address_ID'];
    $User_ID = (int)$_POST['User_ID'];
    $flag = $DB->Del('user_address', 'Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID . ' and Address_ID = ' . $AddressID);
    if ($flag) {
        exit(json_encode(['errorCode' => 0, 'msg' => '删除成功']));
    } else {
        exit(json_encode(['errorCode' => 1, 'msg' => '删除失败']));
    }
}
//---------------- 收货地址操作 end ----------------

//------------------获取活动信息 start -------------

if ($act == 'get_active') {
    if (!empty($_POST['Type_ID'])) {
        if ($_POST['Type_ID'] == 5) {
            $flashsale = $DB->GetRs('active', '*', "where Users_ID = '". $Users_ID ."' and Status = 1 and Type_ID=".(int)$_POST['Type_ID']);
            if (empty($flashsale)) {
                exit(json_encode(['errorCode' => 404, 'msg' => '未找到此活动']));
            }
            if ($flashsale['stoptime'] < time()) {
                exit(json_encode(['errorCode' => 500, 'msg' => '该活动已结束!']));
            }
            $day = intval(($flashsale['stoptime'] - time()) / 86400);   //剩余天数
            $hour = intval((($flashsale['stoptime'] - time()) % 86400) / 3600);  //剩余小时
            $minite = intval(((($flashsale['stoptime'] - time()) % 86400) % 3600) / 60);   //剩余分钟
            $second = intval(((($flashsale['stoptime'] - time()) % 86400) % 3600) % 60);   //剩余秒
            $return = ['Active_Name' => $flashsale['Active_Name'], 'day' => $day, 'hour' => $hour, 'minite' => $minite, 'second' => $second, 'imgurl'=>$flashsale['imgurl']];
            exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $return]));
        }else{
            $return = $DB->GetRs('active', '*', "where Users_ID = '". $Users_ID ."' and Status = 1 and Type_ID=".(int)$_POST['Type_ID']);
            if (empty($return)) {
                exit(json_encode(['errorCode' => 404, 'msg' => '未找到此活动']));
            }
            exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $return]));
        }
    } else {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要的参数Type_ID']));
    }
}

//------------------获取活动信息 end ---------------


//---------------- 订单操作 start ----------------
//获取订单
if ($act == 'get_order') {
    if (!isset($_POST['Order_Type']) || !isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数Order_Type或User_ID']));
    }

    $Order_Type = $_POST['Order_Type'];
    $User_ID = (int)$_POST['User_ID'];

    $condition = ' as o left join pintuan_teamdetail as ptd on o.Order_ID = ptd.order_id left join pintuan_team as pt on ptd.teamid = pt.id where o.Users_ID = "' . $Users_ID . '" and o.User_ID = ' . $User_ID . ' and o.Order_Type = "' . $Order_Type . '"';
    if (isset($_POST['Order_ID'])) {
        $condition .= ' and o.Order_ID = ' . (int)$_POST['Order_ID'];
    }
    if (isset($_POST['Order_Status'])) {
        $condition .= ' and o.Order_Status = ' . (int)$_POST['Order_Status'];
    }

    //获取订单总数
    $totalCount = $DB->GetRs('user_order', 'count(o.Order_ID) as count', $condition)['count'];

    if ($Order_Type == 'offline_st') {
        $condition .= ' order by o.Order_CreateTime desc';
    } else {
        $condition .= ' order by o.Order_Status, o.Order_ID desc';
    }

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $rsOrder = $DB->GetAssoc('user_order', 'o.*, pt.teamstatus', $condition);

    if (!empty($rsOrder)) {
        foreach ($rsOrder as $k => $v) {
            $rsOrder[$k]['back_order'] = $DB->GetAssoc('user_back_order', '*', 'where Order_ID = ' . $v['Order_ID']);
        }
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $rsOrder, 'totalCount' => $totalCount]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无订单']));
    }
}

//获取订单详情
if ($act == 'get_order_detail') {
    if (!isset($_POST['User_ID']) || !isset($_POST['Order_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID或Order_ID']));
    }

    $Order_ID = $_POST['Order_ID'];
    $User_ID = (int)$_POST['User_ID'];

    if (strpos($Order_ID, 'PRE') !== false) {   //多商家订单
        $res = $DB->GetRs('user_pre_order', 'total, createtime, orderids, status', 'where usersid = "' . $Users_ID . '" and userid = ' . $User_ID . ' and pre_sn = "' . $Order_ID . '"');

        $res_order = $DB->GetAssoc('user_order', '*', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID . ' and Order_ID in (' . $res['orderids'] . ')');

        $Order_Status = $res['status'];
        foreach ($res_order as $k => $v) {
            if (isset($_POST['order_action']) && $_POST['order_action'] == 'confirm' && $v['Order_Status'] != 3) {
                $Order_Status = 0;
                break;
            } else if ($v['Order_Status'] != 1) {
                $Order_Status = 0;
                break;
            }
        }

        $rsOrder = [
            'Order_ID' => $Order_ID,
            'Order_TotalPrice' => $res['total'],
            'Order_Status' => $Order_Status,
            'Create_Time' => $res['createtime']
        ];
    } else {
        $rsOrder = $DB->GetRs('user_order', '*', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID . ' and Order_ID = ' . $Order_ID);
    }

    if (!empty($rsOrder)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $rsOrder]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无订单']));
    }
}

//获取退款单
if ($act == 'get_back_order') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数OUser_ID']));
    }

    $Back_Type = empty($_POST['Back_Type']) ? '' : $_POST['Back_Type'];
    $User_ID = (int)$_POST['User_ID'];

    $condition = ' where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID;

    // 根据退款id查询
    if (!empty($_POST['Back_ID'])) {
        $condition .= ' and Back_ID = ' . (int)$_POST['Back_ID'];
    }

    // 退款、退货类型
    if (!empty($Back_Type)) {
        $condition .= ' and Back_Type = "' . $Back_Type . '"';
    }

    // 根据订单id查询
    if (!empty($_POST['Order_ID'])) {
        $condition .= ' and Order_ID = ' . (int)$_POST['Order_ID'];
    }
    // 根据订单状态查询
    if (isset($_POST['Order_Status'])) {
        $condition .= ' and Order_Status = ' . (int)$_POST['Order_Status'];
    }

    //获取订单总数
    $totalCount = $DB->GetRs('user_back_order', 'count(Back_ID) as count', $condition)['count'];

    $condition .= ' order by Back_UpdateTime desc, Back_ID desc';

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $rsBackOrder = $DB->GetAssoc('user_back_order', '*', $condition);

    if (!empty($rsOrder)) {
        foreach ($rsBackOrder as $k => $v) {
            $rsBackOrder[$k]['back_detail'] = $DB->GetAssoc('user_back_order_detail', '*', 'where backid = ' . $v['Back_ID']);
        }
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $rsBackOrder, 'totalCount' => $totalCount]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无订单']));
    }
}
//---------------- 订单操作 end ----------------

//---------------- 评论获取 start ----------------
//获取评论列表
if ($act == 'get_commit') {
    $condition = ' as c left join user as u on c.User_ID = u.User_ID left join user_order as o on c.Order_ID = o.Order_ID where c.Users_ID = "' . $Users_ID . '" and c.Status = 1';

    //产品评论
    if (isset($_POST['Biz_ID'])) {
        $condition .= ' and c.Biz_ID = ' . (int)$_POST['Biz_ID'];
    }

    //产品评论
    if (isset($_POST['Products_ID'])) {
        $condition .= ' and c.Product_ID = ' . (int)$_POST['Products_ID'];
    }

    //评论类型
    if (isset($_POST['MID'])) {
        $condition .= ' and c.MID = "' . $_POST['MID'] . '"';
    }

    $totalCount = $DB->GetRs('user_order_commit', 'count(c.Item_ID) as count', $condition)['count'];

    $condition .= ' order by c.CreateTime desc';

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $res = $DB->GetAssoc('user_order_commit', 'c.Product_ID, c.Score, c.Note, c.ImgPath, c.CreateTime, u.User_Mobile, u.User_NickName, u.User_HeadImg, o.Order_CartList', $condition);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $res, 'totalCount' => $totalCount]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 评论获取 start ----------------

//---------------- 系统公告获取 start ----------------
//系统公告列表获取
if ($act == 'get_notices') {
    $res = $DB->GetAssoc('shop_notices', '*', 'where Users_ID = "' . $Users_ID . '" order by Notice_CreateTime desc');

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//系统公告详情获取
if ($act == 'get_notices_detail') {
    if (!isset($_POST['Notice_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数Notice_ID']));
    }
    $Notice_ID = isset($_POST['Notice_ID']) ? (int)$_POST['Notice_ID'] : 0;

    $res = $DB->GetRs('shop_notices', '*', 'where Users_ID = "' . $Users_ID . '" and Notice_ID = ' . $Notice_ID);
    if (!empty($res)) {
        $res['Notice_Content'] = conUrl(stripslashes(htmlspecialchars_decode($res['Notice_Content'])), $host_url);
        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 系统公告获取 end ----------------

//---------------- 会员消息获取 start ----------------
if ($act == 'get_msg') {
    $condition = 'where Users_ID = "' . $Users_ID . '"';

    if (isset($_POST['Message_ID'])) {
        $condition .= ' and Message_ID = ' . (int)$_POST['Message_ID'];
    }

    $totalCount = $DB->GetRs('user_message', 'count(Message_ID) as count', $condition)['count'];

    $condition .= ' order by Message_CreateTime desc';

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $res = $DB->GetAssoc('user_message', '*', $condition);
    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $res, 'totalCount' => $totalCount]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

/*if ($act == 'get_msg_detail') {
    if (!isset($_POST['Message_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数Message_ID']));
    }
    $res = $DB->GetRs('user_message', '*', 'where Users_ID = "' . $Users_ID . '" and Message_ID = ' . (int)$_POST['Message_ID']);
    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}*/
//---------------- 会员消息获取 end ----------------

//---------------- 用户信息 start ----------------
if ($act == 'get_location_address') {
    //获取当前经纬度
    $current_lng = !empty($_POST['lng']) ? (float)$_POST['lng'] : 0;
    $current_lat = !empty($_POST['lat']) ? (float)$_POST['lat'] : 0;

    if (empty($current_lng) || empty($current_lat)) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少参数lng或lat']));
    }

    //坐标转换  高德（腾讯）转 百度
    $coordinate = gd_trans_bd($current_lat, $current_lng);

    //获取详细的地理位置信息
    $res = getBdPositionAddress($coordinate, $ak_baidu);

    $res = !empty($res) ? json_decode($res, true) : '';
    if (isset($res['status']) && $res['status'] == 0 && !empty($res['result'])) {
        if (!empty($res['result']['addressComponent']['city'])) {
            $rsCity = $DB->GetRs('area', 'area_id', 'where area_name = "' . $res['result']['addressComponent']['city'] . '"');
        }
        $res['result']['o2o_city_id'] = !empty($rsCity['area_id']) ? $rsCity['area_id'] : 0;
        //$address = $res['result']['formatted_address'];
        //$address .= !empty($res['result']['sematic_description']) ? $res['result']['sematic_description'] : '';
        exit(json_encode(['errorCode' => 0, 'msg' => '请求成功', 'data' => $res['result']]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 用户信息 end ----------------

//---------------- redis操作 start ----------------
//获取redis中产品
if ($act == 'get_redis') {
    if (!isset($_POST['User_ID']) || !isset($_POST['cart_key'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID或cart_key']));
    }

    $User_ID = (int)$_POST['User_ID'];
    $cart_key = $_POST['cart_key'];

    $myRedis = new Myredis($DB, 7);
    $redisCart = $myRedis->getCartListValue($Users_ID . $cart_key, $User_ID);
    $CartList = !empty($redisCart) ? json_decode($redisCart, true) : [];

    //获取会员优惠
    $user_curagio = get_user_curagio($Users_ID, $User_ID);

    if (!empty($CartList)) {
        //产品数量
        $pro_count = $total_price = 0;
        $biz_list = [];
        foreach ($CartList as $biz_id => $pro_info) {
            $qty = $biz_total_price = 0;
            foreach ($pro_info as $pro_id => $value) {
                foreach ($value as $attr_id => $attr_val) {
                    $num = !empty($attr_val['Qty']) ? (int)$attr_val['Qty'] : 0;

                    //商家产品数量
                    $qty += $num;

                    //产品单价
                    if (empty($attr_val['Property'])) { //无属性
                        $shu_price = $attr_val['ProductsPriceX'];
                    } else {    //有属性
                        $shu_price = $attr_val['Property']['shu_pricesimp'];
                    }

                    //计算优惠
                    if (isset($user_curagio) && !empty($user_curagio) && $_POST['cart_key']!= 'PTCartList') {
                        $shu_price = $shu_price * (1 - $user_curagio / 100);
                        if($shu_price < 0.01){
                            $shu_price = 0.01;
                        }
                        $shu_price = floor($shu_price * 100) / 100;
//                        $shu_price = getFloatValue($shu_price, 2);

                    }
                    //价格计算
                    $biz_total_price += (float)$shu_price * $num;
                }
            }
            $pro_count += $qty;
            $total_price += $biz_total_price;

            $res = $DB->GetRs('biz', 'Biz_ID, Is_BizStores, Biz_Address, Biz_PrimaryLng, Biz_PrimaryLat, Biz_Phone, Biz_Name, Biz_Logo', 'where Users_ID = "' . $Users_ID . '" and Biz_ID = ' . $biz_id);
            $biz_list[$biz_id] = array_merge($res, ['qty' => $qty, 'biz_total_price' => $biz_total_price]);
        }

        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => json_encode($CartList, JSON_UNESCAPED_UNICODE), 'pro_count' => $pro_count, 'total_price' => $total_price, 'biz_list' => $biz_list, 'user_curagio' => $user_curagio]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- redis操作 end ----------------


//---------------- 参团操作 start ----------------
//获取参团列表
if ($act == 'get_pintuan_team') {
    if (!isset($_POST['Products_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数Products_ID']));
    }

    $Products_ID = (int)$_POST['Products_ID'];

    $rsProducts = $DB->GetRs('shop_products', '*', 'where Users_ID = "' . $Users_ID . '" and Products_Status = 1 and Products_SoldOut = 0 and Products_ID = ' . $Products_ID);
    if (empty($rsProducts)) {
        exit(json_encode(['errorCode' => 1, 'msg' => '此产品已下架或被删除']));
    }

    //查询条件
    $condition = 'where users_id = "' . $Users_ID . '" and productid = ' . $Products_ID . ' and teamstatus = 0 and teamnum > 0 and teamnum < ' . $rsProducts['pintuan_people'] . ' and addtime > ' . (time() - 24 * 60 * 60);

    if (!empty($_POST['User_ID'])) {
        $condition .= ' and userid != ' . (int)$_POST['User_ID'];
    }

    /*if (!empty($_POST['teamid'])) {
        $condition .= ' and id != ' . (int)$_POST['teamid'];
    }*/
    $count = $DB->GetAssoc('pintuan_team', 'count(id)', $condition)[0]['count(id)'];
    $condition .= ' order by id';
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    //小伙伴正在拼团   除去 登录用户已参加的团
    $existTeam = $DB->GetAssoc('pintuan_team', 'id, userid, teamstatus, teamnum, addtime', $condition);


    if (!empty($existTeam) && count($existTeam) > 0) {
        foreach ($existTeam as $teamKey => $team) {

            /*if (($team['addtime'] + 24*60*60 - time()) <= 0) {    //拼团时间已过，不能再拼团
                unset($existTeam[$teamKey]);
                continue;
            }*/
            if (!empty($UserID)) {
                $join = $DB->GetRs('pintuan_teamdetail', 'id, userid', 'where teamid = ' . $team['id'] . ' and userid = ' . $UserID);
                if (!empty($join)) {
                    unset($existTeam[$teamKey]);
                    continue;
                }
            }
            $userInfo = $DB->GetRs('user', 'User_HeadImg, User_NickName, User_Mobile', 'where User_ID = ' . $team['userid']);
            $existTeam[$teamKey]['user'] = [
                'User_HeadImg' => !empty($userInfo['User_HeadImg']) ? $userInfo['User_HeadImg'] : '',
                'User_NickName' => !empty($userInfo['User_NickName']) ? $userInfo['User_NickName'] : substr_replace($userInfo['User_Mobile'], '***', 6, 3)
            ];
        }
    }

    //$existTeam = !empty($existTeam) ? array_slice($existTeam, 0, 2) : [];
    if (!empty($existTeam)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $existTeam, 'totalCount'=>$count]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取参团详情
if ($act == 'get_pintuan_team_detail') {
    if (!isset($_POST['Products_ID']) && !isset($_POST['teamid'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数Products_ID或teamid']));
    }

    $Products_ID = (int)$_POST['Products_ID'];
    $teamid = (int)$_POST['teamid'];

    $rsTeam = $DB->GetRs('pintuan_team', '*', 'where users_id = "' . $Users_ID . '" and productid = ' . $Products_ID . ' and id = ' . $teamid);

    if (empty($rsTeam)) {
        exit(json_encode(['errorCode' => 1, 'msg' => '该拼团不存在']));
    } else if ($rsTeam['teamstatus'] != 0) {
        exit(json_encode(['errorCode' => 1, 'msg' => '该拼团已拼团成功']));
    } else if (($rsTeam['addtime'] + 24*60*60 - time()) <= 0) {
        exit(json_encode(['errorCode' => 1, 'msg' => '该拼团已过期']));
    }

    $rsTeamDetail = $DB->GetAssoc('pintuan_teamdetail', 'ptd.*, u.User_Mobile, u.User_NickName, u.User_HeadImg', ' as ptd left join user as u on ptd.userid = u.User_ID where ptd.teamid = ' . $teamid);

    $data = [];
    if (!empty($rsTeamDetail)) {
        foreach ($rsTeamDetail as $k => $v) {
            $v['user'] = [
                'User_HeadImg' => !empty($v['User_HeadImg']) ? $v['User_HeadImg'] : '',
                'User_NickName' => !empty($v['User_NickName']) ? $v['User_NickName'] : substr_replace($v['User_Mobile'], '***', 6, 3)
            ];

            unset($v['User_HeadImg']);
            unset($v['User_NickName']);
            unset($v['User_Mobile']);

            $data[$k] = $v;
        }
    }

    if (empty($data)) {
        exit(json_encode(['errorCode' => 1, 'msg' => '拼团详情获取失败']));
    }

    $rsTeam['teamdetail'] = $data;
    $return_data = [$rsTeam];

    if (!empty($return_data)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $return_data]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}
//---------------- 参团操作 end ----------------


//---------------- 分销中心操作 start ----------------
//分销配置
if ($act == 'distribute_config') {
    $res = $DB->GetRs('distribute_config', '*', 'where Users_ID = "' . $Users_ID . '"');

    if (!empty($res)) {
        $rsDisLevel = $DB->GetAssoc('distribute_level', '*', 'where Users_ID = "' . $Users_ID . '"');

        $data = [
            'disconfig' => $res,
            'dislevel' => $rsDisLevel
        ];

        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $data]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取用户的分销数据
if ($act == 'get_dis_info') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    $res = $DB->GetRs('distribute_account', 'dis.balance, dis.Shop_Name, dis.Shop_Logo, u.User_NickName, u.User_HeadImg, u.Owner_Id, dis_l.Level_Name', ' as dis left join user as u on u.User_ID = dis.User_ID left join distribute_level as dis_l on dis_l.Level_ID = dis.Level_ID where dis.Users_ID = "' . $Users_ID . '" and dis.User_ID = ' . $User_ID);

    //有推荐人
    if (!empty($res['Owner_Id'])) {
        $res_recommed = $DB->GetRs('user', 'User_Mobile, User_NickName', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . (int)$res['Owner_Id']);
    }

    //计算累计佣金
    require_once($_SERVER["DOCUMENT_ROOT"].'/include/support/distribute_helpers.php');
    $total_income = get_my_leiji_income($Users_ID, $User_ID);

    //交易中的佣金    Record_Money：为扣除手续费...之后的   Record_Total：提现的金额
    $dis_withdraw_record = $DB->GetRs('distribute_withdraw_record', 'sum(Record_Money) as totalMoney', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID . ' and Record_Status = 0');

    $data = [
        'disInfo' => [
            'Level_Name' => !empty($res['Level_Name']) ? $res['Level_Name'] : '',
            'Nick_Name' => !empty($res['User_NickName']) ? $res['User_NickName'] : '',
            'Parent_Mobile' => !empty($res_recommed['User_Mobile']) ? $res_recommed['User_Mobile'] : '未填写',
            'Parent_NickName' => !empty($res_recommed['User_NickName']) ? $res_recommed['User_NickName'] : '来自总店',
            'Shop_Logo' => !empty($res['Shop_Logo']) ? $res['Shop_Logo'] : '',
            'Shop_Name' => !empty($res['Shop_Name']) ? $res['Shop_Name'] : '',
            'User_HeadImg' => !empty($res['User_HeadImg']) ? $res['User_HeadImg'] : ''
        ],
        'totalRecordMoney' => (float)$total_income,     //累计佣金
        'usersRecordMoney' => (float)$res['balance'],    //可提现佣金
        'recording' => (float)$dis_withdraw_record['totalMoney']    //交易中的佣金

    ];
    if (!empty($data)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $data]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取分销等级对应的分销人数
if ($act == 'get_dis_team') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    //获取商城设置的分销级别
    $rsConfig = $DB->GetRs('distribute_config', 'Dis_Level', 'where Users_ID = "' . $Users_ID . '"');
    $level_config = (int)$rsConfig['Dis_Level'];

    $Dis_Account = Dis_Account::where(['Users_ID' => $Users_ID, 'User_ID' => $User_ID])->first();
    $res = $Dis_Account->getLevelNum($level_config);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => ['count' => $res]]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取下级分销商列表
if ($act == 'get_dis_list') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    //获取分销id
    //$rsDisInfo = $DB->GetRs('distribute_account', 'Account_ID', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID);
    //$Account_ID = (int)$rsDisInfo['Account_ID'];

    $condition = ' as dis left join user as u on dis.User_ID = u.User_ID where dis.Users_ID = "' . $Users_ID . '"';

    //级别
    if (!empty($_POST['level']) && (int)$_POST['level'] > 0) {
        $level = (int)$_POST['level'];
        $condition .= ' and dis.Dis_Path regexp ",' . $User_ID . ',([0-9]+,){' . ($level - 1) . '}$"';
    } else {
        $condition .= ' and dis.Dis_Path like "%,' . $User_ID . ',%"';
    }

    $totalCount = $DB->GetRs('distribute_account', 'count(dis.Account_ID) as count', $condition)['count'];

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $res = $DB->GetAssoc('distribute_account', 'dis.Account_ID, dis.Account_CreateTime, u.User_Mobile, u.User_NickName, u.User_HeadImg', $condition);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $res, 'totalCount' => $totalCount]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取下级会员列表
function getSonUserID($invite_user_id) {
    global $UsersID, $DB;

    $rows = [];

    $DB->Get('user', 'User_ID', 'WHERE Users_ID = "' . $UsersID . '" AND Owner_Id IN (' . implode(',', $invite_user_id) . ')');
    while($row = $DB->fetch_assoc()) {
        $rows[] = $row['User_ID'];
    }
    return $rows;
}
if ($act == 'get_user_list') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    //获取商城设置的分销级别
    $rsConfig = $DB->GetRs('distribute_config', 'Dis_Level', 'where Users_ID = "' . $Users_ID . '"');
    $level_config = (int)$rsConfig['Dis_Level'];

    //获取所有的相关会员id
    $result = $invite_user_id = [$User_ID];
    for ($i = 0; $i < $level_config; $i++) {
        $dataUserID = getSonUserID($invite_user_id);
        if (empty($dataUserID)) {
            break;
        } else {
            $result = array_merge($result, $dataUserID);
            $invite_user_id = $dataUserID;
        }
    }

    $condition = 'where Users_ID = "' . $Users_ID . '" and Owner_Id in (' . implode(',', $result) . ')';

    $totalCount = $DB->GetRs('user', 'count(User_ID) as count', $condition)['count'];

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $res = $DB->GetAssoc('user', 'User_Mobile, User_NickName, User_HeadImg, User_CreateTime', $condition);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $res, 'totalCount' => $totalCount]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取用户的分销配置信息
if ($act == 'get_user_dis_config') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    $res = $DB->GetRs('distribute_account', 'balance, Shop_Name, Shop_Logo', 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//更新用户的分销配置信息
if ($act == 'updata_user_dis_config') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    $data = [];
    //店铺名称
    if (isset($_POST['Shop_Name'])) {
        $data['Shop_Name'] = cleanJsCss($_POST['Shop_Name']);
    }

    //店铺头像
    if (isset($_POST['Shop_Logo']) && file_exists('../..' . $_POST['Shop_Logo'])) {
        $data['Shop_Logo'] = $_POST['Shop_Logo'];
    }

    if (!empty($data)) {
        $condition = 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID;
        $res = $DB->Set('distribute_account', $data, $condition);

        if ($res) {
            $res = $DB->GetRs('distribute_account', 'Shop_Name, Shop_Logo', $condition);

            exit(json_encode(['errorCode' => 0, 'msg' => '更新成功', 'data' => $res]));
        } else {
            exit(json_encode(['errorCode' => 1, 'msg' => '更新失败']));
        }
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无提交数据']));
    }
}

//上传图片
if ($act == 'upload_image') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    if (empty($_FILES['image']) || $_FILES['image']['error'] != 0) {
        exit(json_encode(['errorCode' => 1, 'msg' => '上传失败']));
    }

    $ext_arr = ['gif', 'jpg', 'jpeg', 'png'];

    //{"file":{"image":{"name":"5a601322539d5.png","type":"application/octet-stream","tmp_name":"/tmp/phpqOQc9d","error":0,"size":19404}}}
    $file = $_FILES['image'];

    $path_parts = pathinfo($file['name']);

    //检查扩展名
    if (! in_array($path_parts['extension'], $ext_arr)) {
        exit(json_encode(['errorCode' => 1, 'msg' => '上传文件不是图片']));
    }

    //创建文件夹
    $save_path = $_SERVER["DOCUMENT_ROOT"] . '/uploadfiles/userfile/' . $User_ID . '/image/';
    if (!file_exists($save_path)) {
        dmkdir($save_path);
    }

    $file_path = $save_path . $file['name'];

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $path = str_replace($_SERVER["DOCUMENT_ROOT"], '', $file_path);

        exit(json_encode(['errorCode' => 0, 'msg' => '上传成功', 'path' => $path]));
    } else {
        exit(json_encode(['errorCode' => 1, 'msg' => '上传失败']));
    }
}

//获取分销记录列表
if ($act == 'get_dis_record_list') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    //0: 佣金  1：提现  默认：0
    $Record_Type = !empty($_POST['Record_Type']) ? 1 : 0;

    $condition = ' as dis_r left join user as u on dis_r.User_ID = u.User_ID where dis_r.Users_ID = "' . $Users_ID . '" and dis_r.User_ID = ' . $User_ID . ' and Record_Type = ' . $Record_Type;

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $res = $DB->GetAssoc('distribute_account_record', 'dis_r.*, u.User_Mobile, u.User_NickName, u.User_HeadImg', $condition);

    if (!empty($res)) {
        if ($Record_Type == 0) {
            $LevelNames = [
                '1' => '一',
                '2' => '二',
                '3' => '三',
                '4' => '四',
                '5' => '五',
                '6' => '六',
                '7' => '七',
                '8' => '八',
                '9' => '九'
            ];
            foreach ($res as $k => $v) {
                $names = $v['level'] == 110 || empty($LevelNames[$v['level']]) ? '' : $LevelNames[$v['level']] . '级';
                $names .= ($v['User_NickName'] ? $v['User_NickName'] : ($v['User_Mobile'] ? $v['User_Mobile'] : '用户')) . '购买商品';
                $v['Record_Description'] = html_entity_decode($v['Record_Description']);
                $v['Record_Description'] = str_replace('下属分销商分销', $names, $v['Record_Description']);
                $v['Record_Description'] = str_replace('分销商级别', '级别', $v['Record_Description']);
                $v['Record_Description'] = str_replace('分销级别', '级别', $v['Record_Description']);
                $v['Record_Description'] = str_replace('自己销售自己购买', ' 自销 ', $v['Record_Description']);

                $v['Record_Money'] = getFloatValue($v['Record_Money'], 2);

                $res[$k] = $v;
            }
        }

        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取提现记录
if ($act == 'get_withdraw_record_list') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    $condition = 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID;

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $res = $DB->GetAssoc('distribute_withdraw_record', '*', $condition);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//获取商城设置的可提现方式
if ($act == 'get_shop_withdraw_method') {
    $condition = 'where Users_ID = "' . $Users_ID . '" and Status = 1';

    //暂时只支持银行卡
    $condition .= ' and Method_Type = "bank_card"';

    $res = $DB->GetAssoc('distribute_withdraw_method', '*', 'where Users_ID = "' . $Users_ID . '" and Status = 1');

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '商家暂未设置提现方式，请联系商家设置！']));
    }
}

//获取用户的提现方式列表（银行卡、微信、支付宝）
if ($act == 'get_user_withdraw_method') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    $condition = 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID;

    //获取可提现佣金
    $rsDisInfo = $DB->GetRs('distribute_account', 'balance', $condition);
    $balance = !empty($rsDisInfo['balance']) ? $rsDisInfo['balance'] : 0;

    $condition .= ' and Method_Status = 1';
    if (!empty($_POST['User_Method_ID'])) {
        $condition .= ' and User_Method_ID = ' . (int)$_POST['User_Method_ID'];
    }

    //暂时只支持银行卡
    $condition .= ' and Method_Type = "bank_card"';

    $res = $DB->GetAssoc('distribute_withdraw_methods', '*', $condition);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $res, 'balance' => $balance]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//用户添加提现方式
if ($act == 'add_user_withdraw_method') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    $data = [
        'Users_ID' => $Users_ID,
        'User_ID' => $User_ID,
        'Method_Name' => !empty($_POST['Method_Name']) ? $_POST['Method_Name'] : '',
        'Method_Type' => !empty($_POST['Method_Type']) ? $_POST['Method_Type'] : '',
        'Account_Name' => !empty($_POST['Account_Name']) ? $_POST['Account_Name'] : '',
        'Account_Val' => !empty($_POST['Account_Val']) ? $_POST['Account_Val'] : '',
        'Bank_Position' => !empty($_POST['Bank_Position']) ? $_POST['Bank_Position'] : '',
        'Method_CreateTime' => time(),
        'Method_Status' => 1
    ];

    $res = $DB->Add('distribute_withdraw_methods', $data);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '添加成功']));
    } else {
        exit(json_encode(['errorCode' => 1, 'msg' => '添加失败']));
    }
}

//用户修改提现方式
if ($act == 'update_user_withdraw_method') {
    if (!isset($_POST['User_ID']) || !isset($_POST['User_Method_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID或User_Method_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];
    $User_Method_ID = (int)$_POST['User_Method_ID'];

    $condition = 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID . ' and User_Method_ID = ' . $User_Method_ID;

    $res = $DB->GetRs('distribute_withdraw_methods', 'User_Method_ID', $condition);

    if (empty($res)) {
        exit(json_encode(['errorCode' => 1, 'msg' => '没有找到此提现方式']));
    }

    $data = [];
    if (!empty($_POST['Method_Name'])) $data['Method_Name'] = $_POST['Method_Name'];
    if (!empty($_POST['Method_Type'])) $data['Method_Type'] = $_POST['Method_Type'];
    if (!empty($_POST['Account_Name'])) $data['Account_Name'] = $_POST['Account_Name'];
    if (!empty($_POST['Account_Val'])) $data['Account_Val'] = $_POST['Account_Val'];
    if (!empty($_POST['Bank_Position'])) $data['Bank_Position'] = $_POST['Bank_Position'];

    $res = $DB->Set('distribute_withdraw_methods', $data, $condition);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '编辑成功']));
    } else {
        exit(json_encode(['errorCode' => 1, 'msg' => '编辑失败']));
    }
}

//删除用户的提现方式
if ($act == 'del_user_withdraw_method') {
    if (!isset($_POST['User_ID']) || !isset($_POST['User_Method_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID或User_Method_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];
    $User_Method_ID = (int)$_POST['User_Method_ID'];

    $res = $DB->Del('distribute_withdraw_methods', 'Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID . ' and User_Method_ID = ' . $User_Method_ID);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '删除成功']));
    } else {
        exit(json_encode(['errorCode' => 1, 'msg' => '删除失败']));
    }
}

//获取分销订单
if ($act == 'get_dis_order') {
    if (!isset($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 401, 'msg' => '缺少必要参数User_ID']));
    }

    $User_ID = (int)$_POST['User_ID'];

    $condition = 'where Users_ID = "' . $Users_ID . '" and User_ID = ' . $User_ID;

    if (!empty($_POST['Order_ID'])) {
        $condition .= ' and Order_ID = ' . (int)$_POST['Order_ID'];
    }

    //分页
    $page = isset($_POST['page']) && (int)$_POST['page'] >= 1 ? (int)$_POST['page'] : 0;
    $pageSize = isset($_POST['pageSize']) && (int)$_POST['pageSize'] >= 1 ? (int)$_POST['pageSize'] : 0;
    $condition .= ' limit ' . ($page == 0 ? 0 : ($page - 1) * ($pageSize == 0 ? 20 : $pageSize)) . ',' . ($pageSize == 0 ? 20 : $pageSize);

    $res = $DB->GetAssoc('distribute_order', '*', $condition);

    if (!empty($res)) {
        exit(json_encode(['errorCode' => 0, 'msg' => '查询成功', 'data' => $res]));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '暂无数据']));
    }
}

//新增小程序推送授权码接口
if ($act == 'add_template_code') {
    if (empty($_POST['userid']) || empty($_POST['code']) || empty($_POST['times'])) {
        exit(json_encode(['errorCode' => 1, 'msg' => '缺少必要的参数']));
    }

    //组装数组
    $data = [
        'usersid' => $Users_ID,
        'userid' => (int)$_POST['userid'],
        'code' => preg_replace('/[^\w]/', '', $_POST['code']),
        'created_at' => time(),
        'times' => (int)$_POST['times']
    ];

    $flag = $DB->Add('template_message', $data);
    if ($flag) {
        exit(json_encode(['errorCode' => 0, 'msg' => '添加成功']));
    } else {
        exit(json_encode(['errorCode' => 2, 'msg' => '添加失败']));
    }
}

//获取大转盘初始化数据
if ($act == 'init_turntable') {
    if (empty($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 1, 'msg' => '缺少参数']));
    }
    $my_turn_table = new Myturntable($DB);
    $init_turntable = $my_turn_table->initTurn($Users_ID, (int)$_POST['User_ID']);
    exit(json_encode($init_turntable, JSON_UNESCAPED_UNICODE));
}


//开始进行大转盘抽奖
if ($act == 'begin_turntable') {
    if (empty($_POST['User_ID'])) {
        exit(json_encode(['errorCode' => 1, 'msg' => '缺少参数']));
    }
    $my_turn_table = new Myturntable($DB);
    $init_turntable = $my_turn_table->beginTurn($Users_ID, (int)$_POST['User_ID']);
    exit(json_encode($init_turntable, JSON_UNESCAPED_UNICODE));
}

//查询获奖记录
if ($act == 'getExRecord') {
    if (empty($_POST['User_ID']) || empty($_POST['Act_ID'])) {
        exit(json_encode(['errorCode' => 1, 'msg' => '缺少参数']));
    }
    $my_turn_table = new Myturntable($DB);
    $ex_record = $my_turn_table->getExRecord($Users_ID, (int)$_POST['User_ID'], (int)$_POST['Act_ID']);
    exit(json_encode($ex_record, JSON_UNESCAPED_UNICODE));
}

//兑奖接口
if ($act == 'exPrize') {
    if (empty($_POST['User_ID']) || empty($_POST['SN_ID']) || empty($_POST['ex_passwd'])) {
        exit(json_encode(['errorCode' => 1, 'msg' => '缺少参数']));
    }
    $my_turn_table = new Myturntable($DB);
    $ex_record = $my_turn_table->exPrize($Users_ID, (int)$_POST['User_ID'], (int)$_POST['SN_ID'], htmlspecialchars($_POST['ex_passwd']));
    exit(json_encode($ex_record, JSON_UNESCAPED_UNICODE));
}

//发送验证码接口
if ($act == 'sendyzm') {
    //检查手机号是否已注册
    if (empty($_POST['User_ID']) || empty($_POST['Mobile'])) {
        exit(json_encode(['errorCode' => 1, 'msg' => '缺少参数']));
    }
    $User_ID = (int)$_POST['User_ID'];
    $mobile = preg_replace('/[^\d]/', '', $_POST['Mobile']);
    $my_turntable = new Myturntable($DB);
    exit(json_encode($my_turntable->sendYzm($Users_ID, $User_ID, $mobile)));
}

//绑定手机号接口
if ($act == 'bindmobile') {
    if (empty($_POST['User_ID']) || empty($_POST['Mobile']) || empty($_POST['yzm'])) {
        exit(json_encode(['errorCode' => 1, 'msg' => '缺少参数']));
    }
    $User_ID = (int)$_POST['User_ID'];
    $mobile = preg_replace('/[^\d]/', '', $_POST['Mobile']);
    $yzm = (int)$_POST['yzm'];
    $my_turntable = new Myturntable($DB);
    exit(json_encode($my_turntable->bindMobile($Users_ID, $User_ID, $yzm, $mobile)));
}












//---------------- 分销中心操作 end ----------------

//获取物流模版
/*if ($act == 'get_shipping') {
    $rsConfig = shop_config($Users_ID);
    $dis_config = dis_config($Users_ID);
    $rsConfig = array_merge($rsConfig,$dis_config);
    $shipping_company_dropdown = get_front_shiping_company_dropdown($Users_ID, $rsConfig);
    echo json_encode(['errorCode' => 0, 'data' => empty($shipping_company_dropdown) ? [] : $shipping_company_dropdown]);
    exit;
}*/