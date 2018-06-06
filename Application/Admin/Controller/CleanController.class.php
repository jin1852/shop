<?php
namespace Admin\Controller;
use Think\Controller;
class CleanController extends CommonController {
    //清理缓存
    public function clean() {
        header("Content-type: text/html; charset=utf-8");
        $dirs=array(RUNTIME_PATH);
        foreach($dirs as $value) {
            $this->rmdirr($value);
        }
        @mkdir('Runtime',0777,true);
        $this->success('系统缓存清除成功！');
    }
        //缓存文件地址
    public function rmdirr($dirname) {
        if (!file_exists($dirname)) {
            return false;
        }
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }
        $dir = dir($dirname);
        if($dir){
            while (false !== $entry = $dir->read()) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                //递归
                $this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
            }
        }
        $dir->close();
        return rmdir($dirname);
    }
}
