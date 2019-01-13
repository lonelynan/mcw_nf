<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/eloquent.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/backup.class.php');

if(empty($_SESSION["Users_Account"])){
    header("location:/member/login.php");
}

$backup = new backup($DB,$_SESSION["Users_ID"]);
$BackID=empty($_REQUEST['BackID'])?0:$_REQUEST['BackID'];
$rsBack = $DB->GetRs("user_back_order","*","where Users_ID='".$_SESSION["Users_ID"]."' and Back_ID='".$BackID."'");
$Status=$rsBack["Back_Status"];

//$rsOrder = $DB->GetRs("user_order","Order_IsVirtual","where Order_ID=".$rsBack["Order_ID"]);
$rsOrder = $DB->GetRs("user_order","*","where Order_ID=".$rsBack["Order_ID"]);
//订单金额
//$rsUserConfig = $DB->GetRs("user_order","Back_Amount","where Order_ID='".$rsBack['Order_ID']."'");

$rsBack['Back_Amount'] = $rsOrder['Back_Amount'];

if(!empty($_GET["action"])){
	$action = $_GET["action"];
	require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_message.class.php');
    $weixin_message = new weixin_message($DB,$_SESSION["Users_ID"],$rsBack["User_ID"]);
	$url='http://'.$_SERVER["HTTP_HOST"]."/api/".$rsBack['Users_ID']."/".$rsBack["Back_Type"]."/member/backup/detail/".$BackID."/";
	
	if($action=="agree"){
		if($Status<>0){
			echo '<script language="javascript">alert("操作错误");history.back();</script>';
		}else{
			// if($rsOrder['Order_Status'] == 5){
				
			// }
			if(!empty($rsOrder['Order_ShippingID'])){
				if($rsBack['Order_Status'] == 2){
					$backup->update_backup("seller_agrees",$BackID);
				}else{
					$backup->update_backup("seller_agree",$BackID);
				}
			}else {
				$backup->update_backup("seller_agrees",$BackID);
			}            
            $contentStr = '您的退款单商家已同意退款，<a href="'.$url.'">查看详情</a>';
            $weixin_message->sendscorenotice($contentStr);
			echo '<script language="javascript">alert("操作成功");window.location="back_view.php?BackID='.$BackID.'";</script>';
		}
	}elseif($action=="reject"){
		if($Status<>0){
			echo '<script language="javascript">alert("操作错误");history.back();</script>';
		}else{
			$back = $DB->GetRs("user_back_order","*","where Back_ID=".$BackID);
			$Order = $DB->GetRs("user_order","*","where Order_ID=".$back["Order_ID"]);

			if($Order['Order_Status'] == 4){
				echo '<script language="javascript">alert("订单已完成，不能驳回。");window.location="back_view.php?BackID='.$BackID.'";</script>';
			}elseif($Order['Order_Status'] == 5) {
          $backup->update_backup("seller_reject",$BackID,$_GET["reason"]);
          $contentStr = '您的退款单商家拒绝退款，<a href="'.$url.'">查看详情</a>';
          $weixin_message->sendscorenotice($contentStr);
          echo '<script language="javascript">alert("操作成功");window.location="back_view.php?BackID='.$BackID.'";</script>';          
      }elseif($back['Order_Status'] != $Order['Order_Status']) {
				echo '<script language="javascript">alert("订单已完成，不能驳回2。");window.location="back_view.php?BackID='.$BackID.'";</script>';
			}else{
        
          $backup->update_backup("seller_reject",$BackID,$_GET["reason"]);
          $contentStr = '您的退款单商家拒绝退款，<a href="'.$url.'">查看详情</a>';
          $weixin_message->sendscorenotice($contentStr);
          echo '<script language="javascript">alert("操作成功");window.location="back_view.php?BackID='.$BackID.'";</script>';          


				
			}
			
		}
	}elseif($action=="recieve"){
		if(($Status==2 && $rsOrder["Order_IsVirtual"]==0) || ($Status==0 && $rsOrder["Order_IsVirtual"]==1)){
			$backup->update_backup("seller_recieve",$BackID,$_GET["Amount"]."||%$%".$_GET["reason"]);
			echo '<script language="javascript">alert("操作成功");window.location="back_view.php?BackID='.$BackID.'";</script>';
		}else{
			echo '<script language="javascript">alert("操作错误");history.back();</script>';
		}
	}elseif($action=="back_money"){
		if($Status<>3){
			echo '<script language="javascript">alert("操作错误");history.back();</script>';
		}else{
			$backup->update_backup("admin_backmoney",$BackID);
			echo '<script language="javascript">alert("操作成功");window.location="back_view.php?BackID='.$BackID.'";</script>';
		}
	}
	exit;
}else{
	$Recieve_Address = $DB->GetRs("user_recieve_address","*","where Users_ID='".$_SESSION["Users_ID"]."'");
	$rsUser = $DB->GetRs("user","*","where User_ID=".$rsBack['User_ID']);
		
	$_STATUS = array('<font style="color:#F00">申请中</font>','<font style="color:#F60">卖家同意</font>','<font style="color:#0F3">买家发货</font>','<font style="color:#600">卖家收货并确定</font>','<font style="color:#blue">完成</font>','<font style="color:#999; text-decoration:line-through;">卖家拒绝退款</font>');
	$_TUIKUAN = array('<font style="color:#F00">未退款</font>','<font style="color:blue">已退款</font>');
	
	$rsBack["Back_Json"] = str_replace('\n','',htmlspecialchars_decode($rsBack["Back_Json"]));
	$ProductList = json_decode($rsBack["Back_Json"],true);

	$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
	$area_array = json_decode($area_json,TRUE);
	$province_list = $area_array[0];
	$Province = '';
	if(!empty($Recieve_Address['RecieveProvince'])){
		$Province = $province_list[$Recieve_Address['RecieveProvince']].',';
	}
	$City = '';
	if(!empty($Recieve_Address['RecieveCity'])){
		$City = $area_array['0,'.$Recieve_Address['RecieveProvince']][$Recieve_Address['RecieveCity']].',';
	}

	$Area = '';
	if(!empty($Recieve_Address['RecieveArea'])){
		$Area = $area_array['0,'.$Recieve_Address['RecieveProvince'].','.$Recieve_Address['RecieveCity']][$Recieve_Address['RecieveArea']];
	}
	
	
}

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
<style type="text/css">
.back_btn_blue{display:block; width:86px; line-height:28px; height:30px; text-align:center; background:#1584D5; color:#FFF; border-radius:5px; float:left; margin-right:5px;}
.back_btn_grey{display:block; width:86px; line-height:28px; height:30px; text-align:center; background:#888; color:#FFF; border-radius:5px; float:left; margin-right:5px;}
.back_btn_blue a:hover,.back_btn_grey a:hover{text-decoration:none; color:#FFF}
#reject,#recieve{display:none}
</style>
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
        <li class="cur"><a href="backups.php">退货单管理</a></li>
      </ul>
    </div>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <script language="javascript">$(document).ready(shop_obj.backorder_edit);</script>
    <div id="orders" class="r_con_wrap">
      <div class="cp_title">
        <div id="cp_view" class="cur">退货单详情</div>
      </div>
      <div class="detail_card">
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="order_info">
            <tr>
              <td width="8%" nowrap>退货单编号：</td>
              <td width="92%"><?php echo $rsBack["Back_Sn"] ?></td>
            </tr>
            <tr>
              <td nowrap>退货单时间：</td>
              <td><?php echo date("Y-m-d H:i:s",$rsBack["Back_CreateTime"]) ?></td>
            </tr>
            <tr>
              <td nowrap>退款数量：</td>
              <td><?php echo $rsBack["Back_Qty"] ?></td>
            </tr>
			<tr>
              <td nowrap>退款总价：</td>
              <td><font style="color:red"><?php echo $rsBack["Back_Amount"] ?></font></td>
            </tr>
            <tr>
              <td nowrap>退款人：</td>
              <td><?=!empty($rsUser["User_NickName"])?$rsUser["User_NickName"]:'信息不全'?></td>
            </tr>
			<tr>
              <td nowrap>退款账号：</td>
              <td><?php echo $rsBack["Back_Account"] ?></td>
            </tr>
			<tr>
              <td nowrap>商家收货地址：</td>
              <td><?php echo $Province.$City.$Area.'【'.$Recieve_Address["RecieveAddress"].' ， '.$Recieve_Address["RecieveName"].'，'.$Recieve_Address["RecieveMobile"].'】';?></td>
            </tr>
            <tr>
              <td nowrap>退货单状态：</td>
              <td><?php echo $_STATUS[$Status];?></td>
            </tr>
            <tr>
              <td nowrap>网站是否退款：</td>
              <td><?php echo $_TUIKUAN[$rsBack["Back_IsCheck"]] ?></td>
            </tr>
            
            <tbody id="btns">
            <tr>
              <td nowrap>&nbsp;</td>
              <td>
              <?php
              if($Status==0){
			  ?>
					<?php

          //产品是否允许退款判断
          $product = $DB->GetRs('shop_products', 'Products_IsVirtual, Products_IsRecieve', 'WHERE Products_ID=' . intval($rsBack['ProductID']));

          //商品信息未找到或者商品为虚拟产品(产品编辑页面的订单流程值为3)
          if (empty($product) || ($product['Products_IsVirtual'] == 1 && $product['Products_IsRecieve'] == 1)) {
            $allow_back_money = false;
          } else {
            $allow_back_money = true;
          }
          unset($product);

           if ($allow_back_money) {
?>
					<a href="?action=agree&BackID=<?php echo $BackID;?>" class="back_btn_blue">同意</a>&nbsp;&nbsp;<a href="javascript:void(0);" id="reject_btn" class="back_btn_grey">驳回</a>
					<?php }else{?>
					<a href="javascript:void(0);" id="recieve_btn" class="back_btn_blue">同意</a>&nbsp;&nbsp;<a href="javascript:void(0);" id="reject_btn" class="back_btn_grey">驳回</a>
					<?php }?>

              <?php }elseif($Status==2){?>
                <a href="javascript:void(0);" id="recieve_btn" class="back_btn_blue">收货</a>
              <?php }?>
              <?php
              if($Status==3){
			  ?>
              	<a href="?action=back_money&BackID=<?php echo $BackID;?>" class="back_btn_blue">退款给买家</a>
              <?php }?>
              </td>
            </tr>
            </tbody>
            <tbody id="reject">
            <form id="reject_form" action="back_view.php" method="get">
            <tr>
             <td nowrap>驳回理由：</td>
             <td>
               <textarea name="reason" style="width:200px; height:50px; border:1px #dfdfdf solid; font-size:12px;" notnull></textarea>
             </td>
            </tr>
            <tr>
             <td nowrap>&nbsp;</td>
             <td>
               <input type="submit" name="submit" value="提交" style="float:left; display:block; height:32px; line-height:26px; width:86px; text-align:center; font-size:12px; color:#FFF; background:#1584D5; border-radius:5px; margin-right:8px;" /><a href="javascript:void(0);" class="btn_gray" id="goback_reject">返回</a>
             </td>
            </tr>
            <input type="hidden" name="action" value="reject" />
            <input type="hidden" name="BackID" value="<?php echo $BackID;?>" />
            </form>
            </tbody>        
            <tbody id="recieve">
            <form id="recieve_form" action="back_view.php" method="get">
            <tr>
             <td nowrap>退款金额：</td>
             <td>
               <input name="Amount" value="<?php echo $rsBack["Back_Amount"];?>" style="width:180px; height:30px; line-height:30px; border:1px #dfdfdf solid; font-size:12px;" notnull />
             </td>
            </tr>
            <tr>
             <td nowrap>理由：</td>
             <td>
               <textarea name="reason" style="width:200px; height:50px; border:1px #dfdfdf solid; font-size:12px;" notnull></textarea>
             </td>
            </tr>
            <tr>
             <td nowrap>&nbsp;</td>
             <td>
               <input type="submit" name="submit" value="提交" style="float:left; display:block; height:32px; line-height:26px; width:86px; text-align:center; font-size:12px; color:#FFF; background:#1584D5; border-radius:5px; margin-right:8px;" /><a href="javascript:void(0);" class="btn_gray" id="goback_recieve">返回</a>
             </td>
            </tr>
            <input type="hidden" name="action" value="recieve" />
            <input type="hidden" name="BackID" value="<?php echo $BackID;?>" />
            </form>
            </tbody>
          </table>
          <div class="blank12"></div>
          <div class="item_info">退款流程</div>
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="order_info">
          <?php
          	$DB->Get("user_back_order_detail","*","where backid=".$BackID." order by createtime asc");
			while($r = $DB->fetch_assoc()){
		  ?>
          	<tr>
              <td width="150"><?php echo date("Y-m-d H:i:s",$r["createtime"]);?></td>
              <td style="color:#777"><?php echo $r["detail"];?></td>
            </tr>
          <?php }?>
          </table>
        <div class="blank12"></div>
        <div class="item_info">物品清单</div>
        <table class="order_item_list" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tbody><tr class="tb_title">
            <td width="20%">图片</td>
            <td width="35%">产品信息</td>
            <td width="15%">价格</td>
            <td width="15%">数量</td>
            <td class="last" width="15%">小计</td>
          </tr>
          <tr class="item_list" align="center">
            <td valign="top"><img src="<?=$ProductList['ImgPath']?>" height="100" width="100"></td>
            <td class="flh_180" align="left"><?=$ProductList['ProductsName']?>
            <td><?=$ProductList['ProductsPriceX']?></td>
            <td><?=$ProductList['Qty']?></td>
            <td><?=$ProductList['ProductsPriceX']*$ProductList['Qty']?></td>
          </tr>
        </tbody></table>
      </div>
    </div>
  </div>
</div>
<!--拒绝form begin sxf 20160613 17:20 -->
<script type="text/javascript">
$(function(){
  //驳回操作
  $("#reject_btn").click(function(){
      $("#btns").hide();
      $("#reject").show();
    });

    $("#goback_reject").click(function(){
      $("#reject").hide();
      $("#btns").show();
    });
    
    $('#reject_form').submit(function(){
      if(global_obj.check_form($('#reject_form *[notnull]'))){return false};
      $('#reject_form input:submit').attr('disabled', true);
      return true;
    });

    $("#recieve_btn").click(function(){
      $("#btns").hide();
      $("#recieve").show();
    });
})  

</script>            
<!--//拒绝form end -->    
</body>
</html> 