?><?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}
if (!empty($_POST)) {
	$Data = array(
		'Type_Name' => htmlentities($_POST['Type_Name']),
		'Type_CreateTime' => time(),
    'User_Id' => $_SESSION['Users_ID']
		);

	$Flag = $DB->Add("shop_virtual_card_type",$Data);

	if ($Flag) {
		echo '<script language="javascript">alert("添加成功");window.location="virtual_card_type.php";</script>';
	} else {
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	}
}
$jcurid = 2;
?>

<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/weicbd.js'></script>
    <script language="javascript">$(document).ready(weicbd_obj.mulu_edit_init);</script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/product_menubar.php');?>
    <div id="products" class="r_con_wrap">
      <div class="category">
        <div class="m_righter" style="margin-left:0px;">
          <form action="?" method="post" id="mulu_edit">
            <h1>添加虚拟卡密类型：</h1>
            
            <div class="opt_item">
              <label>类型名称：</label>
              <span class="input">
              <input type="text" name="Type_Name" value="" class="form_input" size="30" maxlength="30" notnull />
              <font class="fc_red">*</font></span>
              <div class="clear"></div>
            </div>
	         <br />
          
            <div class="opt_item">
              <label></label>
              <span class="input">
              <input type="submit" class="btn_green btn_w_120" name="submit_button" value="添加" />
              <a href="javascript:void(0);" class="btn_gray" onClick="location.href='virtual_card.php'">返回</a></span>
              <div class="clear"></div>
            </div>
          </form>
        </div>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
</body>
</html>