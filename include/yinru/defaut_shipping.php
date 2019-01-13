<?php
                    if (isset($_GET["UsersID"])) {
                        $UsersID = $_GET["UsersID"];
                    } elseif (isset($_SESSION["Users_ID"])) {
                        $UsersID = $_SESSION['Users_ID'];
                    } else {
                        $UsersID = $Users_ID;
                    }
                    
                    $data = array(
							'SF' => '顺丰',
							'EMS' => 'EMS',
							'ZJS' => '宅急送',
							'YTO' => '圆通',
							'HTKY' => '百世快递',
							'ZTO' => '中通',
							'YD' => '韵达',
							'STO' => '申通',
							'DBL' => '德邦',
							'UC' => '优速',
							'JD' => '京东',
							'XFEX' => '信丰',
							'QFKD' => '全峰',
							'KYSY' => '跨越速运',
							'ANE' => '安能小包',
							'FAST' => '快捷快递',
							'GTO' => '国通',
							'HHTT' => '天天快递',
							'YZPY' => '邮政快递包裹',
							'ZTKY' => '中铁快运',
							'YZBK' => '邮政国内标快'
					);
                    //添加默认开关
                    foreach ($data as $key => $value) {
                        $data2 = array(
                            "Users_ID"=>$UsersID,
                            "Shipping_Name"=>$value,
                            "Shipping_Code"=>$key,
                            "Shipping_Business"=>'express',
                            "Shipping_Status"=>1,
                            "shipping_CreateTime"=>time(),
                        );
                    $DB->Add("shop_shipping_company",$data2);
                    }