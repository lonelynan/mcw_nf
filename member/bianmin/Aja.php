<?php
  require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Conn.php');
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
    $Server_ID=$_POST['serverid'];


//创建数组
$date['Users_ID']=$_POST['usersid'];
$date['Server_Name']=$_POST['name'];
$date['Server_Type']=$_POST['type'];
$date['Service_Provider']=$_POST['provider'];
$date['Server_Profit']=$_POST['price'];
$date['Server_BriefDescription']=$_POST['text'];
$date['Server_Parameter']=json_encode($array);
$date['Server_Newprice']=json_encode($arr1);
$date['Server_Oldprice']=json_encode($arr2);
$date['Count']=$va;
$date['commission_ratio'] = $_POST['commission_ratio'];
$date['nobi_ratio'] = $_POST['nobi'];
$date['Server_Distributes'] = json_encode($_POST['listDristribute']);

//设置表变量
$table='bianmin_server';
// $date"where Server_ID=$Server_ID"
 $t=$DB->Set($table,"Users_ID='".$date['Users_ID']."',Server_Name='".$date['Server_Name']."',commission_ratio='".$date['commission_ratio']."',nobi_ratio='".$date['nobi_ratio']."',Server_Type='".$date['Server_Type']."',Service_Provider='".$date['Service_Provider']."',Server_Profit='".$date['Server_Profit']."',Server_Parameter='".$date['Server_Parameter']."',Server_Distributes='".$date['Server_Distributes']."',Server_Newprice='".$date['Server_Newprice']."',Server_Oldprice='".$date['Server_Oldprice']."',Count='".$date['Count']."'",'WHERE Server_ID='.$Server_ID);
	//返回Ajax  
	$data=json_encode(array('t'=>$t), JSON_UNESCAPED_UNICODE);
	echo $data;

?>