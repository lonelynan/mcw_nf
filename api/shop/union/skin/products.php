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
<?php
	//ad($UsersID, 1, 1);
?>

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


<!-- 产品图片所需插件 end --> 

<script language="javascript">	
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
<header class="bar bar-nav"> <a href="javascript:history.go(-1)" class="pull-left"><img src="/static/api/shop/skin/default/images/black_arrow_left.png" /></a>
	<h1 class="title" id="page_title">产品详情</h1>
</header>
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
			<div id="name_and_share"> 
			    <span id="product_name">
				    <?=$rsProducts["Products_Name"]?>
				</span>
				<p><?=$rsProducts['Products_BriefDescription']?></p>
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
				<span class="Sales">销量
				<?=$rsProducts["Products_Sales"]?>
				件</span>
				<?php if($rsProducts["Products_IsShippingFree"] == 1):?>
				<span class="Shipping">快递免运费</span>
				<?php endif;?>
			</p>
		</div>
	</div>
	<div id="detail">
		<div class="commit">
		    <a href="<?=$base_url?>api/<?=$UsersID?>/shop/commit/<?=$ProductsID?>/"><span>&nbsp;</span>
				<label class="juice">
				    <img src="/static/api/shop/union/star_<?php echo $comment_aggregate['ico'];?>.png" />&nbsp;<?=$comment_aggregate['Points']?>
				</label>
				<label class="pull-right juice" style="color:#0092ff;">
				    查看全部<?=$comment_aggregate['NUM']?>条评论>>
				</label>
			</a>
		</div>
	</div>
</div>
<div class="b5"></div>
<!-- 产品详情start -->
<h4 class="sys_cpxq">产品详情</h4>
<div id="content-filter" class="center-block">
	<div id="table-panel">
		<div id="description">
			<div class="contents">
				<?=$rsProducts["Products_Description"]?>
			</div>
		</div>
	</div>
</div>
<!-- 产品详情end --> 
<div class="b5"></div>
<h4 class="sys_gwxz">购物须知</h4>
<div class="Notice">
	<?php echo htmlspecialchars_decode($rsBiz['Biz_Notice'],ENT_QUOTES);?>
</div>
<div class="b5"></div>
<h4 class="sys_china">浏览此商品的用户还看了</h4>
<div class="visit_more">
	<ul>
		<?php foreach($relatedProducts  as $key=>$item):?>
		<li> 
			<a href="<?=$shop_url?>union/products/<?=$item['Products_ID']?>/">
				<p class="img"><img src="<?=$base_url?><?=$item['ImgPath']?>" /></p>
				<p class="name">
					<?=$item['Products_Name']?>
				</p>
				<p class="price">￥
					<?=$item['Products_PriceX']?>
				</p>
			</a> 
		</li>
		<?php endforeach;?>
		<div class="clear"></div>
	</ul>
</div>
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
<style>
#footer #footer-panel  li{height:45px;}
#footer #footer-panel  li del{margin-top:10px;}
#footer #footer-panel  li p{margin-top:5px;}
#footer #footer-panel li.icon-container-v {width:20%;float:left;text-align:center;}
#footer #footer-panel li.button-conteiner{width:20%;float:left;}
#footer #footer-panel li.icon-container-v span{width:100%;}
</style>
<!-- 页脚panle begin -->
<div id="footer_panel_content">
<input type="hidden" name="flag" value="<?php  echo $flag ?>" />
	<ul id="footer-panel">
	    <li class="icon-container-v" style="width:22%;">
			<p>活动价</p>
			<p id="product_price">&yen;
        	<?=($rsProducts["Products_PriceA"] == 0) ? $rsProducts["Products_PriceX"] : $rsProducts["Products_PriceA"] ?>
			</p>
		</li>
		<li class="icon-container-v" style="line-height:50px;">
			<del id="product_origin_price" >
				&yen;
				<?=$rsProducts["Products_PriceY"]?>
			</del>
		</li>
		<li class="button-conteiner" style="padding-top:5px;">
			<b onClick="javascript:location.href='<?=$rsBiz['Biz_KfProductCode'] ?>'" class="kf">客服</b>
		</li>
		<li class="button-conteiner" style="width:27%;">
			<a href="javascript:void(0)" id="menu-direct-btn">立即购买</a>
		</li>
		
		<div class="clearfix"></div>
	</ul>
</div>
<div class="mask"></div>
<!-- 页脚panle end -->
<footer id="footer"></footer>
<div id="back-to-top"></div>
<script language='javascript'>
    var KfIco = '/static/kf/ico/00.png';
    var OpenId = '';
    var UsersID = '<?=$UsersID?>';
</script>
<?php if(!empty($kfConfig)){?>
<script language='javascript'>var KfIco='<?php echo $KfIco;?>'; var OpenId='<?php echo empty($_SESSION[$UsersID."OpenID"]) ? '' : $_SESSION[$UsersID."OpenID"];?>'; var UsersID='<?php echo $UsersID;?>'; </script> 
<script type='text/javascript' src='/kf/js/webchat.js?t=<?php echo time();?>'></script>
<?php }?>
<?php if($rsConfig["CallEnable"] && $rsConfig["CallPhoneNumber"]){?>
<script language='javascript'>var shop_tel='<?php echo $rsConfig["CallPhoneNumber"];?>';</script> 
<script type='text/javascript' src='/static/api/shop/js/tel.js?t=<?php echo time();?>'></script>
<?php }?>
<div class='conver_favourite'><img src="/static/api/images/global/share/favourite.png" /></div>
<span style="display:none" id="buy-type"></span>
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/substribe.php');?>
    <script>  
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