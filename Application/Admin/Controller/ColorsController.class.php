<?php
namespace Admin\Controller;
use Think\Controller;
class ColorsController extends CommonController {

    private $name='颜色';

    public function _initialize(){
        parent::_initialize();
        $this->check_1or3();
    }
    //颜色列表页面
    public function index(){
        $colorName=I('colorName');
        $where='status<2';
        if(!empty($colorName)){
            $where.=" and colorName LIKE '%".$colorName."%'";
        }
        $count=$this->get_table('colors')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data = $this->get_table('colors')->where($where)->limit($page->firstRow.','.$page->listRows)->order('colorId desc')->select();
        foreach($data as & $one){
            $one['status_name']=$this->format_status($one['status']);
            $one['create_time']=$this->time_format($one['create_time']);
            $one['updata_time']=$this->time_format($one['updata_time']);
        }
        $this->title=$this->name;
        $this->page=$show;
        $this->data=$data;
        $this->display();
    }


    //添加颜色
    public function add(){
        $this->title=$this->name;
        $this->display();
    }

    //颜色添加
    function add_doit(){
        $data['colorName']=I('post.colorName');
        $data['rgb']=I('post.rgb');
        if($_FILES['rgbimg']['error'] === 0){ //如果图片上传
            $pic = $this->upload();
            $data['rgbimg'] = $pic['smimg'];
        }
        if($data['colorName']){
            $this->check_repeat($data['colorName'],'colorName','colors');
            $data['create_time']=time();
            if($this->get_table('colors')->create($data)){
                $res = $this->get_table('colors')->add();
                if($res){
                    $this->write_log('添加颜色','添加颜色:'.$data['colorName']);
                    $this->success('颜色信息添加成功',U('Colors/index'));
                }else{
                    $this->error('颜色信息添加失败',U('Colors/index'));
                }
            }
        }else{
            $this->error("颜色名称未添加");
        }
    }


    //编辑颜色信息
    public function edit(){
        $colorId = I('get.colorId');
        $colorsData = $this->get_table('colors')->where('colorId='.$colorId)->find();
        $url= $_SERVER['HTTP_REFERER'];
        $this->url=$url;
        $this->title=$this->name;
        $this->assign('colorsData',$colorsData);
        $this->display();
    }


    //修改操作
    function edit_doit(){
        $colorId=I('post.colorId');
        $url=I('post.url');
        $colorName=I('post.colorName');
        $rgb=I('post.rgb');
        if($colorId>0){
            $list=M('colors')->where('colorId=%d',$colorId)->find();
            $old_list=$list;
            $log = "编辑图片 id为：" .$colorId;
            if($_FILES['rgbimg']['error'] === 0){ //如果图片上传
                $pic = $this->upload();
                $img = $pic['smimg'];
                $list['rgbimg'] = $img;
                $log.='，更新图片';
            }
            if($colorName){
                if($colorName!=$list['colorName']){
                    $this->check_repeat($colorName,'colorName','colors',$colorId,'colorId');
                    $list['colorName']=$colorName;
                    $log.=$this->save_log('名称',$old_list['colorName'],$list['colorName']);
                }
            }else{
                $this->error("颜色名称未添加");
            }
            if($rgb!=$list['rgb']){
                if($rgb){
                    $list['rgb']=$rgb;
                }else{
                    $list['rgb']='';
                }
                $log.=$this->save_log('rgb参数',$old_list['rgb'],$list['rgb']);
            }
            if(array_diff_assoc($list,$old_list)){
                $list['updata_time'] = time();
                $re= M('colors')->save($list);
                if($re){
                    $this->write_log('修改颜色',$log);
                    $this->success('更新成功',$url);
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

    protected function upload(){ //上传图片，如果有传递$url参数的话就删除对应的图片
        $upload = new \Think\Upload(); //实例化tp的上传类
        $upload->exts = array('jpg','gif','png','jpeg'); //设置附件上传类型
        $upload->rootPath = './Public/Uploads/'; //相对于站点根目录jd
        $upload->savePath = ''; //设置附件上传目录,地址是相对于根目录(rootPath的)
        $info = $upload->upload(); //开始上传
        if(!$info){
            $this->error($upload->getError());
        }else{
            foreach($info as $v) {
                $pic['file'] = $v['savepath'].$v['savename']; //获取文件名
                $pic['smimg'] = $v['savepath'].'sm_'.$v['savename']; //获取缩略图的文件名
                $pic['img'] = $upload->rootPath.$v['savepath'].$v['savename']; //获取完整的图片地址
                $image = new \Think\Image(); // 利用tp的图片处理类对上传的图片进行处理
                $image->open($pic['img']);
                $image->thumb(50, 50)->save($upload->rootPath.$v['savepath'].'xs_'.$v[savename]); //根据网站需要生成50*50的缩略图
                $image->thumb(160, 160)->save($upload->rootPath.$v['savepath'].'sm_'.$v[savename]); //生成160*160的缩略图
                return $pic; //返回相关信息数组
            }
        }
    }

    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1 or $status==2)){
            $data['colorId']=$id;
            $data['status']=$status;
            $data['updata_time']=time();
            $result =$this->get_table('colors')->save($data);
            if($result){
                $this->status_log($status,$id,'颜色');
                $this->success('操作成功',U('Colors/index'));
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }
    //end
}
