<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"]."/pay/alipay/lib/alipay_submit.class.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/pay/alipay/lib/alipay_submit_refund.class.php");
$notify_url = "http://".$_SERVER['HTTP_HOST']."/pay/alipay/refund_pintuan_notify_url.php";

// $orderlist  订单id列表   $type  :   pwd 为有密退款   nopwd 为无密退款
function alipay_refund($orderidlist,$type = 'pwd',$UsersID = ''){
    global $DB,$notify_url;
    $userinfo = "";
    $payConfig = "";
    
    if($UsersID) {
        $userinfo = $DB->GetRs("users","*","where Users_ID='" . $UsersID . "'");
        $rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='" . $UsersID . "'");
        if(empty($userinfo) || empty($rsPay)) continue;
        $idlist = implode(',', $orderidlist);
        $res = $DB->Get("user_order", "*", "WHERE Users_ID = '" . $UsersID . "' AND Order_ID IN ({$idlist}) AND Order_Status IN (2,3,4,5)");
        $orderlist = $DB->toArray($res);
        $detail_data = '';
        mt_srand((double) microtime() * 1000000);
        $no = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $batch_no = date("YmdHis",time()).$no;
        if(!empty($orderlist)){
            foreach($orderlist as $key => $val){
                if(!$val['transaction_id'] || $val['transaction_id']=='') continue;
                $detail_data .= $val['transaction_id']."^".$val['Order_TotalPrice']."^正常退款#";
            }
            
            if(!$detail_data) return false;
            $detail_data = trim($detail_data,'#');
            $batch_num = substr_count($detail_data,"#")+1;
            if($type == 'pwd'){
                refund_pwd_alipay($batch_no,$detail_data,$rsPay,$batch_num,$orderidlist);
            }else{
                refund_alipay($batch_no,$detail_data,$rsPay,$batch_num);
            }
        }
    
    }else{
        if(is_array($orderidlist) && !empty($orderidlist)){
            $idlist = implode(',', $orderidlist);
            $sql = "SELECT Order_ID,Users_ID FROM user_order WHERE Order_ID IN ($idlist) GROUP BY Users_ID";
            $res = $DB->query($sql);
            $list = $DB->toArray($res);

            if(!empty($list)){
                foreach ($list as $k=>$v){
                    $userinfo = $DB->GetRs("users","*","where Users_ID='" . $v['Users_ID'] . "'");
                    $rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='" . $v['Users_ID'] . "'");
                    if(empty($userinfo) || empty($rsPay)) continue;
                    $res = $DB->Get("user_order", "*", "WHERE Users_ID = '" . $v['Users_ID'] . "' AND Order_ID IN ({$idlist}) AND Order_Status IN (2,3,4,5)");
                    $orderlist = $DB->toArray($res);
                    $detail_data = '';
                    mt_srand((double) microtime() * 1000000);
                    $no = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                    $batch_no = date("YmdHis",time()).$no;
                    
                    if(!empty($orderlist)){
                        foreach($orderlist as $key => $val){
                            if(!$val['transaction_id'] || $val['transaction_id']=='') continue;
                            $detail_data .= $val['transaction_id']."^".$val['Order_TotalPrice']."^正常退款#";
                        }
                        if(!$detail_data) return false;
                        $batch_num = count($orderlist);
                        $detail_data = trim($detail_data,'#');
                        refund_alipay($batch_no,$detail_data,$rsPay,$batch_num);
                    }
                } 
            }
        }
    }
}

function refund_pwd_alipay($batch_no,$detail_data,$rsPay,$batch_num,$orderidlist)
{
    global $notify_url;
    $alipay_config = [];
    
    $refund_date = date("Y-m-d H:i:s",time());
    if($detail_data == "") return false;
    $alipay_config['partner']		= trim($rsPay["Payment_AlipayPartner"]);
    $alipay_config['seller_user_id']=$alipay_config['partner'];
    $alipay_config['key']			= trim($rsPay["Payment_AlipayKey"]);
    $alipay_config['notify_url']= $notify_url;
    $alipay_config['sign_type']    = 'MD5';
    $alipay_config['refund_date']= $refund_date;
    $alipay_config['service']='refund_fastpay_by_platform_pwd';
    $alipay_config['input_charset']= 'utf-8';
    $alipay_config['cacert']    = getcwd().'\\cacert.pem';
    $alipay_config['transport']    = 'http';
    
    $parameter = array(
        "service" => trim($alipay_config['service']),
        "partner" => trim($alipay_config['partner']),
        "notify_url"	=> trim($alipay_config['notify_url']),
        "seller_user_id"	=> trim($alipay_config['seller_user_id']),
        "refund_date"	=> trim($alipay_config['refund_date']),
        "batch_no"	=> $batch_no,
        "batch_num"	=> $batch_num,
        "detail_data"	=> $detail_data,
        "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
        
    );
    logRefund($batch_no, $orderidlist, $rsPay['Users_ID'], time());
    $alipaySubmit = new AlipaySubmitRefund($alipay_config);
    $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
    echo $html_text;
    exit;
}

function refund_alipay($batch_no,$detail_data,$rsPay,$batch_num)
{
    global $notify_url;
    $alipay_config = [];
    if($detail_data == "") return false;
    $alipay_config['partner']		= trim($rsPay["Payment_AlipayPartner"]);
    $alipay_config['seller_email']	= trim($rsPay["Payment_AlipayAccount"]);
    $alipay_config['key']			= trim($rsPay["Payment_AlipayKey"]);
    $alipay_config['private_key_path']	= getcwd().'\\key\\rsa_private_key.pem';
    $alipay_config['ali_public_key_path']= getcwd().'\\key\\alipay_public_key.pem';
    $alipay_config['sign_type']    = 'MD5';
    $alipay_config['input_charset']= 'utf-8';
    $alipay_config['cacert']    = getcwd().'\\cacert.pem';
    $alipay_config['transport']    = 'http';

    $refund_date = date("Y-m-d H:i:s",time());
    $parameter = array(
            "service" => "refund_fastpay_by_platform_nopwd",
            "partner" => trim($alipay_config['partner']),
            "notify_url"	=> $notify_url,
            "batch_no"	=> $batch_no,
            "refund_date"	=> $refund_date,
            "batch_num"	=> $batch_num,
            "detail_data"	=> $detail_data,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
    );
    $alipaySubmit = new AlipaySubmit($alipay_config);
    $html_text = $alipaySubmit->buildRequestHttp($parameter);
    $doc = new DOMDocument();
    if($html_text){
        $doc->loadXML($html_text);
        if( ! empty($doc->getElementsByTagName( "alipay" )->item(0)->nodeValue) ) {
            $alipay = $doc->getElementsByTagName( "alipay" )->item(0)->nodeValue;
            if($alipay == "T") return true;
        }
    }
    return false;
}

//记录退款状态
function logRefund($batchno, $orderidlist, $UsersID, $Addtime)
{
    global $DB;
    $detail_data = implode(",", $orderidlist);
    if(!$batchno || !$detail_data || !$UsersID || !$Addtime) return false;
    $signData = md5($batchno.$UsersID.$detail_data.$Addtime);
    $flag = $DB->GetRs("alipay_refund","*","WHERE Batch_No='" . $batchno . "'");
    $data = [
        'Batch_No' => $batchno,
        'Users_ID' => $UsersID,
        'OrderList' => $detail_data,
        'Sign' => $signData,
        'Addtime' => $Addtime,
        'Status' => 0
    ];
    if(empty($flag)){
        $DB->Add("alipay_refund",$data);
    }
}