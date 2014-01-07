<?php
/**
 * 汇商账户
 * @author fxcm
 *
 */
class Application_Model_DbTable_FeAccount extends Application_Model_DbTable_Abstract
{

    protected $_name = 'fe_account';
    protected $_dbname = 'web';
    const STATUS_UNACTIVED=0;
    const STATUS_ACTIVED=1;
    const STATUS_DECLINED=2;
    
    /**
     * 新增账户
     * @param unknown_type $postData
     */
    public function addNewAccount($userId,$postData){
        try{
            $this->_db->beginTransaction();
            $data['fes_id']=$postData['feName'];
            $data['account_num']=$postData['accountNum'];
            $data['account_name']=$postData['accountName'];
            $data['email']=$postData['email'];
            $data['sub_user_id']=$postData['subUser'];
            //$data['user_id']=Zend_Auth::getInstance()->getIdentity()->authedInfo['userId'];
            $data['user_id']=$userId;
            $data['status']=self::STATUS_UNACTIVED;
            $data['active_time']=new Zend_Db_Expr('now()');
            $id=$this->insert($data);
            $data['id']=$id;
            $dbAccCmp=new Application_Model_DbTable_AccountCompare();
            $dbAccCmp->accCompare($data);
            $this->_db->commit();
        }catch (Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
    }
    /**
     * 自动绑定新账户
     * @param unknown_type $data
     */
    public function bindNewAccount($data){
        //查询该账户是否已经存在
        $res=$this->fetchRow("fes_id='".$data['fes_id']."' and account_num='".$data['account_name']."'");
        if(count($res)==0){
            $this->insert($data);
        }
    }
    
    
    
    /**
     * 删除账户
     * @param unknown_type $userId
     * @param unknown_type $accId
     */
    public function deleteAccount($userId,$accId){
        //$userId=Zend_Auth::getInstance()->getIdentity()->authedInfo['userId'];
        $res=$this->fetchRow("id='".$accId."' and user_id='".$userId."' and status !='".self::STATUS_ACTIVED."'");
        if(count($res)==0){
            throw new Exception("您提交的数据不合法");
        }
        $this->delete("id='".$accId."' and user_id='".$userId."' and status !='".self::STATUS_ACTIVED."'");
    }
    /**
     * 根据用户id及账户状态取得账户列表
     * Enter description here ...
     * @param unknown_type $userId
     * @param unknown_type $status
     */
    public function getAccountListByUserId($userId,$status=null){
        $db=$this->getAdapter();
        $selecter=$db->select();
        $selecter->from(array('fe'=>$this->_name),array('id','account_num','account_name','email','sub_user_id','status','active_time'))
                ->joinLeft(array('fes'=>BASEDB.'.fes_base_info'), 'fe.fes_id=fes.id', array('fes_name'))
                ->joinLeft(array('sbu'=>'sub_user_info'), 'fe.sub_user_id=sbu.id', array('sub_user_name'));
                
        if(null!=$status){
            if(is_array($status)){
                $strStatus=implode(',', $status);
                $selecter->where("fe.user_id='".$userId."' and status in(".$strStatus.")");
                $res=$db->fetchAll($selecter);
            }else{
                $selecter->where("fe.user_id='".$userId."' and status ='".$status."'");
                $res=$db->fetchAll($selecter);
            }
        }else{
            $selecter->where("fe.user_id='".$userId."'");
            $res=$db->fetchAll($selecter);
        }
        return $res;
    }
    /**
     * 根据用户id和账号id及非激活状态获取账户信息
     * @param unknown_type $userId
     * @param unknown_type $accId
     */
    public function getAccountInfoForUpdate($userId,$accId){
        $res=$this->fetchRow("user_id ='".$userId."' and id ='".$accId."' and status!='".self::STATUS_ACTIVED."'");
        return $res;
    }
    /**
     * 显示所有待审核账号
     */
    public function getAccountInfoForCheck(){
        $db=$this->_db;
        $selecter=$db->select()->from(array('fa'=>$this->_name),array('id','account_num','account_name','email','user_id','sub_user_id'))
        ->joinLeft(array('fb'=>BASEDB.'.fes_base_info'),'fa.fes_id=fb.id',array('fes_name'))
        ->joinLeft(array('ui'=>'user_identify'), 'fa.user_id=ui.id',array('user_email','nick_name','bind_mail'))
        ->joinLeft(array('sui'=>'sub_user_info'),'fa.sub_user_id=sui.id',array('sub_user_name'))
        ->joinLeft(array('ac'=>'account_compare'),'fa.id=ac.accid',array('status'))
        ->where("fa.status='".self::STATUS_UNACTIVED."'");
        $res=$db->fetchAll($selecter);
        return $res;
    }
    
	/**
     * 显示所有有效账号
     */
    public function getActivedAccounts(){
        $db=$this->getDefaultAdapter();
        $selecter=$db->select()->from(array('fa'=>$this->_name),array('id','account_num','account_name','email','user_id','sub_user_id'))
        ->joinLeft(array('fb'=>'fes_base_info'),'fa.fes_id=fb.id',array('fes_name'))
        ->joinLeft(array('ui'=>'user_identify'), 'fa.user_id=ui.id',array('user_email','nick_name','bind_mail'))
        ->joinLeft(array('sui'=>'sub_user_info'),'fa.sub_user_id=sui.id',array('sub_user_name'))
        ->joinLeft(array('ac'=>'account_compare'),'fa.id=ac.accid',array('status'))
        ->where("fa.status='".self::STATUS_ACTIVED."'");
        $res=$db->fetchAll($selecter);
        return $res;
    }
    
    
    /**
     * 更新账户信息
     * Enter description here ...
     * @param unknown_type $postData
     * @throws Exception
     */
    public function updateAccount($postData){
        try{
            $this->_db->beginTransaction();
            $accId=$postData['accIdHide'];
            $data['fes_id']=$postData['feName'];
            $data['account_num']=$postData['accountNum'];
            $data['account_name']=$postData['accountName'];
            $data['email']=$postData['email'];
            $data['sub_user_id']=$postData['subUser'];
            $data['user_id']=Zend_Auth::getInstance()->getIdentity()->authedInfo['userId'];
            $data['status']=0;
            $data['active_time']=new Zend_Db_Expr('now()');
            $this->update($data, "id ='".$accId."'");
            $data['id']=$accId;
            $dbAccCmp=new Application_Model_DbTable_AccountCompare();
            $dbAccCmp->updateCompare($data);
            $this->_db->commit();
        }catch(Exception $e){
            $this->_db->rollBack();
            throw $e;
        }
    }
    /**
     * 依据id取得账户信息
     * @param unknown_type $id
     */
    public function getAccountById($id){
        $res=$this->fetchRow("id='".$id."'");
        return $res;
    }
    /**
     * 依据汇商id及账户id来获取激活状态的账户
     * @param unknown_type $fesId
     * @param unknown_type $accNum
     */
    public function getAccountByFesAndAccnum($fesId,$accNum){
        $res=$this->fetchRow("fes_id='".$fesId."' and account_num='".$accNum."' and status='1'");
        return $res;
    }
    
    /**
     * 确认账号并计算该账号所有返佣
     * @param unknown_type $accId
     */
    public function confirmAccount($accId){
        $this->update(array('status'=>'1','active_time'=>new Zend_Db_Expr('now()')),"id='".$accId."'");
        $this->doAccCalRebate($accId);
    }
    /**
     * 账号审核被拒绝
     * @param unknown_type $accId
     */
    public function declineAccount($accId){
        $this->update(array('status'=>self::STATUS_DECLINED),"id='".$accId."'");
    }
    /**
     * 计算新增账户的返佣，当管理员审核通过账户后调用该方法
     */
    private function doAccCalRebate($accId){
        $db=$this->_db;
        $selecter=$db->select()->from(array('fa'=>$this->_name),array('account_num'))->joinLeft(array('fbi'=>BASEDB.'.fes_base_info'),'fa.fes_id=fbi.id',array('fes_name'))
                    ->where("fa.id='".$accId."'");
        $res=$db->fetchRow($selecter);
        
        $dataer=Application_Service_Dataimport_DataerFactory::getDataer($res['fes_name']);
        $dataer->calAllDataByAccnum($res['account_num']);
    }

}

