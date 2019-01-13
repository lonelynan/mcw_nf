<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}
$jcurid = 8;
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
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/shop.js'></script>
   <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/jiebase_menubar.php');?>
    <div id="products" class="r_con_wrap"> 
      <script language="javascript">$(document).ready(shop_obj.products_list_init);</script>
      <div class="control_btn">
      <a href="phone_add.php" class="btn_green btn_w_120">话费服务设置</a> 
      <a href="add_data.php" class="btn_green btn_w_120">流量服务设置</a>       
      </div>
      
		
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="8%" nowrap="nowrap">序号</td>
            <td width="15%" nowrap="nowrap">服务名称</td>
            <td width="15%" nowrap="nowrap">服务类别</td>
            <td width="10%" nowrap="nowrap">利润百分比(%)</td>
            <td width="10%" nowrap="nowrap">爵位比例(%)</td>
            <td width="10%" nowrap="nowrap">佣金比例(%)</td>
            <td width="10%" nowrap="nowrap">服务商</td>
            <td width="10%" nowrap="nowrap">金额或流量设置总数</td>          
            <td width="9%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
            <?php
              $usersid=$_SESSION['Users_ID'];
              $DB->query("SELECT * FROM bianmin_server WHERE Users_ID='".$usersid."'");
              $zhuang=0;
              while($res=$DB->fetch_assoc()){  
             //处理状态
              if ($res['Server_Type']==0) {
                      $type='话费';        
                  }else{
                      $type='流量';
                              }
              if ($res['Service_Provider']==0) {
                      $Provider='中国联通';        
                  }else{
                      $Provider='中国移动';
                              }
              //处理爵位  未设置
               echo ' <tr class="del">
            <td class="id">'.$res['Server_ID'].'</td>
            <td>'.$res['Server_Name'].'</td>
            <td> '.$type.'</td>

            <input class="in" type="hidden" value="'.$res['Server_Type'].'"/>

            
            <td>"' .$res['Server_Profit']. '"(%)</td>
            <td nowrap="nowrap">"' .$res['nobi_ratio']. '"(%)<br>
               </td>
            <td>"' .$res['commission_ratio'].'"(%)</td>
            <td nowrap="nowrap">'.$Provider.'</td>
            <td nowrap="nowrap">'.$res['Count'].'</td>
                        
            <td class="last" nowrap="nowrap"><a class="src"><img src="/static/member/images/ico/mod.gif" align="absmiddle" alt="修改" /></a>
      <a class="onclik"><img src="/static/member/images/ico/del.gif" align="absmiddle" alt="删除" /></a>
      </td>
          </tr>';      
          $zhuang++;
         }     

    ?>            
         
                  </tbody>
      </table>
      <div class="blank20"></div>
      <style>
.page{width:auto;text-align:center;height:30px;margin-top:5px;}
.page a,
.page span{display:inline-block;}
.page a{width:26px;height:24px;line-height:24px;color:#36c;border:1px solid #ccc;}
.page a:hover,
.page a.cur{background:#ffede1;border-color:#fd6d01;color:#fd6d24;text-decoration:none;}
.page .pre,
.page .next,
.page .nopre,
.page .nonext{width:41px;height:24px;}
.page .pre,
.page .nopre{padding-left:16px;text-align:left;}
.page .next,
.page .nonext{padding-right:16px;text-align:right;}
.page .nopre,
.page .nonext{border:1px solid #ccc;color:#000;line-height:24px;}
.page .nopre{background:url(/Framework/Static/images/page/bg_pre_g.png) no-repeat 6px 8px;}
.page .pre,
.page .pre:hover{background:url(/Framework/Static/images/page/bg_pre.png) no-repeat 6px 8px;}
.page .nonext{background:url(/Framework/Static/images/page/bg_next_g.png) no-repeat 46px 8px;}
.page .next,
.page .next:hover{background:url(/Framework/Static/images/page/bg_next.png) no-repeat 46px 8px;}
</style>    </div>
  </div>
</div>
</body>
<script type="text/javascript">

//设置删除的ajax
$('.onclik').click(function(){
  //获取服务id
  var id=$(this).parents().find('.id').html();
  if(confirm('您确定删除此服务')){   
    //发送ajax
    $.post("Ajax.php",{ID:id},function(d){
      }, 'json');
    $(this).parents('tr').remove();
  }   
})
//设置修改点击事件
$('.src').click(function(){
    var id=$(this).parents().find('.id').html();
    var ud=$(this).parents().find('.in').val();
    if(ud==0){
         location.href="/member/bianmin/phone_exit.php?ID="+id ;
    }else{
         location.href="/member/bianmin/exit_data.php?ID="+id ;
    }
})


</script>
</html>