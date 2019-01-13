<?php

$kfConfig=$DB->GetRs("kf_config","*","where Users_ID='".$UsersID."' and KF_IsShop=1 and KF_Code<>''");
if($kfConfig){
	echo htmlspecialchars_decode($kfConfig["KF_Code"],ENT_QUOTES);
}


?>

<script type='text/javascript' src='/static/tubiao/js/bootstrap.js'></script>
<link href="/static/tubiao/css/bootstrap.css" rel="stylesheet" type="text/css">

<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/api/shop/skin/distribute_footer.php');
?>
