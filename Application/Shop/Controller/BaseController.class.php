<?php
namespace Shop\Controller;
use Common\Model\ConfigModel;
use Shop\Model\AdvancesModel;
use Shop\Model\ColorModel;
use Shop\Model\ImageshowModel;
use Shop\Model\NavModel;
use Shop\Model\ProductsModel;
use Shop\Model\UserModel;
use Shop\Model\ZtModel;
use Think\Controller;
header('content-type:text/html;charset=utf-8');
class BaseController extends Controller {
    //搜索字符配置
    public $find = "http";
    //长度配置
    public $strlen=6;
    //缓存时间配置
    public $cache_time=3600;
    //手机号码正则
    public $rule = "/^1[3456789]{1}[0-9]{1}[0-9]{8}$/";
    //邮件正则
    public $E = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
    //每页数据条数配置
    public $num = 15;
    public $string = "-,and,or,select,update,;,insert,delete,=, ,,!,@,#,$,%,^,&,*,(,),~,+,|,?,.,/,？,，,。";
    //nav 一列个数配置
    public $pn=22;

    public $welcome='hi,';
    public function url(){
        return 'http://'.$_SERVER['HTTP_HOST'].'/';
    }
    //不可跳过登陆访问模块/接口
    public $allow=array(
        //首页
        'Index/index',
        //登录
        'Login/index','Login/do_login','Login/forget','Login/oauth_do','Login/newsletter','Product/set_key',
        //facebook
        'Facebook/login_with_facebook','Facebook/facebook_callback',
        //twitter
        'Twitter/login_with_twitter','Twitter/twitter_callback',
        //注册
        'Register/index','Register/do_register',
        //关于
        'About/contanctus','About/sitemap',
    );
    //空操作
    public function _empty(){
        $this->error('非法操作');
    }

    //初始化
    public function _initialize(){

        if(ismobile()){
            C('DEFAULT_THEME','Mobile');
            $type=2;
            echo "<h3>非常感谢您的访问,</h3>";
            echo "<h3>但我们的手机站点正在建设中, 敬请期待...</h3>";
            echo "<h3>目前建议您使用电脑浏览...</h3>";
            echo "<br>";
            echo "<h3>The mobile site is being built, please look forward to it...</h3>";
            echo "<h3>It suggested that you use the computer to browse...</h3>";
            echo "<h3>Thank you</h3>";
            die();
        }else{
            C('DEFAULT_THEME','PC');
            $type = 1;
        }
        //获取访问模块/接口
        $visit=CONTROLLER_NAME.'/'.ACTION_NAME;
        //获取导航数据
        $this->nav();
        //获取轮播数据
        $this->banner();
        //检测是否需要登陆
        
        if ($this->ckeck($visit)) {
            $this->_init($type);
        }

        if(!in_array($visit,$this->allow)) {
            //$index=__APP__.'/Index/index';
            $login=__APP__.'/Login/index';
            $uid=$this->get_session();

            if ($uid) {
                $User=new UserModel();
                $user=$User->check_user($uid);
                if($user){
                    if($user['status']==1){
                        $this->users();
                    }else{
                        $this->del_session();
                        //$this->error("账户已被封停，请联系管理员",$login);
                    }
                }else{
                    $this->del_session();
                    //$this->error("账户不存在，请重新登陆",$login);
                }
            } else {
                $this->del_session();
                //$this->error("请先登陆",$login);
            }
        }else{
            $this->users();
        }
    }

    //设置session
    public function set_session($user_id){
        session('user_id',$user_id);
    }
    //获取session
    public function get_session(){
        $user=session('user');
        return $user['userId'];
    }
    //删除session
    public function del_session(){
        session('user_id','');
        session_destroy();
        $url=__APP__.'/Login/index/ls/11';
        echo "<script>window.location.href='$url';</script>";
    }
    //json
    public function list_json_result($status, $msg, $result){
        echo json_encode(array('status' => $status, 'info' => $msg, 'result' => $result));
        die();
    }
    //get nav data
    public function nav(){
        $Nav=new NavModel();
        //product
        $product=S('Nav_product');
        if(!$product) {
            $product = $Nav->product();
        }
        $this->assign('product',$product);
        //mini_title
        $mini_title=array();
        for($i=0;$i<6;$i++){
            $mini_title[$i]=$product['lv1'][$i];
        }
        $this->assign('mini_title',$mini_title);
        //style
        $style=S('Nav_style');
        if(!$style){
            $style = $Nav->style();
        }
        $this->assign('style',$style);
        $this->assign('pn',$this->pn);
        $series = S('Nav_series');
        //series
        if(!$series){
            $name = 'Series';
            $list = $this->get_image($name);
            if($list) {
                $series['title'] = 'Series';
                $series['link'] = __APP__ . '/Series/index';
                $series['list'] = $list;
                $series['bg'] = $Nav->series_bg();
                S('Nav_series', $series);
            }
        }
        $this->assign('series',$series);
    }
    //get banner data
    public function banner(){
        $Banner=new AdvancesModel();
        $banner=S('banner');
        if(!$banner) {
            $banner = $Banner->advances_list();
        }
        $resources=$this->assignArticle();
        $cart=$this->assignShopcart();
        $this->assignNotification();
        $this->assign("resources",$resources);
        $this->assign("cart",$cart);

        $this->assign('banner',$banner);
    }
    public function assignArticle(){
        $catid=1;
        $article=M("Article")->where("catid={$catid}")->order("id desc")->limit(15)->select();
        foreach($article as &$one){
            $one['add_time']=date('M-d-Y',$one['add_time']);
        }
        return $article;

    }
    public function assignNotification(){
        $userId=$_SESSION['user']['userId'];
        if($userId) {
            $count=0;
            $update_cart = M("user_shop_cart")->where("userId={$userId} and ( (unix_timestamp()-create_time)<24*3600*2 or (unix_timestamp()-update_time)<24*3600*2 )")->order("update_time desc ,create_time desc")->select();

            if($update_cart) {

//                var_dump("user_shop_cart". count($update_cart));
                $count += count($update_cart);
            }
            $this->assign("update_cart", $update_cart);


            $update_inquiry = M("Inquiry")->where("userId={$userId} and (unix_timestamp()-itime)<24*3600*2 ")->order("itime desc ")->select();
//            var_dump($update_inquiry);
            if($update_inquiry) {
//                var_dump("Inquiry". count($update_inquiry));
                $count += count($update_inquiry);
            }
            $this->assign("update_inquiry", $update_inquiry);

            $update_viewhistory = M("users_view_history")->where("userId={$userId} and (unix_timestamp()-`time`)<24*3600*2 ")->order("`time` desc ")->select();
            if($update_viewhistory) {
//                var_dump("users_view_history". count($update_viewhistory));
                $count += count($update_viewhistory);
                foreach ($update_viewhistory as &$one) {
                    $prod = M("Prods")->where("prodId=" . $one['prodId'])->cache(true, 3600)->find();
                    $one['prod'] = $prod;
                }
            }
            $this->assign("update_viewhistory", $update_viewhistory);


            $update_favourite = M("users_favourite")->where("userId={$userId} and (unix_timestamp()-`time`)<24*3600*2 ")->order("`time` desc ")->select();

            if($update_favourite ) {
//                var_dump("users_favourite". count($update_favourite));
//                var_dump($update_favourite);
                if($update_favourite) {


                    foreach ($update_favourite as &$one) {
                        $prod = M("Prods")->where("prodId=" . $one['prodId'])->cache(true, 3600)->find();
                        $one['prod'] = $prod;
                    }
                }
                $count += count($update_favourite);
            }
//            var_dump($update_favourite);
            $this->assign("notification_count",$count);
            $this->assign("update_favourite", $update_favourite);
        }
    }

    public function assignShopcart(){
        $data = D("user_shop_cart");
        $userId=$_SESSION['user']['userId'];
        $cartlist = $data->where("userId={$userId}")->select();
        return $cartlist;
    }

    public function get_products_type_list($type_id,$id){
        $type_list=S('type_list_'.$type_id.'_'.$id);
        if(!$type_list) {
            $Type = new ProductsModel();
            $type_list = $Type->type_list($type_id,$id);
        }
        return $type_list;
    }

    public function get_products_color_list($cid){
        $color_list=S('color_list_'.$cid);
        if(!$color_list) {
            $Color = new ColorModel();
            $color_list = $Color->all_color_list($cid);
        }
        return $color_list;
    }


    //show user data
    public function users(){
        $user = session('user');
        $this->assign('user', $user);
    }

    public function explain_user($user){
        unset($user['upwd']);
        unset($user['FB_openid']);
        unset($user['TW_openid']);
        switch($user['sex']){
            case 0:$user['welcome']=$user['nickName'];break;
            case 1:$user['welcome']='Mr.'.$user['nickName'];break;
            case 2:$user['welcome']='Miss.'.$user['nickName'];break;
            default:$user['welcome']=$user['nickName'];break;
        }
        $user['welcome']=$this->welcome.$user['welcome'];
        switch($user['status']){
            case 0:$user['status_name']="已禁用";break;
            case 1:$user['status_name']="已启用";break;
            default:$user['status_name']="未知";break;
        }
        if($user['userimg']){
            if (strpos($user['userimg'], $this->find) == false) {
                $user['userimg'] = $this->url() . $user['userimg'];
            }
        }else{
            $user['userimg']='';
        }
        $User=new UserModel();
        $user['reg_time']=$User->explain_time($user['reg_time']);
        $user['login_time']=$User->explain_time($user['login_time']);
        $user['reg_ip']=$User->explain_ip($user['reg_ip']);
        $user['login_ip']=$User->explain_ip($user['login_ip']);
        return $user;
    }

    //安全过滤特殊字符
    public function filter_str($string){
        $keys = explode(",", $this->string);
        if ($keys) {
            foreach ($keys as &$one) {
                if (strstr($string, $one) != '') {
                    $this->error('含有敏感字符');
                    break;
                }
            }
        }
        return $string;
    }

    //获取ip
    public function get_ip(){
        // $_SERVER=$HTTP_SERVER_VARS;
        //error_reporting (E_ERROR | E_WARNING | E_PARSE);
        if($_SERVER["HTTP_X_FORWARDED_FOR"]){
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif($_SERVER["HTTP_CLIENT_IP"]){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif ($_SERVER["REMOTE_ADDR"]){
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        elseif (getenv("HTTP_X_FORWARDED_FOR")){
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }
        elseif (getenv("HTTP_CLIENT_IP")){
            $ip = getenv("HTTP_CLIENT_IP");
        }
        elseif (getenv("REMOTE_ADDR")){
            $ip = getenv("REMOTE_ADDR");
        }
        else{
            $ip = "Unknown";
        }
        // echo $ip;
        return bindec(decbin(ip2long($ip)));
    }

    public function order_array(){
        $list=array(
            0=>array('id'=>1,'title'=>'Sales','order'=>'1'),
            1=>array('id'=>2,'title'=>'Price','order'=>'2'),
            2=>array('id'=>3,'title'=>'New','order'=>'3'),
            3=>array('id'=>4,'title'=>'Hot','order'=>'4'),
        );
        return $list;
    }

    public function order_select($order,$select){
        if($select>0) {
            foreach ($order as &$one) {
                if ($one['order'] == $select) {
                    $one['selected'] = 1;
                } else {
                    $one['selected'] = 0;
                }
            }
        }else{
            foreach ($order as &$one) {
                $one['selected'] = 0;
            }
        }
        return $order;
    }

    private function ckeck($visit){
        if($visit){
            if($visit == 'Product/set_key' || $visit == 'product/set_key'){
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }

    private function _init($type){
        $conf = new ConfigModel();
        $status = $conf->get_data($type);
        if (!$status or $status != 1) {
            open_close(0);
        } else {
//            open_();
        }
    }

    public function get_image($name){
        $list = S('image_show_'.$name);
        if (!$list) {
            $num = $this->name_get_num($name);
            if($num>0) {
                $Pic = new ImageshowModel();
                $list = $Pic->pic_list($num);
                S('image_show_' . $name, $list);
            }else{
                $list = array();
            }
        }
        if (!$list){
            $list = array();
        }
        return $list;
    }

//所属页面1->index 2->Series;  4->Manufacturing;  5->Hot;  6->Style; 7->Design;8->showtime;9->showroom
    public function name_get_num($name){
        switch ($name){
            case 'Index':return 1;break;
            case 'Series':return 2;break;
            case 'Manufacturing':return 4;break;
            case 'Hot':return 5;break;
            case 'Style':return 6;break;
            case 'Design':return 7;break;
            case 'Showtime':return 8;break;
            case 'Showroom':return 9;break;
            default:return 0;break;
        }
    }

    public function get_zt_view($name){
        $Zt = new ZtModel();
        $view = $Zt->check_zt_conf($name);
        return $view;
    }


    public function ucenter_following_type_list($id){
//        $type = array(
//            0=>array('proTypeId'=>0,'proName'=>'All Product','c_'=>'All'),
//            1=>array('proTypeId'=>99,'proName'=>'Knife','c_'=>'Knife'),
//            2=>array('proTypeId'=>1,'proName'=>'Cutting','c_'=>'Cutting'),
//            3=>array('proTypeId'=>2,'proName'=>'Boards','c_'=>'Boards'),
//
//        );
        $type = S('ucenter_type_list_sb');
        if(!$type) {
            $type_first = array('proTypeId' => 0, 'proName' => 'All Products', 'c_' => 'All');
            $type = M('protype')->field('proTypeId,proName')->where('lv=1 and proPid=0 and proLive=1')->order('top desc,proTypeId asc')->select();
            array_unshift($type, $type_first);
            foreach ($type as &$one) {
                $one['c_'] = $one['proName'];
            }
            if($type) S('ucenter_type_list_sb',$type);
        }
        if(isset($id)){
            foreach ($type as &$one){
                $one['has'] = ($one['proTypeId'] == $id) ? 1 : 0;
            }
        }
        return $type;
    }
    //end
}