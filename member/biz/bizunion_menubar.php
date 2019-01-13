<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/biz/union.php">联盟商家列表</a>
		<dl>
            <dd id="sub12"><a href="/member/biz/index.php">普通商家列表</a></dd>
          </dl>
		</li>
        <li id="sett10"><a href="/member/biz/union_skin.php">风格设置</a></li>
        <li id="sett2"><a href="/member/biz/union_home.php">首页设置</a></li>
        <li id="sett3"><a href="/member/biz/category.php">行业分类</a></li>
<!--        <li id="sett4"><a href="/member/biz/region.php">区域设置</a></li>-->
        <li id="sett5"><a href="/member/biz/price.php">价格筛选</a></li>
        <li id="sett6"><a href="/member/biz/union_pro_skin.php">小程序风格设置</a></li>
        <li id="sett7"><a href="/member/biz/union_pro_home.php">小程序首页设置</a></li>
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