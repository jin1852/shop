<?php

namespace Shop\Model;
class SiteMapModel extends BaseModel{
    public $protype_model='jd_protype';
    public $brand_model='jd_brands';
    public $protype_style_modle='jd_products_style';


    public function sitemap_list(){
        $data = S('site_map_list');
        if(!$data) $data = $this->product();
        if($data) S('site_map_list',$data);
        return $data;
    }


    //product
    public function product(){
        $product = array();
        $product['title'] = 'PRODUCT';
        $link=__APP__.'/Product/product_list/order_type/1';
        $product['link'] = $link;
        $sql_lv1 = $this->create_sql(0,1);
        $product['lv1'] = M()->query($sql_lv1);
        if($product['lv1']) {
            $product['count'] = count($product['lv1']);
            $num = 0;
            foreach ($product['lv1'] as &$p1) {
                $num++;
                $p1['num'] = $num;
                $p1['link'] = $link . '/id/' . $p1['proTypeId'] . '/pid/' . $p1['proPid'] . '/lv/1';
                $sql_lv2 = $this->create_sql($p1['proTypeId'],2);
                $lv2 = M()->query($sql_lv2);
                if ($lv2) {
                    $num=0;
                    foreach ($lv2 as &$p2) {
                        $num++;
                        $p2['num']=$num;
                        $p2['link'] = $link . '/id/' . $p2['proTypeId'] . '/pid/' . $p2['proPid'] . '/lv/2';
                        if($p2['is_type']==1){
                            switch ($p2['proTypeId']){
                                case 15:
                                    //tools&Gadgets
                                    $sql_lv3 = $this->expalin_sql(7);
                                    break;
                                case 99:
                                    //knife
                                    $sql_lv3 = $this->expalin_sql(287);
                                    break;
                            }
                        }else {
                            $sql_lv3 = $this->create_sql($p2['proTypeId'],3);
                        }
                        $lv3 = M()->query($sql_lv3);
                        if ($lv3) {
                            foreach ($lv3 as &$p3) {
                                $num++;
                                $p3['num']=$num;
                                if($p3['radio']==1){
                                    $p3['proPid']=$p2['proTypeId'];
                                    $p3['link']=$link.'/kid/'.$p3['proTypeId'] . '/pid/' . $p3['proPid'] . '/lv/3';
                                }else {
                                    $p3['link'] = $link . '/id/' . $p3['proTypeId'] . '/pid/' . $p3['proPid'] . '/lv/3';
                                }
                            }
                        } else {
                            $lv3 = array();
                        }
                        $p2['lv3'] = $lv3;
                    }
                } else {
                    $lv2 = array();
                }
                $lv2['num']=$num;
                $p1['lv2'] = $lv2;
            }
        }else{
            $product['lv1']=array();
        }
        return $product;
    }

    private function expalin_sql($pid){
        return "select styleId AS proTypeId,styleName AS proName,radio  from " . $this->protype_style_modle . " where pid=".$pid." and status=1 and lv=3 order by top desc,proTypeId asc";
    }

    private function create_sql($pid,$lv){
        return "select * from " . $this->protype_model . " where proPid=".$pid." and proLive=1 and lv=".$lv." order by top desc,proTypeId asc";
    }
//end
}