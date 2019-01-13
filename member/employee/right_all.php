<?php
/*edit in 20160317*/
define('ONLIY_SITE', 'www.401ban.com');
$rmenu["jiebase"] = array(
	'jiebase'=>'基础设置',
	'bt_domain' => '域名绑定设置',    
	'config' => '在线客服设置',	
	'setting' => '系统设置',	
	'shopping' => '支付',
	'index' => '系统升级',
	'shipping' => '快递管理',
	'settingdianzi_print' => '快递鸟账号管理',
	'renewal_record'=>'购买记录'
);
$rmenu["weixin"] = array(
	'weixin' => '我的微信',
	'token_set' => '微信接口配置',
	'attention_reply' => '首次关注设置',
	'menu' => '自定义菜单设置',
	'keyword_reply' => '关键词回复设置',
	'index' => '素材管理',
	'articles' => '文章发布',
	'account' => '我的帐号',
	'profile' => '修改密码',
	'guide' => '操作指南'
);
$rmenu['basic'] = array(
	'basic' => '商城设置',
	'setting/config' => '基本设置',
	'setting/other_config' => '活动设置',
	'setting/skin' => '风格设置',
	'setting/home' => '首页设置',
    'setting/skin_program' => '小程序风格设置',
    'setting/home_program' => '小程序首页设置',
	'setting/menu_config' => '菜单配置',
	'setting/switch' => '开关设置',
	'setting/third_login' => '三方登录',
	'setting/report' => '统计报告',
	'pagemakeup/skin' => '制作页面'
);
if(!empty($_SESSION['Users_ID'])){
	require_once($_SERVER['DOCUMENT_ROOT'] . '/member/plugin/menu_config.php');
}
$rmenu['active'] = array(
	'active' => '活动管理',
	'active_list' => '活动列表',
	'active_add' => '发起活动'
);

$rmenu['biz']=array(
	'biz' => '商家管理',
	'index' => '普通商家列表',
	'union' => '联盟商家列表',
	'money_set' => '批量设置',
	'group' => '商家分组',
    'apply_config' => '入驻描述设置',
	'reg_config' => '注册页面设置',
    'apply_other' => '年费设置',
	'apply' => '入驻资质审核列表',
    'authpay' => '入驻支付列表',
    'chargepay' => '续费支付列表',
    'bond_back' => '保证金退款',
    'article_man' => '文章管理',
    'notices' => '公告管理'
);

$rmenu['product']=array(
	'product' => '产品管理',
	'commision_setting' => '佣金设置',	
	'products' => '产品列表',
	'category' => '产品分类',	
	'commit' => '产品评论管理',
	'orders' => '订单管理',
	'backups' => '退货单管理',
	'oofcommision_setting' => '线下佣金设置'
);

$rmenusub["bia_serverlist"] = array(
	'config' => '基本配置',
	'serverlist'=>'服务管理列表',
	'phone_add'=>'话费服务添加',
	'add_data'=>'流量服务添加',
	'phone_exit'=>'话费服务修改',
	'exit_data'=>'流量服务修改啊',
	'Ajax'=>'ajax处理文件',
	'Aja'=>'ajax处理文件'
);

$rmenu["distribute"] = array(
	'distribute' => '分销管理',
	'setting' => '基础设置',
	'account' => '分销账号管理',
	'record' => '分销记录',
	'agent' => '代理商',
	'shareholder' => '股东',
	'salesman_config' => '创始人设置',
	'withdraw' => '提现管理',
	'get_product' => '领取商品',
	'order_history' => '购买级别订单'
);

$rmenu["user"] = array(
	'user' => '会员中心',
	'config' => '基本设置',
	'user_list' => '会员管理',
	//'card_config' => '会员卡设置',
	//'coupon_list' => '优惠劵管理',
	//'coupon_add' => '新增优惠券',	
	'gift_orders' => '礼品兑换管理',
	'business_password' => '商家密码设置',
	'message' => '消息发布管理'
	//'alluseryielist' => '全部收益明细'
);

$rmenu["marketing"] = array(
	'marketing' => '营销推广',
	'pintuan'=>'拼团',
	//'cloud'=>'云购物',
//    'zhongchou'=>'微众筹'
);

/*$rmenu["stores"] = array(
	'stores' => '门店定位',
	'config' => '基本设置',
	'index' => '门店管理'
);*/
$rmenu["finance"] = array(
	'finance' => '财务结算',
	'sales_record' => '销售记录',
	'payment' => '付款单'
);
// $rmenu["pcsite"] = array(
		// 'pcsite' => 'PC站点管理',
        // 'index_block'=>'首页设置',
		// 'index_focus'=>'幻灯片管理',
		// 'index'=>'基本设置',
		// 'share_setting'=>'分享设置',
		// 'menu_index'=>'导航设置'
// );
$rmenusub["sta_user"] = array(
	'user_area' => '会员来源地统计',	
);

$rmenusub["sta_shop"] = array(
	'visit' => '商城访问统计',
	'sales_money'=>'销售额',
	'distributor'=>'分销商',
	'commission_money'=>'佣金',
	'output'=>'数据导出',
	'outputExcel'=>'数据导出'
	
);

$rmenu["weicuxiao"] = array(
	'guanggao' => '广告中心'
);
//其他权限
$rmenu["html"] = array(
	'html'=>'素材库',
	'material'=>'素材库',
	'spread'=>'推广技巧'
);

$rmenu['employee'] = array(
	'employee'=>'员工管理',
	'emplist'=>'员工管理',
	'emp_add'=>'添加员工',
	'charlist'=>'角色管理',
	'char_add'=>'创建角色'
);

//不在右键菜单内的,系统首页
//自定义菜单设置
$rmenusub["wei_menu"] = array(
	'menu_add' => '添加菜单',
	'menu_edit' => '修改菜单',
	'sysurl_http' => '开关连接',
	'auth_set' => '微信授权配置'
);
//关键词回复设置
$rmenusub["wei_keyword_reply"] = array(
	'keyword_edit' => '添加关键字'
);
//素材管理
$rmenusub["wei_index"] = array(
	'url' => '自定义URL管理',
	'sysurl' => '系统URL查询',
	'add' => '单图文',
	'madd' => '多图文',
	'edit' => '单图文修改',
	'medit' => '多图文修改',
);
//商家列表
$rmenusub["biz_index"] = array(
	'add' => '添加商家',
	'edit' => '修改商家'
);
//商家列表
$rmenusub["biz_union"] = array(
	'union_add' => '添加联盟商家',
	'union_edit' => '修改联盟商家',
	'category' => '行业分类',
	'category_add' => '添加行业分类',
	'category_edit' => '修改行业分类',
	'region' => '区域',
	'region_add' => '添加区域',
	'region_edit' => '修改区域',
	'price' => '价格',
    'union_pro_skin' => '小程序风格设置',
    'union_pro_home' => '小程序首页设置',
	'union_skin' => '模版选择',
    'union_home' => '模版设置'
);
//商家分组
$rmenusub["biz_group"] = array(
	'group_add' => '添加商家分组',
	'group_edit' => '修改商家分组'
);
//文章管理
$rmenusub["biz_article_man"] = array(
	'article_add' => '添加文章',
	'article_edit' => '修改文章',
        'articlecate_man' => '分类管理',
        'articles_category_add' => '分类增加',
        'articles_category_edit' => '分类修改'
);
//系统公告
$rmenusub["biz_notices"] = array(
    'notice_add' => '添加公告',
    'notice_edit' => '修改公告'
);
$rmenusub["biz_apply_config"] = array(
	'apply_other' => '年费设置',
	 
);
$rmenusub["biz_bond_back"] = array(
	'back_detail' => '保证金退款查看',	 
);
$rmenusub["biz_apply"] = array(
	'apply_detail' => '资质查看',	 
);
//文章管理
$rmenusub["wei_articles"] = array(
	'articles_category' => '分类管理',
	'article_add' => '添加文章',
	'article_edit' => '修改文章',
	'articles_category_add' => '添加文章分类',
	'articles_category_edit' => '修改文章分类'
);

$rmenusub["jie_setting"] = array(
	'sms' => '短信配置'
);
$rmenusub["jie_config"] = array(
	'weixin_config' => '微信客服设置'
);
$rmenusub["jie_index"] = array(
	'update' => '升级'
);

$rmenusub["jie_settingdianzi_print"] = array(
	'settingdianzi_printadd' => '电子面单设置添加',
	'settingdianzi_printedit' => '电子面单设置修改'
);

/* $rmenusub["jie_bmconfig"] = array(
	'serverlist' => '服务列表',
	'phone_add'=>'话费服务添加',
	'add_data'=>'流量服务添加',
	'phone_exit'=>'话费服务修改',
	'exit_data'=>'流量服务修改啊',
	'Ajax'=>'ajax处理文件',
	'Aja'=>'ajax处理文件'
); */
//图文消息管理
$rmenusub["mat_index"] = array(
	'add' => '单图文',
	'madd' => '多图文',
	'edit' => '单图文修改',
	'medit' => '多图文修改'
);
//商城设置/基本设置
$rmenusub["bas_setting/config"] = array(
	'setting/sms_add' => '短信购买',
	'setting/reason' => '退货理由',
	'setting/damage' => '退货损坏说明',
	'setting/damage_add' => '退货损坏说明添加',
	'setting/damage_edit' => '退货损坏说明修改',	
	'setting/switch_xiug' => '修改开关',
	'setting/task_config' => '自动结算配置',
	'setting/switch_distribute' => '分销开关',
	'setting/switch_user' => '个人开关',
	'setting/sms_record' => '短信购买记录',
	'setting/send_record' => '短信购买发送记录',
	'pagemakeup/home' => '页面设置'
);
//产品列表
$rmenusub["pro_products"] = array(	
	'output' => '导出产品',
	'ajax' => 'ajax处理文件',
	'products_edit' => '修改产品'
);
//产品分类管理
$rmenusub["pro_category"] = array(
	'category_add' => '添加分类',
	'category_edit' => '修改分类'
);

//产品类型管理
$rmenusub["pro_product_type"] = array(
	'product_type_add' => '添加类型',
	'product_type_edit' => '修改类型'
);
//虚拟卡密管理
$rmenusub["pro_virtual_card"] = array(
	'virtual_card_add' => '添加卡密',
	'virtual_card_edit' => '修改卡密',
	'virtual_card_type' => '卡密类型列表',
	'virtual_card_type_add' => '添加卡密类型',
	'virtual_card_type_edit' => '修改卡密类型'
);
//订单管理
$rmenusub["pro_orders"] = array(
	'virtual_orders_view' => '订单详情',
	'virtual_orders' => '消费认证',
	'orders_send' => '发货',
	'orders_confirm' => '确认订单',
	'send_print' => '打印发货单',
	'offline_orders' => '线下订单管理',
	'order_print' => '详情页面打印订单'
);
//退货单管理
$rmenusub["pro_backups"] = array(
	'orders_view' => '相关订单',
	'back_view' => '查看详情'
);
//模块设置
$rmenusub["dis_setting"] = array(
	'ajax' => 'ajax文件',
	'level' => '弹出级别',
	'level_edit' => '弹出级别修改',
	'level_add' => '弹出级别增加',
	'setting_withdraw' => '提现设置',
	'setting_other' => '其它设置',
	'setting_protitle' => '爵位设置',
	'setting_distribute' => '分销首页设置',
    'dismanual_orders' => '手动申请审核',
    'dismanual_orders_view' => '手动申请审核详情',
    'dismanual_orders_confirm' => '手动申请进行审核',
	'get_product_add' => '添加商品',
	'get_product_edit' => '修改商品',
	'get_product_orders' => '商品订单',
	'get_product_orders_view' => '商品订单查看',
	'levelview' => '分销级别详情'
);
//分销账号管理
$rmenusub["dis_account"] = array(
	'account_posterity' => '下属'
);
//代理商
$rmenusub["dis_agent"] = array(
	'agent_list' => '代理商列表',
	'agent_orders' => '代理商订单',
	'agent_orders_view' => '代理订单详情',
	'agent_orders_confirm' => '代理订单审核'	
);
//股东
$rmenusub["dis_shareholder"] = array(
	'sha_list' => '股东列表',
	'sha_orders' => '股东订单列表',
	'sha_orders_view' => '股东订单详情',
	'sha_orders_confirm' => '股东订单审核'	
);
//创始人列表
$rmenusub["dis_salesman_config"] = array(
	'salesman' => '创始人列表',
	'salesmansqrecorde' => '创始人申请列表',
	'salesmansqrecorde_view' => '查看审核内容'
);
//提现管理
$rmenusub["dis_withdraw"] = array(
	'withdraw_method' => '提现管理',
	'withdraw_method_add' => '提现添加',
	'withdraw_method_edit' => '提现修改'
);
//购买级别订单
$rmenusub["dis_order_history"] = array(
	'commsion' => '佣金记录'
);
//付款单
$rmenusub["fin_payment"] = array(
	'payment_add' => '生成付款单',
	'payment_detail' => '生成付款单详情'
);
//基本设置
$rmenusub["use_config"] = array(
	'lbs' => '一键导航'
);
//会员管理
$rmenusub["use_user_list"] = array(
	'user_view' => '会员查看',
	'user_level' => '会员等级设置',
	'user_profile' => '会员注册资料',
	'card_benefits' => '会员权利说明',
	'card_benefits_add' => '会员权利说明添加内容',
	'card_benefits_edit' => '会员权利说明修改内容',
	'user_capital' => '资金动向',
	'gift_orders_view' => '查看详情',
	'do_order' => '手动下单'
);
//优惠劵管理
$rmenusub["use_coupon_list"] = array(
	'coupon_config' => '优惠劵设置',
	'coupon_list_logs' => '优惠劵使用记录',
	'coupon_edit' => '编辑优惠券'
);
//礼品兑换管理
$rmenusub["use_gift_orders"] = array(
	'gift' => '礼品管理',
	'gift_add' => '礼品添加',
	'gift_orders_view' => '查看详情',
	'gift_edit' => '礼品修改'
);
//消息发布管理
$rmenusub["use_message"] = array(
	'message_add' => '添加消息',
	'message_edit' => '修改消息'
);
//门店管理
$rmenusub["sto_index"] = array(
	'add' => '新增门店',
	'edit' => '门店编辑'
);
//粉丝数据统计
$rmenusub["sta_fans"] = array(
	'visit' => '粉丝数据统计'
);
//员工管理
$rmenusub["emp_emplist"] = array(
	'emp_edit' => '员工修改'
);
//角色管理
$rmenusub["emp_charlist"] = array(
	'char_edit' => '角色修改'
);
//商品领取管理
// $rmenusub["mar_cloud"] = array(
	// 'shipping_orders_view'=>'商品领取详情',
	// 'shipping_orders_send'=>'商品领取发货',
	// 'shipping_orders_recieve' => '批量收货',
	// 'virtual_orders' => '消费认证',
	// 'virtual_orders_view'=>'订单明细查看',
	// 'slide_add' => '添加幻灯',
	// 'slide_edit' => '修改幻灯',
	// 'category_add' => '添加分类',
	// 'category_edit' => '修改分类',
	// 'products_add' => '添加产品',
	// 'products_edit' => '修改产品',
	// 'products_detail_list' => '查看往期',
	// 'buyrecords' => '购买详细'
// );

//角色管理

$rmenusub['act_active']=array(
    'active_add' => '活动添加',
    'active_edit' => '活动编辑',
    'active' => '活动浏览',
    'biz_active'=>'商家浏览',
    'active_view'=>'商家产品浏览',
    'type_add' => '类型添加',
    'type_edit' => '类型编辑',
    'active_edit' => '活动修改'

);

/*edit拼团20160423--st--*/
$rmenusub["mar_pintuan"] = array(
    //'virtual_card_add' => '添加卡密',
    //'virtual_card_edit' => '修改卡密',
    //'virtual_card_type' => '卡密类型列表',
    //'virtual_card_type_add' => '添加卡密类型',
    //'virtual_card_type_edit' => '修改卡密类型',
    //'virtual_card_select' => '卡密绑定',
	//'cate_add'=>'拼团分类添加',
    //'cate_edit'=>'拼团分类修改',
    //'product_add'=>'拼团商品添加',
    //'product_edit'=>'拼团商品修改',
    //'product_newadd'=>'商城商品添加',
	'orders_view' => '订单详情',
	'orders_send' => '发货',
	'orders_confirm' => '确认订单',
	'send_print' => '打印发货单',
	'order_print' => '详情页面打印订单',
	'output'=>"导出",
	'ajax'=>"ajax处理文件",
	//'pintuan'=>'拼团',
    //'config' => '基本设置',
    //'home' => '首页设置',
    //'products' => '产品列表',
    //'cate' => '分类管理',
    'orders' => '订单管理',
    //'aword' => '抽奖统计',
    //'awordConfig'=>'计划任务配置'
);
/*edit拼团20160423--end--*/
/* 云购配置开始 */
$rmenusub["mar_cloud"] = array(
	'cloud'=>'云购物',
    'config'=>'基本设置',
	'products'=>'产品列表',	 
	'category'=>'商品分类',	
	'slide_list'=>'首页幻灯片',
	'orders'=>'订单明细管理',
	'products_add' => '添加产品',
	'products_edit' => '修改产品',
	'products_detail_list' => '查看往期',
	'buyrecords' => '购买详细',
	'category_add' => '添加分类',
	'category_edit' => '修改分类',
	'slide_add' => '添加幻灯',
	'slide_edit' => '修改幻灯',
	'virtual_orders' => '消费认证',
	'virtual_orders_view'=>'订单明细查看',
	'shipping_orders_view'=>'商品领取详情',
	'shipping_orders_send'=>'商品领取发货',
	'shipping_orders_recieve' => '批量收货'
);
/* 云购配置结束 */
$rmenusub["mar_zhongchou"] = array(
	'zhongchou'=>'微众筹',
	'config'=>'基本设置',
	'project'=>'项目管理',
	'project_add' => '添加项目',
	'project_edit' => '修改项目',
	'prize' => '设置',
	'users' => '查看',
	'prize_add' => '添加赠品',
	'prize_edit' => '修改赠品',
	'users_view' => '查看预览'	
);

?>