<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
        $this->display('login');
    }
    public function logout(){
    	session_start();
    	$_SESSION=array();
    	$this->display('Index/login');
    }
}
