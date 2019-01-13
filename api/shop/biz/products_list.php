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
		
	/*先设置skinid为1，等到需要扩展时再变成灵活的方法*/
	$skinId = $rsBiz['Skin_ID'];
	
	//获取此商铺下所有产品
	$condition = "where Users_ID='".$UsersID."' and Biz_ID=".$BizID;
	if(!empty($_GET['Category_ID'])){
		$condition .= " and Products_Mulu=".$_GET['Category_ID'];
	}
	$condition .= " order by Products_CreateTime asc";
	
	
	$rsProducts = $DB->getPage("weicbd_products","*",$condition,$pageSize=10);
	
	$rs_list = $DB->toArray($rsProducts);
	
	$product_list = handle_product_list($rs_list);
	
	/*全局变量*/
	$base_url = base_url();
	$public = $base_url.'static/api/weicbd/intro/'.$skinId .'/';
	$home_url = $base_url.'api/'.$UsersID.'/weicbd/';
	$biz_url = $base_url.'api/weicbd/biz/index.php?UsersID='.$UsersID.'&BizID='.$BizID;
	$page_base = str_replace('index','products_list',$biz_url);
	$page_link = $DB->showWechatPage1($page_base.'&page=',true,'return');
	/*smarty模板变量赋值*/
	
	$smarty->assign('base_url',$base_url);
	$smarty->assign('biz_url',$biz_url);
	$smarty->assign('public',$public);
	$smarty->assign('home_url',$home_url);
	$smarty->assign('biz_url',$biz_url);
	$smarty->assign('product_list',$product_list);
	$smarty->assign('product_base_url',str_replace('index','product',$biz_url));
	
	/*本页数据*/
	$smarty->assign('title',$rsBiz['Biz_Name'].'----产品列表');
	$smarty->assign('biz_url',$biz_url);
	$smarty->assign('biz',$rsBiz);
	$smarty->assign('page_link',$page_link);
	
		$contact_url = $base_url.'api/weicbd/biz/contact.php?UsersID='.$UsersID.'&BizID='.$BizID;
	$smarty->assign('contact_url',$contact_url);
	
	/*渲染页面*/
	$smarty->display("weicbd/biz/".$skinId."/products_list.html"); 
?>
