// JavaScript Document
function share_guide() {
    $("#mcover").css("display", "block"); // 分享给好友圈按钮触动函数
}

function weChat() {
    $("#mcover").css("display", "none"); // 点击弹出层，弹出层消失
}

//拼团订单标志
var order_type = typeof order_type!="undefined" ? order_type : 'shop';

var shop_obj = {
    page_init: function() {
        //点击提现按钮
	$("#withdraw_btn").click(function(){
            window.location.href = $(this).attr("link");
	});
	
		$('.Yuebc_btn').click(function(){//余额补差	
			if($(this).prop("checked")==false){
				$("#Yuebc_info").hide();
				$("input[name='Yuebc_InvoiceInfo']").val('');
				$("#Yuebc").css('height', '60px');
				
				var total_price = $("input[name='total_price']").val();
				$('#Useryue').html('￥' + 0);
				$('#Yingpay').html(total_price);
				$("input[name='total_pricecurpay']").val(total_price);
				$("input[name='Yuebc_price']").val(0);
			}else{
				layer.closeAll();
				$("#Yuebc").css('height', '100px');
				$("#Yuebc_info").show();				
			}
		});		
		
		$("input[name='Yuebc_InvoiceInfo']").keyup(function() {
			var ajax_url = base_url + 'api/' + UsersID + '/shop/cart/ajax/';
			var Yuebcprice = $(this).val();
			var orderid = $(this).attr('orderid');
			var total_price = $("input[name='total_price']").val();
				$.post(ajax_url, 'action=Yuebc_update&Yuebcprice=' + Yuebcprice + '&orderid=' + orderid,
            function(data) {
                if (data.status == 1) { //余额补差                   
                   $('#Useryue').html('￥' + data.Yuebcprice);
                   $("input[name='total_pricecurpay']").val(data.total);
                   $("input[name='Yuebc_price']").val(data.Yuebcprice);
                   $("#Yingpay").html(data.total);
                } else {
					$("input[name='Yuebc_InvoiceInfo']").val('');
					$("input[name='Yuebc_price']").val(0);
					$('#Useryue').html('￥' + 0);
					$("input[name='total_pricecurpay']").val(total_price);
					$("#Yingpay").html(total_price);
                    global_obj.win_alert(data.msg);                    
                }
            },
            'json');
            });
			
        $('#category .close, #cover_layer').click(function() {
            $('html, body').removeAttr('style');
            $('#cover_layer').hide();
            $('#category').animate({
                left: '-100%'
            },
            500);
            $('#shop_page_contents').animate({
                margin: '0'
            },
            500);
        });

        if ($('#support').size()) {
            $('#support').css('bottom', 0);
            $('#footer').css('bottom', 16);
            $('#footer_points').height(57);
        }

        $('footer .category a').click(function() {
            if ($('#category').height() > $(window).height()) {
                $('html, body, #cover_layer').css({
                    height: $('#category').height(),
                    width: $(window).width(),
                    overflow: 'hidden'
                });
            } else {
                $('#category, #cover_layer').css('height', $(window).height());
                $('html, body').css({
                    height: $(window).height(),
                    overflow: 'hidden'
                });
            }

            $('#cover_layer').show().html('');
            $('#category').animate({
                left: '0%'
            },
            500);
            $('#shop_page_contents').animate({
                margin: '0 -60% 0 60%'
            },
            500);
            window.scrollTo(0);

            return false;
        });

        /*确认收货*/
        var numtips = 0;
        $("a.confirmreceive").click(function() {
			var _this = $(this);

			layer.open({
                content: "您点击确定收货后,将不能退款退货。"
                ,btn: ['确定', '取消']
                ,yes: function(index){
                    numtips = numtips+1;
                    
                    var param = {
                      action: 'confirm_receive',
                      Order_ID: _this.attr('Order_ID')
                    };


                    if(numtips==1){
                      $.post(base_url + 'api/' + UsersID + '/shop/member/ajax/', param, function(data) {				
                        if(data.status == 1){
                            layer.open({
                                content: data.msg,
                                className: 'msg_layer',
                                skin: 'msg',
                                time: 1,
                                end: function () {
                                    window.location.href = data.url ? data.url : (base_url + 'api/' + UsersID + '/shop/member/status/4/');
                                }
                            });
                        }else{
                            layer.open({
                              content: data.msg
                              ,btn: '确定'
                            });
                        }
                      },'json');
                    }
                    layer.close(index);
                }
              });
        });

    },
    tao_detail_init: function() {		
		var e = $(window).height();
        var window_height = $(document).height();
        var window_half_height = $(window).height() / 2;
        //处理轮播图片
        $('#detail_images .touchslider').css('height', $(window).width() * 0.8);
        $('#detail_images .touchslider .touchslider-viewport .touchslider-item').css('width', $(window).width());

        $('#detail_images .touchslider .img').css('height', $(window).width() + 'px');
        if ($('.touchslider-viewport').length > 0 && proimg_count > 0) { (function(window, $, PhotoSwipe) {

                $('.touchslider-viewport .list a[rel]').photoSwipe({});

            } (window, window.jQuery, window.Code.PhotoSwipe));

            $('.touchslider').touchSlider({
                mouseTouch: true,
                autoplay: true,
                delay: 2000
            });
        }

        function show_footer_panel() {
            if ($('footer').html().length > 0) {

                $('#option_content').html($("#footer").html());
            }

            var footer_panel_content = $("#footer_panel_content").html();
            $('footer').html(footer_panel_content);

        }
		
		function hide_footer_panel() {
            $("#option_content").css('overflow', 'visible');
            if ($('footer').length > 0) {
            $('footer').html('');
            }
        }

        var handler = function(e) {

            if ($(e.targent).attr('class') != 'property') {
                e.preventDefault();
            }

        }

        show_footer_panel();
		
		//加入购物车和直接购买
        $('footer, .footer_pt').on('click', '#menu-addtocard-btn,#menu-direct-btn',
        function() {
            var flag = $("input[name='flag']").val();
            if(flag == 0){
				/*
                var selector_content = $("#option_content").html();
                $("#option_content").html('');
                $("footer").html(selector_content);*/
                if($(this).attr("id") == "menu-direct-btn"){
                    if (order_type == 'pintuan') {    //拼团购买
                        add_to_cart('PTCartList');
                    } else {
                        if (is_virtual == 1) {  //虚拟产品
                            add_to_cart('Virtual');
                        } else {
                            add_to_cart('DirectBuy');
                        }
                    }
                }else {
                    add_to_cart('CartList');
                }
                show_footer_panel();
                global_obj.cancel_overlay();
            }else if(flag == 1){			
				$("#buy-type").html($(this).attr("id"));
                var a = $(window).height();				
				var b = $("#option_content").height();
				var d = $(".pro_top").height();
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
				hide_footer_panel();
            }				
        });
		
		$("#close-btna").on('click',function(){
			$('#option_content').hide();
			$('.mask').hide();
			$('.fotter').show();
			$('.foot').show();
			show_footer_panel();

		});
		
		$("#selector_confirm_btn").on('click',function(){
            var qty_count = $('#qtycount').val();
            var msg = '';
            var status = true;
			if(typeof(lengglo) == "undefined"){				
                msg = '请选择商品属性';
                status = false;
            }else{
				if(lengglo != lengogglo){
                msg = '请选择商品属性';
                status = false;
            }
			}
            if(msg.length > 0){
                new Toast({
                        context: $("body"),
                        message: msg,
                        top: $("#selector_confirm_btn").offset().top - 70,
                        time: 4000
                    }).show();
                return status;
            }			
			if ($("#buy-type").html() == "menu-direct-btn") {
                if (order_type == 'pintuan') {    //拼团购买
                    add_to_cart('PTCartList');
                } else {
                    if (is_virtual == 1) {
                        add_to_cart('Virtual');
                    } else {
                        add_to_cart('DirectBuy');
                    }
                }
			}else {				
				$('#option_content').hide();
				$('.mask').hide();
				add_to_cart('CartList');
			}
			show_footer_panel();
		});

        $('footer').on('click', '#close-btn',
        function() {
			/*edit20160524-start-*/ 
				$("#footer").css("position","");
				$("#footer").css("top","");
            /*edit20160524-end-*/ 
            $("#option_content").html($("footer").html());
            $("footer").css("height", "45px");
            show_footer_panel();
            global_obj.cancel_overlay();
            $(".option-val-list").html();

        });
		
		
		//确定按钮
        $('footer').on('click', '#selector_confirm_btn',
        function() {
            var spec_list = $('#spec_list').val();
            var qty_count = $('#qtycount').val();
            var msg = '';
            var status = true;
            if(spec_list.indexOf(',') >= 0 || spec_list.length <= 0){
                msg = '请选择商品属性';
                status = false;
            }
            if(spec_list.length > 0 && spec_list.indexOf(',') < 0){
               var result =(new Function("","return "+products_attr_json))(); 
                //products_attr_json = eval('('+products_attr_json+')');
                if(result[spec_list]['count'] <= 0 ){
                    msg = '产品库存不足';
                    status = false;
                }
                if(Number(qty_count) > result[spec_list]['count'] && result[spec_list]['count'] > 0){
                    msg = '产品库存不足,最多能购买'+result[spec_list]['count']+'件';
                    status = false;
                }
            }
            if(msg.length > 0){
                new Toast({
                        context: $("body"),
                        message: msg,
                        top: $("#selector_confirm_btn").offset().top - 70,
                        time: 4000
                    }).show();
                return status;
            }
            
			/*edit20160524-start-*/ 
				$("#footer").css("position","");
				$("#footer").css("top","");
			/*edit20160524-end-*/ 
            $("#option_content").html($("footer").html());
            $("footer").css("height", "45px");
			if ($("#buy-type").html() == "menu-direct-btn") {
				if (is_virtual == 1) {
					add_to_cart('Virtual');    
                } else {
					add_to_cart('DirectBuy');
                }
			}else {
				add_to_cart('CartList');
			}
            show_footer_panel();
            global_obj.cancel_overlay();

        });
		
        /*产品qty改变*/
        $("#option_selecter").on("click", "#qty_selector a[name=minus]",
        function() {
			var msg = '';			
            var status = true;
			if(typeof(lengglo) == "undefined"){				
                msg = '请选择商品属性';
                status = false;
            }else{
				if(lengglo != lengogglo){
                msg = '请选择商品属性';
                status = false;
            }
			}
            if(msg.length > 0){
                new Toast({
                        context: $("body"),
                        message: msg,
                        top: $("#selector_confirm_btn").offset().top - 70,
                        time: 4000
                    }).show();
                return status;
            }
            var qty = parseInt($('#qty_selector input[name=Qty]').val()) - 1;
            var discount = $(".level_pro").attr("proportion");
            if (qty < 1) {
                qty = 1;
            }			
            $('#price').html(parseFloat(okprice * qty).toFixed(2));
            $('#qty_selector input[name=Qty]').attr("value", qty);			
        });

        $("#option_selecter").on("click", "#qty_selector a[name=add]",
        function() {
			var msg = '';			
            var status = true;
			if(typeof(lengglo) == "undefined"){				
                msg = '请选择商品属性';
                status = false;
            }else{
				if(lengglo != lengogglo){
                msg = '请选择商品属性';
                status = false;
            }
			}
            if(msg.length > 0){
                new Toast({
                        context: $("body"),
                        message: msg,
                        top: $("#selector_confirm_btn").offset().top - 70,
                        time: 4000
                    }).show();
                return status;
            }
            var qty = parseInt($('#qty_selector input[name=Qty]').val()) + 1;
            var discount = $(".level_pro").attr("proportion");
            if (qty > okcount) {
                qty = okcount;
            }            
            $('#price').html(parseFloat(okprice * qty));
            $('#qty_selector input[name=Qty]').attr("value", qty);
        });

        $("#option_selecter").on("change", "#qty_selector input[name=Qty]",
        function() {
			var msg = '';			
            var status = true;
			if(typeof(lengglo) == "undefined"){				
                msg = '请选择商品属性';
                status = false;
            }else{
				if(lengglo != lengogglo){
                msg = '请选择商品属性';
                status = false;
            }
			}
            if(msg.length > 0){
                new Toast({
                        context: $("body"),
                        message: msg,
                        top: $("#selector_confirm_btn").offset().top - 70,
                        time: 4000
                    }).show();
					$('#qty_selector input[name=Qty]').attr("value", 1);
                return status;
            }
            var qty = parseInt($('#qty_selector input[name=Qty]').val());
            var discount = $(".level_pro").attr("proportion");
            if (qty < 1) {
                qty = 1;
            }
			if (qty > okcount) {
                qty = okcount;
            }
			if(isNaN(qty)){
				 qty = 1;
			}
            $('#price').html(parseFloat(okprice * qty));
            $('#qty_selector input[name=Qty]').attr("value", qty);
        });
		
        //加入购物车&&直接购买&&虚拟物品直接购买
        function add_to_cart(cart_key) {
			var attrvstr = '';
			var attridstr = '';
				$('.pro_attribute').each(function () {
				attrv = $.trim($(this).first().text());
				sku_x = $(this).find("input.sku_x");
				$.each(sku_x,function (){
				attrvstr += attrv + ':' + sku_x.val() + ' ';
				attridstr += sku_x.attr('attr_id') + ';';
				});
				});
			var reg = /\d+/g;
			var price = $('#price').text();
			var count = $('#count').text();
			var qtysum = $('#qtycount').val();
			var pricesimp = price/qtysum;
			var count = $('#count').text();
			var countarr = count.match(reg);
			var showimg = $(".imgchege img").attr("src")
            $.post($('#addtocart_form').attr('action') + 'ajax/', $('#addtocart_form').serialize()+'&cart_key='+cart_key+'&atr_str='+attrvstr+'&atrid_str='+attridstr+'&price='+price+'&pricesimp='+pricesimp+'&showimg='+showimg+'&count='+countarr+'&flashsale=' + (typeof has_active != 'undefined' && has_active == 1 ? 1 : 0),
            function(data) {
                if (data.status == 1) {
					if(cart_key == 'CartList'){
						//购物车特效
						$('.icon-container span i').show();
						$('.icon-container span i').html(data.qty);

						new Toast({
							context: $('body'),
							message: '产品已成功加入购物车',
							top: $("#footer").offset().top - 70
						}).show();
					}else if(cart_key == 'Virtual'){   //虚拟产品
						window.location.href = "/api/" + UsersID + "/shop/cart/checkout_virtual/?love";
					}else if(cart_key == 'DirectBuy'){   //实物产品
						window.location.href = "/api/" + UsersID + "/shop/cart/checkout_direct/?love";
					} else if (order_type == 'pintuan' && cart_key == 'PTCartList') {     //拼团购买
                        if (is_virtual == 1) {
                            window.location.href = "/api/" + UsersID + "/shop/cart/checkout_virtual/?cart_key=PTCartList";
                        } else {
                            window.location.href = "/api/" + UsersID + "/shop/cart/checkout_direct/?cart_key=PTCartList";
                        }
                    }
                } else if(data.status == 118) {
                    window.location.href = data.url;
                } else {
                    new Toast({
                        context: $("body"),
                        message: data.msg,
                        top: $("#footer").offset().top - 70,
                        time: 4000
                    }).show();
                }
            },
            'json');
        }

        /*收藏产品*/
        $("footer").on('click', '#favorite,#favorited',
        function() {

            var productId = parseInt($(this).attr('productid'));
            var action = 'favourite';
            var isFavourite = parseInt($(this).attr('isFavourite'));
            var id = $(this).attr("id");

            if (isFavourite == parseInt(0)) {
                $.post($('#addtocart_form').attr('action') + 'ajax/', {
                    productId: productId,
                    action: action
                },
                function(data) {
                    if (data.status == 0) {
                        self.location.href = data.url
                    } else if (data.status == 1) {

                        $("#footer #" + id).attr("isFavourite", 1);
                        $("#footer #" + id).attr('id', "favorited");
                    }
                },
                'json');

            } else {

                $.post($('#addtocart_form').attr('action') + 'ajax/', {
                    productId: productId,
                    action: 'cancel_favourite'
                },

                function(data) {
                    if (data.status == 0) {
                        self.location.href = data.url
                    } else if (data.status == 1) {
                        $("#footer #" + id).attr("isFavourite", 0);
                        $("#footer #" + id).attr('id', "favorite");
                    }
                },
                'json');

            }

        });
		
		/*收藏产品top*/
        $("#favoritetop").on('click', '#favorite,#favorited',
        function() {

            var productId = parseInt($(this).attr('productid'));
            var action = 'favourite';
            var isFavourite = parseInt($(this).attr('isFavourite'));
            var id = $(this).attr("id");

            if (isFavourite == parseInt(0)) {
                $.post($('#addtocart_form').attr('action') + 'ajax/', {
                    productId: productId,
                    action: action
                },
                function(data) {
                    if (data.status == 0) {
                        self.location.href = data.url
                    } else if (data.status == 1) {

                        $("#favoritetop #" + id).attr("isFavourite", 1);
                        $("#favoritetop #" + id).attr('id', "favorited");
                    }
                },
                'json');

            } else {

                $.post($('#addtocart_form').attr('action') + 'ajax/', {
                    productId: productId,
                    action: 'cancel_favourite'
                },

                function(data) {
                    if (data.status == 0) {
                        self.location.href = data.url
                    } else if (data.status == 1) {
                        $("#favoritetop #" + id).attr("isFavourite", 0);
                        $("#favoritetop #" + id).attr('id', "favorite");
                    }
                },
                'json');

            }

        });

        /*分销产品*/
        $("#share_product").click(function() {
            var url = "/api/" + UsersID + '/shop/cart/ajax/';
            var is_distribute = $(this).attr("is_distribute");
            var productid = $(this).attr("productid");
            $.post(url, {
                is_distribute: is_distribute,
                productid: productid,
                action: 'distribute_product'
            },
            function(data) {
                if (data.status == 0) {
                    self.location.href = data.url
                } else if (data.status == 1) {
                    //如果此用户为分销用户
                    if (data.Is_Distribute == 1) {
                        window.location.href = "/api/" + UsersID + '/distribute/distribute_goods/' + productid + '/';
                    } else {
                        window.location.href = "/api/" + UsersID + '/distribute/join/';
                    }
                }

            },
            'json');

        });

    },

    cart_init: function() {
        $('#cart .cart_title b').click(function() {
            history.go(-1);
        });

        //从购物车中删除产品
        $('#cart .bizcart .item .del div').click(function() {
            var BizID = $(this).attr("BizID");
            var ProductsID = $(this).attr("ProductsID");
            var CartID = $(this).attr("CartID");
            var obj = $(this);
            $.post(ajax_url, 'action=cart_del&BizID=' + BizID + '&ProductsID=' + ProductsID + '&CartID=' + CartID,
            function(data) {
                if (data.status == 1) { //删除本产品成功
                    $('#cart .cart_total span').html(data.total);
                    obj.parent().parent().parent().parent().remove();
                } else if (data.status == 2) { //该商家的产品都已删除
                    $('#cart .cart_total span').html(data.total);
                    obj.parent().parent().parent().parent().parent().remove();
                } else {
                    location.reload();
                }

            },
            'json');
        });

        $('#cart .bizcart .item .info .price span i').click(function() { //更新购物车            
			var obj = $(this);
            var qty = parseInt(obj.parent().children("input").attr("value"));
			var qtytal = parseInt(obj.parent().parent().prev().attr("contid"));
            var BizID = obj.parent().attr("BizID");
            var ProductsID = obj.parent().attr("ProductsID");
            var CartID = obj.parent().attr("CartID");
            var classname = obj.attr("class");
            if (classname == 'qty_sub') {
                if (qty <= 1) {
                    global_obj.win_alert('最小购买数量为1！');
                    obj.parent().children("input").val(1);
					qty = parseInt(obj.parent().children("input").attr("value"));
                }
            }
			if (classname == 'qty_add') {
                if (qty >= qtytal) {
                    global_obj.win_alert('最多购买' + qtytal + '件！');
                    obj.parent().children("input").val(qtytal);
					qty = parseInt(obj.parent().children("input").attr("value")) - 1;
                }
            }
            $.post(ajax_url, 'action=cart_update&BizID=' + BizID + '&ProductsID=' + ProductsID + '&CartID=' + CartID + '&Type=' + classname + '&Qty=' + qty,
            function(data) {
                if (data.status == 2) { //更新购物车成功                    
                    obj.parent().children("input").attr("value", data.qty);
					$('#cart .cart_total span').html(data.total);
                } else if (data.status == 1) {
                    global_obj.win_alert(data.msg,
                    function() {
                        obj.parent().children("input").attr("value", data.qty);
						$('#cart .cart_total span').html(data.total);
                    });
                } else {
                    global_obj.win_alert(data.msg);
                    obj.parent().parent().parent().parent().remove();
                }
            },
            'json');
        });

        $('#cart .bizcart .item .info .price span input').keyup(function() {
            var obj = $(this);
            var qty = parseInt($(this).attr("value"));
			var qtytal = parseInt(obj.parent().parent().prev().attr("contid"));
            var BizID = $(this).parent().attr("BizID");
            var ProductsID = $(this).parent().attr("ProductsID");
            var CartID = $(this).parent().attr("CartID");			
                if (qty <= 0) {
                    global_obj.win_alert('最小购买数量为1！');
                    obj.val(1);
					qty = parseInt($(this).attr("value"));
                }			
                if (qty > qtytal) {
                    global_obj.win_alert('最多购买' + qtytal + '件！');
					obj.val(qtytal);
					qty = parseInt($(this).attr("value"));
                }
            $.post(ajax_url, 'action=cart_update&BizID=' + BizID + '&ProductsID=' + ProductsID + '&CartID=' + CartID + '&Type=change&Qty=' + qty,
            function(data) {
                if (data.status == 2) { //更新购物车成功                    
                    obj.parent().children("input").attr("value", data.qty);
					$('#cart .cart_total span').html(data.total);
                } else if (data.status == 1) {
                    global_obj.win_alert(data.msg,
                    function() {
                        obj.attr("value", data.qty);
						$('#cart .cart_total span').html(data.total);
                    });
                } else {
                    global_obj.win_alert(data.msg);
                    obj.parent().parent().parent().parent().remove();
                }
            },
            'json');
        });

        $('#cart .cart_total a.gotocheck').click(function() {
            $.post(ajax_url, 'action=cart_check',
            function(data) {
                if (data.status == 1) { //更新购物车成功                    
                    window.location.href = ajax_url.replace('/ajax/', '/checkout/');
                } else {
                    global_obj.win_alert(data.msg);
                }
            },
            'json');
        });

        $('#cart_form .checkout input').click(function() {
            $(this).attr('disabled', true);
            $('#cart_form input[name=action]').val('check');
            $.post($('#cart_form').attr('action') + 'ajax/', $('#cart_form').serialize(),
            function(data) {
                $('#cart_form .checkout input').attr('disabled', false);
                if (data.status == 1) {

                    window.location = $('#cart_form').attr('action') + 'checkout/1/';
                } else {
                    window.location = $('#cart_form').attr('action');
                }
            },
            'json');
        });
    },

    checkout_init: function() {
        var address_display = function() {
            var AddressID = parseInt($('#checkout_form input[name=AddressID]:checked').val());
            if (AddressID == 0 || isNaN(AddressID)) {
                $('#checkout .address dl').css('display', 'block');
            } else {
                $('#checkout .address dl').css('display', 'none');
            }
        }

        var total_price_display = function() {

            var shipping_base_price = parseFloat($('#checkout_form input[name=Shipping\\[Express\\]]:checked').attr('Base_Price'));
            var shipping_continue_price = parseFloat($('#checkout_form input[name=Shipping\\[Express\\]]:checked').attr('Continue_Price'));

            var shipping_weight = parseInt($("#shipping_weight").html()) / 500;

            var shipping_price = 0;

            if (shipping_weight > 0) {
                if (shipping_weight >= 1) {
                    var shipping_price = shipping_base_price + shipping_continue_price * (shipping_weight - 1);
                }

                if (shipping_weight < 1) {
                    var shipping_price = shipping_base_price;
                }
            } else {
                var shipping_price = 0;
            }

            $("#shipping_price").html(shipping_price.toFixed(2));

            isNaN(shipping_price) && (shipping_price = 0);
            var total_price = parseFloat($('#checkout_form input[name=total_price]').val());
            var coupon_price = parseFloat($('#checkout_form input[name=CouponID]:checked').attr('Price'));

            if (isNaN(coupon_price) == false && coupon_price > 0) {
                var usetype = parseInt($('#checkout_form input[name=CouponID]:checked').attr('UseType'));
                if (usetype == 1) {
                    total_price = total_price - coupon_price
                } else {
                    total_price = total_price * coupon_price
                }
            }

            var total_amount = parseFloat(total_price) + shipping_price;
            $('#checkout_form .total_price span').html('￥' + total_amount.toFixed(2));

        }

        $('#checkout_form input[name=AddressID]').click(address_display);
        $('#checkout_form input[name=Shipping\\[Express\\]]').click(total_price_display);
        $('#checkout_form input[name=CouponID]').click(total_price_display);
        address_display();
        total_price_display();

        $('#checkout_form').submit(function() {
            return false;
        });
        $('#checkout_form .checkout input').click(function() {
            var AddressID = parseInt($('#checkout_form input[name=AddressID]:checked').val());
            if (AddressID == 0 || isNaN(AddressID)) {
                if (global_obj.check_form($('*[notnull]'))) {
                    return false
                };
            }

            $(this).attr('disabled', true);

            $.post($('#checkout_form').attr('action') + 'ajax/', $('#checkout_form').serialize() + '&Shipping\[Price\]=' + parseFloat($("#shipping_price").html()),
            function(data) {
                if (data.status == 1) {
                    window.location.href = data.url;
                }
            },
            'json');
        });

    },
    select_payment_init: function() {

        /*同步数据库*/
        function diyong_sync(money, total_price) {
            var Order_ID = $("input[name='OrderID']").val();
            var Integral_Consumption = $("#Integral_Consumption").attr("value");
            var form_url = $("#payment_form").attr("action");
            var param = {
                'action': 'diyong',
                'Order_ID': Order_ID,
                'Integral_Consumption': Integral_Consumption,
                'Integral_Money': money
            };

            $.post(form_url + 'ajax/', param,
            function(data) {
                if (data.status == 1) {
                    $("#Order_TotalPrice").html("&yen;" + total_price);
                    $("#btn-diyong").addClass("disabled");
                }
            },
            'json');
        }

        /*抵用操作*/
        $("#btn-diyong").click(function() {
            var diyong_rate = parseInt($("#diyong_rate").html());
            var user_integral = parseInt($("#user-integral").html());
            var can_diyong = parseInt($("#can-diyong").html());
            var orderMoney = parseFloat($("#Order_TotalPrice").attr("ordermoney"));
            if (user_integral >= can_diyong) {

                var money = can_diyong / diyong_rate;
                var total_price = parseFloat($("input[name='total_price']").val());
                if(money> total_price - 1){
                    $("#btn-diyong").addClass("disabled");
                    var prompt = layer.open({
                        content: "积分最多能抵用" + (total_price - 1) +"元，是否继续？",
                        btn: ['是', '否'],
                        yes: function(){
                            layer.close(prompt);
                            $("input[name='total_price']").attr("value", total_price - 1);
                            $(this).after("<span>&nbsp;&nbsp;&nbsp;&nbsp;抵用了" + (total_price - 1) + "元</span>");
                            $("#Integral_Consumption").attr("value", can_diyong);
                            $("#User_Integral").attr("value", user_integral - can_diyong);
                            $("#user-integral").html(user_integral - can_diyong);
                            diyong_sync(total_price -1 , "1.00");
                        }
                    });
                }else{
                    layer.close(prompt);
                    $("input[name='total_price']").attr("value", total_price - money);
                    $(this).after("<span>&nbsp;&nbsp;&nbsp;&nbsp;抵用了" + money + "元</span>");
                    $("#Integral_Consumption").attr("value", can_diyong);
                    $("#User_Integral").attr("value", user_integral - can_diyong);
                    $("#user-integral").html(user_integral - can_diyong);
                    diyong_sync(money,  (total_price - money));
                }

            } else {
                //如果用户积分小于可抵用积分，只能使用一部分积分
                var money = user_integral / diyong_rate;
                var total_price = parseFloat($("input[name='total_price']").val());

                $("input[name='total_price']").attr("value", total_price - money);
                $(this).after("<span>&nbsp;&nbsp;&nbsp;&nbsp;抵用了" + money + "元</span>");

                $("#Integral_Consumption").attr("value", user_integral);
                $("#User_Integral").attr("value", 0);
                $("#user-integral").html(0);
                diyong_sync(money, total_price - money);
            }

        });
    },
    payment_init: function() {
        // var PaymentMethod = $('#payment_form input[name=PaymentMethod]');
        $(":radio").click(function(){
			var PaymentMethod = $("input[name='Payselect']:checked").attr("data-value");
			var PaymentID = $("input[name='Payselect']:checked").attr("id");
			$("#PaymentMethod_val").attr("value", PaymentMethod);
			$("#PaymentID_val").attr("value", PaymentID);
		});
	
        $("#paygogo").click(function() {            
            var PaymentID = $("#PaymentID_val").attr("id");            
            shop_obj.submit_payment(this);
        });

        /*余额支付确认支付,与线下支付确认*/
        $("#btn-confirm").click(function() {
            shop_obj.submit_payment();
        });

        /*
	    if (PaymentMethod.size()) {
            var change_payment_method = function() {
                if (PaymentMethod.filter(':checked').val() == '线下支付') {
                    $('#payment_form .payment_info').show();
                    $('#payment_form .payment_password').hide();
                } else {
                    if (PaymentMethod.filter(':checked').val() == '余额支付') {
                        $('#payment_form .payment_password').show();
                        $('#payment_form .payment_info').hide();
                    } else {
                        $('#payment_form .payment_info').hide();
                        $('#payment_form .payment_password').hide();
                    }
                }
            }
            PaymentMethod.click(change_payment_method);
            PaymentMethod.filter('[value=' + $('#payment_form input[name=DefautlPaymentMethod]').val() + ']').click();
            change_payment_method();
        } else {
            $('#payment_form').hide();
        }
		*/

        $('#payment_form').submit(function() {
            return false;
        });

    },
    submit_payment: function() {

        if (global_obj.check_form($('*[notnull]'))) {
			return false
		};
        $('#btn-confirm').attr('disabled', true);
		$.ajax({
			type:'post',
			url:$('#payment_form').attr('action') + 'ajax/',
			data:$('#payment_form').serialize(),
			beforeSend:function() {
				$("body").append("<div id='load'><div class='bounce1'></div><div class='bounce2'></div><div class='bounce3'></div></div>");
			},
			success:function(data) {
				$('#payment_form .payment input').attr('disabled', false);
				if (data.status == 1) {
					if (typeof(data.msg) == 'undefined') {
						window.location = data.url;
					} else {
						global_obj.win_alert(data.msg,
                    function() {
                        window.location = data.url;
                    });
					}
				} else if (data.status == 2) {
					window.location = data.url;
				} else {
					$('#btn-confirm').attr('disabled', false);
					global_obj.win_alert(data.msg);
				}
			},
			complete:function() {
				$("#load").remove();
			},
			dataType:'json',
		});
    },
    user_address_init: function() {
        $('#address_form .back').click(function() {
            window.location = './';
        });

        $('#address_form').submit(function() {
            return false;
        });
        $('#address_form .submit').click(function() {
            if (global_obj.check_form($('*[notnull]'))) {
                return false
            };

            $(this).attr('disabled', true);
            $.post($('#address_form').attr('action') + 'ajax/', $('#address_form').serialize(),
            function(data) {
                if (data.status == 1) {
                    window.location = $('#address_form').attr('action') + 'address/';
                }
            },
            'json');
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
                return false;
            }

            $(this).attr('disabled', true);
            $.post($('#commit_form').attr('action') + 'ajax/', $('#commit_form').serialize(),
            function(data) {
                if (data.status == 1) {
                    global_obj.win_alert('评论成功!',
                    function() {
						var url = '';
						if(source=='pintuan'){
							url = "/api/"+UsersID+"/pintuan/orderlist/4/";
						}else{
							url = "/api/"+UsersID+"/shop/member/status/4/";
	
						}
                        window.location = typeof data.url != 'undefined' && data.url != '' ? data.url : url;
                    });
                } else {
                    global_obj.win_alert(data.msg);
                }
            },
            'json');
        });
    },
    
	backup_init: function() {
        $('#apply_form').submit(function() {
            return false;
        });
        $('#apply_form .submit').click(function() {
            if (global_obj.check_form($('*[notnull]'))) {
                return false
            };

            $(this).attr('disabled', true);
            $.post($('#apply_form').attr('action') + 'ajax/', $('#apply_form').serialize(), function(data) {
                if (data.status == 1) {
                    global_obj.win_alert('提交成功!', function() {
                        window.location = data.url;
                    });
                } else {
                    global_obj.win_alert(data.msg);
                }
            }, 'json');
        });
    },
	
	backup_send: function() {
        $('#send_form').submit(function() {
            return false;
        });
        $('#send_form .submit').click(function() {
            if (global_obj.check_form($('*[notnull]'))) {
                return false
            };

            $(this).attr('disabled', true);
            $.post($('#send_form').attr('action') + 'ajax/', $('#send_form').serialize(), function(data) {
                if (data.status == 1) {
                    global_obj.win_alert('提交成功!', function() {
                        window.location = data.url;
                    });
                } else {
                    global_obj.win_alert(data.msg);
                }
            }, 'json');
        });
    },

    distribute_init: function() {

        $('#bank_card').inputFormat('account');

        $("#edit-bankcard-btn").click(function() {

            if (global_obj.check_form($('input["name=bank_card"][notnull]'))) {
                return false
            };

            var url = $("#account_form").attr("action");
            var param = $("#account_form").serialize();

            $.post(url, param,
            function(data) {
                if (data.status == 1) {
                    global_obj.win_alert('银行卡账号修改成功',
                    function() {
                        window.location.href = base_url + 'api/' + UsersID + '/distribute/';
                    });
                }
            },
            'json');

        });

        $("a.remove-msg").click(function() {

            $(this).parent().parent().remove();

            $.post(url, param,
            function(data) {
                if (data.status == 1) {
                    global_obj.win_alert('提现申请提交成功',
                    function() {
                        window.location.reload();
                        //window.location.href = base_url+'api/'+UsersID+'/shop/distribute/';
                    });
                }
            },
            'json');
        });

        /*提现申请*/
        $("#btn-withdraw").click(function() {

            if (global_obj.check_form($('input["name=money"][notnull]'))) {
                return false
            };
            var balance = parseInt($("#balance").val());
            var money = parseInt($("input[name='money']").val());

            if (money <= 0) {
                $(this).after("<label id=\"withdraw_tip\" class=\"fc_red\">必须大于0</label>");
                return false;
            }
            if (balance < money) {
                $("#withdraw-money").css({
                    border: '1px solid red'
                });
                $(this).after("<label id=\"withdraw_tip\" class=\"fc_red\">余额不足</label>");
                return false;
            }

            var url = $("#withdraw-form").attr("action");
            var param = $("#withdraw-form").serialize();

            $.post(url, param,
            function(data) {
                if (data.status == 1) {
                    global_obj.win_alert('提现申请提交成功',
                    function() {
                        window.location.reload();
                        //window.location.href = base_url+'api/'+UsersID+'/shop/distribute/';
                    });
                }
            },
            'json');

        })

        $("#withdraw-money").keydown(function() {
            $("#withdraw_tip").remove();
            $("#withdraw-money").css({
                border: '0px solid red'
            });
        });

        //展开提现记录
        $("a.record-title").click(function() {
            var status = $(this).attr("status");
            if (status == 'close') {
                $(this).find("span.icon").removeClass("icon-chevron-up").addClass("icon-chevron-down");
                $(this).parent().next().css({
                    display: 'block'
                });
                $(this).attr("status", "open");
            } else {
                $(this).find("span.icon").removeClass("icon-chevron-down").addClass("icon-chevron-up");
                $(this).parent().next().css({
                    display: 'none'
                });
                $(this).attr("status", "close");
            }

        });

    },
    join_distribute_init: function() {

        $('#bank_card').inputFormat('account');

        /*表单验证*/
        var remote = base_url + '/api/distribute/ajax.php?UsersID=' + UsersID;
        real_name_remote = remote + '&action=check_exist&field=Real_Name';

        var id_card_remote = remote + "&action=check_exist&field=ID_Card";
        var email_remote = remote + "&action=check_exist&field=Email";

        $("#join-distribute-form").validate({
            rules: {
                real_name: {
                    required: true,
                    remote: real_name_remote
                },
                email: {
                    required: true,
                    email: true,
                    remote: email_remote

                },
                idcard: {
                    required: true,
                    number: true,
                    remote: id_card_remote
                },
                alipay_account: 'required',
                Bank_Card: {
                    required: true
                },
                Bank_Name: {
                    required: true
                }
            },

            messages: {
                real_name: {
                    remote: '此姓名已被占用'
                },
                idcard: {
                    remote: '此身份证号已被占用'
                },
                email: {
                    remote: '此邮箱已被占用'
                }
            },

            onfocusout: function(element) {
                this.element(element);
            }
        });

        /*加入营销会员申请*/
        $("#join-distribute-btn").click(function() {

            var url = $("#join-distribute-form").attr("action");
            var param = $("#join-distribute-form").serialize();
            if ($("#join-distribute-form").valid()) {

                $.post(url, param,
                function(data) {
                    if (data.status == 1) {
                        global_obj.win_alert('您已经成为分销用户',
                        function() {
                            window.location.href = base_url + 'api/' + UsersID + '/distribute/';
                        });
                    }
                },
                'json');

            } else {
                return false;
            }

        });

        /*弹出规则框*/
        $("#see_regulation").click(function() {
            var o = $(this);
            global_obj.div_mask();
            $('#seee_regulation_div').show();
            $('#cancel_view').off().click(function() {
                global_obj.div_mask(1);
                $('#seee_regulation_div').hide();
            });

        });
    },
	
	//虚拟商品详情页js
    products_buy_init: function() {

        var window_height = $(document).height();
        var window_half_height = $(window).height() / 2;

        function show_footer_panel() {

            if ($('#footer').html().length > 0) {

                $('#option_content').html($("#footer").html());
            }

            var footer_panel_content = $("#footer_panel_content").html();
            $('footer').html(footer_panel_content);

        }

        var handler = function(e) {

            if ($(e.targent).attr('class') != 'property') {
                e.preventDefault();
            }

        }

        show_footer_panel();

        $('footer, .footer_pt').on('click', '#menu-addtocard-btn,#menu-direct-btn"',
        function() {
            $("#buy-type").html($(this).attr("id"));
            $(window).scrollTop(0);
            var selector_content = $("#option_content").html();

            $("#option_content").html('');
            $("footer").html(selector_content);
            var footer_height = window_half_height + 20;
            $("footer").height(footer_height);

            $(".option-val-list").height(footer_height - 150);
            $(".option-val-list").css("overflow", "scroll");
            var overlay_height = $(window).height() - $("footer").height();
            global_obj.overlay(overlay_height);
            mauual_check(); //手动勾选已选属性

        });

        $('footer').on('click', '#close-btn',
        function() {

            $("#option_content").html($("footer").html());
            $("footer").css("height", "45px");
            show_footer_panel();
            global_obj.cancel_overlay();
            $(".option-val-list").html();

        });

        $('footer').on('click', '#selector_confirm_btn',
        function() {			
			$("#option_content").html($("footer").html());
            $("footer").css("height", "45px");
			if ($("#buy-type").html() == "menu-direct-btn") {
				if (is_virtual == 1) {
					add_to_cart('Virtual');    
                } else {
					add_to_cart('DirectBuy');
                }
			}else {
				add_to_cart('CartList');
			}
            show_footer_panel();
            global_obj.cancel_overlay();

        });

        /*产品qty改变*/
        $("footer").on("click", "#qty_selector a[name=minus]",
        function() {
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

        $("footer").on("click", "#qty_selector a[name=add]",
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

        $("footer").on("change", "#qty_selector input[name=Qty]",
        function() {

            var qty = parseInt($('#qty_selector input[name=Qty]').val());
            var total_stock = parseInt($('#stock_val').html());

            if (qty < 1) {
                qty = 1;
            }

            if (qty > total_stock) {
                qty = total_stock;
            }

            var cur_price = parseFloat($("#cur_price").val());
            var float_qty = parseFloat(qty + '.00').toFixed(2);
            $('#cur-price-txt').html(parseFloat(cur_price * float_qty).toFixed(2));

            $('#qty_selector input[name=Qty]').attr("value", qty);
        });

        /*确定属性选择*/

        function add_to_cart(cart_key) {
            $.post($('#addtocart_form').attr('action') + 'ajax/', $('#addtocart_form').serialize()+'&cart_key='+cart_key,
            function(data) {
                if (data.status == 1) {
					if(cart_key == 'CartList'){
						//购物车特效
						$('.icon-container span i').show();
						$('.icon-container span i').html(data.qty);

						new Toast({
							context: $('body'),
							message: '产品已成功加入购物车',
							top: $("#footer").offset().top - 70
						}).show();
					}else if(cart_key == 'Virtual'){
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

        /*收藏产品*/
        $("footer").on('click', '#favorite,#favorited',
        function() {

            var productId = parseInt($(this).attr('productid'));
            var action = 'favourite';
            var isFavourite = parseInt($(this).attr('isFavourite'));
            var id = $(this).attr("id");

            if (isFavourite == parseInt(0)) {
                $.post($('#addtocart_form').attr('action') + 'ajax/', {
                    productId: productId,
                    action: action
                },
                function(data) {
                    if (data.status == 0) {
                        self.location.href = data.url
                    } else if (data.status == 1) {

                        $("#footer #" + id).attr("isFavourite", 1);
                        $("#footer #" + id).attr('id', "favorited");
                    }
                },
                'json');

            } else {

                $.post($('#addtocart_form').attr('action') + 'ajax/', {
                    productId: productId,
                    action: 'cancel_favourite'
                },

                function(data) {
                    if (data.status == 0) {
                        self.location.href = data.url
                    } else if (data.status == 1) {
                        $("#footer #" + id).attr("isFavourite", 0);
                        $("#footer #" + id).attr('id', "favorite");
                    }
                },
                'json');

            }

        });

        /*分销产品*/
        $("#share_product").click(function() {
            var url = "/api/" + UsersID + '/shop/cart/ajax/';
            var is_distribute = $(this).attr("is_distribute");
            var productid = $(this).attr("productid");
            $.post(url, {
                is_distribute: is_distribute,
                productid: productid,
                action: 'distribute_product'
            },
            function(data) {
                if (data.status == 0) {
                    self.location.href = data.url
                } else if (data.status == 1) {
                    //如果此用户为分销用户
                    if (data.Is_Distribute == 1) {
                        window.location.href = "/api/" + UsersID + '/distribute/distribute_goods/' + productid + '/';
                    } else {
                        window.location.href = "/api/" + UsersID + '/distribute/join/';
                    }
                }

            },
            'json');

        });

    },

    // 线下买单
	user_paymoney_init:function(){
        // 店内买单提交
		$('#user_form .submit input').click(function(){
            if(global_obj.check_form($('*[notnull]'))){return false;}

		    var price = $("input[name='Amount']").val();
            // 判断金额
            if (! /(^[1-9]([0-9]+)?(\.[0-9]{0,2})?$)|(^(0){1}$)|(^[0-9]\.([0-9]){0,2}?$)/.test(price)) {
                global_obj.win_alert('请输入正确的金额');
                return false;
            }
            console.log($('#user_form').serialize());

			$(this).attr('disabled', true).val('提交中...');
			$.post("/api/"+UsersID+'/shop/cart/ajax/', $('#user_form').serialize(), function(data){
				if(data.status==1){
					if(data.msg){
						global_obj.win_alert(data.msg, function(){
							if(data.url){
								location.href = data.url;
							}else{
								history.go(-1);
							}
						});
					}else{
						if(data.url){
							location.href = data.url;
						}else{
							history.go(-1);
						}
					}
						
				}else{
                    $(this).attr('disabled', false).val('提交');
					global_obj.win_alert(data.msg, function(){
						$('#user_form .submit input').attr('disabled', false);
					});
				}
			}, 'json');
		});

		// 金额输入判断
		$("input[name='Amount']").blur(function(){
		    var Is_store = $("input[name='Is_store']").val();  // 判断是否是店内买单
			price = $(this).val();

			// 判断金额
            if (! /(^[1-9]([0-9]+)?(\.[0-9]{0,2})?$)|(^(0){1}$)|(^[0-9]\.([0-9]){0,2}?$)/.test(price)) {
                global_obj.win_alert('请输入正确的金额');
                return false;
            }
            $('.submit_btn').attr('disabled', true);

            // 写入可使用的优惠券（以供选择）
			$.post('?','action=coupon_go&total_price=' + price,function(data){
                if(data.status == 1){
                    var couponhtml = '<ul class="list-group">';
                    $.each(data.coupon_info,function(k,v){console.debug(v);
                        couponhtml += '<li class="list-group-item"><label><input type="radio" class="coupon" name="CouponID" value="'+v.Coupon_ID+'" UseType="'+v.Coupon_UseType+'" Pricezk="'+v.Coupon_Discount+'" Pricecen="'+v.Coupon_Cash+'" />'+v.Subject+'</label></li>';
                    });
                    couponhtml += '</ul>';
                    $('#coupon-list').html(couponhtml);
                    $('.panel-footer').show();
                }else{
                    /*if(global_obj.check_form($('*[notnull]'))){return false;}
                    $(this).attr('disabled', true);
                    $.post("/api/"+UsersID+'/shop/cart/ajax/', $('#user_form').serialize(), function(data){
                        if(data.status==1){
                            if(data.msg){
                                global_obj.win_alert(data.msg, function(){
                                    if(data.url){
                                        data.url += '?Is_store=' + Is_store;
                                        location.href = data.url;
                                    }else{
                                        history.go(-1);
                                    }
                                });
                            }else{
                                if(data.url){
                                    location.href = data.url;
                                }else{
                                    history.go(-1);
                                }
                            }

                        }else{
                            global_obj.win_alert(data.msg, function(){
                                $('#user_form .submit input').attr('disabled', false);
                            });
                        }
                    }, 'json');*/
                }
                $('.submit_btn').attr('disabled', false);
            }, 'json');
        });

		// 选择优惠券
        $("#coupon-list").on('click',"input[name='CouponID']",function(){
            var pricezk = $(this).attr('Pricezk');
            var Pricecen = $(this).attr('Pricecen');
            var UseType = $(this).attr('UseType');
            if (UseType == 0) {
                var priceok = Math.round(price * pricezk * 100) / 100;
            }else{
                var priceok = price - Pricecen;
            }
            $("input[name='Amount']").val(priceok);
            $("input[name='Amount_Y']").val(price);
        });
	},
	
	youhuijuan_init: function() {		
		var e = $(window).height();
        var window_height = $(document).height();
        var window_half_height = $(window).height() / 2;
        var handler = function(e) {
            if ($(e.targent).attr('class') != 'property') {
                e.preventDefault();
            }
        }
		//优惠劵
        $('.dianpu_contact').on('click', '#menu-addtocard-btn,#menu-direct-btn"',
        function() {
            var flag = 1;
            if(flag == 0){
                var selector_content = $("#option_content").html();
                $("#option_content").html('');
                $("footer").html(selector_content);
                if($(this).attr("id") == "menu-direct-btn"){

                    if (is_virtual == 1) {
                        add_to_cart('Virtual');    
                    } else {
                        
                        add_to_cart('DirectBuy');
                    }
                }else {
                    add_to_cart('CartList');
                }
                show_footer_panel();
                global_obj.cancel_overlay();
            }else if(flag == 1){				
				$("#buy-type").html($(this).attr("id"));
                var a = $(window).height();				
				var b = $("#option_content").height();
				var d = $(".pro_top").height();
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
            }			
        });
		
		$("#close-btna").on('click',function(){
			$('#option_content').hide();
			$('.mask').hide();
			$('.fotter').show();
			$('.foot').show();

		});
		
		$("#selector_confirm_btn").on('click',function(){        
			$('#option_content').hide();
			$('.mask').hide();
		});
		
		$('#coupon .get').on('click',function(){
			var o=$(this);
			o.html('领取中...');
			$.post(biz_ajax_url, 'action=get_coupon&CouponID='+o.attr('CouponID'), function(data){
				o.html('领取');
				if(data.status==1){
					alert("领取成功");
					window.location.reload();
				}else{
					alert("领取失败");
					window.location.reload();
				};
			}, 'json');
			$('#option_content').hide();
			$('.mask').hide();
		});
		
    }
}