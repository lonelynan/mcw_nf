<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
use Illuminate\Database\Capsule\Manager as DB;
S($UsersID . "HTTP_REFERER", $_SERVER["REQUEST_URI"]);

$teamid = I('teamid',0);

$sql = "select * from pintuan_team t left join user u on t.userid=u.user_id left join pintuan_products p on p.products_id=t.productid where t.id={$teamid} order by t.addtime desc";
$teams = DB::select($sql);
if(empty($teams)){
    sendAlert("此团不存在！","/api/{$UsersID}/pintuan/");
}
$team = $teams[0];
$images = json_decode(htmlspecialchars_decode($team["Products_JSON"]), true)['ImgPath']['0'];
$title = empty($team['Products_Name']) ? '团明细' : "团购-{$team['Products_Name']}";
$img = "http://".$_SERVER['HTTP_HOST'].$images;
$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$desc = "";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo empty($team['Products_Name']) ? '团明细' : "团购-{$team['Products_Name']}"; ?></title>
    <link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script>
</head>
<body>
<div class="w">
    <div class="dingdan">
        <span class="fanhui l"><a href="<?php echo isset($_COOKIE['url_referer']) ? "javascript:location.href='{$_COOKIE['url_referer']}';" : 'javascript:history.go(-1)'; ?>"><img src="/static/api/pintuan/images/fanhui.png" width="17px" height="17px"></a></span>
        <span class="querendd l"><?php echo empty($team['Products_Name']) ? '团明细' : "团购-{$team['Products_Name']}"; ?></span>
        <div class="clear"></div>
    </div>
    <div class="chanpin1">
        <span class="l"><a href="<?php echo "/api/$UsersID/pintuan/xiangqing/{$team['productid']}/"; ?>"><img style="width:100px;height:100px;" src="<?php echo $images; ?>"></a></span>
        <span class="cp l" style="width:45%;">
            <ul>
                <li><strong><?php echo $team['Products_Name']; ?></strong></li>
                <li>数量：1</li>
                <li><?php echo $team['Products_BriefDescription']; ?></li>
            </ul>
        </span>
        <span class="jiage r"><?php echo $team['Products_PriceT']; ?>/件</span>
    </div>
    <div class="pintuan">
        <div class="pint">
            <div class="bjt">
                <div class="bjt-img"><img style="width:50px;height:50px;" src="<?php echo $team['User_HeadImg']; ?>"></div>
            </div>
            <div class="bjx">
                <div class="bjxt">
                    <span class="l"><?php echo empty($team['User_NickName']) ? $team['User_Mobile'] : $team['User_NickName']; ?></span>
                    <?php $num = $team['people_num'] - $team['teamnum'];
                    if ($num > 0) {
                        echo "<span class='bjxt1 r'>还差{$num}人成团</span>";
                    }
                    ?>
                    <div class="clear"></div>
                    <span class="bjxt2  l"><?php echo $team['User_Area'];?></span>
                    <span class="bjxt2 r">
                    <?php
                    $time = date('Y-m-d', time());
                    if ($time <= date('Y-m-d', $team['stoptime'])) {
                        echo date('Y-m-d', $team['stoptime']).'结束';
                    } else {
                        echo '已结束';
                    }
                    ?>
                    </span>
                </div>
            </div>
            <div class="bjtp">
                <?php if ($time >= date('Y-m-d', $team['starttime']) && $time <= date('Y-m-d', $team['stoptime']) && $team['teamstatus'] == 0 && $team['teamnum'] < $team['people_num']) {
                    echo "<a id='cantuan'>去参团</a>";
                } else {
                    echo '<a>已结束</a>';
                }
                ?>
            </div>
        </div>
        <div class="clear"></div>
        <div class="chengyuan">
            <ul>
                <?php
                $sql = "select * from pintuan_teamdetail td left join user u on td.userid=u.user_id where td.teamid = {$teamid} order by td.addtime";
                $teamdetails = DB::select($sql);
                if(!empty($teamdetails)) {
                    foreach ($teamdetails as $teamdetail) {
                        ?>
                        <li>
                            <img src="<?php echo $teamdetail['User_HeadImg']; ?>"><br><?php echo empty($teamdetail['User_NickName']) ? $teamdetail['User_Mobile'] : $teamdetail['User_NickName']; ?>
                        </li>

                    <?php }
                }?>
            </ul>
        </div>
    </div>
</div>

<div style="height:70px;"></div>
<div class="fl">
      <div class="fl1">
      <span class="m_p l"><a href="<?php echo "/api/$UsersID/pintuan/"; ?>">更多拼团</a></span>
      <span class="m_p1 l">
      <a>      
            <input type="button" value="分享" onclick="showdiv();" class="m_text">

      </a>
      </span>
      </div>
</div>
<div id="bj_1">
    <span class="l"> <input id="btnclose" type="button" onclick="hidediv();" value="关闭" class="gb_1"/></span>
    <span class="r">
        <img src="/static/api/pintuan/images/fx_1_02.png" width="150" height="118">
    </span>
    <div class="clear"></div>
    <p style="text-align: center">请点击右上角<br>【分享给好友】</p>
</div>
<script type="text/javascript">
    function showdiv() {
        $('#bj_1').show();
    }

    function hidediv() {
        $('#bj_1').hide();
    }

    $(function () {
        $('#cantuan').click(function(){
            var goodsid=<?=$team['productid']?>;
            var UsersID = "<?=$UsersID?>";
            var teamid = "<?=$team['id'] ?>";
            var goodsType = 1;      //  0  单购  1  团购

            $.post('/api/' + UsersID + '/pintuan/ajax/',{action:"addcart",goodsid:goodsid,UsersID:UsersID, goodsType:goodsType, teamid:teamid},function(data){
                if(data.status==0){
                    layer.msg(data.msg,{icon:1,time:1000},function(){
                        if(data.url){
                            window.location.href= data.url;
                        }else{
                            window.location.href="/api/" + UsersID + "/pintuan/";
                        }
                    });
                    return false;
                }else{
                    var isvirtual=data.isvirtual;
                    if (isvirtual==1){
                        location.href="/api/" + UsersID + "/pintuan/vorder/";
                    }else if(isvirtual==0){
                        location.href="/api/" + UsersID + "/pintuan/order/";
                    }
                }
            }, 'json');
        });
    });
</script>
</body>
</html>