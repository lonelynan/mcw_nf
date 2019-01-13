<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
use Illuminate\Database\Capsule\Manager as DB;

$result       = "";
$time         = time();

$active_info = getActive($UsersID, 8);
if(empty($active_info)){
	sendAlert("没有相关活动");
}

$pinConfig = DB::table("pintuan_config")->where(["Users_ID"=> $UsersID])->first();
if(empty($pinConfig)){
    $pinConfig = [
        'Users_ID' => $UsersID,
        'banner_img' => '',
        'SiteName' => '拼团',
        'info' => '',
        'banner_url' => '',
        'is_ems' => 0,
        'is_back' => 0,
        'CallEnable' => 0,
        'CallPhoneNumber' => ''
    ];
}
$rsConfig = array_merge($rsConfig, $pinConfig);
//获取幻灯片列表
$slider = [];
if (!empty($rsConfig['banner_img']) && !empty($rsConfig['banner_url'])) {
    $timg = json_decode($rsConfig['banner_img'], true);
    $turl = json_decode($rsConfig['banner_url'], true);
    if ($timg && $timg[0] && $turl && $turl[0]) {
        $banner_img = array_filter(json_decode($rsConfig['banner_img'], true));
        $banner_url = array_filter(json_decode($rsConfig['banner_url'], true));

        $slider = array();
        for ($i = 0, $len = count($banner_img); $i < $len; $i ++) {
            $slider[$i]['img'] = getImageUrl($banner_img[$i],2);
            if (isset($banner_url[$i])) {
                $slider[$i]['url'] = $banner_url[$i];
            }
        }
    }
}

//在指定的时间内参与拼团并且没有参与限时抢购，勾选在商城首页显示以及非下架的情况下显示
$total = DB::table("shop_products")->where(['Users_ID' => $UsersID, 'Products_SoldOut' => 0, 'Products_IsShow' => 1, 'pintuan_flag' => 1, 'flashsale_flag' => 0])->where('pintuan_start_time', "<=", $time)->where("pintuan_end_time", ">=", $time)->count();
$pagesize = 4;

$totalPage = $total %$pagesize == 0?$total /$pagesize:intval($total/$pagesize)+1;
if(IS_AJAX){
    $page = I("page", 1);
    $sort = I("sort", 0);
    $isappend = I("isappend", -1);
    $method = I("sortmethod", "asc");
    $offset = 0;
    if($isappend == 1){
        $offset = ($page-1)*$pagesize;
    }else{
        $offset = 0;
        $pagesize = $page*$pagesize;
    }

    $order = [
				[
					"Products_ID",
					$method
				],
				[
					"Products_CreateTime",
					$method
				],
				[
					"Products_Sales",
					$method
				],
				[
					"pintuan_pricex",
					$method
				],
				[
					"pintuan_people",
					$method
				]
		];
    $fields = ["Products_ID","Products_Name","Products_CreateTime","Products_JSON","Users_ID","Products_IsNew","Products_IsRecommend","Products_IsHot","Products_Index","Products_Sales","Products_PriceX","pintuan_people","pintuan_pricex","pintuan_start_time","pintuan_end_time"];
		
	$list = DB::table("shop_products")->where(['Users_ID' => $UsersID, 'Products_SoldOut' => 0, 'Products_IsShow' => 1, 'pintuan_flag' => 1, 'flashsale_flag' => 0])->where('pintuan_start_time', "<=", $time)->where("pintuan_end_time", ">=", $time)->orderBy
		($order[$sort][0], $order[$sort][0])->offset($offset)->limit($pagesize)->get($fields);
    $time = time();
    $status = 0;
    if(!empty($list))
    {
        $status = 1;
        foreach($list as $k => $v)
        {
			$list[$k]['products_IsNew'] = $v['products_IsNew'] = $v['Products_IsNew'];
			$list[$k]['products_IsRecommend'] = $v['products_IsRecommend'] = $v['Products_IsRecommend'];
			$list[$k]['products_IsHot'] = $v['products_IsHot'] = $v['Products_IsHot'];
			$list[$k]['starttime'] = $v['starttime'] = $v['pintuan_start_time'];
			$list[$k]['stoptime'] = $v['stoptime'] = $v['pintuan_end_time'];
			$list[$k]['people_num'] = $v['people_num'] = $v['pintuan_people'];
			$list[$k]['Products_PriceT'] = $v['Products_PriceT'] = $v['pintuan_pricex'];
			$list[$k]['Products_PriceD'] = $v['Products_PriceD'] = $v['Products_PriceX'];
            $image = json_decode($v['Products_JSON'], true);
            $list[$k]['url'] = "/api/{$UsersID}/pintuan/xiangqing/{$v['Products_ID']}/";
            $path = $image['ImgPath']['0'];
            $list[$k]['imgpath'] = $path ? getImageUrl($path,1) : "'static/api/shop/skin/default/nopic.jpg'";
            if ($v['products_IsNew'] == 1) {
                // 新品
                $list[$k]['Tie'] = "1";
            } elseif ($v['products_IsRecommend'] == 1) {
                // 促销
                $list[$k]['Tie'] = "6";
            } elseif ($v['products_IsHot'] == 1) {
                // 热销
                $list[$k]['Tie'] = "2";
            }
            if (empty($v['Is_Draw'])) {
				$list[$k]['Draw'] = 0;
			} else {
				$list[$k]['Draw'] = 1;
			}
			$list[$k]['Products_Name'] = sub_str($v['Products_Name'], 16, false);
            $list[$k]['startDateTime'] = date("Y-m-d H:i:s",$v['stoptime']);
            $list[$k]['startStartTime'] = date("Y-m-d H:i:s",$v['starttime']);
            if($v['starttime']>$time){
                $lasttime = $v['starttime'] - time();
                $hour = intval($lasttime/3600)>0?intval($lasttime/3600):0;
                $minute = intval(($lasttime-360*$hour)/60)?intval(($lasttime-360*$hour)/60):0;
                $minute = intval($minute/60);
                $list[$k]['buttonTitle'] = ['status'=>0, 'data'=>['hour'=>$hour,'minute'=>$minute]];
            }else if ($v['stoptime']<$time){    //已结束
                $list[$k]['buttonTitle'] = ['status' =>-1 ];
            }else{  //开始
                $list[$k]['buttonTitle'] = ['status' =>1 ];
            }
        }
    }
    die(json_encode([ 'status'=>$status, 'data' => $list, 'method'=>$method, 'page' =>[
        'pagesize' => $total,
        'hasNextPage' => $total >= $pagesize ? true : false,
        'total' => $total,
    ]], JSON_UNESCAPED_UNICODE));
}
$title = $rsConfig['SiteName'] . "的拼团商城";
$img = "";
$url = PROTOCAL . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$desc = "";
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?=$rsConfig['SiteName'] . "的拼团商城" ?></title>
    <link href="/static/api/pintuan/css/css.css?t=<?=time() ?>" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/media.css?t=<?=time() ?>" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/style11.css?t=<?=time() ?>" rel="stylesheet" type="text/css">
    <script src="/static/api/pintuan/js/jquery.min.js"></script>
    <script src="/static/api/pintuan/js/common.js?t=<?=time() ?>"></script>
    <script src="/static/api/pintuan/js/responsiveslides.min.js"></script>
    <script type="text/javascript" src="/static/js/template.js"></script>
    <script type='text/javascript' src='/static/js/jquery.lazyload.min.js'></script>
    <script src="/static/api/pintuan/js/idangerous.swiper.min.js"></script>
    <script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script>
</head>
<body id="loader">
	<div class="w">
		<!-- 代码 开始 -->
		<div class="device">
			<a class="arrow-left" href="#"></a> <a class="arrow-right" href="#"></a>
			<div class="swiper-container">
				<div class="swiper-wrapper">
                  <?php
                  if (! empty($slider)) {
                      
                    foreach ($slider as $ks => $ress) {
                        if (isset($ress['url']) && isset($ress['img'])) {
                  ?>        
                	<div class="swiper-slide">
            			<a href="<?php echo $ress['url'];?>"><img src="<?php echo $ress['img'];?>"> </a>
            		</div>
                    <?php
                        } else if(isset($ress['img']) && $ress['img']) {
                    ?>
                    <div class="swiper-slide">
            			<img src="<?php echo $ress['img'];?>">
            		</div>
                    <?php
                        }
                    }
                  }
                  ?>
                </div>
                <div class="pagination"></div>
			</div>		
		</div>
        <!-- 代码部分end -->
        <!-- 代码 结束 -->
        <div class="clear"></div>
        <div class="sort">
            <ul>
                <li sort="1" method="asc">时间<span><i class="fa  fa-caret-up fa-x" aria-hidden="true"></i></span></li>
                <li sort="2" method="asc">销量<span><i class="fa  fa-caret-up fa-x" aria-hidden="true"></i></span></li>
                <li sort="3" method="asc">价格<span><i class="fa  fa-caret-up fa-x" aria-hidden="true"></i></span></li>
                <li sort="4" method="asc">综合<span><i class="fa  fa-caret-up fa-x" aria-hidden="true"></i></span></li>
            </ul>
        </div>
        <div id="container"></div>
        <input type="hidden" name="currentPage" value="2" />
        <?php
            require_once ( CMS_ROOT . '/api/pintuan/bottom.php');
            require_once ( CMS_ROOT . '/api/pintuan/share.php');
        ?>
    </div>
    <script id="loadContainer" type="text/html">
        {{each data as product i}}
        <div class="chanpin">
            <div class="biaoqian"><img data-original="/static/api/pintuan/images/xp_{{product.Tie}}.png" width="50px" height="50px"></div>
            <div class="tt"></div>
            <div style="width:35%; float:left;padding:1%;"><div class="tp l"><a href="{{product.url}}"><img data-original="{{product.imgpath}}"></a></div></div>
            <div class="jianjie1 l">
                <div>
                    <span class="ct l"><a href="{{product.url}}">{{product.Products_Name}}</a></span>
                    {{if (product.Draw == 1)}}
                    <span class="cj_choujiang r">抽奖</span>
                    {{/if}}
                </div>
                <div class="clear"></div>
                <div class="t1 l">已售<b style="font-Size:16px; color:#666; font-Weight:normal">{{product.Products_Sales}}</b>件</div>
                <div class="t10 l"></div>
                <div class="shou r"></div>
                <div class="clear"></div>
                <div class="t9 l">￥{{product.Products_PriceT}}</div>
                <div class="t8 l"><del>￥{{product.Products_PriceD}}</del></div>
                <div class="clear"></div>
                <div class="button_xx">
                    {{if (product.buttonTitle.status == 0)}}
                    <div class="tuan2 r"><a href="">即将开团</a></div>
                    {{elseif(product.buttonTitle.status == -1)}}
                    <div class="tuan2 r"><a href="">已结束</a></div>
                    {{else}}
                        <div class="tuan1 r"><a href="{{product.url}}">立即开团</a></div>
                    {{/if}}
                    <div class="tuan r">
                        <div class="t9_x r">{{product.people_num}}人团</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
        {{/each}}
    </script>
    <script>
        var url = "<?=$_SERVER['REQUEST_URI']?>";
        var UsersID = "<?=$UsersID ?>";
        var totalPage = <?=$totalPage?>;
        var page = 1;

        $(function(){
            var mySwiper = new Swiper('.swiper-container',{
                pagination: '.pagination',
                loop:true,
                grabCursor: true,
                paginationClickable: true,
                speed:750,
                autoplay:2000
            });
            $('.arrow-left').on('click', function(e){
                e.preventDefault();
                mySwiper.swipePrev();
            });
            $('.arrow-right').on('click', function(e){
                e.preventDefault();
                mySwiper.swipeNext();
            });
        });
        $(function(){
            var sort = 0;
            var method="asc";
            var marrow = "<i class='fa  fa-caret-up fa-x' aria-hidden='true'></i>";

            sessionStorage.setItem(UsersID + "ListSort", sort);
            sessionStorage.setItem(UsersID + "ListMethod", method);
            sessionStorage.setItem(UsersID + "currentPage", page);
            getContainer(url,page,sort,method,1);
            $(".sort ul li").click(function(){
                method = sessionStorage.getItem(UsersID + "ListMethod");
                if(method=="asc"){
                    method = "desc";
                    marrow = "<i class='fa  fa-caret-up fa-x' aria-hidden='true' style='color:#ee0a3b'></i>";
                }else{
                    method = "asc";
                    marrow = "<i class='fa  fa-caret-down fa-x' aria-hidden='true' style='color:#ee0a3b'></i>";
                }
                $(this).attr("method",method);
                $(this).find("span").html(marrow);
                sort = $(this).attr("sort");
                method = $(this).attr("method");
                sessionStorage.setItem(UsersID + "ListSort", sort);
                sessionStorage.setItem(UsersID + "currentPage", page);
                sessionStorage.setItem(UsersID + "ListMethod", method);
                getContainer(url,page,sort,method,-1);
            });

            //瀑布流加载翻页
            var last_pageno = 1;
            $(window).bind('scroll',function () {
                // 当滚动到最底部以上100像素时， 加载新内容
                if ($(document).height() - $(this).scrollTop() - $(this).height() < 100) {
                    //已无数据可加载
                    var currentPage = $("input[name='currentPage']").val();
                    var sort = sessionStorage.getItem(UsersID + "ListSort");
                    var method = sessionStorage.getItem(UsersID + "ListMethod");
                    if (currentPage == last_pageno) {
                        return false;
                    } else {
                        last_pageno = currentPage;
                    }
                    if (currentPage > totalPage) {
                        return true;
                    }
                    getContainer(url,currentPage,sort,method,-1);
                }
            });
        });
    </script>
</body>
</html>