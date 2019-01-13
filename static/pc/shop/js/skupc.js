    //价格json	
    var sys_item = {
        "kucount": kucuncount,
        "price": pricemin+'-'+pricemax,
        "sys_attrprice": skustr,
		"sys_attrimg": skuimg
    };
	
    //商品规格选择
    $(function () {		
		var hitpoint = 0;
		var atrrimg = '';
		if (skuimg != 'jie') {
		var atrrimgdefault = $('#onlickImg img').attr("src");
		}
        $(".sys_item_spec .sys_item_specpara").each(function () {
            var i = $(this);
            var p = i.find("ul>li");
			if (skuimg != 'jie') {
			var firstval = 0;
			var selftval = 0;
			}
            p.click(function () {
				if (skuimg != 'jie') {
				firstval = $(this).parent().find('li').first().attr("data-aid");
				}
                if (!!$(this).hasClass("selected")) {
                    $(this).removeClass("selected");
                    i.removeAttr("data-attrval");
					if (skuimg != 'jie') {
					if (firstval == 1) {
					atrrimg = atrrimgdefault;
					}
					}
                } else {
                    $(this).addClass("selected").siblings("li").removeClass("selected");
                    i.attr("data-attrval", $(this).attr("data-aid"));
					if (skuimg != 'jie') {
					selftval = $(this).attr("data-aid");
					keyname = $(this).parent().parent().prev().html();
					if (firstval == 1) {
						hitpoint = 1;
						atrrimg = sys_item['sys_attrimg'][keyname][selftval];
					}
					}
                }
                getattrprice(atrrimg,hitpoint) //输出价格
            })
        })
		function midChange(src) {
        $("#midimg").attr("src", src).load(function() {
            changeViewImg();
        });
		}
		function changeViewImg() {
        $("#bigView img").attr("src", $("#midimg").attr("src"));
		}
        //获取对应属性的价格
        function getattrprice(atrrimg,hitpoint) {
            var defaultstats = true;
            var _val = '';
            var _resp = {
                kucount: "#kucount",
                price: "#changprice",
				sys_attrimg: ".bigImg img",
				sys_attrimgsmall: "#onlickImg img"
            }  //输出对应的class
            $(".sys_item_spec .sys_item_specpara").each(function () {
                var i = $(this);
                var v = i.attr("data-attrval");
                if (!v) {
                    defaultstats = false;
                } else {
                    _val += _val != "" ? ";" : "";
                    _val += v;
                }
            })
            if (!!defaultstats) {
                _kucount = sys_item['sys_attrprice'][_val]['Txt_CountSon'];
                _price = sys_item['sys_attrprice'][_val]['Txt_PriceSon'];
            } else {
                _kucount = sys_item['kucount'];				
				_price = sys_item['price'];                
				if (skuimg != 'jie') {
				if (hitpoint == 0) {
				atrrimg = atrrimgdefault;
				}
				}
            }
            //输出价格
            $(_resp.kucount).text(_kucount);  ///其中的math.round为截取小数点位数
            if (pricemin == pricemax) {
				$(_resp.price).text(pricemax);
			}else{
				$(_resp.price).text(_price);
			}
			if (skuimg != 'jie') {
			$(_resp.sys_attrimg).attr("src",atrrimg);
			$(_resp.sys_attrimgsmall).attr("src",atrrimg);
			midChange(atrrimg);
			}
        }
    })