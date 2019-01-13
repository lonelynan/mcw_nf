      <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="10%" nowrap="nowrap">序号</td>
            <td width="20%" nowrap="nowrap">名称</td>
            <td width="60%" nowrap="nowrap" class="last">Url</td>
          </tr>
        </thead>
        <tbody>
          <?php
				$list_biz = array();				
				$i = 1;
				$DB->query("select Biz_ID,Biz_Name,Is_Union from biz as b left join biz_group g on b.Group_ID=g.Group_ID where g.Group_IsStore=1 and b.Users_ID='".$_SESSION["Users_ID"]."' order by b.Biz_ID asc");				
				while($r=$DB->fetch_assoc()){
					$list_biz[] = $r;
				}
				
				foreach($list_biz as $k=>$v){
					$i++;
					if ($v["Is_Union"]) {
						$colorred = 'red';
						$gourl = 'http://'.$rulreal.'/api/'.$_SESSION["Users_ID"].'/shop/biz/'.$v["Biz_ID"].'/';
					} else {
						$colorred = '';
						$gourl = 'http://'.$rulreal.'/api/'.$_SESSION["Users_ID"].'/biz/'.$v["Biz_ID"].'/';
					}
		  ?>
          <tr>
            <td nowrap="nowrap"><?php echo $i;?></td>
            <td nowrap="nowrap" class="<?=$colorred?>"><?php echo $v["Biz_Name"];?></td>
            <td nowrap="nowrap" class="left last">
            	<?=$gourl?>
            </td>
          </tr>
          <?php
		  		}
		  ?>
        </tbody>
      </table>