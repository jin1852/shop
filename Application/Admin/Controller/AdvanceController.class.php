<?php
/*
	*后台广告管理页
*/
namespace Admin\Controller;
use Think\Controller;
class AdvanceController extends CommonController {

    private $name='广告';

    public function _initialize(){
        parent::_initialize();
        $this->check_super();
    }

    //浏览广告
    public function advance() {
        $where='status!=1';
        $count=$this->get_table('Advances')->where($where)->count();
        $page=$this->get_page($count,10);
        $show = $page->show(); //分页显示输出
        $list = $this->get_table('Advances')->where($where)->limit($page->firstRow.','.$page->listRows)->order("advanceId desc")->select();
        $this->page=$show;
        $this->title=$this->name;
        $this->assign('list',$list);
        $this->display('Advance/advance');
    }

    //展示添加广告页
    public function advanceAdd() {
        $this->title=$this->name;
        $this->display('Advance/advanceAdd');
    }

    //展示修改广告页
    public function advanceEdit() {
        $url= $_SERVER['HTTP_REFERER'];
        $data = I('param.');
        $list =  $this->get_table('Advances')->where($data)->find();
        $list['seriation']=$this->not_top($list['seriation']);
        $this->title=$this->name;
        $this->list=$list;
        $this->url=$url;
        $this->display('Advance/advanceEdit');
    }

    //添加
    public function advanceInsert() {
        $data = $_POST;
        $data['addtime'] = time();
        $img = $this->upFile();
        $data['img'] = $img[1];
        if($data['location']==0){
            if(!$data['links']){
                $this->error("不是本地链接,请填写链接方式");
            }
        }
        $result = $this->get_table('Advances')->data($data)->add();
        if(!$result) {
            $this->error('添加'.$this->name.'失败');
        }else{
            $this->write_log('添加广告',"添加广告");
            $this->success('添加'.$this->name.'成功',U('Advance/advance'));
        }
    }

    //修改
    public function advanceUpdate() {
        $advanceId=I('post.advanceId');
        $location=I('post.location');
        $links=I('post.links');
        $seriation=I('post.seriation');
        $status=I('post.status');
        $url=I('post.url');
        if($advanceId){
            $list= $this->get_table('Advances')->where('advanceId=%d',$advanceId)->find();
            $old_list=$list;
            $log = "编辑广告 id为：" . $advanceId;
            if($location==1 or $location==0){
                if($list['location']!=$location){
                    $list['location']=$location;
                    $log.=$this->save_log('读取方式',$old_list['location'],$location);
                }
                if($location==0){
                    if(!empty($links)){
                        if($list['links']!=$links){
                            $list['links']=$links;
                            $log.=$this->save_log('外联地址',$old_list['links'],$links);
                        }
                    }else{
                        $this->error("不是本地链接,请填写链接方式");
                    }
                }
            }else{
                $this->error("请选择读取方式");
            }
            if($status==0 or $status==2){
                if($list['status']!=$status){
                    $list['status']=$status;
                    $log.=$this->save_log('状态',$old_list['status'],$status);
                }
            }else{
                $this->error("请选择状态");
            }
            if(!$seriation){$seriation=0;}
            if($seriation!=$list['seriation']){
                $list['seriation']=$seriation;
                $log.=$this->save_log('排序值',$old_list['seriation'],$seriation);
            }
            if($_FILES['photo']['error'] != 4) {
                $img = $this->upFile();
                $img = $img[1];
                $list['img']=$img;
                $log .= "，更新了图片";
            }
            if(array_diff_assoc($list,$old_list)){
                $save = $this->get_table('Advances')->save($list);
                if($save) {
                    if($img) {
                        unlink('./Public/Uploads/Advance/'.$old_list['img']);
                        unlink('./Public/Uploads/Advance/m_'.$old_list['img']);
                        unlink('./Public/Uploads/Advance/one_'.$old_list['img']);
                        unlink('./Public/Uploads/Advance/two_'.$old_list['img']);
                    }
                    $this->write_log('广告编辑',$log);
                    $this->success('修改成功',$url);
                }else{
                    $this->error('修改失败');
                }
            }else{
                $this->error('没有改变');
            }
        }else{
            $this->error("非法操作");
        }
    }

    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==2 or $status==1)){
            $data['advanceId']=$id;
            $data['status']=$status;
            $result = $this->get_table('Advances')->save($data);
            if($result){
                $text=$this->advance_status($status);
                $this->write_log('状态开关',$text.' id为: '.$id.' 的广告图片');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }

    //上传文件
    public function upFile() {
        $upload = new \Think\Upload();
        $upload->maxSize = 3145728;
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->rootPath= './Public/Uploads/Advance/';
        $upload->savePath = '';
        $upload->autoSub = false;
        $upload->saveName = 'time';
        $info = $upload->upload();
        if(!$info) {
            $this->error($upload->getError());
        }else{
            $image = new \Think\Image();
            $image->open($upload->rootPath.$info['photo']['savepath'].$info['photo']['savename']);
            $image->thumb(50, 50)->save($upload->rootPath.$info['photo']['savepath'].'m_'.$info['photo']['savename']);
            //返回上传的文件名
            $imgname = array();
            $imgname[] = $info['photo']['savepath'];
            $imgname[] = $info['photo']['savename'];
            return $imgname;
        }
    }
    //end
}