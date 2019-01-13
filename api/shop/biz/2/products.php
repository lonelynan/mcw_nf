<?php require_once($rsBiz["Skin_ID"].'/top.php');?>
<div class="list_sorts">
<?php require_once($rsBiz['Skin_ID']."/order_filter.php");?>
</div>
 <div id="products">
  <?php
  	  $i=0;
      $DB->getPage("shop_products","*",$condition,$pageSize=20);
	  while($v=$DB->fetch_assoc()){
		  $i++;
		  $JSON=json_decode($v['Products_JSON'],true);
  ?>
  <div class="items"<?php if($i==1){?> style="border-top:none"<?php }?>>
     <a href="<?php echo $shop_url;?>products/<?php echo $v["Products_ID"];?>/">
      <div class="img"><img src="<?php echo $JSON["ImgPath"][0];?>" /></div>
      <div class="pro_price"><span>已售<?php echo $v["Products_Sales"];?>件</span>￥<?php echo $v["Products_PriceX"];?>&nbsp;<i>&nbsp;￥<?php echo $v["Products_PriceY"];?>&nbsp;</i></div>
      <p><?php echo $v["Products_Name"];?></p>      
     </a>
    </div>
  <?php }?>
 </div>
 <?php $DB->showWechatPage($page_url); ?>
 <div class="b8"></div>
<?php require_once($rsBiz["Skin_ID"].'/footer.php');?>