<?php

namespace Shop\Model;
use Shop\Controller\EmailController;

class UserModel extends BaseModel{
    //配置数据表
    public $users = 'jd_users';  //用户主表
    public $user_shop_cart = 'jd_user_shop_cart'; //用户购物车表
    public $userhydj = 'jd_userhydj'; //用户会员等级表
    public $usergwxwdj = 'jd_usergwxwdj'; //用户购物行为等级表

    //按id查找用户
    //todo
    public function check_user($user_id){
        $data=M()->table($this->users)->where("userId=" . $user_id)->find();
        if($data) {
            return $data;
        }else{
            return array();
        }
    }

    //按用户名登陆
    public function user_login($username){
        $where['uname'] = $username;
        $where['useremail'] = $username;
        $where['usertel']=$username;
        $where['_logic'] = 'OR';
        $user = M()->table($this->users)->where($where)->find();
        if($user){
            return $user;
        }else{
            return array();
        }
    }

    //用户注册
    public function user_reg($uname,$upwd,$email){

        $data['uname']=$uname;
        $data['upwd']=md5($upwd);
        $data['useremail']=$email;
        $data['status']=2;
        $data['nickName'] = $data['uname'];
        $res=M()->table($this->users)->add($data);
        if($res) {
            return $res;
        }else{
            return '';
        }
    }

    //检测验证码
    //$code 是用户输入的
    public function checkCode($code) {
        //实例化验证码
        $Verify = new \Think\Verify();
        //判断验证码
        return $Verify->check($code);
    }

    //激活用户
    public function activated_user($user_id){
        $data=$this->check_user($user_id);
        $data['status']=1;
        $res=M()->table($this->users)->where("userId=".$user_id)->save($data);
        return $res;
    }

    //账户激活邮件发送内容
    public function email_content($user_id,$username,$pwd,$email){
        $time=time();
        $token = md5($user_id . $username . md5($pwd) . $email.$time);
        $url =$this->url().'/index.php/Activated/check_user/user_id/' . $user_id . '/token/' . $token . '/time/' . $time;
        $content = "<p>尊敬的用户：您好，请<a href=" . $url . ">点击这里</a>激活账户。(此连接5分钟内有效)</p>";
        $content .= "<p>您的注册信息如下：(请您妥善保管好自己的个人信息)</p>";
        $content .= "<p>账户：" . $username . "</p>";
        $content .= "<p>密码：" . $pwd . "</p>";
        $content .= "<p>邮箱：" . $email . "</p>";
        return $content;
    }

    //查找第三方登陆
    public function find_oauth_user($openid,$type){
        switch($type){
            case 'FB':
                $where='FB_openid = '.$openid;
                break;
            case 'TW':
                $where="TW_openid = ".$openid;
                break;
        }

        $list=M()->table($this->users)->where($where)->find();
        if($list){
            return $list;
        }else{
            return array();
        }
    }



    //第三方账户注册
    public function reg_with_oauth($user,$openid,$type){
        switch($type){
            case 'FB':
                $user['FB_openid']=$openid;
                break;
            case 'TW':
                $user['TW_openid']=$openid;
                break;
        }
        $result=M()->table($this->users)->add($user);
        return $result;
    }

    public function check_email($email){
        $user = M()->table($this->users)->where("useremail='".$email."' and status=1")->cache(true,$this->cache_time)->find();
        return $user;
    }

    public function reset_pwd($user,$pwd){
        $user['upwd'] = md5($pwd);
        return M()->table($this->users)->where('userId='.$user['userId'])->save($user);
    }

    public function check_reviewed($user){
        if($user['reviewed']==1){
            return true;
        }else{
            return false;
        }
    }

    public function following_series_list($uid){
        $sql = "select i.* from jd_users_favourite_series AS ufs LEFT JOIN jd_imageshow AS i ON i.id = ufs.series_id where ufs.userId = ".$uid." and i.sorts=2 and i.status=1 and ufs.status=1 order by ufs.time";
        $data = M()->query($sql);
        if (!$data) $data = array();
        if ($data) $data = $this->explain_pic_list($data);
        return $data;
    }

    public function un_following_series_list($list){
        $id = 0;
        if ($list) {
            foreach ($list as &$one) {
                if ($id > 0) {
                    $id .= ',' . $one['id'];
                } else {
                    $id = $one['id'];
                }
            }
        }
        $data = M('imageshow')->where('id not in (' . $id . ') and sorts=2 and status=1')->limit(3)->order('rand()')->select();
        if (!$data) $data = array();
        if ($data) $data = $this->explain_pic_list($data);
        return $data;
    }

    public function getUser($uid){
        $data = M('users')->find($uid);
        if(!$data) $data = array();
        return $data;
    }
//end
  }