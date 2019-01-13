<?php
$base_url = base_url();
ini_set("display_errors","On");
$_SERVER['HTTP_REFERER'] =  $base_url.'member/distribute/withdraw_detail.php';
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$rsConfigmenu = Dis_Config::find($_SESSION["Users_ID"]);
$condition = "where Users_ID='".$_SESSION["Users_ID"]."'";

if(isset($_GET["search"])){
	if($_GET["search"]==1){
	
		if(!empty($_GET["Keyword"])){
		  $condition .= " and Method_Account like '%".$_GET["Keyword"]."%'";
		}
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

$condition .= " order by Record_CreateTime desc";
$rsRecords = $DB->getPage("distribute_balance_record","*",$condition,$pageSize=10);
$record_list = $DB->toArray($rsRecords);

$user_array = array();
$product_array = array();
$bank_array = array();

//获取用户列表
$condition = "where Users_ID='".$_SESSION["Users_ID"]."'"; 
if(count($user_array)>0){
	$condition .= "and User_ID in (".implode(',',$user_array).")";
}

$rsUsers = $DB->get('user','User_ID,User_NickName',$condition);
$User_list = $DB->toArray($rsUsers );

$user_dropdown = get_dropdown_list($User_list,'User_ID','User_NickName');

$status = array("申请中","已执行","已驳回");
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
    <script type='text/javascript' src='/static/member/js/distribute/withdraw.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/distribute/distribute_menubar.php');?>
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
        账户名称：
        <input name="Keyword" value="" class="form_input" size="15" type="text">
        记录状态：
        <select name="Status">
          <option value="">--请选择--</option>
          <option value="0">申请中</option>
          <option value="1">已执行</option>
          <option value="2">已驳回</option>
        </select>
        时间：
         <input type="text" class="input" name="AccTime_S" value="" maxlength="20" />
        -
        <input type="text" class="input" name="AccTime_E" value="" maxlength="20" />
		<input value="1" name="search" type="hidden">
        <input class="search_btn" value="搜索" type="submit">
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
			<td width="5%" nowrap="nowrap">ID</td>
			<td width="8%" nowrap="nowrap">微信昵称（姓名）</td>
			<td width="8%" nowrap="nowrap">增加减少前金额</td>
			<td width="8%" nowrap="nowrap">增加减少金额</td>
			<td width="5%" nowrap="nowrap">增加减少后金额</td>
			<td width="15%" nowrap="nowrap">说明</td>
			<td width="8%" nowrap="nowrap">类型</td>
			<td width="8%" nowrap="nowrap">创建时间</td>
          </tr>
        </thead>
        <tbody>
      
		  
	<?php foreach($record_list as $key=>$rsRecord) {
		$userinfo = $DB->GetRs('user','User_NickName','where User_ID='.$rsRecord['User_ID']);
	?>
           <tr Record_ID="<?php echo $rsRecord['Record_ID'] ?>">
          	
          	<td nowarp="nowrap"><?=$rsRecord['Record_ID']?></td>
            <td nowarp="nowrap"><?php echo $userinfo ? $userinfo['User_NickName'] : '无昵称'?></td>
			<td nowarp="nowrap"><?php 
			if($rsRecord['Biz_ID']){
				$rsBiz = Biz::find($rsRecord['Biz_ID']);
				echo $rsBiz['Biz_Name'];
			}else{
				echo "会员";
			}
			?></td>
			<td nowarp="nowrap"><?php 
			if($rsRecord['Payment_Sn']){
				echo '<a href="/member/finance/payment.php?Payment_Sn='.$rsRecord['Payment_Sn'].'">'.$rsRecord['Payment_Sn'] . '</a>';
			}else{
				echo "会员提现";
			}
			?></td>
            <td nowarp="nowrap"><?=$rsRecord['Method_Name']?></td>
            <td nowarp="nowrap"><?=$rsRecord['Method_Account']?></td>
            <td nowarp="nowrap"><?=$rsRecord['Method_No']?></td>   
            <td nowarp="nowrap"><?=$rsRecord['Record_Total']?></td>          
            <td nowarp="nowrap"><?=$rsRecord['Record_Fee']?></td>       
            <td nowarp="nowrap"><?=$rsRecord['Record_Yue']?></td>  
			<td nowarp="nowrap"><?=$rsRecord['No_Record_Desc']?></td> 
            <td nowrap="nowrap"><?=$status[$rsRecord['Record_Status']]?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsRecord['Record_CreateTime']) ?></td>
            <td nowrap="nowrap" class="last">
            <?php if($rsRecord['Record_Status'] == 0) { ?>
            <?php if(trim($rsRecord['Method_Name'])=='微信红包' && $rsRecord['Record_Money']>=1 && $rsRecord['Record_Money']<=200){?>
            	<a href="<?=$base_url?>member/distribute/withdraw.php?action=wx_hongbao&RecordID=<?=$rsRecord['Record_ID']?>">发送红包</a>
			<?php }elseif(trim($rsRecord['Method_Name'])=='微信转账' && $rsRecord['Record_Money']>0){?>
            	<a href="<?=$base_url?>member/distribute/withdraw.php?action=wx_zhuanzhang&RecordID=<?=$rsRecord['Record_ID']?>">微信转账</a>
            <?php }else{?>
            	<a href="<?=$base_url?>member/distribute/withdraw.php?action=fullfill&RecordID=<?=$rsRecord['Record_ID']?>">完成</a>
            <?php }?>
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
    <div class="h">驳回用户提现理由<a class="modal_close" href="#"></a></div>
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
      <input type="hidden" name="action" value="reject_withdraw">
    </form>
    <div class="tips"></div>
  </div>
  
</div>
</div>
</body>
</html>