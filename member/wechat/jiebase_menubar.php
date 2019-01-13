<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/wechat/bt_domain.php">域名绑定设置</a></li>
        <li id="sett2"> <a href="/member/wechat/setting.php">系统设置</a>
          <dl>
            <dd id="sub21"><a href="/member/wechat/sms.php">短信配置</a></dd>
          </dl>
        </li>
        <li id="sett3"> <a href="/member/kf/config.php">客服设置</a>
			<dl>
            <dd id="sub31"><a href="/member/kf/weixin_config.php">微信客服设置</a></dd>
			</dl>
		</li>
        <li id="sett3"> <a href="/member/wechat/shopping.php">支付</a></li>
		<li id="sett6"><a href="/member/update/index.php">系统更新</a></li>
        
		<li id="sett8"><a href="/member/wechat/shipping.php">快递公司管理</a>
			<dl>
            <dd id="sub81"><a href="/member/wechat/settingdianzi_print.php">快递鸟账号管理</a></dd>
			</dl>
		</li>
      </ul>
    </div>
	<script type="text/javascript">	
	var jcurid = '<?=$jcurid?>';
	var subid = '<?php echo isset($subid) ? $subid : 100; ?>';
	$("#menuset li").each(function() {    
    $(this).removeClass();
            });
    $("#sett"+jcurid).addClass("cur");

	if(subid != 100){
		var changeone = $("#"+subid).children().html();
		var changetwo = $("#"+subid).parent().parent().children("a").html();
		var changethree = $("#"+subid).parent().parent().children("a").attr("href");
		$("#"+subid).parent().parent().children("a").html(changeone);
		$("#"+subid).children("a").html(changetwo);
		$("#"+subid).children("a").attr("href",changethree);
	}
	$("#menuset dd a").click(function(e) {
		var cliid = $(this).parent().attr("id");
		var surl = $(this).attr("href");
		var xurl = surl + "?cliid=" + cliid;		
        $(this).attr("href",xurl);
    });
</script>