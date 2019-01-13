<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/20
 * Time: 16:31
 */
//电商ID
$rsUsers = $capsule->table('users')->where(['Users_ID' => $UsersID])->first();
defined('EBusinessID') or define('EBusinessID', $rsUsers['knuserid']);
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('AppKey') or define('AppKey', $rsUsers['knapikey']);
//请求url
defined('ReqURL') or define('ReqURL', 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx');

$shipping_code = [
    "顺丰" => [
        'code' => 'SF',
        'name' => '顺丰快递'
    ],
    "韵达" => [
        'code' => 'YD',
        'name' => '韵达快递'
    ],
    "汇通"=> [
        'code' => 'HTKY',
        'name' => '百世快递'
    ],
    "圆通"=> [
        'code' => 'YTO',
        'name' => '圆通速递'
    ],
    "申通"=> [
        'code' => 'STO',
        'name' => '申通快递'
    ],
    "天天"=> [
        'code' => 'HHTT',
        'name' => '天天快递'
    ],
    "EMS"=> [
        'code' => 'EMS',
        'name' => 'EMS'
    ],
    "中通"=> [
        'code' => 'ZTO',
        'name' => '中通速递'
    ],
    "宅急送"=> [
        'code' => 'ZJS',
        'name' => '宅急送'
    ],
    "德邦"=> [
        'code' => 'DBL',
        'name' => '德邦'
    ],
    "百世"=> [
        'code' => 'BTWL',
        'name' => '百世快运'
    ],
    "众通"=> [
        'code' => 'ZTE	',
        'name' => '众通快递'
    ],
    "越丰"=> [
        'code' => 'YFEX',
        'name' => '百世快运'
    ],
    "原飞航物流"=> [
        'code' => 'YFHEX',
        'name' => '原飞航物流'
    ],
    "亚风快递"=> [
        'code' => 'YFSD',
        'name' => '亚风快递'
    ],
    "运通"=> [
        'code' => 'YTKD',
        'name' => '运通快递'
    ],
    "中铁物流"=> [
        'code' => 'ZTWL',
        'name' => '中铁物流'
    ],
    "中邮物流"=> [
        'code' => 'ZYWL',
        'name' => '中邮物流'
    ],
    "亚马逊物流"=> [
        'code' => 'AMAZON',
        'name' => '亚马逊物流'
    ],
    "速必达物流"=> [
        'code' => 'SUBIDA',
        'name' => '速必达物流'
    ],
    "瑞丰速递"=> [
        'code' => 'RFEX',
        'name' => '瑞丰速递'
    ],
    "快客快递"=> [
        'code' => 'QUICK',
        'name' => '快客快递'
    ],
    "城际快递"=> [
        'code' => 'CJKD',
        'name' => '城际快递'
    ],
    "安捷快递"=> [
        'code' => 'AJ',
        'name' => '安捷快递'
    ],


    "安能物流"=> [
        'code' => 'ANE',
        'name' => '安能物流'
    ],
    "安信达快递"=> [
        'code' => 'AXD',
        'name' => '安信达快递'
    ],
    "北青小红帽"=> [
        'code' => 'BQXHM',
        'name' => '北青小红帽'
    ],
    "百福东方"=> [
        'code' => 'BFDF',
        'name' => '百福东方'
    ],
    "CCES快递"=> [
        'code' => 'CCES',
        'name' => 'CCES快递'
    ],
    "城市100"=> [
        'code' => 'CITY100',
        'name' => '城市100'
    ],
    "COE东方快递"=> [
        'code' => 'COE',
        'name' => 'COE东方快递'
    ],
    "长沙创一"=> [
        'code' => 'CSCY',
        'name' => '长沙创一'
    ],
    "成都善途速运"=> [
        'code' => 'CDSTKY',
        'name' => '成都善途速运'
    ],
    "D速物流"=> [
        'code' => 'DSWL',
        'name' => 'D速物流'
    ],
    "大田物流"=> [
        'code' => 'DTWL',
        'name' => '大田物流'
    ],
    "快捷速递"=> [
        'code' => 'FAST',
        'name' => '快捷速递'
    ],
    "FEDEX联邦"=> [
        'code' => 'FEDEX',
        'name' => 'FEDEX联邦'
    ],
    "'FEDEX联邦(国际件）"=> [
        'code' => 'FEDEX_GJ',
        'name' => 'FEDEX联邦(国际件）'
    ],
    "飞康达"=> [
        'code' => 'FKD',
        'name' => '飞康达'
    ],
    "广东邮政"=> [
        'code' => 'GDEMS',
        'name' => '广东邮政'
    ],
    "共速达"=> [
        'code' => 'GSD',
        'name' => '共速达'
    ],
    "国通快递"=> [
        'code' => 'GTO',
        'name' => '国通快递'
    ],
    "高铁速递"=> [
        'code' => 'GTSD',
        'name' => '高铁速递'
    ],
    "汇丰物流"=> [
        'code' => 'HFWL',
        'name' => '汇丰物流'
    ],
    "恒路物流"=> [
        'code' => 'HLWL',
        'name' => '恒路物流'
    ],
    "天地华宇"=> [
        'code' => 'HOAU',
        'name' => '天地华宇'
    ],
    "华强物流"=> [
        'code' => 'hq568',
        'name' => '华强物流'
    ],
    "华夏龙物流"=> [
        'code' => 'HXLWL',
        'name' => '华夏龙物流'
    ],
    "好来运快递"=> [
        'code' => 'HYLSD',
        'name' => '好来运快递'
    ],
    "京广速递"=> [
        'code' => 'JGSD',
        'name' => '京广速递'
    ],
    "九曳供应链"=> [
        'code' => 'JIUYE',
        'name' => '九曳供应链'
    ],
    "佳吉快运"=> [
        'code' => 'JJKY',
        'name' => '佳吉快运'
    ],
    "嘉里物流"=> [
        'code' => 'JLDT',
        'name' => '嘉里物流'
    ],
    "捷特快递"=> [
        'code' => 'JTKD',
        'name' => '捷特快递'
    ],
    "急先达"=> [
        'code' => 'JXD',
        'name' => '急先达'
    ],
    "晋越快递"=> [
        'code' => 'JYKD',
        'name' => '晋越快递'
    ],
    "加运美"=> [
        'code' => 'JYM',
        'name' => '加运美'
    ],
    "佳怡物流"=> [
        'code' => 'JYWL',
        'name' => '佳怡物流'
    ],
    "跨越物流"=> [
        'code' => 'KYWL',
        'name' => '跨越物流'
    ],
    "龙邦快递"=> [
        'code' => 'LB',
        'name' => '龙邦快递'
    ],
    "联昊通速递"=> [
        'code' => 'LHT',
        'name' => '联昊通速递'
    ],
    "民航快递"=> [
        'code' => 'MHKD',
        'name' => '民航快递'
    ],
    "明亮物流"=> [
        'code' => 'MLWL',
        'name' => '明亮物流'
    ],
    "能达速递"=> [
        'code' => 'NEDA',
        'name' => '能达速递',
    ],
    "平安达腾飞快递"=> [
        'code' => 'PADTF',
        'name' => '平安达腾飞快递'
    ],
    "全晨快递"=> [
        'code' => 'QCKD',
        'name' => '全晨快递'
    ],
    "全峰快递"=> [
        'code' => 'QFKD',
        'name' => '全峰快递'
    ],
    "全日通快递"=> [
        'code' => 'QRT',
        'name' => '全日通快递'
    ],
    "如风达"=> [
        'code' => 'RFD',
        'name' => '如风达'
    ],
    "赛澳递"=> [
        'code' => 'SAD',
        'name' => '赛澳递'
    ],
    "圣安物流"=> [
        'code' => 'SAWL',
        'name' => '圣安物流'
    ],
    "盛邦物流"=> [
        'code' => 'SBWL',
        'name' => '盛邦物流'
    ],
    "上大物流"=> [
        'code' => 'SDWL',
        'name' => '上大物流'
    ],
    "盛丰物流"=> [
        'code' => 'SFWL',
        'name' => '盛丰物流'
    ],
    "盛辉物流"=> [
        'code' => 'SHWL',
        'name' => '盛辉物流'
    ],
    "速通物流"=> [
        'code' => 'ST',
        'name' => '速通物流'
    ],
    "速腾快递"=> [
        'code' => 'STWL',
        'name' => '速腾快递'
    ],
    "速尔快递"=> [
        'code' => 'SURE',
        'name' => '速尔快递'
    ],
    "唐山申通"=> [
        'code' => 'TSSTO',
        'name' => '唐山申通'
    ],
    "全一快递"=> [
        'code' => 'UAPEX',
        'name' => '全一快递'
    ],
    "优速快递"=> [
        'code' => 'UC',
        'name' => '优速快递'
    ],
    "万家物流"=> [
        'code' => 'WJWL',
        'name' => '万家物流'
    ],
    "万象物流"=> [
        'code' => 'WXWL',
        'name' => '万象物流'
    ],
    "新邦物流"=> [
        'code' => 'XBWL',
        'name' => '新邦物流'
    ],
    "信丰快递"=> [
        'code' => 'XFEX',
        'name' => '信丰快递'
    ],
    "希优特"=> [
        'code' => 'XYT',
        'name' => '希优特'
    ],
    "新杰物流"=> [
        'code' => 'XJ',
        'name' => '新杰物流'
    ],
    "源安达快递"=> [
        'code' => 'YADEX',
        'name' => '源安达快递'
    ],
    "远成物流"=> [
        'code' => 'YCWL',
        'name' => '远成物流'
    ],
    "义达国际物流"=> [
        'code' => 'YDH',
        'name' => '义达国际物流'
    ],
    "亿翔快递"=> [
        'code' => 'YXKD',
        'name' => '亿翔快递'
    ],
    "邮政平邮"=> [
        'code' => 'YZPY',
        'name' => '邮政平邮'
    ],
    "增益快递"=> [
        'code' => 'ZENY',
        'name' => '增益快递'
    ],
    "汇强快递"=> [
        'code' => 'ZHQKD',
        'name' => '汇强快递'
    ],
    "中铁快运"=> [
        'code' => 'ZTKY',
        'name' => '中铁快运'
    ],
    "CNPEX中邮快递"=> [
        'code' => 'CNPEX',
        'name' => 'CNPEX中邮快递'
    ],
    "鸿桥供应链"=> [
        'code' => 'HOTSCM',
        'name' => '鸿桥供应链'
    ],
    "海派通物流公司"=> [
        'code' => 'HPTEX',
        'name' => '海派通物流公司'
    ],
    "澳邮专线"=> [
        'code' => 'AYCA',
        'name' => '澳邮专线'
    ],
    "泛捷快递"=> [
        'code' => 'PANEX',
        'name' => '泛捷快递'
    ],
    "PCAExpress"=> [
        'code' => 'PCA',
        'name' => 'PCA Express'
    ],
    "UEQExpress"=> [
        'code' => 'UEQ',
        'name' => 'UEQ Express'
    ]
];