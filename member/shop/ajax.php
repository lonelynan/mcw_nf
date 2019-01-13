<?php  
//网站后台微商城ajax 操作
ini_set("display_errors","On");
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/shipping.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/smarty.php');
require_once(BASEPATH.'/include/support/order_helpers.php');

//设置smarty
$smarty->left_delimiter = "{{";
$smarty->right_delimiter = "}}";
$template_dir = $_SERVER["DOCUMENT_ROOT"].'/member/shop/html';
$smarty->template_dir = $template_dir;

$action = $_REQUEST['action'];
if($action<>'get_region'){
	$UsersID = $_SESSION['Users_ID'];
}
if($action == 'mod_bankcard'){
	
	$User_ID = $_POST["UserID"];

	$condition = "where Users_ID='".$UsersID."' and User_ID= '".$User_ID."'";
	$data = array("Bank_Card"=>$_POST['Bank_Card']);

	$flag = $DB->set('shop_distribute_account',$data,$condition);

	if($flag){
		$response = array("status"=>1);
	}else{
		$response = array("status"=>0);
	}

	echo json_encode($response,JSON_UNESCAPED_UNICODE);	

}else if($action == 'get_attr'){
	
	$UsersID = $_POST["UsersID"];
	$TypeID = intval($_POST["TypeID"]);
	$ProductsID = intval($_POST["ProductsID"]);
			
	$html = build_attr_html($TypeID,$ProductsID);
	
	$Data = array(
		"status"=>1,
		"content"=>$html,
		"msg"=>"获取产品属性成功"
	);	
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	
}else if($action == 'get_attr_group'){
	
	$group_list = get_attr_groups($_GET['Type_ID'],$_SESSION["Users_ID"]);
	
	if(count($group_list) >0 ){
		 $response['status'] = 1;
		 $response['content'] =  $group_list;
	}else{
		$response['status'] = 0;
		$response['msg'] = '此产品类型下无属性组';
	}	
	echo json_encode($response,JSON_UNESCAPED_UNICODE);
}else if($action == 'get_shipping_company_edit_form'){
	
    //获取所要编辑快递公司信息	
	$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Shipping_ID= '".$_GET['Shipping_ID']."'";
	$rsShipping = $DB->getRs('shop_shipping_company','*',$condition);
	
	$Business_Name_List = array('express'=>'快递','common'=>'平邮');
	
	
	$Business_List = array();
	$Business_Checked_List = explode(',',$rsShipping['Shipping_Business']);
	
	foreach($Business_Name_List as  $key=>$item){
		$Business_List[$key]['Name'] = $item;	
		if(in_array($key,$Business_Checked_List)){
			$Business_List[$key]['Checked'] = 1;
		}else{
		   $Business_List[$key]['Checked'] = 0;
		}
	}


	$smarty->assign('Shipping',$rsShipping);	
	$smarty->assign('Business_List',$Business_List);
	$content = $smarty->fetch('shipping_company_edit_form.html');
	$response = array("status"=>1,'content'=>$content);

	echo json_encode($response,JSON_UNESCAPED_UNICODE);

}else if($action == 'get_deliver_content'){
	
   $shipping_company =  $_GET['Shipping_ID'];	
   $shipping_by_method = $_GET['By_Method'];
   
   $content = build_shipping_section_html($smarty,$_SESSION['Users_ID'],$shipping_company,$shipping_by_method);
   $response = array('status'=>1,'content'=>$content,'msg'=>'获取信息成功');
   echo json_encode($response,JSON_UNESCAPED_UNICODE);

}else if($action == 'get_dis_agent_form'){
	
	$account_id = $_GET['account_id'];
	$region_list = Area::getRegionList(array(35));
	$area_json = read_file(BASEPATH.'/data/area.js');
	$area_array = json_decode($area_json,TRUE);
	$province_list = $area_array[0];

	//获取代理地区设置信息
	$agent_areas = Dis_Agent_Area::where('Users_ID',$UsersID)
	               ->get();
	             
    $agent_provinces = $agent_areas->filter(function($agent_area){
    								if($agent_area->type = 1){
    									return true;
    								}
    				              })->map(function($agent_area){
    				              	return $agent_area->toArray();
    				              })->all();
	$agent_citys = $agent_areas->filter(function($agent_area){
    								if($agent_area->type = 2){
    									return true;
    								}
    				              })->map(function($agent_area){
    				              	return $agent_area->toArray();
    				              })->all();
    				             
	$agent_provinces_dropdown = get_dropdown_list($agent_provinces,'area_id');		              
	$agent_citys_dropdown = get_dropdown_list($agent_citys,'area_id');
	
	//整理出属于此分销商的代理地区以及已被占用的分销地区
	$province_checked = array();
	$province_disabled = array();
	$city_checked = array();
	$city_disabled = array();
	
    foreach($agent_provinces_dropdown as $area_id=>$agent_area){
    	if($agent_area['Account_ID'] == $account_id ){
    		$province_checked[] = $area_id;
    	}else{
    		$province_disabled[] = $area_id;
    	}
    }
    
    foreach($agent_citys_dropdown as $area_id=>$agent_area){
    	if($agent_area['Account_ID'] == $account_id ){
    		$city_checked[] = $area_id;
    	}else{
    		$city_disabled[] = $area_id;
    	}
    }

    $province_data_list = array();
    $city_data_list = array();
    
    foreach($province_list as $province_id=>$Province_Name){
    	
    	$province = array();
    	$province['checked'] = in_array($province_id,$province_checked);
    	$province['disabled'] = in_array($province_id,$province_disabled);
    	$province['Province_Name'] = $Province_Name;
    	$selectd_city_num = 0;
		$disable_city_num = 0;
    	
    	$city_list = $area_array['0,'.$province_id];
    	
    	if(count($city_list) > 0 ){
    	    foreach($city_list as $city_id=>$City_Name){
				if(in_array($city_id,$city_checked)){
					$city['checked'] = true;
					$selectd_city_num++;
				}else{
					$city['checked'] = false;
				}
				
				if(in_array($city_id,$city_disabled)){
					$city['disabled'] = true;
					$disable_city_num++;
				}else{
					$city['disabled'] = false;
				}
    	    	
    	    	
    	    	$city['City_Name'] = $City_Name;
    	    	$city_data_list[$province_id][$city_id] = $city;
    	    }
    				
    	}
		
		$province['selectd_city_num'] = $selectd_city_num;
		$province['disable_city_num'] = $disable_city_num;
		$province_data_list[$province_id] = $province;
    }

	

	//模板赋值
	$smarty->assign('account_id',$account_id);
	$smarty->assign('region_list',$region_list);
	$smarty->assign('province_data_list',$province_data_list);
	$smarty->assign('city_data_list',$city_data_list);
	
	$content = $smarty->fetch('dis_agent_form.html');
	$response = array('status'=>1,'content'=>$content,'msg'=>'获取信息成功');

	echo json_encode($response,JSON_UNESCAPED_UNICODE);
	
}elseif($action == 'get_input_record'){
	
    $stamps = get_range_stamp();	
	$input_info = order_input_record($UsersID,$stamps['Begin_Time'],$stamps['End_Time']);
	$html = generate_input_record_table($smarty,$input_info); 

    echo $html;
					  
}elseif($action == 'get_output_record'){
	
	$stamps = get_range_stamp();	
	$output_info = output_record($UsersID,$stamps['Begin_Time'],$stamps['End_Time']);
	
	$html = generate_output_table($smarty,$output_info); 

    echo $html;
	
}elseif($action == 'count_record'){

	$stamps =  get_range_stamp();	
	//入账信息
    $order = new Order();
	$input_builder = $order->ordersBetween($UsersID,$stamps['Begin_Time'],$stamps['End_Time'],4);
	$input_sum =  $input_builder->sum('Order_TotalAmount');
	$input_num = $input_builder->count();
	
	//出账信息
	$shop_dis_account = new Dis_Account_Record();
	$output_builder = $shop_dis_account->recordBetween($UsersID,$stamps['Begin_Time'],$stamps['End_Time'],2);
	$output_sum =  $output_builder->sum('Record_Money');
	$output_num = $output_builder->count();

	$response = array();
	$response['status'] = 1;
	$response['input_sum'] = $input_sum;
	$response['input_total_pages'] = ceil($input_num/5);
	$response['output_sum'] = $output_sum;
	$response['output_total_pages'] = ceil($output_num /5);
	$response['Begin_Time'] = ldate($stamps['Begin_Time']);
	$response['End_Time'] = ldate($stamps['End_Time']);
	
    echo json_encode($response,JSON_UNESCAPED_UNICODE);

}else if($action == 'get_deliver_content'){
	
   $shipping_company =  $_GET['Shipping_ID'];	
   $shipping_by_method = $_GET['By_Method'];
   
   $content = build_shipping_section_html($smarty,$_SESSION['Users_ID'],$shipping_company,$shipping_by_method);
   $response = array('status'=>1,'content'=>$content,'msg'=>'获取信息成功');
   echo json_encode($response,JSON_UNESCAPED_UNICODE);
   
}elseif($action=='get_region'){
	
	$areaid = isset($_GET["areaid"]) ? $_GET["areaid"] : 0;
	$parentid = isset($_GET["parentid"]) ? $_GET["parentid"] : 0;
	$region_model = isset($_GET["region_model"]) ? $_GET["region_model"] : '';
	if(isset($_GET["usersid"])){
		$UsersID = $_GET["usersid"];
	}else{
		exit(0);
	}
	$condition = "";
	if($areaid>0){
		$condition = "where Users_ID='".$UsersID."' and Region_ParentID=0 and Area_ID=".$areaid." order by Region_Index asc, Region_ID asc";
	}
	if($parentid>0){
		$condition = "where Users_ID='".$UsersID."' and Region_ParentID=".$parentid." order by Region_Index asc, Region_ID asc";
	}
	$html = array();
	if($condition){
		$DB->get("area_region","Region_ID,Region_Name",$condition);
		while($r=$DB->fetch_assoc()){
			$html[] = array("id"=>$r["Region_ID"],"name"=>$r["Region_Name"]);
		}
	}
	$Data = array(
		"status"=> count($html)>0 ? 1 : 0,
		"html"=>count($html)>0 ? $html : ""
	);
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
}


function get_range_stamp(){
	$Begin_Time = strtotime($_GET['Begin_Time']);
	$End_Time = strtotime($_GET['End_Time']);

	//代表同一天
	if($Begin_Time == $End_Time){
      
		$End_Time = Carbon\Carbon::createFromTimestamp($Begin_Time)
		                          ->addDay()
								  ->timestamp;
	}
	
	$stamps = array('Begin_Time'=>$Begin_Time,'End_Time'=>$End_Time);
	
	return $stamps;
}

