<?php
namespace Shop\Controller;
use Shop\Model\ImageshowModel;
use Shop\Model\ZtModel;
use Think\Controller;
class HotController extends BaseController {
    public function index(){
        $name = 'Hot';
        $list = $this->get_image($name);
        $this->assign('image_list', $list);
        $this->assign('head_title', $name);
        $view = $this->get_zt_view($name);
        $this->display($view);
    }
}