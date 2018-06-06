<?php
namespace Shop\Controller;
use Shop\Model\ImageshowModel;
use Shop\Model\ZtModel;
use Think\Controller;
class SeriesController extends BaseController {
    public function index(){
        $name = 'Series';
        $list = $this->get_image($name);
        $list = $this->handle_image_list($list);
        $this->assign('image_list', $list);
        $this->assign('head_title', $name);
        $this->assign('status_type',1);
        $view = $this->get_zt_view($name);
        $this->display($view);
    }

    private function handle_image_list($list){
        $user = session("user");
        foreach ($list as &$one){
            $re = M('users_favourite_series')->where('userId=' . $user['userId'] . ' and series_Id=' . $one['id'])->cache(false)->find();
            if ($re) {
                $one['favourite'] = $re['status'];
            } else {
                $one['favourite'] = 0;
            }
        }
        return $list;
    }
}