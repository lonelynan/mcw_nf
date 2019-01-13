<?php
require_once('global.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

$userid = empty($_SESSION[$UsersID.'User_ID']) ? 0 : $_SESSION[$UsersID.'User_ID'];

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
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/shop/js/shop.js'></script>
<script type='text/javascript' src='/static/api/js/user.js'></script>
<script language="javascript">$(document).ready(shop_obj.page_init);</script>
<script language="javascript">  	
  	$(document).ready(shop_obj.cart_init);
</script>
</head>
<body>
<div id="cart">
   <div class="cart_title"><b></b>设置</div>
	<div class="sz_x">
		<ul>
<?php
//是否为分销商
$user = $DB->GetRs('user', 'User_NickName, User_Mobile, User_HeadImg, User_PayPassword, Is_Distribute', "WHERE Users_ID='" . $UsersID . "' AND User_ID=" . intval($userid));

if ($rsConfig['Distribute_Customize'] == 0) {
    $show_logo = !empty($user['User_HeadImg']) ? $user['User_HeadImg'] : '';
} else {
    $show_logo = !empty($rsAccount['Shop_Logo']) ? $rsAccount['Shop_Logo'] : '';
}

if ($user['Is_Distribute'] == 1 && (empty($user['User_NickName']) || empty($user['User_Mobile']) || empty($show_logo) || empty($user['User_PayPassword']) ) ) {
?>
	<li>
		<a href="/api/<?=$UsersID?>/shop/member/init/">
		<span class="flt_x l">完善资料</span>
		<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="20px" height="20px"></span>
		</a>
		<div class="clear"></div>
	</li>	
<?php
} else {
?>
		<li>
			<a href="/api/<?=$UsersID?>/user/my/profile/">
			<span class="flt_x l">完善资料</span>
			<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="20px" height="20px"></span>
			</a>
			<div class="clear"></div>
		</li>		

	 <li>
		<a href="/api/<?=$UsersID?>/distribute/change_bind/">
		<span class="flt_x l ">修改手机号码</span>
		<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="22px" height="22px"></span>
		</a>
		<div class="clear"></div>
	</li>
	<li>
		<a href="javascript:void(0);" class="modify_password">
		<span class="flt_x l">修改登录密码</span>
		<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="22px" height="22px"></span>
		</a>
		<div class="clear"></div>
	</li>
	<li>
		<a href="/api/<?=$UsersID?>/user/payword/">
		<span class="flt_x l ">修改支付密码</span>
		<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="22px" height="22px"></span>
		</a>
		<div class="clear"></div>
	</li>
<?php
}
?>			 
			 			 <li>
				<a href="/api/<?=$UsersID?>/distribute/edit_shop/">
					<span class="flt_x l">自定义分享语</span>
					<span class="r"><img src="/static/api/shop/skin/default/images/gd.png" width="22px" height="22px"></span>
				</a>
				<div class="clear"></div>
			 </li>
		</ul>
	</div>
</div>

<!--修改密码div-->
<div class="pop_form" id="modify_password_div" style="display:none">
  <form id="modify_password_form">
    <h1>修改登录密码</h1>
    <input type="password" name="YPassword" class="input" value="" placeholder="原登录密码" notnull />
    <input type="password" name="Password" class="input" value="" placeholder="登录密码" notnull />
    <input type="password" name="ConfirmPassword" class="input" value="" placeholder="确认密码" notnull />
    <div class="btn">
      <input type="button" class="submit" value="提 交" />
      <input type="button" class="cancel" value="取 消" />
      <div class="clear"></div>
    </div>
  </form>
</div>
<!--//修改密码div -->

<script language="javascript">$(document).ready(user_obj.my_init);</script>

<div class="jlsz_x"></div>
<div>
	<a class="tcd_x" href="/api/<?=$UsersID?>/user/logout/">退出登录</a>
</div>
</body>
</html>