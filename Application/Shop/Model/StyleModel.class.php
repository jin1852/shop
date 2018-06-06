<?php

namespace Shop\Model;
class StyleModel extends BaseModel{

    public $goodsModel = 'jd_prods';
    public $brandModel = 'jd_brands';
    public $typeModel = 'jd_products_style';

    //获取产品列表
    public function get_product($styleId,$typeid,$search_key,$order_type){
        $alias = "p";
        $join = "";
        $where = " p.status=1 ";
        //排序
        $order = $this->explain_order($order_type);
        if ($styleId > 0) {
            $join .= " LEFT JOIN " . $this->brandModel . " AS b ON p.brandId=b.brandId ";
            $where .= " and b.status=1 and p.brandId=" . $styleId;
        }
        if ($typeid) {
            $tid = $this->get_tid($typeid);
            if ($tid) {
                $join .= ' LEFT JOIN jd_prods_styles AS ps ON ps.prodId=p.prodId';
                $where .= $tid;
            }
        }
        if ($search_key) {
            $where .= " and ( p.prodName like '%" . $search_key . "%' or p.keyword like '%" . $search_key . "%')";
        }
        $count = M()->table($this->goodsModel)->alias($alias)->join($join)->where($where)->order($order)->cache(true,$this->cache_time)->count();
        $Page = $this->get_page($count, $this->page_num);
        $show = $Page->show();
        $list = M()->table($this->goodsModel)->alias($alias)->join($join)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order($order)->cache(true,$this->cache_time)->select();
        $data = array();
        if ($list) {
            $Product = new ProductsModel();
            $data['list'] = $Product->handle_favourite($list);
            $data['page'] = $show;
            $data['count'] = $count;
        } else {
            $data['count'] = 0;
            $data['list'] = array();
            $data['page'] = $show;
        }
        return $data;
    }

    private function get_tid($typeid){
        $sql="SELECT * FROM ".$this->typeModel." WHERE styleName=(SELECT styleName FROM ".$this->typeModel." where styleId=".$typeid.")";
        $list=M()->query($sql);
        $tid='';
        if($list){
            foreach ($list as &$one){
                if($tid) {
                    $tid .= " or find_in_set(" . $one['styleId'] . ",ps.styleId)";
                }else{
                    $tid.=" and ( find_in_set(" . $one['styleId'] . ",ps.styleId)";
                }
            }
        }
        if($tid){
            $tid.=" )";
        }
        return $tid;
    }

    public function brand_list($styleId){
        $data = S('brand_list_'.$styleId);
        if(!$data){
            $data['title'] = 'Style';
            $data['select'] = 0;
            $link = __APP__.'/Style/product_list';
            $sql = "SELECT * FROM ".$this->brandModel." WHERE status=1 order by brandId asc";
            $list = M()->query($sql);
            if($list){
                foreach ($list as &$one) {
                    $one['link'] = $link . '/brandId/' . $one['styleId'];
                    if ($one['brandId'] == $styleId) {
                        $one['selected'] = 1;
                        $data['selected'] = 1;
                    } else {
                        $one['selected'] = 0;
                        if ($data['selected'] != 1) {
                            $data['selected'] = 0;
                        }
                    }
                }
                $data['child']=$list;
                S('brand_list_'.$styleId,$data);
            }else{
                $data['child'] = array();
            }
        }
        return $data;
    }

    public function type_list($typeid){
        $data = S('brand_type_list_'.$typeid);
        if(!$data){
            $data['title'] = 'Collections';
            $data['select'] = 0;
            $link = __APP__.'/Style/product_list';
            $sql = "SELECT * FROM ".$this->typeModel." WHERE pid=5 and lv=2 and status=1 group by styleName order by top desc,styleId asc";
            $list = M()->query($sql);
            if($list){
                $sql1="SELECT * FROM ".$this->typeModel." WHERE styleId=".$typeid." and status=1";
                $selected=M()->query($sql1);
                foreach ($list as &$one){
                    $one['link']= $link.'/typeName/'.$one['styleName'];
                    if($selected && ($one['styleName']==$selected[0]['styleName'])){
                        $one['selected'] = 1;
                        $data['selected'] = 1;
                    }else{
                        $one['selected'] = 0;
                        if ($data['selected'] != 1) {
                            $data['selected'] = 0;
                        }
                    }
                }
                $data['child'] = $list;
                S('brand_type_list_'.$typeid,$data);
            }else{
                $data['child'] = array();
            }
        }
        return $data;
    }

//end
  }