<?php
namespace Admin\Controller;
use Think\Controller;
class NewsController extends CommonController {
    /*访问权限*/
    public function _initialize(){
        parent::_initialize();
        if($_SESSION['admin']['levelId']!=1){
            $this->error('权限不够');
        }
    }
   public function news(){
   		header('Content-Type:text/html;charset=utf-8');
   		$news = M('stationnews');
   		$arr = $news -> table('jd_stationnews as C,jd_brands as B,jd_prods as P,jd_protype as Pt')
   					 -> where('C.brandId = B.brandId and C.prodId = P.prodId and C.proTypeId = Pt.proTypeId')
   					 -> select();
       var_dump(M()->getlastsql());
   		//dump($arr);exit;
   		$this -> assign('arr',$arr);
   		$this -> display();
   }
    public function addNews(){
    	header('Content-Type:text/html;charset=utf-8');
    	$protype = M('protype');
    	$data = $protype -> select();
    	$classification = $this -> chaxun($data);
  //   	echo '<pre>';
		// print_r($data);exit;
		$this -> assign('classification',$classification);
    	$this -> display();
    }
    private function chaxun($data, $proPid=0) {
		$arr = array();
		foreach($data as $volist) {
			//dump($volist);
			if($volist['proPid'] == $proPid) { // 得到所有的一级分类
				$volist['child'] = $this->chaxun($data, $volist['proTypeId']);
				$arr[] = $volist;
				// 继续调用当前方法  - 递归函数
			}
		}
		return $arr;
	}
	public function brands(){
		$data = $_GET;
		$id = json_encode($data['proTypeId']);
		//echo $id;
		$products = M('products');
		$brands = $products -> where('jd_products.proTypeId = '.$id)
				 -> join('left join jd_brands ON jd_products.brandId = jd_brands.brandId')
				 -> group('jd_brands.brandId')
				 -> field('jd_brands.*')
				 -> select();
		echo json_encode($brands);
	}
	public function prods(){
		$data = $_GET;
		$brandId = json_encode($data['prodId']);
		$proTypeId = json_encode($data['proTypeId']);
		//echo json_encode($data);
		//echo $brandId;
		//echo $proTypeId;
		$products = M('products');
		$prods = $products -> where('jd_products.brandId = '.$brandId." and jd_products.proTypeId = ".$proTypeId)
		 		 -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
		 		 -> field('jd_prods.prodId,prodName')
		 		 -> select();
		echo json_encode($prods);
	}
	public function insertNews(){
		header("Content-Type:text/html;charset=utf-8");
		$data = $_POST;
		//dump($data);exit;
		$news = M('stationnews');
		if($news -> add($data)){
			$this -> success("新闻添加成功！");
		}else{
			$this -> error("新闻添加失败！");
		}
	}
   public function editNews(){
   		header('Content-Type:text/html;charset=utf-8');
   		$news = M('stationnews');
   		$newsId = $_GET['newsId'];
   		$arr = $news -> table('jd_stationnews as C,jd_brands as B,jd_prods as P,jd_protype as Pt')
   					 -> where('C.brandId = B.brandId and C.prodId = P.prodId and C.proTypeId = Pt.proTypeId and newsId = '.$newsId)
   					 -> select();
   		//dump($arr);exit;
   		$this -> assign('arr',$arr);
   		$this -> display();
   }
   public function do_editNews(){
   		$data = I('post.');
   		$news = M('stationnews');
   		if($news -> save($data)){
   			$this -> redirect("News/news");
   		}else{
   			$this -> error("信息修改失败！");
   		}
   }
   public function delNews(){
   		header('Content-Type:text/html;charset=utf-8');
   		$news = M('stationnews');
   		$newsId = $_GET['newsId'];
   		if($news -> delete($newsId)){
   			$this -> redirect("News/news");
   		}else{
   			$this -> error("信息删除失败！");
   		}
   }
}