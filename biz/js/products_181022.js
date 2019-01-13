/*
 * 产品的添加、编辑
 * 使用在 products_add.php、products_edit.php
 */


$(document).ready(shop_obj.products_init);  //add.php

KindEditor.ready(function (K) {
    K.create('textarea[name="Description"]', {
        themeType: 'simple',
        filterMode: false,
        wellFormatMode: true,
        resizeType: true,
        uploadJson: '/biz/upload_json.php?TableField=shop_products&BIZ_ID=' + BIZ_ID,
        fileManagerJson: '/biz/file_manager_json.php',
        allowFileManager: true,
        afterBlur: function(){this.sync();}
    });
    var editor = K.editor({
        uploadJson: '/biz/upload_json.php?TableField=shop_products&BIZ_ID=' + BIZ_ID,
        fileManagerJson: '/biz/file_manager_json.php',
        showRemote: true,
        allowFileManager: true
    });
    K('#ImgUpload').click(function () {
        if (K('#PicDetail').children().length >= 5) {
            alert('您上传的图片数量已经超过5张，不能再上传！');
            return;
        }
        editor.loadPlugin('image', function () {
            editor.plugin.imageDialog({
                clickFn: function (url, title, width, height, border, align) {
                    K('#PicDetail').append('<div><a href="' + url + '" target="_blank"><img src="' + url + '" /></a> <span onclick="$(this).parent().remove();">删除</span><input type="hidden" name="JSON[ImgPath][]" value="' + url + '" /></div>');
                    editor.hideDialog();
                }
            });
        });
    });

    // 上传产品首页展示图
    K('#Rect_ImgUpload').click(function () {
        if (K('#Rect_PicDetail').children().length >= 1) {
            alert('您上传的图片数量已经超过1张，不能再上传！');
            return;
        }
        editor.loadPlugin('image', function () {
            editor.plugin.imageDialog({
                clickFn: function (url, title, width, height, border, align) {
                    K('#Rect_PicDetail').append('<div><a href="' + url + '" target="_blank"><img src="' + url + '" /></a> <span onclick="$(this).parent().remove();">删除</span><input type="hidden" name="Rect_Img" value="' + url + '" /></div>');
                    editor.hideDialog();
                }
            });
        });
    });

    $('#attrs').on('click', '.attr_upload', function () {
        var log_num = $(this).next().val();
        var logo = '.Logo_' + log_num;
        var LogoDetail = '.LogoDetail_' + log_num;
        var attr_image = '.attr_image_' + log_num;
        editor.loadPlugin('image', function () {
            editor.plugin.imageDialog({
                clickFn: function (url, title, width, height, border, align) {
                    K(logo).val(url);
                    K(LogoDetail).html('<img src="' + url + '" />');
                    K(attr_image).val(url);
                    editor.hideDialog();
                }
            });
        });
    });

    $(document).on('click', '.addProPic', function () {
        var log_num = $(this).attr('data-pic');
        var LogoDetail = '.LogoDetail_' + log_num;
        var attr_image = '.attr_image_' + log_num;
        editor.loadPlugin('image', function () {
            editor.plugin.imageDialog({
                clickFn: function (url, title, width, height, border, align) {
                    K(LogoDetail).html('<img src="' + url + '" style="width: 28px;height:28px;" />');
                    K(attr_image).val(url);
                    editor.hideDialog();
                }
            });
        });
    });

    K('#PicDetail div span').click(function () {
        K(this).parent().remove();
    });

    // 删除产品首页展示图片
    K('#Rect_PicDetail div span').click(function () {
        K(this).parent().remove();
    });
});

function insertRow() {
    var newrow = document.getElementById('wholesale_price_list').insertRow(-1);
    newcell = newrow.insertCell(-1);
    newcell.innerHTML = '数量： <input type="text" name="JSON[Wholesale][' + (document.getElementById('wholesale_price_list').rows.length - 2) + '][Qty]" value="" class="form_input" size="5" maxlength="3" /> 价格：￥ <input type="text" name="JSON[Wholesale][' + (document.getElementById('wholesale_price_list').rows.length - 2) + '][Price]" value="" class="form_input" size="5" maxlength="10" /><a href="javascript:;" onclick="document.getElementById(\'wholesale_price_list\').deleteRow(this.parentNode.parentNode.rowIndex);"> <img src="/static/member/images/ico/del.gif" hspace="5" /></a>';
}

function getAttrHtml(){
	var TypeID = $("#products #Type_ID").find("option:selected").attr('value');
	if (TypeID.length > 0) {
		$.ajax({
			type: "POST",
			url: "/biz/products/ajax.php",
			data: "action=get_attr&UsersID=" + $("#UsersID").val() + "&TypeID=" + $("#Type_ID").val() + "&ProductsID=" + $("#ProductsID").val() + "&FinanceType=" + $('input[name=FinanceType]:checked').val(),
			dataType: "json",
			async: false,
			success: function (data) {

				if (data.content) {
					$(".pro_click").attr("checked",false);
					$("#skutableval").html("")
				} else {
					$("#attrs").html('');
					$("#skutableval").html("")
				}
			}
		});
	}
}

/**
 * 格式化金额
 */
function format_money(money) {
    money = parseFloat(money);
    if (!money) {
        return false;
    }
    return parseInt(money * 100)/100;
}



/**
 * 抢购价格离开焦点事件
 * @param me
 * @returns {boolean}
 */
function check_flashsale_pricex() {

    //现价
    var pricex = format_money($('#PriceX').val());
    //限购价
    var flashsale_pricex = format_money($('#flashsale_pricex').val());
    //当两个价格其中有一个为空时，则返回
    if (pricex === false || flashsale_pricex == false){
        return false;
    }
    //活动设置的比例
    var discount = format_money(parseFloat(PriceDiscount)/ 100);
    var max_price = format_money(discount * pricex);
    //判断限时抢购价是否合法
    if (flashsale_pricex > max_price) {
        layer.tips('按活动要求，限购价格最高可设置为：' + max_price + ' 元',$('#flashsale_pricex'));
        $('#flashsale_pricex').focus();
        return false;
    }
}


/**
 * 价格设置离开焦点事件
 * @param me
 * @returns {boolean}
 */
function check_price_son(me) {
    var _this = $(me);
    var that =  _this.parent().parent().find("td.xs > input.l-text");
    check_price(_this,that)
}

//判断限时抢购价是否合法
function check_flashsale(me) {
    var _this = $(me);
    var that =  _this.parent().siblings().find('input.Txt_PriceSon');
    check_price(that,_this)
}

/**
 * 计算 限时抢购价格合法性
 * @param price       价格
 * @param dis_price   限时抢购价
 */
function check_price(price_obj,dis_obj) {
    var price = format_money(price_obj.val());
    var dis_price = format_money(dis_obj.val());
    if (dis_price < 0.01) {
        return true;
    }
    if (price < 0.01) {
        layer.tips('请填写价格',price_obj);
        return false;
    }
    //活动设置的比例
    var discount = format_money(parseFloat(PriceDiscount)/ 100) ;
    var max_price = format_money(discount * price);
    //判断限时抢购价是否合法
    if (dis_price > max_price) {
        layer.tips('按活动要求，价格最高可设置为：' + max_price + ' 元',dis_obj);
        dis_obj.focus();
        return false;
    }
}


$(document).ready(shop_obj.products_edit_init);

$(function () {
    //新老数据转换过度
    //对属性价格设置表做处理
    if (typeof product_attr_html != 'undefined' && product_attr_html == 1) {
        if ($('#process').length > 0) {
            var skutableval_tab = $('#skutableval');
            skutableval_tab.find('table, tbody, th, input').removeAttr('style');
            skutableval_tab.find('table').removeAttr('border').removeAttr('cellpadding').removeAttr('cellspacing');

            var start = 0;
            skutableval_tab.find('th').each(function (i) {
                if ($(this).text() == '价格') {
                    start = i;
                }
            });
            if (start > 0) {
                skutableval_tab.find('th:gt(' + (start - 1) + ')').addClass('bt');
            }

            skutableval_tab.find('td:visible').removeAttr('style');
            skutableval_tab.find('td:hidden').removeAttr('style').attr('style', 'display:none;');
        }
    }

    //是否推荐到批发市场初始化
    if (isgh == 1 || $("#is_Tj").attr("checked")) {
        $("#PriceS").show();
        if (is_pt == 1) {
            $('#pintuan_time #ptG').show();
        }
    } else {
        $("#PriceS").hide();
        $('#pintuan_time #ptG').hide();
    }

    //拼团状态初始化
    if ($("#is_pintuan").attr("checked")) {

        $("#pintuan_time").show();
        $("#addPT").show();	//批量填充拼团价
        	//批量填充拼团供货价
        if (isgh != 1) {
            $('#pintuan_time #ptG').hide();
			$("#addPTG").hide();
        }else{
			$('#pintuan_time #ptG').show();
			$("#addPTG").show();
		}
        $("#pt_tag").hide();
        is_pt = 1;
    } else {
        $("#pintuan_time").hide();
        $("#pt_tag").show();
        $("#addPT").hide();
        $("#addPTG").hide();
        is_pt = 0;
    }

    //限时抢购状态初始化
    if ($("#is_flashsale").attr("checked")) {
        $("#flashsale_time").show();
        $("#pt_tag").hide();
        is_xs = 1;
    } else {
        $("#flashsale_time").hide();
        $("#pt_tag").show();
        is_xs = 0;
    }

    //只有供货商才有豆币
    //赠送豆币计算
    $("input[name=PriceX], input[name=Products_PriceS]").keyup(function () {
        var prices = parseFloat($("input[name=Products_PriceS]").val());
        var pricex = parseFloat($("input[name=PriceX]").val());
        var coin = 0;
        if (prices && pricex) {
            var val = prices / pricex;
            pricex = parseInt(pricex);
            if (val <= 0.2) {
                coin = pricex;
            } else if (val > 0.2 && val <= 0.3) {
                coin = pricex * 0.8;
            } else if (val > 0.3 && val <= 0.4) {
                coin = pricex * 0.6;
            } else if (val > 0.4 && val <= 0.5) {
                coin = pricex * 0.4;
            } else if (val > 0.5 && val <= 0.6) {
                coin = pricex * 0.2
            } else if (val > 0.6 && val <= 0.65) {
                coin = pricex * 0.1
            } else {
                coin = 0;
            }
        } else {
            coin = 0;
        }
        $("#coin").val(parseInt(coin));
    });

    //现价填写提醒
    $("input[name=PriceX]").focus(function () {
        var tips = layer.tips('此产品价格不能高于原价，否则审核专员会下架您的产品', $(this), {time: 0, tips: 1});
        $(this).blur(function () {
            layer.close(tips);
        });
    });
    //供货价填写提醒
    $("#Products_PriceS").focus(function () {
        var tips = layer.tips('请注意,供货价即为出厂价,留给代销商的利润越高，代销的商家会越多', $(this), {time: 0, tips: 1});
        $(this).blur(function () {
            layer.close(tips);
        });
    });
    //拼团价填写提醒
    $("input[name=pintuan_pricex]").focus(function () {
        //var tips = layer.tips('请注意,如您的拼团人数为2人,则拼团价最少应低于现价的90%,如拼团人数为2人以上,则拼团价最少应低于现价的80%', $(this), {time: 0, tips: 1});
        $(this).blur(function () {
            layer.close(tips);
        });
    });
    //拼团供货价填写提醒
    $("input[name=pintuan_prices]").focus(function () {
        var tips = layer.tips('请注意,拼团供货价必须小于单独购买供货价', $(this), {time: 0, tips: 1});
        $(this).blur(function () {
            layer.close(tips);
        });
    });

    //批发市场选择
    $("#is_Tj").on('change', function () {
        var me = $(this);
        if ($('#is_Tj').is(':checked')) {
            //未进行商家认证
            if (typeof seller_verify != 'undefined' && seller_verify == 0) {
                layer.confirm('对不起,您尚未进行商家认证,请认证后再进行此项操作', {
                    btn: ['立即认证', '暂不认证'] //按钮
                }, function () {
                    window.location = "../authen/index.php";
                }, function () {
                    me.attr("checked", false);
                    $("input[name=Products_PriceS]").hide();
                });
                return false;
            }

            if (typeof verifyExpiresTime != 'undefined' && (verifyExpiresTime == 0 || verifyExpiresTime < (new Date().getTime() / 1000))) {
                var layer_msg = verifyExpiresTime == 0 ? '对不起,您还不是供货商,请交费成为供货商后再进行此项操作' : '对不起,您的供货商时间已经到期,请续费后后再进行此项操作';
                layer.confirm(layer_msg, {
                    btn: ['立即交费', '暂不交费'] //按钮
                }, function () {
                    window.location = "../authen/charge_supply.php";
                }, function () {
                    me.attr("checked", false);
                    $("input[name=Products_PriceS]").hide();
                });
                return false;
            }

            $("input[name=Products_PriceS]").show();
            if (is_pt == 1) {
                $('#pintuan_time #pintuan_prices_label, #pintuan_time input[name="pintuan_prices"]').show();
            }
            isgh = 1;
        } else {
            $("input[name=Products_PriceS]").hide();
            $('#pintuan_time #pintuan_prices_label, #pintuan_time input[name="pintuan_prices"]').hide();
            isgh = 0;
        }
        pro_attr_table();
    });
    //拼团选择
    $("#is_pintuan").on('change', function () {
		/*
        if ($("#is_flashsale").attr("checked")) {
            $("#pt_price").css("display","none");
        } else {
            $("#pt_price").css("display","inline");
        }*/
        var me = $(this);
        if (me.attr('checked')) {
            $("#pintuan_time").show();
            $("#addPT").show();
            
            if (isgh != 1) {
				$('#pintuan_time #ptG').hide();
				$("#addPTG").hide();
            }else{
				$("#addPTG").show();
				$('#pintuan_time #ptG').show();
			}
            $("#pt_tag").hide();
            is_pt = 1;
        } else {
            $("#pintuan_time").hide();
            $("#pt_tag").show();
            $("#addPT").hide();
            $("#addPTG").hide();
            is_pt = 0;
        }
		changeok=1;
        getAttrHtml();
    });
    //拼团人数填写过滤
    $("input[name=pintuan_people]").on("keyup", function () {
        var inputData = $(this).val();
        $(this).val(inputData.replace(/[^0-9]/g, ''));
    });


    //限时抢购选择
    $("#is_flashsale").on('change', function () {
        var me = $(this);
        if (me.attr('checked')) {
            $("#flashsale_time").show();
            $("#xs_tag").hide();
            is_xs = 1;
        } else {
            $("#flashsale_time").hide();
            $("#xs_tag").show();
            is_xs = 0;
        }
		changeok=1;
        getAttrHtml();
    });    //产品参数增加判断
    var index = 0;
    $('.icon-plus').click(function () {
        if (index >= 18) {
            layer.open({
                shadeClose: false,
                title: [
                    '超过最大可添加',
                    'background-color: #3396fe;font-size:12px;color:#fff;'
                ],
                content: '最多添加20项产品参数'
            });
        }
        index++;
        var html = $(this).closest('.form-group').clone();
        html.find('.icon-plus').removeClass('icon-plus').addClass('icon-minus');
        html.find(".ProductsParametername").attr('name', 'Products_Parameter[' + index + '][name]');
        html.find(".ProductsParametervalue").attr('name', 'Products_Parameter[' + index + '][value]');

        $(this).closest('.form-group').after(html);
        $(".icon-minus").html("[-]");
    });
    //产品属性删除
    $('.skipForm').on('click', '.icon-minus', function () {
        $(this).closest('.form-group').remove();
        index--;
    });

    $('input[name=FinanceType]').click(function () {
		var val = $(this).val();
		if(val==0){
			$('#PriceS').hide();
			$('#FinanceRate').show();
			$('#ptG').css('display','none');
			$('#plgh').css('display','none');	//供货价批量填充
			isgh = 0;
		}else{
			$('#PriceS').show();
			$('#FinanceRate').hide();
			$('#ptG').css('display','inline');
			$('#plgh').css('display','inline');
			isgh = 1;
		}
		changeok=1;
        
		//产品属性选择
        getAttrHtml();
    });

    $(".icon-minus").html("[-]");

    $("a.select_category").click(function () {
        //产品分类
        global_obj.create_layer('产品分类', "level.php", 600, 500, 1, 1);
    });


    $('.rows input[name=ordertype]').click(function () {
        var sVal = $('.rows input[name=ordertype]:checked').val();
        if (sVal == 2) {
            if (confirm(typeof productId != 'undefined' ? '是否修改绑定现有虚拟卡密?' : '是否设置绑定虚拟卡密?')) {
                layer.open({
                    type: 2,
                    area: ['800px', '620px'],
                    fix: false,
                    maxmin: true,
                    content: '/biz/products/virtual_card_select.php' + (typeof productId != 'undefined' ? '?productId=' + productId : '')
                });
                $('input[name=Count]').val('0').attr('readonly', 'readonly');
            }
            $("#Come_Stores").css("display","none");
        } else {
            $('input[name=Count]').val('0').removeAttr('readonly');
            if (sVal == 0) {
                $("#Come_Stores").css("display","block");
            }else if(sVal == 1){
                $("#Come_Stores").css("display","none");
            }
        }
    });

    //检测是否绑定虚拟卡
    var productid = $.trim($("input[name='cardids']").val());
    if (productid != null && productid != '') {
        $("input[name='Count']").attr("readonly", true);
    } else {
        $("input[name='Count']").removeAttr("readonly");
    }


    //批发商城分类
    $(document).on('click', '#select_b2ccategory', function () {
        var lib_cate_the = "./lib_cate_the.php" + (typeof productId != 'undefined' ? '?productId=' + productId : '');
        layer.open({
            type: 2,
            title: '选择批发商城类目',
            shadeClose: true,
            area: ['800px', '590px'],
            content: lib_cate_the,
            success: function (layero, index) {
                var body = layer.getChildFrame('body', index);
                var cateid = $("#B2CCategory").val();
                if (cateid > 0) {
                    body.find('#cateid').val(cateid);
                }
                body.find('#releaseBtn').click(function () {
                    var cateid = body.find('#cateid').val();
                    var selectedSort = body.find('#selectedSort').html();
                    $("#B2CCategory").val(cateid);
                    $("#b2cclasss").html(selectedSort);
                    layer.closeAll();
                });
            }
        });
    });

    $(document).on('click', '#cate_close', function () {
        var select_class = '';
        var products_categorys = ",";
        var products_name = "";
        var son = '';
        $('#select_category dd input:radio:checked').each(function () {
            son += $(this).next('qq544731308son').html();
            products_categorys += $(this).attr("parentid") + ',' + $(this).val() + ',';
            products_name += $(this).attr("parentname");
        });
        select_class += '<p style="margin:5px 0px; padding:0px; font-size:12px; color:#666"><font style="color:#333; font-size:12px;">' + products_name + '</font>&nbsp;&nbsp;' + son + '</p>';

        $("input[name='Products_Categorys']").val(products_categorys);
        if (select_class == '') {
            $('#classs').html(son);
        } else {
            $('#classs').html(select_class);
        }

    });

    //批发商城选取分类
    $(document).on('click', '#b2ccate_close', function () {
        var select_class = '';
        var son = '';
        $('#select_b2ccategory dd input:radio:checked').each(function () {
            son += $(this).next('qq271054123son').html();
        });
        $('#select_b2ccategory dt input:radio:checked').each(function () {
            if ($.trim($(this).parent('dt').next('dd').html()) != '') {
                son = $(this).parent('dt').next('dd').find('qq271054123son').html();
            }
            select_class += '<font style="color:#333; font-size:12px;">' + $(this).parent('dt').find('qq271054123').html() + '</font>&nbsp;' + son;
        });
        if (select_class == '') {
            if (son != '') {
                $('#b2cclasss').html(son);
            }
        } else {
            $('#b2cclasss').html(select_class);
        }
    });

    //表单提交前验证
    $('.btn_green').click(function () {
        //禁止重复提示，无法使用 attr("disabled", true);
        var me = $(this);
        if (me.attr('status') == '1') return false;

        //验证产品供货价和产品利润是否达到特定的值
        var supplyPrice = parseFloat($("input[name='PriceS']").val());
        //var Products_Profit = parseFloat($("input[name='Products_Profit']").val());
        var PriceX = Number($("input[name='PriceX']").val());
        var PriceY = Number($("input[name='PriceY']").val());
        var pintuan_pricex = Number($("input[name='pintuan_pricex']").val());
        var pintuan_prices = Number($("input[name='pintuan_prices']").val());
        var pintuan_people = Number($("input[name='pintuan_people']").val());
        var is_Tj = isgh;
        // var is_pt = $('input[name="is_pintuan"]').is(":checked");   //拼团状态

        var BriefDescription = $.trim($("#BriefDescription").val());
        var Products_Weight = Number($("#Products_Weight").val());
        var Name = $.trim($("#Name").val());
        var pic = $("#PicDetail input").val();
        var Index = $("#Index").val();
        var ordertype = $("input[name='ordertype']:checked").val();
        var flash_price = $("#flashsale_pricex").val();

        if (Name == '' || Name == null) {
            $("#Name").focus();
            layer.tips("请填写产品名称！", "#Name");
            return false;
        }
        //选择分类判断
        /*if ($("#cateSec").val() == null || $("#cateSec").val() == '') {
            $("#cateSec").focus();
            layer.tips("请选择分类", "#cateSec");
            return false;
        }*/
        //选择分类判断
        if ($("#classs").html() == '') {
            layer.alert('请选择分类');
            return false;
        }

        if (PriceY <= 0 || isNaN(PriceY)) {
            $("#PriceY").focus();
            layer.tips("请填写原价！", "#PriceY");
            return false;
        }
        if (PriceX <= 0 || isNaN(PriceX)) {
            $("#PriceX").focus();
            layer.tips("请填写现价！", "#PriceX");
            return false;
        }
        if (PriceY < PriceX) {
            $("#PriceY").focus();
            layer.tips("产品原价不能小于等于产品现价!", "#PriceY");
            return false;
        }
        if (is_Tj) {
            if (supplyPrice <= 0 || isNaN(supplyPrice)) {
                $("#Products_PriceS").focus();
                layer.tips("请填写供货价", "#Products_PriceS");
                return false;
            }

            /*if (supplyPrice > PriceX * 0.75) {
                $("#Products_PriceS").focus();
                layer.tips("产品供货价必须小于现价的75%", "#Products_PriceS");
                return false;
            }*/

            // if ($("#B2CCategory").val() == null || $("#B2CCategory").val() == '') {
            //     console.log(1111)
            //     $(document).scrollTop(0);
            //     layer.tips("请选择平台分类", "#select_b2ccategory");
            //     return false;
            // }

        } else {

            var dislevel = [[0, 0, 0], [0, 0, 0]], currentIndex = 0;
            var check = [];
            var tableSize = $(".dislevelcss .item_data_table").size();
            var flag = true;
            if (tableSize == 1) {
                var total = 0;
                $(".dislevelcss .item_data_table").each(function (index, element) {
                    currentIndex = index;
                    $(this).find(".disLevelFlag").each(function (i, e) {
                        total += parseFloat($(this).val());
                    });
                    if (total > 100) {
                        flag = false;
                    }
                });
            } else {
                $(".dislevelcss .item_data_table").each(function (index, element) {
                    currentIndex = index;
                    $(this).find(".disLevelFlag").each(function (i, e) {
                        dislevel[currentIndex][i] = parseFloat($(this).val());
                    });
                });
                check.push(parseFloat(dislevel[0][0]) + parseFloat(dislevel[1][1]) + parseFloat(dislevel[1][2]));
                check.push(parseFloat(dislevel[0][1]) + parseFloat(dislevel[1][0]) + parseFloat(dislevel[1][2]));
                check.push(parseFloat(dislevel[0][2]) + parseFloat(dislevel[1][0]) + parseFloat(dislevel[1][1]));
                check.push(parseFloat(dislevel[1][0]) + parseFloat(dislevel[0][1]) + parseFloat(dislevel[0][2]));
                check.push(parseFloat(dislevel[1][1]) + parseFloat(dislevel[0][0]) + parseFloat(dislevel[0][2]));
                check.push(parseFloat(dislevel[1][2]) + parseFloat(dislevel[0][0]) + parseFloat(dislevel[0][1]));
                check.push(parseFloat(dislevel[0][0]) + parseFloat(dislevel[0][1]) + parseFloat(dislevel[0][2]));
                check.push(parseFloat(dislevel[1][0]) + parseFloat(dislevel[1][1]) + parseFloat(dislevel[1][2]));

                for (var i = 0; i < check.length; i++) {
                    if (check[i] > 100) {
                        flag = false;
                        break;
                    }
                }
            }

            if (flag == false) {
                layer.alert("各项佣金比例不能超过100%");
                return false;
            }
        }

        var re = /^[0-9]+(.[0-9]{1,2})?$/;
        var recount = /^(0|[1-9][0-9]*)$/;
        var renum = /^[a-zA-Z0-9_]/;
        var PriceS = Number($("input[name=PriceS]").val());
        var FinanceType = Number($("input[name=FinanceType]:checked").val());
        if (FinanceType == 1) {
            if (PriceS <= 0 || PriceS > PriceX) {
                $(document).scrollTop(0);
                layer.tips("供货价格必须大于零，且小于售价！", "input[name=PriceS]");
                return false;
            }
        }

        if (is_pt == 1) {
            if (is_xs != 1) {
                if (!pintuan_people || !pintuan_pricex) {
                    $(document).scrollTop(0);
                    layer.tips("请填写完整的拼团参数!", "input[name=" + (!pintuan_people ? 'pintuan_people' : 'pintuan_pricex') + "]");
                    return false;
                }
            } else {
                if (!pintuan_people) {
                    $(document).scrollTop(0);
                    layer.tips("请填写完整的拼团参数!", "input[name=" + (!pintuan_people ? 'pintuan_people' : 'pintuan_pricex') + "]");
                    return false;
                }
            }

            if (pintuan_people < 2) {
                $(document).scrollTop(0);
                layer.tips("拼团人数最少为2人!", "input[name=pintuan_people]");
                return false;
            }

            if (is_Tj == 1 && !pintuan_prices) {
                $(document).scrollTop(0);
                layer.tips("请填写拼团供货价!", "input[name=pintuan_prices]");
                return false;
            }

            /*if (pintuan_people == 2) {
                if (pintuan_pricex > PriceX * 0.9) {
                    $(document).scrollTop(0);
                    layer.tips("2人团价格应低于现价的90%!", "input[name=pintuan_pricex]");
                    return false;
                }
            }
            if (pintuan_people > 2) {
                if (pintuan_pricex > PriceX * 0.8) {
                    $(document).scrollTop(0);
                    layer.tips("多人团价格应低于现价的80%!", "input[name=pintuan_pricex]");
                    return false;
                }
            }*/
            if (is_Tj == 1 && pintuan_pricex <= pintuan_prices) {
                $(document).scrollTop(0);
                layer.tips("拼团价格必须大于拼团供货价!", "input[name=pintuan_pricex]");
                return false;
            }
        }

        //检查产品图片，至少上传一张
        if (pic == undefined || pic == '') {
            $(document).scrollTop(0);
            layer.tips("请上传图片", "#ImgUpload");
            return false;
        }

        //判断简短简介必填
        /*if (BriefDescription == '') {
            $(document).scrollTop(0);
            layer.tips("请填写商品简介", "#BriefDescription");
            return false;
        }*/

        var Countall = $("input[name=Count]");
        var Type_IDls = $("#Type_ID").val();

        if (Type_IDls != 0) {
            var Txt_PriceSon = $("input[name*='Txt_PriceSon']");
            var Txt_NumberSon = $("input[name*='Txt_NumberSon']");
            var Txt_CountSon = $("input[name*='Txt_CountSon']");
            var Txt_PriceG = $("input[name*='Txt_PriceG']");
            var Txt_PtPrice = $("input[name*='pt_pricex']");
            var Txt_PriceSonarr = [];
            var Txt_PriceGarr = [];
            var Txt_NumberSonarr = [];
            var Txt_PtPriceArr = [];
            var isgo = 0;

            if ($('#attrTable input[type="checkbox"]:checked').length <= 0) {
                $(document).scrollTop(100);
                layer.tips('请选择属性', '#attrTable', {tips: 3});
                isgo = 1;
                return false;
            }
            Txt_PriceSon.each(function () {
                if (!re.test($(this).val())) {
                    $(this).focus();
                    layer.tips('属性价格填写错误！', $(this));
                    isgo = 1;
                    return false;
                } else {
                    if ($(this).val() == 0) {
                        $(this).val(PriceX);
                    }
                    Txt_PriceSonarr.push($(this).val());
                    isgo = 0;
                }
            });
            if (isgh == 1) {
                var Txt_PtPriceS = $("input[name*='pt_prices']");
                var Txt_PtPriceSArr = [];
                if (isgo == 0) {
                    Txt_PriceG.each(function () {
                        if (!re.test($(this).val())) {
                            $(this).focus();
                            layer.tips("供货价填写错误！", $(this));
                            isgo = 1;
                            return false;
                        } else {
                            if ($(this).val() == 0) {
                                $(this).val(supplyPrice);
                            }
                            Txt_PriceGarr.push($(this).val());
                            isgo = 0;
                        }
                    });
                }
                if (isgo == 0) {
                    for (var i = 0; i < Txt_PriceSonarr.length; i++) {
                        if (Number(Txt_PriceSonarr[i]) <= Number(Txt_PriceGarr[i])) {
                            $(document).scrollTop(500);
                            layer.tips("供货价不能大于商品价格，填错的价格：" + Txt_PriceGarr[i], "input[name*='Txt_PriceG']");
                            isgo = 1;
                            break;
                        }
                    }
                }
                /*if (isgo == 0) {
                    for (var i = 0; i < Txt_PriceSonarr.length; i++) {
                        var pricegbfb = Number(Txt_PriceGarr[i]) / Number(Txt_PriceSonarr[i]);
                        if (pricegbfb > 0.75) {
                            $(document).scrollTop(100);
                            layer.tips("供货价必须小于商品价格的75%，填错的价格：" + Txt_PriceGarr[i], "input[name*='Txt_PriceG']");
                            isgo = 1;
                            break;
                        }
                    }
                }*/
            }
            if (is_pt) {
                if (isgo == 0) {
                    Txt_PtPrice.each(function () {
                        if (!re.test($(this).val())) {
                            $(this).focus();
                            layer.tips("拼团价填写错误!", $(this));
                            isgo = 1;
                            return false;
                        } else {
                            if ($(this).val() == 0) {
                                if (is_xs != 1) {
                                    $(this).val(pintuan_pricex);
                                } else {
                                    $(this).val(flash_price);
                                }
                            }
                            Txt_PtPriceArr.push($(this).val());
                            isgo = 0;
                        }
                    })
                }
                if (isgh == 1) {
                    if (isgo == 0) {
                        Txt_PtPriceS.each(function () {
                            if (!re.test($(this).val())) {
                                $(this).focus();
                                layer.tips("拼团供货价填写错误!", $(this));
                                isgo = 1;
                                return false;
                            } else {
                                if ($(this).val() == 0) {
                                    $(this).val(pintuan_prices);
                                }
                                Txt_PtPriceSArr.push($(this).val());
                                isgo = 0;
                            }
                        })
                    }
                    /*if (isgo == 0) {
                        for (var i = 0; i < Txt_PtPriceArr.length; i++) {
                            var pt_pricegbfb = Number(Txt_PtPriceSArr[i]) / Number(Txt_PtPriceArr[i]);
                            if (pt_pricegbfb >= 1) {
                                $(document).scrollTop(100);
                                layer.tips("拼团供货价必须小于拼团价，填错的价格：" + Txt_PtPriceSArr[i], "input[name*='pt_prices']");
                                isgo = 1;
                                break;
                            }
                        }
                    }*/
                }







                /*if (isgo == 0) {
                    for (var i = 0; i < Txt_PtPriceArr.length; i++) {
                        var pt_price_per = Number(Txt_PtPriceArr[i]) / Number(Txt_PriceSonarr[i]);
                        if (pintuan_people == 2) {
                            if (pt_price_per >= 0.9) {
                                $(document).scrollTop(100);
                                layer.tips("2人团价格必须小于现价的90%，填错的价格：" + Txt_PtPriceArr[i], "input[name*='pt_pricex']");
                                isgo = 1;
                                break;
                            }
                        } else if (pintuan_people > 2) {
                            if (pt_price_per >= 0.9) {
                                $(document).scrollTop(100);
                                layer.tips("多人团价格必须小于现价的80%，填错的价格：" + Txt_PtPriceArr[i], "input[name*='pt_pricex']");
                                isgo = 1;
                                break;
                            }
                        }
                    }
                }*/
            }
            if (isgo == 0) {
                Txt_NumberSon.each(function () {
                    if (!renum.test($(this).val())) {
                        $(this).focus();
                        layer.tips('编号填写错误！', $(this));
                        isgo = 1;
                        return false;
                    } else {
                        if ($(this).val() != 0) {
                            Txt_NumberSonarr.push($(this).val());
                        }
                    }
                });
                if (isgo == 0) {
                    var s = Txt_NumberSonarr.join(",") + ",";
                    for (var i = 0; i < Txt_NumberSonarr.length; i++) {
                        if (s.replace(Txt_NumberSonarr[i] + ",", "").indexOf(Txt_NumberSonarr[i] + ",") > -1) {
                            $(document).scrollTop(100);
                            layer.alert("编号重复：" + Txt_NumberSonarr[i]);
                            isgo = 1;
                            break;
                        }
                    }
                }
            }
            if (isgo == 0) {
                Txt_CountSon.each(function () {
                    if (!recount.test($(this).val())) {
                        $(this).focus();
                        layer.tips("库存填写错误！", $(this));
                        isgo = 1;
                        return false;
                    } else {
                        isgo = 0;
                    }
                });
            }
            if (isgo == 1) {
                return false;
            }
            var Counts = 0;
            $("#process input[name^='Txt_CountSon']").each(function () {
                Counts += Number($(this).val());
            });
            Countall.val(Counts);
        }
        var Txt_XsPriceSArr = [];
        if (is_xs == 1) {
            var Txt_XsPriceX = $("input[name*='xs_pricex']");
            Txt_XsPriceX.each(function () {
                if (!re.test($(this).val())) {
                    $(this).focus();
                    layer.tips("限时抢购价填写错误!", $(this));
                    return false;
                } else {
                    if ($(this).val() != 0 && $(this).val() != 'undefined') {
                        Txt_XsPriceSArr.push($(this).val());
                    } else {
                        $(this).val(flash_price);
                    }
                }

            })
        }


        //根据订单类型，检查是否填写产品重量（实物产品必须填写重量）
        if (ordertype == 0) {

            if (Products_Weight <= 0 || isNaN(Products_Weight)) {
                $("#Products_Weight").focus();
                layer.tips("请填写产品重量！", "#Products_Weight");
                return false;
            }
        }

        if (Countall.val() == 0) {
            layer.tips("库存不能为零！", "#totalCount");
            return false;
        }

        if (global_obj.check_form($('*[notnull]'))) {
            return false
        }
        var re_number = /^\d+(?=\.{0,1}\d+$|$)/;  //判断字符串是否为数字     //判断正整数 /^[1-9]+[0-9]*]*$/
        $("form input[type='text']").each(function () {
            if ($(this).attr('data-type') == 'number') {
                var value = $(this).val();
                if (value != '') {
                    if (!re_number.test(value)) {
                        $(this).css('border', '1px solid red');
                        $(this).focus();
                        return false;
                    } else {
                        $(this).css('border', '1px solid #666');
                    }
                }
            }
        });

        //属性填写
        $("#skutableval").find("input.l-text").each(function(){
            var sefval = $(this).val();
            $(this).attr("value",sefval);
        });
        $("#attrs input[type=checkbox]").each(function(){
            if($(this).is(':checked')){
                $(this).attr("checked","checked");
            }
        });
        $("#tableeval").val($("#skutableval").html());
        $("#tableevals").val($("#attrs").html());

        me.attr('status', '1').val('提交中...');
        $.ajax({
            type: 'post',
            url: '?',
            data: me.parents('form').serialize(),
            success: function (data) {
                me.attr('status', '0').val('提交保存');
                if (data.errorCode == 0) {
                    layer.alert(data.msg, function () {
                        location.href = data.url;
                    });
                } else {
                    layer.alert(data.msg, function () {
                        if (data.url) {
                            location.href = data.url;
                        }
                    });
                }
            },
            dataType: 'json'
        });
    });
});