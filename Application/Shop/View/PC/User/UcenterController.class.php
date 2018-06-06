<?php
namespace Shop\Controller;
use Admin\Controller\EmailController;
use Shop\Model\BaseModel;
use Shop\Model\CartModel;
use Shop\Model\FavouriteModel;
use Shop\Model\ProductsModel;
use Shop\Model\UserModel;
use Think\Controller;

class UcenterController extends BaseController {
    public $page_num = 20;

    public function _initialize()
    {

        parent::_initialize();
    }

    //购物车展示
    public function shoppingCart(){
        $id = $this->information();
        $Page_num = 10;//每页行数
        $data = D("user_shop_cart");
        $count      = $data->where("userId={$id}")->count();
        $Page       = new \Think\Page($count,$Page_num);
        $show       = $Page->show();
        $orders=$data->where("userId={$id}")->limit($Page->firstRow.','.$Page->listRows)->select();
        //$orders= $data->table(array('jd_user_shop_cart'=>'a','jd_products'=>'b'))->where("a.userid={$id} AND a.pid=b.productId")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign("orders_i",$orders);
        $this->assign('page',$show);
        $this->assign('head_title', 'Cart');
        $this->information();
        $this->assign("count",count($orders));
        $this->assign("title",ACTION_NAME);
        $this->assign('head_title',ACTION_NAME);
//        if(count($orders)==0){
//            $this->check_them(ACTION_NAME."-null");
//        }else{
            $this->check_them('shopping_cart');
//        }

    }

    //搜索
    public function shop_select(){
        $this->information();
        $time_s=I("post.time_s");
        $time_e=I("post.time_e");
        $ltime=explode("/",$time_s);
        $time_s=$ltime[2]."-".$ltime[1]."-".$ltime[0]." 0:00:00";
        $ltime=explode("/",$time_e);
        $time_e=$ltime[2]."-".$ltime[1]."-".$ltime[0]." 0:00:00";
        //dump(strtotime($time_e));
        // $this->check_them(ACTION_NAME);
    }

    //询价单
    public function enquiry(){
        $id = $this->information();
        $Page_num = 10;//每页行数
        $data = D("inquiry");
        $gd=I("get.gd");
        $count      = $data->where("userId={$id} and status=0")->count();
        $Page       = new \Think\Page($count,$Page_num);
        $show       = $Page->show();
        $orders=$data->where("userId={$id} and status=0")->order('inqid desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        //$orders= $data->table(array('jd_user_shop_cart'=>'a','jd_products'=>'b'))->where("a.userid={$id} AND a.pid=b.productId")->limit($Page->firstRow.','.$Page->listRows)->select();
        for ($x=0; $x<count($orders); $x++) {
            $m = M("inquiry_shop");
            $sid=$orders[$x]['inqid'];
            $s_e=$m->where("inqid =({$sid})")->select();
            $orders[$x]['shop_cart']=$s_e;
        }
        //var_dump($orders);
        $this->assign("orders_i",$orders);
        $this->assign('page',$show);
        $this->assign('head_title', 'Enquiry');
        $this->assign('open_gd',$gd);
       // dump($orders);
        $this->assign("title",ACTION_NAME);
        $this->check_them(ACTION_NAME);
    }

    //DeletedOrder
    public function DeletedOrder(){
        $id = $this->information();
        $Page_num = 10;//每页行数
        $data = D("inquiry");
        $gd=I("get.gd");
        $count      = $data->where("userId={$id} and status=1")->count();
        $Page       = new \Think\Page($count,$Page_num);
        $show       = $Page->show();
        $orders=$data->where("userId={$id} and status=1")->limit($Page->firstRow.','.$Page->listRows)->select();
        //$orders= $data->table(array('jd_user_shop_cart'=>'a','jd_products'=>'b'))->where("a.userid={$id} AND a.pid=b.productId")->limit($Page->firstRow.','.$Page->listRows)->select();
        for ($x=0; $x<count($orders); $x++) {
            $m = M("inquiry_shop");
            $sid=$orders[$x]['inqid'];
            $s_e=$m->where("inqid =({$sid})")->select();
            $orders[$x]['shop_cart']=$s_e;
        }

        $this->assign("orders_i",$orders);
        $this->assign('page',$show);
        $this->assign('open_gd',$gd);
        $this->assign('head_title', 'Order');
        //dump($orders);
        $this->assign("title",ACTION_NAME);
        $this->check_them(ACTION_NAME);
    }
      //初始化询价单
    public function enquiry_i(){
        $this->information();
        $m=M('inquiry_shop');
        $id=I("post.id");
        $users = $m->where("inqid={$id} and status=0")->select();
        echo json_encode($users);

    }
    //修改询价单
    public function enquiry_save(){
        $this->information();
        $m=M('inquiry_shop');
        $id=I("post.id");
        $number=I("post.number");
        $remark=I("post.remark");
        $price=I("post.price");
        //update_time
        $data['update_time'] = time();
        $data['number'] = $number;
        $data['remark'] = $remark;
        $data['price'] = $price;
        $m->where("id={$id}")->save($data);

    }
    /*样品*/
    public function samplerequest(){
        $this->enquiry();
        //$this->information();
       // $this->assign("title",ACTION_NAME);
       // $this->check_them(ACTION_NAME);
    }

    /*designerteam*/
    public function designerteam(){
        $this->information();
        $this->specialservice(2);
        $this->assign("title",ACTION_NAME);
        $this->assign('head_title',ACTION_NAME);
        $this->check_them(ACTION_NAME);
    }
    /*designerteam*/
    public function fullservice(){
        $this->information();
        $this->specialservice(1);
        $this->assign("title",ACTION_NAME);
        $this->assign('head_title',ACTION_NAME);
        $this->check_them(ACTION_NAME);
    }
    /*designerteam*/
    public function personalteam(){
        $this->information();
        $this->specialservice(3);
        $this->assign("title",ACTION_NAME);
        $this->assign('head_title',ACTION_NAME);
        $this->check_them(ACTION_NAME);
    }
    /*specialservice*/
    public function specialservice($tid){
        $m=M('specialservice');
        if($tid==1){
            $sql="fullservice";
        }
        if($tid==2){
            $sql="designerteam";
        }
        if($tid==3){
            $sql="personalteam";
        }
        $data = $m->getField($sql);
        $this->assign("img_service",$data);
    }
    /*订单*/
    public function order(){
        $name = 'Index';
        $list = $this->get_image($name);
        $this->assign('image_list', $list);
        $this->assign('head_title', 'Home');
        $this->information();
         $this->assign("title",ACTION_NAME);
         $this->check_them(ACTION_NAME);
         $this->display();
    }

    public function sourcing(){
        $userId=$this->information();
        $list=M("Sourcing")->where("userId={$userId}")->select();

        foreach($list as &$one){
            $item_list=M("SourcingItem")->where("sourcing_id=".$one['id'])->cache(true,120)->select();
            foreach($item_list as &$item){
                $attach=json_decode($item['attach_json'],true);
                if(json_last_error()==JSON_ERROR_NONE){
                    $item['attach']=$attach;
                }
            }
            $one['item_list']=$item_list;
        }
//        var_dump($list);
        $this->assign("list",$list);
        $this->assign("header_title","sourcing");
        $function_list=M("ProductsStyle")->where("pid=2")->field("styleName")->group('styleName')->cache(true,3600)->select();
        $this->assign("function",$function_list);
        $this->assign("title",ACTION_NAME);
        $this->assign("head_title",'Sourcing');
        $this->check_them(ACTION_NAME);
    }
    public function sourcing_new(){
        if(IS_POST){
            $imgmain=I("imgmain");
            $attach=I("attach");
            $prodtype=I("prodtype");
            $size=I("size");
            $size_type=I("size_type");
            $function=I("function");
            $price=I("price");
            $material=I("material");
            $quantity=I("quantity");
            $coating=I("coating");
            $remark=I("remark");
            $user = session('user');
            $userId=$user['userId'];
            $count=count($size);
            if($count==0){
                $this->error("提交错误");
                return;
            }
            $sourcing=array();
            $sourcing['sn']=date("Ymd").rand(100,999);
            $sourcing['add_time']=time();
            $sourcing['modi_time']=0;
            $sourcing['status']=1;
            $sourcing['userId']=$userId;
            $sourcing_id=M("Sourcing")->data($sourcing)->add();
            for($i=0;$i<$count;$i++){
                $data=array();
                $data['img']=$imgmain[$i];
                $data['attach_json']=$attach[$i];
                $data['prodtype']=$prodtype[$i];
                $data['size']=$size[$i];
                $data['size_type']=$size_type[$i];
                $data['function']=$function[$i];
                $data['target_price']=$price[$i];
                $data['material']=$material[$i];
                $data['quantity']=$quantity[$i];
                $data['coating']=$coating[$i];
                $data['remark']=$remark[$i];
                $data['sourcing_id']=$sourcing_id;
                M("SourcingItem")->data($data)->add();
            }
            //var_dump($price);
            $this->success("Order Sourcing Success");
            return;
        }
        $this->information();
        $this->assign("header_title","sourcing new");
        $function_list=M("ProductsStyle")->where("pid=2")->field("styleName")->group('styleName')->cache(true,3600)->select();
        $this->assign("function",$function_list);
        $this->assign("title",ACTION_NAME);
        $this->check_them(ACTION_NAME);
    }
    public function sourcing_ajax_newform(){

            $function_list=M("ProductsStyle")->where("pid=2")->field("styleName")->group('styleName')->cache(true,3600)->select();
            $this->assign("function",$function_list);
            $this->check_them(ACTION_NAME);
    }
    public function uploadSourcingFile(){
//        import('@.Org.Util.UploadHandler');
//        $upload_handler = new \Org\Util\UploadHandler();
        $ext=I('exts','jpg,gif,png,jpeg');
        $ext_arr=explode(",",$ext);
        $config = array(
            'maxSize'=>1024*1024*2,
            'savePath'   =>    '/Public/Uploads/',
            'rootPath'      => './',
            'saveName'   =>    array('uniqid',''),
            'exts'       =>   $ext_arr,
            'autoSub'    =>    true,
            'subName'    =>    array('date','Ymd')
        );
        $upload = new \Think\Upload($config);// 实例化上传类

        $info   =   $upload->upload();
        if(!$info) {// 上传错误提示错误信息
            $this->ajaxReturn(array('status'=>0,'msg'=>$upload->getError()));
        }else{
            // 上传成功
//            var_dump($info);
            $this->ajaxReturn(array('status'=>1,'msg'=>"上传成功！",'name'=>$info['files']['name'],'filename'=>$info['files']['savepath'].$info['files']['savename'],'ext'=>$info['files']['ext']));
        }
    }

//followingProduct
//    public function followingProduct(){
//        //users_favourite
//        $id=I("get.del");
//        if($id>0){
//            $data['status'] = 1;
//            $mm = M('users_favourite');
//            $mm->where("id={$id}")->save($data);
//        }
//        $tid=I("get.add");
//        if($tid>0){
//            $data['status'] = 1;
//            $mm = M('users_favourite');
//            $mm->where("id={$id}")->save($data);
//        }
//        $Page_num = 10;//每页行数
//        $id = $this->information();
//        $data = D("users_favourite");
//        $count      = $data->where("userId={$id} and status=0")->count();
//        $Page       = new \Think\Page($count,$Page_num);
//        $show       = $Page->show();
//       // $orders=$data->where("userId={$id} and status=0")->limit($Page->firstRow.','.$Page->listRows)->select();
//        $orders= $data->table(array('jd_users_favourite'=>'a','jd_prods'=>'b'))->where("a.userid={$id} AND a.prodId=b.prodId and a.status=0")->limit($Page->firstRow.','.$Page->listRows)->select();
//        $this->assign("list",$orders);
//        $this->assign('page',$show);
//        $this->assign("title",ACTION_NAME);
//        $this->check_them(ACTION_NAME);
//
//    }
    /*众筹*/
    public function Crowdfunding(){
        $Page_num = 10;//每页行数
        $id = $this->information();
       // $id = $this->userid;
        $data = D("Crowdfunding");
        $count      = $data->where("status=0")->count();
        $Page       = new \Think\Page($count,$Page_num);
        $show       = $Page->show();
        $orders=$data->where("status=0")->limit($Page->firstRow.','.$Page->listRows)->select();
        //$orders= $data->table(array('jd_user_shop_cart'=>'a','jd_products'=>'b'))->where("a.userid={$id} AND a.pid=b.productId")->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign("orders_i",$orders);
        $this->assign('page',$show);
        $this->information();
        $this->assign("title",ACTION_NAME);
        if(count($orders)==0){
            $this->check_them(ACTION_NAME);
            //$this->check_them(ACTION_NAME."-null");
        }else{
            $this->check_them(ACTION_NAME);
        }
    }
    //删除询价单
    public function enquiry_del(){
        $this->information();
        $m=M('inquiry_shop');
        $id=I("post.id");
        $inqid=I("post.tid");
        //echo $id."--".$inqid;
        $data['status'] = 1;
        $m->where("id={$id}")->save($data);
        $m=M('inquiry_shop');
        $users = $m->where("inqid={$inqid} and status=0")->select();
        if(count($users)>0) {
        }else{
            $mm = M('inquiry');
            $mm->where("inqid={$inqid}")->save($data);
        }
        echo 01;
    }
    //删除询价单
    public function enquiry_delt(){
        $this->information();
        $id=I("post.id");
        $data['status']=1;
            $mm = M('inquiry');
            $mm->where("i_number='{$id}'")->save($data);

        echo 01;
    }
    //用户信息
    public function information(){
        if($_SESSION['user'][uname]==''){
              $this->error('未登录');
        }else{
            return $_SESSION['user']['userId'];
        }

    }

    //添加通信地址
    public function address(){
        $id = I("post.id");
        $m=M("inquiry");

    }
    //询价单提交
    public function send_e_g(){
     $this->information();
    $id = I("post.id");
    $m=M("inquiry");
    $data['state'] =2;
    $m->where("i_number='{$id}'")->save($data);
        $dat= $m->where("i_number='{$id}'")->find();
        echo $dat['inqid'];
        $admin = M()->query('select * from jd_glyadmins where adminId=(select adminId from jd_users where userId='.$dat['userId'].') and levelId=4 and isdeleted=0 limit 1');
        if($admin) {
            $to = $admin[0]['email'];
        }else{
            $to = 'gala@galison.co';
        }
        $Email = new EmailController();
        $title = 'Gala have a new enquiry';
        $content = 'Gala have a new enquiry is : ' . $dat['i_number'];
        $re = $Email->send_email($to, '', $title, $content);
        if ($re == 1) {
            $this->list_json_result(1, 'reg success', '');
        } else {
            $this->list_json_result(0, 'reg fail', '');
        }


}
    //取消询价单
    public function send_q_g(){
        $this->information();
        $id = I("post.id");
        $nr = I("post.nr");
        $m=M("inquiry");
        $data['state'] =3;
        $data['note'] =$nr;
        $m->where("i_number='{$id}'")->save($data);
        $dat= $m->where("i_number='{$id}'")->find();
        echo $dat['inqid'];

    }
    //购物车保存数据
    public function shop_sava(){
        $this->information();
        $id = I("post.id");
        $num_t = I("post.num_t");
        $remark = I("post.remark");
        $type = I("post.type");
        if($id < 1 or $num_t == "" or $id == ""){
            echo "02";
        }else{
            if($type==1){
                $table="Crowdfunding";

            }else{
                $table="user_shop_cart";
            }
            $m=M($table);
            $data['number'] = $num_t;
            $data['remark'] = $remark;
            $m-> where("id={$id}")->setField($data);
            echo "01";
        }
        die();
    }
    //购物车保存询价单
    public function shop_sava_i(){
        $tid = $this->information();
        $id = $_POST['id'];
        if($id == ""){
            echo "02";
        }else{
            $type = I("post.type");
            if($type==1){
                $table="jd_crowdfunding";

            }else{
                $table="jd_user_shop_cart";
            }
            $m=M('inquiry');
            $data['cartid'] = $id;
            $data['userId'] = $tid;
            $data['itime'] = time();
            $data['state'] = 1;
            $data['i_number'] = "No.".$this->num_();
            $m->add($data);
            $no_num=$data['i_number'];

            $result = D("inquiry")->table(array('jd_inquiry'=>'a',$table=>'b'))->where("a.i_number='{$no_num}' AND b.id in ({$id})")->select();
           // dump($result);
            for ($x=0; $x<count($result); $x++) {
                unset($result[$x]['id']);
                unset($result[$x]['cartid']);
                unset($result[$x]['itime']);
                unset($result[$x]['state']);
                unset($result[$x]['status']);
                unset($result[$x]['is_order']);
                unset($result[$x]['sizeId']);
                unset($result[$x]['colorId']);
                unset($result[$x]['rgbimg']);
                unset($result[$x]['note']);
                unset($result[$x]['address_id']);
                $result[$x]['content']=strip_tags($result[$x]['content']);
            }
            $mm=M('inquiry_shop');
            //var_dump($result);
            $mm->addAll($result);
            //dump($result);
            echo "01";
        }
        die();
    }

    //生成订单号
    private function num_()
    {
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
    /*判断当前文件是否存在*/
    private function check_them($file){
        $file_name = "./Application/Shop/View/".C('DEFAULT_THEME')."/User/{$file}.html";

        if(is_file($file_name)){

            $this->display("User/{$file}");
        }else{
            $this->error('异常加载');
        }
    }




/* ============================================  20170819 begin add by jin  ========================================== */

    //following_products
    public function following_products(){
        $user = session('user');
        if ($user) {
            $type = I('get.type');
            $product = new ProductsModel();
            $table = 'jd_users_favourite';
            $alias = 'uf';
            $where = $alias . '.userId = ' . $user['userId'] . ' and ' . $alias . '.status=1 ';
            if ($type > 0) {
                $path = $product->path_info(1, $type);
                $where .= ' and jps.proTypeId in (' . $path . ')';
            }
            $where .= ' and jps.status = 1';
            $order = $alias . ".time desc";
            $join = 'left join jd_prods as jps ON jps.prodId=uf.prodId';
            $field = 'jps.*';
            $list = $product->following_get_product_list($table, $alias, $where, $join, $field, $order, $this->page_num);
            $this->show_type_list($type);
            $this->assign("list", $list['data']);
            $this->assign("page", $list['page']);
            $this->assign("head_title", "following products");
            $this->assign("title", ACTION_NAME);
            $this->assign('type', $type);
            $this->display("Ucenter/following_products");
        } else {
            $this->del_session();
        }
    }

    //following Series
    public function following_series(){
        $user = session('user');
        if ($user) {
            $U = new UserModel();
            $data = $U->following_series_list($user['userId']);
            $u_data = $U->un_following_series_list($data);
            $this->assign("list", $data);
            $this->assign("u_list", $u_data);
            $this->assign("head_title", "following Series");
            $this->assign("title", ACTION_NAME);
            $this->display("Ucenter/following_serise");
        } else {
            $this->del_session();
        }
    }


    //following Series
    public function following_activities(){
        $user = session('user');
        if ($user) {
            $this->assign("head_title", "following activities");
            $this->assign("title", ACTION_NAME);
            $this->display("Ucenter/following_activities");
        } else {
            $this->del_session();
        }
    }

    //view_history
    public function view_history(){
        $user = session('user');
        if ($user) {
            $type = I('get.type');
            $product = new ProductsModel();
            $table = 'jd_users_view_history';
            $alias = 'uvh';
            $where = $alias . '.userId = ' . $user['userId'];
            $order = $alias . ".time desc";
            if ($type > 0) {
                $path = $product->path_info(1, $type);
                $where .= ' and jps.proTypeId in (' . $path . ')';
            }
            $where .= ' and jps.status = 1';
            $join = 'left join jd_prods as jps ON jps.prodId=uvh.prodId';
            $field = 'jps.*';
            $list = $product->following_get_product_list($table, $alias, $where, $join, $field, $order, $this->page_num);
            $this->show_type_list($type);
            $this->assign("list", $list['data']);
            $this->assign("page", $list['page']);
            $this->assign("head_title", "view history");
            $this->assign("title", ACTION_NAME);
            $this->assign('type', $type);
            $this->display("Ucenter/view_history");
        } else {
            $this->del_session();
        }
    }

    //following show_type_list
    private function show_type_list($type){
        $tl = $this->ucenter_following_type_list($type);
        $this->assign("t_list", $tl);
    }

/* ===============================================   end add by jin  ================================================= */
}