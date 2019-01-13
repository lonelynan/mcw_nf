<script>
    var ajaxover = false;
    var pullUp = $(".pullUp");

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
				    var htmltmp = '';
					$.each(data['list'],function(i){
						v = data['list'][i];
						htmltmp += '<div class="shop_pro"><ul><li>';
                        htmltmp += '<a href="' + v['link'] + '"><p class="shop_img"><img data-url="' + (v['Rect_Img'] ? v['Rect_Img'] : v['Products_JSON']['ImgPath'][0]) + '" src="/static/js/plugin/lazyload/grey.gif"></p></a>';
                        htmltmp += '<p class="shop_title">' + v['Products_Name'] + '</p>';
                        htmltmp += '<div class="shop_main">';
                        if (v['flashsale_flag'] == 1) {
                            htmltmp += '<span class="price_pro_list"><font>￥</font>'+ v["flashsale_pricex"] + '<span class="price_y_pro">￥' + v['Products_PriceY'] + '</span></span>';
                            htmltmp += '<span class="right_shop"><p class="shop_tuan">已售' + v['Products_Sales'] + '件</p><a href="' + v['link'] + '" class="shop_kai">去抢购</a></span>';

                        } else {
                            if (v['pt_flag']) { //是拼团产品
                                htmltmp += '<span class="price_pro_list"><font>￥</font>'+ v["pintuan_pricex"] + '<span class="price_y_pro">￥' + v['Products_PriceX'] + '</span></span>';
                                htmltmp += '<span class="right_shop"><p class="shop_tuan">已团' + v['Products_Sales'] + '件</p><a href="' + v['link'] + '" class="shop_kai">去开团</a></span>';
                            } else {
                                htmltmp += '<span class="price_pro_list"><font>￥</font>'+ v["Products_PriceX"] + '<span class="price_y_pro">￥' + v['Products_PriceY'] + '</span></span>';
                                htmltmp += '<span class="right_shop"><p class="shop_tuan">已售' + v['Products_Sales'] + '件</p><a href="' + v['link'] + '" class="shop_kai">去购买</a></span>';
                            }
                        }
                        htmltmp += '</div>';
                        htmltmp += '</li></ul></div>';
					});

					$("#more"+cate_id).append(htmltmp);
                    loaded();

					if(data['totalpage'] <= $(".get_more"+cate_id).attr('page')){
						$(".get_more"+cate_id).hide();
                        ajaxover = false;
					} else {
                        ajaxover = true;
                    }
				}else{
					$(".get_more"+cate_id).hide();
                    ajaxover = false;
				}
			},
			complete:function(){
				$(".get_more"+cate_id).removeClass('loading');
			},
			dataType:'json'
		});
	}
	// 加载第一页。
    pullUp.each(function(){
        load_page($(this).attr('cate_id'), $(this).attr('page'));
    });

    pullUp.click(function(){
		var page = parseInt($(this).attr('page'))+1;
		$(this).attr('page', page);
	    load_page($(this).attr('cate_id'), page);
	});

    //瀑布流
    $(window).bind('scroll', function () {
        // 当滚动到最底部以上100像素时， 加载新内容
        if ($(document).height() - $(this).scrollTop() - $(this).height() < 100) {
            if (ajaxover) { //当ajaxover为真的时候，说明还有未加载的产品
                pullUp.trigger('click');
                ajaxover = false;   //防止连续请求
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
