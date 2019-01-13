<?php
include('../global.php');

if (isset($_GET["NoticeID"])) {
    $NoticeID = $_GET["NoticeID"];
} else {
    echo '<script>alert("缺少必要的参数");history.back();</script>';
    exit;
}

//获取文章
$rsNotice = $DB->GetRs("shop_notices", "*", "where Users_ID = '" . $UsersID . "' and Notice_ID = " . $NoticeID);
if (empty($rsNotice)) {
    echo "不存在该公告";
    exit;
}
/*if(mb_strlen($rsNotice['Notice_Title'],'utf-8') > 7){
	$rsNotice['Notice_Title'] = mb_substr($rsNotice['Notice_Title'],0,7,'utf-8');
}*/
/*$rsNotice['Notice_Content'] = str_replace('&quot;', '"', $rsNotice['Notice_Content']);
$rsNotice['Notice_Content'] = str_replace("&quot;", "'", $rsNotice['Notice_Content']);
$rsNotice['Notice_Content'] = str_replace('&gt;', '>', $rsNotice['Notice_Content']);
$rsNotice['Notice_Content'] = str_replace('&lt;', '<', $rsNotice['Notice_Content']);*/
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta content="telephone=no" name="format-detection"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?= $rsNotice['Notice_Title'] ?></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/api/css/global.css' rel='stylesheet' type='text/css'/>
    <link href="/static/css/bootstrap.css" rel="stylesheet"/>
    <link href="/static/api/shop/skin/default/css/tao_detail.css" rel="stylesheet" type="text/css"/>

    <style type="text/css">
        .notice_header {
            width: 80%;
            font-size: 16px;
            margin: 10px auto 0;
            text-align: center;
            line-height: 25px;
        }

        .notice_time {
            width: 100%;
            margin: 0 auto;
            text-align: center;
            line-height: 35px;
            font-size: 15px;
        }

        .notice_content {
            padding: 10px;
            font-size: 15px;
        }
    </style>
</head>

<body style="background:#FFF;">

<header class="bar bar-nav">
    <a href="javascript:history.go(-1)" class="pull-left"><img src="/static/api/shop/skin/default/images/black_arrow_left.png"/></a>
    <h1 class="title" id="page_title">公告详情</h1>
</header>

<h1 class="notice_header"><?= htmlspecialchars_decode($rsNotice["Notice_Title"]) ?></h1>
<div class="notice_time"><?= date('Y-m-d', $rsNotice['Notice_CreateTime']) ?></div>
<div class="notice_content">
    <?= htmlspecialchars_decode($rsNotice['Notice_Content']) ?>
</div>

</body>
</html>
