<?php
ini_set("display_errors", "On");
include('../global.php');
//分享开始
$Share = array();
$Share["link"] = $shop_url;
$Share["title"] = $rsConfig["ShopName"];
if ($owner['id'] != '0' && $rsConfig["Distribute_Customize"] == 1) {
    $Share["desc"] = $owner['shop_announce'] ? $owner['shop_announce'] : $rsConfig["ShareIntro"];
    $Share["img"] = strpos($owner['shop_logo'], "http://") > -1 ? $owner['shop_logo'] : 'http://' . $_SERVER["HTTP_HOST"] . $owner['shop_logo'];
} else {
    $Share["desc"] = $rsConfig["ShareIntro"];
    $Share["img"] = strpos($rsConfig['ShareLogo'], "http://") > -1 ? $rsConfig['ShareLogo'] : 'http://' . $_SERVER["HTTP_HOST"] . $rsConfig['ShareLogo'];
}

require_once($_SERVER["DOCUMENT_ROOT"] . '/control/weixin/share.class.php');
$shareclass = new share($DB, $UsersID);
$share_config = $shareclass->getshareinfo($rsConfig, $Share);

if (isset($_GET["UsersID"])) {
    $UsersID = $_GET["UsersID"];
} else {
    echo '缺少必要的参数';
    exit;
}

if (isset($_GET['categoryid'])) {
    $CategoryID = $_GET['categoryid'];

} else {
    echo '缺少分类ID';
    exit();
}

$child_cate = $DB->GetAssoc("biz_union_category", "Category_Name,Category_ID,Category_Img", "where Users_ID='" . $UsersID . "' and Category_ParentID=" . $CategoryID . " order by Category_Index,Category_ID asc");

$rsCategory = $DB->GetRs("biz_union_category", "Category_Name", "where Users_ID='" . $UsersID . "' and Category_ID=" . $CategoryID);

$param = array(
    "page" => 1,
    "price" => "",
    "areaid" => $rsConfig["DefaultAreaID"],
    "regionid" => 0,
    "categoryid" => $CategoryID,
    "orderby" => "1"
);
$flag_result = 1;

// 获取城市ID
if (isset($_COOKIE['city'])) {
    $cityid_tmp = explode('|', $_COOKIE['city']);
    $cityid = $cityid_tmp[0];
} else {
    $cityid = 0;
}

if ($_POST) {

    $page = !empty($_POST['page']) && (int)$_POST['page'] > 1 ? (int)$_POST['page'] : 1;
    $limitpage = 4;
    $totalCount = 0;

    // 综合排序
    if ($_POST['choose_type'] == 1) {
        $codtion = 'where Users_ID = "' . $UsersID . '" and Biz_Status = 0 and Is_Union = 1 and Biz_City = ' . $cityid;
        if (!empty($_POST['biz_name'])) {
            $codtion .= " and (Biz_Name like '%" . $_POST['biz_name'] . "%' or Biz_Introduce like '%" . $_POST['biz_name'] . "%')";
        }
        if (!empty($_POST['biz_cate_id'])) {
            $codtion .= " and Category_ID like '%," . $_POST['biz_cate_id'] . ",%'";
        }

        $totalCount = $DB->GetRs('biz', 'count(*) as count', $codtion)['count'];

        $codtion .= ' order by Biz_Index asc LIMIT ' . (($page - 1) * $limitpage) . ',' . $limitpage;
        $biz_list = $DB->GetAssoc("biz", "*", $codtion);
        foreach ($biz_list as $k => $v) {
            $count = $DB->GetRs('user_order_commit', 'count(*) as count', 'where Users_ID="' . $UsersID . '" and Biz_ID=' . $v['Biz_ID'])['count'];
            $score = $DB->GetRs('user_order_commit', 'avg(score) as score', 'where Users_ID="' . $UsersID . '" and Biz_ID=' . $v['Biz_ID'] . ' group by Biz_ID')['score'];
            $biz_list[$k]['commit_count'] = $count;
            $biz_list[$k]['score'] = empty($score) ? '暂无评' : round($score * 10) / 10;
        }
    }
    // 评分最高
    if ($_POST['choose_type'] == 2) {
        $codtion = " as b left join user_order_commit as c on b.Biz_ID = c.Biz_ID where b.Users_ID = '" . $UsersID . "' and b.Biz_Status = 0 and b.Is_Union = 1 and b.Biz_City = " . $cityid;
        if (!empty($_POST['biz_name'])) {
            $codtion .= " and (b.Biz_Name like '%" . $_POST['biz_name'] . "%' or b.Biz_Introduce like '%" . $_POST['biz_name'] . "%')";
        }
        if (!empty($_POST['biz_cate_id'])) {
            $codtion .= " and b.Category_ID like '%," . $_POST['biz_cate_id'] . ",%'";
        }

        $totalCount = $DB->GetRs('biz', 'count(DISTINCT b.Biz_ID) as count', $codtion)['count'];

        $codtion .= " group by b.Biz_ID";

        $codtion .= ' order by (case when c.score is null then 0 else c.score end) desc LIMIT ' . (($page - 1) * $limitpage) . ',' . $limitpage;
        $biz_list = $DB->GetAssoc("biz", "b.*, avg(c.score) sco, count(Item_ID) as commit_count", $codtion);
    }
    // 距离最近
    if ($_POST['choose_type'] == 3) {
        $currentLat = $_POST['lat'];
        $currentLng = $_POST['lng'];
        $start_page = ($page - 1) * $limitpage;
        $end_page = $limitpage;

        $count_sql = "select count(*) as count ";
        $query_sql = "select *,round(6378.138*2*asin(sqrt(pow(sin(({$currentLat}*pi()/180-Biz_PrimaryLat*pi()/180)/2),2)+cos({$currentLat}*pi()/180)*cos(Biz_PrimaryLat*pi()/180)*pow(sin(({$currentLng}*pi()/180-Biz_PrimaryLng*pi()/180)/2),2)))*1000) as distance ";

        $sql = " from biz where Users_ID = '{$UsersID}' and Biz_Status = 0 and Is_Union = 1 and Biz_City = '{$cityid}'";

        if (!empty($_POST['biz_name'])) {
            $sql .= " and (Biz_Name like '%" . $_POST['biz_name'] . "%' or Biz_Introduce like '%" . $_POST['biz_name'] . "%')";
        }
        if (!empty($_POST['biz_cate_id'])) {
            $sql .= " and Category_ID like '%," . $_POST['biz_cate_id'] . ",%'";
        }

        $count_sql .= $sql;
        $query_sql .= $sql;

        $res = $DB->query($count_sql);
        while ($r = $DB->fetch_assoc($res)) {
            $totalCount = $r['count'];
        }

        $query_sql .= " order by distance asc limit {$start_page} , {$end_page}";
        $biz_lists = $DB->query($query_sql);
        while ($r = $DB->fetch_assoc($biz_lists)) {
            $biz_list[] = $r;
        }
    }

    $totalpage = ceil($totalCount / $limitpage);//总计页数

    if (!empty($biz_list)) {
        $data = [
            'biz_list' => $biz_list,
            'totalpage' => $totalpage,
            'totalCount' => $totalCount,
            'status' => 1
        ];
    } else {
        $data = [
            'status' => 0
        ];
    }

    echo json_encode($data);
    exit;
}

// 好评商家。
//$hp_biz_list = $DB->GetAssoc('biz','*','where Users_ID="'. $UsersID .'" ');
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="apple-touch-fullscreen" content="yes"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no"/>
    <title>商家列表</title>
</head>
<link rel="stylesheet" href="/static/css/index.css">
<link rel="stylesheet" href="/static/css/swiper.min.css">
<script type="text/javascript" src="/static/js/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="/static/js/swiper.min.js"></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?= $ak_baidu ?>"></script>
<script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script>

<body>
<style>
    a:hover {
        color: #333;
    }

    .add_shangjia:hover {
        color: #fff;
    }
</style>
<div class="w">
    <div class="head_sj_bg">
        <div class="top" style="width: 100%;">
            <a href="javascript:history.back(-1)"><span><img src="/static/images/back_h.png"></span></a>商家列表
        </div>
        <a href="#" id="address" class="add_shangjia" style="width:70%;" lng="" lat=""><img src="/static/images/sj_list_07.png"></a>
        <div>
            <input id="biz_name" name="biz_name" type="text" placeholder="请输入商家名称或服务项目" class="search_sj">
            <span class="ss_img_sj"><img src="/static/images/sj_list_11.png"></span>
        </div>
    </div>
    <div class="clear"></div>
    <div id="topNav" class="swiper-container" style="display: <?= $CategoryID == 0 ? 'none' : 'block' ?>">
        <ul class="swiper-wrapper">
            <?php foreach ($child_cate as $k => $v) {
                ?>
                <div class="swiper-slide <?= $k == 0 ? 'active' : '' ?>" cate_id="<?= $CategoryID == 0 ? $CategoryID : $v['Category_ID'] ?>">
                <span>
                    <img src="<?= !empty($v['Category_Img']) ? $v['Category_Img'] : '/static/images/no_pic.png' ?>"><font><?= $v['Category_Name'] ?></font>
                </span>
                </div>
            <?php }
            ?>
        </ul>
    </div>
    <div class="clear"></div>
    <div class="container_x">
        <ul class="tabs">
            <li class="active" type="1"><a href="#tab1">综合排序</a></li>
            <li type="2"><a href="#tab2">评分最高</a></li>
            <li type="3"><a href="#tab3">距离最近</a></li>
        </ul>
        <div class="tab_container">
            <div id="tab1" class="tab_content" style="display: block; ">
                <div class="dianpu">
                    <ul></ul>
                </div>
            </div>

            <div id="tab2" class="tab_content" style="display: none; ">
                <div class="dianpu">
                    <ul></ul>
                </div>
            </div>
            <div id="tab3" class="tab_content" style="display: none; ">
                <div class="dianpu">
                    <ul></ul>
                </div>
            </div>
        </div>
    </div>
    <div class="pullUp get_more" page="1" status="1" style="text-align: center;margin-bottom: 30px;"><span class="pullUpIcon"></span><span class="pullUpLabel">点击加载更多...</span></div>
</div>
<script type="text/javascript">
    var cate_id = <?= $CategoryID != 0 ? (isset($child_cate[0]['Category_ID']) ? $child_cate[0]['Category_ID'] : $CategoryID) : $CategoryID ?>;
    var biz_name = '';
    var ajaxover = false;
    var UsersID = "<?=$UsersID?>";

    check_cate(1, 1);

    //    点击分类切换内容
    function check_cate(choose_type, page) {
        if (page == 1) {
            $(".get_more").attr({'page': 1, 'status': 1}).show().find('.pullUpLabel').html('点击加载更多...'); // 初始化页码
            $('.dianpu ul').html('');
        }
        $.ajax({
            url: "?",
            type: "post",
            data: {
                'biz_cate_id': cate_id,
                'biz_name': $("#biz_name").val(),
                'choose_type': choose_type,
                'page': page,
                'lat': $("#address").attr("lat"),
                'lng': $("#address").attr("lng"),
            },
            success: function (data) {
                if (data.status == 1) {
                    var html = '';
                    $.each(data.biz_list, function (k, v) {
                        if (v['Biz_ID'] == null) {
                            html += '';
                            $(".get_more").attr('status', 0).find('.pullUpLabel').html('已经没有了');
                            ajaxover = false;
                        } else {
                            html += '<li class="cate_' + cate_id + '">';
                            html += '<a href="/api/' + UsersID + '/shop/biz_xq/' + v['Biz_ID'] + '/">';
                            html += '<span class="left_img_dp"><img src="' + (v["Biz_Logo"] ? v["Biz_Logo"] : "/static/images/no_pic.png") + '"></span>';
                            html += '<span class="right_main_dp">';
                            html += '<p class="dian_title">' + v['Biz_Name'] + '</p>';
                            html += '' + (choose_type == 2 ? '<p class="pingfen_dian">' + (v['sco'] == null ? '暂无评' : Math.round(v['sco'] * 10) / 10) + '分 |' + v['commit_count'] + ' 条评价</p>' : '') + '';
                            html += '' + (choose_type == 1 ? '<p class="pingfen_dian">' + v['score'] + '分 |' + v['commit_count'] + ' 条评价</p>' : '') + '';
                            html += '<div class="dian_add">';
                            html += '<span class="add_left" style="flex-wrap: nowrap;"><img src="/static/images/index_48.png">' + v['Biz_Address'] + '</span>';
                            html += '' + ( choose_type == 3 ? '<span class="add_right">' + (v['distance'] < 1000 ? '<1km' : Math.round(((v['distance'] / 1000) * 100)) / 100 + 'km' ) + '</span>' : '') + '';
                            html += '</div></span></a></li>';
                            if (data['totalpage'] <= $(".get_more").attr('page')) {
                                $(".get_more").attr('status', 0).find('.pullUpLabel').html('已经没有了');
                                ajaxover = false;
                            } else {
                                ajaxover = true;
                            }
                        }
                    })
                } else {
                    var html = '';
                    $(".get_more").attr('status', 0).find('.pullUpLabel').html('已经没有了');
                    ajaxover = false;
                }
                $("#tab" + choose_type + " div ul").append(html);
            },
            dataType: 'json'
        })
    }


    $(function () {
        $(".get_more").click(function () {
            if ($(this).attr('status') == 0) {
                return false;
            }
            var page = parseInt($(this).attr("page")) + 1;
            $(this).attr('page', page);
            check_cate($('.container_x ul.tabs .active').attr('type'), page);
        })

        $(".ss_img_sj").click(function () {
            check_cate($('.container_x ul.tabs .active').attr('type'), 1);
        })

        var mySwiper = new Swiper('#topNav', {
            freeMode: true,
            freeModeMomentumRatio: 0.5,
            slidesPerView: 'auto',
        });
        swiperWidth = mySwiper.container[0].clientWidth
        maxTranslate = mySwiper.maxTranslate();
        maxWidth = -maxTranslate + swiperWidth / 2
        $(".swiper-container").on('touchstart', function (e) {
            e.preventDefault()
        })
        mySwiper.on('tap', function (swiper, e) {
            $('.dianpu ul').html('');
            //	e.preventDefault()
            slide = swiper.slides[swiper.clickedIndex]
            slideLeft = slide.offsetLeft
            slideWidth = slide.clientWidth
            slideCenter = slideLeft + slideWidth / 2
            // 被点击slide的中心点
            mySwiper.setWrapperTransition(300)
            if (slideCenter < swiperWidth / 2) {
                mySwiper.setWrapperTranslate(0)
            } else if (slideCenter > maxWidth) {
                mySwiper.setWrapperTranslate(maxTranslate)
            } else {
                nowTlanslate = slideCenter - swiperWidth / 2
                mySwiper.setWrapperTranslate(-nowTlanslate)
            }
            $("#topNav  .active").removeClass('active')
            $("#topNav .swiper-slide").eq(swiper.clickedIndex).addClass('active')

            cate_id = $('.active').attr('cate_id');
            check_cate($('.container_x ul.tabs .active').attr('type'), 1)
        })

        //Default Action
        $(".tab_content").hide(); //Hide all content
        $("ul.tabs li:first").addClass("active").show(); //Activate first tab
        $(".tab_content:first").show(); //Show first tab content

        //On Click Event
        $("ul.tabs li").click(function () {
            var type = $(this).attr("type");
            check_cate(type, 1)

            $("ul.tabs li").removeClass("active"); //Remove any "active" class
            $(this).addClass("active"); //Add "active" class to selected tab
            $(".tab_content").hide(); //Hide all tab content
            var activeTab = $(this).find("a").attr("href"); //Find the rel attribute value to identify the active tab + content
            $(activeTab).fadeIn(); //Fade in the active content
            return false;
        });
    });

    $(window).bind('scroll', function () {
        // 当滚动到最底部以上100像素时， 加载新内容
        if ($(document).height() - $(this).scrollTop() - $(this).height() < 100) {
            if (ajaxover) { //当ajaxover为真的时候，说明还有未加载的产品
                $(".get_more").trigger('click');
                ajaxover = false;   //防止连续请求
            } else {
                return false;
            }
        }
    });

    //  百度地图，获取当前位置。
    getAddr()
    function getAddr() {
        var geolocation = new BMap.Geolocation();
        var pt;
        geolocation.getCurrentPosition(function (r) {
            if (this.getStatus() == BMAP_STATUS_SUCCESS) {
                pt = r;
                showPosition(pt);
            }
        });
    }
    //百度地图WebAPI 坐标转地址
    function showPosition(r) {
        // ak = appkey 访问次数流量有限制
        var url = 'http://api.map.baidu.com/geocoder/v2/?ak=<?=$ak_baidu?>&callback=?&location=' + r.point.lat + ',' + r.point.lng + '&output=json&pois=1';
        var a_url = 'http://api.map.baidu.com/marker?location=' + r.point.lat + ',' + r.point.lng + '&output=html';
        $.getJSON(url, function (res) {
            $("#address").attr("href", a_url);
            $("#address").attr("lng", r.point.lng);
            $("#address").attr("lat", r.point.lat);
            $("#address").append(res.result.formatted_address);
        });
    }
    //百度地图JS API 坐标转地址，没有加载地图时获取不到rs,总是null
    function getLocation(myGeo, pt, rs) {
        // 根据坐标得到地址描述
        myGeo.getLocation(pt, function (rs) {
            if (rs) {
                var addComp = rs.addressComponents;
                window.clearInterval(interval);
            }
            return rs;
        });
    }
</script>
</body>
</html>

