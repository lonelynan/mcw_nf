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
    $lists = $DB->GetAssoc("shop_products","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Products_ID desc");
    ?>
    <?php foreach ($lists as $k => $v){
        if ($v['pintuan_flag'] == 1 && $v['pintuan_people'] >= 2 && $v['pintuan_start_time'] < time() && $v['pintuan_end_time'] > time()) {
        ?>
        <tr>
            <td nowrap="nowrap"><?php echo $k;?></td>
            <td nowrap="nowrap"><?php echo $v["Products_Name"];?></td>
            <td nowrap="nowrap" class="left last">
                ../detail_pt/detail_pt?proid=<?=$v['Products_ID']?>
            </td>
        </tr>
            <?php }else{?>
            <tr>
                <td nowrap="nowrap"><?php echo $k;?></td>
                <td nowrap="nowrap"><?php echo $v["Products_Name"];?></td>
                <td nowrap="nowrap" class="left last">
                    ../detail/detail?proid=<?=$v['Products_ID']?>
                </td>
            </tr>
            <?php }?>
    <?php }?>
    </tbody>
</table>