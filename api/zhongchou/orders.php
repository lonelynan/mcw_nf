<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

$_SESSION[$UsersID."HTTP_REFERER"]="/api/zhongchou/orders.php?UsersID=".$_GET["UsersID"];
$rsConfig = $DB->GetRs("zhongchou_config","*","where usersid='".$UsersID."'");
if(!$rsConfig){
	echo '未开通微众筹';
	exit;
}

$rsUsers = $DB->GetRs("users","*","where Users_ID='".$UsersID."'");
$rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."' and PaymentWxpayEnabled=1");
$is_login = 1;
require_once(CMS_ROOT.'/include/library/wechatuser.php');

$lists = array();

$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
$area_array = json_decode($area_json,TRUE);
$province_list = $area_array[0];

$res = $DB->get("user_order","*","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"]." and Order_Type like '%zhongchou_%' and Order_Status=2 order by Order_ID desc");
while($rsOrder = $DB->fetch_assoc($res)){

    $Province = '';
    if(!empty($rsOrder['Address_Province'])){
        $Province = isset($province_list[$rsOrder['Address_Province']]).' '?$province_list[$rsOrder['Address_Province']]:'';
    }
    $City = '';
    if(!empty($rsOrder['Address_City'])){
        $City = isset($area_array['0,'.$rsOrder['Address_Province']][$rsOrder['Address_City']])?$area_array['0,'.$rsOrder['Address_Province']][$rsOrder['Address_City']].' ':'';
    }

    $Area = '';
    if(!empty($rsOrder['Address_Area'])){
        $Area = isset($area_array['0,'.$rsOrder['Address_Province'].','.$rsOrder['Address_City']][$rsOrder['Address_Area']])?$area_array['0,'.$rsOrder['Address_Province'].','.$rsOrder['Address_City']][$rsOrder['Address_Area']]:' ';
    }
    $rsOrder['Address_Province'] = $Province;
    $rsOrder['Address_City'] = $City;
    $rsOrder['Address_Area'] = $Area;

	  $lists[] = $rsOrder;
}

require_once('skin/orders.php');
?>