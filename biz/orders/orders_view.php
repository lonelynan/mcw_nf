<?php
require_once('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');

$OrderID = $request->input('OrderID', 0);
$rsOrder = $capsule->table('user_order')->where(['Users_ID' => $rsBiz["Users_ID"], 'Order_ID' => $OrderID, 'Biz_ID' => $BizID])->first();
if(empty($rsOrder)){
	alert("订单不存在", "orders.php");
}

$rsConfig = shop_config($rsBiz["Users_ID"]);
$Status = $rsOrder["Order_Status"];
$Order_Status = ["待确认","待付款","已付款","已发货","已完成","申请退款中"];
$Shipping=json_decode(htmlspecialchars_decode($rsOrder["Order_Shipping"]),true);
$CartList=json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]),true);
$PaymentInfo = json_decode($rsOrder["Order_PaymentInfo"], true);
$amount = $fee = 0;

$dada = '';
$stores = '';
if($CartList){
    foreach ($CartList as $k => $v ){
        foreach ($v as $key => $val ){
            $stores = isset($val['Is_BizStores']) ? $val['Is_BizStores'] : '';
            $dada = isset($val['Is_dada']) ? $val['Is_dada'] : '';
        }
    }
}


$lists_back = [];
if($rsOrder["Is_Backup"]==1){
	$lists_back = $capsule->table('user_back_order')->where(['Users_ID' => $rsBiz["Users_ID"], 'Order_ID' => $OrderID, 'Back_Type' => 'shop'])->get();
}

$_STATUS = [
	'<font style="color:#F00; font-size:12px;">申请中</font>',
	'<font style="color:#F60; font-size:12px;">卖家同意</font>',
	'<font style="color:#0F3; font-size:12px;">买家发货</font>',
	'<font style="color:#600; font-size:12px;">卖家收货并确定退款价格</font>',
	'<font style="color:blue; font-size:12px;">完成</font>',
	'<font style="color:#999; font-size:12px; text-decoration:line-through;">卖家拒绝退款</font>'
];

//获取区域地址信息并解析
$address = getArea($rsOrder);

$Province = $address['Province'];
$City = $address['City'];
$Area = $address['Area'];
$Address_Detailed = $address['Address'];
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
    <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/biz/js/shop.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/biz/orders/orders_menubar.php');?>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <script language="javascript">$(document).ready(shop_obj.orders_init);</script>
    <div id="orders" class="r_con_wrap">
      <div class="detail_card">
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
              <td><?php if($rsOrder["Order_TotalPrice"]<=$rsOrder["Back_Amount"]){?><font style="color:#999; text-decoration:line-through">已退款</font><?php }else{?><?php echo $Order_Status[$Status] ?><?php }?></td>
            </tr>
			<?php if(!empty($rsOrder['Order_StoresID'])){ ?>
			<tr>
              <td nowrap>所属门店：</td>
              <td>
			  <?php
				$rsStores = $capsule->table('stores')->where(['Users_ID' => $rsOrder["Users_ID"], 'Stores_ID' => $rsOrder['Order_StoresID'], 'Biz_ID' => $BizID])->first();
				if(!empty($rsStores)){
					echo $rsStores['Stores_Name'];
				}
			  ?>
			  </td>
            </tr>
			<tr>
              <td nowrap>门店地址：</td>
              <td>
			  <?php
				echo $rsStores['Stores_City'] . $rsStores['Stores_Area'] . $rsStores['Stores_Address'];
			  ?>
			  </td>
            </tr>
			<?php } ?>
			<?php 
			if(!empty($rsOrder['Order_Ext'])){
			$ext = json_decode($rsOrder['Order_Ext'], 1)['dada'];  ?>
			<tr>
              <td nowrap>配送员姓名：</td>
              <td><?=$ext['dm_name'] ?></td>
            </tr>
			<tr>
              <td nowrap>配送员手机号：</td>
              <td><?=$ext['dm_mobile'] ?></td>
            </tr>
			<tr>
              <td nowrap>配送状态：</td>
              <td><?=$ext['order_status']['name'] ?></td>
            </tr>
			<?php } ?>
			
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
              <td><?php echo $rsOrder["Address_Name"] ?></td>
            </tr>
            <tr>
              <td nowrap>手机号码：</td>
              <td><?php echo $rsOrder["Address_Mobile"] ?></td>
            </tr>
            <tr>
              <td nowrap>配送方式：</td>
              <td><?php echo isset($Shipping) && isset($Shipping["Express"])?$Shipping["Express"]:(!empty($stores) && $stores == 1  ? '到店自提' : (!empty($dada) && $dada == 1 ? '同城配送' : '') ); ?><?php echo $rsOrder["Order_ShippingID"] ? "：".$rsOrder["Order_ShippingID"] : ""; ?></td>
            </tr>
            <tr>
              <td nowrap>地址信息：</td>
              <td><?php echo $Province.$City.$Area.'【'.$rsOrder["Address_Name"].'，'.$rsOrder["Address_Mobile"].'】'.'&nbsp;&nbsp;&nbsp;&nbsp;详细地址: '.$Address_Detailed ?></td>
            </tr>
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
              <td><?php echo $rsOrder["Order_Remark"] ?></td>
            </tr>
          </table>
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
          <?php 
			$total=0;
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
							if(stripos($v["ImgPath"], 'http') !== false){
								$v["ImgPath"] = str_replace($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'], "", $v["ImgPath"]);
							}
							if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $v["ImgPath"]) || empty($v["ImgPath"])){
								$v["ImgPath"] = "/static/images/none.png";
							}
                            $price = (!empty($v['user_curagio']) && $v['user_curagio'] > 0 && $v['user_curagio'] <= 100 ? (100 - $v['user_curagio']) / 100 : 1) * (isset($v["Property"]['shu_pricesimp']) ? $v["Property"]['shu_pricesimp'] : $v["ProductsPriceX"]);
							$total+=$v["Qty"]*$price;
							$qty+=$v["Qty"];
							echo '<tr class="item_list" align="center">
								<td valign="top"><img src="'.$v["ImgPath"].'" width="100" height="100" /></td>
								<td align="left" class="flh_180">'.$v["ProductsName"].'<br><dd>'.(empty($v["Property"]["shu_value"]) ? '' : $v["Property"]["shu_value"]).'</dd></td><td>￥'.$price.'</td>	
							   <td>'.$v["Qty"].'</td>
								<td>￥'.$price*$v["Qty"].'</td>
							  </tr>';
						}
					}
				}
			}

			require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/ordersbak.php');
			?>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>