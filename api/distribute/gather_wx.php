<?php
require_once('global.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/include/library/Gather.class.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/support/sysurl_helpers.php');
$base_url = base_url();
$base_urls = substr($base_url, 0, -1);
if ($_POST) {
    $wx_url = htmlspecialchars($_POST['wx_url']);
    $link_type = (int)$_POST['link_type'];
    $link_url = htmlspecialchars($_POST['link_url']);

    $name = htmlspecialchars($_POST['name']);
    $mobile = preg_replace('/[^\d]*/', '', $_POST['mobile']);
    $qq = preg_replace('/[^\d]*/', '', $_POST['qq']);
    $email = htmlspecialchars($_POST['email']);


    if (empty($wx_url) || !preg_match('/mp.weixin.qq.com/', $wx_url)) {
        echo json_encode(['errcode' => 404, 'msg' => '地址不合法,必须为微信的文章地址!']);exit;
    }
    if (empty($link_url) || !isset($link_url)) {
        echo json_encode(['errcode' => 404, 'msg' => '必须选择链接地址!']);
        exit;
    }
    if (empty($name)) {
        echo json_encode(['errcode' => 404, 'msg' => '请填写姓名!']);
        exit;
    }
    if (empty($mobile) || strlen($mobile) != 11) {
        echo json_encode(['errcode' => 404, 'msg' => '请填写合法手机号码!']);
        exit;
    }
    if (!empty($qq) && strlen($qq) < 5) {
        echo json_encode(['errcode' => 404, 'msg' => '请填写合法QQ号码!']);
        exit;
    }
    if (!empty($email) && !preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/', $email)) {
        echo json_encode(['errcode' => 404, 'msg' => '请填写合法邮箱!']);
        exit;
    }

    $gatherObj = new Gather($wx_url, '../../uploadfiles/' . $UsersID . '/wx_download');
    $output = $gatherObj->fetch();

    $time = time();
    $insData = [
        'usersid' => $UsersID,
        'userid' => $rsAccount['User_ID'],
        'dis_id' => $rsAccount['Account_ID'],
        'wx_url' => $wx_url,
        'wx_title' => $output['title'],
        'link_type' => $link_type,
        'link_url' => $link_url,
        'name' => $name,
        'mobile' => $mobile,
        'qq' => $qq,
        'email' => $email,
        'created_at' => $time,
        'updated_at' => $time
    ];

    $insFlag = $DB->Add('pop_wx_content', $insData);
    $insID = $DB->insert_id();

    if ($insFlag) {
        echo json_encode(['errcode' => 0, 'url' => '/api/shop/gather_content.php?UsersID=' . $UsersID . '&content_id=' . $insID . '&OwnerID=' . $rsAccount['User_ID']]);
        exit;
    }

} else {
    $sys_material = get_sys_material($DB,$UsersID,1);//系统图文
    $diy_material = get_sys_material($DB,$UsersID,0);//自定义图文

    foreach ($sys_material as $k=>$v) {
        $Material_Arr = json_decode($v['Material_Json'], true);
        $sys_material[$k]['real_url'] = $base_urls . $Material_Arr['Url'];
    }

    foreach ($diy_material as $key=>$val) {
        $Material_Arr_Diy = json_decode($val['Material_Json'], true);
		$Material_Arr_Diy_Url = isset($Material_Arr_Diy['Url']) ? $Material_Arr_Diy['Url'] : '';
        if(stripos($Material_Arr_Diy_Url, 'http://') === false){
			$diy_material[$key]['real_url'] = $base_urls . $Material_Arr_Diy_Url;
		} else {
			$diy_material[$key]['real_url'] = $Material_Arr_Diy_Url;
		}
    }
}


?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>推广助手</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="apple-touch-fullscreen" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no" />
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/layer_mobile/layer.js'></script>
</head>
<style>

    *{margin:0;padding:0;border:0;outline:0;box-sizing: border-box;background-repeat: no-repeat;background-position: center center;}
    ul, li, dt, dd, ol, dl{list-style-type:none;}
    a{color:#666; text-decoration:none;}
    a:hover{color: #006fbb;text-decoration:none;}
    input, textarea, button, a{ -webkit-tap-highlight-color:rgba(0,0,0,0); }
    .clear{clear:both;}
    img{width: 100%;border:none;display: block;}
    em{font-style: normal;}
    .left{float:left;}
    .right{float:right;}
    html{overflow-y: scroll;}
    body{background-color:#fff;-webkit-tap-highlight-color:transparent;font-family:"Microsoft YaHei"; font-size: 14px;}
    .w{width:100%; margin:0 auto;overflow: hidden}
    input[type=button], input[type=submit], input[type=file], button { cursor: pointer; -webkit-appearance: none;}


    .title_tg{margin: 0 15px; overflow: hidden;font-weight: bold;font-size: 18px; color: #333;line-height:35px; margin-top: 20px;}
    .main_tg{margin:5px 15px; overflow: hidden; line-height: 22px; color: #666;}
    .lianjie_tg{margin:5px 15px;}
    .lianjie_tg p{line-height: 35px; color: #333;font-weight: bold;font-size: 15px;}
    .lianjie_tg input{width: 100%; border:1px #ededed solid; border-radius: 2px; padding: 0 5px; line-height: 35px; height: 35px; color: #666;}
    .lianjie_tg div span{margin-right: 30px; color: #666; margin-bottom: 10px;}
    .lianjie_tg div label{float: left;position: relative;}
    .lianjie_tg div label input{width: 15px; height: 15px; border: none;position: absolute; top:3px; left: -18px;}
    .lianjie_tg select{width: 100%; border: 1px #ededed solid; margin: 5px 0; line-height: 35px; height: 35px; text-align: center;}

    .lianjie_tg input.name_tg {
        line-height: 40px;
        font-size: 14px;
        width: 100%;
        background: url(/static/distribute/images/21.png) no-repeat;
        background-position: 5px 10px;
        background-size: 20px 20px;
        text-indent: 35px;
        background-color: #fff;
        color: #666;
        border: 1px #ededed solid;
        padding: 0;
        height: 40px;
        margin-bottom: 10px;
    }
    .lianjie_tg input.pho_tg {
        line-height: 40px;
        font-size: 14px;
        width: 100%;
        background: url(/static/distribute/images/pho.png) no-repeat;
        background-position: 5px 10px;
        background-size: 20px 20px;
        text-indent: 35px;
        background-color: #fff;
        color: #666;
        border: 1px #ededed solid;
        padding: 0;
        height: 40px;
        margin-bottom: 10px;
    }
    .lianjie_tg input.qq_tg {
        line-height: 40px;
        font-size: 14px;
        width: 100%;
        background: url(/static/distribute/images/qq.png) no-repeat;
        background-position: 5px 10px;
        background-size: 20px 20px;
        text-indent: 35px;
        background-color: #fff;
        color: #666;
        border: 1px #ededed solid;
        padding: 0;
        height: 40px;
        margin-bottom: 10px;
    }
    .lianjie_tg input.em_tg {
        line-height: 40px;
        font-size: 14px;
        width: 100%;
        background: url(/static/distribute/images/em.png) no-repeat;
        background-position: 5px 10px;
        background-size: 20px 20px;
        text-indent: 35px;
        background-color: #fff;
        color: #666;
        border: 1px #ededed solid;
        padding: 0;
        height: 40px;
        margin-bottom: 10px;
    }
    .button_tg{width: 80%; overflow: hidden; margin:15px auto;}
    .button_tg span{width:50% }
    .button_tg span a div{width: 90%; background: red; text-align: center;line-height: 35px; height: 35px; color: #fff; margin: 0 auto; border-radius: 3px;}
</style>
<script type="text/javascript">
    $(function() {
        $("#submit-btn").on('click', function () {
            layer.open({
                type:2,
                content:'请求中'
            });
            $.ajax({
                type:'post',
                url:"?UsersID=<?=$UsersID?>",
                dataType:'json',
                data:$("#gather_form").serialize(),
                success:function(data) {
                    layer.closeAll();
                    if (data.errcode != 0) {
                        layer.open({
                            content:data.msg
                        })
                    } else {
                        location.href = data.url;
                    }
                }
            });
        });

        $("input[name=link_type]").change(function(){
            var did = $(this).parent('label').attr('id')+'_x';
            $("#"+did).attr('name', 'link_url').siblings('select').attr('name', 'link_url1');
            $("#"+did).show().siblings('select').hide();
        })
    })
</script>
<body>
<div><img src="<?=empty($rsConfig['pop_helper_pic']) ? '/static/distribute/images/pop_default.jpg' : $rsConfig['pop_helper_pic']?>" style="display: block; margin: 0 auto;width: 100%;"></div>
<h3 class="title_tg">推广小助手</h3>
<?
    if (!empty($rsConfig['pop_helper_descr'])) {
?>
<p class="main_tg"><?=$rsConfig['pop_helper_descr']?></p>
<? } ?>
<form action="" method="post" id="gather_form">
    <div class="lianjie_tg">
        <p>内容链接</p>
        <input type="text" value="" placeholder="请在这里粘贴网址" name="wx_url"/>
    </div>
    <div class="lianjie_tg">
        <p>选择链接</p>
        <div>
            <span class="left">链接方式：</span>
            <span class="left"><label id="sc"><input name="link_type" type="radio" value="1" checked />商城链接</label></span>
            <span class="left"><label id="tw"><input name="link_type" type="radio" value="2" />图文链接</label></span>
            <select id="sc_x" name="link_url">
                <option value="<?=$base_url?>api/<?=$UsersID?>/shop/wzw/?love">商城首页</option>
                <option value="<?=$base_url?>api/<?=$UsersID?>/shop/allcategory/?love">全部分类</option>
                <?php
                $list_column = array();
                $DB->get("shop_category","*","where Users_ID='".$UsersID."' order by Category_ID asc");
                while($r=$DB->fetch_assoc()){
                    $list_column[] = $r;
                }
                foreach($list_column as $k=>$v){
                ?>
                    <option value="<?=$base_url?>api/<?=$UsersID?>/shop/category/<?php echo $v["Category_ID"];?>/"><?=$v["Category_Name"]?></option>
                <?php } ?>
            </select>
            <select id="tw_x" style="display: none" name="link_url1">
                <option value=''>--请选择--</option>
                <optgroup label='---------------系统业务模块---------------'></optgroup>
                <?php
                foreach($sys_material as $value){
                    echo '<option value="'.$value['real_url'].'">'.$value['Title'].'</option>';
                }
                ?>
                <optgroup label="---------------自定义图文消息---------------"></optgroup>
                <?php
                foreach($diy_material as $value){
                    echo '<option value="'.$value['real_url'].'">'.($value['Material_Type']?'【多图文】':'【单图文】').$value['Title'].'</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="lianjie_tg">
        <p>联系方式</p>
        <input type="text" value="" placeholder="请在此输入你的姓名" class="name_tg" name="name"/>
        <input type="text" value="" placeholder="请在此输入你的电话" class="pho_tg" name="mobile"/>
        <input type="text" value="" placeholder="请在此输入你的QQ" class="qq_tg" name="qq"/>
        <input type="text" value="" placeholder="请在此输入你的邮箱" class="em_tg" name="email"/>
    </div>
</form>
<div class="clear"></div>
<div class="button_tg">
    <span class=" left"><a href="javascript:void(0);" id="submit-btn"><div>提交</div></a></span>
    <span class=" left"><a href="/api/<?=$UsersID?>/distribute/gather_list/"><div>转发详情</div></a></span>
</div>
</body>
</html>