<?php
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$rsConfigmenu = Dis_Config::find($_SESSION["Users_ID"]);
$dis_level = get_dis_level($DB,$_SESSION['Users_ID']);
if(isset($_GET["action"]))
{
	$action=empty($_GET["action"])?"":$_GET["action"];
	if($action=="del")
	{
		//开始事务定义
		$Flag=true;
		$msg="";
		mysql_query("begin");
		$Flag=$Flag&&$DB->Del("user_get_product","Users_ID='".$_SESSION["Users_ID"]."' and Get_ID=".$_GET["GetID"]);
		if($Flag){
			mysql_query("commit");
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			mysql_query("roolback");
			echo '<script language="javascript">alert("保存失败");history.go(-1);</script>';
		}
	}
}
$jcurid = 7;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>微易宝</title>
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
   <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/distribute/distribute_menubar.php');?>
		<div id="Peas" class="r_con_wrap"> 
		  <script language="javascript">$(document).ready(user_obj.Peas_list_init);</script>
		  <div class="control_btn"> <a href="get_product_add.php" class="btn_green btn_w_120">添加商品</a> </div>
		  <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
			  <tr>
				<td width="8%" nowrap="nowrap">序号</td>
				<td width="20%" nowrap="nowrap">名称</td>
				<td width="20%" nowrap="nowrap">图片</td>
				<td width="10%" nowrap="nowrap">分销商级别</td>
				<td width="10%" nowrap="nowrap">剩余数量</td>
				<td width="10%" nowrap="nowrap">需要物流</td>
				<td width="10%" nowrap="nowrap">时间</td>
				<td width="10%" nowrap="nowrap" class="last">操作</td>
			  </tr>
			</thead>
			<tbody>
			  <?php $DB->getPage("user_get_product","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Get_CreateTime desc",$pageSize=10);
			$i=1;
			while($rsGet=$DB->fetch_assoc()){?>
			  <tr>
				<td nowrap="nowrap"><?php echo $pageSize*($DB->pageNo-1)+$i; ?></td>
				<td><?php echo $rsGet['Get_Name'] ?></td>
				<td nowrap="nowrap"><a href="<?php echo $rsGet['Get_ImgPath'] ?>" target="_blank"><img style="width:100px;height:100px;" src="<?php echo $rsGet['Get_ImgPath'] ?>" class="img" /></a></td>
				<td nowrap="nowrap"><?php 
					foreach($dis_level as $key=>$value){
						if($value['Level_ID'] == $rsGet['Get_Level_ID']){
							echo $value['Level_Name'];
							
						}
					} ?>
				</td>
				<td nowrap="nowrap"><?php echo $rsGet['Get_Qty'] ?></td>
				 <td nowrap="nowrap"><?php echo empty($rsGet['Get_Shipping'])?'否':'是' ?></td>
				<td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsGet["Get_CreateTime"]) ?></td>
				<td class="last" nowrap="nowrap"><a href="get_product_edit.php?GetID=<?php echo $rsGet['Get_ID'] ?>"><img src="/static/member/images/ico/mod.gif" align="absmiddle" alt="修改" /></a> <a href="get_product.php?action=del&GetID=<?php echo $rsGet['Get_ID'] ?>" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" alt="删除" /></a></td>
			  </tr>
			  <?php $i++;
			  }?>
			</tbody>
		  </table>
		  <div class="blank20"></div>
		  <?php $DB->showPage(); ?>
			
		</div>
	</div>
</div>
</body>
</html>
