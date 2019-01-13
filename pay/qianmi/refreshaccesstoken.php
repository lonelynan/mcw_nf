<?php
class refreshaccesstoken{

	function __construct(){
	}
	
	public function get_access_token($paras,$appSecret){
		$data = array("status"=>0);
		ksort($paras);
		$plain_text="";
		foreach($paras as  $key => $value) {
			$plain_text .= $key.$value;
		}
		$plain_text  = $appSecret.$plain_text.$appSecret;
		$sign = strtoupper(sha1($plain_text));
		$paras['sign'] = $sign;
		
		ksort($paras);
		$url_params = "";
		foreach ($paras as $key => $value) {
			$url_params .= "&".$key."=".$value;
		}
		$url_params = ltrim($url_params,"&");
		
		$url = "http://oauth.qianmi.com/token";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $url_params);
		$return = curl_exec ($ch);
		curl_close ( $ch );
		$res = json_decode($return,true);
		
		if(empty($res['errorMessage'])){
			$data = array(
				'status'=>1,
				'access_token'=>$res['data']['access_token'],
				'refresh_token'=>$res['data']['refresh_token'],
				'expiretime'=>(time()+intval($res['data']['expires_in'])-600)				
			);
		}
		
		return $data;
    }	
}
?>