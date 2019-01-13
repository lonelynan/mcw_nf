?><?php
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}

$Users_ID = $_SESSION["Users_ID"];

require_once CMS_ROOT . './include/library/PHPExcel.php';
require_once CMS_ROOT . './include/library/PHPExcel/Writer/Excel2007.php';
require_once CMS_ROOT . './include/library/PHPExcel/Writer/Excel5.php';
include_once CMS_ROOT . './include/library/PHPExcel/IOFactory.php';
 
/**
 * 下载统计结果
 * @param array $data
 * @return attach
 */
function getExcel($fileName, $headArr, $data){
    if(empty($data) || !is_array($data)){
        die("data must be a array");
    }
    if(empty($fileName)){
        exit;
    }
    $date = date("Y_m_d",time());
    $fileName .= "_{$date}.xls";
 
    //创建新的PHPExcel对象
    $objPHPExcel = new PHPExcel();
    $objProps = $objPHPExcel->getProperties()->setCreator("wangzhongwang");;
     
    //设置表头
    $key = ord("A");
    foreach($headArr as $v){
        $colum = chr($key);
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
        $key += 1;
    }

    $column = 2;
    $objActSheet = $objPHPExcel->getActiveSheet();
    foreach($data as $key => $rows){ //行写入
        $span = ord("A");
        foreach($rows as $keyName=>$value){// 列写入
            $j = chr($span);
            $objActSheet->setCellValue($j.$column, $value);
            $span++;
        }
        $column++;
    }

    $fileName = iconv("utf-8", "gb2312", $fileName);
    //重命名表
    $objPHPExcel->getActiveSheet()->setTitle('Simple');

    //设置活动单指数到第一个表,所以Excel打开这是第一个表
    $objPHPExcel->setActiveSheetIndex(0);


	//清除缓存
	ob_clean();


    //将输出重定向到一个客户端web浏览器(Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header("Content-Disposition: attachment; filename=\"$fileName\"");
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output'); //文件通过浏览器下载

	die();  
 
}



?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<link href='/static/css/bootstrap.min.css' rel='stylesheet' type='text/css' />
<link href='/static/css/font-awesome.css' rel='stylesheet' type='text/css' />
<link href='/static/css/daterangepicker.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/account_home.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>

</head>
<body style="background-color:#fff;">
<div id="wrap">
<div class="baobiao_x">
<form method="get" action="?" id="form1">
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
			<label class="labx_x"><input name="time" type="radio" value="day" class="time_xx" style=" margin:5px;">按日</label> 
			<label class="labx_x"><input name="time" type="radio" value="week" class="time_xx" style=" margin:5px;">按周</label> 
			<label class="labx_x"><input name="time" type="radio" value="month" class="time_xx" style=" margin:5px;">按月</label> 			
		</span>
		<span><a href="javascript:void(0);" class="scbg_x">生成报告</a></span>
</form>	
	  </div>
	  
</div>
<!-- day report -->
<?php

//计算时间点
//$carbon = Carbon\Carbon::createFromTimestamp();

$type = isset($_GET['type']) ? $_GET['type'] : 'week';
$type = !in_array($type, ['week', 'month', 'quarter', 'half', 'year']) ? 'week' : $type;

$time = isset($_GET['time']) ? $_GET['time'] : 'day';
$time = !in_array($time, ['day', 'week', 'month']) ? 'day' : $time;

$carbon = Carbon\Carbon::now();

if ($type == 'week') {
	//本周
	$end_time = $carbon->timestamp;
	$begin_time = $end_time - 86400 * 7;
} else if ($type == 'month') {
	//计算本月时间始末
	$begin_time = strtotime("-1 month");
	$end_time =  $carbon->timestamp;
} elseif ($type == 'quarter') {
	//近三月
	$begin_time = strtotime("-3 month");
	$end_time = time();
} elseif ($type == 'half') {
	$begin_time = strtotime("-6 month");
	$end_time = time();
} elseif ($type == 'year') {
	$begin_time = strtotime("-1 year");
	$end_time = time();	
}

$order = new Order();

$Order_Status = 2;
$order_list = $order->where('Users_ID', $Users_ID)
			->whereBetween('Order_CreateTime', [$begin_time, $end_time])
			->where('Order_Status', '>=', $Order_Status)
			->orderBy('Order_ID', 'DESC')
			->get(['Order_ID', 'User_ID', 'Order_TotalAmount', 'Order_CreateTime'])			
			->toArray();

//粒度按天
$data = $data2 = [];

if ($time == 'day' && count($order_list) > 0) {
	foreach ($order_list as $value) {
		$data[date('Y-m-d', $value['Order_CreateTime'])][] = $value;
	}

} else if ($time == 'week' && count($order_list) > 0) {
	//粒度按周
	foreach ($order_list as $value) {
		$data[date('Y-W', $value['Order_CreateTime'])][] = $value;
	}

} else if ($time == 'month' && count($order_list) > 0) {
	//粒度按月
	foreach ($order_list as $value) {
		$data[date('Y-m', $value['Order_CreateTime'])][] = $value;
	}

}

if (count($order_list) > 0) {
	foreach ($data as $key => $value) {
		//订单数量
		$data2[$key]['date'] = $key;
		$data2[$key]['OrderTotal'] = count($value);

		//订单总金额
		foreach ($value as $item) {
			if (! isset($data2[$key]['OrderTotalAmount'])) {
				$data2[$key]['OrderTotalAmount'] = $item['Order_TotalAmount'];
			} else {
				$data2[$key]['OrderTotalAmount'] += $item['Order_TotalAmount'];
			}
		}
	}	
}
	

//按日期索引排序
krsort($data2);



?>
<style>
table{width:70%;margin-left:10px;}
table th{background: #1fbba6; color: #fff; line-height: 25px;}
th,td{padding:5px;}
td span{font-weight:bold; padding:2px 5px; color:red}
</style>
<table><tr><th>日期</th><th>订单数量</th><th>金额</th></tr>
<?php
$totalOrder = $totalAmount = 0;

foreach ($data2 as $date => $item) {
	$totalOrder += $item['OrderTotal'];
	$totalAmount += $item['OrderTotalAmount'];
?>
<tr><td><?php echo $item['date']; ?></td><td><?php echo $item['OrderTotal'];?></td><td><?php echo $item['OrderTotalAmount'] ?>元</td></tr>
<?php
}
?>
<tr style="background-color:#efefef;"><td>共计</td><td><span><?php echo $totalOrder;?></span>个订单</td><td>共<span><?php echo $totalAmount; ?></span>元</td></tr>
</table>
<!--// day report -->

<!-- download -->
<?php
if (count($data2)) {
?>
<button id="down" style="clear:both; margin-top:20px; width:70%; margin-left:5px; background-color:#1fbba6; border:none;height:30px; color:#fff; font-weight:bold; border-radius:4px;">点击下载</button>
<?php
}
?>
<!--// download -->

<script type='text/javascript'>
$(function(){
	$(".scbg_x").click(function(){
		$("#form1").submit();
	})

	$("#type").val('<?php echo $type;?>');
	$("input:radio[value='<?php echo $time;?>']").attr('checked','true');

	$("#down").click(function() {
		var url = location.href;
		if (url.indexOf("?") == -1) {
			url = url + '?';
		}
		location.href = url + '&act=download';
	})
	
})
</script>

<?php
//浏览器里下载
$act = isset($_GET['act']) ? $_GET['act'] : '';
if ($act == 'download') {
	$fileName = "test_excel";
	$headArr = array("日期","订单数量","金额");

	if (count($data2)) {
		$data2 = array_merge($data2, [
			'summary' => [
			'date' => '总计',
			'OrderTotal' => $totalOrder . '个订单',
			'totalAmount' => '共' . $totalAmount . '元',
		]]);
	}

	getExcel($fileName, $headArr, $data2);
	exit();
}

unset($data);
?>
</body>
</html>