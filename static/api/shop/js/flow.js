// 商品购买流程js操作

//新版的提交订单页面 使用
var new_checkout = typeof new_checkout != 'undefined' && new_checkout == 1 ? 1 : 0;

var flow_obj = {
    checkout_init: function() {
        //提交订单页面js操作 
		
		$('.Invoice_btn').click(function(){//发票
			var rel = $(this).attr("rel");
			if(!$(this).attr("checked")){
				$("#Invoice_info_"+rel).hide();
			}else{
				$("#Invoice_info_"+rel).show();
			}
		});
		
		//减商品数量
        $('.qty_selector a[name=minus]').click(function() {
            var qty_input_obj = $(this).next();
            var qty = parseInt($(qty_input_obj).attr('value')) - 1;
            var cart_id = $(qty_input_obj).next().attr('id');
            if (qty < 1) {
                qty = 1;
				global_obj.win_alert('最小购买数量为1！');
            }

            flow_obj.update_checkout_qty(qty, cart_id);

        });
		
		//增加商品数量
        $('.qty_selector a[name=add]').click(function() {
            var qty_input_obj = $(this).prev().prev();
            var qty = parseInt($(qty_input_obj).attr('value')) + 1;
            var cart_id = $(qty_input_obj).next().attr('id');
            var Products_ID = cart_id.split('_')[1];
            var products_Count = parseInt($("#Products_Count_" + Products_ID).attr('value'));

            if (qty > products_Count) {
                qty = products_Count;
				global_obj.win_alert('最大购买数量为'+products_Count);
            }
            flow_obj.update_checkout_qty(qty, cart_id);
        });
		
		//输入框里输入的商品数量
        //$('.qty_selector input').change(function() {
		$('.qty_selector input').keyup(function() {
            var qty_input_obj = $(this);
            var qty = parseInt($(qty_input_obj).attr('value'));
            var cart_id = $(qty_input_obj).next().attr('id');
            var Products_ID = cart_id.split('_')[1];           
            var products_Count = parseInt($("#Products_Count_" + Products_ID).attr('value'));
            if (qty < 1) {
                qty = 1;
                $(qty_input_obj).attr('value', 1);
				global_obj.win_alert('最小购买数量为1！');
            }
			if(isNaN(qty)){
				qty=0;	 
			}
            if (qty > products_Count) {
                qty = products_Count;
				global_obj.win_alert('最大购买数量为'+products_Count);
                $(qty_input_obj).attr('value', products_Count);                
            }

            flow_obj.update_checkout_qty(qty, cart_id);
        });
		
		$('.qty_selector input').blur(function() {
			var qty_input_obj = $(this);
			var qty = parseInt($(qty_input_obj).attr('value'));
			var cart_id = $(qty_input_obj).next().attr('id');
			if (qty < 1) {
                qty = 1;
                $(qty_input_obj).attr('value', 1);
				global_obj.win_alert('最小购买数量为1！');
            }
			flow_obj.update_checkout_qty(qty, cart_id);
        });

        $("#submit-btn").removeAttr('disabled');

        $('#checkout_form').submit(function() {
            return false;
        });

		$('#checkout_form #submit-btn').click(function() {
            var checkout= $('#checkout_form input[name=checkout]').val();
			if(checkout == 'checkout_virtual'){
				var AddressID = parseInt($('#checkout_form input[name=AddressID]').val());
				var Address_WeiXin = $('#Address_WeiXin').val();
				var Mobile = $("input[name=Mobile]").val();
				var xunipro = $("input[name=xunipro]").val();
			
				if (xunipro == 2) {
				var mobiletest = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(17[0-9]{1})|(18[0-9]{1})|(19[0-9]{1}))+\d{8})$/;
				if (!mobiletest.test(Mobile)) {
					msg = "手机号填写有误!";
					var sc=$(window).scrollTop();
					$('body,html').animate({scrollTop:0},500);
					new Toast({
						context: $("body"),
						message: msg,
						top: $(".add_pt").offset().top + 300,
						time: 4000
					}).show();
					return false;
				}
				}
				
				var msg = '';
				if ((AddressID == 0 || isNaN(AddressID)) && Address_WeiXin == "") {
					if (global_obj.check_form($('*[notnull]'))) {
						return false
					};
					var msg = '收货地址不能为空!';
					
				}
				if(msg != ''){
					new Toast({
						context: $("body"),
						message: msg,
						top: $("#footer").offset().top - 70,
						time: 4000
					}).show();
					return false;
				}
			} else {
				var AddressID = parseInt($('#checkout_form input[name=AddressID]').val());
				//var Address_WeiXin = $('#Address_WeiXin').val();
				var msg = '';
				if (AddressID == 0 || isNaN(AddressID)) {
					if (global_obj.check_form($('*[notnull]'))) {
						return false
					};
					var msg = '收货地址不能为空!';
				}
				
				if(msg != ''){
					new Toast({
						context: $("body"),
						message: msg,
						top: $("#footer").offset().top - 70,
						time: 4000
					}).show();
					return false;
				}
			}
            $(this).attr('disabled', true);

            var param = $('#checkout_form').serialize();
            var url = $('#checkout_form').attr('action') + 'ajax/';

			
            $.post(url, param, function(data) {
                if (data.status == 1) {
                    window.location.href = data.url;
                }else {
					if(typeof data.msg != 'undefined'){
						global_obj.win_alert(data.msg);
					}   
				}
            }, 'json');
        });
		
		$(".panel-footer").on('click','input.coupon',function(e){
			flow_obj.change_shipping_method();
		});
		
		$(".tc-main").on("click", ".chooseStore", function(e){
			flow_obj.change_shipping_method();
		});

		if (new_checkout) {
            $(".tc-main").on('click', '.Shiping_ID_Val', function(){
				let shippingID = $(this).val();
				
                flow_obj.change_shipping_method(shippingID);
            });
			
			
		} else {
            var shipping = 0;
            $(".shipping_method").click(function(){
                var BizID = $(this).attr("Biz_ID");
                var top = $(window).height()/2;
                $("#shipping-modal-"+BizID).css('top',top-80);
                $("#shipping-modal-"+BizID).modal('show');
                shipping = $(".Shiping_ID_Val").val();
            });

            $("#confirm_shipping_btn").live('click',function(){
                var Biz_ID = $(this).attr('biz_id');
                $("#shipping-modal-"+Biz_ID).modal('hide');
                flow_obj.change_shipping_method(Biz_ID,shipping);
            });
		}

		
		$("#cancel_shipping_btn").live('click',function(){
		    $(".shipping_modal_sun").modal('hide');
		});
		
        /**
         * json对象转字符串形式
         */
        function json2str(o) {
            var arr = [];
            var fmt = function(s) {
                if (typeof s == 'object' && s != null) return json2str(s);
                return /^(string|number)$/.test(typeof s) ? "'" + s + "'" : s;
            }
            for (var i in o) arr.push("'" + i + "':" + fmt(o[i]));
            return '{' + arr.join(',') + '}';
        }
    },
	
    update_checkout_qty: function(qty, cart_id) {
        var City_Code = $("#City_Code").attr('value');
		var Biz_ID = cart_id.split('_')[0];
        var Products_ID = cart_id.split('_')[1];
        var Shipping_ID = flow_obj.getShippingID();
		var total_shipping_fee = $("#total_shipping_fee").val();
        var Business = $("#Business_" + Products_ID).attr('value');
        var IsShippingFree = parseInt($("#IsShippingFree_" + Products_ID).attr('value'));
        var cart_key = $("#cart_key").attr('value');
        var param = {
            Shipping_ID:Shipping_ID,
            Business: Business,
            City_Code: City_Code,
            _Qty: qty,
			total_shipping_fee:total_shipping_fee,
            _CartID: cart_id,
            IsShippingFree: IsShippingFree,
			cart_key: cart_key,
            action: 'checkout_update'
        };

        var url = base_url + 'api/' + Users_ID + '/shop/cart/ajax/';
        var Cart_ID = cart_id.replace(/\;/g, "");
        $.post(url, param, function(data) {
            if (data.status == 1) {
                if (parseInt(data.biz_shipping_fee) == 0) {
                    $('#biz_shipping_fee_txt_' + Biz_ID).html('免运费');
                } else {
                    $('#biz_shipping_fee_txt_' + Biz_ID).html(data.biz_shipping_fee + '元');
                }
                $('#subtotal_price_' + Cart_ID).html('&yen' + data.Sub_Total);
                $('#Sub_Totalall_' + Biz_ID).val(data.Sub_Total_source);
                $('#subtotal_qty_' + Cart_ID).html(data.Sub_Qty);
                $('#' + Cart_ID).attr('value', data.Sub_Qty);
				$('#biz_shipping_'+Biz_ID).html(data.biz_shipping_name);
				$('#coupon-list-'+Biz_ID).html(data.youhuicontent);
				$('#coupon-list-'+Biz_ID).show();
				if (data.Products_Integration > 0) {
					$('#Integration-list-'+Biz_ID).show();
					$('#jin_diyong'+Biz_ID).html(data.Products_Integration);				
				} else {
					$('#Integration-list-'+Biz_ID).hide();
					$('#jin_diyong'+Biz_ID).html(0);
				}
				var coupon_price = parseFloat($('.coupon:checked').attr("price"));
				
				if($('.coupon:checked').attr("usetype") == 1){
					total_price = data.total - coupon_price;
				}else if($('.coupon:checked').attr("usetype") == 0 && coupon_price > 0){
					total_price = data.total*coupon_price;
				}else{
					total_price = data.total;
				}
				total_price = parseFloat(total_price);
				var total_shipping_fee = parseFloat(data.total_shipping_fee);
                //更新订单合计信息
                var total_price = total_price + total_shipping_fee;
				total_price = (Math.round(total_price*100)/100).toFixed(2);
                $("#total_price_txt").html('&yen' + total_price);
                $("#total_price").attr('value', total_price);
                $("#total_shipping_fee").attr('value', data.total_shipping_fee);
				$("#coupon_value").attr('value',0);				
                

                var integral = parseInt(data.integral);

                if (integral > 0) {
                    $("#total_integral").html(integral);
                }				
            }
        }, 'json');
    },
	change_shipping_method:function(){
		shippingID = arguments[0] || '';
		
		var cart_key = $("#cart_key").attr('value');
		var AddressID = $("input[name='AddressID']").val();
		var BizArr = BizIDStr.split(',');
		var couponID = '{';
		var offset = '{';
		var StoreID = '{';
		var shipping = '{';
		if(BizArr.length>0){
			for(let i=0;i<BizArr.length;i++){
				let BizID = BizArr[i];
				let couponvalue = $("input[name='CouponID["+BizID+"]']:checked").val();
				let shippingvalue = $("input[name='Shiping_ID_"+BizID+"']:checked").val();
				let offsetvalue = $("input[name='offset["+BizID+"]']").val();
				let Storevalue = $("input[name='Store["+BizID+"]']:checked").val();
				if(couponvalue === undefined){
					couponvalue = 0;
				}
				if(shippingvalue === undefined){
					shippingvalue = 0;
				}
				if(shippingvalue == 'dada'){
					shippingvalue = 'Is_dada';
				}
				if(Storevalue === undefined){
					Storevalue = 0;
				}
				if(BizArr.length-1 == i){
					couponID += '"' + BizID + '":"' + couponvalue + '"';
					offset += '"' + BizID + '":"' + offsetvalue + '"';
					StoreID += '"' + BizID + '":"' + Storevalue + '"';
					shipping += '"' + BizID + '":"' + shippingvalue + '"';
				}else{
					couponID += '"' + BizID + '":"' + couponvalue + '",';
					offset += '"' + BizID + '":"' + offsetvalue + '",';
					StoreID += '"' + BizID + '":"' + Storevalue + '",';
					shipping += '"' + BizID + '":"' + shippingvalue + '",';
				}
			}
		}
		couponID += "}";
		offset += "}";
		StoreID += "}";
		shipping += "}";
		$("input[name='Shipping_arr']").val(shipping);
		$("input[name='Offset_arr']").val(offset);
		
		var action = 'get_shipping';
		var url = base_url + 'api/' + Users_ID + '/shop/cart/ajax/';
		var param = {
			cart_key:cart_key,
			AddressID:typeof AddressID==undefined?0:AddressID,
			source:1,
			express:1,
			CouponID:couponID,
			IsOffset:offset,
			StoreID:StoreID,
			action:action,
			default_express:shipping
		};
		
		$.post(url, param, function(data) {
            if(data.errorCode == 0){
				//计算总金额
				data = data.data;
				var total_price = parseFloat(data.total_info.total) + parseFloat(data.total_info.total_shipping_fee);
				
				$(".total_price").html(total_price.toFixed(2));
				$("#total_price").val(total_price.toFixed(2));
				$("#total_shipping_fee").val(data.total_info.total_shipping_fee);
				$("#total_shipping_fee_txt").html('&yen'+data.total_info.total_shipping_fee+'元');
				//计算各个商家总金额
				if(BizArr.length>0){
					for(let i=0;i<BizArr.length;i++){
						let BizID = BizArr[i];
						let subtotal = data.total_info[BizID].total;
						let biz_shipping_fee = data.total_info[BizID].total_shipping_fee;
						let Shipping_Name = data.total_info[BizID].Shipping_Name;
						let Shipping_ID = data.total_info[BizID].Shipping_ID;
						let sub_total = parseFloat(subtotal) + parseFloat(biz_shipping_fee);
						$('#Sub_Totalall_' + BizID).val(sub_total.toFixed(2));
						$('.sub_total_price_' + BizID).html(sub_total.toFixed(2));
						$('#Shipping_ID_' + BizID).val(biz_shipping_fee);
						$('#Shiping_Method_' + BizID).val("");
						$(".stores_" + BizID).hide();
						if(Shipping_ID !='Is_dada'){
							$("#LastShippingID" + BizID).val(Shipping_ID);
						}
						if (parseFloat(biz_shipping_fee) == 0 && Shipping_ID != 'Is_dada') {
							$('.express_' + BizID).find('.right').html(Shipping_Name + '（免运费）');
						} else {
							if(Shipping_ID !='Is_dada'){
								$('.express_' + BizID).find('.right').html(Shipping_Name + '（' + biz_shipping_fee + '元）');
								
							}else{
								//达达配送的情况
								if(biz_shipping_fee>0){
									var StoreName = $("input[name='Store["+BizID+"]']:checked").attr('tagname');
									var shopid = $("input[name='Store["+BizID+"]']:checked").val();
									$('.express_' + BizID).find('.right').html(Shipping_Name);	//配送方式
									$('.stores_' + BizID).show().find('.right').html(StoreName + '('+biz_shipping_fee+'元)');	//具体的门店
									
									$("#LastShopID" + BizID).val(shopid);
								}else{
										//达达不可用，点击回原来的
									global_obj.win_alert(Shipping_Name + "不可用", function(){
										var sid = $("#LastShippingID" + BizID).val();
										var lastshopid = $("#LastShopID" + BizID).val();
										
										$("#shipping" + sid).prop('checked', true);
										$("input[name='Store["+BizID+"]']").each(function(){
											$(this).removeAttr("checked");
										});
										$(".Store_" + BizID + "_" + lastshopid).prop('checked', true);
										flow_obj.change_shipping_method(sid);
										$('.stores_' + BizID).hide().find('.right').html('不可用');
									});
									
								}
								$("#stores_"+BizID).hide();
							}
						}
					}
					$('.tc-bg').hide();
				}
			}else{
				$("#stores").hide();
				global_obj.win_alert(data.msg);
			}
			
		},'json');
	},
	
	getShippingID:function(){
		
		var Shiping_IDS = [];
		$("input.Shiping_ID_Val:checked").each(function(){
			var Biz_ID = $(this).attr('Biz_ID');
			var Shipping_ID = $(this).val();
			var property = $(this).attr('property');
            var obj =  new Object();
			obj.Biz_ID = Biz_ID;
			obj.property = property;
			obj.Shipping_ID = Shipping_ID;
			Shiping_IDS.push(obj);
     	});
	   return Shiping_IDS;
		
	}

}
