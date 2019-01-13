?><?php 
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/update/common.php');
use Illuminate\Database\Capsule\Manager as DB;

if ($_POST) {
    $id = I("id",0);
    if(!$id){
        showMsg("无效的产品ID!");
    }
    $stoptime = strtotime(I("stoptime") . " 23:59:59");
    $Data = array(
            "Products_Index" => I("Products_Index", 0),
            "Products_IsNew"=> I("products_IsNew",0),
            "Products_IsHot"=> I("products_IsHot",0),
            "Products_IsRecommend"=> I("products_IsRecommend", 0),
            "Products_Status" => I("Status"),
            "starttime" => strtotime(I("starttime")),
            "stoptime" => $stoptime
    );
    $Flag = DB::table("pintuan_products")->where(["Products_ID" => $id])->update($Data);
    if ($Flag!==false) {
        if(I("cardids")){
            $idcards = I("cardids");
            $idcards = trim($idcards,",");
            DB::table("pintuan_virtual_card")->where(["Users_ID" =>$UsersID])->whereIn("Card_ID", $idcards)->update(["Products_Relation_ID" => $id]);
        }
        showMsg("保存成功！", "/member/pintuan/products.php");
    }else{
        showMsg("保存失败！");
    }
} else {
    $shop_config = shop_config($_SESSION["Users_ID"]);
    $dis_config = dis_config($_SESSION["Users_ID"]);
    $Shop_Commision_Reward_Arr = array();
    if (! is_null($shop_config['Shop_Commision_Reward_Json'])) {
        $Shop_Commision_Reward_Arr = json_decode(
                $shop_config['Shop_Commision_Reward_Json'], true);
    }
}

$starttime = strtotime(date("Y-m-d") . " 00:00:00");
$endtime = time();
$day = date("d");
$monthStarttime = strtotime(date("Y-m-d") . " 00:00:00") - ($day - 1) * 86400;
$monthEndtime = strtotime(date("Y-m-d") . " 23:59:59");
if (isset($_GET["search"]) && $_GET["search"] == 1) {
    $SearchType = $_GET['SearchType'];
    $searchStarttime = strtotime($_GET['starttime'] . "00:00:00");
    $searchStoptime = strtotime($_GET['stoptime'] . "23:59:59") + 1;
    $days = ($searchStoptime - $searchStarttime) / 86400;
    $searchEnddate = $_GET['stoptime'];
} else {
    $day = date("d");
    $searchStarttime = strtotime(date("Y-m-d") . " 00:00:00") - ($day - 1) * 86400;
    $searchStoptime = strtotime(date("Y-m-d") . " 23:59:59");
    $searchStartdate = date('Y-m-d', $searchStarttime);
    $searchEnddate = date('Y-m-d', $searchStoptime);
    $days = ($searchStoptime + 1 - $searchStarttime) / 86400;
    $SearchType = 'reg';
}
if ($searchStoptime <= $searchStarttime) {
    echo '<script language="javascript">alert("截止时间不能小于开始时间");history.back();</script>';
    exit();
}
for ($i = $days; $i > 0; $i--) {
    $fromtime = strtotime($searchEnddate . "00:00:00") - ($i - 1) * 86400;
    $dates[] = date("m-d", $fromtime);
    $fromtimes[] = $fromtime;
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
<script type='text/javascript' src='/static/member/js/products_attr_helper.js'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script type='text/javascript' src='/static/member/js/shop.js'></script>
<script type='text/javascript' src="/static/js/plugin/laydate/laydate.js"></script>
<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
<script type='text/javascript'>
var Browser = new Object();
KindEditor.options.cssData = 'body {color:#aaa;}';
KindEditor.ready(function(K) {
  K.create('textarea[name="Description"]', {
    themeType : 'simple',
    filterMode : false,
    uploadJson : '/member/upload_json.php?TableField=web_column&Users_ID=<?php echo $_SESSION["Users_ID"];?>',
    fileManagerJson : '/member/file_manager_json.php',
    allowFileManager : true,
    readonlyMode : true
  }); 
});

</script>
<style type="text/css">
.dislevelcss{float:left;margin:5px 0px 0px 8px;text-align:center;border:solid 1px #858585;padding:5px;}
.dislevelcss th{border-bottom:dashed 1px #858585;font-size:16px;}
.r_con_form .rows .input .disabled,.disabled  {color:#aaa; }
.img {margin-left:2px; }
.img img { max-width:240px;width:240px; } 
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/user.js'></script>
    <?php include 'top.php'; ?>
    <div id="products" class="r_con_wrap">
      <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script>
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
      <script type='text/javascript' src='/static/js/plugin/daterangepicker/moment_min.js'></script>
      <link href='/static/js/plugin/daterangepicker/daterangepicker.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/daterangepicker/daterangepicker.js'></script> 
      <script language="javascript">$(document).ready(user_obj.coupon_add_init);</script>
<?php
      $pintuanid=$_GET['id'];
      $pintuan=$DB->getRs("pintuan_products","*","where Users_ID='".$_SESSION["Users_ID"]."'and Products_ID='".$pintuanid."'");
?>
      <form id="product_add_form" class="r_con_form skipForm" method="post" action="product_edit.php">
        <input type="hidden" name="id" value="<?=I('id') ?>" />
        <div class="rows">
          <label>排序</label>
          <span class="input">
          <input type="text" name="Products_Index" value="<?php echo $pintuan["Products_Index"]?>" class="form_input" size="35" maxlength="100"  />
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>产品名称</label>
          <span class="input">
          <input type="text" name="Name" value="<?php echo $pintuan["Products_Name"]?>" class="form_input disabled" size="35" maxlength="100" readonly />
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows" id="type_html">
       <label>产品类型：</label>
       <span class="input">
       <select name="TypeID" style="width:180px;" id="Type_ID" disabled="disabled" class="disabled">
        <option value="">请选择类型</option>
          <?php
            $typeid = explode(',',$pintuan['Products_Category']);
            $DB->get("pintuan_category","*","where Users_ID='".$_SESSION["Users_ID"]."' order by  sort asc");
            while($rsType= $DB->fetch_assoc()){
              $t = in_array($rsType["cate_id"],$typeid)?'selected':'';
              echo '<option value="'.$rsType["cate_id"].'"'.$t.'  >'.$rsType["cate_name"].'</option>';
            }
            ?>
       </select>
       <font class="fc_red">*</font></span>
       <div class="clear"></div>
    </div>     
        <div class="rows">
          <label>产品价</label>
          <span class="input price"> 单购价格:￥
          <input type="text" name="PriceD" value="<?php echo $pintuan["Products_PriceD"]?>" class="form_input disabled" size="5" maxlength="10" readonly/>
          团购价格:￥
          <input type="text" name="PriceT" value="<?php echo $pintuan["Products_PriceT"]?>" class="form_input disabled" size="5" maxlength="10" readonly/>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>财务结算类型</label>
          <span class="input">
          	  <input type="hidden" name="FinanceType" value="<?php echo $pintuan["Products_FinanceType"]?>" />
              <input type="radio" name="FinanceType1" value="0" id="FinanceType_0" onClick="$('#PriceS').hide();$('#FinanceRate').show();"<?php echo $pintuan["Products_FinanceType"]==0 ? ' checked' : '';?> disabled/><label for="FinanceType_0"> 按交易额比例</label>&nbsp;&nbsp;<input type="radio" name="FinanceType1" value="1" id="FinanceType_1" onClick="$('#FinanceRate').hide();$('#PriceS').show();"<?php echo $pintuan["Products_FinanceType"]==1 ? ' checked' : '';?> disabled/><label for="FinanceType_1"> 按供货价</label><br />
          <span class="tips">注：若按交易额比例，则网站提成为：产品售价*比例%</span>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows" id="FinanceRate"<?php echo $pintuan["Products_FinanceType"]==1 ? ' style="display:none"' : '';?>>
          <label>网站提成</label>
          <span class="input">
          <input type="text" name="FinanceRate" value="<?php echo $pintuan["Products_FinanceRate"];?>" class="form_input disabled" size="10" readonly/> %
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows" id="PriceS"<?php echo $pintuan["Products_FinanceType"]==0 ? ' style="display:none"' : '';?>>
          <label>供货价</label>
          <span class="input">
          <input type="text" name="PriceS" value="<?php echo $pintuan["Products_PriceSt"];?>" class="form_input disabled" size="10" readonly/> 元
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>产品重量</label>
          <span class="input">
          <input type="text" name="Weight" value="<?=$pintuan["Products_Weight"]?>" readonly class="form_input disabled" size="5" />&nbsp;&nbsp;千克
          </span>
          <div class="clear"></div>
        </div>
    <?php
    $image=json_decode($pintuan['Products_JSON'],true);
      ?>
        <div class="rows">
          <label>产品图片</label>
          <span class="input">
          		<div class="img" id="PicDetail"></div>
          </span>
          <div class="clear"></div>
        </div> 
        <div class="rows">
          <label>简短介绍</label>
          <span class="input">
          <textarea name="BriefDescription" class="briefdesc disabled" readonly/><?php echo $pintuan['Products_BriefDescription']; ?></textarea>
          </span>
          <div class="clear"></div>
        </div>
         
        <div id="propertys" display="none"></div> 
         <div class="rows">
          <label>商品属性</label>
          <span class="input">
            <label>
              <input type="checkbox"  value="1" name="products_IsNew"
                  <?=$pintuan['products_IsNew']?'checked':'' ?>>&nbsp;新品&nbsp;
          </label>&nbsp;&nbsp;
           <label><input type="checkbox" value="1" name="products_IsHot" <?=$pintuan['products_IsHot']?'checked':'' ?>>&nbsp;热销&nbsp;</label>&nbsp;&nbsp;
              <label><input type="checkbox" value="1" name="products_IsRecommend" <?=$pintuan['products_IsRecommend']?'checked':'' ?>>&nbsp;促销&nbsp;</label>
          </span>
          <div class="clear"></div>
        </div>
         <div class="rows">
          <label>商家信誉</label>
          <span class="input">
          <label>包邮&nbsp;&nbsp;<input name="pinkage" type="checkbox"  value="1" <?php echo empty($pintuan["Products_pinkage"])?"":" checked" ?> disabled/>&nbsp;&nbsp;</label>&nbsp;&nbsp;
          <label>七天退款&nbsp;&nbsp;<input name="refund" type="checkbox"  value="1" <?php echo empty($pintuan["Products_refund"])?"":" checked" ?> disabled/>&nbsp;&nbsp;</label>
          <label>假一赔十&nbsp;&nbsp;<input name="compensation" type="checkbox"  value="1" <?php echo empty($pintuan["Products_compensation"])?"":" checked" ?> disabled/>&nbsp;&nbsp;</label>
          </span>
          <div class="clear"></div>
        </div>
          <div class="clear"></div>
        <div class="rows">
          <label>订单流程</label>
          <span class="input" style="font-size:12px; line-height:22px;">
              <input type="radio" id="order_0" value="0" name="ordertype"  checked <?php echo $pintuan["order_process"]==0?'checked' : '';?> disabled/><label for="order_0"> 实物订单&nbsp;&nbsp;( 买家下单 -> 买家付款 -> 商家发货 -> 买家收货 -> 订单完成 ) </label><br />
              <input type="radio" id="order_1" value="1" name="ordertype" <?php echo $pintuan["order_process"]==1?'checked' : '';?> disabled/><label for="order_1"> 虚拟订单&nbsp;&nbsp;( 买家下单 -> 买家付款 -> 系统发送消费券码到买家手机 -> 商家认证消费 -> 订单完成 ) </label><br />
              
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>是否支持单购</label>
          <span class="input">
          <label>既支持单购又支持团购&nbsp;<input type="radio" id="aa" value="1" name="Isbuy" <?php echo $pintuan["is_buy"]==1?'checked' : '';?> disabled/></label>&nbsp;&nbsp;
          <label>仅支持团购&nbsp;<input type="radio" value="0" id="bb" name="Isbuy" <?php echo $pintuan["is_buy"]==0?'checked' : '';?> disabled/></label>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>拼团人数</label>
          <span class="input">
          <input type="text" name="Peoplenum" value="<?php echo $pintuan["people_num"]?>" class="form_input disabled" size="5" maxlength="10" readonly/> <span class="tips ">&nbsp;注:若不限则填写0.</span>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>是否支持抽奖</label>
          <span class="input">
        	<table cellspacing="1" cellpadding="6" class="tb">  
            <tr> 
                <td class="tr"><input type="radio" name="Isdraw" value="1" <?php echo $pintuan["Is_Draw"]==1?'checked':'';?> disabled/>不支持抽奖    <input type="radio" name="Isdraw" value="0" <?php echo $pintuan["Is_Draw"]==0?'checked':'';?> disabled/> 支持抽奖</td>
            </tr>  
            <tr id="333">  
                <td class="tl"><span color="f_red">抽奖规则</span></td>  
                <td class="tr">
                    <textarea name="Awardrule" class="briefdesc disabled" readonly /><?php echo $pintuan["Award_rule"]?></textarea>
                </td>
          </span>
            </tr>  
            <tr id="444">  
                <td class="tl"><span color="f_red">允许中奖团数</span></td>  
                <td class="tr">
                <input type="text" size="8" name="T_count" value="<?php echo $pintuan["Team_Count"];?>" class="disabled" readonly/>
                <input type="hidden" size="6" name="ratio" id="ratio" value="<?php echo $pintuan["Ratio"]?>"/></td>  
                <td></td></span>
            </tr>  
        
        	</table></span>
          <div class="clear"></div>
        </div>
        <div id="store_part" style="display:none">
          <div class="rows">
                <div class="clear"></div>
            </div>
            <div class="rows">
                <div class="clear"></div>
            </div>
        </div>
        <input type="hidden" name="people_once" value="1" />
        <div class="rows">
          <label>商品库存</label>
          <span class="input">
          <input type="text" name="Count" value="<?php echo $pintuan["Products_Count"]?>" class="form_input disabled" size="5" maxlength="10"  readonly/> <span class="tips ">&nbsp;</span>
          </span>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
         </div>
        <div class="rows">
          <label>拼团有效时间</label>
           <span class="input time">
           <div class="l">
              <div class="form-group">
                  <div class="input-group" id="reportrange" style="width:auto">
                  <input placeholder="开始时间" class="laydate-icon" name="starttime" value="<?php echo date("Y-m-d ",$pintuan['starttime']);?>" onclick="laydate()"/>-
                  <input placeholder="截止时间" class="laydate-icon" name="stoptime" value="<?php echo date("Y-m-d ",$pintuan['stoptime']);?>" onclick="laydate()"/>
                  </div>
              </div>
          </div>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>详细介绍</label>
          <span class="input">
          <textarea class="ckeditor" name="Description" style="width:700px; height:300px;" notnull readonly /><?php echo $pintuan["Products_Description"]?></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>是否审核</label>
          <span class="input" style="font-size:12px;">
              <input type="radio" id="status_0" value="0" name="Status"<?php echo $pintuan["Products_Status"]==0 ? ' checked' : '';?> /><label for="status_0"> 待审核 </label>&nbsp;&nbsp;
              <input type="radio" id="status_1" value="1" name="Status"<?php echo $pintuan["Products_Status"]==1 ? ' checked' : '';?> /><label for="status_1"> 通过审核 </label>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" name="submit_button" value="提交保存" />
          <a href="products.php" class="btn_gray">返回</a></span>
          <div class="clear"></div>
        </div>
        <input type='hidden' value='' id='cardids' name='cardids' />
        <input type="hidden" id="UsersID" value="<?=$_SESSION["Users_ID"]?>" />
        <input type="hidden" id="ProductsID" value="0">
      </form>
    </div>
  </div>
</div>
<script type="text/javascript">
var images = eval('<?php echo json_encode($image['ImgPath']); ?>');
for (var i = 0; i < images.length; i++) {
  $('#PicDetail').append('<div class="imagedel'+i+'"><img src="'+images[i]+'" /></a><a onclick="return imagedel1('+i+');"></div>');
}
</script>  
</body>
</html>