<?
require_once('global.php');
$condition = "where usersid = '". $UsersID ."' AND userid = " . $User_ID;
if (isset($_GET['keyword'])) {
    $condition .= " AND wx_title like '%". htmlspecialchars($_GET['keyword']) ."%'";
}
$condition .= ' ORDER BY id DESC';
$gather_list = $DB->GetAssoc('pop_wx_content', '*', $condition);
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>推广列表</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no" />
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/layer_mobile/layer.js'></script>
</head>
<style>
    *{margin:0;padding:0;border:0;outline:0;box-sizing: border-box;background-repeat: no-repeat;background-position: center center;}
    ul, li, dt, dd, ol, dl{list-style-type:none;}
    a{color:#666; text-decoration:none;}
    a:hover{color: #006fbb;text-decoration:none;}
    input, textarea, button, a{ -webkit-tap-highlight-color:rgba(0,0,0,0); }
    .clear{clear:both;}
    img{width: 100%;border:none;display: block;}
    em{font-style: normal;}
    .left{float:left;}
    .right{float:right;}
    html{overflow-y: scroll;}
    body{background-color:#f8f8f8;-webkit-tap-highlight-color:transparent;font-family:"Microsoft YaHei"; font-size: 14px;}
    .w{width:100%; margin:0 auto;overflow: hidden}
    input[type=button], input[type=submit], input[type=file], button { cursor: pointer; -webkit-appearance: none;}

    .sousuo_fl{position: fixed;width:100%;left: 0;background-color:rgba(254,66,0,1.00);z-index: 5;top:0; padding: 8px 0;}
    .sousuo_fl input{width: 100%;color: #666; line-height: 35px; height: 35px; border-radius: 35px;background-color:rgba(255,255,255,0.8); text-align: center;  padding: 0 40px;}
    .sousuo_fl span.left_b{width: 15%; float: left; padding-top: 2px;}
    .sousuo_fl span.left_b img{width: 30px; height: 30px; margin: 0 auto;}
    .sousuo_fl span.left_s{width: 70%; float: left;position: relative}
    .sousuo_fl span.left_s img{width: 20px; height: 20px;position: absolute;right: 10px;top:7px}

    .wenzhang_x{width: 100%;margin-top: 51px; overflow: hidden; }
    .wenzhang_x ul{}
    .wenzhang_x ul li{margin-bottom: 5px; background: #fff; overflow: hidden; }
    .wenzhang_x ul li h3{width: 100%;color: #333;font-size: 15px; line-height: 50px; text-align: center;border-bottom: 1px #f4f4f4 solid;text-overflow:ellipsis; white-space:nowrap; overflow:hidden;padding: 0 10px;}
    .wenzhang_x div{}
    .wenzhang_x div ul{}
    .wenzhang_x div ul li{ float: left; width: 50%; text-align: center;line-height: 40px;position: relative; margin: 0; color: #999;}
    .wenzhang_x div ul a li{color: #fe4200;}
    .wenzhang_x div ul li:before { border-left: 1px solid #f4f4f4;  bottom: 0;  content: ""; position: absolute;left: 0; top: 0;}


</style>
<script type="text/javascript">
    $(function() {
        $("#search").on('click', function() {
            var keyword = $("#search_keyword").val();
            if (keyword.length == 0) {
                layer.open({
                    content:'请输入关键字!'
                })
            } else {
                location.href = "?keyword=" + keyword;
            }
        })
    })
</script>
<body>
<div class="w">
    <div class="sousuo_fl">
		<span class="left_b">
			<a href="javascript:history.back();"><img src="/static/distribute/images/fanhui.png"></a>
		</span>
		<span class="left_s">
			<input type="text"  placeholder="请输入搜索内容" value="<?=isset($_GET['keyword']) ? $_GET['keyword'] : ''?>" id="search_keyword">
			<img src="/static/distribute/images/ss.png" id="search">
		</span>
    </div>
    <div class="clear"></div>
    <div class="wenzhang_x">
        <ul>
            <?
                if (!empty($gather_list)) {
                    foreach ($gather_list as $k => $v) {
            ?>
            <li>
                <h3 class="title_tg"><?=$v['wx_title']?></h3>
                <div>
                    <ul>
                        <a href="/api/shop/gather_content.php?UsersID=<?= $UsersID ?>&content_id=<?=$v['id']?>&OwnerID=<?=$User_ID?>"><li>文章详情</li></a>
                        <li><?=$v['click']?>人看过</li>
                    </ul>
                    <div class="clear"></div>
                </div>
            </li>
            <? }} else { ?>
            <p>暂无文章</p>

            <? } ?>
        </ul>
    </div>
    <div class="clear"></div>
</div>
</body>
</html>
