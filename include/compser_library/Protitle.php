<?php
//分销商爵位晋级类
// use Illuminate\Database\Capsule\Manager as DB;
class ProTitle {
		
		private $Users_ID;
		private $User_ID;
		
		public function __construct($Users_ID,$UserID){
			$this->Users_ID = $Users_ID;
			$this->User_ID =  $UserID;
		}

		/**
		 * 获取指定用户下级所有分销商用户UserID
		 * @param  int $userid 	[用户userid]
		 * @return array 	所有直接下属一级分销商用户userid]
		 */
		public function get_sons_dis_userid($userid) {

			$dis_userid_arr = Dis_Account::where('invite_id', $userid)->lists('User_ID');

			return $dis_userid_arr;
		}
		
		//获取下级
		function get_sons($level,$UID){
			$temp = Dis_Account::where('Dis_Path','like','%,'.$UID.',%')->get(array('User_ID','Dis_Path'))
					->map(function($account){
						return $account->toArray();
					})->all();
			$list = array();

			foreach($temp as $t){
				$dispath = substr($t['Dis_Path'],1,-1);
				$arr = explode(',',$dispath);
				$key = array_search($UID,$arr);
				
				if(count($arr)-$key <= $level){
					$list[] = $t['User_ID'];
				}else{
					continue;
				}
			}

			return $list;
		}
				
		/*--edit20150516--*/
		function up_nobility_level($Sales_Groupnow = 0){
			$dis_config = Dis_Config::where('Users_ID',$this->Users_ID)->first();
			if(empty($dis_config->Pro_Title_Level)){
				return true;
			}
	
			$protitles = json_decode($dis_config->Pro_Title_Level,true);
			$protitles_temp = array_reverse($protitles,true);
			
			//过滤不启用的爵位(名称为空)
			$protitless = array();
			foreach($protitles_temp as $pk=>$pv){
				if(!empty($pv['Name'])){
					$protitless[$pk]=$pv;
				}
			}

			//爵位全部未启用
			if (empty($protitless)) return true;

			$disaccount =  Dis_Account::Multiwhere(array('Users_ID'=>$this->Users_ID,'User_ID'=>$this->User_ID))
					 ->first();
			if(empty($disaccount->Users_ID)){
				return true;
			}

			//寻找系统限制级别2内的祖先,假如关系为 99->1->4->11,当前分销商为11,则2级别内祖先分销商为[1, 4]
			$ancestors = $disaccount->getAncestorIds($dis_config->Dis_Level,0);	//[1, 4]

			//加上上级分销商用户ID
			array_push($ancestors,$this->User_ID);	//[1, 4, 11]

			$ancestors = array_reverse($ancestors); //[11, 4, 1]

			foreach($ancestors as $UID){
				$user_distribute_account = Dis_Account::Multiwhere(array('Users_ID'=>$this->Users_ID,'User_ID'=>$UID))
					 ->first(array('Professional_Title'));
				//if($user_distribute_account->Professional_Title == count($protitless)){//已经是最高级
					//continue;
				//}elseif($user_distribute_account->Professional_Title > count($protitless)){//大于最高级
					//Dis_Account::Multiwhere(array('Users_ID'=>$this->Users_ID,'User_ID'=>$UID))->update(array('Professional_Title'=>count($protitless)));
					//continue;
				//}else{

				//自身消费额
				if($dis_config->Pro_Title_Status == 4){
					$user_consue_choice = Order::where(array('User_ID'=>$UID))
							->whereIn('Order_Status', array(4,6,7));

					$user_consue = $user_consue_choice->sum('Order_TotalPrice');
					$user_back_consue = $user_consue_choice->sum('Back_Amount_Source');
					$user_consue = $user_consue - $user_back_consue;
				}elseif($dis_config->Pro_Title_Status == 2){
					$user_consue_choice = Order::where(array('User_ID'=>$UID))
							->whereIn('Order_Status', array(2,3,4,6,7));
					$user_consue = $user_consue_choice->sum('Order_TotalPrice');
					$user_back_consue = $user_consue_choice->sum('Back_Amount_Source');
					$user_consue = $user_consue - $user_back_consue;
				}
					$Consume = isset($user_consue) ? $user_consue : 0;

				//自身销售额
				if($dis_config->Pro_Title_Status == 4){
					$user_count_choice = Order::where(function($query) use ($UID){
						$query->where(array('Owner_ID'=>$UID))->orWhere(['User_ID' => $UID]);
					})->whereIn('Order_Status', array(4,6,7));
					$user_count = $user_count_choice->sum('Order_TotalPrice');
					$user_back_count = $user_count_choice->sum('Back_Amount_Source');
					$Sales_Self = $user_count - $user_back_count;
				}elseif($dis_config->Pro_Title_Status == 2){
					$user_count_choice = Order::where(function($query) use ($UID){
						$query->where(array('Owner_ID'=>$UID))->orWhere(['User_ID' => $UID]);
					})->whereIn('Order_Status', array(2,3,4,6,7));
					$user_count = $user_count_choice->sum('Order_TotalPrice');
					$user_back_count = $user_count_choice->sum('Back_Amount_Source');
					$Sales_Self = $user_count - $user_back_count;
				}

				//团队销售额
				$accountObj =  Dis_Account::Multiwhere(array('User_ID'=>$UID))
						->first();
				$posterity = $accountObj->getPosterity($dis_config->Dis_Level);
				require_once($_SERVER["DOCUMENT_ROOT"].'/include/support/distribute_helpers.php');
				$Sales_Group = get_my_leiji_sales($this->Users_ID,$UID,$posterity);

					$level = 0;
					foreach($protitless as $key=>$item){
						if($item['Sales_Group']<=$Sales_Group && $item['Sales_Self']<=(isset($Sales_Self) ? $Sales_Self : 0) && $item['Consume']<=$Consume){

							$level = $key;
							break;
						}
					}

					//更新分销商等级
					if($level > $user_distribute_account->Professional_Title){
						Dis_Account::Multiwhere(array('Users_ID'=>$this->Users_ID,'User_ID'=>$UID))->update(array('Professional_Title'=>$level));
						continue;
					}
				//}
			}
			
			return true;
		}
		
		function up_front_nobility_level($user_consue, $user_count, $Sales_Groupnow){

		$dis_config = Dis_Config::where('Users_ID',$this->Users_ID)->first();
			if(empty($dis_config->Pro_Title_Level)){
				return true;
			}

			$protitles = json_decode($dis_config->Pro_Title_Level,true);
			$protitles_temp = array_reverse($protitles,true);
			
			//过滤不启用的爵位(名称为空)
			$protitless = array();
			foreach($protitles_temp as $pk=>$pv){
				if(!empty($pv['Name'])){
					$protitless[$pk]=$pv;
				}
			}
	
			//爵位全部未启用
			if (empty($protitless)) return true;

			$disaccount =  Dis_Account::Multiwhere(array('Users_ID'=>$this->Users_ID,'User_ID'=>$this->User_ID))
					 ->first();
			if(empty($disaccount->Users_ID)){
				return true;
			}
					$level = 0;		
					foreach($protitless as $key=>$item){
						if($item['Sales_Group']<=$Sales_Groupnow && $item['Sales_Self']<=$user_count && $item['Consume']<=$user_consue){
							$level = $key;
							break;
						}
					}

					//更新					
						Dis_Account::Multiwhere(array('Users_ID'=>$this->Users_ID,'User_ID'=>$this->User_ID))->update(array('Professional_Title'=>$level));
						return true;
		}

		/**
		 * 获取分销商销售金额,对于分销商以前普通会员身份的销售金额不统计
		 * @param  array $son_dis_userid_arr [description]
		 * @return number                     [description]
		 */
		public function get_dis_sale_amount($son_dis_userid_arr) {
				global $DB;

				$dis_config = Dis_Config::where('Users_ID',$this->Users_ID)->first();
				if(empty($dis_config->Pro_Title_Level)){
					return true;
				}				

				$userids = "'" . implode("','", $son_dis_userid_arr) . "'";
				$sql = "WHERE User_ID=Owner_ID AND User_ID IN ($userids) AND Order_Status >= " . $dis_config->Pro_Title_Status;

				$ret = $DB->GetRs('user_order', 'SUM(Order_TotalPrice) AS total', $sql);
				$user_dis_sale_amount = $ret['total'];

				return $user_dis_sale_amount;

		}
}