<?php
/*
	*后台订单管理页
*/
namespace Admin\Controller;
use Think\Controller;
header('Content-type:text/html;charset=utf-8');
class SourcingController extends CommonController {

    //查询所有的需求单
    public function orderAll() {
        $LV=$_SESSION['admin']['levelId'];
        $orderno=I('orderno');
//        $receiver=I('receiver');
        $where=$this->adminLV($LV);
//        if(!empty($receiver)){
//            $where.=" and receiver LIKE '%".$receiver."%'";
//        }
        if(!empty($orderno)){
            $where.=" and o.sn LIKE '%".$orderno."%'";
        }
        $count= M('sourcing')->alias("o")
            ->join("jd_users as u ON u.userId=o.userId")
            ->field('o.*,u.uname,u.useremail')
            ->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $list = M('sourcing')->alias("o")->join("jd_users as u ON u.userId=o.userId")
            ->field('o.*,u.uname,u.useremail')
            ->where($where)->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
        //var_dump(M()->getLastSql());
//        var_dump($list);
        $this->page=$show;
        $this->assign('list',$list);
        $this->display('Sourcing/order');
    }



    //查询询价单详情
    public function detail() {
        $data = I('param.');
        $detail = M('sourcing_item');
        //统计总价
        $inq = M('sourcing')->alias("o")
            ->join("jd_users as u ON u.userId=o.userId")
            ->field('o.*,u.uname,u.useremail,u.usertel')
            ->where('id='.$data['id'])
            ->find();
        //var_dump(M()->getLastSql());
        $list = $detail->where('sourcing_id='.$data['id'])
            ->select();
        //var_dump(M()->getLastSql());
        if($list){
            foreach ($list as &$one){
                if($one['attach_json']){
                    $one['json_'] = json_decode($one['attach_json'],true);
                }else{
                    $one['json_'] = array();
                }

            }
        }
        $this->list = $list;
        $this->inq = $inq;
        $this->display('Sourcing/detail');
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