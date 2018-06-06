<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;

class ProdstyleController extends CommonController {
    private $dir='prodstyle';
    /*访问权限*/
    public function _initialize(){
        parent::_initialize();
        $this->check_1or3();
    }
//==================================================商品分类列表============================================================
    //商品分类列表
    public function protype_list(){
        $proName=I('proName');
        $where='lv=1';
        if(!empty($proName)){
            $where.=" AND proName LIKE '%".$proName."%'";
        }
        $count=$this->get_table('protype')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data=$this->get_table('protype')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        $this->title='商品分类列表';
        $this->page=$show;
        $this->data=$data;
        $this->display();
    }

    //修改商品对应styleId页面
    public function edit_styleId(){
        $proTypeId=I('proTypeId');
        if( $proTypeId){
            $data=$this->get_table('protype')->where("proTypeId=%d",$proTypeId)->find();
            $data['styleId']=$this->StringToArray($data['styleId']);
            $style=$this->get_table('products_style')->where('lv=1 and status=1')->select();
            $url= $_SERVER['HTTP_REFERER'];
            $this->url=$url;
            $this->data=$data;
            $this->style=$style;
            $this->title='修改对应系列';
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //修改商品对应styleId操作
    public function do_edit_styleId(){
        $url=I('post.url');
        $proTypeId=I('post.proTypeId');
        $styleId=implode(',',I('post.styleId'));
        if($proTypeId>0){
            $list=$this->get_table('protype')->where('proTypeId=%d',$proTypeId)->find();
            $old_list=$list;
            $log = "编商品分类对应styleId id为：" .$proTypeId;
            if($styleId){
                if($styleId!=$list['styleId']){
                    $list['styleId']=$styleId;
                    $log.=$this->save_log('定义styleId',$old_list['styleId'],$styleId);
                }
            }else{
                $list['styleId']='';
            }
            if(array_diff_assoc($list,$old_list)){
                $this->write_log('修改商品分类对应styleId',$log);
                $re=$this->get_table("protype")->save($list);
                if($re){
                    $this->success("修改成功",$url);
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

    //字符串转数组
    public function StringToArray($data){
        return explode(",",$data);
    }

//==================================================商品分类列表end=========================================================

//====================================================一级系列==============================================================

    //一级系列=列表
    public function index_lv1(){
        $styleName=I('styleName');
        $where="Lv=1 and status<2";
        if(!empty($styleName)) {$where.=" and styleName LIKE '%".$styleName."%'";}
        $count=$this->get_table('products_style')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data=$this->get_table('products_style')->where($where)->order('top desc,styleId desc')->limit($page->firstRow.','.$page->listRows)->select();
        foreach($data as &$one){
            $one['status_name']=$this->format_status($one['status']);
            $one['display_name']=$this->format_is_show($one['display']);
            $one['radio_name']=$this->format_Radio($one['radio']);
            $one['child_name']=$this->is_not($one['child']);
            $one['photo_img']=$this->full_url().$this->dir."/m_".$one['photo'];
        }
        $this->title='一级系列列表';
        $this->page=$show;
        $this->data=$data;
        $this->display();
    }

    //一级系列修改页面
    public function edit_lv1(){
        $styleId=I('styleId');
        if($styleId){
            $data=$this->get_table('products_style')->where("styleId=%d",$styleId)->find();
            if($data['top']==0){$data['top']='';}
            $data['photo_img']=$this->full_url().$this->dir."/l_".$data['photo'];
            $url = $_SERVER['HTTP_REFERER'];
            $this->url=$url;
            $this->data=$data;
            $this->title='修改一级系列';
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //一级系列修改操作
    public function do_edit_lv1(){
        $url=I('post.url');
        $styleId=I('post.styleId');
        $styleName=I('post.styleName');
        $status=I('post.status');
        $display=I('post.display');
        $radio=I('post.radio');
        $child=I('post.child');
        $top=I('post.top');
        if($styleId){
            if($styleName){
                $styleName=$this->filter_str($styleName);
                $styleName=$this->check_lv1_tyleName($styleName,$styleId);
                if($status!='' && ($status==1 or $status==0)){
                    if($display!='' && ($display==1 or $display==0)) {
                        if($radio!='' && ($radio==1 or $radio==0)) {
                            if($child!='' && ($child==1 or $child==0)) {
                                $list=$this->get_table('products_style')->where('styleId=%d',$styleId)->find();
                                $old_list=$list;
                                $log = "编辑一级系列 id为：" .$styleId;
                                if(!$top){$top=0;}
                                if($top!=$list['top']){
                                    $list['top']=$top;
                                    $log.=$this->save_log('排序值',$old_list['top'],$top);
                                }
                                if($styleName!=$list['styleName']){
                                    $list['styleName']=$styleName;
                                    $log.=$this->save_log('系列名',$old_list['styleName'],$list['styleName']);
                                }
                                if($status!=$list['status']){
                                    $list['status']=$status;
                                    $log.=$this->save_log('状态',$old_list['status'],$list['status']);
                                }
                                if($radio!=$list['radio']){
                                    $list['radio']=$radio;
                                    $log.=$this->save_log('单选/多选',$old_list['radio'],$list['radio']);
                                }
                                if($display!=$list['display']){
                                    $list['display']=$display;
                                    $log.=$this->save_log('list显示状态',$old_list['display'],$list['display']);
                                }
                                if($child!=$list['child']){
                                    $list['child']=$child;
                                    $log.=$this->save_log('是否有下级状态',$old_list['child'],$list['child']);
                                }
                                if($_FILES['image']['name']){
                                    $image= $this->upFile($this->dir);
                                    $list['photo']=$image;
                                    $log.='更新了图片';
                                }
                                if(array_diff_assoc($list,$old_list)){
                                    unset($list['proName']);
                                    $this->get_table("products_style")->save($list);
                                    if($image){
                                        unlink('./Public/Uploads/'.$this->$dir.'/'.$old_list['image']);
                                        unlink('./Public/Uploads/'.$this->$dir.'/m_'.$old_list['image']);
                                        unlink('./Public/Uploads/'.$this->$dir.'/l_'.$old_list['image']);
                                    }
                                    $this->write_log('修改一级系列',$log);
                                    $this->success("保存成功",$url);
                                }else{
                                    $this->error("新旧数据一致，不需要保存");
                                }
                            }else{
                                $this->error("请选择 是否有下级");
                            }
                        }else{
                            $this->error("请选择 单选/多选");
                        }
                    }else{
                        $this->error("请选择 是否list界面显示");
                    }
                }else{
                    $this->error("请选择 开启状态");
                }
            }else{
                $this->error("请输入 系列分类名称");
            }
        }else{
            $this->error("非法操作");
        }
    }

    //添加一级系列
    public function add_lv1(){
        $this->title='添加一级系列';
        $this->display();
    }

    //添加一级系列操作
    public function do_add_lv1(){
        $styleName=I('post.styleName');
        $display=I('post.display');
        $radio=I('post.radio');
        $child=I('post.child');
        $top=I('post.top');
        if($styleName){
            $styleName=$this->filter_str($styleName);
            $data['styleName']=$this->check_lv1_tyleName($styleName);
            if(($radio==1 or $radio==0) && $radio!=''){
                if(($display==1 or $display==0) && $display!=''){
                    if(($child==1 or $child==0) && $child!=''){
                        if($top>0){$data['top']=$top;}else{$data['top']=0;}
                        if($_FILES['image']['name']){$data['photo']= $this->upFile($this->dir);}
                        $data['display']=$display;
                        $data['child']=$child;
                        $data['radio']=$radio;
                        $data['lv']=1;
                        $data['status']=1;
                        $add=$this->get_table('products_style')->add($data);
                        if($add){
                            $this->write_log('添加一级系列','添加一级系列,系列名称：'.$styleName);
                            $this->success("添加成功",U('Prodstyle/protype_list'));
                        }else{
                            $this->error("添加失败");
                        }
                    }else{
                        $this->error("请选择 是否有下级");
                    }
                }else{
                    $this->error("请选择 是否在list显示");
                }
            }else{
                $this->error("请选择 单选/多选");
            }
        }else{
            $this->error("请输入 分类名称");
        }
    }

    //检查一级系列是否存在
    public function check_lv1_tyleName($data,$id=null){
        $where="styleName='".$data."' and lv=1";
        if($id){$where.=' and styleId!='.$id;}
        $res=$this->get_table('products_style')->where($where)->select();
        if($res){
            $this->error($data." 此系列分类已经存在");
        }else{
            return $data;
        }
    }

//====================================================一级系列end===========================================================



//====================================================二级系列==============================================================

    //二级系列列表
    public function index_lv2(){
        $styleName=I('styleName');
        $pid=I('pid');
        $proTypeId=I('proTypeId');
        $where='s.lv=2 and s.status<2';
        if(!empty($styleName)){
            $where.=" and s.styleName LIKE '%".$styleName."%'";
        }
        if(!empty($pid)){
            $where.=" and s.pid=".$pid;
        }
        if(!empty($proTypeId)){
            $where.=" and s.proTypeId =".$proTypeId;
        }
        $count=$this->get_table('products_style')->alias('s')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data=$this->get_table('products_style')->alias('s')
            ->join('jd_protype AS p ON s.proTypeId=p.proTypeId')
            ->join('jd_products_style AS ps ON ps.styleId=s.pid')
            ->where($where)->field('s.*,p.proName,ps.styleName AS pstyleName')
            ->order('p.proTypeId Asc,s.styleId desc')->limit($page->firstRow.','.$page->listRows)->select();
        foreach($data as &$one){
            $one['status_name']=$this->format_status($one['status']);
            $one['display_name']=$this->format_is_show($one['display']);
            $one['radio_name']=$this->format_Radio($one['radio']);
            $one['child_name']=$this->is_not($one['child']);
            $one['photo_img']=$this->full_url().$this->dir."/m_".$one['photo'];
        }
        $proType=$this->get_table('protype')->where('proLive=1 and lv=1')->select();
        $pstyle=$this->get_table('products_style')->where('lv=1 and status=1')->order('top desc')->select();
        $this->proType=$proType;
        $this->pstyle=$pstyle;
        $this->title='二级系列列表';
        $this->page=$show;
        $this->data=$data;
        $this->display();
    }


    //第二级系列修改页面
    public function edit_lv2(){
        $styleId=I('styleId');
        if($styleId){
            $data=$this->get_table('products_style')->alias('s')
                ->join('jd_products_style AS ps ON ps.styleId=s.pid')
                ->where('s.styleId=%d',$styleId)
                ->field('s.*,ps.styleName as pName')
                ->find();
            $data['photo_img']=$this->full_url().$this->dir."/l_".$data['photo'];
            if($data['top']==0){$data['top']='';}
            $url = $_SERVER['HTTP_REFERER'];
            $this->url=$url;
            $this->data=$data;
            $this->title='修改二级系列';
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //二级系列修改操作
    public function do_edit_lv2(){
        $url=I('post.url');
        $styleId=I('post.styleId');
        $styleName=I('post.styleName');
        $top=I('post.top');
        $display=I('post.display');
        $status=I('post.status');
        $radio=I('post.radio');
        $child=I('post.child');
        if($styleId){
            if($styleName){
                $list=$this->get_table('products_style')->where('styleId=%d',$styleId)->find();
                $styleName=$this->filter_str($styleName);
                $styleName=$this->check_styleName($styleName,$list['proTypeId'],$list['pid'],$styleId);
                if($status!='' && ($status==1 or $status==0)){
                    if($display!='' && ($display==1 or $display==0)) {
                        if($radio!='' && ($radio==1 or $radio==0)) {
                            if($child!='' && ($child==1 or $child==0)) {
                                $old_list = $list;
                                $log = "编辑二级系列 id为：" .$styleId;
                                if(!$top){$top=0;}
                                if($top!=$list['top']){
                                    $list['top']=$top;
                                    $log.=$this->save_log('排序值',$old_list['top'],$top);
                                }
                                if ($styleName != $list['styleName']) {
                                    $list['styleName'] = $styleName;
                                    $log.=$this->save_log('名称',$old_list['styleName'],$list['styleName']);
                                }
                                if($status!=$list['status']){
                                    $list['status']=$status;
                                    $log.=$this->save_log('状态',$old_list['status'],$list['status']);
                                }
                                if($radio!=$list['radio']){
                                    $list['radio']=$radio;
                                    $log.=$this->save_log('单选/多选',$old_list['radio'],$list['radio']);
                                }
                                if($display!=$list['display']){
                                    $list['display']=$display;
                                    $log.=$this->save_log('list显示状态',$old_list['display'],$list['display']);
                                }
                                if($child!=$list['child']){
                                    $list['child']=$child;
                                    $log.=$this->save_log('是否有下级状态',$old_list['child'],$list['child']);
                                }
                                if ($_FILES['image']['name']) {
                                    $image = $this->upFile($this->dir);
                                    $list['photo'] = $image;
                                    $log.='更新了图片';
                                }
                                if (array_diff_assoc($list, $old_list)) {
                                    unset($list['proName']);
                                    $this->get_table("products_style")->save($list);
                                    if ($image) {
                                        unlink('./Public/Uploads/' . $this->$dir . '/' . $old_list['image']);
                                        unlink('./Public/Uploads/' . $this->$dir . '/m_' . $old_list['image']);
                                        unlink('./Public/Uploads/' . $this->$dir . '/l_' . $old_list['image']);
                                    }
                                    $this->write_log('修改二级系列',$log);
                                    $this->success("保存成功", $url);
                                } else {
                                    $this->error("新旧数据一致，不需要保存");
                                }
                            }else{
                                $this->error("请选择 是否有下级");
                            }
                        }else{
                            $this->error("请选择 单选/多选");
                        }
                    }else{
                        $this->error("请选择 是否list界面显示");
                    }
                }else{
                    $this->error("请选择 开启状态");
                }
            }else{
                $this->error("请输入 请系列名称");
            }
        }else{
            $this->error("非法操作");
        }
    }

    //添加二级系列
    public function add_lv2(){
        $styleId=I('styleId');
        if($styleId){
            $proType=$this->get_table('protype')->where('proLive=1 and lv=1')->select();
            $this->proType=$proType;
            $this->pidName=$this->get_table('products_style')->where('styleId=%d',$styleId)->find()['styleName'];
            $this->title='添加二级系列';
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //添加二级系列操作
    public function do_add_lv2(){
        $pid=I('post.pid');
        $display=I('post.display');
        $styleName=I('post.styleName');
        $proTypeId=I('post.proTypeId');
        $radio=I('post.radio');
        $child=I('post.child');
        $top=I('post.top');
        if($pid>0){
            if($styleName){
                $styleName=$this->filter_str($styleName);
                if($proTypeId>0){
                    $styleName=$this->check_styleName($styleName,$proTypeId,$pid);
                    if(($display==1 or $display==0) && ($display!=='')){
                        if(($child==1 or $child==0) && ($child!=='')) {
                            if(($radio==1 or $radio==0) && ($radio!=='')) {
                                if($top>0){$data['top']=$top;}else{$data['top']=0;}
                                $data['pid'] = $pid;
                                $data['styleName'] = $styleName;
                                $data['display'] = $display;
                                $data['proTypeId'] = $proTypeId;
                                $data['radio'] = $radio;
                                $data['child'] = $child;
                                $data['lv']=2;
                                $data['status'] = 1;
                                if ($_FILES['image']['name']) {$data['photo'] = $this->upFile($this->dir);}
                                $add=$this->get_table('products_style')->add($data);
                                if($add){
                                    $this->write_log('添加二级系列','添加二级系列,系列名称:'.$styleName);
                                    $this->success('添加成功',U('Prodstyle/index_lv2'));
                                }else{
                                    $this->error('添加失败');
                                }
                            }else{
                                $this->error("请选择 单选/多选");
                            }
                        }else{
                            $this->error("请选择 是否有下级");
                        }
                    }else{
                        $this->error("请选择 是否显示");
                    }
                }else{
                    $this->error("请选择产品分类");
                }
            }else{
                $this->error('请输入 系列名称');
            }
        }else{
            $this->error("非法操作");
        }
    }


//====================================================二级系列end===========================================================


//====================================================三级系列==============================================================

    //三级系列列表
    public function index_lv3(){
        $styleName=I('styleName');
        $proTypeId=I('proTypeId');
        $where="s.lv=3 and s.status<2";
        if(!empty($styleName)){
            $where.=" and s.styleName LIKE '%".$styleName."%'";
        }
        if(!empty($proTypeId)){
            $where.=" and s.proTypeId =".$proTypeId;
        }
        $count=$this->get_table('products_style')->alias('s')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $data=$this->get_table('products_style')->alias('s')
            ->join('jd_protype AS p ON s.proTypeId=p.proTypeId')
            ->join('jd_products_style AS lv2 ON s.pid=lv2.styleId')
            ->join('jd_products_style AS lv1 ON lv1.styleId=lv2.pid')
            ->field('s.*,p.proName,lv2.styleName AS lv2Name,lv1.styleName AS lv1Name')
            ->order('s.styleId desc')->where($where) ->limit($page->firstRow.','.$page->listRows)->select();
        foreach($data as &$one){
            $one['status_name']=$this->format_status($one['status']);
            $one['display_name']=$this->format_is_show($one['display']);
            $one['photo_img']=$this->full_url().$this->dir."/m_".$one['photo'];
        }
        $proType=$this->get_table('protype')->where('proLive=1 and lv=1')->select();
        $this->proType=$proType;
        $this->title='三级系列列表';
        $this->page=$show;
        $this->data=$data;
        $this->display();
    }

    //三级系列修改页面
    public function edit_lv3(){
        $id=I('get.id');
        if($id>0){
            $data=$this->get_table('products_style')->alias('s')->join('jd_products_style AS lv2 ON s.pid=lv2.styleId')->field('s.*,lv2.styleName AS lv2Name')->where('s.styleId=%d',$id)->find();
            $data['photo_img']=$this->full_url().$this->dir."/l_".$data['photo'];
            if($data['top']==0){$data['top']='';}
            $url = $_SERVER['HTTP_REFERER'];
            $this->url=$url;
            $this->data=$data;
            $this->title='修改三级系列';
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }


    //check_系列是否存在
    function check_styleName($styleName,$proTypeId,$pid,$styleId=null){
        $where="s.styleName='".$styleName."' and s.proTypeId=".$proTypeId." and pid=".$pid;
        if($styleId){
            $where.=" and s.styleId!=".$styleId;
        }
        $res=$this->get_table('products_style')
            ->alias('s')
            ->join('jd_protype AS p ON s.proTypeId=p.proTypeId')
            ->where($where)
            ->field('s.*,p.proName')
            ->find();
        if($res){
            $this->error($res['proName']." 产品分类下的 ".$styleName." 已存在");
        }else{
            return $styleName;
        }
    }

    //添加三级系列页面
    public function add_lv3(){
        $styleId=I('styleId');
        if($styleId){
            $proType=$this->get_table('protype')->where('proLive=1 and lv=1')->select();
            $this->proType=$proType;
            $data=$this->get_table('products_style')->where('styleId=%d',$styleId)->find();
            $this->pidName=$data['styleName'];
            $this->proTypeId=$data['proTypeId'];
            $this->pid=$data['styleId'];
            $this->title='添加三级系列';
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //添加三级系列操作
    public function do_add_lv3(){
        $proTypeId=I('post.proTypeId');
        $pid=I('post.pid');
        $top=I('post.top');
        $styleName=I('post.styleName');
        $display=I('post.display');
        if($pid>0){
            if($styleName){
                $styleName=$this->filter_str($styleName);
                if($proTypeId>0){
                    $styleName=$this->check_styleName($styleName,$proTypeId,$pid);
                    if(($display==1 or $display==0) && ($display!=='')){
                        if($top>0){$data['top']=$top;}else{$data['top']=0;}
                        $data['pid'] = $pid;
                        $data['styleName'] = $styleName;
                        $data['display'] = $display;
                        $data['proTypeId'] = $proTypeId;
                        $data['radio'] = 1;
                        $data['child'] = 0;
                        $data['lv']=3;
                        $data['status'] = 1;
                        if ($_FILES['image']['name']) {$data['photo'] = $this->upFile($this->dir);}
                        $add=$this->get_table('products_style')->add($data);
                        if($add){
                            $this->write_log('添加三级系列','添加三级系列,系列名称：'.$styleName);
                            $this->success('添加成功',U('Prodstyle/index_lv3'));
                        }else{
                            $this->error('添加失败');
                        }
                    }else{
                        $this->error("请选择 是否显示");
                    }
                }else{
                    $this->error("请选择产品分类");
                }
            }else{
                $this->error('请输入 系列名称');
            }
        }else{
            $this->error("非法操作");
        }
    }

    //修改三级系列操作
    public function do_edit_lv3(){
        $styleId=I('post.id');
        $display=I('post.display');
        $styleName=I('post.styleName');
        $status=I('post.status');
        $url=I('post.url');
        $top=I('post.top');
        if($styleId>0){
            if($styleName){
                $list=$this->get_table('products_style')->where('styleId=%d',$styleId)->find();
                $styleName=$this->filter_str($styleName);
                $styleName=$this->check_styleName($styleName,$list['proTypeId'],$list['pid'],$styleId);
                if($status!='' && ($status==1 or $status==0)){
                    if($display!='' && ($display==1 or $display==0)) {
                        $old_list=$list;
                        $log = "编辑三级系列 id为：" .$styleId;
                        if(!$top){$top=0;}
                        if($top!=$list['top']){
                            $list['top']=$top;
                            $log.=$this->save_log('排序值',$old_list['top'],$top);
                        }
                        if ($styleName != $list['styleName']) {
                            $list['styleName'] = $styleName;
                            $log.=$this->save_log('名称',$old_list['styleName'],$list['styleName']);
                        }
                        if($status!=$list['status']){
                            $list['status']=$status;
                            $log.=$this->save_log('状态',$old_list['status'],$list['status']);
                        }
                        if($display!=$list['display']){
                            $list['display']=$display;
                            $log.=$this->save_log('list显示状态',$old_list['display'],$list['display']);
                        }
                        if($_FILES['image']['name']){
                            $image= $this->upFile($this->dir);
                            $list['photo']=$image;
                            $log.='更新了图片';
                        }
                        if(array_diff_assoc($list,$old_list)){
                            unset($list['proName']);
                            $this->get_table("products_style")->save($list);
                            if($image){
                                $this->del_img($this->dir,$old_list['image']);
                            }
                            $this->write_log('修改三级系列',$log);
                            $this->success("保存成功",$url);
                        }else{
                            $this->error("新旧数据一致，不需要保存");
                        }

                    }else{
                        $this->error("请选择 是否list界面显示");
                    }
                }else{
                    $this->error("请选择 开启状态");
                }
            }else{
                $this->error("请输入 系列分类名称");
            }
        }else{
            $this->error("非法操作");
        }
    }
//====================================================三层系列end============================================================



    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1 or $status==2)){
            $data['styleId']=$id;
            $data['status']=$status;
            $result = $this->get_table('products_style')->save($data);
            if($result){
                $this->status_log($status,$id,'系列');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }
    //list显示与不显示
    public function p_display(){
        $id=I('get.id');
        $display=I('get.display');
        if($id>0 && ($display==0 or $display==1)){
            $data['styleId']=$id;
            $data['display']=$display;
            $result = $this->get_table('products_style')->save($data);
            if($result){
                $text=$this->format_is_show($display);
                $this->write_log('list显示状态',$text.'id为'.$id.'的系列');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }

    //end
}