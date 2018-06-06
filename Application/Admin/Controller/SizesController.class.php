<?php
namespace Admin\Controller;
use Think\Controller;
class SizesController extends CommonController {
    /*访问权限*/
    public function _initialize(){
        parent::_initialize();
        $this->check_1or3();
    }
    //尺寸列表
    public function index(){
        $sizeName=I('sizeName');
        $where='status<2';
        if(!empty($sizeName)){
            $where.=" and sizeName LIKE '%".$sizeName."%'";
        }
        $size = M('sizes');
        $count=$size->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data = $size->where($where)->limit($page->firstRow.','.$page->listRows)->order('sizeId desc')->select();
        foreach($data as &$one){
            $one['updata_time']=$this->time_format($one['updata_time']);
            $one['create_time']=$this->time_format($one['create_time']);
            $one['status_name']=$this->format_status($one['status']);
        }
        $this->assign('page',$show);
        $this->assign('data', $data);
        $this->display();
    }


    //添加尺寸
    public function add(){
        $this->display();
    }

    //尺寸添加处理函数
    function add_doit(){
        $sizes = M('sizes');
        $data = I('post.');
        if($data['sizeName']){
            $data['create_time']=time();
            if($sizes->create($data)){
                $res = $sizes->add();
                if($res){
                    $this->write_log('添加尺寸','添加尺寸,尺寸：'.$data['sizeName']);
                    $this->success('尺寸添加成功',U('sizes/index'));
                }else{
                    $this->error('尺寸添加失败',U('sizes/index'));
                }
            }
        }else{
            $this->error("请填写尺寸名称");
        }
    }


    //编辑尺寸信息
    public function edit(){
        $sizes = M('sizes');
        $sizeId = I('get.sizeId');
        $brandsData = $sizes->where('sizeId='.$sizeId)->find();
        $url= $_SERVER['HTTP_REFERER'];
        $this->url=$url;
        $this->brandsData=$brandsData;
        $this->display();
    }

    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1 or $status==2)){
            $data['sizeId']=$id;
            $data['status']=$status;
            $data['updata_time']=time();
            $result = M('sizes')->save($data);
            if($result){
                $this->status_log($status,$id,'尺寸');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }


    //修改
    function edit_doit(){
        $sizeId=I('post.sizeId');
        $sizeName=I('post.sizeName');
        $url=I('post.url');
        if($sizeId){
            $list=$this->get_table('sizes')->where('sizeId=%d',$sizeId)->find();
            $old_list=$list;
            $log = "编辑尺寸 id为：" .$sizeId;
            if($sizeName){
                if($sizeName!=$list['sizeName']){
                    $this->filter_str($sizeName);
                    $list['sizeName']=$sizeName;
                    $log.=$this->save_log('尺寸名称',$old_list['sizeName'],$sizeName);
                }
            }else{
                $this->error("请填写尺寸名称");
            }
            if(array_diff_assoc($list,$old_list)){
                $list['updata_time']=time();
                $re=$this->get_table("sizes")->save($list);
                if($re){
                    $this->write_log('修改尺寸',$log);
                    $this->success("修改成功",$url);
                }else{
                    $this->error("修改失败");
                }
            }else{
                $this->error("新旧数据一致，不需要保存");
            }
        }else{
            $this->error("非法操作");
        }
    }
    //end
}
