<?php
$Dwidth = array('260','630','300','300','50','50','50','50','320');
$DHeight = array('100','320','340','340','50','50','50','50','210');
$Home_Json=json_decode($rsSkin['Home_Json'],true);
for($no=1;$no<=9;$no++){
	$json[$no-1]=array(
		"ContentsType"=>$no==2?"1":"0",
		"Title"=>$no==2?json_encode($Home_Json[$no-1]['Title']):$Home_Json[$no-1]['Title'],
		"ImgPath"=>$no==2?json_encode($Home_Json[$no-1]['ImgPath']):$Home_Json[$no-1]['ImgPath'],
		"Url"=>$no==2?json_encode($Home_Json[$no-1]['Url']):$Home_Json[$no-1]['Url'],
		"Postion"=>"t0".$no,
		"Width"=>$Dwidth[$no-1],
		"Height"=>$DHeight[$no-1],
		"NeedLink"=>"1"
	);
}
if(!empty($_POST)){
	if(empty($_POST['cate_id'])){
		//获取新品
		$counts = $DB->GetRs("shop_products","count(Products_ID) as count","where Users_ID='".$UsersID."' and Products_IsNew=1 and Products_Status=1 and Products_SoldOut=0");
		$num = 2;//每页记录数
		$p = !empty($_POST['p'])?intval(trim($_POST['p'])):1;
		$total = $counts['count'];//数据记录总数
		$totalpage = ceil($total/$num);//总计页数
		$limitpage = ($p-1)*$num;//每次查询取记录
		$rsNewProducts = $DB->GetAssoc("shop_products","Products_Name,Products_ID,Products_JSON,Products_PriceX,Products_Sales","where Users_ID='".$UsersID."' and Products_IsNew=1 and Products_SoldOut=0 and Products_Status=1 order by Products_Index asc,Products_ID desc limit $limitpage,$num");
		$new_products = handle_product_list($rsNewProducts);
		foreach($new_products as $k => $item){
			$new_products[$k]['link'] = $shop_url.'products/'.$item['Products_ID'].'/';
			
		}
		if(count($new_products)>0){
			$data = array(
			    'list' => $new_products,
				'totalpage' => $totalpage,
			);
		}else{
			$data = array(//没有数据可加载
			    'list' => '',
				'totalpage' => $totalpage,
			);
		}
		echo json_encode($data);
	}else{
		//根据栏目id的产品列表
		$catid = $_POST['cate_id'];
		$new_products = array();
		$counts = $DB->GetRs("shop_products","count(Products_ID) as count","where Products_SoldOut=0 and Products_Status=1 and Products_Category like '%,".$catid.",%'");
		$num = 2;//每页记录数
		if($counts['count'] <= $num){
			$show_load_btn = 0;
		}
		$p = !empty($_POST['p'])?intval(trim($_POST['p'])):1;
		$total = $counts['count'];//数据记录总数
		$totalpage = ceil($total/$num);//总计页数
		$limitpage = ($p-1)*$num;//每次查询取记录
		
		$rsNewProducts = $DB->GetAssoc("shop_products","Products_Name,Products_ID,Products_JSON,Products_PriceX,Products_Sales","where Products_SoldOut=0 and Products_Category like '%,".$catid.",%' and Products_Status=1 order by Products_Index asc,Products_ID desc limit $limitpage,$num");
		$new_products = handle_product_list($rsNewProducts);
		foreach($new_products as $k => $item){
			$new_products[$k]['link'] = $shop_url.'products/'.$item['Products_ID'].'/';
		}
		if(count($new_products)>0){
			$data = array(
			    'list' => $new_products,
				'totalpage' => $totalpage,
			);
		}else{
			$data = array(//没有数据可加载
			    'list' => '',
				'totalpage' => $totalpage,
			);
		}
		echo json_encode($data);
	}
	exit;
}
include("skin/top.php"); 
?>
<body style=" background:#f8f8f8">
<style type="text/css" media="all">
    /**
	 *
	 * 加载更多样式
	 *
	 */
	.pullUp {
		background:#fff;
		height:40px;
		line-height:40px;
		padding:5px 10px;
		border-bottom:1px solid #ccc;
		font-weight:bold;
		font-size:14px;
		color:#888;
		text-align:center;
		overflow:hidden;
	}
	.pullUp .pullUpIcon  {
		padding:10px 20px;
		background:url(/static/js/plugin/lazyload/pull-icon@2x.png) 0 0 no-repeat;
		-webkit-background-size:40px 80px; background-size:40px 80px;
		-webkit-transition-property:-webkit-transform;
		-webkit-transition-duration:250ms;	
	}
	.pullUp .pullUpIcon  {
		-webkit-transform:rotate(0deg) translateZ(0);
	}

	.pullUp.flip .pullUpIcon {
		-webkit-transform:rotate(0deg) translateZ(0);
	}

	.pullUp.loading .pullUpIcon {
		background-position:0 100%;
		-webkit-transform:rotate(0deg) translateZ(0);
		-webkit-transition-duration:0ms;

		-webkit-animation-name:loading;
		-webkit-animation-duration:2s;
		-webkit-animation-iteration-count:infinite;
		-webkit-animation-timing-function:linear;
	}

	@-webkit-keyframes loading {
		from { -webkit-transform:rotate(0deg) translateZ(0); }
		to { -webkit-transform:rotate(360deg) translateZ(0); }
	}
	*{margin:0;padding:0;list-style-type:none;}  
a,img{border:0;}  
/* WebDown */  
.WebDown{width:100%;margin:0 auto;display:none; position:fixed;z-index:100;}  
.WebDown .tipbox{position:relative;height:50px;background:#fffcef;padding:5px 10px 10px 10px;font-size:12px;}  
.WebDown .laba,.WebDown .Close,.WebDown .arrow{background:url(/static/api/shop/skin/default/images/tipboxbg.gif)no-repeat;}  
.WebDown .laba{display:block;float:left;width:42px;height:39px;overflow:hidden;background-position:-27px 0;margin:5px 0 0 5px;}  
.WebDown .tiptext{float:left;margin-left:15px;display:inline;}  
.WebDown .tiptext h3{font-size:16px;color:#db7c22;font-family:"微软雅黑","宋体";height:28px;line-height:24px;}  
.WebDown .tiptext p label{color:#999;cursor:pointer;margin-left:5px;}  
.WebDown .Close{width:14px;height:14px;overflow:hidden;background-position:-13px 0px;position:absolute;top:10px;right:10px;cursor:pointer;}  
.WebDown .arrow{display:block;width:13px;height:7px;overflow:hidden;background-position:0 -7px;position:absolute;left:15px;top:-7px;}  
 </style>
<?php
//ad($UsersID, 1, 1);
?>
<body>  
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>  
<script type="text/javascript">  
  

$(function(){  
/* 
功能：保存cookies函数  
参数：name，cookie名字；value，值 
*/  
function setCookie(name,value){  
    var Days = 30*12;   //cookie 将被保存一年  
    var exp  = new Date();  //获得当前时间  
    exp.setTime(exp.getTime() + Days*24*60*60*1000);  //换成毫秒  
    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();  
}   
/* 
功能：获取cookies函数  
参数：name，cookie名字 
*/  
function getCookie(name){  
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));  
    if(arr != null){  
     return unescape(arr[2]);   
    }else{  
     return null;  
    }  
} 
      
    $('.WebDown .Close').click(function(){  
        $('.WebDown').fadeOut(1000);   

		
        if($("#NotShow").attr("checked")){  
			
			setCookie("Browse","idea"); 
			
        } 
    });  
      
      
    var Sys = {};  
    var ua = navigator.userAgent.toLowerCase();  
    var s;  
    (s = ua.match(/msie ([\d.]+)/)) ? Sys.ie = s[1] :  
    (s = ua.match(/firefox\/([\d.]+)/)) ? Sys.firefox = s[1] :  
    (s = ua.match(/chrome\/([\d.]+)/)) ? Sys.chrome = s[1] :  
    (s = ua.match(/opera.([\d.]+)/)) ? Sys.opera = s[1] :  
    (s = ua.match(/version\/([\d.]+).*safari/)) ? Sys.safari = s[1] : 0;  
 
    if(getCookie("Browse")!="idea"){  
        $('.WebDown').show();  
    }
  
});  
  

</script>  
  
<style type="text/css">  

</style>  
  
<div class="WebDown">  
    <div class="tipbox">  
        <span class="laba"></span>  
        <div class="tiptext">  
            <h3>更多风格请前往PC端选择</h3>  
            <p><input type="checkbox" id="NotShow" /><label for="NotShow">以后不再显示</label></p>  
        </div>  
        <div class="Close"></div>  
    </div>  
</div>  
<div id="shop_page_contents">
  <div id="cover_layer"></div>
  <link href='/static/api/shop/skin/<?php echo $rsConfig["Skin_ID"] ?>/page.css' rel='stylesheet' type='text/css' />
  <link href='/static/api/shop/skin/<?php echo $rsConfig["Skin_ID"] ?>/page_media.css' rel='stylesheet' type='text/css' />
  <link href='/static/js/plugin/flexslider/flexslider.css' rel='stylesheet' type='text/css' />
  <script type='text/javascript' src='/static/js/plugin/flexslider/flexslider.js'></script> 
  <script type='text/javascript' src='/static/api/shop/js/index.js'></script>
  <script language="javascript">var shop_skin_data=<?php echo json_encode($json) ?>;$(document).ready(index_obj.index_init);</script>
  <link href='/static/api/shop/skin/default/css/products.css' rel='stylesheet' type='text/css' />
  <div id="shop_skin_index">
	<div class="header">
        <div class="shop_skin_index_list logo" rel="edit-t01">
            <div class="img"></div>
        </div>
        <div class="search">
            <?php require_once('skin/search_in.php'); ?>
        </div>
    </div>
    <!--<div class="menu">
    	<ul>
        	<li><a href="<?php echo $shop_url;?>/allcategory/" class="category">全部分类</a></li>
        	<li><a href="/api/<?php echo $UsersID ?>/shop/cart/">购物车</a></li>
        	<li><a href="/api/shop/search.php?UsersID=<?php echo $UsersID;?><?php echo $owner['id'] != '0' ? '&OwnerID='.$owner['id'] : '';?>&IsNew=1">最新商品</a></li>
        	<li><a href="/api/shop/search.php?UsersID=<?php echo $UsersID;?><?php echo $owner['id'] != '0' ? '&OwnerID='.$owner['id'] : '';?>&IsHot=1">热卖商品</a></li>
        </ul>
        <div class="clear"></div>
    </div>-->
	<div class="menu">
		<div class="shop_skin_index_list" rel="edit-t05">
			<div class="img"></div>
			<div class="text"></div>
		</div>
		
		<div class="shop_skin_index_list" rel="edit-t06">
			<div class="img"></div>
			<div class="text"></div>
		</div>
		<div class="shop_skin_index_list" rel="edit-t07">
			<div class="img"></div>
			<div class="text"></div>
		</div>
		<div class="shop_skin_index_list" rel="edit-t08">
			<div class="img"></div>
			<div class="text"></div>
		</div>
		<div class="clear"></div>
	</div>
    <div class="box">
        <div class="shop_skin_index_list banner" rel="edit-t02">
            <div class="img"></div>
        </div>
		<div class="shop_skin_index_list a0" rel="edit-t03">
            <div class="img"></div>
        </div>
		<div class="shop_skin_index_list a1" rel="edit-t04">
            <div class="img"></div>
        </div>
        <div class="clear"></div>
    </div>
	<div class="shop_skin_index_list h2" rel="edit-t09">
      <div class="text"  style=" color:#e30101;"></div>
    </div>
  </div>
  <div class="index_products">
	  <div class="list-0"></div> 
	  <ul id="more" style="overflow:hidden;"></ul>
	  <div class="pullUp get_more" cate_id="" page="1"> <span class="pullUpIcon"></span><span class="pullUpLabel">正在加载中...</span> </div>
 </div>
</div>
<?php require_once('skin/distribute_footer.php'); ?>
<?php include("yh_index_ajax.php");?>
</body>
</html>