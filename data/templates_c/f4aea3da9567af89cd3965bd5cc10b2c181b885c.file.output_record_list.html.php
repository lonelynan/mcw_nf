<?php /* Smarty version Smarty-3.1.13, created on 2018-07-28 19:08:08
         compiled from "/www/wwwroot/maocongwang.com/member/shop/html/output_record_list.html" */ ?>
<?php /*%%SmartyHeaderCode:17080541505b52934e3bc123-75746772%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f4aea3da9567af89cd3965bd5cc10b2c181b885c' => 
    array (
      0 => '/www/wwwroot/maocongwang.com/member/shop/html/output_record_list.html',
      1 => 1532755052,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '17080541505b52934e3bc123-75746772',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.13',
  'unifunc' => 'content_5b52934e4ea8c8_78793823',
  'variables' => 
  array (
    'output_record_list' => 0,
    'item' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5b52934e4ea8c8_78793823')) {function content_5b52934e4ea8c8_78793823($_smarty_tpl) {?><!-- 记录列表  begin -->
<table class="table table-striped">
  <thead>
    <tr>
      <th>#</th>
      <th>分销商</th>
      <th>流水号</th>
      <th>金额</th>
      <th>状态</th>
      <th>时间</th>
    </tr>
  </thead>
  <tbody>
  
  <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['output_record_list']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
  <tr>
    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['Record_ID'];?>
</td>
    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['User_NickName'];?>
</td>
    <td><?php echo $_smarty_tpl->tpl_vars['item']->value['Record_Sn'];?>
 </td>
    <td class="red">&yen;<?php echo $_smarty_tpl->tpl_vars['item']->value['Record_Money'];?>
</td>
    <td style="color:#1fbba6;"><?php echo $_smarty_tpl->tpl_vars['item']->value['Record_Status'];?>
</td>
     <td><?php echo $_smarty_tpl->tpl_vars['item']->value['Record_CreateTime'];?>
</td>
 
  </tr>
  <?php } ?>
    </tbody>
  
</table>

<!-- 记录列表 end-->
<?php }} ?>