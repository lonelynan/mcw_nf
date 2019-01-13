<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$totalUser = $DB->GetRs('user','count(*) as num',"where Users_ID='".$_SESSION["Users_ID"]."'");
$Data = array();
$arrayUser = $DB->Get("user","count(*) as num,User_Province","where Users_ID='".$_SESSION["Users_ID"]."' group by User_Province order by num desc");
$i = 1;
$totalprovice = '';
$totalPercent = '';
 
	while($rsUser=$DB->fetch_assoc($arrayUser)){
	 
		if($i>5){
			break;
		}
		$provice = array();
		$provice[] = empty($rsUser["User_Province"]) ? "未知" : $rsUser["User_Province"];
		$provice[] = intval($rsUser["num"]);
		$provice[] = round((intval($rsUser["num"])/$totalUser['num'])*100,2);
		$totalprovice+=intval($rsUser["num"]);
		$totalPercent+=round((intval($rsUser["num"])/$totalUser['num'])*100,2);
		$Data[] = $provice;
			$i++;
	}
if(isset($provice) && !empty($provice)){
	if(count($Data)>1){
		$Data[]=array('其他',$totalUser['num']-$totalprovice,(100-$totalPercent));  
	}
}else{
	$Data = array();
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
<script>
    $("#search_form .output_btn").click(function(){
		 window.location='../shop/output.php?'+$('#search_form').serialize()+'&type=user_staticstic_list';      
		});
</script>
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
            <li class=""><a href="user.php">会员注册统计</a></li>
            <li class="cur"><a href="user_area.php">会员来源地统计</a></li>
        </ul>
	</div>
    <script type='text/javascript' src='/static/js/plugin/highcharts/highcharts.js'></script>
    <script type='text/javascript' src='/static/member/js/statistics.js' ></script>
    <link href='/static/member/css/statistics.css' rel='stylesheet' type='text/css' />
	
    <script language="javascript">
    var pie_data=<?php echo json_encode($Data,JSON_UNESCAPED_UNICODE);?>;
    $(document).ready(global_obj.chart_pie);
    </script>
    <div class="r_con_wrap">
	<div class="control_btn">
	<form class="form-inline" action="?" method="get" id="search_form">
            <a href="javascript:void(0);" class="output_btn btn_green btn_w_120">生成报告</a>
			<input type="hidden" value="area" name="searchtype" />
			<textarea style="display:none" name="param"><?php echo !empty($Data)?json_encode($Data):''?></textarea>
	</form>  
    </div>    
        <div class="chart"></div>
		<center><?=!empty($Data)?'':'当前没有数据显示！'?></center><div class="chart">
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