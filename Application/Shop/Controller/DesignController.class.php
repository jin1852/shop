<?php
namespace Shop\Controller;
use Shop\Model\ImageshowModel;
use Shop\Model\ZtModel;
use Think\Controller;
class DesignController extends BaseController {

//    //单独设计的界面
//    public function index(){
//        $this->assign('head_title','Desgin');
//        $this->display('Design/index');
//    }

    public function index(){
        $name = "Design";
        $list = $this->get_image($name);
        $this->assign('image_list', $list);
        $this->assign('head_title', $name);
        $view = $this->get_zt_view($name);
        $this->display($view);
    }
}