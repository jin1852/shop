<?php

namespace Shop\Model;
class CartModel extends BaseModel{
    //配置数据表
    public $Cart = 'jd_user_shop_cart';  //购物车表
    public $productSql='jd_products';
    public $prodsSql='jd_prods';
    public $prods_styles_model='jd_prods_styles';
    public $type_model="jd_products_style";

    //添加产品到购物车
    public function add_to_cart($user_id,$prodId,$prodName,$size,$sizeId,$color,$colorId){
        $sql = 'select pr.*,p.prodInfo,p.img,ps.styleId from ' . $this->productSql . ' AS pr left join ' . $this->prodsSql . ' AS p ON pr.prodId=p.prodId left join ' . $this->prods_styles_model . ' AS ps ON ps.prodId=pr.prodId where pr.prodId=' . $prodId . ' and pr.status=1 ';
        if($sizeId or $colorId) {
            if ($sizeId > 0) {
                $sql .= ' and pr.sizeId=' . $sizeId;
            }
            if ($colorId > 0) {
                $sql .= ' and pr.colorId=' . $colorId;
            }
        }else{
            $sql.=' and pr.isDefault=1';
        }
        $product = M()->query($sql);
        if($product[0]) {
            $list = M()->table($this->Cart)->where("userId=" . $user_id . " and productId=".$product[0]['productId']." and prodName='" . $prodName . "' and colorId=" . $product[0]['colorId']." and sizeId=".$product[0]['sizeId']." and is_order=0")->cache(false)->find();
            if ($list && $list['prodName']==$prodName) {
                if ($list['status'] == 1) {
                    $list['number'] = intval(intval($list['number'])+1);
                } else {
                    $list['status'] = 1;
                    $list['number'] = 1;
                }
                $list['update_time']=time();
                $result = M()->table($this->Cart)->save($list);
            } else {
                $data['userId'] = $user_id;
                $data['productNO']=$product[0]['productNO'];
                $data['productId'] = $product[0]['productId'];
                $data['prodName'] = $prodName;
                $data['img']=$product[0]['img'];
                $data['type']=$this->get_type($product[0]['styleId']);
                $data['content']=$product[0]['prodInfo'];
                $data['colorId'] = $product[0]['colorId'];
                $rgb = $this->get_color($product[0]['colorId']);
                $data['color'] = $rgb['rgb'];
                $data['rgbimg'] = $rgb['rgbimg'];
                $data['sizeId'] = $product[0]['sizeId'];
                $data['size'] = $this->get_size($product[0]['sizeId']);
                $data['number'] = 1;
                $data['price']=$product[0]['price'];
                $data['remark'] = '';
                $data['status'] = 1;
                $data['is_order'] = 0;
                $data['create_time']=time();
                $result = M()->table($this->Cart)->add($data);
            }
        }else{
            $result=false;
        }
        return $result;
    }

    public function get_type($styleId){
        $sql ="select * from ".$this->type_model." where styleId in(".$styleId.")";
        $data = M()->query($sql);
        if($data) {
            $style='';
            foreach ($data AS &$one){
                if($style){
                    $style.=','.$one['styleName'];
                }else{
                    $style=$one['styleName'];
                }
            }
            return $style;
        }else{
            return null;
        }
    }

    private function get_size($sizeId){
        $list = S('cart_size_' . $sizeId);
        if (!$list) {
            $list = M()->table('jd_sizes')->where('sizeId=' . $sizeId)->find();
        }
        if ($list) {
            S('cart_size_' . $sizeId, $list);
            return $list['sizeName'];
        } else {
            return '';
        }
    }

    private function get_color($colorId){
        $list = S('cart_color_' . $colorId);
        if (!$list) {
            $list = M()->table('jd_colors')->where('colorId=' . $colorId)->find();
        }
        if ($list) {
            S('cart_color_' . $colorId, $list);
            return $list;
        } else {
            return '';
        }
    }

    //删除购物车种的产品
    public function del_form_cart($id){
        $list=M()->table($this->Cart)->where('id='.$id.' and is_order=0')->find();
        if($list){
            if($list['num']>=1) {
                if ($list['num'] == 1) {
                    $list['status'] = 0;
                    $list['num'] = 0;
                } else {
                    $list['num']-=1;
                }
            }else{
                $list['num']=0;
                $list['status']=0;
            }
            $result=M()->table($this->Cart)->save($list);
        }else{
            $result=false;
        }
        return $result;
    }
//end
  }