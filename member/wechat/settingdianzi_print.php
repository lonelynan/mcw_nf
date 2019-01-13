<?php
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}
$UsersID = $_SESSION['Users_ID'];

$action=empty($_REQUEST['action'])?'':$_REQUEST['action'];
if(!empty($action)){
	if($action=="del"){
		//删除
		$Flag=$DB->Del("users_express_info","usersid='".$UsersID."' and id=".$_GET["itemid"]);
		if($Flag){
			echo '<script language="javascript">alert("删除成功");window.location="settingdianzi_print.php";</script>';
		}else{
			echo '<script language="javascript">alert("删除失败");history.back();</script>';
		}
		exit;
	} elseif ($action=="knusermod") {
		if (empty($_POST['Value'])) {
			echo '<script language="javascript">alert("非法操作！");history.back();</script>';
		}
		$Data=array(
			"knuserid"=>$_POST['Value']
		);
		$Flagknmod = $DB->Set("users",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
		if($Flagknmod){
			$Data=array("status"=>1);
		}else{
			$Data=array("status"=>0);
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	} elseif ($action=="knapikeymod") {
		if (empty($_POST['Value'])) {
			echo '<script language="javascript">alert("非法操作！");history.back();</script>';
		}
		$Data=array(
			"knapikey"=>$_POST['Value']
		);
		$Flagknmod = $DB->Set("users",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
		if($Flagknmod){
			$Data=array("status"=>1);
		}else{
			$Data=array("status"=>0);
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
}else{
	$condition = "where usersid='".$UsersID."'";	
	$condition .= " order by id desc";
	$companys = $templates = array();
	$DB->Get("shop_shipping_company","Shipping_Code,Shipping_Name","where Users_ID='".$UsersID."'");
	while($r = $DB->fetch_assoc()){
		$companys[$r["Shipping_Code"]] = $r['Shipping_Name'];
	}
	
	$DB->getPage("users_express_info","id,shippingcode,cusname,cuspasswd",$condition,10);
	while($rs = $DB->fetch_assoc()){
		$templates[] = $rs;
	}
	$rsusers = $DB->GetRs("users", "knuserid,knapikey", "WHERE Users_ID='".$_SESSION["Users_ID"]."'");
}

$jcurid = 8;
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
<style>
.leftf{float:left;padding-top:4px;}
        .form_input {
    height: 28px;
	width:200px;
    line-height: 28px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 5px;
    padding: 0 5px;
	float:left;
	overflow:hidden;
	padding:0px 8px 0px 8px;	
}
.martop{color:red}
a{color:blue;text-decoration:none; }
a:hover {color:#CC3300;text-decoration:underline;}
    </style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/jiebase_menubar.php');?>
    <div class="r_con_wrap">
      <div class="control_btn">
	  <div id="update_post_tips"></div>
	  <ul>                   
            		<li class="item">
					<div class="leftf">快递鸟用户ID：</div>
                    <div class="form_input" id="knuserid"><?=$rsusers['knuserid']?></div>
					<div class="leftf">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;API Key：</div>
                    <div class="form_input" id="knapikey"><?=$rsusers['knapikey']?></div>&nbsp;&nbsp;&nbsp;<span class="martop"> 注：双击修改，回车确定！&nbsp;&nbsp;&nbsp;&nbsp;如果没有快递鸟账号<span class="blue"><a href="http://www.kdniao.com" target="_blank">点击此处</a></span>注册</font></span>
                </li>
                                        <li class="clear"></li>
            </ul>
      </div>
    </div>
  </div>
</div>
<script language="javascript">
			$('#knuserid').dblclick(function(){
			var o=$(this);
			if(o.children('input').size()){return false;}
			
			o.data('text', o.html()).html('<input value="'+(o.html()!='无'?o.html():'')+'">');
			o.children('input').select();
			o.children('input').keyup(function(event){
				if(event.which==13){
					$(this).blur();
					var value=$(this).val();
					if(value=='' || value=='无' || value==o.data('text')){
						o.html(o.data('text'));
						return false;
					}
					$('#update_post_tips').html('数据提交中...').css({left:$(window).width()/2-100}).show();
					
					$.post('?', "action=knusermod&Value="+value, function(data){
						if(data.status==1){
							var msg='修改成功！';
							o.html(value);
						}else if(data.msg!=''){
							var msg=data.msg;
							o.html(o.data('text'));
						}else{
							var msg='修改失败，出现未知错误！';
							o.html(o.data('text'));
						}
						$('#update_post_tips').html(msg).fadeOut(3000);
					}, 'json');
				}
			});
		});
		
		$('#knapikey').dblclick(function(){
			var o=$(this);
			if(o.children('input').size()){return false;}
			
			o.data('text', o.html()).html('<input value="'+(o.html()!='无'?o.html():'')+'">');
			o.children('input').select();
			o.children('input').keyup(function(event){
				if(event.which==13){
					$(this).blur();
					var value=$(this).val();
					if(value=='' || value=='无' || value==o.data('text')){
						o.html(o.data('text'));
						return false;
					}
					$('#update_post_tips').html('数据提交中...').css({left:$(window).width()/2-100}).show();
					
					$.post('?', "action=knapikeymod&Value="+value, function(data){
						if(data.status==1){
							var msg='修改成功！';
							o.html(value);
						}else if(data.msg!=''){
							var msg=data.msg;
							o.html(o.data('text'));
						}else{
							var msg='修改失败，出现未知错误！';
							o.html(o.data('text'));
						}
						$('#update_post_tips').html(msg).fadeOut(3000);
					}, 'json');
				}
			});
		});
</script>
</body>
</html>