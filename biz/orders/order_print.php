<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once('../global.php');

$OrderID=empty($_REQUEST['OrderID'])?0:$_REQUEST['OrderID'];
$sql = "select * from user_order as o left join user as u on o.User_ID=u.User_ID where u.Users_ID='".$_SESSION["Users_ID"]."' and o.Order_ID in(".$OrderID.")";
$result =$DB->query($sql);
$orderlist = array();

$Order_Status=array("待确认","待付款","已付款","已发货","已完成","申请退款中","申请换货");
$Maintain_Status=array("维修—未处理","维修—已处理","商家驳回维修申请");
$Back_After_Status=array("申请换货中","卖家同意换货","换货-买家发货","换货-卖家确定收货","换货-卖家已发货","买家拒绝换货");
$Back_Status=array("申请退款中","卖家同意退款/退款退货","退货-买家发货","退货-卖家确定收货","已完成","卖家驳回");

while($res=$DB->fetch_assoc($result))
{
  $res['Order_SN'] = date("Ymd",$res['Order_CreateTime']).'-'.$res['Order_ID'];
  $res['Order_CreateTime'] = date("Y-m-d H:i:s",$res['Order_CreateTime']);
  $res['Order_Shipping'] = json_decode(htmlspecialchars_decode($res['Order_Shipping']),true);
  $res['Order_ShippingID'] = !empty($res['Order_ShippingID'])?$res['Order_ShippingID']:'未发货';

  unset($rsBack);
  if($res['Order_Status'] == 6 || $res['Order_Status'] == 5){
    $rsBack = $DB->GetRs("user_back_order","Back_Type, Back_After_Status, Back_Status","where Users_ID='".$_SESSION["Users_ID"]."' and Order_ID=".$res['Order_ID']);
  }
  unset($rsMaintain);
  if($res['Order_Status'] == 7){
    $rsMaintain= $DB->GetRs("user_maintain_order","Maintain_Status","where Users_ID='".$_SESSION["Users_ID"]."' and Order_ID=".$res['Order_ID']);
  }

  $res['Order_Status_type'] = '未知状态';
  if (isset($rsBack) && $rsBack['Back_Type'] == "after_sale") {
    $res['Order_Status_type'] = isset($Back_After_Status[$rsBack["Back_After_Status"]]) ? $Back_After_Status[$rsBack["Back_After_Status"]] : '未知状态';
  } elseif (isset($rsBack) && $rsBack['Back_Type'] == "shop") {
    $res['Order_Status_type'] = isset($Back_Status[$rsBack["Back_Status"]]) ? $Back_Status[$rsBack["Back_Status"]] : '未知状态';
  } elseif (isset($rsMaintain)) {
    $res['Order_Status_type'] = isset($Maintain_Status[$rsMaintain["Maintain_Status"]]) ? $Maintain_Status[$rsMaintain["Maintain_Status"]] : '未知状态';
  } else {
    $res['Order_Status_type'] = isset($Order_Status[$res["Order_Status"]]) ? $Order_Status[$res["Order_Status"]] : '未知状态';
  }

  //订单商品信息处理
  $res['Order_CartList'] = json_decode(htmlspecialchars_decode($res['Order_CartList']), true);
  $res['Order_PaymentMethod'] = $res['Order_PaymentMethod']?$res['Order_PaymentMethod']:'未支付'; 
  if(is_numeric($res['Address_Province'])){
    $area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
    $area_array = json_decode($area_json,TRUE);
    $province_list = $area_array[0];
    $Province = '';
    if(!empty($res['Address_Province'])){
      $Province = $province_list[$res['Address_Province']];
    }
    $City = '';
    if(!empty($res['Address_City'])){
      $City = $area_array['0,'.$res['Address_Province']][$res['Address_City']];
    }

    $Area = '';
    if(!empty($res['Address_Area'])){
      $Area = $area_array['0,'.$res['Address_Province'].','.$res['Address_City']][$res['Address_Area']];
    }
    $res['Address_Province'] = $Province;
    $res['Address_City'] = $City;
    $res['Address_Area'] = $Area;
  }
  //购货人信息
  $orderlist[] = $res;
}

//获取寄件人信息
$receiveInfo = $DB->GetRs("user_recieve_address","*","WHERE Users_ID='".$_SESSION["Users_ID"]."'");
if(is_numeric($receiveInfo['RecieveProvince'])){
    
    $area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
    $area_array = json_decode($area_json,TRUE);
    $province_list = $area_array[0];
    $Province = '';
    if(!empty($receiveInfo['RecieveProvince'])){
        $Province = $province_list[$receiveInfo['RecieveProvince']];
    }
    $City = '';
    if(!empty($receiveInfo['RecieveCity'])){
        $City = $area_array['0,'.$receiveInfo['RecieveProvince']][$receiveInfo['RecieveCity']];
    }
    
    $Area = '';
    if(!empty($receiveInfo['RecieveArea'])){
        $Area = $area_array['0,'.$receiveInfo['RecieveProvince'].','.$receiveInfo['RecieveCity']][$receiveInfo['RecieveArea']];
    }
    $receiveInfo['Address_Province'] = $Province;
    $receiveInfo['Address_City'] = $City;
    $receiveInfo['Address_Area'] = $Area;
}
?>
<html>
  <head>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/member/js/jquery.jqprint-0.3.js'></script>
    <style type="text/css">
      body,td {font-size:13px;}
      .lbl {width:100px;}
      table thead td {  }
      .bold { font-weight:bold;font-size:22px;    border-top: solid 2px #555; }
      .pd200 { margin-right:200px;}
      .pd100 { margin-right:100px;}
      .pdtop30 { margin-top:30px;}
      .grypd10 { margin-top:5px;  border-top: solid 1px #999;}
    </style>
    <script language="javascript">
    function  print(){
    $("#printArea").jqprint();
    }

</script>
  </head>
  <body>
      <input type="button" onclick=" print()" value="打印"/>
      <div id="printArea" class="pdtop30">
      <?php foreach($orderlist as $Key => $order): ?>

          <table width="100%" cellpadding="1" style="margin-bottom: 50px;">
          	 <thead>
          	 	<td class="bold">订单编号：<?=$order['Order_ID']?></td>
          	 	<td></td>
          	 </thead>
          	 <tbody>
          	 	<tr>
          	 		<td colspan="2">
                  <p><span class="lbl">订单状态：<?=$order['Order_Status_type']?></span></p>
                  <p><span class="lbl">订单总金额：￥<?=$order['Order_TotalPrice']?></span></p>
          	 			<p><span class="lbl">收件人：<?=$order['Address_Name']?></span>&nbsp;&nbsp;
                  <span>电话：</span><span><?=$order['Address_Mobile']?></span></p>
          	 			<p><span  class="lbl">收货地址:【<?=$order['Address_Province']?> <?=$order['Address_City']?> <?=$order['Address_Area']?>】&nbsp;<?=$order['Address_Detailed']?>&nbsp;</span></p>
          	 			<?php  
                  if(!empty($order['Order_CartList'])){
                    foreach($order['Order_CartList'] as $kk => $vv){
                      foreach($vv as $k => $v){ 
                  ?>
          	 			<p><span  class="lbl">商品名称：</span><span><?=$v['ProductsName']?>（￥<?=$v['ProductsPriceX'] ?>）</span>
          	 			<span class="pd100">数量：<?=$v['Qty']?>&nbsp;总价：￥<?=$v['ProductsPriceX']*$v['Qty'] ?></span></p>
          	 			
          	 			<?php } } } ?>
                  <p><span  class="lbl">配送费用：</span><span>￥<?=isset($order['Order_Shipping']['Price']) ? $order['Order_Shipping']['Price'] : ''?></span></p>
				  <?php if ($order['Order_Remark']) { ?>
				  <p><span class="lbl">备注：￥<?=$order['Order_Remark']?></span></p>
				  <?php } ?>
          	 		</td>
          	 	</tr>
          	 	<?php if(isset($receiveInfo['RecieveName']) && !empty($receiveInfo['RecieveName']) && isset($receiveInfo['RecieveMobile']) && !empty($receiveInfo['RecieveMobile']) && in_array($order['Order_Status'], [3,4,5,6,7])){?>
          	 	<tr>
          	 		<td colspan="2" class="grypd10">
          	 			<p ><span class="lbl">发件人：<?=$receiveInfo['RecieveName'] ?></span>&nbsp;&nbsp;
                  <span>联系方式：</span><span><?=$receiveInfo['RecieveMobile'] ?></span></p>
          	 			<p><span  class="lbl">发货地址:【<?=$receiveInfo['Address_Province']?> <?=$receiveInfo['Address_City']?> <?=$receiveInfo['Address_Area']?>】&nbsp;<?=$receiveInfo['RecieveAddress']?>&nbsp;</span></p>
                  <p><span  class="lbl">配送方式：<?=isset($order['Order_Shipping']['Express']) ? $order['Order_Shipping']['Express'] : ''?></span>&nbsp;
                    <span>快递单号：<?=isset($order['Order_ShippingID']) ? $order['Order_ShippingID'] : ''?></span></p>
          	 		</td>
          	 	</tr>
          	 	<?php }?>
          	 </tbody>
          </table>
      <?php endforeach; ?>
      </div>
  </body>
</html>