<?php

namespace Shop\Model;
class BusinessModel extends BaseModel{
    public $table='jd_business';

    public function bussiness_list(){
        //$list=M()->table('jd_business')->field('id,title,image,link')->where("status=1")->order('top desc,id desc')->cache(true,$this->cache_time)->select();
        $sql = "select id,title,image,link from ".$this->table." where status=1 order by top desc,id desc";
        $list = M()->query($sql);
        if($list){
            foreach($list as &$one){
                $one['image']=$this->explain_pic($one['image'],'/Public/Uploads/business/');
            }
            S('business_list',$list);
        }else{
            $list=array();
        }
        return $list;
    }
//end
  }