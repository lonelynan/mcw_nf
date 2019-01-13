<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}
if(isset($_GET["BizID"])){
	$BizID=$_GET["BizID"];
}else{
	echo '缺少必要的参数';
	exit;
}
//开始事务定义
$Flag=true;
$action = empty($_POST["action"]) ? '' : $_POST["action"];
if($action == "get_commit"){
	$condition = "where Users_ID='".$UsersID."' and Biz_ID=".$BizID." and Status=1 and MID='shop'";
	$pagesize = 10;
	$limit = " limit 0,".$pagesize;
	
	$itemsnum = $DB->GetRs("user_order_commit","count(*) as num",$condition);
	if(!empty($_POST["page"])){
		$limit = " limit ".(($_POST["page"]-1)*$pagesize).",".$pagesize;
	}
	
	$orderby = " order by CreateTime desc";
	$condition .= $orderby.$limit;
	$DB->Get("user_order_commit","Score,Product_ID,CreateTime,Note",$condition);
	$lists = $result = array();
	while($r=$DB->fetch_assoc()){
		$r["addtime"] = date("Y-m-d",$r["CreateTime"]);
		$lists[] = $r;
	}
	foreach($lists as $key=>$value){
		$products = $DB->GetRs("shop_products","Products_Name","where Products_ID=".$value["Product_ID"]." and Users_ID='".$UsersID."' and Biz_ID=".$BizID);
		$value["productsname"] = $products ? $products["Products_Name"] : '';
		$result[] = $value;
	}
	$pagesnum = 1;
	if($itemsnum["num"]>0){
		$pagesnum = $itemsnum["num"]%$pagesize==0 ? ($itemsnum["num"]/$pagesize) : (intval($itemsnum["num"]/$pagesize)+1);
	}
	$Data = array(
		"status"=>count($result)>0 ? 1 : 0,
		"items"=>$result,
		"pagenum"=>$pagesnum
	);
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}elseif($action == 'add_favourite'){
	if(empty($_SESSION[$UsersID."User_ID"] )) {
		$url = 'http://'.$_SERVER ['HTTP_HOST']."/api/".$UsersID."/user/login/";
		/* 返回值 */
		$Data = array (
				"status" => 0,
				"msg" => "您还为登陆，请登陆！",
				"url" => $url 
		);
	}else{
		$r = $DB->GetRs("user_favourite","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID." and User_ID=".$_SESSION [$UsersID . 'User_ID']);
		if(!$r){
			$insertInfo = array (
				'Users_ID'=>$UsersID,
				'User_ID' => $_SESSION [$UsersID . 'User_ID'],
				'Biz_ID' => $BizID,
				'CreateTime'=>time()
			);			
			$Result = $DB->Add("user_favourite", $insertInfo );
		}
		$Data = array (
			"status" => 1
		);
	}
	echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );
	exit;
}elseif($action == "get_albums"){
	$condition = "where Users_ID='".$UsersID."' and Biz_ID=".$BizID." order by Category_Index asc,Category_ID asc";
	
	$DB->Get("albums_category","*",$condition);
	$lists = $result = array();
	while($r=$DB->fetch_assoc()){
		$lists[] = $r;
	}
	foreach($lists as $key=>$value){
		$r = $DB->GetRs("albums_photo","count(*) as num","where Category_ID=".$value["Category_ID"]." and Users_ID='".$UsersID."' and Biz_ID=".$BizID);
		$value["photonum"] = $r["num"];
		$value["url"] = '/api/'.$UsersID.'/shop/biz/'.$BizID.'/photos/'.$value["Category_ID"].'/';
		$result[] = $value;
	}
	$Data = array(
		"status"=>count($result)>0 ? 1 : 0,
		"items"=>$result
	);
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}elseif($action=='get_coupon'){
	// if(empty($_SESSION[$UsersID."HTTP_REFERER"])){
		// $HTTP_REFERER="/api/".$UsersID."/shop/member/?love";
	// }else{
		// $HTTP_REFERER="/api/".$UsersID."/shop/union/?love";
	// }
	// header('Location:' . $HTTP_REFERER);
	// if(!isset($_SESSION [$UsersID . 'User_ID'])){
		// $login_url = "/api/".$UsersID."/user/login/";
		// header("Location:".$login_url);
		// exit;
	// }
	$rsCoupon=$DB->GetRs("user_coupon","*","where Users_ID='".$UsersID."' and Coupon_ID=".$_POST['CouponID']);
	$rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION [$UsersID . 'User_ID']);
	if($rsCoupon){
		if($rsCoupon["Coupon_UserLevel"] <= $rsUser["User_Level"]){
			$Data=array(
				'Coupon_ID'=>$rsCoupon['Coupon_ID'],
				'Coupon_UsedTimes'=>$rsCoupon['Coupon_UsedTimes'],
				'Coupon_UseArea'=>$rsCoupon['Coupon_UseArea'],
				'Coupon_UseType'=>$rsCoupon['Coupon_UseType'],
				'Coupon_Condition'=>$rsCoupon['Coupon_Condition'],
				'Coupon_Discount'=>$rsCoupon['Coupon_Discount'],
				'Coupon_Cash'=>$rsCoupon['Coupon_Cash'],
				'Coupon_StartTime'=>$rsCoupon['Coupon_StartTime'],
				'Coupon_EndTime'=>$rsCoupon['Coupon_EndTime'],
				'Record_CreateTime'=>time(),
				'Users_ID'=>$UsersID,
				'Biz_ID'=>$BizID,
				'User_ID'=>$_SESSION [$UsersID . 'User_ID']
			);
			$Flag = $DB->Add('user_coupon_record',$Data);
			if($Flag){
				$Data=array(
					'status'=>1,
					'msg'=>'操作成功！'
				);
			}else{
				$Data=array(
					'status'=>0,
					'msg'=>'网络拥堵，请稍后再试！'
				);
			}
		}else{
			$Data=array(
				'status'=>0,
				'msg'=>'你没有权限领取该优惠券'
			);
		}
	}else{
		$Data=array(
			'status'=>0,
			'msg'=>'请勿非法操作！'
		);
	}
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}
?>