<?php
namespace Home\Controller;
use Think\Controller;
header('content-type:text/html;charset=utf-8');
class LoginController extends Controller {


	public function index() {

		$this->display('login');
    }

    public function logoutHeader(){
    	$_SESSION['user']=array();
    	header('Location:'.$_SERVER['HTTP_REFERER']);
    }

    //登陆验证
    public function doLogin() {

    	$data = I('post.');
  
    	//检查验证码
    	$code = $this->checkCode($data['code']);

    	if(!$code) {

    		$this->ajaxReturn(3);
    	}

  		//实例化用户表
  		$user = M('Users');

  		$userLogin = array();

  		//查询
  		$condition['uname'] = $data['uname'];
		  $condition['useremail'] = $data['uname'];
		  $condition['_logic'] = 'OR';
  		$userLogin = $user->where($condition)->find();

  		//判断用户名或邮箱是否存在
  		if(empty($userLogin)) {

  			$this->ajaxReturn(1);
  		}
  
  		//判断密码是否正确
  		if($userLogin['upwd'] != md5(trim($data['upwd']))) {

  			$this->ajaxReturn(2);
  		}
 	
  		//都成功了放入session
  		$_SESSION['user'] = $userLogin;
  		return $this->ajaxReturn(0);

    }

    //生成验证码
    public function setCode() {

    	//设置验证码
		$config=array(
 
			'fontSize' => 16,// 验证码字体大小
			'length' => 4,// 验证码位数
			'useCurve' => true, // 是否画混淆曲线
			'useNoise' => false, // 关闭验证码杂点
    		'reset' => false, // 验证成功后是否重置
    	);

    	//实例化验证码类
    	$Verify = new \Think\Verify($config);
    	$Verify->entry();

    }


    //检测验证码
    //$code 是用户输入的
    public function checkCode($code) {

    	//实例化验证码
    	$Verify = new \Think\Verify();

    	//判断验证码
    	return $Verify->check($code);

    }


}
