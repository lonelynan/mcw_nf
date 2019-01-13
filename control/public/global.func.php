<?php
function is_url($url) {
	return preg_match("/^[http|https]\:\/\/[a-z0-9\/\.\#\&\?\;\,]{4,}$/", $url);
}

function is_email($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

function is_telephone($telephone) {
	return preg_match("/^[0-9\-\+]{7,}$/", $telephone);
}

function is_qq($qq) {
	return preg_match("/^[1-9]{1}[0-9]{4,12}$/", $qq);
}

function is_gbk($string) {
	return preg_match("/^([\s\S]*?)([\x81-\xfe][\x40-\xfe])([\s\S]*?)/", $string);
}

function is_date($date, $sep = '-') {
	if(strlen($date) == 8) $date = substr($date, 0, 4).'-'.substr($date, 4, 2).'-'.substr($date, 6, 2);
	if(strlen($date) > 10 || strlen($date) < 8)  return false;
	list($year, $month, $day) = explode($sep, $date);
	return checkdate($month, $day, $year);
}

/* function base_url(){
	$base_url = 'http://'.$_SERVER['HTTP_HOST'].'/';
	return $base_url;
} */

function generate_qrcode($data){	
	if(strlen($data) == 0){
		echo '数据不可为空';
		return false;
	}
	
	include($_SERVER["DOCUMENT_ROOT"].'/include/library/phpqrcode/phpqrcode.php'); 
	$PNG_TEMP_DIR = $_SERVER["DOCUMENT_ROOT"].'/data/temp/';
	$PNG_WEB_DIR = '/data/temp/';
	$filename = $PNG_TEMP_DIR.'test.png';
	$errorCorrectionLevel = 'H';  
	if(strlen($data)< 15){
		$matrixPointSize = 8; 	
	}else{
	   $matrixPointSize = 5;
	}
	$filename = $PNG_TEMP_DIR.'test'.md5($data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
	QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
	
	$filename = $PNG_WEB_DIR.'test'.md5($data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
	
	return $filename;
}
?>