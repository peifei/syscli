<?php

class Application_Model_DbTable_RebateImport extends Application_Model_DbTable_Abstract
{

    protected $_name = 'rebate_import';
    protected $_dbname='base';
    
    public function getRecords(){
        $res=$this->fetchAll();
        return $res;
    }
    
    public function getRebateDetailForUser($userId){
        $db=$this->_db;
        $select=$db->select()->from(array('ri'=>$this->_name))
                            ->joinLeft(array('fbi'=>'fes_base_info'),'ri.fes_id=fbi.id',array('fes_name'))
                            ->joinInner(array('fa'=>'fe_account'),'ri.fes_id=fa.fes_id and ri.account=fa.account_num',array('account_name','email'))
                            ->joinLeft(array('sui'=>'sub_user_info'),'fa.sub_user_id=sui.id',array('sub_user_name'))
                            //->joinRight(array('fd'=>'fe_detail'),'')
                            ->where("ri.userid='".$userId."'");
        $res=$db->fetchAll($select);
        return $res;
    }
    
    public function getSumRebateByUserid($userId){
         $selecter=$this->_db->select()->from(array('ri'=>$this->_name),array('sum(rebate) as sumrebate'))->where("userid='".$userId."'")
         ->joinInner(array('fa'=>'fe_account'),'ri.fes_id=fa.fes_id and ri.account=fa.account_num');
         $res=$this->_db->fetchRow($selecter);
         return $res;
        
    }


}

