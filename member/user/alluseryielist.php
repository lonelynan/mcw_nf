<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

require_once($_SERVER["DOCUMENT_ROOT"].'/member/user/useryielist.php');
//计算当前所有会员收益
$rsConfig=$DB->GetRs("user_config","IsPro,ProRstart,ProStartime,ProIntegral","where Users_ID='".$_SESSION["Users_ID"]."'");
if($rsConfig['IsPro'] == 1){
$Proman = new useryielist($DB,$_SESSION["Users_ID"],$rsConfig['ProRstart']);
$timecai = $Proman->timediffrence($rsConfig['ProStartime'],strtotime('today'));
if($timecai != 'error'){
		$add = $Proman->addpro($timecai,$rsConfig['ProIntegral']);
		$Data=array(		
		"ProStartime"=>strtotime('tomorrow')
	);
		$DB->Set("user_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	}
	}
$jcurid = 8;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>收益明细</title>
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
    <link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/user.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/user/user_menubar.php');?>    
    <div id="user_message" class="r_con_wrap">      
      <table width="100%" align="center" cellpadding="0" cellspacing="0" border="0" class="r_con_table">
        <thead>
          <tr>
            <td width="10%"><strong>序号</strong></td>            
			 <td width="20%"><strong>会员</strong></td>
			  <td width="20%"><strong>收益日期</strong></td>
			   <td width="20%"><strong>当前余额</strong></td>
            <td width="10%"><strong>当前收益率</strong></td>
            <td width="20%" class="last"><strong>收益额</strong></td>
          </tr>
        </thead>
        <tbody>
          <?php $DB->getPage("user_yielist","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Procee_Date desc",$pageSize=10);
		  $i=1;
		  while($rsYielist=$DB->fetch_assoc()){?>
          <tr>
            <td nowrap="nowrap"><?php echo $pageSize*($DB->pageNo-1)+$i; ?></td>
            <td><?php echo $rsYielist["User_ID"] ?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d",$rsYielist["Procee_Date"]) ?></td>
            <td nowrap="nowrap"><?php echo $rsYielist["Remainder_Mon"] ?></td>
			 <td nowrap="nowrap"><?php echo $rsYielist["Yield_Rate"] ?></td>
			  <td nowrap="nowrap"><?php echo $rsYielist["Procee_Mon"] ?></td>
          </tr>
          <?php $i++;
		  }?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
</div>
</body>
</html>