<?php

$DB->showErr=false;
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
$rsConfig=$DB->GetRs("kf_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
if(empty($rsConfig)){
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"KF_Icon"=>'/static/kf/ico/00.png'
	);
	$DB->Add("kf_config",$Data);
	$rsConfig=$DB->GetRs("kf_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
}
if(isset($_GET["action"])){
	if($_GET["action"]=="item"){
		$Flag=$DB->Set("kf_config","KF_".$_GET['field']."=".(empty($_GET['Status'])?1:0),"where Users_ID='".$_SESSION["Users_ID"]."'");
		$Data=array("status"=>1);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}elseif($_GET["action"]=="icon"){
		$ico = '/static/kf/ico/'.(empty($_GET['ico'])?"00":$_GET['ico']).'.png';
		$Flag=$DB->Set("kf_config","KF_Icon='".$ico."'","where Users_ID='".$_SESSION["Users_ID"]."'");
		$Data=array("status"=>1);
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
}
if($_POST){
	$_POST['Code'] = htmlspecialchars($_POST['Code'], ENT_QUOTES);
	 /*edit在线客服20160419--start--*/
        $kftype=$_POST["kftype"];
        if($kftype == 1){
            $qq = trim($_POST["qq"]);
            $Flag=$DB->Set("kf_config",array("KF_Code"=>$_POST["Code"],"kftype"=>$_POST["kftype"],"qq_postion"=>$_POST["qq_postion"],"qq_icon"=>$_POST["qq_icon"],"qq"=>$qq),"where Users_ID='".$_SESSION["Users_ID"]."'");
        }else{
            $Flag=$DB->Set("kf_config",array("KF_Code"=>$_POST["Code"],"kftype"=>$_POST["kftype"]),"where Users_ID='".$_SESSION["Users_ID"]."'");
        }
        /*edit在线客服20160419--end--*/
	if($Flag){
		echo '<script language="javascript">alert("设置成功");window.location="config.php";</script>';
	}else{
		echo '<script language="javascript">alert("设置失败");history.back();</script>';
	}
	exit;
}
$jcurid = 3;
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
<!--/*edit在线客服20160419--start--*/-->
<style>
    .r_con_table td{
        text-align: left;
    }
    .kftable td{
        border-bottom: 0px solid; 
    }
    .kftable  td{
        border-right: 0px solid;
     }
</style>
<!--/*edit在线客服20160419--end--*/-->
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/kf.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/kf.js'></script>
    <script language="javascript">$(document).ready(kf_obj.web_init);</script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/jiebase_menubar.php');?>
    <div id="kf_web" class="r_con_wrap">
	  <!-- /*edit在线客服20160419--start--*/-->
<form id="kfconfig_form" method="post" action="?" style="width:70%;">
	  <div class="table" style="width: 100%;">
		<table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
                    <tr>
                        <td width="20%">客服类型</td>
                        <td width="80%">                           
                            <input type="radio" name="kftype" <?php echo $rsConfig['kftype']==2?'checked':''; ?> value="2" class="kftype2" onclick="aftershow(this)" ><label>第三方客服</label>
                        </td>
                    </tr>
                    <tr>
                        <td>功能模块</td>
                        <td class="last" id="kf_web">
                            <table class="kftable">
                                <tr>
                                    <td>微官网</td>
                                    <td class="last"><img src="/static/member/images/ico/<?php echo $rsConfig['KF_IsWeb']==1?'on':'off' ?>.gif" field="IsWeb" Status="<?php echo $rsConfig['KF_IsWeb']; ?>" /></td>
                                </tr>
                                <tr>
                                    <td>微商城</td>
                                    <td class="last"><img src="/static/member/images/ico/<?php echo $rsConfig['KF_IsShop']==1?'on':'off' ?>.gif" field="IsShop" Status="<?php echo $rsConfig['KF_IsShop']; ?>" /></td>
                                </tr>
                                <tr>
                                    <td>会员中心</td>
                                    <td class="last"><img src="/static/member/images/ico/<?php echo $rsConfig['KF_IsUser']==1?'on':'off' ?>.gif" field="IsUser" Status="<?php echo $rsConfig['KF_IsUser']; ?>" /></td>
                                </tr>
                                <tr>
                                    <td>微砍价</td>
                                    <td class="last"><img src="/static/member/images/ico/<?php echo $rsConfig['KF_kanjia']==1?'on':'off' ?>.gif" field="kanjia" Status="<?php echo $rsConfig['KF_kanjia']; ?>" /></td>
                                </tr>
                                
                            </table>
                        </td>
                    </tr>
		</table>
	  </div>
	  <div> 
		<table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="kftype1" style="display:<?php echo $rsConfig['kftype']==1?'':'none'?>">
                    <tr>
                        <td width="20%">QQ客服设置</td>
                        <td>
                            <table class="kftable">
                               
                                <tr>
                                    <td width="10%">位置</td>
                                    <td width="80%">
                                        <input type="radio" name="qq_postion" value="left" <?php echo $rsConfig['qq_postion']=='left'?'checked':''; ?> ><label>左边</label>&nbsp;
                                        <input type="radio" name="qq_postion" value="right" <?php echo $rsConfig['qq_postion']=='right'?'checked':''; ?> ><label>右边</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>qq号</td>
                                    <td><input type="text" name="qq" value="<?php echo !empty($rsConfig['qq'])?$rsConfig['qq']:'';?> "></td>
                                </tr>
                                <tr>
                                    <td>qq客服图标</td>
                                    <td>
                                        <input type="radio" name="qq_icon" value="1" <?php echo $rsConfig['qq_icon']=='1'?'checked':''; ?>><label><img src="/static/kf/1.gif"></label>&nbsp;
                                        <input type="radio" name="qq_icon" value="2" <?php echo $rsConfig['qq_icon']=='2'?'checked':''; ?>><label><img src="/static/kf/2.gif"></label>&nbsp;
                                        <input type="radio" name="qq_icon" value="3" <?php echo $rsConfig['qq_icon']=='3'?'checked':''; ?>><label><img src="/static/kf/3.gif"></label>&nbsp;
                                        <input type="radio" name="qq_icon" value="4" <?php echo $rsConfig['qq_icon']=='4'?'checked':''; ?>><label><img src="/static/kf/4.gif"></label>&nbsp;
                                        <input type="radio" name="qq_icon" value="5" <?php echo $rsConfig['qq_icon']=='5'?'checked':''; ?>><label><img src="/static/kf/5.gif"></label>&nbsp;
                                    </td>
                                </tr>
                               
                                
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        </td>
                        <td>
                            <input type="submit" value="提交保存">
                        </td>
                    </tr>
		</table>
                <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table" id="kftype2" style="display:<?php echo $rsConfig['kftype']==2?'':'none'?>">
                    <tr>
                        <td width="20%">第三方客服代码</td>
                        <td width="80%">
                           
                                <textarea name="Code" style="width:100%; height:87px;" notnull><?php echo $rsConfig["KF_Code"];?></textarea>
                                <input type="submit" value="提交保存" name="submit_btn" style="margin:15px auto 8px; display:block; height:30px; line-height:30px; background:#3AA0EB; border:none; color:#FFF; width:145px; border-radius:5px; text-align:center; text-decoration:none;">
                           
                        </td>
                    </tr>
		</table>
	  </div>
    </form>
<!--/*edit在线客服20160419--end--*/-->
	  <div class="clear"></div>
	</div>
  </div>
</div>
    <!--/*edit在线客服20160419--start--*/-->
    <script>
        function aftershow(the){
            if (the.checked){
                var type_val = $(the).val();
                if(type_val == 1){
                    $("#kftype1").css('display','');
                    $("#kftype2").css('display','none');
                }else{
                    $("#kftype2").css('display','');
                    $("#kftype1").css('display','none');
                }
            } else {
                 alert('请选择客服类型'); 
            }
        }
    </script>
    <!--/*edit在线客服20160419--end--*/-->
</body>
</html>