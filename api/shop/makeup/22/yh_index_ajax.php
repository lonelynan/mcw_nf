<script>
	var ajaxover=true;
    function load_page(cate_id, page){
		//首先自动加载第一页
		$.ajax({
			type:'post',
			url:'?',
			data:{p:page,cate_id:cate_id},
			beforeSend:function(){
				$(".get_more"+cate_id).addClass('loading');
			},
			success:function(data){
				if(data['list'] != ''){
					var j = 0;
					$.each(data['list'],function(i){
						j++;
						v = data['list'][i];
						$htmltmp = '<li class="'+(j%2==1 ? '' : '')+'"'+(j>2 ? ' style="border-top:none"' : '')+'>'+
									'<div><a href="javascript:;" data-url="'+v['link']+'"><img class="product-image" width="80%" height="120" data-url="'+v['ImgPath']+'" src="/static/js/plugin/lazyload/grey.gif"/></a></div>'+
									'<p class="products_title">'+v['Products_Name']+'</p>'+
									'<p class="products_price"><span>已售'+v['Products_Sales']+'笔&nbsp;</span>&nbsp;&yen;'+v['Products_PriceX']+'</p>'+
									'</li>'+
									(j%2==0 ? '<div class=""></div>' : '');
									
						$("#more"+cate_id).append($htmltmp);
						loaded();
					})
					if(data['totalpage'] == $(".get_more"+cate_id).attr('page')){
						$(".get_more"+cate_id).hide();
						ajaxover=false;							
					}
				}else{
					$(".get_more"+cate_id).hide();
					ajaxover=false;						
				}
			},
			complete:function(){
				$(".get_more"+cate_id).removeClass('loading');
			},
			dataType:'json',
		});
	}
	//加载第一页
	$(".pullUp").each(function(){
	    load_page($(this).attr('cate_id'), $(this).attr('page'));
	});
	$(".pullUp").click(function(){
		var page = parseInt($(this).attr('page'))+1;
		$(this).attr('page', page);
	    load_page($(this).attr('cate_id'), page);
	});

//瀑布流
	$(window).bind('scroll',function () {

		// 当滚动到最底部以上100像素时， 加载新内容
		if ($(document).height() - $(this).scrollTop() - $(this).height() < 100) {

			//已无数据可加载
			if ($(".get_more").is(":hidden")) {
				return false;
			}

			if (ajaxover) { //当ajaxover为真的时候，才执行loadMore()函数
					
				var page = parseInt($(".pullUp").attr('page'))+1;
				$(".pullUp").attr('page', page);
				load_page($(".pullUp").attr('cate_id'), page);

			} else {
				return false;
			}
		}
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


<script type="text/javascript">
$(function(){
	$(".index_products").on("click", "ul li a", function(){
		global_obj.productDetail_mask();

		var data_url = $(this).attr('data-url');
		$.get(data_url, {}, function(data)  {
			$("#product_Detail_Box").html(data);

			global_obj.share_init_config;
		})
	})
})
</script>

<!-- 产品详情页使用的资源文件清单 -->
<script type='text/javascript' src='/static/js/iscroll.js'></script> 
<script type='text/javascript' src='/static/api/shop/js/product_attr_helper.js?t=<?=time();?>'></script>
<link href="/static/css/bootstrap.css" rel="stylesheet" />
<link rel="stylesheet" href="/static/css/font-awesome.css" />
<link href="/static/api/shop/skin/default/css/tao_detail.css?t=<?php echo time();?>" rel="stylesheet" type="text/css" />


<script src="/static/js/jquery.idTabs.min.js"></script> 
<!-- 产品图片所需插件 begin -->
<link href="/static/js/plugin/photoswipe/photoswipe.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/static/js/plugin/touchslider/touchslider.min.js"></script> 
<script type='text/javascript' src='/static/js/plugin/photoswipe/klass.min.js'></script> 
<script type='text/javascript' src='/static/js/plugin/photoswipe/photoswipe.jquery-3.0.5.min.js'></script> 
<script type='text/javascript' src='/static/api/shop/js/toast.js?t=<?=time();?>'></script> 
<!-- 产品图片所需插件 end --> 
<!--// 产品详情页使用的资源文件清单 -->