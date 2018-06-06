<?php

namespace Common\Model;

/**
 * Created by PhpStorm.
 * User: j
 * Date: 2017/12/15
 * Time: 上午12:12
 */
class ConfigModel extends \Think\Model{

    protected $tableName="base_config";

    public $id = [1, 2, 3];
    public $nameIds = array(1 => "web", 2 => "wap", 3 => "admin");

    public function get_data($id){
        if (!in_array($id, $this->id)) return false;
        $name = $this->nameIds[$id];
        $data = $this->where(array("name" => $name))->cache(true,1800)->find();
        return $data ? $data['status'] : false;
    }

}