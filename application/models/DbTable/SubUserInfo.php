<?php
/**
 * 下级信息
 * @author fxcm
 *
 */
class Application_Model_DbTable_SubUserInfo extends Application_Model_DbTable_Abstract
{

    protected $_name = 'sub_user_info';
    protected $_dbname ='web';
    /**
     * 添加新下级
     * @param unknown_type $data
     */
    public function addNewSubUser($data){
        $data['user_id']=Zend_Auth::getInstance()->getIdentity()->authedInfo['userId'];
        $data['add_time']=new Zend_Db_Expr('now()');
        $this->insert($data);
    }
    /**
     * 取得某一用户的所有下级
     * @param unknown_type $userId
     */
    public function getSubUsersByUserId($userId){
        $res=$this->fetchAll("user_id='".$userId."'",'id desc');
        return $res;
    }
    /**
     * 取得某一用户的指定下级
     * @param unknown_type $userId
     * @param unknown_type $id
     */
    public function getSubUserById($userId,$id){
        $res=$this->fetchRow("id='".$id."' and user_id='".$userId."'");
        //$res=$this->fetchRow("id='".$id."'");
        return $res;
    }
    /**
     * 更新用户标准
     * @param unknown_type $postData
     */
    public function updateUserStandard($postData){
        
        $subUserId=$postData['suIdHide'];
        //$postElements=$postData->getPost();
        foreach ($postData as $key=>$value){
            if('suIdHide'!=$key&&'sumtBtn'!=$key&&'defaultTemplate'!=$key&&'periodApply'!=$key&&'periodHide'!=$key){
                $arr[]=$value;
            }
        }
        //将标准集合转换为字符串
        $strStandardId=implode(',', $arr);
        //更新标准
        $this->update(array('standard_id'=>$strStandardId), "id='".$subUserId."'");
        $periodApply=$postData['periodApply'];
        
        //如果用户选择本月即生效的话，除了更新下级表中的标准记录外，还要更新周期标准表中的记录
        if(0==$periodApply){
            $periodflag=$postData['periodHide'];
            $dbStdPrdRecords=new Application_Model_DbTable_StdPrdRecords();
            $userId=Zend_Auth::getInstance()->getIdentity()->authedInfo['userId'];
            $dbBrokerageStandard=new Application_Model_DbTable_BrokerageStandard();
            
            foreach ($arr as $stdId){
                $standard=$dbBrokerageStandard->getStandardById($stdId);
                $dbStdPrdRecords->updateSubUserPeriodStandard(array('standard_id'=>$stdId), $userId, $subUserId, $periodflag, $standard->fes_id);
            }
        }
        //如果用户选择了将此次选择设置为模板，那么更新用户的默认下级标准
        if('1'==$postData['defaultTemplate']){
            $dbUserIdentity=new Application_Model_DbTable_UserIdentify();
            $userId=Zend_Auth::getInstance()->getIdentity()->authedInfo['userId'];
            $dbUserIdentity->update(array('sub_standard_default'=>$strStandardId), "id='".$userId."'");
        }
        
    }
    /**
     * 根据用户id取得下级用户信息
     * @param unknown_type $userId
     */
    public function getSubUsersArrByUserId($userId){
        $res=$this->getSubUsersByUserId($userId);
        $arr=array();
        foreach($res as $re){
            $arr[$re['id']]=$re['sub_user_name'];
        }
        return $arr;
    }
   
}

