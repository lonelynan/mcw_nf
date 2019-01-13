<?php
require_once('../global.php');
$Biz_ID=$_SESSION["BIZ_ID"];  
$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and biz_id = ".$Biz_ID;



$condition .= " order by addtime desc";

$_Status = array(1=>'<font style="color:#ff0000">申请中</font>',2=>'<font style="color:blue">审核通过</font>',3=>'<font style="color:blue">已退款</font>',-1=>'<font style="color:blue">驳回</font>');
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
<style type="text/css">
#bizs .search{padding:10px; background:#f7f7f7; border:1px solid #ddd; margin-bottom:8px; font-size:12px;}
#bizs .search *{font-size:12px;}
#bizs .search .search_btn{background:#1584D5; color:white; border:none; height:22px; line-height:22px; width:50px;}
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <div class="r_nav">
      <ul>
        <li class=""><a href="back_apply.php">保证金退还</a></li>
		<li class="cur"><a href="back_record.php">保证金退款记录</a></li>	
      </ul>
    </div>
	
    <div id="bizs" class="r_con_wrap">
       
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>

            <td width="6%" nowrap="nowrap">ID</td>
            
            <td width="8%" nowrap="nowrap">类型</td>
            <td width="8%" nowrap="nowrap">商家</td>
            <td width="8%" nowrap="nowrap">支付宝姓名</td>
            <td width="8%" nowrap="nowrap">支付宝账号</td>
            <td width="8%" nowrap="nowrap">退还金额</td>
            <td width="8%" nowrap="nowrap">状态</td>
            <td width="13%" nowrap="nowrap">申请时间</td>
          </tr>
        </thead>
        <tbody>
        <?php 
		  $lists = array();
		  $DB->getPage("biz_bond_back","*",$condition,10);
		  
		  while($r=$DB->fetch_assoc()){
			  $lists[] = $r;
		  }
		  foreach($lists as $k=>$rsBack){
		?>
              
          <tr>
            <td nowrap="nowrap"><?php echo $rsBack["id"];?></td>
            
            
           
            <td>保证金退款</td>   
            <td nowrap="nowrap"><?php echo $rsBiz["Biz_Name"]?></td>
            <td nowrap="nowrap"><?php echo $rsBack["alipay_username"]; ?></td>
            <td nowrap="nowrap"><?php echo $rsBack["alipay_account"]; ?></td>
			<td nowrap="nowrap"><?php echo $rsBack["back_money"]; ?></td>
            <td nowrap="nowrap"><?php echo $_Status[$rsBack["status"]]; ?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsBack["addtime"]) ?></td>
          </tr>
          <?php }?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
     </div>
  </div>
</div>
</body>
</html>