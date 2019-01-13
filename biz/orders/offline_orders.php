<?php
require_once('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');

$_GET = daddslashes($_GET,1);

$psize = 10;
$condition = "where Users_ID='".$rsBiz["Users_ID"]."' and (Order_Type='offline' or Order_Type='offline_st' or Order_Type='offline_qrcode') and Biz_ID=".$_SESSION["BIZ_ID"];
if(isset($_GET["search"])){
	if($_GET["search"]==1){
		if(!empty($_GET["Keyword"])){
			$condition .= " and `".$_GET["Fields"]."` like '%".$_GET["Keyword"]."%'";
		}
		if(!empty($_GET["OrderNo"])){
			$OrderID = substr($_GET["OrderNo"],8);
			$OrderID =  empty($OrderID) ? 0 : intval($OrderID);
			$condition .= " and Order_ID=".$OrderID;
		}		
		if(!empty($_GET["AccTime_S"])){
			$condition .= " and Order_CreateTime>=".(strtotime($_GET["AccTime_S"])==-1 || strtotime($_GET["AccTime_S"])==false?0:strtotime($_GET["AccTime_S"]));
		}
		if(!empty($_GET["AccTime_E"])){
			$condition .= " and Order_CreateTime<=".(strtotime($_GET["AccTime_E"])==-1 || strtotime($_GET["AccTime_E"])==false?0:strtotime($_GET["AccTime_E"]));
		}
		if(!empty($_GET["psize"])){
			$psize = intval($_GET["psize"]);
		}
	}
}

$condition .= " order by Order_CreateTime desc, Order_ID desc";
$jcurid = 2;
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
    <script type='text/javascript' src='/biz/js/shop.js?t=<?=time()?>'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/biz/orders/orders_menubar.php');?>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script> 
	<script type='text/javascript' src='/static/js/jquery.datetimepicker.js'></script>
	<link href='/static/css/jquery.datetimepicker.css' rel='stylesheet' type='text/css' />
    <script language="javascript">
		$(document).ready(shop_obj.orders_init);
	</script>
    <div id="orders" class="r_con_wrap">
      <form class="search" id="search_form" method="get" action="?">
        <select name="Fields">			
			<option value='Address_Mobile'>购买手机</option>			
		</select>
        <input type="text" name="Keyword" value="" class="form_input" size="15" />&nbsp;
		订单号：<input type="text" name="OrderNo" value="" class="form_input" size="15" />&nbsp;        
        时间：
        <input type="text" class="input" name="AccTime_S" value="" maxlength="20" />
        -
        <input type="text" class="input" name="AccTime_E" value="" maxlength="20" />
        &nbsp;
        <input type="text" name="psize" value="" class="form_input" size="5" /> 条/页
        <input type="submit" class="search_btn" value="搜索" />
        <input type="button" class="output_btn" value="导出" />
        <input type="hidden" value="1" name="search" />
      </form>
      <form id="submit_form" method="get" action="/biz/orders/send_print.php">
      <table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="order_list">
        <thead>
          <tr>
          	<td width="3%" nowrap="nowrap"><input type="checkbox" id="checkall"/></td>
            <td width="6%" nowrap="nowrap">序号</td>
            <td width="14%" nowrap="nowrap">订单号</td>
			<td width="8%" nowrap="nowrap">下单类型</td>
            <td width="11%" nowrap="nowrap">分销商</td>            
            <td width="11%" nowrap="nowrap">金额</td>            
            <td width="11%" nowrap="nowrap">订单状态</td>
            <td width="12%" nowrap="nowrap">时间</td>
            <td width="10%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
          <?php		  
		  $DB->getPage("user_order","*",$condition,$psize);
		  $order_type_arr = array('offline_st' => '在线买单', 'offline_qrcode' => '线下扫码');
		  $Order_Status=array("待确认","待付款","已付款","已发货","已完成","申请退款中");
		  /*获取订单列表牵扯到的分销商*/
		  $order_list = array();
		  while($rr=$DB->fetch_assoc()){
			$order_list[] = $rr;
		  }
		  foreach($order_list as $rsOrder){
		  //获取所有分销商列表
			$ds_list = Dis_Account::with('User')
			->where(array('Users_ID' => $_SESSION["Users_ID"],'User_ID' => $rsOrder['User_ID']))
			->get(array('User_ID'))
			->toArray();
			
			$ds_list_dropdown = array();
			foreach($ds_list as $key=>$item){
				if(!empty($item['user'])){
					$ds_list_dropdown[$item['User_ID']] = $item['user']['User_NickName'];
				}
			}
		  ?>
          <tr>
            <td nowrap="nowrap"><input type="checkbox" name="OrderID[]" value="<?php echo $rsOrder["Order_ID"];?>" /></td>
            <td nowrap="nowrap"><?php echo $rsOrder["Order_ID"] ?></td>
            <td nowrap="nowrap"><?php echo date("Ymd",$rsOrder["Order_CreateTime"]).$rsOrder["Order_ID"] ?></td>
			<td nowrap="nowrap"><?=$order_type_arr[$rsOrder["Order_Type"]]?></td>
            <td nowrap="nowrap">
			<?php
			if($rsOrder["Owner_ID"] == 0 ){
				echo '无';
			}else{
				if(isset($ds_list_dropdown[$rsOrder["Owner_ID"]])){
					echo $ds_list_dropdown[$rsOrder["Owner_ID"]];
				}else{
					echo '无昵称';
				}
			}
			?></td>           
            <td nowrap="nowrap">￥<?php echo $rsOrder["Order_TotalPrice"] ?><?php echo $rsOrder["Back_Amount"]>0 && $rsOrder['Is_Backup']==1? '<br /><font style="text-decoration:line-through; color:#999">&nbsp;退款金额：￥'.$rsOrder["Back_Amount"].'&nbsp;</font>' : "";?></td>            
            <td nowrap="nowrap"><?php if(($rsOrder["Order_TotalPrice"]<=$rsOrder["Back_Amount"] || $rsOrder['Order_Status']==4) && $rsOrder['Is_Backup']==1){?><font style="color:#f00; ">已退款</font><?php }else{?><?php echo $Order_Status[$rsOrder["Order_Status"]] ?><?php }?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsOrder["Order_CreateTime"]) ?></td>
            <td class="last" nowrap="nowrap">
            <a href="<?php echo $rsOrder["Order_IsVirtual"]==1 ? 'virtual_' : '';?>orders_view.php?OrderID=<?php echo $rsOrder["Order_ID"] ?>">[详情]</a></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <div style="height:10px; width:100%;"></div>
       <label style="display:block; width:120px; border-radius:5px; height:32px; line-height:30px; background:#3AA0EB; color:#FFF; text-align:center; font-size:12px; cursor:pointer">打印发货单</label>
       <input type="hidden" name="templateid" value="" />
      </form>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
</div>
</body>
</html>