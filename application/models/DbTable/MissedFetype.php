<?php

class Application_Model_DbTable_MissedFetype extends Application_Model_DbTable_Abstract
{

    protected $_name = 'missed_fetype';
    protected $_dbname='base';


    public function addNewMissedType($fesid,$fetype){
        $res=$this->fetchRow("festype='".$fetype."' and fes_id='".$fesid."'");
        $data['fes_id']=$fesid;
        $data['festype']=$fetype;
        $data['date']=new Zend_Db_Expr('now()');
        $data['status']=0;
        if(count($res)==0){
            $this->insert($data);
        }
    }
    /**
     * 取得所有未添加的基本类型列表
     * Enter description here ...
     */
    public function getAllMissedTypeList(){
        $res=$this->fetchAll("status='0'");
        return $res;
    }
    /**
     * 将未添加的基本类型的状态更新
     * @param unknown_type $feType
     * @param unknown_type $fesId
     */
    public function clearMissedTypeStatus($feType,$fesId){
        $res=$this->fetchRow("festype='".$feType."' and fes_id='".$fesId."'");
        if(count($res)>0){
            $this->update(array('status'=>'1'), "id='".$res->id."'");
        }
    }
    
}

