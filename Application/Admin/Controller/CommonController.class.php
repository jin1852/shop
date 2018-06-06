<?php
namespace Admin\Controller;
use Common\Model\ConfigModel;
use \Think\Controller;
use \Think\Page;
header('content-type:text/html;charset=utf-8');
class CommonController extends \Think\Controller {

    //缓存
    public $cache=3600;

    public $email_role= "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";

//    private $lv4=array(
//        'Home/index','Order/orderAll','User/user','User/changepwd','Order/zc_orderAll','Order/Zcorderchannge','Order/orderchannge',
//        'Order/orderUpdate','updateOrder/orderId','Order/Zc_detail','Order/sample','Order/Zc_sample','Order/detail',
//        'Order/update_ZcOrder','Order/ZcorderUpdate','Goods/index','Goods/edit','Goods/edit_doit','Goods/detail',
//        'Goods/productsEdit','Goods/Doedit_products','Goods/products','Goods/add_products','Goods/isDefault',
//        'Goods/style','Goods/del_detail','Multipics/add','Related/index','Related/add','Related/do_add','Related/del',
//        'Related/edit','Related/do_edit','Goods/status','Goods/add','Clean/clean','Goods/add_doit'
//
//    );
//
//    private $lv3= array(
//        'Home/index','Goods/index','Goods/edit','Goods/edit_doit','Goods/detail','Goods/productsEdit','Goods/Doedit_products',
//        'Goods/products','Goods/add_products','Goods/status','Goods/del','Goods/isDefault','Goods/style','Goods/del_detail',
//        'Multipics/add','Related/index','Related/add','Related/do_add','Related/status','Related/del','Related/edit',
//        'Related/do_edit','Goods/add','Clean/clean','Goods/add_doit'
//
//    );
//
//    private $lv2= array(
//        'Home/index','Admin/register','Admin/gdo_addtest', 'Admin/index','Admin/gdo_add','Admin/gaEdit',
//        'Admin/gado_edit','Admin/status','User/user', 'User/changepwd','User/do_change','User/check_user',
//        'User/check_detail','User/do_assign','User/add_user','User/do_add', 'Order/orderAll','Order/zc_orderAll',
//        'Order/Zcorderchannge','Order/orderchannge','Order/detail', 'updateOrder/orderId','Order/orderUpdate',
//        'Order/Zc_detail','Order/sample','Order/Zc_sample','Order/update_ZcOrder','Order/ZcorderUpdate','Goods/index',
//        'Goods/edit','Goods/edit_doit','Goods/detail','Goods/productsEdit','Goods/Doedit_products','Goods/products',
//        'Goods/add_products','Goods/status','Goods/del_detail','Goods/isDefault','Goods/style','Multipics/add',
//        'Related/index','Related/add','Related/do_add','Related/status','Related/del','Cate/index','Related/edit',
//        'Related/do_edit','Goods/add','Clean/clean','Goods/add_doit'
//    );

    private function _init(){
        $conf = new ConfigModel();
        $status = $conf->get_data(3);
        if (!$status or $status != 1) {
            open_close(0);
        } else {
//            open_();
        }
    }
	// 控制器初始化方法
	protected function _initialize() {
        $this->_init();
		if(empty($_SESSION['admin'])) {
			$this->error('您没有登录，或权限过期,请重新登录', __APP__.'/Index/index'); 
		}else{
            if($_SESSION['admin']['levelId']>1){
//                switch($_SESSION['admin']['levelId']){
//                    case 2:$lv=$this->lv2;break;
//                    case 3:$lv=$this->lv3;break;
//                    case 4:$lv=$this->lv4;break;
//                    default:$lv=$this->lv4;break;
//                }
//                if (in_array(CONTROLLER_NAME."/".ACTION_NAME,$lv)){
//                    $this->error("权限不足");
//                }
            }
        }
	}
    //返回图片路径
    public function full_url(){
        if($_SERVER['HTTP_HOST']=='127.0.0.1' or $_SERVER['HTTP_HOST']=='localhost'){
            return "http://".$_SERVER['HTTP_HOST']."/shop/Public/Uploads/";
        }else{
            return "/Public/Uploads/";
        }
    }
    //处理过长标题
    public function string_Handle($string){
        if(strlen($string)>=9){
            return mb_substr($string,0,9,'utf-8')."...";
        }else{
            return $string;
        }
    }
    //修改页面0的时候值为空
    public function not_top($data){
        if($data==0 or $data=='javascript:void(0);'){$data= '';}
        return $data;
    }

    //格式是否审核
    public function format_audit($data){
        switch($data){
            case 0 : return "未审核";break;
            case 1:  return "已审核";break;
            default:return "未知";break;
        }
    }
    //格式化状态
    public function format_status($data){
        switch($data){
            case 0 : return "禁用";break;
            case 1:  return "启用";break;
            case 2:  return "删除";break;
            default:return "未知";break;
        }
    }

    //格式化状态
    public function advance_status($data){
        switch($data){
            case 2 : return "禁用";break;
            case 0:  return "启用";break;
            case 1:  return "删除";break;
            default:return "未知";break;
        }
    }

    //格式化管理员状态
    public function format_isdeleted($data){
        switch($data){
            case 1 : return "禁用";break;
            case 0:  return "启用";break;
            default:return "未知";break;
        }
    }

    //格式化管理员状态
    public function format_is_order($data){
        switch($data){
            case 1 : return "已下单";break;
            case 0:  return "未下单";break;
            default:return "未知";break;
        }
    }

    //格式化状态
    public function format_type($data){
        switch($data){
            case 0 : return "pc端";break;
            case 1:  return "移动端";break;
            default:return "未知";break;
        }
    }

    //格式是否状态
    public function is_not($data){
        switch($data){
            case 0 : return "否";break;
            case 1:  return "是";break;
            default:return "未知";break;
        }
    }


    //格式外链状态
    public function format_Linktype($data){
        switch($data){
            case 0 : return "外联";break;
            case 1:  return "内联";break;
            default:return "未知";break;
        }
    }
    //格式化审核状态
    public function format_is_checked($data){
        switch($data){
            case 0 : return "审核通过";break;
            case 1:  return "未审核";break;
            default:return "未知";break;
        }
    }
    //格式显示状态
    public function format_is_show($data){
        switch($data){
            case 0 : return "不显示";break;
            case 1:  return "显示";break;
            default:return "未知";break;
        }
    }
    //格式单选/多选状态
    public function format_Radio($data){
        switch($data){
            case 0 : return "单选";break;
            case 1:  return "多选";break;
            default:return "未知";break;
        }
    }
     //格式订单状态
    public function format_orderState($data){
        switch($data){
            case 0 : return "等待商家发货";break;
            case 1:  return "商家已发货,等待用户收获";break;
            case 2:  return "交易完成";break;
            case 3:  return "订单取消";break;
            default:return "未知";break;
        }
    }
     //格式寄样状态
    public function format_sample_status($data){
        switch($data){
            case 0 : return "待发货";break;
            case 1:  return "已发";break;
            default:return "未知";break;
        }
    }
    //格式化时间
    public function time_format($data){
        if ($data && $data > 0) {
            return date("Y-m-d H:i:s", $data);
        } else {
            return  '未修改';
        }
    }
    //字符过滤
    public function filter_str($string){
        $find = "select,update,insert,delete,";
        $keys = explode(",", $find);
        if ($keys) {
            foreach ($keys as &$one) {
                if (strstr($string, $one) != '') {
                    $this->error($string."含有非法字符");
                    break;
                }
            }
        }
        return $string;
    }
    //返回加前缀的表
    public function get_table($value)
    {
        if ($value) {
            switch ($value) {
                case 'zc':return M("zc", "jd_");break;
                case 'areasid':return M("areasid", "jd_");break;
                case 'zc_conf':return M("zc_conf", "jd_");break;
                case 'zc_orders':return M("zc_orders", "jd_");break;
                case 'orders':return M("Orders", "jd_");break;
                case 'zc_order_details':return M("zc_order_details", "jd_");break;
                case 'protype':return M("protype", "jd_");break;
                case 'Advances':return M("Advances", "jd_");break;
                case 'imageshow':return M("imageshow", "jd_");break;
                case 'products_style':return M("products_style", "jd_");break;
                case 'prods':return M("prods", "jd_");break;
                case 'products':return M("products", "jd_");break;
                case 'colors':return M("colors", "jd_");break;
                case 'brands':return M("brands", "jd_");break;
                case 'users':return M("users", "jd_");break;
                case 'sizes':return M("sizes", "jd_");break;
                case 'levels':return M("levels", "jd_");break;
                case 'prodimg':return M("prodimg", "jd_");break;
                case 'gendars':return M("gendars", "jd_");break;
                case 'business':return M("business", "jd_");break;
                case 'authentication':return M("authentication", "jd_");break;
                case 'prods_styles':return M("prods_styles", "jd_");break;
                case 'admin':return M("glyadmins", "jd_");break;
                case 'zt':return M("zt_conf", "jd_");break;
                case 'related':return M("related", "jd_");break;
                case 'shopcart':return M("user_shop_cart", "jd_");break;
                case 'Links':return M("Links", "jd_");break;
                case 'country':return M("country", "jd_");break;
                case 'log':return M("log", "jd_");break;
                default:$this->error("公用表名未添加");break;
            }
        } else {
            $this->error("非法操作");
        }
    }
    //分页
    public function get_page($count, $num){
        $page = new page($count, $num);
        $page->lastSuffix = false;
        $page->setConfig('header', '&nbsp;第%NOW_PAGE%页/共%TOTAL_PAGE%页&nbsp;（' . $num . '条记录/页&nbsp;&nbsp;共%TOTAL_ROW%条记录）');
        $page->setConfig('prev', '上一页');
        $page->setConfig('next', '下一页');
        $page->setConfig('last', '末页');
        $page->setConfig('first', '首页');
        $page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $page->parameter = I('get.');
        return $page;
    }
    //检查数据是否存在
    public function check_field($id=null,$title,$table,$text){
        if($id){
            //编辑的情况
            $has=$this->get_table("$table")->where("id!=%d and status!=2 and $text='%s'",$id,$title)->find();

            if($has){
                $this->error($title." 已存在");
            }else{
                return $title;
            }
        }else{
            //添加的情况
            $has=$this->get_table("$table")->where("$text='%s' and status!=2",$title)->find();
            if($has){
                $this->error($title." 已存在");
            }
            else{
                return $title;
            }
        }
    }
    //开启状态
    public function status_on($url,$id,$time=null){
        if($id && $id>0 && $url){
            $data=$this->get_table($url)->where("id=%d",$id)->find();
            if($data['status']==0) {
                $data['status'] = 1;
                if($time){
                    $data['updata_time']=$time;
                }
                $re=$this->get_table($url)->save($data);
                if($re){
                    $this->success("开启成功");
                }else{
                    $this->error("开启失败");
                }
            }elseif($data['status']==1){
                $this->error("已是开启状态");
            }else{
                $this->error("未知错误");
            }
        }else{
            $this->error("非法操作");
        }
    }

    //检查重复
    public function check_repeat($value,$valuename,$table,$id=null,$idname=null){
        if($id){
            $has=$this->get_table("$table")->where($idname."!=$id"." and ".$valuename."='".$value."'")->select();
        }else{
            $has=$this->get_table("$table")->where($valuename."='".$value."'")->select();
        }
        if($has){
            $this->error($value."已存在");
        }
        return $value;
    }


    //禁用状态
    public function status_off($url,$id,$time=null){
        if($id && $id>0 && $url){
            $data=$this->get_table($url)->where("id=%d",$id)->find();
            if($data['status']==1) {
                $data['status'] = 0;
                if($time){
                    $data['updata_time']=$time;
                }
                $re=$this->get_table($url)->save($data);
                if($re){
                    $this->success("禁用成功");
                }else{
                    $this->error("禁用失败");
                }
            }elseif($data['status']==0){
                $this->error("已是禁用状态");
            }else{
                $this->error("未知错误");
            }
        }else{
            $this->error("非法操作");
        }
    }

    //获取ip
    public function get_ip()
    {
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

    //日志修改拼接
    public function save_log($text,$old,$new){
        $log = "，".$text."由 ".$old." 修改为 ".$new;
        return $log;
    }


    //日志
    public function write_log($model,$content){
        $data['admin_id']=$_SESSION['admin']['adminId'];
        $data['content']=$content;
        $data['model']=$model;
        $data['time']=time();
        $data['ip']=$this->get_ip();
        $this->get_table("log")->add($data);
    }

    //开关按钮日志
    public function status_log($status,$id,$name){
        $text=$this->format_status($status);
        $this->write_log('状态开关',$text.' id为: '.$id.' 的'.$name);
    }

    //删除旧图
    public function del_img($dir,$img){
        unlink('./Public/Uploads/'.$dir.'/'.$img);
        unlink('./Public/Uploads/'.$dir.'/m_'.$img);
        unlink('./Public/Uploads/'.$dir.'/l_'.$img);
    }

    //权限控制 级别1或3
    public function check_1or3(){
        if($_SESSION['admin']['levelId']!=1 && $_SESSION['admin']['levelId']!=3){
            $this->error('权限不够');
        }
    }

    //权限控制 级别1到3
    public function check_1to3(){
        if($_SESSION['admin']['levelId']>=4){
            $this->error('权限不够');
        }
    }

    //权限控制 是否超级管理员
    public function check_super(){
        if($_SESSION['admin']['levelId']!=1){
            $this->error('权限不够');
        }
    }

    //上传图片
    public function upFile($dir) {
        $upload = new \Think\Upload();
        $upload->maxSize = 3145728;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath= './Public/Uploads/'.$dir.'/';

        $upload->savePath = '';
        $upload->autoSub = false;
        $upload->saveName = 'time';
        $info = $upload->upload();
        if(!$info) {
            $this->error($upload->getError());
        }else{
            $image = new \Think\Image();
            $image->open($upload->rootPath.$info['image']['savepath'].$info['image']['savename']);
            $image->thumb(250,250)->save($upload->rootPath.$info['image']['savepath'].'l_'.$info['image']['savename']);
            $image->thumb(50, 50)->save($upload->rootPath.$info['image']['savepath'].'m_'.$info['image']['savename']);
            $imgname= $info['image']['savename'];
            return $imgname;
        }
    }
    //end
}