<?php
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$itemid=empty($_REQUEST['itemid'])?'':$_REQUEST['itemid'];
if($_POST){	
	$Data=array(		
		"cusname"=>$_POST["cusname"],
		"cuspasswd"=>$_POST["cuspasswd"],
		"sendsite"=>isset($_POST["sendsite"]) ? $_POST["sendsite"] : ''
	);
	$Flag=$DB->Set("users_express_info",$Data,"where usersid='".$_SESSION["Users_ID"]."' and id=".$itemid);
	if($Flag){
		echo '<script language="javascript">alert("修改成功");window.location.href="settingdianzi_print.php";</script>';
	}else{
		echo '<script language="javascript">alert("修改失败");history.back();</script>';
	}
	exit;
}else{
	$item = $DB->GetRs("users_express_info","shippingcode,sendsite,cusname,cuspasswd","where usersid='".$_SESSION["Users_ID"]."' and id=".$itemid);
	if(!$item){
		echo '<script language="javascript">alert("非法操作");history.back();</script>';
		exit;
	}
	$companys = array();
	$DB->Get("shop_shipping_company","Shipping_Code,Shipping_Name","where Users_ID='".$_SESSION["Users_ID"]."'");
	while($r = $DB->fetch_assoc()){
		$companys[$r["Shipping_Code"]] = $r['Shipping_Name'];
	}
}
$jcurid = 8;
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
<script type='text/javascript' src='/static/member/js/shipping.js?t=20180411'></script>
<script>
$(document).ready(shipping_obj.printtemplate_init);
</script>
</head>

<body>
<div id="iframe_page">
  <div class="iframe_content">
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/jiebase_menubar.php');?>
    <div class="r_con_wrap">
    <div id="printtemplate" class="r_con_wrap">
      
      <script language="javascript"></script>
      <form id="printtemplate_form" class="r_con_form" method="post" action="?">
        <div class="rows">
          <label>物流公司</label>
          <span class="input">
          <select name="Company" disabled style="background:#dddddd;">          	
            <option value="1"><?php echo isset($companys[$item["shippingcode"]]) ? $companys[$item["shippingcode"]] : '快递公司添加有误！';?></option>          
          </select>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>客户帐号</label>
          <span class="input">
          <input name="cusname" value="<?=$item["cusname"]?>" type="text" class="form_input" size="40" maxlength="60" notnull>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>客户密码</label>
          <span class="input">
          <input name="cuspasswd" value="<?=$item["cuspasswd"]?>" type="text" class="form_input" size="40" maxlength="60" notnull>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
		
		<div class="rows"id="sendsite" style="display:<?php echo $item["shippingcode"] == 'HHTT' ? 'block' : 'none';?>">
          <label>快递网点标识</label>
          <span class="input">
          <input name="sendsite" value="<?=$item["sendsite"]?>" type="text" class="form_input" size="40" maxlength="60" notnull>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" value="提交保存" name="submit_btn">
		  <input type="hidden" name="itemid" value="<?php echo $itemid;?>">
          </span>
          <div class="clear"></div>
        </div>        
      </form>
    </div>
  </div>
</div>
</body>
</html>