// JavaScript Document
/*edit in 20160318*/
/**
 * 点选可选属性或改变数量时修改商品价格的函数
 */
function changePrice(t)
{       
    var aa = $(t).attr('title');      
    var attr = getSelectedAttributes(document.forms['addtocart_form']);
 
    var attrnew = new Array();
    var qty = document.forms['addtocart_form'].elements['Qty'].value;
    var no_attr_price  =  document.forms['addtocart_form'].elements['no_attr_price'].value;
    var formSeriall = $('#addtocart_form').serialize();
    var param = {'aa':aa,'formSeriall':formSeriall,action:'getPrice',no_attr_price:no_attr_price,'ProductsID':Products_ID,'attr':attr,'qty':qty};
	$.get(shop_ajax_url,param,function(data){
		if (data.status = 0){
			alert(data.msg);
		}else  {
			if(data.product_property_id){
			    $('input[name=spec_list]').val(data.product_property_id);
				$('.now_price price').html(data.result);
				//$('#cur-price-txt').html(data.result * qty); //返回的价格变化
				$('#stock_val').html(data.property_count);	//返回的库存变化
				$('#number_id').html(data.number_id);	//返回的关联属性的id
			}
			var resultarrays = data.result_arrays;
			$('#addtocart_form .spec a').each(function(){
				var that = this;
				if($(that).attr('url').length > 0){
					if($.inArray($(that).attr('title'), resultarrays) >= 0){
						$(that).css('color','#333');
						$(that).attr('aria-disabled','false');
						$(that).css('cursor','pointer');
						$(that).attr('onclick','changeAtt(this)');
					}else{
						$(that).css('color','#CDCDCD');
						$(that).attr('aria-disabled','true');
						$(that).css('cursor','not-allowed');
						$(that).removeAttr('onclick');
						$(that).removeClass('cattsel');
					}
				}
			});
			//自动组合
			$('#addtocart_form .spec:first .cattsel:first').parent('.spec').parent('.pub').siblings('.pub_attr').each(function(){
				var t = this;
				$(t).find('a[aria-disabled=false]:first').click();
			});
	    }
    },'json');
}
function changeAtt(t) {
    t.lastChild.checked = 'checked';
    for (var i = 0; i<t.parentNode.childNodes.length; i++) {
        if (t.parentNode.childNodes[i].className == 'cattsel') {
            t.parentNode.childNodes[i].className = '';
        }
    }
    t.className = 'cattsel';
	if($(t).attr('url') <= 0){
        changePrice(t);
	}else{
	    var attr_values = '';
	    $('#addtocart_form .spec a.cattsel').each(function(){
		     attr_values += $(this).attr('title')+'|';
		});
		$('body').css({'cursor':'wait'});
	    $.get(shop_ajax_url,{'action':'get_attr_id','attr_values':attr_values,'ProductsID':Products_ID},function(data){
		    $('body').css({'cursor':'auto'});
		    if(data.product_property_id){
			    $('input[name=spec_list]').val(data.product_property_id);
				$('.now_price price').html(data.result);
				$('#stock_val').html(data.property_count);	//返回的库存变化
				$('#number_id').html(data.number_id);	//返回的关联属性的id
				$('.count_box').show();
			}else if(data.status == 0){
			    alert(data.msg);
			}
		},'json');
	}
}
/**

 * 获得选定的商品属性

 */

function getSelectedAttributes(formBuy){
    var spec_arr = new Array();
    var j = 0;
    for (i = 0; i < formBuy.elements.length; i ++ ){
        var prefix = formBuy.elements[i].name.substr(0, 5);
        if (prefix == 'spec_' && (((formBuy.elements[i].type == 'radio' && formBuy.elements[i].parentNode.className == 'cattsel') || (formBuy.elements[i].type == 'checkbox' && formBuy.elements[i].checked)) || formBuy.elements[i].tagName == 'SELECT')){
		    spec_arr[j] = formBuy.elements[i].value;
		    j++ ;
		}
    }
    return spec_arr;
}

$(document).ready(function(){
    if($('#addtocart_form .spec:first a:first').length > 0){
	    $('#addtocart_form .spec:first a:first').click();
	}
})
