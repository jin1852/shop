<?php
namespace Home\Controller;
use Think\Controller;
class BigimgController extends Controller {
	//查看大图页面
	public function bigimg(){
		header("Content-type:text/html;charset=utf-8");
    /*获取橱窗导航栏的数据*/
        /*memCache设定与存取*/
//        S(array(
//            'type'=>'memcache',
//            'host'=>'192.168.150.45',
//            'port'=>'11211',
//            'prefix'=>'',
//            'expire'=>300
//            )
//        );
        
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


		$id = $_GET['id'];
		$prod = M('prods');
		$count = $prod -> where('jd_prods.prodId = '.$id)
					   -> join('left join jd_prodimg ON jd_prods.prodId = jd_prodimg.prodId')
					   -> count();
		$pagesize = 4;
        $page = new \Think\Page($count,$pagesize);
        $page -> setConfig('theme','%FIRST% %UP_PAGE% %NOW_PAGE%/%HEADER% 页 %LINK_PAGE% %DOWN_PAGE% %END%');
        $page -> setConfig('header','<span class="rows">%TOTAL_PAGE%</span>');
        $page -> setConfig('prev','<span> < </span>');
        $page -> setConfig('next','<span> > </span>');
        $show = $page -> show();
		//echo $count;
		$arr = $prod -> where('jd_prods.prodId = '.$id)
					 -> join('left join jd_prodimg ON jd_prods.prodId = jd_prodimg.prodId')
					 -> field('image,price2')
					 ->limit($page->firstRow.','.$page->listRows)
					 -> select();
		$this -> assign('bigimg',$arr);
		$this -> assign('count',$count);
		$this -> assign('show',$show);


		$this -> display();

	}
}
