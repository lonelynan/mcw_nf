<?php
require_once('../global.php');

if ($_POST) {	
    $agree = isset($_POST['agree'])?$_POST['agree']:'0';
    if ($agree != 1) {
        echo "<script>alert('您还没有同意协议');window.location='agreement.php'</script>";
        exit;
    } else {
        
        $Data=array(
		"is_agree"=>1,
	);
        $Flag=$DB->Set("biz",$Data,"where Biz_ID=".$_SESSION["BIZ_ID"]);
        if($Flag){
		echo '<script language="javascript">alert("签署成功");window.location="index.php";</script>';
	}else{
		echo "<script>alert('签署失败');window.location='agreement.php'</script>";
	}
	exit;
        
    }
    
} 

$item = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
$rsshop = $DB->GetRs("shop_config","ShopName","where Users_ID='".$rsBiz['Users_ID']."'");
$item["BaoZhengJin"] = str_replace('&quot;','"',$item["BaoZhengJin"]);
$item["BaoZhengJin"] = str_replace("&quot;","'",$item["BaoZhengJin"]);
$item["BaoZhengJin"] = str_replace('&gt;','>',$item["BaoZhengJin"]);
$item["BaoZhengJin"] = str_replace('&lt;','<',$item["BaoZhengJin"]);
//echo "<pre>"; print_r($item['BaoZhengJin']);


?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>在线签署商家入驻协议</title>
</head>
<link rel="stylesheet" href="/static/biz/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="/static/biz/css/style.min.css" type="text/css">
<link rel="stylesheet" href="/static/biz/css/agreement.min.css" type="text/css">
<body>
<section class="vbox">
    <header class="header bg-white b-b b-light">
        <p>在线签署商家入驻协议</p>
    </header>
    <div class="panel panel-default">
            <header class="panel-heading font-bold"><span id="header" data-protocol="0">在线签署商家入驻协议</span></header>
            <div class="panel-body">
                <div class="m b-a xy xy_0" style="height: 500px; overflow-y: scroll; padding: 10px; display: block;">
                <?php echo $item['BaoZhengJin']?>
               </div>
                <form class="form-horizontal form-validate" method="post" action="">
                    <div class="form-group">
                        <div class="col-sm-10 m-l">
                            <label class="">
                                <input type="checkbox" checked="" name="agree" value="1">
                                我已阅读、同意并接受《<?=$rsshop['ShopName']?>网络交易平台服务协议》
                            </label>
                            <input type="hidden" id="protocolType" name="protocolType" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-4 m-l">
                            <button type="submit" data-msg="您未勾选同意我们的协议" class="btn btn-primary js_agreement_btn" data-loading-text="保存中...">签署</button>
                            <a href="index.php"><button type="button" class="btn btn-default" data-toggle="back">取消</button></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
</section>
</body>
</html>
