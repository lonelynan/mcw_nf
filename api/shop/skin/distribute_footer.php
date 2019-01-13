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

//获取购物车中的产品数量
function getCartNum () {
    global $DB,$UsersID;
    $car_num = 0;
    if (!empty($_SESSION[$UsersID . 'User_ID'])) {
        $myRedis = new Myredis($DB, 7);
        $redisCart = $myRedis->getCartListValue($UsersID . 'CartList', $_SESSION[$UsersID . 'User_ID']);
        if (!empty($redisCart)) {
            $sessionCart = json_decode($redisCart, true);
            foreach($sessionCart as $key_first => $value_first) {
                foreach($value_first as $key_second => $value_second) {
                    foreach($value_second as $key_third => $value_third) {
                        $car_num += $value_third['Qty'];
                    }
                }
            }
        }
    }
    return $car_num;
}

$car_num = getCartNum();

?>
<style>
.cart{position:relative;}
.cart b{background:red; border-radius: 50%;display: block;height: 15px;position: absolute;left: 14px;top: 5px;width: 15px;font-size:12px;text-align:center;line-height:15px;color:#ffffff;}
.menu_name{height:16px;line-height:8px;text-align:center; display:block; font-size: 12px;}
.car_num{background:red;border-radius: 50%;display: block; height: 15px; position: absolute;right: 14px;top: 5px;width: 15px;font-size: 12px;text-align: center;line-height: 15px;color: #FFF;font-style: normal;}
#login_menu_href{height:auto; line-height:14px;background-size:20px;width: 30px;height: 30px;}
</style>
<link rel="stylesheet" href="/static/tubiao/css/font-awesome.css" type="text/css">
<link rel="stylesheet" href="/static/tubiao/css/font-awesome-ie7.css" type="text/css">
<link href="/static/tubiao/css/bootstrap.css" rel="stylesheet" type="text/css">
<link href="/static/api/css/footer.css" rel="stylesheet" type="text/css">
<script type='text/javascript' src='/static/js/plugin/layer_mobile/layer.js'></script>
<?php require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/substribe.php');?>
<script type="text/javascript">
	$(function() {
		$("#jumpSubscribe").on('click', function () {
			if (<?=strlen($ss['SubscribeQrcode']) > 3 ? 1 : 0?>) {
				layer.open({
					type: 1,
					title: '长按图片识别二维码关注',
					area: '300px',
					shadeClose:true,
					content: '<img src="<?=$ss['SubscribeQrcode']?>" style="witdh:300px;height:300px;">'
				});
			} else if (<?=strlen($ss['SubstribeUrl']) > 3 ? 1 : 0?>) {
				location.href = "<?=$ss['SubstribeUrl']?>";
			}
		});
	})
</script>
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
			array('menu_name' => '首页', 'login_menu_name' => '首页', 'icon' => 'fa  fa-home fa-2x', 'menu_href' => '/api/' . $UsersID . '/shop/union/', 'login_menu_href' => '/api/' . $UsersID . '/shop/union/', 'bind_action_attr' => 0, 'menu_order' => '1'),
			array('menu_name' => '购物', 'login_menu_name' => '购物', 'icon' => 'fa fa-cart-plus fa-2x', 'menu_href' => '/api/' . $UsersID . '/shop/wzw/', 'login_menu_href' => '/api/' . $UsersID . '/shop/wzw/', 'bind_action_attr' => 0, 'menu_order' => '2'),
			array('menu_name' => '开店', 'login_menu_name' => '开店', 'icon' => 'fa fa-shopping-bag fa-6x', 'menu_href' => '/api/' . $UsersID . '/distribute/join/', 'login_menu_href' => '/api/' . $UsersID . '/distribute/', 'bind_action_attr' => 1, 'menu_order' => '3'),
			array('menu_name' => '我的', 'login_menu_name' => '我的', 'icon' => 'fa  fa-user fa-2x', 'menu_href' => '/api/' . $UsersID . '/shop/member/', 'login_menu_href' => '/api/' . $UsersID . '/shop/member/', 'bind_action_attr' => 0, 'menu_order' => '4'),
		)
	);
}else{
	$DefaultMenu = array(
		'menu' => array(
			array('menu_name' => '首页', 'login_menu_name' => '首页', 'icon' => '/static/api/distribute/images/home.png','icon_on' => '/static/api/shop/union/2/home.png','icon_up' => '/static/api/shop/union/2/home1.png', 'menu_href' => '/api/' . $UsersID . '/shop/union/', 'login_menu_href' => '/api/' . $UsersID. '/shop/union/', 'bind_action_attr' => 0, 'menu_order' => '1'),
			array('menu_name' => '购物', 'login_menu_name' => '购物', 'icon' => '/static/api/distribute/images/cart.png', 'icon_on' => '/static/api/shop/union/2/cart.png','icon_up' => '/static/api/shop/union/2/cart1.png','menu_href' => '/api/' . $UsersID . '/shop/wzw/', 'login_menu_href' => '/api/' . $UsersID . '/shop/wzw/', 'bind_action_attr' => 0, 'menu_order' => '2'),
			array('menu_name' => '开店', 'login_menu_name' => '开店', 'icon' => '/static/api/distribute/images/sitemap.png', 'icon_on' => '/static/api/shop/union/2/shop.png','icon_up' => '/static/api/shop/union/2/shop1.png','menu_href' => '/api/' . $UsersID . '/distribute/join/', 'login_menu_href' => '/api/' . $UsersID . '/distribute/', 'bind_action_attr' => 1, 'menu_order' => '3'),
			array('menu_name' => '我的', 'login_menu_name' => '我的', 'icon' => '/static/api/distribute/images/user.png', 'icon_on' => '/static/api/shop/union/2/user.png','icon_up' => '/static/api/shop/union/2/user1.png','menu_href' => '/api/' . $UsersID . '/shop/member/', 'login_menu_href' => '/api/' . $UsersID . '/shop/member/', 'bind_action_attr' => 0, 'menu_order' => '4'),
		)
	);
}
$ShopMenu = empty($rsMenuConfig['ShopMenuJson']) ? $DefaultMenu : json_decode($rsMenuConfig['ShopMenuJson'], TRUE);
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
					<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>" style=" height:auto; line-height:14px; 
						<?php if(in_array($array_end,$url) || $i == 1) {?>
						color:<?php echo !empty($rsMenuConfig['Icon_Color']) ? '#'.$rsMenuConfig['Icon_Color'] : '#ff0000' ; ?>;
						<?php }?>
						">
						<span style="padding: 5px 0px; " class="<?php echo !empty($v['icon']) ? $v['icon'] : ''; ?>"><?php if($v['bind_action_attr'] == 2): ?>
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
					<a href="<?php echo $v['menu_href']; ?>" style=" height:auto; line-height:14px;
						<?php if(in_array($array_end,$url)) {?>
						color:<?php echo !empty($rsMenuConfig['Icon_Color']) ? '#'.$rsMenuConfig['Icon_Color'] : '#ff0000' ; ?>;
						<?php }?> 
						<?php if($url_num <= 3 && $array_end == 'shop') {?>
						color:<?php echo !empty($rsMenuConfig['Icon_Color']) ? '#'.$rsMenuConfig['Icon_Color'] : '#ff0000' ; ?>;
						<?php }?> 
						" >
						<span style="padding: 5px 0px;" class="<?php echo !empty($v['icon']) ? $v['icon'] : ''; ?>"><?php if($v['bind_action_attr'] == 2): ?>
					<b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b>
				<?php endif; ?></span><br>
						<span class="menu_name">
							<?php if(isset($_SESSION[$UsersID.'User_ID'])){
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
			<li style="width: <?php echo 100/count($ShopMenu['menu']); ?>%;">
				<?php if($v['bind_action_attr'] == 1 && $distribute_flag): ?>
					<?php if(in_array($array_end,$url) || $i == 1) {?>
						<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href'] ; ?>" style="background:url(<?php echo !empty($v['icon_up']) ? $v['icon_up'] : '' ; ?>) center center no-repeat; width: 30px;height: 35px; background-size: 25px;"></a>
					<?php }else{?>
						<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>" style="background:url(<?php echo !empty($v['icon_on']) ? $v['icon_on'] : ''; ?>) center center no-repeat;width: 30px;height: 35px; background-size: 25px;"></a>
					<?php }?>
					<span class="menu_name"><?php 
						if(isset($_SESSION[$UsersID.'User_ID'])){
							echo !empty($v['login_menu_name']) ? $v['login_menu_name'] : '';
						}else{
							echo !empty($v['menu_name']) ? $v['menu_name'] : '';
						}?></span>
				<?php else: ?>
					<?php if(in_array($array_end,$url)) {?>
						<?php if(in_array($array_end,$url) || $i == 1) {?>
							<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>" style="background:url(<?php echo isset($v['icon_up']) ? $v['icon_up'] : '' ; ?>) center center no-repeat;width: 30px;height: 35px; background-size: 25px;">					<?php if($v['bind_action_attr'] == 2): ?><b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b><?php endif; ?>	</a>
						<?php }else{  ?>
							<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>" style="background:url(<?php echo isset($v['icon_on']) ? $v['icon_on'] : '' ; ?>) center center no-repeat;width: 30px;height: 35px; background-size: 25px;">					<?php if($v['bind_action_attr'] == 2): ?><b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b><?php endif; ?></a>
						<?php }?>
					<?php }else{  ?>
						<?php if($url_num <= 3 && $i == 1) {?>
							<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>" style="background:url(<?php echo isset($v['icon_up']) ? $v['icon_up'] : '' ; ?>) center center no-repeat;width: 30px;height: 35px; background-size: 25px;">					<?php if($v['bind_action_attr'] == 2): ?><b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b><?php endif; ?></a>
						<?php }else{  ?>
							<a href="<?php echo isset($_SESSION[$UsersID.'User_ID'])? $v['login_menu_href'] : $v['menu_href']; ?>" style="background:url(<?php echo !empty($v['icon_on']) ? $v['icon_on'] : ''; ?>) center center no-repeat;width: 30px;height: 35px; background-size: 25px;">					<?php if($v['bind_action_attr'] == 2): ?><b <?php if(empty($car_num)){?>style="display:none"<?php }else{?> class="car_num" <?php }?>><?php echo $car_num;?></b><?php endif; ?></a>
						<?php }?>
					<?php }?>
					<span  class="menu_name">	
					<?php if(isset($_SESSION[$UsersID.'User_ID'])){
							echo !empty($v['login_menu_name']) ? $v['login_menu_name'] : '';
						}else{
							echo !empty($v['menu_name']) ? $v['menu_name'] : '';
						}
					?>
					</a></span>
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

<?php if(!empty($share_config)){ 
if (!isset($share_config["img_url"])) {
if (isset($share_config["img"])) {
	$share_config["img_url"] = $share_config["img"];
}
}
?>
	<script language="javascript">
		var share_config = {
		   appId:"<?php echo $share_config["appId"];?>",   
		   timestamp:<?php echo $share_config["timestamp"];?>,
		   nonceStr:"<?php echo $share_config["noncestr"];?>",
		   url:"<?php echo $share_config["url"];?>",
		   signature:"<?php echo $share_config["signature"];?>",
		   title:"<?php echo isset($share_config["title"]) ? $share_config['title'] : '';?>",
		   desc:"<?php echo str_replace(array("\r\n", "\r", "\n"), "", isset($share_config["desc"]) ? $share_config["desc"] : '');?>",
		   img_url:"<?php echo isset($share_config["img_url"]) ? $share_config["img_url"] : '';?>",
		   link:"<?php echo isset($share_config["link"]) ? $share_config["link"] : '';?>"
		};
		
		$(document).ready(global_obj.share_init_config);
	</script>
<?php }?>
<div class='conver_favourite' style="display:none;"><img src="/static/api/images/global/share/favourite.png" /></div>
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
var MemberMenu = ['user/message/','user/charge/','user/paymoney/','user/charge_record/','user/money_record/','user/charge_useryielist/','user/zhuanzhang/', 'distribute/edit_shop/', 'distribute/change_bind/', 'user/money/', 'user/integral/', 'user/gift/', 'user/integral_record/',
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