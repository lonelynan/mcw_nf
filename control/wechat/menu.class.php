<?php
class menu{
	var $db;
	var $usersid;
	var $table;
	var $primary_key;

	function __construct($DB,$usersid){
		$this->db = $DB;
		$this->usersid = $usersid;
		$this->table = "wechat_menu";
		$this->primary_key = "Menu_ID";
	}
	
	public function get_one($itemid){
		$r = $this->db->GetRs($this->table,"*","where Users_ID='".$this->usersid."' and `".$this->primary_key."`=".$itemid);
		return $r;
	}
	
	public function edit($data,$itemid){
		$flag = $this->db->Set($this->table,$data,"where Users_ID='".$this->usersid."' and `".$this->primary_key."`=".$itemid);
		return $flag;
	}
	
	public function add($data){
		$this->db->Add($this->table,$data);
		return $this->db->insert_id();
	}
	
	public function delete($itemid){
		$flag=$this->db->Del($this->table,"Users_ID='".$this->usersid."' and `".$this->primary_key."`=".$itemid);
		return $flag;
	}
	
	public function get_list($fields = "*",$type=0){
		$condition = "where Users_ID='".$this->usersid."'";
		if($type == 1){//parent
			$condition .= " and Menu_ParentID=0";
		}
		$condition .= " order by Menu_ParentID asc,Menu_Index asc";
		
		$lists = array();
		$this->db->Get($this->table,$fields,$condition);
		while($r = $this->db->fetch_assoc()){
			if($type==1){
				$lists[] = $r;
			}else{
				if($r["Menu_ParentID"]==0){
					$lists[$r["Menu_ID"]] = $r;
				}else{
					$lists[$r["Menu_ParentID"]]["child"][] = $r;
				}
			}
		}
		return $lists;
	}
	
	public function publish(){
		$menus = array();
		$lists = $this->get_list("*",0);
		foreach($lists as $parent){
			if(!empty($parent["child"])){
				$Data=array(
					"name"=>$parent["Menu_Name"],
					"sub_button"=>array()
				);
				foreach($parent["child"] as $child){
					if($child["Menu_MsgType"]==0){
						$Data["sub_button"][]=array(
							"type"=>"click",
							"name"=>$child["Menu_Name"],
							"key"=>$child["Menu_TextContents"]
						);
					}elseif($child["Menu_MsgType"]==1){
						$Data["sub_button"][]=array(
							"type"=>"click",
							"name"=>$child["Menu_Name"],
							"key"=>"MaterialID_".$child["Menu_MaterialID"]
						);
					}elseif($child["Menu_MsgType"]==2){
						$Data["sub_button"][]=array(
							"type"=>"view",
							"name"=>$child["Menu_Name"],
							"url"=>$child["Menu_Url"]
						);
					}
				}
				$menus["button"][]=$Data;
			}else{
				if($parent["Menu_MsgType"]==0){
					$Data=array(
						"type"=>"click",
						"name"=>$parent["Menu_Name"],
						"key"=>$parent["Menu_TextContents"]
					);
				}elseif($parent["Menu_MsgType"]==1){
					$Data=array(
						"type"=>"click",
						"name"=>$parent["Menu_Name"],
						"key"=>"MaterialID_".$parent["Menu_MaterialID"]
					);
				}elseif($parent["Menu_MsgType"]==2){
					$Data=array(
						"type"=>"view",
						"name"=>$parent["Menu_Name"],
						"url"=>$parent["Menu_Url"]
					);
				}
				$menus["button"][]=$Data;
			}
		}
		require_once($_SERVER["DOCUMENT_ROOT"].'/control/weixin/weixin_token.class.php');
		$weixin_token = new weixin_token($this->db,$this->usersid);
		$access_token = $weixin_token->get_access_token();
		if(!$access_token){
			return array("errcode"=>"菜单发布失败");
		}
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
		require_once($_SERVER["DOCUMENT_ROOT"].'/control/weixin/Weixin.class.php');
		$Weixin = new Weixin();
		$data = $Weixin->curl_post($url, json_encode($menus,JSON_UNESCAPED_UNICODE));
		return $data;
	}
	
	public function deletemenu(){
		require_once($_SERVER["DOCUMENT_ROOT"].'/control/weixin/weixin_token.class.php');
		$weixin_token = new weixin_token($this->db,$this->usersid);
		$access_token = $weixin_token->get_access_token();
		if(!$access_token){
			return array("errcode"=>"菜单删除失败");
		}
		$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$access_token;
		require_once($_SERVER["DOCUMENT_ROOT"].'/control/weixin/Weixin.class.php');
		$Weixin = new Weixin();
		$data = $Weixin->curl_get($url);
		return $data;
	}
}
?>