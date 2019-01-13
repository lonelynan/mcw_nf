<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
use Illuminate\Database\Capsule\Manager as DB;
S($UsersID . "HTTP_REFERER", $_SERVER["REQUEST_URI"]);
require_once (CMS_ROOT . '/include/library/wechatuser.php');

$pintuanshop = [];
$sessionid = session_id();
$cart = [];
$myRedis = new Myredis($DB, 7);
$redisCart = $myRedis->getCartListValue($UsersID.'PTCartList', $UserID);
if(!empty($redisCart)){
    $cart = json_decode($redisCart,true);
}

if(empty($cart)){
    sendAlert("购物车中没有商品","/api/{$UsersID}/pintuan/",2);
}

foreach($cart as $res){
    $pintuanshop['teamid'] = $res['teamid'];
    $pintuanshop['goodsid'] = $res['goodsid'];
    $pintuanshop['goods_name'] = $res['goods_name'];
    $pintuanshop['goods_num'] = $res['goods_num'];
    $pintuanshop['goods_price'] = $res['goods_price'];
    $pintuanshop['is_vgoods'] = $res['is_vgoods'];
    $pintuanshop['is_One'] = $res['is_One'];
    $result = DB::table("pintuan_products")->where(["Products_ID" => $pintuanshop['goodsid'], "Users_ID" => $UsersID])->first();
    $pintuanshop['goods_name'] = $result['Products_Name'];
    $pintuanshop['Products_Parameter'] = $result['Products_Parameter'];
    $pintuanshop['Products_PriceT'] = $result['Products_PriceT'];
    $pintuanshop['Products_PriceD'] = $result['Products_PriceD'];
    $pintuanshop['Products_Profit'] = $result['Products_Profit'];
    $pintuanshop['Products_Distributes'] = $result['Products_Distributes'];
    $pintuanshop['Products_JSON'] = $result['Products_JSON'];
    $pintuanshop['Products_BriefDescription'] = $result['Products_BriefDescription'];
    $pintuanshop['Products_CreateTime'] = $result['Products_CreateTime'];
    $pintuanshop['Products_Count'] = $result['Products_Count'];
    $pintuanshop['Is_Draw'] = $result['Is_Draw'];
    $pintuanshop['products_IsHot'] = $result['products_IsHot'];
    $pintuanshop['Products_pinkage'] = $result['Products_pinkage'];
    $imgobj = json_decode(htmlspecialchars_decode($pintuanshop["Products_JSON"]), true);
    $images = $imgobj['ImgPath'][0];
    $pintuanshop['ImgPath'] = $images;
}

?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>确认订单</title>
</head>
<link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
<link href="/static/api/pintuan/css/biaoqian.css" rel="stylesheet" type="text/css">
<link href="/static/api/pintuan/css/style.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script>
<script type="text/javascript" src="/static/api/pintuan/js/scrolltopcontrol.js"></script>
<script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script>
<body>
	<div class="w">
		<div class="dingdan">
			<span class="fanhui l"><a href="<?php echo isset($_COOKIE['product_detail']) ? "javascript:location.href='{$_COOKIE['product_detail']}';" : 'javascript:history.go(-1)'; ?>"><img src="/static/api/pintuan/images/fanhui.png" width="17" height="17"></a></span> <span
				class="querendd l">确认订单</span>
			<div class="clear"></div>
		</div>
		<div class="dizhi"></div>
		<?php
		      $prices= $pintuanshop['goods_price'];
		          if($pintuanshop['is_One']==0){//单购
		              $prices += $pintuanshop['Products_PriceD'];
		          }else{  //团购
		              $prices += $pintuanshop['Products_PriceT'];
		          }
		          
		?>
		<div class="chanpin1">
			<span class="l"><img src="<?php echo $pintuanshop['ImgPath']?>"></span> <span class="cp l">
				<ul>
					<li><strong><?php echo $pintuanshop['goods_name']?></strong></li>
					<?php 
					    $orgPrice = $prices;
                        ?>
                    <input type="hidden" name="pintuanID" value="<?php echo $pintuanshop['teamid']?>" class='pintuanID' />
				</ul>
				</span> <span class="jiage r"><?php echo $prices;?>/件</span>
		</div>
		<div class="wl1">总价：¥<?php echo $prices; ?></div>
		<div class="clear"></div>
		<div class="wuliu">
			<span class="sr2">请输入手机号码</span> <span class="sr"> <input type="num" class="sr1" id="num" name="number" value="" notnull />
			</span>
		</div>
		<div>
			<input type="submit" value="提交" class="zhifu" id="payfor"/>
		</div>
		<div class="clear"></div>
		<div class="pintuan1">
			<div class="pt">
				<span class="l">拼团玩法</span><span class="r"><a
					href="/api/<?php echo $UsersID;?>/pintuan/liucheng/">查看详情》</a></span>
				<div class="clear"></div>
				<ul>
					<li><span class="l"><img
							src="/static/api/pintuan/images/2p-5_03.png"></span><span
						class="ptt l">选择<br />心仪商品
					</span></li>
					<li><span class="l"><img
							src="/static/api/pintuan/images/2p-5_05.png"></span><span
						class="ptt1 l">支付开团<br />或参团
					</span></li>
					<li><span class="l"><img src="/static/api/pintuan/images/2p-5_07.png"></span><span
						class="ptt l">等待好友<br />参团支付
					</span></li>
					<li><span class="l"><img
							src="/static/api/pintuan/images/2p-5_09.png"></span><span
						class="ptt l">达到人数<br />团购成功
					</span></li>
				</ul>
			</div>
		</div>
	</div>
	<script type="text/javascript">
        $(function(){
            $("#payfor").click(function(){
                var UsersID = "<?=$UsersID ?>";
                var pintuanorder="checkout";
                var pintuanID=$("input[name='pintuanID']").val();
                var mobile = $("input[name='mobile']").val();

                if(mobile =="" || mobile==null) {
                    layer.msg("手机号不能为空！",{icon:1,time:2000});
                    return false;
                }
                var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
                if(!myreg.test(mobile))
                {
                    layer.msg("请输入有效的手机号码！",{icon:1,time:2000});
                    return false;
                }

                $.ajax({
                    url: "/api/"+UsersID+"/pintuan/doOrder/",
                    data: {UsersID:UsersID,"ProductsID":"<?=$pintuanshop['goodsid'] ?>","action":"checkout",'pintuanID':pintuanID,mobile:mobile},
                    type: "POST",
                    dataType: "json",
                    success: function (data) {
                        if(data.status == 1){
                            window.location=data.url;
                        } else {
                            layer.msg(data.msg,{icon:1,time:2000},function(){
                                window.location="/api/"+UsersID+"/pintuan/";
                            });
                            return ;
                        }
                    }
                });

            });
        });
	</script>
</body>
</html>
