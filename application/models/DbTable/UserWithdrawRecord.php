<?php

class Application_Model_DbTable_UserWithdrawRecord extends Application_Model_DbTable_Abstract
{

    protected $_name = 'user_withdraw_record';
    protected $_dbname ='web';
    
    //提取返佣记录状态
    const STATUS_UNCHECKED=0;    //待审核
    const STATUS_CHECKFAILED=1;    //审核未通过
    const STATUS_UNDEALED=2;    //待处理（审核通过）
    const STATUS_DEALSUCCESS=3;    //处理成功
    const STATUS_DEALFAILED=4;    //处理失败
    const STATUS_DISMISS=9;    //用户撤销
    
    //记录类别标识
    const TYPE_MAIN=1;    //主记录
    const TYPE_DETAIL=0;    //明细记录
    
    /**
     * 根据请求添加一条新的用户自己的提现记录
     * Enter description here ...
     * @param unknown_type $request
     */
    public function addNewRecord($request){
        $data=array();
        $data['user_id']=Zend_Auth::getInstance()->getIdentity()->authedInfo['userId'];
        $data['withdraw_cash_us']=$request->getParam('withdrawMoney');
        $data['bci_id']=$request->getParam('bankChannel');
        $data['user_comments']=$request->getParam('userComments');
        $data['date_time']=new Zend_Db_Expr('now()');
        $data['type']=self::TYPE_MAIN;
        $data['status']=self::STATUS_UNCHECKED;
        try{
            $this->_db->beginTransaction();
            $dbFeDetail=new Application_Model_DbTable_FeDetail();
            $ucCash=$dbFeDetail->getUnClearedRebateByUserId($data['user_id']);
            //从明细表中取得用户的未结算金额，与请求的金额做比较
            //不一致抛异常,一致则标记数据为提取状态
            $arr=array();
            if($ucCash['sumrebate']!=(double)$data['withdraw_cash_us']){
                throw new Zend_Db_Exception('数据有异常，请重新登录！');
            }else{
                //取得明细表中标记为提取状态的记录id集合
                $arr=$dbFeDetail->clearRebateByUserId($data['user_id']);
            }
            //添加新的提取记录
            $uwrid=$this->insert($data);
            //存储该提取记录对应的明细表索引
            $dbWithdrawIndex=new Application_Model_DbTable_WithdrawIndex();
            $dbWithdrawIndex->addNewRecords($uwrid, $arr);
            $dbBankChannelInfo=new Application_Model_DbTable_BankChannelInfo();
            $dbBankChannelInfo->updatePriority($data['bci_id']);
            $this->_db->commit();
        }catch (Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
        
    }
    /**
     * 取得所有提现记录
     * Enter description here ...
     * @param unknown_type $status
     */
    public function getAllRecords($status=null){
        $selecter=$this->getCommonSelecter();
        if(null!=$status){
            $selecter->where("uw.status='".$status."' and uw.type='".self::TYPE_MAIN."'")->order('date_time desc');
        }else{
            $selecter->where("uw.type='".self::TYPE_MAIN."'")->order('date_time desc');
        }
        $res=$this->_db->fetchAll($selecter);
        return $res;
    }
    
    /**
     * 通用selecter
     * Enter description here ...
     */
    private function getCommonSelecter(){
        $db=$this->getDefaultAdapter();
        $selecter=$db->select()->from(array('uw'=>$this->_name))
                    ->joinLeft(array('ui'=>'user_identify'),'uw.user_id=ui.id',array('user_email','nick_name'))
                    ->joinLeft(array('sui'=>'sub_user_info'),'uw.sub_user_id=sui.id',array('sub_user_name'))
                    ->joinLeft(array('bci'=>'bank_channel_info'),'uw.bci_id=bci.id',array('bank_name','short_name','zs_area','channel_account_name','channel_account_num'));
        return $selecter;
    }
    
    /**
     * 取得用户提现记录
     * 不包含用户撤销的记录
     * @param unknown_type $userId
     */
    public function getWithdrawRedordByUserId($userId){
        $db=$this->getDefaultAdapter();
        $selecter=$this->getCommonSelecter()
                        ->where("uw.user_id='".$userId."' and uw.status!='".self::STATUS_DISMISS."' and uw.type='".self::TYPE_MAIN."'");
        $res=$db->fetchAll($selecter);
        return $res;
    }
    
    
    /**
     * 取得所有未审核的记录
     * 管理员后台审核调用
     */
    public function getUncheckedRecord(){
        $db=$this->_db;
        $selecter=$this->getCommonSelecter()
                        ->where("uw.status='".self::STATUS_UNCHECKED."' and uw.type='".self::TYPE_MAIN."'");
        $res=$db->fetchAll($selecter);
        return $res;
    }
    
    /**
     * 取得所有未处理的记录
     * 管理员后台处理调用
     */
    public function getUndealedRecord(){
        $db=$this->_db;
        $selecter=$this->getCommonSelecter()
                        ->where("uw.status='".self::STATUS_UNDEALED."' and uw.type='".self::TYPE_MAIN."'");
        $res=$db->fetchAll($selecter);
        return $res;
    }
    /**
     * 取得用户及下属体现金额的总和
     * @param unknown_type $userId
     */
    public function getUserAndSubUsersWithdraw($userId){
        $selecter=$this->select()->from($this->_name,array('sum(withdraw_cash_us) as sumwithdraw'))->where("user_id='".$userId."' and (status='".self::STATUS_DEALSUCCESS."' or status='".self::STATUS_UNCHECKED."' or status='".self::STATUS_UNDEALED."' )and type='".self::TYPE_MAIN."'");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    /**
     * 取得用户已提交待处理的金额总和
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getUserWithdrawingRecord($userId){
        $selecter=$this->select()->from($this->_name,array('sum(withdraw_cash_us) as sumwithdraw'))->where("user_id='".$userId."' and sub_user_id is null and (status='".self::STATUS_UNCHECKED."' or status='".self::STATUS_UNDEALED."')");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    
	/**
     * 取得下级用户已提交待处理的金额总和
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getSubUserWithdrawingRecord($userId,$subUserId){
        $selecter=$this->select()->from($this->_name,array('sum(withdraw_cash_us) as sumwithdraw'))->where("user_id='".$userId."' and sub_user_id='".$subUserId."' and (status='".self::STATUS_UNCHECKED."' or status='".self::STATUS_UNDEALED."')");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    
    
    /**
     * 取得用户已经成功获得返佣金额的总和
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getUserWithdrawedRecord($userId){
        $selecter=$this->select()->from($this->_name,array('sum(withdraw_cash_us) as sumwithdraw'))->where("user_id='".$userId."' and sub_user_id is null and status='".self::STATUS_DEALSUCCESS."'");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    
	/**
     * 取得用户已经成功获得返佣金额的总和
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function getSubUserWithdrawedRecord($userId,$subUserId){
        $selecter=$this->select()->from($this->_name,array('sum(withdraw_cash_us) as sumwithdraw'))->where("user_id='".$userId."' and sub_user_id='".$subUserId."' and status='".self::STATUS_DEALSUCCESS."'");
        $res=$this->fetchRow($selecter);
        return $res;
    }
    
    /**
     * 依据请求添加一条新的用户下级提现记录
     * Enter description here ...
     * @param unknown_type $request
     */
    public function addNewSubRecord($request){
        $data['user_id']=Zend_Auth::getInstance()->getIdentity()->authedInfo['userId'];
        $data['withdraw_cash_us']=$request['withdrawMoney'];
        $data['bci_id']=$request['bankChannel'];
        $data['user_comments']=$request['userComments'];
        $data['date_time']=new Zend_Db_Expr('now()');
        $data['status']=self::STATUS_UNCHECKED;
        $data['type']=self::TYPE_MAIN;
        if(""!=$request['subUserIdHide']){
            $data['sub_user_id']=$request['subUserIdHide'];
        }
        //由于可能同时提取多个下级的返佣，所以需要处理多个下级id
        $arrSubUser=explode(',', $data['sub_user_id']);
        
        try{
            $this->_db->beginTransaction();
            $sum=0;
            foreach ($arrSubUser as $subUser){
                if(isset($request['suw_'.$subUser])){
                    $sum+=(double)$request['suw_'.$subUser];
                }else{
                    throw New Exception('下级数据不完整，请退出系统重新登录再操作');
                }
            }
            if($sum!=(double)$data['withdraw_cash_us']){
                throw new Exception('数据不符合，请退出系统重新登录再操作');
            }
            
            $dbFeDetail=new Application_Model_DbTable_FeDetail();
            if(count($arrSubUser)>1){
                $ucCash=$dbFeDetail->getSubsUnClearedRebateBySubUserIds($data['user_id'],$arrSubUser);
            }else{
                $ucCash=$dbFeDetail->getSubUnClearedRebateBySubUserId($data['user_id'],$arrSubUser[0]);
            }
            if($ucCash['sumrebate']!=(double)$data['withdraw_cash_us']){
                throw new Exception('数据不符合，请退出系统重新登录再操作');
            }else{
                $arr=$dbFeDetail->clearRebateBySubUsers($data['user_id'],$data['sub_user_id']);
            }
            //插入一条主记录
            $uwrId=$this->insert($data);
            //如果提现的下级用户数大于1则插入明细记录
            if(count($arrSubUser)>1){
                $subData=$data;
                foreach ($arrSubUser as $subUser){
                    $subData['type']=self::TYPE_DETAIL;
                    $subData['sub_user_id']=$subUser;
                    $subData['withdraw_cash_us']=$request['suw_'.$subUser];
                    $subData['parentid']=$uwrId;
                    $this->insert($subData);
                }
                unset($subData);
            }
            $dbWithdrawIndex=new Application_Model_DbTable_WithdrawIndex();
            $dbWithdrawIndex->addNewRecords($uwrId, $arr);
            
            $dbBankChannelInfo=new Application_Model_DbTable_BankChannelInfo();
            $dbBankChannelInfo->updatePriority($data['bci_id']);
            
            $this->_db->commit();
        }catch (Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
    }
    
    /**
     * 设置审核通过
     * 审核通过后将状态设置为待处理
     * @param unknown_type $uwrId
     */
    public function passCheck($uwrId){
        $db=$this->_db;
        try{
            $db->beginTransaction();
            $this->update(array('status'=>self::STATUS_UNDEALED), "id='".$uwrId."'");
            $this->update(array('status'=>self::STATUS_UNDEALED), "parentid='".$uwrId."'");
            $db->commit();
        }catch (Exception $e){
            $db->rollBack();
            throw $e;
        }
    }
    
    
    /**
     * 恢复明细记录
     * Enter description here ...
     * @param unknown_type $uwrId
     */
    private function recoverDetailRecord($uwrId){
        $dbFeDetail=new Application_Model_DbTable_FeDetail();
        $dbWithdrawIndex=new Application_Model_DbTable_WithdrawIndex();
        $res=$dbWithdrawIndex->getFeDetailIds($uwrId);
        foreach ($res as $re){
            $dbFeDetail->update(array('clearflag'=>'0'), "id='".$re['fed_id']."'");
        }
    }
    /**
     * 用户撤销记录
     * Enter description here ...
     * @param unknown_type $uwrId
     */
    public function dismissRecord($uwrId){
        try{
            $this->_db->beginTransaction();
            $this->update(array('status'=>self::STATUS_DISMISS), "id='".$uwrId."'");
            $this->update(array('status'=>self::STATUS_DISMISS), "parentid='".$uwrId."'");
            $this->recoverDetailRecord($uwrId);
            $this->_db->commit();
        }catch(Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
    }
    /**
     * 提现审核被拒绝
     * Enter description here ...
     * @param unknown_type $uwrId
     */
    public function declineRecord($uwrId,$request){
        try{
            $this->_db->beginTransaction();
            $fbMessage=$request['feedBack'];
            $this->update(array('status'=>self::STATUS_CHECKFAILED,'feedback_message'=>$fbMessage), "id='".$uwrId."'");
            $this->update(array('status'=>self::STATUS_CHECKFAILED), "parentid='".$uwrId."'");
            $this->recoverDetailRecord($uwrId);
            $this->_db->commit();
        }catch(Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
    }
    /**
     * 财务处理
     * Enter description here ...
     * @param unknown_type $request
     */
    public function financialDeal($request){
        try{
            
            $data['exchange_rate']=$request['smtExRate'];
            $data['withdraw_cash_zh']=$request['smtZhRmb'];
            $data['deal_result']=$request['smtResult'];
            $data['feedback_message']=$request['smtFbmessage'];
            $data['status']=($data['deal_result']==0)?self::STATUS_DEALFAILED:self::STATUS_DEALSUCCESS;
            $uwrId=$request['uwrId'];
            $this->_db->beginTransaction();
            $this->update($data, "id='".$uwrId."'");
            $this->update(array('status'=>$data['status']), "parentid='".$uwrId."'");
            if(self::STATUS_DEALFAILED==$data['status']){
                $this->recoverDetailRecord($uwrId);
            }
            
            $this->_db->commit();
        }catch(Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
    }
    /**
     * 取得用户已经成功出账的总金额
     * Enter description here ...
     * @param unknown_type $userId
     */
    public function sumUserRebate($userId){
        $select=$this->select()->from($this->_name,array('sum(withdraw_cash_us) as sumrebate'))->where("user_id='".$userId."' and status='".self::STATUS_DEALSUCCESS."'");
        $res=$this->fetchRow($select);
        return $res;
    }
    /**
     * 一天内提取返佣的次数
     * Enter description here ...
     */
    public function getUserRebateTimes(){
        $nowstr=date('Y-m-d H:i:s',time());
        $lastDatStr=date('Y-m-d H:i:s',time()-86400);
        $selecter=$this->select()->from($this->_name,array('count(*) as count'))->where("date_time between '".$lastDatStr."' and '".$nowstr."' and status!='".self::STATUS_DISMISS."' and type='".self::TYPE_MAIN."'");
        $res=$this->fetchRow($selecter);
        return $res->count;
    }

}

