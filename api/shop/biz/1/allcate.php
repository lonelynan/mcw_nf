<?php require_once($rsBiz["Skin_ID"]."/top.php");?>
 <div id="mulu">
    <?php
		$f_cate = array();
		$DB->Get("biz_category","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID." and Category_ParentID=0 order by Category_Index asc, Category_ID asc");
		while($r=$DB->fetch_assoc()){
			$f_cate[] = $r;
		}
		foreach($f_cate as $f){
	?>
     <dl>
      <dt><a href="<?php echo $shop_url;?>biz/<?php echo $BizID;?>/products/<?php echo $f["Category_ID"];?>/"><?php echo $f["Category_Name"];?></a></dt>
      <dd>
      <?php
	  	 $i=0;
         $DB->Get("biz_category","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID." and Category_ParentID=".$f["Category_ID"]." order by Category_Index asc, Category_ID asc");
		 while($v=$DB->fetch_assoc()){
			 $i++;
	  ?>
       	<a href="<?php echo $shop_url;?>biz/<?php echo $BizID;?>/products/<?php echo $v["Category_ID"];?>/" class="<?php echo $i%2==1 ? 'mulu_left' : ''?>"><?php echo $v["Category_Name"];?></a>
      <?php }?>
      <div class="clear"></div>
      </dd>
     </dl>
    <?php }?>
 </div>
<?php require_once($rsBiz["Skin_ID"]."/footer.php");?>