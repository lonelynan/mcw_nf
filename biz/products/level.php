<?php
require_once('../global.php');
$shop_category = array();

$DB->Get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Category_ParentID asc,Category_Index asc");
while($r=$DB->fetch_assoc()){
    if($r["Category_ParentID"]==0){
        $shop_category[$r["Category_ID"]] = $r;
    }else{
        $shop_category[$r["Category_ParentID"]]["child"][] = $r;
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
    <script type='text/javascript' src='/static/member/js/distribute/dis_level.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
        <script type='text/javascript' src='/static/member/js/distribute/dis_level.js?t=3'></script>
        <script language="javascript">$(document).ready(dis_level.level_init);</script>
        <div id="orders" class="r_con_wrap">
                           <div id="select_category" class="lean-modal lean-modal-form">
          <div class="h">产品分类<a class="modal_close" href="#"></a></div>
          <div class="catlist" style="height:360px; overflow:auto">
           <dl>
           <?php
               foreach($shop_category as $first=>$items){
           ?>
              <dt><qq544731308><?php echo $items["Category_Name"];?></qq544731308></dt>
            <dd>
            <?php
               if(!empty($items["child"])){
				   foreach($items["child"] as $second=>$item){
					   echo '<span><input type="radio" rel="0" name="Category" value="'.$item["Category_ID"].'" id="cate_'.$item["Category_ID"].'" /> <label for="cate_'.$item["Category_ID"].'"><qq544731308son>'.$item["Category_Name"].'<qq544731308son></label></span>';
				   }
               }
           ?>
            </dd>
           <?php }?>
           </dl>
          </div>
          <div class="rows">
            <label></label>
            <span class="submit"><a class="modal_close" style="border-radius:8px;padding:5px 20px; color:#FFF; text-align:center; background:#3AA0EB" href="#">选好了</a></span>
            <div class="clear"></div>
          </div>
        </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(document).on('click','.modal_close',function(){ //label find('label') qq544731308son
	    var select_class = '';
		var son = '';
		var sonid = '';
            $('#select_category dd input:radio:checked').each(function(){
                son += $(this).next('label').children('qq544731308son').html();
				sonid = $(this).val();
                
            })
	    $('#select_category dt input:checkbox:checked').each(function(){
                if($.trim($(this).parent('dt').next('dd').html()) != ''){
                    son = $(this).parent('dt').next('dd').find('qq544731308son').html();
					
                }else{
					son = '';
				}
                select_class += '<p style="margin:5px 0px; padding:0px; font-size:12px; color:#666"><font style="color:#333; font-size:12px;">' + $(this).parent('dt').find('qq544731308').html()+'</font>&nbsp;'+son+'</p>';
            });
        if (select_class == '') {
            parent.$('#classs').html(son);
            parent.$('#classsid').val(sonid);

        } else {
            parent.$('#classs').html(select_class);
            parent.$('#classsid').val(sonid);
        }
        parent.layer.closeAll();
    });
</script>
</body>
</html>