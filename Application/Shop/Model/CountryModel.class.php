<?php
namespace Shop\Model;
class CountryModel extends BaseModel{
    //配置数据表
    public $table = 'jd_country';  //国家表

    //获取国家列表
    public function get_list(){
        $data = S('index_country');
        if (!$data) {
            $sql = "select * from " . $this->table . " where status=1 order by en_name asc";
            $data = M()->query($sql);
            if ($data) {
                S('index_country', $data);
            } else {
                $data = array();
            }
        }
        return $data;
    }
//end
}