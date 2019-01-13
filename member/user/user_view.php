<?php 
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$rsConfig=$DB->GetRs("user_config","UserLevel","where Users_ID='".$_SESSION["Users_ID"]."'");
if(empty($rsConfig)){
	header("location:config.php");
}else{
	if(empty($rsConfig['UserLevel'])){
		$UserLevel[0]=array(
			"Name"=>"普通会员",
			"UpIntegral"=>0,
			"ImgPath"=>""
		);
		$Data=array(
			"UserLevel"=>json_encode($UserLevel,JSON_UNESCAPED_UNICODE)
		);
		$DB->Set("user_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	}else{
		$UserLevel=json_decode($rsConfig['UserLevel'],true);
	}
}
$action=empty($_REQUEST['action'])?'':$_REQUEST['action'];
$UserID=empty($_REQUEST['UserID'])?'':$_REQUEST['UserID'];
$rsUser=$DB->GetRs("user","*","where Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$UserID);
$RecordType=array("每日签到","手动修改","获取积分","使用积分","使用优惠券获取积分","积分兑换礼品","","大转盘消耗积分","刮刮卡消耗积分");
if(isset($action))
{
	if($action=="del"){
		//1删除用户表记录
		$Flag=$DB->Del("user","Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$_GET["UserID"]);
		//删除用户订单记录
		//删除用户签到
		//删除用户团购记录
		if($Flag)
		{
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else
		{
			echo '<script language="javascript">alert("删除失败");history.back();</script>';
		}
		exit;
	}
	
	if($action=="user_mod")
	{
		$FieldName=array("User_No","User_Name","User_Mobile","User_Integral","","User_Level");
		$Data=array(
			$FieldName[$_POST['field']]=>$_POST['Value']
		);
		$Set=$DB->Set("user",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$_POST["UserID"]);
		if($Set){
			$Data=array("status"=>1);
		}else{
			$Data=array("status"=>0,"msg"=>"写入数据库失败");
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	if($action=="integral_mod")
	{
		$rsUser=$DB->GetRs("user","User_Integral,User_Level","where Users_ID='".$_SESSION["Users_ID"]."' and User_ID='".$_POST["UserID"]."'");
		if($rsUser['User_Integral']+$_POST['Value']<0){
			$Data=array("status"=>2);
		}else{
			//增加
			$Data=array(
				'Record_Integral'=>$_POST['Value'],
				'Record_SurplusIntegral'=>$rsUser['User_Integral'] + $_POST['Value'],
				'Operator_UserName'=>$_SESSION["Users_Account"],
				'Record_Type'=>1,
				'Record_Description'=>"手动修改积分",
				'Record_CreateTime'=>time(),
				'Users_ID'=>$_SESSION['Users_ID'],
				'User_ID'=>$_POST["UserID"]
			);
			$Add=$DB->Add('user_Integral_record',$Data);
			foreach($UserLevel as $k=>$v){
				 if($rsUser['User_Integral']+$_POST['Value']>=$v['UpIntegral']){
					 $levelID=$k;
					 $levelName=$v['Name'];
				 }
			}
			$Set=$DB->Set("user","User_Level=".$levelID.",User_Integral=User_Integral+".$_POST['Value'],"where Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$_POST["UserID"]);
			if($Set){
				$Data=array("status"=>1,"lvl"=>1,"level"=>$levelName);
			}else{
				$Data=array("status"=>0,"msg"=>"写入数据库失败");
			}
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
}
$jcurid = 2;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $SiteName;?></title>
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
    <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script> 
    <script language="javascript">
var level_ary=Array();
	<?php foreach($UserLevel as $k=>$v){
		echo 'level_ary['.$k.']="'.$v['Name'].'";';
	}?>
	$(document).ready(function(){user_obj.user_init();});
</script>
    <div id="update_post_tips"></div>
    <div id="user" class="r_con_wrap">
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table"><th></th>
        <tbody>
          <tr>
            <td>手机</td>
            <td><?=$rsUser['User_Telephone']?></td>
            <td>姓名</td>
            <td><?=$rsUser['User_Name']?></td>
          </tr>
          <tr>
            <td>性别</td>
            <td><?=$rsUser['User_Gender']?></td>
            <td>年龄</td>
            <td><?=$rsUser['User_Age']?></td>
          </tr>
          <tr>
            <td>昵称</td>
            <td><?=$rsUser['User_NickName']?></td>
            <td>身份证号</td>
            <td><?=$rsUser['User_IDNum']?></td>
          </tr>
          <tr>
            <td>电话</td>
            <td><?=$rsUser['User_Telephone']?></td>
            <td>传真</td>
            <td><?=$rsUser['User_Fax']?></td>
          </tr>
          <tr>
            <td>生日</td>
            <td><?=$rsUser['User_Birthday']?></td>
            <td>QQ</td>
            <td><?=$rsUser['User_QQ']?></td>
          </tr>
          <tr>
            <td>邮箱</td>
            <td><?=$rsUser['User_Email']?></td>
            <td>公司</td>
            <td><?=$rsUser['User_Company']?></td>
          </tr>
          <tr>
            <td>地区</td>
            <td><?php echo $rsUser['User_Province'].'-'.$rsUser['User_City'].'-'.$rsUser['User_Area']; ?></td>
            <td>详细地址</td>
            <td><?=$rsUser['User_Address']?></td>
          </tr>
		  <?php
			if (!empty($rsUser['User_Json_Input'])) {
				$user_input = json_decode($rsUser['User_Json_Input'],true);
				if (is_array($user_input)) {
					$i = 0;
					foreach ($user_input as $k=>$v) {
					?>
			<?php if ($i == 0 || $i % 2 == 0) { ?>		
			<tr>
			<?php } ?>
			<td><?=$v['InputName']?></td>
            <td><?=$v['InputValue']?></td>
			<?php if ($i != 0 || $i % 2 != 0) { ?>		
			</tr>
			<?php } ?>
		<?php	
			$i++;
					}
				}
			}
			
		  if (!empty($rsUser['User_Json_Select'])) {
				$user_input = json_decode($rsUser['User_Json_Select'],true);
				if (is_array($user_input)) {					
					foreach ($user_input as $k=>$v) {
					?>
			<?php if ($i == 0 || $i % 2 == 0) { ?>		
			<tr>
			<?php } ?>
			<td><?=$v['SelectName']?></td>
            <td><?=$v['SelectValue']?></td>
			<?php if ($i != 0 || $i % 2 != 0) { ?>		
			</tr>
			<?php } ?>
		<?php	
			$i++;
					}
				}
			}
		  ?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="15%" nowrap="nowrap">序号</td>
            <td width="15%" nowrap="nowrap">类型</td>
            <td width="10%" nowrap="nowrap">变动前积分</td>
            <td width="10%" nowrap="nowrap">积分</td>
            <td width="10%" nowrap="nowrap">变动后积分</td>
            <td width="25%" nowrap="nowrap">内容</td>
            <td width="10%" nowrap="nowrap">操作员</td>
            <td width="20%" nowrap="nowrap">时间</td>
          </tr>
        </thead>
        <tbody>
          <?php $DB->getPage("user_integral_record","*","where Users_ID='".$_SESSION['Users_ID']."' and User_ID=".$UserID." order by Record_ID desc",$pageSize=10);
		$i=1;
		while($rsRecord=$DB->fetch_assoc()){?>
          <tr>
            <td nowrap="nowrap"><?php echo $pageSize*($DB->pageNo-1)+$i; ?></td>
            <td nowrap="nowrap"><?php echo $RecordType[$rsRecord["Record_Type"]] ?></td>
			<td nowrap="nowrap"><?php echo $rsRecord["Record_SurplusIntegral"] - $rsRecord["Record_Integral"] ?></td>			
            <td nowrap="nowrap"><?php echo $rsRecord["Record_Integral"] ?></td>
            <td nowrap="nowrap"><?php echo $rsRecord["Record_SurplusIntegral"] ?></td>
            <td nowrap="nowrap"><?php echo $rsRecord["Record_Description"] ?></td>
            <td nowrap="nowrap"><?php echo $rsRecord["Operator_UserName"] ?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsRecord["Record_CreateTime"]) ?></td>
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