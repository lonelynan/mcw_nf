<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/biz/index.php">普通商家列表</a>
		<dl>
            <dd id="sub11"><a href="/member/biz/union.php">联盟商家列表</a></dd>
          </dl>
		</li>
        <li id="sett2"><a href="/member/biz/group.php">商家分组</a></li>
        <li id="sett3"><a href="/member/biz/authpay.php">入驻支付列表</a></li>
        <li id="sett4"><a href="/member/biz/chargepay.php">续费支付列表</a></li>
        <li id="sett5"><a href="/member/biz/apply.php">资质审核列表</a></li>
        <li id="sett6"><a href="/member/biz/apply_config.php">入驻描述设置</a></li>		
        <li id="sett7"><a href="/member/biz/reg_config.php">注册页面设置</a></li>		
        <li id="sett8"><a href="/member/biz/apply_other.php">年费设置</a></li>
        <li id="sett9"><a href="/member/biz/money_set.php">批量设置</a></li>
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