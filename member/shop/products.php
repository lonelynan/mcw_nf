<?php  
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');

if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}	
if ($_POST) {
	$shop_config = shop_config($_SESSION["Users_ID"]);	
	if (empty($_POST['Products_ID'])) {
		 $res = array(
			"status"=>0,
			"info"=>"请选择要审核的产品！"
	);
		echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
	}
	
	$productids = implode(',',$_POST['Products_ID']);
	if (!is_null($shop_config['Shop_Commision_Reward_Json'])) 
	{
	$Shop_Commision_Reward_Arr = json_decode($shop_config['Shop_Commision_Reward_Json'], true);
	$resupdate = array(
			"platForm_Income_Reward"=>isset($Shop_Commision_Reward_Arr['platForm_Income_Reward']) ? $Shop_Commision_Reward_Arr['platForm_Income_Reward'] : 0,
			"commission_ratio"=>isset($Shop_Commision_Reward_Arr['commission_Reward']) ? $Shop_Commision_Reward_Arr['commission_Reward'] : 0,
			"salesman_ratio"=>isset($Shop_Commision_Reward_Arr['salesman_ratio']) ? $Shop_Commision_Reward_Arr['salesman_ratio'] : 0,
			"nobi_ratio"=>isset($Shop_Commision_Reward_Arr['noBi_Reward']) ? $Shop_Commision_Reward_Arr['noBi_Reward'] : 0,
			"area_Proxy_Reward"=>isset($Shop_Commision_Reward_Arr['area_Proxy_Reward']) ? $Shop_Commision_Reward_Arr['area_Proxy_Reward'] : 0,
			"sha_Reward"=>isset($Shop_Commision_Reward_Arr['sha_Reward']) ? $Shop_Commision_Reward_Arr['sha_Reward'] : 0,
			"Products_Distributes"=>isset($Shop_Commision_Reward_Arr['Distribute']) ? json_encode($Shop_Commision_Reward_Arr['Distribute'],JSON_UNESCAPED_UNICODE) : '',
			"salesman_level_ratio"=>isset($Shop_Commision_Reward_Arr['salesman_level_ratio']) ? json_encode($Shop_Commision_Reward_Arr['salesman_level_ratio'],JSON_UNESCAPED_UNICODE) : '',
			"Products_Status"=>1
	);
	$Flag = $DB->Set("shop_Products",$resupdate,"where Users_ID='".$_SESSION["Users_ID"]."' and Products_ID in($productids)");
	if ($Flag) {
		$res = array(
			"status"=>1,
			"info"=>"批量审核成功！"
	);
		echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
	} else {
		$res = array(
			"status"=>0,
			"info"=>"批量审核失败！"
	);
		echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
	}
	}else{
		$res = array(
			"status"=>0,
			"info"=>"没设置佣金无法批量审核！"
		);
		echo json_encode($res,JSON_UNESCAPED_UNICODE);
		exit;
	}
}
$_GET = daddslashes($_GET,1);
$condition = "where Users_ID='".$_SESSION["Users_ID"]."'";
if(isset($_GET['search'])){
	if($_GET['Keyword']){
		$condition .= " and Products_Name like '%".$_GET['Keyword']."%'";
	}
	if (!empty($_GET['SearchCateId'])) {
		$serchcateidarr = explode("|", $_GET["SearchCateId"]);
		if ($serchcateidarr[0] == 'top') {
			$serchcateid = $serchcateidarr[1];
			$catetype = 'Father_Category';
		} else {
			$serchcateid = $_GET['SearchCateId'];
			$catetype = 'Products_Category';
		}
		if($serchcateid>0){
		$condition .= " and ".$catetype."=".$serchcateid;
	}
	}
	
	if($_GET['Status']>0){
	$condition .= " and Products_Status=".($_GET["Status"]-1);
}
if($_GET['BizID']>0){
	$condition .= " and Biz_ID=".$_GET['BizID'];
}
	if($_GET["Attr"]){
		$condition .= " and Products_".$_GET["Attr"]."=1";
	}
}
$condition .= " order by Products_ID desc";
$biz = $shop_cate = array();
$DB->get("biz","Biz_ID,Biz_Name","where Users_ID='".$_SESSION["Users_ID"]."'");
while($value = $DB->fetch_assoc()){
	$biz[$value["Biz_ID"]] = $value;
}
	$DB->get("shop_category","Category_ParentID,Category_ID,Category_Name","where Users_ID='".$_SESSION["Users_ID"]."' order by Category_ParentID asc,Category_Index asc");
	$shop_cate = array();
	while($r=$DB->fetch_assoc()){
		if($r["Category_ParentID"]==0){
			$shop_cate[$r["Category_ID"]] = $r;
		}else{
			$shop_cate[$r["Category_ParentID"]]["child"][] = $r;
		}
	}
$jcurid = 3;
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
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/shop/product_menubar.php');?>
    <div id="products" class="r_con_wrap"> 
      <script language="javascript">$(document).ready(shop_obj.products_list_init);</script>
      <div class="control_btn">
      <a href="#search" class="btn_green btn_w_120">产品搜索</a> 
      <!--<a href="output.php?type=product_gross_info&action=outproduct" class="btn_green btn_w_120">导出产品</a>-->
	  <a href="javascript:void(0);" class="btn_green btn_w_120" id="auditingAll">批量审核</a>
      </div>
      <form class="search" method="get" action="products.php">
        商品名称：
        <input type="text" name="Keyword" value="" class="form_input" size="15" />&nbsp;
        产品分类：
        <select name='SearchCateId'>
          <option value=''>--请选择--</option>
          <?php
          foreach($shop_cate as $key=>$value){
			  $checked = (!empty($_GET['SearchCateId'])&&($_GET['SearchCateId'] == $value['Category_ID'])) ? 'selected=true' : '';
			  echo '<option value=top|"'.$value["Category_ID"].'" '.$checked.'>'.$value["Category_Name"].'</option>';
			  if(!empty($value["child"])){
				  foreach($value["child"] as $v){
					  echo '<option value="'.$v["Category_ID"].'">└'.$v["Category_Name"].'</option>';
				  }
			  }
		  }
		  ?>
        </select>&nbsp;
		商家：
        <select name='BizID'>
          <option value='0'>--请选择--</option>
          <?php
			foreach($biz as $key=>$value){
				$checked = (!empty($_GET['BizID'])&&($_GET['BizID'] == $value['Biz_ID'])) ? 'selected=true' : '';
		  		echo '<option value="'.$value["Biz_ID"].'" '.$checked.'>'.$value["Biz_Name"].'</option>';
		  	}
		  ?>
        </select>&nbsp;
        其他属性：
        <select name="Attr">
          <option value="0">--请选择--</option>
          <option value="SoldOut">下架</option>
          <option value="IsNew">新品</option>
          <option value="IsHot">热卖</option>
        </select>&nbsp;
		状态：
        <select name="Status">
          <option value="0">全部</option>
          <option value="1">未审核</option>
          <option value="2">已审核</option>
        </select>
		<input type="hidden" name="search" value="1" />
        <input type="submit" class="search_btn" value="搜索" />
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
			<td width="6%" nowrap="nowrap"><input type="checkbox" onclick="selectAll(this);"/>全选</td>
            <td width="5%" nowrap="nowrap">序号</td>
            <td nowrap="nowrap">名称</td>
			<td width="8%" nowrap="nowrap">所属商家</td>
			<td width="10%" nowrap="nowrap">结算明细</td>
            <td width="5%" nowrap="nowrap">佣金比例（库存）</td>
            <td width="5%" nowrap="nowrap">佣金/爵位比</td>
            <td width="5%" nowrap="nowrap">价格</td>
            <td width="8%" nowrap="nowrap">图片</td>
            <td width="8%" nowrap="nowrap">二维码</td>
            <td width="8%" nowrap="nowrap">其他属性</td>
			<td width="5%" nowrap="nowrap">状态</td>
            <td width="5%" nowrap="nowrap">时间</td>
            <td width="5%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
          <?php 
		  $lists = array();
		  $DB->getPage("shop_products","*",$condition,10);
		  
		  while($r=$DB->fetch_assoc()){
			  $lists[] = $r;
		  }
		  foreach($lists as $k=>$rsProducts){
			  $JSON=json_decode($rsProducts['Products_JSON'],true);
			  $rsBiz = $DB->GetRs("biz","Finance_Type,Finance_Rate","where Biz_ID=".$rsProducts["Biz_ID"]);
			  ?>
              
          <tr>
		  <td><input type="checkbox" class="auditingid" name="Products_ID[]" value="<?php echo $rsProducts["Products_ID"] ?>" /></td>
            <td nowrap="nowrap"><?php echo $rsProducts["Products_ID"] ?></td>
            <td><?php echo $rsProducts["Products_Name"] ?></td>
			<td><?php echo empty($biz[$rsProducts["Biz_ID"]]) ? '商家不存在或已删除' : $biz[$rsProducts["Biz_ID"]]["Biz_Name"];?></td>
            <td style="text-align:left; padding:5px">
            	<?php if ($rsBiz['Finance_Type'] == 1) { ?>
            	<?php if($rsProducts["Products_FinanceType"] == 0) { ?>
                结算类型：按交易额比例<br />
                网站提成：<?php echo $rsProducts["Products_PriceX"]?> * <?php echo $rsProducts["Products_FinanceRate"];?> % = <?php echo number_format($rsProducts["Products_PriceX"] * $rsProducts["Products_FinanceRate"]/100,2,'.','');?>
				<?php } else { ?>
                结算类型：按产品供货价<br />
                供货价：<?php echo $rsProducts["Products_PriceS"];?><br />
                网站提成：<?php echo $rsProducts["Products_PriceX"]?> - <?php echo $rsProducts["Products_PriceS"];?> = <?php echo $rsProducts["Products_PriceX"]-$rsProducts["Products_PriceS"];?>
                <?php } ?>
				<?php } else { ?>
				结算类型：按交易额比例<br />
                网站提成：<?php echo $rsProducts["Products_PriceX"]?> * <?php echo $rsBiz["Finance_Rate"];?> % = <?php echo number_format($rsProducts["Products_PriceX"] * $rsBiz["Finance_Rate"]/100,2,'.','');?>
				<?php } ?>
            	
			</td>
			<td>
			<label class="mousehand" id="<?=$rsProducts["Products_ID"]?>">查看详细</label>
            </td>
            <td>
            <?php echo $rsProducts["commission_ratio"].'/'.($rsProducts["nobi_ratio"]);?>
			</td>
            <td nowrap="nowrap"><del>￥<?php echo $rsProducts["Products_PriceY"] ?><br>
              </del>￥<?php echo $rsProducts["Products_PriceX"] ?></td>
            <td nowrap="nowrap"><?php echo empty($JSON["ImgPath"])?'':'<img src="'.$JSON["ImgPath"][0].'" class="proimg" />'; ?></td>
            <td nowraqp="nowrap">
            <img width="140" height="140" src="<?=$rsProducts['Products_Qrcode']?>" /></td>
            <td nowrap="nowrap"><?php echo empty($rsProducts["Products_SoldOut"])?"":"下架<br>";
			echo empty($rsProducts["Products_IsShippingFree"])?"":"免运费<br>";
			echo empty($rsProducts["Products_IsNew"])?"":"新品<br>";
			echo empty($rsProducts["Products_IsRecommend"])?"":"推荐<br>";
			echo empty($rsProducts["Products_IsHot"])?"":"热卖"; ?></td>
			<td nowrap="nowrap"><?php echo $rsProducts["Products_Status"]==0 ? '<font style="color:red">未审核</font>' : '<font style="color:blue">已审核</font>'; ?></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d",$rsProducts["Products_CreateTime"]) ?></td>
            <td class="last" nowrap="nowrap"><a href="products_edit.php?ProductsID=<?php echo $rsProducts["Products_ID"] ?>"><img src="/static/member/images/ico/mod.gif" align="absmiddle" alt="修改" /></a>			
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
$(document).ready(function(){
	$('#excoutput').click(function(){
		window.location = './output.php?' + $('#search_form').serialize() + '&type=product_gross_info';
	})
	$('#auditingAll').click(function(){
                var postData = $('.auditingid').serialize();
                if(postData == ''){
                    alert('请选择要审核的产品！');
                    return false;
                     
                }   
              
                $.post('?',postData,function(data){
                    alert(data.info);
                    if(data.status === 1){
                        $('.delid').removeAttr('checked');
                        window.location.reload();
                    }            
                },'json');
        })	
});
  function selectAll(checkbox) {
        $('input[type=checkbox]').prop('checked', $(checkbox).prop('checked'));
    }      
</script>
</body>
</html>