<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/shipping.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/flow.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/library/smarty.php');
use Illuminate\Database\Capsule\Manager as DB;

$base_url = base_url();
$shop_url = shop_url();

if (isset($_GET["UsersID"])) {
    $UsersID = $_GET["UsersID"];
} else {
    layerOpen('缺少必要的参数');
    exit;
}

/****************** 设置smarty模板 start ***********************/
$smarty->left_delimiter = "{{";
$smarty->right_delimiter = "}}";
$template_dir = $_SERVER["DOCUMENT_ROOT"] . '/api/shop/html';
$smarty->template_dir = $template_dir;
/****************** 设置smarty模板 end ***********************/
if (isset($_GET['cart_key']) && !empty($_GET['cart_key'])) {
    $_SESSION[$UsersID . "HTTP_REFERER"] = "/api/" . $UsersID . "/shop/cart/checkout_virtual/?love&cart_key=".$_GET['cart_key'];
} else {
    $_SESSION[$UsersID."HTTP_REFERER"] = "/api/".$UsersID."/shop/cart/checkout_virtual/?love";
}
$rsConfig = shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig,$dis_config);
//用户授权登录
$is_login = 1;
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/library/wechatuser.php');
$User_ID = !empty($_SESSION[$UsersID . "User_ID"]) ? $_SESSION[$UsersID . "User_ID"] : 0;

$rsUser = DB::table('user')->where(['Users_ID' => $UsersID, 'User_ID' => $User_ID])->first();
/*************会员价计算 start **************/
$user_curagio = get_user_curagio($UsersID, $User_ID);
/*************会员价计算 end **************/

/********************* 计算积分 start *************************/
//获取产品列表
$myRedis = new Myredis($DB, 7);
$cartKey = isset($_GET['cart_key']) ? $_GET['cart_key'] : 'Virtual';
$cart_key = isset($_GET['cart_key']) ? $UsersID.$_GET['cart_key'] : $UsersID . "Virtual";
$redisCart = $myRedis->getCartListValue($cart_key, $User_ID);
$CartList = !empty($redisCart) ? json_decode($redisCart, true) : [];
// 获得产品的ID 计算积分
$Integration = 0;
$biz_info = [];
//产品是否有门店
$storeFlag = false;
if(!empty($CartList)){

    foreach ($CartList as $biz_id => $products) {
        //获取对应商家信息
        $rsBiz = $DB->GetRs('biz', 'Biz_Account, Biz_Name, Biz_Logo', 'where Biz_ID = ' . $biz_id);
        if (empty($rsBiz)) {
            unset($CartList[$biz_id]);
            continue;
        }

        $biz_info[$biz_id] = $rsBiz;
        foreach ($products as $pro_id => $vv) {
            foreach ($vv as $attr_id => $v) {
                if (!empty($v['ProductsStore'])) {
                    $storeFlag = true;
                }
                $ProductsIntegration = $DB->GetRs("shop_products", "Products_Integration", "where Products_ID=" . $pro_id);
                $CartList[$biz_id][$pro_id][$attr_id]['ProductsIntegration'] = $ProductsIntegration['Products_Integration'];
                $Integration += $v['Qty'] * $ProductsIntegration['Products_Integration'];
            }
        }
    }
}else{
    header("location:/api/".$UsersID."/shop/cart/?love");
    exit;
}
/********************* 计算积分 end *************************/
if ($cartKey == 'PTCartList') {
    $user_curagio = 0;
}
$total_price = 0;
$toal_shipping_fee = 0;
if (count($CartList) > 0) {
    $info = get_order_total_info($UsersID, $CartList, $rsConfig, -1, 0, $user_curagio);
    $total_price = $info['total'];
    $total_shipping_fee = $info['total_shipping_fee'];
} else {
    header("location:/api/" . $UsersID . "/shop/cart/?love");
    exit;
}

/*商品信息汇总*/
$total_price = $total_price + $info['total_shipping_fee'];

$recieve = 0;
$pids = [];

/*print_r($biz_company_dropdown);
print_r($CartList);
print_r($biz_info);
print_r($info);
die;*/
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <title>提交订单——虚拟</title>
    <link href="/static/api/js/mui/mui.min.css" rel="stylesheet"/>
    <link href="/static/api/css/checkout.css" rel="stylesheet"/>
    <script src="/static/js/jquery-1.7.2.min.js"></script>
    <script src="/static/js/layer/mobile/layer.js"></script>
    <script type='text/javascript' src='/static/api/js/global.js?t=<?=time()?>'></script>
    <script type='text/javascript' src='/static/api/shop/js/flow.js?t=<?=time()?>'></script>
    <script type='text/javascript' src='/static/api/shop/js/toast.js?t=<?=time();?>'></script>
    <script>
        //门店js插入使用, 在flow.js中
        var new_checkout = 1;
        var base_url = '<?=$base_url?>';
        var Users_ID = '<?=$UsersID?>';
        $(document).ready(function(){
            flow_obj.checkout_init();
        });
    </script>
</head>

<body>
<header class="mui-bar mui-bar-nav">
    <a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></a>
    <h1 class="mui-title">提交订单</h1>
</header>
<div class="mui-content">

    <form id="checkout_form" action="<?= $base_url ?>api/<?= $UsersID ?>/shop/cart/" style="display: inline;">
        <input type="hidden" name="action" value="checkout"/>
        <input type="hidden" name="checkout" value="checkout_virtual" />

        <!--购买商品列表-->
        <ul class="Product-list">
            <?php foreach ($CartList as $Biz_ID => $BizCartList) {
            if (!empty($info[$Biz_ID]['error'])) { ?>
                <script>
                    layer.alert("<?= $info[$Biz_ID]['msg'] ?>", function () {
                        history.back();
                    });
                </script>
            <?php } ?>
                <li class="Product">
                    <!--店铺信息-->
                    <div class="user-shop">
                        <img class="user-photo left" src="<?= !empty($biz_info[$Biz_ID]['Biz_Logo']) ? $biz_info[$Biz_ID]['Biz_Logo'] : '/static/api/shop/skin/default/images/face.jpg' ?>">
                        <span class="user-shop-name left"><?= !empty($biz_info[$Biz_ID]['Biz_Name']) ? $biz_info[$Biz_ID]['Biz_Name'] : (!empty($biz_info[$Biz_ID]['Biz_Account']) ? $biz_info[$Biz_ID]['Biz_Account'] : '') ?></span>
                    </div>
                    <!--店铺信息-end-->

                    <!--商品信息列表-->
                    <ul class="mui-table-view mui-table-view-chevron" Biz_ID="<?= $Biz_ID ?>">
                        <?php
                        $Sub_Totalall = 0;
                        $Sub_Totalall_souce = 0;
                        $Qty = 0
                        ?>
                        <?php
                        foreach ($BizCartList as $Products_ID => $Product_List) {
                            $pids[] = $Products_ID;
                            $condition = "where Users_ID = '" . $UsersID . "' and Products_ID =" . $Products_ID;
                            $rsProduct = $DB->GetRs('shop_products', 'Products_Count, Products_IsRecieve', $condition);
                            if($rsProduct["Products_IsRecieve"]== 1){
                                $recieve = 1;
                            }
                            ?>

                            <input type="hidden" id="Products_Count_<?=$Products_ID?>" value="<?=$rsProduct['Products_Count']?>" />

                            <?php
                            foreach ($Product_List as $attr_id => $product) {
                                //产品单价
                                if (empty($product['Property'])) { //无属性
                                    $shu_price = $product['ProductsPriceX'];
                                } else {    //有属性
                                    $shu_price = $product['Property']['shu_pricesimp'];
                                }
                                //优惠之前的价格
                                $Sub_Totalall_souce += $shu_price * $product['Qty'];

                                //计算优惠
                                if (isset($user_curagio) && !empty($user_curagio)) {
                                    $shu_price = $shu_price * (1 - $user_curagio / 100);
                                    $shu_price = $shu_price <= 0.01 ? 0.01 : sprintf("%.2f",substr(sprintf("%.3f", $shu_price), 0, -1));
                                }

                                //价格计算
                                $Sub_Totalall += (float)$shu_price * (int)$product['Qty'];

                                //产品数量
                                $Qty += $product['Qty'];
                                ?>
                                <li class="bc-f7">
                                    <div class="Product-img">
                                        <img src="<?= $product['ImgPath'] ?>">
                                    </div>
                                    <div class="product-information">
                                        <p class="product-name"><?= $product['ProductsName'] ?></p>
                                        <p class="product-color"><?= isset($product['Productsattrstrval']) ? $product['Productsattrstrval'] : '' ?></p>
                                        <div class="product-num-price">
                                            <span class="product-price">¥<span class="price"><?= $shu_price ?></span></span>
                                            <span class="right">X<span class="product-num"><?= $product['Qty'] ?></span></span>
                                        </div>
                                    </div>
                                </li>
                            <?php }
                        } ?>

                        <!-- 选择使用优惠券 start -->
                        <?php
                        //获取优惠券
                        $coupon_info = get_useful_coupons($User_ID, $UsersID, $Biz_ID, $Sub_Totalall);
                        if (!empty($rsConfig['Integral_Buy'])) {
                            $Products_Integration = Integration_get($Biz_ID, $rsConfig, $rsUser['User_Integral'], $Sub_Totalall_souce);
                            $diyong_m = $Products_Integration / $rsConfig['Integral_Buy'];
                        }
                        if ($coupon_info['num'] > 0 && !empty($coupon_info['lists'])) {
                            $coupon_lists = $coupon_info['lists'];
                            foreach ($coupon_lists as $key => $item) {
                                $coupon_lists[$key]['Price'] = $item["Coupon_UseType"] == 0 ? $item["Coupon_Discount"] : $item["Coupon_Cash"];
                            }
                            ?>
                            <li class="mui-table-view-cell tc-bt ticket-box ticket_<?= $Biz_ID ?>" onclick="tcFn(this, 'ticket_<?= $Biz_ID ?>')">
                                <span class="mui-navigate-right">
                                    使用优惠券：
                                </span>
                                <span class="right fc-r">可使用优惠券</span>
                            </li>

                            <div class="tc-main panel-footer" id="ticket_<?= $Biz_ID ?>">
                                <p class="title">店铺优惠</p>
                                <ul class="mui-table-view">
                                    <?php foreach ($coupon_lists as $k => $v) { ?>
                                        <li>
                                            <div class="mui-input-row mui-radio mui-left">
                                                <label><?= $v['Subject'] ?></label>
                                                <input type="radio" name="CouponID[<?= $v['Biz_ID'] ?>]" value="<?= $v['Coupon_ID'] ?>" UseType="<?= $v['Coupon_UseType'] ?>" Price="<?= $v['Price'] ?>" bizid="<?= $v['Biz_ID'] ?>" check="0" class="coupon">
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                                <div class="OKbt">取消</div>
                            </div>
                        <?php } ?>
                        <!-- 选择使用优惠券 end -->

                        <!-- 虚拟订单备注 start -->
                        <?php
                        $cardcount = $DB->GetRs('shop_virtual_card','count(Card_Id) as num','where Products_Relation_ID in('.implode(',',$pids).')');
                        if ($recieve == 1 && $cardcount['num'] == 0) { ?>
                            <input type="hidden" name="Mobile" value="<?= !empty($_SESSION[$UsersID."User_Mobile"]) ? $_SESSION[$UsersID."User_Mobile"] : '' ?>" />
                        <?php } else { ?>
                            <li class="mui-table-view-cell seller-msg virtual">
                                <span class="mui-navigate-right">
                                    接收短信手机：
                                </span>
								<input type="hidden" name="xunipro" value="2" />
                                <input type="text" name="Mobile" value="<?= !empty($_SESSION[$UsersID."User_Mobile"]) ? $_SESSION[$UsersID."User_Mobile"] : '' ?>" pattern="[0-9]*" maxlength="11" placeholder="填写接收短信手机号" id="Mobile_Input" notnull>
                            </li>
                        <?php } ?>
                        <li class="mui-table-view-cell seller-msg virtual">
                            <span class="mui-navigate-right">
                                订单备注信息：
                            </span>
                            <textarea name="Remark[<?= $Biz_ID ?>]" maxlength="50" placeholder="填写内容已和卖家协商确认"></textarea>
                        </li>
                        <!-- 虚拟订单备注 end -->
						<?php 
							$Sub_Totalall = bcmul($Sub_Totalall,100);
							$Sub_Totalall = bcmul($Sub_Totalall,0.01,2);
							$total_price = bcmul($total_price,100);
							$total_price = bcmul($total_price,0.01,2);
						?>
                        <li class="mui-table-view-cell">
                            <div class="right">
                                共<span><?= $Qty ?></span>件商品
                                <span class="allprice">小计：<span class="fc-r">¥</span></span><span class="price sub_total_price_<?= $Biz_ID ?>"><?=$Sub_Totalall?></span>
                            </div>
                        </li>
                    </ul>
                </li>
            <?php } ?>

            <?php if (!empty($Integration)) { ?>
                <li class="mui-table-view-cell">
                    <div class="left">
                        此次购物可获得<span id="total_integral" class="fc-r"><?=$Integration?></span>个积分
                    </div>
                </li>
            <?php } ?>
        </ul>
        <!--购买商品列表-end-->
        <div class="bottom-box">
            <div class="right">
                <span>合计：</span><span class="fc-r">¥</span><span class="price total_price"><?=$total_price?></span>
                <input type="button" id="submit-btn" value="提交订单">
            </div>
        </div>

        <input type="hidden" id="coupon_value" value="0" placeholder="优惠金额"/>
        <input type="hidden" name="Order_Shipping" value="0"/>
        <input type="hidden" name="cart_key" value="<?=$cartKey?>" id="cart_key"/>
        <input type="hidden" name="recieve" value="<?= $recieve ?>" />
    </form>
</div>

<!--点击滑出 遮罩-->
<div class="tc-bg"></div>
<!--点击滑出-end-->

<script src="/static/api/js/mui/mui.min.js"></script>
<script>
    //保留两位小数
    function price_format(price) {
        price = Math.round(parseFloat(price) * 100) / 100;
        var price = price.toString();
        var rs = price.indexOf('.');
        if (rs < 0) {
            rs = price.length;
            price += '.';
        }
        while (price.length <= rs + 2) {
            price += '0';
        }
        return price;
    }

    //动态修改遮罩的高度
    window.onload = function () {
        var winH = document.documentElement.clientHeight;
        var tc_bg = document.getElementsByClassName("tc-bg")[0];
        tc_bg.style.height = winH + "px";
    };

    //产品图片正方形化
    $(".Product-img").each(function (i) {
        $(this).css('height', $(this).width());
    });

    //是否上门自提
    $('.self_get_switch').click(function () {
        var me = $(this);

        //判断是开关状态（开启或关闭）
        if (me.hasClass('mui-active')) {
            //open
            $('input[name="Self_Get"]').prop('checked', true);
        } else {
            //close
            $('input[name="Self_Get"]').prop('checked', false);
        }
    });

    mui.init({
        swipeBack: true //启用右滑关闭功能
    });


    //选择弹窗
    var clickPart;
    function tcFn(that, pop) {
        clickPart = that;
        //遮罩弹出
        $(".tc-bg").fadeIn();
        //显示选择弹窗
        $('.tc-main#' + pop).animate({
            bottom: "0px"
        });
    }

    //隐藏遮罩
    function tcHide() {
        $(".tc-bg").fadeOut();
        $(".tc-main").animate({
            bottom: "-280px"
        })
    }

    //遮罩隐藏
    $(".tc-bg, .OKbt").click(function () {
        tcHide();
    });

    /*//选择后计算价格
    function checkout_change_ajax() {
        $.ajax({
            type: 'post',
            url: '/api/' + Users_ID + '/shop/cart/ajax/',
            data: $('form').serialize() + '&action=checkout_change',
            success: function (data) {
                console.log(data);
                if (data.errorCode == 0) {

                }
            },
            dataType: 'json'
        });
    }*/

    $('.tc-main input').click(function () {
        if (!$(this).hasClass('Shiping_ID_Val')) {  //物流不需要
            var ticket_text = $(this).parent().find("label").text();
            $(clickPart).find(".right").html(ticket_text);
        }
        tcHide();
    });

    /*//物流
    $("#express input").click(function () {
        var ticket_text = $(this).parent().find("label").text();
        $(clickPart).find(".express-box .right").html(ticket_text);
        tcHide();
    });

    //优惠券
    $("#ticket input").click(function () {
        var ticket_text = $(this).parent().find("label").text();
        //$(clickPart).find(".ticket-box .right").removeClass("fc-8").addClass("fc-r");
        $(clickPart).find(".ticket-box .right").html(ticket_text);
        tcHide();
    });*/
</script>
</body>

</html>