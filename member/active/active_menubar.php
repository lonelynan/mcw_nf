<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/active/active_list.php">活动列表</a></li>
        <li id="sett2"> <a href="/member/active/active_add.php">发起活动</a></li>        
      </ul>
    </div>
<script type="text/javascript">	
	var jcurid = '<?php echo isset($jcurid) ? $jcurid : 44; ?>';
	var subidst = <?php echo isset($subidst) ? $subidst : 100; ?>;
	$("#menuset li").each(function() {    
    $(this).removeClass();
            }); 
	$("#sett"+jcurid).addClass("cur");
	if(subidst != 100){
		var changefour = '';
		switch(subidst){
	       case 11:changefour = '活动修改';break;	       
	       default:changefour = '非法操作';break;
	   }				
		$("#sett"+jcurid).children("a").html(changefour);		
	}
</script>