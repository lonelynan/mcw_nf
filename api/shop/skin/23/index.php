<?php
$Dwidth = array('640','210','210','210','320','320');
$DHeight = array('320','210','210','210','210','210');
$Home_Json=json_decode($rsSkin['Home_Json'],true);
for($no=1;$no<=5;$no++){
    $json[$no-1]=array(
        "ContentsType"=>$no==1?"1":"0",
        "Title"=>$no==1?json_encode($Home_Json[$no-1]['Title']):$Home_Json[$no-1]['Title'],
        "ImgPath"=>$no==1?json_encode($Home_Json[$no-1]['ImgPath']):$Home_Json[$no-1]['ImgPath'],
        "Url"=>$no==1?json_encode($Home_Json[$no-1]['Url']):$Home_Json[$no-1]['Url'],
        "Postion"=>"t0".$no,
        "Width"=>$Dwidth[$no-1],
        "Height"=>$DHeight[$no-1],
        "NeedLink"=>"1"
    );
}

$banner = [];
$cate = [];
foreach ($json as $k => $v){
    if($v['ContentsType'] == 1){
       $banner = $v;
    }else{
       $cate[] = $v;
    }
}

$cate_list = $DB->GetAssoc('shop_category','*','where Users_ID="'. $UsersID .'" and Category_ParentID=0 order by Category_Index');

if(!empty($_POST)){
    if(empty($_POST['cate_id'])){
        //获取新品
        $counts = $DB->GetRs("shop_products","count(Products_ID) as count","where Users_ID='".$UsersID."' and Products_SoldOut=0 and Products_IsRecommend=1 and Products_Status=1");
        $num = 4;//每页记录数
        $p = !empty($_POST['p'])?intval(trim($_POST['p'])):1;
        $total = $counts['count'];//数据记录总数
        $totalpage = ceil($total/$num);//总计页数
        $limitpage = ($p-1)*$num;//每次查询取记录

        $rsNewProducts = $DB->GetAssoc("shop_products","p.Products_Name,p.Products_ID,p.Products_JSON,p.Rect_Img,p.Products_PriceA,p.Products_PriceX,p.Products_PriceY,p.Products_Sales,p.pintuan_flag,p.pintuan_people,p.pintuan_start_time,p.pintuan_end_time,p.pintuan_pricex,p.flashsale_flag,p.flashsale_pricex","as p left join biz as b on b.Biz_ID=p.Biz_ID where p.Users_ID='".$UsersID."' and p.Products_IsRecommend=1 and b.Biz_Status=0 and p.Products_SoldOut=0 and p.Products_Status=1 order by p.Products_ID desc limit $limitpage,$num");

        $rsNewProducts = handle_product_list($rsNewProducts, $shop_url, $pintuan_url);
        if(count($rsNewProducts) > 0){
            $data = array(
                'list' => $rsNewProducts,
                'totalpage' => $totalpage,
            );
        }else{
            $data = array(//没有数据可加载
                'list' => '',
                'totalpage' => $totalpage,
            );
        }
        echo json_encode($data);
    }else{
        //根据栏目id的产品列表
        $new_products = array();
        $counts = $DB->GetRs("shop_products","count(Products_ID) as count","where Products_SoldOut=0 and Products_IsRecommend=1 and Products_Status=1");
        $num = 4;//每页记录数
        if($counts['count'] <= $num){
            $show_load_btn = 0;
        }
        $p = !empty($_POST['p'])?intval(trim($_POST['p'])):1;
        $total = $counts['count'];//数据记录总数
        $totalpage = ceil($total/$num);//总计页数
        $limitpage = ($p-1)*$num;//每次查询取记录

        $rsNewProducts = $DB->GetAssoc("shop_products","p.Products_Name,p.Products_ID,p.Products_JSON,p.Rect_Img,p.Products_PriceA,p.Products_PriceX,p.Products_Sales,p.flashsale_flag,p.flashsale_pricex"," as p left join biz as b on b.Biz_ID = p.Biz_ID where p.Users_ID='".$UsersID."' and p.Products_IsRecommend=1 and b.Biz_Status=0 and p.Products_SoldOut=0 and p.Products_Status=1 order by p.Products_ID desc limit $limitpage,$num");

        $new_products = handle_product_list($rsNewProducts, $shop_url, $pintuan_url);
        if(count($new_products)>0){
            $data = array(
                'list' => $new_products,
                'totalpage' => $totalpage,
            );
        }else{
            $data = array(//没有数据可加载
                'list' => '',
                'totalpage' => $totalpage,
            );
        }
        echo json_encode($data);
    }
    exit;
}

include("skin/top.php");
?>

<link rel="stylesheet" href="/static/api/css/swiper.min.css">
<script type="text/javascript" src="/static/api/js/swiper.min.js"></script>
<link href='/static/js/plugin/flexslider/flexslider.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/plugin/flexslider/flexslider.js'></script>
<link href='/static/api/shop/skin/<?php echo $rsConfig["Skin_ID"] ?>/style_media.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/api/shop/js/index.js'></script>
<script language="javascript">
    var shop_skin_data=<?php echo json_encode($json) ?>;$(document).ready(index_obj.index_init);
</script>
<body>
<style>
    .swiper-container-horizontal .sw_hd_x {bottom:0;}
    .swiper-container-horizontal .sw_hd_x .swiper-pagination-bullet{background: #dfdfdf; border-radius: 0; width: 18px; height: 3px; margin: 0;}
    .swiper-container-horizontal .sw_hd_x .swiper-pagination-bullet-active{background: #ff4800; border-radius: 0; width: 18px; height: 3px; margin: 0;}
    a.shop_kai:hover{color:#fff;}
</style>
<div class="w">
    <div class="head_bg_sc">
        <span class="hesd_left_sc">
            <a href="/api/<?=$UsersID?>/shop/category/"><img src="/static/api/shop/skin/<?php echo $rsConfig["Skin_ID"] ?>/shangcheng_pt_03_03.png"></a>
        </span>

        <form id="sub" action="/api/shop/search.php" method="get">
            <input style="border-radius: 80px;" type="text" name="kw" placeholder="请输入商品名称" class="search_sc">
            <input type="hidden" name="UsersID" value="<?php echo $UsersID;?>" />            
            <input type="hidden" name="OwnerID" value="<?php echo $owner['id'] != '0' ? $owner['id'] : '';?>" />
            <span class="ss_img_sc">
                <img src="/static/api/shop/skin/<?php echo $rsConfig["Skin_ID"] ?>/sj_list_11_s.png" ONCLICK="$('#sub').submit()">
            </span>
        </form>

        <span class="xiaoxi_sc">
            <a href="/api/<?= $UsersID ?>/user/message/"><img src="/static/api/shop/skin/<?php echo $rsConfig["Skin_ID"] ?>/shangcheng_pt_03_05.png"></a>
        </span>
    </div>
    <div style="height:46px;"></div>
    <div class="swiper-container sw_hd">
        <div class="swiper-wrapper">
            <?php for($i = 1; $i <= ceil(count($cate_list) / 10); $i++){?>
            <div class="swiper-slide">
                <ul>
                    <?php foreach ($cate_list as $k => $v){
                        if ($k < ($i - 1) * 10) continue;
                        if ($k >= $i * 10) break; 
						$rshavepro = $DB->GetRs('shop_products', 'Products_ID', 'where Users_ID = "' . $UsersID . '" and Father_Category = ' . $v['Category_ID']);
						if (!$rshavepro) continue;
						?>
                    <li>
                        <a href="/api/<?=$UsersID?>/shop/pro_list/<?=$v['Category_ID']?>/">
                            <img src="<?=!empty($v['Category_Img']) ? $v['Category_Img'] : "/static/images/none.png" ?>">
                            <span class="cate_name"><?=$v['Category_Name']?></span>
                        </a>
                    </li>
                    <?php }?>
                </ul>
            </div>
            <?php }?>
        </div>
        <div class="swiper-pagination sw_hd_x"></div>
    </div>
    <div class="clear"></div>
    <div id="shop_skin_index">
        <div class="shop_skin_index_list banner img_gg" rel="edit-t01" style="height: auto;">
            <a href="<?= !empty($banner['Url'][0]) ? $banner['Url'][0] : 'javascript:;' ;?>"><div class="img"></div></a>
        </div>

        <div class="gg_bg">
            <ul>
              <?php foreach ($cate as $k => $v){?>
               <li> <div class="shop_skin_index_list i2" rel="edit-t0<?=$k+2?>">
                    <a href="<?= !empty($v['Url']) ? $v['Url'] : 'javascript:;' ;?>"><div class="img"></div></a>
                </div>
               </li>
              <?php }?>
            </ul>
        </div>
    </div>

    <div class="clear"></div>

    <ul id="more" style="overflow:hidden;"></ul>
    <div class="pullUp get_more" cate_id="" page="1" style="text-align: center;"> <span class="pullUpIcon"></span><span class="pullUpLabel">点击加载更多...</span> </div>
    <div class="clear"></div>
    <!--<div style="height:45px;"></div>-->
</div>
<script type="text/javascript">
    var UsersID = "<?=$UsersID?>";
    $(function () {
        //banner
        var mySwiper = new Swiper('.sw_hd', {
            direction: 'horizontal',
            loop: true,
            autoResize: true,
            pagination: '.sw_hd_x',
            paginationClickable: true,
            autoplayDisableOnInteraction: false
        });
    });
</script>
<?php require_once('skin/distribute_footer.php'); ?>
<?php include("yh_index_ajax.php");?>
</body>
</html>
