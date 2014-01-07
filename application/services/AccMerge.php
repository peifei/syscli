<?php
/**
 * 账户导入及自动识别
 * @author fxcm
 *
 */
class Application_Service_AccMerge
{
    public function importAccount(){
        $this->importFxcmAccount();
        $this->importIronfxAccount();
    }
    /**
     * 导入fxcm的账户
     */
    public function importFxcmAccount(){
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini');
        $db2=$config->production->db2;
        $db2=$db2->toArray();
        $db2=zend_db::factory($db2['adapter'],$db2['params']);
        $selecter=$db2->select()->from('fxcm_all_accounts');
        //取得原始数据集合
        $res=$db2->fetchAll($selecter);
        $dbFesbaseInfo=new Application_Model_DbTable_FesBaseInfo();
        $dbAccountDepository=new Application_Model_DbTable_AccountDepository();
        $dbFeAcctoun=new Application_Model_DbTable_FeAccount();
        $dbMailAutoBind=new Application_Model_DbTable_MailAutoBinded();
        $fxcm=$dbFesbaseInfo->getFesIdByName('FXCM');
        //遍历集合
        foreach($res as $re){
            $data=array();
            $data['fes_id']=$fxcm->id;
            $data['account_num']=$re['account'];
            $data['account_name']=$re['customer_name'];
            $data['email']=$re['email'];
            $data['status']=0;
            $insId=$dbAccountDepository->addNewAccount($data);
            //如果是新增的账户则执行自动绑定操作
            if($insId!=0){
                $mailBindInfo=$dbMailAutoBind->getInfoByMail($data['email']);
                //该邮箱是否可以自动绑定
                if(count($mailBindInfo)==1){
                    if(isset($mailBindInfo->sub_user_id)){
                        $data['sub_user_id']=$mailBindInfo->sub_user_id;
                    }
                    $data['user_id']=$mailBindInfo->user_id;
                    $data['active_time']=new Zend_Db_Expr('now()');
                    $dbFeAcctoun->bindNewAccount($data);
                }
                
            }
            
        }
    
    }
    /**
     * 导入ionfx的账户
     */
    public function importIronfxAccount(){
        $config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/application.ini');
        $db3=$config->production->db3;
        $db3=$db3->toArray();
        $db3=zend_db::factory($db3['adapter'],$db3['params']);
        $selecter=$db3->select()->from('ironfx_all_users_balances')->where("login !=''");
        $res=$db3->fetchAll($selecter);
        $dbFesbaseInfo=new Application_Model_DbTable_FesBaseInfo();
        $dbAccountDepository=new Application_Model_DbTable_AccountDepository();
        $dbFeAcctoun=new Application_Model_DbTable_FeAccount();
        $dbMailAutoBind=new Application_Model_DbTable_MailAutoBinded();
        $ironfx=$dbFesbaseInfo->getFesIdByName('IRONFX');
        foreach($res as $re){
            $data=array();
            $data['fes_id']=$ironfx->id;
            $data['account_num']=$re['login'];
            $data['account_name']=$re['client_name'];
            $data['email']=$re['e-mail'];
            $data['status']=0;
            $insId=$dbAccountDepository->addNewAccount($data);
            if($insId!=0){
                $mailBindInfo=$dbMailAutoBind->getInfoByMail($data['email']);
                if(count($mailBindInfo)==1){
                    if(isset($mailBindInfo->sub_user_id)){
                        $data['sub_user_id']=$mailBindInfo->sub_user_id;
                    }
                    $data['user_id']=$mailBindInfo->user_id;
                    $data['active_time']=new Zend_Db_Expr('now()');
                    $dbFeAcctoun->insert($data);
                }
                
            }
            
        }
    }
    
}
?>