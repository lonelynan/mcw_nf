<?php require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$total_visit = $DB->GetRs("statistics","count(SID) as num","where S_Module='shop'");//全部
$todayStart = strtotime(date('Y-m-d').'00:00:00');
$todayStop = time();
$today_visit = $DB->GetRs("statistics","count(SID) as num","where S_Module='shop' and S_CreateTime>=".$todayStart." and S_CreateTime<=".$todayStop);//今日

$yesterdayStart = strtotime(date('Y-m-d').'00:00:00')-86400;
$yesterdayStop = strtotime(date('Y-m-d').'23:59:59')-86400;
$yester_visit = $DB->GetRs("statistics","count(SID) as num","where S_Module='shop' and S_CreateTime>=".$yesterdayStart." and S_CreateTime<=".$yesterdayStop);//今日

if(isset($_GET["search"]) && $_GET["search"]==1){
    $searchStarttime = strtotime($_GET['starttime']."00:00:00");
    $searchStoptime = strtotime($_GET['stoptime']."23:59:59")+1;
    $days = ($searchStoptime-$searchStarttime)/86400;
    $searchEnddate = $_GET['stoptime'];
}else{
    $week = date("w");  //默认本周的数据
    if($week == 0){
        $searchStarttime = strtotime(date("Y-m-d")." 00:00:00")-6*86400;
        $searchStoptime = strtotime(date("Y-m-d")." 23:59:59");
    }else{
        $searchStarttime = strtotime(date("Y-m-d")." 00:00:00")-($week-1)*86400;
        $searchStoptime = strtotime(date("Y-m-d")." 23:59:59");
    }
    $searchStartdate = date('Y-m-d',$searchStarttime);
    $searchEnddate = date('Y-m-d',$searchStoptime);
    $days = ($searchStoptime+1-$searchStarttime)/86400;
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
$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and S_CreateTime>=".$searchStarttime." and S_CreateTime<=".$searchStoptime;
$reg_result = $DB->Get("statistics","SID,S_CreateTime", $condition);
while($reg_array= $DB->fetch_assoc($reg_result)){
    $time = strtotime(date("Y-m-d",$reg_array["S_CreateTime"])." 00:00:00");
    $list_scratch[$time][] = $reg_array;
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
    foreach($list_scratch as $k =>$v){//访问量不用导出详细访问时间，只导出用每天次数
        $list_scratch_news[$k]=count($v);
    }
}else{
    $reg_array_new=array();
    $dates = array();
    $reg_array_new1 = "当前没有数据显示！";
}
    $Data1[] = array(
        "name" => "微商城访问量",
        "data" => $reg_array_new
    );
     
 
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
            <li class=""><a href="shop.php">订单统计</a></li>
            <li class="cur"><a href="visit.php">商城访问统计</a></li>
            <li class=""><a href="sales_money.php">销售额</a></li>
            <li class=""><a href="distributor.php">分销商</a></li>
            <li class=""><a href="commission_money.php">佣金</a></li>
<!--            <li class=""><a href="">代理奖金</a></li>-->
        </ul>
	</div>
    <div class="w">
    <div class="t">
    基本统计
    </div>
    <div class="bg">
        <ul>
            <li class="l"><div class="t1">总访问量（次）</div>
                <div class="t2 l"><?=$total_visit['num']?></div>
                <div class="p l"></div>
                <div class="clear"></div>
            </li>
        <li class="l"><div class="t1">今次访问量（次）</div>
            <div class="t2 l"><?=$today_visit['num']?></div>
            <div class="p l"></div>
            <div class="clear"></div>
        </li>
        <li class="l"><div class="t1">昨日访问量（次）</div>
            <div class="t2 l"><?=$yester_visit['num']?></div>
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
                <input placeholder="开始时间" class="laydate-icon" name="starttime" value="<?php echo ($_GET)?$_GET['starttime']:$searchStartdate?>" onclick="laydate()">-
                <input placeholder="截止时间" class="laydate-icon" name="stoptime" value="<?php echo ($_GET)?$_GET['stoptime']:"$searchEnddate"?>" onclick="laydate()">
                </div>
            </div>
        </div>
        <div class="form-group xz 1">
           <input type="hidden" value="1" name="search" />&nbsp;&nbsp;<input type="hidden" value="visit" name="searchtype" />
           <button type="submit" class="btn btn-primary" id="input-record-search" >搜索</button>
        </div> 
		<textarea style="display:none" name="param"><?php echo !empty($list_scratch_news)?json_encode($list_scratch_news):''?></textarea>
        <div class="clear"></div>
        <div class="baogao output_btn" style="cursor:pointer">生成报告</div>
		</form>
        <div class="t">商城访问统计</div>
    </div>
</div>  
    <script type='text/javascript' src='/static/js/plugin/highcharts/highcharts.js'></script>
    <script type='text/javascript' src='/static/member/js/statistics.js' ></script>
    <script src="/static/js/plugin/laydate/laydate.js"></script>
    <link href='/static/member/css/statistics.css' rel='stylesheet' type='text/css' />
    <script language="javascript">
    var chart_data=<?php echo json_encode($Data,JSON_UNESCAPED_UNICODE);?>;
    
    $(document).ready(statistics_obj.stat_init);
    </script>
    <div class="r_con_wrap">
    	<div class="chart_btn"><a href="javascript:void(0);" class="tab_bar">切换<span>曲线图</span></a></div>
        <center><?=!empty($reg_array_new1)?$reg_array_new1:''?></center><div class="chart">
        <div class="chart"></div>
    </div>
  </div>
</div>
<script>
    $("#search_form .output_btn").click(function(){
		 window.location='../shop/output.php?'+$('#search_form').serialize()+'&type=user_staticstic_list';      
		});
</script>
</body>
</html>