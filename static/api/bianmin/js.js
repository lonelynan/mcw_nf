var chongzhi_obj = {	
	chongzhi_init:function(){
		$('#phonenum').blur(function(){
			//获取输入框内的值
			var v=$(this).val();
			var reg =/^(1[0-9])\d{9}$/;
			var res = reg.test(v);
			  if(!res){
					 $('.api').html('手机号有误,请重新输入').css('color','red');
					 return false;
				}else{
					$('.api').html('手机号格式正确').css('color','green');
				}
		
			  //进行ajax获取后台页面的传过来的值
			$.post(ajax_url,{input:v,action:'check_phone'},function(data){
				$('.api').html(data.msg).css('color','green');
			}, 'json');
		});
		
		$('.li').click(function(){
			//获取服务商类别
			var type=$(this).find('.type').val();
			//获取服务类别
			var st=$(this).find('.st').val();
			//获取这个的$i
			var i=$(this).find('.put').val();
			
			//获取商品充值
			var chongzhi=$(this).find('.data').val();
			// $(this).find('.pp').css('color','blue');
			$(this).css('background',' #f60').siblings().css('background','');
			$(this).css('color','white').siblings().css('color','');
			$(".submit_btn").removeAttr("disabled"); 
			$.post(ajax_url,{i:i,type:type,Server_Type:st,action:"get_price"},function(data){
		  //对返回值 进行判断
			var res=data.price;
			$('.app').html(res+'元').css('color','red');
			$('.pay_select_list').attr('data-value', res);
			$('.money').val(res);
			}, 'json');
	    });
		
		$('.submit_btn').click(function(){
			var v=$('#phonenum').val();
			var reg =/^(1[0-9])\d{9}$/;
			var res = reg.test(v);
			if(!res){
			//提示错误
				$('.api').html('手机号有误,请重新输入').css('color','red'); 
				return false;
				$(".submit_btn").removeClass("submit_btn"); 
			}else{
				//获取资料发到订单表
				var phone=$('#phonenum').val();
				var money =$('.money').val();
				var userid=$('.userid').val();
				//流量1还是话费0
				var servertype=$('.st').val();
				//联通0移动1
				var style=$('.type').val();
				$(".submit_btn").attr("disabled", 'true'); 
				$.post(ajax_url,{phone:phone,money:money,type:style,Server_Type:servertype,action:'create_order'},function(data){
					//对返回值 进行判断
					var res=data.t;
					var msg=data.msg;
					var ceshi=data.ceshi;
					if(res){
						if(global_obj.check_form($('*[notnull]'))){return false};
						var Obj_Pay_Select_List = $('.pay_select_list');
						applyPrice = parseInt(Obj_Pay_Select_List.attr('data-value'));
						if(applyPrice<=0){
							return false;
						}
		
						// $(this).attr('disabled', true);
						$('.pay_select_bg').css('height',$(window).height()).show();
						Obj_Pay_Select_List.css('height',$(window).height()).show();
						Obj_Pay_Select_List.attr('id', parseInt(data.orderId));
						$('.pay_select_list h3 span').html('&yen;'+applyPrice);
					}else{
						alert('系统繁忙，购买失败！请重新购买！');
						// alert(msg);
						// alert(ceshi);
					}
				}, 'json');
			}
		});
		
		$('#mybtn').click(function(){
			location.href="/api/"+UsersID+"/distribute/" ;
		});
		
		$('.pay_select_list h2 span').click(function(){
			$('.pay_select_list, .money_info').hide();
		});

		$('.pay_select_list a#zfb,.pay_select_list a#wzf').click(function(){
			var orderid = parseInt($(this).parent().attr('id'));
			if(orderid<=0){
				global_obj.win_alert('请先提交订单', function() {});
				return false;
			}
			$.post(ajax_url,'action=get_proxy_pay_method&OrderID='+orderid+'&method='+$(this).attr('data-value'),function(data){
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
			var password = $(this).prev().val();
			var orderid = parseInt($(this).parent().parent().attr('id'));
			
			if(password==''){
				global_obj.win_alert('请输入支付密码', function() {});
				return false;
			}
			
			if(orderid<=0){
				global_obj.win_alert('请先提交订单', function() {});
				return false;
			}
			// $(this).attr('disabled', true);
			$.post(ajax_url,'action=deal_chongzhi_order_pay&PaymentMethod=余额支付&DefautlPaymentMethod=&PaymentInfo=&OrderID='+orderid+'&password='+password,function(data){
				if(data.status==1){
					global_obj.win_alert(data.msg, function() {
                        window.location.href=data.url;
                    });
				}else{
					global_obj.win_alert(data.msg, function() {});
				}
			},'json');
		});

	},
}