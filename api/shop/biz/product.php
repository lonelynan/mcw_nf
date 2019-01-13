<?php
	require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
	require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
	require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');

	if(isset($_GET["UsersID"])){
		$UsersID=$_GET["UsersID"];
	}else{
		echo '缺少必要的参数';
		exit;
	}

	
	//获取指定店铺BizID
	if(isset($_GET["BizID"])){
		$BizID=$_GET["BizID"];
		$rsBiz=$DB->GetRs("weicbd_biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$BizID.' and Biz_Status=1');
	
		if(!$rsBiz){
			echo '您要访问的商铺不存在！';
			exit;
		}
	}else{
		echo '缺少必要的参数';
		exit;
	}
	
	//获取指定店铺BizID

	if(isset($_GET["ProductsID"])){
		$ProductsID=$_GET["ProductsID"];
		$rsProduct=$DB->GetRs("weicbd_products","*","where Users_ID='".$UsersID."' and Products_ID=".$ProductsID);
	
		if(!$rsProduct){
			echo '您要访问的商品不存在！';
			exit;
		}
	}else{
		echo '缺少必要的参数';
		exit;
	}
		
	/*先设置skinid为1，等到需要扩展时再变成灵活的方法*/
	$skinId = $rsBiz['Skin_ID'];
	
	//获取指定产品信息
	
	
	
	/*全局变量*/
	$base_url = base_url();
	$public = $base_url.'static/api/weicbd/intro/'.$skinId .'/';
	$home_url = $base_url.'api/'.$UsersID.'/weicbd/';
	$biz_url = $base_url.'api/weicbd/biz/index.php?UsersID='.$UsersID.'&BizID='.$BizID;
	$page_base = str_replace('index','products_list',$biz_url);
	$page_link = $DB->showWechatPage1($page_base.'&page=',true,'return');
	/*获取产品缩率图*/

	$json= json_decode($rsProduct['Products_JSON'],TRUE);
	
	$thumb = $json['ImgPath'][0];

	/*smarty模板变量赋值*/
	$smarty->assign('base_url',$base_url);
	$smarty->assign('biz_url',$biz_url);
	$smarty->assign('public',$public);
	$smarty->assign('home_url',$home_url);
	$smarty->assign('biz_url',$biz_url);
	
	/*本页数据*/
	$smarty->assign('title',$rsProduct['Products_Name']);
	$smarty->assign('product',$rsProduct);
	$smarty->assign('biz_url',$biz_url);
	$smarty->assign('biz',$rsBiz);
	$smarty->assign('page_link',$page_link);
	$smarty->assign('thumb',$thumb);
	
	
	
	//$smarty->assign('product_image');
	/*渲染页面*/
	$smarty->display("weicbd/biz/".$skinId."/product.html"); 
?>
