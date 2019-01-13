<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/region.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/control/public/global.func.php');
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
if($_POST){
	$_POST["Account"] = trim($_POST["Account"]);
	if(count($_POST['Invitation_Code']) > 10){
        echo '<script language="javascript">alert("邀请码最多10位！");history.back();</script>';
        exit();
    }
	if(empty($_POST["Account"])){
		echo '<script language="javascript">alert("商家登录账号不能为空！");history.back();</script>';
		exit();
	}else{
		$r = $DB->GetRs("biz","*","where Biz_Account='".$_POST["Account"]."'");
		if($r){
			echo '<script language="javascript">alert("此登录账号已被注册，请更改！");history.back();</script>';
			exit();
		}
	}
	if(empty($_POST["PassWord"])){
		echo '<script language="javascript">alert("登录密码不能为空！");history.back();</script>';
		exit();
	}elseif($_POST["PassWord"]!=$_POST["PassWordA"]){
		echo '<script language="javascript">alert("登录密码与确认密码不相同！");history.back();</script>';
		exit();
	}
	
	$areaid = $regionid = 0;
	if(!empty($_POST["Area"])){
		$areaid = $_POST["Area"];
	}
	
	if($areaid==0){
		echo '<script language="javascript">alert("请选择商家所在城市县区！");history.back();</script>';
		exit();
	}
	
	if(empty($_POST['PrimaryLng']) || empty($_POST['PrimaryLat'])){
		echo '<script language="javascript">alert("定位有误重新添加！");history.back();</script>';
		exit();
	}	
	if(empty($_POST['GroupID'])){
		echo '<script language="javascript">alert("请选择商家所属分组！");history.back();</script>';
		exit();
	}
	if ($_POST["FinanceType"] == 0) {
        if (!is_numeric($_POST["FinanceRate"]) || $_POST["FinanceRate"] <= 0 || $_POST["FinanceRate"] > 100) {
            echo '<script language="javascript">alert("网站提成必须数字且大于零且不能超过100%！");history.back();</script>';
            exit();
        }
    } else {
        $_POST["FinanceRate"] = 0;
    }
		if ($_POST["FinanceType2"] == 0) {
        if (!is_numeric($_POST["BizProfit"]) || $_POST["BizProfit"] <= 0 || $_POST["BizProfit"] > 100) {
            echo '<script language="javascript">alert("线下网站提成必须数字且大于零且不能超过100%！");history.back();</script>';
            exit();
        } 
		} else {
			$_POST["BizProfit"] = 0;
		}		
		
    if(!empty($_POST["PaymenteRate"])){
		if(!is_numeric($_POST["PaymenteRate"]) || $_POST["PaymenteRate"]<=0){

			echo '<script language="javascript">alert("结算比例必须大于零！");history.back();</script>';
			exit();
		}
	}
	if (isset($_POST['bond_free'])) {
		if (!is_numeric($_POST['bond_free'])) {
			echo '<script language="javascript">alert("保证金必须是数字");history.back();</script>';
            exit();
		}
	}
	$rmenu = array();
	if(!isset($_POST["rmenu"])){
		echo '<script language="javascript">alert("请选择分类");history.back();</script>';
		exit;
	}
	foreach($_POST["rmenu"] as $k=>$vv){
		$rmenu[] = $k;
		foreach($vv as $v){
			$rmenu[] = $v;
		}
	}
	$rmenu = array_unique($rmenu);
	
	if(!empty($_POST["RegionID_0"])){
		$regionid = $_POST["RegionID_0"];
	}
	
	$_POST['Introduce'] = str_replace('"','&quot;',$_POST['Introduce']);
	$_POST['Introduce'] = str_replace("'","&quot;",$_POST['Introduce']);
	$_POST['Introduce'] = str_replace('>','&gt;',$_POST['Introduce']);
	$_POST['Introduce'] = str_replace('<','&lt;',$_POST['Introduce']);

    // 默认模版id
    $Skin_ID = 2;

	$Data=array(
		"Invitation_Code"=>isset($_POST['Invitation_Code'])?$_POST['Invitation_Code']:'',
		"Biz_contract"=>trim($_POST['contract']),
		"expiredate"=>empty($_POST['expiredate'])?'0':strtotime($_POST['expiredate']),
		"bond_free"=>empty($_POST['bond_free'])?'0':trim($_POST['bond_free']),
		"Finance_Rate"=>empty($_POST["FinanceRate"]) ? 0 : $_POST["FinanceRate"],
		"Finance_Type"=>$_POST["FinanceType"],
        "Biz_Profit"=>empty($_POST["BizProfit"]) ? 0 : $_POST["BizProfit"],
        "Finance_Type2"=>$_POST["FinanceType2"],
		"PaymenteRate"=>empty($_POST["PaymenteRate"]) ? 100 : $_POST["PaymenteRate"],
		"Biz_Account"=>$_POST['Account'],
		"Biz_PassWord"=>md5($_POST['PassWord']),
		"Biz_Name"=>$_POST['Name'],
		"Area_ID"=>$areaid,
		"Biz_Province"=>empty($_POST["Province"]) ? 0 : $_POST["Province"],
		"Biz_City"=>empty($_POST["City"]) ? 0 : $_POST["City"],
		"Biz_Area"=>empty($_POST["Area"]) ? 0 : $_POST["Area"],
		"Group_ID"=>$_POST['GroupID'],
		"City_ID"=>empty($_POST["City"])?0:$_POST["City"],
		"Region_ID"=>$regionid,
		"Biz_Address"=>$_POST['Address'],
		"Biz_PrimaryLng"=>$_POST['PrimaryLng'],
		"Biz_PrimaryLat"=>$_POST['PrimaryLat'],
		"Biz_Homepage"=>$_POST['Homepage'],
		"Biz_Introduce"=>$_POST['Introduce'],
		"Biz_Contact"=>$_POST['Contact'],
		"Biz_Phone"=>$_POST['Phone'],
		"Biz_Email"=>$_POST['Email'],
		"Biz_Status"=>$_POST['Status'],
		"Biz_CreateTime"=>time(),
		"Biz_SmsPhone"=>$_POST['SmsPhone'],
		"Is_Union"=>1,
		"Skin_ID"=> $Skin_ID,
		"is_agree"=>1,
		"is_auth"=>2,
		"is_pay"=>1,
		"is_biz"=>1,
		"addtype"=>1,
		"Users_ID"=>$_SESSION["Users_ID"],
		"Biz_Logo"=>$_POST['LogoPath'],
		/*edit in 20160331*/
		"Category_ID"=>','.implode(",",$rmenu).',',
		"Category_Arr"=>json_encode(isset($_POST['rmenu'])?$_POST['rmenu']:'',JSON_UNESCAPED_UNICODE),
		"Biz_IndexShow"=>$_POST["IndexShow"],
		"Biz_stmdShow"=>$_POST["stmdShow"],
		"Biz_Index"=>isset($_POST["Index"]) ? $_POST["Index"] : 0,
        'Is_BizStores'=>(int)$_POST['Is_BizStores']   // 是否支持到店自提
	);
	$Flag=$DB->Add("biz",$Data);
	$bizid = $DB->insert_id();
	/*edit in 20160330*/
	$biz_url = 'http://'.$_SERVER["HTTP_HOST"].'/api/'.$_SESSION["Users_ID"].'/shop/biz/'.$bizid.'/';
	$qrcode = array(
		"Biz_Qrcode"=>generate_qrcode($biz_url),
	);
	$condition = "where Users_ID = '".$_SESSION["Users_ID"]."' and Biz_ID=".$bizid;	
	$Flag = $DB->set("biz",$qrcode,$condition);
	$r = $DB->GetRs("biz_skin","Skin_Json","where Skin_ID=" . $Skin_ID);
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"Biz_ID"=>$bizid,
		"Skin_ID"=> $Skin_ID,
		"Home_Json"=>$r["Skin_Json"]
	);
	$DB->Add("biz_home",$Data);
	
	if($Flag){
		echo '<script language="javascript">alert("添加成功");window.location="union.php";</script>';
	}else{
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	}
	exit;
}else{
	$category_list = array();
	$DB->Get("biz_union_category","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Category_ParentID asc,Category_Index asc,Category_ID asc");
	while($r = $DB->fetch_assoc()){
		if($r["Category_ParentID"]==0){
			$category_list[$r["Category_ID"]] = $r;
		}else{
			$category_list[$r["Category_ParentID"]]["child"][] = $r;
		}
	}
	
	if(isset($_GET['ItemID'])){
		$res = $DB->getRs('biz_apply','*',' where ItemID='.$_GET['ItemID']);
	}
	//商家分组
	$groups = array();
	$res = $DB->Get("biz_group","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Group_index asc, Group_ID asc");
	$result = $DB->toArray($res);
	if(empty($result)){
	     echo '<script language="javascript">alert("商家分组没有添加，请添加商家分组！");location.href="group_add.php";</script>';
	     exit;

	}
	foreach ($result as $r){
		$groups[$r["Group_ID"]] = $r;
	}
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
<link href='/static/css/buttoncss.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script type='text/javascript' src="/static/js/select2.js"></script>
<script type="text/javascript" src="/static/js/location.js"></script>
<script type="text/javascript" src="/static/js/area.js"></script>
<link href="/static/css/select2.css" rel="stylesheet"/>
<script type='text/javascript' src='/static/member/js/shopbiz.js?t=1'></script>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?php echo $ak_baidu;?>"></script>
<script type='text/javascript' src='/static/js/plugin/My97DatePicker/WdatePicker.js'></script>
<script type="text/javascript">
$(document).ready(function(){
	showLocation(0,0,0);
	shopbiz_obj.biz_edit_init();
	global_obj.map_init();
});
KindEditor.ready(function(K) {
	K.create('textarea[name="Introduce"]', {
        themeType : 'simple',
		filterMode : false,
        uploadJson : '/member/upload_json.php?TableField=shop_biz&UsersID=<?php echo $_SESSION["Users_ID"];?>',
        fileManagerJson : '/member/file_manager_json.php',
        allowFileManager : true,
		items : [
			'source', '|', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
			'removeformat', 'undo', 'redo', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'image', 'link' , '|', 'preview']
    });
	var editor = K.editor({
		uploadJson : '/member/upload_json.php?TableField=shop_biz&UsersID=<?php echo $_SESSION["Users_ID"];?>',
		fileManagerJson : '/member/file_manager_json.php',
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
});

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
<style>
#loc_province, #loc_city, #loc_town{display:none;}
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/bizunion_menubar.php');?>
    <div id="bizs" class="r_con_wrap">
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
      <form class="r_con_form" method="post" action="?" id="biz_edit">
      <input type="hidden" name="ItemID" value="<?=isset($res)?$res['ItemID']:'';?>" class="form_input"/>
      	<div class="rows">
          <label>邀请码</label>
          <span class="input">
          <input type="text" name="Invitation_Code" value="<?=isset($res)?$res['Invitation_Code']:'';?>" class="form_input" size="35" maxlength="50" />
          <font class="fc_red"></font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>登录账号</label>
          <span class="input">
          <input type="text" name="Account" value="" class="form_input" size="35" maxlength="50" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>登录密码</label>
          <span class="input">
          <input type="password" name="PassWord" value="" class="form_input" size="35" maxlength="50" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>确认登录密码</label>
          <span class="input">
          <input type="password" name="PassWordA" value="" class="form_input" size="35" maxlength="50" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>合同编号</label>
          <span class="input">
          <input type="text" name="contract" value="" class="form_input" size="35" maxlength="50" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
		  <div class="rows">
          <label>所属分组</label>
          <span class="input">
          <select name="GroupID" notnull>
          <?php foreach($groups as $GroupID=>$v){?>
          <option value="<?php echo $GroupID;?>"><?php echo $v["Group_Name"];?></option>
          <?php }?>
          </select>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>到期时间</label>
         <span class="input">
          <input type="text" name="expiredate" value="<?php echo date('Y-m-d',time())?>" class="form_input" onfocus="WdatePicker({minDate:''})" size="35" maxlength="50" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>保证金</label>
         <span class="input">
          <input type="text" name="bond_free" value="" class="form_input" size="35" maxlength="50" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>商家名称</label>
          <span class="input">
          <input type="text" name="Name" value="" class="form_input" size="35" maxlength="50" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>       
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
                <?php foreach($category_list as $key=>$value){?>
				<div class="sitemone" id="<?=$key;?>"><li><input type="checkbox" name="rmenu[<?=$key;?>][]" id="checkitem" class="rotop" value="<?=$value["Category_ID"];?>" checked /><strong>&nbsp;<?=$value["Category_Name"]?></strong></li></div>		  
			<?php if(!empty($value["child"])){?>
			<div class="sitemtwo" id="b<?=$key?>">
			 <?php foreach($value["child"] as $k=>$v){?>				
				<li class="oitem"><input type="checkbox" name="rmenu[<?=$key;?>][]" id="checkitem" value="<?=$v["Category_ID"];?>" checked />&nbsp;<?=$v["Category_Name"];?></li>
              <?php } ?>
			 </div>
				<?php } }?>
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
            <select name="Area" id="loc_town" style="width:120px;" >
            </select> <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
        </div>
        <!--<div class="rows">
          <label>所在区域</label>
          <span class="input">
           <select name='RegionID_0' id="regionid_0" style="width:180px" notnull>
              <option value='0'>请选择</option>
           </select>
           <select name='RegionID_1' id="regionid_1" style="width:180px">
              <option value='0'>请选择</option>
           </select>
          </span>
          <div class="clear"></div>
        </div>-->
        <div class="rows">
          <label>详细地址</label>
          <span class="input">
          <input name="Address" id="Address" value="黄河路经八路" type="text" class="form_input" size="40" maxlength="100" notnull>
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
          <div class="img" id="LogoDetail" style="margin-top:8px"></div>
          </span> </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>接受短信手机</label>
          <span class="input">
          <input type="text" name="SmsPhone" value="" class="form_input" size="30" pattern="[0-9]*"/>
          <span class="tips">当用户下单时，系统会自动发短信到该手机</span>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>联系人</label>
          <span class="input">
          <input type="text" name="Contact" value="" class="form_input" size="35" notnull/>
          <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>联系电话</label>
          <span class="input">
          <input type="text" name="Phone" value="" class="form_input" size="35" notnull/>
          <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>电子邮箱</label>
          <span class="input">
          <input type="text" name="Email" value="" class="form_input" size="35"/>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>公司主页</label>
          <span class="input">
          <input type="text" name="Homepage" value="" class="form_input" size="35" />
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>商家简介</label>
          <span class="input">
          <textarea class="ckeditor" name="Introduce" style="width:600px; height:300px;"></textarea>
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>线上结算类型</label>
          <span class="input">
              <input type="radio" name="FinanceType" value="0" id="FinanceType_0" onClick="$('#FinanceRate').show();" checked /><label for="FinanceType_0"> 按交易额比例</label>&nbsp;&nbsp;<input type="radio" name="FinanceType" value="1" id="FinanceType_1" onClick="$('#FinanceRate').hide();" /><label for="FinanceType_1"> 具体产品设置</label><br />
          <span class="tips">注：若按交易额比例，则网站提成为：产品售价*比例%，网站提成包含网站应得和佣金发放两项</span>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows" id="FinanceRate">
          <label>线上网站提成</label>
          <span class="input">
          <input type="text" name="FinanceRate" value="0" class="form_input" size="10" /> %
          <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
        </div>
          <div class="rows">
              <label>线下结算类型</label>
          <span class="input">
              <input type="radio" name="FinanceType2" value="0" id="FinanceType_2" onClick="$('#BizProfit').show();" checked /><label for="FinanceType_2"> 按交易额比例</label>&nbsp;&nbsp;<input type="radio" name="FinanceType2" value="1" id="FinanceType_3" onClick="$('#BizProfit').hide();" /><label for="FinanceType_3"> 商家自身设置</label><br />
          <span class="tips">注：若按交易额比例，则网站提成为：产品售价*比例%，网站提成包含网站应得和佣金发放两项</span>
          </span>
              <div class="clear"></div>
          </div>
          <div class="rows" id="BizProfit">
              <label>线下网站提成</label>
          <span class="input">
          <input type="text" name="BizProfit" value="0" class="form_input" size="10" /> %
          <font class="fc_red">*</font>
          </span>
              <div class="clear"></div>
          </div>
       <div class="rows" id="FinanceRate">
          <label>结算比例</label>
          <span class="input">
          <input type="text" name="PaymenteRate" value="0" class="form_input" size="10" /> %
           <span class="tips">注：商家财务结算时,按照结算比例款项一部分转向商家指定的卡号,剩下的转入商家绑定的前台会员的余额中。</span>
           <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>在首页显示</label>
          <span class="input">
              <input type="radio" name="IndexShow" value="0" id="IndexShow_0" checked /><label for="IndexShow_0">不显示</label>&nbsp;&nbsp;
              <input type="radio" name="IndexShow" value="1" id="IndexShow_1" /><label for="IndexShow_1">显示</label>&nbsp;&nbsp;
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>在线买单</label>
          <span class="input">
              <input type="radio" name="stmdShow" value="0" id="stmdShow_0" checked /><label for="stmdShow_0">不显示</label>&nbsp;&nbsp;
              <input type="radio" name="stmdShow" value="1" id="stmdShow_1" /><label for="stmdShow_1">显示</label>&nbsp;&nbsp;
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>商家排序</label>
          <span class="input">
          <input type="text" name="Index" value="0" class="form_input" size="5" pattern="[0-9]*" />
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>状态</label>
          <span class="input">
              <input type="radio" name="Status" value="0" id="Status_0" checked /><label for="Status_0">正常</label>&nbsp;&nbsp;<input type="radio" name="Status" value="1" id="Status_1" /><label for="Status_1">禁用</label>
          </span>
          <div class="clear"></div>
        </div>
          <div class="rows">
              <label>是否开启到店自提</label>
              <span class="input">
                            <input type="radio" value="0" id="order_0" name="Is_BizStores" checked/><label for="order_0"> 关闭&nbsp;</label>
                            <input type="radio" value="1" id="order_1" name="Is_BizStores"/><label for="order_1"> 开启&nbsp;&nbsp; </label>
              </span>
              <div class="clear"></div>
          </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" name="submit_button" id="submit_button_green" value="提交保存" data-status="0" /></span>
          <div class="clear"></div>
        </div>
        <input type="hidden" name="PrimaryLng" value="">
        <input type="hidden" name="PrimaryLat" value="">
        <input type="hidden" id="LogoPath" name="LogoPath" value="" />
        <input type="hidden" id="usersid" value="<?php echo $_SESSION["Users_ID"];?>">
      </form>
    </div>
  </div>
</div>
</body>
</html>