<div class="main_nav_bg">
		<div class="main_nav">
			<ul class="main_nav_ul">
			<?php foreach($output['main_nav'] as $key => $val){?>
				<li><a href="<?php echo $val;?>"><?php echo $key;?></a></li>
			<?php }?>
			</ul>
			<!-- 代码begin -->
			  <div class="pullDown">
				<dl>
				<dt> 所有商品分类<span><img src="/static/pc/shop/images/多边形 2.png"></span></dt>
				<?php if(!empty($output['categoryTree'])){?>
				<?php foreach($output['categoryTree']  as $key => $parentCate){
				    $imgName = "block.png";
					$dd_display = "none";
					$Select_Cat_ID = !empty($_GET['categoryID'])?$_GET['categoryID']:0;
					if($parentCate['Category_ID'] == $Select_Cat_ID){
						$imgName = "block.png";
						$dd_display = "block";
					}	
				?>
				<dd><a href="<?php echo url('list/index',array('id'=>$parentCate['Category_ID']));?>" class="nav_left"><?=$parentCate['Category_Name']?><span style="float:right">></span></a><div class="nav_right">
				<?php if(!empty($parentCate['child'])){?>
				<?php foreach($parentCate['child'] as $key => $child){?>
				<a href="<?php echo url('list/index',array('id'=>$child['Category_ID']));?>"><?=$child['Category_Name']?></a>
				<?php }} ?>
				</div></dd>
				<?php }} ?>
				</dl>
			  </div>
			<!-- 代码end -->
		</div>
	</div>