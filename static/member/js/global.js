var http_host_ary=window.location.host.split('.');
http_host_ary.shift();
var http_host=http_host_ary.join('.');
var domain={
	www:'http://www.'+http_host,
	static:'/static',
	img:'http://www.'+http_host,
	kf:'http://www.'+http_host
};
var session_id;

var Browser = new Object();
$.ajax({
	type : "post",
	url : "/third_party/uploadify/check-exists.php",
	data : "action=getSession",
	async : false,
	dataType: "json",
	success : function(data){
		session_id=data.session_id;
	}
});

var global_obj={
	file_upload:function(file_input_obj, filepath_input_obj, img_detail_obj, size){
		var multi=(typeof(arguments[4])=='undefined')?false:arguments[4];
		var queueSizeLimit=(typeof(arguments[5])=='undefined')?5:arguments[5];
		var callback=arguments[6];
		var fileTypeExts=(typeof(arguments[7])=='undefined')?'*.gif; *.jpg; *.jpeg; *.png':arguments[7];
		var fileSizeLimit=(typeof(arguments[8])=='undefined')?'500KB':arguments[8];

		file_input_obj.uploadify({
        	'removeTimeout' : 0,
			//'debug':true,
			'formData' : {PHPSESSID:session_id},
			'swf'      : '/third_party/uploadify/uploadify.swf',
			'uploader' : '/third_party/uploadify/uploadify.php',
			'buttonImage' : '/third_party/uploadify/browse-btn.png',
       		'queueSizeLimit' : queueSizeLimit,
        	'multi'    : multi,
			'fileTypeExts' : fileTypeExts,
        	'fileSizeLimit' : fileSizeLimit,
			'onUploadSuccess' : function(file, data, response) {
				if(response){
					
					//alert(data);
					var jsonData=eval('('+data+')');
					if(jsonData.status==1){
						if($.isFunction(callback)){
							callback(jsonData.filename,jsonData.filepath);
						}else{
							
							filepath_input_obj.val(jsonData.filepath);
							img_detail_obj.html(global_obj.img_link(jsonData.filepath));
						}
					}else{
						alert('文件上传失败，出现未知错误！');
					};
				}
			}
		});
	},
	
	img_link:function(img){
		if(!img){return;}
		return '<a href="'+img+'" target="_blank"><img src="'+img+'"></a>';
	},
	
	win_alert:function(tips, handle){
		$('body').prepend('<div id="global_win_alert"><div>'+tips+'</div><h1>好</h1></div>');
		$('#global_win_alert').css({
			position:'fixed',
			left:$(window).width()/2-125,
			top:'30%',
			background:'#fff',
			border:'1px solid #ccc',
			opacity:0.95,
			width:250,
			'z-index':100000,
			'border-radius':'8px'
		}).children('div').css({
			'text-align':'center',
			padding:'30px 10px',
			'font-size':16
		}).siblings('h1').css({
			height:40,
			'line-height':'40px',
			'text-align':'center',
			'border-top':'1px solid #ddd',
			'font-weight':'bold',
			'font-size':20
		});
		$('#global_win_alert h1').click(function(){
			$('#global_win_alert').remove();
		});
		if($.isFunction(handle)){
			$('#global_win_alert h1').click(handle);
		}
	},
	
	check_form_ele:function() {
		var flag = true;
		var re = /^\d+(?=\.{0,1}\d+$|$)/;  //判断字符串是否为数字     //判断正整数 /^[1-9]+[0-9]*]*$/  
		$("#product_edit_form input[type='text'], #product_add_form input[type='text']").each(function() {
			if ($(this).attr('data-type') == 'number') {
				var value = $(this).val();
				if (value != '') {
					 if (!re.test(value)) {
						$(this).css('border', '1px solid red');	
						$(this).focus();
						flag = false;
						$(this).parent().find(".msg").html("只能为非负数");
					}
				}
			}
		})

		return flag;
	},

	check_form:function(obj){
		var flag=false;
		obj.each(function(){
			//如果此元素未被禁用
			if($(this).is(":visible")){
			  
			  
			  if($(this).attr('type') == 'checkbox' ||$(this).attr('type') == 'radio'){
				
				var name = $(this).attr('name');
				var checked = false;
				$.each($("input[name='"+name+"']"),function(i){
				
					if($(this).prop('checked')){
						checked = true;
						return false; 
					}						
				});
				
			
				if(!checked){
				
					$.each($("input[name='"+name+"']"),function(i){
						$(this).parent().css('border', '1px solid red');			
					});
				}
				
				flag = !checked;
				
			  }else{
				 
				if($(this).val()==''){
					$(this).css('border', '1px solid red');
					flag==false && ($(this).focus());
					flag=true;					
				}else{
					$(this).removeAttr('style');
				}
				
				
			  }
			}
			
		});
		

		return flag;
	},
	
	
	reason_form_init:function(){
		$('#config_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
		});
	},
	
	config_form_init:function(){
		
		$('#config_form').submit(function(){return false;});
		$('#config_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			
			$(this).attr('disabled', true);
			$.post('?', $('#config_form').serialize(), function(data){
				if(data.status==1){					
						$('#config_form input:submit').attr('disabled', false);
						alert(data.msg);					
				}else{
					alert(data.msg);
					$('#config_form input:submit').attr('disabled', false);
				}
			}, 'json');
		});
	},
	
	config_setcheck_init:function(){		
		$('#config_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};			
		});
	},
	
	reserve_form_init:function(){
		$('.reverve_field_table .input_add').click(function(){
			$('.reverve_field_table tr[FieldType=text]:hidden').eq(0).show();
			if(!$('.reverve_field_table tr[FieldType=text]:hidden').size()){
				$(this).hide();
			}
		});
		$('.reverve_field_table .input_del').click(function(){
			$('.reverve_field_table .input_add').show();
			$(this).parent().parent().hide().find('input').val('');
		});
		$('.reverve_field_table .select_add').click(function(){
			$('.reverve_field_table tr[FieldType=select]:hidden').eq(0).show();
			if(!$('.reverve_field_table tr[FieldType=select]:hidden').size()){
				$(this).hide();
			}
		});
		$('.reverve_field_table .select_del').click(function(){
			$('.reverve_field_table .select_add').show();
			$(this).parent().parent().hide().find('input').val('');
		});
	},
	
	map_init:function(){
		var myAddress=$('input[name=Address]').val();
		var destPoint=new BMap.Point($('input[name=PrimaryLng]').val(), $('input[name=PrimaryLat]').val());
		var map=new BMap.Map('map');
		map.centerAndZoom(new BMap.Point(destPoint.lng, destPoint.lat), 20);
		map.enableScrollWheelZoom();
		map.addControl(new BMap.NavigationControl());
		var marker=new BMap.Marker(destPoint);
		map.addOverlay(marker);
		
		map.addEventListener('click', function(e){
			destPoint=e.point;
			set_primary_input();
			map.clearOverlays();
			map.addOverlay(new BMap.Marker(destPoint)); 
		});
		
		var ac=new BMap.Autocomplete({'input':'Address','location':map});
		ac.addEventListener('onhighlight', function(e) {
			ac.setInputValue(e.toitem.value.business);
		});
		
		ac.setInputValue(myAddress);
		ac.addEventListener('onconfirm', function(e) {//鼠标点击下拉列表后的事件
			var _value=e.item.value;
			myAddress=_value.business;
			ac.setInputValue(myAddress);
			
			map.clearOverlays();    //清除地图上所有覆盖物
			local=new BMap.LocalSearch(map, {renderOptions:{map: map}}); //智能搜索
			local.setMarkersSetCallback(markersCallback);
			local.search(myAddress);
		});
		
		var markersCallback=function(posi){
			console.log(posi);
			$('#Primary').attr('disabled', false);
			if(posi.length==0){
				alert('定位失败，请重新输入详细地址或直接点击地图选择地点！');
				return false;
			}
			for(var i=0; i<posi.length; i++){
				if(i==0){
					destPoint=posi[0].point;
					set_primary_input();
				}
				posi[i].marker.addEventListener('click', function(data){
					destPoint=data.target.getPosition(0);
				});  
			}
		}
		
		var set_primary_input=function(){
			$('input').filter('[name=PrimaryLng]').val(destPoint.lng).end().filter('[name=PrimaryLat]').val(destPoint.lat);
		}
		
		$('input[name=Address]').keyup(function(event){
			if(event.which==13){
				$('#Primary').click();
			}
		});
		
		$('#Primary').click(function(){
			if(global_obj.check_form($('input[name=Address]'))){return false};
			$(this).attr('disabled', true);

            myAddress=$('input[name=Address]').val();
            ac.setInputValue(myAddress);

            map.clearOverlays();    //清除地图上所有覆盖物

			local=new BMap.LocalSearch(map, {renderOptions:{map: map}}); //智能搜索
			local.setMarkersSetCallback(markersCallback);
			local.search($('input[name=Address]').val());
			return false;
		});
	},
	
	create_layer:function(title, url, width, height){
    var callback = arguments[6] ? arguments[6] : function(){};
		layer.open({
			type: 2,
			title: title,
			fix: false,
			shadeClose: true,
			maxmin: true,
			area: [width+'px', height+'px'],
			content: url,
			end:function(){
          if(callback != undefined){
              callback();
           }
			}
		});
	},
	
	/*edit20160416*/
	chart:function(){
		 $('.chart').height(global_obj.chart_par.height).highcharts({
            chart:{
				type:global_obj.chart_par.themes,
				backgroundColor:global_obj.chart_par.bg
            },
            title:{text:''},
			tooltip: {
				shared: true,
				valueSuffix: global_obj.chart_par.valueSuffix                   
			},
			
			xAxis:{categories:chart_data.date},
			yAxis:[{
				title:{text:''},
				min:0
			}],
			legend:global_obj.chart_par.legend,
			plotOptions:{
				line:{
					dataLabels:{enabled:true},
					enableMouseTracking:false
				},
				bar:{
					dataLabels:{enabled:true}
				}
			},
			series:chart_data.count,
			exporting:{enabled:false}
        });
		 
	},
	
	chart_pie:function(){
		$('.chart').height(500).highcharts({
			title:{text:''},
            credits:{enabled:false},
			tooltip:{
				pointFormat:'{series.name}: <b>{point.percentage:.2f}%</b>'
			},
			plotOptions:{
				pie:{
					allowPointSelect:true,
					cursor:'pointer',
					dataLabels:{
						enabled:true,
						color:'#000000',
						connectorColor:'#000000',
						format:'<b>{point.name}</b>: {point.percentage:.2f} %'
					}
				}
			},
			series:[{
				type:'pie',
				name:'百分比',
				data:pie_data
			}]
		});
	},
	chart_par:{themes:'column',height:500,bg:'',legend:{},valueSuffix:''}
}


function rowindex(tr)

{

  if (Browser.isIE)

  {

    return tr.rowIndex;

  }

  else

  {

    table = tr.parentNode.parentNode;

    for (i = 0; i < table.rows.length; i ++ )

    {

      if (table.rows[i] == tr)

      {

        return i;

      }

    }

  }

}
Array.prototype.contains = function(obj) {
    var i = this.length;
    while (i--) {
        if (this[i] === obj) {
            return true;
        }
    }
    return false;
}

/*
* @param {Function} fn 进行迭代判定的函数
* @param more ... 零个或多个可选的用户自定义参数
* @returns {Array} 结果集，如果没有结果，返回空集
*/
Array.prototype.each = function(fn){
fn = fn || Function.K;
var a = [];
var args = Array.prototype.slice.call(arguments, 1);
for(var i = 0; i < this.length; i++){
var res = fn.apply(this,[this[i],i].concat(args));
if(res != null) a.push(res);
}
return a;
};

/**
* 得到一个数组不重复的元素集合<br/>
* 唯一化一个数组
* @returns {Array} 由不重复元素构成的数组
*/
Array.prototype.uniquelize = function(){
var ra = new Array();
for(var i = 0; i < this.length; i ++){
if(!ra.contains(this[i])){
ra.push(this[i]);
}
}
return ra;
}; 

/*数组差集*/
Array.minus = function(a, b){
	return a.uniquelize().each(function(o){return b.contains(o) ? null : o});
}; 



/*
 *
 检测对象是否是空对象(不包含任何可读属性)。
 *
 方法既检测对象本身的属性，也检测从原型继承的属性(因此没有使hasOwnProperty)。
 */
function isEmpty(obj)
{
    for (var name
in obj)
    {
        return false;
    }
    return true;
};


