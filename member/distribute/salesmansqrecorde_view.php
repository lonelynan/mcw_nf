<?php
ini_set ( "display_errors", "On" );
$RecordID=empty($_REQUEST['RecordID'])?0:$_REQUEST['RecordID'];
if ($_POST) {
	$base_url = base_url();
	$_SERVER['HTTP_REFERER'] =  $base_url.'member/distribute/salesmansqrecorde.php';
	$poststatus = !empty($_POST["submit_button"]) ? $_POST["submit_button"] : '通过';
    if($poststatus == "通过"){
		$UsersID = $_SESSION["Users_ID"];
		$RecordID = $_POST['RecordID'];
		
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
	
	}elseif($poststatus == "取消"){
		$UsersID = $_SESSION["Users_ID"];
		$RecordID = $_POST['RecordID'];
		
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
$rsRecord=$DB->GetRs("distribute_smansq_record","*","where Users_ID='".$_SESSION['Users_ID']."' and Record_ID=".$RecordID);
$rsRecordjson = json_decode($rsRecord['Salesman_Sq'], true);
$userinfo = $DB->GetRs('user','User_NickName','where User_ID='.$rsRecord['User_ID']);
$Record_Status = array("申请中","已审核","已驳回");
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

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css' />    
    <div class="r_nav">
     <ul>
        <li class=""><a href="salesman_config.php" target="iframe">创始人设置</a></li>
        <li class="cur"><a href="salesmansqrecorde.php">创始人申请列表</a></li>
        <li class=""><a href="salesman.php">创始人列表</a></li>
      </ul>
    </div>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>     
    <div id="orders" class="r_con_wrap">
      <div class="detail_card">
	  <form id="order_send_form" method="post" action="?">
          <table width="100%" border="0" cellspacing="0" cellpadding="0" class="order_info">
            <tr>
              <td width="3%" nowrap>申请人：</td>
              <td width="92%"><?=$userinfo['User_NickName']?></td>
            </tr>
			<tr>
              <td align="center" width="3%" colspan="2"><strong style="font-size:18px">===申请内容开始===</strong></td>
            </tr>
			<tr>
              <td width="3%" nowrap>申请人姓名：</td>
              <td width="92%"><?=$rsRecord['Salesman_SqName']?></td>
            </tr>
			<tr>
              <td width="3%" nowrap>申请人电话：</td>
              <td width="92%"><?=$rsRecord['Salesman_SqTelephone']?></td>
            </tr>
			<?php
			if (!empty($rsRecordjson) && is_array($rsRecordjson)) {
					foreach ($rsRecordjson as $key=>$value) {
						?>
						<tr>
						<?php
						foreach ($value as $k=>$v) {
							?>
						<td width="3%" nowrap><?=$k?>：</td>
						<td width="92%"><?=$v?></td>
						<?php
						}
						?>
						<tr/>
						<?php
					}				
			}
		  ?>
			<tr>
              <td align="center" width="3%" colspan="2"><strong style="font-size:15px">===申请内容结束===</strong></td>
            </tr>
			<tr>
              <td width="3%" nowrap>申请时间：</td>
              <td width="92%"><?php echo date("Y-m-d H:i:s",$rsRecord["Record_CreateTime"]) ?></td>
            </tr>
			<tr>
              <td nowrap>当前状态：</td>
              <td><?=$Record_Status[$rsRecord["Record_Status"]]?></td>
            </tr>
			<?php if($rsRecord["Record_Status"] == 2) { ?>
			<tr>
              <td nowrap>驳回原因：</td>
              <td><?=$rsRecord["No_Record_Desc"]?></td>
            </tr>
			<tr>
              <td width="3%" nowrap>驳回时间：</td>
              <td width="92%"><?php echo date("Y-m-d H:i:s",$rsRecord["Record_Notime"]) ?></td>
            </tr>
			<?php } ?>
			<tr>
			<?php if ($rsRecord["Record_Status"] != 1) { ?>
			  <td><input type="submit" class="btn_green" name="submit_button" value="通过"/></td>
			<?php } else { ?>
			<td><input type="submit" class="btn_green" name="submit_button" value="取消"/></td>
			<?php } ?>
              <td><a href="javascript:void(0);" class="btn_gray" onClick="history.go(-1);">返 回</a></td>
            </tr>
          </table>
		  <input type="hidden" name="RecordID" value="<?= $RecordID ?>"/>
		  </form>
      </div>	  
    </div>	
  </div> 
</div>
</body>
</html>