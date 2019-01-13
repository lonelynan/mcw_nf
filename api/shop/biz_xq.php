<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/support/tool_helpers.php');

if(empty($_GET['Biz_ID'])){
    echo '参数错误！';exit;
}

$_SESSION[$UsersID . "HTTP_REFERER"] = '/api/' . $UsersID . '/shop/biz_xq/';
$Biz_ID = $_GET['Biz_ID'];
$UserID = isset($_SESSION[$UsersID.'User_ID']) ? $_SESSION[$UsersID.'User_ID'] : '' ;
$rsBiz=$DB->GetRs("biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$Biz_ID.' and Biz_Status=0');

if($_POST){
    $codtion = 'where Users_ID="'.$UsersID.'" and Products_SoldOut=0 and Products_Status=1 and Biz_ID='.$Biz_ID;

    $page = $_POST['page'];
    $limitpage = 4;
    $count = $DB->GetRs('shop_products','count(*) as count','where Users_ID="'.$UsersID.'" and Products_SoldOut=0 and Products_Status=1 and Biz_ID='.$Biz_ID)['count'];
    $totalpage = ceil($count/$limitpage);//总计页数

    $codtion .=' limit '.(($page - 1) * $limitpage).','.$limitpage;

    $products = $DB->GetAssoc('shop_products','*',$codtion);
    if(!empty($products)){
        $data=[
            'pro' => $products,
            'status' => 1,
            'totalpage' => $totalpage
        ];
    }else{
        $data=[
          'status' => 0
        ];
    }
    echo json_encode($data);exit;
}
$pro = $DB->GetAssoc('shop_products','*','where Users_ID="'.$UsersID.'" and Products_SoldOut=0 and Products_Status=1 and Biz_ID='.$Biz_ID);
//
$biz = $DB->GetRs('biz','*','where Users_ID="'.$UsersID.'" and Biz_ID='.$Biz_ID);

$stores = $DB->GetAssoc('biz_stores','*','where Users_ID="'.$UsersID.'" and Biz_ID='.$Biz_ID);

$order_commit = $DB->GetAssoc('user_order_commit','c.*,u.*','as c left join user as u on c.User_ID=u.User_ID where c.Users_ID="'.$UsersID.'" and c.Biz_ID='.$Biz_ID.' and c.Status=1');
$coupon = $DB->GetAssoc("user_coupon",'*','where Users_ID="'.$UsersID.'" and Biz_ID=' . $Biz_ID . ' and Coupon_EndTime >'.time());
$home_json = $DB->GetRs('biz_home','Home_JSON','where Biz_ID='.$Biz_ID.' and Skin_ID='.$biz['Skin_ID'])['Home_JSON'];
$Home_JSON = json_decode($home_json,true);

$banner = [];
if($Home_JSON){
    foreach ($Home_JSON as $k => $v){
        if ($v['ContentsType'] == 1) {  //banner
            foreach ($v['ImgPath'] as $kk => $vv) {
                if (strpos($vv, '/api/') === false) {
                    $banner[$kk]['ImgPath'] = !empty($vv) ? $vv : '';
                } else {
                    $banner[$kk]['ImgPath'] = !empty($vv) ? '/static' . $vv : '';
                }
                $banner[$kk]['Url'] = !empty($v['Url'][$kk]) ? $v['Url'][$kk] : '';
            }
        }
    }
}

if(!empty($UserID)) {
    $user_coupon = $DB->GetAssoc('user_coupon_record','','where Users_ID="' . $UsersID . '" and Biz_ID=' . $Biz_ID . ' and User_ID='.$UserID);

    $user_coupon_ID = [];
    if(!empty($user_coupon)){
        foreach ($user_coupon as $k => $v){
            $user_coupon_ID = $v['Coupon_ID'];
        }
    }
}


?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no" />
    <title><?=$biz['Biz_Name']?></title>
</head>
<link rel="stylesheet" href="/static/css/index.css">
<link rel="stylesheet" href="/static/css/swiper.min.css">
<link rel="stylesheet" href="/static/css/index_media.css">
<link rel="stylesheet" href="/static/css/layer.css">
<script type="text/javascript" src="/static/js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="/static/js/swiper.min.js"></script>
<script type="text/javascript" src="/static/js/layer/mobile/layer.js"></script>
<body>
<style>
    .swiper-pagination-bullet{background:none;border:1px #ff494e solid;}
    .swiper-pagination-bullet-active{background: #ff494e;border:1px #ff494e solid;}
    .lay_yh h3,.lay_md h3{padding: 0;line-height: 50px;height: 50px;}
    .guanbi{position: absolute;top:10px; right: 10px;z-index: 20;}
    .guanbi img{display: block; width: 24px; height: 24px;}
    .quan_title{line-height: 50px; width: 100%; text-align: center;font-size: 15px;}
    .top_back {width: 100%;height: 50px;line-height: 50px;text-align: center;font-size: 15px;position: relative;}
    .top_back > a {display: inline-block;padding: 0 5px;position: absolute;left: 0; top: -2px;}
    .top_back > a img {width: 30px;height: 30px;vertical-align: middle;}
    .top_back > span {font-size: 15px;}
    a:hover{
        color:#333;
    }
    .maidan:hover{
        color:#fff;
    }
    @media all and (min-width:320px){
        .left_img_dp img,.pro_img img{height:91px;}
    }
    @media all and (min-width:360px){
        .left_img_dp img,.pro_img img{height: 103px;}
    }
    @media all and (min-width:375px){
        .left_img_dp img,.pro_img img{height:107px;}
    }
    @media all and (min-width:411px){
        .left_img_dp img,.pro_img img{height: 118px;}
    }
    @media all and (min-width:420px){
        .left_img_dp img,.pro_img img{height: 120px;}
    }
    @media all and (min-width:435px){
        .left_img_dp img,.pro_img img{height: 125px;}
    }
    @media all and (min-width:480px){
        .left_img_dp img,.pro_img img{height:138px;}
    }
    @media all and (min-width:560px){
        .left_img_dp img,.pro_img img{height: 160px;}
    }
    @media all and (min-width:640px){
        .left_img_dp img,.pro_img img{height:183px;}
    }
    @media all and (min-width:750px){
        .left_img_dp img,.pro_img img{height: 215px;}
    }

</style>
<div class="top_back">
    <a href="javascript:history.back(-1)"><img src="/static/api/shop/skin/default/images/back_1.png"></a>
    <span>商家详情</span>
</div>
<div class="w">
    <div class="swiper-container sw_hd">
        <?php if(!empty($banner)){?>
        <div class="swiper-wrapper">
            <?php foreach ($banner as $key => $value){?>
            <div class="swiper-slide">
                <a href="<?=!empty($value['Url']) ? $value['Url'] : (!empty($value['ImgPath']) ? $value['ImgPath'] : '/static/images/no_pic.png')?>"><img class="banner" src="<?=!empty($value['ImgPath']) ? $value['ImgPath'] : '/static/images/no_pic.png'?>"/></a>
            </div>
            <?php }?>
        </div>
        <?php }?>
        <div class="swiper-pagination sw_hd_x"></div>
    </div>
    <div class="clear"></div>
    <h2 class="shangjia_name"><?=$rsBiz['Biz_Name']?></h2>
    <div class="add_sj">
		<span class="left_add_sj">
			<img src="/static/images/shangjia_07.png">
			<a href="http://api.map.baidu.com/marker?location=<?=$rsBiz['Biz_PrimaryLat']?>,<?=$rsBiz['Biz_PrimaryLng']?>&output=html"><span class="add_sj_main">地址：<?=$rsBiz['Biz_Address']?></span></a>
		</span>
        <span class="line_add"></span>
        <span class="phone_sj">
            <a href="tel:<?=$rsBiz['Biz_Phone']?>">
            <img src="/static/images/shangjia_04.png">
            </a>
        </span>
    </div>
    <div class="clear"></div>

    <?php if(!empty($stores)){?>
    <div class="mendian">
        <span class="left_mendian"><img src="/static/images/shangjia_16.png">其他门店</span>
        <span class="right_mendian">查看更多门店<img src="/static/images/yuanqu_03.png"></span>
    </div>
    <?php }?>
    <!--    <div class="yingye"><img src="/static/images/shangjia_12.png">营业时间：7:00-23:00</div>-->
    <?php if(!empty($coupon)){?>
    <div class="youhui_dp">
        <span class="left_youhui_dp"><img src="/static/images/shangjia_16.png">店铺优惠</span>
        <span class="right_youhui_dp">领取优惠券<img src="/static/images/yuanqu_03.png"></span>
    </div>
    <?php }?>

    <div class="zaishou">
            <p class="zaishou_title"><img src="/static/images/shangjia_20.png">本店在售</p>
            <div class="product">
            <ul>
            </ul>
        </div>
        <?php if(!empty($pro)){?>
        <a id="load_pro" page="1" href="javascript:;" class="more">查看更多<img src="/static/images/hunli_26.png"></a>
        <?php }?>
    </div>

    <div class="clear"></div>
    <?php if(!empty($order_commit)){?>
    <div class="pinglun">
        <p class="zaishou_title"><img src="/static/images/shangjia_28.png">评论</p>
        <ul>
            <?php foreach ($order_commit as $k => $v){
                $order_img = isset($v['ImgPath']) ?  json_decode($v['ImgPath'],true) : '';
                ?>
            <li>
                <p class="top_pl">
                    <span class="left_pl"><img src="<?=!empty($v['User_HeadImg']) ? $v['User_HeadImg'] : '/static/api/shop/skin/default/images/face.jpg' ?>"><?=$v['User_NickName']?></span>
                    <span class="right_pl"><?=date('Y.m.d',$v['CreateTime'])?></span>
                </p>
                <p class="main_pl"><?=$v['Note']?></p>
                <?php if(!empty($order_img)){?>
                <div class="img_pl">
                    <?php foreach ($order_img as $key => $value){?>
                    <img src="<?=$value?>">
                    <?php }?>
                </div>
                <?php }?>
            </li>
            <?php }?>
        </ul>
        <a href='/api/<?=$UsersID?>/shop/commit/?Biz_ID=<?=$rsBiz['Biz_ID']?>' class="more">查看更多<img src="/static/images/hunli_26.png"></a>
    </div>
    <?php }?>

    <div class="dian_other">
        <ul>
            <?php foreach ($stores as $k => $v){?>
            <li>
                <p class="mendian_name"><?=$v['Stores_Name']?></p>
                <div class="add_md">
                    <a href='http://api.map.baidu.com/marker?location=<?=$v['Stores_PrimaryLat']?>,<?=$v['Stores_PrimaryLng']?>&output=html' class="left_add_md">
                        <img src="/static/images/shangjia_07.png">
                        <span class="add_md_main">地址：<?=$v['Stores_Address']?></span>
                    </a>
                    <span class="line_add_md"></span>
                    <a href="tel::<?=$v['Stores_Telephone']?>" class="phone_md"><img src="/static/images/shangjia_04.png"></a>
                </div>
            </li>
            <?php }?>
        </ul>
    </div>

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

    <div class="clear" style="height: 50px;"></div>
    <?php if($rsBiz['Biz_stmdShow'] == 1){?>
    <a href="<?php echo $shop_url;?>biz/<?php echo $Biz_ID;?>/paymoney/" class="maidan">优惠买单</a>
    <?php }?>
</div>

<script type="text/javascript">
    var ajaxover = false;
    var owner_id = "<?=$owner['id']?>";
    var UsersID = "<?=$UsersID?>";
    var shop_url = "<?=$shop_url?>";
    var pintuan_url = "<?=$pintuan_url?>";


    loading_products(1)
    function loading_products(page){
         $.ajax({
             url:'?',
             type:"post",
             data:{
               'page':page,
             },
             success:function(data){
                 if(data.status == 1){
                     var html = '';
                     $.each(data.pro,function(k,v){
                         var img = JSON.parse(v['Products_JSON'])
                         var pintuan_pro = pro_pt_flag(v)

                         html += '<li>';
                         html += '<a href="'+ (pintuan_pro == true ? pintuan_url + 'xiangqing/'+ v['Products_ID'] + '/' : shop_url+'products/' + v['Products_ID'] + '/') +'">';
                         html += '<span class="pro_img"><img src="' + img['ImgPath'][0] + '"></span>';
                         html += '<span class="pro_main">';
                         html += '<p class="pro_title">' + v['Products_Name'] + '</p>';
                         html += '<p class="hj_pro">' + v['Products_BriefDescription'] + '</p>';
                         html += '<div class="pro_pri">';
                         html += '<span class="pro_left"><font>￥</font>' + (pintuan_pro == true ? v['pintuan_pricex'] : ( v['Products_PriceA'] == 0 ? v['Products_PriceX'] : v['Products_PriceA'] )) + '<span class="pri_y">' + v['Products_PriceY'] + '</span></span>';
                         html += '<span class="pro_right">已售' + v['Products_Sales'] + '</span>';
                         html += '</div>';
                         html += '</span>';
                         html += '</a>';
                         html += '</li>';
                     })
                     if(data.totalpage <= $("#load_pro").attr('page')) {
                         $("#load_pro").hide();
                     }
                 }else{
                    var html = '';
                 }
                 $(".product ul").append(html)
             },
             dataType:'json'
         })
    }

    //        判断是否是拼团产品
    function pro_pt_flag(pro){
        var timestamp = Date.parse(new Date()) / 1000;

        var flag = false;
        if(pro['pintuan_flag'] != 'undefined' && pro['pintuan_people'] != 'undefined' && pro['pintuan_start_time'] != 'undefined' && pro['pintuan_end_time'] != 'undefined' && pro['pintuan_pricex'] != 'undefined'){
            flag = pro['pintuan_flag'] == 1 && pro['pintuan_people'] >= 2 && pro['pintuan_start_time'] < timestamp && pro['pintuan_end_time'] > timestamp && pro['pintuan_pricex'] > 0;
        }
        return flag;
    }


    $(function () {
        //banner
        var mySwiper = new Swiper('.sw_hd', {
            direction: 'horizontal',
            loop: true,
            autoResize: true,
            pagination: '.sw_hd_x',
            paginationClickable: true,
            autoplay: 3000,
            autoplayDisableOnInteraction: false
        });
    });

//    加载产品
    $('#load_pro').click(function(){
        var page = parseInt($(this).attr("page"))+1;
        $(this).attr('page', page);
        loading_products(page);
    })


    /*门店*/
    $(".dian_other").hide()
    $(".mendian").click( function () {
        layer.open({
            title: '<p class="quan_title">其他门店</p><span class="guanbi"><img src="/static/images/guan.png"></span>'
            ,shadeClose:true
            ,type: 1
            ,content:$ ('.dian_other').html()
            ,className: 'lay_md'
            ,anim: 'up'
            ,style: 'position:fixed; bottom:0; left:0; width: 100%; padding:0; border:none;font-size:14px'
            ,success: function () {
                var yh_height=$(window).height()
                $(".lay_md ul").height(yh_height*0.65-50);
                $('.lay_md .guanbi').click(function () {
                    layer.closeAll();
                });
            }
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
                            o.find('.ling_yh').html("已领取");
                            me.removeClass('xhh')
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
<?php if($rsBiz["Biz_Kfcode"]){
    echo htmlspecialchars_decode($rsBiz["Biz_Kfcode"],ENT_QUOTES);
}?>
</body>
</html>
