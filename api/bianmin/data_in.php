<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
//设立产品类别  0  联通   1 移动
if (isset($_GET["UsersID"])) {
	$UsersID = $_GET["UsersID"];
} else {
	echo '缺少必要的参数';
	exit;
}
//设立产品类别  0  联通   1 移动
$rsConfig = shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig,$dis_config);

$is_login=1;
$owner = get_owner($rsConfig,$UsersID);
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/wechatuser.php');

//获得平台所有者的id  
$user_id=$_SESSION[$UsersID.'User_ID'];

$mobile_old = array();
$unicom_old = array();
$mobile_new = array();
$unicom_new = array();
//流量为1   话费为0
$Server_Type=1;
//获取会员id
// $user_id=$_SESSION[$_SESSION['Users_ID'].'User_ID'];
// 读取话费数据
$DB->query("SELECT * FROM bianmin_server WHERE Users_ID='".$UsersID."'and Server_Type='".$Server_Type."'");
	$i=0;
	while($res=$DB->fetch_assoc()){ 	 
			 	//判断数据是移动还是联通
			 if ($res['Service_Provider']==1) {
			 	//获得移动流量价格数组
			 	$mobile_new=json_decode($res['Server_Newprice']);
			 	$mobile_old=json_decode($res['Server_Oldprice']);
			 }else{
			 	//获得联通流量价格数组
			 	$unicom_new=json_decode($res['Server_Newprice']);;
			 	$unicom_old=json_decode($res['Server_Oldprice']);;
			 }	 
	$i++;
 	}
$rsUser = $DB->GetRs('user', 'User_Money', ' WHERE `Users_ID` = "'.$UsersID.'" and `User_ID` = '.$user_id);

$myaccount = $DB->GetRs("mycount","*","WHERE usersid='".$UsersID."'");
if(empty($myaccount['expiretime'])){
	$expiretime = 0;
}else{
	$expiretime = $myaccount['expiretime'];
}

if(time()>=$expiretime){
	$paras_app = array(
		'client_id'=>$myaccount['appkey'],
		'grant_type'=>'refresh_token',
		'state'=>'STATE',
		'refresh_token'=>$myaccount['refreshToken']
	);
	require_once($_SERVER["DOCUMENT_ROOT"].'/pay/qianmi/refreshaccesstoken.php');
	$refreshaccesstoken = new refreshaccesstoken();
	if($data_app['status']==1){
		$DB->Set('mycount',array('token'=>$data_app['access_token'],'refreshToken'=>$data_app['refresh_token'],'expiretime'=>$data_app['expiretime']),'where usersid="'.$UsersID.'"');
	}else{
		echo '获取access_token失败';
		exit;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>流量充值</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/bianmin/style.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js'></script>
<script type='text/javascript' src='/static/api/bianmin/js.js?t=<?php echo time();?>'></script>
<style type="text/css">
	html, body { background: #FFF; }
	ul.ultips { display: block; overflow: hidden; margin: 10px 0; }
	span.little_tips { display: block; overflow: hidden; height: 30px; line-height: 36px; text-align: center; width: 100%; margin-left: 4px; }
	.bottom_href { position: fixed; bottom: 0px; height: 40px; line-height: 40px; text-align: center; width: 100%; border-top: 1px solid #ddd; background:#FFF }
	.bottom_clear{height: 56px; width: 100%; }
	.bottom_href a { display: block; text-align: center; width: 100%; font-size: 16px; }
</style>
</head>
<body>
<div id="bianmin_header">
	<a href="/api/<?php echo $UsersID; ?>/bianmin/">话费充值</a>
    <a href="/api/<?php echo $UsersID; ?>/bianmin/data/" class="c">流量充值</a>
    <div class="clear"></div>
</div>
<div id="gift">
  
		<div id="my">
		  <form id="user_form">
		    <div class="input">
		      <input id="phonenum" type="num" name="num" value="" maxlength="11" placeholder="请输入手机号" notnull /><p class="api" style="margin-top: -10px; text-indent:10px;"> </p><strong class="app" style=" float: right; overflow: hidden;text-align: center; margin-right:8px; font-size:16px"></strong>
				<div>
					<label style="width:90%; margin:8px auto 10px; display:block; font-size:16px; ">充值面值：</label>
					<ul class="ultips">
	<?php
		// 获取数组长度
		if (isset($mobile_old)) {
			$length=count($mobile_old);
			$type=1;
			$Server_Type=1; 
			for($i=0; $i<= $length-1; $i++){ 
				echo '<li class="li" style="width:65px; height:60px; border-radius:5px; float: left;overflow: hidden;padding-right: 8px; border: 1px solid #D94600;  margin-left: 13px; margin-right: 10px;border: 1px solid #d94600; margin-bottom: 10px;">
							<span>
									<p class="aja" style="line-height: 9px;  text-align: center;">'.$mobile_new[$i].'<span class="little_tips">售价:'.$mobile_old[$i].'元</span></p>
									<from>
										<input class="put" type="hidden" value="'.$i.'" />
										<input class="type" type="hidden" value="'.$type.'" />
										<input class="st" type="hidden" value="'.$Server_Type.'" />
									

									<input class="data" type="hidden" name="name" value="'.$mobile_old[$i].'"/>
									<input class="money" type="hidden" name="money"  value=""/>
									<input class="userid" type="hidden" name="userid"  value="'.$user_id.'"/>


									</from>
							</span>

					</li>';
			}
		}else{
			$length=count($unicom_old);
			$type=0;
			$Server_Type=1; 
			for($i=0; $i<= $length-1; $i++){ 
				echo '<li class="li" style="width:65px; height:60px; border-radius:5px; float: left;overflow: hidden;padding-right: 8px; border: 1px solid #D94600;  margin-left: 13px; margin-right: 10px;border: 1px solid #d94600; margin-bottom: 10px;">
							<span>
									<p class="aja" style="line-height: 9px;  text-align: center; ">'.$unicom_new[$i].'<span class="little_tips">'.$unicom_old[$i].'</span></p>
									<from>
										<input class="put" type="hidden" value="'.$i.'" />
										<input class="type" type="hidden" value="'.$type.'" />
										<input class="st" type="hidden" value="'.$Server_Type.'" />


									<input class="da" type="hidden" name="name" value="'.$unicom_old[$i].'"/>
									<input class="money" type="hidden" name="money"  value=""/>
									<input class="userid" type="hidden" name="userid"  value="'.$user_id.'"/>

									</from>
							</span>
					</li>';
			}
		}




	?>					
						

					<ul>
				</div>



		    </div>
		    <div class="input"></div> 
		    <div class="submit">
		     <input type="button" class="submit_btn" value="确定"  style=" margin-left: 20px;margin-top: 20px; border-radius: 5px;color:white; width: 90%; background: #f60 none repeat scroll 0 0; line-height: 40px; " />
		    <input type="hidden" name="action" value="profile" />
		  </form>
		</div>
		
		<div class="input">
		      <input type="button" class="submit_btn" id="mybtn" value="我的分销" style=" margin-left: 20px;margin-top: 20px; border-radius: 5px;color:white; width: 90%; background: #f60 none repeat scroll 0 0; line-height: 40px; " />
		    <input type="hidden" name="action" value="profile" />

		</div>




  </div>


<link href='/static/api/distribute/css/area_proxy.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />

<?php $rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");?>
<div class="pay_select_list" id="" data-value="">
    <h2><span>[×]</span>请选择支付方式</h2>
	<h3>支付总额：<span></span></h3>
    <?php if(!empty($rsPay["PaymentWxpayEnabled"])){ ?>
	<a href="javascript:void(0)" class="btn btn-default btn-pay direct_pay" id="wzf" data-value="1"><img  src="/static/api/shop/skin/default/wechat_logo.jpg" width="16px" height="16px"/>&nbsp;微信支付</a>
	<?php }?>
	<?php if(!empty($rsPay["Payment_AlipayEnabled"])){?>
	<a href="javascript:void(0)" class="btn btn-danger btn-pay direct_pay" id="zfb" data-value="2"><img  src="/static/api/shop/skin/default/alipay.png" width="16px" height="16px"/>&nbsp;支付宝支付</a>
	<?php }?>
	<a href="javascript:void(0)" class="btn btn-warning  btn-pay" id="money" data-value="余额支付"><img  src="/static/api/shop/skin/default/money.jpg"  width="16px" height="16px"/>&nbsp;余额支付</a>
    <p class="money_info">
		<b style="display:block; width:85%; height:40px; line-height:38px; font-size:14px; margin:0px auto; font-weight:normal">您当前余额：<span style="padding:0px 5px; font-size:16px; color:#F00">&yen;<?php echo $rsUser['User_Money']; ?></span></b>
    	<input type="password" value="" placeholder="请输入支付密码，默认123456" style="display:block; width:85%; margin:5px auto 0px; border:1px #dfdfdf solid; border-radius:5px; height:40px; line-height:40px;">
        <button style="display:block; width:85%; margin:5px auto 0px; border:none; border-radius:5px; background:#4F93CE; color:#FFF; font-size:14px; text-align:center; height:40px; line-height:38px;">确认支付</button>
    </p>
</div>

<div class="products_list_tobuy">
	<h2><span>[×]</span>请选择商品</h2>
    <div class="products_list_content"></div>
</div>
<div class="body_bg"></div>



<script type="text/javascript">
var UsersID = "<?=$UsersID?>";
var ajax_url  = "/api/"+UsersID+"/bianmin/ajax/";
$(document).ready(chongzhi_obj.chongzhi_init);
</script>
<div class="bottom_clear"></div>
<div class="bottom_href">
	<a href="/api/<?php echo $UsersID; ?>/bianmin/czrecord/">我的充值记录</a>
</div>
</body>
</html>