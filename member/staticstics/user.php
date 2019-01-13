<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$totalUsers=$DB->GetRs("user","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."'"); //会员总数

$starttime = strtotime(date("Y-m-d")." 00:00:00");//今日新增
$endtime = time();
$newUser = $DB->GetRs("user","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."' and User_CreateTime>=".$starttime." and User_CreateTime<=".$endtime);

$yesterdayStart = strtotime(date('Y-m-d').'00:00:00')-86400;//昨日新增
$yesterdayStop = strtotime(date('Y-m-d').'23:59:59')-86400;
$yesterdayUser = $DB->GetRs("user","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."' and User_CreateTime>=".$starttime." and User_CreateTime<=".$endtime);

$week = date("w");//本周消费
if($week == 0){
    $weekStarttime = strtotime(date("Y-m-d")." 00:00:00")-6*86400;
    $weekEndtime = strtotime(date("Y-m-d")." 23:59:59");
}else{
    $weekStarttime = strtotime(date("Y-m-d")." 00:00:00")-($week-1)*86400;
    $weekEndtime = strtotime(date("Y-m-d")." 23:59:59");
}
$weeknewUser = $DB->Get("User_order","User_ID","where Users_ID='".$_SESSION["Users_ID"]."' and Order_CreateTime>=".$weekStarttime." and Order_CreateTime<=".$weekEndtime);
while($weekRes = $DB->fetch_assoc($weeknewUser)){
    $User_IDs[] = $weekRes['User_ID'];
}
if(isset($User_IDs)){
    $weekPur = count(array_unique($User_IDs));
}else{
    $weekPur = 0;
}

$day = date("d");//本月未消费
$monthStarttime = strtotime(date("Y-m-d")." 00:00:00")-($day-1)*86400;
$monthEndtime = strtotime(date("Y-m-d")." 23:59:59");
$monthUser = $DB->Get("User_order","User_ID","where Users_ID='".$_SESSION["Users_ID"]."' and Order_CreateTime>=".$weekStarttime." and Order_CreateTime<=".$weekEndtime);
while($monthRes = $DB->fetch_assoc($monthUser)){
    $Users[] = $monthRes['User_ID'];
}
if(isset($Users)){
    $no_monthPur = $totalUsers['num']-count(array_unique($Users)); 
}else{
    $no_monthPur = $totalUsers['num'];
}


if(isset($_GET["search"]) && $_GET["search"]==1){
    $SearchType = $_GET['SearchType'];
    $searchStarttime = strtotime($_GET['starttime']."00:00:00");
    $searchStoptime = strtotime($_GET['stoptime']."23:59:59")+1;
    $days = ($searchStoptime-$searchStarttime)/86400;
    $searchEnddate = $_GET['stoptime'];
    
}else{
    $day = date("d");   //默认当月的数据
    $searchStarttime = strtotime(date("Y-m-d")." 00:00:00")-($day-1)*86400;
    $searchStoptime = strtotime(date("Y-m-d")." 23:59:59");
    $searchStartdate = date('Y-m-d',$searchStarttime);
    $searchEnddate = date('Y-m-d',$searchStoptime);
    $days = ($searchStoptime+1-$searchStarttime)/86400;
    $SearchType = 'reg';
}
if($searchStoptime<=$searchStarttime){
    echo '<script language="javascript">alert("截止时间不能小于开始时间");history.back();</script>';
    exit;
}
for($i=$days;$i>0;$i--){ 
    $fromtime = strtotime($searchEnddate."00:00:00")-($i-1)*86400;
    $dates[] = date("m-d", $fromtime);
    $fromtimes[] =  $fromtime;
} 
if($SearchType == 'reg'){
    $condition = "where Users_ID='".$_SESSION["Users_ID"]."' and User_CreateTime>=".$searchStarttime." and User_CreateTime<=".$searchStoptime;
    $reg_result = $DB->Get("user","User_ID,User_No,User_Mobile,User_CreateTime,User_NickName", $condition);
    while($reg_array= $DB->fetch_assoc($reg_result)){
        $time = strtotime(date("Y-m-d",$reg_array["User_CreateTime"])." 00:00:00");
        $list_scratch[$time][] = $reg_array;
    }     

}elseif($SearchType == 'purchase'){
    $condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Order_CreateTime>=".$searchStarttime." and Order_CreateTime<=".$searchStoptime.' and Order_Status>1';
    $reg_result = $DB->Get("user_order","Order_ID,User_ID,Order_CreateTime", $condition);
    while($reg_array= $DB->fetch_assoc($reg_result)){
        $time = strtotime(date("Y-m-d",$reg_array["Order_CreateTime"])." 00:00:00");
        $list_scratch[$time][] = $reg_array;
    } 
}elseif($SearchType == 'nopurchase'){
   $condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Order_CreateTime>=".$searchStarttime." and Order_CreateTime<=".$searchStoptime.' and Order_Status>1';
   $reg_result = $DB->Get("user_order","User_ID", $condition);
    while($reg_array= $DB->fetch_assoc($reg_result)){
        $list_scratchs[] = $reg_array['User_ID'];
    }
    $user_result = $DB->Get("user","User_ID,User_No,User_Mobile,User_CreateTime,User_NickName", "where Users_ID='".$_SESSION["Users_ID"]."' and User_CreateTime<=".$searchStoptime);
    while($user_array= $DB->fetch_assoc($user_result)){
        $list_scratch_user[] = $user_array;  //总会员
    }
    if(!isset($list_scratch_user)){
        $list_scratch_user = array();
    }
    if(isset($list_scratchs)){
        $list_scratchs = array_unique($list_scratchs); //消费会员
        foreach($list_scratch_user as $k=>$v){
            if(!in_array($v['User_ID'],$list_scratchs)){
                $list_scratch[] = $v;
            }
        }
    }else{
        $list_scratch = $list_scratch_user;
    }
}
if(!isset($list_scratch)){
    $list_scratch = array();
     $reg_array_new1 = "当前没有数据显示！";
}  
if(isset($list_scratch)){
    foreach($fromtimes as $k=>$v){
        foreach($list_scratch as $k1=>$v1){
            if($v == $k1){
                $reg_array_new[$k]=count($v1);
            }
        }
        if(!in_array($v,array_keys($list_scratch))){
            $reg_array_new[$k]=0;
        }
    }
}else{
    $reg_array_new=array();
    $reg_array_new1 = "当前没有数据显示！";
}
if($SearchType == 'reg'){
    $Data1[] = array(
    "name" => "注册会员",
    "data" => $reg_array_new
    );
}elseif($SearchType == 'purchase'){
    $Data1[] = array(
    "name" => "消费会员",
    "data" => $reg_array_new
    );
}elseif($SearchType == 'nopurchase'){
    $Data1[] = array(
    "name" => "未消费会员",
    "data" => $reg_array_new
    );
}
$Data = array(
    "count" => $Data1,
    "date" => $dates
);
?>

<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script src="/static/js/plugin/laydate/laydate.js"></script>
</head>
<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <div class="r_nav">
        <ul>
            <li class="cur"><a href="user.php">会员统计</a></li>
            <li class=""><a href="user_area.php">会员来源地统计</a></li>
        </ul>
    </div>
    <script type='text/javascript' src='/static/js/plugin/highcharts/highcharts.js'></script>
    <script type='text/javascript' src='/static/member/js/statistics.js' ></script>
    <link href='/static/member/css/statistics.css' rel='stylesheet' type='text/css' />
<div class="w">
    <div class="t">
    基本统计
    </div>
    <div class="bg">
        <ul>
            <li class="l"><div class="t1">总会员数（个）</div>
                <div class="t2 l"><?=$totalUsers['num']?></div>
                <div class="p l"></div>
                <div class="clear"></div>
            </li>
        <li class="l"><div class="t1">昨日加入（个）</div>
            <div class="t2 l"><?=$yesterdayUser['num']?></div>
            <div class="p l"></div>
            <div class="clear"></div>
        </li>
        <li class="l"><div class="t1">今日加入（个）</div>
            <div class="t2 l"><?=$newUser['num']?></div>
            <div class="p l"></div>
            <div class="clear"></div>
        </li>
        <li class="l"><div class="t1">本周消费会员（个）</div>
            <div class="t2 l"><?=$weekPur?></div>
            <div class="p l"></div>
            <div class="clear"></div>
        </li>
        <li class="l"><div class="t1">本月未消费会员（个）</div>
            <div class="t2 l"><?=$no_monthPur?></div>
            <div class="p l"></div>
            <div class="clear"></div>
        </li>
        </ul>
    </div>
    <div class="clear"></div>
    <div class="ss">
        <div class="l">时间范围：</div>
        <form class="form-inline" action="?" method="get" id="search_form">
        <div class="l">
            <div class="form-group">
                <div class="input-group" id="reportrange" style="width:auto">
                <input placeholder="开始时间" class="laydate-icon" name="starttime" value="<?php echo ($_GET)?$_GET['starttime']:"$searchStartdate"?>" onclick="laydate()">-
                <input placeholder="截止时间" class="laydate-icon" name="stoptime" value="<?php echo ($_GET)?$_GET['stoptime']:"$searchEnddate"?>" onclick="laydate()">
                </div>
            </div>
        </div>
        <div class="xz l">
            <div class="l">搜索条件：</div>
            <input type="radio" name="SearchType" value="reg" <?php if(!empty($_GET['SearchType']) && $_GET['SearchType']=='reg')echo "checked";?> checked>注册<input type="radio" name="SearchType" value="purchase" <?php if(!empty($_GET['SearchType']) && $_GET['SearchType']=='purchase')echo "checked";?>>消费<input type="radio" name="SearchType" value="nopurchase" <?php if(!empty($_GET['SearchType']) && $_GET['SearchType']=='nopurchase')echo "checked";?>>未消费
        </div>
        <div class="form-group xz 1">
           <input type="hidden" value="1" name="search" /><input type="hidden" value="user" name="searchtype" />&nbsp;&nbsp;
           <button type="submit" class="btn btn-primary" id="input-record-search" >搜索</button>(未消费不生成统计图,搜索后直接导出)
        </div>    
        <textarea style="display:none" name="param"><?php echo !empty($list_scratch)?json_encode($list_scratch):''?></textarea>
        <div class="clear"></div>
        <div class="baogao output_btn" style="cursor:pointer">生成报告</div>
        <div class="t">会员统计</div><?php //echo json_encode($list_scratch,JSON_UNESCAPED_UNICODE);?>
        </form>
    </div>
</div>
    <script language="javascript">
        var chart_data=<?php echo json_encode($Data,JSON_UNESCAPED_UNICODE);?>;
        $(document).ready(statistics_obj.stat_init);
    </script>
    <div class="r_con_wrap">
    	<div class="chart_btn"><a href="javascript:void(0);" class="tab_bar">切换<span>曲线图</span></a></div>
        <center><?=!empty($reg_array_new1)?$reg_array_new1:''?></center><div class="chart">
        </div>  
    </div>
  </div>
</div>
</body>
<script>
    $("#search_form .output_btn").click(function(){
		 window.location='../shop/output.php?'+$('#search_form').serialize()+'&type=user_staticstic_list';      
		});
</script>
</html>