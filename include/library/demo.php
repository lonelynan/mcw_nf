<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/mysql.inc.php');
$DB=new mysql($host="localhost",$user="root",$pass="btsp@34sdafsadf#@",$data="xywwx",$code="utf8",$conn="conn");
$REQUEST_URI=explode("?",$_SERVER["REQUEST_URI"]);
$REQUEST_URI=empty($REQUEST_URI[1])?array():explode('&',$REQUEST_URI[1]);
foreach($REQUEST_URI as $value)
{
	list($k,$v)=explode('=',$value);
	$_GET[$k]=$v;
}

if(isset($_GET["UsersID"])){
	if($_GET["UsersID"]=="index")
	{
		echo '缺少必要的参数';
		exit;
	}else
	{
		$UsersID=$_GET["UsersID"];
	}
}else{
	echo '缺少必要的参数';
	exit;
}
$rsUsers=$DB->GetRs("users","*","where Users_ID='".$UsersID."'");
define("WX_USER_APPID", $rsUsers["Users_WechatAppId"]);
define("WX_USER_SECRET", $rsUsers["Users_WechatAppSecret"]);
define("UsersID", $UsersID);
define("TOKEN",$rsUsers["Users_WechatToken"]);
$wechatObj = new wechatCallbackapi();
if(isset($_GET["echostr"]))
{
	$wechatObj->valid();
}
if(isset($GLOBALS["HTTP_RAW_POST_DATA"]))
{
	$Data=array(
		"HTTP_RAW_POST_DATA"=>$GLOBALS["HTTP_RAW_POST_DATA"],
		"CreateTime"=>time()
	);
	$DB->Add("HTTP_RAW_POST_DATA",$Data);
	$wechatObj->responseMsg();
}
class wechatCallbackapi{
    public function responseMsg(){
		//接收POST过来的数据
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');

      	//提取出的数据
		if (!empty($postStr)){
                //用SimpleXML解析POST过来的XML数据
              	$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;		//获取发送方帐号（OpenID）
                $toUsername = $postObj->ToUserName;			//获取接收方账号
                $keyword = trim($postObj->Content);			//获取消息内容
                $time = time();								//获取当前时间戳
				//---------- 返 回 数 据 ---------- //
				
				//返回消息模板
				$Tpl = require 'conf.php';
				$msgType = 'text';
				$textTpl = $Tpl[$msgType];
				$contentStr='';
				if($postObj->MsgType=='event'){
					if($postObj->Event=='subscribe'){
						$rsReply=$DB->GetRs("wechat_attention_reply","*","where Users_ID='".UsersID."'");
						if($rsReply){
							if($rsReply['Reply_MsgType']){								
								//取出回复的图文信息
								$rsMaterial=$DB->GetRs("wechat_material","Material_Type,Material_Json","where Users_ID='".UsersID."' and Material_ID=".$rsReply['Reply_MaterialID']);
								$Material_Json=json_decode($rsMaterial['Material_Json'],true);
								if(!empty($Material_Json["TextContents"])){
								    $Material_Json["TextContents"] = str_replace("<br />","\n",$Material_Json["TextContents"]);
								}
								$array = array();
								//如果是单图文的话,如果是多图文的话
								if($rsMaterial['Material_Type']){
									foreach($Material_Json as $key=>$value){
										$array[] =array(
											"Title"=>$value["Title"],
											"TextContents"=>"",
											"ImgPath"=>strpos($value["ImgPath"],"http://")>-1?$value["ImgPath"]:"http://".$_SERVER['HTTP_HOST'].$value["ImgPath"],
											"Url"=>strpos($value["Url"],"http://")>-1?$value["Url"].'?OpenID='.$fromUsername:"http://".$_SERVER['HTTP_HOST'].$value["Url"].'?OpenID='.$fromUsername
										);
									}
								}else{
									$array[] =array(
										"Title"=>$Material_Json["Title"],
										"TextContents"=>$Material_Json["TextContents"],
										"ImgPath"=>strpos($Material_Json["ImgPath"],"http://")>-1?$Material_Json["ImgPath"]:"http://".$_SERVER['HTTP_HOST'].$Material_Json["ImgPath"],
										"Url"=>strpos($Material_Json["Url"],"http://")>-1?$Material_Json["Url"].'?OpenID='.$fromUsername:"http://".$_SERVER['HTTP_HOST'].$Material_Json["Url"].'?OpenID='.$fromUsername
									);
								}
								
								$msgType = "news";								
								  $textHeadTpl = $Tpl[$msgType]["Head"];
								  $resultHeadStr = sprintf($textHeadTpl, $fromUsername, $toUsername, $time, $msgType, count($array));
								  
								  $textContentTpl = $Tpl[$msgType]["Content"];
								  $resultContentStr = "";
								  foreach ($array as $key => $value)
								  {
									  $resultContentStr .= sprintf($textContentTpl, $value['Title'], $value['TextContents'], $value['ImgPath'], $value['Url']);
								  }
								  
								  $textFooterTpl = $Tpl[$msgType]["Footer"];
								  $resultFooterStr = sprintf($textFooterTpl);
								  echo $resultHeadStr . $resultContentStr . $resultFooterStr;
								  exit;
							}else{
								$contentStr=$rsReply['Reply_TextContents'];
							}
						}else{
							$contentStr='未设置关注时回复';
						}						
					}elseif($postObj->Event=="unsubscribe"){
						$contentStr = "取消订阅成功！";
					}elseif($postObj->Event=="CLICK"){
						$EventKey=explode("_",$postObj->EventKey);
						if($EventKey[0]=="MenuID"){
							$rsMenu=$DB->GetRs("wechat_menu","Menu_TextContents","where Users_ID='".UsersID."' and Menu_ID='".$EventKey[1]."'");
							if($rsMenu){
								$contentStr = $rsMenu["Menu_TextContents"];
							}
						}elseif($EventKey[0]=="MaterialID"){
							//取出回复的图文信息
							$rsMaterial=$DB->GetRs("wechat_material","Material_Type,Material_Json","where Users_ID='".UsersID."' and Material_ID=".$EventKey[1]);
							$Material_Json=json_decode($rsMaterial['Material_Json'],true);
							if(!empty($Material_Json["TextContents"])){
								      			$Material_Json["TextContents"] = str_replace("<br />","\n",$Material_Json["TextContents"]);
}
							$array = array();
							//如果是单图文的话,如果是多图文的话
							if($rsMaterial['Material_Type']){
								foreach($Material_Json as $key=>$value){
									$array[] =array(
										"Title"=>$value["Title"],
										"TextContents"=>"",
										"ImgPath"=>strpos($value["ImgPath"],"http://")>-1?$value["ImgPath"]:"http://".$_SERVER['HTTP_HOST'].$value["ImgPath"],
										"Url"=>strpos($value["Url"],"http://")>-1?$value["Url"].'?OpenID='.$fromUsername:"http://".$_SERVER['HTTP_HOST'].$value["Url"].'?OpenID='.$fromUsername
									);
								}
							}else{
								$array[] =array(
									"Title"=>$Material_Json["Title"],
									"TextContents"=>$Material_Json["TextContents"],
									"ImgPath"=>strpos($Material_Json["ImgPath"],"http://")>-1?$Material_Json["ImgPath"]:"http://".$_SERVER['HTTP_HOST'].$Material_Json["ImgPath"],
									"Url"=>strpos($Material_Json["Url"],"http://")>-1?$Material_Json["Url"].'?OpenID='.$fromUsername:"http://".$_SERVER['HTTP_HOST'].$Material_Json["Url"].'?OpenID='.$fromUsername
								);
							}
								  
							$msgType = "news";								
							$textHeadTpl = $Tpl[$msgType]["Head"];
							$resultHeadStr = sprintf($textHeadTpl, $fromUsername, $toUsername, $time, $msgType, count($array));
							$textContentTpl = $Tpl[$msgType]["Content"];
							$resultContentStr = "";
							foreach ($array as $key => $value){
								$resultContentStr .= sprintf($textContentTpl, $value['Title'], $value['TextContents'], $value['ImgPath'], $value['Url']);
							}
							$textFooterTpl = $Tpl[$msgType]["Footer"];
							$resultFooterStr = sprintf($textFooterTpl);
							echo $resultHeadStr . $resultContentStr . $resultFooterStr;
							exit;
						}						
					}
				}elseif($postObj->MsgType=="text"){
					$item = $DB->GetRs("wall_user","*","where Users_ID='".UsersID."' and Open_ID='".$fromUsername."' and Status=1");					
					if($item){//进入微信墙刷墙
						$rsW = $DB->GetRs("wall","*","where Wall_ID=".$item["Wall_ID"]);
						$msgType = "text";
						$textTpl = $Tpl[$msgType];
						$step = intval($item["Step"]);
						switch($step){
							case 0://未打开微信墙
								if($keyword=="退出"){//退出微信墙
									$Data = array(
										"Status" => 0,
										"Step" => 0
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "已退出微信墙系统，感谢您的光临！";
								}elseif($keyword=="投票" && $rsW["Wall_VotesDisplay"]==1 && $rsW["Wall_VotesItems"]){//投票
									$Data = array(
										"Step" => 3
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$VotesItems = json_decode($rsW["Wall_VotesItems"],true);
									$contentStr = "( ".$VotesItems[0]." )回复选项对应的数字进行投票。";
									$i=0;
									foreach($VotesItems as $k=>$v){
										if($k==0 || empty($v)){
											continue;
										}else{
											$i++;
											$contentStr .= $i.".".$v." ";											
										}
									}
								}elseif($keyword=="#"){//打开微信墙，进入文字模式
									$Data = array(
										"Step" => 1
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "已进入图文系统，请输入您想说的内容！";
								}else{
									$Data = array(
										"Status" => 0,
										"Step" => 0
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "请重新输入内容！";
								}
							break;
							case 1://文字模式
								if($keyword=="退出"){//退出微信墙
									$Data = array(
										"Status" => 0,
										"Step" => 0
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "已退出微信墙系统，感谢您的光临！";
								}else{
									$Data = array(
										"Users_ID" => UsersID,
										"Open_ID" => $fromUsername,
										"Wall_ID" => $item["Wall_ID"],
										"Content" => $keyword,
										"Type" => 0,
										"Status"=>$rsW["Wall_NeedCheck"]==1 ? 0 : 1,
										"CreateTime" => time()
									);
									$DB->Add("wall_content",$Data);
									$Data = array(
										"Step" => 2
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "内容已记录，请发送图片！";
								}
							break;
							case 2://需要图片
								if($keyword=="退出"){//退出微信墙
									$Data = array(
										"Status" => 0,
										"Step" => 0
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "已退出微信墙系统，感谢您的光临！";
								}else{
									$contentStr = "请发送图片！";
								}								
							break;
							case 3://投票
								if($keyword=="退出"){//退出微信墙
									$Data = array(
										"Status" => 0,
										"Step" => 0
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "已退出微信墙系统，感谢您的光临！";
								}else{
									$VotesItems = json_decode($rsW["Wall_VotesItems"],true);								
									$option = array();
									$i=0;
									foreach($VotesItems as $k=>$v){
										if($k==0 || empty($v)){
											continue;
										}else{
											$i++;
											$option[] = $i;										
										}
									}
									if(in_array($keyword,$option)){
										$Data = array(
											"Users_ID" => UsersID,
											"Open_ID" => $fromUsername,
											"Wall_ID" => $item["Wall_ID"],
											"Option_ID" => intval($keyword),
											"CreateTime" => time()
										);
										$DB->Add("wall_votes",$Data);
										$Data = array(
											"Step" => 0
										);
										$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
										$contentStr = "投票成功！感谢你的支持";
									}else{
										$contentStr = "请回复选项对应的数字";
									}
								}
							break;
							case 4:
								if($keyword=="退出"){//退出微信墙
									$Data = array(
										"Status" => 0,
										"Step" => 0
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "已退出微信墙系统，感谢您的光临！";
								}else{
									$Data = array(
										"nickname" => $keyword,
										"Step"=>5
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "昵称设置成功，请发送你的头像图片";
								}
							break;
						}
					}else{
						$msgType = "text";
						$textTpl = $Tpl[$msgType];
						if(empty($keyword)){
							$contentStr = "请说些什么...";
						}else{
							$rsRelax = $DB->GetRs("relax","*","where Users_ID='".UsersID."'");//娱乐休闲
							$ischengyu = empty($rsRelax) ? 0 : $rsRelax["Is_Chengyu"];
							$ismengjian = empty($rsRelax) ? 0 : $rsRelax["Is_Mengjian"];
							$isfanyi = empty($rsRelax) ? 0 : $rsRelax["Is_Fanyi"];
							$isshouji = empty($rsRelax) ? 0 : $rsRelax["Is_Shouji"];
							$isshenfen = empty($rsRelax) ? 0 : $rsRelax["Is_Shenfen"];
							$isxiaohua = empty($rsRelax) ? 0 : $rsRelax["Is_Xiaohua"];
							$istianqi = empty($rsRelax) ? 0 : $rsRelax["Is_Tianqi"];
							$isxingzuo = empty($rsRelax) ? 0 : $rsRelax["Is_Xingzuo"];
							$isrenpin = empty($rsRelax) ? 0 : $rsRelax["Is_Renpin"];
							$isnaojin = empty($rsRelax) ? 0 : $rsRelax["Is_Naojin"];
							if($ischengyu==1 && strpos($keyword,'成语')!==false){//成语解释
								$keyword = str_replace('成语', '', $keyword);
								$url = "http://api.uihoo.com/chengyu/chengyu.http.php?format=json&key=".urlencode($keyword);
								$huochestr = file_get_contents($url);
								$a = json_decode($huochestr, true);
								$contentStr = "成语：".$a['0']['chengyu']."\r\n拼音：".$a['0']['pinyin']."\r\n典故：". $a['0']['diangu']."\r\n出处：".$a['0']['chuchu'];
							}elseif($ismengjian==1 && strpos($keyword,'梦见')!==false){//梦见
								$keyword = str_replace('梦见', '', $keyword);
								$url = "http://api.uihoo.com/dream/dream.http.php?format=json&key=".$keyword;
								$huochestr = file_get_contents($url);
								$re = json_decode($huochestr, true);
								$n = count($re);
								if($n>20)
								{
									$n = 20;
								}
								for($a=1;$a<$n+1;$a++)
								{
									$contentStr .= $a.".".$re[$a-1]."\r\n";
								}
							}elseif($isfanyi==1 && strpos($keyword,'翻译')===0){//翻译
								$keyword = str_replace('翻译', '', $keyword);
								$res = file_get_contents('http://openapi.baidu.com/public/2.0/bmt/translate?client_id=071wsxr5yxF3SLlrbI2NiMVG&from=auto&to=auto&q='.urlencode($keyword));
								if(!empty($res)){
									$fanyijson = json_decode($res,true);
									$contentStr = "翻译:".$fanyijson['trans_result']['0']['src']."\r\nfrom:".$fanyijson['from']."  to:".$fanyijson['to']."\r\nresult:\r\n".$fanyijson['trans_result']['0']['dst'];
								}
							}elseif($isshouji==1 && strpos($keyword,'手机')!==false){//手机归属地
								$keyword = str_replace('手机', '', $keyword);
								$url = 'http://api.uihoo.com/mobile/mobile.http.php?format=json&mobile='.$keyword;
								$huochestr = file_get_contents($url);
								$a = json_decode($huochestr, true);
								$contentStr = "手机号码：".$keyword."\r\n归属地：".$a['city']."\r\n手机卡类型：".$a['type'];
							}elseif($isshenfen==1 && strpos($keyword,'身份证')===0){//身份证号查询
								$keyword = str_replace('身份证', '', $keyword);
								$url = 'http://api.uihoo.com/idcard/idcard.http.php?format=json&idnumber='.$keyword;
								$huochestr = file_get_contents($url);
								$a = json_decode($huochestr, true);
								$contentStr = "身份证：".$keyword."\r\n性别：".($a['sex']==1 ? '男' : '女')."\r\n出生年月：".$a['birthday']."\r\n地区：".$a["region"];
							}elseif($isxiaohua==1 && $keyword=='笑话'){//笑话
								$url = 'http://www.tuling123.com/openapi/api?key=96b7fdc1bf8ed9c24b486ceed71747ad&info='.$keyword;
								$res = file_get_contents($url);
								$res = json_decode($res);
								if(!empty($res)){
									$contentStr = $res->text;
								}
							}elseif($istianqi==1 && strpos($keyword,'天气')===0){//天气查询
								$url = 'http://www.tuling123.com/openapi/api?key=96b7fdc1bf8ed9c24b486ceed71747ad&info='.$keyword;
								$res = file_get_contents($url);
								$res = json_decode($res);
								if(!empty($res)){
									$contentStr = $res->text;
								}
							}elseif($isxingzuo==1 && strpos($keyword,'星座')===0){//星座运势
								if($keyword !='星座'){
									$keyword = str_replace('星座', '', $keyword);
								}
								$xingzuo = array('白羊座','金牛座','双子座','巨蟹座','狮子座','处女座','天秤座','天蝎座','射手座','摩羯座','水瓶座','双鱼座');
								$level = array('','差','中','良','好','很好');
								$appid = 0;
								foreach($xingzuo as $k=>$v){
									if($v==$keyword){
										$appid=$k;
									}
								}
								$res = file_get_contents('http://api.uihoo.com/astro/astro.http.php?fun=day&format=json&id='.$appid);
								$res = json_decode($res,true);
								$contentStr = $keyword."今日运势\n\r";
								foreach($res as $k=>$v){
									if($k<=9){
										$contentStr .= $v["title"].':'.(intval($v["rank"])>0 ? $level[$v["rank"]] : $v["value"])."\n\r";
									}
								}
							}elseif($isrenpin==1 && strpos($keyword,'人品')===0){//人品
								$keyword = str_replace('人品', '', $keyword);
								$rp = base_convert(substr(md5(md5($keyword)),26),36,10);
								$rp = (2*($rp%100))%100;
								if($rp == 0){
									$res =  '你一定不是人吧？怎么一点人品都没有？！';
								}elseif (1<= $rp && $rp <=5){
									$res = '算了，跟你没什么人品好谈的...';
								}elseif(6<= $rp && $rp <=10){
									$res = '是我不好...不应该跟你谈人品问题的...';
								}elseif(11<= $rp && $rp <=15){
									$res = '杀过人没有?放过火没有?你应该无恶不做吧?';
								}elseif(16<= $rp && $rp <=20){
									$res = '你貌似应该三岁就偷看隔壁大妈洗澡的吧...';
								}elseif(21<= $rp && $rp <=25){
									$res = '你的人品之低下实在让人惊讶啊...';
								}elseif(26<= $rp && $rp <=30){
									$res = '你的人品太差了。你应该有干坏事的嗜好吧?';
								}elseif(31<= $rp && $rp <=35){
									$res = '你的人品真差!肯定经常做偷鸡摸狗的事...';
								}elseif(36<= $rp && $rp <=40){
									$res = '你拥有如此差的人品请经常祈求佛祖保佑你吧...';
								}elseif(41<= $rp && $rp <=45){
									$res = '老实交待..那些论坛上面经常出现的偷拍照是不是你的杰作?';
								}elseif(46<= $rp && $rp <=50){
									$res = '你随地大小便之类的事没少干吧?';
								}elseif(51<= $rp && $rp <=55){
									$res = '你的人品太差了..稍不小心就会去干坏事了吧?';
								}elseif(56<= $rp && $rp <=60){
									$res = '你的人品很差了..要时刻克制住做坏事的冲动哦..';
								}elseif(61<= $rp && $rp <=65){
									$res = '你的人品比较差了..要好好的约束自己啊..';
								}elseif(66<= $rp && $rp <=70){
									$res = '你的人品勉勉强强..要自己好自为之..';
								}elseif(71<= $rp && $rp <=75){
									$res = '有你这样的人品算是不错了..';
								}elseif(76<= $rp && $rp <=80){
									$res = '你有较好的人品..继续保持..';
								}elseif(81<= $rp && $rp <=85){
									$res = '你的人品不错..应该一表人才吧?';
								}elseif(86<= $rp && $rp <=90){
									$res = '你的人品真好..做好事应该是你的爱好吧..';
								}elseif(91<= $rp && $rp <=95){
									$res = '你的人品太好了..你就是当代活雷锋啊...';
								}elseif(96<= $rp && $rp <=99){
									$res = '你是世人的榜样！';
								}elseif( $rp == 100){
									$res = '天啦！你不是人！你是神！！！';
								}else{
									$res = '么么哒，你的人品爆棚了，宇宙第一大好人！！！';
								}
								$contentStr = '【'.$keyword.'的人品:】  '.$rp.'分'."\r\n".$res."\n\r人品内容纯属虚构，仅供娱乐之用，切勿当真！";
							}elseif($isnaojin==1 && $keyword=='脑筋急转弯'){//脑筋急转弯
								$url = 'http://www.tuling123.com/openapi/api?key=96b7fdc1bf8ed9c24b486ceed71747ad&info='.$keyword;
								$res = file_get_contents($url);
								$res = json_decode($res);
								if(!empty($res)){
									$contentStr = $res->text;
								}
							}else{
								$rsReply=$DB->GetRs("wechat_keyword_reply","Reply_TextContents,Reply_MsgType,Reply_MaterialID","where Users_ID='".UsersID."' and Reply_PatternMethod=0 and Reply_Keywords='".$keyword."' order by Reply_Table desc,Reply_ID desc");
								if(empty($rsReply)){
									$rsReply=$DB->GetRs("wechat_keyword_reply","Reply_TextContents,Reply_MsgType,Reply_MaterialID","where Users_ID='".UsersID."' and Reply_PatternMethod=1 and Reply_Keywords like '%".$keyword."%' order by Reply_Table desc,Reply_ID desc");
								}
								if(!$rsReply){
									$rsReply=$DB->GetRs("wechat_attention_reply","Reply_TextContents,Reply_MsgType,Reply_MaterialID","where Users_ID='".UsersID."' and Reply_Subscribe=1");
								}
								if($rsReply){
									if($rsReply["Reply_MsgType"]){
										  $rsMaterial=$DB->GetRs("wechat_material","Material_Type,Material_Json","where Users_ID='".UsersID."' and Material_ID=".$rsReply["Reply_MaterialID"]);
										  $Material_Json=json_decode($rsMaterial['Material_Json'],true);
										  if(!empty($Material_Json["TextContents"])){
														$Material_Json["TextContents"] = str_replace("<br />","\n",$Material_Json["TextContents"]);
										  }
										  $array = array();
										  if($rsMaterial['Material_Type']){
											  foreach($Material_Json as $key=>$value){
												  $array[] =array(
													  "Title"=>$value["Title"],
													  "TextContents"=>"",
													  "ImgPath"=>strpos($value["ImgPath"],"http://")>-1?$value["ImgPath"]:"http://".$_SERVER['HTTP_HOST'].$value["ImgPath"],
													  "Url"=>strpos($value["Url"],"http://")>-1?$value["Url"].'?OpenID='.$fromUsername:"http://".$_SERVER['HTTP_HOST'].$value["Url"].'?OpenID='.$fromUsername
												  );
											  }
										  }else{
											  $array[] =array(
												  "Title"=>$Material_Json["Title"],
												  "TextContents"=>$Material_Json["TextContents"],
												  "ImgPath"=>strpos($Material_Json["ImgPath"],"http://")>-1?$Material_Json["ImgPath"]:"http://".$_SERVER['HTTP_HOST'].$Material_Json["ImgPath"],
												  "Url"=>strpos($Material_Json["Url"],"http://")>-1?$Material_Json["Url"].'?OpenID='.$fromUsername:"http://".$_SERVER['HTTP_HOST'].$Material_Json["Url"].'?OpenID='.$fromUsername
											  );
										  }
										  
										  $msgType = "news";								
										  $textHeadTpl = $Tpl[$msgType]["Head"];
										  $resultHeadStr = sprintf($textHeadTpl, $fromUsername, $toUsername, $time, $msgType, count($array));
										  
										  $textContentTpl = $Tpl[$msgType]["Content"];
										  $resultContentStr = "";
										  foreach ($array as $key => $value)
										  {
											  $resultContentStr .= sprintf($textContentTpl, $value['Title'], $value['TextContents'], $value['ImgPath'], $value['Url']);
										  }
										  
										  $textFooterTpl = $Tpl[$msgType]["Footer"];
										  $resultFooterStr = sprintf($textFooterTpl);
										  echo $resultHeadStr . $resultContentStr . $resultFooterStr;
										  exit;
									}else{
										  $rwall=$DB->GetRs("wechat_keyword_reply","Reply_TableID","where Users_ID='".UsersID."' and Reply_PatternMethod=0 and Reply_Keywords='".$keyword."' and Reply_Table='wall' order by Reply_TableID desc,Reply_ID desc");
										  if($rwall){//触发微信墙
										  	  $rwall_user=$DB->GetRs("wall_user","*","where Users_ID='".UsersID."' and Wall_ID=".$rwall["Reply_TableID"]." and Open_ID='".$fromUsername."'");
											  if($rwall_user){
												  $Data = array(
													"Status" => 1
												  );
												  if($rwall_user["nickname"]==""){
													  $Data["Step"] = 4;
													  $contentStr = "请发送你的昵称，长度请控制在0-10个字符";
												  }elseif($rwall_user["headimg"]==""){
													  $Data["Step"] = 5;
													  $contentStr = "请发送你的头像图片";
												  }else{
													  $Data["Step"] = 0;
													  if(!strpos($rsReply["Reply_TextContents"],'">"')){
														$contentStr = $rsReply["Reply_TextContents"];
													  }else{
													    $search = '">"';
														$repace = $fromUsername.'/">"';
														$contentStr = str_replace($search,$repace,$rsReply["Reply_TextContents"]);
													  }
												  }
												  $DB->Set("wall_user",$Data,"where Wall_ID=".$rwall["Reply_TableID"]." and Open_ID='".$fromUsername."'");
											  }else{
												  $Data = array(
													"Users_ID" => UsersID,
													"Open_ID" => $fromUsername,
													"Wall_ID" => $rwall["Reply_TableID"],
													"Status" => 1,
													"CreateTime" => time()
												  );
												  $userinfo = $this->getuserinfo($fromUsername);
												  if(count($userinfo)>0){
													  $Data["Step"]=0;
													  $Data["headimg"] = $userinfo["headimgurl"];
													  $Data["nickname"] = $userinfo["nickname"];
													  if(!strpos($rsReply["Reply_TextContents"],'">"')){
														$contentStr = $rsReply["Reply_TextContents"];
													  }else{
													    $search = '">"';
														$repace = $fromUsername.'/">"';
														$contentStr = str_replace($search,$repace,$rsReply["Reply_TextContents"]);
													  }													  
												  }else{
														$Data["Step"]=4;
														$contentStr = "请发送你的昵称，长度请控制在0-10个字符";
												  }
												  $DB->Add("wall_user",$Data);
											  }
										  }else{
											 $contentStr = $rsReply["Reply_TextContents"]; 
										  }										  									  
									}
								}
							}
						}
					}
				}elseif($postObj->MsgType=="image")
				{
					$item = $DB->GetRs("wall_user","*","where Users_ID='".UsersID."' and Open_ID='".$fromUsername."' and Status=1");
					if($item){//进入微信墙刷墙
						$rsW = $DB->GetRs("wall","*","where Wall_ID=".$item["Wall_ID"]);
						$msgType = "text";
						$textTpl = $Tpl[$msgType];
						$step = intval($item["Step"]);
						switch($step){
							case 1://文字模式
								$contentStr = "请发送内容！";
							break;
							case 2://需要图片
								$Data = array(
									"Users_ID" => UsersID,
									"Open_ID" => $fromUsername,
									"Wall_ID" => $item["Wall_ID"],
									"Content" => $postObj->PicUrl,
									"Type" => 1,
									"Status"=>$rsW["Wall_NeedCheck"]==1 ? 0 : 1,
									"CreateTime" => time()
								);
								$DB->Add("wall_content",$Data);
								$Data = array(
									"Step" => 1
								);
								$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
								$contentStr = "图片已保存！";
							break;
							case 5:
								if($keyword=="退出"){//退出微信墙
									$Data = array(
										"Status" => 0,
										"Step" => 0
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "已退出微信墙系统，感谢您的光临！";
								}else{
									$Data = array(
										"headimg" => $postObj->PicUrl,
										"Step" => 0
									);
									$DB->Set("wall_user",$Data,"where Itemid=".$item["Itemid"]);
									$contentStr = "头像图片设置成功";
								}
							break;
						}
					}else{
						$msgType = "image";
						$textTpl = $Tpl[$msgType];
						//$contentStr = "您发来的是图片消息！\n图片地址为".$postObj->PicUrl;
						$contentStr =$postObj->MediaId;
					}
				}elseif($postObj->MsgType=="voice")
				{
					$msgType = "voice";
					$textTpl = $Tpl[$msgType];
					//$contentStr = "您发来的是语音消息！\n语音格式为".$postObj->Format."\nMediaId为".$postObj->MediaId;
					$contentStr =$postObj->MediaId;
				}elseif($postObj->MsgType=="video")
				{
					$msgType = "text";
					$contentStr = "您发来的是视频消息！\n视频消息缩略图的媒体id为".$postObj->ThumbMediaId."\nMediaId为".$postObj->MediaId;
				}elseif($postObj->MsgType=="location")
				{
					$msgType = "text";
					$contentStr = "您发来的是地理位置消息！\n地理位置维度".$postObj->Location_X."\n地理位置经度".$postObj->Location_Y."\n地图缩放大小".$postObj->Scale."\n地理位置信息".$postObj->Label;
				}elseif($postObj->MsgType=="link")
				{
					$msgType = "text";
					$contentStr = "您发来的是链接消息！\n消息标题".$postObj->Title."\n消息描述".$postObj->Description."\n消息链接".$postObj->Url;
				}
				
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;

        }else {
        	echo "";
        	exit;
        }
    }
	
	//系统自带的
	public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
        	echo $echoStr;
        	exit;
        }
    }
	
	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
        		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
	private function getuserinfo($openid){
		$userinfos = array();
		if(WX_USER_APPID && WX_USER_SECRET){
			$wx_config["APPID"]=WX_USER_APPID;
			$wx_config["APPSECRET"]=WX_USER_SECRET;
			$wx_config["ACCESS_TOKEN"]='';
			$wx_config["EXPIRES_IN"]=time();			
			if($wx_config['EXPIRES_IN']<=time()){
				$token_str = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$wx_config["APPID"].'&secret='.$wx_config["APPSECRET"]);
				$token_arr = json_decode($token_str,true);
				if(empty($token_arr["errcode"])){
					$wx_config['ACCESS_TOKEN'] = $token_arr["access_token"];
					$wx_config['EXPIRES_IN'] = time()+intval($token_arr["expires_in"]);
					$userinfo_str = file_get_contents('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$wx_config['ACCESS_TOKEN'].'&openid='.$openid);
					$encoding = mb_detect_encoding($userinfo_str, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
					$userinfo_str = mb_convert_encoding($userinfo_str, 'utf-8', $encoding);
					$userinfos = json_decode($userinfo_str,JSON_UNESCAPED_UNICODE);
				}
			}			
		}
		return $userinfos;
	}
}
?>