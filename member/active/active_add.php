<?php
require_once (CMS_ROOT . '/include/update/common.php');
/**
 * Ajax获取活动是否参加过
 */

$Type_ID = 0;
if (IS_POST) {
    $post = $_POST;


    $data = [];
    $return_uri = "javascript:history.go(-1);";
    
    $data['Users_ID'] = $UsersID;
    $data['Active_Name'] = $post['Active_Name'];
    $data['imgurl'] = $post['imgurl'] ? $post['imgurl'] : '';
    $data['Type_ID'] = intval($post['ActiveType']);
    $data['starttime'] = intval($post['ActiveType']) == 5 ? strtotime($post['starttime']) : 0;
    $data['stoptime'] = intval($post['ActiveType']) == 5 ? strtotime($post['stoptime']) : 0;
    $data['Status'] = $post['Status'];
    $data['addtime'] = time();
    /**
     * 组装JSON字符串（兼容更多属性）
     */
    $json_arr = [];
    $time = strtotime(date("Y-m-d",time()));
    if ($data['Type_ID'] == 5) {
        if($post['PriceDiscount'] < 1){
            showMsg("价格折扣不能为0", $return_uri,1);
        }
        //价格折扣
        $json_arr['PriceDiscount'] = $post['PriceDiscount'];

        if($data['starttime']<$time){
            showMsg("开始时间不能小于当前时间", $return_uri,1);
        }
        if($data['stoptime']<$time){
            showMsg("结束时间不能小于当前时间", $return_uri,1);
        }
        if ($data['starttime'] > $data['stoptime']) {
            showMsg("开始时间不能大于结束时间", $return_uri,1);
        }

    }
    $data['ext_json'] = json_encode($json_arr,JSON_UNESCAPED_UNICODE);


    if (empty($post['Active_Name']) || ! $post['Active_Name']) {
        showMsg("活动名称不能为空!", $return_uri,1);
    }
    $rsActive = $DB->GetRs("active", "*", "WHERE Users_ID='{$UsersID}' AND Active_Name='{$post['Active_Name']}' ");
    if (!empty($rsActive)) {
        showMsg("活动名称已经存在", $return_uri,1);
    }
	if($post['ActiveType']!=5){
		$actifeFlag = $DB->GetRs("active", "*", "WHERE Users_ID='{$UsersID}' AND Type_ID={$post['ActiveType']}");
		if (!empty($actifeFlag)) {
			showMsg("只能发起一次相同类型的活动", $return_uri,1);
		}
	}else{
		$actifeFlag = $DB->GetRs("active", "*", "WHERE Users_ID='{$UsersID}' AND Type_ID={$post['ActiveType']} AND Status = 1 AND ((starttime<{$time} AND stoptime>{$time}) OR stoptime>={$data['starttime']})");
		if (!empty($actifeFlag)) {
			showMsg("同一时间段只能发起一次相同类型的活动", $return_uri,1);
		}
	}
    $flag = $DB->Add("active", $data);
    if (true == $flag) {
        showMsg("添加成功", "active_list.php",1);
    } else {
        showMsg("添加失败", $return_uri,1);
    }
} else {
    $Starttime = strtotime(date("Y-m-d") . " 00:00:00");
    $Stoptime = strtotime(date("Y-m-d") . " 23:59:59") + 3 * 86400;
    $Starttime = date('Y-m-d', $Starttime);
    $Stoptime = date('Y-m-d', $Stoptime);
}
$jcurid = 2;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<title>添加活动</title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/js/jquery.validate.min.js'></script>
<script type='text/javascript' src='/static/js/jquery.validate.zh_cn.js'></script>
<link rel="stylesheet"
	href="/third_party/kindeditor/themes/default/default.css" />
<script type='text/javascript'
	src="/third_party/kindeditor/kindeditor-min.js"></script>
<script type='text/javascript'
	src="/third_party/kindeditor/lang/zh_CN.js"></script>

<link href='/static/js/plugin/daterangepicker/daterangepicker.css'
	rel='stylesheet' type='text/css' />
<script type='text/javascript'
	src='/static/js/plugin/daterangepicker/daterangepicker.js'></script>
<script src="/static/js/plugin/laydate/laydate.js"></script>
<script type='text/javascript' src='/static/member/js/active.js'></script>
<style>
#PicDetail img {
	width: 100px;
}

.r_con_form .rows .input .error {
	color: #f00;
}
.r_con_form .rows .input .ness { color:#f00; margin-left:10px; font-size:13px; } 
.r_con_form .rows .input .error { color:#f00; }
.fc_red { margin-left:20px;
    }
.fc_red_1 {color:#f00;margin-left: 20px;}
.ele {  }
label {padding-left:5px;}
</style>
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

</head>
<body>
	<div id="iframe_page">
		<div class="iframe_content">
		<?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/active/active_menubar.php');?>
			<div id="products" class="r_con_wrap">
				<form id="product_add_form" class="r_con_form skipForm"
					method="post" action="active_add.php">
					<div class="rows">
						<label>活动名称</label>
						<span class="input price">
							<input type="text" name="Active_Name" value="" class="form_input" size="5" style="width: 400px; float: left;" maxlength="10" notnull /> <span class="ness">*</span>
							<span class="tips"></span>
						</span>
						<div class="clear"></div>
					</div>


                    <div class="rows">
                        <label>分类图片</label>
                        <span class="input">
							<input id="ImgUpload" name="ImgUpload" type="button" class="btn_green" value="上传图片" style=" margin-left: 85px;float: left;" notnull /> <span class="ness">建议尺寸：750*240像素*</span>
							<span class="tips"></span>
                            <div style="width: 315px; border: 1px solid #ddd; border-radius: 5px;min-height: 100px;background: #fff; text-align: center;margin-top: 15px;" class="img" id="ImgDetail"><img width="100" src="/static/member/images/shop/nopic.jpg" /></div>
						</span>

                        <input type="hidden" id="ImgPath" name="imgurl" value="" />
                        <div classs="pro-list-type"> </div>
                        <div class="clear"></div>
                    </div>


					<div class="rows">
						<label>活动类型</label> <span class="input">
						<select name="ActiveType" onChange="OnlyImg(this)">
                             <?php foreach ($typelist as $k => $v) {?>
                             <option value="<?=$v['Type_ID'] ?>"
										<?=$k==0?'selected':'' ?>><?=$v['Type_Name'] ?></option>
                             <?php }?>
                        </select>
                            <span id="active_intro" class="fc_red_1">*</span>
                        <span class="tips" /></span>
						<div class="clear"></div>
					</div>

                    <div id="Others_Actives" style="display: block;">
					<div class="rows">
							<label>价格折扣</label> <span class="input"> <input type="number"
								name="PriceDiscount" value="" class="form_input" size="5" maxlength="2" max="100" /> %<span class="fc_red_1">*</span>（活动价必须低于现价的百分之多少，如：70% 表示打7折 ）&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font class="fc_red"></font> </span>
							<div class="clear"></div>
					</div>
                    <div class="rows">
                        <label>参与活动时间</label> <span class="input time">
						<div class="l">
                            <div class="form-group">
                                <div class="input-group" id="reportrange" style="width: auto">
                                    <input placeholder="开始时间" class="laydate-icon"
                                           name="starttime" value="<?=$Starttime ?>"
                                           onclick="laydate({format: 'YYYY-MM-DD hh:mm:ss',istime: true})">- <input placeholder="截止时间"class="laydate-icon" name="stoptime"value="<?=$Stoptime ?>"onclick="laydate({format: 'YYYY-MM-DD hh:mm:ss',istime: true})"><span class="ness">*</span>
                                </div>
                            </div>
                        </div>
						<div class="clear"></div>
                    </div>



					<div class="rows">
						<label>活动状态</label> <span class="input" style="font-size: 12px;">
							<input type="radio" id="status_0" value="0" name="Status" /><label
							for="status_0"> 关闭 </label>&nbsp;&nbsp; <input type="radio"
							id="status_1" value="1" name="Status" checked /><label
							for="status_1"> 开启 </label>
						</span>
						<div class="clear"></div>
					</div>
                    </div>

					<div class="rows">
						<label></label> <span class="input"> <input type="submit"
							class="btn_green" name="submit" value="提交保存" />
							<div class="clear"></div>
					</div>
				    <input type="hidden" name="isactive" value=""/>
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
        ,min: now_time
    });

    laydate.render({
        elem: '#stoptime'
        ,type: 'datetime'
        ,min: now_time
    });

    /**
     * 检测日期范围是否正确
     *
     */
    function check(){
        var first_time = $('#starttime').val();
        var second_time = $('#stoptime').val();
        var first = new Date(first_time).getTime()/1000;
        var second = new Date(second_time).getTime()/1000;
        if (!first) {
            alert('请选择活动开始日期');
            return false;
        }
        if (!second) {
            alert('请选择活动结果日期');
            return false;
        }
        if (first < second) {
            return true;
        } else {
            alert('日期选择不正确');
            return false;
        }
    }



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
