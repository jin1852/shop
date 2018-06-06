<?php
/*
	*后台站内信息管理页
*/
namespace Admin\Controller;
use Think\Controller;
header('Content-type:text/html;charset=utf-8');
class MessageController extends CommonController {
	//查询所有的信息
	public function messageAll() {

		//实例化站内信息表对象
		$message = M('Messages');

		//查询站内信息
		$list = $message
			->field('jd_users.uname as receiver,jd_glyadmins.uname,jd_messages.*')
			->join('__USERS__ on __USERS__.userId = __MESSAGES__.userId')
			->join('__GLYADMINS__ on __GLYADMINS__.adminId = __MESSAGES__.adminId')
			->select();

		//分配变量
		$this->assign('list',$list);

		//加载模板
		$this->display('Message/message');
	}



	//添加站内信息
	public function addMessage() {

		//实力化用户表
		$user = M('Users');

		//查询
		$list = $user->field('userId,uname')->select();

		//分配变量
		$this->assign('list',$list);

		$this->assign('adminUser',$_SESSION['admin']);

		//加载模板
		$this->display('Message/addMessage');
	}


	//发送站内信息
	public function sendMessage() {

		//接收数据
		$data['adminId'] = $_SESSION['admin']['adminId'];
		$data['userId'] = I('param.userId');
		$data['title'] = I('param.title');
		$data['message'] = I('param.message');
		$data['sendTime'] = time();

		//实例化站内信息表对象
		$message = M('Messages');
		
		//查询站内信息
		if($data['userId'] == -1) {

			//发送给全部用户
			//实力化用户表
			$user = M('Users');

			$userList = $user->field('userId')->select();

			foreach($userList as $k=>$v) {
				
				$data['userId'] = $v['userId'];
				$id = $message->data($data)->add();
			}

		}else{

			$id = $message->data($data)->add();
		}

		//判断是否成功
		if($id) {

			$this->success("添加成功",U('Message/messageAll'));
		}else{

			$this->error("添加失败",U('Message/addMessage'));
		}

	}


	//查询订单详情
	public function detail() {

		//获取提交的数据
		$data = I('param.');

		//实例化站内信息表对象
		$message = M('Messages');

		//展示
		$list = $message
			->field('jd_messages.*,jd_users.uname')
			->where('messageId='.$data['messageId'])
			->join('__USERS__ ON __USERS__.userId = __MESSAGES__.userId')
			->find();

		//分配变量
		$this->assign('adminUser',$_SESSION['admin']);
		$this->assign('list',$list);

		//加载模板
		$this->display('Message/detail');
	}



	//删除信息
	public function delMessage() {

		//获取提交的数据
		$data = I('param.');

		//实例化站内信息表对象
		$message = M('Messages');
		
		//查询站内信息
		$list = $message->where('messageId='.$data['messageId'])->delete();

		//判断是否成功
		if($list !== false) {

			$this->success("删除成功",U('Message/messageAll'));
		}else{

			$this->error("删除失败",U('Message/messageAll'));
		}

	}


}