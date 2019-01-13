<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Original Author <author@example.com>                        |
// |          Your Name <you@example.com>                                 |
// +----------------------------------------------------------------------+
//
// $Id:$

/**
 *
 * 将指定信息导出为Excel...
 * 其中包括
 * 	1.采购汇总表
 *  2.订单结算汇总表
 *  3.订单明细打印单
 *  4.退货汇总表
 *  5.订单详细汇总表
 *  6.客户明细汇总表
 *  7.产品详细汇总表
 * @author JohnKuo
 *
 */
//加载所需类
include 'PHPExcel.php';
include 'PHPExcel/Writer/Excel2007.php';
class OutputExcel {
    private $templates = array(
        'product_gross_info' => 'product_gross_info.xls',
        'order_detail_list' => 'order_detail_list.xls',
		'order_detail_list_biz' => 'order_detail_list_biz.xls',
        'sales_record_list' => 'sales_record_list.xls',
		'user_staticstic_list' => 'user_staticstic_list.xls',
        'visit_staticstic_list' => 'visit_staticstic_list.xls',
        'sales_staticstic_list' => 'sales_staticstic_list.xls', 
        'commission_staticstic_list' => 'commission_staticstic_list.xls',
        'users_staticstic_list' => 'users_staticstic_list.xls',
        'distributer_staticstic_list' => 'distributer_staticstic_list.xls',
        'product_staticstic_list' => 'product_staticstic_list.xls',
         'area_staticstic_list' => 'area_staticstic_list.xls',
        'users_pintuan_list' => 'users_pintuan_list.xls',
		'user_list' => 'user_list.xls'
    );
    private $_objPHPExcel;
    private $_objReader;
    private $_objWriter;
    private $template_path;
    private $cur_row = 5; //excel所应输出数据到的当前行号
    
    /**
     *
     * 构造函数 ...
     */
    function __construct() {
        $this->_objPHPExcel = new PHPExcel();
        $this->_objReader = PHPExcel_IOFactory::createReader('Excel5');
        $this->_objWriter = new PHPExcel_Writer_Excel2007($this->_objPHPExcel);
        $this->template_path = $_SERVER["DOCUMENT_ROOT"] . '/data/excel_template/';
    }
    /**
     *
     * 生成 产品详细汇总表...
     * @param string $beingTime 开始时间  
     * @param string $endTime 结束时间
     * @param array $data 需要填充的数据
     * @param int $offset 开始填充的数据行数
     */
    public function product_gross_info($data) {
        $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['product_gross_info']);
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //填充数据
        $baserow = 4;
        foreach ($data as $key => $product) {
            $row = $baserow + $key;
            $objActSheet->setCellValue('A' . $row, $key);
            $objActSheet->setCellValue('B' . $row, $product['Products_ID']);
            $objActSheet->setCellValue('C' . $row, $product['Products_Name']);
            $objActSheet->setCellValue('D' . $row, $product['Products_Property']);
            $objActSheet->setCellValue('E' . $row, $product['Products_Count']);
            $objActSheet->setCellValue('F' . $row, $product['Products_PriceX']);
            $objActSheet->setCellValue('G' . $row, $product['Products_Category']);
        }
        //设置单元格边框
        if (count($data) > 0) {
            $this->_objPHPExcel->getActiveSheet()->getStyle('A5:H' . $row)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        //$this->_objPHPExcel->getActiveSheet()->getStyle('A4:G4')->applyFromArray($styleThinBlackBorderOutline);
        $filename = 'product_gross_info.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
    }
    /*导出所有订单明细列表*/
    public function order_detail_list($beinTime, $endTime, $data) {
        $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['order_detail_list']);
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //设置日期
        $objActSheet->setCellValue('B1', $beinTime);
        $objActSheet->setCellValue('B2', $endTime);
        //输出信息
        //填充数据
		
		$this->cur_row = 4;		
        foreach ($data as $key => $order) {
			$row = $this->cur_row;
            $order['Order_TotalPrice'] = $order['Order_TotalPrice'] >= $order['Back_Amount'] ? ($order['Order_TotalPrice'] - $order['Back_Amount']) : 0;
			 $Shipping = json_decode(htmlspecialchars_decode($order['Order_Shipping']), true);
            if (!empty($Shipping)) {
                $Shipping_Name = !empty($Shipping["Express"]) ? $Shipping["Express"] : '';
				$shipping_Money = !empty($Shipping["Price"]) ? $Shipping["Price"] : '';
            } else {
                $Shipping_Name = '';
                $shipping_Money = '';
            }
			
			$Order_Status=array("待确认","待付款","已付款","已发货","已完成","申请退款中","申请换货");
			if ($order["Order_Status"] == 1 && $order["Order_PaymentMethod"] == "线下支付" && !empty($order["Order_PaymentInfo"])) { 
					$Order_RealStatus = "待卖家确认";
				 } else { 
					$Order_RealStatus = $Order_Status[$order["Order_Status"]];
				 }
				 
			$paymethod = empty($order["Order_PaymentMethod"]) || $order["Order_PaymentMethod"]=="0" ? "暂无" : $order["Order_PaymentMethod"];
				 
            $objActSheet->setCellValue('A' . $this->cur_row, ($key + 1));
            $objActSheet->setCellValue('B' . $this->cur_row,  date("Ymd", $order['Order_CreateTime']).$order['Order_ID'].' ');			
			
			 /*循环输出产品*/
			$cart_num = count($order['Order_CartList']);
			$car_looopnum = 1;
            if ($cart_num > 0) {
			foreach($order['Order_CartList'] as $productid=>$proarr){
				foreach($proarr as $key=>$value){
					$objActSheet->setCellValue('C' . $this->cur_row, $value['ProductsName']);
					$productattr = '';
					$sigleprice = $value['ProductsPriceX'];
					if(!empty($value["Property"])){
						$productattr .= $value["Property"]['shu_value'];
						$sigleprice = $value["Property"]['shu_pricesimp'];
					}
					$objActSheet->setCellValue('D' . $this->cur_row, $productattr);
					$objActSheet->setCellValue('E' . $this->cur_row, $value['Qty']);
					$objActSheet->setCellValue('F' . $this->cur_row, $sigleprice);
					if ($car_looopnum == $cart_num) break;
					if ($cart_num > 1) {
                            $this->cur_row = $this->cur_row + 1;
                        }
				}
				$car_looopnum++;
			}
			}
            
            $objActSheet->setCellValue('G' . $row, $order['biz_Name']);
            $objActSheet->setCellValue('H' . $row, $order['fenxiao_Name']);
            $objActSheet->setCellValue('I' . $row, $order['Address_Name']);
            $objActSheet->setCellValue('J' . $row, $order['Address_Mobile']);
            $objActSheet->setCellValue('K' . $row, $order['Order_TotalPrice']);
            $objActSheet->setCellValue('L' . $row, $Shipping_Name);
            $objActSheet->setCellValue('M' . $row, $order['Order_ShippingID']);
            $objActSheet->setCellValue('N' . $row, $shipping_Money);
            $objActSheet->setCellValue('O' . $row, $order['prciar']);
            $objActSheet->setCellValue('P' . $row, $order['receiver_address']);
            $objActSheet->setCellValue('Q' . $row, $Order_RealStatus);
            $objActSheet->setCellValue('R' . $row, $paymethod);
            $objActSheet->setCellValue('S' . $row, " ".date("Ymd H:i:s", $order['Order_CreateTime']));            
            $objActSheet->setCellValue('T' . $row, $order['Order_Remark']);           
            $this->cur_row = $this->cur_row + 1;
        }
        $filename = 'order_detail_list' . $beinTime . '_' . $endTime . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
    }
	/*导出所有订单明细列表*/
    public function order_detail_list_biz($beinTime, $endTime, $data) {
        $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['order_detail_list_biz']);
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //设置日期
        $objActSheet->setCellValue('B2', $beinTime);
        $objActSheet->setCellValue('B3', $endTime);
        //输出信息
        //填充数据
        foreach ($data as $key => $order) {
            $order['Order_TotalPrice'] = $order['Order_TotalPrice'] >= $order['Back_Amount'] ? ($order['Order_TotalPrice'] - $order['Back_Amount']) : 0;
            $objActSheet->setCellValue('A' . $this->cur_row, ($key + 1));            
            $objActSheet->setCellValue('B' . $this->cur_row, $order['Address_Name']);
            $objActSheet->setCellValue('C' . $this->cur_row, $order['Address_Mobile']);
            $objActSheet->setCellValue('D' . $this->cur_row, date("Y-m-d H:i:s", $order['Order_CreateTime']));
            $objActSheet->setCellValue('E' . $this->cur_row, " ".date("Ymd", $order['Order_CreateTime']) . $order['Order_ID']);
            $objActSheet->setCellValue('F' . $this->cur_row, $order['Order_TotalPrice']);
            $objActSheet->setCellValue('G' . $this->cur_row, $order['Order_Remark']);
            $Shipping = json_decode(htmlspecialchars_decode($order['Order_Shipping']), true);
            if (!empty($Shipping)) {
                $Shipping_Name = !empty($Shipping["Express"]) ? $Shipping["Express"] : '';
            } else {
                $Shipping_Name = '';
            }
            
            /*循环输出产品*/
            $cart_num = count($order['Order_CartList']);
            if ($cart_num > 0) {
                foreach ($order['Order_CartList'] as $key => $item) {
                    foreach ($item as $k => $v) {
                        $property = "";
						if (!empty($v["Property"])) {
							$property = $v["Property"]["shu_value"];
						}else{
							$property = '';
						}
                        $objActSheet->setCellValue('H' . $this->cur_row, !empty($v['ProductsName']) ? $v['ProductsName'] : '');
                        $objActSheet->setCellValue('I' . $this->cur_row, $property);
                        $objActSheet->setCellValue('J' . $this->cur_row, !empty($v['Qty']) ? $v['Qty'] : '');
                        $objActSheet->setCellValue('K' . $this->cur_row, !empty($v['ProductsPriceX']) ? $v['ProductsPriceX'] : '');
                        if ($cart_num > 1) {
                            $this->cur_row = $this->cur_row + 1;
                        }
                    }
                }
            }
			$objActSheet->setCellValue('L' . $this->cur_row, $Shipping_Name);
            $objActSheet->setCellValue('M' . $this->cur_row, $order['receiver_address']);
            $this->cur_row = $this->cur_row + 1;
        }
        $filename = 'order_detail_list_biz' . $beinTime . '_' . $endTime . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
    }
	
	/*导出所有客户列表*/
    public function user_list($data) {
        $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['user_list']);
        $this->cur_row = 3;
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        $objActSheet->getColumnDimension('A')->setWidth(7);
        $objActSheet->getColumnDimension('B')->setWidth(12);
        $objActSheet->getColumnDimension('C')->setWidth(17);
        $objActSheet->getColumnDimension('D')->setWidth(16);
        $objActSheet->getColumnDimension('E')->setWidth(30);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(12);
        $objActSheet->getColumnDimension('H')->setWidth(24);
        $objActSheet->getColumnDimension('I')->setWidth(14);
        $objActSheet->getColumnDimension('J')->setWidth(24);        
        $objActSheet->getColumnDimension('K')->setWidth(24);        

        foreach ($data as $key => $user) {
			
            $objActSheet->getStyle('C' .  $this->cur_row)->getNumberFormat()->setFormatCode
            (PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            //设置align
            foreach(['A','B','C','D','E','F','G','H','I','J','K'] as $k => $v){
                $objActSheet->getStyle($v .  $this->cur_row)->getAlignment()->setHorizontal
                (PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
            $objActSheet->setCellValue('A' . $this->cur_row, ($key + 1));
            $objActSheet->setCellValue('B' . $this->cur_row, $user['source']);
            $objActSheet->setCellValue('C' . $this->cur_row, $user['User_No']);
            $objActSheet->setCellValue('D' . $this->cur_row, $user['User_Mobile']);            
            $objActSheet->setCellValue('E' . $this->cur_row, $user['User_NickName']);
            $objActSheet->setCellValue('F' . $this->cur_row, $user['User_Name']);
            $objActSheet->setCellValue('G' . $this->cur_row, $user['User_Cost']);
            $objActSheet->setCellValue('H' . $this->cur_row, $user['User_Level']);
            $objActSheet->setCellValue('I' . $this->cur_row, $user['User_Integral']);            
            $objActSheet->setCellValue('J' . $this->cur_row, $user['User_Money']);
            $objActSheet->setCellValue('K' . $this->cur_row, $user['regTime']);

            $this->cur_row = $this->cur_row + 1;
        }
        $filename = 'user_list' . date("YmdHis", time()) . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
    }
	
    /*导出所有销售记录明细列表*/
    public function sales_record_list($beinTime, $endTime, $data) {
        $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['sales_record_list']);
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //设置日期
        $objActSheet->setCellValue('B2', $beinTime);
        $objActSheet->setCellValue('B3', $endTime);
        //输出信息
        //填充数据
        $i = 0;
        $_STATUS = array(
            '未结算',
            '已结算'
        );
        foreach ($data as $key => $value) {
            $i++;
            $objActSheet->setCellValue('A' . $this->cur_row, $i);
            $objActSheet->setCellValue('B' . $this->cur_row, $value['Biz_Name']);
            $objActSheet->setCellValue('C' . $this->cur_row, $value['orderno']);
            $objActSheet->setCellValue('D' . $this->cur_row, $value['product_amount']);
            $objActSheet->setCellValue('E' . $this->cur_row, $value['Order_Shipping']);
            $objActSheet->setCellValue('F' . $this->cur_row, $value['Order_Amount']);
            $objActSheet->setCellValue('G' . $this->cur_row, $value['Order_Diff']);
            $objActSheet->setCellValue('H' . $this->cur_row, $value['Order_TotalPrice']);
            $objActSheet->setCellValue('I' . $this->cur_row, $value['web']);
            $objActSheet->setCellValue('J' . $this->cur_row, $value['bonus']);
            $objActSheet->setCellValue('K' . $this->cur_row, $value['supplytotal']);
            $objActSheet->setCellValue('L' . $this->cur_row, $_STATUS[$value["Record_Status"]]);
            $objActSheet->setCellValue('M' . $this->cur_row, date("Y-m-d H:i:s", $value["Record_CreateTime"]));
            $this->cur_row = $this->cur_row + 1;
        }
        $filename = 'sales_record_list' . $beinTime . '_' . $endTime . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
    }
    
    /*导出所有拼团明细列表*/
    public function order_pintuan_list($beinTime, $endTime, $data) {
        
        $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['users_pintuan_list']);
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //设置日期
        $objActSheet->setCellValue('B2', $beinTime);
        $objActSheet->setCellValue('B3', $endTime);
        //输出信息
        //填充数据
        $orderStatus = array(
            '0'=>'待确认',
            '1'=>'未付款',
            '2'=>'已付款',
            '3'=>'已发货',
            '4'=>'完成',
            '5'=>'退款中',
            '6'=>'已退款',
            '7'=>'手动退款成功'
        );
        
        foreach ($data as $key => $order) {
            $order['Order_TotalPrice'] = $order['Order_TotalPrice'] >= $order['Back_Amount'] ? ($order['Order_TotalPrice'] - $order['Back_Amount']) : 0;
            $objActSheet->setCellValue('A' . $this->cur_row, ($key + 1));
            $objActSheet->setCellValue('B' . $this->cur_row, $order['Users_ID']);
            $objActSheet->setCellValue('C' . $this->cur_row, $order['Address_Name']);
            $objActSheet->setCellValue('D' . $this->cur_row, $order['Address_Mobile'].' ');
            $objActSheet->setCellValue('E' . $this->cur_row, date("Y-m-d H:i:s", $order['Order_CreateTime']));
            $objActSheet->setCellValue('F' . $this->cur_row, $order['Order_Code'].' ');
            $objActSheet->setCellValue('G' . $this->cur_row, $order['Order_TotalPrice']);
            $objActSheet->setCellValue('H' . $this->cur_row, $order['Order_PaymentMethod']);
            $Shipping = json_decode(htmlspecialchars_decode($order['Order_Shipping']), true);
            if (!empty($Shipping)) {
                $Shipping_Name = !empty($Shipping["Express"]) ? $Shipping["Express"] : '';
            } else {
                $Shipping_Name = '';
            }
    
            /*循环输出产品*/
            $cart_num = count($order['Order_CartList']);
            if ($cart_num > 0) {
                $temp[] = $order['Order_CartList'];
                foreach ($temp as $key => $item) {
                        $objActSheet->setCellValue('I' . $this->cur_row, !empty($item['ProductsName']) ? $item['ProductsName'] : '');
                        $objActSheet->setCellValue('J' . $this->cur_row, $orderStatus[$order['Order_Status']]);
                        $objActSheet->setCellValue('K' . $this->cur_row, !empty($item['Qty']) ? $item['Qty'] : 1);
                        $objActSheet->setCellValue('L' . $this->cur_row, $order['Order_Type']=='dangou'?$item['ProductsPriceD']:$item['ProductsPriceT']);
                        
                }
            }
            $objActSheet->setCellValue('M' . $this->cur_row, $Shipping_Name);
            $objActSheet->setCellValue('N' . $this->cur_row, $order['receiver_address']);
            $this->cur_row = $this->cur_row + 1;
        }
        $filename = 'users_pintuan_list' . $beinTime . '_' . $endTime . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
    }
    
	
	//导入虚拟卡密数据
    public function importExcel($url, $UsersID, $BizID, $type=0){
		global $capsule;
        $this->_objPHPExcel = $this->_objReader->load($url);
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        $Row = $objActSheet->getHighestRow(); // 取得总行数
        $step = 500;
        $count = floor($Row/$step) + 1;
        if($count>1){
            $flag = true;
            $data = [];
            for($num=0;$num<=$count;$num++) {
                $currentIndex = $num == 0 ? 3 : ($num * $step + 1);
                for ($i = $currentIndex; $i <= ($num + 1) * $step; $i++) {
                    $User = $objActSheet->getCell('A'.$i)->getValue();
                    $Pwd = $objActSheet->getCell('B'.$i)->getValue();
                    if($User && $Pwd){
                        $data[$num][] = [
                            'Card_Name' => $User,
                            'User_ID' => $UsersID,
                            'Card_Password' => $Pwd,
                            'Type_Id' => $type,
							'Biz_ID' => $BizID,
							'Card_UpadteTime' => 0,
                            'Card_CreateTime' => time()
                        ];
                    }else{
                        break 2;
                    }
                }
            }
            for($num=0;$num<=$count;$num++) {
                if(!empty($data[$num])){
                    $flag = $flag && $capsule->table('shop_virtual_card')->insert($data[$num]);
                }
            }
            return $flag;
        }else{
            $data = [];
            for($i=3;$i<=$Row;$i++){
                $User = $objActSheet->getCell('A'.$i)->getValue();
                $Pwd = $objActSheet->getCell('B'.$i)->getValue();
                $data[] = [
                    'Card_Name' => $User,
                    'Card_Password' => $Pwd,
                    'User_ID' => $UsersID,
                    'Type_Id' => $type,
					'Biz_ID' => $BizID,
					'Card_UpadteTime' => 0,
                    'Card_CreateTime' => time()
                ];
            }
			try{
				return $capsule->table('shop_virtual_card')->insert($data);
			}catch(Exception $e){
				echo $e->getMessage();exit;
				return false;
			}
            
        }
    }
    
    /**
     *输出这个表格
     *
     */
    private function __outputExcel($objPHPExcel, $type) {
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-r$code=idate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="' . $type . '"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
	/*edit数据统计20160405--start--*/
	/*导出所有订单明细列表*/
    public function order_staticstic_list($beinTime, $endTime, $data) {
        $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['order_staticstic_list']);
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //设置日期
        $objActSheet->setCellValue('B2', $beinTime);
        $objActSheet->setCellValue('B3', $endTime);
        //输出信息
        //填充数据
        foreach ($data as $key => $order) {
            $order['Order_TotalPrice'] = $order['Order_TotalPrice'] >= $order['Back_Amount'] ? ($order['Order_TotalPrice'] - $order['Back_Amount']) : 0;
            $objActSheet->setCellValue('A' . $this->cur_row, ($key + 1));
            $objActSheet->setCellValue('B' . $this->cur_row, $order['dis_Name']);
            $objActSheet->setCellValue('C' . $this->cur_row, $order['Address_Name']);
            $objActSheet->setCellValue('D' . $this->cur_row, $order['Address_Mobile']);
            $objActSheet->setCellValue('E' . $this->cur_row, date("Y-m-d H:i:s", $order['Order_CreateTime']));
            $objActSheet->setCellValue('F' . $this->cur_row, date("Ymd", $order['Order_CreateTime']) . $order['Order_ID']);
            $objActSheet->setCellValue('G' . $this->cur_row, $order['Order_TotalPrice']);
            $objActSheet->setCellValue('H' . $this->cur_row, $order['Order_Remark']);
            $Shipping = json_decode(htmlspecialchars_decode($order['Order_Shipping']), true);
            if (!empty($Shipping)) {
                $Shipping_Name = !empty($Shipping["Express"]) ? $Shipping["Express"] : '';
            } else {
                $Shipping_Name = '';
            }
            
            /*循环输出产品*/
            $cart_num = count($order['Order_CartList']);
            if ($cart_num > 0) {
                foreach ($order['Order_CartList'] as $key => $item) {
                    foreach ($item as $k => $v) {
                        $property = "";
                        if(!empty($v["Property"])){
                            foreach ($v["Property"] as $Attr_ID => $Attr) {
                                $property.= $Attr['Name'] . ': ' . $Attr['Value'] . ';  ';
                            }
                        }
                        $objActSheet->setCellValue('I' . $this->cur_row, !empty($v['ProductsName']) ? $v['ProductsName'] : '');
                        $objActSheet->setCellValue('J' . $this->cur_row, $property);
                        $objActSheet->setCellValue('K' . $this->cur_row, !empty($v['Qty']) ? $v['Qty'] : '');
                        $objActSheet->setCellValue('L' . $this->cur_row, !empty($v['ProductsPriceX']) ? $v['ProductsPriceX'] : '');
                        if ($cart_num > 1) {
                            $this->cur_row = $this->cur_row + 1;
                        }
                    }
                }
            }
            $objActSheet->setCellValue('M' . $this->cur_row, $Shipping_Name);
            $objActSheet->setCellValue('N' . $this->cur_row, $order['receiver_address']);
            $this->cur_row = $this->cur_row + 1;
        }
        $filename = 'order_staticstic_list' . $beinTime . '_' . $endTime . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
    }
    /*edit数据统计20160405--end--*/
    
    /*edit数据统计20160415--start--*/
    /*导出所有订单明细列表*/
    public function user_staticstic_list($beinTime, $endTime, $data,$searchtype,$shoptype='') {
        if($searchtype=="shop"){
             $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['user_staticstic_list']);
        }elseif($searchtype=="visit"){
             $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['visit_staticstic_list']);
        }elseif($searchtype=="distributer"){
             $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['distributer_staticstic_list']);
        }elseif($searchtype=="reg" || $searchtype=="purchase" || $searchtype=="nopurchase"){
             $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['users_staticstic_list']);
        }
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //设置日期
        $objActSheet->setCellValue('B2', $beinTime);
        $objActSheet->setCellValue('B3', $endTime);
        if($shoptype=="all"){
           $objActSheet->setCellValue('E1', "全部订单统计表");
        }
        if($shoptype=="fanish"){
           $objActSheet->setCellValue('E1', "已完成订单统计表");
        }
        if($searchtype=="reg"){
           $objActSheet->setCellValue('E1', "会员注册统计表");
        }
        if($searchtype=="purchase"){
           $objActSheet->setCellValue('E1', "会员消费统计表");
        }
        if($searchtype=="nopurchase"){
           $objActSheet->setCellValue('E1', "未消费会员统计表");
           $objActSheet->setCellValue('A4', "会员ID");
           $objActSheet->setCellValue('B4', "会员卡号");
           $objActSheet->setCellValue('C4', "会员昵称");
           $objActSheet->setCellValue('D4', "会员手机");
           $objActSheet->setCellValue('E4', " ");
        }
        if($searchtype=="reg" || $searchtype=="purchase"){
            
            foreach ($data as $key => $order) {
            $objActSheet->setCellValue('A' . $this->cur_row, date('y-m-d',$key));
                if(is_array($order)){
                        foreach($order as $k=>$v){
                                $objActSheet->setCellValue('B' . $this->cur_row, ($v['User_ID']));
                                $objActSheet->setCellValue('C' . $this->cur_row, ($v['User_No']));
                                 $objActSheet->setCellValue('D' . $this->cur_row, ($v['User_NickName']));
                                $objActSheet->setCellValue('E' . $this->cur_row, ($v['User_Mobile']));
                                $this->cur_row = $this->cur_row + 1;
                        }	
                }else{
                        $objActSheet->setCellValue('B' . $this->cur_row, $order);
                }
                $this->cur_row = $this->cur_row + 1;
            }
            
        }elseif($searchtype=="nopurchase"){
            foreach ($data as $key => $order) {
              $objActSheet->setCellValue('A' . $this->cur_row, $order['User_ID']);  
              $objActSheet->setCellValue('B' . $this->cur_row, $order['User_No']);    
              $objActSheet->setCellValue('C' . $this->cur_row, $order['User_NickName']);  
              $objActSheet->setCellValue('D' . $this->cur_row, $order['User_Mobile']);  
              $this->cur_row = $this->cur_row + 1;
            }
            
        }elseif($searchtype=="distributer"){
            foreach ($data['date'] as $key => $order) {  //日期
                $objActSheet->setCellValue('A' . $this->cur_row, $order);
                $this->cur_row = $this->cur_row + 1;
            }
            $this->cur_row = 5;
            foreach ($data['count'][0]['data'] as $k1 => $v1) {
                $objActSheet->setCellValue('B' . $this->cur_row, $v1);
                $this->cur_row = $this->cur_row + 1;
            }
        }else{
            foreach ($data as $key => $order) {
            $objActSheet->setCellValue('A' . $this->cur_row, date('y-m-d',$key));
                if(is_array($order)){
                        foreach($order as $k=>$v){
                            $objActSheet->setCellValue('B' . $this->cur_row, ($v['Order_ID']));
                            $this->cur_row = $this->cur_row + 1;
                        }	
                }else{
                        $objActSheet->setCellValue('B' . $this->cur_row, $order);
                }
                $this->cur_row = $this->cur_row + 1;
            }
        }
         
        $filename = 'user_staticstic_list' . $beinTime . '_' . $endTime . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
    }
    public function sales_staticstic_list($beinTime, $endTime, $data,$searchtype) {
       
        if($searchtype=="sales"){
             $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['sales_staticstic_list']);
        }elseif($searchtype=="commission"){
             $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['commission_staticstic_list']);
        }
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //设置日期
        $objActSheet->setCellValue('B2', $beinTime);
        $objActSheet->setCellValue('B3', $endTime);
        foreach ($data['date'] as $key => $order) {  //日期
			
            $objActSheet->setCellValue('A' . $this->cur_row, $order);
            $this->cur_row = $this->cur_row + 1;
        }
        $this->cur_row = 5;
        
        foreach ($data['count'][0]['data'] as $k1 => $v1) {
           $objActSheet->setCellValue('B' . $this->cur_row, $v1);
           $this->cur_row = $this->cur_row + 1;
        }
        
        if($searchtype=="sales" || $searchtype=="commission"){
            $this->cur_row = 5;
            foreach ($data['count'][1]['data'] as $k1 => $v1) {
                $objActSheet->setCellValue('C' . $this->cur_row, $v1);
                $this->cur_row = $this->cur_row + 1;
            }
             $this->cur_row = 5;
            foreach ($data['count'][2]['data'] as $k1 => $v1) {
                $objActSheet->setCellValue('D' . $this->cur_row, $v1);
                $this->cur_row = $this->cur_row + 1;
            }
        }
        if($searchtype=="sales"){
            $this->cur_row = 5;
            foreach ($data['count'][3]['data'] as $k1 => $v1) {
                $objActSheet->setCellValue('E' . $this->cur_row, $v1);
                $this->cur_row = $this->cur_row + 1;
            }    
        }
        
        $filename = 'user_staticstic_list' . $beinTime . '_' . $endTime . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
    }
     public function product_staticstic_list($data) {
        $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['product_staticstic_list']);
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //设置日期
        $nowtime = date('Y-m-d',time());
        $objActSheet->setCellValue('B2',date('Y-m-d',time()) );
        foreach ($data as $k => $v) {   
            $objActSheet->setCellValue('A' . $this->cur_row, $v['Products_ID']);
            $objActSheet->setCellValue('B' . $this->cur_row, $v['Products_Name']);
            $objActSheet->setCellValue('C' . $this->cur_row, $v['Products_Count']);
            $objActSheet->setCellValue('D' . $this->cur_row, $v['Products_Sales']);
            $objActSheet->setCellValue('E' . $this->cur_row, $v['sale']);
            $objActSheet->setCellValue('F' . $this->cur_row, $v['back']);
            $objActSheet->setCellValue('G' . $this->cur_row, $v['click_count']);
            $objActSheet->setCellValue('H' . $this->cur_row, !empty($v['agv'])?$v['agv']:'无评分');
            $this->cur_row = $this->cur_row + 1;
        }
        $filename = 'product_staticstic_list'.$nowtime . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
         
    }
    public function area_staticstic_list($data) {        
        $this->_objPHPExcel = $this->_objReader->load($this->template_path . $this->templates['area_staticstic_list']);
        $objActSheet = $this->_objPHPExcel->getActiveSheet();
        //设置日期
        $nowtime = date('Y-m-d',time());
        $objActSheet->setCellValue('B2',date('Y-m-d',time()) );
        foreach ($data as $k => $v) {   
            $objActSheet->setCellValue('A' . $this->cur_row, $v[0]);
            $objActSheet->setCellValue('B' . $this->cur_row, $v[1]);
            $objActSheet->setCellValue('C' . $this->cur_row, $v[2]."%");
            $this->cur_row = $this->cur_row + 1;
        }
        $filename = 'product_staticstic_list'.$nowtime . '.xls';
        $this->__outputExcel($this->_objPHPExcel, $filename);
         
    }
	/*edit数据统计20160415--end--*/
}