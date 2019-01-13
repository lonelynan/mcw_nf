<?php
  //设立产品类别  0  话费   1 流量
  $type=array('话费','流量');
  //设立产品类别  0  联通   1 移动
  $service=array('中国联通','中国移动');
  $UsersID=$_SESSION['Users_ID'];
  $serverid=$_GET['ID'];
  $dis_config = dis_config($_SESSION["Users_ID"]);
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
<script type='text/javascript' src='/static/member/js/products_attr_helper.js'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script type='text/javascript' src='/static/member/js/shop.js'></script>

</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <div class="r_nav">
      <ul>
        <li><a href="config.php">基本配置</a></li>
        <li class="cur"><a href="serverlist.php">服务列表</a></li>
      </ul>
    </div>
    <div id="products" class="r_con_wrap">
      <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script>
      <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
      <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
      <form id="phone_add.php_form" class="r_con_form skipForm" method="post" action="phone_add.php.php">
       
              <?php
       // 从数据库遍历
      $DB->query("SELECT * FROM bianmin_server WHERE Server_ID='".$serverid."'");
      $res=$DB->fetch_assoc();
      
      //处理 服务商类型

      $ser=$res['Service_Provider'];
      if ($ser==0){
          $se='中国联通';
      }else{
          $se='中国移动';
      }
      //处理 金额数组
      $newjson=json_decode($res['Server_Newprice']);
      $oldjson=json_decode($res['Server_Oldprice']);
      $number=count($newjson);

       ?>
        
        <div class="rows">
          <label>服务名称</label>
          <span class="input">
          <input type="text" name="Name" id="name" value="<?php echo $res['Server_Name']?>" readonly="readonly" class="form_input" size="35" maxlength="100" notnull />
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
      <?php
      //判断 数组有几个元素
          if($number==1){
            echo ' 
    <div class="rows form-group">
        <label>充值金额</label>
         <span class="input"  id="money">
             <input type="text" class="ProductsParametername" name="Products_Parameter[0][name]" placeholder="充值现价" value="'.$newjson['0'].'" class="form_input" size="15" maxlength="30" />
             <input type="text" class="ProductsParametervalue" name="Products_Parameter[0][value]" placeholder="充值原价" value="'.$oldjson['0'].'" class="form_input" size="15" maxlength="30" />
            <span style="cursor: pointer" class="icon-plus addkfval">[+]</span><br>
        </span>
        <div class="clear"></div>  
    </div>';          
          }else{
               echo ' 
    <div class="rows form-group">
        <label>充值金额</label>
         <span class="input"  id="money">
             <input type="text" class="ProductsParametername" name="Products_Parameter[0][name]" placeholder="充值现价" value="'.$newjson['0'].'" class="form_input" size="15" maxlength="30" />
             <input type="text" class="ProductsParametervalue" name="Products_Parameter[0][value]" placeholder="充值原价" value="'.$oldjson['0'].'" class="form_input" size="15" maxlength="30" />
            <span style="cursor: pointer" class="icon-plus addkfval">[+]</span><br>
        </span>
        <div class="clear"></div>  
    </div>';
              for ($i=1; $i<=$number-1;$i++) { 
                echo '<div class="rows form-group">
        <label>充值金额</label>
        <span id="money" class="input">
        <input class="ProductsParametername" type="text" maxlength="30" size="15" value="'.$newjson[$i].'" placeholder="充值现价" name="Products_Parameter[1][name]">
        <input class="ProductsParametervalue" type="text" maxlength="30" size="15" value="'.$oldjson[$i].'" placeholder="充值原价" name="Products_Parameter[1][value]">
        <span class="addkfval icon-minus" style="cursor: pointer">[-]</span>
          <br>
        </span>
        <div class="clear"></div>  
    </div> ';
              }
          }

      ?>                    
               <div class="rows">
          <label>服务利润</label>
          <span class="input price">
          <span>%</span>
          <input type="text" id="price" name="Products_Profit" value="<?php echo $res['Server_Profit']?>" class="form_input" size="5" maxlength="10" notnull />
          <span>(占服务现价的百分比，用于分销返利额计算)</span>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>爵位奖励比例</label>
          <span class="input price">
          <span>%</span>
          <input  id="nobi"type="text" name="nobi_ratio" value="<?php echo $res['nobi_ratio']?>" class="form_input" size="5" maxlength="10" notnull />
          <span>(所占利润百分比)</span>
          </span>
          <div class="clear"></div>
        </div>
    <div class="rows">
          <label>佣金比例</label>
          <span class="input price">
          <span>%</span>
          <input id="commission_ratio" type="text" name="commission_ratio" value="<?php echo $res['commission_ratio']?>" class="form_input" size="5" maxlength="10" notnull />
          <span>(所占利润百分比)</span>
          </span>
          <div class="clear"></div>
        </div>
        
    <div class="rows">
          <label>佣金返利</label>
          <span class="input">
          <table id="wholesale_price_list" class="item_data_table" border="0" cellpadding="3" cellspacing="0">
                  <?php 
            $arr = array('一','二','三','四','五','六','七','八','九','十');
            $level =  $dis_config['Dis_Self_Bonus']?$dis_config['Dis_Level']+1:$dis_config['Dis_Level'];
            for($i=0;$i<$level;$i++){?>
                        
            <tr>
              <td><?php if($i==$level-1 && $dis_config['Dis_Self_Bonus']){?>自销&nbsp;&nbsp;%<?php }else{?><?php echo $arr[$i]?>级&nbsp;&nbsp;%<?php }?>
                <input name="Distribute[<?php echo $i;?>]" value="<?=!empty($distribute_list[$i])?$distribute_list[$i]:0?>" class="listDristribute form_input" size="5" maxlength="10" type="text">
                (佣金利润的百分比)
              </td>
            </tr>
          <?php }?>
                </table>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows" id="type_html">
           <label>服务类型：</label>
           <span class="input">
           <select name="TypeID" style="width:180px;" id="Type_ID" notnull>
           <option value="">请选择类型</option>
              <!--  <option value="0" selected="selected"><?php echo $type['0']?></option>   -->            
              <option value="1" selected="selected"><?php echo $type['1']?></option>
           </select>
           <font class="fc_red">*</font>      
           <font class="fc_red">请选择默认类型!</font>
           </span>
           <div class="clear"></div>
        </div>      
      
        
        <div class="rows" id="type_html">
           <label>服务商类别：</label>
           <span class="input">
           <select class="provider" name="TypeID" style="width:180px;" 
           id="Type_ID" notnull>
				<option value="">请选择类型</option>
               <option value="0"<?php echo $ser==0 ? ' selected' : ''?>><?php echo $service['0']?></option>              
               <option value="1"<?php echo $ser==1 ? ' selected' : ''?>><?php echo $service['1']?></option>
           </select>
           <font class="fc_red">*</font>   
      <font class="fc_red">上次选择的是[<?php echo $se; ?>]</font>
           </span>
           <div class="clear"></div>
        </div>
        
        <div class="rows">
          <label>服务属性</label>
          <span class="input" id="attrs">
           <P>本服务属于即时到账服务</P>
          </span>
          <div class="clear"></div>
        </div>
       
        
        <div class="rows">
          <label>订单流程</label>
          <span class="input" style="font-size:12px; line-height:22px;">
              <p>买家下单 -> 买家付款 -> 订单完成 </p>
          </span>
          <div class="clear"></div>
        </div>
        
        <div id="select_category" class="lean-modal lean-modal-form">
       
          
      <div class="rows">
            <label></label>
            <span class="submit"><a class="modal_close" style="border-radius:8px;padding:5px 20px; color:#FFF; text-align:center; background:#3AA0EB" href="#">选好了</a></span>
            <div class="clear"></div>
          </div>
        </div>
      <div class="rows">
          <label>公告介绍</label>
          <span class="input">
          <textarea id="text" name="BriefDescription"><?php echo $res['Server_BriefDescription']?></textarea>
          </span>
          <div class="clear"></div>
        </div>
         
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="button" class="btn_green" name="button" value="确认更改" />
          <a href="serverlist.php" class="btn_gray">返回</a></span>
          <div class="clear"></div>
        </div>
        <input type='hidden' value='' id='cardids' name='cardids' />
        <input type="hidden" id="UsersID" value="<?php echo $UsersID;?>" />
        <input type="hidden" id="ServerID" value="<?php echo $serverid;?>" />                       
      </form>
    </div>
  </div>
</div>

    <script type="text/javascript">
    // 充值话费的价格  jq
        var index = 0;      
        $('.icon-plus').click(function(){
            index ++;
            var html = $(this).closest('.form-group').clone();
            html.find('.icon-plus').removeClass('icon-plus').addClass('icon-minus');
            html.find(".ProductsParametername").attr('name','Products_Parameter['+index+'][name]');
            html.find(".ProductsParametervalue").attr('name','Products_Parameter['+index+'][value]');
           
            $(this).closest('.form-group').after(html);
      $(".icon-minus").html("[-]");
        }); 
        $('.skipForm').on('click','.icon-minus',function(){
            $(this).closest('.form-group').remove();
        })
 //获取ajax要传的数据
 $('.btn_green').click(function(){
    var name=$('#name').val();
    var price=$('#price').val();
    var brokerage=$('#brokerage').val();
    var type='1';
    var text=$('#text').val();
    var usersid=$('#UsersID').val(); 
    var provider=$(".provider").children("option:selected").val(); 
    //获得服务id
    var serverid=$("#ServerID").val();
    var nobi = $('#nobi').val();
    var commission_ratio = $('#commission_ratio').val();

  //获得金额现价 数组
  var arr = new Array;
  $('.ProductsParametername').each(function(){
  var num =$(this).val();
  arr.push(num);
  });
  //获得金额原价 数组
  var array = new Array;
  $('.ProductsParametervalue').each(function(){
  var num =$(this).val();
  array.push(num);
  });
    var listDristribute = new Array;
  $('.listDristribute').each(function(){
  var s =$(this).val();
  listDristribute.push(s);
  });
    //开始Ajax
  var res;
      $.post("Aja.php",{name:name,newmoney:arr,oldmoney:array,price:price,brokerage:brokerage,type:type,text:text,provider:provider,usersid:usersid,serverid:serverid,nobi:nobi,commission_ratio:commission_ratio,listDristribute:listDristribute},function(data){
  //对返回值 进行判断
  var res=data.t;
  if (res){
    alert('便民服务更改成功!');
    location.href="/member/bianmin/serverlist.php" ;
  }else{
    alert('便民服务更改失败!');
  }   
    }, 'json');


 });
    </script>


<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>

</body>
</html>