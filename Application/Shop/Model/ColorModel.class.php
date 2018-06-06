<?php
namespace Shop\Model;
class ColorModel extends BaseModel{
    public $color_model='jd_colors';

    public function all_color_list($cid){
        $list = S('color_list_'.$cid);
        if (!$list) {
//            $list = M()->table($this->size_model)->where('status=1')->select();
            $sql = "select * from ".$this->color_model." where status=1";
            $list= M()->query($sql);
            if ($list) {
                foreach ($list as &$one) {
                    $one = $this->explain_color_list($one);
                    if($one['id']==$cid){
                        $one['selected']=1;
                    }else{
                        $one['selected']=0;
                    }
                }
                S('color_list_'.$cid, $list);
            } else {
                $list = array();
            }
        }
        return $list;
    }

    private function explain_color_list($one){
        if ($one['rgb']) {
            $one['rgbimg'] = '';
        } else {
            $one['rgbimg'] = $this->explain_pic($one['rgbimg'],'/Public/Uploads/');
        }
        return $one;
    }
//end
}