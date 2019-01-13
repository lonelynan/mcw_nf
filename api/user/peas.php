<?php require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}
$TypeID=empty($_GET["TypeID"])?0:$_GET["TypeID"];	
if(isset($_GET['OpenID'])){
	$_SESSION[$UsersID.'OpenID']=$_GET['OpenID'];
	header("location:/api/".$UsersID."/user/peas/".(empty($TypeID)?'':$TypeID.'/')."?wxref=mp.weixin.qq.com");
	exit;
}else{
	if(empty($_SESSION[$UsersID.'OpenID'])){
		$_SESSION[$UsersID.'OpenID']=session_id();
	}
}
if(!strpos($_SERVER['REQUEST_URI'],"mp.weixin.qq.com")){
	header("location:?wxref=mp.weixin.qq.com");
}
$rsConfig=$DB->GetRs("user_config","*","where Users_ID='".$UsersID."'");
if(isset($_SESSION[$UsersID."User_ID"])){
	$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]);
}else{
	$_SESSION[$UsersID."HTTP_REFERER"]="/api/".$UsersID."/user/peas/";
	header("location:/api/".$UsersID."/user/login/?wxref=mp.weixin.qq.com");
}
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
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/js/user.js'></script>
<script type='text/javascript' src='/static/api/js/user_peas.js'></script>
</head>

<body>
<div class="pop_form">
  <form id="peas_form">
    <h1>豆豆领取商品</h1>
    <div class="input integral">领取本商品需要<span></span>豆豆</div>
    <div class="address">
      <h1>联系人信息</h1>
      <ul>
	  
      <?php $DB->get("user_address","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."'");
		$i=0;
		//$address_list = $DB->toArray($rsAddress);
		
		$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
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
          <select name="Province" id="Province" notnull>
          </select>
          <select name="City" id="City" notnull>
          </select>
          <select name="Area" id="Area" notnull>
          </select>
          <script type='text/javascript' src='/data/area_select.js'></script> 
          
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
  <div class="t_list"> <a href="/api/<?php echo $UsersID ?>/user/peas/" class="<?php echo $TypeID==0?'c':'' ?>">我领取的商品</a> <a href="/api/<?php echo $UsersID ?>/user/peas/1/" class="<?php echo $TypeID==1?'c':'' ?>">豆豆领取商品</a> </div> 
  <?php if($TypeID==0){
	  $DB->get("user_peas`,`user_peas_orders","user_peas.Peas_Name,user_peas.Peas_ImgPath,user_peas.Peas_Shipping,user_peas.Peas_BriefDescription,user_peas_orders.Orders_ID,user_peas_orders.Orders_Status,user_peas_orders.Orders_Shipping,user_peas_orders.Orders_ShippingID,user_peas_orders.Orders_FinishTime","where user_peas.Peas_ID=user_peas_orders.Peas_ID and user_peas.Users_ID='".$UsersID."' and user_peas_orders.User_ID=".$_SESSION[$UsersID."User_ID"]." order by user_peas_orders.Orders_Status asc,user_peas_orders.Orders_ID desc");
	  while($rsPeas=$DB->fetch_assoc()){
	  echo '<div class="item">
    <h1>【'.$rsPeas['Peas_Name'].'】</h1>
    <div class="d"><img src="'.$rsPeas['Peas_ImgPath'].'" /></div>
    <h2>'.(empty($rsPeas['Peas_Shipping'])?(empty($rsPeas['Orders_Status'])?'未领取, 领取':'已领取, 领取'):(empty($rsPeas['Orders_Status'])?'未发货, 领取':'已发货, 发货')).'时间: '.date("Y-m-d H:i:s",$rsPeas['Orders_FinishTime']).'</h2>
    <h3>'.$rsPeas['Peas_BriefDescription'].'</h3>
  </div>';
	  }
  }else{
	  $DB->query("SELECT Peas_ID,Peas_Name,Peas_ImgPath,Peas_Shipping,Peas_Integral,Peas_Qty,Peas_BriefDescription FROM user_peas WHERE Users_ID='".$UsersID."' and Peas_Qty>0 order by Peas_MyOrder asc");
	  while($rsPeas=$DB->fetch_assoc()){
		  echo '<div class="item">
    <h1>【'.$rsPeas['Peas_Name'].'】</h1>
    <div class="p"> <img src="'.$rsPeas['Peas_ImgPath'].'" />
      <div class="get" PeasID="'.$rsPeas['Peas_ID'].'" Shipping="'.$rsPeas['Peas_Shipping'].'" Integral="'.$rsPeas['Peas_Integral'].'">领取</div>
    </div>
    <h2>领取需<span>'.$rsPeas['Peas_Integral'].'</span>豆豆，还剩<span>'.$rsPeas['Peas_Qty'].'</span>件</h2>
    <h3>'.$rsPeas['Peas_BriefDescription'].'</h3>
  </div>';
	  }
  }
  ?>
</div>
<?php require_once('footer.php'); ?>
</body>
</html>