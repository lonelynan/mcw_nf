<?php
require_once('global.php');

if(!empty($dis_account_record)){
	$dis_account_record_list = $dis_account_record->toArray();								 
	
	foreach($dis_account_record_list as $key=>$account_record){
		$account_record['Owner_ID'] = $account_record['dis_record']['Owner_ID'];
		unset($account_record['dis_record']);
		$dis_account_record_list[$key]	= 	$account_record;
	}
	
}
//购买级别数组

//获取记录
$builder = Dis_Account_Record::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$_SESSION[$UsersID.'User_ID']))->where('Nobi_Money','!=',0.00);

$url_param = array('UsersID'=>$UsersID);

	$url_param['filter'] = 'all';


$builder->orderBy('Record_CreateTime','desc');

$Records_paginate_obj = $builder->simplePaginate(10);
$Records_paginate_obj->setPath(base_url('api/distribute/detaillist_nobi.php'));


$Records_paginate_obj->appends($url_param);
$distribute_record  = $Records_paginate_obj->toArray()['data'];

$page_link = $Records_paginate_obj->render();

$header_title = '爵位明细';
require_once('header.php');
$LevelNames = [
	'1' => '一',
	'2' => '二',
	'3' => '三',
	'4' => '四',
	'5' => '五',
	'6' => '六',
	'7' => '七',
	'8' => '八',
	
	'9' => '九'
];
?>

<body>
<link href="/static/api/distribute/css/detaillist.css" rel="stylesheet">

<header class="bar bar-nav">
  <a href="javascript:history.back()" class="fa fa-2x fa-chevron-left grey pull-left"></a>
  <a href="<?=$distribute_url.'?love';?>" class="fa fa-2x fa-sitemap grey pull-right"></a>
  <h1 class="title"><?php echo'爵位明细'?></h1>
  
</header>
<div class="wrap">
	<div class="container">    	
        <div class="row">       
      <ul class="list-group" id="record-panel">
    
      	<?php foreach($distribute_record as $key=>$item):?>        
		<li class="list-group-item">		
        	<p class="record-description">				
				<?=$item['Nobi_Description']?>&nbsp;&nbsp;<span class="red">&yen;<?=round_pad_zero($item['Nobi_Money'],2)?></span>
			
                     
            </p>		
            <p class="record-description" ><?=ldate($item['Record_CreateTime'])?>&nbsp;&nbsp;&nbsp;&nbsp;</p>
            <p >
             <?php 
			 	if($item['Record_Status'] == 0){
					echo '进行中';
				}else{
					echo '已完成';
				}
			 ?>
             </p>                        
        </li>		 
        <?php endforeach;?>
      </ul>   
      <?php
      	echo $page_link;
      ?>
        </div>

    </div>
    
  	
  
    
</div>

 
<?php require_once('../shop/skin/distribute_footer.php');?> 
 
 
</body>
</html>
