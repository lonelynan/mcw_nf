<?php
require_once('../global.php');
if ($rsBiz['is_agree'] !=1 || $rsBiz['is_auth'] !=2) {
   echo '<script language="javascript">alert("您还没完成审核不能付款");history.back();</script>';
   exit;
}
if ($rsBiz['is_pay'] == 1) {
   echo '<script language="javascript">alert("您已支付过商家入驻相关款项");history.back();</script>';
   exit;
}
$item = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
$item["NianFei"] = str_replace('&quot;','"',$item["NianFei"]);
$item["NianFei"] = str_replace("&quot;","'",$item["NianFei"]);
$item["NianFei"] = str_replace('&gt;','>',$item["NianFei"]);
$item["NianFei"] = str_replace('&lt;','<',$item["NianFei"]);



$bizConfig = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
$year_list = json_decode($bizConfig['year_fee'],true);

if ($_POST) {
    if (isset($_POST['bond_money'])) {
       $bond_money = max($_POST['bond_money']); //所选的保证金
    } else {
        $bond_money = 0;
    }
   

    if (isset($_POST['year_money'])) {
        $year_nums = trim($_POST['year_money']);
        $bizConfig = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
         if (!empty($bizConfig['year_fee'])) {
             $year_list = json_decode($bizConfig['year_fee'],true);
             $year_key = array_search($year_nums,$year_list['name']);
             $year_money = isset($year_list['value']["$year_key"])?$year_list['value']["$year_key"]:'0';  
         }
    } else {
        $year_money = 0;
    }
   $toamoney = $bond_money + $year_money;
   echo $toamoney;
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
        <p>付款</p>
    </header>
    <section class="scrollable  wrapper">
    	<section class="panel panel-default">
        	<div class="fukuan"><?php echo $item['NianFei']?>
            </div>
            <form id="checkout_form" method="post" action="ajax.php">
            <input type="hidden" name="action" value="checkout" />
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
							   
								<td><input type="checkbox" class='bond bonds' name="bond_money[]" value="<?php echo $vs['Category_Bond']?>" data-labelauty="<?php echo $vs['Category_Bond']?>" /></td>
							</tr>

						<?php }
					}
					?>
                    
                     
                </tbody>
            </table>
            <div class="year">
            	<div class="title">请选择年限</div>
                <div>
                	<ul class="dowebok">
                             <?php if(!empty($year_list['name'][0])):?>
                                <?php foreach($year_list['name'] as $key=>$year):?>
                             <li><input type="radio" name="year_money" value="<?=$year?>" class="year bonds" data-labelauty="<?=$year?>年"></li>
                                    
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
<link rel="stylesheet" href="/static/biz/css/jquery-labelauty.css">
<script src="/static/biz/js/jquery-1.8.3.min.js"></script>
<script src="/static/biz/js/jquery-labelauty.js"></script>
<script>
     $('#checkout_form #submit-btn').click(function() {
            

            //$(this).attr('disabled', true);

            var param = $('#checkout_form').serialize();
            var url = $('#checkout_form').attr('action');

            $.post(url, param, function(data) {
                if (data.status == 1) {
                    window.location = data.url;
                }else {
                    alert(data.msg);
		}
            }, 'json');
        });
        
        $(".bonds").click(function(){
            var param = $('#checkout_form').serialize();
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
