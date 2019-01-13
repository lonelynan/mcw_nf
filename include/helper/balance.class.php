<?php

class balance
{

    var $db;

    var $usersid;

    function __construct($DB, $usersid)
    {
        $this->db = $DB;
        $this->usersid = $usersid;
    }

    public function add_sales($orderid)
    {
        $item = $this->db->GetRs("user_order", "*", "where Order_ID=" . $orderid . " and Order_Status=4 and Users_ID='" . $this->usersid . "'");
        $r = $this->db->query('select SUM(b.Record_Money) as bonus from distribute_account_record as b LEFT JOIN distribute_record as r ON b.Ds_Record_ID=r.Record_ID where r.Order_ID=' . $orderid . ' limit 0,1');
        $rs = $this->db->fetch_assoc();
        $bonus = $rs["bonus"] ? $rs["bonus"] : 0;		
		$rsBizff = $this->db->GetRs("biz", "Finance_Type,Finance_Rate", "where Biz_ID=" . $item['Biz_ID'] . " and Users_ID='" . $this->usersid . "'");		
        if ($item) {
			$CartList = json_decode($item["Order_CartList"], true);
			if (!empty($item['back_qty_str'])) {
				$web_pjs = $this->repeat_list($CartList, $item['Integral_Money'], $item['curagio_money'], $item['Coupon_Cash'], 0);
				$web_pjs = $web_pjs['web_priejs'];
			} else {
				$web_pjs = $item['Web_Pricejs'];
			}	
            $Shipping = json_decode(htmlspecialchars_decode($item["Order_Shipping"]), true);
			
            $Data = array(
                "Users_ID" => $this->usersid,
                "Order_ID" => $orderid,
                "Order_Json" => $item["Order_CartList"],
                "Biz_ID" => $item["Biz_ID"],
                "Bonus" => $bonus,
               "Order_Amount" => $item["Order_TotalAmount"] - $item["Back_Amount_Source"] - (empty($Shipping["Price"]) ? 0 : $Shipping["Price"]),
                "Order_Diff" => $item["Coupon_Cash"],
                "Order_Shipping" => empty($Shipping["Price"]) ? 0 : $Shipping["Price"],
                "Order_TotalPrice" => $item["Order_TotalPrice"] - $item["Back_Amount"],
                "Record_CreateTime" => time(),
				"Finance_Type" => $rsBizff['Finance_Type'],
                "Finance_Rate" => $rsBizff['Finance_Rate'],				
                "Web_Price" => $web_pjs,
            );
            return $this->db->Add("shop_sales_record", $Data);
        }
        return false;
    }

    public function get_sales_record($condition)
    { // 销售记录$type=1 销售详情 $type=0 辅助付款单生成
        $lists = array();
        $this->db->Get("shop_sales_record", "*", $condition);
        while ($r = $this->db->Fetch_assoc()) {
            $lists[$r["Record_ID"]] = $r;
        }
        return $lists;
    }

    /**
     * 创建付款单主函数 最终结算的总价格=产品总价格+运费总价格-优惠券总价格-网站提成总价格,由于积分抵用和会员折扣是平台设置的优惠,所以此费用由平台承担
     * @param $condition string 获取需要结算的销售记录的where条件
     * @return array|bool
     */
    public function create_payment($condition)
    { // 付款单生成详情
        $lists = $this->get_sales_record($condition);   
        $alltotal = $cash = $web_total = $logistic = 0;
        if(empty($lists)) return false;
        
        foreach ($lists as $id => $item) {
            //产品总价格
            $alltotal += $item["Order_Amount"];
            //优惠券优惠价格
            $cash += isset($item["Order_Diff"]) ? $item["Order_Diff"] : 0;
            //运费总价格
            $logistic += $item["Order_Shipping"];
            //网站提成总价格
			$web_total += $item["Web_Price"];
			}
			
        $data = array(           
            "products_num" => count($lists),
            "alltotal" => $alltotal,
            "logistic" => $logistic,
            "cash" => $cash,
            "web" => $web_total,            
            "supplytotal" => $alltotal + $logistic - $cash - $web_total
        );
        return $data;
    }

    /**
     * 计算网站所得金额函数,此函数牵扯结算问题,函数是之前就存在,20180211只是补写了注释,$curagio_money和$catloop这两个变量没用
     * @param $CartList  string  单个商家的购物车字符串
     * @param $diyong_money   number  积分抵用的金额
     * @param $curagio_money  number 会员折扣优惠的金额
     * @param $cash  number 优惠券抵用的金额
     * @param $catloop  number 这个订单有多少个商家
     * @author 271054123 时间20180211
     * @return array
     */
    function repeat_list($CartList, $diyong_money, $curagio_money, $cash, $catloop)
    {
		global $DB;
		$_SESSION['yformat'] = '';
		$muilti = 0;
		if (count($CartList) > 1) {
			$muilti = 1;
		}
		if (empty($muilti)) {
            foreach($CartList as $key=>$products){
                if (count($products) > 1) {
                    $muilti = 1;
                    break;
                }
            }
		}

		$web_prie = 0;
		$web_priejs = 0;
		if (empty($muilti)) {
            foreach($CartList as $key=>$products){
                foreach($products as $k=>$v){
                    if (count($v["Property"]) > 0) {
                        //有属性的情况,价格设置为属性的价格
                        $produ_price = isset($v["Property"]["shu_pricesimp"]) ? $v["Property"]["shu_pricesimp"] : 0;
                        //有属性的情况下,供货价设置为属性的供货价
                        $produ_priceg = isset($v["Property"]["shu_priceg"]) ? $v["Property"]["shu_priceg"] : 0;
                    } else {
                        //没有属性的情况下, 价格和供货价直接获取现价和供货价的价格
                        $produ_price = isset($v["ProductsPriceX"]) ? $v["ProductsPriceX"] : 0;
                        $produ_priceg = isset($v["Products_PriceS"]) ? $v["Products_PriceS"] : 0;
                    }
                    //计算会员折扣的优惠金额
                    $procuragio = $produ_price * ((!empty($v["user_curagio"]) ? $v["user_curagio"] : 0) * 0.01) * $v["Qty"];
                    //计算网站所得金额,等于现价减去供货价
                    $produ_web = $produ_price - $produ_priceg;
                    if (empty($v['Biz_FinanceType'])) {
                        $web_prie += ($produ_price * $v["Qty"] - $diyong_money - $procuragio - $cash) * $v['Biz_FinanceRate'] * 0.01;
                        $_SESSION['yformat'][$key][$k] = '(商品价格'.$produ_price.'*商品数量'.$v["Qty"].'-积发抵用'.$diyong_money.'-会员折扣'.$procuragio.'-优惠券'.$cash.')*交易额比例'.$v['Biz_FinanceRate'].'%';
                        //$web_priejs += ($produ_price * $v["Qty"] - $diyong_money - $procuragio - $cash) * $v['Biz_FinanceRate'] * 0.01;
                        $web_priejs += ($produ_price * $v["Qty"]) * $v['Biz_FinanceRate'] * 0.01;
                    } else {
                        if (empty($v['Products_FinanceType'])) {
                            $web_prie += ($produ_price * $v["Qty"] - $diyong_money - $procuragio - $cash) * $v['Products_FinanceRate'] * 0.01;
                            $_SESSION['yformat'][$key][$k] = '(商品价格'.$produ_price.'*商品数量'.$v["Qty"].'-积发抵用'.$diyong_money.'-会员折扣'.$procuragio.'-优惠券'.$cash.')*交易额比例'.$v['Products_FinanceRate'].'%';
                            //$web_priejs += ($produ_price * $v["Qty"] - $diyong_money - $procuragio - $cash) * $v['Products_FinanceRate'] * 0.01;
                            $web_priejs += ($produ_price * $v["Qty"]) * $v['Products_FinanceRate'] * 0.01;
                        } else {
                            $diyongagiocash = $diyong_money + $cash + $procuragio;
                            $web_prie += $produ_web * $v["Qty"] - $diyongagiocash;
                            $_SESSION['yformat'][$key][$k] = '(商品价格'.$produ_price.'-供货价'.$produ_priceg.')*商品数量'.$v["Qty"].'-(积发抵用'.$diyong_money.'+优惠券'.$cash.')+会员折扣'.$procuragio;
                            $web_priejs += $produ_web * $v["Qty"];
                        }
                    }
                }
            }
            $CartListupdate = $CartList;
        } else {
            $shop_num = 0;
            foreach($CartList as $key=>$products){
                foreach($products as $k=>$v){
                    $shop_num += $v["Qty"];
                }
            }
            $CartListnew = $CartList;
            foreach($CartList as $key=>$products){
                foreach($products as $k=>$v){
                    if (count($v["Property"]) > 0) {
                        $produ_price = isset($v["Property"]["shu_pricesimp"]) ? $v["Property"]["shu_pricesimp"] : 0;
                        $produ_priceg = isset($v["Property"]["shu_priceg"]) ? $v["Property"]["shu_priceg"] : 0;
                    }else{
                        $produ_price = isset($v["ProductsPriceX"]) ? $v["ProductsPriceX"] : 0;
                        $produ_priceg = isset($v["Products_PriceS"]) ? $v["Products_PriceS"] : 0;
                    }

                    $procuragio = $produ_price * ((!empty($v["user_curagio"]) ? $v["user_curagio"] : 100) * 0.01) * $v["Qty"];
                    $produ_web = $produ_price - $produ_priceg;

                    if (empty($v['Biz_FinanceType'])) {
                        //$allproductbil += $v['Biz_FinanceRate'] * 0.01;
                        //$allproductnum += $v["Qty"];
                        $web_prie += ($produ_price * $v["Qty"] - $diyong_money / $shop_num * $v["Qty"] - $procuragio - $cash / $shop_num * $v["Qty"]) * $v['Biz_FinanceRate'] * 0.01;
                        $_SESSION['yformat'][$key][$k] = '(商品价格'.$produ_price.'*商品数量'.$v["Qty"].'-积发抵用'.$diyong_money.'/商品总数量'.$shop_num.'*商品数量'.$v["Qty"].'-会员折扣'.$procuragio.'-优惠券'.$cash.'/商品总数量'.$shop_num.'*商品数量'.$v["Qty"].')*交易额比例'.$v['Biz_FinanceRate'].'%';
                        //$web_priejs += ($produ_price * $v["Qty"] - $diyong_money / $shop_num * $v["Qty"] - $procuragio - $cash / $shop_num * $v["Qty"]) * $v['Biz_FinanceRate'] * 0.01;
                        $web_priejs += ($produ_price * $v["Qty"]) * $v['Biz_FinanceRate'] * 0.01;
                        $web_prie_shop = ($produ_price * $v["Qty"] - $diyong_money / $shop_num * $v["Qty"] - $procuragio - $cash / $shop_num * $v["Qty"]) * $v['Biz_FinanceRate'] * 0.01;
                    } else {
                        if (empty($v['Products_FinanceType'])) {
                            //$allproductbil += $v['Products_FinanceRate'] * 0.01;
                            //$allproductnum += $v["Qty"];
                            $web_prie += ($produ_price * $v["Qty"] - $diyong_money / $shop_num * $v["Qty"] - $procuragio - $cash / $shop_num * $v["Qty"]) * $v['Products_FinanceRate'] * 0.01;
                            $_SESSION['yformat'][$key][$k] = '(商品价格'.$produ_price.'*商品数量'.$v["Qty"].'-积发抵用'.$diyong_money.'/商品总数量'.$shop_num.'*商品数量'.$v["Qty"].'-会员折扣'.$procuragio.'-优惠券'.$cash.'/商品总数量'.$shop_num.'*商品数量'.$v["Qty"].')*交易额比例'.$v['Products_FinanceRate'].'%';
                            //$web_priejs += ($produ_price * $v["Qty"] - $diyong_money / $shop_num * $v["Qty"] - $procuragio - $cash / $shop_num * $v["Qty"]) * $v['Products_FinanceRate'] * 0.01;
                            $web_priejs += ($produ_price * $v["Qty"]) * $v['Products_FinanceRate'] * 0.01;
                            $web_prie_shop = ($produ_price * $v["Qty"] - $diyong_money / $shop_num * $v["Qty"] - $procuragio - $cash / $shop_num * $v["Qty"]) * $v['Products_FinanceRate'] * 0.01;
                        } else {
                            //$allproductbil += 1 - $produ_priceg / $produ_price;
                            //$allproductnum += $v["Qty"];
                            $diyongagiocash = ($diyong_money + $cash) / $shop_num * $v["Qty"] + $procuragio;
                            $web_prie += $produ_web * $v["Qty"] - $diyongagiocash;
                            $_SESSION['yformat'][$key][$k] = '(商品价格'.$produ_price.'-供货价'.$produ_priceg.')*商品数量'.$v["Qty"].'-((积发抵用'.$diyong_money.'+优惠券'.$cash.')/商品总数量'.$shop_num.'+会员折扣'.$procuragio.')'.'*商品数量'.$v["Qty"];
                            //$web_priejs += $produ_web * $v["Qty"] - $diyongagiocash * $v["Qty"];
                            $web_priejs += $produ_web * $v["Qty"];
                            $web_prie_shop = $produ_web * $v["Qty"] - $diyongagiocash;
                        }
                    }
                    $CartListnew[$key][$k]['web_prie_shop'] = $web_prie_shop;
                }
            }
            $CartListupdate = $CartListnew;
        }

		$webarr = [];
		$webarr['web_priejs'] = $web_priejs;
		$webarr['web_prie'] = $web_prie;
		$webarr['CartListupdate'] = $CartListupdate;
		$webarr['muilti'] = $muilti;
        return $webarr;
    }

    private function rmb_format($money = 0, $is_round = true, $int_unit = '元')
    {
        $chs = array(
            0,
            '壹',
            '贰',
            '叁',
            '肆',
            '伍',
            '陆',
            '柒',
            '捌',
            '玖'
        );
        $uni = array(
            '',
            '拾',
            '佰',
            '仟'
        );
        $dec_uni = array(
            '角',
            '分'
        );
        $exp = array(
            '',
            '万',
            '亿'
        );
        $res = '';
        // 以 元为单位分割
        $parts = explode('.', $money, 2);
        $int = isset($parts[0]) ? strval($parts[0]) : 0;
        $dec = isset($parts[1]) ? strval($parts[1]) : '';
        // 处理小数点
        $dec_len = strlen($dec);
        if (isset($parts[1]) && $dec_len > 2) {
            $dec = $is_round ? substr(strrchr(strval(round(floatval("0." . $dec), 2)), '.'), 1) : substr($parts[1], 0, 2);
        }
        // number= 0.00时，直接返回 0
        if (empty($int) && empty($dec)) {
            return '零';
        }

        // 整数部分 从右向左
        for ($i = strlen($int) - 1, $t = 0; $i >= 0; $t ++) {
            $str = '';
            // 每4字为一段进行转化
            for ($j = 0; $j < 4 && $i >= 0; $j ++, $i --) {
                $u = $int{$i} > 0 ? $uni[$j] : '';
                $str = $chs[$int{$i}] . $u . $str;
            }
            $str = rtrim($str, '0');
            $str = preg_replace("/0+/", "零", $str);
            $u2 = $str != '' ? $exp[$t] : '';
            $res = $str . $u2 . $res;
        }
        $dec = rtrim($dec, '0');
        // 小数部分 从左向右
        if (! empty($dec)) {
            $res .= $int_unit;
            $cnt = strlen($dec);
            for ($i = 0; $i < $cnt; $i ++) {
                $u = $dec{$i} > 0 ? $dec_uni[$i] : ''; // 非0的数字后面添加单位
                $res .= $chs[$dec{$i}] . $u;
            }
            if ($cnt == 1)
                $res .= '整';
            $res = rtrim($res, '0'); // 去掉末尾的0
            $res = preg_replace("/0+/", "零", $res); // 替换多个连续的0
        } else {
            $res .= $int_unit . '整';
        }
        return $res;
    }

    function echo_payment_info($condition, $array)
    {
        $data = $this->get_sales_record($condition);
        if (count($data) == 0) {
            echo "";
            exit();
        }
        if(empty($array)) return '';        
        
        $totalmoney = $array['Total'];
		if (isset($array['webpayrate']) && !empty($array['webpayrate'])) {
			$zhuanzz = $totalmoney * $array['webpayrate'] /100;			
		} else {
			$zhuanzz = 0;			
		}
		$zhuanyy = $totalmoney - $zhuanzz;

        $html = '<div id="printPage"><table cellspacing="0" cellpadding="0" width="90%" style="border:2px #000 solid;">';

        /* */
        $html .= '
		<tr>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体;font-size:12pt; color:#000">单号</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $array["Payment_Sn"] . '</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">时间</td>
		  <td style="text-align:center; height:28pt; width:25%; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . date("Y-m-d H:i:s", $array["CreateTime"]) . '</td>
		</tr>
		<tr>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体;font-size:12pt; color:#000">商家名称</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $array["Biz"] . '</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">结算时间</td>
		  <td style="text-align:center; height:28pt; width:25%; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . date("Y-m-d", $array["FromTime"]) . ' - ' . date("Y-m-d", $array["EndTime"]) . '</td>
		</tr>
		<tr>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体;font-size:12pt; color:#000">合计金额</td>
		  <td colspan="3" style="text-align:left; padding-left:15px; height:30pt; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">人民币：（大写）' . $this->rmb_format($totalmoney, false) . '  ￥' . $totalmoney . '</td>
		</tr>';
        if ($array['Payment_Type'] == 1) {
            global $DB;
            $rsBiz = $DB->GetRs('biz', '*', "WHERE Users_ID='" . $array["Users_ID"] . "' AND Biz_ID = '{$array['Biz_ID']}'");

            $config = json_decode($rsBiz['Biz_PayConfig'], true);
            // 微支付
            $html .= '<tr>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体;font-size:12pt; color:#000">收款类型</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">微支付</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">微信昵称</td>
		  <td style="text-align:center; height:28pt; width:25%; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000"><img src="' . isset($config['config']["headimgurl"])?$config['config']["headimgurl"]:'' . '" width="20"/>' . isset($config['config']["nickname"])?$config['config']["nickname"]:'' . '</td>
		</tr>';
        } else
            if ($array['Payment_Type'] == 2) {
                global $DB;
                $rsBiz = $DB->GetRs('biz', '*', "WHERE Users_ID='" . $array["Users_ID"] . "' AND Biz_ID = '{$array['Biz_ID']}'");
                $config = json_decode($rsBiz['Biz_PayConfig'], true);
                if(isset($config['config']["aliPayNo"]) && !empty($config['config']["aliPayNo"])){
                    $aliPayNo = $config['config']["aliPayNo"];
                }else{
                    $aliPayNo = '无';
                }
                // 微支付
                $html .= '<tr>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体;font-size:12pt; color:#000">收款类型</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">支付宝</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">付款账户名</td>
		  <td style="text-align:center; height:28pt; width:25%; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $aliPayNo . '</td>
		</tr>';
            } else {
                $html .= '<tr>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体;font-size:12pt; color:#000">收款银行</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $array["Bank"] . '</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">银行卡号</td>
		  <td style="text-align:center; height:28pt; width:25%; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $array["BankNo"] . '</td>
		</tr>
		<tr>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体;font-size:12pt; color:#000">收款人</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $array["BankName"] . '</td>
		  <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">收款人手机</td>
		  <td style="text-align:center; height:28pt; width:25%; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $array["BankMobile"] . '</td>
		</tr>';
            }

        $html .= '<tr>
		    <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; border-bottom:1px #000 solid; font-family:宋体;font-size:12pt; color:#000">备注</td>
		    <td style="text-align:center; height:30pt; width:25%; border-bottom:1px #000 solid; font-family:宋体; font-size:12pt; color:#000" colspan="3">总计:'.$totalmoney.'(转账'.$zhuanzz.'+转向余额'.$zhuanyy.')</td>
		</tr>
		<tr>
		    <td style="text-align:center; height:30pt; width:25%; border-right:1px #000 solid; font-family:宋体;font-size:12pt; color:#000">审批</td>
		    <td style="text-align:center; height:30pt; width:25%; font-family:宋体; font-size:12pt; color:#000" colspan="3"></td>
		</tr>
		';
        $html .= '</table>';
        $html .= '<table cellspacing="0" cellpadding="0" width="90%" style="border:1px #000 solid; margin-top:30px;">';
        $html .= '
		<tr>
		  <td style="text-align:center; height:30pt; width:25%; border-bottom:1px #000 solid; font-family:宋体;font-size:12pt; color:#000" colspan="8">' . $array["Payment_Sn"] . '号付款单销售明细</td>
		</tr>';
        $html .= '
			<tr>
			<td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">订单号</td>
			<td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">商品总额</td>
			<td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">运费费用</td>
			<td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">订单应收</td>
			<td style="text-align:center; height:28pt; width:12.5%; border-right:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">优惠金额</td>
			<td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">订单实收</td>
			<td style="text-align:center; height:28pt; width:12.5%; border-right:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">网站提成</td>			
			<td style="text-align:center; height:28pt; width:12.5%; font-family:宋体; font-size:12pt; color:#000">结算金额</td>
			</tr>
			';
        foreach ($data as $recordid => $value) {
            $html .= '
			<tr>
		  <td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; border-top:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . getorderno($value["Order_ID"]) . '</td>
		  <td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; border-top:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $value["Order_Amount"] . '</td>
		  <td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; border-top:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $value["Order_Shipping"] . '</td>
		  <td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; border-top:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . ($value["Order_Amount"] + $value["Order_Shipping"]) . '</td>
		  <td style="text-align:center; height:30pt; width:12.5%; border-right:1px #000 solid; border-top:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $value["Order_Diff"] . '</td>
		  <td style="text-align:center; height:28pt; width:12.5%; border-right:1px #000 solid; border-top:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . ($value["Order_Amount"] + $value["Order_Shipping"] - $value["Order_Diff"]) . '</td>
		  <td style="text-align:center; height:28pt; width:12.5%; border-right:1px #000 solid; border-top:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . $value["Web_Price"] . '</td>
		  <td style="text-align:center; height:28pt; width:12.5%; border-top:1px #000 solid; font-family:宋体; font-size:12pt; color:#000">' . ($value["Order_Amount"] + $value["Order_Shipping"] - $value["Order_Diff"] - $value["Web_Price"]) . '</td>
		  </tr>
			';
        }
        $html .= '</table></div>';
        echo $html;
    }
    //
}
?>
