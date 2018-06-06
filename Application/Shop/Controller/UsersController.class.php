<?php
namespace Shop\Controller;
use Shop\Model\CartModel;
use Shop\Model\FavouriteModel;
use Shop\Model\ProductsModel;
use Shop\Model\UserModel;
use Think\Controller;
use Shop\Controller\EmailController;
class UsersController extends BaseController {

    public function favourite(){
        $user=session('user');
        $prodId=I('post.prodId');
        if($user['userId']>0){
            if($prodId>0) {
                $User=new UserModel();
                $account = $User->check_user($user['userId']);
                if ($account) {
                    $Prod=new ProductsModel();
                    $prod_=$Prod->check_prod($prodId);
                    $prod =$prod_[0];
                    if($prod){
                        if($prod['status']==1 or $prod['status']=='1'){
                            $Favourite=new FavouriteModel();
                            $result=$Favourite->favourite($account['userId'],$prod['prodId']);
                            if($result) {
                                $this->list_json_result(1,'success','');
                            }else {
                                $this->list_json_result(-1,'fail', '');
                            }
                        }else{
                            $this->list_json_result(-6,'product status error','');
                        }
                    }else{
                        $this->list_json_result(-8,'product error','');
                    }
                } else {
                    $this->list_json_result(-8,'user error','');
                }
            }else{
                $this->list_json_result(-9,'prodId error','');
            }
        }else{
            $this->list_json_result(-10,'Please login','');
        }
    }

    public function series_favourite(){
        $user=session('user');
        $series_Id=I('post.series_Id');
        if($user['userId']>0){
            if($series_Id>0) {
                $User=new UserModel();
                $account = $User->check_user($user['userId']);
                if ($account) {
                    $Favourite = new FavouriteModel();
                    $result = $Favourite->series_favourite($account['userId'], $series_Id);
                    if ($result) {
                        $this->list_json_result(1, 'success', '');
                    } else {
                        $this->list_json_result(-1, 'fail', '');
                    }
                } else {
                    $this->list_json_result(-8,'user error','');
                }
            }else{
                $this->list_json_result(-9,'series Id error','');
            }
        }else{
            $this->list_json_result(-10,'Please login','');
        }
    }

    public function remove_series_favourite(){
        $user=session('user');
        $series_Id=I('post.series_Id');
        if($user['userId']>0){
            if($series_Id>0) {
                $User=new UserModel();
                $account = $User->check_user($user['userId']);
                if ($account) {
                    $Favourite = new FavouriteModel();
                    $result = $Favourite->remove_series_favourite_arr($account['userId'], $series_Id);
                    if ($result) {
                        $this->list_json_result(1, 'success', '');
                    } else {
                        $this->list_json_result(-1, 'fail', '');
                    }
                } else {
                    $this->list_json_result(-8,'user error','');
                }
            }else{
                $this->list_json_result(-9,'series Id error','');
            }
        }else{
            $this->list_json_result(-10,'Please login','');
        }
    }


    //添加至购物车
    public function add_to_cart(){
        $user=session('user');
        if($user){
            $prodId=I("post.prodId");
            $prodName=I('post.prodName');
            $sizeId=I("post.sizeId");
            $size=I('post.size');
            $color=I('post.color');
            $colorId=I("post.colorId");
            if($prodName && $prodId>0){
                $user_id=$user['userId'];
                $Cart=new CartModel();
                $result=$Cart->add_to_cart($user_id,$prodId,$prodName,$size,$sizeId,$color,$colorId);
                if($result){
                    //success
                    $this->list_json_result(1,'add success','');
                }else{
                    //fail
                    $this->list_json_result(-1,'add fail','');
                }
            }else{
                //error code
                $this->list_json_result(-2,'please select product','');
            }
        }else{
            //error code
            $this->list_json_result(-20,'please login','');
        }
    }

    //购物车中删除
    public function del_form_cart(){
        $user = session('user');
        if ($user) {
            $prodId = I('get.prodId');
            if ($prodId > 0) {
                $user_id = $user['id'];
                $Cart = new CartModel();
                $result = $Cart->del_form_cart($user_id, $prodId);
                if ($result) {
                    //success
                    $this->list_json_result(1, 'del success', '');
                } else {
                    //fail
                    $this->list_json_result(-1, 'del fail', '');
                }
            } else {
                //error code
                $this->list_json_result(-2, 'please select product', '');
            }
        } else {
            //error code
            $this->list_json_result(-20, 'please login', '');
        }
    }

    public function cart_add(){


    }

    public function cart_del(){

    }

    public function del_history(){
        $user = session('user');
        if($user){
            $prodId = I('post.prodId');
            if ($prodId > 0) {
                $list = M('users_view_history')->where('userId='.$user['userId'].' and prodId='.$prodId)->find();
                if($list) {
                    $result = M('users_view_history')->where('id='.$list["id"])->delete();
                    if ($result) {
                        $this->list_json_result(1, 'del success', '');
                    } else {
                        $this->list_json_result(-1, 'del fail', '');
                    }
                }else{
                    $this->list_json_result(-3, 'no this product', '');
                }
            } else {
                $this->list_json_result(-2, 'please select product', '');
            }
        }else{
            $this->list_json_result(-20, 'please login', '');
        }
    }

    public function add_to_activities(){
        $user = session('user');
        if($user){
            $id = I('post.id');
            if ($id > 0) {
                $a = M('article')->where('id=%d and is_show=%d', $id, 1)->find();
                if($a) {
                    $list = M('users_activities')->where('userId=%d and aid =%d', $user['userId'], $id)->find();
                    if ($list) {
                        $this->list_json_result(-4, 'already add to activities', '');
                    } else {
                        $data['userId'] = $user['userId'];
                        $data['aid'] = $id;
                        $data['time'] = time();
                        $data['status'] = 1;
                        $result =  M('users_activities')->add($data);
                        if($result){
                            $this->list_json_result(1, 'add success', '');
                        }else{
                            $this->list_json_result(-1, 'add fail', '');
                        }
                    }
                }else{
                    $this->list_json_result(-3, 'no this activities', '');
                }
            } else {
                $this->list_json_result(-2, 'please select product', '');
            }
        }else{
            $this->list_json_result(-20, 'please login', '');
        }
    }
}