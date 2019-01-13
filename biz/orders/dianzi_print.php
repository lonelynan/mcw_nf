<?php
date_default_timezone_set("PRC");

header('Content-Type:text/html;charset=utf-8');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');

$shipping_code_arr = [
    'SF' => '顺丰',
    'EMS' => 'EMS',
    'ZJS' => '宅急送',
    'YTO' => '圆通',
    'HTKY' => '百世快递',
    'ZTO' => '中通',
    'YD' => '韵达',
    'STO' => '申通',
    'DBL' => '德邦',
    'UC' => '优速',
    'JD' => '京东',
    'XFEX' => '信丰',
    'QFKD' => '全峰',
    'KYSY' => '跨越速运',
    'ANE' => '安能小包',
    'FAST' => '快捷快递',
    'GTO' => '国通',
    'HHTT' => '天天快递',
    'YZPY' => '邮政快递包裹',
    'ZTKY' => '中铁快运',
    'YZBK' => '邮政国内标快'
];

$Users_ID = $_SESSION['Users_ID'];

//组装寄件人信息开始====================================================================
$recieve_address = $DB->GetRs('biz', 'Biz_RecieveName,Biz_RecieveProvince,Biz_RecieveCity,Biz_RecieveArea,Biz_RecieveAddress,Biz_RecieveMobile', "where Users_ID = '". $Users_ID ."' and Biz_ID=".$_SESSION["BIZ_ID"]);

if (empty($recieve_address) || empty($recieve_address['Biz_RecieveProvince']) || empty($recieve_address['Biz_RecieveCity']) || empty($recieve_address['Biz_RecieveArea']) || empty($recieve_address['Biz_RecieveAddress']) || empty($recieve_address['Biz_RecieveMobile'])) {
    echo '<script language="javascript">alert("您尚未配置寄件地址！");window.location="../account/address_edit.php";</script>';exit;
}


$area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
$area_array = json_decode($area_json,TRUE);
$province_list = $area_array[0];

$recieve_address['Province'] = preg_match('/省/', $province_list[$recieve_address['Biz_RecieveProvince']]) || preg_match('/自治区/', $province_list[$recieve_address['Biz_RecieveProvince']]) ? $province_list[$recieve_address['Biz_RecieveProvince']] : $province_list[$recieve_address['Biz_RecieveProvince']] . '省';
$recieve_address['City'] = $area_array['0,'.$recieve_address['Biz_RecieveProvince']][$recieve_address['Biz_RecieveCity']];
$recieve_address['Area'] = $area_array['0,'.$recieve_address['Biz_RecieveProvince'].','.$recieve_address['Biz_RecieveCity']][$recieve_address['Biz_RecieveArea']];

//组装寄件人信息结束====================================================================

//查询订单信息开始=========================================================================
$orderids = preg_replace('/[^\d,]/', '', $_GET['OrderID']);
$order_arr = $DB->GetAssoc('user_order', 'Order_CreateTime, Address_Name, Address_Mobile, Address_Province, Address_City, Address_Area, Address_Detailed, Order_ID, Order_Shipping, Order_ShippingID, Order_Status', "where Order_ID in (" . $orderids . ")");
if (empty($order_arr)) {
    exit('未找到订单');
}
foreach ($order_arr as $k => $rsOrder) {
    if (empty(json_decode($rsOrder['Order_Shipping'], true)) || !in_array($rsOrder['Order_Status'], [2, 3])) {
        unset($order_arr[$k]);
        continue;
    }
    $Province = '';
    if(!empty($rsOrder['Address_Province'])){
        $order_arr[$k]['Address_Province'] = preg_match('/省/', $province_list[$rsOrder['Address_Province']]) || preg_match('/自治区/', $province_list[$rsOrder['Address_Province']]) ? $province_list[$rsOrder['Address_Province']] : $province_list[$rsOrder['Address_Province']] . '省';
    }
    $City = '';
    if(!empty($rsOrder['Address_City'])){
        $order_arr[$k]['Address_City'] = $area_array['0,'.$rsOrder['Address_Province']][$rsOrder['Address_City']];
    }

    $Area = '';
    if(!empty($rsOrder['Address_Area'])){
        $order_arr[$k]['Address_Area'] = $area_array['0,'.$rsOrder['Address_Province'].','.$rsOrder['Address_City']][$rsOrder['Address_Area']];
    }
    if (!empty(json_decode($rsOrder['Order_Shipping'], true))) {
        $shipping_arr = json_decode($rsOrder['Order_Shipping'], true);
        foreach ($shipping_code_arr as $key => $val) {
            if ($shipping_arr['Express'] == $val) {
                $order_arr[$k]['shipping_code'] = $key;
            }
        }
    }
}
//查询订单信息结束=========================================================================
//电商ID
$rs_express_info = $DB->GetRs("users_express_info","cusname,cuspasswd","where usersid = '". $Users_ID ."'");
$rs_kdniao = $DB->GetRs("users","knuserid,knapikey","where usersid = '". $Users_ID ."'");
	
defined('EBusinessID') or define('EBusinessID', $rs_kdniao['knuserid']);
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('AppKey') or define('AppKey', $rs_kdniao['knapikey']);
//请求url，正式环境地址：http://api.kdniao.cc/api/Eorderservice    测试环境地址：http://testapi.kdniao.cc:8081/api/EOrderService
defined('ReqURL') or define('ReqURL', 'http://api.kdniao.cc/api/Eorderservice');

//构造电子面单提交信息
$dianzi_html = [];
foreach ($order_arr as $order) {

    $eorder = [];
    $cus_express_info = $DB->GetRs('users_express_info', '*', "where usersid = '". $Users_ID ."' and shippingcode = '". $order['shipping_code'] ."'");

    if (!empty($cus_express_info) && !empty($cus_express_info['cusname']) && !empty($cus_express_info['cuspasswd'])) {
        $eorder["CustomerName"] = $cus_express_info['cusname'];
        if ($order['shipping_code'] == 'YTO') {
            $eorder["MonthCode"] = $cus_express_info['cuspasswd'];
        } else {
            $eorder["CustomerPwd"] = $cus_express_info['cuspasswd'];
        }
    }
    $eorder["ShipperCode"] = $order['shipping_code'];
    $eorder["OrderCode"] = $order['Order_CreateTime'] . $order['Order_ID'];
    $eorder["PayType"] = empty($cus_express_info['custype']) ? 1 : $cus_express_info['custype'];   //邮费支付方式:1-现付，2-到付，3-月结，4-第三方支付
    $eorder["ExpType"] = 1;   //快递类型：1-标准快件
    $eorder['IsReturnPrintTemplate'] = 1;  //此项为可选项,返回电子面单模板

    $sender = [];
    $sender["Name"] = $recieve_address['Biz_RecieveName'];
    $sender["Mobile"] = $recieve_address['Biz_RecieveMobile'];
    $sender["ProvinceName"] = $recieve_address['Province'];
    $sender["CityName"] = $recieve_address['City'];
    $sender["ExpAreaName"] = $recieve_address['Area'];
    $sender["Address"] = $recieve_address['Biz_RecieveAddress'];

    $receiver = [];
    $receiver["Name"] = $order['Address_Name'];
    $receiver["Mobile"] = $order['Address_Mobile'];
    $receiver["ProvinceName"] = $order['Address_Province'];
    $receiver["CityName"] = $order['Address_City'];
    $receiver["ExpAreaName"] = $order['Address_Area'];
    $receiver["Address"] = $order['Address_Detailed'];

    $commodityOne = [];
    $commodityOne["GoodsName"] = "其他";
    $commodity = [];
    $commodity[] = $commodityOne;

    $eorder["Sender"] = $sender;
    $eorder["Receiver"] = $receiver;
    $eorder["Commodity"] = $commodity;
//调用电子面单
    $jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);



//echo "电子面单接口提交内容：<br/>".$jsonParam;
    $jsonResult = submitEOrder($jsonParam);
//echo "<br/><br/>电子面单提交结果:<br/>".$jsonResult;

//解析电子面单返回结果
    $result = json_decode($jsonResult, true);
    //代表创建成功了电子面单
    if (!empty($result['Success'])) {
        if ($order['Order_Status'] == 2) {
            $flag = $DB->Set('user_order', ['Order_SendTime' => time(), 'Order_Status' => 3, 'Order_ShippingID' => $result['Order']['LogisticCode']], "where Order_ID = '". $order['Order_ID'] ."'");
        } elseif ($order['Order_Status'] == 3) {
            $flag = true;
        } else {
            $flag = false;
        }

        if ($flag) {
            $dianzi_html[$order['Order_ID']] = $result['PrintTemplate'];
        }
    } else {
        $error_arr[$order['Order_ID']] = $shipping_code_arr[$order['shipping_code']] . '_' . $result['Reason'];
    }
}
//-------------------------------------------------------------


/**
 * Json方式 调用电子面单接口
 */
function submitEOrder($requestData){
    $datas = array(
        'EBusinessID' => EBusinessID,
        'RequestType' => '1007',
        'RequestData' => urlencode($requestData) ,
        'DataType' => '2',
    );
    $datas['DataSign'] = encrypt($requestData, AppKey);
    $result=sendPost(ReqURL, $datas);

    //根据公司业务处理返回的信息......

    return $result;
}


/**
 *  post提交数据
 * @param  string $url 请求Url
 * @param  array $datas 提交的数据
 * @return url响应返回的html
 */
function sendPost($url, $datas) {
    $temps = array();
    foreach ($datas as $key => $value) {
        $temps[] = sprintf('%s=%s', $key, $value);
    }
    $post_data = implode('&', $temps);
    $url_info = parse_url($url);
    if(empty($url_info['port']))
    {
        $url_info['port']=80;
    }
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader.= "Host:" . $url_info['host'] . "\r\n";
    $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
    $httpheader.= "Connection:close\r\n\r\n";
    $httpheader.= $post_data;
    $fd = fsockopen($url_info['host'], $url_info['port']);
    fwrite($fd, $httpheader);
    $gets = "";
    while (!feof($fd)) {
        if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
            break;
        }
    }
    while (!feof($fd)) {
        $gets.= fread($fd, 128);
    }
    fclose($fd);

    return $gets;
}

/**
 * 电商Sign签名生成
 * @param data 内容
 * @param appkey Appkey
 * @return DataSign签名
 */
function encrypt($data, $appkey) {
    return urlencode(base64_encode(md5($data.$appkey)));
}
?>

<!doctype html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>打印电子面单</title>
	<link href='/static/member/css/shop.css?t=0609' rel='stylesheet' type='text/css' />
    <script type="text/javascript" src="/static/js/LodopFuncs.js"></script>
</head>
<body>
<div class="dayin">
	<a href="javascript:;" onclick="javascript:preview(<?=json_encode(array_keys($dianzi_html), JSON_UNESCAPED_UNICODE)?>);">打印预览</a>
	<a href="javascript:;" onclick="javascript:Print5(<?=json_encode(array_keys($dianzi_html), JSON_UNESCAPED_UNICODE)?>);">直接打印</a>
</div>
<div class="clear"></div>
<? if (!empty($error_arr) || !empty($dianzi_html)) { ?>
<table style="BORDER-COLLAPSE: collapse" borderColor=#a9a9a9 cellSpacing=0 width=90% align=center bgColor=#ffffff border=1 class="table_order">
	<tbody>
        <tr>
            <th width=20%><div align=center>订单号</div></th>
            <th width=14%><div align=center>电子面单状态</div></th>
            <th width=56%><div align=center>原因</div></th>
        </tr>
        <?
        if (!empty($error_arr)) {
        foreach ($error_arr as $err_key => $err_val) {
            ?>
            <tr>
                <td width=20%><div align=center><?=$err_key?></div></td>
                <td width=14% class="zhuangtai"><div align=center>未成功</div></td>
                <td width=56%><div><?=$err_val?></div></td>
            </tr>
        <? }} ?>
        <?
        if (!empty($dianzi_html)) {
        foreach ($dianzi_html as $succ_key => $succ_val) {
            ?>
            <tr>
                <td width=20%><div align=center><?=$succ_key?></div></td>
                <td width=14% class="zhuangtai" style="color: green"><div align=center>成功</div></td>
                <td width=56%><div>电子面单创建成功,可直接点击打印预览或打印按钮进行打印操作</div></td>
            </tr>
        <? }} ?>
	</tbody>
</table>
<? } ?>

<div class="clear"></div>
<?
    foreach ($dianzi_html as $k => $v) {
?>

<div id="order<?=$k?>" class="order_wl" style="display: none;"><?=$v?></div>
<? } ?>


<script type="text/javascript">
    if (document.readyState == 'complete') {
        var LODOP = getLodop();
    }

    function Print5(orderid) {
        LODOP=getLodop();
        for (var i = 0; i < orderid.length; i++) {
            LODOP.PRINT_INIT("");
            LODOP.SET_PRINT_PAGESIZE(1,1000,1800,"");
            LODOP.ADD_PRINT_HTM(0,0,"100%","100%",document.getElementById('order' + orderid[i]).innerHTML);
            LODOP.PRINT();
        }
    }


    function preview(orderid) {
        LODOP=getLodop();
        LODOP.PRINT_INIT("");
        LODOP.SET_PRINT_PAGESIZE(1,1000,1800,"");
        for (var i = 0; i < orderid.length; i++) {
            LODOP.NewPage();
            LODOP.ADD_PRINT_HTM(0,0,"100%","100%",document.getElementById('order' + orderid[i]).innerHTML);
        }
        LODOP.PREVIEW();
    }
</script>
</body>
</html>

