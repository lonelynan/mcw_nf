<?php
class keyword{
	var $db;
	var $usersid;
	var $table;
	var $primary_key;

	function __construct($DB,$usersid){
		$this->db = $DB;
		$this->usersid = $usersid;
		$this->table = "wechat_keyword_reply";
		$this->primary_key = "Reply_ID";
	}
	
	public function get_one($itemid){
		$r = $this->db->GetRs($this->table,"*","where Users_ID='".$this->usersid."' and `".$this->primary_key."`=".$itemid);
		return $r;
	}
	
	public function get_one_bymodel($model){
		$r = $this->db->GetRs($this->table,"*","where Users_ID='".$this->usersid."' and Reply_Table='".$model."' and Reply_TableID=0 and Reply_Display=0");
		$r["Reply_Keywords"] = trim($r["Reply_Keywords"],"|");
		return $r;
	}
	
	public function edit($data,$itemid){
		$data["Reply_Keywords"] = $this->set($data["Reply_Keywords"]);
		$flag = $this->db->Set($this->table,$data,"where Users_ID='".$this->usersid."' and `".$this->primary_key."`=".$itemid);
		return $flag;
	}
	
	public function add($data){
		$data["Reply_Keywords"] = $this->set($data["Reply_Keywords"]);
		$this->db->Add($this->table,$data);
		return $this->db->insert_id();
	}
	
	public function delete($itemid){
		$flag=$this->db->Del($this->table,"Users_ID='".$this->usersid."' and `".$this->primary_key."`=".$itemid);
		return $flag;
	}
	
	public function get_list($fields = "*"){
		$condition = "where Users_ID='".$this->usersid."' and Reply_Display=1 order by `".$this->primary_key."` desc";
		$lists = array();
		$this->db->getPage($this->table,$fields,$condition,20);
		while($r = $this->db->fetch_assoc()){
			$r["Reply_Keywords"] = trim($r["Reply_Keywords"],"|");
			$lists[] = $r;
		}
		return $lists;
	}
	
	private function set($kw){
	/*
		$html = '';
		$arr = $arr_temp = array();
		$arr = explode("|",$kw);
		foreach($arr as $a){
			if(!$a) continue;
			$arr_temp[] = $a;
		}
		$html = '|'.implode("|",$arr_temp).'|';
		return $html;
	*/
		return $kw;
	}
	
	
}
?>