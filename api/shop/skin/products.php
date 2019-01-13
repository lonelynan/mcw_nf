<?php

require_once('top.php');
$_SESSION[$UsersID . "HTTP_REFERER"] = '/api/' . $UsersID . '/shop/products/' . $ProductsID . '/' . (empty($flashsale) ? '' : '?flashsale_flag=1');
if (!empty($rsAttr['skuvaljosn'])) {
    $rsAttr_Data = json_decode($rsAttr['skuvaljosn'],true);
    foreach($rsAttr_Data as $key=>$value){
        if($value['Txt_CountSon'] == 0){
            unset($rsAttr_Data[$key]);
        }
    }
    $rsAttr['skuvaljosn'] = json_encode($rsAttr_Data,JSON_UNESCAPED_UNICODE);
}
if (empty($rsAttr['skuimg'])) {
    $skuimg = '""';
}else{
    $skuimg = $rsAttr['skuimg'];
}
$coupon = $DB->GetAssoc('user_coupon','*','where Users_ID="' . $UsersID . '" and Biz_ID='.$rsProducts['Biz_ID'] . ' and Coupon_EndTime >'.time());

?>
<script language="javascript">
    var UsersID = "<?=$UsersID?>";
    var keys = <?= $flag ? $rsAttr['skujosn'] : '""';?>;
    var data = <?= $flag ? $rsAttr['skuvaljosn'] : '""';?>;
    var imgdata = <?= $flag ? $skuimg : '""';?>;
	var current_discount = <?=$current_discount ?>;
    var has_active = <?= $flashsale ?>;
    var cur_jiage = <?= $cur_jiage;?>;
</script>

<script type='text/javascript' src='/static/js/iscroll.js'></script>
<script type='text/javascript' src='/static/api/shop/js/product_attr_helper.js?t=<?=time();?>'></script>
<link href="/static/api/shop/skin/default/css/tao_detail.css?t=<?php echo time();?>" rel="stylesheet" type="text/css" />

<script src="/static/js/jquery.idTabs.min.js"></script>
<!-- 产品图片所需插件 begin -->
<script type='text/javascript' src='/static/api/js/user.js'></script>
<script type='text/javascript' src='/static/api/shop/js/toast.js?t=<?=time();?>'></script>
<?php if ($flag) {?>
    <script type='text/javascript' src='/static/api/shop/js/sku.js?t=<?= time() ?>'></script>
<?php } ?>

<link href="/static/api/css/fx_index.css?t=<?php echo time();?>" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="/static/css/index.css">
<link rel="stylesheet" href="/static/css/swiper.min.css">
<link rel="stylesheet" href="/static/css/index_media.css">
<script type="text/javascript" src="/static/js/swiper.min.js"></script>
<script type="text/javascript" src="/static/js/layer/mobile/layer.js"></script>
<!-- 产品图片所需插件 end -->
<style>
    .layui-m-layerbtn {
        font-size: 16px;
        position: fixed;
        bottom: 0;
        left: 0;
        border-top: 0;
        line-height: 45px;
        height: 45px;
        background: -webkit-linear-gradient(left, #ff6600 , #ff2400);
        background: -o-linear-gradient(right, #ff6600, #ff2400);
        background: -moz-linear-gradient(right, #ff6600, #ff2400);
        /* background: linear-gradient(to right, #ff6600 , #ff2400); */
    }
    .layui-m-layerbtn span[yes] {
        color: #fff;
    }
    .lay_yh ul li div{
        color: #fff;
    }
    .layui-m-layer-msg .layui-m-layercont {
        color: #fff;
    }

    .guanbi{position: absolute;top:10px; right: 10px;z-index: 20;}
    .guanbi img{display: block; width: 24px; height: 24px;}
    .quan_title{line-height: 50px; width: 100%; text-align: center;font-size: 15px;}
</style>
<script language="javascript">
    var base_url = '<?=$base_url?>';
    var UsersID = '<?=$UsersID?>';
    var Products_ID = '<?=$rsProducts['Products_ID']?>';
    var proimg_count = 1;
    var is_virtual = <?= $rsProducts["Products_IsVirtual"]?>;
    $(document).ready(function() {
        shop_obj.tao_detail_init();
    });
</script>
<script language="javascript">/*$(document).ready(user_obj.coupon_init);*/</script>
<body>
<div class="w">
    <div class="top_pt">
        <span class="left"><a href="javascript:history.back(-1)"><img src="/static/api/shop/skin/default/images/back_60.png"></a></span>
        <span class="right"><a href="/api/<?=$UsersID?>/shop/cart/"><img src="/static/api/shop/skin/default/images/gwc.png"></a></span>
    </div>
    <div class="clear"></div>
    <div class="swiper-container pro_xq">
        <div class="swiper-wrapper">
<?php if(isset($JSON["ImgPath"])){
    foreach($JSON["ImgPath"] as $key=>$value){?>
            <div class="swiper-slide">
                <a href="<?=$value?>"><img class="banner_xq" src="<?=$value?>"/></a>
            </div>
        <?php }
}?>
        </div>
        <div class="swiper-pagination pro_xq_x"></div>
    </div>
    <div class="clear"></div>
    <div class="title_pro">
        <span class="left_pro_xq"><?=$rsProducts["Products_Name"]?></span>
        <?php if($perm_config['detailbone']['Perm_Field'] == 'detailbone' && $perm_config['detailbone']['Perm_On'] == 0) { ?>
            <a class="detail_share" id="share_product" productid="<?=$ProductsID?>" is_distribute="<?=$Is_Distribute?>" href="javascript:;">
        <span class="right_pro_xq">
            <img src="/static/api/shop/skin/default/images/pro_xq_03.png">
            分享此产品
        </span>
            </a>
        <?php } ?>
    </div>
    <div class="price_pro">
        <?php if ($flashsale == 1) { ?>
                <span class="left_pri_xq">抢购价 ￥<?= $rsProducts['flashsale_pricex']?><font>￥<?= $rsProducts["Products_PriceY"] ?></font></span>
            <?php } else {
                if(count($discount_list) > 0 && !empty($_SESSION[$UsersID . 'User_ID'])){
                    foreach($discount_list as $key=>$item){
                        if($item['cur'] == 1){
                            $rsProducts['Products_PriceX'] = $item['price'];
                            $Vip_name = $item['Name'] . '价';
                        } else {
                            //$rsProducts['Products_PriceX'] = $rsProducts['Products_PriceX'];
                            $Vip_name = '';
                        }
                    }
                } ?>
                <span class="left_pri_xq"><?= isset($Vip_name) ? $Vip_name : '' ?>￥<?= $rsProducts["Products_PriceX"] ?><font>￥<?= $rsProducts["Products_PriceY"] ?></font></span>
            <?php }
        ?>

        <?php if($perm_config['detailbtwo']['Perm_Field'] == 'detailbtwo' && $perm_config['detailbtwo']['Perm_On'] == 0) { ?>
            <span class="right_pri_xq">已售<?=$rsProducts["Products_Sales"]?>笔</span>
        <?php } ?>
    </div>

    <!--    优惠券领取-->
    <?php if($un_accept_coupon_num > 0 && !empty($coupon)):?>
    <div class="youhui_dp">
        <span class="left_youhui_dp"><img src="/static/api/shop/skin/default/images/shangjia_16.png">店铺优惠</span>
        <span class="right_youhui_dp">领取优惠券<img src="/static/api/shop/skin/default/images/yuanqu_03.png"></span>
    </div>
    <?php endif; ?>

    <?php if(!empty($coupon)){?>
        <div class="quan">
            <ul>
                <?php foreach ($coupon as $k => $v){
                    ?>
                    <li status="0" CouponID="<?=$v['Coupon_ID']?>">
                        <div class="line_yh">
					<span class="price_yh">
						<?= !empty($v['Coupon_Cash']) ? '<font>￥</font>'.$v['Coupon_Cash'] : $v['Coupon_Discount'].'<font>折</font>' ?>
					</span>
                            <span class="youhui_main">
						<p class="main_yh_01"><?=$v['Coupon_Subject']?></p>
						<p class="main_yh_02">购满<?=$v['Coupon_Condition']?>元使用</p>
						<p class="main_yh_03">有效期至：<?=date('Y年m月d日',$v['Coupon_EndTime'])?></p>
					</span>
                            <span class="ling_yh xhh">立即领取</span>
                        </div>
                    </li>
                <?php }?>
            </ul>
        </div>
    <?php }?>

    <div class="clear"></div>

    <div class="pingjia_pt">
        <div class="title_pj">
            <span class="left">商品评价</span>
            <span class="right">
                <?php if($comment_aggregate['num']!=0){?>
				<a href="<?=$base_url?>api/<?=$UsersID?>/shop/commit/<?=$ProductsID?>/?love">查看更多（<?=$comment_aggregate['num']?>）<img src="/static/api/shop/skin/default/images/back_x.png"></a>
                <?php }else{?>
                <a href="<?=$base_url?>api/<?=$UsersID?>/shop/commit/<?=$ProductsID?>/?love" style="color: #E00102">评价<img src="/static/api/shop/skin/default/images/back_x.png"></a>
                <?php }?>
			</span>
        </div>
        <div class="clear"></div>
        <?php if(!empty($commit_list)){?>
        <div class="pj_mian">
            <ul>
                <?php foreach($commit_list as $vv){?>
                <li>
                    <div class="name_pj">
                        <span class="left"><img src="<?=$vv['User_HeadImg']?>"><?=$vv['User_NickName']?></span>
                        <?php if($vv['Score']!=0){?>
                        <span class="right"><?=$vv['Score']?>分</span>
                        <?php }?>
                    </div>
                    <div class="clear"></div>
                    <p><?= date("Y-m-d H:m:s",$vv['CreateTime'])?></p>
                    <h2><?=$vv['Note']?></h2>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php }?>
    </div>
    <div class="clear"></div>
    <?php if($IsStore==1){?>
    <div class="shop_header">
		<?php
		if((strpos($rsBiz["Biz_Logo"], 'http') === false && !file_exists($_SERVER['DOCUMENT_ROOT'] . $rsBiz["Biz_Logo"])) || empty($rsBiz["Biz_Logo"])){
			$rsBiz["Biz_Logo"] = "/static/images/none.png";
		}
		?>
        <p class="shop_header_top"><img src="<?php echo $rsBiz["Biz_Logo"] ? $rsBiz["Biz_Logo"] : $rsConfig["ShopLogo"];?>"><?php echo $rsBiz["Biz_Name"];?></p>
        <ul>
            <li><a href="/api/<?=$UsersID?>/biz/<?=$rsBiz['Biz_ID']?>/allcate/"><img src="/static/api/shop/skin/default/images/sy.png">全部分类</a></li>
            <li><a href="<?php echo $biz_url; ?>"><img src="/static/api/shop/skin/default/images/sy.png">店铺逛逛</a></li>
        </ul>
    </div>
    <?php }?>
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
    <div class="xiangqing_sp">
        <p>商品详情</p>
        <?=$rsProducts["Products_Description"]?><br><br>
    </div>
    <div class="clear" style="height: 60px;"></div>

    <!-- 属性选择内容begin -->
    <div id="option_content">
        <div class="pro_top">
            <div style=" float:right;">
                <div id="close-btna" class="cjlose thi"></div>
            </div>
            <div class="imgchege"><img src="<?=$rsProducts['ImgPath']?>"></div>
            <div class="mid" style="float: none">
                <p class="price_pro" id="price"></p>
                <p id="count"></p>
                <p class="parameter_pro" id="showattr"></p>
            </div>
        </div>
        <div id="option_selecter">
            <form name="addtocart_form" action="/api/<?= $UsersID ?>/shop/cart/" method="post" id="addtocart_form">
                <input type="hidden" name="OwnerID" value="<?=$owner['id']?>"/>
                <input type="hidden" name="IsShippingFree" value="<?= $rsProducts["Products_IsShippingFree"] == 1 ? 1 : 0 ?>"/>
                <input type="hidden" name="ProductsWeight" value="<?=$rsProducts["Products_Weight"];?>"/>
                <input type="hidden" name="shu_value" id="shu_value" value=""/>
                <input type="hidden" name="shu_attribute" id="shu_attribute" value=""/>
                <input type="hidden" name="shu_priceg" id="shu_priceg" value=""/>
                <?php if(!empty($rsattrarr)){ ?>
                    <?php foreach($rsattrarr as $key=>$value){ ?>
                        <div class="pro_attribute">
                            <p><?=$key?></p>
                            <?php foreach($value as $ak=>$av){?>
                                <input type="button" class="sku" attr_id="<?=$ak?>" value="<?=$av?>"/>
                            <?php }  ?>
                        </div>
                    <?php }  ?>
                <?php }  ?>
                <div class="option-val-list">
                    <div id="qty_selector" style="line-height: 45px;font-size: 16px;font-weight: 600;color: #333;">
                        <input type="hidden" id="cur_price" value="<?=$cur_price?>"/>
                        <input type="hidden" id="no_attr_price" value="<?=$no_attr_price?>"/>
                        购买数量：<a href="javascript:void(0)" class="qty_btn" name="minus">-</a>
                        <input type="text" id="qtycount" name="Qty" maxlength="3" style="height:28px; line-height:28px; font-weight:normal;" value="1" pattern="[0-9]*" />
                        <a href="javascript:void(0)" class="qty_btn" name="add">+</a>
                        <br/>
                    </div>
                </div>
                <script>
                    $("#qtycount").blur(function(){

                        var qtycount = $("#qtycount").val();
                        var totalCount = $("#stock_val").text();
                        //alert(qtycount);alert(totalCount);
                        if(qtycount>totalCount){
                            //$("#qtycount").val(1);
                        }
                    })

                </script>
                <input type="hidden" name="ProductsID" value="<?php echo $ProductsID ?>" />
            </form>
            <div style="height:40px;"></div>
            <a href="javascript:void(0);" id="selector_confirm_btn">确定</a>
        </div>
    </div>
    <!-- 属性选择内容end -->

    <div class="footer_pt">
        <input type="hidden" name="flag" value="<?= $flag ?>" />
        <ul>
            <li class="xiaodian"><a href="/api/<?=$UsersID?>/shop/union/"><img src="/static/api/shop/skin/default/images/sy.png">首页</a></li>

            <li style="background-image:none!important;" status="<?=$rsProducts['Products_IsFavourite']?>" class="shoucang" id="<?= $rsProducts['Products_IsFavourite']?'favorited':'favorite' ?>" productid="<?=$rsProducts['Products_ID']?>"><a href="javascript:;" style="<?=$rsProducts['Products_IsFavourite'] == 0 ? '' : 'color:#ff0000'?>"><img src="<?=$rsProducts['Products_IsFavourite'] == 0 ? '/static/api/shop/skin/default/images/sc.png' : '/static/api/shop/skin/default/images/sc_xx.png'?>">收藏</a></li>

            <li class="kefu"><a href="javascript:;" ONCLICK="javascript:location.href='<?=$rsBiz['Biz_KfProductCode']?>';"><img src="/static/api/shop/skin/default/images/kf.png">客服</a></li>

            <?php if ($rsProducts['Products_SoldOut'] == 1) { ?>
                <li class="no_pro">产品已下架</li>
            <?php } else if ($rsProducts['Products_Count'] <= 0) { ?>
                <li class="no_pro">库存不足</li>
            <?php }else{ ?>
                <?php if($rsProducts['Products_IsVirtual'] == 0 && $flashsale != 1){ ?>
                    <li class="dd_buy"><a href="javascript:void(0);" id="menu-addtocard-btn">加入购物车</a></li>
                    <li class="pt_buy"><a href="javascript:void(0);" id="menu-direct-btn">立即购买</a></li>
                <?php }else{ ?>
                    <li class="pt_buy" style="width: 55%;"><a href="javascript:void(0);" id="menu-direct-btn"><?= $flashsale == 1 ? '立即抢购' : '立即购买' ?></a></li>
                <?php } ?>
            <?php }?>
        </ul>
    </div>


    <?php
    $kfConfig=$DB->GetRs("kf_config","*","where Users_ID='".$UsersID."' and KF_IsShop=1 and KF_Code<>''");
    if($kfConfig){
        echo htmlspecialchars_decode($kfConfig["KF_Code"],ENT_QUOTES);
    }
    ?>
</div>
<div class="mask"></div>
<footer id="footer"></footer>
<?php if($rsConfig["CallEnable"] && $rsConfig["CallPhoneNumber"]){?>
    <script language='javascript'>var shop_tel='<?php echo $rsConfig["CallPhoneNumber"];?>';</script>
    <script type='text/javascript' src='/static/api/shop/js/tel.js?t=<?php echo time();?>'></script>
<?php }?>
<?php if(!empty($share_config)){?>
    <script language="javascript">
        var share_config = {
            appId:"<?php echo $share_config["appId"];?>",
            timestamp:<?php echo $share_config["timestamp"];?>,
            nonceStr:"<?php echo $share_config["noncestr"];?>",
            url:"<?php echo $share_config["url"];?>",
            signature:"<?php echo $share_config["signature"];?>",
            title:"<?php echo $share_config["title"];?>",
            desc:"<?php echo str_replace(array("\r\n", "\r", "\n"), "", $share_config["desc"]);?>",
            img_url:"<?php echo $share_config["img"];?>",
            link:"<?php echo $share_config["link"];?>"
        };
        $(document).ready(global_obj.share_init_config);
    </script>
<?php }?>
<script type="text/javascript">
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
    });

    /*头部导航固定*/
    window.onscroll = function () {
        var t = document.documentElement.scrollTop || document.body.scrollTop;
        var top_pt = $('.top_pt');
        if (t > 240) {
            top_pt.css({"background": "#fff","border-bottom":"1px #f4f4f4 solid"});
            top_pt.find('.left img').attr('src', '/static/api/shop/skin/default/images/black_arrow_left.png');
            top_pt.find('.right img').attr('src', '/static/api/shop/skin/default/images/gwc_xx.png');
        } else
        {  top_pt.css({"background":"0","border-bottom":"0"});
            top_pt.find('.left img').attr('src', '/static/api/shop/skin/default/images/back_60.png');
            top_pt.find('.right img').attr('src', '/static/api/shop/skin/default/images/gwc.png');
        }
    };

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

    /*收藏*/
    $(".shoucang").click( function () {
        var productId = parseInt($(this).attr('productid'));
        var action = 'favourite';
        var id = $(this).attr("id");
        var status = $(this).attr('status');
        if (status == 0) {
            $.post($('#addtocart_form').attr('action') + 'ajax/', {
                    productId: productId,
                    action: action
                },
                function(data) {
                    if (data.status == 0) {
                        self.location.href = data.url
                    } else if (data.status == 1) {
                        $('.shoucang').attr('status', '1').find('a').css({"display":"block","color":"#ff0000"}).find('img').attr('src', '/static/api/shop/skin/default/images/sc_xx.png');
                    }
                },
                'json');

        } else {
            $.post($('#addtocart_form').attr('action') + 'ajax/', {
                    productId: productId,
                    action: 'cancel_favourite'
                },
                function(data) {
                    if (data.status == 0) {
                        self.location.href = data.url
                    } else if (data.status == 1) {
                        $('.shoucang').attr('status', '0').find('a').css({"display":"block","color":"#666"}).find('img').attr('src', '/static/api/shop/skin/default/images/sc.png');
                    }
                },
                'json');
        }
    });

</script>
<div class='conver_favourite'><img src="/static/api/images/global/share/favourite.png" /></div>
<span style="display:none" id="buy-type"></span>
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/substribe.php');?>
<script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script>
<script language="javascript">
    var owner_id = "<?=$owner['id']?>";
    $(document).ready(function(){
        $("img").scrollLoading();
    });
    $(function(){
        //当滚动条的位置处于距顶部100像素以下时，跳转链接出现，否则消失
        $(function () {
            $(window).scroll(function(){
                if ($(window).scrollTop()>100){
                    $("#back-to-top").fadeIn(1500);
                }
                else
                {
                    $("#back-to-top").fadeOut(1500);
                }
            });

            //当点击跳转链接后，回到页面顶部位置

            $("#back-to-top").click(function(){
                $('body,html').animate({scrollTop:0},1000);
                return false;
            });
        });
    });

    /*优惠券*/
    $(".quan").hide()
    $(".youhui_dp").click( function () {
        layer.open({
            title: '<p class="quan_title">优惠券</p><span class="guanbi"><img src="/static/images/guan.png"></span>'
            ,shadeClose:true
            ,type: 1
            ,content:$ ('.quan').html()
            ,className: 'lay_yh'
            ,anim: 'up'
            ,style: 'position:fixed; bottom:0; left:0; width: 100%; padding:0; border:none;font-size:14px'
            ,success: function () {
                var yh_height=$(window).height()
                $(".lay_yh ul").height(yh_height*0.65-50);
                $(".lay_yh li").on('click', '.xhh', function(){
                    var me = $(this);
                    var status = $(this).parent("div").parent("li").attr('status');
                    var o=$(this).parent("div").parent("li");
                    o.children(".line_yh").children(".ling_yh").html('领取中...');
                    $.post('/api/' + UsersID + '/user/ajax/'+owner_id+'/', 'action=get_coupon&CouponID='+o.attr('CouponID'), function(data){
                        if(data.status==1){
//                            o.attr('status', '1').css({"background":"#b5b5b5"}).find('.main_yh_02').css({"color":"#b5b5b5"})
                            o.find('.ling_yh').html("已领取");
                            me.removeClass('xhh')
//                            o.attr("class","ling_yh")
                            layer.open({
                                content:data.msg,
                                skin:'msg',
                                time:1,
                            })
                        }else{
                            var index=layer.open({
                                content:data.msg,
                                btn:'确定',
                                yes:function(){
                                    if(data.url == undefined){
                                        layer.close(index);
//                                        o.attr('status', '1').css({"background":"#b5b5b5"}).find('.main_yh_02').css({"color":"#b5b5b5"})
                                        o.find('.ling_yh').html("已领取");
                                        me.removeClass('xhh')
                                    }else{
                                        location.href = data.url;
                                    }
                                }
                            })
                        };
                    }, 'json');

                })
                $('.lay_yh .guanbi').click(function () {
                    layer.closeAll();
                });
            }
        });
    });
</script>
</body>
</html>
