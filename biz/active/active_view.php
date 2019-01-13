<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/update/common.php');
$ID = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : 0;
$sql = "SELECT a.*,b.* FROM biz_active AS b LEFT JOIN active AS a ON b.Active_ID=a.Active_ID  WHERE b.Users_ID='{$UsersID}' AND b.Biz_ID='{$BizID}' AND b.ID='{$ID}'";
$result = $DB->query($sql);
$rsActive = $DB->fetch_assoc($result);
if (! $rsActive) {
    sendAlert("已申请的活动不存在");
}
$list = [];
$goods = [];
if ($rsActive['ListConfig']) {
    $condition = "WHERE ";
    $allWhere = "";
     $rsActive['module'] = $typelist[$rsActive['Type_ID']]['module'];
    /*if ($rsActive['module'] == 'pintuan') { // 拼团
        $table = "pintuan_products";
        $time = time();
        $condition .= " `Users_ID` = '{$UsersID}' AND Biz_ID={$BizID} AND Products_ID IN ({$rsActive['ListConfig']})";
        $allWhere = "WHERE `Users_ID` = '{$UsersID}' AND Biz_ID={$BizID} ";
    } elseif ($rsActive['module'] == 'cloud') { // 云购
        $table = "cloud_products";
        $condition .= " `Users_ID` = '{$UsersID}' AND Biz_ID={$BizID} AND Products_SoldOut=0 AND Products_ID IN ({$rsActive['ListConfig']})";
        $allWhere = "WHERE `Users_ID` = '{$UsersID}' AND Biz_ID={$BizID} ";
    } elseif ($rsActive['module'] == 'zhongchou') { // 重筹
        $table = "zhongchou_project";
        $condition .= " usersid='{$UsersID}' AND Biz_ID={$BizID} AND itemid IN ({$rsActive['ListConfig']})";
        $allWhere = "WHERE `usersid` = '{$UsersID}' AND Biz_ID={$BizID} ";
    } else {
        $table = "pintuan_products";
        $time = time();
        $condition .= " `Users_ID` = '{$UsersID}' AND Biz_ID={$BizID}  AND Products_ID IN ({$rsActive['ListConfig']})";
        $allWhere = "WHERE `Users_ID` = '{$UsersID}' AND Biz_ID={$BizID} ";
    }*/
    $table = "shop_products";
    $time = time();
    $condition .= " `Users_ID` = '{$UsersID}' AND Biz_ID={$BizID}  AND Products_ID IN ({$rsActive['ListConfig']})";
    $allWhere = "WHERE `Users_ID` = '{$UsersID}' AND Biz_ID={$BizID} ";
    $result = $DB->Get($table, "*", $condition);
    if ($result) {
        while ($res = $DB->fetch_assoc($result)) {
            if ($rsActive['module'] == 'zhongchou') {
                $res['Products_ID'] = $res['itemid'];
                $res['Products_Name'] = $res['title'];
            }
            $list[$res['Products_ID']] = $res;
        }
    }
    $result = $DB->Get($table, "*", $allWhere);
    if ($result) {
        while ($res = $DB->fetch_assoc($result)) {
            if ($rsActive['module'] == 'zhongchou') {
                $res['Products_ID'] = $res['itemid'];
                $res['Products_Name'] = $res['title'];
            }
            $goods[$res['Products_ID']] = $res;
        }
    }
}

?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<title>查看产品列表</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet'
	type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
<style>
.r_con_form .rows .input {
	width: 65%;
}
</style>
</head>
<body>
	<div id="iframe_page">
		<div class="iframe_content">
			<div id="products" class="r_con_wrap ">
				<div class="r_con_form skipForm">
					<div class="rows">
						<label>推荐</label> <span class="input">
							<ul>
                      	<?php
                    if (! empty($list)) {
                        foreach ($list as $k => $v) {
                            ?>
                      	<li><?=$v['Products_Name'] ?> </li>
                      	<?php
                        
                        }
                    }
                    ?>
                      	</ul>

						</span>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>