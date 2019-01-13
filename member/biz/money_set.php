<?php
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}

if(isset($_GET["action"])){
	if($_GET["action"]=="set_qiword"){
		$condition = "where Users_ID='".$_SESSION["Users_ID"]."'";		
		$qiword = $_GET['qiword'];
		
		if(isset($_GET["setj"]) && $_GET["setj"] == 1){
        $condition .= " and Is_Union = 0";
		} elseif (isset($_GET["setj"]) && $_GET["setj"] == 2){
        $condition .= " and Is_Union = 1";
		}
			$Data=array(				
				'qiword'=>$qiword				
			);
			$qiword_flag = $DB->Set("biz",$Data,$condition);
		if ($qiword_flag) {
			$Data=array("status"=>1);
		} else {
			$Data=array("status"=>0);
		}		
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}elseif($_GET["action"]=="get_Perm_Name"){
		
		$Data=array(					
			'Perm_Name'=>$_GET['Perm_Name']
		);
		$DB->Set("permission_config",$Data,"where Permission_ID=".$_GET["Perm_ID"]);
		$Data=array("status"=>1);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}elseif($_GET["action"]=="get_delete"){
		$Data=array(					
			'Is_Delete' => 1
		);
		$DB->Set("permission_config",$Data,"where Permission_ID=".$_GET["Perm_ID"]);
		$Data=array("status"=>1);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}elseif($_GET["action"]=="get_Withdraw"){
		$Data=array(					
			'Perm_On' => $_GET['Perm_On']
		);
		$DB->Set("permission_config",$Data,"where Permission_ID=".$_GET["Perm_ID"]);
		$Data=array("status"=>1);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
}

	$jcurid = 9;
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
<style>
	.sun_input{height: 28px;line-height: 28px; border: 1px solid #ddd;background: #fff;border-radius: 5px; padding: 0 5px;}
	.file_input{height: 30px;color: #fff;border: none;border-radius: 2px;background: url('/static/member/images/global/ok-btn-120-bg.jpg') no-repeat left top;cursor: pointer;width:80px;}
	.LogoUpload{float: left;}
	.LogoDetail{float: left;}
	.LogoDetail img{width:30px;height:30px;}
	.photo_config{width:110px;height:40px; margin:0 auto;}
	.form_select_sun{height:32px;border:1px solid #ddd;padding:5px;vertical-align:middle;border-radius: 5px;}
</style>
</head>

<body>
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/biz_menubar.php');?>	
    <div id="user_profile" class="r_con_wrap">
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
		  <tr>
            <tr>
				<td>标题</td>
				<td>内容</td>				
				<td>操作</td>
			</tr>
          </tr>
        </thead>
        <tbody>
		<tr>
				<td>联盟商家列表（起）</td> 
				<td>
				<input type="radio" name="setj" id="c_0" value="0" checked /><label for="c_0"> 全部</label>&nbsp;&nbsp;
           <input type="radio" name="setj" id="c_1" value="1" /><label for="c_1">普通商家</label>&nbsp;&nbsp;
		   <input type="radio" name="setj" id="c_2" value="2" /><label for="c_2">联盟商家</label>&nbsp;&nbsp;
				<input type="text" name="qiword" value="" class="form_input" size="5" /></td>
				<td><input name="qiwordbtn" class="file_input" type="button"  value="设置" /></td>
            </tr>		
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
<script>
	$('.file_input').click(function(){
			var qiword = $("input[name=qiword]").val();
			var setj = $("input[name=setj]:checked").val();
			$.get('?','action=set_qiword&qiword='+qiword+'&setj='+setj,function(data){
				if(data.status == 1){					
					alert('设置成功！');
					window.location.href= "";
				}else{
					alert('设置失败！');
				}
			},'json');
	});
</script>



