<?php
require_once('../global.php');
$Biz_ID=$_SESSION["BIZ_ID"];  
$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and biz_id = ".$Biz_ID;



$condition .= " order by addtime desc";

$_Status = array(0=>'<font style="color:#ff0000">未付款</font>',1=>'<font style="color:blue">已付款</font>');
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
        <li><a href="../authen/charge_man.php">年费</a></li>
        <li class=""><a href="../authen/charge_bond.php">保证金</a></li>
	<li class="cur"><a href="../authen/pay_record.php">支付记录</a></li>  
      </ul>
    </div>
	
    <div id="bizs" class="r_con_wrap">
       
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>

            <td width="6%" nowrap="nowrap">ID</td>
            
            <td width="8%" nowrap="nowrap">订单类型</td>
            <td width="8%" nowrap="nowrap">保证金</td>
            <td width="8%" nowrap="nowrap">开通年限</td>
            <td width="8%" nowrap="nowrap">年费</td>
            <td width="8%" nowrap="nowrap">总额</td>
            <td width="8%" nowrap="nowrap">状态</td>
            <td width="8%" nowrap="nowrap">支付方式</td>
            <td width="13%" nowrap="nowrap">提交时间</td>
            <td width="10%" nowrap="nowrap" class="last">支付时间</td>
          </tr>
        </thead>
        <tbody>
        <?php 
		  $lists = array();
		  $DB->getPage("biz_pay","*",$condition,10);
		  
		  while($r=$DB->fetch_assoc()){
			  $lists[] = $r;
		  }
		  foreach($lists as $k=>$rsBiz){
		?>
              
          <tr>
            <td nowrap="nowrap"><?php echo $rsBiz["id"];?></td>
            
            
           
            <td><?php if($rsBiz['type']==1){echo '入驻订单';}elseif($rsBiz['type']==2){echo'年费订单';}elseif($rsBiz['type']==3){echo'保证金订单';}?></td>   
            <td nowrap="nowrap"><?php echo ($rsBiz["bond_free"] != '0.00')?$rsBiz["bond_free"]:''?></td>
            <td nowrap="nowrap"><?php echo !empty($rsBiz["years"])?$rsBiz["years"].'年':''; ?></td>
            <td nowrap="nowrap"><?php echo ($rsBiz["year_free"] != '0.00')?$rsBiz["year_free"]:''; ?></td>
            <td nowrap="nowrap"><?php echo $rsBiz["total_money"]; ?></td>
            <td nowrap="nowrap"><?php echo $_Status[$rsBiz["status"]]; ?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsBiz["addtime"]) ?></td>
            <td nowrap="nowrap"><?php echo $rsBiz["order_paymentmethod"]; ?></td>
            <td class="last" nowrap="nowrap">
                <?php echo !empty($rsBiz["paytime"])?date("Y-m-d H:i:s",$rsBiz["paytime"]):'' ?>
            </td>

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