<?php
namespace Admin\Controller;
use Think\Controller;
class ZcController extends CommonController {
    private $dir='Zc';
    /*访问权限*/
    public function _initialize(){
        parent::_initialize();
        $this->check_super();
    }

    //初始化阶段
    public function edit_stage(){
        $id=I('post.id');
        $stage=$this->get_table('zc_conf')->where("zc_id=%d",$id)->order("price asc")->select();
        if($stage){
            die($this->list_json_result(1,"加载成功",$stage));
        }else{
            die($this->list_json_result(-1,'没有数据',''));
        }
    }
    //修改阶段
    public  function save_stage(){
        $price=I('post.price');
        $end=I('post.end');
        $id=I('post.stage_id');
        $zc_id=I('post.fid');
        if($price && $end && $id && $zc_id){
            $last=$this->get_table('zc_conf')->where("zc_id=%d and price<%d",$zc_id,$price)->order("price desc")->find();
            if($last){
                $last['start']=$end+1;
                $this->get_table('zc_conf')->save($last);
            }
            $data['end']=$end;
            $data['price']=$price;
            $res=$this->get_table('zc_conf')->where('id=%d',$id)->save($data);
            if($res){
                $stage=$this->get_table('zc_conf')->where("zc_id=%d",$zc_id)->order("price asc")->select();
                die($this->list_json_result(1,"修改成功",$stage));
            }else{
                die($this->list_json_result(-2,"修改失败",''));
            }
        }else{
            die($this->list_json_result(-1,"缺失数据",''));
        }
    }
    //执行添加阶段操作
    public function new_add_stage($end,$start,$price,$zc_id,$big_data,$small_data){
        $add['end']=$end;
        $add['start']=$start;
        $add['price']=$price;
        $add['status']=1;
        $add['zc_id']=$zc_id;
        $re=$this->get_table('zc_conf')->add($add);
        if($re){
            if($big_data && !$small_data){
                $big_data['end']=$start-1;
                $this->get_table('zc_conf')->save($big_data);
            }else{
                $small_data['start']=$end+1;
                $this->get_table('zc_conf')->save($small_data);
            }
            $stage=$this->get_table('zc_conf')->where("zc_id=%d",$zc_id)->order("price asc")->select();
            die($this->list_json_result(1,"添加成功",$stage));
        }else{
            die($this->list_json_result(-1,"添加失败",''));
        }
    }
    //添加阶段
    public function do_add_stage(){
        $zc_id=I('post.id');
        $price=I('post.price');
        $end=I('post.end');
        if($zc_id && $price && $end){
            $res=$this->get_table('zc_conf')->where("zc_id=%d and (price=%d or end=%d)",$zc_id,$price,$end)->find();
            if(!$res){
                $data=$this->get_table('zc_conf')->where("zc_id=%d",$zc_id)->order('price desc')->select();
                foreach($data as &$big){
                    if($big['price']>$price){
                        $big_data=$big;
                    }
                }
                foreach($data as &$small){
                    if($small['price']<$price){
                        $small_data=$small;
                        break;
                    }
                }
                if($big_data && !$small_data){
                    //插入在最前（价格最小,数量最大）
                    foreach($data as &$big_end){
                        if($end<$big_end['end']){
                            die($this->list_json_result(-11,"阶段数量有误，此价格需对应最大数量",''));
                        }
                    }
                    $this->new_add_stage(-1,$end+1,$price,$zc_id,$big_data,null);
                }elseif(!$big_data && $small_data){
                    //插入在最后（价格最大）
                    foreach($data as &$small_end){
                        if($small_end['end']!=-1){
                            if($end>$small_end['end']){die($this->list_json_result(-12,"阶段数量有误，此价格需对应最小数量",''));}
                        }
                    }
                    $this->new_add_stage($end,0,$price,$zc_id,null,$small_data);
                }elseif($big_data && $small_data){
                    //插入在中间
                    if($end<$small_data['end'] && $end>$big_data['end']){
                        $this->new_add_stage($end,$big_data['end']+1,$price,$zc_id,$big_data,$small_data);
                    }else{
                        die($this->list_json_result(-13,"阶段数量有误，此价格对应数量阶段需大于前一级，小于下一级",''));
                    }
                }else{
                    die($this->list_json_result(-999,"非法操作",''));
                }
            }else{
                die($this->list_json_result(-2,"最大数量或价格已存在,请重新添加",''));
            }
        }else{
            die($this->list_json_result(-1,"缺失数据",''));
        }
    }
    //json
    public function list_json_result($status, $msg, $result){
        return json_encode(array('status' => $status, 'msg' => $msg, 'result' => $result));
    }
    //修改众筹商品页面
    public function edit(){
        $id=I('get.id');
        if($id && $id>0){
            $data=$this->get_table('zc')->where("id=%d",$id)->find();
            if($data){
                $data['start_time_date']=$this->time_format($data['start_time']);
                $data['end_time_date']=$this->time_format($data['end_time']);
                $data['image']=$this->full_url().$this->dir."/l_".$data['image'];
                $this->title="修改众筹商品";
                $this->data=$data;
                $this->display();
            }else{
                $this->error("操作失败");
            }
        }else{
            $this->error("非法操作");
        }
    }
    //修改众筹商品操作
    public function do_edit(){
        $id=I('post.id');
        $title=(I('post.title'));
        $content=I('post.content');
        $price=I('post.price');
        $expect_number=I('post.expect_number');
        $start_time=I('post.start_time');
        $end_time=I('post.end_time');
        $top=I('post.top');
        $status=I('post.status');
        if($_FILES['image']['name']){
            $image= $this->upFile($this->dir);
        }
        $current_number=I('post.current_number');
        if($id>0){
            $list=$this->get_table('zc')->where("id=%d",$id)->find();
            if($list){
                $old_list=$list;
                if($title){
                    $title=$this->filter_str($title);
                    if($list['title']!=$title){
                        $this->check_field($id,$title,"zc","title");
                        $list['title']=$title;
                    }
                }else{
                    $title->error("请输入众筹名称");
                }
                if($content){
                    if($list['content']!=$content){
                        $content=$this->filter_str($content);
                        $list['content'] = htmlspecialchars_decode(html_entity_decode($content));
                    }
                }
                if($price>0){
                    if( $list['price']!=$price){
                        $list['price']=$price;
                    }
                }else{
                    $this->error('请输入正确的价格');
                }
                if($expect_number){
                    if($list['expect_number']!=$expect_number){
                        $list['expect_number']=$expect_number;
                    }
                }else{
                    $this->error('请填写期待数量');
                }
                if($current_number){
                    if($current_number && $list['current_number']!=$current_number){
                        $list['current_number']=$current_number;
                    }
                }else{
                    $list['current_number']=0;
                }
                if($start_time){
                    if($list['start']!=$start_time){
                        $list['start_time']=strtotime($start_time);
                    }
                }else{
                    $this->error("请选择开始时间");
                }

                if($end_time){
                    if($list['end_time']!=$end_time){
                        $list['end_time']=strtotime($end_time);
                    }
                }else{
                    $this->error("请选择结束时间");
                }
                if($top>=0){
                    if($top!=$list['top']){
                        $list['top']=$top;
                    }
                }else{
                    $list['top']=0;
                }

                if(($status==0 or $status==1) && $list['status']!=$status){
                    $list['status']=$status;
                }
                if($image){
                    $list['image']=$image;
                }
                if(array_diff_assoc($list,$old_list)){
                    $re=$this->get_table("zc")->save($list);
                    if($re){
                        if($image){
                            unlink('./Public/Uploads/'.$this->dir.'/'.$old_list['image']);
                            unlink('./Public/Uploads/'.$this->dir.'/m_'.$old_list['image']);
                        }
                        $this->success("保存成功",U('Zc/ZcAll'));
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
    //众筹商品展示
    public function ZcAll(){
        $title=I('title');
        $where=1;
        if(!empty($title)){
            $where.=" and title LIKE '%".$title."%'";
        }
        $count=$this->get_table('zc')->where($where)->count();
        $page=$this->get_page($count,15);
        $show = $page->show(); //分页显示输出
        $list=$this->get_table('zc')->where($where)->limit($page->firstRow.','.$page->listRows)->select();
        foreach($list as &$one){
            $one['status_name']=$this->format_status($one['status']);
            $one['start_time_date']=$this->time_format($one['start_time']);
            $one['end_time_date']=$this->time_format($one['end_time']);
            $one['image']=$this->full_url()."Zc/m_".$one['image'];
        }
        $this->page=$show;
        $this->data=$list;
        $this->display('Zc/index');
    }
    //添加众筹商品页
    public function addZc(){
        $this->display('Zc/add');
    }
    //添加众筹商品操作
    public function do_add_Zc(){
        $title=I('post.title');
        $content=I('post.content');
        $price=I('post.price');
        $expect_number=I('post.expect_number');
        $start_time=I('post.start_time');
        $end_time=I('post.end_time');
        $top=I('post.top');
        $stage=I('post.stage');
        $stage=$this->check_array($stage);
        $current_number=I('post.current_number');
        if($start_time){
            $data['start_time']=strtotime($start_time);
        }else{
            $this->error("请选择开始时间");
        }
        if($end_time){
            $data['end_time']=strtotime($end_time);
        }else{
            $this->error("请选择结束时间");
        }
        if($content){
            $content =$this->filter_str($content);
            $data['content'] = htmlspecialchars_decode(html_entity_decode($content));
        }else{
            $data['content']='';
        }
        if($expect_number){
            $data['expect_number']=$expect_number;

        }else{
            $this->error("请填写期待数量");
        }
        if($current_number){
            $data['current_number']=$current_number;
        }else{
            $data['current_number']=0;
        }
        if($price>0){
            $data['price']=$price;
        }else{
            $this->error("请输入正确的价格");
        }
        if($title){
            $title=$this->filter_str($title);
            $data['title']= $this->check_field(false,$title,"zc","title");
        }else{
            $title->error("请输入众筹名称");
        }
        if($top>=0){
            $data['top']=$top;
        }else{
            $data['top']=0;
        }
        $data['image']= $this->upFile($this->dir);

        $data['status']=1;
        $add=$this->get_table('zc')->add($data);
        if($add){
            $stage=$this->add_stage($stage,$add);
            $add_conf=$this->get_table('zc_conf')->addAll($stage);
            if($add_conf){
                $this->success('添加成功',U('Zc/ZcAll'));
            }else{
                $this->error("阶段添加失败");
            }
        }else{
            $this->error("添加失败");
        }

    }
    //关闭
    public function zc_off(){
        $id = I("get.id");
        $url = "zc";
        $this->status_off($url, $id);
    }
    //开启
    public function zc_on(){
        $id = I("get.id");
        $url = "zc";
        $this->status_on($url, $id);
    }
    //商品详情
    public function detail(){
        $id=I('get.id');
        if($id && $id>0){
            $data=$this->get_table('zc')->where("id=%d",$id)->find();
            $stage=$this->get_table('zc_conf')->where("zc_id=%d",$id)->order("price desc")->select();
            if($data && $stage){
                $max=count($stage);
                $count=1;
                foreach($stage as &$one){
                    if($data['current_number']>=$one['start'] && $data['current_number']<=$one['end']){
                        $data['stage_no']=$count;
                        $data['stage_price']=$one['price'];
                    }
                    $count++;
                };
                if(!isset($data['stage_no'])){
                    $data['stage_no']=$max;
                    $data['stage_price']=$stage[$max-1]['price'];
                }
                $data['start_time_date']=$this->time_format($data['start_time']);
                $data['end_time_date']=$this->time_format($data['end_time']);
                $this->title="众筹商品详情";
                $this->goodData=$data;
                $this->display();
            }else{
                $this->error("操作失败");
            }
        }else{
            $this->error("非法操作");
        }
    }
    //二维数组冒泡升序
    public function array_sort($arr,$key){
        $len = count($arr);
        for($i=1;$i<$len;$i++) {
            for($j=$len-1;$j>=$i;$j--)
                if($arr[$j][$key]<$arr[$j-1][$key]) {
                    $data=$arr[$j];
                    $arr[$j]=$arr[$j-1];
                    $arr[$j-1]=$data;
                }
        }
        return $arr;
    }
    //判断是否阶段价格是否正确(数量越到价格越低)
    public function check_price($data){
        foreach($data as $key=>$one){
            if($data[$key]['price']<$data[$key+1]['price'])
            {
                $this->error("阶段参数有误");
            }
        }
    }
    //阶段参数处理
    private function check_array($data){
        $price=array();
        $end=array();
        foreach($data as &$one){
            $one['price']=trim($one['price']);
            $one['end']=trim($one['end']);
            if($one['price']!=0 && $one['price']!=null && $one['price']!='' && is_numeric($one['price']) && $one['end']!=0 && $one['end']!='' && $one['end']!=null && is_numeric($one['end'])){
                $price=array_merge_recursive($price,array($one['price']));
                $end=array_merge_recursive($end,array($one['end']));
            }else{
                $this->error("阶段参数不全或有误");
            }
        }
        $this->stage_array_error($price,'阶段价格出现相等');
        $this->stage_array_error($end,'阶段最大数量出现重复');
        $data=$this->array_sort($data,'end');
        $this->check_price($data);
        return $data;
    }
    //阶段补全
    private function add_stage($data,$zc_id){
        $count = count($data);
        for($i=0;$i<$count;$i++){
            switch ($i){
                case 0:$data[$i]['start']=0;break;
                case $count-1:$data[$i]['start']=$data[$i-1]['end']+1;$data[$i]['end']=-1;break;
                default:$data[$i]['start']=$data[$i-1]['end']+1;break;
            }
            $data[$i]['status']=1;
            $data[$i]['zc_id']=$zc_id;
        }
        return $data;
    }
    //判断是否阶段参数相等
    private function stage_array_error($data,$text){
        if (count($data) != count(array_unique($data))) {
            $this->error($text);
        }
    }
    //end
}