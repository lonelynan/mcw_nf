<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');
require_once(CMS_ROOT.'/api/share.php');

$rsConfigZC = $DB->GetRs("zhongchou_config","*","where usersid='".$UsersID."'");
if(!$rsConfig){
	die('未开通微众筹');
}
if(empty($rsConfigZC)){
    $rsConfigZC = [
        'Users_ID' => $UsersID,
        'name' => '微众筹'
    ];
}
$rsConfig = array_merge($rsConfig, $rsConfigZC);

$ActiveID     = isset($_GET['ActiveID']) && $_GET['ActiveID']?$_GET['ActiveID']:0;
$activelist   = [];
$listGoods    = "";
$result       = "";
$indexGoods   = "";
$time         = time();
$ListShowGoodsCount = 0;
$_SESSION[$UsersID."HTTP_REFERER"]=$request_uri;
$sql = "SELECT Users_ID,Active_ID,MaxBizCount,ListShowGoodsCount FROM active WHERE Users_ID='{$UsersID}' AND Type_ID=3 AND starttime<={$time} AND stoptime>{$time} AND Status = 1 ";

if($BizID){
    $rsBiz = $DB->GetRs("biz", "*", "WHERE Users_ID = '" . $UsersID . "' AND Biz_ID=" . $BizID);
    if($ActiveID){
        $sql.= "AND Active_ID={$ActiveID}";
        $result = $DB->query($sql);
        $result = $DB->fetch_assoc();
        if(empty($result) || !$result){
            sendAlert("没有相关活动");
        }
        $ListShowGoodsCount = $result['ListShowGoodsCount'];
    }
    $activelist = $DB->GetRs("biz_active","ListConfig,Biz_ID,Active_ID","WHERE Users_ID='{$UsersID}' AND Active_ID={$ActiveID} AND Biz_ID={$BizID} AND Status=2");
    if(empty($activelist)){
        sendAlert("没有商家参与相关活动");
    }
    $listGoods = trim($activelist['ListConfig'],',');
}else{
    if($ActiveID){
        $sql.= "AND Active_ID={$ActiveID}";
        $result = $DB->query($sql);
        $result = $DB->fetch_assoc();
        if(empty($result) || !$result){
            sendAlert("没有相关活动");
        }
        $ListShowGoodsCount = $result['ListShowGoodsCount'];
    }else{    //没有传参就显示最新一次活动里边的内容
        $sql = "SELECT a.Users_ID,a.Active_ID,a.MaxBizCount,a.ListShowGoodsCount FROM active AS a LEFT JOIN biz_active AS b ON a.Active_ID=b.Active_ID  WHERE a.Users_ID='{$UsersID}' AND a.Type_ID=3 AND a.starttime<={$time} AND a.stoptime>{$time} AND a.Status = 1 AND b.Status=2 ";
        $result = $DB->query($sql);
        $result = $DB->fetch_assoc();
        if(empty($result) || !$result){
            sendAlert("没有相关活动");
        }
        $ActiveID = $result['Active_ID'];
        $ListShowGoodsCount = $result['ListShowGoodsCount'];
    }
    $result = $DB->Get("biz_active","ListConfig,Biz_ID,Active_ID","WHERE Users_ID='{$UsersID}' AND Active_ID={$ActiveID} AND Status=2 LIMIT 0,{$result['MaxBizCount']}");
    $activelist = $DB->toArray($result);
    if(empty($activelist)){
        sendAlert("没有商家参与相关活动");
    }
    foreach ($activelist as $k => $v)
    {
        $listGoods .= $v['ListConfig'].',';
    }
    $listGoods = trim($listGoods,',');
}
$_SESSION[$UsersID.'_ZhongchouCurrentActive'] = $ActiveID;
$totalInfo = $DB->GetRs("zhongchou_project","count(*) as total","WHERE usersid='{$UsersID}' AND itemid IN ({$listGoods}) AND status = 1 AND fromtime<{$time} AND totime>{$time}");
$pagesize = 3;
if($totalInfo['total']>$ListShowGoodsCount){
    $totalInfo['total'] = $ListShowGoodsCount;
}

$totalPage = round($totalInfo['total']/$pagesize);

if(!isset($_SESSION[$UsersID."_zhongchou_CurLists"]))
{
    $_SESSION[$UsersID."_zhongchou_CurLists"] = 0;
}
if(IS_AJAX){
    $page = isset($_POST['page']) && $_POST['page']?$_POST['page']:1;
    $sort = isset($_POST['sort']) && $_POST['sort']?$_POST['sort']:0;
    $method = isset($_POST['sortmethod']) && $_POST['sortmethod']?$_POST['sortmethod']:'asc';
    $offset = ($page-1)*$pagesize;
    $order = ["itemid {$method}","fromtime {$method}","totime {$method}","amount {$method}","Biz_ID {$method}"];
    $sql = "SELECT * FROM (SELECT * FROM `zhongchou_project` WHERE usersid='{$UsersID}' AND itemid IN ({$listGoods}) AND status = 1 LIMIT {$ListShowGoodsCount}) as t ".($sort?"ORDER BY {$order[$sort]}":"ORDER BY field(itemid,{$listGoods})")." LIMIT {$offset},{$pagesize}";
    $result = $DB->query($sql);
    $list = [];
    if($result){
        $list = $DB->toArray($result);
        if(!empty($list))
        {
            foreach($list as $k => $v)
            {
                $r = $DB->GetRs("user_order","count(*) as num,sum(Order_TotalPrice) as curamount","where Order_Type='zhongchou_".$v['itemid']."' and Order_Status=2 and Users_ID='".$v['usersid']."'");
                if(!empty($r)){
                    $list[$k]['num'] = $r['num']?$r['num']:0;
                    $list[$k]['curamount'] = $r['curamount']?$r['curamount']:0;
                }else{
                    $list[$k]['num'] = 0;
                    $list[$k]['curamount'] = 0;
                }
                $list[$k]['fromtime'] = date("Y-m-d",$v['fromtime']);
                $list[$k]['totime'] = date("Y-m-d",$v['totime']);
                $list[$k]['title'] = sub_str($v['title'], 16, false);
                if($v['fromtime']>$time){
                    $list[$k]['buttonTitle'] = "未开始";
                }else if ($v['totime']<$time){
                    $list[$k]['buttonTitle'] = "已过期";
                }else{
                    $list[$k]['buttonTitle'] = "众筹中";
                }
            }
            die(json_encode([ 'status'=>1 ,'data' => $list,'method'=>$method ], JSON_UNESCAPED_UNICODE));
        }
    }
    die(json_encode([ 'status'=>0 ,'data' => $list ], JSON_UNESCAPED_UNICODE));
}
require_once('skin/index.php');
?>
