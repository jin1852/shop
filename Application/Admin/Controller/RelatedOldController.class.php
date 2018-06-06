<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;

class RelatedController extends CommonController {

    private $name='关联';

    public function _initialize(){
        parent::_initialize();
        if($_SESSION['admin']['levelId']!=1){
            $this->error('权限不够');
        }
    }

    //关联首页
    public function index(){
        $gid=I('gid');
        $prodName=I('prodName');
        if($gid){
            $where="r.prodFromId=".$gid;
            if($prodName){
                $where.=" and p.prodName like '%".$prodName."%'";
            }
            $data=$this->get_table('related')->alias('r')->join('jd_prods AS p ON r.prodId=p.prodId')->join('jd_prods AS Form ON r.prodFromId=Form.prodId')->where($where)->field('r.*,p.prodName,p.simimg,Form.prodName as Form_prodName')->select();
            foreach($data as &$one){
                $one['simimg_img']=$this->full_url().$one['simimg'];
                $one['status_name']=$this->format_status($one['status']);
                $one['typeId']=$this->format_typeId($one['typeId']);
            }
            $this->gid=$gid;
            $this->data=$data;
            $this->title='关联';
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }



    //修改页面
    public function edit(){
        $id=I('id');
        $gid=I('gid');
        if($id && $gid){
            $data=$this->get_table('prods')->where('prodId=%d',$gid)->find();
            $res=$this->get_table('related')->alias('r')->join('jd_prods AS p ON r.prodId=p.prodId')->where('r.id=%d',$id)->field('r.*,p.proTypeId')->find();
            if($res['top']==0){$res['top']='';}
            $prodId_select=$this->get_table('prods')->where('proTypeId=%d',$res['proTypeId'])->select();
            $r = $this->get_table('protype')->where("proLive=1")->order('proPath')->select();
            $r = $this->demo($r, 0);
            $options = $this->option($r,'',$res['proTypeId']);
            $this->data=$data;
            $this->options=$options;
            $this->prodId_select=$prodId_select;
            $this->res=$res;
            $this->title=$this->name;
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //添加页面
    public function add(){
        $gid=I('gid');
        if($gid){
            $data=$this->get_table('prods')->where('prodId=%d',$gid)->find();
            $r = $this->get_table('protype')->where("proLive=1")->order('proPath')->select();
            $r = $this->demo($r, 0);
            $options = $this->option($r);
            $this->data=$data;
            $this->options=$options;
            $this->title=$this->name;
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    //添加操作
    public function do_add(){
        $prodFromId=I('post.prodFromId');
        $prodId=I('post.prodId');
        $typeId=I('post.typeId');
        $top=I('post.top');
        if($prodFromId){
            if($prodId>0){
                if($typeId==1 or $typeId==2 or $typeId==3){
                    $where='prodFromId='.$prodFromId.' and prodId='.$prodId.' and typeId='.$typeId;
                    $this->check_prodId($where);
                    if($top>0){
                        $data['top']=$top;
                    }else{
                        $data['top']=0;
                    }
                    $data['typeId']=$typeId;
                    $data['prodFromId']=$prodFromId;
                    $data['prodId']=$prodId;
                    $data['status']=1;
                    $add=$this->get_table('related')->add($data);
                    if($add){
                        $this->success("添加成功",U('Related/index','gid='.$prodFromId));
                    }else{
                        $this->error("添加失败");
                    }
                }else{
                    $this->error("请选择所属关联分类");
                }
            }else{
                $this->error("请选择关联产品");
            }
        }else{
            $this->error("非法操作");
        }
    }

    //检查是否存在
    private function check_prodId($where){
        $data=$this->get_table('related')->where($where)->select();
        if($data){
            $this->error("已存在");
        }
    }


    //修改操作
    public function do_edit(){
        $prodId=I('post.prodId');
        $top=I('post.top');
        $typeId=I('post.typeId');
        $status=I('post.status');
        $id=I('post.id');
        if($id>0){
            $list=$this->get_table('related')->where('id=%d and prodFromId=%d',$id)->find();
            $old_list=$list;
            if($prodId>0){
                $where='prodFromId='.$list['prodFromId'].' and prodId='.$prodId.' and typeId='.$typeId.' and id!='.$id;
                $this->check_prodId($where);
                if($typeId==1 or $typeId==2 or $typeId==3){
                    if($status==0 or $status==1){
                        if($status!=$list['status']){
                            $list['status']=$status;
                        }
                        if($prodId!=$list['prodId']){
                            $list['prodId']=$prodId;
                        }
                        if($typeId!=$list['typeId']){
                            $list['typeId']=$typeId;
                        }
                        if($top>0){
                            if($top!=$list['top']){
                                $list['top']=$top;
                            }
                        }else{
                            $list['top']=0;
                        }
                    }else{
                        $this->error("请选择状态");
                    }
                }else{
                    $this->error("请选择所属关联分类");
                }
            }else{
                $this->error("请选择关联产品");
            }
            if(array_diff_assoc($list,$old_list)){
                $re=$this->get_table("related")->save($list);
                if($re){
                    $this->success("修改成功",U('Related/index','gid='.$list['prodFromId']));
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

    //开关操作
    public function status(){
        $id=I('get.id');
        $status=I('get.status');
        if($id>0 && ($status==0 or $status==1)){
            $data['id']=$id;
            $data['status']=$status;
            $result =$this->get_table("related")->save($data);
            if($result){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
        }
    }


    //格式化
    private function format_typeId($data){
        switch($data){
            case 1:return 'Related Product Price';break;
            case 2:return 'Similar Product';break;
            case 3:return 'Related Product';break;
            default:return'未知分类';break;
        }
    }

    //改变分类
    public function changeproTypeId(){
        $proTypeId=I('post.proTypeId');
        if($proTypeId>0){
            $data=$this->get_table('prods')->where('proTypeId=%d',$proTypeId)->select();
            if($data){
                $option="<option value=''>请选择</option>";
                foreach($data as &$one){
                    $option.="<option value=".$one['prodId'].">".$one['prodName']."</option>";
                }
            }else{
                $option="<option value=''>该分类未添加产品</option>";
            }
            $this->list_result_json('1','成功改变',$option);
        }else{
            $this->list_result_json('-778','数据有误','');
        }
    }

    //json格式
    public function list_result_json($status,$msg,$result){
        echo json_encode(array('status'=>$status,'msg'=>$msg,'result'=>$result));
        $this->end();
    }

    //end
    public function end(){
        die();
    }


    //递归获取分类信息
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

    //根据传入的数组生成对应的option
    private function option($r,$notdis=null,$id=null){
        $selected = ''; // 判断分类是否选中，如果被选中，其值为select，没有选中的话其值为空
        $options = '';  //用来存储我们要生成的option信息
        if($notdis){
            $disabled='';
        }else{
            $disabled=' disabled';
        }
        //通过遍历获得我们需要的格式组成的option
        foreach($r as $v){ //第一层的遍历
            if($v['proTypeId'] == $id){
                $selected = 'selected';
            }
            $options .= '<option value="'.$v['proTypeId'].'" '.$selected.$disabled.'>'.$v['proName'].'.</option>';
            $selected = ''; //及时的将$selected的值清空，保证后面的状态

            if($v['child']){
                foreach ($v['child'] as $v2) { //第二层分类
                    if($v2['proTypeId'] == $id){
                        $selected = "selected";
                    }
                    if($v2['child']){
                        //如果仍然有子级的话，就加上disabled
                        $options.= '<option value="'.$v2['proTypeId'].'" '.$selected.$disabled.' >&nbsp;&nbsp;&nbsp;&nbsp;'.$v2['proName'].'</option>';
                        $selected = '';

                    }else{ //如果没有子级的话
                        $options.= '<option value="'.$v2['proTypeId'].'" '.$selected.'>&nbsp;&nbsp;&nbsp;&nbsp;'.$v2['proName'].'</option>';
                        $selected = '';
                    }
                    if($v2['child']){ //第三层分类
                        foreach ($v2['child'] as $v3) {
                            if($v3['proTypeId'] == $id){
                                $selected = "selected";
                            }

                            $options.= '<option value="'.$v3['proTypeId'].'" '.$selected.'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$v3['proName'].'</option>';
                            $selected = '';
                        }
                    }
                }
            }
        }
        return $options;
    }

    //end
}