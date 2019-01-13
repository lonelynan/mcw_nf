<?php
require_once('global.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/library/Gather.class.php');

if (!isset($_GET['content_id'])) {
    exit('缺少参数');
} else {
    $content_id = (int)$_GET['content_id'];
}

$contentInfo = $DB->GetRs('pop_wx_content', '*', "where id = " . $content_id);
if (!$contentInfo) {
    exit('找不到此文章!');
}
//获取登录用户分销账号
$rsAccount =  Dis_Account::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$contentInfo['userid']))
    ->first()->toArray();
$pic = !empty($rsAccount['Shop_Logo']) ? $rsAccount['Shop_Logo'] : '/static/api/images/user/face.jpg';

$gatherObj = new Gather($contentInfo['wx_url'], '../../uploadfiles/' . $UsersID . '/wx_download');
$output = $gatherObj->fetch();

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=$output['title'];?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no" />
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
</head>
<style>
    *{margin:0;padding:0;border:0;outline:0;box-sizing: border-box;background-repeat: no-repeat;background-position: center center;}
    li, dt, dd, ol, dl{list-style-type:none;}
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

    .gd_r{position: fixed;right: 0; top:200px;z-index: 2; width: 30px; background: #ff6600; border-top-left-radius: 2px; border-bottom-left-radius: 2px;line-height: 15px; text-align: center;color: #fff; padding: 10px 0;}
    .gd_l{position: fixed;left: 0; top:400px;z-index: 2; width: 50px; height: 90px; background: #ff6600; border-top-right-radius: 2px; border-bottom-right-radius: 2px; text-align: center;color: #fff;}
    .gd_l img{display: block;margin:5px auto;width: 45px; height: 45px; border-radius: 3px;}
    .gd_l a{color: #fff; }
    .gd_l p{line-height: 15px;}
    .lianjie_tg {width:70%;background: #fff; border-radius: 2px; padding: 20px; margin: 0 auto;position: fixed;z-index: 5;top:200px; left: 15%;display: none;}
    .lianjie_tg p img{display: block;margin: 0 auto;position: absolute;top:-20px;width: 35px; height: 35px;right: -15px; }
    .lianjie_tg ul{border: 1px solid #ff6600;}
    .lianjie_tg ul li{width: 100%; border-bottom: 1px #f4f4f4 solid; line-height: 20px; color: #666; padding: 10px;}
    .lianjie_tg  ul li img{display: block; width: 20px; height: 20px; margin-right: 10px;}
</style>
<body>
<div class="w">

    <?=$output['content'];?>
    <div class="gd_r">
        联<br>系<br>方<br>式
    </div>
    <div class="gd_l">
        <a href="<?= $contentInfo['link_url'] . (strpos($contentInfo['link_url'], '?') ? '&' : '?') . 'OwnerID=' . $contentInfo['userid'] ?>">
            <img src="<?=$pic?>">
            <p>查看<br>详情</p>
        </a>
    </div>
    <div class="lianjie_tg">
        <ul>
            <a href="javascript:void(0);"><li style="text-align: center; border-bottom: 1px solid #ff6600">联系方式</li></a>
            <a href="javascript:void(0);"><li><span class="left"><img src="/static/distribute/images/21.png"></span><?=$contentInfo['name']?></li></a>
            <a href="tel:<?=$contentInfo['mobile']?>"><li><span class="left"><img src="/static/distribute/images/pho.png"></span><?=$contentInfo['mobile']?></li></a>
            <?
                if (!empty($contentInfo['qq'])) {
            ?>
            <a href="javascript:void(0);"><li><span class="left"><img src="/static/distribute/images/qq.png"></span><?=$contentInfo['qq']?></li></a>
            <? } if (!empty($contentInfo['email'])) { ?>
            <a href="javascript:void(0);"><li><span class="left"><img src="/static/distribute/images/em.png"></span><?=$contentInfo['email']?></li></a>
            <? } ?>
        </ul>
        <p><img src="/static/distribute/images/gb.png"></p>
    </div>
</div>
<script>
    $(".gd_r").click(function(){
        $(".lianjie_tg").css("display","block");
    });
    $(".lianjie_tg p").click(function(){
        $(".lianjie_tg").css("display","none");
    });
</script>
</body>
</html>