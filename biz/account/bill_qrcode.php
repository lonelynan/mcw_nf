<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
require_once($_SERVER["DOCUMENT_ROOT"] . '/include/helper/tools.php');
require_once $_SERVER['DOCUMENT_ROOT'] . "/include/library/phpqrcode/phpqrcode.php";
$qrcodePath = $_SERVER['DOCUMENT_ROOT'] . '/uploadfiles/biz/'.$_SESSION["BIZ_ID"].'/qrcode/';
$activity = $DB->GetRs('biz', 'Users_ID, bill_reduce_type, bill_man_json, bill_rebate,Is_Union, QrcodeSet', 'where Biz_ID = ' . $_SESSION['BIZ_ID']);
require_once($_SERVER["DOCUMENT_ROOT"].'/control/weixin/weixin_token.class.php');
$weixin_token = new weixin_token($DB,$activity['Users_ID']);
$access_token = $weixin_token->get_access_token();
if(!$activity['Is_Union']){
    echo "<script>alert('您不是联盟商家，不能使用此功能');history.go(-1);</script>";exit;
}
$qrcode = $qrcodePath . 'bill_qrcode.png';

//检查目录是否存在
if (!is_dir($qrcodePath)) {
    dmkdir($qrcodePath);
}

if (true) {
    $longUrl = 'http://'.$_SERVER['HTTP_HOST'].'/pay/wxpay2/bill_wxpay.php?bizid='.$_SESSION["BIZ_ID"];
    $postUrl = 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token=' . $access_token;
    $post = json_encode(['action' => 'long2short', 'long_url' => $longUrl]);
    $ch = curl_init($postUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $output = curl_exec($ch);
    $outArr = json_decode($output, true);
    if (isset($outArr['errcode']) && $outArr['errcode'] == 0) {
        QRcode::png($outArr['short_url'], $qrcode, 3, 30);
    } else {
        QRcode::png($longUrl, $qrcode, 3, 30);
    }
}

//获取商家的活动设置
$activity = $DB->GetRs('biz', 'bill_reduce_type, bill_man_json, bill_rebate, Biz_Name, QrcodeSet', 'where Biz_ID = ' . $_SESSION['BIZ_ID']);
if (!empty($activity['bill_man_json'])) {
    //处理满减
    $bill_offer = json_decode($activity['bill_man_json'], true);
}

if (isset($_POST['act']) && $_POST['act'] == 'activity') {
    $type = $_POST['type'];
    if ($type == 0) {
        $offer = '';
        $rebate = 0.00;
    } else if ($type == 1) {
        $rebate = 0.00;

        $offer = array_filter(array_combine($_POST['money'], $_POST['offer']));
        $offer = !empty($offer) ? json_encode($offer, JSON_UNESCAPED_UNICODE) : '';
    } else if ($type == 2) {
        $offer = '';

        $rebate = $_POST['rebate'];
    } else {
        $Data = [
            'errorCode' => 1,
            'msg' => '活动类型选择错误'
        ];
        echo json_encode($Data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    $data = [
        'bill_reduce_type' => $type,
        'bill_man_json' => $offer,
        'bill_rebate' => $rebate
    ];

    $res = $DB->Set('biz', $data, 'where Biz_ID = ' . $_SESSION['BIZ_ID']);
    if ($res) {
        $Data = [
            'errorCode' => 0,
            'msg' => '更新成功'
        ];
    } else {
        $Data = [
            'errorCode' => 2,
            'msg' => '更新失败'
        ];
    }
    echo json_encode($Data, JSON_UNESCAPED_UNICODE);
    exit;
    
}
$rsConfig = $DB->GetRs("shop_config", "biz_qrcodeone, biz_qrcodetwo", "WHERE Users_ID='".$_SESSION['Users_ID']."'");
if (!empty($rsConfig['biz_qrcodeone'])) {
$onearr = explode('|', $rsConfig['biz_qrcodeone']);
$oneone = 1;
} else {
	$onearr[0] = 1;
	$onearr[1] = 1;
	$oneone = 0;
}
if (!empty($rsConfig['biz_qrcodetwo'])) {
$twoarr = explode('|', $rsConfig['biz_qrcodetwo']);
$twotwo = 1;
} else {
	$twoarr[0] = 1;
	$twoarr[1] = 1;
	$twotwo = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="keywords" content="html5、css3、jquery">
    <meta name="description" content="">
    <title>商户买单二维码</title>
    <script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
    <style>
        img{ border:solid 1px #ddd;}
        *{margin:0;padding: 0}
        a {text-decoration:none;}
        .box{width: 100%;height: 500px;background: #e0e6ef;}
        .box_left{width: 300px;margin:50px 4% 0 5%;float: left;}
        .box_right{width: 60%;margin:150px 0 0 0;float: left;background: #e0e6ef;line-height: 30px;}
        .box_right p {line-height:2.5em;}
        .box_right .download { margin-top:10px; }
        .box_right .download a { padding:6px 18px; background-color:#22AD38; color:#fff; }

        .activity { clear:both;width:100%;background: #e0e6ef;height:300px;}
        .activity .choose {padding-left:5%;}
        .activity .choose label { margin-right:20px; }
        .activity .choose input { margin-left:5px; }
        .activity .offer, .rebate {padding:20px 0 0 18%;}
        .activity .offer input, .rebate input { border:1px solid #bbb; width:100px; height:30px; line-height:30px; text-indent:10px; }
        .activity .offer span {padding-right:10px;}
        .activity .offer img { position:relative; top:10px; width:25px; height:25px; }
        .activity .rebate {margin-top:7px;}
        .activity #btn {padding:40px 0 0 30%;}
        .activity #btn a {padding:5px 25px;background-color:#22AD38; color:#fff;}
    </style>
</head>
<body>
<div class="box">
    <div class="box_left" id="box_left">
        <!--这里放生成的二维码物料-->
    </div>
    <div class="box_right">
        <p>素材用途：下载后自行印刷成物料，供用户扫码买单</p>
        <p>物料尺寸：110mm x 145mm</p>
        <p>推荐位置：可张贴在桌子、服务台等处，方便用户扫码</p>
        <p class="download"><a href="Javascript:;" download="收款二维码.png" id="load">下载素材</a></p>
        <p style="color:#888;">若无法正常下载，请通过右键单击图片 - 图片另存为保存图片</p>
    </div>
</div>
<div class="activity">
    <form id="activity">
        <input type="hidden" name="act" value="activity">
        <div class="choose">
            <span>线下扫码优惠活动设置：</span>
            <input type="radio" name="type" value="0" <?= $activity['bill_reduce_type'] == 0 ? 'checked' : '' ?> id="no"><label for="no">无活动</label>
            <input type="radio" name="type" value="1" <?= $activity['bill_reduce_type'] == 1 ? 'checked' : '' ?> id="offer"><label for="offer">满减</label>
            <input type="radio" name="type" value="2" <?= $activity['bill_reduce_type'] == 2 ? 'checked' : '' ?> id="rebate"><label for="rebate">折扣</label>
        </div>
        <div class="offer" <?= $activity['bill_reduce_type'] == 1 ? '' : 'style="display:none;"' ?>>
            <?php if (!empty($bill_offer)) {
                $i = 0;
                foreach ($bill_offer as $k => $v) {
            ?>
            <p>
                <span>价格：<input type="text" name="money[]" value="<?= $k ?>" maxlength="8"></span>
                <span>优惠：<input type="text" name="offer[]" value="<?= $v ?>" maxlength="8"></span>
                <span class="<?= $i == 0 ? 'add' : 'del' ?>"><img src="/static/images/<?= $i == 0 ? 'add' : 'del' ?>.png"></span>
            </p>
            <?php $i++;
                }
            } else { ?>
            <p>
                <span>价格：<input type="text" name="money[]" maxlength="8"></span>
                <span>优惠：<input type="text" name="offer[]" maxlength="8"></span>
                <span class="add"><img src="/static/images/add.png"></span>
            </p>
            <?php } ?>
        </div>
        <div class="rebate" <?= $activity['bill_reduce_type'] == 2 ? '' : 'style="display:none;"' ?>>
            折扣：<input type="text" name="rebate" value="<?= $activity['bill_rebate'] ?>" maxlength="5" placeholder="请输入优惠折扣" />&nbsp;&nbsp;&nbsp;&nbsp;若商品打8折，则填写0.8
        </div>
        <div id="btn">
            <a href="javascript:;">提交</a>
        </div>
    </form>
</div>
<div id="imgBox" align="center"></div>
<script>
    function hecheng(){
        draw(function(){
            document.getElementById('box_left').innerHTML='<img src="'+base64[0]+'" style="width:300px;height:400px;border: none;">';
            document.getElementById('load').href = base64[0];
        })
    }

    var data=['/static/biz/images/bill_pay.png','/uploadfiles/biz/<?=$_SESSION['BIZ_ID']?>/qrcode/bill_qrcode.png?t=1'],base64=[];
    function draw(fn){
        var c=document.createElement('canvas'),
            ctx=c.getContext('2d'),
            len=data.length;
            c.width=2598;
            c.height=3424;
            function drawing(n){
            if(n<len){
                var img=new Image;
                img.src=data[n];
                img.onload=function(){
                    if(n==1){
                        ctx.drawImage(img,800,1100,1000,1000);
                    }
                    else{
                        ctx.drawImage(img,0,0,c.width,c.height);
                        ctx.font="240px Verdana";
                        <?
                            $chinese = preg_replace('/[^\x{4e00}-\x{9fa5}]/u', '', $activity['Biz_Name']);
                            $chineseLength = mb_strlen($chinese,"utf-8");
                            $english = preg_replace('/[\x{4e00}-\x{9fa5}]/u', '', $activity['Biz_Name']);
                            $englishLength = strlen($english);
                            $allLength = $chineseLength + ($englishLength / 2);
                        ?>
                        var bizLength = <?=$allLength;?> * 300;
                        var oneone = <?=$oneone;?>;
                        var twotwo = <?=$twotwo;?>;
                        var paddingLeft = (c.width - bizLength) / 2 + <?=$activity['QrcodeSet']?>;
                        ctx.fillText('<?=$activity['Biz_Name']?>', paddingLeft, 3150);
						if (oneone == 1) {
						var paddingLeftone = <?=empty($onearr[1]) ? 0 : $onearr[1]?>;
						}	
						if (twotwo == 1) {
						var paddingLefttwo = <?=empty($twoarr[1]) ? 0 : $twoarr[1]?>;
						}
						if (oneone == 1 || twotwo == 1) {
						ctx.font="140px Verdana";
						ctx.fillStyle='#fff';
						}
						if (oneone == 1) {
                        ctx.fillText('<?=$onearr[0]?>', paddingLeftone, 2570);
						}
						if (twotwo == 1) {
                        ctx.fillText('<?=$twoarr[0]?>', paddingLefttwo, 850);
						}
						
                    }

                    drawing(n+1);
                }
            }else{
                base64.push(c.toDataURL("image/png",1));
                fn();
            }
        }
        drawing(0);
    }

    hecheng();

    $(function(){
        //选择活动类型
        $('input[name=type]').change(function(){
            var type = $(this).val();
            if (type == 0) {
                $('.offer').attr('style', 'display:none;');
                $('.rebate').attr('style', 'display:none;');
            } else if (type == 1) {
                $('.offer').removeAttr('style');
                $('.rebate').attr('style', 'display:none;');
            } else if (type == 2) {
                $('.offer').attr('style', 'display:none;');
                $('.rebate').removeAttr('style');
            }
        });

        //添加满减项
        $('.offer').on('click', '.add', function(){
            var offer = '<p><span>价格：<input type="text" name="money[]" maxlength="8"></span> <span>优惠：<input type="text" name="offer[]" maxlength="8"></span> <span class="del"><img src="/static/images/del.png"></span></p>';
            $('.offer').append(offer);
        });

        //删除满减项
        $('.offer').on('click', '.del', function(){
            $(this).parents('p').remove();
        });

        //提交
        $('#btn a').click(function(){
            $.ajax({
                type: 'post',
                url: '?',
                data: $('#activity').serialize(),
                success: function(json){
                    if (json.errorCode == 0) {
                        alert('提交成功');
                        location.reload();
                    } else {
                        alert(json.msg);
                    }
                },
                dataType: 'json'
            });
        });
    });
</script>
</body>
</html>