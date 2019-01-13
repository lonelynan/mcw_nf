<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

//判断是否登录
if (empty($_SESSION[$UsersID . 'User_ID'])) {
    header('location:/api/' . $UsersID . '/user/' . $owner['id'] . '/login/');
    exit;
}

$base_url = base_url();
?>