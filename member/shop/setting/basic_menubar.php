?><div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/shop/setting/config.php">基本设置</a></li>
        <li id="sett2"> <a href="/member/shop/setting/other_config.php">活动设置</a></li>
        <li id="sett3"> <a href="/member/shop/setting/skin.php">风格设置</a></li>
        <li id="sett4"> <a href="/member/shop/setting/home.php">首页设置</a></li>
        <li id="sett5"> <a href="/member/shop/setting/menu_config.php">菜单配置</a></li>
        <li id="sett6"><a href="/member/shop/setting/third_login.php">三方登录</a></li>
		<li id="sett7"><a href="/member/shop/setting/switch.php">开关设置</a>
			<dl>
				<dd id="sub71"><a href="switch_distribute.php">分销开关设置</a></dd>
				<dd id="sub72"><a href="switch_user.php">个人开关设置</a></dd>
			</dl>
		</li>
          <li id="sett8"><a href="/member/shop/setting/skin_program.php">小程序风格设置</a></li>
          <li id="sett9"><a href="/member/shop/setting/home_program.php">小程序首页设置</a></li>
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