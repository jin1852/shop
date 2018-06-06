<?php
namespace Admin\Controller;
use Think\Controller;
class AeditController extends CommonController {
    private $levelName=array(
            '1'=>'超级级管理员',
            '2'=>'高级管理员',
            '3'=>'中级管理员',
            '4'=>'初级管理员',
        );
    /*添加管理员信息修表的用户改页面展示*/
    public function gEdit(){
/*        $adminId=$_SESSION['admin']['adminId'];
        $gadd=M("glyadmins");
        $data=$gadd->where('adminId='.$adminId)->find();
*/      $model=M('glyadmins');
        $data=$model->where('adminId='.$_SESSION['admin']['adminId'])->find();
        //$data=$_SESSION['admin'];  
        if(!Empty($data)){
            $this->assign("data",$data);
            $this->display('Admin/gEdit');
        }else{
            $this->error("用户登录信息已失效，请重新登录",__APP__.'/Index/login');
        }
    }
    /*添加管理员信息表的用户修改实现*/
    public function gdo_edit(){
        $gadd=M("glyadmins");
        /*过滤字段值为空的的字段*/
        $data=array();
        foreach($_POST as $k=>$v){
            $v=trim($v);
            if(!empty($v)){
                if($k=='upwd'){
                    $data[$k]=md5($v);
                }else{
                    $data[$k]=$v;
                }
            }
        }
        $data['adminId']=$_SESSION['admin']['adminId'];
        $gadd->create($data);
        $r=$gadd->save();
        echo $r;
    }
}
