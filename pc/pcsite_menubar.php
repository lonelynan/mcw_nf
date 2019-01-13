<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/pc.php?m=member&c=pc_diy&a=index_block&UsersID=<?=$_SESSION['Users_ID']?>">首页设置</a></li>
        <li id="sett2"> <a href="/pc.php?m=member&c=pc_diy&a=index_focus&UsersID=<?=$_SESSION['Users_ID']?>">幻灯片管理</a></li>
		<li id="sett3"> <a href="/pc.php?m=member&c=pc_setting&a=index&UsersID=<?=$_SESSION['Users_ID']?>">基本设置</a></li>
		<li id="sett4"> <a href="/pc.php?m=member&c=pc_setting&a=share_setting&UsersID=<?=$_SESSION['Users_ID']?>">分享设置</a></li>
		<li id="sett5"> <a href="/pc.php?m=member&c=pc_setting&a=menu_index&UsersID=<?=$_SESSION['Users_ID']?>">导航设置</a></li>
      </ul>
    </div>
<script type="text/javascript">	
	var jcurid = <?php echo isset($jcurid) ? $jcurid : 44; ?>;
	var subid = <?php echo isset($subid) ? $subid : 100; ?>;
	$("#menuset li").each(function() {    
    $(this).removeClass();
            }); 
	$("#sett"+jcurid).addClass("cur");	
	if(subid != 100){
		var changeone = $("#"+subid).children().html();
		var changetwo = $("#"+subid).parent().parent().children("a").html();
		var changethree = $("#"+subid).parent().parent().children("a").attr("href");
		$("#"+subid).parent().parent().children("a").html(changeone);
		$("#"+subid).children("a").html(changetwo);
		$("#"+subid).children("a").attr("href",changethree);
	}
	$("#menuset dd a").click(function(e) {
		var cliid = $(this).parent().attr("id");
		var surl = $(this).attr("href");
		var xurl = surl + "?cliid='" + cliid+"'";
		if(cliid == 'sub16'){		
		xurl = surl + "?cfgPay=1&cliid='" + cliid+"'";
	}
        $(this).attr("href",xurl);
    });
</script>