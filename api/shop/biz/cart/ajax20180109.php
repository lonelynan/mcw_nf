<?php require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo 'error';
	exit;
}

$action=empty($_REQUEST["action"])?"":$_REQUEST["action"];

if(empty($action)){
	//$_SESSION["CartList"]="";
	$Property = isset($_POST["Property"]) ? $_POST["Property"] : array();
	
	$rsProducts=$DB->GetRs("weicbd_products","*","where Users_ID='".$UsersID."' and Products_ID=".$_POST["ProductsID"]);

	$JSON=json_decode($rsProducts['Products_JSON'],true);
	//如果购物车为空
	
	if(empty($_SESSION["CartList"])){
		$CartList[$_POST["ProductsID"]][]=array(
			"ProductsName"=>$rsProducts["Products_Name"],
			"ImgPath"=>empty($JSON["ImgPath"])?"":$JSON["ImgPath"][0],
			"ProductsPriceX"=>$rsProducts["Products_PriceX"],
			"ProductsPriceY"=>$rsProducts["Products_PriceY"],
			"Qty"=>$_POST["Qty"],
			"Products_Count"=>$rsProducts["Products_Count"],
			"Property"=>$Property
		);
	
		$_SESSION["CartList"]=json_encode($CartList,JSON_UNESCAPED_UNICODE);
	}else{
		//如果购物车中已有商品
		$CartList=json_decode($_SESSION["CartList"],true);
		//如果购物车中没有与要添加商品具有相同ProductsID的产品
		if(empty($CartList[$_POST["ProductsID"]])){
			$CartList[$_POST["ProductsID"]][]=array(
				"ProductsName"=>$rsProducts["Products_Name"],
				"ImgPath"=>empty($JSON["ImgPath"])?"":$JSON["ImgPath"][0],
				"ProductsPriceX"=>$rsProducts["Products_PriceX"],
				"ProductsPriceY"=>$rsProducts["Products_PriceY"],
				"Qty"=>$_POST["Qty"],
				"Products_Count"=>$rsProducts["Products_Count"],
				"Property"=>$Property
			);

			$_SESSION["CartList"]=json_encode($CartList,JSON_UNESCAPED_UNICODE);
		}else{
			//如果购物车中有与要添加商品具有相同ProductsID的产品
			$flag=true;
			foreach($CartList[$_POST["ProductsID"]] as $k=>$v){
				//array_diff_assoc 比较数组键值与键名,返回数组
				$array=array_diff_assoc($Property,$v["Property"]);
				if(empty($array)){
					$CartList[$_POST["ProductsID"]][$k]["Qty"]+=$_POST["Qty"];
					$_SESSION["CartList"]=json_encode($CartList,JSON_UNESCAPED_UNICODE);
					$flag=false;
				}
			}
			if($flag){
				$CartList[$_POST["ProductsID"]][]=array(
					"ProductsName"=>$rsProducts["Products_Name"],
					"ImgPath"=>empty($JSON["ImgPath"])?"":$JSON["ImgPath"][0],
					"ProductsPriceX"=>$rsProducts["Products_PriceX"],
					"ProductsPriceY"=>$rsProducts["Products_PriceY"],
					"ProductsWeight"=>$rsProducts["Products_Weight"],
					"Qty"=>$_POST["Qty"],
					"Products_Count"=>$rsProducts["Products_Count"],
					"Property"=>$Property
				);
				$_SESSION["CartList"]=json_encode($CartList,JSON_UNESCAPED_UNICODE);
			}

		}
	}
	$qty=0;
	$total=0;
	foreach($CartList as $key=>$value){
		foreach($value as $k=>$v){
			$qty+=$v["Qty"];
			$total+=$v["Qty"]*$v["ProductsPriceX"];
		}
	}
	$Data=array(
		"status"=>1,
		"qty"=>$qty,
		"total"=>$total
	);
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	
}elseif($action=="del"){
	$CartList=json_decode($_SESSION["CartList"],true);
	$k=explode("_",$_POST["CartID"]);
	unset($CartList[$k[0]][$k[1]]);
	$_SESSION["CartList"]=json_encode($CartList,JSON_UNESCAPED_UNICODE);
	$total=0;
	foreach($CartList as $key=>$value){
		foreach($value as $k=>$v){
			$total+=$v["Qty"]*$v["ProductsPriceX"];
		}
	}
	if(empty($total)){
		$_SESSION["CartList"]="";
	}
	$Data=array(
		"status"=>1,
		"total"=>$total
	);
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
}elseif($action=="update"){
	$CartList=json_decode($_SESSION["CartList"],true);
	$k=explode("_",$_POST["_CartID"]);
	$CartList[$k[0]][$k[1]]["Qty"]=$_POST["_Qty"];
	$price=$CartList[$k[0]][$k[1]]["ProductsPriceX"];
	$_SESSION["CartList"]=json_encode($CartList,JSON_UNESCAPED_UNICODE);
	$total=0;
	foreach($CartList as $key=>$value){
		foreach($value as $k=>$v){
			$total+=$v["Qty"]*$v["ProductsPriceX"];
		}
	}
	$Data=array(
		"status"=>1,
		"price"=>$price,
		"total"=>$total
	);
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
}elseif($action =="minus"){

	$CartList=json_decode($_SESSION["WeicbdList"],true);
	
	$Cart_ID = $_POST["CartID"];
	
	
	$item_qty = intval($CartList[$Cart_ID][0]["Qty"]);
	
	$CartList[$Cart_ID][0]["Qty"] = $item_qty - 1;
	
	$price=$CartList[$Cart_ID][0]["ProductsPriceX"];
	
	$_SESSION["WeicbdList"]=json_encode($CartList,JSON_UNESCAPED_UNICODE);

	$total=0;
	$qty=0;
	
	foreach($CartList as $key=>$value){
		foreach($value as $k=>$v){
			$qty+=$v["Qty"];
			$total+=$v["Qty"]*$v["ProductsPriceX"];
		}
	}
	
	$Data=array(
		"status"=>1,
		"qty"=>$qty,
		"total"=>$total
	);
	
	
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);

}elseif($action =="add"){
	$CartList=json_decode($_SESSION["CartList"],true);
	$k=explode("_",$_POST["CartID"]);
	$CartList[$k[0]][$k[1]]["Qty"]= $CartList[$k[0]][$k[1]]["Qty"]+1;
	$price=$CartList[$k[0]][$k[1]]["ProductsPriceX"];
	$_SESSION["CartList"]=json_encode($CartList,JSON_UNESCAPED_UNICODE);

	$total=0;
	foreach($CartList as $key=>$value){
		foreach($value as $k=>$v){
			$total+=$v["Qty"]*$v["ProductsPriceX"];
		}
	}
	$Data=array(
		"status"=>1,
		"price"=>$price,
		"total"=>$total
	);
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	

}elseif($action == 'favourite'){
	//检测用户是否登陆
	if(empty($_SESSION["User_ID"])){
		$_SESSION["HTTP_REFERER"]='http://'.$_SERVER['HTTP_HOST']."/api/".$UsersID."/shop/products/".$_POST['productId'].'/?wxref=mp.weixin.qq.com';
		$url = 'http://'.$_SERVER['HTTP_HOST']."/api/".$UsersID."/user/login/";
		/*返回值*/
		$Data=array(
			"status"=>0,
			"info"=>"您还为登陆，请登陆！",
			"url"=>$url
		);
	}else{

		$insertInfo = array('User_ID'=>$_SESSION['User_ID'],
							'Products_ID'=>$_POST['productId'],
							'IS_Attention'=>1);
		
		$Result=$DB->Add("user_favourite_products",$insertInfo);
		
			$Data=array(
			"status"=>1,
			"info"=>"收藏成功！",
			
			);
	}

	echo json_encode($Data,JSON_UNESCAPED_UNICODE);

}elseif($action=="check"){
	$CartList=json_decode($_SESSION["CartList"],true);
	if(count($CartList)>0){
		$Data=array(
			"status"=>1
		);
	}else{
		$Data=array(
			"status"=>0,
			"info"=>"购物车空的，赶快去逛逛吧！"
			);
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
}elseif($action=="checkout"){
	$AddressID=empty($_POST['AddressID'])?0:$_POST['AddressID'];
	if(empty($_POST['AddressID'])){
		//增加
		$Data=array(
			"Address_Name"=>$_POST['Name'],
			"Address_Mobile"=>$_POST["Mobile"],
			"Address_Province"=>$_POST["Province"],
			"Address_City"=>$_POST["City"],
			"Address_Area"=>$_POST["Area"],
			"Address_Detailed"=>$_POST["Detailed"],
			"Users_ID"=>$UsersID,
			"User_ID"=>$_SESSION["User_ID"]
		);
		$Flag=$DB->Add("user_address",$Data);
	}else{
		$rsAddress=$DB->GetRs("user_address","*","where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."' and Address_ID='".$AddressID."'");
		$Data=array(
			"Address_Name"=>$rsAddress['Address_Name'],
			"Address_Mobile"=>$rsAddress["Address_Mobile"],
			"Address_Province"=>$rsAddress["Address_Province"],
			"Address_City"=>$rsAddress["Address_City"],
			"Address_Area"=>$rsAddress["Address_Area"],
			"Address_Detailed"=>$rsAddress["Address_Detailed"],
			"Users_ID"=>$UsersID,
			"User_ID"=>$_SESSION["User_ID"]
		);
	}
	$Data["Order_Type"]="shop";
	$Data["Order_Remark"]=$_POST["Remark"];
	$Data["Order_Shipping"]=json_encode(empty($_POST["Shipping"]["Express"])?array():$_POST["Shipping"],JSON_UNESCAPED_UNICODE);
	$Data["Order_CartList"]=$_SESSION["CartList"];
	$total_price = $_POST["total_price"];
	$Data["Order_TotalAmount"]=$total_price+(empty($_POST["Shipping"]["Price"])?0:$_POST["Shipping"]["Price"]);
	if(isset($_POST["CouponID"])){//优惠券使用判断
		$r = $DB->GetRs("user_coupon_record","*","where Coupon_ID=".$_POST["CouponID"]." and Users_ID='".$UsersID."' and User_ID=".$_SESSION["User_ID"]." and Coupon_UseArea=1 and (Coupon_UsedTimes=-1 or Coupon_UsedTimes>0) and Coupon_StartTime<=".time()." and Coupon_EndTime>=".time()." and Coupon_Condition<=".$total_price);
		if($r){
			$Logs_Price=0;
			$Data["Coupon_ID"]=$_POST["CouponID"];
			if($r["Coupon_UseType"]==0 && $r["Coupon_Discount"]>0 && $r["Coupon_Discount"]<1){
				$total_price = $total_price * $r["Coupon_Discount"];
				$Logs_Price = $total_price * $r["Coupon_Discount"];
				$Data["Coupon_Discount"]=$r["Coupon_Discount"];
			}
			if($r['Coupon_UseType']==1 && $r['Coupon_Cash']>0){
				$total_price = $total_price - $r['Coupon_Cash'];
				$Logs_Price = $r['Coupon_Cash'];
				$Data["Coupon_Cash"] = $r["Coupon_Cash"];
			}
			if($r['Coupon_UsedTimes']>=1){
				$DB->Set("user_coupon_record","Coupon_UsedTimes=Coupon_UsedTimes-1","where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."' and Coupon_ID=".$_POST['CouponID']);
			}
			$item = $DB->GetRs("user","User_Name","where User_ID=".$_SESSION["User_ID"]);
			$Data1=array(
				'Users_ID'=>$UsersID,
				'User_ID'=>$_SESSION["User_ID"],
				'User_Name'=>$item["User_Name"],
				'Coupon_Subject'=>"优惠券编号：".$_POST["CouponID"],
				'Logs_Price'=>$Logs_Price,
				'Coupon_UsedTimes'=>$r['Coupon_UsedTimes']>=1?$r['Coupon_UsedTimes']-1:$r['Coupon_UsedTimes'],
				'Logs_CreateTime'=>time(),
				'Operator_UserName'=>"微商城购买商品"
				);
					
				$DB->Add("user_coupon_logs",$Data1);
		}
	}
	$Data["Order_TotalPrice"]=$total_price+(empty($_POST["Shipping"]["Price"])?0:$_POST["Shipping"]["Price"]);
	$Data["Order_CreateTime"]=time();
	$rsConfig=$DB->GetRs("shop_config","CheckOrder","where Users_ID='".$UsersID."'");
	$Data["Order_Status"]=$rsConfig["CheckOrder"]==1 ? 0 : 1;
	$Flag=$DB->Add("user_order",$Data);
	$neworderid = $DB->insert_id();
	if($Flag){
		$url=$rsConfig["CheckOrder"]==1 ? "/api/".$UsersID."/shop/cart/payment/".$neworderid."/":"/api/".$UsersID."/shop/member/status/1/";
		require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/message.func.php');
		user_order_tem($neworderid,2,"http://".$_SERVER["HTTP_HOST"].$url);
		$_SESSION["CartList"]="";
		$Data=array(
			"status"=>1,
			"url"=>$url
		);
	}else{
		$Data=array(
			"status"=>0
		);
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
}elseif($action=="payment"){
	$OrderID=empty($_POST['OrderID'])?0:$_POST['OrderID'];
	$rsOrder=$DB->GetRs("user_order","*","where Users_ID='".$UsersID."' and Order_ID='".$OrderID."'");
	$rsUser = $DB->GetRs("user","User_Money,User_PayPassword","where Users_ID='".$UsersID."' and User_ID=".$_SESSION['User_ID']);
	$PaymentMethod = array(
		"微支付"=>"1",
		"支付宝"=>"2",
		"线下支付"=>"3"
		);
		if($_POST['PaymentMethod']=="线下支付" || $rsOrder["Order_TotalPrice"]<=0){
			$Data=array(
			"Order_PaymentMethod"=>$_POST['PaymentMethod'],
			"Order_PaymentInfo"=>$_POST["PaymentInfo"],
			"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"],
			"Order_Status"=>1
			);
			$Status=1;
			$Flag=$DB->Set("user_order",$Data,"where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."' and Order_ID=".$OrderID);
			$url=empty($_POST['DefautlPaymentMethod'])?"/api/".$UsersID."/shop/member/status/".$Status."/":"/api/".$UsersID."/shop/member/detail/".$_POST['OrderID']."/";
			if($Flag){
				$Data=array(
				"status"=>1,
				"url"=>$url
				);
			}else{
				$Data=array(
				"status"=>0,
				"msg"=>'线下支付提交失败'
				);
			}
		}elseif($_POST['PaymentMethod']=="余额支付" && $rsUser["User_Money"]>=$rsOrder["Order_TotalPrice"]){//余额支付
			//增加资金流水
			if(!$_POST["PayPassword"]){
				$Data=array(
				"status"=>0,
				"msg"=>'请输入支付密码'
				);
			}elseif(md5($_POST["PayPassword"])!=$rsUser["User_PayPassword"]){
				$Data=array(
				"status"=>0,
				"msg"=>'支付密码输入错误'
				);
			}else{
				$Data=array(
				'Users_ID'=>$UsersID,
				'User_ID'=>$_SESSION['User_ID'],				
				'Type'=>0,
				'Amount'=>$rsOrder["Order_TotalPrice"],
				'Total'=>$rsUser['User_Money']-$rsOrder["Order_TotalPrice"],
				'Note'=>"商城购买支出 -".$rsOrder["Order_TotalPrice"]." (订单号:".$OrderID.")",
				'CreateTime'=>time()		
				);
				$Flag=$DB->Add('user_money_record',$Data);
				//更新用户余额
				$Data=array(
				'User_Money'=>$rsUser['User_Money']-$rsOrder["Order_TotalPrice"]				
				);
				$Flag=$DB->Set('user',$Data,"where Users_ID='".$UsersID."' and User_ID=".$_SESSION['User_ID']);
				$Data=array(
				"Order_PaymentMethod"=>$_POST['PaymentMethod'],
				"Order_PaymentInfo"=>"",
				"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"],
				'Order_Status'=>2			
				);
				$Flag=$DB->Set('user_order',$Data,"where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."' and Order_ID=".$OrderID);
				$url="/api/".$UsersID."/shop/member/status/2/";
				if($Flag){
					$Data=array(
					"status"=>1,
					"url"=>$url
					);
				}else{
					$Data=array(
					"status"=>0,
					"msg"=>'余额支付失败'
					);
				}
			}
		}else{//在线支付
			$Data=array(
			"Order_PaymentMethod"=>$_POST['PaymentMethod'],
			"Order_PaymentInfo"=>"",
			"Order_DefautlPaymentMethod"=>$_POST["DefautlPaymentMethod"]
			);
			$Flag=$DB->Set("user_order",$Data,"where Users_ID='".$UsersID."' and User_ID='".$_SESSION["User_ID"]."' and Order_ID=".$OrderID);
			$url="/api/".$UsersID."/shop/cart/pay/".$OrderID."/".$PaymentMethod[$_POST['PaymentMethod']]."/";
			if($Flag){
				$Data=array(
				"status"=>1,
				"url"=>$url
				);
			}else{
				$Data=array(
				"status"=>0,
				"msg"=>'在线支付出现错误'
				);
			}
		}

		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
}

?>