<?php 
/*edit in 20160318*/
require_once('top.php');
if (!empty($rsAttr['skuvaljosn'])) {
$rsAttr_Data = json_decode($rsAttr['skuvaljosn'],true);
foreach($rsAttr_Data as $key=>$value){
	if($value['Txt_CountSon'] == 0){
		unset($rsAttr_Data[$key]);
	}
}
$rsAttr['skuvaljosn'] = json_encode($rsAttr_Data,JSON_UNESCAPED_UNICODE);
}
if (empty($rsAttr['skuimg'])) {
	$skuimg = '""';
}else{
	$skuimg = $rsAttr['skuimg'];
}
?>
<script language="javascript">
var keys = <?php echo $flag ? $rsAttr['skujosn'] : '""';?>;
var data = <?php echo $flag ? $rsAttr['skuvaljosn'] : '""';?>;
var imgdata = <?php echo $flag ? $skuimg : '""';?>;
var cur_jiage = <?php echo $cur_jiage;?>;
</script>
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
<?php if ($flag) {?>
<script type='text/javascript' src='/static/api/shop/js/sku.js?t=<?=time();?>'></script>
<?php } ?>
<link href="/static/api/css/fx_index.css?t=<?php echo time();?>" rel="stylesheet" type="text/css" />
<style>
    a:hover {
        color: #00ccff;
    }
</style>

<!-- 产品图片所需插件 end --> 

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
<!--<header class="bar bar-nav"> <a href="javascript:history.go(-1)" class="pull-left"><img src="/static/api/shop/skin/default/images/black_arrow_left.png" /></a>-->
<!--	<h1 class="title" id="page_title">产品详情</h1>-->
<!--</header>-->
<div id="allpro" style="position: absolute;top: 0;bottom: 0;overflow-y: scroll;-webkit-overflow-scrolling:touch;width: 100%;">
<div id="overlay"></div>
<div id="wrap"> 
	<!-- 产品图片开始 -->
	<div id="detail_images">
		<div class="pro_img">
			<div class="touchslider">
				<div class="img">
					<div class="touchslider-viewport">
						<div class="list">
							<?php if(isset($JSON["ImgPath"])){
									foreach($JSON["ImgPath"] as $key=>$value){
										echo '<div class="touchslider-item"><a href="'.$value.'" rel="'.$value.'"><img src="'.$value.'" style="max-height:331px;" /></a></div>';
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
			<div id="name_and_share">
                <span id="product_name">
				<?=$rsProducts["Products_Name"]?>
				</span> 
				<?php if($perm_config['detailbone']['Perm_Field'] == 'detailbone' && $perm_config['detailbone']['Perm_On'] == 0) { ?>
				<span id="shu_xian"></span> <a class="detail_share" id="share_product" productid="<?=$ProductsID?>" is_distribute="<?=$Is_Distribute?>"><?=$perm_config['detailbone']['Perm_Name']?></a>
				<?php } ?>
				<div class="clearfix"></div>
				<span id="product_price">&yen;
				<?=(empty(intval($rsProducts["Products_PriceA"]))) ? $rsProducts["Products_PriceX"] : $rsProducts["Products_PriceA"] ?>
				元</span>&nbsp;&nbsp;&nbsp;&nbsp; <del id="product_origin_price">&yen;
				<?=$rsProducts["Products_PriceY"]?>
				元</del>
            </div>
            <div class="conbox">
                <?php if($un_accept_coupon_num  >0):?>
                <div id="activity_info">
                    <p><a href="/api/<?=$UsersID?>/user/coupon/1/"><img src="/static/api/shop/skin/default/quan_icon.jpg" />&nbsp;点击领取优惠券</a></p>
                    <?php endif; ?>
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
                </div>
            <?php endif; ?>
            </div>
			<p id="other_info">
				<?php if($rsProducts["Products_IsShippingFree"] == 1){?>
				<span>快递免运费</span>
				<?php }?>
				<?php if($perm_config['detailbtwo']['Perm_Field'] == 'detailbtwo' && $perm_config['detailbtwo']['Perm_On'] == 0) { ?>
				<span><?=$perm_config['detailbtwo']['Perm_Name']?>
				<?=$rsProducts["Products_Sales"]?>
				件</span>
				<?php } ?>
				</p>
		</div>
	</div>
</div>

<!--<div class="conbox">
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
</div>-->

<!-- 会员专享价begin -->
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
    <li class="list-group-item red">&nbsp;&nbsp;
      <?=$item['Name']?>
      价&nbsp;&nbsp;<strong>&yen;
      <?=$item['price']?>
      </strong></li>
    <?php else: ?>
    <li class="list-group-item">&nbsp;&nbsp;
      <?=$item['Name']?>
      价&nbsp;&nbsp;<strong class="red">&yen;
      <?=$item['price']?>
      </strong></li>
    <?php endif;?>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>
<!-- 会员专享价end -->

<div id="detail">
	<div class="b5"></div>
	<div class="commit">
		<a href="<?=$base_url?>api/<?=$UsersID?>/shop/commit/<?=$ProductsID?>/?love">
			<span>&nbsp;</span>商品评价
			<!--<label class="juice">(共<?=$comment_aggregate['num']?>条)</label>
			<label class="pull-right juice"><?=$comment_aggregate['points']?>分</label>-->
            <?php if($comment_aggregate['points']!=0){?>
			<label class="juice"><?=$comment_aggregate['points']?>分</label>
            <?php }?>
            <?php if($comment_aggregate['num']!=0){?>
			<label class="pull-right juice"><?=$comment_aggregate['num']?> 人评价</label>
            <?php }else{?>
            <label class="pull-right juice">评价</label>
            <?php }?>
		</a>
	</div>
	 <?php foreach($commit_list as $vv){?>
		<div class="product_comments">
		<span><?=$vv['Note']?></span>
		<p style="text-align:right;padding-right: 10px; padding-bottom: 5px;"><?= date("Y-m-d H:m:s",$vv['CreateTime'])?></p>
		</div>
		<hr  style="margin:0px;" />
	<?php } ?>
	<div><a href="<?=$base_url?>api/<?=$UsersID?>/shop/commit/<?=$ProductsID?>/?love" style="width:100%;height:30px;line-height:30px; display:block; text-align:center;color:#EB6001;">查看更多>></a></div>

	<div class="b5"></div>
	<?php if($IsStore==1){?>
	<div class="company">
		<div class="company_info"> <img src="<?php echo $rsBiz["Biz_Logo"] ? $rsBiz["Biz_Logo"] : $rsConfig["ShopLogo"];?>" /> <span><?php echo $rsBiz["Biz_Name"];?></span>
			<div class="clear"></div>
		</div>
		<div class="company_btns"> <a href="<?php echo $biz_url; ?>allcate/"><span><img src="/static/api/shop/skin/default/images/category_btns.png" width="16" /> 全部分类</span></a> <a href="<?php echo $biz_url; ?>"><span><img src="/static/api/shop/skin/default/images/home_btns.png" width="16" /> 店铺逛逛</span></a>
			<div class="clear"></div>
		</div>
	</div>
	<?php }?>
</div>

<div class="b5"></div>
<!-- 产品详情start -->
<div id="content-filter" class="center-block">
	<div style="width:100%;height:40px;overflow:hidden;border-top:solid 1px #dcdcdc;border-bottom:solid 1px #eeeeee"> <a href="#description" class="item selected">产品详情</a><span class="shu">|</span><a href="#panel2" class="item">产品参数</a> </div>
	<div id="table-panel">
		<div id="description">
			<div class="contents">
				<?=$rsProducts["Products_Description"]?><br><br>
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
        <?php if(!empty($parameter_list)){foreach($parameter_list as $ks=>$vs) { ?>
        <tr>
          <td class="tdTitle"><?=$vs['name']?></td>
          <td><?=$vs['value']?></td>
        </tr>
        <?php } } ?>
        
      </tbody>
    </table><br><br><br><br><br>
	</div>
	<a href="#" class="clearfix"></a> </div>
<!-- 产品详情end -->
<div class="b5"></div>
<!-- 属性选择内容begin -->
<div id="option_content">
	<div class="pro_top">
		<div style=" float:right;">
			<div id="close-btna" class="cjlose thi"></div>
		</div>
		<div class="imgchege"><img src="<?=$rsProducts['ImgPath']?>"></div>
		<div class="mid">
				<p class="price_pro" id="price"></p>
				<p id="count"></p>
				<p class="parameter_pro" id="showattr"></p>
		</div>
	</div>
	<div id="option_selecter">
		<form name="addtocart_form" action="/api/<?php echo $UsersID ?>/shop/cart/" method="post" id="addtocart_form">
			<input type="hidden" name="OwnerID" value="<?=$owner['id']?>"/>			
			<?php if( $rsProducts["Products_IsShippingFree"] == 1){?>
			<input type="hidden" name="IsShippingFree" value="1"/>
			<?php }else{?>
			<input type="hidden" name="IsShippingFree" value="0"/>
			<?php }?>
			<input type="hidden" name="ProductsWeight" value="<?=$rsProducts["Products_Weight"];?>"/>
			<input type="hidden" name="shu_value" id="shu_value" value=""/>
			<input type="hidden" name="shu_attribute" id="shu_attribute" value=""/>
			<input type="hidden" name="shu_priceg" id="shu_priceg" value=""/>
			<?php if(!empty($rsattrarr)){ ?>			
				<?php foreach($rsattrarr as $key=>$value){ ?>
					<div class="pro_attribute">
					<p><?=$key?></p>	
					<?php foreach($value as $ak=>$av){?>
					<input type="button" class="sku" attr_id="<?=$ak?>" value="<?=$av?>"/>
				<?php }  ?>
				</div>
				<?php }  ?>
			<?php }  ?>
			<div class="option-val-list">
				<div id="qty_selector" style="line-height: 45px;font-size: 16px;font-weight: 600;color: #333;">
					<input type="hidden" id="cur_price" value="<?=$cur_price?>"/>
					<input type="hidden" id="no_attr_price" value="<?=$no_attr_price?>"/>
					购买数量：<a href="javascript:void(0)" class="qty_btn" name="minus">-</a>
					<input type="text" id="qtycount" name="Qty" maxlength="3" style="height:28px; line-height:28px; font-weight:normal;" value="1" pattern="[0-9]*" />
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
		<div style="height:40px;"></div>
		<a href="javascript:void(0);" id="selector_confirm_btn">确定</a> </div>
</div>
<!-- 属性选择内容end --> 

<!-- 页脚panle begin -->
<div id="footer_panel_content">
	<input type="hidden" name="flag" value="<?php  echo $flag ?>" />
	<ul id="footer-panel">
		<li class="icon-container"> <span>
			<?php
					$id = $rsProducts['Products_IsFavourite']?'favorited':'favorite';
				?>
			<b id="<?=$id?>"  isFavourite="<?=$rsProducts['Products_IsFavourite']?>" productid="<?=$rsProducts['Products_ID']?>">收藏</b> </span>
            <span><b class="cart" onClick="javascript:location.href='/api/<?=$UsersID?>/shop/cart/?love';">购物车</b>
			<?php 
			$car_num = 0;
			if(!empty($_SESSION[$UsersID.'CartList'])){
				$sessionCart = json_decode($_SESSION[$UsersID.'CartList'],true);
				foreach($sessionCart as $key_first => $value_first){
					foreach($value_first as $key_second => $value_second){
						foreach($value_second as $key_third => $value_third){
							$car_num += $value_third['Qty'];
						}
					}
				}
			}
		?>
		<i <?php if(empty($car_num)){?>style="display:none"<?php }?>><?php echo $car_num;?></i> </span><span><b class="kf" onClick="javascript:location.href='<?=$rsBiz['Biz_KfProductCode']?>';">客服</b></span> </li>
		<?php if($rsProducts['Products_Count']<=0){?>
		<li class="button-conteiner-virtual">
			<span style="display:block; font-size:16px; width:100%; height:50px; line-height:50px; background:#ddd; text-align:center">库存不足</span>
		</li>
		<?php }else{?>
		<?php if($rsProducts['Products_IsVirtual']==0){?>
		<li class="button-conteiner"><a href="javascript:void(0)" id="menu-direct-btn">立即购买</a></li>
		<li class="button-conteiner"><a href="javascript:void(0)" id="menu-addtocard-btn">加入购物车</a></li>
		<?php }else{?>
		<!--<li class="button-conteiner-virtual"><a href="javascript:void(0)" id="menu-direct-btn">立即购买</a></li>-->
		<li class="button-conteiner"><a href="javascript:void(0)" id="menu-direct-btn">立即购买</a></li>
		<?php }?>
		<?php }?>
		<div class="clearfix"></div>
	</ul>
</div> 
<div class="mask"></div>
</div>
<!-- 页脚panle end <div id="back-to-top"></div>-->
<footer id="footer"></footer>

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
		   appId:"<?php echo $share_config["appId"];?>",   
		   timestamp:<?php echo $share_config["timestamp"];?>,
		   nonceStr:"<?php echo $share_config["noncestr"];?>",
		   url:"<?php echo $share_config["url"];?>",
		   signature:"<?php echo $share_config["signature"];?>",
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
<script language="javascript">
	$(document).ready(function(){
	    $("img").scrollLoading();
	});
	$(function(){
		//当滚动条的位置处于距顶部100像素以下时，跳转链接出现，否则消失  
		$(function () {  
			$(window).scroll(function(){  
				if ($(window).scrollTop()>100){  
					$("#back-to-top").fadeIn(1500);  
				}  
				else  
				{  
					$("#back-to-top").fadeOut(1500);  
				}  
			});  
	  
			//当点击跳转链接后，回到页面顶部位置  
	  
			$("#back-to-top").click(function(){  
				$('body,html').animate({scrollTop:0},1000);  
				return false;  
			});  
		});  
	});	
</script>
</body>
</html>