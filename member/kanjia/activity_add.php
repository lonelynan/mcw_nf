<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/General_tree.php');

if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$base_url = base_url();
$UsersID = $_SESSION['Users_ID'];

//获取分类列表
$DB->get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID=0 order by Category_Index asc");
$ParentCategory=array();
$i=1;
while($rsPCategory=$DB->fetch_assoc()){
  $ParentCategory[$i]=$rsPCategory;
  $i++;
}

$category_list = array(); 
foreach($ParentCategory as $key=>$value){
  $DB->get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' and Category_ParentID=".$value["Category_ID"]." order by Category_Index asc");
  if($DB->num_rows()>0){
    $category_list[$value["Category_ID"]]['name'] = $value["Category_Name"];
    while($rsCategory=$DB->fetch_assoc()){
      $category_list[$value["Category_ID"]]['children'][$rsCategory['Category_ID']] = $rsCategory['Category_Name'];
  }
  }else{
      $category_list[$value["Category_ID"]]['name'] = $value["Category_Name"];
	   $category_list[$value["Category_ID"]]['children'] = array();
  }
}


	
if($_POST)
{
	
	$Fromtime = strtotime($_POST['AccTime_S']);
	$Totime = strtotime($_POST['AccTime_E']);
	
	$Data=array(
		"Kanjia_Name"=>$_POST['Name'],
		"Users_ID"=>$UsersID,
		"Product_Name"=>$_POST['Products_Name'],
		"Product_ID"=>$_POST['Products_ID'],
		"Beginnum"=>$_POST['begin_num'],
		"Endnum"=>$_POST['end_num'],
		"Member_Count"=>0,
		"Bottom_Price"=>$_POST['Bottom_Price'],
		"Fromtime"=>$Fromtime,
		"Totime"=>$Totime,
		"Kanjia_Createtime"=>time(),
		"is_recommend"=>isset($_POST["is_recommend"])?$_POST["is_recommend"]:0,
		"Weight"=>empty($_POST["Weight"]) ? 0 : floatval($_POST["Weight"]),
		"Shipping"=>$_POST["Shipping"]
	);
	
	$Flag=$DB->Add("kanjia",$Data);
	if($Flag)
	{
		echo '<script language="javascript">alert("添加成功");window.location="activity_list.php";</script>';
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
<title>微易宝</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type='text/javascript' src='/static/js/jquery.datetimepicker.js'></script>
<link href='/static/css/jquery.datetimepicker.css' rel='stylesheet' type='text/css' />
<script>
var base_url = "<?=$base_url?>";

</script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/kanjia.js?t=123'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/pintuan/marketing_menubar.php');?>
    <div id="products" class="r_con_wrap">    
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
      <form class="r_con_form" id="add_form" method="post" action="activity_add.php">
        <div class="rows">
          <label>活动名称</label>
          <span class="input">
          <input type="text" name="Name" value="" class="form_input" size="35" maxlength="100" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>选择产品</label>
          <span class="input">
          <select id="Category" >
            <option value=''>--请选择--</option>
            <?php foreach($category_list as $key=>$item):?>
              <option value="<?=$key?>"><?=$item['name']?></option>
               <?php if(count($item['children'])>0):?>
                  <?php foreach($item['children'] as $category_id=>$item):?>
                    <option value="<?=$category_id?>">&nbsp;&nbsp;&nbsp;&nbsp;<?=$item?></option>
                  <?php endforeach;?>
               <?php endif;?>
            <?php endforeach;?>
          </select>
          <input type="text"  id="keyword" placeholder="关键字" value="" class="form_input" size="35" maxlength="30" />
          <button type="button" id="search">搜索</button>
          
        
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>可选商品</label>
        
          <select size='10'  id="select_product" style="width:500px">
            
          </select>
        
 
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>活动商品</label>
          <span class="input">
          <input type="hidden" name="Products_ID" value="" id="Products_ID" />
          <input type="hidden" name="Products_Price" value="0"  id="Products_Price"/>
          <input type="text" name="Products_Name" value="" id="Products_Name" class="form_input" size="35" maxlength="100" notnull />
          <span id="Products_Price_Txt"></span>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
         <div class="rows">
       	 <label>底价</label>
         <span class="input">
         	<input type="text" name="Bottom_Price" id="Bottom_Price" value="" size="10" class="form_input"  notnull />
         <font class="fc_red">*</font>
        </span>
         <div class="clear"></div>
       </div>
       
         <div class="rows">
          <label>随机砍价数区间</label>
          <span class="input">
        	<input type="text" name="begin_num" id="begin_num" value="" size="4" notnull />-<input type="text" id="end_num" name="end_num" value="" size="4" notnull/>
        	
		  <span>必须为正整数,且始价和终价不能相等</span>
          <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
       </div>
        
        <div class="rows">
          <label>产品重量</label>
          <span class="input">
			<input name="Weight" type="text" value="" class="form_input" size="10" /> kg
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>需要物流</label>
          <span class="input">
            <select name="Shipping">
				<option value="1">是</option>
				<option value="0">否</option>
			</select>
          </span>
          <div class="clear"></div>
        </div>
      
      <div class="rows">
          <label>其他属性</label>
           <span class="input">
           推荐:
          <input type="checkbox" value="1" name="is_recommend"<?php echo empty($activity["is_recommend"])?"":" checked" ?> />
          </span>
          <div class="clear"></div>
        </div>
         
      <div class="rows">
          <label>活动时间</label>
          
        <input type="text" name="AccTime_S" value="" maxlength="20" notnull />-<input type="text" name="AccTime_E" value="" maxlength="20" notnull/>
        
       
          <font class="fc_red">*</font>
          <div class="clear"></div>
       </div>
        
        
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" name="submit_button" value="提交保存" />
          <a href="" class="btn_gray">返回</a></span>
          <div class="clear"></div>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>