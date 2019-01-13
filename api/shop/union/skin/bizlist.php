<?php require_once('top.php');?>

<body>
<link href='/static/api/shop/union/style.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/union/biz.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type="text/javascript" src="/static/api/shop/union/bizlist.js?t=<?=time()?>"></script> 
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $ak_baidu;?>"></script> 
<script language="javascript">
var app_ajax_url = '<?php echo $base_url;?>api/<?php echo $UsersID;?>/shop/union/ajax/';
var app_url = '<?php echo $shop_url;?>';
var baidu_map_ak='<?php echo $ak_baidu;?>';
var categoryID = <?php echo $CategoryID;?>;
var newlat = '<?php echo (isset($_SESSION['firstlat']) && !empty($_SESSION['firstlat'])) ? $_SESSION['firstlat'] : '';?>';
var newlng = '<?php echo (isset($_SESSION['firstlng']) && !empty($_SESSION['firstlng'])) ? $_SESSION['firstlng'] : '';?>';
</script>
<?php if($flag_result==1){?>
<div id="shop_page_contents">
	<div id="list_title"><span id="my_address"></span>商家列表</div>
	<ul id="search_type">
		<form style="visibility:hidden" method="post" action="" id="search_form">
			<input type="hidden" value="<?php echo empty($param["categoryid"]) ? '' : $param["categoryid"];?>" name="categoryid" id="search_categoryid">
			<input type="hidden" value="<?php echo empty($param["price"]) ? '' : $param["price"];?>" name="price" id="search_price">
			<input type="hidden" value="<?php echo empty($param["orderby"]) ? '' : $param["orderby"];?>" name="orderby" id="search_orderby">
			<input type="hidden" value="<?php echo empty($param["regionid"]) ? '' : $param["regionid"];?>" name="regionid" id="search_regionid">
			<input type="hidden" value="<?php echo empty($param["page"]) ? 1 : $param["page"];?>" name="page" id="page_cur">
			<input type="hidden" value="1" name="pagenum" id="pagenum">
			<input type="hidden" value="biz_list" name="action" id="action">
			<input type="hidden" value="<?php echo $UsersID;?>" name="UsersID" id="usersid">
		</form>
		<li class="search_type_li" action="get_category"><a href="javascript:;">
		<?php echo !empty($rsCategory["Category_Name"])?$rsCategory["Category_Name"]:'全部分类';?></a></li>
		<li style="border-left:solid 1px #bfbfbf;" action="get_price" class="search_type_li"><a href="javascript:;">价格</a></li>
		<li style="border-left:solid 1px #bfbfbf;" action="get_orderby" class="search_type_li"><a href="javascript:;">排序</a></li>
		<?php 
		    if(empty($rsConfig['DefaultAreaID'])){
				$DefaultAreaID = 240;
			}else{
				$DefaultAreaID = $rsConfig['DefaultAreaID'];
			}
			$rsDefaultArea = $DB->GetRs("area","area_id,area_name,area_code"," where area_id=".$DefaultAreaID);
			if(isset($_COOKIE['city'])){
				$cityname_tmp = explode('|', $_COOKIE['city']);
				$cityname = $cityname_tmp[1];
			}else{
				$cityname = $rsDefaultArea['area_name'];
			}
		?>
		<li style="border-left:solid 1px #bfbfbf;" action="get_region" class="search_type_li"><a class="header_city" href="javascript:;"><?php echo $cityname;?></a></li>
	</ul>
	<div id="bizlist"></div>
	<div id="list_pages">
		<ul>
			<li id="pre"><span>上一页</span></li>
			<li class="cur"><?php echo $param["page"];?></li>
			<li id="next"><span>下一页</span></li>
			<div class="clear"></div>
		</ul>
	</div>
</div>
<div id="cover_layer_search"></div>
<div id="loading"> <img src="/static/api/shop/union/loading.gif" /> </div>
<?php }?>
<script type="application/javascript">
var ispost = <?php echo $flag_result;?>;
$(document).ready(index_obj.index_init);
</script>
<div id="cover_layer_common"></div>
<div id="index_filter"></div>
<?php require_once('../skin/distribute_footer.php'); ?>
</body>
</html>