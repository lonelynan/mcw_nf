var http_host_ary=window.location.host.split('.');
http_host_ary.shift();
var http_host=http_host_ary.join('.');
var domain={
	www:'http://www.'+http_host,
	static:'/static',
	img:'http://www.'+http_host,
	kf:'http://www.'+http_host,
};
var pifa_obj={
	index_init:function(){
		
	},
	product_init:function(){
		$("#seller-info").click(function(){
			var top = $(window).height()/2;
			$("#seller-modal").css('top',top-80);
			$("#seller-modal").modal('show');
		});
	},

	order_init:function(){
		var price_detail=function(){
			var vars = $('#payment_form').serialize();
			$.post('/api/'+UsersID+'/pifa/cart/ajax/', vars+'&action=qty', function(data) {
				if(data.status == 1){
				    var price = parseFloat(data.price);
					var qty = parseInt(data.qty);
					var total_price = parseFloat(data.total)+parseFloat(data.shipping_fee);
					if(qty >= ini_num){
						$("#total_shipping_fee").attr('value', data.shipping_fee);
						$('#total_price').html(parseFloat(price*qty).toFixed(2));//不计运费
						$('#total_shipping_fee_txt').html(data.shipping_fee+'元');
						$('.footer_left span').html(total_price.toFixed(2));//小计+运费
						$('.heji_left b').html(data.price);
					}
				}else{
					alert(data.msg);
					$('input[name=Qty]').val(ini_num);
				}
			},'json');	
		};
		$(document).on('keyup','input[name=Qty]',function(){
			var qty = parseInt($(this).val());
			isNaN(qty) && (qty=ini_num);
			qty<=0 && (qty=ini_num);
			$(this).val(qty);
			price_detail();
		});
		
		$(document).on('click','a[name=minus]',function(){
			var input = $('input[name=Qty]');
			var qty=parseInt(input.val());
			isNaN(qty) && (qty=ini_num);
			qty = qty - 1;
			qty<=0 && (qty=ini_num);
			input.val(qty);
			price_detail();
		});
		
		$(document).on('click','a[name=add]',function(){
			var input = $('input[name=Qty]');
			var qty=parseInt(input.val());
			isNaN(qty) && (qty=ini_num);
			qty = qty + 1;
			input.val(qty);
			price_detail();	
		});
		//运费初始化
		var change_shipping_method=function(){
			var Shiping_ID = parseInt($("input[name='Shiping_ID']:checked").attr('value'));
			var Shipping_Name  = $("input[name='Shiping_ID']:checked").attr('shipping_name');
			var City_Code = $("input[name=City_Code]").attr('value');
			var qty = $('input[name=Qty]').val();
			var ownerid = $('input[name=OwnerID]').val();
			var action = 'change_shipping_method';
			var url = base_url + 'api/' + UsersID + '/pifa/cart/ajax/';
			var param = {
				Shiping_ID:Shiping_ID,
				City_Code:City_Code,
				qty:qty,
				ProductID:ProductID,
				ownerid:ownerid,
				action:action
			};	
			$.post(url, param, function(data) {
				if(data.status == 1){
					var total_price = parseFloat(data.total) + parseFloat(data.total_shipping_fee);
					$("#shipping_name").html(Shipping_Name); 
					$("#Order_Shipping_Express").attr('value',Shipping_Name);
					$("#total_shipping_fee").attr('value', data.total_shipping_fee);
					if (parseFloat(data.total_shipping_fee) == 0) {
						$('#total_shipping_fee_txt').html('免运费');
					} else {
						$('#total_shipping_fee_txt').html(data.total_shipping_fee + '元');
						$('.footer_pay .footer_left span').html(total_price);
					}
				}
				
			},'json');
		}
		//选择配送方式
		$("#shipping_method").click(function(){
			var top = $(window).height()/2;
			$("#shipping-modal").css('top',top-80);
			$("#shipping-modal").modal('show');
		});
		$("#confirm_shipping_btn").live('click',function(){
			$("#shipping-modal").modal('hide');
			change_shipping_method();
		});
		$("#cancel_shipping_btn").live('click',function(){
		    $("#shipping-modal").modal('hide');
		});
		//选择支付方式
		$('.payment li').click(function(){
			$('.payment li').removeClass('cur');
			$(this).addClass('cur');
			var PaymentMethod = $(this).attr("data-value"); 
			var vars = $('#payment_form').serialize();
			$.post('/api/'+UsersID+'/pifa/cart/ajax/', vars+'&action=select_payment', function(data) {
				if(data.status == 1){
					$('.footer_pay .footer_left span').html(data.total);
					$("input[name=PaymentMethod]").val(PaymentMethod);
					$('.footer_pay').show();
				}
			},'json');
		});
		//支付提交
		$('.footer_pay .submit,#btn-confirm').click(function(){
            $.post($('#payment_form').attr('action') + 'ajax/', $('#payment_form').serialize()+'&action=payment', function(data) {
                if (data.status == 1) {
					if(data.url){
						window.location = data.url
					}
                } else {
                    global_obj.win_alert(data.msg);
                }
            }, 'json');
		});
		if(typeof ProductID != 'undefined'){
			//change_shipping_method();//初始化物流信息  （有缓存）
		}
		/*确认收货*/
        var numtips = 0;
        $("a.confirmreceive").click(function() {
			numtips = numtips+1;
			$(this).hide();
            var param = {
                action: 'confirm_receive',
                Order_ID: $(this).attr('Order_ID')
            };


            if(numtips==1){
				$.post(base_url + 'api/' + UsersID + '/pifa/member/ajax/', param, function(data) {				
					if(data.status == 1){
						window.location.href = base_url + 'api/' + UsersID + '/pifa/member/status/4/';
					}else{
						alert(data.msg);
					}
				},'json');
			}
        });
	},
	
	//订单支付（用户中心）
	payment_init: function() {
		$("a.direct_pay,#btn-confirm").click(function(){
			var PaymentMethod = $(this).attr("data-value"); 
			$("input[name=PaymentMethod]").val(PaymentMethod);
			
			$('#payment_form .payment input').attr('disabled', true);
            $.post($('#payment_form').attr('action') + 'ajax/', $('#payment_form').serialize(), function(data) {
                $('#payment_form .payment input').attr('disabled', false);
                if (data.status == 1) {
                    window.location = data.url
                } else {
                    global_obj.win_alert(data.msg);
                }
            }, 'json');
		});
        $('#payment_form').submit(function() {
            return false;
        });
    },
	
	backup_init: function() {

        $("input.back_num").blur(function() {
            var qty = $(this).attr("qty");
            var value = $(this).val();
            if (value > qty) {
                $(this).after('<span id="num_tip" class="fc_red">您只购买了' + qty + '个产品</span>');
                $(this).attr("value", "")
            }
        });

        $("input.back_num").keydown(function() {
            $("#num_tip").remove();
        });

        $("input[name='Products_ID[]']").click(function() {
            var product_id = $(this).val();
            if ($(this).is(':checked')) {

                $("#backup_reason_" + product_id).css({
                    display: "block"
                });

            } else {
                $("#backup_reason_" + product_id).css({
                    display: "none"
                });

            }


        });

        /*提交*/
        $("#submit_shipping").click(function(e) {
            if (global_obj.check_form($('*[notnull]'))) {
                return false;
            };

            var url = $("form#backup_shipping_form").attr("action") + "ajax/";
            var params = $("form#backup_shipping_form").serialize();
            params = decodeURIComponent(params, true);


            $.post(url, params,
                function(data) {
                    if (data.status == 0) {
                        alert("信息提交错误");
                    } else if (data.status == 1) {
                        
						window.location = data.url;
                    }
                },
                'json');

            e.preventDefault();
        });

        /*申请退货*/
        $("#apply_backup").click(function(e) {

            var chk_value = []; //定义一个数组    
            $("input[name='Products_ID[]']:checked").each(function() { //遍历每一个名字为interest的复选框，其中选中的执行函数    

                chk_value.push($(this).val()); //将选中的值添加到数组chk_value中    
            });

            if (chk_value.length == 0) {

                alert("请勾选您要退货的商品");
            } else {
                var flag = true;
                $("input[name='Products_ID[]']:checked").each(function() {
                    var id = $(this).val();

                    if (global_obj.check_form($("#backup_reason_" + id).find('*[notnull]'))) {
                        flag = false;
                    };
                });

                if (!flag) {
                    return false;
                }

                var Order_ID = $("#Order_ID").val();
                var products = chk_value.join("_");
                var url = $("form#apply_form").attr("action") + "ajax/";
				
                var params = $("form#apply_form").serialize();
                params = decodeURIComponent(params, true);


                $.post(url, params,
                    function(data) {
                        if (data.status == 0) {
                           alert("信息提交错误");
                        } else if (data.status == 1) {
                           window.location = data.url;
                        }
                    },
                    'json');

            }


        });
    },
	commit_init: function() {
        $('#commit_form .back').click(function() {
            history.back();
        });

        $('#commit_form').submit(function() {
            return false;
        });
        $('#commit_form .submit').click(function() {
            if (global_obj.check_form($('*[notnull]'))) {
                return false
            };

            $(this).attr('disabled', true);
            $.post($('#commit_form').attr('action') + 'ajax/', $('#commit_form').serialize(),
            function(data) {
                if (data.status == 1) {
                    global_obj.win_alert('评论成功!',
                    function() {
                        window.location = $('#commit_form').attr('action') + 'status/4/';
                    });
                } else {
                    global_obj.win_alert(data.msg);
                }
            },
            'json');
        });
    },
}