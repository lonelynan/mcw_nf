<?php
require_once('global.php');
require_once(CMS_ROOT . "/api/shop/member/const.php");
/**
 * Json方式 查询订单物流轨迹
 */
function getOrderTracesByJson($shopperCode, $logisticCode){
    $requestData = [
        'ShipperCode' => $shopperCode,
        'LogisticCode' => $logisticCode
    ];
    $requestData = json_encode($requestData, JSON_UNESCAPED_UNICODE);

    $datas = array(
        'EBusinessID' => EBusinessID,
        'RequestType' => '1002',
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
    $headerFlag = true;
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
$Order_ID = isset($_REQUEST['Order_ID']) ? $_REQUEST['Order_ID'] : 0;

if(!$Order_ID){
    echo "<p style='text-align: center;'>订单不存在</p>";
    exit;
}
$objOrder = Order::where("Order_ID", $Order_ID)->first(["Order_Shipping","Order_ShippingID","Order_CartList","Order_Type"]);
if(! $objOrder){
    echo "<p style='text-align: center;'>订单不存在</p>";
    exit;
}
$rsOrder = $objOrder->toArray();
$Shippingno = $rsOrder['Order_ShippingID'];
$shipping = json_decode(htmlspecialchars_decode($rsOrder['Order_Shipping']),true) ;
if(!empty($shipping['Express'])){
    $ShippingName = !empty($shipping['Express'])?$shipping['Express']:'';
}else{
    $ShippingName = '';
}
$logisticResult = getOrderTracesByJson($shipping_code[$ShippingName]['code'], $Shippingno);
$logisticResult = json_decode($logisticResult, true);
$status = [
    '0' => '此单无物流信息',
    '2' => '在途中',
    '3' => '已签收',
    '4' => '问题件'
];
$phone = '';
if ($logisticResult['Success']) {
    if(!empty($logisticResult['Traces'])){
        foreach ($logisticResult['Traces'] as $k => $info) {
            preg_match('/1[0-9]{10}/', $info['AcceptStation'], $mobile);
            if(isset($mobile[0])){
                $phone = $mobile[0];
            }
        }
    }
}else{
    $logisticResult['State'] = 0;
}
$CartList = json_decode($rsOrder['Order_CartList'], true);
$imgpath = '';
if(!empty($CartList)){
    $CartList = array_map(function ($val){
        return each($val)['value']['ImgPath'];
    }, $CartList);
    $imgpath = getImageUrl(each($CartList)['value']);
}
?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>物流跟踪</title>
</head>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
<meta name="apple-touch-fullscreen" content="yes" />
<meta name="format-detection" content="telephone=yes">
<meta http-equiv="x-rim-auto-match" content="none">
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta name="format-detection" content="telephone=no" />
<link rel="stylesheet" href="/static/api/shop/member/css/wl.css">
<link rel="stylesheet" href="/static/api/shop/member/css/font-awesome.min.css">
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<body>
<header>
	<div class="back_wl" style="font-size: 16px;">
        <span class="left"><a href="javascript:history.go(-1);">
                <img style="width: 30px;position: absolute;left: -11px;top: 6px;" src="/static/api/shop/member/images/back_x.png">
            </a></span>物流跟踪
    </div>
    <div class="pro_bg_wl">
    		<span class="left img_wl">
    			<img src="<?= $imgpath ? $imgpath : "/static/images/none.png" ?>" width="90" height="90">
    		</span>
        <span class="left main_wl">
    			<p>物流状态：<span style="color: #1fb4f8;"><?=isset($status[$logisticResult['State']]) ? $status[$logisticResult['State']] : "未知"  ?></span></p>
    			<p>物流公司：<?= isset($shipping_code[$ShippingName]['name']) ? $shipping_code[$ShippingName]['name'] : "未知" ?></p>
    			<p>物流单号：<?= $Shippingno ?></p>
    			<p>派送员电话：<?=$phone ?: "暂无" ?> <a href="tel:<?=$phone ?>"><img src="/static/api/shop/member/images/phone.png" width="25" height="25" class="call"/></a>
				</p>
            </span>
    </div>
</header>
<div class="w">
    <div>
        <ul class="wl_list">
            <?php
            if ($logisticResult['Success'] && !empty($logisticResult['Traces'])) {
				$trace = $logisticResult['Traces'];
				krsort($trace);
                foreach ($trace as $k => $info) {
                    ?>
                    <li>
                        <div>
                            <p <?=$k == count($trace)-1 && $logisticResult['State'] == 3 ? "style=\"color:#1fb4f8\"" : "" ?>>
                                <?php
                                preg_match('/1[0-9]{10}/', $info['AcceptStation'], $mobile);
                                if(isset($mobile[0])){
                                    echo str_replace($mobile[0], "<a href='tel:" . $mobile[0] . "' style=\"color: #1fb4f8;\">" .
                                        $mobile[0] . "</a>",
                                        $info['AcceptStation']);
                                }else{
                                    echo $info['AcceptStation'];
                                }
                                ?>
                            </p>
                            <p><?=$info['AcceptTime'] ?></p>
                        </div>
                        <span class="icon-step-line icon-step-line-up"></span>
                        <span class="icon-step-line icon-step-line-down"></span>
                        <span class="<?=$k == 0 ? "icon-step-main-box1" : "icon-step-main-box" ?>"></span>
                    </li>
                    <?php
                }
            }else{
                ?>
                <li>
                    <div>
                        <p><?=$status[0]; ?></p>
                    </div>
                    <span class="icon-step-line icon-step-line-up"></span>
                    <span class="icon-step-line icon-step-line-down"></span>
                    <span class="icon-step-main-box"></span>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>
    <div class="clear"></div>
</div>
</body>
</html>
