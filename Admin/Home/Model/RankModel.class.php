<?php
/**
排名
 * 加密方式：$password = hash("sha256", $password);
 */
namespace Home\Model;
use Think\Model;
class RankModel extends Model {
    /**
     * 商品列表
     */
    public function getGoodsInfo($PageIndex,$gcid,$date1,$date2,$search,$PageSize,$flag){
        $WhereStr =" and goods.state=0";
        if(!empty($flag)){
            $WhereStr .=" AND goods_rank.type=1";
            $wheres     = "AND goods_rank.type=1";
        }else{
            $WhereStr .=" AND goods_rank.type=0";
            $wheres     = "AND goods_rank.type=0";
        }
        if(!empty($gcid)){$WhereStr .=" and goods_classify.gcid=$gcid  ";}
        if(!empty($date1) && !empty($date2)){
            $WhereStr .=createTime($date1,$date2,"goods.rktime");
        }
        if(!empty($search)){$WhereStr .=" and (INSTR(goods.gname ,'".$search."') OR INSTR(business_info.`name`,'".$search."')) ";}
        $PageSize = $PageSize?$PageSize:15;
        $PageIndex = $PageIndex?$PageIndex:1;
        $show = "5";
        $join = "   Inner join goods on goods_rank.goodsid=goods.gid	LEFT JOIN business_info ON goods.bid=business_info.b_id 	LEFT JOIN goods_classify ON goods.gclid = goods_classify.gcid ";
        $joinid = "gid";
        $Coll="CASE WHEN UNIX_TIMESTAMP(goods_rank.endtime) < UNIX_TIMESTAMP('".date("Y-m-d")."') Then 1 ELSE 2 END as rankstatus,rankid,ranksort,goods.gid,goods.gname AS gn,goods_classify.gcname AS gc,goods.sales AS gss,business_info.`name` AS bn,goods_rank.ranktime,goods_rank.ranksort,goods_rank.endtime";
        $sql = SqlStr2($TableName="goods_rank",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey="goods_rank.ranksort",$OrderType="desc",$join,$joinid,"",$wheres);
        //echo $sql;die;
        $model = M("goods_rank");
        $count = $model->join($join)->where("1=1".$WhereStr)->count();
        //echo $model->getLastSql();die;
        $pagenum = ceil($count/$PageSize);
        $data['list']  = $model->query($sql);
        $url = "admin.php?c=Rank&a=HotGoodsRank";
        if(!empty($gcid)){$url .="&gcid=".$gcid;}
        if(!empty($date1)){$url .="&date1=".$date1;}
        if(!empty($date2)){$url .="&date2=".$date2;}
        if(!empty($search)){$url.="&search=".$search;}
        $url.="&goodsorclass=$flag";
        $url .="&page=";
        $data['page'] = PageText($url,$PageIndex,$pagenum,$show);
        return $data;
    }
    /**
     * 商品列表
     */
    public function goodsList($PageIndex,$gcid,$date1,$date2,$sales,$hot,$pte,$search,$PageSize,$adminid,$adminpinyin,$bid,$flag){
        $WhereStr =" and goods.state=0";
        if(!empty($flag)){
            $WhereStr   .=" and classrank=0";
        }else{
            $WhereStr   .=" and hotrank=0";
        }
        if(!empty($gcid)){$WhereStr .=" and goods_classify.gcid=$gcid  ";}
        if(!empty($date1) && !empty($date2)){
            $WhereStr .=createTime($date1,$date2,"goods.rktime");
        }
        $OrderType  = "";
        $OrderKey   ="";
        if(!empty($sales)){
            if($sales==1){
                $OrderType .="goods.sales asc";
            }else{
                $OrderType .="goods.sales desc";
            }
        }else{
            $OrderKey 	.= "goods.gid desc";
        }
        if(!empty($search)){$WhereStr .=" and (INSTR(goods.gname ,'".$search."') OR INSTR(business_info.`name`,'".$search."')) ";}
        $PageSize = $PageSize?$PageSize:15;
        $PageIndex = $PageIndex?$PageIndex:1;
        $show = "5";
        $join = "	LEFT JOIN business_info ON goods.bid=business_info.b_id 	LEFT JOIN goods_classify ON goods.gclid = goods_classify.gcid ";
        $joinid = "gid";
        $Coll="goods.gid,goods.gname AS gn,goods_classify.gcname AS gc,business_info.`name` AS bn";
        $sql = SqlStr2($TableName="goods",$Coll,$WhereStr,$WhereStr,$PageIndex,$PageSize,$OrderKey,$OrderType,$join,$joinid);
        $model = M("goods");
        $count = $model->join($join)->where("1=1".$WhereStr)->count();
        //echo $model->getLastSql();die;
        $pagenum = ceil($count/$PageSize);
        $data['list']  = $model->query($sql);
        $url = "admin.php?c=Rank&a=RankGoodsList";
        if(!empty($gcid)){$url .="&gcid=".$gcid;}
        if(!empty($date1)){$url .="&date1=".$date1;}
        if(!empty($date2)){$url .="&date2=".$date2;}
        if(!empty($sales)){$url .="&sales=".$sales;}
        if(!empty($search)){$url.="&search=".$search;}
        $url.="&goodsorclass=$flag";
        $url .="&page=";
        $data['page'] = PageText($url,$PageIndex,$pagenum,$show);
        return $data;
    }

    /**
     * @param $gid
     * @param $type
     * @param $endtime
     * @return array
     * 设置排名商品
     */
    public function setRankData($gid,$type,$endtime,$flag,$rankid,$sort=""){
        if(empty($gid) || empty($endtime)){
            return reTurnJSONArray("204","基本信息有误");
        }
        $model  = M("goods_rank");
        $data['goodsid']    = $gid;
        $data['type']       = $type;
        $data['ranktime']   = date("Y-m-d");
        $data['endtime']    = $endtime;
        if(!empty($flag)){
            $save['endtime'] = $endtime;
            if(!empty($sort)){
                $save['ranksort']   = $sort;
            }
            $result = $model->where("rankid=$rankid")->save($save);
        }else{
            $data['ranksort']   = $sort;
            $sort   = $this->getRankSort($type);
            $result = $model->add($data);
        }
        if(!is_bool($result)){
            $this->SetGoodsRank($gid,$type,$sort);//设置商品的排序字段值
            return reTurnJSONArray("200","排名成功");
        }else{
            return reTurnJSONArray("204","服务器连接超时");
        }
    }

    /**
     * @param $type
     * @return int
     * 获取排名最大序号
     */
    public function getRankSort($type){
        $model  = M("goods_rank");
        $result = $model->field("MAX(ranksort) as sort")->where("type=$type")->find();
        if(!empty($result)){
            return math_add($result['sort'],1);
        }else{
            return 1;
        }
    }

    /**
     * @param $gid
     * @param $type
     * @param $sort
     * @return bool
     * 设置商品表中的排序字段
     */
    public function SetGoodsRank($gid,$type,$sort){
        $model  = M("goods");
        if($type==0){
            $save['hotrank']=$sort;
        }else{
            $save['classrank']=$sort;
        }
        $result = $model->where("gid=$gid")->save($save);
        if(is_bool($result)){
            return false;
        }
        return true;
    }
    /**
     * [热门商家]
     * @param [type] $page  [description]
     * @param [type] $where [description]
     * @param [type] $where [description]
     * @param [type] $ext   [description]
     * @param [type] $flag  [description]
     * @param [type] $type  [description]
     */
    public function GetRankSellerList($PageIndex,$WhereStr="",$WhereStr2="",$ext,$flag,$type){
        $PageSize = "20";
        $PageIndex = $PageIndex?$PageIndex:1;
        $show = "5";
        $joinid = "h_seller_detail.seller_id";
        $join = "join `user` on h_seller_detail.user_id = `user`.user_id inner join shop_rank on h_seller_detail.seller_id=shop_rank.shopid left join business_info on h_seller_detail.seller_id = business_info.seller_id";
        $Coll="business_info.`name`,CASE WHEN UNIX_TIMESTAMP(shop_rank.endtime)<UNIX_TIMESTAMP('".date("Y-m-d")."') Then 1 ELSE 2 END as rankstatus,shop_rank.*,h_seller_detail.*,`user`.user_name,`user`.user_id,user_phone";
        $sql = SqlStr2($TableName="h_seller_detail",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="shop_rank.ranksort",$OrderType="desc",$join,$joinid,"","");
        $model = M("h_seller_detail");
        $count = $model
            ->join("inner join user on h_seller_detail.user_id = user.user_id")
            ->where("1=1".$WhereStr)->count();
        $pagenum = ceil($count/$PageSize);
        $data['list']  = $model->query($sql);
        $data['count'] = $count;
        $url = "admin.php?c=Rank&a=HotShop&status=1";
        if(!empty($id)){
            $url .="&id=".$id;
        }
        if(!empty($ext)){
            $url .="&ext=".$ext;
        }
        if(!empty($flag)){
            $url .="&flag=".$flag;
        }
        if(!empty($type)){
            $url .="&type=".$type;
        }
        $url .="&page=";
        $data['page'] = PageText($url,$PageIndex,$pagenum,$show);
        return $data;
    }
    /**
     * [选择商家列表]
     * @param [type] $page  [description]
     * @param [type] $where [description]
     * @param [type] $where [description]
     * @param [type] $ext   [description]
     * @param [type] $flag  [description]
     * @param [type] $type  [description]
     */
    public function GetApplySellerList($PageIndex,$WhereStr="",$WhereStr2="",$ext,$flag,$type){
        $WhereStr   .=" and h_seller_detail.sranksort=0";
        $PageSize = "20";
        $PageIndex = $PageIndex?$PageIndex:1;
        $show = "5";
        $joinid = "h_seller_detail.seller_id";
        $join = "join `user` on h_seller_detail.user_id = `user`.user_id left join business_info on h_seller_detail.seller_id = business_info.seller_id";
        $Coll="h_seller_detail.*,`user`.user_name,`user`.user_id,user_phone,business_info.`name`";
        $sql = SqlStr2($TableName="h_seller_detail",$Coll,$WhereStr,$WhereStr2,$PageIndex,$PageSize,$OrderKey="h_seller_detail.create_time",$OrderType="desc",$join,$joinid,"","and h_seller_detail.sranksort=0");
        $model = M("h_seller_detail");
        $count = $model
            ->join("inner join user on h_seller_detail.user_id = user.user_id")
            ->where("1=1".$WhereStr)->count();
        $pagenum = ceil($count/$PageSize);
        $data['list']  = $model->query($sql);
        $data['count'] = $count;
        $url = "admin.php?c=Rank&a=RankShop&status=1";
        if(!empty($id)){
            $url .="&id=".$id;
        }
        if(!empty($ext)){
            $url .="&ext=".$ext;
        }
        if(!empty($flag)){
            $url .="&flag=".$flag;
        }
        if(!empty($type)){
            $url .="&type=".$type;
        }
        $url .="&page=";
        $data['page'] = PageText($url,$PageIndex,$pagenum,$show);
        return $data;
    }

    /**
     * @param $sellerid
     * @param $endtime
     * @param string $sort
     * @return arrays
     * 设置热门店铺
     */
    public function setShopRankData($sellerid,$endtime,$sort="",$rankid="",$flag=""){
        $model  = M("shop_rank");
        $data['shopid']    = $sellerid;
        $data['ranktime']   = date("Y-m-d");
        $data['endtime']    = $endtime;
        $data['ranksort']   = $sort;
        if(!empty($flag)){
            $save['ranksort']   = $sort;
            $this->SetShopRank($sellerid,$sort);
            $result = $model->where("rankid=$rankid")->save($save);
        }else{
            if(empty($sort)){
                $sort   = $this->getShopRankSort();
            }
            $result = $model->add($data);
        }
        if(!empty($result)){
            $this->SetShopRank($sellerid,$sort);//设置店铺的排序字段值
            return reTurnJSONArray("200","排名成功");
        }else{
            return reTurnJSONArray("204","服务器连接超时");
        }
    }
    /**
     * @param $type
     * @return int
     * 获取店铺排名最大序号
     */
    public function getShopRankSort(){
        $model  = M("h_seller_detail");
        $result = $model->field("MAX(sranksort) as sort")->find();
        if(!empty($result)){
            return math_add($result['sort'],1);
        }else{
            return 1;
        }
    }

    /**
     * @param $gid
     * @param $type
     * @param $sort
     * @return bool
     * 设置店铺表中的排序字段
     */
    public function SetShopRank($sellerid,$sort){
        $model  = M("h_seller_detail");
        $save['sranksort']=$sort;
        $result = $model->where("seller_id=$sellerid")->save($save);
        if(is_bool($result)){
            return false;
        }
        return true;
    }
    /******************关闭和续时********************/
    /**
     * @param $goodsid
     * @param $type
     * @param $rankid
     * @return array
     * 关闭商品热门
     */
    public function CloseGoodsRank($goodsid,$type,$rankid){
        $model=M("goods_rank");
        $result = $model->where("rankid =$rankid")->delete();
        if(!is_bool($result)){
            $this->SetGoodsRank($goodsid,$type,0);
            return reTurnJSONArray("200","关闭成功");
        }else{
            reTurnJSONArray("204","服务器连接超时");
        }
    }

    /**
     * @param $rankid
     * @param $sellerid
     * @return array
     * 关闭店铺排名
     */
    public function CloseShopRank($rankid,$sellerid){
        $model  = M("shop_reank");
        $result = $model->where("rankid=$rankid")->delete();
        if(!is_bool($result)){
            $this->SetShopRank($sellerid,0);
            return reTurnJSONArray("200","关闭成功");
        }else{
            reTurnJSONArray("204","服务器连接超时");
        }
    }
}