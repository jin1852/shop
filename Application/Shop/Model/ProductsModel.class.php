<?php
namespace Shop\Model;
class ProductsModel extends BaseModel{
    //配置数据表
    public $typeModel = 'jd_protype';
    public $goodsModel = 'jd_prods';
    public $products = 'jd_products';
    public $linkModel = 'jd_related';
    public $products_styles_Model = 'jd_products_style';
    public $prods_styles_Model = 'jd_prods_styles';
    public $cache=3600;

    //拼接全部id
    public function push_id($kid,$fid,$tid){
        $All_id = array();
        if ($kid) {
            $All_id = $this->push($All_id, $kid);
        }
        if ($fid) {
            $All_id = $this->push($All_id, $fid);
        }
        if ($tid) {
            $All_id = $this->push($All_id, $tid);
        }
        return $All_id;
    }

    //
    private function push($data,$id){
        if ($id) {
            $list = explode(',', $id);
            if ($list) {
                foreach ($list as &$one) {
                    array_push($data, $one);
                }
            } else {
                array_push($data, $id);
            }
        }
        return $data;
    }

    //解释层级 lv
    public function find_lv($id,$lv,$pid){
        $table='jd_protype';
        switch($lv){
            case 1:
                if ($id > 0) {
                    $type_lv1 = M()->table($table)->where('proTypeId=' . $id)->cache(true, $this->cache_time)->find();
                    if ($type_lv1) {
                        $type_id = $type_lv1;
                    } else {
                        $type_id = '';
                    }
                } else {
                    $type_id = '';
                }
                break;
            case 2:
                if ($pid > 0) {
                    $type_lv1 = M()->table($table)->where('proTypeId=' . $pid)->cache(true, $this->cache_time)->find();
                    if ($type_lv1) {
                        $type_id = $type_lv1;
                    } else {
                        $type_id = '';
                    }
                } else {
                    $type_id = '';
                }
                break;
            case 3:
                $type_lv2=M()->table($table)->where('proTypeId='.$pid)->cache(true,$this->cache_time)->find();
                if($type_lv2) {
                    $type_lv1 = M()->table($table)->where('proTypeId=' . $type_lv2['proPid'])->cache(true,$this->cache_time)->find();
                    if($type_lv1){
                        $type_id=$type_lv1;
                    }else{
                        $type_id='';
                    }
                }else{
                    $type_id='';
                }
                break;
            default :$type_id='';break;
        }
        return $type_id;
    }

    //获取属性1级别菜单id
    public function get_style_lv1_id($type_id){
        $table='jd_protype';
        $sql = "select * from ".$table." where proTypeId =".$type_id." limit 1";
        $data = M()->query($sql);
        return $data[0];
    }


    //分类列表数据
    public function type_list($type_id,$id){
        $data=S('type_list_'.$type_id.'_'.$id);
        if(!$data) {
            $table = $this->products_styles_Model;
            $sql = "select styleId,styleName,child,radio from ".$table." where pid=0 and status=1 and display=1 and lv=1 ";
            if ($type_id && $type_id > 0) {
                $sql .= " and styleId in(". $type_id.")";
            }
            $sql.= " order by top desc,styleId asc ";
            $data = M()->query($sql);
            if ($data) {
                foreach ($data as &$one) {
                    if($one['child']==1) {
                        $sql_lv2 = "select * from ".$table." where pid=" . $one['styleId'] . " and status=1 and display=1 and lv=2 ";
                        if ($id > 0) {
                            $sql_lv2 .= " and proTypeId ='" . $id . "' ";
                        }
                        $sql_lv2.=' order by top desc,styleId asc ';
                        $child = M()->query($sql_lv2);
                        if ($child) {
                            foreach ($child as &$lv2){
                                if($lv2['child']==1){
                                    $sql_lv3 = "select * from ".$table." where pid=" . $lv2['styleId'] . " and status=1 and display=1 and lv=3 order by top desc,styleId asc";
                                    $lv3 = M()->query($sql_lv3);
                                    if($lv3){
                                        $one['has_lv3']=1;
                                        foreach ($lv3 as &$l){
                                            $l['photo']=$this->explain_pic($l['photo'],'/Public/Uploads/prodstyle/');
                                            $size = getimagesize($l['image']);
                                            if ($size) {
                                                $l['width'] = $size[0] . 'px';
                                                $l['height'] = $size[1] . 'px';
                                            }
                                        }
                                        $lv2['child_list']=$lv3;
                                    }else{
                                        $one['has_lv3']=0;
                                        $lv2['child_list']=array();
                                    }
                                }
                            }
                            $one['child_list'] = $child;
                        } else {
                            $one['child_list'] = array();
                        }
                    }
                }
                S('type_list_'.$type_id.'_'.$id,$data);
            } else {
                $data = array();
            }
        }
        return $data;
    }

    //产品列表数据
    public function product_list($lv,$tid,$cid,$id,$order_type,$key){
        //查找本分类 的 path
        //表名
        $table = 'jd_prods';
        //$table 表 别名
        $alias = 'p';
        //排序
        $order = $this->explain_order($order_type);
        //基础条件
        if ($id > 0) {
            $path = $this->path_info($lv, $id);
            if ($path) {
                $where = "p.proTypeId in(" . $path . ") and p.status=1";
            } else {
                $data['count'] = 0;
                return $data;
            }

        } else {
            $where = 'p.status=1';
        }
        //字段筛选
        $field = 'p.prodId,p.prodName,p.img,p.simimg,p.isNew,p.isHot';
        //按条件查找产品
        $join = '';
        //属性筛选
        if ($tid) {
            $join .= 'LEFT JOIN '.$this->prods_styles_Model.' AS ps ON ps.prodId=p.prodId';
            $where .= $this->handle_tid($tid);
        }
        //颜色筛选
        if ($cid) {
            $join .= ' LEFT JOIN '.$this->products.' AS pp ON pp.prodId=p.prodId';
            $where .= " and pp.colorId=" . $cid;
        }
        //关键字搜索
        if ($key) {
            $where .= ' and p.prodName like "%' . $key . '%" ';
        }
        $count = M()->table($table)->alias($alias)->field($field)->join($join)->where($where)->order($order)->cache(true,$this->cache)->count();
        $Page = $this->get_page($count, $this->page_num);
        $show = $Page->show();
        $list = M()->table($table)->alias($alias)->field($field)->join($join)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->group('p.prodId')->order($order)->cache(true,$this->cache)->select();
        //定义
        $data=array();
        if ($list) {
            $data['list'] = $this->handle_favourite($list);
            $data['page'] = $show;
            $data['count'] = $count;
        } else {
            $data['count'] = 0;
            $data['list'] = array();
            $data['page'] = $show;
        }
        return $data;
    }

    public function handle_tid($tid){
        $list="";
        if($tid) {
            foreach ($tid as &$one) {
                if($list) {
                    $list .= " or find_in_set('" . $one . "',ps.styleId)";
                }else{
                    $list.= " and (( find_in_set('" . $one . "',ps.styleId)";
                }
            }
        }
        if($list){
            $list.=" ) and ps.status=1 )";
        }
        return $list;
    }

    public function handle_favourite($list){
        $user=session('user');
        foreach($list as &$one) {
            $one=$this->explain_product_list($one);
            if($user['userId']>0){
                $favourite=M()->table('jd_users_favourite')->where('userId='.$user['userId'].' and prodId='.$one['prodId'].' and status=1')->cache(true,$this->cache)->find();
                if($favourite){
                    $one['favourite']=1;
                }else{
                    $one['favourite']=0;
                }
            }else{
                $one['favourite']=0;
            }
        }
        return $list;
    }

    public function path_info($lv,$id){
        if(($lv==1 or $lv==2 or $lv==3) && $id>0) {
            switch ($lv) {
                case 3:
                    $list = M()->table('jd_protype')->where('proTypeId=' . $id . ' and lv=3 and proLive=1')->cache(true, $this->cache_time)->find();
                    if ($list) {
                        return $list['proTypeId'];
                    } else {
                        return '';
                    }
                    break;
                case 2:
                    $path_info = '';
                    $list = M()->table('jd_protype')->where('proTypeId=' . $id . ' and lv=2 and proLive=1')->cache(true, $this->cache_time)->find();
                    if ($list) {
                        $path_info = $list['proTypeId'];
                        $child = M()->table('jd_protype')->where('proPid=' . $list['proTypeId'] . ' and lv=3 and proLive=1')->cache(true, $this->cache_time)->select();
                        if ($child) {
                            foreach ($child as &$one) {
                                $path_info .= ',' . $one['proTypeId'];
                            }
                        }
                    }
                    return $path_info;
                    break;
                case 1:
                    $path_info = '';
                    $lv1 = M()->table('jd_protype')->where('proTypeId=' . $id . ' and lv=1 and proLive=1')->cache(true, $this->cache_time)->find();
                    if ($lv1) {
                        $path_info = $lv1['proTypeId'];
                            $lv2 = M()->table('jd_protype')->where('proPid=' . $lv1['proTypeId'] . ' and lv=2 and proLive=1')->cache(true, $this->cache_time)->select();
                            if ($lv2) {
                                foreach ($lv2 as &$two) {
                                    $path_info .= ',' . $two['proTypeId'];
                                    $lv3 = M()->table('jd_protype')->where('proPid=' . $two['proTypeId'] . ' and lv=3 and proLive=1')->cache(true, $this->cache_time)->select();
                                    if ($lv3) {
                                        foreach ($lv3 as &$th) {
                                            $path_info .= ',' . $th['proTypeId'];
                                        }
                                    }
                                }
                            }
                        }
                    return $path_info;
                    break;
            }
        }else{
            return '';
        }
    }


    //路径
    public function lj($id){
        $field='proTypeId,proName,lv,proPid';
        $table='jd_protype';
        $lj=M()->table($table)->field($field)->where('proTypeId='.$id.' and proLive=1')->cache(true,$this->cache_time)->find();
        $data=array();
        if($lj) {
            switch($lj['lv']){
                case 3:
                    $data['lv3'] = $lj;
                    $lv2 = M()->table($table)->field($field)->where('proTypeId=' . $lj['proPid'] . ' and lv=2 and proLive=1')->cache(true, $this->cache_time)->find();
                    if ($lv2) {
                        $data['lv2']=$lv2;
                        $lv1= M()->table($table)->field($field)->where('proTypeId=' . $lv2['proPid'] . ' and lv=1 and proLive=1')->cache(true, $this->cache_time)->find();
                        if($lv1){
                            $data['lv1']=$lv1;
                        }else{
                            $data['lv1']=array();
                        }
                    }else{
                        $data['lv2']=array();
                        $data['lv1']=array();
                    }
                    break;
                case 2:
                    $data['lv3'] = array();
                    $data['lv2'] = $lj;
                    $lv1 =M()->table($table)->field($field)->where('proTypeId=' . $lj['proPid'] . ' and lv=1 and proLive=1')->cache(true, $this->cache_time)->find();
                    if($lv1){
                        $data['lv1']=$lv1;
                    }else{
                        $data['lv1']=array();
                    }
                    break;
                case 1:
                    $data['lv1']=$lj;
                    $data['lv2'] = array();
                    $data['lv3'] = array();
                    break;
            }
        }else{
            $data['lv1'] = array();
            $data['lv2'] = array();
            $data['lv3'] = array();
        }
        if($data){
            return $data;
        }else{
            return array();
        }
    }


    public function plj($prodId){
        $sql = "select * from ".$this->typeModel." where proTypeId=(select proTypeId from ".$this->goodsModel." where prodId=".$prodId." and status=1) and proLive=1 ";
        $list = M()->query($sql);
        if($list){
            return $this->lj($list[0]['proTypeId']);
        }else{
            return array();
        }
    }

    //产品详情
    public function get_product($prodId,$colorId,$sizeId){
        $data = S('product_detail_' . $prodId.'_'.$colorId.'_'.$sizeId);
        if (!$data) {
            $data = M()->table($this->goodsModel)->where('prodId=' . $prodId . ' and status=1')->cache(true, $this->cache_time)->find();
            if ($data) {
                $data['banner'] = $this->explain_pic($data['banner'], '/Public/Uploads/banner/');
                if ($data['banner'] == '' || !$data['banner']) {
                    $data['banner'] = __ROOT__ . '/Public/Uploads/banner/default.jpg';
                }
                $default = M()->table($this->products)->where('prodId=' . $prodId . ' and status=1 and isDefault=1')->order('prodId desc')->cache(true, $this->cache_time)->find();
                $data['size_list'] = $this->size_list($data, $sizeId, $default);
                $data['color_list'] = $this->color_list($data, $colorId, $default);
                $data['pic_list'] = $this->pic_list($data, $colorId, $default);
                $data['brand'] = $this->brand($data['brandId']);
                $data['authentication_list'] = $this->authentication_list($data['businessId']);
                //$data['imqq'] = $this->imqq();
                //$data['link'] = $this->detail_link($prodId);
                S('product_detail_' . $prodId.'_'.$colorId.'_'.$sizeId, $data);
            } else {
                $data = array();
            }
        }
        return $data;
    }

    //风格
    private function brand($brandId){
        $list=M()->table('jd_brands')->where('brandId='.$brandId.' and status=1')->cache(true,$this->cache_time)->find();
        if($list) {
            return $list['brandName'];
        }else{
            return '';
        }
    }

    //认证列表
    private function authentication_list($businessId){
        if($businessId){
            $list=M()->table('jd_authentication')->field('id,title,image')->where("id in (".$businessId.") and status=1")->order('top desc,id desc')->cache(true,$this->cache_time)->select();
            if($list){
                foreach($list as &$one){$one['image']=$this->explain_pic($one['image'],'/Public/Uploads/authentication/');}
            }else{
                $list=array();
            }
        }else{
            $list=array();
        }
        return $list;
    }

    //图片列表
    private function pic_list($data,$colorId,$default){
        $where='prodId='.$data['prodId'].' and status=1';
        if($colorId>0){
            $where.=' and colorId='.$colorId;
        }else{
            if($default) {$where .= ' and colorId=' . $default['colorId'];}
        }
        $pic_list=M()->table('jd_prodimg')->field('picId,image,colorId')->where($where)->order('picId desc')->cache(true,$this->cache_time)->select();
        if($pic_list) {
            foreach ($pic_list as &$one) {
                $one['image'] = $this->explain_pic($one['image'], '/Public/Uploads/');
            }
        }else{
            $pic_list=array();
        }
        return $pic_list;
    }

    //颜色列表
    private function color_list($data,$colorId,$default){
        $color_list=M()->table($this->products)->alias('p')->join('LEFT JOIN jd_colors AS c ON p.colorId=c.colorId')->where('p.prodId='.$data['prodId'].' and p.status=1 and c.status=1')->field('c.colorId,c.rgb,c.rgbimg,c.colorName')->group('p.colorId')->cache(true,$this->cache_time)->select();
        if($color_list){
            foreach($color_list as &$one){
                $one['rgbimg']=$this->explain_pic($one['rgbimg'], '/Public/Uploads/');
                if($colorId>0){
                    if($colorId==$one['colorId']){
                        $one['selected']=1;
                    }else{
                        $one['selected']=0;
                    }
                }else{
                    if($one['colorId']==$default['colorId']){
                        $one['selected']=1;
                    }else{
                        $one['selected']=0;
                    }
                }
            }
        }else{
            $color_list=array();
        }
        return $color_list;
    }

    //尺寸列表
    private function size_list($data,$sizeId,$default){
        $size_list=M()->table($this->products)->alias('p')->join('LEFT JOIN jd_sizes AS s ON p.sizeId=s.sizeId')->where('p.prodId='.$data['prodId'].' and p.status=1 and s.status=1')->field('s.sizeId,s.sizeName')->group('p.sizeId')->cache(true,$this->cache_time)->select();
        if($size_list){
            foreach($size_list as &$one){
                if($sizeId>0){
                    if($sizeId==$one['sizeId']){
                        $one['selected'] = 1;
                    } else {
                        $one['selected'] = 0;
                    }
                }else {
                    if ($one['sizeId'] == $default['sizeId']) {
                        $one['selected'] = 1;
                    } else {
                        $one['selected'] = 0;
                    }
                }
            }
        }else{
            $size_list=array();
        }
        return $size_list;
    }

    //关联产品列表
//    public function detail_link($id){
//        $sql = "select jp.*,jr.typeId from ".$this->linkModel." AS jr left join ".$this->goodsModel." AS jp ON jp.prodId=jr.prodId where jr.prodFromId=".$id." and jr.status=1 and jp.status=1 order by jr.top desc,jr.id desc limit 7";
//        $data=M()->query($sql);
//        if($data){
//            $link=array();
//            foreach ($data as &$one){
//                $one=$this->explain_product_list($one);
//                switch ($one['typeId']){
//                    case 1:$link['rpp'][]=$one;break;
//                    case 2:$link['sp'][]=$one;break;
//                    case 3:
//                        $one['images']=$this->explain_pic($one['images'],'Public/Uploads/');
//                        $link['rp'][]=$one;break;
//                }
//            }
//            return $link;
//        }else{
//            return array();
//        }
//    }

    //关联产品列表
    public function detail_link($prodId,$typeId,$lv1){
        $data = array();
        $data['rp'] = $this->rp($prodId, $typeId, $lv1);
        $data['sp'] = $this->sp($prodId, $typeId, $lv1);
        $data['rpp'] = $this->rpp($prodId,$typeId,$lv1);
        $data['styleId'] = '';
        $styleId = $this->prods_get_styleId($prodId, $lv1);
        foreach ($styleId as &$one) {
            $data['styleId'] .= $one['styleId'] . ',';
        }
        $data['lv1'] = $lv1;
        $data['proTypeId'] = $typeId;
        return $data;
    }

    //rp
    public function rp($prodId,$typeId,$lv1){
        $data = S('related_' . $prodId . '_' . $typeId . '_' . $lv1 . '_rp');
        if (!$data) {
            $data = $this->get_detail_link($prodId,'rp');
            if(!$data) {
                $styleId = $this->prods_get_styleId($prodId, $lv1);
                $data = $this->get_rp_data($prodId, $typeId, $styleId, $lv1);
            }
            if ($data) {
                S('related_' . $prodId . '_' . $typeId . '_' . $lv1 . '_rp', $data);
            }
        }
        return $data;
    }

    //sp
    public function sp($prodId,$typeId,$lv1){
        $data=S('related_' . $prodId . '_' . $typeId . '_' . $lv1.'_sp');
        if(!$data) {
                $data = $this->get_detail_link($prodId,'sp');
                if(!$data) {
                    $data = $this->get_rp_data($prodId, $typeId, null, $lv1);
                }
            if($data){
                S('related_' . $prodId . '_' . $typeId . '_' . $lv1.'_sp',$data);
            }
        }
        return $data;
    }

    //rpp
    public function rpp($prodId, $typeId, $lv1){
        $data=S('related_' . $prodId . '_' . $typeId . '_' . $lv1.'_rrp');
        if(!$data) {
            $data = $this->get_detail_link($prodId, 'rrp');
            if($data){
                S('related_' . $prodId . '_' . $typeId . '_' . $lv1.'_rrp',$data);
            }
        }
        return $data;
    }

    public function value_get_key($value){
        switch ($value){
            case 'rp': $key = 3; break;
            case 'sp': $key = 2; break;
            case 'rrp': $key = 1; break;
            default: $key = ''; break;
        }
        return $key;
    }

    //通过关联数据表获取管理产品列表
    private function get_detail_link($prodId,$value){
        $key = $this->value_get_key($value);
        if($key) {
            if ($key==1) {
                $sql = "select jr.* from " . $this->linkModel . " AS jr  where jr.prodFromId=" . $prodId . " and jr.status=1 and jr.typeId=" . $key . " order by jr.top desc,jr.id desc limit 7";
            }else{
                $sql = "select jr.*,jp.* from " . $this->linkModel . " AS jr left join ".$this->goodsModel." AS jp ON jp.prodId=jr.prodId  where jr.prodFromId=" . $prodId . " and jr.status=1 and jp.status=1 and jr.typeId=" . $key . " order by jr.top desc,jr.id desc limit 7";
            }
            $data = M()->query($sql);
            if ($data) {
                foreach ($data as &$one) {
                    if($one['img']){
                        $one = $this->explain_product_list($one);
                    }else {
                        $one['img'] = $this->explain_pic($one['image'], 'Public/Uploads/Related-Product/');
                        $one['prodName'] = $one['title'];
                    }
                }
            } else {
                $data = array();
            }
        }else{
            $data = array();
        }
        return $data;
    }

    //
    private function get_rp_data($prodId,$typeId,$styleId=null,$lv1){
        $join="";
        $where="";
        if($styleId){
            $join = " Left join ".$this->prods_styles_Model." AS ps ON p.prodId=ps.prodId";
            $where.=$this->create_where($styleId);
        }else{
            $TK = $this->check_TK($lv1);
            if(!$TK){
                $where.=" and p.proTypeId=".$typeId;
            }else{
                $styleId = $this->sp_prods_get_styleId($prodId,$lv1);
                if($styleId){
                    $join = " Left join ".$this->prods_styles_Model." AS ps ON p.prodId=ps.prodId";
                    $where.=$this->create_where($styleId);
                }
            }
        }
        //$sql = "select p.* from ".$this->goodsModel." AS p ".$join." where p.prodId<>".$prodId." and p.proTypeId=".$typeId." and p.status=1 ".$where." order by p.isHot desc,rand() desc limit 7";
        $sql = "select p.* from ".$this->goodsModel." AS p ".$join." where p.prodId<>".$prodId." and p.status=1 ".$where." order by p.isHot desc,rand() desc limit 7";
        $data = M()->query($sql);
        //var_dump(M()->getLastSql());
        if($data){
            foreach ($data as &$one){
                $one = $this->explain_product_list($one);
            }
        }else{
            $data = array();
        }
        return $data;
    }

    private function create_where($styleId){
        $where='';
        if($styleId) {
            foreach ($styleId as &$one) {
                if ($where) {
                    $where .= " and find_in_set(" . $one['styleId'] . ",ps.styleId) ";
                } else {
                    $where .= " and (( find_in_set(" . $one['styleId'] . ",ps.styleId) ";
                }
            }
            if ($where) {
                $where .= " ) and ps.status=1 ) ";
            }
        }
        return $where;
    }


    private function check_TK($lv1){
        if($lv1==1 or $lv1==2){
            return true;
        }else{
            return false;
        }
    }

    //
    private function prods_get_styleId($prodId,$lv1){
        $sql = "select styleId from ".$this->prods_styles_Model." where prodId =".$prodId." and status=1 limit 1";
        $data = M()->query($sql);
        if($data){
            return $this->find_styleId($data[0]['styleId'],$lv1);
        }else{
            return array();
        }
    }

    private function find_styleId($styleId,$lv1){
        $id = explode(',',$styleId);
        $sid = '';
        foreach ($id as &$one){
            if($sid){
                $sid.=',"'.$one.'"';
            }else {
                $sid.='"'.$one.'"';
            }
        }
                $proType = " and proTypeId=" . $lv1;
        switch ($lv1) {
            case 3:case 4:case 5:case 6:$proType .= " and pid=5";break;
            case 2:$proType .= " and pid=(SELECT styleId FROM " . $this->products_styles_Model . " where pid=1 and status=1)";break;
            case 1:$proType .= " and pid=(SELECT styleId FROM " . $this->products_styles_Model . " where pid=325 and status=1)";break;
            default:$proType = "";break;
        }
        $sql = "select styleId from ".$this->products_styles_Model." where styleId in(".$sid.") and status=1 ".$proType;
        $data = M()->query($sql);
        if(!$data){
            $data = array();
        }
        return $data;
    }

    private function sp_prods_get_styleId($prodId,$lv1){
        $sql = "select styleId from ".$this->prods_styles_Model." where prodId =".$prodId." and status=1 limit 1";
        $data = M()->query($sql);
        if($data){
            return $this->sp_find_styleId($data[0]['styleId'],$lv1);
        }else{
            return array();
        }

    }

    private function sp_find_styleId($styleId,$lv1){
        $id = explode(',',$styleId);
        $sid = '';
        foreach ($id as &$one){
            if($sid){
                $sid.=',"'.$one.'"';
            }else {
                $sid.='"'.$one.'"';
            }
        }
        $proType = " and proTypeId=" . $lv1." and pid=2";
        $sql = "select styleId from ".$this->products_styles_Model." where styleId in(".$sid.") and status=1 ".$proType;
        $data = M()->query($sql);
        if(!$data){
            $data = array();
        }
        return $data;
    }


    //解释产品
    private function explain_product_list($one){
        $one['img'] = $this->explain_pic($one['img'], '/Public/Uploads/');
        $one['simimg'] = $this->explain_pic($one['simimg'], '/Public/Uploads/');
        $one['create_time']=$this->explain_time($one['create_time']);
        $one['updata_time']=$this->explain_time($one['updata_time']);
        return $one;
    }

    //检测商品id
    public function check_prod($prodId){
        //return M()->table($this->goodsModel)->where('prodId='.$prodId)->find();
        $sql = "select * from ".$this->goodsModel." where prodId=".$prodId." limit 1";
        return M()->query($sql);
    }

    public function following_get_product_list($table, $alias, $where, $join, $field, $order, $num){
        $count = M()->table($table)->alias($alias)->where($where)->join($join)->field($field)->where($where)->order($order)->cache(false)->count();
        $Page = $this->get_page($count, $num);
        $show = $Page->show();
        $data = M()->table($table)->alias($alias)->join($join)->field($field)->where($where)->order($order)->limit($Page->firstRow . ',' . $Page->listRows)->cache(false)->select();
        if (!$data) $data = array();
        if ($data) {
            foreach ($data as &$one) {
                $one = $this->explain_product_list($one);
            }
        }
        $list['data'] = $data;
        $list['page'] = $show;
        return $list;
    }
//end
}