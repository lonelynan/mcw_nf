<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once (CMS_ROOT . '/include/helper/url.php');
require_once (CMS_ROOT . '/pay/wxpay2/refund.php');
require_once (CMS_ROOT . '/include/helper/lib_pintuan.php');
require_once (CMS_ROOT . '/include/helper/backup.class.php');
require_once (CMS_ROOT . '/include/helper/tools.php');
require_once (CMS_ROOT . '/include/helper/flow.php');
require_once (CMS_ROOT . '/include/support/tool_helpers.php');

use Illuminate\Database\Capsule\Manager as DB;

if(isset($_GET['create']) && $_GET['create']){
	$Time = "10:14";
	$interval = 5;
	if(PHP_OS == 'WINNT'){
		require_once ($_SERVER["DOCUMENT_ROOT"] . '/cron/windowSchedule.php');
		$taskName = $_SERVER['HTTP_HOST']."_pintuan_refund_Task";
		$task = new Task();
		$task->add("ST", $Time);
		$task->add("RI", $interval);
		$task->add("RU",'"System"');
		$task->remove($taskName);
		$task->create($taskName ,"cmd /c " .$_SERVER["DOCUMENT_ROOT"]."/cron/Run.bat http://".$_SERVER['HTTP_HOST']."/cron/ptrefund.php");
		$task->getXML($taskName);
		echo "创建计划任务成功";exit;
		exit;
	}else{
		require_once ($_SERVER["DOCUMENT_ROOT"] . '/cron/crontab.php');
		// 非windows 执行
		$arrTime = explode(':', $Time);
		$cron  = "*/" . $interval . " " . $arrTime[0] . ' ';
		$cron .= "* * * ";
		$cron .= "curl -s http://".$_SERVER['HTTP_HOST']."/cron/ptrefund.php";
		try{
			Crontab::removeJob($cron);
			Crontab::addJob($cron);
		}catch(Exception $e){
			echo "请授予当前用户运行crontab的权限";exit;
		}
	}
}

set_time_limit(900);
file_put_contents(CMS_ROOT . '/data/pt.log', "开始执行计划任务在" . date("y-m-d H:i:s", time()), FILE_APPEND);
// 结束拼团活动
$UsersID = isset($_GET['UsersID']) && $_GET['UsersID'] ? preg_replace('/[^0-9a-z]/', '', $_GET['UsersID']) : '';
$teamid = isset($_GET['teamid']) && $_GET['teamid'] ? (int)$_GET['teamid'] : '';
$orderID = isset($_GET['orderid']) && $_GET['orderid'] ? preg_replace('/[^0-9]/', '', $_GET['orderid']) : '';
$time = time();

// 多长时间后拼团失败
$deadline = 24 * 60 * 60;

execRefund();

function execRefund()
{
    global $DB,$UsersID,$time,$teamid,$orderID,$capsule,$deadline;

    //活动时间已到或者24小时后，人数达到就设置为成功,否则设为拼团失败
    $sql = "update pintuan_team as pt left join shop_products as pp on pt.productid = pp.Products_ID set teamstatus = (case when pp
.pintuan_people <= pt.teamnum then 1 when pp.pintuan_people > pt.teamnum then 4 end ) where pt
.teamstatus = 0 and (pp.pintuan_end_time < " . $time . " or pt.addtime < ". ($time - $deadline) .")" . ($UsersID ? " AND pp.Users_ID = '" . $UsersID . "'" : "") . (!empty($teamid) ? ' and pt.id = ' . $teamid : '');
    DB::statement($sql);
    // 获取所有拼团失败的订单并按照订单进行退款
    $fields = "u.Order_ID,u.All_Qty,u.Coupon_Cash,u.Integral_Money,u.Biz_ID,u.Back_Integral,u.back_qty,u.Back_Amount,u.Back_Amount_Source,u.Order_CartList,u.Order_PaymentMethod,u.Order_Type,u.Users_ID,u.Order_TotalPrice,u.User_ID,u.Order_Status,u.transaction_id,t.order_id,t.teamid,u.Back_Amount,u.Order_TotalAmount,u.Order_Shipping,u.Owner_ID";
    $sql =  "SELECT " . $fields . " FROM pintuan_teamdetail AS t ";
    $sql .= "LEFT JOIN user_order AS u ON t.order_id = u.Order_ID ";
    $sql .= " WHERE t.teamid IN (SELECT id FROM pintuan_team AS pt LEFT JOIN shop_products AS pp ON pt.productid=pp.Products_ID WHERE (pt.teamstatus=4 OR pp.pintuan_end_time < " . $time . " OR pt.addtime < " . ($time - $deadline) .")) AND u.Is_Backup=0 AND u.Order_Type='pintuan' ". ($UsersID ? " AND u.Users_ID = '" . $UsersID . "'" : "") . ($orderID ? " AND u.Order_ID = " . $orderID  : "") ." ORDER BY u.User_ID ASC";
    $orderlist = DB::select($sql);
    if (! empty($orderlist)) {
        //生成付款单
        foreach ($orderlist as $order) {
            if ($order['Order_PaymentMethod']) {
                $Users_ID = $order['Users_ID'];
                /* 生成退款单 */
                $CartList = json_decode($order['Order_CartList'], true);
                if (!empty($CartList)) {
                    //分析产品参数
                    $proid = array_keys($CartList)[0];
                    $attr = array_keys($CartList[$proid])[0];
                    $proInfo = $CartList[$proid][$attr];

                    //商城配置信息
                    $rsConfig = shop_config($Users_ID);
                    //分销相关设置
                    $dis_config = dis_config($Users_ID);
                    //合并参数
                    $rsConfig = array_merge($rsConfig, $dis_config);

                    $man_reduce_act = get_hand_man_reduce_act($Users_ID, $CartList, $rsConfig);
                    if($CartList[$proid][$attr]){
                        unset($CartList[$proid][$attr]);
                    }
                    if(count($CartList[$proid])==0){
                        unset($CartList[$proid]);
                    }
                    $man_reduce_jian =  get_hand_man_reduce_act($Users_ID, $CartList, $rsConfig);
                    if (!empty($man_reduce_act)) {
                        if (!empty($man_reduce_jian)) {
                            $man_reduce = $man_reduce_act['award'] - $man_reduce_jian['award'];
                        } else {
                            $man_reduce = $man_reduce_act['award'];
                        }
                    } else{
                        $man_reduce = 0;
                    }

                    //退款金额
                    if(!empty($CartList)){
                        $Refund_Price = $proInfo["pintuan_pricex"] * $proInfo["Qty"] - $man_reduce;
                    }else{
                        if ($order['Back_Amount'] > 0) {
                            $Refund_Price = $proInfo["pintuan_pricex"] * $proInfo["Qty"] - $man_reduce;
                        } else {
                            $Refund_Price = $order['Order_TotalAmount'];
                        }
                    }

                    $Refund_Type = $order['Order_Status'] == 2 ? '仅退款' : (($order['Order_Status'] == 3 || $order['Order_Status'] == 4) ? '退款退货' : '');
                    $backup = new backup($DB, $Users_ID);
                    $back_id = $backup->add_backup($order, $proid, $attr, $proInfo["Qty"], '拼团失败,系统统一退款','');
                }else{
					$back = DB::table('user_back_order')->where(['Order_ID' => $order['Order_ID']])->first(['Back_ID']);
					$back_id = $back['Back_ID'];
				}
                /* 生成退款单结束 */
				
                if ($order['Order_PaymentMethod'] == '微支付') {
                    // 微信支付退款
                    $weixinFlag = refund($order, $back_id, $Users_ID);
					if ($weixinFlag['status']) {
                        $pthandle = pt_handle($order);
                        if($pthandle){
							$capsule->table('user_order')->where('Order_ID', $order['Order_ID'])->update(['Is_Backup' => 1, 'Back_Amount' => $Refund_Price]);
                            sendWXMessage($Users_ID, $order['Order_ID'], '拼团失败已退款，订单号：' . $order['Order_ID'] . "，退款金额：" . $order['Order_TotalPrice'], $order['User_ID']);
                        }
                    }
                } else if ($order['Order_PaymentMethod'] == '余额支付' ){
                    $price = $order['Order_TotalPrice'];
                    $userid = $order['User_ID'];
                    // 执行余额支付退款
                    begin_trans();
					$rsUser = $capsule->table('user')->where("User_ID", $userid)->first();
					$flag_user = $capsule->table('user')->where("User_ID", $userid)->update(['User_Money' => $rsUser['User_Money'] + $price, 'User_Cost' => $rsUser['User_Cost'] - $price]);
                    
                    $flag_pt = pt_handle($order);
					//写入资金流水
					$Data = array(
						'Users_ID' => $order['Users_ID'],
						'User_ID' => $order['User_ID'],
						'Type' => 0,
						'Amount' => $price,
						'Total' => $rsUser['User_Money'] + $price,
						'Note' =>  "拼团失败退款 + &yen;" . $price . " (订单号:" . $order['Order_ID'] . ")",
						'CreateTime' => time() 		
					);
					$flag = $capsule->table('user_money_record')->insert($Data);
					if(!$flag || $flag_pt === false || $flag_user === false){
						back_trans();
					}else{
						$capsule->table('user_order')->where('Order_ID', $order['Order_ID'])->update(['Is_Backup' => 1, 'Back_Amount' => $Refund_Price]);
						commit_trans();
						sendWXMessage($Users_ID, $order['Order_ID'], '拼团失败已退款，订单号：' . $order['Order_ID'] . "，退款金额：" . $order['Order_TotalPrice'], $order['User_ID']);
					}
                    
                } else {
                    continue;
                }
            }
        }
    }
}

/**
 * 执行删除订单，删除拼团详情，删除分销记录以及删除佣金记录，写入退款日志
 * @param $OrderID 订单ID
 * @return 返回SQL执行结果, true或者false
 */

	function pt_handle($rsOrder)
	{
		global $capsule;
		$OrderID = $rsOrder['Order_ID'];
		$teamid = $rsOrder['teamid'];
		$user_id = $rsOrder['User_ID'];
		//更新产品库存
		$myProduct = new Myproduct($capsule);
		$flag_product = $myProduct->updateProductStock($OrderID, 'back');

		//更新订单
		$update_order = [
				'Order_Status' => 4
		];
		$flag_order = $capsule->table('user_order')->where('Order_ID', $OrderID)->update($update_order);
		//更新退单
		$update_back_order = [
				'Back_Status' => 4,
				'Back_UpdateTime' => time(),
				'Back_IsCheck' => 1
		];
		$flag_back_order = $capsule->table('user_back_order')->where('Order_ID', $OrderID)->update($update_back_order);

		return $flag_product && $flag_order!==false && $flag_back_order !==false;
	}

?>