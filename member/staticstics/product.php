<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
//$DB->Get("user","count(*) as num,User_Province","where Users_ID='".$_SESSION["Users_ID"]."' group by User_Province order by num desc");
//$totalProduct = $DB->GetRs('shop_products','count(*) as num',"where Users_ID='".$_SESSION['Users_ID']."'");

//商品评价评分
$condition = "where Users_ID = '".$_SESSION['Users_ID']."' group by  Product_ID";
$Commit = $DB->Get("user_order_commit","AVG(Score) as avgcore,Product_ID",$condition);
while($rsCommit=$DB->fetch_assoc($Commit)){
    $rsCommit_array[] = $rsCommit;
}
if(isset($rsCommit_array)){
    foreach ($rsCommit_array as $k=>$v){
            $newCommit[$v['Product_ID']] = $v['avgcore'];
    }
}

//商品销量
$UsersID = $_SESSION['Users_ID'];
$condition = 'where Users_ID = '."'$UsersID '".' and order_status = 4';
$arrayOrder = $DB->Get('user_order','Order_CartList',$condition);
while($resultOrder=$DB->fetch_assoc($arrayOrder)){
   $resultOrder_array[]=json_decode($resultOrder['Order_CartList'],true);
}
if(!empty($resultOrder_array)){
    foreach($resultOrder_array as $k => $v){
        foreach ($v as $ks=>$vs){
            foreach($vs as $ks1=>$vs1){
                $product_sale[$ks][] = $vs1['ProductsPriceX']*$vs1['Qty'];
            }
        }
    } 
    if(!empty($product_sale)){
        foreach($product_sale as $k =>$v){
            $product_sales[$k] = array_sum($v);
        }
    }
}

//商品退货
$UsersID = $_SESSION['Users_ID'];
$conditionBack = 'where Users_ID = '."'$UsersID '".' and Back_Status = 4';
$arrayBack = $DB->Get('user_back_order','Back_Qty,ProductID',$conditionBack);
while($resultBack=$DB->fetch_assoc($arrayBack)){
   $resultBack_array[]=$resultBack;
}
if(!empty($resultBack_array)){
    foreach($resultBack_array as $k => $v){
        $ProductIDBack = $v['ProductID'];
        $product_back[$ProductIDBack][] = $v['Back_Qty'];
    }
    if(!empty($product_back)){
        foreach($product_back as $k =>$v){
            $product_backs[$k] = array_sum($v);
        }
    }
}
/* $DB->get("shop_products","Products_ID,Users_ID,Products_Name,Products_Count,Products_Sales","$condition");

    while($r=$DB->fetch_assoc()){
        $rel[] =  $r;  
    } print_r($rel);
foreach ($rel as $k=>$v){
    $rel[$k]['score'] = get_comment_aggregate($DB,$v['Users_ID'],$v['Products_ID']);
    
} */     
        
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
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
    <div class="iframe_content">
        <div class="r_nav">
            <ul>
                <li class="cur"><a href="product.php">产品统计</a></li>
            </ul>
	</div>
        <div id="products" class="r_con_wrap"> 
        <script type='text/javascript' src='/static/member/js/statistics.js' ></script>
        <link href='/static/member/css/statistics.css' rel='stylesheet' type='text/css' />
        <div class="control_btn">
            <a href="../shop/output.php?type=product_staticstic_list" class="btn_green btn_w_120">导出产品</a>
        </div>    
<table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="5%" nowrap="nowrap">序号</td>
            <td width="8%" nowrap="nowrap">名称</td>
            <td width="10%" nowrap="nowrap">库存</td>
            <td width="8%" nowrap="nowrap">销量</td>
            <td width="12%" nowrap="nowrap">销售额</td>
            <td width="10%" nowrap="nowrap">退货</td>
            <td width="7%" nowrap="nowrap">点击数</td>
            <td width="8%" nowrap="nowrap">评分</td>
          </tr>
        </thead>
        <tbody>
          <?php $condition = "where Users_ID='".$_SESSION["Users_ID"]."'"; 
          $DB->getPage("shop_products","Products_ID,Users_ID,Products_Name,Products_Count,Products_Sales",$condition,$pageSize=10);
		 
		while($rsUser=$DB->fetch_assoc()){
                    $Products_ID = $rsUser['Products_ID']; 
          ?>
          <tr>
            <td nowrap="nowrap"><?php echo $rsUser['Products_ID'] ?></td>
            <td nowrap="nowrap"><?php echo $rsUser['Products_Name'] ?></td>
             <td nowrap="nowrap"><?php echo $rsUser['Products_Count'] ?></td>
            <td nowrap="nowrap"><?php echo $rsUser['Products_Sales'] ?></td>
            <td nowrap="nowrap">&yen;&nbsp;<?php echo isset($product_sales[$Products_ID])?"$product_sales[$Products_ID]":'0';?></td>
            <td nowrap="nowrap"><?php echo isset($product_backs[$Products_ID])?"$product_backs[$Products_ID]":'0';?></td>
            <td nowrap="nowrap"><?php echo !empty($rsUser['click_count'])?$rsUser['click_count']:'0' ?></td>
            <td nowrap="nowrap"><?php $Products_ID = $rsUser['Products_ID']; echo !empty($newCommit["$Products_ID"])?intval($newCommit["$Products_ID"]):'无评分';?></td>
          </tr>
          <?php  
		  }?>
        </tbody>
      </table>
     <div class="blank20">
    </div>
      <?php $DB->showPage(); ?>
  </div>
</div></div>
</body>
</html>