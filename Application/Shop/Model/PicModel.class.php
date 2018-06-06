<?php

namespace Shop\Model;
class PicModel extends BaseModel{
    //获取 图片 列表
    public function pic_list($table,$where,$order,$key,$value,$S_name){
        $list=M()->table($table)->where($where)->order($order)->cache(true,$this->cache_time)->select();
        if($list){
            foreach($list as &$one){
                $one[$key]=$this->explain_pic($one[$key],$value);
            }
            S($S_name,$list);
            return $list;
        }else{
            return array();
        }
    }
//end
  }