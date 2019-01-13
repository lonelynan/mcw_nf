<?php
class unioncate{
	var $db;
	var $usersid;
	var $table;	
	var $cate_table;

	function __construct($DB,$usersid){
		$this->db = $DB;
		$this->usersid = $usersid;
		$this->cate_table = "biz_union_category";
	}
	
	public function get_cat_one($itemid){
		$r = $this->db->GetRs($this->cate_table,"*","where Users_ID='".$this->usersid."' and Category_ID=".$itemid);
		return $r;
	}
	
	public function edit_cat($data,$itemid){
		$flag = $this->db->Set($this->cate_table,$data,"where Users_ID='".$this->usersid."' and Category_ID=".$itemid);
		return $flag;
	}
	
	public function add_cat($data){
		$flag = $this->db->Add($this->cate_table,$data);
		return $flag;
	}
	
	public function delete_cat($itemid){
		$r = $this->db->GetRs($this->cate_table,"count(Category_ID) as num","where Users_ID='".$this->usersid."' and Category_ParentID=".$itemid);
		if($r["num"]>0){
			return array("status"=>0,"msg"=>"该分类下有子分类，不能删除");
		}
		$r = $this->db->GetRs("biz","count(Biz_ID) as num","where Users_ID='".$this->usersid."' and Category_ID=".$itemid);
		if($r["num"]>0){
			return array("status"=>0,"msg"=>"该分类下有商家，不能删除");
		}
		$flag=$this->db->Del($this->cate_table,"Users_ID='".$this->usersid."' and Category_ID=".$itemid);
		return $flag ? array("status"=>1,"msg"=>"删除成功") : array("status"=>0,"msg"=>"删除失败");
	}
	
	public function get_cate_list($fields="*",$condition){
		$lists = array();
		$this->db->Get($this->cate_table,$fields,$condition);
		while($r = $this->db->fetch_assoc()){
			if($r["Category_ParentID"]==0){
				$lists[$r["Category_ID"]] = $r;
			}else{
				$lists[$r["Category_ParentID"]]["child"][] = $r;
			}
		}
		return $lists;
	}
}
?>