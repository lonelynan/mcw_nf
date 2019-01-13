<?php include_once('header.php');?>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $ak_baidu;?>"></script>
<div id="head-bar">
<div class="head-bar">
<div class="head-bar-back">
<a href="javascript:void(0);" onclick="history.go(-1);" data-direction="reverse"><img src="/api/shop/biz/city/images/icon-back.png" width="24" height="24"/><span>返回</span></a>
</div>
<div class="head-bar-title"><?php echo $city_name;?></div>
<div class="head-bar-right">
<?php 
if(isset($_COOKIE['city'])){
	$cityname_tmp = explode('|', $_COOKIE['city']);
	$areaid = $cityname_tmp[0];
}
?>
<span onclick="Dc(-2);">自动定位</span>
</div>
</div>
<div class="head-bar-fix"></div>
</div>
<div style="height:30px;overflow:hidden;margin:10px;background:#FFFFFF;border-radius:4px;"><input type="search" id="kw" maxlength="20" style="border:none;width:100%;padding:4px 10px;color:#999999;font-size:14px;" placeholder="请输入关键词搜索"/></div>
<div id="list"></div>
<div id="city">
<?php if(!empty($lists) && is_array($lists)){?>
<?php foreach($lists as $k => $v){?>
<div style="padding:0 16px;height:24px;line-height:24px;font-size:16px;"><?php echo $k;?></div>
<div class="list-set">
<ul>
<?php foreach($v as $j => $s){?>
<li><div <?php if($j==0){?> style="border:none;"<?php }?> onclick="Dc(<?php echo $s['area_id'];?>);"><?php echo $s['area_name'];?></div></li>
<?php }?>
</ul>
</div>
<?php }?>
<?php }?>
</div>
<script type="text/javascript">
var UsersID = '<?php echo $UsersID;?>';
var baidu_map_ak='<?php echo $ak_baidu;?>';
var SiteUrl = "http://"+window.location.host;
$(document).on('pageinit', function(event) {
	$('#kw').on('input paste', Ds);
	$('#kw').on('blur', function(){window.scrollTo(0,0);});
});
function Ds() {
	var kw = $('#kw').val();
	if(kw) {
		$('#list').show();
		$('#city').hide();
		var res = '';
		var j = 0;
		$('#city li').each(function(i){
			if($(this).text().indexOf(kw) !=-1) {
				var d = $(this).html();
				if(j++ == 0) {
					d = d.replace('<div onclick', '<div style="border:none;" onclick');
				} else {
					d = d.replace('<div style="border:none;"', '<div');
				}
				d = d.replace(kw, '<b class="f_red">'+kw+'</b>');
				res += '<li>'+d+'</li>';
			}
		});
		if(res == '') res = '<li onclick="Dr();"><div style="border:none;">未找到<b class="f_red">'+kw+'</b></div></li>';
		res = '<div style="padding:10px;font-size:16px;" onclick="Dr();"><span style="float:right;color:#007AFF;">取消</span>搜索结果</div><div class="list-set"><ul>'+res+'</ul></div>';
		$('#list').html(res);
	} else {
		$('#list').hide();
		$('#list').html('');
		$('#city').show();
	}
}
function Dr() {
	$('#kw').val('');
	Ds();
}

var renderReverse = function(response){
	var citycode = response.result.cityCode;
	var id = -2;
	$.get(SiteUrl+'/api/shop/biz/city/index.php?UsersID='+UsersID+'&areaid='+id+'&citycode='+citycode, function(data) {
		if(data.status == 'ok') {
			Dtoast('定位成功');
			setTimeout(function() {
				location.href = '/api/'+UsersID+'/shop/union/';
			}, 1000);
		} else {
			Dtoast('定位失败');
		}
	},'json');
}
function Dc(id) {
	if(id == -2){
		var param = {
                      action: 'dcact',
                      actval: 'auto'
                    };
		$.post('/api/'+UsersID+'/shop/union/', param, function(data) {
                        if(data.status == 1){
                          var go = 'go';
                        }
                      },'json');
		var geolocation = new BMap.Geolocation();
	    geolocation.getCurrentPosition(function(pos){
			var url = "http://api.map.baidu.com/geocoder/v2/?ak="+baidu_map_ak+"&callback=renderReverse&location="+pos.point.lat+","+pos.point.lng+"&output=json&pois=0";
			var script = document.createElement('script');
			script.type = 'text/javascript';
			script.src = url;
			document.body.appendChild(script);
		});
		if(navigator.geolocation){
			navigator.geolocation.getCurrentPosition(function(pos){
				showNearest(pos['coords']['latitude'], pos['coords']['longitude']);
			}, function(error){
				switch(error.code){
					case error.TIMEOUT:
						alert('浏览器获取地理位置超时！');
						break;
					case error.PERMISSION_DENIED:
						alert('您未允许浏览器提供地理位置服务，无法导航！清除缓存重新定位！');
						break;
					case error.POSITION_UNAVAILABLE:
						alert('浏览器获取地理位置服务不可用！');
						break;
					default:
						break;
				}
			}, {enableHighAccuracy:true, maximumAge:1000, timeout:5000});
		}
	}else{		
		var param = {
                      action: 'dcact',
                      actval: 'minul'
                    };
		$.post('/api/'+UsersID+'/shop/union/', param, function(data) {
                        if(data.status == 1){
                          var go = 'go';
                        }
                      },'json');
		$.get(SiteUrl+'/api/'+UsersID+'/city/'+id+'/', function(data) {
			if(data.status == 'ok') {
				Dtoast('切换成功');
				setTimeout(function() {
					location.href = '/api/'+UsersID+'/shop/union/';
				}, 1000);
			} else {
				Dtoast('切换失败');
			}
		}, 'json');
	}
}
</script>
<?php include_once('footer.php');?>