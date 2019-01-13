<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/update/common.php');
use Illuminate\Database\Capsule\Manager as DB;

setcookie('url_referer', $_SERVER["REQUEST_URI"], time() + 3600, '/', $_SERVER['HTTP_HOST']);

//url_teamstatus 0全部，1拼团中，2拼团成功，3拼团失败
//db_teamstatus 0拼团中，1拼团成功，2已中奖，3未中奖，4拼团失败, 5已退款
$teamstatus = I("teamstatus");

$sql = "select * from pintuan_teamdetail td left join pintuan_team t on td.teamid=t.id left join pintuan_products p on t.productid=p.products_id left join user_order o on o.order_id=td.order_id where td.userid='$UserID'";
if (!empty($teamstatus)) {
    if ($teamstatus == '1') {
        $sql .= ' and t.teamstatus = 0';
    } elseif ($teamstatus == '2') {
        $sql .= ' and t.teamstatus in (1,2,3)';
    } elseif ($teamstatus == '3') {
        $sql .= ' and t.teamstatus in (4,5)';
    } else {

    }
}
$sql .= " order by t.addtime desc";

$list = DB::select($sql);
foreach ($list as $itemKey => $itemValue) {
    $lsCartList = json_decode(htmlspecialchars_decode($itemValue["Order_CartList"]), true);
    if ($itemValue['Is_Backup'] == 1) {
        $rsBack = $DB->GetRs('user_back_order', '*', 'where Order_ID = ' . $itemValue['Order_ID']);
        if (!empty($rsBack)) {
            $backjson = json_decode(htmlspecialchars_decode($rsBack["Back_Json"]), true);
            if ($rsBack['ProductID'] > 0) {
                if (empty($lsCartList[$rsBack["ProductID"]])) {
                    $lsCartList[$rsBack["ProductID"]][$rsBack["Back_CartID"]] = $backjson;
                } else {
                    if (empty($lsCartList[$rsBack["ProductID"]][$rsBack["Back_CartID"]])) {
                        $lsCartList[$rsBack["ProductID"]][$rsBack["Back_CartID"]] = $backjson;
                    } else {
                        $lsCartList[$rsBack["ProductID"]][$rsBack["Back_CartID"]]["Qty"] = $lsCartList[$rsBack["ProductID"]][$rsBack["Back_CartID"]]["Qty"] + $backjson["Qty"];
                    }
                }
            } else {
                foreach ($backjson as $key => $value) {
                    foreach ($value as $k => $backs) {
                        if (empty($lsCartList[$key])) {
                            $lsCartList[$key][$k] = $backs;
                        } else {
                            if (empty($lsCartList[$key][$k])) {
                                $lsCartList[$key][$k] = $backs;
                            } else {
                                $lsCartList[$key][$k]["Qty"] = $lsCartList[$key][$k]["Qty"] + $backs["Qty"];
                            }
                        }
                    }
                }
            }
        }
    }
    $list[$itemKey]['Order_CartList'] = json_encode($lsCartList, JSON_UNESCAPED_UNICODE);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>我的团</title>
    <link href="/static/api/pintuan/css/biaoqian.css" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/zhezhao.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/scrolltopcontrol.js"></script>
    <style>
        #tags LI {
            width: 25%;
        }
    </style>
</head>
<body>
<div class="dingdan">
    <span class="fanhui l"><a href="/api/<?= $UsersID ?>/pintuan/user/"><img src="/static/api/pintuan/images/fanhui.png" width="17px" height="17px"></a></span>
    <span class="querendd l">我的团</span>
    <div class="clear"></div>
</div>
<div id="con">
    <ul id="tags">
        <li <?= $teamstatus == '0' ? 'class=selectTag' : '' ?>><a href="<?= "/api/$UsersID/pintuan/teamlist/0/"; ?>">全部</a></li>
        <li <?= $teamstatus == '1' ? 'class=selectTag' : '' ?>><a href="<?= "/api/$UsersID/pintuan/teamlist/1/"; ?>">拼团中</a></li>
        <li <?= $teamstatus == '2' ? 'class=selectTag' : '' ?>><a href="<?= "/api/$UsersID/pintuan/teamlist/2/"; ?>">拼团成功</a></li>
        <li <?= $teamstatus == '3' ? 'class=selectTag' : '' ?>><a href="<?= "/api/$UsersID/pintuan/teamlist/3/"; ?>">拼团失败</a></li>
    </ul>
    <div id="tagContent">
        <div id="tagContent0" class="tagContent selectTag">
            <?php if (!empty($list)) {
                foreach ($list as $item) {
                    $CartList = json_decode(htmlspecialchars_decode($item["Order_CartList"]), true);
                    if (empty($CartList)) continue;
                    $shopping_fee = json_decode($item['Order_Shipping'], true);
                    foreach ($CartList as $ProductsID => $value) {
                        foreach ($value as $k => $v) {
                            ?>
                            <div class="chanpin">
                                <div class="chanpin1">
                                    <span class="l"><img style="width:100px;height: 100px;" src="<?= $v['ImgPath'] ?>"></span>
                                    <span class="cp l" style="width:50%;">
                                    <ul>
                                        <li><strong><?= $v['ProductsName'] ?></strong></li>
                                        <li><?= $v['Productsattrstrval'] ?></li>
                                    </ul>
                                </span>
                                    <span class="jiage r"><?= !empty($v['Property']) ? $v['Property']['shu_pricesimp'] : $v['pintuan_pricex'] ?>/件</span>
                                    <div class="clear"></div>
                                </div>
                                <div class="t r">
                                    <span>合计：<?= $item["Order_TotalPrice"] ?><?= empty($cartlist['IsShippingFree']) ? 0 : '(含物流费：¥' . floatval($cartlist['Products_Shipping']) . ')' ?></span>
                                    <div class="clear"></div>
                                </div>
                                <div class="futime2">
                                    <?php switch ($item['teamstatus']) {
                                        case 0:
                                            echo '<span class="fuk r"><a>拼团中</a></span>';
                                            break;
                                        case 1:
                                            echo '<span class="fuk r"><a>拼团成功</a></span>';
                                            break;
                                        case 2:
                                            echo '<span class="fuk r"><a>已中奖</a></span>';
                                            break;
                                        case 3:
                                            echo '<span class="fuk r"><a>未中奖</a></span>';
                                            break;
                                        case 4:
                                            echo '<span class="fuk r"><a>拼团失败</a></span>';
                                            break;
                                        case 5:
                                            echo '<span class="fuk r"><a>已退款</a></span>';
                                            break;
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php }
                    }
                }
            } ?>
        </div>
    </div>
</div>
<?php include 'bottom.php'; ?>
</body>
</html>
