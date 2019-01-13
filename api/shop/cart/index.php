<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/distribute.php');
$base_url = base_url();
$shop_url = shop_url();

if (isset($_GET["UsersID"])) {
    $UsersID = $_GET["UsersID"];
} else {
    echo '缺少必要的参数';
    exit;
}

$rsConfig = shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig, $dis_config);
$owner = get_owner($rsConfig, $UsersID);

//分销级别处理文件
include($_SERVER["DOCUMENT_ROOT"] . '/api/distribute/distribute.php');

$CartList = [];
if (!empty($_SESSION[$UsersID . "User_ID"])) {
    $myRedis = new Myredis($DB, 7);
    $redisCart = $myRedis->getCartListValue($UsersID . 'CartList', $_SESSION[$UsersID . "User_ID"]);
    if (!empty($redisCart)) {
        $CartList = json_decode($redisCart, true);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta content="telephone=no" name="format-detection"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>购物车</title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/api/css/global.css?t=<?= time() ?>' rel='stylesheet' type='text/css'/>
    <link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css'/>
    <link href='/static/api/shop/skin/default/css/cart.css?t=<?= time() ?>' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/api/js/global.js'></script>
    <script type='text/javascript' src='/static/api/shop/js/shop.js?t=<?= time() ?>'></script>
    <script language="javascript">$(document).ready(shop_obj.page_init);</script>
    <script language="javascript">
        var ajax_url = '<?= $base_url ?>api/<?= $UsersID ?>/shop/cart/ajax/';
        $(document).ready(shop_obj.cart_init);
    </script>
    <style>
        .chkbox {
            width: 30px;
            height: 20px;
        }
    </style>
</head>
<body>
<div id="cart">
    <div class="cart_title"><b></b>购物车</div>
    <?php if (empty($CartList)) { ?>
        <div class="empty">
            <img src="/static/api/shop/skin/default/images/cart.png"/><br/>购物车空的，赶快去逛逛吧！
        </div>
    <?php } else {
        $total = $showprice = $qty = 0;
        $i = 0;
        foreach ($CartList as $BizID => $BizCart) {
            $i++;
            $rsBiz = $DB->GetRs("biz", "Biz_Name", "where Biz_ID=" . $BizID);
            if (!$rsBiz) {
                continue;
            }
            ?>
            <?php if ($i > 1) { ?>
                <div class="bizcart_dline"></div>
            <?php } ?>
            <div id="biz_cart_<?= $BizID ?>" class="bizcart">
                <div class="biz_title">
                    <a href="/api/<?= $UsersID ?>/biz/<?= $BizID ?>/"><?= $rsBiz["Biz_Name"] ?></a>
                </div>

                <?php
                foreach ($BizCart as $ProductsID => $Products) {
                    foreach ($Products as $CartID => $Cart) {
                        if (count($Cart["Property"])) {
                            $showprice = $Cart["Property"]['shu_pricesimp'];
                        } else {
                            $showprice = $Cart["ProductsPriceX"];
                        }

                        //计算优惠
                        if (isset($Cart['user_curagio']) && !empty($Cart['user_curagio'])) {
                            $showprice = $showprice * (1 - $Cart['user_curagio'] / 100);
                        }

                        $qty += $Cart["Qty"];
                        $total += sprintf("%.2f",substr(sprintf("%.3f", $Cart["Qty"] * $showprice), 0, 4));
                        $showprice = getFloatValue($showprice, 2);
                        ?>
                        <div class="item">
                            <div class="img">
                                <a href="/api/<?= $UsersID ?>/shop/products/<?= $ProductsID ?>/"><img src="<?= $Cart["ImgPath"] ?>" width="100" height="100"></a>
                            </div>
                            <!--
                            <div class="del">
                                  <input type="checkbox" name="cart[]" value="" class="chkbox"/>
                            </div>
                             -->

                            <dl class="info">
                                <dd class="name"><a href="/api/<?= $UsersID ?>/shop/products/<?= $ProductsID ?>/"><?= $Cart["ProductsName"] ?></a></dd>
                                <?php
                                if (!empty($Cart["Property"])) {
                                    echo '<dd class="property">' . $Cart["Property"]['shu_value'] . '</dd><dd contid="' . $Cart["Property"]['shu_conut'] . '">库存' . $Cart["Property"]['shu_conut'] . '件</dd>';
                                }
                                ?>
                                <dd class="price"<?= empty($Cart["Property"]) ? ' style="margin-top:20px;"' : '' ?>><span BizID="<?= $BizID ?>" ProductsID="<?= $ProductsID ?>" CartID="<?= $CartID ?>"><i class="qty_sub">-</i><input type="text" name="Qty" value="<?= $Cart["Qty"] ?>"/><i class="qty_add">+</i><div class="clear"></div></span><font style="font-size:14px;">￥</font><?= $showprice ?></dd>
                                <dd>
                                    <div class="del">
                                        <div BizID="<?= $BizID ?>" ProductsID="<?= $ProductsID ?>" CartID="<?= $CartID ?>">
                                            <img src="/static/api/shop/skin/default/images/delete.png"/>
                                        </div>
                                    </div>
                                </dd>
                            </dl>
                            <div class="clear"></div>
                        </div>
                    <?php }
                } ?>
            </div>
        <?php } ?>
        <div class="cart_footer"></div>
        <div class="cart_total">
            <a href="javascript:void(0);" class="gotocheck">去结算</a>共计： ￥ <span><?= $total ?></span>
        </div>
    <?php } ?>
</div>
<?php require_once('../footer.php'); ?>
</body>
</html>