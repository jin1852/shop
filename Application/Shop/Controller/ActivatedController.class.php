<?php
namespace Shop\Controller;
use Shop\Model\UserModel;
use Think\Controller;
class ActivatedController extends BaseController{
    public function check_user(){
		$user_id=I('get.user_id');
		$token=I('get.token');
		$time=I('get.time');
		var_dump($user_id);
		var_dump($token);
		var_dump($time);
		if($user_id>0 && $token && $time>0){
			$now=time();
			$check=$now-$time;
			if($check>0 && $check<3600){
				$User=new UserModel();
				$user=$User->check_user($user_id);
				var_dump($user);
				if($user){
					$create=md5($user['userId'].$user['uname'].$user['upwd'].$user['useremail'].$time);
					if($token==$create){
						$res=$User->activated_user($user_id);
						if($res){
							echo '激活成功';
						}else{
							echo '激活失败';
						}
					}else{
						echo 'token错误';
					}
				}else{
					echo '非法操作';
				}
			}else{
				echo '已过期';
			}
		}else{
			echo '参数异常，无法激活';
		}
	}


    //end
}