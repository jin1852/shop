<?php

namespace Shop\Model;
class AdvancesModel extends BaseModel{
    //配置数据表
    public $Advances = 'jd_advances';  //广告主表

    //获取banner数据
    public function advances_list(){
        //$Advances_list = M()->table($this->Advances)->where('status=0')->limit(5)->order('seriation desc,advanceId desc')->cache(true, $this->cache_time)->select();    //查询
        $sql = "select * from ".$this->Advances." where status=0 order by seriation desc,advanceId desc limit 5";
        $Advances_list=M()->query($sql);
        if($Advances_list) {
            $banner=$this->explain_banner($Advances_list);
            S('banner', $banner);
        }else{
            $banner=array();
        }
        return $banner;
    }

    //解释图片地址
    private function explain_banner($banner){
        foreach($banner as &$one){
            if($one['location']==1) {
                $one['image'] = $this->explain_pic($one['img'], '/Public/Uploads/Advance/');
            }else {
                $one['image'] = $one['link'];
            }
            //获取图片宽高
            $size=getimagesize($one['image']);
            if($size) {
                $one['width'] = $size[0] . 'px';
                $one['height'] = $size[1] . 'px';
            }
        }
        return $banner;
    }
//end
  }