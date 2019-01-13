<?php
if (empty($_SESSION["Users_Account"])) {
    header("location:/member/login.php");
    exit;
}
if (isset($_GET["action"])) {
    if ($_GET["action"] == "del") {
        $Flag = $DB->Del("user_order_commit", "Users_ID='" . $_SESSION["Users_ID"] . "' and Item_ID=" . $_GET["ItemID"]);
        if ($Flag) {
            echo '<script language="javascript">alert("删除成功");window.location="' . $_SERVER['HTTP_REFERER'] . '";</script>';
        } else {
            echo '<script language="javascript">alert("删除失败");history.back();</script>';
        }
        exit;
    } elseif ($_GET["action"] == "check") {
//        $DB->Set("user_order_commit", "Status=1", "where Users_ID='" . $_SESSION["Users_ID"] . "' and MID = 'shop' and Item_ID=" . $_GET["ItemID"]);
        $DB->Set("user_order_commit", "Status=1", "where Users_ID='" . $_SESSION["Users_ID"] . "' and Item_ID=" . $_GET["ItemID"]);
        echo '<script language="javascript">alert("已通过审核");window.location="' . $_SERVER['HTTP_REFERER'] . '";</script>';
        exit;
    } elseif ($_GET["action"] == "uncheck") {
        $DB->Set("user_order_commit", "Status=0", "where Users_ID='" . $_SESSION["Users_ID"] . "' and MID = 'shop' and Item_ID=" . $_GET["ItemID"]);
        $DB->Set("user_order_commit", "Status=0", "where Users_ID='" . $_SESSION["Users_ID"] . "' and Item_ID=" . $_GET["ItemID"]);
        echo '<script language="javascript">alert("已取消审核");window.location="' . $_SERVER['HTTP_REFERER'] . '";</script>';
        exit;
    }
}

$jcurid = 7;

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]>
<script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/member/js/weicbd.js'></script>
        <?php require_once($_SERVER["DOCUMENT_ROOT"] . '/member/shop/product_menubar.php'); ?>
        <div id="reviews" class="r_con_wrap">
            <table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="review_list">
                <thead>
                <tr>
                    <td width="5%" nowrap="nowrap">序号</td>
                    <td width="10%" nowrap="nowrap">评论产品</td>
                    <td width="8%" nowrap="nowrap">分数</td>
                    <td width="40%" nowrap="nowrap">评论内容</td>
                    <td width="10%" nowrap="nowrap">时间</td>
                    <td width="8%" nowrap="nowrap">状态</td>
                    <td width="8%" nowrap="nowrap" class="last">操作</td>
                </tr>
                </thead>
                <tbody>
                <?php
                $lists = [];
                $_Status = ['<font style="color:red">待审核</font>', '<font style="color:blue">已审核</font>'];
                $_Check = ['check', 'uncheck'];
                $_Title = ['点击通过审核', '点击取消审核'];

               /* $DB->getPage("user_order_commit", "c.*, p.Products_Name", " as c left join shop_products as p on c.Product_ID = p.Products_ID where c.Users_ID='" . $_SESSION["Users_ID"] . "' and c.MID='shop' order by c.CreateTime desc", 10);*/
                $DB->getPage("user_order_commit", "c.*, p.Products_Name", " as c left join shop_products as p on c.Product_ID = p.Products_ID where c.Users_ID='" . $_SESSION["Users_ID"] . "' order by c.CreateTime desc", 10);
                while ($r = $DB->fetch_assoc()) {
                    $lists[] = $r;
                }
                foreach ($lists as $k => $rsCommit) {
                    ?>
                    <tr>
                        <td nowrap="nowrap"><?= $k + 1 ?></td>
                        <td><?= $rsCommit["Products_Name"] ?></td>
                        <td nowrap="nowrap"><?= $rsCommit["Score"] ?></td>
                        <td><?= $rsCommit["Note"] ?></td>
                        <td nowrap="nowrap"><?= date("Y-m-d H:i:s", $rsCommit["CreateTime"]) ?></td>
                        <td nowrap="nowrap">
                            <a href="?action=<?= $_Check[$rsCommit["Status"]] ?>&ItemID=<?= $rsCommit["Item_ID"] ?>" title="<?= $_Title[$rsCommit["Status"]]; ?>"><?= $_Status[$rsCommit["Status"]] ?></a>
                        </td>
                        <td class="last" nowrap="nowrap">
                            <a href="?action=del&ItemID=<?= $rsCommit["Item_ID"] ?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false;}"><img src="/static/member/images/ico/del.gif" align="absmiddle"/></a>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <div class="blank20"></div>
            <?php $DB->showPage(); ?>
        </div>
    </div>
</div>
</body>
</html>