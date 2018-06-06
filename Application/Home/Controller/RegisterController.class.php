<?php
	namespace Home\Controller;
	use Think\Controller;
	class RegisterController extends Controller{
		function register(){
			$this->display("register");
		}
		//验证邮箱，用户名是否唯一
		function check_name_email(){
			$users=M('users');
			$r=$users->where($_POST)->find();
			if(!empty($r)){
				echo 1;
			}else{
				echo 0;
			}
		}
		function get_code(){
			$config=array(
					'fontSize'	=>30,
					'length'    =>4,
					'useNoise'  =>false,
				);
			$Verify= new \Think\Verify($config);
			$Verify->entry();
		}
		function check_code(){
			$code=$_POST['code'];
			$id='';
			$Verify=new \Think\Verify();
			echo $Verify->check($code,$id);
		}
		//对注册成功的数据进行入库
		function do_register(){
			$user=M('users');
			if(!isset($_POST)){
				return;
			}
			$_POST['nickName']=$_POST['uname'];
			$user->create($_POST);
			$r=$user->add();
			if($r>0){
				//header("window.location.href=/jd/index");
				$_SESSION['user']=$user->where("userId=".$r)
									   ->field('userId,uname,upwd,nickName,usertel,useremail')
									   ->find();
				echo 1;
			}else{
				echo 0;
			}
		}
	}