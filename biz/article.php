<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(!empty($_GET['id'])){
	$id = $_GET['id'];
}else{
	echo '非法访问';
	exit();
}
$Article = $DB->GetRs("biz_article","*","where id=".$id);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $Article['atricle_title'];?></title>
</head>
<link href="/static/biz/css/log_x.css" type="text/css" rel="stylesheet">
<body>
<div class="w">
	<div class="w1">
    	<div class="main_x">
            <ul>
                <li>
                    <a href="#"><span class="l"><?php echo $Article['atricle_title'];?></span><span class="r"><?php echo date('Y-m-d',$Article['addtime']);?></span></a>
                </li>
            </ul>
            <br/>
            <?php echo htmlspecialchars_decode($Article['atricle_content']);?>
        </div>
    </div>
</div>
</body>
</html>
