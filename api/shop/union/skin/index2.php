<?php
$Home_Json = json_decode($rsSkin['Home_Json'], true);
$banner_list = [];
$ad_list = [];
if (!empty($Home_Json) && count($Home_Json) > 0) {
    foreach ($Home_Json as $k => $v) {
        if ($v['ContentsType'] == 1) {  //banner
            foreach ($v['ImgPath'] as $kk => $vv) {
                if (strpos($vv, '/api/') === false) {
                    $banner_list[$kk]['ImgPath'] = !empty($vv) ? $vv : '';
                } else {
                    $banner_list[$kk]['ImgPath'] = !empty($vv) ? '/static' . $vv : '';
                }
                $banner_list[$kk]['Title'] = !empty($v['Title'][$kk]) ? $v['Title'][$kk] : '';
                $banner_list[$kk]['Url'] = !empty($v['Url'][$kk]) ? $v['Url'][$kk] : '';
            }
        }
        if ($v['ContentsType'] == 4) {  //广告
            if (strpos($v['ImgPath'], '/api/') === false) {
                $v['ImgPath'] = !empty($v['ImgPath']) ? $v['ImgPath'] : '';
            } else {
                $v['ImgPath'] = !empty($v['ImgPath']) ? '/static' . $v['ImgPath'] : '';
            }
            $ad_list[] = [
                'ImgPath' => $v['ImgPath'],
                'Url' => $v['Url']
            ];
        }
    }
}

//获取行业分类的一级分类
$catlist = $DB->GetAssoc("biz_union_category", "Category_ID,Category_Img,Category_Name", "where Users_ID='" . $UsersID . "' and Category_ParentID = 0 order by Category_Index asc,Category_ID asc limit 10");
if(!empty($catlist)){
	foreach($catlist as $k => $v){
		if((strpos($v["Category_Img"], 'http') === false && !file_exists($_SERVER['DOCUMENT_ROOT'] . $v["Category_Img"])) || empty($v["Category_Img"])){
			$catlist[$k]["Category_Img"] = "/static/images/none.png";
		}
	}
}

function get_childs($DB, $table, $fields, $condition)
{
    $child = [];
    $DB->Get($table, $fields, $condition);
    while ($r = $DB->fetch_assoc()) {
        $child[] = $r[$fields];
    }
    return count($child) > 0 ? implode(",", $child) : '';
}

//获取定位
if (empty($rsConfig['DefaultAreaID'])) {
    $DefaultAreaID = 240;
} else {
    $DefaultAreaID = $rsConfig['DefaultAreaID'];
}
if (empty($rsConfig['DefaultLng'])) {
    $DefaultLng = 100;
} else {
    $DefaultLng = $rsConfig['DefaultLng'];
}

$rsDefaultArea = $DB->GetRs("area", "area_id,area_name,area_code", " where area_id=" . $DefaultAreaID);

//获取店铺
if (!empty($_POST) && $_POST['action'] == 'get_index_list') {
    if (!empty($_POST['lat'])) {
        $_SESSION['firstlat'] = $_POST['lat'];
        $_SESSION['firstlng'] = $_POST['lng'];
    }

    $condition = "where Users_ID='" . $UsersID . "' and Biz_IndexShow=1 and Is_Union=1 and Biz_Status=0";
    if (!empty($_POST['lat']) && !empty($_POST['lng'])) {
        $rsArea = 0;
    } else {
        if (isset($_COOKIE['city'])) {
            $areaid = explode('|', $_COOKIE['city'])[0];
        } else {
            $areaid = $rsDefaultArea['area_id'];
        }

        if ($areaid != 0) {//0为全国
            $rsArea = $DB->GetRs("area", "area_id,area_name", " where area_id=" . $areaid);
            if (!$rsArea) {
                $rsConfig = shop_config($UsersID);
                $areaid = empty($rsConfig['DefaultAreaID']) ? 240 : $rsConfig['DefaultAreaID'];
            }
            $childids = get_childs($DB, "area", "area_id", "where area_parent_id=" . $areaid);
            $areaids = $childids ? $areaid . ',' . $childids : $areaid;
            $condition .= " and Area_ID in(" . $areaids . ")";
        }
    }
	
	if(!empty($_POST['lat']) && !empty($_POST['lng'])){
	$condition .= " order by ACOS(SIN((".$_POST['lat']." * 3.1415) / 180 ) *SIN((Biz_PrimaryLat * 3.1415) / 180 ) +COS((".$_POST['lat']." * 3.1415) / 180 ) * COS((Biz_PrimaryLat * 3.1415) / 180 ) *COS((".$_POST['lng']." * 3.1415) / 180 - (Biz_PrimaryLng * 3.1415) / 180 ) ) * 6380 asc";
	}
	
    $rsBiz = $DB->GetAssoc("biz", "Biz_ID,Biz_Name,Biz_Logo,Biz_Address,Region_ID,Biz_PrimaryLng,Biz_PrimaryLat", $condition);
    foreach ($rsBiz as $key => $val) {
        $rsBiz[$key]['url'] = "/api/" . $UsersID . "/shop/biz_xq/" . $val['Biz_ID'] . "/";
        $rsCommit = $DB->GetRs("user_order_commit", "count(*) as num, sum(Score) as score", "where Users_ID='" . $UsersID . "' and (MID='shop' or MID='offline_st' or MID='offline_qrcode') and Status=1 and Biz_ID=" . $val['Biz_ID']);
        $rsBiz[$key]['average_ico'] = $rsCommit["num"] == 0 ? '0.5' : round(($rsCommit["score"] / $rsCommit["num"]));
        $rsBiz[$key]['average_num'] = number_format($rsBiz[$key]['average_ico'], 1, '.', '');
        //距离
        if (!empty($_POST['lat']) && !empty($_POST['lng'])) {
            $rsBiz[$key]['meter'] = GetDistance($_POST['lat'], $_POST['lng'], $val['Biz_PrimaryLat'], $val['Biz_PrimaryLng']);
        } else {
            $rsBiz[$key]['meter'] = '';
        }
		if((strpos($val['Biz_Logo'], 'http') === false && !file_exists($_SERVER['DOCUMENT_ROOT'] . $val['Biz_Logo'])) || empty($val['Biz_Logo'])){
			$rsBiz[$key]['Biz_Logo'] = "/static/images/no_pic1.png";
		}
        //打折
        $rsProduct = $DB->GetAssoc("shop_products", "Products_PriceY,Products_PriceX,Products_PriceA", " where Users_ID='" . $UsersID . "' AND Biz_ID=" . $val['Biz_ID']);
        foreach ($rsProduct as $k => $value) {
            if ($value['Products_PriceY'] > 0) {
                $total = $value['Products_PriceX'] / $value['Products_PriceY'] * 10;
                $discount[$key][] = floor($total);
            }
        }
        if (isset($discount[$key]) && !empty($discount[$key])) {
            $rsBiz[$key]['discoun_min'] = min($discount[$key]);
        } else {
            $rsBiz[$key]['discoun_min'] = 10;
        }
        //优惠券
        $rsCoupon = [];
        $DB->Get("user_coupon", "*", "where Biz_ID=" . $val["Biz_ID"]);
        while ($rsCou = $DB->fetch_assoc()) {
            $rsCoupon[] = $rsCou;
        }
        foreach ($rsCoupon as $ky => $vals) {
            if ($vals['Coupon_EndTime'] < time()) {
                unset($rsCoupon[$ky]);
            }
        }
        if (!empty($rsCoupon)) {
            $rsBiz[$key]['rsCoupon'] = 1;
        } else {
            $rsBiz[$key]['rsCoupon'] = 0;
        }
        $DB->query('select count(*) as num from user_order where Biz_ID=' . $val["Biz_ID"]);
        $yuecount = $DB->fetch_assoc();
        $rsBiz[$key]['yuecount'] = $yuecount['num'];
    }

    if (!empty($_POST['lat']) && !empty($_POST['lng'])) {
        $rsBiz = fxy_array_sort($rsBiz, 'meter');
    }
    $counts = count($rsBiz);
    $num = 10;//每页记录数
    $p = !empty($_POST['p']) ? intval(trim($_POST['p'])) : 1;
    $total = $counts;//数据记录总数
    $totalpage = ceil($total / $num);//总计页数
    $min_offset = ($p - 1) * $num;//每次查询取记录
    $max_offset = $p * $num - 1 > $total ? $total - 1 : $p * $num - 1;
    $list = [];
    for ($i = $min_offset; $i <= $max_offset; $i++) {
        $list[] = isset($rsBiz[$i]) ? $rsBiz[$i] : [];
    }
    //去除空值
    foreach ($list as $k => $v) {
        if (empty($list[$k])) {
            unset($list[$k]);
        } else {
            $list[$k]['commit_count'] = $DB->GetRs('user_order_commit', 'count(Item_ID) as commit_count', 'where Biz_ID = ' . $v['Biz_ID'])['commit_count'];
        }
    }

    $data = [
        'list' => !empty($list) ? $list : '',
        'totalpage' => $totalpage,
        'newlat' => isset($_SESSION['firstlat']) ? $_SESSION['firstlat'] : '',
        'newlng' => isset($_SESSION['firstlng']) ? $_SESSION['firstlng'] : '',
    ];
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
} else if (!empty($_POST) && $_POST['action'] == 'dcact') {
    setcookie('dcact', $_POST['actval'], 0, '/');
    //没有数据可加载
    $data = [
        'status' => 1,
    ];
    echo json_encode($data);
    exit;
}

// 获取城市ID
if (isset($_COOKIE['city'])) {
    $cityid_tmp = explode('|', $_COOKIE['city']);
    $cityid = $cityid_tmp[0];
} else {
    $cityid = 0;
}
if (empty($cityid)) $cityid = 0;
//获取产品
$pageSize = 10;
$p = !empty($_POST['p']) ? intval(trim($_POST['p'])) : 1;
$rsProducts = $DB->GetAssoc('shop_products', 'p.*', ' as p left join biz as b on p.Biz_ID = b.Biz_ID where p.Users_ID = "' . $UsersID . '" and p.Products_Status = 1 and p.Products_IsRecommend = 1 and p.Products_SoldOut = 0 and b.Is_Union = 1 and b.Biz_Status=0 and b.Biz_City=' . $cityid .' order by p.Products_CreateTime desc limit ' . ($p - 1) * $pageSize . ',' . $pageSize);

if (!empty($rsProducts)) {
    foreach ($rsProducts as $key => $value) {
        //产品图片处理
        $value['Products_JSON'] = json_decode($value['Products_JSON'], true);

		if((strpos($value['Products_JSON']['ImgPath'][0], 'http') === false && !file_exists($_SERVER['DOCUMENT_ROOT'] . $value['Products_JSON']['ImgPath'][0])) || empty($value['Products_JSON']['ImgPath'][0])){
			$value['pro_img'] = "/static/images/no_pic1.png";
		}else{
			$value['pro_img'] = $value['Products_JSON']['ImgPath'][0];
		}
        $value['pt_flag'] = pro_pt_flag($value);    //产品是否符合拼团条件
        //产品url

        if ($value['pt_flag']) {
            $value['url'] = $pintuan_url . 'xiangqing/' . $value['Products_ID'] . '/';
        } else {
            $value['url'] = $shop_url . 'products/' . $value['Products_ID'] . '/';
        }

        //限时抢购
        $value['flashsale_flag'] = $value['flashsale_flag'] && $flashsale;
        $value['Products_PriceX'] = $value['flashsale_flag'] == 1 ? $value['flashsale_pricex'] : ($value['pt_flag'] ? $value['pintuan_pricex'] : $value['Products_PriceX']);

        $value['Products_PriceY'] = $value['pt_flag'] ? $value['Products_PriceX'] : $value['Products_PriceY'];

        $rsProducts[$key] = $value;
    }
}

$totalCount = $DB->GetRs('shop_products', 'count(p.Products_ID) as totalCount', ' as p left join biz as b on p.Biz_ID = b.Biz_ID where p.Users_ID = "' . $UsersID . '" and p.Products_Status = 1 and p.Products_SoldOut = 0 and b.Is_Union = 1')['totalCount'];
$pro_data = [
    'list' => !empty($rsProducts) ? $rsProducts : '',
    'get_flag' => ceil($totalCount / $pageSize) > $p ? true : false
];

if (isset($_POST['action']) && $_POST['action'] == 'get_pro') {
    echo json_encode($pro_data, JSON_UNESCAPED_UNICODE);
    exit;
}

//短消息
if (isset($_SESSION[$UsersID . 'User_ID'])) {
    $sql = "SELECT COUNT(*) AS total FROM `user_message` WHERE Users_ID='" . $UsersID . "' AND Message_ID NOT IN (SELECT Message_ID FROM user_message_record WHERE Users_ID='" . $UsersID . "' AND User_ID=" . $_SESSION[$UsersID . 'User_ID'] . ")";
    $query = $DB->query($sql);
    $msg = $DB->fetch_assoc();
    $messageTotal = (int)$msg['total'];
} else {
    $messageTotal = 0;
}

//定位城市名称
if (isset($_COOKIE['city'])) {
    $cityname_tmp = explode('|', $_COOKIE['city']);
    $cityname = $cityname_tmp[1];
} else {
    $cityname = $rsDefaultArea['area_name'];
}

//获取公告
$announce = $DB->GetAssoc("shop_notices", "Notice_Title, Notice_ID", "where Users_ID = '" . $UsersID . "' order by Notice_ID desc limit 5");

?>
<?php require_once('top.php'); ?>

<link rel="stylesheet" href="/static/js/plugin/swiper/swiper.min.css">
<script type="text/javascript" src="/static/js/plugin/swiper/swiper.min.js"></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?= $ak_baidu ?>"></script>
<script language="javascript">
    var baidu_map_ak = '<?= $ak_baidu ?>';
    var app_ajax_url = '<?= $shop_url . 'ajax/' ?>';
    var UsersID = '<?= $UsersID ?>';
    var minul = '<?= $act ?>';
    var SiteUrl = "http://" + window.location.host;
</script>
<body>
<style>
    .sw_tou {
        height: 30px;
        overflow: hidden;
        line-height: 30px;
        font-size: 13px;
        width: 100%;
    }

    .sw_tou .swiper-slide {
        height: 30px;
        line-height: 30px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .sw_tou .swiper-slide a {
        color: #333;
    }

    .swiper-pagination-bullet {
        background: none;
        border: 1px #ff494e solid;
    }

    .swiper-pagination-bullet-active {
        background: #ff494e;
        border: 1px #ff494e solid;
    }
</style>
<div class="w">
    <div class="head_bg">
        <span class="hesd_left" onclick="location.href='/api/<?= $UsersID ?>/city/';" rel="<?= $rsDefaultArea['area_code'] ?>"><?= $cityname ?><img src="/static/api/shop/union/2/index_06.png"></span>
        <form method="get" action="/api/shop/search.php">
            <input type="text" name="kw" maxlength="20" placeholder="请输入商品名称" class="search">
			<input type="hidden" name="UsersID" value="<?php echo $UsersID;?>" />
            <input type="hidden" name="Is_Union" value="1"/>
            <input type="hidden" name="OwnerID" value="<?php echo $owner['id'] != '0' ? $owner['id'] : '';?>" />
            <span class="ss_img" onclick="$(this).parents('form').submit()"><img src="/static/api/shop/union/2/ss.png"></span>
        </form>
        <span class="xiaoxi">
            <a href="/api/<?= $UsersID ?>/user/message/">
                <img src="/static/api/shop/union/2/index_03.png">
                <?php if ($messageTotal > 0) { ?>
                    <b class="car_num"><?= $messageTotal > 99 ? '•••' : $messageTotal ?></b>
                <?php } ?>
            </a>
        </span>
    </div>
    <div style="height:40px;"></div>
    <div class="swiper-container sw_hd">
        <div class="swiper-wrapper">
            <?php if (!empty($banner_list)) {
                foreach ($banner_list as $key => $value) { ?>
                    <div class="swiper-slide">
                        <a href="<?= !empty($value['Url']) ? $value['Url'] : 'javascript:;' ?>"><img class="banner" src="<?= $value['ImgPath'] ?>"/></a>
                    </div>
                <?php }
            } ?>
        </div>
        <div class="swiper-pagination sw_hd_x"></div>
    </div>
    <div class="clear"></div>
    <?php if (!empty($catlist)) { ?>
        <div class="fl_list">
            <ul>
                <?php foreach ($catlist as $key => $value) {?>
                    <li><a href="<?= $shop_url ?>bizlist/<?= $value["Category_ID"] ?>/"><img src="<?= $value["Category_Img"] ?>"><?= $value["Category_Name"] ?></a></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>
    <div class="clear"></div>
    <?php if (!empty($announce)) { ?>
        <div class="toutiao">
            <img src="/static/api/shop/union/2/index_30.png">
            <span class="right_tou">
                <div class="swiper-container sw_tou">
                    <div class="swiper-wrapper">
                        <?php foreach ($announce as $key => $value) { ?>
                            <div class="swiper-slide">
                                <a href="<?= $shop_url ?>notice/<?= $value['Notice_ID'] ?>/"><?= htmlspecialchars_decode($value['Notice_Title']) ?></a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </span>
        </div>
    <?php } ?>
    <div class="clear"></div>
    <div class="ggimg">
        <a href="<?= !empty($ad_list[0]['Url']) ? $ad_list[0]['Url'] : 'javascript:;' ?>"><img src="<?= $ad_list[0]['ImgPath'] ?>"></a>
    </div>
    <div class="ggimg_two">
        <span><a href="<?= !empty($ad_list[1]['Url']) ? $ad_list[1]['Url'] : 'javascript:;' ?>"><img src="<?= $ad_list[1]['ImgPath'] ?>"></a></span>
        <span><a href="<?= !empty($ad_list[2]['Url']) ? $ad_list[2]['Url'] : 'javascript:;' ?>"><img src="<?= $ad_list[2]['ImgPath'] ?>"></a></span>
    </div>
    <div class="ggimg"><img src="/static/api/shop/union/2/index_42.jpg"></div>
    <div class="dianpu">
        <!--商家-->
        <ul class="biz_list"></ul>
        <div class="loadbtn" style="display:none;" page="1">加载更多</div>
        <a href="<?= $shop_url ?>bizlist/0/" class="more">查看更多<img src="/static/api/shop/union/2/hunli_26.png"></a>
    </div>
    <div class="clear"></div>
    <div class="ggimg"><img src="/static/api/shop/union/2/index_52.jpg"></div>
    <div class="youhui">
        <a href="<?= !empty($ad_list[3]['Url']) ? $ad_list[3]['Url'] : 'javascript:;' ?>"><img src="<?= $ad_list[3]['ImgPath'] ?>"></a>
        <a href="<?= !empty($ad_list[4]['Url']) ? $ad_list[4]['Url'] : 'javascript:;' ?>"><img src="<?= $ad_list[4]['ImgPath'] ?>"></a>
        <a href="<?= !empty($ad_list[5]['Url']) ? $ad_list[5]['Url'] : 'javascript:;' ?>"><img src="<?= $ad_list[5]['ImgPath'] ?>"></a>
    </div>
    <div class="ggimg"><img src="/static/api/shop/union/2/index_62.jpg"></div>
    <div class="product">
        <ul class="pro_list">
            <?php if (!empty($rsProducts)) {
                foreach ($rsProducts as $key => $value) { ?>
				<?php
				if((strpos($value["pro_img"], 'http') === false && !file_exists($_SERVER['DOCUMENT_ROOT'] . $value["pro_img"])) || empty($value["pro_img"])){
					$value["pro_img"] = "/static/images/none.png";
				}
				?>
                    <li>
                        <a href="<?= $value['url'] ?>">
                            <span class="pro_img"><img src="<?= $value['pro_img'] ?>"></span>
                            <span class="pro_main">
                                <p class="pro_title">
<!--                                    --><?php //if ($value['pt_flag']) { ?>
<!--                                        <span class="pt_mark">拼团</span>-->
<!--                                    --><?php //} ?>
                                    <?= $value['Products_Name'] ?>
                                </p>
                                <p class="hj_pro"><?= $value['Products_BriefDescription'] ?></p>
                                <div class="pro_pri">
                                    <span class="pro_left"><font>￥</font><?= $value['Products_PriceX'] ?><span class="pri_y"><?=$value['Products_PriceY'] ?></span></span>
                                    <span class="pro_right">已售<?= $value['Products_Sales'] ?></span>
                                </div>
                            </span>
                        </a>
                    </li>
                <?php }
            } ?>
        </ul>
        <div class="pro_more" page="1" style="display:<?= $pro_data['get_flag'] ? 'block' : 'none' ?>">加载更多</div>
        <div class="bottom_bottom" style="width:100%;height:40px;line-height:40px;text-align:center;font-size:14px;color:#999; display:none;">已到底了</div>
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    $(function () {
        //banner
        var mySwiper = new Swiper('.sw_hd', {
            direction: 'horizontal',
            loop: true,
            autoResize: true,
            pagination: '.sw_hd_x',
            paginationClickable: true,
            autoplay: 3000,
            autoplayDisableOnInteraction: false
        });
        //头条
        var ttSwiper = new Swiper('.sw_tou', {
            direction: 'vertical',
            loop: true,
            autoplay: 3000
        });
    });
</script>
<?php require_once('../skin/distribute_footer.php'); ?>

<link rel="stylesheet" href="/static/api/shop/union/2/index.css">
<link rel="stylesheet" href="/static/api/shop/union/2/index_media.css">

<?php include("yh_index_ajax.php"); ?>

<div class="container">
    <div class="row">
        <div class="modal" role="dialog" id="area-modal">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body">
                        <p style="text-align:center;">您目前所在城市为<span></span><br/>是否切换？</p>
                    </div>
                    <div class="modal-footer">
                        <a class="pull-left modal-btn" id="confirm_area_btn">确定</a>
                        <a class="pull-right modal-btn" id="cancel_area_btn">取消</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>