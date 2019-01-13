<?php
class property{
	var $db;
	var $usersid;
	var $bizid;
	var $table;	
	var $type_table;
	var $attr_table;
	var $con;

	function __construct($DB,$usersid,$bizid){
		$this->db = $DB;
		$this->usersid = $usersid;
		$this->bizid = $bizid;
		$this->table = "shop_property";
		$this->type_table = "shop_property_type";
		$this->attr_table = "shop_products_attr";
		$this->con = "where Users_ID='".$this->usersid."' and Biz_ID=".$this->bizid;
	}
	//属性
	public function get_one($itemid){
		$condition = $this->con." and Property_ID=".$itemid;
		$r = $this->db->GetRs($this->table,"*",$condition);
		return $r;
	}
	
	public function edit($data,$itemid){
		$condition = $this->con." and Property_ID=".$itemid;
		$flag = $this->db->Set($this->table,$data,$condition);
		return $flag;
	}
	
	public function add($data){
		$flag = $this->db->Add($this->table,$data);
		return $flag;
	}
	
	public function delete($itemid){
		$condition = "where Property_ID=".$itemid;
		$r = $this->db->GetRs($this->attr_table,"count(Property_ID) as num",$condition);
		if($r["num"]>0){
			return array("status"=>0,"msg"=>"已有商品设置了该属性，不能删除");
		}
		$condition = str_replace('where ','',$this->con)." and Property_ID=".$itemid;
		$flag=$this->db->Del($this->table,$condition);
		if($flag){
			return array("status"=>1,"msg"=>"删除成功");
		}else{
			return array("status"=>0,"msg"=>"删除失败");
		}
	}
	
	public function get_list($fields = "*",$condition){
		$lists = array();
		$this->db->Get($this->table,$fields,$condition);
		while($r = $this->db->fetch_assoc()){
			$lists[] = $r;
		}
		return $lists;
	}
	
	public function get_page_list($fields = "*",$condition,$pagesize=20){
		$lists = array();
		$this->db->getPage($this->table,$fields,$condition,$pagesize);
		while($r = $this->db->fetch_assoc()){
			$lists[] = $r;
		}
		return $lists;
	}
	
	//属性类型
	public function get_one_type($itemid){
		$condition = $this->con." and Type_ID=".$itemid;
		$r = $this->db->GetRs($this->type_table,"*",$condition);
		return $r;
	}
	
	public function edit_type($data,$itemid){
		$condition = $this->con." and Type_ID=".$itemid;
		$flag = $this->db->Set($this->type_table,$data,$condition);
		return $flag;
	}
	
	public function add_type($data){
		$flag = $this->db->Addall($this->type_table,$data);
		return $flag;
	}
	
	public function delete_type($itemid){
		$condition = $this->con." and Type_ID=".$itemid;
		$r = $this->db->GetRs($this->table,"count(Property_ID) as num",$condition);
		if($r["num"]>0){
			return array("status"=>0,"msg"=>"该类型下有属性，不能删除");
		}
		
		$condition = str_replace('where ','',$this->con)." and Type_ID=".$itemid;
		$flag=$this->db->Del($this->type_table,$condition);
		if($flag){
			return array("status"=>1,"msg"=>"删除成功");
		}else{
			return array("status"=>0,"msg"=>"删除失败");
		}
	}
	
	public function get_list_type($fields = "*",$condition=""){
		$condition = $this->con.$condition;
		$lists = array();
		$this->db->Get($this->type_table,$fields,$condition);
		while($r = $this->db->fetch_assoc()){
			$lists[] = $r;
		}
		return $lists;
	}
}
?>