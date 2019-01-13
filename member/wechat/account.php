<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once(BASEPATH.'/include/support/order_helpers.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');

/**
 * 获取指定分销记录的支出金额
 * @param  string $Users_ID  [商城ID]
 * @param  array $order_id [订单号]
 * @return decimal            [description]
 */
function get_day_summary($Users_ID, $Order_ID) {
	global $DB;
	$money = $money1 = $money2 = $money3 = 0;

	$row = $DB->GetRs('distribute_record', 'Record_ID', "WHERE Users_ID='" . $Users_ID . "' AND Order_ID=" . intval($Order_ID));


	//统计出所有分销信息
	if ($row) {
		$row = $DB->GetRs('distribute_account_record', 'SUM(Record_Price+Nobi_Money) AS total', "WHERE Users_ID='"  . $Users_ID . "' AND Ds_Record_ID=" . $row['Record_ID'] . " AND Record_Status=2");
		if ($row['total']) {
			$money1= $row['total'];
		}
	}

	//代理商支出的钱
	$row = $DB->GetRs('distribute_agent_rec', 'SUM(Record_Money) AS total', "WHERE Order_ID=" . intval($Order_ID));
	if ($row['total']) {
		$money2 = $row['total'];
	}

	//股东分红支出的钱
	$row = $DB->GetRs('sha_account_record', 'SUM(Shasingle_Money) AS total', "WHERE Order_ID=" . intval($Order_ID));
	if ($row['total']) {
		$money3 = $row['total'];
	}

	$money = $money1 + $money2 + $money3;
	unset($money1, $money2, $money3);

	return $money;
	
}

//设置smarty
$smarty->left_delimiter = "{{";
$smarty->right_delimiter = "}}";
$template_dir = $_SERVER["DOCUMENT_ROOT"].'/member/shop/html';
$smarty->template_dir = $template_dir;

$base_url = base_url();

if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}else{
	  
	//订单状态列表

	$Users_ID = $_SESSION["Users_ID"];
	$rsConfig = shop_config($Users_ID);
	
	$rsUsers = Users::find($Users_ID)->toArray();
	$createtime = $rsUsers['Users_CreateTime'];
	$users_endtime = $rsUsers['Users_ExpireDate'];
	
	//获取续费价格
	$SysConfig = Setting::first()->toArray();
	
	$price_list = json_decode($SysConfig['sys_price'],true);
	$one_year_price = $price_list[1];
	
	//检测此店主是否有续费记录	
	$renewal_count = Users_Money_Record::Multiwhere(array('Users_ID'=>$Users_ID,'Record_Status'=>1))
	                 ->count();
	
	if($renewal_count > 0 ){
		$btn_text = '&nbsp;&nbsp;续费&nbsp;&nbsp;';
	}else{
		$btn_text = '立即升级';
	}
	
	//进行数据统计
	
	//计算时间点
	
	$carbon = Carbon\Carbon::createFromTimestamp(1440055725);
	
	$carbon = Carbon\Carbon::now();
	
	
	
	$now = $carbon->timestamp;
	$today = $carbon->startOfDay()->timestamp;
	
	//计算本月时间始末
	$month_start = $carbon->startOfMonth()->timestamp;
	$month_end =  $carbon->endOfMonth()->timestamp;
	
	$order = new Order();
	$account_record = new Dis_Account_Record();
	$account = new Dis_Account();
	
	//今日订单总数目
	$today_all_order_num = $order->statistics($Users_ID,'num',$today,$now);
	//今日已付款订单
	$today_payed_order_num = $order->statistics($Users_ID,'num',$today,$now,2);
	//今日销售额
	$today_order_sales = $order->statistics($Users_ID,'sales',$today,$now,2);
	//本月销售额
	$month_order_sales = $order->statistics($Users_ID,'sales',$month_start,$month_end,2);
	//今日支出佣金
	$today_output_money = $account_record->recordMoneySum($Users_ID,$today,$now,1);
	//本月支出佣金
	$month_output_money = $account_record->recordMoneySum($Users_ID,$month_start,$month_end,1);
    //今日加入分销商
	$today_new__account_num = $account->accountCount($Users_ID,$today,$now);
	//本月加入分销商
	$month_new_account_num =  $account->accountCount($Users_ID,$month_start,$month_end);
	
	$fields = array('Order_ID','Order_CreateTime','Order_TotalAmount','Order_Status');
	$Begin_Time = $today;
	$End_Time = $now;
	
	//本日进账记录
	$input_info = order_input_record($Users_ID,$Begin_Time,$End_Time);
	$order_input_record_table = generate_input_record_table($smarty,$input_info); 
	
	//本日出账记录
	$output_info  = output_record($Users_ID,$Begin_Time,$End_Time);
	$output_record_table = generate_output_table($smarty,$output_info); 
	
	
	//年，月
	$month = intval(date('m'));
	$year = date('Y');
	$day = intval(date('d'));
	$daysnum = days_in_month($month,$year);


	$carbon = Carbon\Carbon::createFromFormat('Y/m/d',"$year/$month/$day");
		
	$mon_start = $carbon->startOfMonth()->timestamp;
	$month_days_range = array();
	$month_sales = array();
	//获取每天的时间戳
	for ($i=1; $i<=$daysnum; $i++) { 
		$month_days_range[$i] = array('begin'=>$carbon->startOfDay()->timestamp,
		                               'end'=>$carbon->endOfDay()->timestamp);
		$month_sales[$i] = 0;
		if($i < $daysnum){
			$carbon->addDay();							   
		}
		
	}

	$mon_end = $carbon->endOfMonth()->timestamp;
	
	//获取本月内所有订单列表
	$month_order_list = Order::where('Users_ID',$Users_ID)
	                   ->whereBetween('Order_CreateTime', array($mon_start,$mon_end))
					   ->where('Order_Status',4)
					   ->get(array('Order_ID','Order_TotalAmount','Order_CreateTime'));
	
	
	//统计每日销量
	$max_val = 0;	
	$pay_sum = 0;
	$month_summary = [];

	if($month_order_list->count()>0){
		$order_array = 	$month_order_list->toArray();

		$new_collect = collect($order_array);

		foreach($month_days_range as $key => $day){
			
		   $sum = $new_collect->filter(function($order) use($day){
					if($order['Order_CreateTime'] >$day['begin']&&$order['Order_CreateTime'] <= $day['end']){
						return true;
					}
				})->sum('Order_TotalAmount');
			if($sum>$max_val){
				$max_val = $sum;
			}
			$month_sales[$key] = $sum;

		}

		//支出统计 begin （返佣金+代理佣金 + 股东佣金)
		if (count($order_array) > 0) {
			foreach ($order_array as $key => $value) {
				$day = date('Y-m-d', $value['Order_CreateTime']);
				$order_id_arr[$day] = get_day_summary($Users_ID, $value['Order_ID']);				
			}


			foreach ($month_days_range as $key => $value) {
				$day = date('Y-m-d', $value['begin']);
				if (isset($order_id_arr[$day])) {
					$month_summary[$key] = $order_id_arr[$day];
				} else {
					$month_summary[$key] = 0;
				}
			}
		}
		//支出统计 end
	}

	$chart_max_val = 1000;
	if($max_val > 1000){
		$unit = 250;
		$nums =$max_val/$unit;
        $max_nums = ceil($nums);
		$chart_max_val =  $max_nums*$unit;		
	}
	

				   
}

//获取短信数量
$item = $DB->GetRs("users","Users_Sms","where Users_ID='".$_SESSION["Users_ID"]."'");

$rsHome = $DB->GetRs("biz_union_home","*","where Users_ID='".$_SESSION["Users_ID"]."'");
if(!$rsHome){
	$Data = array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"Home_Json"=>'[{"ContentsType":"1","Title":["","",""],"ImgPath":["/uploadfiles/i719b43jsa/image/5806d1f4dd.jpg","/uploadfiles/i719b43jsa/image/5806d13c8f.jpg","/uploadfiles/i719b43jsa/image/5806d140c9.jpg"],"Url":["","",""],"Postion":"t01","Width":"640","Height":"294","NeedLink":"1"},{"ContentsType":"0","Title":"","ImgPath":"/api/shop/union/i1.jpg","Url":"","Postion":"t02","Width":"320","Height":"140","NeedLink":"1"},{"ContentsType":"0","Title":"","ImgPath":"/api/shop/union/i2.jpg","Url":"","Postion":"t03","Width":"320","Height":"140","NeedLink":"1"},{"ContentsType":"0","Title":"","ImgPath":"/api/shop/union/i3.jpg","Url":"","Postion":"t04","Width":"320","Height":"140","NeedLink":"1"},{"ContentsType":"0","Title":"","ImgPath":"/api/shop/union/i4.jpg","Url":"","Postion":"t05","Width":"320","Height":"140","NeedLink":"1"}]'
	);
	$DB->Add("biz_union_home",$Data);	
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset="UTF-8">
	<title>后台首页</title>
	<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
     <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
	<link href='/static/css/bootstrap.min.css' rel='stylesheet' type='text/css' />
	<link href='/static/css/font-awesome.css' rel='stylesheet' type='text/css' />
	<link href='/static/css/daterangepicker.css' rel='stylesheet' type='text/css' />
	<link href='/static/member/css/account_home.css' rel='stylesheet' type='text/css' />
   
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
		<script src="/static/js/html5shiv.js"></script>
		<script src="/static/js/respond.min.js"></script>
	<![endif]-->
        
	<script type='text/javascript' src='/static/js/jquery-1.11.1.min.js'></script>
	<script type='text/javascript' src='/static/js/bootstrap.min.js'></script>
	<script type='text/javascript' src='/static/js/jquery.twbsPagination.min.js'></script>
	<script type='text/javascript' src='/static/member/js/global.js'></script>
	<script src="/static/js/moment.js"></script>
	<script src="/static/js/daterangepicker.js"></script>
	<script type='text/javascript' src='/static/js/plugin/highcharts/highcharts_account.js'></script>
	<script type='text/javascript' src='/static/js/plugin/highcharts/exporting.js'></script>
	<script type='text/javascript' src='/static/member/js/account.js'></script>
    <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script> 
	<script language="javascript">
    var one_year_price = '<?=$one_year_price?>';
	var max_val = '<?=$chart_max_val?>';
	var chart_data={
		
		"count": [ {
			"name": "本月销售曲线图",
			"data": [<?=implode(',',array_values($month_sales))?>],
			"type": "column"
		},{
			"name": "本月支出曲线图",
			"data": [<?=implode(',',array_values($month_summary))?>],
			"type": "column"
		}],
		"date": [<?=implode(',',array_keys($month_sales))?>]
	};
	var base_url = '<?=$base_url?>';
	var Users_ID = '<?=$Users_ID?>'; 
	var ranges  =  {
						'今日': [moment(), moment()],
						'昨日': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'本月': [moment().startOf('month'), moment().endOf('month')]
					};
	
	var input_total_pages = '<?= $input_info['total_pages'] ?>';
	var output_total_pages = '<?= $output_info['total_pages'] ?>';				
	
	$(document).ready(account_obj.home_init);	
	</script>
	</head>

	<body style=" background:#fff;">
    <div id="wrap"> 
      <!-- tip begin -->
      
      	<div id="welcome">
        <p>欢迎你!&nbsp;&nbsp;<span id="Account_Name">
          <?=$rsUsers["Users_Account"]?>
          </span>
		  <span style="font-size:14px;">[剩余短信<a style="color:#ff0000; underline:none;"> <?php echo get_remain_sms(); ?></a> 条]</span>
		  </p>
      </div>
      
      <!-- tip end --> 
      <!-- statistics begin -->
      
      	<dl id="statistics" class="list" style=" margin-bottom:0px;">
        
        <dd class="statis-item ">
          <p><span class="statis-num"><?=$today_all_order_num?></span><br>
		  <span class="statis-name">今日所有订单</span>
		  </p>
        </dd>
        <dd class="statis-item ">
          <p><span class="statis-num"><?=$today_payed_order_num ?></span><br>
		  <span class="statis-name">今日已付款订单</span>
		  </p>
        </dd>
        <dd class="statis-item ">
          <p><span class="statis-num"><?=$today_order_sales?></span><br>
		  <span class="statis-name">今日销售额</span>
		  </p>
        </dd>
        <dd class="statis-item ">
          <p><span class="statis-num"><?=$month_order_sales?></span><br>
		  <span class="statis-name">本月销售额</span>
		  </p>
        </dd>
        <dd class="statis-item ">
          <p><span class="statis-num"><?=round_pad_zero($today_output_money,2)?></span><br>
		  <span class="statis-name">今日支出佣金</span>
		  </p>
        </dd>
        <dd class="statis-item ">
          <p><span class="statis-num"><?=round_pad_zero($month_output_money,2)?></span><br> 
			<span class="statis-name">本月支出佣金</span>
			</p>
        </dd>
        <dd class="statis-item ">
          <p><span class="statis-num"><?=$today_new__account_num?></span><br>
		  <span class="statis-name">今日加入分销商</span>
		  </p>
        </dd>
        <dd class="statis-item">
          <p><span class="statis-num"><?=$month_new_account_num?></span><br>
		  <span class="statis-name">本月加入分销商</span>
		  </p>
        </dd>
      </dl>
      
	  <div class="clearfix"></div>
      <!-- statistics end --> 
      <div class="baobiao_x">
	  <form method="get" action="/member/shop/setting/report.php" id="form11">
		<span style=" font-size:16px; line-height:50px; margin-left:15px;">时间范围：</span>
  		<select class="xx-select" id="type" name="type">
			<option value="week">近七天</option>
			<option value="month">近一个月</option>
			<option value="quarter">近三个月</option>
			<option value="half">近半年</option>
			<option value="year">近一年</option>			
		  </select>
	 
		<span style=" font-size:16px; line-height:50px; margin-left:15px;">时间粒度：</span>
		<span>

			<label class="labx_x"><input name="time" type="radio" value="day" class="time_xx" checked="checked" style=" margin:5px;">按日</label> 
			<label class="labx_x"><input name="time" type="radio" value="week" class="time_xx" style=" margin:5px;">按周</label> 
			<label class="labx_x"><input name="time" type="radio" value="month" class="time_xx" style=" margin:5px;">按月</label> 			
		</span>
				<span><a href="javascript:void(0);" class="scbg_x">生成报告</a></span>
		</form>
<script type="text/javascript">
$(function(){
	$(".scbg_x").click(function(){
		$("#form11").submit();
	})
})	

</script>		
	  </div>
	  <div class="clear"></div>
	  <div class="ddzj_x">
		<!--<span class="r"><a href=""><img src="/static/member/images/account/x3_2_03.jpg"></a></span>-->
		
<?php
$endDayTime = time();
$startDayTime = strtotime(date('Y-m-d', $endDayTime - 86400 * 6));

//获取七天内所有订单列表
$week_order_list = Order::where('Users_ID',$Users_ID)
                   ->whereBetween('Order_CreateTime', array($startDayTime, $endDayTime))
				   ->where('Order_Status',4)
				   ->get(array('Order_ID','Order_TotalAmount','Order_CreateTime'))->toArray();


$weekData = $weekChartData = [];
$orderData = $orderChartData = [];

if (count($week_order_list) > 0) {
	foreach ($week_order_list as $order) {
		$day = date('Y-m-d', $order['Order_CreateTime']);
		if (! isset($weekData[$day])) {
			$weekData[$day] = $order['Order_TotalAmount'];
			$orderData[$day] = 1;
		} else {
			$weekData[$day] += $order['Order_TotalAmount'];
			$orderData[$day]++;
		}
	}
	unset($order);
}


$days = ceil(($endDayTime - $startDayTime) / 86400);

//填充收入为空的日期
if (count($weekData) <= $days) {
	for($i = 0; $i < $days; $i++) {
		$day = date('Y-m-d', $startDayTime + 86400 * $i);
		if (! isset($weekData[$day])) {
			$weekChartData[$day] = 0;
			//$orderData[$day] = 0;
		} else {
			$weekChartData[$day] = $weekData[$day];
		}
	}
}

//填充订单数量为空的日期
if (count($orderData) <= $days) {
	for($i = 0; $i < $days; $i++) {
		$day = date('Y-m-d', $startDayTime + 86400 * $i);
		if (! isset($orderData[$day])) {
			$orderChartData[$day] = 0;
		} else {
			$orderChartData[$day] = $orderData[$day];
		}
	}
}
unset($day);

?>		




		<div id="weekOrderChart"></div>
	  </div>
	  <div class="ddzj_x">
		<!--近七天销售额总计<span class="r"><a href=""><img src="/static/member/images/account/x3_2_03.jpg"></a></span>-->
		<div id="weekChart"></div>
	  </div>
<script type="text/javascript">
$(function () {
$('#weekOrderChart').highcharts({
		chart: {
			backgroundColor: '#f6fbfa'
		},
		lang: {
			printChart:"打印图表",
			downloadJPEG: "下载JPEG 图片",
			downloadPDF: "下载PDF文档",
			downloadPNG: "下载PNG 图片",
			downloadSVG: "下载SVG 矢量图",
			exportButtonTitle: "导出图片" 
      	},
        title: {
            text: '近七天订单总计',
            x: 0 //center
        },
        subtitle: {
            text: '',
            x: 20
        },
        xAxis: {
            categories: ["<?=implode('","',array_keys($orderChartData))?>"]
        },
        yAxis: {
            title: {
                text: '单位（个）'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: '个'
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: [{
            name: '订单',
            data: [<?=implode(',',array_values($orderChartData))?>]
        }]
    });

    $('#weekChart').highcharts({
		chart: {
			backgroundColor: '#f6fbfa'
		},
		lang: {
			printChart:"打印图表",
			downloadJPEG: "下载JPEG 图片",
			downloadPDF: "下载PDF文档",
			downloadPNG: "下载PNG 图片",
			downloadSVG: "下载SVG 矢量图",
			exportButtonTitle: "导出图片" 
      	},		
        title: {
            text: '近七天订单金额总计',
            x: 0 //center
        },
        subtitle: {
            text: '',
            x: 20
        },
        xAxis: {
            categories: ["<?=implode('","',array_keys($weekChartData))?>"]
        },
        yAxis: {
            title: {
                text: '单位（元）'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: '元'
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: [{
            name: '收入',
            data: [<?=implode(',',array_values($weekChartData))?>]
        }]
    });
});	
</script>	  
      <!-- 财务统计begin -->
      
      	
        <div class="col-md-12" id="sale-chart-panel" style=" padding:0px;">
        <div class="panel panel-default" style=" border:none;"> 
          <!-- Default panel contents -->
          <div class="panel-heading"><span class="fa fa-usd golden fz-20"></span>财务统计</div>
          <div> 
            <!-- 日期选择表单 -->
            
            <!-- 日期结束表单--> 
            
            <!-- 财务信息列表begin -->
            <div id="finacial_detail"> 
              
              <!-- Nav tabs -->
              <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#province" aria-controls="province" role="tab" data-toggle="tab">进账记录</a></li>
                <li role="presentation"><a href="#city" aria-controls="city" role="tab" data-toggle="tab">出账记录</a></li>
				
				<form class="form-inline">
              <div class="form-group">
                <div class="input-group" id="reportrange">
                  <div class="input-group-addon "><span class="fa fa-calendar"></span></div>
                  <input type="text"  id="reportrange-input" class="form-control" name="date-range-picker" value="<?=sdate($today)?>-<?=sdate($now)?>" placeholder="日期间隔">
                </div>
              	
              </div>
			 <div class="form-group">
				<button type="button" class="btn btn-primary" id="input-record-search"  style="border:none">搜索</button>
			</div>
            
			</form>
				
              </ul>
              
              <!-- Tab panes -->
              
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="province"> 
                  <!-- 进账记录begin -->
                  <div id="input-record-brief" style=" padding:0 10px;">
                    <p class="fz-16">时间：<span id="input-record-begin" class="red fz-16">
                      <?=ldate($Begin_Time)?>
                      </span>到<span id="input-record-end" class="red fz-16">
                      <?=ldate($End_Time)?>
                      </span> 共计：&yen;<span class="red fz-18" id="input-record-sum">
                      <?=$input_info['sum']?>
                      </span> </p>
                  </div>
                  <div id="input_record_table">
                    <?=$order_input_record_table?>
                  </div>
                  <div id="pagination_container">
                    <ul id="input_record_pagination" class="pagination-sm">
                    </ul>
                  </div>
                  
                  <!-- 进账记录end --> 
                </div>
                <div role="tabpane2" class="tab-pane" id="city"> 
                  
                  <!-- 出账记录begin -->
                  <div id="output-record-brief">
                    <p class="fz-16">时间：<span id="output-record-begin" class="red fz-16">
                      <?=ldate($Begin_Time)?>
                      </span>到<span id="output-record-end"class="red fz-16">
                      <?=ldate($End_Time)?>
                      </span> 共计：&yen;<span class="red fz-18" id="output-record-sum">
                      <?=$output_info['sum']?>
                      </span> </p>
                  </div>
                  <div id="output_record_table">
                    <?=$output_record_table?>
                  </div>
                  <div id="pagination_container">
                    <ul id="output_record_pagination" class="pagination-sm">
                    </ul>
                  </div>
                  
                  <!-- 出账记录end --> 
                  
                </div>
              </div>
            </div>
            <!-- 财务信息列表end --> 
            
          </div>
        </div>
      </div>
      	
      
      <!-- 财务统计end --> 
      
      <!-- sales chart begin  -->
    

        <div class="col-md-12" id="sale-chart-panel" style=" padding:0px;">
        <div class="panel panel-default" style="border:none;"> 
          <!-- Default panel contents -->
          <div class="panel-heading">
			  <span class="fa fa-bar-chart sky-blue fz-20"></span>月销售统计
			  <div class="panel-body">
			  	<div id="circleChart" style="float:right; width:20%"></div>
	            <div class="chart" style="width:78%"></div>
	          </div>
		  </div>

        </div>
      </div>
<script type="text/javascript">
$(function () {
<?php
//进账
$getMoney = array_sum(array_values($month_sales));

//出账
$outMoney = array_sum(array_values($month_summary));

if (($getMoney + $outMoney) == 0) {
	$getPercent = 50;
} else {
	$getPercent = round($getMoney / ($getMoney + $outMoney) * 100, 2);
}

$outPercent = 100 - $getPercent;

?>
    $('#circleChart').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            backgroundColor: '#f6fbfa'
        },
		lang: {
			printChart:"打印图表",
			downloadJPEG: "下载JPEG 图片",
			downloadPDF: "下载PDF文档",
			downloadPNG: "下载PNG 图片",
			downloadSVG: "下载SVG 矢量图",
			exportButtonTitle: "导出图片" 
      	},        
        title: {
            text: '进账与出账比'
        },
        tooltip: {
    	    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                }
            }
        },
        series: [{
            type: 'pie',
            name: '查看',
            data: [
                {
                    name: '进账',
                    y: <?= $getPercent; ?>,
                    sliced: true,
                    selected: true
                },
                ['出账',     <?= $outPercent; ?>]
            ]
        }]
    });
});	

</script>  
     
      <!-- sales chart end --> 
    </div>
    
    <div id="confirm_renewal" class="lean-modal lean-modal-form">
    <div class="h">选择升级年限<span></span><a class="modal_close" href="#"></a></div>

    <form class="form"  id="renewal_form" method="post" action="/member/pay.php">
      <div class="rows">
        
       <p>
     升级年数： 
         
       	<?php foreach($price_list as $Qty=>$item):?>  
         <span id="<?=$Qty?>_<?=$item?>" class="qty_filter <?php if($Qty == 1){echo 'cur'; $cur_qty = $Qty;$cur_price=$item;} ?>">&nbsp;&nbsp&nbsp;&nbsp;<?=$Qty?>年&nbsp;&nbsp&nbsp;&nbsp;</span>
        <?php endforeach;?> 
       
        
        </p>
        <p>
        所需费用:&nbsp;&nbsp;<span class="fc_red" id="renewal_price"><?=$cur_price?></span>元
        </p>
        <p>
        为您节省:&nbsp;&nbsp;<span class="fc_red" id="renewal_sheng">0</span>元
        </p>
        
        <div class="clear"></div>
      </div>
      <div class="rows">
        <label></label>
        <span class="submit">
       	<input type="hidden" name="Qty" id="qty_val" value="<?=$cur_qty?>"/>
        <input type="hidden" name="Money" id="money_val" value="<?=$cur_price?>"/>
        
        <input type="submit" value="确定提交" name="submit_btn">
        </span>
        <div class="clear"></div>
      </div>
      
      <input type="hidden" name="UserID" value="<?=$_SESSION['Users_ID']?>">
      <input type="hidden" name="action" value="confirm_renewal">
    </form>
    <div class="tips"></div>
  </div>
</body>
</html>
