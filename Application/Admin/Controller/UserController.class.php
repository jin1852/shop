<?php
namespace Admin\Controller;
use Shop\Model\AddressModel;
use Shop\Model\CountryModel;
use Think\Controller;
class UserController extends CommonController {

    public $express = array(1 => 'Fedex', 2 => 'UPS', 3 => 'TNT', 4 => 'DHL', 5 => 'SF-EXPRESS', 6 => 'Other');

    public $service = array(1 => 'Intl.Priority', 2 => 'Intl.Economy', 3 => 'Other');

    public function user(){
        $id = I('get.id');
        $aid = I('get.aid');
        $uname=I('uname');
        $adminId=I('adminId');
        $usertel=I('usertel');
        $useremail=I('useremail');
        $LV=$_SESSION['admin']['levelId'];
        $order = M('orders');
        $user = M('users');
        //获取购物次数
        $count = $order -> where('userId = '.$id) -> field('count(userId)') -> select();
        $num = $count[0]['count(userId)'];//购物次数
        //获取成长值        
        $o = $order -> table('jd_orders as O,jd_details as D')
            -> where('O.orderId = D.orderId and O.userId = '.$id)
            -> field('O.userId,O.orderId,O.receiver,D.productId,D.amount,D.price')
            -> select();
        $total = 0;//购物总金额
        $cz = 0;//成长值
        foreach($o as $key => $v){
            $total += $v['amount'] * $v['price'];
            $cz += ceil($v['price']*$v['amount']*1.2);
        }

        /*判定会员等级*/
        if($cz == 0){
            $data['userHYdjId'] = 0;
        }else if($cz < 2000){
            $data['userHYdjId'] = 1;
        }else if($cz < 10000){
            $data['userHYdjId'] = 2;
        }else if($cz < 30000){
            $data['userHYdjId'] = 3;
        }else if($cz >= 30000){
            $data['userHYdjId'] = 4;
        }
        /*判定会员购物行为等级*/
        if($num == 0){
            $data['userGWdjId'] = 0;
        }else if($num < 5){
            $data['userGWdjId'] = 1;
        }else if($num < 10){
            $data['userGWdjId'] = 2;
        }else if($num < 20){
            $data['userGWdjId'] = 3;
        }else if($num < 30){
            $data['userGWdjId'] = 4;
        }else if($num > 50){
            $data['userGWdjId'] = 0;
            if($data['userHYdjId'] < 5){
                $data['userHYdjId'] += 1;
            }else{
                $data['userHYdjId'] = 5;
            }
        }
        $data['userCZ'] = $cz;
        $data['userGCS'] = $num;
        $save = $user -> where('userId='.$id) -> save($data);//更新成长值和购物次数
        $where='U.userHYdjId = UH.userHYdjId and U.reviewed=1';
        if(!empty($useremail)){
            $where.=" and U.useremail LIKE '%".$useremail."%'";
        }
        if(!empty($uname)){
            $where.=" and U.uname LIKE '%".$uname."%'";
        }
        if(!empty($usertel)){
            $where.=" and U.usertel LIKE '%".$usertel."%'";
        }
        $this->uname=$uname;
        $this->useremail=$useremail;
        $r_count=$user->table('jd_users as U,jd_userhydj as UH')->where($where)->count();
        $page=$this->get_page($r_count,10);
        $show = $page->show(); //分页显示输出

//        var_dump($where);
        $r=$user->table('jd_users as U,jd_userhydj as UH')
            -> where($where)
            ->limit($page->firstRow.','.$page->listRows)
            ->order('userId desc')
            ->field('U.*,UH.userHYdjimg')
            -> select();
//        var_dump(M()->getlastsql());
//        var_dump($r);
        $this->page=$show;
        $this -> assign("arr",$r);//分配到会员基本信息
        $db1 = M('orders');
        $r1 = $db1 -> where('jd_orders.userId = '.$id)
            -> join('left join jd_address ON jd_orders.userId = jd_address.userId')
            -> join('left join jd_users ON jd_orders.userId = jd_users.userId')
            -> group('jd_orders.orderId')
            -> select();
        if($r1) $r1 = $this->explain_express($r1);
        $this -> assign("arr1",$r1);//分配到会员收货信息查询
        $r2 = $user -> where('jd_users.userId = '.$aid)
            -> join('left join jd_usergwxwdj ON jd_users.userGWdjId = jd_usergwxwdj.userGWdjId')
            -> select();
        $str = null;
        foreach($r2 as $vo){
            $str = $vo['userdizhi'];
        }
        $areas = explode("/", $str);
        $ad = array();
//        var_dump($areas);
        for($i=0;$i<count($areas);$i++){
            $area = M("country");
            $address = $area -> where('id = '.$areas[$i]) -> select();
            $ad[] = $address;
        }
        $ads = array();
        for($j=0;$j<count($ad);$j++){
            $ads[] = $ad[$j][0]['diqu'];
        }
        $ywy=$this->get_table('admin')->where('levelId=3')->field('uname,adminId')->select();
        $dizhi = implode("/",$ads);
        $address = array();
        if($aid>0) {
            $address = M('address')->alias('a')
                ->join('jd_users as u on a.userId = u.userId',"left")
                ->field("a.*,u.uname")
                ->where('a.userId=%d and a.status=%d', $aid, 1)
                ->select();
            if($address){
                $addressModel = new AddressModel();
                $address = $addressModel ->explain_address($address);
            }

        }
var_dump($address);
        $this->address = $address;
        $this -> dizh=$dizhi;
        $this ->ywy=$ywy;
        $this -> arr2=$r2;//分配到会员详细信息查询
        $this -> display('User/user');
    }



    //未审核会员列表
    public function check_user(){
        $where='adminId<=0';
        $count=$this->get_table('users')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show();
        $data=$this->get_table('users')->where($where)->field('userId,uname,nickName,reviewed,useremail,usertel')->limit($page->firstRow.','.$page->listRows)->cache(false)->select();
        $this->title="用户审核";
        $this->data=$data;
        $this->page=$show;
        $this->display();
    }

    //审核并分配页面
    public function check_detail(){
        $id=I('get.Id');
        if($id){
            $url = $_SERVER['HTTP_REFERER'];
            $data=$this->get_table('users')->where('userId=%d',$id)->find();
            $admin=$this->get_table('admin')->where('levelId=4 and isdeleted=0')->select();
            if($data['userdizhi']>0){
                $res=$this->get_table('country')->where('id=%d',$data['userdizhi'])->find();
                $data['en_name']=$res['en_name'];
            }
            $this->title='审核用户';
            $this->url=$url;
            $this->data=$data;
            $this->admin=$admin;
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //分配
    public function do_assign(){
        $userId=I('post.userId');
        $url=I('post.url');
        $adminId=I('post.adminId');
        if($userId){
//            if($adminId){
                $data=$this->get_table('users')->where('userId=%d',$userId)->find();
                if($data['usertel']){
                    if($data['useremail']){
                        //if($data['lzhengNamen']){

                                $data['adminId']=$adminId;
                                $password=$this->get_password(12);
                                $data['upwd']=md5($password);
                                $data['reviewed']=1;
                                $res=$this->get_table('users')->where('userId=%d',$userId)->save($data);
                                if($res){
                                    $to=array();
                                    $to[0]['address']=$data['useremail'];
                                    $subject='注册成功';
                                    $content='请妥善保管好您的密码:'.$password;
                                    $Email=new EmailController();
                                    $rem=$Email->send_email($to,$cc=null,$subject,$content);
                                    if($rem!=1){
                                        $this->error("邮件发送失败");
                                    }
                                    $this->success('审核通过',$url);
                                }else{
                                    $this->error('审核失败',U('User/check_user'));
                                }

//                        }else{
//                            $this->error("用户未填写真实姓名，审核失败");
//                        }
                    }else{
                        $this->error("用户邮箱未录入，审核失败");
                    }
                }else{
                    $this->error("用户手机号码未填写，审核失败");
                }
//            }else{
//                $this->error('请选择业务员');
//            }
        }else{
            $this->error("非法操作");
        }
    }

    //添加用户
    public function add_user(){
        $Country = new CountryModel();
        $areasid = $Country->get_list();
        $admin=$this->get_table('admin')->where('levelId=4 and isdeleted=0')->select();
        $this->areasid=$areasid;
        $this->admin=$admin;
        $this->title='添加用户';
        $this->display();
    }

    //添加用户操作
    public function do_add(){
        $uname=I('post.uname');
        $nickName=I('post.nickName');
        $upwd=I('post.upwd');
        $sex=I('post.sex');
        $hunyi=I('post.hunyi');
        $usertel=I('post.usertel');
        $useremail=I('post.useremail');
        $lzhengNamen=I('post.lzhengNamen');
        $sfz=I('post.sfz');
        $country = I('country');
        $userdizhi=I('post.userdizhi');
        $userday=I('post.userday');
        $ymoney=I('post.ymoney');
        $xqah=I('post.xqah');
        $adminId=I('post.adminId');
        if($uname){
            $data['uname']=$this->check_repeat($uname,'uname','users');
        }else{
            $this->error("请输入用户名");
        }
        if($nickName){
            $data['nickName']=$nickName;
        }else{
            $data['nickName']=$uname;
        }
        if($upwd){
            $data['upwd']=md5(trim($upwd));
        }else{
            $this->error("请输入密码");
        }
        if($sex==0 or $sex==1 or $sex==2){
            $data['sex']=$sex;
        }
        if($hunyi==0 or $hunyi==1){
            $data['hunyi']=$hunyi;
        }
        if($usertel){
            $data['usertel']=$this->check_repeat($usertel,'usertel','users');
        }else{
            $this->error("请输入手机号码");
        }
        if($useremail){
            $data['useremail']=$useremail;
        }else{
            $this->error("请输入邮箱");
        }
        if($lzhengNamen){
            $data['lzhengNamen']=$lzhengNamen;
        }else{
            $this->error("请输入身份证姓名");
        }
//        if($sfz){
//            $data['sfz']=$this->check_repeat($sfz,'sfz','users');
//        }else{
//            $this->error("请输入身份证");
//        }
        if($userdizhi){
            $data['userdizhi']=$userdizhi;
        }
        if($userday){
            $data['userday']=$userday;
        }
        if($ymoney){
            $data['ymoney']=$ymoney;
        }
        if($xqah){
            $data['xqah']=$xqah;
        }
        if($adminId){
            $data['adminId']=$adminId;
        }else{
            $this->error("请选择业务员");
        }
        if($country>0){
            $data['country'] = $country;
        }
        if($_FILES['image']['error'] === 0){
            $pic = $this->upload();
            $data['userimg'] = $pic['smimg'];
        }
        $data['reviewed']=1;
        $add=$this->get_table('users')->add($data);
        if($add){
            $this->success("添加成功",U('User/user'));
        }else{
            $this->error("添加失败");
        }
    }

    //生成密码发送给用户
    function get_password($length){
        $str = substr(md5((time()+1/20)."abc"), 0, $length);
        return $str;
    }

    //修改用户密码
    public function changepwd(){
        $id=I('get.aid');
        if($id){
            $data=$this->get_table('users')->where('userId=%d',$id)->field('userId,uname')->find();
            $this->title='会员密码';
            $this->data=$data;
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //修改密码操作
    public function do_change(){
        $userId=I('post.userId');
        $upwd=I('post.upwd');
        $rpwd=I('post.rpwd');
        if($userId>0){
            if($upwd){
                if($rpwd){
                    if($upwd==$rpwd){
                        $data=$this->get_table('users')->where('userId=%d',$userId)->find();
                        if(md5($upwd)!=$data['upwd']){
                            $data['upwd']=md5(trim($upwd));
                            $save=$this->get_table('users')->save($data);
                            if($save){
                                $this->success('修改密码成功');
                            }else{
                                $this->error("修改密码失败");
                            }
                        }else{
                            $this->error("密码没有改变");
                        }
                    }else{
                        $this->error('重复密码不同');
                    }
                }else{
                    $this->error("请输入重复密码");
                }
            }else{
                $this->error("请输入密码");
            }
        }else{
            $this->error("非法操作");
        }
    }

    public function cart(){
        $cart_id=I('get.cart_id');
        if($cart_id>0){
            $data=$this->get_table('shopcart')->where('userId=%d',$cart_id)->select();
            foreach($data as &$one){
                $one['status_name']=$this->format_status($one['status']);
                $one['is_order_name']=$this->format_is_order($one['is_order']);
            }
            $this->data=$data;
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    public function cartedit(){
        $id=I('get.id');
        if($id){
            $data = $this->get_table('shopcart')->where('id=%d',$id)->find();
            $color=$this->get_table('colors')->where('status=1')->select();
            $size=$this->get_table('sizes')->where('status=1')->select();
//            var_dump($color);
//            var_dump($size);
            $data['img']=$this->full_url().$data['img'];
            $url= $_SERVER['HTTP_REFERER'];
            $this->url=$url;
            $this->data=$data;
            $this->colors=$color;
            $this->size=$size;
            $this->title=$this->name;
            $this->display();
        }else{
            $this->error('非法操作');
        }
    }

    public function cart_status(){
        $id=I('id');
        $status=I("status");
        if($id>0 && ($status==1 or $status==0)){
            $data['id']=$id;
            $data['status']=$status;
            $data['update_time']=time();
            $result = $this->get_table('shopcart')->save($data);;
            if($result){
                $this->status_log($status,$id,'购物车');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }



    protected function upload(){ //上传图片，如果有传递$url参数的话就删除对应的图片
        $upload = new \Think\Upload(); //实例化tp的上传类
        $upload->exts = array('jpg','gif','png','jpeg'); //设置附件上传类型
        $upload->rootPath = './Public/Uploads/'; //相对于站点根目录jd
        $upload->savePath = ''; //设置附件上传目录,地址是相对于根目录(rootPath的)
        $info = $upload->upload(); //开始上传
        if(!$info){
            $this->error($upload->getError());
        }else{
            foreach($info as $v) {
                $pic['file'] = $v['savepath'].$v['savename']; //获取文件名
                $pic['smimg'] = $v['savepath'].'sm_'.$v['savename']; //获取缩略图的文件名
                $pic['img'] = $upload->rootPath.$v['savepath'].$v['savename']; //获取完整的图片地址
                $image = new \Think\Image(); // 利用tp的图片处理类对上传的图片进行处理
                $image->open($pic['img']);
                $image->thumb(160, 160)->save($upload->rootPath.$v['savepath'].'sm_'.$v[savename]); //生成160*160的缩略图
                $image->thumb(50, 50)->save($upload->rootPath.$v['savepath'].'xs_'.$v[savename]); //根据网站需要生成50*50的缩略图
                return $pic; //返回相关信息数组
            }
        }
    }



    public function edit_user(){
        $uid=I('uid');
        if($uid>0){
            $user = M('users')->where('userId=%d',$uid)->find();
            if($user){
                $Country = new CountryModel();
                $areasid = $Country ->get_list();
                $admin=$this->get_table('admin')->where('levelId=4 and isdeleted=0')->select();
                $this->assign('admin',$admin);
                $this->assign('user',$user);
                $this->assign('areasid',$areasid);
                $this->display('User/edit_user');
            }else{
                $this->error('不存在这个用户');
            }
        }else{
            $this->error('非法操作');
        }
    }

    public function edit_do(){
        $uid=$_GET['uid'];
        if($uid>0){
            $user = M('users')->where('userId=%d',$uid)->find();
            if($user){
                $nickName = I('nickName');
                $sex = I('sex');
                $usertel = I('usertel');
                $useremail = I('useremail');
                $lzhengNamen = I('lzhengNamen');
                $sfz = I('sfz');
                $country = I('country');
                $userday = I('userday');
                $adminId = I('adminId');
                $status = I('status');
                if($nickName or $sex or $usertel or $useremail or $useremail or $lzhengNamen or $sfz or $country or $userday or $adminId){
                    if($useremail){
                        if(!preg_match($this->email_role,$useremail)){
                            $this->error('邮箱格式不正确');
                        }
                    }
                    $user_ = $user;
                    $log ='修改了用户id为:'.$uid.',用户名为:'.$user['uname'].'的资料,';
                    if($nickName && $nickName!=$user['nickName']){
                        $user['nickName'] = $nickName;
                        $log.='修改昵称为:'.$nickName;
                    }
                    if($sex>=0 && $sex<=2 && $sex!=$user['sex']){
                        $user['sex']=$sex;
                        $log.='修改性别为:'.$sex;
                    }
                    if($usertel && $usertel != $user['usertel']){
                        $user['usertel']=$usertel;
                        $log.='修改电话为:'.$usertel;
                    }
                    if($useremail && $useremail != $user['useremail']){
                        $user['useremail']=$useremail;
                        $log.='修改邮箱为:'.$useremail;
                    }
                    if($lzhengNamen && $lzhengNamen != $user['lzhengNamen']){
                        $user['lzhengNamen'] = $lzhengNamen;
                        $log.='修改真实姓名为:'.$lzhengNamen;
                    }
                    if($sfz && $sfz != $user['sfz']){
                        $user['sfz'] = $sfz;
                        $log.='修改身份证为:'.$sfz;
                    }
                    if($country && $country!=$user['country']){
                        $user['country'] = $country;
                        $log.='修改户籍为:'.$country;
                    }
                    if($userday && $userday!=$user['userday']){
                        $user['userday'] = $userday;
                        $log.='修改生日为:'.$userday;
                    }
                    if($adminId>0 && $adminId!=$user['adminId']){
                        $user['adminId'] = $adminId;
                        $log.='修改业务员为(id):'.$adminId;
                    }
                    if($status>=0 && $status<=2 && $status!= $user['status']){
                        $user['status'] = $status;
                        $log.='修改状态为:'.$status;
                    }
                    if(array_diff_assoc($user_,$user)){
                        $result = M('users')->where('userId=%d',$user['userId'])->save($user);
                        if($result){
                            $this->write_log('修改用户资料',$log);
                            $this->success('修改用户资料成功');
                        }else{
                            $this->error('修改用户资料失败');
                        }
                    }else{
                        $this->error('资料一致无需保存');
                    }
                }else{
                    $this->error("参数不全");
                }
            }else{
                $this->error('不存在这个用户');
            }
        }else{
            $this->error('非法操作');
        }
    }

//end
}