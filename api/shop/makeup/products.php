<?php


/*edit in 20160318*/
include_once('top.php'); 

?>

<?php
if (! isAjax()) {
?>
<body >

<script type='text/javascript' src='/static/js/iscroll.js'></script> 
<script type='text/javascript' src='/static/api/shop/js/product_attr_helper.js?t=<?=time();?>'></script>
<link href="/static/css/bootstrap.css" rel="stylesheet" />
<link rel="stylesheet" href="/static/css/font-awesome.css" />
<link href="/static/api/shop/skin/default/css/tao_detail.css?t=<?php echo time();?>" rel="stylesheet" type="text/css" />


<script src="/static/js/jquery.idTabs.min.js"></script> 
<!-- 产品图片所需插件 begin -->
<link href="/static/js/plugin/photoswipe/photoswipe.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/static/js/plugin/touchslider/touchslider.min.js"></script> 
<script type='text/javascript' src='/static/js/plugin/photoswipe/klass.min.js'></script> 
<script type='text/javascript' src='/static/js/plugin/photoswipe/photoswipe.jquery-3.0.5.min.js'></script> 
<script type='text/javascript' src='/static/api/shop/js/toast.js?t=<?=time();?>'></script> 
<!-- 产品图片所需插件 end --> 

<?php
}
?>
<script language="javascript">
	var products_attr_json = '<?=$products_attr_json?>';
	var base_url = '<?=$base_url?>';
	var UsersID = '<?=$UsersID?>';
    var Products_ID = '<?=$rsProducts['Products_ID']?>';
	var proimg_count = 1;
	var is_virtual = <?php echo $rsProducts["Products_IsVirtual"]?>;
	$(document).ready(function() {
		shop_obj.tao_detail_init();
		$("#content-filter").idTabs();
	});
</script>
<style>
	.product_comments{
		width: 100%;
	}
	.product_comments span{
		width: 90%;
		margin:0 auto;
		display:block;
		min-height: 25px;
		padding-top:10px;
	}
	.bar{position:fixed}
</style>
<header class="bar bar-nav"> <a 
<?php
if (! isAjax()) {

?>
href="javascript:history.go(-1)" 
<?php
} else {
?>
href="javascript:global_obj.productDetail_mask_close()"
<?php
}
?>
class="pull-left"><img src="/static/api/shop/skin/default/images/black_arrow_left.png" /></a>
	<h1 class="title" id="page_title">产品详情</h1>
</header>
<div id="overlay"></div>
<div id="wrap" style="margin-top:40px"> 
	<!-- 产品图片开始 -->
	<div id="detail_images">
		<div class="pro_img">
			<div class="touchslider">
				<div class="img">
					<div class="touchslider-viewport">
						<div class="list">
							<?php if(isset($JSON["ImgPath"])){
									foreach($JSON["ImgPath"] as $key=>$value){
										echo '<div class="touchslider-item"><a href="'.$value.'" rel="'.$value.'"><img src="'.$value.'" style="max-height:350px;" /></a></div>';
									}
								}?>
						</div>
					</div>
				</div>
				<div class="touchslider-nav">
					<?php
							if(isset($JSON["ImgPath"])){
							foreach($JSON["ImgPath"] as $key=>$value){
								echo $key==0 ? '<a class="touchslider-nav-item touchslider-nav-item-current"></a>' : '<a class="touchslider-nav-item"></a>';
							}
						}?>
				</div>
			</div>
		</div>
	</div>
	<!-- 产品图片结束 -->
	<div class="conbox">
		<div id="product_brief_info">
			<div id="name_and_share"> <span id="product_name">
				<?=$rsProducts["Products_Name"]?>
				</span> <span id="shu_xian"></span> <a class="detail_share" id="share_product" productid="<?=$ProductsID?>" is_distribute="<?=$Is_Distribute?>">分销此商品</a>
				<div class="clearfix"></div>
				<span id="product_price">&yen;
				<?=(($min_Attr_Price==0 && $max_Attr_Price==0) || ($min_Attr_Price == $max_Attr_Price) )?$rsProducts["Products_PriceX"] + $min_Attr_Price : ($rsProducts['Products_PriceX']+$min_Attr_Price).'-'.($rsProducts['Products_PriceX']+$max_Attr_Price)?>
				元</span>&nbsp;&nbsp;&nbsp;&nbsp; <del id="product_origin_price">&yen;
				<?=$rsProducts["Products_PriceY"]?>
				元</del> </div>
			<p id="other_info">
				<?php if($rsProducts["Products_IsShippingFree"] == 1){?>
				<span>快递免运费</span>
				<?php }?>
				<span>销量
				<?=$rsProducts["Products_Sales"]?>
				件</span> <span>
				<?=$comment_aggregate['points']?>
				分</span> </p>
		</div>
	</div>
</div>

<div class="conbox">
  <div id="activity_info">
    <?php if(!empty($man_list)):?>
    <p> <img src="/static/api/shop/skin/default/gift.jpg" />
      <?php foreach($man_list as $key=>$item):?>
      满<span class="juice">
      <?=$item['reach'];?>
      </span>减<span class="juice">
      <?=$item['award'];?>
      </span>
      <?php endforeach;?>
    </p>
    <?php endif; ?>
  </div>
</div>

<!-- 会员专享价begin
<?php if(count($discount_list) > 0):?>
<div class="b5"></div>
<div class="detail_panel_title">
    <div class="p_info"><a href="javascript:void(0)">会员专享价</a></div>
</div>
<div class="b5"></div>
<div class="detail_panel_content">
  <ul class="list-group" id="user_level_price">
    <?php foreach($discount_list as $key=>$item):?>
    <?php if($item['cur'] == 1):?>
    <li class="list-group-item red">
      <?=$item['Name']?>价&nbsp;&nbsp;<strong>&yen;
      <?=$item['price']?>
      </strong></li>
    <?php else: ?>
    <li class="list-group-item">
      <?=$item['Name']?>价&nbsp;&nbsp;<strong class="red">&yen;
      <?=$item['price']?>
      </strong></li>
    <?php endif;?>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>
 会员专享价end -->
<?php 
	$distribute_data = array();
	$DB->Get('distribute_level','*','where Users_ID = "'.$UsersID.'"');
	while($r = $DB->fetch_assoc()){
		$distribute_data[] = $r;
	}
	$Products_Discount = json_decode($rsProducts['Products_Discount'],true);
	foreach ($distribute_data as $key=>$value) {
		if(isset($_SESSION['Dis_Level_ID']) && $value['Level_ID'] == $_SESSION['Dis_Level_ID']){
			$Products_PriceX = $Products_Discount[$key]*0.01*$rsProducts['Products_PriceX'];
			$Level_Name = $value['Level_Name'];
		}
		
	}
	// echo '<pre>';
	// print_R($rsProducts['Products_Discount']);
	// exit;
?>
<!-- 分销商专享价begin -->
<?php if(isset($_SESSION['Dis_Level_ID'])){?>
<div class="b5"></div>
<div class="detail_panel_title">
    <div class="p_info"><a href="javascript:void(0)">分销商专享价</a></div>
</div>
<div class="b5"></div>
<div class="detail_panel_content">
  <ul class="list-group" id="user_level_price">
    <li class="list-group-item red">
   <?=$Level_Name?>价&nbsp;&nbsp;&nbsp;&nbsp;<strong>&yen;
      <?=$Products_PriceX?>
      </strong></li>
  </ul>
</div>
<?php }?>
<!-- 分销商专享价end -->


<div id="detail">
	<div class="b5"></div>
	<div class="commit">
		<a href="<?=$base_url?>api/<?=$UsersID?>/shop/commit/<?=$ProductsID?>/">
			<span>&nbsp;</span>商品评价
			<!--<label class="juice">(共<?=$comment_aggregate['num']?>条)</label>
			<label class="pull-right juice"><?=$comment_aggregate['points']?>分</label>-->
			<label class="juice"><?=$comment_aggregate['points']?>分</label>
			<label class="pull-right juice"><?=$comment_aggregate['num']?> 人评价</label>
		</a>
	</div>
	 <?php foreach($commit_list as $vv){?>
		<div class="product_comments">
		<span><?=$vv['Note']?></span>
		<p style="text-align:right;padding-right: 10px; padding-bottom: 5px;"><?= date("Y-m-d H:m:s",$vv['CreateTime'])?></p>
		</div>
		<hr  style="margin:0px;" />
	<?php } ?>
	<div><a href="<?=$base_url?>api/<?=$UsersID?>/shop/commit/<?=$ProductsID?>/" style="width:100%;height:30px;line-height:30px; display:block; text-align:center;color:#EB6001;">查看更多>></a></div>
</div>

<div class="b5"></div>
<!-- 产品详情start -->
<div id="content-filter" class="center-block">
	<div style="width:100%;height:40px;overflow:hidden;border-top:solid 1px #dcdcdc;border-bottom:solid 1px #eeeeee"> <a href="#description" class="item selected">产品详情</a><span class="shu">|</span><a href="#panel2" class="item">产品参数</a> </div>
	<div id="table-panel">
		<div id="description">
			<div class="contents">
				<?=$rsProducts["Products_Description"]?>
			</div>
		</div>
	</div>
	<div id="panel2"> <br/>
	<!--/*edit in 20160318*/-->
	<table class="Ptable" border="1" cellpadding="0" cellspacing="1" width="100%">
      <tbody>
        
        <tr>
          <th class="tdTitle" colspan="2">产品参数</th>
        </tr>
        <tr></tr>
        <?php if(!empty($parameter_list)){foreach($parameter_list as $ks=>$vs): ?>
        <tr>
          <td class="tdTitle"><?=$vs['name']?></td>
          <td><?=$vs['value']?></td>
        </tr>
        <?php endforeach; }?>
        
      </tbody>
    </table>
	</div>
	<a href="#" class="clearfix"></a> </div>
<!-- 产品详情end -->
<div class="b5"></div>
<!-- 属性选择内容begin -->
<div id="option_content">
	<div id="option_selecter">
		<form name="addtocart_form" action="/api/<?php echo $UsersID ?>/shop/cart/" method="post" id="addtocart_form">
			<input type="hidden" name="OwnerID" value="<?=$owner['id']?>"/>
			<?php if( $rsProducts["Products_IsShippingFree"] == 1){?>
			<input type="hidden" name="IsShippingFree" value="1"/>
			<?php }else{?>
			<input type="hidden" name="IsShippingFree" value="0"/>
			<?php }?>
			<input type="hidden" name="ProductsWeight" value="<?=$rsProducts["Products_Weight"];?>"/>
			<div id="simple-info">
				<div id="product-thumb"> <img width="100px" height="100px" src="<?=$rsProducts['ImgPath']?>" /> </div>
				<div id="info-txt">					
					<p class="orange" style="font-size:18px; line-height:30px">&yen;<span id="cur-price-txt" style="font-size:20px;">
						<?//=$cur_price?>
						<?=(($min_Attr_Price==0 && $max_Attr_Price==0) || ($min_Attr_Price == $max_Attr_Price) )?$rsProducts["Products_PriceX"] + $min_Attr_Price : ($rsProducts['Products_PriceX']+$min_Attr_Price).'-'.($rsProducts['Products_PriceX']+$max_Attr_Price)?>
						</span> </p>
					<p><span style=" line-height:26px;">
						<?=$rsProducts['Products_Name']?>
						</span> </p>
					<p>
						 <span>库存<span id="stock_val">
					<?=$rsProducts['Products_Count']?>
					</span>件 </span> 
					</p>
				</div>
				<div id="close-btn"></div>
				<div class="clearfix"></div>
			</div>
			<div class="option-val-list">
			
				<!-- choose begin -->
				<ul id="choose" class="list-group">
					<!-- {* 开始循环所有可选属性 *} --> 
					<!-- {foreach from=$specification item=spec key=spec_key} -->
					<?php $spec_list = array(); ?>
					
					<?php foreach($specification as $spec_key=>$spec){?>
						 <!--edit in 20160318-->
							<?php foreach($spec['Name'] as $ks1 => $vs1): ?>
                                            <li  id="choose-version" class="list-group-item">
                                                    <div class="dt">
                                                      <?=$spec['Name'][$ks1]?>
                                                      ：</div>
                                                    <div class="dd catt"> 
                                                      <!-- {* 判断属性是复选还是单选 *} -->
                                                    <?php if($spec['Attr_Type'] == 1):?>
                                                            <?php foreach($spec['Values'] as $key=>$value):?>
                                                                    <?php
																	/*--20160521-start---*/
																		if(isset($spec['Name']) && isset($value['label'])){
																			if(count(count($spec['Name'])>$value['label'])){
																				$new_add = count($spec['Name'])-count($value['label']);
																				for($i=0;$i<$new_add ;$i++){
																					array_push($value['label'],'未设置属性');
																				} 
																			}
																		}
																	/*--20160521-end---*/	
                                                                            if(isset($array_vals[$ks1]) && !empty($array_vals[$ks1])){  ////循环一次就保存，第二次对比是否遍历过 
                                                                                    if(in_array($value['label'][$ks1],$array_vals[$ks1])){
                                                                                            //break;
																							continue;	
                                                                                    }
                                                                            }
                                                                    ?> 
                                                             <!-- //($key == 0)?'cattsel':''; --> 															
                                                        <a  class="<?=($ks1 == 0)?'':'porpers';?> <?=$value['label'][$ks1]?> class<?=$ks1?>" url="<?=($ks1 == 0)?'':'porpers';?> " value="class<?=$ks1?>" onclick="changeAtt(this)" href="javascript:;" name="<?=$value['id']?>" title="<?=$value['label'][$ks1]?>">
                                                                <?php if($key ==0){
                                                                        $spec_list[] = $value['id'];
                                                                        };
                                                                        //print_r($value['id']);
                                                                ?>
                                                                <?=$value['label'][$ks1]?>
                                                                <input style="display:none" id="spec_value_<?=$value['id']?>" type="radio" name="spec_<?=$spec_key?>" value="<?=$value['id']?>" <?=($key == 0)?'checked':'';?> />
                                                                <input class="childrenatt" style="display:none" onclick="getAtt(this)" value="<?=$value['label'][$ks1]?>">    
                                                        </a>
																		<?php $array_vals[$ks1][] = $value['label'][$ks1]; //循环一次就保存，第二次对比是否遍历过 ?>
                                                            <?php endforeach; ?>
                                                    <?php else: ?>
                                                            <?php foreach($spec['Values'] as $key=>$value):?>
                                                            <label for="spec_value_<?=$value['id']?>">
                                                                  <?=$value['label']?>
                                                                  <input type="checkbox" name="spec_<?=$spec_key?>[]" value="<?=$value['id']?>" id="spec_value_<?=$value['id']?>" onClick="changePrice()" />
                                                            </label>
                                                            <!-- {/foreach} -->
                                                            <?php endforeach; ?>
                                                            <div class="clearfix"></div>
                                                    <?php endif; ?>
                                                    </div>
                                                    <div class="clearfix"></div>
                                            </li>
                               <?php endforeach;?> 
					<?php } ?>
					<input type="hidden" id="spec_list" name="spec_list" value="<?=implode(',', $spec_list)?>" />
					<!-- {* 结束循环可选属性 *} -->
				</ul>
				<!--choose end-->
				<div id="qty_selector" style="line-height: 45px;font-size: 16px;font-weight: normal;color: #525252;">
					<input type="hidden" id="cur_price" value="<?=$cur_price?>"/>
					<input type="hidden" id="no_attr_price" value="<?=$no_attr_price?>"/>
					购买数量：<a href="javascript:void(0)" class="qty_btn" name="minus">-</a>
					<input type="text" id="qtycount" name="Qty" maxlength="3" style="height:28px; line-height:28px" value="1" pattern="[0-9]*" />
					<a href="javascript:void(0)" class="qty_btn" name="add">+</a>
					<br/>
					
					</div>
			</div>
			<script>
							
                            $("#qtycount").blur(function(){
								
                                var qtycount = $("#qtycount").val();
                                var totalCount = $("#stock_val").text(); 
								//alert(qtycount);alert(totalCount);
                                if(qtycount>totalCount){
                                    //$("#qtycount").val(1);
                                } 
                            })
							
                        </script>
			<input type="hidden" name="ProductsID" value="<?php echo $ProductsID ?>" />
		</form>
		<div style="height:60px;"></div>
		<a href="javascript:void(0);" id="selector_confirm_btn">确定</a> </div>
</div>
<!-- 属性选择内容end --> 

<!-- 页脚panle begin -->

<!--解决footer遮挡问题 -->
<div style="height:45px;"></div>

<div id="footer_panel_content">
	<input type="hidden" name="flag" value="<?php  echo $flag ?>" />
	<ul id="footer-panel">
		<li class="icon-container"> <span>
			<?php
					$id = $rsProducts['Products_IsFavourite']?'favorited':'favorite';
				?>
			<b id="<?=$id?>"  isFavourite="<?=$rsProducts['Products_IsFavourite']?>" productid="<?=$rsProducts['Products_ID']?>">收藏</b> </span> <span><b class="cart" onClick="javascript:location.href='/api/<?=$UsersID?>/shop/cart/?love';">购物车</b>
			<?php 
			$car_num = 0;
			if(!empty($_SESSION[$UsersID.'CartList'])){
				$sessionCart = json_decode($_SESSION[$UsersID.'CartList'],true);				
				foreach($sessionCart as $key_second => $value_second){
					foreach($value_second as $key_third => $value_third){
						$car_num += $value_third['Qty'];
					}
				}
			}
		?>
		<i <?php if(empty($car_num)){?>style="display:none"<?php }?>><?php echo $car_num;?></i> </span> </li>
		<?php if($rsProducts['Products_Count']<=0){?>
		<li class="button-conteiner-virtual">
			<span style="display:block; font-size:16px; width:100%; height:50px; line-height:50px; background:#ddd; text-align:center">库存不足</span>
		</li>
		<?php }else{?>
		<?php if($rsProducts['Products_IsVirtual']==0){?>
		<li class="button-conteiner"><a href="javascript:void(0)" id="menu-direct-btn">立即购买</a></li>
		<li class="button-conteiner"><a href="javascript:void(0)" id="menu-addtocard-btn">加入购物车</a></li>
		<?php }else{?>
		<li class="button-conteiner-virtual"><a href="javascript:void(0)" id="menu-direct-btn">立即购买</a></li>
		<?php }?>
		<?php }?>
		<div class="clearfix"></div>
	</ul>
</div> 
<!-- 页脚panle end -->

<footer id="footer" class="cls_footer"></footer>
<script>
//取消ajax请求时,原footer的优先级为0,解决冲突bug
$(function(){
	$("footer").each(function(){
		if ($(this).attr("class") != 'cls_footer') {
			$(this).css('z-index', 0)
		}
	})
})
</script>
<div id="back-to-top" style="display:none"></div>
<?php
$kfConfig=$DB->GetRs("kf_config","*","where Users_ID='".$UsersID."' and KF_IsShop=1 and KF_Code<>''");
if($kfConfig){
	echo htmlspecialchars_decode($kfConfig["KF_Code"],ENT_QUOTES);
}
?>
<?php if($rsConfig["CallEnable"] && $rsConfig["CallPhoneNumber"]){?>
<script language='javascript'>var shop_tel='<?php echo $rsConfig["CallPhoneNumber"];?>';</script> 
<script type='text/javascript' src='/static/api/shop/js/tel.js?t=<?php echo time();?>'></script>
<?php }?>
<?php if(!empty($share_config)){?>
<script language="javascript">
		var share_config = {
		   appId:"<?php echo isset($share_config["appId"]) ? $share_config["appId"] : '';?>",   
		   timestamp:<?php echo isset($share_config['timestamp']) ? $share_config["timestamp"] : time();?>,
		   nonceStr:"<?php echo isset($share_config["noncestr"]) ? $share_config["noncestr"] : '';?>",
		   url:"<?php echo isset($share_config["url"]) ? $share_config["url"] : '';?>",
		   signature:"<?php echo isset($share_config["signature"]) ? $share_config["signature"] : '';?>",
		   title:"<?php echo $share_config["title"];?>",
		   desc:"<?php echo str_replace(array("\r\n", "\r", "\n"), "", $share_config["desc"]);?>",
		   img_url:"<?php echo $share_config["img"];?>",
		   link:"<?php echo $share_config["link"];?>"
		};
		$(document).ready(global_obj.share_init_config);
	</script>
<?php }?>
<div class='conver_favourite'><img src="/static/api/images/global/share/favourite.png" /></div>
<span style="display:none" id="buy-type"></span>
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/substribe.php');?>
<script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script> 
<script>
	$(document).ready(function(){
	    $("img").scrollLoading();
	});
</script> 
<script>
 
$(function(){  
	var InstID = 'body';
	var ScrollID = window;

	//根据请求类型是否为ajax来定位不同的元素
	if ($("#product_Detail_mask").size() > 0) {
		InstID = "#product_Detail_mask";
		ScrollID = "#product_Detail_mask"
	}

	//当滚动条的位置处于距顶部100像素以下时，跳转链接出现，否则消失  
	$(ScrollID).scroll(function(){
		if ($(ScrollID).scrollTop()>100){ 
			$("#back-to-top").fadeIn(1500); 
		} else{ 
			$("#back-to-top").fadeOut(1500);
		}  
	});  

	//当点击跳转链接后，回到页面顶部位置  
	$("#back-to-top").click(function(){
		var a = $(InstID).scrollTop();
		$(InstID).animate({scrollTop:0},1000,'swing',function(){

		});  
		return false;  
	});  

});  
</script>

<?php
if (! isAjax()) {
?>
</body>
</html>
<?php
}
?>