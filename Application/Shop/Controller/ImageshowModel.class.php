<?php

namespace Shop\Model;
class ImageshowModel extends BaseModel{
    public $table = 'jd_imageshow';

    //获取 图片 列表
    public function pic_list($sorts){
        $list = $this->get_list($sorts, 0, 1);
        return $list;
    }


    public function get_detail($sorts, $pid, $lv){
        $list = $this->get_list($sorts, $pid, $lv);
        return $list;
    }

    private function get_list($sorts,$pid,$lv){
        $list = S('pic_list_' . $sorts . '_' . $pid . '_' . $lv);
        if (!$list) {
            $sql = "select * from " . $this->table . " where sorts='" . $sorts . "' and status=1 and pid=" . $pid . " and lv=" . $lv . " order by top desc,id desc";
            $list = M()->query($sql);
            if ($list) {
                $list = $this->explain_pic_list($list);
                $list = $this->explain_class($list, 'type', 0, 'halfbox');
                S('pic_list_' . $sorts . '_' . $pid . '_' . $lv, $list);
            } else {
                $list = array();
            }
        }
        return $list;
    }
//end
}