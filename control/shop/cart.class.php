<?php
	/*
	*	购物车使用类
	*/
class cart{
	
	private $DB;
	private $UsersID;
	
	function __construct($DB,$UsersID){
		$this->DB = $DB;//数据库连接资源
		$this->UsersID = $UsersID;//数据库连接资源
		
	}
	
	/*
	*	添加购物车
	*	@param int $UsersID 商家id
	*	@param array $Post_data 购物车数据
	*	@echo jeson 处理结果
	*/
	function cart_add($Post_data){
		
		/* 获取此产品所选属性id */
		$rsProducts = $this->DB->GetRs ( "shop_products", "*", "where Users_ID='" . $this->UsersID . "' and Products_ID=" . $Post_data ["ProductsID"] );
		$rsBiz = $this->DB->GetRs ( "biz", "Biz_ID", "where Biz_ID=" . $rsProducts ["Biz_ID"] );
		$BizID = empty ( $rsBiz ["Biz_ID"] ) ? 0 : intval ( $rsBiz ["Biz_ID"] );
		$cur_price = $rsProducts ["Products_PriceX"];
		
		/* 如果此产品包含属性 */
		$Property = array ();
		if (strlen ( $Post_data ['spec_list'] ) > 0) {
			$attr_id = explode ( ',', $Post_data ['spec_list'] );
			$cur_price = get_final_price ( $cur_price, $attr_id, $Post_data ["ProductsID"], $this->UsersID );
			$Property = get_posterty_desc ($this->DB, $Post_data ['spec_list'], $this->UsersID, $Post_data ["ProductsID"] );
		}
		
		// 获取登录用户的用户级别及其是否对应优惠价
		
		/* if(!empty($_SESSION[$UsersID.'User_ID'])){
			$rsUser = $this->DB->GetRs("user","User_Level","where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID.'User_ID']);
			if($rsUser['User_Level'] >0 ){
				$rsUserConfig = $this->DB->GetRs("User_Config","UserLevel","where Users_ID='".$UsersID."'");
				$discount_list = json_decode($rsUserConfig["UserLevel"],TRUE);
				$discount = empty($discount_list[$rsUser['User_Level']]['Discount']) ? 0 : $discount_list[$rsUser['User_Level']]['Discount'];
				$discount_price = $rsProducts['Products_PriceX']*(1-$discount/100);
				$discount_price = round($discount_price,2);
				$cur_price = $discount_price;
			}
		
		} */
		
		$needcart = isset($Post_data["needcart"]) ? $Post_data["needcart"] : 1;
		$JSON = json_decode ( $rsProducts ['Products_JSON'], true );
		$OwnerID = ! empty ( $Post_data ['OwnerID'] ) ? $Post_data ['OwnerID'] : 0;
		
		$success = TRUE; // 产品成功加入购物车标记
						 // 如果购物车为空
		
		if($rsProducts["Products_IsVirtual"]==0){
			if($needcart==0){
				$cart_key = $this->UsersID."BuyList";
				$_SESSION[$cart_key] = '';
			}else{
				$cart_key = $this->UsersID."CartList";
			}
			
		}else{
			$cart_key = $this->UsersID."Virtual";
			$_SESSION[$cart_key] = '';
		}
		$web_total = ($cur_price-$rsProducts["Products_Supplyprice"]);
		$array_temp = array (
				"ProductsName" => $rsProducts ["Products_Name"],
				"ImgPath" => empty ( $JSON ["ImgPath"] ) ? "" : $JSON ["ImgPath"] [0],
				"ProductsPriceX" => $cur_price,
				"Products_Supplyprice" => $rsProducts ["Products_Supplyprice"],
				"ProductsWebTotal" => number_format ( $web_total, 2, ".", "" ),
				"ProductsPriceY" => $rsProducts ["Products_PriceY"],
				"ProductsWeight" => $rsProducts ["Products_Weight"],
				"OwnerID" => $OwnerID,
				"IsShippingFree" => $rsProducts ["Products_IsVirtual"] == 0 ? $rsProducts ["Products_IsShippingFree"] : 1,
				"Qty" => $Post_data ["Qty"],
				"spec_list" => isset ( $Post_data ['spec_list'] ) ? $Post_data ['spec_list'] : '',
				"Property" => $Property 
		);
	
		if (empty ( $_SESSION [$cart_key] )) {
			$CartList [$BizID] [$Post_data ["ProductsID"]] [] = $array_temp;
		} else {
			$CartList = json_decode ( $_SESSION [$cart_key], true );
			if (empty ( $CartList [$BizID] )) {
				$CartList [$BizID] [$Post_data ["ProductsID"]] [] = $array_temp;
			} elseif (empty ( $CartList [$BizID] [$Post_data ["ProductsID"]] )) {
				$CartList [$BizID] [$Post_data ["ProductsID"]] [] = $array_temp;
			} else {
				$flag = true;
				foreach ( $CartList [$BizID] [$Post_data ["ProductsID"]] as $k => $v ) {
					$spec_list = isset ( $Post_data ['spec_list'] ) ? $Post_data ['spec_list'] : '';
					$array = array_diff ( explode ( ',', $spec_list ), explode ( ',', $v ["spec_list"] ) );
					if (empty ( $array )) {
						$CartList [$BizID] [$Post_data ["ProductsID"]] [$k] ["Qty"] += $Post_data ["Qty"];
						$flag = false;
						break;
					}
				}
				if ($flag) {
					$CartList [$BizID] [$Post_data ["ProductsID"]] [] = $array_temp;
				}
			}
		}
		$_SESSION [$cart_key] = json_encode ( $CartList, JSON_UNESCAPED_UNICODE );
		
		$qty = 0;
		$total = 0;
		
		foreach ( $CartList as $key => $value ) {
			foreach ( $value as $kk => $vv ) {
				foreach ( $vv as $k => $v ) {
					$qty += $v ["Qty"];
					$total += $v ["Qty"] * $v ["ProductsPriceX"];
				}
			}
		}
		
		$Data = array (
				"status" => 1,
				"qty" => $qty,
				"total" => $total 
		);
		
		echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );
	}
	//线下付款 弱智的idea
	function offline_cart_add($Post_data, $UserID = ''){
        $UserID = !empty($UserID) ? $UserID : $_SESSION[$this->UsersID."User_ID"];
        $offline_order_info = $this->DB->GetRs ( 'user_order', "*", "where Users_ID = '" . $this->UsersID . "' and Biz_ID=".$Post_data['BizID']." and User_ID=".$UserID." and Order_Type='offline' order by Order_CreateTime desc");
        //模拟产品
		$rsProducts = array(
		    'Products_ID'     => empty($offline_order_info['Order_ID']) ? ($Post_data['BizID']+$UserID) : ($offline_order_info['Order_ID']+1),
		    'Products_PriceX' => $Post_data['Amount'],
		    'Products_PriceY' => !empty($Post_data['Amount_Y']) ? $Post_data['Amount_Y'] : $Post_data['Amount'],
			'Biz_ID'          => $Post_data['BizID'],
			'ImgPath'         => $Post_data['BizLogo'],
		);
        $rsBiz = $this->DB->GetRs ( "biz", "*", "where Biz_ID=" . $rsProducts ["Biz_ID"] );
		$BizID = empty ( $rsBiz ["Biz_ID"] ) ? 0 : intval ( $rsBiz ["Biz_ID"] );
		$cur_price = $rsProducts ["Products_PriceX"];
        $needcart = 0;
		//商城配置信息
		$rsConfig = shop_config($this->UsersID);
        //分销相关设置
		$dis_config = dis_config($this->UsersID);
		//合并参数
		$rsConfig = array_merge($rsConfig,$dis_config);
		
		$Owner = get_owner($rsConfig, $this->UsersID);
        $OwnerID = $Owner['id'];
		
		$success = TRUE; // 产品成功加入购物车标记
						 // 如果购物车为空
        $cart_key = $this->UsersID."Virtual";//虚拟产品对待

        $myRedis = new Myredis($this->DB, 7);
        $myRedis->clearCartListValue($cart_key, $UserID);

      /*  if(!empty($rsBiz['Biz_Profit'])){
			$Data = array (
				"status" => 0,
				"msg" => '商家未设置利润率'
		    );
        }else{*/
            $Shop_oofCommision_Reward_Json = json_decode($rsConfig['Shop_oofCommision_Reward_Json'], true);
			$web_total = $cur_price * ($rsBiz['Biz_Profit']/100);//利润
			$array_temp = array (
					"ProductsName" => $rsBiz['Biz_Name'].'实体店消费',
					"ImgPath" => empty ( $rsProducts ["ImgPath"] ) ? "" : $rsProducts ["ImgPath"],
					"ProductsPriceX" => $cur_price,
					"Products_Supplyprice" => $cur_price*((100-$rsBiz['Biz_Profit'])/100),
					"ProductsWebTotal" => number_format ( $web_total, 2, ".", "" ),
					"ProductsPriceY" => $rsProducts["Products_PriceY"],
					"ProductsWeight" => 0,
					"platForm_Income_Reward" => isset($Shop_oofCommision_Reward_Json['platForm_Income_Reward']) ? $Shop_oofCommision_Reward_Json['platForm_Income_Reward'] : 0,
					"sha_Reward" => isset($Shop_oofCommision_Reward_Json['sha_Reward']) ? $Shop_oofCommision_Reward_Json['sha_Reward'] : 0,
					"OwnerID" => $OwnerID,
					"IsShippingFree" => 1,
					"Qty" => 1,
					"spec_list" => '',
					"Property" => array(),
			);

            $redisCart = $myRedis->getCartListValue($cart_key, $UserID);
            if (empty ( $redisCart )) {
				$CartList [$BizID] [$rsProducts ["Products_ID"]] [] = $array_temp;
			} else {
				$CartList = json_decode ( $redisCart, true );
				if (empty ( $CartList [$BizID] )) {
					$CartList [$BizID] [$rsProducts ["Products_ID"]] [] = $array_temp;
				} elseif (empty ( $CartList [$BizID] [$rsProducts ["Products_ID"]] )) {
					$CartList [$BizID] [$rsProducts ["Products_ID"]] [] = $array_temp;
				} else {
					$flag = true;
					foreach ( $CartList [$BizID] [$rsProducts ["Products_ID"]] as $k => $v ) {
						$spec_list = isset ( $Post_data ['spec_list'] ) ? $Post_data ['spec_list'] : '';
						$array = array_diff ( explode ( ',', $spec_list ), explode ( ',', $v ["spec_list"] ) );
						if (empty ( $array )) {
							$CartList [$BizID] [$rsProducts ["Products_ID"]] [$k] ["Qty"] += $Post_data ["Qty"];
							$flag = false;
							break;
						}
					}
					if ($flag) {
						$CartList [$BizID] [$rsProducts ["Products_ID"]] [] = $array_temp;
					}
				}
			}
            $myRedis->setCartListValue($cart_key, $UserID, json_encode($CartList, JSON_UNESCAPED_UNICODE));
			
			$qty = 0;
			$total = 0;
			
			foreach ( $CartList as $key => $value ) {
				foreach ( $value as $kk => $vv ) {
					foreach ( $vv as $k => $v ) {
						$qty += $v ["Qty"];
						$total += $v ["Qty"] * $v ["ProductsPriceX"];
					}
				}
			}
			
			$Data = array (
					"status" => 1,
					"qty" => $qty,
					"total" => $total 
			);
			
//		}
//		echo json_encode ( $Data, JSON_UNESCAPED_UNICODE ); exit;
	}
	/*
	*	购物车修改
	*	@param int $UsersID 商家id
	*	@param array $Post_data 购物车数据
	*	@echo jeson 处理结果
	*/
	function cart_set($Post_data){
		$cart_key = $this->UsersID . "CartList";
	
		$CartList = json_decode ( $_SESSION [$cart_key], true );
		$k = explode ( "_", $_POST ["_CartID"] );
		$CartList [$k [0]] [$k [1]] [$k [2]] ["Qty"] = $_POST ["_Qty"];
		$price = $CartList [$k [0]] [$k [1]] [$k [2]] ["ProductsPriceX"];
		$_SESSION [$cart_key] = json_encode ( $CartList, JSON_UNESCAPED_UNICODE );
		$total = 0;
		foreach ( $CartList as $key => $value ) {
			foreach ( $value as $kk => $vv ) {
				foreach ( $vv as $k => $v ) {
					$total += $v ["Qty"] * $v ["ProductsPriceX"];
				}
			}
		}
		
		$Data = array (
				"status" => 1,
				"price" => $price,
				"total" => $total 
		);
		echo  json_encode ( $Data, JSON_UNESCAPED_UNICODE );
	}
	
	/*
	*	购物车非空验证
	*	@param int $UsersID 商家id
	*	@param array $Post_data 购物车数据
	*	@param array $type(0取消,1收藏) 收藏状态
	*	@echo  jeson 处理结果
	*/
	function cart_del(){
		$cart_key = $this->UsersID . "CartList";
	
		$CartList = json_decode ( $_SESSION [$cart_key], true );
		$k = explode ( "_", $_POST ["CartID"] );
		unset ( $CartList [$k [0]] [$k [1]] [$k [2]] );
		
		if (count ( $CartList [$k [0]] [$k [1]] == 0 )) {
			unset ( $CartList [$k [0]] [$k [1]] );
		}
		
		if (count ( $CartList [$k [0]] == 0 )) {
			unset ( $CartList [$k [0]] );
		}
		
		$_SESSION [$cart_key] = json_encode ( $CartList, JSON_UNESCAPED_UNICODE );
		$total = 0;
		foreach ( $CartList as $key => $value ) {
			foreach ( $value as $kk => $vv ) {
				foreach ( $vv as $k => $v ) {
					$total += $v ["Qty"] * $v ["ProductsPriceX"];
				}
			}
		}
		
		if (empty ( $total )) {
			$_SESSION [$cart_key] = "";
		}
		
		$Data = array (
				"status" => 1,
				"total" => $total 
		);
		echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );
	}
	
	/*
	*	购物车修改
	*	@param int $UsersID 商家id
	*	@param array $Post_data 购物车数据
	*	@param array $type(0取消,1收藏) 收藏状态
	*	@echo  jeson 处理结果
	*/
	function cart_favourite($Post_data,$type){
		// 检测用户是否登陆
		if (empty ( $_SESSION [$this->UsersID . "User_ID"] )) {
			$_SESSION [$this->UsersID . "HTTP_REFERER"] = 'http://' . $_SERVER ['HTTP_HOST'] . "/api/" . $this->UsersID . "/shop/products/" . $Post_data ['productId'] . '/?wxref=mp.weixin.qq.com';
			$url = 'http://' . $_SERVER ['HTTP_HOST'] . "/api/" . $this->UsersID . "/user/login/";
			/* 返回值 */
			$Data = array (
					"status" => 0,
					"info" => "您还未登陆，请登陆！",
					"url" => $url 
			);
		} else {
			
			$insertInfo = array (
					'User_ID' => $_SESSION [$this->UsersID . 'User_ID'],
					'Products_ID' => $Post_data ['productId'],
					'IS_Attention' => 1 
			);
			if($type){
				$Result = $this->DB->Add ( "user_favourite_products", $insertInfo );
			
				$Data = array (
						"status" => 1,
						"info" => "收藏成功！" 
				);
			}else{
				$this->DB->Del ( "user_favourite_products", "User_ID='" . $_SESSION [$this->UsersID . "User_ID"] . "' and Products_ID=" . $Post_data ['productId'] );
			
				$Data = array (
						"status" => 1,
						"info" => "取消收藏成功！" 
				);
			}
		}
		echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );
	}
	
	/*
	*	购物车非空验证
	*	@param int $UsersID 商家id
	*	@param array $Post_data 购物车数据
	*	@param array $type(0取消,1收藏) 收藏状态
	*	@echo  jeson 处理结果
	*/
	function cart_check(){
		$cart_key = $this->UsersID . "CartList";
	
		$CartList = json_decode ( $_SESSION [$cart_key], true );
		if (count ( $CartList ) > 0) {
			$Data = array (
					"status" => 1 
			);
		} else {
			$Data = array (
					"status" => 0,
					"info" => "购物车空的，赶快去逛逛吧！" 
			);
		}
		echo json_encode ( $Data, JSON_UNESCAPED_UNICODE );
	}
	
	/*
	*	购物车非空验证
	*	@param int $UsersID 商家id
	*	@param array $Post_data 购物车数据
	*	@param array $type(0取消,1收藏) 收藏状态
	*	@echo  jeson 处理结果
	*/
	function cart_checkout(){
		
	}
	
}
?>