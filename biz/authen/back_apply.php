
<?php
require_once('../global.php');
$UsersID =  $rsBiz['Users_ID']; 
if ($_POST) {
    if (empty($_POST['info'])) {
        echo "<script>alert('请填写理由');history.back();</script>";
        exit;
    }
    if (empty($_POST['alipay_account'])) {
        echo "<script>alert('请填写支付宝账号');history.back();</script>";
        exit;
    }
    if (empty($_POST['alipay_username'])) {
        echo "<script>alert('请填写支付宝账户姓名');history.back();</script>";
        exit;
    } 
    
    if ($rsBiz['bond_free'] == 0) {
        echo "<script>alert('您没有保证金可退');history.back();</script>";
        exit;
    } 
    $BizBackInfo = $DB->GetRS('biz_bond_back','*','WHERE (status =1 or status=2)and Users_ID = "'.$UsersID.'" and biz_id = '.$_SESSION['BIZ_ID']); 
    if (!empty($BizBackInfo)) {
        echo "<script>alert('你已经申请过退款,不可重复提交!');history.back();</script>";
        exit;
    }
    
	$DB->get('user_order','Order_CartList','where Order_Status<4 and  Users_ID = "'.$UsersID.'"');
	while ($r = $DB->fetch_assoc()) {
		$Order_CartList = json_decode($r['Order_CartList'],true);
		if(!empty($Order_CartList) && isset($Order_CartList) && is_array($Order_CartList)) {
			foreach ($Order_CartList as $k => $v) {
				$productid = $k;
				$productInfo = $DB->GetRS('shop_products','*','WHERE  Products_ID = '.$k); 
				if ($productInfo['Biz_ID'] == $_SESSION['BIZ_ID']) {
					echo "<script>alert('您的店铺有未完成的订单,不可申请退款!');history.back();</script>";
					exit;
				}
				
			}
		}
		
	}
	
    $Data['info'] = trim($_POST['info']);;
    $Data['Users_Id'] = $UsersID;
    $Data['biz_id'] = $_SESSION['BIZ_ID'];
    $Data['back_money'] = $rsBiz['bond_free'];
	$Data['alipay_account'] = trim($_POST['alipay_account']);
    $Data['alipay_username'] = trim($_POST['alipay_username']);
    $Data['addtime'] = time();    
    $flag = $DB->add('biz_bond_back',$Data);
	if ($flag) {
		echo "<script>alert('保证金退款申请成功,我们会在5个工作日内完成审核,请耐心等待!');window.location.href='index.php';</script>";	
	}
    
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>保证金退还</title>


<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<style>
.msg { float:right;width:300px;margin-top:-42%;margin-right:20px;background:#fff; }
.msg ul { margin:20px 10px;}
.msg ul li { line-height:20px;}
.msg .title { height:30px; line-height:30px;padding:10px;padding-left:30px;border-bottom:1px solid #fcc;}
</style>
<link href="/static/biz/css/back_x.css" type="text/css" rel="stylesheet">
<link rel="stylesheet" href="/static/biz/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="/static/biz/css/font-awesome.min.css" type="text/css">
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css' />
    <div class="r_nav">
      <ul>
        <li class="cur"><a href="back_apply.php">保证金退还</a></li>
		<li class=""><a href="back_record.php">保证金退款记录</a></li>	
      </ul>
    </div>
    <div id="orders" class="r_con_wrap" style="width:75%;">
        <div class="w">
             
        <div class="process">
            退款流程：
            <span class="bg_x1">填写申请</span>
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
            <span class="bg_x1">提交审核</span>
            <i class="fa fa-arrow-right" aria-hidden="true"></i>
            <span class="bg_x1">退款成功</span>
            <div class="clear"></div>
			保证金金额：<?php echo empty($rsBiz['bond_free'])?'0':$rsBiz['bond_free'];?>元
            <form method="post" action="?">
			<div class="l" style="font-size: 16px;color: #666;">支付宝账号：</div><input style="line-height: normal;" name="alipay_account" ><div class="clear"></div>
			<div class="l" style="font-size: 16px;color: #666;">支付宝姓名：<input style="line-height: normal;" name="alipay_username" ></div><div class="clear"></div>
            <div class="l" style="font-size: 16px;color: #666;">&nbsp;&nbsp;&nbsp;退款理由：</div><textarea name="info" id="info" class="reason_x"></textarea>
            <div style="margin-bottom:30px;">
                
                <button id="btn" class="btn_xt">提交申请</button>
            </div>
            </form>
        </div>

    </div>
    </div>
     
  <div class="clear"></div>
  </div>
</div>
    <script>
    $("#btn").click(function(){
        var info = $("#info").val();
        if (info == '') {
            alert('请填写理由');
            return false;
        }
    })
    </script>
</body>
</html>


