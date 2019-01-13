<div class="r_nav">
      <ul id="menuset">
        <li id="sett1"><a href="/biz/account/account.php">商家资料</a></li>
        <li id="sett2"> <a href="/biz/account/account_edit.php">修改资料</a></li>
        <li id="sett3"> <a href="/biz/account/address_edit.php">收货地址</a></li>
        <li id="sett4"> <a href="/biz/account/bind_user.php">绑定会员</a></li>
        <li id="sett5"> <a href="/biz/account/account_password.php">修改密码</a></li>
        <li id="sett6"><a href="/biz/account/account_payconfig.php">结算配置</a></li>
      </ul>
    </div>
	<script type="text/javascript">	
	var jcurid = '<?=$jcurid?>';	
	$("#menuset li").each(function() {    
    $(this).removeClass();
            });
    $("#sett"+jcurid).addClass("cur");	
</script>