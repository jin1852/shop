<?php
namespace Admin\Controller;
use Think\Controller;
class WindowController extends CommonController {
    public function _initialize(){
        parent::_initialize();
        $access=\Home\Common\checkpower(3);
        if($access!==0){
            $this->error('权限不够');
        }
    }
    /*数据显示*/
    public function showWindow(){
        $show=M("indexccdhl");
        $data=$show->select();
        $showcc=M('cctptypes');
        $datacc=$showcc->select();
        $this->assign("data",$data);
        $this->assign("datacc",$datacc);
        $this->display('Window/window');
    }
    /*------------------------首页橱窗导航栏表操作--------------------------*/
    /*数据增加*/
    public function add(){
        $id=isset($_GET['indexCCId'])?$_GET['indexCCId']:0;
        $add=M("indexccdhl");
        if($id==0){
            $data=array();
            $data['indexCCId']=0;
            $data['indexCC']='根级目录';
        }else{
            $data=$add->where("indexCCId=".$id)->field('indexCCId,indexCC')->find();
        }
        $this->assign("data",$data);
        $this->display("Window/add");
    }
    public function do_add(){
        $doadd=M("indexccdhl");
        $doadd->create($_POST);
        $r=$doadd->add();
        if($r>0){
            $this->success('插入成功');
        }else{
            $this->error("插入数据出错",$_SERVER['HTTP_REFERER']);
        }
    }
    /*数据删除*/
    public function do_delete(){
        $dodelete=M('indexccdhl');
        $where="indexCCId=".$_POST['indexCCId'].' or indexCCPid='.$_POST['indexCCId'];
        $rid=$dodelete->where("indexCCPid=".$_POST['indexCCId'])->field('indexCCId')->select();
        $r=$dodelete->where($where)->delete();
        if($r==1){
            echo 1;
        }else if($r>1){
            $rid=json_encode($rid);
            echo $rid;
        }else{
            echo '-1';
        }

    }
    /*数据修改*/
    public function edit(){
        $edit=M('indexccdhl');
        $data=$edit->where($_GET)->find();
        /*取得一级类别*/
        $rdata=$edit->where("indexCCPid=0")->field("indexCCId,indexCC")->select();
        if(!empty($data)){
            $this->assign('rdata',$rdata);
            $this->assign('data',$data);
            $this->display('Window/edit');
        }else{
            $this->error('数据不存在');
        }
    }
    public function do_edit(){
        $edit=M('indexccdhl');
        $edit->create($_POST);
        $r=$edit->save();
        if($r>=0){
            $this->success('修改成功',U('Window/showWindow'));
        }else{
            $this->error("更新数据出错");
        }
    }
    /*----------------------首页橱窗导航栏表结束---------------------------*/
    /*----------------------首页橱窗橱窗大类别表操作------------------------*/
    public function ccadd(){
        $model=M('indexccdhl');
        $rdata=$model->select();
        $data=$this->order($rdata);
        /*echo "<pre>";
        print_r($data);*/
        if(empty($data)){
            $this->error("橱窗导航栏表为空");
        }else{
            $this->assign('data',$data);
            $this->display('Window/ccadd');
        }
    }
    public function do_ccadd(){
        $doccadd=M("cctptypes");
        $doccadd->create($_POST);
        $r=$doccadd->add();
        if($r>0){
            $this->success('添加成功');
        }else{
            $this->error("没有数据被添加");
        }
    }
    public function do_ccdelete(){
        $doccdelete=M("cctptypes");
        $r=$doccdelete->where($_POST)->delete();
        if($r){
            echo 1;
        }else{
            echo '-1';
        }
    }
    public function ccedit(){
        $ccedit=M("cctptypes");
        $model=M('indexccdhl');
        $rdata=$model->select();
        $data=$this->order($rdata);
        $ccdata=$ccedit->where($_GET)->find();
        if(!empty($ccdata)){
            $this->assign('data',$data);
            $this->assign('ccdata',$ccdata);
            $this->display('Window/ccedit');
        }else{
            $this->error('数据不存在');
        }
    }
    public function do_ccedit(){
        $doccedit=M("cctptypes");
        $doccedit->create($_POST);
        $r=$doccedit->save();
        if($r>=0){
            $this->success('修改成功',U('Window/showWindow'));
        }else{
            $this->error("更新数据出错");
        }
    }
    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        $table=I('get.table');
        if($id>0 && ($status==0 or $status==1)){
            $data['status']=$status;
            if($table==1){
                $data['indexCCId']=$id;
                $result =M("indexccdhl")->save($data);
            }else{
                $data['cctptypeId']=$id;
                $result =M("cctptypes")->save($data);
            }
            if($result){
                $this->success('操作成功',U('Window/showWindow'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }
    /*---------------------首页橱窗橱窗大类别表结束------------------------*/
    private function order($data,$pid=0){
        $arr=array();
        foreach ($data as $value) {
            if($value['indexCCPid']==$pid){
                $value['child']=$this->order($data,$value['indexCCId']);
                $arr[]=$value;
            }
        }
        return $arr;
    }
}

