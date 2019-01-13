<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');

if(isset($_GET["pama"])){
	$pama=$_GET["pama"];
}else{
	echo '缺少必要的参数';
	exit;
}
$arr = explode("_",$pama);
$OrderID = $arr[0];
$ProductsID = $arr[1];
$KEY = $arr[2];

$rsOrder=$DB->GetRs("user_order","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."' and Order_ID='".$OrderID."'");
$CartList=json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]),true);
$item = $CartList[$ProductsID][$KEY];

$text = $rsOrder['Order_Status'] == 2 ? '退款' : '退货';

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>申请<?= $text ?> - 个人中心</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/shop/js/shop.js'></script>
<script language="javascript">$(document).ready(shop_obj.backup_init);$(document).ready(shop_obj.page_init);</script>
</head>

<body>
<div id="shop_page_contents">
  <div id="cover_layer"></div>
  <link href='/static/api/shop/skin/default/css/member.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
  
  <div id="order_detail">
    <div class="item">
        <div class="pro" >
            <div class="img"><a href="/api/<?=$UsersID?>/shop/products/<?=$ProductsID?>/?love"><img src="<?=$item["ImgPath"]?>" height="100" width="100"></a></div>
            <dl class="info" style="padding-top:0px; padding-bottom:0px; margin-top:0px; margin-bottom:0px">
                <dd class="name" style="padding:0px; margin:0px"><a href="/api/<?=$UsersID?>/shop/products/<?=$ProductsID?>/?love"><?=$item["ProductsName"]?></a></dd>
				<?php
                $price = (!empty($item['user_curagio']) && $item['user_curagio'] > 0 && $item['user_curagio'] <= 100 ? (100 - $item['user_curagio']) / 100 : 1) * (isset($item["Property"]['shu_pricesimp']) ? $item["Property"]['shu_pricesimp'] : $item["ProductsPriceX"]);
				?>
                <dd style="padding:0px; margin:0px">￥<?=$price?>×<?=$item["Qty"]?>=￥<?=$price*$item["Qty"]?></dd>
                <dd style="padding:0; margin:0;"><?= empty($item["Property"]["shu_value"]) ? '' : $item["Property"]["shu_value"] ?></dd>
            </dl>
            <div class="clear"></div>
         </div>
    </div>
	<form action="<?=$base_url?>api/<?=$UsersID?>/shop/member/" name="apply_form" id="apply_form" />	
     <input type="hidden" name="action" value="apply_backup" />
     <input type="hidden" name="OrderID" value="<?=$OrderID;?>" />
     <input type="hidden" name="ProductsID" value="<?=$ProductsID;?>" />
     <input type="hidden" name="KEY" value="<?=$KEY;?>" />
     	<div class="backup_reason">
         <dl>
           <dd><?= $text ?>数量：<br /><select name="Qty">
            <?php
				for($i=1; $i<=$item["Qty"]; $i++){
			?>
            <option value="<?php echo $i;?>"><?php echo $i;?></option>
            <?php }?>
           </select></dd>
           <dd>退款账号<br /><textarea name="Account" placeholder="请填写退款账号和户名" notnull></textarea></dd>
           <dd><?= $text ?>原因<br /><textarea name="Reason" notnull></textarea></dd>
           <dt><input type="submit" name="submit" class="submit" value="确定<?= $text ?>" /></dt>
         </dl>
        </div>
    </form>
  </div>
</div>
<?php require_once('../skin/distribute_footer.php'); ?>
</body>
</html>



