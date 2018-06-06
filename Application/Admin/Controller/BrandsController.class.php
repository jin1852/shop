<?php
namespace Admin\Controller;
use Think\Controller;
class BrandsController extends CommonController {

    private $name='风格';

    public function _initialize(){
        parent::_initialize();
        $this->check_1or3();
    }

    //风格列表页
    public function index(){
        $brandName=I('brandName');
        $where='status<2';
        if(!empty($brandName)){
            $where.=" and brandName LIKE '%".$brandName."%'";
        }
        $count=$this->get_table('brands')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show();
        $data = $this->get_table('brands')->where($where)->limit($page->firstRow.','.$page->listRows)->order('brandId desc')->select();
        foreach($data as &$one){
            $one['updata_time']=$this->time_format($one['updata_time']);
            $one['create_time']=$this->time_format($one['create_time']);
            $one['status_name']=$this->format_status($one['status']);
        }
        $this->title=$this->name;
        $this->page=$show;
        $this->data=$data;
        $this->display();
    }

    //添加风格页面
    public function add(){
        $this->title=$this->name;
        $this->display();
    }

    //风格添加操作
    function add_doit(){
        $data = I('post.');
        if($data['brandName']){
            $this->check_repeat($data['brandName'],'brandName','brands');
            $data['create_time']=time();
            if($this->get_table('brands')->create($data)){
                $res = $this->get_table('brands')->add();
                if($res){
                    $this->write_log('添加风格','添加风格，名称：'.$data['brandName']);
                    $this->success('风格添加成功',U('Brands/index'));
                }else{
                    $this->error('风格添加失败',U('Brands/index'));
                }
            }
        }else{
            $this->error("请填写风格名称");
        }
    }

    //编辑风格页面
    public function edit(){
        $brandId = I('get.brandId');
        $brandsData = $this->get_table('brands')->where('brandId='.$brandId)->find();
        $url= $_SERVER['HTTP_REFERER'];
        $this->url=$url;
        $this->brandsData=$brandsData;
        $this->title=$this->name;
        $this->display();
    }

    //编辑风格操作
    function edit_doit(){
        $brandId=I('post.brandId');
        $url=I('post.url');
        $brandName=I('post.brandName');
        if($brandId>0){
            $list=$this->get_table('brands')->where('brandId=%d',$brandId)->find();
            $old_list=$list;
            $log = "编辑风格 id为：" . $brandId;
            if($brandName){
                if($list['brandName']!=$brandName){
                    $this->check_repeat($brandName,'brandName','brands',$brandId,'brandId');
                    $list['brandName']=$brandName;
                    $log.=$this->save_log('名称',$old_list['brandName'],$brandName);
                }
            }else{
                $this->error("请输入风格名称");
            }
            if(array_diff_assoc($list,$old_list)){
                $list['updata_time']=time();
                $re=$this->get_table("brands")->save($list);
                if($re){
                    $this->write_log('修改风格',$log);
                    $this->success("保存成功",$url);
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

    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1 or $status==2)){
            $data['brandId']=$id;
            $data['status']=$status;
            $data['updata_time']=time();
            $result = M('brands')->save($data);
            if($result){
                $this->status_log($status,$id,'风格');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }
}
