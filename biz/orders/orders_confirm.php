<?php
require_once('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');

$OrderID = $request->input('OrderID', 0);
$offline = $request->input('offline', 0);
$rsOrder = $capsule->table('user_order')->where(['Users_ID' => $rsBiz["Users_ID"], 'Order_ID' => $OrderID, 'Biz_ID' => $BizID])->first();
if(empty($rsOrder)){
	alert("订单不存在", "orders.php");
}

if($rsOrder["Order_Status"] != 0 && $offline != 1){
	alert("只有状态为“待确认”的订单才可确认订单");
}

if($_POST){
	$flagReceive = true;
	$IsVirtual = $request->input('IsVirtual');
	$IsRecieve = $request->input('IsRecieve');
	if (!$offline) {	
		$Data = [
			"Address_Name" => $request->input('Name'),
			"Address_Mobile" => $request->input('Mobile'),
			"Order_Remark" => $request->input('Remark'),
			"Order_Status" => 1
		];
	} else if($IsVirtual == 1 && $IsRecieve ==0){
        $Data = [
            "Order_Status"=>2
        ];       
    }else if($IsVirtual == 1 && $IsRecieve==1){
        $Data = [
            "Order_Status"=>4
        ];
        Order::observe(new OrderObserver());
        $order = Order::find($OrderID);
        $flagReceive = $order->confirmReceive();
    }else if($IsVirtual == 0){
        $Data = [
            "Order_Status"=>2
        ];        
    }else{
		$Data = [
            "Order_Status"=>1
        ]; 
	}
	$flag_order = $capsule->table('user_order')->where(['Order_ID' => $OrderID])->update($Data);		
	if($flagReceive && $flag_order){
		alert("订单已确认", "orders.php");
	}else{
		alert("订单已确认", "orders.php");
	}
}

$rsConfig = shop_config($rsBiz["Users_ID"]);
$Status = $rsOrder["Order_Status"];
$Order_Status = ["待确认","待付款","已付款","已发货","已完成","申请退款中"];
$Shipping=json_decode(htmlspecialchars_decode($rsOrder["Order_Shipping"]),true);
$CartList=json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]),true);
$PaymentInfo = json_decode($rsOrder["Order_PaymentInfo"], true);
$amount = $fee = 0;

//获取区域地址信息并解析

if (!empty($rsOrder['Address_Province']) && !empty($rsOrder['Address_City']) && !empty($rsOrder['Address_Area'])) {
  $address = getArea($rsOrder);

  $Province = $address['Province'];
  $City = $address['City'];
  $Area = $address['Area'];
  $Address_Detailed = $address['Address'];
}

$jcurid = 1;
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
    <script type='text/javascript' src='/biz/js/shop.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/biz/orders/orders_menubar.php');?>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/pcas/pcas.js'></script> 
    <script language="javascript">$(document).ready(shop_obj.orders_send);</script>
    <div id="orders" class="r_con_wrap">
      <div class="detail_card">
        <form id="order_send_form" method="post" action="?">
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="order_info">
            <tr>
              <td width="8%" nowrap>订单编号：</td>
              <td width="92%"><?php echo date("Ymd",$rsOrder["Order_CreateTime"]).$rsOrder["Order_ID"] ?></td>
            </tr>
			<tr>
              <td nowrap>物流费用：</td>
              <td>
			  <?php if(!isset($Shipping) || empty($Shipping["Price"])){?>
				免运费 
			  <?php }else{
				$fee = $Shipping["Price"];
			  ?>
				 ￥<?php echo $Shipping["Price"];?>
			  <?php }?>
              </td>
            </tr>
            <tr>
              <td nowrap>订单总价：</td>
              <td>￥<?php echo $rsOrder["Order_TotalPrice"] ?></td>
            </tr>
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
            <tr>
              <td nowrap>订单状态：</td>
              <td><?php echo $Order_Status[$Status];?></td>
            </tr>
            <tr>
              <td nowrap>支付方式：</td>
              <td><?php echo empty($rsOrder["Order_PaymentMethod"]) || $rsOrder["Order_PaymentMethod"]=="0" ? "暂无" : $rsOrder["Order_PaymentMethod"]; ?></td>
            </tr>
            <?php if($rsOrder["Order_PaymentMethod"]=="线下支付"){?>
            <tr>
              <td nowrap>付款信息：</td>
               <td>转账方式：<?php echo $PaymentInfo[0]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				转账时间：<?php echo $PaymentInfo[1]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				转出账号：<?php echo $PaymentInfo[2]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				姓名：<?php echo $PaymentInfo[3]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				联系方式：<?php echo $PaymentInfo[4]?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			  </td>
            </tr>
            <?php }?>
            <tr>
              <td nowrap>联系人：</td>
              <td><input name="Name" value="<?php echo $rsOrder["Address_Name"] ?>" size="10" notnull /></td>
            </tr>
            <tr>
              <td nowrap>手机号码：</td>
              <td><input name="Mobile" value="<?php echo $rsOrder["Address_Mobile"] ?>" size="15" notnull /></td>
            </tr>
            <tr>
              <td nowrap>配送方式：</td>
              <td><?php echo isset($Shipping) && isset($Shipping["Express"])?$Shipping["Express"]:"" ?><?php echo $rsOrder["Order_ShippingID"] ? "：".$rsOrder["Order_ShippingID"] : ""; ?></td>
            </tr>
            <? if (!empty($address)) { ?>
            <tr>
              <td nowrap>地址信息：</td>
              <td><?php echo $Province.$City.$Area.'【'.$rsOrder["Address_Name"].'，'.$rsOrder["Address_Mobile"].'】'.'&nbsp;&nbsp;&nbsp;&nbsp;详细地址: '.$Address_Detailed ?></td>
            </tr>
            <? } ?>
			<tr>
              <td nowrap>是否需要发票：</td>
              <td><?php echo $rsOrder["Order_NeedInvoice"]==1 ? '<font style="color:#F60">是</font>': "否" ?></td>
            </tr>
			<?php if($rsOrder["Order_NeedInvoice"]==1){ ?>
			<tr>
              <td nowrap>发票抬头：</td>
              <td><?php echo $rsOrder["Order_InvoiceInfo"];?></td>
            </tr>
			<?php }?>
            <tr>
              <td nowrap>订单备注：</td>
              <td><textarea name="Remark" rows="5" cols="50"><?php echo $rsOrder["Order_Remark"] ?></textarea></td>
            </tr>
            <tr>
              <td></td>
              <td><input type="submit" class="btn_green" name="submit_button" value="确定" /></td>
            </tr>
          </table>
          <input type="hidden" name="OrderID" value="<?=$rsOrder["Order_ID"]?>" />
          <input type="hidden" name="offline" value="<?=$offline?>" />
            <input type="hidden" name="IsVirtual" value="<?=$rsOrder["Order_IsVirtual"]?>" />
            <input type="hidden" name="IsRecieve" value="<?=$rsOrder["Order_IsRecieve"]?>" />
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
			if ($rsOrder['Order_Type'] == 'kanjia') {
				if(!empty($CartList)){
					foreach($CartList as $biz=>$bizvalue){
						foreach($bizvalue as $pro=>$provalue){
							foreach($provalue as $k=>$v){
								if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $v["ImgPath"]) || empty($v["ImgPath"])){
									$v["ImgPath"] = "/static/images/none.png";
								}
								$total+=$v["Qty"]*$v["Cur_Price"];
								$qty+=$v["Qty"];
								echo '<tr class="item_list" align="center">
									<td valign="top"><img src="'.$v["ImgPath"].'" width="100" height="100" /></td>
									<td align="left" class="flh_180">'.$v["ProductsName"].'<br>';
								
								echo '</td>
									<td>￥'.$v["Cur_Price"].'</td>
									<td>'.$v["Qty"].'</td>
									<td>￥'.$v["Cur_Price"]*$v["Qty"].'</td>
								</tr>';
							}
						}
					}
				}
			} else {
				if(!empty($CartList)){
					foreach($CartList as $key=>$value){
						foreach($value as $k=>$v){
							if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $v["ImgPath"]) || empty($v["ImgPath"])){
								$v["ImgPath"] = "/static/images/none.png";
							}
							if (count($v["Property"]) > 0) {
								$pricesimp = $v["Property"]["shu_pricesimp"];
								$arrval = $v["Property"]["shu_value"];
							}else{
								$pricesimp = $v["ProductsPriceX"];
								$arrval = '';
							}
							$total+=$v["Qty"]*$pricesimp;
							$qty+=$v["Qty"];
							echo '<tr class="item_list" align="center">
								<td valign="top"><img src="'.$v["ImgPath"].'" width="100" height="100" /></td>
								<td align="left" class="flh_180">'.$v["ProductsName"].'<br><dd>'.$arrval.'</dd></td><td>￥'.$pricesimp.'</td>	
							   <td>'.$v["Qty"].'</td>
								<td>￥'.$pricesimp*$v["Qty"].'</td>
							  </tr>';
						}
					}
				}
			}
			?>
          <tr class="total">
            <td colspan="3">&nbsp;</td>
            <td><?php echo $qty ?></td>
            <td>￥<?php echo $total ?></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>