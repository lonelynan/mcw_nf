<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>

<body>
<?php
function unscrambler($code) {
    if(! is_array($code)) {
        $code = str_replace('__FILE__', "'$code'", str_replace('eval', '$code=', file_get_contents($code)));
        eval('?>' . $code);
    }else {
        extract($code);
        $code = str_replace("eval", '$code=', $code);
        eval($code);
    }
    if(strstr($code, 'eval')) return unscrambler(get_defined_vars());
    else return $code;
}


$fpp1 = fopen('temp_.ss.php', 'w');
fwrite($fpp1, unscrambler('products_edit.php')) or die('写文件错误');
?>
</body>
</html>