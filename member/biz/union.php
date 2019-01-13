<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}

if(isset($_GET["action"])){
	if($_GET["action"]=="del"){
		begin_trans();
		$delconfirm_shop  = $DB->Del("shop_products","Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID=".$_GET["BizID"]);		
		$delconfirm_order  = $DB->Del("user_order","Order_Status!=4 and Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID=".$_GET["BizID"]);
		
		$Flag=$DB->Del("biz","Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID=".$_GET["BizID"]);
		if($Flag && $delconfirm_shop && $delconfirm_order){
			commit_trans();
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			back_trans();
			echo '<script language="javascript">alert("删除失败");history.back();</script>';
		}
		exit;
	}
}
$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Is_Union=1";
$OrderBy = "Biz_Status ASC,Biz_CreateTime DESC";
if(isset($_GET['search'])){
	if($_GET['Keyword']){
		$condition .= " and ".$_GET['Fields']." like '%".$_GET['Keyword']."%'";
	}
	if($_GET['SearchCateId']>0){
		$catid = $_GET['SearchCateId'];
		$DB->Get("biz_union_category","Category_ID","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID=".$_GET['SearchCateId']);
		while($r = $DB->fetch_assoc()){
			$catid .= ','.$r["Category_ID"];
		}
		$condition .= " and Category_ID in(".$catid.")";
	}
	if($_GET['Status']!=""){
		$condition .= " and Biz_Status=".intval($_GET['Status']);
	}
	if($_GET['OrderBy']){
		$OrderBy = $_GET['OrderBy'];
	}
}

$condition .= " order by ".$OrderBy;

$_Status = array('<font style="color:#ff6600">正常</font>','<font style="color:blue">禁用</font>');

$category_list = array();
$DB->Get("biz_union_category","Category_ParentID,Category_Name,Category_ID","where Users_ID='".$_SESSION["Users_ID"]."' order by Category_ParentID asc,Category_Index asc,Category_ID asc");
while($r = $DB->fetch_assoc()){
	if($r["Category_ParentID"]==0){
		$category_list[$r["Category_ID"]] = $r;
	}else{
		$category_list[$r["Category_ParentID"]]["child"][] = $r;
	}
}
$jcurid = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<!--<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script> -->
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type="text/javascript" src="/static/js/jquery-1.11.1.js"></script>
<script type="text/javascript" src="/static/js/jquery.qrcode.js"></script>
<script type="text/javascript" src="/static/js/qrcode.js"></script>
<script type="text/javascript" src="/static/js/utf.js"></script>
<style type="text/css">
#bizs .search{padding:10px; background:#f7f7f7; border:1px solid #ddd; margin-bottom:8px; font-size:12px;}
#bizs .search *{font-size:12px;}
#bizs .search .search_btn{background:#1584D5; color:white; border:none; height:22px; line-height:22px; width:50px;}
#iframe_page .iframe_content #bizs .r_con_table tbody tr td div img{ width:80px !important; height:80px !important;}
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/bizunion_menubar.php');?>
	
    <div id="bizs" class="r_con_wrap">
      <div class="control_btn"><a href="union_add.php" class="btn_green btn_w_120">添加商家</a></div>
      <form class="search" method="get" action="?">
        <select name="Fields">
          <option value="Biz_Name">名称</option>
          <option value="Biz_Account">登录名</option>
          <option value="Biz_Address">地址</option>
          <option value="Biz_Contact">联系人</option>
          <option value="Biz_Phone">联系电话</option>
        </select>
        <input type="text" name="Keyword" value="" class="form_input" size="15" />&nbsp;
        商城分类：
        <select name='SearchCateId'>
          <option value=''>--请选择--</option>
          <?php
		  foreach($category_list as $catid=>$value){
			  echo '<option value="'.$value["Category_ID"].'">'.$value["Category_Name"].'</option>';
			  if(!empty($value["child"])){
				  foreach($value["child"] as $k=>$v){
				      echo '<option value="'.$v["Category_ID"].'">└'.$v["Category_Name"].'</option>';
				  }
			   }
		   }
		   ?>
        </select>&nbsp;
        状态：
        <select name="Status">
          <option value="">全部</option>
          <option value="0">正常</option>
          <option value="1">禁用</option>
        </select>&nbsp;
        排序：
        <select name="OrderBy">
          <option value="Biz_Status ASC,Biz_CreateTime DESC">默认</option>
          <option value="Biz_CreateTime DESC">添加时间降序</option>
          <option value="Biz_CreateTime ASC">添加时间升序</option>
        </select>
        <input type="hidden" name="search" value="1" />
        <input type="submit" class="search_btn" value="搜索" />
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="6%" nowrap="nowrap">ID</td>
            <td width="10%" nowrap="nowrap">登录账号</td>
            <td width="10%" nowrap="nowrap">合同编号</td>
            <td width="12%" nowrap="nowrap">商家名称</td>            
            <td width="10%" nowrap="nowrap">联系人</td>
            <td width="10%" nowrap="nowrap">联系电话</td>
            <td width="10%" nowrap="nowrap">添加时间</td>
            <td width="10%" nowrap="nowrap">状态</td>
			<!--edit in 20160330-->
			<td width="12%" nowrap="nowrap">二维码</td>
			<td width="12%" nowrap="nowrap">推广码</td>
            <td width="10%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
          <?php 
		  $lists = array();
		  $DB->getPage("biz","*",$condition,10);
		  
		  while($r=$DB->fetch_assoc()){
			  $lists[] = $r;
		  }
		  foreach($lists as $k=>$rsBiz){
			  ?>
              
          <tr>
            <td nowrap="nowrap"><?php echo $rsBiz["Biz_ID"] ?></td>
            <td><?php echo $rsBiz["Biz_Account"] ?></td>
            <td><?php echo $rsBiz["Biz_contract"] ?></td>
            <td><?php echo $rsBiz["Biz_Name"] ?></td>            
            <td nowrap="nowrap"><?php echo $rsBiz['Biz_Contact']; ?></td>
            <td nowrap="nowrap"><?php echo $rsBiz["Biz_Phone"] ? $rsBiz["Biz_Phone"] : "暂无";?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d",$rsBiz["Biz_CreateTime"]) ?></td>
            <td nowrap="nowrap"><?php echo $_Status[$rsBiz["Biz_Status"]]; ?></td>
			<!--edit in 20160330-->
			 <td nowraqp="nowrap">
            <img width="80" height="80" src="<?=$rsBiz['Biz_Qrcode']?>" /></td>
             <?php 
			 $um=(empty($rsBiz['User_Mobile'])?'0':$rsBiz['User_Mobile']);
			  $rsUser = $DB->GetRs('user','User_ID,User_NickName,User_Mobile',"where User_Mobile=".$um);
			  if($um==0){
			 ?>
			 <td nowraqp="nowrap">请去商家后台绑定会员</td><!-- -->
            <?php }else{?>
			 <td nowraqp="nowrap"><!-- onMouseMove="$('#<'?php echo 'zcode'.$rsBiz["Biz_ID"] ?>').show();$('#<'?php echo 'txt'.$rsBiz["Biz_ID"] ?>').hide();" onMouseOut="$('#<'?php echo 'zcode'.$rsBiz["Biz_ID"] ?>').hide();$('#<'?php echo 'txt'.$rsBiz["Biz_ID"] ?>').show();" -->
<style type="text/css">
#<?php echo 'zcode'.$rsBiz["Biz_ID"] ?> canvas{ width:80px !important; height:80px !important;}</style>
			<div id="<?php echo 'zcode'.$rsBiz["Biz_ID"] ?>"></div>
			<script type="text/javascript">
		     jQuery('#<?php echo 'zcode'.$rsBiz["Biz_ID"] ?>').qrcode({
		     	 render    : "canvas",
		         text    : "<?php echo 'http://shop.maocongwang.com/api/y7c0ppn5cu/shop/'.$rsUser['User_ID'].'/' ?>",
		         width : "1480",
                 height : "1480",
                 background : "#ffffff",
                 foreground : "#2D9EC6",
                 src: 'http://shop.maocongwang.com//uploadfiles/image/logo.jpg'
		     });    
            </script>
            
            </td>
            <?php }?>
            <td class="last" nowrap="nowrap"><a href="union_edit.php?BizID=<?php echo $rsBiz["Biz_ID"] ?>"><img src="/static/member/images/ico/mod.gif" align="absmiddle" alt="修改" /></a> <a href="?action=del&BizID=<?php echo $rsBiz["Biz_ID"] ?>" onClick="if(!confirm('将会一并删除掉该商家的产品及订单您确定删除该商家？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" alt="删除" /></a></td>
          </tr>
          <?php }?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
      <div style="background:#F7F7F7; border:1px #dddddd solid; height:40px; line-height:40px; font-size:12px; margin:10px 0px; padding-left:15px; color:#ff0000">提示：商家登陆地址 <a href="/biz/login.php" target="_blank">http://<?php echo $_SERVER['HTTP_HOST'];?>/biz/login.php</a></div>
    </div>
  </div>
</div>
</body>
</html>