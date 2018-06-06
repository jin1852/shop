<?php
/*
	*后台订单管理页
*/
namespace Admin\Controller;
use Think\Controller;
header('Content-type:text/html;charset=utf-8');
class EnquiryController extends CommonController {

    //查询所有的询价单
    public function orderAll() {
        $LV=$_SESSION['admin']['levelId'];
        $orderno=I('orderno');
//        $receiver=I('receiver');
        $where=$this->adminLV($LV);
//        if(!empty($receiver)){
//            $where.=" and receiver LIKE '%".$receiver."%'";
//        }
        if(!empty($orderno)){
            $where.=" and o.i_number LIKE '%".$orderno."%'";
        }
        $count= M('inquiry')->alias("o")->join("jd_users as u ON u.userId=o.userId")
            ->field('o.*,u.uname,u.useremail')
            ->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $list = M('inquiry')->alias("o")->join("jd_users as u ON u.userId=o.userId")
            ->field('o.*,u.uname,u.useremail')
            ->where($where)->limit($page->firstRow.','.$page->listRows)->order('inqid desc')->select();
        //var_dump(M()->getLastSql());
        //var_dump($list);
        $this->page=$show;
        $this->assign('list',$list);
        $this->display('Enquiry/order');
    }

    //修改询价单状态
    public function orderchannge(){
        $orderId=I('get.inqid');
        $orderState=I('get.status');
        if($orderId && $orderState){
            if($orderState==1 or $orderState==2){
                $data=M('inquiry')->where('inqid=%d',$orderId)->find();
                if($data['status']==0) {
                    $data['status'] = $orderState;
                    $order = M('inquiry')->where('inqid=%d', $orderId)->save($data);
                    if ($order) {
                        $this->write_log('修改询价单', '状态修改为“' . $orderState . '” id为: ' . $orderId . ' 的订单');
                        $this->success('修改询价单成功');
                    } else {
                        $this->error("修改询价单失败");
                    }
                }else{
                    $this->error("非法操作3");
                }
            }else{
                $this->error("非法操作2");
            }
        }else{
            $this->error("非法操作");
        }
    }


    //查询询价单详情
    public function detail() {
        $data = I('param.');
        $detail = M('inquiry_shop');
        //统计总价
        $inq = M('inquiry')->alias('i')
            ->join('left join jd_users AS u ON i.userId=u.userId')
            ->join('left join jd_address AS a ON i.address_id=a.addressId')
            ->where('inqid='.$data['inqid'])
            ->find();
        //var_dump(M()->getLastSql());
        $list = $detail->alias('i')
            ->join('left join jd_products as ps on i.productId=ps.productId')
            ->join('left join jd_prods as p on p.prodId=ps.prodId')
            ->field('i.*,p.simimg,p.prodNO')
            ->where('i.inqid='.$data['inqid'])
            ->select();
       // var_dump(M()->getLastSql());
        //$al = M('address')->where('userId=' . $inq['userId'])->select();
        $this->list = $list;
        $this->inq = $inq;
        //$this->al = $al;
        $this->display('Enquiry/detail');
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