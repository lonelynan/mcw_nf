<?php /* Smarty version Smarty-3.1.13, created on 2018-07-27 09:34:33
         compiled from "/www/wwwroot/maocongwang.com/member/shop/html/shipping_company_edit_form.html" */ ?>
<?php /*%%SmartyHeaderCode:11431265135b5a76a9dce1a2-72508601%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd2812186c8b3add4d2efc425bacbf9708e08d097' => 
    array (
      0 => '/www/wwwroot/maocongwang.com/member/shop/html/shipping_company_edit_form.html',
      1 => 1532086478,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11431265135b5a76a9dce1a2-72508601',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'Shipping' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.13',
  'unifunc' => 'content_5b5a76a9efcfb7_04867066',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5b5a76a9efcfb7_04867066')) {function content_5b5a76a9efcfb7_04867066($_smarty_tpl) {?><form class="form" action="shipping.php" method="post" id="create_shipping_form" name="mod_create_shipping">
        <p class="rows">
        <label for="Shipping_Name">名称</label>
        <input type="text" value="<?php echo $_smarty_tpl->tpl_vars['Shipping']->value['Shipping_Name'];?>
" class="{required:true}" name="Shipping_Name"  />
        </p> 
        
       
        
        <p class="rows">
        <label>状态</label>
           
           <input name="Shipping_Status" value="1"  type="radio" <?php if ($_smarty_tpl->tpl_vars['Shipping']->value['Shipping_Status']==1){?>checked<?php }?> />&nbsp;&nbsp;可用
           <input name="Shipping_Status" value="0"  type="radio"  <?php if ($_smarty_tpl->tpl_vars['Shipping']->value['Shipping_Status']==0){?>checked<?php }?> />&nbsp;&nbsp; 不可用
        </p> 
     
       <p class="rows">
        <label></label>
          
        <input type="submit" value="确定提交" name="submit_btn">
      
      </div>
      
      <input type="hidden" name="Shipping_ID" value="<?php echo $_smarty_tpl->tpl_vars['Shipping']->value['Shipping_ID'];?>
">  
      <input type="hidden" name="action" value="edit_shipping_company">  
</form><?php }} ?>