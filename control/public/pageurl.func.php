<?php
function page_url_select($DB,$usersid,$name,$id,$selected){
	$html = '<select name="'.$name.'"'.($id ? ' id="'.$id.'"' : '').'>';
	$html .= '<option value="">--请选择--</option>
	<optgroup label="------------------系统业务模块------------------"></optgroup>';
	$DB->get("wechat_material","Material_ID,Material_Table,Material_Json","where Users_ID='".$usersid."' and Material_Table<>'0' and Material_TableID=0 and Material_Display=0 order by Material_ID desc");
	while($rsMaterial=$DB->fetch_assoc()){
		$Material_Json=json_decode($rsMaterial['Material_Json'],true);
		$url = '/api/'.$usersid.'/'.$rsMaterial['Material_Table'].'/';
		$html .= '<option value="'.$url.'"'.($url==$selected ? ' selected' : '').'>'.$Material_Json['Title'].'</option>';
	}
	
	$html .= '<optgroup label="------------------微商城产品分类页面------------------"></optgroup>';
	$DB->get("shop_category","Category_ID,Category_Name,Category_ParentID","where Users_ID='".$usersid."' order by Category_ParentID asc,Category_Index asc");
	$shop_cates = array();
	while($r=$DB->fetch_assoc()){
		if($r["Category_ParentID"]==0){
			$shop_cates[$r["Category_ID"]] = $r;
		}else{
			$shop_cates[$r["Category_ParentID"]]["child"][] = $r;
		}
	}
	foreach($shop_cates as $value){
		$url = '/api/'.$usersid.'/shop/category/'.$value["Category_ID"].'/';
		$html .= '<option value="'.$url.'"'.($url==$selected ? ' selected' : '').'>'.$value["Category_Name"].'</option>';
		if(!empty($value["child"])){
			foreach($value["child"] as $v){
				$url = '/api/'.$usersid.'/shop/category/'.$v["Category_ID"].'/';
				$html .= '<option value="'.$url.'"'.($url==$selected ? ' selected' : '').'>&nbsp;&nbsp;├'.$v["Category_Name"].'</option>';
			}
		}
	}
	
	$html .= '<optgroup label="------------------自定义URL------------------"></optgroup>';
	$DB->get("wechat_url","*","where Users_ID='".$usersid."'");
	while($rsUrl=$DB->fetch_assoc()){
		$url = $rsUrl['Url_Value'];
		$html .= '<option value="'.$url.'"'.($url==$selected ? ' selected' : '').'>'.$rsUrl['Url_Name'].'('.$rsUrl['Url_Value'].')</option>';
	}
	$html .= '</select>';
	return $html;
}

function page_url_select_biz($DB,$usersid,$bizid,$name,$id,$selected){
	$html = '<select name="'.$name.'"'.($id ? ' id="'.$id.'"' : '').'>';
	$html .= '<option value="">--请选择--</option>';
	$url = '/api/shop/'.$usersid.'/biz/'.$bizid.'/';
	$html .= '<option value="/api/shop/'.$usersid.'/biz/'.$bizid.'/"'.($url==$selected ? ' selected' : '').'>店铺主页</option>';
	
	$html .= '<optgroup label="------------------产品分类------------------"></optgroup>';
	$DB->get("biz_category","Category_ID,Category_Name,Category_ParentID","where Users_ID='".$usersid."' and Biz_ID=".$bizid." order by Category_ParentID asc,Category_Index asc");
	$shop_cates = array();
	while($r=$DB->fetch_assoc()){
		if($r["Category_ParentID"]==0){
			$shop_cates[$r["Category_ID"]] = $r;
		}else{
			$shop_cates[$r["Category_ParentID"]]["child"][] = $r;
		}
	}
	foreach($shop_cates as $value){
		$url = '/api/shop/'.$usersid.'/biz/'.$bizid.'/products/'.$value["Category_ID"].'/';
		$html .= '<option value="'.$url.'"'.($url==$selected ? ' selected' : '').'>'.$value["Category_Name"].'</option>';
		if(!empty($value["child"])){
			foreach($value["child"] as $v){
				$url = '/api/shop/'.$usersid.'/biz/'.$bizid.'/products/'.$v["Category_ID"].'/';
				$html .= '<option value="'.$url.'"'.($url==$selected ? ' selected' : '').'>&nbsp;&nbsp;├'.$v["Category_Name"].'</option>';
			}
		}
	}
	
	$html .= '</select>';
	return $html;
}
?>