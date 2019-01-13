<?php
class useryielist{
	public $db;     //引入数据库
	public $usersid;    //商家id	
    public $restart;    //是否重新开启收益
    function __construct($DB,$usersid,$restart){
		$this->db = $DB;
		$this->usersid = $usersid;	
		$this->restart = $restart;		
	}
	
    // 计算天数差
    public function timediffrence($prostime,$projtime)
{
  if ($projtime < $prostime) {
    return 'error';
  }
  return round(($projtime-$prostime)/86400);
}

//获取收益数组
function get_procee_mon($Procee_Mon,$days,$rate){
	$arr = array();
	$arr[0] = number_format($Procee_Mon*$rate*0.01,2,'.','');
	for($i=1;$i<$days;$i++){
		$Procee_Mon = $Procee_Mon * (1+$rate*0.01);
		$arr[$i] = number_format($Procee_Mon*$rate*0.01,2,'.','');
	}
	return $arr;
}


//添加收益数据
 public function addpro($ProIntegral)
 {
$sqlh="";
$sqla="";
$j=0;
$user_list = array();
$remder_lslist = array();
$remder_monlist = array();
	 //获取要计算收益的用户表
$this->db->Get("user","User_ID,User_Money","where Users_ID='".$this->usersid."'");
while($rsUser = $this->db->fetch_assoc()){
	$user_list[$rsUser['User_ID']] = $rsUser['User_Money'];
}
$usercou = count($user_list);
//获取每个用户最新更新时间
$this->db->Get("user_yielist","User_ID,MAX(Procee_Date) as maxdate","where Users_ID='".$this->usersid."' group by User_ID");
while($newdate = $this->db->fetch_assoc()){	
	$user_Prodate[$newdate['User_ID']] = $newdate['maxdate'];	
}

//当前时间
$now = strtotime(date('Y-m-d 00:00:00',time()));
	foreach($user_list as $userid=>$money){
	$j++;
	$timestamp = empty($user_Prodate[$userid]) || $this->restart == 1 ? 1 : ($now-$user_Prodate[$userid]);
	$days = empty($user_Prodate[$userid]) || $this->restart == 1 ? 1 : intval($timestamp/86400);	
    if($days!=0){     //添加收益准备	
		$Procee_Mon = $money;
		$Procee_array = $this->get_procee_mon($Procee_Mon,$days,$ProIntegral);	
		for($i=0;$i<$days;$i++){
			if($days > 1){
			$procee_sum_pre = array_sum(array_slice($Procee_array, 0, $i));	
			}else{
			$procee_sum_pre = 0;
			}
			$sqlh .= "('".$this->usersid."',".$userid.",".($now-86400*($days-$i-1)).",".($Procee_Mon+$procee_sum_pre).",".$ProIntegral.",".$Procee_array[$i]."),";
			$remder_lslist[$i] = $Procee_Mon+$procee_sum_pre+$Procee_array[$i];
			$Procee = $Procee_array[$i];
		}
		if($days > 1){
		$pos = array_search(max($remder_lslist), $remder_lslist);
		$remder_monlist[$userid] = $remder_lslist[$pos];
			}else{
		$remder_monlist[$userid] = $Procee_Mon+$Procee;
			}
//添加收益执行
if($j%3000==0 || $j==$usercou){
mysql_query("begin");
$sql = "insert into user_yielist(Users_ID,User_ID,Procee_Date,Remainder_Mon,Yield_Rate,Procee_Mon) VALUES ";
$newsql = substr($sqlh,0,strlen($sqlh)-1);
	 $sql .= $newsql;
	$Data=array(		
		"ProRstart"=>0
	);
	$seta=$this->db->Set("user_config",$Data,"where Users_ID='".$this->usersid."'");
	$execa=$this->db->query($sql);
	$ids = implode(',', array_keys($remder_monlist));
	$sqla = "UPDATE user SET User_Money = CASE User_ID ";
	foreach ($remder_monlist as $uid => $usermon) {
    $sqla .= sprintf("WHEN %d THEN %d ", $uid, $usermon);
}
$sqla .= "END WHERE User_ID IN ($ids)";
$execb=$this->db->query($sqla);
	$Data=array(		
		"ProRstart"=>1
	);
	$setb=$this->db->Set("user_config",$Data,"where Users_ID='".$this->usersid."'");
	if($seta&&$setb&&$execa&&$execb){
		mysql_query("commit");
		$sqlh="";
		$sqla="";
		unset($remder_monlist);
		$remder_monlist = array();
	}else{
	mysql_query("roolback");
	$Data=array(		
		"ProRstart"=>0
				);
	$this->db->Set("user_config",$Data,"where Users_ID='".$this->usersid."'");
			}
			}
		}
	
		}

	}
}
?>