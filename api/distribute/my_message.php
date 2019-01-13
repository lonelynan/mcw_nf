<?php
require_once('global.php');
$UserID = intval($_GET['UserID']);//收件人的id
if (!$UserID) 
{
	exit('非法请求!');
}

$myUserID = $rsUser['User_ID'];//个人登录会员的id(当前的发件人)
$message_list = array();
$message_Obj = $DB->Get('distribute_account_message', '*', ' WHERE (`User_ID` = '.$UserID.' AND `Receiver_User_ID` = '.$myUserID.') OR (`User_ID` = ' .$myUserID. ' AND `Receiver_User_ID` = ' .$UserID. ') ORDER BY `Mess_CreateTime` ASC'); 

while ($row = $DB->fetch_assoc()) 
{
	$message_list[] = $row;
}
$first_message_man = 0;
$last_message_man = 0;
if (!empty($message_list)) 
{
	$first_message_man = $message_list[0]['User_ID']; //首次发消息的消息人
	$last_message_man = $message_list[count($message_list)-1]['User_ID']; //最后发消息的人
}

$userInfoList = array();
$userObj = $DB->Get('user', 'User_ID,User_Name,User_HeadImg,User_NickName', 'WHERE `User_ID` IN('.implode(',', array($UserID, $myUserID)).')');//用户信息
while ($row = $DB->fetch_assoc()) 
{
	$userInfoList[$row['User_ID']] = $row;
}

$MyMessagePosition = 'l'; //我发送的消息位置默认应该在左侧
$MyHeadImg = $userInfoList[$myUserID]['User_HeadImg'];
if (!empty($message_list)) 
{
	$userLimit = array($UserID => '收件人', $myUserID => '我的编号');
	$first_message_headImg = $userInfoList[$first_message_man]['User_HeadImg'];
	unset($userLimit[$first_message_man]);
	$sliceId = array_keys($userLimit);
	$last_message_headImg = $userInfoList[$sliceId[0]]['User_HeadImg'];
	
	$MyHeadImg = $first_message_headImg;
	if($first_message_man != $myUserID) { $MyMessagePosition = 'r'; $MyHeadImg = $last_message_headImg; } //如果我不是作为第一次发消息的人我的消息应该显示在右侧
}




$header_title = "和 “ ".$userInfoList[$UserID]['User_NickName']." ” 的对话";
require_once('header.php');
?>

<body>
<style type="text/css">
	.message_box { padding: 20px 10px; margin-bottom: 82px; }
	.message_list { display: block; overflow: hidden; max-width: 80%; padding: 10px 0; }
	.touxiang { display: inline-block; overflow: hidden; height: 40px; line-height: 40px; float: left; }
	.touxiang img { width: 40px; height: 40px; border-radius: 5px; }
	.rtouxiang { float: right; }
	.message_list .message_content { text-align: left; background: #FFF; display: block; overflow: hidden; min-height: 40px; float: left; padding: 5px; border-radius: 5px; border: 1px solid #ddd;max-width: 75%; }
	.r .message_content { text-align: left; }
	.r .time { text-align: right; margin-right: 10px; }
	.l .message_content { margin-left: 10px; }
	.l { float: left; }
	.r { float: right; }
	.split { display: block; clear: both; }
	.sendMessageBox { display: block; position: fixed; bottom: 0; left: 0; padding: 10px 0px; background: #FFF; width: 100%; }
    .inputBox { width: 79%; float: left; margin-left:1% }
    .inputBox textarea { width: 95%; height: 40px; border:1px #dfdfdf solid; border-radius:8px; }
    .sendButton {width: 19%; float: right; margin-right:1%}
    .sendButton .send { width: 100%; border: none; height: 42px; line-height: 40px; background:#F60; color:#FFF; border-radius:8px; }
	span.status { color: red; font-size: 14px; }
</style>  
<link href="/static/api/distribute/css/detaillist.css?t=<?php echo time();?>" rel="stylesheet">

<header class="bar bar-nav">
  <a href="javascript:history.back()" class="fa fa-2x fa-chevron-left grey pull-left"></a>
  <a href="/api/<?=$UsersID?>/distribute/?love" class="fa fa-2x fa-sitemap grey pull-right"></a>
  <h1 class="title">和 “ <?php echo $userInfoList[$UserID]['User_NickName']; ?> ” 的对话</h1>
</header>

<div class="message_box">
	<?php foreach ($message_list as $k => $v) : ?>
	<div class="message_list <?php if($first_message_man == $v['User_ID']) { echo 'l'; } else { echo 'r'; } ?>">
		<div class="touxiang <?php if($first_message_man != $v['User_ID']) { echo 'rtouxiang'; } ?>"><img src="<?php echo $userInfoList[$v['User_ID']]['User_HeadImg']; ?>"></div>
		<div class="message_content"><span class="arrow"></span><p class="time"><?php echo date('Y-m-d H:i:s', $v['Mess_CreateTime']); ?> <?php if ($myUserID != $last_message_man && $v['Mess_Status'] == 0) : ?><span class="status">[未读]</span><?php endif; ?></p><?php echo $v['Mess_Content']; ?></div>
	</div>
	<div class="split"></div>
	<?php endforeach; ?>

	<div class="messagesplit"></div>
</div>

<div class="sendMessageBox">
	<div class="inputBox">
		<textarea class="messageboxContent"></textarea>
	</div>
	<div class="sendButton">
		<button class="send">发送消息</button>
	</div>
</div>
<?php
if ($last_message_man != $myUserID) 
{
	$DB->Set('distribute_account_message', array('Mess_Status' => 1), ' WHERE `User_ID` = '.$UserID.' AND `Receiver_User_ID` = '.$myUserID.' OR `User_ID` = ' .$myUserID. ' AND `Receiver_User_ID` = ' .$UserID);
}
?>

<script type="text/javascript">
$(document).ready(function(){
	$('.send').click(function(){
		var messageboxContent = $('.messageboxContent').val();
		var UsersID = "<?=$UsersID?>";
		var MyUserID = "<?=$myUserID?>";
		var ajax_url  = "<?=distribute_url()?>ajax/";
		var MyMessagePosition = "<?=$MyMessagePosition?>";
		var MyHeadImg = "<?=$MyHeadImg?>";
		var UserID = "<?=$UserID?>";
		var html = "";

		if (messageboxContent.length <= 0) { global_obj.win_alert('请填写消息内容！', function() {}); return false; };

		$.post(ajax_url, {action:'sendMessageContent', UsersID:UsersID, UserID:MyUserID, Receiver_User_ID:UserID, Mess_Content:messageboxContent}, function(data){
			if (!data.status) { global_obj.win_alert('消息发送失败！', function() {}); return false; };
			html += "<div class=\"message_list "+MyMessagePosition+"\">";
			html += "<div class=\"touxiang "+MyMessagePosition+"touxiang\"><img src=\""+MyHeadImg+"\"><\/div>";
			html += "<div class=\"message_content\"><p class=\"time\">"+data.time+"</p>" +messageboxContent+ "<\/div>";
			html += "<\/div><div class=\"split\"><\/div>";
			$('.messagesplit').before(html);
			$('.messageboxContent').val('');
			$('body').animate({scrollTop: $(document).height()}, 300);
		},'JSON');
	});
});
</script>
</body>
</html>

