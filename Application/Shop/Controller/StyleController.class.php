<?php
namespace Shop\Controller;
use Shop\Model\ImageshowModel;
use Shop\Model\StyleModel;
use Shop\Model\ZtModel;
use Think\Controller;
class StyleController extends BaseController {
    //
    public function index(){
        $name='Style';
        $list = $this->get_image($name);
        $this->assign('image_list', $list);
        $this->assign('head_title', $name);
        $view = $this->get_zt_view($name);
        $this->display($view);
    }

    //
    public function product_list(){
        $styleId = I('get.brandId');
        //$typeName = I('get.typeName');
        $typeId = I('get.typeId');
        //var_dump($typeName);
        //$typeName=urldecode(htmlspecialchars_decode($typeName));
        $search_key=I('get.product_key'); //产品搜索字符
        $order_type=I('get.order_type');
        $Style = new StyleModel();
        $brand = $Style->brand_list($styleId);
        $type = $Style->type_list($typeId);
        $data = $Style->get_product($styleId,$typeId,$search_key,$order_type);
        //排序数据
        $order_list=$this->order_array();
        $order_list=$this->order_select($order_list,$order_type);
        //输出
       // var_dump($typeName);
        //var_dump('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
        $this->assign('brandId',$styleId);
        $this->assign('typeId',$typeId);
        $this->assign('product_key',rawurldecode($search_key));
        $this->assign('brand', $brand);
        $this->assign('type',$type);
        //产品列表数据
        $this->assign('product_list',$data['list']);
        //产品列表分页数据
        $this->assign('page',$data['page']);
        //产品总数
        $this->assign('count',$data['count']);
        $this->assign('head_title', 'Product List');
        $this->assign('order_type',$order_type);
        //排序

        $this->assign('order_list',$order_list);
        $this->display('Style/list');
    }
//end
}