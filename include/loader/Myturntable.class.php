<?php
class Myturntable extends BaseLoader
{
    private $db;
    /**
     * 构造方法
     * @param $DB
     * @return mixed
     */
    public function __construct($DB)
    {
        $this->db = $DB;
    }



    /**
     * 发送验证码接口,领奖之前需要先绑定手机号
     * @params Users_ID string 对应管理员的唯一标识
     * @params User_ID int 绑定手机号的会员ID
     * @params Mobile string 手机号
     * @return array
     */
    public function sendYzm($Users_ID, $User_ID, $Mobile)
    {
        $user = $this->db->GetRs('user', '*', "WHERE Users_ID='".$Users_ID."' AND User_Mobile='" . $Mobile . "' AND User_ID!=".$User_ID);
        if ($user) {
            //手机号已被占用
            $data = [
                'errorCode' => 500,
                'msg' => '手机号已绑定用户，请更换一个手机号'
            ];

            return $data;
        }
        unset($user);

        //发送验证码
        $code = sprintf("%'.04d", rand(0, 9999));
        $myredis = new Myredis($this->db);
        $myredis->setValue('bind' . $Mobile, $code, 120);

        require_once($_SERVER["DOCUMENT_ROOT"].'/Framework/Ext/sms.func.php');
        $message = "手机验证码为：" . $code . "。120秒内有效，过期请重新获取。" ;
        $success = send_sms($Mobile, $message, $Users_ID);
        if ($success) {
            $Data = array(
                "errorCode"=> 0,
                "msg"=>"短信发送成功"
            );
        } else {
            $Data = array(
                "errorCode"=> 2,
                "msg"=>"短信发送失败",
            );
        }

        return $Data;
    }


    /**
     * 绑定手机号
     * @params Users_ID string 对应管理员的唯一标识
     * @params User_ID int 需要绑定手机的UserID
     * @params yzm int 验证码,四位数字
     * @params Mobile number 手机号
     * @return array
     */
    public function bindMobile($Users_ID, $User_ID, $yzm, $Mobile)
    {

        //手机号非法
        require_once($_SERVER["DOCUMENT_ROOT"] . '/include/support/sysurl_helpers.php');
        if (!is_mobile($Mobile)) {
            $data = [
                'errorCode' => 403,
                'msg' => '手机号格式非法',
            ];
            return $data;
        }

        //验证码
        $smsMobileKey = 'bind' . $Mobile;
        $my_redis = new Myredis($this->db);
        $code = $my_redis->getValue($smsMobileKey);
        if (empty($code) || empty($yzm)) {
            $Data = array(
                "errorCode" => 500,
                "msg" => "手机验证码错误",
            );
            return $Data;
        } else {
            if ($yzm != $code) {
                $Data = array(
                    "errorCode" => 500,
                    "msg" => "手机验证码错误",
                );
               return $Data;
            }
        }
        //检查手机号是否已注册
        $user = $this->db->GetRs('user', '*', "WHERE Users_ID='".$Users_ID."' AND User_Mobile='" . $Mobile . "' AND User_ID!=".$User_ID);
        if ($user) {
            //用户已存在
            $data = [
                'errorCode' => 403,
                'msg' => '手机号已绑定用户，请更换一个手机号'
            ];
        } else {
            //保存手机号
            $data = [
                'User_Mobile' => $Mobile,
                'User_Profile' => 1,	//手机号已通过认证
            ];
            $this->db->Set('user', $data, "WHERE Users_ID='" . $Users_ID . "' AND User_ID=" . intval($User_ID));

            $data = [
                'errorCode' => 0,
                'msg' => '绑定手机成功'
            ];
        }
        return $data;
    }


    /**
     * 初始化大转盘数据,如果没有今日的抽奖记录,则写入一条今日的记录,返回可抽奖次数
     * @params Users_ID string 对应管理员的唯一标识
     * @params User_ID int 开始大转盘的会员ID
     * @return array [prize 初始化的大转盘的奖项][chance 剩余抽奖机会]
     */
    public function initTurn($Users_ID, $User_ID)
    {
        $currentTime = time();
        $rsTurntable = $this->db->GetRs('turntable', '*', "where Users_ID = '". $Users_ID ."' and status = 1 and starttime <= ". $currentTime ." and endtime >= ". $currentTime ." order by created_at desc");
        $turntable_ID = $rsTurntable['id'];
        $currentTime = time();
        //+每天额外抽奖次数并把当前总次数入库
        $fromtime = strtotime(date("Y-m-d", $currentTime) . " 00:00:00");
        $totime = strtotime(date("Y-m-d", $currentTime) . " 23:59:59");

        //今天是否有打开过大转盘游戏,如有,会自动写入一条今天的记录
        $Today = $this->db->GetRs('action_num_record', 'AllDayLotteryTimes_have, hasLotteryTimesByToday', "where Users_ID = '". $Users_ID ."' and User_ID = " . $User_ID . " and S_Module = 'turntable' and Act_ID = " . $turntable_ID . " and S_CreateTime >= " . $fromtime . " and S_CreateTime <= " . $totime);


        //记录总抽奖次数
        if (!$Today) {
            //昨天的抽奖记录
            $last_time = $this->db->GetRs('action_num_record', 'AllDayLotteryTimes_have, hasLotteryTimesByToday', "where Users_ID = '". $Users_ID ."' and User_ID = " . $User_ID . " and S_Module = 'turntable' and Act_ID = " . $turntable_ID . " and S_CreateTime < " . $fromtime . " order by S_CreateTime desc");

            $table_init = $this->db->GetRs('action_num_record', 'AllDayLotteryTimes_have, hasLotteryTimesByToday', "where Users_ID = '". $Users_ID ."' and User_ID = " . $User_ID . " and S_Module = 'turntable' and Act_ID = " . $turntable_ID);

            if(!$last_time){
                $last_time = [
                    'hasLotteryTimesByToday' => 0,
                    'AllDayLotteryTimes_have' => 0
                ];
            }
            if (!$table_init) {
                $allhave = $rsTurntable["alltimes"];
                $dayhave = $rsTurntable['daytimes'];
            } else {
                $allhave = $last_time["AllDayLotteryTimes_have"];
                $dayhave = $last_time["hasLotteryTimesByToday"] + $rsTurntable['daytimes'];
            }
            $Data = array(
                "Users_ID" => $Users_ID,
                "S_Module" => "turntable",
                "S_CreateTime" => $currentTime ,
                "User_ID" => $User_ID,
                "AllDayLotteryTimes_have" => $allhave,
                "hasLotteryTimesByToday" => $dayhave,
                "Act_ID" => $turntable_ID ,
            );
            $this->db->Add('action_num_record', $Data);
            $Today = $this->db->GetRs('action_num_record', 'AllDayLotteryTimes_have, hasLotteryTimesByToday', "where Users_ID = '". $Users_ID ."' and User_ID = " . $User_ID . " and S_Module = 'turntable' and Act_ID = " . $turntable_ID . " and S_CreateTime >= " . $fromtime . " and S_CreateTime <= " . $totime);
        }
        $prize_array = json_decode($rsTurntable['prizejson'], true);
        $init_prize = [];
        foreach ($prize_array as $k => $v) {
            $init_prize[$k] = ['index' => $k, 'name' => $v['name']];
        }
        $init_prize[count($prize_array)] = ['index' => count($prize_array), 'name' => '再来一次'];
        $chance = $Today["AllDayLotteryTimes_have"] < $Today["hasLotteryTimesByToday"] ? $Today["AllDayLotteryTimes_have"] : $Today["hasLotteryTimesByToday"];

        return ['errorCode' => 0, 'msg' => '初始化成功', 'data' => ['prize' => $init_prize, 'chance' => $chance, 'AllDayLotteryTimes_have' => $Today["AllDayLotteryTimes_have"], 'hasLotteryTimesByToday' => $Today["hasLotteryTimesByToday"], 'turntable' => $rsTurntable]];
    }

    /**
     * 开始转动转盘抽奖
     * @params Users_ID string 对应管理员的唯一标识
     * @params User_ID int 抽奖的会员ID
     * @return mixed
     */
    public function beginTurn($Users_ID, $User_ID)
    {
        $currentTime = time();
        //+每天额外抽奖次数并把当前总次数入库
        $fromtime = strtotime(date("Y-m-d", $currentTime) . " 00:00:00");
        $totime = strtotime(date("Y-m-d", $currentTime) . " 23:59:59");
        $rsTurntable = $this->db->GetRs('turntable', '*', "where Users_ID = '". $Users_ID ."' and status = 1 and starttime <= ". $currentTime ." and endtime >= ". $currentTime ." order by created_at desc");
        $turntable_ID = $rsTurntable['id'];
        //获取当前用户总抽奖数
        $use_num = $this->db->getCount('turntable_sn', "where Users_ID = '". $Users_ID ."' and Turntable_ID = " . $turntable_ID . " and User_ID = " . $User_ID . " and SN_Code <> 0 and SN_Status > 0");

        $Today = $this->db->GetRs('action_num_record', 'AllDayLotteryTimes_have, hasLotteryTimesByToday', "where Users_ID = '". $Users_ID ."' and User_ID = " . $User_ID . " and S_Module = 'turntable' and Act_ID = " . $turntable_ID . " and S_CreateTime >= " . $fromtime . " and S_CreateTime <= " . $totime);

        $prize_array = json_decode($rsTurntable['prizejson'], true);
        //产品剩余
        $PrizeCount = [];
        foreach($prize_array as $k => $v){
            $PrizeCount[$k] = $v['count'];
        }
        $p_last_num = array_sum($PrizeCount);//剩余奖品数量

        //如果没有奖品的情况下
        if ($p_last_num == 0) {
            return ['errorCode' => 404, 'msg' => '奖品已被抽完', 'url' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']];
        }
        if (isset($rsTurntable["alltimes"]) && $use_num > $rsTurntable["alltimes"]) {
            return ['errorCode' => 403, 'msg' => '您的抽奖机会已用完', 'url' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']];
        } else {
            if (isset($rsTurntable["daytimes"]) && $Today["hasLotteryTimesByToday"] <=0) {
                return ['errorCode' => 403, 'msg' => '您的抽奖机会已用完,请明天再来吧', 'url' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']];
            } else {
                begin_trans();
                //判断抽奖是否完毕
                if (!$rsTurntable) {
                    return ['errorCode' => 404, 'msg' => '没有开始的活动', 'url' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']];
                }

                //初始中奖概率
                $odd = 0;
                $arr = [];
                foreach ($prize_array as $prizek => $prizev) {
                    $prize_arr[$prizek] = [
                        'index' => $prizek,
                        'name' =>  $prizev['name'],
                        'odds' => $prizev['count'] > 0 ? $prizev['odds'] : 0,
                        'count' => $prizev['count']
                    ];
                    $odd += $prize_arr[$prizek]['odds'];
                }
                //谢谢惠顾的几率
                $prize_arr[count($prize_array)] = [
                    'index' => count($prize_array),
                    'name' => '再来一次',
                    'odds' => 100 - $odd,
                    'count' => 1
                ];
                foreach ($prize_arr as $key => $val) {
                    $arr[$val['index']] = $val['odds'];
                }
                $rid = $this->_getRand($arr); //根据概率获取奖项id
                $SN_Code = mt_rand(1000, 9999) . mt_rand(1000, 9999);
                //更新当前抽奖次数
                $num_date = [
                    "AllDayLotteryTimes_have" => (int)$Today["AllDayLotteryTimes_have"] - 1,
                    "hasLotteryTimesByToday" => $Today["hasLotteryTimesByToday"] - 1
                ];
                $flag = $this->db->Set('action_num_record', $num_date, "where Users_ID = '". $Users_ID ."' and User_ID = " . $User_ID . " and S_Module = 'turntable' and Act_ID = " . $turntable_ID);
                if($flag === false){
                    back_trans();
                }

                $turnData = [
                    "Turntable_ID" => (isset($rsTurntable['id']) ? $rsTurntable['id'] : 0) ,
                    "Turntable_PrizeID" => $rid,
                    "Turntable_Prize" => $prize_arr[$rid]['name'],
                    "SN_Code" => $rid < count($prize_array) ? $SN_Code : 0,
                    "Users_ID" => $Users_ID,
                    "User_ID" => $User_ID,
                    "Open_ID" => isset($_SESSION[$Users_ID."OpenID"])?$_SESSION[$Users_ID."OpenID"]:'',
                    "SN_CreateTime" => time()
                ];
                $flag = $this->db->Add('turntable_sn', $turnData);
                if(! $flag){
                    back_trans();
                }
                if ($rid < count($prize_array) && $flag) {
                    return ['errorCode' => 0, 'msg' => '恭喜您，抽中' . $prize_arr[$rid]['name'] . '！', 'data' => ['prize' => $rid, 'total' => count($prize_array) + 1]];
                } else {
                    return ['errorCode' => 0, 'msg' => '距离中奖就差0.01毫米,再来一次!', 'data' => ['prize' => $rid, 'total' => count($prize_array) + 1]];
                }
            }
        }
    }


    /**
     * 输入兑奖密码进行兑奖
     * @params Users_ID string 对应管理员的唯一标识
     * @params User_ID int 兑奖的会员ID
     * @params sn_id int 兑奖码记录的ID
     * @params expasswd string 兑奖的商家兑奖密码
     * @return boolean
     */
    public function exPrize($Users_ID, $User_ID, $sn_id, $expasswd)
    {
        $currentTime = time();
        $rsTurntable = $this->db->GetRs('turntable', '*', "where Users_ID = '". $Users_ID ."' and status = 1 and starttime <= ". $currentTime ." and endtime >= ". $currentTime ." order by created_at desc");
        if ($expasswd == $rsTurntable['expasswd']) {
            $Data = array(
                "SN_UsedTimes" => $currentTime,
                "SN_Status" => 2
            );
            $flag = $this->db->Set('turntable_sn', $Data, "where Users_ID = '". $Users_ID ."' and User_ID = " . $User_ID . " and SN_ID = " . $sn_id);
            if (false !== $flag) {
                return ['errorCode' => 0, 'msg' => '兑奖成功'];
            } else {
                return ['errorCode' => 2, 'msg' => '服务器开小差了,等会再来兑奖吧.'];
            }
        } else {
            return ['errorCode' => 403, 'msg' => '兑奖密码错误'];
        }
    }


    /**
     * 查询兑奖记录
     * @params Users_ID string 对应管理员的唯一标识
     * @params User_ID int 会员ID
     * @params Act_ID int 大转盘的ID
     * @return array
     */
    public function getExRecord($Users_ID, $User_ID, $Act_ID)
    {
        $record = $this->db->GetAssoc('turntable_sn', '*', "where Users_ID = '" . $Users_ID . "' and User_ID = " . $User_ID . " and Turntable_ID = " . $Act_ID . " and SN_Code <> 0 order by SN_ID desc");
        if (!empty($record)) {
            return ['errorCode' => 0, 'msg' => '查询成功', 'data' => $record];
        }
        return ['errorCode' => 404, 'msg' => '暂无记录'];
    }


    /**
     * 经典抽奖概率算法
     */
    private function _getRand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum-= $proCur;
            }
        }
        unset($proArr);
        return $result;
    }
}