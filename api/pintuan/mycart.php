<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

use Illuminate\Database\Capsule\Manager as DB;

$pagesize = 3;
$page = I('page', 1);
$total = DB::table("Shop_Products as p")->leftJoin("user_favourite_products as f", "p.Products_ID","=","f.Products_ID")->whereRaw("p.Users_ID='{$UsersID}' and f.User_ID ='{$UserID}' and p.pintuan_flag=1")->count();
$totalPage = $total%$pagesize == 0?$total/$pagesize:intval($total/$pagesize)+1;
$offset = ($page-1)*$pagesize;
$list = DB::table("Shop_Products as p")->leftJoin("user_favourite_products as f", "p.Products_ID","=","f.Products_ID")->whereRaw("p.Users_ID='{$UsersID}' and f.User_ID ='{$UserID}' and p.pintuan_flag=1")->orderBy("p.Products_ID","desc")->offset($offset)->limit
($pagesize)->get(["p.Products_Name", "p.Users_ID","p.Products_JSON","p.Products_ID", "p.pintuan_pricex","p.pintuan_people","p.pintuan_start_time","p.pintuan_end_time","p.Products_Count","f.FAVOURITE_ID"]);
if(!empty($list)){
    foreach($list as $k => $v) {
        $img = json_decode(htmlspecialchars_decode($v["Products_JSON"]), true)['ImgPath']['0'];
        $list[$k]['image'] = !empty($img) ? $img : '/static/images/no_pic.png';
        $list[$k]['type'] = $v['Products_Count'] && ($v['pintuan_start_time']<=time() && time() <= $v['pintuan_end_time']) ? 1 : 0;
    }
}
if(IS_AJAX){
    $status = 0;
    if(!empty($list)) {
        $status = 1;
    }
    die(json_encode([ 'status'=>$status, 'data' => $list, 'page' =>[
        'pagesize' => $total,
        'hasNextPage' => $total >= $pagesize ? true : false,
        'total' => $total,
    ]], JSON_UNESCAPED_UNICODE));
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
	<title>我的收藏</title>
	</head>
	<link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
	<script src="/static/api/pintuan/js/jquery.min.js"></script>
	<script type="text/javascript" src="/static/api/pintuan/js/scrolltopcontrol.js"></script>
	<script src="/static/api/pintuan/js/common.js"></script>
	<script src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script>
	<script type="text/javascript" src="/static/js/template.js"></script>
	<script type='text/javascript' src='/static/js/jquery.lazyload.min.js'></script>
	<script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script>
	<style>
	.chanpin { border-bottom: 10px #f8f8f8 solid;}
	.chanpin3 img {max-width:120px;}
	.chanpin1 {height:110px;}
	.r {float:right;margin-right:15px;}
	.l {margin-left:10px;}
	.cp {color:#888;}
	.price { margin-left: 140px; margin-top: -60px; font-size:14px;}
	</style>
<body>
    <div class="dingdan">
        <span class="fanhui l"><a href="<?php echo "/api/$UsersID/pintuan/user/"; ?>"><img src="/static/api/pintuan/images/fanhui.png" width="17px" height="17px"></a></span>
        <span class="querendd l">我的收藏</span>
        <div class="clear"></div>
    </div>
    <div id="container"></div>
    <input type="hidden" name="currentPage" value="2" />
    <?php require_once ( CMS_ROOT . '/api/pintuan/bottom.php'); ?>
    <script id="loadContainer" type="text/html">
        {{each data as item i}}
        <div class="chanpin">
            <div class="chanpin1">
                <span class="chanpin3 l"><a href="/api/{{item.Users_ID}}/pintuan/xiangqing/{{item.Products_ID}}/"><img src="{{item.image}}" /></a></span>
                <span class="cp l"><a href="/api/{{item.Users_ID}}/pintuan/xiangqing/{{item.Products_ID}}/">{{item.Products_Name}}</a></span>
                <span class="sc r" cid="{{item.FAVOURITE_ID}}">
                    <img class="image" src="/static/api/shop/skin/default/images/del.gif" width="22" >
                </span>
				<div class="clear"></div>
				<span class="cp l price">
					价格：{{item.pintuan_pricex}} <br/>人数：{{item.pintuan_people}}人
				</span>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
        {{/each}}
    </script>
    <script>
        var url = "<?=$_SERVER['REQUEST_URI']?>";
        var totalPage = <?=$totalPage?>;
        var page = 1;

        $(function(){
            getOrderList(url,page, function(){
                $('.sc').click(function(){
                    var cid = $(this).attr("cid");
                    var UsersID = "<?=$UsersID ?>";
                    $(this).parents('.chanpin').remove();
                    $.post('/api/' + UsersID + '/shop/member/ajax/',{cid:cid,action:"collectDel"},function(data){
                        if(data.status == 1){
                            layer.msg("成功取消收藏",{icon:1,time:700});
                        }else{
                            layer.msg(data.msg,{icon:2,time:700});
                        }
                    }, 'json');
                });
            });
            //瀑布流加载翻页
            var last_pageno = 1;
            $(window).bind('scroll',function () {
                // 当滚动到最底部以上100像素时， 加载新内容
                if ($(document).height() - $(this).scrollTop() - $(this).height() < 100) {
                    //已无数据可加载
                    var currentPage = $("input[name='currentPage']").val();
                    if (currentPage == last_pageno) {
                        return false;
                    } else {
                        last_pageno = currentPage;
                    }
                    if (currentPage > totalPage) {
                        return true;
                    }
                    getOrderList(url,currentPage);
                }
            });
        });
    </script>
</body>
</html>
