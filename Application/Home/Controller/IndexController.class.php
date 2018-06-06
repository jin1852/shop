<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){

        $typeModel = M('protype');
        $goodsModel = M('prods');
        $products = M('products');
        $pid_f1 = array(1,4);   //第一个栏目二级栏目的父级目录
        $map['proPid'] = array('in',$pid_f1);
        $f1nav = $typeModel->limit(16)->where($map)->select();
        //var_dump(M()->getLastSql());
        $titles = array('特价商品','大家电','小家电','手机通讯','汽车五金');  //定义分类名称
        //$prods = array(216,174,175,213,178);    //定义产品所属父级分类
        $prods = array(22,24,25,23,27);
        $proList_f1 = array();

        foreach ($prods as $k => $v) {
            $proList_f1[$k] = $products->where('proTypeId='.$v)
                                        ->join('jd_prods on jd_products.prodId = jd_prods.prodId')
                                        ->limit(10)
                                        ->select();
        }
        
        /**
         * 从几个大类中分别取出固定个数的产品，然后放入数组
            $titles = array('特价商品','大家电','小家电','手机通讯','汽车五金');
            $prods = array(216,174,175,213,178);    //手机配件、大家电、生活家电、手机通讯、五金家装
         */
        $this->assign('f1nav',$f1nav);
        $this->assign('proList_f1',$proList_f1);
        $this->assign('titles',$titles);

        $advances = M('Advances');//实力化广告表
        $list = $advances->where('location=0 AND status=0')->limit(5)->order('seriation')->select();    //查询
        $result=\Home\Common\getCcdhData();
        $this->assign('ccdhData',$result);
        $navList = \Home\Common\getNavList();
        $this->assign('navList',$navList);

        $this->assign('list',$list);
    	$this->display('index');
    }


    //获得搜索查询并返回，这里有2中选择，1中这里直接拼凑结构，
    //2是在js端拼凑
    public function getSugessions(){
    	$goods = M('prods');
    	$query = I('query');
    	$map['prodName'] = array('like','%'.$query.'%'); //利用like 匹配条件
    	$goodsData = $goods->where($map)->field('prodName,prodId')->limit(6)->select();
    	echo json_encode($goodsData);
    }
}