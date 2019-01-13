<?php
require_once (CMS_ROOT . '/include/update/common.php');
$debug = isset($_GET['debug']) && $_GET['debug']?true:false;

if (IS_POST) {
    $post = $_POST;
    $data = [];
    $return_uri = $_SERVER['HTTP_REFERER'];
	$data['Active_Name'] = $post['Active_Name'];
    $data['imgurl'] = $post['imgurl'] ? $post['imgurl'] : '';
	$data['Type_ID'] = intval($post['ActiveType']);
	$data['starttime'] = intval($post['ActiveType']) == 5 ? strtotime($post['starttime']) : 0;
	$data['stoptime'] = intval($post['stoptime']) ? strtotime($post['stoptime']) : 0;
	$data['Status'] = $post['Status'];
	/**
	 * 组装JSON字符串（兼容更多属性）
	 */
	$json_arr = [];
	if ($data['Type_ID'] == 5) {
		if($post['PriceDiscount'] < 1){
			showMsg("价格折扣不能为0", $return_uri,1);
		}
		//价格折扣
		$json_arr['PriceDiscount'] = $post['PriceDiscount'];
        if ($data['starttime'] >= $data['stoptime']) {
            showMsg("开始时间不能大于结束时间", $return_uri, 1);
        }
	}

    $type_id = $post['typeid'];
	$time = strtotime(date("Y-m-d",time()));
	if($post['ActiveType']!=5){
		$actifeFlag = $DB->GetRs("active", "*", "WHERE Users_ID='{$UsersID}' AND Type_ID={$post['ActiveType']} and Active_ID<>" . $type_id);
		if (!empty($actifeFlag)) {
			showMsg("只能发起一次相同类型的活动", $return_uri,1);
		}
	}else{
		$actifeFlag = $DB->GetRs("active", "*", "WHERE Users_ID='{$UsersID}' AND Type_ID={$post['ActiveType']} AND Status = 1 AND (stoptime>={$data['starttime']}) and Active_ID<>{$type_id}");
		if (!empty($actifeFlag)) {
			showMsg("同一时间段只能发起一次相同类型的活动", $return_uri,1);
		}
	}

	$data['ext_json'] = json_encode($json_arr,JSON_UNESCAPED_UNICODE);


    if (empty($post['Active_Name']) || ! $post['Active_Name']) {
        showMsg("活动名称不能为空!", $return_uri, 2);
    }

    $data['Status'] = $post['Status'];
    $flag = $DB->Set("active", $data, "WHERE Users_ID='{$UsersID}' AND Active_ID='{$type_id}' ");
    if (false === $flag) {
        showMsg("编辑失败", $return_uri, 1);
    } else {
        showMsg("编辑成功", "active_list.php", 1);
    }
} else {
    $type_id = isset($_GET['typeid']) ? intval($_GET['typeid']) : 0 ;
	if ($type_id < 1) {
		showMsg("参数不正确!", $return_uri, 2);
	}
    $rsActive = $DB->GetRs("active", "*","WHERE Users_ID='{$UsersID}' AND Active_ID='{$type_id}' ");
	$json = json_decode($rsActive['ext_json'],true);
	$rsActive['PriceDiscount'] = isset($json['PriceDiscount']) ? $json['PriceDiscount'] :'';

    if (! $rsActive) {
        showMsg("不正确的参数传递", "active_list.php", 1);
    }
}
$jcurid = 2;
$subidst = 11;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<title>修改活动</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet'
	type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/js/jquery.validate.min.js'></script>
<script type='text/javascript' src='/static/js/jquery.validate.zh_cn.js'></script>
<link rel="stylesheet"
	href="/third_party/kindeditor/themes/default/default.css" />
<link href='/static/member/css/shop.css' rel='stylesheet'
	type='text/css' />
<link href='/static/member/css/user.css' rel='stylesheet'
	type='text/css' />
<link href='/static/js/plugin/daterangepicker/daterangepicker.css'
	rel='stylesheet' type='text/css' />
<script type='text/javascript'
	src='/static/js/plugin/daterangepicker/daterangepicker.js'></script>
<script src="/static/js/plugin/laydate/laydate.js"></script>
<script type='text/javascript' src='/static/member/js/active.js'></script>
    <link rel="stylesheet"
          href="/third_party/kindeditor/themes/default/default.css" />
    <script type='text/javascript'
            src="/third_party/kindeditor/kindeditor-min.js"></script>
    <script type='text/javascript'
            src="/third_party/kindeditor/lang/zh_CN.js"></script>
<style>
#PicDetail img {
	width: 100px;
}

.r_con_form .rows .input .error {
	color: #f00;
}

.r_con_form .rows .input .ness {
	color: #f00;
}
.r_con_form .rows .input .ness { color:#f00; margin-left:10px; font-size:13px; } 
.r_con_form .rows .input .error { color:#f00; }
.fc_red { margin-left:20px;}
.fc_red_1 {color:#f00;margin-left: 20px;}
.ele {  }
label {padding-left:5px;}
</style>

</head>
<script>
    KindEditor.ready(function(K) {
        var editor = K.editor({
            uploadJson: '/member/upload_json.php?TableField=category_add',
            fileManagerJson: '/member/file_manager_json.php',
            showRemote: true,
            allowFileManager: true
        });
        K('#ImgUpload').click(function () {
            editor.loadPlugin('image', function () {
                editor.plugin.imageDialog({
                    clickFn: function (url, title, width, height, border, align) {
                        K('#ImgPath').val(url);
                        K('#ImgDetail').html('<img src="' + url + '" style="width:100px;height:100px"/>');
                        editor.hideDialog();
                    }
                });
            });
        });
    })
</script>
<body>
	<div id="iframe_page">
		<div class="iframe_content">
		<?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/active/active_menubar.php');?>
			<div id="products" class="r_con_wrap">
				<form id="product_add_form" class="r_con_form skipForm"
					method="post" action="active_edit.php">
					<input type="hidden" name="typeid" value="<?=$type_id ?>" />
					<div class="rows">
						<label>活动名称</label> <span class="input price"> <input type="text"
							name="Active_Name" value="<?=$rsActive['Active_Name']?>"
							class="form_input" size="5"
							style="width: 200px; float: left; margin-right: 40px;"
							maxlength="10" notnull /> <span class="ness">*</span> <span
							class="tips"></span>
						</span>
						<div class="clear"></div>
					</div>


                    <div class="rows">
                        <label>分类图片</label>
                        <span class="input">
							<input id="ImgUpload" name="ImgUpload" type="button" class="btn_green" value="上传图片" style=" margin-left: 85px;float: left;" notnull /> <span class="ness">建议尺寸：750*240像素*</span>
							<span class="tips"></span>
                            <div style="width: 315px; border: 1px solid #ddd; border-radius: 5px;min-height: 100px;background: #fff; text-align: center;margin-top: 15px;" class="img" id="ImgDetail"><img style="width:80%;" width="100" src="<?php echo empty($rsActive["imgurl"])?"/static/member/images/shop/nopic.jpg":$rsActive["imgurl"]; ?>" /></div>
						</span>

                        <input type="hidden" id="ImgPath" name="imgurl" value="<?php echo empty($rsActive["imgurl"])?"/static/member/images/shop/nopic.jpg":$rsActive["imgurl"]; ?>" />
                        <div classs="pro-list-type"> </div>
                        <div class="clear"></div>
                    </div>

                    <div class="rows">
                        <label>活动类型</label> <span class="input"> <select name="ActiveType" onChange="OnlyImg(this)">
                    <?php foreach ($typelist as $k => $v) {?>
                        <option value="<?=$v['Type_ID'] ?>"
                            <?=$rsActive['Type_ID']==$v['Type_ID']?'selected':'' ?>><?=$v['Type_Name'] ?></option>
                    <?php }?>
                </select>
                            <span id="active_intro" class="fc_red_1">*</span>
						</span>
                        <div class="clear"></div>
                    </div>

                    <div id="Others_Actives" style="<?=$rsActive['Type_ID'] == 5 ? 'display:block' : 'display:none'?>">
					<div class="rows">
						<label>价格折扣</label> <span class="input">
							<input type="number" name="PriceDiscount" value="<?=$rsActive['PriceDiscount'] ?>" class="form_input" size="5" maxlength="2" max="100" /> %<span class="fc_red_1">*</span>（活动价必须低于现价的百分之多少，如：70% 表示打7折 ）&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font class="fc_red"></font> </span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>参与活动时间</label> <span class="input time">
						<div class="l">
							<div class="form-group">
								<div class="input-group" id="reportrange" style="width: auto">
									<input placeholder="开始时间" class="laydate-icon"
										   name="starttime" value="<?=date("Y-m-d H:i:s",$rsActive['starttime'])?>"
										   onclick="laydate({format: 'YYYY-MM-DD hh:mm:ss',istime: true})">- <input placeholder="截止时间"
																													class="laydate-icon" name="stoptime" value="<?=date("Y-m-d H:i:s",$rsActive['stoptime'])?>"
																													onclick="laydate({format: 'YYYY-MM-DD hh:mm:ss',istime: true})"><span class="ness">*</span>
								</div>
							</div>
						</div>
						<div class="clear"></div>
					</div>
                    </div>




					<div class="rows">
						<label>活动状态</label> <span class="input" style="font-size: 12px;">
              <?php if($rsActive['Status']==1){ ?>
                  <input type="radio" id="status_0" value="0"
							name="Status" /><label for="status_0"> 关闭 </label>&nbsp;&nbsp; <input
							type="radio" id="status_1" value="1" name="Status" checked /><label
							for="status_1"> 开启 </label>
              <?php }else{ ?>
                  <input type="radio" id="status_0" value="0"
							name="Status" checked /><label for="status_0"> 关闭 </label>&nbsp;&nbsp;
							<input type="radio" id="status_1" value="1" name="Status" /><label
							for="status_1"> 开启 </label>
              <?php }?>
              </span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label></label> <span class="input"> <input type="submit"
							class="btn_green" name="submit" value="提交保存" /></span>
						<div class="clear"></div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script>
		//日期时间范围
		var now_time = (new Date()).getTime();
		laydate.render({
			elem: '#starttime'
			,type: 'datetime'
		});

		laydate.render({
			elem: '#stoptime'
			,type: 'datetime'
		});

        // 判断活动类型
        function OnlyImg(that){
            //获取活动类型
            if(that.options[that.selectedIndex].value == 5){
                $("#Others_Actives").css("display","block")
            }else {
                $("#Others_Actives").css("display","none")
            }

            if(that.options[that.selectedIndex].value == 6){
                $("#active_intro").html("热门推荐展示推荐以及热门产品*")
            }else if(that.options[that.selectedIndex].value == 7){
                $("#active_intro").html("9.9特价展示产品为8-12元*")
            }else if(that.options[that.selectedIndex].value == 8){
                $("#active_intro").html("超级团条件为至少五个人，至少五折*")
            }else if (that.options[that.selectedIndex].value == 5) {
                $("#active_intro").html("*")
            }

        }
	</script>
</body>
</html>
