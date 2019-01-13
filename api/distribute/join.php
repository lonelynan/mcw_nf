<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/lib_products.php');

$base_url = base_url();
$shop_url = shop_urlwzw();
$shop_urldelwzw = str_replace('wzw/','',$shop_url);

if(isset($_GET["UsersID"])){
  $UsersID = $_GET["UsersID"];
}else{
  echo '缺少必要的参数';
  exit;
}
$_SESSION[$UsersID."HTTP_REFERER"] = "/api/".$UsersID."/distribute/?love";
$shop_url = shop_urlwzw();

//商城配置信息
$rsConfig = shop_config($UsersID);
//分销相关设置
$dis_config = dis_config($UsersID);
//合并参数
$rsConfig = array_merge($rsConfig,$dis_config);
//获取底部菜单样式
$styles = !empty($rsConfig['bottom_styles'])?$rsConfig['bottom_styles']:'';
$is_login = 1;
$owner = get_owner($rsConfig,$UsersID);
//require_once $_SERVER["DOCUMENT_ROOT"] . '/include/library/wechatuser.php';

//开始 wechatuser.php 文件
require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/userinfo.class.php');
$rswechat = $DB->GetRs("users","Users_WechatAppId,Users_WechatAppSecret,Users_WechatType","where Users_ID='".$UsersID."'");
if($rswechat["Users_WechatAppId"] && $rswechat["Users_WechatAppSecret"] && $rswechat["Users_WechatType"]==3){
	
	$service = 1;
	$u = new userinfo();
	$u->appid = $rswechat["Users_WechatAppId"];
	$u->appsecret = $rswechat["Users_WechatAppSecret"];
	$u->DB = $DB;
	$u->UsersID = $UsersID;
	
}else{
	$service = 0;
}
if(!isset($is_login)){
	$is_login = 0;
}

if(!isset($is_address)){
	$is_address = 0;
}

if(!empty($_SESSION[$UsersID."User_ID"])){
	$userexit = $DB->GetRs("user","User_ID","where User_ID=".$_SESSION[$UsersID."User_ID"]." and Users_ID='".$UsersID."'");
	if(!$userexit){
		$_SESSION[$UsersID."User_ID"] = "";
	}
}

$codebyurl = "";
$ownerid = isset($owner['id']) ? $owner['id'] : 0;
$rootid =isset($owner['Root_ID']) ? $owner['Root_ID'] : 0;
if(isset($ownerid)){
	$login_url = "/api/".$UsersID."/user/".$ownerid."/login/";
}else{
	$login_url = "/api/".$UsersID."/user/login/";
}
if(empty($_SESSION[$UsersID."User_ID"])){
	$join_share = 1;
}else{
	$join_share = 0;
}



if(empty($_SESSION[$UsersID."User_ID"]) || !isset($_SESSION[$UsersID."User_ID"])){
	if(strpos($_SERVER['REQUEST_URI'],"state=") !== false){//微信通信回来
		if(strpos($_SERVER['REQUEST_URI'],"code=") !== false){//同意授权获取code
			$arr_temp = explode("code=",$_SERVER['REQUEST_URI']);
			if(strpos($arr_temp[1],"?") != false){
				$code_arr = explode("?",$arr_temp[1]);
				$codebyurl = $code_arr[0];
			}elseif(strpos($arr_temp[1],"&") != false){
				$code_arr = explode("&",$arr_temp[1]);
				$codebyurl = $code_arr[0];
			}else{
				$codebyurl = $arr_temp[1];
			}

			$u->getOpenid($codebyurl);
			$login_flag = $u->loginbyopenid();
			if(!empty($_SESSION[$UsersID."OpenID"])){
				if(!$login_flag){
					$dis_config_limit_result = $DB->GetRs('distribute_config','Distribute_Limit','where Users_ID="'.$UsersID.'"');
					if($dis_config_limit_result['Distribute_Limit'] == 1 && $ownerid==0){
						echo '必须通过邀请人才能成为会员';
						exit;
					}
					 $register_flag = $u->registerbyopenid($ownerid,$rootid,$codebyurl);
					// if(!$register_flag && $is_login==1){
						// header("Location:$login_url");
					// }
				}
			}
		}else{//用户不同意授权
			$dis_config_limit_result = $DB->GetRs('distribute_config','Distribute_Limit','where Users_ID="'.$UsersID.'"');
			if($dis_config_limit_result['Distribute_Limit'] == 1 && $ownerid==0){
				echo '必须通过邀请人才能成为会员';
				exit;
			}
			// if($is_login==1){
				// header("Location:$login_url");
			// }
		}	
	}else{//网站和微信通信开始
	
		if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){//weixin client
			if($service==1){
				$url = $u->createOauthUrlForCodeweb(urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']));
				Header("Location: $url");
			}else{
				// if($is_login==1){
					// header("Location:$login_url");
				// }
			}
		}else{
			// if($is_login==1){
				// header("Location:$login_url");
			// }
		}
	}
}
if($is_address && !empty($_SESSION[$UsersID."access_token_web"]) && $service==1){
	$editAddress = $u->GetEditAddressParameters();
}

//结束 wechatuser.php 文件

$owner = get_owner($rsConfig,$UsersID);

//分销级别处理文件
include($_SERVER["DOCUMENT_ROOT"].'/api/distribute/distribute.php');

if($distribute_flag){
	header("location:/api/" . $UsersID . "/distribute/?love");
	exit;
}
$arr_level_name = array('一','二','三','四','五','六','七','八','九','十');
$JSON = json_decode($rsConfig['Distribute_Form'],true);
//分销商几个级别
$dis_level_num = count($dis_level);

// 手动申请
$dis_level_clone = $dis_level;
/* 判断手动申请
 * Reserve_DisplayName          //是否需要填写申请人的姓名
 * Reserve_DisplayTelephone     //是否需要填写申请人的手机号
 * Distribute_Form              //手动申请商家添加的额外提交信息
 * Level_Isauditing             //手动申请是否需要审核
 */
$low_level = array_shift($dis_level_clone);   //获取低级分销商数据
$low_Distribute_Form_JSON = !empty($low_level['Distribute_Form']) ? json_decode($low_level['Distribute_Form'], true) : [];  //解析商家添加的表单
if ($low_level['Level_LimitType'] == 4) {     //手动申请  Level_LimitType：1. 消费额  2.购买商品  3.无门槛  4.手动申请

    //查询是否有手动申请提交记录
    $manualorder = $DB->GetRs("dismanual_order", "Applyfor_Name, Applyfor_Mobile, Applyfor_form, Order_Status, Refuse_Be", "where Users_ID = '" . $UsersID . "' and User_ID = " . (empty($_SESSION[$UsersID."User_ID"]) ? 0 : $_SESSION[$UsersID."User_ID"]) );
    if ($manualorder) {
        switch ($manualorder['Order_Status']) {
            case 0: $apply_btn_name = '等待审核中...'; break;
            case 1: $apply_btn_name = '已审核'; break;
            case 2: $apply_btn_name = '驳回，请重新提交'; break;
            default: $apply_btn_name = '立即加入'; break;
        }
        $manual_form = !empty($manualorder['Applyfor_form']) ? json_decode($manualorder['Applyfor_form'], true) : [];
        $Refuse_Bearr = !empty($manualorder['Refuse_Be']) ? json_decode($manualorder['Refuse_Be'], true) : '';
        if (is_array($Refuse_Bearr)) {
            $showrefuse = array_pop($Refuse_Bearr);
        } else {
            $showrefuse = $Refuse_Bearr;
        }
    } else {
        $apply_btn_name = '立即加入';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta content="telephone=no" name="format-detection" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>成为分销商</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/api/distribute/css/style.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href='/static/api/distribute/css/apply.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href="/static/api/shop/skin/default/css/tao_detail.css?t=<?php echo time();?>" rel="stylesheet" type="text/css" />
<link href="/static/api/css/fx_index.css?t=<?php echo time();?>" rel="stylesheet" type="text/css" />
<link href='/static/api/shop/skin/default/css/cart.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/api/js/global.js?t=<?php echo time();?>'></script>
<script type='text/javascript' src='/static/api/shop/js/product_attr_helper.js?t=<?=time();?>'></script>
<script type='text/javascript' src='/static/api/js/apply.js?t=<?php echo time();?>'></script>
<script type='text/javascript' src='/static/js/plugin/layer_mobile/layer.js'></script>
<script language="javascript">
var UsersID = "<?=$UsersID?>";
var ajax_url  = "<?=distribute_url()?>ajax/";
var styles = '<?=$styles?>';

$(document).ready(function(){
  apply_obj.apply_init();
   styles = eval('(' + styles + ')');
   if(styles !=''){
	  	if(styles['s_text'] != ''){
		  $('.tx_a').text(styles['s_text']);
		}else{
	      $('.tx_a').text('');
	    }
		if(styles['b_text'] != ''){
		  $('.an a').text(styles['b_text']);
		}else{
	      $('.an a').text('立即加入');
	    }
	    if(styles['bf_color'] != ''){
	      $('.an a').css('color','#'+styles['bf_color']);
	    }else{
	      $('.an a').css('color','#FFFFFF');
	    }
	    if(styles['sf_color'] != ''){
	      $('.tx_a').css('color','#'+styles['sf_color']);
	      $('.tx_b').css('color','#'+styles['sf_color']);
	    }else{
	      $('.tx_a').css('color','#FFFFFF');
	      $('.tx_b').css('color','#FFFFFF');
	    }
		if(styles['b_color'] != ''){
		  $('.an').css('background-color', '#'+styles['b_color']);
		}else{
	      $('.an').css('background-color','#e30c0c');
	    }
		if(styles['s_color'] != ''){
		  $('.fotter').css('background-color','#'+styles['s_color']);
		}else{
	      $('.fotter').css('background-color','#e30c0c');
	    }
		if(styles['s_height'] != '' && styles['s_height'] > 0){
			
		  $('.fotter').css('height',styles['s_height']);
		 
		  /*
		  $('.foot').css('height',styles['s_height']);
		  $('.foot .tx_b').css('height',styles['s_height']);
		  $('.foot .tx_b').css('line-height',styles['s_height']);
		  $('.foot .tx_a').css('height',styles['s_height']);
		  $('.foot .tx_a').css('line-height',styles['s_height']);
		  */
		}else{
	      	$('.fotter').css('height','50');
	       /*	$('.foot').css('height','50');*/
	    }
		if(styles['clarity'] !='' && styles['clarity'] > 0){
		  var clarity = styles['clarity']/100;
		  $('.fotter').animate({"opacity":clarity},0);
		}
		if(styles['place'] == 1){
		    $('.foot').css('top','0px');
		    $('.fotter').css('top','0px');
		}else if(styles['place'] == 2){
			$('.foot').css('bottom','0px');
		    $('.fotter').css('bottom','0px');
		}else{
			$('.foot').css('bottom','0px');
	   		$('.fotter').css('bottom','0px');
		}
   }
});
</script>
</head>
<body>
<div class="w">
      <div class="tp_x"><img src='<?php echo !empty($rsConfig['ApplyBanner']) ? $rsConfig['ApplyBanner'] : '/static/api/shop/skin/default/images/001_01.png'; ?>' /></div>
      <div class="clear"></div>

    <?php if(!empty($low_level) && $low_level['Level_LimitType'] == 4){//手动申请?>
        <link href='/static/api/distribute/css/menkan.css?t=1' rel='stylesheet' type='text/css'/>
        <p class="title">立即申请</p>
        <form id="apply_manual" onsubmit="return false;">
            <input type="hidden" name="action" value="manual_tj"/>
            <div class="tequan">
                <?php if (!empty($low_level["Reserve_DisplayName"])) { ?>
                    <input type="text" name="Name" maxlength="20" value="<?= !empty($manualorder['Applyfor_Name']) ? $manualorder['Applyfor_Name'] : '' ?>" placeholder="请输入您的姓名" notnull>
                <?php } ?>
                <?php if (!empty($low_level["Reserve_DisplayTelephone"])) { ?>
                    <input type="tel" name="Mobile" maxlength="11" value="<?= !empty($manualorder['Applyfor_Mobile']) ? $manualorder['Applyfor_Mobile'] : '' ?>" placeholder="请输入您的手机号" id="Mobile" notnull>
                <?php } ?>
                <?php if (!empty($low_Distribute_Form_JSON)) {
                    foreach ($low_Distribute_Form_JSON as $key => $value) {
                        if ($value["Type"] == 'text') {
                            echo '<input type="text" name="JSON[' . $key . '][' . $value['Name'] . ']" maxlength="50" value="' . (!empty($manual_form[$key][$value['Name']]) ? $manual_form[$key][$value['Name']] : '') . '" placeholder="' . $value["Value"] . '" notnull >';

                        } elseif ($value["Type"] == 'select') {
                            echo '<select name="JSON[' . $key . '][' . $value['Name'] . ']" ' . (isset($manualorder['Order_Status']) && in_array($manualorder['Order_Status'], [0, 1]) ? 'disabled' : '') . '>';
                            foreach (explode('|', $value['Value']) as $k => $v) {
                                echo '<option value="' . $v . '" ' . (!empty($manual_form[$key][$value['Name']]) && $manual_form[$key][$value['Name']] == $v ? 'selected' : '') . '>' . $v . '</option>';
                            }
                            echo '</select>';
                        }
                    }
                } ?>
            </div>
            <div class="menkan">
                <button id="submit_btn_bottom_apply" <?= !empty($rsConfig['Button_Color']) ? 'style="background:#' . $rsConfig['Button_Color'] . ';"' : '' ?> <?= !empty($manualorder) && $manualorder['Order_Status'] != 2 ? 'status="1"' : '' ?>><?= !empty($manualorder) && !empty($apply_btn_name) ? $apply_btn_name : (!empty($rsConfig['Button_Name']) ? $rsConfig['Button_Name'] : '立即加入') ?></button>
                <?= !empty($manualorder) && $manualorder['Order_Status'] == 2 ? '<p>驳回理由：' . (empty($showrefuse) ? '' : $showrefuse) . '</p>' : '' ?>
            </div>
        </form>
    <?php } else { ?>

      <?php if($rsConfig['Distribute_Type'] == 0){//直接购买?>

	  <form id="apply_buy" class="distribute_type_0">
			<div id="option_content">
				<div class="level_top"><div class="left"></div><div class="mid">请选择</div><div id="close-btn" class="cjlose thi"></div>
				  </div>
						<div class="level_list">
							<ul id="option_selecter">
								<?php
								$i=0;
								$first_price = 0;
								$first_levelid = 0;
								$n = 0;								
								foreach($dis_level as $id=>$value){
									$n++;
									if($value['Level_LimitType'] <> 0){
										$value['Level_LimitValue'] = 0;
									}else{
										$value['Level_LimitValue'] = number_format($value['Level_LimitValue'],2,'.','');
									}
								?>
									<?php
									if(!empty($value['Level_LimitValue'])){
										$i++;
										if($i==1){
											$first_price = $value['Level_LimitValue'];
											$first_levelid = $value['Level_ID'];
										}
									?>
										<li class="submit_btn" lid="<?php echo $value['Level_ID'];?>"><img src="<?php echo $value['Level_ImgPath'];?>"><span class="top"><?php echo $value['Level_Name'];?></span><?php 
										if(!empty($value['Level_Description'])){
											?>
											<br><span class="mid">
											<?php
											$vsshow_name = $value['Level_Description'];
											$vsshow_name_num =  mb_strlen($vsshow_name);
											if($vsshow_name_num >20){
												echo $vsshow_name = mb_substr($vsshow_name,0,20,'utf-8').'......';
											}else{
												echo $vsshow_name;
											}
											?></span>
										<?php } ?><br><span class="bom">&yen;<?php echo $value['Level_LimitValue'];?>&nbsp;<?php
												if(!empty($value['Present_type']) && $value['Present_type'] == 2){?>
													送<?php echo $value['Level_PresentValue'];?>元
												<?php } ?>
										 </span></li>
									<?php }
									if($n != $dis_level_num){?>
									<div align="center"><div class="hx1"></div></div>
									<?php } }?>						
							</ul>
						</div>
			</div>
			
			<input type="hidden" name="action" value="deal_level_order" />
			<input name="LevelID" type="hidden" id="LevelID" value ="787" />
			<?php if($first_price>0){?>
				<?php $rsPay = $DB->GetRs("users_payconfig","*","where Users_ID='".$UsersID."'");
					// echo '<pre>';
					// print_R($rsPay);
					// exit;
				
				?>
				<div class="pay_select_bg"></div>
				<div class="pay_select_list" id="">
					<div id="payment">
						<div class="i-ture">
							<h1 class="t">订单提交成功！</h1>
							<div class="info"> <span class="Order_info"  style="font-size: 14px;">订 单 号111：</span><br />
								订单总价：<span class="fc_red" id="Order_TotalPrice" style="font-size: 14px;">￥0<br/>
								</span> 
							</div>
						</div>
						<div class="i-ture">
							<h1 class="t">选择支付方式</h1>
							<ul id="pay-btn-panel">
								<?php if(!empty($rsPay["PaymentWxpayEnabled"])){ ?>
								<li> <a href="javascript:void(0)" UsersID="<?=$UsersID?>" join_share="<?=$join_share?>" class="btn btn-default btn-pay direct_pay" id="wzf" data-value="微支付"><img  src="/static/api/shop/skin/default/wechat_logo.jpg" width="16px" height="16px"/>&nbsp;微信支付</a> </li>
								<?php }?>
								<?php if(!empty($rsPay["Payment_AlipayEnabled"])){?>
								<li> <a href="javascript:void(0)" UsersID="<?=$UsersID?>" join_share="<?=$join_share?>" class="btn btn-danger btn-pay direct_pay" id="zfb" data-value="支付宝"><img  src="/static/api/shop/skin/default/alipay.png" width="16px" height="16px"/>&nbsp;支付宝支付</a> </li>
								<?php if(!empty($rsPay["PaymentYeepayEnabled"])){?>
								<li style="display:none;"> <a href="javascript:void(0)" UsersID="<?=$UsersID?>" join_share="<?=$join_share?>" class="btn btn-danger btn-pay direct_pay" id="ybzf" data-value="易宝支付"><img  src="/static/api/shop/skin/default/yeepay.png" width="16px" height="16px"/>&nbsp;易宝支付</a> </li>
								<?php }?>
								<?php }
								if(!empty($rsPay["Payment_UnionpayEnabled"])){ ?>
								<li>
									 <a href="javascript:void(0)" UsersID="<?=$UsersID?>" join_share="<?=$join_share?>" class="btn btn-danger btn-pay direct_pay" id="uni" data-value="银联支付"><img  src="/static/api/shop/skin/default/Unionpay.png" width="16px" height="16px"/>&nbsp;银联支付</a>
								</li>
						<?php }
							   if(!empty($rsPay["Payment_OfflineEnabled"])){?>
								<li> <a  class="btn btn-warning  btn-pay" UsersID="<?=$UsersID?>" join_share="<?=$join_share?>" id="out" data-value="线下支付"><img  src="/static/api/shop/skin/default/huodao.png" width="16px" height="16px"/>&nbsp;线下支付</a> </li>
								<?php }?>
								<?php if(!empty($rsPay["Payment_RmainderEnabled"])){?>
								<li> <a  class="btn btn-warning  btn-pay money_yue" UsersID="<?=$UsersID?>" join_share="<?=$join_share?>" id="money" data-value="余额支付"><img  src="/static/api/shop/skin/default/money.jpg"  width="16px" height="16px"/>&nbsp;余额支付</a> </li>
								<?php }?>
							</ul>
						</div>
					</div>
				</div>
			<?php }?>
			<?php if($rsConfig['Level_Form'] == 1){?>
				<div class="box1_x" style="padding-top: 10px;"  >
					 <?php if(!empty($rsConfig["Reserve_DisplayName"])){ ?>
							<span class="l xmm_x">姓名：</span>
							<span class="l xmm1_x"><input name="Name" value="" class="xmm2_x" placeholder="请输入您的姓名" type="text" notnull></span>
					  <?php }?>
					  <?php if(!empty($rsConfig["Reserve_DisplayTelephone"])){ ?>
							<span class="l xmm_x">电话：</span>
							<span class="l xmm1_x"><input name="Mobile" id="Mobile" value="" class="xmm2_x" placeholder="请输入您的电话号码" type="text" notnull></span>
					  <?php }?>
					  <?php 
						if(!empty($JSON)){
							foreach($JSON AS $key=>$value){
								echo '<span class="l xmm_x">'.$value["Name"].'：</span>';
								if($value["Type"]=="text"){
									echo '<span class="l xmm1_x"><input type="text" class=" xmm2_x" name="JSON['.$key.']['.$value["Name"].']" value="" class="form_input" placeholder="'.$value["Value"].'" notnull /></span>';
								}elseif($value["Type"]=="select"){
									echo '<span class="l xmm1_x"><select class="xmm3_x" name="JSON['.$key.']['.$value["Name"].']">';
									foreach(explode("|",$value["Value"]) as $k=>$v){
										echo '<option value="'.$v.'">'.$v.'</option>';
									}
									echo '</select></span>';
								}
							}
						} ?>
				</div>
			<?php } ?>
			<input type="hidden" name="agree" value="<?php echo $rsConfig["Distribute_AgreementOpen"];?>" />			
			<?php if($rsConfig["Distribute_AgreementOpen"]==1){?>
				<div class="agreement" style="clear:both;"><input type="checkbox" name="agreement" value="1" checked="true"> 同意<a href="<?php echo distribute_url('agreement/');?>" style="color:#4F93CE">《<?php echo htmlspecialchars_decode($rsConfig["Distribute_AgreementTitle"],ENT_QUOTES);?>》</a></div>
			<?php }?>
			<div class="box1_x">
				<div id="anchor" ></div>
				<input type="hidden" value='<?php echo !empty($styles) ? $styles : '';?>' name="styles" />
				<input type="hidden" value='<?php echo '1';?>' name="flag" />
				<?php if($dis_level_num> 1 && $rsConfig['Distribute_UpgradeWay'] == 1){?>
					<a href="javascript:void(0)" class="dl_x submit_btn123" style="background-color:<?php echo !empty($rsConfig['Button_Color'])? '#'.$rsConfig['Button_Color'] : '#e30c0c'; ?>" id="menu-addtocard-btn"><?php echo !empty($rsConfig['Button_Name'])? $rsConfig['Button_Name'] : '立即购买成为分销商';?></a>
				<?php }else{?>
					<a href="javascript:void(0)" class="dl_x submit_btn123" id="submit_btn_bottom" style="background-color:<?php echo !empty($rsConfig['Button_Color'])? '#'.$rsConfig['Button_Color'] : '#e30c0c'; ?>" ><?php echo !empty($rsConfig['Button_Name'])? $rsConfig['Button_Name'] : '立即购买成为分销商';?></a>
				<?php } ?>
			</div>
		</form>
		<div class="clear"></div>
      <div class="mask"></div>
      <div class="jl_x"></div>
      <div class="fotter"> </div>
      <div class="foot">
        <span class="l tx_x"><img src="<?php echo !empty($owner['shop_logo']) ? $owner['shop_logo'] : '/static/api/shop/skin/default/images/001_01.png'; ?>" width="40px" height="40px"></span> 
        <span class="l tx_b"><?php echo !empty($owner['shop_name']) ? $owner['shop_name'] : '';?></span>  
        <span class="l tx_a" >成为分销商</span> 
        <span class="r an"><a href="#anchor" class='topLink '>立即加入</a></span> 
    </div>
      <?php }elseif($rsConfig['Distribute_Type']==1){//消费额 ?>
	  <?php
		foreach($dis_level as $id=>$value){
			$PeopleLimit = json_decode($value['Level_PeopleLimit'],true);
			if (!strpos($value['Level_LimitValue'],'|')) {
				echo "<span class='red'>&nbsp;&nbsp;&nbsp;分销门槛设置出错，请联系管理员！</span>";exit;
			}
			$arr_limit = explode('|',$value['Level_LimitValue']);
			if($value["Level_LimitType"] == 3){
				$limit = '无条件';
			} elseif ($value["Level_LimitType"] == 4) {
                $limit = '手动申请';
            } else{
				$limit = $arr_limit[0]==0 ? '商城总消费额需满'.$arr_limit[1].'元' : '需一次性消费'.$arr_limit[1].'元';
			}
		?>
  
		<table cellpadding="0" cellspacing="0" class="distribute_type_1">	
			<tr>
				<th colspan="2"><?php echo $value['Level_Name'];?></th>
			</tr>
			<tr>
				<td class="td_1">条件</td>
				<td class="td_2">
				<?php echo $limit;?>
				</td>
			</tr>
			<tr>
				<td class="td_1">权限</td>
				<td class="td_2">
					<?php
						foreach($PeopleLimit as $k=>$v){
							if($k>1){
								echo '<br />';
							}
							echo $arr_level_name[$k-1].'级&nbsp;&nbsp;';
							if($v==0){
								echo '无限制';
							}elseif($v==-1){
								echo '禁止';
							}else{
								echo $v.'&nbsp;个';
							}
						}
					?>
				</td>
			</tr>    
			<tr>
			 <td class="td_3" colspan="2">
				<?php if($value["Level_LimitType"]==3){?>
				<a href="<?php echo distribute_url();?>">进入分销中心</a>
				<?php }else{?>
				<a href="<?php echo $shop_url;?>">立即消费</a>
				<?php }?>
			 </td>
			</tr>
		</table>
		<?php }?>
      <?php }elseif($rsConfig['Distribute_Type']==2){//购买商品 ?>
    <?php
  if($rsConfig['Distribute_UpgradeWay']==0){
    $dis_levels = array();
        foreach($dis_level as $id=>$value){
          $dis_levels[0] = $value;
          break;
        }
      $dis_level = $dis_levels;
  }
   ?>
  <table cellpadding="0" cellspacing="0" class="distribute_type_1">
  <?php
      foreach($dis_level as $id=>$value){
      $PeopleLimit = json_decode($value['Level_PeopleLimit'],true);
      $limit_arr = explode('|',$value['Level_LimitValue']);
      $jibie = '';
      $limit = '';
      if($value['Level_LimitType'] == 3){
        $jibie = '无条件';
        $limit = '无条件';
      }else{
        if($limit_arr[0]==0){//任意商品
          $jibie = '购买任意商品';
          $limit = '购买任意商品';
        }elseif($limit_arr[0]==1){
          $jibie = "购买以下任一商品";
          $limit = '';
          if(!empty($limit_arr[1])){
            $obj = $DB->Get('shop_products','*','where Users_ID = "'.$UsersID.'" and Products_ID in ('.$limit_arr[1].')');
            while($r = $DB->toArray($obj)){
              
              foreach($r as $k1=>$v1){
                $link = $shop_urldelwzw.'products/'.$v1['Products_ID'].'/';
                $ImgPath = !empty($v1['Products_JSON'])?json_decode($v1['Products_JSON'],true):'';
                $limit .= "<ul><li><a href='".$link."'";
                $limit .= "<div class='item'>";
                $a = '';
                if(!empty($ImgPath['ImgPath'])){
                  $a = "<img data-url='".$ImgPath['ImgPath'][0]."' src='".$ImgPath['ImgPath'][0]."'>";
                }else{
                  $a = '暂无图片';
                }
                $limit .="<div class='img'>".$a."</div>";
                $limit .="<div class='info'>";
                $limit .="<h1>".$v1['Products_Name']."</h1>";
                $limit .="<h2>￥".$v1['Products_PriceX']."</h2>";
                $limit .="<h3>".$v1['Products_BriefDescription']."</h3>";
                $limit .="</div>";
                $limit .="<div class='detail'><span></span></div>";
                $limit .="</div></a></li></ul>";
              }
              
            }
          }
        }elseif($limit_arr[0]==2){
          $jibie = '购买单一商品';
          $limit = '';
				 	if(!empty($limit_arr[1])){
				 		$DB->Get('shop_products','*','where Users_ID = "'.$UsersID.'" and Products_ID = "'.$limit_arr[1].'"');
				 		while($r = $DB->fetch_assoc()){
				 			$link = $shop_url.'products/'.$r['Products_ID'].'/';
					 		$ImgPath = !empty($r['Products_JSON'])?json_decode($r['Products_JSON'],true):'';
					 		$limit .= "<ul><li><a href='".$link."'";
							$limit .= "<div class='item'>";
							$a = '';
							if(!empty($ImgPath['ImgPath'])){
								$a = "<img data-url='".$ImgPath['ImgPath'][0]."' src='".$ImgPath['ImgPath'][0]."'>";
							}else{
								$a = '暂无图片';
							}
							$limit .="<div class='img'>".$a."</div>";
							$limit .="<div class='info'>";
							$limit .="<h1>".$r['Products_Name']."</h1>";
							$limit .="<h2>￥".$r['Products_PriceX']."</h2>";
							$limit .="<h3>".$r['Products_BriefDescription']."</h3>";
							$limit .="</div>";
							$limit .="<div class='detail'><span></span></div>";
							$limit .="</div></a></li></ul>";
				 		} 
				 	}
				} 
		    }
	?>

		<tr>
			<th colspan="2"><span style="font-size:16px; font-weight:bold;"><?php echo $value['Level_Name'];?></span><span style="font-size:14px; color:#e30b0c; margin-left:5px;">(<?php echo $jibie;?>)</span></th>
		</tr>
		<tr>
			<td td_2><?php echo $limit;?></td>
		</tr>
    <tr>
     <td class="td_3" colspan="2">
      <?php if($value["Level_LimitType"]==3){?>
        <a href="<?php echo distribute_url();?>">进入分销中心</a>
        <?php }elseif($limit_arr[0]==0){?>
      <a href="<?php echo $shop_url;?>">进入商城</a>
        <?php }?>
      </td>
    </tr>
		
	<?php } ?>
	</table>
	  <?php } ?>
	  <?php } ?>
</div>
<?php require_once '../shop/skin/distribute_footer.php';?>
</body>
</html>
<script>
	$(".money_yue").click(function(){
			var join_share = $(this).attr('join_share');
			var Order_ID = $('.pay_select_list').attr("id");
			if(join_share == 1){
				  /*window.location.href="/api/"+UsersID+"/user/0/create/"+Order_ID+"/";*/
				  window.location.href="/api/"+UsersID+"/user/login/"+Order_ID+"/";
			}else{
				window.location.href= "/api/<?=$UsersID?>/distribute/complete_pay/money/"+Order_ID+"/";
			}
			
	});
</script>