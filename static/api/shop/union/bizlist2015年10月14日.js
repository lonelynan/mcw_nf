var http_host_ary=window.location.host.split('.');
http_host_ary.shift();
var http_host=http_host_ary.join('.');
var domain={
	www:'http://www.'+http_host,
	static:'/static',
	img:'http://www.'+http_host,
	kf:'http://www.'+http_host,
};
var renderReverse = function(response){
	var id = $('#your_address').parent().attr('rel');
	var city=response.result.addressComponent.city;
	var citycode = response.result.cityCode;
	var my_address_html = response.result.formatted_address;
	$('#my_address').html(my_address_html).show();
	if($(".header_city").length>0){
		if(!$('.header_city').attr('rel')){
			$(".header_city").html(city);
		    $(".header_city").attr('rel',citycode);
		}
	}
	//开开水提示 搜索页获取商圈的标识
	if($(".search_body_title li[ret=region]").length>0){
		$(".search_body_title li[ret=region]").attr('citycode',citycode);
	}
};
var index_obj={
	index_init:function(){
		var formToJson = function (data) {
			var data = data.replace(/&/g,"\",\"");
			data = data.replace(/=/g,"\":\"");
			data = "{\""+data+"\"}";
			return data;
		};
		var make_para = function(type){
			if(type == 'get_category'){
				var filter_category_ret = '';
				var filter_category_html = '全部分类';
				$('#category_list ul li').each(function(){
					if($(this).is('.cur') && $(this).attr("ret")){
						filter_category_ret = $(this).attr("ret");
						filter_category_html = $(this).html();	
					}
				});
				$('input[name=categoryid]').val(filter_category_ret);
				$('.search_type_li[action='+type+'] a').html(filter_category_html);
			}else if(type == 'get_price'){
				var filter_price_ret = '';
				var filter_price_html = '价格不限';
				$('#price_list ul li').each(function(){
					if($(this).is('.cur') && $(this).attr("ret")){
						filter_price_ret = $(this).attr("ret");
						filter_price_html = $(this).html();
					}
				});
				$('input[name=price]').val(filter_price_ret);
				$('.search_type_li[action='+type+'] a').html(filter_price_html);
			}else if(type == 'get_orderby'){
				var filter_orderby_ret = '';
				var filter_orderby_html = '';
				$('#orderby_list ul li').each(function(){
					if($(this).is('.cur') && $(this).attr("ret")){
						filter_orderby_ret = $(this).attr("ret");
						filter_orderby_html = $(this).html();
					}
				});
				$('input[name=orderby]').val(filter_orderby_ret);
				$('.search_type_li[action='+type+'] a').html(filter_orderby_html);
			}else if(type == 'get_region'){
				var filter_region_ret = '';
				var filter_region_html = '默认';
				$('#region_list ul li').each(function(){
					if($(this).is('.cur') && $(this).attr("ret")){
						filter_region_ret = $(this).attr("ret");
						filter_region_html = $(this).html();
					}
				});
				$('input[name=regionid]').val(filter_region_ret);
				$('.search_type_li[action='+type+'] a').html(filter_region_html);
			}
			var vars = $("#search_form").serialize();
			var search_params = decodeURIComponent(vars,true);//防止中文乱码
			search_params = formToJson(search_params);//转化为json
			search_params = eval("("+search_params+")");
			return search_params;
		};
		var get_biz_list = function(filter){
			$('#loading').show();
			var geolocation=new BMap.Geolocation();
	        geolocation.getCurrentPosition(function(pos){
				var search_params = filter;
				    search_params.lat = pos.point.lat;
					search_params.lng = pos.point.lng;
				$.post(app_ajax_url, search_params, function(data) {
					if(data.status == 1){
						var html='';
						if(search_params.page==1){
							search_params.pagenum = data.pagenum;
							$('#list_pages #pre').addClass('past');
							$('#list_pages #pre').html("上一页");
						}else{
							if($('#list_pages #pre').is('.past')){
								$('#list_pages #pre').removeClass('past');
								$('#list_pages #pre').html("<span>上一页</span>");
							}
						}
						if(search_params.page == data.pagenum){
							$('#list_pages #next').addClass('past');
							$('#list_pages #next').html("下一页");
						}else{
							if($('#list_pages #next').is('.past')){
								$('#list_pages #next').removeClass('past');
								$('#list_pages #next').html("<span>下一页</span>");
							}
						}
						
						$.each(data.items,function(i,n){
							var meter = '';
							if(n.meter<10000){
								meter = '约'+(n.meter>>0)+'m';
							}else{
								meter = '约'+((n.meter/1000)>>0)+'km';
							}
							html+='<div class="items"><a href="'+app_url+'biz/'+n.Biz_ID+'/"><div class="img"><img src="'+n.Biz_Logo+'" /></div><ul><li class="title">'+n.Biz_Name+'&nbsp;<font color="#FF6600;">('+n.Category_Name+')</font></li><li class="category">'+n.Biz_Address+'</li><li><img src="/static/images/star_'+n['average_ico']+'.png"/>&nbsp;<span>'+n['average_num']+'分</span></li></ul><div class="buyinfo"><span class="price"><i>￥'+n.Biz_MinPrice+'</i>起</span><span class="route">'+meter+'</span></div></a></div>';
						});
						$('#bizlist').html(html);
					}else{
						$('#bizlist').html('<div class="nodata">暂无内容</div>');
					}
					$('#cover_layer_search').hide();
					$('#loading').hide();
					var url="http://api.map.baidu.com/geocoder/v2/?ak="+baidu_map_ak+"&callback=renderReverse&location="+pos.point.lat+","+pos.point.lng+"&output=json&pois=0";
					var script=document.createElement('script');
					script.type='text/javascript';
					script.src=url;
					document.body.appendChild(script);
				},'json');
			});
		};
		
		if(ispost==1){
			$('#cover_layer_search').height($(window).height());
			var top_load = ($(window).height()-50-$('#loading').height())/2;
			var left_load = ($(window).width()-$('#loading').width())/2;
			$('#loading').css({"top":top_load,"left":left_load});
			
			//默认查询第一页
			var search_params = make_para('');
			get_biz_list(search_params);
			
			$(document).on('click','#list_pages #pre span',function(){
				if(search_params.page == 1){
					return false;
				}else{
					$('#cover_layer_search').show();
					$('#loading').show();
					search_params.page = parseInt(search_params.page) -1;
					$('#list_pages .cur').html(search_params.page);
					$('input[name=page]').val(search_params.page);
					var filter = make_para('');//组装查询条件
					get_biz_list(filter);
				}
			});
			
			$(document).on('click','#list_pages #next span',function(){
				if(search_params.page >= search_params.pagenum){
					return false;
				}else{
					$('#cover_layer_search').show();
					$('#loading').show();
					search_params.page = parseInt(search_params.page) + 1;
					$('#list_pages .cur').html(search_params.page);
					$('input[name=page]').val(search_params.page);
					var filter = make_para('');//组装查询条件
					get_biz_list(filter);
				}
			});
		};
		
		$('#cover_layer_common').click(function(){
			$('#cover_layer_common').hide();
			$('#index_filter').slideUp();
		});
		
		$('.search_type_li').click(function(){
			var action = $(this).attr('action');
			var cyc = '';
			switch(action){
				case "get_category":
				    var elemID = 'category_list';
                break;
                case "get_price":
                    var	elemID = 'price_list';
                break;
                case "get_orderby":
                    var	elemID = 'orderby_list';
                break;
                case "get_region":
                    var	elemID = 'region_list';
					cyc = $('a',this).attr('rel');
                break;				
			}
			$.post(app_ajax_url, {action: action,citycode: cyc,categoryid: categoryID}, function(data) {
				var htmlTmp = '<div class="filter_items" id="'+elemID+'">'+
							  '<h2>'+data['title']+'</h2>'+
							  '<ul>';
				    if(action!='get_orderby'){
						htmlTmp	+= '<li ret="" style="border-left:1px #dfdfdf solid">不限</li>';
					}
					
				if(data.status == 1){
					var ii = 0;
					$.each(data['list'],function(i){
						ii++;
						var v = data['list'][i];
						htmlTmp += 	'<li ret="'+v['one']+'"'+(ii%4==0 ? ' style="border-left:1px #dfdfdf solid"' : '')+'>'+v['two']+'</li>';
					});
					htmlTmp += 	'</ul></div><div id="filter_submit" type="'+action+'">确定</div>';
				    var height = $('body').height() > $(window).height() ? $('body').height() : $(window).height();
					$('#cover_layer_common').height(height).show();
					$('#index_filter').html(htmlTmp).slideDown();
				}
			},'json');
		});
		
		$('#index_filter').on('click','#price_list ul li',function(){
			$('#price_list ul li').removeClass("cur");
			$(this).addClass("cur");
		});
		
		$('#index_filter').on('click','#category_list ul li',function(){
			$('#category_list ul li').removeClass("cur");
			$(this).addClass("cur");
		});
		$('#index_filter').on('click','#orderby_list ul li',function(){
			$('#orderby_list ul li').removeClass("cur");
			$(this).addClass("cur");
		});
		$('#index_filter').on('click','#region_list ul li',function(){
			$('#region_list ul li').removeClass("cur");
			$(this).addClass("cur");
		});
		$('#index_filter').on('click','#filter_submit',function(){
			var type = $(this).attr('type');
			var filter = make_para(type);//组装查询条件
			get_biz_list(filter);
			$('#cover_layer_common').hide();
			$('#index_filter').slideUp();
		});
	},
	
	search_init:function(){
		//遮罩层				
		var get_biz_list = function(){
			var geolocation=new BMap.Geolocation();
	        geolocation.getCurrentPosition(function(pos){
				search_params.lat = pos.point.lat;
				search_params.lng = pos.point.lng;
				$.post(search_ajax_url, search_params, function(data) {
					if(data.status == 1){
						var html='';
						if(search_params.page==1){
							search_params.pagenum = data.pagenum;
							$('#list_pages #pre').addClass('past');
							$('#list_pages #pre').html("上一页");
						}else{
							if($('#list_pages #pre').is('.past')){
								$('#list_pages #pre').removeClass('past');
								$('#list_pages #pre').html("<span>上一页</span>");
							}
						}
						
						if(search_params.page == data.pagenum){
							$('#list_pages #next').addClass('past');
							$('#list_pages #next').html("下一页");
						}else{
							if($('#list_pages #next').is('.past')){
								$('#list_pages #next').removeClass('past');
								$('#list_pages #next').html("<span>下一页</span>");
							}
						}
						
						$.each(data.items,function(i,n){
							var meter = '';
							if(n.meter<10000){
								meter = '约'+(n.meter>>0)+'m';
							}else{
								meter = '约'+((n.meter/1000)>>0)+'km';
							}
							html+='<div class="items"><a href="'+app_url+'biz/'+n.Biz_ID+'/"><div class="img"><img src="'+n.Biz_Logo+'" /></div><ul><li class="title">'+n.Biz_Name+'&nbsp;<font color="#FF6600;">('+n.Category_Name+')</font></li><li class="category">'+n.Biz_Address+'</li><li><img src="/static/images/star_'+n['average_ico']+'.png"/>&nbsp;<span>'+n['average_num']+'分</span></li></ul><div class="buyinfo"><span class="price"><i>￥'+n.Biz_MinPrice+'</i>起</span><span class="route">'+meter+'</span></div></a></div>';
						});
						$('#bizlist').html(html);
					}else{
						$('#bizlist').html('<div class="nodata">暂无内容</div>');
					}
					$('#cover_layer_search').hide();
					$('#loading').hide();
					//页面加载完毕开始执行百度回调函数
					var url="http://api.map.baidu.com/geocoder/v2/?ak="+baidu_map_ak+"&callback=renderReverse&location="+pos.point.lat+","+pos.point.lng+"&output=json&pois=0";
					var script=document.createElement('script');
					script.type='text/javascript';
					script.src=url;
					document.body.appendChild(script);
				},'json');
			});
		};
		
		if(ispost==1){
			$('#cover_layer_search').height($(window).height());
			var top_load = ($(window).height()-50-$('#loading').height())/2;
			var left_load = ($(window).width()-$('#loading').width())/2;
			$('#loading').css({"top":top_load,"left":left_load});
			get_biz_list();
			
			$(document).on('click','#list_pages #pre span',function(){
				if(search_params.page == 1){
					return false;
				}else{
					$('#cover_layer_search').show();
					$('#loading').show();
					search_params.page = search_params.page -1;
					$('#list_pages .cur').html(search_params.page);
					get_biz_list();
				}
			});
			
			$(document).on('click','#list_pages #next span',function(){
				if(search_params.page >= search_params.pagenum){
					return false;
				}else{
					$('#cover_layer_search').show();
					$('#loading').show();
					search_params.page = search_params.page + 1;
					$('#list_pages .cur').html(search_params.page);
					get_biz_list();
				}
			});
			
		}else{
			$('#footer_points,#footer').hide();
			$('.search_body .search_body_title').height($(window).height());
			var contains = function(a,obj){
				if(a.length>0){
					for (var i = 0; i < a.length; i++) {
						if (a[i] === obj) {
							return true;
						}
					}
					return false;
				}else{
					return false;
				}
			};
			
			var get_filter = function(type){
				$('#loading').show();
				var action = "get_"+type;
				var cyc = '';
				if(action == 'get_region'){
					cyc = $(".search_body_title li[ret=region]").attr('citycode');
				}
				var vars = new Array();
				vars = $('#input_'+type).val().split(",");
				$.post(search_ajax_url, {action:action,citycode:cyc}, function(data) {
					if(data.status == 1){
						var items = data.items;
						var html='<ul filter="'+type+'"><li ret="0">不限</li>';
						$.each(items,function(i){
							var flag = contains(vars,items[i]["id"]);
							html+='<li ret="'+items[i]["id"]+'"'+(flag ? ' class="cur"' : '')+'>'+items[i]["name"]+'</li>';
							if(typeof(items[i]["child"]) != 'undefined'){
								$.each(items[i]["child"],function(k){
									var flag1 = contains(vars,items[i]["child"][k]["id"]);
									html+='<li ret="'+items[i]["child"][k]["id"]+'" style="text-indent:25px;"'+(flag1 ? ' class="cur"' : '')+'>'+items[i]["child"][k]["name"]+'</li>';
								});
							}
						});
						html += '</ul>';
						$('#'+type+'_filter_list').html(html);
					}else{
						$('#'+type+'_filter_list').html('<ul><li ret="0">不限</li></ul>');
					}
					$('#loading').hide();
				},'json');
			};
			$('.search_header_title .goback').click(function(){
				history.back();			
			});
			//加载特效
			var top_load = ($(window).height()-50-$('#loading').height())/2;
			var left_load = ($(window).width()-$('#loading').width())/2;
			$('#loading').css({"top":top_load,"left":left_load});
			//目的：获取citycode
			var geolocation=new BMap.Geolocation();
	        geolocation.getCurrentPosition(function(pos){
				//页面加载完毕开始执行百度回调函数
				var url="http://api.map.baidu.com/geocoder/v2/?ak="+baidu_map_ak+"&callback=renderReverse&location="+pos.point.lat+","+pos.point.lng+"&output=json&pois=0";
				var script=document.createElement('script');
				script.type='text/javascript';
				script.src=url;
				document.body.appendChild(script);
			    get_filter('region');
			});
			
			$('.search_body .search_body_title ul li').click(function(){
				var ret = $(this).attr("ret");
				$('.search_body .search_body_title ul li').removeClass("cur");
				$(this).addClass("cur");
				$('.search_body .search_body_content').hide();
				
				if(ret=="price" || ret=="orderby"){
					$('.search_body #'+ret+'_filter_list').show();
				}else{
					get_filter(ret);
					$('.search_body #'+ret+'_filter_list').show();
				}
			});
			
			$(document).on('click','.search_body .search_body_content ul li',function(){
				var filter_type = $(this).parent().attr("filter");
				var ret = $(this).attr("ret");
				$(this).parent().find("li").removeClass("cur");
				$(this).addClass("cur");
				$('#input_'+filter_type).val(ret);
			});
			
			$('#search_form').submit(function() {
				var str_temp = '';
				return true;
			});
		}
	},
}