<?php
header('Content-type: text/html; charset=UTF-8');
require_once($_SERVER['DOCUMENT_ROOT'] . '/Framework/Conn.php');
require_once(CMS_ROOT . '/third_party/kindeditor/php/JSON.php');
require_once(CMS_ROOT . '/include/library/Image.class.php');
require_once(CMS_ROOT . '/include/helper/tools.php');

use Illuminate\Database\Capsule\Manager as DB;

/**
 * KindEditor PHP
 *
 * 本PHP程序是演示程序，建议不要直接在实际项目中使用。
 * 如果您确定直接使用本程序，使用之前请仔细确认相关安全设置。
 *
 */
/*自定义传入参数开始*/
$UploadFiles_TableField = I('TableField');
$UsersAccount = S("Users_Account");
if(!$UsersAccount)
{
    alert("刷新并重新登录");
}

$php_path = dirname(__FILE__) . '/';
$php_url = dirname($_SERVER['PHP_SELF']) . '/';


//文件保存目录路径
$save_path = $php_path . '../uploadfiles/';
//文件保存目录URL
$save_url = '/uploadfiles/';
//定义允许上传的文件扩展名
$ext_arr = array( 
	'image' => array('gif', 'jpg', 'jpeg', 'png'),
	'flash' => array('swf', 'flv'),
	'media' => array('mp3','mp4','ogg'),
	'file' => array('mp3','mp4','ogg','doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2','pem','pfx','cer'),
);
//最大文件大小，6MB
$max_size = 6 * 1024 * 1024;

$save_path = realpath($save_path) . '/';

//PHP上传失败
if (!empty($_FILES['imgFile']['error'])) {
	switch($_FILES['imgFile']['error']){
		case '1':
			$error = '超过php.ini允许的大小。';
			break;
		case '2':
			$error = '超过表单允许的大小。';
			break;
		case '3':
			$error = '图片只有部分被上传。';
			break;
		case '4':
			$error = '请选择图片。';
			break;
		case '6':
			$error = '找不到临时目录。';
			break;
		case '7':
			$error = '写文件到硬盘出错。';
			break;
		case '8':
			$error = 'File upload stopped by extension。';
			break;
		case '999':
		default:
			$error = '未知错误。';
	}
	alert($error);
}


//有上传文件时
if (empty($_FILES) === false) {
	//原文件名
	$file_name = $_FILES['imgFile']['name'];
	//服务器上临时文件名
	$tmp_name = $_FILES['imgFile']['tmp_name'];
	//文件大小
	$file_size = $_FILES['imgFile']['size'];
	//检查文件名
	if (!$file_name) {
		alert("请选择文件。");
	}
	//检查目录
	if (@is_dir($save_path) === false) {
		alert("上传目录不存在。");
	}
	//检查目录写权限
	if (@is_writable($save_path) === false) {
		alert("上传目录没有写权限。");
	}
	//检查是否已上传
	if (@is_uploaded_file($tmp_name) === false) {
		alert("上传失败。");
	}
	//检查文件大小
	if ($file_size > $max_size) {
		alert("上传文件大小超过".($max_size/1024)."K限制。");
	}
	//检查目录名
	$dir_name = empty(I('dir')) ? 'image' : trim(I('dir'));
	if (empty($ext_arr[$dir_name])) {
		alert("目录名不正确。");
	}
	//获得文件扩展名
	$temp_arr = explode(".", $file_name);
	$file_ext = array_pop($temp_arr);
	$file_ext = trim($file_ext);
	$file_ext = strtolower($file_ext);
	//检查扩展名
	if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
		alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
	}

	//创建文件夹
	$Users_ID = empty(S("Users_ID"))?I("Users_ID"):S("Users_ID");
	$save_path .= $Users_ID . "/";
	$save_url .= $Users_ID . "/";
	if (!file_exists($save_path)) {
		mkdir($save_path);
	}
	if ($dir_name !== '') {
		$save_path .= $dir_name . "/";
		$save_url .= $dir_name . "/";
		if (!file_exists($save_path)) {
			mkdir($save_path);
		}
	}
	//新文件名
	$UploadFilesID=dechex(time()) . dechex(rand(16, 255));
	$new_file_name = $UploadFilesID . '.' . $file_ext;
	//移动文件
	$file_path = $save_path . $new_file_name;
	if (move_uploaded_file($tmp_name, $file_path) === false) {
		alert("上传文件失败。");
	}
	@chmod($file_path, 0644);
	$file_url = $save_url . $new_file_name;
    if (in_array($file_ext, $ext_arr['image']) !== false) {
        //创建文件夹
        if (!file_exists($save_path . 'n0/')) {
            mkdir($save_path . 'n0/');
        }
        if (!file_exists($save_path . 'n1/')) {
            mkdir($save_path . 'n1/');
        }
        if (!file_exists($save_path . 'n2/')) {
            mkdir($save_path . 'n2/');
        }
        if (!file_exists($save_path . 'n3/')) {
            mkdir($save_path . 'n3/');
        }
        if (!file_exists($save_path . 'n4/')) {
            mkdir($save_path . 'n4/');
        }
        $thumImg = new imageThum();
        //开始缩略图
        $thumImg->littleImage($file_path, $save_path . 'n0/', 200);
        $thumImg->littleImage($file_path, $save_path . 'n1/', 300);
        $thumImg->littleImage($file_path, $save_path . 'n2/', 0, 350);
        $thumImg->littleImage($file_path, $save_path . 'n3/', 100);
        $thumImg->littleImage($file_path, $save_path . 'n4/', 420);
    }

	/*向数据库中插入记录*/
	DB::table('uploadfiles')->insertGetId([
	    'UploadFiles_ID' => $UploadFilesID,
        'UploadFiles_TableField' => $UploadFiles_TableField,
        'UploadFiles_DirName' => $dir_name,
        'UploadFiles_SavePath' => $file_url,
        'UploadFiles_FileName' => $file_name,
        'UploadFiles_FileSize' => number_format($file_size/1024,2,".",""),
        'UploadFiles_CreateDate' => date("Y-m-d H:i:s")
    ]);
	$json = new Services_JSON();
	echo $json->encode(array('error' => 0, 'url' => $file_url, 'size' => number_format($file_size/1024,2,".","")));
	exit;
}

function alert($msg) {
	header('Content-type: text/html; charset=UTF-8');
	$json = new Services_JSON();
	echo $json->encode(array('error' => 1, 'message' => $msg));
	exit;
}
