<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/url.php');
$OrderID = empty($_REQUEST['OrderID']) ? 0 : $_REQUEST['OrderID'];
$rsOrder = $DB->GetRs("dismanual_order", "*", "where Users_ID='" . $_SESSION['Users_ID'] . "' and Order_ID=" . $OrderID);
if (empty($rsOrder)) {
    echo '<script>alert("没有找到申请信息");history.back();</script>';
    exit;
}
$Order_Status = array("待审核", "<span class='fc_yellow'>已审核</span>", "已驳回");
$jcurid = 1;
$Applyfor_form = json_decode($rsOrder['Applyfor_form'], true);  //增加的填写项
$Refuse_Bearr = json_decode($rsOrder["Refuse_Be"], true);       //审核内容
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
        <?php require_once($_SERVER["DOCUMENT_ROOT"] . '/member/distribute/distribute_menubar.php'); ?>
        <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
        <div id="orders" class="r_con_wrap">
            <div class="detail_card">
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
                        <td nowrap>当前状态：</td>
                        <?php if ($rsOrder["Order_Status"] != 2) { ?>
                            <td><?= $Order_Status[$rsOrder["Order_Status"]] ?></td>
                        <?php } else { ?>
                            <td><?= $Order_Status[$rsOrder["Order_Status"]] . count($Refuse_Bearr) ?>次</td>
                        <?php } ?>
                    </tr>
                    <?php if (!empty($Refuse_Bearr) && $rsOrder["Order_Status"] != 2) { ?>
                        <tr>
                            <td nowrap>曾经：</td>
                            <td>被驳回<?= count($Refuse_Bearr) ?>次</td>
                        </tr>
                    <?php } ?>
                    <?php if (!empty($Refuse_Bearr)) { ?>
                        <tr>
                            <td nowrap>拒绝原因：</td>
                            <td>
                                <?php
                                if (is_array($Refuse_Bearr)) {
                                    $i = 1;
                                    foreach ($Refuse_Bearr as $k => $v) {
                                        echo $i . '、' . $v . '<br>';
                                        $i++;
                                    }
                                } else {
                                    echo $Refuse_Bearr;
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td nowrap>申请时间：</td>
                        <td><?= date("Y-m-d H:i:s", $rsOrder["Order_CreateTime"]) ?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><a href="javascript:;" class="btn_gray" onClick="history.go(-1);">返 回</a></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>