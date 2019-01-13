<?php
if (empty($_SESSION["Users_Account"])) {
    header("location:/member/login.php");
    exit;
}

if (!empty($_GET['Notice_ID'])) {
    $Notice_ID = $_GET['Notice_ID'];
} else {
    echo '<script>alert("缺少必要参数");history.back();</script>';
    exit;
}

if ($_POST) {
    if (empty($_POST["title"]) || empty($_POST["content"])) {
        echo '<script language="javascript">alert("公告标题和内容必填");history.back();</script>';
        exit;
    }

    $Data = [
        "Notice_Title" => htmlspecialchars($_POST["title"]),
        "Notice_Content" => htmlspecialchars($_POST["content"])
    ];


    $flag = $DB->Set("shop_notices", $Data, "where Users_ID = '" . $_SESSION["Users_ID"] . "' and Notice_ID = " . $Notice_ID);
    if ($flag) {
        echo '<script language="javascript">alert("修改成功！");window.open("notices.php","_self");</script>';
    } else {
        echo '<script language="javascript">alert("修改失败！");window.location="javascript:history.back()";</script>';
    }
    exit;
}

$notices = $DB->GetRs("shop_notices", "*", "where Users_ID = '" . $_SESSION["Users_ID"] . "' and Notice_ID = " . $Notice_ID);
if (empty($notices)) {
    echo '<script language="javascript">alert("此公告已经不存在！");window.location="javascript:history.back()";</script>';
    exit;
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
</head>

<body>
<!--[if lte IE 9]>
<script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
    <div class="iframe_content">
        <div class="r_nav">
            <ul>
                <li class="cur"><a href="notices.php">公告管理</a></li>
            </ul>
        </div>
        <div class="r_con_wrap">
            <form class="r_con_form" method="post" action="?Notice_ID=<?= $Notice_ID ?>">
                <div class="rows">
                    <label>标题</label>
                    <span class="input"><input type="text" name="title" value="<?= htmlspecialchars_decode($notices["Notice_Title"]) ?>" size="50" maxlength="200" class="form_input"/></span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>详细内容</label>
                    <span class="input">
                        <textarea name="content" style="width:98%;height:400px;"><?= htmlspecialchars_decode($notices["Notice_Content"]) ?></textarea>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label></label>
                    <span class="input">
                        <input type="submit" name="Submit" value="确定" class="submit btn_green btn_w_120">
                    </span>
                    <div class="clear"></div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>