<?php
require_once('global.php');
use Illuminate\Database\Capsule\Manager as DB;

S($UsersID . "HTTP_REFERER", $_SERVER["REQUEST_URI"]);
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/library/wechatuser.php');
setcookie('url_referer', $_SERVER["REQUEST_URI"], time()+3600, '/', $_SERVER['HTTP_HOST']);
$addresslist = array();
$address = array();
$msgisdefault = true;
$pintuans = [];
$address = [];
$address_id = I("addressid", 0);
$where = [
    "Users_ID" => $UsersID,
    "User_ID" => $UserID
];
if($address_id){
    $where["Address_ID"] = $address_id;
    $msgisdefault = false;
}else{
    $where["Address_Is_Default"] = 1;
    $msgisdefault = true;
}
$addresslist = DB::table("user_address")->where($where)->get();
// 地址判断
if (! empty($addresslist)) {
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

    foreach ($cart as $biz_id => $pro_list) {
        foreach ($pro_list as $pro_id => $value) {
            foreach ($value as $attr_id => $attr_value) {
                $Biz_ID = $biz_id;

                $rsProducts = $DB->GetRs('shop_products', 'Products_Count, Products_BriefDescription, Products_Weight, Products_IsVirtual, Products_IsRecieve, Products_IsShippingFree', 'where Users_ID = "' . $UsersID . '" and Products_ID = ' . $pro_id);

                $pintuanshop['teamid'] = $attr_value['teamid'];
                $pintuanshop['goodsid'] = $pro_id;
                $pintuanshop['goods_name'] = $attr_value['ProductsName'];
                $pintuanshop['goods_num'] = $attr_value['Qty'];

                //拼团产品价格
                if (empty($attr_value['Property'])) { //无属性
                    $goods_price = $attr_value['pintuan_pricex'];
                    $pintuanshop['Products_Count'] = $rsProducts['Products_Count'];
                } else {    //有属性
                    $goods_price = $attr_value['Property']['shu_pricesimp'];
                    $pintuanshop['Products_Count'] = $attr_value['Property']['shu_conut'];
                }
                $pintuanshop['goods_price'] = $goods_price; //单价

                $pintuanshop['ImgPath'] = $attr_value['ImgPath'];
                $pintuanshop['Products_BriefDescription'] = $rsProducts['Products_BriefDescription'];
                $pintuanshop['Products_Weight'] = $rsProducts['Products_Weight'];
                $pintuanshop['Products_IsShippingFree'] = $rsProducts['Products_IsShippingFree'];

                //是否虚拟
                $pintuanshop['Products_IsVirtual'] = $rsProducts["Products_IsVirtual"];
                $pintuanshop['Products_IsRecieve'] = $rsProducts["Products_IsRecieve"];
            }
        }
    }

    if(empty($pintuanshop)){
        sendAlert("购物车中没有商品","/api/{$UsersID}/pintuan/",2);
    }
    //检测是否参加过拼团
    $flagTeam = DB::table("pintuan_teamdetail")->where(["teamid" => $pintuanshop['teamid'], "userid" => $UserID])->first();
    if (!empty($flagTeam)) {
        sendAlert("您已经参加过本团了","/api/{$UsersID}/pintuan/",2);
    }
    //$Biz_ID = $pintuanshop['Biz_ID'];
} else {
    S($UsersID."IS_NEW_USER", 1);
    S($UsersID."NEW_USER_REFER", $_SERVER["REQUEST_URI"]);
    if($UserID){
        header("location:/api/" . $UsersID . "/pintuan/my/address/" . (empty($TypeID) ? '' : $TypeID . '/') . "?wxref=mp.weixin.qq.com");
    }
    exit();
}
$rsBiz = DB::table("biz")->where(["Biz_ID" => $Biz_ID ])->first();
$shipping_company_dropdown = get_front_shiping_company_dropdown($UsersID, $rsBiz);
$Shipping_ID = ! empty($rsBiz['Default_Shipping']) ? $rsBiz['Default_Shipping'] : 0;
$Shipping_Name = '';
if ($Shipping_ID > 0 && empty($shipping_company_dropdown)) {
    $Shipping_Name = isset($shipping_company_dropdown[$Shipping_ID])?$shipping_company_dropdown[$Shipping_ID]:"";
}
$Default_Shipping = $rsBiz['Default_Shipping'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>确认订单</title>
    <link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/biaoqian.css" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/style.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/scrolltopcontrol.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script>
    <style>
    .disable { background:#999; }
    </style>
</head>
<body>
<div class="dingdan">
  <span class="fanhui l"><a href="<?php echo isset($_COOKIE['product_detail']) ? "javascript:location.href='{$_COOKIE['product_detail']}';" : 'javascript:history.go(-1)'; ?>"><img src="/static/api/pintuan/images/fanhui.png" width="17px" height="17px"></a></span>
  <span class="querendd l">确认订单</span>
  <div class="clear"></div>
</div>
	<div class="w">
		<div>
    		<div class="dz l">送至</div>
    		<div class="dz1 l">
            <?php
            $area_json = read_file($_SERVER["DOCUMENT_ROOT"].'/data/area.js');
            $area_array = json_decode($area_json,TRUE);
            $province_list = $area_array[0];
            $address_id = 0;
            foreach ($addresslist as $r=>$d){
                $Province = '';
                if(!empty($d['Address_Province'])){
                    $Province = $province_list[$d['Address_Province']].' ';
                }
                $City = '';
                if(!empty($d['Address_City'])){
                    $City = isset($area_array['0,'.$d['Address_Province']][$d['Address_City']])?$area_array['0,'.$d['Address_Province']][$d['Address_City']].' ':'';
                }

                $Area = '';
                if(!empty($d['Address_Area'])){
                    $Area = isset($area_array['0,'.$d['Address_Province'].','.$d['Address_City']][$d['Address_Area']])?$area_array['0,'.$d['Address_Province'].','.$d['Address_City']][$d['Address_Area']]:' ';
                }
                $address_id = $d['Address_ID'];
    			?>
    			<ul>
    				<li><div class="l"><?php echo $d['Address_Name']?>&nbsp;&nbsp;<?php echo $d['Address_Mobile']?></div>
    					<?php if($msgisdefault){?><div class="mr_moren l">默认</div><?php } ?>
    					<div class="clear"></div>
    				</li>
    				<li><?php echo $Province.$City.$Area.$d['Address_Detailed']?></li>
    			</ul>
    			<input type="hidden" name="AddressID" value="<?php echo $d['Address_ID']?>" class="AddressID" />
    			<?php }?>
    		</div>
    		<div class="more r">
    			<a href="/api/<?php echo $UsersID?>/pintuan/my/address/">></a>
    		</div>
		</div>
		<div class="clear"></div>
		<div class="dizhi"></div>
 		<?php
            if (! empty($pintuanshop)) {
                $prices= $pintuanshop['goods_price'];
        ?>
		<div class="chanpin1">
			<span class="l"><img style="width:100px;" src="<?php echo $pintuanshop['ImgPath'];?>"/></span>
			<span class="cp l" style="width: 50%;">
				<ul>
					<li><strong><?php echo $pintuanshop['goods_name']?></strong></li>
					<?php 
					    $orgPrice = $prices;
                    ?>
					<li><?php echo $pintuanshop['Products_BriefDescription']; ?></li>
					<input type="hidden" name="orgPrice" value="<?php echo $orgPrice?>" />
					<input type="hidden" name="pintuanID" value="<?php echo $pintuanshop['teamid']?>" class='pintuanID' />
				</ul>
			</span>
			<span class="jiage r"><?php echo $pintuanshop['goods_price']?>元/件</span>
		</div>
		<div class="wuliu">
			<div class="wt">选择物流</div>
			<div class="wl">
				<ul>
  				<?php
  				    $wuliuid = 0;
  				    $fee = 0;
  				    $defee = 0;
                    if (! empty($shipping_company_dropdown)) {
                        foreach ($shipping_company_dropdown as $key => $item) {
                            if($pintuanshop['Products_IsShippingFree']<=0){ //有运费
                                $fee = getShipFee($key,$UsersID,$Biz_ID,$pintuanshop['Products_Weight']);
                                if($Default_Shipping == $key){  //设置默认快递
                                    $prices += $fee;
                                    $defee = $fee;
                                    $wuliuid = $key;
                                }
                            }else{
                                $wuliuid = 0;
                            }
                    ?>
            		<li>
            			<span class="l">
            				<img src="/static/api/pintuan/images/<?=($Default_Shipping == $key)?'2p-5_08.png':'2p-5_06.png';?>">
            				<input type="radio" shipping_name="<?=$item?>" <?=($Default_Shipping == $key)?'checked':'';?> name="Shiping_ID" value="<?=$key?>" fee="<?php echo $fee?$fee:0 ?>"/>
            			</span>
            			<span class="wlt l"><?php echo $item;?></span>
            		</li>
            	<?php
                        }
                    }
                ?>
				</ul>
			</div>
			<script type="text/javascript">
			$(".wuliu .wl .l input").hide();
			$(".wuliu .wl li").click(function(){
				$(this).find("img").attr("src","/static/api/pintuan/images/2p-5_08.png");
				$(this).siblings().find("img").attr("src","/static/api/pintuan/images/2p-5_06.png");
				$(this).find("input[name='Shiping_ID']").attr("checked","checked");
				var fee = $(this).find("input[name='Shiping_ID']").attr("fee")? parseFloat($(this).find("input[name='Shiping_ID']").attr("fee")):0;
    			var orgPrice = parseFloat($("input[name='orgPrice']").val());
    			var totalPrice  = fee + orgPrice;
    			$("input[name='wuliu']").val($(this).find("input[name='Shiping_ID']").val());
    			$("#myfee").html(fee);
    			$("#totalprice").html("总价：¥ "+totalPrice.toFixed(2));
			});
			</script>
		</div>
		<div class="wl1">
			<span class="wl2">运费: ¥ <span id="myfee"><?php echo $defee; ?></span>
			<input type="hidden" name="wuliu" value="<?=$wuliuid ?>"/>
			</span>&nbsp;&nbsp;
			<span id="totalprice">总价：¥ <?php echo $prices;?></span>
		</div>
        <?php
        }
        ?>
		<div class="clear"></div>
	<div>
	<input type="submit" value="提交订单" class="zhifu" id="payfor" />
</div>
<div class="clear"></div>
	<div class="pintuan1">
			<div class="pt">
				<span class="l">拼团玩法</span><span class="r"><a href="/api/<?=$UsersID ?>/pintuan/liucheng/">查看详情》</a></span>
				<div class="clear"></div>
				<ul>
					<li><span class="l"><img src="/static/api/pintuan/images/001_1.png"></span><span class="ptt l">选择<br />心仪商品 </span></li>
					<li><span class="l"><img src="/static/api/pintuan/images/001_2.png"></span><span class="ptt1 l">支付开团<br />或参团 </span></li>
					<li><span class="l"><img src="/static/api/pintuan/images/001_3.png"></span><span class="ptt l">等待好友<br />参团支付 </span></li>
					<li><span class="l"><img src="/static/api/pintuan/images/001_4.png"></span><span class="ptt l">达到人数<br />团购成功 </span></li>
				</ul>
			</div>
	</div>
</div>
<?php /*include 'bottom.php';*/ ?>
</body>
<script type="text/javascript">
    $(function(){
        $("#payfor").click(function(){
            var UsersID = "<?=$UsersID ?>";
            var addressid= $("input[name='AddressID']").val();
            var ProductsID="<?=$pintuanshop['goodsid']?>";
            var pintuanID=$('.pintuanID').val();
            var wuliu=$('input[name="wuliu"]').val();
            if(wuliu==undefined || wuliu==null || wuliu==""){
               layer.msg("不能回退进行订单提交!",{icon:1,time:2000},function(){
                    window.location = "/api/"+UsersID+"/pintuan/orderlist/1/";
               });
               return false;
            }
            if(localStorage.getItem(UsersID + "MyAddressID")){
                addressid=localStorage.getItem(UsersID + "MyAddressID");
                localStorage.removeItem(UsersID + "MyAddressID");
            }
            var obj = $(this);
            obj.attr("disabled",true);
            obj.addClass("disable");
            $.ajax({
              url: "/api/"+UsersID+"/pintuan/doOrder/",
                data: {UsersID:UsersID,"Address_ID":addressid,"ProductsID":ProductsID,"action":"checkout",'pintuanID':pintuanID,"wuliu":wuliu},
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
</html>



