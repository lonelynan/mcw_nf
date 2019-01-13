<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/shipping.php');

if (empty($_SESSION["Users_Account"])) {
    header("location:/member/login.php");
    exit;
}

$OrderID = empty($_REQUEST['OrderID']) ? 0 : $_REQUEST['OrderID'];
$rsOrder = $DB->GetRs("user_order", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "' and Order_ID=" . $OrderID);
if ($rsOrder["Order_Status"] != 31) {
    if ($rsOrder["Order_Status"] != 0) {
        echo '<script language="javascript">alert("只有状态为“待确认”的订单才可确认订单");history.back();</script>';
        exit;
    }
}
if ($_POST) {
    $Data = array(
        "Address_Name" => empty($_POST["Name"]) ? '' : $_POST["Name"],
        "Address_Mobile" => empty($_POST["Mobile"]) ? '' : $_POST["Mobile"],
        "Order_Remark" => $_POST["Remark"],
        "Order_Status" => $_POST["OrderStatus"] == 31 ? 4 : 1
    );
    mysql_query("BEGIN");
    $Flag = $DB->Set("user_order", $Data, "where Order_ID=" . $OrderID);
    $Flag2 = true;
    $Flag3 = true;
    if ($_POST["OrderStatus"] == 31) {
        $UsersID = $_SESSION["Users_ID"];
        $Data2 = array(
            "Status" => 1
        );
        $itemID = $rsOrder['Address_Detailed'];
        $itemID = intval($itemID);
        $Flag2 = $DB->Set("user_charge", $Data2, "where Users_ID='" . $UsersID . "' and User_ID='" . $rsOrder['User_ID'] . "' and Item_ID=" . $itemID);
        $rsUser = $DB->GetRs("user", "User_Money", "where Users_ID='" . $UsersID . "' and User_ID='" . $rsOrder['User_ID'] . "'");
        $Data3 = array(
            "User_Money" => $rsUser['User_Money'] + $_POST['TotalPrice'],
        );
        $Flag3 = $DB->Set("user", $Data3, "where Users_ID='" . $UsersID . "' and User_ID=" . $rsOrder['User_ID']);
    }
    if ($Flag && $Flag2 && $Flag3) {
        mysql_query("COMMIT");
        echo '<script language="javascript">alert("订单已确认");window.location="orders.php";</script>';
    } else {
        mysql_query("ROLLBACK");
        echo '<script language="javascript">alert("订单已确认");history.back();</script>';
    }
    exit;
} else {
    $rsConfig = $DB->GetRs("shop_config", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "'");
    $rsPay = $DB->GetRs("users_payconfig", "Shipping", "where Users_ID='" . $_SESSION["Users_ID"] . "'");

    $Status = $rsOrder["Order_Status"];
    $Order_Status = [0 => "待确认", 1 => "待付款", 2 => "已付款", 3 => "已发货", 4 => "已完成", 31 => '已完成'];

    $PayShipping = get_front_shiping_company_dropdown($_SESSION["Users_ID"], $rsConfig);

    $CartList = json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]), true);
    $PaymentInfo = json_decode($rsOrder["Order_PaymentInfo"], true);
    $amount = $fee = 0;

    $area_json = read_file($_SERVER["DOCUMENT_ROOT"] . '/data/area.js');
    $area_array = json_decode($area_json, TRUE);
    $province_list = $area_array[0];
    $Province = '';
    if (!empty($rsOrder['Address_Province'])) {
        $Province = $province_list[$rsOrder['Address_Province']] . ',';
    }
    $City = '';
    if (!empty($rsOrder['Address_City'])) {
        $City = $area_array['0,' . $rsOrder['Address_Province']][$rsOrder['Address_City']] . ',';
    }

    $Area = '';
    if (!empty($rsOrder['Address_Area'])) {
        $Area = $area_array['0,' . $rsOrder['Address_Province'] . ',' . $rsOrder['Address_City']][$rsOrder['Address_Area']];
    }
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
    <script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]>
<script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/member/js/shop.js'></script>
        <div class="r_nav">
            <ul>
                <li class="cur"><a href="orders.php">订单列表</a></li>
                <li><a href="virtual_orders.php">消费认证</a></li>
            </ul>
        </div>
        <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
        <script type='text/javascript' src='/static/js/plugin/pcas/pcas.js'></script>
        <script language="javascript">$(document).ready(shop_obj.orders_send);</script>
        <div id="orders" class="r_con_wrap">
            <div class="detail_card">
                <form id="order_send_form" method="post" action="?">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="order_info">
                        <tr>
                            <td width="8%" nowrap>订单编号：</td>
                            <td width="92%"><?= date("Ymd", $rsOrder["Order_CreateTime"]) . $rsOrder["Order_ID"] ?></td>
                        </tr>
                        <?php if ($rsOrder["Order_Status"] != 31) { ?>
                            <tr>
                                <td nowrap>物流费用：</td>
                                <td>
                                    <?php if (!isset($Shipping) || empty($Shipping["Price"])) { ?>
                                        免运费
                                    <?php } else {
                                        $fee = $Shipping["Price"];
                                        ?>
                                        ￥<?= $Shipping["Price"] ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td nowrap>订单总价：</td>
                            <td>￥<?= $rsOrder["Order_TotalPrice"] ?></td>
                        </tr>
						<?php if (!empty(floatval($rsOrder["Order_Yebc"]))) { ?>
			<tr>
              <td nowrap>使用余额：</td>
              <td>￥<input type="text" size="10" name="Order_Yebc" value="<?php echo $rsOrder["Order_Yebc"] ?>" disabled="disabled"></td>
            </tr>
			<tr>
              <td nowrap>实际支付：</td>
              <td>￥<input type="text" size="10" name="Order_Fyepay" value="<?php echo $rsOrder["Order_Fyepay"] ?>" disabled="disabled"></td>
            </tr>
			<?php } ?>
                        <?php if ($rsOrder["Coupon_ID"] > 0) { ?>
                            <tr>
                                <td nowrap>优惠详情</td>
                                <td><font style="color:blue;">已使用优惠券</font>(
                                    <?php if ($rsOrder["Coupon_Discount"] > 0) { ?>
                                        享受<?= $rsOrder["Coupon_Discount"] * 10 ?>折
                                    <?php } ?>
                                    <?php if ($rsOrder["Coupon_Cash"] > 0) { ?>
                                        抵现金<?= $rsOrder["Coupon_Cash"] ?>元
                                    <?php } ?>)
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td nowrap>订单时间：</td>
                            <td><?= date("Y-m-d H:i:s", $rsOrder["Order_CreateTime"]) ?></td>
                        </tr>
						<?php if (!empty($rsOrder["Pay_time"])) { ?>
			<tr>
              <td nowrap>付款时间：</td>
              <td><?php echo date("Y-m-d H:i:s",$rsOrder["Pay_time"]) ?></td>
            </tr>
			<?php } ?>
			<?php if (!empty($rsOrder["Finish_time"])) { ?>
			<tr>
              <td nowrap>完成时间：</td>
              <td><?php echo date("Y-m-d H:i:s",$rsOrder["Finish_time"]) ?></td>
            </tr>
			<?php } ?>
                        <tr>
                            <td nowrap>订单状态：</td>
                            <td><?= !empty($Order_Status[$rsOrder["Order_Status"]]) ? $Order_Status[$rsOrder["Order_Status"]] : '' ?></td>
                        </tr>
                        <tr>
                            <td nowrap>支付方式：</td>
                            <td><?= empty($rsOrder["Order_PaymentMethod"]) || $rsOrder["Order_PaymentMethod"] == "0" ? "暂无" : $rsOrder["Order_PaymentMethod"] ?></td>
                        </tr>
                        <?php if ($rsOrder["Order_PaymentMethod"] == "线下付款" || $rsOrder["Order_PaymentMethod"] == "余额充值线下支付") { ?>
                            <tr>
                                <td nowrap>付款信息：</td>
                                <td>转账方式：<?= $PaymentInfo[0] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    转账时间：<?= $PaymentInfo[1] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    转出账号：<?= $PaymentInfo[2] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    姓名：<?= $PaymentInfo[3] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    联系方式：<?= $PaymentInfo[4] ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td nowrap>联系人：</td>
                            <td><?= $rsOrder["Address_Name"] ?></td>
                        </tr>
                        <?php if ($rsOrder["Order_Status"] != 31) { ?>
                            <tr>
                                <td nowrap>手机号码：</td>
                                <td><?= $rsOrder["Address_Mobile"] ?></td>
                            </tr>
                            <tr>
                                <td nowrap>配送方式：</td>
                                <td><?= isset($Shipping) && isset($Shipping["Express"]) ? $Shipping["Express"] : "" ?></td>
                            </tr>
                            <tr>
                                <td nowrap>地址信息：</td>
                                <td><?= $Province . $City . $Area . '【' . $rsOrder["Address_Name"] . '，' . $rsOrder["Address_Mobile"] . '】' . '&nbsp;&nbsp;&nbsp;&nbsp;详细地址: ' . $rsOrder["Address_Detailed"] ?></td>
                            </tr>
                            <tr>
                                <td nowrap>是否需要发票：</td>
                                <td><?= $rsOrder["Order_NeedInvoice"] == 1 ? '<font style="color:#F60">是</font>' : "否" ?></td>
                            </tr>
                            <?php if ($rsOrder["Order_NeedInvoice"] == 1) { ?>
                                <tr>
                                    <td nowrap>发票抬头：</td>
                                    <td><?= $rsOrder["Order_InvoiceInfo"] ?></td>
                                </tr>
                            <?php }
                        } ?>
                        <tr>
                            <td nowrap>订单备注：</td>
                            <td><textarea name="Remark" rows="5" cols="50"><?= $rsOrder["Order_Remark"] ?></textarea></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" class="btn_green" name="submit_button" value="确认"/></td>
                        </tr>
                    </table>
                    <input type="hidden" name="OrderID" value="<?= $rsOrder["Order_ID"] ?>"/>
                    <input type="hidden" name="Name" value="<?= $rsOrder["Address_Name"] ?>"/>
                    <input type="hidden" name="Mobile" value="<?= $rsOrder["Address_Mobile"] ?>"/>
                    <input type="hidden" name="TotalPrice" value="<?= $rsOrder["Order_TotalPrice"] ?>"/>
                    <input type="hidden" name="OrderStatus" value="<?= $rsOrder["Order_Status"] ?>"/>
                </form>
                <div class="blank12"></div>
                <div class="item_info">物品清单</div>
                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="order_item_list">
                    <tr class="tb_title">
                        <td width="20%">图片</td>
                        <td width="35%">产品信息</td>
                        <td width="15%">价格</td>
                        <td width="15%">数量</td>
                        <td width="15%" class="last">小计</td>
                    </tr>
                    <?php
                    if ($rsOrder["Order_Status"] == 31) {
                        $qty = 1;
                        $total = $rsOrder['Order_TotalPrice'] ?>
                        <tr class="item_list" align="center">
                            <td valign="top"><img src="/static/api/shop/skin/default/xianxia.png"/></td>
                            <td align="left" class="flh_180">余额充值线下付款</td>
                            <td>￥<?= $total ?></td>
                            <td><?= $qty ?></td>
                            <td>￥<?= $total ?></td>
                        </tr>
                        <?php
                    } else {
                        $total = 0;
                        $qty = 0;
                        foreach ($CartList as $key => $value) {
                            foreach ($value as $k => $v) {
                                $total += $v["Qty"] * $v["ProductsPriceX"];
                                $qty += $v["Qty"] ?>
                                <tr class="item_list" align="center">
                                    <td valign="top"><img src="<?= $v["ImgPath"] ?>" width="100" height="100"/></td>
                                    <td align="center" class="flh_180"><?= $v["ProductsName"] ?><br>
                                        <?php foreach ($v["Property"] as $Attr_ID => $Attr) {
                                            echo '<dd>' . $Attr_ID . ': ' . $Attr . '</dd>';
                                        } ?>
                                    </td>
                                    <td>￥<?= $v["ProductsPriceX"] ?></td>
                                    <td><?= $v["Qty"] ?></td>
                                    <td>￥<?= $v["ProductsPriceX"] * $v["Qty"] ?></td>
                                </tr>
                            <?php }
                        }
                    } ?>
                    <?php if ($rsOrder["Order_Status"] != 31) { ?>
                        <tr class="total">
                            <td colspan="3">&nbsp;</td>
                            <td><?= $qty ?></td>
                            <td>￥<?= $total ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>