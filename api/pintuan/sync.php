<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
if(!defined("CMS_ROOT"))  define("CMS_ROOT", $_SERVER["DOCUMENT_ROOT"]);
require_once (CMS_ROOT . '/include/helper/url.php');
require_once (CMS_ROOT . '/pay/wxpay2/wxrefund.php');
require_once (CMS_ROOT . '/include/helper/lib_pintuan.php');
require_once (CMS_ROOT . '/include/helper/backup.class.php');

set_time_limit(900);
use Illuminate\Database\Capsule\Manager as DB;
// 结束拼团活动
$UsersID = isset($_GET['UsersID']) && $_GET['UsersID']?$_GET['UsersID']:'';
$condition = $UsersID ? " AND t.users_id='" . $UsersID . "'" : "";
$time = time();
if($UsersID){
    $first = isset($_GET['first']) && $_GET['first']?$_GET['first']:'';
    if($first){
        pintuanend();
        choujiang();
    }else{
        sendMsgBx();
        tuikuan();
    }
}else{
    pintuanend();
    choujiang();
    tuikuan();
    sendMsgBx();
}

function pintuanend()
{
    global $condition,$time;

    //人数满足所达到的人数并且活动时间已到，那么就设置为成功
    $sql = "update pintuan_team as t join pintuan_products as p on t.productid = p.products_id set teamstatus = (case when p
.people_num <= t.teamnum then 1 when p.people_num > t.teamnum then 4 end ) where t
.teamstatus = 0 and p.stoptime < " . $time . $condition;

    DB::select($sql);
}
// 抽奖
// db_teamstatus 0拼团中，1拼团成功，2已中奖，3未中奖，4拼团失败, 5已退款
function choujiang()
{
    global $condition;
    $time = time();

    // 根据所参加的团获取产品数
    $field = 't.id,t.productid,t.users_id,p.Users_ID,p.people_num,p.Team_Count,p.starttime,p.stoptime';
    $condition = ' WHERE p.stoptime < ' . $time . $condition;
    $sql = 'SELECT ' . $field . '  FROM ( SELECT id, productid, users_id FROM pintuan_team WHERE teamstatus = 1 ) AS t LEFT JOIN ( SELECT Products_ID, Users_ID, people_num,Team_Count,starttime,stoptime FROM pintuan_products WHERE is_draw = 0 ) AS p ON t.productid = p.Products_ID ' . $condition;
    $list = DB::select($sql);
    // 抽奖前统计信息，获取产品列表id 和开团列表id，并统计个数
    $goodslist = [];
    $teamlist = [];
    $orderlist = [];
    $award = []; // 获取每个产品所设置的中奖团数
    foreach ($list as $k => $v) {
        $goodslist[$v['productid']] = $v['productid'];
        $teamlist[] = $v['id'];
        $award[$v['productid']] = $v['Team_Count'];
    }
    // 允许抽奖并且拼团成功的开团总团数
    $teamTotal = count($teamlist);
    // 开团里边的商品总数
    $goodsTotal = count($goodslist);
    // 获取允许开奖且拼团成功的订单id
    if ($teamTotal == 0){
        return;
    }
    $teamdetail = DB::table("pintuan_teamdetail")->whereIn("teamid", $teamlist)->get(['order_id']);
    $orderlist = array_column($teamdetail, 'order_id');
    $ordersTotal = count($orderlist);
    if ($ordersTotal == 0){
        return;
    }
    /*
 * 抽奖算法：
 * 首先计算参与抽奖的总团数
 * 其次计算中奖的团数
 */
    $goodsjson = "";
    $orderids = "";
    $awordteamlistStr = "";
    $noneteamlistStr = "";
    $goodsjson = [];

    if (! empty($goodslist)) {
        $awordteamlist = []; // 中奖团id列表
        $noneteamlist = []; // 未中奖团id列表
        foreach ($goodslist as $k => $v) {
            $gTeamlist = [];
            $goodsAwordlist = [];
            foreach ($list as $key => $value) {
                if ($v == $value['productid']) {
                    $gTeamlist[] = $value['id'];
                }
            }
            if (count($gTeamlist) > 0 && count($gTeamlist) > $award[$v]) {
                for ($i = 0; $i < $award[$v]; $i ++) {
                    do {
                        $num = mt_rand(0, count($gTeamlist));
                        if (isset($gTeamlist[$num])) {
                            if (! in_array($gTeamlist[$num], $goodsAwordlist)) {
                                $goodsAwordlist[] = $gTeamlist[$num];
                                break;
                            }
                        }
                    } while (true);
                }
            } else {
                $goodsAwordlist = array_merge($goodsAwordlist, $gTeamlist);
            }
            $noneAwordlist = array_diff($gTeamlist, $goodsAwordlist);
            $goodsjson[] = [
                'id' => $v,
                'AllowCount' => $award[$v],
                'awordlist' => implode(',', $goodsAwordlist),
                'noneAwordlist' => implode(',', $noneAwordlist)
            ];
            $awordteamlist = array_merge($awordteamlist, $goodsAwordlist);
            $noneteamlist = array_merge($noneteamlist, $noneAwordlist);
        }

        if (! empty($awordteamlist)) {
            $awordteamlistStr = implode(',', $awordteamlist);
            DB::table("pintuan_team")->whereIn("id", $awordteamlist)->update(["teamstatus" => 2]);
        }
        if (! empty($noneteamlist)) {
            $noneteamlistStr = implode(',', $noneteamlist);
            DB::table("pintuan_team")->whereIn("id", $noneteamlist)->update(["teamstatus" => 3]);
        }
    }
    if (! empty($orderlist)) {
        DB::table("pintuan_order")->whereIn("order_id", $orderids)->update(['is_ok' => 1]);
    }

    $data = [
        'goodsConfig' => json_encode([
            'total' => $goodsTotal,
            'list' => $goodsjson
        ]),
        'orderlist' => $orderids,
        'orderTotal' => $ordersTotal,
        'teamTotal' => $teamTotal,
        'goodsTotal' => $goodsTotal,
        'awordTeamlist' => $awordteamlistStr,
        'noneAwordTeamlist' => $noneteamlistStr,
        'addtime' => time(),
        'Users_ID' => isset($_SESSION['Users_ID']) ? $_SESSION['Users_ID'] : ''
    ];
    DB::table("pintuan_aword")->insert($data);
}

function sendMsgBx()
{
    $time = time();
    $interval_msg = time() - 500;
    $flow = DB::table("pintuan_aword")->where("addtime", ">", $interval_msg)->first();
    if(!empty($flow)){
        $awordteamlistStr = $flow['awordTeamlist'];
        $sql = "select o.order_id as order_id,o.Users_ID as Users_ID,uo.Order_ID,o.User_ID as User_ID,p.stoptime from pintuan_teamdetail as t left join pintuan_order as o on t.order_id=o.order_id left join user_order as uo ON o.order_id=uo.Order_ID LEFT JOIN pintuan_team AS p ON t.teamid = p.id where t.teamid in ($awordteamlistStr) AND (p.stoptime <{$time}  and o.is_ok is NULL";
        $list = DB::select($sql);
        foreach($list as $res_t){
            sendWXMessage($res_t['Users_ID'], $res_t['order_id'], '恭喜您参与的拼团活动已中奖，订单号为：<a href="' . base_url() . '/api/' . $res_t['Users_ID'] . '/pintuan/orderdetails/' . $res_t['order_id'] . '/">' . $res_t['Order_ID'] . '</a>', $res_t['User_ID']);
        }
    }
}

function tuikuan()
{
    global $UsersID,$time;
    // 获取所有未中奖或者拼团失败的团
    $fields = "u.Order_ID,u.Order_CartList,u.Order_PaymentMethod,u.Order_Type,u.Users_ID,u.Order_TotalPrice,u.User_ID,u.Order_Status,u.Order_ID,u.transaction_id,t.order_id,t.teamid,pts.teamstatus";
    $condition = "(pp.stoptime <{$time}) ";
    $sql =  "SELECT " . $fields . " FROM pintuan_teamdetail AS t ";
    $sql .= "LEFT JOIN user_order AS u ON t.order_id = u.Order_ID ";
    $sql .= "LEFT JOIN pintuan_order AS p ON u.Order_ID = p.order_id ";
    $sql .= "LEFT JOIN pintuan_team AS pts ON t.teamid = pts.id ";
    $sql .= " WHERE t.teamid IN (SELECT id FROM pintuan_team AS pt LEFT JOIN pintuan_products AS pp ON pt.productid=pp.Products_ID WHERE pt.teamstatus IN (3,4) AND ". $condition . ($UsersID?" AND pt.users_id='{$UsersID}'":"").") AND p.order_status >= 2 AND p.order_status<=5 ORDER BY u.User_ID ASC";
    $orderlist = DB::select($sql); 
    if (! empty($orderlist)) {
        $idlist = [];
        foreach ($orderlist as $order) {
            if ($order['Order_PaymentMethod']) {
                $Users_ID = $order['Users_ID'];
                $order_id = $order['Order_ID'];

                if ($order['Order_PaymentMethod'] == '微支付') {
                    // 微信支付退款
                    $weixinFlag = refund($order, $Users_ID);
                    if ($weixinFlag) {
                        $idlist [] = $order_id;
                    }
                } else if (  $order['Order_PaymentMethod'] == '余额支付' ){
                    $price = $order['Order_TotalPrice'];
                    $userid = $order['User_ID'];
                    // 更改退款状态
                    // 执行余额支付退款
                    $objUser = User::where("User_ID", $userid)->first();
                    $objUser->User_Money += $price;
                    $flag1 = $objUser->save();
                    if (! $flag1) {
                        back_trans();
                    }
                    $idlist [] = $order_id;
                } else {
                    $idlist [] = $order_id;
                    continue;
                }
                $goodsinfo = json_decode($order['Order_CartList'], true);
                Stock($goodsinfo['Products_ID'], $Users_ID, '+');
                if (in_array($order['Order_Status'], array(
                    2,
                    3,
                    4,
                    5
                ))) {
                    sendWXMessage($Users_ID, $order_id, '拼团没有成功已退款，订单号：' . $order['Order_ID'] . "，退款金额：" . $order['Order_TotalPrice'], $order['User_ID']);
                }
            }
        }
        if(!empty($idlist)){
            DB::table("pintuan_order")->whereIn("order_id", $idlist)->update(['order_status' => 6]);
            Order::whereIn("Order_ID", $idlist)->update(['Order_Status' => 6]);
        }
    }
    if($UsersID){
        header("Location: /member/pintuan/orders.php?cliid=sub14");
        exit;
    }
}
?>