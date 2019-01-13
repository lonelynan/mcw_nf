<?php
!defined("REQUEST_METHOD") && define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
!defined("IS_GET") && define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
!defined("IS_POST") && define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);

use Illuminate\Database\Capsule\Manager as DB;

$UsersID = !empty($_SESSION['Users_ID']) ? $_SESSION['Users_ID'] : '';
if(!$UsersID)
{
    header("location:/member/login.php");
    exit;
}
$rsShipping = DB::table('third_shipping')->where('Users_ID', $UsersID)->first();
if(!empty($rsShipping)){
	$rsThirdConfig = DB::table('third_shipping_config')->where(['Tid' => $rsShipping['ID']])->first();
	if(!empty($rsThirdConfig['ext'])){
		$ext = json_decode($rsThirdConfig['ext'], true);
		$rsShipping = array_merge($rsShipping, $ext);
	}
}
if(IS_POST){
	$Name = trim($request->input('Name'));
	$Tag = trim($request->input('Tag'));
	$Status = $request->input('Status');
	$ID = $request->input('ID');
	$app_key = trim($request->input('app_key'));
	$app_secret = trim($request->input('app_secret'));
	$domain = trim($request->input('domain'));
	$source_id = trim($request->input('source_id'));
	$config_id = true;
	
	if(empty($Name)){
		echo '<script language="javascript">alert("名称不能为空");location.href="javascript:history.go(-1)";</script>';
		exit;
	}
	
	if(empty($Tag)){
		echo '<script language="javascript">alert("标签不能为空");location.href="javascript:history.go(-1)";</script>';
		exit;
	}
	
	if(empty($app_key)){
		echo '<script language="javascript">alert("appkey不能为空");location.href="javascript:history.go(-1)";</script>';
		exit;
	}
	
	if(empty($app_secret)){
		echo '<script language="javascript">alert("appsecret不能为空");location.href="javascript:history.go(-1)";</script>';
		exit;
	}
	
	if(empty($source_id)){
		echo '<script language="javascript">alert("请填写商户号");location.href="javascript:history.go(-1)";</script>';
		exit;
	}
	if($source_id=='73753'){
		$domain = 'newopen.qa.imdada.cn';
	}else{
		$domain = 'newopen.imdada.cn';
	}
	try{
		begin_trans();
		if(!empty($rsShipping)){
			$flag = DB::table('third_shipping')->where(['Users_ID' => $UsersID, 'ID' => $ID])->update([
				'Name' => $Name,
				'Tag' => $Tag,
				'Status' => $Status
			]);
			if($flag===false){
				throw new Exception("修改配置失败");
			}
			$rsThirdConfig = DB::table('third_shipping_config')->where(['Tid' => $ID])->first();
			if(!empty($rsThirdConfig)){
				$config_flag = DB::table('third_shipping_config')->where(['Tid' => $ID])->update([
					'ext' => json_encode([
						'app_key' => $app_key,
						'app_secret' => $app_secret,
						'domain' => $domain,
						'source_id' => $source_id
					])
				]);
			}else{
				$config_flag = DB::table('third_shipping_config')->insertGetId([
					'Tid' => $ID,
					'ext' => json_encode([
						'app_key' => $app_key,
						'app_secret' => $app_secret,
						'domain' => $domain,
						'source_id' => $source_id
					])
				]);
			}
			
			if($config_flag === false){
				throw new Exception("修改开发者配置失败");
			}
		}else{
			$shipping_id = DB::table('third_shipping')->insertGetId([
				'Users_ID' => $UsersID,
				'Name' => $Name,
				'Tag' => $Tag,
				'Status' => $Status,
				'CreateTime' => time()
			]);
			if(!$shipping_id){
				throw new Exception("修改配置失败");
			}

			$config_id = DB::table('third_shipping_config')->insertGetId([
				'Users_ID' => $UsersID,
				'Tid' => $shipping_id,
				'ext' => json_encode([
					'app_key' => $app_key,
					'app_secret' => $app_secret,
					'domain' => $domain,
					'source_id' => $source_id
				])
			]);
			
			if(!$config_id){
				throw new Exception("修改配置失败");
			}
		}
		commit_trans();
		echo '<script language="javascript">alert("保存成功");window.location="dada_config.php";</script>';
	}catch(Exception $e){
		back_trans();
		echo '<script language="javascript">alert("保存失败");location.href="javascript:history.go(-1)";</script>';
	}
	exit;
}
$jcurid = 2;
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <link href='/static/css/global.css' rel='stylesheet' type='text/css'/>
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css'/>
    <script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>

    <style type="text/css">
		input{outline:none;margin:0 10px;padding-left:5px;border: 1px solid #ddd;border-radius: 5px;height: 32px;width: 210px;}
		input[type='radio']{width:20px;height:20px;margin-left:40px;}
		 ul li dl dd {height:60px;}
		#iframe_page .btn_green{display:inline;float:none;}
    </style>
</head>

<body>
<!--[if lte IE 9]>
<script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/wechat.css' rel='stylesheet' type='text/css'/>
        <div class="r_con_wrap">
            <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
                <thead>
                <tr>
                    <td width="34%">达达配送配置</td>
                    <td width="33%"></td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td valign="top" class="payment">
                        <form action="?" method="post">
							<input type="hidden" name="ID" value="<?php echo !empty($rsShipping["ID"])?$rsShipping["ID"]:0; ?>" />
							<input type="hidden" name="domain" value="<?=!empty($rsShipping["domain"]) ? $rsShipping["domain"] : '' ?>" />
							<input type="hidden" name="Tag" value="dada"/>
                            <ul>
                                <li>
                                    
                                    <dl>
                                        <dd>
											&nbsp;&nbsp;名称<input type="text" name="Name" value="<?php echo !empty($rsShipping["Name"])?$rsShipping["Name"]:''; ?>"/>
										</dd>
										<dd>
											&nbsp;&nbsp;app_key<input type="text" name="app_key" value="<?php echo !empty($rsShipping["app_key"])?$rsShipping["app_key"]:''; ?>"/>
										</dd>
										<dd>
											&nbsp;&nbsp;app_secret<input type="text" name="app_secret" value="<?php echo !empty($rsShipping["app_secret"])?$rsShipping["app_secret"]:''; ?>"/>
										</dd>
										<dd>
											&nbsp;&nbsp;商户号<input type="text" name="source_id" value="<?php echo !empty($rsShipping["source_id"])?$rsShipping["source_id"]:''; ?>"/>
										</dd>
										<dd>
											<label>
											<input type="radio" value="1" name="Status" <?php echo isset($rsShipping["Status"]) && $rsShipping["Status"] == 1 ? 'checked' : ''; ?>/>启用
											</label>
											<label>
											<input type="radio" value="0" name="Status" <?php echo isset($rsShipping["Status"]) && $rsShipping["Status"] == 0 ? 'checked' : ''; ?>/>禁用
											</label>
										</dd>
                                    </dl>
                                </li>
                            </ul>
                            <div class="submit">
                                <input type="submit" class="btn_green" name="submit_button" value="保存"/>
                            </div>
                            <input type="hidden" name="action" value="payment">
                        </form>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script type="text/javascript">
    function show_pay_ment(id) {
        if (document.getElementById('check_' + id).checked == false) {
            document.getElementById('pay_' + id).style.display = 'none';
        } else {
            document.getElementById('pay_' + id).style.display = 'block';
        }
    }
</script>
</body>
</html>
