<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;

class AuthenticationController extends CommonController {
    private $name='认证';
    private $dir='authentication';

    public function _initialize(){
        parent::_initialize();
        $this->check_1or3();
    }

    //列表页
    public function index(){
        $title=I('title');
        $where='status<2';
        if(!empty($title)){
            $where.=" and title LIKE '%".$title."%'";
        }
        $count=$this->get_table('authentication')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data=$this->get_table('authentication')->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
        foreach($data as &$one){
            $one['status_name']=$this->format_status($one['status']);
            $one['image']=$this->full_url().$this->dir."/m_".$one['image'];
        }
        $this->title=$this->name;
        $this->page=$show;
        $this->data=$data;
        $this->display();
    }

    //添加页面
    public function add(){
        $this->title=$this->name;
        $this->display();
    }

    //修改页面
    public function edit(){
        $id=I('get.id');
        if($id>0){
            $data=$this->get_table('authentication')->where('id=%d',$id)->find();
            if($data){
                $data['top']=$this->not_top($data['top']);
                $data['image']=$this->full_url().$this->dir."/l_".$data['image'];
                $url= $_SERVER['HTTP_REFERER'];
                $this->url=$url;
                $this->data=$data;
                $this->title=$this->name;
                $this->display();
            }else{
                $this->error("数据不存在");
            }

        }else{
            $this->error("非法操作");
        }
    }

    //添加操作
    public function do_add(){
        $top=I('post.top');
        $title=I('post.title');
        if($title){
            $title=$this->filter_str($title);
            $data['title']=$this->check_repeat($title,'title','authentication');
        }else{
            $this->error('请输入名称');
        }
        if($top>=0){
            $data['top']=$top;
        }else{
            $data['top']=0;
        }
        $data['image']= $this->upFile($this->dir);
        $add=$this->get_table('authentication')->add($data);
        if($add){
            $this->write_log('添加认证','添加认证,认证名称'.$title);
            $this->success('添加成功',U('authentication/index'));
        }else{
            $this->error('添加失败');
        }
    }

    //修改操作
    public function do_edit(){
        $id=I('post.id');
        $url=I('post.url');
        $title=I('post.title');
        $top=I('post.top');
        $status=I('post.status');
        if($id>0){
            $list=$this->get_table('authentication')->where('id=%d',$id)->find();
            if($list){
                $old_list=$list;
                $log = "编辑认证 id为：" . $list['id'];
                if($title){
                    if($list['title']!=$title){
                        $title=$this->filter_str($title);
                        $list['title']=$this->check_repeat($title,"title","authentication",$id,'id');
                        $log.=$this->save_log('名称',$old_list['title'],$title);
                    }
                }else{
                    $this->error("请输入名称");
                }
                if(!$top){$top=0;}
                if($top!=$list['top']){
                    $list['top']=$top;
                    $log.=$this->save_log('排序值',$old_list['top'],$top);
                }
                if(($status==0 or $status==1) && $list['status']!=$status){
                    $list['status']=$status;
                    $log.=$this->save_log('状态',$old_list['status'],$status);
                }
                if($_FILES['image']['name']){
                    $image= $this->upFile($this->dir);
                    $list['image']=$image;
                    $log.=',更新了图片';
                }
                if(array_diff_assoc($list,$old_list)){
                    $re=$this->get_table("authentication")->save($list);
                    if($re){
                        if($image){
                            $this->del_img($this->dir,$old_list['image']);
                        }
                        $this->write_log('修改认证',$log);
                        $this->success("保存成功",$url);
                    }else{
                        $this->error("保存失败");
                    }
                }else{
                    $this->error("新旧数据一致，不需要保存");
                }
            }else{
                $this->error("数据不存在");
            }
        }else{
            $this->error("非法操作");
        }
    }
    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1 or $status==2)){
            $data['id']=$id;
            $data['status']=$status;
            $result = $this->get_table('authentication')->save($data);
            if($result){
                $this->status_log($status,$id,'认证');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }
//end
}