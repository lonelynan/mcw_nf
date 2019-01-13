<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/region.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
if(empty($_SESSION["BIZ_ID"])){
	header("location:/biz/login.php");
}

$rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);
if(!$rsBiz){
	echo '<script language="javascript">alert("此商家不存在！");history.back();</script>';
	exit();
}
	
if($rsBiz["Is_Union"]==0){
	header("location:/biz/account/account_edit.php");
}

if($_POST){
	$areaid = $regionid = 0;
	if(!empty($_POST["Area"])){
		$areaid = $_POST["Area"];
	}elseif(!empty($_POST["City"])){
		$areaid = $_POST["City"];
	}
	
	if($areaid==0){
		echo '<script language="javascript">alert("请选择商家所在城市！");history.back();</script>';
		exit();
	}
	
	$rmenu = array();
	if(!isset($_POST["rmenu"])){
		echo '<script language="javascript">alert("请选择分类");history.back();</script>';
		exit;
	}
	if (isset($_POST["Profit"])) {
	if (!is_numeric($_POST["Profit"]) || $_POST["Profit"] <= 0 || $_POST["Profit"] > 100) {
            echo '<script language="javascript">alert("线下网站提成必须数字且大于零且不能超过100%！");history.back();</script>';
            exit();
        }
		}
	foreach($_POST["rmenu"] as $k=>$vv){
		$rmenu[] = $k;
		foreach($vv as $v){
			$rmenu[] = $v;
		}
	}

	$rmenu = array_unique($rmenu);
	
	if(!empty($_POST["RegionID_1"])){
		$regionid = $_POST["RegionID_1"];
	}elseif(!empty($_POST["RegionID_0"])){
		$regionid = $_POST["RegionID_0"];
	}
	if (!filterspecil($_POST["Name"],['(',')','（','）'])) {
		echo '<script language="javascript">alert("请输入正确的商家名称");history.back();</script>';
		exit;	
	}
	$_POST['Introduce'] = str_replace('"','&quot;',$_POST['Introduce']);
	$_POST['Introduce'] = str_replace("'","&quot;",$_POST['Introduce']);
	$_POST['Introduce'] = str_replace('>','&gt;',$_POST['Introduce']);
	$_POST['Introduce'] = str_replace('<','&lt;',$_POST['Introduce']);
	$_POST['Notice'] = htmlspecialchars($_POST['Notice'], ENT_QUOTES);
	$_POST['Kfcode'] = htmlspecialchars($_POST['Kfcode'], ENT_QUOTES);
	$Data=array(
		"Biz_Name"=>empty($_POST["Name"]) ? '' : $_POST["Name"],
		"Area_ID"=>$areaid,
		"City_ID"=>empty($_POST["City"])?0:$_POST["City"],		
		"Region_ID"=>$regionid,
		"Biz_Address"=>$_POST['Address'],
		"Biz_PrimaryLng"=>$_POST['PrimaryLng'],
		"Biz_PrimaryLat"=>$_POST['PrimaryLat'],
		"Biz_Introduce"=>$_POST['Introduce'],
		"Biz_Notice"=>$_POST['Notice'],
		"Biz_Contact"=>$_POST['Contact'],
		"Biz_Phone"=>$_POST['Phone'],
		"Biz_SmsPhone"=>isset($_POST['SmsPhone']) ? $_POST['SmsPhone'] : '',
		"Biz_Email"=>$_POST['Email'],
		"Biz_Homepage"=>$_POST['Homepage'],
		"Biz_Logo"=>$_POST['LogoPath'],
		"Biz_Kfcode"=>$_POST['Kfcode'],
		"Biz_KfProductCode"=>$_POST['Biz_KfProductCode'],
		"QrcodeSet"=> intval($_POST['QrcodeSet']),
		/*edit in 20160331*/
		"Category_ID"=>','.implode(",",$rmenu).',',
		"Category_Arr"=>json_encode(isset($_POST['rmenu'])?$_POST['rmenu']:'',JSON_UNESCAPED_UNICODE),
        'Is_BizStores'=>(int)$_POST['Is_BizStores']   // 是否支持到店自提
	);
	if($_POST['FinanceType2'] == 1 && isset($_POST["Profit"])){
		$Data['Biz_Profit'] = $_POST['Profit'];
	}
	$Flag=$DB->Set("biz",$Data,"where Biz_ID=".$_SESSION["BIZ_ID"]);
	if($Flag){
		echo '<script language="javascript">alert("修改成功");window.location="account_union.php";</script>';
	}else{
		echo '<script language="javascript">alert("修改失败");history.back();</script>';
	}
	exit;
}else{	
	$category_list = array();
	$DB->Get("biz_union_category","*","where Users_ID='".$rsBiz["Users_ID"]."' order by Category_ParentID asc,Category_Index asc,Category_ID asc");
	while($r = $DB->fetch_assoc()){
		if($r["Category_ParentID"]==0){
			$category_list[$r["Category_ID"]] = $r;
		}else{
			$category_list[$r["Category_ParentID"]]["child"][] = $r;
		}
	}
	
	$region = new region($DB, $rsBiz["Users_ID"]);
	$areaids = $region->get_areaparent($rsBiz["Area_ID"]);
	$regionids = $region->get_regionids($rsBiz["Region_ID"]);
	
	$category_list = array();
	$DB->Get("biz_union_category","*","where Users_ID='".$rsBiz["Users_ID"]."' order by Category_ParentID asc,Category_Index asc,Category_ID asc");
	while($r = $DB->fetch_assoc()){
		if($r["Category_ParentID"]==0){
			$category_list[$r["Category_ID"]] = $r;
		}else{
			$category_list[$r["Category_ParentID"]]["child"][] = $r;
		}
	}
}

$cate_gory = $DB->GetRs("biz","*","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID='".$_SESSION["BIZ_ID"]."'");
if(!empty($cate_gory['Category_Arr'])){
$category_arr = json_decode($cate_gory['Category_Arr'],true);
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
<link href='/static/css/buttoncss.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script type='text/javascript' src="/static/js/select2.js"></script>
<script type="text/javascript" src="/static/js/location.js"></script>
<script type="text/javascript" src="/static/js/area.js"></script>
<link href="/static/css/select2.css" rel="stylesheet"/>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $ak_baidu;?>"></script>
<link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/biz/js/shop.js?t=4'></script>
<script>
$(document).ready(function(){
	showLocation(<?php echo empty($areaids["province"]) ? 0 : $areaids["province"];?>,<?php echo empty($areaids["city"]) ? 0 : $areaids["city"];?>,<?php echo empty($areaids["area"]) ? 0 : $areaids["area"];?>);
	shop_obj.biz_edit_init();
	global_obj.map_init();
});

KindEditor.ready(function(K) {
	K.create('textarea[name="Introduce"],textarea[name="Notice"]', {
        themeType : 'simple',
		filterMode : false,
        uploadJson : '/biz/upload_json.php?TableField=shop_biz&BIZ_ID=<?php echo $_SESSION["BIZ_ID"]?>',
		fileManagerJson : '/biz/file_manager_json.php',
        allowFileManager : true,
		items : [
			'source', '|', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
			'removeformat', 'undo', 'redo', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link' , '|', 'preview']
    });
	var editor = K.editor({
		uploadJson : '/biz/upload_json.php?TableField=shop_biz&BIZ_ID=<?php echo $_SESSION["BIZ_ID"]?>',
		fileManagerJson : '/biz/file_manager_json.php',
		showRemote : true,
		allowFileManager : true,
	});
	K('#LogoUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#LogoPath').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#LogoPath').val(url);
					K('#LogoDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
})

/*edit in 20160331*/
$(function () {
            // 全选
            $("#btnCheckAll").bind("click", function () {
                $("[id = checkitem]:checkbox").attr("checked", true);
            });
 
            // 全不选
            $("#btnChecknone").bind("click", function () {
                $("[id = checkitem]:checkbox").attr("checked", false);
            });
 
            // 反选
            $("#btnCheckflase").bind("click", function () {
                $("[id = checkitem]:checkbox").each(function () {
                    $(this).attr("checked", !$(this).attr("checked"));
                });
            });
			// 分类选
            $(".rotop").bind("click", function () {
				var catg = $(this).parent().parent().attr("id");				
				if($(this).attr("checked")){
					$("#b"+catg+" input[type=checkbox]:checkbox").attr("checked", true);
				}else{
					$("#b"+catg+" input[type=checkbox]:checkbox").attr("checked", false);
				}
            });
			//权限列表
   $(".sctrl").toggle(function(){
     $(".role").animate({height: 'toggle', opacity: 'toggle'}, "slow");
   },function(){
$(".role").animate({height: 'toggle', opacity: 'toggle'}, "slow");
   });
        });
</script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <div class="r_nav">
      <ul>
        <li><a href="account<?php echo $rsBiz["Is_Union"] ? '_union' : '';?>.php">商家资料</a></li>
        <li class="cur"><a href="account_edit<?php echo $rsBiz["Is_Union"] ? '_union' : '';?>.php">修改资料</a></li>
		<li><a href="address_edit.php">收货地址</a></li>
        <li><a href="account_password.php">修改密码</a></li>
      </ul>
    </div>
    <div id="bizs" class="r_con_wrap">
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
      <form class="r_con_form" method="post" action="?" id="biz_edit">
        <div class="rows">
          <label>商家名称</label>
          <span class="input">
          <input type="text" name="Name" value="<?php echo $rsBiz["Biz_Name"];?>" class="form_input" size="35" maxlength="50" notnull/>
          </span>
          <div class="clear"></div>
        </div>
        <!--edit in 20160331-->
		<div class="rows">
                <label>隶属分类</label>
				<span class="input">
				<div class="sctrl">点击选择分类</div>
				<ul class="role" style="display:none">				
				<div class="sbtnp">				
				<input id="btnCheckAll" type="button" class="button gray" value="全选" />
				<input id="btnChecknone" type="button" class="button gray" value="不选" />
				<input id="btnCheckflase" type="button" class="button gray" value="返选" />
				</div>
				<?php if(!empty($category_list)){
					foreach($category_list as $key=>$value){
					if(isset($category_arr)){
				if (array_key_exists($key,$category_arr)){
				$checked = 'checked';
				}else{
					$checked = '';
					}}else{
						$checked = '';
					} ?>
				<div class="sitemone" id="<?=$key;?>"><li><input type="checkbox" name="rmenu[<?=$key;?>][]" id="checkitem" class="rotop" value="<?=$value["Category_ID"];?>" <?=$checked?> /><strong>&nbsp;<?=$value["Category_Name"]?></strong></li></div>		  
			<?php if(!empty($value["child"])){?>
			<div class="sitemtwo" id="b<?=$key?>">			
			 <?php foreach($value["child"] as $k=>$v){
				 if(isset($category_arr)){
				if (isset($category_arr[$key])){
				if (in_array($v["Category_ID"],$category_arr[$key])){
				$checked = 'checked';
				}else{
					$checked = '';
				 }				 
				}
				 }else{
					 $checked = '';
				 }				
				?>
				<li class="oitem"><input type="checkbox" name="rmenu[<?=$key;?>][]" id="checkitem" value="<?=$v["Category_ID"];?>" <?=$checked?> />&nbsp;<?=$v["Category_Name"];?></li>
              <?php } ?>
			 </div>
				<?php } }}?>
			 </ul>
			 </span>
                <div class="clear"></div>
            </div>
        <div class="rows">
          <label>所在地区</label>
          <span class="input">
          	<select name="Province" id="loc_province" style="width:120px" notnull>
            </select>
            <select name="City" id="loc_city" style="width:120px" notnull>
            </select>
            <select name="Area" id="loc_town" style="width:120px">
            </select> <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
        </div>

       <!-- <div class="rows">
          <label>所在区域</label>
          <span class="input">
           <select name='RegionID_0' id="regionid_0" style="width:180px" notnull>
              <option value='0'>请选择</option>
              <?php
/*              $DB->Get("area_region","Region_ID,Region_Name","where Users_ID='".$rsBiz["Users_ID"]."' and Area_ID=".(empty($areaids["city"]) ? 0 : $areaids["city"])." and Region_ParentID=0 order by Region_Index asc, Region_ID asc");
			  while($r = $DB->fetch_assoc()){
				  $selectid = $regionids[1]==0 ? $regionids[0] : $regionids[1];
				  echo '<option value="'.$r["Region_ID"].'"'.($selectid==$r["Region_ID"] ? " selected" : "").'>'.$r["Region_Name"].'</option>';
			  }
			  */?>
           </select>
          <!-- <select name='RegionID_1' id="regionid_1" style="width:180px">
              <option value='0'>请选择</option>
              <?php
/*              if($regionids[1]>0){
				   $DB->Get("area_region","Region_ID,Region_Name","where Users_ID='".$rsBiz["Users_ID"]."' and Area_ID=".(empty($areaids["city"]) ? 0 : $areaids["city"])." and Region_ParentID=".$regionids[1]." order by Region_Index asc, Region_ID asc");
				  while($r = $DB->fetch_assoc()){
					  $selectid = $regionids[0];
					  echo '<option value="'.$r["Region_ID"].'"'.($selectid==$r["Region_ID"] ? " selected" : "").'>'.$r["Region_Name"].'</option>';
				  }
			  }
			  */?>
           </select>
          </span>
          <div class="clear"></div>
        </div>-->

        <div class="rows">
          <label>详细地址</label>
          <span class="input">
          <input name="Address" id="Address" value="<?php echo $rsBiz["Biz_Address"];?>" type="text" class="form_input" size="40" maxlength="100" notnull>
          <span class="primary" id="Primary">定位</span> <font class="fc_red">*</font><br />
          <div class="tips">如果输入地址后点击定位按钮无法定位，请在地图上直接点击选择地点</div>
          <div id="map"></div>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>商家形象图</label>
          <span class="input"> <span class="upload_file">
          <div>
            <div class="up_input">
              <input type="button" id="LogoUpload" value="添加图片" style="width:80px;" />
            </div>
            <div class="tips">图片建议尺寸：600*600</div>
            <div class="clear"></div>
          </div>
          <div class="img" id="LogoDetail" style="margin-top:8px">
          <?php 
		  if($rsBiz["Biz_Logo"]){
			  echo '<img src="'.$rsBiz["Biz_Logo"].'" />';
          }
		  ?>
          </div>
          </span> </span>
          <div class="clear"></div>
        </div>
		<?php if (!empty($rsBiz["Biz_SmsPhone"])) {?>
        <div class="rows">
          <label>接受短信手机</label>
          <span class="input">
          <input type="text" name="SmsPhone" value="<?php echo $rsBiz["Biz_SmsPhone"];?>" class="form_input" size="30" pattern="[0-9]*" notnull/>
          <font class="fc_red">*</font> <span class="tips">当用户下单时，系统会自动发短信到该手机</span>
          </span>
          <div class="clear"></div>
        </div>
		<?php } ?>
        <div class="rows">
          <label>联系人</label>
          <span class="input">
          <input type="text" name="Contact" value="<?php echo $rsBiz["Biz_Contact"];?>" class="form_input" size="35" notnull/>
          <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>联系电话</label>
          <span class="input">
          <input type="text" name="Phone" value="<?php echo $rsBiz["Biz_Phone"];?>" class="form_input" size="35" notnull/>
          <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>电子邮箱</label>
          <span class="input">
          <input type="text" name="Email" value="<?php echo $rsBiz["Biz_Email"];?>" class="form_input" size="35"/>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>公司主页</label>
          <span class="input">
          <input type="text" name="Homepage" value="<?php echo $rsBiz["Biz_Homepage"];?>" class="form_input" size="35" />
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>店铺客服代码</label>
          <span class="input">
          <textarea name="Kfcode" class="briefdesc"><?php echo $rsBiz["Biz_Kfcode"];?></textarea>
          </span>
          <div class="clear"></div>
        </div>
		
		<div class="rows">
          <label>产品详情客服代码</label>
          <span class="input">
          <textarea name="Biz_KfProductCode" class="briefdesc"><?php echo $rsBiz["Biz_KfProductCode"];?></textarea>
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>购物须知</label>
          <span class="input">
          <textarea class="ckeditor" name="Notice" style="width:600px; height:200px;"><?php echo $rsBiz["Biz_Notice"];?></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>商家介绍</label>
          <span class="input">
          <textarea class="ckeditor" name="Introduce" style="width:600px; height:300px;"><?php echo $rsBiz["Biz_Introduce"];?></textarea>
          </span>
          <div class="clear"></div>
        </div>
			  <div class="rows">
				  <label>线下消费利润率</label>
          <span class="input">
          <input type="text" name="Profit" value="<?php echo $rsBiz["Biz_Profit"];?>" class="form_input" size="5" />%&nbsp;&nbsp;
			  <input type="hidden" name="FinanceType2" value="<?=$rsBiz["Finance_Type2"]?>">
          </span>
				  <div class="clear"></div>
			  </div>
			  
			  <div class="rows">
				  <label>收款二维码坐标</label>
          <span class="input">
          <input type="text" name="QrcodeSet" value="<?php echo $rsBiz["QrcodeSet"];?>" class="form_input" size="5" />
          </span>
				  <div class="clear"></div>
			  </div>
              <div class="rows">
                  <label>是否开启到店自提</label>
                  <span class="input">
                            <input type="radio" value="0" id="order_0" name="Is_BizStores" <?=$rsBiz['Is_BizStores'] == 0 ? 'checked' : '' ?>/><label for="order_0"> 关闭&nbsp;</label>
                            <input type="radio" value="1" id="order_1" name="Is_BizStores" <?=$rsBiz['Is_BizStores'] == 1 ? 'checked' : '' ?>/><label for="order_1"> 开启&nbsp;&nbsp; </label>
                        </span>
                  <div class="clear"></div>
              </div>

        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" name="submit_button" value="提交保存" /></span>
          <div class="clear"></div>
        </div>
        <input type="hidden" name="PrimaryLng" value="<?php echo $rsBiz["Biz_PrimaryLng"]?>">
        <input type="hidden" name="PrimaryLat" value="<?php echo $rsBiz["Biz_PrimaryLat"]?>">
        <input type="hidden" id="LogoPath" name="LogoPath" value="<?php echo $rsBiz["Biz_Logo"];?>" />
        <input type="hidden" id="usersid" value="<?php echo $rsBiz["Users_ID"];?>" />
      </form>
    </div>
  </div>
</div>
</body>
</html>