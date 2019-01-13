<?php
function file_ext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1)));
}

function is_image($file) {
	return preg_match("/^(jpg|jpeg|gif|png|bmp)$/i", file_ext($file));
}

function gb2py($text, $exp = '') {
	if(!$text) return '';
	if(strtolower(DT_CHARSET) != 'gbk') $text = convert($text, DT_CHARSET, 'gbk');
	$data = array();
	$tmp = @file(DT_ROOT.'/file/table/gb-pinyin.table');
	if(!$tmp) return '';
	$tmps = count($tmp);
	for($i = 0; $i < $tmps; $i++) {
		$tmp1 = explode("	", $tmp[$i]);
		$data[$i]=array($tmp1[0], $tmp1[1]);
	}
	$r = array();
	$k = 0;
	$textlen = strlen($text);
	for($i = 0; $i < $textlen; $i++) {
		$p = ord(substr($text, $i, 1));		
		if($p > 160) {
			$q = ord(substr($text, ++$i, 1));
			$p = $p*256+$q-65536;
		}
        if($p > 0 && $p < 160) {
            $r[$k] = chr($p);
        } elseif($p< -20319 || $p > -10247) {
            $r[$k] = '';
        } else {
            for($j = $tmps-1; $j >= 0; $j--) {
                if($data[$j][1]<=$p) break;
            }
            $r[$k] = $data[$j][0];
        }
		$k++;
	}
	return implode($exp, $r);
}

function delete_local($content, $usersid, $ext = 'jpg|jpeg|gif|png|bmp|swf') {
	if(preg_match_all("/src=([\"|']?)([^ \"'>]+\.($ext))\\1/i", $content, $matches)) {
		foreach($matches[2] as $url) {
			delete_upload($url, $usersid);
		}
		unset($matches);
	}
}

function delete_diff($new, $old, $usersid, $ext = 'jpg|jpeg|gif|png|bmp|swf') {
	$new = stripslashes($new);
	$diff_urls = $new_urls = $old_urls = array();
	if(preg_match_all("/src=([\"|']?)([^ \"'>]+\.($ext))\\1/i", $old, $matches)) {
		foreach($matches[2] as $url) {
			$old_urls[] = $url;
		}
	} else {
		return;
	}
	if(preg_match_all("/src=([\"|']?)([^ \"'>]+\.($ext))\\1/i", $new, $matches)) {
		foreach($matches[2] as $url) {
			$new_urls[] = $url;
		}
	}
	foreach($old_urls as $url) {
		in_array($url, $new_urls) or $diff_urls[] = $url;
	}
	if(!$diff_urls) return;
	
	foreach($diff_urls as $url) {
		delete_upload($url, $usersid);
	}
	unset($new, $old, $matches, $url, $diff_urls, $new_urls, $old_urls);
}

function delete_upload($file, $usersid) {
	$exp = explode("uploadfiles/", $file);
	if(!empty($exp[1])){
		$file = $_SERVER["DOCUMENT_ROOT"].'/uploadfiles/'.$exp[1];
		if(is_file($file) && strpos($exp[1], '..') === false && strpos($file,'/'.$usersid.'/')>-1) {
			file_del($file);
		}
	}
}

function file_del($filename) {
	@chmod($filename, 0777);
	return is_file($filename) ? @unlink($filename) : false;
}

/*
function clear_upload($content = '', $itemid = 0) {
	global $CFG, $DT, $db, $session, $_userid;
	if(!is_object($session)) $session = new dsession();
	if(!isset($_SESSION['uploads']) || !$_SESSION['uploads'] || !$content) return;
	$update = array();
	foreach($_SESSION['uploads'] as $file) {
		if(strpos($content, $file) === false) {
			delete_upload($file, $_userid);
		} else {
			if($DT['uploadlog'] && $itemid) $update[] = "'".md5($file)."'";
		}
	}
	if($update) $db->query("UPDATE {$db->pre}upload_".($_userid%10)." SET itemid=$itemid WHERE item IN (".implode(',', $update).")");
	$_SESSION['uploads'] = array();
}
*/
?>