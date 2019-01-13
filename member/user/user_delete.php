<?php
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$User_ID = $_GET['User_ID'];

if($User_ID){
    begin_trans();
    $where = "WHERE User_ID=".$userinfo['User_ID'];
    $DB->Del("user", $where);
    $DB->Del("action_num_record", $where);
    $DB->Del("agent_order", $where);
    $DB->Del("battle_act", $where);
    $DB->Del("battle_sn", $where);
    $DB->Del("cloud_products_detail", $where);
    $DB->Del("cloud_record", $where);
    $DB->Del("user", $where);
    $DB->Del("user", $where);
    $DB->Del("user", $where);
    
    back_trans();
    commit_trans();
}