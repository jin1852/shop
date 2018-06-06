<?php
namespace Home\Controller;
use Think\Controller;
header('content-type:text/html;charset=utf-8');
class ListController extends Controller {
	/*列表页数据私有变量定义*/
    private $leftCate=array();//定义左侧分类变量
	private $bandCate=array();//定义品牌分类变量
	private $priceCate=array();//定义价格分类变量
	private $sizeCate=array();//定义型号分类变量
	private $colorCate=array();//定义分辨率分类变量
    private $datatList=array();//列表页需要展示的数据
    private $where=array();//封装完后的查询条件
    private $order='saleNum desc';//排序默认 
    private $hotSale=array();//热卖数据
    private $proSale=array();//促销信息
    private $limitSale=array();//限时抢购
    private $popSale=array();//推广商品
    private $rankSale=array();//销量排行;
    private $page='1';
	private $catestatus;//当前分类状态
    //private $currentType;//当前类别
    private $historyView=array();//浏览记录
    /*橱窗导航栏数据私有变量*/
    private $ccdhData=array();
	/*第一页面加载*/
    public function index(){
    	$ocat="0,1,15,22";
        $checkCat=explode(',',$_GET['cat']);
        $catNum=count($checkCat);
        /*对传入的路径做简单验证*/
        if($catNum<4||!$checkCat[3]){
            $_GET['cat']=$ocat;
        }
        $cat=empty($_GET['cat'])?$ocat:$_GET['cat'];//取得分类路径

        $id=array();
        $id=explode(',', $cat);//从分类路径中取得各级分类ID
        
        /************面包线导航数据*******************/
        $breadWhere['proTypeId']=array('in',$id);
        $bproType=M('protype');
        $dataBread=$bproType->where($breadWhere)
                            ->field('proName,proPath')
                            ->select();
        //var_dump($dataBread);
        /*******************************/
        /*对比栏的数据状态保持*/
        $prodIdCookieList=cookie('prodIdCookie');
        if(is_array($prodIdCookieList)){
            $prods=M('prods');
            $where['prodId']=array('in',$prodIdCookieList);
            $prodIdCookieData=$prods->where($where)->select();

            //echo $prods->getlastsql();
            //var_dump($prodIdCookieData);

            $this->assign('prodIdCookieData',$prodIdCookieData);
            $this->assign('prodIdCookieList',$prodIdCookieList);
            $prodIdCookieNum=count($prodIdCookieList)+1;
            $this->assign('prodIdCookieNum',$prodIdCookieNum);
        }else{
            $prodIdCookieList=array();
            $prodIdCookieData=array();
            $this->assign('prodIdCookieData',$prodIdCookieData);
            $this->assign('prodIdCookieList',$prodIdCookieList);
            $prodIdCookieNum=1;
            $this->assign('prodIdCookieNum',$prodIdCookieNum);
        }

        /*获取橱窗导航栏的数据*/
        /*memCache设定与存取*/
        //S(array(
        //    'type'=>'memcache',
        //    'host'=>'192.168.150.45',
        //    'port'=>'11211',
        //    'prefix'=>'',
        //    'expire'=>300
        //    )
        //);
        $memResult=S('indexCcData');
        if(!$memResult){
            $result=\Home\Common\getCcdhData();
            S('indexCcData',$result);
        }else{
            $result=$memResult;
        }
        $this->assign('ccdhData',$result);
        /**横条导航数据**/
        $navList=\Home\Common\getNavList();
        $this->assign('navList',$navList);
        //var_dump($navList);
        /*列表页数据获取*/
        $this->condition($id);
    	
        
        $this->catestatus=$id[2];
        $protype=M("protype");//实例化类型表
        $currentTypeData=$protype->where("proTypeId=".$id[3])
                                   ->field('proName')
                                   ->find();
        $leftCateInMemId='leftCateInMemId'.$id[2];
        $lefcateInMem=S($leftCateInMemId);
        if(!$lefcateInMem){
    	    $this->getLeftCate($protype,$id);
           S($leftCateInMemId,$this->leftCate);
        }else{
           $this->leftCate=$lefcateInMem;
        }
    	$products=M('products');//实例化商品详情表
    	$this->getSearchCondition($products,$id);

        //$prods=M("prods")//实例化
        $this->getData($products,$id);
        /*获取浏览记录信息*/
        $this->historyView();
        /*获取其它相关信息*/
        $this->getOtherData($id[3]);
    	/*变量分配*/

        $this->assign('breadLead',$dataBread);

        $this->assign('hotSale',$this->hotSale);
        $this->assign('proSale',$this->proSale);
        $this->assign('limitSale',$this->limitSale);
        $this->assign('popSale',$this->popSale);
        $this->assign('rankSale',$this->rankSale);

        $this->assign('historyView',$this->historyView);

    	$this->assign('catestatus',$this->catestatus);
        $this->assign('currentType',$currentTypeData['proName']);
        //dump($this->brandCate);
    	$this->assign('leftCate',$this->leftCate);
    	$this->assign('brandCate',$this->brandCate);
    	$this->assign('priceCate',$this->priceCate);
        $this->assign('sizeCate',$this->sizeCate);
    	$this->assign('colorCate',$this->colorCate);

        $this->assign("dataList",$this->dataList);
        $this->display('list');
    }

    /**
		func getLeftCate 获取左侧分类导航
		@param $protype 传入需要查找的的表的实例化模型
		@param $id 传入接收分类路径
    */
    private function getLeftCate($protype,$id){
    	/***************获取二级分类*******************/
    	$cat1=$protype->where('proPid='.$id[1])->field('proName, proTypeId')->select();
    	/*****************根据二级分类获取三级分类***********/
    	/*根据二级分类拼接需要查询到的三级分类的IN 条件*/
    	$pidlist=array();
    	foreach ($cat1 as $key => $value) {
    		$pidlist[]=$value['proTypeId'];
    	}
    	$pidliststr=implode(',',$pidlist);
    	/**获取三级分类**/
    	$where='proPid in ('.$pidliststr.')';
    	$cat2=$protype->where($where)->field('proTypeId,proName,proPath,proPid')->select();
    	/*拼接成前台需要的数组*/
    	$lefcate=array();
    	foreach ($cat1 as $k1 => $v1) {
    		foreach ($cat2 as $k2 => $v2) {
    			if($v2['proPid']==$v1['proTypeId']){
    				$v1['child'][]=$v2;
    			}
                //if($v2['proTypeId']==$id[3]){
                    //$this->currentType=$v2['proName'];
                //}
    		}

    		$lefcate[]=$v1;
    	}
    	 // echo "<pre>";
    	 // print_r($lefcate);

    	$this->leftCate=$lefcate;
    }
    /**
    	func 获取条件栏品牌,价格,尺寸信息
    	@param $model 传入需要查找的的表的实例化模型
    	@param $id 传入接收分类路径
    */
    private function getSearchCondition($model,$id){
    	//$r=$model->where("proTypeId=".$id[$size-1])->field('brandId');
    	//搜索数据库取得价格，品牌，尺寸信息，如果需要取得其它信息，则在增加join和field条件即可
        //S(array(
        //    'type'=>'memcache',
        //    'host'=>'192.168.150.45',
        //    'port'=>'11211',
        //    'prefix'=>'',
        //    'expire'=>300
        //    )
        //);
        $SearchCondition='SearchCondition'.$id[3];
        $SearchConditionInMem=S($SearchCondition);
        if(!$SearchConditionInMem){
            $r=$model->where("proTypeId=".$id[3])
              ->join('left join jd_brands on jd_products.brandId=jd_brands.brandId')
              ->join('left join jd_sizes  on jd_products.sizeId=jd_sizes.sizeId')
              ->join('left join jd_colors on jd_products.colorId=jd_colors.colorId')
              ->field('jd_brands.brandName,jd_brands.brandId,jd_sizes.sizeName,jd_sizes.sizeId,jd_products.price,jd_colors.colorId,jd_colors.colorName')
              ->select();
            S($SearchCondition,$r);
        }else{
            $r=$SearchConditionInMem;
        }    
    	//var_dump($r);
    	/*对取得的条件进行封装，成相应的数组;*/
    	$brandCate=array();
    	$priceCate=array();
    	$sizeCate=array();
        $colorCate=array();
        /*使搜索条件数组中的相应的条件不重复,定义的数组*/
        $checkcolor=array();
        $checkbrand=array();
        $checksize=array();
    	//echo $model->getlastsql();
    	foreach ($r as $k => $v) {
            $size=array();
            $brand=array();
            $color=array();
            
            if(!in_array($v['brandId'], $checkbrand)){
                $brand['brandName']=$v['brandName'];
                $brand['brandId']=$v['brandId'];
                $brandCate[]=$brand;

                //将不存在的编号存入数字，用于以后检测
                $checkbrand[]=$v['brandId'];
            }
    		if(!in_array($v['colorId'], $checkcolor)){
                $color['colorName']=$v['colorName'];
                $color['colorId']=$v['colorId'];
                $colorCate[]=$color;
                //将不存在的编号存入数字，用于以后检测
                $checkcolor[]=$v['colorId'];
            }
            if(!in_array($v['sizeId'], $checksize)){
                $size['sizeName']=$v['sizeName'];
                $size['sizeId']=$v['sizeId'];
                $sizeCate[]=$size;
                //将不存在的编号存入数字，用于以后检测
                $checksize[]=$v['sizeId'];
            }
    		
    		$price[]=$v['price'];
            
    	}
        /*价格阶梯*/
    	$max=max($price);
        $min=min($price);
        $step=($max-$min)/20;
        $step=ceil($step);
        for($i=0;$i<=5;$i++){
            $priceList['priceId']=$i;
            if($i==0){
                $priceList['priceName']='0-'.($min+4*$step);  
            }else if($i==1){    
                $priceList['priceName']=($min+4*$step).'-'.($min+7*$step);
            }else if($i==2){
                $priceList['priceName']=($min+7*$step).'-'.($min+11*$step);
            }else if($i==3){
                $priceList['priceName']=($min+11*$step).'-'.($min+15*$step);
            }else if($i==4){
                $priceList['priceName']=($min+15*$step).'-'.($min+18*$step);
            }else{
                $priceList['priceName']='大于'.($min+18*$step);  
            }
            $priceCate[]=$priceList;
        }
    	$this->brandCate=$brandCate;
    	$this->sizeCate=$sizeCate;
        $this->priceCate=$priceCate;
        $this->colorCate=$colorCate;
    }
    /**
        func getdata  获取列表页符合条件的数据
        @param $model 传人需要查询的表的实例化模型
        @param $id 传入接收分类路径
    */
    private function getData($model,$id){

        /*分页*/
       
        $data=$model->where($this->where)->order($this->order)
                    ->page($this->page.',20')
                    ->join('left join jd_prods on jd_products.prodId=jd_prods.prodId')
                    ->field('jd_prods.*,jd_products.commentNum')
                    ->select();
        /*分页实现*/
        $this->dataList=$data;

        $count=$model->where($this->where)->count();
        $Page=new \Think\Page($count,20);
        /*定做页面样式*/
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('end','尾页');

        $show=$Page->show();
        $this->assign('page',$show);

        //echo $model->getlastsql();
        //var_dump($data);
        
    }

    /** 
        func  封装列表页的查询条件 where order page
        //通过$_GET获取参数 
    */
    private function condition(){
        //var_dump($_GET);
        $where=array();
        foreach ($_GET as $k => $v) {
            if(!empty($v)){
                if($k=='priceName'){
                    $i=preg_match('/-+/', $v);
                    if($i==1){
                        $data=explode('-',$v);
                        $where['price']=array('between',array($data[0],$data[1]));
                    }else{
                        $p=substr($v,6);
                        $where['price']=array('gt',$p);
                    }
                }else if($k=='cat'){
                    $data=explode(',',$v);
                    $where['proTypeId']=$data[3];
                }else if($k=='sort'){
                    $str=explode('_',$v);
                    $this->order=$str[0].' '.$str[1];
                }else if($k=='p'){
                    $this->page=$v;
                }else{
                    $where[$k]=$v;
                }
            }
        }
        $this->where=$where;
    }
/**
    获取浏览过的商品信息
    func   historyView
*/
    private function historyView(){
        $viewStr=cookie('RecentlyGoods');
        $viewList=explode(',', $viewStr);
        $prods=M('prods');
        $where['prodId']=array('in',$viewList);
        $dataList=$prods->where($where)
                        ->limit(8)
                        ->select();
        $this->historyView=$dataList;
    }
    /**
        func getOtherData
        @param $id /获取其它相关数据

    */
    private function getOtherData($id){
        $products=M('products');
        $dh=$products->where('proTypeId='.$id)
                     ->order('saleNum desc')
                     ->limit(3)
                     ->join('left join jd_prods on jd_prods.prodId=jd_products.prodId')
                     ->field('jd_prods.*')
                     ->select();
        $this->hotSale=$dh;
        /*******************************************/
        $stationNews=M('stationnews');
            $where['is_show']=1;
            $where['proTypeId']=$id;
            $where['activity']=0;
            $dr1=$stationNews->where($where)
                             ->field('newsInfo,prodId')
                             ->limit(8)
                             ->select();
        $this->proSale=$dr1;
        /*******************************************/
        //$stationNews=M('stationnews');
            $where['is_show']=1;
            $where['proTypeId']=$id;
            $where['activity']=1;
            $dr2=$stationNews->where($where)
                             ->join('left join jd_prods on jd_prods.prodId=jd_stationnews.prodId')
                             ->field('jd_prods.*')
                             ->limit(2)
                             ->select();
        $this->limitSale=$dr2;
        /*******************************************/
        //$stationNews=M('stationnews');
            $where['is_show']=1;
            $where['proTypeId']=$id;
            $where['activity']=2;
            $dr3=$stationNews->where($where)
                             ->join('left join jd_prods on jd_prods.prodId=jd_stationnews.prodId')
                             ->field('jd_prods.*')
                             ->find();
        $this->popSale=$dr3;
        /*******************************************/
        //S(array(
        //    'type'=>'memcache',
        //    'host'=>'192.168.150.45',
        //    'port'=>'11211',
        //    'prefix'=>'',
        //    'expire'=>300
        //    )
        //);
        $rankData='rankData'.$id;
        $memResultRank=S($rankData);
        if(!$memResultRank){
            //echo $id;
            $rankSale=$products->where('proTypeId='.$id)
                               ->order('saleNum desc')
                               ->limit(2)
                               ->join('left join jd_prods on jd_prods.prodId=jd_products.prodId')
                               ->field('jd_prods.*')
                               ->select();
            S($rankData,$rankSale);
        }else{
            $rankSale=$memResultRank;
        }
        $this->rankSale=$rankSale;
    }
}