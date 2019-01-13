?><?php 
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
$rsConfig=$DB->GetRs("shop_config","Skin_ID","where Users_ID='".$_SESSION["Users_ID"]."'");
$rsSkin=$DB->GetRs("shop_home","*","where Users_ID='".$_SESSION["Users_ID"]."' and Skin_ID=".$rsConfig['Skin_ID']);
$json=json_decode($rsSkin['Home_Json'],true);

if($rsConfig["Skin_ID"]==1){
	include('skin/'.$rsConfig['Skin_ID'].'.php');
	exit;
}
if($_POST){
	$no=intval($_POST["no"]);
	if(is_array($json[$no]["ImgPath"])){
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
	
	if(is_array($json[$no]["ImgPath"])){
		$json[$no]["Title"] = array_merge($_POST["TitleList"]);
		$json[$no]["ImgPath"] = array_merge($_POST["ImgPathList"]);
		$json[$no]["Url"] = array_merge($_POST["UrlList"]);
	}else{
		if(!empty($_POST['Title'])){
			$json[$no]["Title"] = $_POST['Title'];
		}
		
		if(!empty($_POST['ImgPath'])){
			$json[$no]["ImgPath"] = $_POST['ImgPath'];
		}
		
		if(!empty($_POST['Url'])){
			$json[$no]["Url"] = $_POST['Url'];
		}
	}
	
	$Data=array(
		"Home_Json"=>json_encode($json,JSON_UNESCAPED_UNICODE),
	);
	
	$Flag=$DB->Set("shop_home",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Skin_ID=".$rsConfig['Skin_ID']);
	
	if($Flag){
		$response=array(
			"Title"=>is_array($json[$no]["Title"]) ? json_encode(array_merge($json[$no]["Title"])):$json[$no]["Title"],
			"ImgPath"=>is_array($json[$no]["ImgPath"]) ? json_encode(array_merge($json[$no]["ImgPath"])):$json[$no]["ImgPath"],
			"Url"=>is_array($json[$no]["Url"]) ? json_encode(array_merge($json[$no]["Url"])):$json[$no]["Url"],
			"status"=>"1"
		);
	}else{
		$response=array(
			"status"=>"0"
		);
	}
	echo json_encode($response);
	exit;
}else{
	if (empty($json)) {
		echo '<script>alert("请重新选择风格");location.href="skin.php";</script>';
		die();
	}	
	//重组json
	foreach($json as $k=>$value){
		if(is_array($value["Title"])){
			$value["Title"] = json_encode($value["Title"]);
		}
		if(is_array($value["ImgPath"])){
			$value["ImgPath"] = json_encode($value["ImgPath"]);
		}
		if(is_array($value["Url"])){
			$value["Url"] = json_encode($value["Url"]);
		}
		$json[$k] = $value;
	}
}
$jcurid = 4;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js?t=<?php echo time() ?>'></script>
<script src="/third_party/uploadify/jquery.uploadify.min.js" type="text/javascript"></script>
<link href="/third_party/uploadify/uploadify.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
    <script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
    <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
</head>
<style>
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
  	<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
    <link href='/static/member/css/shop.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
     <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/setting/basic_menubar.php');?>
	<div style="width:100%; background:#FFF; padding:10px 0px">
		<span onclick="openwin();" style="display:block; cursor:pointer; width:100px; height:36px; line-height:36px; margin:0px 0px 0px 660px; background:#3AA0EB; color:#FFF; text-align:center; font-size:14px; border-radius:5px;">首页预览</span>
		<script language="javascript">
			function openwin(){
				var win_top = ($(window).height()-750)/2;
				var win_left = ($(window).width()-480)/2;
				window.open('/api/<?php echo $_SESSION["Users_ID"];?>/shop/wzw/','','height=750,width=480,top='+win_top+',left='+win_left+',toolbar=no,menubar=no,scrollbars=yes,resizable=no,location=no,status=no');
			}
		</script>
	</div>
    <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script> 
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
    <link href='/static/api/shop/skin/<?php echo $rsConfig['Skin_ID'];?>/page.css?t=<?php echo time() ?>' rel='stylesheet' type='text/css' />
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
			require_once('skin/'.$rsConfig['Skin_ID'].'.php');
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
                        <div class="input"><input name="UrlList[]" value="" type="text" size="30" id="shop_home_url_<?php echo $i;?>" placeholder="输入页面链接" /><img src="/static/member/images/ico/search.png" style="width:22px; height:22px; margin:0px 0px 0px 5px; vertical-align:middle; cursor:pointer" class="btn_select_url" ret="<?php echo $i;?>" /></div>

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
                        	<input name="Url" value="" type="text" size="30" id="shop_home_url_6" placeholder="输入页面链接" /><img src="/static/member/images/ico/search.png" style="width:22px; height:22px; margin:0px 0px 0px 5px; vertical-align:middle; cursor:pointer" class="btn_select_url" ret="6" />
                        </div>
					</div>
					<input type="hidden" name="ImgPath" value="" id="ImgPath"/>
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