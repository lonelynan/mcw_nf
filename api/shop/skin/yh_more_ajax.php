<script language="javascript">
    function load_page(type, page){
		var post_url = '?UsersID='+UsersID+'&Def=<?php echo !empty($_GET['Def']) ? $_GET['Def'] : '';?>&order_by=<?php echo !empty($_GET['order_by']) ? $_GET['order_by'] : '';?>';
		//首先自动加载第一页
		$.ajax({
			type:'post',
			url:post_url,
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
						if(type == 1){
							$htmltmp = '<div class="item">'+
									   '<ul>'+
										'<li class="img"><a href="'+v['link']+'">'+((!v['JSON']["ImgPath"]) ? '暂无图片' : '<img data-url="'+v['JSON']["ImgPath"][0]+'" src="/static/js/plugin/lazyload/grey.gif"/>')+'</a></li>'+
										'<li class="name"><a href="'+v['link']+'">'+v["Products_Name"]+'</a></li>'+
										'<li class="name"><span>￥'+(v['Products_PriceA'] != 0 ? v['Products_PriceA'] : v['Products_PriceX'])+'</span></li>'+
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
