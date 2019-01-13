<?php 
    $request_uri = $_SERVER['REQUEST_URI'];
	$parm_shop = basename(dirname(dirname($request_uri)));
    $first_parm = basename(dirname($request_uri));
    $end_parm = basename($request_uri);
    $isDefault = 'index';
    if( $end_parm=='pintuan') { //首页
        $isDefault = 'index';
    }else if (in_array($first_parm, [ 'seach','sousuo' ])) {
        $isDefault = 'search';
    }else if (in_array($first_parm, [ 'user' ]) && $parm_shop =='biz' ) {
        $isDefault = 'search';
    }else{
		if($parm_shop =='biz'){
			$isDefault = 'search';
		}else{
			$isDefault = 'user';
		} 
    }
?>
<link href="/static/api/pintuan/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<div class="kb"></div>
<div class="clear"></div>
<div class="cotrs">
	<a href="/api/<?=$UsersID ?>/shop/union/"	<?=$isDefault == 'union' ? 'class="thisclass"':'' ?>>
		<i class="fa  fa-home" aria-hidden="true"></i><br />首页
	</a>
	<a href="/api/<?=$UsersID ?>/pintuan/"	<?=$isDefault == 'index' ? 'class="thisclass"':'' ?>>
		<i class="fa  fa-gift" aria-hidden="true"></i><br />拼团
	</a>
	<a href="<?php echo "/api/$UsersID/pintuan/seach/0/"; ?>" <?=$isDefault == 'search' ? 'class="thisclass"':'' ?>>
		<i class="fa  fa-search" aria-hidden="true"></i><br />搜索
	</a>
	<a	href="<?php echo "/api/$UsersID/pintuan/user/"; ?>" <?=$isDefault == 'user' ? 'class="thisclass"':'' ?>>
		<i class="fa  fa-user" aria-hidden="true"></i><br />我的
	</a>
</div>