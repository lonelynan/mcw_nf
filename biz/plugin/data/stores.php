<?php
require_once('../../global.php');
require_once(CMS_ROOT . '/include/helper/tools.php');
require_once('config.php');
use Illuminate\Database\Capsule\Manager as DB;

$url_param = ['Users_ID' => $rsBiz['Users_ID'], 'Biz_ID' => $rsBiz['Biz_ID']];
$build = DB::table('stores')->where($url_param);
/*******************************列表搜索 start *************************************/
$search = $request->input('search');
if($search){
	$keywords = $request->input('keywords');
	if(!empty($url_param)){
		$build->where(function($query) use($keywords){
			$query->where('Stores_Name', 'like', '%'.$keywords.'%')->where('Stores_Telephone', 'like', '%'.$keywords.'%')->where('Stores_No', 'like', '%'.$keywords.'%')->where('Stores_Contact', 'like', '%'.$keywords.'%');
		});
	}
}
/*******************************列表搜索 end *************************************/

$pagesize = 15;
$paginate_obj = $build->orderBy('Stores_CreateTime', 'desc')->paginate($pagesize);
$paginate_obj->setPath(base_url('/biz/plugin/data/stores.php'));
if(!empty($url_param)){
	$paginate_obj->appends($url_param);
}

$list = $paginate_obj->toArray()['data'];
$page_link = $paginate_obj->render();
if(empty($list)){
	$business_list = array_flip($business_list);
	foreach($list as $k => $v){
		$list[$k]['Stores_CreateTime'] = date('Y-m-d H:i:s', $v['Stores_CreateTime']);
		$list[$k]['Stores_Business'] = $business_list[$v['Stores_Business']];
	}
}
$jcurid = 2;
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
    <link href='/static/member/css/stores.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/jquery-1.11.1.min.js'></script>
    <script type='text/javascript' src='/static/js/plugin/layer/layer.js'></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
    <script type='text/javascript' src='/static/member/js/stores.js'></script>
</head>
<body>
<div id="iframe_page">
    <div class="iframe_content">
        <?php require_once('menubar.php'); ?>
        <div id="stores" class="r_con_wrap">
            <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
                <thead>
                <tr>
                    <td width="5%">序号</td>
					<td width="5%">门店编号</td>
					<td width="5%">门店类型</td>
                    <td width="15%">门店名称</td>
					<td width="15%">联系人</td>
                    <td width="15%">联系电话</td>
					<td width="5%">地区</td>
                    <td width="20%">详细地址</td>
                    <td width="5%">添加日期</td>
                    <td width="30%" class="last">操作</td>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($list)) {
                    foreach ($list as $k => $v) {
                ?>
					<tr>
						<td nowrap="nowrap"><?=$v["Stores_ID"] ?></td>
						<td><?=$v["Stores_No"] ?></td>
						<td><?=$v["Stores_Business"] ?></td>
						<td><?=$v["Stores_Name"] ?></td>
						<td><?=$v["Stores_Contact"] ?></td>
						<td><?=$v["Stores_Telephone"] ?></td>
						<td><?=$v["Stores_City"] ?><?=$v["Stores_Area"] ?></td>
						<td><?=$v["Stores_Address"] ?></td>
						<td><?=$v["Stores_CreateTime"] ?></td>
						<td>
							<a href="edit_stores.php?id=<?=$id ?>&StoreID=<?=$v["Stores_ID"] ?>"><img src="/static/member/images/ico/mod.gif" align="absmiddle" alt="修改" /></a>
							<a href="view_stores.php?id=<?=$id ?>&StoreID=<?=$v["Stores_ID"] ?>"><img src="/static/member/images/ico/view.gif" align="absmiddle" alt="查看"/></a>
						</td>
					</tr>
				<?php }} ?>
                </tbody>
            </table>
            <div class="blank20"></div>
            <?=$page_link ?>
        </div>
    </div>
</div>
</body>
</html>