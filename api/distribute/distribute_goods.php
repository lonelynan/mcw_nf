<?php
require_once('global.php');

if(!empty($_GET["ProductID"])){
	$ProductsID = $_GET["ProductID"];

	$where = array('Users_Id'=>$UsersID,'Products_SoldOut'=>0,'Products_ID'=>$ProductsID,'Products_Status'=>1);
	$product_obj  = Product::multiWhere($where)->first();
	if(empty($product_obj)){
		echo '无此产品';
		exit();
	}else{
		$rsProducts =   $product_obj->toArray();
		$product = handle_product($rsProducts);
	}

}else{
	echo '缺少必要的参数产品ID';
}

require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_qrcode.class.php');
$weixin_qrcode = new weixin_qrcode($DB,$UsersID);
$qrcode_path = $weixin_qrcode->get_qrcode("products_".$owner['id']."_".$ProductsID);

$ds_list = $product['Products_Distributes'] ? json_decode($product['Products_Distributes'], true) : array();

if(empty($ds_list)){
	$ds_money = 0;
}else{
	$rsBiz=$DB->GetRs("biz","Finance_Type,Finance_Rate","where Biz_ID=".$rsProducts["Biz_ID"]);
	if ($rsBiz['Finance_Type'] == 1) {
		if($product["Products_FinanceType"]==0){//交易额
			$Products_Profit = number_format($rsProducts["Products_PriceX"] * $rsProducts["Products_FinanceRate"]/100,2,'.','');
		}elseif($product["Products_FinanceType"]==1){//供货价
			$Products_Profit = $rsProducts["Products_PriceX"]-$rsProducts["Products_PriceS"];
		}
	} else {
		$Products_Profit = number_format($rsProducts["Products_PriceX"] * $rsBiz["Finance_Rate"]/100,2,'.','');
	}

	$ds_money = $Products_Profit*$product['platForm_Income_Reward']*$product['commission_ratio']*$ds_list[$rsAccount['Level_ID']][0]/1000000;  //分销佣金

}
$ds_money = number_format($ds_money,2,'.','');
//自定义初始化
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_jssdk.class.php');
$weixin_jssdk = new weixin_jssdk($DB,$UsersID);
$share_config = $weixin_jssdk->jssdk_get_signature();
if(!empty($share_config)){
	$share_config["link"] = $shop_url.'/'.$owner['id'].'/products/'.$ProductsID.'/';
	$share_config["title"] = $product["Products_Name"];
	if($owner['id'] != '0' && $rsConfig["Distribute_Customize"]==1){
		$share_config["desc"] = $owner['shop_announce'] ? $owner['shop_announce'] : $rsConfig["ShareIntro"];
		if (!empty($product['ImgPath'])) {
			$share_config["img"] = strpos($product['ImgPath'],"http://")>-1 ? $product['ImgPath'] : 'http://'.$_SERVER["HTTP_HOST"].$product['ImgPath'];
		} else {
			$share_config["img"] = strpos($owner['shop_logo'],"http://")>-1 ? $owner['shop_logo'] : 'http://'.$_SERVER["HTTP_HOST"].$owner['shop_logo'];
		}
	}else{
		$share_config["desc"] = $rsConfig["ShareIntro"];
		if (!empty($product['ImgPath'])) {
			$share_config["img"] = strpos($product['ImgPath'],"http://")>-1 ? $product['ImgPath'] : 'http://'.$_SERVER["HTTP_HOST"].$product['ImgPath'];
		} else {
			$share_config["img"] = strpos($rsConfig['ShareLogo'],"http://")>-1 ? $rsConfig['ShareLogo'] : 'http://'.$_SERVER["HTTP_HOST"].$rsConfig['ShareLogo'];
		}
	}
	//商城分享相关业务
	include("../share.php");
}

$header_title = $product['Products_Name'];
require_once('header.php');
?>
<body style="background-color: #fff">
<link href="/static/api/distribute/css/goods_distribute.css?t=<?=time()?>" rel="stylesheet">
<script type="text/javascript" src="/static/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="/static/js/plugin/cupture/cupture.js"></script>

<div class="top">
	<div class="col-xs-1 top_left"><span class="fa  golden fa-usd fa-3x"></span></div>
	<span class="top_right">
		<p><?php echo empty($rsConfig['DisSwitch'])?'可得财富':'分销佣金'?><span class="red"><?=$ds_money?></span>元<br/>
			已销售<span class="red"><?=$product['Products_Sales']?></span>件</p>
	</span>
</div>
<div class="wrap" id="canvas2image">
	<?php if($rsAccount['status']): ?>
		<div class="container">

			<div class="row">
				<div class="activity-image">
					<img  width="100%" src="<?=$product['ImgPath']?>"/>
				</div>
			</div>
		</div>
		<div class="mm_container">
			<div class="img_conta">
				<img src="<?=$qrcode_path?>?t=<?=time()?>" style="max-width:100%"/>
			</div>
			<div class="right_conta">
				<p class="title_conta"><?=$product['Products_Name']?></p>
				<p class="price_conta">&yen;<?=$product['Products_PriceX']?></p>
				<p class="saoma_conta">长按识别二维码或扫一扫购买,更多低价产品请直接进入商城查看</p>
			</div>
		</div>
	<?php else: ?>
		<div class="container">
			<div class="row">
				<p>&nbsp;&nbsp;&nbsp;&nbsp;您的分销账号已被禁用,&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?=$share_link?>" class="red">返回</a></p>

			</div>
		</div>
	<?php endif; ?>
</div>
<?php require_once('../shop/skin/distribute_footer.php');?>

<script  type="text/javascript" >
	$(document).ready( function(){
		if (!<?=$rsAccount['status']?>) {
			return false;
		}
		html2canvas(document.getElementById('canvas2image'), {
			allowTaint: true,
			taintTest: false,
			onrendered: function(canvas) {
				canvas.id = "mycanvas";
				var dataUrl = canvas.toDataURL();

				var  newImg = '<img src="'+dataUrl+'" width="100%" />';

				$('#canvas2image').html(newImg);

			}
		});

	});
</script>
</body>
</html>
