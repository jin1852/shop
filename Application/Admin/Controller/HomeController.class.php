<?php
namespace Admin\Controller;
use Think\Controller;
class HomeController extends CommonController {
    public function index(){
    	$statistic=M('statistic');
    	$nowTime=time();
    	$where['yearId']=Date('Y',$nowTime);
    	$where['monthId']=Date('n',$nowTime);
    	/*取出统计信息*/
    	$staData=$statistic->where($where)
    			  ->field('saleTotal,sumMoney,dayId')
    			  ->order('dayId asc')
    			  ->select();
    	$dayNums=date('t',$nowTime);
        $dayStr=Date('Y-m-d',$nowTime);
    	//var_dump($staData);
    	$saleTotal=array();
    	$sumMoney=array();
    	$moneySum='';
    	$saleSum='';
    	foreach($staData as $k=>$v){
    		$total=array();
    		$money=array();
    		$total[]=$v['dayId'];
    		$total[]=$v['saleTotal'];
    		$money[]=$v['dayId'];
    		$money[]=$v['sumMoney'];
    		$saleTotal[]=$total;
    		$sumMoney[]=$money;
    		$moneySum+=$v['sumMoney'];
    		$saleSum+=$v['saleTotal'];
    	}
        /*以json格式返回数据*/
    	$totalJson=json_encode($saleTotal);
    	$moneyJson=json_encode($sumMoney);
    	//var_dump($totalJson);
    	//var_dump($moneyJson);
    	/*取出登录信息*/
    	$loginData=cookie('loginData');
    	$lastTime=($loginData['lastTime']==0)?'您的第一次啊':Date('Y-m-d',$loginData['lastTime']);
    	$loginNum=$loginData['loginNum']+1;
        $loginIP=$loginData['lastIP'];
        $this->assign('dayStr',$dayStr);
    	$this->assign('dayNums',$dayNums);
    	$this->assign('totalJson',$totalJson);
    	$this->assign('moneyJson',$moneyJson);
        $this->loginIP=long2ip($loginIP);
    	$this->assign('moneySum',$moneySum);
    	$this->assign('saleSum',$saleSum);
    	$this->assign('lastTime',$lastTime);
    	$this->assign('loginNum',$loginNum);

    	$this->display('Index/index');
    }
}