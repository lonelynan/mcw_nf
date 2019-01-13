<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_SESSION["BIZ_ID"])){
	header("location:/biz/login.php");
}
function httpget($url,$data='') {
    $curl = curl_init();
	curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

function msg_m1($info){
	$url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$info['access_token'];
	$data='{
		"touser":"'.$info['openid'].'",
		"template_id":"aPZ0s_4uka8i-41500VlXm66hIw5xSTGe2G_yuzjRdk",
		"url":"'.$info['msg_url'].'",            
		"data":{
			"first":{
				"value":"",
				"color":"#000000"
			},
			"keyword1": {
				"value":"'.$info['msg_k1'].'",
				"color":"#0099ff"
			},
			"keyword2": {
				"value":"'.$info['msg_k2'].'",
				"color":"#0099ff"
			},
			"keyword3": {
				"value":"'.$info['msg_k3'].'",
				"color":"#0099ff"
			},
			"keyword4": {
				"value":"'.$info['msg_k4'].'",
				"color":"#0099ff"
			},
			"keyword5": {
				"value":"'.$info['msg_k5'].'",
				"color":"#0099ff"
			},

			"remark":{
				"value":"",
				"color":"#000000"
			}
		}
	}';
	httpget($url,$data);
}
function msg_m2($info){
	$url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$info['access_token'];
	$data='{
		"touser":"'.$info['openid'].'",
		"template_id":"kk2ad8xsWpdvaBhzd4HMeBd2IHvkfLIiF9vKGXH3weo",
		"url":"'.$info['msg_url'].'",            
		"data":{
			"first":{
				"value":"",
				"color":"#000000"
			},
			"keyword1": {
				"value":"'.$info['msg_k1'].'",
				"color":"#0099ff"
			},
			"keyword2": {
				"value":"'.$info['msg_k2'].'",
				"color":"#0099ff"
			},
			"remark":{
				"value":"",
				"color":"#000000"
			}
		}
	}';
	httpget($url,$data);
}
function msg_m3($info){
	$url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$info['access_token'];
	$data='{
		"touser":"'.$info['openid'].'",
		"template_id":"wNzvlnpmydt4QLFKeS9MtMb59TZ7pE-IhsI1y8xHK9o",
		"url":"'.$info['msg_url'].'",            
		"data":{
			"first":{
				"value":"",
				"color":"#000000"
			},
			"keyword1": {
				"value":"'.$info['msg_k1'].'",
				"color":"#0099ff"
			},
			"keyword2": {
				"value":"'.$info['msg_k2'].'",
				"color":"#0099ff"
			},
			"keyword3": {
				"value":"'.$info['msg_k3'].'",
				"color":"#0099ff"
			},
			"remark":{
				"value":"",
				"color":"#000000"
			}
		}
	}';
	httpget($url,$data);
}

$openid= isset($_GET['openid'])?trim($_GET['openid']):'';
if(!empty($openid)){
	$rsu=$DB->GetRs("user","User_NickName,User_No","where User_OpenID='".$openid."'");
	$str='会员['.$rsu['User_No'].']，昵称：'.$rsu['User_NickName'];
}
else{
	$str='群发全体会员';
}

if($_POST){
	$appid='wxb62088b16020bfbb';
	$secret='138bb96b319f7f835c55c275d90bcb84';
	$access_token_url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
	$wx = json_decode(httpget($access_token_url));
	
	if(!empty($_POST['openid'])){//发给指定个人
			if(trim($_POST['template'])=='aPZ0s_4uka8i-41500VlXm66hIw5xSTGe2G_yuzjRdk'){
				$data=array(
					'access_token'=>$wx->access_token,
					'openid'	=>trim($_POST['openid']),
					'msg_k1'	=>trim($_POST['msg1_k1']),
					'msg_k2'	=>trim($_POST['msg1_k2']),
					'msg_k3'	=>trim($_POST['msg1_k3']),
					'msg_k4'	=>trim($_POST['msg1_k4']),
					'msg_k5'	=>trim($_POST['msg1_k5']),
					'msg_url'	=>trim($_POST['msg1_url'])
				);
				msg_m1($data);
			}
			elseif(trim($_POST['template'])=='kk2ad8xsWpdvaBhzd4HMeBd2IHvkfLIiF9vKGXH3weo'){
				$data=array(
					'access_token'=>$wx->access_token,
					'openid'	=>trim($_POST['openid']),
					'msg_k1'	=>trim($_POST['msg2_k1']),
					'msg_k2'	=>trim($_POST['msg2_k2']),
					'msg_url'	=>trim($_POST['msg2_url'])
				);
				msg_m2($data);
			}
			elseif(trim($_POST['template'])=='wNzvlnpmydt4QLFKeS9MtMb59TZ7pE-IhsI1y8xHK9o'){
				$data=array(
					'access_token'=>$wx->access_token,
					'openid'	=>trim($_POST['openid']),
					'msg_k1'	=>trim($_POST['msg3_k1']),
					'msg_k2'	=>trim($_POST['msg3_k2']),
					'msg_k3'	=>trim($_POST['msg3_k3']),
					'msg_url'	=>trim($_POST['msg3_url'])
				);
				msg_m3($data);
			}
	}
	else{
		//获取线下会员
		$rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);
		if(empty($rsBiz['User_Mobile'])){
			echo "<script>alert('请先绑定会员!');window.location='bind_user.php';</script>";
			exit;
		}
		else{
			$rsU=$DB->GetRs("user","*","where User_Mobile='".$rsBiz["User_Mobile"]."'");
		}
		$psize = 99999;
		$condition = "where Owner_Id=".$rsU["User_ID"].' order by User_ID desc';
	
		$DB->getPage("user","*",$condition,$psize);
		$order_list = array();
		while($rr=$DB->fetch_assoc()){
			$order_list[] = $rr;
		}
		foreach($order_list as $rsOrder){
			if(trim($_POST['template'])=='aPZ0s_4uka8i-41500VlXm66hIw5xSTGe2G_yuzjRdk'){
				$data=array(
					'access_token'=>$wx->access_token,
					'openid'	=>$rsOrder['User_OpenID'],
					'msg_k1'	=>trim($_POST['msg1_k1']),
					'msg_k2'	=>trim($_POST['msg1_k2']),
					'msg_k3'	=>trim($_POST['msg1_k3']),
					'msg_k4'	=>trim($_POST['msg1_k4']),
					'msg_k5'	=>trim($_POST['msg1_k5']),
					'msg_url'	=>trim($_POST['msg1_url'])
				);
				msg_m1($data);
			}
			elseif(trim($_POST['template'])=='kk2ad8xsWpdvaBhzd4HMeBd2IHvkfLIiF9vKGXH3weo'){
				$data=array(
					'access_token'=>$wx->access_token,
					'openid'	=>$rsOrder['User_OpenID'],
					'msg_k1'	=>trim($_POST['msg2_k1']),
					'msg_k2'	=>trim($_POST['msg2_k2']),
					'msg_url'	=>trim($_POST['msg2_url'])
				);
				msg_m2($data);
			}
			elseif(trim($_POST['template'])=='wNzvlnpmydt4QLFKeS9MtMb59TZ7pE-IhsI1y8xHK9o'){
				$data=array(
					'access_token'=>$wx->access_token,
					'openid'	=>$rsOrder['User_OpenID'],
					'msg_k1'	=>trim($_POST['msg3_k1']),
					'msg_k2'	=>trim($_POST['msg3_k2']),
					'msg_k3'	=>trim($_POST['msg3_k3']),
					'msg_url'	=>trim($_POST['msg3_url'])
				);
				msg_m3($data);
			}
		}
	}
	echo '<script language="javascript">alert("消息推送成功");window.location="my_user.php";</script>';
	
	
	
	/*
    if(!preg_match("/^[1-9]\d*$/",$User_Mobile)){
        echo "<script>alert('手机输入有误!');window.location='bind_user.php';</script>";
        exit;
    }
    $rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);

    $UserRs = $DB->GetRs('user','*',"where Users_ID='".$rsBiz['Users_ID']."' and User_Mobile=".$User_Mobile);
    if(empty($UserRs)){
        echo "<script>alert('该手机不存在!');window.location='bind_user.php';</script>";
        exit;
    }else{
		 $UserRow = $DB->GetRs('biz','*','where User_Mobile='.$User_Mobile.' and Biz_ID!='.$_SESSION["BIZ_ID"]);
		 if($UserRow){
			  echo "<script>alert('该会员其他商家已被绑定!');window.location='bind_user.php';</script>";
			  exit;
		 }
        $data = array('User_Mobile'=>$User_Mobile);
        $Flag = $DB->Set('biz',$data,"where Biz_ID=".$_SESSION["BIZ_ID"]);
        if($Flag){
	}else{
		echo '<script language="javascript">alert("绑定失败");history.back();</script>';
	}
	exit;
    }	
	*/
}
/*
else{
    $rsBiz=$DB->GetRs("biz","*","where Biz_ID=".$_SESSION["BIZ_ID"]);
    $rsUsers=$DB->GetRs("users","Users_WechatAccount,Users_WechatName","where Users_ID='".$rsBiz["Users_ID"]."'");
}
*/


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
<style type="text/css">
.btn{background:#ddd;border: none !important;border-radius: 5px !important;color: #666;padding: 0px 20px !important;font-size: 12px;height: 30px !important;line-height: 28px !important;}
</style>
<script language="javascript">
function chkmsg(val){
	$('#msg1').hide();
	$('#msg2').hide();
	$('#msg3').hide();
	//alert(val+1);
	$('#msg'+(parseInt(val)+1)).show();
}
</script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/weicbd.css' rel='stylesheet' type='text/css' />
    <div id="bizs" class="r_con_wrap">
      <form class="r_con_form" method="post" action="?" id="biz_edit">
      <input name="openid" type="hidden" value="<?php echo $openid ?>">
        <div class="rows">
          <label>发送对像：</label>
          <span class="input"><?php echo $str ?></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>消息模板：</label>
          <span class="input">
          <select name="template" id="template" onChange="chkmsg(this.selectedIndex);">
            <option value="aPZ0s_4uka8i-41500VlXm66hIw5xSTGe2G_yuzjRdk">保养提醒</option>
            <option value="kk2ad8xsWpdvaBhzd4HMeBd2IHvkfLIiF9vKGXH3weo">抢单提醒</option>
            <option value="wNzvlnpmydt4QLFKeS9MtMb59TZ7pE-IhsI1y8xHK9o">秒杀参与提醒</option>
          </select>
          </span>
          <div class="clear"></div>
        </div>
        <!-- -->
        <div id="msg1">
            <div class="rows">
              <label>顾问姓名：</label>
              <span class="input">
              <input type="text" name="msg1_k1"  class="form_input" size="35" maxlength="50"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>顾问电话：</label>
              <span class="input">
              <input type="text" name="msg1_k2"  class="form_input" size="35" maxlength="50"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>车牌号码：</label>
              <span class="input">
              <input type="text" name="msg1_k3"  class="form_input" size="35" maxlength="50"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>当前里程：</label>
              <span class="input">
              <input type="text" name="msg1_k4"  class="form_input" size="35" maxlength="50"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>下次保养里程：</label>
              <span class="input">
              <input type="text" name="msg1_k5"  class="form_input" size="35" maxlength="50"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>页面链接：</label>
              <span class="input">
              <input type="text" name="msg1_url"  class="form_input" size="35"/>
              </span>
              <div class="clear"></div>
            </div>
        </div>
        <!-- -->
        <div id="msg2" style="display:none">
            <div class="rows">
              <label>结束时间：</label>
              <span class="input">
              <input type="text" name="msg2_k1"  class="form_input" size="35" maxlength="50"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>工作内容：</label>
              <span class="input">
              <input type="text" name="msg2_k2"  class="form_input" size="35"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>页面链接：</label>
              <span class="input">
              <input type="text" name="msg2_url"  class="form_input" size="35"/>
              </span>
              <div class="clear"></div>
            </div>
        </div>
        <!-- -->
        <div id="msg3" style="display:none">
            <div class="rows">
              <label>秒杀时间：</label>
              <span class="input">
              <input type="text" name="msg3_k1"  class="form_input" size="35" maxlength="50"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>秒杀物品：</label>
              <span class="input">
              <input type="text" name="msg3_k2"  class="form_input" size="35"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>秒杀数量：</label>
              <span class="input">
              <input type="text" name="msg3_k3"  class="form_input" size="35"/>
              </span>
              <div class="clear"></div>
            </div>
            <div class="rows">
              <label>活动链接：</label>
              <span class="input">
              <input type="text" name="msg3_url"  class="form_input" size="35"/>
              </span>
              <div class="clear"></div>
            </div>
        </div>
        <!-- -->
        <div class="rows">
          <label></label>
          <span class="input">
              <input type="submit" class="btn_green" name="submit_button" value="确定推送" />
              <input name="按钮" type="button" class="btn" value="返回上页" onClick="window.history.back();">
          </span>
          <div class="clear"></div>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>