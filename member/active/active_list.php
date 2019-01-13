<?php
require_once (CMS_ROOT . '/include/update/common.php');

if (IS_GET && isset($_GET["action"]) && $_GET["action"] == "del") {
    $Active_ID = intval($_GET["Active_ID"]);
    if($Active_ID){
        $flag = $DB->GetRs("biz_active","*", "WHERE Users_ID='{$UsersID}' AND Active_ID={$Active_ID} AND Status=2");
        if(!empty($flag)){
            showMsg("已有商家正在参加活动，不能删除","active_list.php",1);
        }
    }
    $rsActive = $DB->GetRs("active", "Active_Name", "WHERE Users_ID='{$UsersID}' AND Active_ID='{$Active_ID}'");
    begin_trans();
    $Flag = $DB->Del("active", "Users_ID='{$UsersID}' AND Active_ID={$Active_ID}");
    $Flag2 = $DB->Del("biz_active", "Users_ID='{$UsersID}' AND Active_ID={$Active_ID}");
    if (! $Flag || ! $Flag2) {
        back_trans();
    }
    commit_trans();
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
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
	<?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/active/active_menubar.php');?>
    <div id="products" class="r_con_wrap">
      <div class="control_btn">
      <a href="active_add.php" class="btn_green btn_w_120">添加活动</a>
      </div>
      <table align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="48px" align="center"><strong>活动ID</strong></td>
            <td width="48px" align="center"><strong>活动名称</strong></td>
            <td align="center" width="66px"><strong>活动类型</strong></td>
            <td align="center" width="166px"><strong>活动属性</strong></td>
<!--            <td align="center" width="66px"><strong>商家活动列表</strong></td>-->
            <td align="center" width="66px"><strong>活动时间</strong></td>
            <td width="50px" align="center"><strong>活动状态</strong></td>
            <td width="70px" align="center"><strong>操作</strong></td>
          </tr>
          </tr>
        </thead>
        <tbody>
    	<?php
    $lists = array();
    $result = $DB->getPages("active", "*", "WHERE Users_ID='{$UsersID}' ORDER BY Status ASC,Active_ID DESC", 10);
    $lists = $DB->toArray($result);
    foreach ($lists as $k => $v) {
        /**
         * JSON参数
         */
        switch ($v['Type_ID']) {
            case 5:
                $json = json_decode($v['ext_json'],true);
                break;
            case 6:
                break;
            default:
        }


        ?>      
          <tr>
            <td nowrap="nowrap" class="id"><?=$v["Active_ID"] ?></td>
            <td nowrap="nowrap" class="id"><?=$v["Active_Name"] ?></td>
            <td nowrap="nowrap"><?=isset($typelist[$v["Type_ID"]]['Type_Name'])?$typelist[$v["Type_ID"]]['Type_Name']:$typelist[1]['Type_Name'] ?></td>
            <td nowrap="nowrap">价格折扣：<?=isset($json["PriceDiscount"]) ? $json["PriceDiscount"] . '%' : '暂无折扣'?></td>
<!--            <td nowrap="nowrap"><a href="biz_active.php?activeid=--><?//=$v["Active_ID"] ?><!--">查看</a></td>-->
              <?php if($v['starttime'] != 0 || $v["stoptime"] != 0){?>
            <td nowrap="nowrap"><?= $v['starttime'] ? date("Y-m-d H:i:s",$v["starttime"]) : ''; ?> 至 <?= $v["stoptime"] ? date("Y-m-d H:i:s",$v["stoptime"]) : '' ; ?>
            </td>
              <?php }else{?>
                  <td nowrap="nowrap">永久有效</td>
              <?php }?>
            <td nowrap="nowrap"><?php echo $v["Status"]==0 ? '<font style="color:red">已关闭</font>' : '<font style="color:blue">已开启</font>'; ?></td>
            <td class="last" nowrap="nowrap">
            	<a href="active_edit.php?typeid=<?=$v["Active_ID"]?>"><img src="/static/member/images/ico/mod.gif" align="absmiddle" alt="修改" /></a>&nbsp;&nbsp;
            	<a class="onclik" onClick="del(<?=$v["Active_ID"]?>)"><img src="/static/member/images/ico/del.gif" align="absmiddle" alt="删除" /></a>
			</td>
          </tr>
          <?php }?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
</div>
<script>
function del(id){
    if(confirm('您确定删除此活动么？')){   
    	location.href="active_list.php?action=del&Active_ID="+id;
    }   
}
</script>
</body>
</html>