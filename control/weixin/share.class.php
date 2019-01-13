<?php
class share{
	var $db;
	var $usersid;
	var $noncestr;
	var $timestamp;

	function __construct($DB,$usersid){
		$this->db = $DB;
		$this->usersid = $usersid;
		$this->noncestr = 'wybaoshare';
		$this->timestamp = time();
	}
	
	private function jssdk_get_ticket(){
		$get = 1;
		$ticket = "";
		$item = $this->db->GetRs("users_access_token","jssdk_ticket,jssdk_expires_in","where usersid='".$this->usersid."' order by jssdk_expires_in desc");
		if($item){
			$diff = intval($item["jssdk_expires_in"]) - 300;
			if($item["jssdk_ticket"] && $diff>=time()){
				$get = 0;
				$ticket = $item["jssdk_ticket"];
			}
		}
		
		if($get==1){
			if(!class_exists('weixin_token')){
				require_once($_SERVER["DOCUMENT_ROOT"].'/control/weixin/weixin_token.class.php');
			}
			
			$weixin_token = new weixin_token($this->db,$this->usersid);
			$access_token = $weixin_token->get_access_token();
			if($access_token){
				$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
				require_once($_SERVER["DOCUMENT_ROOT"].'/control/weixin/Weixin.class.php');
				$Weixin = new Weixin();
				$data = $Weixin->curl_get($url);
				if(!empty($data["ticket"])){
					$ticket = $data["ticket"];
					$Data = array(
						"jssdk_ticket"=>$data["ticket"],
						"jssdk_expires_in"=>time()+$data["expires_in"]
					);
					$this->db->Set("users_access_token",$Data,"where usersid='".$this->usersid."'");
				}
			}
		}
		
		return $ticket;
	}
	
	private function jssdk_get_signature(){
		$ticket = $this->jssdk_get_ticket();		
		if($ticket==""){
			return "";
		}else{
			
			$tmpArr = array(
				"jsapi_ticket"=>$ticket,
				"noncestr"=>$this->noncestr,
				"timestamp"=>$this->timestamp,
				"url"=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']
			);
			
			ksort($tmpArr);
			$html = "";
			foreach($tmpArr as $k=>$v){
				$html = $html."&".$k."=".$v;
			}
			$s = substr($html,1);
			$tmpArr["signature"] = sha1($s);
			return $tmpArr;
		}    
	}
	
	public function getshareinfo($rsConfig,$Share){
		$users = $this->db->GetRs("users","Users_WechatAppId,Users_WechatAppSecret","where Users_ID='".$this->usersid."'");
		$temArr = array();
		if($users["Users_WechatAppId"] && $users["Users_WechatAppSecret"]){
			$temArr = $this->jssdk_get_signature();
			if(is_array($temArr)){
				$temArr["appId"] = $users["Users_WechatAppId"];
				$temArr["title"] = $Share["title"] ? $Share["title"] : $rsConfig["ShopName"];
				$temArr["desc"] = $Share["desc"] ? str_replace(array("\r\n", "\r", "\n"), "", $Share["desc"]) : $rsConfig["ShopName"];
				$temArr["img_url"] = $Share["img"] ? $Share["img"] : $rsConfig["ShopLogo"];
				$temArr["link"] = $Share["link"] ? $Share["link"] : $temArr["url"];
				
				if(!empty($_SESSION[$this->usersid."User_ID"])){
					$r = $this->db->GetRs("user","Is_Distribute","where Users_ID='".$this->usersid."' and User_ID=".$_SESSION[$this->usersid."User_ID"]);
					$share_addscore = 0;
					if($rsConfig["Distribute_Share"] && $r["Is_Distribute"]==1){
						$share_addscore = $rsConfig["Distribute_ShareScore"];
					}elseif($rsConfig["Member_Share"] && $r["Is_Distribute"]==0){
						$share_addscore = $rsConfig["Member_ShareScore"];
					}
					if($share_addscore>0){
						$transfer = $temArr["link"];
						$Data = array(
							"Users_ID"=>$this->usersid,
							"User_ID"=>$_SESSION[$this->usersid."User_ID"],
							"Type"=>"shop",
							"Score"=>$share_addscore,
							"CreateTime"=>time(),
							"Transfer"=>$transfer
						);
						$Flag = $this->db->Add("share_record",$Data);
						if($Flag){
							$temArr["link"] = 'http://'.$_SERVER['HTTP_HOST'].'/api/'.$this->usersid.'/share_recieve/'.$this->db->insert_id().'/';
						}
					}
				}
			}
		}
		return $temArr;
	}
}
?>