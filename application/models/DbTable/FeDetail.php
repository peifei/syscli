<?php
/**
 * 返佣明细
 * @author fxcm
 */
class Application_Model_DbTable_FeDetail extends Application_Model_DbTable_Abstract
{

    protected $_name = 'fe_detail';
    protected $_dbname ='base';
    /**
     * 添加记录
     * @param unknown_type $records
     */
    public function addRecords(array $records){
        //TODO check detail in the futrue;
        try{
            //$this->_db->beginTransaction();
            foreach($records as $record){
                $this->insert($record);
            }
            //$this->_db->commit();
        }catch(Exception $e){
            //$this->_db->rollBack();
            throw $e;
        }
    }
    //暂时保留该方法，移除首页列表后可以删除TODO
/*    public function getRecordByUserId($userId){
        $res=$this->fetchAll("user_id='".$userId."' and type ='0' and clearFlag='0'");
        return $res;
    }*/
    //暂时保留该方法，移除首页列表后可以删除TODO
/*    public function getSubRecordByUserId($userId){
        $res=$this->fetchAll("user_id='".$userId."' and type ='1' and clearFlag='0'");
        return $res;
    }*/
    /**
     * 取得某一用户某一周期记录
     * @param unknown_type $userId
     * @param unknown_type $type 区分是归属于下级的还是归属于用户的记录
     * @param unknown_type $periodflag
     */
    public function getPeriodRecordsGroupbyAccount($userId,$type,$periodflag){
        //$res=$this->fetchAll("user_id='".$userId."' and type ='".$type."' and periodflag='".$periodflag."'");
        $db=$this->_db;
        $selecter=$db->select()->from($this->_name,array('sum(rebate) as rebate','sum(fe_quantity) as fe_quantity','fe_account_num','fe_account_name','sub_user_id','fe_time','user_id'))
                            ->where("user_id='".$userId."' and type ='".$type."' and periodflag='".$periodflag."'")
                            ->group(array('fe_account_num','fe_account_name','sub_user_id','fe_time','user_id'));
        $res=$db->fetchAll($selecter);
        return $res;
    }
    /**
     * 取得某一账户周期内的明细记录
     * Enter description here ...
     * @param unknown_type $userId
     * @param unknown_type $accNum
     * @param unknown_type $type
     * @param unknown_type $periodflag
     */
    public function getAccountDetail($userId,$subId,$accNum,$type,$periodflag,$feTime){
        //echo $accNum;
        if(''==$subId){
            $res=$this->fetchAll("user_id='".$userId."' and sub_user_id is null and fe_account_num='".$accNum."' and type ='".$type."' and periodflag='".$periodflag."' and fe_time='".$feTime."'");
        }else{
            $res=$this->fetchAll("user_id='".$userId."' and sub_user_id='".$subId."' and fe_account_num='".$accNum."' and type ='".$type."' and periodflag='".$periodflag."' and fe_time='".$feTime."'");
        }
        //$res=$this->fetchAll("user_id='".$userId."' and type ='".$type."' and periodflag='".$periodflag."'");
        return $res;
    }
    
    /**
     * 取得某一用户多个下级的记录
     * @param unknown_type $userId
     * @param unknown_type $subUserIds
     */
    public function getSubRecordsBySubUserIds($userId,$subUserIds){
        $strSubUserIds=implode(',', $subUserIds);
        $res=$this->fetchAll("user_id='".$userId."' and sub_user_id in (".$strSubUserIds.") and type ='1' and clearFlag='0'");
        return $res;
    }

    public function getSubRecordBySubUserId($subUserId){
        $res=$this->fetchAll("sub_user_id='".$subUserId."' and type ='1' and clearFlag='0'");
        return $res;
    }
    /**
     * 取得用户帐号下所有未结算金额总和
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getAllUnClearedRebateByUserId($userId){
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate'))->where("user_id='".$userId."' and clearflag='0'");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    
    public function getUnclearedSubUserList($userId){
        $selecter=$this->select()->from($this->_name,array('sub_user_id'))->where("user_id='".$userId."' and clearflag='0'")->group('sub_user_id');
        $res=$this->fetchAll($selecter);
        return $res;
    }
    
    /**
     * 取得用户自己未结算金额
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getUnClearedRebateByUserId($userId){
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate'))->where("user_id='".$userId."' and type='0' and clearflag='0'");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    /**
     * 将用户未结算金额标记为结算状态
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function clearRebateByUserId($userId){
        $res=$this->fetchAll("user_id='".$userId."' and type='0' and clearflag='0'");
        foreach($res as $re){
            $arr[]=$re['id'];
        }
        //TODO 用户提交返佣申请的时候记录，更新返佣的状态，此处目前想到的办法是新建一个关联表专门标记状态
        //$this->update(array('clearflag'=>'1'), "user_id='".$userId."' and type='0' and clearflag='0'");
        return $arr;
    }
    /**
     * 将下级未结算金额标记为结算状态
     * Enter description here ...
     * @param unknown_type $userId
     * @param unknown_type $subUsersId
     */
    public function clearRebateBySubUsers($userId,$subUsersId){
        $res=$this->fetchAll("user_id='".$userId."' and type='1' and sub_user_id in(".$subUsersId.") and clearflag='0'");
        foreach($res as $re){
            $arr[]=$re['id'];
        }
        //TODO 此处处理同用户未结算金额
        //$this->update(array('clearflag'=>'1'), "user_id='".$userId."' and type='1' and sub_user_id in(".$subUsersId.") and clearflag='0'");
        return $arr;
    }
    
    
    /**
     * 取得所下级未结算金额
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getSubUnClearedRebateByUserId($userId){
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate'))->where("user_id='".$userId."' and type='1' and clearflag=0");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    /**
     * 取得多个下级未结算金额
     * Enter description here ...
     * @param unknown_type $userId
     * @param unknown_type $subUserIds
     */
    public function getSubsUnClearedRebateBySubUserIds($userId,array $subUserIds){
        $strSubUserIds=implode(',', $subUserIds);
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate'))->where("user_id='".$userId."' and sub_user_id in(".$strSubUserIds.") and type='1' and clearflag=0");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    /**
     * 取得单个下级未结算金额
     * Enter description here ...
     * @param unknown_type $userId
     * @param unknown_type $subUserId
     */
    public function getSubUnClearedRebateBySubUserId($userId,$subUserId){
        //return $this->getSubsUnClearedRebateBySubUserIds($userId, array($subUserId));
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate'))->where("user_id='".$userId."' and sub_user_id='".$subUserId."' and type='1' and clearflag=0");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    /**
     * 新增调整记录
     * Enter description here ...
     * @param unknown_type $request
     */
    public function newAdjust($postdata){
        $uid=$postdata['userId'];
        if(''==$uid||0==$uid){
            $email=$postdata['userEmail'];
            $dbUserIdentify=new Application_Model_DbTable_UserIdentify();
            $userInfo=$dbUserIdentify->getUserInfoByEmail($email);
            
        }
        $data['fes_rec_id']=0;
        $data['user_id']=$userInfo->id;
        $subId=$postdata['subUserId'];
        if(''==$subId||0==$subId){
            $data['sub_user_id']=new Zend_Db_Expr('null');
        }else{
            $data['sub_user_id']=(int)$postdata['subUserId'];
        }
        $data['fe_account_num']='--';
        $data['fe_account_name']='--';
        $message=$postdata['message'];
        $data['fe_name']='--';
        $data['fe_quantity']='1';
        $data['rebate']=$postdata['adjust'];
        $data['fe_time']=new Zend_Db_Expr("DATE_FORMAT(now(),'%Y-%m-%d')");
        $data['type']=(''==$subId||0==$subId)?0:1;
        $data['clearflag']=0;
        $dbPeriod=new Application_Model_DbTable_PeriodRecords();
        $res=$dbPeriod->getLastPeriod();
        $data['periodflag']=$res['periodflag'];
        $data['comments']=$message;
        $data['adjust_flag']=1;
        $this->insert($data);
    }
    /**
     * 取得用户调整列表
     * Enter description here ...
     */
    public function getAdjustList($userId){
        
        $selecter=$this->_db->select()->from(array('a'=>$this->_name))->joinLeft(array('b'=>'sub_user_info'),'a.sub_user_id=b.id',array('sub_user_name'))->where("a.user_id='".$userId."' and a.adjust_flag =1");
        $res=$this->_db->fetchAll($selecter);
        return $res;
    }
    /**
     * 取得所有调整信息列表
     */
    public function getAllAdjustList(){
        $selecter=$this->_db->select()->from(array('a'=>$this->_name))
                            ->joinLeft(array('b'=>WEBDB.'.sub_user_info'),'a.sub_user_id=b.id',array('sub_user_name'))
                            ->joinLeft(array('c'=>WEBDB.'.user_identify'), 'a.user_id=c.id',array('user_email'))
                            ->where("a.adjust_flag =1");
        $res=$this->_db->fetchAll($selecter);
        return $res;
    }
    /**
     * 取得用户调整金额总计
     * Enter description here ...
     */
    public function getSumAdjust($userId){
        $selecter=$this->_db->select()->from($this->_name,array('sum(rebate) as sumrebate'))->where("user_id='".$userId."' and fes_rec_id =0");
        $res=$this->_db->fetchRow($selecter);
        return $res;
    }
    /**
     * 按周期分组查询总入账
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getPeriodAllRebateByUserId($userId){
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate','periodflag'))->where("user_id='".$userId."'")->group('periodflag');
        $res=$this->fetchAll($selecter);
        return $res;
    }
	/**
     * 按周期分组查询归属于用户的入账
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getPeriodUserRebateByUserId($userId){
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate','periodflag'))->where("user_id='".$userId."' and type ='0'")->group('periodflag');
        $res=$this->fetchAll($selecter);
        return $res;
    }
	/**
     * 按周期分组查询归属于用户下级的入账
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getPeriodSubusersRebateByUserId($userId){
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate','periodflag'))->where("user_id='".$userId."' and type='1'")->group('periodflag');
        $res=$this->fetchAll($selecter);
        return $res;
    }
    /**
     * 取得用户总入账
     * @param unknown_type $userId
     */
    public function getUserAllIncome($userId){
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate'))->where("user_id='".$userId."' and type='0'");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    /**
     * 取得用户及全部下级的总入账
     * @param unknown_type $userId
     */
    public function getUserAndSubUsersAllIncome($userId){
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate'))->where("user_id='".$userId."'");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    /**
     * 取得下级总入账
     * @param unknown_type $userId
     * @param unknown_type $subUserId
     */
    public function getSubUserAllIncome($userId,$subUserId){
        $selecter=$this->select()->from($this->_name,array('sum(rebate) as sumrebate'))->where("user_id='".$userId."' and sub_user_id='".$subUserId."' and type='1'");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    

    

}

