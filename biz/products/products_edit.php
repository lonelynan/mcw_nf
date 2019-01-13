<?php
require_once('../global.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/tools.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/lib_products.php');
$ProductsID = empty($_REQUEST['ProductsID']) ? 0 : $_REQUEST['ProductsID'];
$ifProducts = $DB->GetRs("shop_Products", "*", "where Users_ID='" . $rsBiz["Users_ID"] . "' and Biz_ID=" . $_SESSION["BIZ_ID"] . " and Products_ID=" . $ProductsID);
if (!$ProductsID || !$ifProducts) {
    echo '非法操作!';
    exit;
}
$rsConfig = $DB->GetRs("users_payconfig", "*", "where Users_ID='" . $_SESSION["Users_ID"] . "'");
if ($_POST) {
    if (!empty($_POST["TypeID"])) {
        $pos = array_search(min($_POST['Txt_PriceSon']), $_POST['Txt_PriceSon']);
        $posmax = array_search(max($_POST['Txt_PriceSon']), $_POST['Txt_PriceSon']);
        $imggo = 0;
        //============================================循环属性开始===================================================================
        global $DB;
        $attr = $DB->GetAssoc('shop_attribute', 'Attr_ID,Attr_Values', "where Type_ID = " . $_POST['TypeID']);
        foreach ($attr as $k => $v) {
            $lsattr[$v['Attr_ID']] = explode("\n", $v['Attr_Values']);
        }
        $arr = $_POST;
        $skuls = 0;
        foreach ($_POST['attr_id_list'] as $k => $v) {
            foreach ($_POST[$k . '_pro'] as $key => $val) {
                $skuls++;
                $arr[$k . '_pro'][$key] = $skuls;
                $arr['skujosn'][$v][$skuls] = preg_replace('/\r/', '', $lsattr[$k][$val]);
            }
        }
        $arr['skujosn'] = json_encode($arr['skujosn'], JSON_UNESCAPED_UNICODE);
        foreach ($_POST['attr_id_list'] as $k => $v) {
            $proArr[] = $arr[$k . '_pro'];
        }
        $proJsonKey = combination($proArr);
        $proKeyArr = [];
        foreach ($proJsonKey as $k => $v) {
            $str = '';
            if (is_array($v)) {
                foreach ($v as $key => $val) {
                    $str .= $val . ';';
                }
                $proKeyArr[$k] = substr($str, 0, -1);
            } else {
                $proKeyArr[$k] = $v;
            }
        }
        if (isset ($_POST['Txt_PriceG'])) {
            foreach ($_POST['Txt_PriceSon'] as $k => $v) {
                $proValue[$k] = ['Txt_PriceSon' => $_POST['Txt_PriceSon'][$k], 'Txt_NumberSon' => isset($_POST['Txt_NumberSon'][$k]) ? $_POST['Txt_NumberSon'][$k] : '', 'Txt_CountSon' => $_POST['Txt_CountSon'][$k], 'Txt_PriceG' => $_POST['Txt_PriceG'][$k]];
                if (isset($_POST['pt_pricex']) && !empty($_POST['pt_pricex'])) {
                    $proValue[$k] = array_merge($proValue[$k], ['Txt_PtPriceSon' => $_POST['pt_pricex'][$k], 'Txt_PtPriceX' => $_POST['pt_pricex'][$k]]);
                    if (isset($_POST['pt_prices']) && !empty($_POST['pt_prices'])) {
                        $proValue[$k] = array_merge($proValue[$k], ['Txt_PtPriceSon' => $_POST['pt_prices'][$k]]);
                    } else {
                        $proValue[$k] = array_merge($proValue[$k], ['Txt_PtPriceSon' => $_POST['pt_pricex'][$k]]);
                    }
                    if (!empty($_POST['flashsale_pricex'])) {
                        if (isset($_POST['xs_prices']) && !empty($_POST['xs_prices'])) {
                            $proValue[$k] = array_merge($proValue[$k], ['Txt_XsPriceSon' => $_POST['xs_prices'][$k]]);
                        } else {
                            $proValue[$k] = array_merge($proValue[$k], ['Txt_XsPriceSon' => $_POST['xs_pricex'][$k]]);
                        }
                    }
                }
            }
        } else {
            foreach ($_POST['Txt_PriceSon'] as $k => $v) {
                $proValue[$k] = ['Txt_PriceSon' => $_POST['Txt_PriceSon'][$k], 'Txt_NumberSon' => !empty($_POST['Txt_NumberSon']) ? $_POST['Txt_NumberSon'][$k] : 0, 'Txt_CountSon' => $_POST['Txt_CountSon'][$k], 'Txt_PriceG' => $_POST['Txt_PriceSon'][$k]];
                if (isset($_POST['pt_pricex']) && !empty($_POST['pt_pricex'])) {
                    $proValue[$k] = array_merge($proValue[$k], ['Txt_PtPriceSon' => $_POST['pt_pricex'][$k], 'Txt_PtPriceX' => $_POST['pt_pricex'][$k]]);
                    if (isset($_POST['pt_prices']) && !empty($_POST['pt_prices'])) {
                        $proValue[$k] = array_merge($proValue[$k], ['Txt_PtPriceSon' => $_POST['pt_prices'][$k]]);
                    } else {
                        $proValue[$k] = array_merge($proValue[$k], ['Txt_PtPriceSon' => $_POST['pt_pricex'][$k]]);
                    }
                }
                //限时 抢购

                if (isset($_POST['xs_pricex']) && !empty($_POST['xs_pricex'])) {
                    $proValue[$k] = array_merge($proValue[$k], ['Txt_PtPriceSon' => $_POST['xs_pricex'][$k]]);
                    $proValue[$k] = array_merge($proValue[$k], ['Txt_XsPriceSon' => $_POST['xs_pricex'][$k]]);
                }
            }
        }
        //判断拼团相关属性值
        foreach ($proValue as $key => $pro) {

            if (isset($pro['Txt_PtPriceSon']) && isset($_POST['is_pintuan']) && $_POST['is_pintuan'] == 1 ) {
                if ($_POST['pintuan_people'] < 2) {
                    die(json_encode([
                        'errorCode' => 6,
                        'msg' => '拼团最少人数为2人!'
                    ], JSON_UNESCAPED_UNICODE));
                }
                /*if ($_POST['pintuan_people'] == 2) {
                    if ($pro['Txt_PtPriceSon'] >= $pro['Txt_PriceSon'] * 0.9) {
                        die(json_encode([
                            'errorCode' => 3,
                            'msg' => '拼团人数为2人时,对应属性的拼团价格必须低于现价的90%'
                        ], JSON_UNESCAPED_UNICODE));
                    }
                } else if ($_POST['pintuan_people'] > 2) {
                    if ($pro['Txt_PtPriceSon'] >= $pro['Txt_PriceSon'] * 0.8) {
                        die(json_encode([
                            'errorCode' => 5,
                            'msg' => '拼团人数为2人时,对应属性的拼团价格必须低于现价的80%'
                        ], JSON_UNESCAPED_UNICODE));
                    }
                } else {
                    die(json_encode([
                        'errorCode' => 6,
                        'msg' => '拼团最少人数为2人!'
                    ], JSON_UNESCAPED_UNICODE));
                }*/
            }
        }
        foreach ($proKeyArr as $k => $v) {
            $skuvaljosn[$v] = $proValue[$k];
        }
        $arr['skuvaljosn'] = json_encode($skuvaljosn, JSON_UNESCAPED_UNICODE);
        $flagInit = 0;
        foreach ($_POST['attr_id_list'] as $k => $v) {
            $flagInit++;
            if ($flagInit == 1) {
                $imgfirstKey = $k;
            } else {
                continue;
            }
        }
        foreach ($_POST['attr_image'] as $k => $v) {
            if (!empty($v)) {
                $imggo = 1;
            }
        }
        if ($imggo == 1) {
            $imgkey = array_shift($_POST['attr_id_list']);
            $vchange = 1;
            foreach ($_POST[$imgfirstKey . '_pro'] as $k => $v) {
                $arr['skuimg'][$imgkey][$vchange] = $_POST['attr_image'][$v];
                $vchange++;
            }
            $arr['skuimg'] = json_encode($arr['skuimg'], JSON_UNESCAPED_UNICODE);
        }
        //注销掉部分无用变量
        foreach ($arr['attr_id_list'] as $k => $v) {
            unset($arr[$k . '_pro']);
        }
        unset($arr['attr_id_list'], $arr['attr_image'], $arr['Txt_PriceSon'], $arr['Txt_NumberSon'], $arr['Txt_CountSon'], $arr['Txt_PriceG']);
    }
    //===============================================循环属性结束=======================================================================
    if ($rsBiz['is_agree'] != 1 || $rsBiz['is_auth'] != 2) {
        die(json_encode([
            'errorCode' => 7,
            'msg' => '您还没完成商家入驻流程不能进行产品管理,点击确定立即完成',
            'url' => '/biz/authen/index.php'
        ], JSON_UNESCAPED_UNICODE));
    }
    if ($rsBiz['is_biz'] != 1) {
        die(json_encode([
            'errorCode' => 7,
            'msg' => '您还没完成商家入驻流程不能进行产品管理,点击确定立即完成',
            'url' => '/biz/authen/index.php'
        ], JSON_UNESCAPED_UNICODE));
    }
    if (!is_numeric($_POST["PriceY"])) {
        die(json_encode([
            'errorCode' => 9,
            'msg' => '产品原价请填写数字'
        ], JSON_UNESCAPED_UNICODE));
    }
    if (!is_numeric($_POST["PriceX"])) {
        die(json_encode([
            'errorCode' => 11,
            'msg' => '产品现价请填写数字'
        ], JSON_UNESCAPED_UNICODE));
    }
    if ($_POST["PriceY"] < $_POST["PriceX"]) {
        die(json_encode([
            'errorCode' => 13,
            'msg' => '产品原价不能小于产品现价'
        ], JSON_UNESCAPED_UNICODE));
    }
    if ($rsBiz['Is_Union'] == 0) {
        //处理产品属性
        $Category = array();
        if (!isset($_POST["Category"])) {
            die(json_encode([
                'errorCode' => 15,
                'msg' => '请选择分类！'
            ], JSON_UNESCAPED_UNICODE));
        }
        $Category_Id = !empty($_POST["Category"]) ? $_POST["Category"] : 0;
        if (empty($Category_Id)) {
            die(json_encode([
                'errorCode' => 17,
                'msg' => '请选择分类！'
            ], JSON_UNESCAPED_UNICODE));
        }
        $cateInfo = $DB->getRs("shop_category", "Category_ParentID,Category_Bond", "where Users_ID='" . $rsBiz["Users_ID"] . "' and Category_ID = " . $Category_Id);
        if ($cateInfo['Category_Bond'] > $rsBiz['bond_free']) {
            die(json_encode([
                'errorCode' => 19,
                'msg' => '您所交的保证金金额,不能在该分类下发布产品',
                'url' => '/biz/authen/charge_bond.php'
            ], JSON_UNESCAPED_UNICODE));
        }
        if (time() > $rsBiz['expiredate']) {
            die(json_encode([
                'errorCode' => 21,
                'msg' => '您的商家已经到期,请及时续费',
                'url' => '/biz/authen/charge_man.php'
            ], JSON_UNESCAPED_UNICODE));
        }
    }
    //拼团限制规则============================
    if (isset($_POST['is_pintuan']) && $_POST['is_pintuan'] == 1) {
        if (empty($_POST['pintuan_people']) || empty($_POST['pintuan_pricex']) || empty($_POST['pintuan_end_time'])) {
            die(json_encode([
                'errorCode' => 23,
                'msg' => '请填写完整的拼团参数!'
            ], JSON_UNESCAPED_UNICODE));
        }
        if ($_POST['pintuan_people'] < 2) {
            die(json_encode([
                'errorCode' => 29,
                'msg' => '拼团人数最少应为2人!'
            ], JSON_UNESCAPED_UNICODE));
        }
        /*if ($_POST['pintuan_people'] >= 2) {
            if ($_POST['pintuan_pricex'] >= $_POST['PriceX'] * 0.9) {
                die(json_encode([
                    'errorCode' => 25,
                    'msg' => '2人团的价格必须低于现价的90%!'
                ], JSON_UNESCAPED_UNICODE));
            }
            if ($_POST['pintuan_people'] >= 3) {
                if ($_POST['pintuan_pricex'] > $_POST['PriceX'] * 0.8) {
                    die(json_encode([
                        'errorCode' => 27,
                        'msg' => '多人团的价格必须低于现价的80%!'
                    ], JSON_UNESCAPED_UNICODE));
                }
            }
        } else {
            die(json_encode([
                'errorCode' => 29,
                'msg' => '拼团人数最少应为2人!'
            ], JSON_UNESCAPED_UNICODE));
        }*/
    }
    if ($_POST["ordertype"] == 0) {
        $isvirtual = 0;
        $isrecieve = 0;
    } elseif ($_POST["ordertype"] == 1) {
        $isvirtual = 1;
        $isrecieve = 0;
    } else {
        $isvirtual = 1;
        $isrecieve = 1;
    }
    if (strlen($_POST['TypeID']) > 0) {
        deal_with_attr($ProductsID);
    } else {
        remove_product_attr($ProductsID);
    }
    if (!isset($_POST["JSON"])) {
        die(json_encode([
            'errorCode' => 31,
            'msg' => '请上传商品图片'
        ], JSON_UNESCAPED_UNICODE));
    }
    if (count($_POST["Products_Parameter"]) > 20) {
        die(json_encode([
            'errorCode' => 33,
            'msg' => '最多添加20项产品参数'
        ], JSON_UNESCAPED_UNICODE));
    }
    //产品结算形式
    if ($rsBiz["Finance_Type"] == 0) {//该商家的结算方式若为按交易额提成
        $Data["Products_FinanceType"] = 0;
        $Data["Products_FinanceRate"] = $rsBiz["Finance_Rate"];
        $Data["Products_PriceS"] = number_format($_POST['PriceX'] * (1 - $rsBiz["Finance_Rate"] / 100), 2, '.', '');
    } else {
        if ($_POST["FinanceType"] == 0) {//商品按交易额比例
            if (!is_numeric($_POST["FinanceRate"]) || $_POST["FinanceRate"] <= 0) {
                die(json_encode([
                    'errorCode' => 35,
                    'msg' => '网站提成比例必须大于零！'
                ], JSON_UNESCAPED_UNICODE));
            }
            $Data["Products_FinanceType"] = 0;
            $Data["Products_FinanceRate"] = $_POST["FinanceRate"];
            $Data["Products_PriceS"] = number_format($_POST['PriceX'] * (1 - $_POST["FinanceRate"] / 100), 2, '.', '');
        } else {//商品按供货价
            if (!is_numeric($_POST["PriceS"]) || $_POST["PriceS"] <= 0 || $_POST["PriceS"] > $_POST['PriceX']) {
                die(json_encode([
                    'errorCode' => 37,
                    'msg' => '供货价格必须大于零，且小于售价！'
                ], JSON_UNESCAPED_UNICODE));
            }
            $Data["Products_FinanceType"] = 1;
            $rate = ($_POST['PriceX'] - $_POST["PriceS"]) * 100 / $_POST['PriceX'];
            $Data["Products_FinanceRate"] = number_format($rate, 2, '.', '');
            $Data["Products_PriceS"] = $_POST['PriceS'];
        }
    }
    $Data["Products_Name"] = $_POST['Name'];
    $Data["Products_Category"] = empty($_POST["Category"]) ? '0' : $_POST["Category"];
    $Data["Father_Category"] = !empty($cateInfo['Category_ParentID']) ? $cateInfo['Category_ParentID'] : 0;
    $Data["Products_PriceY"] = empty($_POST['PriceY']) ? "0" : $_POST['PriceY'];
    $Data["Products_PriceX"] = empty($_POST['PriceX']) ? "0" : $_POST['PriceX'];
    $Data["Products_PriceA"] = isset($pos) ? $_POST['Txt_PriceSon'][$pos] : 0.00;
    $Data["Products_PriceAmax"] = isset($posmax) ? $_POST['Txt_PriceSon'][$posmax] : 0.00;
    $Data["Products_Sales"] = empty($_POST['SoldCount']) ? "0" : $_POST['SoldCount'];
    $Data["Products_JSON"] = json_encode((isset($_POST["JSON"]) ? $_POST["JSON"] : array()), JSON_UNESCAPED_UNICODE);
    $Data["skujosn"] = isset($arr['skujosn']) ? $arr['skujosn'] : '';
    $Data["skuvaljosn"] = isset($arr['skuvaljosn']) ? $arr['skuvaljosn'] : '';
    $Data["skuimg"] = isset($arr['skuimg']) ? $arr['skuimg'] : '';
    $Data["tableeval"] = $_POST["tableeval"];
    $Data["tableevals"] = $_POST["tableevals"];
    $Data["Products_BriefDescription"] = htmlspecialchars($_POST['BriefDescription'], ENT_QUOTES);
    $Data["Products_Description"] = htmlspecialchars($_POST['Description'], ENT_QUOTES);
    $Data["Products_Type"] = empty($_POST["TypeID"]) ? 0 : $_POST["TypeID"];
    $Data["Products_Count"] = empty($_POST["Count"]) ? 10000 : intval($_POST["Count"]);
    $Data["Products_Weight"] = empty($_POST["Weight"]) ? 0 : $_POST["Weight"];
    $Data["Products_IsVirtual"] = $isvirtual;
    $Data["Products_IsRecieve"] = $isrecieve;
    $Data["Products_Status"] = 0;
    $Data["Products_SoldOut"] = isset($_POST["SoldOut"]) ? $_POST["SoldOut"] : 0;
    $Data["Products_IsShippingFree"] = isset($_POST["IsShippingFree"]) ? $_POST["IsShippingFree"] : 0;
    $Data["Products_Parameter"] = json_encode(empty($_POST["Products_Parameter"]) ? 0 : $_POST["Products_Parameter"], JSON_UNESCAPED_UNICODE);
    $Data["Products_sort"] = empty($_POST["sort"]) ? 100 : $_POST["sort"];
    $Data['Is_BizStores'] = isset($_POST["Is_BizStores"]) ? $_POST["Is_BizStores"] : 0;
    //拼团参数
    $Data["pintuan_flag"] = isset($_POST['is_pintuan']) ? (int)$_POST['is_pintuan'] : 0;
    $Data["pintuan_people"] = isset($_POST['pintuan_people']) ? (int)$_POST['pintuan_people'] : 0;
    $Data["pintuan_start_time"] = time();
    $Data["pintuan_end_time"] = isset($_POST['pintuan_end_time']) ? strtotime($_POST['pintuan_end_time']) : 0;
    $Data["pintuan_pricex"] = !empty($_POST['pintuan_pricex']) ? $_POST['pintuan_pricex'] : 0;
    $Data["pintuan_prices"] = !empty($_POST['pintuan_prices']) ? $_POST['pintuan_prices'] : 0;
    //限时抢购参数
    $Data["flashsale_flag"] = isset($_POST['is_flashsale']) ? (int)$_POST['is_flashsale'] : 0;
    $Data["flashsale_pricex"] = !empty($_POST['flashsale_pricex']) ? $_POST['flashsale_pricex'] : 0;
    $Data["Rect_Img"] = !empty($_POST['Rect_Img']) ? $_POST['Rect_Img'] : ''; //获得产品首页展示图片
    //商家店铺相关,只有商家拥有带你普时才可有此设置
    if ($IsStore == 1) {
        $Data["Products_BizCategory"] = empty($_POST["BizCategory"]) ? 0 : $_POST["BizCategory"];
        $Data["Products_BizIsNew"] = isset($_POST["BizIsNew"]) ? $_POST["BizIsNew"] : 0;
        $Data["Products_BizIsHot"] = isset($_POST["BizIsHot"]) ? $_POST["BizIsHot"] : 0;
        $Data["Products_BizIsRec"] = isset($_POST["BizIsRec"]) ? $_POST["BizIsRec"] : 0;
    }
    //查询该商品绑定卡密状态是否更改，
    $isHasRelation = $DB->GetRs("shop_virtual_card", "Card_Id", "where User_ID='" . $rsBiz["Users_ID"] . "' and Products_Relation_ID=" . $ProductsID);
    $relationData = array('Products_Relation_ID' => 0);
    $DB->Set("shop_virtual_card", $relationData, "where User_ID='" . $rsBiz["Users_ID"] . "' and Products_Relation_ID=" . $ProductsID);
    if ($_POST["ordertype"] == 2 && !empty($_POST['cardids'])) {
        $newrelationData = array('Products_Relation_ID' => $ProductsID);
        $Card_Id_List = rtrim($_POST['cardids'], ',');
        $DB->Set("shop_virtual_card", $newrelationData, "where User_ID='" . $rsBiz["Users_ID"] . "' and Card_Id IN(" . $Card_Id_List . ") ");
    }
    $Flag = $DB->Set("shop_Products", $Data, "where Users_ID='" . $rsBiz["Users_ID"] . "' and Biz_ID=" . $_SESSION["BIZ_ID"] . " and Products_ID=" . $ProductsID);
    $biz_array = array();
    if ($_POST["PriceX"] < $rsBiz["Biz_MinPrice"] || $rsBiz["Biz_MinPrice"] == 0) {
        $biz_array["Biz_MinPrice"] = $_POST["PriceX"];
    }
    if ($_POST["PriceX"] > $rsBiz["Biz_MaxPrice"]) {
        $biz_array["Biz_MaxPrice"] = $_POST["PriceX"];
    }
    if (count($biz_array) > 0) {
        $DB->set("biz", $biz_array, "where Users_ID='" . $rsBiz["Users_ID"] . "' and Biz_ID=" . $_SESSION["BIZ_ID"]);
    }
    if ($Flag) {
        die(json_encode([
            'errorCode' => 0,
            'msg' => '修改成功',
            'url' => 'products.php'
        ], JSON_UNESCAPED_UNICODE));
    } else {
        die(json_encode([
            'errorCode' => -1,
            'msg' => '保存失败，请重试！'
        ], JSON_UNESCAPED_UNICODE));
    }
} else {
    $rsProducts = $DB->GetRs("shop_Products", "*", "where Users_ID='" . $rsBiz["Users_ID"] . "' and Biz_ID=" . $BizID . " and Products_ID=" . $ProductsID);
		
    $JSON = json_decode($rsProducts['Products_JSON'], true);
	//属性个数
    if (empty($rsProducts['skujosn'])) {
        $inum = 0;
    } else {
        $inumarr = json_decode($rsProducts['skujosn'], true);
        $inum = count($inumarr);
    }
	//产品是否有供货价
    if ($rsProducts["Products_FinanceType"] == 1) {
        $isgh = 1;
    } else {
        $isgh = 0;
    }
    $parameter_list = json_decode($rsProducts['Products_Parameter'], true);  //参数
    $parameter_nums = count($parameter_list);
    //获取绑定卡密列表
    $rsCard = $DB->Get("shop_virtual_card", "Card_Id", "where User_ID='" . $rsBiz["Users_ID"] . "' and Products_Relation_ID=" . $ProductsID);
    while ($r = $DB->fetch_assoc()) {
        $List[] = $r;
    }
    if (!empty($List)) {
        foreach ($List as $k => $v) {
            $ListStr[] = $v['Card_Id'];
        }
    }
    //商品属性的html
    if (!empty($rsProducts['Products_Type'])) {
        $FinanceType = $rsProducts['Products_FinanceType'];
        $product_attrs = $rsProducts['tableevals'];
        $product_attr_html = $rsProducts['tableeval'];
        $hiddendis = 1;
    } else {
        $product_attrs = '';
        $product_attr_html = '';
        $hiddendis = 0;
    }
    $catids = substr($rsProducts["Products_Category"], 1, -1);
    $Categorys = explode(",", $catids);
    $ordertype = 0;
    if ($rsProducts["Products_IsVirtual"] == 1 && $rsProducts["Products_IsRecieve"] == 1) {
        $ordertype = 2;
    } elseif ($rsProducts["Products_IsVirtual"] == 1) {
        $ordertype = 1;
    }
    $cate_name = array();
	$result = $capsule->table('shop_category')->where(['Users_ID' => $rsBiz["Users_ID"], 'Category_ID' => $rsProducts['Products_Category']])->orderBy('Category_ParentID', 'asc')->orderBy('Category_Index', 'asc')->get();
	if(!empty($result)){
		foreach($result as $k => $v){
			if ($v["Category_ParentID"] == 0 && empty($cate_name[$v["Category_ID"]])) {
				$cate_name[$v["Category_ID"]] = $v["Category_Name"];
			} else {
				$cate_name[$v["Category_ParentID"]]["child"][] = $v["Category_Name"];
			}
		}
	}
	
    $shop_category = [];
	/************* 获取商城分类 start **************/
	$result = $capsule->table('shop_category')->where(['Users_ID' => $rsBiz["Users_ID"]])->orderBy('Category_ParentID', 'asc')->orderBy('Category_Index', 'asc')->get();
	if(!empty($result)){
		foreach($result as $k => $v){
			if ($v["Category_ParentID"] == 0) {
				$shop_category[$v["Category_ID"]] = $v;
			} else {
				$shop_category[$v["Category_ParentID"]]["child"][] = $v;
			}
		}
	}
	/************* 获取商城分类 end **************/
	
	/************* 获取自定义商家的分类 start **************/
	$result = $capsule->table('biz_category')->where(['Users_ID' => $rsBiz["Users_ID"], 'Biz_ID' => $BizID])->orderBy('Category_ParentID', 'asc')->orderBy('Category_Index', 'asc')->get();
	$diy_cate = [];
	if(!empty($result)){
		foreach($result as $k => $v){
			if ($v["Category_ParentID"] == 0) {
				$diy_cate[$v["Category_ID"]] = $v;
			} else {
				$diy_cate[$v["Category_ParentID"]]["child"][] = $v;
			}
		}
	}
	/************* 获取自定义商家的分类 end **************/
	
	//获取属性列表
	$attrList = $capsule->table('shop_product_type')->where(['Users_ID' => $rsBiz["Users_ID"], 'Biz_ID' => $BizID])->where('attrnum', '<>',0)->orderBy('Type_Index', 'asc')->get(['Type_ID', 'Type_Name']);
}

$jcurid = 1;
$subidst = 11;
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css?t=1' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
    <script src="/static/js/plugin/laydate/laydate.js"></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
    <script type='text/javascript' src='/static/member/js/products_attr_helper.js'></script>
    <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css"/>
    <script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
    <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
    <script>
        var BIZ_ID = <?=$BizID ?>;
        var isgh = <?= $isgh ?>;
        var changeok = 3;
        var inum = <?= $inum ?>;
        //产品参数
        var index = <?= $parameter_nums - 1 ?>;
        var is_Tj = 0;
		var is_xs = 0;
        var is_pt = <?= !empty($rsProducts['pintuan_flag']) ? 1 : 0 ?>;
        var productId = "<?= $ProductsID ?>";
    </script>
    <script type='text/javascript' src='/biz/js/shop.js'></script>
    <script type='text/javascript' src='/biz/js/products.js'></script>
    <style type="text/css">
        .dislevelcss {
            float: left;
            margin: 5px 0px 0px 8px;
            text-align: center;
            border: solid 1px #858585;
            padding: 5px;
        }
        .dislevelcss th {
            border-bottom: dashed 1px #858585;
            font-size: 16px;
        }
        .attr_group {
            float: left;
            margin-top: 4px;
        }
        .img {
            float: left;
            margin-left: 5px;
        }
        .img img {
            width: 28px;
            height: 28px;
        }
        .LogoDetail img {
            width: 28px;
            height: 28px;
        }
        .msg {
            margin-left: 10px;
            color: #f00;
        }
		.color_888 { color:#888; }
		.none { display:none; }
    </style>
</head>
<body>
<!--[if lte IE 9]>
<script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css'/>
        <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css'/>
        <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script>
        <?php require_once($_SERVER["DOCUMENT_ROOT"] . '/biz/products/bizshop_menubar.php'); ?>
        <div id="products" class="r_con_wrap">
            <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css'/>
            <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
            <form class="r_con_form skipForm" id="product_edit_form" method="post" action="products_edit.php">
                <div class="rows">
                    <label>产品名称</label>
                    <span class="input">
                        <input type="text" name="Name" id="Name" value="<?= $rsProducts["Products_Name"] ?>" class="form_input" size="35" maxlength="100" notnull/>
                        <font class="fc_red">*</font>
                    </span>
                    <div class="clear"></div>
                </div>
                <?php if ($rsBiz['Is_Union'] != 110) { ?>
                    <div class="rows">
                        <label>商城分类</label>
                        <span class="input">
				            <a href="javascript:void(0);" class="select_category">[选择分类]</a><font class="fc_red">*</font><br/>
                            <p style="margin:5px 0px; padding:0px; font-size:12px; color:#999">已选择分类：</p>
				            <div id="classs">
                                <?php foreach ($cate_name as $value) { ?>
                                    <p style="margin:5px 0px; padding:0px; font-size:12px; color:#666">
                                        <?php if (!empty($value["name"])) { ?>
                                            <font style="color:#333; font-size:12px;"><?= $value["name"] ?></font>
                                        <?php } ?>
                                        <?php if (!empty($value["child"])) { ?>
                                            <?php foreach ($value["child"] as $v) { ?>
                                                &nbsp;&nbsp;<?= $v ?>
                                            <?php }
                                        } ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </span>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
                <div class="rows">
                    <label>销量</label>
                    <span class="input">
                        <input type="text" name="SoldCount" value="<?= $rsProducts["Products_Sales"] ?>" class="form_input" size="5" maxlength="10"/>
                    </span>
                    <div class="clear"></div>
                </div>
                <?php if ($IsStore == 1) { ?>
                    <div class="rows">
                        <label>商家自定义分类</label>
                        <span class="input">
                            <select name="BizCategory" style="width:180px;" notnulll>
                            <?php
							if(!empty($diy_cate)){
								foreach ($diy_cate as $key => $value) {
							?>
							<option value="<?=$value["Category_ID"] ?>" <?=$rsProducts["Products_BizCategory"] == $value["Category_ID"] ? ' selected' : '' ?>><?=$value["Category_Name"] ?></option>
                            <?php
									if (!empty($value["child"])) {
										foreach ($value["child"] as $v) {
							?>
								<option value="<?=$v["Category_ID"] ?>" <?=$rsProducts["Products_BizCategory"] == $v["Category_ID"] ? ' selected' : '' ?>>└<?=$v["Category_Name"] ?></option>
                                         
							<?php
										}
									}
								} 
							}else{
							 ?>
							 <option value="0">默认分类</option>
							 <?php 
							 }
							 ?>
                            </select>
                        </span>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
                <div class="rows">
                    <label>产品价格</label>
                    <span class="input price">
                        原价:￥
                        <input type="text" name="PriceY" id="PriceY" value="<?= $rsProducts["Products_PriceY"] ?>" class="form_input" size="5" maxlength="10" notnull/><font class="fc_red">*</font>
                        &nbsp;&nbsp;现价:￥
                        <input type="text" name="PriceX" id="PriceX" value="<?= $rsProducts["Products_PriceX"] ?>" onblur="check_flashsale_pricex()" class="form_input" size="5" maxlength="10" notnull/> <font class="fc_red">*</font>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>产品排序</label>
                    <span class="input price">
                        <input type="text" name="sort" id="sort" value="<?= $rsProducts["Products_sort"] ?>" class="form_input" size="5" maxlength="10" />数字越小越靠前，默认为100
                    </span>
                    <div class="clear"></div>
                </div>
                <!--限时抢购逻辑开始-->
                <?php if(!empty($flashsale)){?>
                <div class="rows">
                    <label>是否参与限时抢购</label>
                    <span class="input">
                        <input type="checkbox" name="is_flashsale" value="1" id="is_flashsale" <?= $rsProducts["flashsale_flag"] ? 'checked' : '' ?>>&nbsp;&nbsp;<span class="color_888">是否参与限时抢购</span>
                        <span class="color_888 none" id="flashsale_time">
                            &nbsp;&nbsp;抢购价格:&nbsp;&nbsp;<input type="text" class="form_input" name="flashsale_pricex"  id="flashsale_pricex" onblur="check_flashsale_pricex()" placeholder="请输入限时抢购价格" value="<?= $rsProducts['flashsale_pricex'] ?>"/>
                        </span>
                    </span>
                    <div class="clear"></div>
                </div>
                <?php }?>
                <!--限时抢购逻辑结束-->

                <!--拼团逻辑开始-->
                <div class="rows">
                    <label>是否参与拼团</label>
                    <span class="input">
                        <input type="checkbox" name="is_pintuan" value="1" id="is_pintuan" <?= $rsProducts["pintuan_flag"] == 1 ? 'checked' : '' ?>>&nbsp;&nbsp;<span class="color_888">是否参与拼团</span>
                        <span class="color_888 <?= $rsProducts["pintuan_flag"] ? '' : 'none' ?>" id="pintuan_time">
                            &nbsp;&nbsp;拼团人数:&nbsp;&nbsp;<input type="text" class="form_input" name="pintuan_people" placeholder="请输入拼团人数" value="<?= $rsProducts['pintuan_people'] ?>"/>
                            &nbsp;&nbsp;
							&nbsp;&nbsp;<span id="pt_price">拼团价格:&nbsp;&nbsp;<input type="text" class="form_input" name="pintuan_pricex" placeholder="请输入拼团价格" value="<?= $rsProducts['pintuan_pricex'] ?>"/></span>
                              
							
                            &nbsp;
							
							<span id="ptG" class="<?=$rsProducts["pintuan_flag"] == 1 && $rsProducts["Products_FinanceType"] == 1 ? '' : 'none' ?>">&nbsp;拼团供货价:&nbsp;&nbsp;<input id="pt_inp" type="text" class="form_input" name="pintuan_prices" placeholder="请输入拼团供货价" value="<?= $rsProducts['pintuan_prices'] ?>"/></span>
							
                            &nbsp;&nbsp;截止时间:&nbsp;&nbsp;
                            <input placeholder="截止时间" class="laydate-icon" name="pintuan_end_time" value="<?= $rsProducts['pintuan_end_time'] ? date('Y-m-d', $rsProducts['pintuan_end_time']) : date("Y-m-d", strtotime('+12 month')) ?>" onclick="laydate()">
                        </span>
                    </span>
                    <div class="clear"></div>
                </div>
                <!--拼团逻辑结束-->
                <?php if ($rsBiz["Finance_Type"] == 1) { ?>
                    <div class="rows">
                        <label>财务结算类型</label>
                        <span class="input">
                            <input type="radio" name="FinanceType" value="0" id="FinanceType_0" <?= $rsProducts["Products_FinanceType"] == 0 ? ' checked' : '' ?>  /><label for="FinanceType_0"> 按交易额比例</label>&nbsp;&nbsp;
                            <input type="radio" name="FinanceType" value="1" id="FinanceType_1" <?= $rsProducts["Products_FinanceType"] == 1 ? ' checked' : '' ?>/><label for="FinanceType_1"> 按供货价</label><br/>
                            <span class="tips">注：若按交易额比例，则网站提成为：产品售价*比例%</span>
                        </span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows" id="FinanceRate"<?= $rsProducts["Products_FinanceType"] == 1 ? ' style="display:none"' : '' ?>>
                        <label>网站提成</label>
                        <span class="input">
                            <input type="text" name="FinanceRate" value="<?= $rsProducts["Products_FinanceRate"] ?>" class="form_input" size="10"/> %
                        </span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows" id="PriceS"<?= $rsProducts["Products_FinanceType"] == 0 ? ' style="display:none"' : '' ?>>
                        <label>供货价</label>
                        <span class="input">
                            <input type="text" name="PriceS" id="Products_PriceS" value="<?= $rsProducts["Products_PriceS"] ?>" class="form_input" size="10"/> 元
                        </span>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
                <div class="rows">
                    <label>产品重量</label>
                    <span class="input">
                        <input type="text" name="Weight" id="Products_Weight" value="<?= $rsProducts["Products_Weight"] ?>" notnull class="form_input" size="5"/>&nbsp;&nbsp;千克
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>产品图片</label>
                    <span class="input">
                        <span class="upload_file">
                            <div>
                                <div class="up_input">
                                    <input type="button" id="ImgUpload" value="添加图片" style="width:80px;"/>
                                </div>
                                <div class="tips">共可上传<span id="pic_count">5</span>张图片，图片大小建议：640*640像素</div>
                                <div class="clear"></div>
                            </div>
                        </span>
                        <div class="img" id="PicDetail">
                            <?php if (isset($JSON["ImgPath"])) {
                                foreach ($JSON["ImgPath"] as $key => $value) {
                                    ?>
                                    <div>
                                        <a target="_blank" href="<?= $value ?>"> <img src="<?= $value ?>"></a>
                                        <span>删除</span>
                                        <input type="hidden" name="JSON[ImgPath][]" value="<?= $value ?>">
                                    </div>
                                <?php }
                            } ?>
                        </div>
                    </span>
                    <div class="clear"></div>
                </div>


                <div class="rows">
                    <label>产品首页展示图</label>
                    <span class="input">
                        <span class="upload_file">
                            <div>
                                <div class="up_input">
                                    <input type="button" id="Rect_ImgUpload" value="添加图片" style="width:80px;"/>
                                </div>
                                <div class="tips">共可上传<span id="pic_count">1</span>张图片，图片大小建议：710*300像素</div>
                                <div class="clear"></div>
                            </div>
                        </span>
                         <div class="img" id="Rect_PicDetail">
                        <?php if (isset($rsProducts["Rect_Img"]) && $rsProducts['Rect_Img'] != '') {                                ?>
                            <div>
                                    <a target="_blank" href="<?= $rsProducts["Rect_Img"] ?>"> <img src="<?= $rsProducts["Rect_Img"] ?>"></a>
                                    <span>删除</span>
                                    <input type="hidden" name="Rect_Img" value="<?= $rsProducts["Rect_Img"] ?>">
                                </div>
                            <?php
                        } ?>
                    </div>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>简短介绍</label>
                    <span class="input">
                        <textarea name="BriefDescription" class="briefdesc"><?= $rsProducts["Products_BriefDescription"] ?></textarea>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows" id="type_html">
                    <label>产品类型</label>
                    <span class="input">
                        <select name="TypeID" style="width:180px;" id="Type_ID">
                            <option value="0"<?= $rsProducts["Products_Type"] == 0 ? " selected" : "" ?>>无属性</option>
                            <?php
                            $DB->get("shop_product_type", "*", "where Users_ID='" . $rsBiz["Users_ID"] . "' and Biz_ID=" . $_SESSION["BIZ_ID"] . " and attrnum!=0 order by Type_Index asc");
                            while ($rsType = $DB->fetch_assoc()) {
                                echo '<option value="' . $rsType["Type_ID"] . '"' . ($rsProducts["Products_Type"] == $rsType["Type_ID"] ? " selected" : "") . '>' . $rsType["Type_Name"] . '</option>';
                            }
                            ?>
                        </select>
                        <font class="fc_red">*</font>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows" id="attr_show" style="<?= $rsProducts["Products_Type"] == 0 ? "display:none" : ""; ?>">
                    <label>产品属性</label>
                    <span class="input" id="attrs"><?= $product_attrs ?></span>
                    <div class="clear"></div>
                </div>
                <div class="rows" id="attr_table" style="<?= $rsProducts["Products_Type"] == 0 ? "display:none" : ""; ?>">
                    <label>属性表格</label>
                    <span class="input" id="skutableval"><?= $product_attr_html ?></span>
                    <div class="clear"></div>
                </div>
                <div class="rows" id="muliteadd" style="<?= $hiddendis ? '' : 'display:none' ?>">
                    <label>批量添加</label>
                    <span class="input" id="muliteaddval">
			            <label style="width:60px">价格批量填充：</label>
                        <input type="text" class="Attr_Name form_input" name="add_PriceSon" value="" class="form_input" size="15" maxlength="30" placeholder="点后面加号"/>&nbsp;<span style="cursor: pointer" class="icon-plusPrice">[+]</span>&nbsp;&nbsp;&nbsp;
			            <label style="width:60px">库存批量填充：</label>
                        <input type="text" class="Attr_Name form_input" name="add_CountSon" value="" class="form_input" size="15" maxlength="30" placeholder="点后面加号"/>&nbsp;<span style="cursor: pointer" class="icon-plusCount">[+]</span>&nbsp;&nbsp;&nbsp;
                        <span id="plgh" style="<?= $rsProducts["Products_FinanceType"] == 1 ? ' ' : 'display: none;' ?>">
			            <label style="width:60px">供货价批量填充：</label>
                        <input type="text" class="Attr_Name form_input" name="add_PriceG" value="" class="form_input" size="15" maxlength="30" placeholder="点后面加号"/>&nbsp;<span style="cursor: pointer" class="icon-plusPriceG">[+]</span>
                        </span>

                        <span id="addPT" style="<?= $rsProducts["pintuan_flag"] == 1 ? ' ' : 'display: none;' ?>">
			            <label style="width:60px">拼团价批量填充：</label>
                        <input type="text" class="Attr_Name form_input" name="add_PTPrice" value="" class="form_input" size="15" maxlength="30" placeholder="点后面加号"/>&nbsp;<span style="cursor: pointer" class="icon-plusPtPrice">[+]</span>
                        </span>

                         <span id="addPTG" style="<?= $rsProducts["pintuan_flag"] == 1 ? ' ' : 'display: none;' ?>">
			            <label style="width:60px">拼团供货价批量填充：</label>
                        <input type="text" class="Attr_Name form_input" name="add_PTPriceG" value="" class="form_input" size="15" maxlength="30" placeholder="点后面加号"/>&nbsp;<span style="cursor: pointer" class="icon-plusPtPriceG">[+]</span>
                        </span>
                    </span>
                    <div class="clear"></div>
                </div>
                <?php if (!empty($parameter_list)) {
                    foreach ($parameter_list as $kk => $vv) { ?>
                        <?php if ($kk == 0) { ?>
                            <div class="rows form-group">
                                <label>产品参数</label>
                                <span class="input">
                                    <input type="text" class="ProductsParametername" name="Products_Parameter[<?= $kk ?>][name]" value="<?= $vv["name"] ?>" class="form_input" size="15" maxlength="30"/>
                                    <input type="text" class="ProductsParametervalue" name="Products_Parameter[<?= $kk ?>][value]" value="<?= $vv["value"] ?>" class="form_input" size="15" maxlength="30"/>
                                    <span style="cursor: pointer" class="icon-plus addkfval">[+]</span><br>
                                </span>
                                <div class="clear"></div>
                            </div>
                        <?php } else { ?>
                            <div class="rows form-group">
                                <label>产品参数</label>
                                <span class="input">
                                    <input type="text" class="ProductsParametername" name="Products_Parameter[<?= $kk ?>][name]" value="<?= $vv["name"] ?>" class="form_input" size="15" maxlength="30" notnull/>
                                    <input type="text" class="ProductsParametervalue" name="Products_Parameter[<?= $kk ?>][value]" value="<?= $vv["value"] ?>" class="form_input" size="15" maxlength="30" notnull/>
                                    <span style="cursor: pointer" class="icon-minus addkfval">[+]</span><br>
                                </span>
                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                    <?php }
                } else { ?>
                    <div class="rows form-group">
                        <label>产品参数</label>
                        <span class="input">
                            <input type="text" class="ProductsParametername" name="Products_Parameter[0][name]" value="" class="form_input" size="15" maxlength="30" notnull/>
                            <input type="text" class="ProductsParametervalue" name="Products_Parameter[0][value]" value="" class="form_input" size="15" maxlength="30" notnull/>
                            <span style="cursor: pointer" class="icon-plus addkfval">[+]</span><br>
                        </span>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
                <div class="rows">
                    <label>其他属性</label>
                    <span class="input attr">
		                <label><input type="checkbox" value="1" name="SoldOut" <?= empty($rsProducts["Products_SoldOut"]) ? "" : " checked" ?> /> 下架</label> &nbsp;|&nbsp;
                        <label><input type="checkbox" value="1" name="IsShippingFree" <?= empty($rsProducts["Products_IsShippingFree"]) ? "" : " checked" ?> /> 免运费</label>
                        <?php if ($IsStore == 1) { ?>
                            &nbsp;|&nbsp;<label><input type="checkbox" value="1" name="BizIsNew" <?= empty($rsProducts["Products_BizIsNew"]) ? "" : " checked" ?> /> 新品</label> &nbsp;|&nbsp;
<!--                            <label><input type="checkbox" value="1" name="BizIsHot" --><?//= empty($rsProducts["Products_BizIsHot"]) ? "" : " checked" ?><!-- /> 热卖</label> &nbsp;|&nbsp;-->
                            <label><input type="checkbox" value="1" name="BizIsRec" <?= empty($rsProducts["Products_BizIsRec"]) ? "" : " checked" ?> /> 推荐</label> <span class="tips">(新品、推荐在商家店铺主页的相应位置显示)</span>
                        <?php } ?>
                    </span>
                    <div class="clear"></div>
                </div>

                <div class="rows">
                    <label>订单流程</label>
                    <span class="input" style="font-size:12px; line-height:22px;">
                        <input type="radio" id="order_0" value="0" name="ordertype" <?= $ordertype == 0 ? ' checked' : '' ?> /><label for="order_0"> 实物订单&nbsp;&nbsp;( 买家下单 -> 买家付款 -> 商家发货 -> 买家收货 -> 订单完成 ) </label><br/>
                        <input type="radio" id="order_1" value="1" name="ordertype" <?= $ordertype == 1 ? ' checked' : '' ?> /><label for="order_1"> 虚拟订单&nbsp;&nbsp;( 买家下单 -> 买家付款 -> 系统发送消费券码到买家手机 -> 商家认证消费 -> 订单完成 ) </label><br/>
                        <input type="radio" id="order_2" value="2" name="ordertype" <?= $ordertype == 2 ? ' checked' : '' ?> /><label for="order_2"> 其他&nbsp;&nbsp;( 买家下单 -> 买家付款 -> 订单完成 ) </label>
                    </span>
                    <div class="clear"></div>
                </div>

                <!--是否支持到店自提开始-->
                <?php if(!empty($rsBiz['Is_BizStores']) == 1 && $rsBiz['Is_Union'] == 1){?>
                    <div class="rows" id="Come_Stores" style="display: <?=$ordertype == 0 ? 'block' : 'none' ;?>;">
                        <label>是否支持到店自提</label>
                        <span class="input">
                        <input type="checkbox" name="Is_BizStores" value="1" <?=$rsProducts['Is_BizStores'] == 1 ? 'checked' : '' ?>>&nbsp;&nbsp;<span style="color:#888">是否支持到店自提</span>
                    </span>
                        <div class="clear"></div>
                    </div>
                <?php }?>
                <!--是否支持到店自提结束-->

                <div class="rows">
                    <label>库存</label>
                    <span class="input">
                        <input type="text" name="Count" id="totalCount" value="<?= $rsProducts["Products_Count"] ?>" class="form_input" size="5" maxlength="10"/> <span class="tips">&nbsp;注:若不限则填写10000.</span>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>详细介绍</label>
                    <span class="input">
                        <textarea class="ckeditor" name="Description" style="width:700px; height:300px;"><?= $rsProducts["Products_Description"] ?></textarea>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label></label>
                    <span class="input">
                        <input type="button" class="btn_green" name="submit_button" value="提交保存"/>
                    </span>
                    <div class="clear"></div>
                </div>
				<input type="hidden" name="Products_Union_ID" value="<?=$rsBiz['Is_Union'] ?>">
				<?php if(!$rsBiz["Finance_Type"]){ ?>
				<!-- 拼团供货价 start-->
				<input id="pt_inp" type="hidden" name="pintuan_prices" value="0"/>
				<!-- 拼团供货价 end-->
				<?php } ?>
				<input type="hidden" name="tableevals" id="tableevals"/>
				<input type="hidden" name="tableeval" id="tableeval"/>
				<input type="hidden" name="Category" id="classsid" value="<?= $rsProducts['Products_Category'] ?>"/>
				<input type='hidden' value='' id='cardids' name='cardids'/>
				<input type="hidden" id="UsersID" value="<?= $rsBiz["Users_ID"] ?>"/>
				<input type="hidden" name="ProductsID" id="ProductsID" value="<?= $rsProducts["Products_ID"] ?>">
            </form>
        </div>
    </div>
</div>
</body>
</html>