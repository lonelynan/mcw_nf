<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/update/common.php');
$action = isset($_GET['action']) ? $_GET['action'] : "";

if (IS_AJAX && $action) {
    $time = time();
    $sql = "SELECT * FROM active WHERE Users_ID='{$UsersID}' AND starttime<{$time} AND stoptime>{$time} AND Status=1 AND Active_ID NOT IN (SELECT Active_ID FROM biz_active WHERE Users_ID='{$UsersID}' AND Biz_ID={$BizID}) ORDER BY starttime ASC,Active_ID DESC";

    $result = $DB->query($sql);
    $list = $DB->toArray($result);
    $msglist = array();
    
    foreach ($list as $key => $value) {
        $msg = "";
        if ($value['stoptime'] < $time) {
            $msg = "已结束";
        } else {
            $msg = "已经开始";
        }
        $msglist[$key]['title'] = "{$typelist[$value['Type_ID']]['Type_Name']}活动——{$value['Active_Name']}{$msg} ";
        $msglist[$key]['addtime'] = date("Y-m-d H:i", $value['addtime']);
        $msglist[$key]['id'] = $value['Active_ID'];
    }
    if (!empty($msglist)) {
		$Data = array(
			'data' => $msglist,
            'status' => 1
		);        
    } else {
		$Data = array(
			 'data' => '',
            'status' => 0
		);        
    }
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
}