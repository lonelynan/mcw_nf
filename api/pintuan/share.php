<?php
require_once(CMS_ROOT . '/include/library/weixin_jssdk.class.php');
$jssdk = new weixin_jssdk($DB,$_GET["UsersID"]);
$signPackage = $jssdk->jssdk_get_signature();
if(!empty($signPackage)){
?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
wx.config({
    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
    appId: '<?=$signPackage['appId'] ?>', // 必填，公众号的唯一标识
    timestamp:'<?=$signPackage['timestamp'] ?>' , // 必填，生成签名的时间戳
    nonceStr: '<?=$signPackage['noncestr'] ?>', // 必填，生成签名的随机串
    signature: '<?=$signPackage['signature'] ?>',// 必填，签名，见附录1
    jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
});
wx.ready(function() { 
    
    wx.onMenuShareTimeline({
        title: '<?=$title ?>', // 分享标题
        link: '<?=$url ?>', // 分享链接
        imgUrl: '<?=$img ?>', // 分享图标
        success: function () { 
            // 用户确认分享后执行的回调函数
        },
        cancel: function () { 
            
            // 用户取消分享后执行的回调函数
        }
    });
    wx.onMenuShareAppMessage({
        title: '<?=$title ?>', // 分享标题
        desc: '<?=$desc ?>', // 分享描述
        link: '<?=$url ?>', // 分享链接
        imgUrl: '<?=$img ?>', // 分享图标
        type: 'link', // 分享类型,music、video或link，不填默认为link
        dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
        success: function () { 
            // 用户确认分享后执行的回调函数
        },
        cancel: function () {
            // 用户取消分享后执行的回调函数
        }
    });
    wx.error(function(res){

    });
});
</script>
<?php } ?>