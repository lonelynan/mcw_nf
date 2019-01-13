<?php
require_once('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');

$_GET = daddslashes($_GET,1);

$psize = 10;
$condition = " as od left join pintuan_teamdetail as teamdetail on od.Order_ID=teamdetail.order_id ";
$condition .= " left join pintuan_team as team on teamdetail.teamid=team.id ";
$condition .= "where od.Users_ID='".$rsBiz["Users_ID"]."' and od.Order_Type='pintuan' and od.Biz_ID=".$_SESSION["BIZ_ID"];
if(isset($_GET["search"])){
	if($_GET["search"]==1){
		if(!empty($_GET["Keyword"])){
			$condition .= " and od.`".$_GET["Fields"]."` like '%".$_GET["Keyword"]."%'";
		}
		if(!empty($_GET["OrderNo"])){
			$OrderID = substr($_GET["OrderNo"],8);
			$OrderID =  empty($OrderID) ? 0 : intval($OrderID);
			$condition .= " and od.Order_ID=".$OrderID;
		}
		if(isset($_GET["Status"])){
			if($_GET["Status"]<>''){
				$condition .= " and od.Order_Status=".$_GET["Status"];
			}
		}
		if(!empty($_GET["AccTime_S"])){
			$condition .= " and od.Order_CreateTime>=".(strtotime($_GET["AccTime_S"])==-1 || strtotime($_GET["AccTime_S"])==false?0:strtotime($_GET["AccTime_S"]));
		}
		if(!empty($_GET["AccTime_E"])){
			$condition .= " and od.Order_CreateTime<=".(strtotime($_GET["AccTime_E"])==-1 || strtotime($_GET["AccTime_E"])==false?0:strtotime($_GET["AccTime_E"]));
		}
		if(!empty($_GET["psize"])){
			$psize = intval($_GET["psize"]);
		}
	}
}

$condition .= " order by od.Order_CreateTime desc, od.Order_ID desc";
$templates = array();
$DB->Get("shop_shipping_print_template","title,itemid","where usersid='".$rsBiz["Users_ID"]."' and bizid=".$_SESSION["BIZ_ID"]." and enabled=1");
while($rsTemplate = $DB->fetch_assoc()){
	$templates[] = $rsTemplate;
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
		$(function(){
	  $("#checkall").click(function(){
	     if($(this).prop("checked")==true){
	      $("input[name='OrderID[]']").attr("checked","checked");
	      }else{
	        $("input[name='OrderID[]']").removeAttr("checked");
	      }
	  });
	  $("#print").click(function(){
	      var ids = new Array;
	      $("input[name='OrderID[]']:checked").each(function(){
	          ids.push($(this).val());
	      });
	      if(ids.length<1)
	      {
	          alert("至少选择1个");
	          return false;
	      }
	      var idlist = "";
	      for(var i=0;i<ids.length;i++)
	      {
	          if(i==ids.length-1){
	              idlist +=ids[i];
	          }else{
	              idlist +=ids[i]+",";
	          }
	          
	      }
	      location.href = "/biz/orders/order_print.php?OrderID="+idlist;
	  });
	});
	</script>
    <div id="orders" class="r_con_wrap">
      <form class="search" id="search_form" method="get" action="?">
        <select name="Fields">
			<option value='Order_CartList'>商品</option>
			<option value='Address_Name'>购买人</option>
			<option value='Address_Mobile'>购买手机</option>
			<option value='Address_Detailed'>收货地址</option>
		</select>
        <input type="text" name="Keyword" value="" class="form_input" size="15" />&nbsp;
		订单号：<input type="text" name="OrderNo" value="" class="form_input" size="15" />&nbsp;
        订单状态：
        <select name="Status">
          <option value="">--请选择--</option>
          <option value='0'>待确认</option>
          <option value='1'>待付款</option>
          <option value='2'>已付款</option>
          <option value='3'>已发货</option>
          <option value='4'>已完成</option>
		  <option value='5'>申请退款中</option>
        </select>
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
			<td width="10%" nowrap="nowrap">会员号</td>
            <td width="10%" nowrap="nowrap">微信昵称(姓名)</td>
            <td width="11%" nowrap="nowrap">金额</td>
            <td width="11%" nowrap="nowrap">配送方式</td>
            <td width="11%" nowrap="nowrap">订单状态</td>
            <td width="12%" nowrap="nowrap">时间</td>
            <td width="10%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
          <?php
		  $i=0;
		  $DB->getPage("user_order","od.*,team.teamnum,team.teamstatus",$condition,$psize);
		  $Order_Status=array("待确认","待付款","已付款","已发货","已完成","申请退款中");
		  /*获取订单列表牵扯到的分销商*/
		  $order_list = array();
		  while($rr=$DB->fetch_assoc()){
			$order_list[] = $rr;
		  }
		  foreach($order_list as $rsOrder){
			 //获取所有分销商列表
			$ds_list = User::where(array('Users_ID' => $_SESSION["Users_ID"],'User_ID' => $rsOrder['User_ID']))
			->get(array('User_ID','User_NickName','User_No'))
			->toArray();
			
			$ds_list_dropdown = array();
			foreach($ds_list as $key=>$item){
				if(!empty($item)){
					$ds_list_dropdown[$item['User_ID']]['User_NickName'] = $item['User_NickName'];
					$ds_list_dropdown[$item['User_ID']]['User_No'] = $item['User_No'];
				}
			}
		  $Shipping=json_decode(htmlspecialchars_decode($rsOrder["Order_Shipping"]),true);
		  ?>
          <tr>
            <td nowrap="nowrap"><input type="checkbox" name="OrderID[]" value="<?php echo $rsOrder["Order_ID"];?>" /></td>
            <td nowrap="nowrap"><?php echo $rsOrder["Order_ID"] ?></td>
            <td nowrap="nowrap"><?php echo date("Ymd",$rsOrder["Order_CreateTime"]).$rsOrder["Order_ID"] ?></td>
           <td><?php echo $ds_list_dropdown[$rsOrder["User_ID"]]['User_No'] ?></td>
		  <td><?=$ds_list_dropdown[$rsOrder["User_ID"]]['User_NickName']?><?php echo !empty($rsOrder["Address_Name"]) ? '('.$rsOrder["Address_Name"].')' : '' ?></td>
            <td nowrap="nowrap">￥<?php echo $rsOrder["Order_TotalPrice"] ?><?php echo $rsOrder["Back_Amount"]>0 && $rsOrder['Is_Backup']==1? '<br /><font style="text-decoration:line-through; color:#999">&nbsp;退款金额：￥'.$rsOrder["Back_Amount"].'&nbsp;</font>' : "";?></td> 
            <td nowrap="nowrap"><?php		
				if(empty($Shipping)){
					echo "免运费";
				}else{
					if(isset($Shipping["Express"])){
						echo $Shipping["Express"];
					}else{
						echo '无配送信息';
					}
				}
			?></td>	
            <td nowrap="nowrap"><?php if(($rsOrder["Order_TotalPrice"]<=$rsOrder["Back_Amount"] || $rsOrder['Order_Status']==4) && $rsOrder['Is_Backup']==1){?><font style="color:#f00; ">已退款</font><?php }else{?><?php echo $Order_Status[$rsOrder["Order_Status"]] ?><?php }?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsOrder["Order_CreateTime"]) ?></td>
            <td class="last" nowrap="nowrap">
            <a href="<?php echo $rsOrder["Order_IsVirtual"]==1 ? 'virtual_' : '';?>orders_view.php?OrderID=<?php echo $rsOrder["Order_ID"] ?>">[详情]</a>
            <?php if($rsOrder["Order_Status"]==2 && $rsOrder["Order_IsVirtual"] != 1 && $rsOrder["Order_TotalPrice"]>=$rsOrder["Back_Amount"]){ $backstatus=0;?>
			<?php
				if($rsOrder["Is_Backup"]==1){
					$rsBack = $DB->GetRs("user_back_order","Back_Status","where Order_ID=".$rsOrder["Order_ID"]);
					$backstatus = $rsBack ? $rsBack["Back_Status"] : 0;
				}
				if($backstatus<1 ||  $rsOrder["Order_Status"]==2){
			?>
			<?php if($rsOrder["Order_TotalPrice"]>$rsOrder["Back_Amount"] || $rsOrder["Order_Status"]==2){?>
			<?php if ($rsOrder['teamstatus'] == 1) { ?>
            <a href="orders_send.php?OrderID=<?php echo $rsOrder["Order_ID"] ?>">[发货]</a><br />
			<?php } ?>
			<?php }?>
			<?php }?>
            <a href="javascript:void(0);" class="send_print" ret="<?php echo $rsOrder["Order_ID"];?>">[打印发货单]</a>
            <?php } elseif ($rsOrder["Order_Status"]==0) { ?>
            <a href="orders_confirm.php?OrderID=<?php echo $rsOrder["Order_ID"] ?>">[确认订单]</a>
            <?php } elseif ( $rsOrder["Order_Status"] == 1 && $rsOrder["Order_PaymentMethod"] == '线下支付' ) { ?>
            <a href="orders_confirm.php?OrderID=<?php echo $rsOrder["Order_ID"] ?>&offline=1">[确认]</a>
            <?php } ?>
            </td>
          </tr>
          <?php $i++;}?>
        </tbody>
      </table>
      <div style="height:10px; width:100%;"></div>
       <label style="display:block; width:120px; border-radius:5px; height:32px; line-height:30px; background:#3AA0EB; color:#FFF; text-align:center; font-size:12px; cursor:pointer" id="print">打印订单</label>
       <input type="hidden" name="templateid" value="" />
      </form>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
  
  <div id="select_template" class="lean-modal lean-modal-form">
      <div class="h" style="color:#FFF">选择运单模板<a class="modal_close" href="#"></a></div>
      <table width="80%" cellpadding="0" cellspacing="0" style="margin:8px auto 15px">
        <input type="hidden" id="linkid" value="0">
      	<tr>
            <td width="100" align="right" height="50">运单模板&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;<select id="templates" style="height:28px; line-height:28px; border:1px #dfdfdf solid">
         	<option value="">请选择运单模板</option>
            <?php foreach($templates as $temp){?>
            <option value="<?php echo $temp["itemid"];?>"><?php echo $temp["title"];?></option>
            <?php }?>
         </select>
         </td>
        </tr>
        <tr>
            <td height="50">&nbsp;</td>
        	<td>
            	&nbsp;&nbsp;<label style="display:block; width:120px; border-radius:5px; height:32px; line-height:30px; background:#3AA0EB; color:#FFF; text-align:center; font-size:12px; cursor:pointer">确定</label>
            </td>
        </tr>
       </table>
  </div>
  
</div>
</body>
</html>