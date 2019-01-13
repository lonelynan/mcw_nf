<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/lib_products.php');

$Status = empty($_GET["Status"]) ? 0 : $_GET["Status"];
//如果设置了action
if (!empty($_GET['action'])) {
    $action = $_GET['action'];
    $Order_ID = $_GET['OrderID'];

    if ($action == 'delete') {  //暂不能删除此方法，其他地方有使用

        //若是分销订单，删除分销记录
        if (is_distribute_order($DB, $UsersID, $Order_ID)) {
            delete_distribute_record($DB, $UsersID, $Order_ID);
        }
        $condition = "where Users_ID='" . $UsersID . "' and User_ID='" . $_SESSION[$UsersID . "User_ID"] . "' and Order_ID=" . $Order_ID;
        $rsOrder = $DB->getRs('user_order', 'Integral_Consumption', $condition);
        mysql_query("BEGIN"); //开始事务定义

        $Flag_a = true;

        $condition = "Users_ID='" . $UsersID . "' and User_ID='" . $_SESSION[$UsersID . "User_ID"] . "' and Order_ID=" . $Order_ID;
        $Flag_b = $DB->Del("user_order", $condition);


        if ($Flag_a && $Flag_b) {
            mysql_query("COMMIT"); //执行事务
            layerMsg("取消订单成功", "/api/{$UsersID}/shop/member/status/1/");
        } else {
            mysql_query("ROLLBACK"); //回滚事务
            layerMsg("取消订单失败");
        }
        exit;
    }
}

$_STATUS = array('<font style="color:#F00; font-size:12px;">申请中</font>', '<font style="color:#F60; font-size:12px;">卖家同意</font>', '<font style="color:#0F3; font-size:12px;">买家发货</font>', '<font style="color:#600; font-size:12px;">卖家收货并确定退款价格</font>', '<font style="color:blue; font-size:12px;">完成</font>', '<font style="color:#999; font-size:12px; text-decoration:line-through;">卖家拒绝退款</font>');

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
    <meta content="telephone=no" name="format-detection"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>个人中心</title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/layer_mobile/layer.js'></script>
    <script type='text/javascript' src='/static/api/js/global.js'></script>
    <script type='text/javascript' src='/static/api/shop/js/shop.js?t=6'></script>
    <script language="javascript">
        //禁止iframe
        if (window != window.top) {
            window.top.location.replace(window.location)
        }

        var base_url = '<?php echo $base_url;?>';
        var UsersID = '<?php echo $UsersID;?>';
        $(document).ready(shop_obj.page_init);
    </script>
    <style>
        .msg_layer div.layui-m-layercont {color: #fff;}
    </style>
</head>

<body>
<div class="top_back">
    <a href="/api/<?= $UsersID ?>/shop/member/"><img src="/static/api/shop/skin/default/images/back_1.png"></a>
    <span>订单列表</span>
</div>
<div id="shop_page_contents">
    <div id="cover_layer"></div>
    <link href='/static/api/shop/skin/default/css/member.css?t=<?php echo time(); ?>' rel='stylesheet' type='text/css'/>
    <ul id="member_nav">
        <li class="<?php echo $Status == 0 ? "cur" : "" ?>"><a href="/api/<?php echo $UsersID ?>/shop/member/status/0/">待确认</a></li>
        <li class="<?php echo $Status == 1 ? "cur" : "" ?>"><a href="/api/<?php echo $UsersID ?>/shop/member/status/1/">待付款</a></li>
        <li class="<?php echo $Status == 2 ? "cur" : "" ?>"><a href="/api/<?php echo $UsersID ?>/shop/member/status/2/">已付款</a></li>
        <li class="<?php echo $Status == 3 ? "cur" : "" ?>"><a href="/api/<?php echo $UsersID ?>/shop/member/status/3/">已发货</a></li>
        <li class="<?php echo $Status == 4 ? "cur" : "" ?>"><a href="/api/<?php echo $UsersID ?>/shop/member/status/4/">已完成</a></li>
    </ul>
    <div id="order_list">
        <?php
        if ($Status == 1) {
            $DB->get("user_order", "*", "where Users_ID='" . $UsersID . "' and User_ID='" . $_SESSION[$UsersID . "User_ID"] . "' and (Order_Type='shop' or Order_Type='offline_charge') and (Order_Status= 1 or Order_Status=31) order by Order_CreateTime desc");
        } elseif ($Status == 4) {
            $DB->get("user_order", "*", "where Users_ID='" . $UsersID . "' and User_ID='" . $_SESSION[$UsersID . "User_ID"] . "' and (Order_Type='shop' or Order_Type='offline_charge') and Order_Status= 4 order by Order_CreateTime desc");
        } else {
            $DB->get("user_order", "*", "where Users_ID='" . $UsersID . "' and User_ID='" . $_SESSION[$UsersID . "User_ID"] . "' and Order_Type='shop' and Order_Status=" . $Status . "  order by Order_CreateTime desc");
        }
        $lists = [];
        while ($r = $DB->fetch_assoc()) {
            $lists[] = $r;
        }
        foreach ($lists as $rsOrder) {
            $CartList = json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]), true);
            $lists_back = array();
            if ($rsOrder["Is_Backup"] == 1) {
                $condition = "where Users_ID='" . $UsersID . "' and Order_ID=" . $rsOrder["Order_ID"] . " and Back_Type='shop'";
                $DB->Get("user_back_order", "*", $condition);
                while ($b = $DB->fetch_assoc()) {
                    $lists_back[] = $b;
                }
            }
            $shipping = json_decode(htmlspecialchars_decode($rsOrder['Order_Shipping']), true);
            ?>
            <div class="item">
                <h1> 订单号：
                    <?php if ($rsOrder["Order_Type"] == 'offline_charge') { ?>
                        <a href="javascript:;"><?= date("Ymd", $rsOrder["Order_CreateTime"]) . $rsOrder["Order_ID"] ?></a>
                    <?php } else { ?>
                        <a href="<?= '/api/' . $UsersID . '/shop/member/detail/' . $rsOrder["Order_ID"] . '/' ?>"><?= date("Ymd", $rsOrder["Order_CreateTime"]) . $rsOrder["Order_ID"] ?></a>
                    <?php } ?>
                    （<strong class="fc_red">￥<?= $rsOrder["Order_TotalPrice"] ?></strong>）<?= $rsOrder["Coupon_ID"] == 0 ? '' : '<font style="color:blue; font-size:12px;">已使用优惠券</font>' ?>
                </h1>
                <?php if (count($shipping) > 0 && !empty($rsOrder["Order_ShippingID"]) && empty($shipping['Tag'])) { ?>
                    <?php
                    if (!empty($shipping['Express'])) {
                        $shipping_trace = 'http://m.kuaidi100.com/index_all.html?type=' . $shipping['Express'] . '&postid=' . $rsOrder["Order_ShippingID"] . '&callbackurl=' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
                        $Shipping_Express = !empty($shipping['Express']) ? $shipping['Express'] . '-' : '';
                        $Express_Name = !empty($shipping['Express']) ? $shipping['Express'] : '';
                    } else {
                        $shipping_trace = 'javascript:void(0)';
                        $Shipping_Express = '';
                    }
                    ?>
					<h1 style='height:30px;line-height:30px;'><a href="/api/<?=$rsOrder['Users_ID'] ?>/shop/member/viewshipping/<?=$rsOrder['Order_ID'] ?>/"><?php echo $Shipping_Express.$rsOrder["Order_ShippingID"] ?></a><a href="/api/<?=$rsOrder['Users_ID'] ?>/shop/member/viewshipping/<?=$rsOrder['Order_ID'] ?>/"><span style='background-color:#DE2337;padding:3px 10px;border-radius:5px;color:#FFF;margin-left:15px;'>快递跟踪</span></a> </h1>
                    <!--
					<h1 style='height:30px;line-height:30px;'>
                        <a href="<?= $shipping_trace ?>"><?= $Shipping_Express . $rsOrder["Order_ShippingID"] ?></a>
                        <a href="<?= 'http://m.kuaidi100.com/index_all.html?type=' . $Express_Name . '&postid=' . $rsOrder["Order_ShippingID"] . '&callbackurl=' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING']; ?>"><span style='background-color:#DE2337;padding:3px 10px;border-radius:5px;color:#FFF;margin-left:15px;'>快递跟踪</span></a>
                    </h1>-->
                <?php } ?>

                <?php
                if (isset($CartList)) {
                    foreach ($CartList as $key => $value) {
                        foreach ($value as $k => $v) {
							if((strpos($v["ImgPath"], 'http') === false && !file_exists($_SERVER['DOCUMENT_ROOT'] . $v["ImgPath"])) || empty($v["ImgPath"])){
								$v["ImgPath"] = "/static/images/none.png";
							}
                            if ($rsOrder['Order_Type'] == 'offline') {
                                $price = $rsOrder['Order_TotalPrice'];
                                $rsBiz = Biz::where("Biz_ID", $rsOrder['Biz_ID'])->first(["Biz_Name"])->toArray(); ?>
                                <div class="pro">
                                    <div class="img"><img src="<?= $v["ImgPath"] ?>" width="70" height="70"></div>
                                    <dl class="info">
                                        <dd class="name">来自<?= $rsBiz["Biz_Name"] ?>的门店产品消费</dd>
                                        <dd>￥<?= $price . '×' . $v["Qty"] . '=￥' . $price * $v["Qty"] ?></dd>
                                        <?php
                                        //if(($rsOrder['Order_Status'] == 3 && $rsOrder['Order_IsVirtual'] == 0) || ($rsOrder['Order_Status'] == 2 && $rsOrder['Order_IsVirtual'] == 1)){
                                        if ($rsOrder['Order_Status'] == 2 || $rsOrder['Order_Status'] == 3) { ?>
                                            <dd style="height:26px">
                                                <a href="<?= $base_url . 'api/' . $UsersID . '/shop/member/backup/' . $rsOrder["Order_ID"] . '_' . $key . '_' . $k . '/?love' ?>" style="display:block; width:70px; height:26px; line-height:24px; color:#FFF; background:#F60; border-radius:8px; text-align:center; font-size:12px; font-weight:normal; float:right"><?= $rsOrder['Order_Status'] == 2 ? '申请退款' : '申请退货' ?></a>
                                            </dd>
                                        <?php } ?>
                                    </dl>
                                    <div class="clear"></div>
                                </div>
                            <?php } elseif ($rsOrder["Order_Type"] == 'offline_charge') {
                                $Profit = isset($v['ProductsProfit']) && $v['ProductsProfit'] > 0 && $v['ProductsProfit'] <= 1 ? (float)$v['ProductsProfit'] : 1;
                                $price = sprintf("%.2f", $Profit * $v["ProductsPriceX"]) <= 0.01 ? 0.01 : sprintf("%.2f", $Profit * $v["ProductsPriceX"]);
                                ?>
                                <div class="pro">
                                    <div class="img"><a href="javascript:;"><img src="<?= $v["ImgPath"] ?>" width="70" height="70"></a></div>
                                    <dl class="info">
                                        <dd class="name"><a href="#"><?= $v["ProductsName"] ?></a></dd>

                                        <dd>￥<?= $price . '×' . $v["Qty"] . '=￥' . $price * $v["Qty"] ?></dd>
                                    </dl>
                                    <div class="clear"></div>
                                </div>
                            <?php } else {
                                $price = (!empty($v['user_curagio']) && $v['user_curagio'] > 0 && $v['user_curagio'] <= 100 ? (100 - $v['user_curagio']) / 100 : 1) * (isset($v["Property"]['shu_pricesimp']) ? $v["Property"]['shu_pricesimp'] : $v["ProductsPriceX"]);
                                ?>
                                <div class="pro">
                                    <div class="img"><a href="/api/<?= $UsersID ?>/shop/products/<?= $key ?>/?love"><img src="<?= $v["ImgPath"] ?>" width="70" height="70"></a></div>
                                    <dl class="info">


                                        <dd class="name"><a href="/api/<?= $UsersID ?>/shop/products/<?= $key ?>/?love"><?= $v["ProductsName"] ?></a></dd>
                                        <dd>￥<?= $price . '×' . $v["Qty"] . '=￥' . $price * $v["Qty"] ?></dd>
                                        <dd><?= empty($v["Property"]["shu_value"]) ? '' : $v["Property"]["shu_value"] ?></dd>
                                        <?php
                                        // if(($rsOrder['Order_Status'] == 3 && $rsOrder['Order_IsVirtual'] == 0) || ($rsOrder['Order_Status'] == 2 && $rsOrder['Order_IsVirtual'] == 1)){
                                        if ($rsOrder['Order_Status'] == 2 || $rsOrder['Order_Status'] == 3) { ?>
                                            <dd style="height:26px"><a href="<?= $base_url . 'api/' . $UsersID . '/shop/member/backup/' . $rsOrder["Order_ID"] . '_' . $key . '_' . $k . '/?love' ?>" style="display:block; width:70px; height:26px; line-height:24px; color:#FFF; background:#F60; border-radius:8px; text-align:center; font-size:12px; font-weight:normal; float:right"><?= $rsOrder['Order_Status'] == 2 ? '申请退款' : '申请退货' ?></a></dd>
                                        <?php } ?>
                                    </dl>
                                    <div class="clear"></div>
                                </div>
                            <?php }
                        }
                    }
                }

                if (!empty($lists_back)) {
                    foreach ($lists_back as $item) {
                        $CartList_back = json_decode(htmlspecialchars_decode($item["Back_Json"]), true);
                        $price = (!empty($CartList_back['user_curagio']) && $CartList_back['user_curagio'] > 0 && $CartList_back['user_curagio'] <= 100 ? (100 - $CartList_back['user_curagio']) / 100 : 1) * (isset($CartList_back["Property"]['shu_pricesimp']) ? $CartList_back["Property"]['shu_pricesimp'] : $CartList_back["ProductsPriceX"]); ?>
                        <div class="pro">
                            <div class="img"><a href="/api/<?= $UsersID ?>/shop/products/<?= $item["ProductID"] ?>/?love"><img src="<?= $CartList_back["ImgPath"] ?>" width="70" height="70"></a></div>
                            <dl class="info">
                                <dd class="name"><a href="/api/<?= $UsersID ?>/shop/products/<?= $item["ProductID"] ?>/?love"><?= $CartList_back["ProductsName"] ?></a></dd>
                                <dd>￥<?= $price . '×' . $CartList_back["Qty"] . '=￥' . $price * $CartList_back["Qty"] ?></dd>
                                <dd><?= empty($CartList_back["Property"]["shu_value"]) ? '' : $CartList_back["Property"]["shu_value"] ?></dd>
                                <dd style="height:26px; font-size:12px;"><?= empty($_STATUS[$item["Back_Status"]]) ? '' : $_STATUS[$item["Back_Status"]] ?><a href="<?= $base_url . 'api/' . $UsersID . '/shop/member/backup/detail/' . $item["Back_ID"] . '/' ?>" style="display:block; width:70px; height:26px; line-height:24px; color:#FFF; background:#696969; border-radius:8px; text-align:center; font-size:12px; font-weight:normal; float:right">退款详情</a></dd>
                            </dl>
                            <div class="clear"></div>
                        </div>
                    <?php }
                }
                ?>

                <?php if ($rsOrder['Order_Status'] == 0) { ?>
                    <div class="button_panel">
                        <div class="cancel"><a onclick='return cancelorder(<?php echo '"' . $UsersID . '",' . $rsOrder["Order_ID"]; ?>,this);'>取消</a></div>
                        <div class="clear"></div>
                    </div>
                <?php } ?>

                <?php if ($rsOrder['Order_Status'] == 1 || $rsOrder['Order_Status'] == 31) { ?>
                    <div class="button_panel">
                        <?php if ($rsOrder['Order_Status'] == 1) { ?>
                            <div class="cancel" style="padding:0px"><a onclick='return cancelorder(<?php echo '"' . $UsersID . '",' . $rsOrder["Order_ID"]; ?>,this);'>取消</a></div>
                        <?php } ?>
                        <?php if (($rsOrder["Order_PaymentMethod"] == "线下支付" || $rsOrder["Order_PaymentMethod"] == "余额充值线下支付") && !empty($rsOrder["Order_PaymentInfo"])) { ?>
                            <div class="payment" style="padding:0px"><a href="#">已付款等待卖家确认</a></div>
                        <?php } else { ?>
                            <div class="payment" style="padding:0px"><a href="<?= $base_url ?>api/<?= $UsersID ?>/shop/cart/payment/<?= $rsOrder["Order_ID"] ?>/">付款</a></div>
                        <?php } ?>
                        <div class="clear"></div>
                    </div>
                <?php } ?>

                <?php if ($rsOrder['Order_Status'] == 3 && !empty($CartList)) { ?>
                    <div class="button_panel">
                        <div class="confirm_receive"><a class="confirmreceive" href="javascript:void(0)" Order_ID="<?= $rsOrder['Order_ID'] ?>" style="margin:0px 0px 0px 8px">确认收货</a></div>
                    </div>
                <?php } ?>
                <?php if ($rsOrder['Order_Status'] == 4 && empty($lists_back)) { ?>
                    <?php if ($rsOrder['Is_Commit'] == 0 && $rsOrder['Is_Backup'] == 0) { ?>
                        <div class="button_panel">
                            <div class="payment"><a href="<?= $base_url ?>api/<?php echo $UsersID ?>/shop/member/commit/<?php echo $rsOrder["Order_ID"] ?>/">评论</a></div>
                            <div class="clear"></div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
<?php
/*require_once('../skin/distribute_footer.php');*/
?>
</body>
</html>
<script language="javascript">
    function cancelorder(usersid, orderid, that) {
        layer.open({
            content: '确定要删除吗？'
            , btn: ['确定', '取消']
            , yes: function (index) {
                $.ajax({
                    type: 'post',
                    url: '/api/' + usersid + '/shop/cart/ajax/',
                    data: {action: 'del', Order_ID: orderid},
                    success: function (data) {
                        layer.close(index);
                        if (data.status == 1) {
                            $(that).parents('.item').remove();
                            layer.open({
                                content: data.msg,
                                className: 'msg_layer',
                                skin: 'msg',
                                time: 1
                            })
                        } else {
                            layer.open({
                                content: data.msg,
                                btn: '确定'
                            });
                        }
                    },
                    dataType: 'json'
                });
            }
        });
    }
</script>