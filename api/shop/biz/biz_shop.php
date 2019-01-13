<?php
//include('../global.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/shop/setting.class.php');

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

$base_url = base_url();
$shop_url = shop_url();
$setting = new setting($DB,$UsersID);
$rsConfig=$setting->get_one();
//页面内容
/* $rsSkin=$setting->get_home($rsConfig['Skin_ID']);
$Home_Json=json_decode($rsSkin['Home_Json'],true);
$json=$setting->set_homejson_array($Home_Json);

$rsCategory = $DB->get("shop_category","Category_Name,Category_ID,Category_Img","where Users_ID='".$UsersID."' and Category_IndexShow=1 and Category_ParentID=0 order by Category_Index asc ");
$category_list = $DB->toArray($rsCategory);
$cate_id = array();
foreach($category_list as $v){
	$cate_id[] = $v['Category_ID'];
}
$cat_list = implode($cate_id,','); */

/* $rsCategory = $DB->get("shop_category","Category_ParentID,Category_Name,Category_ID,Category_Img","where Users_ID='".$UsersID."' and Category_IndexShow=1 and Category_ParentID in (".$cat_list.")   order by Category_Index asc ");
$resl = $DB->toArray();
$result = array();
foreach($resl as $key=>$val){
	$result[$val['Category_ParentID']][] = $val['Category_ID'];
}

$ANNOUNCE = $rsConfig["ShopAnnounce"]; */

if(isset($_POST['kw'])){
	$rsNewBiz = $DB->get("biz","Users_ID,Biz_ID,Biz_Name,Biz_Account,Biz_Logo,Biz_Address,Biz_Contact,Biz_Phone","where Is_Union=1 and Users_ID='".$UsersID."' and Biz_Name like '%".$_POST['kw']."%' order by Biz_CreateTime");
	$result = $DB->toArray();
}else{
	$rsNewBiz = $DB->get("biz","Biz_Name,Users_ID,Biz_ID,Biz_Account,Biz_Logo,Biz_Address,Biz_Contact,Biz_Phone","where  Is_Union=1 and Users_ID='".$UsersID."' and Biz_ID >= (((select max(Biz_ID) from biz)-(select min(Biz_ID) from biz))*rand()) order by Biz_CreateTime limit 0,8");
	$result = $DB->toArray();
}
require_once('../skin/top.php');
?>
<body>

<div id="shop_page_contents">
 <div id="cover_layer"></div>
 
 <link href='/static/api/shop/skin/<?php echo $rsConfig['Skin_ID'];?>/page.css' rel='stylesheet' type='text/css' />
 <link href='/static/api/shop/skin/<?php echo $rsConfig['Skin_ID'];?>/page_media.css' rel='stylesheet' type='text/css' />

 <link href='/static/js/plugin/flexslider/flexslider.css' rel='stylesheet' type='text/css' />
 <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
 <script type='text/javascript' src='/static/js/plugin/flexslider/flexslider.js'></script>
 <script type='text/javascript' src='/static/api/shop/js/index.js'></script>

 <div id="shop_skin_index">
  <div class="header">
   <div class="search">
		<form action="" method="post">
			<input type="text" name="kw" class="input" value="<?=isset($_POST['kw'])?$_POST['kw']:'';?>" placeholder="输入商品名称..." />
			<input style='border:0;' type="submit" class="submit" value="" />
		</form>
   </div>
  </div>        
 </div>
<div style='line-height:50px;text-align:center;height:50px;width:50px;position:fixed;right:5px;bottom:55px;cursor:pointer;background-color:#A66;border-radius:50%;' onclick='window.location.href=""'>换一批</div>
<?php foreach($result as $t):?>
 <div class="index_products">
	
		<div style='border-bottom:5px solid #DDD;overflow:hidden;'>
			<div style='float:left;margin-top:15px;margin-left:5px;'>
			<img class="product-image" width="80" height='80' src="<?=$t['Biz_Logo']?>"/>
			</div>
			<div style='float:left;overflow:hidden;width:50%;margin-top:15px;margin-left:5px;'>
				<ul style='height:80px;list-style:none;overflow:hidden;'>
					<li style='margin-top:3px;white-space:nowrap;overflow:hidden;'>店铺：<?php echo $t["Biz_Name"]?></li>
					<li style='margin-top:3px;white-space:nowrap;overflow:hidden;'>地址：<?php echo $t["Biz_Address"]?></li>
					<li style='margin-top:3px;white-space:nowrap;overflow:hidden;'><a style='color:blue;' href='tel:<?php echo $t["Biz_Phone"]?>'>电话：<?php echo $t["Biz_Phone"]?></a></li>
				</ul>
			</div>
			<div style='border-radius:5px;float:right;background-color:#ED145B;margin-right:5px;margin-top:35px;'>
				<a style='cursor:pointer;text-decoration:none;color:#FFF;padding:10px;' href="<?php echo $shop_url;?>biz/<?php echo $t["Biz_ID"];?>/">进入</a>
			</div>
		</div>
		

   <?php endforeach;?>
   <div class="clear"></div> 
 </div>
</div>


<link href='/static/api/shop/skin/default/style.css' rel='stylesheet' type='text/css' />

<div id="footer_points"></div>
<footer id="footer">  
  <ul class="list-group" style='margin:0px;padding:0px;list-style:none;' id="footer-nav">
    	<li class="home"><a href="<?=$shop_url?>">首页</a></li>
		<li class="cate1"><a href="<?=$shop_url.'allcategory/'?>">全部分类</a></li>
		<li class="cart"><a href="/api/<?=$UsersID?>/shop/cart/">购物车</a></li>
		<li class="user"><a href="<?=$shop_url.'member/'?>">个人中心</a></li>
  </ul>
</footer>

<div class='conver_favourite'><img src="/static/api/images/global/share/favourite.png" /></div>
</body>
</html>