<?php /* Smarty version Smarty-3.1.13, created on 2018-07-28 19:08:08
         compiled from "/www/wwwroot/maocongwang.com/member/shop/html/input_record_list.html" */ ?>
<?php /*%%SmartyHeaderCode:17045403345b52934db2e208-59809360%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2d5a3f8b76a46c8d8c7847f843e999eb83821c4e' => 
    array (
      0 => '/www/wwwroot/maocongwang.com/member/shop/html/input_record_list.html',
      1 => 1532755052,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17045403345b52934db2e208-59809360',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.13',
  'unifunc' => 'content_5b52934e3a0393_20817132',
  'variables' => 
  array (
    'input_record_list' => 0,
    'item' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5b52934e3a0393_20817132')) {function content_5b52934e3a0393_20817132($_smarty_tpl) {?><!-- 记录列表  begin -->
<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>订单号</th>
      <th>总价</th>
      <th>状态</th>
      <th>时间</th>
      <th>详情</th>
    </tr>
  </thead>
  <tbody>
  
  <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['input_record_list']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
  <tr>
    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['Order_ID'];?>
</td>
    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['Order_Sn'];?>
 </td>
    <td class="red">&yen;<?php echo $_smarty_tpl->tpl_vars['item']->value['Order_TotalAmount'];?>
</td>
    <td style="color:#1fbba6;"><?php echo $_smarty_tpl->tpl_vars['item']->value['Order_Status'];?>
</td>
     <td><?php echo $_smarty_tpl->tpl_vars['item']->value['Order_CreateTime'];?>
</td>
    <td><a href="<?php echo $_smarty_tpl->tpl_vars['item']->value['Order_Link'];?>
"><img src="/static/member/images/ico/x3_06_03.jpg"/></a></td>
  </tr>
  <?php } ?>
    </tbody>
  
</table>

<!-- 记录列表 end-->
<?php }} ?>