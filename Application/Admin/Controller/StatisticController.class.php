<?php
namespace Admin\Controller;
use Think\Controller;
class StatisticController extends Controller {
    public function index(){
        ignore_user_abort();
        set_time_limit(0);
        $interval=400;//定时
       //do{
        	$staData=array();
	        $orders=M('orders');
	        $timeNow=time();
	        $timeLast=time()-24*3600;//统计过去24小时数据
	        $where['orderTime']=array('between',array($timeLast,$timeNow));
	        $staData=$orders->where($where)
	        				->join('left join jd_details on jd_orders.orderId=jd_details.orderId')
	        				->field('jd_details.amount,jd_details.price')
	        				->select();
	        //echo $orders->getlastsql();
	        /*数据处理*/
	       	$totalNum=0;
	       	$totalMoney=0;
	        foreach($staData as $k=>$v){
	        	$totalNum+=$v['amount'];
	        	$totalMoney+=$v['amount']*$v['price'];
	        }
	        $insertData=array();
	        $insertData['saleTotal']=$totalNum;
	        $insertData['sumMoney']=$totalMoney;
	        $insertData['yearId']=Date('Y',$timeNow);
	        $insertData['monthId']=Date('n',$timeNow);
	        $insertData['dayId']=Date('j',$timeNow);
	        $insertData['addTime']=$timeNow;
	        /*入库*/
	        $Statistic=M('statistic');
	        $Statistic->create($insertData);
	        $Statistic->add();
	        //echo '<hr/>';
	        //echo $Statistic->getlastsql();
	       //sleep($interval);
       //}while(true);
    }
}
