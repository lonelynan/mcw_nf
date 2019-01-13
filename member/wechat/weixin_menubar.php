<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/wechat/token_set.php">微信接口配置</a></li>
        <li id="sett2"> <a href="/member/wechat/attention_reply.php">首次关注设置</a></li>
        <li id="sett3"> <a href="/member/wechat/menu.php">自定义菜单设置</a></li>
        <li id="sett4"> <a href="/member/wechat/keyword_reply.php">关键词回复设置</a></li>
        <li id="sett5"> <a href="/member/material/index.php">图文消息管理</a>
          <dl>            
            <dd id="sub51"><a href="/member/material/url.php">自定义URL</a></dd>
			<dd id="sub52"><a href="/member/material/sysurl.php">系统URL查询</a></dd>			
          </dl>
        </li>
        <li id="sett6"><a href="/member/shop/articles.php">文章管理</a>        
          <dl>
            <dd id="sub61"><a href="/member/shop/articles_category.php">分类管理</a></dd>
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