<?php

class Application_Model_DbTable_MailAutoBinded extends Application_Model_DbTable_Abstract
{

    protected $_name = 'mail_auto_binded';
    protected $_dbname='base';


    public function addNewBind($res){
        $data['email']=$res->email;
        $data['user_id']=$res->user_id;
        $data['sub_user_id']=$res->sub_user_id;
        $this->insert($data);
    }
    
    public function getInfoByMail($email){
        //取得状态为有效的邮箱绑定信息
        $res=$this->fetchRow("email='".$email."' and status='1'");
        return $res;
    }
    public function getExistInfoByMail($email){
        $res=$this->fetchRow("email='".$email."'");
        return $res;
    }
    
    public function updateBindInof($res,$id){
        $data['email']=$res->email;
        $data['user_id']=$res->user_id;
        $data['sub_user_id']=$res->sub_user_id;
        $data['status']='1';
        $this->update($data, "id='".$id."'");
    }
}

