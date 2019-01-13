<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/library/pay_order.class.php');
use Illuminate\Database\Capsule\Manager as DB;
set_time_limit(0);

// $flag 为 true 立即执行
Task::Run(function () {
	$schedule = DB::table('users_schedule')->get();
	$curTime = time();
	if (! empty($schedule)) {
		foreach ($schedule as $k => $v) {
			$SalesPayment = new SalesPayment($v['Users_ID']);
			$SalesPayment->payfor();
		}
	}
});

class Task
{
    public static function Run($callable)
    {
        if ($callable) {
            call_user_func($callable);
        }
        
    }
} 

class SalesPayment
{

    private $Users_ID;

    private $pay_order;

    public function __construct($Users_ID)
    {
        $this->Users_ID = $Users_ID;
    }
 
    public function payfor()
    {
	   global $DB;
	   $list = DB::table('shop_sales_payment')->where(['Payment_Type'=>1])->where('Status',3)->get(['Payment_ID','Payment_Sn','Payment_Type','OpenID','Biz_ID','Total','Bonus','Web','Amount']);
	   $pay_order = new pay_order($DB, 0);
	   file_put_contents($_SERVER["DOCUMENT_ROOT"].'/data/record.log','执行自动结算程序...\r\n\r\n',FILE_APPEND);
        if (! empty($list)) {
			$strcontent = '';
            foreach ($list as $k => $v) {
				$strcontent .= "结算ID:{$v['Payment_ID']}\t\t商家ID: {$v['Biz_ID']}\t\t 结算金额:{$v['Total']}\t\t平台获得{$v['Web']}\r\n\r\n";
				if($v['Total']>8000){
					DB::table('shop_sales_payment')->where(['Payment_ID' => $v['Payment_ID']])->update(['Status' => 4,'Msg' => '由于超过限额，系统中断此次交易']);
					DB::table('shop_sales_record')->where(['Payment_ID' => $v['Payment_ID']])->update(['Record_Status' => 1]);
				}else{
					$Data = $pay_order->withdraws($this->Users_ID, $v["OpenID"], $v['Total'], $v['Payment_Sn'], $v['Biz_ID'], 1);
					//file_put_contents($_SERVER["DOCUMENT_ROOT"].'/data/debug.log','自动结算结果调试'.print_r($Data,true).'...\r\n\r\n',FILE_APPEND);
					if ($Data["status"] == 1) {
						DB::table('shop_sales_payment')->where(['Payment_ID' => $v['Payment_ID']])->update(['Status' => 1]);
						DB::table('shop_sales_record')->where(['Payment_ID' => $v['Payment_ID']])->update(['Record_Status' => 1]);
					} else {
						if(empty($Data["msg"])){
							$Data["msg"] = '自动结算异常，需手动结算';
						}
						DB::table('shop_sales_payment')->where(['Payment_ID' => $v['Payment_ID']])->update(['Msg' => $Data["msg"]]);
					}
				}
                
            }
			file_put_contents($_SERVER["DOCUMENT_ROOT"].'/data/record.log',$strcontent . '\r\n',FILE_APPEND);
        }
    }
}