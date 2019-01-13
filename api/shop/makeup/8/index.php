<?php
$Dwidth = array('640','100','100','100','100');
$DHeight = array('314','139','139','139','139');
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
?>
<?php require_once('skin/top.php'); ?>

<body>
<?php
ad($UsersID, 1, 1);
?>
<div id="shop_page_contents">
 <div id="cover_layer"></div>
 <link href='/static/api/shop/skin/<?php echo $rsConfig['Skin_ID'];?>/page.css' rel='stylesheet' type='text/css' />
 <link href='/static/api/shop/skin/<?php echo $rsConfig['Skin_ID'];?>/page_media.css' rel='stylesheet' type='text/css' />
 <link href='/static/js/plugin/flexslider/flexslider.css' rel='stylesheet' type='text/css' />
 <script type='text/javascript' src='/static/js/plugin/flexslider/flexslider.js'></script>
 <script type='text/javascript' src='/static/api/shop/js/index.js'></script>
 <script language="javascript">
  var shop_skin_data=<?php echo json_encode($json) ?>;
  $(document).ready(index_obj.index_init);
 </script>
 <div id="shop_skin_index">
	<div class="shop_skin_index_list banner" rel="edit-t01">
		<div class="img" ></div>
    </div>
	 <div class="index-h">
		<div class="shop_skin_index_list" rel="edit-t02">
			<div class="img"></div>
			<div class="text"></div>
		</div>
		
		<div class="shop_skin_index_list" rel="edit-t03">
			<div class="img"></div>
			<div class="text"></div>
		</div>
		<div class="shop_skin_index_list" rel="edit-t04">
			<div class="img"></div>
			<div class="text"></div>
		</div>
		<div class="shop_skin_index_list" rel="edit-t05">
			<div class="img"></div>
			<div class="text"></div>
		</div>
		<div class="clear"></div>
	</div>
	    <?php
			$i=0;
			$DB->get("shop_category","Category_Name,Category_ID","where Users_ID='".$UsersID."' and Category_ParentID=0 order by Category_Index asc");
			$bg_array = array('bg_blue','bg_f8ca5a','bg_ee7884');
			$Category_List = array();
			while($r=$DB->fetch_assoc()){
				$Category_List[] = $r;
			}
			foreach($Category_List as $rscategory){
				$i = $i>=2 ? 0 : $i;
				$bg = $bg_array[$i];
				echo '<div class="products_cont"><div class="title '.$bg.'"><span class="fenlei_x">'.$rscategory["Category_Name"].'</span><a href="'.$shop_url.'category/'.$rscategory["Category_ID"].'/" class="more">></a></div></div><ul class="products_list">';
				$i++;
				
				$condition = "where Users_ID='".$UsersID."' and Products_SoldOut=0 and Products_Status=1";
				
				$condition .= " and Products_Category like '%,".$rscategory["Category_ID"].",%' order by Products_Index asc,Products_ID desc";
				
				$DB->get("shop_products","*",$condition,3);
				$j=0;
				while($item=$DB->fetch_assoc()){
					$JSON=json_decode($item['Products_JSON'],true);
					if($j==0){
						echo '<li class="big right_bor"><a href="'.$shop_url.'products/'.$item["Products_ID"].'/"><div class="name"><span class="price">￥'.$item["Products_PriceX"].'</span><br />'.$item["Products_Name"].'</div><div class="pic">'.(empty($JSON["ImgPath"])?'暂无图片':'<img data-url="'.$JSON["ImgPath"][0].'" src="/static/js/plugin/lazyload/grey.gif" />').'</div></a></li>';
					}elseif($j==1){
						echo '<li class="small btm_bor"><a href="'.$shop_url.'products/'.$item["Products_ID"].'/"><div class="name"><span class="price">￥'.$item["Products_PriceX"].'</span><br />'.$item["Products_Name"].'</div><div class="pic">'.(empty($JSON["ImgPath"])?'暂无图片':'<img data-url="'.$JSON["ImgPath"][0].'" src="/static/js/plugin/lazyload/grey.gif" />').'</div></a></li>';
					}else{
						echo '<li class="small"><a href="'.$shop_url.'products/'.$item["Products_ID"].'/"><div class="name"><span class="price">￥'.$item["Products_PriceX"].'</span><br />'.$item["Products_Name"].'</div><div class="pic">'.(empty($JSON["ImgPath"])?'暂无图片':'<img data-url="'.$JSON["ImgPath"][0].'" src="/static/js/plugin/lazyload/grey.gif" />').'</div></a></li>';
					}
					$j++;
				}
				echo '<div class="clear"></div></ul>';
			}
		?>
 </div>
</div>
<?php require_once('skin/distribute_footer.php'); ?>
<!--懒加载--> 
<script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script> 
<script language="javascript">
	$("img").scrollLoading();
</script>
</body>
</html>