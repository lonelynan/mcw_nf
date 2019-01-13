<?php
$json=json_decode($rsSkin['Home_Json'],true);
include("skin/top.php"); 
?>
<body>
<?php
ad($UsersID, 1, 1);
?>
<div id="shop_page_contents">
 <div id="cover_layer"></div>
 <link href='/static/api/shop/skin/1/page.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
 <link href="/static/api/shop/skin/1/page_media.css?t=<?php echo time();?>" type="text/css" rel="stylesheet" />
 <link href='/static/js/plugin/flexslider/flexslider.css' rel='stylesheet' type='text/css' />
 <script type='text/javascript' src='/static/js/plugin/flexslider/flexslider.js'></script>
 <script type='text/javascript' src='/static/api/shop/js/index.js'></script>
 <script language="javascript">
  var shop_skin_data=<?php echo json_encode($json) ?>;
 </script>
 <?php echo '<script type="text/javascript" src="/static/js/plugin/swipe/swipe.js"></script>' ?> 
 <div id="shop_skin_index">
  <?php
  if(isset($json)){
	  foreach($json as $key=>$value){
	  	if($value['type']!='p13' && $value['type'] != 'p14'){
	  		$url=explode('|',$value['url']);
			$pic=explode('|',$value['pic']);
			$txt=explode('|',$value['txt']);
	  	}
		if($value['type']=='p1'){//一行两列
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep1Sprite wrap_content">
		  <div class="wrapP1Item" style="margin:0;">
			<div class="p1Img"><a href="'.$url[0].'"><img src="'.$pic[0].'" width="100%" /></a>';
			if(!empty($txt[0])){
				echo '<div class="p1Word" style="color:'.$txtColor[0].'; background:'.$bgColor[0].';"><a href="'.$url[0].'" style="color:'.$txtColor[0].';">'.$txt[0].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP1Item">
			<div class="p1Img"><a href="'.$url[1].'"><img src="'.$pic[1].'" width="100%" /></a>';
			if(!empty($txt[1])){
				echo'<div class="p1Word" style="color:'.$txtColor[1].'; background:'.$bgColor[1].';"><a href="'.$url[1].'" style="color:'.$txtColor[1].';">'.$txt[1].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="clean"></div>
		</div>';
		}elseif($value['type']=='p0'){//一行三列
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep0Sprite wrap_content">
		  <div class="wrapP0Item" style="margin:0;">
			<div class="p0Img"><a href="'.$url[0].'"><img src="'.$pic[0].'" width="100%" /></a>';
			if(!empty($txt[0])){
				echo '<div class="p0Word" style="color:'.$txtColor[0].'; background:'.$bgColor[0].';"><a href="'.$url[0].'" style="color:'.$txtColor[0].';">'.$txt[0].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP0Item">
			<div class="p0Img"><a href="'.$url[1].'"><img src="'.$pic[1].'" width="100%" /></a>';
			if(!empty($txt[1])){
				echo '<div class="p0Word" style="color:'.$txtColor[1].'; background:'.$bgColor[1].';"><a href="'.$url[1].'" style="color:'.$txtColor[1].';">'.$txt[1].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP0Item">
			<div class="p0Img"><a href="'.$url[2].'"><img src="'.$pic[2].'" width="100%" /></a>';
			if(!empty($txt[2])){
				echo '<div class="p0Word" style="color:'.$txtColor[2].'; background:'.$bgColor[2].';"><a href="'.$url[2].'" style="color:'.$txtColor[2].';">'.$txt[2].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="clean"></div>
		</div>';
		}elseif($value['type']=='p6'){//一行四列
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep6Sprite wrap_content">
		  <div class="wrapP6Item" style="margin:0;">
			<div class="p6Img"><a href="'.$url[0].'"><img src="'.$pic[0].'" width="100%" /></a>';
			if(!empty($txt[0])){
				echo '<div class="p6Word" style="color:'.$txtColor[0].'; background:'.$bgColor[0].';"><a href="'.$url[0].'" style="color:'.$txtColor[0].';">'.$txt[0].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP6Item">
			<div class="p6Img"><a href="'.$url[1].'"><img src="'.$pic[1].'" width="100%" /></a>';
			if(!empty($txt[1])){
				echo '<div class="p6Word" style="color:'.$txtColor[1].'; background:'.$bgColor[1].';"><a href="'.$url[1].'" style="color:'.$txtColor[1].';">'.$txt[1].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP6Item">
			<div class="p6Img"><a href="'.$url[2].'"><img src="'.$pic[2].'" width="100%" /></a>';
			if(!empty($txt[2])){
				echo '<div class="p6Word" style="color:'.$txtColor[2].'; background:'.$bgColor[2].';"><a href="'.$url[2].'" style="color:'.$txtColor[2].';">'.$txt[2].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP6Item">
			<div class="p6Img"><a href="'.$url[3].'"><img src="'.$pic[3].'" width="100%" /></a>';
			if(!empty($txt[3])){
				echo '<div class="p6Word" style="color:'.$txtColor[3].'; background:'.$bgColor[3].';"><a href="'.$url[3].'" style="color:'.$txtColor[3].';">'.$txt[3].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="clean"></div>
		</div>';
		}elseif($value['type']=='p7'){//一行五列
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep7Sprite wrap_content">
		  <div class="wrapP7Item" style="margin:0;">
			<div class="p7Img"><a href="'.$url[0].'"><img src="'.$pic[0].'" width="100%" /></a>';
			if(!empty($txt[0])){
				echo '<div class="p7Word" style="color:'.$txtColor[0].'; background:'.$bgColor[0].';"><a href="'.$url[0].'" style="color:'.$txtColor[0].';">'.$txt[0].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP7Item">
			<div class="p7Img"><a href="'.$url[1].'"><img src="'.$pic[1].'" width="100%" /></a>';
			if(!empty($txt[1])){
				echo '<div class="p7Word" style="color:'.$txtColor[1].'; background:'.$bgColor[1].';"><a href="'.$url[1].'" style="color:'.$txtColor[1].';">'.$txt[1].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP7Item">
			<div class="p7Img"><a href="'.$url[2].'"><img src="'.$pic[2].'" width="100%" /></a>';
			if(!empty($txt[2])){
				echo '<div class="p7Word" style="color:'.$txtColor[2].'; background:'.$bgColor[2].';"><a href="'.$url[2].'" style="color:'.$txtColor[2].';">'.$txt[2].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP7Item">
			<div class="p7Img"><a href="'.$url[3].'"><img src="'.$pic[3].'" width="100%" /></a>';
			if(!empty($txt[3])){
				echo '<div class="p7Word" style="color:'.$txtColor[3].'; background:'.$bgColor[3].';"><a href="'.$url[3].'" style="color:'.$txtColor[3].';">'.$txt[3].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="wrapP7Item">
			<div class="p7Img"><a href="'.$url[4].'"><img src="'.$pic[4].'" width="100%" /></a>';
			if(!empty($txt[3])){
				echo '<div class="p7Word" style="color:'.$txtColor[4].'; background:'.$bgColor[4].';"><a href="'.$url[4].'" style="color:'.$txtColor[4].';">'.$txt[4].'</a></div>';
			}
			echo '</div>
		  </div>
		  <div class="clean"></div>
		</div>';
		}elseif($value['type']=='p8'){//左一右二
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep8Sprite wrap_content">
			<div class="left8">
		  <div class="wrapP8Item">
			<div class="p8Img"><a href="'.$url[0].'"><img src="'.$pic[0].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  </div>
		  <div class="right8">
		  <div class="wrapP8Item">
			<div class="p8Img"><a href="'.$url[1].'"><img src="'.$pic[1].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  <div class="wrapP8Item">
			<div class="p8Img"><a href="'.$url[2].'"><img src="'.$pic[2].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  </div>
		  <div class="clean"></div>
		</div>';
		}elseif($value['type']=='p9'){//左一右四
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep9Sprite wrap_content">
			<div class="left9">
		  <div class="wrapP9Item">
			<div class="p9Img"><a href="'.$url[0].'"><img src="'.$pic[0].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  </div>
		  <div class="right9">
		  <div class="wrapP9Item">
			<div class="p9Img"><a href="'.$url[1].'"><img src="'.$pic[1].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  <div class="wrapP9Item">
			<div class="p9Img"><a href="'.$url[2].'"><img src="'.$pic[2].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  <div class="wrapP9Item">
			<div class="p9Img"><a href="'.$url[3].'"><img src="'.$pic[3].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  <div class="wrapP9Item">
			<div class="p9Img"><a href="'.$url[4].'"><img src="'.$pic[4].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  <div class="clean"></div>
		  </div>
		  <div class="clean"></div>
		</div>';
		}elseif($value['type']=='p10'){//左一右二
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep10Sprite wrap_content">
			<div class="left10">
		  <div class="wrapP10Item">
			<div class="p10Img"><a href="'.$url[0].'"><img src="'.$pic[0].'" width="100%" /></a>';
			echo '</div>
		  </div>
		  <div class="wrapP10Item">
			<div class="p10Img"><a href="'.$url[1].'"><img src="'.$pic[1].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  </div>
		  <div class="right10">
		  <div class="wrapP10Item">
			<div class="p10Img"><a href="'.$url[2].'"><img src="'.$pic[2].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  </div>
		  <div class="clean"></div>
		</div>';
		}elseif($value['type']=='p11'){//左一右四
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep11Sprite wrap_content">
			<div class="left11">
		  <div class="wrapP11Item">
			<div class="p11Img"><a href="'.$url[0].'"><img src="'.$pic[0].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  <div class="wrapP11Item">
			<div class="p11Img"><a href="'.$url[1].'"><img src="'.$pic[1].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  <div class="wrapP11Item">
			<div class="p11Img"><a href="'.$url[2].'"><img src="'.$pic[2].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  <div class="wrapP11Item">
			<div class="p11Img"><a href="'.$url[3].'"><img src="'.$pic[3].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  <div class="clean"></div>
		  </div>
		  <div class="right11">
		  <div class="wrapP11Item">
			<div class="p11Img"><a href="'.$url[4].'"><img src="'.$pic[4].'" width="100%" /></a>';
			
			echo '</div>
		  </div>
		  </div>
		  <div class="clean"></div>
		</div>';
		}elseif($value['type']=='p2'){//文字
			$txt[0] = str_replace('&quot;','"',$txt[0]);
			echo '<div class="packagep2Sprite wrap_content">
		  <div class="p2word">'.$txt[0].'</div>
		</div>';
		}elseif($value['type']=='p3'){//图片
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep3Sprite wrap_content">
		  <div class="wrapP3Item">
			<div class="p3Img"><a href="'.$url[0].'"><img src="'.$pic[0].'" width="100%" /></a>';
			if(!empty($txt[0])){
				echo '<div class="p3Word" style="color:'.$txtColor[0].'; background:'.$bgColor[0].';"><a href="'.$url[0].'" style="color:'.$txtColor[0].';">'.$txt[0].'</a></div>';
			}
			echo '</div>
		  </div>
		</div>';
		}elseif($value['type']=='p4'){//幻灯片
			echo '<div id="p4_'.$rsSkin['Home_ID'].'" class="packagep4Sprite wrap_content">
		  <ul>';
		  for($i=0;$i<count($pic);$i++){
			  if($pic[$i]<>'' && $pic[$i]<>'undefined'){
				  echo '<li><a href="'.$url[$i].'"><img src="'.$pic[$i].'" alt="'.($i+1).'" style="width:100%;vertical-align:top;"/></a></li>';
			  }
		  }
		  echo '</ul>
		</div>
		<script>$(function(){new Swipe(document.getElementById("p4_'.$rsSkin['Home_ID'].'"), {speed:500,auto:3000})});</script>';
		}elseif($value['type']=='p5'){//电话拨号
			echo '<ul style="background:'.$value['bgColor'].'" class="packagep5Sprite wrap_content">';
			$tels=explode('<br>',$value['txt']);
			foreach($tels as $k=>$v){
				$tel=explode('/',$v);
				echo '<li style="'.(count($tels)>1?'':'width:100%').'"><a style="color:'.$value['txtColor'].'; font-size:'.$value['fontSize'].'px;" href="tel:'.$tel[0].'">'.$tel[0].(count($tel)>1 ? '('.$tel[1].')' :'').'</a></li>';
			}
			echo '</ul>';
		}elseif($value['type']=='p12'){//搜索框
			$txtColor=explode('|',$value['txtColor']);
			$bgColor=explode('|',$value['bgColor']);
			echo '<div class="packagep12Sprite wrap_content">
		  <div class="wrapP12Item" style="background:'.$bgColor[0].'"><form action="/api/shop/search.php" method="get">
        <input type="text" name="kw" class="input" style="border:1px solid '.$txtColor[0].'" value="" placeholder="输入商品名称..." />
		<input type="hidden" name="UsersID" value="'.$UsersID.'" />
		<input type="hidden" name="OwnerID" value="'.($owner['id'] != '0' ? $owner['id'] : '').'" />
        <input type="submit" class="submit" value=" " style="background:url('.$pic[0].') no-repeat left top;" />
      </form>
		  </div>
		</div>';
		}elseif($value['type'] == 'p13'){//分类
			$condition = "where Users_ID='".$UsersID."' and Products_SoldOut=0 and Products_Status=1";			
			if(!empty($value['category'])){
			$product_istopc = $DB->getRs("shop_products","Father_Category","where Users_ID='".$UsersID."' and Father_Category = ".$value['category']);
				if (!empty($product_istopc)) {
					$condition .= " and Father_Category=".$value['category']." ";
				} else {
					$condition .= " and Products_Category=".$value['category']." ";
				}
				$more_url = $shop_url.'category/'.$value['category'].'/';
			}else{
				$more_url = $shop_url.'category/0/';
			}
			if(!empty($value['order'])){
				if($value['order'] == '1'){
					$condition .= " order by Products_CreateTime desc";
				}elseif($value['order'] == '2'){
					$condition .= " order by Products_PriceX asc,Products_Index asc,Products_ID desc";
				}elseif($value['order'] == '3'){
					$condition .= " order by Products_Sales desc,Products_Index asc,Products_ID desc";
				}else{
					$condition .= " order by Products_Index asc,Products_ID desc";
				}
			}
			if(!empty($value['num'])){
				 $condition .= "  limit 0,".$value['num']."";
			}else{
				$condition .= " limit 0,10";
			}
			$productList = $DB->get("shop_products","*",$condition);	
			$productList = $DB->toArray($productList);
			foreach($productList as $key => $val){
				$productList[$key]['link'] = $shop_url.'products/'.$val["Products_ID"].'/';
				$productList[$key]['JSON'] = json_decode($val['Products_JSON'],true);
			}
			//更多文字
			if(!empty($value['txt'])){
				$txt = $value['txt'];
			}else{
				$txt = '更多';
			}
			//
			if(!empty($value['p_title'])){
				$category_name = $value['p_title'];
			}else{
				$category_name = '推荐列表';
			}
			//商品显示属性
			if(!empty($value['nature'])){
				$nature = explode(',',rtrim($value['nature'],','));
			}else{
				$nature = array();
			}
			echo '<div class="packagep13Sprite wrap_content">';
			if($value['top_type'] == '1') {
				echo '<div class="w">
					<div class="biaot_x">
				    	<span class="fenlei_x l">'.$category_name.'</span>';
				    	if($value['check_more'] == '1') {
				    		echo '<span class="more r"><a href="'.$more_url.'">'.$txt.'>></a></span>';
				    	}
				echo '</div>
				</div>';
			}elseif($value['top_type'] == '2') {
				echo '<div class="w">
					<div class="biaot1_x">
				    	<span class="fenlei_x l">'.$category_name.'</span>';
				    	if($value['check_more'] == '1') {
					        echo '<span class="more r"><a href="'.$more_url.'">></a></span>';
					    }
				    echo '</div>
				</div>';
			}elseif($value['top_type'] == '3') {
				echo '<div class="w">
					<div class="biaot2_x">
				    	<span class="fenlei_x l">'.$category_name.'</span>';
				    	if($value['check_more'] == '1') {
				         	echo '<span class="r"><a href="'.$more_url.'"><span style="margin-right:15px;">'.$txt.'</span></a></span>';
				   		}
				    echo '</div>
				</div>';
			}elseif($value['top_type'] == '4') {
				echo '<div class="w">
					<div class="biaot3_x">
						<div class="fenlei3_x l">'.$category_name.'</div>';
						if($value['check_more'] == '1') {
				        	echo '<div class="more r"><a href="'.$more_url.'">'.$txt.'<span style="color:#e30101;">></span></a></div>';
				        }
				    echo '</div>
				</div>';
			}
			if($value['list_type'] == '1'){            
				echo '<div class="chanpint_x">
                         <ul>';
    			if(!empty($productList)){
    				foreach($productList as $k=>$v){
					/*商品直接加入购物车开始*/
						$spec_list = array();
						$properties = get_product_properties($v['Products_ID']);  // 获得商品的规格和属性
						if(!empty($properties['spe'])){	
							$specification = $properties['spe'];
							foreach($specification as $Attr_ID=>$item){
								if($item['Attr_Type'] == 1){
									foreach($item['Values'] as $k1=>$v1){
										if($k1 == 0){
											$spec_list[] = $v1['id'];
										}
										
									}
								}
							}
						}
						$str = empty($spec_list)?'':implode(',',$spec_list);
						if($v['Products_IsVirtual'] == 1){
							$cart_key = 'Virtual';
						}else{
							$cart_key = 'CartList';
						}
						echo '<li>
			            	<a href="'.$v['link'].'"><span class="cpt_x l"><img src="'.$v['JSON']['ImgPath'][0].'"></span></a>
			                <span class="cpxq_x l">
			                	<div class="cpbt_x"><a href="'.$v['link'].'">'.$v['Products_Name'].'</a></div>';
			                	if(!empty($nature)){
									if(in_array('integration', $nature)){
										$Integration2 = $v['Products_Integration'];
									}
									if(in_array('sale', $nature)){
										$sales2 = $v['Products_Sales'];
									}
									$dis2 = '';
									if(in_array('dis', $nature)){
										if($v['Products_PriceY'] != 0 && $v['Products_PriceX'] < $v['Products_PriceY']){
											$dis2 = round(($v['Products_PriceX']/$v['Products_PriceY']),2);
											$dis2 = ($dis2*10);
										}
									}
									if(in_array('cart', $nature)){
										$cart2 = '1';
									}
								}
								if(!empty($dis2)){
									echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 2px; font-weight:normal;">'.$dis2.'折</a></span>';
								}else{
									echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'</span>';
								}
								if(isset($Integration2)){
			                   		echo '<span class="r" style="font-size:13px; color:#999; line-height:28px;">积分：'.$Integration2.'</span>';
			                    }
			                    echo '<div class="clear"></div>';
			                    if(isset($sales2)){
			                   		echo '<span class="l" style="font-size:13px; color:#999; line-height:26px;">已售'.$sales2.'</span>';
			                    } 
								if(isset($cart2)){
									echo " <span class='r' style='margin-top:3px;'><a href='javascript:void(0)' onclick='add_to_cart(".$v['Products_ID'].")' ><img src='/static/api/shop/skin/".$rsConfig['Skin_ID']."/gwct_x.png' width='20' height='20'></a></span>";
								}	                                     
			                echo '<input type="hidden" id="spec_list_'.$v['Products_ID'].'" value="'.$str.'" />
			                	<input type="hidden" id="ownerid_'.$v['Products_ID'].'" value="'.$owner["id"].'" />
			                	<input type="hidden" id="cart_key_'.$v['Products_ID'].'" value="'.$cart_key.'" />
			                	<input type="hidden" id="Qty_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="cattsel_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="url_'.$v['Products_ID'].'" value="'.$shop_url.'cart/ajax/" />
			                </span>
			               </li>';						
					}
				}
				echo '</ul></div>';
			}elseif($value['list_type'] == '2'){
				echo '<div class="chanpint1_x">
                        <ul>';
				if(!empty($productList)){
    				foreach($productList as $k=>$v){
					/*商品直接加入购物车开始*/
						$spec_list = array();
						$properties = get_product_properties($v['Products_ID']);  // 获得商品的规格和属性
						if(!empty($properties['spe'])){	
							$specification = $properties['spe'];
							foreach($specification as $Attr_ID=>$item){
								if($item['Attr_Type'] == 1){
									foreach($item['Values'] as $k1=>$v1){
										if($k1 == 0){
											$spec_list[] = $v1['id'];
										}
										
									}
								}
							}
						}
						$str = empty($spec_list)?'':implode(',',$spec_list);
						if($v['Products_IsVirtual'] == 1){
							$cart_key = 'Virtual';
						}else{
							$cart_key = 'CartList';
						}
						echo '<li>
			            	<a href="'.$v['link'].'"><span class="cpt_x"><img src="'.$v['JSON']['ImgPath'][0].'"></span></a>
			               <div class="cpxq1_x">
			                	<div class="cpbt_x"><a href="'.$v['link'].'">'.$v['Products_Name'].'</a></div>';
			                	if(!empty($nature)){
									if(in_array('integration', $nature)){
										$Integration1 = $v['Products_Integration'];
									}
									if(in_array('sale', $nature)){
										$sales1 = $v['Products_Sales'];
									}
									$dis1 = '';
									if(in_array('dis', $nature)){
										if($v['Products_PriceY'] != 0 && $v['Products_PriceX'] < $v['Products_PriceY']){
											$dis1 = round(($v['Products_PriceX']/$v['Products_PriceY']),2);
											$dis1 = ($dis1*10);
										}
									}
									if(in_array('cart', $nature)){
										$cart1 = '1';
									}
								}
								if(isset($Integration1)){
			                   	 	echo '<span class="l" style="font-size:13px; color:#999; line-height:22px;">积分：'.$Integration1.'</span>';
			                   		
			                    }
			                    if(isset($sales1)){
			                   		echo '<span class="r" style="font-size:13px; color:#999; line-height:24px;">已售'.$sales1.'</span>';
			                
			                    }
			                    echo '<div class="clear"></div>';   
								if(!empty($dis1)){
									echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 3px;">'.$dis1.'折</a></span>';
								}else{
									echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'</span>';
								}
								if(isset($cart1)){
									echo " <span class='r' style='margin-top:3px;'><a href='javascript:void(0)' onclick='add_to_cart(".$v['Products_ID'].")' ><img src='/static/api/shop/skin/".$rsConfig['Skin_ID']."/gwct_x.png' width='20' height='20'></a></span>";
								}             
				                 echo '<input type="hidden" id="spec_list_'.$v['Products_ID'].'" value="'.$str.'" />
			                	<input type="hidden" id="ownerid_'.$v['Products_ID'].'" value="'.$owner["id"].'" />
			                	<input type="hidden" id="cart_key_'.$v['Products_ID'].'" value="'.$cart_key.'" />
			                	<input type="hidden" id="Qty_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="cattsel_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="url_'.$v['Products_ID'].'" value="'.$shop_url.'cart/ajax/" />
			                </div>
			            </li>';
					}
				}
				echo '</ul></div>'; 
			} elseif($value['list_type'] == '3') {
				echo '<div class="chanpint2_x">
                  <ul>';
                  if(!empty($productList)){
    				foreach($productList as $k=>$v){
					/*商品直接加入购物车开始*/
						$spec_list = array();
						$properties = get_product_properties($v['Products_ID']);  // 获得商品的规格和属性
						if(!empty($properties['spe'])){	
							$specification = $properties['spe'];
							foreach($specification as $Attr_ID=>$item){
								if($item['Attr_Type'] == 1){
									foreach($item['Values'] as $k1=>$v1){
										if($k1 == 0){
											$spec_list[] = $v1['id'];
										}
										
									}
								}
							}
						}
						$str = empty($spec_list)?'':implode(',',$spec_list);
						if($v['Products_IsVirtual'] == 1){
							$cart_key = 'Virtual';
						}else{
							$cart_key = 'CartList';
						}
						 echo '<li>
                          <a href="'.$v['link'].'"><span class="cpt_x"><img src="'.$v['JSON']['ImgPath'][0].'"></span></a>
                            <div class="cpxq1_x">
                              <div class="cpbt_x"><a href="'.$v['link'].'">'.$v['Products_Name'].'</a></div> ';
                              if(!empty($nature)){
								if(in_array('integration', $nature)){
									$Integration1 = $v['Products_Integration'];
								}
								if(in_array('sale', $nature)){
									$sales1 = $v['Products_Sales'];
								}
								$dis1 = '';
								if(in_array('dis', $nature)){
									if($v['Products_PriceY'] != 0 && $v['Products_PriceX'] < $v['Products_PriceY']){
										$dis1 = round(($v['Products_PriceX']/$v['Products_PriceY']),2);
										$dis1 = ($dis1*10);
									}
								}
								if(in_array('cart', $nature)){
									$cart1 = '1';
								}
							  }
							  if(isset($Integration1)) {
                                echo '<span class="l" style="font-size:13px; color:#999; line-height:22px;">积分：'.$Integration1.'分</span>';
                              }
                              if(isset($sales1)) {
                                echo '<span class="r" style="font-size:13px; color:#999; line-height:24px;">已售'.$sales1.'</span>';
                              }
                              echo '<div class="clear"></div>';
                              if(!empty($dis1)) {
                                echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 3px;">'.$dis1.'折</a></span>';
                              } else {
                                echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'</span>';
                              }
                              if(isset($cart1)) {
                                echo "<span class='r' style='margin-top:3px;'><a href='javascript:void(0)' onclick='add_to_cart(".$v['Products_ID'].")' ><img src='/static/api/shop/skin/".$rsConfig['Skin_ID']."/gwct_x.png' width='20' height='20'></a></span>";
                              }
                              echo '<input type="hidden" id="spec_list_'.$v['Products_ID'].'" value="'.$str.'" />
			                	<input type="hidden" id="ownerid_'.$v['Products_ID'].'" value="'.$owner["id"].'" />
			                	<input type="hidden" id="cart_key_'.$v['Products_ID'].'" value="'.$cart_key.'" />
			                	<input type="hidden" id="Qty_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="cattsel_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="url_'.$v['Products_ID'].'" value="'.$shop_url.'cart/ajax/" />
			                </div>
			            </li>';
					}
				}
				echo '</ul></div>';
			} elseif($value['list_type'] == '4') {
				echo '<div class="chanpint3_x">
                  <ul>';
                  if(!empty($productList)) {
    				foreach($productList as $k=>$v) {
					/*商品直接加入购物车开始*/
						$spec_list = array();
						$properties = get_product_properties($v['Products_ID']);  // 获得商品的规格和属性
						if(!empty($properties['spe'])) {	
							$specification = $properties['spe'];
							foreach($specification as $Attr_ID=>$item) {
								if($item['Attr_Type'] == 1) {
									foreach($item['Values'] as $k1=>$v1) {
										if($k1 == 0) {
											$spec_list[] = $v1['id'];
										}
										
									}
								}
							}
						}
						$str = empty($spec_list)?'':implode(',',$spec_list);
						if($v['Products_IsVirtual'] == 1) {
							$cart_key = 'Virtual';
						}else{
							$cart_key = 'CartList';
						}
						if(!empty($nature)) {
							if(in_array('cart', $nature)){
								$cart1 = '1';
							}
						}
						echo '<li>';
						if($k%3 == 0) {
							$Products_Name = mb_strwidth($v["Products_Name"], 'utf-8') > 20 ? mb_strimwidth($v["Products_Name"], 0, 20,'', 'utf-8') : substr($v['Products_Name'], 0, 20); 
							//$Products_Name = substr($v['Products_Name'], 0, 21);
							echo '<span class="big_pr l">';
								echo '<div class="pic"><img src="'.$v['JSON']['ImgPath'][0].'"></div>';
                            	if(isset($cart1)) {
                            		echo "<div class='name'><a href='".$v['link']."'>".$Products_Name."</a><br><span class='price l'>￥".$v['Products_PriceX']."</span><span class='r' style='margin-top:3px; margin-right:3px'><a href='javascript:void(0)' onclick='add_to_cart(".$v['Products_ID'].")' ><img src='/static/api/shop/skin/".$rsConfig['Skin_ID']."/gwct_x.png' width='20' height='20'></a></span></div>";
                            	} else {
                            		echo "<div class='name'><a href='".$v['link']."'>".$Products_Name."</a><br><span class='price l'>￥".$v['Products_PriceX']."</span></div>";
                            	} 
                            	
                            echo '</span>';
						}
						echo '<span class="smal_pr l">'; 
						if($k%3 == 1) {
							$Products_Name = mb_strwidth($v["Products_Name"], 'utf-8') > 10 ? mb_strimwidth($v["Products_Name"], 0, 10,'', 'utf-8') : $v['Products_Name'];
							echo '<div class="small_pr">
								<a href="'.$v['link'].'">
	                          	<span class="name l"><span class="price">￥'.$v['Products_PriceX'].'</span><br>'.$Products_Name.'</span>
	                          	<span class="pic l"><img src="'.$v['JSON']['ImgPath'][0].'"></span>
	                          	</a>
	                      	</div>';
						} 
						echo '<div class="clear"></div>';
						if($k%3 == 2) {
							$Products_Name = mb_strwidth($v["Products_Name"], 'utf-8') > 10 ? mb_strimwidth($v["Products_Name"], 0, 10,'', 'utf-8') : $v['Products_Name'];
							echo '<div>
	                          	<a href="'.$v['link'].'">
	                          	<span class="name l"><span class="price">￥'.$v['Products_PriceX'].'</span><br>'.$Products_Name.'</span>
	                          	<span class="pic l"><img src="'.$v['JSON']['ImgPath'][0].'"></span>
	                          	</a>
	                  		</div>';
						}
						echo '<input type="hidden" id="spec_list_'.$v['Products_ID'].'" value="'.$str.'" />
			                	<input type="hidden" id="ownerid_'.$v['Products_ID'].'" value="'.$owner["id"].'" />
			                	<input type="hidden" id="cart_key_'.$v['Products_ID'].'" value="'.$cart_key.'" />
			                	<input type="hidden" id="Qty_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="cattsel_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="url_'.$v['Products_ID'].'" value="'.$shop_url.'cart/ajax/" />
						</li>';
					}

				}
				echo '</ul>
              </div>'; 
			}
			echo '</div>';
		}elseif($value['type'] == 'p14'){//属性
			$condition = "where Users_ID='".$UsersID."' and Products_SoldOut=0 and Products_Status=1";			
			if(!empty($value['def'])){
				$def = $value['def'];
				if($value['def'] == '1'){
					$condition .= " and Products_IsRecommend = 1";
				}elseif($value['def'] == '2'){
					$condition .= " and Products_IsHot = 1";
				}elseif($value['def'] == '3'){
					$condition .= " and Products_IsNew = 1 ";
				}
			}else{
				$def = 0;
				
			}
			
			if(!empty($value['order'])){
				$order = $value['order'];
				if($value['order'] == '1'){
					$condition .= " order by Products_CreateTime desc";
				}elseif($value['order'] == '2'){
					$condition .= " order by Products_PriceX asc,Products_Index asc,Products_ID desc";
				}elseif($value['order'] == '3'){
					$condition .= " order by Products_Sales desc,Products_Index asc,Products_ID desc";
				}else{
					$condition .= " order by Products_Index asc,Products_ID desc";
				}
			}else{
				$order = 0;
			}
			if(!empty($owner['id'])){
				$more_url = $shop_url.'more/'.$def.'/'.$order.'/';
			}else{
				$more_url = $shop_url.'0/more/'.$def.'/'.$order.'/';
			}
			if(!empty($value['num'])){
				 $condition .= "  limit 0,".$value['num']."";
			}else{
				$condition .= " limit 0,10";
			}
			$productList = $DB->get("shop_products","*",$condition);	
			$productList = $DB->toArray($productList);
			foreach($productList as $key => $val){
				$productList[$key]['link'] = $shop_url.'products/'.$val["Products_ID"].'/';
				$productList[$key]['JSON'] = json_decode($val['Products_JSON'],true);
			}
			//更多文字
			if(!empty($value['txt'])){
				$txt = $value['txt'];
			}else{
				$txt = '更多';
			}
			if(!empty($value['p_title'])){
				$category_name = $value['p_title'];
			}else{
				$category_name = '默认列表';
			}
			//商品显示属性
			if(!empty($value['nature'])){
				$nature = explode(',',rtrim($value['nature'],','));
			}else{
				$nature = array();
			}
			echo '<div class="packagep14Sprite wrap_content">';
			if($value['top_type'] == '1'){
				echo '<div class="w">
					<div class="biaot_x">
				    	<span class="fenlei_x l">'.$category_name.'</span>';
				    	if($value['check_more'] == '1') {
					    	echo '<span class="more r"><a href="'.$more_url.'">'.$txt.'>></a></span>';
					    }
				    echo '</div>
				</div>';
			}elseif($value['top_type'] == '2'){
				echo '<div class="w">
					<div class="biaot1_x">
				    	<span class="fenlei_x l">'.$category_name.'</span>';
				    	if($value['check_more'] == '1') {
				        	echo '<span class="more r"><a href="'.$more_url.'">></a></span>';
				        }
				    echo '</div>
				</div>';
			}elseif($value['top_type'] == '3'){
				echo '<div class="w">
					<div class="biaot2_x">
				    	<span class="fenlei_x l">'.$category_name.'</span>';
				    	if($value['check_more'] == '1') {
				        	echo '<span class="r"><a href="'.$more_url.'"><span style="margin-right:15px;">'.$txt.'</span></a></span>';
				        }
				    echo '</div>
				</div>';
			}elseif($value['top_type'] == '4'){
				echo '<div class="w">
					<div class="biaot3_x">
						<div class="fenlei3_x l">'.$category_name.'</div>';
						if($value['check_more'] == '1') {
					        echo '<div class="more r"><a href="'.$more_url.'">'.$txt.'<span style="color:#e30101;">></span></a></div>';
					    }
				    echo '</div>
				</div>';
			}
			if($value['list_type'] == '1'){            
				echo '<div class="chanpint_x">
                         <ul>';
    			if(!empty($productList)) {
    				foreach($productList as $k=>$v) {
					/*商品直接加入购物车开始*/
						$spec_list = array();
						$properties = get_product_properties($v['Products_ID']);  // 获得商品的规格和属性
						if(!empty($properties['spe'])){	
							$specification = $properties['spe'];
							foreach($specification as $Attr_ID=>$item){
								if($item['Attr_Type'] == 1){
									foreach($item['Values'] as $k1=>$v1){
										if($k1 == 0){
											$spec_list[] = $v1['id'];
										}
										
									}
								}
							}
						}
						$str = empty($spec_list)?'':implode(',',$spec_list);
						if($v['Products_IsVirtual'] == 1){
							$cart_key = 'Virtual';
						}else{
							$cart_key = 'CartList';
						}
						echo '<li>
			            	<a href="'.$v['link'].'"><span class="cpt_x l"><img src="'.$v['JSON']['ImgPath'][0].'"></span></a>
			                <span class="cpxq_x l">
			                	<div class="cpbt_x"><a href="'.$v['link'].'">'.$v['Products_Name'].'</a></div>';
			                	if(!empty($nature)){
									if(in_array('integration', $nature)){
										$Integration2 = $v['Products_Integration'];
									}
									if(in_array('sale', $nature)){
										$sales2 = $v['Products_Sales'];
									}
									$dis2 = '';
									if(in_array('dis', $nature)){
										if($v['Products_PriceY'] != 0 && $v['Products_PriceX'] < $v['Products_PriceY']){
											$dis2 = round(($v['Products_PriceX']/$v['Products_PriceY']),2);
											$dis2 = ($dis2*10);
										}
									}
									if(in_array('cart', $nature)){
										$cart2 = '1';
									}
								}
								if(!empty($dis2)){
									echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 2px; font-weight:normal;">'.$dis2.'折</a></span>';
								}else{
									echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'</span>';
								}
								if(isset($Integration2)){
			                   		echo '<span class="r" style="font-size:13px; color:#999; line-height:28px;">积分：'.$Integration2.'</span>';
			                    }
			                    echo '<div class="clear"></div>';
			                    if(isset($sales2)){
			                   		echo '<span class="l" style="font-size:13px; color:#999; line-height:26px;">已售'.$sales2.'</span>';
			                    } 
								if(isset($cart2)){
									echo " <span class='r' style='margin-top:3px;'><a href='javascript:void(0)' onclick='add_to_cart(".$v['Products_ID'].")' ><img src='/static/api/shop/skin/".$rsConfig['Skin_ID']."/gwct_x.png' width='20' height='20'></a></span>";
								}	                                     
			                echo '<input type="hidden" id="spec_list_'.$v['Products_ID'].'" value="'.$str.'" />
			                	<input type="hidden" id="ownerid_'.$v['Products_ID'].'" value="'.$owner["id"].'" />
			                	<input type="hidden" id="cart_key_'.$v['Products_ID'].'" value="'.$cart_key.'" />
			                	<input type="hidden" id="Qty_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="cattsel_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="url_'.$v['Products_ID'].'" value="'.$shop_url.'cart/ajax/" />
			                </span>
			               </li>';						
					}
				}
				echo '</ul></div>';
			}elseif($value['list_type'] == '2'){
				echo '<div class="chanpint1_x">
                        <ul>';
				if(!empty($productList)){
    				foreach($productList as $k=>$v){
					/*商品直接加入购物车开始*/
						$spec_list = array();
						$properties = get_product_properties($v['Products_ID']);  // 获得商品的规格和属性
						if(!empty($properties['spe'])){	
							$specification = $properties['spe'];
							foreach($specification as $Attr_ID=>$item){
								if($item['Attr_Type'] == 1){
									foreach($item['Values'] as $k1=>$v1){
										if($k1 == 0){
											$spec_list[] = $v1['id'];
										}
										
									}
								}
							}
						}
						$str = empty($spec_list)?'':implode(',',$spec_list);
						if($v['Products_IsVirtual'] == 1){
							$cart_key = 'Virtual';
						}else{
							$cart_key = 'CartList';
						}
						echo '<li>
			            	<a href="'.$v['link'].'"><span class="cpt_x"><img src="'.$v['JSON']['ImgPath'][0].'"></span></a>
			               <div class="cpxq1_x">
			                	<div class="cpbt_x"><a href="'.$v['link'].'">'.$v['Products_Name'].'</a></div>';
			                	if(!empty($nature)){
									if(in_array('integration', $nature)){
										$Integration2 = $v['Products_Integration'];
									}
									if(in_array('sale', $nature)){
										$sales2 = $v['Products_Sales'];
									}
									$dis2 = '';
									if(in_array('dis', $nature)){
										if($v['Products_PriceY'] != 0 && $v['Products_PriceX'] < $v['Products_PriceY']){
											$dis2 = round(($v['Products_PriceX']/$v['Products_PriceY']),2);
											$dis2 = ($dis2*10);
										}
									}
									if(in_array('cart', $nature)){
										$cart2 = '1';
									}
								}
								if(isset($Integration2)){
			                   	 	echo '<span class="l" style="font-size:13px; color:#999; line-height:22px;">积分：'.$Integration2.'</span>';
			                   		
			                    }
			                    if(isset($sales2)){
			                   		echo '<span class="r" style="font-size:13px; color:#999; line-height:24px;">已售'.$sales2.'</span>';
			                
			                    }
			                    echo '<div class="clear"></div>';   
								if(!empty($dis2)){
									echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 3px;">'.$dis2.'折</a></span>';
								}else{
									echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'</span>';
								}
								if(isset($cart2)){
									echo " <span class='r' style='margin-top:3px;'><a href='javascript:void(0)' onclick='add_to_cart(".$v['Products_ID'].")' ><img src='/static/api/shop/skin/".$rsConfig['Skin_ID']."/gwct_x.png' width='20' height='20'></a></span>";
								}             
				                 echo '<input type="hidden" id="spec_list_'.$v['Products_ID'].'" value="'.$str.'" />
			                	<input type="hidden" id="ownerid_'.$v['Products_ID'].'" value="'.$owner["id"].'" />
			                	<input type="hidden" id="cart_key_'.$v['Products_ID'].'" value="'.$cart_key.'" />
			                	<input type="hidden" id="Qty_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="cattsel_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="url_'.$v['Products_ID'].'" value="'.$shop_url.'cart/ajax/" />
			                </div>
			            </li>';
					}
				}
				echo '</ul></div>'; 
			} elseif($value['list_type'] == '3') {
				echo '<div class="chanpint2_x">
                  <ul>';
                  if(!empty($productList)) {
    				foreach($productList as $k=>$v) {
					/*商品直接加入购物车开始*/
						$spec_list = array();
						$properties = get_product_properties($v['Products_ID']);  // 获得商品的规格和属性
						if(!empty($properties['spe'])) {	
							$specification = $properties['spe'];
							foreach($specification as $Attr_ID=>$item) {
								if($item['Attr_Type'] == 1) {
									foreach($item['Values'] as $k1=>$v1) {
										if($k1 == 0) {
											$spec_list[] = $v1['id'];
										}
										
									}
								}
							}
						}
						$str = empty($spec_list)?'':implode(',',$spec_list);
						if($v['Products_IsVirtual'] == 1) {
							$cart_key = 'Virtual';
						}else{
							$cart_key = 'CartList';
						}
						 echo '<li>
                          <a href="'.$v['link'].'"><span class="cpt_x"><img src="'.$v['JSON']['ImgPath'][0].'"></span></a>
                            <div class="cpxq1_x">
                              <div class="cpbt_x"><a href="'.$v['link'].'">'.$v['Products_Name'].'</a></div> ';
                              if(!empty($nature)) {
								if(in_array('integration', $nature)) {
									$Integration2 = $v['Products_Integration'];
								}
								if(in_array('sale', $nature)){
									$sales2 = $v['Products_Sales'];
								}
								$dis2 = '';
								if(in_array('dis', $nature)){
									if($v['Products_PriceY'] != 0 && $v['Products_PriceX'] < $v['Products_PriceY']){
										$dis2 = round(($v['Products_PriceX']/$v['Products_PriceY']),2);
										$dis2 = ($dis2*10);
									}
								}
								if(in_array('cart', $nature)){
									$cart2 = '1';
								}
							  }
							  if(isset($Integration2)) {
                                echo '<span class="l" style="font-size:13px; color:#999; line-height:22px;">积分：'.$Integration2.'分</span>';
                              }
                              if(isset($sales2)) {
                                echo '<span class="r" style="font-size:13px; color:#999; line-height:24px;">已售'.$sales2.'</span>';
                              }
                              echo '<div class="clear"></div>';
                              if(!empty($dis2)) {
                                echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'<a style="font-size:10px; color:#fff; margin-left:5px;  background:#333; padding:0 3px;">'.$dis2.'折</a></span>';
                              } else {
                                echo '<span class="jiaget_x l">￥'.$v['Products_PriceX'].'</span>';
                              }
                              if(isset($cart2)) {
                                echo "<span class='r' style='margin-top:3px;'><a href='javascript:void(0)' onclick='add_to_cart(".$v['Products_ID'].")' ><img src='/static/api/shop/skin/".$rsConfig['Skin_ID']."/gwct_x.png' width='20' height='20'></a></span>";
                              }
                              echo '<input type="hidden" id="spec_list_'.$v['Products_ID'].'" value="'.$str.'" />
			                	<input type="hidden" id="ownerid_'.$v['Products_ID'].'" value="'.$owner["id"].'" />
			                	<input type="hidden" id="cart_key_'.$v['Products_ID'].'" value="'.$cart_key.'" />
			                	<input type="hidden" id="Qty_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="cattsel_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="url_'.$v['Products_ID'].'" value="'.$shop_url.'cart/ajax/" />
			                </div>
			            </li>';
					}
				}
				echo '</ul></div>';
			} elseif($value['list_type'] == '4') {
				echo '<div class="chanpint3_x">
                  <ul>';
                  if(!empty($productList)) {
    				foreach($productList as $k=>$v) {
					/*商品直接加入购物车开始*/
						$spec_list = array();
						$properties = get_product_properties($v['Products_ID']);  // 获得商品的规格和属性
						if(!empty($properties['spe'])) {	
							$specification = $properties['spe'];
							foreach($specification as $Attr_ID=>$item) {
								if($item['Attr_Type'] == 1) {
									foreach($item['Values'] as $k1=>$v1) {
										if($k1 == 0) {
											$spec_list[] = $v1['id'];
										}
										
									}
								}
							}
						}
						$str = empty($spec_list)?'':implode(',',$spec_list);
						if($v['Products_IsVirtual'] == 1) {
							$cart_key = 'Virtual';
						}else{
							$cart_key = 'CartList';
						}
						if(!empty($nature)) {
							if(in_array('cart', $nature)){
								$cart2 = '1';
							}
						}
						echo '<li>';
						if($k%3 == 0) {
							$Products_Name = mb_strwidth($v["Products_Name"], 'utf-8') > 20 ? mb_strimwidth($v["Products_Name"], 0, 20,'', 'utf-8') : substr($v['Products_Name'], 0, 20); 
							//$Products_Name = substr($v['Products_Name'], 0, 21);
							echo '<span class="big_pr l">';
								echo '<div class="pic"><img src="'.$v['JSON']['ImgPath'][0].'"></div>';
                            	if(isset($cart2)) {
                            		echo "<div class='name'><a href='".$v['link']."'>".$Products_Name."</a><br><span class='price l'>￥".$v['Products_PriceX']."</span><span class='r' style='margin-top:3px; margin-right:3px'><a href='javascript:void(0)' onclick='add_to_cart(".$v['Products_ID'].")' ><img src='/static/api/shop/skin/".$rsConfig['Skin_ID']."/gwct_x.png' width='20' height='20'></a></span></div>";
                            	} else {
                            		echo "<div class='name'><a href='".$v['link']."'>".$Products_Name."</a><br><span class='price l'>￥".$v['Products_PriceX']."</span></div>";
                            	} 
                            	
                            echo '</span>';
						}
						echo '<span class="smal_pr l">'; 
						if($k%3 == 1) {
							$Products_Name = mb_strwidth($v["Products_Name"], 'utf-8') > 10 ? mb_strimwidth($v["Products_Name"], 0, 10,'', 'utf-8') : $v['Products_Name'];
							echo '<div class="small_pr">
								<a href="'.$v['link'].'">
	                          	<span class="name l"><span class="price">￥'.$v['Products_PriceX'].'</span><br>'.$Products_Name.'</span>
	                          	<span class="pic l"><img src="'.$v['JSON']['ImgPath'][0].'"></span>
	                          	</a>
	                      	</div>';
						} 
						echo '<div class="clear"></div>';
						if($k%3 == 2) {
							$Products_Name = mb_strwidth($v["Products_Name"], 'utf-8') > 10 ? mb_strimwidth($v["Products_Name"], 0, 10,'', 'utf-8') : $v['Products_Name'];
							echo '<div>
	                          	<a href="'.$v['link'].'">
	                          	<span class="name l"><span class="price">￥'.$v['Products_PriceX'].'</span><br>'.$Products_Name.'</span>
	                          	<span class="pic l"><img src="'.$v['JSON']['ImgPath'][0].'"></span>
	                          	</a>
	                  		</div>';
						}
						echo '<input type="hidden" id="spec_list_'.$v['Products_ID'].'" value="'.$str.'" />
			                	<input type="hidden" id="ownerid_'.$v['Products_ID'].'" value="'.$owner["id"].'" />
			                	<input type="hidden" id="cart_key_'.$v['Products_ID'].'" value="'.$cart_key.'" />
			                	<input type="hidden" id="Qty_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="cattsel_'.$v['Products_ID'].'" value="1" />
			                	<input type="hidden" id="url_'.$v['Products_ID'].'" value="'.$shop_url.'cart/ajax/" />
						</li>';
					}

				}
				echo '</ul>
              </div>'; 
			}
			echo '</div>';
		}
	  }
  }
?>
 </div>
</div>
<div id="back-to-top"></div>
<?php require_once('skin/distribute_footer.php'); ?>
<script>  
	$(function(){  
		//当滚动条的位置处于距顶部100像素以下时，跳转链接出现，否则消失  
		$(function () {  
			$(window).scroll(function(){  
				if ($(window).scrollTop()>100){  
					$("#back-to-top").fadeIn(1500);  
				}  
				else  
				{  
					$("#back-to-top").fadeOut(1500);  
				}  
			});  
	  
			//当点击跳转链接后，回到页面顶部位置  
	  
			$("#back-to-top").click(function(){  
				$('body,html').animate({scrollTop:0},1000);  
				return false;  
			});  
		});  
	});  

</script>
<script type='text/javascript' >
	function add_to_cart(p){
		var spec_list = $("#spec_list_"+p+"").val();
		var url       = $("#url_"+p+"").val();
		var ownerid   = $("#ownerid_"+p+"").val();
		var cart_key  = $("#cart_key_"+p+"").val();
		var Qty       = $("#Qty_"+p+"").val();
		var cattsel   = $("#cattsel_"+p+"").val();
		var UsersID   = $("#UsersID_"+p+"").val();
		$.post(url,'ProductsID='+p+'&spec_list='+spec_list+'&cattsel='+cattsel+'&Qty='+Qty+'&OwnerID='+ownerid+'&cart_key='+cart_key,
			function(data){
				if (data.status == 1) {
					alert("产品成功加入购物车!");
				}else{
					alert(data.msg);
				}
		},'json');
	}

</script>
<!--懒加载--> 
<script type='text/javascript' src='/static/js/plugin/lazyload/jquery.scrollLoading.js'></script> 
<script language="javascript">
	$("img").scrollLoading();
</script>
</body>
</html>