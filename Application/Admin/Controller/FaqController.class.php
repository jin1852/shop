<?php
namespace Admin\Controller;
use \Think\Controller;
class FaqController extends CommonController {
    public function index(){
        $title = I('title');
        $where=1;
        if($title){
            $where .= " and title like '%".$title."%'";
        }
        $count=M("faq")->where($where)->count();
        $page=$this->get_page($count,15);
        $list=M("faq")->where($where)->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
        $this->list=$list;
        $this->page=$page->show();
        $this->display();
    }

    public function add(){
        if(IS_POST){
            $title=I("title","");
            $content=I("content","");
            $top=I("top",1);
            $article=array();
            $article['title']=$title;
            $article['content']=htmlspecialchars_decode(html_entity_decode($content));
            $article['status']=1;
            $article['top']=$top;
            $article['add_time']=time();
            $result=M("faq")->data($article)->add();
            if($result){
                $this->success("添加成功",U("Faq/index"));
            }else{
                $this->error("添加不成功",U("Faq/add"));
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
            $article=M("faq")->where("id=".$id)->find();

            $title=I("title","");
            $content=I("content","");
            $is_show=I("status",1);
            $toplevel=I('top',0);
            //实例化上传类


            $article['title']=$title;
            $article['content']=$content;
            $article['status']=$is_show;
            $article['top']=$toplevel;
            $article['updata_time']=time();
            $result=M("faq")->data($article)->where("id=".$article['id'])->save();
            if($result){
                $this->success('更新成功');
            }else{
                $this->success('数据没改变，不需要更新');
            }
            die();
        }

        $id=I("id",0);
        if($id==0)$this->error("文章不存在");

        $article=M("faq")->find($id);
        $article['content']=htmlspecialchars_decode(html_entity_decode($article['content']));
        $this->assign("article",$article);
        $this->display();
    }

}
