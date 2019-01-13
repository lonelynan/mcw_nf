<?php
                    if (isset($_GET["UsersID"])) {
                        $UsersID = $_GET["UsersID"];
                    } elseif (isset($_SESSION["Users_ID"])) {
                        $UsersID = $_SESSION['Users_ID'];
                    } else {
                        $UsersID = $Users_ID;
                    }
                    $Pinyin = new Utf8pinyin();
                    $data = array("顺丰","申通","中通","圆通","韵达","邮政","EMS","百世汇通","宅急送","天天快递");
                    //添加默认开关
                    foreach ($data as $value) {
                        $data2 = array(
                            "Users_ID"=>$UsersID,
                            "Shipping_Name"=>$value,
                            "Shipping_Code"=>$Pinyin->str2py($value,TRUE,TRUE),
                            "Shipping_Business"=>'express',
                            "Shipping_Status"=>1,
                            "Biz_ID"=>$_SESSION["BIZ_ID"],
                            "shipping_CreateTime"=>time(),
                        );
                    $DB->Add("shop_shipping_company",$data2);
                    }