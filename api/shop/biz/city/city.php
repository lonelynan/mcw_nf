<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
//use Illuminate\Database\Capsule\Manager as Capsule;
if(isset($_GET["UsersID"])){
	$UsersID = $_GET["UsersID"];
}else{
	echo '缺少必要的参数';
	exit;
}
$rsConfig = shop_config($UsersID);
$defaultArea = $DB->GetRs('area','area_code'," where area_id=".$rsConfig['DefaultAreaID']);
if($defaultArea)
	$defaultArea['area_code'] = 268;//没有设置默认地区则默认郑州
$city_name = "";
$areaid = isset($_REQUEST['areaid']) ? intval($_REQUEST['areaid']) : 0;
if($areaid) {
	if($areaid == -2) {//自动定位
	    $citycode = empty($_REQUEST['citycode']) ? $defaultArea['area_code'] : $_REQUEST['citycode'];
		$rsArea = $DB->GetRs('area','area_id,area_name'," where area_code=".$citycode);
		$flag = setcookie('cookieflag', 'null', 0, '/');
		$flag = $flag && setcookie('city', $rsArea['area_id'].'|'.$rsArea['area_name'], 0, '/');
		if($flag){
			$Data = array(
			    'status' => 'ok',
			    'name' => $rsArea['area_name']
			);
			echo json_encode($Data);
			exit;
		}
	} else if($areaid == -1) {//全国
		$flag = setcookie('cookieflag', 'all', 0, '/');
		$flag = $flag && setcookie('city', '0|全国', 0, '/');
		if($flag){
			$Data = array(
				'status' => 'ok',
			);	
			echo json_encode($Data);
			exit;
		}
	} else {
		$r = $DB->GetRs("area","area_id,area_name"," WHERE area_id=$areaid");
		if($r) {
			$flag = setcookie('cookieflag', 'one', 0, '/');
			$flag = $flag && setcookie('city', $r['area_id'].'|'.$r['area_name'], 0, '/');
			if($flag){
				$Data = array(
					'status' => 'ok',
				);
				echo json_encode($Data);
			    exit;
			}
		}
	}
	$Data = array(
		'status' => 'ko',
	);
	echo json_encode($Data);
	exit;
} else {
	if(isset($_COOKIE['city'])){
		$areaid = explode('|', $_COOKIE['city'])[0];
	    $c = $DB->GetRs("area", "area_name", "WHERE area_id = ".$areaid);
        $city_name = $c['area_name'];
    }
}
//////////////////////////////
//include($_SERVER["DOCUMENT_ROOT"].'/include/library/Utf8pinyin.php');
//$o = NEW utf8pinyin;

//$collection = Area::all(array('area_id', 'area_name'));

//$data = $collection->map(function($area) use($o){
//	return array('area_id'=>$area->area_id,
//	'letter'=>substr( $o->str2py($area->area_name),0,1));
//});

//$query = generateUpdateBatchQuery('area', $data->toArray());
//Capsule::update($query);
//exit();
//////////////////////////////
$Bizs = $DB->Get("biz", "City_ID", "WHERE Users_ID='".$UsersID."' GROUP BY City_ID ORDER BY Biz_ID DESC");
$Area_IDarr = array();
while($b = $DB->fetch_assoc($Bizs)) {
	if(!empty($b['City_ID']))
	    $Area_IDarr[] = $b['City_ID'];
}
//var_dump($Area_IDarr);exit;
$Area_IDs = implode(',', $Area_IDarr);
$lists = array();
if(!empty($Area_IDs)){
	$result = $DB->Get("area","area_id,area_name,letter"," WHERE area_id in(".$Area_IDs.") AND area_code>0 ORDER BY letter asc");
	while($r = $DB->fetch_assoc($result)) {
		//////////////////////////////////////
		//$pinyin = $o->str2py($r['area_name']);
		//$letter = substr( $pinyin, 0, 1 );
		//$Data = array();
		//$Data[] = array('area_id'=>$r['area_id'],'letter'=>$letter);
		//////////////////////////////////////
		$lists[strtoupper($r['letter'])][] = $r;
	}
}
//$query = generateUpdateBatchQuery('area', $Data);
//$DB->query($query);
?>