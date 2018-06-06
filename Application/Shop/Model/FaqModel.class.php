<?php

namespace Shop\Model;
class FaqModel extends BaseModel{
    //配置数据表
    public $faq = 'jd_faq';

    public function get_list(){
        $count = M()->table($this->faq)->where('status=1')->order('top desc,id desc')->cache(true,$this->cache_time)->count();
        $Page = $this->get_page($count,$this->page_num);
        $show = $Page->show();
        $list = M()->table($this->faq)->where('status=1')->order('top desc,id desc')->limit($Page->firstRow.','.$Page->listRows)
            ->cache(true,$this->cache_time)->select();
        if($list){
            $data['data'] = $list;
        }else{
            $data['data'] = array();
        }
        $data['page'] = $show;
        return $data;
    }
//end
  }