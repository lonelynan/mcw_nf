<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
//file_put_contents('ddd.txt', $xml);
$xmlObj = simplexml_load_string($xml);
$Ls_OrderID = substr($xmlObj->out_trade_no, 10);
$LS_rsOrder = $DB->GetRs("biz_pay","Users_ID","where id='".$Ls_OrderID."'");
$UsersID = $LS_rsOrder['Users_ID'];
require_once "lib/WxPay.Api.php";
require_once 'lib/WxPay.Notify.php';

class PayNotifyCallBack extends WxPayNotify
{
	public $db;
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}

		if ($data['result_code'] == 'SUCCESS' && $data['return_code'] == 'SUCCESS') {
			$out_trade_no = $data['out_trade_no'];
			$OrderID = substr($out_trade_no, 10);
			$rsOrder = $this->db->GetRs("biz_pay","*","where id='".$OrderID."'");
			$UsersID = $rsOrder['Users_ID'];
			$Biz_ID = $rsOrder['biz_id'];
			$Status = $rsOrder['status'];
			$years = $rsOrder['years']; //时间
			$expiredate = time()+$years*365*24*3600;
			$bond_free = $rsOrder['bond_free']; //保证金

			if ($Status == 0) {
				if (!$rsOrder) {
					$msg = "不存在此订单";
					return false;
				} else {
					$Data = array(
							"status" => 1,
							"paytime" => time()
					);
					mysql_query("BEGIN");
					$flag_a = $this->db->set('biz_pay',$Data,"where Users_ID='".$UsersID."' and Biz_ID='".$Biz_ID."' and id='".$OrderID."'");

					$Data1 = array(
							"bond_free" => $bond_free,
							"expiredate" => $expiredate,
							'is_pay' => 1,
							'is_biz' => 1
					);
					$flag_b = $this->db->set('biz',$Data1,"where Users_ID='".$UsersID."' and Biz_ID=".$Biz_ID);
					if ($flag_a && $flag_b){
						mysql_query('commit');
						$msg = '回调成功';
						return true;
					}else{
						mysql_query("ROLLBACK");
						$msg = '回调失败';
						return false;
					}
				}
			}else {
				$msg = '回调成功';
				return true;
			}
		}
		return false;
	}
}

$notify = new PayNotifyCallBack();
$notify->db = $DB;
$notify->Handle(false);
?>