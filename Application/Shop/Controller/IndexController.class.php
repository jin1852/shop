<?php
namespace Shop\Controller;
use Shop\Model\ImageshowModel;
use Shop\Model\SearchModel;
use Think\Controller;
class IndexController extends BaseController {

    //首页
    public function index(){
        $name = 'Index';
        $list = $this->get_image($name);
        $this->assign('image_list', $list);
        $this->assign('head_title', 'Home');
        $this->display('Index/index');
    }

    //搜索接口
    public function search(){
        $search_key = I("get.search_key");
        if ($search_key) {
            $status = 1;
            $Search = new SearchModel();
            $list = $Search->search_list($search_key);
            $this->assign('list', $list);
            $this->assign('search_key', $search_key);
        } else {
            $status = 0;
        }
        $this->assign('status', $status);
        $this->assign('head_title', 'Search');
        $this->display('Index/search');
    }
//end
}