var apply_obj = {	
	apply_init:function(){
		var e = $(window).height();
		var f = $("#option_content").height();
		$('.pay_select_bg').height($(window).height());
		$('.pay_select_list').height($(window).height());
		$('.products_list_bg').height($(window).height());
		$('.products_list_tobuy').height($(window).height());
		$('a.products_buy').click(function(){
			var lid=$(this).attr('lid');
			$.post(ajax_url,'action=get_level_products&LevelID='+lid,function(data){
				if(data.status==1){					
					$('.products_list_bg').show();
					$('.products_list_tobuy').show();
					$('.products_list_content').html(data.msg);
				}else{
					global_obj.win_alert(data.msg, function() {
                        window.location.reload();
                    });
				}
			},'json');
		});		
		$('.products_list_tobuy h2 span').click(function(){
			$('.products_list_bg').hide();
			$('.products_list_tobuy').hide();
		});
		
		$('#apply_buy').submit(function() {
            return false;
        });
		
		$('#submit_btn_bottom').click(function(){		
			if(global_obj.check_form($('.box1_x *[notnull]'))){return false};
			var lid=parseInt($('#apply_buy input[name=LevelID]').attr('value'));			
			if(lid<=0){
				return false;
			}
			var Mobile = $("#Mobile").val();
			if(Mobile != undefined){
				var myreg = /^(((13[0-9]{1})|(14[0-9]{1})|(17[0]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1}))+\d{8})$/;       
		  		if(!myreg.test(Mobile)){
					alert("请输入有效的手机号码！");
					return false;
				}
			}
			
			if($('input[name=agree]').attr("value")==1){
				if(!$('input[name=agreement]').attr("checked")){
					alert('你还没同意协议');
					return false;
				}
			}
			
			$(this).attr('disabled', true);
			var lid =  $("#apply_buy li.submit_btn:first").attr('lid');
			$("#LevelID").val(lid);			
			$.post(ajax_url,$('#apply_buy').serialize(),function(data){
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
		});

        //手动提交申请
        $('#submit_btn_bottom_apply').click(function() {
            var me = $(this);
            if (me.attr('status') == 1) return false;

            if(global_obj.check_form($('#apply_manual *[notnull]'))) return false;

            var Mobile = $("#Mobile").val();
            if(Mobile != undefined){
                var myreg = /^1[3-9]\d{9}$/;
                if(!myreg.test(Mobile)){
                    layer.open({
                        content: '请输入有效的手机号码',
                        skin: 'msg',
                        time: 1,
                        end: function () {
                            $('#Mobile').focus();
                        }
                    });
                    return false;
                }
            }

            var name = me.html();
            me.attr('status', '1').html('提交中...');

            $.post('/api/' + UsersID + '/distribute/ajax/', $('#apply_manual').serialize(), function(data){
                if(data.status == 1){
                    layer.open({
                        content: data.msg,
                        skin: 'msg',
                        time: 1,
                        end: function () {
                            me.html(data.msg);
                        }
                    });
                } else {
                    me.attr('status', '0').html(name);
                    if (data.status == -1) {
                        layer.open({
                            content: data.msg,
                            btn: '确定',
                            shadeClose: false,
                            yes: function () {
                                window.self.location.reload();
                            }
                        });
                    } else {
                        layer.open({
                            content: data.msg,
                            btn: '确定'
                        });
                    }
                }
            },'json');
        });
		
		
		$('#apply_buy li.submit_btn').click(function(){
			if(global_obj.check_form($('.box1_x *[notnull]'))){return false};
			var lid=parseInt($('#apply_buy input[name=LevelID]').attr('value'));	
			if(lid<=0){
				return false;
			}
			if($('input[name=agree]').attr("value")==1){
				if(!$('input[name=agreement]').attr("checked")){
					alert('你还没同意协议');
					return false;
				}
			}
			$(this).attr('disabled', true);
			var lid =  $(this).attr('lid');
			$("#LevelID").val(lid);			
			$.post(ajax_url,$('#apply_buy').serialize(),function(data){
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
		});			
		
		$('.pay_select_list a#zfb,.pay_select_list a#wzf,.pay_select_list a#ybzf,.pay_select_list a#uni,.pay_select_list a#out').click(function(){	
			var join_share = $(this).attr('join_share');
			var Order_ID = $('.pay_select_list').attr("id");
			if(join_share == 1){
				  window.location.href="/api/"+UsersID+"/user/login/"+Order_ID+"/";
			}
			var orderid = parseInt($(this).parent().parent().parent().parent().parent().attr('id'));			
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
		
		$('.pay_select_list h2 span').click(function(){
			$('.pay_select_bg').hide();
			$('.pay_select_list').hide();
			$('#apply_buy input[name=submit]').attr('disabled', false)
		});
		
		$('.pay_select_list a#money').click(function(){
			$('.pay_select_list .money_info').show();
		});
		
		$('.pay_select_list button').click(function(){			
			var orderid = parseInt($(this).parent().parent().attr('id'));			
			$(this).attr('disabled', true);
			$.post(ajax_url,'action=deal_level_order_pay&OrderID='+orderid+'&password='+password,function(data){
				if(data.status==1){
					global_obj.win_alert(data.msg, function() {
                        window.location.href=data.url;
                    });
				}else{
					$('.pay_select_list button').attr('disabled', false);
					global_obj.win_alert(data.msg, function() {});
				}
			},'json');
		});
		
		
		 $("a.topLink").click(function() {
		    $("html, body").animate({
		      scrollTop: $($(this).attr("href")).offset().top + "px"
		    }, {
		      duration: 500,
		      easing: "swing"
		    });
		    return false;
		  });	    
		
		$("#menu-addtocard-btn").on('click',function(){			
			if(global_obj.check_form($('.box1_x *[notnull]'))){
			}else{
				var Mobile = $("#Mobile").val();
				if(Mobile != undefined){
					var myreg = /^(((13[0-9]{1})|(14[0-9]{1})|(17[0]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1}))+\d{8})$/;       
			  		if(!myreg.test(Mobile)){
						alert("请输入有效的手机号码！");
						return false;
					}
				}
				if($('input[name=agree]').attr("value")==1){
					if(!$('input[name=agreement]').attr("checked")){
						alert('你还没同意协议');
						return false;
					}
				}				
				var a = $(window).height();
				var h = $("#option_content").find('li').length;
				var b = h*120;
				var d = $(".level_top").height();
				var c = a*0.7;
				if (e-a > 100) {					
					a = e;					
					c = a*0.7;
				}				
				if(b>c){
					$('#option_content').height(c);
					$("#option_selecter").css("height",c-d);
				}else{
					$('#option_content').height(b);
					$("#option_selecter").css("height",b-d);
				}
				$(".mask").fadeIn(100);
				$("#option_content").slideDown(200);
				$('.fotter').hide();
				$('.foot').hide();
			}			
		});

		$("#selector_confirm_btn").on('click',function(){
			if (is_virtual == 1) {
				add_to_cart('Virtual');    
            } else {
				add_to_cart('DirectBuy');
            }
		});
		 $("#qty_selector a[name=minus]").on("click", function() {

            var qty = parseInt($('#qty_selector input[name=Qty]').val()) - 1;
            var total_stock = parseInt($('#stock_val').html());

            if (qty < 1) {
                qty = 1;
            }

            var cur_price = parseFloat($("#cur_price").val());
            var float_qty = parseFloat(qty + '.00').toFixed(2);
            $('#cur-price-txt').html(parseFloat(cur_price * float_qty).toFixed(2));

            $('#qty_selector input[name=Qty]').attr("value", qty);
        });

        $("#qty_selector a[name=add]").on("click", 
        function() {

            var qty = parseInt($('#qty_selector input[name=Qty]').val()) + 1;
            var total_stock = parseInt($('#stock_val').html());

            if (qty > total_stock) {
                qty = total_stock;
            }

            var cur_price = parseFloat($("#cur_price").val());
            var float_qty = parseFloat(qty + '.00').toFixed(2);
            $('#cur-price-txt').html(parseFloat(cur_price * float_qty).toFixed(2));

            $('#qty_selector input[name=Qty]').attr("value", qty);

        });

        $("#qty_selector input[name=Qty]").on("change",
        function() {

            var qty = parseInt($('#qty_selector input[name=Qty]').val());
            var total_stock = parseInt($('#stock_val').html());
            if (qty < 1) {
                qty = 1;
            }

            if (qty > total_stock) {
                qty = total_stock;
            }
			if(isNaN(qty)){
				 qty = 1;
			}
            var cur_price = parseFloat($("#cur_price").val());
            var float_qty = parseFloat(qty + '.00').toFixed(2);
            $('#cur-price-txt').html(parseFloat(cur_price * float_qty).toFixed(2));

            $('#qty_selector input[name=Qty]').attr("value", qty);
        });
		
        //直接购买&&虚拟物品直接购买
        function add_to_cart(cart_key) {			
            $.post($('#addtocart_form').attr('action') + 'ajax/', $('#addtocart_form').serialize()+'&cart_key='+cart_key,
            function(data) {
                if (data.status == 1) {
					if(cart_key == 'Virtual'){
						window.location.href = "/api/" + UsersID + "/shop/cart/checkout_virtual/?love";
					}else if(cart_key == 'DirectBuy'){
						window.location.href = "/api/" + UsersID + "/shop/cart/checkout_direct/?love";
					}
                } else {
                    new Toast({
                        context: $('body'),
                        message: data.msg,
                        top: $("#footer").offset().top - 70,
                        time: 4000
                    }).show();
                }
            },
            'json');
        }

		$("#close-btn").on('click',function(){
			$('#option_content').hide();
			$('.mask').hide();
			$('.fotter').show();
			$('.foot').show();

		});

	},
}