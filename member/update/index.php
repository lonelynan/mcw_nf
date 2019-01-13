<?php
require($_SERVER['DOCUMENT_ROOT'] . '/version.php');
defined('IN_UPDATE') or exit('No Access');

require($_SERVER['DOCUMENT_ROOT'] . '/include/update/file.func.php');
$bad = is_badguy($version);
if ($bad) {
	msg('非法操作');
}

$url = 'http://down.haofenxiao.net/version_list2.php?product='.$version['product'].'&type='.$version['type'].'&isopen='.$version['isopen'];
$code = @file_get_contents($url);
if($code){
	if(substr($code, 0, 8) == 'StatusOK') {
		$code = substr($code, 8);
	} else {
		msg($code);
	}
}

//获取版本列表
$version_lists = json_decode($code,true);

//获取下一版本
$release = $version['release'];
$dates = array_keys($version_lists);

if($release==$dates[0]){
	$release = 0;
}elseif($release<$dates[count($dates)-1]){
	$release = $dates[count($dates)-1];
}else{
	for($i=0;$i<count($dates);$i++){
		if($release<$dates[$i] && $release>=$dates[$i+1]){
			$release = $dates[$i];
		}
	}
}
$jcurid = 6;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/admin/css/main.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/admin/js/global.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
   <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/jiebase_menubar.php');?>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
    <div style="margin-left:10px;margin-right:10px;background-color:#efefef; font-weight:bold; color:red; padding:10px; line-height:25px; font-size:16px; border:1px #ccc solid; margin-top:10px; clear:both;margin-bottom:10px; ">升级提示：凡自行修改源代码或对程序有特殊要求，对程序文字、功能有过改动的客户，请慎重升级。凡程序改动，升级出现错误的，后果自行负责。建议升级前备份程序。</div>  
    <div class="r_con_wrap">
      <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="20%" nowrap="nowrap">版本号</td>
            <td width="16%" nowrap="nowrap">更新时间</td>
            <td width="16%" nowrap="nowrap">文件大小</td>
            <td width="16%" nowrap="nowrap">在线更新</td>  
            <td width="16%" nowrap="nowrap">更新说明</td>
          </tr>
        </thead>
        <tbody>
        <?php
			foreach($version_lists as $key=>$value){
		?>
          <tr>
            <td nowrap="nowrap">V<?php echo $value["version"];?>R<?php echo $key;?></td>
            <td nowrap="nowrap"><?php echo date('Y-m-d',strtotime($value['datetime']));?></td>
            <td nowrap="nowrap"><?php echo $value["filesize"];?></td>
            <td nowrap="nowrap"><?php if($release == $value['datetime']){?><a class="up" href="update.php?release=<?php echo $value['datetime']?>">[一键升级]</a><?php } elseif (!empty($release)) { echo $release < $value['datetime'] ? '<span class="red">未升级</span>' : '已升级'; } else { echo '已升级'; }?></td>
            <td style="text-align:left" nowrap="nowrap"><?php echo empty($value['exp']) ? '' : str_replace('#','',$value['exp']);?></td>
          </tr>
          <?php }?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script type="text/javascript">
$(function(){
  $(".up").click(function(){
    if (confirm("您确认要升级吗？")) {
      return true;
    } else {
      return false;
    }
  })

})  

</script>
</body>
</html>