<?php
namespace Home\Common;
function getNavList(){
	 $indexccdhl=M('indexccdhl');
	 $where['indexCCPid']=0;
     $navList=$indexccdhl->where($where)
      					  ->field('indexCC,indexCCDWZ')
      					  ->limit(11)
      					  ->select();
     return $navList;
}
function getCcdhData(){
      $indexccdhl=M('indexccdhl'); 

      $ccResult=$indexccdhl->join(' left join jd_cctptypes on jd_indexccdhl.indexCCId=jd_cctptypes.indexCCId')
      					   ->field('jd_indexccdhl.*,jd_cctptypes.cctptypeName')
      					   ->select();
 	/*对数组进行结构化处理*/   
      $handResult=order($ccResult);
      $lastData=array();

      $protype=M('protype');

      foreach($handResult as $k1=>$v1){
      		$beforeTotal=array();
      		$afterTotal=array();
      		$checkId=array();
      		foreach ($v1['child'] as $k2=>$v2){
      			/*去除父ID相同则不进行二次查找*/
      			$idList=explode(',',$v2['cctptypeName']);
      			if(!in_array($idList[1], $checkId)){
      				array_push($checkId, $idList[1]);
	      			$typeResult2=$protype->where('proPid='.$idList[1])
	      								 ->select();
	      			$whereId=handleId($typeResult2);
	      			$typeResult3=$protype->where($whereId)
	      								 ->field('proName,proPid,proPath')
	      								 ->select();
	      			$typeResult=mergeType($typeResult2,$typeResult3);
	     			$afterTotal[]=$typeResult;
     			}
      			if(!empty($v2['indexCCDWZ'])){    				 
      				$currentValue['name']=$v2['indexCC'];
      				$currentValue['value']=$v2['indexCCDWZ'];
      			}else{
      				$currentValue['name']=$v2['indexCC'];
      				$currentValue['value']=$v2['cctptypeName'];
      			}
      			$beforeTotal[]=$currentValue;
      		}
      	$lastData[$k1][]=$beforeTotal;
      	$lastData[$k1][]=$afterTotal;
      }
   /*  echo '<pre>';
      print_r($lastData);
      echo '</pre>';*/
      return $lastData;
}
/**
	func order 对数组进行结构化处理
	@param $data 需要处理的分类型数据
	@param $indexCCpid 父节点等于当前值的数组才进行处理
*/
function order($data,$indexCCPid=0){
		$arr=array();
		foreach ($data as $v) {
			if($v['indexCCPid']==$indexCCPid){
				$v['child']=order($data,$v['indexCCId']);
				$arr[]=$v;
			}
		}
		return $arr;
}
/*加工proTypeId，并生产where条件*/
function handleId($data){
	$idList=array();
	foreach ($data as $key => $value) {
		$idList[]=$value['proTypeId'];
	}
	$whereId=array('proPid'=>array('in',$idList));
	return $whereId;
}
/*合并并生产浮动橱窗数据*/
function mergeType($data2,$data3){
	$typeResult=array();
	foreach ($data2 as $k2=> $v2) {
		foreach ($data3 as $k3 => $v3) {
			if($v3['proPid']==$v2['proTypeId']){
				$v2['child'][]=$v3;
			}
		}
		$typeResult[]=$v2;
	}
	return $typeResult;
}
