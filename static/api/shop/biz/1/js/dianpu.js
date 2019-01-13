// JavaScript Document

var dianpu_obj = {
	page_init:function(){
		$('#header_biz .back').click(function(){
			history.go(-1);
		});		
		
		$('#header_biz .favourite').click(function(){
			$.post(biz_ajax_url, {action:'add_favourite'}, function(data) {
				if(data.status == 1){
					alert('已加入收藏夹');
				}else{
					window.location.href=data.url;
				}
			},'json');
		});
	},
	
	commit_init:function(){
		var get_commit_list = function(page){
			$.post(biz_ajax_url, {action:'get_commit',page:page}, function(data) {
				if(data.status == 1){
					var html='';
					var ret = $('#list_more').attr("page");
					 $('#list_more').attr("page",parseInt(ret)+1);
					if(ret == data.pagenum){
						 $('#list_more').hide();
					}
					
					$.each(data.items,function(i,n){
						html+='<div class="items"><h2><i>'+n.Score+'</i>分<span>'+n.productsname+'&nbsp;&nbsp;'+n.addtime+'</span></h2><h3>'+n.Note+'</h3></div>';
					});
					$('#commit_list').append(html);
				}
			},'json');
		};
		get_commit_list(1);
		$('#list_more').click(function(){
			var f_page = $(this).attr("page");
			get_commit_list(f_page);
		});
	},
	
	albums_init:function(){
		var get_albums_list = function(){
			$.post(biz_ajax_url, {action:'get_albums'}, function(data) {
				if(data.status == 1){
					var html='';					
					$.each(data.items,function(i,n){
						html+='<div class="item"><div><ul><li class="img"><a href="'+n.url+'"><img src="'+n.Category_ImgPath+'" /></a></li><li class="bg"></li><li class="title"><a href="'+n.url+'">'+n.Category_Name+'('+n.photonum+')'+'</a></li></ul></div></div>';
						html = i%2==1 ? html+'<div class="clear"></div>' : html;
					});
					$('#albums_list').html(html+'<div class="clear"></div>');
				}
			},'json');
		};
		get_albums_list();
	},
	
	photos_init:function(){		
		(function(window, $, PhotoSwipe){
			$('#albums_list a[rel]').photoSwipe({});
		}(window, window.jQuery, window.Code.PhotoSwipe));
	}
}