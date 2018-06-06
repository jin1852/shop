<?php
namespace Home\Controller;
use Think\Controller;
class CompareController extends Controller{
    function compare(){
    	header('content-type:text/html;charset=utf-8');
    	/*cookie值的设定*/
	  	$prodIdCookie=array();
    	$pcookie=cookie('prodIdCookie');
    	if(!empty($pcookie)){
    		$prodIdCookie=$pcookie;
    	}
    	$num=count($prodIdCookie);
    	$prodId=$_GET['prodId'];
        //限定对比数量，小于则4加入cookie,并组成字串返回
    	 if($num<4&&!empty($prodId)){

    	 	array_push($prodIdCookie,$prodId);
    		cookie('prodIdCookie',$prodIdCookie,10000);

	    	$model=M('prods');
	    	$data=$model->where($_GET)->find();
	    	$str1='<a href='.U('Details/details',array('id'=>$data['prodId'])).' target="_blank"><img width="50" height="50" src="'.__ROOT__.'/Public/Uploads/'.$data['simimg'].'"></a>';
	    	$str2='<a class="diff-item-name" href="'.U('Details/details',array('id'=>$data['prodId'])).'" target="_blank">'.$data['prodInfo'].'</a><span class="p-price"><strong class="J-p-1093625">￥'.$data['price1'].'</strong><a class="del-comp-item" style="visibility:hidden;">删除</a></span>';
	    	$returnData['str1']=$str1;
	    	$returnData['str2']=$str2;
	    	$returnData['prodId']=$data['prodId'];
	    	echo json_encode($returnData);
    	 }else{
    	 	echo 1;
    	 }
    }
    function removeCompare(){
    	$prodId=$_GET['prodId'];
    	echo $this->fuck($prodId);

    }
    /*删除对比项所用的函数,更据不同状态值删除*/
    private function fuck($prodId){
    	$prodIdCookie=array();
    	$prodIdCookie=cookie('prodIdCookie');
    	if(empty($prodIdCookie)){
    		if($prodId=='all'){
    			return -1;
    		}else{
    			return $prodId;
    		} 
    	}
    	if($prodId=='all'){
    		cookie('prodIdCookie',null);
    		return 0;
    	}else{
    		$now=array();
    		foreach($prodIdCookie as $k=>$v){
    			if($v!=$prodId){
    				array_push($now,$v);
    			}
    		}
    		cookie('prodIdCookie',null);
    		cookie('prodIdCookie',$now,10000);//删除后重新如cookie
    		return $prodId;
    	}
    }
    public function compareShow(){

        /*memCache设定与存取----全部商品分类导航,*/
//        S(array(
//            'type'=>'memcache',
//            'host'=>'192.168.150.45',
//            'port'=>'11211',
//            'prefix'=>'',
//            'expire'=>300
//            )
//        );
        $memResult=S('indexCcData');
        if(!$memResult){
            $result=\Home\Common\getCcdhData();
            S('indexCcData',$result);
        }else{
            $result=$memResult;
        }
        $this->assign('ccdhData',$result);
        $navList=\Home\Common\getNavList();
        $this->assign('navList',$navList);
       /********************************************/ 
        $prodIdList=cookie('prodIdCookie');
        $where['jd_prods.prodId']=array('in',$prodIdList);
        $model=M('products');
        /*获取对比所需要的数据集合*/
        $data=$model->where($where)
        			->field('jd_brands.brandName,jd_sizes.sizeName,jd_colors.colorName,jd_prods.price1,jd_prods.prodId,jd_prods.prodInfo,jd_prods.img')
        			->join('left join jd_brands on jd_products.brandId=jd_brands.brandId')
        			->join('left join jd_sizes  on jd_products.sizeId=jd_sizes.sizeId')
        			->join('left join jd_colors on jd_products.colorId=jd_colors.colorId')
        			->join('left join jd_prods on jd_products.prodId=jd_prods.prodId')
        			->select();
        /*echo $model->getlastsql();
        var_dump($data);*/
        $num=count($data);
        $num=4-$num;
        $this->assign('data',$data);
        $this->assign('num',$num);
    	$this->display('Compare/compareshow');
    }
    /*列表也弹层所需要的相应的数据的处理*/
    public function careProds(){
        $username=$_POST['uname'];
        $status=preg_match('/^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/',$username);
        if($status==0){
            $where['uname']=$username;
        }else{
            $where['useremail']=$username;
        }
        $userpass=$_POST['upass'];
        $userpass=md5($userpass);
    
        $model=M('users');
        $rdata=$model->where($where)
                     ->field('userId,uname,upwd,nickName,usertel,useremail')
                     ->find();
        if(!empty($rdata)){
            if($rdata['upwd']==$userpass){
                $_SESSION['user']=$rdata;
                echo 0;
            }else{
                echo 1;
            }
        }else{
            if($status==0){
                echo 2;
            }else{
                echo 3;
            }
        }
    }
    /*将关注的相关数据入库*/
    public function do_care(){
        $prodId=$_POST['prodId'];
        $model=M('users');
        $userInfo=session('user');
        $userId['userId']=$userInfo['userId'];
        $userCare=$model->where($userId)->field('userCare')->find();
        if(empty($userCare['userCare'])){
            $userNewCare['userCare']=$prodId;
            $model->data($userNewCare)->where($userId)->save();
            echo 0;
        }else{
            $userCareList=explode(',',$userCare['userCare']);
            $inStatus=in_array($prodId, $userCareList);
            if($inStatus){
                echo 1;
            }else{
                $userNewCare['userCare']=$userCare['userCare'].','.$prodId;
                $model->data($userNewCare)->where($userId)->save();
                echo 0;
            }
        }
    }
}
