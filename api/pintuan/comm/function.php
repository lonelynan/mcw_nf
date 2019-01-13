<?php
use Illuminate\Database\Capsule\Manager as DB;

//开团
function addTeam($orderid,$Users_ID)
{
    $orderInfo = DB::table("user_order")->where(["Order_ID" => $orderid, "Users_ID" => $Users_ID])->first();
    if(empty($orderInfo)) return false;
    $CartList = json_decode($orderInfo['Order_CartList'],true);
    $userid = $orderInfo["User_ID"];
    $data = [];

    $teamid = 0;
    $productid = 0;
    foreach ($CartList as $pro_id => $value) {
        foreach ($value as $attr_id => $attr_value) {
            $teamid = !empty($attr_value['teamid']) ? $attr_value['teamid'] : 0;
            $productid = $pro_id;
        }
    }

    $rsProduct = DB::table("shop_products")->where(["Users_ID" => $Users_ID, "Products_ID" => $productid])->first();
    if(!$rsProduct) return false;
    if(!$teamid) {
        $data['productid'] = $productid;
        $data['users_id'] = $Users_ID;
        $data['userid'] = $userid;
        $data['starttime'] = $rsProduct['pintuan_start_time'];
        $data['stoptime'] = $rsProduct['pintuan_end_time'];
        $data['teamstatus'] = 0;
        $data['teamnum'] = 0;
        $data['addtime'] = time();
        $teamid = DB::table("pintuan_team")->insertGetId($data);
        if($teamid){
            $ef = enterTeam($teamid,$orderid,$userid);
        }else{
            return false;
        }
    }else{
        //参团
        $ef = enterTeam($teamid,$orderid,$userid);
    }

    $return = false;
    //参团成功或者已参团的情况
    if($ef){
        $flag = DB::table("pintuan_team")->where(["id" => $teamid])->first();
        if(! $flag){
            return false;
        }
        if($flag['teamnum'] >= $rsProduct['pintuan_people']){
            DB::table("pintuan_team")->where(["id" => $teamid])->update(['teamstatus' => 1]);
            $return =-1;   //拼团成功
        }else{
            $return = true;
        }
    }else{
        $return = false;
    }
    return $return;
}
//  参团
function enterTeam($pid,$order_id,$userid)
{
    $flag = DB::table("pintuan_teamdetail")->where(["userid" => $userid, "teamid" => $pid])->first();
    if(!$flag){
        $data['teamid']=$pid;
        $data['userid']=$userid;
        $data['order_id']=$order_id;
        $data['addtime']=time();
        $flag1 = DB::table("pintuan_teamdetail")->insertGetId($data);
        if($flag1){
            $ptFlag = DB::table("pintuan_team")->where(["id" => $pid])->increment("teamnum",1);
            return $ptFlag ? true: false;
        }else{
            return false;
        }
    }
    return true;
}