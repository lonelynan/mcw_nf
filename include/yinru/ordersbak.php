<?php
if(!empty($lists_back)){
	foreach($lists_back as $item){
		$CartList_back=json_decode(htmlspecialchars_decode($item["Back_Json"]),true);
		echo '<tr class="item_list" align="center">
            <td valign="top"><img src="'.$CartList_back["ImgPath"].'" width="100" height="100" /></td>
            <td align="left" class="flh_180">'.$CartList_back["ProductsName"].'<br>';
			if(!empty($CartList_back["Property"])){
						$Attr = $CartList_back["Property"]['shu_value'];
						$ProductsPriceX = $CartList_back["Property"]['shu_pricesimp'];
					}else{
						$Attr = '';
						$ProductsPriceX = $CartList_back["ProductsPriceX"];
					}
		echo '<dd>'.$Attr.'</dd>';
        echo '</td>
            <td>￥'.$CartList_back["ProductsPriceX"].'</td>
            <td>'.$CartList_back["Qty"].'</td>
            <td>￥'.$CartList_back["ProductsPriceX"]*$CartList_back["Qty"].'<br /><font style="text-decoration:line-through; font-size:12px; color:#999">&nbsp;退款金额：￥'.$item["Back_Amount"].'&nbsp;</font><br />'.$_STATUS[$item["Back_Status"]].'</td>
          </tr>';
	}
}
?>