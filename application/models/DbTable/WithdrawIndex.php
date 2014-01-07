<?php

class Application_Model_DbTable_WithdrawIndex extends Application_Model_DbTable_Abstract
{

    protected $_name = 'withdraw_index';
    protected $_dbname ='web';


    public function addNewRecords($uwrId,array $fedIds){
        foreach ($fedIds as $fedId){
            $this->insert(array('uwr_id'=>$uwrId,'fed_id'=>$fedId));
        }
    }
    
    public function getFeDetailIds($uwrId){
        $res=$this->fetchAll("uwr_id='".$uwrId."'");
        return $res;
    }
}

