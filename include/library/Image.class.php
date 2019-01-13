<?php

/**
 * 图像处理类库
 * textOnImage('原图片','水印需要写的字',水印字体大小,数组类型水印字体颜色,'输出文件路径');
 * littleImage('原图片','输出文件路径',输出缩略图文件大小);
 * waterImg('原图片','水印图片',水印放置位置X坐标,水印放置位置Y坐标);
 */
class imageThum
{
    public function  textOnImage($img, $text, $textsize, $textC, $imgDir)
    {
        $img_info = getimagesize($img);
        $type = image_type_to_extension($img_info[2], false);
        $createstyle = 'imagecreatefrom' . $type;
        $dsource = $createstyle($img);
        $textColor = imagecolorallocate($dsource, $textC[0], $textC[1], $textC[2]);
        imagettftext($dsource, $textsize, 0, 50, 50, $textColor, '../fonts/msyh.ttf', $text);
        $outfun = 'image' . $type;
        header("Content-type:image/" . $type);
        $outfun($dsource, $imgDir . basename($img));
        imagedestroy($dsource);
    }

    public function waterImg($dst, $src, $dst_x, $dst_y)
    {
        $dst_info = getimagesize($dst);
        $dst_type = image_type_to_extension($dst_info[2], false);
        $src_info = getimagesize($src);
        $src_type = image_type_to_extension($src_info[2], false);
        $dfun = 'imagecreatefrom' . $dst_type;
        $sfun = 'imagecreatefrom' . $src_type;
        $dsource = $dfun($dst);
        $ssource = $sfun($src);
        imagecopymerge($dsource, $ssource, $dst_x, $dst_y, 0, 0, $src_info[0], $src_info[1], 100);
        $outfun = 'image' . $dst_type;
        header("Content-type:image/" . $src_type);
        $outfun($dsource);
        imagedestroy($dsource);
        imagedestroy($ssource);
    }

    public function littleImage($src, $thumbDir, $w = 0, $h = 0)
    {
		$comkzmarr = ['wf', 'lv', 'p3', 'av', 'ma', 'mv', 'id', 'vi', 'sf', 'rm', 'vb', 'p4'];
		$comkzm = substr($src, -2);
		if (in_array($comkzm,$comkzmarr)) {
			return true;
		}
        $src_info = getimagesize($src);
        $srctype = image_type_to_extension($src_info[2], false);
        $srcfun = 'imagecreatefrom' . $srctype;
        $srcimg = $srcfun($src);
        imagesavealpha($srcimg, true);//这里很重要;
        if ($w == 0) {
            $scale = $src_info[1] / $src_info[0];
            $w = $h / $scale;
        } elseif ($h == 0) {
            $scale = $src_info[0] / $src_info[1];
            $h = $w / $scale;
        }
        $thumbimg = imagecreatetruecolor($w, $h);
        imagealphablending($thumbimg, false);
        imagesavealpha($thumbimg, true);
        imagecopyresampled($thumbimg, $srcimg, 0, 0, 0, 0, $w, $h, $src_info[0], $src_info[1]);
        $outfun = 'image' . $srctype;
        //header("Content-type:image/".$srctype);
        $outfun($thumbimg, $thumbDir . basename($src));
        imagedestroy($thumbimg);
        imagedestroy($srcimg);
    }
}