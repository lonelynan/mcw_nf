<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}

  //当前usersid
  $UsersID=$_SESSION['Users_ID'];
  $DB->query("SELECT * FROM mycount WHERE usersid='".$UsersID."'");
  $res=$DB->fetch_assoc();

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
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/wechat.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/wechat.js'></script>
   <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/jiebase_menubar.php');?>
    <script language="javascript">$(document).ready(wechat_obj.set_token_init);</script>
    <div id="token" class="r_con_wrap">

      <form class="r_con_form" action="token_set.php" method="post">
        <div class="set_token_msg"></div>
        <div class="rows">
          <label>接口URL</label>
          <span class="input"><span class="tips">http://<?php echo $_SERVER['HTTP_HOST'].'/api/'.$_SESSION["Users_ID"]; ?></span></span>
          <div class="clear"></div>
        </div>

        <div class="rows">
          <label>AppSecret <span class="fc_red">*</span></label>
          <span class="input">
          <input name="WechatAppSecret" id="appsecret" value="<?php echo $res['appsecret']?>" type="text" class="form_input" placeholder="<?php echo $res['appsecret']?>"  notnull>
          </span>
          <div class="clear"></div>
        </div>
		
    <div class="rows">
          <label>AppKey <span class="fc_red">*</span></label>
          <span class="input">
          <input name="EncodingAESKey" id="EncodingAESKey" value="<?php echo $res['appkey']?>" type="text" class="form_input"  placeholder="<?php echo $res['appkey']?>" />          
          </span>
          <div class="clear"></div>
        </div>
    <div class="rows">
      <label>千米网账号</label>
      <span class="input">
      <input name="zhanhao" id="name" value="<?php echo $res['qianmizhanghao']?>" type="text" class="form_input"  placeholder="<?php echo $res['qianmizhanghao']?>" />          
      </span>
      <div class="clear"></div>
    </div>
    <div class="rows">
      <label>千米网密码</label>
      <span class="input">
      <input name="mima" id="password" value="<?php echo $res['qianmimima']?>" type="text" class="form_input"  placeholder="<?php echo $res['qianmimima']?>" />          
      </span>
      <div class="clear"></div>
    </div>
    <div class="rows">
      <label>千米网token</label>
      <span class="input">
      <input name="token" class="token" value="<?php echo $res['token']?>" type="text" class="form_input"  placeholder="<?php echo $res['token']?>" />          
      </span>
      <div class="clear"></div>
    </div>
    <div class="rows">
      <label>refreshToken</label>
      <span class="input">
      <input name="refreshToken" id="refreshToken" value="<?php echo $res['refreshToken']?>" type="text" class="form_input"  placeholder="<?php echo $res['refreshToken']?>" />          
      </span>
      <div class="clear"></div>
    </div>

        <div class="rows">
          <label></label>
          <span class="input">
          <input type="button" id="sub" name="submit_button" value="设置" />
          <input type="button" id="updata" name="submit_button" value="更改" />
          </span>
          <div class="clear"></div>
        </div>
      
      </form>
    </div>
  </div>
</div>
<script>

//设置点击事件
$('#sub').click(function(){

//获取AppKey
var appsecret=$('#appsecret').val();
//获取accessToken
var aeskey= $('#EncodingAESKey').val();
// 获取账号
var zhang=$('#name').val();
// 获取密码
var password=$('#password').val();
// 获取token
var toke=$('.token').val();
// 获取refreshToken
var rtoken=$('#refreshToken').val();
//发送ajax

      var res;
      $.post("Ajax.php",{secret:appsecret,key:aeskey,zhang:zhang,pass:password,token:toke,refresh:rtoken},function(data){
        //对返回值 进行判断
      var res=data.t; 
      if(res==2){
        alert('您已经添加过了 请前往修改页面');
      }else{
            if(res){
              alert('添加成功');
              location.href="/member/bianmin/serverlist.php";
            }else{
              alert('添加失败');
            } 
      }
    }, 'json');

 });

$('#updata').click(function(){
var i=confirm('您好 您确定修改配置?');
//限制
var x='yes';
//获取AppKey
var appsecret=$('#appsecret').val();
//获取accessToken
var aeskey= $('#EncodingAESKey').val();
// 获取账号
var zhang=$('#name').val();
// 获取密码
var password=$('#password').val();
// 获取token
var toke=$('.token').val();
// 获取refreshToken
var rtoken=$('#refreshToken').val();  
    if(i){
                $.post("Ajax.php",{secret:appsecret,key:aeskey,zhang:zhang,pass:password,token:toke,refresh:rtoken,xian:x},function(data){
                var res=data.t; 
                if(!res){
                  alert('您的数据库遭遇非法数据更改!!!!');
                }
          }, 'json');
    }


})






</script>
</body>
</html>