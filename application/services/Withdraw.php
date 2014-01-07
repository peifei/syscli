<?php
class Application_Service_Withdraw
{
    private $fedb;
    private $wddb;
    public function __construct(){
        $this->fedb=new Application_Model_DbTable_FeDetail();
        $this->wddb=new Application_Model_DbTable_UserWithdrawRecord();
    }
    /**
     * 取得用户总入账
     * @param unknown_type $userId
     */
    public function getUserAllIncome($userId){
        $res=$this->fedb->getUserAllIncome($userId);
        return $res['sumrebate'];
    }
    /**
     * 取得下级总入账
     * @param unknown_type $userId
     * @param unknown_type $subUserId
     */
    public function getSubUserAllIncome($userId,$subUserId){
        $res=$this->fedb->getSubUserAllIncome($userId,$subUserId);
        return $res['sumrebate'];
    }
    /**
     * 取得用户及全部下级的总入账
     * @param unknown_type $userId
     */
    public function getUserAndSubUsersAllIncome($userId){
        $res=$this->fedb->getUserAndSubUsersAllIncome($userId);
        return $res['sumrebate'];
    }
    /**
     * 取得用户总出账（提取金额）
     * @param unknown_type $userId
     */
    public function getUserAllWithdraw($userId){
        //已出账+待出账
        return $this->getUserWithdrawed($userId)+$this->getUserWithdrawing($userId);
    }
    /**
     * 
     * 取得用户下级总出账（提取金额）
     * @param unknown_type $userId
     * @param unknown_type $subUserId
     */
    public function getSubUserAllWithdraw($userId,$subUserId){
        return $this->getSubUserWithdrawed($userId, $subUserId)+$this->getSubUserWithdrawing($userId, $subUserId);
    }
    /**
     * 取得用户及全部下级的总出账
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getUserAndSubUsersAllWithdraw($userId){
        $res=$this->wddb->getUserAndSubUsersWithdraw($userId);
        return $res['sumwithdraw'];
    }
    
    
    /**
     * 取得用户已出账金额
     * @param unknown_type $userId
     */
    public function getUserWithdrawed($userId){
        $res=$this->wddb->getUserWithdrawedRecord($userId);
        return $res['sumwithdraw'];
    }
    /**
     * 取得用户待出账（出账处理中）金额
     * @param unknown_type $userId
     */
    public function getUserWithdrawing($userId){
        $res=$this->wddb->getUserWithdrawingRecord($userId);
        return $res['sumwithdraw'];
    }
    /**
    * 取得下级已出账金额
    * @param unknown_type $userId
    */
    public function getSubUserWithdrawed($userId,$subUserId){
        $res=$this->wddb->getSubUserWithdrawedRecord($userId, $subUserId);
        return $res['sumwithdraw'];
    }
    /**
    * 取得用户待出账（出账处理中）金额
    * @param unknown_type $userId
    */
    public function getSubUserWithdrawing($userId,$subUserId){
        $res=$this->wddb->getSubUserWithdrawingRecord($userId, $subUserId);
        return $res['sumwithdraw'];
    }
    /**
     * 取得用户余额
     * @param unknown_type $userId
     */
    public function getUserBalance($userId){
        return $this->getUserAllIncome($userId)-$this->getUserAllWithdraw($userId);
    }
    /**
     * 取得下级余额
     * @param unknown_type $userId
     * @param unknown_type $subUserId
     */
    public function getSubUserBalance($userId,$subUserId){
        return round(($this->getSubUserAllIncome($userId, $subUserId)-$this->getSubUserAllWithdraw($userId, $subUserId)),2);
    }
    /**
     * 取得多个下级的余额
     * Enter description here ...
     * @param unknown_type $userId
     * @param array $subUsersId
     */
    public function getSubUsersBalance($userId,array $subUsersId){
        $balance=0;
        foreach ($subUsersId as $subUserId){
            $balance+=$this->getSubUserBalance($userId, $subUserId);
        }
        return $balance;
    }
    /**
     * 取得用户下级佣金信息列表
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getSubUsersWithdrawList($userId){
        $dbSubUserInfo=new Application_Model_DbTable_SubUserInfo();
        $subUserList=$dbSubUserInfo->getSubUsersByUserId($userId);
        $arrList=array();
        foreach($subUserList as $subUser){
            $data=array();
            $data['subUserId']=$subUser['id'];
            $data['subUserName']=$subUser['sub_user_name'];
            $data['income']=(float)$this->getSubUserAllIncome($userId, $subUser['id']);
            $data['withdraw']=(float)$this->getSubUserAllWithdraw($userId, $subUser['id']);
            $data['balance']=(float)$this->getSubUserBalance($userId, $subUser['id']);
            $arrList[]=$data;
        }
        return $arrList;
    }
    /**
     * 取得可提取返佣（返佣金额>0）的下级列表
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getWithDrawableSubUserList($userId){
        $list=$this->getSubUsersWithdrawList($userId);
        $availableList=array();
        foreach($list as $subUser){
            if($subUser['balance']>0){
                $availableList[]=$subUser;
            }
        }
        return $availableList;
    }
    
    
}
