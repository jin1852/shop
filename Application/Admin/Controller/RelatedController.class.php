<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Page;

class RelatedController extends CommonController {

    private $name='关联';
    private $dir='Related-Product';

    //关联首页
    public function index(){
        $gid=I('gid');
        $prodName=I('prodName');
        if($gid){
            $where="r.status<2 and r.prodFromId=".$gid;
            if($prodName){
                $where.=" and p.prodName like '%".$prodName."%'";
            }
            $data=$this->get_table('related')->alias('r')->join('jd_prods AS Form ON r.prodFromId=Form.prodId')->where($where)->field('r.*,Form.prodName as Form_prodName')->select();
            foreach($data as &$one){
                if($one['typeId']==3 or $one['typeId']==2){
                    $res=$this->get_table('prods')->where('prodId=%d',$one['prodId'])->field('prodName,simimg')->find();
                    $one['name']=$res['prodName'];
                    $one['image']=$res['simimg'];
                    $one['image_img']=$this->full_url().$one['image'];
                }else{
                    $one['image_img']=$this->full_url().$this->dir.'/m_'.$one['image'];
                }
                $one['typeId']=$this->changetypeId($one['typeId']);
                $one['status_name']=$this->format_status($one['status']);

            }
            $this->gid=$gid;
            $this->data=$data;
            $this->title='关联';
            $this->display();
        }else{
            $this->error("非法操作");
        }
    }

    public function changetypeId($id){
        switch($id){
            case 1:$text='Related Product Price';break;
            case 2:$text='Similar Produc';break;
            case 3:$text='Related Product';break;
        }
        return $text;
    }

    //修改页面
    public function edit(){
        $id=I('id');
        $gid=I('gid');
        if($id){
            $data=$this->get_table('prods')->where('prodId=%d',$gid)->find();
            $res=$this->get_table('related')->where('id=%d',$id)->find();
            if($res['typeId']==3 or $res['typeId']==2){
                $res_typeId=$this->get_table('prods')->where('prodId=%d',$res['prodId'])->find();
            }
            $res['image_img']=$this->full_url().$this->dir.'/l_'.$res['image'];
            $res['top']=$this->not_top($res['top']);
            $res['link']=$this->not_top($res['link']);
            $prodId_select=$this->get_table('prods')->where('proTypeId=%d',$res_typeId['proTypeId'])->select();
            $r = $this->get_table('protype')->where("proLive=1")->order('proPath')->select();
            $r = $this->demo($r, 0);
            $options = $this->option($r,'',$res_typeId['proTypeId']);
            $url= $_SERVER['HTTP_REFERER'];
            $this->data=$data;
            $this->url=$url;
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


    //检查是否存在
    private function check_prodId($where){
        $data=$this->get_table('related')->where($where)->select();
        if($data){
            $this->error("已存在");
        }
    }

    //添加操作
    public function do_add(){
        $prodFromId=I('post.prodFromId');
        $typeId=I('post.typeId');
        $prodId=I('post.prodId');
        $name=I('post.name');
        $link=I('post.link');
        $top=I('post.top');
        if($prodFromId){
            $data['typeId']=$typeId;
            $data['prodFromId']=$prodFromId;
            if($typeId==3 or $typeId==2){
                if($prodId>0){
                    $data['prodId']=$prodId;
                }else{
                    $this->error("请选择关联产品");
                }
            }else{
                if($name){
                    $this->filter_str($name);
                    $data['name']=$name;
                }else{
                    $this->error("请输入名称");
                }
                if($link){
                    $data['link']=$link;
                }else{
                    $data['link']='javascript:void(0);';
                }
                if($_FILES['image']['name']){
                    $image= $this->upFile($this->dir);
                    $data['image']=$image;
                }else{
                    $this->error("请上传图片");
                }
            }

            if($top>0){
                $data['top']=$top;
            }else{
                $data['top']=0;
            }
            $data['status']=1;
            $add=$this->get_table('related')->add($data);
            if($add){
                $this->write_log("添加产品关联",'产品id:'.$prodFromId."添加关联");
                $this->success("添加成功",U('Related/index','gid='.$prodFromId));
            }else{
                $this->error("添加失败");
            }
        }else{
            $this->error("非法操作");
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
                    $option.="<option value=".$one['prodId'].">id :".$one['prodId'].'======编号 :'.$one['prodNO'].'======名称 :'.$one['prodName']."</option>";
                }
            }else{
                $option="<option value=''>该分类未添加产品</option>";
            }
            $this->list_result_json('1','成功改变',$option);
        }else{
            $this->list_result_json('-778','数据有误','');
        }
    }

    //修改操作
    public function do_edit(){
        $name=I('post.name');
        $url=I('post.url');
        $link=I('post.link');
        $top=I('post.top');
        $id=I('post.id');
        $typeId=I('post.typeId');
        $prodId=I('post.prodId');
        if($id>0){
            $list=$this->get_table('related')->where('id=%d',$id)->find();
            $old_list=$list;
            $log = "编辑商品关联 id为：" .$id;
            if(!$top){$top=0;}
            if($top!=$list['top']){
                $list['top']=$top;
                $log.=$this->save_log('排序值',$old_list['top'],$top);
            }
            if($typeId>0){
                if($typeId!=$list['typeId']){
                    $list['typeId']=$typeId;
                    $log.=$this->save_log('关联类型',$old_list['typeId'],$typeId);
                }
                if($typeId==3 or $typeId==2){
                    if($prodId>0){
                        if($prodId!=$list['prodId']){
                            $list['prodId']=$prodId;
                            $log.=$this->save_log('关联产品id',$old_list['prodId'],$prodId);
                        }
                    }else{
                        $this->error("请选择关联产品");
                    }
                }else{
                    if($name){
                        if($name!=$list['name']){
                            $this->filter_str($name);
                            $list['name']=$name;
                            $log.=$this->save_log('名称',$old_list['name'],$name);
                        }
                    } else{
                        $this->error("请输入名称");
                    }
                    if(!$link){$link='javascript:void(0);';}
                    if($link!=$list['link']){
                        $list['link']=$link;
                        $log.=$this->save_log('链接地址',$old_list['link'],$link);
                    }
                    if($_FILES['image']['name']){
                        $image= $this->upFile($this->dir);
                        $list['image']=$image;
                        $log.=',更新了图片';
                    }else{
                        if(!$list['image']){
                            $this->error("请上传图片");
                        }
                    }
                }
            }else{
                $this->error("请选择关联类型");
            }
            if(array_diff_assoc($list,$old_list)){
                $re=$this->get_table("related")->save($list);
                if($re){
                    if($image){
                        $this->del_img($this->dir,$old_list['image']);
                    }
                    $this->write_log('修改产品关联',$log);
                    $this->success("修改成功",$url);
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
        $this->check_1to3();
        $id=I('get.id');
        $status=I('get.status');
        $this->do_status($id,$status);
    }

    public function del(){
        $this->check_1or3();
        $id=I('get.id');
        $status=I('get.status');
        $this->do_status($id,$status);
    }

    public function do_status($id,$status){
        if($id>0 && ($status==0 or $status==1 or $status=2)){
            $data['id']=$id;
            $data['status']=$status;
            $result =$this->get_table("related")->save($data);
            if($result){
                $this->status_log($status,$id,'产品关联');
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }else{
            $this->error("非法操作");
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