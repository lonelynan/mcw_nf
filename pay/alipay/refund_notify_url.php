<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once(CMS_ROOT . "/pay/alipay/lib/alipay_notify.class.php");
require_once(CMS_ROOT . "/include/models/UsersPayConfig.php");
require_once(CMS_ROOT . '/include/models/UserBackOrder.php');
require_once(CMS_ROOT . '/include/models/UserBackOrderDetail.php');
require_once(CMS_ROOT . '/include/helper/backup.class.php');

if (!empty($_POST)) {
    $batch_no = $_POST['batch_no'];
    $success_num = $_POST['success_num'];
    begin_trans();
    $rsObjUserBackOrder = UserBackOrder::where("Batch_No", $batch_no)->first();
    if (!$rsObjUserBackOrder) {
        echo "Batch_No not found";
        exit;
    }
    $rsRefund = $rsObjUserBackOrder->toArray();
    $UsersID = $rsRefund['Users_ID'];
    $objUsersPayConfig = UsersPayConfig::where("Users_ID", $UsersID)->get(["Payment_AlipayPartner", "Payment_AlipayAccount", "Payment_AlipayKey", "Payment_AlipayRSAPrivateKey", "Payment_AlipayPublicKey"])->first();
    if (!$objUsersPayConfig) {
        echo "User config information not found";
        exit;
    }
    $rsPay = $objUsersPayConfig->toArray();
    require_once(CMS_ROOT . "/pay/alipay/alipay.config.php");
    $UsersID = $rsRefund['Users_ID'];
    $alipay_config = [];
    $alipay_config['partner'] = trim($rsPay["Payment_AlipayPartner"]);
    $alipay_config['seller_user_id'] = $alipay_config['partner'];
    $alipay_config['key'] = trim($rsPay["Payment_AlipayKey"]);
    $alipay_config['notify_url'] = "http://" . $_SERVER['HTTP_HOST'] . "/pay/alipay/refund_notify_url.php";
    $alipay_config['sign_type'] = 'MD5';
    $alipay_config['refund_date'] = date("Y-m-d H:i:s", $rsRefund['Back_UpdateTime']);
    $alipay_config['service'] = 'refund_fastpay_by_platform_pwd';
    $alipay_config['input_charset'] = 'utf-8';
    $alipay_config['cacert'] = getcwd() . '\\cacert.pem';
    $alipay_config['transport'] = 'http';
    $alipayNotify = new AlipayNotify($alipay_config);
    $verify_result = $alipayNotify->getSignVeryfy($_POST, $_POST['sign'], true);
    if ($verify_result) {
        $time = time();
        $Back_ID = $rsRefund['Back_ID'];
        $rsObjUserBackOrder->Back_Status = 4;
        $rsObjUserBackOrder->Buyer_IsRead = 0;
        $rsObjUserBackOrder->Back_IsCheck = 1;
        $rsObjUserBackOrder->Back_UpdateTime = time();
        $flag_UserBackOrder = $rsObjUserBackOrder->save();
        if (!$flag_UserBackOrder) {
            back_trans();
        }
        $objUserOrder = Order::where("Order_ID", $rsRefund['Order_ID'])->first();
        if (!$objUserOrder) {
            echo "Order is not found";
            exit;
        }
        $rsOrder = $objUserOrder->toArray();
        $Order_CartList = json_decode($rsOrder['Order_CartList'], true);
        if (empty($Order_CartList)) {
            Order::where("Order_ID", $rsOrder['Order_ID'])->update([
                'Order_Status' => 4
            ]);
        }

        //增加流程
        $userBackOrderDetail = new UserBackOrderDetail();
        $Data = array(
            "backid" => $rsRefund['Back_ID'],
            "detail" => '管理员已退款给买家',
            "status" => 4,
            "createtime" => time()
        );
        $flag_BackOrderDetail = $userBackOrderDetail->add($Data);
        if (!$flag_BackOrderDetail) {
            back_trans();
        }
        commit_trans();
        echo "success";
        exit;
    }
} else {
    echo "error";
    exit;
}