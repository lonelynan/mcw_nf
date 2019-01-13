<?php
	/*
	*	优惠劵使用类
	*/
class coupon_use{
	
	private $DB;
	
	function __construct($DB){
		$this->DB = $DB;//数据库连接资源
		
	}
	
	/*
	*	获取优惠劵
	*	@param int $Coupon_ID 优惠劵id
	*	@return array 优惠劵信息
	*/
	function coupon_check($Coupon_ID,$User_ID,$total_price,$Biz_ID,$UsersID){
		
			$r = $this->DB->GetRs("user_coupon_record","*","where Coupon_ID=".$Coupon_ID." and Users_ID='".$UsersID."' and User_ID=".$User_ID." and Coupon_UseArea=1 and (Coupon_UsedTimes=-1 or Coupon_UsedTimes>0) and Coupon_StartTime<=".time()." and Coupon_EndTime>=".time()." and Coupon_Condition<=".$total_price." and Biz_ID=".$Biz_ID);
			
			if($r){
				$Data["Coupon_ID"] = $Coupon_ID;
				if($r["Coupon_UseType"]==0 && $r["Coupon_Discount"]>0 && $r["Coupon_Discount"]<1){
					$cash = $total_price * $r["Coupon_Discount"];
					$cash = number_format($cash, 2, '.', '');
					$Data["Coupon_Discount"]=$r["Coupon_Discount"];
				}
				if($r['Coupon_UseType']==1 && $r['Coupon_Cash']>0){
					$cash = $r['Coupon_Cash'];
				}
				$Data["Coupon_Cash"] = $cash;
				//$total_price = $total_price - $cash;
				$Logs_Price = $cash;
				
				if($r['Coupon_UsedTimes']>=1){
					$this->DB->Set("user_coupon_record","Coupon_UsedTimes=Coupon_UsedTimes-1","where Users_ID='".$UsersID."' and User_ID='".$_SESSION[$UsersID."User_ID"]."' and Coupon_ID=".$_POST['CouponID'][$Biz_ID]);
				}
				$item = $this->DB->GetRs("user","User_Name","where User_ID=".$_SESSION[$UsersID."User_ID"]);
				$Data1=array(
					'Users_ID'=>$UsersID,
					'User_ID'=>$_SESSION[$UsersID."User_ID"],
					'User_Name'=>$item["User_Name"],
					'Coupon_Subject'=>"优惠券编号：".$Coupon_ID,
					'Logs_Price'=>$Logs_Price,
					'Coupon_UsedTimes'=>$r['Coupon_UsedTimes']>=1?$r['Coupon_UsedTimes']-1:$r['Coupon_UsedTimes'],
					'Logs_CreateTime'=>time(),
					"Biz_ID"=>$Biz_ID,
				
					'Operator_UserName'=>"微商城购买商品"
				);
				$this->DB->Add("user_coupon_logs",$Data1);
			}else{
				$Data["Coupon_ID"]=0;
				$Data["Coupon_Discount"]=0;
				$Data["Coupon_Cash"] = 0;
			}
		return $Data;
	}
}
?>