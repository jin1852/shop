<?php
namespace Admin\Controller;
use Think\Controller;
class ImgshowController extends CommonController {
    private $name='子页大图';

    private $dir="ImageShow";

    public function _initialize(){
        parent::_initialize();
        $this->check_super();
    }
    //分类图片展示1
    public function ImageAll() {
        $sorts=I('sorts');
        $where='status<2';
        if(!empty($sorts)){
            $where.=" and sorts =".$sorts;
        }
        $count= $this->get_table('imageshow')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $list = $this->get_table('imageshow')->where($where)->limit($page->firstRow.','.$page->listRows)->order('id desc')->select();
        foreach($list as &$one){
            $one['status_name']=$this->format_status($one['status']);
            $one['create_time_date']=$this->time_format($one['create_time']);
            $one['updata_time_date']=$this->time_format($one['updata_time']);
            $one['LinkType_name']=$this->format_Linktype($one['LinkType']);
            $one['image']=$this->full_url().$this->dir."/m_".$one['image'];
            $one['sorts_val']=$one['sorts'];
            $one['sorts']=$this->switch_html($one['sorts']);
        }
        $this->option=$this->html_Array();
        $this->page=$show;
        $this->title=$this->name;
        $this->list=$list;
        $this->display();
    }
    //添加分类图片页
    public function ImageAdd() {
        $this->option=$this->html_Array();
        $this->title=$this->name;

        $pid=I("pid",0);
        if($pid>0) {
            $pimg = M("imageshow")->where("id=" . $pid)->find();
            if($pimg){
                $this->assign("pimg",$pimg);
            }
        }
        $this->assign("pid",$pid);

        $this->display('Imgshow/ImageAdd');

    }
    //修改分类图片页
    public function ImageEdit() {
        $data = I('param.');
        $data = $this->get_table('imageshow')->where($data)->find();
        $data['image']=$this->full_url().$this->dir."/l_".$data['image'];
        if($data['LinkType']==0){
            if($data['OpenLink']=='javascript:void(0);'){
                $data['OpenLink']='';
            }
        }elseif($data['LinkType']==1){
            if($data['link']=='javascript:void(0);'){
                $data['link']='';
            }
        }



        $url= $_SERVER['HTTP_REFERER'];
        $this->url=$url;
        $this->data=$data;
        $this->option=$this->html_Array();
        $this->title=$this->name;
        $this->display('Imgshow/ImageEdit');
    }

    //添加操作
    public function ImageInsert() {
        $sorts=I('post.sorts');
        $address=I('post.address');
        $data['LinkType']=I('post.LinkType');
        $top=I('post.top');
        $type=I('post.type');
        $title=I("post.title","");
        $pid=I("pid",0);
        if($_FILES['image']['name']){
            $data['image']= $this->upFile($this->dir);
        }else{
            $this->error("请上传图片");
        }
        if($sorts){
            $data['sorts']=$sorts;
        }else{
            $this->error("请选择图片所属");
        }
        if(!$address){
            $address='javascript:void(0);';
        }
        if($top){
            $data['top']=$top;
        }else{
            $data['top']=0;
        }
        if($type==0 or $type==1){
            $data['type']=$type;
        }
        if($data['LinkType']==0){
            $data['OpenLink']=$address;
        }else{
            $data['link']=$address;
        }
        $data['title']=$title;
        $data['create_time']=time();
        $pimg=M("imageshow")->where("id=".$pid)->find();
        $data['pid']=$pid;
        $data['lv']=$pimg['lv']+1;
        $add=$this->get_table('imageshow')->add($data);
        $this->updateChild($pid);
        if($add) {
            $this->write_log('添加子页大图','添加子页大图,图片所属页面:'.$this->switch_html($sorts));
            $this->success('添加成功',U('Imgshow/ImageAll'));
        }else{
            $this->error('添加失败');
        }
    }
    private function updateChild($pid){
        if($pid==0)return false;
        $count=M("imageshow")->where("status=1 and pid=".$pid)->count();
        $child=0;
        if($count>0){
            $child=1;
        }elseif($count==0){
            $child=0;
        }
        M("imageshow")->where("id={$pid}")->setField('child',$child);
        return true;
    }
    //修改操作
    public function ImageUpdate() {
        $id=I('post.id');
        $url=I('post.url');
        $address=I('post.address');
        $type=I('post.type');
        $LinkType=I('post.LinkType');
        $sorts=I('post.sorts');
        $top=I('post.top');
        $title=I("post.title","");
        if($id>0){
            $list=$this->get_table('imageshow')->where("id=%d",$id)->find();
            if($list){
                $old_list=$list;
                $log = "编辑子页大图 id为：" .$id;
                if($sorts){
                    if($list['sorts']!=$sorts){
                        $list['sorts']=$sorts;
                        $log.=$this->save_log('图片所属',$this->switch_html($old_list['sorts']),$this->switch_html($sorts));
                    }
                }else{
                    $this->error("请选择图片所属");
                }
                if(!$top){$top=0;}
                if($top!=$list['top']){
                    $list['top']=$top;
                    $log.=$this->save_log('排序值',$old_list['top'],$top);
                }
                if(!$address){
                    $address='javascript:void(0);';
                }
                if($type==0 or $type==1){
                    if($list['type']!=$type){
                        $list['type']=$type;
                        $log.=$this->save_log('全屏/宽屏',$old_list['type'],$type);
                    }
                }
                $list['title']=$title;
                if($LinkType==0 or $LinkType==1){
                    if ($list['LinkType']!=$LinkType){
                        $list['LinkType']=$LinkType;
                        $log.=$this->save_log('链接类型',$old_list['LinkType'],$LinkType);
                    }
                    if($LinkType==0){
                        if($address && $address!=$list['OpenLink']){
                            $log.=$this->save_log('外链地址',$old_list['OpenLink'],$address);
                            $list['OpenLink']=$address;
                        }
                    }elseif($LinkType==1){
                        if($address && $address!=$list['link']){
                            $log.=$this->save_log('内链地址',$old_list['link'],$address);
                            $list['link']=$address;
                        }
                    }
                }
                if($_FILES['image']['name']){
                    $image= $this->upFile($this->dir);
                    $list['image']=$image;
                    $log.=',更新了图片';
                }
                if(array_diff_assoc($list,$old_list)){
                    $list['updata_time']=time();
                    $re=$this->get_table("imageshow")->save($list);
                    if($re){
                        if($image){
                            $this->del_img($this->dir,$old_list['image']);
                        }
                        $this->write_log('修改子页大图',$log);
                        $this->success("保存成功",$url);
                    }else{
                        $this->error("保存失败");
                    }
                }else{
                    $this->error("新旧数据一致，不需要保存");
                }

            }else{
                $this->error("数据不存在");
            }
        }else{
            $this->error("非法操作");
        }
    }

    //开关操作
    public function Imagestatus(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1 or $status==2)){
            $data['id']=$id;
            $data['status']=$status;
            $data['updata_time']=time();
            $result = $this->get_table('imageshow')->save($data);
            $imageshow=M("imageshow")->where('id='.$id)->find();
            if($imageshow['pid']>0) {
                $this->updateChild($imageshow['pid']);
            }
            if($result){
                $this->status_log($status,$id,'子页大图');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }

    //html子页标题名数组
    function html_Array(){
        $option=array(
            array('Id'=>1,'name'=>'Index'),
            array('Id'=>2,'name'=>'Series'),
            array('Id'=>4,'name'=>'Manufacturing'),
            array('Id'=>5,'name'=>'Hot'),
            array('Id'=>6,'name'=>'Style'),
            array('Id'=>7,'name'=>'Design'),
            array('Id'=>8,'name'=>'showtime'),
            array('Id'=>9,'name'=>'showroom')
        );
        return $option;
    }

    function switch_html($data){
        switch($data){
            case 1: return  'Index';
            case 2: return  'Series';
            case 4: return  'Manufacturing';
            case 5: return  'Hot';
            case 6: return  'Style';
            case 7: return  'Design';
            case 8: return  'showtime';
            case 9: return  'showroom';
            default:return  '未知参数';
        }
    }
    //end
}