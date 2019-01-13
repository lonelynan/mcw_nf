<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_SESSION["BIZ_ID"])){
	header("location:/biz/login.php");
}
$rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);
if(empty($rsBiz['User_Mobile'])){
	echo "<script>alert('请先绑定会员!');window.location='bind_user.php';</script>";
	exit;
}
else{
	$rsU=$DB->GetRs("user","*","where User_Mobile='".$rsBiz["User_Mobile"]."'");
}
$Keyword=isset($_GET['Keyword'])?trim($_GET['Keyword']):'';
if(!empty($Keyword)){
	$where=' and ( User_No="'.$Keyword.'" or User_NickName like "%'.$Keyword.'%" or User_Name like "%'.$Keyword.'%" or User_Mobile like "%'.$Keyword.'%")';
}
else{
	$where='';
}

$psize = 20;
$condition = "where Owner_Id=".$rsU["User_ID"].$where.' order by User_ID desc';

/*
if($_POST){
    $User_Mobile = $_POST['User_Mobile'];
    if(!preg_match("/^[1-9]\d*$/",$User_Mobile)){
        echo "<script>alert('手机输入有误!');window.location='bind_user.php';</script>";
        exit;
    }
    $rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);

    $UserRs = $DB->GetRs('user','*',"where Users_ID='".$rsBiz['Users_ID']."' and User_Mobile=".$User_Mobile);
    if(empty($UserRs)){
        echo "<script>alert('该手机不存在!');window.location='bind_user.php';</script>";
        exit;
    }else{
		 $UserRow = $DB->GetRs('biz','*','where User_Mobile='.$User_Mobile.' and Biz_ID!='.$_SESSION["BIZ_ID"]);
		 if($UserRow){
			  echo "<script>alert('该会员其他商家已被绑定!');window.location='bind_user.php';</script>";
			  exit;
		 }
        $data = array('User_Mobile'=>$User_Mobile);
        $Flag = $DB->Set('biz',$data,"where Biz_ID=".$_SESSION["BIZ_ID"]);
        if($Flag){
		echo '<script language="javascript">alert("绑定成功");window.location="bind_user.php";</script>';
	}else{
		echo '<script language="javascript">alert("绑定失败");history.back();</script>';
	}
	exit;
    }	
}else{
    $rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);
    $rsUsers=$DB->GetRs("users","Users_WechatAccount,Users_WechatName","where Users_ID='".$rsBiz["Users_ID"]."'");
}
*/
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<style type="text/css">
.btn{background:#3AA0EB;border: none !important;border-radius: 5px !important;color: #fff;padding: 0px 20px !important;font-size: 12px;height: 26px !important;line-height: 24px !important;cursor: pointer; margin-left:50px;}
</style>
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
    <div id="orders" class="r_con_wrap">
      <form class="search" id="search_form" method="get" action="?">
        <input type="text" name="Keyword" value="<?php echo $Keyword ?>" class="form_input" size="35" placeholder="搜索会员号、微信昵称、姓名或电话"/>
        &nbsp;<input name="提交" type="submit" value="搜索">
        &nbsp;<input name="按钮" type="button" value="消息群发" class="btn" onClick="window.location='my_wxmsg.php';">
      </form>
      <form id="submit_form" method="get" action="">
      <table border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="order_list">
        <thead>
          <tr>
            <td width="6%" nowrap="nowrap">序号</td>
			<td width="10%" nowrap="nowrap">会员号</td>
            <td width="10%" nowrap="nowrap">微信昵称(姓名)</td>
            <td width="14%" nowrap="nowrap">会员姓名</td>
            <td width="14%" nowrap="nowrap">会员电话</td>
            <td width="12%" nowrap="nowrap">注册时间</td>
            <td width="12%" nowrap="nowrap">操作</td>
            </tr>
        </thead>
        <tbody>
          <?php
		  $i=0;
		  $DB->getPage("user","*",$condition,$psize);
		  /*获取订单列表牵扯到的分销商*/
		  $order_list = array();
		  while($rr=$DB->fetch_assoc()){
			$order_list[] = $rr;
		  }
		  foreach($order_list as $rsOrder){
			 //获取所有分销商列表
			//$ds_list = User::where(array('Users_ID' => $_SESSION["Users_ID"],'User_ID' => $rsOrder['User_ID']))
			//->get(array('User_ID','User_NickName','User_No'))
			//->toArray();
			
		  ?>
          <tr>
            <td nowrap="nowrap"><?php echo $rsOrder["User_ID"] ?></td>
			<td><?php echo $rsOrder['User_No'] ?></td>
		  	<td><?php echo $rsOrder['User_NickName'] ?></td>
            <td nowrap="nowrap"><?php echo $rsOrder["User_Name"] ?></td>
            <td nowrap="nowrap"><?php echo $rsOrder["User_Mobile"] ?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsOrder["User_CreateTime"]) ?></td>
            <td nowrap="nowrap"><a href="my_wxmsg.php?openid=<?php echo $rsOrder['User_OpenID'] ?>">发消息</a></td>
            </tr>
          <?php $i++;}?>
        </tbody>
      </table>
      <div style="height:10px; width:100%;"></div>
       <input type="hidden" name="templateid" value="" />
      </form>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
  
  
</div>
</body>
</html>