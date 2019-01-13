<?php /* Smarty version Smarty-3.1.13, created on 2018-08-24 16:15:53
         compiled from "/www/wwwroot/maocongwang.com/biz/html/shipping_free_setting.html" */ ?>
<?php /*%%SmartyHeaderCode:19545642595b7fbeb9e36bf9-93626981%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd6d09f8ee1037e128e00564b0535df6a5ef38c7d' => 
    array (
      0 => '/www/wwwroot/maocongwang.com/biz/html/shipping_free_setting.html',
      1 => 1532755044,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '19545642595b7fbeb9e36bf9-93626981',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'free_content' => 0,
    'item' => 0,
    'key' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.13',
  'unifunc' => 'content_5b7fbeb9ef7aa9_29264775',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5b7fbeb9ef7aa9_29264775')) {function content_5b7fbeb9ef7aa9_29264775($_smarty_tpl) {?><table style="display: table;">
  <tbody>
    <tr>
      <th>选择地区</th>
      <th>设置包邮条件</th>
      <th>操作</th>
    </tr>
    <?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_smarty_tpl->tpl_vars['key'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['free_content']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['key']->value = $_smarty_tpl->tpl_vars['item']->key;
?>
    	<tr data-index="0">
      <td>
      <?php if (strlen($_smarty_tpl->tpl_vars['item']->value['areas_desc'])>0){?>
       <span><?php echo $_smarty_tpl->tpl_vars['item']->value['areas_desc'];?>
</span>
   	  <?php }else{ ?>
      <span>未制定区域</span>
      <?php }?>
      <a href="javascript:void(0)" area_type="set_free" area_value_container="address-area-<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" class="Edit_Area" title="编辑运送区域" data-areas="">编辑</a>
        <input class="address-area" name="areas[]" id="address-area-<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['areas'];?>
" type="hidden">
        <input class="area_desc" name="areas_desc[]" id="address-desc-<?php echo $_smarty_tpl->tpl_vars['key']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['areas_desc'];?>
" type="hidden"></td>    
     
      <td><select class="J_ChageContion" name="designated[]">
          
          <option value="0" <?php if ($_smarty_tpl->tpl_vars['item']->value['designated']==0){?>selected<?php }?>>件数</option>
          <option value="1" <?php if ($_smarty_tpl->tpl_vars['item']->value['designated']==1){?>selected<?php }?>>金额</option>
          <option value="2" <?php if ($_smarty_tpl->tpl_vars['item']->value['designated']==2){?>selected<?php }?>>件数 + 金额</option>
          
        </select>
        <p class="free-contion"> 
         <?php if ($_smarty_tpl->tpl_vars['item']->value['designated']==0){?>
          满<input class="input-text" name="preferentialQty[]" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['prefrrendial_qty'];?>
" notnull type="text">件包邮
           <input value="0" type="hidden" name="preferentialMoney[]" />
         <?php }elseif($_smarty_tpl->tpl_vars['item']->value['designated']==1){?>
          满<input class="input-text" name="preferentialMoney[]" value="<?php echo $_smarty_tpl->tpl_vars['item']->value['prefrrendial_money'];?>
" notnull type="text"> 元包邮 
               <input value="0"  name="preferentialQty[]" notnull type="hidden">
         <?php }elseif($_smarty_tpl->tpl_vars['item']->value['designated']==2){?>
         
         满 <input  class="input-text" name="preferentialQty[]" notnull value="<?php echo $_smarty_tpl->tpl_vars['item']->value['prefrrendial_qty'];?>
"type="text"> 件 , <input name="preferentialMoney[]" class="input-text input-65" notnull value="<?php echo $_smarty_tpl->tpl_vars['item']->value['prefrrendial_money'];?>
" type="text"> 元以上 包邮      
         <?php }?> 
          </p></td>
      <td><a data-spm-anchor-id="0.0.0.0" href="javascript:void(0)" class="J_AddItem small-icon"></a> <a href="javascript:void(0)" class="J_DelateItem small-icon"></a></td>
    </tr>
    <?php } ?>
  </tbody>
</table>
<?php }} ?>