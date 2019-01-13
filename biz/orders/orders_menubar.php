<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="orders.php">订单列表</a></li>        
        <li id="sett2"> <a href="offline_orders.php">线下订单列表</a></li>
        <li id="sett3"> <a href="virtual_orders.php">消费认证</a></li>        
		<li id="sett4"><a href="backorders.php">退货列表</a></li>
		<li id="sett5"><a href="commit.php">评论管理</a></li>		
      </ul>
    </div>
	<script type="text/javascript">	
	var jcurid = <?=$jcurid?>;	
	$("#menuset li").each(function() {    
    $(this).removeClass();
            });
    $("#sett"+jcurid).addClass("cur");	
</script>