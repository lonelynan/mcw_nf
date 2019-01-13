<?php
use Illuminate\Database\Capsule\Manager as DB;

function doUse($UsersID, $Products_Relation_ID)
{
    $vcard = DB::table("shop_virtual_card")->where(["User_ID" => $UsersID, "Products_Relation_ID" =>
        $Products_Relation_ID, "Card_Status" => 0])->get();
    if(!empty($vcard)){
        $cardName = "";
        $cardlist = [];
        foreach ($vcard as $k => $v)
        {
            $cardName = $v['Card_Name'];
            $cardlist[] = $v['Card_Id'];
            break;
        }
        DB::table("shop_virtual_card")->where(["User_ID" => $UsersID])->whereIn("Card_Id", $cardlist)->update(['Card_Status' => 1]);
        return $cardName;
    }
}


function sendAlert($msg,$url="",$timeout=3)
{
    echo '<!doctype html><html><head><title>'.$msg.'</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no,minimal-ui">';
    echo '<script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script><script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script></head><body>';
    echo '<script>layer.msg("'.$msg.'",{icon:1,time:'.($timeout*1000).'},function(){';
    if($url){
        echo 'window.location="'.$url.'";';
    }
    echo '});</script>';
    echo "</body></html>";
    exit;
}

// $type = 1 弹窗，2  是否 按钮的弹窗

function showMsg($msg,$url="",$type=1,$otherurl="")
{
    if($type==1){
        echo '<script>alert("'.$msg.'");';
        if($url){
            echo 'window.location="'.$url.'";';
        }else{
            echo 'history.back();';
        }
    }else{
        echo '<script> var flag = confirm("'.$msg.'");';
        echo 'if(true == flag){';
        if($url){
            echo 'window.location="'.$url.'";';
        }
        echo '}else{';
        echo 'window.location="'.$otherurl.'";';
        echo '}';
    }
    echo '</script>';
    exit;
}

function  sendWXMessage($UsersID,$orderid,$msg,$userid){
    global $DB;
    require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_message.class.php');
    $weixin_message = new weixin_message($DB,$UsersID,$userid);
    $contentStr = $msg;
    $weixin_message->sendscorenotice($contentStr);
}

function payDone($UsersID,$OrderID,$paymethod,$mix = '')
{
    global $DB;

	$sql = "select * from user_order WHERE Users_ID='{$UsersID}' AND Order_ID='{$OrderID}'";
	
	$orderlist = DB::select($sql);
    $isajax = false;
    if($isajax){
        if(empty($orderlist)) {
            die(json_encode([
                'status' => 0,
                'msg' => '订单不存在',
                'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
            ], JSON_UNESCAPED_UNICODE));
        }
    }else{
        if(empty($orderlist)){
            $Data=array(
                "status"=>0,
                "msg"=>'该订单不存在',
                'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
            );
            return $Data;
        }
    }
	$rsOrder=$orderlist[0];
    if(is_bool($mix)){
        $rsTeamDetail =  DB::table("pintuan_teamdetail")->where(["order_id" => $OrderID])->first(["teamid"]);
        if(!empty($rsTeamDetail)){
            $rsTeam = DB::table("pintuan_team")->where(["id" => $rsTeamDetail['teamid']])->first(["id", "teamstatus","teamnum","productid"]);
            $rsProduct = DB::table("shop_products")->where(["Products_ID" => $rsTeam['productid']])->first(["pintuan_people"]);
            if($rsTeam['teamstatus'] == 1 || $rsTeam['teamnum'] == $rsProduct["pintuan_people"]) {
                $Data=[
                    "status"=>1,
                    "msg"=>'拼团成功',
                    'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
                ];
            }else{
                $Data=[
                    'status'=>1,
                    'msg'=>'支付成功',
                    'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
                ];
            }
        }else{
            $Data=[
                'status'=>1,
                'msg'=>'支付成功',
                'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
            ];
        }
        return $Data;
    }

    $order_status = $rsOrder["Order_Status"];
    $order_total = $rsOrder["Order_TotalPrice"];
    $goodsInfo = json_decode($rsOrder['Order_CartList'],true);

    $pro_id = 0;
    $order_process = 0;
    if ($rsOrder['Order_IsVirtual'] == 0 && $rsOrder['Order_IsRecieve'] == 0) {     //实物产品
        $order_process = 0;
    } else if ($rsOrder['Order_IsVirtual'] == 1 && $rsOrder['Order_IsRecieve'] == 0) {     //虚拟产品
        $order_process = 1;
    } else if ($rsOrder['Order_IsVirtual'] == 1 && $rsOrder['Order_IsRecieve'] == 1) {     //卡密产品
        $order_process = 2;
    }
    $goods_id = array_keys($goodsInfo)[0];
    $UserID = $rsOrder['User_ID'];

    if($goods_id){
        $info = DB::table("shop_products")->where(["Users_ID" => $UsersID, "Products_ID" => $goods_id])->first();
        if($info){
            $time = time();
            if($info['pintuan_start_time']>$time){
                die(json_encode(['status'=>0,'msg'=>'活动未开始','url'=>'/api/'.$UsersID.'/pintuan/'],
                    JSON_UNESCAPED_UNICODE));
            }
            if($info['pintuan_end_time']<$time){
                die(json_encode(['status'=>0,'msg'=>'活动已结束','url'=>'/api/'.$UsersID.'/pintuan/'],
                    JSON_UNESCAPED_UNICODE));
            }
        }
    }

    $rsUser = DB::table("user")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->first(["User_Money","User_PayPassword","Is_Distribute","User_Name","User_NickName","Owner_Id","User_Integral","User_Cost"]);
    if($isajax){
        if(!$rsUser) {
            die(json_encode([
                "status" => 0,
                "msg" => '用户信息不存在'
            ], JSON_UNESCAPED_UNICODE));
        }
    }else{
        if(!$rsUser){
            $Data=array(
                "status"=>0,
                "msg"=>'用户信息不存在',
                'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
            );
            return $Data;
        }
    }

    $PaymentMethod = [
        "余额支付" => "0",
        "微支付" => "1",
        "支付宝" => "2",
        "银联支付" => "3",
        "易宝支付" => "4",
        "线下支付" => "5"
    ];
    $payData = [];
    $payment = $PaymentMethod[$paymethod];
	if($payment == 0){
        $pwd = I("PayPassword");
        if(! $pwd){
            return [
                "status"=>0,
                "msg"=>'请输入支付密码'
            ];
        }else if(md5($pwd) != $rsUser["User_PayPassword"]){
            return [
                "status"=>0,
                "msg"=>'支付密码输入错误'
            ];
        }
    }
    //增加资金流水
    if($order_status != 1){
        $Data=[
            "status"=>0,
            "msg"=>'该订单状态不是待付款状态，不能付款',
            'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
        ];
        if($isajax){
            die(json_encode($Data,JSON_UNESCAPED_UNICODE));
        }else{
            return $Data;
        }
    }else{
        //增加资金流水
        if($payment != 5){
            if($order_process==0){
                $payData['order']['Order_Status']=2;
            }elseif($order_process==1){
                $payData['order']['Order_Status']=2;
                //发送短信到用户手机中
                $sendsms = 1;
            }else{
                $payData['order']['Order_Status']=4;
            }
        }else{
            $payData['order']['Order_Status']=1;
        }

        $Card_Name = "";
		
        //拼团开启
        if($rsOrder["Order_Type"]=='pintuan'){
            $tdata = [];
            begin_trans();
            $UserName = $rsUser['User_NickName']?$rsUser['User_NickName']:($rsUser['User_Name']?$rsUser['User_Name']:'');

            // 判断是否使用余额补差
            $Order_Yebc = empty($rsOrder['Order_Yebc']) ? 0 : (float)$rsOrder['Order_Yebc'];
            if ($Order_Yebc > 0) {
                // 写入补差记录
                $Flag = add_user_money_record([
                    'Users_ID' => $UsersID,
                    'User_ID' => $UserID,
                    'Type' => 0,
                    'Amount' => $Order_Yebc,
                    'Total' => $rsUser['User_Money'] - $Order_Yebc,
                    'Note' => "拼团购买使用余额补差支出 -" . $Order_Yebc . " (订单号:" . $rsOrder['Order_ID'] . ")"
                ]);
                if(!$Flag){
                    back_trans();
                }
            }

			if($payment == 0){      //余额支付
                $Flag = DB::table("user")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->update([
                    'User_Money'=>$rsUser['User_Money'] - $order_total,
                    'User_Cost' =>$rsUser['User_Cost'] + $order_total
                ]);
                if(!$Flag){
                    back_trans();
                }
            }else{
                $Flag = DB::table("user")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->update([
                    'User_Cost' =>$rsUser['User_Cost'] + $order_total
                ]);
                if(!$Flag){
                    back_trans();
                }
            }
            $Flag = add_user_money_record([
                'Users_ID' => $UsersID,
                'User_ID' => $UserID,
                'Type' => 0,
                'Amount' => empty($Order_Yebc) ? $order_total : $order_total - $Order_Yebc,
                'Total' => $rsUser ['User_Money'] - $order_total,
                'Note' => $UserName." 使用".$paymethod."  拼团购买支出 -" . (empty($Order_Yebc) ? $order_total : $order_total - $Order_Yebc) . " (订单号:" . $rsOrder['Order_ID'] . ")"
            ]);
			if(!$Flag){
                back_trans();
            }
            $teamFlag = addTeam($OrderID,$UsersID);
			if($teamFlag ===-1){
                //拼团成功
                $tdata = [
                    "status"=>1,
                    "msg"=>'拼团成功',
                    'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
                ];
                $Card_Name = doUse($UsersID,$goods_id);
            }else if($teamFlag===true){
                //支付成功
                $tdata = [
                    'status'=>1,
                    'msg'=>'支付成功',
                    'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
                ];
                $Card_Name = doUse($UsersID,$goods_id);
            }else{
                //拼团发生异常
                $tdata = [
                    'status'=>1,
                    'msg'=>'您已经拼过团了！',
                    'url'=>'/api/'.$UsersID.'/pintuan/'
                ];
                back_trans();
            }
			$Data = array(
                "Order_PaymentMethod"=>$paymethod,
                "Order_PaymentInfo"=>$paymethod,
                "Order_DefautlPaymentMethod"=>$paymethod,
                "transaction_id" =>$mix
            );
            $Data = array_merge($Data,$payData['order']);
            if($order_process == 1){
                $confirm_code=get_virtual_confirm_code($rsOrder["Users_ID"]);
                $Data['Order_Code']=$confirm_code;
            }else if($order_process == 2){
                $Data['Order_Virtual_Cards']=$Card_Name;
            }
			$userOrderFlag = DB::table("user_order")->where(["Users_ID" => $UsersID, "User_ID" => $UserID, "Order_ID" => $OrderID])->update($Data);
			$sflag = addSales($goods_id,$UsersID);
            if(!$userOrderFlag || !$sflag){
                back_trans();
            }
            commit_trans();
			
			if(!empty($Card_Name) && $teamFlag ===-1){
				//拼团成功发送短信
				$getlist = DB::select("select order_id from pintuan_teamdetail where teamid in (select teamid from pintuan_teamdetail where order_id={$rsOrder['Order_ID']})");
				if(!empty($getlist)){
					$idlists = array_map(function($v){
						return $v['order_id'];
					}, $getlist);
					$getlist = DB::table('user_order')->whereIn('Order_ID', $idlists)->get(['Address_Mobile', 'Users_ID', 'Order_ID', 'Order_CreateTime']);
					foreach($getlist as $k => $v){
						$sms_mess = (!empty($tdata['msg']) ? $tdata['msg']:'您拼团成功') . '，订单号为：' . (date("Ymd", $v['Order_CreateTime']).$v["Order_ID"]).'，';
						if($order_process == 1){
							$sms_mess .= "消费卷码为：" . $confirm_code;
						}else if($order_process == 2){
							$vcard = DB::table("shop_virtual_card")->where(["User_Id" => $UsersID, "Products_Relation_ID" => $pro_id, "Card_Name" => $rsOrder['Order_Virtual_Cards']])->first(['Card_Name', 'Card_Password']);
							if(!empty($vcard)){
								$sms_mess .= "虚拟卡号：" . $vcard['Card_Name'] . ",虚拟卡密码：" . $vcard['Card_Password'];
							}
						}
						send_sms($v["Address_Mobile"], $sms_mess, $v["Users_ID"]);
					}
				}
			}
			
            //发送短信
            if(isset($confirm_code)){
                $sms_mess = '您已成功购买商品，订单号'.$rsOrder['Order_ID'];
                send_sms($rsOrder["Address_Mobile"], $sms_mess, $rsOrder["Users_ID"]);
            }

			if($order_process==2){
                require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/balance.class.php');
                $balance_sales= new balance($DB,$UsersID);
                $balance_sales->add_sales($OrderID);
            }
            sendWXMessage($UsersID,$OrderID,'您使用' .$paymethod . '已'.$tdata['msg']."，支付金额：".$order_total."，订单号为：" . (date("Ymd", $rsOrder['Order_CreateTime']).$rsOrder["Order_ID"]),$rsOrder["User_ID"]);
            if($isajax){
                die(json_encode($tdata,JSON_UNESCAPED_UNICODE));
            }else{
                return $tdata;
            }
        }else{      //单购支付（已废弃，拼团单购走商城购买）
            $tdata = [
                'status'=>1,
                'msg'=>'支付成功',
                'url'=>'/api/'.$UsersID.'/pintuan/orderlist/0/'
            ];
            begin_trans();
            $UserName = $rsUser['User_NickName']?$rsUser['User_NickName']:($rsUser['User_Name']?$rsUser['User_Name']:'');
            if($payment == 0){      //余额支付
                $Flag = DB::table("user")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->update([
                    'User_Money'=>$rsUser['User_Money'] - $order_total,
                    'User_Cost' =>$rsUser['User_Cost'] + $order_total
                ]);
                if(!$Flag){
                    back_trans();
                }
            }else{
                $Flag = DB::table("user")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->update([
                    'User_Cost' =>$rsUser['User_Cost'] + $order_total
                ]);
                if(!$Flag){
                    back_trans();
                }
            }
            $Flag = add_user_money_record([
                'Users_ID' => $UsersID,
                'User_ID' => $UserID,
                'Type' => 0,
                'Amount' => $order_total,
                'Total' => $rsUser ['User_Money'] - $order_total,
                'Note' => $UserName."  ".$_POST['PaymentMethod']."  拼团购买支出 -" . $rsOrder['Order_TotalPrice'] . " (订单号:" . $rsOrder['Order_ID'] . ")"
            ]);

            if(!$Flag){
                back_trans();
            }
            $Card_Name = doUse($UsersID,$goods_id);
            $Data = [
                "Order_PaymentMethod"=>$paymethod,
                "Order_PaymentInfo"=>$paymethod,
                "Order_DefautlPaymentMethod"=>$paymethod
            ];
            $Data = array_merge($Data,$payData['order']);
            if($order_process == 1){
                $confirm_code=virtual_randchar();
                $Data['Order_Code']=$confirm_code;
            }else if($order_process == 2){
                $Data['Order_Virtual_Cards']=$Card_Name;
            }
            if($order_process == 1){
                $sms_mess = '您已成功购买商品，订单号'.$Data['Order_Code'].'，消费券码为 '.$confirm_code;
                send_sms($rsOrder["Address_Mobile"], $sms_mess, $rsOrder["Users_ID"]);
            }
            $userOrderFlag = DB::table("user_order")->where(["Users_ID" => $UsersID, "User_ID" => $UserID, "Order_ID" => $OrderID])->update($Data);

            $sflag = addSales($goods_id,$UsersID);
            if(!$userOrderFlag || !$sflag){
                back_trans();
            }
            commit_trans();
            if($order_process==2){
                require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/balance.class.php');
                $balance_sales= new balance($DB,$UsersID);
                $balance_sales->add_sales($OrderID);
            }
            sendWXMessage($UsersID,$OrderID,'您使用' . $paymethod .  '已'.$tdata['msg']."，支付金额：".$order_total."，订单号为：".$rsOrder["Order_Code"],$rsOrder["User_ID"]);
            if($isajax){
                die(json_encode($tdata,JSON_UNESCAPED_UNICODE));
            }else{
                return $tdata;
            }
        }
    }
}

//变更库存（修改的不合理，没有根据订单的产品数量和产品的属性变更产品数量）
function Stock($goodsid,$sellerid,$char='-',$count = 1)
{
    $stock = DB::table("shop_products")->where(["Products_ID" => $goodsid, "Users_ID" => $sellerid])->first(["Products_Count","Products_Sales"]);
    if (empty($stock)) {
        return false;
    }
    $count = (int)$count;
    if($char == '-'){
        if($stock['Products_Count'] < $count){  //库存不足
            return false;
        }
        $data = [ 'Products_Count' => $stock['Products_Count'] - $count ];
    }else{
        $data = [ 'Products_Count' => $stock['Products_Count'] + $count ];
    }
    $flag = DB::table("shop_products")->where(["Products_ID" => $goodsid, "Users_ID" => $sellerid])->update($data);
    if($flag){
        return true;
    }else{
        return false;
    }
}

//变更销量（修改的不合理，没有根据订单的产品数量变更销量）
function addSales($goodsid,$sellerid,$char='+', $count = 1)
{
    $stock = DB::table("shop_products")->where(["Products_ID" => $goodsid, "Users_ID" => $sellerid])->first(["Products_Count","Products_Sales"]);
    if (empty($stock)) {
        return false;
    }
    $count = (int)$count;
    if($char == '-'){
        $data = [ 'Products_Sales' => $stock['Products_Sales'] - $count ];
    }else{
        $data = [ 'Products_Sales' => $stock['Products_Sales'] + $count ];
    }
    $flag = DB::table("shop_products")->where(["Products_ID" => $goodsid, "Users_ID" => $sellerid])->update($data);
    if($flag){
        return true;
    }else{
        return false;
    }
}

//移除购物车
function removeCar()
{
    $sessionid = session_id();
    if(isset($_SESSION[$sessionid.'_cart']) && !empty($_SESSION[$sessionid.'_cart'])){
        $cartlist = unserialize($_SESSION[$sessionid.'_cart']);
        $arr = array_map(function($value){
            if($value['id']) return $value['id'];
        }, $cartlist);
        $idlist = implode(',', $arr);
        $flag = DB::table("pintuan_shop")->whereIn("id", $idlist)->delete();
        if($flag){
            $_SESSION[$sessionid.'_cart'] = "";
        }
    }
}

//生成订单编号
function order_no($no=''){
    /* 选择一个随机的方案 */
    mt_srand((double) microtime() * 1000000);
    $fixlen = 8;
    $len = strlen($no);
    $min = pow(10, $fixlen-$len-1);
    $max = pow(10, $fixlen-$len)-1;

    $no = date('ymd') . mt_rand(10,99)  .$no  .  str_pad(mt_rand($min, $max), $fixlen - $len, '0', STR_PAD_LEFT);
    do{
        $flag = Order::where("Order_Code", $no)->first();
        if(!$flag){
            break;
        }
        $no = date('ymd') . mt_rand(10,99) . $no  .  str_pad(mt_rand($min, $max), $fixlen - $len, '0', STR_PAD_LEFT);
    }while(true);
    return $no;
}


//物流费用计算
function getShipFee($shipid,$sellorid,$bizid,$weight){
    if($shipid){
		$shipping_t = DB::table("shop_shipping_template")->where(["Users_ID" => $sellorid, "Shipping_ID" => $shipid, "Biz_ID" => $bizid])
            ->first(["Template_Status","Template_Content"]);
        if($shipping_t){
            if(!$shipping_t['Template_Status']) return 0;
            if(empty($shipping_t['Template_Content'])) return 0;
            $t_content = json_decode($shipping_t['Template_Content']);
            $default_value=$t_content->express->default;
            if($default_value->start<$weight){
                $fee = $default_value->postage + ($weight - $default_value->start)/$default_value->plus * $default_value->postageplus;
            }else{
                $fee = $default_value->postage;
            }
            return $fee;
        }else{
            return 0;
        }
    }
    return 0;
}

//获取商品信息
function getProducts($goods_id,$sellor_id,$spec_list="")
{
    $goodsInfo = DB::table("pintuan_products")->where(["Products_ID" => $goods_id, "Users_ID" => $sellor_id])->first();
    if($goodsInfo){
        $JSON = json_decode($goodsInfo['Products_JSON'], true);
        return [
            'products'=>$goodsInfo,
            'cartlist'=>[
                "Products_ID"               =>  $goodsInfo['Products_ID'],
                "Products_Count"            =>  $goodsInfo['Products_Count'],
                "ProductsName"              =>  $goodsInfo["Products_Name"],
                "Products_Sales"            =>  $goodsInfo["Products_Sales"],
                "ImgPath"                   =>  !empty($JSON["ImgPath"]) ? $JSON["ImgPath"][0]:"",
                "ProductsPriceD"            =>  $goodsInfo["Products_PriceD"],
                "ProductsPriceT"            =>  $goodsInfo["Products_PriceT"],
                "ProductsWeight"            =>  $goodsInfo["Products_Weight"],
                "Products_Shipping"         =>  $goodsInfo["Products_Shipping"],
                "Products_Business"         =>  $goodsInfo["Products_Business"],
                "Shipping_Free_Company"     =>  $goodsInfo["Shipping_Free_Company"],
                "IsShippingFree"            =>  $goodsInfo["Products_pinkage"], // 1 包邮，0 不包邮
                "ProductsIsShipping"        =>  $goodsInfo["Products_pinkage"],
                "spec_list"                 =>  $spec_list,
                "nobi_ratio"                =>  $goodsInfo["nobi_ratio"],
                "platForm_Income_Reward"    =>  $goodsInfo["platForm_Income_Reward"],
                "area_Proxy_Reward"         =>  $goodsInfo["area_Proxy_Reward"],
                "sha_Reward"                =>  $goodsInfo["sha_Reward"],
                "Products_Profit"           =>  $goodsInfo["Products_Profit"],
                "Is_Draw"                   =>  $goodsInfo["Is_Draw"],
                "order_process"             =>  $goodsInfo["order_process"],
                "pintuan_type"              =>  $goodsInfo["pintuan_type"],
                "pintuan_id"                =>  $goodsInfo["pintuan_id"],
                "people_once"               =>  $goodsInfo["people_once"],
                "people_num"                =>  $goodsInfo["people_num"],
                "num"                       =>  1,
                "Biz_ID"                    =>  $goodsInfo["Biz_ID"],
                "starttime"                 =>  $goodsInfo["starttime"],
                "stoptime"                  =>  $goodsInfo["stoptime"],
                "Products_PriceSd"          =>  $goodsInfo["Products_PriceSd"],
                "Products_PriceSt"          =>  $goodsInfo["Products_PriceSt"],
                "Products_Weight"           =>  $goodsInfo["Products_Weight"],
                "Biz_ID"                    =>  $goodsInfo["Biz_ID"],
                "Products_FinanceRate"      =>  $goodsInfo["Products_FinanceRate"],
                "Products_FinanceType"      =>  $goodsInfo["Products_FinanceType"]
            ]
        ];
    }else{
        return [];
    }
}


function treelist($arr,$spilt='——',$default=0){
    static $i = 0;
    if($arr){
        $list = [];
        $isChild = false;
        foreach($arr as $k=>$v){
            if($v['parent_id']==0){
                $list[$k]['base'] = $v;
            }
            if($default == $v['parent_id']){
                $isChild = true;
            }
        }

        foreach ($list as $k => $v){
            foreach($arr as $key => $val){
                if($v['base']['cate_id']==$val['parent_id']){
                    $list[$k]['child'][] = $val;
                }
            }
        }

        if(!empty($list)){
            $html = "";
            foreach ($list as $k => $v){
                if( $default == $v['base']['cate_id']){
                    $html .= '<option value="'.$v['base']["cate_id"].'" selected >'.$v['base']["cate_name"].'</option>';
                }else{
                    if($isChild==true){
                        $html .= '<option value="'.$v['base']["cate_id"].'" disabled >'.$v['base']["cate_name"].'</option>';
                    }else{
                        $html .= '<option value="'.$v['base']["cate_id"].'"  >'.$v['base']["cate_name"].'</option>';
                    }
                }

                if(!empty($v['child'])){
                    foreach ($v['child'] as $key => $val){
                        if($val['cate_id']==$default){
                            $html .= '<option value="'.$val["cate_id"].'" selected >'.$spilt.$val["cate_name"].'</option>';
                        }else{
                            $html .= '<option value="'.$val["cate_id"].'" >'.$spilt.$val["cate_name"].'</option>';
                        }
                        
                    }
                }
            }
            return $html;
        }
        
    }
}


if ( ! function_exists('getActive'))
{
	function getActive($UsersID, $typeid = 1)
	{
		$time = time();
		if($typeid==5){
			$rsActive = DB::table("active")->where(['Users_ID' => $UsersID, 'Status' => 1, 'Type_ID' => $typeid])->where("starttime", "<", $time)->where("stoptime", ">", $time)->orderBy("starttime","ASC")->orderBy("Active_ID","DESC")->first();
		}else{
			$rsActive = DB::table("active")->where(['Users_ID' => $UsersID, 'Status' => 1, 'Type_ID' => $typeid])->orderBy("starttime","ASC")->orderBy("Active_ID","DESC")->first();
		}
		
		return $rsActive;
	}
}

