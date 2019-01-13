<?php
require_once('../global.php');
$DB->showErr=false;

if(empty($_SESSION["BIZ_ID"])){
    header("location:login.php");
}

$stores = $DB->GetRs('biz','Biz_StoresMobile,Biz_StoresAddress,Biz_StoresLng,Biz_StoresLat','where Biz_ID=' . $_SESSION['BIZ_ID'] . ' and Users_ID="'.$_SESSION['Users_ID'].'"');

if($_POST)
{
    //开始事务定义
    $Flag=true;
    $msg="";
    mysql_query("begin");
    $Data=array(
        "Biz_StoresMobile"=>$_POST["Telephone"],
        "Biz_StoresAddress"=>$_POST["Address"],
        "Biz_StoresLng"=>$_POST["PrimaryLng"],
        "Biz_StoresLat"=>$_POST["PrimaryLat"],
        'Is_BizStores'=>$_POST['Is_BizStores'],
    );
    $Add=$DB->set("biz",$Data,'where Biz_ID=' . $_SESSION['BIZ_ID'] . ' and Users_ID="'.$_SESSION['Users_ID'].'"');
    $Flag=$Flag&&$Add;

    if($Flag){
        mysql_query("commit");
        echo '<script language="javascript">alert("增加成功");history.go(-1);location.reload()</script>';
    }else{
        mysql_query("roolback");
        echo '<script language="javascript">alert("增加失败");history.go(-1);</script>';
    }
    /*
  echo json_encode($Data,JSON_UNESCAPED_UNICODE);
    exit;
  */
}
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
    <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
    <script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
    <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
    body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/stores.css' rel='stylesheet' type='text/css' />
        <script type='text/javascript' src='/static/member/js/stores.js'></script>
        <div class="r_nav">
            <ul>
                <li><a href="skin.php">模板风格</a></li>
                <li><a href="home.php">首页设置</a></li>
                <li><a href="stores.php">门店管理</a></li>
                <li class="cur"><a href="come_stores.php">到店自提管理</a></li>
            </ul>
        </div>
        <div id="stores" class="r_con_wrap">
            <script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $ak_baidu;?>"></script>
            <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
            <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
            <script language="javascript">$(document).ready(stores_obj.stores_init);</script>
            <form id="stores_form" class="r_con_form" method="post" action="come_stores.php">
                <div class="rows">
                    <label>联系电话</label>
                    <span class="input">
          <input class="form_input" maxlength="11" name="Telephone" value="<?= !empty($stores['Biz_StoresMobile']) ? $stores['Biz_StoresMobile'] : ''?>">
          </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>详细地址</label>
                    <span class="input">
          <input name="Address" id="Address" value="<?= !empty($stores['Biz_Stores']) ? $stores['Biz_Stores'] : '黄河路经八路'?>" type="text" class="form_input" size="45" maxlength="100" notnull>
          <span class="primary" id="Primary">定位</span> <font class="fc_red">*</font><br />
          <div class="tips">如果输入地址后点击定位按钮无法定位，请在地图上直接点击选择地点</div>
          <div id="map"></div>
          </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>是否开启到店自提</label>
                    <span class="input" style="font-size:12px; line-height:22px;">
                        <input type="radio" value="0" id="order_0" name="Is_BizStores" checked/><label for="order_0"> 关闭&nbsp;</label><br/>
                        <input type="radio" value="1" id="order_1" name="Is_BizStores"/><label for="order_1"> 开启&nbsp;&nbsp; </label><br/>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label></label>
                    <span class="input">
          <input type="submit" class="btn_green" name="submit_button" value="提交保存" />
          <a href="" class="btn_gray">返回</a></span>
                    <div class="clear"></div>
                </div>
                <input type="hidden" name="PrimaryLng" value="<?= !empty($stores['Biz_StoresLng']) ? $stores['Biz_StoresLng'] : '113.676832'?>">
                <input type="hidden" name="PrimaryLat" value="<?= !empty($stores['Biz_StoresLat']) ? $stores['Biz_StoresLat'] : '34.780696'?>">
                <input type="hidden" id="ImgPath" name="ImgPath" value="" />
            </form>
        </div>
    </div>
</div>
</body>
</html>