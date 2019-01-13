<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
use Illuminate\Database\Capsule\Manager as DB;

$goodsid = I("productid");
if(!$goodsid){
    sendAlert("相关的产品不存在","/api/{$UsersID}/pintuan/");
}
$goodsInfo = DB::table("pintuan_products")->where(["Users_ID" => $UsersID, "Products_ID" => $goodsid])->first(["Products_Parameter", "Products_JSON", "Products_Name", "Products_BriefDescription", "Products_PriceT","people_num"]);
if(empty($goodsInfo)){
    sendAlert("相关的产品不存在","/api/{$UsersID}/pintuan/");
}
$images = "/static/images/no_pic.png";
$json = json_decode(htmlspecialchars_decode($goodsInfo["Products_JSON"]), true);
foreach ($json['ImgPath'] as $v){
    if($v){
        $images = $v;
        break;
    }
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>参团</title>
    <link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
    <style>
        .querendd {
            line-height:30px;
        }
        .fanhui a img {
            padding-top:6px;
        }
    </style>
</head>
<body>
<div class="w">
    <div class="fanhui_bj">
        <span class="fanhui l"><a href="javascript:history.go(-1);"><img src="/static/api/pintuan/images/fanhui.png" width="17px" height="17px"></a></span>
        <span class="querendd l">拼团中</span>
        <div class="clear"></div>
    </div>
    <div class="chanpin1">
        <span class="l"><img style="width:100px;height: 100px;" src="<?=$images ?>"></span>
        <span class="cp l" style="width:50%;">
            <ul>
                <li><strong><?php echo $goodsInfo['Products_Name']; ?></strong></li>
                <li>数量：1</li>
                <li>
                  <?php echo $goodsInfo['Products_BriefDescription']; ?>
                </li>
            </ul>
        </span>
        <span class="jiage r"><?php echo $goodsInfo['Products_PriceT']; ?>/件</span>
        <div class="clear"></div>
    </div>
    <div class="pintuan">
        <?php
        $sql = "select t.productid,t.id,t.userid,t.teamnum,t.teamstatus,t.userid,u.User_NickName,u.User_Mobile,u.User_HeadImg,u.User_Area,
p.starttime,p.stoptime,p.people_num from pintuan_team t left join user u on t.userid=u.user_id LEFT JOIN pintuan_products AS p ON t.productid=p.Products_ID  where t.productid={$goodsid} order by t.addtime desc";
        $list = DB::select($sql);
        foreach ($list as $item) {
            ?>
            <div class="pint">
                <div class="bjt">
                    <div class="bjt-img"><img style="width:50px;height:50px;" src="<?php echo $item['User_HeadImg']; ?>"></div>
                </div>
                <div class="bjx">
                    <div class="bjxt">
                        <span class="l"><?php echo empty($item['User_NickName']) ? $item['User_Mobile'] : $item['User_NickName']; ?></span>
                        <?= ($num = $goodsInfo['people_num'] - $item['teamnum']) > 0 ? "<span class='bjxt1 r'>还差" .
                            $num . "人成团</span>" : "" ?>
                        <div class="clear"></div>
                        <span class="bjxt2  l"><?=$item['User_Area']?></span>
                        <span class="bjxt2 r"><?=($time=time())<$item['stoptime'] ? date('Y-m-d', $item['stoptime']).'结束'
                                : '已结束' ?>
					</span>
                    </div>
                </div>
                <div class="bjtp">
                    <?php
                    $detail = DB::table("pintuan_teamdetail")->where(["teamid" => $item['id'], 'userid' => $UserID])
                        ->first();
                    if ($time >= $item['starttime'] && $time <= $item['stoptime'] && $item['teamstatus'] == 0  &&
                        $item['teamnum'] < $item['people_num']) {
                        if($item['userid'] == $UserID || !empty($detail)){
                            echo '<a>已参团</a>';
                        }else{
                            echo '<a href="/api/'.$UsersID.'/pintuan/teamdetail/'.$item['id'].'/">去参团</a>';
                        }
                    } else {
                        if($item['teamstatus']==1){
                            echo "<a>已完成</a>";
                        }else{
                            echo '<a>已结束</a>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="clear"></div>
        <?php } ?>
    </div>
</div>
<?php include 'bottom.php'; ?>
</body>
</html>