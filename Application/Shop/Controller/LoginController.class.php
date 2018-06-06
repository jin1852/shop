<?php
namespace Shop\Controller;
use Abraham\TwitterOAuth\TwitterOAuth;
use Admin\Controller\EmailController;
use Facebook\Facebook;
use Shop\Controller\Cookie;
use Shop\Model\Newsletter;
use Shop\Model\NewsletterModel;
use Shop\Model\UserModel;
use Think\Controller;
header('content-type:text/html;charset=utf-8');
//登陆类
class LoginController extends BaseController {
	public function _initialize(){
		parent::_initialize();
	}

	//登陆界面
	public function index() {
		$this->assign('head_title','Login');
		$this->display('User/login');
    }

	//登出
    public function logout(){
    	$_SESSION['user']=array();
    	//header('Location:'.$_SERVER['HTTP_REFERER']);
		session_destroy();
		$user=session('user');
		if($user){
			$this->list_json_result(-1,'fail','');
		}else{
			$this->list_json_result(1,'success','');
		}
    }

    //登陆验证
    public function do_login() {
		$uname=I("post.uname");
		$upwd=I("post.upwd");
		if($uname && $upwd){
            if(preg_match($this->E,$uname)){
				//实例化UserModel
                $User = new UserModel();
                $userLogin = $User->user_login($uname);
                //判断用户名或邮箱是否存在
                if ($userLogin) {
                    //判断密码是否正确
                    if ($userLogin['upwd'] == md5(trim($upwd))) {
                        $userLogin['login_count'] += 1;
                        $userLogin['login_ip'] = $this->get_ip();
                        $userLogin['login_time'] = time();
                        $re = $this->login_update($userLogin);
                        if ($re) {
                            if($User->check_reviewed($userLogin)) {
                                $user = $this->explain_user($userLogin);
                                //都成功了放入session
                                session('user', $user);
                                $this->list_json_result(1, 'Login successful', $user);
                            }else{
                                $this->list_json_result(2, 'This account is under review','');
                            }
                        } else {
                            $this->list_json_result(0, 'login fail', '');
                        }
                    } else {
                        //密码错误
                        $this->list_json_result(-1, 'Please enter a valid email address or password', '');
                    }
                } else {
                    //账户或者邮箱不存在
                    $this->list_json_result(-2, 'Please enter a valid email address', '');
                }
            } else {
                //邮箱格式错误
                $this->list_json_result(-3, 'Please enter a valid email address', '');
            }
		}else{
			//参数不全
			$this->list_json_result(-4,'post error','');
		}
    }

    private function login_update($user){
        return M()->table('jd_users')->where('userId='.$user['userId'])->save($user);
    }

	//生成验证码
    public function setCode() {
		ob_clean();
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

	public function oauth_do($openid,$type){
		//实例化用户模型
		$User=new UserModel();
		//通过openid 和 类型 查找用户
		$list=$User->find_oauth_user($openid,$type);
		if($list){
			//如果 账户存在时， 返回登陆成功， 记录 session (用户)，跳转首页
            if($User->check_reviewed($list)) {
                $user = $this->explain_user($list);
                session('user', $user);
                $this->alert_value('Login successful');
            }else{
                $this->alert_value('this account is under reviewed');
            }
		}else{
			//如果 账户 不存在 ，记录 openid 和 type,跳转至注册页
			echo "<script>alert('No this account');</script>";
			$Cookie=new Cookie();
			$Cookie->set('openid',$openid);
			$Cookie->set('type',$type);
			$this->redirect('Register/index');
		}
	}

	private function alert_value($value){
        $url = "http://www.galacasa.com/";
        echo "<script>alert('$value');</script>";
        echo "<script>window.location.href='$url';</script>";
    }

	public function forget(){
	    $email=I('post.email');
        if($email){
            if(preg_match($this->E,$email)) {
                $User = new UserModel();
                $has = $User->check_email($email);
                if ($has) {
                    $Email = new EmailController();
                    $password = $User->create_random_str(8);
                    $title = 'Gala reset password';
                    $content = 'your new password is : ' . $password;
                    $res = $Email->send_email($email, '', $title, $content);
                    if ($res == 1) {
                        $re = $User->reset_pwd($has, $password);
                        if ($re) {
                            $this->list_json_result(1, 'We will send a new password to your email within two working days', '');
                        } else {
                            $this->list_json_result(-1, 'reset fail', '');
                        }
                    } else {
                        $this->list_json_result(-2, 'send fail', '');
                    }
                } else {
                    $this->list_json_result(-2, 'Please enter a valid email address', '');
                }
            }else{
                $this->list_json_result(-3,'Please enter a valid email address','');
            }
        }else{
            $this->list_json_result(-10,'Please enter a valid email address','');
        }
    }

    public function newsletter(){
        $email=I('post.email');
        if($email){
            if(preg_match($this->E,$email)) {
                $New = new NewsletterModel();
                $result = $New->newsletter($email);
                if ($result){
                    $this->list_json_result(1,'Add successful','');
                }else{
                    $this->list_json_result(-1,'Add fail','');
                }
            }else{
                $this->list_json_result(-3,'Please enter a valid email address','');
            }
        }else{
            $this->list_json_result(-10,'Please enter a valid email address','');
        }
    }
//end
}


