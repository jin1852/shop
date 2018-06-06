<?php
namespace Admin\Model;
class ZtModel{
    //视图定义路径
    public $dir = "/home/wwwroot/gala/Application/Shop/View/";

    //获取文件列表
    public function get_view_list($value=null){
        $dir = $this->check_type($value);
        if (is_dir($dir)) {
            $open = opendir($dir);
            if ($open) {
                $data = array();
                while (($files = readdir($open)) !== false) {
                    $f = explode('.', $files);
                    if ($f[0]) {
                        $data[] = $f[0];
                    }
                }
                $list = array_merge(array_filter($data));
                return $list;
            } else {
                return array();
            }
        } else {
            return array();
        }
    }

    //判断类型 PC端、移动端 返回详细视图路径
    private function check_type($value){
        switch ($value){
            case 'PC':$dir=$this->dir."PC/Zt";break;
            case 'Mobile':$dir=$this->dir."Mobile/Zt";break;
            default: $dir=$this->dir."PC/Zt";break;
        }
        return $dir;
    }
//end
}