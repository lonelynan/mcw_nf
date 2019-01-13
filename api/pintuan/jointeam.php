<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/api/shop/products.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/tools.php');

$product_pintuan = pro_pt_flag($rsProducts);    //产品是否符合拼团条件
if (!$product_pintuan) {   //不符合拼团条件，跳转到单独购买的产品详情页面
    //layerOpen('此商品不支持拼团，跳转首页', 2, '/api/' . $UsersID . '/shop/?OwnerID=' . $owner['id']);
    layerOpen('此商品不支持拼团，跳转至单购页面', 2, $shop_url . '/products/' . $ProductsID . '/');
    exit;
}

$teamid = !empty($_GET['teamid']) ? (int)$_GET['teamid'] : 0;

//获取该团的信息
$rsTeam = $DB->GetRs('pintuan_team', 'teamnum, addtime, userid', 'where users_id = "' . $UsersID . '" and productid = ' . $ProductsID . ' and teamstatus = 0 and id = ' . $teamid);
if (empty($rsTeam)) {
    layerOpen('该拼团不存在', 2);
    exit;
}

$team_flag = [
    'status' => 1,
    'msg' => '正常拼团'
];

if (($rsTeam['addtime'] + 24 * 60 * 60 - time()) <= 0) {
    $team_flag = [
        'status' => 0,
        'msg' => '此拼团的时间已到期'
    ];
}
if ($rsTeam['teamnum'] >= $rsProducts['pintuan_people']) {
    $team_flag = [
        'status' => 0,
        'msg' => '此拼团人数已达上限'
    ];
}

//获取该团的成员信息
$sql = 'select pd.userid,u.User_HeadImg from pintuan_teamdetail as pd left join user as u on pd.userid = u.User_ID where pd.teamid = ' . $teamid . ' order by pd.addtime asc';
$DB->query($sql);
$rsTeamDetail = $DB->toArray();
//查询是否已参加
if (!empty($UserID) && in_array($UserID, array_column($rsTeamDetail, 'userid'))) {  //已登录
    $team_flag = [
        'status' => 0,
        'msg' => '还差' . ($rsProducts['pintuan_people'] - $rsTeam['teamnum']) . '人，人满才算拼团成功'
    ];
}
//删选出团队创始人的信息
if (!in_array($rsTeam['userid'], array_column($rsTeamDetail, 'userid'))) {     //团队队长不在，查询队长信息
    $teamCreateUser = $DB->GetRs('user', 'User_ID, User_HeadImg', 'where User_ID = ' . $rsTeam['userid']);
} else {
    $teamCreateUser = $rsTeamDetail[array_search($rsTeam['userid'], array_column($rsTeamDetail, 'userid'))];
    unset($rsTeamDetail[array_search($rsTeam['userid'], array_column($rsTeamDetail, 'userid'))]);
}

$skuimg = !empty($rsAttr['skuimg']) ? $rsAttr['skuimg'] : '';


if ($flag == 1 && !empty($rsAttr['skuvaljosn'])) {  //显示拼团价格
    $skuvaljosn = json_decode($rsAttr['skuvaljosn'], true);
    foreach ($skuvaljosn as $key => $value) {
        $skuvaljosn[$key]['Txt_PriceSon'] = !empty($value['Txt_PtPriceSon']) ? $value['Txt_PtPriceSon'] : $rsProducts['pintuan_pricex'];
    }
    $rsAttr['skuvaljosn'] = json_encode($skuvaljosn, JSON_UNESCAPED_UNICODE);
}

//产品承诺
/*$ProductPromise = new ProductPromise();
$PromiseTags = $ProductPromise->getAllPromise($ProductsID);*/
$PromiseTags = [];

//获取推荐商品
$use_pro_num = 6;
$recommend_products = $DB->GetAssoc("shop_products", "Products_Name, Products_ID, Products_JSON, Products_PriceY, Products_PriceX, Products_Sales, Products_PriceA, Products_PriceAmax, skujosn, Products_Count, Products_IsHot, pintuan_flag, pintuan_people, pintuan_start_time, pintuan_end_time, pintuan_pricex", "where Users_ID='" . $UsersID . "' and Products_IsRecommend=1 and Products_SoldOut=0 and Products_Status=1 order by Products_Index asc,Products_ID desc limit " . $use_pro_num);
if (!empty($recommend_products) && is_array($recommend_products) && count($recommend_products) > 0) {
    foreach ($recommend_products as $k => $item) {
        $product_pintuan = pro_pt_flag($item);    //产品是否符合拼团条件
        if ($product_pintuan) {   //符合拼团条件，产品详情为拼团产品详情
            $recommend_products[$k]['link'] = $pintuan_url . 'xiangqing/' . $item['Products_ID'] . '/';
            $recommend_products[$k]['is_pt'] = 1;
            $recommend_products[$k]['Products_Name'] = '<span style="padding:1px 5px;background-color:#fe5400;color:#fff;border-radius:5px;font-size:10px;margin-right:5px;">拼团</span>' . sub_str($item['Products_Name'], 30);
        } else {
            $recommend_products[$k]['link'] = $shop_url . 'products/' . $item['Products_ID'] . '/';
            $recommend_products[$k]['is_pt'] = 0;
            $recommend_products[$k]['Products_Name'] = mb_substr($item['Products_Name'], 0, 35, 'utf-8');
        }
        $Products_JSON = json_decode($item['Products_JSON'], true);
        $recommend_products[$k]['ImgPath'] = !empty($Products_JSON["ImgPath"][0]) ? getImageUrl($Products_JSON["ImgPath"][0], 2) : '/static/images/nopic.jpg';

        if (empty($item['skujosn'])) {
            $recommend_products[$k]['priceshow'] = $item['Products_PriceX'];
        } else {
            /*if ($item['Products_PriceA'] == $item['Products_PriceAmax']) {
                $recommend_products[$k]['priceshow'] = $item['Products_PriceA'];
            } else {
                $recommend_products[$k]['priceshow'] = $item['Products_PriceA'] . ' ~ ' . $item['Products_PriceAmax'];
            }*/
            $recommend_products[$k]['priceshow'] = $item['Products_PriceA'];
        }
    }
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
    <title>参团-拼团</title>

    <link href="/static/css/bootstrap.css" rel="stylesheet"/>
    <link href="/static/api/css/fx_index.css" rel="stylesheet" type="text/css"/>
    <link href="/static/api/shop/skin/default/css/tao_detail.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="/static/api/pintuan/css/jointeam.css?t=1">

    <script src="/static/js/jquery-1.11.1.min.js"></script>
    <script src="/static/js/layer/mobile/layer.js"></script>
    <!--懒加载-->
    <script type='text/javascript' src='/static/js/jquery.lazyload.min.js'></script>
	<script type='text/javascript' src='/static/api/shop/js/toast.js?t=<?= time(); ?>'></script>
    <script type='text/javascript' src='/static/api/js/global.js?t=<?php echo time(); ?>'></script>
    <script type='text/javascript' src='/static/api/shop/js/shop.js?t=<?php echo time(); ?>'></script>
    <script src="/static/js/index.css.js"></script>
    <script>
        var base_url = '<?= $base_url ?>';
        var UsersID = '<?= $UsersID ?>';
		var Products_ID = '<?= $rsProducts['Products_ID'] ?>';
        var OwnerID = '<?= isset($_GET['OwnerID']) ? $_GET['OwnerID'] : 0 ?>';
        var OrderID = '<?= isset($_GET['OrderID']) ? $_GET['OrderID'] : 0 ?>';
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
                appId: "<?php echo isset($share_config["appId"]) ? $share_config["appId"] : '';?>",
                timestamp:<?php echo isset($share_config['timestamp']) ? $share_config["timestamp"] : time();?>,
                nonceStr: "<?php echo isset($share_config["noncestr"]) ? $share_config["noncestr"] : '';?>",
                url: "<?php echo isset($share_config["url"]) ? $share_config["url"] : '';?>",
                signature: "<?php echo isset($share_config["signature"]) ? $share_config["signature"] : '';?>",
                title: "<?php echo $share_config["title"];?>",
                desc: "<?=$share_config["desc"]?>",
                img_url: "<?php echo $share_config["img"];?>",
                link: "<?php echo isset($share_config["url"]) ? $share_config["url"] : '';?>"
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
	<?php if($team_flag['status'] == 0){ ?>
	<style> 
		.team_flag .team_over img {max-width:50px;margin-bottom:15px;}
		.team_flag .team_over {color:#000;font-weight:bold;}
	</style>
	<script>
	$(function(){
		$("#favourite_friend").on('click', function(){
			$('body').prepend('<div id="global_share_layer"><div></div></div>');
			$('#global_share_layer').css({
				width:'100%',
				height:'100%',
				overflow:'hidden',
				position:'fixed',
				top:0,
				left:0,
				background:'#000',
				opacity:0.8,
				'z-index':100000
			}).children('div').css({ 
				width:'100%',
				height:'100%',
				background:'url(/static/api/images/global/share/share.png) left top no-repeat',
				'background-size':'100% auto',
				position:'relative',
				left:0,
				color:'#fff',
				top:0
			});
			$('#global_share_layer').click(function(){
				$('#global_share_layer').remove();
			});
		});
	});
	</script>
	<?php } ?>
	
</head>
<body>
<div class="head_ct">
    <a href="<?=$pintuan_url ?>user/"><img src="/static/api/shop/skin/default/images/gdx.png">返回</a>
</div>
<div class="w" id="allpro" style="position: absolute;top: 0;bottom: 0;overflow-y: scroll;-webkit-overflow-scrolling:touch;width: 100%;">
    <div style="height: 50px;"></div>
    <div class="pro_ct">
        <span class="img_l"><a href="<?=$pintuan_url ?>xiangqing/<?=$ProductsID ?>/"/><img src="<?= $JSON['ImgPath'][0] ?>"></a></span>
		<span class="main_l">
			<p><?= $rsProducts['Products_Name'] ?></p>
			<div class="price_d">￥<?= $rsProducts['pintuan_pricex'] ?><font>拼团省 ￥<?= $rsProducts['Products_PriceX'] - $rsProducts['pintuan_pricex'] ?></font></div>
			<div class="tuan_ct"><?= $rsProducts['pintuan_people'] ?>人团 • 已团<?= $rsProducts['Products_Sales'] ?>件</div>
		</span>
    </div>
    <div class="clear"></div>
    <?php if (!empty($PromiseTags)) { ?>
        <div class="baozhang">
            <ul>
                <?php foreach ($PromiseTags as $k => $v) {
                    echo '<li><img src="/static/api/shop/skin/default/images/gou.png">' . $v['promise_text'] . '</li>';
                } ?>
            </ul>
        </div>
    <?php } ?>
    <div class="clear"></div>
    <div class="team_flag">
        <?php if ($team_flag['status'] == 0) { ?>
            <div class="team_over">
                <img src="/static/api/shop/skin/default/images/face.jpg" alt=""><br><?= $team_flag['msg'] ?>
            </div>
        <?php } else { ?>
            <div class="renshu_ct">
            <span class="tuanzhang">
                <img src="<?= !empty($teamCreateUser['User_HeadImg']) ? $teamCreateUser['User_HeadImg'] : '/static/api/images/user/face.jpg' ?>">
                <p><img src="/static/api/shop/skin/default/images/tuanzhang.png"></p>
            </span>
                <?php if (!empty($rsTeamDetail)) {
                    foreach ($rsTeamDetail as $key => $value) {
                        ?>
                        <span><img src="<?= !empty($value['User_HeadImg']) ? $value['User_HeadImg'] : '/static/api/images/user/face.jpg' ?>"></span>
                    <?php }
                } ?>
            </div>
            <div class="clear"></div>
            <div class="ren_ct">
                <img src="/static/api/shop/skin/default/images/shizhong.png">
                <span>拼团中，还差 <font><?= $rsProducts['pintuan_people'] - $rsTeam['teamnum'] ?></font> 人</span>
            </div>
            <div class="daojishi">
                <p class="line_l"></p>
                <div class="colockbox" id="demo02">
                    剩余
                    <span class="hour">-</span>：
                    <span class="minute">-</span>：
                    <span class="second">-</span>
                    结束
                </div>
                <p class="line_r"></p>
            </div>
        <?php } ?>
    </div>
    <div class="clear"></div>
    <div class="footer_pt">
        <input type="hidden" name="flag" value="<?= $flag ?>" title="是否有属性"/>
        <div class="cantuan_sub">
			<?php if($team_flag['status'] == 1){ ?>
            <a href="javascript:;" id="menu-direct-btn" >立即参团</a>
			<?php }else{ ?>
			<a href="javascript:;" id="favourite_friend" >邀请好友</a>
			<?php } ?>
        </div>
    </div>
    <div class="guize">
        <h2><img src="/static/api/shop/skin/default/images/guize.png">拼团规则</h2>
        <ul>
            <li><img src="/static/api/shop/skin/default/images/icon1.png">开团或参加别人的团</li>
            <li><img src="/static/api/shop/skin/default/images/icon1.png">在规定的时间内，邀请好友参团</li>
            <li><img src="/static/api/shop/skin/default/images/icon1.png">达到拼团人数，分别给团长和团员发货</li>
            <li><img src="/static/api/shop/skin/default/images/icon1.png">未达到拼团人数，货款将自动原路返还</li>
        </ul>
    </div>
    <div class="clear"></div>
    <?php if (!empty($recommend_products)) { ?>
        <div class="tuijian_ct">
            <p class="title"><img src="/static/api/shop/skin/default/images/tuijian.png">店长推荐</p>
            <ul>
                <?php foreach ($recommend_products as $key => $value) { ?>
                    <li>
                        <a href="<?= $value['link'] ?>">
                            <img class="lazy" data-original="<?= $value['ImgPath'] ?>">
                            <p><?= $value['Products_Name'] ?></p>
                            <div>
                                <span class="left">￥<font><?= $value['priceshow'] ?></font></span>
                                <span class="right">已销售<?= $value['Products_Sales'] ?>件</span>
                            </div>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>
</div>

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
            <input type="hidden" name="teamid" value="<?= $team_flag['status'] == 1 ? $teamid : 0 ?>"/>
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

<span style="display:none;" id="buy-type"></span>
<div class="mask"></div> 
<footer id="footer"></footer>
<div class='conver_favourite'><img src="/static/api/images/global/share/favourite.png" /></div>

<script type="text/javascript">
    document.title="<?= $rsProducts['Products_Name'] ?>";

    var shareUrl = 'http://<?= $_SERVER['HTTP_HOST'] ?>/api/<?= $rsProducts['Users_ID'] ?>/pintuan/<?= $owner['id'] ?>/jointeam/<?= $rsProducts['Products_ID'] ?>/<?= $teamid ?>/';
    $(function(){
        var ua = navigator.userAgent;
        if (ua.indexOf('fromAndroid') > -1) {
            share.shareAndroidToJs(shareUrl);
        }
        if (ua.indexOf('fromIOS') > -1) {
            window.webkit.messageHandlers.shareIosToJs.postMessage(shareUrl);
        }
    });

    function androidClose() {
        $("body").css({overflow: '', position: 'static'});

        var scrollTop = $('body').attr('data-scrollTop');
        $(document).scrollTop(scrollTop);

        $('#product_Detail_mask').remove();
        var ua = navigator.userAgent;
        if (ua.indexOf('fromAndroid') > -1) {
            share.clearShareAndroidToJs();
        }
        if (ua.indexOf('fromIOS') > -1) {
            window.webkit.messageHandlers.clearShareIosToJs.postMessage(null);
        }
        $(".common_footer").html(footer_html);
        document.title=	page_title;
    }

    var UsersID = "<?= $UsersID ?>";
    var userid = "<?= $UserID ?>";
    var ProductsID = goodsid = "<?= $ProductsID ?>";

    var pintuan_end_time = <?= $rsProducts['pintuan_end_time'] ?>;  //产品拼团结束时间
    var team_start_time = <?= $rsTeam['addtime'] ?>;   //该团的拼团开始时间戳
    //剩余时间函数
    function last_time() {
        var pintuan_last_time = pintuan_end_time - (new Date().getTime() / 1000);   //产品拼团结束时间
        var team_last_time = team_start_time + 24 * 60 * 60 - (new Date().getTime() / 1000);     //该团的结束时间
        var hour = Math.floor(team_last_time / (60 * 60));
        var minute = Math.floor(team_last_time % (60 * 60) / 60);
        var second = Math.floor(team_last_time % (60 * 60) % 60);
        var last_time = [
            hour < 10 ? '0' + hour : hour,
            minute < 10 ? '0' + minute : minute,
            second < 10 ? '0' + second : second
        ];
        $('#demo02 span').each(function (i) {
            $(this).html(last_time[i]);
        });
        //结束后跳转
        if (team_last_time <= 0) {      //此拼团未在24小时凑齐人数
            $('input[name=teamid]').val(0);
            $('#menu-direct-btn').html('重新开团');
            $('.team_flag').html('<div class="team_over"><img src="/static/api/shop/skin/default/images/face.jpg" alt=""><br>此拼团的时间已到期</div>');
            return false;
        }
        if (pintuan_last_time <= 0) {   //产品拼团活动结束
            location.href = '<?= $shop_url ?>products/<?= $ProductsID ?>/';
            return false;
        }
    }

    $(function () {
        //拼团时间倒计时
        if ($('#demo02').length > 0) {
            last_time();
            setInterval('last_time()', 1000);
        }

        //产品图片懒加载
        $("img.lazy").lazyload({
            placeholder: '/static/images/no_pic1.png',
            //placeholder: '/static/images/grey.gif',
            effect: "show", //使用淡入特效
            failure_limit: 10,  //容差范围
            skip_invisible: false,  //加载隐藏的图片，弄人为不加载
            threshold: 300,     //提前加载
            container: $('#allpro')
        });

        //推荐产品图片规则化
        $('.tuijian_ct li img').each(function () {
            $(this).css('height', $(this).width());
        });
    });
</script>
</body>
</html>