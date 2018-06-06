<?php
namespace Home\Controller;
use Think\Controller;
class SurveyController extends Controller {
    public function index(){
    	$this->display('Survey/index');
    }
    /*调查统计数据入库*/
    public function do_survey(){
    	$survey=M('survey');
    	$status=true;
    	foreach($_POST as $k=>$v){
    		if($k=='suggest'){
    			continue;
    		}else{
    			if(empty($v)){
    				$status=false;
    				break;
    			}
    		}
    	}
    	if($status){
    		$survey->create($_POST);
    		$survey->add();
    	}
        $this->display('Survey/finish');
    }
}
