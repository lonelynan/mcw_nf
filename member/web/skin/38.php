<?php
$Dwidth = array('640','63','63','63','63');
$DHeight = array('1010','63','63','63','63');

if(empty($rsSkin['Home_Json'])){
	for($no=1;$no<=5;$no++){
		$json[$no-1]=array(
			"ContentsType"=>$no==1?"1":"0",
			"Title"=>$no==1?json_encode(array("")):"",
			"ImgPath"=>$no==1?json_encode(array("/api/web/skin/".$rsSkin['Skin_ID']."/banner.jpg")):"/api/web/skin/".$rsSkin['Skin_ID']."/i".($no-2).".jpg",
			"Url"=>$no==1?json_encode(array("")):"",
			"Postion"=>"t0".$no,
			"Width"=>$Dwidth[$no-1],
			"Height"=>$DHeight[$no-1],
			"NeedLink"=>$no==1?"0":"1"
		);
	}
}else{
	$Home_Json=json_decode($rsSkin['Home_Json'],true);
	for($no=1;$no<=5;$no++){
		$json[$no-1]=array(
			"ContentsType"=>$no==1?"1":"0",
			"Title"=>$no==1?json_encode($Home_Json[$no-1]['Title']):$Home_Json[$no-1]['Title'],
			"ImgPath"=>$no==1?json_encode($Home_Json[$no-1]['ImgPath']):$Home_Json[$no-1]['ImgPath'],
			"Url"=>$no==1?json_encode($Home_Json[$no-1]['Url']):$Home_Json[$no-1]['Url'],
			"Postion"=>"t0".$no,
			"Width"=>$Dwidth[$no-1],
			"Height"=>$DHeight[$no-1],
			"NeedLink"=>$no==1?"0":"1"
		);
	}
}

if($_POST){
	$no=intval($_POST["no"])+1;
	if(empty($_POST["ImgPath"])){
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
	$Home_Json[$no-1]=array(
		"ContentsType"=>$no==1?"1":"0",
		"Title"=>$no==1?array_merge($_POST["TitleList"]):$_POST['Title'],
		"ImgPath"=>$no==1?array_merge($_POST["ImgPathList"]):$_POST["ImgPath"],
		"Url"=>$no==1?array_merge($_POST["UrlList"]):$_POST['Url'],
		"Postion"=>"t0".$no,
		"Width"=>$Dwidth[$no-1],
		"Height"=>$DHeight[$no-1],
		"NeedLink"=>$no==1?"0":"1"
	);
	$Data=array(
		"Home_Json"=>json_encode($Home_Json,JSON_UNESCAPED_UNICODE),
	);
	$Flag=$DB->Set("web_home",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Skin_ID=".$rsConfig['Skin_ID']);
	if($Flag){
		$json=array(
			"Title"=>$no==1?json_encode(array_merge($_POST["TitleList"])):$_POST['Title'],
			"ImgPath"=>$no==1?json_encode(array_merge($_POST["ImgPathList"])):$_POST["ImgPath"],
			"Url"=>$no==1?json_encode(array_merge($_POST["UrlList"])):$_POST['Url'],
			"status"=>"1"
		);
		echo json_encode($json);
	}else{
		$json=array(
			"status"=>"0"
		);
		echo json_encode($json);
	}
	exit;
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>微易宝</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css?t=20140122' rel='stylesheet' type='text/css' />
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
    <link href='/static/member/css/web.css?t=<?php echo time() ?>' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/web.js?t=<?php echo time() ?>'></script>
    <div class="r_nav">
      <ul>
        <li class=""><a href="./config.php">基本设置</a></li>
        <li class=""><a href="./skin.php">风格设置</a></li>
        <li class="cur"><a href="./home.php">首页设置</a></li>
        <li class=""><a href="./column.php">栏目管理</a></li>
        <li class=""><a href="./article.php">一键导航</a></li>
      </ul>
    </div>
    <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script> 
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
    <link href='/static/api/web/skin/<?php echo $rsConfig['Skin_ID'];?>/page.css' rel='stylesheet' type='text/css' />
    <script language="javascript">var web_skin_data=<?php echo json_encode($json) ?>;</script>
      <script language="javascript">
          window.onload = web_obj.home_init;
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
	   <script language="javascript">
		   var skin_index_init=function(){
			$('#header,#footer, #footer_points, #global_support').hide();
			$('a').filter('[ajax_url]').off().each(function(){
				$(this).attr('href', $(this).attr('ajax_url'));
			});
			$('#web_skin_index .banner *').not('img').height($(window).height());
		};
	   </script>
        <div id="web_skin_index">
          <div class="web_skin_index_list banner" rel="edit-t01" style="height:505px;">
           <div class="img"></div>
    	  </div>
	      <div class="web_contents">
		   <div class="box">
			<ul>
			 <li>
				<div class="item_bg"></div>
				<div class="web_skin_index_list items" rel="edit-t02">
					<div class="img"></div>
					<div class="text"></div>
				</div>
			 </li>
			 <li>
				<div class="item_bg"></div>
				<div class="web_skin_index_list items" rel="edit-t03">
					<div class="img"></div>
					<div class="text"></div>
				</div>
			 </li>
			 <li>
				<div class="item_bg"></div>
				<div class="web_skin_index_list items" rel="edit-t04">
					<div class="img"></div>
					<div class="text"></div>
				</div>
			 </li>
			 <li>
				<div class="item_bg"></div>
				<div class="web_skin_index_list items" rel="edit-t05">
					<div class="img"></div>
					<div class="text"></div>
				</div>
			 </li>
			</ul>
		   </div>
	      </div>
        </div>
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
                            <input type="hidden" name="ImgPathList[]" value=""  id="ImgPathList_<?=$i?>"/><input type="hidden" name="TitleList[]" value="" />
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
              <div value="title"> <span class="fc_red">*</span> 标题<br />
                <div class="input">
                  <input name="Title" value="" type="text" />
                </div>
                <div class="blank20"></div>
              </div>
              <div value="images"> <span class="fc_red">*</span> 图片<span class="tips">大图建议尺寸：
                <label></label>
                px</span><br />
                <div class="blank6"></div>
                  <div class="picDetail">
                      <input name="FileUpload" id="HomeFileUpload" type="button" value="图片上传" />
                  </div>
                <div class="blank20"></div>
              </div>
              <div class="url_select"> <span class="fc_red">*</span> 链接页面<br />
                <div class="input">
                  <select name='Url'>
                    <?php UrlList(); ?>
                  </select>
                </div>
              </div>
              <input type="hidden" name="ImgPath" value="" id="ImgPath"/>
            </div>
          </div>
          <div class="button">
            <input type="submit" class="btn_green" name="submit_button" style="cursor:pointer" value="提交保存" />
          </div>
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