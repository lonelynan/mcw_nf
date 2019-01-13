<?php
$rsConfig = shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);	
//合并参数
$rsConfig = array_merge($rsConfig,$dis_config);

if (! isset($distribute_flag)) {
	//分销级别处理文件
	include($_SERVER["DOCUMENT_ROOT"].'/api/distribute/distribute.php');
}

?>
<style>
.cart{position:relative;}
.cart b{background:red; border-radius: 50%;display: block;height: 15px;position: absolute;left: 14px;top: 5px;width: 15px;font-size:12px;text-align:center;line-height:15px;color:#ffffff;}
.menu_name{height:16px;line-height:8px;text-align:center; font-size: 12px;}
.car_num{background:red;border-radius: 50%;display: block; height: 15px; position: absolute;right: 14px;top: 5px;width: 15px;font-size: 12px;text-align: center;line-height: 15px;color: #FFF;font-style: normal;}
#login_menu_href{height:auto; line-height:14px;background-size:20px;width: 30px;height: 30px;}
#footer ul#footer-nav li {padding-bottom: 5px;}
</style>
<link rel="stylesheet" href="/static/tubiao/css/font-awesome.css" type="text/css">
<link rel="stylesheet" href="/static/tubiao/css/font-awesome-ie7.css" type="text/css">
<script type='text/javascript' src='/static/tubiao/js/bootstrap.js'></script>
<script type='text/javascript' src='/static/tubiao/js/npm.js'></script>
<link href="/static/tubiao/css/bootstrap.css" rel="stylesheet" type="text/css">
<link href="/static/api/css/footer.css" rel="stylesheet" type="text/css">
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/substribe.php');?>
<!--/*edit在线客服20160419--start--*/--> 
<?php
$kfConfig=$DB->GetRs("kf_config","*","where Users_ID='".$UsersID."' and KF_IsShop=1");
if($kfConfig){
    if($kfConfig['kftype']==1){
        $qq = $kfConfig["qq"];
        $qq_icon = $kfConfig["qq_icon"];
        $qq_postion = $kfConfig["qq_postion"];
?>        
<a style="position:fixed;cursor:pointer;<?php echo $qq_postion?>:0px;top:50%;z-index: 99999;" target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?= $qq;?>&site=qq&menu=yes"><img border="0" src="/static/kf/<?php echo $qq_icon?>.gif" alt="点击这里给我发消息" title="点击这里给我发消息"/ ></a>
<?php    
    }else{
        echo htmlspecialchars_decode($kfConfig["KF_Code"],ENT_QUOTES);
    }
}
?>
<!--/*edit在线客服20160419--end--*/-->
<div id="footer_points"></div>
<?php
$rsMenuConfig = $DB->GetRs('shop_config', '*', ' WHERE  Users_ID="' .$UsersID. '"');
if($rsMenuConfig['Bottom_Style'] == 0){
	$DefaultMenu = array(
		'menu' => array(
			array('menu_name' => '首页', 'login_menu_name' => '首页', 'icon' => 'fa  fa-home fa-2x', 'menu_href' => '/api/' . $UsersID . '/shop/', 'login_menu_href' => '/api/' . $UsersID . '/shop/', 'bind_action_attr' => 0, 'menu_order' => '1'),
			array('menu_name' => '分类', 'login_menu_name' => '分类', 'icon' => 'fa  fa-th-list fa-2x', 'menu_href' => '/api/' . $UsersID . '/shop/1//allcategory/', 'login_menu_href' => '/api/' . $UsersID . '/shop/1//allcategory/', 'bind_action_attr' => 0, 'menu_order' => '1'),
			array('menu_name' => '我要分销', 'login_menu_name' => '分销中心', 'icon' => 'fa fa-sitemap fa-2x', 'menu_href' => '/api/' . $UsersID . '/distribute/join/', 'login_menu_href' => '/api/' . $UsersID . '/distribute/', 'bind_action_attr' => 1, 'menu_order' => '2'),
			array('menu_name' => '购物车', 'login_menu_name' => '购物车', 'icon' => 'fa fa-cart-plus fa-2x', 'menu_href' => '/api/' . $UsersID . '/shop/cart/', 'login_menu_href' => '/api/' . $UsersID . '/shop/cart/', 'bind_action_attr' => 2, 'menu_order' => '3'),
			array('menu_name' => '个人中心', 'login_menu_name' => '个人中心', 'icon' => 'fa  fa-user fa-2x', 'menu_href' => '/api/' . $UsersID . '/shop/member/', 'login_menu_href' => '/api/' . $UsersID . '/shop/member/', 'bind_action_attr' => 0, 'menu_order' => '4'),
		)
	);
}else{
	$DefaultMenu = array(
		'menu' => array(
			array('menu_name' => '首页', 'login_menu_name' => '首页', 'icon' => '/static/api/distribute/images/home.png','icon_on' => '/static/api/distribute/images/shouye1.png','icon_up' => '/static/api/distribute/images/shouye.png', 'menu_href' => '/api/' . $UsersID . '/shop/', 'login_menu_href' => '/api/' . $UsersID. '/shop/', 'bind_action_attr' => 0, 'menu_order' => '1'),
			array('menu_name' => '分类', 'login_menu_name' => '分类', 'icon' => '/static/api/distribute/images/home.png','icon_on' => '/static/api/distribute/images/fl1.png','icon_up' => '/static/api/distribute/images/fl.png', 'menu_href' => '/api/' . $UsersID . '/shop/1//allcategory/', 'login_menu_href' => '/api/' .$UsersID . '/shop/1//allcategory/', 'bind_action_attr' => 0, 'menu_order' => '1'),
			array('menu_name' => '我要分销', 'login_menu_name' => '分销中心', 'icon' => '/static/api/distribute/images/sitemap.png', 'icon_on' => '/static/api/distribute/images/fxzx2.png','icon_up' => '/static/api/distribute/images/fxzx3.png','menu_href' => '/api/' . $UsersID . '/distribute/join/', 'login_menu_href' => '/api/' . $UsersID . '/distribute/', 'bind_action_attr' => 1, 'menu_order' => '2'),
			array('menu_name' => '购物车', 'login_menu_name' => '购物车', 'icon' => '/static/api/distribute/images/cart.png', 'icon_on' => '/static/api/distribute/images/gwc1.png','icon_up' => '/static/api/distribute/images/gwc.png','menu_href' => '/api/' . $UsersID . '/shop/cart/', 'login_menu_href' => '/api/' . $UsersID . '/shop/cart/', 'bind_action_attr' => 2, 'menu_order' => '3'),
			array('menu_name' => '个人中心', 'login_menu_name' => '个人中心', 'icon' => '/static/api/distribute/images/user.png', 'icon_on' => '/static/api/distribute/images/hyzx1.png','icon_up' => '/static/api/distribute/images/hyzx.png','menu_href' => '/api/' . $UsersID . '/shop/member/', 'login_menu_href' => '/api/' . $UsersID . '/shop/member/', 'bind_action_attr' => 0, 'menu_order' => '4'),
		)
	);
}
$ShopMenu = empty($rsMenuConfig['ShopMenuJson']) ? $DefaultMenu : json_decode($rsMenuConfig['ShopMenuJson'], TRUE);
//print_R($ShopMenu);exit;
//$ShopMenu = empty($rsMenuConfig['ShopMenuJson']) ? $DefaultMenu : $DefaultMenu;
//分销开关关闭时去掉分销中心链接的菜单(去除用户自定义的分销中心菜单) 
if (empty($rsConfig['DisSwitch'])) { 
    foreach ($ShopMenu['menu'] as $k => $v) {
        if (strpos($v['login_menu_href'],'distribute') > -1) {
            unset($ShopMenu['menu'] [$k]); 
        } 
    }  
}
 
/*
foreach($ShopMenu['menu'] as $key=>$value){
	$list[$key] = $value['menu_order'];
}*/

//对菜单进行排序
foreach($ShopMenu['menu'] as $key=>$value){
	$list[$key] = $value['menu_order'];
}
sort($list);
$lists = array_unique($list); 
foreach($lists as $k=>$val){
	foreach($ShopMenu['menu'] as $key=>$value){
		if($val == $value['menu_order']){
			$menu_list[] = $ShopMenu['menu'][$key];
		}	
		
	}
}

$ShopMenu['menu'] = $menu_list;

?>
<?php $i = 1;?>
	<footer id="footer" style="height:auto;">  
	  <ul class="list-group" id="footer-nav" style="height:auto; ">
		<?php foreach ($ShopMenu['menu'] as $k => $v) : ?>
		<?php
		
			$bottom_url = array_filter(explode('/',$v['login_menu_href']));
			$array_end = array_pop($bottom_url);
			$url = array_filter(explode('/',$_SERVER['REQUEST_URI']));
			$bottom_reverse = array_flip($url);
			if(in_array('shop',$url)){
				$bottom_key = $bottom_reverse['shop'];
				unset($url[$bottom_key]);
			}
			$url_num = count($url);	
		?>
		<?php if($rsMenuConfig['Bottom_Style'] == 0) {?>
			<li style="  height:auto; width: <?php echo 100/count($ShopMenu['menu']); ?>%;" >
				<?php if($v['bind_action_attr'] == 1 && $distribute_flag): ?>	
					<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>?love" style=" height:auto; line-height:14px; 
						<?php if(in_array($array_end,$url) || $i == 1) {?>
						color:<?php echo !empty($rsMenuConfig['Icon_Color']) ? '#'.$rsMenuConfig['Icon_Color'] : '#ff0000' ; ?>;
						<?php }?>
						">
						<span style="padding: 5px 0px; " class="<?php echo !empty($v['icon']) ? $v['icon'] : ''; ?>"><?php if($v['bind_action_attr'] == 2): ?>
					<?php 
						$car_num = 0;
						if(!empty($_SESSION[$UsersID.'CartList'])) {					
							$sessionCart = json_decode($_SESSION[$UsersID.'CartList'],true);
							foreach($sessionCart as $key_first => $value_first) {
								foreach($value_first as $key_second => $value_second) {								
									$car_num += $value_second['Qty'];
								}
							}
						}
					?>
					<b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b>
					<?php endif; ?></span><br>
						<span class="menu_name" ><?php  
						if(isset($_SESSION[$UsersID.'User_ID'])){
							echo !empty($v['login_menu_name']) ? $v['login_menu_name'] : '';
						}else{
							echo !empty($v['menu_name']) ? $v['menu_name'] : '';
						}
						?></span>
					</a>
				<?php else: ?>
					<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>?love" style=" height:auto; line-height:14px;
						<?php if(in_array($array_end,$url)) {?>
						color:<?php echo !empty($rsMenuConfig['Icon_Color']) ? '#'.$rsMenuConfig['Icon_Color'] : '#ff0000' ; ?>;
						<?php }?> 
						<?php if($url_num <= 3 && $array_end == 'shop') {?>
							<?php if(!in_array('cart',$url)){  ?>
								color:<?php echo !empty($rsMenuConfig['Icon_Color']) ? '#'.$rsMenuConfig['Icon_Color'] : '#ff0000' ; ?>;
							<?php }?> 
						<?php }?> 
						" >
						<span style="padding: 5px 0px;" class="<?php echo !empty($v['icon']) ? $v['icon'] : ''; ?>"><?php if($v['bind_action_attr'] == 2): ?>
					<?php 
						$car_num = 0;
						if(!empty($_SESSION[$UsersID.'CartList'])) {					
							$sessionCart = json_decode($_SESSION[$UsersID.'CartList'],true);
							foreach($sessionCart as $key_first => $value_first) {
								foreach($value_first as $key_second => $value_second) {								
									$car_num += $value_second['Qty'];
								}
							}
						}
					?>
					<b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b>
				<?php endif; ?></span><br>
						<span class="menu_name">
							<?php 
								if(isset($_SESSION[$UsersID.'User_ID'])){
									echo !empty($v['login_menu_name']) ? $v['login_menu_name'] : '';
								}else{
									echo !empty($v['menu_name']) ? $v['menu_name'] : '';
								}
							?>
						</span>
					</a>
				<?php endif; ?>	 			
			</li>
		<?php }else{?>	

		<!-- begin -->
				<?php if($v['bind_action_attr'] == 2): ?>
					<?php 
						$car_num = 0;
						if(!empty($_SESSION[$UsersID.'CartList'])) {					
							$sessionCart = json_decode($_SESSION[$UsersID.'CartList'],true);
							foreach($sessionCart as $key_first => $value_first) {
								foreach($value_first as $key_second => $value_second) {								
									$car_num += $value_second['Qty'];
								}
							}
						}
					?>

				<?php endif; ?>		
		<!--//end -->	
			<li style="width: <?php echo 100/count($ShopMenu['menu']); ?>%;">
				<?php if($v['bind_action_attr'] == 1 && $distribute_flag): ?>
					<?php if(in_array($array_end,$url) || $i == 1) {?>
						<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href'] ; ?>?love" style="background:url(<?php echo !empty($v['icon_up']) ? $v['icon_up'] : '' ; ?>) center center no-repeat;  background-size: 20px;">
					<?php }else{?>
						<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>?love" style="background:url(<?php echo !empty($v['icon_on']) ? $v['icon_on'] : ''; ?>) center center no-repeat; background-size: 20px;">
					<?php }?>
					<span class="menu_name"><?php 
						if(isset($_SESSION[$UsersID.'User_ID'])){
							echo !empty($v['login_menu_name']) ? $v['login_menu_name'] : '';
						}else{
							echo !empty($v['menu_name']) ? $v['menu_name'] : '';
						}?></span></a>
				<?php else: ?>
					<?php if(in_array($array_end,$url)) {?>
						<?php if(in_array($array_end,$url) || $i == 1) {?>
							<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>?love" style="background:url(<?php echo isset($v['icon_up']) ? $v['icon_up'] : '' ; ?>) center center no-repeat; background-size: 20px;">					<?php if($v['bind_action_attr'] == 2): ?><b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b><?php endif; ?>
						<?php }else{  ?>
							<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>?love" style="background:url(<?php echo isset($v['icon_on']) ? $v['icon_on'] : '' ; ?>) center center no-repeat; background-size: 20px;">					<?php if($v['bind_action_attr'] == 2): ?><b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b><?php endif; ?>
						<?php }?>
					<?php }else{  ?>
						<?php if($url_num <= 3 && $i == 1) { ?>
							<?php if(!in_array('cart',$url)){?>
								<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>?love" style="background:url(<?php echo isset($v['icon_up']) ? $v['icon_up'] : '' ; ?>) center center no-repeat; background-size: 20px;">					<?php if($v['bind_action_attr'] == 2): ?><b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b><?php endif; ?>
							<?php }else{ ?>
								<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>?love" style="background:url(<?php echo isset($v['icon_on']) ? $v['icon_on'] : '' ; ?>) center center no-repeat; background-size: 20px;">					<?php if($v['bind_action_attr'] == 2): ?><b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b><?php endif; ?>
							<?php } ?>
						<?php }else{  ?>
							<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>?love" style="background:url(<?php echo !empty($v['icon_on']) ? $v['icon_on'] : ''; ?>) center center no-repeat; background-size: 20px;">					<?php if($v['bind_action_attr'] == 2): ?><b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b><?php endif; ?>
						<?php }?>
					<?php }?>
					<span  class="menu_name">
						<?php 
							if(isset($_SESSION[$UsersID.'User_ID'])){
								echo !empty($v['login_menu_name']) ? $v['login_menu_name'] : '';
							}else{
								echo !empty($v['menu_name']) ? $v['menu_name'] : '';
							}
						?>
					</span></a>
				<?php endif; ?>

			</li>
		<?php }?>
		<?php $i++; ?>
		<?php endforeach; ?>
	  </ul>
	</footer>
<?php if($rsConfig["CallEnable"] && $rsConfig["CallPhoneNumber"]){?>
<script language='javascript'>var shop_tel='<?php echo $rsConfig["CallPhoneNumber"];?>';</script>
<script type='text/javascript' src='/static/api/shop/js/tel.js?t=<?php echo time();?>'></script>
<?php }?>

<?php if(!empty($share_config)){?>
	<script language="javascript">
		var share_config = {
		   appId:"<?php echo $share_config["appId"];?>",   
		   timestamp:<?php echo $share_config["timestamp"];?>,
		   nonceStr:"<?php echo $share_config["noncestr"];?>",
		   url:"<?php echo $share_config["url"];?>",
		   signature:"<?php echo $share_config["signature"];?>",
		   title:"<?php echo isset($share_config["title"]) ? $share_config['title'] : '';?>",
		   desc:"<?php echo str_replace(array("\r\n", "\r", "\n"), "", isset($share_config["desc"]) ? $share_config["desc"] : '');?>",
		   img_url:"<?php echo isset($share_config["img"]) ? $share_config["img"] : '';?>",
		   link:"<?php echo isset($share_config["link"]) ? $share_config["link"] : '';?>"
		};
		
		$(document).ready(global_obj.share_init_config);
	</script>
<?php }?>
<div class='conver_favourite'><img src="/static/api/images/global/share/favourite.png" /></div>
<?php
$CURSCRIPT = '';
$uriPartArr = array_filter(explode('/', $_SERVER['REQUEST_URI']));

if (isset($uriPartArr[3])) {
	if (isset($uriPartArr[4])) {
		$uri = $uriPartArr[3] . '/' . $uriPartArr[4] . '/';
	} else {
		$uri = '';
	}
	
	$CURSCRIPT = strtolower($uri);
}
?>

<!-- 底部菜单导航 -->
<script type="text/javascript">
var CURSCRIPT = '<?php echo $CURSCRIPT; ?>';
var CURSCRIPTCOLOR = '<?php echo !empty($rsMenuConfig['Icon_Color']) ? '#' . $rsMenuConfig['Icon_Color'] : '#ff0000' ;?>';
var MemberMenu = ['user/message/','user/charge/','user/paymoney/','user/charge_record/','user/money_record/','user/charge_useryielist/','user/zhuanzhang/','user/coupon/', 'distribute/edit_shop/', 'distribute/change_bind/', 'user/money/', 'user/integral/', 'user/gift/', 'user/integral_record/',
	'user/payword/', 'user/my/', 'distribute/withdraw/', 'distribute/bankcards/', 'distribute/edit_headimg/'
];

//会员中心
if (CURSCRIPT != '' && ($.inArray(CURSCRIPT, MemberMenu) != -1) ) {
	var MenuID = '';
	$("footer li a").each(function(i) {
		var href = $(this).attr('href');
		if (href.indexOf('/shop/member/') != -1) {
			MenuID = $(this);
		}
	})

	if (typeof MenuID === 'object') {
		$("footer li a").css('color', '');
		$("footer li span").css('color', '');

		MenuID.css('color', CURSCRIPTCOLOR);
		MenuID.find("span").css('color', CURSCRIPTCOLOR);
	}
	
}
</script>