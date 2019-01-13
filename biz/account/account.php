<?php 
require_once('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
$_STATUS = array('<font style="color:#ff6600">正常</font>','<font style="color:blue">禁用</font>');
$rsBiz['Biz_Introduce'] = htmlspecialchars_decode($rsBiz['Biz_Introduce'],ENT_QUOTES);

$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
$area_array = json_decode($area_json,TRUE);
$province_list = $area_array[0];
$Province = '';
if(!empty($rsBiz['Biz_Province'])){
	$Province = $province_list[$rsBiz['Biz_Province']].',';
}
$City = '';
if(!empty($rsBiz['Biz_City'])){
	$City = $area_array['0,'.$rsBiz['Biz_Province']][$rsBiz['Biz_City']].',';
}

$Area = '';
if(!empty($rsBiz['Biz_Area'])){
	$Area = $area_array['0,'.$rsBiz['Biz_Province'].','.$rsBiz['Biz_City']][$rsBiz['Biz_Area']];
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

<script type='text/javascript' src='/static/js/jquery.messager.js'></script>
	<script>
		$(document).ready(function(){
				$.get("/biz/active/msg.php",{action:"msg"},function(data){
					var ttf = 0;
					var str = "";
					if(data.status==1){
						var msgg = data.data;
						for(var i=0;i<msgg.length;i++)
						{
							str += "<li><a href='/biz/active/active_add.php?activeid="+msgg[i].id+"' onclick='return false;'>"+msgg[i].title+"</a></li>";
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
				 },"JSON");
				 		
		});
	</script>
<style>
.msg1 { padding:10px;}
.msg1 ul {}
.msg1 ul li { line-height:38px; font-size:14px; color:#666; border-bottom:1px #ddd dashed}
.detail_card table tr td img {max-width:120px;}
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content" style="padding:0px">
    <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css' />
    <div class="r_nav">
      <ul>
        <li class="cur"><a href="account.php">商家资料</a></li>
        <li><a href="account_edit.php">修改资料</a></li>
		<li><a href="address_edit.php">收货地址</a></li>
                <li><a href="bind_user.php">绑定会员</a></li>

        <li><a href="account_password.php">修改密码</a></li>
        <li><a href="account_payconfig.php">结算配置</a></li>
      </ul>
    </div>
    <div id="orders" class="r_con_wrap" >
      <div class="detail_card">
    	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="order_info">
          <tr>
            <td nowrap>到期时间：</td>
            <td><?php if($rsBiz['is_biz']==1 && !empty($rsBiz["expiredate"])) {
                echo date('Y-m-d',$rsBiz["expiredate"]);
                echo '<a style="text-decoration:none;" href="../authen/charge_man.php"><span style="margin-left:30px;color:red">续费</span></a>';
            } else {
                echo '您还未完成商家入驻';
                echo '<a style="text-decoration:none;" href="../authen/index.php"><span style="margin-left:30px;color:red">立即完成</span></a>';
            }?></td>
          </tr>
           <tr>
            <td nowrap>保证金：</td>
            <td><?php if($rsBiz['is_biz']==1 && !empty($rsBiz["bond_free"])) {echo $rsBiz["bond_free"].'&nbsp;元';
                echo '<a style="text-decoration:none;" href="../authen/back_apply.php"><span style="margin-left:30px;color:red">申请退款</span></a>';
            } else {
                echo '您还未缴纳保证金';
            }?></td>
          </tr>
		  <?php if($IsStore==1){?>
		  <tr>
            <td width="8%" nowrap>我的店铺网址：</td>
            <td width="92%"><a href="http://<?php echo $_SERVER["HTTP_HOST"];?>/api/<?php echo $rsBiz["Users_ID"];?>/biz/<?php echo $_SESSION["BIZ_ID"];?>/" target="_blank">http://<?php echo $_SERVER["HTTP_HOST"];?>/api/<?php echo $rsBiz["Users_ID"];?>/biz/<?php echo $_SESSION["BIZ_ID"];?>/</a></td>
          </tr>
          <?php }?>
          <tr>
            <td width="8%" nowrap>商家登录账号：</td>
            <td width="92%"><?php echo $rsBiz["Biz_Account"];?></td>
          </tr>
          <tr>
            <td nowrap>商家名称：</td>
            <td><?php echo $rsBiz["Biz_Name"];?></td>
          </tr>
          <tr>
            <td nowrap>商家Logo：</td>
            <td><?php echo $rsBiz["Biz_Logo"] ? '<img src="'.$rsBiz["Biz_Logo"].'" />' : '';?></td>
          </tr>
          <tr>
            <td nowrap>所在地区：</td>
            <td><?php echo $Province.$City.$Area;?></td>
          </tr>
          <tr>
            <td nowrap>商家地址：</td>
            <td><?php echo $rsBiz["Biz_Address"];?></td>
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
            <td nowrap>结算类型：</td>
            <td><?php echo $rsBiz["Finance_Type"]==0 ? '按交易额比例' : '具体产品设置';?></td>
          </tr>
          <?php if($rsBiz["Finance_Type"]==0){?>
          <tr>
            <td nowrap>网站提成：</td>
            <td><?php echo $rsBiz["Finance_Rate"];?> %</td>
          </tr>
		  <?php }?>

           <tr>
            <td nowrap>结算比例：</td>
            <td><?php echo $rsBiz["PaymenteRate"];?>% &nbsp;&nbsp;注：商家财务结算时,按照结算比例款项一部分转向商家指定的卡号,剩下的部分转入商家绑定的前台会员的余额中。</td>
          </tr>

          <tr>
            <td nowrap>加入时间：</td>
            <td><?php echo date("Y-m-d H:i:s",$rsBiz["Biz_CreateTime"]);?></td>
          </tr>
          <tr>
            <td nowrap>状态：</td>
            <td><?php echo $_STATUS[$rsBiz["Biz_Status"]];?></td>
          </tr>
        </table>
      </div>
    </div>
  <div class="clear"></div>
  </div>
</div>
</body>
</html>