<?php
namespace Admin\Controller;
use Think\Controller;
class AreasidController extends CommonController {

    private $name='城市';

    public function _initialize(){
        parent::_initialize();
        $this->check_super();
    }

    //城市展示
    public function index(){
        $diqu=I('diqu');
        $where='status<2';
        if(!empty($diqu)){$where.=" and en_name LIKE '%".$diqu."%'";}
        $count=$this->get_table('country')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show();
        $data = $this->get_table('country')->where($where)->limit($page->firstRow.','.$page->listRows)->order('en_name asc')->select();
        foreach($data as &$one){
            $one['status_name']=$this->format_status($one['status']);
        }
        $this->title=$this->name;
        $this->page=$show;
        $this->data=$data;
        $this->display();
    }

    //添加城市页面
    public function add(){
        $this->title=$this->name;
        $this->display();
    }

    //城市添加操作
    function add_doit(){
        $en_name=I('post.en_name');
        if($en_name){
            $data['en_name']=$en_name;
            $this->check_repeat($en_name,'en_name','country');
            $res = $this->get_table('country')->add($data);
            if($res){
                $this->write_log('添加'.$this->name,"添加".$this->name.$en_name);
                $this->success($this->name.'添加成功',U('Areasid/index'));
            }else{
                $this->error($this->name.'添加失败',U('Areasid/index'));
            }
        }else{
            $this->error("请填写".$this->name."名称");
        }
    }

    //编辑城市页面
    public function edit(){
        $id = I('get.id');
        $Data = $this->get_table('country')->where('id='.$id)->find();
        $url= $_SERVER['HTTP_REFERER'];
        $this->url=$url;
        $this->title=$this->name;
        $this->Data=$Data;
        $this->display();
    }
    //编辑城市操作
    function edit_doit(){
        $id=I('post.id');
        $en_name=I('post.en_name');
        $url=I('post.url');
        if($id){
            if($en_name){
                $this->check_repeat($en_name,'en_name','country',$id,'id');
                $list=$this->get_table('country')->where('id=%d',$id)->find();
                $old_list=$list;
                $log = "编辑城市 id为：" .$id;
                if($list['en_name']!=$en_name){
                    $list['en_name']=$en_name;
                    $log.=$this->save_log('图片所属',$old_list['en_name'],$en_name);
                }
                if(array_diff_assoc($list,$old_list)){
                    $re=$this->get_table("country")->save($list);
                    if($re){
                        $this->write_log('修改城市',$log);
                        $this->success('数据更新成功',$url);
                    }else{
                        $this->error("保存失败");
                    }
                }else{
                    $this->error("新旧数据一致，不需要保存");
                }
            }else{
                $this->error("请填写".$this->name."名称");
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
            $result = M('country')->save($data);
            if($result){
                $this->status_log($status,$id,'城市');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }
}
