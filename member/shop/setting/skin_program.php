?><?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$rsConfig=$DB->GetRs("shop_config","Pro_Skin_ID","where Users_ID='".$_SESSION["Users_ID"]."'");
if(isset($_GET["action"]))
{
	if($_GET["action"]=="set")
	{		
		//开始事务定义
		$flag=true;
		mysql_query("begin");
		$Data=array(
			"Pro_Skin_ID"=>$_GET["Skin_ID"]
		);
		$Set=$DB->Set("shop_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
		$flag=$flag&&$Set;
		$rsHome=$DB->GetRs("shop_skin_program","*","where Pro_Skin_ID=".$_GET["Skin_ID"]);
		//判断shop_home表中是否有记录
		$rsSkin=$DB->GetRs("shop_home_program","*","where Users_ID='".$_SESSION["Users_ID"]."' and Pro_Skin_ID=".$_GET["Skin_ID"]);
		if(empty($rsSkin)){
			$Data=array(
				"Home_Json"=>$rsHome["Skin_Json"],
				"Pro_Skin_ID"=>$_GET["Skin_ID"],
				"Users_ID"=>$_SESSION["Users_ID"]
			);
			$Add=$DB->Add("shop_home_program",$Data);
			$flag=$flag&&$Add;
		}
		
		if($flag)
		{
			//执行事务
			mysql_query("commit");
			echo '<script language="javascript">alert("选择成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else
		{
			//失败回滚
			mysql_query("roolback");
			echo '<script language="javascript">alert("操作失败");history.back();</script>';
		}
	} elseif ($_GET["action"]=="setre") {
		$rsHome=$DB->GetRs("shop_skin_program","Skin_Json","where Pro_Skin_ID=".$_GET["Skin_ID"]);
			$Data=array(				
				"Home_Json"=>$rsHome["Skin_Json"]
			);
		$Set=$DB->Set("shop_home_program",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Pro_Skin_ID=".$_GET["Skin_ID"]);
		if($Set)
		{
			echo '<script language="javascript">alert("重置成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			echo '<script language="javascript">alert("操作失败");history.back();</script>';
		}
	}
	exit;
}
$jcurid = 8;
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
<SCRIPT type=text/javascript>
function SelectSkinID(Skin_ID){	
		location.href="skin_program.php?action=set&Skin_ID="+Skin_ID;
}
function ReSkinID(Skin_ID){
	if(confirm("您确定要重置此风格吗？")){
		location.href="skin_program.php?action=setre&Skin_ID="+Skin_ID;
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
     <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/setting/basic_menubar.php');?>
    <script language="javascript">//$(document).ready(shop_obj.skin_init);</script>
  
    <div id="skin" class="r_con_wrap">
      <ul>
        
        <?php 
		$i=0;
		$DB->get("shop_skin_program","*","where Skin_Status=1 order by Skin_Index asc,Pro_Skin_ID asc");
while($rsSkin=$DB->fetch_assoc()){
	$show_title = $rsSkin['Pro_Skin_ID']==1 ? 'Diy个性首页' : $i.'号模版';
	echo '<li class="'.($rsConfig['Pro_Skin_ID']==$rsSkin['Pro_Skin_ID']?"cur":"").'">
            <div class="item" title="点击选择小程序风格" onClick="SelectSkinID('.$rsSkin['Pro_Skin_ID'].');">
              <div class="img"><img src="/static/member/images/shop/skin/'.$rsSkin["Pro_Skin_ID"].'.jpg" /></div>              
            </div>
			<input type="button" onClick="ReSkinID('.$rsSkin['Pro_Skin_ID'].');" class="btn_green" name="submit_button" style="cursor:pointer;margin:5px 0px 0px 25px" value="恢复'.$show_title.'" />
          </li>';
		  $i++;
}?>
      </ul>
      <div class="clear"></div>
    </div>
  </div>
</div>
</body>
</html>