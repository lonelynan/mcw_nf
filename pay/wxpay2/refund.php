<?php
ini_set('date.timezone','Asia/Shanghai');
require_once($_SERVER["DOCUMENT_ROOT"]."/pay/wxpay2/lib/WxPay.Api.php");
function refund($order,$backid,$Users_ID){
    global $DB;
    if($Users_ID){
        $userinfo = $DB->GetRs("users","Users_WechatAppId","where Users_ID='{$Users_ID}'");
        $payConfig = $DB->GetRs("users_payconfig","PaymentWxpayPartnerId,PaymentWxpayPartnerKey,PaymentWxpayCert,PaymentWxpayKey","where Users_ID='{$Users_ID}'");
    }else{
        return [
                    'status' => 0,
                    'msg' => '商家不存在'
                ];
    }
    $orderid = $order['Order_ID'];
    if($orderid){
        $orderInfo = $DB->GetRs("user_order","Order_ID,transaction_id,Order_Status,Order_CreateTime,Order_Yebc,Order_Fyepay,Order_TotalPrice,Order_Shipping","where Order_ID='{$orderid}'");
        //获得退货价格
        $backInfo = $DB->GetRs("user_back_order","Back_Amount,Back_Sn","where Back_ID='{$backid}'");
        if($orderInfo){
			$orderInfo['Order_TotalPrice'] = !empty(floatval($orderInfo["Order_Yebc"])) ? $orderInfo['Order_Fyepay'] : $orderInfo['Order_TotalPrice'];
			/************************判断是否是0元购 start *******************************/
			/*
			if($orderInfo['Order_Status']==3){
				$Order_Shipping = !empty($orderInfo['Order_Shipping']) ? json_decode($orderInfo['Order_Shipping'],true) : ["Express" =>"","Price" => 0];
				if($Order_Shipping['Price']>=$orderInfo['Order_TotalPrice']){
					return [
						'status' => 1,
						'msg' => ''
					];
				}
			}*/
			/************************判断是否是0元购 end *******************************/
			
			$totalAmount = 0;
			$orderno = $orderInfo["Order_CreateTime"].$orderInfo["Order_ID"];
			//查询是否已经退过款了
			$obj = new WxPayRefundQuery();
			$obj->SetAppid($userinfo['Users_WechatAppId']);
			$obj->SetMch_id($payConfig['PaymentWxpayPartnerId']);
			$obj->SetOut_trade_no($orderno);
			WxPayApi::setkey($payConfig['PaymentWxpayPartnerKey']);
			$result = WxPayApi::refundQuery($obj);
			$backAmount = 0;
			if($result['result_code'] == 'SUCCESS' && $result['return_code']=='SUCCESS'){
				$backAmount = $result['total_fee']-$result['refund_fee'];
				if($backAmount == 0){
					return [
						'status' => 1,
						'msg' => ''
					];
				}
				$totalAmount = $result['total_fee'];
			}else{
				$backAmount = $backInfo['Back_Amount'] * 100;
				$totalAmount = $orderInfo['Order_TotalPrice'] * 100;
				if($backAmount>$totalAmount){
					$backAmount = $totalAmount;
				}
			}
            $input = new WxPayRefund();
            if(isset($orderInfo['transaction_id']) && $orderInfo['transaction_id']){
                $input->SetTransaction_id($orderInfo['transaction_id']);
                $input->SetMch_id($payConfig['PaymentWxpayPartnerId']);
                $input->SetAppid($userinfo['Users_WechatAppId']);
                $input->SetOut_refund_no($backInfo['Back_Sn']);
                $input->SetTotal_fee($totalAmount);
                $input->SetRefund_fee($backAmount);
                $input->SetOp_user_id($payConfig['PaymentWxpayPartnerId']);
            }else{
                $input->SetAppid($userinfo['Users_WechatAppId']);
                $input->SetMch_id($payConfig['PaymentWxpayPartnerId']);
                $input->SetOut_trade_no($orderno);
                $input->SetOut_refund_no($backInfo['Back_Sn']);
                $input->SetTotal_fee($totalAmount);
                $input->SetRefund_fee($backAmount);
                $input->SetOp_user_id($payConfig['PaymentWxpayPartnerId']);
            }

            WxPayApi::setkey($payConfig['PaymentWxpayPartnerKey']);
            WxPayApi::setSSLCert($payConfig['PaymentWxpayCert']);
            WxPayApi::setSSLKey($payConfig['PaymentWxpayKey']);
            $result = WxPayApi::refund($input);
            if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                return [
                    'status' => 1,
                    'msg' => ''
                ];
            }else{
                return [
                    'status' => 0,
                    'msg' => !empty($result['err_code_des'])?$result['err_code_des']:$result['return_msg']
                ];
            }
        }
    }
    return [
                    'status' => 0,
                    'msg' => '订单不存在'
                ];
}