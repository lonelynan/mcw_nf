<?php
require_once('../global.php');
$Biz_ID=$_SESSION["BIZ_ID"];
$BizPayInfo = $DB->GetRS('biz_pay','*','WHERE type=1 and biz_id = '.$Biz_ID); 

   
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>


<link rel="stylesheet" href="/static/biz/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="/static/biz/css/style.min.css" type="text/css">
<link rel="stylesheet" href="/static/biz/css/agreement.min.css" type="text/css">
<style type="text/css">
#bizs .search{padding:10px; background:#f7f7f7; border:1px solid #ddd; margin-bottom:8px; font-size:12px;}
#bizs .search *{font-size:12px;}
#bizs .search .search_btn{background:#1584D5; color:white; border:none; height:22px; line-height:22px; width:50px;}
</style>
 <link href='/static/member/css/audit.css' rel='stylesheet' type='text/css' />
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<section class="vbox">
    <header class="header bg-white b-b b-light">
        <p>入驻支付信息</p>
    </header>
    <div class="panel panel-default">
   
            <div class="panel-body">    
        
    
<div id="iframe_page">
  <div class="iframe_content"> 
     <div class="w">
	<div class="main_xx">
    	<p>订单信息</p>
        
        

        <div class="group ">
        	<span>保证金：</span><span><?php echo !empty($BizPayInfo['bond_free'])?$BizPayInfo['bond_free'].'元':''?></span>
        </div>
        <div class="group ">
        	<span>开通年限：</span><span><?php echo !empty($BizPayInfo['years'])?$BizPayInfo['years'].'年':''?></span>
        </div>
        <div class="group ">
        	<span>平台使用费：</span><span><?php echo !empty($BizPayInfo['year_free'])?$BizPayInfo['year_free'].'元':''?></span>
        </div>
        <div class="group ">
        	<span>总额：</span><span><?php echo !empty($BizPayInfo['total_money'])?$BizPayInfo['total_money']:''?></span>
        </div>
        <div class="group ">
        	<span>支付方式：</span><span><?php echo !empty($BizPayInfo['order_paymentmethod'])?$BizPayInfo['order_paymentmethod']:''?></span>
        </div>
        <div class="group ">
        	<span>支付时间：</span><span><?php echo !empty($BizPayInfo['paytime'])?date('Y-m-d',$BizPayInfo['paytime']):'';?></span>
        </div>
         
    </div>
     


    <div style="margin-bottom:30px;">
        <!--<button class="btn_x1">驳回 </button>-->
        <a style="cursor:hand;" href="index.php"><button class="btn_xx" href="javascript:void(0)">返回</button></a>
       
    </div>
</div> 
    
  </div>
</div>
        
        
         </div>
        </div>
</section>        
    <script>
    $("#btn").click(function(){
        var apply_id = $("#btn").attr('item');
        $.get('?',{apply_id:apply_id,action:'read'},function(data){
            alert(data.info);
            if (data.status == 1) {
                window.location = 'apply.php';
            }
        }, 'json')
    })
    </script>    
</body>
</html>