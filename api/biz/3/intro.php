<?php
require_once($rsBiz["Skin_ID"].'/top.php');

$applyInfo = $DB->getRs('biz_apply','authtype','where Biz_ID = '.$BizID);
?>
<style type="text/css">
body{background:#f5f5f5}
</style>
 <div id="intro">
   <div class="company_info">
      <img src="<?php echo $rsBiz["Biz_Logo"];?>" />
      <span><?php echo $rsBiz["Biz_Name"];?></span>
      <div class="clear"></div>
   </div>
   
    <div class="company_info">
      <img style="border: 0px" src="/static/biz/images/<?php echo ($applyInfo['authtype'] ==1)?'qiye':"geren";?>.png" />
      <span><?php echo ($applyInfo['authtype'] ==1)?'企业认证商家':"个人认证商家";?></span>
      <div class="clear"></div>
   </div>  
     
   <div class="introduce"><?php echo $rsBiz["Biz_Introduce"];?></div>
 </div>
<?php
require_once($rsBiz["Skin_ID"].'/footer.php');
?>