<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

if($_POST){
	$Flag = true;
	$r = $DB->GetRs("user_peas_orders","*","where Orders_ID=".$_POST['OrderID']."");
	if($r['Peas_ID']){
		$item = $DB->GetRs("user_peas","*","where Peas_ID=".$r['Peas_ID']."");
	}
	if(!$r['Orders_Status']){
		if($item['Peas_Shipping']>0){
			$Data = array(
				 'Orders_Shipping' => $_POST['Orders_Shipping'], 
				 'Orders_ShippingID' => $_POST['Orders_ShippingID'], 
				 'Orders_Status' => 1,
				 'Orders_FinishTime' =>time()
			);
		}else{
			$Data = array(
			     'Orders_Status' => 1,
				 'Orders_FinishTime' =>time()
			);
		}
		$Set=$DB->Set("user_peas_orders",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Orders_ID=".$_POST['OrderID']);
		$Flag=$Flag&&$Set;
		if($Flag){
			//echo "<script>alert('操作成功');document.location.href='?OrderId=".$_POST['OrderID']."&page=".$_POST['page']."';</script>";
			echo "<script>alert('操作成功');document.location.href='peas_orders.php';</script>";
		}else{
			echo "<script>alert('操作失败');document.location.href='?OrderId=".$_POST['OrderID']."&page=".$_POST['page']."';</script>";
		}
	}
	
}else{
	$peas_orders = $DB->GetRs("user_peas_orders","*","where Orders_ID=".$_GET['OrderId']."");
	//print_r($peas_orders);
	$peas = $DB->GetRs("user_peas","*","where Peas_ID=".$peas_orders['Peas_ID']."");
	if($peas_orders['Peas_ID'] && !empty($peas_orders['Address_Province'])){
		$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
		$area_array = json_decode($area_json,TRUE);
		$City = $area_array['0,'.$peas_orders['Address_Province']][$peas_orders['Address_City']];
		$Area = $area_array['0,'.$peas_orders['Address_Province'].','.$peas_orders['Address_City']][$peas_orders['Address_Area']];
	}
}
// echo '<pre>';
// print_R($peas_orders);
// exit;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>微易宝</title>
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
    <div class="r_nav">
      <ul>
        <li class="cur"> <a href="peas_orders.php">商品领取</a>
          <dl>
            <dd class="first"><a href="peas.php">商品管理</a></dd>
            <dd class=""><a href="peas_orders.php">领取订单管理</a></dd>
          </dl>
        </li>
      </ul>
    </div>
    <div id="peas_orders" class="r_con_wrap">
      <form id="orders_mod_form" method="post" action="peas_orders_view.php" class="r_con_form">
        <div class="rows">
          <label>商品产品</label>
          <span class="input"><span class="tips"><?php echo $peas['Peas_Name'];?></span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>商品图片</label>
          <span class="input"><img src="<?php echo $peas['Peas_ImgPath'];?>" width="300" /></span>
          <div class="clear"></div>
        </div>
        <?php if($peas['Peas_Shipping']){?>
        <div class="rows">
          <label>地址信息</label>
          <span class="input"><span class="tips"><?php echo $peas_orders['Address_Detailed'];?>【<?php echo $area_array[0][$peas_orders['Address_Province']];?>，<?php echo $City;?>，<?php echo $Area;?>】</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>联系方式</label>
          <span class="input"><span class="tips"><?php echo $peas_orders['Address_Name'];?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $peas_orders['Address_Mobile'];?></span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>发货状态</label>
          <span class="input">
          <?php if($peas_orders['Orders_Status']){?>
          <font style="color:blue">已发货</font>
          &nbsp;&nbsp;<span class="tips">发货时间：<?php echo date("Y-m-d H:i:s",$peas_orders["Orders_FinishTime"]); ?></span>
          <?php }else{?>
          <font style="color:red">未发货</font>
          <?php }?>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>配送方式</label>
          <span class="input">
          <?php if($peas_orders['Orders_Status']){?>
           <?php echo $peas_orders['Orders_Shipping'];?>
          <?php }else{?>
          <select name="Orders_Shipping">
            <option value='申通'>申通</option>
            <option value='中通' >中通</option>
          </select>
          <?php }?>
          &nbsp;&nbsp;<span class="tips">快递单号：</span>
          <input name="Orders_ShippingID" value="<?php echo $peas_orders['Orders_ShippingID'];?>" class="form_input" />
          </span>
          <div class="clear"></div>
        </div>
        <?php }else{?>
        <div class="rows">
          <label>领取状态</label>
          <span class="input">
          <?php if($peas_orders['Orders_Status']){?>
          <font style="color:blue">已领取</font>
          &nbsp;&nbsp;<span class="tips">领取时间：<?php echo date("Y-m-d H:i:s",$peas_orders["Orders_FinishTime"]); ?></span>
          <?php }else{?>
          <font style="color:red">未领取</font>
          <?php }?>
          </span>
          <div class="clear"></div>
        </div>
        <?php }?>
        <div class="rows">
          <label>领取时间</label>
          <span class="input"><span class="tips"><?php echo date("Y-m-d H:i:s",$peas_orders["Orders_CreateTime"]); ?></span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <?php if(!$peas_orders['Orders_Status']){?>
          <?php if($peas['Peas_Shipping']){?>
          <input type="submit" class="btn_green" name="submit_button" value="发货" />
          <?php }else{?>
          <input type="submit" class="btn_green" name="submit_button" value="领取" />
          <?php }?>
          <?php }?>
          <a href="javascript:history.go(-1)" class="btn_gray">返 回</a></span>
          <div class="clear"></div>
        </div>
        <input type="hidden" name="OrderID" value="<?php echo $_GET['OrderId'];?>" />
        <input type="hidden" name="page" value="<?php echo $_GET['page'];?>" />
      </form>
    </div>
  </div>
</div>
</body>
</html>