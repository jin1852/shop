<?php
class Statistic{
	private $link=null;
    public function index(){
        ignore_user_abort();
        set_time_limit(0);
        $interval=300;
       do{
       		$this->connect();
        	$staData=array();
	        $timeNow=time();
	        $timeLast=time()-24*3600;

	        $sql1='SELECT jd_details.amount,jd_details.price FROM jd_orders left join jd_details on jd_orders.orderId=jd_details.orderId WHERE orderTime BETWEEN '.$timeLast.' AND '.$timeNow ;
	        $result=mysql_query($sql1,$this->link);
	        //echo $sql1;
	        while ($result&&$data=mysql_fetch_assoc($result)) {
	        	$staData[]=$data;
	        }
	       	$totalNum=0;
	       	$totalMoney=0;
	       	//var_dump($staData);
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
	        $dataStr=implode(',',$insertData);

	        $sql2='INSERT INTO jd_statistic (saleTotal,sumMoney,yearId,monthId,dayId,addTime) VALUES ( '.$dataStr.' )';
	        mysql_query($sql2);
	        mysql_close($this->link);
	      sleep($interval);
       }while(true);
    }
    private function connect(){
    	$this->link=mysql_connect('localhost','root','') or die('连接失败');
    	mysql_select_db('jd',$this->link);
    	mysql_set_charset('utf8',$this->link);
    }
}

$statistic=new Statistic();
$statistic->index();
