<?php
$Dwidth = array('640','320','320','320','320');
$DHeight = array('320','140','140','140','140');
$Home_Json = json_decode($rsSkin['Home_Json'],true);
for($no=1;$no<=5;$no++){
	$json[$no-1]=array(
		"ContentsType"=>$no==1?"1":"0",
		"Title"=>$no==1?json_encode($Home_Json[$no-1]['Title']):$Home_Json[$no-1]['Title'],
		"ImgPath"=>$no==1?json_encode($Home_Json[$no-1]['ImgPath']):$Home_Json[$no-1]['ImgPath'],
		"Url"=>$no==1?json_encode($Home_Json[$no-1]['Url']):$Home_Json[$no-1]['Url'],
		"Postion"=>"t0".$no,
		"Width"=>$Dwidth[$no-1],
		"Height"=>$DHeight[$no-1],
		"NeedLink"=>"1"
	);
}
if(empty($rsConfig['DefaultAreaID'])){
	$DefaultAreaID = 240;
}else{
	$DefaultAreaID = $rsConfig['DefaultAreaID'];
}

if(empty($rsConfig['DefaultLng'])){
	$DefaultLng = 100;
}else{
	$DefaultLng = $rsConfig['DefaultLng'];
}

$rsDefaultArea = $DB->GetRs("area","area_id,area_name,area_code"," where area_id=".$DefaultAreaID);

if(!empty($_POST) && $_POST['action'] == 'get_index_list'){	
		if (!empty($_POST['lat'])) {
		$_SESSION['firstlat']=$_POST['lat'];
		$_SESSION['firstlng']=$_POST['lng'];
		}
	
	$condition = "where Users_ID='".$UsersID."' and Biz_IndexShow=1 and Biz_Status=0";
	
		if(isset($_COOKIE['city'])){		
			$areaid = explode('|', $_COOKIE['city'])[0];
		}else{
			$areaid = $rsDefaultArea['area_id'];
		}
		
		if($areaid != 0){//0为全国
			$rsArea = $DB->GetRs("area","area_id,area_name"," where area_id=".$areaid);
			if(!$rsArea){
				$rsConfig = shop_config($UsersID);
				$areaid = empty($rsConfig['DefaultAreaID']) ? 240 : $rsConfig['DefaultAreaID'];
			}
			$childids = get_childs($DB,"area","area_id","where area_parent_id=".$areaid);
			$areaids = $childids ? $areaid.','.$childids : $areaid;
			$condition .= " and Area_ID in(".$areaids.")";
		}
		
	if(!empty($_POST['lat']) && !empty($_POST['lng'])){
	$condition .= " order by ACOS(SIN((".$_POST['lat']." * 3.1415) / 180 ) *SIN((Biz_PrimaryLat * 3.1415) / 180 ) +COS((".$_POST['lat']." * 3.1415) / 180 ) * COS((Biz_PrimaryLat * 3.1415) / 180 ) *COS((".$_POST['lng']." * 3.1415) / 180 - (Biz_PrimaryLng * 3.1415) / 180 ) ) * 6380 asc";
	}
	
	$rsBiz = $DB->Get("biz","Biz_ID,Biz_Name,Biz_Logo,Biz_Address,Region_ID,Biz_PrimaryLng,Biz_PrimaryLat",$condition);
    $rsBiz = $DB->toArray($rsBiz);
	foreach($rsBiz as $key => $val){
		$rsBiz[$key]['url'] = "/api/".$UsersID."/shop/biz_xq/".$val['Biz_ID']."/";
		$rsCommit = $DB->GetRs("user_order_commit","count(*) as num, sum(Score) as score","where Users_ID='".$UsersID."' and (MID='shop' or MID='offline_st' or MID='offline_qrcode') and Status=1 and Biz_ID=".$val['Biz_ID']);
        $rsBiz[$key]['average_ico'] = $rsCommit["num"]==0 ? '0.5' : round(($rsCommit["score"]/$rsCommit["num"]));
		$rsBiz[$key]['average_num'] = number_format ( $rsBiz[$key]['average_ico'] ,  1 ,  '.' ,  '' );
		//距离
		if(!empty($_POST['lat']) && !empty($_POST['lng'])){
		    $rsBiz[$key]['meter'] = GetDistance($_POST['lat'],$_POST['lng'],$val['Biz_PrimaryLat'],$val['Biz_PrimaryLng']);
		}else{
			$rsBiz[$key]['meter'] = '';
		}
		//打折
		$products_data = $DB->Get("shop_products","Products_PriceY,Products_PriceX,Products_PriceA","where Users_ID='".$UsersID."' AND Biz_ID=".$val['Biz_ID']);
		$rsProduct = $DB->toArray($products_data);
		//$discount = array();
		foreach ($rsProduct as $k=>$value) {
			$total= $value['Products_PriceX']/$value['Products_PriceY']*10;
			$discount[$key][] = floor($total);
		}
		if(isset($discount[$key])&&!empty($discount[$key])){
			$rsBiz[$key]['discoun_min'] = min($discount[$key]);
		}else{
			$rsBiz[$key]['discoun_min'] = 10;
		}
		//优惠券
		$rsCoupon = array();
		$DB->Get("user_coupon","*","where Biz_ID=".$val["Biz_ID"]);
		while($rsCou = $DB->fetch_assoc()){
			$rsCoupon[] = $rsCou;
		}
		foreach ($rsCoupon as $ky => $vals) {
			if($vals['Coupon_EndTime'] < time()){
				unset($rsCoupon[$ky]);
			}
		}
		if(!empty($rsCoupon)){
			$rsBiz[$key]['rsCoupon'] = 1;
		}else{
			$rsBiz[$key]['rsCoupon'] = 0;
		}
		$DB->query('select count(*) as num from user_order where Biz_ID=' . $val["Biz_ID"]);
		$yuecount = $DB->fetch_assoc();
		$rsBiz[$key]['yuecount'] = $yuecount['num'];
	}
	
	if(!empty($_POST['lat']) && !empty($_POST['lng'])){
		$rsBiz = fxy_array_sort($rsBiz,'meter');
	}
    $counts = count($rsBiz);
	$num = 10;//每页记录数
	$p = !empty($_POST['p'])?intval(trim($_POST['p'])):1;
	$total = $counts;//数据记录总数
	$totalpage = ceil($total/$num);//总计页数
	$min_offset = ($p-1) * $num;//每次查询取记录
	$max_offset = $p * $num-1 > $total ? $total-1 : $p * $num-1;
	$list = array();
	for($i=$min_offset; $i<=$max_offset; $i++){
		$list[] = isset($rsBiz[$i]) ? $rsBiz[$i] : array();
	}
		//去除空值
	foreach($list as $k => $v){
		if(empty($list[$k])){
			unset($list[$k]);
		}
	}


    $data = [
        'list' => !empty($list) ? $list : '',
        'totalpage' => $totalpage,
        'newlat' => isset($_SESSION['firstlat']) ? $_SESSION['firstlat'] : '',
        'newlng' => isset($_SESSION['firstlng']) ? $_SESSION['firstlng'] : '',
    ];
	echo json_encode($data, JSON_UNESCAPED_UNICODE);
	exit;

} elseif(!empty($_POST) && $_POST['action'] == 'dcact'){
	setcookie('dcact', $_POST['actval'], 0, '/');
	$data = array(//没有数据可加载
			'status' => 1,
		);
	echo json_encode($data, JSON_UNESCAPED_UNICODE);exit;
}
function get_childs($DB,$table,$fields,$condition){
	$child = array();
	$DB->Get($table,$fields,$condition);
	while($r=$DB->fetch_assoc()){
		$child[] = $r[$fields];
	}
	return count($child)>0 ? implode(",",$child) : '';
}
$catlist = array();
$DB->Get("biz_union_category","Category_ID,Category_Img,Category_Name","where Users_ID='".$UsersID."' and Category_ParentID=0 order by Category_Index asc,Category_ID asc");
while($r = $DB->fetch_assoc()){
	$catlist[] = $r;
}
//短消息
if(isset($_SESSION[$UsersID.'User_ID'])){
	$sql = "SELECT COUNT(*) AS total FROM `user_message` WHERE Users_ID='" . $UsersID . "' AND Message_ID NOT IN (SELECT Message_ID FROM user_message_record WHERE Users_ID='" . $UsersID . "' AND User_ID=" .$_SESSION[$UsersID.'User_ID']. ")";
	$query = $DB->query($sql);
	$msg = $DB->fetch_assoc();
	$messageTotal = (int)$msg['total'];
}else{
	$messageTotal = 0;
}
?>
<?php require_once('top.php');?>
<body style="background:#f8f8f8;font-family:微软雅黑">
<!--header-->
<?php 

if(isset($_COOKIE['city'])){
	$cityname_tmp = explode('|', $_COOKIE['city']);
	$cityname = $cityname_tmp[1];
}else{
	$cityname = $rsDefaultArea['area_name'];
}
?>
<div class="header">
<span class="header_city" onclick="location.href='/api/<?php echo $UsersID;?>/city/';" rel="<?php echo $rsDefaultArea['area_code'];?>"><?php echo $cityname;?><i class="fa  fa-angle-down fa-2x" aria-hidden="true" style="font-size:18px;position: relative;top:-39px; left:15px"></i></span>
<div class="search">
    <form method="post" action="/api/<?php echo $UsersID;?>/shop/union/search/">
	    <input name="title" value="" class="search_input" placeholder="搜商家"/>
		<input type="submit" value="" class="submit"/>
	</form>
</div>
<span class="header_user">
	<a href="/api/<?=$UsersID?>/user/message/" style="color: #FFF;">
		<i class="fa fa-commenting-o" aria-hidden="true" style="font-size:22px; position:relative">
			<?php if ($messageTotal > 0) {?>
			<b class="car_num"><?php echo $messageTotal;?></b>
			<?php }?>
		</i>
	</a>
</span>
</div>
<!--header-->
<link href="/static/css/bootstrap.min.css" rel="stylesheet">
<style>
*, *::before, *::after {
    box-sizing: inherit;
}
.clear{clear:both;}
.left{float:left;}
.right{float:right;}
h1, h2, h3{margin:0;}
.modal-footer, #confirm_area_btn::before, #cancel_area_btn::after{
    box-sizing: border-box;
}
.car_num{background: #fff;
    border-radius: 50%;
    display: block;
    height: 15px;
    position: absolute;
    top:-2px;
    width: 15px;
    font-size: 12px;
    text-align: center;
    line-height: 15px;
    color: #ff5000;
    font-style: normal;
	right:-7px;}
</style>
<script type='text/javascript' src='/static/api/shop/js/shop.js?t=<?php echo time();?>'></script>
<script type='text/javascript' src='/static/js/bootstrap.min.js'></script>
<link href="/static/css/font-awesome.min.css" type="text/css" rel="stylesheet">
<link href="/static/css/index_o2o.css?t=132" type="text/css" rel="stylesheet">
<link rel="stylesheet" href="/static/api/shop/skin/default/css/tao_checkout.css" />
<link href="/static/js/plugin/flexslider/flexslider.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/static/js/plugin/flexslider/flexslider.js"></script> 
<script type="text/javascript" src="/static/js/swipe.js"></script> 
<script>
	$(document).ready(function(){
		$('.flexslider').flexslider({
			animation:"slide",
			directionNav: false,
			controlNav: true, 
			touch: true //是否支持触屏滑动
		});
	});  
</script> 
<link href='/static/api/shop/union/page_media.css' rel='stylesheet' type='text/css' />
<link href='/static/api/shop/union/media.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/api/shop/js/index.js'></script> 
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=<?= $ak_baidu ?>"></script>
<script language="javascript">
    var shop_skin_data=<?php echo json_encode($json) ?>;
    $(document).ready(index_obj.index_init);
	var baidu_map_ak='<?php echo $ak_baidu;?>';
	var app_ajax_url = '<?php echo $shop_url.'ajax/';?>';
	var UsersID = '<?php echo $UsersID;?>';
	var minul = '<?php echo $act;?>';
	var SiteUrl = "http://"+window.location.host;
</script>
<div id="shop_skin_index">
		<div class="page-swipe">
        <div id="slider" class="swipe" style="visibility:visible;">  
            <div class="swipe-wrap">  
				<?php if(count($catlist)>0){ ?>
					<figure>  
						<ul class="pic_link clearfix">  
							<?php $j = 1;
								foreach($catlist as $key=>$value){
									if($j<=10){?>
										<li><a href="<?php echo $shop_url;?>bizlist/<?php echo $value["Category_ID"];?>/"><img src="<?php echo $value["Category_Img"];?>" width="42" height="42"></a><p><?php echo $value["Category_Name"];?></p></li> 
							<?php } $j++;}?>
						</ul>  
					</figure>  
				<?php }?>
				<?php if(count($catlist)>10){ ?>
					<figure>  
						<ul class="pic_link clearfix">  
							<?php 
								$i = 1;
								foreach($catlist as $key=>$value){
									if($i>10 && $i<=20){
							?>
										<li><a href="<?php echo $shop_url;?>bizlist/<?php echo $value["Category_ID"];?>/"><img src="<?php echo $value["Category_Img"];?>" width="42" height="42"></a><p><?php echo $value["Category_Name"];?></p></li> 
							<?php } $i++; } ?>
						</ul>    
					</figure>  
				<?php }?>
                <?php if(count($catlist)>20){ ?>
                <figure>  
                    <ul class="pic_link clearfix">  
                        <?php 
							$h = 1;
							foreach($catlist as $key=>$value){
								if($h>20 && $h<=30){
						?>
									<li><a href="<?php echo $shop_url;?>bizlist/<?php echo $value["Category_ID"];?>/"><img src="<?php echo $value["Category_Img"];?>" width="42" height="42"></a><p><?php echo $value["Category_Name"];?></p></li> 
						<?php } $h++;} ?>
                    </ul>   
                </figure>  
				<?php }?>
            </div>  
        </div>  
        <div id="nav">  
            <ul id="position">  
				<?php if(count($catlist)>0){ ?>
					<li class="on"></li>  
				<?php }?>
				<?php if(count($catlist)>10){ ?>
					<li class=""></li>  
				<?php }?>
				<?php if(count($catlist)>20){ ?>
					<li class=""></li>  
				<?php }?>
            </ul>  
        </div>  
        <script>  
            var slider =  
                    Swipe(document.getElementById('slider'), {  
                        auto: 0,  
                        loop: false, 
						stopPropagation: false,						
                        continuous: false,  
                        callback: function(pos) {  
                            var i = bullets.length;  
                            while(i--){  
                                bullets[i].className = ' ';  
                            }  
                            bullets[pos].className = 'on';  
                        }  
                    });  
            var bullets = document.getElementById('position').getElementsByTagName('li');  
        </script> 
    </div>
	<div class="shop_skin_index_list i1 img_xx" rel="edit-t02">
		<div class="img"></div>
	</div>
	<div class="shop_skin_index_list i2 img_xx" rel="edit-t03">
		<div class="img"></div>
	</div>
	<div class="shop_skin_index_list i3 img_xx" rel="edit-t04">
		<div class="img"></div>
	</div>
	<div class="shop_skin_index_list i4 img_xx" rel="edit-t05">
		<div class="img"></div>
	</div>
	<div class='clear'></div>
	<div class='recommend'>
		<ul>
			<li class='index_title'>
				<span class="left">附近商家</span>
				<span class="right"><a href="<?php echo $shop_url;?>bizlist/0/" style="color:#999; font-size:13px;">更多>></a></span>
			</li>
			<div class="merchants biz_list"></div>
		</ul>
		<div class="loadbtn" style="line-height:40px; font-size:12px; color:#999; text-align:center" page="1">加载更多</div>
	</div>
	<div class='clear'></div>
</div>
<?php include("yh_index_ajax.php");?>
<?php require_once('../skin/distribute_footer.php'); ?>
<div class="container">
	<div class="row">
		<div class="modal"  role="dialog" id="area-modal">
			<div class="modal-dialog modal-sm">
				<div class="modal-content">
					<div class="modal-body">
						<p style="text-align:center;">您目前所在城市为<span></span><br />是否切换？</p>
					</div>
					<div class="modal-footer">
						<a class="pull-left modal-btn" id="confirm_area_btn">确定</a>
						<a class="pull-right modal-btn" id="cancel_area_btn">取消</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>