<?php
namespace Shop\Controller;
use Shop\Model\AdvancesModel;
use Shop\Model\ListModel;
use Shop\Model\ProductsModel;
use Shop\Model\SiteMapModel;
use Think\Controller;
class AboutController extends BaseController {

    public function contactUs(){
        $this->assign('head_title','About');
        $this->display('About/contactUs');
    }

    public function siteMap(){
        $SM = new SiteMapModel();
        $data = $SM->sitemap_list();
        $this->assign('data',$data);
        $this->assign('head_title','About');
        $this->display('About/siteMap');
    }

    public function TC(){
        $this->assign('head_title','About');
        $this->display('About/Terms_Conditions');
    }
}