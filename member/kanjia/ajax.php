<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/General_Tree.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');

//指定模版存放目录
$smarty->left_delimiter = "{{";
$smarty->right_delimiter = "}}";
$UsersID = $_SESSION['Users_ID'];

$template_dir = $_SERVER["DOCUMENT_ROOT"].'/member/kanjia/lbi/';
$smarty->template_dir = $template_dir;

$action = $_POST['action'];

//获得产品option list 列表
if($action == 'get_product'){   
   $cate_id = $_POST['cate_id'];
   $keyword = $_POST['keyword'];   
   $condition = "where Users_ID='".$UsersID."' and Products_SoldOut=0 and Products_Status=1";
   if(!empty($cate_id)){
	$rsCategory=$DB->GetRs("shop_category","*","where Users_ID='".$UsersID."' and Category_ID=".$cate_id);
	if($rsCategory["Category_ParentID"] > 0){
		$rsPCategory=$DB->GetRs("shop_category","*","where Users_ID='".$UsersID."' and Category_ID=".$rsCategory["Category_ParentID"]);
		$condition .= " and Products_Category = ".$cate_id;
	}else{
		$DB->Get("shop_category","*","where Users_ID='".$UsersID."' and Category_ParentID=".$cate_id);
		$categoryidlist = '';
		while($r = $DB->fetch_assoc()){
		$categoryidlist .= $r["Category_ID"].',';
	}
		$categoryidlist = substr($categoryidlist,0,strlen($categoryidlist)-1);
		$condition .= " and Products_Category in (".$categoryidlist.")";
	}
	}   
   if(strlen($keyword)>0){
   		$condition .= " and Products_Name like '%".$_POST["keyword"]."%'";	
   }   
   $rsProducts = $DB->Get("shop_products",'Products_ID,Products_Name,Products_PriceX',$condition);
   
   $product_list = $DB->toArray($rsProducts);
   
   $smarty->assign('list',$product_list);
   
   $option_list=  $smarty->fetch('option_list.html');
   
   echo $option_list;
 
	
}