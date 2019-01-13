<?php include('top.php'); ?>
<style>
	.actionsun{display:none;}
</style>
<body style="background-color:#f8f8f8;">
    <link href="/static/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" href="/static/css/font-awesome.css">
	<link href='/static/api/shop/skin/default/css/cate.css' rel='stylesheet' type='text/css' />
	<link href='/static/api/shop/skin/default/css/navigation.css' rel='stylesheet' type='text/css' />
    <div class="header_search">
     <div class="search_form">
      <?php require_once('search_in.php'); ?>
     </div>
    </div>
    <div class="wraps">
    	<div class="navigation">
    		<ul>
    		<?php foreach($CategoryList  as $key=>$parentCate):?>
            <?php
				$imgName = "block.png";
				$dd_display = "none";
				
				if($parentCate['Category_ID'] == $Select_Cat_ID){
					$imgName = "block.png";
					$dd_display = "block";
				}
				//var_dump($parentCate);exit;
			?>
    			<li><a href="#"  rel="<?=$key?>"><?=$parentCate['Category_Name']?></a></li>
    		<!-- 	<li><a href="#"  rel="2">水果</a></li>
    			<li><a href="#"  rel="3">肉类</a></li>
    			<li><a href="#"  rel="4">预字贫</a></li>
    			<li><a href="#"  rel="5">预字贫</a></li> -->

    		<?php endforeach;?>
    		</ul>
    	</div>
    	<?php foreach($CategoryList  as $key=>$parentCate):?>    		
	    	<div class="two_navigation two_navigation<?=$key?> actionsun">
	    		<!-- <div width="100%">
	    			<img src="/static/member/images/shop/558d2fbaNb3c2f349.jpg" style="width:100%;height:auto"/>
	    		</div> -->
	    		<h4><a  href="<?=$shop_url?>category/<?=$parentCate['Category_ID']?>/"><?=$parentCate['Category_Name']?></a></h4>
				<?php if(isset($parentCate['child'])):?>
	    		<div class="two_product">
	    			<ul>
	    			 	<?php foreach($parentCate['child'] as $key=>$child):?>
	    				<li>
	    					<a href="<?=$shop_url?>category/<?=$child['Category_ID']?>/">
	    						<img src="<?=$child['Category_Img']?>" />
	    						<span><?=$child['Category_Name']?></span>
	    					</a>
	    				</li>
	    				<?php endforeach;?>
	    				<!-- <li>
	    					<img src="/static/member/images/shop/5706abf8N7d477311.jpg" />
	    					<span></sapn>
	    				</li>
	    				<li>
	    					<img src="/static/member/images/shop/5706ac08Ndad39749.jpg" />
	    					<span></sapn>
	    				</li> -->
	    			</ul>
	    		</div>
				<?php endif?>
	    	</div>	    	
    	<?php endforeach;?>
    </div>
<script type="text/javascript">
$(document).ready(function(){
	$(".two_navigation").eq(0).removeClass("actionsun");
	$(".navigation ul li").eq(0).children().attr('id',"action");
	$(".navigation a").click(function(){
		var nanum = $(this).attr('rel');
		var two_navigation = ".two_navigation"+nanum;
		$(".two_navigation").css("display","none");
		$(".navigation a").removeAttr('id');
		$(two_navigation).css("display","block");
		$(this).attr('id',"action");
	})
});

</script>


<!--页脚导航 begin-->
<?php
require_once("distribute_footer.php");
?>
<!--页脚导航 end-->


