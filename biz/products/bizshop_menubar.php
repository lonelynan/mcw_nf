<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/biz/products/products.php">产品管理</a>
		<dl>            
		<dd id="sub11"><a href="/biz/products/products_add.php">添加产品</a></dd>
		</dl>
		</li>
        <li id="sett2"> <a href="/biz/products/product_type.php">产品类型管理</a>
		<dl>            
		<dd id="sub21"><a href="/biz/products/product_type_add.php">添加类型</a></dd>
		</dl>
		</li>
        <li id="sett3"> <a href="/biz/products/shop_attr.php">产品属性管理</a>
		<dl>            
		<dd id="sub31"><a href="/biz/products/shop_attr_add.php">添加属性</a></dd>
		</dl>
		</li>
        <li id="sett4"> <a href="/biz/products/virtual_card.php">虚拟卡密管理</a>
		<dl>            
		<dd id="sub41"><a href="/biz/products/virtual_card_type.php">卡密类型管理</a></dd>
		<dd id="sub42"><a href="/biz/products/virtual_card_type_add.php">添加卡密类型</a></dd>
		<dd id="sub43"><a href="/biz/products/virtual_card_add.php">添加卡密</a></dd>
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
	$("#menuset dd a").click(function(e) {
		var cliid = $(this).parent().attr("id");
		var surl = $(this).attr("href");
		var xurl = surl + "?cliid='" + cliid+"'";		
        $(this).attr("href",xurl);
    });
	if(subidst != 100){
		var changefour = '';
		switch(subidst){
		   case 11:changefour = '编辑产品';break;
		   case 21:changefour = '类型编辑';break;
		   case 31:changefour = '属性编辑';break;
		   case 41:changefour = '编辑卡密';break;
		   case 42:changefour = '编辑卡密类型';break;
	       default:changefour = '产品管理';break;
	   }				
		$("#sett"+jcurid).children("a").html(changefour);
	}
</script>