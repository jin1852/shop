<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends CommonController {

    /*访问权限*/
    public function _initialize(){
        parent::_initialize();
        if($_SESSION['admin']['levelId']>2){
            $this->error('权限不够');
        }
    }
    /*所有权限表信息展示*/
    public function index(){
        $where='a.levelId!=1 and isdeleted<2';
        if($_SESSION['admin']['levelId']!=1){
            $where.=' and a.levelId=4';
        }
        $count=$this->get_table('admin')->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $gdatalist=$this->get_table('admin')
            ->alias("a")
            ->join('jd_levels AS l ON l.levelId=a.levelId')
            ->where($where)
            ->field('a.*,l.levelName')
            ->order('levelId asc ,adminId desc')
            ->limit($page->firstRow.','.$page->listRows)
            ->select();
        foreach($gdatalist as &$one){
            $one['status']=$this->format_isdeleted($one['isdeleted']);
        }
        $this->title='管理员列表表';
        $this->page=$show;
        $this->gdatalist=$gdatalist;
        $this->display('Admin/index');
    }
    /*添加管理员界面*/
    public function register(){
        $data=$this->get_table('levels')->where('status=1 and levelId>'.$_SESSION['admin']['levelId'])->select();
        $option='';
        foreach($data as &$one){
            if($one['num']==-1){
                $option.="<option  value='".$one['levelId']."'>".$one['levelName']."</option>";
            }else{
                $count=$this->get_table('admin')->where('levelId=%d and 	isdeleted<2',$one['levelId'])->count();
                if($one['num']>$count){
                    $option.="<option  value='".$one['levelId']."'>".$one['levelName']."</option>";
                }
            }
        }
        $this->optino=$option;
        $this->display();
    }

    /*管理员信息表，用户名信息ajax验证*/
    public function gdo_addtest(){
        $gadd=M("glyadmins");
        $r=$gadd->where($_POST)->find();
        if(empty($r)){
            echo 0;
        }else{
            echo 1;
        }
    }
    /*添加管理员信息，数据库是实现*/
    public function gdo_add(){
        $gadd=M("glyadmins");
        if($_SESSION['admin']['levelId']>$_POST['levelId']){
            echo 0;
        }else{
            $gadd->create($_POST);
            $gadd->upwd=md5($_POST['upwd']);
            $r=$gadd->add();
            $loginCount=M('logincount');
            $data['adminId']=$r;
            $data['lastTime']=0;
            $loginCount->create($data);
            $loginCount->add();
            $this->write_log('添加管理员','添加管理员:'.$_POST['uname']);
            echo $r;
        }
    }
    /*添加管理员信息修改页面展示*/
    public function gaEdit(){
        $adminId=I('get.adminId');
        $data=$this->get_table('admin')->where('adminId=%d',$adminId)->find();
        $level=$this->get_table('levels')->where('status=1 and levelId>'.$_SESSION['admin']['levelId'])->select();
        $option='';
        foreach($level as &$one){
            if($one['num']==-1){
                $option.="<option  value='".$one['levelId']."'>".$one['levelName']."</option>";
            }else{
                $count=$this->get_table('admin')->where('levelId=%d and 	isdeleted<2',$one['levelId'])->count();
                if($one['num']>$count){
                    $option.="<option  value='".$one['levelId']."'>".$one['levelName']."</option>";
                }
            }
        }
        if($data['levelId']<$_SESSION['admin']['levelId']){
            $this->error("权限不够");
            return;
        }
        $url= $_SERVER['HTTP_REFERER'];
        $this->data=$data;
        $this->url=$url;
        $this->option=$option;
        $this->display('Admin/gaEdit');
    }
    /*添加管理员信息修改实现*/
    public function gado_edit(){
        $gadd = M("glyadmins");
        $adminId=I('post.adminId');
        $upwd=I('post.upwd');
        $r_upwd=I('post.r_upwd');
        $nickName=I('post.nickName');
        $levelId=I('post.levelId');
        $data = $gadd->where('adminId=' . $adminId)->find();
        if ($adminId != $_SESSION['admin']['adminId'] && $_SESSION['admin']['levelId'] >= $data['levelId'] ) {
            echo -1;
        }else{
            $log = "管理员 id为：" . $adminId;
            if(!empty($upwd) && !empty($r_upwd) && $upwd==$r_upwd){
                if(md5($upwd)!=$data['upwd']){
                    $data['upwd']=md5($upwd);
                    $log .= "，修改密码";
                }
            }
            if($nickName){
                if($data['nickName']!=$nickName){
                    $data['nickName']=$nickName;
                    $log .= "，名称修改为".$nickName;
                }
            }
            if($levelId>0){
                if($data['levelId']!=$levelId){
                    $data['levelId']=$levelId;
                    $log .= "，权限级别修改为".$levelId;
                }
            }
            $this->write_log('修改管理员',$log);
            $r = $gadd->save($data);
            echo $r;
        }
    }

    //开关操作
    public function status(){
        $id=I('get.id');
        $isdeleted=I('get.status');
        if($id>0 && ($isdeleted==0 or $isdeleted==1)){
            $data['adminId']=$id;
            $data['isdeleted']=$isdeleted;
            $result = $this->get_table('admin')->save($data);
            if($result){
                if($isdeleted==1){
                    $text='禁用';
                }else{
                    $text='开启';
                }
                $this->write_log('状态开关',$text.'id为'.$id.'的管理员');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }

    //删除
    public function del(){
        $id=I('get.id');
        if($_SESSION['admin']['levelId']==1){
            if($id>0){
                $data['adminId']=$id;
                $data['isdeleted']=2;
                $result = $this->get_table('admin')->save($data);
                if($result){
                    $this->write_log('删除管理员','删除id为'.$id.'的管理员');
                    $this->success('操作成功');
                }else{
                    $this->error('操作失败');
                }
            }else{
                $this->error("非法操作");
            }
        }else{
            $this->error("权限不足");
        }
    }
    //管理员角色管理列表
    public function admin_level(){
        $levelName=I('levelName');
        $where='status<2';
        if(!empty($levelName)){
            $where.=" and levelName LIKE '%".$levelName."%'";
        }
        $count=$this->get_table('levels')->where($where)->count();
        $page=$this->get_page($count,10);
        $show = $page->show(); //分页显示输出
        $data=$this->get_table('levels')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        foreach($data as &$one){
            if($one['num']==-1){
                $one['num']='无限';
            }
            $one['status_name']=$this->format_status($one['status']);
            $one['sum']=$this->get_table('admin')->where('levelId=%d and isdeleted<2',$one['levelId'])->count();
        }
        $this->title='管理员角色';
        $this->data=$data;
        $this->page=$show;
        $this->display();
    }

    //管理员角色修改页面
    public function leveledit(){
        $levelId=I('levelId');
        if($levelId){
            $data=$this->get_table('levels')->where('levelId=%d',$levelId)->find();
            if($data['num']==-1){
                $data['num']='';
            }
            $this->title='管理员角色';
            $this->data=$data;
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //管理员角色修改操作
    public function do_leveledit(){
        $levelId=I('post.levelId');
        $levelName=I('post.levelName');
        $status=I('post.status');
        $num=I('post.num');
        if($levelId){
            $list=$this->get_table('levels')->where("levelId=%d",$levelId)->find();
            $old_list=$list;
            $log = "管理员角色 id为：" . $list['id'];
            if($levelName){
                $levelName=$this->filter_str($levelName);
                if($list['levelName']!=$levelName){
                    $has=$this->get_table('levels')->where("levelId!=%d and levelName='%s'",$levelId,$levelName)->select();
                    if($has){
                        $this->error($levelName."已存在");
                    }else{
                        $list['levelName']=$levelName;
                        $log .= "，角色名称修改为".$levelName;
                    }
                }
            }else{
                $this->error("未填写角色名称");
            }
            if(($status==0 or $status==1) && $list['status']!=$status){
                $list['status']=$status;
            }
            if($num>0){
                if($list['num']!=$num){
                    $list['num']=$num;
                    $log .= "，人数限制修改为".$num;
                }
            }else{
                $list['num']=-1;
                $log .= "，人数限制修改为无限制";
            }
            if(array_diff_assoc($list,$old_list)){
                $re=$this->get_table("levels")->save($list);
                if($re){
                    $this->write_log('管理员角色编辑',$log);
                    $this->success('数据更新成功',U('Admin/admin_level'));
                }else{
                    $this->error("保存失败");
                }
            }else{
                $this->error("新旧数据一致，不需要保存");
            }
        }else{
            $this->error("非法操作");
        }
    }

    //添加管理员角色
    public function leveladd(){
        $this->title='管理员角色';
        $this->display();
    }

    //添加管理员角色操作
    public function do_leveladd(){
        $levelName=I('post.levelName');
        $num=I('post.num');
        if($levelName){
            $data['levelName']=$this->check_repeat($levelName,'levelName','levels');
        }else{
            $this->error("请填写角色名称");
        }
        if($num){
            $data['num']=$num;
            $text_num=$num;
        }else{
            $data['num']=-1;
            $text_num='无限';
        }
        $res = $this->get_table('levels')->add($data);
        if($res){
            $this->write_log('添加管理员角色',"添加管理员角色，角色名称 ：".$levelName."，人数限制 ：".$text_num);
            $this->success("添加成功",U('Admin/admin_level'));
        }else{
            $this->error("添加失败");
        }
    }

    //管理员角色开关操作
    public function level_status(){
        $levelId=I('get.id');
        $status=I('get.status');
        if($levelId>0 && ($status==0 or $status==1 or $status==2)){
            $data['levelId']=$levelId;
            $data['status']=$status;
            $result = $this->get_table('levels')->save($data);
            if($result){
                $this->status_log($status,$levelId,'管理员角色');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }

//    end
}
