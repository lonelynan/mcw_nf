<?php
	/*
	*	优惠劵操作类
	*/
class coupon{
	
	private $DB;
	private $BIZ_ID;
	private $key_word;
	
	function __construct($DB,$BIZ_ID,$type='user'){
		$this->DB = $DB;//数据库连接资源
		$this->BIZ_ID = $BIZ_ID;//商家id
		if($type == 'user'){
			$this->key_word = 'Biz_ID';
		}else{
			$this->key_word = 'Users_ID';
		}
	}
	
	/*
	*	获取优惠劵
	*	@param int $Coupon_ID 优惠劵id
	*	@return array 优惠劵信息
	*/
	function coupon_get($Coupon_ID){
		
		$rsCoupon=$this->DB->GetRs("user_coupon","*","where Biz_ID=".$this->BIZ_ID." and Coupon_ID=".$Coupon_ID);
		
		return $rsCoupon;
		
	}
	
	/*
	*	优惠劵分页
	*	@param int $Coupon_ID 优惠劵id
	*	@return array $rsCoupon优惠劵信息
	*/
	function coupon_get_page($pageSize=10){

		$rsCoupon = $this->DB->getPage("user_coupon","*","where Biz_ID=".$this->BIZ_ID." order by Coupon_CreateTime desc",$pageSize);
		
		return $rsCoupon;
	}
	
	/*
	*	优惠劵添加操作
	*	@param	array $Post_Data 
	*	@echo <script> 操作状态 
	*/
	public function coupon_add($Post_Data){
		$rsBiz=$this->DB->GetRs("biz","*","where Biz_ID=".$this->BIZ_ID);
		$Time=empty($Post_Data["Time"])?array(time(),time()):explode(" - ",$Post_Data["Time"]);
		$StartTime=strtotime($Time[0]);
		$EndTime=strtotime($Time[1]);
		$Data=array(
			"Coupon_Subject"=>$Post_Data["Subject"],
			"Biz_ID"=>$this->BIZ_ID,
			"Coupon_PhotoPath"=>$Post_Data["PhotoPath"],
			"Coupon_UsedTimes"=>$Post_Data["UsedTimes"],
			"Coupon_StartTime"=>$StartTime,
			"Coupon_EndTime"=>$EndTime,
			"Coupon_Description"=>$Post_Data["Description"],
			"Coupon_UseType"=>$Post_Data["UseType"],
			"Coupon_Condition"=>empty($Post_Data["Condition"]) ? 0 : intval($Post_Data["Condition"]),
			"Coupon_Discount"=>empty($Post_Data["Discount"]) ? 0 : number_format($Post_Data["Discount"],2),
			"Coupon_Cash"=>empty($Post_Data["Cash"]) ? 0 : intval($Post_Data["Cash"]),
			"Coupon_CreateTime"=>time(),
			"Coupon_UseArea"=>1,
			"Users_ID"=>$rsBiz["Users_ID"]
		);
		$Flag=$this->DB->Add("user_coupon",$Data);
		if($Flag){
			echo '<script language="javascript">alert("添加成功");window.location.href="coupon_list.php";</script>';
		}else{
			echo '<script language="javascript">alert("添加失败");history.back();</script>';
		}
	}
	
	/*
	*	优惠劵修改操作
	*	@param	array $Post_Data 
	*	@echo <script> 操作状态 
	*/
	function coupon_set($Post_Data){
		$CouponID = $Post_Data["CouponID"];
		$Time=empty($Post_Data["Time"])?array(time(),time()):explode(" - ",$Post_Data["Time"]);
		$StartTime=strtotime($Time[0]);
		$EndTime=strtotime($Time[1]);
		$Data=array(
			"Coupon_Subject"=>$Post_Data["Subject"],
			"Coupon_PhotoPath"=>$Post_Data["PhotoPath"],
			"Coupon_UsedTimes"=>$Post_Data["UsedTimes"],
			"Coupon_StartTime"=>$StartTime,
			"Coupon_EndTime"=>$EndTime,
			"Coupon_Description"=>$Post_Data["Description"],
			"Coupon_UseType"=>$Post_Data["UseType"],
			"Coupon_Condition"=>$Post_Data["Condition"],
			"Coupon_Discount"=>$Post_Data["Discount"],
			"Coupon_Cash"=>$Post_Data["Cash"]		
		);
		
		$Flag=$this->DB->Set("user_coupon",$Data,"where Coupon_ID=".$CouponID);
		if($Flag){
			echo '<script language="javascript">alert("修改成功");window.location.href="coupon_list.php";</script>';
		}else{
			echo '<script language="javascript">alert("修改失败");history.back();</script>';
		}
	}
	
	/*
	*	优惠劵删除操作
	*	@param	int $Coupon_ID 
	*	@echo <script> 操作状态 
	*/
	function coupon_del($Coupon_ID){
		$Flag = $this->DB->Del("user_coupon","Biz_ID=".$this->BIZ_ID." and Coupon_ID=".$Coupon_ID);
		if($Flag){
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			echo '<script language="javascript">alert("删除失败");history.go(-1);</script>';
		}
	}
	/*
	*	优惠劵删除操作
	*	@param	int $Coupon_ID 
	*	@echo <script> 操作状态 
	*/
	function coupon_logs_page($pageSize=10){
		$res = $this->DB->getPage("user_coupon_logs","*","where Biz_ID=".$this->BIZ_ID." order by Logs_CreateTime desc",$pageSize);
		echo $this->BIZ_ID;
		var_dump($res);
		return $res;
	}
}
?>