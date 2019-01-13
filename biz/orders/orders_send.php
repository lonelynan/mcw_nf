<?php
require_once('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/shipping.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/biz/plugin/dada/DadaOpenapi.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/biz/plugin/dada/function.php');

use Illuminate\Database\Capsule\Manager as DB;

$OrderID = $request->input('OrderID', 0);
$rsOrder = $capsule->table('user_order')->where(['Users_ID' => $rsBiz["Users_ID"], 'Order_ID' => $OrderID, 'Biz_ID' => $BizID])->first();
if(empty($rsOrder)){
	alert("订单不存在", "orders.php");
}
if($rsOrder["Order_Status"]<>2){
	alert("只有状态为“已付款”的订单才可发货", "orders.php");
}
$Shipping=json_decode(htmlspecialchars_decode($rsOrder["Order_Shipping"]),true);
if($_POST){
	$Data = [];
	if(!empty($Shipping['Tag'])){
		$Data = ['Order_Status' => 3, 'Order_Remark' => $_POST["Remark"]];
		$UsersID = $rsOrder['Users_ID'];
		//获取订单编号
		$rsStores = DB::table('stores')->where(['Users_ID' => $UsersID, 'Stores_ID' => $rsOrder['Order_StoresID']])->first(['Stores_No']);

		$rsShippingList = DB::table('third_shipping')->where(['Status' => 1, 'Tag' => $Shipping['Tag']])->first(['ID']);
		if(empty($rsShippingList)){
			alert("商户开发者信息没有配置，请配置");
		}
				
		$rsThirdConfig = DB::table('third_shipping_config')->where('Tid', $rsShippingList['ID'])->first();
		if(empty($rsThirdConfig)){
			alert("商户开发者信息没有配置，请配置");
		}
		
		if(empty($rsBiz['Biz_Ext'])){
			alert("商户开发者信息没有配置，请配置");
		}
		$ext = json_decode($rsThirdConfig['ext'], 1);
		
		!defined('APP_KEY') && define('APP_KEY', $ext['app_key']);
		!defined('APP_SECRET') && define('APP_SECRET', $ext['app_secret']);
		!defined('APP_DOMAIN') && define('APP_DOMAIN', 'http://' . $ext['domain']);
		// 测试模式 !defined('APP_DOMAIN') && define('APP_DOMAIN', 'http://newopen.qa.imdada.cn');
		$Areas = getAreaName($UsersID, $rsOrder['User_ID'], ['Address_Province' => $rsOrder['Address_Province'], 'Address_City' => $rsOrder['Address_City'], 'Address_Area' => $rsOrder['Address_Area'], 'Address_Detailed' => $rsOrder['Address_Detailed']]);
		
		$address = $Areas['Province'] . $Areas['City'] . $Areas['Area'] . $Areas['Address'];
		$City = str_replace('市','', $Areas['City']);
		
		$location = getBdPosition($address, $ak_baidu);
		$position = bd_trans_gd($location['lat'], $location['lng']);
		$receiver_lng = $position['lng'];
		$receiver_lat = $position['lat'];

		$ext = json_decode($rsBiz['Biz_Ext'],1)['dada'];
		/*
		测试模式
		$ext['source_id'] = '73753';
		$rsStores['Stores_No'] = '11047059'; */
		
		$citylist = get_cityCode($ext['source_id']);
		$cityCode = 0;
		foreach($citylist as $k => $v){
			if($v['cityName'] == $City){
				$cityCode = $v['cityCode'];
				break;
			}
		}
		
		$result = addOrder([
			'shop_no' => $rsStores['Stores_No'],
			'origin_id' => $rsOrder['Order_CreateTime'] . $rsOrder['Order_ID'],
			'city_code' => $cityCode,
			'cargo_price' => $rsOrder['Order_TotalPrice'],
			'is_prepay' => 0,
			'expected_fetch_time' => time() + 1200,
			'receiver_name' => $rsOrder['Address_Name'],
			'receiver_address' => $address,
			'receiver_lat' => $receiver_lat,
			'receiver_lng' => $receiver_lng,
			'callback' => 'http://' . $_SERVER['HTTP_HOST'] . '/biz/plugin/dada/notify_url.php',
			//'callback' => 'http://www.cqboguan9998.com/notify_url.php',	//测试回调地址
			'receiver_phone' => $rsOrder['Address_Mobile']
			
		], $ext['source_id']);
		if($result['status']=='success'){
			$Flag = $capsule->table('user_order')->where('Order_ID', $OrderID)->update($Data);
		}
	}else{
		$Data=[
			"Address_Name" => $request->input('Name', ''),
			"Address_Mobile" => $request->input('Mobile', ''),
			"Order_ShippingID"=> $request->input('ShippingID', 0),
			"Order_Remark" => $request->input('Remark', 0),
			"Order_SendTime"=>time(),
			"Order_Status"=>3
		];
		$Express = $request->input('Express', '');
		if(!empty($Express)){
			$ShippingN = array(
				"Express" => $Express,
				"Price" => empty($Shipping["Price"]) ? 0 : $Shipping["Price"]
			);
			$Data["Order_Shipping"] = json_encode($ShippingN, JSON_UNESCAPED_UNICODE);
		}
		$Flag = $capsule->table('user_order')->where('Order_ID', $OrderID)->update($Data);
	}

	if($Flag!==false){	
		alert("发货成功", "orders.php");
	}else{
		alert("发货失败");
	}
}

$rsConfig = shop_config($rsBiz["Users_ID"]);
$Status = $rsOrder["Order_Status"];
$Order_Status = ["待确认","待付款","已付款","已发货","已完成","申请退款中"];
$PayShipping = get_front_shiping_company_dropdown($rsBiz["Users_ID"],$rsBiz);
$CartList=json_decode(htmlspecialchars_decode($rsOrder["Order_CartList"]),true);
$PaymentInfo = json_decode($rsOrder["Order_PaymentInfo"], true);
$amount = $fee = 0;

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
            
			<?php if(!empty($Shipping['Tag'])){ ?>
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
              <td>
				<?=$Shipping['Express'] ?>
			  </td>
			</tr>
			<?php }else{ ?>
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
              <td>
				<select name="Express">
                  <?php foreach($PayShipping as $key=>$value){
					  $select = '';
				  	if(!empty($Shipping["Express"])){
						$select = $value == $Shipping["Express"]?' selected':'';
					}
				  ?>
                  <option value="<?=$value?>" <?=$select?>><?php echo $value ?></option>
                  <?php }?>
                </select>
                &nbsp;&nbsp;快递单号：
                <input name="ShippingID" value="<?php echo $rsOrder["Order_ShippingID"] ?>" notnull />
              </td>
            </tr>
			<?php } ?>
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
              <td><textarea name="Remark" rows="5" cols="50"><?php echo $rsOrder["Order_Remark"] ?></textarea></td>
            </tr>
            <tr>
              <td></td>
              <td><input type="submit" class="btn_green" name="submit_button" value="确认发货" /></td>
            </tr>
          </table>
          <input type="hidden" name="OrderID" value="<?php echo $rsOrder["Order_ID"] ?>" />
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