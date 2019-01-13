<?php
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if(isset($_GET["action"]))
{
	if($_GET["action"]=="logout")
	{
		session_unset();
		header("location:/member/login.php");
	}
}

$rsUsers=$DB->GetRs("users","*","where Users_ID='".$_SESSION["Users_ID"]."'");
$rsConfig = $DB->GetRs("shop_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");

$RIGHT = json_decode($rsUsers["Users_Right"],true);

$rsUsers = $DB->GetRs("users","*","where Users_ID='".$_SESSION["Users_ID"]."'");
$Users_Right = json_decode($rsUsers['Users_Right'],true);
foreach($rmenu as $key=>$value){
	if (array_key_exists($key,$Users_Right)){
	foreach($value as $k=>$v){
		$Users_Right[$key][] = $k;
	}
	}
}

$myrmenu = array();
foreach($sysrmenu as $key=>$value){
	if (array_key_exists($key,$Users_Right)){
		foreach($value as $k=>$v){
			if($key == 'weicuxiao'){
				$cux = 0;
				$cux = $k == 'weicuxiao'?1:0;
				if($k != 'weicuxiao' && in_array($k,$Users_Right[$key])){			
				$cux = 1;
				}
			}else{
				$cux = 1;
			}
		if($cux == 1){
					$myrmenu[$key][$k] = $v;					
				}				
			}
		}
	}
	foreach($myrmenu as $key=>$value){	
	if($key != 'web'){
	$rmenu['marketing'][$key] = $value[$key];
	}
	}
$newmyrmenu = array();
foreach($myrmenu as $key=>$value){
	if($key == 'web'){
	$newmyrmenu[$key] = $value;
	break;
	}		
	}	
$my_right = $rmenu;
$newone = array();
$newthree = array();
foreach($my_right as $key=>$value){	
	$newone[$key] = $value;
	if($key == 'weixin'){
	break;
	}		
	}
foreach($my_right as $key=>$value){
	if($key != 'jiebase' && $key != 'weixin'){
	$newthree[$key] = $value;	
	}		
	}
$my_right = array_merge($newone,$newmyrmenu,$newthree);
if(isset($_SESSION['user_type'])){
	$my_rightcopy = array();
	$my_rightcopy = $my_right;
	unset($my_right);
	$my_right = array();
	$role = $DB->GetRs("users_roles","*","where id='".$_SESSION["role_id"]."'");
	$employee_right = json_decode($role['role_right'],true)?json_decode($role['role_right'],true):array();
	$newemparray = array();
	foreach($employee_right as $key=>$val){
		foreach($val as $k=>$v){
		if($val[0] == $key){
			$newemparray[$key][$k] = $v;
		}else{
			$newemparray[$key][0] = $key;
			$newemparray[$key][] = $v;
		}
		}
	}
	foreach ($my_rightcopy as $key=>$val){
		if (array_key_exists($key,$newemparray)){
			foreach($val as $k=>$v){
				if(in_array($k,$newemparray[$key])){
					$my_right[$key][$k] = $v;
				}			
			}
		}
	}	
}
$json_right = json_encode($my_right,JSON_UNESCAPED_UNICODE);
$myArray = Array("basic","product","article","order","backup");
$pcArrayone = Array('index_block','index_focus');
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $SiteName;?></title>
<link href="/static/css/font-awesome.css" rel="stylesheet" type="text/css" />
<link href="/static/style.css?t=<?php echo time(); ?>" rel="stylesheet" type="text/css" />

<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>
<body>
<script type='text/javascript' src='/static/js/plugin/jscrollpane/jquery.mousewheel.js'></script> 
<script type='text/javascript' src='/static/js/plugin/jscrollpane/jquery.jscrollpane.js'></script>
<link href='/static/js/plugin/jscrollpane/jquery.jscrollpane.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/member/js/main.js?t=789'></script> 
<script language="javascript">$(document).ready(main_obj.page_init); window.onresize=main_obj.page_init;</script>
<div id="header">
  <div class="logo"><img src="<?php echo $SiteLogo ? $SiteLogo : '/static/member/images/main/header_logo.png';?>" /></div>
  <dl style="float:right;">
	<dd><a id='logout' href="?action=logout"><img src="/static/member/images/main/zx_x.png" width="17" height="17"> 退出登录</a></dd>
  </dl>
  <ul style="float:left;">
    <?php foreach($topmeu as $key=>$value){?>	
	<?php foreach($value as $k=>$v){
		switch($v){
			case '我的帐号':
			$n = 0;
			break;
			case '修改密码':
			$n = 6;
			break;
			case '操作指南':
			$n = 1;
			break;
			case '素材库':
			$n = 2;
			break;
			case '推广技巧':
			$n = 4;
			break;
			case '员工管理':
			$n = 3;
			break;
		}
		?>
    <li><a href="/member/<?=$key?>/<?=$k?>.php" target="iframe" class="ico-<?=$n?>" ><?=$v?></a></li>
	<?php }}?>        
	<?php if(isset($_SESSION['user_type'])){?>
    <li><b style='color:#FFF;'  class="">员工在线:<?php echo $_SESSION['employee_name'];?></b></li>
	<?php }?>
  </ul>
  
</div>

<script type="text/javascript">
$(document).ready(function(e) {
    $("#header li").click(function(e) {
        $(this).addClass("bg").siblings().removeClass("bg");
        $(".mod-menu .menu-item li").removeClass('cur');
    });
});
</script>
<style>li.cur{background-color: #fff}</style>
<script type="text/javascript">
$(document).ready(function(){
	var mod_menu=$(".mod-menu");//导航模块区
	var menu=function(){
		var menuItem=$(".menu-item li");//选择导航列表
		menuItem.each(function(){
			var _index=$(this).index();//获取当前选择菜单列表的索引
			$(this).click(function(){
				$("#header li").removeClass('bg');	//顶部菜单取消选中样式
				menuItem.removeClass('cur');
				$(this).addClass('cur');
				var y = $(this).position().top+1;//获取当前鼠标滑过的列表的顶部坐标
				$(".menu-cont").show();
				$(".menu-cont").css("top",y);//需要显示的对应索引内容
				$(this).addClass("mouse-bg").siblings().removeClass("mouse-bg");
				$(".menu-cont>div").eq(_index).show().siblings().hide();
			});
		});/*导航菜单菜单*/
		$(".mod-menu .menu-cont-list").mouseleave(function(){
			$(".menu-cont").hide();
			menuItem.removeClass("mouse-bg");
		})
	}//展开二级菜单	
	menu();//执行展开二级菜单函
});
</script>

<div id="main">
  <div class="mod-menu">
  <!--edit in 20160326-->    
	
    <ul class="menu-item">
	<?php $i = 0; ?>
	<?php foreach($my_right as $key=>$value){
		$i++;
		?>
	<?php if(!array_key_exists($key,$topmeurkey)){ ?>	
	<?php foreach($value as $k=>$v){?>	
			<?php if($k == $key){ ?>
            <li group="<?=$key?>" class="" style="cursor:pointer"><?=$v?></li>
            <?php if(count($my_right) == $i){ ?>
            </ul>
            <?php }
            
            }
	}
	}
	} ?>
	</ul>
<!--//end mod-menu --> 
    
	<div class="menu-cont" style="display:none;top:0px;">
	<?php foreach($my_right as $key=>$value){ ?>
	<?php if(!array_key_exists($key,$topmeurkey)){ ?>	
	<?php foreach($value as $k=>$v){ ?>	
	<?php if($k == $key){ ?>	
	<div  class="menu-cont-list" style="display:none; height?:100%;">
	<ul>	   
	<?php }else{ ?>
	<?php if($key == 'weixin'){?>
	<?php if(!array_key_exists(substr($key,0,3).'_'.$k,$topmeurv)){
	switch ($k)
	{
	case 'index':
	$key = 'material';
	break;
	case 'articles':
	$key = 'shop';
	break;	
	default:
	$key = 'wechat';
	}
	?>    
	<li><a href="/member/<?=$key?>/<?=$k?>.php" target="iframe"><?=$v?></a><div class="menu-list-link"></div></li>
	<?php $key = 'weixin'; }}else if(in_array($key,$myArray)){?>
		       <li><a href="/member/shop/<?=$k?>.php" target="iframe"><?=$v?></a><div class="menu-list-link"></div></li>
	<?php }else if($key == 'weicuxiao'){?>
      	<?php if($k == 'sctrach'){?>
		       <li><a href="/member/scratch/config.php" target="iframe"><?=$v?></a><div class="menu-list-link"></div></li>
		<?php }else{?>
		       <li><a href="/member/<?=$k?>/config.php" target="iframe"><?=$v?></a><div class="menu-list-link"></div></li>
		<?php }?>
	<?php }elseif($key=='pcsite'){						
			if(in_array($k,$pcArrayone)){
				$pcpath = 'pc_diy';
			}else{
				$pcpath = 'pc_setting';
			}
	?>		
		<li><a href="/pc.php?m=member&c=<?=$pcpath?>&a=<?=$k?>&UsersID=<?=$_SESSION['Users_ID']?>" target="iframe"><?=$v?></a><li>		
	<?php }elseif($key == 'jiebase'){	
	switch ($k)
	{
	case 'config':
	$key = 'kf';
	break;
	case 'index':
	$key = 'update';
	break;
	case 'bmconfig':
	$key = 'bianmin';
	break;
	default:
	$key = 'wechat';
	}
	if($k == 'bmconfig'){
		$k = 'config';
	}
	?>	
		<li><a href="/member/<?=$key?>/<?=$k?>.php" target="iframe"><?=$v?></a><div class="menu-list-link"></div></li>		
	<?php
	$key = 'jiebase'; }elseif($key == 'marketing'){	
	switch ($k)
	{
	case 'pintuan':
	$key = 'pintuan';
	break;
	case 'cloud':
	$key = 'cloud';
	break;
	case 'kanjia':
	$key = 'kanjia';
	break;
	case 'zhuli':
	$key = 'zhuli';
	break;
	case 'votes':
	$key = 'votes';
	break;
	case 'weicuxiao':
	$key = 'scratch';
	break;
	case 'zhongchou':
	$key = 'zhongchou';
	break;
	case 'active':
	    $key = 'active';
	break;	
	default:
	$key = 'games';
	}	
	$k = $key == 'pintuan' ? 'orders' : 'config';
	?>	
		<li><a href="/member/<?=$key?>/<?=$k?>.php" target="iframe"><?=$v?></a><div class="menu-list-link"></div></li>		
	<?php
	$key = 'marketing'; }else{ ?>
		       <li><a href="/member/<?=$key?>/<?=$k?>.php" target="iframe"><?=$v?></a><div class="menu-list-link"></div></li>	
	<?php }
	} ?>
	
    <?php }?>
    </ul>
    </div>
        	<?php }}?>
    

 </div>
      
</div>  
 
  <div class="iframe">
    <iframe src="wechat/account.php" name="iframe" frameborder="0" scrolling="auto"></iframe>
  </div>
  
  <div class="clear"></div>
</div>

<div id="footer">
  <div class="oem"><?php echo $Copyright;?></div>
</div>
<script type="text/javascript">
	var right = <?php echo $json_right;?>;
	var myArray = new Array("basic","product","article","order","backup","financial"); 
	var pcArrayone = new Array("index_block","index_focus");	
	var pcpath = '';
	if(right == ''){		
		$("a").hide();
		$("a").parent("dt").hide();
		$("a").parent("div").hide();
		$("a").parent("div").parent("dd").hide();
		$("a").parent("div").parent("dd").prev("dt").hide();
		$("#logout").show();
	}else{		
		$("a").hide();
		$("a").parent("li").hide();
		$("a").parent("dt").hide();
		$("a").parent("div").hide();
		$("a").parent("div").parent("dd").hide();
		$("a").parent("div").parent("dd").prev("dt").hide();
		$("#logout").show();
		$.each(right,function(key,val){
			$.each(val,function(k,v){
				if(key == 'weixin'){
				switch(k){	       
				case 'index':key = 'material';break;
				case 'articles':key = 'shop';break;				
				default:key = 'wechat';break;
				}
					str = "'\/member\/"+key+"\/"+k+".php'";
					key = 'weixin';
				}else if($.inArray(key,myArray) != -1){
					str = "'\/member\/shop\/"+k+".php'";
				}else if(key == 'weicuxiao'){
					(k == 'sctrach') ? str = "'\/member\/scratch\/config.php'" : str = "'\/member\/"+k+"\/config.php'"; 					
				}else if(key == 'pcsite'){
					if($.inArray(k,pcArrayone) != -1){
						pcpath = 'pc_diy';
					}else{
						pcpath = 'pc_setting';
					}
					str = "'/pc.php?m=member&c="+pcpath+"&a="+k+"&UsersID=<?=$_SESSION['Users_ID']?>'";
				}else if(key == 'jiebase'){
    				switch(k){	       
        				case 'config':key = 'kf';break;
        				case 'index':key = 'update';break;
        				case 'bmconfig':key = 'bianmin';break;
        				default:key = 'wechat';break;
    				}
    				if(k == 'bmconfig'){
    					k = 'config';
    				}
					str = "'\/member\/"+key+"\/"+k+".php'";
					key = 'jiebase';
				}else if(key == 'marketing'){
    				switch(k){	       
        				case 'pintuan':key = 'pintuan';break;
        				case 'cloud':key = 'cloud';break;
        				case 'active':key = 'active';break;
        				case 'kanjia':key = 'kanjia';break;
        				case 'zhuli':key = 'zhuli';break;
        				case 'votes':key = 'votes';break;
        				case 'weicuxiao':key = 'scratch';break;
        				case 'zhongchou':key = 'zhongchou';break;				
        				default:key = 'games';break;
    				}
					k = key == 'pintuan' ? 'orders' : 'config';
					str = "'\/member\/"+key+"\/"+k+".php'";
					key = 'marketing';
				}else{
					str = "'\/member\/"+key+"\/"+k+".php'";
				}				
				$("a[href="+str+"]").show();
				$("a[href="+str+"]").parent("li").show();
				$("a[href="+str+"]").parent("dt").show();
				$("a[href="+str+"]").parent("div").show();
				$("a[href="+str+"]").parent("div").parent("dd").show();
				$("a[href="+str+"]").parent("div").parent("dd").prev("dt").show();
			});
		});
		$("#logout").parent('li').show();
	}
	$('dt').next('dd').hide();	
</script>
</body>
</html>