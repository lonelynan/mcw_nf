<!--收货地址信息-->
    <?php if ($rsConfig['NeedShipping'] == 1) { ?>
        <?php if (!empty($rsAddress)) { ?>
            <input type="hidden" id="City_Code" value="<?= $rsAddress['Address_City'] ?>"/>
            <div class="user-information">
                <div>收货人：<span class="username"><?= $rsAddress['Address_Name'] ?></span><span class="right user-phone"><?= $rsAddress['Address_Mobile'] ?></span></div>
                <div class="user-information-address" onclick="window.location.href = '/api/<?= $UsersID ?>/user/my/address/<?= $rsAddress['Address_ID'] ?>/';">
                    <img src="/static/api/shop/skin/default/images/icon-map.png">
                    <div class="user-address-box">
                        收货地址：
                        <span class="user-address"><?= $Province . '&nbsp;&nbsp;' . $City . '&nbsp;&nbsp;' . $Area ?><?= $rsAddress['Address_Detailed'] ?></span>
                    </div>
                    <span class="mui-icon mui-icon-arrowright"></span>
                </div>
            </div>
        <?php } else { ?>
            <div class="user-information" style="height:55px;">
                <div class="user-information-address" onclick="window.location.href = '/api/<?= $UsersID ?>/user/my/address/edit/';">
                    <img src="/static/api/shop/skin/default/images/icon-map.png">
                    <div class="user-address-box" style="line-height:34px;">
                        添加收货地址
                    </div>
                    <span class="mui-icon mui-icon-arrowright"></span>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="user-information" style="height:25px;text-align:center;">
            此商城无需物流
        </div>
    <?php } ?>
    <!--收货地址信息-end-->

    <form id="checkout_form" action="<?= $base_url ?>api/<?= $UsersID ?>/shop/cart/" style="display: inline;">
        <input type="hidden" name="AddressID" value="<?= $rsAddress['Address_ID'] ?>"/>
        <input type="hidden" name="action" value="checkout"/>
        <input type="hidden" name="Need_Shipping" value="<?= $rsConfig['NeedShipping'] ?>"/>
        <input type="hidden" name="Shipping_arr" value='<?= $Shipping_IDsstr ?>'/>
        <input type="hidden" name="Offset_arr" value=''/>
        <input type="hidden" name="City_Code" value="<?= $rsAddress['Address_City'] ?>"/>

		<!--购买商品列表-->
		<ul class="Product-list">
			<?php foreach ($CartList as $Biz_ID => $BizCartList) {
				$rsBiz = $DB->GetRs('biz', 'Biz_Account, Biz_Name, Biz_Logo, Is_BizStores, Biz_Address, Biz_PrimaryLng, Biz_PrimaryLat, Biz_Phone,Is_Union', 'where Biz_ID = ' . $Biz_ID . ' and is_biz = 1 and is_auth = 2');
				
				$isStores = $DB->GetRs('stores', '*', 'where Users_ID = "' . $UsersID . '" and Biz_ID = ' . $Biz_ID . ' and Stores_Status = 1');
				$thridShippList = $DB->GetAssoc('third_shipping', '*', 'where Users_ID = "' . $UsersID . '" and Status = 1');
			if ((!empty($info[$Biz_ID]['error']) && (empty($thridShippList) || empty($isStores)))) { ?>
				<script>
					layer.open({
						content: "<?= $info[$Biz_ID]['msg'] ?>",
						btn: '确定',
						yes: function () {
							location.href= document.referrer;
						}
					});
				</script>
			<?php exit;} ?>
				<li class="Product">
					<!--店铺信息-->
					<div class="user-shop">
						<img class="user-photo left" src="<?= !empty($biz_info[$Biz_ID]['Biz_Logo']) ? $biz_info[$Biz_ID]['Biz_Logo'] : '/static/api/shop/skin/default/images/face.jpg' ?>">
						<span class="user-shop-name left"><?= !empty($biz_info[$Biz_ID]['Biz_Name']) ? $biz_info[$Biz_ID]['Biz_Name'] : (!empty($biz_info[$Biz_ID]['Biz_Account']) ? $biz_info[$Biz_ID]['Biz_Account'] : '') ?></span>
					</div>
					<!--店铺信息-end-->

					<!--商品信息列表-->
					<ul class="mui-table-view mui-table-view-chevron" Biz_ID="<?= $Biz_ID ?>">
						<?php
						$Qty = 0;
						$Sub_Totalall = !empty($info[$Biz_ID]['total_shipping_fee']) ? $info[$Biz_ID]['total_shipping_fee'] : 0;
						foreach ($BizCartList as $Products_ID => $Product_List) {
							//$condition = "where Users_ID = '" . $UsersID . "' and Products_ID =" . $Products_ID;
							//$rsProduct = $DB->GetRs('shop_products', 'Products_Count, Products_IsShippingFree, skuvaljosn', $condition);
							foreach ($Product_List as $attr_id => $product) {

								//产品单价
								if (empty($product['Property'])) { //无属性
									$shu_price = $product['ProductsPriceX'];
								} else {    //有属性
									$shu_price = $product['Property']['shu_pricesimp'];
								}
								//优惠之前的价格
								//计算优惠
								if (isset($user_curagio) && !empty($user_curagio)) {
									$shu_price = $shu_price * (1 - $user_curagio / 100);
									$shu_price <= 0.01 ? 0.01 : sprintf("%.2f",substr(sprintf("%.3f", $shu_price), 0, -2));
								}

								//价格计算
								$Sub_Totalall += (float)$shu_price * (int)$product['Qty'];
								//产品数量
								$Qty += $product['Qty'];
								?>
								<li class="bc-f7">
									<div class="Product-img">
										<img src="<?= $product['ImgPath'] ?>">
									</div>
									<div class="product-information">
										<p class="product-name"><?= $product['ProductsName'] ?></p>
										<p class="product-color"><?= $product['Productsattrstrval'] ?></p>
										<div class="product-num-price">
											<span class="product-price">¥<span class="price"><?= $shu_price ?></span></span>
											<span class="right">X<span class="product-num"><?= $product['Qty'] ?></span></span>
										</div>
									</div>
								</li>
							<?php }
						} 
						
						?>

						<?php if ($rsConfig['NeedShipping'] == 1) { ?>
						<!-- 配送方式选择 start -->
						<input type="hidden" id="trigger_cart_id" value=""/>
						<li class="mui-table-view-cell tc-bt express-box express_<?= $Biz_ID ?>" onclick="tcFn(this, 'express_<?= $Biz_ID ?>')">
							<span class="mui-navigate-right">
								配送方式：
							</span>
							<span class="right">
							<?php
							$shippingName = '';
							$shipping_fee = 0;
							$shipping_ID = 0;
							if(!empty($info[$Biz_ID]['Shipping_Name'])){
								$shippingName = $info[$Biz_ID]['Shipping_Name'];
								$shipping_fee = $info[$Biz_ID]['total_shipping_fee'];
								$shipping_ID = $info[$Biz_ID]['Shipping_ID'];
							}else if($rsBiz['Is_BizStores'] == 1 && !empty($Is_BizStores[$Biz_ID]['Is_BizStores'])){
								$shippingName = '到店自提';
								$shipping_fee = 0;
								$shipping_ID = 'Is_BizStores';
							}else{
								$shippingName = '选择配送方式';
								$shipping_fee = 0;
								$shipping_ID = 0;
							}
							?>
							
							<?= $shippingName . '（' . $shipping_fee . '元）' ?>
							</span>
							<input type="hidden" name="Biz_Shipping_Fee[<?= $Biz_ID ?>]" id="Shipping_ID_<?= $Biz_ID ?>" value="<?= $shipping_fee ?>"/>
							<input type="hidden" id="LastShippingID<?= $Biz_ID ?>" value="<?= $shipping_ID ?>"/>
							<input type="hidden" id="LastShopID<?= $Biz_ID ?>" value="0"/>
						</li>

						<?php if (!empty($biz_company_dropdown)) { ?>
							<div class="tc-main" id="express_<?= $Biz_ID ?>">
								<p class="title">选择快递</p>
								<ul class="mui-table-view">
									<?php foreach ($biz_company_dropdown as $biz_id => $shipping_company_dropdown) {
										if (!empty($shipping_company_dropdown) && $biz_id == $Biz_ID) {
											foreach ($shipping_company_dropdown as $key => $item) { ?>
												<li>
													<div class="mui-input-row mui-radio mui-left">
														<label for="shipping<?= $key ?>"><?= $item ?></label>
														<input type="radio" name="Shiping_ID_<?= $biz_id ?>" value="<?= $key ?>" <?= $info[$biz_id]['Biz_Config']['Default_Shipping'] == $key ? 'checked' : '' ?> property="" Biz_ID="<?= $biz_id ?>" class="Shiping_ID_Val" id="shipping<?= $key ?>" />
													</div>
												</li>
											<?php }
										}
									} ?>
									
									<!-- 获取门店自提配置信息-->
									<?php if($rsBiz['Is_BizStores'] == 1){
									?>
									<li>
										<div class="mui-input-row mui-radio mui-left">
											<label for="shippingIs_BizStores">到店自提</label>
											<input type="radio" name="Shiping_ID_<?= $Biz_ID ?>" value="Is_BizStores" property="" Biz_ID="<?= $Biz_ID ?>" class="Shiping_ID_Val" id="shippingIs_BizStores" <?=empty($biz_company_dropdown[$Biz_ID]) ? 'checked' : ''  ?>/>
										</div>
									</li>
									<?php }?>
									
									
									<!-- 获取达达物流配置信息 start -->
									<?php
									if (!empty($thridShippList) && !empty($isStores)) {
										foreach ($thridShippList as $k => $v) {
											?>
											<li>
												<div class="mui-input-row mui-radio mui-left">
													<label for="shipping<?= $v['ID'] ?>"><?= $v['Name'] ?></label>
													<input type="radio" name="Shiping_ID_<?= $Biz_ID ?>" value="<?= $v['Tag'] ?>" class="Shiping_ID_Val" id="shipping<?= $v['ID'] ?>" />
												</div>
											</li>
											<?php
										}
									}
									?>
									<!-- 获取达达物流配置信息 end -->
								</ul>
								<div class="OKbt">取消</div>
							</div>
						<?php } ?>
						<!-- 配送方式选择 end -->
						<?php } ?>
						<input type="hidden" id="Sub_Totalall_<?= $Biz_ID ?>" name="total_price_bizb" value="<?= $Sub_Totalall ?>" class="subtotalall"/>
						<!-- 选择使用优惠券 start -->
						<?php
						//获取优惠券
						$coupon_info = get_useful_coupons($User_ID, $UsersID, $Biz_ID, $Sub_Totalall);
						if (!empty($rsConfig['Integral_Buy'])) {
							$Products_Integration = Integration_get($Biz_ID, $rsConfig, $rsUser['User_Integral'], $Sub_Totalall);
							$diyong_m = $Products_Integration / $rsConfig['Integral_Buy'];
						}
						if ($coupon_info['num'] > 0 && !empty($coupon_info['lists'])) {
							$coupon_lists = $coupon_info['lists'];
							foreach ($coupon_lists as $key => $item) {
								$coupon_lists[$key]['Price'] = $item["Coupon_UseType"] == 0 ? $item["Coupon_Discount"] : $item["Coupon_Cash"];
							}
							?>
							<li class="mui-table-view-cell tc-bt ticket-box ticket_<?= $Biz_ID ?>" onclick="tcFn(this, 'ticket_<?= $Biz_ID ?>')">
								<span class="mui-navigate-right">
									使用优惠券：
								</span>
								<span class="right fc-r">可使用优惠券</span>
							</li>

							<div class="tc-main panel-footer" id="ticket_<?= $Biz_ID ?>">
								<p class="title">店铺优惠</p>
								<ul class="mui-table-view">
									<?php foreach ($coupon_lists as $k => $v) { ?>
										<li>
											<div class="mui-input-row mui-radio mui-left">
												<label><?= $v['Subject'] ?></label>
												<input type="radio" name="CouponID[<?= $v['Biz_ID'] ?>]" value="<?= $v['Coupon_ID'] ?>" UseType="<?= $v['Coupon_UseType'] ?>" Price="<?= $v['Price'] ?>" bizid="<?= $v['Biz_ID'] ?>" check="0" class="coupon">
											</div>
										</li>
									<?php } ?>
								</ul>
								<div class="OKbt">取消</div>
							</div>
						<?php } ?>
						<!-- 选择使用优惠券 end -->

						<!-- 选择门店 start -->
						<?php
						if (!empty($rsBiz)) {
							//此区域代码块主要用于检测是否有门店,如果有的话,可以选择是否上门自提以及下面的展示门店信息
							$storelist = $DB->GetRs('stores', 'Stores_ID, Stores_Name, Stores_PrimaryLng, Stores_PrimaryLat', 'where Users_ID = "' . $UsersID . '" and Biz_ID = ' . $Biz_ID . ' and Stores_Status = 1');
							if (!empty($storelist)) {
								?>
								<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?= $ak_baidu ?>"></script>

								<input type="hidden" name="storeFlag" value="1" />
								<li class="mui-table-view-cell tc-bt stores-box stores_<?= $Biz_ID ?>" style="display:none;" onclick="tcFn(this, 'stores_<?= $Biz_ID ?>')">
									<span class="mui-navigate-right">
										选择门店：
									</span>
									<span class="right fc-r">门店</span>
								</li>

								<div class="tc-main mendian_tt" id="stores_<?= $Biz_ID ?>" style="display:none;">
									<p class="title">选择门店</p>
									<ul class="mui-table-view"></ul>
									<div class="OKbt">取消</div>
								</div>
							<?php }
						} ?>
						<!-- 选择门店 end -->

						<!-- 使用积分 start -->
						<?php if (!empty($Products_Integration) && !empty($diyong_m)) { ?>
							<li class="mui-table-view-cell" use_integral="<?= $Products_Integration ?>" free_money="<?= $diyong_m ?>">
								<span class="mui-navigate-right">
									使用<?= $Products_Integration ?>积分抵扣<?= $diyong_m ?>元：
								</span>
								<div class="mui-switch mui-switch-mini integral_switch">
									<div class="mui-switch-handle"></div>
									<input type="checkbox" name="offset[<?= $Biz_ID ?>]" value="0"/>
								</div>
							</li>
						<?php } ?>
						<!-- 使用积分 end -->

						<?php
						foreach ($perm_config as $key => $value) {
							if ($value['Perm_Field'] == 'fapiao' && $value['Perm_On'] == 0) { ?>
								<li class="mui-table-view-cell">
									<span class="mui-navigate-right">
										是否索要发票：
									</span>
									<div class="mui-switch mui-switch-mini invoice_switch">
										<div class="mui-switch-handle"></div>
										<input type="checkbox" name="Order_NeedInvoice[<?= $Biz_ID ?>]" value="1">
									</div>
								</li>
								<li class="mui-table-view-cell invoice_title">
									<input type="text" name="Order_InvoiceInfo[<?= $Biz_ID ?>]" maxlength="20" placeholder="请填写发票抬头">
								</li>
							<?php }
						} ?>
						<li class="mui-table-view-cell seller-msg">
							<span class="mui-navigate-right">
								买家留言：
							</span>
							<textarea name="Remark[<?= $Biz_ID ?>]" maxlength="50" placeholder="填写内容已和卖家协商确认"></textarea>
						</li>
						<li class="mui-table-view-cell">
							<div class="right">
								共<span><?= $Qty ?></span>件商品
								<span class="allprice">小计：<span class="fc-r">¥</span></span><span class="price sub_total_price_<?= $Biz_ID ?>"><?=$Sub_Totalall ?></span>
							</div>
						</li>
					</ul>
				</li>
				<input type="hidden" name="Shiping_Method_<?= $Biz_ID ?>" id="Shiping_Method_<?= $Biz_ID ?>" value=""/>
				<input type="hidden" name="deliveryNo_<?= $Biz_ID ?>" id="deliveryNo_<?= $Biz_ID ?>" value=""/>
			<?php } ?>
		</ul>
		<!--购买商品列表-end-->

	<div class="bottom-box">
		<div class="right">
			<span>合计：</span><span class="fc-r">¥</span><span class="price total_price"><?=$total_price ?></span>
			<input type="button" id="submit-btn" value="提交订单">
		</div>
	</div>

	<input type="hidden" id="cart_key" name="cart_key" value="<?=$cartKey ?>"/>
	<input type="hidden" id="total_price" value="<?= $total_price ?>"/>
	<input type="hidden" id="coupon_value" value="0" placeholder="优惠金额"/>
	<input type="hidden" id="prebiz_value" value="0" placeholder="选择优惠金额的商家biz_id"/>
	<input type="hidden" id="total_shipping_fee" name="Order_Shipping" value="<?= $total_shipping_fee ?>"/>
</form>
<script src="/static/api/js/mui/mui.min.js"></script>
<script>
	$(function(){  
		pushHistory();  
		window.addEventListener("popstate", function(e) {  //回调函数中实现需要的功能
			location.href= document.referrer; 
		}, false);  
	});

	function pushHistory() {  
		var state = {  
			title: "title",  
			url: ""  
		};  
		window.history.pushState(state, state.title, state.url);  
	} 


	/****初始化达达配送门店列表 start****/
	$(function(){
		var AddressID = "<?=$rsAddress['Address_ID'] ?>";
		var UsersID = '<?=$UsersID?>';
		var cartKey = '<?=$cartKey?>';
		var url = "/api/" + UsersID + "/shop/cart/ajax/";
		var param = {
			action:'getBizProduct',
			cart_key:cartKey,
			AddressID:AddressID,
			source:1
		};
		
		$.post(url, param, function(data){
			if(data.errorCode>1){
				layer.alert(data.msg);
			}else{
				let jsondata = data.data;
				for(let inx in jsondata){
					let store = jsondata[inx]['store'];
					htmltmp = "";
					
					for(let i=0;i<store.length;i++){
						if(i==0){
							$("#LastShopID" + inx).val(store[i]['Stores_ID']);
						}
						if(i>10){
							break;
						}
						htmltmp += '<li>';
						htmltmp += '<div class="mui-input-row mui-radio mui-left">';
						htmltmp += '<label style="padding-left:0">门店名称：' + store[i]['Stores_Name']  + '</label>';
						htmltmp += '<input type="radio" name="Store['+inx+']" class="chooseStore Store_'+inx + '_'+ store[i]['Stores_ID'] +'" value="' + store[i]['Stores_ID'] + '"' + (i == 0 ? " checked " : "") + ' BizID="' + store[i]['Biz_ID'] + '" tagname="'+ store[i]['Stores_Name'] +'"><br/>';
						htmltmp += '地址：<a href="http://api.map.baidu.com/marker?location=' + store[i]['Stores_PrimaryLat'] + ',' + store[i]['Stores_PrimaryLng'] + '&title=' + store[i]['Stores_Name'] + '&name=' + store[i]['Stores_Name'] + '&content=' + store[i]['Stores_Address'] + '&output=html">' + store[i]['Stores_Address'] + '</a>';
						htmltmp += '</div>';
						htmltmp += '</li>';
					}
					
					$('#stores_' + inx + ' ul.mui-table-view').html(htmltmp);
				}
			}
		}, 'json');
	});
	/****初始化达达配送门店列表 end****/

    //动态修改遮罩的高度
    window.onload = function () {
        var winH = document.documentElement.clientHeight;
        var tc_bg = document.getElementsByClassName("tc-bg")[0];
        tc_bg.style.height = winH + "px";
    };

    //产品图片正方形化
    $(".Product-img").each(function (i) {
        $(this).css('height', $(this).width());
    });

    //是否需要发票开关
    $('.invoice_switch').click(function () {
        var me = $(this);
        var Biz_ID = me.parents('ul').attr('Biz_ID');

        //判断是开关状态（开启或关闭）
        if (me.hasClass('mui-active')) {
            //open
            $('input[name="Order_InvoiceInfo[' + Biz_ID + ']"]').parents('.invoice_title').show();
            $('input[name="Order_NeedInvoice[' + Biz_ID + ']"]').prop('checked', true);
        } else {
            //close
            $('input[name="Order_InvoiceInfo[' + Biz_ID + ']"]').parents('.invoice_title').hide();
            $('input[name="Order_NeedInvoice[' + Biz_ID + ']"]').prop('checked', false);
        }
    });

    //是否启用积分兑换
    $('.integral_switch').click(function () {
        var me = $(this);
        var Biz_ID = me.parents('ul').attr('Biz_ID');
		var diyong = parseFloat(me.parent().attr('free_money'));
        //小计价格（对应商家）
        var sub_total_price = parseFloat($('.sub_total_price_' + Biz_ID).text());
        //总计金额
        var total_price = parseFloat($('.total_price').text());

        //判断是开关状态（开启或关闭）
        if (me.hasClass('mui-active')) {
            //open
            $('input[name="offset[' + Biz_ID + ']"]').val(1);
			if(sub_total_price - diyong<0){
				me.find('input').val(0);
				me.removeClass('mui-active');
				me.find('.mui-switch-handle').css('transform','translate(0px, 0px)');
				global_obj.win_alert("此商家使用积分抵用后不得少于0元");
				
				return false;
			}
			
			if(total_price - diyong<0){
				me.find('input').val(0);
				me.removeClass('mui-active');
				me.find('.mui-switch-handle').css('transform','translate(0px, 0px)');
				global_obj.win_alert("总价使用积分抵用后不得少于0元");
				
				return false;
			}
        } else {
            //close
            $('input[name="offset[' + Biz_ID + ']"]').val(0);
        }
        
		
		flow_obj.change_shipping_method();
    });


    mui.init({
        swipeBack: true //启用右滑关闭功能
    });


    //选择弹窗
    var clickPart;
    function tcFn(that, pop) {
		$("#"+ pop +"").css("display","block");
		/*
        if (pop.indexOf("stores") > -1) {
            $("#"+ pop +"").css("display","block")
        }*/
        clickPart = that;
        //遮罩弹出
        $(".tc-bg").fadeIn();
        //显示选择弹窗
        $('.tc-main#' + pop).animate({
            bottom: "0px"
        });
    }

    //隐藏遮罩
    function tcHide() {
        $(".tc-bg").fadeOut();
        $(".tc-main").css("display","none")
    }

    //遮罩隐藏
    $(".tc-bg, .OKbt").click(function () {
        tcHide();
    });

    $('.tc-main input').click(function () {
        if (!$(this).hasClass('Shiping_ID_Val')) {  //物流不需要
            var ticket_text = $(this).parent().find("label").text();
            $(clickPart).find(".right").html(ticket_text);
        }
        tcHide();
    });
</script>