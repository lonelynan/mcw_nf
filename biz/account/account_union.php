<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/region.class.php');
if(empty($_SESSION["BIZ_ID"])){
	header("location:/biz/login.php");
}

$rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);
if($rsBiz["Is_Union"]==0){
	header("location:/biz/account/account.php");
}
$_STATUS = array('<font style="color:#ff6600">正常</font>','<font style="color:blue">禁用</font>');

$rsBiz['Biz_Introduce'] = str_replace('&quot;','"',$rsBiz['Biz_Introduce']);
$rsBiz['Biz_Introduce'] = str_replace("&quot;","'",$rsBiz['Biz_Introduce']);
$rsBiz['Biz_Introduce'] = str_replace('&gt;','>',$rsBiz['Biz_Introduce']);
$rsBiz['Biz_Introduce'] = str_replace('&lt;','<',$rsBiz['Biz_Introduce']);
$category_name = '';
if(empty($rsBiz["Category_ID"])){
echo '升级商家未选择所属分类，点下面的修改资料重新选择提交';exit;
}
$DB->Get("biz_union_category","Category_ParentID,Category_Name","where Category_ParentID=0 and Category_ID in (".trim($rsBiz["Category_ID"],',') . ')');
$items = array();
while($c = $DB->fetch_assoc()){
	$items[] = $c;
}
if($items){
	foreach($items as $item){
		$category_name .= $item["Category_Name"] . ',';
	}
}
$region = new region($DB, $rsBiz["Users_ID"]);
$areaids = $region->get_areaparent($rsBiz["Area_ID"]);
$regionids = $region->get_regionids($rsBiz["Region_ID"]);

$areainfo = "";
$province = $region->get_areainfo("area_id",$areaids["province"]);
if($province){
	$areainfo .= $province["area_name"];
}
if(!empty($areaids["city"])){
	$city = $region->get_areainfo("area_id",$areaids["city"]);
	if($city){
		$areainfo .= ' &gt; '.$city["area_name"];
		if(!empty($areaids["area"])){
			$area = $region->get_areainfo("area_id",$areaids["area"]);
			if($area){
				$areainfo .= ' &gt; '.$area["area_name"];
			}
		}
	}
}

$regioninfo = '';
$s_region = $DB->GetRs("area_region","Region_Name","where Region_ID=".$regionids[0]);
if($s_region){
	$regioninfo = $s_region["Region_Name"];
	if($regionids[1]>0){
		$f_region = $DB->GetRs("area_region","Region_Name","where Region_ID=".$regionids[1]);
		if($f_region){
			$regioninfo = $f_region["Region_Name"].' &gt; '.$regioninfo;
		}
	}
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $ak_baidu;?>"></script>
<script type='text/javascript' src='/static/js/jquery.messager.js'></script>
<script type="text/javascript">
$(document).ready(function() {
	var myAddress='<?php echo $rsBiz["Biz_Address"];?>';
	var destPoint=new BMap.Point(<?php echo $rsBiz["Biz_PrimaryLng"];?>, <?php echo $rsBiz["Biz_PrimaryLat"];?>);
	var map=new BMap.Map('map');
	map.centerAndZoom(new BMap.Point(destPoint.lng, destPoint.lat), 20);
	map.enableScrollWheelZoom();
	map.addControl(new BMap.NavigationControl());
	var marker=new BMap.Marker(destPoint);
	map.addOverlay(marker); 
/*$.get("/biz/active/msg.php",{action:"msg"},function(data){
					var ttf = 0;
					var str = "";
					if(data.status==1){
						var msgg = data.data;
						for(var i=0;i<msgg.length;i++)
						{
							str += "<li><a href='/biz/active/active_add.php?activeid="+msgg[i].id+"'>"+msgg[i].title+"</a></li>";
						}
						ttf = 1;
					}else{
						str = "";
						ttf = 0;
					}
					if(ttf == 1){
						$.messager.lays(320, 200);
						$.messager.show(0, '<div class=\"msg1\"><ul id=\"wewe\">'+str+'</ul></div>');
					}
				 },"JSON");	*/
});
</script>
<style>
.msg1 { padding:10px;}
.msg1 ul {}
.msg1 ul li { line-height:38px; font-size:14px; color:#666; border-bottom:1px #ddd dashed}
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css' />
    <div class="r_nav">
      <ul>
        <li class="cur"><a href="account<?php echo $rsBiz["Is_Union"] ? '_union' : '';?>.php">商家资料</a></li>
        <li><a href="account_edit<?php echo $rsBiz["Is_Union"] ? '_union' : '';?>.php">修改资料</a></li>
		<li><a href="address_edit.php">收货地址</a></li>
        <li><a href="account_password.php">修改密码</a></li>
      </ul>
    </div>
    <div id="orders" class="r_con_wrap">
      <div class="detail_card">
    	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="order_info">
		  <tr>
		  <!--edit in 20160330-->
            <td width="8%" nowrap><img width="80" height="80" src="<?=$rsBiz['Biz_Qrcode']?>?t=<?=time()?>" /></td>
            <td width="92%">我的店铺网址：<a href="http://<?php echo $_SERVER["HTTP_HOST"];?>/api/<?php echo $rsBiz["Users_ID"];?>/shop/biz/<?php echo $_SESSION["BIZ_ID"];?>/" target="_blank">http://<?php echo $_SERVER["HTTP_HOST"];?>/api/<?php echo $rsBiz["Users_ID"];?>/shop/biz/<?php echo $_SESSION["BIZ_ID"];?>/</a></td>
          </tr>
          <tr>
            <td width="8%" nowrap>商家登录账号：</td>
            <td width="92%"><?php echo $rsBiz["Biz_Account"];?></td>
          </tr>
          <tr>
            <td nowrap>商家名称：</td>
            <td><?php echo $rsBiz["Biz_Name"];?></td>
          </tr>
          <tr>
            <td nowrap>所在地区：</td>
            <td><?php echo $areainfo;?></td>
          </tr>
          <tr>
            <td nowrap>所在区域：</td>
            <td><?php echo $regioninfo;?></td>
          </tr>
          <tr>
            <td nowrap>详细地址：</td>
            <td><?php echo $rsBiz["Biz_Address"];?></td>
          </tr>
          <tr>
            <td nowrap>地图定位：</td>
            <td>
             <div id="map" style="height:200px; width:300px;"></div>
            </td>
          </tr>
          <tr>
            <td nowrap>所属行业：</td>
            <td><?php echo $category_name;?></td>
          </tr>
          <tr>
            <td nowrap>商家形象图：</td>
            <td><?php echo $rsBiz["Biz_Logo"] ? '<img src="'.$rsBiz["Biz_Logo"].'" width="150" />' : '';?></td>
          </tr>
          <tr>
            <td nowrap>接受短信手机：</td>
            <td><?php echo $rsBiz["Biz_SmsPhone"];?> <font style="color:red">(用户下单后，系统会自动发送短信到该手机)</font></td>
          </tr>
          <tr>
            <td nowrap>联系人：</td>
            <td><?php echo $rsBiz["Biz_Contact"];?></td>
          </tr>
          <tr>
            <td nowrap>联系电话：</td>
            <td><?php echo $rsBiz["Biz_Phone"];?></td>
          </tr>
          <tr>
            <td nowrap>电子邮箱：</td>
            <td><?php echo $rsBiz["Biz_Email"];?></td>
          </tr>
          <tr>
            <td nowrap>公司主页：</td>
            <td><?php echo $rsBiz["Biz_Homepage"];?></td>
          </tr>
          <tr>
            <td nowrap>商家简介：</td>
            <td><?php echo $rsBiz["Biz_Introduce"];?></td>
          </tr>
          <tr>
            <td nowrap>加入时间：</td>
            <td><?php echo date("Y-m-d H:i:s",$rsBiz["Biz_CreateTime"]);?></td>
          </tr>
          <tr>
            <td nowrap>状态：</td>
            <td><?php echo $_STATUS[$rsBiz["Biz_Status"]];?></td>
          </tr>
            <tr>
                <td nowrap>是否支持到店自提：</td>
                <td><?php echo $rsBiz["Is_BizStores"] == 1 ? '支持' : '不支持';?></td>
            </tr>
        </table>
      </div>
    </div>
  </div>
</div>
</body>
</html>