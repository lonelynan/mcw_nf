<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
if(!defined("CMS_ROOT"))  define("CMS_ROOT", $_SERVER["DOCUMENT_ROOT"]);
require_once (CMS_ROOT . '/include/helper/tools.php');
require_once (CMS_ROOT . '/include/helper/url.php');
require_once (CMS_ROOT . '/include/helper/shipping.php');
require_once (CMS_ROOT . '/include/helper/lib_pintuan.php');
require_once (CMS_ROOT . '/include/helper/lib_products.php');
require_once (CMS_ROOT . '/include/helper/distribute.php');
require_once (CMS_ROOT . '/include/helper/flow.php');

use Illuminate\Database\Capsule\Manager as DB;

define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? true : false));
define('IN_UPDATE', 1);
define('PROTOCAL',((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://');

$request_uri = $_SERVER['REQUEST_URI'];
$UsersID = "";
$rsConfig = [];
$BizID = 0;
$IsStore = 0;

$SortType = [
    '按发布时间',
    '按销量',
    '按价格',
    '手动'
];

$typelist = [
    /*1 =>
    [
        'Type_ID' => 1,
        'module' => 'pintuan',
        'Type_Name' => '拼团',
        'Status' =>1
    ],*/
//    2 =>
//    [
//        'Type_ID' => 2,
//        'module' => 'cloud',
//        'Type_Name' => '云购',
//        'Status' =>1
//    ],
//    3=>
//    [
//        'Type_ID' => 3,
//        'module' => 'zhongchou',
//        'Type_Name' => '众筹',
//        'Status' =>1
//    ],
    5 =>
        [
            'Type_ID' => 5,
            'module' => 'flashsale',
            'Type_Name' => '限时抢购',
            'Status' =>1
        ],
    6 =>
        [
            'Type_ID' => 6,
            'module' => 'Is_hot',
            'Type_Name' => '热门推荐',
            'Status' =>1
        ],
    7 =>
        [
            'Type_ID' => 7,
            'module' => 'speail_offer',
            'Type_Name' => '9.9特价',
            'Status' =>1
        ],
    8 =>
        [
            'Type_ID' => 8,
            'module' => 'super_team',
            'Type_Name' => '超级团',
            'Status' =>1
        ]
];

$_SESSION[$UsersID . "HTTP_REFERER"] = $_SERVER['REQUEST_URI'];

if (stripos($request_uri, "api")) { // 对于前台的初始化
    if (isset($_SERVER["HTTP_REFERER"]) && stripos($_SERVER["HTTP_REFERER"], 'shop') > 0) {
        S("Index_URI", $_SERVER['REQUEST_URI']);
    }
    $UsersID = I("UsersID");
    !$UsersID && die("缺少必要的参数");
    $rsConfig = DB::table("shop_config")->where("Users_ID", $UsersID)->first();
    $share_flag = 0;
    $signature = '';
    empty($rsConfig) && die("商城没有配置");
    $UserID = S($UsersID."User_ID") ? S($UsersID."User_ID") : 0;
    $BizID = I("BizID", 0);
    // 分销相关设置
    $dis_config = dis_config($UsersID);
    if (empty($dis_config)) {
        $dis_config = [];
    }
    // 合并参数
    $rsConfig = array_merge($rsConfig, $dis_config);
    $is_login = 1;
    $owner = get_owner($rsConfig, $UsersID);
} else {
    if (stripos($request_uri, "member")) { // 对于商城后台的初始化
        ! S("Users_ID") && header("Location: /member/login.php");
        $UsersID = S("Users_ID");
    } else {
        if (stripos($request_uri, "biz")) { // 对于商家后台的初始化
            basename($_SERVER['PHP_SELF']) == 'common.php' && header('Location:http://' . $_SERVER['HTTP_HOST']);
            $BizID = S("BIZ_ID");
            if (! $BizID) {
                header("location:/biz/login.php");
            }
            $rsBiz = DB::table("biz")->where(["Biz_ID" => $BizID ])->first();
            if (! $rsBiz) {
                echo '<script language="javascript">alert("此商家不存在！");history.back();</script>';
                exit();
            }
            $UsersID = $rsBiz['Users_ID'];
            S("Users_ID", $UsersID);
            $rsGroup = DB::table("biz_group")->where(["Group_ID" => $rsBiz["Group_ID"]])->first(["Group_Name","Group_IsStore"]);
            if ($rsGroup) {
                $IsStore = $rsGroup["Group_IsStore"];
            }
        }
    }
}
