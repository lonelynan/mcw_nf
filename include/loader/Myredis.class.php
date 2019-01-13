<?php
class Myredis extends BaseLoader
{
    private $db;
    private $redisHost = '127.0.0.1';
    private $redisPort = '6379';
    private $redis;


    /**
     * Myredis constructor.
     * @param $db
     * @param $redis_db_no
     */
    public function __construct($db, $redis_db_no = 0)
    {
        $this->db = $db;
        $this->redis = new Redis();
        $this->redis->connect($this->redisHost, $this->redisPort);
        $this->redis->select($redis_db_no);
    }

    /**
     * @param $bizAccount
     * @return 用户经常点击的分类
     */
    public function getMyFavorCate($bizAccount)
    {
        return $this->redis->lRange($bizAccount . '_cate', 0, -1);
    }

    public function setValue($key, $value)
    {
        $this->redis->set($key, $value);
    }

    public function getValue($key)
    {
        return $this->redis->get($key);
    }

    //设置过期时间
    public function expiresAt($key, $time)
    {
        return $this->redis->expire($key, $time);
    }

    /**
     * @param $listName //有序列表名称
     * @param $content //需要写入的内容
     * @return int      //list的新长度
     */
    public function setListValue($listName,$content)
    {
        return $this->redis->rPush($listName, $content);
    }

    /**
     * 写入购物车
     * @param $type //购物车类型,分别为CartList和DirectBuy
     * @param $User_ID //会员ID
     * @param $value //会员的购物车
     * @return int
     */
    public function setCartListValue($type, $User_ID, $value)
    {
        return $this->redis->hSet($type, $User_ID, $value);
    }


    /**
     * 获取会员的购物车
     * @param $type //购物车类型,分别为CartList和DirectBuy
     * @param $User_ID  //会员ID
     * @return string    //对应的购物车数据
     */
    public function getCartListValue($type, $User_ID)
    {
        return $this->redis->hGet($type, $User_ID);
    }

    /**
     * 清除会员的购物车
     * @param $type //购物车类型,分别为CartList和DirectBuy
     * @param $User_ID  //会员ID
     * @return string    //对应的购物车数据
     */
    public function clearCartListValue($type, $User_ID)
    {
        return $this->redis->hDel($type, $User_ID);
    }

    /**
     * @param $session_id //用户商家在支付完成后清理session重新登录
     */
    public function clearSession($session_id)
    {
        $this->redis->del($session_id);
        $this->redis->close();
    }
}