<?php if (isset($_SESSION['firstlat'])) { ?>
    <script>
        newlat = <?= $_SESSION['firstlat'] ?>;
        newlng = <?= $_SESSION['firstlng'] ?>;
    </script>
<?php } ?>
<script>
    var Skin_ID = <?= $rsBiz_config['Skin_ID'] ?>;

    function readcookie(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    }

    var renderReverse = function (response) {
        var city = response.result.addressComponent.city;
        var citycode = response.result.cityCode;

        var id = -2;
        var coo_city_old = readcookie('city');
        $.get(SiteUrl + '/api/shop/biz/city/index.php?UsersID=' + UsersID + '&areaid=' + id + '&citycode=' + citycode, function (data) {
            if (data.status == 'ok') {
                var coo_city = readcookie('city');
                var strcity = data.name + '<i class="fa  fa-angle-down fa-2x" aria-hidden="true" style="font-size:18px;position: relative;top:-39px; left:15px"></i>';
                $(".header_city").html(strcity);
                if (coo_city_old === null && coo_city !== null) {
                    window.location.reload();
                }
            } else {
                console.log('定位失败');
            }
        }, 'json');


        if ($(".header_city").length > 1) {
            city = city + '<i class="fa  fa-angle-down fa-2x" aria-hidden="true" style="font-size:18px;position: relative;top:-39px; left:15px"></i>';
            $(".header_city").html(city);
            $(".header_city").attr('rel', citycode);
        }
        $('#area-modal .modal-body span').html(city);
        /*if(citycode != id){
         var top = $(window).height() / 2;
         $("#area-modal").css('top', top - 80);
         $("#area-modal").modal('show');
         }
         $('#confirm_area_btn').click(function(){
         $("#area-modal").modal('hide');
         location.href = '/api/<?= $UsersID ?>/city/';
         });
         $('#cancel_area_btn').click(function(){
         $("#area-modal").modal('hide');
         });*/
    };

    function decimal(num, v) {
        var vv = Math.pow(10, v);
        return Math.round(num * vv) / vv;
    }

    //获取店铺
    function load_page(page, lat, lng) {
        //首先自动加载第一页
        $.ajax({
            type: 'post',
            url: '',
            data: {p: page, action: 'get_index_list', lat: lat, lng: lng},
            beforeSend: function () {
                $("body").append("<div id='load'><div class='double-bounce1'></div><div class='double-bounce2'></div></div>");
            },
            success: function (data) {
                if (data['list'] != '') {
                    var htmltmp = "";
                    $.each(data['list'], function (i) {
                        v = data['list'][i];

                        if (v['meter']) {
                            if (v['meter'] < 1000) {
                                var meter = '约' + (v['meter'] >> 0) + 'm';
                            } else {
                                var met_num = v['meter'] / 1000;
                                var meter_num = decimal(met_num, 2);
                                var meter = '约' + meter_num + 'km';
                            }
                        } else {
                            var meter = '';
                        }

                        if (Skin_ID == 1) {
                            if (v['yuecount'] != 0) {
                                var yuecount = ''
                            } else {
                                var yuecount = '';
                            }
                            htmltmp += '<li class="item2">';
                            htmltmp += '<a href="' + v['url'] + '">';
                            htmltmp += '<span class="img_logo"><img src="' + v['Biz_Logo'] + '"></span>';
                            htmltmp += '<span class="content">';
                            htmltmp += '<h2>';
                            htmltmp += '<span class="title_x">' + v['Biz_Name'] + '</span>';
                            htmltmp += '<span class="distance "><p>' + meter + '</p></span>';
                            htmltmp += '</h2>' + yuecount;
                            htmltmp += '<div style="width:100%;overflow:hidden; border-bottom:1px #eee solid;">';
                            htmltmp += '<p class="address_x">' + v['Biz_Address'] + '</p>';
                            htmltmp += '<span style="float:right;color: #ff3600;font-size: 12px;line-height: 20px; width:45%;text-align:right">' + v['average_num'] + '分&nbsp;<span style="float:right"><img src="/static/images/star_' + v['average_ico'] + '.png" /></span></span>';
                            htmltmp += '</div>';
                            htmltmp += '<div class="clear"></div>';
                            if (v['rsCoupon'] == 1) {
                                htmltmp += '<span class="quan_x">券</span><span class="activity">可用优惠券</span>';
                            }
                            if (v['discoun_min'] < 10) {
                                htmltmp += '<div class="clear"></div>';
                                htmltmp += '<span class="fen_x">折</span><span class="activity">折扣商品' + v['discoun_min'] + '折起</span>';
                            }
                            htmltmp += '</span>';
                            htmltmp += '</a>';
                            htmltmp += '</li>';

                        } else if (Skin_ID == 2) {
                            htmltmp += '<li>';
                            htmltmp += '<a href="' + v['url'] + '">';
                            htmltmp += '<span class="left_img_dp"><img src="' + v['Biz_Logo'] + '"></span>';
                            htmltmp += '<span class="right_main_dp">';
                            htmltmp += '<p class="dian_title">' + v['Biz_Name'] + '</p>';
                            htmltmp += '<p class="pingfen_dian">' + v['average_num'] + '分 | ' + v['commit_count'] + '条评价</p>';
                            htmltmp += '<div class="dian_add">';
                            htmltmp += '<span class="add_left"><img src="/static/api/shop/union/2/index_48.png"><span>' + v['Biz_Address'] + '</span></span>';
                            htmltmp += '<span class="add_right">' + meter + '</span>';
                            htmltmp += '</div>';
                            htmltmp += '</span>';
                            htmltmp += '</a>';
                            htmltmp += '</li>'
                        }
                    });

                    newlat = data['newlat'];
                    newlng = data['newlng'];
                    if (lat && lng && page == 1) {
                        $(".biz_list").html('');
                    }
                    var $oldHtml = $(".biz_list").html();
                    $(".biz_list").html($oldHtml + htmltmp);
                    if (data['totalpage'] == $(".loadbtn").attr('page')) {
                        $(".loadbtn").hide();
                    }
                } else {
                    $(".loadbtn").hide();
                }
                if (lat && lng) {
                    var url = "http://api.map.baidu.com/geocoder/v2/?ak=" + baidu_map_ak + "&callback=renderReverse&location=" + lat + "," + lng + "&output=json&pois=0";
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = url;
                    document.body.appendChild(script);
                }
            },
            complete: function () {
                $("#load").remove();
            },
            dataType: 'json'
        });
    }

    $(".loadbtn").click(function(){
		var page = parseInt($(this).attr('page'))+1;
		$(this).attr('page', page);
		if (minul != 'minul') {
		    load_page(page, newlat, newlng);
		} else {
		    load_page(page, '', '');
		}
	});
    if (minul != 'minul') {
		if(typeof newlat=="undefined"){
            var geolocation=new BMap.Geolocation();
            geolocation.getCurrentPosition(function(pos){
                //加载第一页
                load_page($(".loadbtn").attr('page'), pos.point.lat, pos.point.lng);
                $(".loadbtn").click(function(){
                    var page = parseInt($(this).attr('page'))+1;
                    $(this).attr('page', page);
                    load_page(page, pos.point.lat, pos.point.lng);
                });
            });
        } else {
            load_page($(".loadbtn").attr('page'), newlat, newlng);
        }
    }else{
        load_page($(".loadbtn").attr('page'), '', '');
    }


    if (Skin_ID == 2) {
        //获取产品
        var pro_more = $('.pro_more');
        var pro_ajax = true;

        function load_pro(page) {
            //首先自动加载第一页
            $.ajax({
                type: 'post',
                url: '',
                data: {p: page, action: 'get_pro'},
                success: function (data) {
                    $('.pro_more').attr('page', page);
                    if (data.list != '') {
                        var htmltmp = '';
                        $.each(data.list, function (i) {
                            v = data.list[i];

                            htmltmp += '<li>';
                            htmltmp += '<a href="' + v['url'] + '">';
                            htmltmp += '<span class="pro_img"><img src="' + v['pro_img'] + '"></span>';
                            htmltmp += '<span class="pro_main">';
                            htmltmp += '<p class="pro_title">';
//                            if (v['pt_flag']) {
//                                htmltmp += '<span class="pt_mark">拼团</span>';
//                            }
                            htmltmp += v['Products_Name'] + '</p>';
                            htmltmp += '<p class="hj_pro">' + v['Products_BriefDescription'] + '</p>';
                            htmltmp += '<div class="pro_pri">';
                            htmltmp += '<span class="pro_left"><font>￥</font>' + v['Products_PriceX'] + '<span class="pri_y">' + v['Products_PriceY'] + '</span></span>';
                            htmltmp += '<span class="pro_right">已售' + v['Products_Sales'] + '</span>';
                            htmltmp += '</div>';
                            htmltmp += '</span>';
                            htmltmp += '</a>';
                            htmltmp += '</li>';
                        });

                        $('.pro_list').append(htmltmp);

                        if (data.get_flag) {
                            pro_ajax = true;
                        } else {
                            $('.pro_more').hide();
                            $('.bottom_bottom').show();
                            pro_ajax = false;
                        }
                    } else {
                        $('.pro_more').hide();
                        $('.bottom_bottom').show();
                        pro_ajax = false;
                    }
                },
                dataType: 'json'
            });
        }

        pro_more.click(function () {
            var page = parseInt($(this).attr('page')) + 1;
            load_pro(page);
            pro_ajax = false;   //防止连续请求
        });

        //瀑布流
        $(window).bind('scroll', function () {
            //当滚动到最底部以上100像素时， 加载新内容
            if ($(document).height() - $(this).scrollTop() - $(this).height() < 100) {
                if (pro_ajax) { //当ajaxover为真的时候，说明还有未加载的产品
                    pro_more.trigger('click');
                    pro_ajax = false;   //防止连续请求
                } else {
                    return false;
                }
            }
        });
    }
</script>
