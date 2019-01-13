<?php
date_default_timezone_set('PRC');
require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once (CMS_ROOT . '/api/shop/global.php');
$UserID = isset($_SESSION[$UsersID . 'User_ID']) ? $_SESSION[$UsersID . 'User_ID'] : 0;
if (empty($UserID)) {
    header("Location:/api/".$UsersID."/user/login/");
    exit;
}
//获取抽奖项目信息
$currentTime = time();
$rsTurntable = $DB->GetRs('turntable', '*', "where Users_ID = '". $UsersID ."' and status = 1 and starttime <= ". $currentTime ." and endtime >= ". $currentTime ." order by created_at desc");

$rsUser = $DB->GetRs('user', 'User_Mobile', "where User_ID = " . $UserID);

$error_message = '';
if($rsTurntable){
    //初始化大转盘
    $my_turn_table = new Myturntable($DB);
    $init_turntable = $my_turn_table->initTurn($UsersID, $UserID);
    $prize_arr = $init_turntable['data']['prize'];

    //查询获奖记录
    $ex_arr = [];
    $ex_record = $my_turn_table->getExRecord($UsersID, $UserID, $rsTurntable['id']);
    if ($ex_record['errorCode'] == 0) {
        $ex_arr = $ex_record['data'];
    }

    if (isset($_POST["action"]) && $_POST["action"] != "move") {

        //验证码登录
        $act = isset($_POST['action']) ? $_POST['action'] : '';

        if ($act == 'send') {
            //手机验证码校验开始
            $smsMobileKey = isset($_POST['Mobile']) ? $_POST['Mobile'] : '';
            $smsMobileKey = 'bind' . $smsMobileKey;

            //发送手机验证码
            if (isset($_POST['Mobile']) && $_POST['Mobile']) {

                //检查手机号是否已注册
                $mobile = $_POST['Mobile'];
                $user = $DB->GetRs('user', '*', "WHERE Users_ID='".$UsersID."' AND User_Mobile='" . $mobile . "' AND User_ID!=".$UserID);
                if ($user) {
                    //手机号已被占用
                    $data = [
                        'status' => 0,
                        'msg' => '手机号已绑定用户，请更换一个手机号'
                    ];

                    echo json_encode($data);
                    exit();
                }
                unset($user);

                //发送验证码
                $code = sprintf("%'.04d", rand(0, 9999));
                $_SESSION[$smsMobileKey] = json_encode([
                    'code' => $code,
                    'time' => time() + 120,	//120秒
                ]);

                require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
                $message = "手机验证码为：" . $code . "。120秒内有效，过期请重新获取。" ;
                $success = send_sms($_POST['Mobile'], $message, $UsersID);
                if ($success) {
                    $Data = array(
                        "status"=> 1,
                        "msg"=>"短信发送成功"
                    );
                } else {
                    $Data = array(
                        "status"=> 0,
                        "msg"=>"短信发送失败",
                    );
                }

                echo json_encode($Data, JSON_UNESCAPED_UNICODE);
                exit;
            }

        } else if ($act == 'bind') {

            $mobile = isset($_POST['Mobile']) ? $_POST['Mobile'] : '';
            $captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';

            //手机号非法
            require_once($_SERVER["DOCUMENT_ROOT"] . '/include/support/sysurl_helpers.php');
            if (!is_mobile($mobile)) {
                $data = [
                    'status' => 0,
                    'msg' => '手机号格式非法',
                ];
                echo json_encode($data);
                exit();
            }


            if ($setting['sms_enabled'] == 1) {

                //验证码
                $smsMobileKey = 'bind' . $mobile;
                if (!isset($_SESSION[$smsMobileKey]) || empty($_SESSION[$smsMobileKey]) || empty($captcha)) {
                    $Data = array(
                        "status" => 0,
                        "msg" => "手机验证码错误",
                    );
                    echo json_encode($Data,JSON_UNESCAPED_UNICODE);
                    exit();
                } else {
                    $dbSmsMobileKey = json_decode($_SESSION[$smsMobileKey], true);
                    if ((time() > $dbSmsMobileKey['time']) || ($dbSmsMobileKey['code'] != $captcha)) {
                        $Data = array(
                            "status" => 0,
                            "msg" => "手机验证码错误",
                        );
                        echo json_encode($Data,JSON_UNESCAPED_UNICODE);
                        exit();
                    }
                }

                if (isset($_SESSION[$smsMobileKey])) {
                    unset($_SESSION[$smsMobileKey]);
                }
            }
            //检查手机号是否已注册
            $user = $DB->GetRs('user', '*', "WHERE Users_ID='".$UsersID."' AND User_Mobile='" . $mobile . "' AND User_ID!=".$UserID);
            if ($user) {
                //用户已存在
                $data = [
                    'status' => 0,
                    'msg' => '手机号已绑定用户，请更换一个手机号'
                ];
            } else {
                //保存手机号
                $data = [
                    'User_Mobile' => $mobile,
                    'User_Profile' => 1,	//手机号已通过认证
                ];
                $DB->Set('user', $data, "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($UserID));

                $data = [
                    'status' =>1,
                    'msg' => '绑定手机成功'
                ];
            }

            echo json_encode($data);
            exit();

        } else if ($act == 'used') {
            $ex_flag = $my_turn_table->exPrize($UsersID, $UserID, (int)$_POST['SNID'], htmlspecialchars($_POST['bp']));
            exit(json_encode($ex_flag));
        }

    } else {
        $error_message = '';
        if (!empty($init_turntable) && $init_turntable['data']['chance'] == 0) {
            $error_message = '您的抽奖机会已用完';
        }
        //开始抽奖
        if (isset($_POST["action"]) && $_POST["action"] == "move") {
            $Data = $my_turn_table->beginTurn($UsersID, $UserID);
            echo json_encode($Data, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
} else {
    $style1 = "style='width:100%;text-align:center;background-color:#F1F2F7;height:800;line-height:800px;margin:0px;'";
    $num = rand(-30,30);

    $style2 = "style='border:5px solid red;width:80%;height:160px;line-height:160px;margin:200px auto;color:red;transform:rotate({$num}deg);-ms-transform:rotate({$num}deg);-moz-transform:rotate({$num}deg);-webkit-transform:rotate({$num}deg);-o-transform:rotate({$num}deg);'";
    echo "<div class='un_access' ".$style1."><div ".$style2."><b style='font-size:50px;'>活动已结束！</b></div></div>";
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta content="telephone=no" name="format-detection" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?= isset($rsTurntable) ? $rsTurntable['title'] : '当前没有进行中的活动！' ?></title>
    
    <link href='/static/api/css/turntable.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/api/js/global.js'></script>
    <script type='text/javascript' src='/static/js/plugin/jquery/easing.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/rotate/rotate.2.2.js'></script>
    <script type='text/javascript' src='/static/api/js/turntable.js'></script>
    <script type="text/javascript">
        var prize_count = <?=count($prize_arr)?>;
        var start=true;
        var bind_mobile = <?=!empty($rsUser['User_Mobile']) ? 1 : 0?>;
    </script>
</head>
<body>
<div class="canvas-container">
    <div class="canvas-content" id="canvas-content">
        <canvas class="canvas-element" id="lotteryCanvas" width="300" height="300"></canvas>
        <? foreach ($prize_arr as $k => $v) { ?>
            <div class="canvas-list">
                <div class="canvas-item">
                    <span class="canvas-item-text" style="-webkit-transform: rotate(<?=$v['index'] * (360 / count($prize_arr))?>deg);transform: rotate(<?=$v['index'] *  (360 / count($prize_arr))?>deg)"><?=$v['name']?></span>
                </div>
            </div>
        <? } ?>
    </div>
    <label class="canvas-btn">抽奖</label>
</div>
<p align="center" class="fs">今日剩余抽奖次数：<i><?=$init_turntable['data']['hasLotteryTimesByToday']?></i>次&nbsp;&nbsp;&nbsp;<br/>
    总共剩余抽奖次数：<i><?=$init_turntable['data']['AllDayLotteryTimes_have']?></i>
</p>
<div class="refer"> <span class="s2">活动说明</span>
    <div><?=$rsTurntable['descr']?></div>
</div>
<div class="refer"> <span class="s1">奖品设置</span>
    <div>
        <? foreach ($prize_arr as $k => $v) { if ($k < count($prize_arr) - 1) {  ?>
        <?=$v['name']?><br>
        <? }} ?>
    </div>
</div>
<div class="refer times"> <span class="s3">活动时间</span>
    <div>
        <?=date('Y/m/d', $rsTurntable['starttime'])?>-<?=date('Y/m/d', $rsTurntable['endtime'])?>
    </div>
</div>
<div id="UsedSn" class="refer none"> <span>兑奖申请(SN码：<font id="UsedSnNumber"></font>)</span>
    <div class="use">
        <form id="UsedPrize">
            <div class="input">
                <input type="password" name="bp" value="" class="form_input" placeholder="请输入商家兑奖密码" maxlength="16">
            </div>
            <div class="input">
                <input type="button" value="提交" class="submit">
                <input type="button" value="关闭" class="close">
            </div>
            <div class="clear"></div>
            <input type="hidden" name="SNID" value="">
        </form>
    </div>
</div>

<!-- 申请兑奖填写手机号 -->

<div id="phoneN" class="refer none"><span>请先绑定手机号,再进行领奖操作</span>
    <div class="use">
        <div class="input">
            <input type="number" name="Mobile" value="" class="form_input" placeholder="请输入手机号码" maxlength="11">
        </div>
        <div class="yanzhengma">
            <span class="yanzhengma_left">
                <input type="number" name="yzm" value="" class="form_input" placeholder="请输入手机验证吗">
            </span>
            <span class="yanzhengma_right">
                <button id="sendyzm">获取验证码</button>
            </span>
        </div>
        <div class="input">
            <input type="button" value="取消" class="close">
            <input type="submit" value="确认" class="submit">
        </div>
        <div class="clear"></div>
        <input type="hidden" name="phID" value="">
    </div>
</div>

<div id="PrizeTips" class="refer"> <span class="s2">我的中奖记录</span>
    <div class="prize_list">
        <ul>
            <? if (!empty($ex_arr)) {
                foreach ($ex_arr as $k => $v) { ?>
                <li style="color:#FFF">
                    <label><?=$k+1?>、</label>
                    <span sn="<?=$v['SN_Code']?>" snid="<?=$v['SN_ID']?>"> 奖项：<?=$v['Turntable_Prize']?><?=$v['SN_Status'] == 2 ? '<i style="color:#f00; display: inline-block">(已兑奖)</i>' : ''?><br>
                    中奖SN码：<font class="sn"><?=$v['SN_Code']?></font><? if ($v['SN_Status'] != 2) { ?> <a href="#usesn">申请兑奖</a><? } ?><br>
                    中奖时间：<?=date('Y-m-d', $v['SN_CreateTime'])?><br>
                    </span>
                    <div class="clear"></div>
                </li>
            <? }} else {?>
                <li style="color:#FFF">
                    暂无获奖记录
                </li>
            <? } ?>
	    </ul>
    </div>
</div>
<div class="b10"></div>
</body>
</html>
