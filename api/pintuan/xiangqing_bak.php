<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
S($UsersID . "HTTP_REFERER", $_SERVER['REQUEST_URI']);
use Illuminate\Database\Capsule\Manager as DB;

//ajax获取评论
if(IS_AJAX && I("action")=='getCommit'){
    $gid = I("productsid");
    $page = I("page");
    $pagesize = I("pagesize");
    $offset = ($page-1)*$pagesize;
    //获取总数
    $list = DB::table("pintuan_commit")->where(['Product_ID' => $gid, 'Users_ID' => $UsersID, 'Status' => 1])
        ->orderBy("CreateTime","desc")->offset($offset)->limit($pagesize)->get(["CreateTime","pingfen", "Note"]);
    foreach($list as $k => $v){
        $list[$k]['CreateTime'] = date('Y-m-d H:i:s',$v["CreateTime"]);
    }
    $msg = [];
    if(!empty($list)){
        $msg = [
            'status'=>1,
            'data'=>$list,
            'page'=>$page
        ];
    }else{
        $msg = [
            'status'=>0,
            'data'=>''
        ];
    }
    die(json_encode($msg,JSON_UNESCAPED_UNICODE));
}

setcookie('product_detail', $_SERVER["REQUEST_URI"], time()+3600, '/', $_SERVER['HTTP_HOST']);
setcookie('url_referer', $_SERVER["REQUEST_URI"], time()+3600, '/', $_SERVER['HTTP_HOST']);
$productsid = intval(I("productid"));
$goodsInfo = DB::table("pintuan_products")->where(["products_id" => $productsid])->first();
if(empty($goodsInfo)){
    sendAlert("相关的产品不存在","/api/{$UsersID}/pintuan/");
}

$title = $goodsInfo['Products_Name'];
$img = "";
$url = PROTOCAL . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$desc = "";
$rsUser = DB::table("users")->where(["Users_ID" => $UsersID])->first(["Users_Account"]);
//订单类别 0实物 1虚拟(验证) 2虚拟(直冲)
$orderstype = $goodsInfo['Products_Type'];
//是否是抽奖  等于0  显示抽奖
$Draw = $goodsInfo['Is_Draw'];
$teamid = I("teamid");
if($Draw==0) {
    $awardTitle='<span class="cj_choujiang">抽奖</span>';
    $choujiang=isset($goodsInfo['Award_rule'])?$goodsInfo['Award_rule']:'';
}else{
    $awardTitle="";
    $choujiang='';
}
$baoyou=$goodsInfo['Products_pinkage'];
$day=$goodsInfo['Products_refund'];
$jia1=$goodsInfo['Products_compensation'];
$property = '';
if($baoyou){
    $property .= '<li class="l">包邮</li>';
}
if($day){
    $property .= '<li class="l">7天退款</li>';
}
if($jia1){
    $property .= '<li class="l">假一赔十</li>';
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?=$goodsInfo['Products_Name']?></title>
	<link href="/static/api/pintuan/css/css.css?t=<?=time() ?>" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/style.css?t=<?=time() ?>" rel="stylesheet" type="text/css">
    <link href="/static/api/pintuan/css/zhezhao.css?t=<?=time() ?>" rel="stylesheet" type="text/css">
	<link href="/static/api/pintuan/css/media1.css?t=<?=time() ?>" rel="stylesheet" type="text/css">
    <script src="/static/api/pintuan/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/scrolltopcontrol.js?t=<?=time() ?>"></script> 
    <script src="/static/api/pintuan/js/responsiveslides.min.js"></script>
    <script src="/static/api/pintuan/js/slide.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script>
    <script type='text/javascript' src='/static/js/jquery.lazyload.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script>
    <script src="/static/api/pintuan/js/common.js"></script>
</head>
<body>
    <div class="w">
    	<div class="dingdan">
    	    <span class="fanhui l"><a href="javascript:history.go(-1);"><img src="/static/api/pintuan/images/fanhui.png" width="16px" height="16px"></a></span>
    	    <span class="querendd l" style=" line-height:40px; height:40px; overflow:hidden"><?=$goodsInfo['Products_Name']?></span>
    	    <div class="clear"></div>
        </div>
        <!-- 产品幻灯片开始 -->
        <div class="device">
            <a class="arrow-left" href="#"></a>
            <a class="arrow-right" href="#"></a>
            <div class="swiper-container">
                <div class="swiper-wrapper">
                <?php
                    $patha=json_decode($goodsInfo['Products_JSON'],true)['ImgPath'];
                    foreach ($patha as $key => $image) {
                        $image = getImageUrl($image,2);
                        if($key===0){
                            $img = "http://".$_SERVER['HTTP_HOST'].$image;
                        }
                        echo '<div class="swiper-slide"><img src="'.$image.'"> </div>';
                    }
                ?>
                </div>
                <div class="pagination"></div>
            </div>
        </div>
        <!-- 产品幻灯片结束 -->
	    <div class="clear"></div>

        <!-- 产品属性栏 开始 -->
        <div class="bj">
            <div class="">
                <span class="t3">¥<?=$goodsInfo['Products_PriceT']?></span>
                <span class="t4"><del>¥<?=$goodsInfo['Products_PriceD']?></del></span>
                <?=$awardTitle?>
                <span class="t5 r">累计销量：<?=$goodsInfo['Products_Sales']?>件</span>
            </div>
            <div class="clear"></div>
            <div class="t6"><?=$goodsInfo['Products_Name']?></div>
            <div class="t7"><?=$goodsInfo['Products_BriefDescription'].''.$choujiang?></div>
        </div>
        <div class="fl_1">
            <ul>
                <?=$property ?>
            </ul>
        </div>
        <!-- 产品属性栏 结束 -->
        <div class="clear"></div>
        <?php
        if($goodsInfo['people_num'] - 1) {
            echo'<div class="kt2">支付开团并邀请'. ($goodsInfo['people_num'] -1) . '人参团，人数不足自动退款</div>';
        }
        ?>
    <?php 
        $biz = $goodsInfo['Biz_ID'];
        if($biz){
            $sql = "SELECT * FROM biz AS b LEFT JOIN biz_group AS g ON b.Group_ID=g.Group_ID WHERE b.Users_ID='{$UsersID}' AND b.Biz_ID={$biz} AND g.Group_IsStore=1";
            $result = DB::select($sql);
            if(!empty($result)){
        ?>
        <div class="company">
            <div class="company_btns"> <a href="/api/<?=$UsersID ?>/pintuan/"><span><img src="/static/api/shop/skin/default/images/category_btns.png" width="16" /> 所有产品</span></a> <a href="/api/<?=$UsersID ?>/pintuan/biz/<?=$biz ?>/act_<?=S($UsersID.'_CurrentActive') ? S($UsersID.'_CurrentActive'):I("activeid") ?>/"><span><img src="/static/api/shop/skin/default/images/home_btns.png" width="16" /> 店铺逛逛</span></a>
              <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
        <?php
                }
            }
        ?>
        <!-- 评论模块 -->
        <!-- 评论模块 -->
        <?php
        $list = DB::select("select pt.id,pt.userid,pt.productid,pt.teamnum,pt.teamstatus,pt.starttime,pt.stoptime,pp.people_num,pp.Products_Name,pp
.Products_Status,pp.Team_Count,pp.Is_Draw,pp.order_process,u.User_NickName,u.User_Mobile,u.User_HeadImg 
            from pintuan_team as pt left join pintuan_products as pp on pt.productid=pp.Products_ID 
            left join `user` as u on pt.userid=u.User_ID where pt.teamstatus='0' and pt.productid='".$productsid."' and pt.users_id='".$UsersID."' LIMIT 0,3");
        ?>
        <?php if (count($list) > 0) { ?>
        <div class="kt1">以下小伙伴正在发起团购，您可以直接参与</div>
        <?php } ?>
		<?php  foreach ($list as $key =>$v) { ?>
        <div class="clear"></div>
        <div class="pintuan">
            <div class="pint">
                <div class="bjt">
                    <div class="bjt-img"><img style="width:50px;height:50px;" src="<?= $v['User_HeadImg'] ? $v['User_HeadImg'] : '/static/api/images/user/face.jpg'; ?>"></div>
                </div>
                <div class="bjx">
                    <div class="bjxt">
                        <span class="l" style="width:100px;height:40px;"><?= empty($v['User_NickName']) ? $v['User_Mobile'] : $v['User_NickName'] ?></span>
                        <span class="bjxt1 r"><?= ($num = $v['people_num']-$v['teamnum'])>0 ? "还差" . $num  . "人成团" : "已结束" ?></span>
                        <div class="clear"></div>
                        <span class="bjxt2 r" style="margin-top:-20px;"><?= $v['stoptime']>= time() ? date('Y-m-d', $v['stoptime']).'结束' : '已结束' ?></span>
                    </div>
                </div>
                <div class="bjtp">
                    <?php
                    $detail = DB::table("pintuan_teamdetail")->where(["teamid" => $v['id'], 'userid' => $UserID])
                        ->first();
                    $time = time();
                    if ($time >= $v['starttime'] && $time <= $v['stoptime'] && $v['teamstatus'] == 0 &&
                        $v['teamnum'] < $v['people_num'] ) {
                        if($v['userid'] == $UserID || !empty($detail)){
                            echo '<a>已参团</a>';
                        }else{
                            echo '<a href="/api/'.$UsersID.'/pintuan/teamdetail/'.$v['id'].'/">去参团</a>';
                        }
                    } else {
                        if($v['teamstatus']==1){
                            echo "<a>已完成</a>";
                        }else{
                            echo '<a>已结束</a>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="clear"></div>
            <?php
            }
            if (count($list) > 0) {
                ?>
                <div class="gengduo"><a href="/api/<?=$UsersID ?>/pintuan/cantuan/<?=$productsid ?>/">查看更多团</a></div>
            <?php }
            $total = DB::table("pintuan_commit")->where(['Product_ID' => $productsid, 'Users_ID' => $UsersID, 'Status' => 1])->count('*');
            if ($total>=0) {
                echo '<div class="pingjia">
	            <div class="pj"><span class="l">评价'.$total.'条</span></div>
	            <div class="clear"></div></div>';
            }
            ?>
            <div id="commit"></div>
            <input type="hidden" name="page" value="1"/>
        </div>

        <div class="chanpin2">
            <div id="con">
                <ul id="tags">
                    <li class="selectTag" style="border-right:1px #ccc solid;"><a onClick="selectTag('tagContent0',this)"
                    href="javascript:void(0)">产品详情</a> </li>
                    <li ><a onClick="selectTag('tagContent1',this)"
                    href="javascript:void(0)">购买记录</a> </li>
                </ul>
                <div id="tagContent">
                    <div class="tagContent  selectTag" id="tagContent0">
                        <div class="jj">
                            <?php
                            $content = htmlspecialchars_decode($goodsInfo['Products_Description']);
                            if (!empty($content)) {
                                $content = str_replace('/uploadfiles',  '/uploadfiles',stripslashes($content));
                                $content = str_replace('src', 'data-original', $content);
                                echo $content;
                            }else{
                                echo '此商品暂无产品详情!';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="tagContent " id="tagContent1">
                        <div class="bq2">
                            <ul>
                            <?php
                            $li = DB::select("SELECT * FROM `user_order` as o left join user as u on o.User_ID=u.User_ID  where o.Users_ID='".$UsersID."' and (o.Order_Type='pintuan' or o.Order_Type='dangou') and o.Order_Status>2 and o.Order_CartList like '%\"Products_ID\":\"{$productsid}\"%' ORDER BY Order_CreateTime desc LIMIT 0,5");;
                            if (empty($li)) {
                                echo '<li>暂时无数据</li>';
                            }else{
                                foreach ($li as $key => $v) {
                                    if ($v['User_NickName']==Null){
                                        //产品的数量
                                        $num=isset(json_decode($v['Order_CartList'],true)['num'])?json_decode($v['Order_CartList'],true)["num"]:1;
                                        echo '<li>
                                            <div class="pj2"><span class="l">匿名</span><span class="r">'.date("Y-m-d H:i:s",$v['Order_CreateTime']).'</span>
                                            <div class="clear"></div><div> <span class="l">'.json_decode($v['Order_CartList'],true)['ProductsName'].'</span><span class="r">件数:'.$num.'</span></div></div>
                                            <div class="clear"></div>
                                            </li>';
                                    }else{
                                        //产品的数量
                                        $num=isset(json_decode($v['Order_CartList'],true)['num'])?json_decode($v['Order_CartList'],true)["num"]:1;
                                        echo '<li>
                                            <div class="pj2"><span class="l">'.$v['User_NickName'].'</span><span class="r">'.date("Y-m-d H:i:s",$v['Order_CreateTime']).'</span>
                                            <div class="clear"></div><div> <span class="l">'.json_decode($v['Order_CartList'],true)['ProductsName'].'</span><span class="r">件数'.$num.'</span></div></div>
                                            <div class="clear"></div>
                                            </li>';
                                    }
                                }
                            }
                            ?>
                            </ul>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <div style="height:50px;"></div>
            <div class="bottom1">
                <div class="footer1">
                    <ul>
                        <li style="width:18%;">
                            <a href="<?php echo "/api/{$UsersID}/pintuan/"; ?>"><img src="/static/api/pintuan/images/552cd5641bbec_128.png" width="20px" height="20px" /><p>首页</p></a>
                        </li>
                        <?php
                        $collectlist = DB::table("pintuan_collet")->where(["users_id" => $UsersID, 'userid'
                        => $UserID, 'productid' => $productsid])->first();
                        if(!empty($collectlist)){
                            echo '<li style="width:18%;"><a href="#" id="shoucang"><img src="/static/api/pintuan/images/shoucang01.png" width="20px" height="20px" /><p>收藏</p></a></li>';
                        }else{
                            echo '<li style="width:18%;"><a href="#" id="shoucang"><img src="/static/api/pintuan/images/shoucang02.png" width="20px" height="20px" /><p>收藏</p></a></li>';
                        }
                        //判断是否过期，是否支持单单独购买
                        $is_only_buy = $goodsInfo['is_buy'];
                        $tip_title = "";
                        $time = time();
                        if($goodsInfo['starttime']>$time){
                            $tip_title = "团购失效<br/>时间未开始";
                        }else if($goodsInfo['stoptime']<$time){
                            $tip_title = "团购失效<br/>时间过期";
                        }
                        if($is_only_buy){
                        ?>
                        <li style="width:31.3%;">
                            <div class="buy_bj1" id="danBuy">
                                <a href="javascript:void();" class="vt" style="color:#fff;font-weight:bold;">
                                    <?=$tip_title ?>
                                    <?php if(!$tip_title){ ?>
                                        <span class="va">¥&nbsp;<?php echo empty($goodsInfo['Products_PriceD'])?0:$goodsInfo['Products_PriceD'];?></span>
                                        <p><span class="va">单独购买</span></p>
                                    <?php } ?>

                                </a>
                            </div>
                            <div class="clear"></div>
                        </li>
                        <?php }else{?>
                        <li style="width:31.3%;">
                            <div class="buy_bj1" style="padding:0px">
                                <a href="<?php echo "/api/{$UsersID}/pintuan/user/"; ?>" style="color:#fff;"><img src="/static/api/pintuan/images/002-3.png" width="18px" height="18px" style="padding-top:5px" /><p>我的</p></a>
                            </div>
                        </li>
                        <?php }?>
                        <li style="width:31.3%;">
                            <div class="buy_bj" id="groupBuy">
                                <a href="javascript:void();" class="vt" style="color:#fff;font-weight:bold;">
                                    <?php
                                    if($tip_title){
                                        echo $tip_title;
                                    }else{
                                        ?>
                                        <span  class="va" ><?php echo empty($goodsInfo['Products_PriceT'])?0:$goodsInfo['Products_PriceT'];?></span>
                                        <p class="va">开团</p>
                                        <!-- 单购页面 -->
                                    <?php }?>
                                </a>
                            </div>
                            <div class="clear"></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php  require_once(CMS_ROOT . '/api/pintuan/share.php'); ?>
    <script src="/static/api/pintuan/js/idangerous.swiper.min.js"></script>
    <script>
        //产品幻灯片滚动效果
        $(function(){
            var mySwiper = new Swiper('.swiper-container',{
                pagination: '.pagination',
                loop:true,
                grabCursor: true,
                paginationClickable: true
            });
            $('.arrow-left').on('click', function(e){
                e.preventDefault()
                mySwiper.swipePrev()
            });
            $('.arrow-right').on('click', function(e){
                e.preventDefault()
                mySwiper.swipeNext()
            });
        });
        //获取产品评论
        $(function(){
            var UsersID = "<?=$UsersID ?>";
            var productid = parseInt("<?=$productsid ?>");
            var total = parseInt("<?=$total ?>");
            getCommit(UsersID, productid, total);
        });
        $(function(){
            //页面图片懒加载
            $('body img').lazyload({
                placeholder : "/static/images/no_pic.png",
                effect : "fadeIn", //使用淡入特效
                failure_limit : 10,  //容差范围
                skip_invisible : true,  //加载隐藏的图片，弄人为不加载
                threshold: 50
            });

            //控制收藏按钮
            $('#shoucang').click(function(){
                var UsersID="<?php echo $UsersID;?>";
                var goodsid="<?php echo $productsid;?>";

                $.post('/api/' + UsersID + '/pintuan/ajax/',{UsersID:UsersID,goodsid:goodsid,action:"collect"},function(data){
                    if(data.status == 1){
                        if(data.state == 1){
                            $('#shoucang').find('img').attr('src','/static/api/pintuan/images/shoucang01.png');
                        }else{
                            $('#shoucang').find('img').attr('src','/static/api/pintuan/images/shoucang02.png');
                        }
                    }
                }, 'json');
            })
            //单人按钮的设置
            $('#danBuy').click(function(){
                var goodsid=<?php echo $productsid;?>;
                var UsersID = "<?php echo $UsersID;?>";
                var teamid = "<?=I("teamid") ?>";
                var goodsType = 0;      //  0  单购  1  团购

                $.post('/api/' + UsersID + '/pintuan/ajax/',{action:"addcart",goodsid:goodsid,UsersID:UsersID,goodsType:goodsType, teamid:teamid},function(data){
                    if(data.status==0){
                        layer.msg(data.msg,{icon:1,time:1000},function(){
                            window.location.href="/api/" + UsersID + "/pintuan/";
                        });
                        return false;
                    }else{
                        var isvirtual=data.isvirtual;
                        if (isvirtual==1){
                            location.href="/api/" + UsersID + "/pintuan/vorder/";
                        }else if(isvirtual==0){
                            location.href="/api/" + UsersID + "/pintuan/order/";
                        }
                    }
                }, 'json');
            });
            //团购
            $('#groupBuy').click(function(){
                var goodsid=<?php echo $productsid;?>;
                var UsersID = "<?php echo $UsersID;?>";
                var teamid = "<?=I("teamid") ?>";
                var goodsType = 1;      //  0  单购  1  团购

                $.post('/api/' + UsersID + '/pintuan/ajax/',{action:"addcart",goodsid:goodsid,UsersID:UsersID, goodsType:goodsType, teamid:teamid},function(data){
                    if(data.status==0){
                        layer.msg(data.msg,{icon:1,time:1000},function(){
                            if(data.url){
                                window.location.href= data.url;
                            }else{
                                window.location.href="/api/" + UsersID + "/pintuan/";
                            }
                        });
                        return false;
                    }else{
                        var isvirtual=data.isvirtual;
                        if (isvirtual==1){
                            location.href="/api/" + UsersID + "/pintuan/vorder/";
                        }else if(isvirtual==0){
                            location.href="/api/" + UsersID + "/pintuan/order/";
                        }
                    }
                }, 'json');
            });
        });
        document.addEventListener('touchmove', function (e) {
            $("img").scrollLoading();
        }, false);
        document.addEventListener('DOMContentLoaded', function(){
            $("img").scrollLoading();
        }, false);
    </script>
</body>
</html>