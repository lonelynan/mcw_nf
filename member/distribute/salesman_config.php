<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');

$base_url = base_url();

if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
//获取分销商称号配置

if($_POST){	
	//判断增加的输入项是否为空
                if (!empty($_POST['JSON'])) {
                    foreach ($_POST['JSON'] as $key => $value) {
                        $Type = trim(cleanJsCss($value['Type']));
                        $Name = trim(cleanJsCss($value['Name']));
                        $Value = trim(cleanJsCss($value['Value']));
                        if (empty($Type) || empty($Name) || empty($Value)) {
                            echo '<script>alert("请填写输入信息提示，以便用户填写");history.back();</script>';
                            exit;
                        } else {
                            $_POST[$key] = [
                                'Type' => $Type,
                                'Name' => $Name,
                                'Value' => $Value
                            ];
                        }
                    }
                }
	$Dis_salesman['Salesman'] = json_encode((isset($_POST['JSON']) ?$_POST['JSON'] : []), JSON_UNESCAPED_UNICODE);
	$Dis_salesman['DisplayName'] =  $_POST['DisplayName'];
	$Dis_salesman['DisplayTelephone'] =  $_POST['DisplayTelephone'];
	$Dis_salesman['Isauditing'] =  $_POST['isauditing'];
	$Dis_salesman['Salesman_ImgPath'] =  $_POST['ImgPath'];
	$flag = $DB->Set('distribute_config', $Dis_salesman, "where Users_ID='" . $_SESSION['Users_ID'] . "'");
	if($flag){
		echo "<script>alert('设置成功');window.location='salesman_config.php';</script>";
	}
}

//获取此商家业务设置
$rsDsLvel = $DB->GetRs('distribute_config', 'Salesman,Isauditing,DisplayTelephone,DisplayName,Salesman_ImgPath', "where Users_ID='" . $_SESSION['Users_ID'] . "'");
if(!$rsDsLvel){
	$Data = array(
		"Users_ID"=>$_SESSION['Users_ID'],
		"Salesman"=>0,
		"Salesman_ImgPath"=>'/static/api/distribute/images/sales_join_header.jpg'
	);
	
	$DB->Set('distribute_config', $Data,"where Users_ID='" . $_SESSION['Users_ID'] . "'");
	$rsDsLvel = $Data;
}

// 手动申请表单
    $JSON = json_decode($rsDsLvel['Salesman'], true);
	
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/js/jquery.formatCurrency-1.4.0.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type='text/javascript' src='/static/member/js/shop.js'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script>
KindEditor.ready(function(K) {
	var editor = K.editor({
		uploadJson : '/member/upload_json.php?TableField=shop_sales_config&UsersID=<?php echo $_SESSION["Users_ID"];?>',
		fileManagerJson : '/member/file_manager_json.php',
		showRemote : true,
		allowFileManager : true,
	});
	
	K('#ImgUpload').click(function(){
        editor.loadPlugin('image', function(){
            editor.plugin.imageDialog({
                imageUrl : K('#ImgPath').val(),
                clickFn : function(url, title, width, height, border, align){
                    K('#ImgPath').val(url);
                    K('#ImgDetail').html('<img src="'+url+'" />');
                    editor.hideDialog();
                }
            });
        });
	
    });
})


</script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <div class="r_nav">
     <ul>
        <li class="cur"><a href="salesman_config.php" target="iframe">创始人设置</a></li>
        <li class=""><a href="salesmansqrecorde.php">创始人申请列表</a></li>
        <li class=""><a href="salesman.php">创始人列表</a></li>
      </ul>
    </div>
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <script language="javascript">
	$(document).ready(function(){
		shop_obj.dis_title_init();
	});
    </script>
    <div id="bizs" class="r_con_wrap">
      <form id="level_form" method="post" action="?" class="r_con_form">
		<div class="rows">
                  <label>申请表单设置</label>
                  <span class="input">
                            <div class="tips">填写你要收集的内容，预约默认选项不可以修改删除！</div>
                            <table border="0" cellpadding="5" cellspacing="0" id="reverve_field_table" class="reverve_field_table">
                                <thead>
                                <tr>
                                    <td width="16%">字段类型</td>
                                    <td width="32%">字段名称</td>
                                    <td width="32%">初始内容</td>
                                    <td width="20%">操作</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>文本框：</td>
                                    <td><input type="text" class="form_input" value="姓名" name="" disabled="disabled"/></td>
                                    <td><input type="text" class="form_input" value="请输入您的姓名" name="" disabled="disabled"/></td>
                                    <td><input type="checkbox" name="DisplayName" value="1" <?= empty($rsDsLvel["DisplayName"]) ? "" : "checked" ?> /> 是否显示</td>
                                </tr>
                                <tr>
                                    <td>文本框：</td>
                                    <td><input type="text" class="form_input" value="电话" name="" disabled="disabled"/></td>
                                    <td><input type="text" class="form_input" value="请输入您的电话" name="" disabled="disabled"/></td>
                                    <td><input type="checkbox" name="DisplayTelephone" value="1" <?= empty($rsDsLvel["DisplayTelephone"]) ? "" : "checked" ?> /> 是否显示</td>
                                </tr>
                                <?php
                                if (!empty($JSON)) {
                                    foreach ($JSON as $key => $value) {
                                        echo '<tr><td><select name="JSON[' . $key . '][Type]" onchange="JSONType(this);"><option value="text"' . ($value["Type"] == "text" ? " selected" : "") . '>文本框</option><option value="select"' . ($value["Type"] == "select" ? " selected" : "") . '>选择框</option></select>：</td><td><input type="text" name="JSON[' . $key . '][Name]" value="' . $value["Name"] . '" class="form_input"></td><td><input type="text" placeholder="" name="JSON[' . $key . '][Value]" value="' . $value["Value"] . '" class="form_input"></td><td><a onclick="document.getElementById(\'reverve_field_table\').deleteRow(this.parentNode.parentNode.rowIndex);" href="javascript:void(0);"> <img hspace="5" src="/static/member/images/ico/del.gif"></a></td></tr>';
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                            <table border="0" cellpadding="5" cellspacing="0">
                                <thead>
                                <tr>
                                    <td align="center"><a href="javascript:;" onClick="insertRow()" class="btn_gray"><img src="/static/member/images/ico/add.gif" align="absmiddle"/>增加</a></td>
                                </tr>
                                </thead>
                            </table>
                            </span>
                  <div class="clear"></div>
              </div>
			  
			  <div class="rows">
                  <label>是否审核</label>
                  <span class="input">
                            <input name="isauditing" value="1" id="radio_5" type="radio" <?= $rsDsLvel['Isauditing'] == 1 ? 'checked' : '' ?> ><label for="radio_5">审核</label>&nbsp;&nbsp;
                            <input name="isauditing" value="0" id="radio_6" type="radio" <?= $rsDsLvel['Isauditing'] == 0 ? 'checked' : '' ?> ><label for="radio_6">不审核</label>
                        </span>
                  <div class="clear"></div>
              </div>
		
        <div class="rows">
          <label>申请创始人页面顶部图</label>
          <span class="input"> <span class="upload_file">
          <div>
            <div class="up_input">
              <input type="button" id="ImgUpload" value="添加图片" style="width:80px;" />
            </div>
            <div class="tips">图片建议尺寸：640*自定义</div>
            <div class="clear"></div>
          </div>
          <div class="img" id="ImgDetail" style="margin-top:8px"><img src="<?=!empty($rsDsLvel['Salesman_ImgPath'])?$rsDsLvel['Salesman_ImgPath']:'/static/api/distribute/images/sales_join_header.jpg'?>" /></div>
          </span> </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" name="submit_button" value="提交保存" /></span>
          <div class="clear"></div>
        </div>
        <input type="hidden" id="ImgPath" name="ImgPath" value="<?=$rsDsLvel['Salesman_ImgPath']?>" />
      </form>
    </div>
  </div>
</div>

<script  type='text/javascript'>
	// 表单增加
    function insertRow() {
        var newrow = document.getElementById('reverve_field_table').insertRow(-1);
        newrow.insertCell(0).innerHTML = '<select onChange="JSONType(this);" name="JSON[' + (document.getElementById('reverve_field_table').rows.length - 6) + '][Type]"><option value="text" selected>文本框</option><option value="select">选择框</option></select>：';
        newrow.insertCell(1).innerHTML = '<input type="text" class="form_input" value="" name="JSON[' + (document.getElementById('reverve_field_table').rows.length - 6) + '][Name]" />';
        newrow.insertCell(2).innerHTML = '<input type="text" class="form_input" value="" name="JSON[' + (document.getElementById('reverve_field_table').rows.length - 6) + '][Value]" placeholder="格式：红色|蓝色|黄色" />';
        newrow.insertCell(3).innerHTML = '<a href="javascript:void(0);" onclick="document.getElementById(\'reverve_field_table\').deleteRow(this.parentNode.parentNode.rowIndex);"> <img src="/static/member/images/ico/del.gif" hspace="5" /></a>';
    }
	
	
</script>

</body>
</html>