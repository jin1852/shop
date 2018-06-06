<?php

namespace Shop\Model;
use Think\Page;
class BaseModel{
    public $cache_time=3600;
    public $find='http://';
    public $page_num=40;
    public $rand_seed=array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',0,1,2,3,4,5,6,7,8,9);
    public function url(){
        if($_SERVER['HTTP_HOST']=='127.0.0.1' or $_SERVER['HTTP_HOST']=='localhost'){
            return 'http://'.$_SERVER['HTTP_HOST'].'/Shop/';
        }else{
            return 'http://'.$_SERVER['HTTP_HOST'].'/';
        }
    }

    public function explain_pic($one,$value=null){
        if ($one) {
            if (strpos($one, $this->find) == false) {
                if ($value) {
                        $one = $this->url() . $value . $one;
                } else {
                        $one = $this->url() . $one;
                }
            }
        } else {
            $one= "";
        }
        return $one;
    }
    public function explain_time($one){
        if ($one>0) {
            $one= date("Y-m-d H:i:s",$one);
        } else {
            $one = '';
        }
        return $one;
    }
    public function explain_ip($one){
        if ($one>0) {
            $one= ip2long($one);
        } else {
            $one = '';
        }
        return $one;
    }
    //分页
    public function get_page($count, $num){
        $page = new Page($count, $num);
        $page->lastSuffix = false;
       // $page->setConfig('header', '&nbsp;page&nbsp;%NOW_PAGE%/total&nbsp;page&nbsp;%TOTAL_PAGE%&nbsp;');
        $page->setConfig('header','');
        $page->setConfig('prev', 'prev');
        $page->setConfig('next', 'next');
        $page->setConfig('last', 'last');
        $page->setConfig('first', 'first');
        $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $page->parameter = I('get.');
        return $page;
    }

    public function handle_path($string){
        $str=substr($string,-1);
        if($str==','){
            return substr($string,0,-1);
        }else{
            return $string;
        }
    }

    //解释class
    public function explain_class($list,$key,$value,$class_name){
        foreach($list as &$one){
            if($one[$key]==$value){
                $one['class']=$class_name;
            }else{
                $one['class']='';
            }
        }
        return $list;
    }

    public function lock_table($table,$type=1){
        $type = ( $type == 1 ) ? 'READ' : 'WRITE';
        $sql = "LOCK TABLES `$table` $type";
        return M()->execute($sql);
    }

    public function unlock_table(){
        $sql = "UNLOCK TABLES";
        return M()->execute($sql);
    }

    public function create_random_str($len){
        $str = '';
        $len = (intval($len) && $len) ? $len : 8;
        for ($i = 0; $i < $len; $i++) {
            $str .= $this->rand_seed[array_rand($this->rand_seed)];
        }
        return $str;
    }

    //解释排序
    public function explain_order($order){
        $limit = 'p.isNew desc,';
        switch($order){
            case 1:$limit.='p.totalSale desc';break;
            case 2:$limit.='p.price1 desc';break;
            case 3:$limit.='p.prodId desc';break;
            case 4:$limit.='p.isHot desc';break;
            default:$limit.='p.prodId desc';break;
        }
        return $limit;
    }

    //
    public function explain_pic_list($list){
        foreach ($list as &$one) {
            $one['image'] = $this->explain_pic($one['image'], '/Public/Uploads/ImageShow/');
            $size = getimagesize($one['image']);
            if ($size) {
                $one['width'] = $size[0] . 'px';
                $one['height'] = $size[1] . 'px';
            }
            if ($one['LinkType'] == 1) {
                if ($one['child'] == 1 && $one['sorts'] == 4) {
                    $one['href'] = __APP__ . '/Manufacturing/detail/id/' . $one['id'];
                }else {
                    if ((strstr($one['link'], 'javascript') == false)) {
                        $one['href'] = __APP__ . '/' . $one['link'];
                    } else {
                        $one['href'] = $one['link'];
                    }
                }
            } else {
                $one['href'] = $one['OpenLink'];
            }
        }
        return $list;
    }
}