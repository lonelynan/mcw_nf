<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo 'error';
	exit;
}
$type=empty($_REQUEST["type"])?"":$_REQUEST["type"];
if($type=="check"){
	if(isset($_POST["itemid"])){
		$itemid=$_POST["itemid"];
	}else{
		echo 'error';
		exit;
	}
	if(isset($_POST["prizeid"])){
		$prizeid=$_POST["prizeid"];
	}else{
		echo 'error';
		exit;
	}
	$item = $DB->GetRs("zhongchou_project","*","where usersid='".$UsersID."' and itemid=$itemid");
	if(!$item){
		$Data=array(
			"status"=>0,
			"msg"=>"项目不存在"
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}else{
		if($item["fromtime"]>time()){
			$Data=array(
				"status"=>0,
				"msg"=>"项目未开始"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		if($item["totime"]<time()){
			$Data=array(
				"status"=>0,
				"msg"=>"项目已结束"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		$prize = $DB->GetRs("zhongchou_prize","*","where usersid='".$UsersID."' and projectid=$itemid and prizeid=$prizeid and money=".$_POST["money"]);
		if(!$prize){
			$Data=array(
				"status"=>0,
				"msg"=>"该项目与支持金额不匹配"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	}
	$is_shipping = $_POST["is_shipping"];
	$total_price = $prize["money"];
	$Data=array(
		"Users_ID"=>$UsersID,
		"User_ID"=>$_SESSION[$UsersID."User_ID"]
	);
	if($is_shipping==1){//物流检测
		if(isset($_POST["AddressID"])){
			$AddressID=$_POST["AddressID"];
		}else{
			$msg=array(
				"status"=>0,
				"msg"=>"请填写联系人信息"
			);
			echo json_encode($msg,JSON_UNESCAPED_UNICODE);
			exit;
		}
		
		$rsAddress=$DB->GetRs("user_address","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."' and Address_ID='".$AddressID."'");
		
		$Data["Address_Name"] = $rsAddress['Address_Name'];
		$Data["Address_Mobile"] = $rsAddress['Address_Mobile'];
		$Data["Address_Province"] = $rsAddress['Address_Province'];
		$Data["Address_City"] = $rsAddress['Address_City'];
		$Data["Address_Area"] = $rsAddress['Address_Area'];
		$Data["Address_Detailed"] = $rsAddress['Address_Detailed'];
		$Data["Users_ID"] = $UsersID;
		$Data["User_ID"] = $_SESSION[$UsersID."User_ID"];
		
		//运费计算
		$City_Code = $rsAddress['Address_City'];
		$Shipping_ID = !empty($_POST['Shipping_ID'])?$_POST['Shipping_ID']:0;
		if(!empty($Shipping_ID)){
			$info_array = array(
				"qty"=>1,
				"weight"=>$prize["weight"],
				"money"=>$prize["money"]
			);
			$Data["Order_ShippingID"] = $Shipping_ID;
			$rsbizConfig = $DB->GetRs("biz", "*", "where Users_ID='" . $UsersID . "' AND Biz_ID={$item['Biz_ID']}");
      $rsConfig = array_merge($rsConfig, $rsbizConfig);

			$shipping_company_dropdown = get_front_shiping_company_dropdown($UsersID,$rsConfig);
			if(empty($shipping_company_dropdown[$Shipping_ID])){
				$msg=array(
					"status"=>0,
					"msg"=>"物流不存在"
				);
				echo json_encode($msg,JSON_UNESCAPED_UNICODE);
				exit;
			}
			$total_shipping_fee = getShipFee($Shipping_ID,$UsersID,$item['Biz_ID'],$prize['weight']);

			$total_price = $total_price + $total_shipping_fee;
			$shipping_temp = array(
				"Express"=>$shipping_company_dropdown[$Shipping_ID],
				"Price"=> ((int)($total_shipping_fee*100))/100
			);
			$Data["Order_Shipping"] = json_encode($shipping_temp,JSON_UNESCAPED_UNICODE);
		}		
	}
	$cartlist = [
	   $item['Biz_ID'] => [
	        'project' => $item,
	        'prize' => $prize
	   ]
	];
	$Data["Order_Type"]="zhongchou_".$itemid;
	$Data["Order_Remark"]=$_POST["Remark"];
	$Data["Order_CartList"]=json_encode($cartlist , JSON_UNESCAPED_UNICODE);
	$Data["Order_TotalPrice"] = $Data["Order_TotalAmount"] = $total_price;
	$Data["Order_CreateTime"]=time();
	$Data["Order_Status"]=1;
	$Data["Order_PaymentMethod"] = "微支付";
	$Data["Biz_ID"] = $item['Biz_ID'];
	$Flag=$DB->Add("user_order",$Data);
	$neworderid = $DB->insert_id();
	if($Flag){		
		$url="/api/".$UsersID."/zhongchou/payment/".$neworderid."/";
		$Data=array(
			"status"=>1,
			"url"=>$url
		);
	}else{
		$Data=array(
			"status"=>0,
			"msg"=>"服务器繁忙，请稍候再试"
		);
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
}elseif($type=="checkpay"){
	if(isset($_POST["itemid"])){
		$itemid=$_POST["itemid"];
	}else{
		echo 'error';
		exit;
	}
	$item = $DB->GetRs("zhongchou_project","*","where usersid='".$UsersID."' and itemid=$itemid");
	if(!$item){
		$Data=array(
			"status"=>0,
			"msg"=>"项目不存在"
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}else{
		if($item["fromtime"]>time()){
			$Data=array(
				"status"=>0,
				"msg"=>"项目未开始"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
		if($item["totime"]<time()){
			$Data=array(
				"status"=>0,
				"msg"=>"项目已结束"
			);
			echo json_encode($Data,JSON_UNESCAPED_UNICODE);
			exit;
		}
	}
	$money = empty($_POST["money"]) ? 0 : floatval($_POST["money"]);
	if($money<=0){
		$Data=array(
			"status"=>0,
			"msg"=>"支持金额必须大于零"
		);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	$Data["Users_ID"]=$UsersID;
	$Data["User_ID"]=$_SESSION[$UsersID."User_ID"];
	$Data["Order_Type"]="zhongchou_".$itemid;
	$Data["Order_TotalPrice"] = $Data["Order_TotalAmount"] = $money;
	$Data["Order_CreateTime"]=time();
	$Data["Order_Status"]=1;
	$Data["Order_PaymentMethod"] = "微支付";
	$Data["Biz_ID"] = $item['Biz_ID'];
	$Flag=$DB->Add("user_order",$Data);
	
	$neworderid = $DB->insert_id();
	if($Flag){		
		$url="/api/".$UsersID."/zhongchou/payment/".$neworderid."/";
		$Data=array(
			"status"=>1,
			"url"=>$url
		);
	}else{
		$Data=array(
			"status"=>0,
			"msg"=>"服务器繁忙，请稍候再试"
		);
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
}
?>