<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $rsConfig["name"];?></title>
<link href='/static/css/global.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href='/static/api/zhongchou/css/zhongchou.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js?t=<?php echo time();?>'></script>
<script type='text/javascript' src='/static/api/js/global.js?t=<?php echo time();?>'></script>
<script type='text/javascript' src='/static/api/zhongchou/js/zhongchou.js?t=<?php echo time();?>'></script>
<script type="text/javascript">
$(document).ready(zhongchou_obj.detail_init);
</script>
<style type="text/css">
    .company {
        background: #FFF;
        padding: 8px 0px;
        border-top: solid 1px #dadada;
        border-bottom: solid 1px #dadada;
    }
    .company .company_btns {
        margin-top: 8px;
        height: 32px;
        line-height: 32px;
        width: 100%;
    }
    .company .company_btns a {
        display: block;
        width: 50%;
        float: left;
        height: 32px;
        line-height: 32px;
        font-size: 12px;
        color: #333;
    }
    .company .company_btns a span {
        display: block;
        width: 95%;
        margin: 0px auto;
        height: 32px;
        line-height: 32px;
        text-align: center;
        border: 1px #dfdfdf solid;
        border-radius: 5px;
    }
    .buy_bj1,buy_bj a{display:block;}
    </style>
</head>

<body>
<div class="header">
 项目信息
 <a href="/api/<?php echo $UsersID;?>/zhongchou/" id="home"></a>
 <a href="/api/<?php echo $UsersID;?>/zhongchou/orders/" id="user"></a>
</div>

<div class="main">
 <div id="detail">
  <div class="thumb">
   <div class="flag">
  	<?php if($item["fromtime"]>time()){?>
    未开始
    <?php }elseif($item["totime"]<time()){?>
    已结束
    <?php }else{?>
    众筹中
    <?php }?>
   </div>
   <img src="<?php echo $item["thumb"];?>" />
  </div>
  <div class="time">活动时间：<?php echo date("Y.m.d",$item["fromtime"]);?> - <?php echo date("Y.m.d",$item["totime"]);?></div>
  <div class="title"><?php echo $item["title"];?></div>
   <?php 
        $biz = $item['Biz_ID'];
        if($biz){
            $sql = "SELECT * FROM biz AS b LEFT JOIN biz_group AS g ON b.Group_ID=g.Group_ID WHERE b.Users_ID='{$UsersID}' AND b.Biz_ID={$biz} AND g.Group_IsStore=1";
            $result = $DB->query($sql);
            $flag = $DB->fetch_assoc($result);
            if($flag){
    ?>
    <div class="company">
        <div class="company_btns"> <a href="/api/<?=$UsersID ?>/zhongchou/"><span><img src="/static/api/shop/skin/default/images/category_btns.png" width="16" /> 所有产品</span></a> <a href="/api/<?=$UsersID ?>/zhongchou/biz/<?=$item['Biz_ID'] ?>/act_<?=isset($_SESSION[$UsersID.'_ZhongchouCurrentActive'])?$_SESSION[$UsersID.'_ZhongchouCurrentActive']:0 ?>/"><span><img src="/static/api/shop/skin/default/images/home_btns.png" width="16" /> 店铺逛逛</span></a>
          <div class="clear"></div>
        </div>
    </div>
    <div class="clear"></div>
    <?php 
            }
        }
    ?>
  <div class="jindu">    
    <span>筹集<font style="font-family:'Times New Roman'; font-size:14px; color:#F60"> ￥<?php echo str_replace('.00','',$item["complete"]);?></font></span>
    <span>目标<font style="font-family:'Times New Roman'; font-size:14px;"> ￥<?php echo str_replace('.00','',$item["amount"]);?></font></span>
    <span>支持<font style="font-family:'Times New Roman'; font-size:14px; color:#0dc05d"> <?php echo $item["people"];?> </font>人</span>
  </div>
  <div class="split"></div>
  <div class="desc">
   <h2>活动简介</h2>
   <?php echo $item["description"];?>
  </div>
  <div class="prizes">
   <h2>回报</h2>
   <dl>
    <?php foreach($lists as $k=>$v){?>
    <dt>支持<font style="font-family:'Times New Roman'; font-size:16px; font-weight:bold; padding-left:2px;">￥<?php echo $v["money"];?></font></dt>
    <dd>您可获得<?php echo $v["title"];?><?php if($v["thumb"]){?> <a href="<?php echo $v["thumb"];?>">[图片]</a><?php }?>
     <?php if($v["introduce"]){?>
      <p><?php echo $v["introduce"];?></p>  
     <?php }?>  
    </dd>
    <?php }?>
   </dl>
  </div>
 </div>
</div>
<div class="pay_items">
  <p>[ ╳ ]</p>
  <a href="/api/<?php echo $UsersID;?>/zhongchou/checkpay/<?php echo $itemid;?>/">无私奉献</a>
  <?php
	foreach($lists as $k=>$v){
		$prize_flag=0;
		$userid = isset($_SESSION[$v['usersid']."User_ID"])?$_SESSION[$v['usersid']."User_ID"]:0;
		if($v["maxtimes"]>0){
		    $num = $DB->GetRs("user_order","count(*) as num","where Order_Type='zhongchou_".$itemid."' and Users_ID='".$v['usersid']."' and Order_Status=2 and User_ID=".$userid);
		    if($num["num"]>=$v["maxtimes"]){
		        $prize_flag=1;
		    }
		}
		if($prize_flag==0){
  ?>
  <a href="/api/<?php echo $UsersID;?>/zhongchou/check/<?php echo $itemid;?>/<?php echo $v["prizeid"];?>/">￥<?php echo $v["money"];?></a>
  <?php 
		}
	}
  ?>
</div>
<div class="detail_footer_bg"></div>
<div class="detail_footer">
 <?php if($item["fromtime"]>time()){?>
 <p class="footer_btns_1">未开始</p> 
 <?php }elseif($item["totime"]<time()){?>
 <p class="footer_btns_1">已结束</p>
 <?php }else{?>
 <p class="footer_btns">支持众筹</p>
 <?php }?>
 <p class="footer_other">
    <span>筹集<font style="font-family:'Times New Roman'; font-size:16px; color:#F60"> ￥<?php echo str_replace('.00','',$item["complete"]);?></font></span>
    <span>&nbsp;目标<font style="font-family:'Times New Roman'; font-size:16px;"> ￥<?php echo str_replace('.00','',$item["amount"]);?></font></span>
 </p>
</div>

<?php if($share_flag==1 && $signature<>""){?>
	<script language="javascript">
		var share_config = {
		   appId:"<?php echo $share_user["Users_WechatAppId"];?>",		   
		   timestamp:<?php echo $timestamp;?>,
		   nonceStr:"<?php echo $noncestr?>",
		   url:"<?php echo $url?>",
		   signature:"<?php echo $signature;?>",
		   title:'<?php echo $item["title"];?>',
		   desc:'<?php echo str_replace(array("\r\n", "\r", "\n"), "", $item["introduce"]);?>',
		   img_url:'http://<?php echo $_SERVER["HTTP_HOST"].$item["thumb"];?>',
		   link:''
		};
		$(document).ready(global_obj.share_init_config);
	</script>
<?php }?>
</body>
</html>