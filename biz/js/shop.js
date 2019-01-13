var step = {
	//组合数组
	doExchange: function (doubleArrays) {
		var len = doubleArrays.length;
		if (len >= 2) {
			var arr1 = doubleArrays[0];
			var arr2 = doubleArrays[1];
			var len1 = doubleArrays[0].length;
			var len2 = doubleArrays[1].length;
			var newlen = len1 * len2;
			var temp = new Array(newlen);
			var index = 0;
			for (var i = 0; i < len1; i++) {
				for (var j = 0; j < len2; j++) {
					temp[index] = arr1[i] + "," + arr2[j];
					index++;
				}
			}
			var newArray = new Array(len - 1);
			newArray[0] = temp;
			if (len > 2) {
				var _count = 1;
				for (var i = 2; i < len; i++) {
					newArray[_count] = doubleArrays[i];
					_count++;
				}
			}
			//console.log(newArray);
			return step.doExchange(newArray);
		}
		else {
			return doubleArrays[0];
		}
	},
	//合并行
	hebingFunction: function () {
		$.fn.mergeCell = function (options) {
			return this.each(function () {
				var cols = options.cols;
				for (var i = cols.length - 1; cols[i] != undefined; i--) {
					// fixbug console调试
					//console.debug(options);
					mergeCell($(this), cols[i]);
				}
				dispose($(this));
			});
		};
		function mergeCell($table, colIndex) {
			$table.data('col-content', ''); // 存放单元格内容
			$table.data('col-rowspan', 1); // 存放计算的rowspan值 默认为1
			$table.data('col-td', $()); // 存放发现的第一个与前一行比较结果不同td(jQuery封装过的), 默认一个"空"的jquery对象
			$table.data('trNum', $('tbody tr', $table).length); // 要处理表格的总行数, 用于最后一行做特殊处理时进行判断之用
			// 进行"扫面"处理 关键是定位col-td, 和其对应的rowspan
			$('tbody tr', $table).each(function (index) {
				// td:eq中的colIndex即列索引
				var $td = $('td:eq(' + colIndex + ')', this);
				// 取出单元格的当前内容
				var currentContent = $td.html();
				// 第一次时走此分支
				if ($table.data('col-content') == '') {
					$table.data('col-content', currentContent);
					$table.data('col-td', $td);
				} else {
					// 上一行与当前行内容相同
					if ($table.data('col-content') == currentContent) {
						// 上一行与当前行内容相同则col-rowspan累加, 保存新值
						var rowspan = $table.data('col-rowspan') + 1;
						$table.data('col-rowspan', rowspan);
						// 值得注意的是 如果用了$td.remove()就会对其他列的处理造成影响
						$td.hide();
						// 最后一行的情况比较特殊一点
						// 比如最后2行 td中的内容是一样的, 那么到最后一行就应该把此时的col-td里保存的td设置rowspan
						if (++index == $table.data('trNum'))
							$table.data('col-td').attr('rowspan', $table.data('col-rowspan'));
					} else { // 上一行与当前行内容不同
						// col-rowspan默认为1, 如果统计出的col-rowspan没有变化, 不处理
						if ($table.data('col-rowspan') != 1) {
							$table.data('col-td').attr('rowspan', $table.data('col-rowspan'));
						}
						// 保存第一次出现不同内容的td, 和其内容, 重置col-rowspan
						$table.data('col-td', $td);
						$table.data('col-content', $td.html());
						$table.data('col-rowspan', 1);
					}
				}
			});
		}
		// 同样是个private函数 清理内存之用
		function dispose($table) {
			$table.removeData();
		}
	}
}

//添加删除价格表的元素
function add_del_html(act, type){


    if ($('#process tr').length > 0) {
        $('#process tr').each(function(i){
            var tr = $('#process tr').eq(i);
            if (tr.html().indexOf('</th>') > -1) {
                if (act == 'add') {
                    if (type == 'tj_g') {
                        tr.append('<th class="pt" style=\"width:70px;border: 1px #ddd solid;\">供货价</th>');
                    } else if (type == 'pt_x') {
                        tr.append('<th class="pt" style=\"width:70px;border: 1px #ddd solid;\">拼团价</th>');
                    } else if (type == 'pt_s') {
                        tr.append('<th class="pt" style=\"width:70px;border: 1px #ddd solid;\">拼团供货价</th>');
                    }else if (type == 'xs') {
						tr.append('<th class="xs" style=\"width:70px;border: 1px #ddd solid;\">限时抢购价</th>');
					}
                } else if (act == 'del') {
                    tr.find('th').each(function(j){     //老数据过度
                        if (type == 'tj_g') {
							var th = $('#process tr').eq(i).find('th.pt').eq(j);
							if (th.text() == '供货价') {
								th.detach();
							}
                        } else if (type == 'pt_x') {
							var th = $('#process tr').eq(i).find('th.pt').eq(j);
							if (th.text() == '拼团价') {
								th.detach();
							}
                        } else if (type == 'pt_s') {
							var th = $('#process tr').eq(i).find('th.pt').eq(j);
							if (th.text() == '拼团供货价') {
								th.detach();
							}
                        } else if (type == 'xs') {
							var th = $('#process tr').eq(i).find('th.xs').eq(j);
							if (th.text() == '限时抢购价') {
								th.detach();
							}
						}
                    });
                }
            } else {
                if (act == 'add') {
                    if (type == 'tj_g') {
                        tr.append('<td class="pt" style=\"border: 1px #ddd solid;border-top: 0;border-left: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name="Txt_PriceG[]" value="0" class="l-text" type="text" placeholder="请输入供货价格" /></td>');
                    } else if (type == 'pt_x') {
                        tr.append('<td class="pt" style=\"border: 1px #ddd solid;border-top: 0;border-left: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name="pt_pricex[]" value="0" class="l-text" type="text" placeholder="请输入拼团价格" /></td>');
                    } else if (type == 'pt_s') {
                        tr.append('<td class="pt" style=\"border: 1px #ddd solid;border-top: 0;border-left: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name="pt_prices[]" value="0" class="l-text" type="text" placeholder="请输入拼团供货价格" /></td>');
                    } else if (type == 'xs') {
						tr.append('<td class="xs" style=\"border: 1px #ddd solid;border-top: 0;border-left: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name="xs_pricex[]" value="0" class="l-text" type="text" placeholder="请输入限时价格" onblur=\"check_flashsale(this)\" /></td>');
					}
                } else if (act == 'del') {
                    if (type == 'tj_g') {
                        tr.find('input[name="Txt_PriceG[]"]').parents('td.pt').detach();
                    } else if (type == 'pt_x') {
                        tr.find('input[name="pt_pricex[]"]').parents('td.pt').detach();
                    } else if (type == 'pt_s') {
                        tr.find('input[name="pt_prices[]"]').parents('td.pt').detach();
                    } else if (type == 'xs') {
						tr.find('td.xs').detach();
					}
                }

            }
        });
    }
}


//是否参团选择，改变属性设置的表格
function pro_attr_table(){
    var attr_table = $("#skutableval").html();  //属性设置表格内容
    console.log(skutableval);
	if (attr_table == '') return false;

    if ($('#is_Tj').length > 0) {
        var is_tj = $('#is_Tj').attr('checked');  //是否推荐到平台
    } else {
        var url = location.href;
        if (url.indexOf('products_add.php') > -1 || url.indexOf('products_edit.php') > -1) {
            var is_tj = 0;  //是否推荐到平台
        } else if (url.indexOf('products_supply.php') > -1) {
            var is_tj = 1;  //是否推荐到平台
        }
    }
    var is_pt = $("#is_pintuan").attr('checked');   //是否参加拼团
	var is_xs = $("#is_flashsale").attr('checked');   //是否参加限时抢购

    //判断是否有推荐的供货价
    //判断是否有参团价 和 参团供货价
    var tj_price = attr_table.indexOf('>供货价<') > -1 ? true : false;   //是否有 推荐供货价
    var pt_price = attr_table.indexOf('>拼团价<') > -1 ? true : false;   //是否有 拼团价
    var pt_tj_price = attr_table.indexOf('>拼团供货价<') > -1 ? true : false;   //是否有 拼团供货价
	var xs_price = attr_table.indexOf('>限时抢购价<') > -1 ? true : false;   //是否有 限时抢购价
    if (is_tj) {     //推荐到平台
        if (!tj_price) {
            //没有供货价，写入供货价
            add_del_html('add', 'tj_g');
        }

        if (is_pt) {     //参团
            if (!pt_price) {
                //没有拼团价，写入拼团价
                add_del_html('add', 'pt_x');
            }
            if (!pt_tj_price) {
                //没有拼团供货价，写入拼团供货价
                add_del_html('add', 'pt_s');
            }
        } else {    //不参团
            if (pt_price) {
                //没有拼团价，写入拼团价
                add_del_html('del', 'pt_x');
            }
            if (pt_tj_price) {
                //没有拼团供货价，写入拼团供货价
                add_del_html('del', 'pt_s');
            }
        }

		if (is_xs) {     //参加限时抢购
			if (!xs_price) {
				//没有限时抢购价，写入限时抢购价
				add_del_html('add', 'xs');
			}
		} else {    //不参加限时抢购
			if (xs_price) {
				//没有限时抢购价，写入限时抢购价
				add_del_html('del', 'xs');
			}
		}
    } else {    //不推荐
        if (tj_price) {
            //没有供货价，写入供货价
            add_del_html('del', 'tj_g');
        }

        if (is_pt) {     //参团
            if (!pt_price) {
                //没有拼团价，写入拼团价
                add_del_html('add', 'pt_x');
            }
            if (pt_tj_price) {
                //没有拼团供货价，写入拼团供货价
                add_del_html('del', 'pt_s');
            }
        } else {    //不参团
            if (pt_price) {
                //没有拼团价，写入拼团价
                add_del_html('del', 'pt_x');
            }
            if (pt_tj_price) {
                //没有拼团供货价，写入拼团供货价
                add_del_html('del', 'pt_s');
            }
        }

		if (is_xs) {     //参加限时抢购
			if (!xs_price) {
				//没有限时抢购价，写入限时抢购价
				add_del_html('add', 'xs');
			}
		} else {    //不参团限时抢购
			if (xs_price) {
				//没有限时抢购价，写入限时抢购价
				add_del_html('del', 'xs');
			}
		}
    }
}

var shop_obj={
	biz_edit_init:function(){
		$('#biz_edit').submit(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
			$('#biz_edit input:submit').attr('disabled', true);
			return true;
		});
		var type = $("input[name=FinanceType2]").val();
		if(type == 0){
			$("input[name=Profit]").attr('disabled', true);
			$("input[name=Profit]").removeClass('form_input');
			$("input[name=Profit]").addClass('form_input_disable');
		}else {
			$("input[name=Profit]").attr('disabled', false);
			$("input[name=Profit]").removeClass('form_input_disable');
			$("input[name=Profit]").addClass('form_input');
		}






	},
	
	products_list_init:function(){
		$('a[href=#search]').click(function(){
			$('form.search').slideDown();
			return false;
		});	
		$("#search_form .output_btn").click(function(){
			window.location='/biz/output.php?'+$('#search_form').serialize()+'&type=product_gross_info';
		});
	},
	
	orders_init:function(){
		$("#search_form .output_btn").click(function(){
			window.location='/biz/output.php?'+$('#search_form').serialize()+'&type=order_detail_list';
		});
		
		var logic = function( currentDateTime ){
	if( currentDateTime.getDay()==6 ){
		this.setOptions({
			minTime:'11:00'
		});
	}else
		this.setOptions({
			minTime:'8:00'
		});
};
$('#search_form input[name=AccTime_S], #search_form input[name=AccTime_E]').datetimepicker({
	lang:'ch',
	onChangeDateTime:logic,
	onShow:logic
});
		
		$('#order_list a.send_print').click(function(){
			var orderid = $(this).attr("ret");
			$('#select_template #linkid').attr("value",orderid);
			$('#select_template').leanModal();
		});
		
		$('#submit_form #checkall').click(function(){
			if($(this).is(":checked")){
				$("#submit_form input[name=OrderID\\[\\]]").attr("checked","true");
			}else{
				$("#submit_form input[name=OrderID\\[\\]]").removeAttr("checked");
			}			
		});
		
		$("#submit_form input[name=OrderID\\[\\]]").click(function(){
			if($(this).is(":checked")){
				var i = 1;
				$("#submit_form input[name=OrderID\\[\\]]").each(function(index, element) {
                    if(!$(this).is(":checked")){
						i=0;
					}
                });
				if(i==1){
					$("#submit_form #checkall").attr("checked","true");
				}
			}else{
				$("#submit_form #checkall").removeAttr("checked");
			}
		});
		
		// $("#submit_form label").click(function(){
			// var j = 0;
			// $("#submit_form input[name=OrderID\\[\\]]").each(function(index, element) {
                // if($(this).is(":checked")){
					// j=1;
				// }
            // });
			// if(j==1){
				// $('#select_template #linkid').attr("value",0);
				// $('#select_template').leanModal();
			// }else{
				// alert("请选择订单1212");
				// return false;
			// }
			
		// });
		
		$("#select_template label").click(function(){
			var templateid = $("#select_template #templates").val();
			if(templateid==0 || templateid == ""){
				alert("请选择运单模板");
				return false;
			}
			
			var linkid = $('#select_template #linkid').attr("value");
			if(linkid==0){
				$("#submit_form input[name=templateid]").attr("value",templateid);				
				$("#select_template,#lean_overlay").hide();
				window.open('/biz/orders/send_print.php?'+$('#submit_form').serialize());
			}else{
				$("#select_template,#lean_overlay").hide();
				window.open('/biz/orders/send_print.php?templateid='+templateid+'&OrderID='+linkid);
			}
			
		});
	},
	
	print_orders_init:function(){
		
		$('.r_nav, .ui-nav-tabs').hide();
		$('html,body').css('background','none');
		$('.iframe_content').removeClass('iframe_content');	
		$('.print_area input[name=print_close]').click(function(){
			$('#print_cont').fadeOut();
		});
		
		$('.print_area input[name=print_go]').click(function(){
			window.print();
		});
		$('.print_area input[name=print_close]').click(function(){
			$(window.parent.document).find('#print_cont').fadeOut();
		});
		
	},
	
	sales_init:function(){
		var logic = function( currentDateTime ){
	if( currentDateTime.getDay()==6 ){
		this.setOptions({
			minTime:'11:00'
		});
	}else
		this.setOptions({
			minTime:'8:00'
		});
};
$('#search_form input[name=AccTime_S], #search_form input[name=AccTime_E]').datetimepicker({
	lang:'ch',
	onChangeDateTime:logic,
	onShow:logic
});
		
		$("#search_form .output_btn").click(function(){
			window.location='/biz/output.php?'+$('#search_form').serialize()+'&type=sales_record_list';
		});
	},
	
	orders_send:function(){
		$('#order_send_form').submit(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
			$('#order_send_form input:submit').attr('disabled', true);
			return true;
		});
	},
	
	backorder_edit:function(){
		$("#reject_btn").click(function(){
			$("#btns").hide();
			$("#reject").show();
		});
		
		$("#goback_reject").click(function(){
			$("#reject").hide();
			$("#btns").show();
		});
		
		$('#reject_form').submit(function(){
			if(global_obj.check_form($('#reject_form *[notnull]'))){return false};
			$('#reject_form input:submit').attr('disabled', true);
			return true;
		});
		
		$("#recieve_btn").click(function(){
			$("#btns").hide();
			$("#recieve").show();
		});
		
		$("#goback_recieve").click(function(){
			$("#recieve").hide();
			$("#btns").show();
		});
		
		$('#recieve_form').submit(function(){
			if(global_obj.check_form($('#recieve_form *[notnull]'))){return false};
			$('#recieve_form input:submit').attr('disabled', true);
			return true;
		});
	},
	
	category_init:function(){
		// $('#category_form').submit(function(){
		// 	if(global_obj.check_form($('*[notnull]'))){return false};
		// 	$('#category_form input:submit').attr('disabled', true);
		// 	return true;
		// });
	},
	
	products_attr_edit_init:function(){
		shop_obj.products_attr_cu();
		var value  = parseInt($(".Attr_Input_Type").val());
		if(value == 0||value == 2){
			$("#Attr_Values").removeAttr('notnull');			
		}else{
			$("#Attr_Values").attr('notnull','');
		}
		$('#shop_attr_edit_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false};
		});
	
	},
	
	products_attr_cu:function(){
		//产品属性create 和  update 共用属性
		
		//如录入方式为手工录入和多行文本框，则禁用可选值textarea
		$(".Attr_Input_Type").click(function(){
			var value  = parseInt($(this).val());
			$("#Attr_Values").removeAttr('style');
			if(value == 0||value == 2){
				$("#Attr_Values").attr({"disabled":true});
				
			}else{
				$("#Attr_Values").removeAttr('disabled');
			}
		
		});
		
	},
	
	products_edit_init:function(){		
		$("#product_add_form").submit(function(){
			$("#skutableval").find("input.l-text").each(function(){
			var sefval = $(this).val();
			$(this).attr("value",sefval);
		});
		$("#attrs input[type=checkbox]").each(function(){
			if($(this).is(':checked')){			
			$(this).attr("checked","checked");
			}			
		});
						
			if(global_obj.check_form($('*[notnull]'))){return false};
			var tval = $("#skutableval").html();
			var tvals = $("#attrs").html();
			$("#tableeval").val(tval);
			$("#tableevals").val(tvals);
		});
		
		$("#product_edit_form").submit(function(){
			$("#skutableval").find("input.l-text").each(function(){
			var sefval = $(this).val();
			$(this).attr("value",sefval);
		});					
			if(global_obj.check_form($('*[notnull]'))){return false};
			$("#attrs input[type=checkbox]").each(function(){
			if($(this).is(':checked')){			
			$(this).attr("checked","checked");
			}			
		});
			var tval = $("#skutableval").html();
			var tvals = $("#attrs").html();
			$("#tableeval").val(tval);
			$("#tableevals").val(tvals);
		});
		shop_obj.products_form_init();
		var i = 0;
		$(document).on('click', '.pro_click',function() {
			gook = 0;
			if (changeok == 1) {
				i = 0;
				changeok = 0;
			}
			if (changeok == 3) {
				i = inum;
				changeok = 0;
			}
			var checkstu = 3;
			var titlearr = [] ;
			var arrayInfor = [];
			$.each($("#attrTable").find('tr').find('td:first'),function(){
				var proty = $(this);
				titlearr.push(proty.html());
			});
			var me = $(this);
			var arrlength = $("#attrTable").find('tr').length;
			var titleintro = me.parent().siblings('td:first').html();
			if (me.attr("checked")) {
				checkstu = 1;
				if (me.siblings('input:checked').length == 0) {
					i++;					
				}
			} else {
				checkstu = 0;
				if (me.siblings('input:checked').length == 0) {
					i--;					
				}
			}
			var checkedarrlength = titlearr.length;
			if (checkedarrlength == i) {
				gook = 1;
				layer.tips('温馨提示:<br>1、点击第一个属性的值可以上传对应的属性图片哦<br>2、注意重新改变复选框表格值将会清空哦', '#skutableval', {
					tips: [4, '#ff6600'],
					time:14000
				});
				$.each($("#attrTable").find('tr').find('td:nth-child(2)'),function(v,n){
					var prome = $(this);
					var lsarr = [];
					$.each(prome.find("input:checked"),function(){
						if (v==0) {
							lsarr.push('<a href="javascript:void(0);" class="addProPic" title="点击这里上传图片" data-pic="'+$(this).val()+'">'+$(this).attr("data-pro")+'</a>' + '<div class="LogoDetail_'+$(this).val()+'"></div>' + '<input type="hidden" name="attr_image['+$(this).val()+']" class="attr_image_'+$(this).val()+'" /> ');
						} else {
							lsarr.push($(this).attr("data-pro"));
						}
					});
					arrayInfor[v] = lsarr;
				});
				var arrayColumn = new Array();//指定列，用来合并哪些列
				columnIndex = 0;				
				$("#muliteadd").show();
				$("#skutableval").html('');
				step.hebingFunction();
				var table = $("<table id=\"process\" border=\"1\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%;text-align:center;border:none;line-Height:35px;\"></table>");
				table.appendTo($("#skutableval"));
				var thead = $("<thead></thead>");
				thead.appendTo(table);
				var trHead = $("<tr></tr>");
				trHead.appendTo(thead);
				//创建表头
				$.each(titlearr, function (index, item) {
					var td = $("<th style=\"border: 1px #ddd solid;border-right: 0;\">" + item + "</th>");
					td.appendTo(trHead);
					arrayColumn.push(columnIndex);
					columnIndex++;
				});
				if (isgh == 1) {
					var itemColumHead = "<th  style=\"width:70px;border: 1px #ddd solid;border-right: 0;\">价格</th><th style=\"width:70px;border: 1px #ddd solid;border-right: 0;\">库存</th><th style=\"width:70px;border: 1px #ddd solid;\">供货价</th>";
				}else{
					var itemColumHead = "<th  style=\"width:70px;border: 1px #ddd solid;border-right: 0;\">价格</th><th style=\"width:70px;border: 1px #ddd solid;\">库存</th>";
				}
                if (is_pt == 1) {
                    itemColumHead += "<th  style=\"width:70px;border: 1px #ddd solid;\">拼团价</th>";
                    if (isgh == 1) {
                        itemColumHead += "<th  style=\"width:70px;border: 1px #ddd solid;\">拼团供货价</th>";
                    }
                }
				if (is_xs == 1) {
					itemColumHead += "<th class='xs' style=\"width:70px;border: 1px #ddd solid;\">限时抢购价</th>";
				}
                $(itemColumHead).appendTo(trHead);
				var tbody = $("<tbody style=\"border: 1px #ddd solid;border-top: 0;border-right: 0;border-left: 1px #ddd solid;\"></tbody>");
				tbody.appendTo(table);
				var groupDate = step.doExchange(arrayInfor);
				if (groupDate.length > 0) {
					//创建行
					$.each(groupDate, function (index, item) {
						var td_array = item.split(",");
						var tr = $("<tr></tr>");
						tr.appendTo(tbody);
						$.each(td_array, function (i, values) {
							var td = $("<td style=\"border-bottom: 1px #ddd solid;border-top: 0;border-right: 0;border-left: 1px #ddd solid;\">" + values + "</td>");
							td.appendTo(tr);
						});
						var td1 = $("<td style=\"border: 1px #ddd solid;border-top: 0;border-right: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name=\"Txt_PriceSon[]\" class=\"Txt_PriceSon l-text\"  value=\"0\" class=\"l-text\" type=\"text\" placeholder=\"请输入价格\" onblur=\"check_price_son(this)\" /></td>");
						td1.appendTo(tr);

						if (isgh == 1) {
							var td3 = $("<td style=\"border: 1px #ddd solid;border-top: 0;border-right: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name=\"Txt_CountSon[]\" value=\"\" class=\"l-text\" type=\"text\" placeholder=\"请输入库存\" /></td>");
							td3.appendTo(tr);
							var td4 = $("<td style=\"border: 1px #ddd solid;border-top: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name=\"Txt_PriceG[]\" value=\"\" class=\"l-text\" type=\"text\" placeholder=\"请输入供货价\" /></td>");
							td4.appendTo(tr);
						}else{
							var td3 = $("<td style=\"border: 1px #ddd solid;border-top: 0;border-left: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name=\"Txt_CountSon[]\" value=\"\" class=\"l-text\" type=\"text\" placeholder=\"请输入库存\" /></td>");
							td3.appendTo(tr);
						}
                        if (is_pt == 1) {
                            var td5 = $("<td class='pt' style=\"border: 1px #ddd solid;border-top: 0;border-left: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name=\"pt_pricex[]\" value=\"0\" class=\"l-text\" type=\"text\" placeholder=\"请输入拼团价格\" /></td>");
                            td5.appendTo(tr);
                            if (isgh == 1) {
                                var td6 = $("<td class='pt' style=\"border: 1px #ddd solid;border-top: 0;border-left: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name=\"pt_prices[]\" value=\"0\" class=\"l-text\" type=\"text\" placeholder=\"请输入拼团供货价\" /></td>");
                                td6.appendTo(tr);
                            }
                        }

						if (is_xs == 1) {
							var td7 = $("<td class='xs' style=\"border: 1px #ddd solid;border-top: 0;border-left: 0;border-left: 1px #ddd solid;\" ><input style=\"line-height: 35px;border: none;outline:none;text-align:center;\" name=\"xs_pricex[]\" value=\"0\" class=\"l-text\" type=\"text\" placeholder=\"请输入限时抢购价\" onblur=\"check_flashsale(this)\" /></td>");
							td7.appendTo(tr);
						}
					});					
				}
				
				//结束创建Table表
				arrayColumn.pop();//删除数组中最后一项
				//合并单元格
				$(table).mergeCell({
					// 目前只有cols这么一个配置项, 用数组表示列的索引,从0开始
					cols: arrayColumn
				});				
			} else {
				$("#skutableval").empty();
			}
		if (checkstu != 3) {
			if (checkstu == 1) {
				me.attr("checked","checked");
			}else{
				me.removeAttr("checked");
			}
		}
		});
		$('.icon-plusPrice').click(function(){
			var attrname = $(this).prev().val();			
			if(attrname == '') {
				alert("空值不能添加！");
				return false;
			}
			var re = /^(0|[1-9][0-9]{0,9})(\.[0-9]{1,2})?$/;			
			if (!re.test(attrname)) {
				alert("价格批量添加值错误！");
				return false;
			}
			var Txt_PriceSon = $("input[name*='Txt_PriceSon']");
			Txt_PriceSon.each(function(){
			$(this).val(attrname);			
		});
		$(this).prev().val('');
		});

		$('.icon-plusCount').click(function(){
			var attrname = $(this).prev().val();			
			if(attrname == '') {
				alert("空值不能添加！");
				return false;
			}
			var re = /^(0|[1-9][0-9]*)$/;			
			if (!re.test(attrname)) {
				alert("库存批量添加值错误！");
				return false;
			}
			var Txt_CountSon = $("input[name*='Txt_CountSon']");
			Txt_CountSon.each(function(){
			$(this).val(attrname);			
		});
		$(this).prev().val('');
		});
		$('.icon-plusPriceG').click(function(){
			var attrname = $(this).prev().val();			
			if(attrname == '') {
				alert("空值不能添加！");
				return false;
			}
			var re = /^(0|[1-9][0-9]{0,9})(\.[0-9]{1,2})?$/;			
			if (!re.test(attrname)) {
				alert("供货价批量添加值错误！");
				return false;
			}
			var Txt_PriceG = $("input[name*='Txt_PriceG']");
			Txt_PriceG.each(function(){
			$(this).val(attrname);			
		});
		$(this).prev().val('');
		});
        //拼团价格批量添加
        $('.icon-plusPtPrice').click(function(){
            var me = $(this);
            var attrname = $(this).prev().val();
            if(attrname == '') {
                alert("空值不能添加！");
                return false;
            }
            var re = /^(0|[1-9][0-9]{0,9})(\.[0-9]{1,2})?$/;
            if (!re.test(attrname)) {
                layer.tips("拼团价格批量添加值错误！", me);
                return false;
            }
            var Txt_PtPrice = $("input[name*='pt_pricex']");
            Txt_PtPrice.each(function(){
                $(this).val(attrname);
            });
            $(this).prev().val('');
        });
        //拼团供货价格批量添加
        $('.icon-plusPtPriceG').click(function(){
            var me = $(this);
            var attrname = $(this).prev().val();
            if(attrname == '') {
                alert("空值不能添加！");
                return false;
            }
            var re = /^(0|[1-9][0-9]{0,9})(\.[0-9]{1,2})?$/;
            if (!re.test(attrname)) {
                layer.tips("拼团价格批量添加值错误！", me);
                return false;
            }
            var Txt_PtPriceG = $("input[name*='pt_prices']");
            Txt_PtPriceG.each(function(){
                $(this).val(attrname);
            });
            $(this).prev().val('');
        });
	},
	
	products_form_init:function(){
		$('a[href=#select_category]').each(function(){
			$(this).click(function(){
				$('#select_category').leanModal();
			});
		});
		
		var category = function(object1){
			var c_k = true;
			object1.parent().find("input").each(function(){
				if(!$(this).attr("checked")){
					c_k = false;
				}
			});
			if(c_k){
				object1.parent().prev("dt").find("input").attr("checked",true);
			}else{
				object1.parent().prev("dt").find("input").removeAttr("checked"); 
			}
		};
		
		$("#select_category .catlist input:checkbox").click(function(){
			var flag = $(this).attr("rel");
			if(flag == 1){
				if($(this).is(':checked')){
					$(this).parent().next("dd").find("input").attr("checked",true);
				}else{
					$(this).parent().next("dd").find("input").attr("checked",false);
				}
			}else{
				category($(this).parent());
			}
		});
		
		$("#products #Type_ID").change(function(){
			changeok = 1;
			if ($("#skutableval").length > 0) {
				$("#skutableval").empty();
			}
			var TypeID = $("#Type_ID").val();
			var ProductsID = 0;
			if (TypeID <= 0) {
				$("#attr_show").hide();
				$("#attr_table").hide();
				$("#muliteadd").hide();
			}else{
				$("#attr_show").show();
				$("#attr_table").show();
				$("#muliteadd").show();
				gook = 0;
			}
			if(TypeID.length > 0){
				$.ajax({
					type	: "POST",
					url		: "ajax.php",
					data	: "action=get_attr&UsersID="+$("#UsersID").val()+"&TypeID="+$("#Type_ID").val()+"&ProductsID="+$("#ProductsID").val(),
					dataType: "json",
					async : false,
					success	: function(data){
						
						if(data.content){
							$("#attrs").css("display","block");
							$("#attrs").html(data.content);
						}else{							
							$("#attrs").html('');
						}
					}
				});	
			} else {
				
			 $("#attrs").html('');
			   $("#Category").focus();
				
			}
		});
		
	},	
}