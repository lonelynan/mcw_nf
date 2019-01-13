<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/compser_library/Salesman.php');

$base_url = base_url();
$shop_url = shop_url();
$shop_url = $shop_url.'/wzw/';


/*分享页面初始化配置*/
$share_flag = 1;
$signature = '';

if(isset($_GET["UsersID"])){
  $UsersID = $_GET["UsersID"];
}else{
  echo '缺少必要的参数';
  exit;
}

if(empty($_SESSION[$UsersID."User_ID"])){
  	$_SESSION[$UsersID."HTTP_REFERER"]="/api/".$UsersID."/distribute/";
  	header("location:/api/".$UsersID."/user/login/");
}

$salesman = new Salesman($UsersID, $_SESSION[$UsersID."User_ID"]);
$limit = $salesman->up_salesman(1);
$is_salesman = $salesman->get_salesman();

$condition = "where Users_ID='".$UsersID."' and User_ID=".$_SESSION[$UsersID."User_ID"];
$distribute_smansq_record = $DB->getRs('distribute_smansq_record','No_Record_Desc,Record_Status',$condition);
if ($distribute_smansq_record) {
$showbt = $distribute_smansq_record['Record_Status'] == 2 ? '重新申请' : '立即申请';
} else {
$showbt = '立即申请';
}
if ($is_salesman && $distribute_smansq_record['Record_Status'] == 1) {
	header("location:".$base_url."api/".$UsersID."/distribute/sales/");
	exit;
}

$rsConfig = $DB->GetRs("distribute_config","Salesman,DisplayName,DisplayTelephone,Salesman_ImgPath","where Users_ID='".$UsersID."'");

$JSON = json_decode($rsConfig['Salesman'], true);
?>


<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<title>加入创始人</title>
 <link href="/static/css/bootstrap.css" rel="stylesheet">
	<link rel="stylesheet" href="/static/css/font-awesome.css">
    <link href="/static/api/distribute/css/style.css" rel="stylesheet">
     <link href="/static/api/distribute/sales/style.css" rel="stylesheet">
	 <link href="/static/api/css/fx_index.css" rel="stylesheet">	 
     <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="/static/js/jquery-1.11.1.min.js"></script>
    <script src="/static/api/js/ZeroClipboard.min.js"></script>
    <script type='text/javascript' src='/static/api/js/global.js'></script>
    <script src="/static/api/distribute/js/distribute.js?t=<?=time()?>"></script>
     <script language="javascript">
	 
	var base_url = '<?=$base_url?>';
	var UsersID = '<?=$UsersID?>';
	$(document).ready(distribute_obj.pro_file_init);
</script>


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
<style>
.sqstatus {
    margin: auto 0;
    padding: 5px 66px 5px 66px;
	line-height: 30px;
    font-size: 22px;
    color: #cc0000;
}
</style>   
</head>

<body style="background:#f5f5f5">
<div class="join_header">
	<img src="<?php echo !empty($rsConfig["Salesman_ImgPath"])?$rsConfig["Salesman_ImgPath"]:'/static/api/distribute/images/sales_join_header.jpg';?>" />
</div>
<div class="clear"></div>
<?php if ($distribute_smansq_record['Record_Status'] == 2) { ?>
<div class="sqstatus">驳回理由：<?=$distribute_smansq_record['No_Record_Desc']?></div>
<?php } else { ?>
<div class="sqstatus">正在审核中...</div>
<?php } ?>
<div class="clear"></div>
				<?php if (!$distribute_smansq_record || $distribute_smansq_record['Record_Status'] == 2) { ?>
				<form id="apply_manual" onsubmit="return false;">
				<div class="box1_x" style="padding-top: 10px;"  >
					 <?php if(!empty($rsConfig["DisplayName"])){ ?>
							<span class="l xmm_x">姓名：</span>
							<span class="l xmm1_x"><input name="Name" value="" class="xmm2_x" placeholder="请输入您的姓名" type="text" notnull></span>
					  <?php }?>
					  <?php if(!empty($rsConfig["DisplayTelephone"])){ ?>
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
				<input type="hidden" name="action" value="sqsallman"/>
				</form>
				<div class="r an" id="sqsallman" style="margin-right: 13%;"><a href="#anchor" class='topLink '><?=$showbt?></a></div>
			<div class="clear"></div>
				<?php } ?>

<div class="join_intro">
<h2>创始人特权</h2>
<p class="weidian"><strong>推荐商家</strong>商家产生的营业额、创始人可以分红</p>
<div class="join_dline"></div>
<p class="yongjin"><strong>推荐创始人</strong>可以拿商家营业额的利润</p>
</div>
</body>
</html>

