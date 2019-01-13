<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/page.class.php');

if(isset($_GET["action"]))
{
	$action=empty($_GET["action"])?"":$_GET["action"];
	if($action=="del")
	{
		$Flag=true;
		$msg="";
		$Del=$DB->Del("user_gift_orders","Users_ID='".$_SESSION["Users_ID"]."' and Orders_ID=".$_GET["OrderId"]);
		$Flag=$Flag&&$Del;
		if($Flag){
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			echo '<script language="javascript">alert("删除失败");history.go(-1);</script>';
		}
	}
}
$jcurid = 5;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>好分销</title>
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
    <link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/user.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/user/user_menubar.php');?>
    <div id="gift_orders" class="r_con_wrap"> 
      <script language="javascript">$(document).ready(user_obj.gift_orders_init);</script>
      <form class="search" method="get" action="">
        关键词：
        <input type="text" name="Keyword" value="" class="form_input" size="15" />
        <input type="submit" class="search_btn" value="搜索" />
        <input type="hidden" name="m" value="user" />
        <input type="hidden" name="a" value="gift_orders" />
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="8%" nowrap="nowrap">订单号</td>
            <td width="20%" nowrap="nowrap">名称</td>            
            <td width="20%" nowrap="nowrap">图片</td>
			<td width="20%" nowrap="nowrap">会员号</td>
            <td width="10%" nowrap="nowrap">姓名</td>
            <td width="10%" nowrap="nowrap">手机</td>
            <td width="10%" nowrap="nowrap">时间</td>
            <td width="10%" nowrap="nowrap">状态</td>
            <td width="10%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
        <?php
$p = isset($_GET['p']) ? intval($_GET['p']) : 1;
if ($p < 1) $p = 1;
$user_id = $_SESSION['Users_ID'];
//print_R($user_id);
$pagesize = 5;        
$Keyword = isset($_GET['Keyword']) ? $_GET['Keyword'] : '';
$condition = " WHERE o.Users_ID = '$user_id'"; 
if ($Keyword) {
  //手机号搜索
  if (preg_match("/^1[34578]\d{9}$/", $Keyword)) {
    $condition .= " WHERE Address_Mobile='" . trim($Keyword) . "'";
  } else {
    $condition .= " WHERE g.Gift_Name LIKE '%" . $Keyword . "%'";
  }
}

//总记录数量
$sql = "SELECT COUNT(*) AS total FROM user_gift_orders o LEFT JOIN user_gift g ON o.Gift_ID = g.Gift_ID $condition";
$query = $DB->query($sql);
$row = $DB->fetch_assoc($query);
$total = $row['total'];

//分页设置
$Page = new Page();
$Page->setvar(array('Keyword' => $Keyword));
$Page->set($pagesize, $total, $p);


//分页内容获取

$sql = "SELECT o.*,g.Gift_Name, g.Gift_ImgPath FROM user_gift_orders o LEFT JOIN user_gift g ON o.Gift_ID = g.Gift_ID $condition ORDER BY Orders_ID DESC";
$sql .= ' LIMIT ' . $Page->limit();
$query = $DB->query($sql);

if ($DB->num_rows($query) > 0) {

		while($rsGift=$DB->fetch_assoc()){
			
			$lists[] = $rsGift;
		}

		foreach($lists as $k=>$v){
			$rsuserno = $DB->GetRs('user','User_No','where User_ID='.$v['User_ID']);
?>
          <tr>
            <td nowrap="nowrap"><?php echo $v['Orders_ID']?></td>
            <td><?php echo $v['Gift_Name'];?></td>
            <td nowrap="nowrap"><img src="<?php echo $v['Gift_ImgPath']?>" class="img" /></a></td>
            <td nowrap="nowrap"><?php echo $rsuserno['User_No'];?></td>
            <td nowrap="nowrap"><?php echo $v['Address_Name'];?></td>
            <td nowrap="nowrap"><?php echo $v['Address_Mobile'];?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$v['Orders_FinishTime']);?></td>
            <td nowrap="nowrap"> <?php echo $v['Orders_Status'] ? '已领取' : '未领取';?> </td>
            <td class="last" nowrap="nowrap"><a href="gift_orders_view.php?OrderId=<?php echo $v['Orders_ID']?>&page=<?php echo $p;?>"><img src="/static/member/images/ico/view.gif" align="absmiddle" alt="查看" /></a> <a href="?action=del&OrderId=<?php echo $v['Orders_ID']?>&page=<?php echo $p;?>" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" alt="删除" /></a></td>
          </tr>
          
          <?php 
 }
		  }?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <center><?php $Page->output();?></center>
    </div>
  </div>
</div>
</body>
</html>