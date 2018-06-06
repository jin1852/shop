<?php
namespace Shop\Controller;
use Shop\Model\BaseModel;
use Think\Controller;
class ArticleController extends BaseController {

    public function index(){
        $catid=I("catid",1);
        $year_key=intval(I("year_key",0));
        $title=I("title",null);
        $yearlist=$this->getArticleYearList();
        $where="catid={$catid} ";
        if($year_key==0){
            //$year_key=$yearlist[0];
        }else{
            $where=$where." and year_key={$year_key}";
        }

        if($title!=null){
            if(strlen(trim($title))>0) {
                $where = $where . " and title like '%{$title}%'";
            }
        }
        $count=M("Article")->where($where)->cache(true,120)->count();
        $Base = new BaseModel();
        $Page = $Base->get_page($count,40);
        $show = $Page->show();
        $list=M("Article")->where($where)->order('toplevel desc,id desc')->limit($Page->firstRow.','.$Page->listRows)->cache(true,120)->select();
        $this->assign("list",$list);
        $this->assign('page',$show);
        $this->assign("yearlist",$yearlist);
        $this->assign('head_title','Marketing Support');
        $this->display();
    }
    private function getArticleYearList(){
        $cache_years=S("article_years");
        if(!$cache_years) {
            $sql = "select year_key from " . M("Article")->getTableName() . " group by year_key order by year_key desc";
            $years = M()->query($sql);
            $arr = array();
            foreach ($years as $one) {
                $arr[] = $one['year_key'];
            }
            $cache_years=$arr;
            S("article_years",$arr,3600);
            return $arr;
        }
        return $cache_years;
    }
    public function show(){
        $id=I("id",0);
        $article = M("Article")->where("id={$id}")->cache(true, 100)->find();
        if($article){
            $year_key=$article['year_key'];
            $where=" year_key={$year_key} and id <>{$id} ";
            $list=M("Article")->where($where)->order('toplevel desc,id desc')->select();
            //var_dump($list);
            $this->assign('list',$list);
        }
        $article['content']=htmlspecialchars_decode(html_entity_decode($article['content']));
        //var_dump($article);
        $this->assign('head_title','Marketing Support');
        $this->assign("article",$article);
        $this->display();
    }


}