<?php
if (empty($_SESSION["Users_Account"])) {
    header("location:/member/login.php");
}
if ($_POST) {

    //开始事务定义

    $Data = array(
        "accept_temp_id" => isset($_POST["accept_temp_id"]) ? $_POST["accept_temp_id"] : 0,
        "get_temp_id" => isset($_POST["get_temp_id"]) ? $_POST["get_temp_id"] : 0,
        "comp_temp_id" => isset($_POST["comp_temp_id"]) ? $_POST["comp_temp_id"] : 0,
    );

    $flag = $DB->Set("shop_config", $Data, "where Users_ID='" . $_SESSION["Users_ID"] . "'");

    if ($flag) {
        echo '<script language="javascript">alert("保存成功");window.location="' . $_SERVER['HTTP_REFERER'] . '";</script>';
    } else {
        echo '<script language="javascript">alert("保存失败");history.go(-1);</script>';
    }
    exit;
} else {

    $rsConfig = $DB->GetRs("shop_config", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "'");
    if (!$rsConfig) {
        echo '<script language="javascript">alert("未找到系统配置");history.go(-1);</script>';
        exit;
    }
}
?>


<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>
</head>
<style>
    .dz_table{border-collapse: collapse;width: 100%; margin: 30px 0 0 30px;}
    .dz_table dd{ text-align: left;color: #888;;
        line-height:30px; padding:6px 0;}
    .dz_tab_dd{width: 145px; text-align: right;display: inline-block;float: left; color: #333;}
    .dz_table input{width:280px; line-height: 30px;border:1px #eee solid;background: none;
        height:30px; margin-right: 6px;}
</style>
<body>
<div id="iframe_page">
    <div class="iframe_content">
        <div id="shopping" class="r_con_wrap">
            <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="dz_table">
                <thead>
                <tr>
                    <td width="34%" style="font-weight: bold;font-size: 20px;">模板消息配置</td>
                    <td width="33%"></td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td valign="top" class="payment">
                        <form action="temp_config.php" method="post">
                            <ul>
                                <li>
                                    <dl>
                                        <dd><span class="dz_tab_dd">接单提醒模板消息ID：</span><input type="text" name="accept_temp_id" value="<?php echo isset($rsConfig["accept_temp_id"]) ? $rsConfig["accept_temp_id"] : '';  ?>"/>(模板ID:AT0328,请勾选【商户名称,订单号,接单时间,接单人,联系电话】)</dd>
                                        <dd><span class="dz_tab_dd">已取货模板消息ID：</span><input type="text" name="get_temp_id" value="<?php echo isset($rsConfig["get_temp_id"]) ? $rsConfig["get_temp_id"] : ''; ?>"/>(模板ID:AT0177,请勾选【订单编号,配送员,联系电话,配送状态】)</dd>
                                        <dd><span class="dz_tab_dd">已完成模板消息ID：</span><input type="text" name="comp_temp_id" value="<?php echo isset($rsConfig["get_temp_id"]) ? $rsConfig["get_temp_id"] : ''; ?>"/>(模板ID:AT0241,请勾选【订单编号,订单状态】)</dd>
                                    </dl>
                                </li>
                            </ul>
                            <div class="submit">
                                <input type="submit" class="btn_green" name="submit_button" value="提交保存"/>
                            </div>
                        </form>
                    </td>

                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>