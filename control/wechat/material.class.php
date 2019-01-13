<?php
class material{
	var $db;
	var $usersid;
	var $table;	
	var $primary_key;

	function __construct($DB,$usersid){
		$this->db = $DB;
		$this->usersid = $usersid;
		$this->table = "wechat_material";
		$this->primary_key = "Material_ID";
	}
	
	public function get_one($itemid){
		$r = $this->db->GetRs($this->table,"*","where Users_ID='".$this->usersid."' and `".$this->primary_key."`=".$itemid);
		return $r;
	}
	
	public function get_one_bymodel($model){
		$r = $this->db->GetRs($this->table,"*","where Users_ID='".$this->usersid."' and Material_Table='".$model."' and Material_TableID=0 and Material_Display=0");
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
	
	public function get_list($fields = "*"){
		$condition = "where Users_ID='".$this->usersid."' order by `".$this->primary_key."` desc";
		$lists = array();
		$this->db->Get($this->table,$fields,$condition);
		while($r = $this->db->fetch_assoc()){
			$lists[] = $r;
		}
		return $lists;
	}
	
	public function get_page_list($fields = "*"){
		$condition = "where Users_ID='".$this->usersid."' and Material_TableID='0' and Material_TableID=0 and Material_Display=1 order by `".$this->primary_key."` desc";
		$lists = array();
		$this->db->getPage($this->table,$fields,$condition,10);
		while($r = $this->db->fetch_assoc()){
			$lists[] = $r;
		}
		return $lists;
	}
	
	public function get_sys_list($materials){
		$lists = array();
		foreach($materials as $v){
			if($v["Material_Table"]<>'0' && $v["Material_TableID"]==0 && $v["Material_Display"]==0){
				$lists[] = $v;
			}
		}
		return $lists;
	}
	
	public function get_diy_list($materials){
		$lists = array();
		foreach($materials as $v){
			if($v["Material_Table"]=='0' && $v["Material_TableID"]==0 && $v["Material_Display"]==1){
				$lists[] = $v;
			}
		}
		return $lists;
	}
}
?>