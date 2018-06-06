<?php

namespace Shop\Model;
class AddressModel extends BaseModel{
    //配置数据表
    public $Address = 'jd_address';

    public $express = array(1 => 'Fedex', 2 => 'UPS', 3 => 'TNT', 4 => 'DHL', 5 => 'SF-EXPRESS', 6 => 'Other');

    public $service = array(1 => 'Intl.Priority', 2 => 'Intl.Economy', 3 => 'Other');

    //获取banner数据
    public function address_list($uid){
        $sql = "select * from ".$this->Address." where status=1  and userId = {$uid} order by addressId desc";
        $list=M()->query($sql);
        if($list){
            $list = $this->explain_address($list);
        } else{
            $list=array();
        }
        return $list;
    }

    //解释地址
    public function explain_address($address){
        if($address) {
            foreach($address AS &$one){
                if ($one['Express'] >= 1 && $one['Express'] <= 5) {
                    $one['Express_name'] = $this->express[$one['Express']];
                } else {
                    $one['Express'] = 6;
                    $one['Express_name'] = $one['Expressother'];

                }
                if ($one['Service'] >= 1 && $one['Service'] <= 2) {
                    $one['Service_name'] = $this->service[$one['Service']];
                } else {
                    $one['Service'] = 3;
                    $one['Service_name'] = $one['Serviceother'];
                }
            }
        }
        return $address;
    }

    public function getExpress(){
        return $this->express;
    }

    public function getService(){
        return $this->service;
    }

    public function get_one($id,$uid){
        $sql = "select * from ".$this->Address." where addressId={$id}  and userId = {$uid} ";
        $list=M()->query($sql);
        if(!$list){
            $list=array();
        }
        return $list[0];
    }

    public function del($id,$uid){
        $list = $this->get_one($id,$uid);
        if($list && $list['status']==1){
            $list['status'] = 0;
            $list['updata_time'] = time();
            return M()->table($this->Address)->where('addressId=%d',$id)->save($list);
        }else{
            return false;
        }

    }


    public function set($id,$uid){
        $list = $this->get_one($id,$uid);
        if($list && $list['status']==1){

            return M()->table($this->Address)->where('addressId=%d',$id)->save($list);
        }else{
            return false;
        }
    }

    public function set_default($id,$uid){
        $data = M()->table($this->Address)->where("userId=%d and status=%d and isDefault=%d", $uid, 1, 1)->find();
        if($data) {
            if ($id == $data['addressId']) {
                return true;
            } else {
                $data['isDefault'] = 0;
                $data['updata_time'] = time();
                $result = M()->table($this->Address)->where('addressId=%d', $data['addressId'])->save($data);
                if ($result) {
                    $list = $this->get_one($id, $uid);
                    if ($list && $list['status'] == 1) {
                        $list['isDefault'] = 1;
                        return M()->table($this->Address)->where('addressId=%d', $id)->save($list);
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }else{
            return false;
        }
    }


//end
  }