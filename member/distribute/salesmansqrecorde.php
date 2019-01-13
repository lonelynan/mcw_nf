<?php
$base_url = base_url();
ini_set("display_errors","On");
$_SERVER['HTTP_REFERER'] =  $base_url.'member/distribute/salesmansqrecorde.php';
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$rsConfigmenu = Dis_Config::find($_SESSION["Users_ID"]);
$action=empty($_GET['action'])?'':$_GET['action'];

if(isset($action))
{	

	if($action == "yesgo"){
		$UsersID = $_SESSION["Users_ID"];
		$RecordID = $_GET['RecordID'];
		
		$withdraw_data = $capsule->table('distribute_smansq_record')->where(['Users_ID' => $UsersID, 'Record_ID' =>$RecordID ])->first();
		if(empty($withdraw_data)){
		    die('<script language="javascript">alert("相应的申请记录不存在");history.back();</script>');
		}		
		$UserID = $withdraw_data['User_ID'];
		
		$objUser = User::Multiwhere(['Users_ID' => $UsersID, 'User_ID' =>$UserID ])->first();
		if(!$objUser){
		    die('<script language="javascript">alert("相应的会员不存在");history.back();</script>');
		}
        
		$FlagDis = $capsule->table('distribute_smansq_record')->where(['Users_ID' => $UsersID, 'Record_ID' =>$RecordID ])->update(['Record_Status' => 1]);		
		if($FlagDis)
		{		    
			echo '<script language="javascript">alert("更新成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else
		{		    
			echo '<script language="javascript">alert("更新失败");history.back();</script>';
		}
		exit;
	
	}elseif($action == "nogo"){
		$UsersID = $_SESSION["Users_ID"];
		$RecordID = $_GET['RecordID'];
		
		$withdraw_data = $capsule->table('distribute_smansq_record')->where(['Users_ID' => $UsersID, 'Record_ID' =>$RecordID ])->first();
		if(empty($withdraw_data)){
		    die('<script language="javascript">alert("相应的申请记录不存在");history.back();</script>');
		}		
		$UserID = $withdraw_data['User_ID'];
		
		$objUser = User::Multiwhere(['Users_ID' => $UsersID, 'User_ID' =>$UserID ])->first();
		if(!$objUser){
		    die('<script language="javascript">alert("相应的会员不存在");history.back();</script>');
		}
        
		$FlagDis = $capsule->table('distribute_smansq_record')->where(['Users_ID' => $UsersID, 'Record_ID' =>$RecordID ])->update(['Record_Status' => 0]);		
		if($FlagDis)
		{		    
			echo '<script language="javascript">alert("更新成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else
		{		    
			echo '<script language="javascript">alert("更新失败");history.back();</script>';
		}
		exit;
	}
}


$condition = "where Users_ID='".$_SESSION["Users_ID"]."'";

if(isset($_GET["search"])){

	if($_GET["search"]==1){
		if(isset($_GET["Status"])){
		  if($_GET["Status"]<>''){
			$condition .= " and Record_Status=".$_GET["Status"];
		  }
		}
		if(!empty($_GET["AccTime_S"])){
		  $condition .= " and Record_CreateTime>=".strtotime($_GET["AccTime_S"]);
		}
		if(!empty($_GET["AccTime_E"])){
		  $condition .= " and Record_CreateTime<=".strtotime($_GET["AccTime_E"]);
		}
	
	}
}

$condition .= " order by Record_ID desc";
$rsRecords = $DB->getPage("distribute_smansq_record","*",$condition,$pageSize=10);
$record_list = $DB->toArray($rsRecords);
$status = array("申请中","已审核","已驳回");
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/js/jquery.datetimepicker.js'></script>
<link href='/static/css/jquery.datetimepicker.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/distribute/withdraw.js?t=101'></script>
    <div class="r_nav">
     <ul>
        <li class=""><a href="salesman_config.php" target="iframe">创始人设置</a></li>
        <li class="cur"><a href="salesmansqrecorde.php">创始人申请列表</a></li>
        <li class=""><a href="salesman.php">创始人列表</a></li>
      </ul>
    </div>
    <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script> 
     <script type='text/javascript' src='/static/js/inputFormat.js'></script>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <script language="javascript">
	$(document).ready(function(){withdraw_obj.withdraw_init();});
	
</script>
    <div id="update_post_tips"></div>
    <div id="user" class="r_con_wrap">
      
      <form class="search" id="search_form" method="get" action="?">        
        记录状态：
        <select name="Status">
          <option value="">--请选择--</option>
          <option value="0">申请中</option>
          <option value="1">已审核</option>
          <option value="2">已驳回</option>
        </select>
        申请时间：
         <input type="text" class="input" name="AccTime_S" value="" maxlength="20" />
        -
        <input type="text" class="input" name="AccTime_E" value="" maxlength="20" />
		<input value="1" name="search" type="hidden">
        <input class="search_btn" value="搜索" type="submit">
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
			<td width="5%" nowrap="nowrap">id</td>
			<td width="8%" nowrap="nowrap">申请人</td>
			<td width="5%" nowrap="nowrap">申请姓名</td>
			<td width="15%" nowrap="nowrap">申请电话</td>			<
			<td width="10%" nowrap="nowrap">状态</td>
			<td width="10%" nowrap="nowrap">申请时间</td>
			<td width="8%" nowrap="nowrap" class="last"><strong>操作</strong></td>
          </tr>
        </thead>
        <tbody>		
	<?php foreach($record_list as $key=>$rsRecord) {
		$userinfo = $DB->GetRs('user','User_No,User_NickName','where User_ID='.$rsRecord['User_ID']);
	?>
           <tr Record_ID="<?php echo $rsRecord['Record_ID'] ?>">
          	
          	<td nowarp="nowrap"><?=$rsRecord['Record_ID']?></td>
            <td nowarp="nowrap"><a href="<?=$base_url?>member/user/user_list.php?Fields=User_No&Keyword=<?=$userinfo['User_No']?>&UserFrom=-1&MemberLevel=-1&search=1"><?php echo $userinfo ? $userinfo['User_NickName'] : '无昵称'?></a></td>			
            <td nowarp="nowrap"><?=$rsRecord['Salesman_SqName']?></td>
            <td nowarp="nowrap"><?=$rsRecord['Salesman_SqTelephone']?></td>
			<?php if($rsRecord['Record_Status'] == 1) { ?>
            <td nowarp="nowrap"><a href="<?=$base_url?>member/distribute/salesmansqrecorde.php?action=nogo&RecordID=<?=$rsRecord['Record_ID']?>" onClick="if(!confirm('确定取消通过？')){return false};"><?=$status[$rsRecord['Record_Status']]?></a></td>
			<?php } else { ?>
			<td nowarp="nowrap"><a href="<?=$base_url?>member/distribute/salesmansqrecorde.php?action=yesgo&RecordID=<?=$rsRecord['Record_ID']?>" onClick="if(!confirm('确定审核通过？')){return false};"><?=$status[$rsRecord['Record_Status']]?></a></td>
			<?php } ?>
            <td nowarp="nowrap"><?php echo date("Y-m-d H:i:s",$rsRecord['Record_CreateTime']) ?></td>
            <td nowrap="nowrap" class="last">
            	<a href="<?=$base_url?>member/distribute/salesmansqrecorde_view.php?RecordID=<?=$rsRecord['Record_ID']?>">查看审核</a>
            <?php if($rsRecord['Record_Status'] == 0) { ?>
            <a class="reject_btn" href="javascript:void(0)">驳回</a>
			<?php } ?>
            </td>
          </tr>
	<?php } ?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
  
  <div id="reject_withdraw" class="lean-modal lean-modal-form">
    <div class="h">驳回用户申请理由<a class="modal_close" href="#"></a></div>
    <form class="form">
      <div class="rows">
        <label>驳回理由：</label>
        <span class="input">
        <textarea name="Reject_Reason" id="Reject_Reason" notnull ></textarea>
        <font class="fc_red">*</font></span>
        <div class="clear"></div>
      </div>
      <div class="rows">
        <label></label>
        <span class="submit">
        <input type="submit" value="确定提交" name="submit_btn">
        </span>
        <div class="clear"></div>
      </div>
      <input type="hidden" name="Record_ID" value="" >
      <input type="hidden" name="action" value="reject_salesmansq">
    </form>
    <div class="tips"></div>
  </div>
  
</div>
</div>
</body>
</html>