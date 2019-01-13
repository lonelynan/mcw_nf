<!--
 <div id="footer_points"></div>
 <footer id="footer">
  <ul>
   <li class="first"><a href="<?php echo $shop_url;?>biz/<?php echo $BizID;?>/">店铺首页</a></li>
   <li><a href="<?php echo $shop_url;?>biz/<?php echo $BizID;?>/allcate/">全部分类</a></li>
   <li><a href="<?php echo $shop_url;?>biz/<?php echo $BizID;?>/products/0/">全部产品</a></li>
   <li><a href="<?php echo $shop_url;?>biz/<?php echo $BizID;?>/intro/">店铺简介</a></li>
  </ul>
 </footer>
 -->
 
 <?php if(!empty($share_config)){ 
if (!isset($share_config["img_url"])) {
if (isset($share_config["img"])) {
	$share_config["img_url"] = $share_config["img"];
}
}
?>

	<script language="javascript">
		var share_config = {
		   appId:"<?php echo $share_config["appId"];?>",   
		   timestamp:<?php echo $share_config["timestamp"];?>,
		   nonceStr:"<?php echo $share_config["noncestr"];?>",
		   url:"<?php echo $share_config["url"];?>",
		   signature:"<?php echo $share_config["signature"];?>",
		   title:"<?php echo isset($share_config["title"]) ? $share_config['title'] : '';?>",
		   desc:"<?php echo str_replace(array("\r\n", "\r", "\n"), "", isset($share_config["desc"]) ? $share_config["desc"] : '');?>",
		   img_url:"<?php echo isset($share_config["img_url"]) ? $share_config["img_url"] : '';?>",
		   link:"<?php echo isset($share_config["link"]) ? $share_config["link"] : '';?>"
		};
		
		$(document).ready(global_obj.share_init_config);
	</script>
<?php }?>

<script type="text/javascript">
$('#header_biz .back').click(function(){
	history.go(-1);
});
$('#header_biz .more').click(function(){
	var display = $('#header_biz ul').css('display');
	if(display=='none'){
		$('#header_biz ul').css('display','block');
	}else{
		$('#header_biz ul').css('display','none');
	}
});
</script>
<?php if($rsBiz["Biz_Kfcode"]){
	echo htmlspecialchars_decode($rsBiz["Biz_Kfcode"],ENT_QUOTES);
}?>
</body>
</html>