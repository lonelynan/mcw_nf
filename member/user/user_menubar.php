<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/user/config.php">基本设置</a>
          <dl>
            <dd id="sub11"><a href="/member/user/lbs.php">一键导航设置</a></dd>
          </dl>
        </li>
        <li id="sett2"> <a href="/member/user/user_list.php">会员管理</a>
          <dl>
            <dd id="sub21"><a href="/member/user/user_level.php">会员等级设置</a></dd>
            <dd id="sub22"><a href="/member/user/user_profile.php">会员注册资料</a></dd>          
          </dl>
        </li>
        <li id="sett5"> <a href="/member/user/gift_orders.php">礼品兑换</a>
          <dl>
            <dd id="sub51"><a href="/member/user/gift.php">礼品管理</a></dd>            
          </dl>
        </li>
        <li id="sett6"><a href="/member/user/business_password.php">商家密码设置</a></li>
        <li id="sett7"><a href="/member/user/message.php">消息发布管理</a></li>
		<!--<li id="sett8"><a href="/member/user/alluseryielist.php">收益明细</a>
          <dl>
            <dd id="sub81"><a href="/member/user/config.php">收益率设置</a></dd>
          </dl>
        </li>-->
      </ul>
    </div>
<script type="text/javascript">	
	var jcurid = '<?=$jcurid?>';
	var subid = '<?php echo isset($subid) ? $subid : 100; ?>';
	$("#menuset li").each(function() {    
    $(this).removeClass();
            });    
	if(subid == 'sub81'){		
		$("#sett8").addClass("cur");
	}else{
		$("#sett"+jcurid).addClass("cur");
	}
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
		var xurl = surl + "?cliid=" + cliid;
        $(this).attr("href",xurl);
    });
</script>