<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/url.php');
$OrderID = empty($_REQUEST['OrderID']) ? 0 : $_REQUEST['OrderID'];
$rsOrder = $DB->GetRs("dismanual_order", "*", "where Users_ID='" . $_SESSION['Users_ID'] . "' and Order_ID=" . $OrderID);
if (empty($rsOrder)) {
    echo '<script>alert("没有找到申请信息");history.back();</script>';
    exit;
}
$Order_Status = array("待审核", "<span class='fc_yellow'>已审核</span>", "已驳回");
if ($rsOrder["Order_Status"] <> 0) {
    echo '<script language="javascript">alert("只有状态为“待审核”的申请才可以审核");history.back();</script>';
    exit;
}
if ($_POST) {
    if ($_POST["refuse"] == 1) {
        $Data = array(
            "Order_Status" => 1
        );
    } else {
        $rsRefuse_Be = $DB->GetRs("dismanual_order", "Refuse_Be", "where Order_ID=" . $OrderID);
        if (!empty($rsRefuse_Be['Refuse_Be'])) {
            $rsRefuse_Bearr = json_decode($rsRefuse_Be['Refuse_Be'], true);
            if (!is_array($rsRefuse_Bearr)) {
                $refuse_an = strFilter($rsRefuse_Be['Refuse_Be']);
                $rsRefuse_Bearr = explode("|", $refuse_an);
            }
        }
        $rsRefuse_Bearr[] = strFilter($_POST['refusebe']);
        $Data = array(
            "Order_Status" => 2,
            "Refuse_Be" => json_encode($rsRefuse_Bearr, JSON_UNESCAPED_UNICODE)
        );
    }
    $Flag = $DB->Set("dismanual_order", $Data, "where Order_ID=" . $OrderID);
    if ($Flag) {
        if ($_POST["refuse"] == 1) {
            echo '<script language="javascript">alert("申请已通过");window.location="dismanual_orders.php";</script>';
        } else {
            echo '<script language="javascript">alert("申请已回绝");window.location="dismanual_orders.php";</script>';
        }
    } else {
        echo '<script language="javascript">alert("审核出错");history.back();</script>';
    }
    exit;
}
$Applyfor_form = empty($rsOrder['Applyfor_form']) || $rsOrder['Applyfor_form'] == '[]' ? [] : json_decode($rsOrder['Applyfor_form'], true);  //增加的填写项
$jcurid = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css?t=14713' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]>
<script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/member/js/distribute/order.js'></script>
        <?php require_once($_SERVER["DOCUMENT_ROOT"] . '/member/distribute/distribute_menubar.php'); ?>
        <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
        <script type='text/javascript' src='/static/js/plugin/pcas/pcas.js'></script>
        <script language="javascript">$(document).ready(order_obj.orders_send);</script>
        <div id="orders" class="r_con_wrap">
            <div class="detail_card">
                <form id="order_send_form" class="s_con_form" method="post" action="?">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="order_info">
                        <tr>
                            <td width="3%" nowrap>订单编号：</td>
                            <td width="92%"><?= date("Ymd", $rsOrder["Order_CreateTime"]) . $rsOrder["Order_ID"] ?></td>
                        </tr>
                        <tr>
                            <td nowrap>申请人：</td>
                            <td><?= $rsOrder["Applyfor_Name"] ?></td>
                        </tr>
                        <tr>
                            <td nowrap>申请人手机：</td>
                            <td><?= $rsOrder["Applyfor_Mobile"] ?></td>
                        </tr>
                        <?php if (isset($Applyfor_form) && !empty($Applyfor_form)) {
                            foreach ($Applyfor_form as $key => $value) { ?>
                                <tr>
                                    <?php foreach ($value as $k => $v) { ?>
                                        <td nowrap><?= $k ?></td>
                                        <td><?= $v ?></td>
                                    <?php } ?>
                                </tr>
                            <?php }
                        } ?>
                        <tr>
                            <td nowrap>状态：</td>
                            <td><?= $Order_Status[$rsOrder["Order_Status"]] ?></td>
                        </tr>
                        <tr>
                            <td nowrap>申请时间：</td>
                            <td><?= date("Y-m-d H:i:s", $rsOrder["Order_CreateTime"]) ?></td>
                        </tr>
                        <tr>
                            <td nowrap>是否拒绝：</td>
                            <td>
                                <input class="input" id="c_0" type="radio" name="refuse" value="1" checked/><label for="c_0">通过</label>&nbsp;&nbsp;<input class="input" id="c_1" type="radio" name="refuse" value="0"/><label for="c_1">不通过</label>
                            </td>
                        </tr>
                        <tr id='refuseshow' style="display:none">
                            <td nowrap>原因：</td>
                            <td><textarea name="refusebe" style="width:30%;height:120px;"></textarea></td>
                        </tr>
                        <tr>
                            <td><input type="submit" class="btn_green" name="submit_button" value="通过审核"/></td>
                            <td colspan="2"><a href="javascript:void(0);" class="btn_gray" onClick="history.go(-1);">返 回</a></td>
                        </tr>
                    </table>
                    <input type="hidden" name="OrderID" value="<?= $rsOrder["Order_ID"] ?>"/>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>