<?php
require_once('global.php');
date_default_timezone_set('PRC');
$cate_id = isset($_GET['CategoryID']) ? $_GET['CategoryID'] : '' ;
if (!empty($cate_id) || $cate_id === 0) {
$cate = $DB->GetAssoc('shop_category','*','where Users_ID="'.$UsersID.'" and Category_ParentID='.$cate_id.' order by Category_IndexShow,Category_ID asc');
$cate_img = $DB->GetRs('shop_category','Category_Head_Img','where Users_ID="'.$UsersID.'" and Category_ID='.$cate_id)['Category_Head_Img'];
}
$codtion = 'where Users_ID="' . $UsersID . '" and Products_SoldOut=0 and Products_Status=1 ';
$param = '';

$starttime = isset($active_info['starttime']) ? $active_info['starttime'] : 0 ;;
$endtime = isset($active_info['stoptime']) ? $active_info['stoptime'] : 0 ;
$nowtime = time();

// 获得热卖产品
if (isset($_GET['Is_hot']) && $_GET['Is_hot'] == 1) {
    $param = 'Is_hot';
    $imgurl = $DB->GetRs('active', 'imgurl', 'where Users_ID = "' . $UsersID . '" and Status = 1 and Type_ID = 6')['imgurl'];
}

// 获得超级团
if (isset($_GET['super_team']) && $_GET['super_team'] == 1) {
    $param = 'super_team';
    $imgurl = $DB->GetRs('active', 'imgurl', 'where Users_ID = "' . $UsersID . '" and Status = 1 and Type_ID = 8')['imgurl'];
}

// 获得限时抢购
if (isset($_GET['flashsale_flag']) && $_GET['flashsale_flag'] == 1) {
        $param = 'flashsale_flag';
        $imgurl = $DB->GetRs('active', 'imgurl', 'where Users_ID = "' . $UsersID . '" and Status = 1 and Type_ID = 5 and starttime < ' . time() . ' and stoptime > ' . time())['imgurl'];

}
// 获得特价
if (isset($_GET['special_offer']) && $_GET['special_offer'] == 1) {
    $param = 'special_offer';

    $imgurl = $DB->GetRs('active', 'imgurl', 'where Users_ID = "' . $UsersID . '" and Status = 1 and Type_ID = 7')['imgurl'];
}
if($_POST){

  if (!empty($_POST['param'])) {
      if ($_POST['param'] == 'Is_hot') {
          $codtion .= ' and Products_SoldOut=0 and Products_Status=1 and Products_IsHot = 1';
      } else if($_POST['param'] == 'super_team') {
          $codtion .=' and Products_SoldOut=0 and Products_Status=1 and pintuan_flag = 1 and pintuan_end_time > ' . time() . ' and pintuan_start_time <= ' . time() . ' and pintuan_people >= 5 ';  //如果人数超过5个,拼团价小于现价的50%,则加入超级团
      } else if($_POST['param'] == 'flashsale_flag') {
          if ($flashsale == 1) {
              $codtion .= ' and Products_SoldOut=0 and Products_Status=1 and flashsale_flag = 1';
          } else {
              $data = [
                  'products' => [],
                  'totalpage' => 1,
                  'status' => 1
              ];
              echo  json_encode($data); exit;
          }
      } else if($_POST['param'] == 'special_offer') {
          $codtion .= ' and ((Products_PriceX >= 8 and Products_PriceX <= 12) or (pintuan_pricex >= 8 and pintuan_pricex <= 12))';
      }
  }

  $page = $_POST['page'];
  $limitpage = 4;
  $count = $DB->GetRs('shop_products','count(*) as count',$codtion)['count'];

  $pro_cate_id = isset($_POST['pro_cate_id']) ? $_POST['pro_cate_id'] : '';
  $totalpage = ceil($count/$limitpage);//总计页数
    if (!empty($pro_cate_id)) {
        $codtion .= ' and Products_Category='.$pro_cate_id;
    }
  $codtion .= ' LIMIT '.(($page - 1) * $limitpage).',' . $limitpage;
  $products = $DB->GetAssoc('shop_products','*',$codtion);
    foreach ($products as $k => $v) {
        $v['Products_JSON'] = empty($v['Products_JSON']) ? '' : json_decode($v['Products_JSON'], true);
        $v['ImgPath'] = empty($v['Products_JSON']['ImgPath'][0]) ? '' : $v['Products_JSON']['ImgPath'][0];
        $v['pt_flag'] = pro_pt_flag($v);
        // 判断是否是限时抢购跳转
        if ($_POST['param'] == 'flashsale_flag' && $flashsale == 1) {
            $v['link'] = $shop_url . 'products/' . $v['Products_ID'] . '/?flashsale_flag=1';
        } else {
            $v['link'] = $v['pt_flag'] ? $pintuan_url . 'xiangqing/' . $v['Products_ID'] . '/' : $shop_url . 'products/' . $v['Products_ID'] . '/';
        }

        if ($param == 'super_team') {
            $v['pro_price'] = $v['pintuan_pricex'];
            $v['btn_text'] = '去开团';
        } else {
            $v['pro_price'] = $flashsale && isset($_POST['param']) && $_POST['param'] == 'flashsale_flag' ? $v['flashsale_pricex'] : ($v['pt_flag'] ? $v['pintuan_pricex'] : $v['Products_PriceX']);
            $v['btn_text'] = $flashsale && isset($_POST['param']) && $_POST['param'] == 'flashsale_flag' ? '去抢购' : ($v['pt_flag'] ? '去开团' : '去购买');
        }

        $products[$k] = $v;
    }

      $data = [
          'products' => $products,
          'totalpage' => $totalpage,
          'status' => 1
      ];
    echo  json_encode($data); exit;
}
?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no" />
    <title>分类</title>
    <style>
        .colockbox_q {margin: 10px auto;text-align: center;width: 86%;background: #ff4200;color: #fff;line-height: 24px;font-size: 13px;padding: 5px 0;border-radius: 100px;}
        .colockbox_q span {width: 24px;height: 24px;line-height: 24px;text-align: center;color: #ff4200;background: #fff5e3;font-size: 13px;display: inline-block;border-radius: 50%;}
    </style>
</head>
<link rel="stylesheet" href="/static/css/index.css?t=1">
<link rel="stylesheet" href="/static/css/swiper.min.css">
<link rel="stylesheet" href="/static/css/index_media.css">
<script type="text/javascript" src="/static/js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="/static/js/swiper.min.js"></script>
<!--懒加载-->
<script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script>
<body>
<div class="w">
    <div class="top_pt">
        <span class="left">
            <a href="javascript:history.back(-1)"><img src="/static/images/back_60.png"></a>
        </span>
    </div>
    <div class="top_pro">
        <img id="cate_head_img" src="<?= !empty($imgurl) ? $imgurl : (empty($cate_img) ? '/static/images/no_pic.png' : $cate_img)?>">
    </div>
    <?php if (isset($_GET['flashsale_flag']) && $_GET['flashsale_flag'] == 1) {
        if ($flashsale == 1) {?>
    <div class="colockbox_q" id="demo02">
        距活动结束仅剩：
        <span class="day" id="RemainD"><strong></strong></span>天
        <span class="hour" id="RemainH"><strong></strong></span>时
        <span class="minute" id="RemainM"><strong></strong></span>分
        <span class="second" id="RemainS"><strong></strong>.<strong id="RemainL"></strong></span>秒
    </div>
    <?php } else { ?>
            <div class="colockbox_q" id="demo02">
                活动已结束
            </div>
        <?php }
    }?>
    <div class="clear"></div>
    <?php if(!empty($cate)){?>
    <div id="pro_nav" class="swiper-container">
        <ul class="swiper-wrapper" style="    height: 42px;">
            <?php foreach ($cate as $k => $v){?>
            <div class="swiper-slide <?= $k==0 ? 'active' : '' ?>" cate_id="<?=$v['Category_ID']?>"><span><?=$v['Category_Name']?></span></div>
            <?php }?>
        </ul>
    </div>
    <?php }?>
    <div class="clear"></div>

    <div class="products"></div>
    <div class="pullUp get_more" page="1" style="text-align: center;margin-bottom: 30px;"> <span class="pullUpIcon"></span><span class="pullUpLabel">点击加载更多...</span> </div>
</div>
<script type="text/javascript">
    var UsersID = "<?=$UsersID?>";
    var param = "<?=$param?>";
    var shop_url = "<?=$shop_url?>";
    var pintuan_url = "<?=$pintuan_url?>";
    var ajaxover = false;
    var cate_id = "<?= isset($cate[0]['Category_ID']) ? $cate[0]['Category_ID'] : $cate_id ?>";
	
	function checkTime(i){ //将0-9的数字前面加上0，例1变为01 
	  if(i<10) 
	  { 
		i = "0" + i; 
	  } 
	  return i; 
	}
	
	function activeTimer(){
		var date = new Date();
		var now = date.getTime();
		//设置截止时间
		var endDate = new Date("<?=date("Y-m-d H:i:s", $endtime) ?>");
		var end = endDate.getTime();
		//时间差
		var leftTime = end-now;
		//定义变量 d,h,m,s保存倒计时的时间
		var d,h,m,s;
		if (leftTime>=0) {
			d = Math.floor(leftTime/1000/60/60/24);
			h = Math.floor(leftTime/1000/60/60%24);
			m = Math.floor(leftTime/1000/60%60);
			s = Math.floor(leftTime/1000%60);
			d = checkTime(d);
			h = checkTime(h);
			m = checkTime(m);
			s = checkTime(s);
			$("#RemainD").html(d);
			$("#RemainH").html(h);
			$("#RemainM").html(m);
			$("#RemainS").html(s);
		}

	}
	
    $(function(){
		setInterval("activeTimer()",1000); 
	});

check_cate(cate_id,1)
    function check_cate(cate_id,page){
        $.ajax({
            url:'?',
            type:'post',
            data:{
                'pro_cate_id':cate_id,
                'param':param,
                'page' : page
            },
            success:function(data){
                if(data.status == 1){
                    if(data.products == ''){
//                        var html = '<span style="text-align: center;width: 100%;color:#666;line-height: 50px;display:inline-block;">该分类下暂无产品！</span>';
                        $(".get_more").hide();
                        ajaxover = false;
                    }else{
                        var html ='<ul class="hide">';
                        $.each(data.products,function(key,val){
                            html += '<li>';
                            html +='<span class="left_product"><a href="' + val['link'] + '"><img src="' + val['ImgPath'] + '"></a></span>';
                            html += '<span class="right_product">';
                            html += '<p class="product_title"><a href="' + val['link'] + '">' + val['Products_Name'] + '</a></p>';
                            html += '<p class="yituan">' +(val['pt_flag'] ? '已团' : '已售') + val['Products_Sales'] + ' 件</p>';
                            html += '<div class="price_product">';
                            html += ' <span class="price_pro_list"><font>￥</font>' + val['pro_price'] + '<span class="price_y_pro">￥' + val['Products_PriceY'] +'</span></span>';
                            html += '<a href="' + val['link'] + '"><span class="goumai">'+ val['btn_text'] +'</span></a>';
                            html += '</div></span></li>'
                        });
                        html += '</ul>';

                        if(data['totalpage'] <= $(".get_more"+cate_id).attr('page')){
                            $(".get_more").hide();
                            ajaxover = false;
                        } else {
                            ajaxover = true;
                        }
                    }
                    $(".products").append(html);

                }
//                if(data.cate_img != ''){
//                    $('#cate_head_img').attr("src",data.cate_img);
//                }else{
//                    $('#cate_head_img').attr("src",'/static/images/no_pic.png');
//                }

            },
            dataType:'json'
        })
    }

    $(function () {
        $(".get_more").click(function(){
            var page = parseInt($(this).attr("page"))+1;
            $(this).attr('page', page);
            check_cate($('.active').attr('cate_id'),page);
        })

        /*头部导航固定*/
        window.onscroll = function () {
            var t = document.documentElement.scrollTop || document.body.scrollTop;
            var top_pt = $('.top_pt');
            if (t > 90) {
                top_pt.css({"background": "#fff","border-bottom":"1px #f4f4f4 solid"});
                top_pt.find('.left img').attr('src', '/static/images/back.png');
            } else
            {  top_pt.css({"background":"0","border-bottom":"0"});
                top_pt.find('.left img').attr('src', '/static/images/back_60.png');
            }
        };

        /*滑动导航*/
        var mySwiper = new Swiper('#pro_nav', {
            freeMode: true,
            freeModeMomentumRatio: 0.5,
            slidesPerView: 'auto',
        });
		if(mySwiper.container[0]!=undefined){
			swiperWidth = mySwiper.container[0].clientWidth;
			maxTranslate = mySwiper.maxTranslate();
			maxWidth = -maxTranslate + swiperWidth / 2
			$(".swiper-container").on('touchstart', function(e) {
				e.preventDefault()
			})
			mySwiper.on('tap', function(swiper, e) {
				//	e.preventDefault()
				slide = swiper.slides[swiper.clickedIndex];
				slideLeft = slide.offsetLeft;
				slideWidth = slide.clientWidth;
				slideCenter = slideLeft + slideWidth / 2;
				// 被点击slide的中心点
				mySwiper.setWrapperTransition(300);
				if (slideCenter < swiperWidth / 2) {
					mySwiper.setWrapperTranslate(0);
				} else if (slideCenter > maxWidth) {
					mySwiper.setWrapperTranslate(maxTranslate);
				} else {
					nowTlanslate = slideCenter - swiperWidth / 2
					mySwiper.setWrapperTranslate(-nowTlanslate);
				}
				$("#pro_nav  .active").removeClass('active');
				$("#pro_nav .swiper-slide").eq(swiper.clickedIndex).addClass('active');

				// 动态根据数据改变产品
				var pro_cate_id = $("#pro_nav .active").attr("cate_id");
				check_cate(pro_cate_id,1);
				$(".products").html('');
			});
		}


        //Default Action
        $(".tab_content").hide(); //Hide all content
        $("ul.tabs li:first").addClass("active").show(); //Activate first tab
        $(".tab_content:first").show(); //Show first tab content
        //On Click Event
        $("ul.tabs li").click(function() {
            $("ul.tabs li").removeClass("active"); //Remove any "active" class
            $(this).addClass("active"); //Add "active" class to selected tab
            $(".tab_content").hide(); //Hide all tab content
            var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
            $(activeTab).fadeIn(); //Fade in the active content
            return false;
        });

    });
    $(window).bind('scroll', function () {
        // 当滚动到最底部以上100像素时， 加载新内容
        if ($(document).height() - $(this).scrollTop() - $(this).height() < 100) {
            if (ajaxover) { //当ajaxover为真的时候，说明还有未加载的产品
                $(".get_more").trigger('click');
                ajaxover = false;   //防止连续请求
            } else {
                return false;
            }
        }
    });
</script>
</body>
</html>