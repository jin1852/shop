<?php
namespace Admin\Controller;
use \Think\Controller;
class ArticleController extends CommonController {
    public function index(){
        $title = I('title');
        $where = "catid=1";
        if($title){
            $where.= " and title like '%".$title."%'";
        }

        $count=M("Article")->where($where)->count();
        $page=$this->get_page($count,15);
        $list=M("Article")->limit($page->firstRow.','.$page->listRows)->where($where)->order('id desc')->select();
        $this->list=$list;
        $this->page=$page->show();
        $this->display();
    }
    public function add(){
        if(IS_POST){
            $title=I("title","");
            $content=I("content","");
            $is_show=I("is_show",1);
            $catid=I("catid",1);
            $toplevel=I('toplevel',0);
            //实例化上传类
            $upload = new \Think\Upload();
            //设置上传大小
            $upload->maxSize = 4194304;
            //设置上传类型
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            // 设置上传目录
            $upload->rootPath= './';
            $upload->savePath = '/Public/Uploads/article/';
            $upload->autoSub = true;
            //设置保存的文件名
            $upload->saveName = 'time';
            //上传文件
            $info = $upload->uploadOne($_FILES['image']);
            $pic_path=$info['savepath'].$info['savename'];
            if(!$info) {
                //上传失败
                $this->error($upload->getError());
            }else{
                //上传成功
                //图片处理
                $image = new \Think\Image();
                $image->open($upload->rootPath.$info['savepath'].$info['savename']);
                $width=800;
                $height=800;
                if($image->width()<800)$width=$image->width();
                if($image->height()<800)$height=$image->height();
                $image->thumb($width, $height,$image::IMAGE_THUMB_SCALE)->save($upload->rootPath.$info['savepath'].'m_'.$info['savename']);
                //返回上传的文件名
            }
            $article=array();
            $article['title']=$title;
            $article['content']=htmlspecialchars_decode(html_entity_decode($content));
            $article['is_show']=$is_show;
            $article['toplevel']=$toplevel;
            $article['add_time']=time();
            $article['catid']=$catid;
            $article['thumb']=$pic_path;
            $article['year_ket']=$this->get_year_key();
            $result=M("article")->data($article)->add();
            if($result){
                $this->success("添加成功",U("Article/index"));
            }else{
                $this->error("添加不成功",U("Article/add"));
            }


        }
        $this->display();

    }
    public function edit(){

        if(IS_POST){
            $id=I("id",0);
            if($id==0){
                $this->error("找不到文章");
                return;
            }
            $article=M("Article")->where("id=".$id)->find();

            $title=I("title","");
            $content=I("content","");
            $is_show=I("is_show",1);
            $toplevel=I('toplevel',0);
            //实例化上传类
            $oldthumb=null;

            if(isset($_FILES['image'])) {
                $upload = new \Think\Upload();
                //设置上传大小
                $upload->maxSize = 4194304;
                //设置上传类型
                $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
                // 设置上传目录
                $upload->rootPath = './';
                $upload->savePath = '/Public/Uploads/article/';
                $upload->autoSub = true;
                //设置保存的文件名
                $upload->saveName = 'time';
                //上传文件
                $info = $upload->uploadOne($_FILES['image']);


                if (!$info) {
                    //上传失败

                    $oldthumb=null;
                    unset($article['thumb']);
                } else {
                    $pic_path = $info['savepath'] . $info['savename'];
                    //上传成功
                    //图片处理
                    $image = new \Think\Image();
                    $image->open($upload->rootPath . $info['savepath'] . $info['savename']);
                    $width = 800;
                    $height = 800;
                    if ($image->width() < 800) $width = $image->width();
                    if ($image->height() < 800) $height = $image->height();
                    $image->thumb($width, $height, $image::IMAGE_THUMB_SCALE)->save($upload->rootPath . $info['savepath'] . 'm_' . $info['savename']);
                    //返回上传的文件名
                    $oldthumb=$_SERVER['DOCUMENT_ROOT'].$article['thumb'];
                    $article['thumb']=$pic_path;
                }


            }else{

                $oldthumb=null;
                unset($article['thumb']);
            }

            //$add_time=time();
            //$year_key=date("Y",$add_time);
            $article['title']=$title;
            $article['content']=$content;
            $article['is_show']=$is_show;
            $article['toplevel']=$toplevel;
            //$article['add_time']=$add_time;
            $article['year_key']=$this->get_year_key();
            $result=M("Article")->data($article)->where("id=".$article['id'])->save();
            if($result){
                if($oldthumb!=null){
                    unlink($oldthumb);
                }
                $this->success('更新成功');
            }else{
                $this->success('数据没改变，不需要更新');
            }
            die();
        }

        $id=I("id",0);
        if($id==0)$this->error("文章不存在");

        $article=M("Article")->where("id=".$id)->find();
        $article['content']=htmlspecialchars_decode(html_entity_decode($article['content']));
        $this->assign("article",$article);

        $this->display();
    }
    public function del(){
        $id=I("id",0);
        if($id==0)$this->error("文章不存在");
        M("Article")->where("id=".$id)->delete();
        $this->success("删除成功！");
    }

    private function get_year_key(){
        return date('Y',time());
    }
}
