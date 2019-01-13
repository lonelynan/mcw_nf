<?php
basename($_SERVER['PHP_SELF'])=='global.php'&&header('Location:http://'.$_SERVER['HTTP_HOST']);
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
$rsShaod=$DB->GetRs("sha_order","*","where Users_ID='".$_SESSION["Users_ID"]."'");
$dis_config = Dis_Config::find($_SESSION['Users_ID']);
?>