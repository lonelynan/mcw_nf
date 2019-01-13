var member_coupon_obj={
	member_coupon_init:function(){
		function load_page(page) {
			$.ajax({
				type:'post',
				url:ajax_url,
				data:{p:page},
				success:function(data){
					if(data['list'] != '') {
						var htmltmp = '';
						var jiazhi = '<div style="height:45px;"></div>';
						var fanwei = '<div style="height:45px;"></div>';
						$.each(data['list'],function(i) {
							v = data['list'][i];
							var bg_img = '';
							if(v['Coupon_UseType'] == 0 && v['Coupon_Discount'] > 0 && v['Coupon_Discount'] < 1) {
							    jiazhi = (v['Coupon_Discount']*10) + '<i>折</i>';
							}else if(v['Coupon_UseType'] == 1 && v['Coupon_Cash'] > 0) {
							    jiazhi = '<i>￥</i>' + v['Coupon_Cash'];
							}
							if(v['Coupon_UseArea'] == 1 && v['Coupon_Condition'] > 0){
							    fanwei = '【一次性购买满 ' + v['Coupon_Condition'] + '元 可用】';
							}else if(v['Coupon_UseArea'] == 0){
							    bg_img = 'background:url('+v['Coupon_PhotoPath']+') no-repeat center center';
							}
							htmltmp += '<div class="coupons_form">'+
												'<div class="top" style="'+bg_img+'">'+
													'<div class="left">'+
														'<span class="couprice">'+jiazhi+'</span><div class="clear"></div>'+
														'<span>'+fanwei+'</span>'+
														'<span>'+v['Coupon_StartTime']+'--'+v['Coupon_EndTime']+'</span>'+
													'</div>'+
													'<div class="right">';
							if(data['type'] == 0) {
								if(v['Coupon_UseArea'] == 0){
								    htmltmp += 			'<a href="javascript:;" class="use" CouponID='+v['Coupon_ID']+'>立即使用</a>';
								}else{
									htmltmp += 			'<a href="javascript:;">商城使用</a>';
								}
							}else if(data['type'] == 2) {
								htmltmp += 			'<a href="javascript:;" style="color:#666">已经过期</a>';
							}else if(data['type'] == 1){
								htmltmp += 			'<a href="javascript:;" class="get" CouponID='+v['Coupon_ID']+'>立即领取</a>';
							}			
							htmltmp +=		    '</div>'+
												'</div>'+
												'<div class="buttom">'+
													'<span>名&nbsp;&nbsp;称： '+v['Coupon_Subject']+'</span><br>'+
													'<span>适 用 于： '+(v['Coupon_UseArea'] == 1 ? '在线商城' : '实体店')+' </span><br>'+
													'<span>可使用次数： '+(v['Coupon_UsedTimes'] == -1 ? '无限' : v['Coupon_UsedTimes'])+'次</span><br>'+
												'</div>'+
											'</div>';
							
								
						});
						if($('input[name=page]').val() == 1) {
							$('#up').attr('flag','false').css({'color':'#999','cursor':'auto'});
						}else {
							$('#up').attr('flag','true').css({'color':'#333','cursor':'pointer'});
						}
						if(data.totalpage == $('input[name=page]').val()) {
							$('#down').attr('flag','false').css({'color':'#999','cursor':'auto'});
						}else {
							$('#down').attr('flag','true').css({'color':'#333','cursor':'pointer'});
						}
						$('#cur_page').html($('input[name=page]').val());
						$('#total_page').html(data.totalpage);
						$('.coupons').html(htmltmp);
					 }else{
						$('#up').attr('flag','false').css({'color':'#999','cursor':'auto'});
						$('#down').attr('flag','false').css({'color':'#999','cursor':'auto'});
					}
				},
				dataType:'json',
			});
		}
        load_page($('input[name=page]').val());
		$('.fanye').on('click','#up[flag=true]',function(){//上一页
			var page = parseInt($('input[name=page]').val()) - 1;
			$('input[name=page]').val(page);
			load_page($('input[name=page]').val());
		});
		$('.fanye').on('click','#down[flag=true]',function(){//下一页
			var page = parseInt($('input[name=page]').val()) + 1;
			$('input[name=page]').val(page);
			load_page($('input[name=page]').val());
		});
		$('.fanye').on('click','#submit',function(){//跳转
		    if($('#text').val() != ''){
				var page = parseInt($('#text').val());
				$('input[name=page]').val(page);
				load_page($('input[name=page]').val());
			}	
		});
		$(document).on('click', '.delete', function(){
			var f_id = $(this).attr('f_id');
			$.post(shop_ajax_url, {action:'del_shoucang',del_id:f_id}, function(data){
				if(data.status == 1){
					alert(data.msg);
					location.reload();
				}else {
					alert(data.msg);
				}
			}, 'json');
		});
		//领取动作
		$('.coupons').on('click','.get',function(){
			var o = $(this);
			o.html('领取中...');
			$.post(shop_ajax_url, 'action=get_coupon&CouponID='+o.attr('CouponID'), function(data){
				o.html('领取');
				if(data.status == 1){
					alert(data.msg);
					window.location = data.url;
				}else{
					alert(data.msg);
				};
			}, 'json');
		});
		//实体店使用操作
		$('.saveaddress').click(function(){
		    var that = this;
			var post_date = $('.box_dizhi_form form').serialize()+'&action=use_coupon';
			$.post(shop_ajax_url, post_date, function(data){
				if(data.status == 1) {
					$('.box_dizhi_form').fadeOut(200);
					if(data.url){
					    location.href = data.url;
					}else{
					    location.reload();
					}
					
				}else {
					alert(data.msg);
				}
			}, 'json');
		});
		$('.coupons').on('click','.use',function(){
		    $('.box_dizhi_form input[name=CouponID]').val($(this).attr('CouponID'));
		    $('.box_dizhi_form').fadeIn(200);
		});
		//关闭弹窗
		$('.cut').click(function(e) {
			$('.box_dizhi_form').fadeOut(200);
		});
	},
}