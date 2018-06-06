<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        $this->display('login');
    }
    public function test_excel(){
        $file="/Public/Uploads/excel/20170817.xlsx";

        $data=loadExcelContent($file);
        var_dump($data);
    }
    public function get_ip(){
        // $_SERVER=$HTTP_SERVER_VARS;
        //error_reporting (E_ERROR | E_WARNING | E_PARSE);
        if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif ($_SERVER["HTTP_CLIENT_IP"]) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif ($_SERVER["REMOTE_ADDR"]) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "Unknown";
        }
        return bindec(decbin(ip2long($ip)));
    }
    public function check_login(){
    	$check=M("glyadmins");
        //$_POST['isdeleted']='0';
        /*存用户登录的session*/
//        $user=array('uname'=>'admin','upwd'=>'4297f44b13955235245b2497399d7a93');
//        $data=$check->where($user)->find();
        $data=$check->where($_POST)->find();
    	if(!empty($data)){
            if($data['isdeleted']==0){
                unset($data['upwd']);
    		    session_start();
    		    $_SESSION['admin']=$data;
//                if($data['levelId']==1){
//                    $_SESSION['admin']['power']=1;
//                }else{
//                    $power=M('relation_level_power');
//                    $list=$power->where('levelId='.$data['levelId'])
//                                     ->field('powerId')
//                                     ->select();
//                    $powerList=array();
//                    foreach($list as $k=>$v){
//                        $powerList[]=$v['powerId'];
//                    }//获取权限列表
//                    $_SESSION['admin']['power']=$powerList;
//                }
                $loginCount=M('logincount');
                $loginData=$loginCount->where('adminId='.$data['adminId'])->find();
                unset($loginData['upwd']);
                cookie('loginData',$loginData,120);
                $now['lastTime']=time();//管理员登录信息统计
                $now['loginNum']=$loginData['loginNum']+1;
                $now['lastIP']=$this->get_ip();
                $loginCount->where('adminId='.$data['adminId'])
                           ->data($now)
                           ->save();
    		    echo 0;
            }else{
                echo 2;
            }
    	}else{
    		echo 1;
    	}
    }
}
