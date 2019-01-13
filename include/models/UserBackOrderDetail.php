<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 总后台配置
 */
use Illuminate\Database\Eloquent\SoftDeletes;
class UserBackOrderDetail extends Illuminate\Database\Eloquent\Model
{
    
    protected  $primaryKey = "itemid";
    protected  $table = "user_back_order_detail";
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
