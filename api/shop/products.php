<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/api/shop/global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');

use Illuminate\Database\Capsule\Manager as DB;

if(isset($_GET["ProductsID"])){
	$ProductsID=$_GET["ProductsID"];
}else{
	echo '缺少必要的参数';
	exit;
}

//产品信息
//Is_Union: 1:联盟商家 0:普通商家
$rsProducts = $DB->GetRs('shop_products', 'p.*, b.expiredate, b.Is_Union', ' as p left join biz as b on p.Biz_ID = b.Biz_ID where p.Users_ID = "' . $UsersID . '" and p.Products_ID = ' . $ProductsID . ' and b.expiredate > ' . time());

if(!$rsProducts){
	echo "此商品不存在或已下架";
	exit;
}

if($rsProducts["Products_Status"]==0){
	echo "此商品不存在或已下架";
	exit;
}
if($rsProducts['Products_Parameter']){
    $parameter_list = json_decode($rsProducts['Products_Parameter'],true);  //参数
}

$ImgPath = get_prodocut_cover_img($rsProducts);
$JSON = json_decode($rsProducts['Products_JSON'],true);
$rsProducts  = handle_product($rsProducts);
$rsProducts["Products_Description"] = htmlspecialchars_decode($rsProducts["Products_Description"],ENT_QUOTES);

$UserID = 0;
$Is_Distribute = 0;
$un_accept_coupon_num = 0;
$rsProducts['Products_IsFavourite'] = 0;

$from_type = 'shop';
if (strpos($_SERVER['REQUEST_URI'], 'pintuan') !== false) {
    $from_type = 'pintuan';
}

//默认开关
require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/defaut_switch.php');

//模块开关的开启
$perm_config = array();
$rs = $DB->Get('permission_config','*','where Users_ID="'.$UsersID.'"  AND Is_Delete = 0 AND Perm_Tyle = 4  order by Permission_ID asc');
while($rs = $DB->fetch_assoc()){
	$perm_config[$rs['Perm_Field']] = $rs;
}

//用户已登录
if(!empty($_SESSION[$UsersID."User_ID"])){
    $UserID = $_SESSION[$UsersID."User_ID"];
    $rsUser = $DB->GetRs("user","Is_Distribute,User_Level","where User_ID=" . $UserID. " and Users_ID='".$UsersID."'");
    $Is_Distribute = $rsUser['Is_Distribute'];

	//获取用户未领取的优惠券数
    $sql = "SELECT COUNT(*) as num FROM user_coupon WHERE Users_ID='".$UsersID."' and Coupon_StartTime<".time()." and Coupon_EndTime>".time()." and user_coupon.Coupon_ID NOT IN ( SELECT Coupon_ID FROM user_coupon_record WHERE Users_ID='".$UsersID."' and User_ID = ".$UserID." ) and Is_Delete=0 order by Coupon_CreateTime desc";
    $result = DB::select($sql);
	$un_accept_coupon_num = $result[0]['num'];

	//判断此商品是否被当前登录用户收藏
	$rsFavourites = $DB->getRs('user_favourite_products',"Products_ID","where User_ID='".$UserID."' and Products_ID=".$ProductsID." and MID = '" . $from_type . "'");
	if($rsFavourites != FALSE){
		$rsProducts['Products_IsFavourite'] = 1;
	}	
		
	//获取登录用户的用户级别及其是否对应优惠价
}

$flag = 0;
//得到该商品的属性josn
$rsAttr = $DB->GetRs("shop_products","skujosn,skuvaljosn,skuimg","where Users_ID='".$UsersID."' and Products_ID=".$ProductsID);
$rsattrarr = json_decode($rsAttr['skujosn'], true);
if (count($rsattrarr) > 0) {
	$flag = 1;
}
$cur_jiage = $rsProducts['Products_PriceX'];

// 查询活动, 判断是否是限时抢购跳转
$flashsale = 0;
if (!empty($_GET['flashsale_flag'])) {
    $flashsale = pro_flashsale_flag($rsProducts, $UsersID);
}

if ($flashsale) {
    $cur_price = $rsProducts["flashsale_pricex"];
} else if ($from_type == 'pintuan') {
    $cur_price = $rsProducts["pintuan_pricex"];
} else {
    $cur_price = $rsProducts["Products_PriceX"];
}

/*$skuvaljosn = !empty($rsAttr['skuvaljosn']) ? json_decode($rsAttr['skuvaljosn'], 1) : [];
$min_property = $max_property = 0;
if(!empty($skuvaljosn)){
    $price = [];
    foreach ($skuvaljosn as $k => $v){
        if($flashsale){
            $price[] = $v['Txt_XsPriceSon'];
        } else if ($from_type == 'pintuan') {
            $price[] = $v['Txt_PtPriceSon'];
        } else{
            $price[] = $v['Txt_PriceSon'];
        }
    }
    $min_property = min($price);
    $max_property = max($price);
}*/

//会员级别对应的价格
$current_discount = 0;
$rsUserConfig = $DB->GetRs("User_Config","UserLevel","where Users_ID='".$UsersID."'");
$discount_list = $rsUserConfig["UserLevel"] ? json_decode($rsUserConfig["UserLevel"],TRUE) : array();
if(!empty($discount_list)){
	if(count($discount_list)>1){
		foreach($discount_list as $key=>$item){
			if(empty($item['Discount'])){
				$item['Discount'] = 0;
			}
			$discount_price = $rsProducts['Products_PriceX']*(1-$item['Discount']/100);
			$discount_price = sprintf("%.2f",substr(sprintf("%.3f", $discount_price), 0, -1));
			
			$discount_list[$key]['price'] =  $discount_price;
			$cur = 0;
			if(!empty($rsUser["User_Level"])){
				if($rsUser['User_Level'] == $key){
					$cur = 1;
					$cur_price = $discount_price;
					$current_discount = (1-$item['Discount']/100);
				}else{
					$cur = 0;
				}
			}
			$discount_list[$key]['cur'] =  $cur;
		}
	}
	array_shift($discount_list);
}
$no_attr_price = $cur_price;
$cur_price = (string)((string)$cur_price * 100) / 100;

/*获取本店满多少减多少条件*/
//$man_list = json_decode($rsConfig['Man'],true);
/*获取此商品thumb*/

//自定义分享
if(!empty($share_config)){
    if ($from_type == 'pintuan') {
        $share_config["link"] = $pintuan_url . 'xiangqing/' . $ProductsID . '/';
    } else {
        $share_config["link"] = $shop_url . 'products/' . $ProductsID . '/';
    }
	$share_config["title"] = $rsProducts["Products_Name"];
	if($owner['id'] != '0' && $rsConfig["Distribute_Customize"]==1){	
		$share_config["desc"] = $owner['shop_announce'] ? $owner['shop_announce'] : $rsConfig["ShareIntro"];
        if (!empty($rsProducts['ImgPath'])) {
            $share_config["img"] = strpos($rsProducts['ImgPath'],"http://")>-1 ? $rsProducts['ImgPath'] : 'http://'.$_SERVER["HTTP_HOST"].$rsProducts['ImgPath'];
        } else {
            $share_config["img"] = strpos($owner['shop_logo'],"http://")>-1 ? $owner['shop_logo'] : 'http://'.$_SERVER["HTTP_HOST"].$owner['shop_logo'];
        }
	}else{
		$share_config["desc"] = $rsConfig["ShareIntro"];
        if (!empty($rsProducts['ImgPath'])) {
            $share_config["img"] = strpos($rsProducts['ImgPath'],"http://")>-1 ? $rsProducts['ImgPath'] : 'http://'.$_SERVER["HTTP_HOST"].$rsProducts['ImgPath'];
        } else {
            $share_config["img"] = strpos($rsConfig['ShareLogo'],"http://")>-1 ? $rsConfig['ShareLogo'] : 'http://'.$_SERVER["HTTP_HOST"].$rsConfig['ShareLogo'];
        }
	}

    //商城分享相关业务
    if ($from_type != 'pintuan') {
        include("share.php");
    }
}
$DB->query("SELECT b.Biz_Name,b.Is_Union,b.Biz_Ext,b.Is_BizStores,b.Biz_Logo,b.Biz_ID,b.Biz_KfProductCode,g.Group_IsStore FROM biz as b,biz_group as g WHERE b.Group_ID=g.Group_ID and b.Biz_ID=".$rsProducts["Biz_ID"]);
$rsBiz = $DB->fetch_assoc();
if(empty($rsBiz["Group_IsStore"])){
	$IsStore = 0;
}else{
	$IsStore = $rsBiz["Group_IsStore"];
	if ($rsBiz['Is_Union'] == 1) {
		$biz_url = $base_url.'api/'.$UsersID.'/'.($owner['id']==0 ? '' : $owner['id'].'/').'shop/biz_xq/'.$rsBiz["Biz_ID"].'/';
	}else{
		$biz_url = $base_url.'api/'.$UsersID.'/'.($owner['id']==0 ? '' : $owner['id'].'/').'biz/'.$rsBiz["Biz_ID"].'/';
	}
}
//获取产品相关评论
$comment_aggregate = get_comment_aggregate($DB, $UsersID, $ProductsID);

$sql = 'SELECT c.Product_ID,c.Score,c.Note,c.CreateTime,u.User_Mobile,u.User_NickName,u.User_HeadImg,o.Order_CartList FROM user_order_commit AS c LEFT JOIN user AS u ON c.User_ID = u.User_ID LEFT JOIN user_order AS o ON c.Order_ID = o.Order_ID WHERE c.Status = 1 AND c.Product_ID = ' . $ProductsID . ' ORDER BY c.CreateTime DESC limit 3';
$DB->query($sql);
$commit_list = $DB->toArray();

// 因拼团和参团页面直接引入
if ($from_type != 'pintuan') {
    include("skin/products.php");
}
?>