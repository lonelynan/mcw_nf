<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Submit.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Corefunction.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Md5function.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/FF/vendor/alipay/pc/Notify.php');

    if(empty($_POST)) {//判断POST来的数组是否为空
        echo 'fail';
        exit;
    }
    
    $out_trade_no = $_POST['out_trade_no'];
    $OrderID = substr($out_trade_no, 10);
	$rsOrder = $DB->GetRs("biz_pay","*","where id='".$OrderID."'");
    
	if(!$rsOrder){
        echo 'fail';
        exit;
    }
    $UsersID = $rsOrder['Users_ID'];
    $Biz_ID = $rsOrder['biz_id'];
    $Status = $rsOrder['status'];     
    $years = $rsOrder['years']; //时间
    $expiredate = time()+$years*365*24*3600;
    $bond_free = $rsOrder['bond_free']; //保证金
    
    $users_payconfig = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
    $alipay_config = array(
        'sign_type'=>'MD5',
        'key'=>$users_payconfig['Payment_AlipayKey'],
        'transport'=>'http',
        'partner'=>$users_payconfig['Payment_AlipayPartner'],
        'input_charset'=> strtolower('utf-8')
    );
          
    $alipayNotify = new \vendor\alipay\pc\Notify($alipay_config);//计算得出通知验证结果
    $verify_result = $alipayNotify->verifyNotify();
	
    if ($verify_result) { //验证成功	 
        if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
		
            if ($Status == 0) {
                if (!$rsOrder) {
                    echo 'fail';
                    exit;
                } else {
                    $Data = array(
                        "status" => 1,
                        "paytime" => time()
                    );
                    mysql_query("BEGIN");
					$flag_a = $DB->set('biz_pay',$Data,"where Users_ID='".$UsersID."' and Biz_ID='".$Biz_ID."' and id='".$OrderID."'");
                    
                    $Data1 = array(
                        "bond_free" => $bond_free,
                        "expiredate" => $expiredate,
                        'is_pay' => 1,
                        'is_biz' => 1
                    );					
					$flag_b = $DB->set('biz',$Data1,"where Users_ID='".$UsersID."' and Biz_ID=".$Biz_ID);					
                    if ($flag_a && $flag_b){					
                        mysql_query('commit');
                        echo 'success';
			exit;
                    }else{
                        mysql_query("ROLLBACK");
                        echo 'fail';
			exit;
                    }
                } 
            }else {
                echo 'success';
                exit;
            }
        }
        echo 'success';//请不要修改或删除
    }else {
	 
        //验证失败
        echo 'fail';
    }    
?> 