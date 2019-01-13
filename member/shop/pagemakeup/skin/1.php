?><?php
if($_POST){
	$do_action=empty($_POST['do_action'])?'':$_POST['do_action'];
	$Skin_ID=empty($_POST['Skin_ID'])?$rsConfig['Makeup_Skin_ID']:$_POST['Skin_ID'];
	
	if($do_action=='shop.home_diy'){
		$Data=array(
			"Home_Json"=>str_replace('undefined','',$_POST["gruopPackage"])
		);
		$Flag=$DB->Set("makeup_home",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Skin_ID=".$Skin_ID);
		if($Flag){
			$response=array(
				"status"=>"1"
			);
		}else{
			$response=array(
				"status"=>"0"
			);
		}
		echo json_encode($response);
		exit;
	}
}
if(isset($_POST['template'])){
	if(!empty($_POST['Skin_ID']) && $_POST['Skin_ID'] != 1){
		$rsSkin=$DB->GetRs("makeup_home","*","where Users_ID='".$_SESSION["Users_ID"]."' and Skin_ID=".intval($_POST['Skin_ID']));
		
		$Datas = array(
			"skin_name" => !empty($_POST['skin_name'])?$_POST['skin_name']:0,
			"Skin_Json" => $rsSkin['Home_Json']
		);
		$Flag_c = $DB->Set("makeup_skin",$Datas,"where Users_ID='".$_SESSION["Users_ID"]."' and Skin_ID=".intval($_POST['Skin_ID']));
		if($Flag_c){
			$Date = array(
				'status' => 1
			);
		}else{
			$Date = array(
				'status' => 0 
			);
		}
	}else{
		$rsSkin=$DB->GetRs("makeup_home","*","where Users_ID='".$_SESSION["Users_ID"]."' and Skin_ID=".intval($_POST['Skin_ID']));
		$Datas = array(
			"Users_ID" => $_SESSION["Users_ID"],
			"skin_name" => !empty($_POST['skin_name'])?$_POST['skin_name']:0,
			"Skin_Json" => $rsSkin['Home_Json'],
			"Skin_status" => 1
		);
		$Flag_a=$DB->Add("makeup_skin",$Datas);
		$makeup_id=$DB->GetRs("makeup_skin","Skin_ID","where Users_ID='".$_SESSION["Users_ID"]."' ORDER BY Skin_ID DESC");
		// echo '<pre>';
		// print_R($makeup_id);
		// exit;
		$Dates = array(
			"Users_ID" => $_SESSION["Users_ID"],
			"Skin_ID" => !empty($makeup_id['Skin_ID'])?$makeup_id['Skin_ID']:0,
			"Home_Json" => $rsSkin['Home_Json']
		);
		$Flag_b=$DB->Add("makeup_home",$Dates);
		if($Flag_a && $Flag_b){
			$Date = array(
				'status' => 1
			);
		}else{
			$Date = array(
				'status' => 0 
			);
		}
		
	}
	echo json_encode($Date,JSON_UNESCAPED_UNICODE);
	exit;
	
	
}else{
	$makeup_data = $DB->GetRs("makeup_skin","*","where Users_ID='".$_SESSION["Users_ID"]."' AND Skin_ID=".$Makeup_Skin_ID);
}

function CategoryList($Makeup_Skin_ID){
  global $DB;
  echo '<option value="">--请选择--</option>';
  $DB->get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID=0 order by Category_Index asc");
  $ParentCategory=array();
  $i=1;
  while($rsPCategory=$DB->fetch_assoc()){
    $ParentCategory[$i]=$rsPCategory;
    $i++;
  }
  foreach($ParentCategory as $key=>$value){
    $DB->get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID=".$value["Category_ID"]." order by Category_Index asc");
    if($DB->num_rows()>0){
      echo '<option value="'.$value["Category_ID"].'">'.$value["Category_Name"].'</option>';
      while($rsCategory=$DB->fetch_assoc()){
        echo '<option value="'.$rsCategory["Category_ID"].'">&nbsp;&nbsp;├'.$rsCategory["Category_Name"].'</option>';
      }
    }else{
      echo '<option value="'.$value["Category_ID"].'">'.$value["Category_Name"].'</option>';
    }
  }
}
function UrlList(){
	global $DB;
	echo '<option value="">--请选择--</option>
	<optgroup label="------------------系统业务模块------------------"></optgroup>';
	$DB->get("wechat_material","Material_ID,Material_Table,Material_Json","where Users_ID='".$_SESSION["Users_ID"]."' and Material_Table<>'0' and Material_TableID=0 and Material_Display=0 order by Material_ID desc");
	while($rsMaterial=$DB->fetch_assoc()){
		$Material_Json=json_decode($rsMaterial['Material_Json'],true);
		echo '<option value="'.$Material_Json["Url"].'">'.$Material_Json['Title'].'</option>';
	}
	echo '<optgroup label="------------------微商城产品分类页面------------------"></optgroup>';
	$DB->get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID=0 order by Category_Index asc");
	$ParentCategory=array();
	$i=1;
	while($rsPCategory=$DB->fetch_assoc()){
		$ParentCategory[$i]=$rsPCategory;
		$i++;
	}
	foreach($ParentCategory as $key=>$value){
		$DB->get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID=".$value["Category_ID"]." order by Category_Index asc");
		if($DB->num_rows()>0){
			echo '<option value="/api/'.$_SESSION["Users_ID"].'/shop/category/'.$value["Category_ID"].'/">'.$value["Category_Name"].'</option>';
			while($rsCategory=$DB->fetch_assoc()){
				echo '<option value="/api/'.$_SESSION["Users_ID"].'/shop/category/'.$rsCategory["Category_ID"].'/">&nbsp;&nbsp;├'.$rsCategory["Category_Name"].'</option>';
			}
		}else{
			echo '<option value="/api/'.$_SESSION["Users_ID"].'/shop/category/'.$value["Category_ID"].'/">'.$value["Category_Name"].'</option>';
		}
	}
	
	echo '<optgroup label="------------------自定义URL------------------"></optgroup>';
	$DB->get("wechat_url","*","where Users_ID='".$_SESSION["Users_ID"]."'");
	while($rsUrl=$DB->fetch_assoc()){
		echo '<option value="'.$rsUrl['Url_Value'].'">'.$rsUrl['Url_Name'].'('.$rsUrl['Url_Value'].')</option>';
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
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <?php //require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/pagemakeup/basic_menubar.php');?>
    <link href='/static/member/css/shop.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js?t=<?php echo time();?>'></script>
    <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
    <script type='text/javascript' src="/third_party/kindeditor/kindeditor-diy.js"></script>
    <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
    <script>
    var editor;
    KindEditor.ready(function(K) {
        editor = K.create('textarea[name="content"]', {
            themeType : 'simple',
            filterMode : true,
            uploadJson : '/member/upload_json.php?TableField=shop_home',
            fileManagerJson : '/member/file_manager_json.php',
            allowFileManager : true,
            items : [
                'source', '|', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
                'removeformat', 'undo', 'redo', '/', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link' , '|', 'preview']
        });
    })
    </script>
	<link href='/static/member/css/shop.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
	<script type='text/javascript' src='/static/js/plugin/dragsort/ca_drag_shop.js'></script> 
    <script type='text/javascript' src='/static/js/plugin/dragsort/ca_orderDrag_shop.js'></script> 
    <script type='text/javascript' src='/static/js/plugin/colorpicker/js/colorpicker.js'></script>
    <link href='/static/js/plugin/colorpicker/css/colorpicker.css' rel='stylesheet' type='text/css' />
    <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
    <script type="text/javascript">
    var myeditor = "";
    var pName    = ""; //全局变量记录当前使用组件名称
    var Skin_ID  = "<?php echo $rsConfig['Makeup_Skin_ID'];?>";
    $(function(){
        shop_obj.diy_init();
        shop_obj.colorPicker();
    });
    </script>
<div  class="r_con_form">
	<div class="rows">
	  <label>模板名称</label>
	   <span class="input">
	  <input type="text" class="form_input" name="skin_name" id="skin_name" value="<?php echo isset($makeup_data['Skin_Name'])?$makeup_data['Skin_Name']:''; ?>" maxlength="100" size="35" notnull />
	  <input type="hidden" name="Skin_ID" id="Skin_ID" value="<?php echo isset($Makeup_Skin_ID)?$Makeup_Skin_ID:''; ?>">
	  <font class="fc_red">*</font></span>
	  <div class="clear"></div>
	</div>
	<div style="width:100%; background:#FFF; padding:10px 0px">
		<span onclick="openwin();" style="display:block; cursor:pointer; width:100px; height:36px; line-height:36px; margin:0px 0px 0px 660px; background:#3AA0EB; color:#FFF; text-align:center; font-size:14px; border-radius:5px;">首页预览</span>
		<script language="javascript">
		function openwin(){
			var win_top = ($(window).height()-750)/2;
			var win_left = ($(window).width()-480)/2;
			window.open('/api/<?php echo $_SESSION["Users_ID"];?>/shop/makeup/<?=$Makeup_Skin_ID?>/','','height=750,width=480,top='+win_top+',left='+win_left+',toolbar=no,menubar=no,scrollbars=yes,resizable=no,location=no,status=no');
		}
		</script>
	</div>
	<div class="r_con_config r_con_wrap"> 
  		<!-- 组件栏start -->
        <div class="package_sprite">
            <div class="sp1">组件</div>
            <div class="sp2"> 
              <!-- 图标start -->
              <div class="square_sprite" packageName="p1">
                <div class="square_img"><img src="/static/member/images/shop/diy/l5.jpg" /><span></span></div>
                <div class="square_name">一行两列</div>
              </div>
              <div class="square_sprite" packageName="p0">
                <div class="square_img"><img src="/static/member/images/shop/diy/l6.jpg" /><span></span></div>
                <div class="square_name">一行三列</div>
              </div>              
              <div class="square_sprite" packageName="p6">
                <div class="square_img"><img src="/static/member/images/shop/diy/l7.jpg" /><span></span></div>
                <div class="square_name">一行四列</div>
              </div>
              <div class="square_sprite" packageName="p7">
                <div class="square_img"><img src="/static/member/images/shop/diy/l8.jpg" /><span></span></div>
                <div class="square_name">一行五列</div>
              </div>
              <div class="square_sprite" packageName="p8">
                <div class="square_img"><img src="/static/member/images/shop/diy/l9.jpg" /><span></span></div>
                <div class="square_name">左一右二</div>
              </div>
              <div class="square_sprite" packageName="p9">
                <div class="square_img"><img src="/static/member/images/shop/diy/l10.jpg" /><span></span></div>
                <div class="square_name">左一右四</div>
              </div>
              <div class="square_sprite" packageName="p10">
                <div class="square_img"><img src="/static/member/images/shop/diy/l11.jpg" /><span></span></div>
                <div class="square_name">左二右一</div>
              </div>
              <div class="square_sprite" packageName="p11">
                <div class="square_img"><img src="/static/member/images/shop/diy/l12.jpg" /><span></span></div>
                <div class="square_name">左四右一</div>
              </div>
              <div class="square_sprite" packageName="p2">
                <div class="square_img"><img src="/static/member/images/shop/diy/l1.jpg" /><span></span></div>
                <div class="square_name">文字</div>
              </div>
              <div class="square_sprite" packageName="p3">
                <div class="square_img"><img src="/static/member/images/shop/diy/l2.jpg" /><span></span></div>
                <div class="square_name">图片</div>
              </div>
              <div class="square_sprite" packageName="p4">
                <div class="square_img"><img src="/static/member/images/shop/diy/l3.jpg" /><span></span></div>
                <div class="square_name">幻灯片</div>
              </div>
              <div class="square_sprite" packageName="p5">
                <div class="square_img"><img src="/static/member/images/shop/diy/l4.jpg" /><span></span></div>
                <div class="square_name">电话拨号</div>
              </div>
			  <div class="square_sprite" packageName="p12">
                <div class="square_img"><img src="/static/member/images/shop/diy/l13.jpg" /><span></span></div>
                <div class="square_name">搜索框</div>
              </div>
              <div class="square_sprite" packageName="p13">
                <div class="square_img"><img src="/static/member/images/shop/diy/l13.jpg" /><span></span></div>
                <div class="square_name">分类列表</div>
              </div>
              <div class="square_sprite" packageName="p14">
                <div class="square_img"><img src="/static/member/images/shop/diy/l13.jpg" /><span></span></div>
                <div class="square_name">产品列表</div>
              </div>
              <!-- 图标end --> 
            </div>
        </div>
  		<!-- 组件栏end --> 
  		<!-- 视图start -->
        <div class="ipad_sprite">
            <div class="ipad isNon">
              <?php if(empty($json)){
                  echo '<div id="ipadNotice">拖动组件到中心区域</div>';
              }else{
                  foreach($json as $key=>$value){
                    if($value['type'] != 'p13' && $value['type'] != 'p14'){
                      $url=explode('|',$value['url']);
                      $pic=explode('|',$value['pic']);
                      $txt=explode('|',$value['txt']);
                    }
                     
					  if($value['type']=='p1'){//一行两列
                          $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p1 sprite1" packageName="p1" link0="'.$url[0].'" link1="'.$url[1].'" color0="'.$txtColor[0].'" color1="'.$txtColor[1].'" background0="'.$bgColor[0].'" background1="'.$bgColor[1].'">
                          <div class="dragPart">
                            <div class="p1ImgFrame">
                              <div class="imgObj"><img src="'.$pic[0].'" width="146" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[0].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p1ImgFrame">
                              <div class="imgObj"><img src="'.$pic[1].'" width="146" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[1].'</div>
                              <div class="clean"></div>
                            </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p1\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
                      }elseif($value['type']=='p0'){//一行三列
                          $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p0 sprite1" packageName="p0" link0="'.$url[0].'" link1="'.$url[1].'" link2="'.$url[2].'" color0="'.$txtColor[0].'" color1="'.$txtColor[1].'" color2="'.$txtColor[2].'" background0="'.$bgColor[0].'" background1="'.$bgColor[1].'" background2="'.$bgColor[2].'">
                          <div class="dragPart">
                            <div class="p0ImgFrame">
                              <div class="imgObj"><img src="'.$pic[0].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[0].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p0ImgFrame">
                              <div class="imgObj"><img src="'.$pic[1].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[1].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p0ImgFrame">
                              <div class="imgObj"><img src="'.$pic[2].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[2].'</div>
                              <div class="clean"></div>
                            </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p0\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
					  }elseif($value['type']=='p6'){//一行四列
						  $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p6 sprite1" packageName="p6" link0="'.$url[0].'" link1="'.$url[1].'" link2="'.$url[2].'" link3="'.$url[3].'" color0="'.$txtColor[0].'" color1="'.$txtColor[1].'" color2="'.$txtColor[2].'" color3="'.$txtColor[3].'" background0="'.$bgColor[0].'" background1="'.$bgColor[1].'" background2="'.$bgColor[2].'" background3="'.$bgColor[3].'">
                          <div class="dragPart">
                            <div class="p6ImgFrame">
                              <div class="imgObj"><img src="'.$pic[0].'" width="73" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[0].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p6ImgFrame">
                              <div class="imgObj"><img src="'.$pic[1].'" width="73" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[1].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p6ImgFrame">
                              <div class="imgObj"><img src="'.$pic[2].'" width="73" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[2].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="p6ImgFrame">
                              <div class="imgObj"><img src="'.$pic[3].'" width="73" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[3].'</div>
                              <div class="clean"></div>
                            </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p6\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
					  }elseif($value['type']=='p7'){//一行五列
						  $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p7 sprite1" packageName="p7" link0="'.$url[0].'" link1="'.$url[1].'" link2="'.$url[2].'" link3="'.$url[3].'" link4="'.$url[4].'" color0="'.$txtColor[0].'" color1="'.$txtColor[1].'" color2="'.$txtColor[2].'" color3="'.$txtColor[3].'" color4="'.$txtColor[4].'" background0="'.$bgColor[0].'" background1="'.$bgColor[1].'" background2="'.$bgColor[2].'" background3="'.$bgColor[3].'" background4="'.$bgColor[4].'">
                          <div class="dragPart">
                            <div class="p7ImgFrame">
                              <div class="imgObj"><img src="'.$pic[0].'" width="58" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[0].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p7ImgFrame">
                              <div class="imgObj"><img src="'.$pic[1].'" width="58" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[1].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p7ImgFrame">
                              <div class="imgObj"><img src="'.$pic[2].'" width="58" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[2].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="p7ImgFrame">
                              <div class="imgObj"><img src="'.$pic[3].'" width="58" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[3].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="p7ImgFrame">
                              <div class="imgObj"><img src="'.$pic[4].'" width="58" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[4].'</div>
                              <div class="clean"></div>
                            </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p7\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
					  }elseif($value['type']=='p8'){//左一右二
                          $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p8 sprite1" packageName="p8" link0="'.$url[0].'" link1="'.$url[1].'" link2="'.$url[2].'" color0="'.$txtColor[0].'" color1="'.$txtColor[1].'" color2="'.$txtColor[2].'" background0="'.$bgColor[0].'" background1="'.$bgColor[1].'" background2="'.$bgColor[2].'">
                          <div class="dragPart">
						   <div class="left8">
                            <div class="p8ImgFrame">
                              <div class="imgObj"><img src="'.$pic[0].'" width="146" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[0].'</div>
                              <div class="clean"></div>
                            </div>
						   </div>
						   <div class="right8">
                            <div class="p8ImgFrame">
                              <div class="imgObj"><img src="'.$pic[1].'" width="146" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[1].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p8ImgFrame">
                              <div class="imgObj"><img src="'.$pic[2].'" width="146" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[2].'</div>
                              <div class="clean"></div>
                            </div>
						   </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p8\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
					  }elseif($value['type']=='p9'){//左一右四
                          $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p9 sprite1" packageName="p9" link0="'.$url[0].'" link1="'.$url[1].'" link2="'.$url[2].'" link3="'.$url[3].'" link4="'.$url[4].'" color0="'.$txtColor[0].'" color1="'.$txtColor[1].'" color2="'.$txtColor[2].'" color3="'.$txtColor[3].'" color4="'.$txtColor[4].'" background0="'.$bgColor[0].'" background1="'.$bgColor[1].'" background2="'.$bgColor[2].'" background3="'.$bgColor[3].'" background4="'.$bgColor[4].'">
                          <div class="dragPart">
						   <div class="left9">
                            <div class="p9ImgFrame">
                              <div class="imgObj"><img src="'.$pic[0].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[0].'</div>
                              <div class="clean"></div>
                            </div>
						   </div>
						   <div class="right9">
                            <div class="p9ImgFrame">
                              <div class="imgObj"><img src="'.$pic[1].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[1].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p9ImgFrame">
                              <div class="imgObj"><img src="'.$pic[2].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[2].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="p9ImgFrame">
                              <div class="imgObj"><img src="'.$pic[3].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[3].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="p9ImgFrame">
                              <div class="imgObj"><img src="'.$pic[4].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[4].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="clean"></div>
						   </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p9\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
					  }elseif($value['type']=='p10'){//左二右一
                          $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p10 sprite1" packageName="p10" link0="'.$url[0].'" link1="'.$url[1].'" link2="'.$url[2].'" color0="'.$txtColor[0].'" color1="'.$txtColor[1].'" color2="'.$txtColor[2].'" background0="'.$bgColor[0].'" background1="'.$bgColor[1].'" background2="'.$bgColor[2].'">
                          <div class="dragPart">
						   <div class="left10">
                            <div class="p10ImgFrame">
                              <div class="imgObj"><img src="'.$pic[0].'" width="146" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[0].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="p10ImgFrame">
                              <div class="imgObj"><img src="'.$pic[1].'" width="146" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[1].'</div>
                              <div class="clean"></div>
                            </div>
						   </div>
						   <div class="right10">
                            <div class="p10ImgFrame">
                              <div class="imgObj"><img src="'.$pic[2].'" width="146" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[2].'</div>
                              <div class="clean"></div>
                            </div>
						   </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p10\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
					  }elseif($value['type']=='p11'){//左四右一
                          $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p11 sprite1" packageName="p11" link0="'.$url[0].'" link1="'.$url[1].'" link2="'.$url[2].'" link3="'.$url[3].'" link4="'.$url[4].'" color0="'.$txtColor[0].'" color1="'.$txtColor[1].'" color2="'.$txtColor[2].'" color3="'.$txtColor[3].'" color4="'.$txtColor[4].'" background0="'.$bgColor[0].'" background1="'.$bgColor[1].'" background2="'.$bgColor[2].'" background3="'.$bgColor[3].'" background4="'.$bgColor[4].'">
                          <div class="dragPart">
						   <div class="left11">
                            <div class="p11ImgFrame">
                              <div class="imgObj"><img src="'.$pic[0].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[0].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="p11ImgFrame">
                              <div class="imgObj"><img src="'.$pic[1].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[1].'</div>
                              <div class="clean"></div>
                            </div>
                            <div class="p11ImgFrame">
                              <div class="imgObj"><img src="'.$pic[2].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[2].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="p11ImgFrame">
                              <div class="imgObj"><img src="'.$pic[3].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[3].'</div>
                              <div class="clean"></div>
                            </div>
						   </div>
						   <div class="right11">
							<div class="p11ImgFrame">
                              <div class="imgObj"><img src="'.$pic[4].'" width="95" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[4].'</div>
                              <div class="clean"></div>
                            </div>
							<div class="clean"></div>
						   </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p11\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
                      }elseif($value['type']=='p2'){//文字
                          $txt[0] = str_replace('&quot;','"',$txt[0]);
                          echo '<div class="p2 sprite1" packageName="p2" link0="">
                          <div class="dragPart">'.$txt[0].'</div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p2\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                        </div>';
                      }elseif($value['type']=='p3'){//图片
                          $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p3 sprite1" packageName="p3" link0="'.$url[0].'" color0="'.$txtColor[0].'" background0="'.$bgColor[0].'">
                          <div class="dragPart">
                            <div class="p3ImgFrame">
                              <div class="imgObj"><img src="'.$pic[0].'" width="292" /></div>
                              <div class="wordObj" style="color:; background:">'.$txt[0].'</div>
                              <div class="clean"></div>
                            </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p3\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
                      }elseif($value['type']=='p4'){//幻灯片
                          echo '<div class="p4 sprite1" packageName="p4" link0="'.$url[0].'" link1="'.$url[1].'" link2="'.$url[2].'" link3="'.$url[3].'" link4="'.$url[4].'">
                          <div class="dragPart">
                            <div class="p4ImgFrame"><img width="292"'.($pic[0]=='undefined'?' style="display:none"':' src="'.$pic[0].'" style="display:block"').'/><img width="292"'.($pic[1]=='undefined'?' style="display:none"':' src="'.$pic[1].'" style="display:none"').'/><img width="292"'.($pic[2]=='undefined'?' style="display:none"':' src="'.$pic[2].'" style="display:none"').'/><img width="292"'.($pic[3]=='undefined'?' style="display:none"':' src="'.$pic[3].'" style="display:none"').'/><img width="292"'.($pic[4]=='undefined'?' style="display:none"':' src="'.$pic[4].'" style="display:none"').'/></div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p4\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                        </div>';
                      }elseif($value['type']=='p5'){//电话拨号
                          echo '<div style="background:'.$value['bgColor'].'" class="p5 sprite1" packageName="p5" background0="'.$value['bgColor'].'" color0="'.$value['txtColor'].'" fontsize0="'.$value['fontSize'].'px" >
                          <div class="dragPart" style="color:'.$value['txtColor'].'; font-size:'.$value['fontSize'].'px;">'.$value['txt'].'</div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p5\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                        </div>';
                      }elseif($value['type']=='p12'){//搜索框
                          $txtColor=explode('|',$value['txtColor']);
                          $bgColor=explode('|',$value['bgColor']);
                          echo '<div class="p12 sprite1" packageName="p12" color0="'.$txtColor[0].'" background0="'.$bgColor[0].'">
                          <div class="dragPart">
                            <div class="p12ImgFrame" style="background:'.$bgColor[0].'">
                              <div class="submit"><img src="'.$pic[0].'" /></div>
                              <input type="text" class="input" style="border:1px solid '.$txtColor[0].'" />
                              <div class="clean"></div>
                            </div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this, \'p12\');"><img  src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                        </div>';
                      }elseif($value['type'] == 'p13') {//分类
                        if(isset($value['p_title'])) {
                          $p_title = $value['p_title'];
                        } else {
                          $p_title = '';
                        }
                        if(isset($value['check_more'])) {
                          $check_more = $value['check_more'];
                        } else {
                          $check_more = '';
                        }
                        echo '<div class="p13 sprite1" packageName="p13"  num="'.$value['num'].'" type="'.$value['top_type'].'" txt="'.$value['txt'].'" list_type="'.$value['list_type'].'" nature="'.$value['nature'].'"  order="'.$value['order'].'" category="'.$value['category'].'" p_title="'.$p_title.'" check_more="'.$check_more.'">
                          <div class="dragPart">
                            <div class="w">';
                              if($value['top_type'] == '1') {
                                 echo '<div class="biaot_x">
                                  <span class="fenlei_x l">'.$p_title.'</span>';
                                  if($check_more == '1'){
                                    echo  '<span class="more r"><a href="#">'.$value['txt'].'>></a></span>';
                                  }
                                  echo '</div>';
                              }
                              if($value['top_type'] == '2') {
                                echo '<div class="biaot1_x">
                                  <span class="fenlei_x l">'.$p_title.'</span>';
                                  if($check_more == '1'){
                                    echo '<span class="more r"><a href="#">></a></span>';
                                  }
                                  echo '</div>';
                              }
                              if($value['top_type'] == '3') {
                                echo '<div class="biaot2_x">
                                  <span class="fenlei_x l">'.$p_title.'</span>';
                                  if($check_more == '1'){
                                    echo '<span class="more r"><a href="#"><span style="margin-right:15px;">'.$value['txt'].'</span></a></span>';
                                  }
                                echo '</div>';
                              }
                              if($value['top_type'] == '4') {
                                echo '<div class="biaot3_x">
                                  <div class="fenlei3_x l">'.$p_title.'</div>';
                                  if($check_more == '1'){
                                    echo '<div class="more r"><a href="#">'.$value['txt'].'<span style="color:#e30101; font-family:宋体; font-weight:bold;">></span></a></div>';
                                  }
                                echo '</div>';
                              }
                              if(!empty($value['num'])){
                                $num = $value['num'];
                              }else{
                                $num = 2;
                              }
                              if(!empty($value['nature'])) {
                                $nature = explode(',',rtrim($value['nature'],','));
                              }else{
                                $nature = array();
                              }
                              if(!empty($nature)) {
                                if(in_array('integration', $nature)) {
                                  $integration = '1';
                                }
                                if(in_array('sale', $nature)) {
                                  $sales = '1';
                                }
                                if(in_array('dis', $nature)) {
                                  $dis = '1';
                                }
                                if(in_array('cart', $nature)) {
                                  $cart = '1';
                                }
                              }
                              if($value['list_type'] == '1') {
                                echo '<div class="chanpint_x">
                                  <ul>';
                                    for($i=0;$i<$num;$i++) {
                                      echo '<li>
                                        <a href="#"><span class="cpt_x l"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp01.jpg"></span></a>
                                        <span class="cpxq_x l">
                                          <div class="cpbt_x"><a href="#">蛋糕蛋糕蛋糕蛋糕</a></div>
                                            <span class="jiaget_x l">￥36.5';
                                            if(isset($dis)) {
                                              echo '<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 2px; font-weight:normal;">9.5折</a>';
                                            }
                                            echo '</span>'; 
                                            if(isset($integration)) {
                                              echo '<span class="r" style="font-size:13px; color:#999; line-height:28px;">积分：20</span>';
                                            }
                                            echo ' <div class="clear"></div>';
                                            if(isset($sales)) {
                                              echo '<span class="l" style="font-size:13px; color:#999; line-height:26px;">已售1993</span>';
                                            }
                                          
                                         
                                          if(isset($cart)) {
                                            echo '<span class="r" style="margin-top:3px;"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/gwct_x.png" width="20" height="20"></span>';
                                          }
                                        echo '</span>
                                      </li>';  
                                    }
                                  echo '</ul>
                                </div>';
                              } elseif($value['list_type'] == '2') {
                                echo '<div class="chanpint1_x">
                                  <ul>';
                                  for($i=0; $i<$num; $i++) {
                                    echo '<li>
                                      <a href="#"><span class="cpt_x"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp01.jpg"></span></a>
                                        <div class="cpxq1_x">
                                          <div class="cpbt_x"><a href="#">蛋糕蛋糕蛋糕蛋糕</a></div>';
                                          if(isset($integration)) {
                                            echo '<span class="l" style="font-size:13px; color:#999; line-height:22px;">积分：20</span>';
                                          }
                                          if(isset($sales)) {
                                            echo '<span class="r" style="font-size:13px; color:#999; line-height:24px;">已售1993</span>';
                                          }
                                          echo '<div class="clear"></div>';
                                          if(isset($dis)) {
                                            echo '<span class="jiaget_x l">￥36.5<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 3px;">9.5折</a></span>';
                                          } else {
                                            echo '<span class="jiaget_x l">￥36.5</span>';
                                          }
                                          if(isset($cart)) {
                                            echo '<span class="r" style="margin-top:3px;"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/gwct_x.png" width="20" height="20"></span>';
                                          }
                                        echo '</div>
                                    </li>';
                                  }
                                  echo '</ul>
                                </div>';
                              } elseif($value['list_type'] == '3') {
                                echo '<div class="chanpint2_x">
                                  <ul>';
                                  for($i=0;$i<$num;$i++) {
                                    echo '<li>
                                      <a href="#"><span class="cpt_x"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp03.jpg"></span></a>
                                        <div class="cpxq1_x">
                                          <div class="cpbt_x"><a href="#">蛋糕蛋糕蛋糕蛋糕</a></div> ';
                                          if(isset($integration)) {
                                            echo '<span class="l" style="font-size:13px; color:#999; line-height:22px;">积分：20分</span>';
                                          }
                                          if(isset($sales)) {
                                            echo '<span class="r" style="font-size:13px; color:#999; line-height:24px;">已售1993</span>';
                                          }
                                          echo '<div class="clear"></div>';
                                          if(isset($dis)) {
                                            echo '<span class="jiaget_x l">￥36.5<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 3px;">9.5折</a></span>';
                                          } else {
                                            echo '<span class="jiaget_x l">￥36.5</span>';
                                          }
                                          if(isset($cart)) {
                                            echo ' <span class="r" style="margin-top:3px;"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/gwct_x.png" width="20" height="20"></span>';
                                          }
                                        echo '</div>
                                    </li>';
                                  }
                                  echo '</ul>
                                </div>';
                              } elseif ($value['list_type'] == '4') {
                                echo '<div class="chanpint3_x">
                                  <ul>';
                                  $n = floor($num/3);
                                  for($i = 0;$i < $n;$i++){
                                    echo '<li>
                                      <span class="big_pr l">
                                        <a href="#">';
                                        echo '<div class="pic"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp01.jpg"></div>';
                                        if(isset($cart)){
                                          echo '<div class="name">小苹果小苹果小苹果小苹果小苹果小苹果小苹果<br><span class="price l">￥123.00</span><span class="r" style="margin-top:3px; margin-right:3px"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/gwct_x.png" width="20" height="20"></span></div>';
                                        }else{
                                          echo '<div class="name">小苹果小苹果小苹果小苹果小苹果小苹果小苹果<br><span class="price l">￥123.00</span></div>';
                                        }
                                          
                                        echo '</a>
                                      </span>
                                      <span class="smal_pr l">
                                        <div class="small_pr">
                                              <a href="#">
                                              <span class="name l"><span class="price">￥150.00</span><br>阿卡卡</span>
                                              <span class="pic l"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp03.jpg"></span>
                                              </a>
                                          </div> 
                                          <div class="clear"></div>
                                          <div>
                                              <a href="#">
                                              <span class="name l"><span class="price">￥150.00</span><br>阿卡卡</span>
                                              <span class="pic l"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp02.jpg"></span>
                                              </a>
                                          </div>                  
                                      </span>
                                    </li>';  
                                  }
                                  echo '</ul>
                                </div>';
                              }
                          echo '</div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this,\'p13\');"><img src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                          </div>';
                      }elseif($value['type'] == 'p14'){
                        if(isset($value['p_title'])) {
                          $p_title = $value['p_title'];
                        } else {
                          $p_title = '';
                        }
                        if(isset($value['check_more'])) {
                          $check_more = $value['check_more'];
                        } else {
                          $check_more = '';
                        }
                        echo '<div class="p14 sprite1" packageName="p14"  num="'.$value['num'].'" type="'.$value['top_type'].'" txt="'.$value['txt'].'" list_type="'.$value['list_type'].'" nature="'.$value['nature'].'"  order="'.$value['order'].'" def="'.$value['def'].'" p_title="'.$p_title.'" check_more="'.$check_more.'">
                        <div class="dragPart">
                          <div class="w">';
                            if($value['top_type'] == '1') {
                              echo '<div class="biaot_x">
                                <span class="fenlei_x l">'.$p_title.'</span>';
                                if($check_more == '1'){
                                  echo  '<span class="more r"><a href="#">'.$value['txt'].'>></a></span>';
                                }
                                echo '</div>';
                            }
                            if($value['top_type'] == '2') {
                              echo '<div class="biaot1_x">
                                <span class="fenlei_x l">'.$p_title.'</span>';
                                if($check_more == '1'){
                                  echo '<span class="more r"><a href="#">></a></span>';
                                }
                                echo '</div>';
                            }
                            if($value['top_type'] == '3') {
                              echo '<div class="biaot2_x">
                                <span class="fenlei_x l">'.$p_title.'</span>';
                                if($check_more == '1'){
                                  echo '<span class="r"><a href="#"><span style="margin-right:15px;">'.$value['txt'].'</span></a></span>';
                                }
                              echo '</div>';
                            }
                            if($value['top_type'] == '4') {
                              echo '<div class="biaot3_x">
                                <div class="fenlei3_x l">'.$p_title.'</div>';
                                if($check_more == '1'){
                                  echo '<div class="more r"><a href="#">'.$value['txt'].'<span style="color:#e30101; font-family:宋体; font-weight:bold;">></span></a></div>';
                                }
                              echo '</div>';
                            }
                            if(!empty($value['num'])) {
                              $num = $value['num'];
                            } else {
                              $num = 2;
                            }
                            if(!empty($value['nature'])) {
                              $nature = explode(',',rtrim($value['nature'],','));
                            }else{
                              $nature = array();
                            }
                            if(!empty($nature)) {
                              if(in_array('integration', $nature)) {
                                $integration1 = '1';
                              }
                              if(in_array('sale', $nature)) {
                                $sales1 = '1';
                              }
                              if(in_array('dis', $nature)) {
                                $dis1 = '1';
                              }
                              if(in_array('cart', $nature)) {
                                $cart1 = '1';
                              }
                            }
                            if($value['list_type'] == '1') {
                                echo '<div class="chanpint_x">
                                  <ul>';
                                    for($i=0;$i<$num;$i++) {
                                      echo '<li>
                                        <a href="#"><span class="cpt_x l"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp01.jpg"></span></a>
                                        <span class="cpxq_x l">
                                          <div class="cpbt_x"><a href="#">蛋糕蛋糕蛋糕蛋糕</a></div>
                                            <span class="jiaget_x l">￥36.5';
                                            if(isset($dis1)) {
                                              echo '<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 2px; font-weight:normal;">9.5折</a>';
                                            }
                                            echo '</span>'; 
                                            if(isset($integration1)) {
                                              echo '<span class="r" style="font-size:13px; color:#999; line-height:28px;">积分：20</span>';
                                            }
                                            echo ' <div class="clear"></div>';
                                            if(isset($sales1)) {
                                              echo '<span class="l" style="font-size:13px; color:#999; line-height:26px;">已售1993</span>';
                                            }
                                          if(isset($cart1)) {
                                            echo '<span class="r" style="margin-top:3px;"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/gwct_x.png" width="20" height="20"></span>';
                                          }
                                        echo '</span>
                                      </li>';  
                                    }
                                  echo '</ul>
                                </div>';
                              } elseif($value['list_type'] == '2') {
                                echo '<div class="chanpint1_x">
                                  <ul>';
                                  for($i=0; $i<$num; $i++) {
                                    echo '<li>
                                      <a href="#"><span class="cpt_x"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp01.jpg"></span></a>
                                        <div class="cpxq1_x">
                                          <div class="cpbt_x"><a href="#">蛋糕蛋糕蛋糕蛋糕</a></div>';
                                          if(isset($integration1)) {
                                            echo '<span class="l" style="font-size:13px; color:#999; line-height:22px;">积分：20</span>';
                                          }
                                          if(isset($sales1)) {
                                            echo '<span class="r" style="font-size:13px; color:#999; line-height:24px;">已售1993</span>';
                                          }
                                          echo '<div class="clear"></div>';
                                          if(isset($dis1)) {
                                            echo '<span class="jiaget_x l">￥36.5<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 3px;">9.5折</a></span>';
                                          } else {
                                            echo '<span class="jiaget_x l">￥36.5</span>';
                                          }
                                          if(isset($cart1)) {
                                            echo '<span class="r" style="margin-top:3px;"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/gwct_x.png" width="20" height="20"></span>';
                                          }
                                        echo '</div>
                                    </li>';
                                  }
                                  echo '</ul>
                                </div>';
                              } elseif($value['list_type'] == '3') {
                                echo '<div class="chanpint2_x">
                                  <ul>';
                                  for($i=0;$i<$num;$i++) {
                                    echo '<li>
                                      <a href="#"><span class="cpt_x"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp03.jpg"></span></a>
                                        <div class="cpxq1_x">
                                          <div class="cpbt_x"><a href="#">蛋糕蛋糕蛋糕蛋糕</a></div> ';
                                          if(isset($integration1)) {
                                            echo '<span class="l" style="font-size:13px; color:#999; line-height:22px;">积分：20分</span>';
                                          }
                                          if(isset($sales1)) {
                                            echo '<span class="r" style="font-size:13px; color:#999; line-height:24px;">已售1993</span>';
                                          }
                                          echo '<div class="clear"></div>';
                                          if(isset($dis1)) {
                                            echo '<span class="jiaget_x l">￥36.5<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 3px;">9.5折</a></span>';
                                          } else {
                                            echo '<span class="jiaget_x l">￥36.5</span>';
                                          }
                                          if(isset($cart1)) {
                                            echo ' <span class="r" style="margin-top:3px;"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/gwct_x.png" width="20" height="20"></span>';
                                          }
                                        echo '</div>
                                    </li>';
                                  }
                                  echo '</ul>
                                </div>';
                              } elseif ($value['list_type'] == '4') {
                                echo '<div class="chanpint3_x">
                                  <ul>';
                                  $n = floor($num/3);
                                  for($i = 0;$i < $n;$i++){
                                    echo '<li>
                                      <span class="big_pr l">
                                        <a href="#">';
                                        echo '<div class="pic"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp01.jpg"></div>';
                                        if(isset($cart1)){
                                          echo '<div class="name">小苹果小苹果小苹果小苹果小苹果小苹果小苹果<br><span class="price l">￥123.00</span><span class="r" style="margin-top:3px; margin-right:3px"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/gwct_x.png" width="20" height="20"></span></div>';
                                        }else{
                                          echo '<div class="name">小苹果小苹果小苹果小苹果小苹果小苹果小苹果<br><span class="price l">￥123.00</span></div>';
                                        }
                                          
                                        echo '</a>
                                      </span>
                                      <span class="smal_pr l">
                                        <div class="small_pr">
                                              <a href="#">
                                              <span class="name l"><span class="price">￥150.00</span><br>阿卡卡</span>
                                              <span class="pic l"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp03.jpg"></span>
                                              </a>
                                          </div> 
                                          <div class="clear"></div>
                                          <div>
                                              <a href="#">
                                              <span class="name l"><span class="price">￥150.00</span><br>阿卡卡</span>
                                              <span class="pic l"><img src="/static/api/shop/skin/'.$rsConfig['Makeup_Skin_ID'].'/tp02.jpg"></span>
                                              </a>
                                          </div>                  
                                      </span>
                                    </li>';  
                                  }
                                  echo '</ul>
                                </div>';
                              }
                        echo '</div>
                          </div>
                          <div class="delObj hand" onclick="shop_obj.delObjEvt(this,\'p14\');"><img src="/static/member/images/shop/diy/del.png" /></div>
                          <div class="clean"></div>
                          </div>';
                      }
                  }
              }?>
            </div>
        </div>
  		<!-- 视图end --> 
  		<!-- 属性start -->
      <div class="property_sprite">
        <div class="ps1">
          <div class="ps1_1"></div>
          <div class="ps1_2">组件属性编辑</div>
        </div>
        <div class="ps2">
          <div class="ps2Notice">组件属性编辑面板</div>
          
          <!--一行两列-->
          <div class="ps2_frmae_p1">
            <div class="pNotice">注：图片高度保持一致(宽：320px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p1_0" />
                </div>
                <div class="uploadNotice"></div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp1_0" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp1_0" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p1_1" />
                </div>
                <div class="uploadNotice"></div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域2" onFocus="this.value=='文字区域2'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp1_1" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp1_1" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p1.insertWord("p1")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!--一行三列-->
          <div class="ps2_frmae_p0">
            <div class="pNotice">注：图片高度保持一致(宽：213px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p0_0" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp0_0" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp0_0" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p0_1" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域2" onFocus="this.value=='文字区域2'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp0_1" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp0_1" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p0_2" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域3" onFocus="this.value=='文字区域3'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp0_2" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp0_2" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p0.insertWord("p0")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!--一行四列-->
          <div class="ps2_frmae_p6">
            <div class="pNotice">注：图片高度保持一致(宽：160px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p6_0" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp6_0" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp6_0" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p6_1" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域2" onFocus="this.value=='文字区域2'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp6_1" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp6_1" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p6_2" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域3" onFocus="this.value=='文字区域3'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp6_2" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp6_2" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p6_3" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域4" onFocus="this.value=='文字区域4'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp6_3" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp6_3" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p6.insertWord("p6")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!--一行五列-->
          <div class="ps2_frmae_p7">
            <div class="pNotice">注：图片高度保持一致(宽：128px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p7_0" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp7_0" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp7_0" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p7_1" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域2" onFocus="this.value=='文字区域2'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp7_1" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp7_1" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p7_2" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域3" onFocus="this.value=='文字区域3'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp7_2" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp7_2" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p7_3" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域4" onFocus="this.value=='文字区域4'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp7_3" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp7_3" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p7_4" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="文字区域5" onFocus="this.value=='文字区域5'?this.value='':''" />
              </div>
              <div class="colorTitle">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp7_4" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp7_4" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p7.insertWord("p7")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!--左一右二-->
          <div class="ps2_frmae_p8">
            <div class="pNotice">注：图片高度保持一致(宽：320px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p8_0" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp8_0" class="txtColor" value="#ffffff" readonly style="display:none" />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp8_0" class="bgColor" value="#4C4C4C" readonly style="display:none" />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p8_1" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域2" onFocus="this.value=='文字区域2'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp8_1" style="display:none" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px; display:none">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp8_1" style="display:none" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p8_2" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域3" onFocus="this.value=='文字区域3'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorWordp8_2" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorBgp8_2" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p8.insertWord("p8")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!--左一右四-->
          <div class="ps2_frmae_p9">
            <div class="pNotice">注：图片高度保持一致(宽：213px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p9_0" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp9_0" class="txtColor" value="#ffffff" readonly style="display:none" />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp9_0" class="bgColor" value="#4C4C4C" readonly style="display:none" />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p9_1" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域2" onFocus="this.value=='文字区域2'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp9_1" style="display:none" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px; display:none">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp9_1" style="display:none" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p9_2" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域3" onFocus="this.value=='文字区域3'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorWordp9_2" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorBgp9_2" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p9_3" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域4" onFocus="this.value=='文字区域4'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorWordp9_3" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorBgp9_3" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p9_4" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域5" onFocus="this.value=='文字区域5'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorWordp9_4" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorBgp9_4" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p9.insertWord("p9")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!--左二右一-->
          <div class="ps2_frmae_p10">
            <div class="pNotice">注：图片高度保持一致(宽：320px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p10_0" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp10_0" class="txtColor" value="#ffffff" readonly style="display:none" />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp10_0" class="bgColor" value="#4C4C4C" readonly style="display:none" />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p10_1" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域2" onFocus="this.value=='文字区域2'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp10_1" style="display:none" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px; display:none">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp10_1" style="display:none" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p10_2" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域3" onFocus="this.value=='文字区域3'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorWordp10_2" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorBgp10_2" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p10.insertWord("p10")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!--左四右一-->
          <div class="ps2_frmae_p11">
            <div class="pNotice">注：图片高度保持一致(宽：213px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p11_0" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp11_0" class="txtColor" value="#ffffff" readonly style="display:none" />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp11_0" class="bgColor" value="#4C4C4C" readonly style="display:none" />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p11_1" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域2" onFocus="this.value=='文字区域2'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp11_1" style="display:none" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px; display:none">背景：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp11_1" style="display:none" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p11_2" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域3" onFocus="this.value=='文字区域3'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorWordp11_2" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorBgp11_2" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p11_3" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域4" onFocus="this.value=='文字区域4'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorWordp11_3" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorBgp11_3" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p11_4" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="文字区域5" onFocus="this.value=='文字区域5'?this.value='':''" />
              </div>
              <div class="colorTitle" style="display:none">颜色：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorWordp11_4" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;display:none">背景：</div>
              <input type="text" maxlength="8" size="8" style="display:none" id="colorSelectorBgp11_4" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p11.insertWord("p11")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!--文字-->
          <div class="ps2_frmae_p2"> 
            <div class="editorSprite">
              <textarea name="content" id="content" style="width:100%; height:300px;"></textarea>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p2.insertHtml("p2")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!---图片-->
          <div class="ps2_frmae_p3">
            <div class="pNotice">注：图片高度保持一致(宽：640px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div class="clean"></div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name">
                <input type="text" value="添加文字" onFocus="this.value=='添加文字'?this.value='':''" />
              </div>
              <div class="colorTitle">文字颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp3_0" class="txtColor" value="#ffffff" readonly />
              <div class="colorTitle" style="margin-left:20px;">文字背景颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp3_0" class="bgColor" value="#4C4C4C" readonly />
              <div class="clean"></div>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p3.insertWord("p3")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!---幻灯片-->
          <div class="ps2_frmae_p4">
            <div class="pNotice">注：图片高度保持一致(宽：640px，高：自定义)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p4_0" />
                </div>
                <div id="p4LookDetail0" class="lookDetail"></div>
              </div>
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p4_1" />
                </div>
                <div id="p4LookDetail1" class="lookDetail"></div>
              </div>
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p4_2" />
                </div>
                <div id="p4LookDetail2" class="lookDetail"></div>
              </div>
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p4_3" />
                </div>
                <div id="p4LookDetail3" class="lookDetail"></div>
              </div>
              <div class="clean"></div>
            </div>
            <div class="warp_packges">
              <div class="selectLink">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_p4_4" />
                </div>
                <div id="p4LookDetail4" class="lookDetail"></div>
              </div>
              <div class="clean"></div>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p4.insertWord("p4")'>保 存</div>
            <div class="clean"></div>
          </div>
          
          <!---电话拨号-->
          <div class="ps2_frmae_p5">
            <div class="pNotice">注：一行填写一个号码，如：<span class="fc_red">13800138000/陈生</span></div>
            <div class="clean"></div>
            <div class="phoneSprite">
              <textarea></textarea>
            </div>
            <div class="clean"></div>
            <div class="colorTitle">颜色：</div>
            <input type="text" maxlength="8" size="8" id="colorSelectorWordp5" class="txtColor" value="#ffffff" readonly />
            <div class="colorTitle" style="margin-left:20px;">背景：</div>
            <input type="text" maxlength="8" size="8" id="colorSelectorBgp5" class="bgColor" value="#4C4C4C" readonly />
            <div class="colorTitle" style="margin-left:20px;">大小：</div>
            <select class="fontSize_p5" style="vertical-align:-5px;">
              <option value="14">14px</option>
              <option value="16">16px</option>
              <option value="18">18px</option>
              <option value="20">20px</option>
              <option value="22">22px</option>
              <option value="24">24px</option>
            </select>
            <div class="clean"></div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p5.insertHtml("p5")'>保 存</div>
            <div class="clean"></div>
          </div>
          
		  <!--搜索框-->
          <div class="ps2_frmae_p12">
            <div class="pNotice">注：搜索按钮(宽：40px，高：30px)</div>
            <div class="clean"></div>
            <div class="warp_packges">
              <div class="selectLink" style="display:none">链接地址：
                <select name='Url'>
                  <?php UrlList(); ?>
                </select>
              </div>
              <div  class="wrap_upload">
                <div class="uploadBtn hand">
                  <input type="file" id="upfile_search" />
                </div>
              </div>
              <div class="clean"></div>
              <div class="img_name" style="display:none">
                <input type="text" value="添加文字" onFocus="this.value=='添加文字'?this.value='':''" />
              </div>
              <div class="colorTitle">区域背景颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorBgp12_0" class="bgColor" value="#" readonly />
              <div class="clean"></div>
              <div class="colorTitle" style="margin-left:20px;">搜索边框颜色：</div>
              <input type="text" maxlength="8" size="8" id="colorSelectorWordp12_0" class="txtColor" value="#" readonly />
              
              <div class="clean"></div>
            </div>
            <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p12.insertWord("p12")'>保 存</div>
            <div class="clean"></div>
          </div>
		    <!--分类-->
            <div class="ps2_frmae_p13">
                <div class="pNotice">注：图片高度保持一致(宽：320px，高：自定义)</div>
                <div class="clean"></div>
                <div class="selectLink"><label>产品默认列表</label>
                  <select id="category_p13">
                    <?PHP CategoryList($rsConfig['Makeup_Skin_ID']); ?>
                  </select>
                </div>
                 <div class="selectLink"><label>产品默认排序</label>
                  <select id="order_p13">
                    <option value="1">时间</option>
                    <option value="2">价格</option>
                    <option value="3">销量</option>
                  </select>
                </div>
               
                <div class="img_name num"><label>产品显示条数</label>
                    <input  type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
                </div>
                 <div class="selectLink"><label>产品列表样式
                  <select id='list_type_p13'>
                    <option value='1'>列表样式01</option>
                    <option value='2'>列表样式02</option>
                    <option value='3'>列表样式03</option>
                    <option value='4'>列表样式04</option>
                  </select>
                </div>
                 <div class="checkList"><label>产品额外属性</label>
                  <div>
                    <span><input type="checkbox" name="nature_p13" value="dis"><label>折扣</label></span>
                    <span><input type="checkbox" name="nature_p13" value="sale"><label>销量</label></span>
                    <span><input type="checkbox" name="nature_p13" value="integration"><label>积分</label></span>
                    <span><input type="checkbox" name="nature_p13" value="cart"><label>购物车</label></span>
                  </div>
                </div>
                <div class="selectLink"><label>产品标题栏样式</label>
                  <select id='type_p13'>
                    <option value='1'>标题样式01</option>
                    <option value='2'>标题样式02</option>
                    <option value='3'>标题样式03</option>
                    <option value='4'>标题样式04</option>
                  </select>
                </div>
                <div class="checkList"><label>产品标题栏更多显示</label>
                  <div>
                    <span><input type='radio' name='check_more' checked='checked' value='1'><label>显示更多</label></span>
                    <span><input type='radio' name='check_more' value='2'><label>不显示</label></span>
                  </div>
                </div>
                 <div class="img_name p_title"><label>产品标题栏标题文字</label>
                    <input  type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
                </div>
                <div class="img_name more"><label>产品标题栏按钮文字</label>
                    <input  type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
                </div>
                <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p13.insertWord("p13")'>保 存</div>
                <div class="clean"></div>
            </div>
            <!--属性-->
            <div class="ps2_frmae_p14">
                <div class="pNotice">注：图片高度保持一致(宽：320px，高：自定义)</div>
                <div class="clean"></div>
                 <div class="selectLink"><label>产品默认类型</label>
                  <select id="def_p14">
                    <option value="1">推荐</option>
                    <option value="2">热卖</option>
                    <option value="3">新品</option>
                  </select>
                </div>
                <div class="selectLink"><label>产品默认排序</label>
                  <select id="order_p14">
                    <option value="1">时间</option>
                    <option value="2">价格</option>
                    <option value="3">销量</option>
                  </select>
                </div>
                <div class="img_name num"><label>产品显示条数</label>
                    <input  value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
                </div>
                <div class="selectLink"><label>产品列表样式</label>
                  <select id='list_type_p14'>
                    <option value='1'>列表样式01</option>
                    <option value='2'>列表样式02</option>
                    <option value='3'>列表样式03</option>
                    <option value='4'>列表样式04</option>
                  </select>
                </div>
                <div class="checkList"><label>产品额外属性</label>
                  <div>
                    <span><input type="checkbox" name="nature_p14" value="dis"><label>折扣</label></span>
                    <span><input type="checkbox" name="nature_p14" value="sale"><label>销量</label></span>
                    <span><input type="checkbox" name="nature_p14" value="integration"><label>积分</label></span>
                    <span><input type="checkbox" name="nature_p14" value="cart"><label>购物车</label></span>
                  </div>
                </div>
                <div class="selectLink"><label>产品标题栏样式</label>
                  <select id='type_p14'>
                    <option value='1'>标题样式01</option>
                    <option value='2'>标题样式02</option>
                    <option value='3'>标题样式03</option>
                    <option value='4'>标题样式04</option>
                  </select>
                </div>
                <div class="checkList"><label>产品标题栏更多显示</label>
                 <div>
                    <span><input type='radio' name='check_more_p14' checked='checked' value='1'><label>显示更多</label></span>
                    <span><input type='radio' name='check_more_p14' value='2'><label>不显示</label></span>
                  </div>
                </div> 
                <div class="img_name p_title"><label>产品标题栏标题文字</label>
                    <input  type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
                </div>  
                <div class="img_name more"><label>产品标题栏按钮文字</label>
                    <input type="text" value="文字区域1" onFocus="this.value=='文字区域1'?this.value='':''" />
                </div>
                 <div class='btn_green btn_w_120 editBtn hand' onclick='shop_obj.p14.insertWord("p14")'>保 存</div>
                <div class="clean"></div>
            </div>
        </div>
      </div>
  		<!-- 属性end -->
 	 	<div class="clear"></div>
		</div>
	<div class="rows">
	  <label></label>
	  <span class="input">
	  <input type="button" class="btn_green btn_green_sun" value="提交保存" style="height: 30px;line-height: 30px;background-color:#3AA0EB;border: none;border-radius: 8px;color: #fff;width: 145px;font-size: 14px;" name="submit_btn">
	  <a href="skin.php" class="btn_gray" style="color:#000;">返回</a></span> 
	  <div class="clear"></div>
	</div>

</div>
</body>
</html>

<script>
	$(".btn_green_sun").click(function(){
		var skin_name = $("#skin_name").val();
		var Skin_ID = $("#Skin_ID").val();
		var ImgPath = $("#ImgPath").val();
		if(skin_name == ''){
			alert('请填写模板名称！');
			return false;
		}
		$.post("", { "template": "template", "skin_name":skin_name,"Skin_ID":Skin_ID},
		   function(data){
			   if(data.status == 1){
				   alert("保存成功!");
				   location.href="http://<?=$_SERVER['HTTP_HOST']?>/member/shop/pagemakeup/skin.php";
			   }
		   }, "json");
		});

</script>