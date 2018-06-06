<?php
namespace Shop\Model;
use Shop\Controller\BaseController;
use Shop\Model\ZtModel;
use Think\Controller;
class NavModel extends BaseModel{
    public $protype_model='jd_protype';
    public $brand_model='jd_brands';
    public $protype_style_modle='jd_products_style';

	//product
	public function product(){
		$product = array();
		$product['title'] = 'PRODUCT';
        $link=__APP__.'/Product/product_list/order_type/1';
		$product['link'] = $link;
        $sql_lv1 = $this->create_sql(0,1);
        $product['lv1'] = M()->query($sql_lv1);
		if($product['lv1']) {
			foreach ($product['lv1'] as &$p1) {
                $p1['image'] = $this->explain_pic($p1['image'], '/Public/Uploads/protype/');
                $size = getimagesize($p1['image']);
                if ($size) {
                    $p1['width'] = $size[0] . 'px';
                    $p1['height'] = $size[1] . 'px';
                }
				$p1['link'] = $link . '/id/' . $p1['proTypeId'] . '/pid/' . $p1['proPid'] . '/lv/1';
                $p1['background'] = $this->product_bg($p1['proTypeId']);
                $size = getimagesize($p1['background']);
                if ($size) {
                    $p1['background_width'] = $size[0] . 'px';
                    $p1['background_height'] = $size[1] . 'px';
                }
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
		S('Nav_product',$product);
		return $product;
	}

	//产品导航条下拉底图
	private function product_bg($prodTypeId){
	    $data = S('product_bg_'.$prodTypeId);
        if(!$data) {
            $name = "Product List";
            $data = M()->query("select * from jd_zt_conf where name = '" . $name . "' and type=0 and prodTypeId=".$prodTypeId." and status=1 limit 1");
        }
        if($data){
            $bg = $this->explain_pic($data[0]['view_img'],'Public/Uploads/productright/');
            S('product_bg_'.$prodTypeId,$bg);
            return $bg;
        }else{
            return '';
        }
    }


	private function expalin_sql($pid){
        return "select styleId AS proTypeId,styleName AS proName,radio  from " . $this->protype_style_modle . " where pid=".$pid." and status=1 and lv=3 order by top desc,proTypeId asc";
    }

    private function create_sql($pid,$lv){
        return "select * from " . $this->protype_model . " where proPid=".$pid." and proLive=1 and lv=".$lv." order by top desc,proTypeId asc";
    }

	//style
	public function style(){
		//系列分类
		$style=array();
		$link=__APP__.'/Style/product_list/order_type/1';
		$style['title']='STYLE';
		$style['link']=$link;
        $sql="select * from ".$this->brand_model." where status=1 limit 10";
        $style['lv1']=M()->query($sql);
        $style['bg'] = $this->style_bg();
        if($style['lv1']) {
            foreach ($style['lv1'] as &$s1) {
                $s1['link'] = $link . '/brandId/' . $s1['brandId'];
            }
            S('Nav_style',$style);
        }else{
            $style['lv1'] = array();
        }
		return $style;
	}

    public function series_bg(){
        $S_name = 'series_bg';
        $name = "Series";
        $data = $this->select_bg($name,$S_name);
        return $data;
    }

	public function style_bg(){
	    $S_name = 'style_bg';
        $name = "Style";
        $data = $this->select_bg($name,$S_name);
        return $data;
    }

    public function select_bg($name,$S_name){
        $data = S($S_name);
        if(!$data) {
            $data = M()->query("select * from jd_zt_conf where name = '" . $name . "' and type=0 and status=1 limit 1");
        }
        if($data){
            $bg = $this->explain_pic($data[0]['view_img'],'Public/Uploads/productright/');
            S($S_name,$bg);
            return $bg;
        }else{
            return '';
        }
    }
    //end
}