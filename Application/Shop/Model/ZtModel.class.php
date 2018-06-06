<?php

namespace Shop\Model;

class ZtModel extends BaseModel{
    //配置数据表
    public $zt_conf = 'jd_zt_conf';  //主题配置主表
    public $zt_dir = 'Zt/'; //主题文件夹
    public $zt_default = 'default'; //默认视图

    //检测主题配置
    public function check_zt_conf($name){
        $view = S('zt_view_'.$name);
        if(!$view) {
            $sql = "select * from " . $this->zt_conf . " where name='" . $name . "' and status=1 limit 1";
            $data = M()->query($sql);
            if ($data) {
                $view_new = $this->zt_dir.$data[0]['view'];
                S('zt_view_' . $name, $view_new);
                return $view_new;
            } else {
                return $this->zt_dir.$this->zt_default;
            }
        }else{
            return $view;
        }
    }
//end
  }