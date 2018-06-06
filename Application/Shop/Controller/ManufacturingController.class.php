<?php
namespace Shop\Controller;
use Shop\Model\ImageshowModel;
use Shop\Model\ZtModel;
use Think\Controller;
class ManufacturingController extends BaseController {
    public function index(){
        $name = 'Manufacturing';
        $list = $this->get_image($name);
        $this->assign('image_list', $list);
        $this->assign('head_title', $name);
        $view = $this->get_zt_view($name);
        $this->display($view);
    }

    public function detail(){
        $pid = I('get.id');
        $title = 'Manufacturing';
        $name = 'Manufacturing_detail';
        if ($pid > 0) {
            $sorts = $this->name_get_num($title);
            $IS = new ImageshowModel();
            $list = $IS->get_detail($sorts, $pid, 2);
            $pl = $IS->get_pl($pid);
        } else {
            $list = array();
            $pl = '';
        }
        $this->assign('image_list', $list);
        $this->assign('head_title', $title);
        $this->assign('hth',__APP__.'/Manufacturing/index');
        $this->assign('pl',$pl);
        $view = $this->get_zt_view($name);
        $this->display($view);
    }
}