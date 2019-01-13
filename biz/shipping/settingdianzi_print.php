<?php
require_once('../global.php');
$UsersID = $rsBiz['Users_ID'];

$action=empty($_REQUEST['action'])?'':$_REQUEST['action'];
if(!empty($action)){
	if($action=="del"){
		//删除
		$Flag=$DB->Del("users_express_info","usersid='".$UsersID."' and id=".$_GET["itemid"]);
		if($Flag){
			echo '<script language="javascript">alert("删除成功");window.location="settingdianzi_print.php";</script>';
		}else{
			echo '<script language="javascript">alert("删除失败");history.back();</script>';
		}
		exit;
	}
}else{
	$condition = "where Biz_ID=".$_SESSION["BIZ_ID"]." and usersid='".$UsersID."'";	
	$condition .= " order by id desc";
	$companys = $templates = array();
	$DB->Get("shop_shipping_company","Shipping_Code,Shipping_Name","where Biz_ID=0 and Users_ID='".$UsersID."'");
	while($r = $DB->fetch_assoc()){
		$companys[$r["Shipping_Code"]] = $r['Shipping_Name'];
	}
	
	$DB->getPage("users_express_info","id,shippingcode,cusname,cuspasswd",$condition,10);
	while($rs = $DB->fetch_assoc()){
		$templates[] = $rs;
	}
}

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
<style>
.leftf{float:left;padding-top:4px;}
        .form_input {
    height: 28px;
	width:200px;
    line-height: 28px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 5px;
    padding: 0 5px;
	float:left;
	overflow:hidden;
	padding:0px 8px 0px 8px;	
}
.martop{color:red}
a{color:blue;text-decoration:none; }
a:hover {color:#CC3300;text-decoration:underline;}
    </style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
    <div class="r_nav">
      <ul>
        <li><a href="config.php">运费设置</a></li>
        <li><a href="company.php">快递公司管理</a></li>
        <li><a href="template.php">快递模板</a></li>
		<li><a href="printtemplate.php">运单模板</a></li>
		<li class="cur"><a href="settingdianzi_print.php">电子面单管理</a></li>
      </ul>
    </div>
    <div class="r_con_wrap">
      <div class="control_btn">	  
      <a href="settingdianzi_printadd.php" class="btn_green btn_w_120">添加</a>
      </div>
      <br><br><br>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="5%" nowrap="nowrap">序号</td>
            <td width="15%" nowrap="nowrap">快递公司</td>
            <td width="15%" nowrap="nowrap">快递公司客户帐号</td>
            <td width="20%" nowrap="nowrap">快递公司客户密码</td>            
            <td width="15%" nowrap="nowrap" class="last"><strong>操作</strong></td>
          </tr>
        </thead>
        <tbody>
		<?php foreach($templates as $key=>$value):?>
           <tr>
            <td><?php echo $key+1;?></td>
            <td><?php echo isset($companys[$value["shippingcode"]]) ? $companys[$value["shippingcode"]] : '快递公司添加有误！';?></td>
            <td><?=$value["cusname"]?></td>
            <td><?=$value["cuspasswd"]?></td>
            <td nowrap="nowrap" class="last" style="line-height:22px;">            
            <a href="settingdianzi_printedit.php?itemid=<?php echo $value["id"];?>">[ 修改 ]</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="?action=del&itemid=<?php echo $value["id"];?>" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};">[ 删除 ]</a>
            </td>
          </tr>
      <?php endforeach; ?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
</div>
</body>
</html>