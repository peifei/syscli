<?php

class Application_Model_DbTable_BrokerageStandardBak extends Application_Model_DbTable_Abstract
{


    protected $_name = 'brokerage_standard_bak';
    protected $_dbname='base';
    
    public function bakNewStandard($bakData){
        $data['standard_id_bak']=$bakData['id'];
        $data['fes_id_bak']=$bakData['fes_id'];
        $data['name_bak']=$bakData['name'];
        $data['standard_bak']=$bakData['standard'];
        $data['active_time_bak']=$bakData['active_time'];
        $data['status_bak']=$bakData['status'];
        $data['bak_time']=new Zend_Db_Expr('now()');
        $data['isdefault_bak']=$bakData['isdefault'];
        $data['isfuhe_bak']=$bakData['isfuhe'];
        $data['priority_bak']=$bakData['priority'];
        if(isset($bakData)){
            $data['comments']=$bakData['comments'];
        }
        $this->insert($data);
    }
    
}

