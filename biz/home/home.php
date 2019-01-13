<?php
require_once('../global.php');
if($IsStore==0){
	echo '<script language="javascript">alert("请勿非法操作！");history.back();</script>';
	exit();
}

$rsSkin=$DB->GetRs("biz_home","*","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Skin_ID=".$rsBiz["Skin_ID"]);
$rsConfig=$DB->GetRs("web_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
$biz_data= $DB->GetRs("biz","Skin_ID","where Biz_ID=".$_SESSION["BIZ_ID"]);
$rsConfig['Biz_Skin_ID'] = $biz_data['Skin_ID'];
$json=json_decode($rsSkin['Home_Json'],true);
$rsBizs = $DB->GetRs("biz","Is_Union","where Users_ID='".$_SESSION['Users_ID']."' AND Biz_ID=".$_SESSION['BIZ_ID']);
if($rsBiz['Skin_ID']==1 && $rsBizs['Is_Union'] == 0){
	include('skin/'.$rsBiz['Skin_ID'].'.php');
	exit;
}else{
	function UrlList($DB){
		$rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);
		echo '<option value="">--请选择--</option>
			  <optgroup label="------------------产品分类------------------"></optgroup>';
		echo '<option value="/api/shop/'.$rsBiz["Users_ID"].'/biz/'.$_SESSION["BIZ_ID"].'/">店铺主页</option>';
		
		$cates = array();
		$DB->get("biz_category","*","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Category_ParentID=0 order by Category_Index asc,Category_ID asc");
		while($r=$DB->fetch_assoc()){
			$cates[] = $r;
		}
		foreach($cates as $v){
			echo '<option value="/api/shop/'.$rsBiz["Users_ID"].'/biz/'.$_SESSION["BIZ_ID"].'/products/'.$v["Category_ID"].'/">'.$v["Category_Name"].'</option>';
			$DB->get("biz_category","*","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Category_ParentID=".$v["Category_ID"]." order by Category_Index asc,Category_ID asc");
			while($f=$DB->fetch_assoc()){
				echo '<option value="/api/shop/'.$rsBiz["Users_ID"].'/biz/'.$_SESSION["BIZ_ID"].'/products/'.$f["Category_ID"].'/">└─'.$f["Category_Name"].'</option>';
			}
		}
	}
	
}
if (empty($rsConfig)) {
	echo '<script>alert("请先进行微官网基本配置");location.href="config.php";</script>';
	die();
}


if($_POST){
	$no=intval($_POST["no"]);
	if(is_array($json[$no]["ImgPath"])){
		$_POST["TitleList"]=array();
		foreach($_POST["ImgPathList"] as $key=>$value){
			$_POST["TitleList"][$key]="";
			if(empty($value)){
				unset($_POST["TitleList"][$key]);
				unset($_POST["ImgPathList"][$key]);
				unset($_POST["UrlList"][$key]);
			}
		}
	}
	
	if(is_array($json[$no]["ImgPath"])){
		$json[$no]["Title"] = array_merge($_POST["TitleList"]);
		$json[$no]["ImgPath"] = array_merge($_POST["ImgPathList"]);
		$json[$no]["Url"] = array_merge($_POST["UrlList"]);
	}else{
		if(!empty($_POST['Title'])){
			$json[$no]["Title"] = $_POST['Title'];
		}
		
		if(!empty($_POST['ImgPath'])){
			$json[$no]["ImgPath"] = $_POST['ImgPath'];
		}
		
		if(!empty($_POST['Url'])){
			$json[$no]["Url"] = $_POST['Url'];
		}
	}
	$Data=array(
		"Home_Json"=>json_encode($json,JSON_UNESCAPED_UNICODE),
	);
	
	$Flag=$DB->Set("biz_home",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Skin_ID=".$rsBiz['Skin_ID'].' AND Biz_ID ='.$_SESSION["BIZ_ID"]);
	
	if($Flag){
		$response=array(
			"Title"=>is_array($json[$no]["Title"]) ? json_encode(array_merge($json[$no]["Title"])):$json[$no]["Title"],
			"ImgPath"=>is_array($json[$no]["ImgPath"]) ? json_encode(array_merge($json[$no]["ImgPath"])):$json[$no]["ImgPath"],
			"Url"=>is_array($json[$no]["Url"]) ? json_encode(array_merge($json[$no]["Url"])):$json[$no]["Url"],
			"status"=>"1"
		);
	}else{
		$response=array(
			"status"=>"0"
		);
	}
	echo json_encode($response);
	exit;
}else{
	if (empty($json)) {
		echo '<script>alert("请重新选择风格");location.href="skin.php";</script>';
		die();
	}	
	//重组json
	foreach($json as $k=>$value){
		if(is_array($value["Title"])){
			$value["Title"] = json_encode($value["Title"]);
		}
		if(is_array($value["ImgPath"])){
			$value["ImgPath"] = json_encode($value["ImgPath"]);
		}
		if(is_array($value["Url"])){
			$value["Url"] = json_encode($value["Url"]);
		}
		$json[$k] = $value;
	}
}

require_once('skin/'.$rsBiz['Skin_ID'].'.php');
?>