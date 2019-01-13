var stores_obj={
    stores_init:function(json_storeInfo){
        var geolocation=new BMap.Geolocation();
        function sortNumber(a,b)
        {
            return a - b;
        }

        var getPointDistance = function(lat1, lng1, lat2, lng2){
            var pi = 3.14169;
            var pk = 180/pi;
            var a1 = lat1/pk,
                a2 = lng1/pk,
                b1 = lat2/pk,
                b2 = lng2/pk,
                t1 = Math.cos(a1)*Math.cos(a2)*Math.cos(b1)*Math.cos(b2),
                t2 = Math.cos(a1)*Math.sin(a2)*Math.cos(b1)*Math.sin(b2),
                t3 = Math.sin(a1)*Math.sin(b1),
                tt = Math.acos(t1+t2+t3);
            return 6366000 * tt;
        };

        geolocation.getCurrentPosition(function(pos){
            var latself = pos.point.lat;
            var lngself = pos.point.lng;
            var shopos = '';
            var nearest = '';
            var mapbb = {};
            var newObj = {};
            $.each(json_storeInfo,function(key,val){
                var map = new BMap.Map("allmap");
                var pointA = new BMap.Point(latself,lngself);
                var pointB = new BMap.Point(val['Stores_PrimaryLat'],val['Stores_PrimaryLng']);
                var d = getPointDistance(latself,lngself,val['Stores_PrimaryLat'],val['Stores_PrimaryLng']);
                d=d.toFixed(2);
                
                mapbb[d] = val;
            });

            var newkey = Object.keys(mapbb);
            for (var i = 0; i < newkey.length; i++) {
                newObj[newkey[i]] = mapbb[newkey[i]];
            }
            var one = 0;
			var count = newObj.length;
            $.each(newObj,function(key,val){
				if(one>10){
					return false;
				}
                
                if (one == 0) {
                    nearest = 'nearest';
                } else {
                    nearest = '';
                }
                var htmltmp = '<div class="row store '+nearest+'" style="line-height: 30px;"><div><input type="radio" name="Store" value="'+val['Stores_ID']+'" '+ (one==0?"checked":"") +' BizID="'+ val['Biz_ID'] +'">&nbsp;&nbsp;'+val['Stores_Name']+'&nbsp;&nbsp;<span style="float: right; color: #f00;" class="dis">'+shopos+'</span></div><div>地址:<a href="http://api.map.baidu.com/marker?location='+val['Stores_PrimaryLat']+','+val['Stores_PrimaryLng']+'&title='+val['Stores_Name']+'&name='+val['Stores_Name']+'&content='+val['Stores_Address']+'&output=html">'+val['Stores_Address']+'</a></div>';
                if(val['newtel']){
                	htmltmp += '<div>联系电话:';
                    $.each(val['newtel'],function(k,v){
                        htmltmp += v+'';
                    });
                    htmltmp +='</div>';
				}
                htmltmp += '</div>';
                $("#stores").append(htmltmp);
				
                one++;
            });

            //layer.tips("距离最近", ".nearest", {tips: [1, '#3595CC'], time:0});
        });
    }
}