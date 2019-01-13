<?php
require_once('global.php');

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>设置</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/user.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/style.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/css/cart.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>

</head>
<body>
<div id="cart">
   <div class="cart_title"><a href="/api/<?=$UsersID?>/distribute/"><b></b></a>我的二维码</div>
	<div class="sz_x">
		<ul>
			<li>
            <a href="/api/<?=$UsersID?>/distribute/qrcodehb/">
                <span class="flt_x l">推广二维码</span>
                <!--<span class="flt1_x l">你是我的小苹果</span>-->
                <span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="20px" height="20px"></span>
            </a>
            <div class="clear"></div>
			 </li>		
			 	 <li>
				<a href="/api/<?=$UsersID?>/distribute/qrcodehb/">
					<span class="flt_x l ">微信二维码</span>
					<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="22px" height="22px"></span>
				</a>
				<div class="clear"></div>
			 </li>
			 <li>
				<a href="/api/<?=$UsersID?>/distribute/qrcodehb/">
					<span class="flt_x l modify_password">重生微信二维码</span>
					<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="22px" height="22px"></span>
				</a>
				<div class="clear"></div>
			 </li>
			
	
		</ul>
	</div>
</div>


</body>
</html>