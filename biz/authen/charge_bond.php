<?php
require_once('../global.php');
 

$item = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
$item["bond_desc"] = str_replace('&quot;','"',$item["bond_desc"]);
$item["bond_desc"] = str_replace("&quot;","'",$item["bond_desc"]);
$item["bond_desc"] = str_replace('&gt;','>',$item["bond_desc"]);
$item["bond_desc"] = str_replace('&lt;','<',$item["bond_desc"]);



$bizConfig = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
$year_list = json_decode($bizConfig['year_fee'],true);

$bizInfo = $DB->GetRs("biz","*","where Users_ID='".$rsBiz['Users_ID']."' and Biz_ID=".$_SESSION["BIZ_ID"]);
if ($_POST) { 
   if (isset($_POST['bond_money'])) { 
        $bond_money = max($_POST['bond_money']); //所选的保证金
        $bond_free = !empty($bizInfo['bond_free'])?$bizInfo['bond_free']:'0'; //已交
        $bond_need = $bond_money - $bond_free; //保证金所需金额
        if (!isset($bond_need) || $bond_need <0 ) {
            $bond_need = 0;
        }
        echo $bond_need;
        exit;
   }
   exit;
}

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
        <li><a href="../authen/charge_man.php">年费</a></li>
        <li class="cur"><a href="../authen/charge_bond.php">保证金</a></li>
	<li><a href="../authen/pay_record.php">支付记录</a></li>  
      </ul>
    </div>
    <div id="orders" class="r_con_wrap" style="width:75%;">
        
<section class="vbox">
    <header class="header bg-white b-b b-light">
        <p>追加保证金</p>
    </header>
    <section class="scrollable  wrapper">
    	<section class="panel panel-default">
            <div class="fukuan">您当前保证金：<?php echo !empty($bizInfo['bond_free'])?$bizInfo['bond_free']:'0'?>
			<?php echo !empty($bizInfo['bond_free'])?'<a style="text-decoration:none;" href="../authen/back_apply.php"><span style="margin-left:30px;color:red">申请退款</span></a>':'';?>
            </div>
            
            <div class="fukuan">
                <?php echo $item['bond_desc']?>
            </div>
            
            <form id="checkout_bond" method="post" action="ajax.php">
            <input type="hidden" name="action" value="checkout_bond" />
            
            <table class="tab_x">
            	<thead>
                	<tr>
                        <td width="80%" class="liebiao">可选择分类</td>
                        <td class="liebiao" style=" width:20% ;background-color:#1fbba6; color:#fff">请选择保证金</td>
                    </tr>
                </thead>
                <tbody>
                    <?php //$DB->get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID!=0 group by  Category_Bond");
                    $DB->get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID!=0 order by  Category_Bond");
                    
                    while($rsPCategory=$DB->fetch_assoc()){
                        $Category_Bond = $rsPCategory['Category_Bond'];
                        $ParentMenu["$Category_Bond"][]=$rsPCategory;
                         
                    } 
                    if (isset($ParentMenu) && !empty($ParentMenu)) {
					
						foreach($ParentMenu as $key=>$value){?>
							<tr>
								<td>
							  <?php foreach($value as $ks=>$vs){  
								echo $vs['Category_Name'].'&nbsp;';
							  }?>
								</td>
							   
								<td><input type="checkbox" class='bond' name="bond_money[]" value="<?php echo $vs['Category_Bond']?>" data-labelauty="<?php echo $vs['Category_Bond']?>" /></td>
							</tr>

						<?php 
						}  
					}	?>
                    
                     
                </tbody>
            </table>
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
    $('#checkout_bond #submit-btn').click(function() {
            

            //$(this).attr('disabled', true);

            var param = $('#checkout_bond').serialize();
            var url = $('#checkout_bond').attr('action');

            $.post(url, param, function(data) {
                if (data.status == 1) {
                    window.location = data.url;
                }else {
                    alert(data.msg);
		}
            }, 'json');
    });
    $(".bond").click(function(){
        var param = $('#checkout_bond').serialize();
        $.post('?', param, function(data) {
                $("#totmoney").html(data+'元');
            });
        
    })
$(function(){
	$(':input').labelauty();
});
</script>
</body>
</html>
