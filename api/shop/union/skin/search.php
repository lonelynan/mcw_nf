<?php require_once('top.php');?>
<body>
<script type="text/javascript" src="/static/api/shop/union/bizlist.js"></script> 
<link href='/static/api/shop/union/search.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type="text/javascript" src="/static/js/plugin/cookies/jquery.cookie.js"></script> 
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $ak_baidu;?>"></script> 
<script language="javascript">
var search_ajax_url = '<?php echo $base_url;?>api/<?php echo $UsersID;?>/shop/union/search_ajax/';
var app_url = '<?php echo $shop_url;?>';
var baidu_map_ak='<?php echo $ak_baidu;?>';
</script> 
<div id="loading"> <img src="/static/api/shop/union/loading.gif" /> </div>
<?php if($flag_result==1){?>
<div id="shop_page_contents">
	<div id="list_title"><span id="my_address"></span>商家列表</div>
	<div id="bizlist"> </div>
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
<?php } else {?>
<form id="search_form" method="post" action="<?php echo $shop_url;?>search/">
	<div class="search_header_title"> <span class="goback"></span><input type="text" name="title" placeholder="商家名称" /></div>
	<div class="search_body">
		<div class="search_body_title">
			<ul>
				<li ret="region" class="cur">区域</li>
				<li ret="categoryid">分类</li>
				<li ret="price">价格</li>
				<li ret="orderby">排序</li>
			</ul>
		</div>
		<div class="search_body_content" id="region_filter_list" style="display:block"></div>
		<div class="search_body_content" id="categoryid_filter_list" style="display:none"></div>
		<div class="search_body_content" id="price_filter_list" style="display:none">
			<ul filter="price">
				<li ret="0"<?php echo empty($param["price"]) ? ' class="cur"' : ''?>>不限</li>
				<?php 
					$range_list = json_decode($rsConfig['PriceRange'], true);
					// echo '<pre>';
					// print_R($range_list);
					// exit;
					if(!empty($range_list)){
						//构造数组
						foreach($range_list as $key => $val){
							$range_list[$key]['text'] = '￥'.$val['start'].'~￥'.$range_list[$key]['end'];
							if(empty($val['start'])){
								$range_list[$key]['start'] = 0;
								$range_list[$key]['text'] = '￥'.$range_list[$key]['end'].'元以下';
							}
							if(empty($val['end'])){
								$range_list[$key]['end'] = 0;
								$range_list[$key]['text'] = '￥'.$range_list[$key]['start'].'元以上';
							}
						}
						foreach($range_list as $k => $v){
				?>
				<li ret="<?php echo $v['start'];?>-<?php echo $v['end'];?>"<?php echo $param["price"]==($v['start'].'-'.$v['end']) ? ' class="cur"' : ''?>><?php echo $v['text'];?></li>
						<?php }?>
					<?php }?>
			</ul>
		</div>
		<div class="search_body_content" id="orderby_filter_list" style="display:none">
			<ul filter="orderby">
				<li ret="1"<?php echo $param["orderby"]=='1' ? ' class="cur"' : ''?>>默认</li>
				<li ret="2"<?php echo $param["orderby"]=='2' ? ' class="cur"' : ''?>>价格升序</li>
				<li ret="3"<?php echo $param["orderby"]=='3' ? ' class="cur"' : ''?>>价格降序</li>
			</ul>
		</div>
	</div>
	<div class="search_submit_point"></div>
	<div class="search_submit">
		<input type="submit" value="确定" />
	</div>
	<input type="hidden" name="price" id="input_price" value="<?php echo $param["price"];?>" />
	<input type="hidden" name="regionid" id="input_region" value="<?php echo $param["regionid"];?>" />
	<input type="hidden" name="categoryid" id="input_categoryid" value="<?php echo ','.$param["categoryid"].',';?>" />
	<input type="hidden" name="orderby" id="input_orderby" value="<?php echo $param["orderby"];?>" />
</form>
<?php }?>
<script type="application/javascript">
var ispost = <?php echo $flag_result;?>;
var search_params = {
	UsersID:"<?php echo $UsersID;?>",
	action:"biz_list",
	page:<?php echo $param["page"];?>,
	pagenum:1,
	title:"<?php echo $param["title"];?>",
	price:"<?php echo $param["price"];?>",
	regionid:"<?php echo $param["regionid"];?>",
	categoryid:"<?php echo $param["categoryid"];?>",
	orderby:"<?php echo $param["orderby"];?>",
};
$(document).ready(index_obj.search_init);
</script>
<?php require_once('../skin/distribute_footer.php'); ?>
</body>
</html>