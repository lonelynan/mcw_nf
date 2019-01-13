<?php
date_default_timezone_set('PRC');
if(empty($_SESSION["Users_Account"])){
  header("location:/member/login.php");
}
require_once('vertify.php');
$TurntableID=empty($_REQUEST['TurntableID'])?0:$_REQUEST['TurntableID'];
$rsTurntable=$DB->GetRs("turntable","*","where Users_ID='".$_SESSION["Users_ID"]."' and id=".$TurntableID);
if (!empty($rsTurntable['prizejson'])) {
  $rsTurntable['prizejson'] = json_decode($rsTurntable['prizejson'], true);
}
if($_POST){
  if (empty($_POST['name']) || empty($_POST['count']) || empty($_POST['odds'])) {
    $Data=array(
        "status" => 102
    );
    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
    exit;
  }
  if (array_sum($_POST['odds']) > 100) {
    $Data=array(
        "status" => 101
    );
    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
    exit;
  }

  $prize_arr = [];
  foreach ($_POST['name'] as $k => $v) {
    array_push($prize_arr, ['name' => $v, 'count' => $_POST['count'][$k], 'odds' => $_POST['odds'][$k]]);
  }
  $prize_json = '';
  if (!empty($prize_arr)) {
    $prize_json = json_encode($prize_arr, JSON_UNESCAPED_UNICODE);
  }
  $Time=empty($_POST["Time"])?array(time(),time()):explode(" - ",$_POST["Time"]);
  $StartTime=strtotime($Time[0]);
  $EndTime=strtotime($Time[1]);
  $Data=array(
      "title"=>$_POST['Title'],
      "overtodaytips"=>$_POST['overtodaytips'],
      "overalltips"=>$_POST['overalltips'],
      "alltimes"=>$_POST['alltimes'],
      "daytimes"=>$_POST['daytimes'],
      "expasswd"=>$_POST['expasswd'],
      "descr"=>$_POST['descr'],
      "status" => 1,
      'prizejson' => $prize_json
  );
  if(empty($_POST['status'])){
    $Data["starttime"]=$StartTime;
    $Data["endtime"]=$EndTime;
  }
  $Flag=$DB->Set("turntable",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and id=".$TurntableID);
  if($Flag){
    $Data=array(
        "status"=>1
    );
  }else{
    $Data=array(
        "status"=>0
    );
  }
  echo json_encode($Data,JSON_UNESCAPED_UNICODE);
  exit;
}


$prize_add = <<<JS
<div class="prize_one"><div class="rows"><label>奖品名称</label><span class="input"><input type="text" class="form_input" name="name[]" value="" /><span class="fc_red">*</span><span class="tips">请输入奖品的名称</span><span style="width: 30px; height: 30px; display: inline-block; background-color: #00A3FF; text-align: center; line-height: 30px; font-size: 20px; cursor: pointer; color: #fff; margin-left: 50px;" class="add_prize">+</span></span><div class="clear"></div></div><div class="rows"><label>奖品数量</label><span class="input"><input type="text" class="form_input" name="count[]" value="" /><span class="fc_red">*</span><span class="tips">请输入该奖品的数量</span></span><div class="clear"></div></div><div class="rows"><label>获奖概率</label><span class="input"><input type="text" class="form_input" name="odds[]" value="" />%<span class="fc_red">*</span><span class="tips">请输入该奖品的中奖几率百分比</span></span><div class="clear"></div></div></div>
JS;


$prize_min = <<<JS
<div class="prize_one"><div class="rows"><label>奖品名称</label><span class="input"><input type="text" class="form_input" name="name[]" value="" /><span class="fc_red">*</span><span class="tips">请输入奖品的名称</span><span style="width: 30px; height: 30px; display: inline-block; background-color: #00A3FF; text-align: center; line-height: 30px; font-size: 20px; cursor: pointer; color: #fff; margin-left: 50px;" class="min_prize">-</span></span><div class="clear"></div></div><div class="rows"><label>奖品数量</label><span class="input"><input type="text" class="form_input" name="count[]" value="" /><span class="fc_red">*</span><span class="tips">请输入该奖品的数量</span></span><div class="clear"></div></div><div class="rows"><label>获奖概率</label><span class="input"><input type="text" class="form_input" name="odds[]" value="" />%<span class="fc_red">*</span><span class="tips">请输入该奖品的中奖几率百分比</span></span><div class="clear"></div></div></div>
JS;

?>
<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title>微易宝</title>
  <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
  <link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
  <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
  <script type='text/javascript' src='/static/member/js/global.js'></script>
  <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css?t=1" />
  <script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js?t=1"></script>
  <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js?t=1"></script>
  <script type="text/javascript">
    var editor;
    KindEditor.ready(function(K) {
      editor = K.create('textarea[name="descr"]', {
        themeType : 'simple',
        filterMode : false,
        uploadJson : '/member/upload_json.php?TableField=turntable',
        fileManagerJson : '/member/file_manager_json.php',
        allowFileManager : true,
        items : [
          'source', '|', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
          'removeformat', 'undo', 'redo', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link' , '|', 'preview']
      });
    })

    $(function() {
      $("#prizeall").on('click', '.add_prize', function() {
        $("#prizeall").append('<?=$prize_min?>');
      });

      $("#prizeall").on('click', '.min_prize', function() {
        $(this).parent().parent().parent('.prize_one').remove();
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
    <link href='/static/member/css/turntable.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/turntable.js?t=1'></script>
    <div class="r_nav">
      <ul>
        <li class=""><a href="config.php">基本设置</a></li>
        <li class="cur"><a href="index.php">欢乐大转盘</a></li>
      </ul>
    </div>
    <script type='text/javascript' src='/static/js/plugin/daterangepicker/moment_min.js'></script>
    <link href='/static/js/plugin/daterangepicker/daterangepicker.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/daterangepicker/daterangepicker.js'></script>
    <script language="javascript">$(document).ready(turntable_obj.wheel_mod);</script>
    <div id="wheel" class="r_con_wrap">
      <form class="r_con_form" id="wheel_form">
        <div class="rows">
          <label>活动名称</label>
          <span class="input">
          <input type="text" class="form_input" name="Title" value="<?php echo $rsTurntable['title'] ?>" size="50"/>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>活动时间</label>
          <span class="input">
          <input name="Time"  type="text" value="<?php echo date("Y-m-d H:i:s",$rsTurntable["starttime"])." - ".date("Y-m-d H:i:s",$rsTurntable["endtime"]) ?>" class="form_input" size="42" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>超过当天参与次数</label>
          <span class="input">
          <input type="text" class="form_input" name="overtodaytips" value="<?php echo empty($rsTurntable['overtodaytips'])?'亲，您今日的参与次数已经用完了，请明天再来吧！':$rsTurntable['overtodaytips'] ?>" size="80" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>超过总参与次数</label>
          <span class="input">
          <input type="text" class="form_input" name="overalltips" value="<?php echo empty($rsTurntable['overalltips'])?'亲，本次活动的累计可玩次数您已经用完了喔！敬请关注我们的下个活动吧~。':$rsTurntable['overalltips'] ?>" size="80" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>每人参与的总次数</label>
          <span class="input">
          <input type="text" class="form_input" name="alltimes" value="<?php echo empty($rsTurntable['alltimes'])?400:$rsTurntable['alltimes'] ?>" size="8" maxlength="4" notnull />
          <span class="fc_red">*</span><span class="tips">数量必须是大于1且小于10000</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>每人每天可参与次数</label>
          <span class="input">
          <input type="text" class="form_input" name="daytimes" value="<?php echo empty($rsTurntable['daytimes'])?1:$rsTurntable['daytimes'] ?>" maxlength="4" size="8" notnull />
          <span class="fc_red">*</span><span class="tips">数量必须是大于1且不能大于每人可以参与的总次数</span></span>
          <div class="clear"></div>
        </div>
        <div id="prizeall">
          <? if (!empty($rsTurntable['prizejson'])) {
            foreach ($rsTurntable['prizejson'] as $prizek => $prizev) { ?>
              <div class="prize_one">
                <div class="rows">
                  <label>奖品名称</label>
                <span class="input">
                  <input type="text" class="form_input" name="name[]" value="<?=$prizev['name']?>" />
                  <span class="fc_red">*</span><span class="tips">请输入奖品的名称</span>
                  <span style="width: 30px; height: 30px; display: inline-block; background-color: #00A3FF; text-align: center; line-height: 30px; font-size: 20px; cursor: pointer; color: #fff; margin-left: 50px;" class="<?=$prizek ? 'min_prize' : 'add_prize'?>"><?=$prizek ? '-' : '+'?></span>
                </span>
                  <div class="clear"></div>
                </div>
                <div class="rows">
                  <label>奖品数量</label>
                <span class="input">
                <input type="text" class="form_input" name="count[]" value="<?=$prizev['count']?>" />
                <span class="fc_red">*</span><span class="tips">请输入该奖品的数量</span></span>
                  <div class="clear"></div>
                </div>
                <div class="rows">
                  <label>获奖概率</label>
                <span class="input">
                <input type="text" class="form_input" name="odds[]" value="<?=$prizev['odds']?>" />%
                <span class="fc_red">*</span><span class="tips">请输入该奖品的中奖几率百分比</span></span>
                  <div class="clear"></div>
                </div>
              </div>
            <? }
          } else {
            echo $prize_add;
          } ?>
        </div>
        <div class="rows">
          <label>商家兑奖密码</label>
          <span class="input">
          <input type="text" class="form_input" name="expasswd" value="<?php echo empty($rsTurntable['expasswd'])?'666666':$rsTurntable['expasswd'] ?>" size="20" maxlength="16" notnull />
          <span class="fc_red">*</span><span class="tips">在中奖手机上输入此码可直接使用SN码，不能超过16个字符。</span></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>活动说明</label>
          <span class="input">
          <textarea name="descr" id="descr"><?php echo empty($rsTurntable['descr'])?'本次活动规则受中国法律管辖':$rsTurntable['descr'] ?></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>&nbsp;</label>
          <span class="input">
          <?php if($rsTurntable['status']!=0){ ?><input type="submit" class="btn_green" name="submit_button" value="提交保存" /><?php }?>
            <a href="" class="btn_gray">返回</a></span>
          <div class="clear"></div>
        </div>
        <input type="hidden" name="action" value="mod">
        <input type="hidden" name="TurntableID" value="<?php echo $TurntableID ?>" />
        <input type="hidden" name="status" value="<?php echo $rsTurntable['status'] ?>" />
      </form>
    </div>
  </div>
</div>
</body>
</html>