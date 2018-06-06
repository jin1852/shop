<?php
namespace Admin\Controller;
use Admin\Model\ZtModel;
use Think\Controller;
class ZtconfController extends CommonController {
    private $name='子页样式';
    private $dir='productright';

    public function _initialize(){
        parent::_initialize();
        $this->check_super();
    }
    //子页样式展示
    public function index() {
        $count= $this->get_table('zt')->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $list = $this->get_table('zt')->limit($page->firstRow.','.$page->listRows)->order('prodTypeId asc,id desc')->select();
        foreach($list as &$one){
            $one['type']=$this->format_type($one['type']);
            $one['status_name']=$this->format_status($one['status']);
            if($one['view_img']) {
                $one['view_img'] = '/Public/Uploads/' . $this->dir . '/' . $one['view_img'];
            }
            if($one['prodTypeId']>0){
                $res=$this->get_table('protype')->where('proTypeId=%d',$one['prodTypeId'])->find();
                $one['proName']=$res['proName'];
            }
        }
        $this->page=$show;
        $this->title=$this->name;
        $this->list=$list;
        $this->display();
    }

    //修改子页样式
    public function edit() {
        $id=I('id');

        if($id){
            $data = $this->get_table('zt')->where('id=%d',$id)->find();
            if($data['view_img']) {
                $data['view_img'] = '/Public/Uploads/' . $this->dir . '/' . $data['view_img'];
            }

            if($data['prodTypeId']>0){
                $res=$this->get_table('protype')->where('proTypeId=%d',$data['prodTypeId'])->find();
                $data['proName']=$res['proName'];
            }
            $this->data=$data;
            $mod=new ZtModel();
            $view=$mod->get_view_list();
            $this->title=$this->name;
            $this->view_select=$view;
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //修改操作
    public function do_edit() {
        $id=I('post.id');
        $status=I('post.status');
        $name=I('post.name');
        $type=I('post.type');
        $view=I('post.view');
        $prodTypeId=I('post.prodTypeId');
        if($id>0){
            if($status==1 or $status==0){
                if($type==1 or $type==0){
                    $list=$this->get_table('zt')->where("id=%d",$id)->find();
                    if($list){
                        $old_list=$list;
                        $log = "子页样式 id为：" .$id;
                        if($status!=$list['status']){
                            $list['status']=$status;
                            $log.=$this->save_log('状态',$old_list['status'],$status);
                        }
                        if($type!=$list['type']){
                            $list['type']=$type;
                            $log.=$this->save_log('类型',$old_list['type'],$type);
                        }

                        if($_FILES['image']['name']){
                            $image= $this->upFile($this->dir);
                            $list['view_img']=$image;
                            $log.=',更新了图片';
                        }

//                        if($view){
//                            if($view!=$list['view']){
//                                $list['view']=$view;
//                                $log.=$this->save_log('样式',$old_list['view'],$view);
//                            }
//                        }else{
//                            $this->error("请选择样式");
//                        }

                        $this->check_view_repeat($view,$list['name'],$type,$id,$prodTypeId);
                        if(array_diff_assoc($list,$old_list)){
                            $re=$this->get_table("zt")->save($list);
                            if($re){
                                $this->write_log('修改子页样式',$log);
                                $this->success("保存成功",U('ZtConf/index'));
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
                    $this->error("请选择类型");
                }
            }else{
                $this->error('请选择状态');
            }
        }else{
            $this->error("非法操作");
        }
    }

    //检查是否重复
    private function check_view_repeat($view,$name,$type,$id=null,$prodTypeId=null){
        if(!$prodTypeId){
            $where="name ='".$name."' and view='".$view."' and type=".$type;
            if($id>0){
                $where.=' and id!='.$id;
            }
            $data=$this->get_table('zt')->where($where)->select();
            if($data){
                $this->error("已存在");
            }
        }
    }

    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1)){
            $data['id']=$id;
            $data['status']=$status;
            $result = $this->get_table('zt')->save($data);
            if($result){
                $this->status_log($status,$id,'子页样式');
                $this->success('操作成功',U('ZtConf/index'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }
    //end
}