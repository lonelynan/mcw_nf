<?php
class attention{
	var $db;
	var $usersid;
	var $table;

	function __construct($DB,$usersid){
		$this->db = $DB;
		$this->usersid = $usersid;
		$this->table = "wechat_attention_reply";
	}
	
	public function get_one(){
		$this->check();
		$r = $this->db->GetRs($this->table,"*","where Users_ID='".$this->usersid."'");
		return $r;
	}
	
	public function edit($data){
		$flag = $this->db->Set($this->table,$data,"where Users_ID='".$this->usersid."'");
		return $flag;
	}
	
	private function add(){
		$data=array(
			"Users_ID"=>$this->usersid,
			"Reply_TextContents"=>"非常高兴认识你，新朋友！"
		);
		$this->db->Add($this->table,$Data);
	}
	
	private function check(){
		$r = $this->db->GetRs($this->table,"count(*) as num","where Users_ID='".$this->usersid."'");
		if($r["num"]==0){
			$this->add();
		}
	}
}
?>