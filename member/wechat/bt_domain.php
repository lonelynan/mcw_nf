<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$rsDombt = $DB->GetRs("users","domenable,domname","where Users_ID='".$_SESSION["Users_ID"]."'");
$tis = '绑定成功';
if ($_POST) {	
  if (isset($_POST["domenable"]) && ($_POST['domenable'] ==1) && empty($_POST["domname"])) {
    echo '<script language="javascript">alert("绑定域名不能为空");history.back();</script>';
    exit;
  }		
	if ($_POST["domname"] == $_SERVER["HTTP_HOST"]) {
    echo '<script language="javascript">alert("非法操作");history.back();</script>';
    exit;
  }
		if (isset($_POST["domenable"])) {
    $rsUsers = $DB->GetRs("users", "domname", "where domname = '".$_POST["domname"]."' and domenable = 1 and Users_ID != '".$_SESSION["Users_ID"]."'");
		if($rsUsers){
		echo '<script language="javascript">alert("该域名绑定过了");history.back();</script>';
		exit;
	}
	} else {
		$tis = '解绑成功';
	}
		$Data = array(
			"domenable"=>isset($_POST["domenable"])?1:0,			
			"domname"=>$_POST["domname"]
		);
		$Flag = $DB->Set("users",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");	
	if($Flag)
	{
		echo '<script language="javascript">alert("'.$tis.'");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
	}else
	{
		echo '<script language="javascript">alert("操作失败");history.back();</script>';
	}
	exit;
}
$jcurid = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>好分销</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/wechat.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/wechat.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/jiebase_menubar.php');?>
    <div id="attention" class="r_con_wrap">
      <form id="attention_domain_form" class="r_con_form" method="post" action="bt_domain.php">        
        <div class="rows" id="img_msg_row">
          <label>域名绑定是否启用</label>
          <span class="input"><label>
          <input type="checkbox" value="1" name="domenable"<?php echo $rsDombt["domenable"]?" checked":""; ?> />
          <span class="tips">开启（开启后，绑定的域名才会有效）</span></label></span>
          <div class="clear"></div>
        </div>
		<div class="rows" id="text_msg_row">
          <label>域名</label>
          <span class="input">
          <input name="domname" value="<?php echo $rsDombt["domname"]; ?>"><span class="tips">&nbsp;&nbsp;&nbsp;例如：（www.abc.com）</span>
          </span>
          <div class="clear"></div>
        </div>        
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" name="submit_button" value="提交" />
          </span>
          <div class="clear"></div>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>