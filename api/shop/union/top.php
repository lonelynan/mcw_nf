<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>

<?php
echo $rsConfig["ShopName"];
?>

</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/style.css?t=<?= time() ?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.11.1.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js?t=<?= time() ?>'></script>
<script type='text/javascript' src='/static/api/shop/js/shop.js?t=<?= time() ?>'></script>
<link href='/static/api/shop/union/page.css?t=<?= time() ?>' rel='stylesheet' type='text/css' />
<script type="text/javascript" src="/static/js/plugin/cookies/jquery.cookie.js"></script> 
<script language="javascript">
var UsersID = "<?=$UsersID?>";
$(document).ready(shop_obj.page_init);
</script>
</head>
