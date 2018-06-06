<?php

namespace Shop\Model;
class NewsletterModel extends BaseModel{
    public $table='jd_newsletter';

    public function newsletter($email){
        $data = M()->table($this->table)->where("email = '" . $email . "'")->find();
        if ($data) {
            if ($data['status'] == 1) {
                $result = true;
            } else {
                $result = $this->change_email_status($data);
            }
        } else {
            $result = $this->add_email($email);
        }
        return $result;
    }

    public function add_email($email){
        $data['email'] = $email;
        $data['status'] = 1;
        $data['time'] = time();
        $res = M()->table($this->table)->add($data);
        return $this->explain_result($res);
    }

    public function change_email_status($data){
        $data['time'] = time();
        $data['status'] = 1;
        $res = M()->table($this->table)->where("id=" . $data['id'])->save($data);
        return $this->explain_result($res);
    }

    private function explain_result($res){
        if($res){
           return true;
        }else{
           return false;
        }
    }
//end
  }