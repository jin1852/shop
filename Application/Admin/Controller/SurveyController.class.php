<?php
namespace Admin\Controller;
use Think\Controller;
class SurveyController extends CommonController {
	private $myd=array(
			'0'=>'非常满意',
			'1'=>'满意',
			'2'=>'一般',
			'3'=>'不满意',
			'4'=>'非常不满意',
		);
	private $sex=array(
			'1'=>'女',
			'2'=>'男',
		);
	private $age=array(
			'1'=>'17岁及以下',
			'2'=>'18-23岁',
			'3'=>'24-30岁',
			'4'=>'31-35岁',
			'5'=>'36岁及以上'
		);
	private $learn=array(
			'1'=>'高中/中专/技校及以下',
			'2'=>'大学专科',
			'3'=>'大学本科',
			'4'=>'硕士及以上'
		);
	private $work=array(
			'1'=>'计算机/互联网/通信/电子',
			'2'=>'会计/金融/银行/保险',
			'3'=>'贸易/消费/制造/营运',
			'4'=>'制药/医疗',
			'5'=>'广告/媒体',
			'6'=>'房地产/建筑',
			'7'=>'专业服务/教育/培训',
			'8'=>'服务业',
			'9'=>'物流/运输',
			'0'=>'能源/原材料',
			'1'=>'政府/非盈利机构',
			'2'=>'其他',
		);
	private $province=array(
			'1'=>'安徽',
			'2'=>'北京',
			'3'=>'重庆',
			'4'=>'福建',
			'5'=>'甘肃',
			'6'=>'广东',
			'7'=>'广西',
			'8'=>'贵州',
			'9'=>'海南',
			'10'=>'河北',
			'11'=>'河南',
			'12'=>'黑龙江',
			'13'=>'湖北',
			'14'=>'湖南',
			'15'=>'吉林',
			'16'=>'江苏',
			'17'=>'江西',
			'18'=>'辽宁',
			'19'=>'内蒙古',
			'20'=>'宁夏',
			'21'=>'青海',
			'22'=>'山东',
			'23'=>'山西',
			'24'=>'陕西',
			'25'=>'上海',
			'26'=>'四川',
			'27'=>'天津',
			'28'=>'西藏',
			'29'=>'新疆',
			'30'=>'云南',
			'31'=>'浙江',
		);
	/*变量值转化*/
	function index(){
		$survey=M('survey');
		$surveyData=array();
		$data=$survey->select();
		foreach($data as $k=>$v){
			$data0=array();
			$data0['sId']=$v['sId'];
			$data0['suggest']=$v['suggest'];
			$data0['myd']=$this->myd[$v['myd']];
			$data0['sex']=$this->sex[$v['sex']];
			$data0['age']=$this->age[$v['age']];
			$data0['learn']=$this->learn[$v['learn']];
			$data0['work']=$this->work[$v['work']];
			$data0['province']=$this->province[$v['province']];
			$surveyData[]=$data0;
		}
		$this->assign('surveyData',$surveyData);
    	$this->display('Survey/index');
	}
	/*删除*/
	function delete(){
		$survey=M('survey');
		$survey->where('sId='.$_GET['sId'])->delete();
		$this->success('删除成功');
	}
}
