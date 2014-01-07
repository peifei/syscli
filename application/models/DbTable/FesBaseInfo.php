<?php

class Application_Model_DbTable_FesBaseInfo extends Application_Model_DbTable_Abstract
{

    protected $_name = 'fes_base_info';
    protected $_dbname='base';
    
    public function addNewFes($data){
        $this->insert($data);
    }
    
    public function updateFes($data,$fesId){
        $this->update($data, "id='".$fesId."'");
    }
    
    public function getFesList(){
        $res=$this->fetchAll();
        return $res;
    }
    
    public function getFesById($id){
        $res=$this->fetchRow("id='".$id."'");
        return $res;
    }
    
    public function getFesListArray(){
        $arr=array();
        foreach ($this->getFesList() as $fes){
            $arr[$fes->id]=$fes->fes_name;
        }
        return $arr;
    }
    public function getFesIdByName($fesName){
        $res=$this->fetchRow("fes_name='".$fesName."'");
        return $res;
    }


}

