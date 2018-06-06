<?php
namespace Shop\Controller;
use Shop\Model\AdvancesModel;
use Shop\Model\BusinessModel;
use Shop\Model\ListModel;
use Shop\Model\ProductsModel;
use Shop\Model\ZtModel;
use Think\Controller;
class StoryController extends BaseController {
    //index
    public function index(){
        $name='Story';
        $list=S('business_list');
        if(!$list){
            $Business=new BusinessModel();
            $list=$Business->bussiness_list();
        }
        $this->assign('business_list',$list);
        $this->assign('head_title',$name);
        $this->ht();
        $this->display('Story/index');
    }

    public function ht(){
        $this->assign('ht', 'Story');
    }

    private function hth($pl){
        $this->assign('hth', __APP__ . '/Story/index');
        $this->ht();
        $this->assign('pl', $pl);
    }

    //certification
    public function certification(){
        $this->hth(ACTION_NAME);
        $this->assign('head_title','Story');
        $this->display('Story/certification');
    }

    //map
    public function map(){
        $list=S('business_list');
        if(!$list){
            $Business=new BusinessModel();
            $list=$Business->bussiness_list();
        }
        $this->hth(ACTION_NAME);
        $this->assign('business_list',$list);
        $this->assign('head_title','Story');
        $this->display('Story/map');
    }

    //showtime
    public function showtime(){
        $name = 'Showtime';
        $this->hth(ACTION_NAME);
        $list = $this->get_image($name);
        $this->assign('image_list', $list);
        $this->assign('head_title', $name);
        $view = $this->get_zt_view($name);
        $this->display($view);
    }

    //showroom
    public function showroom(){
        $name = 'Showroom';
        $this->hth(ACTION_NAME);
        $list = $this->get_image($name);
        $this->assign('image_list', $list);
        $this->assign('head_title', $name);
        $view = $this->get_zt_view($name);
        $this->display($view);
    }
}