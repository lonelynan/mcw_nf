<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/distribute.php');
require_once(CMS_ROOT . '/include/library/Image.class.php');
require_once(CMS_ROOT . '/include/helper/tools.php');

if (isset($_GET["OrderID"])) {
    $OrderID = $_GET["OrderID"];
} else {
    layerOpen('缺少必要的参数');
    exit;
}
$rsOrder = $DB->GetRs("user_order", "*", "where Order_ID=" . $OrderID . " and User_ID=" . $_SESSION[$UsersID . "User_ID"] . " and Users_ID='" . $UsersID . "' and Order_Status=4");

if (!$rsOrder) {
    layerOpen('此订单不存在');
    exit;
} elseif ($rsOrder["Is_Commit"] == 1) {
    layerOpen('此订单已评论，不可重复评论！');
    exit;
}

function delImg($imagepath, $index)
{
    if (empty($imagepath) || !is_int($index)) {
        return json_encode(['errorCode' => 1, 'msg' => '数据错误!']);
    }
    $imageArr = explode(',', $imagepath);
    $file = $imageArr[$index];
    $fileInfo = pathinfo($file);
    $allowArr = ['jpg', 'png', 'jpeg', 'gif', 'bmp'];
    if (!in_array($fileInfo['extension'], $allowArr)) {
        return json_encode(['errorCode' => 2, 'msg' => '请勿非法操作!']);
    }
    if (file_exists('../..'.$file)) {
        $flag = unlink('../..'.$file);
        for ($i = 0; $i < 5; $i++) {
            $path_img = '../..' . $fileInfo['dirname'] . '/n' . $i . '/' . $fileInfo['basename'];
            if (file_exists($path_img)) unlink($path_img);
        }
    } else {
        $flag = true;
        //return json_encode(['errorCode' => 1, 'msg' => '图片不存在']);
    }
    if ($flag) {
        unset($imageArr[$index]);
        $imageStr = implode(',', $imageArr);
        return json_encode(['errorCode' => 0, 'msg' => $imageStr]);
    } else {
        return json_encode(['errorCode' => 1, 'msg' => '删除失败!']);
    }
}

if (isset($_POST['act'])) {
    $act = $_POST['act'];
    if ($act == 'upload_img') {
        $base64code = $_POST['data'];

        $image = base64_decode(explode(',', $base64code)[1]);
        $type = explode(',', $base64code)[0];
        $ext = substr($type, strpos($type, '/') + 1, strrpos($type, ':') - strpos($type, '/') - 1);
        $filename = uniqid() . '.' . $ext;

        $dirurl = '../../../uploadfiles/biz/' . $rsOrder['Biz_ID'] . '/image';

        dmkdir($dirurl);
        $imgurl = rtrim($dirurl, '/').'/'.$filename;
        $result = file_put_contents($imgurl, $image);


        //====================生成缩略图开始=============================
        $bigImg = dirname(__FILE__).'/' . $imgurl;
        if (!file_exists(dirname($bigImg) . '/n0/')) {
            mkdir(dirname($bigImg) . '/n0/');
        }
        if (!file_exists(dirname($bigImg) . '/n1/')) {
            mkdir(dirname($bigImg) . '/n1/');
        }
        if (!file_exists(dirname($bigImg) . '/n2/')) {
            mkdir(dirname($bigImg) . '/n2/');
        }
        if (!file_exists(dirname($bigImg) . '/n3/')) {
            mkdir(dirname($bigImg) . '/n3/');
        }
        $thumImg = new imageThum();

        //根据不同的类型，判断使用的尺寸  头像、产品
        if (isset($imgType) && $imgType == 'avatar') {
            $thumImg->littleImage($imgurl, dirname($bigImg) . '/n0/', 0, 200);
            $thumImg->littleImage($imgurl, dirname($bigImg) . '/n1/', 35);
            $thumImg->littleImage($imgurl, dirname($bigImg) . '/n2/', 50);
            $thumImg->littleImage($imgurl, dirname($bigImg) . '/n3/', 100);
        } else {
            $thumImg->littleImage($imgurl, dirname($bigImg) . '/n0/', 200);
            $thumImg->littleImage($imgurl, dirname($bigImg) . '/n1/', 190);
            $thumImg->littleImage($imgurl, dirname($bigImg) . '/n2/', 0, 350);
            $thumImg->littleImage($imgurl, dirname($bigImg) . '/n3/', 100);
        }

        //====================生成缩略图结束=============================

        $imgurl = substr($imgurl, 8);   //去除前面的“../..”
        if ($result) {
            echo json_encode(['errorCode' => 0, 'msg' => $imgurl], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['errorCode' => 1, 'msg' => '上传失败'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    } else if ($act == 'delImg') {
        $imagepath = htmlspecialchars(trim($_POST['image_path']));
        $index = (int)$_POST['index'];

        if (empty($imagepath) || !is_int($index)) {
            return json_encode(['errorCode' => 1, 'msg' => '数据错误!']);
        }
        $imageArr = explode(',', $imagepath);
        $file = $imageArr[$index];
        $fileInfo = pathinfo($file);
        $allowArr = ['jpg', 'png', 'jpeg', 'gif', 'bmp'];
        if (!in_array($fileInfo['extension'], $allowArr)) {
            return json_encode(['errorCode' => 2, 'msg' => '请勿非法操作!']);
        }
        if (file_exists('../../..'.$file)) {
            $flag = unlink('../../..'.$file);
            for ($i = 0; $i < 5; $i++) {
                $path_img = '../../..' . $fileInfo['dirname'] . '/n' . $i . '/' . $fileInfo['basename'];
                if (file_exists($path_img)) unlink($path_img);
            }
        } else {
            $flag = true;
            //return json_encode(['errorCode' => 1, 'msg' => '图片不存在']);
        }
        if ($flag) {
            unset($imageArr[$index]);
            $imageStr = implode(',', $imageArr);
            echo json_encode(['errorCode' => 0, 'msg' => $imageStr]);
        } else {
            echo json_encode(['errorCode' => 1, 'msg' => '删除失败!']);
        }
        exit;
    }
}
$source = $request->input('source', '');
?>
<?php require_once('../skin/top.php'); ?>
<body>
<script src="/static/js/layer/mobile/layer.js"></script>
<script src="/static/api/js/jquery.uploadView.js"></script>
<style>
    .ImgPath {margin-top: 10px;}
    .ImgPath .img, #upload {position: relative; float: left; margin-right: 5px; margin-bottom: 5px; width: 60px;height: 60px;}
    .ImgPath .img img, #upload img {width: 100%; height: 100%;}
    .ImgPath .img div {position: absolute; bottom: 0; width: 100%; height: 20px;line-height: 20px;text-align: center; font-size: 12px;color: #fff; background: rgba(0, 0, 0, 0.3); }
</style>
<div class="top_back">
    <a href="javascript:history.go(-1);"><img src="/static/api/shop/skin/default/images/back_1.png"></a>
    <span>订单评论</span>
</div>
<div id="shop_page_contents">
    <div id="cover_layer"></div>
    <link href='/static/api/shop/skin/default/css/member.css?t=<?= time(); ?>' rel='stylesheet' type='text/css'/>
    <!--<ul id="member_nav">
        <li><a href="/api/<?php /*echo $UsersID */?>/shop/member/status/0/">待付款</a></li>
        <li><a href="/api/<?php /*echo $UsersID */?>/shop/member/status/1/">待确认</a></li>
        <li><a href="/api/<?php /*echo $UsersID */?>/shop/member/status/2/">已付款</a></li>
        <li><a href="/api/<?php /*echo $UsersID */?>/shop/member/status/3/">已发货</a></li>
        <li class="cur"><a href="/api/<?php /*echo $UsersID */?>/shop/member/status/4/">已完成</a></li>
    </ul>-->
    <div id="commit">
        <script language="javascript">
		var source='<?=$source ?>';
		$(document).ready(shop_obj.commit_init);
		</script>
        <form action="/api/<?php echo $UsersID ?>/shop/member/" method="post" id="commit_form">
            <dl>
                <dd>
                    为卖家打分 <font class="fc_red">*</font><br/>
                    <select name="Score" class="score_select">
                        <option value="5">非常满意</option>
                        <option value="4">满意</option>
                        <option value="3">一般</option>
                        <option value="2">差</option>
                        <option value="1">非常差</option>
                    </select>
                </dd>
                <dd>
                    评论内容 <br/>
                    <textarea name="Note" value="" notnull class="score_textarea"></textarea>
                </dd>
                <dd>
                    上传图片(最多上传5张) <br/>
                    <div class="ImgPath" id="upload_pic_img"></div>
                    <div id="upload">
                        <input type="file" class="upload_pic" id="upload_pic" style="display:none;"/>
                        <img src="/static/api/shop/skin/default/images/add.png">
                    </div>
                    <input type="hidden" name="upload_pic_json" value="">
                </dd>
                <div class="clear"></div>

                <dt style="margin-top:10px;">
                    <input type="button" class="submit" value="提交保存"/>
                </dt>
            </dl>
            <input type="hidden" name="OrderID" value="<?= $OrderID ?>"/>
            <input type="hidden" name="source" value="<?= $source ?>"/>
            <input type="hidden" name="action" value="commit"/>
        </form>
    </div>
</div>
<?php /*require_once('../skin/distribute_footer.php');*/ ?>
<script>
    $(function () {

        //产品图片上传
        var pro = document.getElementById('upload_pic');
        if (typeof FileReader === 'undefined') {
            pro.setAttribute('disabled', 'disabled');
        } else {
            pro.addEventListener('change', readFile, false);
        }

        //上传
        $('#upload').click(function () {
            pro.click();
        });

        function readFile() {
            var me = this.id;
            var file = this.files[0];//获取上传文件列表中第一个文件
            this.value = '';
            if (!/image\/\w+/.test(file.type)) {
                //图片文件的type值为image/png或image/jpg
                layer.open({content: '文件必须为图片！', skin: 'msg', time: 1});
                return false;
            }

            var reader = new FileReader();  //实例一个文件对象
            reader.readAsDataURL(file);     //把上传的文件转换成url
            //当文件读取成功便可以调取上传的接口
            reader.onload = function (e) {
                var image = new Image();
                //设置src属性
                image.src = e.target.result;
                var max = 200;
                //绑定load事件处理器，加载完成后执行，避免同步问题
                image.onload = function () {
                    var upload_num = $('#' + me + '_img img').length;
                    /*var width = image.width;
                    var height = image.height;
                    if (width / height > 1.2 || height / width > 1.2) {
                        layer.open({
                            content: '为了您的店铺美观,主图限制长宽比例不得超过1.2!',
                            btn: '确定'
                        });
                        return false;
                    }*/
                    if (upload_num >= 5) {
                        layer.open({
                            content: '产品图片最多上传5张',
                            btn: '确定'
                        });
                        return false;
                    }

                    layer.open({type: 2});
                    $.ajax({
                        type: 'post',
                        url: '?',
                        data: {'act': 'upload_img', 'data': image.src},
                        success: function(data){
                            layer.closeAll();
                            if (data.errorCode == 0) {
                                $('#' + me + '_img').append('<div class="img"><img src="' + image.src + '"><div class="del">删除</div></div>');
                                var img_json = $('input[name="' + me + '_json"]');
                                var json_val = img_json.val() == '' ? data.msg : img_json.val() + ',' + data.msg;
                                img_json.val(json_val);
                                //隐藏上传按钮
                                if (upload_num + 1 >= 5) {$('#upload').hide();}
                            } else {
                                layer.open({content: '上传失败', skin: 'msg', time: 1});
                            }
                        },
                        dataType: 'json'
                    });
                    return;

                    // 获取 canvas DOM 对象
                    var canvas = document.getElementById("cvs");
                    // 如果高度超标 宽度等比例缩放 *=
                    /*if(image.height > max) {
                     image.width *= max / image.height;
                     image.height = max;
                     } */
                    // 获取 canvas的 2d 环境对象,
                    var ctx = canvas.getContext("2d");
                    // canvas清屏
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    // 重置canvas宽高
                    /*canvas.width = image.width;
                     canvas.height = image.height;
                     if (canvas.width>max) {canvas.width = max;}*/
                    // 将图像绘制到canvas上
                    // ctx.drawImage(image, 0, 0, image.width, image.height);
                    //ctx.drawImage(image, 0, 0, 60, 60);
                    // 注意，此时image没有加入到dom之中
                };
            }
        }

        //删除图片
        $('.ImgPath').on('click', '.del', function(){
            var me = $(this);
            layer.open({
                content: '确定删除吗?',
                shadeClose: false,
                btn: ['确定', '取消'],
                yes: function(){
                    layer.closeAll();
                    $.ajax({
                        type:"POST",
                        url:"?",
                        data:{"act": "delImg", "index": me.parent().index(), "image_path": $('input[name="upload_pic_json"]').val()},
                        dataType:"json",
                        success:function(data) {
                            if (data.errorCode == 0) {
                                $('input[name="upload_pic_json"]').val(data.msg);
                                me.parent().remove();
                                $('#upload').show();
                            } else {
                                layer.open({content: data.msg, shadeClose: false, btn: '确定'});
                            }
                        }
                    });
                }
            });
        });
    });
</script>
</body>
</html>