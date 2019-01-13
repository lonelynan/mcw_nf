<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

use Illuminate\Database\Capsule\Manager as DB;

$id = I("id",0);
$time = time();
$headTitle = "分类搜索";
$catelist = DB::table("shop_category")->where([
    'Users_ID' => $UsersID,
    'Category_ParentID' => 0
])->orderBy("Category_Index","ASC")->get();

if(empty($catelist)){
    die("商品暂无分类，请添加分类");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>搜索</title>
</head>
<link href="/static/api/pintuan/css/css.css" type="text/css" rel="stylesheet">
<link href="/static/api/pintuan/css/tab.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script>
<style>
    .querendd {
        line-height:30px;
    }
    .fanhui a img {
        padding-top:6px;
    }
</style>
<body>
<div class="w">
  <?php include_once("top.php"); ?>
    <div>
        <form action="/api/<?=$UsersID ?>/pintuan/sousuo/<?=$id ?>/" method="post">
            <input type = "hidden" name = "cateid" value = "<?=$id ?>"/>
            <span class="box l"><input type="text" class="text" name="name" value="" style="width:99%;"/></span>
            <span class="btnSubmit l"><input name="sousuo" type="submit" value="搜索"   class="btnSubmit_btn" /></span>

        </form>
    </div>
    <div class="clear"></div>
    <!--代码开始 -->
    <script type="text/javascript">
    $(function(){
        $("#container_0").show();
        $("#menu ul li").hover(function(){
            var container = $(this).attr("container");
            $("#content >div").hide();
            $("#"+container).show();
        },function(){
            $("#"+container).hide();
        });
        $("#menu ul").mouseout(function(){
            $("#container_0").show();
        });
    });
    </script>
    <style>
    .hide{ display:none; }
    .hide ul li{height:160px;}
    }
    </style>
    <div id="wrap">
          <div id="menu">
                <ul>
                    <?php
                    if(!empty($catelist)){
                        foreach($catelist as $key => $value) {
                            ?>
                            <li class="cate" container="container_<?=$key?>"><a class="href" href="/api/<?=$UsersID?>/pintuan/seach/<?=$value['Category_ID']?>/" ><?=$value['Category_Name']?></a>
                                <input type="hidden" class="cate" value="<?=$value['Category_ID']?>">
                            </li>
                            <?php
                        }
                    }
                    ?>
                </ul>
          </div>
          <!-- 搜索列表栏目内容开始 -->
          <div id="content">
          <?php
          if(!empty($catelist)){
              foreach($catelist as $key => $value) {
                  if($id){
                      $cateid = $id;
                  }else{
                      $cateid = $value['Category_ID'];
                  }
                   $goods = DB::table("shop_products")->where(["Users_ID" => $UsersID, "Products_Status" => 1,'pintuan_flag' => 1, 'flashsale_flag' => 0, "Father_Category" => $cateid])->whereRaw("pintuan_start_time<= {$time} AND {$time}<= pintuan_end_time")->get(["Products_JSON","Products_Name","Products_ID"]);
         ?>
              <div id="container_<?=$key ?>" class="hide">
                  <ul>
                      <?php
                      if(!empty($goods)){
                          foreach($goods as $k => $v){
                              ?>
                              <li class="l">
                                  <a href="/api/<?=$UsersID ?>/pintuan/xiangqing/<?=$v['Products_ID'] ?>/">
                                      <img src="<?=json_decode($v['Products_JSON'],true)['ImgPath']['0'] ?>">
                                      <span>
                                      <?=sub_str($v['Products_Name'])?></a>
                                  </span>
                              </li>

                              <?php
                          }
                      }
                      ?>
                  </ul>
              </div>
                  <?php

              }
          }
          ?>
          </div>
    <div class="clear"></div>
    <!--代码结束 -->
    <?php include 'bottom.php';?>
</div>
</body>
</html>
