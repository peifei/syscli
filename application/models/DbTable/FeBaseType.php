<?php

class Application_Model_DbTable_FeBaseType extends Application_Model_DbTable_Abstract
{

    protected $_name = 'fe_base_type';
    protected $_dbname='base';
    
    public function addNewFeType($postData){
        $data['name']=$postData['feTypeName'];
        $data['transform']=$postData['feTransform'];
        $data['fes_id']=$postData['fesSelector'];
        $data['type']=0;
        $dbBrokerageStandard=new Application_Model_DbTable_BrokerageStandard();
        $standareList=$dbBrokerageStandard->getStandardListByFes($data['fes_id']);
        try{
        $this->_db->beginTransaction();
        $feTypeId=$this->insert($data);
        //var_dump($feTypeId);
        foreach ($standareList as $standard) {
            $arr[$standard->id]=$postData['h'.$standard->id];
            $dbBrokerageStandard->addNewFeType($arr, $feTypeId);
        }
        $this->_db->commit();
        }catch (Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
    }
    
    public function getFeTypeList(){
        $res=$this->fetchAll();
        return $res;
    }
    
    public function getFeTypeListByFesId($fesId){
        $res=$this->fetchAll("fes_id='".$fesId."'");
        return $res;
    }
    
    public function getFeTypeByFesIdAndFeName($fesId,$feName){
        $res=$this->fetchRow("fes_id='".$fesId."' and name='".$feName."'");
        return $res;
    }
    
    public function getFeTypeListArray(){
        $arr=array();
        foreach ($this->getFeTypeList() as $fe){
            $arr[$fe->id]=$fe->name;
        }
        return $arr;
    }
    
    
    public function getFeTypeById($id){
        $res=$this->fetchRow("id='".$id."'");
        return $res;
    }
    
    public function updateFeType($postData){
        $data['name']=$postData['feTypeName'];
        $data['transform']=$postData['feTransform'];
        $data['fes_id']=$postData['fesSelector'];
        $this->update($data, "id='".$postData['feTypeId']."'");
    }


}

