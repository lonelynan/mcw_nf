<?php
require_once('../global.php');

$UsersID = $rsBiz['Users_ID'];
$bizInfo = $DB->GetRs("biz","*","where Users_ID='".$UsersID."' and Biz_ID=".$_SESSION["BIZ_ID"]);
$action = empty($_REQUEST["action"]) ? "" : $_REQUEST["action"];

if ($action == "checkout") {
 //   print_r($_POST);
    
    if (empty($_POST['year_money']) || empty($_POST['bond_money'])) {
        $res = array(
		"status"=>0,
		"msg"=>"请选择分类和年限！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }
    $bond_money = max($_POST['bond_money']);
    $year_nums = trim($_POST['year_money']);
   
 
    $bizConfig = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
    if (!empty($bizConfig['year_fee'])) {
        $year_list = json_decode($bizConfig['year_fee'],true);
        $year_key = array_search($year_nums,$year_list['name']);
    }

    $year_money = isset($year_list['value']["$year_key"])?$year_list['value']["$year_key"]:'0';   
    $payinfo['bond_money'] = $bond_money;
    $payinfo['year_nums'] = $year_nums;//年限
    $payinfo['year_money'] = $year_money;//年费
    $payData['payinfo'] = !empty($payinfo)?json_encode($payinfo,JSON_UNESCAPED_UNICODE) :'';
 
    mysql_query('BEGIN');
    $Flag_a = $DB->Set("biz_apply",$payData,"where Biz_ID=".$_SESSION["BIZ_ID"]);
 
    $data1['bond_free'] = $bond_money;
    $data1['years'] = $year_nums; //年限
    $data1['year_free'] = $year_money;//年费
    $data1['total_money'] = $bond_money+$year_money;
    $data1['Biz_ID'] = $_SESSION['BIZ_ID'];
    $data1['Users_ID'] = $rsBiz['Users_ID'];
    $data1['type'] = 1;
    $data1['addtime'] = time();
    
    $applyInfo = $DB->GetRs("biz_pay",'*',"where type = 1 and Biz_ID=".$_SESSION["BIZ_ID"]);
    if (!empty($applyInfo)) {
       
        if ($applyInfo['status'] == 1) {  //已经支付成功的，不可重复提交
            mysql_query("ROLLBACK");
            $res = array(
		"status"=>0,
		"msg"=>"提交失败，您已经支付过了"
            );
            echo json_encode($res,JSON_UNESCAPED_UNICODE);
            exit;
            
        }
        $payid = $applyInfo['id'];
        $Flag_b = $DB->Set('biz_pay',$data1,"where Biz_ID=".$_SESSION["BIZ_ID"].' and id = '.$payid);
        
    } else {
        $Flag_b = $DB->ADD('biz_pay',$data1);
        $payid = $DB->insert_id();
    }
    
   
    if ($Flag_a && $Flag_b) {
        
        mysql_query('commit');
        $url = "/biz/authen/pay.php?payid=".$payid;
        $res = array(
                "url" => $url,
		"status"=>1,	 
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    } else {
        mysql_query("ROLLBACK");
        $res = array(
		"status"=>0,
		"msg"=>"提交失败！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }   
} elseif ($action == "payment") {
    //var_dump($_POST);
    $OrderID = empty($_POST['OrderID']) ? 0 : $_POST['OrderID'];
    
    $rsOrder=$DB->GetRs("biz_pay","*","where Users_ID='".$UsersID."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and id='".$OrderID."'");
    if ($rsOrder['status'] > 0) {
        $res = array(
                "msg" => "该订单已支付不可重复支付",
                "status" => 0
        );
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }
    $Data=array(
        "order_paymentmethod"=>$_POST['PaymentMethod'],
    );
    $Flag = $DB->Set ( "biz_pay", $Data, "where Users_ID='" . $UsersID . "' and Biz_ID='" . $_SESSION ["BIZ_ID"] . "' and id in(".$OrderID.")");
    if ($_POST['PaymentMethod'] == '支付宝') {
        $url = "/biz/authen/pay/alipay/alipay.php?OrderID=" . $OrderID;
        if($Flag){
            $res=array(
                "status"=>1,
                "url"=>$url,
                'method' => '支付宝'
            );
        }else{
            $res=array(
                "status"=>0,
                "msg"=>'在线支付出现错误'
            );

        }
    } else {
        $initUrl = $_SERVER['HTTP_HOST'] . '/biz/authen/pay/wxpay/native.php?OrderID=' . $OrderID . '&UsersID=' . $UsersID;
        $ch = curl_init($initUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $wxPayUrl = curl_exec($ch);
		if ($wxPayUrl == 110) {
        $res=array(
            "status"=>0,
            "msg"=>'请检查你的微信配置！'
        );
		} else {
			$res=array(
            "status"=>1,
            "url"=>'http://paysdk.weixin.qq.com/example/qrcode.php?data=' .$wxPayUrl,
            'method' => '微支付',
            'id' => $OrderID
        );
		}
    }
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
    exit;    
}elseif ($action == 'check_status') {
    $OrderID = empty($_GET['orderid']) ? 0 : (int)$_GET['orderid'];
    $status = $DB->GetRs('biz_pay', 'status', "where id = '".$OrderID."'");
    echo $status['status'];
    exit;
}elseif ($action == 'checkout_man') {
    if ($bizInfo['is_biz'] != 1) {
        $res = array(
		"status"=>0,
		"msg"=>"您还未完成商家认证,不能续费！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }
    if (empty($_POST['year_money'])) {
        $res = array(
		"status"=>0,
		"msg"=>"请选择年限！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }
 
    $year_nums = trim($_POST['year_money']);
   
 
    $bizConfig = $DB->GetRs("biz_config","*","where Users_ID='".$rsBiz['Users_ID']."'");
    if (!empty($bizConfig['year_fee'])) {
        $year_list = json_decode($bizConfig['year_fee'],true);
        $year_key = array_search($year_nums,$year_list['name']);
    }


    $year_money = isset($year_list['value']["$year_key"])?$year_list['value']["$year_key"]:'0';   //print_r($year_money);die;
 
    $data1['years'] = $year_nums; //年限
    $data1['year_free'] = $year_money;//年费
    $data1['total_money'] = $year_money;
    $data1['Biz_ID'] = $_SESSION['BIZ_ID'];
    $data1['Users_ID'] = $rsBiz['Users_ID'];
    $data1['type'] = 2; //类型2年费续费
    $data1['addtime'] = time();
    
    $payInfo = $DB->GetRs("biz_pay",'*',"where type = 2 and status = 0 and Biz_ID=".$_SESSION["BIZ_ID"]);
    if (!empty($payInfo)) {
        if ($payInfo['status'] == 1) {  //已经支付成功的，不可重复提交
           
            $res = array(
		"status"=>0,
		"msg"=>"提交失败！"
            );
            
        }
        $payid = $payInfo['id'];
        $Flag_a = $DB->Set('biz_pay',$data1,"where Biz_ID=".$_SESSION["BIZ_ID"].' and id = '.$payid);
    } else {
        $Flag_a = $DB->ADD('biz_pay',$data1);
        $payid = $DB->insert_id();
    }
    if ($Flag_a) {
        $url = "/biz/authen/chargeman_pay.php?payid=".$payid;
        $res = array(
                "url" => $url,
		"status"=>1,	 
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    } else {
        mysql_query("ROLLBACK");
        $res = array(
		"status"=>0,
		"msg"=>"提交失败！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }   
    
} elseif ($action == "chargeman_payment") {
    $OrderID = empty($_POST['OrderID']) ? 0 : $_POST['OrderID'];
    $rsOrder=$DB->GetRs("biz_pay","*","where Users_ID='".$UsersID."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and id='".$OrderID."'");
    if ($rsOrder['status'] > 0) {
        $res = array(
                "msg" => "该订单已支付不可重复支付",
                "status" => 0
        );
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }
	if($rsOrder['total_money']<=0){
		 $res=array(
                "status"=>0,
                "msg"=>'支付金额必须大于零'
         );
		 echo json_encode($res,JSON_UNESCAPED_UNICODE);
		 exit;
	}
    $Data=array(
        "order_paymentmethod"=>$_POST['PaymentMethod'],
    );
    $Flag = $DB->Set ( "biz_pay", $Data, "where Users_ID='" . $UsersID . "' and Biz_ID='" . $_SESSION ["BIZ_ID"] . "' and id in(".$OrderID.")");
	
	
	if ($_POST['PaymentMethod'] == '支付宝') {
        $url = "/biz/authen/pay/alipay/alipay_man.php?OrderID=" . $OrderID;
        if($Flag){
            $res=array(
                "status"=>1,
                "url"=>$url,
                'method' => '支付宝'
            );
        }else{
            $res=array(
                "status"=>0,
                "msg"=>'在线支付出现错误'
            );

        }
    } else {
        $initUrl = $_SERVER['HTTP_HOST'] . '/biz/authen/pay/wxpay/native.php?OrderID=' . $OrderID . '&UsersID=' . $UsersID;
        $ch = curl_init($initUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $wxPayUrl = curl_exec($ch);
		if ($wxPayUrl == 110) {
        $res=array(
            "status"=>0,
            "msg"=>'请检查你的微信配置！'
        );
		} else {
			$res=array(
            "status"=>1,
            "url"=>'http://paysdk.weixin.qq.com/example/qrcode.php?data=' .$wxPayUrl,
            'method' => '微支付',
            'id' => $OrderID
        );
		}
    }
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
    exit;
}elseif ($action == 'checkout_bond') {
    if ($bizInfo['is_biz'] != 1) {
        $res = array(
		"status"=>0,
		"msg"=>"您还未完成商家认证,不能追加保证金！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }
    if (empty($_POST['bond_money'])) {
        $res = array(
		"status"=>0,
		"msg"=>"请选择保证金金额！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }
 
    $bond_money = max($_POST['bond_money']); //所选的保证金
    $bond_free = !empty($bizInfo['bond_free'])?$bizInfo['bond_free']:'0'; //已交
    $bond_need = $bond_money - $bond_free; //保证金所需金额
    if ($bond_need ==0) {
        $res = array(
		"status"=>0,
		"msg"=>"您所选保证金和您当前保证金金额相等,不能提交！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
        
    }
    if ($bond_need < 0) {
        $res = array(
		"status"=>0,
		"msg"=>"您所选保证金小于您当前保证金金额,不能提交！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
        
    }
    
    $data1['bond_free'] = $bond_need; //本次订单保证金金额
    $data1['total_money'] = $bond_need;
    $data1['Biz_ID'] = $_SESSION['BIZ_ID'];
    $data1['Users_ID'] = $rsBiz['Users_ID'];
    $data1['type'] = 3; //类型2年费续费
    $data1['addtime'] = time();
    
    $payInfo = $DB->GetRs("biz_pay",'*',"where type = 3 and status = 0 and Biz_ID=".$_SESSION["BIZ_ID"]);
    if (!empty($payInfo)) {
        if ($payInfo['status'] == 1) {  //已经支付成功的，不可重复提交
           
            $res = array(
		"status"=>0,
		"msg"=>"提交失败！"
            );
            
        }
        $payid = $payInfo['id'];
        $Flag_a = $DB->Set('biz_pay',$data1,"where Biz_ID=".$_SESSION["BIZ_ID"].' and id = '.$payid);
    } else {
        $Flag_a = $DB->ADD('biz_pay',$data1);
        $payid = $DB->insert_id();
    }
    if ($Flag_a) {
        $url = "/biz/authen/chargebond_pay.php?payid=".$payid;
        $res = array(
                "url" => $url,
		"status"=>1,	 
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    } else {
        mysql_query("ROLLBACK");
        $res = array(
		"status"=>0,
		"msg"=>"提交失败！"
	);
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }   
    
}elseif ($action == "chargebond_payment") {
    $OrderID = empty($_POST['OrderID']) ? 0 : $_POST['OrderID'];
    $rsOrder=$DB->GetRs("biz_pay","*","where Users_ID='".$UsersID."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and id='".$OrderID."'");
    if ($rsOrder['status'] > 0) {
        $res = array(
                "msg" => "该订单已支付不可重复支付",
                "status" => 0
        );
        echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
    }
    $Data=array(
        "order_paymentmethod"=>$_POST['PaymentMethod'],
    );
    $Flag = $DB->Set ( "biz_pay", $Data, "where Users_ID='" . $UsersID . "' and Biz_ID='" . $_SESSION ["BIZ_ID"] . "' and id in(".$OrderID.")");
    $url = "/biz/authen/pay/alipay/alipay_bond.php?OrderID=" . $OrderID;
    if($Flag){
            $res=array(
                    "status"=>1,
                    "url"=>$url
            );
    }else{
            $res=array(
                    "status"=>0,
                    "msg"=>'在线支付出现错误'
            );
    }
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
    exit;
}

?>