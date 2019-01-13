<?php

if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/tools.php');

if(isset($_GET["action"])){
	if($_GET["action"] == 'get_product'){
		$cate_id = $_GET['cate_id'];
	    $keyword = $_GET['keyword'];
	    $condition = "where Users_ID = '".$_SESSION['Users_ID']."'";
	   
	    if(strlen($cate_id)>0){
			$condition .= " and Products_Category = ".$cate_id." or Father_Category = ".$cate_id;
	   }
	   
	   if(strlen($keyword)>0){
			$condition .= " and Products_Name like '%".$_GET["keyword"]."%'";	
	   }
	   
	   $rsProducts = $DB->Get("shop_products",'Products_ID,Products_Name,Products_PriceX',$condition);
	   $product_list = $DB->toArray($rsProducts);
		if(empty($product_list)){
			echo '<script>alert("该选择下没有商品")</script>>';
		}
	   $option_list = '';
	   foreach($product_list as $v){
		   $option_list .= '<option value="'.$v['Products_ID'].'">'.$v['Products_Name'].'---'.$v['Products_PriceX'].'</option>';
	   }
	   echo $option_list;
	   exit;
	}
}

if(empty($_REQUEST['LevelID'])){
	echo '缺少必要的参数';
	exit;
}

$LevelID = $_REQUEST['LevelID'];

if(empty($_REQUEST['level'])){
	echo '缺少必要的参数';
	exit;
}

if(!isset($_REQUEST['type'])){
	echo '缺少必要的参数';
	exit;
}

$level = $_REQUEST['level'];
$type = $_REQUEST['type'];
$firstone = $DB->GetRs('distribute_level','Level_ID,Level_LimitType,Present_type','where Users_ID="'.$_SESSION["Users_ID"].'" order by Level_ID asc');//获取第一个分销商级别
$first = $firstone;
$firsttwo = [];
if ($firstone['Level_LimitType'] == 3 || $firstone['Level_LimitType'] == 4) {
	$firsttwo = $DB->GetRs('distribute_level','Level_ID,Level_LimitType,Present_type','where Level_LimitType !=3 and Level_LimitType !=4 and Users_ID="'.$_SESSION["Users_ID"].'" order by Level_ID asc');//获取第一个分销商级别
}
if($_POST){
	$PeopleLimit = array();
	foreach($_POST['PeopleLimit'] as $k=>$v){
		$PeopleLimit[$k] = empty($v) ? 0 : intval($v);
	}
	
	if($first['Level_ID']==$LevelID){
		if($_POST['Come_Type'] == 3){
			$type_confirm = 3;
		} else if ($_POST['Come_Type'] == 4) {
            $type_confirm = 4;
        }else{
			$type_confirm = $type;
		}
	}else{
		$type_confirm = $type;
	}
	$Data=array(
		"Level_Name"=>$_POST['Name'],
		"Level_ImgPath"=>$_POST['ImgPath'],
		"Level_LimitType"=>$type_confirm,  //3是无门槛,4是手动申请
        "Level_Isauditing" => !empty($_POST['isauditing']) ? $_POST['isauditing'] : 0,  //手动申请是否需要审核
		"Level_PeopleLimit"=>empty($PeopleLimit) ? '' : json_encode($PeopleLimit,JSON_UNESCAPED_UNICODE)
	);
	
			$Data['Level_Description'] = $_POST['Level_Description'];	
	
	if($type==0){//直接购买
		if($first['Level_ID']==$LevelID){
			if($_POST['Come_Type']==1){
				if(empty($_POST['Price'])){
					echo '<script language="javascript">alert("请填写级别价格！");history.back();</script>';
					exit;
				}
				$Distributes = array();
				foreach($_POST['Distributes'] as $key=>$val){
					$Distributes[$key] = empty($val) ? 0 : number_format($val,2,'.','');
				}
				$Data['Level_LimitValue'] = $_POST['Price'];
				$Data['Level_Distributes'] = empty($Distributes) ? '' : json_encode($Distributes,JSON_UNESCAPED_UNICODE);		
			} elseif ($_POST['Come_Type'] == 4) {
                //判断增加的输入项是否为空
                if (!empty($_POST['JSON'])) {
                    foreach ($_POST['JSON'] as $key => $value) {
                        $Type = trim(cleanJsCss($value['Type']));
                        $Name = trim(cleanJsCss($value['Name']));
                        $Value = trim(cleanJsCss($value['Value']));
                        if (empty($Type) || empty($Name) || empty($Value)) {
                            echo '<script>alert("请填写输入信息提示，以便用户填写");history.back();</script>';
                            exit;
                        } else {
                            $_POST[$key] = [
                                'Type' => $Type,
                                'Name' => $Name,
                                'Value' => $Value
                            ];
                        }
                    }
                }
                $Data['Reserve_DisplayName'] = empty($_POST['DisplayName']) ? "0" : $_POST['DisplayName'];
                $Data['Reserve_DisplayTelephone'] = empty($_POST['DisplayTelephone']) ? "0" : $_POST['DisplayTelephone'];
                $Data['Distribute_Form'] = json_encode((isset($_POST["JSON"]) ? $_POST["JSON"] : []), JSON_UNESCAPED_UNICODE);
            }else{
				$Data['Level_LimitValue'] = '';
				$Data['Level_Distributes'] = '';	
			}
		}else{
			$Distributes = array();
			foreach($_POST['Distributes'] as $key=>$val){
				$Distributes[$key] = empty($val) ? 0 : number_format($val,2,'.','');
			}
			$Data['Level_LimitValue'] = $_POST['Price'];
			$Data['Level_Distributes'] = empty($Distributes) ? '' : json_encode($Distributes,JSON_UNESCAPED_UNICODE);
		}
		if(!empty($_POST['Present_Price'])){
			$Data['Level_PresentValue'] = $_POST['Present_Price'];
		}
		if(isset($_POST['Present_type'])){
			$Data['Present_type'] = $_POST['Present_type'];
		}
		if(!empty($_POST['Present_UpdatePrice'])){
			$Data['Level_PresentUpdateValue'] = $_POST['Present_UpdatePrice'];	
		}		
	}elseif($type==1){//消费额限制
		if($first['Level_ID']==$LevelID){
			if($_POST['Come_Type']==1){
				if(empty($_POST['Xiaofei_Amount'])){
					echo '<script language="javascript">alert("请填写消费额！");history.back();</script>';
					exit;
				}
				$Data['Level_LimitValue'] = $_POST['Xiaofei_Type'].'|'.number_format($_POST['Xiaofei_Amount'],2,'.','').'|'.$_POST['Xiaofei_Time'];
			} elseif ($_POST['Come_Type'] == 4) {
                //判断增加的输入项是否为空
                if (!empty($_POST['JSON'])) {
                    foreach ($_POST['JSON'] as $key => $value) {
                        $Type = trim(cleanJsCss($value['Type']));
                        $Name = trim(cleanJsCss($value['Name']));
                        $Value = trim(cleanJsCss($value['Value']));
                        if (empty($Type) || empty($Name) || empty($Value)) {
                            echo '<script>alert("请填写输入信息提示，以便用户填写");history.back();</script>';
                            exit;
                        } else {
                            $_POST[$key] = [
                                'Type' => $Type,
                                'Name' => $Name,
                                'Value' => $Value
                            ];
                        }
                    }
                }
                $Data['Reserve_DisplayName'] = empty($_POST['DisplayName']) ? "0" : $_POST['DisplayName'];
                $Data['Reserve_DisplayTelephone'] = empty($_POST['DisplayTelephone']) ? "0" : $_POST['DisplayTelephone'];
                $Data['Distribute_Form'] = json_encode((isset($_POST["JSON"]) ? $_POST["JSON"] : []), JSON_UNESCAPED_UNICODE);
            }else{
				$Data['Level_LimitValue'] = '';
			}
		}else{
			$Data['Level_LimitValue'] = $_POST['Xiaofei_Type'].'|'.number_format($_POST['Xiaofei_Amount'],2,'.','').'|'.$_POST['Xiaofei_Time'];
		}		
	}elseif($type==2){//购买商品
		if($first['Level_ID']==$LevelID){
			if($_POST['Come_Type']==1){
				if($_POST['Fanwei']==1){
					if(empty($_POST['BuyIDs'])){
						echo '<script language="javascript">alert("请选择商品！");history.back();</script>';
						exit;
					}
					$Data['Level_LimitValue'] = $_POST['Fanwei'].'|'.substr($_POST['BuyIDs'],1,-1);
				}elseif($_POST['Fanwei'] == 2){
					if(empty($_POST['product_id'])){
						echo '<script language="javascript">alert("请选择商品！");history.back();</script>';
						exit;
					}
					$Data['Level_LimitValue'] = $_POST['Fanwei'].'|'.$_POST['product_id'];
				}else{
					$Data['Level_LimitValue'] = $_POST['Fanwei'];
				}
			} elseif ($_POST['Come_Type'] == 4) {
                //判断增加的输入项是否为空
                if (!empty($_POST['JSON'])) {
                    foreach ($_POST['JSON'] as $key => $value) {
                        $Type = trim(cleanJsCss($value['Type']));
                        $Name = trim(cleanJsCss($value['Name']));
                        $Value = trim(cleanJsCss($value['Value']));
                        if (empty($Type) || empty($Name) || empty($Value)) {
                            echo '<script>alert("请填写输入信息提示，以便用户填写");history.back();</script>';
                            exit;
                        } else {
                            $_POST[$key] = [
                                'Type' => $Type,
                                'Name' => $Name,
                                'Value' => $Value
                            ];
                        }
                    }
                }
                $Data['Reserve_DisplayName'] = empty($_POST['DisplayName']) ? "0" : $_POST['DisplayName'];
                $Data['Reserve_DisplayTelephone'] = empty($_POST['DisplayTelephone']) ? "0" : $_POST['DisplayTelephone'];
                $Data['Distribute_Form'] = json_encode((isset($_POST["JSON"]) ? $_POST["JSON"] : []), JSON_UNESCAPED_UNICODE);
            }else{
				$Data['Level_LimitValue'] = '';
			}
		}else{		
			if($_POST['Fanwei']==1){
				if(empty($_POST['BuyIDs'])){
					echo '<script language="javascript">alert("请选择商品！");history.back();</script>';
					exit;
				}
				$Data['Level_LimitValue'] = $_POST['Fanwei'].'|'.substr($_POST['BuyIDs'],1,-1);
			}elseif($_POST['Fanwei'] == 2){
					if(empty($_POST['product_id'])){
						echo '<script language="javascript">alert("请选择商品！");history.back();</script>';
						exit;
					}
					$Data['Level_LimitValue'] = $_POST['Fanwei'].'|'.$_POST['product_id'];
			}else{
				$Data['Level_LimitValue'] = $_POST['Fanwei'];
			}
		}
	}
	
	//升级相关
	
	if($LevelID != $first['Level_ID'] && $type != 1){
		if(($_POST['Update_Type']==0 || $_POST['Update_Type']==2) && $_POST['Update_switch'] != 0 ){//补价格
			if(empty($_POST['UpdatePrice'])){
				echo '<script language="javascript">alert("请填写价格！");history.back();</script>';
				exit;
			}
			$UpdateDistributes = array();
			foreach($_POST['UpdateDistributes'] as $key=>$val){
				$UpdateDistributes[$key] = empty($val) ? 0 : number_format($val,2,'.','');
			}
			$Data['Level_UpdateValue'] = $_POST['UpdatePrice'];
			$Data['Level_UpdateDistributes'] = empty($UpdateDistributes) ? '' : json_encode($UpdateDistributes,JSON_UNESCAPED_UNICODE);
		}else{
			if(empty($_POST['UpdateBuyIDs']) && $_POST['Update_switch'] != 0){
				echo '<script language="javascript">alert("请选择商品！");history.back();</script>';
				exit;
			}
			$Data['Level_UpdateValue'] = substr($_POST['UpdateBuyIDs'],1,-1);
			$Data['Level_UpdateDistributes'] = '';
		}
		$Data['Level_UpdateType'] = $_POST['Update_Type'];
		$Data['Update_switch'] = $_POST['Update_switch'];
	}
	$Flag=$DB->Set("distribute_level",$Data,'where Users_ID="'.$_SESSION['Users_ID'].'" and Level_ID='.$LevelID);
	if($Flag){
		//更新分销商级别文件
		update_dis_level($DB,$_SESSION['Users_ID']);
		echo '<script language="javascript">alert("修改成功！");window.location.href="level.php?level='.$level.'&type='.$type.'";</script>';
		exit;
	}else{
		echo '<script language="javascript">alert("修改失败！");history.back();</script>';
		exit;
	}
}else{
	$rsLevel = $DB->GetRs('distribute_level','*','where Users_ID="'.$_SESSION["Users_ID"].'" and Level_ID='.$LevelID);
	if(!$rsLevel){
		echo '您要修改的级别不存在';
		exit;
	}
	
	$shop_config = shop_config($_SESSION["Users_ID"]);
	
	$category_list = array();
	$DB->get("shop_category","*","where Users_ID='".$_SESSION["Users_ID"]."' order by Category_ParentID asc,Category_Index asc");
	while($rsCategory = $DB->fetch_assoc()){
		if($rsCategory["Category_ParentID"] != $rsCategory["Category_ID"]){
			if($rsCategory["Category_ParentID"] == 0){
				$category_list[$rsCategory["Category_ID"]] = $rsCategory;
			}else{
				$category_list[$rsCategory["Category_ParentID"]]["child"][] = $rsCategory;
			}
		}
	}

	
	$PeopleLimit = json_decode($rsLevel['Level_PeopleLimit'],true);
	
	if($type==0){//直接购买
		if($rsLevel['Level_LimitType']==$type){
			$Distributes = json_decode($rsLevel['Level_Distributes'],true);
		}else{
			$rsLevel['Level_LimitValue'] = '';
			$Distributes = array();
		}
	}elseif($type==1){//消费额
		if($rsLevel['Level_LimitType']==$type){
			$limit_arr = explode('|',$rsLevel['Level_LimitValue']);
		}else{
			$limit_arr = array(0,'',4);
		}
	}elseif($type == 2){//购买产品
		$products = array();
		if($rsLevel['Level_LimitType']==$type){
			$limit_arr = explode('|',$rsLevel['Level_LimitValue']);
			if($limit_arr[0]==1 && $limit_arr[1]){
				$DB->Get('shop_products','Products_ID,Products_Name,Products_PriceX','where Users_ID="'.$_SESSION['Users_ID'].'" and Products_ID in('.$limit_arr[1].')');
				while($r = $DB->fetch_assoc()){
					$products[] = $r;
				}
			}elseif($limit_arr[0]==2 && $limit_arr[1]){
				$DB->Get('shop_products','Products_ID,Products_Name,Products_PriceX','where Users_ID="'.$_SESSION['Users_ID'].'" and Products_ID = "'.$limit_arr[1].'"');
				while ($r = $DB->fetch_assoc()) {
					$products[] = $r;
				}
			}
		}else{
			$limit_arr = array(0,'');
		}
	}
	$arr = array('一','二','三','四','五','六','七','八','九','十');
	$_TYPE = array('直接购买','消费额','购买商品','无门槛', '手动申请');

	// 手动申请表单
    $JSON = json_decode($rsLevel['Distribute_Form'], true);
	
	//升级设置相关
	$UpdateDistributes = $updateproducts = array();
	if($rsLevel['Level_ID'] != $first['Level_ID']){//第一个级别没有升级设置
		$UpdateDistributes = json_decode($rsLevel['Level_UpdateDistributes'],true);
		if($rsLevel['Level_UpdateType']!=0){//补差价
			if($rsLevel['Level_UpdateValue']){
				$DB->Get('shop_products','Products_ID,Products_Name,Products_PriceX','where Users_ID="'.$_SESSION['Users_ID'].'" and Products_ID in('.$rsLevel['Level_UpdateValue'].')');
				while($r = $DB->fetch_assoc()){
					$updateproducts[] = $r;
				}
			}
		}
	}
}

$Present_typearr = array(0=>'直接购买会员',1=>'送产品',2=>'存入余额');
$dis_level = get_dis_level($DB,$_SESSION['Users_ID']);
if (count($dis_level) > 1) {
	$realtwo = next($dis_level);
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
<link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/member/js/distribute/dis_level.js?t=<?=time()?>'></script>
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script language="javascript">
var Update_switch = <?php echo $rsLevel['Update_switch'] ? $rsLevel['Update_switch'] : 0;?>;
var type = <?php echo $type ? $type : 0;?>;
$(document).ready(dis_level.level_edit);
KindEditor.ready(function(K) {
	var editor = K.editor({
		uploadJson : '/member/upload_json.php?TableField=distribute',
		fileManagerJson : '/member/file_manager_json.php',
		showRemote : true,
		allowFileManager : true,
	});
	K('#ImgUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#ImgPath').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#ImgPath').val(url);
					K('#ImgDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
})
</script>
<style type="text/css">
#ImgDetail img{width:100px; margin-top:8px}
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
    
    <div id="level" class="r_con_wrap">
      <form id="level_form" class="r_con_form" method="post" action="?">
      	<h2 style="height:40px; line-height:40px; font-size:14px; font-weight:bold; background:#eee; text-indent:15px;">基本设置</h2>    
        <div class="rows">
          <label>级别名称</label>
          <span class="input">
          <input name="Name" value="<?php echo $rsLevel['Level_Name'];?>" type="text" class="form_input" size="20" notnull>
          <font class="fc_red">*</font>
          </span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>级别描述</label>
          <span class="input">
			<textarea name="Level_Description"> <?php echo $rsLevel['Level_Description'];?></textarea>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>级别图片</label>
          <span class="input"> <span class="upload_file">
          <div>
            <div class="up_input">
              <input type="button" id="ImgUpload" value="添加图片" style="width:80px;" />
            </div>
            <div class="tips">图片建议尺寸：200*120</div>
            <div class="clear"></div>
          </div>
          <div class="img" id="ImgDetail"><?php if($rsLevel['Level_ImgPath']){?><img src="<?php echo $rsLevel['Level_ImgPath'];?>" width="100" /><?php }?></div>
          </span> </span>
          <input name="ImgPath" id="ImgPath" type="hidden" value="<?php echo $rsLevel['Level_ImgPath'];?>">
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>佣金人数限制</label>
          <span class="input">
            <table class="item_data_table" border="0" cellpadding="3" cellspacing="0">
            <?php 
				$arr = array('一','二','三','四','五','六','七','八','九','十');
				for($i=0;$i<$level;$i++){
			?>
				<tr>
					<td>
						<?php echo $arr[$i]?>级
                        &nbsp;&nbsp;
						<input name="PeopleLimit[<?php echo $i+1;?>]" value="<?php echo empty($PeopleLimit[$i+1]) ? 0 : $PeopleLimit[$i+1];?>" class="form_input" size="5" maxlength="10" type="text">
						个
					</td>
				</tr>
			<?php }?>
            </table>
             <div class="tips" style="height:80px; line-height:20px;">注：此级别的分销商获得佣金的人数限制。<br />
            如：一级 3、二级 -1、三级 -1，说明此级别分销商只能获得3个下属的一级佣金，不能获得二级、三级佣金；<br />
            0表示不限制，-1 表示禁止获得此级别佣金。<br />
            此设置对于发展下级会员人数不起作用
            </div>
          </span>
          <div class="clear"></div>
        </div>
        <?php if($first['Level_ID']==$LevelID){?>
        <div class="rows">
          <label>级别门槛</label>
          <span class="input">
           <input name="Come_Type" value="3" id="radio_0" type="radio" <?= $rsLevel['Level_LimitType'] == 3 ? 'checked' : '' ?> ><label for="radio_0">无门槛</label>&nbsp;&nbsp;
              <input name="Come_Type" value="4" id="radio_4" type="radio" <?= $rsLevel['Level_LimitType'] == 4 ? 'checked' : '' ?> ><label for="radio_4">手动申请</label>&nbsp;&nbsp;
		   <input name="Come_Type" value="1" id="radio_1" type="radio" <?= $rsLevel['Level_LimitType'] != 3 && $rsLevel['Level_LimitType'] != 4 ? 'checked' : '' ?>><label for="radio_1"><?= $_TYPE[$type] ?></label>
          </span>
          <div class="clear"></div>
        </div>
        <?php }?>
		
        <?php if($first['Level_ID']==$LevelID){?>
        <div id="type_1"<?php if($rsLevel['Level_LimitType']==3 || $rsLevel['Level_LimitType'] == 4){?> style="display:none"<?php }?>>
        <?php }?>
        <?php if($type==0){//直接购买?>
			<div class="rows">
			  <label>直接购买</label>
			  <span class="input">
			  <?php if (isset($firsttwo) && !empty($firsttwo)) { ?>
				<?php if ($firsttwo['Level_ID'] == $rsLevel['Level_ID']) { ?>
					<select name="Present_type" id="Present_type">
							<option value="0" <?php if($rsLevel['Present_type'] =='0' ){ ?> selected <?php }?>>直接购买会员</option>
							<option value="1" <?php if($rsLevel['Present_type'] =='1' ){?> selected <?php }?>>送产品</option>
							<option value="2" <?php if($rsLevel['Present_type'] =='2' ){?> selected <?php }?>>存入余额</option>
					</select>
					<?php } else { 
					$Present_type = empty($firsttwo['Present_type']) ? 0 : $firsttwo['Present_type'];
					?>
					<select name="Present_type" id="Present_type">
							<option value="<?=$Present_type?>" selected ><?=$Present_typearr[$Present_type]?></option>
					</select>
					<?php } ?>

					<?php } else { ?>
						<select name="Present_type" id="Present_type">
							<option value="0" <?php if($rsLevel['Present_type'] =='0' ){ ?> selected <?php }?>>直接购买会员</option>
							<option value="1" <?php if($rsLevel['Present_type'] =='1' ){?> selected <?php }?>>送产品</option>
							<option value="2" <?php if($rsLevel['Present_type'] =='2' ){?> selected <?php }?>>存入余额</option>
					</select>
					<?php } ?>
			  </span>
			  <div class="clear"></div>
			</div>
			
		<?php if($rsLevel['Present_type'] =='2'){?>
			<div class="rows">
			  <label>级别价格</label>
			  <span class="input" id="Prices">
				<input name='Price' value='<?php echo $rsLevel['Level_LimitValue'];?>' type='text' class='form_input' size='5' <?php if($first['Level_ID']==$LevelID){?><?php }else{?>notnull<?php }?> > 元 赠送 <input type='text' name='Present_Price' value='<?php echo $rsLevel['Level_PresentValue'];?>' class='form_input' size='5' maxlength='10' /> 元 <span class='tips'>&nbsp;&nbsp;注：用户购买此级别的价格</span>
			  </span>
			  <div class="clear"></div>
			</div>
		<?php }else{?>
			    <div class="rows">
				  <label>级别价格</label>
				  <span class="input" id="Prices">
				   <input name="Price" value="<?php echo $rsLevel['Level_LimitValue'];?>" type="text" class="form_input" size="5" <?php if($first['Level_ID']==$LevelID){?><?php }else{?>notnull<?php }?>> 元<span class="tips">&nbsp;&nbsp;注：用户购买此级别的价格</span>
				  </span>
				  <div class="clear"></div>
				</div>
		<?php } ?>
        
		
          <div class="rows">
          <label>佣金发放设置</label>
          <span class="input">
            <table class="item_data_table" border="0" cellpadding="3" cellspacing="0">
            <?php 
                $arr = array('一','二','三','四','五','六','七','八','九','十');
                for($i=0;$i<$level;$i++){
            ?>
                <tr>
                    <td>
                        <?php echo $arr[$i]?>级
                        &nbsp;&nbsp;
                        <input name="Distributes[<?php echo $i+1;?>]" value="<?php echo empty($Distributes[$i+1]) ? 0 : $Distributes[$i+1];?>" class="form_input" size="5" maxlength="10" type="text">
                        元
                    </td>
                </tr>
            <?php }?>
            </table>
            <div class="tips">注：会员购买此级别时，其上级获得的佣金</div>
          </span>
          <div class="clear"></div>
        </div>
        <?php }elseif($type==1){//消费额?>
        <div class="rows">
          <label>消费类型</label>
          <span class="input">
           <input name="Xiaofei_Type" value="0" id="xiaofei_0" type="radio"<?php echo $limit_arr[0]==0 ? ' checked' : ''?> ><label for="xiaofei_0">商城总消费</label>&nbsp;&nbsp;
           <input name="Xiaofei_Type" value="1" id="xiaofei_1" type="radio"<?php echo $limit_arr[0]==1 ? ' checked' : ''?>><label for="xiaofei_1">一次性消费</label>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>消费金额</label>
          <span class="input">
           <input name="Xiaofei_Amount" value="<?php echo empty($limit_arr[1]) ? 0 : $limit_arr[1];?>" type="text" class="form_input" size="5" <?php if($first['Level_ID']==$LevelID){?><?php }else{?>notnull<?php }?>> 元<span class="tips">&nbsp;&nbsp;注：用户需消费此额度才能成为该级别分销商</span>
          </span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label>生效状态</label>
          <span class="input">
           <input name="Xiaofei_Time" type="radio" value="2" id="time_2"<?php echo $limit_arr[2]==2 ? ' checked' : ''?>><label for="time_2">订单付款后计入</label>&nbsp;&nbsp;
           <input name="Xiaofei_Time" type="radio" value="4" id="time_4"<?php echo $limit_arr[2]==4 ? ' checked' : ''?>><label for="time_4">订单确认收货后计入</label>
          </span>
          <div class="clear"></div>
        </div>
        <?php }elseif($type==2){//购买商品?>
        <div class="rows">
               <label>选择商品</label>
               <span class="input">
                <input type="radio" name="Fanwei" id="Fanwei_0" value="0"<?php echo $limit_arr[0]==0 ? ' checked' : ''?> /><label> 任意商品</label>&nbsp;&nbsp;<input type="radio" name="Fanwei" id="Fanwei_1" value="1"<?php echo $limit_arr[0]==1 ? ' checked' : ''?> /><label> 特定商品</label>
                <div class="products_option"<?php echo $limit_arr[0]==1 ? ' style="display:block"' : ' style="display:none"'?>>
                    <div class="search_div">
                      <select>
                      <option value=''>--请选择--</option>
                      <?php foreach($category_list as $key=>$item):?>
                      <option value="<?=$key?>"><?=$item['Category_Name']?></option>
                       <?php if(!empty($item['child'])):?>              
                           <?php foreach($item['child'] as $cate_id=>$child):?>
                            <option value="<?php echo $child["Category_ID"];?>">&nbsp;&nbsp;&nbsp;&nbsp;<?=$child["Category_Name"]?></option>
                           <?php endforeach;?>
                       <?php endif;?>
                      <?php endforeach;?>
                     </select>
                     <input type="text" placeholder="关键字" value="" class="form_input" size="35" maxlength="30" />
                     <button type="button" class="button_search">搜索</button>
                   </div>
                   
                   <div class="select_items">
                     <select size='10' class="select_product0" style="width:240px; height:100px; display:block; float:left">
                     </select>
                     <button type="button" class="button_add">=></button>
                     <select size='10' class="select_product1" multiple style="width:240px; height:100px; display:block; float:left">
                     <?php if(!empty($products)){?>
                     	<?php foreach($products as $item){?>
                        <option value="<?php echo $item['Products_ID'];?>"><?php echo $item['Products_Name'];?>---<?php echo $item['Products_PriceX'];?></option>
                        <?php }?>
                     <?php }?>
                     </select>
                     <input type="hidden" name="BuyIDs" value="<?php echo !empty($limit_arr[1]) ? ','.$limit_arr[1].',' : '';?>" />
                   </div>
                   
                   <div class="options_buttons">
                        <button type="button" class="button_remove">移除</button>
                        <button type="button" class="button_empty">清空</button>
                   </div>
                </div>
                <div class="products_one" <?php echo $limit_arr[0]==2 ? 'style="display:block"' : 'style="display:none"'?>>
                  <div class="search_one">
                      <select>
                      <option value=''>--请选择--</option>
                      <?php foreach($category_list as $key=>$item):?>
                      <option value="<?=$key?>"><?=$item['Category_Name']?></option>
                       <?php if(!empty($item['child'])):?>              
                           <?php foreach($item['child'] as $cate_id=>$child):?>
                            <option value="<?php echo $child["Category_ID"];?>">&nbsp;&nbsp;&nbsp;&nbsp;<?=$child["Category_Name"]?></option>
                           <?php endforeach;?>
                       <?php endif;?>
                      <?php endforeach;?>
                     </select>
                     <input type="text" placeholder="关键字" value="" class="form_input_one" size="35" maxlength="30" />
                     <button type="button" class="button_one">搜索</button>
                  </div>
                  <div class="select_item" >
                     <select class="select_product" name="product_id" style="float:left" >
                     	<?php if(!empty($products)){?>
                     		<?php foreach($products as $item){?>
                     		<option value="<?php echo $item['Products_ID'];?>"><?php echo $item['Products_Name'];?>---<?php echo $item['Products_PriceX'];?></option>
                     		<?php }?>
                     	<?php }?>
                     </select>
                  </div>
                 
                </div>
               </span>
               <div class="clear"></div>
        </div>
        <?php }?>
       
        <?php if($first['Level_ID']==$LevelID){?>
        </div>
            <!--手动申请-->
          <div id="type_2" <?= $rsLevel['Level_LimitType'] == 4 ? '' : 'style="display:none"' ?>>
              <div class="rows">
                  <label>添加表单设置</label>
                  <span class="input">
                            <div class="tips">填写你要收集的内容，预约默认选项不可以修改删除！</div>
                            <table border="0" cellpadding="5" cellspacing="0" id="reverve_field_table" class="reverve_field_table">
                                <thead>
                                <tr>
                                    <td width="16%">字段类型</td>
                                    <td width="32%">字段名称</td>
                                    <td width="32%">初始内容</td>
                                    <td width="20%">操作</td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>文本框：</td>
                                    <td><input type="text" class="form_input" value="姓名" name="" disabled="disabled"/></td>
                                    <td><input type="text" class="form_input" value="请输入您的姓名" name="" disabled="disabled"/></td>
                                    <td><input type="checkbox" name="DisplayName" value="1" <?= empty($rsLevel["Reserve_DisplayName"]) ? "" : "checked" ?> /> 是否显示</td>
                                </tr>
                                <tr>
                                    <td>文本框：</td>
                                    <td><input type="text" class="form_input" value="电话" name="" disabled="disabled"/></td>
                                    <td><input type="text" class="form_input" value="请输入您的电话" name="" disabled="disabled"/></td>
                                    <td><input type="checkbox" name="DisplayTelephone" value="1" <?= empty($rsLevel["Reserve_DisplayTelephone"]) ? "" : "checked" ?> /> 是否显示</td>
                                </tr>
                                <?php
                                if (!empty($JSON)) {
                                    foreach ($JSON as $key => $value) {
                                        echo '<tr><td><select name="JSON[' . $key . '][Type]" onchange="JSONType(this);"><option value="text"' . ($value["Type"] == "text" ? " selected" : "") . '>文本框</option><option value="select"' . ($value["Type"] == "select" ? " selected" : "") . '>选择框</option></select>：</td><td><input type="text" name="JSON[' . $key . '][Name]" value="' . $value["Name"] . '" class="form_input"></td><td><input type="text" placeholder="" name="JSON[' . $key . '][Value]" value="' . $value["Value"] . '" class="form_input"></td><td><a onclick="document.getElementById(\'reverve_field_table\').deleteRow(this.parentNode.parentNode.rowIndex);" href="javascript:void(0);"> <img hspace="5" src="/static/member/images/ico/del.gif"></a></td></tr>';
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                            <table border="0" cellpadding="5" cellspacing="0">
                                <thead>
                                <tr>
                                    <td align="center"><a href="javascript:;" onClick="insertRow()" class="btn_gray"><img src="/static/member/images/ico/add.gif" align="absmiddle"/>增加</a></td>
                                </tr>
                                </thead>
                            </table>
                            </span>
                  <div class="clear"></div>
              </div>
              <div class="rows">
                  <label>是否审核</label>
                  <span class="input">
                            <input name="isauditing" value="1" id="radio_5" type="radio" <?= $rsLevel['Level_Isauditing'] == 1 ? 'checked' : '' ?> ><label for="radio_5">审核</label>&nbsp;&nbsp;
                            <input name="isauditing" value="0" id="radio_6" type="radio" <?= $rsLevel['Level_Isauditing'] == 0 ? 'checked' : '' ?> ><label for="radio_6">不审核</label>
                        </span>
                  <div class="clear"></div>
              </div>
          </div>


        <?php }
		if (isset($realtwo) && $type != 1) {
			$tipupswitch = '升级设置（低级分销商向本级分销商升级）';
		?>
        <?php if($first['Level_ID'] != $LevelID){?>
        <h2 style="height:40px; line-height:40px; font-size:14px; font-weight:bold; background:#eee; text-indent:15px;">
		<input name="Update_switch" value="0" id="upswi_0" type="radio"<?php echo $rsLevel['Update_switch']==0 ? ' checked' : ''?> ><label for="upswi_0">关闭</label>&nbsp;&nbsp;<input name="Update_switch" value="1" id="upswi_1" type="radio"<?php echo $rsLevel['Update_switch']==1 ? ' checked' : ''?> ><label for="upswi_1">开启</label>&nbsp;&nbsp;<?=$tipupswitch?></h2>
        <div class="rows" id="update_zd">
          <label>升级门槛</label>
          <span class="input">
           <input name="Update_Type" value="0" id="update_0" type="radio"<?php echo $rsLevel['Level_UpdateType']==0 ? ' checked' : ''?> ><label for="update_0">补差</label>&nbsp;&nbsp;<input name="Update_Type" value="2" id="update_2" type="radio"<?php echo $rsLevel['Level_UpdateType']==2 ? ' checked' : ''?> ><label for="update_2">指定</label>&nbsp;&nbsp;
           <?php if($type == 2){ ?><input name="Update_Type" value="1" id="update_1" type="radio"<?php echo $rsLevel['Level_UpdateType']==1 ? ' checked' : ''?>><label for="update_1">购买指定商品</label><?php }?>
          </span>
          <div class="clear"></div>
        </div>
        <div id="update_div_0"<?php if($rsLevel['Level_UpdateType']==1){?> style="display:none"<?php }?>>
		
			<?php if($rsLevel['Present_type'] == 2){?>
				<div class="rows">
				  <label>价格</label>
				  <span class="input" id="UpdatePrices">
					<input name='UpdatePrice' value='<?php echo $rsLevel['Level_UpdateType']==0 || $rsLevel['Level_UpdateType']==2 ? $rsLevel['Level_UpdateValue'] : '';?>' type='text' class='form_input' size='5' > 元 赠送<input type='text' name='Present_UpdatePrice' value='<?php echo $rsLevel['Level_PresentUpdateValue'];?>' class='form_input' size='5' maxlength='10' /> 元 <span class='tips'>&nbsp;&nbsp;注：会员升级到本级别时所需支付的金额
				  </span>
				  <div class="clear"></div>
				</div>
			<?php }else{?>
				<div class="rows">
				  <label>价格</label>
				  <span class="input" id="UpdatePrices">
				   <input name="UpdatePrice" value="<?php echo $rsLevel['Level_UpdateType']==0 || $rsLevel['Level_UpdateType']==2 ? $rsLevel['Level_UpdateValue'] : '';?>" type="text" class="form_input" size="5" > 元 <span class="tips">&nbsp;&nbsp;注：会员升级到本级别时所需支付的金额</span>
				  </span>
				  <div class="clear"></div>
				</div>
			<?php } ?>
		
		
 
              <div class="rows">
              <label>佣金发放设置</label>
              <span class="input">
                <table class="item_data_table" border="0" cellpadding="3" cellspacing="0">
                <?php 
                    $arr = array('一','二','三','四','五','六','七','八','九','十');
                    for($i=0;$i<$level;$i++){
                ?>
                    <tr>
                        <td>
                            <?php echo $arr[$i]?>级
                            &nbsp;&nbsp;
                            <input name="UpdateDistributes[<?php echo $i+1;?>]" value="<?php echo empty($UpdateDistributes[$i+1]) ? 0 : $UpdateDistributes[$i+1];?>" class="form_input" size="5" maxlength="10" type="text">
                            元
                        </td>
                    </tr>
                <?php }?>
                </table>
                <div class="tips">注：会员升级到本级别时，其上级获得的佣金</div>
              </span>
              <div class="clear"></div>
            </div>
        </div>
            <div id="update_div_1"<?php if($rsLevel['Level_UpdateType']==0 || $rsLevel['Level_UpdateType']==2){?> style="display:none"<?php }?>>
        	<div class="rows">
               <label>选择商品</label>
               <span class="input">
                <div class="products_option">
                    <div class="search_div">
                      <select>
                      <option value=''>--请选择--</option>
                      <?php foreach($category_list as $key=>$item):?>
                      <option value="<?=$key?>"><?=$item['Category_Name']?></option>
                       <?php if(!empty($item['child'])):?>              
                           <?php foreach($item['child'] as $cate_id=>$child):?>
                            <option value="<?php echo $child["Category_ID"];?>">&nbsp;&nbsp;&nbsp;&nbsp;<?=$child["Category_Name"]?></option>
                           <?php endforeach;?>
                       <?php endif;?>
                      <?php endforeach;?>
                     </select>
                     <input type="text" placeholder="关键字" value="" class="form_input" size="35" maxlength="30" />
                     <button type="button" class="button_search">搜索</button>
                   </div>
                   
                   <div class="select_items">
                     <select size='10' class="select_product0" style="width:240px; height:100px; display:block; float:left">
                     </select>
                     <button type="button" class="button_add">=></button>
                     <select size='10' class="select_product1" multiple style="width:240px; height:100px; display:block; float:left">
                     <?php if(!empty($updateproducts)){?>
                     	<?php foreach($updateproducts as $item){?>
                        <option value="<?php echo $item['Products_ID'];?>"><?php echo $item['Products_Name'];?>---<?php echo $item['Products_PriceX'];?></option>
                        <?php }?>
                     <?php }?>
                     </select>
                     <input type="hidden" name="UpdateBuyIDs" value="<?php echo !empty($rsLevel['Level_UpdateValue']) ? ','.$rsLevel['Level_UpdateValue'].',' : '';?>" />
                   </div>
                   
                   <div class="options_buttons">
                        <button type="button" class="button_remove">移除</button>
                        <button type="button" class="button_empty">清空</button>
                   </div>
                </div>
               </span>
               <div class="clear"></div>
        	</div>
        </div>
        <?php } } ?>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" value="确定" name="submit_btn">
          <a href="javascript:void(0);" class="btn_gray" onClick="window.location.href='level.php?level=<?php echo $level;?>&type=<?php echo $type;?>'">返回</a></span>
          <div class="clear"></div>
        </div>
        <input type="hidden" name="level" value="<?php echo $level;?>">
        <input type="hidden" name="type" value="<?php echo $type;?>">
        <input type="hidden" name="LevelID" value="<?php echo $LevelID;?>">
      </form>
    </div>
  </div>
</div>
</body>
</html>
<script  type='text/javascript'>
	//表单提交前验证
		var type = <?php echo $type;?>;		
		$('.btn_green').on('click','',function(){
			var re = /^[0-9]+(.[0-9]{1,2})?$/;
			var Come_Type = $("input[name='Come_Type']:checked").val();
			if (Come_Type != 3) {
                if (Come_Type == 4) {    //手动申请
                    var flag = 0;
                    $('#reverve_field_table input').each(function(){
                        var value = $.trim($(this).val());
                        if (!value) {
                            flag += 1;
                            $(this).css('border-color', 'red');
                        } else {
                            $(this).css('border-color', '#ddd');
                        }
                    });
                    if (flag > 0) {
                        alert('请填写输入信息提示，以便用户填写');
                        return false;
                    }
                } else {
                    if(type == 0){
                        var Price = $("input[name='Price']").val();
                        if(!re.test(Price) || Price < 0){
                            alert('价格必须是数字且大于零!');
                            return false;
                        }
                    }

                    if(type == 1){
                        var Xiaofei_Amount = $("input[name='Xiaofei_Amount']").val();
                        if(!re.test(Xiaofei_Amount) || Xiaofei_Amount < 0){
                            alert('消费额价格必须是数字且大于零!');
                            return false;
                        }
                    }
                }
            }
			$('#level_form').submit();
		});
		
	$("#Present_type").change(function(){
		var Present_type = $("#Present_type").val();
		if(Present_type == 2){
			$("#Prices").empty();
			$("#UpdatePrices").empty();
			$("#Prices").append("<input name='Price' value='<?php echo $rsLevel['Level_LimitValue'];?>' type='text' class='form_input' size='5' <?php if($first['Level_ID']==$LevelID){?><?php }else{?>notnull<?php }?> > 元 赠送 <input type='text' name='Present_Price' value='<?php echo $rsLevel['Level_PresentValue'];?>' class='form_input' size='5' maxlength='10' /> 元<span "+"class='tips'>&nbsp;&nbsp;注：用户购买此级别的价格");
			$("#UpdatePrices").append("<input name='UpdatePrice' value='<?php echo $rsLevel['Level_UpdateType']==0 ? $rsLevel['Level_UpdateValue'] : '';?>' type='text' class='form_input' size='5' > 元 赠送 <input type='text' name='Present_UpdatePrice' value='<?php echo $rsLevel['Level_PresentUpdateValue'];?>' class='form_input' size='5' maxlength='10' /> 元<span class='tips'>&nbsp;&nbsp;注：会员升级到本级别时所需支付的金额");
		}else{
			$("#Prices").empty();
			$("#UpdatePrices").empty();
			$("#Prices").append("<input name='Price' value='<?php echo $rsLevel['Level_LimitValue'];?>' type='text' class='form_input' size='5' <?php if($first['Level_ID']==$LevelID){?><?php }else{?>notnull<?php }?>> 元<span "+"class='tips'>&nbsp;&nbsp;注：用户购买此级别的价格");
			$("#UpdatePrices").append("<input name='UpdatePrice' value='<?php echo $rsLevel['Level_UpdateType']==0 ? $rsLevel['Level_UpdateValue'] : '';?>' type='text' class='form_input' size='5' > 元<span class='tips'>&nbsp;&nbsp;注：会员升级到本级别时所需支付的金额");
		}
	});

	// 表单增加
    function insertRow() {
        var newrow = document.getElementById('reverve_field_table').insertRow(-1);
        newrow.insertCell(0).innerHTML = '<select onChange="JSONType(this);" name="JSON[' + (document.getElementById('reverve_field_table').rows.length - 6) + '][Type]"><option value="text" selected>文本框</option><option value="select">选择框</option></select>：';
        newrow.insertCell(1).innerHTML = '<input type="text" class="form_input" value="" name="JSON[' + (document.getElementById('reverve_field_table').rows.length - 6) + '][Name]" />';
        newrow.insertCell(2).innerHTML = '<input type="text" class="form_input" value="" name="JSON[' + (document.getElementById('reverve_field_table').rows.length - 6) + '][Value]" placeholder="格式：红色|蓝色|黄色" />';
        newrow.insertCell(3).innerHTML = '<a href="javascript:void(0);" onclick="document.getElementById(\'reverve_field_table\').deleteRow(this.parentNode.parentNode.rowIndex);"> <img src="/static/member/images/ico/del.gif" hspace="5" /></a>';
    }
	
	
</script>
