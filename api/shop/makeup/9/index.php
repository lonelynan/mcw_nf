<?php
$Dwidth = array('640','322','20','20','20','20','296','296','600','600','600','320');
$DHeight = array('315','108','20','20','20','20','240','240','200','200','200','210');

$Home_Json=json_decode($rsSkin['Home_Json'],true);

for($no=1;$no<=12;$no++){
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
if(!empty($_POST)){
	if(empty($_POST['cate_id'])){
		//获取新品
		$counts = $DB->GetRs("shop_products","count(Products_ID) as count","where Users_ID='".$UsersID."' and Products_IsNew=1 and Products_Status=1 and Products_SoldOut=0");
		$num = 10;//每页记录数
		$p = !empty($_POST['p'])?intval(trim($_POST['p'])):1;
		$total = $counts['count'];//数据记录总数
		$totalpage = ceil($total/$num);//总计页数
		$limitpage = ($p-1)*$num;//每次查询取记录
		$rsNewProducts = $DB->GetAssoc("shop_products","Products_Name,Products_ID,Products_JSON,Products_PriceX,Products_Sales","where Users_ID='".$UsersID."' and Products_IsNew=1 and Products_SoldOut=0 and Products_Status=1 order by Products_Index asc,Products_ID desc limit $limitpage,$num");
		$new_products = handle_product_list($rsNewProducts);
		foreach($new_products as $k => $item){
			$new_products[$k]['link'] = $shop_url.'products/'.$item['Products_ID'].'/';
			
		}
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
	}else{
		//根据栏目id的产品列表
		$catid = $_POST['cate_id'];
		$new_products = array();
		$counts = $DB->GetRs("shop_products","count(Products_ID) as count","where Products_SoldOut=0 and Products_Status=1 and Products_Category like '%,".$catid.",%'");
		$num = 2;//每页记录数
		if($counts['count'] <= $num){
			$show_load_btn = 0;
		}
		$p = !empty($_POST['p'])?intval(trim($_POST['p'])):1;
		$total = $counts['count'];//数据记录总数
		$totalpage = ceil($total/$num);//总计页数
		$limitpage = ($p-1)*$num;//每次查询取记录
		
		$rsNewProducts = $DB->GetAssoc("shop_products","Products_Name,Products_ID,Products_JSON,Products_PriceX,Products_Sales","where Products_SoldOut=0 and Products_Category like '%,".$catid.",%' and Products_Status=1 order by Products_Index asc,Products_ID desc limit $limitpage,$num");
		$new_products = handle_product_list($rsNewProducts);
		foreach($new_products as $k => $item){
			$new_products[$k]['link'] = $shop_url.'products/'.$item['Products_ID'].'/';
		}
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
?>
<?php require_once('skin/top.php'); ?>

<body style=" background:#f8f8f8">
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
	<div class="shop_skin_index_list logo" rel="edit-t02">
		<div class="img" ></div>
    </div>
    <div class="search_box">
        <?php require_once('skin/search_in.php'); ?>
     </div>
	<div class="shop_skin_index_list banner" rel="edit-t01">
		<div class="img" ></div>
    </div>
	  <div class="index-h">
		   <div class="items" rel="edit-t03">
			<div class="img"></div>
		   </div>
		   <div class="items" rel="edit-t04">
			<div class="img"></div>
		   </div>
		   <div class="items" rel="edit-t05">
			 <div class="img"></div>
		   </div>
		   <div class="items" rel="edit-t06">
			<div class="img"></div>
		   </div>
		   <div class="clear"></div>
	 </div>
	<div class="ind_wrap">
    	<div class="ind_one_box">
            <div class="lbar fl">
                <div class="shop_skin_index_list" rel="edit-t07"><div class="img"></div></div>
            </div>
            <div class="rbar fr">
                <div class="shop_skin_index_list" rel="edit-t08"><div class="img"></div></div>
            </div>
            <div class="clear"></div>
   		</div>
        <div class="ad_items"><div class="shop_skin_index_list" rel="edit-t09"><div class="img"></div></div></div>
        <div class="ad_items"><div class="shop_skin_index_list" rel="edit-t010"><div class="img"></div></div></div>
        <div class="ad_items"><div class="shop_skin_index_list" rel="edit-t011"><div class="img"></div></div></div>
    </div>
	<div class="shop_skin_index_list h2" rel="edit-t012">
      <div class="text"  style=" color:#e30101;"></div>
    </div>
 </div>
 <div class="index_products">
	  <div class="list-0"></div> 
	  <ul id="more" style="overflow:hidden;"></ul>
	  <div class="pullUp get_more" cate_id="" page="1"> <span class="pullUpIcon"></span><span class="pullUpLabel">MORE+</span> </div>
 </div>
</div>
<?php require_once('skin/distribute_footer.php'); ?>
<?php include("yh_index_ajax.php");?>
</body>
</html>