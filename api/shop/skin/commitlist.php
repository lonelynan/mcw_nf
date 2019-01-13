<?php require_once('skin/top.php'); ?>
<style>
    * {margin: 0;padding: 0;border: 0;outline: 0;box-sizing: border-box;background-repeat: no-repeat;background-position: center center;}
    .pinglun {width: 100%;margin: 0 auto;padding: 12px 0;overflow: hidden;}
    .zaishou_title {width: 100%;padding: 0 10px;display: flex;flex-wrap: wrap;line-height: 24px;}
    .pinglun ul li {width: 100%;padding: 10px 10px;border-bottom: 1px #eee solid;}
    .top_pl {display: flex;justify-content: space-between;}
    .left_pl {display: flex;flex-wrap: wrap;line-height: 50px;margin-bottom: 10px;}
    .right_pl {line-height: 50px;color: #999;font-size: 13px;}
    .main_pl {line-height: 20px;}
    a:hover{
        color:#333;
    }
</style>
<link href='/static/api/shop/skin/default/css/commit.css' rel='stylesheet' type='text/css'/>

<div class="top_back">
    <a href="javascript:history.go(-1);"><img src="/static/api/shop/skin/default/images/back_1.png"></a>
    <span>评论列表</span>
</div>
<div class="commit_title">
    <a href="javascript:;"><?= !empty($ProductsID) ? $rsProducts["Products_Name"] : $rsProducts['Biz_Name']?></a>
</div>
<div class="commit_list">
    <?php
    if(!empty($ProductsID)) {
        $commit_res = get_comment_aggregate($DB, $UsersID, $ProductsID,'');
    }
    if (!empty($Biz_ID)){
        $commit_res = get_comment_aggregate($DB, $UsersID, '',$Biz_ID);
    }

    $average = empty($commit_res["points"]) ? '' : '( <font style="color:#7d0000; font-size:16px; font-weight:bold; font-family:Times New Roman, Times, serif;">' . number_format(($commit_res["points"]), 2, '.', '') . '</font> 分)';
    ?>
    <h2>共有 <font style="color:#F60; font-size:14px;"><?= $commit_res["num"] ?></font> 条评论<?= $average ?></h2>
    <?php
    if(!empty($ProductsID)) {
        $DB->getPage("user_order_commit", "c.CreateTime, c.Note, c.Score,c.ImgPath,u.User_HeadImg,u.User_NickName", "as c left join user as u on c.User_ID=u.User_ID where Status=1 and Product_ID=" . $ProductsID . " order by CreateTime DESC", $pageSize = 20);
    }
    if(!empty($Biz_ID)){
        $DB->getPage("user_order_commit", "c.CreateTime, c.Note, c.Score,c.ImgPath,u.User_HeadImg,u.User_NickName", "as c left join user as u on c.User_ID=u.User_ID where Status=1 and Biz_ID=" . $Biz_ID . " order by CreateTime DESC", $pageSize = 20);
    }
    while ($v = $DB->fetch_assoc()) {;
        $order_img = json_decode($v['ImgPath'],true);
        ?>
            <div class="pinglun">
<!--                <p class="zaishou_title"><img src="/static/images/shangjia_28.png">评论</p>-->
                <ul>
                        <li>
                            <p class="top_pl">
                                <span class="left_pl"><img src="<?=!empty($v['User_HeadImg']) ? $v['User_HeadImg'] : '/static/api/shop/skin/default/images/face.jpg' ?>"><?=$v['User_NickName']?></span>
                                <span class="right_pl"><?=date('Y.m.d',$v['CreateTime'])?></span>
                            </p>
                            <p class="main_pl"><?=$v['Note']?></p>
                            <?php if(!empty($order_img)){?>
                                <div class="img_pl">
                                    <?php foreach ($order_img as $key => $value){?>
                                        <img src="<?=$value?>">
                                    <?php }?>
                                </div>
                            <?php }?>
                        </li>
                </ul>
            </div>
    <?php } ?>
</div>
<?php
        if(!empty($ProductsID)){
            $DB->showWechatPage('/api/' . $UsersID . '/shop/commit/' . $ProductsID . '/');
        }
        if(!empty($Biz_ID)){
            $DB->showWechatPage('/api/' . $UsersID . '/shop/commit/?Biz_ID=' . $Biz_ID);
        }

 ?>
<?php /*require_once('skin/distribute_footer.php');*/ ?>
</body>
</html>