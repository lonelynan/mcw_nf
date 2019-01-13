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



/*若用户没有登陆，跳转到登陆页面*/
if(empty($_SESSION["User_ID"]) || !isset($_SESSION["User_ID"])){
	$_SESSION["HTTP_REFERER"]="/api/weicbd/biz/cart/index.php?UsersID=".$UsersID."&BizID=".$_GET["BizID"];
	header("location:/api/".$UsersID."/user/login/?wxref=mp.weixin.qq.com");
}

	$header_title = "购物车";

	//如果不是，则使用smarty
	//全局变量配置
	$base_url = base_url();
	$dist = dist_url();
	
	$public = $base_url.'static/api/weicbd/biz/'.$rsBiz['Skin_ID'].'/';
	$home_url = $base_url.'api/'.$UsersID.'/weicbd/';
	
	
	//获取购物车中产品列表
	$WeicbdList=json_decode($_SESSION["WeicbdList"],true);
	
	$total = cart_sub($WeicbdList);
	//计算出购物车中产品总价
	
	//网页基本参数
	$smarty->assign('home_url',$home_url);
	$smarty->assign('UsersID',$UsersID);
	$smarty->assign('base_url',$base_url);
	$smarty->assign('dist',$dist);
	$smarty->assign('public',$public);
	$smarty->assign('header_title',$header_title); 
	$smarty->assign('biz_id',$BizID);
	$smarty->assign('cart_list',$WeicbdList);
	$smarty->assign('total',$total);
	
	//渲染页面
	$smarty->display("weicbd/biz/".$rsBiz['Skin_ID']."/cart.html"); 
	
	/**
	 *@desc 计算出购物车中商品总价
	 *@param array $CartList商品列表
	 *@return float $total 商品总价
	 */
	function cart_sub($CartList){
		
		$total = 0;
		
		foreach($CartList as $key=>$product_array){
			foreach($product_array as $k=>$product){
				$total += $product['ProductsPriceX']*$product['Qty'];
			}
		}
		
		return $total;
	}