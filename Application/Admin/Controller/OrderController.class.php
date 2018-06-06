<?php
/*
	*后台订单管理页
*/
namespace Admin\Controller;
use Think\Controller;
header('Content-type:text/html;charset=utf-8');
class OrderController extends CommonController {

    //查询所有的订单
    public function orderAll() {
        $LV=$_SESSION['admin']['levelId'];
        $orderno=I('orderno');
        $receiver=I('receiver');
        $where=$this->adminLV($LV);
        if(!empty($receiver)){
            $where.=" and receiver LIKE '%".$receiver."%'";
        }
        if(!empty($orderno)){
            $where.=" and orderno LIKE '%".$orderno."%'";
        }
        $count= $this->get_table('orders')->alias("o")->join("jd_users as u ON u.userId=o.userId")->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $list = $this->get_table('orders')->alias("o")->join("jd_users as u ON u.userId=o.userId")->where($where)->limit($page->firstRow.','.$page->listRows)->order('orderId desc')->select();
        $this->page=$show;
        $this->assign('list',$list);
        $this->display('Order/order');
    }

    //修改订单展示
    public function updateOrder() {
        $data = I('param.');
        $list = $this->get_table('orders')->where($data)
            ->field('jd_orders.*,jd_users.uname')
            ->join('__USERS__ on __USERS__.userId = __ORDERS__.userId')
            ->find();
        //分配变量
        $url = $_SERVER['HTTP_REFERER'];
        $this->list=$list;
        $this->url=$url;
        //加载模板
        $this->display('editOrder');
    }

    //开始寄样
    public function sample(){
        $orderId = I('orderId');
        if ($orderId) {
            $res = $this->get_table('orders')->where('orderId=%d', $orderId)->find();
            if ($res['send_sample'] == 1) {
                if ($res['sample_status'] == 1) {
                    $this->error("已经是开始寄样");
                } else {
                    $res['sample_status'] = 1;
                    $save = $this->get_table('orders')->save($res);
                    if ($save) {
                        $text=$this->format_sample_status(1);
                        $this->write_log('修改订单','寄样状态修改为“'.$text.'” id为: '.$orderId.' 的订单');
                        $this->success("成功寄样");
                    } else {
                        $this->error("寄样失败");
                    }
                }
            } else {
                $this->error("此用户没有要求寄样");
            }

        } else {
            $this->error("非法操作");
        }
    }

    //修改订单状态
    public function orderchannge(){
        $orderId=I('get.orderId');
        $orderState=I('get.orderState');
        if($orderId && $orderState){
            if($orderState==1){
                $data=$this->get_table('orders')->where('orderId=%d',$orderId)->find();
                if($data['send_sample']==1){
                    if($data['sample_status']==0){
                        $this->error("用户要求寄样，请先寄样");
                    }
                }
            }
            $data['orderState']=$orderState;
            $order=$this->get_table('orders')->where('orderId=%d',$orderId)->save($data);
            if($order){
                $text=$this->format_orderState($data['orderState']);
                $this->write_log('修改订单','状态修改为“'.$text.'” id为: '.$orderId.' 的订单');
                $this->success('修改成功');
            }else{
                $this->error("修改失败");
            }
        }else{
            $this->error("非法操作");
        }
    }

    //修改订单
    public function orderUpdate() {
        $orderId=I('post.orderId');
        $url=I('post.url');
        $receiver=I('post.receiver');
        $tel=I('post.tel');
        $email=I('post.email');
        $orderState=I('post.orderState');
        $address=I('post.address');
        if($orderId>0 &&($receiver or $tel or $email or $orderState or $address)){
            $list=$this->get_table('orders')->where("orderId=%d",$orderId)->find();
            if($list){
                $log = "编辑订单 id为：" .$orderId;
                $old_list=$list;
                if($receiver && $list['receiver']!=$receiver){
                    $list['receiver']=$receiver;
                    $log.=$this->save_log('收货人',$old_list['receiver'],$list['receiver']);
                }
                if($tel && $list['teh']!=$tel){
                    $list['tel']=$tel;
                    $log.=$this->save_log('联系方式',$old_list['teh'],$list['teh']);
                }

                if($email && $list['email']!=$email){
                    $list['email']=$email;
                    $log.=$this->save_log('邮箱',$old_list['email'],$list['email']);
                }
                if(($orderState==0 or $orderState==3) && $list['orderState']!=$orderState){
                    $list['orderState']=$orderState;
                    $log.=$this->save_log('订单状态',$old_list['orderState'],$list['orderState']);
                }
                if($address && $list['address']!=$address){
                    $list['address']=$address;
                    $log.=$this->save_log('收货地址',$old_list['address'],$list['address']);
                }
                if(array_diff_assoc($list,$old_list)){
                    $re=$this->get_table("orders")->save($list);
                    if($re){
                        $this->write_log('修改订单',$log);
                        $this->success("保存成功",$url);
                    }else{
                        $this->success("保存失败");
                    }
                }else{
                    $this->error("新旧数据一致，不需要保存");
                }
            }else{
                $this->error("数据不存在");
            }
        }else{
            $this->error("参数有误");
        }
    }
    //查询订单详情
    public function detail() {
        $data = I('param.');
        $detail = M('Details');
        //统计总价
        $total = $detail
            ->where('jd_details.orderId='.$data['orderId'])
            ->join('__ORDERS__ on __DETAILS__.orderId = __ORDERS__.orderId')
            ->field('SUM(amount*price) as tsum')
            ->select();
        $list = $detail
            ->where('jd_details.orderId='.$data['orderId'])
            ->field('jd_details.*,jd_details.price as p,jd_details.amount as a,jd_orders.*,jd_prods.*,jd_products.*,jd_users.uname')
            ->join('__PRODUCTS__ on __DETAILS__.productId = __PRODUCTS__.productId')
            ->join('__PRODS__ on __PRODS__.prodId = __PRODUCTS__.prodId')
            ->join('__ORDERS__ on __DETAILS__.orderId = __ORDERS__.orderId')
            ->join('__USERS__ on __ORDERS__.userId = __USERS__.userId')
            ->select();
        $this->list=$list;
        $this->total=$total;
        $this->display('Order/detail');
    }

    //查询所有众筹订单
    public function zc_orderAll(){
        $LV=$_SESSION['admin']['levelId'];
        $receiver=I('receiver');
        $orderno=I('orderno');
        $where=$this->adminLV($LV);
        if(!empty($receiver)){
            $where.=" and o.receiver LIKE '%".$receiver."%'";
        }
        if(!empty($orderno)){
            $where.=" and o.orderno LIKE '%".$orderno."%'";
        }
        $count=$this->get_table('zc_orders')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $list=$this->get_table('zc_orders')
            ->alias("o")
            ->join("jd_users as u ON u.userId=o.userId")
            ->field("o.*,u.uname")
            ->where($where)
            ->order("o.orderId desc")
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        foreach($list as &$one){
            $one['orderTime_date']=$this->time_format($one['orderTime']);
        }
        $this->page=$show;
        $this->list=$list;
        $this->display('Order/zc_orderAll');
    }
    //修改众筹订单展示
    public function update_ZcOrder() {
        $id=I('get.orderId');
        if($id){
            $url = $_SERVER['HTTP_REFERER'];
            $list=$this->get_table('zc_orders')->alias('o')->join("jd_users as u ON u.userId=o.userId")->where("o.orderId=%d",$id)->find();
            $this->list=$list;
            $this->url=$url;
            $this->display('editZcOrder');
        }else{
            $this->error("非法操作");
        }
    }
    //修改众筹订单
    public function Zcorderchannge(){
        $orderId=I('get.orderId');
        $orderState=I('get.orderState');
        if($orderId && $orderState){
            if($orderState==1){
                $data=$this->get_table('zc_orders')->where('orderId=%d',$orderId)->find();
                if($data['send_sample']==1){
                    if($data['sample_status']==0){
                        $this->error("用户要求寄样，请先寄样");
                    }
                }
            }
            $data['orderState']=$orderState;
            $order=$this->get_table('zc_orders')->where('orderId=%d',$orderId)->save($data);
            if($order){
                $text=$this->format_orderState($data['orderState']);
                $this->write_log('修改订单','状态修改为“'.$text.'” id为: '.$orderId.' 的订单');
                $this->success('修改成功');
            }else{
                $this->error("修改失败");
            }
        }else{
            $this->error("非法操作");
        }
    }
    //修改众筹订单状态
    public function ZcorderUpdate() {
        $id=I('post.id');
        $url=I('post.url');
        $receiver=I('post.receiver');
        $tel=I('post.tel');
        $hometel=I('post.hometel');
        $email=I('post.email');
        $orderState=I('post.orderState');
        $address=I('post.address');
        $addressbie=I('addressbie');
        if($id>0 &&($receiver or $tel or $email or $orderState or $address or $hometel or $addressbie)){
            $list=$this->get_table('zc_orders')->where("orderId=%d",$id)->find();
            if($list){
                $old_list=$list;
                $log = "编辑众筹订单 id为：" .$id;
                if($receiver && $list['receiver']!=$receiver){
                    $list['receiver']=$receiver;
                    $log.=$this->save_log('收货人',$old_list['receiver'],$list['receiver']);
                }
                if($tel && $list['teh']!=$tel){
                    $list['tel']=$tel;
                    $log.=$this->save_log('联系方式',$old_list['teh'],$list['teh']);
                }
                if($hometel && $list['hometel']!=$hometel){
                    $list['hometel']=$hometel;
                    $log.=$this->save_log('家庭电话',$old_list['hometel'],$list['hometel']);
                }
                if($email && $list['email']!=$email){
                    $list['email']=$email;
                    $log.=$this->save_log('邮箱',$old_list['email'],$list['email']);
                }
                if(($orderState==0 or $orderState==3) && $list['orderState']!=$orderState){
                    $list['orderState']=$orderState;
                    $log.=$this->save_log('订单状态',$old_list['orderState'],$list['orderState']);
                }
                if($address && $list['address']!=$address){
                    $list['address']=$address;
                    $log.=$this->save_log('收货地址',$old_list['address'],$list['address']);
                }
                if($addressbie && $list['addressbie']!=$addressbie){
                    $list['addressbie']=$addressbie;
                    $log.=$this->save_log('地址别名',$old_list['addressbie'],$list['addressbie']);
                }
                if(array_diff_assoc($list,$old_list)){
                    $re=$this->get_table("zc_orders")->save($list);
                    if($re){
                        $this->write_log('修改众筹订单',$log);
                        $this->success("保存成功",$url);
                    }else{
                        $this->success("保存失败");
                    }
                }else{
                    $this->error("新旧数据一致，不需要保存");
                }

            }else{
                $this->error("数据不存在");
            }
        }else{
            $this->error("参数有误");
        }
    }
    //查询众筹订单详情
    public function Zc_detail() {
        $id=I('get.orderId');
        if($id){
            $list=$this->get_table('zc_orders')->alias('o')->join("jd_users as u ON u.userId=o.userId")->where("o.orderId=%d",$id)->find();
            $list['orderTime_date']=$this->time_format($list['orderTime']);
            $total=$this->get_table('zc_order_details')->alias('d')->join("jd_zc_orders as o ON o.orderId=d.orderId")->where("d.orderId=%d",$id)->field('SUM(d.number*d.price) as tsum')->select();
            $data=$this->get_table('zc_order_details')->alias('d')->join("jd_zc as z ON z.id=d.zc_id")->where("d.orderId=%d",$id)->field('d.number,d.price ,(d.number*d.price) as p,z.id,z.image,z.title')->select();
            $this->data=$data;
            $this->list=$list;
            $this->total=$total;
            $this->display('Order/Zc_detail');
        }else{
            $this->error("非法操作");
        }
    }
    //众筹开始寄样
    public function Zc_sample(){
        $orderId=I('orderId');
        if($orderId){
            $res=$this->get_table('zc_orders')->where('orderId=%d',$orderId)->find();
            if($res['send_sample']==1){
                if($res['sample_status']==1){
                    $this->error("已经是开始寄样");
                }else{
                    $res['sample_status']=1;
                    $save=$this->get_table('zc_orders')->save($res);
                    if($save){
                        $text=$this->format_sample_status(1);
                        $this->write_log('修改订单','寄样状态修改为“'.$text.'” id为: '.$orderId.' 的订单');
                        $this->success("成功寄样");
                    }else{
                        $this->error("寄样失败");
                    }
                }
            }else{
                $this->error("此用户没有要求寄样");
            }

        }else{
            $this->error("非法操作");
        }


    }
    function adminLV($LV){
        $where=1;
        if($LV>2){
            $user=$this->get_table('users')->where('adminId=%d',$_SESSION['admin']['adminId'])->field('userId')->select();
            $userId='';
            foreach($user as &$one){
                if(!empty($userId)){
                    $userId.=",".$one['userId'];
                }else{
                    $userId.=$one['userId'];
                }
            }
            $where.=" and o.userId in (".$userId.")";
        }
        return $where;
    }
    //end
}