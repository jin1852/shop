<?php
namespace Admin\Controller;
use Think\Controller;
class LogoutController extends Controller {
    public function logout(){
    	session_start();
    	$_SESSION['admin']=array();
    	$this->display('Index/login');
    }
}
