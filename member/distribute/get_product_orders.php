<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}
$rsConfigmenu = Dis_Config::find($_SESSION["Users_ID"]);
if(isset($_GET["action"]))
{
	$action=empty($_GET["action"])?"":$_GET["action"];
	if($action=="del")
	{
		$Flag=true;
		$msg="";
		$Del=$DB->Del("user_get_orders","Users_ID='".$_SESSION["Users_ID"]."' and Orders_ID=".$_GET["OrderId"]);
		$Flag=$Flag&&$Del;
		if($Flag){
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			echo '<script language="javascript">alert("保存失败");history.go(-1);</script>';
		}
	}
}
$condition = "where Users_ID='".$_SESSION["Users_ID"]."'";
if(isset($_GET["search"])){
	if($_GET["search"]==1){
		if(!empty($_GET["Keyword"])){
			if($_GET["Fields"] == 'Get_Name'){
				$Get_data = $DB->GetRs("user_get_product","*","where Get_Name like '%".$_GET["Keyword"]."%'");
				$condition .= " and Get_ID =".$Get_data["Get_ID"];
			}else{
				$condition .= " and ".$_GET["Fields"]." LIKE '%".$_GET["Keyword"]."%'";
			}
		}
	}
}
$condition .= " order by Orders_CreateTime desc";
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
    <script type='text/javascript' src='/static/member/js/user_peas.js'></script>
   <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/distribute/distribute_menubar.php');?>
    <div id="peas_orders" class="r_con_wrap"> 
      <script language="javascript">$(document).ready(user_obj.peas_orders_init);</script>
		 <form class="search" id="search_form" method="get" action="?">
        <div class="l">
		  <select name="Fields">
		    <option value="Get_Name">产品名称</option>
			<option value="Address_Name">会员名称</option>
			<option value="Address_Mobile">会员手机</option>
		  </select>
		   <input type="text" name="Keyword" value="" class="form_input" size="15" />
          <input type="hidden" name="search" value="1" />
          <input type="submit" class="search_btn" value=" 搜索 " />
        </div>
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
           <td width="8%" nowrap="nowrap">序号</td>
            <td width="20%" nowrap="nowrap">名称</td>
            <td width="20%" nowrap="nowrap">图片</td>
            <td width="10%" nowrap="nowrap">姓名</td>
            <td width="10%" nowrap="nowrap">手机</td>
            <td width="10%" nowrap="nowrap">时间</td>
            <td width="10%" nowrap="nowrap">状态</td>
            <td width="10%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
        <?php $DB->getPage("user_get_orders","*",$condition,$pageSize=10);
		$i=1;
		$lists = array();
		while($rsGet=$DB->fetch_assoc()){
			$lists[] = $rsGet;
		}
		// echo '<pre>';
		// print_R($lists);
		// exit;
		foreach($lists as $k=>$v){
			$Get = $DB->GetRs("user_get_product","*","where Get_ID=".$v['Get_ID']."");
			$v['Get_Name'] = $Get['Get_Name'];
			$v['Get_ImgPath'] = $Get['Get_ImgPath'];
			?>
          <tr>
            <td nowrap="nowrap"><?php echo $pageSize*($DB->pageNo-1)+$i; ?></td>
            <td><?php echo $v['Get_Name'];?></td>
            <td nowrap="nowrap"><img src="<?php echo $v['Get_ImgPath']?>" class="img" /></a></td>
            <td nowrap="nowrap"><?php echo $v['Address_Name'];?></td>
            <td nowrap="nowrap"><?php echo $v['Address_Mobile'];?></td>
            <td nowrap="nowrap"><?php echo date('Y-m-d H:m:s',$v['Orders_CreateTime']);?></td>
            <td nowrap="nowrap"> <?php echo $v['Orders_Status'] ? '已领取' : '未领取';?> </td>
            <td class="last" nowrap="nowrap"><a href="get_product_orders_view.php?OrderId=<?php echo $v['Orders_ID']?>&page=<?php echo $DB->pageNo;?>"><img src="/static/member/images/ico/view.gif" align="absmiddle" alt="查看" /></a> <a href="?action=del&OrderId=<?php echo $v['Orders_ID']?>&page=<?php echo $DB->pageNo;?>" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" alt="删除" /></a></td>
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
