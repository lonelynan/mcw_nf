<?php
ini_set("display_errors","On");
include('../global.php');
if(isset($_GET["UsersID"])){
	$UsersID=$_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}

$rsConfig = $DB->GetRs("shop_config","*","where Users_ID='".$UsersID."'");

$action = empty($_POST["action"]) ? '' : $_POST["action"];

if(empty($rsConfig['DefaultAreaID'])){
	$DefaultAreaID = 240;
}else{
	$DefaultAreaID = $rsConfig['DefaultAreaID'];
}
$rsDefaultArea = $DB->GetRs("area","area_id,area_name,area_code"," where area_id=".$DefaultAreaID);

if($action=="biz_list"){
	$condition = "where Users_ID='".$UsersID."' and Biz_Status=0";
	$order_arr = array("","Biz_Index asc,Biz_ID desc","Biz_MinPrice asc","Biz_MaxPrice desc");
	$orderby = "";
	if(!empty($_POST['lat']) && !empty($_POST['lng'])){
		$location = map($_POST['lat'], $_POST['lng'], 9);
		$condition .= " and Biz_PrimaryLat > ".$location['lat_min']." and Biz_PrimaryLat < ".$location['lat_max']." and Biz_PrimaryLng > ".$location['lng_min']." and Biz_PrimaryLng < ".$location['lng_max'];
	}else{
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
	}
	if(!empty($_POST["title"])){
		$condition .= " and Biz_Name like '%".$_POST["title"]."%'";
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
	if(!empty($_POST["categoryid"])){
		$_POST["categoryid"] = trim($_POST["categoryid"],",");
		$condition .= $_POST["categoryid"] ? " and Category_ID in(".$_POST["categoryid"].")" : '';
	}else{
	    $categoryid = 0;
	    $condition .= '';
	}
	
	if(!empty($_POST["orderby"])){
		$orderby = " order by ".$order_arr[$_POST["orderby"]];
	}
	$condition .= $orderby;
	$DB->Get("biz","Biz_ID,Biz_Name,Biz_Logo,Category_ID,Biz_MinPrice,Biz_Address,Region_ID,Biz_PrimaryLng,Biz_PrimaryLat",$condition);
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
		$result[] = $value;
	}
	if(!empty($result)){
		if(!empty($_POST['lat']) && !empty($_POST['lng'])){
			$result = fxy_array_sort($result,'meter');
		}
	}
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
			$areaid = $DefaultAreaID['area_id'];
		}else{
			$citycode = $_POST['citycode'];
			$area = $DB->GetRs("area","area_id,area_name","where area_code='".$citycode."'");
			if($area){
				$areaid = $area["area_id"];
			}
		}
	}	
	$DB->Get("area_region","*","where Users_ID='".$UsersID."' and Region_Model='shop'".($areaid>0 ? " and Area_ID=".$areaid : " and Area_ID in (".$areaids.")")." order by Region_ParentID asc,Region_Index asc,Region_ID asc");
	while($value=$DB->fetch_assoc()){
		$arr_temp = array(
			"id"=>$value["Region_ID"],
			"name"=>$value["Region_Name"]
		);
		
		if($value["Region_ParentID"]==0){
			$lists[$value["Region_ID"]] = $arr_temp;
		}else{
			$lists[$value["Region_ParentID"]]["child"][] = $arr_temp;
		}
	}
	$Data = array(
		"status"=>count($lists)>0 ? 1 : 0,
		"items"=>$lists
	);
	
	echo json_encode($Data, JSON_UNESCAPED_UNICODE );
	exit;
}elseif($action=="get_categoryid"){
	$lists = array();
	$DB->Get("shop_union_category","*","where Users_ID='".$UsersID."' order by Category_ParentID asc,Category_Index asc,Category_ID asc");
	while($value=$DB->fetch_assoc()){
		$arr_temp = array(
			"id"=>$value["Category_ID"],
			"name"=>$value["Category_Name"]
		);
		
		if($value["Category_ParentID"]==0){
			$lists[$value["Category_ID"]] = $arr_temp;
		}else{
			$lists[$value["Category_ParentID"]]["child"][] = $arr_temp;
		}
	}
	$Data = array(
		"status"=>count($lists)>0 ? 1 : 0,
		"items"=>$lists
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