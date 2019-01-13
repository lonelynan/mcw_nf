<?PHP
require_once('global.php');
use Illuminate\Database\Capsule\Manager as DB;

$pagesize = 4;
setcookie('url_referer', $_SERVER["REQUEST_URI"], time()+3600, '/', $_SERVER['HTTP_HOST']);
$order_status = I("Order_Status", 0);
$where = ['Users_ID' => $UsersID, 'User_ID' => $UserID];
if ($order_status != 0) {
    $where['Order_Status'] = $order_status;
}
$total = DB::table("user_order")->where($where)->whereRaw("(Order_Type='pintuan' or Order_Type='dangou')")->count("Order_ID");
$totalPage = $total%$pagesize == 0?$total/$pagesize:intval($total/$pagesize)+1;
$page = isset($_POST['page']) && $_POST['page']?$_POST['page']:1;
$offset = ($page-1)*$pagesize;

$orders = DB::table("user_order")->where($where)->whereRaw("(Order_Type='pintuan' or Order_Type='dangou')")->orderBy("Order_Createtime","desc")->orderBy("Order_Status","asc")->offset($offset)->limit($pagesize)->get();

$pintuan_url = "/api/{$UsersID}/pintuan/";
$shop_url = "/api/{$UsersID}/shop/";
if(!empty($orders)){
    foreach($orders as $k => $v){
        $Order_CartList = json_decode(htmlspecialchars_decode($v["Order_CartList"]), true);
		$cartlist = [];
		if(!empty($Order_CartList)){
			foreach($Order_CartList as $productid => $value){
				list($cartid, $carts) = each($value);
				$cartlist = $carts;
			}
		}
		if(!empty($cartlist['Property']['shu_pricesimp'])){
			$cartlist['ProductsPriceX'] = $cartlist['Property']['shu_pricesimp'];
		}
		$orders[$k]['Order_CartList'] = $cartlist;
        //拼团产品价格
        if (empty($pro_detail['Property'])) { //无属性
            $goods_price = !empty($pro_detail['pintuan_pricex']) ? $pro_detail['pintuan_pricex'] : 0;
        } else {    //有属性
            $goods_price = !empty($pro_detail['Property']['shu_pricesimp']) ? $pro_detail['Property']['shu_pricesimp'] : 0;
        }
		$orders[$k]['Order_No'] = date('Ymd', $v['Order_CreateTime']) . $v['Order_ID'];
        $Order_Shipping = !empty($v['Order_Shipping']) ? json_decode($v['Order_Shipping'], true) : '';
        $orders[$k]['Shipping_fee'] = !empty($Order_Shipping['Price']) ? (float)$Order_Shipping['Price'] : 0;

        $ordertip = "";
        switch ($v['Order_Status']) {
            case 0:
                $ordertip = '<span class="fuk r"><a>待确认</a></span>';
                break;
            case 1:
                if($v['Order_PaymentMethod'] == '线下支付'){
                    $ordertip = '<span class="fuk r"><a>线下付款待确认</a></span>';
                }else{
                    $ordertip = "<span class='fuk1 r'><a href='".$shop_url."cart/payment/{$v['Order_ID']}/'>去付款</a></span><span class='fuk r'><a class='remove'>取消订单</a></span>";
                }

                break;
            case 2:
                $ordertip = '<span class="fuk r"><a>已付款</a></span>';
                break;
            case 3:
                $ordertip .='<span class="fuk r"><a>已发货</a></span>';
				$ordertip .='<span class="fuk r"><a href="/api/' . $UsersID . '/shop/member/viewshipping/' . $v['Order_ID'] . '/">查看物流</a></span>';
                $ordertip .='<span class="fuk1 r"><a class="button" onclick="doconfirm('.$v['Order_ID'].')">确定收货</a></span>';
                break;
            case 4:
                $commitTotal = DB::table("pintuan_commit")->where(["order_id" => $v['Order_ID']])->count();
				$ordertip .='<span class="fuk r"><a href="/api/' . $UsersID . '/shop/member/viewshipping/' . $v['Order_ID'] . '/">查看物流</a></span>';
                if ($v['Is_Commit'] == 1) {
                    $ordertip .="<span class='fuk r'><a>已评价</a></span>";
                } else {
                    $ordertip .= "<span class='fuk1 r'><a href='/api/" . $UsersID . "/shop/member/commit/" . $v['Order_ID'] . "/?source=pintuan'>去评价</a></span>";
                }
                break;
            case 5:
                $ordertip .= '<span class="fuk r"><a>退款中</a></span>';
                break;
            case 6:
                $ordertip .= '<span class="fuk r"><a>已退款</a></span>';
                break;
            case 7:
                $ordertip .= '<span class="fuk r"><a>已手动退款</a></span>';
                break;
        }
        $orders[$k]['OrderTip'] = $ordertip;
    }
}

if(IS_AJAX){
    $status = 0;
    if(!empty($orders)) {
        $status = 1;
    }
    die(json_encode([ 'status'=>$status, 'data' => $orders, 'page' =>[
        'pagesize' => $total,
        'hasNextPage' => $total >= $pagesize ? true : false,
        'total' => $total,
    ]], JSON_UNESCAPED_UNICODE));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>我的订单</title>
    <link href="/static/api/pintuan/css/css.css" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/biaoqian.css" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/zhezhao.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="/static/js/jquery-1.11.1.min.js"></script>
    <script src="/static/js/layer/mobile/layer.js"></script>
    <script src="/static/api/js/jquery.uploadView.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/scrolltopcontrol.js"></script>
    <script type="text/javascript" src="/static/js/template.js"></script>
    <script type='text/javascript' src='/static/js/jquery.lazyload.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script>
    <script type='text/javascript' src="/static/api/pintuan/js/common.js"></script>
    <style>
        .layui-layer {width:100%;left:0px;}
        .ImgPath {margin-top: 10px;}
        .ImgPath .img, .upload {position: relative; float: left; margin-right: 5px; margin-bottom: 5px; width: 60px;height: 60px;}
        .ImgPath .img img, .upload img {width: 100%; height: 100%;}
        .ImgPath .img div {position: absolute; bottom: 0; width: 100%; height: 20px;line-height: 20px;text-align: center; font-size: 12px;color: #fff; background: rgba(0, 0, 0, 0.3); }
		.cp{padding:0px;}
    </style>
</head>
<body>
<div class="top_back border_bottom">
    <a href="<?=$pintuan_url ?>user/"><img src="/static/api/shop/skin/default/images/back_1.png"></a>
    <span>全部订单</span>
</div>
<div id="con">
    <ul id="tags">
        <li style="border-width: 0px;" <?php echo $order_status == 0 ? 'class=selectTag' : '' ?>><a href="<?=$pintuan_url . "orderlist/0/"; ?>">全部</a></li>
        <li style="border-width: 0px;" <?php echo $order_status == 1 ? 'class=selectTag' : '' ?>><a href="<?=$pintuan_url . "orderlist/1/"; ?>">待付款</a></li>
		<li style="border-width: 0px;" <?php echo $order_status == 2 ? 'class=selectTag' : '' ?>><a href="<?=$pintuan_url . "orderlist/2/"; ?>">已付款</a></li>
        <li style="border-width: 0px;" <?php echo $order_status == 3 ? 'class=selectTag' : '' ?>><a href="<?=$pintuan_url . "orderlist/3/"; ?>">已发货</a></li>
        <li style="border-width: 0px;" <?php echo $order_status == 4 ? 'class=selectTag' : '' ?>><a href="<?=$pintuan_url . "orderlist/4/"; ?>">已完成</a></li>
    </ul>
    <div id="tagContent">
        <div id="container" class="tagContent selectTag"></div>
        <input type="hidden" name="currentPage" value="2" />
    </div>
</div>
<div id="bg1"></div>
<div id="show">
    <div class="zzbj">
        <span class="zzbj1 l"><img src="<?php echo isset($pro_detail['ImgPath'])?$pro_detail['ImgPath']:'';?>"></span>
        <span class="sr l">
            <textarea name="content" placeholder="请输入您对商品的评价" class="sr1"></textarea>
        </span>
        <div class="clear"></div>
        <div class="pingfen1">商品评分：</div>
        <div class="clear"></div>
        <div class="pingfen">
            <label class="pingf1" for="fruit1"><input name="Fruit" type="radio" value="1分" class="pingf" id="fruit1"/>1分</label>
            <label class="pingf1" for="fruit2"><input name="Fruit" type="radio" value="2分" class="pingf" id="fruit2"/>2分</label>
            <label class="pingf1" for="fruit3"><input name="Fruit" type="radio" value="3分" class="pingf" id="fruit3"/>3分</label>
            <label class="pingf1" for="fruit4"><input name="Fruit" type="radio" value="4分" class="pingf" id="fruit4"/>4分</label>
            <label class="pingf1" for="fruit5"><input name="Fruit" type="radio" value="5分" class="pingf" id="fruit5"/>5分</label>
        </div>
        <div>
            上传图片(最多上传5张) <br/>
            <div class="ImgPath upload_pic_img"></div>
            <div class="upload">
                <input type="file" class="upload_pic" style="display:none;"/>
                <img src="/static/api/shop/skin/default/images/add.png">
            </div>
            <input type="hidden" name="upload_pic_json" value="">
        </div>
        <div class="clear"></div>
    </div>
</div>
<?php /*include 'bottom.php';*/ ?>
<script type="text/javascript">

    function showdiv(orderid, productid) {
        var mylayer = layer.open({
            type: 1,
            title:"订单评论",
            shadeClose:true,
            btn:["评论","取消"],
            content: $("#show").html(),
            yes:function(){
                addordercomment(orderid, productid,mylayer);
            }
        });
    }

    //添加订单评论
    function addordercomment(orderid, productid,mylayer) {
        var comment = {
            'action': 'submitCommit',
            'UsersID': '<?=$UsersID ?>',
            'Order_ID': orderid,
            'txt': $('.sr1').val(),
            'fenshu': $('input:radio[name="Fruit"]:checked').val(),
            'products_id': productid,
			'source':'pintuan'
        };

        if (comment.fenshu == null || comment.txt == null) {
            layer.open({
                content: '请输入您对商品的评价和评分',
                skin: 'msg',
                time: 1
            });
            return false;
        }

        $.ajax({
            url: "/api/<?php echo $UsersID; ?>/pintuan/ajax/",
            data: comment,
            type: "POST",
            dataType: "json",
            success: function (data) {
                if(data.status){
                    layer.open({
                        content: '评论成功！',
                        skin: 'msg',
                        time: 1,
                        end: function () {
                            layer.closeAll();
                            location.href = '<?php echo "/api/{$UsersID}/pintuan/orderlist/4/"; ?>';
                        }
                    });
                }else{
                    layer.open({
                        content: data.msg,
                        btn: '确定'
                    });
                }
            }
        });
    }

    //确定收货按钮
    function doconfirm(orderid){
        // 获取当前的orderid
        var UsersID="<?=$UsersID ?>";

        $.post('/api/<?= $UsersID ?>/shop/member/ajax/',{Order_ID:orderid,action:"confirm_receive"},function(data){
            if(data.status == 1){
                layer.open({
                    content: "确认收货成功",
                    skin: 'msg',
                    time: 1,
                    end: function () {
                        location.href = data.url;
                    }
                });
            }else{
                layer.open({
                    content: data.msg,
                    btn: '确定'
                });
            }
        },"json");
    }

    var url = "<?=$_SERVER['REQUEST_URI']?>";
    var totalPage = <?=$totalPage?>;
    var page = 1;

    $(function(){
		
		$(document).on('click', '.remove', function(){
			var orderid = $(this).parent().parent().parent().attr('id');
			var UsersID = "<?=$UsersID?>";
			var _this = $(this);
			var info = {
				'action': 'cancelOrder',
				'orderid': orderid,
				'UsersID': UsersID
			};
			
			layer.open({
				content: '确定要删除吗？'
				, btn: ['确定', '取消']
				, yes: function (index) {
					$.ajax({
						url: "/api/"+UsersID+"/pintuan/ajax/",
						data: info,
						type: "POST",
						dataType: "json",
						success: function (data) {
							if(data.status){
								layer.open({
									content: "订单取消成功！",
									skin: 'msg',
									time: 1,
									end: function () {
										_this.parent().parent().parent().remove();
										return;
									}
								});
							}else{
								layer.open({
									content: '操作失败！',
									btn: '确定'
								});
							}
						}
					});
				}
			});
		});
		
        getOrderList(url,page);
        //瀑布流加载翻页
        var last_pageno = 1;
        $(window).bind('scroll',function () {
            // 当滚动到最底部以上100像素时， 加载新内容
            if ($(document).height() - $(this).scrollTop() - $(this).height() < 100) {
                //已无数据可加载
                var currentPage = $("input[name='currentPage']").val();
                if (currentPage == last_pageno) {
                    return false;
                } else {
                    last_pageno = currentPage;
                }
                if (currentPage > totalPage) {
                    return true;
                }
                getOrderList(url,currentPage);
            }
        });
    });
</script>
<script id="loadContainer" type="text/html">
    {{each data as item i}}
    <div class="chanpin" id="{{item.Order_ID}}">
        <div class="chanpin1">
            <a href="/api/{{item.Users_ID}}/pintuan/orderdetails/{{item.Order_ID}}/">
			
                <span class="l"><img style="width:100px;height: 100px;" data-original="{{item.Order_CartList.ImgPath}}"></span>
                <span class="cp l" style="width:50%;">
                  <ul>
                    <li><strong>{{item.Order_CartList.ProductsName}}</strong></li>
                  </ul>
                </span>
                <span class="jiage r">¥{{item.Order_CartList.ProductsPriceX}}/件</span>
				<span class="cp l" style="width:50%;padding-left:20px;">
				{{item.Order_CartList.Productsattrstrval}}
				</span>
            </a>
            <div class="clear"></div>
        </div>
        <div class="t r">
            <span class="l">订单：{{item.Order_No}}</span>
            <span>&nbsp;合计：¥ {{item.Order_TotalPrice}} (含物流费：¥ {{item.Shipping_fee}})</span>
            <div class="clear"></div>
        </div>
        <div class="futime2">
            {{item.OrderTip}}
        </div>
    </div>
    {{/each}}
</script>
</body>
</html>