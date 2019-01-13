<?php
$rsSkin = $DB->GetRs("biz_home","Home_Json","where Skin_ID=".$rsBiz["Skin_ID"]." and Biz_ID=".$BizID);
$Dwidth = array('640');
$DHeight = array('480');
$Home_Json=json_decode($rsSkin['Home_Json'],true);
for($no=1;$no<=1;$no++){
	$json[$no-1]=array(
		"ContentsType"=>$no==1?"1":"0",
		"Title"=>$no==1?json_encode($Home_Json[$no-1]['Title']):$Home_Json[$no-1]['Title'],
		"ImgPath"=>$no==1?json_encode($Home_Json[$no-1]['ImgPath']):$Home_Json[$no-1]['ImgPath'],
		"Url"=>$no==1?json_encode($Home_Json[$no-1]['Url']):$Home_Json[$no-1]['Url'],
		"Postion"=> $no<=9 ? "t0".$no : "t".$no,
		"Width"=>$Dwidth[$no-1],
		"Height"=>$DHeight[$no-1],
		"NeedLink"=>"0"
	);
}
//商品列表
$lists_products = array();
$DB->get("shop_products","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID." and Products_SoldOut=0 and Products_Status=1 order by Products_CreateTime desc");
while($r=$DB->fetch_assoc()){
	$lists_products[] = $r;
}

//相关商品
$catids = array();
$DB->get("biz_category","Category_ID","where Biz_ID=".$BizID);
while($c = $DB->fetch_assoc()){
    if($c['Category_ID']){
	   $catids[] = $c["Category_ID"];
	}
}
$lists_products_relate = array();
if($catids){
    $DB->get("shop_products","*","where Users_ID='".$UsersID."' and Products_SoldOut=0 and Biz_ID<>".$rsBiz["Biz_ID"]." and Products_Status=1 and Products_IsRecommend=1 and Products_Category in(".(implode(",",$catids)).") order by Products_CreateTime desc limit 0,6");
	while($r=$DB->fetch_assoc()){
		$lists_products_relate[] = $r;
	}
}
	

//评论
$rsCommit = $DB->GetRs("user_order_commit","count(*) as num, AVG(Score) as Points","where Users_ID='".$UsersID."' and MID='shop' and Status=1 and Biz_ID=".$rsBiz['Biz_ID']);
$average_num = $rsCommit["num"];
$average_score = $rsCommit["num"]>0 ? number_format ( $rsCommit["Points"] ,  1 ,  '.' ,  '' ) : '0.5';
if($average_num>0){
	$list_commit = array();
	$result = $DB->get("user_order_commit","*","where Users_ID='".$UsersID."' and MID='shop' and Status=1 and Biz_ID=".$rsBiz['Biz_ID']." order by CreateTime desc limit 0,5");
	while($r = $DB->fetch_assoc()){
		$list_commit[] = $r;
	}
}
$rsCoupon_data = array();
if (isset($_SESSION[$UsersID.'User_ID'])) {
	$DB->query("SELECT * FROM user_coupon WHERE Users_ID='".$UsersID."' and Biz_ID=".$BizID." and Coupon_StartTime<".time()." and Coupon_EndTime>".time()." and user_coupon.Coupon_ID NOT IN ( SELECT Coupon_ID FROM user_coupon_record WHERE Users_ID='".$UsersID."' and Biz_ID=".$BizID." AND User_ID=".$_SESSION[$UsersID.'User_ID']." ) order by Coupon_CreateTime desc");
}else{
	$DB->query("SELECT * FROM user_coupon WHERE Users_ID='".$UsersID."' and Biz_ID=".$BizID." and Coupon_StartTime<".time()." and Coupon_EndTime>".time()." order by Coupon_CreateTime desc");
}

while($rsC=$DB->fetch_assoc()){
	$rsCoupon_data[] = $rsC;
}	
require_once($rsBiz["Skin_ID"]."/top.php");

// echo '<pre>';
// print_R($lists_products);
// exit;

?>
<script type='text/javascript' src='/static/api/shop/js/shop.js?t=<?php echo time();?>'></script>
<link href='/static/api/shop/biz/page.css?t=<?php echo time() ?>' rel='stylesheet' type='text/css' />
<link href='/static/js/plugin/flexslider/flexslider.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/plugin/flexslider/flexslider.js'></script>
<script type='text/javascript' src='/static/api/shop/js/index.js'></script>
<link href='/static/api/shop/union/page.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href="/static/api/shop/skin/default/css/tao_detail.css?t=<?php echo time();?>" rel="stylesheet" type="text/css" />
<link href='/static/api/css/user.css?t=<?=time()?>' rel='stylesheet' type='text/css' />
<link href="/static/api/css/fx_index.css?t=<?php echo time();?>" rel="stylesheet" type="text/css" />
<script type='text/javascript' src='/static/api/shop/js/toast.js?t=<?=time();?>'></script>
<script type='text/javascript' src='/static/api/js/global.js?t=<?php echo time();?>'></script>
<script language="javascript">
var shop_skin_data=<?php echo json_encode($json);?>;
$(document).ready(index_obj.index_init);
$(document).ready(shop_obj.youhuijuan_init);
</script>
<style>
.fen_x{ float: left;
    background: #1bcaef;
	margin: 8px 5px 0px 108px;
    width: 19px;
    height: 19px;
    border-radius: 5px;
    color: #fff;
    text-align: center;
    line-height: 19px;
    font-size: 12px;
    margin-top: 5px;}
</style>
 <div id="shop_skin_index">
    <div class="shop_skin_index_list banner" rel="edit-t01">
        <?php echo $rsBiz["Biz_Logo"] ? '<img src="'.$rsBiz["Biz_Logo"].'" width="640" />' : '';?>
    </div>
    <div class="biz_info" style="z-index: 500;">
     <p class="biz_info_name"><?php echo $rsBiz["Biz_Name"];?></p>
     <p class="biz_info_score"><img src="/static/images/star_<?php echo $average_score;?>.png" /> <?php echo $average_score;?>分 <span>&nbsp;&nbsp;<?php echo $average_num;?>人评价</span></p>
	 <?php if(!empty($rsBiz['Biz_stmdShow'])){?>
	 <?php if(!empty($rsBiz['Biz_Profit'])){?>
	 <a class="offline_pay" href="<?php echo $shop_url;?>biz/<?php echo $BizID;?>/paymoney/">店内买单</a>
     <?php } } ?>
	</div>
 </div>
 <div class="dianpu_contact">
  <a href="http://api.map.baidu.com/marker?location=<?php echo $rsBiz["Biz_PrimaryLat"].','.$rsBiz["Biz_PrimaryLng"] ?>&title=<?php echo $rsBiz["Biz_Name"] ?>&name=<?php echo $rsBiz["Biz_Name"] ?>&content=<?php echo $rsBiz["Biz_Address"] ?>&output=html&wxref=mp.weixin.qq.com" class="location"><span></span>地址：<?php echo $rsBiz["Biz_Address"];?></a>
  <a href="tel:<?php echo $rsBiz["Biz_Phone"];?>" class="tel"><span></span>客服电话：<?php echo $rsBiz["Biz_Phone"];?></a><a href="<?php echo $shop_url;?>biz/<?php echo $BizID;?>/intro/1/" class="abc"><span></span>店铺介绍：点击进入</a><a href="<?php echo $shop_url;?>biz/<?php echo $BizID;?>/intro/2/" class="abc"><span></span>购物须知：点击进入</a>
  <?php if(count($rsCoupon_data) > 0 ){?>
	<a id="menu-direct-btn" class="youhui"><span></span>领取优惠卷</a>
  <?php }?>
 </div>
<?php
	if(count($lists_products)>0){
 ?>
<div class="b8"></div>
<div id="products">
	<h2>商品<span>(<?php echo count($lists_products);?>)</span></h2>
 <?php
 		foreach($lists_products as $v){
			$JSON=json_decode($v['Products_JSON'],true);
			$table = $v['Products_PriceX']/$v['Products_PriceY']*10;			
			$discount = floor($table);	
 ?>
 	<div class="items">
     <a href="<?php echo $shop_url;?>products/<?php echo $v["Products_ID"];?>/">
      <div class="img"><img src="<?php echo $JSON["ImgPath"][0];?>" /></div>
      <div class="pro_price"><span>已售<?php echo $v["Products_Sales"];?>件</span>￥<?php echo $v["Products_PriceX"];?>&nbsp;<i>&nbsp;￥<?php echo $v["Products_PriceY"];?>&nbsp;</i></div>
      <p><?php echo $v["Products_Name"];?></p>  
	  <div><span class="fen_x">折</span><span class="activity" style="line-height: 28px;color:#999;font-size:13px;">折扣商品<?=$discount?>折起</span></div>
     </a>
    </div>

	   
 <?php }?>
</div>
<?php }?>

<?php
if($average_num>0){
?>
<div class="b8"></div>
<div id="commits">
	<h2>评论<span>(<?php echo count($lists_products);?>)</span><p><img src="/static/images/star_<?php echo intval($average_score);?>.png" /> <?php echo $average_score;?>分</p></h2>
 <?php
 		foreach($list_commit as $v){
 ?>
 	<div class="items">
      <div class="creattime"><?php echo date("Y-m-d H:i:s",$v["CreateTime"]);?></div>
      <div class="commit_content"><?php echo $v["Note"];?></div>
    </div>
 <?php }?>
 <?php if($average_num>5){?>
 <div class="commit_more"><a href="<?php echo $shop_url;?>biz/<?php echo $rsBiz["Biz_ID"];?>/commit/">查看全部(<?php echo $average_num;?>)条评价 &gt;&gt;</a></div>
 <?php }?>
</div>
<?php
}
?>


<?php
	if(count($lists_products_relate)>0){
 ?>
<div class="b8"></div>
<div id="products">
	<h2>相关商品</h2>
 <?php
 		foreach($lists_products_relate as $v){
			$JSON=json_decode($v['Products_JSON'],true);
 ?>
 	<div class="items">
     <a href="<?php echo $shop_url;?>products/<?php echo $v["Products_ID"];?>/">
      <div class="img"><img src="<?php echo $JSON["ImgPath"][0];?>" /></div>
      <div class="pro_price"><span>已售<?php echo $v["Products_Sales"];?>件</span>￥<?php echo $v["Products_PriceX"];?>&nbsp;<i>&nbsp;￥<?php echo $v["Products_PriceY"];?>&nbsp;</i></div>
      <p><?php echo $v["Products_Name"];?></p>      
     </a>
    </div>
 <?php }?>
</div>
<?php }?>
<!-- 弹出框begin -->
<div id="option_content" style="overflow:auto">
	<div class="pro_top" style="height:35px;">
		<div style=" float:right;">
			<div id="close-btna" class="cjlose thi"></div>
		</div>
	</div>
	<div id="coupon" style="background-color:#FFF;">
		<?php 
		foreach ($rsCoupon_data as $key=>$rsCoupon){
			echo '<div class="item" style="margin:0px;">
			<h1>【'.$rsCoupon['Coupon_Subject'].'】</h1><div class="p"><img src="' . $rsCoupon['Coupon_PhotoPath'] . '" />';
			if(empty($rsCoupon['Coupon_UsedTimes'])){
				echo '';
			}
			if (!isset($_SESSION [$UsersID . 'User_ID'])) {
				echo '<a href="/api/'.$UsersID . '/user/0/login/"><div class="get" CouponID="'.$rsCoupon['Coupon_ID'].'">领取</div></a>';
			} else {
				echo '<div class="get" CouponID="'.$rsCoupon['Coupon_ID'].'">领取</div>';
			}
			echo '</div>
			<h2>'.date("Y-m-d H:i:s",$rsCoupon['Coupon_EndTime']).'过期【'.($rsCoupon['Coupon_UsedTimes']==-1?'无限':$rsCoupon['Coupon_UsedTimes']).'次使用】</h2>';
			if($rsCoupon['Coupon_UseArea']==0){
				echo '<h3>实体店可用</h3>';
			}else{
				echo '<h3>微商城'.($rsCoupon['Coupon_Condition']==0 ? '' : '(一次性购买满'.$rsCoupon['Coupon_Condition'].')').'可用</h3>';
				if($rsCoupon['Coupon_UseType']==0 && $rsCoupon['Coupon_Discount']>0 && $rsCoupon['Coupon_Discount']<1){
					echo '<h3>可享受折扣'.($rsCoupon['Coupon_Discount']*10).'折</h3>';
				}
				if($rsCoupon['Coupon_UseType']==1 && $rsCoupon['Coupon_Cash']>0){
					echo '<h3>可抵现金'.$rsCoupon['Coupon_Cash'].'元</h3>';
				}
			}
			echo '<h3>' . $rsCoupon['Coupon_Description'] . '</h3>
		</div>';
		}?>
	</div>
	<!--<div id="option_selecter">
		<div style="height:40px;">
		</div>
		<a href="javascript:void(0);" id="selector_confirm_btn">确定</a> 
	</div>-->
</div>
<!-- 弹出框end --> 
<div class="mask"></div>
<div class="b8"></div>
<?php require_once($rsBiz["Skin_ID"]."/footer.php");?>
