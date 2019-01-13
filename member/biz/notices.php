<?php
if (empty($_SESSION["Users_Account"])) {
    header("location:/member/login.php");
    exit;
}

$_GET = daddslashes($_GET, 1);
$Keywords = empty($_REQUEST["Keywords"]) ? "" : trim($_REQUEST["Keywords"]);
$condition = "where Users_ID='" . $_SESSION["Users_ID"] . "' ";
if ($Keywords) {
    $condition .= " and Notice_Title like '%" . $Keywords . "%'";
}

//删除开始
if (!empty($_GET["action"]) && $_GET["action"] == "Del") {
    $Notice_ID = empty($_GET["Notice_ID"]) ? "0" : $_GET["Notice_ID"];
    $res = $DB->Del('shop_notices', 'Users_ID = "' . $_SESSION["Users_ID"] . '" and Notice_ID = ' . $Notice_ID);
    if ($res) {
        echo "<script language='javascript'>alert('删除成功！');window.open('notices.php','_self');</script>";
    } else {
        echo "<script language='javascript'>alert('删除失败！');history.back();</script>";
    }
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
        <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
        <div class="r_con_wrap">
            <form class="search" id="search_form" method="get" action="">
                关键字：<input name="Keywords" value="<?= $Keywords ?>" type="text" class="form_input" size="30" notnull/>
                <input type="submit" class="search_btn" value="搜索"/>
            </form>
            <br/>
            <div class="b10"></div>
            <div class="control_btn"><a href="notice_add.php" class="btn_green btn_w_120">添加公告</a></div>
            <div class="b10"></div>
            <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
                <thead>
                <tr>
                    <td nowrap="nowrap" width="6%">ID</td>
                    <td nowrap="nowrap" width="20%">标题</td>
                    <td nowrap="nowrap">内容</td>
                    <td nowrap="nowrap" width="15%">添加时间</td>
                    <td nowrap="nowrap" width="10%" class="last">操作</td>
                </tr>
                </thead>
                <tbody>
                <?php
                $lists = [];
                $DB->getPage("shop_notices", "*", $condition . " order by Notice_ID desc", 10);
                while ($r = $DB->fetch_assoc()) {
                    $lists[] = $r;
                }
                foreach ($lists as $t) {
                    ?>
                    <tr>
                        <td nowrap="nowrap"><?= $t["Notice_ID"] ?></td>
                        <td style="text-align:left; padding-left:10px;"><?= $t["Notice_Title"] ?></td>
                        <td style="text-align:left; padding-left:10px;"><?= mb_substr($t["Notice_Content"], 0, 340, 'utf-8') . (mb_strlen($t["Notice_Content"], 'utf-8') > 340 ? '...' : '') ?></td>
                        <td nowrap="nowrap"><?= date("Y-m-d H:i:s", $t["Notice_CreateTime"]) ?></td>
                        <td class="last" nowrap="nowrap">
                            <a href="notice_edit.php?Notice_ID=<?= $t["Notice_ID"] ?>"><img src="/static/admin/images/ico/mod.gif" align="absmiddle" alt="修改" title="修改"/></a>
                            <a href="?action=Del&Notice_ID=<?= $t["Notice_ID"] ?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false;}"><img src="/static/admin/images/ico/del.gif" align="absmiddle" alt="删除" title="删除"/></a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div class="blank20"></div>
            <?php $DB->showPage(); ?>
        </div>
    </div>
</div>
</body>
</html>