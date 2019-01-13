<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/public/pageurl.func.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/shop/setting.class.php');

if(empty($_SESSION["Users_Account"])){
    header("location:/member/login.php");
    exit;
}
$jcurid = 7;
$setting = new setting($DB,$_SESSION["Users_ID"]);
$rsConfig = $DB->GetRs("biz_config", "Pro_Skin_ID", "where Users_ID='" . $_SESSION["Users_ID"] . "'");
$flag = true;
if (empty($rsConfig)) {
    $flag = $DB->Add('biz_config',['Pro_Skin_ID'=>2,'Users_ID'=>$_SESSION['Users_ID'],'Skin_ID'=>2]);
    $Skin_ID = 2;
}
$Skin_ID = $DB->GetRs("biz_config","*","where Users_ID='".$_SESSION["Users_ID"]."'")['Pro_Skin_ID'];

$rsHome = $DB->GetRs("biz_union_program_home","*",'where Users_ID="'.$_SESSION["Users_ID"].'" and Pro_Skin_ID='.$Skin_ID);
$biz_skin = $DB->GetRs("biz_union_program_skin","*",'where Pro_Skin_ID='.$Skin_ID);
if(empty($biz_skin)){
    echo '<script language="javascript">alert("请重新选择模版！");history.back();</script>';
    exit;
}
if(empty($rsHome)){
    $Data = array(
        "Users_ID"=>$_SESSION["Users_ID"],
        "Home_Json"=>$biz_skin['Skin_Json'],
        "Pro_Skin_ID"=>$Skin_ID
    );
    $DB->Add("biz_union_program_home",$Data);
    $rsHome = $Data;
}

$Home_Json=json_decode($rsHome['Home_Json'],true);

if($_POST){
    $no=intval($_POST["no"])+1;
    $is_array = is_array($Home_Json[$no-1]['Title']) ? 1 : 0;
    if($is_array==1){
        $_POST["TitleList"]=array();
        foreach($_POST["ImgPathList"] as $key=>$value){
            $_POST["TitleList"][$key]="";
            if(empty($value)){
                unset($_POST["TitleList"][$key]);
                unset($_POST["ImgPathList"][$key]);
                unset($_POST["UrlList"][$key]);
            }
        }
    }
    if(!empty($Home_Json[$no-1])){
        $Home_Json[$no-1]['Title'] = $is_array==1 ? $_POST["TitleList"] : $_POST['Title'];
        $Home_Json[$no-1]['ImgPath'] = $is_array==1 ? $_POST["ImgPathList"] : $_POST['ImgPath'];
        $Home_Json[$no-1]['Url'] = $is_array==1 ? $_POST["UrlList"] : $_POST['Url'];

        $Data=array(
            "Home_Json"=>json_encode($Home_Json,JSON_UNESCAPED_UNICODE),
        );
        $Flag = $DB->Set("biz_union_program_home",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Pro_Skin_ID=".$Skin_ID);
        if($Flag){
            $json=array(
                "Title"=>$Home_Json[$no-1]['Title'],
                "ImgPath"=>$Home_Json[$no-1]['ImgPath'],
                "Url"=>$Home_Json[$no-1]['Url'],
                "status"=>"1"
            );
            echo json_encode($json);
        }else{
            $json=array(
                "status"=>"0"
            );
            echo json_encode($json);
        }
    }else{
        $json=array(
            "status"=>"0"
        );
        echo json_encode($json);
    }
    exit;

}else{
    $json=$setting->set_homejson_array($Home_Json);
    $pageurllist_html = page_url_select($DB,$_SESSION["Users_ID"],"UrlList[]","","");
    $pageurlalone_html = page_url_select($DB,$_SESSION["Users_ID"],"Url","","");
}
if ($json == 110) {
    echo '模版设置有误！联系管理员！';exit;
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/member/css/main.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/layer/layer.js?t=<?=time()?>'></script>
    <script type='text/javascript' src='/static/member/js/global.js?t=<?php echo time() ?>'></script>
    <script src="/third_party/uploadify/jquery.uploadify.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
    <script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
    <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
    <link href="/third_party/uploadify/uploadify.css" rel="stylesheet" type="text/css">
</head>
<style>
    .cate{
        width: 50%;
        float: left;
    }
    .rows input[type='button'] {
        height: 30px;
        color: #fff;
        border: none;
        border-radius: 2px;
        background: url(/static/member/images/global/ok-btn-120-bg.jpg) no-repeat left top;
        cursor: pointer;
        width: 80px;
    }
</style>
<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/shop.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
        <script type='text/javascript' src='/static/member/js/shop.js'></script>
        <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/bizunion_menubar.php');?>
        <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
        <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
        <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script>
        <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
        <link href='/static/api/shop/union/page.css?t=<?php echo time() ?>' rel='stylesheet' type='text/css' />
        <script language="javascript">var shop_skin_data=<?php echo json_encode($json) ?>;</script>
        <script language="javascript">
            window.onload = shop_obj.home_init;
            KindEditor.ready(function(K) {
                var editor = K.editor({
                    uploadJson : '/member/upload_json.php?TableField=pintuan_index',
                    fileManagerJson : '/member/file_manager_json.php',
                    showRemote : true,
                    allowFileManager : true
                });
                K('#HomeFileUpload_0').click(function(){
                    editor.loadPlugin('image', function() {
                        editor.plugin.imageDialog({
                            clickFn : function(url, title, width, height, border, align) {
                                K('#ImgPathList_0').val(url);
                                K('.picDetail_0').append('<img src="' + url + '" style="width:50px;height:50px;"/>');
                                editor.hideDialog();
                            }
                        });
                    });
                });
                K('#HomeFileUpload_1').click(function(){
                    editor.loadPlugin('image', function() {
                        editor.plugin.imageDialog({
                            clickFn : function(url, title, width, height, border, align) {
                                K('#ImgPathList_1').val(url);
                                K('.picDetail_1').append('<img src="' + url + '" style="width:50px;height:50px;"/>');
                                editor.hideDialog();
                            }
                        });
                    });
                });
                K('#HomeFileUpload_2').click(function(){
                    editor.loadPlugin('image', function() {
                        editor.plugin.imageDialog({
                            clickFn : function(url, title, width, height, border, align) {
                                K('#ImgPathList_2').val(url);
                                K('.picDetail_2').append('<img src="' + url + '" style="width:50px;height:50px;"/>');
                                editor.hideDialog();
                            }
                        });
                    });
                });
                K('#HomeFileUpload_3').click(function(){
                    editor.loadPlugin('image', function() {
                        editor.plugin.imageDialog({
                            clickFn : function(url, title, width, height, border, align) {
                                K('#ImgPathList_3').val(url);
                                K('.picDetail_3').append('<img src="' + url + '" style="width:50px;height:50px;"/>');
                                editor.hideDialog();
                            }
                        });
                    });
                });
                K('#HomeFileUpload_4').click(function(){
                    editor.loadPlugin('image', function() {
                        editor.plugin.imageDialog({
                            clickFn : function(url, title, width, height, border, align) {
                                K('#ImgPathList_4').val(url);
                                K('.picDetail_4').append('<img src="' + url + '" style="width:50px;height:50px;"/>');
                                editor.hideDialog();
                            }
                        });
                    });
                });
                K('#HomeFileUpload').click(function(){
                    editor.loadPlugin('image', function() {
                        editor.plugin.imageDialog({
                            clickFn : function(url, title, width, height, border, align) {
                                K('#ImgPathList').val(url);
                                K('#ImgPath').val(url);
                                console.log(K('#ImgPath').val(url))
                                if($(".picDetail > img").length == 0){
                                    K('.picDetail').append('<img src="' + url + '" style="width:50px;height:50px;"/>');
                                }else{
                                    $(".picDetail > img").attr("src",url);
                                }
                                editor.hideDialog();
                            }
                        });
                    });
                });
            })
        </script>
        <div id="home" class="r_con_wrap">
            <div class="m_lefter">
                <?php
                require_once('skin_program/'.$Skin_ID.'.php');
                ?>
            </div>
            <div class="m_righter">
                <form id="home_form">
                    <div id="setbanner">
                        <?php
                        for($i=0; $i<5; $i++){
                            ?>
                            <div class="item">
                                <div class="rows">
                                    <div class="b_l">
                                        <strong>图片(<?php echo $i+1;?>)</strong><span class="tips">大图建议尺寸：<label></label>px</span><?php if($i>0){?><a href="#shop_home_img_del" value='<?php echo $i;?>'><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a><?php }?><br />
                                        <div class="blank6"></div>
                                        <div class="picDetail_<?=$i?>"><input name="FileUpload" id="HomeFileUpload_<?php echo $i;?>" type="button" value="图片上传" /></div>
                                    </div>
                                    <div class="b_r"></div>
                                    <input type="hidden" name="ImgPathList[]" value="" id="ImgPathList_<?=$i?>"/><input type="hidden" name="TitleList[]" value="" />
                                </div>
                                <div class="blank9"></div>
                                <div class="rows url_select">
                                    <div class="input"><input name="UrlList[]" value="" type="text" size="30" id="shop_home_url_<?php echo $i;?>" placeholder="输入页面链接" /><img src="/static/member/images/ico/search.png" style="width:22px; height:22px; margin:0px 0px 0px 5px; vertical-align:middle; cursor:pointer" class="btn_select_url" ret="<?php echo $i;?>" module="program"/></div>

                                </div>
                                <div class="clear"></div>
                            </div>
                        <?php }?>
                    </div>
                    <div id="setimages">
                        <div class="item rows">
                            <div value="title">
                                <span class="fc_red">*</span> 标题<br />
                                <div class="input"><input name="Title" value="" type="text" /></div>
                                <div class="blank20"></div>
                            </div>
                            <div value="images">
                                <span class="fc_red">*</span> 图片<span class="tips">大图建议尺寸：<label></label>px</span><br />
                                <div class="blank6"></div>
                                <div class="picDetail">
                                    <input name="FileUpload" id="HomeFileUpload" type="button" value="图片上传" />
                                </div>
                                <div class="blank20"></div>
                            </div>
                            <div class='b_images' ></div>
                            <div class="url_select">
                                <span class="fc_red">*</span> 链接页面<br />
                                <div class="input">
                                    <input name="Url" value="" type="text" size="30" id="shop_home_url_6" placeholder="输入页面链接" /><img src="/static/member/images/ico/search.png" style="width:22px; height:22px; margin:0px 0px 0px 5px; vertical-align:middle; cursor:pointer" class="btn_select_url" ret="6" module="program"/>
                                </div>
                            </div>
                            <input type="hidden" name="ImgPath" value=""  id="ImgPath" />
                        </div>
                    </div>
                    <div class="button"><input type="submit" class="btn_green" name="submit_button" value="提交保存" /></div>
                    <input type="hidden" name="PId" value="" />
                    <input type="hidden" name="SId" value="" />
                    <input type="hidden" name="ContentsType" value="" />
                    <input type="hidden" name="no" value="" />
                </form>
            </div>
            <div class="clear"></div>
        </div>
        <div id="home_mod_tips" class="lean-modal pop_win">
            <div class="h">首页设置<a class="modal_close" href="#"></a></div>
            <div class="tips">首页设置成功</div>
        </div>
    </div>
</div>
</body>
</html>