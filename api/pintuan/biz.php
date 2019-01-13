<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

use Illuminate\Database\Capsule\Manager as DB;

$rsConfig = DB::table("pintuan_config")->where(["Users_ID"=> $UsersID])->first();
if(empty($rsConfig)){
    $rsConfig = [
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

$ActiveID     = I("ActiveID", 0);
if(!$ActiveID){
    sendAlert("ActiveID不存在");
}
$sql = "SELECT Users_ID,Active_ID,MaxBizCount,ListShowGoodsCount,BizShowGoodsCount FROM active WHERE Users_ID='{$UsersID}' AND Type_ID=1 AND Active_ID={$ActiveID} ";
$rsActive = DB::select($sql);
if(empty($rsActive)) {
    sendAlert("活动不存在");
}
$bizCount = $rsActive[0]['BizShowGoodsCount'];
$rsBiz = DB::table("biz")->where(['Users_ID' => $UsersID, 'Biz_ID' => $BizID])->first(["Biz_Account","Biz_Name"]);
if(empty($rsBiz)){
  sendAlert("商家店铺不存在");
}
$shopname = isset($rsBiz['Biz_Name']) && $rsBiz['Biz_Name']?$rsBiz['Biz_Name']:"";
$activelist = DB::table("biz_active")->where(['Users_ID' => $UsersID, 'Active_ID' => $ActiveID, 'Status' => 2])->offset(0)->limit($rsActive[0]['MaxBizCount'])->get(["ListConfig","Biz_ID","Active_ID"]);

if(empty($activelist)){
    sendAlert("没有商家参与相关活动");
}
$listGoods = "";
foreach ($activelist as $k => $v)
{
    $listGoods .= $v['ListConfig'].',';
}
$listGoods = trim($listGoods,',');

$total = DB::table("pintuan_products")->where(["Users_ID" => $UsersID, "Biz_ID" => $BizID])->whereRaw("Products_ID in ({$listGoods})")->count("*");
//以下内容是按需加载
$pagesize = 6;
$totalPage = $total%$pagesize == 0?$total/$pagesize:intval($total/$pagesize)+1;
if(IS_AJAX){
    $time = time();
    $page = I("page", 1);
    $sort = I("sort");
    $isappend = I("isappend", -1);
    $method = I("sortmethod", "asc");
    $offset = 0;
    if($isappend == 1){
        $offset = ($page-1)*$pagesize;
    }else{
        $offset = 0;
        $pagesize = $page*$pagesize;
    }

    $order = ["Products_ID {$method}","Products_CreateTime {$method}","Products_Sales {$method}","Products_PriceT {$method}","Products_Index {$method}"];
    $fields = "starttime,Products_CreateTime,Users_ID,Products_JSON,products_IsNew,products_IsRecommend,products_IsHot,Is_Draw,Products_ID,Products_Index,Products_Name,stoptime,Products_Sales,Products_PriceT,Products_PriceD,people_num";

    $sql = "SELECT {$fields} FROM (SELECT {$fields} FROM `pintuan_products` WHERE Users_ID='{$UsersID}' AND Biz_ID = {$BizID} AND Products_ID IN ({$listGoods}) AND Products_Status = 1  LIMIT {$bizCount}) as t ".
    ($sort?"ORDER BY {$order[$sort]}":"ORDER BY field(Products_ID,{$listGoods})")." LIMIT {$offset},{$pagesize}";
    $list = DB::select($sql);
    if(!empty($list))
    {
        $status = 1;
        foreach($list as $k => $v)
        {
            $image = json_decode($v['Products_JSON'], true);
            $path = $image['ImgPath']['0'];
            $list[$k]['imgpath'] = getImageUrl($path,1);
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
            if ($v['Is_Draw'] == 0) {
                $list[$k]['Draw'] = 1;
            } elseif ($v['Is_Draw'] == 1) {
                $list[$k]['Draw'] = 0;
            }
            $list[$k]['url'] = "/api/{$UsersID}/pintuan/xiangqing_{$BizID}_{$ActiveID}_{$v['Products_ID']}/";
            $list[$k]['Products_Name'] = sub_str($v['Products_Name'], 16, false);
            if($v['starttime']>$time){
                $lasttime = $v['starttime'] - time();
                $hour = intval($lasttime/3600)>0?intval($lasttime/3600):0;
                $minute = intval(($lasttime-360*$hour)/60)?intval(($lasttime-360*$hour)/60):0;
                $minute = intval($minute/60);
                $list[$k]['buttonTitle'] = json_encode(['status'=>0, 'data'=>['hour'=>$hour,'minute'=>$minute]]);
            }else if ($v['stoptime']<$time){
                $list[$k]['buttonTitle'] = json_encode(['status' =>-1 ]);
            }else{
                $list[$k]['buttonTitle'] = json_encode(['status' =>1 ]);
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
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?="{$title}" ?></title>
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
		<div class="sort">
			<ul>
				<li sort="1" method="asc">时间<span><i class="fa  fa-caret-up fa-x" aria-hidden="true"></i></span></li>
				<li sort="2" method="asc">销量<span><i class="fa  fa-caret-up fa-x" aria-hidden="true"></i></span></li>
				<li sort="3" method="asc">价格<span><i class="fa  fa-caret-up fa-x" aria-hidden="true"></i></span></li>
				<li sort="4" method="asc">综合<span><i class="fa  fa-caret-up fa-x" aria-hidden="true"></i></span></li>
			</ul>
		</div>
		<div class="clear"></div>
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
            <div style="width:35%; float:left;padding:1%;"><div class="tp l"><a href="/api/{{product.Users_ID}}/pintuan/xiangqing/{{product.Products_ID}}/"><img data-original="{{product.imgpath}}"></a></div></div>
            <div class="jianjie1 l">
                <div>
                    <span class="ct l"><a href="/api/{{product.Users_ID}}/pintuan/xiangqing/{{product.Products_ID}}/">{{product.Products_Name}}</a></span>
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
                    {{if (product.buttonTitle.status == 1)}}
                    <div class="tuan2 r"><a href="">即将开团</a></div>
                    {{else}}
                    {{if (product.url !=undefined)}}
                    <div class="tuan1 r"><a href="{{product.url}}">立即开团</a></div>
                    {{else}}
                    <div class="tuan1 r"><a href="/api/{{product.Users_ID}}/pintuan/xiangqing/{{product.Products_ID}}/">立即开团</a></div>
                    {{/if}}
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
        var url = "/api/<?=$UsersID ?>/pintuan/biz/<?=$BizID ?>/act_<?=$ActiveID ?>/";
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