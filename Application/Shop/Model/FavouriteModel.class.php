<?php

namespace Shop\Model;
class FavouriteModel extends BaseModel{
    public $table='jd_users_favourite';
    public $table_2='jd_users_favourite_series';

    public function favourite($userId,$prodId){
        $favourite=M()->table($this->table)->where('userId='.$userId.' and prodId='.$prodId)->find();
        if($favourite){
            $favourite['time'] = time();
            if($favourite['status']==1){
                $favourite['status']=0;
            }else{
                $favourite['status']=1;
            }
            $result=M()->table($this->table)->save($favourite);
        }else {
            $data['userId'] = $userId;
            $data['prodId'] = $prodId;
            $data['status'] = 1;
            $data['time'] = time();
            $result = M()->table($this->table)->add($data);
        }
        return $result;
    }

    public function series_favourite($userId,$series_Id){
        $favourite = M()->table($this->table_2)->where('userId=' . $userId . ' and series_Id=' . $series_Id)->find();
        if ($favourite) {
            $favourite['time'] = time();
            if ($favourite['status'] == 1) {
                $favourite['status'] = 0;
            } else {
                $favourite['status'] = 1;
            }
            $result = M()->table($this->table_2)->save($favourite);
        } else {
            $data['userId'] = $userId;
            $data['series_Id'] = $series_Id;
            $data['status'] = 1;
            $data['time'] = time();
            $result = M()->table($this->table_2)->add($data);
        }
        return $result;
    }


    public function remove_series_favourite_arr($userId,$sid_Arr){
        $favourite = M()->table($this->table_2)->where(array('series_Id'=>array('IN',$sid_Arr),'userId'=>$userId))->select();
        if ($favourite) {
            foreach ($favourite AS &$one) {
                $one['status'] = 0;
                $result = M()->table($this->table_2)->save($one);
                if(!$result) break;
            }
        } else {
            $result = false;
        }
        return $result;
    }
//end
  }