<?php

class Application_Model_DbTable_Ironfxdata extends Zend_Db_Table_Abstract
{

    protected $_name = 'ironfxdata';

    public function getDataListByPeriod($periodflag){
        $res=$this->fetchAll("periodflag='".$periodflag."'");
        return $res->toArray();
    }
    /**
     * 取得用户当前周期的总手数
     * 
     * Enter description here ...
     * @param unknown_type $periodflag
     * @param unknown_type $userId
     */
    public function getUserSumPeriodQuantity($periodflag,$userId){
        $db=$this->_db;
        $selecter=$db->select()->from(array('id'=>$this->_name),array('sum(id.volume) as sumvolume') )
                               ->joinLeft(array('fa'=>'fe_account'),'fa.account_num=id.login',null)
                               ->where("id.periodflag='".$periodflag."' and id.transformed='1' and fa.user_id='".$userId."'");
       $res=$db->fetchRow($selecter);
        return $res;
    }
    
    /**
     * 取得下属当前周期的总手数
     * Enter description here ...
     * @param unknown_type $periodflag
     * @param unknown_type $subUserId
     */
    public function getSubUserSumPeriodQuantity($periodflag,$subUserId){
        $db=$this->_db;
        $selecter=$db->select()->from(array('id'=>$this->_name),array('sum(id.volume) as sumvolume'))
                               ->joinLeft(array('fa'=>'fe_account'),'fa.account_num=id.login',null)
                               ->where("id.periodflag='".$periodflag."' and id.transformed='1' and fa.sub_user_id='".$subUserId."'");
        $res=$db->fetchRow($selecter);
        return $res;
    }
    
    public function getDataListByAccnum($accnum){
        $res=$this->fetchAll("login='".$accnum."'");
        return $res;
    }
    
    
}

