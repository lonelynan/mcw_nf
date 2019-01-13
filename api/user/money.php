<?php require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}
if(!strpos($_SERVER['REQUEST_URI'],"OpenID=")){
	if(empty($_SESSION[$UsersID.'OpenID'])){
		$_SESSION[$UsersID.'OpenID']=session_id();
	}
}else{
	$url_arr = explode("OpenID=",$_SERVER['REQUEST_URI']);
	$endpos = explode("&",$url_arr[1]);
	$_SESSION[$UsersID.'OpenID']=$endpos[0];	
}

if(!empty($_SESSION[$UsersID."User_ID"])){
	$userexit = $DB->GetRs("user","*","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Users_ID='".$UsersID."'");
	if(!$userexit){
		$_SESSION[$UsersID."User_ID"] = "";
	}	
}

if(!strpos($_SERVER['REQUEST_URI'],"mp.weixin.qq.com")){
	header("location:?wxref=mp.weixin.qq.com");
}
$rsConfig=$DB->GetRs("user_config","*","where Users_ID='".$UsersID."'");
if(empty($_SESSION[$UsersID."User_ID"]) || !isset($_SESSION[$UsersID."User_ID"])){
	$_SESSION[$UsersID."HTTP_REFERER"]="/api/".$UsersID."/user/money/?wxref=mp.weixin.qq.com";
	header("location:/api/".$UsersID."/user/login/?wxref=mp.weixin.qq.com");
}
$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]);
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>会员中心</title>
<link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/user.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/style.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/js/user.js'></script>
</head>

<body>
<script language="javascript">$(document).ready(user_obj.money_init);</script>
<div id="integral_header">
  <div class="l"><span><?php echo $rsUser["User_Money"] ?></span><br />
    我的余额
  </div>
</div>
<div id="integral_get_use">
  <div class="border_r"><a href="/api/<?php echo $UsersID ?>/user/charge/">充值</a></div>
  <div><a href="/api/<?php echo $UsersID ?>/user/paymoney/">实体店消费</a></div>
</div>
<div id="integral">
  <div class="list_item">
    <div class="dline"></div>
	<a href="/api/<?php echo $UsersID ?>/user/charge_record/" class="item item_0"><span class="ico"></span>充值记录<span class="jt"></span></a>
	<a href="/api/<?php echo $UsersID ?>/user/money_record/" class="item item_0"><span class="ico"></span>资金流水<span class="jt"></span></a>
	<!--<a href="/api/<?php echo $UsersID ?>/user/charge_useryielist/" class="item item_0"><span class="ico"></span>收益明细<span class="jt"></span></a>-->
	<a href="/api/<?php echo $UsersID ?>/user/zhuanzhang/" class="item item_0"><span class="ico"></span>余额互转<span class="jt"></span></a>
</div>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>