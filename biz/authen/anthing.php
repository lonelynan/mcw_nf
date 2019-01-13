<?php
require_once('../global.php');
$Biz_ID=$_SESSION["BIZ_ID"];
if ($rsBiz['is_agree'] !=1) {
    echo '<script language="javascript">alert("您还没签署入驻协议不能提交资质");history.back();</script>';
    exit;
 }
$BizInfo = $DB->GetRS('biz_apply','*','WHERE Biz_ID = '.$Biz_ID); 
if (!empty($BizInfo)) {
    $step = isset($_GET['step'])?$_GET['step']:'1'; 
    $baseinfo = json_decode($BizInfo['baseinfo'],true);
    $authinfo = json_decode($BizInfo['authinfo'],true);
    $accountinfo = json_decode($BizInfo['accountinfo'],true);

    $authinfo['company_type'] = isset($authinfo['company_type'])?$authinfo['company_type']:2;
   
} else {
    $step = 1; 
}

if ($_POST) {
    $step = isset($_GET['step'])?$_GET['step']:'1';
    if ($step == 2) {
        
        $Data1["authtype"] = $_POST['authtype'];
        $Data1["CreateTime"] = time();
        $Data1["baseinfo"] = json_encode((isset($_POST["basedata"])?$_POST["basedata"]:array()),JSON_UNESCAPED_UNICODE);
        if (empty($Data1["baseinfo"])) {
            echo '<script language="javascript">alert("您有必填项没有填写");history.back();</script>';
            exit;
        }
        $BizInfo = $DB->GetRS('biz_apply','*','WHERE Biz_ID = '.$Biz_ID);
        if (isset($BizInfo['status']) && $BizInfo['status'] == 2) {
            echo '<script language="javascript">alert("您的资质审核已经通过,不可重新提交");window.top.location="/biz/index.php";</script>';
            exit;
        }
        if (!empty($BizInfo)) {
           $flag = $DB->Set('biz_apply',$Data1,'WHERE Biz_ID = '.$Biz_ID); 
        } else {
            $Data1['Biz_ID'] = $Biz_ID;
            $Data1['Users_ID'] = $rsBiz['Users_ID'];
           $flag = $DB->ADD('biz_apply',$Data1);
        }
        $BizInfo = $DB->GetRS('biz_apply','*','WHERE Biz_ID = '.$Biz_ID);
         //print_r($BizInfo); 
    }
    
    if ($step == 3) {
        $Data2["authinfo"] = json_encode((isset($_POST["authdata"])?$_POST["authdata"]:array()),JSON_UNESCAPED_UNICODE);
        if (empty($Data2["authinfo"])) {
            echo '<script language="javascript">alert("您有必填项没有填写");history.back();</script>';
            exit;
        }
        $BizInfo = $DB->GetRS('biz_apply','*','WHERE Biz_ID = '.$Biz_ID);
        if (!empty($BizInfo)) {
           $flag = $DB->Set('biz_apply',$Data2,'WHERE Biz_ID = '.$Biz_ID); 
           if (empty($BizInfo['accountinfo'])) {
                $accountdata = '{"withdraw_type":"1","blan_city":"","blan_name":"","blan_realname":"","blan_card":"","alipay_account":"","alipay_realname":""}';
                if ($flag) {
                   $DB->Set('biz_apply',array('accountinfo'=>$accountdata),'WHERE Biz_ID = '.$Biz_ID); 
                }
           }
          
           
        } else {
            echo '<script language="javascript">alert("修改失败");history.back();</script>';
            exit;
        }
    }
    if ($step == 4) {
        $Data3["accountinfo"] = json_encode((isset($_POST["accountdata"])?$_POST["accountdata"]:array()),JSON_UNESCAPED_UNICODE);
        $Data3["status"] = 1;
        if (empty($Data3["accountinfo"])) {
            echo '<script language="javascript">alert("您有必填项没有填写");history.back();</script>';
            exit;
        }
        $BizInfo = $DB->GetRS('biz_apply','*','WHERE Biz_ID = '.$Biz_ID);
        if (isset($BizInfo['status']) && $BizInfo['status'] == 2) {
            echo '<script language="javascript">alert("您的资质审核已经通过,不可重新提交");window.top.location="/biz/index.php";</script>';
            exit;
        }
        if (isset($BizInfo['is_del']) && $BizInfo['is_del'] == 0) {
            $DB->Set('biz_apply',array('is_del'=>1),'WHERE Biz_ID = '.$Biz_ID); 
        }
        if (!empty($BizInfo)) {
             
           $flag = $DB->Set('biz_apply',$Data3,'WHERE Biz_ID = '.$Biz_ID); 
           if ($flag) {
              $DB->Set('biz',array('is_auth'=>1),'WHERE Biz_ID = '.$Biz_ID); 
           }
        } else {
            echo '<script language="javascript">alert("修改失败");history.back();</script>';
            exit;
        }
    }
    
    
} 


?>
<!DOCTYPE html>
<html lang="cn" class="app fadeInUp animated">
<head>
    <meta charset="utf-8" />
    <title>供货商-管理后台</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
 
    <link rel="stylesheet" href="/static/biz/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="/static/biz/css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="/static/biz/css/style.min.css" type="text/css">
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type="text/javascript" src="/static/biz/js/jsAddress.js"></script>
    <script type='text/javascript' src='/static/biz/js/pintuer.js'></script>
    <style>
       .check-error {
	color: #e33;
} 
    </style>
</head>
<link rel="stylesheet" href="/static/biz/css/formwizard.css" type="text/css">
<link rel="stylesheet" href="/static/biz/css/select2.min.css" type="text/css">
<link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
<script>
KindEditor.ready(function(K) {

	var editor = K.editor({
		uploadJson : '/biz/upload_json.php?TableField=biz',
		fileManagerJson : '/biz/file_manager_json.php',
		showRemote : true,
		allowFileManager : true,
	});
	K('#compay_shenfenimgUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#compay_shenfenimgPath').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#compay_shenfenimgPath').val(url);
					K('#compay_shenfenimgDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
        
        K('#compay_licenseimgUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#compay_licenseimgPath').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#compay_licenseimgPath').val(url);
					K('#compay_licenseimgDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
        
        K('#compay_shuiwuimgUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#compay_shuiwuimgPath').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#compay_shuiwuimgPath').val(url);
					K('#compay_shuiwuimgDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
        K('#per_shenfenimgUpload').click(function(){
		editor.loadPlugin('image', function(){
			editor.plugin.imageDialog({
				imageUrl : K('#per_shenfenimgPath').val(),
				clickFn : function(url, title, width, height, border, align){
					K('#per_shenfenimgPath').val(url);
					K('#per_shenfenimgDetail').html('<img src="'+url+'" />');
					editor.hideDialog();
				}
			});
		});
	});
        
})

</script>
<body>
<section class="vbox">
    <header class="header bg-white b-b b-light">
        <p>资质审核</p>
    </header>
    
    <?php if ($step == 1){ ?>
    <section class="scrollable  wrapper">
        <section class="panel panel-default">
            <div class="panel-body">
                <form class="form-horizontal form-validate" method="post" action="anthing.php?step=2">
                     <div class="step" id="firstStep">
                        <div class="process-bar">
                            <ul>
                                <li class="size5 on ">
                                    <span>基本信息</span>
                                </li>
                                 
                                <li class="size5 nnext ">
                                    <span>资质信息</span>
                                </li>
                                <li class="size5 nnext ">
                                    <span>账户信息</span>
                                </li>
                                <li class="size5 nnext last">
                                    <span>提交审核</span>
                                </li>
                             </ul>
                        </div>
                        <div class="step-forms">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">认证类型：</label>
                                <div class="col-sm-10">
                                     <label class="radio-inline"> <input type="radio" name="authtype" <?php echo ($BizInfo['authtype']==1)?'checked':''?> <?php echo (!$BizInfo)?'checked':''?> onclick="checktype(this)" value="1"/> 企业认证 </label>
                                     <label class="radio-inline"> <input type="radio" name="authtype" <?php echo ($BizInfo['authtype']==2)?'checked':''?> onclick="checktype(this)" value="2" /> 个人认证 </label>
                                </div>
                            </div>
                        <div id="company" style="display:<?php echo ($BizInfo['authtype']==2)?'none':''?>">    
                            <div class="line line-dashed line-lg pull-in compay"></div>
                            <div class="form-group compay">
                                <label class="col-sm-2 control-label">公司名称：</label>
                                <div class="col-sm-5 field">
                                    <input type="text" class="form-control companys" data-rule-required="true" <?php echo ($BizInfo['authtype']==2)?'':'data-validate="required:公司名称必须填写"'?>  value="<?php echo !empty($baseinfo['company_name'])?$baseinfo['company_name']:''?>" name="basedata[company_name]" value=""  />
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline">公司名称与营业执照上一致</span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in compay"></div>
                            <div class="form-group compay">
                                <label class="col-sm-2 control-label">公司主体：</label>
                                <div class="col-sm-5 must field">
                                    <select name="basedata[main_type]" class="form-control companys" data-rule-required="true" <?php echo ($BizInfo['authtype']==2)?'':'data-validate="required:公司主体必须填写"'?> >
                                        <option value="">请选择</option>
                                        <option value="1" <?php echo (isset($baseinfo) && $baseinfo['main_type']==1)?'selected':''?> >大陆企业</option>
                                        <option value="2" <?php echo (isset($baseinfo) && $baseinfo['main_type']==2)?'selected':''?> >境外企业</option>
                                        <option value="3" <?php echo (isset($baseinfo) && $baseinfo['main_type']==3)?'selected':''?> >保税区</option>
                                    </select>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in compay"></div>
                            <div class="form-group compay">
                                <label class="col-sm-2 control-label">公司固话：</label>
                                <div class="col-sm-1 field">
                                    <input type="text" class="form-control companys" <?php echo ($BizInfo['authtype']==2)?'':'data-validate="required:固话必须填写"'?> value="<?php echo !empty($baseinfo['tel'][0])?$baseinfo['tel'][0]:''?>" data-rule-number="true" data-rule-maxlength="4" name="basedata[tel][]" value=""/>
                                </div>
                                <div class="col-sm-4 must company field">
                                    <input type="text" class="form-control companys" <?php echo ($BizInfo['authtype']==2)?'':'data-validate="required:公司固话必须填写"'?> value="<?php echo !empty($baseinfo['tel'][1])?$baseinfo['tel'][1]:''?>" data-rule-phone="true" name="basedata[tel][]" value=""/>
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline">格式如：021-12345678</span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in compay"></div>
                            <div class="form-group compay">
                                <label class="col-sm-2 control-label">企业所在地：</label>
                                <div class="col-sm-10">
                                    <div class="form-inline" data-toggle="location">
                                       
                                        <div class="form-group">
                                            <div class="col-lg-12">
                                                <select class="form-control mn140" id="cmbProvince" data-selected="360000" name="basedata[city][]" data-rule-required="true" data-location="provinces"></select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <div class="col-lg-12">
                                                <select class="form-control mn140" id="cmbCity" data-selected="360100" name="basedata[city][]" data-rule-required="true" data-location="city"></select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <div class="col-lg-12">
                                                <select class="form-control mn140" id="cmbArea" data-selected="360127" name="basedata[city][]" data-rule-required="true" data-location="district"></select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in compay"></div>
                            <div class="form-group compay">
                                <label class="col-sm-2 control-label"></label>
                                <div class="col-sm-5 must field">
                                    <input type="text" placeholder="请输入具体地址" <?php echo ($BizInfo['authtype']==2)?'':'data-validate="required:具体地址必须填写"'?> class="form-control companys" value="<?php echo !empty($baseinfo['address'])?$baseinfo['address']:''?>" data-rule-required="true" name="basedata[address]" value=""/>
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline">与营业执照上一致</span>
                                    </p>
                                </div>
                            </div>
                        </div>    
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">主营商品：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" class="form-control" data-validate="required:主营商品必须填写" data-rule-required="true" value="<?php echo !empty($baseinfo['goods'])?$baseinfo['goods']:''?>" name="basedata[goods]" value=""/>
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline">（多个主营商品以逗号间隔）</span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                            
                            
                            <div class="form-group">
                                <label class="col-sm-2 control-label">联系人：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" data-rule-required="true" data-validate="required:联系人必须填写" class="form-control" value="<?php echo !empty($baseinfo['contacts'])?$baseinfo['contacts']:''?>" name="basedata[contacts]" value=""/>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">手机：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" class="form-control" data-validate="required:手机必须填写,mobile:请输入正确的手机号" data-rule-required="true" value="<?php echo  !empty($baseinfo['mobile'])?$baseinfo['mobile']:''?>" data-rule-Mobile="true" name="basedata[mobile]" value=""/>
                                </div>
                            </div>
                            
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">邮箱地址：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" class="form-control" data-validate="required:邮箱地址必须填写,email:请输入正确的邮箱" value="<?php echo  !empty($baseinfo['email'])?$baseinfo['email']:''?>" data-rule-required="true" data-rule-email="true" name="basedata[email]" value=""/>
                                </div>
                            </div>
                            
                            <div class="line line-dashed line-lg pull-in"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                            <button class="btn btn-primary" type="submit">下一步</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </section>
    <?php }?> 
    
   <?php if ($step == 2){ ?>
    <section class="scrollable  wrapper">
        <section class="panel panel-default">
            <div class="panel-body">
                <form class="form-horizontal form-validate" method="post" action="anthing.php?step=3">
                     <div class="step" id="firstStep">
                        <div class="process-bar">
                            <ul>
                                <li class="size5 next ">
                                    <span>基本信息</span>
                                </li>
                                
                                <li class="size5 on ">
                                    <span>资质信息</span>
                                </li>
                                <li class="size5 nnext ">
                                    <span>账户信息</span>
                                </li>
                                <li class="size5 nnext last">
                                    <span>提交审核</span>
                                </li>
                             </ul>
                        </div>
                         <div class="step-forms">
                    <?php if($BizInfo['authtype'] ==1){?>
                             <div class="form-group">
                                <label class="col-sm-2 control-label">企业类型：</label>
                                <div class="col-sm-5 must field">
                                    <select name="authdata[company_type]" data-validate="required:企业类型必须填写" class="form-control" data-rule-required="true">
                                        <option value="">请选择</option>
                                        <option value="1" <?php echo (isset($authinfo) && $authinfo['company_type']==1)?'selected':''?> >有限责任公司</option>
                                        <option value="2" <?php echo (isset($authinfo) && $authinfo['company_type']==2)?'selected':''?>>农民专业合作社</option>
                                        <option value="3" <?php echo (isset($authinfo) && $authinfo['company_type']==3)?'selected':''?>>中外合资企业</option>
                                        <option value="4" <?php echo (isset($authinfo) && $authinfo['company_type']==4)?'selected':''?>>外国或港澳台地区独资企业</option>
                                     </select>
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">企业住所：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" class="form-control" data-validate="required:企业住所必须填写"  value="<?php echo !empty($authinfo['compay_add'])?$authinfo['compay_add']:''?>" data-rule-required="true" name="authdata[compay_add]" value=""  />
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline">需与营业执照上保持一致</span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">注册资金：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" class="form-control" data-validate="required:注册资金必须填写"  value="<?php echo !empty($authinfo['compay_reg_money'])?$authinfo['compay_reg_money']:''?>" data-rule-required="true" name="authdata[compay_reg_money]" value=""/>
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">营业执照注册号或统一社会信用代码：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" class="form-control" data-validate="required:营业执照注册号或统一社会信用代码必须填写" value="<?php echo !empty($authinfo['compay_license'])?$authinfo['compay_license']:''?>"  data-rule-required="true" name="authdata[compay_license]" value=""/>
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">法人：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" class="form-control" data-validate="required:法人必须填写" value="<?php echo !empty($authinfo['compay_user'])?$authinfo['compay_user']:''?>" data-rule-required="true" name="authdata[compay_user]" value=""/>
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">法人身份证扫描件：</label>
                                <div class="col-sm-5 must field">
                                    <input type="hidden" id="compay_shenfenimgPath" value="<?php echo !empty($authinfo['compay_shenfenimg'])?$authinfo['compay_shenfenimg']:''?>" data-validate="required:法人身份证扫描件必须填写" name="authdata[compay_shenfenimg]" />
                                    <input type="button" id="compay_shenfenimgUpload" value="添加扫描件" style="width:80px;" />
                                    <div class="img" id="compay_shenfenimgDetail" style="margin-top:8px">
                                    <?php 
                                            if(!empty($authinfo["compay_shenfenimg"])){
                                                    echo '<img src="'.$authinfo["compay_shenfenimg"].'" />';
                                    }
                                    ?>
                                    </div>
                                </div>
                                
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div>
                            

                            
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">营业执照影印件：</label>
                                <div class="col-sm-5 must field">
                                    <input type="hidden" id="compay_licenseimgPath" data-validate="required:营业执照影印件必须填写" name="authdata[compay_licenseimg]" value="<?php echo !empty($authinfo['compay_licenseimg'])?$authinfo['compay_licenseimg']:''?>" />
                                    <input type="button" id="compay_licenseimgUpload" value="添加扫描件" style="width:80px;" />
                                    <div class="img" id="compay_licenseimgDetail" style="margin-top:8px">
                                    <?php 
                                            if(!empty($authinfo["compay_licenseimg"])){
                                                    echo '<img src="'.$authinfo["compay_licenseimg"].'" />';
                                    }
                                    ?>
                                    </div>    
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div> 
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">税务登记证扫描件：</label>
                                <div class="col-sm-5 must field">
                                    <input type="hidden" id="compay_shuiwuimgPath" data-validate="required:税务登记证扫描件必须填写" name="authdata[compay_shuiwuimg]" value="<?php echo !empty($authinfo['compay_shuiwuimg'])?$authinfo['compay_shuiwuimg']:''?>" />
                                    <input type="button" id="compay_shuiwuimgUpload" value="添加扫描件" style="width:80px;" />
                                    <div class="img" id="compay_shuiwuimgDetail" style="margin-top:8px">
                                    <?php 
                                            if(!empty($authinfo["compay_shuiwuimg"])){
                                                    echo '<img src="'.$authinfo["compay_shuiwuimg"].'" />';
                                    }
                                    ?>
                                    </div>   
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div> 
                        
                    <?php }else{?> 
                            <div class="form-group">
                                <label class="col-sm-2 control-label">真实姓名：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" class="form-control" data-validate="required:真是姓名必须填写" data-rule-required="true" name="authdata[per_realname]" value="<?php echo !empty($authinfo['per_realname'])?$authinfo['per_realname']:''?>"  />
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline">需与身份证上保持一致</span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">身份证号码：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" class="form-control" data-validate="required:需与身份证上保持一致必须填写" data-rule-required="true" name="authdata[per_shenfenid]" value="<?php echo !empty($authinfo['per_shenfenid'])?$authinfo['per_shenfenid']:''?>"  />
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline">需与身份证上保持一致</span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                            
                            
                            <div class="form-group">
                                <label class="col-sm-2 control-label">身份证扫描件：</label>
                                <div class="col-sm-5 must field">
                                 
                                    <input type="hidden" id="per_shenfenimgPath" data-validate="required:身份证扫描件必须填写" name="authdata[per_shenfenimg]" value="<?php echo !empty($authinfo['per_shenfenimg'])?$authinfo['per_shenfenimg']:''?>" />
                                    <input type="button" id="per_shenfenimgUpload" value="添加扫描件" style="width:80px;" />
                                    <div class="img" id="per_shenfenimgDetail" style="margin-top:8px">
                                    <?php 
                                            if(!empty($authinfo["per_shenfenimg"])){
                                                    echo '<img src="'.$authinfo["per_shenfenimg"].'" />';
                                    }
                                    ?>
                                    </div>   
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div>
                             
                            <div class="line line-dashed line-lg pull-in"></div>
                            
                        
                    <?php }?>     
                        
                            
                          
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                             <a href="anthing.php?step=1"><button class="btn btn-primary" type="button">上一步</button></a>
                            <button class="btn btn-primary" type="submit">下一步</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </section>
      <?php }?> 
    
    <?php if ($step == 3){ 
        $BizInfo = $DB->GetRS('biz_apply','*','WHERE Biz_ID = '.$Biz_ID); 
        $accountinfo = !empty($BizInfo['accountinfo'])?json_decode($BizInfo['accountinfo'],true):array();
    ?>
    <section class="scrollable  wrapper">
        <section class="panel panel-default">
            <div class="panel-body">
                <form class="form-horizontal form-validate" method="post" action="anthing.php?step=4">
                     <div class="step" id="firstStep">
                        <div class="process-bar">
                            <ul>
                                <li class="size5 next ">
                                    <span>基本信息</span>
                                </li>
                                
                                <li class="size5 next ">
                                    <span>资质信息</span>
                                </li>
                                <li class="size5 on ">
                                    <span>账户信息</span>
                                </li>
                                <li class="size5 nnext last">
                                    <span>提交审核</span>
                                </li>
                             </ul>
                        </div>
                        <div class="step-forms">
                            <input type="hidden" name="supplier_id" value="62789"/>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">提现方式：</label>
                                <div class="col-sm-5 must field">
                                    <select id="zffs"  onChange="paychange()" name="accountdata[withdraw_type]" class="form-control ui-wizard-content ui-helper-reset ui-state-default" data-rule-required="true" aria-required="true" style="width:164px;">
                                        <option value="1" <?php echo ($accountinfo['withdraw_type'] == 1)?'selected':''?>>银行卡</option>
                                        <option value="2"  <?php echo (isset($accountinfo) && $accountinfo['withdraw_type']==2)?'selected':''?>>支付宝</option>
                                    </select>
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in blan"></div>
                            
                        <div id="blan" style="display:<?php echo (isset($accountinfo) && $accountinfo['withdraw_type']==2)?'none':''?>">    
                            <div class="form-group blan">
                                <label class="col-sm-2 control-label">开户城市：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" <?php echo (isset($accountinfo) && $accountinfo['withdraw_type']==1)?'data-validate="required:开户城市必须填写"':''?> class="form-control blans" value="<?php echo !empty($accountinfo['blan_city'])?$accountinfo['blan_city']:''?>" data-rule-required="true" name="accountdata[blan_city]" value=""/>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in blan"></div>
                            <div class="form-group blan">
                                <label class="col-sm-2 control-label">开户银行：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" <?php echo (isset($accountinfo) && $accountinfo['withdraw_type']==1)?'data-validate="required:开户银行必须填写"':''?>  value="<?php echo !empty($accountinfo['blan_name'])?$accountinfo['blan_name']:''?>" class="form-control blans" data-rule-required="true" name="accountdata[blan_name]" value=""/>
                                </div>
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in blan"></div>
                            <div class="form-group blan">
                                <label class="col-sm-2 control-label ">开户姓名：</label>
                                <div class="col-sm-5 field">
                                     <input type="text" <?php echo (isset($accountinfo) && $accountinfo['withdraw_type']==1)?'data-validate="required:开户姓名必须填写"':''?> value="<?php echo !empty($accountinfo['blan_realname'])?$accountinfo['blan_realname']:''?>" class="form-control blans" data-rule-required="true" name="accountdata[blan_realname]" value=""/>      
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in blan"></div>
                            <div class="form-group blan">
                                <label class="col-sm-2 control-label">银行卡号：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text" <?php echo (isset($accountinfo) && $accountinfo['withdraw_type']==1)?'data-validate="required:银行卡号必须填写"':''?> data-rule-required="true" value="<?php echo !empty($accountinfo['blan_card'])?$accountinfo['blan_card']:''?>" class="form-control blans" name="accountdata[blan_card]" value=""/>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                        </div>    
                           
                            
    <div id="alipay" style="display:<?php echo (empty($accountinfo['withdraw_type']) || $accountinfo['withdraw_type']==1)?'none':''?>">        
                            <div class="form-group alipay">
                                <label class="col-sm-2 control-label">支付宝账号：</label>
                                <div class="col-sm-5 must field">
                                    <input type="text"  <?php echo (isset($accountinfo) && $accountinfo['withdraw_type']==2)?'data-validate="required:支付宝账号必须填写"':''?>  class="form-control alipays" value="<?php echo !empty($accountinfo['alipay_account'])?$accountinfo['alipay_account']:''?>" name="accountdata[alipay_account]" />
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in alipay"></div>
                            <div class="form-group alipay">
                                <label class="col-sm-2 control-label">姓名：</label>
                                <div class="col-sm-5 field">
                                    <input type="text" <?php echo (isset($accountinfo) && $accountinfo['withdraw_type']==2)?'data-validate="required:姓名必须填写"':''?> class="form-control alipays" value="<?php echo !empty($accountinfo['alipay_realname'])?$accountinfo['alipay_realname']:''?>" name="accountdata[alipay_realname]" />
                                </div>
                             
                                <div class="col-sm-5">
                                    <p class="form-control-static">
                                        <span class="help-inline"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="line line-dashed line-lg pull-in"></div>
                         </div> 
                            
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                            <a href="anthing.php?step=2"><button class="btn btn-primary" type="button">上一步</button></a>
                            <button class="btn btn-primary" type="submit">下一步</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </section>
    <?php }?> 
    
    <?php if ($step == 4){ ?>
    <section class="scrollable  wrapper">
        <section class="panel panel-default">
            <div class="panel-body">
                <form class="form-horizontal form-validate" method="post" action="anthing.php?step=5">
                     <div class="step" id="firstStep">
                        <div class="process-bar">
                            <ul>
                                <li class="size5 next ">
                                    <span>基本信息</span>
                                </li>
                                
                                <li class="size5 next ">
                                    <span>资质信息</span>
                                </li>
                                <li class="size5  ">
                                    <span>账户信息</span>
                                </li>
                                <li class="size5 on last">
                                    <span>提交审核</span>
                                </li>
                             </ul>
                        </div>
                        
                        <div class="step-forms">
                            <div class="form-group" style="margin-top:100px;margin-bottom: 100px">
                                <div class="col-sm-2 col-sm-offset-2 text-right m-t"> <i class="fa fa-check text-primary fa-4x"></i>
                                </div>
                                <div class="col-sm-8">
                                    <h5 class="fa-2x">辛苦啦，您已完成所有信息填写~</h5>
                                    <h5 class="fa-2x">我们将会在3个工作日内完成您信息的审核！</h5>
                                </div>
                                <div class="col-sm-8 col-sm-offset-4 m-t-lg">
                                    <a href="index.php" class="btn btn-primary" />确定</a>
                                </div>
                            </div>
                        </div>
                    

                         
                    </div>
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                           
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </section>
    <?php }?> 
    
</section>
    <script>
    function checktype (the) {
         
        if (the.value == 1){
            $('#company').show();
            $('.companys').attr('data-validate',"required:该项必须填写");
        } else {
            $('#company').hide();
            $('.companys').attr('data-validate','false');
            
        }
    }
    function paychange () {
        var changeValue = $("#zffs").val(); 
        if (changeValue ==1) {
            $('#blan').show();
            $('#alipay').hide();
            $('.blans').attr('data-validate',"required:该项必须填写");
            $('.alipays').removeAttr('data-validate');
            $('.alipay').removeClass('check-error');
             
           
        }else{
            $('#blan').hide();
            $('#alipay').show();
            $('.alipays').attr('data-validate',"required:该项必须填写");
            $('.blans').removeAttr('data-validate');
            $('.blan').removeClass('check-error');
        }
    }
    addressInit('cmbProvince', 'cmbCity', 'cmbArea', "<?php echo !empty($baseinfo['city'][0])?$baseinfo['city'][0]:''?>", "<?php echo !empty($baseinfo['city'][1])?$baseinfo['city'][1]:''?>", "<?php echo !empty($baseinfo['city'][2])?$baseinfo['city'][2]:''?>");
    </script>
</body>
</html>