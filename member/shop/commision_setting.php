<?php
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}


if ($_POST) 
{
  unset($_POST['submit_button']);
  if (!is_numeric($_POST['platForm_Income_Reward']) || $_POST['platForm_Income_Reward'] > 100 || $_POST['platForm_Income_Reward'] < 0) 
  {
    echo '<script language="javascript">alert("发放金额应小于100%大于0%");history.back();</script>'; exit();
  }

  if (!is_numeric($_POST['noBi_Reward']) || !is_numeric($_POST['area_Proxy_Reward']) || !is_numeric($_POST['sha_Reward']) || !is_numeric($_POST['commission_Reward']) || !is_numeric($_POST['salesman_ratio']) || !is_numeric($_POST['platForm_Income_Reward'])) 
  {
    echo '<script language="javascript">alert("比例应为整数，您填写有误！");history.back();</script>'; exit();
  }
  $sumcommission = $_POST['noBi_Reward']+$_POST['area_Proxy_Reward']+$_POST['commission_Reward']+$_POST['sha_Reward']+$_POST['salesman_ratio'];
  if ( $sumcommission > 100 || $sumcommission < 0 ) 
  {
    echo '<script language="javascript">alert("爵位奖励比例+区域代理比例+股东佣金比例+佣金比例+业务比例不能超过100%且不能小于0！");history.back();</script>'; exit();
  }  
  
  $salesman_level_commission = 0;
  foreach($_POST["salesman_level_ratio"] as $key => $v){
	  $salesman_level_commission += $v;
  }
  if ( $salesman_level_commission > 100 || $salesman_level_commission < 0 ) {
	  echo '<script language="javascript">alert("一级业务+二级业务+三级业务比例不能超过100%且不能小于0！");history.back();</script>'; exit();
  }
// 判断佣金比例是否超过百分之百
  foreach($_POST["Distribute"] as $key => $v){
    $yongjifls = 0;
    $yongjifl = 0;
    foreach($v as $k){
      $yongjifls += $k;
      if($yongjifls > 100){
        break;
      } 
    }
    if($yongjifls >100){
      $yongjifl = 1;
      break;
    }
  }
  if($yongjifl==1){
    echo '<script language="javascript">alert("佣金返利设置不可超过百分百");history.back();</script>';
    exit;
  }
	$Data = array('Shop_Commision_Reward_Json' => json_encode($_POST, JSON_UNESCAPED_UNICODE));

  $Flag=$DB->Set("shop_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
  if($Flag){
    echo '<script language="javascript">alert("修改成功");window.location="commision_setting.php";</script>';
  }else{
    echo '<script language="javascript">alert("保存失败");history.back();</script>';
  }
  exit;
} else {
	$shop_config = shop_config($_SESSION["Users_ID"]);
	$dis_config = dis_config($_SESSION["Users_ID"]);

  $Shop_Commision_Reward_Arr = array();
  if (!is_null($shop_config['Shop_Commision_Reward_Json'])) 
  {
    $Shop_Commision_Reward_Arr = json_decode($shop_config['Shop_Commision_Reward_Json'], true);
  }
}
$jcurid = 1;
?>

<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">

<link href='/static/css/global.css' rel='stylesheet' type='text/css' />

<link href='/static/member/css/main.css?t=<?php echo time(); ?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type='text/javascript' src='/static/member/js/products_attr_helper.js'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>

</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
.dislevelcss{float:left;margin:5px 0px 0px 8px;text-align:center;border:solid 1px #858585;padding:5px;}
.dislevelcss th{border-bottom:dashed 1px #858585;font-size:16px;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script>
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
    <script type='text/javascript'>
    	$(document).ready(shop_obj.products_edit_init);
    </script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/product_menubar.php');?>
    <div id="products" class="r_con_wrap">
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
      <form class="r_con_form" id="product_edit_form" method="post" action="?">

        <!--edit in 20160321-->

        <div class="rows">
          <label>网站发放比例</label>		  
          <span class="input price">
          <span>%</span>
          <input type="text" name="platForm_Income_Reward" value="<?php echo !empty($Shop_Commision_Reward_Arr['platForm_Income_Reward']) ? $Shop_Commision_Reward_Arr['platForm_Income_Reward'] : 0; ?>" class="form_input" size="5" maxlength="10" notnull />
          <span>(发放金额所占网站利润的百分比；小于100%大于0%；)</span>
          </span>
          <div class="clear"></div>
        </div> 
        
        <div class="rows">
          <label>爵位奖励比例</label>		  
          <span class="input price">
          <span>%</span>
          <input type="text" name="noBi_Reward" value="<?php echo !empty($Shop_Commision_Reward_Arr['noBi_Reward']) ? $Shop_Commision_Reward_Arr['noBi_Reward'] : 0; ?>" class="form_input" size="5" maxlength="10" notnull />
          <span>(所占发放比例的百分比)</span>
          </span>
          <div class="clear"></div>
        </div>

        <div class="rows">
          <label>区域代理比例</label>		  
          <span class="input price">
          <span>%</span>
          <input type="text" name="area_Proxy_Reward" value="<?php echo !empty($Shop_Commision_Reward_Arr['area_Proxy_Reward']) ? $Shop_Commision_Reward_Arr['area_Proxy_Reward'] : 0; ?>" class="form_input" size="5" maxlength="10" notnull />
          <span>(所占发放比例的百分比)</span>
          </span>
          <div class="clear"></div>
        </div>

        <div class="rows">
          <label>股东佣金比例</label>     
          <span class="input price">
          <span>%</span>
          <input type="text" name="sha_Reward" value="<?php echo !empty($Shop_Commision_Reward_Arr['sha_Reward']) ? $Shop_Commision_Reward_Arr['sha_Reward'] : 0; ?>" class="form_input" size="5" maxlength="10" notnull />
          <span>(所占发放比例的百分比)</span>
          </span>
          <div class="clear"></div>
        </div>
        
		<div class="rows">
          <label>佣金比例</label>		  
          <span class="input price">
          <span>%</span>
          <input type="text" name="commission_Reward" value="<?php echo !empty($Shop_Commision_Reward_Arr['commission_Reward']) ? $Shop_Commision_Reward_Arr['commission_Reward'] : 0; ?>" class="form_input" size="5" maxlength="10" notnull />
          <span>(佣金所占发放比例的百分比)</span>
          </span>
          <div class="clear"></div>
        </div> 
		
        <div class="rows">
        	<label>佣金返利<b class="red mousehand" id="allchange">（全部统一）</b></label>
            <span class="input">
			<?php
			$dislevel = $DB->Get("distribute_level","Level_ID,Users_ID,Level_Name","where Users_ID='".$_SESSION["Users_ID"]."'");
			while($dislevelarr=$DB->fetch_assoc()){
			  $dislevelarrs[] = $dislevelarr;
			  $disidarr[] = $dislevelarr['Level_ID'];
		  }	

		  $jsondisidarr = json_encode($disidarr,JSON_UNESCAPED_UNICODE);
		  $dislevelcont = count($dislevelarrs);	
			foreach($dislevelarrs as $key=>$disinfo){
			?>
			<div class="dislevelcss">
            	<table id="11" class="item_data_table" border="0" cellpadding="3" cellspacing="0">
				<tr><th><?=$disinfo['Level_Name']?></th></tr>
               		<?php 
						$arr = array('一','二','三','四','五','六','七','八','九','十');
						$level =  $dis_config['Dis_Self_Bonus']?$dis_config['Dis_Level']+1:$dis_config['Dis_Level'];						
						for($i=0;$i<$level;$i++){?>                        
						<tr>
							<td>
              <?php if($dis_config['Dis_Self_Bonus']==1 && $i==$dis_config['Dis_Level']){?>
              自销佣金
              <?php }else{?>                            
							<?php echo $arr[$i]?>级
              <?php }?>&nbsp;&nbsp; %
								<input id="dischange<?=$disinfo['Level_ID'].$i?>" name="Distribute[<?=$disinfo['Level_ID']?>][<?php echo $i;?>]" value="<?php echo !empty($Shop_Commision_Reward_Arr['Distribute'][$disinfo['Level_ID']][$i]) ? $Shop_Commision_Reward_Arr['Distribute'][$disinfo['Level_ID']][$i] : 0; ?>" class="form_input" size="5" maxlength="10" type="text">
								(佣金比例的百分比)
							</td>
						</tr>
					<?php }?>
                </table>
				</div>
			<?php } ?>
            </span>
            <div class="clear"></div>
        </div>
		<div class="rows disnone">
          <label>业务比例</label>  
          <span class="input price">
          <span>%</span>
          <input type="text" name="salesman_ratio" value="<?php if(!empty($rsProducts["salesman_ratio"])){ echo $rsProducts["salesman_ratio"]; }elseif(isset($Shop_Commision_Reward_Arr['salesman_ratio'])){ echo $Shop_Commision_Reward_Arr['salesman_ratio']; }else{ echo "0"; } ?>" class="form_input commision_config" data-value="<?php if(!empty($rsProducts["salesman_ratio"])){ echo $rsProducts["salesman_ratio"]; }elseif(isset($Shop_Commision_Reward_Arr['salesman_ratio'])){ echo $Shop_Commision_Reward_Arr['salesman_ratio']; }else{ echo "0"; } ?>" size="5" maxlength="10" notnull /><span>(业务提成所占发放比例的百分比)</span>
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows disnone">
          <label>各级业务提成比例</label>		  
          <span class="input">
              <table>
                  <tr>
                      <td>
                       一级业务<span>%</span>
          <input type="text" name="salesman_level_ratio[0]" value="<?php if(!empty($salesman_ratio_list[0])){ echo $salesman_ratio_list[0]; }elseif(isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][0])){ echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][0]; }else{ echo "0"; } ?>" class="form_input commision_config" data-value="<?php if(!empty($salesman_ratio_list["salesman_level_ratio"][0])){ echo $salesman_ratio_list["salesman_level_ratio"][0]; }elseif(isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][0])){ echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][0]; }else{ echo "0"; } ?>" size="5" maxlength="10" notnull />
          <span>(业务比例的百分比)</span>
                      </td>
                  </tr>
                  <tr>
                      <td>
                       二级业务<span>%</span>
          <input type="text" name="salesman_level_ratio[1]" value="<?php if(!empty($salesman_ratio_list[1])){ echo $salesman_ratio_list[1]; }elseif(isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][1])){ echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][1]; }else{ echo "0"; } ?>" class="form_input commision_config" data-value="<?php if(!empty($salesman_ratio_list["salesman_level_ratio"][1])){ echo $salesman_ratio_list["salesman_level_ratio"][1]; }elseif(isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][1])){ echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][1]; }else{ echo "0"; } ?>" size="5" maxlength="10" notnull />
          <span>(业务比例的百分比)</span>
                      </td>
                  </tr>
                  <tr>
                      <td>
                       三级业务<span>%</span>
          <input type="text" name="salesman_level_ratio[2]" value="<?php if(!empty($salesman_ratio_list[2])){ echo $salesman_ratio_list[2]; }elseif(isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][2])){ echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][2]; }else{ echo "0"; } ?>" class="form_input commision_config" data-value="<?php if(!empty($salesman_ratio_list["salesman_level_ratio"][2])){ echo $salesman_ratio_list["salesman_level_ratio"][2]; }elseif(isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][2])){ echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][2]; }else{ echo "0"; } ?>" size="5" maxlength="10" notnull />
          <span>(业务比例的百分比)</span>
                      </td>
                  </tr>
              </table>
         
          </span>
           
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" name="submit_button" value="提交保存" /></span>
          <div class="clear"></div>
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript">
/*edit in 20160321*/
var level = <?=$level?>;
var dislevelcont = <?=$dislevelcont?>;
var disidarr = <?=$jsondisidarr?>;
var fistarr = new Array();
$("#allchange").click(function(){
for(i=0;i<dislevelcont;i++){
	if(i == 0){
		for(j=0;j<level;j++){
		fistarr[j] = $("#dischange"+disidarr[i]+j).val();
		}	
	}else{
		for(j=0;j<level;j++){
		$("#dischange"+disidarr[i]+j).val(fistarr[j]);
		}
	}
}
});
$('.btn_green').on('click','',function(){
			var retest = /^(0|[1-9][0-9]*)$/;
			var platForm_Income_Reward = Number($("input[name='platForm_Income_Reward']").val());
			if(platForm_Income_Reward > 100 || platForm_Income_Reward < 0){
					global_obj.win_alert("发放金额应小于100%大于0%");
					return false;
				}	
			var noBi_Reward = Number($("input[name='noBi_Reward']").val());
			var area_Proxy_Reward = Number($("input[name=area_Proxy_Reward]").val());
			var sha_Reward = Number($("input[name='sha_Reward']").val());
			var commission_Reward = Number($("input[name=commission_Reward]").val());
			var salesman_ratio = Number($("input[name='salesman_ratio']").val());			
			var rewardall = noBi_Reward + area_Proxy_Reward + sha_Reward + commission_Reward + salesman_ratio;
			if(!retest.test(noBi_Reward) || !retest.test(area_Proxy_Reward) || !retest.test(sha_Reward) || !retest.test(commission_Reward) || !retest.test(salesman_ratio) || !retest.test(platForm_Income_Reward)){
					global_obj.win_alert("比例应为整数，您填写有误！");
					return false;
				}
			if(rewardall > 100 || rewardall < 0){
					global_obj.win_alert("爵位奖励比例+区域代理比例+股东佣金比例+佣金比例+业务比例不能超过100%且不能小于0！");
					return false;
				}
			var salesman_level = 0;
			for(var i=0; i<3; i++){
				salesman_level += Number($("input[name='salesman_level_ratio["+i+"]']").val());
			}
			if ( salesman_level > 100 || salesman_level < 0 ) {
					global_obj.win_alert("一级业务+二级业务+三级业务比例不能超过100%且不能小于0！");
					return false;
				}
			$('#product_add_form').submit();
		});
</script>
</body>
</html>