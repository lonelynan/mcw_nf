<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once(CMS_ROOT . '/include/support/tool_helpers.php');
require_once(CMS_ROOT . '/include/helper/tools.php');

//分销模块url
$distribute_url = distribute_url();
$distribute_flag = false;//底部分销中心名称,true 分销中心,false 我要分销

//通过小程序或APP会请求此文件,通过api接口传过来一个UserID
if (isset($_POST['User_ID'])) {
	$User_ID = (int)$_POST['User_ID'];

	$res = little_program_key_check($_POST);
	if ($res['errorCode'] != 0) {
		exit(json_encode($res, JSON_UNESCAPED_UNICODE));
	}
} else {
	$User_ID = empty($_SESSION[$UsersID.'User_ID']) ? 0 : $_SESSION[$UsersID.'User_ID'];

    if(!empty($User_ID)){
        $rsUser=$DB->GetRs("user","*","where Users_ID='".$UsersID."' and User_ID=".$User_ID);
        if ($rsUser['Owner_Id'] > 0) {
            $rsUserup = $DB->GetRs("user","User_ID","where Users_ID='" . $UsersID . "' and User_ID=" . $rsUser["Owner_Id"]);
            if (!$rsUserup) {
                echo '上级会员不存在！联系管理员！';
                exit;
            }
            $rsDiscont = $DB->GetRs("distribute_account","Level_ID","where Users_ID='" . $UsersID . "' and User_ID=" . $rsUser["Owner_Id"]);
            if (!$rsDiscont) {
                echo '上级不存在！联系管理员！';
                exit;
            }
        }
    }
}

if (!isset($rsConfig)) {
    $UsersID = $_GET['UsersID'];
    //商城配置信息
    $rsConfig = shop_config($UsersID);
    //分销相关设置
    $dis_config = dis_config($UsersID);

    if (!$rsConfig || !$dis_config) {
        die('商城不存在!');
    }

    //合并参数
    $rsConfig = array_merge($rsConfig, $dis_config);
}

$rsUser = $DB->GetRs('user','*','where Users_ID="'.$UsersID.'" and User_ID='.$User_ID);

//获得分销级别
$dis_level = get_dis_level($DB,$UsersID);
//处理用户,不是分销商成为分销商,是分销商的更新分销等级
if(!$distribute_flag){
    $distribute_flag = deal_user_to_distribute($UsersID,$rsConfig,$rsUser,$dis_level);
    if (isset($_POST['User_ID'])) {
        if ($distribute_flag) {
            exit(json_encode(['errorCode' => 0, 'msg' => '请求成功!']));
        } else {
            exit(json_encode(['errorCode' => 2, 'msg' => '请求失败!']));
        }
    }
}
?>