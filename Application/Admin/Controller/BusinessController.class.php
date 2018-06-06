<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;

class BusinessController extends CommonController {

    private $dir='business';

    private $name='厂商';

    public function _initialize(){
        parent::_initialize();
        $this->check_1or3();
    }
    //厂商列表页
    public function index(){
        $title=I('title');
        $where='status<2';
        if(!empty($title)){
            $where.=" and title LIKE '%".$title."%'";
        }
        $count=$this->get_table('business')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data=$this->get_table('business')->where($where)->order('id desc')->limit($page->firstRow.','.$page->listRows)->select();
        foreach($data as &$one){
            $one['create_time']=$this->time_format($one['create_time']);
            $one['updata_time']=$this->time_format($one['updata_time']);
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
            $data=$this->get_table('business')->where('id=%d',$id)->find();
            if($data){
                $data['image']=$this->full_url().$this->dir."/l_".$data['image'];
                $data['link']=$this->not_top($data['link']);
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
        $title=I('post.title');
        $content=I('post.content');
        $top=I('post.top');
        $link=I('post.link');
        $data['image']= $this->upFile($this->dir);
        if($title){
            $title=$this->filter_str($title);
            $data['title']=$this->check_repeat($title,'title','business');
        }else{
            $this->error("请填写厂商名称");
        }
        if($content){
            $content= $this->filter_str($content);
            $data['content']=htmlspecialchars_decode(html_entity_decode($content));
        }
        if($link){
            $data['link']=$link;
        }else{
            $data['link']='#';
        }
        if($top>=0){
            $data['top']=$top;
        }else{
            $data['top']=0;
        }
        $data['create_time']=time();
        $add=$this->get_table('business')->add($data);
        if($add){
            $this->write_log('添加厂商','添加厂商,厂商名称;'.$title);
            $this->success('添加成功',U('Business/index'));
        }else{
            $this->error('添加失败');
        }
    }
    //修改操作
    public function do_edit(){
        $id=I('post.id');
        $url=I('post.url');
        $title=I('post.title');
        $content=I('post.content');
        $top=I('post.top');
        $link=I('post.link');
        $status=I('post.status');
        if($id>0){
            $list=$this->get_table('business')->where('id=%d',$id)->find();
            if($list){
                $old_list=$list;
                $log = "编辑厂商 id为：" . $id;
                if($title){
                    if($list['title']!=$title){
                        $title=$this->filter_str($title);
                        $list['title']=$this->check_repeat($title,'title','business',$id,'id');
                        $log.=$this->save_log('厂商名称',$old_list['title'],$title);
                    }
                }else{
                    $this->error("请填写厂商名称");
                }
                if($list['content']!=$content){
                    $content=htmlspecialchars_decode(html_entity_decode($this->filter_str($content)));
                    if($content){
                        $list['content']=$content;
                    }else{
                        $list['content']='';
                    }
                    $log.=',修改了介绍';
                }

                if(!$link){$link='javascript:void(0);';}
                if($link!=$list['link']){
                    $list['link']=$link;
                    $log.=$this->save_log('链接',$old_list['link'],$link);
                }
                if(!$top){$top=0;}
                if($top!=$list['top']){
                    $list['top']=$top;
                    $log.=$this->save_log('排序值',$old_list['top'],$top);
                }

                if(($status==0 or $status==1) && $list['status']!=$status){
                    $log.=$this->save_log('状态',$old_list['status'],$list['status']);
                    $list['status']=$status;
                }
                if($_FILES['image']['name']){
                    $image= $this->upFile($this->dir);
                    $list['image']=$image;
                    $log.=',更新了图片';
                }
                if(array_diff_assoc($list,$old_list)){
                    $list['updata_time']=time();
                    $re=$this->get_table("business")->save($list);
                    if($re){
                        if($image){
                            $this->del_img($this->dir,$old_list['image']);
                        }
                        $this->write_log('修改厂商',$log);
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
            $data['updata_time']=time();
            $result = $this->get_table('business')->save($data);
            if($result){
                $this->status_log($status,$id,'厂商');
                $this->success('操作成功',U('Business/index'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }
    //end
}