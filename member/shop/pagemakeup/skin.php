?><?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$rsConfig=$DB->GetRs("shop_config","Makeup_Skin_ID","where Users_ID='".$_SESSION["Users_ID"]."'");
if(isset($_GET["action"]))
{
	if($_GET["action"]=="set")
	{		
		//开始事务定义
		$flag=true;
		mysql_query("begin");
		$Data=array(
			"Makeup_Skin_ID"=>$_GET["Skin_ID"]
		);
		$Set=$DB->Set("shop_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
		$flag=$flag&&$Set;
		$rsHome=$DB->GetRs("makeup_skin","*","where Skin_ID=".$_GET["Skin_ID"]);
		//判断shop_home表中是否有记录
		$rsSkin=$DB->GetRs("makeup_home","*","where Users_ID='".$_SESSION["Users_ID"]."' and Skin_ID=".$_GET["Skin_ID"]);
		if(empty($rsSkin)){
			$Data=array(
				"Home_Json"=>$rsHome["Skin_Json"],
				"Skin_ID"=>$_GET["Skin_ID"],
				"Users_ID"=>$_SESSION["Users_ID"]
			);
			$Add=$DB->Add("makeup_home",$Data);
			$flag=$flag&&$Add;
		}
		
		if($flag)
		{
			//执行事务
			mysql_query("commit");
			echo '<script language="javascript">window.location="home.php?Makeup_Skin_ID='.$_GET["Skin_ID"].'";</script>';
		}else
		{
			//失败回滚
			mysql_query("roolback");
			echo '<script language="javascript">alert("操作失败");history.back();</script>';
		}
	}
	
	//删除开始
	if(!empty($_GET["action"])&&$_GET["action"]=="Del"){
		$ID=empty($_GET["ID"]) ? "0" : $_GET["ID"];
		mysql_query("delete from makeup_skin where Skin_ID='".$ID."'");
		echo "<script language='javascript'>alert('删除成功！');window.open('skin.php','_self');</script>";
	}
	exit;
}
$jcurid = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css?t=1' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<SCRIPT type=text/javascript>
function SelectSkinID(Skin_ID){
	if(confirm("您确定要选择此风格吗？")){
		location.href="skin.php?action=set&Skin_ID="+Skin_ID;
	}
}
</SCRIPT>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
     <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/pagemakeup/basic_menubar.php');?>
    <script language="javascript">$(document).ready(shop_obj.skin_init);</script>
	<div class="b10" style="height:5px;"></div>
    <div class="control_btn"><a href="home.php" class="btn_green btn_w_120">添加页面</a><span class="red">（注：仅添加用，不能编辑!）</span></div>
	<div class="b10" style="height:5px;"></div>
	    <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
          	<td nowrap="nowrap" width="6%">ID</td>         
            <td nowrap="nowrap" width="15%">模板名称</td>
            <td nowrap="nowrap" >URL</td>
            <td nowrap="nowrap" width="8%" class="last">操作</td>
          </tr>
        </thead>
		<?php 
			$i=0;
			$makeup_date = array();
			$DB->get("makeup_skin","*","where Skin_Status=1 order by Skin_Index asc,Skin_ID asc");
			while($rsSkin=$DB->fetch_assoc()){
				$makeup_date[] = $rsSkin;
			}
		?>
        <tbody>
        <?php foreach($makeup_date as $key=>$value){?>
		<!--<img src="/static/admin/images/ico/mod.gif" align="absmiddle" alt="修改" title="修改" /><img src="/static/admin/images/ico/del.gif" align="absmiddle" alt="删除" title="删除" />-->
		<?php if($value['Skin_ID'] != 1 ){ ?>
			<tr>
				<td nowrap="nowrap"><?=$i?></td>
				<td nowrap="nowrap"><?=$value['Skin_Name']?></td>
				<td nowrap="nowrap">http://<?php echo $_SERVER['HTTP_HOST'] ?>/api/<?php echo $_SESSION["Users_ID"]; ?>/shop/makeup/<?php echo $value['Skin_ID'];?>/?love</td>
				 <td class="last" nowrap="nowrap"><a href="?action=set&Skin_ID=<?=$value['Skin_ID']?>">编辑首页</a>&nbsp;<a href="?action=Del&ID=<?=$value['Skin_ID']?>" title="删除" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};">删除</a></td>
			  </tr>
			
		<?php  }?>
		<?php 
			$i++;
		} ?>
        </tbody>
      </table>
  </div>
</div>
</body>
</html>