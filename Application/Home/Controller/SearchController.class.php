<?php

namespace Home\Controller;
use Think\Controller;

class SearchController extends Controller {

    /**
     * 需要完成几个步骤
     *      通过关键词获得对应的商品
     *      左侧栏显示那些这些商品分别包含在那些栏目当中(仅仅是二级目录 > 三级目录)
     *      中间显示这些商品包含的特点，比如价格，品牌、尺寸、颜色
     *      
     */
    function index(){
        //$query = I('query')?I('query'):'宏碁'; //搜索词
        $query = I('query');
        $prodModel = M('prods');
        $goodsModel = M('products');
        $brandsModel = M('brands');
        $sizesModel = M('sizes');
        $typeModel = M('protype');
        $colorsModel = M('colors');
        $brandId = I('brandId');
        $sizeId = I('sizeId');
        $colorId = I('colorId');
        $typeId = I('proTypeId');
        $sort = I('sort');

        $result=\Home\Common\getCcdhData();
        $this->assign('ccdhData',$result);
        $navList = \Home\Common\getNavList();
        $this->assign('navList',$navList);

        $navCate = array(); //定义侧边栏导航，里面的结构包含2、3级分类且为层级关系

        $typesArr = $typeModel->getField('proTypeId, proName');
        $brandsArr = $brandsModel->getField('brandId, brandName');
        $sizesArr = $sizesModel->getField('sizeId, sizeName');
        $colorsArr = $colorsModel->getField('colorId, colorName');
        $submap['prodName'] = array('like','%'.$query.'%');
        $mymap['prodName'] = array('like','%'.$query.'%');  //构造搜索条件
        $mymap['prodInfo'] = array('like','%'.$query.'%');  //构造搜索条件
        $mymap['_logic'] = "OR";
        $where['_complex'] = $mymap;

        if(!empty($brandId)){
            $where['jd_products.brandId'] = array('eq',$brandId);
        }
        
        if(!empty($sizeId)){
            $where['jd_products.sizeId'] = array('eq',$sizeId);
        }

        if(!empty($colorId)){
            $where['jd_products.colorId'] = array('eq',$colorId);
        }

        if(!empty($typeId)){
            $where['jd_products.proTypeId'] = array('eq',$typeId);
        }

        if(!empty($sort)){
            $sortItem = explode('_', $sort);

            switch ($sortItem[0]) {
                case 'price':
                    $order = "price DESC";
                    break;
                case 'saleNum':
                    $order = "saleNum DESC";
                    break;
                case 'commentNum':
                    $order = "commentNum DESC";
                    break;
                case 'addtime':
                    $order = "addtime DESC";
                    break;
                default:
                    $order = 'saleNum DESC';
                }
        }
        
        $count = $prodModel
                    ->join('LEFT JOIN jd_products ON jd_prods.prodId = jd_products.prodId')
                    ->where($where)
                    ->count();
        $page = new \Think\Page($count, 24);    //分页
        $page->setConfig('prev','上一页');
        $page->setConfig('next','下一页');
        $page->setConfig('first','首页');
        $page->setConfig('last','尾页');
         $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $page->show();


        $prodData = $prodModel
                    ->join('LEFT JOIN jd_products ON jd_prods.prodId = jd_products.prodId')
                    ->where($where)->order($order)->limit($page->firstRow.", ".$page->listRows)->select();    //查询产品数据
        // echo $prodModel->getLastSql();
        // return;
    

        //显示出过滤条件
        //SEELCT * FROM products WHERE products.prodId in (SELECT prodId FROM prods WHERE prodName like %nokia%); 
        $subQuery = $prodModel->field('prodId')->where($submap)->select(false);
        
        $brandsQuery['prodId'] = array('exp','in '.$subQuery);  //构建子查询语句
        $brandsData = $goodsModel->field('brandId')->group('brandId')->where($brandsQuery)
                                ->select(); //这里使用了EXP：表达式  获得brand相关数据

        $colorsData = $goodsModel->field('colorId')->group('colorId')->where($brandsQuery)
                                ->select(); //获取color相关数据
        
        $sizesData = $goodsModel->field('sizeId')->group('sizeId')->where($brandsQuery)
                                ->select(); //获取size相关数据

        $typesIds = $goodsModel->field('proTypeId')->group('proTypeId')->where($brandsQuery)
                                ->select(false); //获取三级目录的id号
        //( SELECT `proTypeId` FROM `jd_products` WHERE `prodId` in ( SELECT `prodId` FROM `jd_prods` WHERE `prodName` LIKE '%诺基亚%'  ) GROUP BY proTypeId  )

        $map['proTypeId'] = array('exp','in '.$typesIds);
        $typesData = $typeModel->where($map)->select(); //获得所有的三级分类数据，包括id号、proName、proPid等数据
        // echo $typeModel->getLastSql();
        // dump($typesData);
        // return;

        /**
         * 下面需要对获得到的数据进行处理，拼凑成适合展示在左侧的样式
         *      主要是将【上级分类】相同的目录组合到统一层级
                    eg:比如上级分类是0
                        array(
                            '0'=>array(
                                array(),
                                array()
                            )
                        )
                    数据第一层为关联数组(键名为当前分类的父级id)，第二层为索引数组
         *      这里只需要搞清楚one thing,需要怎样的数据格式，如果不知道的话，可以在纸上先画出自己的逻辑结构，然后再去用程序实现
         *
         *
         *这边测试数据不完善，因为非最终极目录是不允许存放商品的。
         */
        foreach ($typesData as $k1 => $v1) {
                $navCate[$v1['proPid']][] = $v1;
        }
        // dump($navCate);
        // return;


        $this->assign('brandsArr',$brandsArr);
        $this->assign('typesArr',$typesArr);
        $this->assign('sizesArr',$sizesArr);
        $this->assign('colorsArr',$colorsArr);

        $this->assign('brandsData',$brandsData);
        $this->assign('colorsData',$colorsData);
        $this->assign('sizesData',$sizesData);
        $this->assign('prodData',$prodData);
        $this->assign('navCate',$navCate);
        $this->assign('page',$show);
        $this->assign('query',$query);
        $this->display('search');
    }
}
