<?php
require_once('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
$rsatttype = $DB->Get("shop_product_type","Type_ID,Users_ID,Biz_ID","where Status=1");
	$rsatttypearr = $DB->toArray($rsatttype);
	foreach ( $rsatttypearr as $key => $value ) {			
			$rsatt = $DB->Get("shop_attribute","Attr_Name","where Users_ID='".$value["Users_ID"]."' and Biz_ID=".$value["Biz_ID"]." and Type_ID=".$value['Type_ID']);
			$rsattarr = $DB->toArray($rsatt);			
			$Data = array(
	 	'attrnum'=> count($rsattarr)
    );
			$Flagattr=$DB->Set("shop_product_type",$Data,"where Users_ID='".$value["Users_ID"]."' and Biz_ID=".$value["Biz_ID"]." and Type_ID=".$value['Type_ID']);
			if(!$Flagattr){
	echo '<script language="javascript">alert("增加失败！");history.back();</script>';exit;
	}
			
			}
echo 'jieok';

?>