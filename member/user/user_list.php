<?php
require_once (CMS_ROOT . '/include/helper/tools.php');
use Illuminate\Database\Capsule\Manager as DB;

$UsersID = isset($_SESSION["Users_ID"]) && $_SESSION["Users_ID"] ? $_SESSION["Users_ID"] : 0;
if(!$UsersID)
{
	header("location:/member/login.php");
}
$rsConfigs = shop_config($UsersID);
$rsConfigs = array_merge($rsConfigs, dis_config($UsersID));
$rsConfig = shop_user_config($UsersID);

$_GET = daddslashes($_GET,1);

if(empty($rsConfig)){
	header("location:config.php");
}else{
	if(empty($rsConfig['UserLevel'])){
		$UserLevel[0]=array(
			"Name"=>"普通会员",
			"UpIntegral"=>0,
			"ImgPath"=>""
		);
		$Data=array(
			"UserLevel"=>json_encode($UserLevel,JSON_UNESCAPED_UNICODE)
		);
		$DB->Set("user_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	}else{
		$UserLevel=json_decode($rsConfig['UserLevel'],true);
		
	}
}


$condition = "where Users_ID='".$_SESSION["Users_ID"]."'";
if(isset($_GET["search"]) && $_GET["search"]==1){
    if(isset($_GET["Keyword"]) && $_GET["Keyword"]){
        $condition .= " and " .(isset($_GET["Fields"]) && in_array($_GET["Fields"], ['User_No','User_Name','User_NickName','User_Mobile']) ? $_GET["Fields"] : "User_No") . " like '%".$_GET["Keyword"]."%'";
    }
    if(isset($_GET["UserFrom"]) && $_GET["UserFrom"] != '-1'){
        $condition .= " and User_From = ". intval($_GET["UserFrom"]);
    }
    if(isset($_GET["MemberLevel"]) && $_GET["MemberLevel"] != "-1"){
        $condition .= " and User_Level=".intval($_GET["MemberLevel"]);
    }
}
$condition .= " order by User_CreateTime desc";
$action=empty($_REQUEST['action'])?'':$_REQUEST['action'];
if(isset($action))
{	
	if($action=="mod_password")
	{
		$Data=array(
			"User_Password"=>md5(123456)
		);
		$Set=$DB->Set("user",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$_POST["UserID"]);
		if($Set){
			$Data=array("status"=>1);
		}else{
			$Data=array("status"=>0);
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($action=="mod_payword")
	{
		$Data=array(
			"User_PayPassword"=>md5(123456)
		);
		$Set=$DB->Set("user",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$_POST["UserID"]);
		if($Set){
			$Data=array("status"=>1);
		}else{
			$Data=array("status"=>0);
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($action=="user_mod")
	{
		$FieldName=array("User_No","User_Name","User_Mobile","User_Integral","","User_Level");
		if ($_POST['field'] == 2) {
			$rsUsermobile = $DB->GetRs("user", "User_Mobile", "WHERE User_Mobile=" . $_POST['Value'] . " and Users_ID='".$_SESSION["Users_ID"]."'");
			if ($rsUsermobile) {
				$Data=array("status"=>0,"msg"=>"手机号已绑定请换个手机号");
				echo json_encode($Data,JSON_UNESCAPED_UNICODE);
				exit;
			}
		}
		$Data=array(		
			$FieldName[$_POST['field']]=>$_POST['Value']
		);
		$Set=$DB->Set("user",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$_POST["UserID"]);
		if($Set){
			$Data=array("status"=>1);
		}else{
			$Data=array("status"=>0,"msg"=>"写入数据库失败");
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	if($action=="integral_mod")
	{
		$rsUser=$DB->GetRs("user","User_Integral,User_TotalIntegral,User_Level","where Users_ID='".$_SESSION["Users_ID"]."' and User_ID='".$_POST["UserID"]."'");
		$levelID=$rsUser["User_Level"];
		$levelName=$UserLevel[$rsUser["User_Level"]]["Name"];
		if($rsUser['User_Integral']+$_POST['Value']<0){
			$Data=array("status"=>2);
		}else{
			//增加
			$Data=array(
				'Record_Integral'=>$_POST['Value'],
				'Record_SurplusIntegral'=>$rsUser['User_Integral'] + $_POST['Value'],
				'Operator_UserName'=>$_SESSION["Users_Account"],
				'Record_Type'=>1,
				'Record_Description'=>"手动修改积分",
				'Record_CreateTime'=>time(),
				'Users_ID'=>$_SESSION['Users_ID'],
				'User_ID'=>$_POST["UserID"]
			);
			$Add=$DB->Add('user_Integral_record',$Data);
			if($_POST['Value']>0){
				foreach($UserLevel as $k=>$v){
					if(!empty($v['UpIntegral'])){
					 if($rsUser['User_TotalIntegral']+$_POST['Value']>=$v['UpIntegral']){
						 $levelID=$k;
						 $levelName=$v['Name'];
					 }
					}
				}
				$Data=array(
					"User_Level"=>$levelID,
					"User_Integral"=>$rsUser['User_Integral']+$_POST['Value'],
					"User_TotalIntegral"=>$rsUser['User_TotalIntegral']+$_POST['Value']
				);
			}else{
				$levelName=$UserLevel[$rsUser["User_Level"]]["Name"];
				$Data=array(
					"User_Integral"=>$rsUser['User_Integral']+$_POST['Value']
				);
			}
			$Set=$DB->Set("user",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$_POST["UserID"]);
			if($Set){
				$Data=array("status"=>1,"lvl"=>1,"level"=>$levelName);
				
				require_once($_SERVER["DOCUMENT_ROOT"].'/include/library/weixin_message.class.php');
				$weixin_message = new weixin_message($DB,$_SESSION["Users_ID"],$_POST["UserID"]);
				$contentStr = $_POST['Value']>0 ? "管理员手动增加".$_POST['Value']."积分" : "管理员手动减少".$_POST['Value']."积分";
				$weixin_message->sendscorenotice($contentStr);
				
			}else{
				$Data=array("status"=>0,"msg"=>"写入数据库失败");
			}
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if($action=="money_mod")
	{
		$rsUser=$DB->GetRs("user","User_Money","where Users_ID='".$_SESSION["Users_ID"]."' and User_ID='".$_POST["UserID"]."'");
		if($rsUser['User_Money']+$_POST['Value']<0){
			$Data=array("status"=>2);
		}else{
			//增加充值记录
			if($_POST['Value']>0){
				$Data=array(
					'Users_ID'=>$_SESSION['Users_ID'],
					'User_ID'=>$_POST["UserID"],
					'Amount'=>$_POST['Value'],
					'Total'=>$rsUser['User_Money']+$_POST['Value'],
					'Operator'=>$_SESSION["Users_Account"]." 线下充值 +".$_POST['Value'],
					'Status'=>1,
					'CreateTime'=>time()
				);
				$Add=$DB->Add('user_charge',$Data);
			}
			//增加资金流水
			$Data=array(
				'Users_ID'=>$_SESSION['Users_ID'],
				'User_ID'=>$_POST["UserID"],				
				'Type'=>$_POST['Value']>0 ? 1 : 0,
				'Amount'=>$_POST['Value'],
				'Total'=>$rsUser['User_Money']+$_POST['Value'],
				'Note'=>$_SESSION["Users_Account"].($_POST['Value']>0 ? " 线下充值 +".$_POST['Value'] : " 线下减余额 ".$_POST['Value']),
				'CreateTime'=>time()			
			);
			$Add=$DB->Add('user_money_record',$Data);
			//更新用户余额
			$Data=array(				
				'User_Money'=>$rsUser['User_Money']+$_POST['Value']					
			);
			$Set=$DB->Set("user",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$_POST["UserID"]);
			if($Set){
				$Data=array("status"=>1);
			}else{
				$Data=array("status"=>0,"msg"=>"写入数据库失败");
			}
		}
		echo json_encode($Data,JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	if ($action == 'deleteUser') {
        $UserID = I('UserID');
        $c = clearUser($UsersID, [$UserID]);
        if($c){
            layerMsg("删除会员成功！", '/member/user/user_list.php');
        }else{
            layerMsg("删除会员失败！");
        }
    }
    if($action == 'deleteAll'){
        $UserID = I('UserID');
        $c = clearUser($UsersID);
        if($c){
            layerMsg("清除成功！", '/member/user/user_list.php');
        }else{
            layerMsg("清除失败！");
        }
    }
	
	if($action == 'deleteAllreal'){
        $password = md5(trim(I('password')));
        $islogin = DB::table('users')->where(['Users_ID' => $UsersID, 'Users_Password' => $password])->first();
        $status = 0;
        if($islogin){
            $status = 1;
        }
        die(json_encode([ 'status' => $status, 'msg' => '' ] , JSON_UNESCAPED_UNICODE));
    }
}
$From_arr = ['无','注册','PC', 'QQ','微信'];
$jcurid = 2;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css?t=1' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<style type="text/css">
body, html{background:url(/static/member/images/main/main-bg.jpg) left top fixed no-repeat;}
</style>
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/user.css?t=3' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/user.js?t=<?=time()?>'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/user/user_menubar.php');?>
    <link href='/static/js/plugin/lean-modal/style.css?t=2' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script> 
	<style>
        .clearAll {
            background: #1584D5;
            color: white;
            border: none;
            height: 22px;
            line-height: 22px;
            width: 80px;
        }
        .inputPwd input{
            width: 90%;
            height: 30px;
            /* border: none; */
            border: 1px solid #bdb6b6;
            margin-left: 10px;
            margin-top: 10px;
        }
        #password { display: none; }
    </style>
    <script language="javascript">
	var level_ary=Array();
	<?php foreach($UserLevel as $k=>$v){
		 echo 'level_ary['.$k.']="'.$v['Name'].'";';
	}?>
	$(document).ready(function(){user_obj.user_init();});
	function deleteUser(user){
		if(user){
			var url= '?action=deleteUser&UserID=' + user;
			if(user==-1){
				url= '?action=deleteAll';
				layer.open({
					type: 1,
					title: "登陆密码",
					closeBtn: 0,
					shadeClose: true,
					area:['300px','150px'],
					btn:['确定'],
					content: '<div class="inputPwd">' +
					'<input type="password" name="password" value="" placeholder="请输入登录密码" /></div>',
					yes: function(){
						var password = $.trim($("input[name='password']").val());
						if(password=="" || password==null){
							layer.alert("请输入登录密码");
							return false;
						}
						$.post("?",{ password:password,action:'deleteAllreal' }, function(data){
							if(data.status == 1){
								layer.confirm('本操作将清空与会员相关的一切数据（会员管理  订单管理  分销记录  分销账号管理  代理信息 股东信息  创始人信息 结算信息等）请慎操！<br>确定清空全部会员？', {
									btn: ['确定','取消'], //按钮
									shade: false //不显示遮罩
								}, function(){
									location.href = url;
								});
							}else{
								layer.alert("登录密码错误！");
							}
						}, 'json');
					}
				});
			}else{
				layer.confirm('是否要删除此会员？', {
					btn: ['确定','取消'], //按钮
					shade: false //不显示遮罩
				}, function(){
					location.href = url;
				});
			}
			return false;
		}
	}
</script>
    <div id="update_post_tips"></div>
    <div id="user" class="r_con_wrap">
      <form class="search" id="search_form" method="get" action="?">
        <div class="l">
		  <select name="Fields">
		    <option value="User_No" <?=isset($_GET['Fields']) && $_GET['Fields'] == "User_No" ? "selected" :"" ?>>会员号</option>
			<option value="User_Name" <?=isset($_GET['Fields']) && $_GET['Fields'] == "User_Name" ? "selected" :"" ?>>会员名称</option>
			<option value="User_NickName" <?=isset($_GET['Fields']) && $_GET['Fields'] == "User_NickName" ? "selected" :"" ?>>微信昵称</option>
			<option value="User_Mobile" <?=isset($_GET['Fields']) && $_GET['Fields'] == "User_Mobile" ? "selected" :"" ?>>会员手机</option>
		  </select>
          <input type="text" name="Keyword" value="<?= empty($_GET['Keyword']) ? '' : $_GET['Keyword'] ?>" class="form_input" size="15" />
          来源：
          <select name="UserFrom">
              <option value="-1">--请选择--</option>
              <?php if (!empty($From_arr)) {
                  foreach ($From_arr as $k => $v) { ?>
                    <option value='<?= $k ?>' <?=isset($_GET['UserFrom']) && $_GET['UserFrom'] == $k ? "selected" :"" ?>><?= $v ?></option>
              <?php }
              } ?>
          </select>
          会员等级：     
          <select name="MemberLevel">
            <option value="-1">--请选择--</option>
            <?php foreach($UserLevel as $k=>$u){?>
            	<option value='<?= $k ?>' <?= isset($_GET['MemberLevel']) && $_GET['MemberLevel'] == $k ? 'selected' : '' ?>><?= $u["Name"] ?></option>
            <?php }?>
          </select>
          <input type="hidden" name="search" value="1" />
          <input type="submit" class="search_btn" value=" 搜索 " />
		  <input type="button" class="output_btn" value="导出" />
		  <button type="button" class="clearAll"onclick="deleteUser(-1)">清空会员</button>
        </div>
        <div class="r"><strong>提示：</strong><span class="fc_red">双击表格可修改会员资料，按回车提交修改</span></div>
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="5%" nowrap="nowrap">序号</td>
            <td width="8%" nowrap="nowrap">来源</td>
			<td width="8%" nowrap="nowrap">会员号</td>
            <td width="10%" nowrap="nowrap">手机号</td>
            <td width="8%" nowrap="nowrap">微信昵称（姓名）</td>
            <td width="12%" nowrap="nowrap">总消费额</td>
            <td width="10%" nowrap="nowrap">头像</td>           
            <td width="8%" nowrap="nowrap">会员等级</td>
            <td width="7%" nowrap="nowrap">积分</td>
			<td width="7%" nowrap="nowrap">余额</td>
            <td width="10%" nowrap="nowrap">注册时间</td>
            <td width="8%" nowrap="nowrap" class="last"><strong>操作</strong></td>
          </tr>
        </thead>
        <tbody>
        <?php 
	    $lists = array();
		$DB->getPage("user","*",$condition,$pageSize=10);
		while($r=$DB->fetch_assoc()){
			$lists[] = $r;
		}
		$i = 1;
		foreach($lists as $key=>$rsUser){
			if ($rsUser['Is_Distribute'] == 1 && $rsConfigs['Distribute_Customize'] == 1) {
				$distribute_account = $DB->GetRs("distribute_account","Shop_Logo","where Users_ID='".$_SESSION["Users_ID"]."' and User_ID=".$rsUser['User_ID']);

				if (empty($distribute_account['Shop_Logo'])) {
					$rsUser['User_HeadImg'] = '/static/api/images/user/face.jpg';
				} else {
					$rsUser['User_HeadImg'] = $distribute_account['Shop_Logo'];
				}
			} else {
				if (empty($rsUser['User_HeadImg'])) {
					$rsUser['User_HeadImg'] = '/static/api/images/user/face.jpg';
				}
			}

			$showname= '';
			if (empty($rsUser['User_NickName']) && empty($rsUser['User_Name'])) {
				$showname = '暂无';
			} else {
				if (!empty($rsUser['User_NickName'])) {
					$showname = $rsUser['User_NickName'];
				}
				
				if (!empty($rsUser['User_Name'])) {
					$showname .= '（'.$rsUser['User_Name'].'）';
				}
				
			}
		?>
          <tr UserID="<?php echo $rsUser['User_ID'] ?>">
            <td nowrap="nowrap"><?php echo $pageSize*($DB->pageNo-1)+$i; ?></td>
            <td nowrap="nowrap"><?= empty($From_arr[$rsUser['User_From']]) ? '无' : $From_arr[$rsUser['User_From']] ?></td>
			<td nowrap="nowrap"><?php echo $rsUser['User_No'] ?></td>
            <td nowrap="nowrap" class="upd_rows" field="2"><span class="upd_txt"><?php echo $rsUser['User_Mobile'] ?></span></td>
           <td nowrap="nowrap" class="upd_rows" field="1"><span class="upd_txt"><?php echo $showname ?></span></td>
            <td nowrap="nowrap">&yen;&nbsp;<?php echo $rsUser['User_Cost']?></td>
			<td nowrap="nowrap"> <img src="<?=$rsUser['User_HeadImg'] ?>" width="60" height="60" /> </td>
             <!--<td nowrap="nowrap"><?php echo $rsUser['User_Money'] ? '<img src="'.$rsUser['User_HeadImg'].'" width="60" height="60" />' : "";?></td>
            <td nowrap="nowrap" class="upd_rows" field="0">No. <span class="upd_txt"><?php echo $rsUser['User_No'] ?></span></td>-->            
            <td nowrap="nowrap" class="upd_select" field="5"><span class="upd_txt"><?php echo empty($UserLevel[$rsUser["User_Level"]]["Name"]) ? '' : $UserLevel[$rsUser["User_Level"]]["Name"]; ?></span></td>
            <td nowrap="nowrap" class="upd_points" field="3"><span class="upd_txt"><?php echo $rsUser['User_Integral'] ?></span></td>
			<td nowrap="nowrap" class="upd_money" field="4"><span class="upd_txt"><?php echo $rsUser['User_Money'] ?></span></td>
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsUser['User_CreateTime']) ?></td>            
			<td nowrap="nowrap" class="last">
			<a class="doOrder" url="<?php echo $rsUser['User_ID'] ?>" href="javascript:void(0)"><img src="/static/member/images/ico/add.gif" align="absmiddle" /></a>
			<a href="#modpass"><img src="/static/member/images/ico/user.png" align="absmiddle" /></a><a href="#modpay"><img src="/static/member/images/ico/paychange.png" align="absmiddle" /></a> <a href="user_view.php?UserID=<?php echo $rsUser['User_ID'] ?>"><img src="/static/member/images/ico/view.gif" align="absmiddle" /></a><a href="user_capital.php?UserID=<?php echo $rsUser['User_ID'] ?>"><img src="/static/member/images/ico/x3_06_03.jpg" align="absmiddle" /></a>
			<?php
                        if($rsUser['Is_Distribute']){
				$rsUserdow = User::Multiwhere(array('Users_ID'=>$rsUser['Users_ID'],'Owner_Id'=>$rsUser['User_ID']))->first();
                            if(!$rsUserdow){
						?>
						<a href="javascript:void(0);" onclick="deleteUser('<?php echo $rsUser['User_ID'] ?>')"><img src="/static/member/images/shop/del.gif" align="absmiddle" /></a>
						<?php
					}				
			}else{
				?>
				<a href="javascript:void(0);" onclick="deleteUser('<?php echo $rsUser['User_ID'] ?>')"><img src="/static/member/images/shop/del.gif" align="absmiddle" /></a>
			<?php } ?>
			</td>
          </tr>
          <?php 
		  $i++;
		  }?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
  <div id="mod_user_pass" class="lean-modal lean-modal-forma">
    <div class="ha">登录密码重置为（<span class="red">123456</span>）<a class="modal_close" href="#"></a></div>
    <form class="form">      
      <div class="rows">
        <label></label>
        <span class="submit">
        <input type="submit" value="确定提交" name="submit_btn">
        </span>
        <div class="clear"></div>
      </div>
      <input type="hidden" name="UserID" value="">
      <input type="hidden" name="action" value="mod_password">
    </form>
    <div class="tips"></div>
  </div>
  
  <div id="mod_user_pay" class="lean-modal lean-modal-forma">
    <div class="ha">支付密码重置为（<span class="red">123456</span>）<a class="modal_close" href="#"></a></div>
    <form class="form">      
      <div class="rows">
        <label></label>
        <span class="submit">
        <input type="submit" value="确定重置" name="submit_btn">
        </span>
        <div class="clear"></div>
      </div>
      <input type="hidden" name="UserID" value="">
      <input type="hidden" name="action" value="mod_payword">
    </form>
    <div class="tips"></div>
  </div>
  
</div>
</div>
<script language="javascript">
        $(".doOrder").click(function(){
            var uid;
            uid =$(this).attr('url');
            var url = 'do_order.php?uid='+uid;
            layer.open({
              type: 2,
              title: '线下交易手动下单',

              area: ['800px', '350px'], //宽高
              content: [url, 'no']
            });
        })
    </script>
</body>
</html>