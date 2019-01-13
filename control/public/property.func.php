<?php
 
function build_attr_html($DB,$type_id, $product_id = 0){
	
    $attr = get_attr_list_by_typeid($DB,$type_id,$product_id);
	
    $html = '<table width="100%" id="attrTable">';
    $spec = 0;
	

    foreach ($attr AS $key => $val)
    {
		
        $html .= "<tr><td class='label'>";
        if ($val['Property_Type'] == 2||$val['Property_Type'] == 1)
        {
            $html .= ($spec != $val['Property_ID']) ?
                "<a href='javascript:;' onclick='addSpec(this)'>[+]</a>" :
                "<a href='javascript:;' onclick='removeSpec(this)'>[-]</a>";
            
			$spec = $val['Property_ID'];
        }

        $html .= $val['Property_Name']."</td><td><input type='hidden' name='attr_id_list[]' value='".$val['Property_ID']."' />";

        if ($val['Property_Input_Type'] == 0)
        {
            $html .= '<input  notnull name="attr_value_list[]" type="text" value="' .(empty($val['Property_Value']) ? '' : htmlspecialchars($val['Property_Value'])). '" size="40" /> ';
        }
        elseif ($val['Property_Input_Type'] == 2)
        {
            $html .= '<textarea notnull name="attr_value_list[]" rows="3" cols="40">' .(empty($val['Property_Value']) ? '' : htmlspecialchars($val['Property_Value'])). '</textarea>';
        }
        else
        {
            $html .= '<select  name="attr_value_list[]" notnull>';
            $html .= '<option value="">请选择属性值</option>';

            $attr_values = explode("\n", $val['Property_Values']);
			
			
            foreach ($attr_values AS $opt)
            {
				$opt = trim($opt);
				if(!$opt) continue;
                $opt    = trim(htmlspecialchars($opt));
				if(!empty($val['Property_Value'])){
					$html   .= ($val['Property_Value'] != $opt) ?
                    '<option value="' . $opt . '">' . $opt . '</option>' :
                    '<option value="' . $opt . '" selected="selected">' . $opt . '</option>';
				}else{
					$html   .= '<option value="' . $opt . '">' . $opt . '</option>';
				}
                
            }
            $html .= '</select> ';
        }

	
        $html .= ($val['Property_Type'] == 1 || $val['Property_Type'] == 2) ?
             '&nbsp;&nbsp;属性价格&nbsp;&nbsp;<input type="text" notnull name="attr_price_list[]" value="' . (empty($val['Property_Price']) ? "0" : $val['Property_Price']) . '" size="5" maxlength="10" />' :
            ' <input type="hidden" name="attr_price_list[]" value="0" />';

        $html .= '</td></tr>';
    }
	

    $html .= '</table>';
	
    return $html;
}




/**
 * 获取属性列表
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_attr_list($DB,$Users_ID)
{
	
	$table_attribute = 'shop_property';
	$product_type = 'shop_property_type';
	
    $sql = "SELECT a.Property_ID, A.Type_ID, A.Property_Name ".
           " FROM " .$table_attribute. " AS a,  ".
           $product_type. " AS t ".
           " WHERE  a.Type_ID = t.Type_ID AND t.Status = 1 ".
           " And a.Users_ID= '".$Users_ID."'".
		   " And t.Users_ID= '".$Users_ID."'".
		   " ORDER BY a.Type_ID , a.Sort_Order";
    $rsAttributes = $DB->query($sql);
	$arr = $DB->toArray($rsAttributes);
    $list = array();

    foreach ($arr as $val)
    {
        $list[$val['Type_ID']][] = array($val['Property_ID']=>$val['Property_Name']);
    }
	
    return $list;
}
	
/**
 * 取得通用属性和某分类的属性，以及某商品的属性值
 * @param   int     $type_id     分类编号
 * @param   int     $products_id   商品编号
 * @return  array   规格与属性列表
 */
function get_attr_list_by_typeid($DB,$type_id, $products_id = 0)
{
    if (empty($type_id))
    {
        return array();
    }
	
	$table_attribute = 'shop_property';
	$product_attribute = 'shop_products_attr';
	
    // 查询属性值及商品的属性值
	if($products_id == 0){
		$sql = "SELECT Property_ID, Property_Name, Property_Input_Type, Property_Type, Property_Values".
            " FROM " .$table_attribute.
            " WHERE Type_ID = " . intval($type_id) ." OR Type_ID = 0".
            " ORDER BY Sort_Order, Property_Type, Property_ID";
	}else{
		$sql = "SELECT a.Property_ID, a.Property_Name, a.Property_Input_Type, a.Property_Type, a.Property_Values, v.Property_Value, v.Property_Price ".
            "FROM " .$table_attribute. " AS a ".
            "LEFT JOIN " .$product_attribute. " AS v ".
            "ON v.Property_ID = a.Property_ID AND v.products_id = '$products_id' ".
            "WHERE a.Type_ID = " . intval($type_id) ." OR a.Type_ID = 0 ".
            "ORDER BY a.Sort_Order, a.Property_Type, a.Property_ID,v.Product_Property_ID";
	
	}
	$rsProductAttrs = $DB->query($sql);
	
	if($rsProductAttrs){
		$result = $DB->toArray($rsProductAttrs);
	}else{
		$result = array();
		
	}

    return $result;
}
	
/**
 * 获得商品类型的列表
 *
 * @access  public
 * @param   integer     $selected   选定的类型编号
 * @return  string
 */
function shop_property_type_list($DB,$selected,$Users_ID){
	
    $sql = "SELECT Type_ID, Type_Name FROM  shop_property_type WHERE Status = 1 and Users_ID='".$Users_ID."'";
   
	$res = $DB->query($sql);
	$type_list = $DB->toArray($res);
	
    $lst = '';
    foreach($type_list as $key=>$row)
    {
        $lst .= "<option value='$row[Type_ID]'";
        $lst .= ($selected == $row['Type_ID']) ? ' selected="true"' : '';
        $lst .= '>' . htmlspecialchars($row['Type_Name']). '</option>';
    }
	

    return $lst;
}


/**
 *处理产品属性，产品后台信息编辑的时候属性如何
 *
 */
function deal_with_attr($DB,$product_id){
	
	$product_type   = !empty($_POST['Type_ID'])?$_POST['Type_ID']:0; 
	
	if ((isset($_POST['attr_id_list']) && isset($_POST['attr_value_list'])) || (empty($_POST['attr_id_list']) && empty($_POST['attr_value_list'])))
    {
        // 取得原有的属性值
        $products_attr_list = array();

        $sql = "SELECT p.*, A.Property_Type
                FROM  shop_products_attr AS p
                    LEFT JOIN shop_property AS a
                        ON a.Property_ID = p.Property_ID
                WHERE p.Products_ID = '$product_id'";

        $res = $DB->query($sql);
		$resulat_array = $DB->toArray($res); 
	
        foreach($resulat_array as $key=>$row)
        {
            $products_attr_list[$row['Property_ID']][$row['Property_Value']] = array('sign' => 'delete', 'product_attr_id' => $row['Product_Property_ID']);
        }
		
        // 循环现有的，根据原有的做相应处理
        if(isset($_POST['attr_id_list']))
        {
            foreach ($_POST['attr_id_list'] AS $key => $attr_id)
            {
                $attr_value = $_POST['attr_value_list'][$key];
                $attr_price = $_POST['attr_price_list'][$key];
               
			    if (!empty($attr_value))
                {
                    if (isset($products_attr_list[$attr_id][$attr_value]))
                    {
                        // 如果原来有，标记为更新
                        $products_attr_list[$attr_id][$attr_value]['sign'] = 'update';
                        $products_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                    else
                    {
                        // 如果原来没有，标记为新增
                        $products_attr_list[$attr_id][$attr_value]['sign'] = 'insert';
                        $products_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                    $val_arr = explode(' ', $attr_value);
                    
					
                }
            }
        }
		
		
	

        /* 插入、更新、删除数据 */
        foreach ($products_attr_list as $attr_id => $attr_value_list)
        {
            foreach ($attr_value_list as $attr_value => $info)
            {
                if ($info['sign'] == 'insert')
                {
                    $sql = "INSERT INTO shop_products_attr (Property_ID, Products_ID, Property_Value, Property_Price)".
                            "VALUES ('$attr_id', '$product_id', '$attr_value', '$info[attr_price]')";
                }
                elseif ($info['sign'] == 'update')
                {
                    $sql = "UPDATE shop_products_attr SET Property_Price = '$info[attr_price]' WHERE Product_Property_ID = '$info[product_attr_id]' LIMIT 1";
                }
                else
                {
                    $sql = "DELETE FROM shop_products_attr WHERE Product_Property_ID = '$info[product_attr_id]' LIMIT 1";
                }
				
                $DB->query($sql);
            }
        }
    }
}



/**
 * 获得商品的属性和规格
 *
 * @access  public
 * @param   integer $product_id
 * @return  array
 */
function get_product_properties($DB,$product_id)
{
	$product_type_table = 'shop_property_type';
	$products_table = 'shop_products';	
    $product_attr_table = 'shop_products_attr';
	$attr_table = 'shop_property';
	
	
    /* 获得商品的规格 */
    $sql = "SELECT a.Property_ID, a.Property_Name, a.Property_Type, ".
                "pa.Product_Property_Id, pa.Property_Value, pa.Property_Price " .
            'FROM ' . $product_attr_table . ' AS pa ' .
            'LEFT JOIN ' . $attr_table . ' AS a ON a.Property_ID = pa.Property_ID ' .
            "WHERE pa.Products_ID = '$product_id' " .
            'ORDER BY a.Sort_Order,pa.Product_Property_ID';


    $res = $DB->query($sql);
	$res_array = $DB->toArray($res);
	
    $arr['pro'] = array();     // 属性
    $arr['spe'] = array();     // 规格
    

    foreach ($res_array AS $row)
    {
		
		
        $row['Property_Value'] = str_replace("\n", '<br />', $row['Property_Value']);

        if ($row['Property_Type'] == 0)
        {
            $group = '属性';

            $arr['pro'][$group][$row['Property_ID']]['Name']  = $row['Property_Name'];
            $arr['pro'][$group][$row['Property_ID']]['Value'] = $row['Property_Value'];
        }
        else
        {
            $arr['spe'][$row['Property_ID']]['Property_Type'] = $row['Property_Type'];
            $arr['spe'][$row['Property_ID']]['Name']     = $row['Property_Name'];
            $arr['spe'][$row['Property_ID']]['Values'][] = array(
                                                        'label'        => $row['Property_Value'],
                                                        'price'        => $row['Property_Price'],
                                                        'format_price' => abs($row['Property_Price']),
                                                        'id'           => $row['Product_Property_Id']);
        }
	
		      
    }

    return $arr;
}	
	
function get_posterty_desc($DB,$attr_id_string,$Users_ID,$Products_ID){
  
  $fields = ' a.Property_ID,a.Property_Name,pa.Property_Value';
  $table = 'shop_property as a,shop_products_attr as pa';
  $condition = "a.Property_ID = pa.Property_ID and pa.Products_ID =".$Products_ID;
  $condition .= " and pa.Product_Property_ID in (".$attr_id_string.")";
  $condition .= " and a.Users_ID = '".$Users_ID."'";
  
  $sql = 'select '.$fields.' from '.$table.' where '.$condition;
  $rs = $DB->query($sql);
  $rsArray = $DB->toArray($rs);
  $attr_list = array();
  foreach($rsArray as $k=>$item){
	 if(isset($attr_list[$item['Property_ID']])){
	 	$attr_list[$item['Property_ID']]['Value'] .= ','.$item['Property_Value']; 
	 }else{
		$attr_list[$item['Property_ID']]['Name'] = $item['Property_Name'];
	 	$attr_list[$item['Property_ID']]['Value'] = $item['Property_Value'];
	 }

  }

  return $attr_list;

}

/**
 *去除产品属性
 */
function remove_product_attr($DB,$Products_ID){
	$DB->Del('shop_products_attr','Products_ID='.$Products_ID);
}

	

  