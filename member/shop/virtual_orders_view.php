?><?php
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
$OrderID=empty($_REQUEST['OrderID'])?0:$_REQUEST['OrderID'];
$rsOrder=$DB->GetRs("user_order","*","where Users_ID='".$_SESSION["Users_ID"]."' and Order_ID='".$OrderID."'");
if($_POST){
	if($rsOrder["Order_Status"]<2){
		$Data = array(
			"Order_TotalPrice"=>empty($_POST["TotalPrice"]) ? 0 : $_POST["TotalPrice"],
			"Order_Status"=>$_POST["Status"]
		);
		$Flag = $DB->Set("user_order",$Data,"where Order_ID=".$OrderID);
		if($Flag){
			echo '<script language="javascript">alert("修改成功");window.location.href="virtual_orders_view.php?OrderID='.$OrderID.'";</script>';
		}else{
			echo '<script language="javascript">alert("修改失败");history.back();</script>';
		}
		exit;
	}
}

$rsConfig=$DB->GetRs("shop_config","ShopName,NeedShipping","where Users_ID='".$_SESSION["Users_ID"]."'");

$Status=$rsOrder["Order_Status"];
$Order_Status=array("待确认","待付款","已付款","已发货","已完成");
$CartList=json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]),true);
$amount = $fee = 0;

$lists_back = array();
if($rsOrder["Is_Backup"]==1){
	$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Order_ID=".$OrderID." and Back_Type='shop'";
	$DB->Get("user_back_order","*",$condition);
	while($b=$DB->fetch_assoc()){
		$lists_back[] = $b;
	}
}
$_STATUS = array('<font style="color:#F00; font-size:12px;">申请中</font>','<font style="color:#F60; font-size:12px;">卖家同意</font>','<font style="color:#0F3; font-size:12px;">买家发货</font>','<font style="color:#600; font-size:12px;">卖家收货并确定退款价格</font>','<font style="color:blue; font-size:12px;">完成</font>','<font style="color:#999; font-size:12px; text-decoration:line-through;">卖家拒绝退款</font>');
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

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
    <div class="r_nav">
      <ul>
        <li class="cur"><a href="orders.php">订单管理</a></li>
		<li><a href="virtual_orders.php">消费认证</a></li>
      </ul>
    </div>
    <div id="orders" class="r_con_wrap">
      <div class="control_btn">
      <a href="javascript:void(0);" class="btn_gray" onClick="history.go(-1);">返 回</a>
      </div>     
      <div class="detail_card">
        <form method="post" action="?OrderID=<?php echo $OrderID;?>">
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="order_info">
            <tr>
              <td width="8%" nowrap>订单编号：</td>
              <td width="92%"><?php echo date("Ymd",$rsOrder["Order_CreateTime"]).$rsOrder["Order_ID"] ?></td>
            </tr>
			
            <tr>
              <td nowrap>订单总价：</td>
              <td>￥<input type="text" size="10" name="TotalPrice" value="<?php echo $rsOrder["Order_TotalPrice"] ?>"<?php echo $rsOrder["Order_Status"]>=2 ? ' disabled="disabled"' : '';?> notnull><?php echo $rsOrder["Back_Amount"]>0 ? '&nbsp;&nbsp;<font style="text-decoration:line-through; color:#999">&nbsp;退款金额：￥'.$rsOrder["Back_Amount"].'&nbsp;</font>&nbsp;&nbsp;' : "";?></td>
            </tr>
			<?php if (!empty(floatval($rsOrder["Order_Yebc"]))) { ?>
			<tr>
              <td nowrap>使用余额：</td>
              <td>￥<input type="text" size="10" name="Order_Yebc" value="<?php echo $rsOrder["Order_Yebc"] ?>" disabled="disabled"></td>
            </tr>
			<tr>
              <td nowrap>实际支付：</td>
              <td>￥<input type="text" size="10" name="Order_Fyepay" value="<?php echo $rsOrder["Order_Fyepay"] ?>" disabled="disabled"></td>
            </tr>
			<?php } ?>
			<?php if($rsOrder["Coupon_ID"]>0){?>
			<tr>
			  <td nowrap>优惠详情</td>
			  <td><font style="color:blue;">已使用优惠券</font>(
				  <?php if($rsOrder["Coupon_Discount"]>0){?>
				  享受<?php echo $rsOrder["Coupon_Discount"]*10;?>折
				  <?php }?>
				  <?php if($rsOrder["Coupon_Cash"]>0){?>
				  抵现金<?php echo $rsOrder["Coupon_Cash"];?>元
				  <?php }?>)
			  </td>
			</tr>
			<?php }?>
            <tr>
              <td nowrap>订单时间：</td>
              <td><?php echo date("Y-m-d H:i:s",$rsOrder["Order_CreateTime"]) ?></td>
            </tr>
			<?php if (!empty($rsOrder["Pay_time"])) { ?>
			<tr>
              <td nowrap>付款时间：</td>
              <td><?php echo date("Y-m-d H:i:s",$rsOrder["Pay_time"]) ?></td>
            </tr>
			<?php } ?>
			<?php if (!empty($rsOrder["Finish_time"])) { ?>
			<tr>
              <td nowrap>完成时间：</td>
              <td><?php echo date("Y-m-d H:i:s",$rsOrder["Finish_time"]) ?></td>
            </tr>
			<?php } ?>
            <tr>
              <td nowrap>订单状态：</td>
              <td><select name="Status"<?php echo $rsOrder["Order_Status"]>=2 ? ' disabled="disabled"' : ''?>>
					<option value="0"<?php echo $rsOrder["Order_Status"]==0 ? ' selected' : ''?>>待确认</option>
					<option value="1"<?php echo $rsOrder["Order_Status"]==1 ? ' selected' : ''?>>待付款</option>
					<option value="2"<?php echo $rsOrder["Order_Status"]==2 ? ' selected' : ''?>>已付款</option>
					<option value="3"<?php echo $rsOrder["Order_Status"]==3 ? ' selected' : ''?>>已发货</option>
					<option value="4"<?php echo $rsOrder["Order_Status"]==4 ? ' selected' : ''?>>已完成</option>
				</select></td>
            </tr>
            <tr>
              <td nowrap>支付方式：</td>
              <td><?php echo empty($rsOrder["Order_PaymentMethod"]) || $rsOrder["Order_PaymentMethod"]=="0" ? "暂无" : $rsOrder["Order_PaymentMethod"]; ?></td>
            </tr>
            <tr>
              <td nowrap>手机号码：</td>
              <td><?php echo $rsOrder["Address_Mobile"] ?></td>
            </tr>
            
            <tr>
              <td nowrap>订单备注：</td>
              <td><?php echo $rsOrder["Order_Remark"] ?></td>
            </tr>
			<?php if($rsOrder["Order_Status"]<2){?>
			<tr>
              <td nowrap>&nbsp;</td>
              <td><input type="submit" name="submit" value="确定" /></td>
            </tr>
			<?php }?>
			
          </table>
          
        </form>
        <div class="blank12"></div>
        <div class="item_info">物品清单</div>
        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="order_item_list">
          <tr class="tb_title">
            <td width="20%">图片</td>
            <td width="35%">产品信息</td> 
            <td width="15%">价格</td>
            <td width="15%">数量</td>
            <td width="15%" class="last">小计</td>
          </tr>
          <?php $total=0;
$qty=0;
foreach($CartList as $key=>$value){
	foreach($value as $k=>$v){
        if($rsOrder['Order_Type']=='offline') {
            $price = $rsOrder['Order_TotalPrice'];
            $rsBiz = Biz::where("Biz_ID", $rsOrder['Biz_ID'])->first(["Biz_Name"])->toArray();
            echo '<tr class="item_list" align="center">
            <td valign="top"><img src="/static/member/images/xianxia.png" width="100" height="100" /></td>
            <td align="left" class="flh_180">线下扫码支付<br>';
            echo '</td>
            <td>￥'.$price.'</td>
            <td>'.$v["Qty"].'</td>
            <td>￥'.$price*$v["Qty"].'</td>
          </tr>';
        } else if($rsOrder['Order_Type']=='offline_st') {
            $price = $rsOrder['Order_TotalPrice'];
            $rsBiz = Biz::where("Biz_ID", $rsOrder['Biz_ID'])->first(["Biz_Name"])->toArray();
            echo '<tr class="item_list" align="center">
            <td valign="top"><img src="'.$v["ImgPath"].'" width="100" height="100" /></td>
            <td align="left" class="flh_180">线下实体买单<br>';
            echo '</td>
            <td>￥'.$price.'</td>
            <td>'.$v["Qty"].'</td>
            <td>￥'.$price*$v["Qty"].'</td>
          </tr>';
        }else {
            $total+=$v["Qty"]*$v["ProductsPriceX"];
            $qty+=$v["Qty"];
            echo '<tr class="item_list" align="center">
            <td valign="top"><img src="'.$v["ImgPath"].'" width="100" height="100" /></td>
            <td align="left" class="flh_180">'.$v["ProductsName"].'<br>';
            if(!empty($v["Property"])){
                $Attr = $v["Property"]['shu_value'];
                $ProductsPriceX = $v["Property"]['shu_pricesimp'];
            }else{
                $Attr = '';
                $ProductsPriceX = $v["ProductsPriceX"];
            }
            echo '<dd>'.$Attr.'</dd>';

            echo '</td>
            <td>￥'.$v["ProductsPriceX"].'</td>
            <td>'.$v["Qty"].'</td>
            <td>￥'.$v["ProductsPriceX"]*$v["Qty"].'</td>
          </tr>';
        }
	}
}?>
<?php
if(!empty($lists_back)){
	foreach($lists_back as $item){
		$CartList_back=json_decode(htmlspecialchars_decode($item["Back_Json"]),true);
		echo '<tr class="item_list" align="center">
            <td valign="top"><img src="'.$CartList_back["ImgPath"].'" width="100" height="100" /></td>
            <td align="left" class="flh_180">'.$CartList_back["ProductsName"].'<br>';
			if(!empty($CartList_back["Property"])){
						$Attr = $CartList_back["Property"]['shu_value'];
						$ProductsPriceX = $CartList_back["Property"]['shu_pricesimp'];
					}else{
						$Attr = '';
						$ProductsPriceX = $CartList_back["ProductsPriceX"];
					}
		echo '<dd>'.$Attr.'</dd>';
        echo '</td>
            <td>￥'.$CartList_back["ProductsPriceX"].'</td>
            <td>'.$CartList_back["Qty"].'</td>
            <td>￥'.$CartList_back["ProductsPriceX"]*$CartList_back["Qty"].'<br /><font style="text-decoration:line-through; font-size:12px; color:#999">&nbsp;退款金额：￥'.$item["Back_Amount"].'&nbsp;</font><br />'.$_STATUS[$item["Back_Status"]].'</td>
          </tr>';
	}
}?>

        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>