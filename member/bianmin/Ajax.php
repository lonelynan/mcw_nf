<?php
  require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
//对mycount表的操作
if (isset($_POST['xian'])) {
	$UsersID=$_SESSION['Users_ID'];
	//获取post传值
  	//获取post传值
  	$key=$_POST['key'];
 	$secret=$_POST['secret'];
  	$zhang=$_POST['zhang'];
  	$pass=$_POST['pass'];
  	$token=$_POST['token'];
  	$refresh=$_POST['refresh'];
 	//获取$id;
 	$DB->query("SELECT * FROM mycount WHERE usersid='".$UsersID."'");
 	$re=$DB->fetch_assoc();
 	$id=$re['id'];
 	$a=$DB->Set('mycount',"appkey='".$key."',appsecret='".$secret."',qianmizhanghao='".$zhang."',qianmimima='".$pass."',token='".$token."',refreshToken='".$refresh."'",'WHERE id='.$id);
 	$data=json_encode(array('t'=>$a), JSON_UNESCAPED_UNICODE);
	echo $data;

	exit();
}

if(isset($_POST['key'])){
	 //当前usersid
  $UsersID=$_SESSION['Users_ID'];
  //获取post传值
  $key=$_POST['key'];
  $secret=$_POST['secret'];
  $zhang=$_POST['zhang'];
  $pass=$_POST['pass'];
  $token=$_POST['token'];
  $refresh=$_POST['refresh'];
 //数据库中只能有一条数据
  $DB->query("SELECT * FROM mycount WHERE usersid='".$UsersID."'");
  $res=$DB->num_rows();
  if($res>=1){
  		$data=json_encode(array('t'=>2), JSON_UNESCAPED_UNICODE);
		echo $data;
  }else{
  	//开始添加
	$t=$DB->Add('mycount',"appsecret=$secret,appkey=$key,qianmizhanghao=$zhang,qianmimima=$pass,token=$token,refreshToken=$refresh,usersid=$UsersID");
  	  	$data=json_encode(array('t'=>1), JSON_UNESCAPED_UNICODE);
		echo $data;	
		
  }
	exit();
}
  
if(isset($_POST['newmoney'])){

// 将数据添加进bianmin_server表
	//获取现价  原价数组  
	$arr1=$_POST['newmoney'];
	$arr2=$_POST['oldmoney'];
	$xuni['id']=$arr1;
	$xuni['price']=$arr2;
	// 将两个数组合并
    $array=[];  //创建个数组
    $va =count($xuni['id']); //获得数组的长度
    for ($i=0; $i <$va ; $i++) { 
        $array[]=[$xuni['id'][$i],$xuni['price'][$i]];
	}


//创建数组
$date['Users_ID']=$_POST['usersid'];
$date['Server_Name']=$_POST['name'];
$date['Server_Type']=$_POST['type'];
$date['Service_Provider']=$_POST['provider'];
$date['Server_Profit']=$_POST['price'];
$date['Server_BriefDescription']=0;
$date['Server_Parameter']=json_encode($array);
$date['Server_Newprice']=json_encode($arr1);
$date['Server_Oldprice']=json_encode($arr2);
$date['Count']=$va;
$date['commission_ratio'] = $_POST['commission_ratio'];
$date['nobi_ratio'] = $_POST['nobi'];
$date['Server_Distributes'] = json_encode($_POST['listDristribute']);

//设置表变量
$table='bianmin_server';
//查询数据库来做限制 每个类别 不准超过2个
$DB->query("SELECT * FROM bianmin_server WHERE Server_Type='".$date['Server_Type']."' and Users_ID='".$date['Users_ID']."'");
  $nu=$DB->num_rows();
//查询数据库来做限制 每个服务商 不准超过2个
$DB->query("SELECT * FROM bianmin_server WHERE Service_Provider='".$date['Service_Provider']."' and Users_ID='".$date['Users_ID']."'");
  $num=$DB->num_rows();
//添加的限制
  	if($nu<2&&$num<2){
  		$dataArr = array("Users_ID"=>$date['Users_ID'],"Server_Name"=>$date['Server_Name'],'commission_ratio'=>$date['commission_ratio'],"nobi_ratio"=>$date['nobi_ratio'],"Server_Type"=>$date['Server_Type'],"Service_Provider"=>$date['Service_Provider'],"Server_Profit"=>$date['Server_Profit'],"Server_Parameter"=>$date['Server_Parameter'],"Server_Distributes"=>$date['Server_Distributes'],"Server_Newprice"=>$date['Server_Newprice'],"Server_Oldprice"=>$date['Server_Oldprice'],"Count"=>$date['Count'], "Server_BriefDescription" => $date['Server_BriefDescription']);

	$t=$DB->Add($table, $dataArr);

	//返回执行后的id
	$id=mysql_insert_id(); 
	
	//缺少个判断 暂时不判断
	
	//返回Ajax  
	$data=json_encode(array('t'=>$t,'msg'=>'便民服务添加成功'), JSON_UNESCAPED_UNICODE);
	echo $data;
	}else{
		$t=false;
			//返回Ajax  
	$data=json_encode(array('t'=>$t,'msg'=>'请勿重复添加'), JSON_UNESCAPED_UNICODE);
	echo $data;
	}




}else{
			$id=$_POST['ID'];
			$t=$DB->Del('bianmin_server',"Server_ID=$id");	
			$d=json_encode(array('t'=>$t,'msg'=>'服务添加失败'), JSON_UNESCAPED_UNICODE);
			echo $d;
		}








?>