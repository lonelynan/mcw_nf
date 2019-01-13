<div class="r_nav">
      <ul id="menuset">
        <!--<li id="sett1"><a href="/member/pintuan/config.php">拼团设置</a>
          <dl>
            <dd id="sub11"><a href="/member/pintuan/home.php">首页设置</a></dd>
			<dd id="sub12"><a href="/member/pintuan/products.php">拼团管理</a></dd>
			<dd id="sub13"><a href="/member/pintuan/cate.php">拼团分类管理</a></dd>
			<dd id="sub14"><a href="/member/pintuan/orders.php">订单管理</a></dd>
			<dd id="sub15"><a href="/member/pintuan/comment.php">评论管理</a></dd>
			<dd id="sub16"><a href="/member/pintuan/config.php">计划任务配置</a></dd>
			<dd id="sub17"><a href="/member/pintuan/aword.php">抽奖统计</a></dd>
          </dl>
        </li>-->

          <li id="sett1"><a href="/member/pintuan/orders.php">拼团管理</a>
              <dl>
                  <dd id="sub14"><a href="/member/pintuan/orders.php">订单管理</a></dd>
                  <dd id="sub15"><a href="/member/pintuan/comment.php">评论管理</a></dd>
              </dl>
          </li>
        
        <li id="sett3"> <a href="/member/kanjia/config.php">微砍价设置</a>
		<dl>
            <dd id="sub31"><a href="/member/kanjia/activity_list.php">活动管理</a></dd>
            <dd id="sub32"><a href="/member/kanjia/orders.php">订单管理</a></dd>
            <dd id="sub33"><a href="/member/kanjia/commit.php">评论管理</a></dd>			
          </dl>
		</li>
        <li id="sett4"> <a href="/member/votes/config.php">投票设置</a>
          <dl>            
            <dd id="sub41"><a href="/member/votes/votemanage.php">投票管理</a></dd>            
          </dl>
        </li>
      <!--  <li id="sett5"> <a href="/member/zhongchou/config.php">众筹设置</a>
          <dl>
            <dd id="sub51"><a href="/member/zhongchou/project.php">项目管理</a></dd>            
          </dl>
        </li>-->
        <li id="sett6"><a href="/member/zhuli/config.php">微助力设置</a>
		<dl>            
            <dd id="sub61"><a href="/member/zhuli/rules.php">活动规则</a></dd>
            <dd id="sub62"><a href="/member/zhuli/awordrules.php">兑奖规则</a></dd>
			<dd id="sub63"><a href="/member/zhuli/prize.php">奖品设置</a></dd>
			<dd id="sub64"><a href="/member/zhuli/users.php">用户列表</a></dd>
          </dl>
		</li>
        <li id="sett7"> <a href="/member/scratch/config.php">刮刮卡设置</a>
          <dl>
			<dd id="sub71"><a href="/member/scratch/index.php">刮刮卡</a></dd>
            <dd id="sub72"><a href="/member/fruit/config.php">水果达人设置</a></dd>
			<dd id="sub73"><a href="/member/fruit/index.php">水果达人</a></dd>
			<dd id="sub74"><a href="/member/turntable/config.php">欢乐大转盘设置</a></dd>
			<dd id="sub75"><a href="/member/turntable/index.php">欢乐大转盘</a></dd>
			<dd id="sub76"><a href="/member/battle/config.php">一战到底设置</a></dd>
			<dd id="sub77"><a href="/member/battle/exam.php">题库管理</a></dd>
			<dd id="sub78"><a href="/member/battle/battle.php">活动管理</a></dd>
			<dd id="sub79"><a href="/member/battle/battle_user.php">用户列表</a></dd>
			<dd id="sub7a"><a href="/member/guanggao/config.php">广告位管理</a></dd>
			<dd id="sub7b"><a href="/member/guanggao/ad_list.php">广告列表</a></dd>
          </dl>
        </li>
		<!--<li id="sett8"><a href="/member/games/config.php">游戏设置</a>
          <dl>
            <dd id="sub81"><a href="/member/games/lists.php">游戏管理</a></dd>
          </dl>
        </li>-->
      </ul>
    </div>
<script type="text/javascript">	
	var jcurid = '<?php echo isset($jcurid) ? $jcurid : 44; ?>';
	var subid = '<?php echo isset($subid) ? $subid : 100; ?>';
	var subidst = <?php echo isset($subidst) ? $subidst : 100; ?>;
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
	if(subidst != 100){
		var changefour = '';
		switch(subidst){	       
		   case 31:changefour = '新建活动';break;		   
	       default:changefour = '一级代理管理';break;
	   }				
		$("#sett"+jcurid).children("a").html(changefour);		
	}
	$("#menuset dd a").click(function(e) {
		var cliid = $(this).parent().attr("id");
		var surl = $(this).attr("href");
		var xurl = surl + "?cliid=" + cliid;
		if(cliid == 'sub16'){		
		xurl = surl + "?cfgPay=1&cliid=" + cliid;
	}
        $(this).attr("href",xurl);
    });
</script>