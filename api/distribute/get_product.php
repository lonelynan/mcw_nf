<?php 
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

$dis_level = get_dis_level($DB,$UsersID);

$rs_distribute = $DB->GetRs("distribute_account","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]);

$TypeID=empty($_GET["TypeID"])?0:$_GET["TypeID"];	
if(isset($_GET['OpenID'])){
	$_SESSION[$UsersID.'OpenID']=$_GET['OpenID'];
	header("location:/api/".$UsersID."/user/get_product/".(empty($TypeID)?'':$TypeID.'/')."?wxref=mp.weixin.qq.com");
	exit;
}else{
	if(empty($_SESSION[$UsersID.'OpenID'])){
		$_SESSION[$UsersID.'OpenID']=session_id();
	}
}
if(!strpos($_SERVER['REQUEST_URI'],"mp.weixin.qq.com")){
	header("location:?wxref=mp.weixin.qq.com");
}
$rsConfigs=$DB->GetRs("user_config","*","where Users_ID='".$UsersID."'");
if(isset($_SESSION[$UsersID."User_ID"])){
	$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]);
}else{
	$_SESSION[$UsersID."HTTP_REFERER"]="/api/".$UsersID."/user/get_product/";
	header("location:/api/".$UsersID."/user/login/?wxref=mp.weixin.qq.com");
}

$reorders = $DB->Get("user_get_orders","*", "where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]);
while($reorders = $DB->fetch_assoc()){
	$user_get_orders[] = $reorders;
}
if(!empty($user_get_orders)){
	foreach($user_get_orders as $key=>$value){
		$Order_Level[] = $value['Order_Level']; 
	}
}

 // echo '<pre>'; print_R($rsConfig); exit;

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>会员中心</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/user.css' rel='stylesheet' type='text/css' />

<link rel="stylesheet" href="/static/css/font-awesome.css">
<link href="/static/api/distribute/css/style.css" rel="stylesheet">
<link href="/static/api/distribute/css/distribute_center.css" rel="stylesheet">


<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/js/user.js'></script>
<script type='text/javascript' src='/static/api/js/user_peas.js'></script>
<style>
	.Dis_span{ height: 40px; text-align: center;display: block;line-height: 40px;background: #eee;font-size: 16px;font-weight: bold;}
</style>
</head>

<body>
<div class="pop_form">
<form id="peas_form">
    <span>领取商品</span>
    <div class="address">
      <span>联系人信息</span>
      <ul>
	  
      <?php $DB->get("user_address","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."'");
		$i=0;
		//$address_list = $DB->toArray($rsAddress);
		
		$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/areaduihuan.js');
		$area_array = json_decode($area_json,TRUE);
		$province_list = $area_array[0];
		while($rsAddress=$DB->fetch_assoc()){ 
			
			$City = $area_array['0,'.$rsAddress['Address_Province']][$rsAddress['Address_City']];
			$Area = $area_array['0,'.$rsAddress['Address_Province'].','.$rsAddress['Address_City']][$rsAddress['Address_Area']];
			echo '<li> <span class="lbar">
          <input type="radio" name="AddressID" value="'.$rsAddress["Address_ID"].'" id="address-'.$rsAddress["Address_ID"].'"'.($i==0?" checked":"").' />
          </span> <span class="rbar">
          <label for="address-'.$rsAddress["Address_ID"].'">'.$area_array[0][$rsAddress["Address_Province"]].$City.$Area.$rsAddress["Address_Detailed"].'【'.$rsAddress["Address_Name"].'，'.$rsAddress["Address_Mobile"].'】</label>
          </span> </li>
        <li>';
			$i++;
			
		}?>
        
          <input type="radio" name="AddressID" id="new-address" value="0"<?php echo $i==0?" checked":"" ?> />
          <label for="new-address">使用新的联系人信息</label>
        </li>
      </ul>
      <dl>
        <dd>
          <input type="text" name="Name" value="" placeholder="姓名" notnull />
        </dd>
        <dd>
          <input type="text" name="Mobile" value="" pattern="[0-9]*" placeholder="手机" notnull />
        </dd>
		
		<dd>
          <select name="Province" notnull>
          </select>
          <select name="City" notnull>
          </select>
          <select name="Area" notnull>
          </select>
          <script type='text/javascript' src='/static/js/plugin/pcas/pcas.js?t=<?php echo time();?>'></script> 
          <script language="javascript">new PCAS('Province', 'City', 'Area', '', '', '');</script> 
        </dd>
        <dd>
          <input type="text" name="Detailed" value="" placeholder="详细地址" notnull />
        </dd>
      </dl>
    </div>
    <div class="btn">
      <input type="button" class="submit" value="确认领取" />
      <input type="button" class="cancel" value="取 消" />
      <div class="clear"></div>
    </div>
  </form>
</div>
<script language="javascript">$(document).ready(user_obj.peas_init);</script> 
<div id="peas">
  <div class="t_list"> <a href="/api/<?php echo $UsersID ?>/distribute/get_product/" class="<?php echo $TypeID==0?'c':'' ?>">我领取的商品</a> <a href="/api/<?php echo $UsersID ?>/distribute/get_product/1/" class="<?php echo $TypeID==1?'c':'' ?>">领取商品</a> </div> 
  <?php if($TypeID==0){
	  $DB->get("user_get_product`,`user_get_orders","user_get_product.Get_Name,user_get_product.Get_ImgPath,user_get_product.Get_Shipping,user_get_product.Get_BriefDescription,user_get_orders.Orders_ID,user_get_orders.Orders_Status,user_get_orders.Orders_Shipping,user_get_orders.Orders_ShippingID,user_get_orders.Orders_FinishTime","where user_get_product.Get_ID=user_get_orders.Get_ID and user_get_product.Users_ID='".$UsersID."' and user_get_orders.User_ID=".$_SESSION[$UsersID."User_ID"]." order by user_get_orders.Orders_Status asc,user_get_orders.Orders_ID desc");
	  while($rsGet=$DB->fetch_assoc()){
		echo '<div class="item">
		<span>【'.$rsGet['Get_Name'].'】</span>
		<div class="d"><img src="'.$rsGet['Get_ImgPath'].'" /></div>
		<span style="color:red;">'.(empty($rsGet['Get_Shipping'])?(empty($rsGet['Orders_Status'])?'未领取, 领取':'已领取, 领取'):(empty($rsGet['Orders_Status'])?'未发货, 领取':'已发货, 发货')).(!empty($rsGet['Orders_Shipping'])?$rsGet['Orders_Shipping'].':':'').(!empty($rsGet['Orders_ShippingID'])?$rsGet['Orders_ShippingID']:'').'<br>时间: '.date("Y-m-d H:i:s",$rsGet['Orders_FinishTime']).'</span>
		<span>'.$rsGet['Get_BriefDescription'].'</span>
	  </div>';
	  }
  }else{
	  $DB->query("SELECT * FROM user_get_product WHERE Users_ID='".$UsersID."' and Get_Qty>0 order by Get_MyOrder asc");
	  while($rsGet=$DB->fetch_assoc()){
			$get_product[] = $rsGet;
 	  } 
	  if(!empty($get_product)){ ?>
		  <?php foreach($dis_level as $k=>$val){
			    $distribute_order = $DB->GetRs('distribute_order','*','where Users_ID="'.$UsersID.'" and User_ID='.$_SESSION[$UsersID."User_ID"].'  and Level_ID= '.$val['Level_ID']);
				
				if($rs_distribute['Level_ID'] >= $val['Level_ID']){?>
				<span class="Dis_span"><?=$val['Level_Name']?></span>
				<?php foreach($get_product as $key=>$value){
					if($val['Level_ID'] == $value['Get_Level_ID']){
					?> 
					<div class="item">
						<span><?=$value['Get_Name']?></span>
						<div class="p"> <img src="<?=$value['Get_ImgPath']?>" />
						<?php if(isset($Order_Level) && in_array($value['Get_Level_ID'],$Order_Level)){?>
						  <div style="background-color: #ccc;">领取</div>
						<?php }elseif(!empty($distribute_order) && $distribute_order['Order_Present_type'] != 1){?>
							<div style="background-color: #ccc;">领取</div>
						<?php }else{?>
							<div class="get" GetID="<?=$value['Get_ID']?>" Shipping="<?=$value['Get_Shipping']?>" Get_Level_ID="<?=$value['Get_Level_ID']?>">领取</div>
						<?php } ?>
						</div>
						<h2>商品价格<font style="color:red;"><?=$value['shop_price']?> 元</font>  还剩<span><?=$value['Get_Qty']?></span>件</h2>
						<h3 class="photo_sun"><?php echo $value['Get_BriefDescription'];?></h3>
					</div>
				<?php } } ?>  
		   <?php }} ?>	  
	  <?php }?>
  <?php } ?>
</div>
<?php require_once '../shop/skin/distribute_footer.php';?>
</body>
</html>
<style>
.photo_sun img{width:100%;height:100%;}
</style>