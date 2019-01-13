<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/shop/commision_setting.php">佣金设置</a>
		<dl>
        <dd id="sub11"><a href="/member/shop/oofcommision_setting.php">线下佣金设置</a></dd>
        </dl>
		</li>        
        <li id="sett3"> <a href="/member/shop/products.php">产品列表</a></li>
        <li id="sett4"> <a href="/member/shop/category.php">产品分类</a></li>        
		<li id="sett7"><a href="/member/shop/commit.php">产品评论管理</a></li>
		<li id="sett8"><a href="/member/shop/orders.php">订单管理</a>
		<dl>
        <dd id="sub81"><a href="/member/shop/offline_orders.php">线下订单管理</a></dd>
        <dd id="sub82"><a href="/member/shop/backups.php">退货单管理</a></dd>
        </dl>
		</li>		
      </ul>
    </div>
	<script type="text/javascript">	
	var jcurid = <?=$jcurid?>;
	var subid = <?php echo isset($subid) ? $subid : 100; ?>;
	var subidst = <?php echo isset($subidst) ? $subidst : 100; ?>;
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
	if(subidst != 100){
		var changefour = '';
		switch(subidst){
	       case 41:changefour = '添加分类';break;
	       case 42:changefour = '修改分类';break;	      
	       default:changefour = 'error';break;
	   }				
		$("#sett"+jcurid).children("a").html(changefour);		
	}
	$("#menuset dd a").click(function(e) {
		var cliid = $(this).parent().attr("id");
		var surl = $(this).attr("href");
		var xurl = surl + "?cliid='" + cliid+"'";		
        $(this).attr("href",xurl);
    });
</script>