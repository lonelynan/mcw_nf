<script>
    function load_page(cate_id, page,key){
		//首先自动加载第一页
		$.ajax({
			type:'post',
			url:'?',
			data:{p:page,cate_id:cate_id},
			beforeSend:function(){
				$(".get_more"+key).addClass('loading');
			},
			success:function(data){
				if(data['list'] != ''){
					var j = 0;
					$.each(data['list'],function(i){
						j++;
						v = data['list'][i];
						$htmltmp = '<li class="'+(j%2==1 ? 'border_r' : '')+'"'+(j>2 ? ' style="border-top:none"' : '')+'>'+
									'<p><a href="'+v['link']+'"><img class="product-image" width="80%" data-url="'+v['ImgPath']+'" src="/static/js/plugin/lazyload/grey.gif"/></a><p>'+
									'<p class="products_title">'+v['Products_Name']+'</p>'+
									'<p class="products_price"><span>已售'+v['Products_Sales']+'笔&nbsp;</span>&nbsp;&yen;'+(v['Products_PriceA'] != 0 ? v['Products_PriceA'] : v['Products_PriceX'])+'</p>'+
									'</li>'+
									(j%2==0 ? '<div class="double_clear"></div>' : '');
									
						$("#more"+key).append($htmltmp);
						loaded();
					})
					if(data['totalpage'] == $(".get_more"+key).attr('page')){
						$(".get_more"+key).hide();
					}
				}else{
					$(".get_more"+key).hide();
				}
			},
			complete:function(){
				$(".get_more"+key).removeClass('loading');
			},
			dataType:'json',
		});
	}
	//加载第一页
	$(".pullUp").each(function(){
	    load_page($(this).attr('cate_id'), $(this).attr('page'),$(this).attr('key'));
	});
	$(".pullUp").click(function(){
		var page = parseInt($(this).attr('page'))+1;
		$(this).attr('page', page);
	    load_page($(this).attr('cate_id'), page,$(this).attr('key'));
	});
</script>
<!--懒加载--> 
<script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script> 
<script language="javascript">
function loaded(){
	$("img").scrollLoading();
}
$(document).ready(function(){
	document.addEventListener('touchmove', function (e) { $("img").scrollLoading(); }, false);
	document.addEventListener('DOMContentLoaded', loaded, false); 
})
</script> 
