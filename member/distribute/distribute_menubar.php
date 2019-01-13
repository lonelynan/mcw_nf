<?php
$rsConfigmenu = Dis_Config::find($_SESSION["Users_ID"]);
?>
<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/member/distribute/setting.php">基础设置</a>
		<dl>            
			<dd id="sub11"><a href="/member/distribute/setting_withdraw.php">提现设置</a></dd>
			<dd id="sub12"><a href="/member/distribute/setting_other.php">其他设置</a></dd>
			<dd id="sub13"><a href="/member/distribute/setting_protitle.php">爵位设置</a></dd>
			<dd id="sub14"><a href="/member/distribute/setting_distribute.php">分销首页设置</a></dd>
            <dd id="sub15"><a href="/member/distribute/dismanual_orders.php">手动申请审核</a></dd>
          </dl>
		</li>
        <li id="sett2"> <a href="/member/distribute/account.php">分销账号管理</a></li>
        <li id="sett3"> <a href="/member/distribute/record.php">分销记录</a></li>
        <li id="sett4"> <a href="/member/distribute/agent.php">代理获奖记录</a>
		<dl>            
            <dd id="sub41"><a href="/member/distribute/agent_list.php">地区代理列表</a></dd>
			<?php if($rsConfigmenu->Dis_Agent_Type == 1):?>
			<dd id="sub42"><a href="/member/distribute/agent_orders.php">地区代理申请列表</a></dd>
			<?php endif;?>
          </dl>
		</li>
        <li id="sett5"> <a href="/member/distribute/shareholder.php">股东分红记录</a>
          <dl>            
            <dd id="sub51"><a href="/member/distribute/sha_list.php">股东列表</a></dd>
			<?php if($rsConfigmenu->Sha_Agent_Type == 1):?>
			<dd id="sub52"><a href="/member/distribute/sha_orders.php">股东申请列表</a></dd>
			<?php endif;?>
          </dl>
        </li>
        <li id="sett6"><a href="/member/distribute/withdraw.php">提现记录</a>
		<dl>
            <dd id="sub61"><a href="/member/distribute/withdraw_method.php">提现方法管理</a></dd>
			<!--<dd id="sub62"><a href="/member/distribute/withdraw_detail.php">提现明细</a></dd>-->
          </dl>
		</li>
		<li id="sett7"><a href="/member/distribute/get_product.php">商品管理</a>
		<dl>
			<dd id="sub71"><a href="/member/distribute/get_product_add.php">添加商品</a></dd>
            <dd id="sub72"><a href="/member/distribute/get_product_orders.php">领取订单管理</a></dd>
        </dl>
		</li>
		<li id="sett8"><a href="/member/distribute/order_history.php">订单记录</a>        
          <dl>
            <dd id="sub81"><a href="/member/distribute/commsion.php">佣金记录</a></dd>
          </dl>
        </li>		
      </ul>
    </div>
	<script type="text/javascript">	
	var jcurid = '<?=$jcurid?>';
	var subid = '<?php echo isset($subid) ? $subid : 100; ?>';
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
		var xurl = surl + "?cliid=" + cliid;
        $(this).attr("href",xurl);
    });
</script>