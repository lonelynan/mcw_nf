<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');

$level_config = $rsConfig['Dis_Level'];
			   
//获取此用户下属排行榜
$posterity = $accountObj->getPosterity($level_config);
$posterity_income_list = income_list($posterity,100);

//获取总店排行榜
$HeadDistributeList = Dis_Account::where(array('Users_ID'=>$UsersID))
					  ->where('Total_Income', '>=', $rsConfig['H_Incomelist_Limit'])
		              ->orderBy('Total_Income','desc')
					  ->take(10)
					  ->get(array('Users_ID','User_ID','invite_id','User_Name','Account_ID','Shop_Name','Total_Income','Professional_Title'));
					 
$H_Incomelist = $HeadDistributeList->toArray();

$battle_act_data = array();
foreach($H_Incomelist as $key=>$item) {
	
	$battle_act_data['Total_Income'] = get_my_leiji_income($item['Users_ID'],$item['User_ID']);
       $condition = "where Users_ID='".$item['Users_ID']."' and User_ID=".$item['User_ID'];  
		//	print_r($battle_act_data);
	$DB->Set("distribute_account", $battle_act_data,$condition);
}


//购买级别所得
$in_list = false;
if($rsConfig["HIncomelist_Open"] == 1){
	$in_list = true;;
}else{
	$in_list = $HeadDistributeList->contains('User_ID',$User_ID);
}

$dis_title_level = get_dis_pro_title($DB,$UsersID);

$header_title = '财富排行榜';
require_once('header.php');
?>
<body>
<link href="/static/api/distribute/css/income_list.css" rel="stylesheet">
<script src="/static/js/jquery.idTabs.min.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$("#income-filter").idTabs("table1");
	});
</script>

<div class="wrap">
	<div class="container">
    
      <div class="row">
      		<div class="income-list-image">
            	 <img  width="100%" src="/static/api/distribute/images/income_list_banner.jpg"/>	
            </div>
      </div>
   	 
      <div class="row" id="filter-panel">
    		 <div id="income-filter" class="btn-group"><a href="#table1" class="item btn btn-default">总部分销商</a><a href="#table2" class="item btn btn-default">我的好友</a>
             <a href="#" class="clearfix"></a></div>
      </div>
      
     
		<div class="row" >
           <div id="table-panel" >
           <table class="table income_list" id="table1">
           <thead>
        <tr>
          <th colspan="2">排名</th>
          <th>爵位</th>
          <th>佣金</th>
        </tr>
      </thead>
      
        <?php if(!$in_list) { ?>	
         	
            <tr><td colspan="4"><span class="alert-danger">无权查看，需入榜后才能查看。</span></td></tr>
        <?php } else { ?>
        
      
      <tbody>
       	<?php foreach($H_Incomelist as $key=>$item) { $userinfo = $DB->GetRs("user","User_HeadImg","where User_ID=".$item["User_ID"]);?>		
        	<tr id="rank_<?=$key+1?>">
          <th>
            
             <span class="rank"><?=($key>2)?($key+1):''?></span> 
          </th>
          <td>
		  <?php if(!empty($userinfo['User_HeadImg'])) { ?>
		  	  <img class="hd_img" src="<?=$userinfo['User_HeadImg']?>"/>
		  <?php } else { ?>
			  <?php if(!empty($item['Shop_Logo'])) { ?>
				  <img class="hd_img" src="<?=$item['Shop_Logo']?>"/>
			  <?php } else { ?>
				  <img class="hd_img" src="/static/api/images/user/face.jpg"/>	
			  <?php } ?>
		  <?php } ?>
		  <?=str_limit($item['Shop_Name'],3)?>
          </td>
          <td>
          
            <?php if(!empty($dis_title_level)) { ?>
          		<?php if(!empty($dis_title_level[$item['Professional_Title']])) { ?>	
               
                <span class="juewei"><?=$dis_title_level[$item['Professional_Title']]['Name'];?></span>
                <?php } else { ?>
                 无
                <?php } ?>
			<?php } else { ?>
                无
			<?php } ?>
          </td>
          
          <td><span class="total_income">&yen;<?=round_pad_zero($item['Total_Income'],2)?></span></td>
        
        </tr>		
		<?php } ?>
      </tbody>
    
        <?php }  ?>
        </table>
   		  <table class="table income_list" id="table2">
      <thead>
        <tr>
      
          <tr>
          <th colspan="2">排名</th>
          <th>爵位</th>
          <th>佣金</th>
        </tr>
        
        </tr>
      </thead>
      <tbody>
       
       <?php foreach($posterity_income_list as $key=>$item) { ?>	   
        	<tr id="rank_<?=$key+1?>">
          <th>
            
             <span class="rank"><?=($key>2)?($key+1):''?></span> 
          </th>
          <td>
          <?php if(!empty($item['Shop_Logo'])) { ?>
		  	  <img class="hd_img" src="<?=$item['Shop_Logo']?>"/>
		  <?php } else { ?>
		  	  <img class="hd_img" src="/static/api/images/user/face.jpg"/>	
		  <?php } ?>
		  <?=str_limit($item['Shop_Name'],10)?>
          </td>
          <td>
          
            <?php if(!empty($dis_title_level)) { ?>
          		<?php if(!empty($dis_title_level[$item['Professional_Title']])) { ?>	
               
                <span class="juewei"><?=$dis_title_level[$item['Professional_Title']]['Name'];?></span>
                <?php } else { ?>
                 无
                <?php } ?>
			<?php } else { ?>
                无
			<?php } ?>
          </td>
          
          <td><span class="total_income">&yen;<?=round_pad_zero($item['Total_Income'],2)?></span></td>
        
        </tr>	  
       <?php } ?>
        
     
     
      </tbody>
    </table>
           </div>
   	  </div>
  </div>

</div>
    
  	
  
    
</div>

<?php require_once('../shop/skin/distribute_footer.php');?> 
 
 
</body>
</html>



