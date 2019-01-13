<?php 
if(empty($_SESSION["Users_Account"]))
{
    header("location:/member/login.php");
}
$account_record = new Dis_Account_Record();
$todayStart = strtotime(date('Y-m-d').'00:00:00');
$todayStop = time(); 
$today_output_pay = $account_record->recordMoneySum2($_SESSION["Users_ID"],$todayStart,$todayStop,1);//今日已付款佣金
$today_output_cash = $account_record->recordMoneySum3($_SESSION["Users_ID"],$todayStart,$todayStop,1);//今日提现佣金

$yesterdayStart = strtotime(date('Y-m-d').'00:00:00')-86400;
$yesterdayStop = strtotime(date('Y-m-d').'23:59:59')-86400;
$yesterday_output_pay = $account_record->recordMoneySum2($_SESSION["Users_ID"],$yesterdayStart,$yesterdayStop,1);//昨日已付款佣金
$yesterday_output_cash = $account_record->recordMoneySum3($_SESSION["Users_ID"],$yesterdayStart,$yesterdayStop,1);//昨日提现佣金  

$all_output_pay = $account_record->recordMoneySum4($_SESSION["Users_ID"],0,$todayStop,0,1); //全部付款佣金
$all_output_cash = $account_record->recordMoneySum4($_SESSION["Users_ID"],0,$todayStop,1,1);//全部提现佣金
 
$back_output_pay = $account_record->recordMoneySum5($_SESSION["Users_ID"],0,$todayStop,0,0);//退款佣金



if(isset($_GET["search"]) && $_GET["search"]==1){
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
//$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Record_CreateTime>=".$searchStarttime." and Record_CreateTime<=".$searchStoptime." and Record_Type = 0 and Record_Status >=1 and Record_Money>0";
//付款佣金
$reg_result1 = $DB->Get("distribute_account_record","Record_Money,Record_CreateTime", "where Users_ID='".$_SESSION["Users_ID"]."' and Record_CreateTime>=".$searchStarttime." and Record_CreateTime<=".$searchStoptime." and Record_Type = 0 and Record_Status >=1 and Record_Money>0");
//退款佣金
$reg_result2 = $DB->Get("distribute_account_record","Record_Money,Record_CreateTime", "where Users_ID='".$_SESSION["Users_ID"]."' and Record_CreateTime>=".$searchStarttime." and Record_CreateTime<=".$searchStoptime." and Record_Type = 0 and Record_Status >=1 and Record_Money<0");
//提现佣金
$reg_result3 = $DB->Get("distribute_account_record","Record_Money,Record_CreateTime", "where Users_ID='".$_SESSION["Users_ID"]."' and Record_CreateTime>=".$searchStarttime." and Record_CreateTime<=".$searchStoptime." and Record_Type = 1 and Record_Status =1");

while($reg_array1= $DB->fetch_assoc($reg_result1)){
    $time1 = strtotime(date("Y-m-d",$reg_array1["Record_CreateTime"])." 00:00:00");
    $list_scratch1[$time1][] = $reg_array1;
}
if(!isset($list_scratch1)){
    $list_scratch1 = array();
    $reg_array_new1s = array();
}
foreach($fromtimes as $k=>$v){
    foreach($list_scratch1 as $k1=>$v1){
        if($v == $k1){
            foreach($v1 as $k2=>$v2){
                $Record_Money1[$k][]= $v2['Record_Money'];
            }
            //$reg_array_new[$k]=count($v1);
            $reg_array_new1[$k] = array_sum($Record_Money1[$k]);
        }
    }
    if(!in_array($v,array_keys($list_scratch1))){
        $reg_array_new1[$k]=0;
    }
}
$Data1[] = array(
    "name" => "已付款佣金",
    "data" => $reg_array_new1
);    

while($reg_array2= $DB->fetch_assoc($reg_result2)){
    $time2 = strtotime(date("Y-m-d",$reg_array2["Record_CreateTime"])." 00:00:00");
    $list_scratch2[$time2][] = $reg_array2;
}
if(!isset($list_scratch2)){
    $list_scratch2 = array();
    $reg_array_new2s = array();
} 
foreach($fromtimes as $k=>$v){
    foreach($list_scratch2 as $k1=>$v1){
        if($v == $k1){
            foreach($v1 as $k2=>$v2){
                $Record_Money2[$k][]= $v2['Record_Money'];
            }
            $reg_array_new2[$k] = abs(array_sum($Record_Money2[$k]));
        }
    }
    if(!in_array($v,array_keys($list_scratch2))){
        $reg_array_new2[$k]=0;
    }
}
$Data1[] = array(
    "name" => "退款佣金",
    "data" => $reg_array_new2
);

while($reg_array3= $DB->fetch_assoc($reg_result3)){
    $time3 = strtotime(date("Y-m-d",$reg_array3["Record_CreateTime"])." 00:00:00");
    $list_scratch3[$time3][] = $reg_array3;
}
if(!isset($list_scratch3)){
    $list_scratch3 = array();
    $reg_array_new3s = array();
}  
foreach($fromtimes as $k=>$v){
    foreach($list_scratch3 as $k1=>$v1){
        if($v == $k1){
            foreach($v1 as $k2=>$v2){
                $Record_Money3[$k][]= $v2['Record_Money'];
            }
            $reg_array_new3[$k] = abs(array_sum($Record_Money3[$k]));
        }
    }
    if(!in_array($v,array_keys($list_scratch3))){
        $reg_array_new3[$k]=0;
    }
}
$Data1[] = array(
    "name" => "提现佣金",
    "data" => $reg_array_new3
); 
if(isset($reg_array_new1s) && isset($reg_array_new2s) && isset($reg_array_new3s)){
    $reg_array_new1s = "当前没有数据显示！";
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
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
ul .t2 {
    margin-top: 1px;
}
</style>
<div id="iframe_page">
    <div class="iframe_content">
        <div class="r_nav">
            <ul>
                <li class=""><a href="shop.php">订单统计</a></li>
                <li class=""><a href="visit.php">商城访问统计</a></li>
                <li class=""><a href="sales_money.php">销售额</a></li>
                <li class=""><a href="distributor.php">分销商</a></li>
                <li class="cur"><a href="commission_money.php">佣金</a></li>
<!--                <li class=""><a href="">代理奖金</a></li>-->
            </ul>
        </div>
        <div class="w">
    <div class="t">
    基本统计
    </div>
    <div class="bg">
        <ul>
            
        <li class="l"><div class="t1">全部佣金</div>
                <div class="t2 l">已付款:&nbsp;&nbsp<?=$all_output_pay?></div>
                <div class="clear"></div>
                <div class="t2 l">已提现:&nbsp;&nbsp<?=$all_output_cash?></div>
            </li>
        <li class="l"><div class="t1">昨日佣金</div>
                <div class="t2 l">已付款:&nbsp;&nbsp<?=$today_output_pay?></div>
                <div class="clear"></div>
                <div class="t2 l">已提现:&nbsp;&nbsp<?=$today_output_cash?></div>
            </li>
        <li class="l"><div class="t1">今日佣金</div>
                <div class="t2 l">已付款:&nbsp;&nbsp<?=$today_output_pay ?></div>
                <div class="clear"></div>
                <div class="t2 l">已提现:&nbsp;&nbsp<?=$today_output_cash ?></div>
            </li>
            <li class="l"><div class="t1">退款佣金</div>
                <div class="t2 l">退款佣金:&nbsp;&nbsp<?=$back_output_pay ?></div>
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
        <div class="form-group xz 1">
           <input type="hidden" value="1" name="search" /><input type="hidden" value="commission" name="searchtype" />&nbsp;&nbsp;
           <button type="submit" class="btn btn-primary" id="input-record-search" >搜索</button>
        </div>    
        <div class="clear"></div>
        <textarea style="display:none" name="param"><?php echo !empty($Data)?json_encode($Data):''?></textarea>
        <div class="baogao output_btn" style="cursor:pointer">生成报告</div>
        </form>
        <div class="t">佣金统计</div>
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
            <center><?=!empty($reg_array_new1s)?$reg_array_new1s:''?></center><div class="chart">
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