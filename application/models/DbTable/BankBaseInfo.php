<?php

class Application_Model_DbTable_BankBaseInfo extends Application_Model_DbTable_Abstract
{

    protected $_name = 'bank_base_info';
    protected $_dbname='base';
    
    public function addNewBank($data){
        $this->insert($data);
    }
    
    public function getBankList(){
        $res=$this->fetchAll();
        return $res;
    }
    
    public function getBankByWords($words){
        
    }
    
    public function getBankById($id){
        $res=$this->fetchRow("id='".$id."'");
        return $res;
    }
    
    public function getBankByName($bankName){
        $res=$this->fetchRow("bank_name='".$bankName."'");
        return $res;
    }
    
    public function updateBank($data,$bankId){
        $this->update($data, "id='".$bankId."'");
    }


}

