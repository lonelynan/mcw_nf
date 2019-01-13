<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 总后台配置
 */
use Illuminate\Database\Eloquent\SoftDeletes;
class UserBackOrder extends Illuminate\Database\Eloquent\Model
{
    
    protected  $primaryKey = "Back_ID";
    protected  $table = "user_back_order";
    public $timestamps = false;
    

    // 多where
    public function scopeMultiwhere($query, $arr)
    {
        if (!is_array($arr)) {
            return $query;
        }
    
        foreach ($arr as $key => $value) {
            $query = $query->where($key, $value);
        }
        return $query;
    }
	
	/**
	 * 获取指定订单中退款单的数量,如果有退款单则不允许用户从“已付款”修改为'已发货'，也不允许用户从'已发货'修改为"确认收货完成"状态
	 * back_status 已经不允许进行专家拒绝操作，所以状态值 5、6已弃用
	 * @param int $Order_ID
	 * @return int
	 */
	static public function getUncompleteBackOrderCount($Order_ID)
	{
		return self::where('Order_ID', $Order_ID)->where('Back_Status', '<', '4')->count();
	}
		
    
    public function order()
    {
        return $this->belongsTo("Order", "Order_ID", "User_ID");
    }
    
    /**
     * 关闭日期转换功能
     *
     */
    public function getDates() {
        return array();
    }
    
    public function modify($data){
        return $this->add($data);
    }
    
    public function add($data){
        if(is_array($data)){
            foreach ($data as $k => $v){
                if(!empty($v) && $v){
                    $this->$k = $v;
                }
            }
            return $this->save();
        }
        return false;
    }
}
