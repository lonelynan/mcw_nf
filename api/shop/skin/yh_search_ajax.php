<script language="javascript">
    function load_page(type, page){
		var post_url = '?UsersID='+UsersID+'&kw='+search_kw+'&IsHot='+search_hot+'&IsNew='+search_new+'&order_by='+search_order+'&Is_Union='+Is_Union;
		//首先自动加载第一页
		$.ajax({
			type:'post',
			url: post_url,
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
                        if (v['flashsale_flag'] == 1) {
                            var priceshow = v['flashsale_pricex'];
                        } else {
                            if (v['skujosn'] != '' && typeof(v['skujosn']) != 'object') {
                                var priceshow = v['Products_PriceA'];
                            }else{
                                var priceshow = v['Products_PriceX'];
                            }
                        }
						if(type == 0){
						    $htmltmp = '<ul>'+
											'<li class="item">'+
										'<a href="javascript:;" data-url="'+v['link']+'">'+
												'<span class="img left">'+((!v['JSON']["ImgPath"]) ? '暂无图片' : '<img data-url="'+v['JSON']["ImgPath"][0]+'" src="/static/js/plugin/lazyload/grey.gif"/>')+'</span>'+
												'<span class="cpxq_x left">'+
													  '<div class="cpbt_x">'+v["Products_Name"]+'</div>'+
													  '<span class="jiaget_x l">￥'+priceshow+''+
													  '<b style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 2px; font-weight:normal;">9.5折</b>'+
													  '</span>'+
													  '<div class="clear"></div>'+
													  '<span class="left" style="font-size:13px;color:#999;line-height:26px;">已售'+v['Products_Sales']+'</span>'+
													  '<span class="r" style="margin-top:3px;">'+
													  '<img src="/static/api/shop/skin/default/gwct_t.png" width="18" height="18">'+
													  '</span>'+
													  //'<h3>'+v["Products_BriefDescription"]+'</h3>'+
													//'<div class="detail"><span></span></div>'+
												'</span>'+
											'</a>'+
											'</li>'+
										'</ul>';
					    }else if(type == 1){
							$htmltmp = '<div class="item">'+
									   '<ul>'+
										'<li class="img"><a href="'+v['link']+'">'+((!v['JSON']["ImgPath"]) ? '暂无图片' : '<img data-url="'+v['JSON']["ImgPath"][0]+'" src="/static/js/plugin/lazyload/grey.gif"/>')+'</a></li>'+
										'<li class="name"><a href="'+v['link']+'">'+v["Products_Name"]+'</a><span>￥'+priceshow+'</span></li>'+
									   '</ul>'+
									  '</div>'+
									  (j%2==0 ? '<div class="clear"></div>' : '');
						}		
						$(".list-"+type).append($htmltmp);
						loaded();
					})
					if(data['totalpage'] == $(".get_more").attr('page')){
						$(".get_more").hide();
					}
				}else{
					$(".get_more").hide();
				}
			},
			complete:function(){
				$(".get_more").removeClass('loading');
			},
			dataType:'json',
		});
	}
	//加载第一页
	load_page($('.pullUp').attr('listtype'), $('.pullUp').attr('page'));
	$(".pullUp").click(function(){
		var page = parseInt($(this).attr('page'))+1;
		$(this).attr('page', page);
	    load_page($(this).attr('listtype'), page);
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
