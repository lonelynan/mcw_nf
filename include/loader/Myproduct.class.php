<?php
class Myproduct extends BaseLoader
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * 修改产品库存，及上下架状态
     * @param $rsProduct        //产品详情
     * @param $update_data      //需更新的数据
     * @return bool
     */
    private function productStockSoldOut($rsProduct, $update_data)
    {
        $Products_ID = $rsProduct['Products_ID'];

        //修改商品上下架状态 Products_SoldOut = 0为上架     1下架
        if (isset($update_data['Products_SoldOut'])) {
            unset($update_data['Products_SoldOut']);
        }
        //更新上下架接口要在先执行
        $flag = $this->db->table('shop_products')->where('Products_ID', $Products_ID)->update($update_data);

        return $flag;
    }

    /**
     * 提交订单、退订单 更新库存
     * @param $CartList     //购买产品信息
     * @param $act          //操作  add: 提交订单   del: 删除订单
     * @return bool
     */
    private function orderStock($CartList, $act)
    {
        if (empty($CartList) || !in_array($act, ['add', 'del'])) return false;

        foreach ($CartList as $ProductsID => $value) {
			$rsProduct = $this->db->table('shop_products')->where('Products_ID', $ProductsID)->first();
            if (empty($rsProduct)) {
                if ($act == 'add') {
                    return false;
                } else if ($act == 'del') {
                    continue;
                }
            }

            $Qty = 0;   //该产品的总购买数量
            $skuvaljosn = !empty($rsProduct['skuvaljosn']) ? json_decode($rsProduct['skuvaljosn'], true) : '';
            foreach ($value as $attr => $item) {
                $Qty += $item['Qty'];   //购买商品件数

                if (!empty($attr) && !empty($item['Property']) && !empty($skuvaljosn)) {   //有属性    更新属性库存
                    if ($act == 'add') {
                        if (empty($skuvaljosn[$attr]) || $skuvaljosn[$attr]['Txt_CountSon'] < $item['Qty']) {
                            return false;   //不存在该属性或属性库存不足
                        } else {
                            $skuvaljosn[$attr]['Txt_CountSon'] -= $item['Qty'];
                        }
                    } else if ($act == 'del') {
                        if (!empty($skuvaljosn[$attr])) {   //有退单属性
                            $skuvaljosn[$attr]['Txt_CountSon'] += $item['Qty'];
                        } else {    //没有退单属性
                            $Qty -= $item['Qty'];
                        }
                    }
                } else {
                    if ($act == 'add' && $rsProduct['Products_Count'] < $item['Qty']) {
                        return false;   //库存不足
                    }
                }
            }

            //判断库存是否充足
            if ($act == 'add' && $rsProduct['Products_Count'] < $Qty) {
                return false;   //库存不足
            }

            //获取库存  有属性为属性库存之和，可消除库存库存和属性库存之和不一致问题
            if (!empty($skuvaljosn)) {
                $update_data['Products_Count'] = array_sum(array_column($skuvaljosn, 'Txt_CountSon'));
                $update_data['skuvaljosn'] = !empty($skuvaljosn) ? json_encode($skuvaljosn, JSON_UNESCAPED_UNICODE) : '';
            } else {
                $update_data['Products_Count'] = $act == 'add' ? $rsProduct['Products_Count'] - $Qty : $rsProduct['Products_Count'] + $Qty;
            }

            //添加、删除订单时 判断该订单的产品 做上下架操作
            if ($update_data['Products_Count'] <= 0 && $rsProduct['Products_SoldOut'] == 0) {   //无库存，且产品是上架状态，则下架
                $update_data['Products_SoldOut'] = 1;
            } else if ($update_data['Products_Count'] > 0 && $rsProduct['Products_SoldOut'] == 1) { //有库存，且产品为下架状态，则上架
                $update_data['Products_SoldOut'] = 0;
            }

            $flag = $this->productStockSoldOut($rsProduct, $update_data);

            return boolval($flag);
        }
        return true;
    }

    /**
     * 退款、退货单 更新库存
     * (观察得：退款单中 ProductID != 0 时 退款中的产品为 无属性 产品id为 ProductID 的产品， == 0 时，退款单中有一个或多个产品（一个时有属性）)
     * @param $rsBack   //退单详情
     * @return bool
     */
    private function backOrderStock($rsBack)
    {
        if (empty($rsBack)) return false;

        foreach ($rsBack as $k => $value) {
            $Back_Json = !empty($value['Back_Json']) ? json_decode($value['Back_Json'], true) : '';
            if (empty($Back_Json)) return false;    //退单中没有产品信息

            if ($value['ProductID'] != 0) {  //退单中只有一个产品，且产品id为$value['ProductID']  若$value['Back_CartID']不为0，则为有属性的产品
                $ProductsID = $value['ProductID'];
                $rsProduct = $this->db->table('shop_products')->where('Products_ID', $ProductsID)->first();
                if (empty($rsProduct)) continue;

                $update_data = [];
                $skuvaljosn = !empty($rsProduct['skuvaljosn']) ? json_decode($rsProduct['skuvaljosn'], true) : '';
                if (!empty($value['Back_CartID']) && !empty($skuvaljosn)) {    //有属性的产品
                    if (!empty($skuvaljosn[$value['Back_CartID']])) {   //有此属性
                        $skuvaljosn[$value['Back_CartID']]['Txt_CountSon'] += $Back_Json['Qty'];
                    } else {    //无此属性

                    }

                    //获取库存  有属性为属性库存之和，可消除库存库存和属性库存之和不一致问题
                    $update_data['Products_Count'] = array_sum(array_column($skuvaljosn, 'Txt_CountSon'));
                    $update_data['skuvaljosn'] = !empty($skuvaljosn) ? json_encode($skuvaljosn, JSON_UNESCAPED_UNICODE) : '';
                } else {    //无属性产品
                    $update_data['Products_Count'] = $rsProduct['Products_Count'] + $Back_Json['Qty'];
                }

                //已下架产品若有库存则上架  因为退单操作，故不做下架处理
                if ($update_data['Products_Count'] > 0 && $rsProduct['Products_SoldOut'] == 1) {
                    $update_data['Products_SoldOut'] = 0;
                }

                $flag = !empty($update_data) ? $this->productStockSoldOut($rsProduct, $update_data) : true;

                return boolval($flag);

            } else {    //一个或多个产品
                foreach ($Back_Json as $ProductsID => $value) {
                    $rsProduct = $this->db->table('shop_products')->where('Products_ID', $ProductsID)->first();
                    if (empty($rsProduct)) continue;

                    $Qty = 0;   //该产品的总购买数量
                    $skuvaljosn = !empty($rsProduct['skuvaljosn']) ? json_decode($rsProduct['skuvaljosn'], true) : '';
                    foreach ($value as $attr => $item) {
                        $Qty += $item['Qty'];
                        if (!empty($attr) && !empty($item['Property']) && !empty($skuvaljosn)) {     //有属性
                            if (!empty($skuvaljosn[$attr])) {   //存在该属性
                                $skuvaljosn[$attr]['Txt_CountSon'] += $item['Qty'];
                            } else {    //无此属性
                                $Qty -= $item['Qty'];
                            }
                        } else {    //无属性

                        }
                    }

                    //获取库存  有属性为属性库存之和，可消除库存库存和属性库存之和不一致问题
                    if (!empty($skuvaljosn)) {
                        $update_data['Products_Count'] = array_sum(array_column($skuvaljosn, 'Txt_CountSon'));
                        $update_data['skuvaljosn'] = !empty($skuvaljosn) ? json_encode($skuvaljosn, JSON_UNESCAPED_UNICODE) : '';
                    } else {
                        $update_data['Products_Count'] = $rsProduct['Products_Count'] + $Qty;
                    }

                    //若产品已下架，有库存则做上架操作
                    if (!empty($update_data['Products_Count']) && $rsProduct['Products_SoldOut'] == 1) {
                        $update_data['Products_SoldOut'] = 0;
                    }

                    $flag = $this->productStockSoldOut($rsProduct, $update_data);

                    return boolval($flag);
                }
            }
        }
        return true;
    }

    /**
     * 增加、减少库存
     * 涉及到 自营产品、代销产品、平台产品
     * @param integer $OrderID 订单id（user_order）
     * @param string  $act     操作性（add：提交订单  del：取消订单  back: 退款退货）
     */
    public function updateProductStock($OrderID, $act)
    {
        //是否传入订单id和操作方法
        if (empty($OrderID) || empty($act)) return false;
        if (! in_array($act, ['add', 'del', 'back'])) return false;

        //判断是否有此订单  判断订单类型
		$rsOrder = $this->db->table('user_order')->where('Order_ID', $OrderID)->first();
        if (empty($rsOrder) || !in_array($rsOrder['Order_Type'], ['shop', 'pintuan', 'convert'])) return false;

        //判断
        if ($act == 'back') {   //退款退货订单
            if ($rsOrder['Is_Backup'] != 1) return false;   //订单没有退款退货
            //查询退款单
            $rsBack = $this->db->table('user_back_order')->where('Order_ID', $OrderID)->whereIn('Back_Status', [3, 4, 7])->get(['Back_Json', 'ProductID', 'Back_CartID']);
			
            if (empty($rsBack)) return false;   //没有查询到退款单

            $res = $this->backOrderStock($rsBack);

        } else {    //提交订单、取消订单
            $CartList = json_decode($rsOrder['Order_CartList'], true);
            if (empty($CartList)) return false;     //购物车为空

            $res = $this->orderStock($CartList, $act);
        }

        return boolval($res);
    }

    /**
     * 更新销量
     * 涉及到 自营产品、代销产品、平台产品
     * @param integer $OrderID 订单id（user_order）
     */
    public function updateProdcutSales($OrderID)
    {
        //检查传入的变量
        if (empty($OrderID)) return false;

        //判断是否有此订单  判断订单类型
		$rsOrder = $this->db->table('user_order')->where('Order_ID', $OrderID)->first();
        if (empty($rsOrder) || !in_array($rsOrder['Order_Type'], ['shop', 'pintuan', 'convert'])) return false;

        $CartList = !empty($rsOrder['Order_CartList']) ? json_decode($rsOrder['Order_CartList'], true) : '';
        if (empty($CartList)) return false;     //购物车为空

        foreach ($CartList as $ProductsID => $value) {
            $rsProduct = $this->db->table('shop_products')->where('Products_ID', $ProductsID)->first();
            if (empty($rsProduct)) return false;

            $Qty = 0;   //该产品的总购买数量
            foreach ($value as $attr => $item) {
                $Qty += $item['Qty'];   //购买商品件数
            }

            //产品数量
            $update_data['Products_Sales'] = $rsProduct['Products_Sales'] + $Qty;

            $Products_ID = $rsProduct['Products_ID'];

            $flag_b2c_sales = true;
			$flag = $this->db->table('shop_products')->where('Products_ID', $Products_ID)->update($update_data);

            return $flag;
        }
        return true;
    }
}