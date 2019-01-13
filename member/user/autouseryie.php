<script type="text/javascript">
		<!--
		function closeWin() {			
			var browserName=navigator.appName;		
                 if (browserName=="Netscape"){					 
                       if (navigator.userAgent.indexOf("Firefox") > 0) {
						window.open("about:blank","_self").close();
						} else {
						window.opener = null;
						window.open('', '_top', '');
						window.close();
						}
                 }
                 if (browserName=="Microsoft Internet Explorer") {
					  window.opener=null;
					window.open('', '_self', ''); 
                       window.close();
                 }
		}
		var sec = 2;
		function clock() {
			sec -=1;			
			document.getElementById("closeTip").innerHTML = "本窗口将在" + sec + "秒后自动关闭";
			if(sec>0) 
				setTimeout("clock();", 1000);
			else 
				closeWin();
		}		
		//-->
	</script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/member/user/useryielist.php');
$DB->Get("users","Users_ID");
while($usersid = $DB->fetch_assoc()){	
	$usersids[] = $usersid['Users_ID'];	
}
foreach($usersids as $key=>$uid){
$lsjie = $uid;
$rsConfig=$DB->GetRs("user_config","*","where Users_ID='".$lsjie."'");
if($rsConfig['IsPro'] == 1){
$autoyieupdate = new useryielist($DB,$lsjie,$rsConfig['ProRstart']);
$autoyieupdate->addpro($rsConfig['ProIntegral']);
}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>updatayie</title>
</head>
<body>
 <div id="closeTip"></div>
 <script type="text/javascript">
 clock();
 </script>
 </body>
 </html>