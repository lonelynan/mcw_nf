<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
S($UsersID . "HTTP_REFERER", $_SERVER["REQUEST_URI"]);
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/library/wechatuser.php');
setcookie('url_referer', $_SERVER["REQUEST_URI"], time()+3600, '/', $_SERVER['HTTP_HOST']);

use Illuminate\Database\Capsule\Manager as DB;

$user = DB::table("user")->where(["Users_ID" => $UsersID, 'User_ID' => $UserID])->first(["User_NickName",
    "User_HeadImg", "User_Mobile"]);
// 统计各个订单状态的数量
$totalReady = Order::Multiwhere([
    'Users_ID' => $UsersID,
    'User_ID' => $UserID,
    'Order_Status' => 1
])->whereRaw("(Order_Type='pintuan' or Order_Type='dangou')")->count();
$totalhassent = Order::Multiwhere([
    'Users_ID' => $UsersID,
    'User_ID' => $UserID,
    'Order_Status' => 3
])->whereRaw("(Order_Type='pintuan' or Order_Type='dangou')")->count();
$totalrefund = Order::Multiwhere([
    'Users_ID' => $UsersID,
    'User_ID' => $UserID,
    'Order_Status' => 6
])->whereRaw("(Order_Type='pintuan' or Order_Type='dangou')")->count();
$result = DB::select("select count(*) as total from pintuan_teamdetail as d left join pintuan_team as t on  d.teamid = t.id where t.users_id = '{$UsersID}' and (d.userid = {$UserID}  or t.userid = {$UserID} ) AND t.teamstatus = 0");
$totalteaming = $result[0]['total'];
$result = DB::select("select count(*) as total from pintuan_teamdetail as d left join pintuan_team as t on  d.teamid = t.id where t.users_id = '{$UsersID}' and (d.userid = {$UserID}  or t.userid = {$UserID} ) AND t.teamstatus = 1");
$totalteamsuccess = $result[0]['total'];
$result = DB::select("select count(*) as total from pintuan_teamdetail as d left join pintuan_team as t on  d.teamid = t.id where t.users_id = '{$UsersID}' and (d.userid = {$UserID}  or t.userid = {$UserID} ) AND t.teamstatus = 4");
$totalteamfailed = $result[0]['total'];

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>我的抢购</title>
	<link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script>
</head>
<body>
	<div class="w">
        <div class="txbj">
            <div class="txk">
                <span class="txb"><img src="<?= $user['User_HeadImg'] ? $user['User_HeadImg'] : '/static/api/images/user/face.jpg' ?>"/></span><br/>
                <span class="txt"><?php echo empty($user['User_NickName']) ? $user['User_Mobile'] : $user['User_NickName']; ?></span>
            </div>
        </div>
		<div class="clear"></div>
        <div class="dingdan_qb">
            <div class="dingdan_t">
                <span class="l">我的订单</span>
                <span class="r"><a href="<?php echo "/api/$UsersID/pintuan/orderlist/0/"; ?>">查看全部订单></a></span>
                <div class="clear"></div>
            </div>
            <div class="nav_dingdan">
                <ul>
                    <li class="l"><a href="<?php echo "/api/$UsersID/pintuan/orderlist/1/"; ?>"><span><img width="25px" height="25px" src="/static/api/pintuan/images/552cd71a8a190_128.png"><?php if($totalReady){ ?><b><?=$totalReady ?></b><?php } ?></span><p>待付款</p></a></li>
                    <li class="l"><a href="<?php echo "/api/$UsersID/pintuan/teamlist/1/"; ?>"><span><img width="25px" height="25px" src="/static/api/pintuan/images/552cd7015c801_128.png"><?php if($totalteaming){ ?><b><?=$totalteaming ?></b><?php } ?></span><p>拼团中</p></a></li>
                    <li class="l"><a href="<?php echo "/api/$UsersID/pintuan/teamlist/2/"; ?>"><span><img width="25px" height="25px" src="/static/api/pintuan/images/552cd6dc78241_128.png"><?php if($totalteamsuccess){ ?><b><?=$totalteamsuccess ?></b><?php } ?></span><p>拼团成功</p></a></li>
                    <li class="l"><a href="<?php echo "/api/$UsersID/pintuan/teamlist/3/"; ?>"><span><img width="25px" height="25px" src="/static/api/pintuan/images/552cd6dc78241_128.png"><?php if($totalteamfailed){ ?><b><?=$totalteamfailed ?></b><?php } ?></span><p>拼团失败</p></a></li>
                    <li class="l"><a href="<?php echo "/api/$UsersID/pintuan/orderlist/3/"; ?>"><span><img width="25px" height="25px" src="/static/api/pintuan/images/552cd6f0b1fea_128.png"><?php if($totalhassent){ ?><b><?=$totalhassent ?></b><?php } ?></span><p>已发货</p></a></li>
                </ul>
            </div>
        </div>
        <div class="clear"></div>
        <div class="nav_lb">
            <ul class="nav_list">
                <li class="l"><a href="<?php echo "/api/$UsersID/pintuan/teamlist/0/"; ?>"><span><img width="35px" height="35px" src="/static/api/pintuan/images/001.png"></span><br><span>我的团</span></a></li>
                <li class="l"><a href="<?php echo "/api/$UsersID/pintuan/mycart/"; ?>"><span><img width="35px" height="35px" src="/static/api/pintuan/images/002.png"></span><br><span>我的收藏</span></a></li>
                <li class="l"><a href="<?php echo "/api/$UsersID/pintuan/my/address/"; ?>"><span><img width="35px" height="35px" src="/static/api/pintuan/images/003.png"></span><br><span>收货地址</span></a></li>
            </ul>
        </div>
	    <div class="clear"></div>
	    <div class="shuaxin"><a href="javascript:location.reload();">刷新页面</a></div>
	    <div class="kb"></div>
	    <div class="clear"></div>
	    <div style="height:70px;"></div>
	    
    </div>
	<script type="text/javascript">
		$(function(){
			$('.shuaxin').click(function(){
				location.reload();
			});
		});
	</script>
</body>
</html>
