//保存最后的组合结果信息
var SKUResult = {};
var pricesarr = new Array();
var pt_pricesarr = [];
var flashsale_pricesarr = [];
var countarr = new Array();
var attrvsarr = new Array();
var has_active = typeof has_active != 'undefined' ? has_active : 0;
var order_type = typeof order_type != 'undefined' ? order_type : '';
//获得对象的key
function getObjKeys(obj) {	
    if (obj !== Object(obj)) throw new TypeError('Invalid object');
    var keys = [];
    for (var key in obj)
        if (Object.prototype.hasOwnProperty.call(obj, key))
            keys[keys.length] = key;
    return keys;
}
var lengo = 0;
$.each(keys,function(){
	lengo++;			
			});
//把组合的key放入结果集SKUResult
function add2SKUResult(combArrItem, sku) {
	var key = combArrItem.join(";");
    if(SKUResult[key]) {//SKU信息key属性·
        SKUResult[key].count += sku.Txt_CountSon;
        SKUResult[key].prices.push(sku.Txt_PriceSon);
		SKUResult[key].shu_priceg.push(sku.Txt_PriceG);
    } else {
        SKUResult[key] = {
            count : sku.Txt_CountSon,
            prices : [sku.Txt_PriceSon],
			shu_priceg : [sku.Txt_PriceG]
        };
    }
}

//初始化得到结果集
function initSKU() {
		$('.pro_attribute').each(function () {
		attrvs = $.trim($(this).first().text());
		attrvsarr.push(attrvs);
		});
        var discount = $('.level_pro').attr("proportion"); // 获得会员优享折扣
    	discount = discount ? discount : 0;
		firstval = attrvsarr[0];
		firstimg = $(".imgchege img").attr("src");
		showattr = '请选择 ' + attrvsarr.join(" ");
		$('#showattr').text(showattr);
		var i, j, skuKeys = getObjKeys(data);	
		for(i = 0; i < skuKeys.length; i++) {
        var skuKey = skuKeys[i];//一条SKU信息key
        var sku = data[skuKey];	//一条SKU信息value		
        var skuKeyAttrs = skuKey.split(";"); //SKU信息key属性值数组
		skuKeyAttrs.sort(function(value1, value2) {
			return parseInt(value1) - parseInt(value2);
		});

        //对每个SKU信息key属性值进行拆分组合
		var combArr = combInArray(skuKeyAttrs);
		for(j = 0; j < combArr.length; j++) {
			add2SKUResult(combArr[j], sku);
		}
        //结果集接放入SKUResult
        SKUResult[skuKeyAttrs.join(";")] = {
            count:sku.Txt_CountSon,
            prices:[sku.Txt_PriceSon],
			shu_priceg:[sku.Txt_PriceG],
            pt_priceg:[sku.Txt_PtPriceSon],
            xs_priceg:[sku.Txt_XsPriceSon],
        }
		
		pricesarr.push(sku.Txt_PriceSon);
		pt_pricesarr.push(sku.Txt_PtPriceSon);
		flashsale_pricesarr.push(sku.Txt_XsPriceSon);
		countarr.push(sku.Txt_CountSon);
    }

    var show_prices_arr = has_active == 1 ? flashsale_pricesarr : (order_type == 'pintuan' ? pt_pricesarr : pricesarr);

	maxPce = Math.max.apply(this, show_prices_arr) * (1 - (discount / 100));// 获取会员优享价
	minPce = Math.min.apply(this, show_prices_arr) * (1 - (discount / 100));// 获取会员优享价
	if (maxPce == minPce) {
		$('#price').text(minPce);
	}else{
		$('#price').text(minPce + "-" + maxPce);
	}	
	contsum = eval(countarr.join('+'));
	$('#count').text('库存'+contsum+'件');
}

/**
 * 从数组中生成指定长度的组合
 * 方法: 先生成[0,1...]形式的数组, 然后根据0,1从原数组取元素，得到组合数组
 */
function combInArray(aData) {
	if(!aData || !aData.length) {
		return [];
	}

	var len = aData.length;
	var aResult = [];

	for(var n = 1; n < len; n++) {
		var aaFlags = getCombFlags(len, n);
		while(aaFlags.length) {
			var aFlag = aaFlags.shift();
			var aComb = [];
			for(var i = 0; i < len; i++) {
				aFlag[i] && aComb.push(aData[i]);
			}
			aResult.push(aComb);
		}
	}
	
	return aResult;
}


/**
 * 得到从 m 元素中取 n 元素的所有组合
 * 结果为[0,1...]形式的数组, 1表示选中，0表示不选
 */
function getCombFlags(m, n) {
	if(!n || n < 1) {
		return [];
	}

	var aResult = [];
	var aFlag = [];
	var bNext = true;
	var i, j, iCnt1;

	for (i = 0; i < m; i++) {
		aFlag[i] = i < n ? 1 : 0;
	}

	aResult.push(aFlag.concat());

	while (bNext) {
		iCnt1 = 0;
		for (i = 0; i < m - 1; i++) {
			if (aFlag[i] == 1 && aFlag[i+1] == 0) {
				for(j = 0; j < i; j++) {
					aFlag[j] = j < iCnt1 ? 1 : 0;
				}
				aFlag[i] = 0;
				aFlag[i+1] = 1;
				var aTmp = aFlag.concat();
				aResult.push(aTmp);
				if(aTmp.slice(-n).join("").indexOf('0') == -1) {
					bNext = false;
				}
				break;
			}
			aFlag[i] == 1 && iCnt1++;
		}
	}
	return aResult;
} 



//初始化用户选择事件
$(function() {	
	initSKU();	
	$('.sku').each(function() {
		var self = $(this);
		var attr_id = self.attr('attr_id');
		if(!SKUResult[attr_id]) {
			self.attr('disabled', 'disabled');
		}
	}).click(function() {
		var self = $(this);
		//选中自己，兄弟节点取消选中		
		self.toggleClass('sku_x').siblings().removeClass('sku_x');
		cleararv = $.trim(self.parent().text());
		attrid = self.attr('attr_id');
		if (cleararv == firstval) {
		if (self.attr("class") == 'sku') {
		$(".imgchege img").attr("src",firstimg);
		}else{
		if (imgdata != '') {
		if (imgdata[firstval][attrid] == '') {
		$(".imgchege img").attr("src",firstimg);	
		}else{
		$(".imgchege img").attr("src",imgdata[firstval][attrid]);
		}
		}else{
		$(".imgchege img").attr("src",firstimg);
		}		
		}
		}
		if (self.attr("class") == 'sku') {		
		attrvsarr.push(cleararv);				
		}else{		
		if ($.inArray(cleararv,attrvsarr) >= 0) {
		attrvsarr.splice($.inArray(cleararv,attrvsarr),1);
		}		
		}
		showattr = '请选择 ' + attrvsarr.join(" ");
		$('#showattr').text(showattr);
		//已经选择的节点
		var selectedObjs = $('.sku_x');
		if(selectedObjs.length) {
			//获得组合key价格
			var selectedIds = [];
			selectedObjs.each(function() {				
				selectedIds.push($(this).attr('attr_id'));
			});
			selectedIds.sort(function(value1, value2) {
				return parseInt(value1) - parseInt(value2);
			});
			var len = selectedIds.length;
			lengglo = len;
			lengogglo = lengo;
			if (len == lengo) {
				// has_active 为是否有限时抢购
			var prices = has_active == 1  && SKUResult[selectedIds.join(';')].xs_priceg != 'undefined' && SKUResult[selectedIds.join(';')].xs_priceg != '' ?  SKUResult[selectedIds.join(';')].xs_priceg : (order_type == 'pintuan' ? SKUResult[selectedIds.join(';')].pt_priceg : SKUResult[selectedIds.join(';')].prices);
			var discount = $('.level_pro').attr("proportion");  // 获取会员优享折扣
                discount = discount ? discount : 0;
			var count = SKUResult[selectedIds.join(';')].count;
			var shu_priceg =  SKUResult[selectedIds.join(';')].shu_priceg;
			var maxPrice = Math.max.apply(Math, prices)  * (1 - (discount / 100));  // 获取会员优享价
			var minPrice = Math.min.apply(Math, prices) * (1 - (discount / 100));  // 获取会员优享价

			if (maxPrice == minPrice) {
			$('#price').text(maxPrice);
			}else{
			$('#price').text(maxPrice > minPrice ? minPrice + "-" + maxPrice : maxPrice);
			}			
			$('#count').text('库存'+count+'件');
			$('#shu_priceg').val(shu_priceg);
			okprice = maxPrice;
			okcount = count;
			$('#qty_selector input[name=Qty]').attr("value", 1);
			var attrxx = new Array();
			$('.sku_x').each(function() {
			var attrvsv = $(this).val();
			attrxx.push(attrvsv);
			});	
			showattr = '已选 ' + attrxx.join(" ");
			$('#showattr').text(showattr);
			}else{
			if (minPce == maxPce) {
			$('#price').text(minPce);
			}else{
			$('#price').text(minPce + "-" + maxPce);
			}						
			$('#count').text('库存'+contsum+'件');
			}
			
			//用已选中的节点验证待测试节点 underTestObjs
			$(".sku,.sku_d").not(selectedObjs).not(self).each(function() {
				var siblingsSelectedObj = $(this).siblings('.sku_x');
				var testAttrIds = [];//从选中节点中去掉选中的兄弟节点
				if(siblingsSelectedObj.length) {
					var siblingsSelectedObjId = siblingsSelectedObj.attr('attr_id');
					for(var i = 0; i < len; i++) {
						(selectedIds[i] != siblingsSelectedObjId) && testAttrIds.push(selectedIds[i]);
					}
				} else {
					testAttrIds = selectedIds.concat();
				}
				testAttrIds = testAttrIds.concat($(this).attr('attr_id'));
				testAttrIds.sort(function(value1, value2) {
					return parseInt(value1) - parseInt(value2);
				});
				if(!SKUResult[testAttrIds.join(';')]) {
					$(this).attr('disabled', 'disabled').attr('class', 'sku_d');
				} else {
					$(this).removeAttr('disabled').attr('class', 'sku');
				}
			});
		} else {
			//设置默认价格
			if (minPce == maxPce) {
			$('#price').text(minPce);
			}else{
			$('#price').text(minPce + "-" + maxPce);
			}
			//设置属性状态
			$('.sku,.sku_d').each(function() {
				SKUResult[$(this).attr('attr_id')] ? $(this).removeAttr('disabled').attr('class', 'sku') : $(this).attr('disabled', 'disabled').attr('class', 'sku_d');
			})
		}
	});
});