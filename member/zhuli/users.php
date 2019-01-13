<?php

if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}

if(isset($_GET["action"])){
	if($_GET["action"]=="del"){		
		$Flag=$DB->Del("zhuli_act","Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$_GET["User_ID"]);
		if($Flag)
		{
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else
		{
			echo '<script language="javascript">alert("删除失败");history.back();</script>';
		}
		exit;
	}
}

require_once('vertify.php');
$condition = "where Users_ID='".$_SESSION["Users_ID"]."' order by Act_Score desc";
$jcurid = 6;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/zhuli.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/zhuli.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/pintuan/marketing_menubar.php');?>
    <div id="users" class="r_con_wrap">
      <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="8%" nowrap="nowrap">序号</td>
            <td width="22%" nowrap="nowrap">头像</td>
            <td width="15%" nowrap="nowrap">昵称</td>
            <td width="12%" nowrap="nowrap">助力指数</td>
			<td width="13%" nowrap="nowrap">好友数量</td>
            <td width="20%" nowrap="nowrap">参与时间</td>
            <td width="10%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
          <?php
			  $lists = array();
			  $DB->getPage("zhuli_act","*",$condition,10);			  
			  while($r=$DB->fetch_assoc()){
				  $lists[] = $r;
			  }
			  foreach($lists as $k=>$v){
				$user = $DB->GetRs("user","User_HeadImg,User_NickName","where User_ID=".$v["User_ID"]);
				$v["User_HeadImg"] = $user["User_HeadImg"];
				$v["User_NickName"] = $user["User_NickName"];
				$count = $DB->GetRs("zhuli_record","count(*) as num","where Act_ID=".$v["Act_ID"]);
				$v["friend"] = $count["num"];
		  ?>
          <tr>
            <td nowrap="nowrap"><?php echo $k+1;?></td>
            <td nowrap="nowrap"><img src="<?php echo $v["User_HeadImg"] ? $v["User_HeadImg"] : '/static/api/zhuli/images/user.jpg';?>" width="50" height="50" /></td>
            <td nowrap="nowrap"><?php echo $v["User_NickName"] ? $v["User_NickName"] : '微友助力';?></td>
            <td nowrap="nowrap"><?php echo $v["Act_Score"];?></td>
            <td nowrap="nowrap"><?php echo $v["friend"];?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$v["Act_Time"]) ?></td>
            <td class="last" nowrap="nowrap"><a href="friend.php?ActID=<?php echo $v["Act_ID"] ?>"><img src="/static/member/images/ico/view.gif" align="absmiddle" alt="查看好友" /></a><a href="?action=del&User_ID=<?php echo $v["User_ID"] ?>" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" alt="删除" /></a></td>
          </tr>
          <?php }?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
</div>
</body>
</html>