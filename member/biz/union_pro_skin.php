<?php
require_once(CMS_ROOT . '/include/helper/tools.php');
require_once(CMS_ROOT . '/include/helper/url.php');
require_once(CMS_ROOT . '/include/library/phpqrcode/phpqrcode.php');

!defined('PROTOCAL') && define('PROTOCAL',((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://');

if (empty($_SESSION["Users_Account"])) {
    header("location:/member/login.php");
    exit;
}

$rsConfig = $DB->GetRs("biz_config", "Pro_Skin_ID", "where Users_ID='" . $_SESSION["Users_ID"] . "'");
$flag = true;
if (empty($rsConfig)) {
    $flag = $DB->Add('biz_config',['Pro_Skin_ID'=>2,'Users_ID'=>$_SESSION['Users_ID'],'Skin_ID'=>2]);
}
if (isset($_GET["action"]) && $flag) {
    if ($_GET["action"] == "set") {
        $Skin_ID = (int)$_GET["Skin_ID"];
        $rsHome = $DB->GetRs("biz_union_program_skin", "*", "where Pro_Skin_ID=" . $Skin_ID);
        if (!$rsHome) {
            echo '<script language="javascript">alert("传递ID有误!");history.back();</script>';
            exit;
        }
        $data = [
            "Pro_Skin_ID" => $Skin_ID
        ];
        $biz_flag = $DB->Set('biz_config',$data,'where Users_ID="'.$_SESSION["Users_ID"].'"');
        $biz_home = $DB->GetAssoc('biz_union_program_home','*','where Users_ID="'.$_SESSION["Users_ID"].'" and Pro_Skin_ID='.$Skin_ID);
        if(empty($biz_home)){
            $Data=[
                "Pro_Skin_ID" => $Skin_ID,
                "Users_ID" => $_SESSION['Users_ID'],
                "Home_Json" =>$rsHome['Skin_Json']
            ];
            $biz_home_flag = $DB->Add('biz_union_program_home',$Data);
            if($biz_flag && $biz_home_flag){
                echo '<script language="javascript">window.location="' . $_SERVER['HTTP_REFERER'] . '";</script>';
            }else{
                echo '<script language="javascript">alert("操作失败");history.back();</script>';
            }
        }else{
            if($biz_flag){
                echo '<script language="javascript">window.location="' . $_SERVER['HTTP_REFERER'] . '";</script>';
            }else{
                echo '<script language="javascript">alert("操作失败");history.back();</script>';
            }
        }
    }
    exit;
}

$reSkin = $DB->GetAssoc("biz_union_program_skin", "*", "where Skin_Status=1 order by Skin_Index asc");
$path = $_SERVER["REQUEST_URI"];
$my_file = basename($path, '.php');
$jcurid = 6;

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
    <script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
    <script type=text/javascript>
        function SelectSkinID(Skin_ID) {
            if(confirm('您确定要选择此风格吗？')){
                location.href = "union_pro_skin.php?action=set&Skin_ID=" + Skin_ID;
            }
        }
        $(function(){
            $("#skin ul li").hover(function(){
                if ($(this).index() != 0) {
                    $(this).find(".qrcode").slideDown(100);
                }
            },function(){
                if ($(this).index() != 0) {
                    $(this).find(".qrcode").slideToggle(100);
                }
            });
        });
    </script>
</head>
<body>
<!--[if lte IE 9]>
<script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/bizunion_menubar.php');?>
    <div class="iframe_content">
        <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/member/js/shop.js'></script>
        <div id="skin" class="r_con_wrap">
            <ul>
                <?php if (!empty($reSkin)) {
                    foreach ($reSkin as $k => $v) {
                        $i = 0; ?>
                        <li class="<?= $v['Pro_Skin_ID'] == $rsConfig['Pro_Skin_ID'] ? "cur" : "" ?>">
                            <div class="item" <?= $v['Pro_Skin_ID'] == $rsConfig['Pro_Skin_ID'] ? '' : 'onClick="SelectSkinID(' . $v['Pro_Skin_ID'] . ');"' ?>>
                                <div class="img"><img src="/static/biz/images/skin/<?= $v["Pro_Skin_ID"] ?>.jpg"/></div>
                                <div class="title"><?=$v['Skin_Name'] ?></div>
                            </div>
                        </li>
                    <?php }
                } ?>
            </ul>
            <div class="clear"></div>
        </div>
    </div>
</div>
</body>
</html>
