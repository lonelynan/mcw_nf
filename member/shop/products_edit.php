?><?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/lib_products.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/shipping.php');

if (empty($_SESSION["Users_Account"])) {
    header("location:/member/login.php");
    exit;
}
$ProductsID = empty($_REQUEST['ProductsID']) ? 0 : $_REQUEST['ProductsID'];
$rsProducts = $DB->GetRs("shop_Products", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "' and Products_ID=" . $ProductsID);
$rsBiz = $DB->GetRs("biz", "Group_ID,Finance_Type,Finance_Rate", "where Biz_ID=" . $rsProducts["Biz_ID"]);
$rsConfig = $DB->GetRs("users_payconfig", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "'");

//判断系统是否开启显示抢购活动，并且在使用方范围内
$active_info = $DB->GetRs('active', 'Active_ID', 'where Users_ID = "' . $_SESSION["Users_ID"] . '" and Status = 1 and Type_ID = 5 and starttime < ' . time() . ' and stoptime > ' . time());
$flashsale = !empty($active_info) ? 1 : 0;

if ($_POST) {
    if ($rsProducts['Products_Union_ID'] == 0) {
        $Category = array();
        if (!isset($_POST["Category"])) {
            echo '<script language="javascript">alert("请选择分类！");history.back();</script>';
            exit;
        }
    }

    if (!empty($_POST["Index"])) {
        if ($_POST["Index"] < 0) {
            echo '<script language="javascript">alert("产品排序必须大于零");history.back();</script>';
            exit;
        }
    }

    if (!is_numeric($_POST['platForm_Income_Reward']) || $_POST['platForm_Income_Reward'] > 100 || $_POST['platForm_Income_Reward'] <= 0) {
        echo '<script language="javascript">alert("请设置合理的网站所得比例");history.back();</script>';
        exit();
    }


    if (!is_numeric($_POST['nobi_ratio']) || !is_numeric($_POST['area_Proxy_Reward']) || !is_numeric($_POST['sha_Reward']) || !is_numeric($_POST['commission_ratio']) || !is_numeric($_POST['salesman_ratio']) || ($_POST['nobi_ratio'] + $_POST['area_Proxy_Reward'] + $_POST['commission_ratio'] + $_POST['sha_Reward'] + $_POST['salesman_ratio']) > 100 || ($_POST['nobi_ratio'] + $_POST['area_Proxy_Reward'] + $_POST['commission_ratio'] + $_POST['sha_Reward'] + $_POST['salesman_ratio']) < 0) {
        echo '<script language="javascript">alert("请设置合理的佣金分配比例");history.back();</script>';
        exit();
    }
    foreach ($_POST['salesman_level_ratio'] as $k => $v) {
        if (!is_numeric($v)) {
            echo '<script language="javascript">alert("请设置合理的各级业务提成比例");history.back();</script>';
            exit();
        }
    }
    if ($_POST['salesman_ratio'] < 0 || array_sum($_POST['salesman_level_ratio']) > 100 || $_POST['salesman_level_ratio'][0] < 0 || $_POST['salesman_level_ratio'][1] < 0 || $_POST['salesman_level_ratio'][2] < 0) {
        echo '<script language="javascript">alert("请设置合理的各级业务提成比例");history.back();</script>';
        exit();
    }

    $Category_Id = !empty($_POST["Category"]) ? $_POST["Category"] : 0;
    if (empty($Category_Id)) {
        echo '<script language="javascript">alert("请选择分类！");history.back();</script>';
        exit();
    }
    $cateInfo = $DB->getRs("shop_category", "Category_ParentID", "where Users_ID='" . $_SESSION["Users_ID"] . "' and Category_ID = " . $Category_Id);

    $Data = array(
        "Products_Index" => empty($_POST["Index"]) ? 9999 : intval($_POST["Index"]),
        "Products_Integration" => empty($_POST['Products_Integration']) ? "0" : $_POST['Products_Integration'],
        "Products_Category" => empty($_POST["Category"]) ? "0" : $_POST["Category"],
        "Father_Category" => !empty($cateInfo['Category_ParentID']) ? $cateInfo['Category_ParentID'] : 0,
        "Products_Distributes" => empty($_POST['Distribute']) ? "" : json_encode($_POST['Distribute'], JSON_UNESCAPED_UNICODE),
        "Products_IsNew" => isset($_POST["IsNew"]) ? $_POST["IsNew"] : 0,
        "Products_IsHot" => isset($_POST["IsHot"]) ? $_POST["IsHot"] : 0,
        "Products_IsRecommend" => isset($_POST["IsRecommend"]) ? $_POST["IsRecommend"] : 0,
        "Products_IsPaysBalance" => isset($_POST["IsPaysBalance"]) ? $_POST["IsPaysBalance"] : 0,
        "Products_Status" => $_POST["Status"],
        "commission_ratio" => isset($_POST["commission_ratio"]) ? intval($_POST["commission_ratio"]) : 0,
        "nobi_ratio" => isset($_POST["nobi_ratio"]) ? intval($_POST["nobi_ratio"]) : 0,
        "platForm_Income_Reward" => isset($_POST['platForm_Income_Reward']) ? intval($_POST['platForm_Income_Reward']) : 0,
        "area_Proxy_Reward" => isset($_POST['area_Proxy_Reward']) ? intval($_POST['area_Proxy_Reward']) : 0,
        "sha_Reward" => isset($_POST['sha_Reward']) ? intval($_POST['sha_Reward']) : 0,
        "salesman_ratio" => isset($_POST["salesman_ratio"]) ? intval($_POST["salesman_ratio"]) : 0,
        "salesman_level_ratio" => empty($_POST['salesman_level_ratio']) ? "" : json_encode($_POST['salesman_level_ratio'], JSON_UNESCAPED_UNICODE)

    );


    $Flag = $DB->Set("shop_Products", $Data, "where Users_ID='" . $_SESSION["Users_ID"] . "' and Products_ID=" . $ProductsID);
    if ($Flag) {
        echo '<script language="javascript">alert("修改成功");window.location="products.php";</script>';
    } else {
        echo '<script language="javascript">alert("保存失败");history.back();</script>';
    }
    exit;
} else {
    $parameter_list = json_decode($rsProducts['Products_Parameter'], true);  //参数
    $shop_config = shop_config($_SESSION["Users_ID"]);
    $dis_config = dis_config($_SESSION["Users_ID"]);

    $Shop_Commision_Reward_Arr = array();
    if (!is_null($shop_config['Shop_Commision_Reward_Json'])) {
        $Shop_Commision_Reward_Arr = json_decode($shop_config['Shop_Commision_Reward_Json'], true);
    }

    $JSON = json_decode($rsProducts['Products_JSON'], true);
    $distribute_list = $rsProducts['Products_Distributes'] ? json_decode($rsProducts['Products_Distributes'], true) : array(); //分佣金额列表

    $salesman_ratio_list = $rsProducts['salesman_level_ratio'] ? json_decode($rsProducts['salesman_level_ratio'], true) : array(); //分佣金额列表
    if (!empty($rsProducts['Products_Type'])) {
        $FinanceType = $rsProducts['Products_FinanceType'];
        $product_attr_html = $rsProducts['tableeval'];
    } else {
        $product_attr_html = '';
    }

    $catids = substr($rsProducts["Products_Category"], 1, -1);
    $Categorys = explode(",", $catids);
    $ordertype = 0;
    if ($rsProducts["Products_IsVirtual"] == 1 && $rsProducts["Products_IsRecieve"] == 1) {
        $ordertype = 2;
    } elseif ($rsProducts["Products_IsVirtual"] == 1) {
        $ordertype = 1;
    }
    $cate_name = array();
    $DB->Get("shop_category", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "' and Category_ID = '" . $rsProducts["Products_Category"] . "' order by Category_Index asc, Category_ID asc");
    while ($r = $DB->fetch_assoc()) {
        if ($r["Category_ParentID"] == 0 && empty($cate_name[$r["Category_ID"]])) {
            $cate_name[$r["Category_ID"]]["name"] = $r["Category_Name"];
        } else {
            $cate_name[$r["Category_ParentID"]]["child"][] = $r["Category_Name"];
        }
    }

    $shop_category = array();
    $DB->get("shop_category", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "' order by Category_ParentID asc,Category_Index asc");
    while ($r = $DB->fetch_assoc()) {
        if ($r["Category_ParentID"] == 0) {
            $shop_category[$r["Category_ID"]] = $r;
        } else {
            $shop_category[$r["Category_ParentID"]]["child"][] = $r;
        }
    }

    $IsStore = 0;
    if (!empty($rsBiz["Group_ID"])) {
        $rsGroup = $DB->GetRs("biz_group", "Group_IsStore", "where Group_ID=" . $rsBiz["Group_ID"]);
        if ($rsGroup) {
            $IsStore = $rsGroup["Group_IsStore"];
        }
    }
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">

    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>

    <link href='/static/member/css/main.css?t=8' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
    <script type='text/javascript' src='/static/member/js/products_attr_helper.js'></script>
    <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css"/>
    <script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
    <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
    <script>
        KindEditor.ready(function (K) {
            K.create('textarea[name="Description"]', {
                themeType: 'simple',
                filterMode: false,
                uploadJson: '/member/upload_json.php?TableField=web_column&Users_ID=<?php echo $_SESSION["Users_ID"];?>',
                fileManagerJson: '/member/file_manager_json.php',
                allowFileManager: true

            });
            var editor = K.editor({
                uploadJson: '/member/upload_json.php?TableField=web_article',
                fileManagerJson: '/member/file_manager_json.php',
                showRemote: true,
                allowFileManager: true
            });
            K('#ImgUpload').click(function () {
                if (K('#PicDetail').children().length >= 5) {
                    alert('您上传的图片数量已经超过5张，不能再上传！');
                    return;
                }
                editor.loadPlugin('image', function () {
                    editor.plugin.imageDialog({
                        clickFn: function (url, title, width, height, border, align) {
                            K('#PicDetail').append('<div><a href="' + url + '" target="_blank"><img src="' + url + '" /></a> <span onclick="$(this).parent().remove();">删除</span><input type="hidden" name="JSON[ImgPath][]" value="' + url + '" /></div>');
                            editor.hideDialog();
                        }
                    });
                });
            });

            K('#PicDetail div span').click(function () {
                K(this).parent().remove();
            });
        })
        function insertRow() {
            var newrow = document.getElementById('wholesale_price_list').insertRow(-1);
            newcell = newrow.insertCell(-1);
            newcell.innerHTML = '数量： <input type="text" name="JSON[Wholesale][' + (document.getElementById('wholesale_price_list').rows.length - 2) + '][Qty]" value="" class="form_input" size="5" maxlength="3" /> 价格：￥ <input type="text" name="JSON[Wholesale][' + (document.getElementById('wholesale_price_list').rows.length - 2) + '][Price]" value="" class="form_input" size="5" maxlength="10" /><a href="javascript:;" onclick="document.getElementById(\'wholesale_price_list\').deleteRow(this.parentNode.parentNode.rowIndex);"> <img src="/static/member/images/ico/del.gif" hspace="5" /></a>';
        }
    </script>
</head>

<body>
<!--[if lte IE 9]>
<script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
    .dislevelcss {
        float: left;
        margin: 5px 0px 0px 8px;
        text-align: center;
        border: solid 1px #858585;
        padding: 5px;
    }

    .dislevelcss th {
        border-bottom: dashed 1px #858585;
        font-size: 16px;
    }

    .error {
        color: #f00;
        border-color: #f00;
    }
</style>
<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css'/>
        <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script>
        <script type='text/javascript' src='/static/member/js/shop.js'></script>
        <script type='text/javascript'>
            $(document).ready(shop_obj.products_edit_init);
        </script>
        <div class="r_nav">
            <ul>
                <li class="cur"><a href="products.php">产品列表</a></li>
                <li class=""><a href="category.php">产品分类</a></li>
                <li class=""><a href="commit.php">产品评论</a></li>
                <li class=""><a href="commision_setting.php">佣金设置</a></li>
            </ul>
        </div>
        <div id="products" class="r_con_wrap">
            <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css'/>
            <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
            <form class="r_con_form" id="product_edit_form" method="post" action="products_edit.php">

                <div class="rows">
                    <label>产品排序</label>
                    <span class="input">
                        <input type="text" name="Index" id="Index" value="<?= $rsProducts["Products_Index"] ?>" class="form_input" size="10" maxlength="100"/><span class="tips"> 注：数字越小，越往前（必须大于0），为0则表示默认</span>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>产品名称</label>
                    <span class="input">
                        <input type="text" name="Name" id="Name" value="<?= $rsProducts["Products_Name"] ?>" class="form_input_disable" size="35" maxlength="100" disabled="disabled" notnull/>
                    <font class="fc_red">*</font></span>
                    <div class="clear"></div>
                </div>

                <?php if ($rsProducts['Products_Union_ID'] == 0) { ?>
                    <div class="rows">
                        <label>商城分类</label>
                        <span class="input">
                            <a href="#select_category">[选择分类]</a><br/>
                            <p style="margin:5px 0px; padding:0px; font-size:12px; color:#999">已选择分类：</p>
                            <div id="classs">
                                <?php foreach ($cate_name as $value) { ?>
                                    <p style="margin:5px 0px; padding:0px; font-size:12px; color:#666">
                                        <?php if (!empty($value["name"])) { ?>
                                            <font style="color:#333; font-size:12px;"><?= $value["name"] ?></font>
                                        <?php } ?>
                                        <?php if (!empty($value["child"])) { ?>
                                            <?php foreach ($value["child"] as $v) { ?>
                                                &nbsp;&nbsp;<?= $v ?>
                                            <?php }
                                        } ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </span>
                        <div class="clear"></div>
                    </div>
                <?php } ?>

                <div class="rows">
                    <label>产品价格</label>
                    <span class="input price">
                        原价:￥
                        <input type="text" name="PriceY" id="PriceY" value="<?= $rsProducts["Products_PriceY"] ?>" class="form_input_disable" size="5" maxlength="10" disabled="disabled"/> <font class="fc_red">*</font>
                        &nbsp;&nbsp;现价:￥
                        <input type="text" name="PriceX" id="PriceX" value="<?= $rsProducts["Products_PriceX"] ?>" class="form_input_disable" size="5" maxlength="10" disabled="disabled"/> <font class="fc_red">*</font>
                    </span>
                    <div class="clear"></div>
                </div>

                <!--限时抢购逻辑开始-->
                <?php if(!empty($flashsale) && $rsProducts["flashsale_flag"]){?>
                    <div class="rows">
                        <label>是否参与限时抢购</label>
                        <span class="input">
                        <input type="checkbox" name="is_flashsale" value="1" id="is_flashsale" <?= $rsProducts["flashsale_flag"] ? 'checked' : '' ?> disabled="disabled">&nbsp;&nbsp;<span style="color:#888">是否参与限时抢购</span>
                        <span style="color:#888;" id="flashsale_time">
                            &nbsp;&nbsp;抢购价格:&nbsp;&nbsp;<input type="text" class="form_input_disable" name="flashsale_pricex" id="flashsale_pricex" placeholder="请输入限时抢购价格" value="<?= $rsProducts['flashsale_pricex'] ?>" disabled="disabled"/>
                        </span>
                    </span>
                        <div class="clear"></div>
                    </div>
                <?php }?>
                <!--限时抢购逻辑结束-->

                <!--拼团逻辑开始-->
                <div class="rows">
                    <label>是否参与拼团</label>
                    <span class="input">
                        <input type="checkbox" name="is_pintuan" value="1" id="is_pintuan" <?= $rsProducts["pintuan_flag"] ? 'checked' : '' ?> disabled="disabled">&nbsp;&nbsp;<span style="color:#888">是否参与拼团</span>
                        <span style="color:#888;<?= $rsProducts["pintuan_flag"] ? '' : 'display: none' ?>" id="pintuan_time">
                            &nbsp;&nbsp;拼团人数:&nbsp;&nbsp;<input type="text" class="form_input_disable" name="pintuan_people" placeholder="请输入拼团人数" value="<?= $rsProducts['pintuan_people'] ?>" disabled="disabled"/>
                            &nbsp;&nbsp;拼团价格:&nbsp;&nbsp;<input type="text" class="form_input_disable" name="pintuan_pricex" placeholder="请输入拼团价格" value="<?= $rsProducts['pintuan_pricex'] ?>" disabled="disabled"/>
                            &nbsp;&nbsp;截止时间:&nbsp;&nbsp;
                            <input placeholder="截止时间" class="form_input_disable" name="pintuan_end_time" value="<?= $rsProducts['pintuan_end_time'] ? date('Y-m-d', $rsProducts['pintuan_end_time']) : date("Y-m-d", strtotime('+12 month')) ?>" disabled="disabled">
                        </span>
                    </span>
                    <div class="clear"></div>
                </div>
                <!--拼团逻辑结束-->

                <?php if ($rsBiz["Finance_Type"] == 1) { ?>
                    <div class="rows">
                        <label>财务结算类型</label>
                        <span class="input">
                            <input type="radio" name="FinanceType" value="0" id="FinanceType_0"<?= $rsProducts["Products_FinanceType"] == 0 ? ' checked' : '' ?> disabled="disabled"/><label for="FinanceType_0"> 按交易额比例</label>&nbsp;&nbsp;
                            <input type="radio" name="FinanceType" value="1" id="FinanceType_1"<?= $rsProducts["Products_FinanceType"] == 1 ? ' checked' : '' ?> disabled="disabled"/><label for="FinanceType_1"> 按供货价</label><br/>
                            <span class="tips">注：若按交易额比例，则网站提成为：产品售价*比例%</span>
                        </span>
                        <div class="clear"></div>
                    </div>

                    <div class="rows" id="FinanceRate"<?= $rsProducts["Products_FinanceType"] == 1 ? ' style="display:none"' : '' ?>>
                        <label>网站提成</label>
                        <span class="input">
                            <input type="text" name="FinanceRate" value="<?= $rsProducts["Products_FinanceRate"] ?>" class="form_input" size="10" disabled="disabled"/> %
                        </span>
                        <div class="clear"></div>
                    </div>

                    <div class="rows" id="PriceS"<?= $rsProducts["Products_FinanceType"] == 0 ? ' style="display:none"' : '' ?>>
                        <label>供货价</label>
                        <span class="input">
                            <input type="text" name="PriceS" id="Products_PriceS" value="<?= $rsProducts["Products_PriceS"]; ?>" class="form_input_disable" size="10" disabled="disabled"/> 元&nbsp;&nbsp;<span class="tips">此产品网站提成<font style="color:#F60"> <?= $rsProducts["Products_PriceX"] - $rsProducts["Products_PriceS"] ?> </font>元</span>
                        </span>
                        <div class="clear"></div>
                    </div>
                <?php } ?>

                <!--edit in 20160321-->
                <div class="rows disnone">
                    <label>发放比例</label>
                    <span class="input price">
                        <span>%</span>
                        <input type="text" name="platForm_Income_Reward" value="<?php if (!empty($rsProducts["platForm_Income_Reward"]) || $rsProducts["platForm_Income_Reward"] == 0) {
                            echo $rsProducts["platForm_Income_Reward"];
                        } elseif (isset($Shop_Commision_Reward_Arr['platForm_Income_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['platForm_Income_Reward'];
                        } else {
                            echo "0";
                        } ?>" class="form_input commision_config" data-value="<?php if (!empty($rsProducts["platForm_Income_Reward"])) {
                            echo $rsProducts["platForm_Income_Reward"];
                        } elseif (isset($Shop_Commision_Reward_Arr['platForm_Income_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['platForm_Income_Reward'];
                        } else {
                            echo "0";
                        } ?>" size="5" maxlength="10" notnull/>
                        <span>当前值：<?=$rsProducts["platForm_Income_Reward"]?>&nbsp;&nbsp;&nbsp;(发放金额所占网站利润的百分比；小于100%大于0%；)</span>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows disnone">
                    <label>佣金比例</label>
                    <span class="input price">
                        <span>%</span>
                        <input type="text" name="commission_ratio" value="<?php if (!empty($rsProducts["commission_ratio"]) || $rsProducts["commission_ratio"] == 0) {
                            echo $rsProducts["commission_ratio"];
                        } elseif (isset($Shop_Commision_Reward_Arr['commission_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['commission_Reward'];
                        } else {
                            echo "0";
                        } ?>" class="form_input commision_config" data-value="<?php if (!empty($rsProducts["commission_ratio"])) {
                            echo $rsProducts["commission_ratio"];
                        } elseif (isset($Shop_Commision_Reward_Arr['commission_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['commission_Reward'];
                        } else {
                            echo "0";
                        } ?>" size="5" maxlength="10" notnull/><span>当前值：<?=$rsProducts["commission_ratio"]?>&nbsp;&nbsp;&nbsp;(佣金所占发放比例的百分比)</span>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows disnone">
                    <label>业务比例</label>
                    <span class="input price">
                        <span>%</span>
                        <input type="text" name="salesman_ratio" value="<?php if (!empty($rsProducts["salesman_ratio"] || $rsProducts["salesman_ratio"] == 0)) {
                            echo $rsProducts["salesman_ratio"];
                        } elseif (isset($Shop_Commision_Reward_Arr['salesman_ratio'])) {
                            echo $Shop_Commision_Reward_Arr['salesman_ratio'];
                        } else {
                            echo "0";
                        } ?>" class="form_input commision_config" data-value="<?php if (!empty($rsProducts["salesman_ratio"])) {
                            echo $rsProducts["salesman_ratio"];
                        } elseif (isset($Shop_Commision_Reward_Arr['salesman_ratio'])) {
                            echo $Shop_Commision_Reward_Arr['salesman_ratio'];
                        } else {
                            echo "0";
                        } ?>" size="5" maxlength="10" notnull/><span>当前值：<?=$rsProducts["salesman_ratio"]?>&nbsp;&nbsp;&nbsp;(业务提成所占发放比例的百分比)</span>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows disnone">
                    <label>爵位奖励比例</label>
                    <span class="input price">
                        <span>%</span>
                        <input type="text" name="nobi_ratio" value="<?php if (!empty($rsProducts["nobi_ratio"]) || $rsProducts["nobi_ratio"] == 0) {
                            echo $rsProducts["nobi_ratio"];
                        } elseif (isset($Shop_Commision_Reward_Arr['noBi_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['noBi_Reward'];
                        } else {
                            echo "0";
                        } ?>" class="form_input commision_config" data-value="<?php if (!empty($rsProducts["nobi_ratio"])) {
                            echo $rsProducts["nobi_ratio"];
                        } elseif (isset($Shop_Commision_Reward_Arr['noBi_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['noBi_Reward'];
                        } else {
                            echo "0";
                        } ?>" size="5" maxlength="10" notnull/>
                        <span>当前值：<?=$rsProducts["nobi_ratio"]?>&nbsp;&nbsp;&nbsp;(所占发放比例的百分比)</span>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows disnone">
                    <label>区域代理比例</label>
                    <span class="input price">
                    <span>%</span>
                        <input type="text" name="area_Proxy_Reward" value="<?php if (!empty($rsProducts["area_Proxy_Reward"]) || $rsProducts["area_Proxy_Reward"] == 0) {
                            echo $rsProducts["area_Proxy_Reward"];
                        } elseif (isset($Shop_Commision_Reward_Arr['area_Proxy_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['area_Proxy_Reward'];
                        } else {
                            echo "0";
                        } ?>" class="form_input commision_config" data-value="<?php if (!empty($rsProducts["area_Proxy_Reward"])) {
                            echo $rsProducts["area_Proxy_Reward"];
                        } elseif (isset($Shop_Commision_Reward_Arr['area_Proxy_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['area_Proxy_Reward'];
                        } else {
                            echo "0";
                        } ?>" size="5" maxlength="10" notnull/>
                        <span>当前值：<?=$rsProducts["area_Proxy_Reward"]?>&nbsp;&nbsp;&nbsp;(所占发放比例的百分比)</span>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows disnone">
                    <label>股东佣金比例</label>
                    <span class="input price">
                        <span>%</span>
                        <input type="text" name="sha_Reward" value="<?php if (!empty($rsProducts["sha_Reward"]) || $rsProducts["sha_Reward"] == 0) {
                            echo $rsProducts["sha_Reward"];
                        } elseif (isset($Shop_Commision_Reward_Arr['sha_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['sha_Reward'];
                        } else {
                            echo "0";
                        } ?>" class="form_input commision_config" data-value="<?php if (!empty($rsProducts["sha_Reward"])) {
                            echo $rsProducts["sha_Reward"];
                        } elseif (isset($Shop_Commision_Reward_Arr['sha_Reward'])) {
                            echo $Shop_Commision_Reward_Arr['sha_Reward'];
                        } else {
                            echo "0";
                        } ?>" size="5" maxlength="10" notnull/>
                        <span>当前值：<?=$rsProducts["sha_Reward"]?>&nbsp;&nbsp;&nbsp;(所占发放比例的百分比)</span>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows disnone">
                    <label>佣金返利<b class="red mousehand" id="allchange">（全部统一）</b></label>
                    <span class="input">
                        <?php
                        $dislevel = $DB->Get("distribute_level", "Level_ID,Users_ID,Level_Name", "where Users_ID='" . $_SESSION["Users_ID"] . "'");
                        while ($dislevelarr = $DB->fetch_assoc()) {
                            $dislevelarrs[] = $dislevelarr;
                            $disidarr[] = $dislevelarr['Level_ID'];
                        }
                        $jsondisidarr = json_encode($disidarr, JSON_UNESCAPED_UNICODE);
                        $dislevelcont = count($dislevelarrs);
                        foreach ($dislevelarrs as $key => $disinfo) {
                            ?>
                            <div class="dislevelcss">
                            <table id="11" class="item_data_table" border="0" cellpadding="3" cellspacing="0">
                            <tr><th><?= $disinfo['Level_Name'] ?></th></tr>
                                <?php
                                $arr = array('一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
                                $level = $dis_config['Dis_Self_Bonus'] ? $dis_config['Dis_Level'] + 1 : $dis_config['Dis_Level'];
                                for ($i = 0; $i < $level; $i++) {
                                    ?>
                                    <tr>
                                        <td>
                                        <?php if ($dis_config['Dis_Self_Bonus'] == 1 && $i == $dis_config['Dis_Level']) { ?>
                                            自销佣金
                                        <?php } else { ?>
                                            <?php echo $arr[$i] ?>级
                                        <?php } ?>
                                            &nbsp;&nbsp;%
                                            <input id="dischange<?= $disinfo['Level_ID'] . $i ?>" name="Distribute[<?= $disinfo['Level_ID'] ?>][<?php echo $i; ?>]" value="<?php if (!empty($distribute_list[$disinfo['Level_ID']][$i])) {
                                                echo $distribute_list[$disinfo['Level_ID']][$i];
                                            } elseif (isset($Shop_Commision_Reward_Arr['Distribute'][$disinfo['Level_ID']][$i])) {
                                                echo $Shop_Commision_Reward_Arr['Distribute'][$disinfo['Level_ID']][$i];
                                            } else {
                                                echo "0";
                                            } ?>" class="form_input commision_config" data-value="<?php if (!empty($distribute_list[$disinfo['Level_ID']][$i])) {
                                                echo $distribute_list[$disinfo['Level_ID']][$i];
                                            } elseif (isset($Shop_Commision_Reward_Arr['Distribute'][$disinfo['Level_ID']][$i])) {
                                                echo $Shop_Commision_Reward_Arr['Distribute'][$disinfo['Level_ID']][$i];
                                            } else {
                                                echo "0";
                                            } ?>" size="5" maxlength="10" type="text">
                                            当前值：<?=isset($distribute_list[$disinfo['Level_ID']][$i]) ? $distribute_list[$disinfo['Level_ID']][$i] : 0?>&nbsp;&nbsp;&nbsp;(佣金比例的百分比)
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                            </div>
                        <?php } ?>
                    </span>
                    <div class="clear"></div>
                </div>
                <script>
                    function Distribute(obj) {
                        var curVal = $(obj).val();
                        var topobj = $(obj).parent().parent().parent().find("input");
                        var count = 0;
                        topobj.each(function () {
                            count += parseFloat($(this).val());
                        });
                        if (count > 100) {
                            topobj.addClass("error");
                            topobj.first().focus();
                            alert("分销商比例不能大于100");
                            return false;
                        } else {
                            topobj.removeClass("error");
                        }
                    }
                </script>
                <div class="rows disnone">
                    <label>各级业务提成比例</label>
                    <span class="input">
                        <table>
                            <tr>
                                <td>
                                    一级业务<span>%</span>
                                    <input type="text" name="salesman_level_ratio[0]" value="<?php if (!empty($salesman_ratio_list[0])) {
                                        echo $salesman_ratio_list[0];
                                    } elseif (isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][0])) {
                                        echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][0];
                                    } else {
                                        echo "0";
                                    } ?>" class="form_input commision_config" data-value="<?php if (!empty($salesman_ratio_list["salesman_level_ratio"][0])) {
                                        echo $salesman_ratio_list["salesman_level_ratio"][0];
                                    } elseif (isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][0])) {
                                        echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][0];
                                    } else {
                                        echo "0";
                                    } ?>" size="5" maxlength="10" notnull/>
                                    <span>当前值：<?=isset($salesman_ratio_list[0]) ? $salesman_ratio_list[0] : 0?>&nbsp;&nbsp;&nbsp;(业务比例的百分比)</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    二级业务<span>%</span>
                                    <input type="text" name="salesman_level_ratio[1]" value="<?php if (!empty($salesman_ratio_list[1])) {
                                        echo $salesman_ratio_list[1];
                                    } elseif (isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][1])) {
                                        echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][1];
                                    } else {
                                        echo "0";
                                    } ?>" class="form_input commision_config" data-value="<?php if (!empty($salesman_ratio_list["salesman_level_ratio"][1])) {
                                        echo $salesman_ratio_list["salesman_level_ratio"][1];
                                    } elseif (isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][1])) {
                                        echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][1];
                                    } else {
                                        echo "0";
                                    } ?>" size="5" maxlength="10" notnull/>
                                    <span>当前值：<?=isset($salesman_ratio_list[1]) ? $salesman_ratio_list[1] : 0?>&nbsp;&nbsp;&nbsp;(业务比例的百分比)</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    三级业务<span>%</span>
                                    <input type="text" name="salesman_level_ratio[2]" value="<?php if (!empty($salesman_ratio_list[2])) {
                                        echo $salesman_ratio_list[2];
                                    } elseif (isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][2])) {
                                        echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][2];
                                    } else {
                                        echo "0";
                                    } ?>" class="form_input commision_config" data-value="<?php if (!empty($salesman_ratio_list["salesman_level_ratio"][2])) {
                                        echo $salesman_ratio_list["salesman_level_ratio"][2];
                                    } elseif (isset($Shop_Commision_Reward_Arr['salesman_level_ratio'][2])) {
                                        echo $Shop_Commision_Reward_Arr['salesman_level_ratio'][2];
                                    } else {
                                        echo "0";
                                    } ?>" size="5" maxlength="10" notnull/>
                                    <span>当前值：<?=isset($salesman_ratio_list[2]) ? $salesman_ratio_list[2] : 0?>&nbsp;&nbsp;&nbsp;(业务比例的百分比)</span>
                                </td>
                            </tr>
                        </table>
                    </span>
                    <div class="clear"></div>
                </div>

                <style type="text/css">
                    .disnone {
                        display: none;
                        background: #EEE;
                    }

                    .custom {
                        position: fixed;
                        top: 30%;
                        background: blue;
                        color: #FFF;
                        padding: 5px 10px;
                        right: 25px;
                        width: 90px;
                        height: 30px;
                        line-height: 30px;
                        overflow: hidden;
                    }

                    .custom a {
                        color: #FFF;
                    }
                </style>
                <div class="custom"><a href="javascript:void(0);" class="show_commision">显示佣金设置</a><a href="javascript:void(0);" class="close_commision">隐藏佣金设置</a></div>

                <div class="rows">
                    <label>库存</label>
                    <span class="input">
                        <input type="text" name="Count" id="totalCount" value="<?= $rsProducts["Products_Count"] ?>" class="form_input_disable" size="5" maxlength="10" disabled="disabled"/> <span class="tips">&nbsp;注:若不限则填写10000.</span>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>产品重量</label>
                    <span class="input">
                        <input type="text" name="Weight" id="Products_Weight" value="<?= $rsProducts["Products_Weight"] ?>" notnull class="form_input_disable" size="5" disabled="disabled"/>&nbsp;&nbsp;千克
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>购买送积分</label>
                    <span class="input price">
                        <input type="text" name="Products_Integration" value="<?= $rsProducts["Products_Integration"] ?>" class="form_input" size="5" maxlength="10" notnull data-type="number"/>&nbsp;&nbsp;<span>分</span><span class="msg"></span>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>产品图片</label>
                    <span class="input">
                        <span class="upload_file">
                            <div>
                                <!--<div class="up_input">-->
                                    <!--<input type="button" id="ImgUpload" value="添加图片" style="width:80px;" />-->
                                <!--</div>-->
                                <!--<div class="tips">共可上传<span id="pic_count">5</span>张图片，图片大小建议：640*640像素</div>-->
                                <div class="clear"></div>
                            </div>
                        </span>
                        <div class="img" id="PicDetail">
                            <?php if (isset($JSON["ImgPath"])) {
                                foreach ($JSON["ImgPath"] as $key => $value) {
                                    ?>
                                    <div>
                                        <a target="_blank" href="<?= $value ?>"> <img src="<?= $value ?>"></a>
                                        <input type="hidden" name="JSON[ImgPath][]" value="<?= $value ?>">
                                    </div>
                                <?php }
                            } ?>
                        </div>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>简短介绍</label>
                    <span class="inputdisable">
                        <textarea name="BriefDescription" class="briefdesc" disabled="disabled">注：商家编辑才能生效，在这里编辑是无效的！<?= $rsProducts["Products_BriefDescription"] ?></textarea>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows" id="type_html">
                    <label>产品类型</label>
                    <span class="input">
                        <select name="TypeID" style="width:180px;cursor: not-allowed;background:#e3e3e3" id="Type_ID" disabled="disabled">
                            <option value="0"<?= $rsProducts["Products_Type"] == 0 ? " selected" : "" ?>>无属性</option>
                            <?php
                            $DB->get("shop_product_type", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "' and Biz_ID=" . $rsProducts["Biz_ID"] . " order by Type_Index asc");
                            while ($rsType = $DB->fetch_assoc()) {
                                echo '<option value="' . $rsType["Type_ID"] . '"' . ($rsProducts["Products_Type"] == $rsType["Type_ID"] ? " selected" : "") . '>' . $rsType["Type_Name"] . '</option>';
                            }
                            ?>
                        </select>
                        <font class="fc_red">*</font>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>产品属性</label>
                    <span class="input" id="attrs"><?= $product_attr_html ?></span>
                    <div class="clear"></div>
                </div>

                <?php if (!empty($parameter_list)) {
                    foreach ($parameter_list as $kk => $vv) { ?>
                        <div class="rows form-group">
                            <label>产品参数</label>
                            <span class="input">
                                <input type="text" class="ProductsParametername disable" name="Products_Parameter[<?= $kk ?>][name]" value="<?= $vv["name"] ?>" class="form_input" size="15" maxlength="30" disabled="disabled"/>
                                <input type="text" class="ProductsParametervalue disable" name="Products_Parameter[<?= $kk ?>][value]" value="<?= $vv["value"] ?>" class="form_input" size="15" maxlength="30" disabled="disabled"/>
                            </span>
                            <div class="clear"></div>
                        </div>
                    <?php }
                } ?>

                <div class="rows">
                    <label>其他属性</label>
                    <span class="input attr">
<!--                        <label>新品:-->
<!--                        <input type="checkbox" value="1" name="IsNew" --><?//= empty($rsProducts["Products_IsNew"]) ? "" : " checked" ?><!-- /></label>&nbsp;|&nbsp;-->
                        <label>热卖推荐:
                        <input type="checkbox" value="1" name="IsHot" <?= empty($rsProducts["Products_IsHot"]) ? "" : " checked" ?> /></label>&nbsp;|&nbsp;
                        <label>首页推荐（是否展示到首页）:
                        <input type="checkbox" value="1" name="IsRecommend" <?= empty($rsProducts["Products_IsRecommend"]) ? "" : " checked" ?> /></label>&nbsp;
                    </span>
                    <div class="clear"></div>
                </div>

                <?php if ($rsConfig["Payment_RmainderEnabled"] == 1) { ?>
                    <div class="rows">
                        <label>特殊属性</label>
                        <span class="input attr">
			                余额支付:
                            <input type="checkbox" value="1" name="IsPaysBalance" <?= empty($rsProducts["Products_IsPaysBalance"]) ? "" : " checked" ?> />&nbsp;|&nbsp;
			            </span>
                        <div class="clear"></div>
                    </div>
                <?php } ?>

                <div class="rows">
                    <label>订单流程</label>
                    <span class="input disable" style="font-size:12px; line-height:22px;">
                        <input type="radio" id="order_0" value="0" name="ordertype"<?= $ordertype == 0 ? ' checked' : '' ?> disabled="disabled"/><label for="order_0"> 实物订单&nbsp;&nbsp;( 买家下单 -> 买家付款 -> 商家发货 -> 买家收货 -> 订单完成 ) </label><br/>
                        <input type="radio" id="order_1" value="1" name="ordertype"<?= $ordertype == 1 ? ' checked' : '' ?> disabled="disabled"/><label for="order_1"> 虚拟订单&nbsp;&nbsp;( 买家下单 -> 买家付款 -> 系统发送消费券码到买家手机 -> 商家认证消费 -> 订单完成 ) </label><br/>
                        <input type="radio" id="order_2" value="2" name="ordertype"<?= $ordertype == 2 ? ' checked' : '' ?> disabled="disabled"/><label for="order_2"> 其他&nbsp;&nbsp;( 买家下单 -> 买家付款 -> 订单完成 ) </label>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>详细介绍</label>
                    <span class="input">
                        <textarea class="ckeditor" name="Description" style="width:700px; height:300px;"><?= $rsProducts["Products_Description"] ?></textarea>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>是否审核</label>
                    <span class="input" style="font-size:12px;">
                        <input type="radio" id="status_0" value="0" name="Status"<?= $rsProducts["Products_Status"] == 0 ? ' checked' : '' ?> /><label for="status_0"> 待审核 </label>&nbsp;&nbsp;
                        <input type="radio" id="status_1" value="1" name="Status"<?= $rsProducts["Products_Status"] == 1 ? ' checked' : '' ?> /><label for="status_1"> 通过审核 </label>
                    </span>
                    <div class="clear"></div>
                </div>

                <div id="select_category" class="lean-modal lean-modal-form">
                    <div class="h">产品分类<a class="modal_close" href="#"></a></div>
                    <div class="catlist" style="height:350px; overflow:auto">
                        <dl>
                            <?php foreach ($shop_category as $first => $items) { ?>
                                <dt>
                                    <qq544731308><?= $items["Category_Name"] ?></qq544731308>
                                </dt>
                                <dd>
                                    <?php
                                    if (!empty($items["child"])) {
                                        foreach ($items["child"] as $second => $item) {
                                            echo '<span><input type="radio" rel="0" name="Category" value="' . $item["Category_ID"] . '"' . (($item["Category_ID"] == $rsProducts['Products_Category']) ? ' checked' : '') . ' id="cate_' . $item["Category_ID"] . '"/> <label for="cate_' . $item["Category_ID"] . '"><qq544731308son>' . $item["Category_Name"] . '</qq544731308son></label></span>';
                                        }
                                    }
                                    ?>
                                </dd>
                            <?php } ?>
                        </dl>
                    </div>
                    <div class="rows">
                        <label></label>
                        <span class="submit"><a class="modal_close" style="border-radius:8px;padding:5px 20px; color:#FFF; text-align:center; background:#3AA0EB" href="#">选好了</a></span>
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="rows">
                    <label></label>
                    <span class="input">
                        <input type="hidden" id="UsersID" value="<?= $_SESSION["Users_ID"] ?>"/>
                        <input type="hidden" name="ProductsID" id="ProductsID" value="<?= $rsProducts["Products_ID"] ?>">
                        <input type="submit" class="btn_green" name="submit_button" value="提交保存"/>
                    </span>
                    <div class="clear"></div>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    /*edit in 20160321*/
    var level = <?=$level?>;
    var dislevelcont = <?=$dislevelcont?>;
    var disidarr = <?=$jsondisidarr?>;
    var fistarr = new Array();
    $("#allchange").click(function () {
        for (i = 0; i < dislevelcont; i++) {
            if (i == 0) {
                for (j = 0; j < level; j++) {
                    fistarr[j] = $("#dischange" + disidarr[i] + j).val();
                }
            } else {
                for (j = 0; j < level; j++) {
                    $("#dischange" + disidarr[i] + j).val(fistarr[j]);
                }
            }
        }
    });

    $('.custom .show_commision').click(function () {
        $('.disnone').show();
        $(this).hide();
        $('.custom .close_commision').show();
    });
    $('.custom .close_commision').click(function () {
        $('.disnone').hide();
        $(this).hide();
        $('.custom .show_commision').show();
        $('.commision_config').each(function () {
            $(this).val($(this).attr('data-value'));
        });
    });
    $("#process").find("input.l-text").each(function () {
        $(this).attr('disabled', 'disabled');
    });

    $(document).on('click', '.modal_close', function () {
        var dtcontent = [];
        var ddcontent = [];
        var select_class = '';
        $('#select_category dt').each(function (i) {
            if ($(this).children('input').attr("checked") == 'checked') {
                dtcontent[i] = $(this).children('qq544731308').html();
            }
            if (typeof(dtcontent[i]) == 'undefined') {
                dtcontent[i] = '&nbsp';
            }
            $(this).next('dd').children('span').each(function () {
                if ($(this).children('input').attr('checked') == 'checked') {
                    if (typeof(ddcontent[i]) == 'undefined') {
                        ddcontent[i] = '&nbsp';
                    }
                    ddcontent[i] += $(this).children('label').children('qq544731308son').html() + '&nbsp';
                }
            })
            if (typeof(ddcontent[i]) == 'undefined') {
                ddcontent[i] = '&nbsp';
            }
            select_class += '<p style="margin:5px 0px; padding:0px; font-size:12px; color:#666"><font style="color:#333; font-size:12px;">' + dtcontent[i] + '</font>&nbsp;' + ddcontent[i] + '</p>';
        })
        $('#classs').html(select_class);
    })
</script>
</body>
</html>