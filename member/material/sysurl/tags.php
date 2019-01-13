<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
    <?php
    $Actives = $DB->GetAssoc('active','*','where Users_ID="' . $_SESSION['Users_ID'] . '"');
    ?>
    <thead>
    <tr>
        <td width="10%" nowrap="nowrap">序号</td>
        <td width="20%" nowrap="nowrap">名称</td>
        <td width="60%" nowrap="nowrap" class="last">Url</td>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($Actives as $k => $v){?>
        <tr>
            <td nowrap="nowrap"><?=$v['Active_ID']?></td>
            <td nowrap="nowrap"><?=$v['Active_Name']?></td>
            <td nowrap="nowrap" class="left last">
                <?php if($v['Type_ID'] == 5){
                    echo 'http://'. $rulreal .'/api/' . $_SESSION["Users_ID"] . '/shop/pro_list/?flashsale_flag=1';
                }elseif ($v['Type_ID'] == 6){
                    echo 'http://'. $rulreal .'/api/' . $_SESSION["Users_ID"] . '/shop/pro_list/0/?Is_hot=1';
                }elseif ($v['Type_ID'] == 7){
                    echo 'http://'. $rulreal .'/api/' . $_SESSION["Users_ID"] . '/shop/pro_list/?special_offer=1';
                }elseif ($v['Type_ID'] == 8){
                    echo 'http://'. $rulreal .'/api/' . $_SESSION["Users_ID"] . '/shop/pro_list/?super_team=1';
                }
                ?>

            </td>
        </tr>
    <?php }?>
    </tbody>
</table>