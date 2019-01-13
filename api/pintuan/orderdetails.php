<?PHP
require_once('global.php');

use Illuminate\Database\Capsule\Manager as DB;

isset($_GET["orderid"]) || die("缺少必要的参数");

$orderid=I("orderid");
$orederInfo = $capsule->table('user_order')->where(['Order_ID' => $orderid])->first();
$orederInfo || die("订单不存在");
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

$pro_id = 0;
$pro_detail = [];
$order_process = 0;
$goods_price = 0;   //单价
$cartinfo = [];
$Order_CartList = json_decode(htmlspecialchars_decode($orederInfo["Order_CartList"]), true);
if (!empty($Order_CartList)) {
	if(!empty($Order_CartList)){
		foreach($Order_CartList as $productid => $value){
			$pro_id = $productid;
			list($cartid, $carts) = each($value);
			$cartinfo = $carts;
		}
		
	}
}

if($orederInfo['Order_IsVirtual'] && $orederInfo['Order_IsRecieve']==0){
	$order_process = 1;
}else if($orederInfo['Order_IsVirtual'] && $orederInfo['Order_IsRecieve']==1){
	$order_process = 2;
}else{
	$order_process = 0;
}

$goodsInfo = DB::table("shop_products")->where(["Products_ID" => $pro_id, "Users_ID" => $UsersID])->first();

$isPTOK = 0;	//是否拼团成功
$pdetail = $capsule->table('pintuan_teamdetail')->where(['order_id' => $orderid, 'userid' => $orederInfo['User_ID']])->first();
if(!empty($pdetail)){
	$teamid = $pdetail['teamid'];
	$pteam = $capsule->table('pintuan_team')->where(['users_id' => $UsersID, 'id' => $teamid])->first();
	if($pteam['teamstatus']==1){
		$isPTOK = 1;
	}
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>订单详情</title>
    <link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/scrolltopcontrol.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script>
</head>
<body>
<div class="top_back border_bottom">
    <a href="javascript:history.go(-1);"><img src="/static/api/shop/skin/default/images/back_1.png"></a>
    <span>订单详情</span>
</div>
<div class="w">
    <div class="ddxq">
        <span>订单号：<?php echo $orederInfo['Order_ID']; ?></span>
        <span class="ddxqr r"><?php echo $orderStatus[$orederInfo['Order_Status']]; ?></span>
    </div>
    <div class="clear"></div>
    <div class="adr">
        <div class="adr1 l"></div>
        <div class="cp l">
            <ul>
                <?php if($orederInfo['Order_IsVirtual'] == 1 && $isPTOK){?>
                    <li>
                        <div>购买手机：<?=$orederInfo['Address_Mobile']; ?></div>
                    </li>
                    <?php if($order_process == 1){?>
                        <li>消费劵码：
                            <span style="color: #ee0000;font-weight:bold;"><?=$orederInfo['Order_Code']?$orederInfo['Order_Code']:'暂无劵码'; ?></span>
                        </li>
                    <?php }else if($order_process == 2 ){
                        $vcard = DB::table("shop_virtual_card")->where(["User_Id" => $UsersID, "Products_Relation_ID" => $pro_id, "Card_Name" => $orederInfo['Order_Virtual_Cards']])->first();
                        if(!empty($vcard)){
                            ?>
                            <li>
                                <div>虚拟卡号：&nbsp;&nbsp;<?=$vcard['Card_Name']?></div>
                            </li>
                            <li>
                                <div>虚拟密码：&nbsp;&nbsp;<?=$vcard['Card_Password'] ?></div>
                            </li>
                            <?php
                        }
                    }?>
                <?php }else{?>
                    <li>
                        <div><?php echo $orederInfo['Address_Name']."&nbsp;&nbsp;".$orederInfo['Address_Mobile']; ?></div>
                        <div class="clear"></div>
                    </li>
                    <li><?php echo $orederInfo['Address_Detailed']; ?></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="jianju"></div>
    <div class="chanpin1">
        <span class="ll l"><img src="<?= $cartinfo['ImgPath']  ?>"></span>
        <span class="cp l">
              <ul>
                  <li><strong><?= $cartinfo['ProductsName']  ?></strong></li>
                  <li><?= $goodsInfo['Products_BriefDescription'] ?></li>
              </ul>
          </span>
        <span class="jiage r">¥<?= $cartinfo['pintuan_pricex'] ?>/件</span>
        <span class="jiage r">数量：<?= $orederInfo['All_Qty'] ?>件</span>
    </div>
    <div class="clear"></div>
    <div class="jianju"></div>
    <?php
    $shipping_price = 0;
    if($orederInfo['Order_Shipping']){
        $shipping = json_decode($orederInfo['Order_Shipping'],JSON_UNESCAPED_UNICODE);
        $shipping_price = !empty($shipping['Price']) ? $shipping['Price'] : 0;
        if(!empty($shipping)){
            ?>
            <div class="ddxq">
                <span>配送物流</span><span class="r">
         <?php
         echo $shipping['Express']."物流";

         ?></span>
            </div>
        <?php } } ?>
    <div class="jianju"></div>
    <div class="fkuan">
        <span>商品总额</span><span class="ddxqr r">¥<?php echo number_format($orederInfo['Order_TotalPrice']-$shipping_price,2);  ?></span><br/>
        <span class="fkuan1">+运费</span><span class="fkuan2 r">¥ <?php echo number_format($shipping_price,2);  ?></span>
    </div>
    <div class="fkuan">
        <span class="ddxqr r">¥<?php echo $orederInfo['Order_TotalPrice'];  ?></span><span class=" r">实付款：&nbsp;</span><br/>
        <span class="fkuan1 r">下单时间：<?php  echo date("Y-m-d H:i:s",$orederInfo['Order_CreateTime']);?></span>
    </div>
    <div class="clear"></div>
    <?php if($orederInfo['Order_Status']==1){ ?>
        <div class="futime l"><span class="futime1">付款剩余时间：</span><br/><span id="shengyu"></span></div>
        <div class="futime2 r">
            <?php if(($orederInfo['Order_CreateTime']+24*3600)>time()){  ?>
                <span class="fuk1 r"><a href="<?='http://'.$_SERVER['HTTP_HOST'].'/'?>api/<?=$UsersID?>/pintuan/cart/payment/<?=$orderid; ?>/">去付款</a></span>
            <?php } ?>
            <span class="fuk r"><a onclick='return cancelorder("<?php echo $orderid; ?>");'>取消订单</a></span>
        </div>
    <?php } ?>
    <?php /*include 'bottom.php';*/ ?>
    <script>
        minu();
        setInterval(function(){
            minu();
        },"30000");
        function minu()
        {
            var times=<?php echo $orederInfo['Order_CreateTime']; ?>;
            var nowTime = parseInt((new Date().getTime())/1000);
            if((times+3600*24)>nowTime){
                var interval = 3600*24-(nowTime-times);
                var hour = parseInt(interval%86400/3600);
                var minute = parseInt(interval%3600/60);
                var html = hour+"小时"+minute+"分钟";
                $("#shengyu").html(html);
            }else{
                $("#shengyu").html("下单已超过24小时");
            }

        }

        function cancelorder(orderid) {
            if (!confirm('确定取消订单?')) {
                return false;
            }

            var info = {
                'action': 'cancelOrder',
                'UsersID':'<?=$UsersID ?>',
                'orderid': orderid,
                'productID':"<?= $pro_id ?>"
            };

            $.ajax({
                url: "/api/<?=$UsersID ?>/pintuan/ajax/",
                data: info,
                type: "POST",
                dataType: "json",
                success: function (data) {
                    if (data.status == 1) {
                        layer.msg("取消订单成功！", {icon:1, time:2000},function(){
                            location.href="/api/<?php echo $UsersID;  ?>/pintuan/orderlist/1/";
                        });
                    }else{
                        layer.msg("操作失败！", {icon:1, time:2000});
                    }
                }
            });
            return false;
        }
    </script>
</div>
</body>
</html>