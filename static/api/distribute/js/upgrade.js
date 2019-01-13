var upgrade_obj = {	
	upgrade_init:function(){
		/*$('#upgrade_buy .level_list ul li').click(function(){alert(11);
			var lid =  $(this).attr('lid');
			$(this).attr('disabled', true);
			$("#LevelID").val(lid);			
			$.post(ajax_url,$('#upgrade_buy').serialize(),function(data){
				if(data.status==1){
					$("#option_content").hide();
					$(".mask").hide();
					$('.pay_select_bg').show();
					$('.pay_select_list').show();
					$('.pay_select_list').attr('id',data.orderid);
					$('.Order_info').html('订 单 号：'+data.orderhao);
					$('#Order_TotalPrice').html('￥'+data.money);
					$('.fotter').hide();
					$('.foot').hide();
					$('.pay_select_list h3 span').html('&yen;'+data.money);
					
				}else{
					global_obj.win_alert(data.msg, function() {
                        window.location.reload();
                    });
				}
			},'json');
		});*/
		
		
		
		$('#upgrade_buy .level_list ul li').click(function(){
			$('#upgrade_buy .level_list ul li').removeClass('cur');
			$(this).addClass('cur');
			var lid=$(this).attr('lid');
			var settype=$(this).attr('settype');			
			var tip=$(this).find('b').html();			
			$.post(ajax_url,'action=get_level_info_upgrade&LevelID='+lid+'&settype='+settype+'&tip='+tip,function(data){
				if(data.status==1){//补差价
					$('#upgrade_buy input[name=LevelID]').attr('value',lid);
					$('#upgrade_buy .level_total span').html('&yen; '+data.price);
					$('#upgrade_buy .level_total,#upgrade_buy .level_submit').show();
					$.post(ajax_url,$('#upgrade_buy').serialize(),function(data){
						if(data.status==1){
							$(".level_list").hide();
							$(".fxbj_x").hide();
							$('.pay_select_bg').show();
							$('.pay_select_list').show();
							$('.pay_select_list').attr('id',data.orderid);
							$('.Order_info').html('订 单 号：'+data.orderhao);
							$('#Order_TotalPrice').html('￥'+data.money);
							$('.fotter').hide();
							$('.foot').hide();
							$('.pay_select_list h3 span').html('&yen;'+data.money);
							
						}else{
							global_obj.win_alert(data.msg, function() {
								window.location.reload();
							});
						}
					},'json');
				}else if(data.status==2){//购买指定商品
					$('#upgrade_buy .level_total,#upgrade_buy .level_submit').hide();
					$('#upgrade_buy input[name=LevelID]').attr('value','');
					$('#upgrade_buy .level_total span').html('');
					$('.body_bg').css('height',$(window).height()).show();
					$('.products_list_tobuy').css('height',$(window).height()).show();
					$('.products_list_content').html(data.msg);
				}else if(data.status==3){//消费额
					global_obj.win_alert(data.msg, function() {
                       window.location.href=data.gourl;
                    });
				}else{
					global_obj.win_alert(data.msg, function() {
                        window.location.reload();
                    });
				}
			},'json');
		});
		
		$('.products_list_tobuy h2 span').click(function(){
			$('.body_bg').hide();
			$('.products_list_tobuy').hide();
		});
		
		$('#upgrade_buy').submit(function() {
            return false;
        });
		
		$('#upgrade_buy input[name=submit]').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
			var lid=parseInt($('#upgrade_buy input[name=LevelID]').attr('value'));			
			if(lid<=0){
				return false;
			}
			$(this).attr('disabled', true);
			$.post(ajax_url,$('#upgrade_buy').serialize(),function(data){
				if(data.status==1){
					$('.pay_select_bg').css('height',$(window).height()).show();
					$('.pay_select_list').css('height',$(window).height()).show();
					$('.pay_select_list').attr('id',data.orderid);
					$('.pay_select_list h3 span').html('&yen;'+data.money);
				}else{
					global_obj.win_alert(data.msg, function() {
                        window.location.reload();
                    });
				}
			},'json');
		});		
		
		$('.pay_select_list a#zfb,.pay_select_list a#wzf').click(function(){
			var orderid = $('.pay_select_list').attr("id");
			/*var orderid = parseInt($(this).parent().attr('id'));*/
			if(orderid<=0){
				global_obj.win_alert('请先提交订单', function() {});
				return false;
			}
			$.post(ajax_url,'action=get_distribute_pay_method&OrderID='+orderid+'&method='+$(this).attr('data-value'),function(data){
				if(data.status==1){
                    window.location.href=data.url;
				}else{
					global_obj.win_alert(data.msg, function() {});
				}
			},'json');
		});
		
		$('.pay_select_list a#money').click(function(){
			$('.pay_select_list .money_info').show();
		});
		
		$('.pay_select_list button').click(function(){
			var password = $(this).parent().children('input').attr('value');
			var orderid = parseInt($(this).parent().parent().attr('id'));
			if(password==''){
				global_obj.win_alert('请输入支付密码', function() {});
				return false;
			}
			
			if(orderid<=0){
				global_obj.win_alert('请先提交订单', function() {});
				return false;
			}
			$(this).attr('disabled', true);
			$.post(ajax_url,'action=deal_level_order_pay&OrderID='+orderid+'&password='+password,function(data){
				if(data.status==1){
					global_obj.win_alert(data.msg, function() {
                        window.location.href=data.url;
                    });
				}else{
					global_obj.win_alert(data.msg, function() {});
					$('.pay_select_list button').attr('disabled', false);
				}
			},'json');
		});
	},
}