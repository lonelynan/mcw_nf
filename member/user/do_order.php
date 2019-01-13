<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

if ($_GET) {
	if (isset($_GET['act']) && $_GET['act'] == 'proChange') {
		$pid = intval($_GET['pid']);
		if (empty($pid)) {
			echo json_encode(array('status'=>0,'info'=>'未找到此产品'));
			exit;
		}
		$productInfo = $DB->GetRs('shop_products','Products_ID,Products_PriceX','WHERE Products_ID = '.$pid);
		if (empty($productInfo)) {
			echo json_encode(array('status'=>0,'info'=>'未找到此产品'));
			exit;
		}
		$price = $productInfo['Products_PriceX'];
		echo json_encode(array('status'=>1,'data'=>$price));
		exit;
	}
}
if($_POST)
{
	$User_ID = intval($_POST['User_ID']);
	if(empty($User_ID)){
		echo '<script>alert("缺少必要的参数");</script>';
		exit;
	}
	if(!is_numeric($_POST['price']) || empty($_POST['price'])) {
		echo '<script>alert("订单价格不能为空且只能为整数"); history.back();</script>';
		exit;
	}
	if(!is_numeric($_POST['Products_ID']) || empty($_POST['Products_ID'])) {
		echo '<script>alert("没有产品！"); history.back();</script>';
		exit;
	}
	$Products_ID = intval($_POST['Products_ID']);
	$price = trim($_POST['price']);
	$rsProducts = $DB->GetRs('shop_products','Products_JSON,Products_Name,Products_ID,Products_PriceX,Products_Integration,Products_PriceY,Products_Weight,Products_Shipping,Products_Business,Shipping_Free_Company,Products_IsShippingFree,Products_IsPaysBalance,nobi_ratio,platForm_Income_Reward,area_Proxy_Reward,sha_Reward,Products_Profit,Biz_ID','WHERE Products_ID = '.$Products_ID);
	$userInfo = $DB1->getRs('user','User_ID,Is_Distribute,Owner_Id','WHERE User_ID='.$User_ID);
	if (!empty($userInfo['Is_Distribute'])) {
		$realownerid = $User_ID;
	} else {
		$realownerid = $userInfo['Owner_Id'];
	}
	//产品图片
	$JSON = json_decode($rsProducts['Products_JSON'], true);//产品图片
	$CartList[$Products_ID][] = array(
			"ProductsName" => $rsProducts["Products_Name"],
			"ImgPath" => $JSON["ImgPath"][0],
			"ProductsPriceX" => $price,
			"ProductsPriceY" => $rsProducts["Products_PriceY"],
			"ProductsWeight" => $rsProducts["Products_Weight"],
			"Products_Shipping" => $rsProducts["Products_Shipping"],
			"Products_Business" => $rsProducts["Products_Business"],
			"Shipping_Free_Company" => $rsProducts["Shipping_Free_Company"],
			"IsShippingFree" => $rsProducts["Products_IsShippingFree"],
			"Products_IsPaysBalance" => $rsProducts["Products_IsPaysBalance"],
			"OwnerID" => $realownerid,
			"ProductsIsShipping" => $rsProducts["Products_IsShippingFree"],
			"Qty" => 1,
			"spec_list" => '',
			"Property" => array(),
			"nobi_ratio" => $rsProducts["nobi_ratio"],
			"platForm_Income_Reward" => $rsProducts["platForm_Income_Reward"],
			"area_Proxy_Reward" => $rsProducts["area_Proxy_Reward"],
			"sha_Reward" => $rsProducts["sha_Reward"],
			"Products_Profit" => $rsProducts["Products_Profit"]
		);	
	
	$Data = array(
		"Users_ID" => $_SESSION['Users_ID'],
		"User_ID" => $User_ID,
		"Order_IsVirtual" => 1,
		"Order_IsRecieve" => 1,
		"Address_Mobile" => $_POST ["Mobile"],
		'Owner_ID' => $realownerid,
		"Order_Status" => 1,
		"Order_Type" => "shop",
		"Order_TotalPrice" => $price,		
		"Order_TotalAmount" => $price,
		"Integral_Get" => $rsProducts['Products_Integration'],
		"Biz_ID" => $rsProducts['Biz_ID'],
		"Order_CartList" => json_encode($CartList,JSON_UNESCAPED_UNICODE),
		"Order_PaymentMethod" => '后台手动下单',
		"Order_CreateTime" => time(),
		"addtype" => 1,//后台添加
		
	);
	//mysql_query("BEGIN");	
	$Flag_a = $DB->Add("user_order",$Data);
	$neworderid = $DB->insert_id();
	Dis_Record::observe(new DisRecordObserver());
	require_once($_SERVER["DOCUMENT_ROOT"].'/include/support/distribute_helpers.php');
	if($userInfo['Owner_Id'] > 0) {
		add_distribute_record($_SESSION['Users_ID'],$User_ID,$price,$Products_ID,1,$neworderid,0);
	}
	
	require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/pay_order.class.php');
	require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/flow.php');
	$pay_order = new pay_order($DB, $neworderid);
	$Data = $pay_order->make_pay();
	
	if($Flag_a && $Data['status'] ==1){	 
		//mysql_query('commit');
		echo '<script language="javascript">alert("生成成功");parent.location.reload();</script>';
		exit;
	}else{	
		//mysql_query("ROLLBACK");
		echo '<script language="javascript">alert("生成失败");history.go(0);</script>';
		exit;
	}
	 
}else{
	if(empty($_GET['uid'])){
		echo '<script>alert("缺少必要的参数");  history.go(0) ;</script>';
		exit;
	}
	 
	$userInfo = $DB->getRs('user','User_Mobile,User_NickName','WHERE Users_ID="'.$_SESSION['Users_ID'].'" and User_ID = '.$_GET['uid']);
	$username = !empty($userInfo['User_Mobile'])?$userInfo['User_Mobile']:$userInfo['User_NickName'];
	$DB->get('shop_products','Products_ID,Products_Name','WHERE Users_ID="'.$_SESSION['Users_ID'].'" and Products_IsVirtual = 1');
	$productList = $DB->toArray();
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
<script type="text/javascript" src="/third_party/uploadify/jquery.uploadify.min.js"></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
	<div class="iframe_content">
		<link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
		<script type='text/javascript' src='/static/member/js/shop.js'></script> 
		<script type='text/javascript'>
$(document).ready(function(){
	shop_obj.category_init();
});
</script>
		
		<div id="products" class="r_con_wrap"> 
			<script type='text/javascript' src='/static/js/plugin/dragsort/dragsort-0.5.1.min.js'></script>
			<link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
			<script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
			<script language="javascript">
				//$(document).ready(shop_obj.products_category_init);
			</script>
			<div class="category">
				<div class="m_righter" style="margin-left:0px;">
					<form action="do_order.php" name="category_form" id="category_form" method="post">
						<div class="opt_item">
							<label>会员名称：</label>
							<span class="input">
							<input type="text" disabled value="<?php echo $username?>" class="form_input" size="15" maxlength="30" notnull /> 
							<input type="hidden" name="Mobile" value="<?php echo $userInfo['User_Mobile']?>" class="form_input" size="15" maxlength="30" notnull />
							<input type="hidden" name="User_ID" value="<?php echo $_GET['uid']?>" class="form_input" size="15" maxlength="30" notnull />
							<font class="fc_red">*</font></span>
							 
							</span>
							<div class="clear"></div>
						</div>
						
						<div class="opt_item">
							<label>产品名称：</label>
							<span class="input">
							<select name="Products_ID" id="proChange">
							<option value="0">请选择产品</option>
							<?php
							if(!empty($productList)) {
								foreach($productList as $k=>$v){ 
									echo '<option value="'.$v['Products_ID'].'">'.$v['Products_Name'].'</option>';
								}
							}
							?>
							
							</select>
							
							<font class="fc_red">*</font>只显示订单流程为其他类型的产品
							</span>
							<div class="clear"></div>
						</div>
						<div class="opt_item">
							<label>订单价格：</label>
							<span class="input">
							<input type="text" name="price" value="" id="price" class="form_input" size="15" maxlength="30" notnull />
							<font class="fc_red">*</font>可根据实际情况进行更改</span>
							<div class="clear"></div>
						</div><br>
						 
						 
						 
						<div class="opt_item">
							<label></label>
							<span class="input">
							<input type="submit" class="btn_green btn_w_120" name="submit_button" value="生成订单" /></span>
							<div class="clear"></div>
						</div>
					</form>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<script>
$("#proChange").change(function(){
	var pid = $(this).val();
	if(pid == 0) {
		$("#price").val('');
		return false;
	}
	$.get('?',{pid:pid,act:'proChange'},function(res){
		if(res.status == 1){
			var price = res.data;
			$("#price").val(price);
		} else {
			alert(res.info);
		}
	},'json')
	 
	
})
</script>
</body>
</html>