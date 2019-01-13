?><?php
$DB->showErr=false;
if (empty($_SESSION["Users_Account"])) {
	header("location:/member/login.php");
}
if ($_POST) {
	$Data=array(
		"Users_ID" => $_SESSION["Users_ID"],
		"Reason_Name" => !empty($_POST["Reason_Name"]) ? $_POST["Reason_Name"] : '',
		"Reason_Type" => !empty($_POST["Reason_Type"]) ? $_POST["Reason_Type"] : 0,
        "Create_Time" => time()
	);
	$Flag=$DB->Add("shop_reason",$Data);
	if ($Flag) {
		echo '<script language="javascript">alert("添加成功");window.location="reason.php";</script>';
	} else {
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	}
}
if (isset($_GET['Reason_ID'])) {
	$Data = array(
		"Reason_Status" => 1
	);
	$Flag = $DB->set("shop_reason",$Data,"where Reason_ID=".intval($_GET['Reason_ID']));
	if ($Flag) {
		$Data=array("status"=>1,"msg"=>"修改成功！");
	} else {
		$Data=array("status"=>0,"msg"=>"修改失败！");
	}
	echo json_encode($Data,JSON_UNESCAPED_UNICODE);
	exit;
}
$DB->get("shop_reason","*","where Users_ID='".$_SESSION["Users_ID"]."' AND Reason_Status = 0 AND Reason_Type = 0");
$reason_data = array();
while($r=$DB->fetch_assoc()){
	$reason_data[] = $r;
}
$DB->get("shop_reason","*","where Users_ID='".$_SESSION["Users_ID"]."' AND Reason_Status = 0 AND Reason_Type = 1");
$reason_shop = array();
while($h=$DB->fetch_assoc()){
	$reason_shop[] = $h;
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
<link rel="stylesheet" href="/static/tubiao/css/font-awesome.css" type="text/css">
<link rel="stylesheet" href="/static/tubiao/css/font-awesome-ie7.css" type="text/css">
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type='text/javascript' src='/static/member/js/shop.js'></script>
<style type="text/css">
	#config_form img{width:100px; height:100px;}
	.reson_list{ border: 0px; height: 25px;border-radius: 5px;padding: 0px 10px;background-color: #3AA0EB; color:#FFF;}
</style>
<script language="javascript">
	$(document).ready(function(){
		global_obj.reason_form_init();
	});
</script>
</head>
<body>

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/setting/basic_menubar.php');?>
    <div class="r_con_config r_con_wrap" style="min-height:100px;">
      <form id="config_form" action="reason.php" method="post">
        <table border="0" cellpadding="0" cellspacing="0">
		  <tr>
            <td style="width:35%;">
				<label>退货/换货：</label> 
				 <select name="Reason_Type" class="input" style="height:30px;">
					<option value = "0" >退货</option>
					<option value = "1">换货</option>
				 </select>
			</td>
          </tr>
          <tr>
            <td style="width:35%;">
				<label>&nbsp;&nbsp;&nbsp;&nbsp;理&nbsp;&nbsp; 由：&nbsp;</label> <input type="text" class="input" name="Reason_Name" value="" notnull />
			</td>
			
          </tr>
		  <tr>
			<td>
				<div class="submit" style="float:left;">
				  <input type="submit" name="submit_button" value="提交保存" />
				</div>
			</td>
		  </tr>
        </table>
      </form>
    </div>
	<div class="r_con_config r_con_wrap">
	    <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td style="width:8%;">
				<label>退货标签：</label>
			</td>
			<td>
				<?php foreach ($reason_data as $key=>$value){?>
					<button class="reson_list"><?=$value['Reason_Name']?> <span class="fa fa-times deleted" Reason_ID = "<?=$value['Reason_ID']?>" style="font-size:15px"></span></button>
				<?php } ?>
			</td>
          </tr>
		  <tr>
            <td style="width:8%;">
				<label>换货标签：</label>
			</td>
			<td>
				<?php foreach ($reason_shop as $key=>$value){?>
					<button class="reson_list"><?=$value['Reason_Name']?> <span class="fa fa-times deleted" Reason_ID = "<?=$value['Reason_ID']?>" style="font-size:15px"></span></button>
				<?php } ?>
			</td>
          </tr>
        </table>
	</div>
  </div>
</div>
</body>
</html>
<script>
	$(".deleted").click(function(){
		var Reason_ID = $(this).attr("Reason_ID");
		$.get("reason.php", {Reason_ID: Reason_ID},
		  function(data){
			  if (data.status == 1) {
				  alert("修改成功！");
				  window.location.href="reason.php";
			  }else{
				  alert("修改成功！");
				  window.location.href="reason.php";
			  }
		});
		
	});
</script>