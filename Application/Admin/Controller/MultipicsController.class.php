<?php
namespace Admin\Controller;
use Think\Controller;

class MultipicsController extends CommonController {

    //多图片上传
    public function index(){
        $multipics  = M('prodimg');
        $color = M('colors');
        $pid = I('get.prodId');
        if(!empty($pid)){
            $where['prodId'] = $pid;
        }
        $count = $multipics->where($where)->count();
        $page = new \Think\Page($count,15);
        $show = $page->show();
        $colorData = $color->select();
        $data = $multipics->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        $colorArr = array();
        foreach ($colorData as $k => $v) {
            $colorArr[$v['colorId']] = $v['colorName'];
        }
        $this->assign('show',$show);
        $this->assign('colorArr',$colorArr);
        $this->assign('data',$data);
        $this->display();
    }
    //initial初始化列表
    public function initial(){
        $id=I('post.value')['id'];
        if($id){
            $imgArr=$this->get_table('prodimg')->alias('p')->join("jd_colors AS c ON c.colorID=p.colorId")->field('p.*,c.colorName')->where('p.prodId=%d and p.status=1',$id)->select();
            if($imgArr){
                foreach($imgArr as &$one){
                    $one['image']=$this->full_url().$one['image'];
                    $one['status']=$this->format_status($one['status']);
                    $one['create_time']=$this->time_format($one['create_time']);
                }
                die($this->list_json_result(1,"初始化成功",$imgArr));
            }else{
                die($this->list_json_result(1,"无数据",''));
            }
        }else{
            die($this->list_json_result(-1,"数据异常",''));
        }
    }

    //json
    public function list_json_result($status, $msg, $result){
        return json_encode(array('status' => $status, 'msg' => $msg, 'result' => $result));
    }
    public function add(){
        $gid = I('get.gid');
        if($gid){
            $colorsData=$this->get_table('colors')
                ->alias('c')
                ->join('jd_products AS p ON p.colorId=c.colorId')
                ->where("prodId=%d",$gid)
                ->group('c.colorId')->select();
            if(!$colorsData){
                $colorsData =array(array('colorId'=>'','colorName'=>'请返回商品列表->对应产品的属性->添加详细属性'));
            }
            $this->title='新增商品图片';
            $this->title2='产品图片列表';
            $this->colorsData=$colorsData;
            $this->assign('gid',$gid);
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //多图片添加处理函数,因为每张图片还要和自己的图片颜色关联起来，所以每次只能上传一张，解决方法可以改变设置的前后的顺序，先上传图片后设置图片的颜色属性
    public function add_doit(){
        $multipics = M('prodimg');
        $data = I('post.');

        if($_FILES['image']['error'] === 0){ //如果图片上传
            $pic = $this->upload();
            $data['image'] = $pic['smimg'];
        }
        if($multipics->create($data)){
            $res = $multipics->add();
            if($res){
                $this->success('图片添加成功',U('Multipics/index'));
            }else{
                $this->error('图片添加失败',U('Multipics/index'));
            }
        }

    }

    function multiupload(){
        $prodImg = M('prodimg');
        $upload = new \Think\Upload(); //实例化tp的上传类
        $upload->exts = array('jpg','gif','png','jpeg'); //设置附件上传类型
        $upload->rootPath = './Public/Uploads/'; //相对于站点根目录jd
        $upload->savePath = ''; //设置附件上传目录,地址是相对于根目录(rootPath的)
        //定义空数组，用于存储上传的图片地址
        $datalist = array();
        $info = $upload->upload(); //开始上传
        if(!$info){
            $this->error($upload->getError());
        }else{
            foreach($info as $v) {
                $datalist['prodId'] = I('prodId'); //商品id
                $datalist['colorId'] = I('colorId'); //颜色id
                $datalist['create_time'] = time();
                $pic['file'] = $v['savepath'].$v['savename']; //获取文件名
                $pic['smimg'] = $v['savepath'].'sm_'.$v['savename']; //获取缩略图的文件名
                $pic['img'] = $upload->rootPath.$v['savepath'].$v['savename']; //

                $datalist['image'] = $pic['file'];

                //商品id获取完整的图片地址
                $image = new \Think\Image(); // 利用tp的图片处理类对上传的图片进行处理
                $image->open($pic['img']);

                $image->thumb(50, 50)->save($upload->rootPath.$v['savepath'].'xs_'.$v['savename']); //根据网站需要生成50*50的缩略图
                $image->thumb(160, 160)->save($upload->rootPath.$v['savepath'].'sm_'.$v['savename']); //生成160*160的缩略图
                if($prodImg->create($datalist)){
                    $res = $prodImg->add();
                    if($res){
                        $this->write_log('添加多图','添加多图id:'.$res.',产品id'.$datalist['prodId'].',颜色id'.$datalist['colorId']);
                        echo "1";
                    }else{
                        echo "0";
                    }
                }
            }
        }
    }

    //上传函数
    protected function upload($url=null){ //上传图片，如果有传递$url参数的话就删除对应的图片
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

                $image->thumb(50, 50)->save($upload->rootPath.$v['savepath'].'xs_'.$v['savename']); //根据网站需要生成50*50的缩略图
                $image->thumb(160, 160)->save($upload->rootPath.$v['savepath'].'sm_'.$v['savename']); //生成160*160的缩略图
                return $pic; //返回相关信息数组
            }
        }
    }

    function edit(){
        $cid = I('get.cid');
        $multipics = M('prodimg');
        $colors = M('colors')->select();
        $pics = $multipics->find($cid);

        $colorId = $pics['colorId'];
        $colorOption = '';
        foreach ($colors as $k => $v) {
            $selected = '';
            if($colorId == $v['colorId']){
                $selected = "selected";
            }
            $colorOption .= '<option value="'.$v['colorId'].'" '.$selected.'>'.$v['colorName'].'</option>';
        }

        $this->assign('colorOption',$colorOption);
        $this->assign('pics',$pics);
        $this->display();
    }


    function edit_doit(){
        $pics = M('prodimg');
        $data = I('post.');

        if($_FILES['image']['error'] === 0){ //如果图片上传
            $pic = $this->upload();
            $data['image'] = $pic['smimg'];
        }

        if($pics->create($data)){
            $data['updata_time']=time();
            $res = $pics->save();
            if($res){
                $this->success('更新成功',U('Multipics/index'));
            }else{
                $this->error('更新失败',U('Multipics/index'));
            }
        }
    }

    public function delete(){
        $this->check_1or3();
        $cid = I('get.cid');
        if($cid){
            $data=$this->get_table('prodimg')->where("picId=%d",$cid)->find();
//            $img=explode('/',$data['image']);
            if($data){
                $data['status']=0;
                $data['updata_time']=time();
                $res=$this->get_table('prodimg')->save($data);
//                $res = $pics->delete($cid);
                if($res){
                    $this->write_log('删除产品多图','id为'.$cid.'的图片');
//                    unlink('./Public/Uploads/'.$img[0].'/'.$img[1]);
//                    unlink('./Public/Uploads/'.$img[0].'/xs_'.$img[1]);
//                    unlink('./Public/Uploads/'.$img[0].'/sm_'.$img[1]);
                    $this->success('删除成功');
                }else{
                    $this->error('删除失败');
                }
            }else{
                $this->error("不存在此圖片");
            }
        }else{
            $this->error("非法操作");
        }
    }
}