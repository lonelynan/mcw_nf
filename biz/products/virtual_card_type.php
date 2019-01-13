<?php  
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/library/outputExcel.php');

require_once('../global.php');
if ($rsBiz['is_agree'] !=1 || $rsBiz['is_auth'] !=2) {
	echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.location="/biz/authen/index.php";</script>';
	exit;
}
if ($rsBiz['is_biz'] !=1) {
	echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.location="/biz/authen/index.php";</script>';
	exit;
}
$Biz_ID=$_SESSION["BIZ_ID"];
$bizInfo = $DB->getRs('biz','','where Biz_ID = '.$Biz_ID);
if (time() > $bizInfo['expiredate']) {
		echo '<script language="javascript">alert("您的商家已经到期,请及时续费");window.location="/biz/authen/charge_man.php";</script>';
		exit;
}

if(I('action')==='import'){
    //导入excel文件
    $url = I('url');
    $realpath = $_SERVER["DOCUMENT_ROOT"] . $url;
    $outputExcel = new OutputExcel();
    $typeid = I('typeid');
    $flag = $outputExcel->importExcel($realpath, $_SESSION["Users_ID"], $Biz_ID, $typeid);
    if($flag!==false){
        die(json_encode([
            'status' => 1,
            'msg' => 'ok'
        ] , JSON_UNESCAPED_UNICODE));
    }else{
        die(json_encode([
            'status' => 0,
            'msg' => '批量导入失败'
        ] , JSON_UNESCAPED_UNICODE));
    }
}

if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}
//设置常规固定常量
define('NO_RELATION_PRODUCTS_STATUS', '未设置关联商品');
define('RELATION_PRODUCTS_ERROR', '关联产品设置出错');
define('CARD_NORMAL', '未使用');
define('CARD_ABNORMAL', '已使用');

if (isset($_GET['action'])) {
  if ($_GET['action'] == 'del') {
    $Flag=$DB->Del("shop_virtual_card_type","User_ID='".$_SESSION["Users_ID"]."' and Biz_ID='" .$_SESSION['BIZ_ID']. "' and Type_id=".$_GET["TypeId"]);
    if($Flag){
      echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
    }else{
      echo '<script language="javascript">alert("删除失败");history.back();</script>';
    }
    exit;
  }
}

$condition = "WHERE `User_ID` = '".$rsBiz["Users_ID"]."' and Biz_ID='" .$_SESSION['BIZ_ID']. "'";
$condition .= " ORDER BY Type_Id DESC";
$List = Array();
$DB->getPage("shop_virtual_card_type","*",$condition,10);
while ($r=$DB->fetch_assoc()) { $List[] = $r; }
$jcurid = 4;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>卡密导入</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type='text/javascript' src='/static/js/layer/layer.js'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<style type="text/css">
	span.red, span.error, span.normal { display: inline-block; overflow: hidden; padding: 2px 5px; background: #c09853; color: #FFF; border-radius: 3px; }
	span.error { background: red; }
	span.normal { background: #468847; }
</style>
<style type="text/css">
	span.red, span.error, span.normal { display: inline-block; overflow: hidden; padding: 2px 5px; background: #c09853; color: #FFF; border-radius: 3px; }
	span.error { background: red; }
	span.normal { background: #468847; }
     .r_con_form .rows .input .upload_file .img { clear:both; margin-top:20px;}
    .r_con_form .rows .input .upload_file .img img { max-width:320px;max-height:100px;}
    .r_con_form .rows .input .upload_file .tips {clear:both;}
    .select {width:100px;height:30px;margin:22px 70px;}
    .layui-layer-page .layui-layer-content {
        overflow: hidden;}
</style>
    <script>
        KindEditor.ready(function(K) {
            var editor = K.editor({
                uploadJson: '/biz/upload_json.php?TableField=stores',
                fileManagerJson: '/biz/file_manager_json.php',
                showRemote: true,
                allowFileManager: true,
            });
            K('#vcard').click(function(){
                var rejectLayer = layer.open({
                    type: 1,
                    title: "选择类型",
                    closeBtn: 1,
                    shadeClose: true,
                    area:['260px','165px'],
                    btn:['确定'],
                    content: "<select class='select' name='selectType'><?php foreach($List as $k => $v){  ?><option value='<?=$v['Type_Id'] ?>'><?=$v['Type_Name'] ?></option><?php } ?></select>",
                    yes: function(){
                        var typeid = $("select[name='selectType']").val();
                        layer.close(rejectLayer);
                        editor.loadPlugin('insertfile', function(){
                            editor.plugin.fileDialog({
                                clickFn : function(url, title, width, height, border, align){
                                    $.post("", {url:url,"action":"import",typeid:typeid}, function(data){
                                        if(data.status==1){
                                            alert("批量导入成功");
                                        }else{
                                            alert(data.msg);
                                        }
                                    },'json');
                                    editor.hideDialog();
                                }
                            });
                        });
                    }
                });
            });
        });
    </script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/biz/products/bizshop_menubar.php');?>
    <div id="products" class="r_con_wrap">
		<div class="control_btn"><a href="virtual_card_type_add.php" class="btn_green btn_w_120">添加类型</a> <a href="javascript:void(0);" class="btn_green btn_w_120" id="vcard">卡密导入</a>
		<a href="/data/vcard_import.xls" class="btn_green">下载卡密导入模板</a>
		</div>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="8%" nowrap="nowrap">序号</td>
            <td width="8%" nowrap="nowrap" style="text-align:left; padding-left:10px;">类型名称</td>
            <td width="15%" nowrap="nowrap">添加时间</td>
            <td width="22%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
        <?php
        	$List = Array();
        	$DB->getPage("shop_virtual_card_type","*",$condition,10);
        	while ($r=$DB->fetch_assoc()) { $List[] = $r; }
        	foreach ($List as $k => $v) {
        ?>
          <tr>
            <td nowrap="nowrap"><?php echo $v['Type_Id']; ?></td>
            <td width="15%" style="text-align:left; padding-left:10px;"><?php echo $v['Type_Name']; ?></td>
            <td><?php echo date('Y-m-d H:i:s', $v['Type_CreateTime']); ?></td>
            
            <td class="last" nowrap="nowrap"><a href="virtual_card_type_edit.php?TypeId=<?php echo $v['Type_Id']; ?>"><img src="/static/member/images/ico/mod.gif" align="absmiddle" alt="修改"></a>
			<a href="virtual_card_type.php?action=del&amp;TypeId=<?php echo $v['Type_Id']; ?>" onclick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" alt="删除"></a>
			</td>
          </tr>

        <?php } ?>
        </tbody>
      </table>

      <div class="blank20"></div>
      <?php $DB->showPage(); ?>

    </div>
  </div>
</div>
</body>
</html>