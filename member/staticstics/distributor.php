<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/level3.php');
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
//$account = new Dis_Account();
$rsConfig = $DB->GetRs('distribute_config','Dis_Level,Dis_Mobile_Level','where Users_ID='."'".$_SESSION['Users_ID']."'");

$account = new Dis_Account();
$todayStart = strtotime(date('Y-m-d').'00:00:00');
$todayStop = time();
$today_new_account_num = $account->accountCount($_SESSION["Users_ID"],$todayStart,$todayStop);//今日加入分销商数量

$yesterdayStart = strtotime(date('Y-m-d').'00:00:00')-86400;
$yesterdayStop = strtotime(date('Y-m-d').'23:59:59')-86400;
$yesterday_new_account_num = $account->accountCount($_SESSION["Users_ID"],$yesterdayStart,$yesterdayStop);//昨天加入分销商数量

$all_account_num = $account->where('Users_ID', $_SESSION["Users_ID"])->count();//全部分销商数量

$activeStop = time();//活跃的分销商
$activeStart = time()-86400*20;
$active_account= $account->whereBetween('Account_CreateTime', [$activeStart, $activeStop])->where('Users_ID', $_SESSION["Users_ID"])->where('invite_id','!=',0)->lists('invite_id');
$active_account_num = count($active_account);

if(isset($_GET["search"]) && $_GET["search"]==1){
    $searchStarttime = strtotime($_GET['starttime']."00:00:00");
    $searchStoptime = strtotime($_GET['stoptime']."23:59:59")+1;
    $days = ($searchStoptime-$searchStarttime)/86400;
    $searchStopdata = $_GET['stoptime'];
}else{               //默认本周的数据   
    $week = date("w");  
    if($week == 0){
        $searchStarttime = strtotime(date("Y-m-d")." 00:00:00")-6*86400;
        $searchStoptime = strtotime(date("Y-m-d")." 23:59:59");
    }else{
        $searchStarttime = strtotime(date("Y-m-d")." 00:00:00")-($week-1)*86400;
        $searchStoptime = strtotime(date("Y-m-d")." 23:59:59");
    }
    $searchStartdata = date('Y-m-d',$searchStarttime);
    $searchStopdata = date('Y-m-d',$searchStoptime);
    $days = ($searchStoptime+1-$searchStarttime)/86400;
}
if($searchStoptime<=$searchStarttime){
    echo '<script language="javascript">alert("截止时间不能小于开始时间");history.back();</script>';
    exit;
}
for($i=$days;$i>0;$i--){
    $fromtime = strtotime($searchStopdata."00:00:00")-($i-1)*86400;
    $totime = $fromtime+86399;
    $date[] = date('m-d',$fromtime);
    $account_num[] = intval($account->accountCount($_SESSION["Users_ID"],$fromtime,$totime)); 
    $Account_ids[$i] = $account->where('Users_ID', $_SESSION["Users_ID"])->whereBetween('Account_CreateTime', [$fromtime, $totime])->lists('User_ID');//
    $Account_shopname[$i] = $account->where('Users_ID', $_SESSION["Users_ID"])->whereBetween('Account_CreateTime', [$fromtime, $totime])->lists('Shop_Name');//
   if(empty($Account_ids[$i])){
       $posterity_list[$i]=array();
    }else{
        foreach($Account_ids[$i] as $k =>$v){
            $User_ID=$v;//print_r($v);
            $Shop_Name=$Account_shopname[$i][$k];
            $Shop_Names[$i][$k]=$Shop_Name; //各个分销商的名字
            $accountObj =  Dis_Account::Multiwhere(array('Users_ID'=>$_SESSION['Users_ID'],'User_ID'=>$User_ID))->first();;
            $level_config = $rsConfig['Dis_Level']; 
            $posterity[$i][$k] = $accountObj->getPosterity($level_config);	 
            $posterity_count[$i][$k] = $posterity[$i][$k]->count();
            $posterity_list[$i][$k] = $accountObj->getLevelList($level_config);
            $posterity_list[$i][$k] = array_slice($posterity_list[$i][$k],0,$rsConfig['Dis_Mobile_Level'],TRUE); //各个分销商的下级
        }
   }
}
$Data1[] = array(
    "name" => "加入分销商",
    "data" => $account_num
);
$Data = array(
    "count" => $Data1,
    "date" => $date
); 
$level_name_list = $yinrulevel;

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
            <li class=""><a href="visit.php">商城访问统计</a></li>
            <li class=""><a href="sales_money.php">销售额</a></li>
            <li class="cur"><a href="distributor.php">分销商</a></li>
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
            <li class="l">
                <div class="t2 l">全部分销商:&nbsp;&nbsp<?=$all_account_num?></div>
                <div class="clear"></div>
                <div class="t2 l">活跃分销商:&nbsp;&nbsp<?=$active_account_num?></div>
            </li>
            <li class="l"><div class="t1">今日加入（个）</div>
                <div class="t2 l"><?=$today_new_account_num?></div>
                <div class="p l"></div>
                <div class="clear"></div>
            </li>
            <li class="l"><div class="t1">昨日加入（个）</div>
                <div class="t2 l"><?=$yesterday_new_account_num?></div>
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
                <input placeholder="开始时间" class="laydate-icon" name="starttime" value="<?php echo ($_GET)?$_GET['starttime']:"$searchStartdata"?>" onclick="laydate()">-
                <input placeholder="截止时间" class="laydate-icon" name="stoptime" value="<?php echo ($_GET)?$_GET['stoptime']:"$searchStopdata"?>" onclick="laydate()">
                </div>
            </div>
        </div>
        <div class="form-group xz 1">
           <input type="hidden" value="1" name="search" />&nbsp;&nbsp;
           <button type="submit" class="btn btn-primary" id="input-record-search" >搜索</button><input type="hidden" value="distributer" name="searchtype" />
        </div>    
        <textarea style="display:none" name="param"><?php echo !empty($Data)?json_encode($Data):''?></textarea>
        <div class="clear"></div>
        <div class="baogao output_btn" style="cursor:pointer">生成报告</div>
        </form>
        <div class="t">分销商统计</div>
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
            <div class="chart"></div>
    </div>
    

    <div>
    </div>
    <?php  rsort($date);krsort($account_num);$account_nums = array_values($account_num);?> 
    <?php foreach ($posterity_list as $k1=>$v1){?>
  
    <?php echo "<b>".$date[$k1-1]."</b>";  
    if(empty($v1)){
        echo "&nbsp;&nbsp;&nbsp;&nbsp"."该日无分销商加入"."<br>";
    }else{
        echo "&nbsp;&nbsp;&nbsp;&nbsp"."该日加入".$account_nums[$k1-1]."分销商";
    }
    ?>
        <?php foreach($v1 as $k2=>$v2){?>
            <div class="list_item" id="posterity_list">
                <div class="dline"></div>
                 <?php echo "&nbsp;&nbsp;&nbsp;&nbsp".$Shop_Names[$k1][$k2]?>的团队&nbsp;&nbsp;<span class="pink font17">(<?php echo $posterity_count[$k1][$k2]?>个)</span> 
                <div class="dline"></div>
                <?php foreach ($v2 as $key => $sub_list): ?>
                    <span class="ico"></span><?="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp".$level_name_list[$key]?>(<?=count($sub_list)?>个)
                        <?php if (count($sub_list) > 0): ?>
                        <ul class="list-group distribute-sub-list">
                            <?php foreach ($sub_list as $k => $v): ?>
                                        <li style="float:left">
                                        <?php if($k==0)echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";?>     
                                                <?php
                                                if (strlen($v['Shop_Name']) > 0) {
                                                        echo $v['Shop_Name'];
                                                } else {
                                                        echo '暂无';
                                                }
                                                ?>
                                &nbsp;&nbsp; 
                                        </li>
                            <?php endforeach;?>    
                        </ul>
                        <?php endif;?>
                 <br>
                <?php endforeach;?>
            </div>
        <?php echo"<br>";}?>
    <?php echo"<br><hr>";}?>
    
    
  </div>
</div>
<script>
    $("#search_form .output_btn").click(function(){
		 window.location='../shop/output.php?'+$('#search_form').serialize()+'&type=user_staticstic_list';      
		});
</script>
</body>
</html>