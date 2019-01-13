<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(!empty($_GET['cid'])){
	$cid = $_GET['cid'];
}else{
	echo '非法访问';
	exit();
}
 $cateInfo = $DB->getRs("biz_article_cate","*","where id=".$cid);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $cateInfo['category_name']?></title>
</head>
<link href="/static/biz/css/log_x.css" type="text/css" rel="stylesheet">
<link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
<body>
	<div class="w">
    	<div class="w1">
        	<div class="main_x">
            	<h1><i class="fa fa-volume-up" aria-hidden="true" style="color:#1ba0a3; margin-right:5px"></i><?php echo $cateInfo['category_name']?></h1>
                <ul>
                    <?php
                   $DB->get("biz_article","*","where category_id=".$cid);
                    while($res=$DB->fetch_assoc()){ ?>
                    <li>
                    	<a href="article.php?id=<?=$res['id']?>"><span class="l">【<?php echo $cateInfo['category_name']?>】<?php echo $res['atricle_title']?></span><span class="r"><?php echo date('Y-m-d',$res['addtime'])?></span></a>
                    </li>    
                    <?php }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
