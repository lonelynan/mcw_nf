<?php require_once($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
if (isset($_GET["UsersID"])) {
    $UsersID = $_GET["UsersID"];
} else {
    echo '缺少必要的参数';
    exit;
}

if (!strpos($_SERVER['REQUEST_URI'], "OpenID=")) {
    if (empty($_SESSION[$UsersID . 'OpenID'])) {
        $_SESSION[$UsersID . 'OpenID'] = session_id();
    }
} else {
    $url_arr = explode("OpenID=", $_SERVER['REQUEST_URI']);
    $endpos = explode("&", $url_arr[1]);
    $_SESSION[$UsersID . 'OpenID'] = $endpos[0];
}

if (!empty($_SESSION[$UsersID . "User_ID"])) {
    $rsUser = $DB->GetRs("user", "*", "where Users_ID='" . $UsersID . "' and User_ID=" . $_SESSION[$UsersID . "User_ID"]);
    if (empty($rsUser)) {
        $_SESSION[$UsersID . "User_ID"] = "";
    }
}

if (!strpos($_SERVER['REQUEST_URI'], "mp.weixin.qq.com")) {
    header("location:?wxref=mp.weixin.qq.com");
    exit;
}

if (empty($_SESSION[$UsersID . "User_ID"]) || !isset($_SESSION[$UsersID . "User_ID"])) {
    $_SESSION[$UsersID . "HTTP_REFERER"] = "/api/" . $UsersID . "/user/money_record/?wxref=mp.weixin.qq.com";
    header("location:/api/" . $UsersID . "/user/login/?wxref=mp.weixin.qq.com");
    exit;
}

$pageSize = 20;
$page = !empty($_POST['page']) && (int)$_POST['page'] > 1 ? (int)$_POST['page'] : 1;

$condition = 'where Users_ID = "' . $UsersID . '" and User_ID = ' . $_SESSION[$UsersID . 'User_ID'] . ' order by Item_ID desc';

$totalCount = $DB->GetRs('user_money_record', 'count(Item_ID) as count', $condition)['count'];
$condition .= ' limit ' . ($page - 1) * $pageSize . ',' . $pageSize;
$record = $DB->GetAssoc('user_money_record', '*', $condition);

foreach ($record as $k => $v) {
    $record[$k]['CreateTime_format'] = date('Y-m-d H:i:s', $v['CreateTime']);
}

$return = [
    'list' => $record,
    'get_flag' => ceil($totalCount / $pageSize) <= $page ? 0 : 1
];

if (!empty($_POST['action']) && $_POST['action'] == 'get_more') {
    exit(json_encode($return, JSON_UNESCAPED_UNICODE));
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta content="telephone=no" name="format-detection"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>会员中心</title>
    <link href='/static/api/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/api/css/user.css' rel='stylesheet' type='text/css'/>
    <link href='/static/api/shop/skin/default/style.css?t=<?php echo time(); ?>' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/api/js/global.js'></script>
    <script type='text/javascript' src='/static/api/js/user.js'></script>
    <style>
        #integral_record ul li {
            height: auto;
            border-bottom: dashed 1px #e6e6e6;
            padding: 2px 10px;
        }

        /* 加载更多 */
        .get_more {
            margin-top: 5px;
            margin-right: 5px;
            height: 35px;
            line-height: 35px;
            text-align: center;
        }

        .get_more span {
            display: inline-block;
            width: 100%;
            height: 100%;
            background: #eee;
        }
    </style>
</head>

<body>
<script language="javascript">$(document).ready(user_obj.money_init);</script>
<div id="integral_header">
    <div class="l"><span><?= $rsUser["User_Money"] ?></span><br/>
        我的余额
    </div>
</div>
<div id="integral_get_use">
    <div class="border_r"><a href="/api/<?= $UsersID ?>/user/charge/">充值</a></div>
    <div><a href="/api/<?= $UsersID ?>/user/paymoney/">实体店消费</a></div>
</div>
<div id="integral_record">
    <ul>
        <?php if (!empty($return['list'])) {
            foreach ($return['list'] as $v) { ?>
                <li>
                    【<?= $v["CreateTime_format"] ?>】
                    <?= $v["Note"] ?>
                </li>
            <?php }
        } ?>
    </ul>
    <div class="get_more" data-next-page="2">
        <?php if (empty($return['get_flag'])) { ?>
            已经没有了
        <?php } else { ?>
            <span>点击加载更多</span>
        <?php } ?>
    </div>
</div>
<script>
    // 获取更多
    var get_flag = true;
    function get_more(data) {
        $.ajax({
            type: 'post',
            url: '?wxref=mp.weixin.qq.com',
            data: data,
            success: function (json) {
                var html = '';

                $.each(json.list, function (i, v) {
                    html += '<li>';
                    html += '【' + v["CreateTime_format"] + '】' + v["Note"];
                    html += '</li>';
                });

                if (html) {
                    if (data.page == 1) {
                        $('#integral_record ul').html(html);
                    } else {
                        $('#integral_record ul').append(html);
                    }
                }
                if (json.get_flag) {
                    $('.get_more').attr('data-next-page', parseInt(data.page) + 1).html('<span>点击加载更多</span>');
                } else {
                    $('.get_more').attr('data-next-page', parseInt(data.page) + 1).html('已经没有了');
                }
                get_flag = true;
            },
            dataType: 'json'
        });
    }

    $(function () {
        // 获取更多产品
        $('.get_more').on('click', 'span', function () {
            if (!get_flag) return false;
            var page = parseInt($(this).parent().attr('data-next-page'));
            $('.get_more span').html('加载中...');
            get_more({action: 'get_more', page: page});
            get_flag = false;
        });
    });
</script>
<?php require_once('footer.php'); ?>
</body>
</html>