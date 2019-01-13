<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
<thead>
<tr>
    <td width="10%" nowrap="nowrap">序号</td>
    <td width="20%" nowrap="nowrap">名称</td>
    <td width="60%" nowrap="nowrap" class="last">Url</td>
</tr>
</thead>
<tbody>
<?php
$lists = $DB->GetAssoc("biz","Biz_ID,Biz_Name","where Users_ID='".$_SESSION["Users_ID"]."' order by Biz_IndexShow asc,Biz_ID asc");
?>
<?php foreach ($lists as $k => $v){?>
    <tr>
        <td nowrap="nowrap"><?php echo $k;?></td>
        <td nowrap="nowrap"><?php echo $v["Biz_Name"];?></td>
        <td nowrap="nowrap" class="left last">
            ../shangjia_xq/shangjia_xq?biz_id=<?=$v['Biz_ID']?>
        </td>
    </tr>
<?php }?>
</tbody>
</table>