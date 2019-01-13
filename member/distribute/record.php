<?php
$base_url = base_url();
require_once($_SERVER["DOCUMENT_ROOT"].'/include/yinru/level3.php');
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$builder = Dis_Record::where('Users_ID', $_SESSION["Users_ID"])
                     ->with("Order")
				     ->with('DisAccountRecord')
					 ->with('product');
					
$url_param = array();	
					
if(isset($_GET["search"])){
	
	if($_GET["search"]==1){
		$url_param['search'] = 1;
		
		if(isset($_GET["Status"])){
			if($_GET["Status"]<>''){
				$builder->where('status',$_GET["Status"]);
			}
			$url_param['Status'] = $_GET["Status"];
		}
	
		if(!empty($_GET["AccTime_S"])){
			$builder->where('Record_CreateTime','>=',strtotime($_GET["AccTime_S"]));
			$url_param['AccTime_S'] = $_GET["AccTime_S"];
		}
	
		if(!empty($_GET["AccTime_E"])){
		   $builder->where('Record_CreateTime','<=',strtotime($_GET["AccTime_E"]));	
		   $url_param['AccTime_E'] = $_GET["AccTime_E"];
		}
		

	
		if(!empty($_GET["Keyword"])&&strlen(trim($_GET["Keyword"]))>0){
			
			$url_param['Search_Type'] = $_GET["Search_Type"];
			$url_param['Keyword'] = $_GET["Keyword"];
			
			//搜索用户
			if($_GET['Search_Type'] == 'buyer'){
				$builder->whereHas('Buyer',function($query){
						 $query->where('User_NickName','like','%'.$_GET["Keyword"].'%');
				});
			}
			
			if($_GET['Search_Type'] == 'owner'){
				$builder->whereHas('Owner',function($query){
						 $query->where('User_NickName','like','%'.$_GET["Keyword"].'%');
				});
			}
			
			if($_GET['Search_Type'] == 'product'){
				//搜索产品
				
				$builder->whereHas('product',function($query){
						  	$query->where('Products_Name','like','%'.$_GET["Keyword"].'%');
				});
			}	
		}
	}
	
}


$dis_record_paginator = $builder->orderBy('Record_ID', 'desc')
                                ->paginate('10');
$dis_record_paginator->setPath(base_url('member/distribute/record.php'));

if(!empty($url_param)){
	$dis_record_paginator->appends($url_param);
}

$page_links = $dis_record_paginator->render();
$account_record_dropdown = array();

$user_id_array = array();
$record_list = array();

foreach($dis_record_paginator->items() as $key=>$record){
	
	$user_id_array[] = $record['Buyer_ID'];
	$user_id_array[] = $record['Owner_ID'];
	
	$record = $record->toArray();
	
	$record['Product_Name'] = $record['product']['Products_Name'];
	
	$record_list[$key] = $record;
	
	if(!empty($record['dis_account_record'])){
		foreach($record['dis_account_record'] as $key=>$account_record){
				$user_id_array[] = $account_record['User_ID'];
		}
	}
}

$user_id_array = array_unique($user_id_array);

$user_nickname_dropdown = array();

if(!empty($user_id_array)){
	$user_dictionary = User::whereIn('User_ID',$user_id_array)
	                  ->get()->getDictionary();
	
	if(!empty($user_dictionary)){
		foreach($user_dictionary as $User_ID=>$User){
			$user_dropdown[$User_ID] = $User->User_NickName;
		}
	
	}	
}

$rsConfig = $rsConfigmenu = Dis_Config::find($_SESSION['Users_ID']);
$show_word = json_decode($rsConfig['Index_Professional_Json'],true);
		$show_word = $show_word['catcommission'];
		if (empty($show_word)) {
		$show_word = '奖金';			
		}
$recode_stutus = array('已生成', '已付款', '已完成');
$jcurid = 3;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/bootstrap.min.css' rel='stylesheet' type='text/css' />
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type='text/javascript' src='/static/js/jquery.datetimepicker.js'></script>
<link href='/static/css/jquery.datetimepicker.css' rel='stylesheet' type='text/css' />
<link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
<script type='text/javascript' src='/static/member/js/distribute/record.js?t=1'></script> 
<style>.search_btn {width:100px;}</style>   
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/distribute.css' rel='stylesheet' type='text/css' />
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/distribute/distribute_menubar.php');?>
    <script language="javascript">
	$(document).ready(function(){record_obj.record_init();});
	</script>
    <div id="record" class="r_con_wrap">
      <form class="search" id="search_form" method="get" action="?" style="margin-bottom:8px;">
        搜索类型:
        <select name="Search_Type">
          <option value='owner'>店主</option>
          <option value='buyer'>购买者</option>
          <option value='product'>产品</option>      
        </select>
        
        关键词：
        <input type="text" name="Keyword" value="" class="form_input" size="20" />
        状态：
        <select name="Status">
          <option value="">--请选择--</option>
          <option value='0'>未完成</option>
          <option value='1'>已完成</option>
      
        </select>
        时间：
        <input type="text" class="input" name="AccTime_S" value="" size="30" maxlength="20" />
        -
        <input type="text" class="input" name="AccTime_E" value="" size="30" maxlength="20" />
		<input type="hidden" value="1" name="search" />
        <input type="submit" class="search_btn" value="搜索" />
 
      </form>
      
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="5%" nowrap="nowrap">序号</td>
			<td width="8%" nowrap="nowrap">订单号</td>
            <td width="8%" nowrap="nowrap">店主</td>
            <td width="7%" nowrap="nowrap">购买者</td>
            <td width="10%" nowrap="nowrap">分销产品</td>
            <td width="20%" nowrap="nowrap">所获<?=$show_word?></td>
            <td width="5%" nowrap="nowrap">状态</td>
            <td width="10%" nowrap="nowrap">时间</td>
          </tr>
        </thead>
        <tbody> 
		<?php foreach($record_list as $key=>$rsRecord):?>
			<?php if(!empty($rsRecord['dis_account_record'])){?>
				<tr UserID="<?php echo $rsRecord['Record_ID'] ?>">
					<td nowarp="nowrap"><?=$rsRecord['Record_ID']?></td>
					<td nowrap="nowrap"><?php echo date("Ymd",$rsRecord["Record_CreateTime"]).$rsRecord["Order_ID"] ?></td>
					<td nowarp="nowrap" field=1><?php 
					echo empty($user_dropdown[$rsRecord['Owner_ID']]) ? '' : $user_dropdown[$rsRecord['Owner_ID']];?></td>
					<td nowarp="nowrap"><?php echo empty($user_dropdown[$rsRecord['Buyer_ID']]) ? '' : $user_dropdown[$rsRecord['Buyer_ID']];?></td>
					<td nowarp="nowrap">
				
					<?php
                        if($rsRecord['order']['Order_Type'] == 'offline' || $rsRecord['order']['Order_Type'] == 'offline_st'){
                            $ordercart = json_decode($rsRecord['order']['Order_CartList'], true);
                            foreach($ordercart as $BizID){
                                foreach ($BizID as $rsProduct){
                                    echo $rsProduct['ProductsName'];
                                }
                            }
                        }else{
                            echo !empty($rsRecord['Product_Name'])?$rsRecord['Product_Name']:'产品已删';
                        }
					?>
					</td>
					<td nowarp="nowrap" style="line-height:20px;">
						<?php
							$level_name = array();
							$tgo = 1;
							foreach ($level_name_new as $k=>$v) {
								if ($tgo > $rsConfig['Dis_Level']) break;
								$level_name[$k] = $v;
								$tgo++;
							}
							
							$i = 0;
							$j = 0;
							$uum = 0;
							foreach($rsRecord['dis_account_record'] as $keynum=>$account_record){
								if ($account_record['Record_Money'] >= 0 && ($account_record['Nobi_Money'] >= 0)) {
									//正常销售
									if ($i > $rsConfig['Title_Dislevel']) continue;
									$i++;
								} else {
									//退款																		
									if ($j > $rsConfig['Title_Dislevel']) continue;
									$j++;
								}
								
								if (isset($level_name[$account_record['level']]) || $account_record['level'] == 110) {
								echo '<img src="/static/member/images/account/yong.png" width="15">&nbsp;';
								echo !empty($user_dropdown[$account_record['User_ID']])?$user_dropdown[$account_record['User_ID']]:'无昵称'.'&nbsp;&nbsp;';
								
								if($rsRecord['Buyer_ID']==$account_record['User_ID']){
									$i++;
									$j++;						
									echo '<font style="color:blue">自销获</font>&nbsp;&nbsp;';
								}else{
									if ($account_record['Record_Money']>0) {
									echo '获';
									echo $level_name[$account_record['level']];
									}
								}
								$yformatarr = json_decode($account_record['yformat'],true);
								if (!empty($yformatarr)) {
								foreach($yformatarr as $key=>$value){
								foreach($value as $k=>$val){
									$yformatstr = '<strong style="color:#8a8a8a">('.$key.'号商品网站提成比:'.$val.')</strong>';
								}
								}
								} else {
									$yformatstr = '';
								}
								
								echo $account_record['Record_Money']>0 ? '<span>'.$show_word.'<strong style="color:#67b01a">&yen;'.round_pad_zero($account_record['Record_Money'],2).'</strong>'.$yformatstr : '' .($account_record['Record_Money']<0 ? '用户退款,'.$show_word.'<span style="color:#f7a000">' . $account_record['Record_Money'] . '</span>' : '').'</span>';
								} else {
									echo !empty($user_dropdown[$account_record['User_ID']])?$user_dropdown[$account_record['User_ID']]:'无昵称'.'&nbsp;&nbsp;';
								}

								//启用了爵位
								if ($account_record['Nobi_Level'] != '') {
									echo '&nbsp;<img src="/static/member/images/account/jue.png" width="15">&nbsp;';
									if ($account_record['Nobi_Money'] >= 0) {										
										if ($account_record['Nobi_Level'] == 'err') {
											echo $account_record['Nobi_Description'];											
										} else {
											echo '获爵位'.$account_record['Nobi_Level'].$show_word;
											echo '<strong style="color:#67b01a">&nbsp;&nbsp;&yen;' . $account_record['Nobi_Money'] . '</strong>';
										}
									} else {
										echo '<span>&nbsp;&nbsp;退款,爵位'.$show_word.'</span><span style="color:#f7a000">'. $account_record['Nobi_Money'] . '</span>';	
									}
								}
								if ($account_record['Nobi_Money'] >= 0) {
								echo '('.$recode_stutus[$account_record['Record_Status']].')';
								}
								if ($keynum < (count($rsRecord['dis_account_record']) - 1)) {
								echo '<hr style="height:1px;border:none;border-top:1px dashed #f7a101;" />';
								}
									$uum++;
							}
						?>
					</td>
					<td nowrap="nowrap"> 
					<?php if($rsRecord['status'] == 0):?>
						未完成
					<?php else:?>
						已完成
					<?php endif;?>
					
					</td>
					<td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsRecord['Record_CreateTime']) ?></td>            
				</tr>
			<?php } ?>
        <?php endforeach; ?>
        </tbody>
      </table>
      <div class="page"><?=$page_links?></div>
    </div>
  </div>  
</div>
</div>
</body>
</html>