<?php 
require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
if(isset($_GET["cliid"])){	
	$subid = $_GET["cliid"];
}
$jcurid = 1;
?>
<?php
if($_POST){
	// print_r($_POST);die;
    $banner_img = json_encode($_POST['ImgPathList']);
	$banner_url = !empty($_POST['UrlList'])?json_encode($_POST['UrlList']):'';
    if(empty($_POST['ImgPathList'][0]) && empty($_POST['ImgPathList'][1]) && empty($_POST['ImgPathList'][2]) &&empty($_POST['ImgPathList'][3])){
        die(json_encode(['status' => 0]));
        
    }
    if($_POST['id']>0){
        $data=array(
            "banner_img"=>$banner_img,
			"banner_url"=>$banner_url
        );

        $Flag=$DB->Set("pintuan_config",$data,"where Users_ID='".$_SESSION["Users_ID"]."' and id=".$_POST['id']);
        if($Flag)
        {
               die(json_encode(['status' => 1]));
        }else
        {
                die(json_encode(['status' => 0]));
        }
        exit;	
    }else{
         $data=array(
            "Users_ID"=>$_SESSION["Users_ID"],
            "banner_img"=>$banner_img
        );
        $Flag=$DB->Add("pintuan_config",$data);
            if($Flag)
            {
                    die(json_encode(['status' => 1]));
            }else
            {
                    die(json_encode(['status' => 0]));
            }
	exit;	 
        
    }
    
}else{
    $pt_config = $DB->GetRs('pintuan_config','*',"where Users_ID='".$_SESSION["Users_ID"]."'");
    if(!empty($pt_config)){
        $banner_img = json_decode($pt_config['banner_img'],true);
		 $banner_url = json_decode($pt_config['banner_url'],true);
    //print_r($banner_img); print_r($pt_config);
    }
    
   // print_r($_SESSION["Users_ID"]);
}
	//print_r($_GET["del"]);
if(isset($_GET["del"])){
	$del = $_GET["del"];
	//print_r($del);
	$banner_img_rel=$DB->GetRs("pintuan_config","banner_img","where Users_ID='".$_SESSION["Users_ID"]."'");
	$banner_img_array = json_decode($banner_img_rel['banner_img'],"true");
//print_r($banner_img_array);
	$banner_img_array[$del] = '';
	//print_r($banner_img_array);
//die;
	$banner_img_date = array('banner_img'=>json_encode($banner_img_array));
			$row =$DB->Set("pintuan_config",$banner_img_date,"where Users_ID='".$_SESSION["Users_ID"]."'");
				if($row){
				
					echo '<script language="javascript">window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
				}else{
						
					echo '<script language="javascript">history.back();</script>';
				}

}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $SiteName;?></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css?' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<?php require_once (CMS_ROOT . '/member/image_config.php'); ?>
</head>
<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/web.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/pintuan.js'></script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/pintuan/marketing_menubar.php');?>
    <script language="javascript">$(document).ready(pintuan_obj.home_init);
    </script>
    <div id="home" class="r_con_wrap">                   
      <div class="m_righter r_con_config">
          <form id="home_form" method="post">
          <div id="setbanner">
             <div>首页banner图设置</div>  
            <div class="item">
              <div class="rows">
                <div class="b_l"> <strong>图片(1)</strong><span class="tips">大图建议尺寸：
                  <label>640*350</label>
                  px</span><a href="home.php?del=0" value="0"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a><br />
                    <div class="file" style="margin-top:10px;">
                        <input type="button" id="HomeFileUpload_0" value="选择图片" style="width:80px;" />
                        <input type="hidden" id="ImgPath_0" name="ImgPathList[]" value="<?php echo !empty($banner_img[0])?$banner_img[0]:''?>" />
                        <span class="tips">图片建议尺寸：640*350px</span>
                    </div>
                    <div class="blank6"></div>
                  <input type="hidden" name="Title[]" value="" />
                </div>
                  <div class="b_r" id="PagesPicDetail_0">
                      <?php echo !empty($banner_img[0])?"<img src=$banner_img[0]>":''?>
                  </div>
              </div>
              <div class="blank9"></div>
              <div class="rows url_select">
                <div class="u_l">链接页面</div>
                <div class="u_r">
                 <input type="text" name='UrlList[]' value="<?php echo !empty($banner_url[0])?$banner_url[0]:''?>">
                </div>
              </div>
            </div>
            <div class="item">
              <div class="rows">
                <div class="b_l"> <strong>图片(2)</strong><span class="tips">大图建议尺寸：
                  <label>640*350</label>
                  px</span><a href="home.php?del=1" value="1"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a><br />
                    <div class="file" style="margin-top:10px;">
                        <input type="button" id="HomeFileUpload_1" value="选择图片" style="width:80px;" />
                        <input type="hidden" id="ImgPath_1" name="ImgPathList[]" value="<?php echo !empty($banner_img[1])?$banner_img[1]:''?>" />
                        <span class="tips">图片建议尺寸：640*350px</span>
                    </div>

                    <div class="blank6"></div>
                  <input type="hidden" name="Title[]" value="" />
                </div>
                  <div class="b_r"  id="PagesPicDetail_1">
                       <?php echo !empty($banner_img[1])?"<img src=$banner_img[1]>":''?>
                  </div>
              </div>
              <div class="blank9"></div>
              <div class="rows url_select">
                <div class="u_l">链接页面</div>
                <div class="u_r">
                  <input type="text" name='UrlList[]' value="<?php echo !empty($banner_url[1])?$banner_url[1]:''?>">
                </div>
              </div>
            </div>
            <div class="item">
              <div class="rows">
                <div class="b_l"> <strong>图片(3)</strong><span class="tips">大图建议尺寸：
                  <label>640*350</label>
                  px</span><a href="home.php?del=2" value="2"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a><br />
                    <div class="file" style="margin-top:10px;">
                        <input type="button" id="HomeFileUpload_2" value="选择图片" style="width:80px;" />
                        <input type="hidden" id="ImgPath_2" name="ImgPathList[]" value="<?php echo !empty($banner_img[2])?$banner_img[2]:''?>" />
                        <span class="tips">图片建议尺寸：640*350px</span>
                    </div>
                    <div class="blank6"></div>
                  <input type="hidden" name="Title[]" value="" />
                </div>
                  <div class="b_r" id="PagesPicDetail_2">
                        <?php echo !empty($banner_img[2])?"<img src=$banner_img[2]>":''?>
                  </div>
              </div>
              <div class="blank9"></div>
              <div class="rows url_select">
                <div class="u_l">链接页面</div>
                <div class="u_r">
                  <input type="text" name='UrlList[]' value="<?php echo !empty($banner_url[2])?$banner_url[2]:''?>">
                </div>
              </div>
            </div>
            <div class="item">
              <div class="rows">
                <div class="b_l"> <strong>图片(4)</strong><span class="tips">大图建议尺寸：
                  <label>640*350</label>
                  px</span><a href="home.php?del=3" value="3"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a><br />
                    <div class="file" style="margin-top:10px;">
                        <input type="button" id="HomeFileUpload_3" value="选择图片" style="width:80px;" />
                        <input type="hidden" id="ImgPath_3" name="ImgPathList[]" value="<?php echo !empty($banner_img[3])?$banner_img[3]:''?>" />
                        <span class="tips">图片建议尺寸：640*350px</span>
                    </div>
                    <div class="blank6"></div>
                  <input type="hidden" name="Title[]" value="" />
                </div>
                  <div class="b_r" id="PagesPicDetail_3">
                       <?php echo !empty($banner_img[3])?"<img src=$banner_img[3]>":''?>
                  </div>
                <input type="hidden" name="ImgPathList[]" value="<?php echo !empty($banner_img[3])?$banner_img[3]:''?>" />
              </div>
              <div class="blank9"></div>
              <div class="rows url_select">
                <div class="u_l">链接页面</div>
                <div class="u_r">
                  <input type="text" name='UrlList[]' value="<?php echo !empty($banner_url[3])?$banner_url[3]:''?>">
                </div>
              </div>
            </div>
            <div class="item">
              <div class="rows">
                <div class="b_l"> <strong>图片(5)</strong><span class="tips">大图建议尺寸：
                  <label>640*350</label>
                  px</span><a href="home.php?del=4" value="4"><img src="/static/member/images/ico/del.gif" align="absmiddle" /></a><br />
                    <div class="file" style="margin-top:10px;">
                        <input type="button" id="HomeFileUpload_4" value="选择图片" style="width:80px;" />
                        <input type="hidden" id="ImgPath_4" name="ImgPathList[]" value="<?php echo !empty($banner_img[4])?$banner_img[4]:''?>" />
                        <span class="tips">图片建议尺寸：640*350px</span>
                    </div>
                    <div class="blank6"></div>
                  <input type="hidden" name="Title[]" value="" />
                </div>
                  <div class="b_r" id="PagesPicDetail_4">
                       <?php echo !empty($banner_img[4])?"<img src=$banner_img[4]>":''?>
                  </div>
                <input type="hidden" name="ImgPathList[]" value="<?php echo !empty($banner_img[4])?$banner_img[4]:''?>" />
              </div>
              <div class="blank9"></div>
              <div class="rows url_select">
                <div class="u_l">链接页面</div>
                <div class="u_r">
                  <input type="text" name='UrlList[]' value="<?php echo !empty($banner_url[4])?$banner_url[4]:''?>">
                </div>
              </div>
            </div>
          </div>
          
          <div class="submit">
              <input type="hidden" name="id" value="<?php echo !empty($pt_config)?$pt_config['id']:''?>">
              <input type="submit" name="submit" value="提交保存" />
          </div>
          <input type="hidden" name="PId" value="" />
          <input type="hidden" name="SId" value="" />
          <input type="hidden" name="ContentsType" value="" />
          <input type="hidden" name="no" value="" />
        </form>
      </div>
      <div class="clear"></div>
      
    </div>
  </div>
</div>
</body>
</html>