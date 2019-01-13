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
    $lists = array();
    $DB->get("shop_category","Category_ID,Category_Name,Category_ParentID","where Users_ID='".$_SESSION["Users_ID"]."' order by Category_ParentID asc,Category_Index asc,Category_ID asc");
    while($r=$DB->fetch_assoc()){
        if($r["Category_ParentID"] == 0){
            $lists[$r["Category_ID"]] = $r;
        }else{
            if($r["Category_ParentID"]<>$r["Category_ID"]){
                $lists[$r["Category_ParentID"]]["child"][] = $r;
            }
        }
    }
    $i = 0;
    foreach($lists as $k=>$v){

        $i++;
        ?>
        <tr>
            <td nowrap="nowrap"><?php echo $i;?></td>
            <td nowrap="nowrap"><?php echo $v["Category_Name"];?></td>
            <td nowrap="nowrap" class="left last">
                ../pro_list/pro_list?Cate_ID=<?=$v['Category_ID']?>
            </td>
        </tr>
        <?php
        if(!empty($v["child"])){
            foreach($v["child"] as $key=>$value){
                ?>
                <tr>
                    <td nowrap="nowrap"><?php echo $i.'.'.($key+1);?></td>
                    <td nowrap="nowrap"><?php echo $value["Category_Name"];?></td>
                    <td nowrap="nowrap" class="left last">
                        ../pro_list/pro_list?Cate_ID=<?=$value['Category_ID']?>
                    </td>
                </tr>
            <?php	}
        }
    }
    ?>

    </tbody>
</table>