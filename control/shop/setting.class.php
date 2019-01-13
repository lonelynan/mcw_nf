<?php
class setting{
	var $db;
	var $usersid;
	var $table;
	var $skin_table;
	var $home_table;

	function __construct($DB,$usersid){
		$this->db = $DB;
		$this->usersid = $usersid;
		$this->table = 'shop_config';
		$this->skin_table = 'shop_skin';
		$this->home_table = 'shop_home';
	}
	
	public function get_one($fields='*'){
		$r = $this->db->GetRs($this->table,$fields,"where Users_ID='".$this->usersid."'");
		return $r;
    }
	
	public function get_home($skinid){
		$r = $this->db->GetRs($this->home_table,"*","where Users_ID='".$this->usersid."' and Skin_ID=".$skinid);
		return $r;
	}
	
	public function set_config($data){
		$flag = $this->db->Set($this->table,$data,"where Users_ID='".$this->usersid."'");
		return $flag;
	}
	
	public function set_skin($skinid){
		$data = array(
			"Skin_ID"=>$skinid
		);
		$flag = $this->db->Set($this->table,$data,"where Users_ID='".$this->usersid."'");
		
		$data = array();
		$home = $this->get_home($skinid);
		if(!$home){
			$skin = $this->db->GetRs($this->skin_table,"Skin_Json","where Skin_ID='".$skinid."'");
			$data = array(
				"Users_ID"=>$this->usersid,
				"Skin_ID"=>$skinid,
				"Home_Json"=>$skin["Skin_Json"]
			);
			$Add = $this->db->Add($this->home_table,$data);
			$flag = $flag && $Add;
		}
		return $flag;
	}
	
	public function set_home($skinid, $data){
		$flag = $this->db->Set($this->home_table,$data,"where Users_ID='".$this->usersid."' and Skin_ID=".$skinid);
		return $flag;
	}
	
	public function set_homejson_array($array){
		$data = array();
		if (empty($array)) {
			return 110;
		}
		foreach($array as $value){
			if(is_array($value['Title'])){
				$value['Title'] = json_encode($value['Title']);
			}
			if(is_array($value['ImgPath'])){
				$value['ImgPath'] = json_encode($value['ImgPath']);
			}
			if(is_array($value['Url'])){
				$value['Url'] = json_encode($value['Url']);
			}
			$data[] = $value;
		}
		return $data;
	}
	
	public function get_skin_list(){
		$lists = array();
		$this->db->Get($this->skin_table,"Skin_ID,Skin_ImgPath","where Skin_Status=1 order by Skin_ID asc");
		while($r = $this->db->fetch_assoc()){
			$lists[] = $r;
		}
		return $lists;
	}
}
?>
