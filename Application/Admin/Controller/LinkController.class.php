<?php
namespace Admin\Controller;
use Think\Controller;
class LinkController extends CommonController {

    private $name='友情链接';

    public function _initialize(){
        parent::_initialize();
        $this->check_super();
    }

	//查询所有友情链接
	public function link() {
        $linkname=I('linkname');
        $where=1;
        if(!empty($linkname)){
            $where.=" and linkname LIKE '%".$linkname."%'";
        }
        $count=$this->get_table('Links')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show();
		$list = $this->get_table('Links')->where($where)->order('linkId desc')->limit($page->firstRow.','.$page->listRows)->select();
        $this->title=$this->$name;
		$this->list=$list;
        $this->page=$show;
		$this->display('Link/link');
	}

	//友情链接添加页
	public function addLink() {
        $this->title=$this->$name;
		$this->display('Link/addLink');
	}

	//友情链接修改页
	public function editLink() {
		$data = I('param.');
		$list = $this->get_table('Links')->where($data)->find();
        $this->title=$this->$name;
		$this->list=$list;
		$this->display('Link/editLink');
	}


	public function insertLink() {
		$data = I('param.');
		$link = $this->get_table('Links')->data($data)->add();
		if($link) {
			$this->success('添加成功',U('Link/link'),3);
		}else{
			$this->error('添加失败',U('Link/link'));
		}
	}


	//修改
	public function updateLink() {
		$data = I('param.');
		$list = $this->get_table('Links')->data($data)->save();
		if($list) {
			$this->redirect('Link/Link');
		}else{
			$this->error('修改失败',U('Link/editLink'));
		}
	}

    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1)){
            $data['linkId']=$id;
            $data['status']=$status;
            $result = M('Links')->save($data);
            if($result){
                $this->success('操作成功',U('Link/link'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }
    //end
}
