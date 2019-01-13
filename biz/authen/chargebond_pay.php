<?php
require_once('../global.php');
$UsersID =  $rsBiz['Users_ID'];
$rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");

$OrderID = $_GET['payid'];
$rsOrder = $DB->GetRs("biz_pay","*","where Users_ID='".$UsersID."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and id='".$OrderID."'");
//print_r($rsOrder);
?>  
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>供货商-管理后台</title>
</head>
    <link rel="stylesheet" href="/static/biz/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="/static/biz/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="/static/biz/css/style.min.css" type="text/css">
    <link rel="stylesheet" href="/static/biz/css/fukuan.css" type="text/css">
<body>
<style>
*{ margin:0; padding:0;}
h1{ background:red; font-size:14px; height:35px; line-height:35px; padding-left:20px; color:white;}
#Methods2{ width:95% auto;}
#Methods2 dl{width:100%; height:60px; border:1px #ccc solid; text-align:center; line-height:60px; }
#Methods2 dt{width:10%; height:60px; float:left; background:#FFF;}
#Methods2 dd{width:60%; height:60px; float:left;padding:10px 20px; line-height:20px; background:#FFFFFF; text-align:left;}

</style>
<section class="vbox">
    <header class="header bg-white b-b b-light">
        <p>追加保证金</p>
    </header>
    <section class="scrollable  wrapper">
    	<section class="panel panel-default">
            <div class="year" style="height: 200px;">
                <div class="title" style="margin-bottom: 10px">选择支付方式</div>
                <div style="margin-bottom: 30px">订单金额：<span><?php echo $rsOrder["total_money"].'元' ?></span></div>
                <form id="payment_form" action="/biz/authen/ajax.php">
                  
           
                                     
                            <ul id="dowebok">
                                    <?php if(!empty($rsPay["PaymentWxpayEnabled"])){ ?>
                                    <!--<li style="float:left;"> <a href="javascript:void(0)" class="btn btn-default btn-pay direct_pay" id="wzf" data-value="微支付"><img  src="/static/api/shop/skin/default/wechat_logo.jpg" width="16px" height="16px"/>&nbsp;微信支付</a> </li>-->
                                    <?php }?>
                                    <?php if(!empty($rsPay["Payment_AlipayEnabled"])){?>
                                    <li style="float:left;margin-left: 20px"> <a href="javascript:void(0)" class="btn btn-danger btn-pay direct_pay" id="zfb" data-value="支付宝"><img  src="/static/api/shop/skin/default/alipay.png" width="16px" height="16px"/>&nbsp;支付宝支付</a> </li>
                                     
                                    <?php }else{
										echo '<li style="float:left;">商城未配置支付宝接口</li>';
									}?>


                            </ul>
                    
                            <input type="hidden" name="PaymentMethod" id="PaymentMethod_val" value="微支付"/>
                            <input type="hidden" name="OrderID" value="<?php echo $OrderID;?>" />
                            <input type="hidden" name="total_price" value="<?php echo $rsOrder["total_money"] ?>" />
                            <input type="hidden" name="action" value="chargebond_payment" />
                       
                </form> 
            
            
            </div>
            
        </section>
    </section>
     
</section>
<link rel="stylesheet" href="/static/biz/css/jquery-labelauty.css">
<script src="/static/biz/js/jquery-1.8.3.min.js"></script>
<script src="/static/biz/js/jquery-labelauty.js"></script>
<script>
    $("a.direct_pay").click(function() {

            var PaymentMethod = $(this).attr("data-value");
            var PaymentID = $(this).attr("id");
            $("#PaymentMethod_val").attr("value", PaymentMethod);

		$.ajax({
			type:'post',
			url:$('#payment_form').attr('action'),
			data:$('#payment_form').serialize(),
			 
			success:function(data) {
				$('#payment_form .payment input').attr('disabled', false);
				if (data.status == 1) {
					window.location = data.url;
				} else {
					alert(data.msg);
				}
			},
			 
			dataType:'json',
		});

        });
            
                
  
</script>
<script>
 

$(function(){
	$(':input').labelauty();
});
</script>
</body>
</html>
