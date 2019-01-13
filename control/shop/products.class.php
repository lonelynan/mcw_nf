<?php
class products{
	var $db;
	var $usersid;
	var $table;	
	var $cate_table;

	function __construct($DB,$usersid){
		$this->db = $DB;
		$this->usersid = $usersid;
		$this->table = "shop_products";
		$this->cate_table = "shop_category";
	}
	
	public function get_one($itemid){
		$r = $this->db->GetRs($this->table,"*","where Users_ID='".$this->usersid."' and Products_ID=".$itemid);
		return $r;
	}
	
	public function edit($data,$itemid){
		$products = $this->get_one($itemid);
		$old = $products['Products_Description'];
		$JSON=json_decode($products['Products_JSON'],true);
		if(isset($JSON["ImgPath"])){
			foreach($JSON["ImgPath"] as $v){
				$old .= $v;
			}
		}
		$new = $data['Products_Description'];
		$Json=json_decode($data['Products_JSON'],true);
		if(isset($Json["ImgPath"])){
			foreach($Json["ImgPath"] as $n){
				$new .= $n;
			}
		}
		delete_diff($new, $old, $products["Biz_ID"]);
		$flag = $this->db->Set($this->table,$data,"where Users_ID='".$this->usersid."' and Products_ID=".$itemid);
		return $flag;
	}
	
	public function add($data){
		$this->db->Add($this->table,$data);
		return $this->db->insert_id();
	}
	
	public function delete($itemid){
		$products = $this->get_one($itemid);
		$JSON=json_decode($products['Products_JSON'],true);
		if(isset($JSON["ImgPath"])){
			foreach($JSON["ImgPath"] as $v){
				delete_upload($v,$products["Biz_ID"]);
			}
		}
		if($products['Products_Description']) delete_local($products['Products_Description'], $products["Biz_ID"]);
		$flag=$this->db->Del($this->table,"Users_ID='".$this->usersid."' and Products_ID=".$itemid);
		return $flag;
	}
	
	public function get_page_list($fields = "*",$condition){
		$lists = array();
		$this->db->getPage($this->table,$fields,$condition,20);
		while($r = $this->db->fetch_assoc()){
			$lists[] = $r;
		}
		return $lists;
	}
	
	public function get_products_list($fields = "*",$condition,$pagesize = 0){
		$lists = array();
		$this->db->Get($this->table,$fields,$condition,$pagesize);
		while($r = $this->db->fetch_assoc()){
			$lists[] = $r;
		}
		return $lists;
	}
	
	public function act_products_list($lists=array()){
		if(empty($lists)){
			return $lists;
		}
		$biz = array();
		foreach($lists as $h=>$l){
			$biz[] = $l['Biz_ID'];
		}

		$biz_id = array_unique($biz);
		$biz_ids = implode($biz_id,',');
		$this->db->Get('biz','Biz_ID','where Is_Union=1 and Biz_ID in ('.$biz_ids.')');
		$is_union = $this->db->toArray();

		if(empty($is_union)){return $lists;}
		
		$keys = array();
		foreach($is_union as $k=>$v){
			$key = array_keys($biz,$v['Biz_ID']);
			foreach($key as $p=>$q){
				$keys[] = $q;
			}
		}
		foreach($keys as $m=>$n){
			unset($lists[$n]);
		}
		
		return $lists;
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
		$r = $this->db->GetRs($this->table,"count(Products_ID) as num","where Users_ID='".$this->usersid."' and Products_Category like '%".(",".$itemid.",")."%'");
		if($r["num"]>0){
			return array("status"=>0,"msg"=>"该分类下有产品，不能删除");
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
	//商品所属分类名称
	public function get_cate_list_products($condition){
		$lists = array();
		$this->db->Get($this->cate_table,"Category_ID,Category_Name,Category_ParentID",$condition);
		while($r = $this->db->fetch_assoc()){
			if($r["Category_ParentID"]==0){
				$lists[$r["Category_ID"]]["name"] = $r["Category_Name"];
			}else{
				$lists[$r["Category_ParentID"]]["child"][] = $r["Category_Name"];
			}
		}
		return $lists;
	}
}
?>