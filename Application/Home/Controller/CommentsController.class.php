<?php
namespace Home\Controller;
use Think\Controller;
class CommentsController extends Controller{
	public function index(){
		$this -> display();
	}
	public function comments(){
		/*商品信息展示处理*/
		header("Content-type:text/html;charset=utf-8");
		$id = $_GET['id']?$_GET['id']:1;
		$db = M('prods');
		$arr = $db -> table('jd_prods as P,jd_comments as C')
				   -> where('P.prodId=C.cId and prodId = '.$id)
				   -> field('img,prodInfo,price2,Gcode,count(cId),prodId')
				   -> select();
		$this -> assign('arr',$arr);
		/*其他谈论话题*/
		$db1 = M('comments');
		$arr1 = $db1 -> table('jd_comments as C,jd_prods as P')
				  -> where('C.cId = P.prodId')
				  -> field('simimg,prodName')
				  -> group('C.cId')
				  -> having('count(C.cId)')
				  -> order('count(C.cId) desc')
				  -> limit(1)
				  -> select();
		$this -> assign('arr1',$arr1);
		/*同类商品推荐*/
		$products = M('products');
		$arr2 = $products -> where('prodId = '.$id)
                          -> field('proTypeId,brandId')
                          -> select();
        $proTypeId = 0;
        $brandId = 0;
        foreach($arr2 as $v){
            $proTypeId = $v['proTypeId'];
            $brandId = $v['brandId'];
        }
		$arr3 = $products -> where('jd_products.proTypeId = '.$proTypeId)
                          -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
                          -> limit(6)
                          -> select();
        $this -> assign('arr3',$arr3);
		/*搭配组合*/
		$arr4 = $products -> where('jd_products.proTypeId = '.$proTypeId)
					-> join('left join jd_protype ON jd_products.proTypeId = jd_protype.proTypeId')
					-> field('proPid')
					-> select();
		$pId = 0;
		foreach($arr4 as $v){
			$pId = $v['proPid'];
		}
		$protype = M('protype');
		$arr5 = $protype -> where('proTypeId = '.$pId) -> field('proPid') -> select();
		$pId1 = 0;
		foreach($arr5 as $vo){
			$pId1 = $vo['proPid'];
		}
		$arr6 = $protype -> where('proTypeId = '.$pId1) -> field('proTypeId,proPid') -> select();
		$pId2 = 0;
		foreach($arr6 as $v){
			$pId2 = $v['proPid'];
		}
		$arr7 = $protype -> where('proPid = '.$pId2.' and proTypeId != '.$pId1) ->field('proTypeId') -> select();
		$arr10 = array();
		foreach($arr7 as $v){
			$pId3 = $v['proTypeId'];
			$arr8 = $protype -> where('proPid = '.$pId3) -> field('proTypeId') -> select();
			foreach ($arr8 as $value) {
				$arr9 = $products -> where('jd_products.proTypeId =  '.$value['proTypeId'])
								  -> join('left join jd_prods ON jd_products.prodId = jd_prods.prodId')
								  -> limit(6)
								  -> select();
				$arr10[] =  $arr9;
			}
		}
		$this -> assign('arr10',$arr10);
		/*按销量排序*/
		/*$db2 = M('detail');
		$arr2 = $db2 -> /*table('jd_details as D,jd_prods as P,jd_products as Pd')
					 -> where('D.productId = PD.productId and PD.prodId = P.prodId')
					 -> group('D.productId')
					 -> having('count(D.amount)')
					 -> order('D.amount*count(D.amount) desc')
					 -> select();
		$this -> assign('$arr2',$arr2);*/
		$this -> display();
	}
	public function do_comments(){
		//添加商品评论处理
		$data = $_POST;
		$arr['uId'] = $_SESSION['user']['userId'];
		$arr['cId'] = $data['cId'];
		$arr['commentsdj'] = $data['commentsdj'];
		$arr['commentsBId'] = $data['commentsBId'];
		$arr['content'] = $data['content'];
		$arr['commentsTime'] = time();
		$arr['Gcode'] = $data['Gcode'];
		$arr['Scode'] = $data['Scode'];
		$arr['Tcode'] = $data['Tcode'];
		$arr['mjyx'] = $data['impression'];
		$db = M('comments');
		if($db -> add($arr)){
			$comments = M('comments');
			$commentsNum = $comments -> where('cId = '.$arr['cId'].' and is_delete = 0') -> count();
			$products = M('products');
			$data1['commentNum'] = $commentsNum;
			$products -> where('prodId = '.$arr['cId']) -> save($data1);
			if($products){
				$this -> redirect('Details/allComments?id='.$arr['cId']);
			}else{
				$this -> error('评论失败！');
			}
		}else{
			$this -> error('评论失败！');
		}
	}
}