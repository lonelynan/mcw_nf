<div class="r_nav">
	<ul id="menuset">
        <li id="sett1"><a href="list.php?id=<?=$id?>">创建商户</a>
		</li>
        <li id="sett2"> <a href="stores.php?id=<?=$id?>">门店管理</a>
		<dl>
		    <dd id="sub21"><a href="add_stores.php?id=<?=$id?>">添加门店</a></dd>
		</dl>
		</li>
		</li>       
	</ul>
</div>
	<script type="text/javascript">	
	var jcurid = <?=$jcurid?>;
	var subid = "<?php echo isset($subid) ? $subid : 100; ?>";
	var subidst = "<?php echo isset($subidst) ? $subidst : 100; ?>";
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
		var xurl = surl + "&cliid=" + cliid+"";		
        $(this).attr("href",xurl);
    });
	if(subidst != 100){
		var changefour = '';
		switch(subidst){
		   case 11:changefour = '添加产品';break;
		   case 21:changefour = '添加门店';break;
	       default:changefour = '商户管理';break;
	   }				
		$("#sett"+jcurid).children("a").html(changefour);
	}
</script>