<?php
/*edit in 20160322*/
use Illuminate\Database\Capsule\Manager as DB;
$Users_ID = $_SESSION["Users_ID"];
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
//删除级别
if(empty($_REQUEST['ProductsID'])){
	echo '缺少必要的参数';
	exit;
}

$ProductsID=empty($_REQUEST['ProductsID'])?0:$_REQUEST['ProductsID'];
$rsProducts = DB::table('shop_Products')->where(['Users_ID' => $Users_ID, 'Products_ID' => $ProductsID])->first(['Products_Distributes', 'skuvaljosn', 'skujosn','Products_Count']);
$distribute_list = $rsProducts['Products_Distributes'] ? json_decode($rsProducts['Products_Distributes'],true) : array(); //分佣金额列表
$dis_config = dis_config($Users_ID);
$rsProducts['skuvaljosn'] = !empty($rsProducts['skuvaljosn']) ? json_decode($rsProducts['skuvaljosn'], true) : [];
$rsProducts['skujosn'] = !empty($rsProducts['skujosn']) ? json_decode($rsProducts['skujosn'], true) : [];
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
</head>
<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/member/js/distribute/dis_level.js'></script>    
    <div id="orders" class="r_con_wrap">
      <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="10%" nowrap="nowrap">序号</td>
            <td width="20%" nowrap="nowrap">级别名称</td>           
            <td width="70%" nowrap="nowrap">佣金明细</td>            
          </tr>
        </thead>
        <tbody>
         <?php
		 $dislevelarrs = DB::table('distribute_level')->where(['Users_ID' => $Users_ID])->get();
		 $n = 0;
		 foreach($dislevelarrs as $key=>$disinfo){
		 $n++;				
		 ?>
         <tr>
            <td nowrap="nowrap"><?=$n?></td>
            <td nowrap="nowrap"><?=$disinfo['Level_Name']?></td>
            <td nowrap="nowrap">
			<?php $arr = array('一','二','三','四','五','六','七','八','九','十');
				$level =  $dis_config['Dis_Self_Bonus']?$dis_config['Dis_Level']+1:$dis_config['Dis_Level'];
				for($i=0;$i<$level;$i++){
			?>
				<li>
				<?php if($dis_config['Dis_Self_Bonus']==1 && $i==$dis_config['Dis_Level']){?>
				<strong>自销佣金</strong>
				<?php }else{?>                            
				<strong><?=$arr[$i]?>级</strong>
				<?php }?>                           
				<?=!empty($distribute_list[$disinfo['Level_ID']][$i])?$distribute_list[$disinfo['Level_ID']][$i]:0?>%&nbsp;(佣金比例的百分比)<em></em></li>
			<?php }?>			
			</td>            
          </tr>
         <?php }?>
        </tbody>
      </table>
	  
	  <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="30%" nowrap="nowrap">总库存</td>
            <td width="70%" colspan="2" nowrap="nowrap">库存数量</td>             
          </tr>
        </thead>
        <tbody>		
		<tr>
			<td>总库存</td>
			<td><?=$rsProducts['Products_Count'] ?></td>
		</tr>
		<?php
		if(!empty($rsProducts['skujosn'])){
			$array_sku = [];
			//获取多维数组元素最多的
			foreach($rsProducts['skujosn'] as $k => $v){
				foreach($v as $key => $val){
					$array_sku[$key] = $val;
				}
			}
			foreach($rsProducts['skuvaljosn'] as $k => $v){
				echo "<tr>";
				if(stripos($k,';')!==false){
					$temp = explode(';',$k);
					$str='';
					foreach($temp as $val){
						$str .= $array_sku[$val] . '--'; 
					}
					$str = trim($str,'--');
					echo "<td>".$str."</td>";
				}else{
					echo "<td>".$array_sku[$k]."</td>";
				}
				echo "<td>". $v['Txt_CountSon'] ."</td>";
				echo "</tr>";
			}
		?>
		 <?php } ?>
		 
		 <tr>
			<td>价格</td>
			<td></td>
		</tr>
		<?php
		if(!empty($rsProducts['skujosn'])){
			$array_sku = [];
			//获取多维数组元素最多的
			foreach($rsProducts['skujosn'] as $k => $v){
				foreach($v as $key => $val){
					$array_sku[$key] = $val;
				}
			}
			foreach($rsProducts['skuvaljosn'] as $k => $v){
				echo "<tr>";
				if(stripos($k,';')!==false){
					$temp = explode(';',$k);
					$str='';
					foreach($temp as $val){
						$str .= $array_sku[$val] . '--'; 
					}
					$str = trim($str,'--');
					echo "<td>".$str."</td>";
				}else{
					echo "<td>".$array_sku[$k]."</td>";
				}
				echo "<td>". $v['Txt_PriceSon'] ."</td>";
				echo "</tr>";
			}
		?>
		 <?php } ?>
		</tbody>
      </table>
	  
    </div>
</body>
</html>