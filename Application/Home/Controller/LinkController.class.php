<?php
/*
	*前台友情链接展示页
*/
namespace Home\Controller;
use Think\Controller;
class LinkController extends Controller {

	//展示
	public function index() {
		
		//实例化链接表
		$link = M('Links');

		//查询满足要求的总记录数
		$count = $link->where('status=1 AND audit=1')->count();
	
		//实例化分页类，传入总条数和每页显示条数
		$page = new \Think\Page($count,10);

		// 进行分页数据查询 注意page方法的参数的前面部分是当前的页数使用 $_GET[p]获取
		$p = empty($_GET['p'])?1:$_GET['p'];
		$list = $link
			->where('status=1 AND audit=1')  //按显示和已审核
			->order('seriation')
			->page($p.',10')
			->select();	

		//分页样式进行定制
		$a = $page->setConfig('header',$count);
		$page->setConfig('prev', "上一页"); 
		$page->setConfig('next', '下一页');
		$page->setConfig('first', '首页'); 
		$page->setConfig('last', '尾页');
		
		//分页显示输出
		$show = $page->show();

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

		//分配变量
		$this->assign('list',$list);
		$this->assign('page',$show);

		//加载模板
		$this->display('Link/link');

	}


	//友情链接申请
	public function alink() {

		//获取数据
		$data = I('param.');
		
		//实例化链接表
		$link = M('Links');

		//添加
		$link->create($data);
		$list = $link->add();

		//判断
		if($list){
			$this->success('申请成功',U('Index/index'));
		}else{
			$this->error('申请失败,请再次申请',U('Link/index'));
		}


	}
}