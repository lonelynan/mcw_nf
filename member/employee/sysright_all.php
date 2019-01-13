<?php
/*edit in 20160319*/
$sysrmenu = [];
$sysrmenusub = [];
$sysrmenu["web"] = array(
	'web' => '微官网',
	'config' => '基本设置',
	'skin' => '风格设置',
	'home' => '首页设置',
	'column' => '栏目管理',
	'article' => '内容管理',
	'lbs' => '一键导航'
);
$sysrmenu["kanjia"] = array(
	'kanjia'=>'微砍价'
);
$sysrmenu["votes"] = array(
	'votes'=>'微投票'	
);

$sysrmenu["zhuli"] = array(
	'zhuli'=>'微助力'
);
$sysrmenu["weicuxiao"] = array(
	'weicuxiao' => '微促销',
	'sctrach' => '刮刮卡',
	'fruit' => '水果达人',
	'turntable' => '欢乐大转盘',
	'battle' => '一战到底',
	'guanggao' => '广告中心'
);
// $sysrmenu["games"] = array(
	// 'games' => '游戏中心',
	// 'config' => '基础设置',
	// 'lists' => '游戏管理'
// );

//不在右键菜单内的,栏目管理
$sysrmenusub["web_column"] = array(
	'column_add' => '添加栏目',
	'column_edit' => '修改栏目'
);
$sysrmenusub["zhu_zhuli"] = array(
	'friend'=>'查看用户',
	'config'=>'基本设置',
	'rules'=>'活动规则',
	'awordrules'=>'兑奖规则',
	'prize'=>'奖品设置',
	'users'=>'用户列表'
);
//内容管理
$sysrmenusub["web_article"] = array(
	'article_add' => '添加内容',
	'article_edit' => '修改内容'
);
//活动设置
$sysrmenusub["kan_kanjia"] = array(
	'activity_add' => '创建活动',
	'activity_edit' => '编辑活动',
	'orders_view' => '订单详情',
	'config'=>'基本设置',
	'activity_list'=>'活动设置',
	'orders'=>'订单管理',
	'commit'=>'评论管理',
	'ajax'=>'AJAX'
);

//项目管理
$sysrmenusub["zho_project"] = array(
	'project_add' => '添加项目',
	'project_edit' => '修改项目',
	'prize' => '设置',
	'users' => '查看',
	'prize_add' => '添加赠品',
	'prize_edit' => '修改赠品',
	'users_view' => '查看预览'	
);

//游戏管理
$sysrmenusub["gam_lists"] = array(
	'add_1' => '我是你的小苹果',
	'add_2' => '一个都别掉',
	'add_3' => '2048',
	'add_4' => '全民拼图',
	'add_5' => '宠物碰碰对',
	'edit_1' => '',
	'edit_2' => '',
	'edit_3' => '',
	'edit_4' => '',
	'edit_5' => ''
);
//投票管理
$sysrmenusub["vot_votes"] = array(
	'votes_add' => '添加投票',
	'votes_edit' => '修改投票',
	'items' => '管理',
	'orders' => '列表',
	'items_add' => '添加',
	'items_edit' => '修改',
	'config'=>'基本设置',
	'votemanage'=>'投票管理'
);
?>