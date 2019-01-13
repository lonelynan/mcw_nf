<?php
require_once('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');
if ($rsBiz['is_agree'] !=1 || $rsBiz['is_auth'] !=2) {
        echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.location="/biz/authen/index.php";</script>';
        exit;
    }
    if ($rsBiz['is_biz'] !=1) {
        echo '<script language="javascript">alert("您还没完成商家入驻流程不能进行产品管理,点击确定立即完成");window.location="/biz/authen/index.php";</script>';
        exit;
    }
    $bizInfo = $DB->getRs('biz','','where Biz_ID = '.$_SESSION["BIZ_ID"]);
    if (time() > $bizInfo['expiredate']) {
            echo '<script language="javascript">alert("您的商家已经到期,请及时续费");window.location="/biz/authen/charge_man.php";</script>';
            exit;
    }
$rsTypes =  $DB->get("shop_product_type","*","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and attrnum < 100 order by Type_Index asc");
$shop_product_type_list = $DB->toArray($rsTypes);

$Attr_Option_List = array("唯一属性");
$Input_List = array("手工录入","录入属性值(一行代表一个)","多行文本框");
$base_url = base_url();
if(isset($_GET['Attr_Name']) && isset($_GET['Type_ID'])){
	$rsname = $DB->GetRs("shop_attribute","Attr_Name","where Users_ID ='".$_SESSION["Users_ID"]."' and Biz_ID ='".$_SESSION["BIZ_ID"]."' and Attr_Name = '".$_GET['Attr_Name']."' and Type_ID = ".$_GET['Type_ID']);
	if(!empty($rsname)){
	if ($rsname['Attr_Name'] != $_GET['Attr_Name']) {
		$data = array('status'=>1);
	}else{
		$data = array('status'=>0);
	}	
	}else{
		$data = array('status'=>0);
	}	
	echo json_encode($data,JSON_UNESCAPED_UNICODE);
	exit;
}
$AttrID = empty($_REQUEST['Attr_ID'])?0:$_REQUEST['Attr_ID'];
if (!$AttrID) {
	echo '<script language="javascript">alert("非法操作！");window.location="shop_attr.php";</script>';
}
$rsAttr = $DB->GetRs("shop_attribute","*","where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and Attr_ID=".$AttrID);
if($_POST){
	$rsatt = $DB->Get("shop_attribute","Attr_Name","where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and Type_ID=".$_POST['Type_ID']);
	$rsattarr = $DB->toArray($rsatt);
	if (count($rsattarr) >= 4) {	
	echo '<script language="javascript">alert("属性已增加到最大！");window.location="shop_attr.php";</script>';exit;
	}
	$Attr_Valuesarr = explode("\r\n",$_POST['Attr_Values']);
	$ignorego = array('*', '.', '(', ')', '~', '/', '|', '<', '>');
	foreach($Attr_Valuesarr as $key=>$v){
		if (empty($v)) {
			echo '<script language="javascript">alert("属性值不能为空！");history.back();</script>';exit;
		}
		if ($v != '|') {
		if (!filterspecil($v, $ignorego)) {
			echo '<script language="javascript">alert("属性值填写错误！");history.back();</script>';exit;
		}
		}
        // 去除左右两边的空格(包含全角空格)
        $Attr_Valuesarr[$key] = trim(trim($v, '　'));
	}
	$Data = array(
	 	'Attr_Name'=> $_POST['Attr_Name'],        
		'Sort_Order' => $_POST['Sort_Order'],		
        'Attr_Values' => implode('\r\n', $Attr_Valuesarr),
        'Attr_Type' => 1
    );
	$condition = "where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID='".$_SESSION["BIZ_ID"]."' and Attr_ID=".$_POST['Attr_ID'];
	$Flag=$DB->Set("shop_attribute",$Data,$condition);	
	if($Flag)
	{
		echo '<script language="javascript">alert("更新成功");window.location="shop_attr.php";</script>';
	}else
	{
		echo '<script language="javascript">alert("保存失败");history.back();</script>';
	}
	exit;
}
$jcurid = 3;
$subidst = 31;
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
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/biz/js/shop.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/biz/products/bizshop_menubar.php');?>
    <div id="products" class="r_con_wrap"> 
	<script language="javascript">
		var base_url  =  '<?=$base_url?>';
		$(document).ready(shop_obj.products_attr_edit_init);
    </script>
      <div class="attr">
        <div class="m_righter" style="margin-left:0px;">
          <form action="shop_attr_edit.php" method="post" id="shop_attr_edit_form">
		  <input type="hidden" name="Attr_ID" value="<?=$rsAttr['Attr_ID']?>"/>		  
            <h1>修改产品属性</h1>
            <div class="opt_item">
              <label>属性名称：</label>
              <span class="input">
              <input type="text" name="Attr_Name" id="Attr_Name" value="<?=$rsAttr['Attr_Name']?>" class="form_input" size="15" maxlength="30" notnull />
              <font class="fc_red">*</font></span>
              <div class="clear"></div>
            </div>
            
            
            <div class="opt_item" id="product_type_html">
              <label>属性类型：</label>
              <span class="input">
              <select name="Type_ID" id="Type_ID" style="width:180px;" disabled="disabled" notnull>
               <option value="">关联类型</option>
               <?php foreach($shop_product_type_list as $key=>$product_type):?>
               	<option value="<?=$product_type['Type_ID']?>" <?=($product_type['Type_ID']  == $rsAttr['Type_ID'])?'selected':''?> ><?=$product_type['Type_Name']?></option>
               <?php endforeach;?>
              </select>
              </span>
              <div class="clear"></div>
            </div>
            
           <div class="opt_item">
              <label>属性排序：</label>
              <span class="input">
              <input type="text" name="Sort_Order" value="<?=$rsAttr['Sort_Order']?>" class="form_input" size="5" maxlength="30" notnull />
              <font class="fc_red">*</font>请输入数字</span>
              <div class="clear"></div>
            </div>
             
           <div class="opt_item" style="">
              <label>类型：</label>
              <div class="input">
              	<?php foreach($Attr_Option_List as $key=>$item):?>
                    	<input type="radio" name="Attr_Type" value="<?=$key?>" checked />&nbsp;&nbsp;<span><?=$item?></span>
				<?php endforeach; ?>              	
              </div>
              <div class="clear"></div>
            </div>
            
            
           <div class="opt_item">
            	<label>录入方式:</label>
            	<div class="input">
                  		<?php foreach($Input_List as $key=>$item):?>
                	
					<?php if($key  == 1):?>
                    	<input type="radio" name="Attr_Input_Type" class="Attr_Input_Type" value="<?=$key?>" checked />&nbsp;&nbsp;<span><?=$item?></span>&nbsp;&nbsp; 
				   <?php endif; ?>
					
				<?php endforeach; ?>
                </div>
            </div>
            
           <div class="opt_item">
          <label>可选值：</label>
          <span class="input">
          	<textarea name="Attr_Values" id="Attr_Values" notnull><?=$rsAttr['Attr_Values']?></textarea>
          </span>
          <br/>
         
          <div class="clear"></div>
          
        </div>
     
            <div class="opt_item">
              <label></label>
              <span class="input">
              <input type="button" class="btn_green btn_w_120" name="submit_button" id="submit_button" value="修改属性" /></span>
              <div class="clear"></div>
            </div>
          </form>
        </div>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
<script>
	$("#submit_button").click(function(){
		if(global_obj.check_form($('*[notnull]'))){return false};
		var Attr_Name = $("#Attr_Name").val();
		var Type_ID = $("#Type_ID").val();
		$.get("?", {Attr_Name: Attr_Name,Type_ID: Type_ID},
		    function(data){
				if(data.status == 1){
					alert('属性名称重名');
				}else{
					$("#Type_ID").removeAttr('disabled');
					$("#shop_attr_edit_form").submit();
				}
		    },"json");
		
	});
	
</script>