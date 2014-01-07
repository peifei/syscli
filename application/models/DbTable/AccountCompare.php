<?php
/**
 * 账户匹配
 * @author fxcm
 *
 */
class Application_Model_DbTable_AccountCompare extends Application_Model_DbTable_Abstract
{

    protected $_name = 'account_compare';
    protected $_dbname ='web';

    /**
     * 修改之前的匹配方法,根据需求重写，本方法暂时保留
     */
/*    public function accCompare($accInfo){
        $dbAccountDepository=new Application_Model_DbTable_AccountDepository();
        $res=$dbAccountDepository->fetchRow("fes_id='".$accInfo['fes_id']."' and account_num='".$accInfo['account_num']."' and email='".$accInfo['email']."'");
        if(count($res)==1){
            //插入一条完全匹配的标记
            $this->insert(array('accid'=>$accInfo['id'],'acdid'=>$res['id'],'status'=>'0'));
            return;
        }
        
        $res=$dbAccountDepository->fetchRow("fes_id='".$accInfo['fes_id']."' and account_num='".$accInfo['account_num']."'");
        if(count($res)==1){
            //插入一条帐号匹配的标记
            $this->insert(array('accid'=>$accInfo['id'],'acdid'=>$res['id'],'status'=>'1'));
        }
        $res=$dbAccountDepository->fetchAll("fes_id='".$accInfo['fes_id']."' and email='".$accInfo['email']."'");
        $count=count($res);
        foreach($res as $re){
            if(Application_Service_Strcmp::similarCmp($accInfo['account_num'], $re->account_num, 80)){
                //插入一条邮箱匹配帐号80%相似的标记
                //$data=array('accid'=>$accInfo['id'],'acdid'=>$res['id'],'status'=>'2');
                $this->insert(array('accid'=>$accInfo['id'],'acdid'=>$re['id'],'status'=>'2'));
            }
        }
    }*/
    /**
     * 匹配账户并插入新数据
     * @param unknown_type $accInfo
     */
    public function accCompare($accInfo){
        $dbAccountDepository=new Application_Model_DbTable_AccountDepository();
        $res=$dbAccountDepository->fetchRow("fes_id='".$accInfo['fes_id']."' and account_num='".$accInfo['account_num']."'");
        if(count($res)==1){
            if($res['email']==$accInfo['email']&&$res['account_name']==$accInfo['account_name']){
                //插入一条完全匹配的标记
                $this->insert(array('accid'=>$accInfo['id'],'acdid'=>$res['id'],'status'=>'0'));
            }elseif($res['email']==$accInfo['email']&&$res['account_name']!=$accInfo['account_name']){
                //插入一条邮箱匹配但用户名不匹配的标记
                $this->insert(array('accid'=>$accInfo['id'],'acdid'=>$res['id'],'status'=>'1'));
            }elseif($res['email']!=$accInfo['email']&&$res['account_name']==$accInfo['account_name']){
                //插入一条邮箱不匹配但用户名匹配的标记
                $this->insert(array('accid'=>$accInfo['id'],'acdid'=>$res['id'],'status'=>'2'));
            }
        }
    }
    /**
     * 更新匹配数据
     * @param unknown_type $accInfo
     */
    public function updateCompare($accInfo){
        $this->delete("accid='".$accInfo['id']."'");
        $this->accCompare($accInfo);
    }
    /**
     * 
     * 取得匹配记录
     * @param unknown_type $accId
     */
    public function getComparedAcc($accId){
        $db=$this->_db;
        $selecter=$db->select()->from(array('ac'=>$this->_name),array('status'))
                    ->joinLeft(array('ad'=>BASEDB.'.account_depository'),'ac.acdid=ad.id',array('account_num','account_name','email'))
                    ->joinLeft(array('fb'=>BASEDB.'.fes_base_info'), 'ad.fes_id=fb.id',array('fes_name'))
                    ->where("ac.accid='".$accId."'");
        $res=$db->fetchAll($selecter);
        return $res;
    }

}

