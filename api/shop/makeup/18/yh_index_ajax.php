<script>
	var ajaxover=true;
    function load_page(page){
		$.ajax({
			type:'post',
			url:'?',
			data:{p:page},
			beforeSend:function(){
				$(".get_more").addClass('loading');
			},
			success:function(data){
				if(data['list'] != ''){
					var j = 0;
					$.each(data['list'],function(i){
						j++;
						v = data['list'][i];
						$htmltmp = '<div class="items">'+
									'<ul>'+
									 '<li class="cell_l"><a href="javascript:;" data-url="'+v['link']+'"><img class="product-image" width="80%" src="'+v['ImgPath']+'"/></a></li>'+
									 '<li class="cell_r">'+
									  '<p class="products_title"><a href="javascript:;" data-url="'+v['link']+'">'+v['Products_Name']+'</a></p>'+
									  '<p class="products_price">&yen;'+v['Products_PriceX']+'</p>'+
									  '<p class="btns"><a href="'+v['link']+'">立即抢购</a></p>'+
									 '</li>'+
									 '<div class="clear"></div>'+
									'</ul>'+
								   '</div>';

						$(".index_products").append($htmltmp);
						loaded();
					})
					if(data['totalpage'] == $(".get_more").attr('page')){
						$(".get_more").hide();
						ajaxover=false;							
					}
				}else{
					$(".get_more").hide();
						ajaxover=false;						
				}
			},
			complete:function(){
				$(".get_more").removeClass('loading');
			},
			dataType:'json',
		});
	}
	//加载第一页
	load_page($('.pullUp').attr('page'));
	$(".pullUp").click(function(){
		var page = parseInt($(this).attr('page'))+1;
		$(this).attr('page', page);
	    load_page(page);
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
				load_page(page);

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