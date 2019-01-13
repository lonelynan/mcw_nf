
<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
use Illuminate\Database\Capsule\Manager as DB;

$name = I("name", "");
$cateid = intval(I('cateid'));
$time = time();
if($cateid){
    $rsProductCate = DB::table("shop_category")->where(["Category_ParentID" => $cateid])->first();
    if(!empty($rsProductCate)){
        $cateid = $rsProductCate['Category_ID'];
    }
}
$build = $capsule->table('shop_products')->where(["Users_ID" => $UsersID, "Products_Status" => 1,'pintuan_flag' => 1, 'flashsale_flag' => 0]);
if($cateid){
	$build->where(["Products_Category" => $cateid]);
	if(!empty($name)){
		$build->where('Products_Name', 'like', '%'.$name.'%');
	}
	
}
$goods = $build->orderBy('Products_ID', 'desc')->limit(30)->get(["Products_JSON","Products_Name","Products_ID","pintuan_pricex"]);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>搜索</title>
    <link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
    <script src="/static/api/pintuan/js/jquery.min.js"></script>
    <style>
        .tj li {height:150px;}
    </style>
</head>
<body>
<div>
    <form action="/api/<?=$UsersID ?>/pintuan/sousuo/0/" method="post">
        <span class="box l"><input type="text" class="text" name="name" value="" style="width:99%;"/></span>
        <span class="btnSubmit l"><input name="sousuo" type="submit" value="搜索"   class="btnSubmit_btn" /></span>
    </form>
</div>
<div class="clear"></div>
<div class="tj">
    <ul>
        <?php
        if(!empty($goods)){
            foreach ($goods as $key => $val){
                ?>
                <li class="li">
                    <a href="/api/<?=$UsersID ?>/pintuan/xiangqing/<?=$val['Products_ID'] ?>/"><img style="width:90px;height:90px;" src="<?=json_decode($val['Products_JSON'],true)['ImgPath']['0'] ?>"><br/><?=sub_str($val['Products_Name'],10,false)?></a><br/>
                    <span class="tjt l">¥<?=$val['pintuan_pricex'] ?></span>
                </li>
                <?php
            }
        }else{
            ?>
            <li>没有搜索到数据</li>
            <?php
        }
        ?>
    </ul>
</div>
<?php include 'bottom.php';?>
</div>
</body>
</html>