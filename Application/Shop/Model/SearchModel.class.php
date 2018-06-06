<?php

namespace Shop\Model;
class SearchModel extends BaseModel{
    //配置数据表
    public $table = 'jd_prods';  //广告主表
    //全站搜索
    public function search_list($key){
        $list=array();
        //搜索产品
        $product_list=$this->product_list($key);
        if($product_list){
            foreach($product_list as &$one) {
                $list[] = $one;
            }
        }
        //搜索系列
        $brand_list=$this->brand_list($key);
        if($brand_list){
            foreach($brand_list as &$one) {
                $list[] = $one;
            }
        }
        //搜索分类
        $type_list=$this->type_list($key);
        if($type_list){
            foreach($type_list as &$one) {
                $list[] = $one;
            }
        }
        return $list;
    }

    //搜索产品
    private function product_list($key){
        $product_list=S('search_product_'.$key);
        if(!$product_list) {
            $where = "prodName like '%" . $key . "%' and status=1";
            //$product_list = M()->field('prodId AS id , prodName AS title')->table('jd_prods')->where($where)->order('id desc')->cache(true, $this->cache)->select();
            $sql = "select prodId AS id , prodName AS title from ".$this->table." where ".$where." order by id desc";
            $product_list=M()->query($sql);
            if ($product_list) {
                foreach ($product_list as &$one) {
                    $one['link'] = __APP__ . '/Product/detail/prodId/' . $one['id'];
                    $one['type'] = 'prods';
                }
                S('search_product_'.$key,$product_list);
            } else {
                $product_list = array();
            }
        }
        return $product_list;
    }

    //搜索系列
    private function brand_list($key){
        $brand_list=S('search_brand_'.$key);
        if(!$brand_list) {
            //$brand_list = M()->table('jd_brands')->field('brandId AS id , brandName AS title')->where("brandName like '%" . $key . "%' and status=1")->order('id desc')->cache(true, $this->cache)->select();
            $sql = "select brandId AS id , brandName AS title from jd_brands where brandName like '%" . $key . "%' and status=1 order by id desc ";
            $brand_list = M()->query($sql);
            if ($brand_list) {
                foreach ($brand_list as &$one) {
                    $one['link'] = '#';
                    $one['type'] = 'brands';
                }
                S('search_brand_'.$key,$brand_list);
            } else {
                $brand_list = array();
            }
        }
        return $brand_list;
    }

    //搜索分类
    private function type_list($key){
        $type_list=S('search_type_'.$key);
        if(!$type_list) {
//            $type_list = M()->table('jd_protype')->field('prodTypeId AS id , proName AS title,*')->where("proName like '%" . $key . "%' and proLive=1")->order('id desc')->cache(true, $this->cache)->select();
            $sql = "select prodTypeId AS id , proName AS title,* from jd_protype where proName like '%" . $key . "%' and proLive=1 order by id desc";
            $type_list = M()->query($sql);
            if ($type_list) {
                foreach ($type_list as &$one) {
                    $one['link'] = __APP__ . '/Product/index/id/' . $one['id'] . '/pid/' . $one['proPid'] . '/lv/' . $one['lv'];
                    $one['type'] = 'protype';
                }
                S('search_type_'.$key,$type_list);
            } else {
                $type_list = array();
            }
        }
        return $type_list;
    }
//end
  }