<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

use Illuminate\Database\Capsule\Manager as DB;

$base_url = base_url();

$TypeID = I("TypeID");
if(IS_GET && I("action") && I("AddressID")){
    if("del" == I("action"))
    {
        $AddressID = I("AddressID");
        $rsAddress = DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID, "Address_ID" =>
            $AddressID])->first();
        //将另一个地址设置为默认地址
        DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID, "Address_ID" =>
            $AddressID])->delete();
        if(1 == $rsAddress['Address_Is_Default']){
            $rsAddress = DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->first();
            if($rsAddress){
                $AddressID = 	$rsAddress['Address_ID'];
                DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID, "Address_ID" =>
                    $AddressID])->update(['Address_Is_Default' => 1]);
            }
        }
        header("location:".$_SERVER['HTTP_REFERER']);
        exit;
    }
}
if($openid = I("OpenID")){
    $_SESSION[$UsersID.'OpenID'] = $openid;
    header("location:/api/".$UsersID."/pintuan/my/address/".(empty($TypeID)?'':$TypeID.'/')."?wxref=mp.weixin.qq.com");
    exit;
}else{
    if(!isset($_SESSION[$UsersID.'OpenID'])){
        $_SESSION[$UsersID.'OpenID']=session_id();
    }
}
if(!strpos($_SERVER['REQUEST_URI'],"mp.weixin.qq.com")){
    header("location:?wxref=mp.weixin.qq.com");
}

$rsConfig = DB::table("user_config")->where(["Users_ID" => $UsersID])->first();
if($UserID){
    $rsUser = DB::table("user")->where(["Users_ID" => $UsersID, 'User_ID' => $UserID])->first();
}else{
    $_SESSION[$UsersID."HTTP_REFERER"]="/api/".$UsersID."/pintuan/my/address/?wxref=mp.weixin.qq.com";
    header("location:/api/".$UsersID."/user/login/?wxref=mp.weixin.qq.com");
}

//用户收货地址列表
$address_list = DB::table("user_address")->where(["Users_ID" => $UsersID, 'User_ID' => $UserID])->get();
$area_json = read_file(CMS_ROOT . '/data/area.js');
$area_array = json_decode($area_json,true);
$province_list = $area_array[0];

if($address_id = I("AddressID")){
    $Select_Model = true;
    $AddressID = $address_id;
    $_SESSION[$UsersID."Select_Model"] = 1;
}else{
    $Select_Model = false;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>收货地址管理</title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/api/shop/skin/default/style.css?t=<?php echo time(); ?>' rel='stylesheet' type='text/css' />
    <link href="/static/css/bootstrap.css" rel="stylesheet" />
    <link rel="stylesheet" href="/static/css/font-awesome.css" />
    <link rel="stylesheet" href="/static/api/shop/skin/default/css/address_list.css" />
    <script type="text/javascript" src="/static/api/pintuan/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/api/pintuan/js/layer/1.9.3/layer.js"></script>
    <script type='text/javascript' src='/static/api/js/global.js?t=<?php echo time(); ?>'></script>
</head>
<body >
<header class="bar bar-nav">
    <a href="<?php echo isset($_COOKIE['url_referer']) ? "javascript:location.href='{$_COOKIE['url_referer']}';" : 'javascript:history.go(-1)'; ?>" class="pull-left"><img src="/static/api/shop/skin/default/images/black_arrow_left.png" width="17px" height="17px"/></a>
    <h1 class="title" id="page_title">收货地址管理 </h1>
</header>
<div id="wrap">
    <!-- 地址信息简述begin -->
    <div class="container">
        <?php if(count($address_list) >0 ):?>
            <?php foreach($address_list as $key=>$address): ?>
                <?php
                $Province = $province_list[$address['Address_Province']];
                $City = $area_array['0,'.$address['Address_Province']][$address['Address_City']];
                $Area = $area_array['0,'.$address['Address_Province'].','.$address['Address_City']][$address['Address_Area']];

                ?>
                <div  class="row receiver-info" id="myaddress">
                    <dl address_id="<?=$address['Address_ID']?>">
                        <dd class="col-xs-1" style="line-height: 60px;">
                            <input name="Fruit" type="radio" value="<?php echo $address['Address_ID'];?>"  class="dz" <?=$address['Address_Is_Default'] == 1?"checked":"";  ?>/>
                            <?php if($Select_Model):?>
                                <a href="javascript:void(0)" address_id = '<?=$address['Address_ID']?>' class="select_address">&nbsp;&nbsp;<span class="fa fa-check <?=($AddressID == $address['Address_ID'])?'red':'grey'?>"></span></a>
                            <?php endif;?>
                        </dd>
                        <dd class="col-xs-9"><p><?=$address['Address_Name']?>&nbsp;&nbsp;&nbsp;&nbsp;<?=$address['Address_Mobile']?><br/>
                                所在地区:<?=$Province?>&nbsp;&nbsp;<?=$City?>&nbsp;&nbsp;<?=$Area?><br/>
                                详细地址:<?=$address['Address_Detailed']?>
                                <?=$address['Address_Is_Default'] == 1?'&nbsp;&nbsp;<span class="red">默认</span>':''?>
                            </p></dd>
                        <dd class="col-xs-1">
                            <a class="edit_address" href="/api/<?=$UsersID?>/pintuan/my/address/del/<?=$address['Address_ID']?>/"><span class="fa fa-remove red"></span></a>    <br/>
                            <a class="edit_address" href="/api/<?=$UsersID?>/pintuan/my/address/edit/<?=$address['Address_ID']?>/"><span class="fa fa-pencil"></span></a>
                        </dd>
                    </dl>
                </div>
            <?php endforeach; ?>
            <br/>
        <?php else: ?>
            <div  class="row">
                <p style="text-align:center"><br/>暂无收货地址，请添加</p>
            </div>
        <?php endif; ?>
    </div>
    <a id="manage-address-btn" href="/api/<?=$UsersID?>/pintuan/my/address/edit/" style="background-color:#f61d4b;">新增收货地址</a>
    <!-- 地址信息简述end-->
</div>
<?php if(count($address_list) >1 ){  ?>
    <script>
        $(function(){
            var myid = $("input[name='Fruit']:checked").val();
            var UsersID = "<?=$UsersID ?>";
            if(localStorage.getItem(UsersID + "MyAddressID")){
                localStorage.removeItem(UsersID + "MyAddressID");
            }
            localStorage.setItem(UsersID + "MyAddressID",myid);
            $("#myaddress dl").on("click",function(){
                $(this).find("input[name='Fruit']").prop("checked",true);
                var address_id = $(this).attr("address_id");
                if(localStorage.getItem(UsersID + "MyAddressID")){
                    localStorage.removeItem(UsersID + "MyAddressID");
                }
                localStorage.setItem(UsersID + "MyAddressID",address_id);
                <?php if(isset($_SESSION[$UsersID.'csid'])){ ?>
                location.href = "/api/<?=$UsersID?>/pintuan/order/<?=$_SESSION[$UsersID.'csid']?>/"+address_id+"/";
                <?php } ?>
            });
        });
    </script>
<?php } ?>
</body>
</html>