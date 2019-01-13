<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/api/shop/products.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/api/distribute/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/tools.php');

$product_pintuan = pro_pt_flag($rsProducts);    //产品是否符合拼团条件
if (!$product_pintuan) {   //不符合拼团条件，跳转到单独购买的产品详情页面
    layerOpen('此商品不支持拼团，跳转至单购页面', 2, $shop_url . '/products/' . $ProductsID . '/');
    exit;
}

$teamid = !empty($_GET['teamid']) ? (int)$_GET['teamid'] : 0;
//查询拼团的条件
if (!empty($UserID) && isset($_GET['teamid'])) {
    $rsTeam = $DB->GetRs('pintuan_team', 'addtime', 'where id = ' . $teamid . ' and users_id = "' . $UsersID . '" and productid = ' . $ProductsID . ' and teamnum < ' . $rsProducts['pintuan_people'] . ' and teamstatus = 0');
    if (empty($teamid) || empty($rsTeam) || ($rsTeam['addtime'] + 24 * 60 * 60) < time()) {
        layerOpen('此拼团已失效，跳转至开团页面', 2, $pintuan_url . 'xiangqing/' . $ProductsID . '/');
        exit;
    }
    //判断是否参加过此团
    $rsTeamDetail = $DB->GetRs('pintuan_teamdetail', 'pintuan_teamdetail.id', 'left join pintuan_team on pintuan_teamdetail.teamid = pintuan_team.id where pintuan_team.users_id = "' . $UsersID . '" and pintuan_team.productid = ' . $ProductsID . ' and pintuan_team.id = ' . $teamid . ' and pintuan_teamdetail.userid = ' . $UserID);
    if (!empty($rsTeamDetail)) {
        layerOpen('您已参加过此团', 2);
        exit;
    }
}

//图片路径缩略处理
$proimg_count = 0;
if (isset($JSON["ImgPath"]) && count($JSON["ImgPath"]) > 0) {
    foreach ($JSON["ImgPath"] as $k => $v) {
        $proimg_count += 1;
    }
}

//产品承诺（暂无）
/*$ProductPromise = new ProductPromise();
$PromiseTags = $ProductPromise->getAllPromise($ProductsID);*/
$PromiseTags = [];

$skuimg = !empty($rsAttr['skuimg']) ? $rsAttr['skuimg'] : '';

//拼团产品
$cur_jiage = $rsProducts['pintuan_pricex'];
if ($flag == 1 && !empty($rsAttr['skuvaljosn'])) {  //显示拼团价格
    $skuvaljosn = json_decode($rsAttr['skuvaljosn'], true);
    foreach ($skuvaljosn as $key => $value) {
        $skuvaljosn[$key]['Txt_PriceSon'] = !empty($value['Txt_PtPriceSon']) ? $value['Txt_PtPriceSon'] : $rsProducts['pintuan_pricex'];
    }
    $rsAttr['skuvaljosn'] = json_encode($skuvaljosn, JSON_UNESCAPED_UNICODE);
}

//是否收藏
/*$res_sc = $DB->GetRs('pintuan_collet', 'id', 'where users_id = "' . $UsersID . '" and userid = ' . $UserID . ' and productid = ' . $ProductsID);
$sc = !empty($res_sc) ? 1 : 0;*/

//小伙伴正在拼团   除去 登录用户已参加的团
$existTeam = $DB->GetAssoc('pintuan_team', 'id, userid, teamstatus, teamnum, addtime', 'where users_id = "' . $UsersID . '" and productid = ' . $ProductsID . ' and teamstatus = 0 and teamnum > 0 and teamnum < ' . $rsProducts['pintuan_people'] . ' and userid != ' . $UserID . ($teamid ? ' and id != ' . $teamid : '') . ' order by id desc limit 0,2');
if (!empty($existTeam) && count($existTeam) > 0) {
    foreach ($existTeam as $teamKey => $team) {
        if (($team['addtime'] + 24 * 60 * 60 - time()) <= 0) {    //拼团时间已过，不能再拼团
            unset($existTeam[$teamKey]);
            continue;
        }
        if (!empty($UserID)) {
            $join = $DB->GetRs('pintuan_teamdetail', 'id, userid', 'where teamid = ' . $team['id'] . ' and userid = ' . $UserID);
            if (!empty($join)) {
                unset($existTeam[$teamKey]);
                continue;
            }
        }
        $userInfo = $DB->GetRs('user', 'User_HeadImg, User_NickName, User_Mobile', 'where User_ID = ' . $team['userid']);
        $existTeam[$teamKey]['User_HeadImg'] = !empty($userInfo['User_HeadImg']) ? $userInfo['User_HeadImg'] : '/static/api/images/user/face.jpg';
        $existTeam[$teamKey]['User_NickName'] = !empty($userInfo['User_NickName']) ? $userInfo['User_NickName'] : substr_replace($userInfo['User_Mobile'], '***', 6, 3);
    }
}

//评论统计
$commit_count = [
    'low' => 0,
    'medium' => 0,
    'high' => 0
];
if (!empty($commit_list)) {
    foreach ($commit_list as $key => $value) {
        if ($value['Score'] == 1 || $value['Score'] == 2) $commit_count['low'] += 1;
        if ($value['Score'] == 3 || $value['Score'] == 4) $commit_count['medium'] += 1;
        if ($value['Score'] == 5) $commit_count['high'] += 1;
    }
}

$back_url = 'javascript:history.go(-1);';
if($rsProducts['Products_Union_ID']){
	$backurl = '/api/'.$UsersID.'/shop/union/';
}else{
	$backurl = '/api/'.$UsersID.'/shop/wzw/';
}

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="apple-touch-fullscreen" content="yes"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no"/>
    <title><?= $rsProducts['Products_Name'] ?></title>

    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/api/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/api/shop/skin/default/style.css?t=0613' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/api/js/global.js?t=0613'></script>
    <script type='text/javascript' src='/static/api/shop/js/shop.js?t=<?= time() ?>'></script>

    <script type='text/javascript' src='/static/js/iscroll.js'></script>
    <script type='text/javascript' src='/static/api/shop/js/product_attr_helper.js?t=0613'></script>
    <link href="/static/api/shop/skin/default/css/tao_detail.css?t=0613" rel="stylesheet" type="text/css"/>

    <script src="/static/js/jquery.idTabs.min.js"></script>
    <!-- 产品图片所需插件 begin -->
    <script type='text/javascript' src='/static/api/js/user.js'></script>
    <script type='text/javascript' src='/static/api/shop/js/toast.js?t=0613'></script>

    <link href="/static/api/css/fx_index.css?t=0613" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="/static/css/index.css">
    <link rel="stylesheet" href="/static/css/swiper.min.css">
    <link rel="stylesheet" href="/static/css/index_media.css">
    <script type="text/javascript" src="/static/js/swiper.min.js"></script>
    <script type="text/javascript" src="/static/js/layer/mobile/layer.js"></script>
    <style>
        .swiper-pagination-bullet {
            background: #fff;
        }

        .swiper-pagination-bullet-active {
            background: #727272;
        }

        .layui-m-layerbtn {
            font-size: 16px;
            position: fixed;
            bottom: 0;
            left: 0;
            border-top: 0;
            line-height: 45px;
            height: 45px;
            background: -webkit-linear-gradient(left, #ff6600, #ff2400); /* Safari 5.1 - 6.0 */
            background: -o-linear-gradient(right, #ff6600, #ff2400); /* Opera 11.1 - 12.0 */
            background: -moz-linear-gradient(right, #ff6600, #ff2400); /* Firefox 3.6 - 15 */
            background: linear-gradient(to right, #ff6600, #ff2400); /* 标准的语法 */
        }

        .layui-m-layerbtn span[yes] {
            color: #fff;
        }
		
		.red{font-size: 18px;color: #ff0000;}
    </style>

    <script>
        var base_url = '<?= $base_url ?>';
        var UsersID = '<?= $UsersID ?>';
        var Products_ID = '<?= $rsProducts['Products_ID'] ?>';
        //var fromFlag = 0;
        var proimg_count = <?= $proimg_count ?>;
        var is_virtual = <?= $rsProducts["Products_IsVirtual"] ?>;

        //增加拼团购买标志
        var order_type = 'pintuan';
        $(document).ready(function () {
            shop_obj.tao_detail_init();
        });
    </script>

    <!--实现自定义分享功能-->
    <?php if (!empty($share_config)) { ?>
        <script language="javascript">
            var share_config = {
                appId: "<?= isset($share_config["appId"]) ? $share_config["appId"] : '' ?>",
                timestamp:<?= isset($share_config['timestamp']) ? $share_config["timestamp"] : time() ?>,
                nonceStr: "<?= isset($share_config["noncestr"]) ? $share_config["noncestr"] : '' ?>",
                url: "<?= isset($share_config["url"]) ? $share_config["url"] : '' ?>",
                signature: "<?= isset($share_config["signature"]) ? $share_config["signature"] : '' ?>",
                title: "<?= $share_config["title"] ?>",
                desc: "<?= $share_config["desc"] ?>",
                img_url: "<?= $share_config["img"] ?>",
                link: "<?= $share_config["link"] ?>"
            };
            $(document).ready(global_obj.share_init_config);
        </script>
    <?php } ?>
    <!--自定义分享功能结束-->

    <?php if ($flag == 1) { ?>
        <script>
            //sku
            var keys = <?= $flag ? $rsAttr['skujosn'] : '""' ?>;
            var data = <?= $flag ? $rsAttr['skuvaljosn'] : '""' ?>;
            var imgdata = <?= $flag && $skuimg ? $skuimg : '""' ?>;
            var cur_jiage = <?= $cur_jiage ?>;
            var has_active = <?= $flashsale ?>;
        </script>
        <script type='text/javascript' src='/static/api/shop/js/sku.js?t=<?= time() ?>'></script>
    <?php } ?>
	<script>
	
		function pushHistory() {
			var state = {  
				title: "title",  
				url: ""  
			};  
			window.history.pushState(state, state.title, state.url);  
		} 
		
		pushHistory();  
		window.addEventListener("popstate", function(e) {  //回调函数中实现需要的功能
			location.href= "<?=$backurl ?>"; 
		}, false);  
	</script>
</head>
<body>
<div class="w">
    <div class="top_pt">
        <span class="left"><a href="<?=$backurl ?>"><img src="/static/api/shop/skin/default/images/back_60.png"></a></span>
        <span class="right"><a href="/api/<?= $UsersID ?>/shop/cart/"><img src="/static/api/shop/skin/default/images/gwc.png"></a></span>
    </div>
    <div class="clear"></div>
    <div class="swiper-container pro_xq">
        <div class="swiper-wrapper">
            <?php if (isset($JSON["ImgPath"]) && count($JSON["ImgPath"]) > 0) {
                foreach ($JSON["ImgPath"] as $key => $value) {
                    ?>
                    <div class="swiper-slide">
                        <a href="<?= $value ?>"><img class="banner_xq" src="<?= getImageUrl($value, 2) ?>"/></a>
                    </div>
                <?php }
            } ?>
        </div>
        <div class="swiper-pagination pro_xq_x"></div>
    </div>
    <div class="clear"></div>
    <div class="price_pro_pt">
        <span class="left_pt_xq"><span>￥</span><?= !empty($flashsale) && $rsProducts['flashsale_flag'] == 1 ? '抢购价 ' . $rsProducts['flashsale_pricex'] : $rsProducts['pintuan_pricex'] ?><font>￥<?= $rsProducts['Products_PriceX'] ?></font></span>
        <span class="right_pt_xq">已团<?= $rsProducts['Products_Sales'] ?>件 ● <?= $rsProducts['pintuan_people'] ?>人团</span>
    </div>
    <div class="title_pro">
        <span class="left_pro_xq"><?= $rsProducts['Products_Name'] ?></span>
        <?php if ($perm_config['detailbone']['Perm_Field'] == 'detailbone' && $perm_config['detailbone']['Perm_On'] == 0) { ?>
            <a class="detail_share" id="share_product" productid="<?= $ProductsID ?>" is_distribute="<?= $Is_Distribute ?>" href="javascript:;">
                <span class="right_pro_xq"><img src="/static/api/shop/skin/default/images/pro_xq_03.png">分享此产品</span>
            </a>
        <?php } ?>
    </div>

    <div class="jianjie"><?= $rsProducts['Products_BriefDescription'] ?></div>
 <!--   <div class="baozhang">
        <ul>
            <?php /*if ($rsProducts["Products_IsShippingFree"] == 1) { */?>
                <li><img src="/static/api/shop/skin/default/images/gou.png">全场包邮</li>
            <?php /*} */?>
            <li><img src="/static/api/shop/skin/default/images/gou.png">7天退换</li>
            <li><img src="/static/api/shop/skin/default/images/gou.png">48小时发货</li>
            <li><img src="/static/api/shop/skin/default/images/gou.png">假一赔十</li>
        </ul>
    </div>-->
    <div class="clear"></div>
    <?php if (!empty($existTeam) && count($existTeam) > 0) { ?>
        <div class="kaituan">
            <p>小伙伴在开团</p>
            <ul>
                <?php foreach ($existTeam as $teamk => $teamv) { ?>
                    <li>
                        <span class="left"><img src="<?= $teamv['User_HeadImg'] ?>"></span>
                        <span class="left_time">
                            <?= $teamv['User_NickName'] ?>
                            <p>还差<?= $rsProducts['pintuan_people'] - $teamv['teamnum'] ?>人，剩余<span class="closeTime" init-time="<?= 86400 - (time() - $teamv['addtime']) ?>"><?= date('H : i : s', $teamv['addtime']) ?></span></p>
                        </span>
                        <span class="right">
                            <a href="/api/<?= $UsersID ?>/pintuan/<?= $owner['id'] ?>/jointeam/<?= $ProductsID ?>/<?= $teamv['id'] ?>/">去参团</a>
                        </span>
                        <div class="clear"></div>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <div class="clear"></div>
    <?php } ?>
    <?php if ($IsStore == 1) { ?>
        <div class="shop_header">
            <p class="shop_header_top"><img src="<?= $rsBiz["Biz_Logo"] ? $rsBiz["Biz_Logo"] : $rsConfig["ShopLogo"] ?>"><?= $rsBiz["Biz_Name"] ?></p>
            <ul>
                <li><a href="/api/<?=$UsersID?>/biz/<?=$rsBiz['Biz_ID']?>/allcate/"><img src="/static/api/shop/skin/default/images/sy.png">全部分类</a></li>
                <li><a href="<?= $biz_url ?>"><img src="/static/api/shop/skin/default/images/sy.png">店铺逛逛</a></li>
            </ul>
        </div>
    <?php } ?>
    <div class="clear"></div>
	<div class="cp_gm">
        <ul>
            <li id="canshu">
                <span class="left">产品参数</span>
                <span class="right">
					<a href="#"><img src="/static/api/shop/skin/default/images/back_x.png"></a>
				</span>
            </li>
        </ul>
    </div>
	<div class="canshu_list">
        <ul>
            <p>产品参数</p>
            <?php if(!empty($parameter_list)){
                foreach($parameter_list as $ks=>$vs) { ?>
                    <?php if(!empty($vs['name']) && !empty($vs['value'])){?>
            <li>
                <span class="left"><?=$vs['name']?>：</span><?=$vs['value']?>
            </li>
                        <?php }else{?>
                        <li>
                           暂无参数
                        </li>
                        <?php }?>
            <?php }
            } ?>
        </ul>
    </div>
	<div class="clear"></div>
    <div class="pingjia_pt">
        <div class="title_pj">
            <span class="left">商品评价</span>
            <span class="right">
                <a href="<?= $base_url ?>api/<?= $UsersID ?>/shop/commit/<?= $ProductsID ?>/?love">查看更多（<?= $comment_aggregate['num'] ?>）<img src="/static/api/shop/skin/default/images/back_x.png"></a>
			</span>
        </div>
        <div class="clear"></div>
        <?php if (!empty($commit_list)) { ?>
            <div class="pj_mian">
                <ul>
                    <?php foreach ($commit_list as $k => $v) { ?>
                        <li>
                            <div class="name_pj">
                                <span class="left">
                                    <img src="<?= strlen($v['User_HeadImg']) > 5 ? $v['User_HeadImg'] : '/static/api/images/user/face.jpg' ?>">
                                    <?= !empty($v['User_NickName']) ? $v['User_NickName'] : substr_replace($v['User_Mobile'], '***', -7, 3) ?>
                                </span>
                                <span class="right"><?= date("Y-m-d H:m:s", $v['CreateTime']) ?></span>
                            </div>
                            <div class="clear"></div>
                            <h2><?= $v['Note'] ?></h2>
                            <?php $CartList = json_decode($v['Order_CartList'], true);
                            if (!empty($CartList)) {
                                $proid = array_keys($CartList)[0];
                                $attr = array_keys($CartList[$proid])[0];
                                $proInfo = $CartList[$proid][$attr];
                                if (!empty($proInfo['Productsattrstrval'])) {
                                    ?>
                                    <p>款式：<?= $proInfo['Productsattrstrval'] ?></p>
                                <?php }
                            } ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>
    </div>
    <div class="clear"></div>
    <div class="xiangqing_sp">
        <p>商品详情</p>
        <?php
        $content = htmlspecialchars_decode($rsProducts["Products_Description"]);
        if (!empty($content)) {
            $content = stripslashes($content);
            //$content = str_replace('src', 'class="lazy" data-original', $content);
        }
        echo $content;
        ?>
    </div>
    <!--<div class="clear" style="height: 60px;"></div>-->

    <!-- 属性选择内容begin -->
    <div id="option_content">
        <div class="pro_top">
            <div style="float:right;">
                <div id="close-btna" class="cjlose thi"></div>
            </div>
            <div class="imgchege"><img src="<?= $rsProducts['ImgPath'] ?>"></div>
            <div class="mid">
                <p class="price_pro" id="price"></p>
                <p id="count"></p>
                <p class="parameter_pro" id="showattr"></p>
            </div>
        </div>
        <div id="option_selecter">
            <form name="addtocart_form" action="/api/<?= $UsersID ?>/shop/cart/" method="post" id="addtocart_form">
                <input type="hidden" name="teamid" value="<?= $teamid ?>"/>
                <input type="hidden" name="OwnerID" value="<?= $owner['id'] ?>"/>
                <input type="hidden" name="IsShippingFree" value="<?= $rsProducts["Products_IsShippingFree"] == 1 ? 1 : 0 ?>"/>
                <input type="hidden" name="ProductsWeight" value="<?= $rsProducts["Products_Weight"] ?>"/>
                <input type="hidden" name="shu_value" id="shu_value" value=""/>
                <input type="hidden" name="shu_attribute" id="shu_attribute" value=""/>
                <input type="hidden" name="shu_priceg" id="shu_priceg" value=""/>
                <?php if (!empty($rsattrarr)) { ?>
                    <?php foreach ($rsattrarr as $key => $value) { ?>
                        <div class="pro_attribute">
                            <p><?= $key ?></p>
                            <?php foreach ($value as $ak => $av) { ?>
                                <input type="button" class="sku" attr_id="<?= $ak ?>" value="<?= $av ?>"/>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>
                <div class="option-val-list">
                    <div id="qty_selector" style="line-height: 45px;font-size: 16px;font-weight: 600;color: #333;">
                        <input type="hidden" id="cur_price" value="<?= $rsProducts['Products_PriceX'] ?>"/>
                        <input type="hidden" id="no_attr_price" value="<?= $rsProducts['pintuan_pricex'] ?>"/>
                        <span>购买数量：</span><a href="javascript:;" class="qty_btn" name="minus">-</a>
                        <input type="text" id="qtycount" name="Qty" maxlength="3" style="height:28px; line-height:28px; font-weight:normal;" value="1" pattern="[0-9]*"/>
                        <a href="javascript:;" class="qty_btn" name="add">+</a>
                        <br/>
                    </div>
                </div>
                <input type="hidden" name="ProductsID" value="<?= $ProductsID ?>"/>
            </form>
            <div style="height:40px;"></div>
            <a href="javascript:;" id="selector_confirm_btn" style="background-color:#ff0000;">确定</a>
        </div>
    </div>
    <!-- 属性选择内容end -->

    <div class="footer_pt">
        <input type="hidden" name="flag" value="<?= $flag ?>" title="是否有属性"/>
        <ul>
            <li class="xiaodian"><a href="/api/<?= $UsersID ?>/shop/union/"><img src="/static/api/shop/skin/default/images/sy.png">首页</a></li>
            <li class="shoucang" style="background-image:none!important;" status="<?= $rsProducts['Products_IsFavourite'] ?>" id="<?= $rsProducts['Products_IsFavourite'] ? 'favorited' : 'favorite' ?>" productid="<?= $rsProducts['Products_ID'] ?>"><a href="javascript:;" style="<?= $rsProducts['Products_IsFavourite'] == 0 ? '' : 'color:#ff0000' ?>"><img src="<?= $rsProducts['Products_IsFavourite'] == 0 ? '/static/api/shop/skin/default/images/sc.png' : '/static/api/shop/skin/default/images/sc_xx.png' ?>">收藏</a></li>
            <li class="kefu"><a href="javascript:;" ONCLICK="javascript:location.href='<?= $rsBiz['Biz_KfProductCode'] ?>"><img src="/static/api/shop/skin/default/images/kf.png">客服</a></li>
            <?php if ($rsProducts['Products_SoldOut'] == 1) { ?>
                <li class="no_pro">产品已下架</li>
            <?php } else if ($rsProducts['Products_Count'] <= 0) { ?>
                <li class="no_pro">库存不足</li>
            <?php } else { ?>
                <li class="dd_buy_pt"><a href="<?= $shop_url ?>products/<?= $ProductsID ?>/"><p>￥<?= $rsProducts['Products_PriceX'] ?></p>单独购买</a></li>
                <li class="pt_buy_pt"><a href="javascript:void(0);" id="menu-direct-btn"><p>￥<?=  !empty($flashsale) && $rsProducts['flashsale_flag'] == 1 ? $rsProducts['flashsale_pricex'] : $rsProducts['pintuan_pricex']  ?></p><?= $teamid ? '去参团' : '一键开团' ?></a></li>
            <?php } ?>
        </ul>
    </div>
</div>

<span style="display:none" id="buy-type"></span>
<div class="mask"></div>
<footer id="footer"></footer>

<script type="text/javascript">
    var UsersID = "<?= $UsersID ?>";
    var userid = "<?= $UserID ?>";
    var ProductsID = goodsid = "<?= $ProductsID ?>";

    var pintuan_end_time = <?= $rsProducts['pintuan_end_time'] ?>;   //结束时间戳

    function pt_jishi(startTime, selector) {
        startTime--;
        var _html;
        if (startTime == 0) {
            _html = '0分0秒';
            $(".closeTime").eq(selector).parents('li').css('display', 'none');
        } else {
            startTime = parseInt(startTime);
            var hour = Math.floor(startTime / (60 * 60));
            var minute = Math.floor(startTime % (60 * 60) / 60);
            var second = Math.floor(startTime % (60 * 60) % 60);
            _html = hour + '时' + minute + '分' + second + '秒';
            $(".closeTime").eq(selector).html(_html);
            setTimeout('pt_jishi(' + startTime + ', ' + selector + ')', 1000);
        }
    }

    $(function () {
        //banner
        var mySwiper = new Swiper('.pro_xq', {
            direction: 'horizontal',
            loop: true,
            autoResize: true,
            pagination: '.pro_xq_x',
            paginationClickable: true,
            autoplay: 3000,
            autoplayDisableOnInteraction: false
        });

        //拼团剩余时间
        if ($(".closeTime").length > 0) {
            $(".closeTime").each(function (i, n) {
                var me = $(this);
                var startTime = me.attr('init-time');
                if (startTime <= 0) {
                    pt_jishi(1, i);
                } else {
                    pt_jishi(startTime, i);
                }
            });
        }
		
		/*产品参数*/
    $(".canshu_list").hide()
    $("#canshu").click( function () {
        layer.open({
            btn: '完成',
            shadeClose:true
            ,type: 1
            ,content:$ ('.canshu_list').html()
            ,className: 'lay_cs'
            ,anim: 'up'
            ,style: 'position:fixed; bottom:0; left:0; width: 100%; padding:0; border:none;font-size:14px'
            ,success: function () {
            }
        });
    });

        /*头部导航固定*/
        window.onscroll = function () {
            var t = document.documentElement.scrollTop || document.body.scrollTop;
            var top_pt = $('.top_pt');
            if (t > 240) {
                top_pt.css({"background": "#fff", "border-bottom": "1px #f4f4f4 solid"});
                top_pt.find('.left img').attr('src', '/static/api/shop/skin/default/images/black_arrow_left.png');
                top_pt.find('.right img').attr('src', '/static/api/shop/skin/default/images/gwc_xx.png');
            } else {
                top_pt.css({"background": "0", "border-bottom": "0"});
                top_pt.find('.left img').attr('src', '/static/api/shop/skin/default/images/back_60.png');
                top_pt.find('.right img').attr('src', '/static/api/shop/skin/default/images/gwc.png');
            }
        };
    });
</script>
</body>
</html>
