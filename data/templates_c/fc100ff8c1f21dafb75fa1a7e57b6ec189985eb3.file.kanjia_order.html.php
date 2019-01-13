<?php /* Smarty version Smarty-3.1.13, created on 2018-08-01 16:47:18
         compiled from "/www/wwwroot/maocongwang.com/api/user/html/kanjia_order.html" */ ?>
<?php /*%%SmartyHeaderCode:3639848585b59754944ea39-90653413%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fc100ff8c1f21dafb75fa1a7e57b6ec189985eb3' => 
    array (
      0 => '/www/wwwroot/maocongwang.com/api/user/html/kanjia_order.html',
      1 => 1532755042,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '3639848585b59754944ea39-90653413',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.13',
  'unifunc' => 'content_5b5975497692b5_78058045',
  'variables' => 
  array (
    'title' => 0,
    'base_url' => 0,
    'UsersID' => 0,
    'Status' => 0,
    'order_list' => 0,
    'item' => 0,
    'Product_List' => 0,
    'Product' => 0,
    'Product_ID' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5b5975497692b5_78058045')) {function content_5b5975497692b5_78058045($_smarty_tpl) {?><!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</title>
<link href='<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
static/css/global.css' rel='stylesheet' type='text/css' />
<link href='<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
static/api/css/user.css' rel='stylesheet' type='text/css' />
<link href="/static/api/shop/skin/default/css/member.css?t=1493369078" rel="stylesheet" type="text/css">
<script type='text/javascript' src='<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
static/api/js/global.js'></script>
<script type='text/javascript' src='<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
static/api/js/user.js'></script>
<script type='text/javascript' src='<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
static/js/plugin/layer_mobile/layer.js'></script>
<script type='text/javascript' src='<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
static/api/shop/js/shop.js?t=10'></script>
    <style>
        .msg_layer .layui-m-layercont {color: #fff;}
    </style>
</head>

<body>
<script type="text/javascript">$(document).ready(user_obj.message_init);</script>
<script>
var base_url = '<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
';
var UsersID = '<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
';
$(document).ready(shop_obj.page_init);
</script>
<div id="message">
  <div class="t"><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</div>
  <!-- 订单状态选择器 begin -->
  <ul id="member_nav">
    <li class="<?php if ($_smarty_tpl->tpl_vars['Status']->value==0){?>cur<?php }?>" style="width:20%"><a href="/api/<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
/user/kanjia_order/status/0/">待确认</a></li>
	<li class="<?php if ($_smarty_tpl->tpl_vars['Status']->value==1){?>cur<?php }?>" style="width:20%"><a href="/api/<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
/user/kanjia_order/status/1/">待付款</a></li>
    <li class="<?php if ($_smarty_tpl->tpl_vars['Status']->value==2){?>cur<?php }?>" style="width:20%"><a href="/api/<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
/user/kanjia_order/status/2/">已付款</a></li>
    <li class="<?php if ($_smarty_tpl->tpl_vars['Status']->value==3){?>cur<?php }?>" style="width:20%"><a href="/api/<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
/user/kanjia_order/status/3/">已发货</a></li>
	<li class="<?php if ($_smarty_tpl->tpl_vars['Status']->value==4){?>cur<?php }?>" style="width:20%"><a href="/api/<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
/user/kanjia_order/status/4/">已完成</a></li>
  </ul>
  <!-- 订单状态选择器 end -->
  
  <!-- 订单列表 begin -->
	<div id="order_list">
    <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['order_list']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
?>
    <div class="item">
            <h1>
      订单号：<a href="<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
api/<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
/user/kanjia_order/detail/<?php echo $_smarty_tpl->tpl_vars['item']->value['Order_ID'];?>
/?wxref=mp.weixin.qq.com"><?php echo $_smarty_tpl->tpl_vars['item']->value['Order_Sn'];?>
</a>（<strong class="fc_red">￥<?php echo $_smarty_tpl->tpl_vars['item']->value['Order_TotalAmount'];?>
</strong>）  
      </h1>
     	<?php  $_smarty_tpl->tpl_vars['Product_List'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['Product_List']->_loop = false;
 $_smarty_tpl->tpl_vars['Product_ID'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['item']->value['Order_CartList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['Product_List']->key => $_smarty_tpl->tpl_vars['Product_List']->value){
$_smarty_tpl->tpl_vars['Product_List']->_loop = true;
 $_smarty_tpl->tpl_vars['Product_ID']->value = $_smarty_tpl->tpl_vars['Product_List']->key;
?>
 			<?php  $_smarty_tpl->tpl_vars['Product'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['Product']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['Product_List']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['Product']->key => $_smarty_tpl->tpl_vars['Product']->value){
$_smarty_tpl->tpl_vars['Product']->_loop = true;
?>
				<?php  $_smarty_tpl->tpl_vars['Product_ID'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['Product_ID']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['Product']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['Product_ID']->key => $_smarty_tpl->tpl_vars['Product_ID']->value){
$_smarty_tpl->tpl_vars['Product_ID']->_loop = true;
?>
					<div class="pro">
					<div class="img"><a href="<?php echo $_smarty_tpl->tpl_vars['base_url']->value;?>
api/<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
/user/kanjia_order/detail/<?php echo $_smarty_tpl->tpl_vars['item']->value['Order_ID'];?>
/?wxref=mp.weixin.qq.com"><img src="<?php echo $_smarty_tpl->tpl_vars['Product_ID']->value['ImgPath'];?>
" width="100" height="100"></a></div>
					<dl class="info">
						<dd class="name"><?php echo $_smarty_tpl->tpl_vars['Product_ID']->value['ProductsName'];?>
</dd>
						<dd>价格:￥<?php echo $_smarty_tpl->tpl_vars['Product_ID']->value['Cur_Price'];?>
×<?php echo $_smarty_tpl->tpl_vars['Product_ID']->value['Qty'];?>
=￥<?php echo $_smarty_tpl->tpl_vars['Product_ID']->value['Cur_Price']*$_smarty_tpl->tpl_vars['Product_ID']->value['Qty'];?>
</dd></dl>
					<div class="clear"></div>
					</div>
				<?php } ?>
          	<?php } ?>			
        <?php } ?>
		<?php if ($_smarty_tpl->tpl_vars['Status']->value==3){?>
    <div class="confirm_receive"><a class="confirmreceive" href="javascript:void(0)" Commission_withdraw="5" Commission_withdraw_day = "" Order_ID="<?php echo $_smarty_tpl->tpl_vars['item']->value['Order_ID'];?>
" style="margin:0px 4px 0px 4px">确认收货</a></div>
        <?php }elseif($_smarty_tpl->tpl_vars['Status']->value==1){?>
        <div class="confirm_receive"><a href="/api/<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
/user/payment/<?php echo $_smarty_tpl->tpl_vars['item']->value['Order_ID'];?>
/<?php echo $_smarty_tpl->tpl_vars['item']->value['productid'];?>
/?wxref=mp.weixin.qq.com" style="margin:0px 4px 0px 4px">付 款</a></div>
        <?php }?>
        <?php if ($_smarty_tpl->tpl_vars['item']->value['show_comment_btn']==1){?>
        <div class="confirm_receive"><a href="/api/<?php echo $_smarty_tpl->tpl_vars['UsersID']->value;?>
/user/commit/<?php echo $_smarty_tpl->tpl_vars['item']->value['Order_ID'];?>
/">评论</a></div>
        <?php }?>
   </div>
    <?php } ?>
  </div>
  
  <!-- 订单列表 begin -->
  
  
  </div>
<div id="footer_user_points"></div>
<!--<?php echo $_smarty_tpl->getSubTemplate ("lbi/footer_user.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
-->

</body>
</html><?php }} ?>