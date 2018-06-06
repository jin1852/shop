<?php
namespace Home\Controller;
use Think\Controller;
class SecondController extends Controller{
	private $currentType=array();
	private $middle2List=array();
	private $middle3List1=array();
	private $middle3List2=array();
	private $middle4List=array();
	private $middle5List=array();
	private $rightSide1=array();
	private $rightSide2=array();
	public function index(){
		/*橱窗导航栏数据*/
		/*memCache设定与存取*/
		//S(array(
		//	'type'=>'memcache',
		//	'host'=>'192.168.150.45',
		//	'port'=>'11211',
		//	'prefix'=>'',
		//	'expire'=>300
		//	)
		//);
		$memResult=S('indexCcData');
		if(!$memResult){
			$result=\Home\Common\getCcdhData();
        	S('indexCcData',$result);
		}else{
			$result=$memResult;
		}
		$this->assign('ccdhData',$result);
		/*****************横条导航*****************/
		$navList=\Home\Common\getNavList();
        $this->assign('navList',$navList);

        $cat="0,1,15";
    	$cat=empty($_GET['cat'])?$cat:$_GET['cat'];//取得分类路径
        $id=array();
        $id=explode(',', $cat);//从分类路径中取得各级分类ID
        $this->getCate($id);//把分类的父类传人，函数中进行了assign操作
        $this->getData($id[2]);//根据二级分类取得数据
        /*对左侧的图片小图标进行信息分配，现在写死，如果库中有数据据直接取出赋值给$imaglist即可*/
		$imagelist=array();
		for($i=1;$i<=15;$i++){
			$imagelist[$i]['proPath']='0,1,15,22';
			$imagelist[$i]['pic']=$i;
		}
		/*对中间的顶部滚动进行赋值*/
		/*我们暂时不对它进行动态*/
		$middleTopImage=array(1,2,3,4,5,6,7,8);
		/*从公告表取数据*/
		//实例化广告表
		$advance = M('Advances');
		$middleTopImageList = $advance->where('location=1 AND status=0')->limit(8)->order('seriation')->select();
		$this->assign("imagelist",$imagelist);

		$this->assign("rightSide1",$this->rightSide1);
		$this->assign("rightSide2List",$this->rightSide2);

		$this->assign("middle2List",$this->middle2List);
		$this->assign("middle3List1",$this->middle3List1);
		$this->assign("middle3List2",$this->middle3List2);
		$this->assign("middle4List",$this->middle4List);
		$this->assign("middle5List",$this->middle5List);
		$this->assign('list',$middleTopImageList);
		$this->display('second');
	}
	/**
		func getCate
		@param $id=array();
		获取二级页面分类导航信息
	*/
	private function getCate($id=array()){
		$protype=M("protype");
		$type2=$protype->where(' proPid='.$id[1])->field("proTypeId,proName,proPath")
				->select();
		$type3=$protype->where(' proPid='.$id[2])->field('proTypeId,proName,proPid,proPath')
				->select();
		$data1=array();
		$data2=array();
		foreach ($type2 as $k => $v) {
			if($v['proTypeId']==$id[2]){
				$data1=$v;
				$data1['child']=$type3;
			}else{
				$data2[]=$v;
			}
		}
		$this->currentType=$data1;

		$this->assign('currentType',$data1);
		$this->assign('linkType',$data2);
		/*var_dump($data1);
		var_dump($data2);*/
	}
	/**
		func getData
		@param $id --二级分类id
		获取整个二级页面数据
	*/
		function getData($id){
			$products=M('products');
			$typeList=array();
			foreach($this->currentType['child'] as $k=>$v){
				$typeList[]=$v['proTypeId'];
			}
			//var_dump($typeList);
			$dm2=$products->where('proTypeId='.$typeList[0])
					 ->order('saleNum desc')
					 ->limit(3)
					 ->join('left join jd_prods on jd_prods.prodId=jd_products.prodId')
					 ->field('jd_prods.*')
					 ->select();
			$this->middle2List=$dm2;
			/****************************************/
			$dm3=$products->where('proTypeId='.$typeList[0])
					 ->order(' addtime desc ,saleNum desc')
					 ->limit(6)
					 ->join('left join jd_prods on jd_prods.prodId=jd_products.prodId')
					 ->field('jd_prods.*')
					 ->select();
			$cutArray=array_chunk($dm3, 3,true);
			$this->middle3List1=$cutArray[0];
			$this->middle3List2=$cutArray[1];
			/****************************************/
			$dm4=$products->where('proTypeId='.$typeList[0])
					 ->join('left join jd_prods on jd_prods.prodId=jd_products.prodId')
					 ->field('jd_prods.*,(jd_prods.price1-jd_prods.price2) as jj')
					 ->order(' jj desc')
					 ->limit(6)
					 ->select();
			$this->middle4List=$dm4;
			/****************************************/
			$dm5=$products->where('proTypeId='.$typeList[0])
					 ->order(' commentNum desc')
					 ->limit(6)
					 ->join('left join jd_prods on jd_prods.prodId=jd_products.prodId')
					 ->field('jd_prods.*')
					 ->select();
			$this->middle5List=$dm5;
			/******************************************/
			$stationNews=M('stationnews');
			$where['is_show']=1;
			$where['proTypeId']=$typeList[0];
			$where['activity']=0;
			$dr1=$stationNews->where($where)
							 ->field('newsInfo,prodId')
							 ->limit(8)
							 ->select();
			$this->rightSide1=$dr1;
			//var_dump($this->rightSide1);
			/*****************************************/
			$stationNews=M('stationnews');
			$where['is_show']=1;
			$where['proTypeId']=$typeList[0];
			$where['activity']=1;
			$dr2=$stationNews->where($where)
							 ->join('left join jd_prods on jd_prods.prodId=jd_stationnews.prodId')
							 ->field('jd_prods.*')
							 ->limit(2)
							 ->select();
			$this->rightSide2=$dr2;
			//var_dump($this->rightSide2);
			/*****************************************/

		}
}