<?php 
require_once($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
require_once(CMS_ROOT . '/include/helper/tools.php');
require_once(CMS_ROOT . '/include/helper/url.php');
require_once(CMS_ROOT . '/include/library/smarty.php');

$q = $DB->query("ALTER TABLE `user_pre_order`
CHANGE COLUMN `sn` `pre_sn`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '';");
exit;





?>