<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$no_pays = $DB->GetRs("user_order","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."' and Order_Status<2");
$pays = $DB->GetRs("user_order","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."' and Order_Status=2");
$fanishs = $DB->GetRs("user_order","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."' and Order_Status=4");
$backs = $DB->GetRs("user_back_order","count(*) as num","where Users_ID='".$_SESSION["Users_ID"]."'");
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
    $SearchType = 'all';
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
if($SearchType == 'all'){
    $condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Order_CreateTime>=".$searchStarttime." and Order_CreateTime<=".$searchStoptime;

}elseif($SearchType == 'fanish'){
    $condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Order_CreateTime>=".$searchStarttime." and Order_CreateTime<=".$searchStoptime." and Order_Status=4";
}
$reg_result = $DB->Get("user_order","User_ID,Order_ID,Order_CreateTime", $condition);
while($reg_array= $DB->fetch_assoc($reg_result)){
    $time = strtotime(date("Y-m-d",$reg_array["Order_CreateTime"])." 00:00:00");
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
}else{
    $reg_array_new=array();
    $reg_array_new1 = "当前没有数据显示！";
}
if($SearchType == 'all'){
    $Data1[] = array(
    "name" => "全部订单",
    "data" => $reg_array_new
    );
}elseif($SearchType == 'fanish'){
    $Data1[] = array(
    "name" => "已完成",
    "data" => $reg_array_new
    );
}
$Data = array(
    "count" => $Data1,
    "date" => $dates
);     
 
 
$totalNums = $DB->GetRs('user_order',"count(Order_ID) as num",'where Users_ID='."'".$_SESSION['Users_ID']."'");
$noNums = $DB->GetRs('user_order',"count(Order_ID) as num",'where Users_ID='."'".$_SESSION['Users_ID']."'".' and Order_Status=0 or Users_ID='."'".$_SESSION['Users_ID']."'".'and Order_Status=1');
$payNums = $DB->GetRs('user_order',"count(Order_ID) as num",'where Users_ID='."'".$_SESSION['Users_ID']."'".' and Order_Status=2');
$finshNums = $DB->GetRs('user_order',"count(Order_ID) as num",'where Users_ID='."'".$_SESSION['Users_ID']."'".' and Order_Status=4');
//获取所有分销商列表
$ds_list = Dis_Account::with('User')
        ->where(array('Users_ID' => $_SESSION["Users_ID"]))
        ->get(array('Users_ID', 'User_ID', 'invite_id', 'User_Name', 'Account_ID', 'Shop_Name','Account_CreateTime'))
        ->toArray();
$ds_list_dropdown = array();
foreach($ds_list as $key=>$item){
	if(!empty($item['user'])){
		$ds_list_dropdown[$item['User_ID']] = $item['user']['User_NickName'];
	}
}
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
            <li class="cur"><a href="shop.php">订单统计</a></li>
            <li class=""><a href="visit.php">商城访问统计</a></li>
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
            <li class="l"><div class="t1">未付款（个）</div>
                <div class="t2 l"><?=$no_pays['num']?></div>
                <div class="p l"></div>
                <div class="clear"></div>
            </li>
        <li class="l"><div class="t1">已付款（个）</div>
            <div class="t2 l"><?=$pays['num']?></div>
            <div class="p l"></div>
            <div class="clear"></div>
        </li>
        <li class="l"><div class="t1">已完成（个）</div>
            <div class="t2 l"><?=$fanishs['num']?></div>
            <div class="p l"></div>
            <div class="clear"></div>
        </li>
        <li class="l"><div class="t1">退货（个））</div>
            <div class="t2 l"><?=$backs['num']?></div>
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
            <input type="radio" name="SearchType" value="all" <?php if(!empty($_GET['SearchType']) && $_GET['SearchType']=='all')echo "checked";?> checked>全部<input type="radio" name="SearchType" value="fanish" <?php if(!empty($_GET['SearchType']) && $_GET['SearchType']=='fanish')echo "checked";?>>已完成
        </div>
        <div class="form-group xz 1">
           <input type="hidden" value="1" name="search" /><input type="hidden" value="shop" name="searchtype" />&nbsp;&nbsp;
           <button type="submit" class="btn btn-primary" id="input-record-search" >搜索</button>
        </div>  
        <textarea style="display:none" name="param"><?php echo !empty($list_scratch)?json_encode($list_scratch):''?></textarea>
        <div class="clear"></div>
        <div class="baogao output_btn" style="cursor:pointer">生成报告</div>
		</form>
        <div class="t">订单统计</div>
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
        <center><?=!empty($reg_array_new1)?$reg_array_new1:''?></center><div class="chart"></div>
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