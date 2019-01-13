<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/web/config.php">基本设置</a></li>
        <li id="sett2"> <a href="/member/web/skin.php">风格设置</a></li>
        <li id="sett3"> <a href="/member/web/home.php">首页设置</a></li>
        <li id="sett4"> <a href="/member/web/column.php">栏目管理</a></li>
        <li id="sett5"> <a href="/member/web/article.php">内容管理</a></li>
        <li id="sett6"><a href="/member/web/lbs.php">一键导航</a></li>
      </ul>
    </div>
	<script type="text/javascript">	
	var jcurid = <?=$jcurid?>;
	var subid = <?php echo isset($subid) ? $subid : 100; ?>;
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
		var xurl = surl + "?cliid='" + cliid+"'";		
        $(this).attr("href",xurl);
    });
</script>