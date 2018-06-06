<?php
namespace Admin\Controller;
use Think\Controller;
class CateController extends CommonController {

    private $name='分类';

    private $dir='protype';

    public function _initialize(){
        parent::_initialize();
        $this->check_1or3();
    }

    //列表页
    public function index(){
        $proName=I('proName');
        $where=1;
        if(!empty($proName)){
            $where.=" and proName LIKE '%".$proName."%'";
        }
        $type = M('protype');
        $count=$type->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data = $type->where($where)->order('lv asc')->limit($page->firstRow.','.$page->listRows)->select();
        $this->title=$this->name;
        $this->data=$data;
        $this->page=$show;
        $this->display();
    }


    //添加页
    public function add(){
        $type = M('protype');
        $data = $type->where("proLive=1")->order('proPath')->select();
        $r = $this->demo($data, 0);
        $options = $this->option($r);
        $this->options=$options;
        $this->title=$this->name;
        $this->display();
    }

    //用来将导航按照需要生成我们想要的数据
    protected function demo($data, $pid=0){
        $arr = array();
        foreach($data as $v){
            if($v['proPid'] == $pid){
                $v['child'] = $this->demo($data, $v['proTypeId']);
                $arr[] = $v;
            }
        }
        return $arr;
    }



    //添加分类
    public function add_doit(){
        $type = M('protype');
        $proName= I('proName');
        if($proName){
            $proName=$this->filter_str($proName);
            $has=$type->where("proName='%s'",$proName)->select();
            if(!$has){
                $data['proName']=$proName;
            }else{
                $this->error($proName."已存在");
            }
        }else{
            $this->error("请输入分类名称");
        }
        $data['proPid'] = I('proPid')?I('proPid'):0;
        if(!$data['proPid']){
            $shortcut = "0";
        }else{
            $shortcut = rtrim($type->find($data['proPid'])['proPath'],',');
        }
        $data['lv'] = count(explode(',',$shortcut));
        if($_FILES['image']['name']){
            if($data['lv']==1){
                $data['image']= $this->upFile($this->dir);
            }
        }
       $data['create_time']=time();
        $r = $this->get_table('protype')->add($data);
        if($r){
            $id = $r;
            $path['proPath'] = $shortcut . "," . $r . ",";
            if($type->create($path)){
                $res =  $type->where("proTypeId = ".$id)->save();
                if(false !== $res){
                    $this->write_log('添加分类','添加分类,分类名称：'.$proName);
                    $this->success('新增成功','index');
                }else{
                    $this->error('添加目录失败');
                }
            }
        }
    }

    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1)){
            $data['proTypeId']=$id;
            $data['proLive']=$status;
            $data['updata_time']=time();
            $result = M('protype')->save($data);
            if($result){
                $this->status_log($status,$id,'分类');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }

    //修改相应的分类
    //可以修改---父级名称、分类名称
    public function update(){
        $type = M('protype');
        $url= $_SERVER['HTTP_REFERER'];
        $id = I('id');
        $val = $type->find($id);
        $data = $type->order('proPath')->select();
        $val['image_name']=$this->full_url().$this->dir."/l_".$val['image'];
        $r = $this->demo($data, 0);
        $options = $this->option($r);
        $this->title=$this->name;
        $this->url=$url;
        $this->val=$val;
        $this->options=$options;
        $this->display('update');
    }

    //修改分类
    function update_doit(){
        $proTypeId = I('post.proTypeId');
        $url = I('post.url');
        $proName = I('post.proName');
        if ($proTypeId>0) {
            $list=M('protype')->where('proTypeId=%d',$proTypeId)->find();
            $old_list=$list;
            $log = "编辑分类 id为：" .$proTypeId;
            if($proName){
                $proName=$this->filter_str($proName);
                $has=M('protype')->where("proName='%s' and proTypeId!=%d",$proName,$proTypeId)->select();
                if(!$has){
                    $list['proName']=$proName;
                    $log.=$this->save_log('名称',$old_list['proName'],$list['proName']);
                }else{
                    $this->error($proName."已存在");
                }
            }else{
                $this->error("请输入分类名称");
            }
            if($_FILES['image']['name']){
                if($list['lv']==1){
                    $image= $this->upFile($this->dir);
                    $list['image']=$image;
                    $log.='，更新图片';
                }
            }
            if(array_diff_assoc($list,$old_list)){
                $list['updata_time'] = time();
                $re= M('protype')->save($list);
                if($re){
                    if($image){
                        $this->del_img($this->dir,$old_list['image']);
                    }
                    $this->write_log('修改分类',$log);
                    $this->success('更新成功',$url);
                }else{
                    $this->error('更新失败');
                }
            }else{
                $this->error("新旧数据一致，不需要保存");
            }
        }else{
            $this->error("非法操作");
        }
    }

    function option($r){
        $cateid = $_GET['cateid'] ? $_GET['cateid'] : 0;
        $select = '';
        $options = '<option value="0">根分类</option>';
        foreach ($r as $v) {
            //判断proTypeId是否和传过来的cateid相等
            if($cateid==$v['proTypeId']){
                $select = 'selected';
            }
            $options .= '<option value="'.$v['proTypeId'] . '" '.$select.'>&nbsp;&nbsp;'.$v['proName'].'</option>';
            //再次清空select的值，不然如果第一次为selected，那么下一次也依然为selected
            $select = '';
            //如果其child数组不为空-->表示带有下级元素
            //可以完善的地方，可以判断数组的长度，然后一直遍历到最后一层
            if($v['child']){
                foreach ($v['child'] as $vc) {
                    if($cateid==$vc['proTypeId']){
                        $select = "selected";
                    }
                    $options.= '<option value="'.$vc['proTypeId'].'" '.$select.'>&nbsp;&nbsp;&nbsp;&nbsp;'.$vc['proName'].'</option>';
                    //同样需要清空select的值
                    $select = "";
                    if($vc['child']){
                        foreach ($vc['child'] as $vc2) {
                            if($cateid==$vc2['proTypeId']){
                                $select = "selected";
                            }
                            $options.= '<option value="'.$vc2['proTypeId'].'" '.$select.'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$vc2['proName'].'</option>';
                            //同样需要清空select的值
                            $select = "";
                        }
                    }
                }
            }
        }
        return $options;
    }
    //end
}