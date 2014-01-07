<?php

class Application_Model_DbTable_Fxcmdata extends Zend_Db_Table_Abstract
{

    protected $_name = 'fxcmdata';
    /**
     * 依据周期取得数据
     * Enter description here ...
     * @param unknown_type $periodflag
     */
    public function getDataListByPeriod($periodflag){
        $res=$this->fetchAll("periodflag='".$periodflag."'");
        return $res->toArray();
    }
    /**
     * 取得用户当前周期的总手数
     * Enter description here ...
     * @param unknown_type $periodflag
     * @param unknown_type $userId
     */
    public function getUserSumPeriodQuantity($periodflag,$userId){
        $db=$this->_db;
        $selecter=$db->select()->from(array('fd'=>$this->_name),array('sum(fd.volume_closed) as sumvolume') )
                               ->joinLeft(array('fa'=>'fe_account'),'fa.account_num=fd.account',null)
                               ->where("fd.periodflag='".$periodflag."' and fd.transformed='1' and fa.user_id='".$userId."'");
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
        $selecter=$db->select()->from(array('fd'=>$this->_name),array('sum(fd.volume_closed) as sumvolume'))
                               ->joinLeft(array('fa'=>'fe_account'),'fa.account_num=fd.account',null)
                               ->where("fd.periodflag='".$periodflag."' and fd.transformed='1' and fa.sub_user_id='".$subUserId."'");
        $res=$db->fetchRow($selecter);
        return $res;
    }
    /**
     * 依据账号取得数据
     * Enter description here ...
     * @param unknown_type $accnum
     */
    public function getDataListByAccnum($accnum){
        $res=$this->fetchAll("account='".$accnum."'");
        return $res;
    }
    
    
}

