<div id="global_support_point"></div><div id="global_support"><div class="bg"></div><?php echo $Copyright;?></div>
<!--/*edit在线客服20160419--start--*/--> 
<?php
if($kfConfig){
    if($kfConfig['kftype']==1){
        $qq = $kfConfig["qq"];
        $qq_icon = $kfConfig["qq_icon"];
        $qq_postion = $kfConfig["qq_postion"];
        ?>        
        <a style="position:fixed;cursor:pointer;<?php echo $qq_postion?>:0px;top:50%;" target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=<?= $qq;?>&site=qq&menu=yes"><img border="0" src="/static/kf/<?php echo $qq_icon?>.gif" alt="点击这里给我发消息" title="点击这里给我发消息"/ ></a>
        <?php   
    }else{
        echo htmlspecialchars_decode($kfConfig["KF_Code"],ENT_QUOTES);
    }
}
?>
<!--/*edit在线客服20160419--end--*/-->
<img src='/static/api/images/cover_img/web.jpg' class='shareimg'/>
<?php if(!empty($share_config)){?>
	<script language="javascript">
		var share_config = {
		   appId:"<?php echo $share_config["appId"];?>",   
		   timestamp:<?php echo $share_config["timestamp"];?>,
		   nonceStr:"<?php echo $share_config["noncestr"];?>",
		   url:"<?php echo $share_config["url"];?>",
		   signature:"<?php echo $share_config["signature"];?>",
		   title:"<?php echo $share_config["title"];?>",
		   desc:"<?php echo str_replace(array("\r\n", "\r", "\n"), "", $share_config["desc"]);?>",
		   img_url:"<?php echo $share_config["img"];?>",
		   link:"<?php echo $share_config["link"];?>"
		};
		
		$(document).ready(global_obj.share_init_config);
	</script>
<?php }?>
</body>
</html>