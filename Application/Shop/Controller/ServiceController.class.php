<?php
namespace Shop\Controller;
use Shop\Model\AdvancesModel;
use Shop\Model\ImageshowModel;
use Shop\Model\ListModel;
use Shop\Model\ProductsModel;
use Shop\Model\ZtModel;
use Think\Controller;
class ServiceController extends BaseController {

    public function index(){
        $this->assign('head_title','Service');
        $this->display('Service/index');
    }
//end
}