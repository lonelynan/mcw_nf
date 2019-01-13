<?php
ini_set("display_errors","On");
include('../global.php');

if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}
$action = empty($_POST["action"]) ? '' : $_POST["action"];

if(empty($rsConfig['DefaultAreaID'])){
	$DefaultAreaID = 240;
}else{
	$DefaultAreaID = $rsConfig['DefaultAreaID'];
}
$rsDefaultArea = $DB->GetRs("area","area_id,area_name,area_code"," where area_id=".$DefaultAreaID);

if($action=="biz_list"){
	$condition = "where Users_ID='".$UsersID."' and Biz_Status=0";
	$order_arr = array("","Biz_Index desc,Biz_ID desc","Biz_MinPrice asc","Biz_MaxPrice desc","near");
	$orderby = "Biz_Index desc,Biz_ID desc";
	if(!empty($_POST['lat']) && !empty($_POST['lng'])){
		$_SESSION['firstlat']=$_POST['lat'];
		$_SESSION['firstlng']=$_POST['lng'];
		$location = map($_POST['lat'], $_POST['lng'], 100);
		$condition .= " and Biz_PrimaryLat > ".$location['lat_min']." and Biz_PrimaryLat < ".$location['lat_max']." and Biz_PrimaryLng > ".$location['lng_min']." and Biz_PrimaryLng < ".$location['lng_max'];
	}
	//setcookie('city', '240|郑州', 0, '/');
		if(isset($_COOKIE['city'])){
			$areaid = explode('|', $_COOKIE['city'])[0];
		}else{
			$areaid = $rsDefaultArea['area_id'];
		}
		if($areaid != 0){
			$rsArea = $DB->GetRs("area","area_id,area_name"," where area_id=".$areaid);
			$childids = get_childs($DB,"area","area_id","where area_parent_id=".$areaid);
			$areaids = $childids ? $areaid.','.$childids : $areaid;
			$condition .= " and Area_ID in(".$areaids.")";
		}
	
	if(!empty($_POST["title"])){
		$condition .= " and Biz_Name like '%".$_POST["title"]."%'";
	}
	if(!empty($_POST["categoryid"])){
		//$childids = get_childs($DB,"shop_union_category","Category_ID","where Category_ParentID=".$_POST["categoryid"]);
		$categoryid = $_POST["categoryid"];
		$condition .= " and Category_ID like '%," . $categoryid . ",%'";
	}else{
	    $categoryid = 0;
	    $condition .= '';
	}
	if(!empty($_POST["price"])){
		$arr_temp = array();
		$arr_temp = explode("-",$_POST["price"]);
		$arr_temp[1] = $arr_temp[1] ==0 ? 9999999 : $arr_temp[1];
		$condition .= " and ((Biz_MinPrice between ".$arr_temp[0]." and ".$arr_temp[1].") or (Biz_MaxPrice between ".$arr_temp[0]." and ".$arr_temp[1]."))";
	}
	if(!empty($_POST["regionid"])){
		$childids = get_childs($DB,"area_region","Region_ID","where Region_ParentID=".$_POST["regionid"]);
		$regionids = $childids ? $_POST["regionid"].','.$childids : $_POST["regionid"];
		$condition .= " and Region_ID in(".$regionids.")";
	}
	
	if(!empty($_POST["orderby"])){
		if($order_arr[$_POST["orderby"]]=='near'){
			setcookie('cookieflag', 'null', 0, '/');
			$orderby = ' order by '.$orderby;
		}else{
			$orderby = ' order by '.$order_arr[$_POST["orderby"]];
		}
	}else{
		$orderby = ' order by '.$orderby;
	}
	
	$condition .= $orderby;
	$DB->Get("biz","Biz_ID,Biz_Index,Biz_Name,Biz_Logo,Category_ID,Biz_MinPrice,Region_ID,Biz_PrimaryLng,Biz_PrimaryLat,Biz_Address,Category_Arr,qiword",$condition);
	
	$lists = $result = array();
	while($r=$DB->fetch_assoc()){
		$lists[] = $r;
	}
	
	foreach($lists as $key=>$value){
		$value["Category_Name"] = get_info($DB,"biz_union_category","Category_Name","where Category_ID=".$categoryid);
		$value["Region_Name"] = get_info($DB,"area_region","Region_Name","where Region_ID=".$value["Region_ID"]);
		$rsCommit = $DB->GetRs("user_order_commit","count(*) as num, sum(Score) as score","where Users_ID='".$UsersID."' and MID='shop' and Status=1 and Biz_ID=".$value['Biz_ID']);
        $value['average_ico'] = $rsCommit["num"]==0 ? '0.5' : round(($rsCommit["score"]/$rsCommit["num"]));
		$value['average_num'] = number_format ( $value['average_ico'] ,  1 ,  '.' ,  '' );
		if(!empty($_POST['lat']) && !empty($_POST['lng'])){
		    $value['meter'] = GetDistance($_POST['lat'],$_POST['lng'],$value['Biz_PrimaryLat'],$value['Biz_PrimaryLng']);
		}else{
			$value['meter'] = '';
		}
		/*if($value['Category_Arr'] != '""'){
		    $cat_arr = json_decode($value['Category_Arr'], true);
			$Categort_IDS = array_keys($cat_arr);
			if(!in_array($_POST["categoryid"], $Categort_IDS)){
				continue;
			}
		}*/
		$result[] = $value;
	}
	// if(!empty($result)){
		// if(!empty($_POST['lat']) && !empty($_POST['lng'])){
			// $result = fxy_array_sort($result,'meter');
		// }
	// }
	$counts = count($result);
	$num = 10;//每页记录数
	$p = !empty($_POST['page'])?intval(trim($_POST['page'])):1;
	$total = $counts;//数据记录总数
	$totalpage = ceil($total/$num);//总计页数
	$min_offset = ($p-1) * $num;//每次查询取记录
	$max_offset = $p * $num-1 > $total ? $total-1 : $p * $num-1;
	$list = array();
	for($i=$min_offset; $i<=$max_offset; $i++){
		$list[] = isset($result[$i]) ? $result[$i] : array();
	}
	//去除空值
	foreach($list as $k => $v){
		if(empty($list[$k])){
			unset($list[$k]);
		}
	}
	
	$pagesnum = 1;
	if($total>0){
		$pagesnum = $total%$num==0 ? ($total/$num) : (intval($total/$num)+1);
	}
	$Data = array(
		"status"=>count($list)>0 ? 1 : 0,
		"items"=>$list,
		"pagenum"=>$pagesnum
	);
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}elseif($action=="get_category"){//首页
    $condition = "where Users_ID='".$UsersID."'";
	if(!empty($_POST['categoryid'])){
		$condition .= " and Category_ParentID='".$_POST['categoryid']."'";
	}else{
		$condition .= " and Category_ParentID=0";
	}
	$order = " order by Category_Index asc,Category_ID asc";
	$condition .= $order;
	$lists = array();
	$DB->Get("biz_union_category","Category_ID,Category_Name",$condition);
	while($value=$DB->fetch_assoc()){
		$arr_temp = array(
			"one"=>$value["Category_ID"],
			"two"=>$value["Category_Name"]
		);
		$lists[] = $arr_temp;
	}
	$Data = array(
		"status"=>1,
		"list"=>$lists,
		"title"=>"全部分类"
	);
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}elseif($action=="get_price"){//首页
	$rsConfig=$DB->GetRs("shop_config","PriceRange","where Users_ID='".$UsersID."'");
	$array2 = json_decode($rsConfig['PriceRange'], true);
	$lists = array();
	if(!empty($array2)){
		foreach($array2 as $key => $value){
			//构造数组
			$one = $value['start'].'-'.$array2[$key]['end'];
			$two = '￥'.$value['start'].'~￥'.$array2[$key]['end'];
			if(empty($value['start'])){
				$one = '0-'.$array2[$key]['end'];
				$two = '￥'.$array2[$key]['end'].'元以下';
			}
			if(empty($value['end'])){
				$one = $value['start'].'-0';
				$two = '￥'.$value['start'].'元以上';
			}
			$arr_temp = array(
				"one"=>$one,
				"two"=>$two
			);
			$lists[] = $arr_temp;
		}
	}
	$Data = array(
		"status"=>1,
		"list"=>$lists,
		"title"=>"价格"
	);
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}elseif($action=="get_orderby"){//首页
	$lists = array(
	    array(
		    'one'=>1,
			'two'=>'默认'
		),
		array(
		    'one'=>2,
			'two'=>'价格升序'
		),
		array(
		    'one'=>3,
			'two'=>'价格降序'
		),
		array(
		    'one'=>4,
			'two'=>'离我最近'
		),
	);
	$Data = array(
		"status"=>1,
		"list"=>$lists,
		"title"=>"排序"
	);
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}elseif($action=="get_region"){
	$lists = array();
	$areaid = 0;
	if(isset($_COOKIE['city'])){
		$areaid = explode('|', $_COOKIE['city'])[0];
		if($areaid == 0){
			$rs = $DB->Get("area","area_id"," WHERE area_code>0 ORDER BY letter");
			while($b = $DB->fetch_assoc($rs)) {
				$Area_IDarr[] = $b['area_id'];
			}
			$areaids = implode(',', $Area_IDarr);
		}
		
	}else{
		if(empty($_POST['citycode'])){
			$areaid = $rsDefaultArea['area_id'];
		}else{
			$citycode = $_POST['citycode'];
			$area = $DB->GetRs("area","area_id,area_name","where area_code='".$citycode."'");
			if($area){
				$areaid = $area["area_id"];
			}
		}
	}
	//全国有bug  本应显示省份  所以定位处去掉全国定位
	$DB->Get("area_region","*","where Users_ID='".$UsersID."' and Region_Model='shop'".($areaid>0 ? " and Area_ID=".$areaid : " and Area_ID in(".$areaids.")")." and Region_ParentID=0 order by Region_ParentID asc,Region_Index asc,Region_ID asc");
	while($value=$DB->fetch_assoc()){
		$arr_temp = array(
			"one"=>$value["Region_ID"],
			"two"=>$value["Region_Name"]
		);
		$lists[] = $arr_temp;
	}
	$Data = array(
		"status"=>count($lists)>0 ? 1 : 0,
		"list"=>$lists,
		"title"=>"区域"
	);
	
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}elseif($action=="get_areaid"){
	$areaid = 0;
	$areaname="";
	$citycode = $_POST['citycode'];
	$area = $DB->GetRs("area","area_id,area_name","where area_code='".$citycode."'");
	if($area){
		$areaid = $area["area_id"];
		$areaname = $area["area_name"];
	}
	$Data = array(
		"areaid"=>$areaid,
		"areaname"=>$areaname
	);
	
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}

function get_childs($DB,$table,$fields,$condition){
	$child = array();
	$DB->Get($table,$fields,$condition);
	while($r=$DB->fetch_assoc()){
		$child[] = $r[$fields];
	}
	return count($child)>0 ? implode(",",$child) : '';
}

function get_info($DB,$table,$fields,$condition){
	$r = $DB->GetRs($table,$fields,$condition);
	return $r ? $r[$fields] : '';
}
?>