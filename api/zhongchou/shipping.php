<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

$User_ID = $_SESSION[$UsersID."User_ID"];
//物流运费
$BizID   = $item['Biz_ID'];
$rsbizConfig = $DB->GetRs("biz", "*", "where Users_ID='" . $UsersID . "' AND Biz_ID={$BizID}");
$rsConfig = array_merge($rsConfig, $rsbizConfig);

 
$Address_ID = !empty($_GET['AddressID'])?$_GET['AddressID']:0;
if($Address_ID == 0){
		$condition = "Where Users_ID = '".$UsersID."' And User_ID = ".$User_ID." And Address_Is_Default = 1";
}else{
		$condition = "Where Users_ID = '".$UsersID."' And User_ID = ".$User_ID." And Address_ID =".$Address_ID;
}

$rsAddress = $DB->GetRs('user_address','*',$condition);
$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
$area_array = json_decode($area_json,TRUE);
$province_list = $area_array[0];
$City_Code = 0;
if(!$rsAddress){
		$_SESSION[$UsersID."From_Checkout"] = "/api/zhongchou/check.php?UsersID=".$_GET["UsersID"]."&itemid=".$itemid."&prizeid=".$prizeid;
		header("location:/api/".$UsersID."/user/my/address/edit/");
}else{
		$City_Code = $rsAddress['Address_City'];
}

$shipping_company_dropdown = get_front_shiping_company_dropdown($UsersID,$rsConfig);
$Business = 'express';
$is_shipping = 0;
if(!empty($shipping_company_dropdown)){
    $is_shipping = 1;
}
$Default_Shipping = $rsConfig['Default_Shipping']?$rsConfig['Default_Shipping']:0;
$info_array = array(
		"qty"=>1,
		"weight"=>$prize["weight"],
		"money"=>$prize["money"]
);

?>
