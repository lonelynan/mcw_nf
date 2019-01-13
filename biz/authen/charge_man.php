<?php
require_once('../global.php');
 

$item = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
$item["JieSuan"] = str_replace('&quot;','"',$item["JieSuan"]);
$item["JieSuan"] = str_replace("&quot;","'",$item["JieSuan"]);
$item["JieSuan"] = str_replace('&gt;','>',$item["JieSuan"]);
$item["JieSuan"] = str_replace('&lt;','<',$item["JieSuan"]);



$bizConfig = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
$year_list = json_decode($bizConfig['year_fee'],true);
$bizInfo = $DB->GetRs("biz","*","where Users_ID='".$rsBiz['Users_ID']."' and Biz_ID=".$_SESSION["BIZ_ID"]);//echo"<pre>";print_r($year_list);
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
    
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<body>
<style>
*{ margin:0; padding:0;}
h1{ background:red; font-size:14px; height:35px; line-height:35px; padding-left:20px; color:white;}
#Methods2{ width:95% auto;}
#Methods2 dl{width:100%; height:60px; border:1px #ccc solid; text-align:center; line-height:60px; }
#Methods2 dt{width:10%; height:60px; float:left; background:#FFF;}
#Methods2 dd{width:60%; height:60px; float:left;padding:10px 20px; line-height:20px; background:#FFFFFF; text-align:left;}

</style>

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css' />
    <div class="r_nav">
      <ul>
        <li class="cur"><a href="../authen/charge_man.php">年费</a></li>
        <li><a href="../authen/charge_bond.php">保证金</a></li>
	<li><a href="pay_record.php">支付记录</a></li> 
      </ul>
    </div>
    <div id="orders" class="r_con_wrap" style="width:75%;">
        
<section class="vbox">
    <header class="header bg-white b-b b-light">
        <p>支付年费</p>
    </header>
    <section class="scrollable  wrapper">
    	<section class="panel panel-default">
            <div class="fukuan">到期时间：<?php echo !empty($bizInfo['expiredate'])?date('Y-m-d',$rsBiz["expiredate"]):''?>
            </div>
            <div class="fukuan">
                <?php echo $item['JieSuan']?>
            </div>
            <form id="checkout_man" method="post" action="ajax.php">
            <input type="hidden" name="action" value="checkout_man" />
            
            <div class="year">
            	<div class="title">请选择年限</div>
                <div>
                	<ul class="dowebok">
                             <?php if(!empty($year_list['name'][0])):?>
                                <?php foreach($year_list['name'] as $key=>$year):?>
                            <li><input type="radio" name="year_money" value="<?=$year?>" class="year years" titile="<?=!empty($year_list['value'][$key])?$year_list['value'][$key]:'0'?>"  data-labelauty="<?=$year?>年"></li>
                                    
                                <?php endforeach; ?>
                            <?php endif;?>
                   </ul>

                </div>
            </div>
            <div class="fukuan" style="text-align: right">支付金额：<span id="totmoney"></span>
            </div>
            <div class="but_x">
                <button class="zhifu" id="submit-btn" type="button">立即支付</button>
            </div>
        </form>   
        </section>
    </section>
</section>

  <div class="clear"></div>
  </div>
</div>
<link rel="stylesheet" href="/static/biz/css/jquery-labelauty.css">
<script src="/static/biz/js/jquery-1.8.3.min.js"></script>
<script src="/static/biz/js/jquery-labelauty.js"></script>
<script>
     $('#checkout_man #submit-btn').click(function() {
            var param = $('#checkout_man').serialize();
            var url = $('#checkout_man').attr('action');

            $.post(url, param, function(data) {
                if (data.status == 1) {
                    window.location = data.url;
                }else {
                    alert(data.msg);
		}
            }, 'json');
        });
        
        $(".years").click(function(){
            var yearsmoney = $(this).attr('titile');
            $("#totmoney").html(yearsmoney+'元');
        })
       /* onclick="checktype(this)"
        * function checktype (t) {
           var a = $(t).attr("title");
         //   var a = $(t).val();
           alert(a);
            
        }*/

$(function(){
	$(':input').labelauty();
});
</script>
</body>
</html>
