<?php 
require_once('global.php');
//pintuan
	$rsBizActive= $DB->Get("biz_active","*","where Users_ID='".$UsersID."' and Biz_ID = ".$rsBiz['Biz_ID']);
	$rsBizActive = $DB->toArray($rsBizActive);
	foreach ($rsBizActive as $key=>$value) {
		$rsActive = $DB->GetRs("active","Active_ID,Type_ID,stoptime","where Users_ID='".$UsersID."' and Active_ID=".$value['Active_ID'].' order by addtime desc');
		if($rsActive['stoptime'] > time()){
			if($rsActive['Type_ID'] == 1){
				$rsPintuan = 1;
				$Pintuan_id = $rsActive['Active_ID'];
			} elseif ($rsActive['Type_ID'] == 2){
				$rsCloud = 1;
				$Cloud_id = $rsActive['Active_ID'];
			} elseif ($rsActive['Type_ID'] == 3){
				$rsZhongchou = 1;
				$Zhongchou = $rsActive['Active_ID'];
			}
		}
	}
?>

<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">  
<meta name="app-mobile-web-app-capable" content="yes">
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/skin/default/style.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href="/static/api/biz/css/font-awesome.min.css" type="text/css" rel="stylesheet">
<link href='/static/api/shop/biz/1/style.css?t=<?php echo time() ?>' rel='stylesheet' type='text/css' />
<title>活动中心</title>
</head>

<style>
	*{margin:0px; padding:0px;}
	.clear{clear:both;}
	img{border:none;vertical-align: middle;}
	.left{float:left;}
	.right{float:right;}
	body{background-color:#f8f8f8;-webkit-tap-highlight-color:transparent;font-size:14px; font-family:"微软雅黑"}
	.back_x{ background:#fff; overflow:hidden; line-height:45px; color:#666; text-align:center; padding:0 10px;}
	.w{width:100%; margin:0 auto;}
	.act_img img{ display:block; width:100%; margin-bottom:5px}
</style>
<body>
<div class="w">
	<div class="back_x">
    	<a class="left"><i class="fa  fa-angle-left fa-2x" aria-hidden="true"></i></a>活动中心
    </div>
	<div class="act_img">
		<?php  if(isset($rsPintuan) && $rsPintuan == 1){?>
			<a href="<?php echo $base_url.'api/'.$UsersID.'/pintuan/biz/'.$rsBiz['Biz_ID'].'/act_'.$Pintuan_id.'/';?>"><img src="/static/api/biz/images/hd1.jpg"></a>
		<?php } ?>
    	<?php  if(isset($rsCloud) && $rsCloud == 1){?>
			<a href="<?php echo $base_url.'api/'.$UsersID.'/cloud/biz/'.$rsBiz['Biz_ID'].'/act_'.$Cloud_id.'/';?>"><img src="/static/api/biz/images/hd2.jpg"></a>
		<?php } ?>
		<?php  if(isset($rsZhongchou) && $rsZhongchou == 1){?>
			<a href="<?php echo $base_url.'api/'.$UsersID.'/zhongchou/biz/'.$rsBiz['Biz_ID'].'/act_'.$Zhongchou.'/';?>"><img src="/static/api/biz/images/hd3.jpg"></a>
		<?php } ?>
    </div>
</div>
<?php require_once("footer.php");?>
</body>
</html>
