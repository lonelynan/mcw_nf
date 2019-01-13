<?php
				if (isset($_GET["UsersID"])) {
					$UsersID = $_GET["UsersID"];
				} elseif (isset($_SESSION["Users_ID"])) {
					$UsersID = $_SESSION['Users_ID'];
				} else {
					$UsersID = $Users_ID;
				}
				
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '二维码',
					'Perm_Picture' => '/static/api/distribute/images/ewm1.png',
					'Perm_Url' => '/api/distribute/popularize/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'barcode',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '微信二维码',
					'Perm_Picture' => '/static/api/distribute/images/ewm1.png',
					'Perm_Url' => 'qrcodehb',
					'Perm_Tyle' => 3,
					'Perm_Field' => 'winxbarcode',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '推广二维码',
					'Perm_Picture' => '/static/api/distribute/images/tgm_x.png',
					'Perm_Url' => 'popuqrcodehb',
					'Perm_Tyle' => 3,
					'Perm_Field' => 'popbarcode',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '我的团队',
					'Perm_Picture' => '/static/api/distribute/images/td.png',
					'Perm_Url' => '/api/distribute/team/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'team',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '财务明细',
					'Perm_Picture' => '/static/api/distribute/images/cw_xx.png',
					'Perm_Url' => '/api/distribute/detaillist/self/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'finance',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '爵位晋升',
					'Perm_Picture' => '/static/api/distribute/images/jw.png',
					'Perm_Url' => '/api/distribute/pro_title/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'knighthood',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '爵位明细',
					'Perm_Picture' => '/static/api/distribute/images/cw_xx.png',
					'Perm_Url' => '/api/distribute/detaillist_nobi/self/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'financenobi',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '我的商家',
					'Perm_Picture' => '/static/api/shop/skin/default/images/csr.png',
					'Perm_Url' => '/api//shop/member/setting/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'salesman',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '区域代理',
					'Perm_Picture' => '/static/api/distribute/images/dl.png',
					'Perm_Url' => '/api/distribute/area_proxy/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'area_agency',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '股东分红',
					'Perm_Picture' => '/static/api/distribute/images/fh_x.png',
					'Perm_Url' => '/api/distribute/sha/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'shareholder',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '财富排行榜',
					'Perm_Picture' => '/static/api/distribute/images/cf.png',
					'Perm_Url' => '/api/distribute/income_list/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'wealth',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '领取商品',
					'Perm_Picture' => '/static/api/distribute/images/lip.png',
					'Perm_Url' => '/api/distribute/get_product/1/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'get_product',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '自定义分享',
					'Perm_Picture' => '/static/api/distribute/images/fxz_x.png',
					'Perm_Url' => '/api/distribute/edit_shop/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'custom',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '级别升级',
					'Perm_Picture' => '/static/api/distribute/images/sj_xx.png',
					'Perm_Url' => '/api/distribute/upgrade/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'grade',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '提现',
					'Perm_Picture' => '/static/api/distribute/images/578471497b.png',
					'Perm_Url' => '/api/distribute/withdraw/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'withdraw',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '积分',
					'Perm_Picture' => '/static/api/shop/skin/default/images/jif.png',
					'Perm_Url' => '/api/user/integral/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'integral',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '余额',
					'Perm_Picture' => '/static/api/shop/skin/default/images/yue.png',
					'Perm_Url' => '/api/user/money/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'balance',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '实体店消费单',
					'Perm_Picture' => '/static/api/shop/skin/default/images/yue.png',
					'Perm_Url' => '/api//user/money/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'oofline_orders',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '砍价订单',
					'Perm_Picture' => '/static/api/shop/skin/default/images/kanjia.png',
					'Perm_Url' => '/api//user/kanjia_order/status/2/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'kanjia',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '云购订单',
					'Perm_Picture' => '/static/api/shop/skin/default/images/cloud.png',
					'Perm_Url' => '/api//cloud/member/status/2/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'cloud',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '拼团订单',
					'Perm_Picture' => '/static/api/shop/skin/default/images/pintuan.png',
					'Perm_Url' => '/api//pintuan/orderlist/0/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'pintuan',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '礼品兑换',
					'Perm_Picture' => '/static/api/shop/skin/default/images/lip.png',
					'Perm_Url' => '/api/user/gift/1/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'gift_exchange',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '优惠券',
					'Perm_Picture' => '/static/api/shop/skin/default/images/yhj.png',
					'Perm_Url' => '/api/user/coupon/1/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'coupon',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '地址管理',
					'Perm_Picture' => '/static/api/shop/skin/default/images/dizhi.png',
					'Perm_Url' => '/api/user/my/address/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'address',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '收藏夹',
					'Perm_Picture' => '/static/api/shop/skin/default/images/shouc.png',
					'Perm_Url' => '/api/shop/member/favourite/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'favorite',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '退款/售后',
					'Perm_Picture' => '/static/api/shop/skin/default/images/tuih.png',
					'Perm_Url' => '/api/shop/member/backup/status/5/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'goods_returned',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '设置',
					'Perm_Picture' => '/static/api/shop/skin/default/images/pz.png',
					'Perm_Url' => '/api/shop/member/setting/',
					'Perm_Tyle' => 2,
					'Perm_Field' => 'settings',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '分销此商品',
					'Perm_Picture' => '/static/api/shop/skin/default/images/pz.png',
					'Perm_Url' => '/api/shop/member/setting/',
					'Perm_Tyle' => 4,
					'Perm_Field' => 'detailbone',
				);
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '销量',
					'Perm_Picture' => '/static/api/shop/skin/default/images/pz.png',
					'Perm_Url' => '/api/shop/member/setting/',
					'Perm_Tyle' => 4,
					'Perm_Field' => 'detailbtwo',
				);
				
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '是否要发票',
					'Perm_Picture' => '/static/api/shop/skin/default/images/pz.png',
					'Perm_Url' => '/api/shop/member/setting/',
					'Perm_Tyle' => 5,
					'Perm_Field' => 'fapiao',
				);
				
				$onoff_data[] = array(
					'Users_ID' => $UsersID,
					'Perm_Name' => '推广小助手',
					'Perm_Picture' => '/static/api/distribute/images/gather.png',
					'Perm_Url' => '/distribute/gather_wx/',
					'Perm_Tyle' => 1,
					'Perm_Field' => 'gather',
				);
				
	$perm_configs = array();
	$rss = $DB->Get('permission_config','*','where Users_ID="'.$UsersID.'" AND Is_Delete = 0 AND Perm_Field != \'0\'  order by Perm_On desc');
	while($rss = $DB->fetch_assoc()){
		$perm_configs[] = $rss['Perm_Field'];
	}
	if ($perm_configs) {
		if (count($perm_configs) < 31) {
			foreach ($onoff_data as $key=>$value) {
			if (!in_array($value['Perm_Field'], $perm_configs)) { 
			$DB->Add("permission_config",$value);
				}
		}
		} else {
			if (count($perm_configs) != 31) {
			$DB->Del('permission_config','Users_ID="'.$UsersID.'" AND Is_Delete = 0 AND Perm_Field != \'0\'');
			foreach ($onoff_data as $key=>$value) {
			$DB->Add("permission_config",$value); 
		}
			}
		}
	} else {
		//添加默认开关	
		foreach ($onoff_data as $key=>$value) {
			$DB->Add("permission_config",$value); 
		}
	}
?>