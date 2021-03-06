<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
$DB->showErr=false;
if(empty($_SESSION["BIZ_ID"]))
{
	header("location:/biz/login.php");
}
$rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);

if($_POST){
	if($_POST['UseType']==1 && $_POST["Cash"] > 20){
		echo '<script language="javascript">alert("抵金额不能超过20元");history.back();</script>';
		exit;
	}
	if($_POST['UseType']==0 && $_POST["Discount"] < 0.85){
		echo '<script language="javascript">alert("折扣不能小于0.85");history.back();</script>';
		exit;
	}
	$CouponID = $_POST["CouponID"];
	$Time=empty($_POST["Time"])?array(time(),time()):explode(" - ",$_POST["Time"]);
	$StartTime=strtotime($Time[0]);
	$EndTime=strtotime($Time[1]);
	$Data=array(
		"Coupon_Subject"=>$_POST["Subject"],
		"Coupon_PhotoPath"=>$_POST["PhotoPath"],
		"Coupon_UsedTimes"=>$_POST["UsedTimes"],
		"Coupon_StartTime"=>$StartTime,
		"Coupon_EndTime"=>$EndTime,
		"Coupon_Description"=>$_POST["Description"],
		"Coupon_UseType"=>$_POST["UseType"],
		"Coupon_Condition"=>$_POST["Condition"],
		"Coupon_Discount"=>$_POST["Discount"],
		"Coupon_Cash"=>$_POST["Cash"]		
	);
	
	$Flag=$DB->Set("user_coupon",$Data,"where Coupon_ID=".$CouponID);
	if($Flag){
		echo '<script language="javascript">alert("修改成功");window.location.href="coupon_list.php";</script>';
	}else{
		echo '<script language="javascript">alert("修改失败");history.back();</script>';
	}
}else{
	$CouponID=empty($_REQUEST['CouponID'])?0:$_REQUEST['CouponID'];
	$rsCoupon=$DB->GetRs("user_coupon","*","where Biz_ID=".$_SESSION["BIZ_ID"]." and Coupon_ID=".$CouponID);
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
<script>
KindEditor.ready(function(K) {
	K.create('textarea[name="Description"]', {
		themeType : 'simple',
		filterMode : false,
		uploadJson : '/biz/upload_json.php?TableField=coupon',
		fileManagerJson : '/biz/file_manager_json.php',
		allowFileManager : true,
		items : [
			'source', '|', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
			'removeformat', 'undo', 'redo', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link' , '|', 'preview']
	});
	var editor = K.editor({
		uploadJson : '/biz/upload_json.php?TableField=coupon',
		fileManagerJson : '/biz/file_manager_json.php',
		showRemote : true,
		allowFileManager : true,
	});
	K('#PhotoUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#PhotoPath').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#PhotoPath').val(url);
					K('#PhotoDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
})
</script>
<script type="text/javascript" src="/third_party/uploadify/jquery.uploadify.min.js"></script>
<link href="/third_party/uploadify/uploadify.css" rel="stylesheet" type="text/css">
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bgg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/user.js'></script>
    <div class="r_nav">
      <ul>
        <li class="cur"> <a href="coupon_list.php">优惠券管理</a></li>
        <li> <a href="coupon_add.php">添加优惠券</a></li>
        <li> <a href="coupon_list_logs.php">使用记录</a></li>
      </ul>
    </div>
    <div id="coupon_list" class="r_con_wrap">
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
      <script type='text/javascript' src='/static/js/plugin/daterangepicker/moment_min.js'></script>
      <link href='/static/js/plugin/daterangepicker/daterangepicker.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/daterangepicker/daterangepicker.js'></script> 
      <script language="javascript">$(document).ready(user_obj.coupon_list_init);</script>
      <form id="coupon_list_form" class="r_con_form" method="post" action="?">
        <div class="rows">
          <label>优惠券名称</label>
          <span class="input">
          <input name="Subject" value="<?php echo $rsCoupon["Coupon_Subject"] ?>" type="text" class="form_input" size="40" maxlength="100" notnull>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>优惠券图片</label>
          <span class="input"> <span class="upload_file">
          <div>
            <div class="up_input">
              <input type="button" id="PhotoUpload" value="添加图片" style="width:80px;" />
            </div>
            <div class="tips">图片建议尺寸：640*360px</div>
            <div class="clear"></div>
          </div>
          <div class="img" id="PhotoDetail"><?php echo empty($rsCoupon["Coupon_PhotoPath"])?'':'<img src="'.$rsCoupon["Coupon_PhotoPath"].'" />' ?></div>
          </span> </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
            <label>优惠方式</label>
            <span class="input">
               <input type="radio" name="UseType" value="0"<?php echo $rsCoupon["Coupon_UseType"]==0 ? " checked" : "";?>/>折扣 <input name="Discount" class="form_input" value="<?php echo $rsCoupon["Coupon_Discount"];?>" size="5" /> <span style="color:red;">注：在0-1之间，1为不打折，折扣不能小于0.85</span>
				<div class="blank6"></div>
                <input type="radio" name="UseType" value="1"<?php echo $rsCoupon["Coupon_UseType"]==1 ? " checked" : "";?>/>抵现金 ￥<input name="Cash" class="form_input" value="<?php echo $rsCoupon["Coupon_Cash"];?>" size="5" />
				<span style="color:red;">注：抵金额不能超过20元（因为计算佣金时，按照产品的现价进行结算）</span>
            </span>
            <div class="clear"></div>
        </div>
        <div class="rows">
            <label>使用条件</label>
            <span class="input">￥<input name="Condition" class="form_input" value="<?php echo $rsCoupon["Coupon_Condition"];?>" size="5" /> <font class="tips">消费满一定金额才可以使用</font></span>
            <div class="clear"></div>
        </div>
        <div class="rows">
          <label>可用次数</label>
          <span class="input">
          <select name="UsedTimes">
            <option value="-1">不限</option>
            <?php
				for($i=1;$i<=100;$i++){
				  echo '<option value="'.$i.'"'.($rsCoupon['Coupon_UsedTimes']==$i?' selected':'').'>'.$i.'次</option>';
				}
			?>
          </select>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>有效时间</label>
          <span class="input time">
          <input name="Time" type="text" value="<?php echo date("Y/m/d H:i:s",$rsCoupon["Coupon_StartTime"])." - ".date("Y/m/d H:i:s",$rsCoupon["Coupon_EndTime"]) ?>" class="form_input" size="40" readonly notnull />
          <font class="fc_red">*</font> </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>详细内容</label>
          <span class="input">
          <textarea name="Description" style="width:500px; height:300px;"><?php echo $rsCoupon["Coupon_Description"] ?></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" value="提交保存" name="submit_btn">
          <a href="" class="btn_gray">返回</a></span>
          <div class="clear"></div>
        </div>
        <input type="hidden" id="PhotoPath" name="PhotoPath" value="<?php echo $rsCoupon["Coupon_PhotoPath"] ?>" />
        <input type="hidden" name="CouponID" value="<?php echo $rsCoupon["Coupon_ID"] ?>">
      </form>
    </div>
  </div>
</div>
</body>
</html>