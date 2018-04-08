<?php
/**
 * 自动执行模型
 * 加密方式：$password = hash("sha256", $password);
 */
namespace Home\Model;
use Think\Model;
class AutoModel extends Model {

    public function AutoGoodsReceipt(){
        $autoorder  = $this->getIsAutoGoodsReceipt();
        if(empty($autoorder)){
            return reTurnJSONArray(204,"没有自动收货的订单");
        }
        $timestamp  = time();
        $n  = 0;//统计自动收货的数量
        for($i=0;$i<count($autoorder);$i++){
            if(date('Ymd', $timestamp) == $autoorder[$i]['autoshtime']) {
                D("Order")->ConfirmReceipt2($autoorder[$i]['userid'],$autoorder[$i]['odid']);
                $n++;
            }
        }
        return reTurnJSONArray("200","已经处理了".$n."条自动收货订单");

    }

    /**
     * 获取需要自动收货的订单数据
     * @return bool
     */
    public function getIsAutoGoodsReceipt(){
        $model  = M("order_detail");
        $result = $model->field("odid,goid2 AS goid,autoshtime,userid")->where("ispay=2 AND isfh=2 AND issh=1 AND isth=1")->select();
        if(!empty($result)){
            return $result;
        }
        return false;
    }
}
